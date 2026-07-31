<?php

namespace App\Http\Controllers;

use App\Models\RealLifeBill;
use App\Models\RealLifeBillPayment;
use App\Models\RealLifeExpense;
use App\Models\RealLifeSavingsDeposit;
use App\Models\RealLifeSavingsGoal;
use App\Models\UserBudgetRatio;
use App\Models\UserExpenseCategory;
use App\Services\PlanGate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Real-life money tools — bill reminders and savings goals for the player's
 * ACTUAL life, on real calendar dates. Deliberately isolated from every game
 * model (no balance/credit-score/tick interaction anywhere in this file).
 *
 * A lapsed subscription never hides a player's OWN data — index() (read) is
 * open to any authenticated player so they can always SEE what they saved.
 * Every WRITE action (add/edit/delete/mark paid/log deposit) stays gated
 * behind PlanGate::smart_tools_access — that's where "Premium" actually means
 * something, not in whether you can look at what you already recorded.
 */
class RealLifeToolsController extends Controller
{
    private function ensureUnlocked(): void
    {
        $user = auth()->user();
        if (app(PlanGate::class)->limit($user, 'smart_tools_access') < 1) {
            abort(response()->json(app(PlanGate::class)->deny('smart_tools_access', 0), 403));
        }
    }

    /** Everything the dashboard modal needs to render, in one call. Read-only — never gated. */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $bills = RealLifeBill::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->orderBy('next_due_date')
            ->get()
            ->map(fn ($b) => $this->billPayload($b));

        $goals = RealLifeSavingsGoal::where('user_id', $user->id)
            ->with('deposits')
            ->orderByDesc('status')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($g) => $this->goalPayload($g));

        return response()->json([
            'bills'        => $bills,
            'goals'        => $goals,
            'categories'   => UserExpenseCategory::mapForUser($user->id),
            'budget_ratio' => UserBudgetRatio::forUser($user->id)->only(['needs_pct', 'wants_pct', 'savings_pct']),
            'unlocked'     => app(PlanGate::class)->limit($user, 'smart_tools_access') > 0,
        ]);
    }

    // ── Budget ratio (Bajeti Smart) ─────────────────────────────────────────

    public function saveBudgetRatio(Request $request): JsonResponse
    {
        $this->ensureUnlocked();
        $data = $request->validate([
            'needs_pct'   => 'required|integer|min:0|max:100',
            'wants_pct'   => 'required|integer|min:0|max:100',
            'savings_pct' => 'required|integer|min:0|max:100',
        ]);

        if ($data['needs_pct'] + $data['wants_pct'] + $data['savings_pct'] !== 100) {
            return response()->json(['success' => false, 'message' => 'The three percentages must add up to 100.'], 422);
        }

        $ratio = UserBudgetRatio::forUser(auth()->id());
        $ratio->update($data);

        return response()->json(['success' => true, 'budget_ratio' => $ratio->only(['needs_pct', 'wants_pct', 'savings_pct'])]);
    }

    // ── Custom expense categories ────────────────────────────────────────────

    public function storeCategory(Request $request): JsonResponse
    {
        $this->ensureUnlocked();
        $data = $request->validate([
            'label' => 'required|string|max:60',
            'icon'  => 'nullable|string|max:10',
        ]);

        $userId = auth()->id();
        $existing = UserExpenseCategory::forUser($userId);
        if ($existing->count() >= 20) {
            return response()->json(['success' => false, 'message' => 'You can have up to 20 categories.'], 422);
        }

        $key  = \Illuminate\Support\Str::slug($data['label'], '_') ?: 'category';
        $base = $key;
        $n    = 1;
        while ($existing->firstWhere('key', $key)) {
            $key = $base . '_' . (++$n);
        }

        $category = UserExpenseCategory::create([
            'user_id'    => $userId,
            'key'        => $key,
            'label'      => $data['label'],
            'icon'       => $data['icon'] ?: '📋',
            'sort_order' => $existing->max('sort_order') + 1,
        ]);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function updateCategory(Request $request, UserExpenseCategory $category): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($category->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'label' => 'required|string|max:60',
            'icon'  => 'nullable|string|max:10',
        ]);
        $category->update(['label' => $data['label'], 'icon' => $data['icon'] ?: $category->icon]);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function destroyCategory(UserExpenseCategory $category): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($category->user_id !== auth()->id(), 403);

        // Expenses already logged under this category keep their key as plain
        // text — expensePayload() falls back gracefully if the category is gone.
        $category->delete();

        return response()->json(['success' => true]);
    }

    // ── Real-life bills ──────────────────────────────────────────────────────

    public function storeBill(Request $request): JsonResponse
    {
        $this->ensureUnlocked();
        $data = $this->validatedBill($request);
        $data['user_id'] = auth()->id();

        $bill = RealLifeBill::create($data);
        return response()->json(['success' => true, 'bill' => $this->billPayload($bill)]);
    }

    public function updateBill(Request $request, RealLifeBill $bill): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($bill->user_id !== auth()->id(), 403);

        $bill->update($this->validatedBill($request));
        return response()->json(['success' => true, 'bill' => $this->billPayload($bill->fresh())]);
    }

    public function destroyBill(RealLifeBill $bill): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($bill->user_id !== auth()->id(), 403);
        $bill->delete();
        return response()->json(['success' => true]);
    }

    /** Player confirms they paid it in real life — rolls the date forward or completes it. */
    public function markBillPaid(RealLifeBill $bill): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($bill->user_id !== auth()->id(), 403);
        $bill->advanceOrComplete();
        return response()->json(['success' => true, 'bill' => $this->billPayload($bill->fresh())]);
    }

    private function validatedBill(Request $request): array
    {
        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'icon'                => 'nullable|string|max:10',
            'category'            => 'required|string|in:' . implode(',', array_keys(RealLifeBill::CATEGORIES)),
            'amount'              => 'required|integer|min:0|max:99999999',
            'next_due_date'       => 'required|date',
            'is_recurring'        => 'boolean',
            'frequency_days'      => 'nullable|integer|min:1|max:3650',
            'reminder_lead_days'  => 'required|integer|min:0|max:30',
            'notes'               => 'nullable|string|max:500',
        ]);

        $data['icon']           = $data['icon'] ?: (RealLifeBill::CATEGORIES[$data['category']]['icon'] ?? '🧾');
        $data['is_recurring']   = $request->boolean('is_recurring');
        $data['frequency_days'] = $data['is_recurring'] ? ($data['frequency_days'] ?? 30) : null;

        return $data;
    }

    private function billPayload(RealLifeBill $b): array
    {
        $daysUntil = (int) now()->startOfDay()->diffInDays($b->next_due_date, false);
        return [
            'id'                  => $b->id,
            'name'                => $b->name,
            'icon'                => $b->icon,
            'category'            => $b->category,
            'category_label'      => RealLifeBill::CATEGORIES[$b->category]['label'] ?? ucfirst($b->category),
            'amount'              => $b->amount,
            'next_due_date'       => $b->next_due_date->toDateString(),
            'is_recurring'        => $b->is_recurring,
            'frequency_days'      => $b->frequency_days,
            'frequency_label'     => RealLifeBill::FREQUENCIES[$b->frequency_days] ?? ($b->frequency_days ? "Every {$b->frequency_days} days" : null),
            'reminder_lead_days'  => $b->reminder_lead_days,
            'status'              => $b->status,
            'notes'               => $b->notes,
            'days_until_due'      => $daysUntil,
            'is_overdue'          => $b->isOverdue(),
        ];
    }

    // ── Real-life savings goals ──────────────────────────────────────────────

    public function storeGoal(Request $request): JsonResponse
    {
        $this->ensureUnlocked();
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'icon'          => 'nullable|string|max:10',
            'target_amount' => 'required|integer|min:1|max:999999999',
            'target_date'   => 'nullable|date',
        ]);
        $data['icon']    = $data['icon'] ?: '🎯';
        $data['user_id'] = auth()->id();

        $goal = RealLifeSavingsGoal::create($data);
        return response()->json(['success' => true, 'goal' => $this->goalPayload($goal->fresh('deposits'))]);
    }

    public function updateGoal(Request $request, RealLifeSavingsGoal $goal): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($goal->user_id !== auth()->id(), 403);
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'icon'          => 'nullable|string|max:10',
            'target_amount' => 'required|integer|min:1|max:999999999',
            'target_date'   => 'nullable|date',
        ]);
        $goal->update($data);
        $goal->refreshCompletionState();

        return response()->json(['success' => true, 'goal' => $this->goalPayload($goal->fresh('deposits'))]);
    }

    public function destroyGoal(RealLifeSavingsGoal $goal): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($goal->user_id !== auth()->id(), 403);
        $goal->delete();
        return response()->json(['success' => true]);
    }

    public function storeDeposit(Request $request, RealLifeSavingsGoal $goal): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($goal->user_id !== auth()->id(), 403);
        $data = $request->validate([
            'amount'       => 'required|integer|min:1|max:99999999',
            'note'         => 'nullable|string|max:150',
            'deposited_on' => 'required|date',
        ]);

        RealLifeSavingsDeposit::create($data + ['goal_id' => $goal->id]);
        $goal->refreshCompletionState();

        return response()->json(['success' => true, 'goal' => $this->goalPayload($goal->fresh('deposits'))]);
    }

    public function destroyDeposit(RealLifeSavingsDeposit $deposit): JsonResponse
    {
        $this->ensureUnlocked();
        $goal = $deposit->goal;
        abort_if($goal->user_id !== auth()->id(), 403);

        $deposit->delete();

        // Deleting a deposit can drop the total back below target — un-complete it.
        if ($goal->status === 'completed' && $goal->totalSaved() < $goal->target_amount) {
            $goal->update(['status' => 'active', 'completed_at' => null]);
        }

        return response()->json(['success' => true, 'goal' => $this->goalPayload($goal->fresh('deposits'))]);
    }

    private function goalPayload(RealLifeSavingsGoal $g): array
    {
        return [
            'id'             => $g->id,
            'name'           => $g->name,
            'icon'           => $g->icon,
            'target_amount'  => $g->target_amount,
            'target_date'    => $g->target_date?->toDateString(),
            'status'         => $g->status,
            'total_saved'    => $g->totalSaved(),
            'progress_pct'   => $g->progressPct(),
            'completed_at'   => $g->completed_at?->toDateString(),
            'deposits'       => $g->deposits->map(fn ($d) => [
                'id'           => $d->id,
                'amount'       => $d->amount,
                'note'         => $d->note,
                'deposited_on' => $d->deposited_on->toDateString(),
            ])->values(),
        ];
    }

    // ── Real-life expenses ────────────────────────────────────────────────────

    public function storeExpense(Request $request): JsonResponse
    {
        $this->ensureUnlocked();
        $userId     = auth()->id();
        $categories = UserExpenseCategory::mapForUser($userId);

        $data = $request->validate([
            'amount'   => 'required|integer|min:1|max:99999999',
            'category' => 'required|string|in:' . implode(',', array_keys($categories)),
            'note'     => 'nullable|string|max:150',
            'spent_on' => 'required|date',
        ]);
        $data['user_id'] = $userId;

        $expense = RealLifeExpense::create($data);
        return response()->json(['success' => true, 'expense' => $this->expensePayload($expense)]);
    }

    public function destroyExpense(RealLifeExpense $expense): JsonResponse
    {
        $this->ensureUnlocked();
        abort_if($expense->user_id !== auth()->id(), 403);
        $expense->delete();
        return response()->json(['success' => true]);
    }

    private function expensePayload(RealLifeExpense $e): array
    {
        $cat = UserExpenseCategory::mapForUser($e->user_id)[$e->category] ?? null;
        return [
            'id'              => $e->id,
            'amount'          => $e->amount,
            'category'        => $e->category,
            // Falls back gracefully if the category was since renamed/deleted —
            // the expense keeps its raw key as text rather than disappearing.
            'category_label'  => $cat['label'] ?? ucfirst($e->category),
            'icon'            => $cat['icon'] ?? '📋',
            'note'            => $e->note,
            'spent_on'        => $e->spent_on->toDateString(),
        ];
    }

    /** Recent expenses (for the list view — most recent 30, any month). Read-only, never gated. */
    public function expenses(): JsonResponse
    {
        $expenses = RealLifeExpense::where('user_id', auth()->id())
            ->orderByDesc('spent_on')
            ->take(30)
            ->get()
            ->map(fn ($e) => $this->expensePayload($e));

        return response()->json(['expenses' => $expenses]);
    }

    // ── Monthly report & snapshot ─────────────────────────────────────────────

    /**
     * A month's worth of real-life activity in one snapshot — expenses (by
     * category), bills paid, and savings deposited. Read-only, never gated,
     * so a lapsed subscriber can still look back at their own history.
     */
    public function report(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $month = $request->input('month'); // "YYYY-MM", defaults to current month
        $start = $month ? Carbon::parse($month . '-01')->startOfMonth() : now()->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $expenses = RealLifeExpense::where('user_id', $user->id)
            ->whereBetween('spent_on', [$start->toDateString(), $end->toDateString()])
            ->get();

        $billsPaid = RealLifeBillPayment::where('user_id', $user->id)
            ->whereBetween('paid_on', [$start->toDateString(), $end->toDateString()])
            ->get();

        $deposits = RealLifeSavingsDeposit::whereHas('goal', fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('deposited_on', [$start->toDateString(), $end->toDateString()])
            ->get();

        $totalExpenses = (int) $expenses->sum('amount');
        $totalBills    = (int) $billsPaid->sum('amount');
        $totalSaved    = (int) $deposits->sum('amount');
        $totalOut      = $totalExpenses + $totalBills;
        $tracked       = $totalSaved + $totalOut;
        $savingsRate   = $tracked > 0 ? (int) round($totalSaved / $tracked * 100) : 0;

        $categoryMap = UserExpenseCategory::mapForUser($user->id);
        $byCategory  = $expenses->groupBy('category')->map(fn ($rows, $cat) => [
            'category' => $cat,
            'label'    => $categoryMap[$cat]['label'] ?? ucfirst($cat),
            'icon'     => $categoryMap[$cat]['icon'] ?? '📋',
            'total'    => (int) $rows->sum('amount'),
        ])->sortByDesc('total')->values();

        return response()->json([
            'month'          => $start->format('Y-m'),
            'month_label'    => $start->format('F Y'),
            'total_expenses' => $totalExpenses,
            'total_bills'    => $totalBills,
            'total_saved'    => $totalSaved,
            'total_tracked'  => $tracked,
            'savings_rate'   => $savingsRate,
            'grade'          => match (true) {
                $tracked === 0        => null,
                $savingsRate >= 30    => 'A',
                $savingsRate >= 15    => 'B',
                $savingsRate >= 0     => 'C',
                default               => 'D',
            },
            'by_category'    => $byCategory,
            'expense_count'  => $expenses->count(),
            'bill_count'     => $billsPaid->count(),
            'deposit_count'  => $deposits->count(),
        ]);
    }
}
