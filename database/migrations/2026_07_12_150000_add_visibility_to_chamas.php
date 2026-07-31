<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Public vs private chamas. Public ones appear in the directory and are
     * joinable by anyone who clears the creator-set entry requirements
     * (min level / credit / savings — all optional, 0 = open to all).
     * Private ones are invisible: entry is by friend invite or the 6-char
     * join code only. Existing chamas stay public so nothing disappears.
     */
    public function up(): void
    {
        Schema::table('chamas', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'private'])->default('public')->after('status');
            $table->string('join_code', 8)->nullable()->unique()->after('visibility');
            $table->unsignedInteger('min_level')->default(0)->after('join_code');
            $table->unsignedInteger('min_credit_score')->default(0)->after('min_level');
            $table->unsignedInteger('min_savings')->default(0)->after('min_credit_score');
        });
    }

    public function down(): void
    {
        Schema::table('chamas', function (Blueprint $table) {
            $table->dropColumn(['visibility', 'join_code', 'min_level', 'min_credit_score', 'min_savings']);
        });
    }
};
