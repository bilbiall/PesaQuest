<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * New promotion rule: a job is permanently disqualified from future title
 * promotions (raises are unaffected) the moment it either (a) racks up 2
 * missed paydays in one unresolved stretch, or (b) starts a second separate
 * miss-incident after an earlier one was cleared via Report to Work. Once
 * set, promotion_disqualified never clears — it's a "has this ever
 * happened" flag, not a current-status snapshot like missed_paydays.
 *
 * Backfill is intentionally conservative: we only have the CURRENT
 * missed_paydays snapshot, not real incident history, so existing rows
 * default to "never disqualified" unless they are mid-strike right now
 * (missed_paydays >= 2) — that's the one case where the existing signal is
 * strong enough to act on. This does NOT touch promotions_count,
 * salary_multiplier, or city_job_id, so players already promoted under the
 * old logic keep everything they've earned; the new rule only gates
 * promotions from this point forward.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->boolean('promotion_disqualified')->default(false)->after('promotions_count');
            $table->unsignedInteger('miss_incidents')->default(0)->after('promotion_disqualified');
        });

        DB::table('player_city_jobs')
            ->where('missed_paydays', '>=', 2)
            ->update(['promotion_disqualified' => true, 'miss_incidents' => 1]);
    }

    public function down(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->dropColumn(['promotion_disqualified', 'miss_incidents']);
        });
    }
};
