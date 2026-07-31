<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('life_events', function (Blueprint $table) {
            $table->string('asset_category', 30)->nullable()->after('chapter')
                ->comment('If set, only fires for players who own an active asset of this category.');
            $table->unsignedSmallInteger('last_month_reported')->default(0)->after('is_active')
                ->comment('Unused on life_events — placeholder for future per-event cooldown.');
        });

        // Also add month-report tracking to user_progress
        Schema::table('user_progress', function (Blueprint $table) {
            $table->unsignedSmallInteger('last_month_report_tick')->default(0)->after('last_assessment_at_decision');
        });
    }

    public function down(): void
    {
        Schema::table('life_events', function (Blueprint $table) {
            $table->dropColumn(['asset_category', 'last_month_reported']);
        });
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn('last_month_report_tick');
        });
    }
};
