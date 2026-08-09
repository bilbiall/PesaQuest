<?php

namespace App\Http\Controllers;

use App\Models\Dream;
use App\Models\PlayerDream;
use App\Services\DreamService;
use Illuminate\Http\Request;

class DreamController extends Controller
{
    public function index(DreamService $service)
    {
        $user   = auth()->user();
        $dreams = $service->eligibleFor($user);
        $ownedIds = PlayerDream::where('user_id', $user->id)->pluck('dream_id')->all();

        $ownedDreams = PlayerDream::where('user_id', $user->id)->with('dream')->latest('purchased_at')->get();

        return view('dreams.index', [
            'dreams'      => $dreams,
            'ownedIds'    => $ownedIds,
            'ownedDreams' => $ownedDreams,
            'balance'     => $user->getOrCreateProgress()->balance ?? 0,
        ]);
    }

    public function purchase(Request $request, Dream $dream, DreamService $service)
    {
        $result = $service->purchase(auth()->user(), $dream);

        if (!$result['ok']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', "🎉 Dream claimed — \"{$dream->name}\" is now yours forever!");
    }
}
