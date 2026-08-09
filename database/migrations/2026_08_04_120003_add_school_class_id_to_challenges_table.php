<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a teacher-launched Class Challenge scope to one school_classes row
 * instead of always enrolling the whole school (see
 * ChallengeService::enrollSchoolRoster()). Null = whole school, matching
 * every Class Challenge created before this migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('school_subscription_id')->constrained('school_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_class_id');
        });
    }
};
