<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

class QuestExpansion8to12Band3Seeder extends Seeder
{
    public function run(): void
    {
        // ── Level 8 ──────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Junior Shareholder'], [
            'title'         => 'Junior Shareholder',
            'icon'          => '📈',
            'description'   => 'Buy your very first share on the Pesa City exchange. Owning even one share makes you a business owner.',
            'lesson'        => 'A share is a tiny slice of a real company. When the company grows, your slice grows with it.',
            'xp_reward'     => 400,
            'kes_reward'    => 550,
            'level_required'=> 8,
            'trigger_type'  => 'buy_share',
            'trigger_value' => null,
            'trigger_label' => 'Buy any share',
            'sort_order'    => 801,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Small Business Owner'], [
            'title'         => 'Small Business Owner',
            'icon'          => '🏪',
            'description'   => 'Buy a business-category asset from the Marketplace — your first real piece of a working enterprise.',
            'lesson'        => 'A business asset earns money for you even while you sleep. That is very different from a toy that only costs money.',
            'xp_reward'     => 420,
            'kes_reward'    => 580,
            'level_required'=> 8,
            'trigger_type'  => 'buy_item_category',
            'trigger_value' => 'business',
            'trigger_label' => 'Buy any business asset',
            'sort_order'    => 802,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Twenty Thousand Club'], [
            'title'         => 'Twenty Thousand Club',
            'icon'          => '🏦',
            'description'   => 'Grow your total savings to Ksh 20,000. A serious number for a serious saver.',
            'lesson'        => 'Ksh 20,000 saved is more than most Kenyan adults have set aside. You are ahead already.',
            'xp_reward'     => 470,
            'kes_reward'    => 650,
            'level_required'=> 8,
            'trigger_type'  => 'reach_savings',
            'trigger_value' => '20000',
            'trigger_label' => 'Reach Ksh 20,000 in total savings',
            'sort_order'    => 803,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Spin It Forward'], [
            'title'         => 'Spin It Forward',
            'icon'          => '🎡',
            'description'   => 'Spin the Lucky Wheel, then decide what to do with whatever you land — spend it, or add it to savings.',
            'lesson'        => 'A windfall is a test, not a prize. What you do right after matters more than the amount itself.',
            'xp_reward'     => 400,
            'kes_reward'    => 550,
            'level_required'=> 8,
            'trigger_type'  => 'spin_wheel',
            'trigger_value' => null,
            'trigger_label' => 'Spin the Lucky Wheel',
            'sort_order'    => 804,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        $funWorldFoundations = Quest::updateOrCreate(['title' => 'Fun World Foundations'], [
            'title'         => 'Fun World Foundations',
            'icon'          => '🎮',
            'description'   => 'Spend some of your money enjoying Fun World — fun is part of a healthy budget too, in moderation.',
            'lesson'        => 'Budgets fail when they leave no room for fun at all. A small, planned "fun fund" makes saving sustainable.',
            'xp_reward'     => 400,
            'kes_reward'    => 550,
            'level_required'=> 8,
            'trigger_type'  => 'fun_world_spend',
            'trigger_value' => null,
            'trigger_label' => 'Spend on any Fun World activity',
            'sort_order'    => 805,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Level 8 Legend'], [
            'title'         => 'Level 8 Legend',
            'icon'          => '🏅',
            'description'   => 'Prove Fun World Foundations was just the start: complete it, then push your net worth past Ksh 25,000.',
            'lesson'        => 'Balancing fun spending with growing net worth at the same time is the real financial skill — not choosing one over the other.',
            'xp_reward'     => 660,
            'kes_reward'    => 910,
            'level_required'=> 8,
            'trigger_type'  => null,
            'trigger_value' => null,
            'trigger_label' => null,
            'triggers'      => [
                ['type' => 'complete_quest',  'values' => [(string) $funWorldFoundations->id], 'label' => 'Have completed "Fun World Foundations"'],
                ['type' => 'reach_net_worth', 'values' => ['25000'], 'label' => 'Push your net worth past Ksh 25,000'],
            ],
            'trigger_mode'  => 'all',
            'sort_order'    => 806,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        // ── Level 9 ──────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Second Share, Second Lesson'], [
            'title'         => 'Second Share, Second Lesson',
            'icon'          => '📊',
            'description'   => 'Buy a share and grow your wallet balance to Ksh 5,000 in the same stretch — prove you can invest and still keep cash on hand.',
            'lesson'        => 'Smart investors never put every shilling into one place. Keep some cash spare for real life.',
            'xp_reward'     => 770, 'kes_reward' => 1120,
            'level_required'=> 9,
            'trigger_type'  => null, 'trigger_value' => null, 'trigger_label' => null,
            'triggers'      => [
                ['type' => 'buy_share',     'values' => [], 'label' => 'Buy any share'],
                ['type' => 'reach_balance', 'values' => ['5000'], 'label' => 'Grow your wallet to Ksh 5,000'],
            ],
            'trigger_mode'  => 'all',
            'sort_order'    => 901,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Deal Maker Junior'], [
            'title'         => 'Deal Maker Junior',
            'icon'          => '🤝',
            'description'   => 'Put money into any Equity Square investment deal. Bigger opportunities need bigger trust.',
            'lesson'        => 'An investment deal is a bet on a business plan, not a guaranteed win. Only invest what you can afford to be patient about.',
            'xp_reward'     => 500,
            'kes_reward'    => 700,
            'level_required'=> 9,
            'trigger_type'  => 'invest_deal',
            'trigger_value' => null,
            'trigger_label' => 'Invest in any Equity Square deal',
            'sort_order'    => 902,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Property Ladder, First Rung'], [
            'title'         => 'Property Ladder, First Rung',
            'icon'          => '🏠',
            'description'   => 'Buy any property-category asset from the Marketplace.',
            'lesson'        => 'Property is one of the oldest ways families build lasting wealth — it rarely disappears overnight the way cash can.',
            'xp_reward'     => 520,
            'kes_reward'    => 720,
            'level_required'=> 9,
            'trigger_type'  => 'buy_item_category',
            'trigger_value' => 'property',
            'trigger_label' => 'Buy any property asset',
            'sort_order'    => 903,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => "A Shilling's Tale"], [
            'title'         => "A Shilling's Tale",
            'icon'          => '📖',
            'description'   => 'Play through any life-decision scenario in Pesa City and see where your choice leads.',
            'lesson'        => 'Every money decision is really a small story with consequences — the game lets you see them play out safely.',
            'xp_reward'     => 480,
            'kes_reward'    => 0,
            'level_required'=> 9,
            'trigger_type'  => 'play_scenario',
            'trigger_value' => null,
            'trigger_label' => 'Resolve any life-decision scenario',
            'sort_order'    => 904,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Thirty Thousand Milestone'], [
            'title'         => 'Thirty Thousand Milestone',
            'icon'          => '💰',
            'description'   => 'Push your total savings to Ksh 30,000.',
            'lesson'        => 'The bigger your savings buffer, the fewer emergencies can knock you off course.',
            'xp_reward'     => 550,
            'kes_reward'    => 800,
            'level_required'=> 9,
            'trigger_type'  => 'reach_savings',
            'trigger_value' => '30000',
            'trigger_label' => 'Reach Ksh 30,000 in total savings',
            'sort_order'    => 905,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Gadget With a Purpose'], [
            'title'         => 'Gadget With a Purpose',
            'icon'          => '🎧',
            'description'   => 'Buy any gadget-category asset — one that could actually help you earn, not just entertain.',
            'lesson'        => 'A gadget bought to help you earn (like recording gear or a bike) is an investment. A gadget bought only for fun is a want.',
            'xp_reward'     => 500,
            'kes_reward'    => 700,
            'level_required'=> 9,
            'trigger_type'  => 'buy_item_category',
            'trigger_value' => 'gadget',
            'trigger_label' => 'Buy any gadget asset',
            'sort_order'    => 906,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        // ── Level 10 ─────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Opening the Portfolio'], [
            'title'         => 'Opening the Portfolio',
            'icon'          => '📂',
            'description'   => 'Own at least two different kinds of investments at once: buy a share AND put money in a fixed-income asset like a Money Market Fund.',
            'lesson'        => 'Spreading your money across different investment types is called diversification — it keeps one bad day from wiping you out.',
            'xp_reward'     => 910, 'kes_reward' => 1400,
            'level_required'=> 10,
            'trigger_type'  => null, 'trigger_value' => null, 'trigger_label' => null,
            'triggers'      => [
                ['type' => 'buy_share',          'values' => [], 'label' => 'Buy any share'],
                ['type' => 'buy_item_category',  'values' => ['fixed_income'], 'label' => 'Buy any fixed-income asset (MMF, T-Bill, T-Bond)'],
            ],
            'trigger_mode'  => 'all',
            'sort_order'    => 1001,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Forty Thousand Fortress'], [
            'title'         => 'Forty Thousand Fortress',
            'icon'          => '🏰',
            'description'   => 'Reach Ksh 40,000 in total savings — a fortress big enough to weather almost anything.',
            'lesson'        => 'By this point saving isn\'t about willpower anymore — it\'s a habit that runs almost on its own.',
            'xp_reward'     => 650,
            'kes_reward'    => 1000,
            'level_required'=> 10,
            'trigger_type'  => 'reach_savings',
            'trigger_value' => '40000',
            'trigger_label' => 'Reach Ksh 40,000 in total savings',
            'sort_order'    => 1002,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Vehicle of Ambition'], [
            'title'         => 'Vehicle of Ambition',
            'icon'          => '🚲',
            'description'   => 'Buy any vehicle-category asset — something that could one day carry goods, passengers, or you to work.',
            'lesson'        => 'A vehicle can be a cost that drains you every month, or an asset that pays for itself — the difference is whether it earns you money too.',
            'xp_reward'     => 620,
            'kes_reward'    => 950,
            'level_required'=> 10,
            'trigger_type'  => 'buy_item_category',
            'trigger_value' => 'vehicle',
            'trigger_label' => 'Buy any vehicle asset',
            'sort_order'    => 1003,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Recognised Achiever'], [
            'title'         => 'Recognised Achiever',
            'icon'          => '🏅',
            'description'   => 'Earn any badge in Pesa City — recognition that your habits are being noticed.',
            'lesson'        => 'Badges are a nice bonus, but the real reward is the habit that earned it in the first place.',
            'xp_reward'     => 600,
            'kes_reward'    => 900,
            'level_required'=> 10,
            'trigger_type'  => 'earn_badge',
            'trigger_value' => null,
            'trigger_label' => 'Earn any badge',
            'sort_order'    => 1004,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Chama Founder'], [
            'title'         => 'Chama Founder',
            'icon'          => '👥',
            'description'   => 'Join a chama and start pooling money with others the Kenyan way.',
            'lesson'        => 'A chama turns solo willpower into group accountability — it is much harder to skip a contribution when others are counting on you.',
            'xp_reward'     => 650,
            'kes_reward'    => 1000,
            'level_required'=> 10,
            'trigger_type'  => 'join_chama',
            'trigger_value' => null,
            'trigger_label' => 'Join a chama',
            'sort_order'    => 1005,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);

        Quest::updateOrCreate(['title' => 'Pesa City Graduate'], [
            'title'         => 'Pesa City Graduate',
            'icon'          => '🎓',
            'description'   => 'The final test: reach Ksh 50,000 net worth and level up one more time. Show the whole city what you\'ve built.',
            'lesson'        => 'Everything you\'ve learned — saving, investing, spending wisely — compounds together into real net worth. That is the whole game.',
            'xp_reward'     => 910, 'kes_reward' => 1400,
            'level_required'=> 10,
            'trigger_type'  => null, 'trigger_value' => null, 'trigger_label' => null,
            'triggers'      => [
                ['type' => 'reach_net_worth', 'values' => ['50000'], 'label' => 'Push your net worth past Ksh 50,000'],
                ['type' => 'reach_level',     'values' => ['11'],    'label' => 'Reach Level 11'],
            ],
            'trigger_mode'  => 'all',
            'sort_order'    => 1006,
            'is_active'     => true,
            'age_group'     => '8-12',
        ]);
    }
}
