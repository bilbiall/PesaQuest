<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->string('career_field', 50)->nullable()->after('total_decisions');
            $table->string('career_title', 100)->nullable()->after('career_field');
            $table->unsignedInteger('career_income_rate')->default(0)->after('career_title');
            $table->timestamp('career_income_claimed_at')->nullable()->after('career_income_rate');
            $table->json('active_loans')->nullable()->after('career_income_claimed_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn(['career_field','career_title','career_income_rate','career_income_claimed_at','active_loans']);
        });
    }
};
