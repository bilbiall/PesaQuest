<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealLifeSavingsDeposit extends Model
{
    protected $fillable = ['goal_id', 'amount', 'note', 'deposited_on'];

    protected $casts = ['deposited_on' => 'date'];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(RealLifeSavingsGoal::class, 'goal_id');
    }
}
