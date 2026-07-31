<?php

namespace App\Services;

use App\Models\ChamaMember;
use App\Models\FinancialCrisis;
use App\Models\PlayerBill;
use App\Models\PlayerCityJob;
use App\Models\PlayerLoan;
use App\Models\SavingsScheme;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * The player-facing game calendar. Everything is DERIVED — no new tables:
 * bills come from next_due_tick, paydays from the 30-tick wage cycle, loan
 * installments from next_payment_tick, chama contributions from the game
 * month, crises from their real-world schedule mapped onto game days,
 * birthdays from the (private) DOB. One tick = one game day.
 */
class GameCalendarService
{
    public const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    public const HORIZON_DAYS = 14; // how far ahead the strip/API looks

    public function __construct(private GameClock $clock)
    {
    }

    /** Chip payload: where the player stands in game time right now. */
    public function today(User $user): array
    {
        $progress = $user->getOrCreateProgress();
        $tick     = (int) ($progress->tick_count ?? 0);

        return [
            'tick'    => $tick,
            'day'     => $tick + 1,                          // humans start at Day 1
            'weekday' => self::WEEKDAYS[$tick % 7],
            'week'    => intdiv($tick % 30, 7) + 1,
            'month'   => intdiv($tick, 30) + 1,
            'year'    => intdiv($tick, 365) + 1,
            'month_progress' => round(($tick % 30) / 30 * 100),
        ];
    }

    /** The next HORIZON_DAYS game days, each with its scheduled events. */
    public function upcoming(User $user): array
    {
        $progress = $user->getOrCreateProgress();
        $tick     = (int) ($progress->tick_count ?? 0);

        // offset (0 = today) → list of events
        $byOffset = array_fill(0, self::HORIZON_DAYS, []);

        $this->addBills($user, $tick, $byOffset);
        $this->addPaydays($user, $byOffset);
        $this->addLoanPayments($user, $tick, $byOffset);
        $this->addFriendLoans($user, $tick, $byOffset);
        $this->addChamaDays($user, $tick, $byOffset);
        $this->addSavingsInterest($user, $tick, $byOffset);
        $this->addAssetIncome($user, $tick, $byOffset);
        $this->addCrises($byOffset);
        $this->addBirthday($user, $byOffset);

        $days = [];
        for ($i = 0; $i < self::HORIZON_DAYS; $i++) {
            $dayTick = $tick + $i;
            $days[] = [
                'offset'   => $i,
                'day'      => $dayTick + 1,
                'weekday'  => self::WEEKDAYS[$dayTick % 7],
                'is_today' => $i === 0,
                'events'   => $byOffset[$i],
            ];
        }

        return ['today' => $this->today($user), 'days' => $days];
    }

    private function push(array &$byOffset, int $offset, array $event): void
    {
        if ($offset >= 0 && $offset < self::HORIZON_DAYS) {
            $byOffset[$offset][] = $event;
        }
    }

    private function addBills(User $user, int $tick, array &$byOffset): void
    {
        if (!Schema::hasTable('player_bills')) return;

        $bills = PlayerBill::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('bill:id,name,icon')
            ->get();

        foreach ($bills as $pb) {
            $offset = (int) $pb->next_due_tick - $tick;
            $this->push($byOffset, max(0, $offset), [
                'kind'   => 'bill',
                'icon'   => $pb->bill?->icon ?? '🧾',
                'label'  => ($pb->bill?->name ?? 'Bill') . ' due',
                'amount' => -(int) $pb->amount,
                'url'    => '/life',
            ]);
        }
    }

    private function addPaydays(User $user, array &$byOffset): void
    {
        if (!Schema::hasTable('player_city_jobs') || !Schema::hasColumn('player_city_jobs', 'unpaid_ticks')) return;

        $jobs = PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->with('job:id,title,employer_name,employer_logo,salary_kes_month,employment_type,is_part_time')
            ->get();

        foreach ($jobs as $pj) {
            if (!$pj->job) continue;
            // Wages accrue per tick; every 30 unpaid ticks a new payslip lands.
            // Pay stacks (never forfeited) — but a payday landing on uncollected
            // pay adds an attendance strike (Report-to-Work mechanic).
            $toBoundary = 30 - (((int) $pj->unpaid_ticks) % 30);

            $hasPending    = (int) ($pj->pending_salary ?? 0) > 0;
            $onFinalNotice = !empty($pj->removal_warned_at_tick);

            // Uncollected pay shows on TODAY — the most actionable marker there is
            if ($hasPending) {
                $this->push($byOffset, 0, [
                    'kind'   => 'payday',
                    'icon'   => $onFinalNotice ? '🚨' : '💰',
                    'label'  => ($onFinalNotice ? 'FINAL NOTICE — report to work or be dismissed! ' : 'Pay ready to collect — ') . $pj->job->employer_name,
                    'amount' => (int) $pj->pending_salary,
                    'url'    => '/life/career',
                ]);
            }

            $this->push($byOffset, $toBoundary, [
                'kind'   => 'payday',
                'icon'   => $pj->job->employer_logo ?? '💼',
                'label'  => 'Payday — ' . $pj->job->employer_name . ($hasPending ? ' (skipping adds a strike!)' : ''),
                'amount' => (int) $pj->job->salary_kes_month,
                'url'    => '/life/career',
            ]);
        }
    }

    /** Active loan installments (bank loans + asset financing) auto-deduct on their tick. */
    private function addLoanPayments(User $user, int $tick, array &$byOffset): void
    {
        if (!Schema::hasTable('player_loans')) return;

        $loans = PlayerLoan::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('loanProduct:id,name,icon')
            ->get();

        foreach ($loans as $loan) {
            $offset = (int) $loan->next_payment_tick - $tick;
            $this->push($byOffset, max(0, $offset), [
                'kind'   => 'loan',
                'icon'   => $loan->loanProduct?->icon ?? '📄',
                'label'  => 'Loan installment — ' . $loan->displayName(),
                'amount' => -(int) $loan->payment_amount,
                'url'    => '/life',
            ]);
        }
    }

    /** Friend loans the player owes — due on the borrower's own clock. */
    private function addFriendLoans(User $user, int $tick, array &$byOffset): void
    {
        if (!Schema::hasTable('friend_loans')) return;

        $loans = \App\Models\FriendLoan::where('borrower_id', $user->id)
            ->where('status', 'active')
            ->whereNotNull('due_at_tick')
            ->with('lender:id,name')
            ->get();

        foreach ($loans as $loan) {
            $offset = (int) $loan->due_at_tick - $tick;
            $this->push($byOffset, max(0, $offset), [
                'kind'   => 'loan',
                'icon'   => '🤝',
                'label'  => 'Repay ' . ($loan->lender?->name ?? 'friend') . "'s loan" . ($offset <= 0 ? ' (overdue!)' : ''),
                'amount' => -$loan->remaining(),
                'url'    => '/friends',
            ]);
        }
    }

    /** Chama contribution windows: one per game month, on the member's own clock. */
    private function addChamaDays(User $user, int $tick, array &$byOffset): void
    {
        if (!Schema::hasTable('chama_members') || !Schema::hasTable('chama_contributions')) return;

        $memberships = ChamaMember::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('chama:id,name,monthly_contribution,status')
            ->get();

        if ($memberships->isEmpty()) return;

        $gameMonth = 'GM-' . str_pad((string) intdiv($tick, 30), 4, '0', STR_PAD_LEFT);
        $paidThisMonth = \App\Models\ChamaContribution::where('user_id', $user->id)
            ->where('game_month', $gameMonth)
            ->where('status', 'paid')
            ->pluck('chama_id')
            ->all();

        $toNextMonth = 30 - ($tick % 30);

        foreach ($memberships as $m) {
            if (!$m->chama || $m->chama->status === 'dissolved') continue;

            if (!in_array($m->chama_id, $paidThisMonth, true)) {
                // This month's contribution is still open — due before the month turns
                $this->push($byOffset, 0, [
                    'kind'   => 'chama',
                    'icon'   => '🤝',
                    'label'  => 'Chama contribution open — ' . $m->chama->name . " (closes in {$toNextMonth}d)",
                    'amount' => -(int) $m->chama->monthly_contribution,
                    'url'    => '/chama',
                ]);
            }

            // Next month's window opens when the game month turns
            $this->push($byOffset, $toNextMonth, [
                'kind'   => 'chama',
                'icon'   => '🤝',
                'label'  => 'New chama month — ' . $m->chama->name . ' contribution due',
                'amount' => -(int) $m->chama->monthly_contribution,
                'url'    => '/chama',
            ]);
        }
    }

    private function addSavingsInterest(User $user, int $tick, array &$byOffset): void
    {
        if (!Schema::hasTable('savings_schemes') || !Schema::hasColumn('savings_schemes', 'last_interest_tick')) return;

        $schemes = SavingsScheme::where('user_id', $user->id)
            ->where('current_amount', '>', 0)
            ->get(['id', 'name', 'current_amount', 'last_interest_tick']);

        foreach ($schemes as $s) {
            $offset = 30 - max(0, $tick - (int) $s->last_interest_tick) % 30;
            $this->push($byOffset, $offset === 30 ? 30 : $offset, [
                'kind'   => 'interest',
                'icon'   => '🏦',
                'label'  => 'Bank interest — ' . ($s->name ?? 'Savings'),
                'amount' => (int) round($s->current_amount * 0.08 / 12),
                'url'    => '/savings',
            ]);
        }
    }

    /** Passive income from owned assets — every payout inside the horizon. */
    private function addAssetIncome(User $user, int $tick, array &$byOffset): void
    {
        if (!Schema::hasTable('player_assets')) return;

        $hasAnchor = Schema::hasColumn('player_assets', 'income_paid_to_tick');

        $assets = \App\Models\PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('asset:id,name,icon,monthly_income,income_period_ticks')
            ->get();

        foreach ($assets as $pa) {
            $a = $pa->asset;
            if (!$a || ($a->monthly_income ?? 0) <= 0) continue;
            if ($pa->conditionFactor() <= 0) continue; // broken — nothing to forecast

            $period = max(1, (int) ($a->income_period_ticks ?? 30));
            $anchor = $hasAnchor && $pa->income_paid_to_tick !== null
                ? (int) $pa->income_paid_to_tick
                : (int) ($pa->purchased_at_tick ?? $tick);
            $offset = $period - (max(0, $tick - $anchor) % $period);
            $amount = (int) round($a->monthly_income * $pa->quantity * $pa->conditionFactor());

            for ($o = $offset; $o < self::HORIZON_DAYS; $o += $period) {
                $this->push($byOffset, $o, [
                    'kind'   => 'asset',
                    'icon'   => $a->icon ?? '🏗️',
                    'label'  => 'Asset income — ' . $a->name,
                    'amount' => $amount,
                    'url'    => '/portfolio',
                ]);
            }
        }
    }

    /**
     * Scheduled crises appear on the calendar from their warning moment
     * (admin sets warning_at, typically ~48 real hours before it strikes) —
     * a "storm warning" on the day it will hit, so players can build a buffer.
     */
    private function addCrises(array &$byOffset): void
    {
        if (!Schema::hasTable('financial_crises')) return;

        $crises = FinancialCrisis::where('warning_at', '<=', now())
            ->where('active_until', '>=', now())
            ->get(['id', 'name', 'icon', 'effect_type', 'effect_amount', 'active_from']);

        foreach ($crises as $c) {
            $offset = $c->active_from->isPast() ? 0 : $this->clock->gameDaysUntil($c->active_from);
            $this->push($byOffset, (int) $offset, [
                'kind'   => 'crisis',
                'icon'   => $c->icon ?? '⚠️',
                'label'  => ($c->active_from->isPast() ? 'CRISIS ACTIVE — ' : 'Incoming crisis — ') . $c->name,
                'amount' => null,
                'url'    => '/life',
            ]);
        }
    }

    private function addBirthday(User $user, array &$byOffset): void
    {
        if (!$user->date_of_birth) return;

        $today = now('Africa/Nairobi')->startOfDay();
        $next  = $user->date_of_birth->copy()->year($today->year)->startOfDay();
        if ($next->lt($today)) $next->addYear();

        $realDays = (int) floor($today->diffInDays($next));
        // Map the real date onto game days: how many ticks pass in that real time
        $offset = $realDays === 0 ? 0 : (int) $this->clock->ticksSince(now()->subDays($realDays));
        $this->push($byOffset, $offset, [
            'kind'   => 'birthday',
            'icon'   => '🎂',
            'label'  => $realDays === 0 ? 'Happy Birthday! A gift awaits 🎁' : 'Your birthday (real-world) — gifts! 🎁',
            'amount' => null,
            'url'    => '/dashboard',
        ]);
    }
}
