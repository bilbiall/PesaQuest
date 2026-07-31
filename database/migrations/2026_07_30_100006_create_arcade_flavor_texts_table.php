<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-editable pools of reward/expense flavor text — mirrors the
     * existing ArcadeMysteryOutcome pattern (which mystery tiles already use
     * for randomized gift/curse text). Reward/expense tiles previously only
     * had 4 hardcoded lessons each, picked deterministically by tile number
     * (ArcadeSnakesService::REWARD_LESSONS/EXPENSE_LESSONS) — the same tile
     * always showed the same line, every game, forever. This table lets an
     * admin grow the pool and have a genuinely random pick each landing.
     */
    public function up(): void
    {
        Schema::create('arcade_flavor_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arcade_game_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['reward', 'expense']);
            $table->string('text', 160);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed with the existing hardcoded lessons so the pool isn't empty on
        // upgrade — the game already showed these, this just makes them the
        // admin-editable, randomly-picked starting set instead of a fixed PHP array.
        $gameId = DB::table('arcade_games')->where('slug', 'snakes-and-cash')->value('id');
        if ($gameId) {
            $now = now();
            $reward = [
                'Consistent saving compounds — small wins add up.',
                'A side hustle payday — extra income is a buffer, not a lifestyle upgrade.',
                'Smart spending freed up cash for this.',
                'Patience paid off here — literally.',
            ];
            $expense = [
                'Unexpected costs happen — that\'s exactly why an emergency fund matters.',
                'Small leaks sink big ships — track where this went.',
                'A bill you didn\'t plan for — budgeting catches these before they catch you.',
                'This is the cost of not having a buffer ready.',
            ];
            $rows = [];
            foreach ($reward as $text) {
                $rows[] = ['arcade_game_id' => $gameId, 'category' => 'reward', 'text' => $text, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            }
            foreach ($expense as $text) {
                $rows[] = ['arcade_game_id' => $gameId, 'category' => 'expense', 'text' => $text, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            }
            DB::table('arcade_flavor_texts')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('arcade_flavor_texts');
    }
};
