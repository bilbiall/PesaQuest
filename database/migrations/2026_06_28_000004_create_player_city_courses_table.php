<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_city_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('city_course_id');
            $table->foreign('city_course_id')->references('id')->on('city_courses')->cascadeOnDelete();
            $table->enum('status', ['enrolled', 'completed'])->default('enrolled');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'city_course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_city_courses');
    }
};
