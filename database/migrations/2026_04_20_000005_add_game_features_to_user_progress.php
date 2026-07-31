<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('current_scenario_start_id')->nullable()->after('current_node_id');
            $table->unsignedInteger('consecutive_save_choices')->default(0)->after('last_bonus_at');
            $table->unsignedInteger('total_decisions')->default(0)->after('consecutive_save_choices');
        });
    }

    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn(['current_scenario_start_id', 'consecutive_save_choices', 'total_decisions']);
        });
    }
};
