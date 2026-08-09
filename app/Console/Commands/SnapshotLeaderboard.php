<?php

namespace App\Console\Commands;

use App\Models\LeaderboardSnapshot;
use App\Models\SchoolMember;
use App\Models\SchoolSubscription;
use App\Models\UserProgress;
use Illuminate\Console\Command;

/**
 * Daily rank snapshot so the leaderboard can show a "▲3 / ▼1" move indicator —
 * without a stored history there's nothing to compare "today" against.
 * Idempotent per day (unique on user_id+scope_key+snapshot_date), so a retry
 * or a manual re-run the same day just overwrites, never duplicates.
 */
class SnapshotLeaderboard extends Command
{
    protected $signature   = 'game:snapshot-leaderboard';
    protected $description = 'Record today\'s leaderboard ranks (XP + Net Worth, per age group and school) for rank-change tracking';

    public function handle(): int
    {
        $today = now()->toDateString();
        $ageGroups = ['8-12', '13-17', '18-25', '26+'];
        $rows = [];

        foreach ($ageGroups as $ageGroup) {
            foreach (['xp', 'networth'] as $sortType) {
                $rows = array_merge($rows, $this->rankRows(
                    UserProgress::whereHas('user', fn ($q) => $q->where('age_group', $ageGroup)),
                    $sortType,
                    "global:{$sortType}:{$ageGroup}",
                    $today,
                ));
            }
        }

        foreach (SchoolSubscription::where('status', 'active')->where('ends_at', '>', now())->get() as $school) {
            $rosterIds = SchoolMember::where('school_subscription_id', $school->id)->where('status', 'active')->pluck('user_id');
            if ($rosterIds->isEmpty()) continue;

            foreach (['xp', 'networth'] as $sortType) {
                $rows = array_merge($rows, $this->rankRows(
                    UserProgress::whereIn('user_id', $rosterIds),
                    $sortType,
                    "school:{$sortType}:{$school->id}",
                    $today,
                ));
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            LeaderboardSnapshot::upsert($chunk, ['user_id', 'scope_key', 'snapshot_date'], ['rank', 'points', 'updated_at']);
        }

        $this->info('Snapshotted ' . count($rows) . ' leaderboard row(s) for ' . $today . '.');

        return self::SUCCESS;
    }

    private function rankRows($query, string $sortType, string $scopeKey, string $today): array
    {
        $now = now();

        if ($sortType === 'networth') {
            $players = $query->selectRaw('user_id, COALESCE(net_worth_cache, balance) as sort_value')
                ->orderByDesc('sort_value')
                ->limit(200)
                ->get();
            $pointsOf = fn ($p) => (int) $p->sort_value;
        } else {
            $players = $query->select('user_id', 'points_total')->orderByDesc('points_total')->limit(200)->get();
            $pointsOf = fn ($p) => (int) $p->points_total;
        }

        return $players->values()->map(fn ($p, $i) => [
            'user_id'       => $p->user_id,
            'scope_key'     => $scopeKey,
            'rank'          => $i + 1,
            'points'        => $pointsOf($p),
            'snapshot_date' => $today,
            'created_at'    => $now,
            'updated_at'    => $now,
        ])->all();
    }
}
