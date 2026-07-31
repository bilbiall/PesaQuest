<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcadeSession extends Model
{
    protected $fillable = [
        'arcade_game_id', 'arcade_match_id', 'user_id', 'is_bot', 'turn_order', 'missed_turns', 'stake_amount', 'pot_amount',
        'position', 'status', 'last_roll', 'last_event', 'session_assets',
        'xp_awarded', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'is_bot'         => 'boolean',
        'last_event'     => 'array',
        'session_assets' => 'array',
        'started_at'     => 'datetime',
        'ended_at'       => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(ArcadeGame::class, 'arcade_game_id');
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(ArcadeMatch::class, 'arcade_match_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
