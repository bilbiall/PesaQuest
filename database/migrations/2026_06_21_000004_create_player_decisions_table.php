<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('player_decisions')) return;
        Schema::create('player_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('decision_id')->constrained('life_decisions')->cascadeOnDelete();
            $table->foreignId('choice_id')->nullable()->constrained('life_decision_choices')->nullOnDelete();
            $table->integer('balance_before')->default(0);
            $table->integer('balance_after')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_decisions');
    }
};
