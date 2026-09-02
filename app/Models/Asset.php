<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'name', 'slug', 'brand', 'category', 'product_type', 'tier', 'age_group',
        'base_price', 'monthly_income', 'monthly_cost', 'income_period_ticks',
        'income_description', 'cost_description',
        'appreciation_rate', 'volatility', 'risk_level',
        'icon', 'image_url', 'description', 'flavor_text', 'educational_note',
        'creates_bill_slug', 'max_per_player', 'is_active', 'is_luxury',
        'badge', 'featured_section', 'mmf_sponsor_id', 'rate_updated_at',
        'maturity_ticks', 'locked', 'early_exit_penalty_pct', 'maturity_bonus_pct',
        'mmf_min_rate', 'mmf_max_rate',
    ];

    /** Annual rate band this MMF fluctuates within — falls back to the
     *  global admin-tunable Setting when the fund doesn't set its own,
     *  so every sponsor can keep a distinct feel (see MmfSponsorSeeder). */
    public function mmfRateBand(): array
    {
        return [
            (float) ($this->mmf_min_rate ?? \App\Models\Setting::get('mmf_min_rate_annual', 8)),
            (float) ($this->mmf_max_rate ?? \App\Models\Setting::get('mmf_max_rate_annual', 16)),
        ];
    }

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
        'mmf_min_rate'       => 'float',
        'mmf_max_rate'       => 'float',
        'locked'             => 'boolean',
        'early_exit_penalty_pct' => 'float',
        'maturity_bonus_pct' => 'float',
        'rate_updated_at'    => 'datetime',
    ];

    public function mmfSponsor(): BelongsTo
    {
        return $this->belongsTo(MmfSponsor::class);
    }

    /** Human label for the fixed_income sub-classification — plain string
     *  column (not an enum) so new product types are cheap to add; anything
     *  not in this list just gets title-cased rather than failing. */
    public function productTypeLabel(): string
    {
        return match ($this->product_type) {
            'money_market_fund' => 'Money Market Fund',
            'treasury_bill'     => 'Treasury Bill',
            'treasury_bond'     => 'Treasury Bond',
            'fixed_deposit'     => 'Fixed Deposit',
            'corporate_bond'    => 'Corporate Bond',
            'sacco_deposit'     => 'SACCO Deposit',
            'endowment'         => 'Endowment Plan',
            'sukuk'             => 'Sukuk Bond',
            null, ''            => '',
            default             => ucwords(str_replace('_', ' ', $this->product_type)),
        };
    }

    /** True once a rate hasn't been reviewed in a while — only meaningful
     *  for sponsor-branded products, where a stale number reads as wrong
     *  rather than merely simulated. Admin surfaces this as a nag. */
    public function rateIsStale(int $days = 30): bool
    {
        if (!$this->mmf_sponsor_id) return false;
        if (!$this->rate_updated_at) return true;
        return $this->rate_updated_at->diffInDays(now()) >= $days;
    }

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

    /**
     * Whether this asset is a growth play, an income play, or holds steady —
     * derived purely from appreciation_rate, the actual number that drives
     * value drift in LifeSimulator::settleAssets(). Condition/maintenance
     * never factors into value, only income — this badge exists so a player
     * can tell the two apart before buying, instead of only discovering
     * "this was never going to grow" months later from a shrinking WORTH NOW.
     */
    public function appreciationLabel(): string
    {
        return match (true) {
            $this->appreciation_rate > 0 => 'Growth Asset',
            $this->appreciation_rate < 0 => 'Depreciating',
            default                      => 'Stable Value',
        };
    }

    public function appreciationIcon(): string
    {
        return match (true) {
            $this->appreciation_rate > 0 => '📈',
            $this->appreciation_rate < 0 => '📉',
            default                      => '➡️',
        };
    }

    public function appreciationColor(): string
    {
        return match (true) {
            $this->appreciation_rate > 0 => '#34d399',
            $this->appreciation_rate < 0 => '#fb923c',
            default                      => '#60a5fa',
        };
    }

    /** Plain-language explainer — condition/maintenance never changes this;
     *  it only ever protects income, never value. */
    public function appreciationNote(): string
    {
        return match (true) {
            $this->appreciation_rate > 0 => 'Tends to gain value over time — maintaining it keeps the income flowing, but the growth here is the real prize.',
            $this->appreciation_rate < 0 => "Tends to lose value over time regardless of maintenance — maintenance only protects the income. Good for cash flow, not for building net worth.",
            default                      => "Value tends to hold roughly steady — maintenance protects the income; don't expect this one to grow your net worth on its own.",
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
