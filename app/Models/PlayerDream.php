<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerDream extends Model
{
    protected $fillable = ['user_id', 'dream_id', 'price_paid', 'purchased_at'];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dream(): BelongsTo
    {
        return $this->belongsTo(Dream::class);
    }
}
