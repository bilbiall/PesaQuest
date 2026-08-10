<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cached reaction count per topic, mirroring how replies_count/score are
     * already cached — lets the activity-ranked feed sort without a join.
     */
    public function up(): void
    {
        Schema::table('forum_topics', function (Blueprint $table) {
            $table->unsignedInteger('reactions_count')->default(0)->after('replies_count');
        });

        if (Schema::hasTable('forum_reactions')) {
            DB::table('forum_topics')
                ->update(['reactions_count' => 0]);

            DB::table('forum_reactions')
                ->selectRaw('topic_id, COUNT(*) as c')
                ->groupBy('topic_id')
                ->get()
                ->each(function ($row) {
                    DB::table('forum_topics')->where('id', $row->topic_id)->update(['reactions_count' => $row->c]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('forum_topics', function (Blueprint $table) {
            $table->dropColumn('reactions_count');
        });
    }
};
