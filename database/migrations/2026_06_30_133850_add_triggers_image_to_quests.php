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
        Schema::table('quests', function (Blueprint $table) {
            if (!Schema::hasColumn('quests', 'triggers')) {
                // JSON array of trigger objects: [{type, values:[], label}]
                $table->json('triggers')->nullable()->after('trigger_label');
            }
            if (!Schema::hasColumn('quests', 'image')) {
                // Path to uploaded quest image (relative to storage/public)
                $table->string('image', 260)->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            foreach (['triggers', 'image'] as $col) {
                if (Schema::hasColumn('quests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
