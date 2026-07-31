<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_life_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('life_event_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('tick_triggered');
            $table->unsignedTinyInteger('game_age_at_trigger')->default(18);
            $table->string('chapter_at_trigger')->default('graduate');
            $table->json('effect_applied')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'tick_triggered']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_life_events');
    }
};
