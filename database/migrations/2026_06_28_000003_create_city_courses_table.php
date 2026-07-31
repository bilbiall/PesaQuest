<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon', 10)->default('📚');
            $table->string('career_track');         // tech | business | finance | creative
            $table->string('color', 20)->default('#4DA8F7');
            $table->unsignedInteger('cost_kes')->default(0);
            $table->boolean('is_free')->default(true);
            $table->unsignedInteger('duration_hours')->default(2);
            $table->string('outcome');              // what skill/label it unlocks
            $table->string('age_group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_courses');
    }
};
