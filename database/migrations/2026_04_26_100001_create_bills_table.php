<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->enum('age_group', ['8-12', '13-17', '18-25', '26+', 'all']);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('flavor_text');
            $table->string('consequence_text');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('frequency_ticks');
            $table->string('category');
            $table->string('icon', 8)->default('📋');
            $table->boolean('is_essential')->default(false);
            $table->smallInteger('credit_impact_pay')->default(5);
            $table->smallInteger('credit_impact_miss')->default(-20);
            $table->boolean('auto_assign')->default(false);
            $table->string('trigger')->default('auto');
            $table->string('min_chapter')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
