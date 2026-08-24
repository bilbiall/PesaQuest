<?php

namespace Database\Seeders;

use App\Http\Controllers\GameSetController;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Appends the deeper progression-ladder milestones to whatever's already
 * live in the `journey_milestones` setting — merges by title rather than
 * overwriting, so an admin's own custom edits (or a fresh install still on
 * GameSetController::defaultMilestones()) are both respected. Safe to
 * re-run: already-present titles are skipped.
 */
class JourneyMilestoneExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $json    = Setting::get('journey_milestones', null);
        $current = $json ? (json_decode($json, true) ?: []) : [];
        if (empty($current)) {
            $current = GameSetController::defaultMilestones();
        }

        $existingTitles = collect($current)->pluck('title')->map(fn ($t) => strtolower(trim($t)))->all();

        $newOnes = [
            ['icon' => '🎯', 'title' => 'Rising Star',          'description' => 'Reach Level 10',                         'type' => 'level',    'threshold' => 10],
            ['icon' => '🏅', 'title' => 'Money Mind',           'description' => 'Reach Level 15',                         'type' => 'level',    'threshold' => 15],
            ['icon' => '👑', 'title' => 'Financial Guru',       'description' => 'Reach Level 20',                         'type' => 'level',    'threshold' => 20],
            ['icon' => '🚀', 'title' => 'PesaQuest Elite',      'description' => 'Reach Level 25',                         'type' => 'level',    'threshold' => 25],
            ['icon' => '💵', 'title' => 'Steady Saver',         'description' => 'Save KES 50,000',                        'type' => 'balance',  'threshold' => 50000],
            ['icon' => '🏆', 'title' => 'Six-Figure Saver',     'description' => 'Save KES 250,000',                       'type' => 'balance',  'threshold' => 250000],
            ['icon' => '📖', 'title' => 'Lifelong Learner',     'description' => 'Complete 3 courses',                     'type' => 'course',   'threshold' => 3],
            ['icon' => '🎓', 'title' => 'Scholar of Money',     'description' => 'Complete 5 courses',                     'type' => 'course',   'threshold' => 5],
            ['icon' => '🏛️', 'title' => 'Finance Graduate',     'description' => 'Complete 10 courses',                    'type' => 'course',   'threshold' => 10],
            ['icon' => '🗺️', 'title' => 'Quest Champion',       'description' => 'Complete 10 quests',                     'type' => 'quest',    'threshold' => 10],
            ['icon' => '⚔️', 'title' => 'Quest Legend',         'description' => 'Complete 25 quests',                     'type' => 'quest',    'threshold' => 25],
            ['icon' => '🌟', 'title' => 'Quest Master',         'description' => 'Complete 50 quests',                     'type' => 'quest',    'threshold' => 50],
            ['icon' => '👔', 'title' => 'Career Climber',       'description' => 'Get hired 2 times',                      'type' => 'job',      'threshold' => 2],
            ['icon' => '🏠', 'title' => 'Asset Collector',      'description' => 'Own 3 assets',                           'type' => 'asset',    'threshold' => 3],
            ['icon' => '🏙️', 'title' => 'Portfolio Builder',    'description' => 'Own 5 assets',                           'type' => 'asset',    'threshold' => 5],
            ['icon' => '🏰', 'title' => 'Empire Builder',       'description' => 'Own 10 assets',                          'type' => 'asset',    'threshold' => 10],
            ['icon' => '💰', 'title' => 'Half-Millionaire',     'description' => 'Reach a net worth of KES 500,000',       'type' => 'net_worth','threshold' => 500000],
            ['icon' => '💎', 'title' => 'PesaQuest Millionaire','description' => 'Reach a net worth of KES 1,000,000',     'type' => 'net_worth','threshold' => 1000000],
            ['icon' => '🏔️', 'title' => 'Wealth Titan',         'description' => 'Reach a net worth of KES 5,000,000',     'type' => 'net_worth','threshold' => 5000000],
            ['icon' => '🎂', 'title' => 'One Year in Pesa City','description' => 'Play for 365 game days',                 'type' => 'game_day', 'threshold' => 365],
        ];

        $added = 0;
        foreach ($newOnes as $ms) {
            if (in_array(strtolower(trim($ms['title'])), $existingTitles, true)) {
                continue;
            }
            $current[] = $ms;
            $added++;
        }

        Setting::set('journey_milestones', json_encode(array_values($current)), 'game');

        $this->command?->info("Journey Milestones: added {$added} new milestone(s), " . count($current) . ' total.');
    }
}
