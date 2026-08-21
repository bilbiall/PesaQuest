<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * Tops up the 8-12 quest pool at levels 4-7 from 2 to 8 per level, leaning
 * on real game activities (shares, deals, assets, chama, fun world) instead
 * of courses/jobs — see the Aug 2026 quest-diversity pass.
 */
class QuestExpansion8to12Band2Seeder extends Seeder
{
    public function run(): void
    {
        // ── Level 4 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Market Day Investor'], [
            'description'    => 'Buy your first share on the Market — even one share makes you a small owner of a real company.',
            'icon'            => '📊',
            'xp_reward'       => 200,
            'kes_reward'      => 180,
            'level_required'  => 4,
            'trigger_type'    => 'buy_share',
            'trigger_value'   => '',
            'trigger_label'   => 'Buy any share on the Market',
            'lesson'          => 'Owning a tiny piece of a big company is how regular people build wealth, not just salaried workers.',
            'sort_order'      => 40,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        $gadgetFundGoal = Quest::updateOrCreate(['title' => 'Gadget Fund Goal'], [
            'description'    => 'Grow your total savings to Ksh 3,500 — a real gadget fund, not just loose coins in a jar.',
            'icon'            => '🐷',
            'xp_reward'       => 230,
            'kes_reward'      => 210,
            'level_required'  => 4,
            'trigger_type'    => 'reach_savings',
            'trigger_value'   => '3500',
            'trigger_label'   => 'Get your total savings to Ksh 3,500',
            'lesson'          => 'A named goal — "gadget fund" not just "savings" — makes it much harder to spend the money on something else.',
            'sort_order'      => 41,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'First Job Badge'], [
            'description'    => 'Get hired for any job, then keep Ksh 1,500 sitting safely in your wallet — earning and holding are two different skills.',
            'icon'            => '💼',
            'xp_reward'       => 300,
            'kes_reward'      => 280,
            'level_required'  => 4,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'get_job',       'values' => [],       'label' => 'Get hired for any job'],
                ['type' => 'reach_balance', 'values' => ['1500'], 'label' => 'Grow your wallet to Ksh 1,500'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'A payday only builds wealth if some of it stays in your pocket instead of leaving the same day it arrives.',
            'sort_order'      => 42,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Toy Box Upgrade'], [
            'description'    => 'Buy any gadget from the Marketplace — pick one that will actually be useful, not just fun for a day.',
            'icon'            => '🎮',
            'xp_reward'       => 240,
            'kes_reward'      => 220,
            'level_required'  => 4,
            'trigger_type'    => 'buy_item_category',
            'trigger_value'   => 'gadget',
            'trigger_label'   => 'Buy any gadget item',
            'lesson'          => 'Before buying anything fun, ask: will I still want this in a month? That question saves more money than any budget rule.',
            'sort_order'      => 43,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Lucky Wheel Windfall'], [
            'description'    => 'Take one spin on the Lucky Wheel and see what you land — small fun, small stakes.',
            'icon'            => '🎡',
            'xp_reward'       => 190,
            'kes_reward'      => 170,
            'level_required'  => 4,
            'trigger_type'    => 'spin_wheel',
            'trigger_value'   => '',
            'trigger_label'   => 'Spin the Lucky Wheel',
            'lesson'          => 'A spin is entertainment, not a plan — never treat luck as a way to reach a real savings goal.',
            'sort_order'      => 44,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Double Hustle'], [
            'description'    => 'Complete the Money Basics course, then grow your wallet to Ksh 2,500 — knowledge first, cash second.',
            'icon'            => '🪙',
            'xp_reward'       => 310,
            'kes_reward'      => 280,
            'level_required'  => 4,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'take_course',  'values' => ['money-basics'], 'label' => 'Complete the Money Basics course'],
                ['type' => 'reach_balance','values' => ['2500'],         'label' => 'Grow your wallet to Ksh 2,500'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'Learning the rules of money before you have a lot of it means you make fewer expensive mistakes later.',
            'sort_order'      => 45,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        // ── Level 5 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Family Chama Junior'], [
            'description'    => 'Join a chama — Kenyan families have pooled money together for generations, and kids can start learning the habit early too.',
            'icon'            => '🤝',
            'xp_reward'       => 260,
            'kes_reward'      => 240,
            'level_required'  => 5,
            'trigger_type'    => 'join_chama',
            'trigger_value'   => '',
            'trigger_label'   => 'Join a chama',
            'lesson'          => 'It is much harder to skip saving when other people are counting on you to contribute too.',
            'sort_order'      => 50,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Fixed Income Starter'], [
            'description'    => 'Buy into a Money Market Fund or Treasury product from the Marketplace — safe, steady growth for patient savers.',
            'icon'            => '💰',
            'xp_reward'       => 300,
            'kes_reward'      => 320,
            'level_required'  => 5,
            'trigger_type'    => 'buy_item_category',
            'trigger_value'   => 'fixed_income',
            'trigger_label'   => 'Buy any fixed-income item (MMF, Treasury Bill, or Bond)',
            'lesson'          => 'Not every investment needs to be exciting. Slow and steady, low-risk products protect money you cannot afford to lose.',
            'sort_order'      => 51,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Equity Square Explorer'], [
            'description'    => 'Put money into any deal at Equity Square — a small step into how real investors grow their money together.',
            'icon'            => '🏛️',
            'xp_reward'       => 270,
            'kes_reward'      => 290,
            'level_required'  => 5,
            'trigger_type'    => 'invest_deal',
            'trigger_value'   => '',
            'trigger_label'   => 'Invest in any Equity Square deal',
            'lesson'          => 'Pooling money into a shared deal can grow it faster than saving alone — but it also means sharing the risk.',
            'sort_order'      => 52,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Savings Streak Kid'], [
            'description'    => 'Grow one savings pocket to Ksh 4,500 — pick a target and stay stubborn about it.',
            'icon'            => '📈',
            'xp_reward'       => 290,
            'kes_reward'      => 310,
            'level_required'  => 5,
            'trigger_type'    => 'deposit_savings',
            'trigger_value'   => '4500',
            'trigger_label'   => 'Grow one savings pocket to Ksh 4,500',
            'lesson'          => 'One pocket, one goal, no mixing — separating savings by purpose makes it far less tempting to raid.',
            'sort_order'      => 53,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Fun Day Planner'], [
            'description'    => 'Spend some money at Fun World — but plan it first, don\'t just wander in and see what happens.',
            'icon'            => '🎢',
            'xp_reward'       => 260,
            'kes_reward'      => 280,
            'level_required'  => 5,
            'trigger_type'    => 'fun_world_spend',
            'trigger_value'   => '',
            'trigger_label'   => 'Spend at Fun World',
            'lesson'          => 'Fun deserves its own small budget too — the trick is deciding the amount before you get there, not after.',
            'sort_order'      => 54,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Racing to Level Six'], [
            'description'    => 'Prove your Gadget Fund habit stuck, then push on to Level 6 — momentum is the real reward.',
            'icon'            => '🚀',
            'xp_reward'       => 400,
            'kes_reward'      => 420,
            'level_required'  => 5,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'complete_quest', 'values' => [(string) $gadgetFundGoal->id], 'label' => 'Have completed "Gadget Fund Goal"'],
                ['type' => 'reach_level',    'values' => ['6'],                          'label' => 'Reach Level 6'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'Progress compounds: the habits from your first savings goal make every goal after it a little easier.',
            'sort_order'      => 55,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        // ── Level 6 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Second Share Buy'], [
            'description'    => 'Buy another share on the Market — building a portfolio means owning more than just one thing.',
            'icon'            => '📊',
            'xp_reward'       => 320,
            'kes_reward'      => 380,
            'level_required'  => 6,
            'trigger_type'    => 'buy_share',
            'trigger_value'   => '',
            'trigger_label'   => 'Buy any share on the Market',
            'lesson'          => 'Spreading money across more than one share is how investors protect themselves if one company has a bad year.',
            'sort_order'      => 60,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        $businessStarterKit = Quest::updateOrCreate(['title' => 'Business Starter Kit'], [
            'description'    => 'Buy a business asset from the Marketplace — a small stake in something that could earn you income.',
            'icon'            => '🏪',
            'xp_reward'       => 360,
            'kes_reward'      => 420,
            'level_required'  => 6,
            'trigger_type'    => 'buy_item_category',
            'trigger_value'   => 'business',
            'trigger_label'   => 'Buy any business asset',
            'lesson'          => 'A business asset can pay you back over and over, unlike a toy that only gives you one moment of fun.',
            'sort_order'      => 61,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Twelve Thousand and Climbing'], [
            'description'    => 'Push your net worth past Ksh 12,000 — everything you own, minus anything you owe.',
            'icon'            => '📐',
            'xp_reward'       => 340,
            'kes_reward'      => 400,
            'level_required'  => 6,
            'trigger_type'    => 'reach_net_worth',
            'trigger_value'   => '12000',
            'trigger_label'   => 'Push your net worth past Ksh 12,000',
            'lesson'          => 'Net worth is the real scoreboard — not how much cash is in your hand today, but everything you have built so far.',
            'sort_order'      => 62,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Badge of Discipline'], [
            'description'    => 'Earn any badge in Pesa City — proof that good habits, not just luck, are building your results.',
            'icon'            => '🏅',
            'xp_reward'       => 330,
            'kes_reward'      => 390,
            'level_required'  => 6,
            'trigger_type'    => 'earn_badge',
            'trigger_value'   => '',
            'trigger_label'   => 'Earn any badge',
            'lesson'          => 'Badges are just a way of tracking consistency — the same idea behind a real credit score.',
            'sort_order'      => 63,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Story Choices Matter'], [
            'description'    => 'Make a choice in any Life Decision story — every scenario is a small rehearsal for a real one someday.',
            'icon'            => '📖',
            'xp_reward'       => 310,
            'kes_reward'      => 370,
            'level_required'  => 6,
            'trigger_type'    => 'play_scenario',
            'trigger_value'   => '',
            'trigger_label'   => 'Resolve any Life Decision scenario',
            'lesson'          => 'Practicing money decisions in a safe story is far cheaper than learning the same lesson with real cash.',
            'sort_order'      => 64,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Triple Play'], [
            'description'    => 'Buy a vehicle asset, then keep Ksh 7,000 steady in your wallet — owning something big doesn\'t mean spending everything else.',
            'icon'            => '🏍️',
            'xp_reward'       => 480,
            'kes_reward'      => 560,
            'level_required'  => 6,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'buy_item_category', 'values' => ['vehicle'], 'label' => 'Buy any vehicle asset'],
                ['type' => 'reach_balance',     'values' => ['7000'],    'label' => 'Grow your wallet to Ksh 7,000'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'A big purchase should never wipe out your whole cushion — always keep something in reserve.',
            'sort_order'      => 65,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        // ── Level 7 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Vehicle Value Lesson'], [
            'description'    => 'Buy any vehicle asset from the Marketplace and start putting it to work.',
            'icon'            => '🚲',
            'xp_reward'       => 380,
            'kes_reward'      => 480,
            'level_required'  => 7,
            'trigger_type'    => 'buy_item_category',
            'trigger_value'   => 'vehicle',
            'trigger_label'   => 'Buy any vehicle asset',
            'lesson'          => 'A vehicle can either earn you money or just cost you money — the difference is whether it pays for its own upkeep.',
            'sort_order'      => 70,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Equity Square Regular'], [
            'description'    => 'Invest in an Equity Square deal, then push your net worth past Ksh 18,000 — investing regularly, not just once.',
            'icon'            => '🏛️',
            'xp_reward'       => 560,
            'kes_reward'      => 700,
            'level_required'  => 7,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'invest_deal',    'values' => [],        'label' => 'Invest in any Equity Square deal'],
                ['type' => 'reach_net_worth','values' => ['18000'], 'label' => 'Push your net worth past Ksh 18,000'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'Investing once is luck; investing again after seeing how it works is the start of a real habit.',
            'sort_order'      => 71,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Reach Level 8'], [
            'description'    => 'Push on to Level 8 — every level is proof your habits are actually working.',
            'icon'            => '⭐',
            'xp_reward'       => 400,
            'kes_reward'      => 500,
            'level_required'  => 7,
            'trigger_type'    => 'reach_level',
            'trigger_value'   => '8',
            'trigger_label'   => 'Reach Level 8',
            'lesson'          => 'Progress in the game mirrors progress with money — small, steady steps are what actually add up.',
            'sort_order'      => 72,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Wheel of Fortune Kid'], [
            'description'    => 'Take one more spin on the Lucky Wheel — just for fun, nothing riding on it.',
            'icon'            => '🎡',
            'xp_reward'       => 390,
            'kes_reward'      => 490,
            'level_required'  => 7,
            'trigger_type'    => 'spin_wheel',
            'trigger_value'   => '',
            'trigger_label'   => 'Spin the Lucky Wheel',
            'lesson'          => 'The fun of a small chance game is real — as long as it stays small and never becomes the plan.',
            'sort_order'      => 73,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Badge Collector II'], [
            'description'    => 'Earn another badge — one is a good start, but a collection shows the habit has really stuck.',
            'icon'            => '🎖️',
            'xp_reward'       => 410,
            'kes_reward'      => 510,
            'level_required'  => 7,
            'trigger_type'    => 'earn_badge',
            'trigger_value'   => '',
            'trigger_label'   => 'Earn any badge',
            'lesson'          => 'One good result could be luck. A pattern of good results is a skill.',
            'sort_order'      => 74,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Grand Finale L7'], [
            'description'    => "Prove your Business Starter Kit is paying off: keep it, and grow your total savings to Ksh 15,000.",
            'icon'            => '🏆',
            'xp_reward'       => 560,
            'kes_reward'      => 700,
            'level_required'  => 7,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'complete_quest', 'values' => [(string) $businessStarterKit->id], 'label' => 'Have completed "Business Starter Kit"'],
                ['type' => 'reach_savings',  'values' => ['15000'],                          'label' => 'Get your total savings to Ksh 15,000'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'The biggest financial wins rarely come from one clever move — they come from old habits compounding quietly.',
            'sort_order'      => 75,
            'is_active'       => true,
            'age_group'       => '8-12',
        ]);
    }
}
