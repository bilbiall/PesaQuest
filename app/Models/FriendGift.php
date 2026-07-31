<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FriendGift extends Model
{
    /** Minimum player level before gifting unlocks — matches friend loans. */
    public const MIN_LEVEL = 3;

    /** A sender may gift at most this share of their cash in one go. */
    public const MAX_GIFT_SHARE = 0.20;

    /** Gifts a player may send per real day — a soft brake on account-to-account farming. */
    public const DAILY_LIMIT = 5;

    protected $fillable = ['sender_id', 'recipient_id', 'amount', 'message'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public static function sentTodayCount(int $userId): int
    {
        return static::where('sender_id', $userId)->whereDate('created_at', now()->toDateString())->count();
    }
}
