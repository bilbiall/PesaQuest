<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSeries extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'icon', 'color', 'age_group', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(CityCourse::class, 'series_id')
            ->orderByRaw('topic_number IS NULL, topic_number')
            ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * The topics THIS player actually sees — age-group variants of the same
     * topic_number all live in the series, but a player only ever encounters
     * their own age's row, so progress must be scoped to it too (otherwise
     * "3 of 9" would read as "3 of 36" once 4 age variants exist per topic).
     */
    private function coursesFor(User $user)
    {
        return $this->courses()->where('is_active', true)->get()
            ->filter(fn (CityCourse $c) => $c->matchesAgeGroup($user->age_group ?? null));
    }

    /** Completed-vs-total course count for this user, for a progress chip. */
    public function progressFor(User $user): array
    {
        $courses = $this->coursesFor($user);
        $total   = $courses->count();
        $completed = $total > 0
            ? PlayerCityCourse::where('user_id', $user->id)
                ->whereIn('city_course_id', $courses->pluck('id'))
                ->where('status', 'completed')
                ->count()
            : 0;

        return ['completed' => $completed, 'total' => $total];
    }

    /** True once every active course in this series is completed by the user. */
    public function isCompletedBy(User $user): bool
    {
        $progress = $this->progressFor($user);
        return $progress['total'] > 0 && $progress['completed'] >= $progress['total'];
    }
}
