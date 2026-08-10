<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaDeal extends Model
{
    protected $fillable = [
        'chama_id', 'deal_id', 'amount_invested', 'resolve_at',
        'status', 'profit_loss', 'resolved_at',
    ];

    protected $casts = [
        'amount_invested' => 'integer',
        'profit_loss'     => 'integer',
        'resolve_at'      => 'datetime',
        'resolved_at'     => 'datetime',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(InvestmentDeal::class, 'deal_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
