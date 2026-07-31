<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A player's own Needs/Wants/Savings split for Bajeti Smart — defaults to 50/30/20. */
class UserBudgetRatio extends Model
{
    protected $fillable = ['user_id', 'needs_pct', 'wants_pct', 'savings_pct'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            ['needs_pct' => 50, 'wants_pct' => 30, 'savings_pct' => 20]
        );
    }
}
