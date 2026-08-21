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
            // ── Achievement badges (level gated) ─────────────────────────────
            // trigger_type/trigger_value are set explicitly on every row below —
            // leaving them out lets the `badges` table's column default
            // (trigger_type='level', trigger_value=1) apply instead, which is
            // the exact bug that made every one of these award at level 1
            // regardless of its intended required_level.
            ['slug' => 'first-step',       'badge_type' => 'achievement', 'name' => 'First Step',       'description' => 'Completed your first decision.',          'icon' => '👣', 'required_level' => 1,  'required_points' => 0,    'trigger_type' => 'level', 'trigger_value' => 1,  'color' => '#6366f1'],
            ['slug' => 'saver',            'badge_type' => 'achievement', 'name' => 'Saver',            'description' => 'Reached 100 points.',                     'icon' => '🐷', 'required_level' => 2,  'required_points' => 100,  'trigger_type' => 'level', 'trigger_value' => 2,  'color' => '#10b981'],
            ['slug' => 'smart-spender',    'badge_type' => 'achievement', 'name' => 'Smart Spender',    'description' => 'Reached level 3.',                        'icon' => '💡', 'required_level' => 3,  'required_points' => 300,  'trigger_type' => 'level', 'trigger_value' => 3,  'color' => '#f59e0b'],
            ['slug' => 'budget-master',    'badge_type' => 'achievement', 'name' => 'Budget Master',    'description' => 'Reached 600 points.',                     'icon' => '📊', 'required_level' => 4,  'required_points' => 600,  'trigger_type' => 'level', 'trigger_value' => 4,  'color' => '#ec4899'],
            ['slug' => 'investor',         'badge_type' => 'achievement', 'name' => 'Investor',         'description' => 'Reached 1000 points.',                    'icon' => '📈', 'required_level' => 5,  'required_points' => 1000, 'trigger_type' => 'level', 'trigger_value' => 5,  'color' => '#06b6d4'],
            ['slug' => 'wealth-builder',   'badge_type' => 'achievement', 'name' => 'Wealth Builder',   'description' => 'Reached 1500 points.',                    'icon' => '🏗️', 'required_level' => 6,  'required_points' => 1500, 'trigger_type' => 'level', 'trigger_value' => 6,  'color' => '#8b5cf6'],
            ['slug' => 'pesa-champion',    'badge_type' => 'achievement', 'name' => 'Pesa Champion',    'description' => 'Reached 2100 points.',                    'icon' => '🏆', 'required_level' => 7,  'required_points' => 2100, 'trigger_type' => 'level', 'trigger_value' => 7,  'color' => '#f97316'],
            ['slug' => 'financial-guru',   'badge_type' => 'achievement', 'name' => 'Financial Guru',   'description' => 'Reached 2800 points.',                    'icon' => '🧠', 'required_level' => 8,  'required_points' => 2800, 'trigger_type' => 'level', 'trigger_value' => 8,  'color' => '#ef4444'],
            ['slug' => 'pesaquest-legend', 'badge_type' => 'achievement', 'name' => 'PesaQuest Legend', 'description' => 'Reached the pinnacle at 3600 points.',   'icon' => '⭐', 'required_level' => 9,  'required_points' => 3600, 'trigger_type' => 'level', 'trigger_value' => 9,  'color' => '#facc15'],
            ['slug' => 'pesa-city-icon',   'badge_type' => 'achievement', 'name' => 'Pesa City Icon',   'description' => 'Reached the very top — level 10.',       'icon' => '🌆', 'required_level' => 10, 'required_points' => 4500, 'trigger_type' => 'level', 'trigger_value' => 10, 'color' => '#22d3ee'],

            // ── Pesa City mission badges (awarded by MissionChecker) ──────────
            ['slug' => 'connected',    'badge_type' => 'mission', 'name' => 'Connected',    'description' => 'Bought your first device from Mama Mboga Market.',     'icon' => '📱', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'manual', 'trigger_value' => 0, 'color' => '#15C77E'],
            ['slug' => 'scholar',      'badge_type' => 'mission', 'name' => 'Scholar',      'description' => 'Completed a course at the Opportunity Hub.',          'icon' => '🎓', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'manual', 'trigger_value' => 0, 'color' => '#4DA8F7'],
            ['slug' => 'first-hustle', 'badge_type' => 'mission', 'name' => 'First Hustle', 'description' => 'Landed your first job in Pesa City. Salary incoming!', 'icon' => '💼', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'manual', 'trigger_value' => 0, 'color' => '#FFBC00'],

            // ── Non-level badges — GameController::checkAndAwardBadges() has
            // supported all these trigger types for a while, but nothing had
            // ever been seeded to actually use them until now.
            ['slug' => 'week-warrior',      'badge_type' => 'achievement', 'name' => 'Week Warrior',       'description' => 'Logged in 7 days in a row.',                    'icon' => '🔥', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'streak',         'trigger_value' => 7,     'color' => '#f97316'],
            ['slug' => 'unstoppable',       'badge_type' => 'achievement', 'name' => 'Unstoppable',        'description' => 'Logged in 30 days in a row.',                   'icon' => '🌋', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'streak',         'trigger_value' => 30,    'color' => '#dc2626'],
            ['slug' => 'quest-hunter',      'badge_type' => 'achievement', 'name' => 'Quest Hunter',       'description' => 'Completed 25 quests.',                          'icon' => '🗺️', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'quest_complete', 'trigger_value' => 25,    'color' => '#0ea5e9'],
            ['slug' => 'quest-master',      'badge_type' => 'achievement', 'name' => 'Quest Master',       'description' => 'Completed 100 quests.',                         'icon' => '🏹', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'quest_complete', 'trigger_value' => 100,   'color' => '#1d4ed8'],
            ['slug' => 'lifelong-learner',  'badge_type' => 'achievement', 'name' => 'Lifelong Learner',   'description' => 'Completed 5 courses at the Opportunity Hub.',   'icon' => '📖', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'course_complete','trigger_value' => 5,     'color' => '#7c3aed'],
            ['slug' => 'career-climber',    'badge_type' => 'achievement', 'name' => 'Career Climber',     'description' => 'Been hired for 5 different jobs.',              'icon' => '🪜', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'job_hired',      'trigger_value' => 5,     'color' => '#059669'],
            ['slug' => 'asset-collector',   'badge_type' => 'achievement', 'name' => 'Asset Collector',    'description' => 'Bought 10 assets.',                             'icon' => '🏆', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'asset_purchased','trigger_value' => 10,    'color' => '#d97706'],
            ['slug' => 'portfolio-builder', 'badge_type' => 'achievement', 'name' => 'Portfolio Builder',  'description' => 'Made 10 successful investments.',               'icon' => '📊', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'investment',     'trigger_value' => 10,    'color' => '#0891b2'],
            ['slug' => 'net-worth-1m',      'badge_type' => 'achievement', 'name' => 'First Million',      'description' => 'Reached a net worth of KES 1,000,000.',        'icon' => '💰', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'net_worth',      'trigger_value' => 1000000,'color' => '#eab308'],
            ['slug' => 'net-worth-10m',     'badge_type' => 'achievement', 'name' => 'Ten-Million Club',   'description' => 'Reached a net worth of KES 10,000,000.',       'icon' => '💎', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'net_worth',      'trigger_value' => 10000000,'color' => '#a855f7'],
            ['slug' => 'forum-voice',       'badge_type' => 'achievement', 'name' => 'Forum Voice',        'description' => 'Earned 50 forum karma from helpful posts.',     'icon' => '🗣️', 'required_level' => 0, 'required_points' => 0, 'trigger_type' => 'forum_karma',    'trigger_value' => 50,    'color' => '#ec4899'],
        ];

        foreach ($badges as $badge) {
            \App\Models\Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }

        // Legacy pre-slug duplicates (created before the `slug` column existed)
        // sit alongside the correctly-slugged rows above with an empty slug —
        // deactivate them rather than delete, so any user_badges history that
        // points at their IDs stays intact.
        $names = collect($badges)->pluck('name')->all();
        \App\Models\Badge::whereIn('name', $names)
            ->where(fn ($q) => $q->whereNull('slug')->orWhere('slug', ''))
            ->update(['is_active' => false]);
    }
}
