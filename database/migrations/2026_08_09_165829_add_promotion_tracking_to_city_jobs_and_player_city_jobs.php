<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->foreignId('promotes_to_job_id')->nullable()->after('level')
                ->constrained('city_jobs')->nullOnDelete();
        });

        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->decimal('salary_multiplier', 6, 3)->default(1.000)->after('missed_paydays');
            $table->unsignedInteger('ticks_employed_at_last_review')->default(0)->after('salary_multiplier');
            $table->unsignedInteger('promotions_count')->default(0)->after('ticks_employed_at_last_review');
        });
    }

    public function down(): void
    {
        Schema::table('city_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotes_to_job_id');
        });

        Schema::table('player_city_jobs', function (Blueprint $table) {
            $table->dropColumn(['salary_multiplier', 'ticks_employed_at_last_review', 'promotions_count']);
        });
    }
};
