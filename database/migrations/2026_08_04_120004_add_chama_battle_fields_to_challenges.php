<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chama-vs-chama battles: an open broadcast challenge any chairman can enter
 * their whole chama into (ChallengeService::enrollChamaIntoBattle()).
 * `challenges.chama_id` stays null for these — no single chama owns the
 * battle — so each participant instead carries its OWN chama_id, letting
 * settle() group/rank by chama (average progress) rather than by individual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->boolean('is_chama_battle')->default(false)->after('is_team_based');
        });

        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->foreignId('chama_id')->nullable()->after('team_id')->constrained('chamas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('is_chama_battle');
        });

        Schema::table('challenge_participants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chama_id');
        });
    }
};
