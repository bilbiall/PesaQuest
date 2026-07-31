<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jobs get a type (full_time | part_time | freelance) defined on the job
     * itself, and salaries switch to a check-in model: pay accrues as
     * pending_salary and is only banked when the player reports to work.
     */
    public function up(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->string('employment_type', 20)->default('full_time')->after('is_part_time');
        });

        // Backfill from the legacy boolean
        DB::table('city_jobs')->where('is_part_time', true)->update(['employment_type' => 'part_time']);

        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->unsignedInteger('pending_salary')->default(0)->after('ticks_employed');
            $table->unsignedInteger('unpaid_ticks')->default(0)->after('pending_salary');
            $table->unsignedBigInteger('gig_ends_tick')->nullable()->after('unpaid_ticks');
            $table->unsignedBigInteger('cooldown_until_tick')->nullable()->after('gig_ends_tick');
        });

        // Widen the enums: freelance gigs complete (status) and exist as a type
        try {
            DB::statement("ALTER TABLE player_city_jobs MODIFY status ENUM('employed','resigned','completed') NOT NULL DEFAULT 'employed'");
            DB::statement("ALTER TABLE player_city_jobs MODIFY employment_type ENUM('full_time','part_time','freelance') NOT NULL DEFAULT 'full_time'");
        } catch (\Throwable $e) {
            // Non-MySQL drivers (sqlite tests) treat enums as strings — nothing to widen
        }

        Schema::table('user_progress', function (Blueprint $table) {
            $table->unsignedInteger('pending_salary')->default(0)->after('career_income_rate');
        });
    }

    public function down(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->dropColumn('employment_type');
        });
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->dropColumn(['pending_salary', 'unpaid_ticks', 'gig_ends_tick', 'cooldown_until_tick']);
        });
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn('pending_salary');
        });
    }
};
