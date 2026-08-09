<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CityJob extends Model
{
    /** How long a freelance gig takes before it completes and pays out. */
    public const GIG_DURATION_TICKS = 7;

    /** Cooldown before the same freelance gig can be taken again (4 game weeks). */
    public const GIG_COOLDOWN_TICKS = 28;

    public const EMPLOYMENT_TYPES = [
        'full_time' => ['label' => 'Full-time', 'icon' => '🏢', 'hint' => 'One job only — demands all your time'],
        'part_time' => ['label' => 'Part-time', 'icon' => '⏰', 'hint' => 'Hold up to 2 at once'],
        'freelance' => ['label' => 'Freelance gig', 'icon' => '⚡', 'hint' => 'One-off payment, re-apply after cooldown'],
    ];

    /** The player age groups a job can be targeted at (mirrors users.age_group values). */
    public const AGE_GROUPS = [
        '8-12'  => ['label' => 'Kids (8–12)',        'icon' => '🧒'],
        '13-17' => ['label' => 'Teens (13–17)',      'icon' => '🧑‍🎓'],
        '18-25' => ['label' => 'Young Adults (18–25)', 'icon' => '🧑'],
        '26+'   => ['label' => 'Adults (26+)',       'icon' => '🧑‍💼'],
    ];

    protected $fillable = [
        'title', 'employer_name', 'employer_logo', 'description', 'career_track', 'career_tracks',
        'salary_kes_month', 'xp_reward', 'level', 'promotes_to_job_id', 'required_course_id', 'required_course_ids',
        'age_group', 'age_groups', 'is_active', 'is_part_time', 'employment_type', 'gig_cooldown_ticks',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'is_part_time'        => 'boolean',
        'required_course_ids' => 'array',
        'career_tracks'       => 'array',
        'age_groups'          => 'array',
    ];

    /** Every career track this job belongs to (multi-select, falling back to the single legacy column). */
    public function careerTrackList(): array
    {
        if (!empty($this->career_tracks)) {
            return array_values(array_unique($this->career_tracks));
        }
        return $this->career_track ? [$this->career_track] : [];
    }

    /** Whether this job belongs to the given career track. */
    public function matchesTrack(?string $track): bool
    {
        if (!$track) return false;
        return in_array($track, $this->careerTrackList(), true);
    }

    /**
     * Every age group this job is targeted at (multi-select, falling back to the
     * legacy free-text column). An empty list means the job is open to all ages.
     */
    public function ageGroupList(): array
    {
        if (!empty($this->age_groups)) {
            return array_values(array_intersect(array_unique($this->age_groups), array_keys(self::AGE_GROUPS)));
        }
        // Legacy single value — only honour it if it matches a known group
        // ("all", "18+" and other free text mean no restriction).
        if ($this->age_group && array_key_exists($this->age_group, self::AGE_GROUPS)) {
            return [$this->age_group];
        }
        return [];
    }

    /** Whether a player in the given age group can see/take this job. Empty target list = everyone. */
    public function matchesAgeGroup(?string $ageGroup): bool
    {
        $targets = $this->ageGroupList();
        if (empty($targets)) return true;
        return $ageGroup && in_array($ageGroup, $targets, true);
    }

    /** Freelance gig cooldown in ticks — per-job override, or the game default. */
    public function gigCooldownTicks(): int
    {
        return $this->gig_cooldown_ticks ?: self::GIG_COOLDOWN_TICKS;
    }

    /** Job type with legacy-boolean fallback for rows created before the column existed. */
    public function type(): string
    {
        return $this->employment_type ?: ($this->is_part_time ? 'part_time' : 'full_time');
    }

    public function typeLabel(): string
    {
        return self::EMPLOYMENT_TYPES[$this->type()]['label'] ?? ucfirst($this->type());
    }

    public function levelLabel(): string
    {
        return match ((int) $this->level) {
            1 => 'Entry Level',
            2 => 'Mid Level',
            3 => 'Senior Level',
            default => 'Level ' . $this->level,
        };
    }

    public function meetsRequirements(\Illuminate\Support\Collection $completedIds): bool
    {
        // Multi-course requirement (all must be completed)
        if (!empty($this->required_course_ids)) {
            foreach ($this->required_course_ids as $cid) {
                if (!$completedIds->contains((int) $cid)) return false;
            }
            return true;
        }
        // Single course requirement (legacy)
        if ($this->required_course_id) {
            return $completedIds->contains((int) $this->required_course_id);
        }
        return true;
    }

    public function requiredCourse(): BelongsTo
    {
        return $this->belongsTo(CityCourse::class, 'required_course_id');
    }

    /** Every required course ID (multi-course list, falling back to the single legacy FK). */
    public function requiredCourseIdList(): array
    {
        if (!empty($this->required_course_ids)) {
            return array_values(array_unique(array_map('intval', $this->required_course_ids)));
        }
        return $this->required_course_id ? [(int) $this->required_course_id] : [];
    }

    /** Full course rows (id/title/icon) for every required course, in gate order. */
    public function requiredCourses(): \Illuminate\Support\Collection
    {
        $ids = $this->requiredCourseIdList();
        if (empty($ids)) return collect();

        $courses = CityCourse::whereIn('id', $ids)->get(['id', 'title', 'icon'])->keyBy('id');
        return collect($ids)->map(fn ($id) => $courses->get($id))->filter()->values();
    }

    public function playerJobs(): HasMany
    {
        return $this->hasMany(PlayerCityJob::class);
    }

    /** Admin-curated explicit promotion target — the automatic same-track/next-level
     *  match in LifeSimulator::findNextTierJob() only kicks in when this is unset. */
    public function promotesTo(): BelongsTo
    {
        return $this->belongsTo(CityJob::class, 'promotes_to_job_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
