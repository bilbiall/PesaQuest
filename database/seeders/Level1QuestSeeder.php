<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

class Level1QuestSeeder extends Seeder
{
    public function run(): void
    {
        $quests = [

            // ── 1. Orientation course ────────────────────────────────────────
            [
                'title'         => 'City Orientation',
                'description'   => 'Complete the Welcome to Pesa City course to learn the game and earn your first XP reward.',
                'icon'          => '🏙️',
                'xp_reward'     => 100,
                'kes_reward'    => 50,
                'level_required'=> 1,
                'trigger_type'  => 'take_course',
                'trigger_value' => 'pesa-city-orientation',
                'trigger_label' => 'Complete the Welcome to Pesa City course',
                'lesson'        => 'Knowing how the game works gives you a head start. Real financial success also starts with education — understanding how money works before you spend it.',
                'sort_order'    => 1,
            ],

            // ── 2. Open savings ──────────────────────────────────────────────
            [
                'title'         => 'Open Your Vault',
                'description'   => 'Visit the Bank district and open a savings account.',
                'icon'          => '🏦',
                'xp_reward'     => 100,
                'kes_reward'    => 100,
                'level_required'=> 1,
                'trigger_type'  => 'open_savings',
                'trigger_value' => '',
                'trigger_label' => 'Open a savings account',
                'lesson'        => 'A savings account is the foundation of financial health. In Kenya, over 70% of adults live without any savings buffer — don\'t be one of them.',
                'sort_order'    => 2,
            ],

            // ── 3. Get a job ─────────────────────────────────────────────────
            [
                'title'         => 'Land Your First Job',
                'description'   => 'Get hired for any job at the Opportunity Hub. Your salary will start adding to your income every game cycle.',
                'icon'          => '💼',
                'xp_reward'     => 150,
                'kes_reward'    => 200,
                'level_required'=> 1,
                'trigger_type'  => 'get_job',
                'trigger_value' => '',
                'trigger_label' => 'Get hired for any job',
                'lesson'        => 'A steady income is the engine of wealth. Before you can invest or save seriously, you need reliable cash coming in every month.',
                'sort_order'    => 3,
            ],

            // ── 4. First marketplace purchase ────────────────────────────────
            [
                'title'         => 'Market Explorer',
                'description'   => 'Buy your first item from the Marketplace. Assets hold value — choose wisely.',
                'icon'          => '🛒',
                'xp_reward'     => 75,
                'kes_reward'    => 0,
                'level_required'=> 1,
                'trigger_type'  => 'buy_item_category',
                'trigger_value' => '',
                'trigger_label' => 'Buy any item from the marketplace',
                'lesson'        => 'Not all spending is bad. Buying assets that hold or grow in value — like tools, tech, or property — is very different from spending on things that depreciate.',
                'sort_order'    => 4,
            ],

            // ── 5. First savings deposit ─────────────────────────────────────
            [
                'title'         => 'First Deposit',
                'description'   => 'Make your first deposit into a savings scheme. Start small — the habit is what matters.',
                'icon'          => '💰',
                'xp_reward'     => 75,
                'kes_reward'    => 50,
                'level_required'=> 1,
                'trigger_type'  => 'deposit_savings',
                'trigger_value' => '',
                'trigger_label' => 'Make any deposit into savings',
                'lesson'        => 'The first deposit is always the hardest. Research shows that people who make even one deposit are far more likely to keep saving consistently.',
                'sort_order'    => 5,
            ],

            // ── 6. Savings milestone ─────────────────────────────────────────
            [
                'title'         => 'Savings Milestone',
                'description'   => 'Grow your savings to KES 500. Consistency beats big one-off deposits.',
                'icon'          => '📈',
                'xp_reward'     => 125,
                'kes_reward'    => 0,
                'level_required'=> 1,
                'trigger_type'  => 'reach_savings',
                'trigger_value' => '500',
                'trigger_label' => 'Reach KES 500 in savings',
                'lesson'        => 'KES 500 saved is your first emergency buffer. Financial experts recommend building 3–6 months of expenses in savings before taking on investment risk.',
                'sort_order'    => 6,
            ],

            // ── 7. City Starter Pack (multi-trigger) ─────────────────────────
            [
                'title'         => 'City Starter Pack',
                'description'   => 'The full launch sequence: complete the orientation, open savings, and land a job. Do all three to claim a big bonus.',
                'icon'          => '🚀',
                'xp_reward'     => 350,
                'kes_reward'    => 500,
                'level_required'=> 1,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'take_course',  'values' => ['pesa-city-orientation'], 'label' => 'Complete the Welcome to Pesa City course'],
                    ['type' => 'open_savings', 'values' => [],                        'label' => 'Open a savings account'],
                    ['type' => 'get_job',      'values' => [],                        'label' => 'Get hired for any job'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => 'You\'ve laid the foundation: knowledge, savings, and income. These three pillars are what every financially secure person in the world builds on first.',
                'sort_order'    => 7,
            ],

        ];

        foreach ($quests as $data) {
            Quest::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['is_active' => true, 'age_group' => 'all'])
            );
        }
    }
}
