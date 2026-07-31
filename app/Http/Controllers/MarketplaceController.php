<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Bill;
use App\Models\GameNotification;
use App\Models\PlayerAsset;
use App\Models\PlayerBill;
use App\Models\StockPriceHistory;
use App\Services\PlanGate;
use App\Services\QuestTriggerService;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    // ── Growth Plays section metadata ───────────────────────────────────────────
    private const GROWTH_SECTIONS = [
        'serious_money'      => ['icon' => '♟️',  'label' => 'Serious Money',      'desc' => 'Significant capital required — meaningful returns', 'color' => '#8b5cf6'],
        'high_growth'        => ['icon' => '🚀',  'label' => 'High Growth',         'desc' => 'Higher risk, higher reward — for the ambitious',     'color' => '#f59e0b'],
        'dividend_builders'  => ['icon' => '💰',  'label' => 'Dividend Builders',   'desc' => 'Steady dividend income — grow your cash flow',       'color' => '#10b981'],
        'lifestyle_upgrades' => ['icon' => '🎧',  'label' => 'Lifestyle Upgrades',  'desc' => 'Improve your life and productivity',                 'color' => '#06b6d4'],
    ];

    public function index(Request $request)
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        $category = $request->get('category', 'all');

        // Starter Moves carousel — ALL categories, client-side tab filtering via Alpine
        $starterMoves = Asset::active()
            ->orderBy('base_price')
            ->where(fn($q) => $q->where('featured_section', 'starter_moves')->orWhere('tier', '<=', 2))
            ->limit(24)
            ->get();

        // Growth Plays sections with counts
        $growthCounts = Asset::active()
            ->whereIn('featured_section', array_keys(self::GROWTH_SECTIONS))
            ->selectRaw('featured_section, count(*) as cnt')
            ->groupBy('featured_section')
            ->pluck('cnt', 'featured_section');

        $growthSections = collect(self::GROWTH_SECTIONS)->map(fn($s, $key) => array_merge($s, [
            'key'   => $key,
            'count' => $growthCounts[$key] ?? 0,
        ]));

        $categoryCounts = Asset::active()->selectRaw('category, count(*) as cnt')->groupBy('category')->pluck('cnt', 'category');
        $totalCount     = Asset::active()->count();

        $ownedCounts = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->selectRaw('asset_id, SUM(quantity) as total')
            ->groupBy('asset_id')
            ->pluck('total', 'asset_id');

        // Monthly income from assets
        $myAssets          = PlayerAsset::where('user_id', $user->id)->where('status', 'active')->with('asset')->get();
        $assetMonthlyIncome = $myAssets->sum(fn($pa) => max(0, ($pa->asset->monthly_income - $pa->asset->monthly_cost) * $pa->quantity * $pa->conditionFactor()));
        $monthlyGross      = (int) ($progress->career_income_rate ?? 0);
        $totalMonthlyIncome = $monthlyGross + (int) $assetMonthlyIncome;

        return view('marketplace.index', compact(
            'user', 'progress', 'starterMoves', 'growthSections', 'category',
            'categoryCounts', 'totalCount', 'ownedCounts', 'totalMonthlyIncome', 'monthlyGross'
        ));
    }

    public function all(Request $request)
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        $query = Asset::active();

        // Text search
        $q = trim($request->get('q', ''));
        if (strlen($q) >= 2) {
            $query->where(fn($s) => $s
                ->where('name', 'like', "%{$q}%")
                ->orWhere('brand', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%"));
        }

        // Category filter
        $cat = $request->get('cat', 'all');
        if ($cat !== 'all') {
            $query->where('category', $cat);
        }

        // Section filter (includes group 'growth_plays' to match all growth sections)
        $section = $request->get('section', '');
        if ($section === 'growth_plays') {
            $query->whereIn('featured_section', array_keys(self::GROWTH_SECTIONS));
        } elseif ($section && $section !== 'all') {
            $query->where('featured_section', $section);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', (int) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', (int) $request->max_price);
        }

        // Income type checkboxes
        $incomeTypes = array_filter((array) $request->get('income', []));
        if ($incomeTypes) {
            $query->where(function ($q) use ($incomeTypes) {
                foreach ($incomeTypes as $t) {
                    match ($t) {
                        'passive'  => $q->orWhere(fn($s) => $s->where('monthly_income', '>', 0)->where('monthly_cost', 0)),
                        'use_earn' => $q->orWhere('category', 'vehicle'),
                        'business' => $q->orWhere('category', 'business'),
                        'capital'  => $q->orWhere('appreciation_rate', '>', 0),
                        default    => null,
                    };
                }
            });
        }

        // Income per month range
        $incomeRange = $request->get('income_range', '');
        if ($incomeRange === '0-5k') {
            $query->where('monthly_income', '>=', 0)->where('monthly_income', '<', 5000);
        } elseif ($incomeRange === '5k-50k') {
            $query->where('monthly_income', '>=', 5000)->where('monthly_income', '<', 50000);
        } elseif ($incomeRange === '50k+') {
            $query->where('monthly_income', '>=', 50000);
        }

        // Sort
        match ($request->get('sort', 'featured')) {
            'price_asc'  => $query->orderBy('base_price'),
            'price_desc' => $query->orderByDesc('base_price'),
            'income'     => $query->orderByDesc('monthly_income'),
            'newest'     => $query->orderByDesc('created_at'),
            default      => $query->orderBy('tier')->orderBy('base_price'),
        };

        $assets         = $query->paginate(12)->withQueryString();
        $categoryCounts = Asset::active()->selectRaw('category, count(*) as cnt')->groupBy('category')->pluck('cnt', 'category');
        $totalCount     = Asset::active()->count();
        $maxPrice       = Asset::active()->max('base_price') ?? 5000000;
        $monthlyGross   = (int) ($progress->career_income_rate ?? 0);

        $ownedCounts = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->selectRaw('asset_id, SUM(quantity) as total')
            ->groupBy('asset_id')
            ->pluck('total', 'asset_id');

        return view('marketplace.all', compact(
            'user', 'progress', 'assets', 'categoryCounts', 'totalCount',
            'ownedCounts', 'maxPrice', 'monthlyGross'
        ));
    }

    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $results = Asset::active()
            ->where(fn($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('brand', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhere('category', 'like', "%{$q}%"))
            ->orderBy('tier')
            ->limit(8)
            ->get()
            ->map(fn($a) => [
                'id'       => $a->id,
                'name'     => $a->name,
                'icon'     => $a->icon,
                'category' => $a->categoryLabel(),
                'price'    => 'Ksh ' . number_format($a->base_price),
                'net'      => $a->monthly_income > 0
                    ? ($a->monthly_income - $a->monthly_cost >= 0 ? '+' : '') . 'Ksh ' . number_format($a->monthly_income - $a->monthly_cost) . '/mo'
                    : null,
                'badge'    => $a->badge,
            ]);

        return response()->json($results);
    }

    public function buy(Request $request, Asset $asset)
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        // Checks
        if (!$asset->is_active) {
            return response()->json(['error' => 'This asset is no longer available.'], 422);
        }

        $owned = PlayerAsset::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->where('status', 'active')
            ->sum('quantity');

        if ($owned >= $asset->max_per_player) {
            return response()->json(['error' => "You already own the maximum ({$asset->max_per_player}) of this asset."], 422);
        }

        // Financed purchase: pay only the deposit, the balance becomes a monthly loan
        $financing = app(\App\Services\AssetFinancingService::class);
        $financed  = $request->boolean('financing');
        $quote     = null;

        if ($financed) {
            $quote = $financing->quote($asset);
            if (!$quote) {
                return response()->json(['error' => 'Only vehicles and property can be bought on financing.'], 422);
            }
            if ($progress->balance < $quote['deposit']) {
                $shortfall = number_format($quote['deposit'] - $progress->balance);
                return response()->json(['error' => "You need Ksh {$shortfall} more for the deposit of Ksh " . number_format($quote['deposit']) . "."], 422);
            }
        } elseif ($progress->balance < $asset->base_price) {
            $shortfall = number_format($asset->base_price - $progress->balance);
            return response()->json(['error' => "Insufficient balance. You need Ksh {$shortfall} more."], 422);
        }

        // Plan gate: free accounts own a limited number of active assets
        $gate         = app(PlanGate::class);
        $activeAssets = PlayerAsset::where('user_id', $user->id)->where('status', 'active')->count();
        if (!$gate->allows($user, 'max_assets', $activeAssets)) {
            return response()->json($gate->deny('max_assets', $gate->limit($user, 'max_assets')), 422);
        }

        $assignedBills = [];

        \DB::transaction(function () use ($user, $progress, $asset, $financed, $quote, $financing, &$assignedBills) {
            // Deduct cost — full price for cash, deposit only when financed
            $progress->balance -= $financed ? $quote['deposit'] : $asset->base_price;

            // Create player_asset record (income/upkeep accrue from purchase day)
            $playerAsset = PlayerAsset::create([
                'user_id'           => $user->id,
                'asset_id'          => $asset->id,
                'purchase_price'    => $asset->base_price,
                'current_value'     => $asset->base_price,
                'quantity'          => 1,
                'purchased_at_tick' => $progress->tick_count ?? 0,
                'status'            => 'active',
            ] + (\Illuminate\Support\Facades\Schema::hasColumn('player_assets', 'income_paid_to_tick') ? [
                'income_paid_to_tick' => $progress->tick_count ?? 0,
                'upkeep_paid_to_tick' => $progress->tick_count ?? 0,
            ] : []));

            // Financed: the unpaid balance becomes a monthly loan (auto-deducts like a bill)
            if ($financed) {
                $financing->finance($user, $asset, $playerAsset, $progress);
            }

            // Assign contextual bills for this asset (explicit slug or category-based:
            // vehicle → insurance + fuel, property → service charge)
            $assignedBills = app(\App\Services\BillService::class)->assignAssetBills($user, $asset);

            // Recalculate net worth (assets + savings + deals + chama − loans)
            $progress->recalculateNetWorth();
            $progress->save();

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'asset_purchased',
                'title'   => "{$asset->icon} {$asset->name} " . ($financed ? 'Financed!' : 'Purchased!'),
                'body'    => $financed
                    ? "Deposit of Ksh " . number_format($quote['deposit']) . " paid for {$asset->name}. Monthly installment: Ksh " . number_format($quote['monthly']) . " for {$quote['months']} game months. New balance: Ksh " . number_format($progress->balance)
                    : "You bought {$asset->name} for Ksh " . number_format($asset->base_price) . ". New balance: Ksh " . number_format($progress->balance),
                'icon'    => $asset->icon,
                'data'    => ['asset_id' => $asset->id, 'price' => $asset->base_price, 'financed' => $financed],
            ]);
        });

        // Quest auto-triggers: buying an item
        $qs = app(QuestTriggerService::class);
        $qs->fire($user, 'buy_item_category', ['category' => $asset->category]);
        $qs->fire($user, 'buy_item_slug',     ['slug'     => $asset->slug]);
        // Check balance/net-worth thresholds after purchase
        $qs->fire($user, 'reach_balance',   ['amount' => $progress->balance]);
        $qs->fire($user, 'reach_net_worth', ['amount' => $progress->net_worth_cache]);

        return response()->json([
            'success'          => true,
            'message'          => $financed
                ? "You now own {$asset->name}! Deposit Ksh " . number_format($quote['deposit']) . " paid — Ksh " . number_format($quote['monthly']) . "/game month for {$quote['months']} months."
                : "You now own {$asset->name}!",
            'new_balance'      => $progress->balance,
            'net_worth'        => $progress->net_worth_cache,
            'financing'        => $financed ? $quote : null,
            'bill_added'       => !empty($assignedBills),
            'bill_name'        => !empty($assignedBills) ? implode(' + ', $assignedBills) : null,
            'quest_completions'=> session('pending_quest_completions', []),
        ]);
    }

    public function portfolio()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        $playerAssets = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('asset')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalValue     = $playerAssets->sum('current_value');
        $totalCost      = $playerAssets->sum('purchase_price');
        $monthlyIncome  = $playerAssets->sum(fn($pa) => $pa->asset->monthly_income * $pa->quantity);
        $monthlyCost    = $playerAssets->sum(fn($pa) => $pa->asset->monthly_cost * $pa->quantity);
        $monthlyCashFlow = $monthlyIncome - $monthlyCost;

        $activeIds     = $playerAssets->pluck('id');
        $priceHistories = StockPriceHistory::whereIn('player_asset_id', $activeIds)
            ->orderBy('tick')
            ->get()
            ->groupBy('player_asset_id')
            ->map(fn($rows) => $rows->pluck('price')->values());

        $soldAssets = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'sold')
            ->with('asset')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $monthlyGross = (int) ($progress->career_income_rate ?? 0);

        return view('marketplace.portfolio', compact(
            'user', 'progress', 'playerAssets', 'priceHistories', 'soldAssets',
            'totalValue', 'totalCost', 'monthlyIncome', 'monthlyCost', 'monthlyCashFlow',
            'monthlyGross'
        ));
    }

    public function sell(Request $request, PlayerAsset $playerAsset)
    {
        $user = auth()->user();

        if ($playerAsset->user_id !== $user->id) {
            return response()->json(['error' => 'Not your asset.'], 403);
        }

        if ($playerAsset->status !== 'active') {
            return response()->json(['error' => 'This asset is not available for sale.'], 422);
        }

        $progress   = $user->getOrCreateProgress();
        $salePrice  = (int) round($playerAsset->current_value * 0.95); // 5% platform fee
        $asset      = $playerAsset->asset;

        \DB::transaction(function () use ($user, $progress, $playerAsset, $asset, $salePrice) {
            $playerAsset->update([
                'status'      => 'sold',
                'sold_price'  => $salePrice,
                'sold_at_tick'=> $progress->tick_count ?? 0,
            ]);

            $progress->balance += $salePrice;

            // Cancel associated auto-bill if no other asset of this type remains
            if ($asset->creates_bill_slug) {
                $stillOwns = PlayerAsset::where('user_id', $user->id)
                    ->where('asset_id', $asset->id)
                    ->where('status', 'active')
                    ->exists();
                if (!$stillOwns) {
                    $bill = Bill::where('slug', $asset->creates_bill_slug)->first();
                    if ($bill) {
                        PlayerBill::where('user_id', $user->id)
                            ->where('bill_id', $bill->id)
                            ->whereIn('status', ['active', 'overdue'])
                            ->update(['status' => 'cancelled']);
                    }
                }
            }

            $progress->recalculateNetWorth();
            $progress->save();

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'asset_sold',
                'title'   => "{$asset->icon} {$asset->name} Sold",
                'body'    => "Sold for Ksh " . number_format($salePrice) . " (after 5% platform fee). Balance: Ksh " . number_format($progress->balance),
                'icon'    => $asset->icon,
                'data'    => ['asset_id' => $asset->id, 'sale_price' => $salePrice],
            ]);
        });

        return response()->json([
            'success'     => true,
            'message'     => "Sold! Ksh " . number_format($salePrice) . " credited to your balance.",
            'new_balance' => $progress->balance,
            'net_worth'   => $progress->net_worth_cache,
        ]);
    }
}
