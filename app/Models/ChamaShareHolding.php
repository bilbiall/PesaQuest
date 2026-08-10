<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaShareHolding extends Model
{
    protected $fillable = ['chama_id', 'share_id', 'quantity', 'avg_cost'];

    protected $casts = [
        'quantity' => 'integer',
        'avg_cost' => 'float',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class);
    }

    public function currentValue(): float
    {
        return (float) ($this->share?->current_price ?? $this->avg_cost) * $this->quantity;
    }

    public function unrealizedGain(): float
    {
        return $this->currentValue() - ($this->avg_cost * $this->quantity);
    }
}
