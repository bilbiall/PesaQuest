<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Same physical board rows used by the tile-position formula this migration
     *  backfills from (see ArcadeSnakesController::tilePositions() pre-migration). */
    private const ROW_BOUNDS = [[1, 13], [14, 27], [28, 40], [41, 54], [55, 68], [69, 81]];
    private const ROW_PIXELS = [
        ['top' => 87.74, 'left' => 12.97, 'right' => 98.42],
        ['top' => 70.75, 'left' =>  7.44, 'right' => 97.15],
        ['top' => 54.72, 'left' =>  7.52, 'right' => 87.58],
        ['top' => 38.68, 'left' =>  5.14, 'right' => 96.44],
        ['top' => 23.11, 'left' => 10.68, 'right' => 98.10],
        ['top' =>  8.49, 'left' =>  6.65, 'right' => 94.54],
    ];

    /**
     * Two independent additions:
     *
     * 1. Turn-taking: a match can opt into `turn_mode = 'turns'` (default stays
     *    'free', preserving today's "everyone rolls whenever" behavior for solo
     *    bot races and casual matches). In turn mode, arcade_sessions.turn_order
     *    fixes seating order and arcade_matches.current_turn_session_id names
     *    whose roll it is — the service enforces it, the client polls it.
     *
     * 2. Per-tile board coordinates: pos_left/pos_top move tile placement from a
     *    computed-on-the-fly formula (accurate on average, wrong on some tiles)
     *    to admin-adjustable per-tile values, seeded here with that same
     *    formula's output so the board looks identical until an admin nudges a
     *    tile in the GameSet calibration UI.
     */
    public function up(): void
    {
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->enum('turn_mode', ['free', 'turns'])->default('free')->after('max_players');
            $table->foreignId('current_turn_session_id')->nullable()->after('turn_mode')
                ->constrained('arcade_sessions')->nullOnDelete();
        });

        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->unsignedTinyInteger('turn_order')->default(0)->after('is_bot');
        });

        Schema::table('arcade_tiles', function (Blueprint $table) {
            $table->decimal('pos_left', 5, 2)->nullable()->after('label');
            $table->decimal('pos_top', 5, 2)->nullable()->after('pos_left');
        });

        $gameId = DB::table('arcade_games')->where('slug', 'snakes-and-cash')->value('id');

        foreach (self::ROW_BOUNDS as $rowIndex => [$from, $to]) {
            $n = $to - $from + 1;
            ['top' => $topPercent, 'left' => $leftBound, 'right' => $rightBound] = self::ROW_PIXELS[$rowIndex];
            $leftToRight = $rowIndex % 2 === 0;
            for ($i = 0; $i < $n; $i++) {
                $number = $from + $i;
                $fraction = $n > 1 ? $i / ($n - 1) : 0.5;
                $leftPercent = $leftToRight
                    ? $leftBound + $fraction * ($rightBound - $leftBound)
                    : $rightBound - $fraction * ($rightBound - $leftBound);

                DB::table('arcade_tiles')->where('arcade_game_id', $gameId)->where('number', $number)->update([
                    'pos_left' => round($leftPercent, 2),
                    'pos_top'  => round($topPercent, 2),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('arcade_tiles', function (Blueprint $table) {
            $table->dropColumn(['pos_left', 'pos_top']);
        });
        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->dropColumn('turn_order');
        });
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_turn_session_id');
            $table->dropColumn('turn_mode');
        });
    }
};
