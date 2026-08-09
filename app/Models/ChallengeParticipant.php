<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeParticipant extends Model
{
    protected $fillable = [
        'challenge_id', 'user_id', 'team_id', 'chama_id', 'status', 'baseline',
        'progress', 'baseline_2', 'progress_2', 'rank', 'is_winner', 'stake_paid', 'joined_at',
    ];

    protected $casts = [
        'progress'   => 'float',
        'progress_2' => 'float',
        'is_winner'  => 'boolean',
        'stake_paid' => 'boolean',
        'joined_at'  => 'datetime',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }
}
