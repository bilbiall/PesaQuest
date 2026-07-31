<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Server-persisted version of the in-game Matumizi expense logger, but for
 * the player's REAL spending — so monthly "you spent X last month, Y this
 * month" comparisons are possible (the in-game tool is a stateless calculator
 * with no history). Never touches game balance/credit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('real_life_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('category', 30)->default('other');
            $table->string('note', 150)->nullable();
            $table->date('spent_on');
            $table->timestamps();

            $table->index(['user_id', 'spent_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('real_life_expenses');
    }
};
