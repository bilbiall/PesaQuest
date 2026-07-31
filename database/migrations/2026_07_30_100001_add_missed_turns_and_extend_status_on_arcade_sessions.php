<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `missed_turns` counts consecutive auto-expired turns for a session —
     * resets to 0 on every roll, only ever incremented by the turn-expiry
     * path. At 8 it triggers a Rivals Trail forfeit.
     *
     * `status` gains two new terminal values, kept deliberately distinct so
     * the existing win-rate/best-pot stats query stays semantically honest:
     * `busted` already means "this player's OWN pot hit the floor" from tile
     * events; `forfeited` means they were withdrawn for missing turns, and
     * `lost` means someone ELSE won/attritted them out while they were still
     * active. Conflating any of these would misreport why a game ended.
     */
    public function up(): void
    {
        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->unsignedTinyInteger('missed_turns')->default(0)->after('turn_order');
        });

        DB::statement("ALTER TABLE arcade_sessions MODIFY status ENUM('active','won','busted','cashed_out','forfeited','lost') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE arcade_sessions MODIFY status ENUM('active','won','busted','cashed_out') NOT NULL DEFAULT 'active'");

        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->dropColumn('missed_turns');
        });
    }
};
