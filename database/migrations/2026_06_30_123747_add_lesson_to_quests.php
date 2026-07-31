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
            if (!Schema::hasColumn('quests', 'lesson')) {
                $table->text('lesson')->nullable()->after('instructions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            if (Schema::hasColumn('quests', 'lesson')) {
                $table->dropColumn('lesson');
            }
        });
    }
};
