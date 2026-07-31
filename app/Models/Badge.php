<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'image_url',
        'required_level', 'required_points', 'color',
        'trigger_type', 'trigger_value', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'required_level' => 'integer',
        'required_points' => 'integer',
        'trigger_value' => 'integer',
    ];

    public const TRIGGER_TYPES = [
        'level'           => 'Reach Level X',
        'points'          => 'Earn X Total XP',
        'streak'          => 'X-Day Login Streak',
        'balance'         => 'Save KES X',
        'net_worth'       => 'Reach Net Worth KES X',
        'quest_complete'  => 'Complete X Quests',
        'course_complete' => 'Complete X Courses',
        'job_hired'       => 'Get Hired X Times',
        'asset_purchased' => 'Buy X Assets',
        'investment'      => 'Make X Investments',
        'forum_karma'     => 'Earn X Forum Karma (votes received)',
        'manual'          => 'Manually Awarded',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
