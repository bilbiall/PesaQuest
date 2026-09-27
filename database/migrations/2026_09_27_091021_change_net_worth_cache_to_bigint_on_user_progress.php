<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Signed INT tops out at 2,147,483,647 — a late-game net worth (salary can
// reach tens of millions/month) can cross that and every subsequent tick's
// UPDATE throws SQLSTATE[22003] "Out of range value for column
// 'net_worth_cache'", hard-failing Report to Work / the tick processor.
// bigInteger (signed, since net_worth_cache can go negative when debts
// exceed assets) covers the full range of realistic in-game net worth.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->bigInteger('net_worth_cache')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->integer('net_worth_cache')->default(0)->change();
        });
    }
};
