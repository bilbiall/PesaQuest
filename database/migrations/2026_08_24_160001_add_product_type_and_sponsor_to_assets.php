<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sub-classifies `fixed_income` assets (Money Market Fund, Treasury
     * Bill, Treasury Bond, ...) so the Marketplace can group/compare them
     * as real product types instead of one flat list, and lets any of them
     * carry a real sponsor's branding. `product_type` is a plain string
     * (not an enum) — this list is expected to grow as new fixed-income
     * product types get added, and a string is far cheaper to extend than
     * an enum column. `rate_updated_at` exists because these numbers, once
     * tied to a real sponsor brand, need to visibly go stale in Admin
     * rather than be trusted to memory.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('product_type', 40)->nullable()->after('category');
            $table->foreignId('mmf_sponsor_id')->nullable()->after('badge')->constrained('mmf_sponsors')->nullOnDelete();
            $table->timestamp('rate_updated_at')->nullable()->after('mmf_sponsor_id');
        });

        // Backfill every existing fixed_income asset by its known slug —
        // these are the exact slugs from AssetSeeder.php as of this migration.
        $map = [
            'money-market-fund'          => 'money_market_fund',
            'treasury-bills-91'          => 'treasury_bill',
            'treasury-bills-182'         => 'treasury_bill',
            'treasury-bills-364'         => 'treasury_bill',
            'treasury-bonds-5yr'         => 'treasury_bond',
            'treasury-bonds-2yr'         => 'treasury_bond',
            'treasury-bonds-10yr-infra'  => 'treasury_bond',
            'fixed-deposit-6mo'          => 'fixed_deposit',
            'fixed-deposit-12mo'         => 'fixed_deposit',
            'corporate-bond-3yr'         => 'corporate_bond',
            'sacco-fixed-deposit'        => 'sacco_deposit',
            'insurance-endowment-5yr'    => 'endowment',
            'sukuk-bond-3yr'             => 'sukuk',
        ];

        foreach ($map as $slug => $type) {
            DB::table('assets')->where('slug', $slug)->update(['product_type' => $type]);
        }

        // Anything else already in fixed_income that isn't in the map above
        // (a custom asset an admin created) — default it to money_market_fund
        // rather than leaving it unclassified and invisible to any future
        // "group by product type" view.
        DB::table('assets')
            ->where('category', 'fixed_income')
            ->whereNull('product_type')
            ->update(['product_type' => 'money_market_fund']);
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mmf_sponsor_id');
            $table->dropColumn(['product_type', 'rate_updated_at']);
        });
    }
};
