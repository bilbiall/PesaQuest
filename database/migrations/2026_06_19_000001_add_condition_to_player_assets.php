<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_assets', function (Blueprint $table) {
            $table->tinyInteger('condition')->unsigned()->default(100)->after('sold_price')
                ->comment('Asset health 0-100. Degrades 3pts/game-month. Below 70 reduces income.');
        });
    }

    public function down(): void
    {
        Schema::table('player_assets', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
    }
};
