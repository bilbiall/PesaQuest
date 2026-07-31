<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\UserQuest;
use Illuminate\Support\Facades\Schema;

/**
 * Quest Gate — quests are homework, not homework you can skip.
 *
 * When enabled, a player cannot level PAST a level that still has incomplete
 * quests: the cap is the lowest level_required among their active, applicable
 * (age group + career path), unfinished quests. XP keeps accumulating, but the
 * level only advances once the gating quests are cleared — at which point the
 * banked XP applies instantly.
 *
 * The gate never demotes: a player who out-levelled their quests before the
 * gate existed keeps their level; it simply binds from there onward.
 */
class QuestGate
{
    public const SETTING = 'quest_gate_enabled';

    /** Per-request cache: user_id => ['cap' => int, 'remaining' => int] */
    private static array $cache = [];

    public static function enabled(): bool
    {
        return Schema::hasTable('quests')
            && Schema::hasColumn('quests', 'level_required')
            && Setting::get(self::SETTING, '1') === '1';
    }

    /** Drop the cached cap (call after completing quests so level-ups apply immediately). */
    public static function forget(int $userId): void
    {
        unset(self::$cache[$userId]);
    }

    /**
     * The gate for a player considering XP level $xpLevel:
     * ['cap' => lowest incomplete quest level (PHP_INT_MAX if clear),
     *  'remaining' => count of incomplete quests at that gating level].
     */
    public static function capFor(UserProgress $progress, int $xpLevel): array
    {
        $userId = (int) $progress->user_id;
        if (isset(self::$cache[$userId])) {
            return self::$cache[$userId];
        }

        $ageGroup = User::where('id', $userId)->value('age_group');

        $completedIds = UserQuest::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->pluck('quest_id');

        $query = Quest::where('is_active', true)
            ->where('level_required', '<=', max(1, $xpLevel))
            ->whereNotIn('id', $completedIds)
            ->where(fn ($q) => $q->where('age_group', 'all')
                ->orWhereNull('age_group')
                ->orWhere('age_group', $ageGroup ?: 'all'));

        if (Schema::hasColumn('quests', 'career_fields')) {
            $query->forCareerField($progress->career_field ?? null);
        }

        $cap = $query->min('level_required');

        $result = $cap === null
            ? ['cap' => PHP_INT_MAX, 'remaining' => 0]
            : ['cap' => (int) $cap, 'remaining' => (clone $query)->where('level_required', (int) $cap)->count()];

        return self::$cache[$userId] = $result;
    }

    /** Apply the gate to a raw XP level. Never demotes below the stored level. */
    public static function apply(UserProgress $progress, int $xpLevel): int
    {
        if ($xpLevel <= 1 || !self::enabled()) {
            return $xpLevel;
        }

        $gate = self::capFor($progress, $xpLevel);

        return min($xpLevel, max($gate['cap'], (int) ($progress->level ?? 1) ?: 1));
    }

    /**
     * Player-facing status for banners/HUDs:
     * blocked=true when banked XP is waiting behind unfinished quests.
     */
    public static function status(UserProgress $progress): array
    {
        $none = ['blocked' => false, 'level' => (int) ($progress->level ?? 1), 'xp_level' => (int) ($progress->level ?? 1), 'gate_level' => null, 'remaining' => 0];

        if (!self::enabled()) return $none;

        $xpLevel = $progress->calculateXpLevel();
        $current = (int) ($progress->level ?? 1);
        if ($xpLevel <= $current) return $none;

        $gate = self::capFor($progress, $xpLevel);
        if ($gate['cap'] >= $xpLevel) return $none;

        return [
            'blocked'    => true,
            'level'      => $current,
            'xp_level'   => $xpLevel,
            'gate_level' => $gate['cap'],
            'remaining'  => $gate['remaining'],
        ];
    }
}
