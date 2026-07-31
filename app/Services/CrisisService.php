<?php

namespace App\Services;

use App\Models\FinancialCrisis;
use App\Models\GameNotification;
use App\Models\Investment;
use App\Models\LifeEvent;
use App\Models\PlayerAsset;
use App\Models\PlayerLifeEvent;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Server-wide financial crisis engine.
 *
 * Crises are scheduled by admins/gameset users (financial_crises table):
 *   warning_at  → every player gets a "48-hour warning" notification
 *   active_from → the effect hits every player once (one-shot)
 *   active_until→ while inside this window, salary_cut crises also reduce
 *                 Pesa City job salaries paid by the LifeSimulator.
 *
 * Runs from the hourly scheduler AND opportunistically on player logins
 * (shared hosting often has no cron), guarded by cheap exists() checks.
 */
class CrisisService
{
    /** Per-request memo so multiple logins in one request don't re-query. */
    private static ?bool $ranThisRequest = null;

    private static ?float $salaryCutPct = null;

    /** Run warnings + effects if any crisis needs processing. Safe to call often. */
    public function processIfPending(): void
    {
        if (self::$ranThisRequest) return;
        self::$ranThisRequest = true;

        if (!Schema::hasTable('financial_crises')) return;

        $hasWarningCol = Schema::hasColumn('financial_crises', 'warning_sent_at');

        $pending = FinancialCrisis::where('is_processed', false)
            ->where(function ($q) use ($hasWarningCol) {
                $q->where('active_from', '<=', now());
                if ($hasWarningCol) {
                    $q->orWhere(fn ($w) => $w->whereNull('warning_sent_at')->where('warning_at', '<=', now()));
                } else {
                    $q->orWhere('warning_at', '<=', now());
                }
            })
            ->exists();

        if (!$pending) return;

        $this->sendWarnings();
        $this->applyEffects();
    }

    // ── Phase 1: warnings ─────────────────────────────────────────────────────

    public function sendWarnings(): int
    {
        $hasWarningCol = Schema::hasColumn('financial_crises', 'warning_sent_at');

        $query = FinancialCrisis::where('warning_at', '<=', now())
            ->where('active_from', '>', now())
            ->where('is_processed', false);

        // Pre-migration fallback: skip warnings entirely rather than re-spamming
        if (!$hasWarningCol) return 0;

        $due  = $query->whereNull('warning_sent_at')->get();
        $sent = 0;

        foreach ($due as $crisis) {
            $hoursAway = max(1, (int) round(($crisis->active_from->getTimestamp() - now()->getTimestamp()) / 3600));
            $userIds   = UserProgress::pluck('user_id');

            foreach ($userIds as $uid) {
                GameNotification::firstOrCreate(
                    ['user_id' => $uid, 'type' => 'crisis_warning', 'title' => "⚠️ Incoming: {$crisis->name}"],
                    [
                        'body' => "A financial crisis hits in about {$hoursAway} hour(s). {$crisis->description} — prepare your finances now.",
                        'icon' => $crisis->icon,
                        'data' => ['crisis_id' => $crisis->id, 'active_from' => $crisis->active_from],
                    ]
                );
                $sent++;
            }

            $crisis->update(['warning_sent_at' => now()]);
        }

        return $sent;
    }

    // ── Phase 2: effects ──────────────────────────────────────────────────────

    public function applyEffects(): int
    {
        $active  = FinancialCrisis::active()->where('is_processed', false)->get();
        $applied = 0;

        foreach ($active as $crisis) {
            DB::transaction(function () use ($crisis) {
                match ($crisis->effect_type) {
                    'investment_drop' => $this->dropInvestments($crisis),
                    'asset_drop'      => $this->dropAssets($crisis),
                    'balance_drain'   => $this->drainBalances($crisis),
                    'salary_cut'      => $this->cutSalaries($crisis),
                    default           => null,
                };
                $crisis->update(['is_processed' => true]);
            });
            $applied++;
        }

        return $applied;
    }

    /**
     * While a salary_cut crisis window is open, city-job salaries are reduced
     * by this percentage (largest active cut wins). Cached per request.
     */
    public function activeSalaryCutPercent(): float
    {
        if (self::$salaryCutPct !== null) return self::$salaryCutPct;
        if (!Schema::hasTable('financial_crises')) return self::$salaryCutPct = 0.0;

        return self::$salaryCutPct = (float) FinancialCrisis::active()
            ->where('effect_type', 'salary_cut')
            ->where('is_percentage', true)
            ->max('effect_amount') ?: 0.0;
    }

    /** The crisis currently in its active window (for UI banners), if any. */
    public function currentCrisis(): ?FinancialCrisis
    {
        if (!Schema::hasTable('financial_crises')) return null;
        return FinancialCrisis::active()->orderBy('active_from')->first();
    }

    // ── Effect appliers ───────────────────────────────────────────────────────

    private function dropInvestments(FinancialCrisis $crisis): void
    {
        $investments = Investment::where('status', 'pending')->get();

        foreach ($investments as $inv) {
            $drop = $crisis->is_percentage
                ? $inv->amount * ($crisis->effect_amount / 100)
                : min($inv->amount, $crisis->effect_amount);
            $inv->amount        = max(0, $inv->amount - $drop);
            $inv->return_amount = max(0, $inv->return_amount - $drop);
            $inv->save();

            GameNotification::create([
                'user_id' => $inv->user_id,
                'type'    => 'crisis_impact',
                'title'   => "📉 {$crisis->name} Hit Your Investment",
                'body'    => "Your {$inv->label} investment lost " . ($crisis->is_percentage ? "{$crisis->effect_amount}%" : 'Ksh ' . number_format($drop)) . ' due to the crisis.',
                'icon'    => $crisis->icon,
                'data'    => ['crisis_id' => $crisis->id],
            ]);

            $this->recordTimelineEvent($crisis, $inv->user_id, -$drop, 'Investment value reduced');
        }
    }

    private function dropAssets(FinancialCrisis $crisis): void
    {
        $assets = PlayerAsset::where('status', 'active')->get();

        $lossByUser = [];
        foreach ($assets as $pa) {
            $before = (int) $pa->current_value;
            $factor = $crisis->is_percentage ? (1 - $crisis->effect_amount / 100) : 1;
            $flat   = $crisis->is_percentage ? 0 : (int) $crisis->effect_amount;
            $pa->current_value = max(1, (int) round($pa->current_value * $factor) - $flat);
            $pa->save();
            $lossByUser[$pa->user_id] = ($lossByUser[$pa->user_id] ?? 0) + ($before - (int) $pa->current_value);
        }

        foreach ($lossByUser as $uid => $loss) {
            GameNotification::create([
                'user_id' => $uid,
                'type'    => 'crisis_impact',
                'title'   => '📉 Market Crash: Assets Devalued',
                'body'    => "The {$crisis->name} has reduced the value of your assets by Ksh " . number_format($loss) . '. Check your portfolio.',
                'icon'    => $crisis->icon,
                'data'    => ['crisis_id' => $crisis->id, 'loss' => $loss],
            ]);

            $this->recordTimelineEvent($crisis, $uid, -$loss, 'Asset values dropped');
        }
    }

    private function drainBalances(FinancialCrisis $crisis): void
    {
        $progresses = UserProgress::all();

        foreach ($progresses as $p) {
            $drain = $crisis->is_percentage
                ? (int) round($p->balance * ($crisis->effect_amount / 100))
                : min((int) $p->balance, (int) $crisis->effect_amount);
            if ($drain <= 0) continue;

            $p->balance = max(0, $p->balance - $drain);
            $p->save();

            GameNotification::create([
                'user_id' => $p->user_id,
                'type'    => 'crisis_impact',
                'title'   => "💸 {$crisis->name} Drained Your Balance",
                'body'    => 'You lost Ksh ' . number_format($drain) . ' due to the economic crisis.',
                'icon'    => $crisis->icon,
                'data'    => ['crisis_id' => $crisis->id, 'drain' => $drain],
            ]);

            $this->recordTimelineEvent($crisis, $p->user_id, -$drain, 'Cash reserves hit');
        }
    }

    private function cutSalaries(FinancialCrisis $crisis): void
    {
        // One-shot cut for legacy career income (mostly historical accounts)
        $progresses = UserProgress::where('career_income_rate', '>', 0)->get();
        foreach ($progresses as $p) {
            $cut = $crisis->is_percentage
                ? (int) round($p->career_income_rate * ($crisis->effect_amount / 100))
                : min((int) $p->career_income_rate, (int) $crisis->effect_amount);
            $p->career_income_rate = max(0, $p->career_income_rate - $cut);
            $p->save();
        }

        // City-job salaries are reduced live while the crisis window is open —
        // see LifeSimulator::settleJobSalaries() + activeSalaryCutPercent().
        // Notify every employed player so the pay cut isn't a mystery.
        if (Schema::hasTable('player_city_jobs')) {
            $employed = DB::table('player_city_jobs')->where('status', 'employed')->distinct()->pluck('user_id');
            foreach ($employed as $uid) {
                GameNotification::create([
                    'user_id' => $uid,
                    'type'    => 'crisis_impact',
                    'title'   => "📉 {$crisis->name}: Salaries Cut",
                    'body'    => "Employers are cutting pay by {$crisis->effect_amount}% while the crisis lasts. Salaries recover when it ends.",
                    'icon'    => $crisis->icon,
                    'data'    => ['crisis_id' => $crisis->id, 'cut_pct' => $crisis->effect_amount],
                ]);

                $this->recordTimelineEvent($crisis, $uid, 0, "Salaries cut {$crisis->effect_amount}% during the crisis");
            }
        }
    }

    // ── Timeline integration ──────────────────────────────────────────────────

    /**
     * Every crisis impact also lands on the player's Life Story timeline.
     * Each crisis is backed by a hidden LifeEvent template (is_active=false so
     * the random-event roller never picks it up).
     */
    private function recordTimelineEvent(FinancialCrisis $crisis, int $userId, float $delta, string $note): void
    {
        if (!Schema::hasTable('life_events') || !Schema::hasTable('player_life_events')) return;

        $template = $this->lifeEventTemplateFor($crisis);
        $progress = UserProgress::where('user_id', $userId)->first();

        PlayerLifeEvent::create([
            'user_id'            => $userId,
            'life_event_id'      => $template->id,
            'tick_triggered'     => $progress->tick_count ?? 0,
            'game_age_at_trigger'=> $progress->level ?? 1,
            'chapter_at_trigger' => $progress->life_chapter ?? 'student',
            'effect_applied'     => [
                'kind'      => 'crisis',
                'crisis_id' => $crisis->id,
                'delta'     => (int) round($delta),
                'note'      => $note,
            ],
        ]);
    }

    private array $templateCache = [];

    private function lifeEventTemplateFor(FinancialCrisis $crisis): LifeEvent
    {
        if (isset($this->templateCache[$crisis->id])) return $this->templateCache[$crisis->id];

        return $this->templateCache[$crisis->id] = LifeEvent::firstOrCreate(
            ['slug' => 'crisis-' . $crisis->id],
            [
                'chapter'          => 'all',
                'title'            => $crisis->icon . ' ' . $crisis->name,
                'description'      => $crisis->description,
                'flavor_text'      => '"Habari mbaya kwa uchumi." — Breaking news, every radio station.',
                'educational_note' => 'Economic crises are why emergency funds exist. Keep 3–6 months of expenses saved so a shock never forces you to sell assets at a loss.',
                'effect_type'      => 'narrative',
                'effect_data'      => ['source' => 'financial_crisis'],
                'probability'      => 0,
                'icon'             => $crisis->icon,
                'is_positive'      => false,
                'is_active'        => false,
            ]
        );
    }
}
