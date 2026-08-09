<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Share extends Model
{
    protected $fillable = [
        'name', 'symbol', 'icon', 'sector',
        'current_price', 'previous_price', 'price_history', 'min_price', 'max_price',
        'volatility', 'drift', 'last_event_reason', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'current_price'  => 'float',
        'previous_price' => 'float',
        'price_history'  => 'array',
        'min_price'      => 'float',
        'max_price'      => 'float',
        'volatility'     => 'float',
        'drift'          => 'float',
        'is_active'      => 'boolean',
    ];

    public function holdings(): HasMany
    {
        return $this->hasMany(PlayerShareHolding::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(ShareTrade::class);
    }

    public function priceChangePct(): float
    {
        if (!$this->previous_price) return 0;
        return round((($this->current_price - $this->previous_price) / $this->previous_price) * 100, 2);
    }

    public function priceChangeDirection(): string
    {
        if (!$this->previous_price || $this->current_price == $this->previous_price) return 'flat';
        return $this->current_price > $this->previous_price ? 'up' : 'down';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Recent prices, oldest first, for a sparkline — falls back to a flat
     *  single-point line for a brand-new share with no history yet. */
    public function recentHistory(): array
    {
        $history = $this->price_history ?? [];
        return empty($history) ? [$this->current_price] : $history;
    }

    /** Derived purely from volatility so it's always honest — no admin field
     *  to fall out of sync with the number that actually drives price swings. */
    public function riskLabel(): string
    {
        return match (true) {
            $this->volatility <= 0.025 => 'Blue-chip — steady',
            $this->volatility <= 0.045 => 'Balanced — moderate swings',
            $this->volatility <= 0.065 => 'Growth — noticeable swings',
            default                    => 'High-risk — big swings',
        };
    }

    public function riskColor(): string
    {
        return match (true) {
            $this->volatility <= 0.025 => '#34d399',
            $this->volatility <= 0.045 => '#60a5fa',
            $this->volatility <= 0.065 => '#fbbf24',
            default                    => '#f87171',
        };
    }

    /** Bid-ask spread as a fraction of price — wider for wilder shares, same
     *  real-world pattern where illiquid/volatile assets cost more to trade. */
    public function spreadPct(): float
    {
        return max(0.005, min(0.02, $this->volatility * 0.25));
    }

    public function buyPrice(): float
    {
        return round($this->current_price * (1 + $this->spreadPct() / 2), 2);
    }

    public function sellPrice(): float
    {
        return round($this->current_price * (1 - $this->spreadPct() / 2), 2);
    }
}
