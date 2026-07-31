<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_loans', function (Blueprint $table) {
            // Set when a loan finances a specific asset purchase (Car Yard / Estates / Marketplace)
            $table->unsignedBigInteger('player_asset_id')->nullable()->after('loan_product_id');
            // Human label shown in bills & loan lists, e.g. "Toyota Fielder — Vehicle Loan"
            $table->string('label', 120)->nullable()->after('player_asset_id');

            $table->index('player_asset_id');
        });
    }

    public function down(): void
    {
        Schema::table('player_loans', function (Blueprint $table) {
            $table->dropIndex(['player_asset_id']);
            $table->dropColumn(['player_asset_id', 'label']);
        });
    }
};
