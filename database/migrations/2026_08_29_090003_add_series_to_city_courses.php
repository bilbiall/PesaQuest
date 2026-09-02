<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()->after('id')
                ->constrained('course_series')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('series_id');
        });
    }

    public function down(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('series_id');
            $table->dropColumn('sort_order');
        });
    }
};
