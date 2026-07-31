<?php

namespace App\Http\Controllers;

use App\Models\SpinSegment;
use Illuminate\Http\Request;

class GamesetSpinController extends Controller
{
    public function index()
    {
        $segments = SpinSegment::orderBy('sort_order')->orderBy('id')->get();

        $totalWeight = max(1, $segments->where('is_active', true)->sum('weight'));
        $stats = [
            'total'   => $segments->count(),
            'active'  => $segments->where('is_active', true)->count(),
            'good'    => $segments->where('is_active', true)->whereIn('tier', ['good', 'great'])->sum('weight'),
            'bad'     => $segments->where('is_active', true)->where('tier', 'bad')->sum('weight'),
            'weight'  => $totalWeight,
        ];

        return view('gameset.spin.index', compact('segments', 'stats'));
    }

    public function store(Request $request)
    {
        SpinSegment::create($this->validateSegment($request));
        return redirect()->route('gameset.spin.index')->with('success', 'Wheel segment added.');
    }

    public function update(Request $request, SpinSegment $segment)
    {
        $segment->update($this->validateSegment($request));
        return redirect()->route('gameset.spin.index')->with('success', 'Wheel segment updated.');
    }

    public function destroy(SpinSegment $segment)
    {
        if (SpinSegment::where('is_active', true)->count() <= SpinSegment::MIN_SEGMENTS && $segment->is_active) {
            return redirect()->route('gameset.spin.index')
                ->with('success', 'Not deleted — the wheel needs at least ' . SpinSegment::MIN_SEGMENTS . ' active segments. Deactivate/add others first.');
        }
        $segment->delete();
        return redirect()->route('gameset.spin.index')->with('success', 'Wheel segment deleted.');
    }

    public function toggleActive(SpinSegment $segment)
    {
        // Never let the wheel drop below the minimum wedge count
        if ($segment->is_active && SpinSegment::where('is_active', true)->count() <= SpinSegment::MIN_SEGMENTS) {
            return response()->json(['success' => false, 'error' => 'The wheel needs at least ' . SpinSegment::MIN_SEGMENTS . ' active segments.'], 422);
        }
        $segment->update(['is_active' => !$segment->is_active]);
        return response()->json(['success' => true, 'is_active' => $segment->is_active]);
    }

    private function validateSegment(Request $request): array
    {
        return $request->validate([
            'label'      => 'required|string|max:40',
            'emoji'      => 'required|string|max:10',
            'color'      => 'required|string|max:12',
            'type'       => 'required|in:balance,credit,xp,salary_2x',
            'value'      => 'required|integer|min:-100000|max:1000000',
            'weight'     => 'required|integer|min:1|max:100',
            'tier'       => 'required|in:good,great,bad',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ]) + [
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
