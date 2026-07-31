<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supports admin pause/resume: pausing freezes the countdown (records when),
 * resuming shifts ends_at forward by the paused duration so the player never
 * loses paid-for days. status='paused' is a new valid value for the existing
 * plain string `status` column — no schema change needed there.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('paused_at');
        });
    }
};
