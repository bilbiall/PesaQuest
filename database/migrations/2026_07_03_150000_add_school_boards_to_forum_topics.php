<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_topics', function (Blueprint $table) {
            // null = public forum; set = private board for that school's members
            $table->foreignId('school_subscription_id')->nullable()->after('user_id')
                ->constrained('school_subscriptions')->cascadeOnDelete();
            $table->boolean('is_challenge')->default(false)->after('is_locked');
            $table->string('posted_by_name', 80)->nullable()->after('is_challenge'); // teacher name on portal-posted challenges
            $table->index(['school_subscription_id', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::table('forum_topics', function (Blueprint $table) {
            $table->dropIndex(['school_subscription_id', 'last_activity_at']);
            $table->dropConstrainedForeignId('school_subscription_id');
            $table->dropColumn(['is_challenge', 'posted_by_name']);
        });
    }
};
