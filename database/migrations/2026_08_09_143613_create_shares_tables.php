<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 12)->unique();
            $table->string('icon', 8)->default('📈');
            $table->string('sector', 40)->nullable();
            $table->decimal('current_price', 12, 2);
            $table->decimal('previous_price', 12, 2)->nullable();
            $table->decimal('min_price', 12, 2);
            $table->decimal('max_price', 12, 2);
            $table->float('volatility')->default(0.03);
            $table->float('drift')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('player_share_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('share_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('avg_cost', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'share_id']);
        });

        Schema::create('share_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('share_id')->constrained()->cascadeOnDelete();
            $table->string('action', 10);
            $table->unsignedInteger('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->decimal('profit_loss', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_trades');
        Schema::dropIfExists('player_share_holdings');
        Schema::dropIfExists('shares');
    }
};
