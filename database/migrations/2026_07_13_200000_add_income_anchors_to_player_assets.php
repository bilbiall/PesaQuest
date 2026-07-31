<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asset income was computed as floor(ticks_in_this_settle_window / period),
     * discarding the remainder each run — players who checked in often never
     * spanned a full period in one window, so their assets NEVER paid.
     *
     * These anchors make accrual persistent (same pattern as job unpaid_ticks):
     *   income_paid_to_tick — income has been paid up to this game day
     *   upkeep_paid_to_tick — condition/appreciation applied up to this day
     *
     * Null = not yet initialized; LifeSimulator backfills capped catch-up
     * (3 income periods / 1 upkeep month) on the first settle after deploy.
     */
    public function up(): void
    {
        Schema::table('player_assets', function (Blueprint $table) {
            $table->unsignedBigInteger('income_paid_to_tick')->nullable()->after('last_valued_tick');
            $table->unsignedBigInteger('upkeep_paid_to_tick')->nullable()->after('income_paid_to_tick');
        });
    }

    public function down(): void
    {
        Schema::table('player_assets', function (Blueprint $table) {
            $table->dropColumn(['income_paid_to_tick', 'upkeep_paid_to_tick']);
        });
    }
};
