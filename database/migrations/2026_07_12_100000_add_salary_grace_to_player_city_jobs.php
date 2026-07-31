<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Salary grace system: uncollected wages now STACK instead of being
     * forfeited. Discipline moves to attendance — each payday that lands on
     * top of an uncollected one counts as a missed collection. At 3
     * consecutive misses the employer issues a final notice
     * (removal_warned_at_tick); a further game month of silence ends in
     * dismissal (new status). Collecting pay resets both.
     */
    public function up(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->unsignedTinyInteger('missed_paydays')->default(0)->after('unpaid_ticks');
            $table->unsignedBigInteger('removal_warned_at_tick')->nullable()->after('missed_paydays');
        });

        // Widen the status enum: absentee workers can now be dismissed
        try {
            DB::statement("ALTER TABLE player_city_jobs MODIFY status ENUM('employed','resigned','completed','dismissed') NOT NULL DEFAULT 'employed'");
        } catch (\Throwable $e) {
            // Non-MySQL drivers (sqlite tests) treat enums as strings — nothing to widen
        }
    }

    public function down(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->dropColumn(['missed_paydays', 'removal_warned_at_tick']);
        });

        try {
            DB::statement("UPDATE player_city_jobs SET status = 'resigned' WHERE status = 'dismissed'");
            DB::statement("ALTER TABLE player_city_jobs MODIFY status ENUM('employed','resigned','completed') NOT NULL DEFAULT 'employed'");
        } catch (\Throwable $e) {
            // ignore on non-MySQL
        }
    }
};
