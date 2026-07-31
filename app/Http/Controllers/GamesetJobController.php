<?php

namespace App\Http\Controllers;

use App\Models\CityJob;
use App\Models\CityCourse;
use Illuminate\Http\Request;

class GamesetJobController extends Controller
{
    public function index()
    {
        $jobs = CityJob::orderBy('career_track')->orderBy('level')->get();
        return view('gameset.jobs.index', compact('jobs'));
    }

    public function create()
    {
        $courses = CityCourse::active()->orderBy('title')->get(['id', 'title', 'career_track', 'icon']);
        return view('gameset.jobs.form', ['job' => null, 'mode' => 'create', 'courses' => $courses, 'billBurden' => $this->billBurdenPerAgeGroup()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $job = CityJob::create($data);

        // Quest Factory: draft the "study → get hired" quest chain (never blocks content creation)
        $drafted = null;
        try {
            $drafted = app(\App\Services\QuestFactory::class)->draftForJob($job);
        } catch (\Throwable $e) { /* factory is best-effort */ }

        $note = $drafted
            ? ($drafted->is_active ? ' A quest for it was auto-published. 🤖' : ' A quest draft is waiting in Quests → review it. 🤖')
            : '';

        return redirect()->route('gameset.jobs.index')->with('success', "Job \"{$data['title']}\" created.{$note}");
    }

    public function edit(CityJob $job)
    {
        $courses = CityCourse::active()->orderBy('title')->get(['id', 'title', 'career_track', 'icon']);
        return view('gameset.jobs.form', ['job' => $job, 'mode' => 'edit', 'courses' => $courses, 'billBurden' => $this->billBurdenPerAgeGroup()]);
    }

    public function update(Request $request, CityJob $job)
    {
        $job->update($this->validated($request));
        return redirect()->route('gameset.jobs.index')->with('success', "Job \"{$job->title}\" updated.");
    }

    public function destroy(CityJob $job)
    {
        $title = $job->title;
        $job->delete();
        return redirect()->route('gameset.jobs.index')->with('success', "Job \"{$title}\" deleted.");
    }

    public function toggleActive(CityJob $job)
    {
        $job->update(['is_active' => !$job->is_active]);
        return back()->with('success', "Job " . ($job->is_active ? 'activated' : 'deactivated') . ".");
    }

    /**
     * Approximate monthly bill burden (KES per 30 game days) for each age group,
     * shown in the job form so salaries can be set to leave players a livable
     * margin after bills. Includes each group's own bills plus "all"-audience bills.
     */
    private function billBurdenPerAgeGroup(): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('bills')) {
            return [];
        }

        $bills = \App\Models\Bill::where('is_active', true)->get(['age_group', 'amount', 'frequency_ticks']);

        $burden = [];
        foreach (array_keys(CityJob::AGE_GROUPS) as $group) {
            $burden[$group] = (int) round(
                $bills->filter(fn ($b) => $b->age_group === $group || $b->age_group === 'all' || $b->age_group === null)
                      ->sum(fn ($b) => $b->amount * (30 / max(1, (int) $b->frequency_ticks)))
            );
        }
        return $burden;
    }

    private function validated(Request $request): array
    {
        $validTracks = array_column(\App\Services\CareerService::tracks(), 'key');

        $data = $request->validate([
            'title'                  => 'required|string|max:120',
            'employer_name'          => 'required|string|max:100',
            'employer_logo'          => 'nullable|string|max:10',
            'career_tracks'          => 'required|array|min:1',
            'career_tracks.*'        => 'in:' . implode(',', $validTracks),
            'employment_type'        => 'required|in:full_time,part_time,freelance',
            'salary_kes_month'       => 'required|integer|min:100',
            'gig_cooldown_ticks'     => 'nullable|integer|min:1|max:365',
            'level'                  => 'required|integer|in:1,2,3',
            'required_course_ids'    => 'nullable|array',
            'required_course_ids.*'  => 'integer|exists:city_courses,id',
            'age_groups'             => 'nullable|array',
            'age_groups.*'           => 'in:' . implode(',', array_keys(CityJob::AGE_GROUPS)),
            'is_active'              => 'sometimes|boolean',
        ]);

        // Keep the legacy boolean in sync for old code paths
        $data['is_part_time'] = $data['employment_type'] === 'part_time';

        // Cooldown override only makes sense for freelance gigs — ignore it otherwise
        // so a stale value left in the form doesn't silently apply if the type is switched.
        $data['gig_cooldown_ticks'] = $data['employment_type'] === 'freelance' ? ($data['gig_cooldown_ticks'] ?? null) : null;

        // Normalize the multi-track selection: dedupe, store the full list plus
        // the first as the legacy single column (older code that only reads
        // career_track still gets a sane value; careerTrackList()/matchesTrack()
        // always consult the full array).
        $tracks = array_values(array_unique($data['career_tracks']));
        $data['career_tracks'] = $tracks;
        $data['career_track']  = $tracks[0];

        // Normalize the multi-course selection: dedupe, cast to int, store the
        // full list plus the first as the legacy single FK (older code that
        // only reads required_course_id still gates correctly on the first
        // prerequisite; meetsRequirements() and requiredCourses() always
        // consult the full array).
        $ids = array_values(array_unique(array_map('intval', $data['required_course_ids'] ?? [])));
        $data['required_course_ids'] = !empty($ids) ? $ids : null;
        $data['required_course_id']  = $ids[0] ?? null;

        // Normalize the age-group multi-select: none (or all) checked means the
        // job is open to every age. Keep the legacy string column in sync so
        // older code that only reads age_group still gets a sane value.
        $ages = array_values(array_unique($data['age_groups'] ?? []));
        $openToAll = empty($ages) || count($ages) === count(CityJob::AGE_GROUPS);
        $data['age_groups'] = $openToAll ? null : $ages;
        $data['age_group']  = $openToAll ? 'all' : $ages[0];

        return $data;
    }
}
