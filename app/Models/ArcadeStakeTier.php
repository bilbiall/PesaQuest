<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcadeStakeTier extends Model
{
    protected $fillable = [
        'arcade_game_id', 'label', 'level_min', 'level_max', 'stake_amount', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(ArcadeGame::class, 'arcade_game_id');
    }

    public static function forLevel(int $gameId, int $level): ?self
    {
        return self::where('arcade_game_id', $gameId)
            ->where('is_active', true)
            ->where('level_min', '<=', $level)
            ->where('level_max', '>=', $level)
            ->orderByDesc('level_min')
            ->first();
    }
}
