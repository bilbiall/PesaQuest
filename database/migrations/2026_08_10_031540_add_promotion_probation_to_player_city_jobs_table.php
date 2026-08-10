<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Makes the promotion_disqualified flag (added in the previous migration)
 * temporary instead of permanent: a job serves a probation window (one game
 * year, same length as the tenure requirement) after the disqualifying
 * incident, then automatically regains promotion eligibility if it stays
 * clean through to the end of that window — LifeSimulator::settlePromotions
 * does the actual clearing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->unsignedInteger('promotion_probation_until_tick')->nullable()->after('miss_incidents');
        });

        // Rows the previous migration already flagged as disqualified (mid-strike
        // at migration time) need a redemption point too, or they'd be stuck
        // disqualified forever with no probation clock ever started.
        DB::table('player_city_jobs')
            ->where('promotion_disqualified', true)
            ->whereNull('promotion_probation_until_tick')
            ->update([
                'promotion_probation_until_tick' => DB::raw('ticks_employed + 360'),
            ]);
    }

    public function down(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->dropColumn('promotion_probation_until_tick');
        });
    }
};
