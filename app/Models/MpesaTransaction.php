<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'user_id', 'subscription_plan_id', 'checkout_request_id', 'merchant_request_id',
        'phone', 'amount', 'status', 'mpesa_receipt', 'callback_data', 'failure_reason', 'completed_at',
        'school_name', 'coupon_id', 'discount_kes',
    ];

    protected $casts = [
        'callback_data' => 'array',
        'completed_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }
}
