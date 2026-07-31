<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaAsset extends Model
{
    protected $fillable = [
        'chama_id', 'asset_id', 'purchase_price', 'quantity', 'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function monthlyIncome(): int
    {
        return (int) (($this->asset->monthly_income ?? 0) * $this->quantity);
    }

    public function currentValue(): int
    {
        return (int) (($this->asset->base_price ?? $this->purchase_price) * $this->quantity);
    }
}
