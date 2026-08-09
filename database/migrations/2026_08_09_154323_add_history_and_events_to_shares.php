<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            $table->json('price_history')->nullable()->after('current_price');
            $table->string('last_event_reason', 160)->nullable()->after('drift');
        });
    }

    public function down(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            $table->dropColumn(['price_history', 'last_event_reason']);
        });
    }
};
