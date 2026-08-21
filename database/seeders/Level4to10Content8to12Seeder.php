<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * Closes the level 4-10 content gap for the 8-12 age group: 2 quests + 1 job
 * per level, in the same idempotent updateOrCreate style as Level1QuestSeeder
 * and Level123ContentSeeder. Kept as its own file, not yet wired into
 * DatabaseSeeder, pending review.
 */
class Level4to10Content8to12Seeder extends Seeder
{
    public function run(): void
    {
        $this->seedJobs();
        $this->seedQuests();
    }

    private function seedJobs(): void
    {
        $moneyBasics = CityCourse::where('slug', 'money-basics')->first()?->id;
        $custCare    = CityCourse::where('slug', 'customer-care-101')->first()?->id;

        $jobs = [
            [
                'title' => 'Stall Supervisor', 'employer_name' => 'Twiga Fresh Fruit Stand', 'employer_logo' => '🍊',
                'career_track' => 'business', 'level' => 4, 'salary_kes_month' => 19000, 'xp_reward' => 70,
                'required_course_id' => $moneyBasics,
                'description' => 'Run the fruit stall alone on Saturdays — price the produce, make change fast, and count the till twice before closing.',
            ],
            [
                'title' => 'Homework Tutor Junior', 'employer_name' => 'Bright Minds After-School Club', 'employer_logo' => '📚',
                'career_track' => 'creative', 'level' => 5, 'salary_kes_month' => 20500, 'xp_reward' => 85,
                'required_course_id' => null,
                'description' => 'Tutor younger kids in maths after school, paid per session. Every shilling earned from teaching still needs a plan before it\'s spent.',
            ],
            [
                'title' => 'Errand Squad Leader', 'employer_name' => 'Estate Errands Co-op', 'employer_logo' => '🏃',
                'career_track' => 'business', 'level' => 6, 'salary_kes_month' => 22000, 'xp_reward' => 100,
                'required_course_id' => $custCare,
                'description' => 'Lead two friends running paid errands for neighbours — fetching shopping, carrying water. Split the pay fairly and everyone keeps coming back.',
            ],
            [
                'title' => 'Junior Recycler', 'employer_name' => 'Mtaa Scrap Collectors', 'employer_logo' => '♻️',
                'career_track' => 'finance', 'level' => 7, 'salary_kes_month' => 23500, 'xp_reward' => 115,
                'required_course_id' => null,
                'description' => 'Collect plastic bottles and scrap metal around the estate, then negotiate the weight price at the buyer yourself. No negotiation, no profit.',
            ],
            [
                'title' => 'Farm Stand Cashier', 'employer_name' => 'Green Acre Family Farm', 'employer_logo' => '🌽',
                'career_track' => 'finance', 'level' => 8, 'salary_kes_month' => 25000, 'xp_reward' => 130,
                'required_course_id' => $moneyBasics,
                'description' => 'Manage the family farm\'s roadside stand cash box and reconcile the day\'s sales against what\'s actually in the tin.',
            ],
            [
                'title' => 'Paper Round Captain', 'employer_name' => 'Habari Njema Newsstand', 'employer_logo' => '📰',
                'career_track' => 'business', 'level' => 9, 'salary_kes_month' => 26500, 'xp_reward' => 145,
                'required_course_id' => null,
                'description' => 'Run a small newsletter delivery round with two juniors under you — and pay them out of what the round actually earns, not what you wish it earned.',
            ],
            [
                'title' => 'Mini Mart Junior Manager', 'employer_name' => 'Neema Family Mini Mart', 'employer_logo' => '🏬',
                'career_track' => 'business', 'level' => 10, 'salary_kes_month' => 28000, 'xp_reward' => 160,
                'required_course_id' => $custCare,
                'description' => 'Trusted to open and close the family mini mart, handle the M-Pesa till, and balance the books at the end of every day.',
            ],
        ];

        foreach ($jobs as $j) {
            CityJob::updateOrCreate(
                ['title' => $j['title'], 'employer_name' => $j['employer_name']],
                $j + [
                    'is_active'      => true,
                    'is_part_time'   => true,
                    'employment_type'=> 'part_time',
                    'age_group'      => '8-12',
                    'age_groups'     => ['8-12'],
                ]
            );
        }
    }

    private function seedQuests(): void
    {
        $quests = [
            // ── Level 4 ──────────────────────────────────────────────────────
            [
                'title' => 'Piggy Bank Pro', 'icon' => '🐷', 'xp_reward' => 220, 'kes_reward' => 200,
                'level_required' => 4, 'sort_order' => 41,
                'description' => 'Grow your total savings to Ksh 2,500 — prove your piggy bank discipline.',
                'lesson' => 'Small regular deposits beat one big spend every time. Compounding always starts with the first coin.',
                'trigger_type' => 'reach_savings', 'trigger_value' => '2500', 'trigger_label' => 'Reach Ksh 2,500 in savings 🏦',
            ],
            [
                'title' => 'Junior Entrepreneur', 'icon' => '🧢', 'xp_reward' => 310, 'kes_reward' => 280,
                'level_required' => 4, 'sort_order' => 42,
                'description' => 'Land any Pesa City hustle job and earn a badge — prove you can work and learn at the same time.',
                'lesson' => 'Working and learning together is how young hustlers in Kenya build both skills and shillings at once.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null, 'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'get_job',    'values' => [], 'label' => 'Get hired for any job 💼'],
                    ['type' => 'earn_badge', 'values' => [], 'label' => 'Earn any badge 🏅'],
                ],
            ],

            // ── Level 5 ──────────────────────────────────────────────────────
            [
                'title' => 'Market Day Shopper', 'icon' => '🛍️', 'xp_reward' => 280, 'kes_reward' => 300,
                'level_required' => 5, 'sort_order' => 51,
                'description' => 'Buy anything from the Marketplace using money you saved yourself — practice spending on purpose, not by accident.',
                'lesson' => 'Every purchase is a choice between this thing now and something else later. Spending on purpose beats spending on impulse.',
                'trigger_type' => 'buy_item_category', 'trigger_value' => '', 'trigger_label' => 'Buy anything from the Marketplace 🏗️',
            ],
            [
                'title' => 'Skill Builder', 'icon' => '📖', 'xp_reward' => 390, 'kes_reward' => 420,
                'level_required' => 5, 'sort_order' => 52,
                'description' => 'Complete the Communication Basics course, then grow one savings pocket to Ksh 1,500 with what you learned.',
                'lesson' => 'Skills and savings grow the same way — a little added consistently, week after week.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null, 'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'take_course',     'values' => ['communication-basics'], 'label' => 'Complete the "Communication Basics" course 📚'],
                    ['type' => 'deposit_savings', 'values' => ['1500'],                 'label' => 'Grow one savings pocket to Ksh 1,500 🏦'],
                ],
            ],

            // ── Level 6 ──────────────────────────────────────────────────────
            [
                'title' => 'Wallet Watcher', 'icon' => '💵', 'xp_reward' => 340, 'kes_reward' => 400,
                'level_required' => 6, 'sort_order' => 61,
                'description' => 'Grow your wallet cash to Ksh 3,000 without stashing it all in savings — some money needs to stay ready to use.',
                'lesson' => 'A healthy money life keeps some cash liquid and ready, not locked away where you can\'t reach it in an emergency.',
                'trigger_type' => 'reach_balance', 'trigger_value' => '3000', 'trigger_label' => 'Grow your wallet to Ksh 3,000 💰',
            ],
            [
                'title' => 'Steady Saver Duo', 'icon' => '🤝', 'xp_reward' => 480, 'kes_reward' => 560,
                'level_required' => 6, 'sort_order' => 62,
                'description' => 'Make a savings deposit of at least Ksh 2,000, then push your wallet cash to Ksh 4,000 on top of it.',
                'lesson' => 'Saving and spending money aren\'t enemies — a good plan grows both your safety net and your everyday cash at once.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null, 'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'deposit_savings', 'values' => ['2000'], 'label' => 'Deposit at least Ksh 2,000 into savings 🏦'],
                    ['type' => 'reach_balance',   'values' => ['4000'], 'label' => 'Grow your wallet to Ksh 4,000 💰'],
                ],
            ],

            // ── Level 7 ──────────────────────────────────────────────────────
            [
                'title' => 'Badge Collector Jr.', 'icon' => '🏅', 'xp_reward' => 400, 'kes_reward' => 500,
                'level_required' => 7, 'sort_order' => 71,
                'description' => 'Earn any badge in Pesa City — a sign that your money habits are being noticed.',
                'lesson' => 'Recognition for good habits is a bonus, not the goal — but it\'s proof the habit is actually sticking.',
                'trigger_type' => 'earn_badge', 'trigger_value' => '', 'trigger_label' => 'Earn any badge 🏅',
            ],
            [
                'title' => 'Spin & Shop', 'icon' => '🎡', 'xp_reward' => 560, 'kes_reward' => 700,
                'level_required' => 7, 'sort_order' => 72,
                'description' => 'Spin the Lucky Wheel once, then buy anything from the Marketplace with whatever you land — good luck still needs a good plan.',
                'lesson' => 'Even a lucky windfall can be wasted without a plan. Decide what a bonus is for before it lands in your hand.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null, 'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'spin_wheel',        'values' => [], 'label' => 'Spin the Lucky Wheel 🎡'],
                    ['type' => 'buy_item_category', 'values' => [], 'label' => 'Buy anything from the Marketplace 🏗️'],
                ],
            ],

            // ── Level 8 ──────────────────────────────────────────────────────
            [
                'title' => 'Big Kiosk Dream', 'icon' => '🏪', 'xp_reward' => 470, 'kes_reward' => 650,
                'level_required' => 8, 'sort_order' => 81,
                'description' => 'Deposit at least Ksh 4,000 into savings in one go — start building toward a real business fund.',
                'lesson' => 'A big deposit at the right moment can jump-start a savings goal that small deposits alone would take months to reach.',
                'trigger_type' => 'deposit_savings', 'trigger_value' => '4000', 'trigger_label' => 'Deposit at least Ksh 4,000 into savings 🏦',
            ],
            [
                'title' => 'Course & Career', 'icon' => '💻', 'xp_reward' => 660, 'kes_reward' => 910,
                'level_required' => 8, 'sort_order' => 82,
                'description' => 'Complete the Digital Skills Intro course, then land any job in Pesa City to put it to use.',
                'lesson' => 'A skill without a job to use it in just sits there. Chase both together, in that order.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null, 'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['digital-skills-intro'], 'label' => 'Complete the "Digital Skills Intro" course 📚'],
                    ['type' => 'get_job',     'values' => [],                       'label' => 'Get hired for any job 💼'],
                ],
            ],

            // ── Level 9 ──────────────────────────────────────────────────────
            [
                'title' => 'Net Worth Watcher', 'icon' => '📊', 'xp_reward' => 550, 'kes_reward' => 800,
                'level_required' => 9, 'sort_order' => 91,
                'description' => 'Push your net worth past Ksh 15,000 — savings, wallet cash, and anything you own, added together.',
                'lesson' => 'Net worth is the real scoreboard, not just your wallet — what you own matters as much as what you\'re holding.',
                'trigger_type' => 'reach_net_worth', 'trigger_value' => '15000', 'trigger_label' => 'Push your net worth past Ksh 15,000 📈',
            ],
            [
                'title' => 'Triple Threat', 'icon' => '🌟', 'xp_reward' => 770, 'kes_reward' => 1120,
                'level_required' => 9, 'sort_order' => 92,
                'description' => 'Grow your total savings to Ksh 11,000, earn a badge, and land any job — all three, no shortcuts.',
                'lesson' => 'Saving, learning, and earning are three separate muscles. Real financial strength trains all three, not just one.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null, 'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'reach_savings', 'values' => ['11000'], 'label' => 'Reach Ksh 11,000 in savings 🏦'],
                    ['type' => 'earn_badge',    'values' => [],       'label' => 'Earn any badge 🏅'],
                    ['type' => 'get_job',       'values' => [],       'label' => 'Get hired for any job 💼'],
                ],
            ],

            // ── Level 10 ─────────────────────────────────────────────────────
            [
                'title' => 'Capstone Saver', 'icon' => '🏆', 'xp_reward' => 650, 'kes_reward' => 1000,
                'level_required' => 10, 'sort_order' => 101,
                'description' => 'Grow your wallet cash to Ksh 6,000 — proof you can hold real money without spending it all.',
                'lesson' => 'Holding on to money you could spend is its own kind of discipline — often harder than earning it in the first place.',
                'trigger_type' => 'reach_balance', 'trigger_value' => '6000', 'trigger_label' => 'Grow your wallet to Ksh 6,000 💰',
            ],
            [
                'title' => 'Pesa City Junior Legend', 'icon' => '👑', 'xp_reward' => 910, 'kes_reward' => 1400,
                'level_required' => 10, 'sort_order' => 102,
                'description' => 'Get hired for any job, complete the Financial Literacy 101 course, and push your net worth past Ksh 20,000 — the full picture, all at once.',
                'lesson' => 'Income, knowledge, and net worth growing together is what "good with money" actually looks like — not one big win, but all three moving up.',
                'trigger_type' => null, 'trigger_value' => null, 'trigger_label' => null, 'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'get_job',           'values' => [],                        'label' => 'Get hired for any job 💼'],
                    ['type' => 'take_course',       'values' => ['financial-literacy-101'], 'label' => 'Complete the "Financial Literacy 101" course 📚'],
                    ['type' => 'reach_net_worth',    'values' => ['20000'],                 'label' => 'Push your net worth past Ksh 20,000 📈'],
                ],
            ],
        ];

        foreach ($quests as $q) {
            Quest::updateOrCreate(
                ['title' => $q['title']],
                $q + ['is_active' => true, 'age_group' => '8-12']
            );
        }
    }
}
