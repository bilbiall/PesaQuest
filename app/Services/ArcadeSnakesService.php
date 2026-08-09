<?php

namespace App\Services;

use App\Models\ArcadeFlavorText;
use App\Models\ArcadeGame;
use App\Models\ArcadeMatch;
use App\Models\ArcadeMatchInvite;
use App\Models\ArcadeSession;
use App\Models\ArcadeStakeTier;
use App\Models\ArcadeTile;
use App\Models\Friendship;
use App\Models\GameNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Snakes & Cash resolution engine. A session's pot is separate from the
 * player's real wallet — the stake leaves the wallet at start, the pot
 * lives inside the session while playing, and only cashOut() settles it
 * back (more if the session went well, nothing if it busted).
 *
 * Tile resolution order for a roll that lands mid-board: apply the landed
 * tile's own money/mystery effect, then (if it's a ladder bottom or snake
 * head) move to its target tile and apply THAT tile's effect too. One hop
 * only — a destination's own movement_role (if any) never cascades further
 * in the same roll.
 */
class ArcadeSnakesService
{
    public const TURN_SECONDS = 15; // how long a turn-based match waits before auto-passing an idle turn
    public const BOT_THINK_SECONDS = 2; // how long Robo "thinks" before rolling on its own turn

    // Mirrors the client's animateHopPath() HOP_MS and the fixed post-hop delays in
    // processRollResult()/animateOpponent() (play.blade.php) — used only to estimate
    // how long a roll's token animation will visibly take, so the NEXT player's
    // TURN_SECONDS countdown can be delayed to start once it's actually done playing
    // out on screen, instead of counting down while they're still watching the
    // previous move animate (see advanceTurn()'s $animationDelayMs).
    private const HOP_MS = 260;
    private const MOVE_EVENT_EXTRA_MS = 1300; // 400ms pause + snake/ladder glide settle
    private const PLAIN_LANDING_EXTRA_MS = 500;
    // Both roll paths hold on the die face for a beat before the token starts
    // hopping at all (580ms — doRoll()'s cube spin settle; a bot roll's
    // spinDieForBot() flourish is longer still, but that extra stretch is covered
    // client-side by animatingSessions rather than here — see pollState()). This
    // is a floor under the server's own TURN_SECONDS enforcement clock, not the
    // authoritative source for what players see on screen.
    private const PRE_HOP_REVEAL_MS = 580;

    // Rivals Trail (wager mode)
    public const FORFEIT_MISSED_TURNS = 8; // consecutive auto-expired turns before a player is withdrawn
    public const WINNER_CUT_PERCENT = 60;  // % of each remaining opponent's pot the winner takes
    public const FORFEIT_CUT_PERCENT = 30; // % of a forfeiting player's pot that joins the match's bonus pool
    public const MIN_WAGER_STAKE = 100;
    public const MAX_WAGER_STAKE = 5000;

    private const GOLDEN_BOOST_PERCENT = 25; // % of the ORIGINAL stake, added to the pot on every golden landing after the first

    /** Dead-pool safety net ONLY — see flavorText(). The real, admin-editable,
     *  randomly-picked pool lives in arcade_flavor_texts (GameSet Arcade →
     *  Flavor Text), seeded from these exact lines. These constants stay only
     *  so a landing can never show blank if that table is ever emptied out. */
    private const REWARD_LESSONS = [
        'Consistent saving compounds — small wins add up.',
        'A side hustle payday — extra income is a buffer, not a lifestyle upgrade.',
        'Smart spending freed up cash for this.',
        'Patience paid off here — literally.',
    ];
    private const EXPENSE_LESSONS = [
        'Unexpected costs happen — that\'s exactly why an emergency fund matters.',
        'Small leaks sink big ships — track where this went.',
        'A bill you didn\'t plan for — budgeting catches these before they catch you.',
        'This is the cost of not having a buffer ready.',
    ];

    public function stakeTierFor(User $user, ArcadeGame $game): ?ArcadeStakeTier
    {
        $progress = $user->getOrCreateProgress();

        return ArcadeStakeTier::forLevel($game->id, $progress->level)
            ?? ArcadeStakeTier::where('arcade_game_id', $game->id)->where('is_active', true)->orderBy('stake_amount')->first();
    }

    public function startSolo(User $user, ArcadeGame $game): ArcadeSession
    {
        return $this->openSession($user, $game, null);
    }

    /** Solo play, but with Robo the bot in a private 2-seat turn-based match —
     *  races the player, taking its own turn a couple seconds after the player's
     *  (see autoPlayBotTurn()) rather than moving in lockstep with them. */
    public function startSoloWithBot(User $user, ArcadeGame $game): ArcadeSession
    {
        $match = ArcadeMatch::create([
            'arcade_game_id' => $game->id,
            'created_by'     => $user->id,
            'join_code'      => null,
            'visibility'     => 'private',
            'max_players'    => 2,
            'status'         => 'active',
            'turn_mode'      => 'turns',
        ]);

        $session = $this->openSession($user, $game, $match, false, 0);
        $this->openSession($this->botUser(), $game, $match, true, 1);
        $match->update(['current_turn_session_id' => $session->id, 'turn_started_at' => now()]);

        return $session;
    }

    public function botUser(): User
    {
        return User::where('email', 'robo@bot.moski.internal')->firstOrFail();
    }

    /** Called on every state() poll: if a bot currently holds the turn and has
     *  "thought" about it for BOT_THINK_SECONDS, roll for it and hand the turn
     *  back. Returns the bot's roll result (for the client to animate) or null
     *  if nothing happened. Deliberately NOT called from inside roll() itself —
     *  that would let a human's own roll recurse into rolling the bot in the
     *  same request, which is exactly the "moves automatically" behavior this
     *  replaces; the bot's turn only ever resolves on its own, later, tick. */
    public function autoPlayBotTurn(ArcadeMatch $match): ?array
    {
        if (!$match->isTurnBased() || !$match->current_turn_session_id || !$match->turn_started_at) return null;

        // state() can be polled by more than one open tab/device for the same
        // match, and adaptive polling fires as often as every ~1.2s — without
        // this, two near-simultaneous polls could both pass the checks below
        // and both call roll() for the bot's SAME turn, double-rolling it and
        // leaving current_turn_session_id bouncing between players. Cache::add()
        // only succeeds for the first caller; a losing caller just no-ops this
        // tick and picks the (by-then-updated) state next tick instead.
        $lockKey = "arcade_bot_turn_lock_{$match->id}";
        if (!Cache::add($lockKey, true, 5)) {
            return null;
        }

        try {
            $holder = ArcadeSession::find($match->current_turn_session_id);
            if (!$holder || !$holder->is_bot) return null;

            if (!$holder->isActive()) {
                // Bot already finished playing but is still parked as the turn
                // holder (e.g. it busted on its last roll) — just hand the turn on.
                $this->advanceTurn($match);
                return null;
            }

            if ($match->turn_started_at->diffInSeconds(now()) < self::BOT_THINK_SECONDS) return null;

            $result = $this->roll($holder);
            $result['session_id'] = $holder->id;

            return $result;
        } finally {
            Cache::forget($lockKey);
        }
    }

    /** Every match is turn-based — waiting your turn isn't a choice, it's how the
     *  game works (matches the always-on 10s-per-turn rule solo-vs-bot already uses). */
    public function createMatch(User $user, ArcadeGame $game, string $visibility, int $maxPlayers, ?string $name = null): ArcadeMatch
    {
        $match = ArcadeMatch::create([
            'arcade_game_id' => $game->id,
            'created_by'     => $user->id,
            'name'           => $name,
            'join_code'      => $visibility === 'private' ? ArcadeMatch::generateJoinCode() : null,
            'visibility'     => $visibility,
            'max_players'    => max(2, min(8, $maxPlayers)),
            'status'         => 'open',
            'turn_mode'      => 'turns',
        ]);

        $session = $this->openSession($user, $game, $match, false, 0);

        if ($match->isTurnBased()) {
            $match->update(['current_turn_session_id' => $session->id, 'turn_started_at' => now()]);
        }

        return $match;
    }

    public function joinMatch(User $user, ArcadeMatch $match): ArcadeSession
    {
        if ($match->sessions()->count() >= $match->max_players) {
            throw new \RuntimeException('This match is full.');
        }
        if ($match->sessions()->where('user_id', $user->id)->exists()) {
            throw new \RuntimeException('You are already in this match.');
        }

        $maxOrder = $match->sessions()->max('turn_order');
        $nextOrder = $maxOrder === null ? 0 : ((int) $maxOrder) + 1;
        $session = $this->openSession($user, $match->game, $match, false, $nextOrder);
        $match->update(['status' => 'active']);

        if ($match->isTurnBased() && !$match->current_turn_session_id) {
            $match->update(['current_turn_session_id' => $session->id, 'turn_started_at' => now()]);
        }

        return $session;
    }

    /**
     * Rivals Trail — a head-to-head money round. Multiplayer-only by
     * construction (never seats a bot), the initiator picks the entry
     * amount every joiner must match, and money moves automatically when
     * the round is decided rather than via a manual cash-out (see
     * settleMatchIfDecided()/cashOut()'s wager guard).
     */
    public function createWagerMatch(User $user, ArcadeGame $game, string $visibility, int $maxPlayers, int $stakeAmount, ?string $name = null): ArcadeMatch
    {
        $stake = max(self::MIN_WAGER_STAKE, min(self::MAX_WAGER_STAKE, $stakeAmount));

        $match = ArcadeMatch::create([
            'arcade_game_id' => $game->id,
            'created_by'     => $user->id,
            'name'           => $name,
            'join_code'      => $visibility === 'private' ? ArcadeMatch::generateJoinCode() : null,
            'visibility'     => $visibility,
            'max_players'    => max(2, min(8, $maxPlayers)),
            'status'         => 'open',
            'turn_mode'      => 'turns',
            'mode'           => 'wager',
            'stake_amount'   => $stake,
        ]);

        $this->openSession($user, $game, $match, false, 0, $stake);
        $this->notifyStakeJoined($user, $stake);

        return $match;
    }

    public function joinWagerMatch(User $user, ArcadeMatch $match): ArcadeSession
    {
        if (!$match->isWager()) {
            throw new \RuntimeException('This is not a Rivals Trail round.');
        }
        if ($match->status === 'completed') {
            throw new \RuntimeException('This round has already ended.');
        }
        if ($match->sessions()->count() >= $match->max_players) {
            throw new \RuntimeException('This round is full.');
        }
        if ($match->sessions()->where('user_id', $user->id)->exists()) {
            throw new \RuntimeException('You are already in this round.');
        }

        $maxOrder = $match->sessions()->max('turn_order');
        $nextOrder = $maxOrder === null ? 0 : ((int) $maxOrder) + 1;
        $session = $this->openSession($user, $match->game, $match, false, $nextOrder, (int) $match->stake_amount);
        $match->update(['status' => 'active']);

        if ($match->isTurnBased() && !$match->current_turn_session_id) {
            $match->update(['current_turn_session_id' => $session->id, 'turn_started_at' => now()]);
        }

        $this->notifyStakeJoined($user, (int) $match->stake_amount);

        return $session;
    }

    private function notifyStakeJoined(User $user, int $stake): void
    {
        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'arcade_stake_joined',
            'title'   => '🎯 Entered a Rivals Trail round',
            'body'    => 'KES ' . number_format($stake) . ' entered the round.',
            'icon'    => '🎯',
            'data'    => ['url' => route('arcade.snakes.lobby'), 'amount' => $stake],
        ]);
    }

    /** Only the round's creator may invite — matches this feature's own framing
     *  ("the initiator ... invites friends"); a later "any participant can
     *  invite more" relaxation is a one-line change, not a schema change. */
    public function inviteFriend(ArcadeMatch $match, User $inviter, User $friend): ArcadeMatchInvite
    {
        if (!$match->isWager()) {
            throw new \RuntimeException('This is not a Rivals Trail round.');
        }
        if ((int) $match->created_by !== $inviter->id) {
            throw new \RuntimeException('Only the round creator can invite players.');
        }
        if (!Friendship::areFriends($inviter->id, $friend->id)) {
            throw new \RuntimeException('You can only invite accepted friends.');
        }

        $invite = ArcadeMatchInvite::updateOrCreate(
            ['arcade_match_id' => $match->id, 'invited_user_id' => $friend->id],
            ['invited_by' => $inviter->id, 'status' => 'pending']
        );

        GameNotification::create([
            'user_id' => $friend->id,
            'type'    => 'arcade_match_invite',
            'title'   => '🎲 ' . $inviter->name . ' invited you to a Rivals Trail round',
            'body'    => 'Entry amount: KES ' . number_format($match->stake_amount) . '.',
            'icon'    => '🎲',
            'data'    => ['url' => route('arcade.snakes.lobby'), 'match_id' => $match->id],
        ]);

        return $invite;
    }

    private function openSession(User $user, ArcadeGame $game, ?ArcadeMatch $match, bool $isBot = false, int $turnOrder = 0, ?int $stakeOverride = null): ArcadeSession
    {
        if ($stakeOverride !== null) {
            $stake = $stakeOverride;
        } else {
            $tier = $this->stakeTierFor($user, $game);
            $stake = $tier->stake_amount ?? 200;
        }

        $progress = $user->getOrCreateProgress();
        if ($progress->balance < $stake) {
            throw new \RuntimeException("You need at least KES {$stake} in your wallet to play — your current balance is KES {$progress->balance}.");
        }

        return DB::transaction(function () use ($user, $game, $match, $stake, $progress, $isBot, $turnOrder) {
            $progress->balance -= $stake;
            $progress->recalculateNetWorth();
            $progress->save();

            return ArcadeSession::create([
                'arcade_game_id'  => $game->id,
                'arcade_match_id' => $match?->id,
                'user_id'         => $user->id,
                'is_bot'          => $isBot,
                'turn_order'      => $turnOrder,
                'stake_amount'    => $stake,
                'pot_amount'      => $stake,
                'position'        => 0,
                'status'          => 'active',
                'started_at'      => now(),
            ]);
        });
    }

    /** Roll the die and resolve the landing. Returns a small event log for the UI to animate. */
    public function roll(ArcadeSession $session): array
    {
        if (!$session->isActive()) {
            throw new \RuntimeException('This session has already ended.');
        }

        // A double-tap, a retried slow request, or a browser somehow firing the
        // same roll twice must never both execute — either would advance the
        // board/turn twice for one real action and leave two players' clients
        // disagreeing about whose turn it even is. Auto-expires after 10s (far
        // longer than a real roll takes) so a mid-roll exception can never wedge
        // this open beyond a few seconds.
        $lockKey = "arcade_roll_lock_{$session->id}";
        if (!Cache::add($lockKey, true, 10)) {
            throw new \RuntimeException("You're already rolling — hang on a second.");
        }

        $match = $session->arcade_match_id ? ArcadeMatch::find($session->arcade_match_id) : null;
        if ($match && $match->isWager() && $match->status === 'open') {
            throw new \RuntimeException('Waiting for at least one more player to join before this round can start.');
        }
        if ($match) {
            $this->expireTurnIfNeeded($match);
        }
        if ($match && $match->isTurnBased() && $match->current_turn_session_id && $match->current_turn_session_id !== $session->id) {
            throw new \RuntimeException("It's not your turn yet — wait for the other player(s) to roll.");
        }

        $game      = ArcadeGame::findOrFail($session->arcade_game_id);
        $tileCount = $game->tile_count;
        $tiles     = ArcadeTile::where('arcade_game_id', $game->id)->get()->keyBy('number');

        $rollValue = random_int(1, 6);
        $from      = $session->position;
        $target    = $from + $rollValue;
        $events    = [];
        $firstLanding = $target;

        // The real, sequential tile numbers the token visibly hops through on its
        // way to $firstLanding — lets the client animate a genuine tile-by-tile
        // hop instead of one long diagonal glide. Only covers this first segment;
        // a snake/ladder jump afterward (via the 'move' event) isn't a sequential
        // hop — target_number can be anywhere on the board — so it stays a plain
        // two-point glide on the client, same as today.
        $hopPath = [];

        if ($target > $tileCount) {
            $needed    = $tileCount - $from;
            $overshoot = $rollValue - $needed;
            $newPosition = $tileCount - $overshoot;
            $firstLanding = $tileCount;
            $events[] = ['type' => 'overshoot', 'roll' => $rollValue, 'needed' => $needed, 'bounced_to' => $newPosition];
            $session->position = $newPosition;
            $hopPath = array_merge(range($from + 1, $tileCount), range($tileCount - 1, $newPosition, -1));
        } elseif ($target === $tileCount) {
            $session->position = $tileCount;
            $bonus = (int) round($session->pot_amount * $game->finish_bonus_percent / 100);
            $session->pot_amount += $bonus;
            $session->status = 'won';
            $session->ended_at = now();
            $events[] = ['type' => 'win', 'roll' => $rollValue, 'bonus' => $bonus];
            $hopPath = range($from + 1, $tileCount);
            // Wager wins are settled (and XP-awarded) once, uniformly, by
            // settleMatchIfDecided() below — whether the win came from reaching
            // the finish tile (here) or from the last opponent forfeiting/busting.
            if (!$match || !$match->isWager()) {
                $this->awardXp($session);
            }
        } else {
            $session->position = $target;
            $hopPath = range($from + 1, $target);
            $tile = $tiles->get($target);
            if ($tile) {
                $this->applyTileEffect($session, $tile, $events, $game);
                if ($session->isActive() && $tile->movement_role !== 'none' && $tile->target_number) {
                    $events[] = ['type' => 'move', 'via' => $tile->movement_role, 'from' => $target, 'to' => $tile->target_number];
                    $session->position = $tile->target_number;
                    $destTile = $tiles->get($tile->target_number);
                    if ($destTile) {
                        $this->applyTileEffect($session, $destTile, $events, $game);
                    }
                }
            }
        }

        $session->last_roll = $rollValue;
        $session->last_event = $events;
        $session->missed_turns = 0; // a completed roll clears any prior missed-turn count
        $session->save();

        $settlement = null;
        if ($match) {
            $hasMoveEvent = collect($events)->contains(fn ($e) => $e['type'] === 'move');
            $animationDelayMs = self::PRE_HOP_REVEAL_MS + count($hopPath) * self::HOP_MS
                + ($hasMoveEvent ? self::MOVE_EVENT_EXTRA_MS : self::PLAIN_LANDING_EXTRA_MS);
            $this->advanceTurn($match, $animationDelayMs);
            $settlement = $this->settleMatchIfDecided($match);
            // settleMatchIfDecided() mutates a separately-queried copy of this same
            // row (money/status changes happen on ITS $winner instance, not this
            // one) — without this refresh, a winner whose own roll decided the
            // match would see only their finish-tile bonus below, not the wager
            // winnings that settlement just added on top of it.
            if ($settlement) {
                $session->refresh();
            }
        }

        Cache::forget($lockKey);

        $wonHere = $settlement && $settlement['winner_session_id'] === $session->id;

        return [
            'roll' => $rollValue, 'from' => $from, 'first_landing' => $firstLanding, 'hop_path' => $hopPath,
            'events' => $events, 'position' => $session->position, 'pot' => $session->pot_amount, 'status' => $session->status,
            'winner_gain' => $wonHere ? $settlement['winner_gain'] : null,
            'forfeit_bonus' => $wonHere ? $settlement['forfeit_bonus'] : null,
        ];
    }

    /** Hands the current turn to the next active session in seating order (wrapping
     *  around), skipping anyone who has already won/busted/cashed out. No-op for
     *  free-mode matches — turn_mode is what gates whether the client ever enforces it.
     *  $animationDelayMs pushes turn_started_at into the near future rather than
     *  starting it immediately — used only by roll() (see above), so the new turn's
     *  TURN_SECONDS countdown doesn't start burning down while the roll that just
     *  happened is still visibly animating on everyone's screen. Every other caller
     *  (join/forfeit/cash-out — no token animation to wait for) keeps the instant
     *  default. */
    private function advanceTurn(ArcadeMatch $match, int $animationDelayMs = 0): void
    {
        if (!$match->isTurnBased()) return;

        $all = ArcadeSession::where('arcade_match_id', $match->id)->orderBy('turn_order')->get();
        $active = $all->where('status', 'active')->values();

        if ($active->isEmpty()) {
            $match->update(['current_turn_session_id' => null]);
            return;
        }

        $currentOrder = optional($all->firstWhere('id', $match->current_turn_session_id))->turn_order;
        $next = $active->first(fn ($s) => $currentOrder === null || $s->turn_order > $currentOrder) ?? $active->first();

        $match->update(['current_turn_session_id' => $next->id, 'turn_started_at' => now()->addMilliseconds($animationDelayMs)]);
    }

    /** Lazily auto-passes an idle turn — checked on every roll()/state() call rather
     *  than via a cron, so a player who never returns doesn't stall the match.
     *  For Rivals Trail specifically, this is also where the 8-consecutive-miss
     *  forfeit is detected — standard (non-wager) matches track nothing extra,
     *  they just keep auto-passing forever exactly as before. */
    public function expireTurnIfNeeded(ArcadeMatch $match): void
    {
        if (!$match->isTurnBased() || !$match->current_turn_session_id || !$match->turn_started_at) return;
        // Still covering the previous roll's animation delay (see advanceTurn())
        // — diffInSeconds() is an ABSOLUTE difference, so without this guard a
        // still-future turn_started_at could read as "already expired" instead
        // of "hasn't even started counting down yet".
        if ($match->turn_started_at->isFuture()) return;
        if ($match->turn_started_at->diffInSeconds(now()) < self::TURN_SECONDS) return;

        $holder = ArcadeSession::find($match->current_turn_session_id);

        if ($holder && $match->isWager() && $holder->isActive()) {
            $holder->increment('missed_turns');
            $holder->refresh();
            if ($holder->missed_turns >= self::FORFEIT_MISSED_TURNS) {
                $this->forfeitSession($match, $holder);
                return; // forfeitSession() already advances the turn and re-settles
            }
        }

        $this->advanceTurn($match);
    }

    /** Withdraws a player from a Rivals Trail round after too many missed turns:
     *  30% of their current pot joins the match's forfeit pool (paid to whoever
     *  eventually wins), they keep the remaining 70% back in their real wallet
     *  immediately — not held for match end, since they're leaving now. */
    private function forfeitSession(ArcadeMatch $match, ArcadeSession $session): void
    {
        DB::transaction(function () use ($match, $session) {
            $lockedMatch = ArcadeMatch::where('id', $match->id)->lockForUpdate()->first();
            $lockedSession = ArcadeSession::where('id', $session->id)->lockForUpdate()->first();

            if (!$lockedSession || !$lockedSession->isActive()) return; // already resolved by a concurrent request

            $cut = (int) round($lockedSession->pot_amount * self::FORFEIT_CUT_PERCENT / 100);
            $remaining = $lockedSession->pot_amount - $cut;

            $lockedMatch->forfeit_pool_amount += $cut;
            $lockedMatch->save();

            $lockedSession->pot_amount = $remaining;
            $lockedSession->status = 'forfeited';
            $lockedSession->ended_at = now();
            $this->awardXp($lockedSession);
            $lockedSession->save();

            $progress = $lockedSession->user->getOrCreateProgress();
            $progress->balance += $remaining;
            $progress->recalculateNetWorth();
            $progress->save();

            GameNotification::create([
                'user_id' => $lockedSession->user_id,
                'type'    => 'arcade_forfeit_penalty',
                'title'   => '🚪 Left a Rivals Trail round early',
                'body'    => 'You missed too many turns and were withdrawn — you kept KES ' . number_format($remaining) . ' of your in-round savings.',
                'icon'    => '🚪',
                // No 'amount' key here on purpose: this notification sits in the
                // neutral/event statement bucket (not income or expense), and the
                // existing statement row always renders a signed amount as a
                // deduction for non-income rows — showing "-KES X" for money the
                // player actually got BACK would misreport it. The body text above
                // already states the KES amount kept in plain language.
                'data'    => ['url' => route('arcade.snakes.lobby')],
            ]);
        });

        $match->refresh();
        $this->advanceTurn($match);
        $this->settleMatchIfDecided($match);
    }

    /**
     * The single choke point for match completion — called from the bottom of
     * roll() and from forfeitSession(). Decides whether the round is over
     * (someone reached the finish tile, or attrition has left exactly one
     * player still active) and, if so, ends the match for everyone: standard
     * matches just flip the remaining active sessions to 'lost' (otherwise
     * they'd keep rolling forever against a match that already has a winner),
     * while Rivals Trail (wager) matches additionally move money — 60% of
     * each remaining opponent's pot to the winner, the opponents' other 40%
     * back to their own wallets, plus the entire forfeit pool.
     *
     * Returns a settlement summary (winner_session_id/winner_gain/forfeit_bonus)
     * when the match was just decided by THIS call, or null if it was already
     * decided/still in progress — callers use a non-null return to know their
     * in-memory $session is now stale and needs refreshing.
     */
    public function settleMatchIfDecided(ArcadeMatch $match): ?array
    {
        return DB::transaction(function () use ($match) {
            $lockedMatch = ArcadeMatch::where('id', $match->id)->lockForUpdate()->first();
            if (!$lockedMatch || $lockedMatch->status === 'completed') return null; // idempotency guard

            $sessions = ArcadeSession::where('arcade_match_id', $lockedMatch->id)->lockForUpdate()->get();
            $winner = $sessions->firstWhere('status', 'won');
            $stillActive = $sessions->where('status', 'active');

            if (!$winner && $stillActive->count() > 1) return null; // mid-game — not decided yet

            if (!$winner && $stillActive->count() === 1) {
                $winner = $stillActive->first();
                $winner->status = 'won';
            }
            // Structurally unreachable under the single-actor turn model (see
            // ArcadeSnakesService design notes): every path that can drop active
            // count to 0 or 1 does so via a roll/forfeit that itself immediately
            // triggers this method. If it somehow still happens, do nothing rather
            // than crediting an arbitrary "winner" — money stays untouched and the
            // match simply stays open for the next poll to re-evaluate.
            if (!$winner) return null;

            if (!$lockedMatch->isWager()) {
                // No money to move, but the race is still over — every other
                // active session would otherwise keep rolling forever against a
                // match that already has a winner, since nothing else ever
                // flips their status or completes the match for standard mode.
                foreach ($sessions as $s) {
                    if ($s->id === $winner->id || $s->status !== 'active') continue;
                    $s->status = 'lost';
                    $s->ended_at = now();
                    $this->awardXp($s);
                    $s->save();
                }
                $winner->ended_at ??= now();
                $winner->save();

                $lockedMatch->status = 'completed';
                $lockedMatch->current_turn_session_id = null;
                $lockedMatch->save();

                return ['winner_session_id' => $winner->id, 'winner_gain' => 0, 'forfeit_bonus' => 0];
            }

            $winnerGain = 0;
            foreach ($sessions as $s) {
                if ($s->id === $winner->id || $s->status !== 'active') continue;

                $cut = (int) round($s->pot_amount * self::WINNER_CUT_PERCENT / 100);
                $s->pot_amount -= $cut;
                $s->status = 'lost';
                $s->ended_at = now();
                $this->awardXp($s);
                // Persisted (not just used for the notification below) so a loser
                // who learns about this via a later poll — the decisive roll was
                // someone else's, or the match ended by an opponent's forfeit —
                // can still see who beat them and by how much: state() reads it
                // back off this same column, same pattern as the winner's event
                // just below.
                $loserEvents = is_array($s->last_event) ? $s->last_event : [];
                $loserEvents[] = ['type' => 'settlement', 'amount_lost' => $cut, 'winner_name' => $winner->user->name ?? 'the other player'];
                $s->last_event = $loserEvents;
                $s->save();

                $loserProgress = $s->user->getOrCreateProgress();
                $loserProgress->balance += $s->pot_amount;
                $loserProgress->recalculateNetWorth();
                $loserProgress->save();

                GameNotification::create([
                    'user_id' => $s->user_id,
                    'type'    => 'arcade_stake_lost',
                    'title'   => '📉 Lost a Rivals Trail round',
                    'body'    => 'You kept KES ' . number_format($s->pot_amount) . ' of your in-round savings.',
                    'icon'    => '📉',
                    'data'    => ['url' => route('arcade.snakes.lobby'), 'amount' => $cut],
                ]);

                $winnerGain += $cut;
            }

            $winner->pot_amount += $winnerGain;
            $forfeitBonus = (int) $lockedMatch->forfeit_pool_amount;
            if ($forfeitBonus > 0) {
                $winner->pot_amount += $forfeitBonus;
                $lockedMatch->forfeit_pool_amount = 0;
            }
            $winner->ended_at ??= now();
            $this->awardXp($winner);
            // Persisted (not just returned) so a winner who learns about this via
            // a later poll — e.g. the decisive event was an opponent's forfeit,
            // not their own roll — can still see the breakdown: state() reads it
            // back off this same column (see ArcadeSnakesController::state()).
            $winnerEvents = is_array($winner->last_event) ? $winner->last_event : [];
            $winnerEvents[] = ['type' => 'settlement', 'winner_gain' => $winnerGain, 'forfeit_bonus' => $forfeitBonus];
            $winner->last_event = $winnerEvents;
            $winner->save();

            $winnerProgress = $winner->user->getOrCreateProgress();
            $winnerProgress->balance += $winner->pot_amount;
            $winnerProgress->recalculateNetWorth();
            $winnerProgress->save();

            if ($winnerGain > 0) {
                GameNotification::create([
                    'user_id' => $winner->user_id,
                    'type'    => 'arcade_stake_won',
                    'title'   => '🏆 Won a Rivals Trail round',
                    'body'    => 'You brought in KES ' . number_format($winnerGain) . ' from the other players.',
                    'icon'    => '🏆',
                    'data'    => ['url' => route('arcade.snakes.lobby'), 'amount' => $winnerGain],
                ]);
            }
            if ($forfeitBonus > 0) {
                GameNotification::create([
                    'user_id' => $winner->user_id,
                    'type'    => 'arcade_forfeit_bonus',
                    'title'   => '🎁 Bonus from players who left early',
                    'body'    => 'KES ' . number_format($forfeitBonus) . " from the round's bonus pool landed in your wallet.",
                    'icon'    => '🎁',
                    'data'    => ['url' => route('arcade.snakes.lobby'), 'amount' => $forfeitBonus],
                ]);
            }

            $lockedMatch->status = 'completed';
            $lockedMatch->current_turn_session_id = null;
            $lockedMatch->save();

            return ['winner_session_id' => $winner->id, 'winner_gain' => $winnerGain, 'forfeit_bonus' => $forfeitBonus];
        });
    }

    /** A random line from the admin-editable arcade_flavor_texts pool (GameSet
     *  Arcade → Flavor Text) for this category — falls back to the tiny
     *  hardcoded set only if the pool is somehow empty, so a landing never
     *  shows blank. Genuinely random each landing (not keyed by tile number
     *  the way the old hardcoded-only version was), so returning players don't
     *  see the exact same line every time they land on the same tile. */
    private function flavorText(int $gameId, string $category, int $tileNumber): string
    {
        return ArcadeFlavorText::randomFor($gameId, $category)
            ?? ($category === 'reward'
                ? self::REWARD_LESSONS[$tileNumber % count(self::REWARD_LESSONS)]
                : self::EXPENSE_LESSONS[$tileNumber % count(self::EXPENSE_LESSONS)]);
    }

    private function applyTileEffect(ArcadeSession $session, ArcadeTile $tile, array &$events, ArcadeGame $game): void
    {
        if (!$session->isActive()) return;

        if ($tile->is_mystery) {
            $outcome = $this->pickMysteryOutcome($game->id);
            if ($outcome) {
                $amount = (int) round($session->pot_amount * $outcome->percent / 100);
                if ($outcome->effect === 'gift') {
                    $session->pot_amount += $amount;
                } else {
                    $session->pot_amount = max(0, $session->pot_amount - $amount);
                }
                $events[] = ['type' => 'mystery', 'effect' => $outcome->effect, 'label' => $outcome->label, 'amount' => $amount, 'tile' => $tile->number];
            }
        } elseif ($tile->money_effect === 'reward') {
            $amount = (int) round($session->pot_amount * $tile->money_percent / 100);
            $session->pot_amount += $amount;
            $events[] = ['type' => 'reward', 'amount' => $amount, 'tile' => $tile->number, 'icon' => $tile->icon, 'label' => $tile->label ?: $this->flavorText($game->id, 'reward', $tile->number)];
        } elseif ($tile->money_effect === 'expense') {
            $amount = (int) round($session->pot_amount * $tile->money_percent / 100);
            $session->pot_amount = max(0, $session->pot_amount - $amount);
            $events[] = ['type' => 'expense', 'amount' => $amount, 'tile' => $tile->number, 'icon' => $tile->icon, 'label' => $tile->label ?: $this->flavorText($game->id, 'expense', $tile->number)];
        }

        if ($tile->is_golden) {
            $assets = $session->session_assets ?? [];
            if (empty($assets['golden_seen'])) {
                $assets['golden_seen'] = true;
                $session->session_assets = $assets;
                $events[] = ['type' => 'golden_first', 'tile' => $tile->number];
            } else {
                $boost = (int) round($session->stake_amount * self::GOLDEN_BOOST_PERCENT / 100);
                $session->pot_amount += $boost;
                $events[] = ['type' => 'golden_boost', 'amount' => $boost, 'tile' => $tile->number];
            }
        }

        $this->checkBust($session, $game, $events);
    }

    private function checkBust(ArcadeSession $session, ArcadeGame $game, array &$events): void
    {
        $floor = (int) round($session->stake_amount * $game->floor_percent / 100);
        if ($session->pot_amount <= $floor) {
            $session->pot_amount = 0;
            $session->status = 'busted';
            $session->ended_at = now();
            $events[] = ['type' => 'bust'];
            $this->awardXp($session);
        }
    }

    private function pickMysteryOutcome(int $gameId)
    {
        $outcomes = DB::table('arcade_mystery_outcomes')->where('arcade_game_id', $gameId)->where('is_active', true)->get();
        if ($outcomes->isEmpty()) return null;

        $totalWeight = $outcomes->sum('weight');
        $roll = random_int(1, max(1, $totalWeight));
        $cursor = 0;
        foreach ($outcomes as $o) {
            $cursor += $o->weight;
            if ($roll <= $cursor) return $o;
        }
        return $outcomes->last();
    }

    /** Voluntary walk-away (banks whatever pot you currently have) or settling an already-ended session. */
    public function cashOut(ArcadeSession $session): array
    {
        if ($session->arcade_match_id) {
            $match = ArcadeMatch::find($session->arcade_match_id);
            if ($match && $match->isWager()) {
                throw new \RuntimeException('Rivals Trail rounds settle automatically — there\'s no manual cash-out to protect your pot from the cut.');
            }
        }

        if ($session->status === 'cashed_out') {
            throw new \RuntimeException('This session was already cashed out.');
        }

        $wasVoluntary = $session->status === 'active';
        if ($wasVoluntary) {
            $session->status = 'cashed_out';
            $session->ended_at = now();
            $this->awardXp($session);
        }

        // Tidy up a solo companion bot when the player's own game ends — it has
        // no strategy of its own, so there's nothing meaningful left for it to do.
        if ($session->arcade_match_id) {
            $botSession = ArcadeSession::where('arcade_match_id', $session->arcade_match_id)
                ->where('is_bot', true)->where('status', 'active')->first();
            if ($botSession) {
                $botSession->status = 'cashed_out';
                $botSession->ended_at = now();
                $botSession->save();
            }
        }

        $payout = $session->pot_amount;

        return DB::transaction(function () use ($session, $payout) {
            $progress = $session->user->getOrCreateProgress();
            $progress->balance += $payout;
            $progress->recalculateNetWorth();
            $progress->save();

            $session->save();

            return ['payout' => $payout, 'xp_awarded' => $session->xp_awarded, 'balance' => $progress->balance, 'status' => $session->status];
        });
    }

    /** Progress-based XP + a status bonus, plus the admin-configurable flat
     *  xp_per_play/xp_per_win knobs (GameSet Arcade settings) added on top of —
     *  not replacing — the tuned curve below. Never zero — even a bust teaches
     *  something worth a few points. */
    private function awardXp(ArcadeSession $session): void
    {
        $game = $session->game;

        $xp = (int) round($session->position / 2);
        $xp += match ($session->status) {
            'won'        => 50,
            'busted'     => 5,
            'cashed_out' => $session->pot_amount > $session->stake_amount ? 10 : 3,
            'lost'       => 8,  // still played a full Rivals Trail round, just didn't win it
            'forfeited'  => 5,  // matches the 'busted' bar — left early, still worth a few points
            default      => 0,
        };
        $xp += (int) ($game->xp_per_play ?? 0);
        if ($session->status === 'won') {
            $xp += (int) ($game->xp_per_win ?? 0);
        }

        $session->xp_awarded = $xp;
        $session->user->getOrCreateProgress()->addPoints($xp);
    }
}
