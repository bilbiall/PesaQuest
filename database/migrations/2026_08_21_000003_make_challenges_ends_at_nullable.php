<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * All-Time challenges (duration_days = 0) carry a null ends_at — no
     * deadline to ever hit. Raw ALTER (not Schema::table()->nullable(),
     * which needs doctrine/dbal to modify an existing column) matching the
     * pattern already used elsewhere in this project for enum/column widening.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE challenges MODIFY ends_at DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE challenges SET ends_at = DATE_ADD(starts_at, INTERVAL 7 DAY) WHERE ends_at IS NULL');
        DB::statement('ALTER TABLE challenges MODIFY ends_at DATETIME NOT NULL');
    }
};
