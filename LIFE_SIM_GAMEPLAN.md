# PesaQuest — Life Simulator Game Plan

> This document is the single source of truth for the Life Simulator expansion.
> Reference it at the start of every new session before building.

---

## Vision

PesaQuest becomes a **continuous virtual life** where players manage real finances
in a fast-forwarding world. Time never stops. Log back in and your salary was
deposited, bills were deducted, your property gained value, and a life event may
have hit you. Every decision has lasting consequence.

---

## The Life Clock (Core Engine)

**Concept:** Virtual time runs faster than real time. Rate is set by admin.

| Admin Setting | Real time | Game time |
|---|---|---|
| 0.25 hrs/week | 15 min real | 1 game week |
| 0.5 hrs/week  | 30 min real | 1 game week |
| 1 hr/week     | 1 hour real | 1 game week |
| 2 hrs/week    | 2 hours real | 1 game week |
| 3 hrs/week    | 3 hours real | 1 game week |
| 6 hrs/week    | 6 hours real | 1 game week |
| 12 hrs/week   | 12 hours real | 1 game week |
| 24 hrs/week   | 24 hours real | 1 game week |

**1 game week = 7 game days (ticks). 1 tick = 1 game day.**

On every login the engine calculates ticks missed since last session (capped at 60
= 2 game months) and processes each one:
- Passive income credited (rent, dividends)
- Bills deducted (rent/mortgage, utilities, food, insurance)
- Asset values drift (appreciation / depreciation)
- Career salary deposited every 30 ticks
- Random life event rolls
- Credit score updated

The player sees a **"While You Were Away"** summary screen before landing on the
dashboard.

---

## Life Chapters

Life chapters replace flat age groups with a narrative arc. Each chapter gates
marketplace items, loans, and event types.

| Chapter | Age | Key unlocks |
|---|---|---|
| The Student | 8–17 | Pocket money, savings goals, small hustles |
| The Graduate | 18–22 | First job, renting, starting from zero |
| The Hustler | 23–28 | Career growth, first investment, first loan |
| The Settler | 29–40 | Mortgage, family costs, business stakes |
| The Builder | 41–55 | Passive income, portfolio, wealth transfer |
| The Elder | 56+ | Retirement planning, legacy |

---

## Financial Systems (Per-Tick)

### Income
- **Salary** — deposited every 30 ticks (1 game month). Uses existing CareerService
  with PAYE/NHIF/NSSF deductions already implemented.
- **Rental income** — daily passive from owned property (player_assets).
- **Business dividends** — weekly/monthly depending on asset type.
- **Investment maturity** — existing Investment model, now tick-driven.

### Recurring Bills (auto-deducted, player cannot opt out)
| Bill type | Frequency | Chapter gating |
|---|---|---|
| Rent / mortgage | Monthly (30 ticks) | Student+ |
| Electricity & water | Monthly | Graduate+ |
| Food & transport | Weekly (7 ticks) | All |
| Insurance (health) | Monthly | Hustler+ |
| Car insurance/fuel | Monthly | If vehicle owned |
| Loan repayments | Monthly | If loan active |

**Missed payment consequences:**
- Credit score -20 per missed payment
- Debt accrues penalty interest
- Services may be "suspended" (gameplay restriction)

### Credit Score (300–850)
| Event | Delta |
|---|---|
| On-time bill payment | +5 |
| Missed payment | -20 |
| Loan repaid in full | +30 |
| Debt-to-income > 50% | -10/month |
| Savings rate > 20% | +5/month |
| Long account history | +2/month |

Credit score gates loan access and some premium marketplace items.

### Net Worth
`net_worth = balance + sum(player_asset current_values) - sum(outstanding_loan_balances)`

Cached in `user_progress.net_worth_cache`, recalculated on every tick.

---

## The Marketplace

### Categories

**1. Real Estate**
- Studio apartment, 2BR flat, townhouse, plot of land
- Primary residence: removes rent bill, adds mortgage bill
- Investment property: generates rental income, appreciates
- Land: pure speculation, high volatility

**2. Vehicles**
- Motorbike — low cost, boda-boda income option, mild depreciation
- Personal car — depreciates ~1.5%/game month, adds fuel+insurance bill
- Matatu stake — generates income, high maintenance risk events

**3. Business & Investments**
- Kiosk, salon, tech startup, farm — each with risk/return profile
- Virtual stocks (Kenyan company proxies) — price fluctuates with market events
- T-bills, MMF, SACCOs — low risk, steady monthly return

### Asset Value Engine (per tick)
Each asset has: `base_price`, `current_value`, `monthly_income`, `monthly_cost`,
`appreciation_rate`, `volatility`, `risk_level`.

- Real estate: +0.1–0.5% per game month, location modifier
- Cars: -0.5% per game month (depreciation)
- Business: ±random within volatility band, correlated with market events
- Stocks: ±random, market event multiplier

### Player-to-Player Listings
Players can list owned assets for resale. Buyer pays listing price, seller gets
credited minus a 2% platform fee.

---

## "While You Were Away" Screen

Shown on dashboard login if ticks > 0. Components:
1. Animated fast-forward header showing game time elapsed
2. Itemised event list (salary paid, bills paid/missed, investments matured,
   random events triggered)
3. Deferred decisions queue (if a bill could not be paid, offer choices now)
4. Net worth delta (was X, now Y, change ±Z)
5. "Continue" button → clears the screen, lands on dashboard

---

## Database Tables to Build

```
player_life_state    — chapter, game_age, last_tick_at, credit_score (added to user_progress)
assets               — catalog: category, base_price, income_rate, cost_rate, volatility, min_chapter
player_assets        — owned: user_id, asset_id, current_value, quantity, purchased_at_tick
bills                — user_id, type, amount, frequency_ticks, next_due_tick, last_paid_tick
life_events          — catalog: chapter, effect_type, effect_data, probability, icon
player_life_events   — triggered: user_id, event_id, tick_triggered, resolved_at
stock_price_history  — asset_id, tick, price
market_listings      — seller_id, asset_id, player_asset_id, asking_price, status
```

---

## Build Phases

### Phase 1 — The Clock Engine ✅ IN PROGRESS
- [x] `GameClock` service: tick rate from admin setting, ticks-since calculation
- [x] `LifeSimulator` service: login catch-up processor, summary builder
- [x] Migration: add `last_tick_at`, `credit_score`, `net_worth_cache`, `life_chapter` to `user_progress`
- [x] Admin setting: `game_clock_real_hours_per_game_week` with UI in admin panel
- [x] `UserProgress` model: new fields + casts
- [x] `DashboardController`: run LifeSimulator on load, pass summary to view
- [x] "While You Were Away" modal/card on dashboard

### Phase 2 — Bills & Financial Consequences ✅ COMPLETE
- [x] `bills` catalog table + migration + `Bill` model
- [x] `player_bills` table + migration + `PlayerBill` model
- [x] `BillSeeder` — 25 Kenyan-context bills across all 4 age groups with rich flavor text
- [x] `LifeSimulator::settleBills()` — processes all due periods per catch-up session
- [x] `LifeSimulator::initializeBillsIfNeeded()` — auto-assigns age-group bills on first login
- [x] `UserProgress::adjustCreditScore()` — clamped 300–850
- [x] `LifeController::payBill()` — manual pay endpoint for overdue bills
- [x] `POST /life/bills/{playerBill}/pay` route
- [x] Dashboard "Bills & Credit" panel — credit score gauge, overdue alerts, upcoming list
- [x] GameNotification on bill payment/miss events

### Phase 3 — Marketplace v1 ✅ COMPLETE
- [x] `assets` catalog table + migration + model (`Asset.php`)
- [x] `player_assets` table + migration + model (`PlayerAsset.php`)
- [x] Asset seeder — 31 items: vehicles (Bajaj → Porsche), property (Mavoko plot → Kilimani 2BR), business (mama mboga → fuel station + tech startup), investments (MMF → private equity), gadgets (Tecno → laptop)
- [x] `/marketplace` route + `MarketplaceController@index` + view with category tabs, tier grouping, inspect modal
- [x] Buy flow: balance check, max_per_player guard, bill auto-creation, net worth recalc, GameNotification
- [x] `/portfolio` route + `MarketplaceController@portfolio` view — P&L, cash flow summary, sell button
- [x] Sell flow: 5% platform fee, bill cancellation if last of type, net worth recalc
- [x] `LifeSimulator::settleAssets()` — per-session monthly income credit, cost deduction, appreciation/depreciation with volatility
- [x] Dashboard nav links: 🛒 Marketplace + 💼 Portfolio
- [x] `DatabaseSeeder` updated to include `AssetSeeder`

### Phase 4 — Asset Economy ✅ COMPLETE
- [x] Per-tick appreciation/depreciation with volatility (`settleAssets()`)
- [x] Passive income crediting (rent, dividends) per session
- [x] Maintenance cost deduction per session
- [x] Net worth recalculation after every catch-up (`balance + sum(player_asset.current_value)`)
- [x] Market events affect asset category prices (`market_event` life event type)
- [x] `stock_price_history` table — snapshots for investment/business/property assets per session

### Phase 5 — Life Chapters & Events ✅ COMPLETE
- [x] `life_events` catalog table + migration + model + `LifeEventSeeder` (35 Kenyan events)
- [x] `player_life_events` table + migration + model
- [x] `game_age` field added to `user_progress`; initialized from age_group on first login
- [x] Chapter auto-advance: every 365 ticks = 1 game year; chapter derived from `game_age`
- [x] Chapter unlock fires in `processTick()` when chapter changes, appears in WYWA modal as MILESTONE
- [x] Random event roll: cumulative probability per session, max 3 per session
- [x] Life events: `balance_delta`, `compound`, `market_event`, `credit_delta`, `narrative` types
- [x] "While You Were Away" modal: chapter badge, edu note toggle (💡), milestone styling
- [x] Dashboard Life Chapter panel: chapter ring, game age, chapter progress bar, mini journey timeline, recent events
- [x] `/life/timeline` — full life story page grouped by chapter with vertical timeline, flavor text, edu notes

### Phase 6 — Social Layer
- [ ] Net worth leaderboard (separate from XP leaderboard)
- [ ] `market_listings` table: player-to-player resale
- [ ] Marketplace listings view + buy flow
- [ ] Community challenges tied to Kenyan financial literacy milestones

---

## Key Architectural Rules

1. **The tick processor is the single source of truth.** All financial changes happen
   inside `LifeSimulator::processTick()`. Never mutate balances elsewhere for
   recurring items.

2. **Ticks are idempotent by design.** `last_tick_at` is only advanced after all
   tick processing completes. A failed mid-tick does not corrupt state.

3. **Max catch-up is 60 ticks (2 game months).** Prevents infinite loops and runaway
   debt for players who disappear for months.

4. **Node decisions and the life sim are independent layers.** Node choices may grant
   bonuses / trigger one-time events, but do not replace the tick system.

5. **Admin sets the clock. Players cannot change it.** The `game_clock_real_hours_per_game_week`
   setting is admin-only. All players share the same clock speed.

---

## File Map (Life Sim specific)

```
app/Services/GameClock.php          — tick rate, game time calculations
app/Services/LifeSimulator.php      — login processor, tick engine
app/Models/Bill.php                 — (Phase 2)
app/Models/Asset.php                — (Phase 3) catalog
app/Models/PlayerAsset.php          — (Phase 3) owned
app/Models/LifeEvent.php            — (Phase 5)
app/Models/PlayerLifeEvent.php      — (Phase 5)
app/Http/Controllers/MarketplaceController.php   — (Phase 3)
app/Http/Controllers/PortfolioController.php     — (Phase 3)
resources/views/game/partials/life-sim-catchup.blade.php
resources/views/marketplace/index.blade.php      — (Phase 3)
resources/views/portfolio/index.blade.php        — (Phase 3)
database/migrations/*_add_life_sim_fields_to_user_progress.php
database/migrations/*_create_bills_table.php     — (Phase 2)
database/migrations/*_create_assets_table.php    — (Phase 3)
database/migrations/*_create_player_assets_table.php — (Phase 3)
```
