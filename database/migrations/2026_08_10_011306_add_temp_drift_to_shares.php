<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            // A temporary nudge on top of the share's normal drift, consumed
            // by the regular step() cron over several real-time steps — how
            // a Market Watch bulletin trends a price gradually instead of
            // snapping it in one jump.
            $table->float('temp_drift')->default(0)->after('drift');
            $table->dateTime('temp_drift_expires_at')->nullable()->after('temp_drift');
        });
    }

    public function down(): void
    {
        Schema::table('shares', function (Blueprint $table) {
            $table->dropColumn(['temp_drift', 'temp_drift_expires_at']);
        });
    }
};
