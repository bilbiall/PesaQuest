<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyChallenge;

class DailyChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $challenges = [
            // 8-12
            ['age_group' => '8-12', 'title' => 'Little Saver', 'description' => 'Make 3 save decisions in your journey today', 'challenge_type' => 'save_choices', 'target_value' => 3, 'xp_bonus' => 30],
            ['age_group' => '8-12', 'title' => 'Decision Maker', 'description' => 'Complete 5 decisions in the game', 'challenge_type' => 'make_decisions', 'target_value' => 5, 'xp_bonus' => 20],
            ['age_group' => '8-12', 'title' => 'Piggy Bank Hero', 'description' => 'Reach a balance of Ksh 200 in any scenario', 'challenge_type' => 'reach_balance', 'target_value' => 200, 'xp_bonus' => 40],

            // 13-17
            ['age_group' => '13-17', 'title' => 'Teen Hustler', 'description' => 'Earn Ksh 500 across all decisions today', 'challenge_type' => 'earn_ksh', 'target_value' => 500, 'xp_bonus' => 50],
            ['age_group' => '13-17', 'title' => 'Smart Choices', 'description' => 'Make 5 financially sound decisions', 'challenge_type' => 'save_choices', 'target_value' => 5, 'xp_bonus' => 40],
            ['age_group' => '13-17', 'title' => 'Goal Setter', 'description' => 'Reach a balance of Ksh 1,000 in any scenario', 'challenge_type' => 'reach_balance', 'target_value' => 1000, 'xp_bonus' => 60],
            ['age_group' => '13-17', 'title' => 'Story Explorer', 'description' => 'Complete 8 decisions today', 'challenge_type' => 'make_decisions', 'target_value' => 8, 'xp_bonus' => 35],

            // 18-25
            ['age_group' => '18-25', 'title' => 'Chama Starter', 'description' => 'Accumulate Ksh 2,000 balance today', 'challenge_type' => 'reach_balance', 'target_value' => 2000, 'xp_bonus' => 80],
            ['age_group' => '18-25', 'title' => 'Investment Ready', 'description' => 'Earn Ksh 1,000 across decisions today', 'challenge_type' => 'earn_ksh', 'target_value' => 1000, 'xp_bonus' => 70],
            ['age_group' => '18-25', 'title' => 'Budget Master', 'description' => 'Make 10 financial decisions today', 'challenge_type' => 'make_decisions', 'target_value' => 10, 'xp_bonus' => 50],
            ['age_group' => '18-25', 'title' => 'Savings Champion', 'description' => 'Choose savings options 6 times today', 'challenge_type' => 'save_choices', 'target_value' => 6, 'xp_bonus' => 60],

            // 26+
            ['age_group' => '26+', 'title' => 'Wealth Builder', 'description' => 'Reach Ksh 5,000 balance in any scenario', 'challenge_type' => 'reach_balance', 'target_value' => 5000, 'xp_bonus' => 100],
            ['age_group' => '26+', 'title' => 'Income Streams', 'description' => 'Earn Ksh 3,000 across decisions today', 'challenge_type' => 'earn_ksh', 'target_value' => 3000, 'xp_bonus' => 90],
            ['age_group' => '26+', 'title' => 'Financial Guru', 'description' => 'Make 15 financial decisions today', 'challenge_type' => 'make_decisions', 'target_value' => 15, 'xp_bonus' => 75],
            ['age_group' => '26+', 'title' => 'Wise Elder', 'description' => 'Choose wise financial options 8 times', 'challenge_type' => 'save_choices', 'target_value' => 8, 'xp_bonus' => 80],
        ];

        foreach ($challenges as $c) {
            DailyChallenge::firstOrCreate(
                ['title' => $c['title'], 'age_group' => $c['age_group']],
                array_merge($c, ['is_active' => true])
            );
        }
    }
}
