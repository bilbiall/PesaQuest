<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * Tops up levels 4-7 for the 26+ age group to 8 quests each (2 already
 * exist from an earlier batch) — leans on real game activities (shares,
 * deals, assets, chama) instead of courses/jobs, per the Aug 2026
 * content-diversity pass.
 */
class QuestExpansion26PlusBand2Seeder extends Seeder
{
    public function run(): void
    {
        // ── Level 4 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Boma Foundation'], [
            'description'    => 'Push your total savings to Ksh 25,000 — the base every serious plan in Pesa City stands on.',
            'icon'           => '🏗️',
            'xp_reward'      => 220,
            'kes_reward'     => 200,
            'level_required' => 4,
            'trigger_type'   => 'reach_savings',
            'trigger_value'  => '25000',
            'trigger_label'  => 'Reach Ksh 25,000 in total savings',
            'lesson'         => 'A savings foundation lets you say yes to a good deal when it appears, instead of watching it pass you by.',
            'sort_order'     => 400,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'First Brick, First Rent'], [
            'description'    => 'Buy your first property-category asset, then keep Ksh 10,000 in your wallet on top of it — never own an asset with nothing left to run it.',
            'icon'           => '🧱',
            'xp_reward'      => 308,
            'kes_reward'     => 280,
            'level_required' => 4,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'buy_item_category', 'values' => ['property'], 'label' => 'Buy a property-category asset'],
                ['type' => 'reach_balance',      'values' => ['10000'],    'label' => 'Keep Ksh 10,000 in your wallet'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'Property income is real, but only after costs. Landlords who spend every shilling of rent the day it lands never survive a bad tenant month.',
            'sort_order'     => 401,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Shares in the Game'], [
            'description'    => 'Buy your first shares on the exchange — ownership, not just a paycheck.',
            'icon'           => '📊',
            'xp_reward'      => 220,
            'kes_reward'     => 200,
            'level_required' => 4,
            'trigger_type'   => 'buy_share',
            'trigger_value'  => '',
            'trigger_label'  => 'Buy any shares',
            'lesson'         => 'A salary pays you for time. A share pays you for owning a piece of a business that keeps working after you clock out.',
            'sort_order'     => 402,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Boardroom Seat'], [
            'description'    => 'Put money into any Equity Square deal — this is how real investors get a stake in a growing business.',
            'icon'           => '🤵',
            'xp_reward'      => 220,
            'kes_reward'     => 200,
            'level_required' => 4,
            'trigger_type'   => 'invest_deal',
            'trigger_value'  => '',
            'trigger_label'  => 'Invest in any Equity Square deal',
            'lesson'         => 'Equity investing means your return depends on the business doing well — you carry real risk, but also real upside, unlike a fixed salary.',
            'sort_order'     => 403,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Steady Hand at the Wheel'], [
            'description'    => 'Buy a vehicle-category asset, then keep Ksh 15,000 in your wallet — a boda or matatu only earns if you can also fuel and fix it.',
            'icon'           => '🏍️',
            'xp_reward'      => 308,
            'kes_reward'     => 280,
            'level_required' => 4,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'buy_item_category', 'values' => ['vehicle'],  'label' => 'Buy a vehicle-category asset'],
                ['type' => 'reach_balance',      'values' => ['15000'],   'label' => 'Keep Ksh 15,000 in your wallet'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'An income-generating vehicle is a business, not a toy — fuel, service, and insurance all come out of the same pocket before profit does.',
            'sort_order'     => 404,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Circle That Pays'], [
            'description'    => 'Join a chama — pooled discipline beats solo willpower almost every time.',
            'icon'           => '🤝',
            'xp_reward'      => 220,
            'kes_reward'     => 200,
            'level_required' => 4,
            'trigger_type'   => 'join_chama',
            'trigger_value'  => '',
            'trigger_label'  => 'Join a chama',
            'lesson'         => 'A chama makes skipping a contribution socially expensive — that peer pressure is a feature, not a bug.',
            'sort_order'     => 405,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        // ── Level 5 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Second Investment'], [
            'description'    => 'Buy shares a second time — one purchase is luck, a pattern is a strategy.',
            'icon'           => '📈',
            'xp_reward'      => 280,
            'kes_reward'     => 300,
            'level_required' => 5,
            'trigger_type'   => 'buy_share',
            'trigger_value'  => '',
            'trigger_label'  => 'Buy any shares',
            'lesson'         => 'Investing once is an experiment. Investing on a schedule, regardless of headlines, is how most real wealth is actually built.',
            'sort_order'     => 406,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Own the Means'], [
            'description'    => 'Buy a business-category asset — stop only working for income and start owning something that produces it.',
            'icon'           => '🏭',
            'xp_reward'      => 280,
            'kes_reward'     => 300,
            'level_required' => 5,
            'trigger_type'   => 'buy_item_category',
            'trigger_value'  => 'business',
            'trigger_label'  => 'Buy a business-category asset',
            'lesson'         => 'A job trades your hours for money. A business asset can earn money on hours you never worked.',
            'sort_order'     => 407,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Badge on the Wall'], [
            'description'    => 'Earn any badge in Pesa City — recognition that your habits are actually working.',
            'icon'           => '🏅',
            'xp_reward'      => 280,
            'kes_reward'     => 300,
            'level_required' => 5,
            'trigger_type'   => 'earn_badge',
            'trigger_value'  => '',
            'trigger_label'  => 'Earn any badge',
            'lesson'         => 'Badges are a side effect of good habits, not the goal — but noticing the side effect keeps you going.',
            'sort_order'     => 408,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Play It Out'], [
            'description'    => 'Work through any life-decision scenario — every choice teaches something before it costs you for real.',
            'icon'           => '🎭',
            'xp_reward'      => 280,
            'kes_reward'     => 300,
            'level_required' => 5,
            'trigger_type'   => 'play_scenario',
            'trigger_value'  => '',
            'trigger_label'  => 'Resolve any life-decision scenario',
            'lesson'         => 'A rehearsed decision is a faster, cheaper decision when the real version shows up.',
            'sort_order'     => 409,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Fun Money, Real Limits'], [
            'description'    => 'Spend on any Fun World activity — enjoyment has a place in a budget, as long as it has a limit too.',
            'icon'           => '🎡',
            'xp_reward'      => 280,
            'kes_reward'     => 300,
            'level_required' => 5,
            'trigger_type'   => 'fun_world_spend',
            'trigger_value'  => '',
            'trigger_label'  => 'Spend on any Fun World activity',
            'lesson'         => 'A "fun budget" isn\'t a weakness — it\'s what stops fun from quietly eating the savings budget instead.',
            'sort_order'     => 410,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Six-Figure Sights'], [
            'description'    => 'Push your net worth past Ksh 300,000 and your total savings past Ksh 50,000 in the same stretch — growth and a cushion, together.',
            'icon'           => '🎯',
            'xp_reward'      => 392,
            'kes_reward'     => 420,
            'level_required' => 5,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'reach_net_worth', 'values' => ['300000'], 'label' => 'Push net worth past Ksh 300,000'],
                ['type' => 'reach_savings',   'values' => ['50000'],  'label' => 'Reach Ksh 50,000 in total savings'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'Net worth without liquid savings is fragile — assets can\'t always be sold fast when an emergency needs cash today.',
            'sort_order'     => 411,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        // ── Level 6 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Bond With the Bank'], [
            'description'    => 'Buy into a Money Market Fund — the safest, most liquid step up from a plain savings account.',
            'icon'           => '💰',
            'xp_reward'      => 340,
            'kes_reward'     => 400,
            'level_required' => 6,
            'trigger_type'   => 'buy_item_slug',
            'trigger_value'  => 'money-market-fund',
            'trigger_label'  => 'Buy into a Money Market Fund',
            'lesson'         => 'An MMF pays several times a bank savings rate while staying accessible within days — there\'s little reason idle cash should sit anywhere less.',
            'sort_order'     => 412,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Spin, Then Save'], [
            'description'    => 'Spin the Lucky Wheel, then deposit Ksh 8,000 into savings — treat a windfall as fuel for the plan, not a reason to skip it.',
            'icon'           => '🎡',
            'xp_reward'      => 476,
            'kes_reward'     => 560,
            'level_required' => 6,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'spin_wheel',      'values' => [],      'label' => 'Spin the Lucky Wheel'],
                ['type' => 'deposit_savings', 'values' => ['8000'], 'label' => 'Deposit Ksh 8,000 into savings'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'Unearned money vanishes fastest of all, because it never had a plan attached to it — give every windfall a job the moment it lands.',
            'sort_order'     => 413,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Deal Maker'], [
            'description'    => 'Put money into a second Equity Square deal — diversifying beats betting everything on one business.',
            'icon'           => '🤝',
            'xp_reward'      => 340,
            'kes_reward'     => 400,
            'level_required' => 6,
            'trigger_type'   => 'invest_deal',
            'trigger_value'  => '',
            'trigger_label'  => 'Invest in any Equity Square deal',
            'lesson'         => 'Spreading investments across more than one deal means one bad outcome doesn\'t take your whole portfolio down with it.',
            'sort_order'     => 414,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Reach the Next Rung'], [
            'description'    => 'Push through to Level 7 — every level unlocks bigger jobs, bigger deals, and bigger consequences.',
            'icon'           => '🪜',
            'xp_reward'      => 340,
            'kes_reward'     => 400,
            'level_required' => 6,
            'trigger_type'   => 'reach_level',
            'trigger_value'  => '7',
            'trigger_label'  => 'Reach Level 7',
            'lesson'         => 'Progress compounds the same way money does — each level makes the next one a little easier to reach.',
            'sort_order'     => 415,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Grow the Fleet'], [
            'description'    => 'Buy a second vehicle-category asset — a working fleet earns more than a single unit, if you can afford to run it.',
            'icon'           => '🚚',
            'xp_reward'      => 340,
            'kes_reward'     => 400,
            'level_required' => 6,
            'trigger_type'   => 'buy_item_category',
            'trigger_value'  => 'vehicle',
            'trigger_label'  => 'Buy a vehicle-category asset',
            'lesson'         => 'Scaling a fleet multiplies both income and running costs — grow only as fast as your cash flow can actually service.',
            'sort_order'     => 416,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'The Ledger Grows'], [
            'description'    => 'Grow your wallet balance to Ksh 60,000 — cash on hand is what lets you move fast on the next good opportunity.',
            'icon'           => '💵',
            'xp_reward'      => 340,
            'kes_reward'     => 400,
            'level_required' => 6,
            'trigger_type'   => 'reach_balance',
            'trigger_value'  => '60000',
            'trigger_label'  => 'Grow your wallet to Ksh 60,000',
            'lesson'         => 'A healthy cash balance isn\'t idle money — it\'s optionality, ready to move the moment the right deal appears.',
            'sort_order'     => 417,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        // ── Level 7 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Bond With the Republic'], [
            'description'    => 'Buy into a 5-Year Treasury Bond, then deposit Ksh 40,000 into savings on the side — lock some money away, keep some liquid.',
            'icon'           => '📜',
            'xp_reward'      => 560,
            'kes_reward'     => 700,
            'level_required' => 7,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'buy_item_slug',   'values' => ['treasury-bonds-5yr'], 'label' => 'Buy into the 5-Year Treasury Bond'],
                ['type' => 'deposit_savings', 'values' => ['40000'],              'label' => 'Deposit Ksh 40,000 into savings'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'A 5-year bond pays a locked-in rate for patience — never put money into it that you might need before the term ends.',
            'sort_order'     => 418,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Play the City'], [
            'description'    => 'Work through another life-decision scenario — the choices keep getting harder as the stakes get bigger.',
            'icon'           => '🎭',
            'xp_reward'      => 400,
            'kes_reward'     => 500,
            'level_required' => 7,
            'trigger_type'   => 'play_scenario',
            'trigger_value'  => '',
            'trigger_label'  => 'Resolve any life-decision scenario',
            'lesson'         => 'The scenarios that feel repetitive are usually the ones teaching the habit that matters most.',
            'sort_order'     => 419,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Shares, Again'], [
            'description'    => 'Buy shares once more — consistency is what turns a hobby into a portfolio.',
            'icon'           => '📊',
            'xp_reward'      => 400,
            'kes_reward'     => 500,
            'level_required' => 7,
            'trigger_type'   => 'buy_share',
            'trigger_value'  => '',
            'trigger_label'  => 'Buy any shares',
            'lesson'         => 'Regularly buying shares regardless of the day\'s price — not trying to time the perfect moment — is how most long-term investors actually win.',
            'sort_order'     => 420,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Fixed and Certain'], [
            'description'    => 'Buy a fixed-income asset — MMF, Treasury Bill, or Treasury Bond — the calm, predictable corner of a portfolio.',
            'icon'           => '🏛️',
            'xp_reward'      => 400,
            'kes_reward'     => 500,
            'level_required' => 7,
            'trigger_type'   => 'buy_item_category',
            'trigger_value'  => 'fixed_income',
            'trigger_label'  => 'Buy any fixed-income asset',
            'lesson'         => 'Fixed income won\'t make you rich fast, but it\'s the ballast that keeps a portfolio steady when shares wobble.',
            'sort_order'     => 421,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'One Course, Sharpened'], [
            'description'    => 'Complete the Bookkeeping Basics course — you can\'t manage money you\'re not tracking properly.',
            'icon'           => '📒',
            'xp_reward'      => 400,
            'kes_reward'     => 500,
            'level_required' => 7,
            'trigger_type'   => 'take_course',
            'trigger_value'  => 'bookkeeping-basics',
            'trigger_label'  => 'Complete the Bookkeeping Basics course',
            'lesson'         => 'Every business that collapses "out of nowhere" usually had a ledger that stopped telling the truth months earlier.',
            'sort_order'     => 422,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);

        Quest::updateOrCreate(['title' => 'Capstone: Building the Estate'], [
            'description'    => 'Buy a property-category asset and push your net worth past Ksh 750,000 — real estate and real growth, together.',
            'icon'           => '🏘️',
            'xp_reward'      => 560,
            'kes_reward'     => 700,
            'level_required' => 7,
            'trigger_type'   => null,
            'trigger_value'  => null,
            'trigger_label'  => null,
            'triggers'       => [
                ['type' => 'buy_item_category', 'values' => ['property'],  'label' => 'Buy a property-category asset'],
                ['type' => 'reach_net_worth',    'values' => ['750000'],   'label' => 'Push net worth past Ksh 750,000'],
            ],
            'trigger_mode'   => 'all',
            'lesson'         => 'Property held alongside a growing net worth — not instead of one — is what turns an asset into real financial ground to stand on.',
            'sort_order'     => 423,
            'is_active'      => true,
            'age_group'      => '26+',
        ]);
    }
}
