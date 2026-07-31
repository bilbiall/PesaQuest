<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPriceHistory extends Model
{
    public $timestamps = false;
    protected $table = 'stock_price_history';

    protected $fillable = ['player_asset_id', 'tick', 'price', 'recorded_at'];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function playerAsset(): BelongsTo
    {
        return $this->belongsTo(PlayerAsset::class);
    }
}
