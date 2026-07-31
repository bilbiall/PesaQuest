<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('employer_name');
            $table->string('employer_logo', 10)->default('🏢');
            $table->string('career_track');
            $table->unsignedInteger('salary_kes_month');
            $table->unsignedInteger('level')->default(1);  // 1=entry, 2=mid, 3=senior
            $table->unsignedBigInteger('required_course_id')->nullable();
            $table->foreign('required_course_id')->references('id')->on('city_courses')->nullOnDelete();
            $table->string('age_group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_jobs');
    }
};
