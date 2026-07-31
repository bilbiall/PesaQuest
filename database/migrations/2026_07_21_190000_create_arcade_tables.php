<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Arcade — quick filler mini-games that spend/pay out of a session pot
     * (not the player's real wallet directly; the pot is cashed out on finish
     * or forfeited on bust). Snakes & Cash is the first game: an 81-tile
     * board where each tile independently stacks money effect + movement
     * role + mystery/golden flags — a tile can be a reward AND a ladder
     * bottom AND nothing else, all three axes are configured separately.
     *
     * Resolution order for a landed tile: (1) apply its own money/mystery
     * effect, (2) if it's a ladder bottom or snake head, move to target_number,
     * (3) apply the target tile's own money effect too. One hop only — a
     * destination tile's own movement_role (if any) doesn't cascade further
     * in the same turn.
     */
    public function up(): void
    {
        Schema::create('arcade_games', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name', 60);
            $table->unsignedTinyInteger('tile_count');
            $table->unsignedTinyInteger('floor_percent')->default(15);        // session pot bust threshold, % of entry stake
            $table->unsignedTinyInteger('finish_bonus_percent')->default(10); // house-funded bonus on reaching the last tile
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('arcade_tiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arcade_game_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number');
            $table->enum('money_effect', ['none', 'reward', 'expense'])->default('none');
            $table->unsignedTinyInteger('money_percent')->nullable(); // % of current pot
            $table->enum('movement_role', ['none', 'ladder_bottom', 'snake_head'])->default('none');
            $table->unsignedTinyInteger('target_number')->nullable(); // where a ladder/snake sends you
            $table->boolean('is_mystery')->default(false);
            $table->boolean('is_golden')->default(false);
            $table->string('icon', 10)->nullable();
            $table->string('label', 120)->nullable();
            $table->timestamps();
            $table->unique(['arcade_game_id', 'number']);
        });

        Schema::create('arcade_mystery_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arcade_game_id')->constrained()->cascadeOnDelete();
            $table->string('label', 120);
            $table->enum('effect', ['gift', 'curse']);
            $table->unsignedTinyInteger('percent'); // % of current pot
            $table->unsignedTinyInteger('weight')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $gameId = DB::table('arcade_games')->insertGetId([
            'slug'                 => 'snakes-and-cash',
            'name'                 => 'Snakes & Cash',
            'tile_count'           => 81,
            'floor_percent'        => 15,
            'finish_bonus_percent' => 10,
            'is_active'            => true,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // ── Per-tile overrides, keyed by tile number. Every other number
        // from 1-81 defaults to a plain neutral tile. ─────────────────────
        $rewardSmall  = ['money_effect' => 'reward', 'money_percent' => 5,  'icon' => '🪙'];
        $rewardMedium = ['money_effect' => 'reward', 'money_percent' => 10, 'icon' => '💰'];
        $rewardLarge  = ['money_effect' => 'reward', 'money_percent' => 15, 'icon' => '💵'];
        $expense      = ['money_effect' => 'expense', 'money_percent' => 8, 'icon' => '💸'];
        $mystery      = ['is_mystery' => true, 'icon' => '❓'];
        $golden       = ['is_golden' => true, 'icon' => '🏆'];

        $overrides = [];
        foreach ([21, 48, 53] as $n) $overrides[$n] = $rewardSmall;
        foreach ([3, 17, 44, 50, 68, 77] as $n) $overrides[$n] = $rewardMedium;
        foreach ([13, 22, 30, 36, 39, 56, 64, 70] as $n) $overrides[$n] = $rewardLarge;
        foreach ([5, 8, 18, 23, 27, 28, 33, 45, 52, 63, 65, 75, 78] as $n) $overrides[$n] = $expense;
        foreach ([6, 34, 71] as $n) $overrides[$n] = $mystery;
        foreach ([10, 74] as $n) $overrides[$n] = $golden;

        // Combo tiles: money/mystery/golden effect merged with a movement role
        $overrides[60] = $mystery + ['movement_role' => 'ladder_bottom', 'target_number' => 74];
        $overrides[47] = $golden + ['movement_role' => 'ladder_bottom', 'target_number' => 77];
        $overrides[7]  = ['movement_role' => 'ladder_bottom', 'target_number' => 36, 'icon' => '🪜'];
        $overrides[25] = ['movement_role' => 'ladder_bottom', 'target_number' => 29, 'icon' => '🪜'];
        $overrides[38] = ['movement_role' => 'ladder_bottom', 'target_number' => 43, 'icon' => '🪜'];
        $overrides[31] = ['movement_role' => 'snake_head', 'target_number' => 23, 'icon' => '🐍'];
        $overrides[40] = ['movement_role' => 'snake_head', 'target_number' => 8,  'icon' => '🐍'];
        $overrides[66] = ['movement_role' => 'snake_head', 'target_number' => 43, 'icon' => '🐍'];
        $overrides[80] = ['movement_role' => 'snake_head', 'target_number' => 50, 'icon' => '🐍'];
        $overrides[81] = ['icon' => '🏁', 'label' => 'Finish — reach this tile to win'];

        $rows = [];
        for ($n = 1; $n <= 81; $n++) {
            $rows[] = array_merge([
                'arcade_game_id' => $gameId,
                'number'         => $n,
                'money_effect'   => 'none',
                'money_percent'  => null,
                'movement_role'  => 'none',
                'target_number'  => null,
                'is_mystery'     => false,
                'is_golden'      => false,
                'icon'           => null,
                'label'          => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ], $overrides[$n] ?? []);
        }
        DB::table('arcade_tiles')->insert($rows);

        // ── Starter mystery pool — gift/curse outcomes for any mystery tile ──
        DB::table('arcade_mystery_outcomes')->insert([
            ['arcade_game_id' => $gameId, 'label' => 'Found cash in an old coat',      'effect' => 'gift',  'percent' => 12, 'weight' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['arcade_game_id' => $gameId, 'label' => 'A relative sends you a gift',    'effect' => 'gift',  'percent' => 8,  'weight' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['arcade_game_id' => $gameId, 'label' => 'Side hustle pays off',           'effect' => 'gift',  'percent' => 18, 'weight' => 8,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['arcade_game_id' => $gameId, 'label' => 'Phone repair bill',              'effect' => 'curse', 'percent' => 10, 'weight' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['arcade_game_id' => $gameId, 'label' => 'Lost a bet with a friend',       'effect' => 'curse', 'percent' => 8,  'weight' => 15, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['arcade_game_id' => $gameId, 'label' => 'Unexpected transport fare hike', 'effect' => 'curse', 'percent' => 15, 'weight' => 8,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('arcade_mystery_outcomes');
        Schema::dropIfExists('arcade_tiles');
        Schema::dropIfExists('arcade_games');
    }
};
