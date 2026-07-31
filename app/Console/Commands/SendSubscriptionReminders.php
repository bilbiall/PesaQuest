<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminderMail;
use App\Models\GameNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminders extends Command
{
    protected $signature   = 'subscriptions:remind {--dry-run : List users without sending}';
    protected $description = 'Send subscription expiry reminder emails, in-app notifications and pushes at 7d, 3d, 1d, due, 4d overdue, 14d overdue';

    // Days relative to expiry (negative = overdue, user's subscription expired N days ago)
    private const REMINDER_DAYS = [
        '7d'          =>  7,
        '3d'          =>  3,
        '1d'          =>  1,
        'due'         =>  0,
        '4d_overdue'  => -4,
        '14d_overdue' => -14,
    ];

    // In-app/push copy per reminder type — subtle, not alarmist, matches the game's voice
    private const NUDGE_COPY = [
        '7d'          => ['icon' => '📅', 'title' => 'Your subscription expires in a week', 'body' => 'expires {date} — renew any time and it queues up automatically with zero gap in your access.'],
        '3d'          => ['icon' => '⏳', 'title' => 'Only 3 days of Premium left',          'body' => 'expires {date}. Renew now so your city never slows back down.'],
        '1d'          => ['icon' => '⚠️', 'title' => 'Your subscription expires tomorrow',   'body' => 'expires {date} — last call to renew before you drop back to the free pace.'],
        'due'         => ['icon' => '🔔', 'title' => 'Your subscription expires today',      'body' => 'expires today. Renew now to keep full access without interruption.'],
        '4d_overdue'  => ['icon' => '😔', 'title' => "You're back on the free plan",         'body' => 'expired {date}. Renew any time to unlock full pace and depth again.'],
        '14d_overdue' => ['icon' => '💚', 'title' => 'We miss you on Premium',                'body' => 'expired {date}. Come back whenever you\'re ready — your progress is all still here.'],
    ];

    public function handle(): int
    {
        $today   = Carbon::today();
        $dryRun  = $this->option('dry-run');
        $sent    = 0;
        $skipped = 0;

        foreach (self::REMINDER_DAYS as $type => $daysOffset) {
            $targetDate = $today->copy()->addDays($daysOffset);

            // Find users whose most recent subscription ends on targetDate.
            // For pre-expiry reminders (daysOffset > 0) we only want active subscriptions.
            // For overdue reminders (daysOffset <= 0) the subscription will no longer be active,
            // so we just match by ends_at date without the status filter.
            $users = User::whereHas('subscriptions', function ($q) use ($targetDate, $daysOffset) {
                $q->whereDate('ends_at', $targetDate);
                if ($daysOffset > 0) {
                    $q->where('status', 'active');
                }
            })
            ->whereNotNull('email_verified_at')
            ->get();

            foreach ($users as $user) {
                // Get the relevant subscription (the one ending on targetDate)
                $sub = $user->subscriptions()
                    ->whereDate('ends_at', $targetDate)
                    ->latest()
                    ->first();

                // A paused subscription isn't really "expiring" (frozen), and an
                // upcoming stacked renewal hasn't started yet — neither deadline is
                // real to the player right now, so skip both to avoid confusing them.
                if (! $sub || $sub->isPaused() || $sub->isUpcoming()) {
                    continue;
                }

                $expiresAt   = Carbon::parse($sub->ends_at)->format('D, d M Y');
                $reminderKey = "sub_reminder_{$user->id}_{$type}_" . $today->format('Ymd');

                if (cache()->has($reminderKey)) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [DRY RUN] Would send '$type' to {$user->email} (expires {$expiresAt})");
                    continue;
                }

                try {
                    Mail::to($user->email)->send(new SubscriptionReminderMail($user, $type, $expiresAt));

                    // In-app bell + push (auto-mirrored via GameNotification::booted()) —
                    // same reminder, same cadence, so the player hears it even with email
                    // notifications off or unread.
                    $copy = self::NUDGE_COPY[$type] ?? null;
                    if ($copy) {
                        GameNotification::create([
                            'user_id' => $user->id,
                            'type'    => 'subscription_expiring',
                            'icon'    => $copy['icon'],
                            'title'   => $copy['icon'] . ' ' . $copy['title'],
                            'body'    => 'Your ' . ucfirst($sub->plan) . ' plan ' . str_replace('{date}', $expiresAt, $copy['body']),
                            'data'    => ['url' => route('subscribe.index'), 'reminder_type' => $type],
                        ]);
                    }

                    cache()->put($reminderKey, true, now()->endOfDay());
                    $sent++;
                    $this->line("  Sent '$type' to {$user->email}");
                } catch (\Exception $e) {
                    $this->error("  Failed for {$user->email}: " . $e->getMessage());
                }
            }
        }

        $this->info("Done. Sent: {$sent}, Skipped (already sent today): {$skipped}");
        return Command::SUCCESS;
    }
}
