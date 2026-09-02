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
        'mmf_principal', 'mmf_interest_earned', 'mmf_interest_taxed',
        'mmf_last_interest_tick', 'mmf_pending_topup_amount', 'mmf_topup_ready_tick',
        'mmf_pending_withdrawal_amount', 'mmf_withdrawal_ready_tick',
    ];

    protected $casts = [
        'purchase_price'   => 'integer',
        'current_value'    => 'integer',
        'quantity'         => 'integer',
        'purchased_at_tick'=> 'integer',
        'mmf_principal'                 => 'integer',
        'mmf_interest_earned'           => 'integer',
        'mmf_interest_taxed'            => 'integer',
        'mmf_pending_topup_amount'      => 'integer',
        'mmf_pending_withdrawal_amount' => 'integer',
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

    /** The game-day tick this holding matures on, or null if it never matures. */
    public function maturesAtTick(): ?int
    {
        if (!$this->asset || !$this->asset->hasMaturity()) return null;
        return (int) $this->purchased_at_tick + (int) $this->asset->maturity_ticks;
    }

    public function isMatured(int $nowTick): bool
    {
        $maturesAt = $this->maturesAtTick();
        return $maturesAt !== null && $nowTick >= $maturesAt;
    }

    public function ticksUntilMaturity(int $nowTick): ?int
    {
        $maturesAt = $this->maturesAtTick();
        return $maturesAt === null ? null : max(0, $maturesAt - $nowTick);
    }

    /** Whether the player is blocked from selling this before it matures. */
    public function isLockedForSale(int $nowTick): bool
    {
        return ($this->asset->locked ?? false) && !$this->isMatured($nowTick);
    }

    /** True for a Money Market Fund position — drives the invest/top-up/
     *  withdraw flow (MmfController) instead of the generic buy/sell one. */
    public function isMmf(): bool
    {
        return ($this->asset->product_type ?? null) === 'money_market_fund';
    }

    /** Untaxed lifetime interest still sitting in the fund — the base the
     *  15% withholding tax is prorated against on withdrawal. */
    public function mmfUntaxedInterest(): int
    {
        return max(0, (int) ($this->mmf_interest_earned ?? 0) - (int) ($this->mmf_interest_taxed ?? 0));
    }
}
