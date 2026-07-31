<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Turn-based matches originally hard-blocked a roll until the turn holder
     * acted, with no way to force it along if someone walked away. This adds a
     * clock: turn_started_at is stamped whenever the turn changes hands, and
     * ArcadeSnakesService::expireTurnIfNeeded() auto-passes the turn once
     * TURN_SECONDS have elapsed, checked lazily on every roll()/state() call
     * (no cron needed — the same lazy-check pattern already used elsewhere
     * in this project).
     */
    public function up(): void
    {
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->timestamp('turn_started_at')->nullable()->after('current_turn_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->dropColumn('turn_started_at');
        });
    }
};
