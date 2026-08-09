<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dreams — expensive, purely cosmetic purchases players make as a mark of
 * success. Never resellable (no sell path is ever built) and never counted
 * toward net worth (would let players launder cash into net-worth-gated
 * unlocks). Owned dreams surface on the profile Trophy Case.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dreams', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name', 120);
            $table->string('tagline', 200)->nullable();
            $table->text('description')->nullable();
            $table->string('icon', 10)->default('🌟');
            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('price');
            $table->string('category', 20)->default('lifestyle'); // property|vehicle|travel|legacy|business|lifestyle
            $table->unsignedTinyInteger('min_level')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('player_dreams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dream_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price_paid');
            $table->timestamp('purchased_at');
            $table->timestamps();

            $table->unique(['user_id', 'dream_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_dreams');
        Schema::dropIfExists('dreams');
    }
};
