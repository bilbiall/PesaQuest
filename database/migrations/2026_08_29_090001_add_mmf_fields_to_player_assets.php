<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_assets', function (Blueprint $table) {
            $table->unsignedInteger('mmf_principal')->nullable()->after('current_value');
            $table->unsignedInteger('mmf_interest_earned')->nullable()->after('mmf_principal');
            $table->unsignedInteger('mmf_interest_taxed')->nullable()->after('mmf_interest_earned');
            $table->unsignedInteger('mmf_last_interest_tick')->nullable()->after('mmf_interest_taxed');
            $table->unsignedInteger('mmf_pending_topup_amount')->default(0)->after('mmf_last_interest_tick');
            $table->unsignedInteger('mmf_topup_ready_tick')->nullable()->after('mmf_pending_topup_amount');
            $table->unsignedInteger('mmf_pending_withdrawal_amount')->default(0)->after('mmf_topup_ready_tick');
            $table->unsignedInteger('mmf_withdrawal_ready_tick')->nullable()->after('mmf_pending_withdrawal_amount');
        });
    }

    public function down(): void
    {
        Schema::table('player_assets', function (Blueprint $table) {
            $table->dropColumn([
                'mmf_principal', 'mmf_interest_earned', 'mmf_interest_taxed',
                'mmf_last_interest_tick', 'mmf_pending_topup_amount', 'mmf_topup_ready_tick',
                'mmf_pending_withdrawal_amount', 'mmf_withdrawal_ready_tick',
            ]);
        });
    }
};
