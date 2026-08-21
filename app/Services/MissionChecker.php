<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Mission;
use App\Models\PlayerCityCourse;
use App\Models\PlayerCityJob;
use App\Models\PlayerAsset;
use App\Models\PlayerMission;
use App\Models\User;

class MissionChecker
{
    public function check(User $user, Mission $mission): bool
    {
        $reqs = $mission->requirements;
        $type = $reqs['type'] ?? '';

        return match ($type) {
            'asset_category'   => $this->checkAssetCategory($user, $mission, $reqs),
            'course_completed' => $this->checkCourseCompleted($user, $mission),
            'job_employed'     => $this->checkJobEmployed($user),
            default            => false,
        };
    }

    public function award(User $user, Mission $mission): array
    {
        PlayerMission::where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->update(['status' => 'completed', 'completed_at' => now()]);

        $rewards = $mission->rewards ?? [];
        $progress = $user->progress;

        if (!empty($rewards['xp'])) {
            $progress->addPoints((int) $rewards['xp']);
        }

        if (!empty($rewards['kes'])) {
            $progress->balance += (int) $rewards['kes'];
            $progress->save();
        }

        $badge = null;
        if (!empty($mission->badge_slug)) {
            $badge = Badge::where('slug', $mission->badge_slug)->first();
            if ($badge && !$user->badges()->where('badge_id', $badge->id)->exists()) {
                $user->badges()->attach($badge->id);
                app(\App\Services\QuestTriggerService::class)->fire($user, 'earn_badge', ['slug' => $badge->slug ?? '']);
            }
        }

        // Activate the next mission in sequence
        $next = Mission::active()
            ->where('sequence_order', $mission->sequence_order + 1)
            ->first();

        if ($next) {
            PlayerMission::updateOrCreate(
                ['user_id' => $user->id, 'mission_id' => $next->id],
                ['status' => 'active', 'activated_at' => now()]
            );
        }

        return [
            'mission_title' => $mission->title,
            'rewards'       => $rewards,
            'badge'         => $badge ? ['name' => $badge->name, 'icon' => $badge->icon, 'color' => $badge->color ?? '#15C77E'] : null,
            'next_mission'  => $next ? $next->title : null,
            'chain_complete' => $next === null,
        ];
    }

    // ── Requirement handlers ──────────────────────────────────────────────────

    private function checkAssetCategory(User $user, Mission $mission, array $reqs): bool
    {
        $pm = PlayerMission::where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->first();

        $since = $pm?->activated_at ?? now()->subDays(90);

        return PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('created_at', '>=', $since)
            ->whereHas('asset', fn ($q) => $q->where('category', $reqs['value'] ?? ''))
            ->exists();
    }

    private function checkCourseCompleted(User $user, Mission $mission): bool
    {
        $pm = PlayerMission::where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->first();

        $since = $pm?->activated_at ?? now()->subDays(90);

        return PlayerCityCourse::where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $since)
            ->exists();
    }

    private function checkJobEmployed(User $user): bool
    {
        return PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->exists();
    }
}
