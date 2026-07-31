<?php

namespace App\Http\Controllers;

use App\Models\DistrictPosition;
use Illuminate\Http\Request;

class GamesetWorldController extends Controller
{
    public function index()
    {
        return view('gameset.world.index', [
            'districts' => WorldController::districtMeta(),
            'positions' => DistrictPosition::allBySlug(),
        ]);
    }

    public function savePositions(Request $request)
    {
        $slugs = array_keys(WorldController::districtMeta());

        $data = $request->validate([
            'positions'              => 'required|array',
            'positions.*.slug'       => 'required|string|in:' . implode(',', $slugs),
            'positions.*.pos_left'   => 'required|numeric|min:0|max:100',
            'positions.*.pos_top'    => 'required|numeric|min:0|max:100',
            'positions.*.pos_width'  => 'required|numeric|min:1|max:100',
            'positions.*.pos_height' => 'required|numeric|min:1|max:100',
        ]);

        foreach ($data['positions'] as $row) {
            DistrictPosition::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'pos_left'   => $row['pos_left'],
                    'pos_top'    => $row['pos_top'],
                    'pos_width'  => $row['pos_width'],
                    'pos_height' => $row['pos_height'],
                ]
            );
        }

        return back()->with('success', count($data['positions']) . ' district position(s) saved — live immediately on the World Map.');
    }
}
