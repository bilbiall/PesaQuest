<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_asset_id')->constrained('player_assets')->cascadeOnDelete();
            $table->unsignedBigInteger('tick');
            $table->unsignedInteger('price');
            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['player_asset_id', 'tick']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_price_history');
    }
};
