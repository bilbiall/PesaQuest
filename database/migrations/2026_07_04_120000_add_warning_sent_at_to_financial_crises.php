<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Warnings and effects used to share the single is_processed flag, so a
     * crisis that sent its 48-hour warning was skipped by the effect pass and
     * never actually hit players. Warnings now track their own timestamp.
     */
    public function up(): void
    {
        // The table was created with TIMESTAMP NOT NULL columns (no default).
        // Under strict-mode MySQL (NO_ZERO_DATE) those carry an implicit
        // invalid 0000-00-00 default, which makes ANY later ALTER on this
        // table fail with "Invalid default value for 'active_from'".
        // Convert them to DATETIME once — same data, no zero-default quirk.
        try {
            DB::statement('ALTER TABLE `financial_crises`
                MODIFY `warning_at`   DATETIME NOT NULL,
                MODIFY `active_from`  DATETIME NOT NULL,
                MODIFY `active_until` DATETIME NOT NULL');
        } catch (\Throwable $e) {
            // Non-MySQL drivers: nothing to fix
        }

        Schema::table('financial_crises', function (Blueprint $table) {
            $table->dateTime('warning_sent_at')->nullable()->after('warning_at');
        });

        // Crises marked processed before their active window opened were only
        // warned, never applied — reset them so their effects can still fire.
        DB::table('financial_crises')
            ->where('is_processed', true)
            ->where('active_from', '>', now())
            ->update(['is_processed' => false, 'warning_sent_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('financial_crises', function (Blueprint $table) {
            $table->dropColumn('warning_sent_at');
        });
    }
};
