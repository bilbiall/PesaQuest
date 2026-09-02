<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('mmf_min_rate', 5, 2)->nullable()->after('mmf_sponsor_id');
            $table->decimal('mmf_max_rate', 5, 2)->nullable()->after('mmf_min_rate');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['mmf_min_rate', 'mmf_max_rate']);
        });
    }
};
