<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * player_event_log — tracks which world events (nodes) each player has seen.
     * Used by EventEngine::deduplicateRecent() to avoid repeating events within 14 game days.
     */
    public function up(): void
    {
        Schema::create('player_event_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');   // user_progress.user_id
            $table->unsignedBigInteger('node_id');      // nodes.id (the scenario shown)
            $table->unsignedBigInteger('choice_id')->nullable();  // choices.id (what they picked)
            $table->integer('delta')->default(0);       // KES balance change applied
            $table->timestamp('seen_at');

            $table->index(['player_id', 'seen_at']);
            $table->unique(['player_id', 'node_id']);   // one log entry per node per player (updateOrInsert)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_event_log');
    }
};
