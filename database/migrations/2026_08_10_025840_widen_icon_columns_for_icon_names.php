<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // doctrine/dbal isn't installed, so Schema::table()->change() isn't
    // available here — raw ALTER TABLE instead, same pattern already used
    // elsewhere in this app's migrations for column-level tweaks.
    public function up(): void
    {
        // These were sized for a single emoji glyph (8-10 bytes); icon-name
        // strings like "shopping-bag" or "check-circle" need more room.
        DB::statement("ALTER TABLE assets MODIFY icon VARCHAR(30) NULL DEFAULT 'store'");
        DB::statement("ALTER TABLE quests MODIFY icon VARCHAR(30) NOT NULL DEFAULT 'target'");
        DB::statement("ALTER TABLE shares MODIFY icon VARCHAR(30) NULL");
        DB::statement("ALTER TABLE investment_deals MODIFY icon VARCHAR(30) NOT NULL DEFAULT 'briefcase'");
        // badges.icon is already an unrestricted string() column — no change needed.
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE assets MODIFY icon VARCHAR(8) NULL DEFAULT '📦'");
        DB::statement("ALTER TABLE quests MODIFY icon VARCHAR(10) NOT NULL DEFAULT '🎯'");
        DB::statement("ALTER TABLE shares MODIFY icon VARCHAR(8) NULL");
        DB::statement("ALTER TABLE investment_deals MODIFY icon VARCHAR(8) NOT NULL DEFAULT '💼'");
    }
};
