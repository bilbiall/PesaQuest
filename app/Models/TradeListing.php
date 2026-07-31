<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeListing extends Model
{
    protected $fillable = [
        'seller_id', 'player_asset_id', 'asking_price', 'status', 'buyer_id', 'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function seller()   { return $this->belongsTo(User::class, 'seller_id'); }
    public function buyer()    { return $this->belongsTo(User::class, 'buyer_id'); }
    public function playerAsset() { return $this->belongsTo(PlayerAsset::class); }

    public function scopeActive($query)  { return $query->where('status', 'active'); }
    public function scopeMine($query)    { return $query->where('seller_id', auth()->id()); }
}
