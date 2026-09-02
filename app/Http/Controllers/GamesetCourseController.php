<?php

namespace App\Http\Controllers;

use App\Models\CityCourse;
use App\Models\CityJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GamesetCourseController extends Controller
{
    public function index()
    {
        $courses = CityCourse::with('series')->orderBy('career_track')->orderBy('title')->get();
        return view('gameset.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('gameset.courses.form', ['course' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title'] . '-' . Str::random(5));
        $course = CityCourse::create($data);

        // Quest Factory: draft this course's quest (never blocks content creation)
        $drafted = null;
        try {
            $drafted = app(\App\Services\QuestFactory::class)->draftForCourse($course);
        } catch (\Throwable $e) { /* factory is best-effort */ }

        $note = $drafted
            ? ($drafted->is_active ? ' A quest for it was auto-published. 🤖' : ' A quest draft is waiting in Quests → review it. 🤖')
            : '';

        return redirect()->route('gameset.courses.index')->with('success', "Course \"{$data['title']}\" created.{$note}");
    }

    public function edit(CityCourse $course)
    {
        return view('gameset.courses.form', ['course' => $course, 'mode' => 'edit']);
    }

    public function update(Request $request, CityCourse $course)
    {
        $course->update($this->validated($request, $course));
        return redirect()->route('gameset.courses.index')->with('success', "Course \"{$course->title}\" updated.");
    }

    public function destroy(CityCourse $course)
    {
        $title = $course->title;
        $course->delete();
        return redirect()->route('gameset.courses.index')->with('success', "Course \"{$title}\" deleted.");
    }

    public function toggleActive(CityCourse $course)
    {
        $course->update(['is_active' => !$course->is_active]);
        return back()->with('success', "Course " . ($course->is_active ? 'activated' : 'deactivated') . ".");
    }

    private function validated(Request $request, ?CityCourse $course = null): array
    {
        $data = $request->validate([
            'title'          => 'required|string|max:120',
            'description'    => 'required|string|max:500',
            'content'        => 'nullable|string|max:8000',
            'icon'           => 'nullable|string|max:10',
            'career_track'   => 'required|in:tech,business,finance,creative',
            'color'          => 'nullable|string|max:20',
            'cost_kes'       => 'nullable|integer|min:0',
            'is_free'        => 'sometimes|boolean',
            'duration_hours' => 'nullable|integer|min:1|max:200',
            'difficulty'     => 'nullable|in:beginner,intermediate,advanced',
            'xp_reward'      => 'nullable|integer|min:0',
            'outcome'        => 'required|string|max:200',
            'financial_tip'  => 'nullable|string|max:2000',
            'jobs_intro'     => 'nullable|string|max:300',
            'age_group'      => 'nullable|in:' . implode(',', array_keys(CityJob::AGE_GROUPS)),
            'is_active'      => 'sometimes|boolean',
            'series_id'      => 'nullable|exists:course_series,id',
            'sort_order'     => 'nullable|integer|min:0',
            'topic_number'   => 'nullable|integer|min:1',
        ]);
        $data['series_id']    = $request->input('series_id') !== '' ? $request->input('series_id') : null;
        $data['topic_number'] = $request->input('topic_number') !== '' ? $request->input('topic_number') : null;
        return $data;
    }
}
