<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-calibrated world-map district geometry — replaces the two
     * hardcoded, disagreeing position arrays previously duplicated in
     * world/index.blade.php (tap-area rectangles) and public/js/world.js
     * (player-pin destinations). Now there is one rectangle per district;
     * the pin destination is simply its center, computed on the fly.
     */
    public function up(): void
    {
        Schema::create('district_positions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->decimal('pos_left', 5, 2);
            $table->decimal('pos_top', 5, 2);
            $table->decimal('pos_width', 5, 2);
            $table->decimal('pos_height', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('district_positions');
    }
};
