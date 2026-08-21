<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * Closes the level 4-10 content gap for the 26+ age group: quests and jobs
 * previously stopped cold at level 3 for every age group (see the coverage
 * audit) — this is the 26+ slice of that fix. Kept as its own file so each
 * age group's batch can be authored/reviewed independently before being
 * wired into DatabaseSeeder.
 */
class Level4to10Content26PlusSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedQuests();
        $this->seedJobs();
    }

    private function seedQuests(): void
    {
        $quests = [
            // ── Level 4 ──────────────────────────────────────────────────────
            [
                'title'          => 'First Comma',
                'icon'           => '💵',
                'description'    => 'Push your net worth past Ksh 150,000 — the first real proof your money habits are working, not just your salary.',
                'lesson'         => 'Net worth, not income, is the real financial scoreboard — a big salary means nothing if it all leaves as fast as it arrives.',
                'xp_reward'      => 220,
                'kes_reward'     => 200,
                'level_required' => 4,
                'trigger_type'   => 'reach_net_worth',
                'trigger_value'  => '150000',
                'trigger_label'  => 'Push your net worth past Ksh 150,000',
                'sort_order'     => 1,
            ],
            [
                'title'          => 'Two Bricks Deep',
                'icon'           => '🧱',
                'description'    => 'A steady worker plants two seeds: something that earns while it sits, and something set aside for the day work slows down. Buy a Business-category asset and grow your total savings to Ksh 20,000.',
                'lesson'         => 'Buying an income-earning asset and building savings side by side is how a payslip stops being the whole plan.',
                'xp_reward'      => 300,
                'kes_reward'     => 280,
                'level_required' => 4,
                'triggers'       => [
                    ['type' => 'buy_item_category', 'values' => ['business'], 'label' => 'Buy any Business-category asset'],
                    ['type' => 'reach_savings',      'values' => ['20000'],   'label' => 'Grow your total savings to Ksh 20,000'],
                ],
                'trigger_mode'   => 'all',
                'sort_order'     => 2,
            ],

            // ── Level 5 ──────────────────────────────────────────────────────
            [
                'title'          => 'Circle of Legacy',
                'icon'           => '🤝',
                'description'    => 'Join a chama. Kenyans have pooled money together for generations — for a reason.',
                'lesson'         => 'A chama turns individual discipline into group accountability — it is much harder to skip a contribution when eleven other people are watching.',
                'xp_reward'      => 280,
                'kes_reward'     => 300,
                'level_required' => 5,
                'trigger_type'   => 'join_chama',
                'trigger_value'  => null,
                'trigger_label'  => 'Join a chama',
                'sort_order'     => 3,
            ],
            [
                'title'          => 'Payslip to Plan',
                'icon'           => '📋',
                'description'    => 'Land a job that matches your new station in the city, then prove the raise did not just raise your spending — grow your wallet balance to Ksh 30,000.',
                'lesson'         => 'A bigger salary that gets fully spent every month is not progress — it is just a bigger treadmill.',
                'xp_reward'      => 400,
                'kes_reward'     => 420,
                'level_required' => 5,
                'triggers'       => [
                    ['type' => 'get_job',       'values' => [],       'label' => 'Get hired for any job'],
                    ['type' => 'reach_balance', 'values' => ['30000'],'label' => 'Grow your wallet balance to Ksh 30,000'],
                ],
                'trigger_mode'   => 'all',
                'sort_order'     => 4,
            ],

            // ── Level 6 ──────────────────────────────────────────────────────
            [
                'title'          => 'Safe Money Moves',
                'icon'           => '🏛️',
                'description'    => 'Buy into a Fixed Income asset — a Money Market Fund or Treasury instrument. Not every shilling needs to chase excitement.',
                'lesson'         => 'Fixed-income assets like MMFs and T-Bills will not make you rich overnight, but they are the ballast that keeps the rest of your portfolio from capsizing.',
                'xp_reward'      => 340,
                'kes_reward'     => 400,
                'level_required' => 6,
                'trigger_type'   => 'buy_item_category',
                'trigger_value'  => 'fixed_income',
                'trigger_label'  => 'Buy any Fixed Income asset',
                'sort_order'     => 5,
            ],
            [
                'title'          => 'Sharpen the Pitch',
                'icon'           => '🗣️',
                'description'    => 'Complete Sales 101, then put it to use — earn any badge showing your growth.',
                'lesson'         => 'Selling is not sleazy — it is the skill that turns a good product or good work into an actual paycheck.',
                'xp_reward'      => 480,
                'kes_reward'     => 560,
                'level_required' => 6,
                'triggers'       => [
                    ['type' => 'take_course', 'values' => ['sales-101'], 'label' => 'Complete the Sales 101 course'],
                    ['type' => 'earn_badge',  'values' => [],            'label' => 'Earn any badge'],
                ],
                'trigger_mode'   => 'all',
                'sort_order'     => 6,
            ],

            // ── Level 7 ──────────────────────────────────────────────────────
            [
                'title'          => 'Eighty Thousand Strong',
                'icon'           => '🏦',
                'description'    => 'Grow your total savings to Ksh 80,000 — enough to genuinely absorb a bad month without panic.',
                'lesson'         => 'Emergency savings is not about the number feeling big — it is about the number being bigger than your worst realistic month.',
                'xp_reward'      => 400,
                'kes_reward'     => 500,
                'level_required' => 7,
                'trigger_type'   => 'reach_savings',
                'trigger_value'  => '80000',
                'trigger_label'  => 'Grow your total savings to Ksh 80,000',
                'sort_order'     => 7,
            ],
            [
                'title'          => 'Built to Last',
                'icon'           => '💻',
                'description'    => 'Complete Web Design Basics, then land a job that puts the skill to work.',
                'lesson'         => 'A skill you can point to on a CV opens doors that experience alone sometimes cannot.',
                'xp_reward'      => 560,
                'kes_reward'     => 700,
                'level_required' => 7,
                'triggers'       => [
                    ['type' => 'take_course', 'values' => ['web-design-basics'], 'label' => 'Complete the Web Design Basics course'],
                    ['type' => 'get_job',     'values' => [],                    'label' => 'Get hired for any job'],
                ],
                'trigger_mode'   => 'all',
                'sort_order'     => 8,
            ],

            // ── Level 8 ──────────────────────────────────────────────────────
            [
                'title'          => 'Pocket the Difference',
                'icon'           => '💰',
                'description'    => 'Deposit Ksh 15,000 into a single savings pocket in one go — proof you can move real money with intent, not just dribs and drabs.',
                'lesson'         => 'A single disciplined deposit says more about your financial control than a dozen scattered small ones.',
                'xp_reward'      => 470,
                'kes_reward'     => 650,
                'level_required' => 8,
                'trigger_type'   => 'deposit_savings',
                'trigger_value'  => '15000',
                'trigger_label'  => 'Deposit Ksh 15,000 into one savings pocket',
                'sort_order'     => 9,
            ],
            [
                'title'          => 'Tell the Market',
                'icon'           => '📣',
                'description'    => 'Complete Digital Marketing 101, then push your net worth past Ksh 900,000 — you are closing in on Settler territory.',
                'lesson'         => 'Marketing skill does not just sell products — it sells you, your CV, and your side hustle too.',
                'xp_reward'      => 660,
                'kes_reward'     => 900,
                'level_required' => 8,
                'triggers'       => [
                    ['type' => 'take_course',     'values' => ['digital-marketing-101'], 'label' => 'Complete the Digital Marketing 101 course'],
                    ['type' => 'reach_net_worth', 'values' => ['900000'],                 'label' => 'Push your net worth past Ksh 900,000'],
                ],
                'trigger_mode'   => 'all',
                'sort_order'     => 10,
            ],

            // ── Level 9 ──────────────────────────────────────────────────────
            [
                'title'          => 'Badge of Honour',
                'icon'           => '🏅',
                'description'    => 'Earn any badge — a visible marker of the ground you have covered to get here.',
                'lesson'         => 'Badges are a receipt, not the goal — but a wall of receipts is still proof of a real track record.',
                'xp_reward'      => 550,
                'kes_reward'     => 800,
                'level_required' => 9,
                'trigger_type'   => 'earn_badge',
                'trigger_value'  => null,
                'trigger_label'  => 'Earn any badge',
                'sort_order'     => 11,
            ],
            [
                'title'          => 'Lend to the Republic',
                'icon'           => '📜',
                'description'    => 'Buy into a 5-Year Treasury Bond, then grow a single savings pocket to Ksh 25,000 alongside it — patient money and disciplined money, working together.',
                'lesson'         => 'A bond locks up your cash for years in exchange for a guaranteed coupon — patience is the price of that certainty.',
                'xp_reward'      => 770,
                'kes_reward'     => 1100,
                'level_required' => 9,
                'triggers'       => [
                    ['type' => 'buy_item_slug',   'values' => ['treasury-bonds-5yr'], 'label' => 'Buy a Treasury Bond (5-Year)'],
                    ['type' => 'deposit_savings', 'values' => ['25000'],              'label' => 'Grow one savings pocket to Ksh 25,000'],
                ],
                'trigger_mode'   => 'all',
                'sort_order'     => 12,
            ],

            // ── Level 10 ─────────────────────────────────────────────────────
            [
                'title'          => 'Cross Into Settler Territory',
                'icon'           => '📈',
                'description'    => 'Push your net worth past Ksh 1,000,000 — the first real milestone on the road to real wealth.',
                'lesson'         => 'A million shillings in net worth is not the finish line — it is the point where compounding finally starts pulling its own weight.',
                'xp_reward'      => 650,
                'kes_reward'     => 1000,
                'level_required' => 10,
                'trigger_type'   => 'reach_net_worth',
                'trigger_value'  => '1000000',
                'trigger_label'  => 'Push your net worth past Ksh 1,000,000',
                'sort_order'     => 13,
            ],
            [
                'title'          => 'One Last Spin, Then Steady',
                'icon'           => '🎡',
                'description'    => 'Try your luck on a scenario for the fun of it, then grow your wallet to Ksh 60,000 — because the real plan was never luck.',
                'lesson'         => 'There is nothing wrong with a little fun with money — as long as the plan does not depend on it.',
                'xp_reward'      => 910,
                'kes_reward'     => 1400,
                'level_required' => 10,
                'triggers'       => [
                    ['type' => 'play_scenario', 'values' => [],        'label' => 'Play any scenario'],
                    ['type' => 'reach_balance', 'values' => ['60000'], 'label' => 'Grow your wallet balance to Ksh 60,000'],
                ],
                'trigger_mode'   => 'all',
                'sort_order'     => 14,
            ],
        ];

        foreach ($quests as $q) {
            $q += ['trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null];
            Quest::updateOrCreate(
                ['title' => $q['title']],
                $q + ['is_active' => true, 'age_group' => '26+']
            );
        }
    }

    private function seedJobs(): void
    {
        $bookkeeping = CityCourse::where('slug', 'bookkeeping-basics')->value('id');
        $customerCare = CityCourse::where('slug', 'customer-care-101')->value('id');
        $sales = CityCourse::where('slug', 'sales-101')->value('id');
        $webDesign = CityCourse::where('slug', 'web-design-basics')->value('id');
        $digitalMarketing = CityCourse::where('slug', 'digital-marketing-101')->value('id');

        $jobs = [
            [
                'title'              => 'Credit & Accounts Officer',
                'employer_name'      => 'Amka SACCO',
                'employer_logo'      => '🏦',
                'career_track'       => 'finance',
                'salary_kes_month'   => 68000,
                'level'              => 4,
                'xp_reward'          => 70,
                'required_course_id'=> $bookkeeping,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'description'        => 'Vet loan applications, reconcile member accounts, and chase overdue repayments without chasing away members. Every entry you post is someone else\'s savings.',
            ],
            [
                'title'              => 'Branch Operations Supervisor',
                'employer_name'      => 'Tumaini Retail Holdings',
                'employer_logo'      => '🏬',
                'career_track'       => 'business',
                'salary_kes_month'   => 82000,
                'level'              => 5,
                'xp_reward'          => 85,
                'required_course_id'=> $customerCare,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'description'        => 'Run the floor of a busy branch: staff rotas, stock counts, and a queue that must never look like a queue. You now sign off other people\'s shift reports.',
            ],
            [
                'title'              => 'Regional Sales Manager',
                'employer_name'      => 'Savanna FoodWorks',
                'employer_logo'      => '🥛',
                'career_track'       => 'business',
                'salary_kes_month'   => 98000,
                'level'              => 6,
                'xp_reward'          => 100,
                'required_course_id'=> $sales,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'description'        => 'Own the numbers for a whole region\'s worth of distributors and route sales reps. Targets are quarterly; excuses are not accepted, but neither is burning out your team to hit them.',
            ],
            [
                'title'              => 'IT Systems Manager',
                'employer_name'      => 'Zuri Bank',
                'employer_logo'      => '💻',
                'career_track'       => 'tech',
                'salary_kes_month'   => 116000,
                'level'              => 7,
                'xp_reward'          => 115,
                'required_course_id'=> $webDesign,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'description'        => 'Keep the bank\'s internal portals and branch systems running, and manage the small team that fixes them at 2am when they don\'t. Uptime is the whole job description.',
            ],
            [
                'title'              => 'Head of Marketing',
                'employer_name'      => 'Karibu Beverages Ltd',
                'employer_logo'      => '📊',
                'career_track'       => 'creative',
                'salary_kes_month'   => 136000,
                'level'              => 8,
                'xp_reward'          => 130,
                'required_course_id'=> $digitalMarketing,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'description'        => 'Set the campaign calendar, defend the budget in front of the board, and own the number everyone asks about first: did the campaign actually move sales.',
            ],
            [
                'title'              => 'General Manager',
                'employer_name'      => 'Pwani Wholesale Distributors',
                'employer_logo'      => '🏭',
                'career_track'       => 'business',
                'salary_kes_month'   => 158000,
                'level'              => 9,
                'xp_reward'          => 145,
                'required_course_id'=> null,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'description'        => 'Every department — sales, warehouse, accounts — reports to you now, and every one of their problems eventually lands on your desk before it lands on the owner\'s.',
            ],
            [
                'title'              => 'Managing Director',
                'employer_name'      => 'Baraka Enterprises Group',
                'employer_logo'      => '👑',
                'career_track'       => 'business',
                'salary_kes_month'   => 183000,
                'level'              => 10,
                'xp_reward'          => 160,
                'required_course_id'=> null,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'description'        => 'You set the strategy, sign the big contracts, and answer to a board that measures you in one number: did the enterprise grow this year, and will it survive without you next year.',
            ],
        ];

        foreach ($jobs as $j) {
            CityJob::updateOrCreate(
                ['title' => $j['title'], 'employer_name' => $j['employer_name']],
                $j + ['is_active' => true, 'age_group' => '26+']
            );
        }
    }
}
