<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * X-style forum voting: one vote (▲ +1 / ▼ −1) per player per topic or
     * reply, cached as `score` on the votable. Author reputation accumulates
     * as users.forum_karma (sum of votes received) — displayed on profiles
     * and usable as a badge trigger (trigger_type = forum_karma).
     */
    public function up(): void
    {
        Schema::create('forum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('votable_type', 10);              // 'topic' | 'reply'
            $table->unsignedBigInteger('votable_id');
            $table->tinyInteger('value');                     // +1 | -1
            $table->timestamps();

            $table->unique(['user_id', 'votable_type', 'votable_id']);
            $table->index(['votable_type', 'votable_id']);
        });

        Schema::table('forum_topics', function (Blueprint $table) {
            $table->integer('score')->default(0)->after('views');
        });

        Schema::table('forum_replies', function (Blueprint $table) {
            $table->integer('score')->default(0)->after('body');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('forum_karma')->default(0)->after('friend_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_votes');
        Schema::table('forum_topics', fn (Blueprint $t) => $t->dropColumn('score'));
        Schema::table('forum_replies', fn (Blueprint $t) => $t->dropColumn('score'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('forum_karma'));
    }
};
