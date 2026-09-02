<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CityCourse extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'content', 'icon', 'career_track', 'color',
        'cost_kes', 'is_free', 'duration_hours', 'difficulty', 'xp_reward',
        'outcome', 'financial_tip', 'jobs_intro', 'age_group', 'is_active',
        'series_id', 'sort_order', 'topic_number',
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

    public function series(): BelongsTo
    {
        return $this->belongsTo(CourseSeries::class, 'series_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Whether a player in the given age group can see/take this course. Blank/'all' = everyone. */
    public function matchesAgeGroup(?string $ageGroup): bool
    {
        if (!$this->age_group || !array_key_exists($this->age_group, CityJob::AGE_GROUPS)) {
            return true;
        }
        return $ageGroup === $this->age_group;
    }

    /**
     * The course one rung down this ladder for a given player's age group —
     * age-group variants of the same topic all share topic_number, so this
     * finds THIS player's version of "the previous topic," not just any row.
     */
    public function previousTopic(?string $ageGroup): ?self
    {
        if (!$this->topic_number || $this->topic_number <= 1) return null;

        return static::active()
            ->where('topic_number', $this->topic_number - 1)
            ->get()
            ->first(fn (self $c) => $c->matchesAgeGroup($ageGroup));
    }

    /** True if this topic is gated behind an earlier, not-yet-completed topic. */
    public function isLockedFor(User $user): bool
    {
        $prev = $this->previousTopic($user->age_group ?? null);
        if (!$prev) return false; // topic 1, no ladder, or no matching prior variant authored yet

        return !PlayerCityCourse::where('user_id', $user->id)
            ->where('city_course_id', $prev->id)
            ->where('status', 'completed')
            ->exists();
    }
}
