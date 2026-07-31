# Pesa City — Fable 5 Handover Prompt

> Paste everything below this line into a new Fable 5 session.

---

## Project overview

You are continuing development of **PesaQuest / Pesa City** — a gamified financial literacy platform built for a Kenyan NGO called **Moski**. It targets youth across four age groups (8–12, 13–17, 18–25, 26+) and teaches real financial concepts through gameplay: savings, investment, loans, credit scores, bills, careers, and asset ownership.

**Stack:** Laravel 12 · Blade · Alpine.js · TailwindCSS · MySQL · Vite · GD (image processing) · WebAudio API (procedural sound, no MP3s)

**Working directory:** `C:\xampp\htdocs\finlit`  
**Local URL:** `http://localhost/finlit`  
**Admin login:** admin@moski.org / admin123  
**DB:** MySQL `pesaquest`--from .env

---

## Memory system

This project has a persistent memory directory at:
`C:\Users\user\.claude\projects\C--xampp-htdocs-finlit\memory\`

Read `MEMORY.md` first — it indexes every phase that has been shipped. Key phases to be aware of:

- **Phase 12** — Pesa City world map live, 7 districts (Marketplace, Bank/Savings, Equity Square, Car Yard, Estates, Fun World, Workplace/OpHub), procedural WebAudio, no MP3s
- **Phase 15–16** — Quest system (in-map panel, auto-complete via `QuestTriggerService::fire()`), notification bell, active quests sidebar, Workplace + Opportunity Hub distinction
- **Phase 17** — Investment Deals (Equity Square), Loans, credit score, `income_period_ticks` on assets
- **Phase 18** — Career badges, journey milestones, Pesa City jobs on career page
- **Phase 19** — Marketplace card redesign, dashboard Current Quest widget, Car Yard/Estates buy buttons, Fun World mood mechanic, Equity Square vs Bank split, game_age → net worth chapters, quest celebration live polling

**Critical model knowledge:**
- `UserProgress::points_total` is the XP field — NOT `xp`
- `UserProgress::calculateLevel()` must be called after every `points_total` update
- `UserProgress::chapterFromNetWorth(int $netWorth): string` drives chapters — NOT age
- `QuestTriggerService::fire($user, $triggerType, $context)` is the central quest engine
- `window.__PESA_QUEST_COMPLETIONS__` / poll at `/world/quests/pending-completions` for live celebrations
- Images stored at `public/uploads/profiles/` — no symlink dependency
- Game time is tick-based: 1 tick = 1 game day. `$progress->tick_count` is the canonical game clock. The admin can configure ticks-per-real-day in Settings.
- `GameClock::ticksSince($lastTickAt)` converts real elapsed time to game ticks
- `LifeSimulator::processLogin()` runs on every login — processes catch-up ticks, settles bills/loans/salaries, fires life events, updates net_worth_cache, advances chapter
- `GameNotification` table stores all in-game notifications shown in the bell

---

## What to build in this session

Work through these in order. Some are bug fixes, some are new features, some are new pages. After completing all of them, write a recommendations file (instructions at the end).

---

### FIX 1 — Marketplace popup: scroll broken, buy button unreachable

**Problem:** When a user clicks "View Details" on a marketplace asset, a popup opens. The popup content overflows and the purchase button at the bottom cannot be reached — the user cannot scroll inside the modal.

**Where to look:** `resources/views/marketplace/index.blade.php` — find the modal/popup HTML. The inner content wrapper likely lacks `overflow-y: auto` and a bounded `max-height`, or the outer overlay has `overflow:hidden`. Fix it so:
- The modal has a fixed max height (e.g. `90vh`) on mobile and desktop
- The inner content scrolls independently (`overflow-y: auto; flex: 1; min-height: 0`)
- The purchase button and price are always visible — either sticky at the bottom of the modal or naturally reachable by scrolling
- Test the fix mentally for both short-content assets and long-description assets

---

### FIX 2 — Fun World: purchase popup, sound, mood decay, admin-configurable activities

**Context:** Fun World district (`slug: fun-world`) already has a mood mechanic (columns `mood` tinyint 0–100, `mood_last_boosted_at` on `user_progress`), a `POST /world/fun-world/spend` endpoint in `WorldController::funWorldSpend()`, and a mood bar in the panel UI. The spend button deducts balance and boosts mood.

**What is missing / needs improving:**

**a) Purchase success popup**
When the spend button is clicked and the AJAX succeeds, show a celebration popup — not just a toast. It should:
- Display the activity name and icon
- Show the mood boost gained (`+X mood`)
- Show XP earned
- Show new mood level as a visual bar
- Have a close button
- Play the existing quest-complete or a new "fun" sound via the WebAudio `SoundMgr` system already in `public/js/world.js` — look at how `SoundMgr.playSuccess()` or similar is called elsewhere and add a `playFun()` variant if needed

**b) Mood decay over time**- 
Mood should decrease naturally over game ticks so players are motivated to return and recharge. Implement:
- In `LifeSimulator::processTick()`, decay mood by a small amount each tick (e.g. `max(30, mood - 1)` per tick, meaning mood floors at 30 not 0 — players are always at baseline, not miserable)
- When mood drops below 50 for the first time that game-day, create a `GameNotification` of type `mood_low` with message "Your mood is low — visit Fun World to recharge"
- The mood bar in the Fun World panel should reflect real-time mood from the server (already loaded via district data)
- Add a mood indicator to the main world HUD (small emoji + number near the player character area)

**c) Mood → gameplay effects**
Mood should matter beyond a number. Add:
- When mood < 40: career income reduced by 10% (in `CareerService::claimIncome()` or `LifeSimulator::settleJobSalaries()`)
- When mood > 80: XP from completing quests boosted by 10% (in `WorldController::completeQuest()` and `QuestTriggerService`)
- Show these effects as small labels in the relevant panels ("Mood bonus active ✓" / "Low mood penalty active ⚠")

**d) Admin-configurable Fun World activities**
Currently activities are hardcoded in `WorldController`. Move them to the database:
- Create a migration for table `fun_world_activities`: `id, name, icon, description, price, mood_boost_base, xp_reward, is_active, sort_order, created_at, updated_at`
- `mood_boost_base` is the base boost (actual boost = `min(25, max(5, price/200))` using the existing formula)
- Add a seeder with 8–10 activities (cinema, nyama choma, spa, beach trip, gaming, gym session, live music, road trip, etc.) with Kenyan pricing that feels realistic (KES 200–5000)
- Expose a simple CRUD panel in admin GameSet area (`/admin/gameset`) for managing these activities — same style as existing gameset panels
- `WorldController::district()` for `fun-world` should load activities from DB instead of hardcoded array
- `funWorldSpend()` should validate that the activity exists in DB

---

### FIX 3 — Chapters: complete removal of game_age, fix "Days Lived: 0" and "Events: 0"

**Context:** In the previous session, `chapterKey()` was updated to use `net_worth_cache` via `chapterFromNetWorth()`, and the chapter panel and timeline views had `game_age` display replaced. However there may be remaining references.

**Tasks:**
- Search the entire codebase for any remaining `game_age` display (not just the DB column — that can stay for now). Remove or replace every user-facing reference. The DB column `game_age` and `game_age_at_trigger` can remain in the schema since dropping columns requires a migration, but nothing should display "Age X" or "Game Age" to the user anywhere.
- On the **life timeline page** (`/life/timeline` → `resources/views/life/timeline.blade.php`), the stats row shows "Days Lived" and "Events". These pull from `$progress->tick_count` and `$totalEvents`. If they show 0, the controller (`LifeController::timeline()`) may not be passing these variables or `tick_count` is genuinely 0 for users who haven't had their first tick processed. Fix: ensure `LifeController::timeline()` passes `tick_count` from the user's progress record, and `$totalEvents` = count of `PlayerLifeEvent` records for that user. If tick_count is 0, show "New player" or similar rather than a misleading 0.
- On the **life board page** (`/life` → `resources/views/life/board.blade.php`), verify the chapter progress bar now uses net worth (it was updated last session). Check that `$progress->netWorthToNextChapter()` and `$progress->nextChapterNetWorth()` are being called correctly.

---

### FIX 4 — Chama area: use game days instead of real days

**Context:** The Chama feature (group savings/investment circles) calculates contribution cycles, deadlines, and payouts using real calendar days (`Carbon::now()->diffInDays(...)`). The game has a tick-based clock where 1 tick = 1 game day, and admin can configure how many real minutes = 1 game day in the Settings table (key: `game_clock_speed` or similar — check the Settings model/table).

**Task:** Find all date/day calculations in the Chama-related files (search for `Chama`, `chama`, `PlayerChama` in controllers, models, views). Replace every instance of real-day calculation with game-tick-based calculation:
- Use `$progress->tick_count` as the game day counter
- For "days until deadline" style calculations: `$deadline_tick - $progress->tick_count`
- For "days since" calculations: `$progress->tick_count - $start_tick`
- Store Chama deadlines as tick numbers (e.g. `due_at_tick`) not timestamps where possible, OR convert timestamps to ticks using `GameClock::ticksSince()`
- Display these as "game days" in the UI, not "real days"
- Apply this same game-day principle to any other part of the system that currently uses real days for gameplay purposes (savings scheme durations, quest deadlines, investment deal durations if not already tick-based)

---

### FIX 5 — Quest start popup not showing

**Context:** When a player clicks "Start Quest" on a quest, there should be a popup/modal that explains how to complete the quest — what actions to take, what the trigger is. This was built in a previous phase but reportedly stopped showing.

**Where to investigate:**
- `resources/views/world/index.blade.php` — find the quest start flow and the popup HTML. Look for `@click="startQuest(quest)"` or similar Alpine.js handlers.
- `public/js/world.js` — find `startQuest()` function, check if it sets a reactive state property that controls the popup's `x-show`
- `WorldController::startQuest()` — check what it returns and whether the frontend handles the response
- The popup should show: quest icon, title, description, instructions (with `[TRIGGER:...]` tags stripped), XP reward, and a "Got it, I'll do it!" dismiss button
- Also check: when a quest is already in progress and the player opens the quest panel, clicking on it should re-show these instructions

Fix whatever is preventing the popup from appearing.

---

### BUILD 1 — /life page: full Life Board interface

**Route:** `GET /life` → `LifeController::board()` → `resources/views/life/board.blade.php`

The current `/life` page exists but may be incomplete or not visually consistent. Build a complete, polished Life Board that matches the design language of `/dashboard` (dark theme, rounded cards, purple/emerald accents, mobile-first). It should contain:

**Sections to include:**

1. **Character Identity bar** — avatar, name, chapter icon + name (net-worth based), level badge, career title, financial personality tag, net worth

2. **Life Chapter progress card** — current chapter (🎒 Student / 🎓 Graduate / 💪 Hustler / 🏠 Settler / 📊 Builder / 🌟 Elder), chapter tagline, net worth progress bar toward next chapter threshold, KES amount remaining, chapter mini-map showing all 6 stages

3. **Monthly P&L panel** — income sources (salary, asset income, job salary) vs outgoings (bills, loan payments, asset costs) → net monthly cashflow number. Pull from existing `DashboardController` data patterns

4. **Bills & Credit section** — active bills list with urgency (overdue in red, due soon in amber, ok in green), credit score gauge (300–850) with label (Very Poor / Poor / Fair / Good / Excellent), next bill due date in game days

5. **Assets snapshot** — owned assets with current value, condition %, monthly income. Link to marketplace for each

6. **Life Events feed** — last 5 `PlayerLifeEvent` records with icon, title, description, tick + chapter when it happened

7. **Mood bar** — current mood (0–100), emoji state, time since last boost, "Visit Fun World →" CTA when mood < 60

**Design requirements:**
- Same dark background (`#07060f` or similar), same card style as dashboard
- Fully responsive — single column on mobile, 2-column grid on desktop (same breakpoint as dashboard)
- Use existing `<x-mobile-bottom-nav active="life" />` component
- Sticky header with back-to-dashboard link
- No inline `<style>` blocks — use Tailwind utility classes + small `<style>` block at top only if necessary

---

### BUILD 2 — /portfolio page: Investment & Assets Portfolio

**Route:** Create `GET /portfolio` → new `PortfolioController::index()` → new `resources/views/portfolio/index.blade.php`

Add the route to `routes/web.php` in the auth middleware group.

**What to show:**

1. **Portfolio summary bar** — total asset value, total invested, total profit/loss (unrealised), monthly passive income, portfolio count

2. **Active investments** — `PlayerDeal` records with status `pending` showing: deal name, amount invested, expected return range, ticks until resolution (as "game days remaining"), risk level badge

3. **Completed deals** — resolved `PlayerDeal` records (success/failed) with outcome: profit or loss amount, return % achieved

4. **Owned assets** — `PlayerAsset` records grouped by category (property / vehicle / business / investment / gadget). Each card: asset image_url, name, purchase price, current value, appreciation %, condition bar, monthly income/cost, buy-more or sell button placeholder

5. **Savings schemes** — active `PlayerSavingsScheme` records: name, target, current amount, % progress bar, projected completion in game days, monthly contribution

6. **Loans tracker** — active `PlayerLoan` records: loan name, outstanding balance, interest rate, next payment amount, next payment due in game days, credit impact

7. **Net worth chart** — a simple SVG or Canvas sparkline of net worth over time using `StockPriceHistory` data (already being recorded per asset). Show a composite line if possible.

**Design requirements:**
- Same visual language as `/dashboard` and `/life`  
- Mobile: single column, cards stack vertically  
- Desktop: summary bar across top, then 2-column grid  
- Use `<x-mobile-bottom-nav active="portfolio" />` — add "portfolio" as an active state to that component if it doesn't exist  
- Add portfolio link to the main navigation (`resources/views/components/navigation.blade.php` or wherever the nav lives)

---

### FIX 6 — Credit score: use more relevant data

**Current state:** Credit score only changes via: bills paid (+5), bills missed (-20), loans paid on time (+5), loan missed (-20), loan paid off (+30), loan defaulted (-100), some quest completions.

**Refine the credit score to also respond to:**
- Maintaining a savings scheme for 30+ game ticks without withdrawing: `+10` (fire once per scheme per 30-tick window in `LifeSimulator`)
- Net worth milestone crossing (student→graduate, etc.): `+15`
- Balance going to zero: `-10` (check in `LifeSimulator::processLogin()` before ticks run)
- Owning 3+ active assets: `+5` (one-time, track with a flag or check badge)
- Having an active Pesa City job for 60+ ticks: `+10`

Add a credit score history panel to the `/life` page showing recent changes with reasons (store reason in a new `credit_score_log` or use `GameNotification` with type `credit_change` — whichever is simpler given existing schema).

---

### FIX 7 — Bills: make them feel relevant and contextual

**Current state:** Bills exist in the `bills` table and auto-assign on first login based on age group. Players may not understand why they have certain bills or feel connected to them.

**Improvements:**

1. **Asset-linked bills:** When a player buys a property from the marketplace (Car Yard or Estates buy buttons), automatically assign a relevant `PlayerBill` for that asset (e.g. buying a Bedsitter → assign a rent bill; buying a car → assign insurance + fuel bill). Check `MarketplaceController::buy()` and `WorldController` (Car Yard / Estates purchase handlers) — after purchase, call a new `BillService::assignAssetBills(User $user, Asset $asset)` method.

2. **Bill explanation UI:** In the `/life` board bills section and in the notification bell when a bill_missed notification shows, include the bill's `flavor_text` and `consequence_text` from the `Bill` model — help the player understand WHY this bill exists (educational context).

3. **Bills dashboard widget:** On the `/dashboard`, the bills section should show the next 2 bills due (in game days), the total monthly outgoing from bills, and a prominent "OVERDUE" badge if any bills are overdue. This likely already exists partially — ensure it's wired to real `PlayerBill` data, not hardcoded.

4. **Admin bills editor in GameSet:** Ensure the admin can create/edit bills in the GameSet panel. If this exists, verify it works. If not, add a simple CRUD table for `bills` in the gameset area.

---

## Interface consistency rules (apply to everything built)

These rules match the existing system — do not deviate:

- **Dark theme background:** `#07060f` or `bg-black/90` with `backdrop-blur`
- **Cards:** `rounded-2xl` or `rounded-3xl`, semi-transparent dark surface with colored border `rgba(color, 0.2)`
- **Accent colors by type:** purple/violet (XP/level), emerald (money/positive), amber (warnings/bills), red (negative/danger), cyan (investments/deals), indigo (learning/quests)
- **Typography:** `font-black` for headings, `text-xs` uppercase tracking for labels, `font-semibold` for body
- **Mobile:** single-column, `<x-mobile-bottom-nav>` at bottom, no horizontal overflow
- **Desktop:** multi-column grid, same sticky top nav as dashboard
- **Sound:** any new success/celebration moments should call `SoundMgr` — look at existing calls in `world.js` and follow the same pattern (procedural WebAudio, no external files)
- **NEVER** hardcode KES amounts or tick counts as display values — always pull from the database/model

---

## After completing all fixes and builds

Once all items above are done:

1. **Write `fablerecommendations.md`** in the project root (`C:\xampp\htdocs\finlit\fablerecommendations.md`). This is a markdown document the project owner will review and selectively approve. Structure it as:

```
# Pesa City — Extension Recommendations

## How to use this document
[brief explanation]

## Recommended features (each as a separate section)
### Feature Name
- What it is (1 paragraph)
- Why it increases engagement / teaches financial literacy
- Estimated complexity: Low / Medium / High
- Dependencies: what must exist first
- Suggested implementation approach (brief)
```

Cover at minimum these categories of ideas — but go deeper and be creative, keeping Kenya / East Africa context in mind:
- **Retention mechanics** (daily streaks, comeback bonuses, seasonal events)
- **Social features** (Chama leaderboards, friend challenges, sharing milestones)
- **Narrative depth** (life event story arcs, random crises, market shocks)
- **Mini-games** (budgeting puzzles, investment simulations, negotiation scenarios)
- **Real-world tie-ins** (M-Pesa integration concepts, SACCO mechanics, Harambee events)
- **Progression systems** (prestige resets, mentor roles, legacy scoring)
- **Educational depth** (financial concept unlocks, PesaAI tutoring moments, certificate milestones)
- **Seasonal / time-limited content** (school term events, harvest season market shifts, holiday spending traps)
- Anything else that genuinely fits this game's educational mission and audience

Be specific and grounded — every recommendation should feel like it belongs in a Kenyan financial literacy game, not a generic RPG.

2. Run `php artisan route:list 2>&1 | tail -5` to confirm all new routes are registered and the count is sensible.

3. Run `php -l` on every PHP file you created or edited to confirm no syntax errors.

---

## Notes for the model

- The project owner **cannot run artisan commands on the live server** — any migration must also be triggerable from the admin panel's Artisan web runner (`/admin` → Artisan section, which has a whitelist in `AdminController`)
- Profile images are now stored at `public/uploads/profiles/` — not `/storage/` — this was fixed last session
- The game uses **ticks as game days everywhere** — never use `Carbon::diffInDays()` for game-time calculations
- When adding new DB columns, always create a migration AND add the column to the model's `$fillable` and `$casts` arrays
- The WebAudio system generates sound procedurally — look at `SoundMgr` in `public/js/world.js` before adding any new sounds
- `QuestTriggerService::fire($user, $triggerType, $context)` should be called whenever a player completes a meaningful game action — check existing trigger types in that service before adding new ones
- Every new page must work on mobile — test mentally for 375px width

---

*Generated by Sonnet 4.6 as a handover prompt for Fable 5. Project is 97%+ complete on core infrastructure — this session focuses on polish, depth, and retention.*
