<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerShareHolding extends Model
{
    protected $fillable = ['user_id', 'share_id', 'quantity', 'avg_cost'];

    protected $casts = [
        'quantity' => 'integer',
        'avg_cost' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class);
    }

    public function currentValue(): int
    {
        return (int) round($this->quantity * ($this->share->current_price ?? 0));
    }

    public function gainLoss(): int
    {
        return $this->currentValue() - (int) round($this->quantity * $this->avg_cost);
    }

    public function gainLossPct(): float
    {
        if ($this->avg_cost <= 0) return 0;
        return round((($this->share->current_price - $this->avg_cost) / $this->avg_cost) * 100, 1);
    }

    /** The exact shape the "My Shares" list renders — shared by WorldController
     *  (initial page load) and ShareController (post-trade patch). */
    public function toHoldingPayload(): array
    {
        return [
            'share_id'      => $this->share_id,
            'name'          => $this->share->name,
            'symbol'        => $this->share->symbol,
            'icon'          => $this->share->icon,
            'quantity'      => $this->quantity,
            'avg_cost'      => (float) $this->avg_cost,
            'price'         => (float) $this->share->current_price,
            'sell_price'    => $this->share->sellPrice(),
            'value'         => $this->currentValue(),
            'gain_loss'     => $this->gainLoss(),
            'gain_loss_pct' => $this->gainLossPct(),
            'history'       => $this->share->recentHistory(),
            'direction'     => $this->share->priceChangeDirection(),
            'change_pct'    => $this->share->priceChangePct(),
        ];
    }
}
