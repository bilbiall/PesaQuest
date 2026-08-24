<?php

namespace App\Http\Controllers;

use App\Models\PlayerAsset;
use App\Models\PlayerDeal;
use App\Models\PlayerLoan;
use App\Models\PlayerShareHolding;
use App\Models\SavingsScheme;
use App\Models\ShareTrade;
use App\Models\StockPriceHistory;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    /**
     * Investment & Assets Portfolio — everything the player owns,
     * is invested in, is saving toward, and owes.
     */
    public function index()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $tick     = (int) ($progress->tick_count ?? 0);

        /* ── Owned assets ── */
        $playerAssets = PlayerAsset::where('user_id', $user->id)
            ->active()
            ->with('asset.mmfSponsor')
            ->orderByDesc('created_at')
            ->get();

        $totalValue    = (int) $playerAssets->sum('current_value');
        $totalInvested = (int) $playerAssets->sum('purchase_price');
        $unrealisedPL  = $totalValue - $totalInvested;
        $monthlyIncome = (int) $playerAssets->sum(fn ($pa) => ($pa->asset->monthly_income ?? 0) * $pa->quantity);
        $monthlyCost   = (int) $playerAssets->sum(fn ($pa) => ($pa->asset->monthly_cost ?? 0) * $pa->quantity);

        // Grouped for the view: property / vehicle / business / investment / gadget / other
        $assetsByCategory = $playerAssets->groupBy(fn ($pa) => $pa->asset->category ?? 'other');

        // Plain-language "why" statements for the summary bar, built from this
        // player's actual holdings so they read as an answer, not a definition.
        $assetInsights = $this->buildAssetInsights($playerAssets, $totalValue, $totalInvested, $unrealisedPL);

        /* ── Active investment deals (cyan) ── */
        $activeDeals = PlayerDeal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('deal')
            ->orderBy('resolve_at_tick')
            ->get();

        $totalDealCapital = (int) $activeDeals->sum('amount_invested');

        /* ── Equity Square share holdings (cyan, same "active investments" family) ── */
        $myShares = PlayerShareHolding::where('user_id', $user->id)
            ->where('quantity', '>', 0)
            ->with('share')
            ->get()
            ->filter(fn ($h) => $h->share !== null)
            ->values();

        $totalSharesValue    = (int) $myShares->sum(fn ($h) => $h->currentValue());
        $totalSharesInvested = (int) $myShares->sum(fn ($h) => $h->quantity * $h->avg_cost);
        // Sum of gainLoss() (sell-price based), not totalValue - totalInvested
        // (mid-price based) -- so this header always equals the sum of the
        // per-holding Gain/Loss cards rendered right below it.
        $sharesUnrealisedPL  = (int) $myShares->sum(fn ($h) => $h->gainLoss());

        /* ── Realised share trades (sells only — profit/loss booked) ── */
        $shareTradeHistory = ShareTrade::where('user_id', $user->id)
            ->where('action', 'sell')
            ->with('share')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        /* ── Completed deals history ── */
        $completedDeals = PlayerDeal::where('user_id', $user->id)
            ->whereIn('status', ['success', 'failed'])
            ->with('deal')
            ->orderByDesc('resolved_at')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        /* ── Savings schemes (emerald) ── */
        $savingsSchemes = SavingsScheme::where('user_id', $user->id)
            ->where('is_archived', false)
            ->with(['deposits' => fn ($q) => $q->limit(20)])
            ->get()
            ->map(function ($scheme) {
                // Projected completion: average monthly pace from deposits in the last 60 days
                $recent = $scheme->deposits
                    ->filter(fn ($d) => $d->created_at && $d->created_at->gte(now()->subDays(60)));

                $projection = null; // "—" in the view when null
                if ($recent->isNotEmpty()) {
                    $spanDays    = max(1, (int) $recent->min('created_at')->diffInDays(now()));
                    $monthlyPace = $recent->sum('amount') / $spanDays * 30;
                    $remaining   = max(0, $scheme->target_amount - $scheme->current_amount);
                    if ($remaining <= 0) {
                        $projection = 'Goal reached';
                    } elseif ($monthlyPace > 0) {
                        $months     = (int) ceil($remaining / $monthlyPace);
                        $projection = $months <= 1 ? '~1 month at this pace' : "~{$months} months at this pace";
                    }
                }
                $scheme->projection = $projection;

                return $scheme;
            });

        /* ── Active loans (amber/red) ── */
        $loans = PlayerLoan::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('loanProduct')
            ->orderBy('next_payment_tick')
            ->get();

        $totalDebt = (int) $loans->sum('outstanding_balance');

        /* ── Net worth chart: composite of asset price histories ── */
        $netWorthSeries = [];
        if ($playerAssets->isNotEmpty()) {
            $history = StockPriceHistory::whereIn('player_asset_id', $playerAssets->pluck('id'))
                ->orderBy('tick')
                ->get(['player_asset_id', 'tick', 'price']);

            if ($history->isNotEmpty()) {
                $byTick = $history->groupBy('tick');
                $ticks  = $byTick->keys()->sort()->values();

                // Running "latest price seen so far" per asset; assets with no
                // recorded price yet fall back to their purchase price.
                $latest = [];
                foreach ($ticks as $t) {
                    foreach ($byTick[$t] as $row) {
                        $latest[$row->player_asset_id] = (int) $row->price;
                    }
                    $sum = 0;
                    foreach ($playerAssets as $pa) {
                        $sum += $latest[$pa->id] ?? (int) $pa->purchase_price;
                    }
                    $netWorthSeries[] = ['tick' => (int) $t, 'value' => $sum];
                }

                // Cap at ~30 evenly-sampled points (always keep first + last)
                $n = count($netWorthSeries);
                if ($n > 30) {
                    $sampled = [];
                    for ($i = 0; $i < 30; $i++) {
                        $sampled[] = $netWorthSeries[(int) round($i * ($n - 1) / 29)];
                    }
                    $netWorthSeries = $sampled;
                }
            }
        }

        return view('portfolio.index', [
            'user'             => $user,
            'progress'         => $progress,
            'tick'             => $tick,
            'playerAssets'     => $playerAssets,
            'assetsByCategory' => $assetsByCategory,
            'totalValue'       => $totalValue,
            'totalInvested'    => $totalInvested,
            'unrealisedPL'     => $unrealisedPL,
            'monthlyIncome'    => $monthlyIncome,
            'monthlyCost'      => $monthlyCost,
            'portfolioCount'   => $playerAssets->count(),
            'assetInsights'    => $assetInsights,
            'activeDeals'      => $activeDeals,
            'totalDealCapital' => $totalDealCapital,
            'completedDeals'   => $completedDeals,
            'myShares'            => $myShares,
            'totalSharesValue'    => $totalSharesValue,
            'totalSharesInvested' => $totalSharesInvested,
            'sharesUnrealisedPL'  => $sharesUnrealisedPL,
            'shareTradeHistory'   => $shareTradeHistory,
            'savingsSchemes'   => $savingsSchemes,
            'loans'            => $loans,
            'totalDebt'        => $totalDebt,
            'netWorthSeries'   => $netWorthSeries,
        ]);
    }

    /**
     * Turns the six summary-bar numbers into brief, player-specific
     * sentences — naming the actual category/asset driving each figure
     * instead of a generic dictionary definition.
     */
    private function buildAssetInsights($playerAssets, int $totalValue, int $totalInvested, int $unrealisedPL): array
    {
        $count = $playerAssets->count();

        if ($count === 0) {
            return [
                'asset_value'   => "You don't own any assets yet — buy your first one from the Marketplace to start building this picture.",
                'invested'      => "Nothing invested yet.",
                'unrealised_pl' => "No holdings to gain or lose value on yet.",
                'income'        => "No assets generating income yet.",
                'costs'         => "No ongoing asset costs right now.",
                'holdings'      => "Your holdings will show up here once you buy your first asset.",
            ];
        }

        $byCategoryValue = $playerAssets->groupBy(fn ($pa) => $pa->asset->categoryLabel())
            ->map(fn ($group) => (int) $group->sum('current_value'))
            ->sortDesc();

        $byCategoryPL = $playerAssets->groupBy(fn ($pa) => $pa->asset->categoryLabel())
            ->map(fn ($group) => (int) $group->sum(fn ($pa) => $pa->gainLoss()));

        $topValueCat  = $byCategoryValue->keys()->first();
        $worstPlCat   = $byCategoryPL->sort()->keys()->first();
        $bestPlCat    = $byCategoryPL->sortDesc()->keys()->first();

        $incomeAssets = $playerAssets->filter(fn ($pa) => ($pa->asset->monthly_income ?? 0) > 0);
        $topIncomeAsset = $incomeAssets->sortByDesc(fn ($pa) => ($pa->asset->monthly_income ?? 0) * $pa->quantity)->first();

        $costAssets = $playerAssets->filter(fn ($pa) => ($pa->asset->monthly_cost ?? 0) > 0);
        $topCostAsset = $costAssets->sortByDesc(fn ($pa) => ($pa->asset->monthly_cost ?? 0) * $pa->quantity)->first();

        $holdingsBreakdown = $playerAssets->groupBy(fn ($pa) => $pa->asset->categoryLabel())
            ->map->count()
            ->sortDesc()
            ->map(fn ($n, $label) => $n . ' ' . ($n === 1 ? $label : Str::plural($label)))
            ->implode(', ');

        $insights = [];

        $insights['asset_value'] = "Your {$count} " . Str::plural('asset', $count) . " are worth Ksh " . number_format($totalValue) . " combined right now"
            . ($topValueCat ? " — mostly {$topValueCat} (Ksh " . number_format($byCategoryValue->first()) . ')' : '') . '.';

        $insights['invested'] = "You've put Ksh " . number_format($totalInvested) . " into these assets since buying them. This number only changes when you buy or sell — never just because prices move.";

        if ($unrealisedPL < 0 && $worstPlCat) {
            $insights['unrealised_pl'] = "You're down Ksh " . number_format(abs($unrealisedPL)) . " on paper, mostly from {$worstPlCat} losing value (Ksh " . number_format(abs($byCategoryPL[$worstPlCat])) . "). This is only a loss on paper until you actually sell — values can recover.";
        } elseif ($unrealisedPL > 0 && $bestPlCat) {
            $insights['unrealised_pl'] = "You're up Ksh " . number_format($unrealisedPL) . " on paper, led by {$bestPlCat} (+Ksh " . number_format($byCategoryPL[$bestPlCat]) . "). Still just a paper gain until you sell.";
        } else {
            $insights['unrealised_pl'] = "Your assets are worth exactly what you paid for them — no gain or loss yet.";
        }

        if ($topIncomeAsset) {
            $incomeAmt = ($topIncomeAsset->asset->monthly_income ?? 0) * $topIncomeAsset->quantity;
            $insights['income'] = $incomeAssets->count() . " of your {$count} assets pay out every month — {$topIncomeAsset->asset->name} is your biggest earner at Ksh " . number_format($incomeAmt) . '/mo.';
        } else {
            $insights['income'] = "None of your current assets pay monthly income — they're value plays (buy low, sell high) rather than cash generators.";
        }

        if ($topCostAsset) {
            $costAmt = ($topCostAsset->asset->monthly_cost ?? 0) * $topCostAsset->quantity;
            $insights['costs'] = "Costs/mo come from upkeep or loan repayments on assets like {$topCostAsset->asset->name} (Ksh " . number_format($costAmt) . "/mo) — these apply whether or not the asset is making you money.";
        } else {
            $insights['costs'] = "None of your assets have ongoing costs right now.";
        }

        $insights['holdings'] = "You own {$count} active " . Str::plural('asset', $count) . ": {$holdingsBreakdown}. Selling or losing one removes it from this count.";

        return $insights;
    }
}
