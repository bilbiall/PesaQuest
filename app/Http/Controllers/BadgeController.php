<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BadgeController extends Controller
{
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
            $path = $request->file('image')->store('badges', 'public');
            $data['image_url'] = asset('storage/' . $path);
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
            if ($badge->image_url && str_starts_with($badge->image_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $badge->image_url));
            }
            $path = $request->file('image')->store('badges', 'public');
            $data['image_url'] = '/storage/' . $path;
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
        if ($badge->image_url && str_starts_with($badge->image_url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $badge->image_url));
        }
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
