<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->timestamp('last_bonus_at')->nullable()->after('node_unlocked_at');
        });
    }
    public function down(): void {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn('last_bonus_at');
        });
    }
};
