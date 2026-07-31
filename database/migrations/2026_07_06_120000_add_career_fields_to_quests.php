<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional career-path targeting for quests — null/empty means "every
     * career path", a non-empty array of CareerService field keys means the
     * quest only appears to players who chose one of those paths.
     */
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->json('career_fields')->nullable()->after('age_group');
        });
    }

    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn('career_fields');
        });
    }
};
