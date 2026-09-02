<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->unsignedInteger('topic_number')->nullable()->after('series_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->dropIndex(['topic_number']);
            $table->dropColumn('topic_number');
        });
    }
};
