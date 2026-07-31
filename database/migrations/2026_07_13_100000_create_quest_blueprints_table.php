<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Quest Blueprints — the quest printing press.
     *
     * A blueprint is a reusable quest RECIPE: which trigger(s) it composes
     * (save X, buy an asset, take any course + deposit Y…), which levels it
     * repeats across (level_min → level_max every level_step), and how its
     * numbers grow per level (value/reward curves). The nightly sweep walks
     * every active blueprint and prints one quest per level rung that doesn't
     * exist yet — so the quest ladder builds itself and stays gap-free.
     *
     * chain=true links consecutive rungs with a 'complete_quest' first step,
     * so the level-5 quest asks you to finish the level-3 one first: a story
     * arc across levels, generated, never hand-wired.
     *
     * steps JSON: [{type, value_mode: none|any|fixed|curve, value_fixed,
     *              value_base, value_per_level, label}]
     *
     * quests.blueprint_id + blueprint_slot form the coverage ledger: the sweep
     * only prints a rung once. Delete a printed quest and the next sweep
     * reprints it; retire the rung by deactivating/editing the blueprint.
     */
    public function up(): void
    {
        Schema::create('quest_blueprints', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                                  // admin-facing label
            $table->string('archetype', 40)->default('stash_it');         // pesa_voice archetype fronting the copy
            $table->string('icon', 10)->nullable();                       // overrides archetype icon
            $table->string('age_group', 10)->default('all');              // 8-12 | 13-17 | 18-25 | 26+ | all
            $table->json('career_fields')->nullable();                    // null = all career paths
            $table->unsignedTinyInteger('level_min')->default(1);
            $table->unsignedTinyInteger('level_max')->default(1);
            $table->unsignedTinyInteger('level_step')->default(1);        // print a rung every N levels
            $table->boolean('chain')->default(false);                     // rungs require the previous rung
            $table->json('steps');                                        // trigger recipe (see class docblock)
            $table->unsignedInteger('xp_base')->default(100);
            $table->unsignedInteger('xp_per_level')->default(25);
            $table->unsignedInteger('kes_base')->default(50);
            $table->unsignedInteger('kes_per_level')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('quests') && !Schema::hasColumn('quests', 'blueprint_id')) {
            Schema::table('quests', function (Blueprint $table) {
                $table->unsignedBigInteger('blueprint_id')->nullable()->after('source');
                $table->unsignedTinyInteger('blueprint_slot')->nullable()->after('blueprint_id'); // the level rung
                $table->index(['blueprint_id', 'blueprint_slot']);
            });
        }

        // ── Starter blueprints — the ladder prints itself out of the box ──
        // Money curves are per-rung values: base + per_level × (level − 1).
        $now  = now();
        $rows = [
            [
                'name'      => "Saver's Staircase",
                'archetype' => 'stash_it', 'icon' => '🏦', 'chain' => true,
                'level_min' => 1, 'level_max' => 9, 'level_step' => 2,
                'steps'     => [[ 'type' => 'reach_savings', 'value_mode' => 'curve', 'value_base' => 300, 'value_per_level' => 250 ]],
                'xp_base'   => 100, 'xp_per_level' => 40, 'kes_base' => 50, 'kes_per_level' => 25,
            ],
            [
                'name'      => 'Wallet Milestones',
                'archetype' => 'balance_builder', 'icon' => '💰', 'chain' => true,
                'level_min' => 2, 'level_max' => 10, 'level_step' => 2,
                'steps'     => [[ 'type' => 'reach_balance', 'value_mode' => 'curve', 'value_base' => 500, 'value_per_level' => 400 ]],
                'xp_base'   => 120, 'xp_per_level' => 45, 'kes_base' => 60, 'kes_per_level' => 30,
            ],
            [
                'name'      => 'Net Worth Ladder',
                'archetype' => 'worth_climb', 'icon' => '📈', 'chain' => true,
                'level_min' => 3, 'level_max' => 12, 'level_step' => 3,
                'steps'     => [[ 'type' => 'reach_net_worth', 'value_mode' => 'curve', 'value_base' => 1500, 'value_per_level' => 900 ]],
                'xp_base'   => 180, 'xp_per_level' => 60, 'kes_base' => 100, 'kes_per_level' => 50,
            ],
            [
                // The combo showcase: learn something, then put money where the lesson is
                'name'      => 'Learn & Stash',
                'archetype' => 'stash_it', 'icon' => '📚', 'chain' => false,
                'level_min' => 2, 'level_max' => 8, 'level_step' => 3,
                'steps'     => [
                    [ 'type' => 'take_course',     'value_mode' => 'any' ],
                    [ 'type' => 'deposit_savings', 'value_mode' => 'curve', 'value_base' => 200, 'value_per_level' => 100 ],
                ],
                'xp_base'   => 160, 'xp_per_level' => 50, 'kes_base' => 80, 'kes_per_level' => 40,
            ],
            [
                // Job + cash combo: income first, then hold onto it
                'name'      => 'Earn & Bank',
                'archetype' => 'balance_builder', 'icon' => '💼', 'chain' => false,
                'level_min' => 2, 'level_max' => 6, 'level_step' => 2,
                'steps'     => [
                    [ 'type' => 'get_job',       'value_mode' => 'any' ],
                    [ 'type' => 'reach_balance', 'value_mode' => 'curve', 'value_base' => 400, 'value_per_level' => 300 ],
                ],
                'xp_base'   => 150, 'xp_per_level' => 50, 'kes_base' => 75, 'kes_per_level' => 35,
            ],
            [
                'name'      => 'Asset Collector',
                'archetype' => 'first_brick', 'icon' => '🏗️', 'chain' => true,
                'level_min' => 4, 'level_max' => 10, 'level_step' => 3,
                'steps'     => [[ 'type' => 'buy_item_category', 'value_mode' => 'any' ]],
                'xp_base'   => 200, 'xp_per_level' => 60, 'kes_base' => 100, 'kes_per_level' => 40,
            ],
            [
                'name'      => 'Open For Business',
                'archetype' => 'open_account', 'icon' => '🔑', 'chain' => false,
                'level_min' => 1, 'level_max' => 1, 'level_step' => 1,
                'steps'     => [[ 'type' => 'open_savings', 'value_mode' => 'none' ]],
                'xp_base'   => 80, 'xp_per_level' => 0, 'kes_base' => 40, 'kes_per_level' => 0,
            ],
            [
                'name'      => 'The Circle',
                'archetype' => 'circle_up', 'icon' => '🤝', 'chain' => false,
                'level_min' => 3, 'level_max' => 3, 'level_step' => 1,
                'steps'     => [[ 'type' => 'join_chama', 'value_mode' => 'none' ]],
                'xp_base'   => 120, 'xp_per_level' => 0, 'kes_base' => 60, 'kes_per_level' => 0,
            ],
            [
                'name'      => 'Lucky Break',
                'archetype' => 'lucky_spin', 'icon' => '🎡', 'chain' => false,
                'level_min' => 1, 'level_max' => 1, 'level_step' => 1,
                'steps'     => [[ 'type' => 'spin_wheel', 'value_mode' => 'none' ]],
                'xp_base'   => 60, 'xp_per_level' => 0, 'kes_base' => 30, 'kes_per_level' => 0,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('quest_blueprints')->insert([
                'name'          => $row['name'],
                'archetype'     => $row['archetype'],
                'icon'          => $row['icon'],
                'age_group'     => 'all',
                'career_fields' => null,
                'level_min'     => $row['level_min'],
                'level_max'     => $row['level_max'],
                'level_step'    => $row['level_step'],
                'chain'         => $row['chain'],
                'steps'         => json_encode($row['steps']),
                'xp_base'       => $row['xp_base'],
                'xp_per_level'  => $row['xp_per_level'],
                'kes_base'      => $row['kes_base'],
                'kes_per_level' => $row['kes_per_level'],
                'is_active'     => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('quests') && Schema::hasColumn('quests', 'blueprint_id')) {
            Schema::table('quests', function (Blueprint $table) {
                $table->dropIndex(['blueprint_id', 'blueprint_slot']);
                $table->dropColumn(['blueprint_id', 'blueprint_slot']);
            });
        }
        Schema::dropIfExists('quest_blueprints');
    }
};
