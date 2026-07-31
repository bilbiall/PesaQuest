<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsScheme extends Model
{
    protected $fillable = [
        'user_id', 'name', 'target_amount', 'current_amount',
        'interest_earned', 'last_interest_tick',
        'emoji', 'color', 'is_archived',
    ];

    protected $casts = [
        'target_amount'   => 'float',
        'current_amount'  => 'float',
        'interest_earned' => 'integer',
        'is_archived'     => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposits()
    {
        return $this->hasMany(SavingsDeposit::class, 'scheme_id')->latest();
    }

    public function progressPercent(): float
    {
        if ($this->target_amount <= 0) return 0;
        return min(100, round($this->current_amount / $this->target_amount * 100, 1));
    }
}
