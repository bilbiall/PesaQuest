<?php

namespace App\Services;

use App\Models\GameNotification;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Facades\Schema;

/**
 * Single source of truth for "what is this player's current X" — shared by
 * ContractService (personal contracts) and ChallengeService (PvP/broadcast
 * challenges), so both baseline+delta systems measure metrics identically.
 */
class GameMetrics
{
    /** metric key => style: count|amount|absolute|clear */
    public const METRIC_STYLES = [
        'courses_completed'   => 'count',
        'jobs_started'        => 'count',
        'gigs_completed'      => 'count',
        'paydays_collected'   => 'count',
        'assets_owned'        => 'count',
        'chama_contributions' => 'count',
        'friends_count'       => 'count',
        'forum_posts'         => 'count',
        'bills_paid'          => 'count',
        'savings_balance'     => 'amount',
        'wallet_balance'      => 'amount',
        'net_worth'           => 'amount',
        'xp_points'           => 'amount',
        'mood_level'          => 'absolute',
        'overdue_cleared'     => 'clear',
        'arcade_wins'         => 'count',
        'arcade_winnings'     => 'amount',
    ];

    /**
     * Metrics safe to use as a PERCENT-growth challenge objective, with a
     * floor divisor so a near-zero starting balance doesn't produce an
     * absurd or divide-by-zero percentage (see ChallengeService::refresh()).
     */
    public const PERCENT_FLOOR = [
        'net_worth'       => 500,
        'savings_balance' => 200,
        'xp_points'       => 50,
    ];

    /** Current absolute value of a metric for this player. */
    public static function current(string $metric, User $user, UserProgress $progress, ?\Carbon\Carbon $since = null): int
    {
        $since = $since ?? now();

        return (int) match ($metric) {
            'courses_completed'   => \App\Models\PlayerCityCourse::where('user_id', $user->id)->where('status', 'completed')->count(),
            'jobs_started'        => \App\Models\PlayerCityJob::where('user_id', $user->id)->count(),
            'gigs_completed'      => \App\Models\PlayerCityJob::where('user_id', $user->id)->where('employment_type', 'freelance')->where('status', 'completed')->count(),
            'paydays_collected'   => GameNotification::where('user_id', $user->id)->where('type', 'salary')->where('created_at', '>=', $since)->count(),
            'bills_paid'          => GameNotification::where('user_id', $user->id)->where('type', 'bill_paid')->where('created_at', '>=', $since)->count(),
            'assets_owned'        => \App\Models\PlayerAsset::where('user_id', $user->id)->count(),
            'chama_contributions' => Schema::hasTable('chama_contributions')
                ? \App\Models\ChamaContribution::where('user_id', $user->id)->where('status', 'paid')->count() : 0,
            'friends_count'       => count($user->friendIds()),
            'forum_posts'         => \App\Models\ForumTopic::where('user_id', $user->id)->count()
                                   + \App\Models\ForumReply::where('user_id', $user->id)->count(),
            'savings_balance'     => Schema::hasTable('savings_schemes')
                ? \App\Models\SavingsScheme::where('user_id', $user->id)->sum('current_amount') : 0,
            'wallet_balance'      => (int) ($progress->balance ?? 0),
            'net_worth'           => (int) ($progress->net_worth_cache ?? 0),
            'xp_points'           => (int) ($progress->points_total ?? 0),
            'mood_level'          => (int) ($progress->mood ?? 0),
            'overdue_cleared'     => \App\Models\PlayerBill::where('user_id', $user->id)->where('status', 'overdue')->count(),
            // Pesa Trail (arcade) sessions are permanent, one-per-play rows — a
            // continuous, no-new-content metric source, unlike course/job-based
            // metrics above which need authored content to keep growing. Counts
            // across solo, standard multiplayer, and Rivals Trail wager play alike.
            'arcade_wins'         => \App\Models\ArcadeSession::where('user_id', $user->id)->where('status', 'won')->count(),
            'arcade_winnings'     => (int) \App\Models\ArcadeSession::where('user_id', $user->id)->whereIn('status', ['won', 'cashed_out'])->sum('pot_amount'),
            default               => 0,
        };
    }
}
