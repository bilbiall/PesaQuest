<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamas', function (Blueprint $table) {
            $table->boolean('is_rotating')->default(false)->after('undistributed_gains');
            // Index into the active-members-by-joined_at order — whose turn is next.
            $table->unsignedInteger('rotation_index')->default(0)->after('is_rotating');
            // How many full loops through the order have completed — gates disable_rotation.
            $table->unsignedInteger('rotation_cycles_completed')->default(0)->after('rotation_index');
        });

        DB::statement("ALTER TABLE chama_proposals MODIFY type ENUM(
            'buy_asset', 'sell_asset', 'change_contribution', 'remove_member',
            'take_loan', 'withdraw', 'change_loan_terms',
            'enable_rotation', 'disable_rotation'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE chama_proposals MODIFY type ENUM(
            'buy_asset', 'sell_asset', 'change_contribution', 'remove_member',
            'take_loan', 'withdraw', 'change_loan_terms'
        ) NOT NULL");

        Schema::table('chamas', function (Blueprint $table) {
            $table->dropColumn(['is_rotating', 'rotation_index', 'rotation_cycles_completed']);
        });
    }
};
