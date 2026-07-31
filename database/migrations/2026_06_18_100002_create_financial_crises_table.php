<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_crises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('icon')->default('⚠️');
            // Effect: 'investment_drop', 'bill_increase', 'salary_cut', 'balance_drain'
            $table->string('effect_type');
            $table->decimal('effect_amount', 8, 2);   // percentage or flat amount
            $table->boolean('is_percentage')->default(true);
            $table->timestamp('warning_at');           // 48hr warning notification
            $table->timestamp('active_from');
            $table->timestamp('active_until');
            $table->boolean('is_processed')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_crises');
    }
};
