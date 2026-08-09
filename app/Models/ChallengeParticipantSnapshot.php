<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeParticipantSnapshot extends Model
{
    protected $fillable = ['challenge_participant_id', 'rank', 'progress', 'snapshot_date'];

    protected $casts = [
        'progress'      => 'float',
        'snapshot_date' => 'date',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(ChallengeParticipant::class, 'challenge_participant_id');
    }
}
