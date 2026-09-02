<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\GameNotification;
use App\Models\PlayerAsset;
use App\Models\Setting;
use App\Services\GameClock;
use Illuminate\Http\Request;

/**
 * Money Market Fund invest / top-up / withdraw — a dedicated flow because
 * MMF positions behave nothing like the generic buy-a-fixed-price-lot /
 * sell-instantly model everything else in Marketplace/Portfolio uses:
 * any KES amount, one position per fund, and a real 1-3 game-day
 * withdrawal delay. Daily compounding itself happens in
 * LifeSimulator::settleMmfInterest()/settleMmfWithdrawals() — this
 * controller only opens/adjusts/closes positions.
 */
class MmfController extends Controller
{
    public function invest(Request $request, Asset $asset)
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        if (($asset->product_type ?? null) !== 'money_market_fund') {
            return response()->json(['error' => 'This asset is not a Money Market Fund.'], 422);
        }

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $amount   = (int) $request->input('amount');
        $minDeposit = (int) Setting::get('mmf_min_deposit', 1000);

        if ($amount < $minDeposit) {
            return response()->json(['error' => "Minimum first deposit is Ksh " . number_format($minDeposit) . "."], 422);
        }

        if ($progress->balance < $amount) {
            return response()->json(['error' => 'Insufficient balance. You need Ksh ' . number_format($amount - $progress->balance) . ' more.'], 422);
        }

        $existing = PlayerAsset::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return response()->json(['error' => 'You already have a position in this fund — use Top Up instead.'], 422);
        }

        $tick       = (int) ($progress->tick_count ?? 0);
        $anchorTick = $tick + (now()->hour >= 11 ? 1 : 0);

        \DB::transaction(function () use ($user, $progress, $asset, $amount, $tick, $anchorTick) {
            $progress->balance -= $amount;

            PlayerAsset::create([
                'user_id'                => $user->id,
                'asset_id'               => $asset->id,
                'purchase_price'         => $amount,
                'current_value'          => $amount,
                'quantity'               => 1,
                'purchased_at_tick'      => $tick,
                'status'                 => 'active',
                'mmf_principal'          => $amount,
                'mmf_interest_earned'    => 0,
                'mmf_interest_taxed'     => 0,
                'mmf_last_interest_tick' => $anchorTick,
            ]);

            $progress->recalculateNetWorth();
            $progress->save();

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'mmf_invested',
                'title'   => "💰 Invested in {$asset->name}",
                'body'    => "Ksh " . number_format($amount) . " invested. It starts earning daily interest from " .
                             ($anchorTick > $tick ? 'the day after tomorrow' : 'tomorrow') . " — deposits after 11am start a day later.",
                'icon'    => $asset->icon ?? '💰',
                'data'    => ['asset_id' => $asset->id, 'amount' => $amount],
            ]);
        });

        try {
            app(\App\Services\QuestTriggerService::class)->fire($user, 'buy_item_category', ['category' => $asset->category]);
            app(\App\Services\QuestTriggerService::class)->fire($user, 'buy_item_slug', ['slug' => $asset->slug]);
        } catch (\Throwable $e) { /* quest engine is best-effort */ }

        return response()->json(['success' => true, 'balance' => $progress->balance]);
    }

    public function topup(Request $request, PlayerAsset $playerAsset)
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        $user = auth()->user();
        if ($playerAsset->user_id !== $user->id || !$playerAsset->isMmf() || $playerAsset->status !== 'active') {
            return response()->json(['error' => 'Not a valid MMF position.'], 422);
        }

        $progress   = $user->getOrCreateProgress();
        $amount     = (int) $request->input('amount');
        $minTopup   = (int) Setting::get('mmf_min_topup', 100);

        if ($amount < $minTopup) {
            return response()->json(['error' => "Minimum top-up is Ksh " . number_format($minTopup) . "."], 422);
        }
        if ($progress->balance < $amount) {
            return response()->json(['error' => 'Insufficient balance. You need Ksh ' . number_format($amount - $progress->balance) . ' more.'], 422);
        }

        // Bring interest current before adding new money, so the existing
        // balance's anchor is "now" and the cutoff logic below is accurate.
        app(\App\Services\LifeSimulator::class)->settleMmfPosition($playerAsset, $progress);

        $tick = (int) ($progress->tick_count ?? 0);

        \DB::transaction(function () use ($user, $progress, $playerAsset, $amount, $tick) {
            $progress->balance -= $amount;

            if (now()->hour >= 11) {
                $playerAsset->mmf_pending_topup_amount = ($playerAsset->mmf_pending_topup_amount ?? 0) + $amount;
                $playerAsset->mmf_topup_ready_tick     = $tick + 1;
            } else {
                $playerAsset->mmf_principal  = ($playerAsset->mmf_principal ?? 0) + $amount;
                $playerAsset->current_value  = ($playerAsset->current_value ?? 0) + $amount;
            }
            $playerAsset->save();

            $progress->recalculateNetWorth();
            $progress->save();
        });

        return response()->json(['success' => true, 'balance' => $progress->balance, 'position' => $playerAsset->fresh()]);
    }

    public function withdraw(Request $request, PlayerAsset $playerAsset)
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        $user = auth()->user();
        if ($playerAsset->user_id !== $user->id || !$playerAsset->isMmf() || $playerAsset->status !== 'active') {
            return response()->json(['error' => 'Not a valid MMF position.'], 422);
        }

        $progress = $user->getOrCreateProgress();

        // Settle interest first so current_value/interest figures are current.
        app(\App\Services\LifeSimulator::class)->settleMmfPosition($playerAsset, $progress);

        $amount = (int) $request->input('amount');
        if ($amount > (int) $playerAsset->current_value) {
            return response()->json(['error' => 'You can only withdraw up to your current balance of Ksh ' . number_format($playerAsset->current_value) . '.'], 422);
        }

        $taxPct         = (float) Setting::get('mmf_withholding_tax_pct', 15);
        $untaxed        = $playerAsset->mmfUntaxedInterest();
        $currentValue   = (int) $playerAsset->current_value;
        $interestShare  = $currentValue > 0 ? (int) round($amount * ($untaxed / $currentValue)) : 0;
        $tax            = (int) round($interestShare * $taxPct / 100);
        $netPayout      = $amount - $tax;

        $clock    = app(GameClock::class);
        $tick     = (int) ($progress->tick_count ?? 0);
        $minDays  = max(1, (int) Setting::get('mmf_withdrawal_min_days', 1));
        $maxDays  = max($minDays, (int) Setting::get('mmf_withdrawal_max_days', 3));
        $delay    = random_int($minDays, $maxDays);
        $readyTick = $tick + $delay;

        \DB::transaction(function () use ($playerAsset, $progress, $amount, $interestShare, $netPayout, $readyTick, $delay, $tax) {
            $playerAsset->current_value                 = max(0, (int) $playerAsset->current_value - $amount);
            $playerAsset->mmf_principal                 = max(0, (int) $playerAsset->mmf_principal - ($amount - $interestShare));
            $playerAsset->mmf_interest_taxed            = ($playerAsset->mmf_interest_taxed ?? 0) + $interestShare;
            $playerAsset->mmf_pending_withdrawal_amount = ($playerAsset->mmf_pending_withdrawal_amount ?? 0) + $netPayout;
            $playerAsset->mmf_withdrawal_ready_tick     = $readyTick;
            $playerAsset->save();

            $progress->recalculateNetWorth();
            $progress->save();

            GameNotification::create([
                'user_id' => $playerAsset->user_id,
                'type'    => 'mmf_withdrawal_requested',
                'title'   => "⏳ Withdrawal requested: {$playerAsset->asset->name}",
                'body'    => "Ksh " . number_format($amount) . " requested" . ($tax > 0 ? " (15% withholding tax of Ksh " . number_format($tax) . " applies to the interest portion)" : '') .
                             ". Ksh " . number_format($netPayout) . " will land in your wallet in {$delay} game day(s).",
                'icon'    => '⏳',
                'data'    => ['asset_id' => $playerAsset->asset_id, 'amount' => $amount, 'net' => $netPayout, 'ready_tick' => $readyTick],
            ]);
        });

        return response()->json([
            'success'     => true,
            'net_payout'  => $netPayout,
            'tax'         => $tax,
            'ready_in'    => $delay,
        ]);
    }
}
