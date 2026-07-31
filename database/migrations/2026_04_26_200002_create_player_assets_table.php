<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('purchase_price');
            $table->unsignedInteger('current_value');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('purchased_at_tick');
            $table->unsignedBigInteger('last_valued_tick')->nullable();
            $table->enum('status', ['active', 'sold', 'repossessed'])->default('active');
            $table->unsignedBigInteger('sold_at_tick')->nullable();
            $table->unsignedInteger('sold_price')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_assets');
    }
};
