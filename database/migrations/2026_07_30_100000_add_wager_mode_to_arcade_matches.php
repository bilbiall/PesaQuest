<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Rivals Trail" — a head-to-head money mode alongside the existing
     * race-yourself play. `mode` is the single switch every wager-specific
     * code path branches on; `stake_amount` is the match-level AGREED entry
     * amount (set once by the initiator, validated against every joiner's
     * own arcade_sessions.stake_amount); `forfeit_pool_amount` holds 30% cuts
     * from forfeited players until the whole pool is paid to the eventual
     * winner — it has to live at the match level since it must survive
     * across multiple players' session lifecycles and be paid out once.
     */
    public function up(): void
    {
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->enum('mode', ['standard', 'wager'])->default('standard')->after('visibility');
            $table->unsignedInteger('stake_amount')->nullable()->after('mode');
            $table->unsignedInteger('forfeit_pool_amount')->default(0)->after('stake_amount');
        });
    }

    public function down(): void
    {
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->dropColumn(['mode', 'stake_amount', 'forfeit_pool_amount']);
        });
    }
};
