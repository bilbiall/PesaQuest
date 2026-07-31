<?php

namespace App\Http\Controllers;

use App\Models\ArcadeGame;
use App\Models\ArcadeMatch;
use App\Models\ArcadeMatchInvite;
use App\Models\ArcadeSession;
use App\Models\ArcadeTile;
use App\Models\Friendship;
use App\Models\User;
use App\Services\ArcadeSnakesService;
use App\Services\PlanGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ArcadeSnakesController extends Controller
{
    /** Physical board rows (bottom to top) — same boundaries as the GameSet tile editor.
     *  odd rows read left-to-right as numbers increase, even rows read right-to-left. */
    private const ROW_BOUNDS = [[1, 13], [14, 27], [28, 40], [41, 54], [55, 68], [69, 81]];

    /** Polled site-wide (see resources/views/components/mobile-bottom-nav.blade.php)
     *  so a Rivals Trail invite pops up wherever the player currently is in the
     *  game, not only if they happen to open the arcade lobby themselves. */
    public function pendingInvites()
    {
        $invites = ArcadeMatchInvite::where('invited_user_id', auth()->id())
            ->where('status', 'pending')
            ->whereHas('match', fn ($q) => $q->whereIn('status', ['open', 'active']))
            ->with(['match:id,stake_amount', 'inviter:id,name'])
            ->latest()
            ->get()
            ->map(fn (ArcadeMatchInvite $i) => [
                'id'           => $i->id,
                'inviter_name' => $i->inviter->name ?? 'A friend',
                'stake_amount' => $i->match->stake_amount ?? 0,
            ]);

        return response()->json(['invites' => $invites]);
    }

    public function index()
    {
        $user  = auth()->user();
        $game  = ArcadeGame::where('slug', 'snakes-and-cash')->firstOrFail();
        $tier  = app(ArcadeSnakesService::class)->stakeTierFor($user, $game);

        $activeSession = ArcadeSession::where('user_id', $user->id)
            ->where('arcade_game_id', $game->id)
            ->where('status', 'active')
            ->with('match')
            ->latest()->first();

        $openMatches = ArcadeMatch::where('arcade_game_id', $game->id)
            ->where('mode', 'standard')
            ->where('visibility', 'public')
            ->whereIn('status', ['open', 'active'])
            ->withCount('sessions')
            ->latest()->limit(20)->get()
            ->filter(fn ($m) => $m->sessions_count < $m->max_players)
            ->take(10)->values();

        // Rivals Trail — public rounds still waiting for players (an already-started
        // round is only reachable by code/invite, not this browse list).
        $openWagerMatches = ArcadeMatch::where('arcade_game_id', $game->id)
            ->where('mode', 'wager')
            ->where('visibility', 'public')
            ->where('status', 'open')
            ->withCount('sessions')
            ->latest()->limit(20)->get()
            ->filter(fn ($m) => $m->sessions_count < $m->max_players)
            ->take(10)->values();

        $myInvites = ArcadeMatchInvite::where('invited_user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('match', fn ($q) => $q->whereIn('status', ['open', 'active']))
            ->with(['match', 'inviter:id,name'])
            ->latest()->get();

        $friends = Friendship::where(fn ($q) => $q->where('requester_id', $user->id)->orWhere('addressee_id', $user->id))
            ->where('status', 'accepted')
            ->get()
            ->map(fn ($f) => $f->otherUser($user->id))
            ->filter()
            ->values();

        $ended = ArcadeSession::where('user_id', $user->id)->where('arcade_game_id', $game->id)
            ->whereIn('status', ['won', 'busted', 'cashed_out', 'lost', 'forfeited'])->get();
        $stats = [
            'games_played' => $ended->count(),
            'win_rate'     => $ended->count() ? (int) round($ended->where('status', 'won')->count() / $ended->count() * 100) : 0,
            'best_pot'     => (int) $ended->max('pot_amount'),
        ];

        return view('arcade.snakes.lobby', [
            'game' => $game, 'tier' => $tier, 'activeSession' => $activeSession, 'openMatches' => $openMatches, 'stats' => $stats,
            'openWagerMatches' => $openWagerMatches, 'myInvites' => $myInvites, 'friends' => $friends,
            'minWagerStake' => ArcadeSnakesService::MIN_WAGER_STAKE, 'maxWagerStake' => ArcadeSnakesService::MAX_WAGER_STAKE,
        ]);
    }

    /** Per-row (top%, left%, right%) measured directly from pesatrail.png via a luminance
     *  scan (tile cream color vs. dark board background) — the tile strip's real vertical
     *  center and horizontal extent for each physical row, keyed by ROW_BOUNDS index. */
    private const ROW_PIXELS = [
        ['top' => 87.74, 'left' => 12.97, 'right' => 98.42], // [1,13]
        ['top' => 70.75, 'left' =>  7.44, 'right' => 97.15], // [14,27]
        ['top' => 54.72, 'left' =>  7.52, 'right' => 87.58], // [28,40]
        ['top' => 38.68, 'left' =>  5.14, 'right' => 96.44], // [41,54]
        ['top' => 23.11, 'left' => 10.68, 'right' => 98.10], // [55,68]
        ['top' =>  8.49, 'left' =>  6.65, 'right' => 94.54], // [69,81]
    ];

    /** Percentage position for every tile number, measured against the real board art
     *  (see ROW_PIXELS) rather than guessed — tiles within a row are evenly subdivided
     *  across that row's measured span, alternating direction per the boustrophedon layout. */
    private function tilePositions(): array
    {
        $positions = [];
        foreach (self::ROW_BOUNDS as $rowIndex => [$from, $to]) {
            $n = $to - $from + 1;
            ['top' => $topPercent, 'left' => $leftBound, 'right' => $rightBound] = self::ROW_PIXELS[$rowIndex];
            $leftToRight = $rowIndex % 2 === 0;
            for ($i = 0; $i < $n; $i++) {
                $number = $from + $i;
                $fraction = $n > 1 ? $i / ($n - 1) : 0.5;
                $leftPercent = $leftToRight
                    ? $leftBound + $fraction * ($rightBound - $leftBound)
                    : $rightBound - $fraction * ($rightBound - $leftBound);
                $positions[$number] = ['left' => round($leftPercent, 1), 'top' => round($topPercent, 1)];
            }
        }
        return $positions;
    }

    /** Per-tile position, preferring the admin-calibrated arcade_tiles.pos_left/pos_top
     *  (set via the GameSet board layout editor) and falling back to the computed
     *  formula only for a tile that was never calibrated. Also emits the separate
     *  pos_left_mobile/pos_top_mobile pair (falling back to the desktop values when
     *  a tile was never mobile-calibrated) — the client picks whichever set applies
     *  based on the same forced-landscape condition the CSS itself uses, mirroring
     *  the responsive-JS pattern relocateGameHud()/dockOverBoard() already use. */
    private function tilePositionsFor($tiles): array
    {
        $fallback = $this->tilePositions();
        $positions = [];
        foreach ($tiles as $tile) {
            $left = $tile->pos_left ?? ($fallback[$tile->number]['left'] ?? 50);
            $top  = $tile->pos_top ?? ($fallback[$tile->number]['top'] ?? 50);
            $positions[$tile->number] = [
                'left' => $left,
                'top'  => $top,
                'left_m' => $tile->pos_left_mobile ?? $left,
                'top_m'  => $tile->pos_top_mobile ?? $top,
            ];
        }
        return $positions;
    }

    public function startSolo(ArcadeSnakesService $service)
    {
        $user = auth()->user();
        $game = ArcadeGame::where('slug', 'snakes-and-cash')->firstOrFail();

        if ($err = $this->dailyPlayGateError($user, $game)) return back()->with('error', $err);

        try {
            $session = $service->startSoloWithBot($user, $game);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('arcade.snakes.play', $session);
    }

    public function createMatch(Request $request, ArcadeSnakesService $service)
    {
        $data = $request->validate([
            'visibility'  => 'required|in:public,private',
            'max_players' => 'required|integer|min:2|max:8',
        ]);
        $user = auth()->user();
        $game = ArcadeGame::where('slug', 'snakes-and-cash')->firstOrFail();

        if ($err = $this->dailyPlayGateError($user, $game)) return back()->with('error', $err);

        try {
            $match = $service->createMatch($user, $game, $data['visibility'], (int) $data['max_players']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $session = $match->sessions()->where('user_id', auth()->id())->first();
        return redirect()->route('arcade.snakes.play', $session)->with('success', $match->join_code ? "Match created — share code {$match->join_code} with friends." : 'Public match created.');
    }

    public function joinMatch(Request $request, ArcadeSnakesService $service)
    {
        $data = $request->validate(['code' => 'nullable|string|max:8', 'match_id' => 'nullable|integer']);

        $match = $data['code'] ?? null
            ? ArcadeMatch::where('join_code', strtoupper($data['code']))->first()
            : ArcadeMatch::find($data['match_id'] ?? 0);

        if (!$match) {
            return back()->with('error', 'No match found with that code.');
        }

        $user = auth()->user();
        $game = ArcadeGame::find($match->arcade_game_id);
        if ($err = $this->dailyPlayGateError($user, $game)) return back()->with('error', $err);

        try {
            $session = $service->joinMatch($user, $match);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('arcade.snakes.play', $session);
    }

    /** Creates a Rivals Trail round — the initiator's chosen entry amount every
     *  joiner must match, with optional friend invites sent immediately. */
    public function createWagerMatch(Request $request, ArcadeSnakesService $service)
    {
        $data = $request->validate([
            'visibility'   => 'required|in:public,private',
            'max_players'  => 'required|integer|min:2|max:8',
            'stake_amount' => 'required|integer|min:' . ArcadeSnakesService::MIN_WAGER_STAKE . '|max:' . ArcadeSnakesService::MAX_WAGER_STAKE,
            'invite_ids'   => 'nullable|array',
            'invite_ids.*' => 'integer|exists:users,id',
        ]);
        $user = auth()->user();
        $game = ArcadeGame::where('slug', 'snakes-and-cash')->firstOrFail();

        if ($err = $this->dailyPlayGateError($user, $game)) return back()->with('error', $err);

        try {
            $match = $service->createWagerMatch($user, $game, $data['visibility'], (int) $data['max_players'], (int) $data['stake_amount']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        foreach ($data['invite_ids'] ?? [] as $friendId) {
            $friend = User::find($friendId);
            if (!$friend) continue;
            try {
                $service->inviteFriend($match, $user, $friend);
            } catch (\RuntimeException $e) {
                // A bad individual invite (e.g. no longer friends) shouldn't block the round itself.
            }
        }

        $session = $match->sessions()->where('user_id', auth()->id())->first();
        return redirect()->route('arcade.snakes.play', $session)->with('success', $match->join_code
            ? "Rivals Trail round created — share code {$match->join_code} with friends."
            : 'Public Rivals Trail round created.');
    }

    public function joinWagerMatch(Request $request, ArcadeSnakesService $service)
    {
        $data = $request->validate(['code' => 'nullable|string|max:8', 'match_id' => 'nullable|integer']);

        $match = $data['code'] ?? null
            ? ArcadeMatch::where('join_code', strtoupper($data['code']))->where('mode', 'wager')->first()
            : ArcadeMatch::where('mode', 'wager')->find($data['match_id'] ?? 0);

        if (!$match) {
            return back()->with('error', 'No Rivals Trail round found with that code.');
        }

        $user = auth()->user();
        $game = ArcadeGame::find($match->arcade_game_id);
        if ($err = $this->dailyPlayGateError($user, $game)) return back()->with('error', $err);

        try {
            $session = $service->joinWagerMatch($user, $match);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('arcade.snakes.play', $session);
    }

    public function acceptInvite(ArcadeMatchInvite $invite, ArcadeSnakesService $service)
    {
        $user = auth()->user();
        abort_if($invite->invited_user_id !== $user->id, 403);

        if ($invite->status !== 'pending') {
            return back()->with('error', 'This invite is no longer valid.');
        }

        $match = $invite->match;
        $game = ArcadeGame::find($match->arcade_game_id);
        if ($err = $this->dailyPlayGateError($user, $game)) return back()->with('error', $err);

        try {
            $session = $service->joinWagerMatch($user, $match);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $invite->update(['status' => 'accepted']);

        return redirect()->route('arcade.snakes.play', $session);
    }

    public function declineInvite(ArcadeMatchInvite $invite)
    {
        abort_if($invite->invited_user_id !== auth()->id(), 403);

        $invite->update(['status' => 'declined']);

        return back()->with('success', 'Invite declined.');
    }

    /**
     * Premium (and admin/free-for-all/trial) players start Pesa Trail games and
     * invite others unlimited times. Free players are capped at admin-tunable
     * games/day (PlanGate `pesatrail_games_per_day`) — joining a friend's match
     * still consumes one of the free player's own daily slots.
     */
    private function dailyPlayGateError(User $user, ?ArcadeGame $game): ?string
    {
        if (!$game) return null;
        $gate  = app(PlanGate::class);
        $limit = $gate->limit($user, 'pesatrail_games_per_day');
        if ($limit === 0) return null;

        $playedToday = ArcadeSession::where('user_id', $user->id)
            ->where('arcade_game_id', $game->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return $gate->allows($user, 'pesatrail_games_per_day', $playedToday)
            ? null
            : $gate->deny('pesatrail_games_per_day', $limit)['error'];
    }

    public function play(ArcadeSession $session, ArcadeSnakesService $service)
    {
        abort_if($session->user_id !== auth()->id(), 403);

        $game     = ArcadeGame::findOrFail($session->arcade_game_id);
        $tiles    = ArcadeTile::where('arcade_game_id', $game->id)->with('sponsor')->orderBy('number')->get();
        $progress = auth()->user()->getOrCreateProgress();
        $match    = $session->arcade_match_id ? ArcadeMatch::find($session->arcade_match_id) : null;

        if ($match) {
            $service->expireTurnIfNeeded($match);
            $match->refresh();
        }

        $opponents = collect();
        if ($match) {
            $opponents = ArcadeSession::with('user')
                ->where('arcade_match_id', $session->arcade_match_id)
                ->where('user_id', '!=', auth()->id())
                ->orderBy('turn_order')
                ->get();
        }

        $positions = $this->tilePositionsFor($tiles);
        $turnMode  = $match->turn_mode ?? 'free';
        $isMyTurn  = !$match || !$match->isTurnBased() || $match->current_turn_session_id === $session->id;
        $turnSecondsRemaining = $this->turnSecondsRemaining($match);

        return view('arcade.snakes.play', compact('session', 'game', 'tiles', 'opponents', 'progress', 'positions', 'turnMode', 'isMyTurn', 'turnSecondsRemaining', 'match'));
    }

    private function turnSecondsRemaining(?ArcadeMatch $match): ?int
    {
        if (!$match || !$match->isTurnBased() || !$match->turn_started_at) return null;
        // turn_started_at is pushed into the near future right after a roll (see
        // ArcadeSnakesService::advanceTurn()) to cover the roll's own token
        // animation — diffInSeconds() returns an ABSOLUTE difference, so without
        // this guard a still-future turn_started_at would make the remaining time
        // shrink as if the countdown had already started, the exact "ticks down
        // while the previous move is still animating" bug this delay exists to fix.
        if ($match->turn_started_at->isFuture()) return ArcadeSnakesService::TURN_SECONDS;
        return max(0, ArcadeSnakesService::TURN_SECONDS - (int) round($match->turn_started_at->diffInSeconds(now())));
    }

    /** Lightweight JSON snapshot the play view polls so newly-joined opponents and
     *  whose-turn-it-is stay live without a page reload — no websockets/broadcasting
     *  infra exists in this project, so polling is the pragmatic real-time approach. */
    public function state(ArcadeSession $session, ArcadeSnakesService $service)
    {
        abort_if($session->user_id !== auth()->id(), 403);
        $session->refresh();

        $match = $session->arcade_match_id ? ArcadeMatch::find($session->arcade_match_id) : null;
        $botRoll = null;
        if ($match) {
            $service->expireTurnIfNeeded($match);
            $botRoll = $service->autoPlayBotTurn($match);
            $match->refresh();
        }

        $opponents = collect();
        if ($match) {
            $opponents = ArcadeSession::with('user')
                ->where('arcade_match_id', $match->id)
                ->where('id', '!=', $session->id)
                ->orderBy('turn_order')
                ->get()
                ->map(fn (ArcadeSession $s) => [
                    'session_id'   => $s->id,
                    'name'         => $s->is_bot ? 'Robo' : ($s->user->name ?? 'Player'),
                    'avatar'       => $s->is_bot ? null : $s->user?->avatar_url,
                    'is_bot'       => $s->is_bot,
                    'position'     => $s->position,
                    'status'       => $s->status,
                    'pot'          => $s->pot_amount,
                    'stake'        => $s->stake_amount,
                    'missed_turns' => $s->missed_turns,
                    'turn_order'   => $s->turn_order,
                ])->values();
        }

        $myTurn = true;
        $currentTurnName = null;
        if ($match && $match->isTurnBased()) {
            $myTurn = $match->current_turn_session_id === $session->id;
            if (!$myTurn && $match->current_turn_session_id) {
                $holder = ArcadeSession::with('user')->find($match->current_turn_session_id);
                $currentTurnName = $holder ? ($holder->is_bot ? 'Robo' : ($holder->user->name ?? 'Player')) : null;
            }
        }

        return response()->json([
            'success'               => true,
            'turn_mode'             => $match->turn_mode ?? 'free',
            'mode'                  => $match->mode ?? 'standard',
            'match_status'          => $match->status ?? null,
            'forfeit_pool_amount'   => $match->forfeit_pool_amount ?? 0,
            'my_turn'               => $myTurn,
            'current_turn_user'     => $currentTurnName,
            'current_turn_session_id' => $match->current_turn_session_id ?? null,
            'turn_seconds_remaining' => $this->turnSecondsRemaining($match),
            'session'               => ['position' => $session->position, 'pot' => $session->pot_amount, 'status' => $session->status, 'missed_turns' => $session->missed_turns, 'turn_order' => $session->turn_order],
            'opponents'             => $opponents,
            'bot_roll'              => $botRoll,
            'reaction'              => $match ? Cache::get("arcade_match_{$match->id}_reaction") : null,
        ]);
    }

    /** A curated (not free-text) set of emoji taunts — deliberately small given
     *  the youth-oriented audience, avoids any moderation concern. Ephemeral by
     *  design: the latest one per match lives briefly in cache, no history table. */
    private const REACTION_EMOJIS = ['😂', '😮', '😤', '🔥', '👏', '😅', '💪', '😬'];

    public function sendReaction(Request $request, ArcadeSession $session)
    {
        abort_if($session->user_id !== auth()->id(), 403);

        $data = $request->validate(['emoji' => 'required|string|in:' . implode(',', self::REACTION_EMOJIS)]);

        if (!$session->arcade_match_id) {
            return response()->json(['success' => false, 'message' => 'No one to react to.'], 422);
        }

        $cooldownKey = "arcade_reaction_cooldown_{$session->id}";
        if (Cache::has($cooldownKey)) {
            return response()->json(['success' => false, 'message' => 'Wait a moment before reacting again.'], 429);
        }
        Cache::put($cooldownKey, true, now()->addSeconds(6));

        Cache::put("arcade_match_{$session->arcade_match_id}_reaction", [
            'id'               => (string) Str::uuid(),
            'from_session_id'  => $session->id,
            'emoji'            => $data['emoji'],
        ], now()->addSeconds(8));

        return response()->json(['success' => true]);
    }

    public function roll(ArcadeSession $session, ArcadeSnakesService $service)
    {
        abort_if($session->user_id !== auth()->id(), 403);

        try {
            $result = $service->roll($session);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true] + $result);
    }

    public function cashOut(ArcadeSession $session, ArcadeSnakesService $service)
    {
        abort_if($session->user_id !== auth()->id(), 403);

        try {
            $result = $service->cashOut($session);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true] + $result);
    }
}
