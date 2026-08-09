<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Challenges — fair PvP/team/broadcast competitions measured by BASELINE +
 * DELTA (same mechanism as player_contract_objectives), so existing wealth
 * never decides the winner. Two modes:
 *   - duel:      1v1 or team vs team, invite -> accept, player-created.
 *   - broadcast: publish once, anyone eligible (or a school roster) joins,
 *                ranked at the deadline. Used for "PesaCity Challenges"
 *                (is_official) and teacher-created Class Challenges
 *                (school_subscription_id set).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name', 120);
            $table->string('description', 300)->nullable();
            $table->string('metric', 40);            // net_worth | savings_balance | xp_points | courses_completed ...
            $table->string('style', 20)->default('percent'); // percent|amount|count
            $table->string('icon', 10)->default('🏆');
            $table->string('image_url')->nullable();
            $table->unsignedSmallInteger('default_duration_days')->default(7);
            $table->unsignedTinyInteger('level_min')->default(1);
            $table->unsignedTinyInteger('level_max')->default(99);
            $table->boolean('allow_player_created')->default(true);
            $table->boolean('allow_broadcast')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('challenge_templates')->nullOnDelete();
            $table->enum('mode', ['duel', 'broadcast'])->default('duel');
            $table->string('scope', 20)->default('open'); // open|school|friends
            $table->boolean('is_official')->default(false);
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('school_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->string('metric', 40);
            $table->string('style', 20)->default('percent');
            $table->decimal('goal', 12, 2)->default(0);
            $table->unsignedInteger('stake_amount')->nullable();
            $table->unsignedTinyInteger('level_min')->default(1);
            $table->unsignedTinyInteger('level_max')->default(99);
            // Real wall-clock deadlines, NOT game ticks — tick_count only advances
            // per-user on login/catchup (see GameClock), so it isn't comparable
            // across participants. Settlement runs off a real cron, not a visit.
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('active'); // pending|active|completed|cancelled
            $table->timestamps();

            $table->index(['mode', 'scope', 'status']);
        });

        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('team_id')->nullable();
            $table->string('status', 20)->default('invited'); // invited|accepted|declined
            $table->bigInteger('baseline')->default(0);
            $table->decimal('progress', 12, 2)->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->boolean('is_winner')->default(false);
            $table->boolean('stake_paid')->default(false);
            $table->dateTime('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['challenge_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_participants');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('challenge_templates');
    }
};
