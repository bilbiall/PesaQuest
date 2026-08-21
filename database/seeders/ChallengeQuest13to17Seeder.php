<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * See ChallengeQuest8to12Seeder for the full mechanic explanation. Same
 * 12-quest-per-level blueprint (single-trigger at L1, OR/AND 2-combos at
 * L2, 3-trigger AND combos + capstone at L3), amounts scaled to 13-17's
 * existing bill/deal economy (roughly 2.5x the 8-12 tier).
 */
class ChallengeQuest13to17Seeder extends Seeder
{
    private const AGE_GROUP = '13-17';

    private const QUESTS = [

        // ══════════════════════════════════ LEVEL 1 — single trigger ══════
        ['title' => 'First Real Net Worth', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '📈',
            'desc' => 'Grow your net worth to Ksh 1,500 — everything you own, minus anything you owe.',
            'lesson' => 'Net worth is the number that actually matters — not just what\'s in your pocket today.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['1500']]]],
        ['title' => 'Shares Profit Debut', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💹',
            'desc' => 'Sell a share for a Ksh 100 profit — your first proof that trading can actually work.',
            'lesson' => 'One profitable trade doesn\'t make you a pro, but it proves the mechanics work.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['100']]]],
        ['title' => 'Pesa Trail Rookie', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎲',
            'desc' => 'Win your first game of Pesa Trail against the odds on the board.',
            'lesson' => 'Board games with money rules are a safe place to make your first bad decisions.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['1']]]],
        ['title' => 'Ksh 800 Saved', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🐷',
            'desc' => 'Grow your savings scheme balance to Ksh 800.',
            'lesson' => 'Ksh 800 sounds small until you realize it\'s pocket money you didn\'t blow on snacks.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_savings', 'values' => ['800']]]],
        ['title' => 'Open a Savings Plan', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏦',
            'desc' => 'Open a savings scheme — separate from your everyday spending money.',
            'lesson' => 'Money in a separate account is money your future self actually gets to keep.',
            'mode' => 'all', 'triggers' => [['type' => 'open_savings']]],
        ['title' => 'One Solid Deposit', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💰',
            'desc' => 'Deposit Ksh 400 into your savings in one go.',
            'lesson' => 'One disciplined deposit beats ten "I\'ll save later" promises.',
            'mode' => 'all', 'triggers' => [['type' => 'deposit_savings', 'values' => ['400']]]],
        ['title' => 'Chama Membership Card', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🤝',
            'desc' => 'Join a chama — a group where everyone contributes and everyone benefits.',
            'lesson' => 'A chama makes saving a group habit — harder to skip when your mtu are counting on you.',
            'mode' => 'all', 'triggers' => [['type' => 'join_chama']]],
        ['title' => 'First Spin of the Wheel', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎡',
            'desc' => 'Take your first spin on the Pesa City wheel.',
            'lesson' => 'Luck-based money — like a spin, a raffle, a bet — is entertainment, not a plan.',
            'mode' => 'all', 'triggers' => [['type' => 'spin_wheel']]],
        ['title' => 'Course Certificate #1', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎓',
            'desc' => 'Finish any course at the Opportunity Hub.',
            'lesson' => 'A certificate today is leverage for a better job tomorrow.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course']]],
        ['title' => 'First Payslip', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💼',
            'desc' => 'Get hired for your first job in Pesa City.',
            'lesson' => 'Your first job is rarely your best-paying one — it\'s the one that gets you experience.',
            'mode' => 'all', 'triggers' => [['type' => 'get_job']]],
        ['title' => 'First Gadget Purchase', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🛍️',
            'desc' => 'Buy your first gadget from the marketplace — after saving up, not on a whim.',
            'lesson' => 'Planning a purchase in advance beats impulse-buying every single time.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_category', 'values' => ['gadget']]]],
        ['title' => 'First Badge Earned', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏅',
            'desc' => 'Earn your very first badge.',
            'lesson' => 'Badges track real actions — collect them and you\'re also collecting habits.',
            'mode' => 'all', 'triggers' => [['type' => 'earn_badge']]],

        // ══════════════════════════════════ LEVEL 2 — OR/AND combos begin ══
        ['title' => 'Net Worth Ksh 4,000', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📈',
            'desc' => 'Push your net worth to Ksh 4,000.',
            'lesson' => 'Net worth climbs fastest when you cut a bad habit and grow a good one at the same time.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['4000']]]],
        ['title' => 'Ksh 250 Trading Profit', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💹',
            'desc' => 'Bank Ksh 250 in total shares profit.',
            'lesson' => 'Consistent small trades usually beat one risky big bet over time.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['250']]]],
        ['title' => 'Two Trail Wins', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🎲',
            'desc' => 'Win 2 games of Pesa Trail.',
            'lesson' => 'Two wins in a row means you\'re reading the board, not just rolling dice.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']]]],
        ['title' => 'Ksh 1,800 Cash Buffer', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💵',
            'desc' => 'Keep Ksh 1,800 in your wallet balance at once.',
            'lesson' => 'A cash buffer means a surprise cost doesn\'t wreck your whole week.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['1800']]]],
        ['title' => 'Laptop Upgrade', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🛍️',
            'desc' => 'Buy the HP 250 laptop from the marketplace — schoolwork and side-hustle ready.',
            'lesson' => 'A tool that helps you earn or study pays for itself over time — a pure expense doesn\'t.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_slug', 'values' => ['laptop-hp-250']]]],
        ['title' => 'First Shareholder Move', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📊',
            'desc' => 'Buy your first share on the Pesa City Market.',
            'lesson' => 'Even a teenager can be a shareholder — ownership isn\'t about age, it\'s about starting.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_share']]],
        ['title' => 'Equity Square Debut', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🏗️',
            'desc' => 'Put money into an Equity Square deal.',
            'lesson' => 'Every deal has a risk level printed on it for a reason — read it before you commit.',
            'mode' => 'all', 'triggers' => [['type' => 'invest_deal']]],
        ['title' => 'Trail Wins or Trading Wins', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either win 2 games of Pesa Trail, or bank Ksh 250 in shares profit.',
            'lesson' => 'Different strengths lead to the same destination — money grown is money grown.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']], ['type' => 'shares_profit', 'values' => ['250']]]],
        ['title' => 'Save Big or Save Steady', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 2,000, or make one Ksh 800 top-up in a single deposit.',
            'lesson' => 'A big push and slow-and-steady saving both work — pick whichever fits your week.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['2000']], ['type' => 'deposit_savings', 'values' => ['800']]]],
        ['title' => 'Chama or Equity Square', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either join a chama, or put money into an Equity Square deal.',
            'lesson' => 'Group saving and solo investing are two different tools for the same job.',
            'mode' => 'any', 'triggers' => [['type' => 'join_chama'], ['type' => 'invest_deal']]],
        ['title' => 'Growth With a Cushion', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Reach Ksh 4,000 net worth AND keep Ksh 1,800 cash on hand at the same time.',
            'lesson' => 'Growing wealth while keeping real cash available is the actual balancing act.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['4000']], ['type' => 'reach_balance', 'values' => ['1800']]]],
        ['title' => 'From Class to Cashflow', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Finish a course AND get hired for a job.',
            'lesson' => 'A certificate is potential — a payslip is proof it turned into something real.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job']]],

        // ══════════════════════════════════ LEVEL 3 — 3-trigger combos, capstone ══
        ['title' => 'Net Worth Ksh 8,000', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '📈',
            'desc' => 'Push your net worth past Ksh 8,000.',
            'lesson' => 'Every jump in net worth is last month\'s good decisions catching up to you.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['8000']]]],
        ['title' => 'Ksh 500 Profit Banked', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💹',
            'desc' => 'Bank Ksh 500 total in shares profit.',
            'lesson' => 'Knowing when to sell is a skill on its own — separate from knowing what to buy.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['500']]]],
        ['title' => 'Three Trail Wins', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '🎲',
            'desc' => 'Win 3 games of Pesa Trail.',
            'lesson' => 'Consistency across three wins is a pattern, not a coincidence.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']]]],
        ['title' => 'Ksh 3,500 Cash Reserve', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💵',
            'desc' => 'Hold Ksh 3,500 in your wallet balance at once.',
            'lesson' => 'A real cash reserve means an emergency stays an inconvenience, not a crisis.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['3500']]]],
        ['title' => 'Trade It or Invest It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either bank Ksh 500 in shares profit, or put money into an Equity Square deal.',
            'lesson' => 'The goal is growth — the specific vehicle you use to get there matters less.',
            'mode' => 'any', 'triggers' => [['type' => 'shares_profit', 'values' => ['500']], ['type' => 'invest_deal']]],
        ['title' => 'Win It or Join It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either win 3 Pesa Trail games, or join a chama.',
            'lesson' => 'Solo wins and group commitment both build financial confidence, just differently.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => 'Spend Sharp or Study More', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either buy a gadget from the marketplace, or finish another course.',
            'lesson' => 'Spending on yourself and investing in yourself aren\'t opposites — both can pay off.',
            'mode' => 'any', 'triggers' => [['type' => 'buy_item_category', 'values' => ['gadget']], ['type' => 'take_course']]],
        ['title' => 'Save It or Grow It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 4,000, or push your net worth to Ksh 8,000.',
            'lesson' => 'Cash saved and net worth grown are related, but hitting one doesn\'t guarantee the other.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['4000']], ['type' => 'reach_net_worth', 'values' => ['8000']]]],
        ['title' => 'Sharp Investor', 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Reach Ksh 8,000 net worth AND bank Ksh 500 in shares profit.',
            'lesson' => 'Growing overall wealth while also nailing individual trades is what separates the sharp from the lucky.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['8000']], ['type' => 'shares_profit', 'values' => ['500']]]],
        ['title' => 'Team Player, Solo Winner', 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Win 3 Pesa Trail games AND join a chama.',
            'lesson' => 'Real financial skill shows up both alone and in a group — you need both.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => 'Job-Ready Teen', 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Finish a course, get hired, AND keep Ksh 3,500 cash on hand — the full package.',
            'lesson' => 'Skill, income and savings reinforce each other — weak in one, weak overall.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job'], ['type' => 'reach_balance', 'values' => ['3500']]]],
        ['title' => 'Money Hat-Trick', 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Reach Ksh 8,000 net worth, bank Ksh 500 in shares profit, AND win 3 Pesa Trail games.',
            'lesson' => 'Being good at more than one money skill at once is what real financial confidence looks like.',
            'mode' => 'all', 'triggers' => [
                ['type' => 'reach_net_worth', 'values' => ['8000']],
                ['type' => 'shares_profit', 'values' => ['500']],
                ['type' => 'arcade_wins', 'values' => ['3']],
            ]],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::QUESTS as $i => $q) {
            Quest::updateOrCreate(
                ['title' => $q['title'], 'age_group' => self::AGE_GROUP],
                [
                    'description'    => $q['desc'],
                    'lesson'         => $q['lesson'],
                    'icon'           => $q['icon'],
                    'xp_reward'      => $q['xp'],
                    'kes_reward'     => $q['kes'],
                    'level_required' => $q['level'],
                    'age_group'      => self::AGE_GROUP,
                    'trigger_mode'   => $q['mode'],
                    'triggers'       => $q['triggers'],
                    'source'         => 'challenge_quest',
                    'is_active'      => true,
                    'sort_order'     => 500 + $i,
                ]
            );
            $created++;
        }

        $this->command->info("ChallengeQuest13to17Seeder: {$created} quests upserted (" . count(self::QUESTS) . ' total, levels 1-3).');
    }
}
