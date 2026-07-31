<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Solo play gets a companion bot ("Robo") so the board doesn't feel empty —
     * it's a real row in arcade_sessions (is_bot=true) sharing a private match
     * with the player, which lets it reuse every bit of opponent-panel/token
     * rendering built for real multiplayer instead of a special-cased UI path.
     * Its wallet is a fixed huge balance so stake/payout bookkeeping never
     * has to special-case "this session belongs to a bot".
     */
    public function up(): void
    {
        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->boolean('is_bot')->default(false)->after('user_id');
        });

        $botId = DB::table('users')->where('email', 'robo@bot.moski.internal')->value('id');
        if (!$botId) {
            $botId = DB::table('users')->insertGetId([
                'name'              => 'Robo',
                'email'             => 'robo@bot.moski.internal',
                'password'          => Hash::make(bin2hex(random_bytes(32))),
                'is_active'         => false,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        if (!DB::table('user_progress')->where('user_id', $botId)->exists()) {
            DB::table('user_progress')->insert([
                'user_id'    => $botId,
                'balance'    => 999999999,
                'level'      => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('user_progress')->where('user_id', $botId)->update(['balance' => 999999999]);
        }
    }

    public function down(): void
    {
        Schema::table('arcade_sessions', function (Blueprint $table) {
            $table->dropColumn('is_bot');
        });
        // Bot user intentionally left in place — other sessions/history may reference it.
    }
};
