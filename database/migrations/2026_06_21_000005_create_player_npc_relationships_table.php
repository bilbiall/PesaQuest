<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('player_npc_relationships')) return;
        Schema::create('player_npc_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('npc_id')->constrained('npcs')->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->default(50); // 0–100
            $table->unsignedSmallInteger('total_interactions')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'npc_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_npc_relationships');
    }
};
