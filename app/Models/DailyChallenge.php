<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyChallenge extends Model
{
    protected $fillable = [
        'age_group', 'title', 'description', 'challenge_type',
        'target_value', 'xp_bonus', 'active_date', 'is_active',
    ];

    protected $casts = [
        'active_date' => 'date',
        'is_active'   => 'boolean',
    ];

    public function userChallenges()
    {
        return $this->hasMany(UserDailyChallenge::class, 'challenge_id');
    }

    public function scopeActiveToday($query, string $ageGroup = null)
    {
        $today = now()->toDateString();
        $query->where('is_active', true)
              ->where(fn($q) => $q->whereNull('active_date')->orWhere('active_date', $today));
        if ($ageGroup) $query->where('age_group', $ageGroup);
        return $query;
    }
}
