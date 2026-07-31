<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->text('intro_content')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('city_courses', function (Blueprint $table) {
            $table->dropColumn('intro_content');
        });
    }
};
