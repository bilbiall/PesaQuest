<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerDeal extends Model
{
    protected $fillable = [
        'user_id', 'deal_id', 'amount_invested',
        'resolve_at_tick', 'status', 'profit_loss', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(InvestmentDeal::class, 'deal_id');
    }
}
