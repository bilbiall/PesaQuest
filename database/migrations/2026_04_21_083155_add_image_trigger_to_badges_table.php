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
        Schema::table('badges', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('icon');
            $table->enum('trigger_type', [
                'level', 'points', 'decisions', 'streak',
                'investment', 'story_complete', 'manual',
            ])->default('level')->after('image_url');
            $table->integer('trigger_value')->default(1)->after('trigger_type');
            $table->boolean('is_active')->default(true)->after('trigger_value');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'trigger_type', 'trigger_value', 'is_active']);
        });
    }
};
