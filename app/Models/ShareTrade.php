<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareTrade extends Model
{
    protected $fillable = ['user_id', 'share_id', 'action', 'quantity', 'price', 'total', 'profit_loss'];

    protected $casts = [
        'quantity'    => 'integer',
        'price'       => 'float',
        'total'       => 'float',
        'profit_loss' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class);
    }
}
