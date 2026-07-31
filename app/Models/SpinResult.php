<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpinResult extends Model
{
    protected $fillable = [
        'user_id', 'prize_label', 'prize_type', 'prize_value',
        'prize_emoji', 'prize_tier', 'balance_before', 'balance_after', 'segment_index',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function canSpinToday(int $userId): bool
    {
        return !static::where('user_id', $userId)
            ->where('created_at', '>=', now()->startOfDay())
            ->exists();
    }

    public static function nextSpinAt(int $userId): ?\Carbon\Carbon
    {
        $last = static::where('user_id', $userId)->latest()->first();
        if (!$last) return null;
        return $last->created_at->startOfDay()->addDay();
    }
}
