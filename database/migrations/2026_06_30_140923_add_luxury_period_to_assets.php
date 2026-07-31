<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedSmallInteger('income_period_ticks')->default(30)->after('monthly_cost');
            $table->boolean('is_luxury')->default(false)->after('is_active');
        });

        // Speed up existing income-generating assets: 30 ticks (monthly) → 7 ticks (weekly)
        DB::table('assets')->where('monthly_income', '>', 0)->update(['income_period_ticks' => 7]);
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['income_period_ticks', 'is_luxury']);
        });
    }
};
