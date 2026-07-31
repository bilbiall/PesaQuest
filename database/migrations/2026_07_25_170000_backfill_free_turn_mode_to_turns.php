<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Turn-based play is no longer a choice — every match is created with
     * turn_mode='turns' now (see ArcadeSnakesService::createMatch() /
     * startSoloWithBot()). But matches created before that change are still
     * sitting in the DB as turn_mode='free' with no current_turn_session_id
     * or turn_started_at ever set, so ArcadeMatch::isTurnBased() is false for
     * them forever and roll()'s turn check never engages — anyone (including
     * a solo companion bot) can roll anytime, indistinguishable from the bug
     * this phase fixed. Backfill any still-open/active one to behave exactly
     * like a freshly-created turn-based match: hand the turn to whichever
     * session is first in seating order and start its 10s clock now.
     */
    public function up(): void
    {
        $matches = DB::table('arcade_matches')
            ->where('turn_mode', 'free')
            ->whereIn('status', ['open', 'active'])
            ->get();

        foreach ($matches as $match) {
            $firstSession = DB::table('arcade_sessions')
                ->where('arcade_match_id', $match->id)
                ->where('status', 'active')
                ->orderBy('turn_order')
                ->orderBy('id')
                ->first();

            DB::table('arcade_matches')->where('id', $match->id)->update([
                'turn_mode'               => 'turns',
                'current_turn_session_id' => $firstSession->id ?? null,
                'turn_started_at'         => now(),
                'updated_at'              => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Deliberately not reversed — free-for-all mode no longer exists anywhere
        // in the app, so there is nothing meaningful to roll these back to.
    }
};
