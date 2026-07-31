<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $badges = [
            // ── Achievement badges (level/XP gated) ──────────────────────────
            ['slug' => 'first-step',       'badge_type' => 'achievement', 'name' => 'First Step',       'description' => 'Completed your first decision.',          'icon' => '👣', 'required_level' => 1, 'required_points' => 0,    'color' => '#6366f1'],
            ['slug' => 'saver',            'badge_type' => 'achievement', 'name' => 'Saver',            'description' => 'Reached 100 points.',                     'icon' => '🐷', 'required_level' => 2, 'required_points' => 100,  'color' => '#10b981'],
            ['slug' => 'smart-spender',    'badge_type' => 'achievement', 'name' => 'Smart Spender',    'description' => 'Reached level 3.',                        'icon' => '💡', 'required_level' => 3, 'required_points' => 300,  'color' => '#f59e0b'],
            ['slug' => 'budget-master',    'badge_type' => 'achievement', 'name' => 'Budget Master',    'description' => 'Reached 600 points.',                     'icon' => '📊', 'required_level' => 4, 'required_points' => 600,  'color' => '#ec4899'],
            ['slug' => 'investor',         'badge_type' => 'achievement', 'name' => 'Investor',         'description' => 'Reached 1000 points.',                    'icon' => '📈', 'required_level' => 5, 'required_points' => 1000, 'color' => '#06b6d4'],
            ['slug' => 'wealth-builder',   'badge_type' => 'achievement', 'name' => 'Wealth Builder',   'description' => 'Reached 1500 points.',                    'icon' => '🏗️', 'required_level' => 6, 'required_points' => 1500, 'color' => '#8b5cf6'],
            ['slug' => 'pesa-champion',    'badge_type' => 'achievement', 'name' => 'Pesa Champion',    'description' => 'Reached 2100 points.',                    'icon' => '🏆', 'required_level' => 7, 'required_points' => 2100, 'color' => '#f97316'],
            ['slug' => 'financial-guru',   'badge_type' => 'achievement', 'name' => 'Financial Guru',   'description' => 'Reached 2800 points.',                    'icon' => '🧠', 'required_level' => 8, 'required_points' => 2800, 'color' => '#ef4444'],
            ['slug' => 'pesaquest-legend', 'badge_type' => 'achievement', 'name' => 'PesaQuest Legend', 'description' => 'Reached the pinnacle at 3600 points.',   'icon' => '⭐', 'required_level' => 9, 'required_points' => 3600, 'color' => '#facc15'],

            // ── Pesa City mission badges (awarded by MissionChecker) ──────────
            ['slug' => 'connected',    'badge_type' => 'mission', 'name' => 'Connected',    'description' => 'Bought your first device from Mama Mboga Market.',     'icon' => '📱', 'required_level' => 0, 'required_points' => 0, 'color' => '#15C77E'],
            ['slug' => 'scholar',      'badge_type' => 'mission', 'name' => 'Scholar',      'description' => 'Completed a course at the Opportunity Hub.',          'icon' => '🎓', 'required_level' => 0, 'required_points' => 0, 'color' => '#4DA8F7'],
            ['slug' => 'first-hustle', 'badge_type' => 'mission', 'name' => 'First Hustle', 'description' => 'Landed your first job in Pesa City. Salary incoming!', 'icon' => '💼', 'required_level' => 0, 'required_points' => 0, 'color' => '#FFBC00'],
        ];

        foreach ($badges as $badge) {
            \App\Models\Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}
