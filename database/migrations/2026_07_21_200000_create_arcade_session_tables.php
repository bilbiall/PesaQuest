<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Arcade play state: stake tiers (how much a level deposits into the
     * session pot), matches (an async lobby — public or join-code private —
     * other players can be seated in so their progress is visible to each
     * other; no real-time turn locking, everyone rolls on their own time),
     * and sessions (one row per player per game — solo sessions simply have
     * a null arcade_match_id).
     */
    public function up(): void
    {
        Schema::create('arcade_stake_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arcade_game_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40);
            $table->unsignedTinyInteger('level_min')->default(1);
            $table->unsignedTinyInteger('level_max')->default(99);
            $table->unsignedInteger('stake_amount');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('arcade_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arcade_game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('join_code', 8)->nullable()->unique();
            $table->enum('visibility', ['public', 'private'])->default('private');
            $table->unsignedTinyInteger('max_players')->default(4);
            $table->enum('status', ['open', 'active', 'completed'])->default('open');
            $table->timestamps();
        });

        Schema::create('arcade_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arcade_game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('arcade_match_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('stake_amount');
            $table->unsignedInteger('pot_amount');
            $table->unsignedTinyInteger('position')->default(0); // 0 = not yet on the board
            $table->enum('status', ['active', 'won', 'busted', 'cashed_out'])->default('active');
            $table->unsignedTinyInteger('last_roll')->nullable();
            $table->json('last_event')->nullable();  // most recent tile resolution, for the UI to animate/replay
            $table->json('session_assets')->nullable(); // golden-tile modifiers bought this session
            $table->unsignedInteger('xp_awarded')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['arcade_match_id', 'user_id']);
        });

        $now = now();
        $gameId = DB::table('arcade_games')->where('slug', 'snakes-and-cash')->value('id');
        if ($gameId) {
            DB::table('arcade_stake_tiers')->insert([
                ['arcade_game_id' => $gameId, 'label' => 'Starter (Lv 1-5)',   'level_min' => 1,  'level_max' => 5,  'stake_amount' => 200,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['arcade_game_id' => $gameId, 'label' => 'Grower (Lv 6-10)',   'level_min' => 6,  'level_max' => 10, 'stake_amount' => 500,  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['arcade_game_id' => $gameId, 'label' => 'Builder (Lv 11-15)', 'level_min' => 11, 'level_max' => 15, 'stake_amount' => 1000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['arcade_game_id' => $gameId, 'label' => 'Investor (Lv 16+)',  'level_min' => 16, 'level_max' => 99, 'stake_amount' => 2000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('arcade_sessions');
        Schema::dropIfExists('arcade_matches');
        Schema::dropIfExists('arcade_stake_tiers');
    }
};
