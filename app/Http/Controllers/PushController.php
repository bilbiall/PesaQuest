<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Models\Setting;
use App\Services\PushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushController extends Controller
{
    /** Public VAPID key — safe to expose, it's how the browser verifies pushes came from us. */
    public function publicKey(): JsonResponse
    {
        return response()->json([
            'key'       => Setting::get('vapid_public_key'),
            'available' => (bool) Setting::get('vapid_public_key'),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'                 => 'required|string|max:2048',
            'keys.p256dh'              => 'required|string',
            'keys.auth'                => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id'    => auth()->id(),
                'endpoint'   => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'last_used_at' => now(),
                'failed_count' => 0,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint');
        if ($endpoint) {
            PushSubscription::where('endpoint_hash', hash('sha256', $endpoint))
                ->where('user_id', auth()->id())
                ->delete();
        } else {
            // No endpoint given (e.g. permission revoked) — drop every subscription for this player
            PushSubscription::where('user_id', auth()->id())->delete();
        }

        return response()->json(['success' => true]);
    }

    public function getPreferences(): JsonResponse
    {
        $user  = auth()->user();
        $prefs = $user->notification_prefs ?? [];

        $categories = collect(PushService::CATEGORIES)
            ->reject(fn ($c) => $c === 'teacher' && !($user->is_school_teacher ?? false))
            ->reject(fn ($c) => $c === 'monetization' && app(PushService::class)->isProtectedAccount($user))
            ->mapWithKeys(fn ($c) => [$c => (bool) ($prefs[$c] ?? true)]);

        return response()->json([
            'preferences'  => $categories,
            'subscribed'   => PushSubscription::where('user_id', $user->id)->exists(),
        ]);
    }

    public function savePreferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'boolean',
        ]);

        $valid = array_intersect_key($data['preferences'], array_flip(PushService::CATEGORIES));

        $user = auth()->user();
        $user->notification_prefs = array_merge($user->notification_prefs ?? [], $valid);
        $user->save();

        return response()->json(['success' => true]);
    }
}
