<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\PlayerShareHolding;
use App\Models\ShareTrade;
use App\Models\GameNotification;
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

        $user      = auth()->user();
        $progress  = $user->getOrCreateProgress();
        $share     = Share::where('id', $request->share_id)->where('is_active', true)->firstOrFail();
        $quantity  = (int) $request->quantity;
        $execPrice = $share->buyPrice();
        $cost      = round($execPrice * $quantity, 2);

        if ($progress->balance < $cost) {
            return response()->json(['error' => 'You need KES ' . number_format($cost) . ' to buy ' . $quantity . ' ' . $share->symbol . ' share(s).'], 422);
        }

        DB::transaction(function () use ($user, $progress, $share, $quantity, $cost, $execPrice) {
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
                'price'    => $execPrice,
                'total'    => $cost,
            ]);

            $progress->recalculateNetWorth();
            $progress->save();
        });

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'share_buy',
            'title'   => "{$share->icon} Bought {$quantity} {$share->symbol}",
            'body'    => 'KES ' . number_format($cost, 2) . " @ KES {$execPrice}/share. See it on your Portfolio.",
            'icon'    => $share->icon,
            'data'    => ['amount' => $cost, 'url' => '/portfolio'],
        ]);

        app(QuestTriggerService::class)->fire($user, 'buy_share', ['symbol' => $share->symbol]);

        return response()->json([
            'success' => true,
            'message' => "Bought {$quantity} {$share->symbol} @ KES " . number_format($execPrice, 2) . " (mkt KES " . number_format($share->current_price, 2) . ')',
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

        $execPrice  = $share->sellPrice();
        $revenue    = round($execPrice * $quantity, 2);
        $profitLoss = round(($execPrice - $holding->avg_cost) * $quantity, 2);

        DB::transaction(function () use ($user, $progress, $share, $quantity, $revenue, $profitLoss, $holding, $execPrice) {
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
                'price'       => $execPrice,
                'total'       => $revenue,
                'profit_loss' => $profitLoss,
            ]);

            $progress->recalculateNetWorth();
            $progress->save();
        });

        $verdict = $profitLoss > 0 ? 'profit' : ($profitLoss < 0 ? 'loss' : 'break-even');

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'share_sell',
            'title'   => "{$share->icon} Sold {$quantity} {$share->symbol}",
            'body'    => 'KES ' . number_format($revenue, 2) . " @ KES {$execPrice}/share — " . ($profitLoss >= 0 ? '+' : '') . number_format($profitLoss, 2) . " {$verdict}.",
            'icon'    => $share->icon,
            'data'    => ['amount' => $revenue, 'url' => '/portfolio'],
        ]);

        return response()->json([
            'success'     => true,
            'message'     => "Sold {$quantity} {$share->symbol} @ KES " . number_format($execPrice, 2) . " (mkt KES " . number_format($share->current_price, 2) . ") — " . ($profitLoss >= 0 ? '+' : '') . number_format($profitLoss, 2) . " {$verdict}",
            'profit_loss' => $profitLoss,
            'balance'     => $progress->balance,
        ]);
    }
}
