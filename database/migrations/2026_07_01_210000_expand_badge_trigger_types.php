<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change enum to varchar for flexibility — supports all existing and new types
        DB::statement("ALTER TABLE badges MODIFY COLUMN trigger_type VARCHAR(50) NOT NULL DEFAULT 'level'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE badges MODIFY COLUMN trigger_type ENUM('level','points','decisions','streak','investment','story_complete','manual') NOT NULL DEFAULT 'level'");
    }
};
