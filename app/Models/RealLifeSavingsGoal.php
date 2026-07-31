<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RealLifeSavingsGoal extends Model
{
    protected $fillable = [
        'user_id', 'name', 'icon', 'target_amount', 'target_date', 'status', 'completed_at',
    ];

    protected $casts = [
        'target_date'  => 'date',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(RealLifeSavingsDeposit::class, 'goal_id')->orderByDesc('deposited_on');
    }

    public function totalSaved(): int
    {
        return (int) $this->deposits()->sum('amount');
    }

    public function progressPct(): int
    {
        if ($this->target_amount <= 0) return 0;
        return (int) min(100, round($this->totalSaved() / $this->target_amount * 100));
    }

    /** Marks the goal completed the moment total deposits reach the target — called after every new deposit. */
    public function refreshCompletionState(): void
    {
        if ($this->status === 'active' && $this->totalSaved() >= $this->target_amount) {
            $this->update(['status' => 'completed', 'completed_at' => now()]);
        }
    }
}
