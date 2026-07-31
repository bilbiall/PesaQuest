<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ⚠️ cPanel TODO: run `php artisan migrate` after deploying
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->text('content')->nullable()->after('description');  // short lesson content (markdown/HTML)
            $table->string('difficulty')->default('beginner')->after('duration_hours'); // beginner | intermediate | advanced
            $table->unsignedInteger('xp_reward')->default(50)->after('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->dropColumn(['content', 'difficulty', 'xp_reward']);
        });
    }
};
