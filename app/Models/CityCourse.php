<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CityCourse extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'content', 'icon', 'career_track', 'color',
        'cost_kes', 'is_free', 'duration_hours', 'difficulty', 'xp_reward',
        'outcome', 'financial_tip', 'jobs_intro', 'age_group', 'is_active',
    ];

    protected $casts = [
        'is_free'   => 'boolean',
        'is_active' => 'boolean',
    ];

    public function playerCourses(): HasMany
    {
        return $this->hasMany(PlayerCityCourse::class);
    }

    public function cityJobs(): HasMany
    {
        return $this->hasMany(CityJob::class, 'required_course_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
