<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE chama_proposals MODIFY type ENUM(
            'buy_asset', 'sell_asset', 'change_contribution', 'remove_member',
            'take_loan', 'withdraw', 'change_loan_terms'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE chama_proposals MODIFY type ENUM(
            'buy_asset', 'sell_asset', 'change_contribution', 'remove_member'
        ) NOT NULL");
    }
};
