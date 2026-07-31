<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Personal contracts: procedurally generated, NPC-issued mini-quests
     * built from the player's OWN state — zero admin authoring required.
     *
     * contract_rules — admin-tunable generation recipe per age group + level
     *   band (how many objectives, all-or-any-N completion, duration, rewards,
     *   how many contracts a player holds). Seeded with sane defaults so the
     *   system runs untouched. Editable in GameSet → Quests → Automation.
     *
     * player_contracts + player_contract_objectives — the generated instances.
     *   Objectives are measured by BASELINE + DELTA against live metrics
     *   (courses completed, savings balance, paydays collected…), so no
     *   event wiring is needed and progress can never desync.
     *
     * quests.source — marks Quest Factory drafts ('factory') so they queue
     *   for approval in the quests admin.
     */
    public function up(): void
    {
        Schema::create('contract_rules', function (Blueprint $table) {
            $table->id();
            $table->string('age_group', 10)->default('all');            // 8-12 | 13-17 | 18-25 | 26+ | all
            $table->unsignedTinyInteger('level_min')->default(1);
            $table->unsignedTinyInteger('level_max')->default(99);
            $table->unsignedTinyInteger('objectives_min')->default(3);
            $table->unsignedTinyInteger('objectives_max')->default(4);
            $table->enum('completion_mode', ['all', 'any'])->default('any');
            $table->unsignedTinyInteger('required_count')->default(2);   // used when mode = any
            $table->unsignedSmallInteger('duration_days')->default(7);   // game days
            $table->unsignedTinyInteger('active_contracts')->default(2); // held at once per player
            $table->unsignedInteger('reward_xp')->default(150);
            $table->unsignedInteger('reward_kes')->default(200);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('player_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('npc_key', 30);
            $table->string('icon', 10)->default('🎯');
            $table->string('title', 150);
            $table->text('intro');
            $table->string('signoff', 300)->nullable();
            $table->enum('completion_mode', ['all', 'any'])->default('any');
            $table->unsignedTinyInteger('required_count')->default(2);
            $table->enum('status', ['active', 'completed', 'expired'])->default('active');
            $table->unsignedBigInteger('issued_at_tick');
            $table->unsignedBigInteger('expires_at_tick');
            $table->unsignedInteger('reward_xp')->default(0);
            $table->unsignedInteger('reward_kes')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('player_contract_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('player_contracts')->cascadeOnDelete();
            $table->string('archetype', 40);
            $table->string('metric', 40);
            $table->string('style', 20)->default('count');   // count | amount | absolute | clear
            $table->string('label', 220);
            $table->string('icon', 10)->default('✅');
            $table->string('lesson', 300)->nullable();
            $table->bigInteger('baseline')->default(0);
            $table->bigInteger('goal')->default(1);
            $table->bigInteger('progress')->default(0);
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
        });

        if (Schema::hasTable('quests') && !Schema::hasColumn('quests', 'source')) {
            Schema::table('quests', function (Blueprint $table) {
                $table->string('source', 20)->nullable()->after('is_active'); // 'factory' = auto-drafted
            });
        }

        // Default recipe — the game generates contracts out of the box.
        // Admins refine per age group/level in GameSet → Quests → Automation.
        $now = now();
        DB::table('contract_rules')->insert([
            ['age_group' => 'all', 'level_min' => 1,  'level_max' => 2,  'objectives_min' => 3, 'objectives_max' => 3, 'completion_mode' => 'any', 'required_count' => 2, 'duration_days' => 5, 'active_contracts' => 1, 'reward_xp' => 120, 'reward_kes' => 150, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['age_group' => 'all', 'level_min' => 3,  'level_max' => 5,  'objectives_min' => 3, 'objectives_max' => 4, 'completion_mode' => 'any', 'required_count' => 3, 'duration_days' => 6, 'active_contracts' => 2, 'reward_xp' => 220, 'reward_kes' => 300, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['age_group' => 'all', 'level_min' => 6,  'level_max' => 9,  'objectives_min' => 4, 'objectives_max' => 5, 'completion_mode' => 'all', 'required_count' => 4, 'duration_days' => 7, 'active_contracts' => 2, 'reward_xp' => 380, 'reward_kes' => 500, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['age_group' => 'all', 'level_min' => 10, 'level_max' => 99, 'objectives_min' => 5, 'objectives_max' => 5, 'completion_mode' => 'all', 'required_count' => 5, 'duration_days' => 7, 'active_contracts' => 2, 'reward_xp' => 550, 'reward_kes' => 750, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('player_contract_objectives');
        Schema::dropIfExists('player_contracts');
        Schema::dropIfExists('contract_rules');
        if (Schema::hasTable('quests') && Schema::hasColumn('quests', 'source')) {
            Schema::table('quests', fn (Blueprint $t) => $t->dropColumn('source'));
        }
    }
};
