<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UploadsImages;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    use UploadsImages;

    public function index()
    {
        $badges = Badge::orderBy('trigger_value')->get();
        return view('admin.badges', compact('badges'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:60',
            'description'   => 'required|string|max:255',
            'icon'          => 'nullable|string|max:10',
            'image'         => 'nullable|image|mimes:png,jpg,jpeg,svg,gif,webp|max:2048',
            'color'         => 'nullable|string|max:20',
            'trigger_type'  => 'required|in:' . implode(',', array_keys(\App\Models\Badge::TRIGGER_TYPES)),
            'trigger_value' => 'required|integer|min:0',
            'is_active'     => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image_url'] = '/uploads/' . $this->resizeContain($request->file('image'), 'badges', 256, 256, 90);
        }

        // Map trigger_type → legacy fields for backward compat
        if ($data['trigger_type'] === 'level') {
            $data['required_level'] = $data['trigger_value'];
        } elseif ($data['trigger_type'] === 'points') {
            $data['required_points'] = $data['trigger_value'];
        }

        $badge = Badge::create($data);

        return response()->json(['success' => true, 'badge' => $badge]);
    }

    public function update(Request $request, Badge $badge)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:60',
            'description'   => 'required|string|max:255',
            'icon'          => 'nullable|string|max:10',
            'image'         => 'nullable|image|mimes:png,jpg,jpeg,svg,gif,webp|max:2048',
            'color'         => 'nullable|string|max:20',
            'trigger_type'  => 'required|in:' . implode(',', array_keys(\App\Models\Badge::TRIGGER_TYPES)),
            'trigger_value' => 'required|integer|min:0',
            'is_active'     => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($badge->image_url);
            $data['image_url'] = '/uploads/' . $this->resizeContain($request->file('image'), 'badges', 256, 256, 90);
        }

        if ($data['trigger_type'] === 'level') {
            $data['required_level'] = $data['trigger_value'];
        } elseif ($data['trigger_type'] === 'points') {
            $data['required_points'] = $data['trigger_value'];
        }

        $badge->update($data);

        return response()->json(['success' => true, 'badge' => $badge->fresh()]);
    }

    public function destroy(Badge $badge)
    {
        $this->deleteStoredImage($badge->image_url);
        $badge->delete();
        return response()->json(['success' => true]);
    }

    public function award(Request $request)
    {
        $data = $request->validate([
            'badge_id' => 'required|exists:badges,id',
            'user_id'  => 'required|exists:users,id',
        ]);

        $already = \App\Models\UserBadge::where('user_id', $data['user_id'])
            ->where('badge_id', $data['badge_id'])->exists();

        if (!$already) {
            \App\Models\UserBadge::create([
                'user_id'   => $data['user_id'],
                'badge_id'  => $data['badge_id'],
                'earned_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'already_had' => $already]);
    }

    public function revoke(Request $request)
    {
        $data = $request->validate([
            'badge_id' => 'required|exists:badges,id',
            'user_id'  => 'required|exists:users,id',
        ]);

        \App\Models\UserBadge::where('user_id', $data['user_id'])
            ->where('badge_id', $data['badge_id'])
            ->delete();

        return response()->json(['success' => true]);
    }
}
