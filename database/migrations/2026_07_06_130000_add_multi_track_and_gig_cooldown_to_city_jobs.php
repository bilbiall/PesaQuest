<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->json('career_tracks')->nullable()->after('career_track');
            $table->unsignedInteger('gig_cooldown_ticks')->nullable()->after('employment_type');
        });
    }

    public function down(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->dropColumn(['career_tracks', 'gig_cooldown_ticks']);
        });
    }
};
