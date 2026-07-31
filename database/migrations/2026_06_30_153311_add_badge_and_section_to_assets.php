<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Badge shown on the card (popular, trending, new, stable, risky)
            $table->string('badge', 20)->nullable()->after('is_luxury');
            // Groups asset into a named editorial section for the marketplace
            $table->string('featured_section', 40)->nullable()->after('badge');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['badge', 'featured_section']);
        });
    }
};
