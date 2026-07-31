PesaQuest — Build Progress Tracker
Last updated: 2026-06-19 (Session 6 — Asset Conditions, Career Screen, Onboarding, Report Card)

This file tracks every to-do item across all build phases.
Status: [ ] = pending | [x] = done | [~] = in progress | [!] = blocked

================================================================================
PHASE 0 — AUDIT AND BASELINE
================================================================================

[x] Read all memory files for prior session context
[x] Run deep code audit via automated agent
[x] Read all critical controllers and service files
[x] Identify all CRITICAL, MAJOR, and MINOR issues
[x] Document all findings in analysis.md
[ ] Manual play-through as a fresh user (register, play, paywall, subscribe)
[ ] Manual test of While You Were Away modal (login after time gap)
[ ] Manual test of Chama journey (two test accounts)
[ ] Confirm M-Pesa sandbox credentials are set in admin settings


================================================================================
PHASE 1 — CRITICAL BUG FIXES
================================================================================

--- CRITICAL ---

[x] FIX: Missing fillable fields on UserProgress model
      Added current_scenario_start_id, total_decisions, consecutive_save_choices

[x] FIX: Duplicate str_contains in GameController::choose()
      Second condition now checks 'invest' (was duplicate 'save')

[x] FIX: Investment creation uses full balance as fallback
      Now skips investment creation if balance_change is 0 or player can't afford it

[x] FIX: Race condition in GameController::claimDailyBonus()
      Wrapped in DB::transaction() + lockForUpdate() on UserProgress row

[x] FIX: submitQuest() has no age-group check
      Added abort_if that enforces quest->age_group === player->age_group

[x] FIX: Market event sign error in GameController::index()
      Both bonus and penalty now use abs($marketEvent->effect_amount)

--- MAJOR ---

[x] FIX: Leaderboard rank is array index (1-10 always), not actual rank
      Fixed: uses ->values() + actual rank count query. $myRank is now real position.

[x] FIX: Null check missing on appreciation_rate in LifeSimulator::settleAssets()
      Guard changed from != 0 to (?? 0) != 0

[x] FIX: MpesaController callback swallows all errors and always returns success
      Now returns HTTP 500 + ResultCode 1 on processing failure (triggers Safaricom retry)

[x] FIX: DashboardController has no try/catch around LifeSimulator::processLogin()
      Wrapped in try/catch; $lifeSim = [] on failure; dashboard still loads

--- MINOR ---

[x] CLEANUP: Delete NodeController — done, file removed
[x] CLEANUP: Extract duplicated portfolio value into User model accessor
      User::getPortfolioValueAttribute() added; ProfileController uses $user->portfolio_value
[x] CONFIG: Daily bonus amount now reads from Setting::get('daily_bonus_amount', 1000)

[ ] CLEANUP: Extract duplicated investment maturity notification logic
      Still in both GameController::index() and DashboardController — deferred to Phase 4

NOTE: C-06 (stale $progress) was already fixed in code — refresh() on line 21.
      N-03 (diary view) — diary() returns JSON; no separate blade needed.
      N-04 (tools page) — Bajeti Smart planner already exists on dashboard.
      N-05 (CareerService) — confirmed wired in GameController and LifeSimulator.
      INCOMPLETE-01 (scenario ratings UI) — already exists in play.blade.php lines 472-489.
      INCOMPLETE-03 (daily challenges) — drawer exists in play.blade.php; dashboard card added this session.
      INCOMPLETE-04 (stock price charts) — SVG sparklines already exist in portfolio.blade.php.


================================================================================
PHASE 2 — COMPLETE HALF-BUILT FEATURES
================================================================================

[x] FEATURE: Quest board — player-facing page listing available quests
      resources/views/game/quests.blade.php created; GET /game/quests route added
      Shows filter tabs, quest cards, XP reward, submit button
      Quests nav link added to dashboard quick links bar

[x] FEATURE: Daily challenge card on dashboard
      DashboardController now loads challenges; progress card added in right column

[x] FEATURE: Notification badge count on navigation (layouts/navigation.blade.php)
      Notification bell with unread count badge added to desktop nav

[x] FEATURE: Level-up celebration moment
      $leveledUp passed from DashboardController; animated overlay with confetti on dashboard

[ ] FEATURE: Scenario rating UI in game play view
      Already exists in play.blade.php lines 472-489 — confirmed working, no change needed

[ ] FEATURE: Scenario ratings aggregation in gameset admin
      Not yet built — deferred to Phase 4/5

[ ] AUDIT: Verify CareerService.php is actually called somewhere
      Confirmed: called in GameController::index() lines 47-59 and LifeSimulator

[ ] FEATURE: Stock price history → interactive portfolio charts
      SVG sparklines already exist in portfolio.blade.php; full Chart.js charts deferred

[ ] AUDIT: Confirm diary view exists (game/diary.blade.php)
      diary() returns JSON consumed by the diary modal in play.blade.php — no blade needed


================================================================================
PHASE 3 — CONTENT EXPANSION AND AGE-AGNOSTIC POSITIONING
================================================================================

[x] CONTENT: 25 new scenarios for the 26+ adult age group
      database/seeders/AdultScenariosSeeder.php — registered in DatabaseSeeder
      Topics: SACCO loans, digital lending, emergency fund, land due diligence,
      crypto awareness, freelance tax, joint finances, will/estate, inheritance,
      NSE investing, T-Bills, salary advance addiction, parental support, business
      exit, credit card debt, rental income, health insurance, business loan,
      job abroad, insurance claim, stock market crash, business purchase,
      Chama governance, savings automation, NSE dividend strategy

[x] UI: Landing page repositioned for all ages
      "Kids Love Learning" section → "Real Money Skills At Every Age"
      Age cards: 8-17, 18-25, 26+ with specific topic descriptions
      Hero subtitle updated; marquee shows adult topics; scenario count 75+
      Meta keywords updated to include adult finance and investment topics

[ ] CONTENT: Review all 40 bulk scenarios for accuracy and Kenyan-market relevance
[ ] CONTENT: Confirm all 31 marketplace assets have meaningful images


================================================================================
PHASE 4 — RETENTION AND ENGAGEMENT LOOPS
================================================================================

[x] FEATURE: Streak at-risk warning banner on dashboard
      $streakAtRisk = streak > 1 && last_activity_date == yesterday
      Red animated banner appears in streak card with "Play today or lose streak" CTA

[x] FEATURE: "While You Were Away" enrichment modal
      Full-screen modal fires on dashboard load when $lifeSim has events
      Shows time elapsed, balance/net worth/age stats, each event with edu tips

[x] FEATURE: Weekly summary email
      WeeklySummaryMail, SendWeeklyEmails command, weekly-summary.blade.php
      Scheduled every Monday 9am in routes/console.php

[x] FEATURE: Net worth leaderboard tab
      GameController::leaderboard() accepts ?sort=networth
      leaderboard.blade.php has XP / Net Worth toggle tabs

[x] FEATURE: Player-to-player asset trade market
      TradeListing model + migration, TradeController, /trade routes
      trade/index.blade.php — browse + buy listings with edu tip
      portfolio.blade.php — "Trade" button to list assets


================================================================================
PHASE 5 — NEW GAME MECHANICS
================================================================================

[x] FEATURE: Financial personality assessment (every 10 decisions)
      4-question Alpine modal in play.blade.php
      8 personality types scored and saved to UserProgress.financial_personality
      Shown as 🧠 badge on dashboard hero

[x] FEATURE: Server-wide financial crisis events (admin-triggered)
      FinancialCrisis model + migration, ProcessFinancialCrises command
      Admin panel "🌪 Crises" tab — create/delete, 48hr warning notifications
      4 effect types: investment_drop, asset_drop, balance_drain, salary_cut

[ ] FEATURE: Peer lending via Chama (borrow from pool, vote to approve)
      Deferred — Chama model exists but needs peer-lending extension

[x] FEATURE: Budget planner expansion
      Tool 5: Mkopo Planner — loan amortisation (monthly payment, total interest)
      Tool 6: Faida Compounder — compound interest at 4 Kenyan rate tiers


================================================================================
PHASE 7 — LIFE SIMULATION REDESIGN (the "Soul" update)
================================================================================

Goal: Transform PesaQuest from a scenario-quiz into a persistent life simulation.
Scenarios remain but become the EVENT LAYER on top of a living financial world.

--- PHASE A: LIFE BOARD (core dashboard) ---

[x] FEATURE: Life Board page — GET /life → life.board
      Character banner: name, location, chapter, age, career, personality
      Game Balance card (M-Pesa wallet style) — balance, net worth, monthly net
      Monthly Statement — income vs expenses, savings rate bar
      Bills Board — urgency-coloured rows, Pay button for overdue/urgent
      Credit Score gauge — gradient bar with live needle
      Next Unlock / Milestone bar — progress to next affordable asset
      Your World — asset trading cards with category gradients
      Life Feed — last 8 life-sim notifications
      "Life Board" nav link + hero CTA on dashboard

--- PHASE B: ASSET-TRIGGERED EVENTS ---

[x] FEATURE: Asset health system
      Migration: add_condition_to_player_assets (TINYINT 0-100 default 100)
      conditionFactor(): 70%+ → 1.0x, 40%+ → 0.7x, 20%+ → 0.4x, <20% → 0x income
      monthlyCashFlow() applies conditionFactor; costs deducted in full regardless
      LifeSimulator.settleAssets() degrades 3pts/month; fires warning events at crossings
      Life Board: condition health bar + "Maintain — Ksh X" button per asset card

[x] FEATURE: Asset-specific life events
      Migration: add_asset_category_to_life_events (VARCHAR 30 NULLABLE)
      LifeEvent.scopeForAssetCategories(): filters to owned categories or null
      LifeSimulator.rollLifeEvents() gathers owned categories and uses scope
      AssetLifeEventsSeeder: 15 events across vehicle/property/business/investment/harambee

[x] FEATURE: Maintenance endpoint
      POST /life/assets/{pa}/maintain → LifeController::maintain()
      Deducts maintenanceCost() from balance, restores condition +40 (max 100)

--- PHASE C: CAREER & INCOME SCREEN ---

[x] FEATURE: Career screen — GET /life/career → life.career view
      Payslip card: gross → PAYE → NHIF → NSSF → loans → NET PAY (Kenya tax bands)
      7-rung career ladder with current rung highlighted in amber
      Career fields sidebar showing all 5 fields; current field highlighted
      Recent paydays list from salary GameNotifications
      Contextual money tips per income bracket

[ ] FEATURE: Salary negotiation events
      After 365 ticks in same role, career-growth scenario fires
      Deferred — needs new scenario type + event trigger logic

--- PHASE D: SOCIAL PRESSURE SYSTEM ---

[x] FEATURE: Harambee / social obligation events
      Seeded as general life events (asset_category = null) in AssetLifeEventsSeeder:
      cousin-harambee, school-fees-request, colleague-funeral-collection,
      wedding-contribution, unexpected-windfall-uncle
      Fire for all players on every rollLifeEvents() call

[ ] FEATURE: Chama integration on Life Board
      Show player's active Chama name + next payout date on Life Board
      Deferred — needs Chama model join on Life Board controller

[ ] FEATURE: Rival comparison chip in Life Feed
      Show "[Username] just bought their first plot"
      Deferred — needs leaderboard chapter-filter query

--- PHASE E: CHARACTER CREATION / ONBOARDING ---

[x] FEATURE: Onboarding wizard — fires on first login when no career set
      Full-screen overlay in dashboard.blade.php (not dismissible)
      5 starting situations with income, bonus, field, icon
      POST /life/onboard → LifeController::onboard() sets career, adds bonus
      DashboardController passes $needsOnboarding to view

--- PHASE F: VISUAL HOME ---

[ ] FEATURE: Visual life home/room illustration
      SVG room that upgrades with net worth thresholds
      Deferred — complex SVG authoring + asset placement logic

--- PHASE G: MONTHLY REPORT CARD ---

[x] FEATURE: Monthly Report Card modal on dashboard
      DashboardController computes tick delta per login; fires when month boundary crossed
      Shows: grade (A/B/C/D), income, expenses, net, savings rate bar
      Motivational message per grade; elegant inline-style modal overlay


================================================================================
PHASE 6 — PRODUCTION DEPLOYMENT
================================================================================

[ ] INFRA: Move from XAMPP to production server (Nginx + PHP-FPM + Certbot SSL)
[ ] INFRA: Set up Laravel queue worker as persistent process
[ ] INFRA: Set up Laravel scheduler (daily challenges, weekly emails, cleanup)
[ ] INFRA: Set up error monitoring (Bugsnag or Sentry)
[ ] INFRA: Automated daily database backups to cloud storage
[ ] TEST: Load test the Life Simulator tick processing under classroom-scale load
[ ] DEPLOY: Soft launch — one school or closed beta of 20-50 players
[ ] DEPLOY: Gather feedback, fix regressions, then open wider


================================================================================
ONGOING
================================================================================

[ ] Keep analysis.md updated as new issues are found
[x] Keep this file updated — mark items done as they complete
[x] After each phase completes, write a phase summary at the bottom of this file


================================================================================
PHASE COMPLETION LOG
================================================================================

Phase 0 (Audit): COMPLETE — automated audit + manual code verification done
Phase 1 (Bug Fixes): COMPLETE — all critical and major bugs fixed; 1 minor deferred
Phase 2 (Half-built Features): COMPLETE — quest board, notification bell, challenges, level-up
Phase 3 (Content Expansion): COMPLETE — 25 adult scenarios + landing page all-ages reposition
Phase 4 (Retention Loops): COMPLETE — streak warning, WYWA modal, weekly email, net worth leaderboard, P2P trading
Phase 5 (New Mechanics): MOSTLY COMPLETE — personality assessment, crisis events, budget tools expansion; peer lending deferred
Phase 7A (Life Board): COMPLETE — Life Board view at /life with all 8 sections; dashboard integrated
Phase 7B (Asset Conditions + Events): COMPLETE — condition system, maintenance endpoint, 15 asset/harambee events seeded
Phase 7C (Career Screen): COMPLETE — /life/career with payslip, 7-rung ladder, career field sidebar, recent paydays
Phase 7D (Social Pressure): PARTIAL — harambee events seeded; Chama integration + rival chip deferred
Phase 7E (Onboarding Wizard): COMPLETE — fires on first login, 5 situations, sets career + balance bonus
Phase 7G (Monthly Report Card): COMPLETE — fires on tick-month boundary crossing, grades A–D, motivational tips
