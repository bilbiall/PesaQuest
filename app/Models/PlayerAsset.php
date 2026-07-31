<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerAsset extends Model
{
    protected $fillable = [
        'user_id', 'asset_id', 'purchase_price', 'current_value',
        'quantity', 'purchased_at_tick', 'last_valued_tick',
        'income_paid_to_tick', 'upkeep_paid_to_tick',
        'status', 'sold_at_tick', 'sold_price', 'condition',
    ];

    protected $casts = [
        'purchase_price'   => 'integer',
        'current_value'    => 'integer',
        'quantity'         => 'integer',
        'purchased_at_tick'=> 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** Unrealised gain/loss vs purchase price */
    public function gainLoss(): int
    {
        return $this->current_value - $this->purchase_price;
    }

    public function gainLossPct(): float
    {
        if ($this->purchase_price === 0) return 0;
        return round(($this->gainLoss() / $this->purchase_price) * 100, 1);
    }

    /** Effective income multiplier based on condition (0-100). */
    public function conditionFactor(): float
    {
        $c = $this->condition ?? 100;
        return match(true) {
            $c >= 70 => 1.0,
            $c >= 40 => 0.7,
            $c >= 20 => 0.4,
            default  => 0.0,
        };
    }

    /** Monthly net cash flow factoring in condition. */
    public function monthlyCashFlow(): int
    {
        $income = (int) round(($this->asset->monthly_income ?? 0) * $this->quantity * $this->conditionFactor());
        $cost   = ($this->asset->monthly_cost ?? 0) * $this->quantity;
        return $income - $cost;
    }

    public function conditionLabel(): string
    {
        $c = $this->condition ?? 100;
        return match(true) {
            $c >= 80 => 'Excellent',
            $c >= 60 => 'Good',
            $c >= 40 => 'Fair',
            $c >= 20 => 'Poor',
            default  => 'Broken',
        };
    }

    public function conditionColor(): string
    {
        $c = $this->condition ?? 100;
        return match(true) {
            $c >= 80 => '#10b981',
            $c >= 60 => '#22c55e',
            $c >= 40 => '#eab308',
            $c >= 20 => '#f97316',
            default  => '#ef4444',
        };
    }

    /** Maintenance cost to restore 40 condition points. */
    public function maintenanceCost(): int
    {
        $base = $this->asset->monthly_cost ?? 0;
        return max(500, $base > 0 ? $base * 3 : (int)($this->purchase_price * 0.04));
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
