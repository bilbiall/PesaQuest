<?php

namespace App\Console\Commands;

use App\Models\GameNotification;
use App\Models\RealLifeBill;
use App\Services\PlanGate;
use Illuminate\Console\Command;

/**
 * Daily check for the player's real-life bills (Money Toolkit, premium) —
 * fires a push reminder once per billing cycle when a bill enters its
 * reminder window, via the SAME GameNotification → PushService pipeline
 * every other in-game notification uses (quiet hours, daily cap, per-category
 * opt-out all apply automatically). Runs on real calendar dates — completely
 * independent of the game clock/ticks.
 */
class SendRealLifeBillReminders extends Command
{
    protected $signature   = 'reallife:remind {--dry-run : List what would be sent without sending}';
    protected $description = 'Send push reminders for real-life bills entering their reminder window';

    public function handle(PlanGate $gate): int
    {
        $dryRun = $this->option('dry-run');
        $sent   = 0;
        $skipped = 0;

        $candidates = RealLifeBill::where('status', 'active')
            ->whereNull('last_reminded_at')
            ->with('user')
            ->get()
            ->filter(fn (RealLifeBill $b) => $b->isDueWithin($b->reminder_lead_days));

        foreach ($candidates as $bill) {
            $user = $bill->user;
            if (!$user) continue;

            // A player who lost premium access stops getting reminders until they resubscribe.
            if ($gate->limit($user, 'smart_tools_access') < 1) {
                $skipped++;
                continue;
            }

            $daysLeft = max(0, (int) now()->startOfDay()->diffInDays($bill->next_due_date, false));
            $dueLabel = $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'tomorrow' : "in {$daysLeft} days");

            if ($dryRun) {
                $this->line("  [DRY RUN] Would remind {$user->email} — {$bill->name} due {$dueLabel}");
                continue;
            }

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'real_life_bill_due',
                'title'   => "{$bill->icon} {$bill->name} due {$dueLabel}",
                'body'    => 'Ksh ' . number_format($bill->amount) . " — real life, not the game. Mark it paid once it's settled.",
                'icon'    => $bill->icon,
                'data'    => ['real_life_bill_id' => $bill->id, 'amount' => $bill->amount],
            ]);

            $bill->update(['last_reminded_at' => now()]);
            $sent++;
        }

        $this->info("Done. Sent: {$sent}, Skipped (no premium access): {$skipped}");
        return Command::SUCCESS;
    }
}
