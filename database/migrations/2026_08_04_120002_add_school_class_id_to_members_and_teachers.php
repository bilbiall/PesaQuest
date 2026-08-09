<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assigns a student (school_members) or teacher (school_teachers) to a
 * school_classes row. Nullable both ways — unassigned means "whole school"
 * for backward compatibility with schools that never adopt classes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_members', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('school_subscription_id')->constrained('school_classes')->nullOnDelete();
        });

        Schema::table('school_teachers', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('school_subscription_id')->constrained('school_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_class_id');
        });

        Schema::table('school_teachers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_class_id');
        });
    }
};
