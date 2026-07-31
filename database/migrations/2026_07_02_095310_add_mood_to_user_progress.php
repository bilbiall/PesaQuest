<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->unsignedTinyInteger('mood')->default(70)->after('career_income_claimed_at');
            $table->timestamp('mood_last_boosted_at')->nullable()->after('mood');
        });
    }

    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn(['mood', 'mood_last_boosted_at']);
        });
    }
};
