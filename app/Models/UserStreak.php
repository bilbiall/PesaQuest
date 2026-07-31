<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStreak extends Model
{
    protected $fillable = ['user_id', 'current_streak', 'longest_streak', 'last_activity_date', 'bonus_points_earned'];
    protected $casts = ['last_activity_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }

    public function recordActivity(): bool
    {
        // Day boundaries in the players' local timezone, not UTC — with the app
        // clock on UTC, "a day" rolled over at 3am Nairobi time, so playing on
        // three consecutive local days (e.g. late night, then morning) could
        // land in non-adjacent UTC dates and wrongly reset the streak.
        $tz    = 'Africa/Nairobi';
        $today = now($tz)->toDateString();
        if ($this->last_activity_date?->toDateString() === $today) return false;

        $yesterday = now($tz)->subDay()->toDateString();
        if ($this->last_activity_date?->toDateString() === $yesterday) {
            $this->current_streak += 1;
        } else {
            $this->current_streak = 1;
        }

        $this->longest_streak = max($this->longest_streak, $this->current_streak);
        $this->last_activity_date = $today;
        $this->save();
        return true;
    }
}
