<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fun_world_activities', function (Blueprint $table) {
            $table->unsignedInteger('min_level')->default(1)->after('xp_reward');
        });
    }

    public function down(): void
    {
        Schema::table('fun_world_activities', function (Blueprint $table) {
            $table->dropColumn('min_level');
        });
    }
};
