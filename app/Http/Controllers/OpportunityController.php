<?php

namespace App\Http\Controllers;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\GameNotification;
use App\Models\Mission;
use App\Models\PlayerCityCourse;
use App\Models\PlayerCityJob;
use App\Models\PlayerMission;
use App\Services\MissionChecker;
use App\Services\QuestTriggerService;
use Illuminate\Http\JsonResponse;

class OpportunityController extends Controller
{
    /** Course/job track recommended for the player's chosen career path (null if no path). */
    private function recommendedTrack($user): ?string
    {
        return \App\Services\CareerService::trackForField($user->progress?->career_field);
    }

    public function index()
    {
        $user = auth()->user();
        $recommendedTrack = $this->recommendedTrack($user);
        $ageGroup = $user->age_group ?? '18-25';

        $courses = CityCourse::active()->with('series')->orderBy('career_track')->orderBy('title')->get()
            ->filter(fn (CityCourse $c) => $c->matchesAgeGroup($ageGroup))->values();
        $jobs    = CityJob::active()->orderBy('salary_kes_month')->get()
            ->filter(fn ($j) => $j->matchesAgeGroup($ageGroup))->values();

        // Series progress chips ("2/4 complete") for every series with an active course
        $seriesProgress = $courses->pluck('series')->filter()->unique('id')
            ->mapWithKeys(fn ($s) => [$s->id => $s->progressFor($user)]);

        $completedIds = PlayerCityCourse::where('user_id', $user->id)->where('status', 'completed')->pluck('city_course_id');
        $enrolledIds  = PlayerCityCourse::where('user_id', $user->id)->where('status', 'enrolled')->pluck('city_course_id');
        $employedId   = PlayerCityJob::where('user_id', $user->id)->where('status', 'employed')->value('city_job_id');

        $tracks = \App\Services\CareerService::tracksByKey();

        // Learning Path: series-linked topics only, in strict ladder order,
        // annotated with lock state for this player specifically.
        $learningPath = \App\Models\CourseSeries::active()->orderBy('sort_order')->get()
            ->map(function ($series) use ($user, $completedIds, $ageGroup) {
                $topics = $series->courses()->where('is_active', true)->get()
                    ->filter(fn (CityCourse $c) => $c->matchesAgeGroup($ageGroup))
                    ->values()
                    ->map(fn (CityCourse $c) => [
                        'course'    => $c,
                        'completed' => $completedIds->contains($c->id),
                        'locked'    => $c->isLockedFor($user),
                    ]);
                return ['series' => $series, 'progress' => $series->progressFor($user), 'topics' => $topics];
            })
            ->filter(fn ($group) => $group['topics']->isNotEmpty())
            ->values();

        return view('opportunities.index', compact('user', 'courses', 'jobs', 'completedIds', 'enrolledIds', 'employedId', 'tracks', 'recommendedTrack', 'seriesProgress', 'learningPath'));
    }

    public function courses(): JsonResponse
    {
        $user    = auth()->user();
        $recommendedTrack = $this->recommendedTrack($user);

        $courses = CityCourse::active()->with('series')->orderBy('career_track')->get()
            ->filter(fn (CityCourse $c) => $c->matchesAgeGroup($user->age_group ?? '18-25'))
            ->sortByDesc(fn ($c) => $c->career_track === $recommendedTrack)
            ->values();

        $playerMap = PlayerCityCourse::where('user_id', $user->id)
            ->get()
            ->keyBy('city_course_id');

        return response()->json(
            $courses->map(fn ($c) => [
                'recommended'    => $c->career_track === $recommendedTrack,
                'id'             => $c->id,
                'title'          => $c->title,
                'description'    => $c->description,
                'intro_content'  => $c->intro_content,
                'content'        => $c->content,
                'icon'           => $c->icon,
                'career_track'   => $c->career_track,
                'color'          => $c->color,
                'duration_hours' => $c->duration_hours,
                'difficulty'     => $c->difficulty ?? 'beginner',
                'xp_reward'      => $c->xp_reward ?? 50,
                'outcome'        => $c->outcome,
                'financial_tip'  => $c->financial_tip,
                'jobs_intro'     => $c->jobs_intro,
                'is_free'        => $c->is_free,
                'cost_kes'       => $c->cost_kes ?? 0,
                'player_status'  => $playerMap->get($c->id)?->status ?? 'not_enrolled',
                'series_title'   => $c->series?->title,
                'series_icon'    => $c->series?->icon,
                'topic_number'   => $c->topic_number,
                'is_locked'      => $c->isLockedFor($user),
            ])
        );
    }

    public function enroll(int $id): JsonResponse
    {
        $user   = auth()->user();
        $course = CityCourse::active()->findOrFail($id);

        // Already completed
        $existing = PlayerCityCourse::where('user_id', $user->id)
            ->where('city_course_id', $id)->first();
        if ($existing?->status === 'completed') {
            return response()->json(['status' => 'completed', 'already' => true]);
        }

        // Ladder gate — topic-numbered courses must be taken in order
        if ($course->isLockedFor($user)) {
            $prev = $course->previousTopic($user->age_group ?? null);
            return response()->json([
                'error' => $prev ? "Complete \"{$prev->title}\" first." : 'This topic is locked until an earlier one is completed.',
            ], 422);
        }

        // KES gate — deduct balance if paid course
        $progress = null;
        if (!$course->is_free && $course->cost_kes > 0) {
            $progress = $user->progress ?? $user->getOrCreateProgress();
            if (($progress->balance ?? 0) < $course->cost_kes) {
                return response()->json([
                    'error' => 'You need KES ' . number_format($course->cost_kes) . ' to enroll. Earn more in Pesa City first!',
                ], 422);
            }
            $progress->decrement('balance', $course->cost_kes);
        }

        // Enroll only — player reads the content and completes separately
        PlayerCityCourse::updateOrCreate(
            ['user_id' => $user->id, 'city_course_id' => $id],
            ['status' => 'enrolled', 'enrolled_at' => now()]
        );

        return response()->json([
            'status' => 'enrolled',
            'course' => ['title' => $course->title, 'icon' => $course->icon],
        ]);
    }

    public function complete(int $id): JsonResponse
    {
        $user   = auth()->user();
        $course = CityCourse::active()->findOrFail($id);

        $pc = PlayerCityCourse::where('user_id', $user->id)
            ->where('city_course_id', $id)
            ->firstOrFail();

        if ($pc->status === 'completed') {
            return response()->json(['status' => 'completed', 'already' => true]);
        }

        if ($pc->status !== 'enrolled') {
            return response()->json(['error' => 'Enroll in this course before completing it.'], 422);
        }

        $pc->update(['status' => 'completed', 'completed_at' => now()]);

        // Award XP
        $xpAwarded = 0;
        if ($course->xp_reward > 0) {
            $progress = $user->progress ?? $user->getOrCreateProgress();
            $progress->addPoints($course->xp_reward);
            $xpAwarded = $course->xp_reward;
        }

        // Jobs this course unlocks or contributes to (multi-course jobs need
        // ALL their required courses — flag whether more are still needed).
        $completedIdsFresh = PlayerCityCourse::where('user_id', $user->id)->where('status', 'completed')->pluck('city_course_id');
        $jobsUnlocked = CityJob::active()
            ->where(fn ($q) => $q->where('required_course_id', $id)
                ->orWhereJsonContains('required_course_ids', $id))
            ->get(['id', 'title', 'employer_name', 'employer_logo', 'salary_kes_month', 'required_course_id', 'required_course_ids', 'age_group', 'age_groups'])
            ->filter(fn ($j) => $j->matchesAgeGroup($user->age_group ?? '18-25'))
            ->map(fn ($j) => [
                'title'            => $j->title,
                'employer_name'    => $j->employer_name,
                'employer_logo'    => $j->employer_logo,
                'salary_kes_month' => $j->salary_kes_month,
                'fully_unlocked'   => $j->meetsRequirements($completedIdsFresh),
            ])->values();

        $missionResult = $this->tryMissionCheck($user, 'course_completed');

        // Quest auto-trigger: fire for this specific course slug
        app(QuestTriggerService::class)->fire($user, 'take_course', ['slug' => $course->slug ?? '']);

        // If this completion finished the course's whole series, fire that too
        if ($course->series_id && $course->series && $course->series->isCompletedBy($user)) {
            app(QuestTriggerService::class)->fire($user, 'complete_series', ['series_slug' => $course->series->slug]);
        }

        return response()->json([
            'status'         => 'completed',
            'xp_awarded'     => $xpAwarded,
            'course'         => [
                'title'         => $course->title,
                'icon'          => $course->icon,
                'color'         => $course->color,
                'career_track'  => $course->career_track,
                'financial_tip' => $course->financial_tip,
                'jobs_intro'    => $course->jobs_intro,
                'xp_reward'     => $course->xp_reward ?? 50,
            ],
            'jobs_unlocked'  => $jobsUnlocked,
            'mission_result' => $missionResult,
        ]);
    }

    public function jobs(): JsonResponse
    {
        $user = auth()->user();

        $completedIds = PlayerCityCourse::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('city_course_id');

        $playerJobs = PlayerCityJob::where('user_id', $user->id)->get()->keyBy('city_job_id');
        $employedIds = $playerJobs->where('status', 'employed')->keys();

        $currentTick = (int) ($user->progress?->tick_count ?? 0);

        $jobs = CityJob::active()
            ->orderBy('salary_kes_month')
            ->get()
            ->filter(fn ($j) => $j->matchesAgeGroup($user->age_group ?? '18-25'))
            ->values();

        // Bulk-preload every referenced course in one query (avoids N+1 across
        // the whole job board, since a job may now require several courses).
        $allCourseIds = $jobs->flatMap(fn ($j) => $j->requiredCourseIdList())->unique()->values();
        $coursesById  = \App\Models\CityCourse::whereIn('id', $allCourseIds)->get(['id', 'title', 'icon'])->keyBy('id');

        $recommendedTrack = $this->recommendedTrack($user);

        return response()->json(
            $jobs->map(function ($j) use ($recommendedTrack, $completedIds, $employedIds, $playerJobs, $currentTick, $coursesById) {
                $mine     = $playerJobs->get($j->id);
                $cooldown = ($j->type() === 'freelance' && $mine && $mine->cooldown_until_tick && $mine->cooldown_until_tick > $currentTick)
                    ? (int) ($mine->cooldown_until_tick - $currentTick)
                    : 0;

                $requiredCourses = collect($j->requiredCourseIdList())
                    ->map(fn ($cid) => $coursesById->get($cid))
                    ->filter()
                    ->map(fn ($c) => ['id' => $c->id, 'title' => $c->title, 'icon' => $c->icon, 'done' => $completedIds->contains($c->id)])
                    ->values();

                return [
                    'id'              => $j->id,
                    'recommended'     => $j->matchesTrack($recommendedTrack),
                    'title'           => $j->title,
                    'employer_name'   => $j->employer_name,
                    'employer_logo'   => $j->employer_logo,
                    'description'     => $j->description,
                    'career_track'    => $j->career_track,
                    'career_tracks'   => $j->careerTrackList(),
                    'level'           => $j->level,
                    'level_label'     => $j->levelLabel(),
                    'salary_kes_month'=> $j->salary_kes_month,
                    'xp_reward'       => $j->xp_reward ?? 100,
                    'is_part_time'    => (bool) $j->is_part_time,
                    'employment_type' => $j->type(),
                    'type_label'      => $j->typeLabel(),
                    'is_gig'          => $j->type() === 'freelance',
                    'gig_pay_once'    => $j->type() === 'freelance',
                    'cooldown_days'   => $cooldown,
                    'required_courses'=> $requiredCourses,
                    // Kept for any older cached frontend code — mirrors the first required course
                    'required_course' => $requiredCourses->first(),
                    'has_requirement' => $j->meetsRequirements($completedIds),
                    'is_employed'     => $employedIds->contains($j->id),
                ];
            })
        );
    }

    public function apply(int $id): JsonResponse
    {
        $user = auth()->user();
        $job  = CityJob::active()->findOrFail($id);

        // Age-group gate — mirrors the listing filter so a stale/cached job card can't be applied to
        if (!$job->matchesAgeGroup($user->age_group ?? '18-25')) {
            return response()->json(['error' => 'This job is not available for your age group.'], 422);
        }

        // Qualification gate
        $completedIds = PlayerCityCourse::where('user_id', $user->id)->where('status', 'completed')->pluck('city_course_id');
        if (!$job->meetsRequirements($completedIds)) {
            return response()->json(['error' => 'Complete the required course(s) first.'], 422);
        }

        $progress       = $user->progress ?? $user->getOrCreateProgress();
        $currentTick    = (int) ($progress->tick_count ?? 0);
        $employmentType = $job->type();

        $employed = PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->get();

        // Already doing this exact job/gig?
        if ($employed->contains('city_job_id', $id)) {
            return response()->json(['error' => 'You already hold this position.'], 422);
        }

        // A full-time contract blocks everything else
        if ($employed->contains('employment_type', 'full_time')) {
            return response()->json(['error' => 'Your full-time contract takes all your working hours. Resign it before taking any other work.'], 422);
        }

        if ($employmentType === 'full_time' && $employed->isNotEmpty()) {
            return response()->json(['error' => 'Full-time roles demand full commitment — resign your other job(s) and gigs first.'], 422);
        }

        if ($employmentType === 'part_time'
            && $employed->where('employment_type', 'part_time')->count() >= 2) {
            return response()->json(['error' => 'You already hold 2 part-time jobs (the maximum). Resign one first.'], 422);
        }

        if ($employmentType === 'freelance') {
            if ($employed->where('employment_type', 'freelance')->count() >= 3) {
                return response()->json(['error' => 'You already have 3 gigs running. Deliver those first.'], 422);
            }
            // Repeat-gig cooldown: 4 game weeks after the last gig completed
            $previous = PlayerCityJob::where('user_id', $user->id)
                ->where('city_job_id', $id)
                ->first();
            if ($previous && $previous->cooldown_until_tick && $previous->cooldown_until_tick > $currentTick) {
                $daysLeft = $previous->cooldown_until_tick - $currentTick;
                return response()->json(['error' => "You just delivered this gig — the client will have new work in {$daysLeft} game day(s)."], 422);
            }
        }

        $playerJob = PlayerCityJob::updateOrCreate(
            ['user_id' => $user->id, 'city_job_id' => $id],
            [
                'status'              => 'employed',
                'xp_awarded'          => false,
                'employment_type'     => $employmentType,
                'started_at'          => now(),
                'pending_salary'      => 0,
                'unpaid_ticks'        => 0,
                'gig_ends_tick'       => $employmentType === 'freelance' ? $currentTick + CityJob::GIG_DURATION_TICKS : null,
                'cooldown_until_tick' => null,
            ]
        );

        // Award XP for getting hired
        $xpReward = $job->xp_reward ?? 100;
        if ($xpReward > 0 && !$playerJob->xp_awarded) {
            $progress->addPoints($xpReward);
            $playerJob->update(['xp_awarded' => true]);
        }

        // A full-time hire becomes the player's career title (salary itself is paid
        // per job by LifeSimulator::settleJobSalaries — never via career_income_rate)
        if ($employmentType === 'full_time') {
            $progress->update(['career_title' => $job->title]);
        }

        // Create a pending "you're hired!" notification shown next login
        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'job_hired',
            'title'   => $employmentType === 'freelance' ? '⚡ Gig Landed!' : '🎉 You\'re Hired!',
            'body'    => $employmentType === 'freelance'
                ? "{$job->employer_name} gave you the \"{$job->title}\" gig. Deliver in " . CityJob::GIG_DURATION_TICKS . ' game days, then Report to Work to collect KES ' . number_format($job->salary_kes_month) . '.'
                : "Welcome to {$job->employer_name}! Your role as {$job->title} ({$job->typeLabel()}) starts now. Salary: KES " . number_format($job->salary_kes_month) . '/month — report to work to collect each payday.',
            'icon'    => $job->employer_logo ?? '💼',
            'is_read' => false,
            'data'    => ['job_id' => $job->id, 'xp' => $xpReward, 'employer' => $job->employer_name, 'title' => $job->title, 'salary' => $job->salary_kes_month, 'employment_type' => $employmentType],
        ]);

        // Trigger mission check + quest auto-trigger
        $result = $this->tryMissionCheck($user, 'job_employed');
        app(QuestTriggerService::class)->fire($user, 'get_job', ['slug' => (string)$job->id, 'id' => (string)$job->id]);

        return response()->json([
            'status'          => 'employed',
            'xp_awarded'      => $xpReward,
            'employment_type' => $employmentType,
            'mission_result'  => $result,
        ]);
    }

    public function resign(int $id): JsonResponse
    {
        $user = auth()->user();
        $playerJob = PlayerCityJob::where('user_id', $user->id)
            ->where('city_job_id', $id)
            ->where('status', 'employed')
            ->firstOrFail();

        $playerJob->update(['status' => 'resigned', 'ended_at' => now()]);

        // Salaries are paid per employed city job by LifeSimulator::settleJobSalaries —
        // career_income_rate must stay untouched or the legacy payer double-pays.
        $remaining = PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->with('job:id,title')
            ->get();

        // Career title follows the remaining full-time job (or clears)
        $progress = $user->progress ?? $user->getOrCreateProgress();
        $fullTime = $remaining->firstWhere('employment_type', 'full_time');
        $progress->update(['career_title' => $fullTime?->job?->title]);

        return response()->json(['status' => 'resigned', 'remaining_jobs' => $remaining->count()]);
    }

    // ── Internal helpers ──────────────────────────────────────────────────────

    private function tryMissionCheck($user, string $requirementType): ?array
    {
        $mission = Mission::active()
            ->where('is_active', true)
            ->get()
            ->first(fn ($m) => ($m->requirements['type'] ?? '') === $requirementType);

        if (!$mission) return null;

        $pm = PlayerMission::where('user_id', $user->id)
            ->where('mission_id', $mission->id)
            ->where('status', 'active')
            ->first();

        if (!$pm) return null;

        $checker = app(MissionChecker::class);

        if ($checker->check($user, $mission)) {
            return $checker->award($user, $mission);
        }

        return null;
    }
}
