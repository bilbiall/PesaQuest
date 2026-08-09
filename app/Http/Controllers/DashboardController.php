<?php

namespace App\Http\Controllers;

use App\Models\DailyChallenge;
use App\Models\GameNotification;
use App\Models\Investment;
use App\Models\Node;
use App\Models\PlayerAsset;
use App\Models\PlayerBill;
use App\Models\PlayerDeal;
use App\Models\PlayerLifeEvent;
use App\Models\SpinResult;
use App\Models\UserDailyChallenge;
use App\Models\UserProgress;
use App\Models\UserQuest;
use App\Services\LifeSimulator;
use App\Services\PlanGate;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $user        = auth()->user();
        $progress    = $user->getOrCreateProgress();
        $levelBefore = $progress->level ?? 1;
        $tickBefore  = $progress->tick_count ?? 0;

        // Run life simulator catch-up ticks and store summary for the view
        try {
            $lifeSim = app(LifeSimulator::class)->processLogin($user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('LifeSimulator::processLogin failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            $lifeSim = [];
        }
        $progress->refresh();
        $leveledUp = ($progress->level ?? 1) > $levelBefore;

        // Quest Gate: banked XP waiting behind unfinished quests — surfaced here
        // (not just the world-map quest panel) since the Level number itself
        // lives on this page and "my level isn't moving" is confusing without it.
        $questGate = \App\Services\QuestGate::status($progress);

        // "Last Played" + streak must reflect every real visit, not only visits
        // where XP happens to be earned (addPoints() is the only other writer
        // of last_played_at, which made genuinely-active players look stale).
        $progress->last_played_at = now();
        $progress->save();
        // Capture "at risk" BEFORE recording — recordActivity() moves
        // last_activity_date to today, which made the check below always false.
        $streakModel  = $user->streak()->firstOrCreate([]);
        $streakAtRisk = ($streakModel->current_streak ?? 0) > 1
            && $streakModel->last_activity_date?->toDateString() === now('Africa/Nairobi')->subDay()->toDateString();
        $streakModel->recordActivity();

        // Inject any unread "job hired" notifications into the WYWA events so they surface prominently
        $hiredNotifs = \App\Models\GameNotification::where('user_id', $user->id)
            ->where('type', 'job_hired')->where('is_read', false)->get();
        if ($hiredNotifs->isNotEmpty()) {
            $hiredEvents = $hiredNotifs->map(fn ($n) => [
                'icon'         => $n->icon ?? '💼',
                'type'         => 'job_hired',
                'text'         => $n->title,
                'sub'          => $n->body,
                'delta'        => 0,
                'is_milestone' => true,
                'xp'           => $n->data['xp'] ?? 0,
            ])->toArray();
            if (!is_array($lifeSim)) $lifeSim = [];
            $lifeSim['events'] = array_merge($hiredEvents, $lifeSim['events'] ?? []);
            $hiredNotifs->each(fn ($n) => $n->update(['is_read' => true]));
        }

        // Monthly Report Card: fires when a 30-tick game-month boundary was crossed
        $tickAfter      = $progress->tick_count ?? 0;
        $monthsBefore   = (int) floor($tickBefore / 30);
        $monthsAfter    = (int) floor($tickAfter / 30);
        $monthsElapsed  = $monthsAfter - $monthsBefore;
        $monthlyReport  = null;

        // Below this much total tracked activity (income + expenses), a percentage-based
        // grade is meaningless — a single small life-event windfall against a small
        // bill could mathematically read "30%+ savings, Grade A" despite the player
        // having no job and no real savings. Skip grading rather than mislead.
        $minReportActivityKes = 1000;

        if ($monthsElapsed > 0 && !empty($lifeSim['events'])) {
            $evs        = collect($lifeSim['events'] ?? []);
            $incomeRows = $evs->filter(fn($e) => ($e['delta'] ?? 0) > 0)->values();
            $expenseRows= $evs->filter(fn($e) => ($e['delta'] ?? 0) < 0)->values();
            $totalIn    = (int) $incomeRows->sum('delta');
            $totalOut   = (int) abs($expenseRows->sum('delta'));
            $net        = $totalIn - $totalOut;
            $hasGrade   = ($totalIn + $totalOut) >= $minReportActivityKes;
            $savePct    = ($hasGrade && $totalIn > 0) ? (int)(($net / $totalIn) * 100) : 0;
            $monthlyReport = [
                'months'       => $monthsElapsed,
                'total_in'     => $totalIn,
                'total_out'    => $totalOut,
                'net'          => $net,
                'savings_rate' => $savePct,
                'has_grade'    => $hasGrade,
                'grade'        => !$hasGrade ? null : match(true) {
                    $savePct >= 30 => 'A',
                    $savePct >= 15 => 'B',
                    $savePct >= 0  => 'C',
                    default        => 'D',
                },
                // Line-item breakdown for the "Show details" dropdown — e.g. which
                // asset paid income, which loan installment/bill drove an expense.
                'income_items'  => $incomeRows->map(fn($e) => [
                    'icon' => $e['icon'] ?? '💰', 'text' => $e['text'] ?? '', 'sub' => $e['sub'] ?? '', 'amount' => (int) ($e['delta'] ?? 0),
                ])->values()->all(),
                'expense_items' => $expenseRows->map(fn($e) => [
                    'icon' => $e['icon'] ?? '💸', 'text' => $e['text'] ?? '', 'sub' => $e['sub'] ?? '', 'amount' => (int) abs($e['delta'] ?? 0),
                ])->values()->all(),
            ];
        }

        // Onboarding: show wizard if player has no career set yet
        $needsOnboarding = ($progress->career_field === null || $progress->career_field === '')
            && (($progress->career_income_rate ?? 0) === 0);

        // First-time tutorial wizard — shown AFTER the career-quiz gate above is
        // cleared (a player picks a career field), not at the same time. Also
        // rendered on the World page (see WorldController) since the career
        // quiz redirects there, not back to the Dashboard, after completion.
        $showOnboardingWizard = \App\Services\OnboardingService::shouldShow($user, $needsOnboarding);
        $onboardingSteps      = \App\Services\OnboardingService::steps();
        $streak = $streakModel->fresh() ?? $streakModel; // re-read after recordActivity above
        $badges   = $user->badges()->orderByPivot('earned_at', 'desc')->get();

        // Weekly ranking
        $totalUsers = UserProgress::count();
        $rank       = UserProgress::where('points_total', '>', $progress->points_total)->count();
        $percentile = $totalUsers > 1 ? (int) (100 - ($rank / ($totalUsers - 1) * 100)) : 100;

        // Active investments — split matured vs still-pending
        $allPendingInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('mature_at')
            ->get();

        $maturedInvestments = $allPendingInvestments->filter(fn($i) => $i->mature_at <= now());
        $activeInvestments  = $allPendingInvestments;

        // Displayed investment count — Phase 17 moved investing to PlayerDeal (Equity Square)
        // and investment-category PlayerAssets, so count those instead of legacy Investments.
        $investmentCount = 0;
        if (Schema::hasTable('player_deals')) {
            $investmentCount += PlayerDeal::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
        }
        $investmentCount += PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('asset', fn($q) => $q->where('category', 'investment'))
            ->count();

        // Create matured-investment notifications once per investment (batch check to avoid N+1)
        if ($maturedInvestments->isNotEmpty()) {
            $alreadyNotifiedIds = GameNotification::where('user_id', $user->id)
                ->where('type', 'investment_ready')
                ->get(['data'])
                ->pluck('data.investment_id')
                ->filter()
                ->map(fn($id) => (int) $id)
                ->toArray();

            foreach ($maturedInvestments as $inv) {
                if (!in_array($inv->id, $alreadyNotifiedIds)) {
                    $expected = round((float)$inv->amount * (1 + (float)$inv->return_rate / 100), 2);
                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'investment_ready',
                        'title'   => '💰 Investment Matured!',
                        'body'    => "Your {$inv->label} investment is ready! Claim Ksh " . number_format($expected) . " now.",
                        'icon'    => '💰',
                        'data'    => ['investment_id' => $inv->id, 'returns' => $expected],
                    ]);
                }
            }
        }

        // Recent notifications
        $recentNotifications = GameNotification::where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get();

        $unreadCount = GameNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Daily challenges widget
        $ageGroup = $user->age_group ?? '18-25';
        $today    = now()->toDateString();
        $challenges = DailyChallenge::activeToday($ageGroup)->get()->map(function ($c) use ($user, $today) {
            $udc = UserDailyChallenge::firstOrCreate(
                ['user_id' => $user->id, 'challenge_id' => $c->id, 'date' => $today],
                ['progress' => 0]
            );
            $c->user_progress  = $udc->progress;
            $c->user_completed = $udc->isCompleted();
            $c->user_claimed   = $udc->isClaimed();
            return $c;
        });

        // Daily bonus eligibility
        $canClaimBonus = !$progress->last_bonus_at
            || !$progress->last_bonus_at->copy()->tz('Africa/Nairobi')->isSameDay(now('Africa/Nairobi'));

        // Journey progress
        $visitedStartIds    = collect($progress->path_history ?? [])->pluck('node_id')->unique();
        $totalStartNodes    = Node::where('age_group', $user->age_group)->where('is_start', true)->count();
        $completedScenarios = Node::where('age_group', $user->age_group)
            ->where('is_start', true)
            ->whereIn('id', $visitedStartIds)
            ->count();

        // Bills for the Bills & Credit panel
        $allPlayerBills = PlayerBill::where('user_id', $user->id)
            ->whereIn('status', ['active', 'overdue'])
            ->with('bill')
            ->get();

        $overdueBills   = $allPlayerBills->where('status', 'overdue')->sortBy('next_due_tick');
        $upcomingBills  = $allPlayerBills->where('status', 'active')
            ->sortBy('next_due_tick')
            ->take(5);

        $monthlyBurn = $allPlayerBills->sum(fn($pb) => $pb->amount * (30 / max(1, $pb->frequency_ticks)));

        // Days till next salary (salary fires every 30 ticks)
        $currentTick     = $progress->tick_count ?? 0;
        $ticksInCycle    = $currentTick % 30;
        $daysTillSalary  = $ticksInCycle === 0 ? 30 : (30 - $ticksInCycle);

        // Real Pesa City job salary ONLY (full-time/part-time monthly pay).
        // career_income_rate is a legacy field from the old scenario/node
        // career-unlock system — showing it here misleadingly implied a
        // "job" even for players with zero real Pesa City employment.
        $salaryAmount = \App\Models\PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->whereIn('employment_type', ['full_time', 'part_time'])
            ->with('job:id,salary_kes_month')
            ->get()
            ->sum(fn ($pj) => $pj->job ? $pj->effectiveSalary() : 0);

        // Bills due within 7 game days
        $billsDueSoon = $allPlayerBills->filter(
            fn($pb) => $pb->next_due_tick <= ($currentTick + 7) && $pb->status !== 'overdue'
        )->count();

        // Move these queries here so the view stays free of DB calls
        $canSpin = !SpinResult::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->exists();

        // Money Toolkit (Bajeti/Lengo/Matumizi/Ukuaji/Mkopo/Faida) is a premium feature
        $gate                = app(PlanGate::class);
        $smartToolsUnlocked  = $gate->limit($user, 'smart_tools_access') > 0;

        $recentLifeEvents = PlayerLifeEvent::where('user_id', $user->id)
            ->with('lifeEvent')
            ->orderByDesc('tick_triggered')
            ->take(4)
            ->get();

        // "This Week" cashflow strip — the next 7 game days of money events,
        // derived by the calendar service (no new tables)
        $weekCal = null;
        try {
            $cal      = app(\App\Services\GameCalendarService::class)->upcoming($user);
            $weekDays = array_slice($cal['days'], 0, 7);
            $in = 0; $out = 0;
            foreach ($weekDays as $d) {
                foreach ($d['events'] as $ev) {
                    $amt = (int) ($ev['amount'] ?? 0);
                    if ($amt > 0) $in += $amt; elseif ($amt < 0) $out += -$amt;
                }
            }
            $weekCal = ['days' => $weekDays, 'in' => $in, 'out' => $out, 'net' => $in - $out];
        } catch (\Throwable $e) { /* pre-migration — hide the strip */ }

        // Personal NPC contracts — self-generated from player state (settled + topped up here)
        $contracts = collect();
        try {
            $contracts = app(\App\Services\ContractService::class)->refresh($user);
        } catch (\Throwable $e) { /* pre-migration — no widget */ }

        // Challenges — recompute progress on every dashboard visit so duels can
        // settle the instant someone crosses the goal, not just when they check.
        try {
            app(\App\Services\ChallengeService::class)->refresh($user);
        } catch (\Throwable $e) { /* pre-migration — safe to skip */ }

        // Active quests (started, not yet completed/approved) — used for Today's Goals
        $questGoals = UserQuest::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->with('quest')
            ->latest()
            ->take(4)
            ->get();
        $activeQuest = $questGoals->first();

        return view('dashboard', compact(
            'user', 'progress', 'streak', 'streakAtRisk', 'badges', 'percentile',
            'activeInvestments', 'maturedInvestments', 'investmentCount', 'recentNotifications', 'unreadCount',
            'canClaimBonus', 'completedScenarios', 'totalStartNodes', 'lifeSim',
            'overdueBills', 'upcomingBills', 'monthlyBurn', 'leveledUp', 'challenges',
            'needsOnboarding', 'monthlyReport', 'showOnboardingWizard', 'onboardingSteps',
            'daysTillSalary', 'salaryAmount', 'billsDueSoon',
            'canSpin', 'recentLifeEvents',
            'questGoals', 'activeQuest', 'smartToolsUnlocked', 'weekCal', 'contracts', 'currentTick', 'questGate'
        ));
    }

    /** Dismiss the first-time onboarding wizard (Next on the last step, or Close). */
    public function completeOnboarding()
    {
        $user = auth()->user();
        if (Schema::hasColumn('users', 'onboarding_completed_at') && !$user->onboarding_completed_at) {
            $user->update(['onboarding_completed_at' => now()]);
        }
        return response()->json(['success' => true]);
    }

    /** Re-arm the intro tour (Profile → "Replay intro tour"). */
    public function replayOnboarding()
    {
        $user = auth()->user();
        if (Schema::hasColumn('users', 'onboarding_completed_at')) {
            $user->update(['onboarding_completed_at' => null]);
        }
        return redirect()->route('dashboard');
    }
}
