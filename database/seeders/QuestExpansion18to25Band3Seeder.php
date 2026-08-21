<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

class QuestExpansion18to25Band3Seeder extends Seeder
{
    public function run(): void
    {
        // ── Level 8 ──────────────────────────────────────────────────────────
        $sixFigureSignal = Quest::updateOrCreate(['title' => 'The Six-Figure Signal'], [
            'description'    => 'Push your net worth past Ksh 550,000 — a real milestone, not a lucky payday.',
            'icon'           => '📶',
            'xp_reward'      => 490,
            'kes_reward'     => 700,
            'level_required' => 8,
            'trigger_type'   => 'reach_net_worth',
            'trigger_value'  => '550000',
            'trigger_label'  => 'Push your net worth past Ksh 550,000',
            'lesson'         => 'Net worth counts what you own minus what you owe — a fat wallet with unpaid loans isn\'t wealth.',
            'sort_order'     => 80,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Shareholder Status'], [
            'description'    => 'Buy any share on the market. One unit makes you a part-owner of a real company.',
            'icon'           => '📈',
            'xp_reward'      => 450,
            'kes_reward'     => 600,
            'level_required' => 8,
            'trigger_type'   => 'buy_share',
            'trigger_value'  => null,
            'trigger_label'  => 'Buy any share on the market',
            'lesson'         => 'Owning even one share makes you a part-owner of a real company — dividends and price growth now work for you.',
            'sort_order'     => 81,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Own a Piece of the Hustle'], [
            'description'    => 'Buy a business asset from the Marketplace, then deposit Ksh 15,000 into savings — grow two engines at once.',
            'icon'           => '🏗️',
            'xp_reward'      => 660,
            'kes_reward'     => 910,
            'level_required' => 8,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'buy_item_category', 'values' => ['business'],  'label' => 'Buy a business asset from the Marketplace'],
                ['type' => 'deposit_savings',    'values' => ['15000'],     'label' => 'Deposit Ksh 15,000 into savings'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'A business asset that earns monthly income compounds faster than cash sitting idle — feed both at once.',
            'sort_order'     => 82,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Equity Square Debut'], [
            'description'    => 'Back a real venture: make your first investment in an Equity Square deal.',
            'icon'           => '🤝',
            'xp_reward'      => 500,
            'kes_reward'     => 720,
            'level_required' => 8,
            'trigger_type'   => 'invest_deal',
            'trigger_value'  => null,
            'trigger_label'  => 'Invest in an Equity Square deal',
            'lesson'         => 'Equity Square deals let you back a real venture directly — higher risk, higher potential reward than a savings account.',
            'sort_order'     => 83,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Real-World Rehearsal'], [
            'description'    => 'Play through any life-decision scenario to the end.',
            'icon'           => '🎭',
            'xp_reward'      => 420,
            'kes_reward'     => 580,
            'level_required' => 8,
            'trigger_type'   => 'play_scenario',
            'trigger_value'  => null,
            'trigger_label'  => 'Play through any life-decision scenario',
            'lesson'         => 'Every life decision you rehearse in a scenario is a mistake you don\'t have to make with real money later.',
            'sort_order'     => 84,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Fixed and Steady'], [
            'description'    => 'Buy a Money Market Fund unit, then grow your total savings to Ksh 20,000.',
            'icon'           => '💰',
            'xp_reward'      => 660,
            'kes_reward'     => 910,
            'level_required' => 8,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'buy_item_slug', 'values' => ['money-market-fund'], 'label' => 'Buy a Money Market Fund unit'],
                ['type' => 'reach_savings',  'values' => ['20000'],             'label' => 'Grow your total savings to Ksh 20,000'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'Locking a slice of your money into a low-risk instrument protects you from your own impulse to spend it.',
            'sort_order'     => 85,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        // ── Level 9 ──────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Liquidity Check'], [
            'description'    => 'Grow your wallet cash to Ksh 40,000 — real spending power, not just paper wealth.',
            'icon'           => '💵',
            'xp_reward'      => 570,
            'kes_reward'     => 830,
            'level_required' => 9,
            'trigger_type'   => 'reach_balance',
            'trigger_value'  => '40000',
            'trigger_label'  => 'Grow your wallet to Ksh 40,000',
            'lesson'         => 'Net worth on paper means nothing if you can\'t cover this week\'s matatu fare — keep real cash on hand.',
            'sort_order'     => 90,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Park It Smart'], [
            'description'    => 'Buy a 5-Year Treasury Bond. Your money is locked, but the coupon pays out twice a year.',
            'icon'           => '📜',
            'xp_reward'      => 520,
            'kes_reward'     => 760,
            'level_required' => 9,
            'trigger_type'   => 'buy_item_slug',
            'trigger_value'  => 'treasury-bonds-5yr',
            'trigger_label'  => 'Buy a 5-Year Treasury Bond',
            'lesson'         => 'A 5-year bond locks your money but pays a coupon twice a year — patient capital earns a premium.',
            'sort_order'     => 91,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Calculated Risk'], [
            'description'    => 'Take one spin on the Lucky Wheel — then decide what the payout is for before it lands.',
            'icon'           => '🎡',
            'xp_reward'      => 500,
            'kes_reward'     => 720,
            'level_required' => 9,
            'trigger_type'   => 'spin_wheel',
            'trigger_value'  => null,
            'trigger_label'  => 'Spin the Lucky Wheel',
            'lesson'         => 'Even a lucky spin needs a plan for the payout — decide before you win, not after.',
            'sort_order'     => 92,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Recognition Round'], [
            'description'    => 'Earn any badge in Pesa City.',
            'icon'           => '🏅',
            'xp_reward'      => 540,
            'kes_reward'     => 780,
            'level_required' => 9,
            'trigger_type'   => 'earn_badge',
            'trigger_value'  => null,
            'trigger_label'  => 'Earn any badge',
            'lesson'         => 'Badges are proof, not points — the real reward already happened when the habit stuck.',
            'sort_order'     => 93,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Building the Nest Egg'], [
            'description'    => 'Buy another share, and push your net worth past Ksh 650,000 — compounding in two directions at once.',
            'icon'           => '🥚',
            'xp_reward'      => 770,
            'kes_reward'     => 1120,
            'level_required' => 9,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'buy_share',       'values' => [],         'label' => 'Buy any share on the market'],
                ['type' => 'reach_net_worth', 'values' => ['650000'], 'label' => 'Push your net worth past Ksh 650,000'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'Consistent share purchases plus a rising net worth is compounding in two directions at once.',
            'sort_order'     => 94,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'One Step From The Summit'], [
            'description'    => 'Reach Level 10.',
            'icon'           => '⭐',
            'xp_reward'      => 600,
            'kes_reward'     => 850,
            'level_required' => 9,
            'trigger_type'   => 'reach_level',
            'trigger_value'  => '10',
            'trigger_label'  => 'Reach Level 10',
            'lesson'         => 'A level is a proxy for consistency — you don\'t jump to it, you accumulate into it.',
            'sort_order'     => 95,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        // ── Level 10 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Upskill for the Top'], [
            'description'    => 'Complete the Web Design Basics course — the skills that separate a Ksh 100K job from a Ksh 300K one.',
            'icon'           => '💻',
            'xp_reward'      => 700,
            'kes_reward'     => 1050,
            'level_required' => 10,
            'trigger_type'   => 'take_course',
            'trigger_value'  => 'web-design-basics',
            'trigger_label'  => 'Complete the Web Design Basics course',
            'lesson'         => 'At the top of the ladder, skills still separate a Ksh 100K job from a Ksh 300K one.',
            'sort_order'     => 100,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'The Senior Seat'], [
            'description'    => 'Get hired for any job at the Opportunity Hub — a new role resets your ceiling.',
            'icon'           => '💼',
            'xp_reward'      => 680,
            'kes_reward'     => 1000,
            'level_required' => 10,
            'trigger_type'   => 'get_job',
            'trigger_value'  => null,
            'trigger_label'  => 'Get hired for any job',
            'lesson'         => 'Every promotion or new senior role resets your ceiling — chase the role, not just the raise.',
            'sort_order'     => 101,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Circle at the Summit'], [
            'description'    => 'Join a chama. Even wealthy people stay in chamas.',
            'icon'           => '🫱🏾‍🫲🏾',
            'xp_reward'      => 600,
            'kes_reward'     => 900,
            'level_required' => 10,
            'trigger_type'   => 'join_chama',
            'trigger_value'  => null,
            'trigger_label'  => 'Join a chama',
            'lesson'         => 'Even wealthy people stay in chamas — shared discipline scales further than solo willpower.',
            'sort_order'     => 102,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'Hustle Hard, Spend Smart'], [
            'description'    => 'Spend on any Fun World activity — a budgeted line for fun isn\'t wasteful.',
            'icon'           => '🎉',
            'xp_reward'      => 580,
            'kes_reward'     => 850,
            'level_required' => 10,
            'trigger_type'   => 'fun_world_spend',
            'trigger_value'  => null,
            'trigger_label'  => 'Spend on any Fun World activity',
            'lesson'         => 'A budgeted line for fun isn\'t wasteful — it\'s what keeps the whole plan sustainable enough to stick to.',
            'sort_order'     => 103,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'The Culmination'], [
            'description'    => 'Have completed "The Six-Figure Signal", then push your net worth past Ksh 1,000,000.',
            'icon'           => '👑',
            'xp_reward'      => 910,
            'kes_reward'     => 1400,
            'level_required' => 10,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'complete_quest',   'values' => [(string) $sixFigureSignal->id], 'label' => 'Have completed "The Six-Figure Signal"'],
                ['type' => 'reach_net_worth',  'values' => ['1000000'],                       'label' => 'Push your net worth past Ksh 1,000,000'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'A million-shilling net worth isn\'t a single win — it\'s every disciplined choice before it, arriving at once.',
            'sort_order'     => 104,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);

        Quest::updateOrCreate(['title' => 'First Brick of Empire'], [
            'description'    => 'Buy a property asset from the Marketplace — the slowest-moving, most durable asset class.',
            'icon'           => '🧱',
            'xp_reward'      => 750,
            'kes_reward'     => 1100,
            'level_required' => 10,
            'trigger_type'   => 'buy_item_category',
            'trigger_value'  => 'property',
            'trigger_label'  => 'Buy a property asset from the Marketplace',
            'lesson'         => 'Property is the slowest-moving asset class but often the most durable — it rarely evaporates overnight.',
            'sort_order'     => 105,
            'is_active'      => true,
            'age_group'      => '18-25',
        ]);
    }
}
