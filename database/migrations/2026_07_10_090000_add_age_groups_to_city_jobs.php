<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->json('age_groups')->nullable()->after('age_group');
        });
    }

    public function down(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->dropColumn('age_groups');
        });
    }
};
