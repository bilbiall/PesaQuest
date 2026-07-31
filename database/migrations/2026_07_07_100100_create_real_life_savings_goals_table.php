<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real-life savings goals — the "external" counterpart to Pesa City's game
 * savings schemes. A player can hold several at once; each tracks its own
 * deposit history (see real_life_savings_deposits) so progress is auditable,
 * not just a running total. Real money, real dates, zero effect on the game.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_life_savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('icon', 10)->default('🎯');
            $table->unsignedInteger('target_amount');
            $table->date('target_date')->nullable();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_life_savings_goals');
    }
};
