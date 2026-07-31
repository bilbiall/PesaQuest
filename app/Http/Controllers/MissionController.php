<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\PlayerMission;
use App\Services\MissionChecker;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MissionController extends Controller
{
    public function active(): JsonResponse
    {
        $user = auth()->user();

        $pm = PlayerMission::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('mission')
            ->first();

        if (!$pm) {
            return response()->json(null);
        }

        $m = $pm->mission;

        $deadlineDays  = $m->requirements['deadline_game_days'] ?? null;
        $daysElapsed   = $pm->activated_at
            ? app(\App\Services\GameClock::class)->gameDaysSince(Carbon::parse($pm->activated_at))
            : 0;
        $daysLeft      = $deadlineDays ? max(0, $deadlineDays - $daysElapsed) : null;
        $fractionUsed  = ($deadlineDays && $deadlineDays > 0) ? min(1, $daysElapsed / $deadlineDays) : 0;
        $urgency       = $fractionUsed >= 0.75 ? 'critical' : ($fractionUsed >= 0.5 ? 'warning' : 'safe');

        return response()->json([
            'id'                  => $m->id,
            'slug'                => $m->slug,
            'title'               => $m->title,
            'description'         => $m->description,
            'icon'                => $m->icon,
            'district_slug'       => $m->district_slug,
            'sequence'            => $m->sequence_order,
            'activated_at'        => $pm->activated_at,
            'deadline_game_days'  => $deadlineDays,
            'days_elapsed'        => (int) $daysElapsed,
            'days_left'           => $daysLeft !== null ? (int) ceil($daysLeft) : null,
            'fraction_used'       => round($fractionUsed, 3),
            'urgency'             => $urgency,
        ]);
    }

    public function check(int $id): JsonResponse
    {
        $user = auth()->user();

        $mission = Mission::findOrFail($id);

        $pm = PlayerMission::where('user_id', $user->id)
            ->where('mission_id', $id)
            ->where('status', 'active')
            ->first();

        if (!$pm) {
            return response()->json(['completed' => false, 'reason' => 'not_active']);
        }

        $checker = app(MissionChecker::class);

        if ($checker->check($user, $mission)) {
            $result = $checker->award($user, $mission);
            return response()->json(['completed' => true, ...$result]);
        }

        return response()->json(['completed' => false, 'reason' => 'requirement_not_met']);
    }
}
