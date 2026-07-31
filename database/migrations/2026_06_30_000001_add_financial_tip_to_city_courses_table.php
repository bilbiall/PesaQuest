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
            $table->text('financial_tip')->nullable()->after('outcome');
            $table->string('jobs_intro', 300)->nullable()->after('financial_tip');
        });
    }

    public function down(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->dropColumn(['financial_tip', 'jobs_intro']);
        });
    }
};
