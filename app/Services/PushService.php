<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\PushNotificationLog;
use App\Models\PushSubscription;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Web Push delivery. Every GameNotification is automatically mirrored here
 * (see GameNotification::booted()) — this class decides whether a push should
 * actually leave the server, and sends it synchronously (no queue daemon
 * required, which suits shared hosting). Sending is best-effort: any failure
 * here must NEVER interrupt the gameplay action that triggered it.
 *
 * Whether the player's phone actually POPS the notification (vs silently
 * queuing it) is decided client-side by the service worker, which only calls
 * showNotification() when no game tab is currently visible — so a player
 * actively playing never gets double-notified by their own in-game toast.
 */
class PushService
{
    /** How many pushes a single player may receive in a rolling 24h window. */
    const DAILY_CAP = 4;

    /** No pushes leave the server in this window (Africa/Nairobi local time). */
    const QUIET_START = '21:30';
    const QUIET_END   = '06:00';
    const TIMEZONE    = 'Africa/Nairobi';

    /** GameNotification `type` → push category, for per-category preferences. */
    const CATEGORY_MAP = [
        // 💰 money_alerts — bills, salary, credit, crises, investing
        'bill_assigned' => 'money_alerts', 'bill_due_soon' => 'money_alerts', 'bill_missed' => 'money_alerts',
        'bill_paid' => 'money_alerts', 'wages_lost' => 'money_alerts', 'salary_ready' => 'money_alerts',
        'job_warning' => 'money_alerts', 'job_dismissed' => 'money_alerts', 'friend_loan' => 'money_alerts',
        'salary' => 'money_alerts', 'credit_change' => 'money_alerts', 'loan_taken' => 'money_alerts',
        'savings_interest' => 'money_alerts', 'crisis_warning' => 'money_alerts', 'crisis_impact' => 'money_alerts',
        'mood_low' => 'money_alerts', 'deal_failed' => 'money_alerts', 'deal_success' => 'money_alerts',
        'investment' => 'money_alerts', 'investment_ready' => 'money_alerts', 'life_event' => 'money_alerts',

        // 🏆 achievements — celebrations
        'badge' => 'achievements', 'chapter_unlock' => 'achievements', 'quest' => 'achievements', 'contract_completed' => 'achievements',
        'quest_completed' => 'achievements', 'daily_bonus' => 'achievements', 'success' => 'achievements',
        'asset_purchased' => 'achievements', 'asset_sold' => 'achievements', 'asset_maintained' => 'achievements',

        // ⚡ opportunities — social & new content
        'job_hired' => 'opportunities', 'career_path' => 'opportunities', 'fun_world' => 'opportunities',
        'chama_member_joined' => 'opportunities', 'chama_asset_purchased' => 'opportunities', 'chama_invite' => 'opportunities',
        'chama_contribution' => 'opportunities', 'chama_income' => 'opportunities', 'forum_reply' => 'opportunities',
        'challenge' => 'opportunities', 'school_challenge' => 'opportunities', 'smart_reminder' => 'opportunities',
        'gig_available' => 'opportunities', 'teacher_invite' => 'opportunities',
        'friend_request' => 'opportunities', 'friend_accepted' => 'opportunities',

        // 📢 announcements — admin broadcasts
        'announcement' => 'announcements',

        // 🎓 teacher — school-portal digests (teachers only)
        'teacher_digest' => 'teacher',

        // 💳 monetization — subscription nudges (never sent to minors/school accounts)
        'subscribe_nudge' => 'monetization', 'plan_upsell' => 'monetization', 'subscription_expiring' => 'monetization',

        // 🌍 real_life_reminders — the player's OWN real-world bills/goals (premium tool),
        // deliberately separate from money_alerts since these are real dates, not game ticks
        'real_life_bill_due' => 'real_life_reminders',

        // 🎲 Rivals Trail (PesaTrail wager mode) — money movement alongside an
        // invite, which is social rather than a money alert
        'arcade_stake_joined' => 'money_alerts', 'arcade_stake_won' => 'money_alerts',
        'arcade_stake_lost' => 'money_alerts', 'arcade_forfeit_penalty' => 'money_alerts',
        'arcade_forfeit_bonus' => 'money_alerts', 'arcade_match_invite' => 'opportunities',
    ];

    const CATEGORIES = ['money_alerts', 'achievements', 'opportunities', 'announcements', 'teacher', 'monetization', 'real_life_reminders'];

    public function categoryFor(string $type): string
    {
        return self::CATEGORY_MAP[$type] ?? 'announcements';
    }

    /** Where tapping the notification should take the player, by type. */
    private function defaultUrlFor(string $type): string
    {
        return match (true) {
            str_starts_with($type, 'bill_'), $type === 'wages_lost', $type === 'salary_ready', $type === 'salary' => '/life',
            str_starts_with($type, 'crisis') => '/life/timeline',
            str_starts_with($type, 'quest') => '/quests',
            $type === 'job_hired', $type === 'career_path', $type === 'job_warning', $type === 'job_dismissed' => '/life/career',
            $type === 'badge', $type === 'chapter_unlock' => '/profile',
            $type === 'fun_world' => '/world',
            str_starts_with($type, 'chama_') => '/chama',
            str_starts_with($type, 'arcade_') => '/arcade/snakes-and-cash',
            str_starts_with($type, 'friend_') => '/friends',
            str_starts_with($type, 'forum'), $type === 'challenge', $type === 'school_challenge' => '/forum',
            $type === 'subscribe_nudge', $type === 'plan_upsell', $type === 'subscription_expiring' => '/subscribe',
            $type === 'teacher_digest' => '/dashboard',
            $type === 'real_life_bill_due' => '/dashboard#smart-tools',
            default => '/dashboard',
        };
    }

    public function isQuietHours(): bool
    {
        $t = now()->timezone(self::TIMEZONE)->format('H:i');
        // Window wraps midnight (21:30 → 06:00), so it's quiet if AFTER start OR BEFORE end.
        return $t >= self::QUIET_START || $t < self::QUIET_END;
    }

    public function withinDailyCap(User $user): bool
    {
        return PushNotificationLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->count() < self::DAILY_CAP;
    }

    /** Minors and school-managed accounts never receive monetization pushes. */
    public function isProtectedAccount(User $user): bool
    {
        if (in_array($user->age_group, ['8-12', '13-17'], true)) return true;
        if (method_exists($user, 'hasActiveSchoolMembership') && $user->hasActiveSchoolMembership()) return true;
        return false;
    }

    public function categoryEnabled(User $user, string $category): bool
    {
        $prefs = $user->notification_prefs ?? [];
        return (bool) ($prefs[$category] ?? true);
    }

    /** True if this notification should leave the server for this player right now. */
    public function isEligible(User $user, string $type): bool
    {
        $category = $this->categoryFor($type);

        if ($category === 'monetization' && $this->isProtectedAccount($user)) return false;
        if ($category === 'teacher' && !($user->is_school_teacher ?? false)) return false;
        if (!$this->categoryEnabled($user, $category)) return false;
        if ($this->isQuietHours()) return false;
        if (!$this->withinDailyCap($user)) return false;

        return true;
    }

    /** Called automatically whenever a GameNotification is created — see GameNotification::booted(). */
    public function mirror(GameNotification $notification): void
    {
        try {
            $user = $notification->user ?? User::find($notification->user_id);
            if (!$user) return;
            if (!$this->isEligible($user, $notification->type)) return;
            if (!PushSubscription::where('user_id', $user->id)->exists()) return;

            $this->send(
                $user,
                trim(($notification->icon ?? '') . ' ' . $notification->title),
                (string) $notification->body,
                [
                    'type' => $notification->type,
                    'url'  => $notification->data['url'] ?? $this->defaultUrlFor($notification->type),
                    // Unique per notification — a shared tag (e.g. 'announcement')
                    // made each new broadcast silently REPLACE the previous OS
                    // notification instead of alerting again.
                    'tag'  => $notification->type . '-' . ($notification->id ?? uniqid()),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Push mirror failed: ' . $e->getMessage(), ['notification_id' => $notification->id ?? null]);
        }
    }

    /**
     * Send a push directly (bypasses category/quiet-hour/cap checks). Used by
     * AdminController::testPush() for a deliberate manual self-test. The
     * broadcast composer does NOT use this — it creates a GameNotification
     * per recipient instead, which goes through mirror() below and correctly
     * respects each player's quiet hours/cap/preferences, exactly like any
     * other in-game notification.
     */
    public function send(User $user, string $title, string $body, array $options = []): bool
    {
        $publicKey  = Setting::get('vapid_public_key');
        $privateKey = Setting::get('vapid_private_key');
        if (empty($publicKey) || empty($privateKey)) return false;

        $subs = PushSubscription::where('user_id', $user->id)->get();
        if ($subs->isEmpty()) return false;

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => Setting::get('vapid_subject', 'mailto:support@moski.org'),
                'publicKey'  => $publicKey,
                'privateKey' => $privateKey,
            ],
        ], [], 5); // 5s timeout per push — never let a slow endpoint stall the request

        $payload = json_encode([
            'title' => mb_substr($title, 0, 80),
            'body'  => mb_substr($body, 0, 180),
            'icon'  => '/img/game/pwa-192.png',
            'badge' => '/img/game/pwa-192.png',
            'url'   => $options['url'] ?? '/dashboard',
            'tag'   => $options['tag'] ?? 'pesaquest',
        ]);

        foreach ($subs as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'publicKey'       => $sub->public_key,
                    'authToken'       => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aes128gcm',
                ]),
                $payload
            );
        }

        $sentAny = false;
        foreach ($webPush->flush() as $report) {
            $endpoint = (string) $report->getRequest()->getUri();
            $sub      = $subs->firstWhere('endpoint', $endpoint);
            if (!$sub) continue;

            if ($report->isSuccess()) {
                $sub->update(['last_used_at' => now(), 'failed_count' => 0]);
                $sentAny = true;
                continue;
            }

            $status = $report->getResponse()?->getStatusCode();
            if (in_array($status, [404, 410], true) || $sub->failed_count >= 4) {
                $sub->delete(); // dead subscription — browser revoked it
            } else {
                $sub->increment('failed_count');
            }
        }

        if ($sentAny) {
            PushNotificationLog::create([
                'user_id'  => $user->id,
                'category' => $this->categoryFor($options['type'] ?? 'announcement'),
                'type'     => $options['type'] ?? 'announcement',
            ]);
        }

        return $sentAny;
    }
}
