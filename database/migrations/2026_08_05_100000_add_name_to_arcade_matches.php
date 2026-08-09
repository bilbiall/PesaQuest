<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a host name a public match/round (e.g. "Friday Night Showdown")
     * so the lobby's browse lists show something more identifiable than a
     * bare numeric ID — optional, falls back to "Match #id"/"Round #id".
     */
    public function up(): void
    {
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->string('name', 40)->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('arcade_matches', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
