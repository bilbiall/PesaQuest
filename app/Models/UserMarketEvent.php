<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMarketEvent extends Model
{
    protected $fillable = ['user_id', 'event_id', 'triggered_date'];

    protected $casts = ['triggered_date' => 'date'];

    public function user()  { return $this->belongsTo(User::class); }
    public function event() { return $this->belongsTo(MarketEvent::class, 'event_id'); }
}
