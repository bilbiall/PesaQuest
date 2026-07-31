<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealLifeBillPayment extends Model
{
    protected $fillable = ['user_id', 'real_life_bill_id', 'bill_name', 'amount', 'paid_on'];

    protected $casts = ['paid_on' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(RealLifeBill::class, 'real_life_bill_id');
    }
}
