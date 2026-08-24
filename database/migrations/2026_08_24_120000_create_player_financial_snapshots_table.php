<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per player per game-day the simulator actually advanced them
     * to (not one per real login, and not backfilled for skipped days) --
     * enough to answer "what were balance/net worth around N game-days
     * ago" via the closest snapshot at-or-before that tick. See
     * LifeSimulator::processLogin(), written right after tick settlement.
     */
    public function up(): void
    {
        Schema::create('player_financial_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tick');
            $table->bigInteger('balance');
            $table->bigInteger('net_worth');
            $table->timestamp('recorded_at')->useCurrent();
            $table->unique(['user_id', 'tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_financial_snapshots');
    }
};
