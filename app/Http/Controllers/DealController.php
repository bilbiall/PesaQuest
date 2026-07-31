<?php

namespace App\Http\Controllers;

use App\Models\InvestmentDeal;
use App\Models\PlayerDeal;
use App\Services\PlanGate;
use App\Services\QuestTriggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealController extends Controller
{
    public function invest(Request $request)
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        $request->validate([
            'deal_id' => 'required|integer|exists:investment_deals,id',
        ]);

        $deal = InvestmentDeal::where('id', $request->deal_id)
            ->where('is_active', true)
            ->firstOrFail();

        if ($progress->balance < $deal->cost) {
            return response()->json(['error' => "You need KES " . number_format($deal->cost) . " to enter this deal."], 422);
        }

        // One active deal per deal type per player
        $existing = PlayerDeal::where('user_id', $user->id)
            ->where('deal_id', $deal->id)
            ->where('status', 'pending')
            ->exists();
        if ($existing) {
            return response()->json(['error' => 'You already have a pending position in this deal.'], 422);
        }

        // Plan gate: free accounts run a limited number of pending deals at once
        $gate         = app(PlanGate::class);
        $pendingDeals = PlayerDeal::where('user_id', $user->id)->where('status', 'pending')->count();
        if (!$gate->allows($user, 'max_active_deals', $pendingDeals)) {
            return response()->json($gate->deny('max_active_deals', $gate->limit($user, 'max_active_deals')), 422);
        }

        DB::transaction(function () use ($user, $progress, $deal, $request) {
            $progress->balance -= $deal->cost;

            PlayerDeal::create([
                'user_id'         => $user->id,
                'deal_id'         => $deal->id,
                'amount_invested' => $deal->cost,
                'resolve_at_tick' => $progress->tick_count + $deal->maturity_ticks,
                'status'          => 'pending',
            ]);

            // Net worth unchanged in truth (cash → deal position) — keep cache accurate
            $progress->recalculateNetWorth();
            $progress->save();
        });

        // Fire quest triggers
        app(QuestTriggerService::class)->fire($user, 'invest_deal', [
            'category' => $deal->category,
            'risk'     => $deal->risk_level,
        ]);

        $ticksLabel = $deal->maturity_ticks <= 7 ? "{$deal->maturity_ticks} game day(s)" : "{$deal->maturity_ticks} game days";

        return response()->json([
            'success'   => true,
            'message'   => "Deal entered! You'll know in ~{$ticksLabel}.",
            'balance'   => $progress->balance,
        ]);
    }
}
