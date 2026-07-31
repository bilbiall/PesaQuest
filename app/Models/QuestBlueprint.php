<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A reusable quest recipe: trigger steps + a level range/cadence + value and
 * reward curves. The Quest Factory sweep prints one quest per level "rung";
 * chain=true links consecutive rungs via a complete_quest step.
 */
class QuestBlueprint extends Model
{
    protected $fillable = [
        'name', 'archetype', 'icon', 'age_group', 'career_fields',
        'level_min', 'level_max', 'level_step', 'chain', 'steps',
        'xp_base', 'xp_per_level', 'kes_base', 'kes_per_level', 'is_active',
    ];

    protected $casts = [
        'career_fields' => 'array',
        'steps'         => 'array',
        'chain'         => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function quests()
    {
        return $this->hasMany(Quest::class, 'blueprint_id');
    }

    /** The level rungs this blueprint prints quests at: min → max every step. */
    public function slots(): array
    {
        $out  = [];
        $step = max(1, (int) $this->level_step);
        for ($l = (int) $this->level_min; $l <= (int) $this->level_max; $l += $step) {
            $out[] = $l;
        }
        return $out;
    }

    /**
     * Resolve a step's trigger value at a given level rung.
     * Returns null for none/any modes (engine treats empty value as "matches any").
     */
    public function valueFor(array $step, int $level): ?string
    {
        return match ($step['value_mode'] ?? 'none') {
            'fixed' => ($step['value_fixed'] ?? '') !== '' ? (string) $step['value_fixed'] : null,
            'curve' => (string) self::roundMoney(
                (int) ($step['value_base'] ?? 0) + (int) ($step['value_per_level'] ?? 0) * max(0, $level - 1)
            ),
            default => null, // 'none' | 'any'
        };
    }

    /** Round curve outputs to friendly figures (nearest 50 once past pocket money). */
    public static function roundMoney(int $n): int
    {
        return $n >= 100 ? (int) (round($n / 50) * 50) : $n;
    }
}
