<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Backfill existing rows so old challenges get a shareable link too.
        foreach (\App\Models\Challenge::whereNull('slug')->get() as $challenge) {
            $challenge->update(['slug' => \App\Models\Challenge::freshSlug($challenge->title)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
