<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->unsignedInteger('xp_reward')->default(100)->after('salary_kes_month');
            $table->json('required_course_ids')->nullable()->after('required_course_id');
            $table->text('description')->nullable()->after('employer_logo');
            $table->boolean('is_part_time')->default(false)->after('is_active');
        });

        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->boolean('xp_awarded')->default(false)->after('status');
            $table->enum('employment_type', ['full_time', 'part_time'])->default('full_time')->after('xp_awarded');
        });
    }

    public function down(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->dropColumn(['xp_reward', 'required_course_ids', 'description', 'is_part_time']);
        });
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->dropColumn(['xp_awarded', 'employment_type']);
        });
    }
};
