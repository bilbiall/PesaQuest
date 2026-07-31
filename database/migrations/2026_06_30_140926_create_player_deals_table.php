<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deal_id');
            $table->unsignedInteger('amount_invested');
            $table->unsignedInteger('resolve_at_tick');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->integer('profit_loss')->default(0);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deal_id')->references('id')->on('investment_deals')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'resolve_at_tick']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_deals');
    }
};
