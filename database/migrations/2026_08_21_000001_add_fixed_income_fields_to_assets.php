<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gives MMF/T-Bill/T-Bond a real identity instead of reusing the generic
     * appreciating-asset engine: a distinct marketplace category, a maturity
     * date, whether early exit is blocked or penalised, and a lump-sum bonus
     * for discount instruments (T-Bills) redeemed at maturity.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE assets MODIFY category ENUM(
            'vehicle', 'property', 'business', 'investment', 'gadget', 'fixed_income'
        ) NOT NULL");

        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('maturity_ticks')->nullable()->after('income_period_ticks');
            $table->boolean('locked')->default(false)->after('maturity_ticks');
            $table->decimal('early_exit_penalty_pct', 5, 2)->default(0)->after('locked');
            $table->decimal('maturity_bonus_pct', 6, 3)->default(0)->after('early_exit_penalty_pct');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['maturity_ticks', 'locked', 'early_exit_penalty_pct', 'maturity_bonus_pct']);
        });

        DB::statement("ALTER TABLE assets MODIFY category ENUM(
            'vehicle', 'property', 'business', 'investment', 'gadget'
        ) NOT NULL");
    }
};
