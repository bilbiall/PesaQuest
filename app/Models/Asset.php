<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'name', 'slug', 'brand', 'category', 'tier', 'age_group',
        'base_price', 'monthly_income', 'monthly_cost', 'income_period_ticks',
        'income_description', 'cost_description',
        'appreciation_rate', 'volatility', 'risk_level',
        'icon', 'image_url', 'description', 'flavor_text', 'educational_note',
        'creates_bill_slug', 'max_per_player', 'is_active', 'is_luxury',
        'badge', 'featured_section',
        'maturity_ticks', 'locked', 'early_exit_penalty_pct', 'maturity_bonus_pct',
    ];

    /** Returns the best available image URL for this asset */
    public function imageUrl(): ?string
    {
        return $this->image_url ?: null;
    }

    protected $casts = [
        'is_active'          => 'boolean',
        'is_luxury'          => 'boolean',
        'appreciation_rate'  => 'float',
        'volatility'         => 'float',
        'locked'             => 'boolean',
        'early_exit_penalty_pct' => 'float',
        'maturity_bonus_pct' => 'float',
    ];

    /** Fixed-income instrument with a maturity date (T-Bill, T-Bond) vs. always-liquid (MMF, everything else). */
    public function hasMaturity(): bool
    {
        return (int) ($this->maturity_ticks ?? 0) > 0;
    }

    public function playerAssets(): HasMany
    {
        return $this->hasMany(PlayerAsset::class);
    }

    public function riskLabel(): string
    {
        return match($this->risk_level) {
            1 => 'Very Low',
            2 => 'Low',
            3 => 'Medium',
            4 => 'High',
            5 => 'Very High',
            default => 'Unknown',
        };
    }

    public function riskColor(): string
    {
        return match($this->risk_level) {
            1 => '#10b981',
            2 => '#60a5fa',
            3 => '#f59e0b',
            4 => '#fb923c',
            5 => '#f87171',
            default => '#9ca3af',
        };
    }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'vehicle'      => 'Vehicle',
            'property'     => 'Property',
            'business'     => 'Business',
            'investment'   => 'Investment',
            'gadget'       => 'Gadget',
            'fixed_income' => 'Fixed Income',
            default        => ucfirst($this->category),
        };
    }

    public function monthlyNetLabel(): string
    {
        $net = $this->monthly_income - $this->monthly_cost;
        if ($net > 0) return '+Ksh ' . number_format($net) . '/mo';
        if ($net < 0) return '-Ksh ' . number_format(abs($net)) . '/mo';
        return 'Break even';
    }

    public function badgeColor(): string
    {
        return match($this->badge) {
            'popular'  => '#f97316',
            'trending' => '#10b981',
            'new'      => '#8b5cf6',
            'stable'   => '#0ea5e9',
            'risky'    => '#ef4444',
            default    => '#6b7280',
        };
    }

    public function badgeLabel(): string
    {
        return match($this->badge) {
            'popular'  => 'POPULAR',
            'trending' => 'TRENDING',
            'new'      => 'NEW',
            'stable'   => 'STABLE',
            'risky'    => 'RISKY',
            default    => strtoupper($this->badge ?? ''),
        };
    }

    public function categoryEmoji(): string
    {
        return match($this->category) {
            'vehicle'      => '🚗',
            'property'     => '🏠',
            'business'     => '🏢',
            'investment'   => '📈',
            'gadget'       => '📱',
            'fixed_income' => '🏛️',
            default        => '📦',
        };
    }

    public function categoryChipColor(): string
    {
        return match($this->category) {
            'vehicle'      => 'rgba(59,130,246,0.18)',
            'property'     => 'rgba(16,185,129,0.18)',
            'business'     => 'rgba(249,115,22,0.18)',
            'investment'   => 'rgba(6,182,212,0.18)',
            'gadget'       => 'rgba(139,92,246,0.18)',
            'fixed_income' => 'rgba(45,212,191,0.18)',
            default        => 'rgba(107,114,128,0.18)',
        };
    }

    public function categoryTextColor(): string
    {
        return match($this->category) {
            'vehicle'      => '#93c5fd',
            'property'     => '#6ee7b7',
            'business'     => '#fdba74',
            'investment'   => '#67e8f9',
            'gadget'       => '#c4b5fd',
            'fixed_income' => '#5eead4',
            default        => '#9ca3af',
        };
    }

    public function categoryGradient(): string
    {
        return match($this->category) {
            'vehicle'      => 'linear-gradient(145deg,#1e3a8a,#1e40af,#0f172a)',
            'property'     => 'linear-gradient(145deg,#064e3b,#065f46,#0f172a)',
            'business'     => 'linear-gradient(145deg,#3730a3,#4c1d95,#1e1b4b)',
            'investment'   => 'linear-gradient(145deg,#0e7490,#0891b2,#0f172a)',
            'gadget'       => 'linear-gradient(145deg,#831843,#9d174d,#1a1025)',
            'fixed_income' => 'linear-gradient(145deg,#134e4a,#0f766e,#0f172a)',
            default        => 'linear-gradient(145deg,#1f2937,#374151,#111827)',
        };
    }

    public function featuredSectionLabel(): string
    {
        return match($this->featured_section) {
            'starter_moves'      => 'Starter Moves',
            'serious_money'      => 'Serious Money',
            'high_growth'        => 'High Growth',
            'dividend_builders'  => 'Dividend Builders',
            'lifestyle_upgrades' => 'Lifestyle Upgrades',
            default              => '',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAgeGroup($query, string $ageGroup)
    {
        return $query->where(function ($q) use ($ageGroup) {
            $q->where('age_group', $ageGroup)->orWhere('age_group', 'all');
        });
    }
}
