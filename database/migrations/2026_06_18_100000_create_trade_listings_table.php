<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('player_asset_id')->constrained('player_assets')->cascadeOnDelete();
            $table->unsignedBigInteger('asking_price');
            $table->enum('status', ['active', 'sold', 'cancelled'])->default('active');
            $table->foreignId('buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'seller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_listings');
    }
};
