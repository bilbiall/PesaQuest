<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameNotification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'icon', 'data', 'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        // Every in-game notification is automatically offered to Web Push —
        // PushService decides eligibility (preferences, quiet hours, daily cap,
        // minors policy) and the service worker decides whether to actually pop
        // it (only when the player isn't already looking at the game).
        static::created(function (GameNotification $notification) {
            app(\App\Services\PushService::class)->mirror($notification);
        });
    }
}
