<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A second, independent set of tile coordinates for the forced-landscape
     * mobile layout — the desktop pos_left/pos_top calibration doesn't line up
     * once the board is CSS-rotated into landscape on a phone, because the
     * admin calibrator was never built with that rotated frame in mind.
     * Nullable and falls back to the desktop columns when unset, so existing
     * calibration isn't lost until an admin recalibrates for mobile.
     */
    public function up(): void
    {
        Schema::table('arcade_tiles', function (Blueprint $table) {
            $table->decimal('pos_left_mobile', 5, 2)->nullable()->after('pos_top');
            $table->decimal('pos_top_mobile', 5, 2)->nullable()->after('pos_left_mobile');
        });
    }

    public function down(): void
    {
        Schema::table('arcade_tiles', function (Blueprint $table) {
            $table->dropColumn(['pos_left_mobile', 'pos_top_mobile']);
        });
    }
};
