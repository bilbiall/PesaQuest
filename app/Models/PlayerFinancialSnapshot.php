<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerFinancialSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'tick', 'balance', 'net_worth', 'recorded_at'];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The closest snapshot at-or-before the given tick, or null if the
     *  player has no history reaching back that far yet. */
    public static function asOf(int $userId, int $tick): ?self
    {
        return self::where('user_id', $userId)
            ->where('tick', '<=', $tick)
            ->orderByDesc('tick')
            ->first();
    }
}
