<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\GameNotification;
use App\Models\PlayerAsset;
use App\Models\PlayerBill;
use App\Models\PlayerCityJob;
use App\Models\PlayerLifeEvent;
use App\Services\CareerService;
use App\Services\CrisisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LifeController extends Controller
{
    /** Total uncollected pay across the player's Pesa City jobs and gigs. */
    private function pendingPayFor($user, $progress): int
    {
        if (!Schema::hasTable('player_city_jobs') || !Schema::hasColumn('player_city_jobs', 'pending_salary')) {
            return 0;
        }

        return (int) PlayerCityJob::where('user_id', $user->id)->sum('pending_salary');
    }

    /**
     * Report to Work — banks every pending paycheck (city jobs, completed
     * freelance gigs, legacy career income). Mood and active crisis salary
     * cuts apply at collection time.
     */
    public function workCheckin(): JsonResponse
    {
        $user = auth()->user();

        // Settle the clock first so any month that just completed is pending
        app(\App\Services\LifeSimulator::class)->processLogin($user);
        $progress = $user->getOrCreateProgress()->refresh();

        $moodPenalty  = ($progress->mood ?? 70) < 40;
        $crisisCutPct = app(CrisisService::class)->activeSalaryCutPercent();
        $applyMods    = function (int $gross) use ($moodPenalty, $crisisCutPct): int {
            $net = $gross;
            if ($moodPenalty)       $net = (int) round($net * 0.9);
            if ($crisisCutPct > 0)  $net = (int) round($net * (1 - min(90, $crisisCutPct) / 100));
            return $net;
        };

        $total = 0;
        $items = [];

        if (Schema::hasTable('player_city_jobs') && Schema::hasColumn('player_city_jobs', 'pending_salary')) {
            $graceTracking = Schema::hasColumn('player_city_jobs', 'missed_paydays');

            $rows = PlayerCityJob::where('user_id', $user->id)
                ->where('pending_salary', '>', 0)
                ->with('job:id,title,employer_name,employer_logo')
                ->get();

            foreach ($rows as $pj) {
                $net    = $applyMods((int) $pj->pending_salary);
                $total += $net;
                $items[] = [
                    'icon'     => $pj->job?->employer_logo ?? '💼',
                    'label'    => ($pj->job?->title ?? 'Job') . ' · ' . ($pj->job?->employer_name ?? ''),
                    'type'     => $pj->employment_type,
                    'amount'   => $net,
                ];
                $pj->pending_salary = 0;
                if ($graceTracking) {
                    // Showing up wipes the attendance slate clean
                    $pj->missed_paydays         = 0;
                    $pj->removal_warned_at_tick = null;
                }
                $pj->save();
            }
        }

        if ($total <= 0) {
            // Nothing to collect — tell the player when the next payday lands
            $nextIn = null;
            if (Schema::hasTable('player_city_jobs') && Schema::hasColumn('player_city_jobs', 'unpaid_ticks')) {
                $nextIn = PlayerCityJob::where('user_id', $user->id)
                    ->where('status', 'employed')
                    ->get()
                    ->map(fn ($pj) => ($pj->employment_type === 'freelance' && $pj->gig_ends_tick)
                        ? max(0, $pj->gig_ends_tick - ($progress->tick_count ?? 0))
                        : 30 - ((int) $pj->unpaid_ticks % 30))
                    ->min();
            }
            $progress->save();
            return response()->json([
                'paid'    => 0,
                'message' => $nextIn !== null
                    ? "You reported to work — nothing to collect yet. Next payday in ~{$nextIn} game day(s)."
                    : 'You reported to work — no pay is due yet. Get a job at the Opportunity Hub first!',
            ]);
        }

        $progress->balance += $total;
        $progress->recalculateNetWorth();
        $progress->save();

        $notes = [];
        if ($moodPenalty)      $notes[] = 'low mood −10%';
        if ($crisisCutPct > 0) $notes[] = "crisis cut −{$crisisCutPct}%";

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'salary',
            'title'   => '💼 Payday! You reported to work',
            'body'    => 'Ksh ' . number_format($total) . ' collected' . ($notes ? ' (' . implode(', ', $notes) . ')' : '') . '.',
            'icon'    => '💼',
            'data'    => ['amount' => $total, 'items' => $items],
        ]);

        return response()->json([
            'paid'         => $total,
            'items'        => $items,
            'notes'        => $notes,
            'new_balance'  => $progress->balance,
            'message'      => 'Ksh ' . number_format($total) . ' collected. Attendance slate wiped clean — keep reporting to work to stay in your employer\'s good books!',
        ]);
    }

    public function board()
    {
        $user = auth()->user();

        // Advance the game clock first — bills settle and tick_count updates, so
        // every "due in N game days" below reflects the admin clock setting live.
        app(\App\Services\LifeSimulator::class)->processLogin($user);

        $progress = $user->getOrCreateProgress()->refresh();

        $playerAssets = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('asset')
            ->orderByDesc('current_value')
            ->get();

        $allBills = PlayerBill::where('user_id', $user->id)
            ->whereIn('status', ['active', 'overdue'])
            ->with('bill')
            ->orderBy('next_due_tick')
            ->get();

        $currentTick = $progress->tick_count ?? 0;
        $clock       = app(\App\Services\GameClock::class);

        // Active loans — installments behave like monthly bills (auto-deducted)
        $activeLoans = \App\Models\PlayerLoan::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('loanProduct')
            ->orderBy('next_payment_tick')
            ->get()
            ->map(function ($l) use ($currentTick) {
                $l->due_in_days   = max(0, ($l->next_payment_tick ?? 0) - $currentTick);
                $l->installments  = $l->totalInstallments();
                $l->is_financing  = (bool) $l->player_asset_id;
                return $l;
            });

        // Pesa City job salaries count as income (courses → jobs → salary)
        $cityJobSalary = \App\Models\PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->with('job:id,salary_kes_month')
            ->get()
            ->sum(fn($pj) => $pj->job?->salary_kes_month ?? 0);

        // Income breakdown
        $salaryPerMonth      = max((int) ($progress->career_income_rate ?? 0), (int) $cityJobSalary);
        $assetIncomePerMonth = (int) $playerAssets->sum(fn($pa) => ($pa->asset->monthly_income ?? 0) * $pa->quantity);
        $totalIncome         = $salaryPerMonth + $assetIncomePerMonth;

        // Expense breakdown (loan installments are a real monthly outflow)
        $billsBurnPerMonth  = (int) $allBills->sum(fn($pb) => $pb->amount * (30 / max(1, $pb->frequency_ticks)));
        $assetCostsPerMonth = (int) $playerAssets->sum(fn($pa) => ($pa->asset->monthly_cost ?? 0) * $pa->quantity);
        $loanPaymentsPerMonth = (int) $activeLoans->sum(fn($l) => $l->payment_amount * (30 / max(1, $l->payment_period_ticks)));
        $totalExpenses      = $billsBurnPerMonth + $assetCostsPerMonth + $loanPaymentsPerMonth;

        $netMonthly  = $totalIncome - $totalExpenses;
        $savingsRate = $totalIncome > 0 ? (int)(($netMonthly / $totalIncome) * 100) : 0;

        $overdueBills  = $allBills->where('status', 'overdue');
        $upcomingBills = $allBills->where('status', 'active')->sortBy('next_due_tick');

        // Next affordable asset milestone
        $ageGroup = $user->age_group ?? '18-25';
        $nextAsset = Asset::active()
            ->forAgeGroup($ageGroup)
            ->where('base_price', '>', $progress->balance)
            ->orderBy('base_price')
            ->first();

        $daysToNextAsset     = null;
        $progressToNextAsset = 0;
        if ($nextAsset) {
            $progressToNextAsset = min(99, (int) (($progress->balance / max(1, $nextAsset->base_price)) * 100));
            if ($netMonthly > 0) {
                $needed = $nextAsset->base_price - $progress->balance;
                $daysToNextAsset = (int) ceil($needed / ($netMonthly / 30));
            }
        }

        // Location from net worth
        $netWorth = (int) ($progress->net_worth_cache ?? $progress->balance ?? 0);
        $location = match(true) {
            $netWorth >= 5_000_000 => 'Karen, Nairobi',
            $netWorth >= 2_000_000 => 'Westlands, Nairobi',
            $netWorth >= 1_000_000 => 'Kilimani, Nairobi',
            $netWorth >= 500_000   => 'Kasarani, Nairobi',
            $netWorth >= 200_000   => 'Embakasi, Nairobi',
            default                => 'Eastleigh, Nairobi',
        };

        $lifeFeed = GameNotification::where('user_id', $user->id)
            ->whereIn('type', ['life_sim', 'bill_paid', 'bill_missed', 'asset_income', 'salary', 'life_event'])
            ->latest()
            ->take(8)
            ->get();

        // Full statement history — all money events, paginated
        $statementFilter = request('stmt_filter', 'all');
        $stmtTypes = match($statementFilter) {
            'income'   => ['salary', 'asset_income', 'arcade_stake_won', 'arcade_forfeit_bonus'],
            'expenses' => ['bill_paid', 'bill_missed', 'arcade_stake_joined', 'arcade_stake_lost'],
            default    => ['salary', 'asset_income', 'bill_paid', 'bill_missed', 'life_sim', 'life_event',
                            'arcade_stake_joined', 'arcade_stake_won', 'arcade_stake_lost', 'arcade_forfeit_penalty', 'arcade_forfeit_bonus'],
        };
        $statement = GameNotification::where('user_id', $user->id)
            ->whereIn('type', $stmtTypes)
            ->latest()
            ->take(30)
            ->get();

        // Credit score history — populated as bills/loans/savings events fire
        $creditHistory = GameNotification::where('user_id', $user->id)
            ->where('type', 'credit_change')
            ->latest()
            ->take(8)
            ->get();

        // Recent life events (story feed)
        $lifeEvents = PlayerLifeEvent::where('user_id', $user->id)
            ->with('lifeEvent')
            ->orderByDesc('tick_triggered')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $pendingPay = $this->pendingPayFor($user, $progress);

        return view('life.board', compact(
            'user', 'progress', 'playerAssets', 'allBills', 'activeLoans',
            'overdueBills', 'upcomingBills', 'currentTick', 'clock',
            'salaryPerMonth', 'assetIncomePerMonth', 'totalIncome',
            'billsBurnPerMonth', 'assetCostsPerMonth', 'loanPaymentsPerMonth', 'totalExpenses',
            'netMonthly', 'savingsRate',
            'nextAsset', 'daysToNextAsset', 'progressToNextAsset',
            'location', 'netWorth', 'lifeFeed', 'statement', 'statementFilter',
            'creditHistory', 'lifeEvents', 'pendingPay'
        ));
    }

    // ── Career Screen ─────────────────────────────────────────────────────────

    public function career()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $career   = app(CareerService::class);

        $payslip   = $career->generatePayslip($progress);
        $fieldMeta = $progress->career_field ? $career->fieldMeta($progress->career_field) : null;

        $careerLadder = [
            ['title' => 'Intern / Trainee',   'min' => 0,       'max' => 29999,  'icon' => '🎒'],
            ['title' => 'Junior Professional', 'min' => 30000,   'max' => 59999,  'icon' => '📋'],
            ['title' => 'Mid-level',           'min' => 60000,   'max' => 119999, 'icon' => '💼'],
            ['title' => 'Senior',              'min' => 120000,  'max' => 249999, 'icon' => '📊'],
            ['title' => 'Manager',             'min' => 250000,  'max' => 499999, 'icon' => '🏢'],
            ['title' => 'Director',            'min' => 500000,  'max' => 999999, 'icon' => '👔'],
            ['title' => 'Executive / Partner', 'min' => 1000000, 'max' => null,   'icon' => '🌟'],
        ];

        // Salary comes from Pesa City jobs (courses → jobs → salary). The legacy
        // career_income_rate only applies to old accounts onboarded before that change.
        $cityJobSalary = \App\Models\PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->with('job:id,salary_kes_month')
            ->get()
            ->sum(fn($pj) => $pj->job?->salary_kes_month ?? 0);

        $salary = max((int) ($progress->career_income_rate ?? 0), (int) $cityJobSalary);

        $currentRung = 0;
        foreach ($careerLadder as $i => $rung) {
            if ($salary >= $rung['min']) $currentRung = $i;
        }

        $nextRung = $careerLadder[$currentRung + 1] ?? null;
        $salaryToNextRung = $nextRung ? max(0, $nextRung['min'] - $salary) : 0;

        $salaryHistory = GameNotification::where('user_id', $user->id)
            ->where('type', 'salary')
            ->latest()
            ->take(6)
            ->get();

        $allFields = CareerService::fieldsByKey();

        // Pesa City active jobs — annotate each with its payday countdown so
        // players can see "ready now" vs "ready in N days" at a glance.
        $activePesaJobs = \App\Models\PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->with('job')
            ->get();

        $currentTick = (int) ($progress->tick_count ?? 0);
        $activePesaJobs->each(function ($pj) use ($currentTick) {
            if (($pj->pending_salary ?? 0) > 0) {
                $pj->days_until_pay = 0; // ready to claim right now
            } elseif ($pj->employment_type === 'freelance') {
                $pj->days_until_pay = $pj->gig_ends_tick !== null ? max(0, (int) $pj->gig_ends_tick - $currentTick) : null;
            } else {
                $pj->days_until_pay = 30 - ((int) ($pj->unpaid_ticks ?? 0) % 30);
            }
        });

        $readyPesaJobs = $activePesaJobs->filter(fn ($pj) => ($pj->pending_salary ?? 0) > 0)->values();

        // Pesa City completed courses
        $completedCourses = \App\Models\PlayerCityCourse::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        $pendingPay = $this->pendingPayFor($user, $progress);

        return view('life.career', compact(
            'user', 'progress', 'payslip', 'fieldMeta', 'allFields',
            'careerLadder', 'currentRung', 'nextRung', 'salaryToNextRung',
            'salaryHistory', 'activePesaJobs', 'readyPesaJobs', 'completedCourses', 'salary', 'pendingPay'
        ));
    }

    // ── Asset Maintenance ─────────────────────────────────────────────────────

    public function maintain(PlayerAsset $playerAsset): JsonResponse
    {
        if ($playerAsset->user_id !== auth()->id()) abort(403);
        if ($playerAsset->status !== 'active')     return response()->json(['error' => 'Asset is not active.'], 422);
        if (($playerAsset->condition ?? 100) >= 95) {
            return response()->json(['error' => 'Asset is already in excellent condition.'], 422);
        }

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $cost     = $playerAsset->maintenanceCost();

        if ($progress->balance < $cost) {
            return response()->json(['error' => "Need Ksh " . number_format($cost) . " to maintain this asset."], 422);
        }

        $progress->balance -= $cost;
        $oldCondition = $playerAsset->condition ?? 100;
        $playerAsset->condition = min(100, $oldCondition + 40);
        $playerAsset->save();
        $progress->save();

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'asset_maintained',
            'title'   => "🔧 {$playerAsset->asset->name} Maintained",
            'body'    => "Condition restored to {$playerAsset->condition}%. Cost: Ksh " . number_format($cost),
            'icon'    => '🔧',
            'data'    => ['asset_id' => $playerAsset->asset_id, 'cost' => $cost, 'condition' => $playerAsset->condition],
        ]);

        return response()->json([
            'success'     => true,
            'condition'   => $playerAsset->condition,
            'new_balance' => $progress->balance,
            'cost'        => $cost,
        ]);
    }

    // ── Career Interest Quiz ──────────────────────────────────────────────────

    public function quiz()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        if ($progress->career_field !== null && $progress->career_field !== '') {
            return redirect()->route('life.board');
        }

        $quizJson  = \App\Models\Setting::get('quiz_questions', null);
        $quizQuestions = $quizJson ? (json_decode($quizJson, true) ?: null) : null;

        $career = app(CareerService::class);
        $fieldMeta = collect(CareerService::fields())->mapWithKeys(fn ($f) => [$f['key'] => $career->fieldMeta($f['key'])])->all();

        return view('life.career-quiz', compact('quizQuestions', 'fieldMeta'));
    }

    // ── Onboarding Wizard ─────────────────────────────────────────────────────

    public function onboard(Request $request): JsonResponse
    {
        $data = $request->validate([
            'field' => 'required|string|in:' . implode(',', array_column(CareerService::fields(), 'key')),
        ]);

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        if ($progress->career_field !== null && $progress->career_field !== '') {
            return response()->json(['error' => 'Career path already chosen.'], 422);
        }

        $meta = CareerService::fieldsByKey()[$data['field']];

        // The quiz only assigns a career PATH. No job, no salary, no cash bonus —
        // players must take courses at the Opportunity Hub and get hired to earn.
        $progress->career_field       = $data['field'];
        $progress->career_title       = null;
        $progress->career_income_rate = 0;
        $progress->save();

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'career_path',
            'title'   => "{$meta['icon']} Career Path Chosen: {$meta['label']}",
            'body'    => "Head to the Opportunity Hub in Pesa City — take a course on your path, then apply for a job to start earning a salary.",
            'icon'    => $meta['icon'],
            'data'    => ['field' => $data['field']],
        ]);

        return response()->json([
            'success' => true,
            'field'   => $data['field'],
            'label'   => $meta['label'],
            'next'    => 'Take a course at the Opportunity Hub to qualify for your first job.',
        ]);
    }

    public function timeline()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        $allEvents = PlayerLifeEvent::where('user_id', $user->id)
            ->with('lifeEvent')
            ->orderBy('tick_triggered')
            ->get();

        $groupedEvents = $allEvents->groupBy('chapter_at_trigger');
        $totalEvents   = $allEvents->count();

        // Journey milestones — same admin-configured source/logic as the World map's bottom bar
        $journeyMilestones = \App\Models\UserProgress::journeyMilestonesFor($user, $progress);

        return view('life.timeline', compact('user', 'progress', 'groupedEvents', 'totalEvents', 'journeyMilestones'));
    }

    /** Pay a specific overdue or upcoming bill immediately. */
    public function payBill(PlayerBill $playerBill): JsonResponse
    {
        if ($playerBill->user_id !== auth()->id()) {
            abort(403);
        }

        if ($playerBill->status === 'cancelled') {
            return response()->json(['error' => 'This bill is no longer active.'], 422);
        }

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        if ($progress->balance < $playerBill->amount) {
            return response()->json([
                'error'   => 'Insufficient balance.',
                'balance' => $progress->balance,
                'needed'  => $playerBill->amount,
            ], 422);
        }

        $bill = $playerBill->bill;

        // Clearing an overdue bill repairs less credit than paying on time
        $wasOverdue   = $playerBill->status === 'overdue';
        $creditReward = $wasOverdue
            ? max(1, (int) floor(($bill->credit_impact_pay ?? 5) / 2))
            : ($bill->credit_impact_pay ?? 5);

        $progress->balance -= $playerBill->amount;
        $progress->adjustCreditScoreWithLog(
            $creditReward,
            $wasOverdue ? "Overdue bill cleared: {$bill->name}" : "Bill paid on time: {$bill->name}",
            ['kind' => 'bill_paid', 'bill_id' => $bill->id]
        );
        $progress->recalculateNetWorth();
        $progress->save();

        // The billing CYCLE stays anchored to the original due date — paying
        // early doesn't shorten it and paying late doesn't extend it:
        //  · on-time/early pay: next due = current due + frequency (pay 5 days
        //    early → next bill lands in 35 days)
        //  · overdue pay: settleBills already advanced next_due_tick to the
        //    next cycle when it went overdue — leave it (paid 5 days late →
        //    next bill lands in 25 days)
        $nextDue = $wasOverdue
            ? $playerBill->next_due_tick
            : $playerBill->next_due_tick + $playerBill->frequency_ticks;

        $playerBill->update([
            'status'            => 'active',
            'missed_count'      => 0,
            'last_paid_tick'    => $progress->tick_count,
            'next_due_tick'     => max($nextDue, $progress->tick_count + 1),
            'overdue_since_tick'=> null,
        ]);

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'bill_paid',
            'title'   => "{$bill->icon} {$bill->name} Paid",
            'body'    => "Ksh " . number_format($playerBill->amount) . " deducted. Credit score: +{$creditReward}",
            'icon'    => $bill->icon,
            'data'    => ['bill_id' => $bill->id, 'amount' => $playerBill->amount],
        ]);

        return response()->json([
            'success'      => true,
            'new_balance'  => $progress->balance,
            'credit_score' => $progress->credit_score,
            'credit_label' => $progress->creditScoreLabel(),
        ]);
    }
}
