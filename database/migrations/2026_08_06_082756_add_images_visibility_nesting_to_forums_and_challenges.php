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
        Schema::table('forum_topics', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
            $table->enum('visibility', ['general', 'friends'])->default('general')->after('category');
        });

        Schema::table('forum_replies', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
            $table->foreignId('parent_id')->nullable()->after('topic_id')
                ->references('id')->on('forum_replies')->onDelete('cascade');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->json('requirements')->nullable()->after('goal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_topics', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'visibility']);
        });

        Schema::table('forum_replies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('image_path');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('requirements');
        });
    }
};
