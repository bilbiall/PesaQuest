<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArcadeGame extends Model
{
    protected $fillable = [
        'slug', 'name', 'tile_count', 'floor_percent', 'finish_bonus_percent', 'is_active',
        'xp_per_play', 'xp_per_win',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tiles(): HasMany
    {
        return $this->hasMany(ArcadeTile::class)->orderBy('number');
    }

    public function mysteryOutcomes(): HasMany
    {
        return $this->hasMany(ArcadeMysteryOutcome::class);
    }

    public function flavorTexts(): HasMany
    {
        return $this->hasMany(ArcadeFlavorText::class);
    }

    public function stakeTiers(): HasMany
    {
        return $this->hasMany(ArcadeStakeTier::class)->orderBy('level_min');
    }
}
