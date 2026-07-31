<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->string('financial_personality')->nullable()->after('consecutive_save_choices');
            $table->unsignedSmallInteger('last_assessment_at_decision')->default(0)->after('financial_personality');
        });
    }

    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn(['financial_personality', 'last_assessment_at_decision']);
        });
    }
};
