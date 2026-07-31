<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Display-name rename only — the slug ('snakes-and-cash'), route prefix,
     * and every file/class name stay put so no URL, bookmark, or internal
     * lookup breaks. Just the name players actually see changes.
     */
    public function up(): void
    {
        DB::table('arcade_games')->where('slug', 'snakes-and-cash')->update(['name' => 'Pesa Trail']);
    }

    public function down(): void
    {
        DB::table('arcade_games')->where('slug', 'snakes-and-cash')->update(['name' => 'Snakes & Cash']);
    }
};
