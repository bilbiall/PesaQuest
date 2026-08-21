<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spin_segments', function (Blueprint $table) {
            $table->unsignedInteger('min_level')->default(1)->after('tier');
        });
    }

    public function down(): void
    {
        Schema::table('spin_segments', function (Blueprint $table) {
            $table->dropColumn('min_level');
        });
    }
};
