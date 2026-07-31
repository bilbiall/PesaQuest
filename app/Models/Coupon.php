<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'max_redemptions', 'redemptions_count',
        'plan_id', 'expires_at', 'is_active', 'note',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /** Why this coupon can't be used right now — null when it's valid. */
    public function invalidReason(?SubscriptionPlan $plan = null): ?string
    {
        if (!$this->is_active) {
            return 'This coupon is currently paused.';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This coupon has expired.';
        }
        if ($this->max_redemptions !== null && $this->redemptions_count >= $this->max_redemptions) {
            return 'This coupon has been fully redeemed.';
        }
        if ($this->plan_id && $plan && $this->plan_id !== $plan->id) {
            return 'This coupon is not valid for the selected plan.';
        }
        return null;
    }

    public function isValidFor(?SubscriptionPlan $plan = null): bool
    {
        return $this->invalidReason($plan) === null;
    }

    /** KES discount off a given price (never more than the price itself). */
    public function discountFor(int $priceKes): int
    {
        $discount = $this->type === 'percent'
            ? (int) round($priceKes * min(100, $this->value) / 100)
            : (int) $this->value;

        return min($priceKes, max(0, $discount));
    }

    public function label(): string
    {
        return $this->type === 'percent'
            ? "{$this->value}% off"
            : 'Ksh ' . number_format($this->value) . ' off';
    }

    public static function findByCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') return null;
        return static::where('code', $code)->first();
    }
}
