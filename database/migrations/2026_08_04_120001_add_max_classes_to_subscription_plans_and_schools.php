<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class-count cap, configured in the same two places `seats` already is:
 * the school plan template (what an org buys) and the individual school
 * subscription instance (what an admin can hand-tune per school).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_classes')->default(3)->after('seats');
        });

        Schema::table('school_subscriptions', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_classes')->default(3)->after('seats');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('max_classes');
        });

        Schema::table('school_subscriptions', function (Blueprint $table) {
            $table->dropColumn('max_classes');
        });
    }
};
