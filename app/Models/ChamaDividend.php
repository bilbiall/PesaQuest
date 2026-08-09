<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaDividend extends Model
{
    protected $fillable = ['chama_id', 'member_id', 'amount', 'choice', 'declared_at', 'resolved_at'];

    protected $casts = [
        'declared_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(ChamaMember::class, 'member_id');
    }

    public function isPending(): bool
    {
        return $this->choice === null;
    }
}
