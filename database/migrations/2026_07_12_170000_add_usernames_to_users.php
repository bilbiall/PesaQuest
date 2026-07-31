<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Unique @usernames. Used everywhere a player is referenced publicly:
     * profile URLs (/players/{username}), friend adding, and share links —
     * so sequential numeric IDs never leak into anything shareable.
     * Format: starts with a letter, then letters/digits/underscores, 3–20
     * chars, lowercase. Existing users are backfilled from their name.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('name');
        });

        // Backfill every existing user with a valid, unique handle
        $taken = [];
        DB::table('users')->orderBy('id')->select('id', 'name')->chunkById(200, function ($users) use (&$taken) {
            foreach ($users as $u) {
                $base = strtolower(Str::slug($u->name ?? '', '_'));
                $base = preg_replace('/[^a-z0-9_]/', '', $base);
                if ($base === '' || !preg_match('/^[a-z]/', $base)) $base = 'player' . $base;
                $base = substr($base, 0, 20);
                if (strlen($base) < 3) $base = str_pad($base, 3, '0');

                $candidate = $base;
                $i = 1;
                while (isset($taken[$candidate]) || DB::table('users')->where('username', $candidate)->exists()) {
                    $suffix    = (string) (++$i);
                    $candidate = substr($base, 0, 20 - strlen($suffix)) . $suffix;
                }

                $taken[$candidate] = true;
                DB::table('users')->where('id', $u->id)->update(['username' => $candidate]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
