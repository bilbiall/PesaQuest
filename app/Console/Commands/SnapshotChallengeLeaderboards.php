<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Models\ChallengeParticipantSnapshot;
use App\Services\ChallengeService;
use Illuminate\Console\Command;

/**
 * Daily rank+progress snapshot per challenge participant, mirroring
 * SnapshotLeaderboard's approach for the main leaderboard — without a stored
 * history there's nothing to diff "today" against for the ↑/↓/— trend arrows
 * on a challenge's own leaderboard. Idempotent per day (unique on
 * challenge_participant_id+snapshot_date), so a retry just overwrites.
 */
class SnapshotChallengeLeaderboards extends Command
{
    protected $signature   = 'game:snapshot-challenge-leaderboards';
    protected $description = 'Record today\'s rank + progress for every active challenge\'s participants (for rank-change tracking)';

    public function handle(ChallengeService $service): int
    {
        $today = now()->toDateString();
        $now   = now();
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
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            ChallengeParticipantSnapshot::upsert($chunk, ['challenge_participant_id', 'snapshot_date'], ['rank', 'progress', 'updated_at']);
        }

        $this->info('Snapshotted ' . count($rows) . ' challenge participant row(s) for ' . $today . '.');

        return self::SUCCESS;
    }
}
