<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A challenge can optionally track a SECOND metric alongside the primary
     * one (e.g. net worth + XP points), tracked separately but shown together
     * in the same challenge. The primary metric alone still decides the
     * winner — the second metric is informational/parallel tracking, not a
     * combined win condition, to keep settlement logic unchanged.
     */
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('metric_2', 40)->nullable()->after('metric');
            $table->string('style_2', 20)->nullable()->after('style');
            $table->decimal('goal_2', 12, 2)->nullable()->after('goal');
        });

        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->bigInteger('baseline_2')->nullable()->after('baseline');
            $table->decimal('progress_2', 12, 2)->nullable()->after('progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn(['metric_2', 'style_2', 'goal_2']);
        });

        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->dropColumn(['baseline_2', 'progress_2']);
        });
    }
};
