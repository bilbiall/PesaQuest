<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerCityJob extends Model
{
    protected $fillable = [
        'user_id', 'city_job_id', 'status', 'xp_awarded', 'employment_type',
        'started_at', 'ended_at', 'ticks_employed',
        'pending_salary', 'unpaid_ticks', 'gig_ends_tick', 'cooldown_until_tick',
        'missed_paydays', 'removal_warned_at_tick',
        'salary_multiplier', 'ticks_employed_at_last_review', 'promotions_count', 'title_bumps',
        'promotion_disqualified', 'miss_incidents', 'promotion_probation_until_tick',
    ];

    protected $casts = [
        'started_at'              => 'datetime',
        'ended_at'                => 'datetime',
        'salary_multiplier'       => 'float',
        'promotion_disqualified'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(CityJob::class, 'city_job_id');
    }

    /** Base job salary scaled by whatever raises this player has earned at this
     *  role — the job template's own salary_kes_month never changes, so every
     *  place that pays or displays this player's salary must read this instead. */
    public function effectiveSalary(): int
    {
        return (int) round(($this->job->salary_kes_month ?? 0) * ($this->salary_multiplier ?: 1.0));
    }

    /** The job template's level (1-3) plus any in-place title bumps earned
     *  when tenure had nowhere real to promote into, capped at 3 (Senior) —
     *  the ceiling of the level system. */
    public function effectiveLevel(): int
    {
        return min(3, (int) ($this->job->level ?? 1) + (int) $this->title_bumps);
    }

    /** Job title adjusted for any in-place bumps — swaps a recognizable
     *  Junior/Entry/Mid prefix for the new tier, or prepends one if the base
     *  title has none. Only changes for display; city_job_id never moves
     *  unless a real next-tier job exists to promote into. */
    public function displayTitle(): string
    {
        $base = $this->job->title ?? 'Employee';
        if ($this->title_bumps <= 0) return $base;

        $stripped = preg_replace('/^(Junior|Entry-Level|Entry|Mid-Level|Mid|Senior)\s+/i', '', $base);
        $prefix = match ($this->effectiveLevel()) {
            3       => 'Senior ',
            2       => 'Mid-Level ',
            default => '',
        };
        return $prefix . $stripped;
    }
}
