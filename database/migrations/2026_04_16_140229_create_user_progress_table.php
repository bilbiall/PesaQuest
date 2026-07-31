<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('current_node_id')->nullable()->constrained('nodes')->onDelete('set null');
            $table->json('path_history')->nullable();
            $table->integer('points_total')->default(0);
            $table->integer('balance')->default(0);
            $table->integer('level')->default(1);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamp('node_unlocked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};
