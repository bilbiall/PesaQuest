<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('spin_results')) return;
        Schema::create('spin_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('prize_label', 100);
            $table->string('prize_type', 30); // balance, credit, xp, badge, salary_2x
            $table->integer('prize_value');   // can be negative
            $table->string('prize_emoji', 10)->default('🎰');
            $table->string('prize_tier', 20)->default('good'); // great, good, bad
            $table->integer('balance_before')->default(0);
            $table->integer('balance_after')->nullable();
            $table->unsignedTinyInteger('segment_index'); // which wheel segment landed on
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spin_results');
    }
};
