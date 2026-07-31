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
        Schema::create('chamas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('goal_text', 200)->nullable();
            $table->integer('target_amount')->default(0);
            $table->integer('monthly_contribution');
            $table->enum('status', ['forming', 'active', 'dissolved'])->default('forming');
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('max_members')->default(5);
            $table->integer('pool_balance')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chamas');
    }
};
