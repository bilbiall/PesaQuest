<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ArcadeMatch extends Model
{
    protected $fillable = [
        'arcade_game_id', 'created_by', 'name', 'join_code', 'visibility', 'max_players', 'status',
        'turn_mode', 'current_turn_session_id', 'turn_started_at',
        'mode', 'stake_amount', 'forfeit_pool_amount',
    ];

    protected $casts = [
        'turn_started_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(ArcadeGame::class, 'arcade_game_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ArcadeSession::class);
    }

    public function currentTurnSession(): BelongsTo
    {
        return $this->belongsTo(ArcadeSession::class, 'current_turn_session_id');
    }

    public function isTurnBased(): bool
    {
        return $this->turn_mode === 'turns';
    }

    public function isWager(): bool
    {
        return $this->mode === 'wager';
    }

    public function invites(): HasMany
    {
        return $this->hasMany(ArcadeMatchInvite::class);
    }

    public static function generateJoinCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (self::where('join_code', $code)->exists());

        return $code;
    }
}
