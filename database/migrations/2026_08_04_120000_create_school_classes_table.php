<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A "class" (cohort) within a school subscription — lets a multi-teacher
 * school split its roster so each teacher's Class Challenges and evaluation
 * dashboard scope to their own students, not the whole school.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_subscription_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->foreignId('teacher_id')->nullable()->constrained('school_teachers')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_subscription_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
