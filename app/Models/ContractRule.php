<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractRule extends Model
{
    protected $fillable = [
        'age_group', 'level_min', 'level_max', 'objectives_min', 'objectives_max',
        'completion_mode', 'required_count', 'duration_days', 'active_contracts',
        'reward_xp', 'reward_kes', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * The rule that governs a player: exact age-group match beats 'all',
     * then the narrowest matching level band wins.
     */
    public static function for(?string $ageGroup, int $level): ?self
    {
        $rules = static::where('is_active', true)
            ->where('level_min', '<=', $level)
            ->where('level_max', '>=', $level)
            ->whereIn('age_group', array_filter([$ageGroup, 'all']))
            ->get();

        return $rules
            ->sortBy(fn ($r) => [($r->age_group === 'all' ? 1 : 0), $r->level_max - $r->level_min])
            ->first();
    }
}
