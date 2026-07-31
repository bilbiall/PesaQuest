# Pesa City — Fable 5 Session 2 Prompt

> Use this after Session 1 is complete. Paste everything below this line.

---

## Context

You are continuing development of **PesaQuest / Pesa City** — a gamified financial literacy platform for Kenyan NGO **Moski**. Laravel 12 · Blade · Alpine.js · TailwindCSS · MySQL · WebAudio API (procedural, no MP3s).

**Working directory:** `C:\xampp\htdocs\finlit`  
**DB:** MySQL `pesaquest` (from .env)  
**Admin login:** admin@moski.org / admin123

## Memory system

Read `MEMORY.md` at `C:\Users\user\.claude\projects\C--xampp-htdocs-finlit\memory\` first — it has the full project history. Also read `project_fable_handover.md` in that directory for what was done in the session before Session 1.

## What Session 1 completed

Session 1 covered all fixes:
- Marketplace popup scroll fixed (buy button reachable)
- Fun World: purchase popup + sound + mood decay + admin-configurable activities (`fun_world_activities` table)
- game_age fully removed from all user-facing views; "Days Lived" and "Events" now pull real data
- Chama area uses game ticks not real days
- Quest start popup working
- Credit score uses more data points
- Bills contextual (asset-linked bills, flavor text, admin CRUD)

If any of those were NOT completed in Session 1 (context ran out), read the current state of the relevant files before starting and finish them first before moving to the builds below.

---

## Critical facts — read before touching any file

- `UserProgress::points_total` = XP field. Never use `xp`.
- Always call `$progress->calculateLevel()` after updating `points_total`.
- Chapters are net-worth based: `UserProgress::chapterFromNetWorth(int $netWorth)` — not age.
- Game time = ticks. `$progress->tick_count` is the game day counter. Use `GameClock::ticksSince()` to convert real time to ticks. Never use `Carbon::diffInDays()` for gameplay.
- Images live at `public/uploads/profiles/` — not `/storage/`.
- `QuestTriggerService::fire($user, $triggerType, $context)` — call this on every meaningful player action.
- Sound: procedural WebAudio via `SoundMgr` in `public/js/world.js`. No external files. Follow existing patterns.
- Every new page must use `<x-mobile-bottom-nav>` and work at 375px width.
- Admin cannot run CLI on live server — any migration must also be whitelisted in the admin Artisan web runner (`AdminController` around line 700).

---

## Design system — apply to everything built

- **Background:** `#07060f` or `bg-black` with `backdrop-blur`
- **Cards:** `rounded-2xl` / `rounded-3xl`, semi-transparent dark surface, colored border at `rgba(color, 0.2)`
- **Accent colors:** violet/purple = XP/level · emerald = money/positive · amber = warnings · red = danger · cyan = investments · indigo = learning/quests
- **Text:** `font-black` for headings · `text-xs uppercase tracking-wider` for labels · `font-semibold` for body
- **Layout:** single-column mobile, 2-column desktop grid — match `/dashboard` breakpoints exactly
- **Never** hardcode numbers as display values — always pull from DB/model

---

## BUILD 1 — /life : Full Life Board

**Route:** `GET /life` → `LifeController::board()` → `resources/views/life/board.blade.php`

Read the current state of `life/board.blade.php` and `LifeController.php` before starting — some of this page may already exist. Build or complete it to include all sections below.

### Sections (in this order, top to bottom)

**1. Character identity bar** (full width)
- Avatar (`$user->profile_photo`), name, chapter icon + name (net-worth based), level badge
- Career title pill, financial personality tag, net worth figure
- Mood indicator: emoji + bar (😄 80+ · 😊 60–79 · 😐 40–59 · 😔 below 40) + "Visit Fun World →" when mood < 60

**2. Life Chapter card**
- Chapter name + icon + tagline (`$progress->chapterTagline()`)
- Net worth progress bar toward next threshold — use `$progress->netWorthToNextChapter()` and `$progress->nextChapterNetWorth()`
- Chapter mini-map: 6 stages with KES milestones (0 / 50K / 200K / 1M / 5M / 20M), current stage highlighted
- "Ksh X more to reach [Next Chapter]" label

**3. Monthly P&L panel** (2 columns desktop: income left, outgoings right)
- Income: salary (`$progress->career_income_rate`), Pesa City job salary (from `PlayerCityJob`), asset passive income (from `PlayerAsset` sum of `monthly_income`)
- Outgoings: bills (sum of active `PlayerBill` amounts per 30 ticks), loan payments (sum of `PlayerLoan` payment amounts due per 30 ticks), asset running costs
- Net monthly cashflow = income − outgoings, colored emerald if positive, red if negative

**4. Bills & Credit** (2 columns desktop)
- Left: active `PlayerBill` list — each bill shows icon, name, amount, frequency, next due (in game days using `$pb->ticksUntilDue($progress->tick_count)`), urgency color (overdue=red, ≤5 ticks=amber, ok=green). Include `bill->flavor_text` as a subtle italic line below name.
- Right: credit score gauge (300–850 arc), score number, label (Very Poor/Poor/Fair/Good/Excellent), last 4 changes from `GameNotification` where type = `credit_change`

**5. Owned assets** (card grid: 2 cols mobile, 3 cols desktop)
- Pull `PlayerAsset` where `user_id = auth()->id()` and `status = active`, with `asset` relation
- Each card: asset `image_url` (or placeholder icon), name, category badge, current value vs purchase price, condition bar (color: green ≥70, amber 40–69, red <40), monthly income/cost
- Group by category with a small section header

**6. Life Events feed**
- Last 6 `PlayerLifeEvent` for this user, with `lifeEvent` relation
- Show: icon, title, description, balance_change (colored), chapter at trigger, "Tick X" (game day)
- If no events: empty state "Your story is just beginning — keep playing to unlock life events"

### Controller requirements

`LifeController::board()` must pass to the view:
```php
$user, $progress, $netWorth (= $progress->net_worth_cache),
$activePlayerBills (with bill relation, status active|overdue),
$overdueBills, $upcomingBills,
$playerAssets (with asset relation, status active),
$playerLoans (with loanProduct, status active),
$playerCityJobs (with job relation, status active),
$recentLifeEvents (last 6, with lifeEvent),
$monthlyIncome, $monthlyOutgoings, $netMonthly,
$creditHistory (last 4 GameNotification where type=credit_change)
```

---

## BUILD 2 — /portfolio : Investment & Assets Portfolio

**Route:** Create `GET /portfolio` → new `PortfolioController::index()` → new `resources/views/portfolio/index.blade.php`

Add to `routes/web.php` inside the auth middleware group.  
Add "Portfolio" link to the main navigation component (`resources/views/components/navigation.blade.php` or wherever nav lives — search for "Dashboard" or "Life" links to find it).  
Add `portfolio` as a recognised active state in `<x-mobile-bottom-nav>`.

### Sections (in order)

**1. Portfolio summary bar** (horizontal scroll on mobile, grid on desktop)
Five stat tiles:
- Total asset value (sum of `PlayerAsset.current_value` where active)
- Total invested (sum of `PlayerAsset.purchase_price` + sum of active `PlayerDeal.amount_invested`)
- Unrealised P&L (total value − total invested), show % and absolute, colored
- Monthly passive income (sum of asset `monthly_income` × conditionFactor)
- Active positions count

**2. Active investment deals** (cards)
- `PlayerDeal` where `status = pending`, with `deal` relation
- Each card: deal icon, name, amount invested, expected return range (`min_return_pct`–`max_return_pct`%), risk badge (color by `success_probability`: ≥0.7=green, 0.4–0.69=amber, <0.4=red), game days remaining (`$pd->resolve_at_tick - $progress->tick_count`), lesson text
- Empty state: "No active deals — visit Equity Square to invest"

**3. Completed deals** (collapsible table or card list)
- `PlayerDeal` where `status = success|failed`, ordered by `resolved_at` desc, last 10
- Show: deal name, amount invested, profit/loss (colored), return % achieved, outcome badge (Success / Failed)
- Summary line: X deals total, total profit/loss

**4. Owned assets by category** (tabbed or sectioned)
Categories: Property · Vehicle · Business · Investment · Gadget · Other  
Each asset card: image, name, purchase price, current value, value change % (colored), condition bar, monthly income, monthly cost, net monthly. Small "View in Marketplace →" link.

**5. Savings schemes**
- `PlayerSavingsScheme` where `user_id` and `status = active` (check model/table name — may be `player_saving_schemes` or `savings_schemes`)
- Each card: name, icon, target amount, current amount, % progress bar, projected completion in game days (`($target - $current) / $monthly_contribution * 30` ticks), monthly contribution
- Empty state: "No active savings goals — visit Bank & Savings to start one"

**6. Active loans**
- `PlayerLoan` where `status = active`, with `loanProduct` relation
- Each card: loan name, original amount, outstanding balance, interest rate %, next payment amount, next payment due in game days (`$loan->next_payment_tick - $progress->tick_count`), payments made vs total
- Credit impact note: "Paying on time: +5 credit/payment"

**7. Net worth sparkline** (simple canvas chart)
Use `StockPriceHistory` data — this table records asset value snapshots per tick. Query the last 20 snapshots across all player assets, group by tick, sum values per tick, and draw a simple line chart on a `<canvas>` element using plain JS (no Chart.js — write 30–40 lines of canvas 2D API). Show a "your wealth is growing" message if the last value > first value.

### Controller requirements

`PortfolioController::index()` must pass:
```php
$user, $progress,
$totalAssetValue, $totalInvested, $unrealisedPnl, $monthlyPassiveIncome,
$activeDeals (PlayerDeal pending, with deal),
$completedDeals (last 10 resolved, with deal),
$assetsByCategory (PlayerAsset active grouped, with asset),
$savingsSchemes (active, with scheme if relational),
$activeLoans (PlayerLoan active, with loanProduct),
$sparklineData (array of [tick, value] for canvas chart)
```

---

## AFTER BOTH BUILDS: write fablerecommendations.md

Once `/life` and `/portfolio` are built and verified (php -l on all new PHP files, route:list confirms new routes), write `C:\xampp\htdocs\finlit\fablerecommendations.md`.

This is a document the project owner will review and selectively approve for future builds. Make it a proper markdown document they can read comfortably — not a bullet dump.

**Structure:**

```markdown
# Pesa City — Feature Extension Recommendations

## How to use this document
Brief explanation: owner reviews each section, ticks what they want, and those become the next session's build list.

## Recommendations

### [Feature Name]
**What it is:** 1 paragraph — be specific, set in Kenya/East Africa context
**Why it works:** How it increases engagement OR teaches financial literacy (or both)
**The hook:** What makes a player keep playing because of this feature
**Complexity:** Low / Medium / High
**Depends on:** What must already be built
**Rough approach:** 2–4 sentences on implementation
```

**Cover at minimum one idea from each of these categories** — but go further where you see genuine opportunity. Every idea must feel like it belongs in a Kenyan youth financial literacy game, not a generic RPG:

1. **Retention & daily habits** — streaks, comeback bonuses, seasonal events (school term start, harvest season, December spending traps)
2. **Social & community** — Chama leaderboards, peer challenges, Harambee events, sharing milestones
3. **Narrative depth** — life event story arcs, random financial crises (job loss, medical bill, unexpected opportunity), market shocks (maize prices drop, matatu fares spike)
4. **Mini-games** — budgeting puzzles, negotiation scenarios, market timing games, investment simulations
5. **Real-world tie-ins** — M-Pesa mechanics, SACCO membership, county-specific opportunities (Nairobi vs Kisumu vs Mombasa)
6. **Progression & prestige** — legacy scoring, mentor roles (high-level players help new ones), chapter prestige resets
7. **Educational depth** — concept unlocks (player earns the right to access loans after understanding credit), PesaAI tutoring moments, financial certificate milestones
8. **Live/time events** — Kenya public holidays (Jamhuri Day bonuses, Madaraka Day challenges), flash market events, limited-time deals
9. **Accessibility & onboarding** — first-time player experience, age-appropriate difficulty tuning, parent/teacher view
10. **Anything else** you identify that fits this game's mission

Aim for 12–18 recommendations total. Be opinionated — rank the top 5 by impact and mark them clearly.

---

## Final checks

After writing `fablerecommendations.md`:

```bash
php artisan route:list 2>&1 | tail -8
php -l app/Http/Controllers/PortfolioController.php
php -l app/Http/Controllers/LifeController.php
```

Confirm `/life` and `/portfolio` appear in the route list.

---

*Session 2 prompt — prepared by Sonnet 4.6. Session 1 covered all bug fixes. This session covers the two major new pages and the recommendations document.*
