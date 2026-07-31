<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan', 'plan_id', 'status', 'starts_at', 'ends_at', 'paused_at',
        'payment_reference', 'payment_method', 'amount_paid',
        'coupon_code', 'discount_kes',
        'mpesa_checkout_request_id', 'mpesa_receipt', 'approved_by',
    ];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'paused_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    public function subscriptionPlan() { return $this->belongsTo(SubscriptionPlan::class, 'plan_id'); }

    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    /** Genuinely in effect right now — has started, hasn't ended, isn't paused. */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
            ($this->starts_at === null || $this->starts_at->isPast()) &&
            ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /** Paid for and scheduled, but its start date hasn't arrived yet (stacked renewal). */
    public function isUpcoming(): bool
    {
        return $this->status === 'active' && $this->starts_at !== null && $this->starts_at->isFuture();
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /** Admin pause: freezes the countdown. Refuses to double-pause or pause a non-active sub. */
    public function pause(): void
    {
        if (!$this->isActive()) return;
        $this->update(['status' => 'paused', 'paused_at' => now()]);
    }

    /** Admin resume: shifts ends_at forward by exactly how long it was paused — no days lost. */
    public function resume(): void
    {
        if (!$this->isPaused() || !$this->paused_at) return;

        $pausedDuration = $this->paused_at->diffInSeconds(now());
        $this->update([
            'status'    => 'active',
            'ends_at'   => $this->ends_at ? $this->ends_at->copy()->addSeconds($pausedDuration) : null,
            'paused_at' => null,
        ]);
    }
}
