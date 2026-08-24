<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a vague, no-numbers heads-up post (via the Forums, in the same
     * Market Watch category jitters already post to) a few game days before
     * a jitter actually fires — mirrors the Financial Crisis engine's
     * warning_at step. `warn_at` is resolved once, same as `scheduled_at`,
     * so it stays game-time-relative rather than a fixed real date.
     */
    public function up(): void
    {
        Schema::table('market_jitters', function (Blueprint $table) {
            $table->timestamp('warn_at')->nullable()->after('scheduled_at');
            $table->timestamp('warned_at')->nullable()->after('applied_at');
        });

        // Backfill the jitters already seeded before this column existed —
        // same GameClock formula (secondsPerTick = hours-per-week*3600/7)
        // used everywhere else, read directly since a migration shouldn't
        // depend on the app container. 3 game days' lead, same as new rows
        // going forward (see MarketJitterSeeder).
        $hoursPerWeek   = (float) (DB::table('settings')->where('key', 'game_clock_real_hours_per_game_week')->value('value') ?: 1.0);
        $secondsPerTick = ($hoursPerWeek * 3600) / 7;
        $leadSeconds    = (int) round(3 * $secondsPerTick);

        DB::table('market_jitters')
            ->where('status', 'scheduled')
            ->whereNull('warn_at')
            ->update(['warn_at' => DB::raw("DATE_SUB(scheduled_at, INTERVAL {$leadSeconds} SECOND)")]);
    }

    public function down(): void
    {
        Schema::table('market_jitters', function (Blueprint $table) {
            $table->dropColumn(['warn_at', 'warned_at']);
        });
    }
};
