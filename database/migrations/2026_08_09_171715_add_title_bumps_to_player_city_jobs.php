<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->unsignedTinyInteger('title_bumps')->default(0)->after('promotions_count');
        });
    }

    public function down(): void
    {
        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->dropColumn('title_bumps');
        });
    }
};
