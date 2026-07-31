<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDailyChallenge extends Model
{
    protected $fillable = [
        'user_id', 'challenge_id', 'date', 'progress', 'completed_at', 'claimed_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'completed_at' => 'datetime',
        'claimed_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(DailyChallenge::class, 'challenge_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }
}
