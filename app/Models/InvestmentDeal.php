<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentDeal extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'icon',
        'cost', 'min_return_pct', 'max_return_pct', 'loss_pct',
        'success_probability', 'maturity_ticks', 'risk_level',
        'lesson', 'is_active', 'sort_order', 'created_by',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'success_probability'  => 'float',
        'min_return_pct'       => 'float',
        'max_return_pct'       => 'float',
        'loss_pct'             => 'float',
    ];

    public function playerDeals(): HasMany
    {
        return $this->hasMany(PlayerDeal::class, 'deal_id');
    }

    public function riskLabel(): string
    {
        return match($this->risk_level) {
            1 => 'Very Low', 2 => 'Low', 3 => 'Medium', 4 => 'High', 5 => 'Very High',
            default => 'Medium',
        };
    }

    public function riskColor(): string
    {
        return match($this->risk_level) {
            1 => '#10b981', 2 => '#60a5fa', 3 => '#f59e0b', 4 => '#fb923c', 5 => '#f87171',
            default => '#f59e0b',
        };
    }
}
