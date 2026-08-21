<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * ChallengeQuest{AgeGroup}Seeder — a new quest flavor sitting alongside the
 * regular 8/level quest ladder: growth-metric quests built on the same
 * baseline+delta metrics City Contracts and Champions' Court Challenges
 * already use (net worth, shares profit, Pesa Trail wins), auto-completing
 * via QuestTriggerService::checkMetricQuests() (polled every dashboard
 * load — these targets drift continuously, unlike a one-shot action).
 *
 * Complexity ramps by level: L1 is single-trigger only, L2 introduces
 * 'any' (OR) and 'all' (AND) 2-trigger combos, L3 adds 3-trigger AND
 * combos including a capstone quest that requires net worth + shares
 * profit + Pesa Trail wins all at once. 12 quests per level, this file
 * covers levels 1-3 for 8-12 (Primary School).
 */
class ChallengeQuest8to12Seeder extends Seeder
{
    private const AGE_GROUP = '8-12';

    private const QUESTS = [

        // ══════════════════════════════════ LEVEL 1 — single trigger ══════
        ['title' => 'Piggy Bank Power', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '📈',
            'desc' => 'Grow your net worth to Ksh 600 — everything you own, minus anything you owe.',
            'lesson' => 'Net worth counts your savings AND your stuff, not just coins in your pocket.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['600']]]],
        ['title' => 'Tiny Shares, Real Money', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💹',
            'desc' => 'Sell a share for a Ksh 30 profit — even a small win counts as real investing.',
            'lesson' => 'A small profit today is proof the idea works — bigger amounts come later.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['30']]]],
        ['title' => 'First Pesa Trail Win', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎲',
            'desc' => 'Win your first game of Pesa Trail — roll the dice and get to Freedom Square first.',
            'lesson' => "Games teach money moves safely — mistakes here don't cost you anything real.",
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['1']]]],
        ['title' => 'Save Ksh 300', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🐷',
            'desc' => 'Grow your savings scheme balance to Ksh 300 — start small, stay consistent.',
            'lesson' => "Saving Ksh 10 a day adds up faster than it feels like it will.",
            'mode' => 'all', 'triggers' => [['type' => 'reach_savings', 'values' => ['300']]]],
        ['title' => 'Open Your First Savings Jar', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏦',
            'desc' => "Open a savings scheme — a safe place for shilingi you're not spending yet.",
            'lesson' => "Money you can't see every day is money you're less tempted to spend.",
            'mode' => 'all', 'triggers' => [['type' => 'open_savings']]],
        ['title' => 'Top Up the Jar', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💰',
            'desc' => 'Deposit Ksh 150 into your savings in one go.',
            'lesson' => 'One good deposit builds the habit faster than ten tiny ones.',
            'mode' => 'all', 'triggers' => [['type' => 'deposit_savings', 'values' => ['150']]]],
        ['title' => 'Chama Rookie', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🤝',
            'desc' => 'Join a chama — a savings group where everyone contributes and everyone benefits.',
            'lesson' => "Saving with others keeps you accountable — it's harder to skip when people are counting on you.",
            'mode' => 'all', 'triggers' => [['type' => 'join_chama']]],
        ['title' => 'First Spin', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎡',
            'desc' => 'Take your first spin on the Pesa City wheel — you might land on a bonus, or a small fine.',
            'lesson' => "Not every spin is a win — that's exactly how real luck-based money works too.",
            'mode' => 'all', 'triggers' => [['type' => 'spin_wheel']]],
        ['title' => 'First Lesson Done', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🎓',
            'desc' => 'Finish any course at the Opportunity Hub — knowledge is the one thing nobody can take from you.',
            'lesson' => 'Every course you finish is a skill you now own for free, forever.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course']]],
        ['title' => 'First Hustle', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '💼',
            'desc' => 'Get hired for your first job in Pesa City — your very first paycheck starts here.',
            'lesson' => "Everyone's very first job is small — it's the starting line, not the finish line.",
            'mode' => 'all', 'triggers' => [['type' => 'get_job']]],
        ['title' => 'First Big Buy', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🛍️',
            'desc' => 'Buy your first gadget from the marketplace — save up before you spend.',
            'lesson' => 'Waiting and saving for something you want beats buying on impulse.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_category', 'values' => ['gadget']]]],
        ['title' => 'Badge Debut', 'level' => 1, 'xp' => 150, 'kes' => 110, 'icon' => '🏅',
            'desc' => "Earn your very first badge — proof of a money skill you've actually used.",
            'lesson' => "Badges aren't decorations — each one means you did the real thing at least once.",
            'mode' => 'all', 'triggers' => [['type' => 'earn_badge']]],

        // ══════════════════════════════════ LEVEL 2 — OR/AND combos begin ══
        ['title' => 'Net Worth Climb', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📈',
            'desc' => 'Push your net worth to Ksh 1,500 — savings, chama shares and stuff you own, added up.',
            'lesson' => 'Net worth grows fastest when saving AND spending wisely happen together.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['1500']]]],
        ['title' => 'Trading Momentum', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💹',
            'desc' => 'Bank Ksh 80 in total shares profit — one sale, or a few small ones.',
            'lesson' => 'Profit adds up across many small trades, not just one big lucky one.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['80']]]],
        ['title' => 'Two-Game Streak', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🎲',
            'desc' => "Win 2 games of Pesa Trail — show it wasn't just beginner's luck.",
            'lesson' => "One win can be luck. Two wins means you're learning the game.",
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']]]],
        ['title' => 'Cash on Hand', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '💵',
            'desc' => 'Keep Ksh 700 in your wallet balance — real spending power, not locked away.',
            'lesson' => "Cash you can use today matters as much as money you're saving for later.",
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['700']]]],
        ['title' => 'Phone Upgrade', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🛍️',
            'desc' => 'Buy the Tecno Spark from the marketplace — your first real gadget purchase.',
            'lesson' => 'A phone is a tool, not just a toy — it can help you learn and earn too.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_item_slug', 'values' => ['tecno-spark']]]],
        ['title' => 'Shareholder Status', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '📊',
            'desc' => 'Buy your first share on the Pesa City Market — become a tiny owner of a real company.',
            'lesson' => 'Owning even one share makes you a shareholder, with the same rights as a big investor.',
            'mode' => 'all', 'triggers' => [['type' => 'buy_share']]],
        ['title' => 'Equity Square Entry', 'level' => 2, 'xp' => 190, 'kes' => 150, 'icon' => '🏗️',
            'desc' => 'Put money into an Equity Square deal — your first step into bigger investing.',
            'lesson' => 'Deals come with risk — that\'s why you never put in more than you can afford to lose.',
            'mode' => 'all', 'triggers' => [['type' => 'invest_deal']]],
        ['title' => 'Win Big or Trade Smart', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either win 2 games of Pesa Trail, or bank Ksh 80 in shares profit — your pick.',
            'lesson' => 'There\'s more than one way to grow money — games, trading, or both.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['2']], ['type' => 'shares_profit', 'values' => ['80']]]],
        ['title' => 'Save Steady', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 800, or make one Ksh 300 top-up in a single deposit.',
            'lesson' => 'Steady small saves and one big push both get you to the same place.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['800']], ['type' => 'deposit_savings', 'values' => ['300']]]],
        ['title' => 'Team Up or Invest', 'level' => 2, 'xp' => 230, 'kes' => 190, 'icon' => '🔀',
            'desc' => 'Either join a chama, or put money into an Equity Square deal.',
            'lesson' => 'Group saving and solo investing both grow money — just with different risks.',
            'mode' => 'any', 'triggers' => [['type' => 'join_chama'], ['type' => 'invest_deal']]],
        ['title' => 'Balanced Growth', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Reach Ksh 1,500 net worth AND keep Ksh 700 cash on hand at the same time.',
            'lesson' => 'Growing net worth while still keeping usable cash is the real balancing act.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['1500']], ['type' => 'reach_balance', 'values' => ['700']]]],
        ['title' => 'Skill to Salary', 'level' => 2, 'xp' => 270, 'kes' => 220, 'icon' => '🎯',
            'desc' => 'Finish a course AND get hired for a job — turn learning into earning.',
            'lesson' => 'Skills open doors, but only a job actually pays you for walking through them.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job']]],

        // ══════════════════════════════════ LEVEL 3 — 3-trigger combos, capstone ══
        ['title' => 'Net Worth Breakthrough', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '📈',
            'desc' => 'Push your net worth past Ksh 3,000 — real progress since you started.',
            'lesson' => 'Big totals are just many small good choices, added up over time.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['3000']]]],
        ['title' => 'Profit Taker', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💹',
            'desc' => 'Bank Ksh 150 total in shares profit — knowing when to sell matters as much as buying.',
            'lesson' => 'A good investor also knows when to sell, not just when to buy.',
            'mode' => 'all', 'triggers' => [['type' => 'shares_profit', 'values' => ['150']]]],
        ['title' => 'Hat-Trick Winner', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '🎲',
            'desc' => "Win 3 games of Pesa Trail — you've officially got the hang of it.",
            'lesson' => 'Repetition is how any skill, even a game, turns into a habit.',
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']]]],
        ['title' => 'Serious Cash Reserve', 'level' => 3, 'xp' => 210, 'kes' => 180, 'icon' => '💵',
            'desc' => 'Hold Ksh 1,300 in your wallet balance at once.',
            'lesson' => "Keeping cash ready means you're never caught off guard by a surprise cost.",
            'mode' => 'all', 'triggers' => [['type' => 'reach_balance', 'values' => ['1300']]]],
        ['title' => 'Grow It However You Can', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either bank Ksh 150 in shares profit, or put money into an Equity Square deal.',
            'lesson' => 'Different investments suit different people — the goal is growth, not the method.',
            'mode' => 'any', 'triggers' => [['type' => 'shares_profit', 'values' => ['150']], ['type' => 'invest_deal']]],
        ['title' => 'Play the Game, Any Game', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either win 3 Pesa Trail games, or join a chama — different games, same lesson.',
            'lesson' => 'Whether it\'s a board game or a savings group, the rules of money still apply.',
            'mode' => 'any', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => 'Spend or Learn', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either buy a gadget from the marketplace, or finish another course — invest in stuff or invest in yourself.',
            'lesson' => 'Money spent on learning is its own kind of investment — sometimes the better one.',
            'mode' => 'any', 'triggers' => [['type' => 'buy_item_category', 'values' => ['gadget']], ['type' => 'take_course']]],
        ['title' => 'Two Roads to Wealth', 'level' => 3, 'xp' => 250, 'kes' => 210, 'icon' => '🔀',
            'desc' => 'Either grow your savings to Ksh 1,600, or push your net worth to Ksh 3,000.',
            'lesson' => 'Saving cash and growing net worth are related, but they\'re not the same race.',
            'mode' => 'any', 'triggers' => [['type' => 'reach_savings', 'values' => ['1600']], ['type' => 'reach_net_worth', 'values' => ['3000']]]],
        ['title' => "Investor's Edge", 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Reach Ksh 3,000 net worth AND bank Ksh 150 in shares profit — grow your wealth and prove you can trade.',
            'lesson' => 'The best investors grow their total wealth while also making smart individual trades.',
            'mode' => 'all', 'triggers' => [['type' => 'reach_net_worth', 'values' => ['3000']], ['type' => 'shares_profit', 'values' => ['150']]]],
        ['title' => 'Community and Cash', 'level' => 3, 'xp' => 300, 'kes' => 250, 'icon' => '🎯',
            'desc' => 'Win 3 Pesa Trail games AND join a chama — show you can win alone and grow with a group.',
            'lesson' => "Financial skill isn't just personal — it also means knowing how to work with others.",
            'mode' => 'all', 'triggers' => [['type' => 'arcade_wins', 'values' => ['3']], ['type' => 'join_chama']]],
        ['title' => 'Career Foundation', 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Finish a course, get hired, AND keep Ksh 1,300 cash on hand — the full starter package.',
            'lesson' => 'Skills, income, and savings work together — missing one weakens the other two.',
            'mode' => 'all', 'triggers' => [['type' => 'take_course'], ['type' => 'get_job'], ['type' => 'reach_balance', 'values' => ['1300']]]],
        ['title' => 'Triple Threat', 'level' => 3, 'xp' => 360, 'kes' => 300, 'icon' => '👑',
            'desc' => 'Reach Ksh 3,000 net worth, bank Ksh 150 in shares profit, AND win 3 Pesa Trail games — the full money hat-trick.',
            'lesson' => 'Real financial confidence comes from being good at more than one money skill at once.',
            'mode' => 'all', 'triggers' => [
                ['type' => 'reach_net_worth', 'values' => ['3000']],
                ['type' => 'shares_profit', 'values' => ['150']],
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

        $this->command->info("ChallengeQuest8to12Seeder: {$created} quests upserted (" . count(self::QUESTS) . ' total, levels 1-3).');
    }
}
