<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->unsignedInteger('banked_amount')->default(0)->after('pot_amount');
            $table->json('pending_decision')->nullable()->after('session_assets');
            $table->timestamp('decision_started_at')->nullable()->after('pending_decision');
        });
    }

    public function down(): void
    {
        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->dropColumn(['banked_amount', 'pending_decision', 'decision_started_at']);
        });
    }
};
