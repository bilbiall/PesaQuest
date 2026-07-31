<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsDeposit extends Model
{
    protected $fillable = ['scheme_id', 'amount', 'note', 'type'];

    protected $casts = ['amount' => 'float'];

    public function scheme()
    {
        return $this->belongsTo(SavingsScheme::class, 'scheme_id');
    }
}
