<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

class QuestExpansion13to17Band2Seeder extends Seeder
{
    public function run(): void
    {
        // Captured so "Full Stack Teen" (L7) can chain off it via complete_quest.
        $firstStockPick = Quest::updateOrCreate(['title' => 'First Stock Pick'], [
            'description'    => 'Buy your first share on the Pesa City stock market. Small stake, real experience.',
            'icon'           => '📊',
            'xp_reward'      => 220,
            'kes_reward'     => 200,
            'level_required' => 4,
            'trigger_type'   => 'buy_share',
            'trigger_value'  => '',
            'trigger_label'  => 'Buy any share on the stock market',
            'lesson'         => 'The best time to learn how shares move is with money you can afford to watch closely.',
            'sort_order'     => 43,
            'is_active'      => true,
            'age_group'      => '13-17',
        ]);

        $quests = [

            // ── Level 4 ──────────────────────────────────────────────────────
            [
                'title' => 'Boda Fare Vault', 'icon' => '💰', 'xp_reward' => 200, 'kes_reward' => 180,
                'level_required' => 4, 'sort_order' => 41,
                'description' => 'Save Ksh 5,000 — enough to build a dedicated fare fund instead of always paying cash per trip.',
                'lesson' => 'A savings pot with one clear goal fills faster than a vague "save some money" plan.',
                'trigger_type' => 'reach_savings', 'trigger_value' => '5000', 'trigger_label' => 'Reach Ksh 5,000 in total savings',
            ],
            [
                'title' => "Duka Owner's Eye", 'icon' => '🏪', 'xp_reward' => 210, 'kes_reward' => 190,
                'level_required' => 4, 'sort_order' => 42,
                'description' => 'Buy any business-category asset from the Marketplace — see what it takes to own the means of making money, not just spending it.',
                'lesson' => 'Business assets pay you back over time; consumer goods only ever cost you.',
                'trigger_type' => 'buy_item_category', 'trigger_value' => 'business', 'trigger_label' => 'Buy any business-category asset',
            ],
            // First Stock Pick — captured above (sort_order 43)
            [
                'title' => 'Spin Toward Capital', 'icon' => '🎡', 'xp_reward' => 190, 'kes_reward' => 150,
                'level_required' => 4, 'sort_order' => 44,
                'description' => 'Take a spin on the Lucky Wheel — treat whatever you land as seed capital, not free cash.',
                'lesson' => 'Even a windfall needs a plan the moment it lands, or it vanishes the same day it arrived.',
                'trigger_type' => 'spin_wheel', 'trigger_value' => '', 'trigger_label' => 'Spin the Lucky Wheel',
            ],
            [
                'title' => 'Teen Budget, Real Limits', 'icon' => '🎮', 'xp_reward' => 200, 'kes_reward' => 150,
                'level_required' => 4, 'sort_order' => 45,
                'description' => "Spend in Fun World — but only after deciding in advance what you're willing to lose.",
                'lesson' => "Entertainment spending is fine when it's budgeted ahead of time, not automatic.",
                'trigger_type' => 'fun_world_spend', 'trigger_value' => '', 'trigger_label' => 'Spend anything in Fun World',
            ],
            [
                'title' => 'Scenario Smarts, Then Shares', 'icon' => '🧠', 'xp_reward' => 310, 'kes_reward' => 280,
                'level_required' => 4, 'sort_order' => 46,
                'description' => 'Play through a life-decision scenario, then put what you learned into practice by buying a share.',
                'lesson' => "Knowledge that isn't applied within a week rarely gets applied at all.",
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null,
                'triggers' => [
                    ['type' => 'play_scenario', 'values' => [], 'label' => 'Play through any life-decision scenario'],
                    ['type' => 'buy_share',     'values' => [], 'label' => 'Buy any share on the stock market'],
                ],
                'trigger_mode' => 'all',
            ],

            // ── Level 5 ──────────────────────────────────────────────────────
            [
                'title' => 'Ten Thousand Strong', 'icon' => '🏦', 'xp_reward' => 280, 'kes_reward' => 300,
                'level_required' => 5, 'sort_order' => 51,
                'description' => 'Push your total savings to Ksh 10,000 — the kind of cushion that survives a genuinely bad week.',
                'lesson' => "An emergency fund isn't about being rich — it's about never being one bad week from crisis.",
                'trigger_type' => 'reach_savings', 'trigger_value' => '10000', 'trigger_label' => 'Reach Ksh 10,000 in total savings',
            ],
            [
                'title' => 'Property Ladder Peek', 'icon' => '🏘️', 'xp_reward' => 290, 'kes_reward' => 310,
                'level_required' => 5, 'sort_order' => 52,
                'description' => "Buy any property-category asset — Kenya's oldest form of wealth-building, just at teen-hustle scale.",
                'lesson' => 'Property compounds slowly but rarely disappears overnight, unlike cash sitting in a pocket.',
                'trigger_type' => 'buy_item_category', 'trigger_value' => 'property', 'trigger_label' => 'Buy any property-category asset',
            ],
            [
                'title' => 'Deal Hunter', 'icon' => '🤝', 'xp_reward' => 300, 'kes_reward' => 320,
                'level_required' => 5, 'sort_order' => 53,
                'description' => 'Put money into an Equity Square investment deal — real returns come from real risk, taken on purpose.',
                'lesson' => 'An investment deal and a bet look identical until you check who did the homework first.',
                'trigger_type' => 'invest_deal', 'trigger_value' => '', 'trigger_label' => 'Invest in any Equity Square deal',
            ],
            [
                'title' => 'Badge Secured', 'icon' => '🏅', 'xp_reward' => 260, 'kes_reward' => 250,
                'level_required' => 5, 'sort_order' => 54,
                'description' => "Earn any badge in Pesa City — proof your habits are being noticed, not just your luck.",
                'lesson' => 'Recognition should follow a good habit, never replace the habit itself.',
                'trigger_type' => 'earn_badge', 'trigger_value' => '', 'trigger_label' => 'Earn any badge',
            ],
            [
                'title' => 'Wallet Check', 'icon' => '👛', 'xp_reward' => 270, 'kes_reward' => 280,
                'level_required' => 5, 'sort_order' => 55,
                'description' => 'Grow your spendable wallet balance to Ksh 6,000 without touching your savings pot.',
                'lesson' => 'Keeping "spend money" and "save money" separate stops one from quietly eating the other.',
                'trigger_type' => 'reach_balance', 'trigger_value' => '6000', 'trigger_label' => 'Grow your wallet to Ksh 6,000',
            ],
            [
                'title' => 'Grow It Two Ways', 'icon' => '🌱', 'xp_reward' => 390, 'kes_reward' => 420,
                'level_required' => 5, 'sort_order' => 56,
                'description' => 'Buy a share AND make a savings deposit in the same stretch — growth on two fronts at once.',
                'lesson' => "Diversifying isn't only about which assets you hold — it's about running more than one plan for your money at a time.",
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null,
                'triggers' => [
                    ['type' => 'buy_share',       'values' => [], 'label' => 'Buy any share on the stock market'],
                    ['type' => 'deposit_savings', 'values' => [], 'label' => 'Make any deposit into savings'],
                ],
                'trigger_mode' => 'all',
            ],

            // ── Level 6 ──────────────────────────────────────────────────────
            [
                'title' => 'Gadget With a Plan', 'icon' => '📱', 'xp_reward' => 340, 'kes_reward' => 400,
                'level_required' => 6, 'sort_order' => 61,
                'description' => 'Buy any gadget-category asset — but only one that can also earn you money, not just impress your friends.',
                'lesson' => "A gadget that generates income is a tool. A gadget that doesn't is just a want with good marketing.",
                'trigger_type' => 'buy_item_category', 'trigger_value' => 'gadget', 'trigger_label' => 'Buy any gadget-category asset',
            ],
            [
                'title' => 'Steady and Fixed', 'icon' => '🏛️', 'xp_reward' => 350, 'kes_reward' => 420,
                'level_required' => 6, 'sort_order' => 62,
                'description' => 'Put money into a Money Market Fund, Treasury Bill, or Treasury Bond — the safest shelf in the whole Marketplace.',
                'lesson' => "Not every investment needs to be exciting. Boring and safe has its own kind of value.",
                'trigger_type' => 'buy_item_category', 'trigger_value' => 'fixed_income', 'trigger_label' => 'Buy any fixed-income asset',
            ],
            [
                'title' => 'Second Stock Move', 'icon' => '📈', 'xp_reward' => 330, 'kes_reward' => 380,
                'level_required' => 6, 'sort_order' => 63,
                'description' => 'Buy a share again — this time, check the sector before you click buy.',
                'lesson' => 'Repetition with attention beats repetition on autopilot every time.',
                'trigger_type' => 'buy_share', 'trigger_value' => '', 'trigger_label' => 'Buy any share on the stock market',
            ],
            [
                'title' => 'Climbing to Level Seven', 'icon' => '⭐', 'xp_reward' => 320, 'kes_reward' => 350,
                'level_required' => 6, 'sort_order' => 64,
                'description' => 'Push through to Level 7 — the XP is already yours to earn, just keep moving.',
                'lesson' => 'Progress compounds the same way savings do — small consistent pushes beat rare big ones.',
                'trigger_type' => 'reach_level', 'trigger_value' => '7', 'trigger_label' => 'Reach Level 7',
            ],
            [
                'title' => 'A Story Worth Learning', 'icon' => '📖', 'xp_reward' => 300, 'kes_reward' => 300,
                'level_required' => 6, 'sort_order' => 65,
                'description' => 'Play through any life-decision scenario in Pesa City and see where your choice leads.',
                'lesson' => "Every scenario is a rehearsal for a real decision you'll eventually face with real money.",
                'trigger_type' => 'play_scenario', 'trigger_value' => '', 'trigger_label' => 'Play through any life-decision scenario',
            ],
            [
                'title' => 'The Course That Counts', 'icon' => '📒', 'xp_reward' => 475, 'kes_reward' => 560,
                'level_required' => 6, 'sort_order' => 66,
                'description' => 'Complete Bookkeeping Basics, then prove you can apply it — grow your wallet balance right after.',
                'lesson' => 'A course only pays off once its lesson shows up in your actual numbers.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null,
                'triggers' => [
                    ['type' => 'take_course',   'values' => ['bookkeeping-basics'], 'label' => 'Complete Bookkeeping Basics'],
                    ['type' => 'reach_balance', 'values' => ['8000'], 'label' => 'Grow your wallet to Ksh 8,000'],
                ],
                'trigger_mode' => 'all',
            ],

            // ── Level 7 ──────────────────────────────────────────────────────
            [
                'title' => 'Twenty Thousand Milestone', 'icon' => '🏆', 'xp_reward' => 400, 'kes_reward' => 500,
                'level_required' => 7, 'sort_order' => 71,
                'description' => 'Push your total savings to Ksh 20,000.',
                'lesson' => 'Every milestone you actually hit makes the next one feel achievable instead of abstract.',
                'trigger_type' => 'reach_savings', 'trigger_value' => '20000', 'trigger_label' => 'Reach Ksh 20,000 in total savings',
            ],
            [
                'title' => 'Vehicle Value Play', 'icon' => '🏍️', 'xp_reward' => 410, 'kes_reward' => 520,
                'level_required' => 7, 'sort_order' => 72,
                'description' => 'Buy any vehicle-category asset — transport that pays for itself is a different animal from transport that only costs.',
                'lesson' => 'The right vehicle asset turns a cost centre into an income stream.',
                'trigger_type' => 'buy_item_category', 'trigger_value' => 'vehicle', 'trigger_label' => 'Buy any vehicle-category asset',
            ],
            [
                'title' => 'Deal Closer II', 'icon' => '🤝', 'xp_reward' => 420, 'kes_reward' => 540,
                'level_required' => 7, 'sort_order' => 73,
                'description' => 'Close another Equity Square deal — one good investment is luck, two is a pattern.',
                'lesson' => 'Consistency is what separates an investor from someone who just got lucky once.',
                'trigger_type' => 'invest_deal', 'trigger_value' => '', 'trigger_label' => 'Invest in any Equity Square deal',
            ],
            [
                'title' => 'Job Well Landed', 'icon' => '💼', 'xp_reward' => 380, 'kes_reward' => 450,
                'level_required' => 7, 'sort_order' => 74,
                'description' => 'Get hired for any job at the Opportunity Hub — even a side income changes what your money can do.',
                'lesson' => 'Income you control is worth more than income you only hope for.',
                'trigger_type' => 'get_job', 'trigger_value' => '', 'trigger_label' => 'Get hired for any job',
            ],
            [
                'title' => 'Worth Watching', 'icon' => '📈', 'xp_reward' => 400, 'kes_reward' => 480,
                'level_required' => 7, 'sort_order' => 75,
                'description' => 'Push your net worth past Ksh 15,000 — savings, shares, and assets all counting together.',
                'lesson' => 'Net worth is the real scoreboard — everything you own, minus what you owe.',
                'trigger_type' => 'reach_net_worth', 'trigger_value' => '15000', 'trigger_label' => 'Push your net worth past Ksh 15,000',
            ],
            [
                'title' => 'Full Stack Teen', 'icon' => '🧱', 'xp_reward' => 560, 'kes_reward' => 700,
                'level_required' => 7, 'sort_order' => 76,
                'description' => 'Prove the loop works: have "First Stock Pick" completed, and push your net worth past Ksh 18,000.',
                'lesson' => "Real financial progress isn't one lucky action — it's a stack of small ones that agree with each other.",
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null,
                'triggers' => [
                    ['type' => 'complete_quest',  'values' => [(string) $firstStockPick->id], 'label' => 'Have completed "First Stock Pick"'],
                    ['type' => 'reach_net_worth', 'values' => ['18000'], 'label' => 'Push your net worth past Ksh 18,000'],
                ],
                'trigger_mode' => 'all',
            ],
        ];

        foreach ($quests as $data) {
            Quest::updateOrCreate(
                ['title' => $data['title']],
                $data + ['is_active' => true, 'age_group' => '13-17']
            );
        }
    }
}
