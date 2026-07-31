<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->timestamp('last_tick_at')->nullable()->after('last_bonus_at');
            $table->unsignedBigInteger('tick_count')->default(0)->after('last_tick_at');
            $table->unsignedInteger('credit_score')->default(500)->after('tick_count');
            $table->integer('net_worth_cache')->default(0)->after('credit_score');
            $table->string('life_chapter', 32)->nullable()->after('net_worth_cache');
        });
    }

    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn(['last_tick_at', 'tick_count', 'credit_score', 'net_worth_cache', 'life_chapter']);
        });
    }
};
