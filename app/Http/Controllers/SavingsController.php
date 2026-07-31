<?php

namespace App\Http\Controllers;

use App\Models\SavingsDeposit;
use App\Models\SavingsScheme;
use App\Services\PlanGate;
use App\Services\QuestTriggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SavingsController extends Controller
{
    public function index()
    {
        if (request()->wantsJson() || request()->ajax()) {
            $schemes = SavingsScheme::where('user_id', auth()->id())
                ->where('is_archived', false)
                ->with('deposits')
                ->latest()
                ->get();
            return response()->json($schemes->map(fn($s) => $this->schemePayload($s)));
        }
        return view('savings.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:60',
            'target_amount' => 'required|numeric|min:1',
            'emoji'         => 'nullable|string|max:4',
            'color'         => 'nullable|string|max:20',
        ]);

        // Plan gate: free accounts get a limited number of open savings goals
        $user        = auth()->user();
        $gate        = app(PlanGate::class);
        $openSchemes = SavingsScheme::where('user_id', $user->id)->where('is_archived', false)->count();
        if (!$gate->allows($user, 'max_savings_schemes', $openSchemes)) {
            return response()->json($gate->deny('max_savings_schemes', $gate->limit($user, 'max_savings_schemes')), 422);
        }

        $data['user_id']            = auth()->id();
        $data['current_amount']     = 0;
        // Interest accrues per game month from the tick the scheme opens
        $data['last_interest_tick'] = (int) ($user->getOrCreateProgress()->tick_count ?? 0);

        $scheme = SavingsScheme::create($data);

        app(QuestTriggerService::class)->fire(auth()->user(), 'open_savings');

        return response()->json(['success' => true, 'scheme' => $this->schemePayload($scheme->load('deposits'))]);
    }

    public function deposit(Request $request, SavingsScheme $scheme)
    {
        abort_if($scheme->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'note'   => 'nullable|string|max:120',
        ]);

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        // Real money: savings move from the wallet into the scheme
        if (($progress->balance ?? 0) < $data['amount']) {
            return response()->json([
                'error' => 'Insufficient wallet balance — you have Ksh ' . number_format($progress->balance ?? 0) . '.',
            ], 422);
        }

        SavingsDeposit::create(['scheme_id' => $scheme->id, 'amount' => $data['amount'], 'note' => $data['note'] ?? null]);

        $scheme->current_amount += $data['amount'];
        $scheme->save();

        $progress->balance -= (int) $data['amount'];
        $progress->recalculateNetWorth();
        $progress->save();

        // Quest auto-triggers. Semantics (mirrored in the quest form's guide):
        //   deposit_savings — ONE savings pocket's balance reaches the target
        //   reach_savings   — TOTAL saved across ALL pockets reaches the target
        //   reach_balance   — wallet (spendable cash) reaches the target
        $totalSaved = (float) SavingsScheme::where('user_id', $user->id)->sum('current_amount');
        $qs = app(QuestTriggerService::class);
        $qs->fire($user, 'deposit_savings', ['amount' => $scheme->current_amount]);
        $qs->fire($user, 'reach_savings',   ['amount' => $totalSaved]);
        $qs->fire($user, 'reach_balance',   ['amount' => $progress->balance ?? 0]);
        $qs->fire($user, 'reach_net_worth', ['amount' => $progress->net_worth_cache ?? 0]);

        return response()->json([
            'success'     => true,
            'new_balance' => $progress->balance,
            'scheme'      => $this->schemePayload($scheme->load('deposits')),
        ]);
    }

    /** Withdraw part (or all) of a scheme's balance back to the wallet. */
    public function withdraw(Request $request, SavingsScheme $scheme)
    {
        abort_if($scheme->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = (int) $data['amount'];

        if ($amount > $scheme->current_amount) {
            return response()->json([
                'error' => 'You only have Ksh ' . number_format($scheme->current_amount) . ' in this scheme.',
            ], 422);
        }

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        // Record as a negative movement so the history reads like a bank statement
        SavingsDeposit::create([
            'scheme_id' => $scheme->id,
            'amount'    => -$amount,
            'type'      => 'withdrawal',
            'note'      => $request->input('note') ?: 'Withdrawal to wallet',
        ]);

        $scheme->current_amount -= $amount;
        $scheme->save();

        $progress->balance += $amount;
        $progress->recalculateNetWorth();
        $progress->save();

        return response()->json([
            'success'     => true,
            'withdrawn'   => $amount,
            'new_balance' => $progress->balance,
            'scheme'      => $this->schemePayload($scheme->load('deposits')),
        ]);
    }

    public function destroy(SavingsScheme $scheme)
    {
        abort_if($scheme->user_id !== auth()->id(), 403);

        // Closing a scheme returns the saved money to the wallet
        $refund = (int) $scheme->current_amount;
        $scheme->update(['is_archived' => true, 'current_amount' => 0]);

        if ($refund > 0) {
            $progress = auth()->user()->getOrCreateProgress();
            $progress->balance += $refund;
            $progress->recalculateNetWorth();
            $progress->save();
        }

        return response()->json(['success' => true, 'refunded' => $refund]);
    }

    private function schemePayload(SavingsScheme $s): array
    {
        $pct = $s->target_amount > 0
            ? min(100, round($s->current_amount / $s->target_amount * 100, 1))
            : 0;

        // Estimate completion date based on average deposit interval
        $deposits       = $s->deposits;
        $avgDeposit     = $deposits->count() > 0 ? $deposits->avg('amount') : 0;
        $remaining      = max(0, $s->target_amount - $s->current_amount);
        $estimatedMonths = ($avgDeposit > 0) ? ceil($remaining / $avgDeposit) : null;
        $estimatedDate  = $estimatedMonths ? now()->addMonths($estimatedMonths)->format('M Y') : null;

        $totalDeposited = (int) $deposits->where('type', 'deposit')->sum('amount');
        $totalWithdrawn = (int) abs($deposits->where('type', 'withdrawal')->sum('amount'));

        return [
            'id'              => $s->id,
            'name'            => $s->name,
            'emoji'           => $s->emoji,
            'color'           => $s->color,
            'target_amount'   => $s->target_amount,
            'current_amount'  => $s->current_amount,
            'interest_earned' => (int) ($s->interest_earned ?? 0),
            'total_deposited' => $totalDeposited,
            'total_withdrawn' => $totalWithdrawn,
            'interest_rate'   => (float) \App\Models\Setting::get('savings_interest_annual', 8),
            'progress_pct'    => $pct,
            'estimated_date'  => $estimatedDate,
            'deposit_count'   => $deposits->count(),
            'deposits'        => $deposits->take(6)->map(fn($d) => [
                'amount' => $d->amount,
                'type'   => $d->type ?? 'deposit',
                'note'   => $d->note,
                'date'   => $d->created_at->format('d M'),
            ])->values(),
        ];
    }

}
