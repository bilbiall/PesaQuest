<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chama_share_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chama_id')->constrained()->cascadeOnDelete();
            $table->foreignId('share_id')->constrained('shares')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('avg_cost', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['chama_id', 'share_id']);
        });

        Schema::create('chama_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chama_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->constrained('investment_deals')->cascadeOnDelete();
            $table->unsignedInteger('amount_invested');
            // Real timestamp, not a tick count — a chama has no personal game
            // clock the way a player does, so maturity is resolved on wall-clock
            // time (via GameClock::realSecondsForTicks), same as FinancialCrisis.
            $table->timestamp('resolve_at');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->integer('profit_loss')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'resolve_at']);
        });

        DB::statement("ALTER TABLE chama_proposals MODIFY type ENUM(
            'buy_asset', 'sell_asset', 'change_contribution', 'remove_member',
            'take_loan', 'withdraw', 'change_loan_terms',
            'enable_rotation', 'disable_rotation',
            'buy_share', 'sell_share', 'invest_deal'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE chama_proposals MODIFY type ENUM(
            'buy_asset', 'sell_asset', 'change_contribution', 'remove_member',
            'take_loan', 'withdraw', 'change_loan_terms',
            'enable_rotation', 'disable_rotation'
        ) NOT NULL");

        Schema::dropIfExists('chama_deals');
        Schema::dropIfExists('chama_share_holdings');
    }
};
