<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Distinguishes an auto-redeemed T-Bill/T-Bond at maturity from a
     * player-initiated sale, so portfolio history reads correctly.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE player_assets MODIFY status ENUM(
            'active', 'sold', 'repossessed', 'matured'
        ) NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE player_assets SET status = 'sold' WHERE status = 'matured'");
        DB::statement("ALTER TABLE player_assets MODIFY status ENUM(
            'active', 'sold', 'repossessed'
        ) NOT NULL DEFAULT 'active'");
    }
};
