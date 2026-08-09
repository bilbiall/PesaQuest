<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\ChallengeParticipantSnapshot;
use App\Services\ChallengeService;
use Illuminate\Console\Command;

/**
 * Rank+progress snapshot per challenge participant, taken every 15 minutes —
 * without a stored history there's nothing to diff "now" against for the
 * ↑/↓/— trend arrows on a challenge's own leaderboard. Unlike the old daily
 * version, this always INSERTs a fresh row (no more one-per-day upsert),
 * since the whole point now is a short-window rolling comparison rather than
 * a day-over-day one. Prunes anything older than 48 hours so the table
 * doesn't grow unbounded — that's already far more history than the ~15-min
 * comparison window needs.
 */
class SnapshotChallengeLeaderboards extends Command
{
    protected $signature   = 'game:snapshot-challenge-leaderboards';
    protected $description = 'Record rank + progress for every active challenge\'s participants (for rank-change tracking)';

    public function handle(ChallengeService $service): int
    {
        $now   = now();
        $today = $now->toDateString();
        $rows  = [];

        foreach (Challenge::where('status', 'active')->get() as $challenge) {
            $participants = $challenge->participants()->where('status', 'accepted')->get();
            if ($participants->isEmpty()) continue;

            $rankMap = $service->rankParticipants($challenge, $participants);

            foreach ($participants as $p) {
                $rows[] = [
                    'challenge_participant_id' => $p->id,
                    'rank'                     => $rankMap[$p->id] ?? null,
                    'progress'                 => $p->progress,
                    'snapshot_date'            => $today,
                    'snapshot_at'              => $now,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            ChallengeParticipantSnapshot::insert($chunk);
        }

        $pruned = ChallengeParticipantSnapshot::where('snapshot_at', '<', $now->copy()->subHours(48))->delete();

        $this->info('Snapshotted ' . count($rows) . ' challenge participant row(s) at ' . $now->toDateTimeString() . '. Pruned ' . $pruned . ' old row(s).');

        return self::SUCCESS;
    }
}
