<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Share extends Model
{
    protected $fillable = [
        'name', 'symbol', 'icon', 'sector',
        'current_price', 'previous_price', 'min_price', 'max_price',
        'volatility', 'drift', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'current_price'  => 'float',
        'previous_price' => 'float',
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
}
