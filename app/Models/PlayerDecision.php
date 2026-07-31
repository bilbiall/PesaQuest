<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerDecision extends Model
{
    protected $fillable = [
        'user_id', 'decision_id', 'choice_id',
        'balance_before', 'balance_after', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(LifeDecision::class, 'decision_id');
    }

    public function choice(): BelongsTo
    {
        return $this->belongsTo(LifeDecisionChoice::class, 'choice_id');
    }

    public function isPending(): bool
    {
        return $this->choice_id === null;
    }

    public function isResolved(): bool
    {
        return $this->choice_id !== null;
    }
}
