<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\PlayerShareHolding;
use App\Models\ShareTrade;
use App\Services\QuestTriggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    public function buy(Request $request)
    {
        $request->validate([
            'share_id' => 'required|integer|exists:shares,id',
            'quantity' => 'required|integer|min:1|max:100000',
        ]);

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $share    = Share::where('id', $request->share_id)->where('is_active', true)->firstOrFail();
        $quantity = (int) $request->quantity;
        $cost     = round($share->current_price * $quantity, 2);

        if ($progress->balance < $cost) {
            return response()->json(['error' => 'You need KES ' . number_format($cost) . ' to buy ' . $quantity . ' ' . $share->symbol . ' share(s).'], 422);
        }

        DB::transaction(function () use ($user, $progress, $share, $quantity, $cost) {
            $progress->balance -= $cost;

            $holding = PlayerShareHolding::firstOrNew(['user_id' => $user->id, 'share_id' => $share->id]);
            $existingTotal = $holding->quantity * $holding->avg_cost;
            $holding->quantity  = $holding->quantity + $quantity;
            $holding->avg_cost  = round(($existingTotal + $cost) / $holding->quantity, 2);
            $holding->save();

            ShareTrade::create([
                'user_id'  => $user->id,
                'share_id' => $share->id,
                'action'   => 'buy',
                'quantity' => $quantity,
                'price'    => $share->current_price,
                'total'    => $cost,
            ]);

            $progress->recalculateNetWorth();
            $progress->save();
        });

        app(QuestTriggerService::class)->fire($user, 'buy_share', ['symbol' => $share->symbol]);

        return response()->json([
            'success' => true,
            'message' => "Bought {$quantity} {$share->symbol} @ KES " . number_format($share->current_price),
            'balance' => $progress->balance,
        ]);
    }

    public function sell(Request $request)
    {
        $request->validate([
            'share_id' => 'required|integer|exists:shares,id',
            'quantity' => 'required|integer|min:1|max:100000',
        ]);

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $share    = Share::findOrFail($request->share_id);
        $quantity = (int) $request->quantity;

        $holding = PlayerShareHolding::where('user_id', $user->id)->where('share_id', $share->id)->first();
        if (!$holding || $holding->quantity < $quantity) {
            return response()->json(['error' => "You don't own {$quantity} {$share->symbol} share(s) to sell."], 422);
        }

        $revenue    = round($share->current_price * $quantity, 2);
        $profitLoss = round(($share->current_price - $holding->avg_cost) * $quantity, 2);

        DB::transaction(function () use ($user, $progress, $share, $quantity, $revenue, $profitLoss, $holding) {
            $progress->balance += $revenue;

            $holding->quantity -= $quantity;
            if ($holding->quantity <= 0) {
                $holding->delete();
            } else {
                $holding->save();
            }

            ShareTrade::create([
                'user_id'     => $user->id,
                'share_id'    => $share->id,
                'action'      => 'sell',
                'quantity'    => $quantity,
                'price'       => $share->current_price,
                'total'       => $revenue,
                'profit_loss' => $profitLoss,
            ]);

            $progress->recalculateNetWorth();
            $progress->save();
        });

        $verdict = $profitLoss > 0 ? 'profit' : ($profitLoss < 0 ? 'loss' : 'break-even');

        return response()->json([
            'success'     => true,
            'message'     => "Sold {$quantity} {$share->symbol} @ KES " . number_format($share->current_price) . " — " . ($profitLoss >= 0 ? '+' : '') . number_format($profitLoss) . " {$verdict}",
            'profit_loss' => $profitLoss,
            'balance'     => $progress->balance,
        ]);
    }
}
