<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('life_decision_choices')) return;
        Schema::create('life_decision_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained('life_decisions')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('label', 100);
            $table->string('description', 300)->nullable();
            $table->text('outcome_text');
            $table->text('financial_lesson')->nullable();
            $table->integer('balance_delta')->default(0);
            $table->tinyInteger('credit_score_delta')->default(0);
            $table->tinyInteger('relationship_delta')->default(0);
            $table->unsignedSmallInteger('xp_delta')->default(0);
            $table->string('badge_slug', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_decision_choices');
    }
};
