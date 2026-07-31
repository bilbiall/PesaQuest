<?php

namespace App\Http\Controllers;

use App\Models\FunWorldActivity;
use Illuminate\Http\Request;

class GamesetFunWorldController extends Controller
{
    public function index()
    {
        $activities = FunWorldActivity::orderBy('sort_order')->orderBy('name')->get();

        $stats = [
            'total'    => $activities->count(),
            'active'   => $activities->where('is_active', true)->count(),
            'cheapest' => $activities->min('price') ?? 0,
            'priciest' => $activities->max('price') ?? 0,
        ];

        return view('gameset.fun-world.index', compact('activities', 'stats'));
    }

    public function store(Request $request)
    {
        FunWorldActivity::create($this->validateActivity($request));

        return redirect()->route('gameset.fun-world.index')->with('success', 'Activity created.');
    }

    public function update(Request $request, FunWorldActivity $activity)
    {
        $activity->update($this->validateActivity($request));

        return redirect()->route('gameset.fun-world.index')->with('success', 'Activity updated.');
    }

    public function destroy(FunWorldActivity $activity)
    {
        $activity->delete();

        return redirect()->route('gameset.fun-world.index')->with('success', 'Activity deleted.');
    }

    public function toggleActive(FunWorldActivity $activity)
    {
        $activity->update(['is_active' => !$activity->is_active]);

        return response()->json(['success' => true, 'is_active' => $activity->is_active]);
    }

    private function validateActivity(Request $request): array
    {
        return $request->validate([
            'name'            => 'required|string|max:80',
            'icon'            => 'required|string|max:8',
            'description'     => 'nullable|string|max:200',
            'price'           => 'required|integer|min:1|max:1000000',
            'mood_boost_base' => 'required|integer|min:1|max:25',
            'xp_reward'       => 'required|integer|min:0|max:1000',
            'sort_order'      => 'nullable|integer|min:0|max:999',
            'is_active'       => 'nullable|boolean',
        ]) + ['is_active' => $request->boolean('is_active', true), 'sort_order' => (int) $request->input('sort_order', 0)];
    }
}
