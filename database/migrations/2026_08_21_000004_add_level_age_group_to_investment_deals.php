<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_deals', function (Blueprint $table) {
            $table->unsignedInteger('min_level')->default(1)->after('risk_level');
            $table->string('age_group', 10)->nullable()->default('all')->after('min_level');
        });
    }

    public function down(): void
    {
        Schema::table('investment_deals', function (Blueprint $table) {
            $table->dropColumn(['min_level', 'age_group']);
        });
    }
};
