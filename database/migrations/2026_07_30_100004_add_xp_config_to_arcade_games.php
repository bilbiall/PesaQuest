<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Admin-configurable XP knobs, added on top of the existing position-based
     *  curve in ArcadeSnakesService::awardXp() — not replacing it, just letting
     *  an admin tune a flat bonus for playing at all and an extra one for winning. */
    public function up(): void
    {
        Schema::table('arcade_games', function (Blueprint $table) {
            $table->unsignedSmallInteger('xp_per_play')->default(5)->after('finish_bonus_percent');
            $table->unsignedSmallInteger('xp_per_win')->default(20)->after('xp_per_play');
        });
    }

    public function down(): void
    {
        Schema::table('arcade_games', function (Blueprint $table) {
            $table->dropColumn(['xp_per_play', 'xp_per_win']);
        });
    }
};
