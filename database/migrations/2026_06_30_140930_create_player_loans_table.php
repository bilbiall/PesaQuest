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
        Schema::create('player_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('loan_product_id');
            $table->unsignedInteger('principal');
            $table->decimal('annual_interest_rate', 5, 2);
            $table->unsignedInteger('outstanding_balance');
            $table->unsignedInteger('payment_amount');
            $table->unsignedSmallInteger('payment_period_ticks');
            $table->unsignedInteger('disbursed_at_tick');
            $table->unsignedInteger('due_at_tick');
            $table->unsignedInteger('next_payment_tick');
            $table->unsignedSmallInteger('payments_made')->default(0);
            $table->unsignedSmallInteger('payments_missed')->default(0);
            $table->enum('status', ['active', 'paid', 'defaulted'])->default('active');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('loan_product_id')->references('id')->on('loan_products')->onDelete('cascade');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_loans');
    }
};
