<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaVote extends Model
{
    protected $fillable = [
        'proposal_id', 'user_id', 'vote', 'cast_at',
    ];

    protected $casts = [
        'cast_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ChamaProposal::class, 'proposal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
