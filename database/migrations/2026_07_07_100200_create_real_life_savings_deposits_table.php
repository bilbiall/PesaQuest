<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_life_savings_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('real_life_savings_goals')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('note', 150)->nullable();
            $table->date('deposited_on');
            $table->timestamps();

            $table->index('goal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_life_savings_deposits');
    }
};
