<?php

namespace App\Http\Controllers;

use App\Models\Npc;
use App\Models\PlayerNpcRelationship;
use App\Services\LifeInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class InboxController extends Controller
{
    public function __construct(private LifeInboxService $inbox) {}

    public function index()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        try {
            $pending       = $this->inbox->getPendingDecisions($user);
            $recent        = $this->inbox->getRecentResolved($user, 2);
            $npcs          = Npc::where('is_active', true)->get();
            $relationships = PlayerNpcRelationship::where('user_id', $user->id)
                ->pluck('score', 'npc_id');
        } catch (\Exception $e) {
            // Tables not yet migrated — show empty inbox
            $pending       = new Collection();
            $recent        = new Collection();
            $npcs          = new Collection();
            $relationships = new Collection();
        }

        return view('play.inbox', compact('progress', 'pending', 'recent', 'npcs', 'relationships'));
    }

    public function resolve(Request $request)
    {
        $request->validate([
            'player_decision_id' => 'required|integer',
            'choice_id'          => 'required|integer',
        ]);

        try {
            $result = $this->inbox->resolve(
                auth()->user(),
                $request->player_decision_id,
                $request->choice_id
            );
            return response()->json(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Migrations pending — run php artisan migrate.'], 503);
        }
    }
}
