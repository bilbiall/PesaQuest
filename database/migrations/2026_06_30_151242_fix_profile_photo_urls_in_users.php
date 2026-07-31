<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert any absolute-URL stored photos to relative /storage/ paths.
        // Handles: http://domain.com/storage/profiles/... → /storage/profiles/...
        // Also handles: https://domain.com/storage/...
        DB::statement("
            UPDATE users
            SET profile_photo = CONCAT('/storage/', SUBSTRING_INDEX(profile_photo, '/storage/', -1))
            WHERE profile_photo LIKE 'http%/storage/%'
        ");

        DB::statement("
            UPDATE users
            SET cover_photo = CONCAT('/storage/', SUBSTRING_INDEX(cover_photo, '/storage/', -1))
            WHERE cover_photo LIKE 'http%/storage/%'
        ");
    }

    public function down(): void
    {
        // Not reversible — original URLs had wrong APP_URL baked in.
    }
};
