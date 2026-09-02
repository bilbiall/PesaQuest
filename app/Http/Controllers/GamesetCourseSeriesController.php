<?php

namespace App\Http\Controllers;

use App\Models\CourseSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GamesetCourseSeriesController extends Controller
{
    public function index()
    {
        $series = CourseSeries::withCount('courses')->orderBy('sort_order')->orderBy('title')->get();
        return view('gameset.course-series.index', compact('series'));
    }

    public function create()
    {
        return view('gameset.course-series.form', ['series' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title'] . '-' . Str::random(5));
        $series = CourseSeries::create($data);

        // Quest Factory: draft this series' completion quest (never blocks content creation)
        $drafted = null;
        try {
            $drafted = app(\App\Services\QuestFactory::class)->draftForSeries($series);
        } catch (\Throwable $e) { /* factory is best-effort */ }

        $note = $drafted
            ? ($drafted->is_active ? ' A quest for it was auto-published. 🤖' : ' A quest draft is waiting in Quests → review it. 🤖')
            : '';

        return redirect()->route('gameset.course-series.index')->with('success', "Series \"{$data['title']}\" created.{$note}");
    }

    public function edit(CourseSeries $series)
    {
        return view('gameset.course-series.form', ['series' => $series, 'mode' => 'edit']);
    }

    public function update(Request $request, CourseSeries $series)
    {
        $series->update($this->validated($request, $series));
        return redirect()->route('gameset.course-series.index')->with('success', "Series \"{$series->title}\" updated.");
    }

    public function destroy(CourseSeries $series)
    {
        $title = $series->title;
        $series->delete();
        return redirect()->route('gameset.course-series.index')->with('success', "Series \"{$title}\" deleted.");
    }

    public function toggleActive(CourseSeries $series)
    {
        $series->update(['is_active' => !$series->is_active]);
        return back()->with('success', "Series " . ($series->is_active ? 'activated' : 'deactivated') . ".");
    }

    private function validated(Request $request, ?CourseSeries $series = null): array
    {
        return $request->validate([
            'title'       => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
            'age_group'   => 'nullable|string|max:30',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'sometimes|boolean',
        ]);
    }
}
