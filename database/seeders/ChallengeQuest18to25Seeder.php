<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * See ChallengeQuest8to12Seeder for the full mechanic explanation. Same
 * 12-quest-per-level blueprint, amounts scaled to 18-25's real hustle
 * economy (jobs, deals, vehicles at this tier cost real money).
 */
class ChallengeQuest18to25Seeder extends Seeder
{
    private const AGE_GROUP = '18-25';

    private const QUESTS = [

        // ══════════════════════════════════ LEVEL 1 — single trigger ══════
        ['title' => 'Net Worth: First Ksh 6K', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '📈',
            'desc' => 'Grow your net worth to Ksh 6,000 — everything you own, minus anything you owe.',
            'lesson' => 'Net worth is the real scoreboard once you\'re out here hustling for yourself.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['6000']]]],
        ['title' => 'First Trading Win', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💹',
            'desc' => 'Sell a share for a Ksh 500 profit — your first real trading win.',
            'lesson' => 'One good trade proves you can read the market — it doesn\'t prove you always will.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['500']]]],
        ['title' => 'Pesa Trail Debut Win', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎲',
            'desc' => 'Win your first game of Pesa Trail.',
            'lesson' => 'A board game is a cheap place to learn risk before real chapaa is on the line.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['1']]]],
        ['title' => 'Ksh 3,000 Saved', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🐷',
            'desc' => 'Grow your savings scheme balance to Ksh 3,000.',
            'lesson' => 'A first Ksh 3,000 saved matters more than the amount — it proves you actually can.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_savings', 'values' => ['3000']]]],
        ['title' => 'Open a Savings Plan', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏦',
            'desc' => 'Open a savings scheme — separate from your everyday M-PESA float.',
            'lesson' => 'Money mixed in with your daily spending float always finds a reason to get spent.',
            'mode' => 'all', 'triggers' => [['type' => 'open_savings']]],
        ['title' => 'One Real Deposit', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💰',
            'desc' => 'Deposit Ksh 1,500 into your savings in one go.',
            'lesson' => 'One disciplined move often does more than weeks of "I\'ll save what\'s left."',
            'mode' => 'all', 'triggers' => [['type' => 'deposit_savings', 'values' => ['1500']]]],
        ['title' => 'Chama Card Holder', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🤝',
            'desc' => 'Join a chama — group saving with people who\'ll actually hold you accountable.',
            'lesson' => 'A chama works because your mtu are watching — peer pressure, but for good.',
            'mode' => 'all', 'triggers' => [['type' => 'join_chama']]],
        ['title' => 'First Wheel Spin', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎡',
            'desc' => 'Take your first spin on the Pesa City wheel.',
            'lesson' => 'A spin is fun once in a while — never mistake it for a financial plan.',
            'mode' => 'all', 'triggers' => [['type' => 'spin_wheel']]],
        ['title' => 'First Certificate', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎓',
            'desc' => 'Finish any course at the Opportunity Hub.',
            'lesson' => 'A certificate is leverage — the CV line that gets your call picked up.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course']]],
        ['title' => 'First Payday', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💼',
            'desc' => 'Get hired for your first job in Pesa City.',
            'lesson' => 'Your first payday is proof of income — build from there, don\'t judge by it.',
            'mode' => 'all', 'triggers' => [['type' => 'get_job']]],
        ['title' => 'First Wheels', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🛍️',
            'desc' => 'Buy your first vehicle from the marketplace — after saving up for it.',
            'lesson' => 'A vehicle bought outright beats one financed beyond what you can actually service.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_category', 'values' => ['vehicle']]]],
        ['title' => 'First Badge', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏅',
            'desc' => 'Earn your very first badge.',
            'lesson' => 'Every badge is a real money habit you\'ve already proven, not just decoration.',
            'mode' => 'all', 'triggers' => [['type' => 'earn_badge']]],

        // ══════════════════════════════════ LEVEL 2 — OR/AND combos begin ══
        ['title' => 'Net Worth: Ksh 15K', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📈',
            'desc' => 'Push your net worth to Ksh 15,000.',
            'lesson' => 'Net worth compounds faster once income, savings and smart spending all pull the same direction.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['15000']]]],
        ['title' => 'Ksh 1,200 Trading Profit', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💹',
            'desc' => 'Bank Ksh 1,200 in total shares profit.',
            'lesson' => 'Consistent trading profit over time beats one lucky spike almost every time.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['1200']]]],
        ['title' => 'Two Trail Wins', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🎲',
            'desc' => 'Win 2 games of Pesa Trail.',
            'lesson' => 'Winning twice means you\'re making the read, not just getting good dice.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']]]],
        ['title' => 'Ksh 6,000 Cash Buffer', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💵',
            'desc' => 'Keep Ksh 6,000 in your wallet balance at once.',
            'lesson' => 'A real cash buffer means an emergency doesn\'t force you into a bad loan.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['6000']]]],
        ['title' => 'Boda Boxer Buy', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🛍️',
            'desc' => 'Buy the Bajaj Boxer 150cc from the marketplace — an affordable, working motorbike.',
            'lesson' => 'A vehicle that can also earn (a boda gig) pays for itself differently than one that only spends.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_slug', 'values' => ['bajaj-boxer-150']]]],
        ['title' => 'Shareholder Card', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📊',
            'desc' => 'Buy your first share on the Pesa City Market.',
            'lesson' => 'Owning shares while you\'re young gives your money the most time to compound.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_share']]],
        ['title' => 'Equity Square Debut', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🏗️',
            'desc' => 'Put money into an Equity Square deal.',
            'lesson' => 'Every deal lists its risk level for a reason — read it like it\'s your own money, because it is.',
            'mode' => 'all', 'triggers' => [['type' => 'invest_deal']]],
        ['title' => 'Board Wins or Market Wins', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either win 2 games of Pesa Trail, or bank Ksh 1,200 in shares profit.',
            'lesson' => 'Two different skill sets, same outcome — growing money however fits you.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']], ['type' => 'shares_profit', 'values' => ['1200']]]],
        ['title' => 'Big Save or Steady Save', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 7,000, or make one Ksh 3,000 top-up in a single deposit.',
            'lesson' => 'A big deposit or a steady habit both build the same savings muscle.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['7000']], ['type' => 'deposit_savings', 'values' => ['3000']]]],
        ['title' => 'Chama or Deal', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either join a chama, or put money into an Equity Square deal.',
            'lesson' => 'Group saving and solo investing both grow wealth — they just carry different kinds of risk.',
            'mode' => 'any', 'triggers' => [['type' => 'join_chama'], ['type' => 'invest_deal']]],
        ['title' => 'Growth Plus Buffer', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Reach Ksh 15,000 net worth AND keep Ksh 6,000 cash on hand at the same time.',
            'lesson' => 'Growing on paper while staying liquid in real life is the actual skill.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['15000']], ['type' => 'reach_balance', 'values' => ['6000']]]],
        ['title' => 'Certificate to Chapaa', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Finish a course AND get hired for a job.',
            'lesson' => 'A certificate is a claim about your skill — a payslip is the proof.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job']]],

        // ══════════════════════════════════ LEVEL 3 — 3-trigger combos, capstone ══
        ['title' => 'Net Worth: Ksh 30K', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '📈',
            'desc' => 'Push your net worth past Ksh 30,000.',
            'lesson' => 'By this stage, net worth is compounding — small wins are stacking on top of each other.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['30000']]]],
        ['title' => 'Ksh 2,500 Profit Banked', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💹',
            'desc' => 'Bank Ksh 2,500 total in shares profit.',
            'lesson' => 'Selling at the right time is its own separate skill from picking the right stock.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['2500']]]],
        ['title' => 'Hat-Trick on the Trail', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '🎲',
            'desc' => 'Win 3 games of Pesa Trail.',
            'lesson' => 'Three wins is a pattern, not a coincidence — you\'re actually reading the game now.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']]]],
        ['title' => 'Ksh 12,000 Cash Reserve', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💵',
            'desc' => 'Hold Ksh 12,000 in your wallet balance at once.',
            'lesson' => 'A real reserve is the difference between an emergency and a mild inconvenience.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['12000']]]],
        ['title' => 'Trade It or Invest It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either bank Ksh 2,500 in shares profit, or put money into an Equity Square deal.',
            'lesson' => 'Chasing growth through more than one channel is smarter than betting on just one.',
            'mode' => 'any', 'triggers' => [['type' => 'shares_profit', 'values' => ['2500']], ['type' => 'invest_deal']]],
        ['title' => 'Win It or Join It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either win 3 Pesa Trail games, or join a chama.',
            'lesson' => 'Solo skill and group discipline are both real financial muscles.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => 'Ride or Learn', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either buy a vehicle from the marketplace, or finish another course.',
            'lesson' => 'A vehicle can earn you money and a course can raise your ceiling — both are investments in yourself.',
            'mode' => 'any', 'triggers' => [['type' => 'buy_item_category', 'values' => ['vehicle']], ['type' => 'take_course']]],
        ['title' => 'Bank It or Grow It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 15,000, or push your net worth to Ksh 30,000.',
            'lesson' => 'Cash saved and total net worth move together, but they\'re not the exact same race.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['15000']], ['type' => 'reach_net_worth', 'values' => ['30000']]]],
        ['title' => 'Sharp Investor', 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Reach Ksh 30,000 net worth AND bank Ksh 2,500 in shares profit.',
            'lesson' => 'Growing your total wealth while also nailing individual trades is what separates sharp from lucky.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['30000']], ['type' => 'shares_profit', 'values' => ['2500']]]],
        ['title' => 'Table and Trail', 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Win 3 Pesa Trail games AND join a chama.',
            'lesson' => 'Real financial confidence shows up both alone at the table and inside a group.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => 'Employable and Liquid', 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Finish a course, get hired, AND keep Ksh 12,000 cash on hand — the full package.',
            'lesson' => 'Skills, income and liquidity reinforce each other — weak in one drags the others down.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job'], ['type' => 'reach_balance', 'values' => ['12000']]]],
        ['title' => 'The Full Hustle', 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Reach Ksh 30,000 net worth, bank Ksh 2,500 in shares profit, AND win 3 Pesa Trail games.',
            'lesson' => 'The full hustle is being good at more than one money game at the same time — that\'s real confidence.',
            'mode' => 'all', 'triggers' => [
                ['type' => 'reach_net_worth', 'values' => ['30000']],
                ['type' => 'shares_profit', 'values' => ['2500']],
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

        $this->command->info("ChallengeQuest18to25Seeder: {$created} quests upserted (" . count(self::QUESTS) . ' total, levels 1-3).');
    }
}
