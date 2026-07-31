<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Investment;
use App\Models\Node;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\UserStreak;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Admin ──────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@moski.org'],
            [
                'name'              => 'Moski Admin',
                'password'          => Hash::make('admin123'),
                'date_of_birth'     => '1990-01-01',
                'age_group'         => '26+',
                'is_admin'          => true,
                'is_gameset'        => true,
                'email_verified_at' => now(),
            ]
        );

        // ── 2. GameSet operator ───────────────────────────────────
        User::updateOrCreate(
            ['email' => 'gameset@moski.org'],
            [
                'name'              => 'Story Builder',
                'password'          => Hash::make('gameset123'),
                'date_of_birth'     => '1995-06-15',
                'age_group'         => '26+',
                'is_admin'          => false,
                'is_gameset'        => true,
                'email_verified_at' => now(),
            ]
        );

        // ── 3. Sample player (teen, active, with real progress) ───
        $player = User::updateOrCreate(
            ['email' => 'player@moski.org'],
            [
                'name'              => 'Amara Wanjiku',
                'password'          => Hash::make('player123'),
                'date_of_birth'     => '2010-03-22',   // age 14 → 13-17
                'age_group'         => '13-17',
                'is_admin'          => false,
                'is_gameset'        => false,
                'email_verified_at' => now(),
            ]
        );

        // Build realistic path_history using real node IDs if they exist
        $startNode  = Node::where('is_start', true)->first();
        $secondNode = Node::where('is_start', false)->first();

        $history = [];
        if ($startNode) {
            $history[] = ['node_id' => $startNode->id, 'choice_id' => null, 'points' => 20, 'at' => now()->subDays(5)->toDateTimeString()];
        }
        if ($secondNode) {
            $history[] = ['node_id' => $secondNode->id, 'choice_id' => null, 'points' => 15, 'at' => now()->subDays(4)->toDateTimeString()];
        }
        $history = array_merge($history, [
            ['node_id' => $startNode?->id ?? 1, 'choice_id' => null, 'points' => 25, 'at' => now()->subDays(3)->toDateTimeString()],
            ['node_id' => $startNode?->id ?? 1, 'choice_id' => null, 'points' => 20, 'at' => now()->subDays(2)->toDateTimeString()],
            ['node_id' => $startNode?->id ?? 1, 'choice_id' => null, 'points' => 15, 'at' => now()->subHours(3)->toDateTimeString()],
        ]);

        $progress = UserProgress::updateOrCreate(
            ['user_id' => $player->id],
            [
                'points_total'    => 750,
                'balance'         => 1200,
                'level'           => 4,
                'current_node_id' => $startNode?->id,
                'last_played_at'  => now()->subHours(3),
                'path_history'    => $history,
            ]
        );

        // Give player a 5-day streak
        UserStreak::updateOrCreate(
            ['user_id' => $player->id],
            [
                'current_streak'      => 5,
                'longest_streak'      => 7,
                'last_activity_date'  => today(),
                'bonus_points_earned' => 50,
            ]
        );

        // Award first 3 badges (if badges seeded)
        $badges = Badge::take(3)->get();
        foreach ($badges as $badge) {
            if (!$player->badges()->where('badge_id', $badge->id)->exists()) {
                $player->badges()->attach($badge->id, [
                    'earned_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }

        // Sample pending investment (matures in 2 days, so they see it in pending state)
        if ($progress->wasRecentlyCreated) {
            Investment::create([
                'user_id'     => $player->id,
                'amount'      => 5000,
                'return_rate' => 20,
                'return_days' => 2,
                'mature_at'   => now()->addDays(2),
                'label'       => 'Sample M-Pesa investment',
                'status'      => 'pending',
            ]);
        }
    }
}
