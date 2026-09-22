# Pesa Trail Strategy Powers — Build & Reference Doc

**Status: implemented and browser-tested (Sept 2026), pending only the migration on the
live server.** This is the reference point for the Protect / Boost / Reroll / Bank feature —
read this first if a power misbehaves in production, before re-deriving the design from
scratch or from the original spec PDF.

Source spec: `Pesa Trail Feature Specification.pdf` (user-supplied, Sept 2026). Game is
internally still named "Snakes & Cash" in code — routes, classes, and the `arcade_*` tables
all keep that name; only the player-facing display name is "Pesa Trail". This doc uses the
code names.

**Player-facing companion doc**: `docs/PESA-TRAIL-POWERUPS-GUIDE.md` — plain-language
explanation of how each power works and how to play them well, for players/teachers rather
than engineers. Written from the same source of truth as this file; update both if the
mechanics change.

---

## 1. What this feature is

Four player-activated powers layered onto the existing 1v1 wager race
(`ArcadeSnakesService`), plus a "Money Mined" leaderboard:

| Power | Timing | Uses/game | Effect |
|---|---|---|---|
| 🚀 Boost | Before rolling | 3 | Next reward/mystery-gift gain ×1.5 |
| 🎲 Reroll | After seeing the die, before movement | 2 | Roll again once, no second offer |
| 🛡 Protect | After landing on a negative effect, before the loss applies | 3 | Prevents that one loss |
| 🏦 Bank | After landing on a positive effect | 4 | Move up to 20% of current pot to a secured, untouchable balance |

Design intent (from the spec, keep this in mind for any future tuning): **luck decides what
happens to you, strategy decides how you respond to it.** The powers must never let a player
control *when* the race ends or remove the dice's role in movement — they only change how a
player reacts to a result that already happened (or is about to be rolled, for Boost).

## 2. Decisions already made (do not re-litigate without a reason)

1. **Full pause-and-resume turn flow** — not a simplified pre-declare version. A roll can
   pause mid-resolution waiting for a human decision.
2. **1v1 only for the first playtest build (Sept 22), relaxed to any match size same day.**
   Powers were initially gated to matches with exactly 2 sessions to keep the first build's
   surface area small. Once N-player settlement/turn-advance code was confirmed to already
   handle more than 2 sessions uniformly (the wager cut loop iterates "every other active
   session," not "the one opponent" — see §8), the gate was relaxed. Powers now apply to
   solo-vs-bot, 1v1, and 2-8 player standard/wager lobbies alike.
3. **Decision timeouts always default to "skip", never "use".** A stuck/expired decision
   must never auto-consume a power or auto-apply an effect the player didn't confirm. This is
   the single most important fairness rule in the whole feature — if you're debugging a bug
   where a power fired without the player choosing it, this rule was violated somewhere.

## 3. Data model

New columns on `arcade_sessions`:

- `banked_amount` (int, default 0) — money the player has secured. Kept fully separate from
  `pot_amount` (the "Active Balance") so it's *structurally* excluded from tile effects and
  from the opponent's 60% claim — nothing needs to remember to exclude it, no code path
  touches `banked_amount` except the Bank decision handler and final settlement payout.
- `pending_decision` (json, nullable) — `{type: 'reroll'|'protect'|'bank', ...context}` when a
  roll is paused mid-resolution waiting on the current turn holder. `null` when nothing is
  pending.
- `decision_started_at` (timestamp, nullable) — when the pending decision was raised; used by
  the decision-timeout check, same pattern as `turn_started_at` / `expireTurnIfNeeded()`.

Power counters and Boost's armed state live in the existing `session_assets` json column
(already holds `golden_seen`):

```json
{"protect_left": 3, "boost_left": 3, "reroll_left": 2, "bank_left": 4, "boost_active": false}
```

Seeded in `openSession()`. New constants on `ArcadeSnakesService`, mirroring the existing
`WINNER_CUT_PERCENT` style:

```php
public const PROTECT_USES = 3;
public const BOOST_USES = 3;
public const REROLL_USES = 2;
public const BANK_USES = 4;
public const BANK_LIMIT_PERCENT = 20;   // of current pot_amount, per Bank use
public const BOOST_GAIN_MULTIPLIER = 1.5; // applies once to the next reward/mystery-gift gain
public const DECISION_TIMEOUT_SECONDS = 8; // separate clock from TURN_SECONDS
```

⚠️ **Naming collision to watch for**: the tile system already has a `GOLDEN_BOOST_PERCENT`
constant and `golden_boost` event type for the unrelated golden-tile mechanic. Do not let the
new Boost power's code, event types, or session_assets keys collide with those — e.g. use
`boost_power_active` / event type `power_boost_applied` if `boost_active` / `boost` ever reads
ambiguous in a diff.

## 4. The roll() pause/resume mechanism

`roll()` today is one atomic call: roll → move → resolve tile → advance turn → settle. It
needs to be able to stop partway through and wait for a human.

**Mechanism**: when a stage hits a decision point, it writes `pending_decision` +
`decision_started_at`, does **not** call `advanceTurn()` or `settleMatchIfDecided()`, and
returns to the client. The turn's own `TURN_SECONDS` clock is bumped forward so it doesn't
burn down while the player is deciding (same technique `advanceTurn()`'s
`$animationDelayMs` already uses).

New endpoint: `POST /arcade/snakes/{session}/decide` — body: `{type, choice: 'use'|'skip',
amount?}` (amount only for Bank). Resolving it clears `pending_decision` and resumes `roll()`
from that stage with the decision applied.

**Per-power pause points, in resolution order:**

1. **Boost** is not a pause point — it's a flag set by a *separate* pre-roll request, before
   `roll()` is even called. If `boost_active` is true when a reward/mystery-gift gain is about
   to apply, multiply it by `BOOST_GAIN_MULTIPLIER`, then clear the flag — consumed regardless
   of whether the roll turned out to be a gain or not (the player committed before knowing).
2. **Reroll**: right after `$rollValue = random_int(1, 6)`, before `$target`/movement math, if
   `reroll_left > 0`, pause with `pending_decision = {type: 'reroll', roll: $rollValue}`.
   `skip` continues with the original value. `use` decrements `reroll_left`, rerolls once, and
   **must not offer Reroll again this turn** regardless of the new value.
3. **Protect**: after tile lookup resolves to a loss (an `expense` tile or a mystery outcome
   with `effect !== 'gift'`), before `pot_amount` is decremented, if `protect_left > 0`, pause
   with `pending_decision = {type: 'protect', tile, amount}`. `use` skips the deduction
   entirely and decrements `protect_left`; `skip` applies the loss normally.
4. **Bank**: after a reward/mystery-gift gain has already applied to `pot_amount`, if
   `bank_left > 0`, pause with `pending_decision = {type: 'bank', cap: round(pot_amount *
   BANK_LIMIT_PERCENT / 100)}`. Player responds with an amount `0 <= amount <= cap`.
   `pot_amount -= amount; banked_amount += amount`. **Only decrement `bank_left` if `amount >
   0`** — declining (amount 0) costs nothing, same as skipping Protect/Reroll costs nothing.

**Decision-timeout**: a new check (call it `expireDecisionIfNeeded()`, same shape as
`expireTurnIfNeeded()`) runs on every `roll()`/`state()` call. If `pending_decision` is set and
`decision_started_at` is older than `DECISION_TIMEOUT_SECONDS`, resolve it as `skip` and
resume automatically. This is the fail-safe from decision #3 above — never resolve a timeout
as `use`.

**Bots**: `autoPlayBotTurn()` resolves the bot's own pending decision synchronously, inline,
using the heuristics below — a bot's decision is never exposed as a `pending_decision` pause
that a human has to watch or wait on.

**Client UI (Sept 22)**: a pending decision renders as `#decisionToast`
(`resources/views/arcade/snakes/play.blade.php`) — a small card anchored in the exact same
spot/style as the reward/expense `#eventToast` (both driven by `positionToastOverBoard()`),
**not** a full-screen dimming `.overlay`. The board and roll button stay visible; only
`cashOutBtn` is explicitly disabled for the pending window (in `doRoll()`, re-enabled in
`finishRoll()` and the `pollState()` stale-decision path) since the old overlay's backdrop
isn't there anymore to block clicks. Mobile also relocates `#powerTray` (remaining
Reroll/Protect/Boost/Bank counts) from the sidebar into the always-visible floating die widget
via `relocatePowerTray()` — it was previously buried inside the collapsed drawer with no
glanceable mobile view. Do not re-introduce a full-screen blocking modal for decisions without
re-adding an equivalent guard on cash-out.

## 5. Bot heuristics

- **Boost**: activate ~40% of turns while `boost_left > 0`. No real judgment needed — just
  texture.
- **Reroll**: use if the current roll would land the bot on a tile it can already see is a
  heavy expense or a snake head; skip otherwise.
- **Protect**: use if the prevented loss would exceed ~8% of current `pot_amount` and
  `protect_left > 0`; skip small losses (mirrors the spec's own "is this loss big enough to
  spend a Protect on" framing).
- **Bank**: bank the full 20% cap whenever available and `bank_left > 2`; be more
  conservative (skip more often) once uses are low, so the bot doesn't burn all four in the
  first quarter of the race.

These are starting values for playtesting, not tuned — same spirit as the spec's own "20%
should be the initial value" caveat on Bank.

## 6. Settlement changes

`settleMatchIfDecided()` needs to stop being blind to `banked_amount`:

- Winner's payout: `pot_amount + banked_amount` for themselves, **plus** 60% of each loser's
  `pot_amount` only — never a loser's `banked_amount`. This is the entire point of Bank: it's
  protected from the opponent's claim.
- Loser's retained amount: `banked_amount + 40% × pot_amount`.

If you're debugging "banked money got taken by the opponent," check that the 60%-cut loop in
`settleMatchIfDecided()` is still computing `$cut` off `$s->pot_amount` and not off a combined
total — that line is the single point where this rule could regress.

## 7. Leaderboard — Money Mined

New `LeaderboardSnapshot` scope: `arcade:money_mined:{period}` (reuses the existing
snapshot/trend-arrow infra that already powers the global/school leaderboards — see
`SnapshotLeaderboard` command). Per completed `ArcadeSession`, `net_mined` = that session's
**closing balance**:

- Winner: `pot_amount + banked_amount` (post-settlement).
- Loser: `banked_amount + 40% × pot_amount` (post-settlement).

Deliberately *not* raw wallet balance — that would mix in non-arcade income and defeat the
point of a Pesa-Trail-specific leaderboard. Wins / games played / win rate are already
derivable from `arcade_sessions.status` per user, no new tracking needed for those.

## 8. Gating powers (1v1-only → any match size, both Sept 22)

**First build**: a session-count check (`ArcadeMatch::sessions()->count() === 2`) at the
point powers are offered/checked, not a change to lobby creation.

**Same-day extension to N-player**: `powersEligible()` was relaxed from
`(int) $match->max_players === 2` to just `(bool) $match` — any real match qualifies,
regardless of size. This was safe to do without touching anything else because:

- `settleMatchIfDecided()`'s wager cut loop already iterated "every other active session,"
  not "the one opponent" — it predates the powers work and was never 2-player-specific.
- The pause/resume pipeline (`pauseDecision`/`resumeFromDecision`/`expireDecisionIfNeeded`)
  only ever touches the CURRENT turn holder's own session — it has no N-player-specific
  logic to add or remove.
- `autoPlayBotTurn()`'s bot heuristics only ever run for `is_bot` sessions, which only ever
  exist in solo-vs-bot matches (always exactly 2 seats) — N-player lobbies never seat a bot,
  so nothing there needed to change either.
- Power counters were already seeded on every session regardless of match size (see below),
  so no seeding change was needed.

Verified via a 4-player wager simulation (see §12) — 45 rolls, all four sessions eligible,
8 reroll/8 protect/9 bank uses across the table, settled as 1 winner + 3 losers with each
loser's banked amount intact.

- Solo-vs-bot matches (`startSoloWithBot()`) qualify — good low-risk testbed, and still the
  easiest way to test alone.
- Any 1v1 or N-player (2-8) standard/wager lobby qualifies once populated.
- Power counters are seeded on every session unconditionally (cheap, harmless if unused) —
  `powersEligible()` at the point of *use* was always the real gate, not the seeding.

## 9. Build phases

- [x] **Phase 1** — migration (`banked_amount`, `pending_decision`, `decision_started_at`),
      new constants, power-counter seeding in `openSession()`. Migration
      `2026_09_22_100000_add_strategy_powers_to_arcade_sessions.php`, run locally.
- [x] **Phase 2** — service-layer staged `roll()` refactor with pause/resume, `decide()`
      method, `expireDecisionIfNeeded()`, bot heuristics in `autoPlayBotTurn()`.
- [x] **Phase 3** — settlement changes for `banked_amount` in `settleMatchIfDecided()`,
      plus `forfeitSession()` and `cashOut()` (both also convert a session's money back
      to real wallet balance, so both needed the same banked-amount inclusion).
- [x] **Phase 4** — controller/routes (`arcade.snakes.decide`, `arcade.snakes.boost`),
      `state()`/`play()` payload additions (`pending_decision`, `powers`).
- [x] **Phase 5** — front-end: decision modal (Reroll/Protect/Bank), Boost arm button,
      power-tray use-counter badges, banked-balance HUD chip, new sound cues
      (`boostArm`/`shieldUp`/`dicePulse`/`bankVault`/`decisionPing`) in
      `resources/views/arcade/snakes/play.blade.php` + `public/js/arcade-sounds.js`.
- [x] **Phase 6** — Money Mined leaderboard. Shipped as a **live aggregate query**
      (`SUM(pot_amount + banked_amount)` grouped by user) in `ArcadeSnakesController::index()`,
      rendered as a top-10 section in `lobby.blade.php`. Deliberately simpler than the
      original plan's `LeaderboardSnapshot` scope/trend-arrow integration — no daily
      snapshot job needed for v1, since a completed session's `pot_amount + banked_amount`
      already IS its closing balance. Revisit only if trend arrows are wanted later.
- [x] **Phase 7** — 1v1-only gating via `powersEligible()` (single choke point, checked
      everywhere a power is offered or used).

All backend logic (staged pipeline, bot heuristics, settlement/forfeit/cash-out banked
handling, the Money Mined query) was exercised against the local dev DB with rolled-back
transactions — see §12. Not yet done: a real browser click-through of the decision modal/
power tray UI, and running the migration on the live cPanel server (see
[[project_git_cpanel_deploy_setup]] in memory for the deploy routine).

## 10. Troubleshooting checklist (for when a power breaks)

- **A power fired without the player choosing it** → decision-timeout resolved as `use`
  instead of `skip` somewhere. Check `expireDecisionIfNeeded()`'s branch logic first.
- **A turn is stuck / match won't advance** → check `pending_decision` and
  `decision_started_at` on the current turn holder's session. If `decision_started_at` is
  older than `DECISION_TIMEOUT_SECONDS` and `pending_decision` is still set, the timeout sweep
  isn't running — verify it's called from both `roll()`'s entry and `state()`, same as
  `expireTurnIfNeeded()` is today.
- **Banked money got included in the opponent's 60% cut, or in a tile effect** → grep every
  read/write of `pot_amount` in `applyTileEffect()` and `settleMatchIfDecided()` for an
  accidental `pot_amount + banked_amount` combination; there should be none outside the final
  payout line.
- **Reroll offered twice in one turn** → the reroll pause point is being re-entered after
  `use` instead of falling through to movement. Check that the `use` branch doesn't loop back
  through the same pause check with the new roll value.
- **Bot appears to "hang" or pause visibly on a decision** → bots must never receive a
  `pending_decision` state that persists past the same `autoPlayBotTurn()` call. If a bot
  session shows `pending_decision` set on a later poll, the bot's inline resolution didn't run.
- **Boost multiplies a loss, or persists across turns** → `boost_active` must be cleared the
  first time a reward/mystery-gift resolution is reached, win or lose, same turn it was armed.
- **Money Mined numbers look inflated/deflated vs wallet** → confirm `net_mined` is being
  computed from the session's closing balance, not from `progress->balance` (which mixes in
  unrelated income/spending).

## 11. Open items deferred, not forgotten

- Bot heuristic thresholds (8% Protect trigger, 40% Boost rate, `bank_left > 2` cutoff) are
  first-guess values — revisit once there's real playtest data, per the spec's own "not
  necessarily the final balance" caveat.
- Leaderboard period scope (global/weekly/monthly/all-time) defaults to whatever periods the
  existing leaderboard already supports — no new decision needed unless that turns out to be
  wrong in practice.
- ~~Extending powers to N-player free-for-all Rivals Trail rounds was explicitly deferred~~
  — done same day, see decision #2 and §8. No longer open.
- A session created **before** this migration/deploy has no power counters in its
  `session_assets` — `usesLeft()`'s `?? 0` fallback means it silently offers zero uses of
  everything rather than erroring, so an in-progress game survives the deploy without a
  crash; it just won't see powers until its next fresh game.

## 12. What's been verified so far (Sept 2026)

All checks below ran against the local dev DB inside a transaction that was rolled back
afterward — no test data persisted. Scripts aren't kept in the repo (they lived in the
session's scratch directory); re-derive similar checks from this description if regression
hunting later.

- **Full pipeline, solo-vs-bot**: an 18-roll game with Reroll/Protect/Bank all exercised —
  the bot resolved its own decisions via the heuristics in §5 without ever exposing a pause,
  and the match settled correctly (winner/loser statuses, banked amounts intact on both
  sides).
- **Turn enforcement**: confirmed `roll()` correctly rejects a call when it isn't that
  session's turn — including immediately after a pending-decision resume (the turn only
  advances once `finalizeRoll()` actually runs).
- **Wager settlement math**: `settleMatchIfDecided()` — a winner with `pot=1000,
  banked=200` against a loser with `pot=800, banked=300` produced `winner_gain=480` (60% of
  800 exactly, off `pot_amount` only), winner's final pot `1480`, loser's final pot `320`,
  both `banked_amount`s untouched, and both wallets credited `pot + banked` exactly.
- **Forfeit math**: a 30%-cut forfeit on `pot=800, banked=300` produced the correct
  remaining pot, untouched banked, and a wallet credit of `remaining + banked`.
- **Cash-out**: a standard-mode session with `pot=1200, banked=150` paid out `1350`
  (both components) to the wallet.
- **Money Mined query**: the live aggregate query runs against real data without SQL
  errors and returns sensible rows.
- **N-player extension**: a 4-player wager match (4 real, non-bot users) showed
  `powersEligible() === true` for all four sessions; a 45-roll simulation exercised 8
  Rerolls, 8 Protects, and 9 Banks across the table and settled as 1 winner + 3 losers,
  each loser's `banked_amount` intact and excluded from the winner's cut, matching the
  1v1 settlement math exactly, just applied per-opponent in a loop.
- **Compile/lint**: `ArcadeSnakesService.php` and `ArcadeSnakesController.php` pass
  `php -l`; both edited Blade views pass a full `artisan view:cache` compile; the rendered
  `play.blade.php` inline `<script>` block (with real server-side values substituted,
  including a live pending-decision restore payload) passes `node --check`.

**Browser-tested (Sept 2026)** against a real solo-vs-bot round via Claude in Chrome, logged
in as the admin dev account: Boost armed correctly (golden pulse glow rendered, count
decremented); a Reroll pause appeared with a live countdown and both `use` (new roll shown,
counter decremented, no second offer) and the timeout-driven `skip` path were exercised
under real network latency; a Protect pause correctly blocked a KES 80 loss when skipped
vs. accepted; the banked-balance HUD chip and power tray counts matched DB ground truth
after a page reload; the bot won the race naturally, the match settled as a non-wager
result (no money moved, statuses flipped correctly), and the **Money Mined leaderboard
updated live and correctly included the bot's own session** (`Robo` and `Moski Admin`
both ranked, numbers matching the completed session's `pot_amount`). A live human-vs-human
Rivals Trail wager round was not clicked through in-browser (needs a second account) but
its settlement math was already verified via the rolled-back-transaction scripts in §12.
Remaining: run the migration on the live cPanel server before this is usable in production.
