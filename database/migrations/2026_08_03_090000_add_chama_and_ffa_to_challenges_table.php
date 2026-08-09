<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds chama-scoped challenges (mirrors school_subscription_id) and
 * free-for-all challenges. FFA reuses mode='duel' (same invite/accept flow)
 * with is_team_based=false, so ChallengeService::settle() falls back to the
 * individual-progress ranking branch instead of team-average — avoids an
 * ALTER on the `mode` enum column, which needs doctrine/dbal (not installed).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->foreignId('chama_id')->nullable()->after('school_subscription_id')->constrained()->nullOnDelete();
            $table->boolean('is_team_based')->default(true)->after('mode');
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chama_id');
            $table->dropColumn('is_team_based');
        });
    }
};
