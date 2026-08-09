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
        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scope_key', 40); // e.g. "global:xp:18-25" or "school:networth:3"
            $table->unsignedInteger('rank');
            $table->bigInteger('points');
            $table->date('snapshot_date');
            $table->timestamps();
            $table->unique(['user_id', 'scope_key', 'snapshot_date'], 'lb_snap_unique');
            $table->index(['scope_key', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_snapshots');
    }
};
