# PostHog Setup Guide — Pesa Trail & Game Analytics

*How to wire up PostHog and which insights actually tell you something about the game.*
*Version: August 2026 · Audience: administrators, Moski leadership*

**Companion documents:** `ADMIN-GUIDE.md` §4 covers the admin panel generally; this guide
is specific to the analytics setup and the events Pesa Trail (Snakes & Cash / Rivals
Trail) now emits.

---

## 1. Get a PostHog account

1. Go to **posthog.com** and create a free project. Pick the **US or EU cloud** — note
   which one, you'll need it below. (The free tier covers a game this size comfortably —
   1M events/month before any billing kicks in.)
2. In the new project, go to **Settings → Project → Project API Key** and copy the key
   (starts with `phc_`).

## 2. Paste the key into the admin panel

1. Log in as an admin and open **Admin → Analytics → 🔌 Third-Party Tracking Setup**
   (`/admin/analytics`, section handled by `AdminController::saveTrackers()`).
2. Paste the key into **PostHog → Project API Key**. Leave **API Host** as
   `https://us.i.posthog.com` unless you picked the EU region, in which case use
   `https://eu.i.posthog.com`.
3. Click **💾 Save Tracker IDs**. No redeploy needed — the next page load on any
   logged-in page starts sending events.

Under the hood this just writes to the `settings` table
(`posthog_key`/`posthog_host`); `resources/views/partials/trackers.blade.php` reads those
settings and inlines the PostHog snippet — it's a no-op until a key is saved. That
partial is included in the three main layouts (`layouts/app`, `layouts/guest`,
`components/layouts/world`) **and now also in the Pesa Trail lobby and play pages**
(`resources/views/arcade/snakes/lobby.blade.php` / `play.blade.php`), which previously
had no tracking at all since they're standalone documents that don't extend those
layouts.

## 3. Verify it's actually firing

1. Open the Pesa Trail lobby while logged in.
2. In PostHog, go to **Activity → Live events** (left sidebar). You should see a
   `$pageview` land within a few seconds, tagged with your user ID (PostHog is
   `identify()`'d with the logged-in user's ID and current game level on every page —
   see `trackers.blade.php:24`).
3. Create a test match or play a solo round — you should see the custom events below
   appear live.

## 4. What's now being tracked

Every event below is a genuine gameplay action, not just a page load — this is what
makes the funnels/retention insights in §5 possible. All fire client-side via
`posthog.capture(...)`, guarded so they silently no-op if PostHog isn't configured
(`phTrack()` helper in both `lobby.blade.php` and `play.blade.php`).

| Event | Fires when | Key properties |
|---|---|---|
| `pesatrail_solo_start` | Player starts a solo (vs-bot) game | — |
| `pesatrail_match_create` | Player creates a standard multiplayer match | — |
| `pesatrail_match_join` | Player joins a standard match | `via`: `code` or `list` |
| `pesatrail_wager_create` | Player creates a Rivals Trail (money) round | — |
| `pesatrail_wager_join` | Player joins a Rivals Trail round | `via`: `code` or `list` |
| `pesatrail_round_won` | Any round ends in a win (own roll or opponent forfeit) | `mode` (`standard`/`wager`), `pot`, `gain` (wager winnings, 0 for standard) |
| `pesatrail_round_lost` | Standard race lost, or Rivals Trail round lost | `mode`, `pot`, `amount_lost` (wager only) |
| `pesatrail_round_forfeited` | Player withdrawn from a Rivals Trail round after 8 missed turns | `mode`, `pot` |
| `pesatrail_round_busted` | Standard-mode savings hit zero | `mode` |

Every event also carries `mode` (read from the server-rendered `MATCH_MODE` constant),
so every insight below can be sliced standard-vs-wager without extra setup.

Because autocapture ships on by default with the standard snippet, you'll also see
`$pageview` / `$autocapture` (button clicks) for free — those are useful for the
funnel steps in §5 that PostHog can't see any other way (e.g. "opened the lobby").

## 5. Insights worth building

Build these under **Product analytics → Insights** in PostHog. None of this requires
code changes — it's all built from the events in §4.

### a) Core funnel: lobby → play → settle
**Insight type: Funnel.** Steps:
1. `$pageview` where URL contains `/arcade/snakes-and-cash`
2. `pesatrail_match_create` **OR** `pesatrail_wager_create` **OR** `pesatrail_solo_start` **OR** `pesatrail_match_join` **OR** `pesatrail_wager_join`
3. `pesatrail_round_won` **OR** `pesatrail_round_lost` **OR** `pesatrail_round_forfeited` **OR** `pesatrail_round_busted`

This is the single most useful chart — it tells you what fraction of people who open
the lobby actually start a game, and what fraction of started games get finished.
A big drop between steps 1→2 means the lobby itself is the problem (this is exactly
why the lobby was decluttered — re-run this funnel before/after to check it worked).

### b) Rivals Trail (wager) adoption
**Insight type: Trends.** Line chart of `pesatrail_wager_create` + `pesatrail_wager_join`
counts per day, compared against `pesatrail_match_create` + `pesatrail_match_join`
(standard mode). Tells you whether the money mode is actually being adopted relative to
free play, and whether that's growing week over week.

### c) Win/loss economics
**Insight type: Trends**, filtered to `mode = wager`. Two series:
- Sum of `gain` from `pesatrail_round_won`
- Sum of `amount_lost` from `pesatrail_round_lost`

These two sums should track closely (money moving from losers to winners, roughly
60/40 by design — see `ArcadeSnakesService::WINNER_CUT_PERCENT`). If they diverge a
lot over time, something in the settlement logic is worth re-checking against real data.

### d) Forfeit rate (early-warning for a broken UX)
**Insight type: Trends** — `pesatrail_round_forfeited` as a percentage of all
`mode=wager` round-end events (`won` + `lost` + `forfeited`). A rising forfeit rate
usually means players are abandoning mid-round rather than actively losing — often a
sign of a UX/latency problem worth investigating before it shows up as churn.

### e) Retention
**Insight type: Retention.** Use `pesatrail_solo_start` (or any round-start event) as
both the starting and returning event, weekly cohorts. This is standard PostHog
retention — tells you whether people who play once come back to play again.

### f) Session recordings (Clarity already covers this, PostHog can too)
If you want to *watch* someone struggle with the new lobby dropdowns rather than just
see the drop-off number, turn on **Session Replay** in PostHog (Settings → Project →
Session replay). Since this app already runs Microsoft Clarity for the same purpose
(see the admin panel), only turn this on in one tool at a time to avoid paying for
double recording quota.

## 6. Notes / gotchas

- **All these events are unauthenticated-safe** — they only fire for logged-in players
  (the pages requiring these routes are all behind auth), so `identify()` always has a
  real user ID attached; no anonymous/pre-signup traffic pollutes these funnels.
- **`person_profiles: 'identified_only'`** is set in the snippet — anonymous pre-login
  page views (landing page, etc.) don't create a full PostHog "person," keeping billing
  volume down. This means none of the Pesa Trail events above will ever be anonymous.
- **Extending this further:** the `phTrack()` pattern in both `lobby.blade.php` and
  `play.blade.php` is deliberately a one-line helper — add more `phTrack('event_name', {...})`
  calls anywhere else in those files (or copy the pattern into other game screens) to
  extend tracking without touching the PostHog snippet itself.
