<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_schemes', function (Blueprint $table) {
            // Interest the bank has paid into this scheme so far (part of current_amount)
            $table->unsignedInteger('interest_earned')->default(0)->after('current_amount');
            // Game tick when interest was last accrued (set at creation, advanced monthly)
            $table->unsignedInteger('last_interest_tick')->nullable()->after('interest_earned');
        });

        Schema::table('savings_deposits', function (Blueprint $table) {
            // 'deposit' | 'withdrawal' | 'interest' — withdrawals store negative amounts
            $table->string('type', 16)->default('deposit')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('savings_schemes', function (Blueprint $table) {
            $table->dropColumn(['interest_earned', 'last_interest_tick']);
        });
        Schema::table('savings_deposits', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
