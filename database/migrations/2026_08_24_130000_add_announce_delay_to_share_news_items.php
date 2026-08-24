<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Splits "price moves" from "forum finds out" into two separate moments —
 * previously both happened in the same resolveDue() pass, so the outcome
 * reply landed the instant the price changed. Now the reply waits until
 * announce_at, a few ticks after resolved_at, so the price has visibly
 * already moved by the time anyone reads the update (see ShareNewsService).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_news_items', function (Blueprint $table) {
            $table->dateTime('announce_at')->nullable()->after('resolved_at');
            $table->dateTime('announced_at')->nullable()->after('announce_at');
        });
    }

    public function down(): void
    {
        Schema::table('share_news_items', function (Blueprint $table) {
            $table->dropColumn(['announce_at', 'announced_at']);
        });
    }
};
