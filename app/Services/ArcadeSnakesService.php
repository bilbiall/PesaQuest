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
    public const MIN_WAGER_STAKE = 100; // floor only — no ceiling, players can wager any amount their wallet covers

    private const GOLDEN_BOOST_PERCENT = 25; // % of the ORIGINAL stake, added to the pot on every golden landing after the first

    /**
     * Strategy powers (Protect / Boost / Reroll / Bank) — see docs/PESA-TRAIL-POWERS.md
     * for the full design and troubleshooting reference. Deliberately named with a
     * "_POWER"/"POWER_" prefix on anything that could otherwise collide with the
     * unrelated GOLDEN_BOOST_PERCENT tile mechanic above.
     */
    public const PROTECT_USES = 3;
    public const POWER_BOOST_USES = 3;
    public const REROLL_USES = 2;
    public const BANK_USES = 4;
    public const BANK_LIMIT_PERCENT = 20;      // of current pot_amount, per Bank use
    public const POWER_BOOST_MULTIPLIER = 1.5; // applies once to the next reward/mystery-gift gain
    public const DECISION_TIMEOUT_SECONDS = 8; // separate clock from TURN_SECONDS — how long a pending decision waits before auto-skipping

    /** Dead-pool safety net ONLY — see flavorText(). The real, admin-editable,
     *  randomly-picked pool lives in arcade_flavor_texts (GameSet Arcade →
     *  Flavor Text), seeded from these exact lines. These constants stay only
     *  so a landing can never show blank if that table is ever emptied out. */
    private const REWARD_LESSONS = [
        'Consistent saving compounds — small wins add up.',
        'A side hustle payday — extra income is a buffer, not a lifestyle upgrade.',
        'Smart spending freed up cash for this.',
        'Patience paid off here — literally.',
        'One income stream is a paycheck — two is a plan. This is why the second one matters.',
        'Delayed gratification just paid out — the wait was the strategy, not the setback.',
        'That\'s interest working while you slept — the whole point of investing early.',
        'A tracked budget spotted room for this before it even happened.',
        'Reinvesting a windfall instead of spending it all is how small gains become big ones.',
        'Good credit history just opened a door — past discipline is paying you back now.',
        'A chama contribution matured — group discipline beats solo willpower most days.',
        'You waited instead of spending — that\'s opportunity cost working in your favor for once.',
        'A skill you invested time in just turned into cash — that\'s the real return on learning.',
        'Keeping overhead low made this profit bigger than it looks on paper.',
        'Selling something you no longer needed just funded something you do.',
        'A small, boring, regular deposit just beat a flashy one-time gamble.',
    ];
    private const EXPENSE_LESSONS = [
        'Unexpected costs happen — that\'s exactly why an emergency fund matters.',
        'Small leaks sink big ships — track where this went.',
        'A bill you didn\'t plan for — budgeting catches these before they catch you.',
        'This is the cost of not having a buffer ready.',
        'This is what debt costs when it\'s left to compound — interest doesn\'t take days off.',
        'Insurance exists for exactly this moment — a small premium beats a large surprise.',
        'Lifestyle crept up faster than income did — that gap is where this expense lived.',
        'An impulse buy from last week just came due — the cost of a decision outlives the moment.',
        'A late fee is a penalty for a plan that didn\'t account for timing.',
        'That asset lost value the moment you bought it — depreciation doesn\'t wait for you to notice.',
        'Keeping up with everyone else\'s spending is its own kind of bill.',
        'Money borrowed for one thing rarely stays cheap by the time it\'s repaid.',
        'A fee buried in the fine print just found you — always read what you\'re signing.',
        'Prices moved and your shilling bought less than it used to — that\'s inflation, quietly working against you.',
        'Too much borrowed against too little saved — this is what over-leveraging feels like.',
        'All your eggs were in one basket, and the basket just tipped.',
    ];

    public function stakeTierFor(User $user, ArcadeGame $game): ?ArcadeStakeTier
    {
        $progress = $user->getOrCreateProgress();

        return ArcadeStakeTier::forLevel($game->id, $progress->level)
            ?? ArcadeStakeTier::where('arcade_game_id', $game->id)->where('is_active', true)->orderBy('stake_amount')->first();
    }

    public function startSolo(User $user, ArcadeGame $game, ?int $stakeOverride = null): ArcadeSession
    {
        return $this->openSession($user, $game, null, false, 0, $stakeOverride);
    }

    /** Solo play, but with Robo the bot in a private 2-seat turn-based match —
     *  races the player, taking its own turn a couple seconds after the player's
     *  (see autoPlayBotTurn()) rather than moving in lockstep with them.
     *  $stakeOverride lets the player pick their own starting savings instead of
     *  the level-tier default — the bot always mirrors whatever the player ends
     *  up with, so the race stays even either way. */
    public function startSoloWithBot(User $user, ArcadeGame $game, ?int $stakeOverride = null): ArcadeSession
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

        $session = $this->openSession($user, $game, $match, false, 0, $stakeOverride);
        $this->openSession($this->botUser(), $game, $match, true, 1, (int) $session->stake_amount);
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

            // Boost is a pre-roll commitment (per spec timing) — the bot has to
            // "decide" before it knows what it'll land on, same as a human would.
            $this->maybeBotActivateBoost($holder);

            $result = $this->roll($holder);
            $result = $this->resolveBotDecisionsIfAny($holder, $result);
            $result['session_id'] = $holder->id;

            return $result;
        } finally {
            Cache::forget($lockKey);
        }
    }

    /** Bots never expose a pending_decision pause to the UI — they resolve their
     *  own Reroll/Protect/Bank choices synchronously via simple heuristics
     *  (see docs/PESA-TRAIL-POWERS.md §5), inline within this same autoPlayBotTurn()
     *  call, so a human opponent never sees a bot "thinking" about a power. */
    private function maybeBotActivateBoost(ArcadeSession $session): void
    {
        if (!$this->powersEligible($session)) return;
        if ($this->usesLeft($session, 'boost') <= 0) return;
        if (!empty(($session->session_assets ?? [])['boost_active'])) return;
        if (random_int(1, 100) > 40) return; // roughly 40% of turns while uses remain
        $this->activateBoost($session);
    }

    private function resolveBotDecisionsIfAny(ArcadeSession $session, array $result): array
    {
        $guard = 0; // hard ceiling — a turn only ever has at most 2 real pauses (reroll, then protect-or-bank)
        while (($result['pending'] ?? false) && $guard < 5) {
            $guard++;
            $session->refresh();
            [$choice, $amount] = $this->botDecisionFor($session, $session->pending_decision);
            $result = $this->decide($session, $choice, $amount);
        }
        return $result;
    }

    private function botDecisionFor(ArcadeSession $session, array $decision): array
    {
        if ($decision['type'] === 'reroll') {
            return [$this->botWantsReroll($session, $decision['roll'], $decision['from']) ? 'use' : 'skip', null];
        }
        if ($decision['type'] === 'protect') {
            $threshold = (int) round($session->pot_amount * 0.08); // "is this loss big enough to spend a Protect on"
            return [$decision['amount'] > $threshold ? 'use' : 'skip', null];
        }
        if ($decision['type'] === 'bank') {
            $bankLeft = $this->usesLeft($session, 'bank');
            if ($bankLeft > 2) return ['use', $decision['cap']]; // eager early, more conservative once low
            return [random_int(1, 100) <= 30 ? 'use' : 'skip', $decision['cap']];
        }
        return ['skip', null];
    }

    private function botWantsReroll(ArcadeSession $session, int $roll, int $from): bool
    {
        $game = ArcadeGame::find($session->arcade_game_id);
        if (!$game) return false;
        $target = $from + $roll;
        if ($target >= $game->tile_count) return false; // never reroll away from a win or near-finish
        $tile = ArcadeTile::where('arcade_game_id', $game->id)->where('number', $target)->first();
        if (!$tile) return false;
        if ($tile->movement_role === 'snake_head') return true;
        if ($tile->money_effect === 'expense' && $tile->money_percent >= 15) return true;
        return false;
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
        $stake = max(self::MIN_WAGER_STAKE, $stakeAmount);

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
                'banked_amount'   => 0,
                'position'        => 0,
                'status'          => 'active',
                'started_at'      => now(),
                // Seeded on every session regardless of match size — cheap, and
                // powersEligible() is what actually gates whether they're ever
                // offered, not whether the counters exist (see docs/PESA-TRAIL-POWERS.md).
                'session_assets'  => [
                    'protect_left' => self::PROTECT_USES,
                    'boost_left'   => self::POWER_BOOST_USES,
                    'reroll_left'  => self::REROLL_USES,
                    'bank_left'    => self::BANK_USES,
                    'boost_active' => false,
                ],
            ]);
        });
    }

    /** Roll the die and resolve the landing. Returns a small event log for the UI to
     *  animate — OR, if a strategy power decision point is hit (Reroll before movement,
     *  Protect before a loss, Bank after a gain), a `pending: true` payload describing
     *  the decision instead, with the turn left un-advanced until decide() resolves it.
     *  See docs/PESA-TRAIL-POWERS.md §4 for the full pause/resume design. */
    public function roll(ArcadeSession $session): array
    {
        if (!$session->isActive()) {
            throw new \RuntimeException('This session has already ended.');
        }
        if ($session->pending_decision) {
            throw new \RuntimeException('Resolve your pending strategy decision before rolling again.');
        }

        // A double-tap, a retried slow request, or a browser somehow firing the
        // same roll twice must never both execute — either would advance the
        // board/turn twice for one real action and leave two players' clients
        // disagreeing about whose turn it even is. Auto-expires after 10s (far
        // longer than a real roll takes) so a mid-roll exception can never wedge
        // this open beyond a few seconds. Held across the WHOLE pipeline (through
        // decide() too, see below) rather than just this method, since a paused
        // roll is still "in progress" from the double-submission-guard's point of view.
        $lockKey = "arcade_roll_lock_{$session->id}";
        if (!Cache::add($lockKey, true, 10)) {
            throw new \RuntimeException("You're already rolling — hang on a second.");
        }

        try {
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

            $rollValue = random_int(1, 6);
            $from = $session->position;

            if ($this->powersEligible($session, $match) && $this->usesLeft($session, 'reroll') > 0) {
                $this->pauseDecision($session, ['type' => 'reroll', 'roll' => $rollValue, 'from' => $from]);
                return $this->pendingPayload($session, []);
            }

            return $this->continueAfterReroll($session, $match, $rollValue, $from, []);
        } finally {
            Cache::forget($lockKey);
        }
    }

    /** Resolves a pending Reroll/Protect/Bank decision and resumes the roll pipeline
     *  from exactly where it paused. $amount is only meaningful for a Bank decision
     *  (how much of the current pot to secure, capped server-side regardless of what's sent). */
    public function decide(ArcadeSession $session, string $choice, ?int $amount = null): array
    {
        if (!$session->pending_decision) {
            throw new \RuntimeException('There is no decision waiting to be made.');
        }
        if (!in_array($choice, ['use', 'skip'], true)) {
            throw new \RuntimeException('Invalid decision.');
        }

        $lockKey = "arcade_roll_lock_{$session->id}";
        if (!Cache::add($lockKey, true, 10)) {
            throw new \RuntimeException("Still processing your last action — hang on a second.");
        }

        try {
            $match = $session->arcade_match_id ? ArcadeMatch::find($session->arcade_match_id) : null;
            return $this->resumeFromDecision($session, $match, $choice, $amount);
        } finally {
            Cache::forget($lockKey);
        }
    }

    /** Activates Boost for the player's NEXT roll — must happen before roll() is
     *  called, per the spec's timing table (the player doesn't yet know what tile
     *  they'll land on). Consumed on the next reward/mystery-gift gain regardless
     *  of the outcome, same as a human committing to it blind. */
    public function activateBoost(ArcadeSession $session): array
    {
        if (!$session->isActive()) {
            throw new \RuntimeException('This session has already ended.');
        }
        if ($session->pending_decision) {
            throw new \RuntimeException('Resolve your pending strategy decision first.');
        }
        if (!$this->powersEligible($session)) {
            throw new \RuntimeException('Strategy powers are only available in matches with an opponent.');
        }

        $match = $session->arcade_match_id ? ArcadeMatch::find($session->arcade_match_id) : null;
        if ($match && $match->isTurnBased() && $match->current_turn_session_id && $match->current_turn_session_id !== $session->id) {
            throw new \RuntimeException("It's not your turn yet.");
        }

        $assets = $session->session_assets ?? [];
        if (($assets['boost_left'] ?? 0) <= 0) {
            throw new \RuntimeException('No Boost uses left.');
        }
        if (!empty($assets['boost_active'])) {
            throw new \RuntimeException('Boost is already armed for your next roll.');
        }

        $assets['boost_active'] = true;
        $assets['boost_left'] = $assets['boost_left'] - 1;
        $session->session_assets = $assets;
        $session->save();

        return $this->powersPayload($session);
    }

    /** Are strategy powers offered at all for this session? Gated to any real
     *  match (solo-vs-bot, 1v1, or 2-8 player standard/wager lobbies) — excludes
     *  only the rare, currently-unrouted match-less solo session, which has no
     *  opponent for Protect/Boost/Reroll/Bank's dynamics to mean anything
     *  against. Originally scoped to 1v1-only for the first playtest (see
     *  docs/PESA-TRAIL-POWERS.md §2), relaxed to any size once the settlement/
     *  turn-advance code was confirmed to already handle N players uniformly —
     *  this is the single choke point for that gate, nothing else should
     *  re-implement this check. */
    public function powersEligible(ArcadeSession $session, ?ArcadeMatch $match = null): bool
    {
        if (!$session->arcade_match_id) return false;
        $match ??= ArcadeMatch::find($session->arcade_match_id);
        return (bool) $match;
    }

    /** Remaining-use counters + banked balance, for both the play view's power
     *  tray and state() polling. Public — the controller reads this directly. */
    public function powersPayload(ArcadeSession $session): array
    {
        $a = $session->session_assets ?? [];
        return [
            'protect_left'  => (int) ($a['protect_left'] ?? 0),
            'boost_left'    => (int) ($a['boost_left'] ?? 0),
            'reroll_left'   => (int) ($a['reroll_left'] ?? 0),
            'bank_left'     => (int) ($a['bank_left'] ?? 0),
            'boost_active'  => (bool) ($a['boost_active'] ?? false),
            'banked_amount' => $session->banked_amount,
            'eligible'      => $this->powersEligible($session),
        ];
    }

    private function usesLeft(ArcadeSession $session, string $key): int
    {
        return (int) (($session->session_assets ?? [])["{$key}_left"] ?? 0);
    }

    private function decrementUse(ArcadeSession $session, string $key): void
    {
        $assets = $session->session_assets ?? [];
        $assets["{$key}_left"] = max(0, ($assets["{$key}_left"] ?? 0) - 1);
        $session->session_assets = $assets;
    }

    private function pauseDecision(ArcadeSession $session, array $decision): void
    {
        $session->pending_decision = $decision;
        $session->decision_started_at = now();
        $session->save();
    }

    /** The envelope returned to the client whenever roll()/decide() pauses instead
     *  of completing — always carries the current (already-persisted) position/pot/
     *  banked amount, so the client can animate up to this point before showing the
     *  decision prompt. */
    private function pendingPayload(ArcadeSession $session, array $events, ?int $from = null, ?int $firstLanding = null, array $hopPath = []): array
    {
        $decision = $session->pending_decision;

        return [
            'pending' => true,
            'decision' => match ($decision['type']) {
                'reroll'  => ['type' => 'reroll', 'roll' => $decision['roll']],
                'protect' => ['type' => 'protect', 'amount' => $decision['amount']],
                'bank'    => ['type' => 'bank', 'cap' => $decision['cap']],
                default   => ['type' => $decision['type']],
            },
            'roll' => $decision['roll'] ?? null,
            'from' => $from, 'first_landing' => $firstLanding, 'hop_path' => $hopPath,
            'events' => $events, 'position' => $session->position, 'pot' => $session->pot_amount,
            'banked' => $session->banked_amount, 'status' => $session->status,
            'powers' => $this->powersPayload($session),
        ];
    }

    /** Applies the player's choice for whichever decision is currently pending, then
     *  resumes the roll pipeline from exactly that point — a decision resolves within
     *  the SAME call that raised it (via decide() or the timeout sweep), it never
     *  needs a third round trip. */
    private function resumeFromDecision(ArcadeSession $session, ?ArcadeMatch $match, string $choice, ?int $amount): array
    {
        $pending = $session->pending_decision;
        $session->pending_decision = null;
        $session->decision_started_at = null;

        if ($pending['type'] === 'reroll') {
            $rollValue = $pending['roll'];
            $events = [];
            if ($choice === 'use') {
                $this->decrementUse($session, 'reroll');
                $rollValue = random_int(1, 6);
                $events[] = ['type' => 'reroll_used', 'original' => $pending['roll'], 'new' => $rollValue];
            } else {
                $events[] = ['type' => 'reroll_skipped'];
            }
            // No second reroll offer this turn regardless of the new value — the
            // reroll gate only lives at the top of roll(), never re-entered here.
            return $this->continueAfterReroll($session, $match, $rollValue, $pending['from'], $events);
        }

        if ($pending['type'] === 'protect') {
            $events = $pending['events'] ?? [];
            if ($choice === 'use') {
                $this->decrementUse($session, 'protect');
                $events[] = ['type' => 'protect_used', 'amount_saved' => $pending['amount'], 'tile' => $pending['tile_number']];
            } else {
                $session->pot_amount = max(0, $session->pot_amount - $pending['amount']);
                $events[] = $pending['loss_event'];
            }
            return $this->resumePrimaryEffectFromContext($session, $match, $pending, $events);
        }

        if ($pending['type'] === 'bank') {
            $events = $pending['events'] ?? [];
            $bankAmount = $choice === 'use' ? max(0, min($amount ?? 0, $pending['cap'])) : 0;
            if ($bankAmount > 0) {
                $this->decrementUse($session, 'bank');
                $session->pot_amount -= $bankAmount;
                $session->banked_amount += $bankAmount;
                $events[] = ['type' => 'bank_used', 'amount' => $bankAmount];
            } else {
                $events[] = ['type' => 'bank_skipped'];
            }
            return $this->resumePrimaryEffectFromContext($session, $match, $pending, $events);
        }

        throw new \RuntimeException('Unknown pending decision.');
    }

    private function resumePrimaryEffectFromContext(ArcadeSession $session, ?ArcadeMatch $match, array $pending, array $events): array
    {
        $game = ArcadeGame::findOrFail($session->arcade_game_id);
        $tile = ArcadeTile::where('arcade_game_id', $game->id)->where('number', $pending['tile_number'])->first();

        return $this->continueAfterPrimaryEffect(
            $session, $match, $tile, $pending['from'], $pending['roll'], $pending['first_landing'], $pending['hop_path'], $events, $game
        );
    }

    /** Movement + primary-landing-tile resolution — runs fresh from roll(), or resumed
     *  (with a possibly-changed $rollValue) after a Reroll decision. */
    private function continueAfterReroll(ArcadeSession $session, ?ArcadeMatch $match, int $rollValue, int $from, array $events): array
    {
        $game      = ArcadeGame::findOrFail($session->arcade_game_id);
        $tileCount = $game->tile_count;
        $target    = $from + $rollValue;
        $firstLanding = $target;
        $hopPath = [];

        if ($target > $tileCount) {
            $needed    = $tileCount - $from;
            $overshoot = $rollValue - $needed;
            $newPosition = $tileCount - $overshoot;
            $firstLanding = $tileCount;
            $events[] = ['type' => 'overshoot', 'roll' => $rollValue, 'needed' => $needed, 'bounced_to' => $newPosition];
            $session->position = $newPosition;
            $hopPath = array_merge(range($from + 1, $tileCount), range($tileCount - 1, $newPosition, -1));
            return $this->finalizeRoll($session, $match, $rollValue, $from, $firstLanding, $hopPath, $events);
        }

        if ($target === $tileCount) {
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
            return $this->finalizeRoll($session, $match, $rollValue, $from, $firstLanding, $hopPath, $events);
        }

        $session->position = $target;
        $hopPath = range($from + 1, $target);
        $tile = ArcadeTile::where('arcade_game_id', $game->id)->where('number', $target)->first();

        if (!$tile) {
            return $this->finalizeRoll($session, $match, $rollValue, $from, $firstLanding, $hopPath, $events);
        }

        $preview  = $this->previewTileOutcome($session, $tile);
        $powersOn = $this->powersEligible($session, $match);

        if ($preview['kind'] === 'loss') {
            if ($powersOn && $this->usesLeft($session, 'protect') > 0) {
                $this->pauseDecision($session, [
                    'type' => 'protect', 'tile_number' => $tile->number, 'amount' => $preview['amount'],
                    'loss_event' => $this->buildEffectEvent($tile, $preview, $preview['amount'], $game),
                    'events' => $events, 'from' => $from, 'roll' => $rollValue,
                    'first_landing' => $firstLanding, 'hop_path' => $hopPath,
                ]);
                return $this->pendingPayload($session, $events, $from, $firstLanding, $hopPath);
            }

            $session->pot_amount = max(0, $session->pot_amount - $preview['amount']);
            $events[] = $this->buildEffectEvent($tile, $preview, $preview['amount'], $game);
            return $this->continueAfterPrimaryEffect($session, $match, $tile, $from, $rollValue, $firstLanding, $hopPath, $events, $game);
        }

        if ($preview['kind'] === 'gain') {
            $amount = $preview['amount'];
            $assets = $session->session_assets ?? [];
            $boosted = !empty($assets['boost_active']);
            if ($boosted) {
                $amount = (int) round($amount * self::POWER_BOOST_MULTIPLIER);
                $assets['boost_active'] = false;
                $session->session_assets = $assets;
            }
            $session->pot_amount += $amount;
            $events[] = $this->buildEffectEvent($tile, $preview, $amount, $game);
            if ($boosted) $events[] = ['type' => 'boost_consumed', 'amount' => $amount];

            if ($powersOn && $this->usesLeft($session, 'bank') > 0) {
                $this->pauseDecision($session, [
                    'type' => 'bank', 'tile_number' => $tile->number,
                    'cap' => (int) round($session->pot_amount * self::BANK_LIMIT_PERCENT / 100),
                    'events' => $events, 'from' => $from, 'roll' => $rollValue,
                    'first_landing' => $firstLanding, 'hop_path' => $hopPath,
                ]);
                return $this->pendingPayload($session, $events, $from, $firstLanding, $hopPath);
            }
            return $this->continueAfterPrimaryEffect($session, $match, $tile, $from, $rollValue, $firstLanding, $hopPath, $events, $game);
        }

        return $this->continueAfterPrimaryEffect($session, $match, $tile, $from, $rollValue, $firstLanding, $hopPath, $events, $game);
    }

    /** Golden-tile + bust checks for the primary landing tile, then the automatic
     *  (never paused — see docs/PESA-TRAIL-POWERS.md §4) snake/ladder chain tile,
     *  then hands off to finalizeRoll(). */
    private function continueAfterPrimaryEffect(ArcadeSession $session, ?ArcadeMatch $match, ?ArcadeTile $tile, int $from, int $rollValue, int $firstLanding, array $hopPath, array $events, ArcadeGame $game): array
    {
        if (!$tile) {
            return $this->finalizeRoll($session, $match, $rollValue, $from, $firstLanding, $hopPath, $events);
        }

        $this->applyGoldenIfNeeded($session, $tile, $events);
        $this->checkBust($session, $game, $events);

        if ($session->isActive() && $tile->movement_role !== 'none' && $tile->target_number) {
            $events[] = ['type' => 'move', 'via' => $tile->movement_role, 'from' => $tile->number, 'to' => $tile->target_number];
            $session->position = $tile->target_number;
            $destTile = ArcadeTile::where('arcade_game_id', $game->id)->where('number', $tile->target_number)->first();
            if ($destTile) {
                $this->applyTileEffect($session, $destTile, $events, $game);
            }
        }

        return $this->finalizeRoll($session, $match, $rollValue, $from, $firstLanding, $hopPath, $events);
    }

    /** Persists the roll, advances the turn, and settles the match if this roll
     *  decided it — the tail end every path through the pipeline funnels into. */
    private function finalizeRoll(ArcadeSession $session, ?ArcadeMatch $match, int $rollValue, int $from, int $firstLanding, array $hopPath, array $events): array
    {
        $session->last_roll = $rollValue;
        $session->last_event = $events;
        $session->missed_turns = 0; // a completed roll clears any prior missed-turn count
        $session->pending_decision = null;
        $session->decision_started_at = null;
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

        $wonHere = $settlement && $settlement['winner_session_id'] === $session->id;

        return [
            'pending' => false,
            'roll' => $rollValue, 'from' => $from, 'first_landing' => $firstLanding, 'hop_path' => $hopPath,
            'events' => $events, 'position' => $session->position, 'pot' => $session->pot_amount,
            'banked' => $session->banked_amount, 'status' => $session->status,
            'winner_gain' => $wonHere ? $settlement['winner_gain'] : null,
            'forfeit_bonus' => $wonHere ? $settlement['forfeit_bonus'] : null,
            'powers' => $this->powersPayload($session),
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

        $holder = ArcadeSession::find($match->current_turn_session_id);

        // A player mid-decision (Reroll/Protect/Bank) still legitimately holds
        // the turn — their own decision-timeout clock governs them, not the
        // outer turn clock, so a poll landing here must never force-advance the
        // turn away from someone who's actively deciding.
        if ($holder && $holder->pending_decision) {
            $this->expireDecisionIfNeeded($holder, $match);
            return;
        }

        // Still covering the previous roll's animation delay (see advanceTurn())
        // — diffInSeconds() is an ABSOLUTE difference, so without this guard a
        // still-future turn_started_at could read as "already expired" instead
        // of "hasn't even started counting down yet".
        if ($match->turn_started_at->isFuture()) return;
        if ($match->turn_started_at->diffInSeconds(now()) < self::TURN_SECONDS) return;

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

    /** Auto-resolves a stale pending decision as a skip once DECISION_TIMEOUT_SECONDS
     *  has passed — the fail-safe from docs/PESA-TRAIL-POWERS.md §2: a timeout must
     *  NEVER resolve as "use", only ever as "skip", so a dropped connection can't
     *  accidentally burn a power use or apply an effect the player never confirmed. */
    private function expireDecisionIfNeeded(ArcadeSession $session, ?ArcadeMatch $match): void
    {
        if (!$session->pending_decision || !$session->decision_started_at) return;
        if ($session->decision_started_at->diffInSeconds(now()) < self::DECISION_TIMEOUT_SECONDS) return;

        $lockKey = "arcade_roll_lock_{$session->id}";
        if (!Cache::add($lockKey, true, 10)) return; // a real decide() call is already resolving it

        try {
            $this->resumeFromDecision($session, $match, 'skip', null);
        } finally {
            Cache::forget($lockKey);
        }
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

            // The 30% forfeit cut only ever comes off pot_amount — banked_amount
            // stays untouchable here too, same rule as the 60% winner's claim
            // (see settleMatchIfDecided()), so Bank protects a player even when
            // THEY are the one leaving early, not just when they win.
            $cut = (int) round($lockedSession->pot_amount * self::FORFEIT_CUT_PERCENT / 100);
            $remaining = $lockedSession->pot_amount - $cut;
            $keptTotal = $remaining + $lockedSession->banked_amount;

            $lockedMatch->forfeit_pool_amount += $cut;
            $lockedMatch->save();

            $lockedSession->pot_amount = $remaining;
            $lockedSession->status = 'forfeited';
            $lockedSession->ended_at = now();
            $this->awardXp($lockedSession);
            $lockedSession->save();

            $progress = $lockedSession->user->getOrCreateProgress();
            $progress->balance += $keptTotal;
            $progress->recalculateNetWorth();
            $progress->save();

            GameNotification::create([
                'user_id' => $lockedSession->user_id,
                'type'    => 'arcade_forfeit_penalty',
                'title'   => '🚪 Left a Rivals Trail round early',
                'body'    => 'You missed too many turns and were withdrawn — you kept KES ' . number_format($keptTotal) . ' of your in-round savings.',
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

                // The cut is computed off pot_amount ONLY — banked_amount is never
                // part of this arithmetic. That's the entire point of Bank (see
                // docs/PESA-TRAIL-POWERS.md §6): money a player secured is
                // protected from the opponent's claim.
                $cut = (int) round($s->pot_amount * self::WINNER_CUT_PERCENT / 100);
                $s->pot_amount -= $cut;
                $s->status = 'lost';
                $s->ended_at = now();
                $this->awardXp($s);
                $keptTotal = $s->pot_amount + $s->banked_amount;
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
                $loserProgress->balance += $keptTotal;
                $loserProgress->recalculateNetWorth();
                $loserProgress->save();

                GameNotification::create([
                    'user_id' => $s->user_id,
                    'type'    => 'arcade_stake_lost',
                    'title'   => '📉 Lost a Rivals Trail round',
                    'body'    => 'You kept KES ' . number_format($keptTotal) . ' of your in-round savings.',
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
            $winnerProgress->balance += $winner->pot_amount + $winner->banked_amount;
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

    /** Auto-applies a tile's effect with no strategy-power pause — used ONLY for the
     *  chained snake/ladder destination tile (see continueAfterPrimaryEffect()). The
     *  PRIMARY landing tile goes through previewTileOutcome()/buildEffectEvent()
     *  instead so Protect/Bank can pause before this same math runs — see
     *  docs/PESA-TRAIL-POWERS.md §4 for why the chain tile is deliberately excluded. */
    private function applyTileEffect(ArcadeSession $session, ArcadeTile $tile, array &$events, ArcadeGame $game): void
    {
        if (!$session->isActive()) return;

        $preview = $this->previewTileOutcome($session, $tile);
        if ($preview['kind'] === 'gain') {
            $session->pot_amount += $preview['amount'];
            $events[] = $this->buildEffectEvent($tile, $preview, $preview['amount'], $game);
        } elseif ($preview['kind'] === 'loss') {
            $session->pot_amount = max(0, $session->pot_amount - $preview['amount']);
            $events[] = $this->buildEffectEvent($tile, $preview, $preview['amount'], $game);
        }

        $this->applyGoldenIfNeeded($session, $tile, $events);
        $this->checkBust($session, $game, $events);
    }

    /** Computes what a tile WOULD do (including rolling a mystery outcome, so the
     *  player is shown a concrete amount — see spec: "the player knows exactly what
     *  they are protecting themselves from") without mutating pot_amount. Callers
     *  decide whether to pause for Protect/Bank before applying it via buildEffectEvent(). */
    private function previewTileOutcome(ArcadeSession $session, ArcadeTile $tile): array
    {
        if ($tile->is_mystery) {
            $outcome = $this->pickMysteryOutcome($session->arcade_game_id);
            if (!$outcome) return ['kind' => 'none'];
            $amount = (int) round($session->pot_amount * $outcome->percent / 100);
            return [
                'kind' => $outcome->effect === 'gift' ? 'gain' : 'loss',
                'amount' => $amount,
                'mystery' => ['effect' => $outcome->effect, 'label' => $outcome->label],
            ];
        }
        if ($tile->money_effect === 'reward') {
            return ['kind' => 'gain', 'amount' => (int) round($session->pot_amount * $tile->money_percent / 100)];
        }
        if ($tile->money_effect === 'expense') {
            return ['kind' => 'loss', 'amount' => (int) round($session->pot_amount * $tile->money_percent / 100)];
        }
        return ['kind' => 'none'];
    }

    /** Builds the event log entry for a preview, using the FINAL amount (which may
     *  differ from $preview['amount'] if Boost multiplied a gain) so the event log
     *  always reflects what actually happened to pot_amount. */
    private function buildEffectEvent(ArcadeTile $tile, array $preview, int $amount, ArcadeGame $game): array
    {
        if (!empty($preview['mystery'])) {
            return ['type' => 'mystery', 'effect' => $preview['mystery']['effect'], 'label' => $preview['mystery']['label'], 'amount' => $amount, 'tile' => $tile->number];
        }
        if ($preview['kind'] === 'gain') {
            return ['type' => 'reward', 'amount' => $amount, 'tile' => $tile->number, 'icon' => $tile->icon, 'label' => $tile->label ?: $this->flavorText($game->id, 'reward', $tile->number)];
        }
        return ['type' => 'expense', 'amount' => $amount, 'tile' => $tile->number, 'icon' => $tile->icon, 'label' => $tile->label ?: $this->flavorText($game->id, 'expense', $tile->number)];
    }

    private function applyGoldenIfNeeded(ArcadeSession $session, ArcadeTile $tile, array &$events): void
    {
        if (!$session->isActive() || !$tile->is_golden) return;

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

        // banked_amount is included here too — it's the only other code path
        // (besides settleMatchIfDecided()) that ever converts a session's money
        // back into real wallet balance, so leaving it out would strand a
        // standard-mode player's banked savings forever.
        $payout = $session->pot_amount + $session->banked_amount;

        return DB::transaction(function () use ($session, $payout) {
            $progress = $session->user->getOrCreateProgress();
            $progress->balance += $payout;
            $progress->recalculateNetWorth();
            $progress->save();

            $session->save();

            return ['payout' => $payout, 'banked' => $session->banked_amount, 'xp_awarded' => $session->xp_awarded, 'balance' => $progress->balance, 'status' => $session->status];
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
