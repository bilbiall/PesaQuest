<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Moving the snapshot cadence from once-daily to every 15 minutes means
     * multiple rows per participant per day, so the old
     * (challenge_participant_id, snapshot_date) unique upsert key no longer
     * makes sense — each run now INSERTs a fresh row. snapshot_at gives the
     * precise ordering the every-15-minutes trend comparison needs.
     */
    public function up(): void
    {
        // Add the column + its own index FIRST — MySQL needs some index
        // covering challenge_participant_id to support the FK constraint,
        // and chal_part_snap_unique was the only one until this exists.
        Schema::table('challenge_participant_snapshots', function (Blueprint $table) {
            $table->timestamp('snapshot_at')->nullable()->after('snapshot_date');
            $table->index(['challenge_participant_id', 'snapshot_at'], 'chal_part_snap_at_idx');
        });

        // Backfill existing rows so nothing has a null snapshot_at.
        \Illuminate\Support\Facades\DB::table('challenge_participant_snapshots')
            ->whereNull('snapshot_at')
            ->update(['snapshot_at' => \Illuminate\Support\Facades\DB::raw('created_at')]);

        Schema::table('challenge_participant_snapshots', function (Blueprint $table) {
            $table->dropUnique('chal_part_snap_unique');
        });
    }

    public function down(): void
    {
        // Re-add the old unique index first so it can support the FK before
        // the new one is dropped. Note: this will fail if 15-minute cadence
        // has already produced duplicate (challenge_participant_id,
        // snapshot_date) pairs — expected, since that's the whole point of
        // this migration; rolling back cleanly needs those duplicates pruned
        // first in that case.
        Schema::table('challenge_participant_snapshots', function (Blueprint $table) {
            $table->unique(['challenge_participant_id', 'snapshot_date'], 'chal_part_snap_unique');
        });

        Schema::table('challenge_participant_snapshots', function (Blueprint $table) {
            $table->dropIndex('chal_part_snap_at_idx');
            $table->dropColumn('snapshot_at');
        });
    }
};
