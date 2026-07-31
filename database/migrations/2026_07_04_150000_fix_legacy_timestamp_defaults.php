<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Several tables were created with TIMESTAMP NOT NULL columns and no
     * default. Under strict-mode MySQL (NO_ZERO_DATE — most cPanel hosts)
     * those carry an implicit invalid 0000-00-00 default, so ANY later
     * `ALTER TABLE` on them fails with "Invalid default value for '...'".
     * Convert them all to DATETIME once (same data, no zero-default quirk)
     * so future migrations never trip on this again.
     */
    public function up(): void
    {
        $fixes = [
            'user_badges'      => ['earned_at'],
            'subscriptions'    => ['starts_at'],
            'investments'      => ['mature_at'],
            'chama_proposals'  => ['expires_at'],
            'chama_votes'      => ['cast_at'],
            'player_event_log' => ['seen_at'],
        ];

        foreach ($fixes as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) continue;

            $mods = collect($columns)
                ->filter(fn ($c) => Schema::hasColumn($tableName, $c))
                ->map(fn ($c) => "MODIFY `{$c}` DATETIME NOT NULL")
                ->implode(', ');

            if ($mods === '') continue;

            try {
                DB::statement("ALTER TABLE `{$tableName}` {$mods}");
            } catch (\Throwable $e) {
                // Non-MySQL drivers, or column already converted — skip
            }
        }
    }

    public function down(): void
    {
        // Intentionally left as-is: DATETIME is strictly safer than the old
        // invalid-default TIMESTAMP definition; nothing to restore.
    }
};
