<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaContribution extends Model
{
    protected $fillable = [
        'chama_id', 'user_id', 'amount', 'game_month', 'status',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
