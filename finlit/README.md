PesaQuest — Build Summaries
============================

Each section below summarises what was built in that session. Most recent at the top.

────────────────────────────────────────────────────────────────────────────────
SESSION 6 — June 2026: Phases B, C, E, G — Asset Conditions, Career Screen,
            Onboarding Wizard, Monthly Report Card
────────────────────────────────────────────────────────────────────────────────

WHAT WAS BUILT
--------------

PHASE B — ASSET HEALTH / CONDITION SYSTEM
  Migration: database/migrations/2026_06_19_000001_add_condition_to_player_assets.php
    - Adds `condition` TINYINT UNSIGNED DEFAULT 100 to player_assets
  PlayerAsset model (app/Models/PlayerAsset.php):
    - conditionFactor(): 100–70% → 1.0x income, 69–40% → 0.7x, 39–20% → 0.4x, <20% → 0x
    - monthlyCashFlow() applies conditionFactor() to income (costs still full)
    - conditionLabel(): Excellent / Good / Fair / Warning / Broken
    - conditionColor(): returns hex colour (#10b981 → #ef4444)
    - maintenanceCost(): max(500, base_cost×3 or purchase_price×4%)
  LifeSimulator (app/Services/LifeSimulator.php):
    - settleAssets() degrades condition by 3pts per game-month
    - Fires warning notifications at condition crossings: 70 (amber), 40 (red), 20 (broken)
    - Applies conditionFactor() to income per asset
  Life Board (resources/views/life/board.blade.php):
    - Each income-generating asset card now shows a condition health bar
    - "🔧 Maintain — Ksh X" button fires POST /life/assets/{id}/maintain via Alpine fetch
    - maintain() method added to lifeBoard() Alpine component

PHASE B — ASSET-TRIGGERED LIFE EVENTS
  Migration: database/migrations/2026_06_19_000002_add_asset_category_to_life_events.php
    - Adds `asset_category` VARCHAR(30) NULLABLE to life_events
    - Adds `last_month_reported` SMALLINT to life_events
    - Adds `last_month_report_tick` SMALLINT to user_progress
  LifeEvent model (app/Models/LifeEvent.php):
    - Added `asset_category` to $fillable
    - scopeForAssetCategories(): only returns general events OR events matching owned categories
  LifeSimulator: rollLifeEvents() now gathers player's owned asset categories and uses scope
  Seeder (database/seeders/AssetLifeEventsSeeder.php) — 15 events:
    Vehicle: boda-rider-quit, boda-accident-repair, vehicle-great-month
    Property: tenant-missed-rent, tenant-paid-early, property-plumbing-burst
    Business: business-competitor-arrived, business-viral-moment, business-stock-theft
    Investment: market-rally (+9%), market-correction (-8%)
    Social/Harambee: cousin-harambee, school-fees-request, colleague-funeral-collection,
                     wedding-contribution, unexpected-windfall-uncle
  DatabaseSeeder: AssetLifeEventsSeeder::class registered

PHASE B — ASSET MAINTENANCE ENDPOINT
  Route: POST /life/assets/{playerAsset}/maintain → LifeController::maintain()
  Logic: deducts maintenanceCost() from balance, boosts condition by +40 (capped at 100)
  Creates a 'asset_maintained' GameNotification

PHASE C — CAREER SCREEN
  Route: GET /life/career → LifeController::career() → life.career view
  View: resources/views/life/career.blade.php
    - Career banner: field icon + color, career title, gross + net salary
    - Payslip card: gross → PAYE → NHIF → NSSF → loan deductions → NET PAY
      with red/green deduction vs take-home bar and a tax rate display
    - Career ladder: 7 rungs (Intern → Executive/Partner) with salary ranges
      current rung highlighted in amber, completed rungs in emerald with ✓
      "how to advance" guidance box under the ladder
    - Career fields sidebar: all 5 fields with icons and current field highlighted
    - Recent paydays: last 6 salary GameNotifications
    - Financial tip cards (contextual: SACCO, 10% investment rule, NHIF/NSSF value)
  Controller feeds: payslip (from CareerService::generatePayslip), fieldMeta,
    careerLadder array, currentRung, nextRung, salaryToNextRung, salaryHistory,
    allFields (CareerService::FIELDS)
  Life Board nav: "💼 Career" pill added to nav bar

PHASE E — ONBOARDING WIZARD
  Route: POST /life/onboard → LifeController::onboard()
  Trigger: DashboardController passes $needsOnboarding = (career_field is null AND income = 0)
  Modal: full-screen glass overlay in dashboard.blade.php (not dismissible — must pick a situation)
  5 starting situations:
    junior_employee   → Office Assistant,   Ksh 30,000/mo + Ksh 15,000 bonus
    government_worker → Civil Servant,      Ksh 45,000/mo + Ksh 22,000 bonus
    tech_professional → Software Developer, Ksh 85,000/mo + Ksh 40,000 bonus
    freelancer        → Freelancer,         Ksh 28,000/mo + Ksh 12,000 bonus
    healthcare_worker → Clinical Officer,   Ksh 55,000/mo + Ksh 27,000 bonus
  On selection: sets career_field, career_title, career_income_rate, adds bonus to balance
  Creates 'career_start' GameNotification

PHASE G — MONTHLY REPORT CARD
  Trigger: DashboardController computes tick delta on login; if floor(tickAfter/30) >
           floor(tickBefore/30), $monthlyReport is populated
  Data: total_in, total_out, net, savings_rate, grade (A/B/C/D)
  Modal: elegant overlay in dashboard.blade.php — shows grade (giant letter in grade colour),
         stat table (income / expenses / net / savings rate), horizontal savings bar,
         motivational message per grade, "Got it — keep going!" dismiss button
  Grades: A ≥30%, B ≥15%, C ≥0%, D negative

────────────────────────────────────────────────────────────────────────────────
HOW TO USE (Session 6 features)
────────────────────────────────────────────────────────────────────────────────

  Run migrations:
    php artisan migrate

  Seed new events:
    php artisan db:seed --class=AssetLifeEventsSeeder

  OR full reseed (dev only):
    php artisan migrate:fresh --seed

  First login for a new player:
    1. Registers → dashboard loads → Onboarding Wizard fires (full-screen, can't dismiss)
    2. Player picks a starting situation → career set, bonus deposited
    3. Play scenarios to earn ticks → assets degrade over time
    4. Visit /life to see condition bars on asset cards; click Maintain to restore
    5. Visit /life/career to see full payslip breakdown and career ladder
    6. After 30 ticks (1 game month), Monthly Report Card fires on next login

────────────────────────────────────────────────────────────────────────────────
FURTHER ENHANCEMENTS (Phase 6 remaining / next sessions)
────────────────────────────────────────────────────────────────────────────────

  Phase D (Rival comparison): show "Username just bought a plot" in Life Feed
    → pull from leaderboard of players in same life chapter
  Phase F (Visual home): SVG room illustration that upgrades with net worth
    → bedsitter → studio → apartment → house → villa
  Maintenance events in scenario layer: fire a scenario card when condition < 40
    → player chooses Repair / Ignore / Sell, affecting condition directly
  Salary negotiation events: after 365 ticks in same role, career-growth scenario fires
  Chama integration on Life Board: show Chama name and next payout date
  Career field change: add 'job_offer' life events that call onboard-like flow
  Admin: add bulk life_event management to GameSet panel
  Analytics: "What % of players are in deficit?" chart in admin dashboard

────────────────────────────────────────────────────────────────────────────────
SESSION 5 — June 2026: Life Board (Phase A of Life Simulation Redesign)
────────────────────────────────────────────────────────────────────────────────

CONTEXT & MOTIVATION
After testers said the game had "no soul", PesaQuest is evolving from a
scenario-quiz into a persistent life simulation. The Life Board is Phase A
of that transition: a full game-HUD view of the player's financial life,
built on top of the existing Life Simulator tick engine, asset system, and
bill system — all of which already run in the background.

The design goal: a player should be able to look at the Life Board and
immediately feel their financial situation — not read it, feel it. Assets
should feel like living businesses. Bills should feel urgent. Progress should
feel real.

────────────────────────────────────────────────────────────────────────────────
LIFE BOARD — COMPLETE FEATURE REFERENCE
────────────────────────────────────────────────────────────────────────────────

URL:   GET /life
Route: life.board
View:  resources/views/life/board.blade.php
Ctrl:  app/Http/Controllers/LifeController::board()

HOW TO ACCESS
─────────────
• Dashboard hero: "🏠 My Life Board" button (new CTA alongside "Play Now")
• Dashboard nav: "🏠 Life Board" link in the top navigation bar
• Direct URL: /life

────────────────────────────────────────────────────────────────────────────────
SECTION-BY-SECTION BREAKDOWN
────────────────────────────────────────────────────────────────────────────────

1. CHARACTER BANNER (top of page)
   What it shows:
   — Player name (large, bold)
   — Location: dynamically assigned from net worth
       Eastleigh → Embakasi → Kasarani → Kilimani → Westlands → Karen
   — Life Chapter: 🎒 The Graduate / 💪 The Hustler / 🏠 The Settler / etc.
   — Game age
   — Career title (if unlocked via scenarios)
   — Financial personality (if assessed)
   — Level badge
   — Asset count
   — Chapter progress bar: years remaining to next chapter unlock
   — Quick stats: Balance | Net Worth | Monthly Net (right side)

   Data source: $progress (UserProgress model) + computed from PlayerAsset collection

2. GAME BALANCE (M-Pesa style wallet card)
   — Large current balance in KSH (emerald if net positive, red if deficit)
   — Net worth below in smaller text
   — Monthly net pill: shows +/- Ksh X/mo and savings rate %
   — Card turns red automatically when expenses > income
   — Styled to look like an M-Pesa-inspired balance screen

3. MONTHLY STATEMENT
   — Income section: Salary (career_income_rate from UserProgress)
                     Passive income (sum of all active asset monthly_income × quantity)
   — Expenses section: Bills & Living (all player bills normalised to 30-tick month)
                       Asset Running Costs (sum of asset monthly_cost × quantity)
   — Net result with income-vs-expense bar
   — Savings rate percentage shown in bar labels
   — If income = 0, shows guidance to play scenarios to unlock career

   Data: $salaryPerMonth, $assetIncomePerMonth, $billsBurnPerMonth, $assetCostsPerMonth
   computed in LifeController::board()

4. BILLS BOARD
   — All active + overdue player bills, sorted by next_due_tick (soonest first)
   — Shows up to 8 bills in urgency-coloured rows:
       OVERDUE (red background) → due_tick < current_tick
       URGENT  (amber)          → due in ≤ 5 game days
       SOON    (yellow)         → due in ≤ 15 game days
       OK      (neutral)        → due in > 15 game days
   — "Pay" button appears on OVERDUE and URGENT bills
   — Pay button calls existing POST /life/bills/{id}/pay endpoint via Alpine fetch
   — On success: toast confirmation + auto-reload
   — Header shows "X OVERDUE" badge (pulsing red) if any bills are overdue

5. CREDIT SCORE GAUGE
   — Range 300 (Very Poor) to 850 (Excellent)
   — Visual gradient bar (red → orange → yellow → green → emerald)
   — White needle marker at exact score position with glow effect
   — Score number + label shown in appropriate color
   — Explains that on-time bill payment improves score
   — Affects loan access in the game (existing credit mechanics)

6. NEXT UNLOCK / MILESTONE BAR
   — Finds the cheapest active asset the player cannot yet afford
   — Shows: asset icon, name, price, category, net income/mo
   — Progress bar: current balance / asset price → shimmering indigo bar
   — "X game days away" counter: calculates (needed / net monthly) × 30
   — If net monthly ≤ 0: shows "Fix deficit first" warning instead
   — If all affordable assets already owned: hides the section
   — "Go to Marketplace →" link

7. YOUR WORLD (Asset Cards)
   — One trading-card per owned active PlayerAsset
   — 2-column grid on desktop, 1-column on mobile
   — Each card has a category-specific colour scheme:
       vehicle    → amber/orange tinted
       property   → blue/cyan tinted
       business   → purple/violet tinted
       investment → emerald/green tinted
       gadget     → slate/gray tinted
   — Card contents:
       Header: asset icon (large), name, category + quantity
       Status badge: Earning (green, pulsing) / Break Even (amber) /
                     Costly (red) / Appreciating (blue — no income/cost)
       Cash flow rows: Income/mo (green), Running costs/mo (red), Net/mo (bold)
       Value row: current value vs purchase price, gain/loss %
       Action buttons: Portfolio → and Trade →
   — Hover effect: lift + category-coloured shadow glow
   — Empty state: illustrated card with CTA to Marketplace

8. LIFE FEED
   — Last 8 life-sim related notifications (GameNotification records)
   — Types shown: life_sim, bill_paid, bill_missed, asset_income, salary, life_event
   — Each row: icon, title, body (truncated), time-ago
   — Left border color: green (positive events), red (negative), indigo (neutral)
   — "Full Timeline →" link to /life/timeline

────────────────────────────────────────────────────────────────────────────────
HOW THE LIFE SIMULATION WORKS UNDER THE HOOD
────────────────────────────────────────────────────────────────────────────────

1. Game Clock (app/Services/GameClock.php)
   — 1 game tick = 1 game day
   — Ticks are real-time based: every real-world minute = 1 game tick (configurable)
   — $clock->ticksSince($lastTickAt) calculates missed ticks since last login

2. LifeSimulator::processLogin() — runs on every dashboard load
   — Bills: settles all bills whose next_due_tick fell in the missed window
       · If balance covers it → deduct + credit score bonus
       · If not → mark overdue + credit score penalty
   — Assets: credits monthly_income, deducts monthly_cost for each month elapsed
   — Life Events: probabilistic roll against LifeEvent table (chapter-filtered)
   — Stock Prices: records price history point for portfolio charts
   — Updates: net_worth_cache = balance + sum(active player asset values)
   — Returns: events array + summary stats for "While You Were Away" modal

3. Bill Assignment (LifeSimulator::initializeBillsIfNeeded)
   — On first login, creates PlayerBill rows for all Bills where auto_assign=true
   — Bills are age-group or 'all' scoped
   — next_due_tick = current_tick + frequency_ticks

4. Asset Cash Flow (LifeSimulator::settleAssets)
   — For each month elapsed: credits monthly_income, deducts monthly_cost
   — Applies appreciation_rate monthly compounding
   — Adds volatility swing (random ± based on asset.volatility field)
   — Records new current_value on PlayerAsset

────────────────────────────────────────────────────────────────────────────────
TECHNICAL ARCHITECTURE
────────────────────────────────────────────────────────────────────────────────

Controller: app/Http/Controllers/LifeController.php
  Methods:
  — board()    → Life Board view (NEW this session)
  — timeline() → Life Timeline view (pre-existing)
  — payBill()  → JSON: pay a specific bill immediately (pre-existing)

Routes (routes/web.php, inside life. prefix group):
  GET  /life                          → life.board     (NEW)
  GET  /life/timeline                 → life.timeline
  POST /life/bills/{playerBill}/pay   → life.bills.pay

Key Models:
  — UserProgress: balance, net_worth_cache, credit_score, career_income_rate,
                  career_title, game_age, life_chapter, tick_count
  — PlayerAsset:  user_id, asset_id, current_value, purchase_price, quantity, status
  — PlayerBill:   user_id, bill_id, amount, frequency_ticks, next_due_tick, status
  — Asset:        name, icon, category, monthly_income, monthly_cost, appreciation_rate
  — Bill:         name, icon, amount, frequency_ticks, category, credit_impact_pay/miss

Computed in Controller (LifeController::board()):
  — $salaryPerMonth      = progress.career_income_rate
  — $assetIncomePerMonth = Σ (asset.monthly_income × quantity) for active assets
  — $billsBurnPerMonth   = Σ (bill.amount × (30/frequency_ticks)) for active bills
  — $assetCostsPerMonth  = Σ (asset.monthly_cost × quantity) for active assets
  — $netMonthly          = totalIncome - totalExpenses
  — $savingsRate         = netMonthly / totalIncome × 100
  — $progressToNextAsset = balance / nextAsset.base_price × 100
  — $daysToNextAsset     = (nextAsset.base_price - balance) / (netMonthly / 30)
  — $location            = neighborhood tier from net_worth_cache

────────────────────────────────────────────────────────────────────────────────
FURTHER ENHANCEMENT ROADMAP (what comes next)
────────────────────────────────────────────────────────────────────────────────

Phase B — Asset-Triggered Events
  [ ] Random events fired specifically because of WHAT you own
      (boda-boda rider quits, rental tenant misses payment, stock crashes)
  [ ] Asset health system: each asset has a 'condition' (0-100%)
      — condition degrades over time if no "maintenance" choice is made
      — below 50%: income drops; below 20%: asset stops earning
  [ ] Maintenance mode: player can "invest in maintenance" to restore condition

Phase C — Career Screen
  [ ] /life/career — view current job, salary, career progression
  [ ] Career tracks: employee → manager → director → entrepreneur
  [ ] Salary negotiation events (scenario-triggered)
  [ ] Side hustle unlocks: owning specific assets enables side income events

Phase D — Social Pressure System
  [ ] Harambee events: family requests (contribute Ksh X or lose social credit)
  [ ] Chama integration on Life Board: show your Chama's next payout
  [ ] Rival comparison: "Your classmate just bought a plot. You're still renting."
  [ ] WhatsApp-style investment group scam events (teach due diligence)

Phase E — Character Creation
  [ ] First-time player wizard: choose name, starting situation
  [ ] Starter scenarios: Employed / Freelancer / Business owner / Unemployed
  — Each gives different starting income_rate, balance, and assigned bills

Phase F — Visual Home / Room Illustration
  [ ] A simple SVG or image of the player's living space
  — Starts as a bedsitter, upgrades as net worth grows
  — Assets appear as items in the illustration
  — Location name updates with visual backdrop

Phase G — Monthly Report Card
  [ ] At the start of each game-month, a full-screen "Monthly Summary" fires
  — Shows: income earned, bills paid, savings rate, net worth change
  — Graded: "You saved 38% this month. Well done!" or "Month in the red — here's why"
  — Ties back to the WYWA modal concept already on the dashboard

────────────────────────────────────────────────────────────────────────────────
FILES CHANGED THIS SESSION
────────────────────────────────────────────────────────────────────────────────

app/Http/Controllers/LifeController.php   — board() method added; Asset + PlayerAsset imported
routes/web.php                            — GET /life → life.board route added
resources/views/life/board.blade.php      — NEW: Life Board view (~450 lines)
resources/views/dashboard.blade.php      — "Life Board" link in nav + hero CTA button added

MIGRATIONS STILL PENDING (from Session 4):
  php artisan migrate
  (runs trade_listings, financial_personality fields, financial_crises — 3 migrations)

────────────────────────────────────────────────────────────────────────────────
SESSION 4 — June 2026: Phase 4 Retention + Phase 5 New Mechanics
────────────────────────────────────────────────────────────────────────────────

PHASE 1 — CRITICAL BUG FIXES (13 fixes, 1 deletion)

1. UserProgress fillable fields — Added current_scenario_start_id, total_decisions,
   and consecutive_save_choices to $fillable. These were silently ignored by mass
   assignment, causing badge tracking and save-streak logic to never persist.

2. Duplicate str_contains in choose() — The consecutive_save_choices increment
   had `str_contains(..., 'save') || str_contains(..., 'save')` (duplicate). Fixed
   second check to 'invest' so invest-type choices also count toward the streak.

3. Investment full-balance fallback removed — When a scenario choice had
   balance_change = 0, the code used to invest the player's ENTIRE balance as a
   fallback. Now skips investment creation if amount is 0 or exceeds balance.

4. Market event sign error — Penalty events with negative effect_amount were
   accidentally crediting players instead of penalising them. Fixed by using
   abs($marketEvent->effect_amount) for both bonus and penalty calculations.

5. Leaderboard rank fixed — $myRank was returning points_total (a score, not a
   rank). Now correctly calculated as: count of players with higher points + 1.
   Leaderboard now uses ->values() so array indices reset to 0 after filtering.

6. submitQuest() age group check — Players could submit quests for any age group.
   Added abort_if check so a quest's age_group must match the player's age_group.

7. claimDailyBonus() race condition — Added DB::transaction() with lockForUpdate()
   on the UserProgress row. Two simultaneous requests can no longer both claim the
   bonus. Bonus amount now reads from Setting::get('daily_bonus_amount', 1000).

8. LifeSimulator null appreciation_rate — Changed guard from `!= 0` to
   `(?? 0) != 0` so null rates are treated as zero, eliminating PHP warnings.

9. MpesaController callback error handling — On processing failure, now returns
   HTTP 500 with ResultCode 1 so Safaricom retries. Previously always returned 200.

10. DashboardController try/catch — LifeSimulator::processLogin() is now wrapped
    in try/catch. On failure, $lifeSim = [] so the dashboard still loads. Error
    logged to Laravel log.

11. Level-up detection — DashboardController now stores $levelBefore before
    processLogin(), then computes $leveledUp = new_level > level_before.
    $leveledUp is passed to the dashboard view.

12. User::getPortfolioValueAttribute() — New accessor on User model. Replaced
    identical calculation in ProfileController::edit() and show() with a single
    $user->portfolio_value call.

13. DashboardController — Loads daily challenges for the dashboard widget.
    Added DailyChallenge and UserDailyChallenge model imports.

Deleted: app/Http/Controllers/NodeController.php — empty dead-code file with no routes.


PHASE 2 — COMPLETED HALF-BUILT FEATURES

1. Notification bell in navigation.blade.php — Added a notification bell icon
   with an unread count badge to the layouts/navigation.blade.php (the nav used
   on profile, portfolio, marketplace, and subscription pages). Badge uses a live
   inline DB query for accuracy.

2. Quest board view and route — Created resources/views/game/quests.blade.php
   with filter tabs (All / Available / Submitted / Completed), animated quest
   cards showing icon, description, instructions, XP reward, and status pill.
   Submit button fires POST /game/quests/{id}/submit via Alpine fetch. Added
   GET /game/quests route → GameController::questBoard(). The questBoard()
   method loads quests filtered by player age group with completion stats.

3. Daily challenges on dashboard — DashboardController now loads today's
   challenges with user progress. A new "Today's Challenges" card is shown in
   the right column of the dashboard between the Daily Streak and Badges sections.
   Shows progress bar, completion count, and claim state for each challenge.

4. Level-up celebration — When $leveledUp is true, an animated overlay fires on
   the dashboard showing the new level number with the level icon and confetti.
   Reuses the existing dashboardConfetti() function. Dismiss with a click or the
   CTA button.


PHASE 3 — CONTENT EXPANSION

1. AdultScenariosSeeder — 25 new Kenya-specific scenarios for the 26+ age group.
   File: database/seeders/AdultScenariosSeeder.php
   Topics covered:
   — SACCO loan strategy (patience beats bank rates)
   — Digital lending spiral (Fuliza compounding trap)
   — Emergency fund sizing (3–6 months rule)
   — Land title due diligence (fraud prevention)
   — Crypto scam awareness (CMA verification)
   — Freelance tax declaration (iTax compliance)
   — Joint finances after marriage (proportional model)
   — Will and estate planning
   — Inheritance management (debt-first order)
   — NSE direct investing (diversification)
   — T-Bills vs savings accounts
   — Salary advance addiction (payday buffer fix)
   — Parental support obligations (fair sharing)
   — Business partner exit (structured buyout)
   — Credit card debt (always clear with savings)
   — Rental income management (reinvest passive income)
   — Health insurance upgrade (private vs NHIF)
   — Business expansion loan (calculated debt)
   — Job abroad decision (structured diaspora move)
   — Car insurance claim process
   — Stock market crash response (hold strategy)
   — Buying an existing business (due diligence)
   — Chama governance crisis (early withdrawal policy)
   — Digital savings automation (pay yourself first)
   — NSE dividend strategy (income portfolio)
   Registered in DatabaseSeeder.php.

2. Landing page all-ages positioning:
   — "Kids Love Learning About Money" section → "Real Money Skills At Every Age"
   — Three age cards: 8-17 (pocket money), 18-25 (salary/NHIF), 26+ (mortgage/SACCO)
   — Hero subtitle updated to "Navigate real Kenyan financial decisions — from pocket
     money to pension planning"
   — Marquee ticker updated with adult financial topics (SACCO, NSE, T-Bills,
     Mortgages, "Ages 8 to 60+")
   — Scenario count updated from "50+" to "75+"
   — Meta keywords updated to include adult finance topics, removed kids-only focus


FILES CHANGED THIS SESSION

app/Models/UserProgress.php           — fillable fields added
app/Models/User.php                   — portfolioValue accessor added
app/Http/Controllers/GameController.php — 7 bugs fixed, questBoard() added
app/Http/Controllers/DashboardController.php — try/catch, leveledUp, challenges
app/Http/Controllers/ProfileController.php — uses portfolio_value accessor
app/Http/Controllers/MpesaController.php  — returns 500 on failure
app/Services/LifeSimulator.php         — null appreciation_rate guard fixed
resources/views/layouts/navigation.blade.php — notification bell added
resources/views/game/quests.blade.php  — NEW: quest board view
resources/views/dashboard.blade.php   — challenges panel + level-up overlay + Quests nav link
resources/views/landing.blade.php     — all-ages positioning
database/seeders/AdultScenariosSeeder.php — NEW: 25 adult scenarios
database/seeders/DatabaseSeeder.php   — AdultScenariosSeeder registered
routes/web.php                        — GET /game/quests route added
app/Http/Controllers/NodeController.php — DELETED (dead code)

TO RUN THE NEW SEEDER (adult scenarios only, safe to run on existing data):
  php artisan db:seed --class=AdultScenariosSeeder


────────────────────────────────────────────────────────────────────────────────
SESSION 4 — June 2026: Phase 4 Retention + Phase 5 New Mechanics
────────────────────────────────────────────────────────────────────────────────

PHASE 4 — RETENTION AND ENGAGEMENT LOOPS

1. Streak at-risk warning — DashboardController now computes $streakAtRisk (streak > 1 and
   last_activity_date was yesterday). A red warning banner appears in the streak card if you
   haven't played today but have a streak to protect.

2. "While You Were Away" modal — When LifeSimulator returns events on login, a full-screen
   modal fires before the level-up overlay. Shows: time elapsed, balance/net worth/age stats,
   each event with icon, text, delta (green/red), and the edu tip when present.

3. Weekly summary email — New system:
   app/Mail/WeeklySummaryMail.php — Mailable with full player summary
   resources/views/emails/weekly-summary.blade.php — dark-themed HTML email
   app/Console/Commands/SendWeeklyEmails.php — artisan game:weekly-emails
   routes/console.php — scheduled every Monday at 9am

4. Net worth leaderboard tab — GameController::leaderboard() now accepts ?sort=networth.
   Sorts by net_worth_cache. leaderboard.blade.php has XP / Net Worth toggle tabs.
   My rank is calculated correctly for both sort modes.

5. P2P Asset Trade Market — Full player-to-player asset marketplace:
   database/migrations/2026_06_18_100000_create_trade_listings_table.php
   app/Models/TradeListing.php — seller_id, player_asset_id, asking_price, status, buyer_id
   app/Http/Controllers/TradeController.php — index, list, buy, cancel
   routes/web.php — GET/POST /trade/* routes under 'trade.' namespace
   resources/views/trade/index.blade.php — browse listings, educational tip
   resources/views/marketplace/portfolio.blade.php — "Trade" button added to asset cards
   dashboard.blade.php — "Trade" link in quick nav bar

PHASE 5 — NEW GAME MECHANICS

1. Financial personality assessment — fires every 10 decisions:
   database/migrations/2026_06_18_100001_add_financial_personality_to_user_progress.php
   UserProgress: financial_personality + last_assessment_at_decision fields added
   GameController::choose() — includes showAssessment:true in JSON when decisions%10===0
   GameController::savePersonality() — POST /game/personality saves result
   play.blade.php — 4-question Alpine modal, computePersonality() scoring, 8 personality types
   dashboard.blade.php — shows 🧠 personality name in hero badges row

2. Financial crisis events (server-wide, admin-triggered):
   database/migrations/2026_06_18_100002_create_financial_crises_table.php
   app/Models/FinancialCrisis.php — name, effect_type, effect_amount, warning_at, active_from/until
   app/Console/Commands/ProcessFinancialCrises.php — sends warnings + applies effects (4 types)
   routes/console.php — game:process-crises runs hourly
   AdminController — createCrisis() + deleteCrisis() methods, index() passes $crises
   admin/panel.blade.php — new "🌪 Crises" tab with create form + list

3. Budget planner expansion — Two new tools added to the dashboard tools grid:
   Mkopo Planner (Tool 5) — Loan amortisation: monthly payment, total repaid, total interest
   Faida Compounder (Tool 6) — Compound interest: lump sum + monthly at 4 Kenyan rate tiers
   mkopoTool() + faidaTool() Alpine functions added to dashboard script block.

FILES CHANGED/CREATED THIS SESSION

app/Http/Controllers/DashboardController.php — streakAtRisk added
app/Http/Controllers/GameController.php      — leaderboard networth tab, savePersonality(), showAssessment
app/Http/Controllers/AdminController.php     — createCrisis, deleteCrisis, $crises in index
app/Http/Controllers/TradeController.php     — NEW: P2P trade market controller
app/Models/TradeListing.php                  — NEW: trade listings model
app/Models/FinancialCrisis.php               — NEW: financial crisis model
app/Models/UserProgress.php                  — financial_personality fields added to fillable
app/Mail/WeeklySummaryMail.php               — NEW: weekly email Mailable
app/Console/Commands/SendWeeklyEmails.php    — NEW: artisan command
app/Console/Commands/ProcessFinancialCrises.php — NEW: crisis warning + effect processor
database/migrations/2026_06_18_100000_create_trade_listings_table.php — NEW
database/migrations/2026_06_18_100001_add_financial_personality_to_user_progress.php — NEW
database/migrations/2026_06_18_100002_create_financial_crises_table.php — NEW
resources/views/trade/index.blade.php        — NEW: P2P trade market view
resources/views/emails/weekly-summary.blade.php — NEW: email template
resources/views/game/leaderboard.blade.php   — XP/Net Worth sort tabs
resources/views/game/play.blade.php          — personality assessment modal + Alpine methods
resources/views/marketplace/portfolio.blade.php — Trade button + list-for-trade modal
resources/views/dashboard.blade.php         — WYWA modal, streak warning, personality badge,
                                               Trade nav link, Mkopo + Faida tools, tools JS
resources/views/admin/panel.blade.php       — Crises tab + crisesPanel() Alpine function
routes/web.php                              — TradeController import + /trade routes + /game/personality
routes/console.php                          — weekly email + crisis processing scheduled

TO RUN NEW MIGRATIONS:
  php artisan migrate

TO TEST CRISIS PROCESSING:
  php artisan game:process-crises

TO SEND WEEKLY EMAIL MANUALLY (test with --limit):
  php artisan game:weekly-emails --limit=1


────────────────────────────────────────────────────────────────────────────────
SESSION 2 — May 2026: School Subscriptions + AI Enhancements + Gameset
────────────────────────────────────────────────────────────────────────────────

Features: School subscription system (seats, portal_token, school_members table),
pesAI minimize-to-tray + suggestion chips + API key guard, Gameset scenario
search bar + created_at timestamps. See memory file project_school_sub_and_ai.md.


────────────────────────────────────────────────────────────────────────────────
SESSION 1 — May 2026: Bulk Scenarios + Admin Fixes + Free-for-all Toggle
────────────────────────────────────────────────────────────────────────────────

Features: 40 bulk scenarios across 4 age groups seeded, admin JS fixed, free-for-all
node access toggle, enhanced player profiles. See memory file
project_bulk_scenarios_and_fixes.md.
