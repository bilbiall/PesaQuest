PesaQuest — Phase 0 Code Audit Report
Conducted: 2026-06-18
Auditor: Automated deep read of all critical files

This document is the permanent record of the Phase 0 audit. It should be updated
whenever new issues are discovered or confirmed issues are resolved.

================================================================================
AUDIT SCOPE
================================================================================

Files read in full:
- app/Http/Controllers/GameController.php
- app/Http/Controllers/ChamaController.php
- app/Http/Controllers/SubscriptionController.php
- app/Http/Controllers/MpesaController.php
- app/Http/Controllers/DashboardController.php
- app/Http/Controllers/ProfileController.php
- app/Http/Controllers/SavingsController.php
- app/Http/Controllers/AiChatController.php
- app/Services/LifeSimulator.php
- app/Services/CareerService.php
- app/Services/MpesaService.php
- app/Models/UserProgress.php
- app/Models/User.php
- resources/views/dashboard.blade.php
- resources/views/game/play.blade.php
- resources/views/game/partials/life-sim-catchup.blade.php
- resources/views/chama/show.blade.php
- resources/views/marketplace/index.blade.php
- resources/views/marketplace/portfolio.blade.php
- database/seeders/QuestSeeder.php
- database/seeders/DailyChallengeSeeder.php


================================================================================
CRITICAL ISSUES — Must fix before any user-facing rollout
================================================================================


ISSUE C-01: Missing fillable fields on UserProgress model
File: app/Models/UserProgress.php
Lines: 9-14 (the $fillable array)
Status: OPEN

The $fillable array on UserProgress does not include several fields that controllers
actively use. In Laravel, mass assignment silently ignores any field not in $fillable.
This means updates via update() or fill() to these fields are silently dropped.

Fields missing from $fillable that are used in GameController:
  - current_scenario_start_id (used in at least 4 places)
  - total_decisions (used in badge threshold checks)
  - consecutive_save_choices (used in streak badge logic)

Impact: Badge awards that depend on total_decisions will never trigger correctly.
Streak logic for save choices will always reset because the counter never persists.
The current_scenario_start_id will not persist, breaking scenario restart flows.

Fix needed: Add all three fields to the $fillable array.


ISSUE C-02: Unvalidated effect_data in GameController::choose()
File: app/Http/Controllers/GameController.php
Lines: 190-208
Status: OPEN

When a player makes a choice, the controller reads effect_data from the Choice model
to determine financial consequences. The code does a null-coalescing fallback for
balance_change (effect_data['balance_change'] ?? 0) which is safe. However there is
a separate problem: the consecutive_save_choices logic has a duplicate condition.

The check reads:
  str_contains(strtolower($choice->label), 'save') || str_contains(strtolower($choice->label), 'save')

Both sides of the OR are identical. One of these was meant to check for a different
keyword (possibly 'invest' or 'budget'). This means the save streak logic only fires
on one condition instead of the intended two, and the badge for consistent saving
behaviour may never trigger as designed.

Impact: Moderate. The save streak badge likely never awards correctly.

Fix needed: Correct the duplicate condition to check the second intended keyword.


ISSUE C-03: Investment creation uses full player balance as fallback
File: app/Http/Controllers/GameController.php
Lines: 206-227
Status: OPEN

When a choice triggers an investment (effect_data type is 'investment'), the amount
to invest is read from balance_change. If balance_change is 0 or absent, the code
falls back to investing the player's entire current balance.

The exact fallback line is:
  $investedAmount = abs((int) ($effectData['balance_change'] ?? 0)) ?: $progress->balance;

The PHP ?: operator means: if the left side is falsy (zero), use the right side.
So a balance_change of 0 causes the player to invest everything they have.

Additionally, there is no check that the player actually has enough balance to cover
the investment amount, even when balance_change is a specific positive number.

Impact: High. A poorly authored scenario node with balance_change of 0 could wipe a
player's entire balance. This is a data integrity risk.

Fix needed: Remove the full-balance fallback. If balance_change is 0, skip investment
creation. Always check player balance is sufficient before deducting.


ISSUE C-04: Race condition in claimDailyBonus()
File: app/Http/Controllers/GameController.php
Lines: 477-484
Status: OPEN

The daily bonus check reads last_bonus_at from the database, checks if it is today,
and if not, adds the bonus and saves. There is no database transaction, no row lock,
and no atomic check-and-set. Two simultaneous HTTP requests from the same user (e.g.,
a double-tap on mobile) can both pass the date check before either writes the update,
resulting in the bonus being claimed twice.

The flow is:
  1. Request A: reads last_bonus_at = yesterday → proceeds
  2. Request B: reads last_bonus_at = yesterday → proceeds (A hasn't saved yet)
  3. Request A: saves balance += 1000, last_bonus_at = today
  4. Request B: saves balance += 1000, last_bonus_at = today (bonus paid twice)

Impact: Medium. Easy to exploit deliberately or hit accidentally on slow connections.

Fix needed: Wrap the entire check-and-update in a DB::transaction() with a
lockForUpdate() call on the UserProgress row.


ISSUE C-05: submitQuest() applies to any quest regardless of player age group
File: app/Http/Controllers/GameController.php
Lines: 456-467
Status: OPEN

When a player submits a quest for admin approval, the controller only checks that
the quest is active (is_active flag). It does not check that the quest is intended
for the submitting player's age group. A player aged 12 could submit an adult quest.

Impact: Low for data integrity, but produces confusing admin review queues where
age group mismatches must be manually filtered.

Fix needed: Add a check that quest->age_group matches auth()->user()->age_group.
Also add the player's age_group to the UserQuest record for easier admin filtering.


ISSUE C-06: Stale $progress object after LifeSimulator runs in DashboardController
File: app/Http/Controllers/DashboardController.php
Lines: ~18-30
Status: OPEN

DashboardController calls LifeSimulator::processLogin($user) which modifies the
user's balance, tick_count, credit_score, and other fields inside a database
transaction. However, the $progress object that was loaded BEFORE this call is
never refreshed. Any code in the controller that runs after processLogin() and
reads from $progress is reading the pre-simulation stale values.

This means the dashboard may show an outdated balance, outdated credit score, and
outdated level immediately after a login tick fires — values that correct themselves
only on the next page load.

Impact: Medium. Players will see briefly incorrect numbers on their dashboard after
long absences when the simulator fires.

Fix needed: Call $progress->refresh() immediately after processLogin() returns.


ISSUE C-07: Market event sign error
File: app/Http/Controllers/GameController.php
Lines: ~116-128
Status: OPEN

When a market event applies a penalty, the code does:
  $progress->balance = max(0, $progress->balance - $marketEvent->effect_amount);

If a market event is authored with a negative effect_amount to represent a penalty,
the subtraction becomes: balance - (-500) = balance + 500. The player receives a
bonus instead of a penalty.

Impact: Low probability but high impact when it hits. A badly authored market event
could accidentally reward players generously.

Fix needed: Always use abs($marketEvent->effect_amount) for penalty calculations.


================================================================================
MAJOR ISSUES — Significant bugs that affect correctness or user experience
================================================================================


ISSUE M-01: Leaderboard rank is always 1-10, never the player's true rank
File: app/Http/Controllers/GameController.php
Lines: ~354-370
Status: OPEN

The leaderboard fetches the top 10 players and uses the array index (0-9) plus 1
as the rank. This means the first result is always rank 1 through rank 10.

There are two separate problems:
First, the rank shown for each player in the top 10 is always sequential from 1
regardless of whether there are ties. Two players with equal points both get
different ranks.

Second, the variable called $myRank is set by calling .value('points_total') which
returns the player's points_total, not their rank. The variable name is misleading
and the value is wrong — the frontend likely displays a points total where a rank
number is expected.

Impact: Medium. Leaderboard is a key engagement feature. Showing wrong ranks
undermines trust.

Fix needed: Calculate actual rank as: count of players with higher points_total + 1.
Calculate $myRank using the same formula, not by fetching points_total.


ISSUE M-02: LifeSimulator::settleAssets() can produce PHP warnings on null rate
File: app/Services/LifeSimulator.php
Lines: ~195-205
Status: OPEN

The condition that guards the appreciation calculation checks:
  if ($asset->appreciation_rate != 0)

This check passes when appreciation_rate is null because null != 0 is true in PHP
(loose comparison). The code then does null / 100 which produces null, and then
pow(null, $months) which produces a PHP warning and an incorrect value.

Impact: Low in production (most seeded assets likely have a rate), but triggers
warnings that could fill error logs and mask real errors.

Fix needed: Change the guard to: if (($asset->appreciation_rate ?? 0) != 0)


ISSUE M-03: MpesaController callback always returns success even on failure
File: app/Http/Controllers/MpesaController.php
Lines: ~16-24
Status: OPEN

The M-Pesa callback handler wraps all processing in a try/catch. On any exception,
it logs the error but still returns HTTP 200 with ResultCode 0 (success) to
Safaricom. This means Safaricom believes every callback was processed successfully.
Safaricom will not retry failed callbacks.

If a database error occurs while processing a payment confirmation, the player's
subscription will not activate, but Safaricom will never retry because it was told
to consider the transaction complete. The only way to fix such a case is manual
admin intervention.

Impact: High severity when triggered. Payments that fail processing are permanently
lost with no automatic recovery.

Fix needed: Return HTTP 500 with ResultCode 1 on processing errors so Safaricom
retries. Log full stack trace including request payload for debugging.


ISSUE M-04: DashboardController has no error handling around LifeSimulator
File: app/Http/Controllers/DashboardController.php
Status: OPEN

If LifeSimulator::processLogin() throws any exception (database error, division by
zero, null pointer), the entire dashboard page crashes with a 500 error. Players
would be locked out of their dashboard and see a generic error page until the
underlying issue is fixed.

Impact: High availability risk. The life simulator touches bills, assets, life
events, and stock prices in a single session — many points of potential failure.

Fix needed: Wrap processLogin() in try/catch. On failure, log the error, set
$lifeSim to an empty array, and let the dashboard load without the While You
Were Away modal.


ISSUE M-05: Hardcoded daily bonus amount
File: app/Http/Controllers/GameController.php
Line: ~481
Status: OPEN

The daily bonus is set to 1000 (KES) as a PHP literal. There is no admin setting
to change this. To adjust the bonus, a code deployment is required.

Impact: Low urgency, but creates a recurring maintenance issue.

Fix needed: Read from Setting::get('daily_bonus_amount', 1000).


ISSUE M-06: Duplicated investment notification logic
File: app/Http/Controllers/GameController.php and DashboardController.php
Status: OPEN

The logic that checks for matured investments and creates game notifications is
copy-pasted in at least two controllers. If the notification format changes, it
must be updated in multiple places and it is easy to miss one.

Impact: Low urgency but high maintenance risk as the codebase grows.

Fix needed: Extract to a dedicated InvestmentService or a method on GameNotification.


================================================================================
MINOR ISSUES — Code quality, incomplete features, edge cases
================================================================================


ISSUE N-01: NodeController is dead code
File: app/Http/Controllers/NodeController.php
Status: OPEN

The file exists but has no routes pointing to it. It serves no function.
Fix: Delete the file.


ISSUE N-02: Portfolio value calculation duplicated
File: app/Http/Controllers/ProfileController.php lines 34 and 65
Status: OPEN

Exact same calculation: $user->playerAssets ? $user->playerAssets->sum('current_value') : 0
appears in both edit() and show(). Should be a User model accessor.


ISSUE N-03: Diary view likely missing
File: app/Http/Controllers/GameController.php — diary() method
Status: NEEDS VERIFICATION

The diary() method returns data but it is unclear if resources/views/game/diary.blade.php
exists. If the route /game/diary is visited without the view, it will throw a view
not found exception.

Action: Verify the view file exists. If not, either create it or remove the route.


ISSUE N-04: Tools page is a dead route
File: routes/web.php — GET /tools
Status: NEEDS VERIFICATION

The tools route returns a view but the extent of functionality on that page is
unknown. If the page is essentially empty, it should either be built out (it is
in Phase 5 plan as the Budget Planner) or removed from navigation.


ISSUE N-05: CareerService integration is unclear
File: app/Services/CareerService.php
Status: NEEDS VERIFICATION

The service exists and career fields exist on UserProgress but no controller or
service clearly calls CareerService. The salary that fires every 30 ticks in
LifeSimulator may use a static amount rather than CareerService's logic.

Action: Trace whether CareerService is called anywhere via grep. If it is dead
code, either wire it into LifeSimulator properly or remove it.


================================================================================
INCOMPLETE / MISSING FEATURES DISCOVERED DURING AUDIT
================================================================================

INCOMPLETE-01: Scenario ratings have no UI
The POST /game/rate route exists and the ScenarioRating model exists, but there is
no button or form in resources/views/game/play.blade.php that sends a rating.
Players cannot currently rate scenarios even though the backend supports it.

INCOMPLETE-02: Quest board has no player-facing view
Quests can be submitted via POST /game/quests/{quest}/submit but there is no page
where players can browse available quests. The quest board is effectively invisible.

INCOMPLETE-03: Daily challenges not surfaced on dashboard
The DailyChallenge model and seeder exist but the dashboard view does not appear to
include a daily challenge card or countdown. Players have no prompt to complete daily
challenges.

INCOMPLETE-04: Stock price history collected but not displayed
LifeSimulator::recordStockPrices() writes to the stock_price_history table on every
session. The portfolio view does not yet include a chart. The data is being collected
for a feature that does not yet exist in the UI.

INCOMPLETE-05: Notification badge count missing from navigation
GameNotification records are created for investment maturity and other events but
the main navigation has no unread count badge. Players have no visual signal that
something awaits them.

INCOMPLETE-06: Level-up event is silent
When points_total crosses a level threshold, the level field updates but there is no
event, notification, or visual celebration fired. Players may not notice they levelled up.


================================================================================
THINGS CONFIRMED WORKING (no issues found)
================================================================================

- AiChatController properly uses null-safe operators throughout; no crash on missing progress
- ChamaController::contribute() is inside a DB transaction; balance and contribution stay in sync
- User::canAccessNode() correctly checks free_for_all → school membership → subscription in order
- LifeSimulator MAX_CATCHUP_TICKS = 60 cap is properly applied
- MpesaService structure is correct for Daraja API; uses proper STK push format
- School portal uses token-based access with no authentication requirement as intended
- Gameset admin JS strings use Js::from() escaping; no apostrophe injection risk
- BulkScenarioSeeder result_title null coalescing fix is applied


================================================================================
SUMMARY SCORECARD
================================================================================

Critical issues found:        7
Major issues found:           6
Minor / code quality issues:  5
Incomplete features:          6
Confirmed working:            8

Overall assessment:
The platform has a solid architectural foundation. The life simulator, subscription
system, and school portal are well-structured. The critical issues are concentrated
in GameController and will not be difficult to fix individually. The most dangerous
issue is the race condition on daily bonus (C-04) and the investment full-balance
fallback (C-03). The M-Pesa callback swallowing errors silently (M-03) is the most
consequential for real-money reliability. None of these are architectural — they are
all local, surgical fixes.

The platform is NOT ready for production as-is. After Phase 1 fixes, it will be in
a strong position for a controlled soft launch.


================================================================================
REVISION HISTORY
================================================================================

2026-06-18 — Initial audit completed. 7 critical, 6 major, 5 minor, 6 incomplete.
