<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Flags the system reply that reveals a Market Watch outcome, so the
 *  thread can highlight it (pulsating outline) instead of matching it
 *  by author name/body text in the view. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_replies', function (Blueprint $table) {
            $table->boolean('is_market_update')->default(false)->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('forum_replies', function (Blueprint $table) {
            $table->dropColumn('is_market_update');
        });
    }
};
