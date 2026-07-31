<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcadeFlavorText extends Model
{
    protected $fillable = [
        'arcade_game_id', 'category', 'text', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(ArcadeGame::class, 'arcade_game_id');
    }

    /** A random active line for this category, or null if the pool is empty
     *  (caller falls back to a hardcoded default rather than showing nothing). */
    public static function randomFor(int $gameId, string $category): ?string
    {
        return static::where('arcade_game_id', $gameId)
            ->where('category', $category)
            ->where('is_active', true)
            ->inRandomOrder()
            ->value('text');
    }
}
