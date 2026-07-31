<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('life_decisions')) return;
        Schema::create('life_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->nullable()->constrained('npcs')->nullOnDelete();
            $table->string('title', 200);
            $table->text('body');
            $table->string('image_url', 500)->nullable();
            $table->string('category', 50)->default('social'); // social, career, emergency, opportunity, housing, market, family
            $table->string('icon', 10)->default('💬');
            $table->unsignedSmallInteger('weight')->default(10); // higher = more likely
            $table->unsignedSmallInteger('min_tick')->default(0);
            $table->unsignedSmallInteger('max_tick')->nullable();
            $table->integer('min_balance')->nullable();
            $table->integer('max_balance')->nullable();
            $table->json('required_career_fields')->nullable(); // ["technology"] or null = any
            $table->boolean('is_repeatable')->default(false);
            $table->unsignedSmallInteger('cooldown_ticks')->default(90);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('gameset_id')->nullable(); // loose ref, no FK constraint
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_decisions');
    }
};
