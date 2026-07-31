<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('quests', function (Blueprint $table) {
            // 'all' = all triggers must fire, 'any' = first trigger completes quest
            $table->enum('trigger_mode', ['all','any'])->default('all')->after('triggers');
        });
        Schema::table('user_quests', function (Blueprint $table) {
            // JSON: {"0": true, "1": false} — tracks which trigger indexes are done
            $table->json('step_progress')->nullable()->after('completed_at');
        });
    }
    public function down(): void {
        Schema::table('quests', function (Blueprint $table) { $table->dropColumn('trigger_mode'); });
        Schema::table('user_quests', function (Blueprint $table) { $table->dropColumn('step_progress'); });
    }
};
