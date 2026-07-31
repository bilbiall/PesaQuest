<?php

namespace App\Console\Commands;

use App\Models\GameNotification;
use App\Models\PlayerBill;
use App\Models\PlayerCityJob;
use App\Models\PushSubscription;
use App\Models\UserProgress;
use App\Services\GameClock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * The reason push notifications can be trusted while a player is offline:
 * this command predicts real-world ETAs from stored tick data WITHOUT
 * simulating or mutating anything (that only ever happens on login, per the
 * game's core architecture). It just asks "if this player logged in right
 * now, how many game days would have passed — and does that cross a
 * bill due-date or a missed-payday strike (attendance) line soon?"
 *
 * Meant to run every 15–30 minutes via real cron. Because shared hosting
 * cron isn't guaranteed (see docs/ADMIN-GUIDE.md §8), this only sharpens
 * timing — the login-driven simulation is unaffected either way. Only
 * players with at least one push subscription are considered, so cost is
 * proportional to actual push adoption, not total player count.
 */
class SendPredictivePushes extends Command
{
    protected $signature = 'push:predictive-check';

    protected $description = 'Warn offline players before a bill goes overdue or a skipped payday earns an attendance strike';

    /** Only warn once a bill/payslip is within this many real hours of the cutoff. */
    const WARNING_WINDOW_HOURS = 5;

    /** Don't re-warn about the same thing more often than this. */
    const DEDUPE_HOURS = 4;

    public function handle(GameClock $clock): int
    {
        $userIds = PushSubscription::distinct()->pluck('user_id');
        if ($userIds->isEmpty()) {
            $this->info('No push subscribers — nothing to check.');
            return self::SUCCESS;
        }

        $sent = 0;
        $progresses = UserProgress::whereIn('user_id', $userIds)->get()->keyBy('user_id');

        foreach ($progresses as $progress) {
            if (!$progress->last_tick_at) continue;

            // The tick count this player WOULD be at if they logged in right now —
            // purely arithmetic, no writes.
            $effectiveTick = (int) $progress->tick_count + $clock->ticksSince($progress->last_tick_at);

            $sent += $this->checkBills($progress, $effectiveTick, $clock);
            $sent += $this->checkPayday($progress, $effectiveTick, $clock);
        }

        $this->info("Predictive pushes queued for creation: {$sent}.");
        return self::SUCCESS;
    }

    private function alreadyWarned(int $userId, string $type, string $dedupeKey): bool
    {
        return GameNotification::where('user_id', $userId)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subHours(self::DEDUPE_HOURS))
            ->whereJsonContains('data->dedupe_key', $dedupeKey)
            ->exists();
    }

    private function checkBills(UserProgress $progress, int $effectiveTick, GameClock $clock): int
    {
        $count = 0;

        $bills = PlayerBill::where('user_id', $progress->user_id)
            ->where('status', 'active')
            ->with('bill')
            ->get();

        foreach ($bills as $pb) {
            if (!$pb->bill) continue;

            $ticksLeft = $pb->next_due_tick - $effectiveTick;
            if ($ticksLeft <= 0) continue; // already due/overdue — settleBills will handle it on next login

            $secondsLeft = $clock->realSecondsForTicks($ticksLeft);
            if ($secondsLeft > self::WARNING_WINDOW_HOURS * 3600) continue; // not urgent yet

            $key = "bill-{$pb->id}-{$pb->next_due_tick}";
            if ($this->alreadyWarned($progress->user_id, 'bill_due_soon', $key)) continue;

            GameNotification::create([
                'user_id' => $progress->user_id,
                'type'    => 'bill_due_soon',
                'title'   => "⚠️ {$pb->bill->name} due soon",
                'body'    => 'Ksh ' . number_format($pb->amount) . ' is due in about ' . $clock->approxRealLabel($ticksLeft) . '. Pay it from Life HQ before it goes overdue.',
                'icon'    => $pb->bill->icon ?? '🧾',
                'data'    => ['bill_id' => $pb->bill_id, 'dedupe_key' => $key, 'url' => '/life'],
            ]);
            $count++;
        }

        return $count;
    }

    private function checkPayday(UserProgress $progress, int $effectiveTick, GameClock $clock): int
    {
        if (!Schema::hasTable('player_city_jobs') || !Schema::hasColumn('player_city_jobs', 'pending_salary')) {
            return 0;
        }

        $count = 0;
        $jobs  = PlayerCityJob::where('user_id', $progress->user_id)
            ->where('status', 'employed')
            ->where('pending_salary', '>', 0)
            ->with('job')
            ->get();

        foreach ($jobs as $pj) {
            if (!$pj->job) continue;

            // Ticks the player has effectively accrued toward the NEXT payday.
            // Wages stack (never forfeited), but a payday landing on uncollected
            // pay adds an attendance strike — 3 strikes = final notice, then
            // one more silent month = dismissal.
            $extraTicks       = $effectiveTick - (int) $progress->tick_count;
            $effectiveUnpaid  = (int) $pj->unpaid_ticks + max(0, $extraTicks);
            $ticksToNextPayday = 30 - ($effectiveUnpaid % 30);

            if ($ticksToNextPayday <= 0) continue;
            $secondsLeft = $clock->realSecondsForTicks($ticksToNextPayday);
            if ($secondsLeft > self::WARNING_WINDOW_HOURS * 3600) continue;

            $onFinalNotice = !empty($pj->removal_warned_at_tick);
            $key  = "payday-{$pj->id}-{$pj->pending_salary}";
            $type = $onFinalNotice ? 'job_warning' : 'salary_ready';
            if ($this->alreadyWarned($progress->user_id, $type, $key)) continue;

            GameNotification::create([
                'user_id' => $progress->user_id,
                'type'    => $type,
                'title'   => $onFinalNotice
                    ? '🚨 Last chance at ' . $pj->job->employer_name
                    : '🧾 Ksh ' . number_format($pj->pending_salary) . ' waiting at ' . $pj->job->employer_name,
                'body'    => $onFinalNotice
                    ? 'You are on a final notice. Report to work in the next ' . $clock->approxRealLabel($ticksToNextPayday) . ' or you will be dismissed — your pay stays collectible, but the job is gone.'
                    : 'Your pay stacks up safely, but payday lands in about ' . $clock->approxRealLabel($ticksToNextPayday) . ' — skipping it adds an attendance strike (3 in a row risks your job).',
                'icon'    => $onFinalNotice ? '🚨' : ($pj->job->employer_logo ?? '🧾'),
                'data'    => ['job_id' => $pj->id, 'dedupe_key' => $key, 'url' => '/life/career'],
            ]);
            $count++;
        }

        return $count;
    }
}
