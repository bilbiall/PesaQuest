<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * See ChallengeQuest8to12Seeder for the full mechanic explanation. Same
 * 12-quest-per-level blueprint, amounts scaled to 26+'s real economy
 * (family obligations, career-stage income, business-scale purchases).
 */
class ChallengeQuest26PlusSeeder extends Seeder
{
    private const AGE_GROUP = '26+';

    private const QUESTS = [

        // ══════════════════════════════════ LEVEL 1 — single trigger ══════
        ['title' => 'Net Worth: Ksh 15,000', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '📈',
            'desc' => 'Grow your net worth to Ksh 15,000 — everything you own, minus anything you owe.',
            'lesson' => 'Net worth, not salary, is the real measure once you have a household depending on you.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['15000']]]],
        ['title' => 'First Trading Profit', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💹',
            'desc' => 'Sell a share for a Ksh 1,200 profit — your first real trading win.',
            'lesson' => 'A first profitable trade is proof of concept, not a reason to bet the household budget.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['1200']]]],
        ['title' => 'Pesa Trail: First Win', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎲',
            'desc' => 'Win your first game of Pesa Trail.',
            'lesson' => 'Even as an adult, a low-stakes game is still the cheapest place to practice financial decisions.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['1']]]],
        ['title' => 'Ksh 8,000 Saved', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🐷',
            'desc' => 'Grow your savings scheme balance to Ksh 8,000.',
            'lesson' => 'This is the start of the emergency fund every financial advisor keeps insisting on.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_savings', 'values' => ['8000']]]],
        ['title' => 'Open a Savings Plan', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏦',
            'desc' => 'Open a savings scheme — separate from the account your bills come out of.',
            'lesson' => 'Savings mixed into your spending account gets spent — separation protects it from yourself.',
            'mode' => 'all', 'triggers' => [['type' => 'open_savings']]],
        ['title' => 'One Real Deposit', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💰',
            'desc' => 'Deposit Ksh 3,000 into your savings in one go.',
            'lesson' => 'One deliberate deposit builds the habit faster than "whatever\'s left at month-end."',
            'mode' => 'all', 'triggers' => [['type' => 'deposit_savings', 'values' => ['3000']]]],
        ['title' => 'Chama Membership', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🤝',
            'desc' => 'Join a chama — a group where everyone contributes and everyone benefits.',
            'lesson' => 'A chama\'s peer accountability often succeeds where personal willpower alone fails.',
            'mode' => 'all', 'triggers' => [['type' => 'join_chama']]],
        ['title' => 'First Wheel Spin', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎡',
            'desc' => 'Take your first spin on the Pesa City wheel.',
            'lesson' => 'A spin is a small thrill — never a substitute for a real financial plan.',
            'mode' => 'all', 'triggers' => [['type' => 'spin_wheel']]],
        ['title' => 'First Certificate', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎓',
            'desc' => 'Finish any course at the Opportunity Hub.',
            'lesson' => 'It\'s never too late in a career to add a skill that raises your ceiling.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course']]],
        ['title' => 'First Paycheck', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💼',
            'desc' => 'Get hired for your first job in Pesa City.',
            'lesson' => 'A steady paycheck is the foundation everything else — savings, investing, giving — is built on.',
            'mode' => 'all', 'triggers' => [['type' => 'get_job']]],
        ['title' => 'First Small Business', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🛍️',
            'desc' => 'Buy your first small business from the marketplace — after saving up for it.',
            'lesson' => 'A business bought outright starts earning immediately, with no loan eating into the profit.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_category', 'values' => ['business']]]],
        ['title' => 'First Badge', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏅',
            'desc' => 'Earn your very first badge.',
            'lesson' => 'Every badge marks a real money decision you\'ve actually made, not a participation trophy.',
            'mode' => 'all', 'triggers' => [['type' => 'earn_badge']]],

        // ══════════════════════════════════ LEVEL 2 — OR/AND combos begin ══
        ['title' => 'Net Worth: Ksh 40,000', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📈',
            'desc' => 'Push your net worth to Ksh 40,000.',
            'lesson' => 'At this stage, net worth grows fastest when income, savings and smart spending all pull the same way.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['40000']]]],
        ['title' => 'Ksh 3,000 Trading Profit', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💹',
            'desc' => 'Bank Ksh 3,000 in total shares profit.',
            'lesson' => 'Consistent, modest trading gains usually beat one big risky swing over the long run.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['3000']]]],
        ['title' => 'Two Trail Wins', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🎲',
            'desc' => 'Win 2 games of Pesa Trail.',
            'lesson' => 'Two wins in a row means you\'re reading the board, not just getting lucky rolls.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']]]],
        ['title' => 'Ksh 15,000 Cash Buffer', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💵',
            'desc' => 'Keep Ksh 15,000 in your wallet balance at once.',
            'lesson' => 'A real buffer means a family emergency is an inconvenience, not a crisis that needs a loan.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['15000']]]],
        ['title' => 'Mama Mboga Kiosk Buy', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🛍️',
            'desc' => 'Buy the Mama Mboga Kiosk from the marketplace — a small business with daily income.',
            'lesson' => 'A business that pays daily changes your cash flow rhythm completely, for better and for worse.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_slug', 'values' => ['mama-mboga-kiosk']]]],
        ['title' => 'Shareholder Status', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📊',
            'desc' => 'Buy your first share on the Pesa City Market.',
            'lesson' => 'Owning shares alongside a salary is how income and assets start working together.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_share']]],
        ['title' => 'Equity Square Entry', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🏗️',
            'desc' => 'Put money into an Equity Square deal.',
            'lesson' => 'A deal\'s printed risk level exists so you can decide with your eyes open, not after the fact.',
            'mode' => 'all', 'triggers' => [['type' => 'invest_deal']]],
        ['title' => 'Board Wins or Market Wins', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either win 2 games of Pesa Trail, or bank Ksh 3,000 in shares profit.',
            'lesson' => 'Whichever skill is stronger for you, both roads still grow the household\'s money.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']], ['type' => 'shares_profit', 'values' => ['3000']]]],
        ['title' => 'Big Save or Steady Save', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 20,000, or make one Ksh 6,000 top-up in a single deposit.',
            'lesson' => 'A lump-sum push and a slow steady habit both get a family to the same savings goal.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['20000']], ['type' => 'deposit_savings', 'values' => ['6000']]]],
        ['title' => 'Chama or Deal', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either join a chama, or put money into an Equity Square deal.',
            'lesson' => 'Group saving and solo investing both build wealth — they just carry different kinds of risk.',
            'mode' => 'any', 'triggers' => [['type' => 'join_chama'], ['type' => 'invest_deal']]],
        ['title' => 'Growth With Reserves', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Reach Ksh 40,000 net worth AND keep Ksh 15,000 cash on hand at the same time.',
            'lesson' => 'A family\'s financial security rests on growing wealth while staying liquid enough to handle life.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['40000']], ['type' => 'reach_balance', 'values' => ['15000']]]],
        ['title' => 'Skill Into Salary', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Finish a course AND get hired for a job.',
            'lesson' => 'A new skill only changes your income once it actually shows up on a payslip.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job']]],

        // ══════════════════════════════════ LEVEL 3 — 3-trigger combos, capstone ══
        ['title' => 'Net Worth: Ksh 80,000', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '📈',
            'desc' => 'Push your net worth past Ksh 80,000.',
            'lesson' => 'At this stage, net worth is compounding — years of decisions are visibly stacking up.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['80000']]]],
        ['title' => 'Ksh 6,000 Profit Banked', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💹',
            'desc' => 'Bank Ksh 6,000 total in shares profit.',
            'lesson' => 'Knowing when to take profit protects gains that a market swing could otherwise erase.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['6000']]]],
        ['title' => 'Hat-Trick on the Trail', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '🎲',
            'desc' => 'Win 3 games of Pesa Trail.',
            'lesson' => 'Three wins is a pattern — you\'re genuinely reading the game, not just rolling well.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']]]],
        ['title' => 'Ksh 30,000 Cash Reserve', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💵',
            'desc' => 'Hold Ksh 30,000 in your wallet balance at once.',
            'lesson' => 'A reserve this size is what turns a job loss or medical bill from a disaster into a setback.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['30000']]]],
        ['title' => 'Trade It or Invest It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either bank Ksh 6,000 in shares profit, or put money into an Equity Square deal.',
            'lesson' => 'Spreading growth across more than one channel is safer than relying on just one.',
            'mode' => 'any', 'triggers' => [['type' => 'shares_profit', 'values' => ['6000']], ['type' => 'invest_deal']]],
        ['title' => 'Win It or Join It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either win 3 Pesa Trail games, or join a chama.',
            'lesson' => 'Solo skill and group discipline are both legitimate financial muscles worth building.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => 'Build a Business or Build Skill', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either buy a small business from the marketplace, or finish another course.',
            'lesson' => 'A business generates income directly; a new skill raises what your existing income can become.',
            'mode' => 'any', 'triggers' => [['type' => 'buy_item_category', 'values' => ['business']], ['type' => 'take_course']]],
        ['title' => 'Bank It or Grow It', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 40,000, or push your net worth to Ksh 80,000.',
            'lesson' => 'Cash saved and total net worth move together, but hitting one doesn\'t guarantee the other.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['40000']], ['type' => 'reach_net_worth', 'values' => ['80000']]]],
        ['title' => 'Sharp Investor', 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Reach Ksh 80,000 net worth AND bank Ksh 6,000 in shares profit.',
            'lesson' => 'Growing total household wealth while also nailing individual trades is what separates sharp from lucky.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['80000']], ['type' => 'shares_profit', 'values' => ['6000']]]],
        ['title' => 'Table and Trail', 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Win 3 Pesa Trail games AND join a chama.',
            'lesson' => 'Financial maturity shows up both in solo decisions and in how you show up for a group.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => "Provider's Foundation", 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Finish a course, get hired, AND keep Ksh 30,000 cash on hand — the full foundation.',
            'lesson' => 'Skill, income and reserves reinforce each other — the household is only as stable as the weakest one.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job'], ['type' => 'reach_balance', 'values' => ['30000']]]],
        ['title' => 'Complete Financial Discipline', 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Reach Ksh 80,000 net worth, bank Ksh 6,000 in shares profit, AND win 3 Pesa Trail games.',
            'lesson' => 'Being disciplined across saving, investing, and even game-time decisions is what real financial maturity looks like.',
            'mode' => 'all', 'triggers' => [
                ['type' => 'reach_net_worth', 'values' => ['80000']],
                ['type' => 'shares_profit', 'values' => ['6000']],
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

        $this->command->info("ChallengeQuest26PlusSeeder: {$created} quests upserted (" . count(self::QUESTS) . ' total, levels 1-3).');
    }
}
