<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaMember extends Model
{
    protected $fillable = [
        'chama_id', 'user_id', 'role', 'total_contributed',
        'share_pct', 'joined_at', 'is_active',
    ];

    protected $casts = [
        'joined_at'  => 'datetime',
        'is_active'  => 'boolean',
        'share_pct'  => 'float',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isChairman(): bool
    {
        return $this->role === 'chairman';
    }
}
