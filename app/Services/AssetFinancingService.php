<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\GameNotification;
use App\Models\LoanProduct;
use App\Models\PlayerAsset;
use App\Models\PlayerLoan;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserProgress;

/**
 * Asset financing — buy a vehicle or property with a deposit and a loan.
 *
 * The player pays only the deposit up front; the balance becomes a PlayerLoan
 * that auto-deducts monthly (every 30 game days) via LifeSimulator::settleLoans,
 * exactly like a bill. Because the loan carries interest, the total paid over
 * the term is HIGHER than the cash price — that difference is the core lesson.
 *
 * Rates/deposit/term are admin-configurable via GameSet Hub → Asset Financing,
 * stored as Settings JSON exactly like CareerService's fields/tracks. See
 * docs/GAMESET_GUIDE.md §9b for the full admin guide.
 */
class AssetFinancingService
{
    /** Fallback terms — used only until an admin saves custom ones. Ticks: 30 = 1 game month. */
    const DEFAULT_TERMS = [
        'vehicle'  => ['deposit_pct' => 0.20, 'annual_rate' => 14.0, 'term_ticks' => 720,  'product' => 'Vehicle Financing',  'icon' => '🚗'],
        'property' => ['deposit_pct' => 0.10, 'annual_rate' => 12.0, 'term_ticks' => 1080, 'product' => 'Property Mortgage',  'icon' => '🏠'],
    ];

    private static ?array $termsCache = null;

    /** All financing terms, admin-configured (Settings JSON) or defaults. */
    public static function terms(): array
    {
        if (self::$termsCache !== null) return self::$termsCache;

        $saved = [];
        try {
            $saved = json_decode(Setting::get('asset_financing_terms', '') ?: '[]', true) ?: [];
        } catch (\Throwable $e) {
            // settings table unavailable (fresh install) — use defaults
        }

        // Merge over defaults per-category so a saved row always keeps its
        // 'product'/'icon' even if the admin form only submits numeric fields.
        $merged = self::DEFAULT_TERMS;
        foreach ($saved as $category => $row) {
            if (isset($merged[$category]) && is_array($row)) {
                $merged[$category] = array_merge($merged[$category], $row);
            }
        }

        return self::$termsCache = $merged;
    }

    public function isFinanceable(Asset $asset): bool
    {
        return isset(self::terms()[$asset->category]);
    }

    /**
     * Full financing quote for an asset (null if the category can't be financed).
     *
     * @return array{deposit:int, principal:int, monthly:int, months:int,
     *               total_repay:int, total_cost:int, interest_cost:int, annual_rate:float}|null
     */
    public function quote(Asset $asset): ?array
    {
        if (!$this->isFinanceable($asset)) return null;

        $terms     = self::terms()[$asset->category];
        $product   = $this->product($asset->category);
        $price     = (int) $asset->base_price;
        $deposit   = (int) ceil($price * $terms['deposit_pct']);
        $principal = max(0, $price - $deposit);
        $monthly   = $product->calculatePayment($principal);
        $months    = (int) ceil($product->term_ticks / max(1, $product->payment_period_ticks));
        $totalRepay = $monthly * $months;
        $totalCost  = $deposit + $totalRepay;

        return [
            'deposit'       => $deposit,
            'principal'     => $principal,
            'monthly'       => $monthly,
            'months'        => $months,
            'total_repay'   => $totalRepay,
            'total_cost'    => $totalCost,
            'interest_cost' => max(0, $totalCost - $price),
            'annual_rate'   => $terms['annual_rate'],
        ];
    }

    /**
     * Create the financing loan for a just-purchased asset. The deposit must
     * already have been deducted by the caller (inside the same transaction).
     */
    public function finance(User $user, Asset $asset, PlayerAsset $playerAsset, UserProgress $progress): PlayerLoan
    {
        $terms   = self::terms()[$asset->category];
        $product = $this->product($asset->category);
        $quote   = $this->quote($asset);
        $tick    = (int) ($progress->tick_count ?? 0);

        $loan = PlayerLoan::create([
            'user_id'              => $user->id,
            'loan_product_id'      => $product->id,
            'player_asset_id'      => $playerAsset->id,
            'label'                => "{$asset->name} — {$product->name}",
            'principal'            => $quote['principal'],
            'annual_interest_rate' => $product->annual_interest_rate,
            'outstanding_balance'  => $quote['principal'],
            'payment_amount'       => $quote['monthly'],
            'payment_period_ticks' => $product->payment_period_ticks,
            'disbursed_at_tick'    => $tick,
            'due_at_tick'          => $tick + $product->term_ticks,
            'next_payment_tick'    => $tick + $product->payment_period_ticks,
            'status'               => 'active',
        ]);

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'loan_taken',
            'title'   => "{$terms['icon']} Financing Approved: {$asset->name}",
            'body'    => 'Deposit paid: Ksh ' . number_format($quote['deposit'])
                       . '. Monthly installment: Ksh ' . number_format($quote['monthly'])
                       . ' for ' . $quote['months'] . ' game months (auto-deducted like a bill). Total cost with interest: Ksh '
                       . number_format($quote['total_cost']) . ' — Ksh ' . number_format($quote['interest_cost'])
                       . ' more than the cash price. That\'s what credit costs.',
            'icon'    => $terms['icon'],
            'data'    => ['asset_id' => $asset->id, 'loan_id' => $loan->id] + $quote,
        ]);

        return $loan;
    }

    /**
     * Hidden system loan product per category. is_active=false keeps it out of
     * the bank's takeable-loan list — it can only be reached through financing.
     *
     * Kept in sync with the live terms() on every call (updateOrCreate, not
     * firstOrCreate) so that editing rates in GameSet Hub → Asset Financing
     * takes effect immediately for anyone financing an asset afterwards —
     * previously this row was written once and never updated, so edited
     * rates silently had no effect on the actual instalment calculation.
     */
    /** @var array<string, LoanProduct> per-request cache */
    private array $productCache = [];

    public function product(string $category): LoanProduct
    {
        if (isset($this->productCache[$category])) {
            return $this->productCache[$category];
        }

        $terms = self::terms()[$category];

        return $this->productCache[$category] = LoanProduct::updateOrCreate(
            ['name' => $terms['product']],
            [
                'icon'                 => $terms['icon'],
                'description'          => 'System product for financed ' . $category . ' purchases. Deposit up front, balance repaid monthly with interest.',
                'min_amount'           => 0,
                'max_amount'           => 0,
                'annual_interest_rate' => $terms['annual_rate'],
                'term_ticks'           => $terms['term_ticks'],
                'payment_period_ticks' => 30,
                'min_credit_score'     => 0,
                'is_active'            => false,
                'sort_order'           => 99,
            ]
        );
    }
}
