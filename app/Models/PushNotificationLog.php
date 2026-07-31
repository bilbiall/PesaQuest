<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One row per push actually delivered to a player — powers the daily cap. */
class PushNotificationLog extends Model
{
    protected $fillable = ['user_id', 'category', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
