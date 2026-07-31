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
        Schema::create('savings_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('target_amount', 12, 2);
            $table->decimal('current_amount', 12, 2)->default(0);
            $table->string('emoji')->default('💰');
            $table->string('color')->default('#10b981');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('savings_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('savings_schemes')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_deposits');
        Schema::dropIfExists('savings_schemes');
    }
};
