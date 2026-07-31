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
        Schema::create('investment_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('description')->nullable();
            $table->string('category', 40)->default('general');
            $table->string('icon', 8)->default('💼');
            $table->unsignedInteger('cost');
            $table->decimal('min_return_pct', 6, 2)->default(0);
            $table->decimal('max_return_pct', 6, 2)->default(0);
            $table->decimal('loss_pct', 6, 2)->default(100);
            $table->decimal('success_probability', 5, 4)->default(0.5);
            $table->unsignedSmallInteger('maturity_ticks')->default(7);
            $table->unsignedTinyInteger('risk_level')->default(3);
            $table->text('lesson')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_deals');
    }
};
