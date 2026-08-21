<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\Quest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Levels 4-10 content for the 18-25 age group: closes the gap where quests
 * and jobs previously stopped dead at level 3 for every age group. Follows
 * two employer ladders (Tumaini Sacco: L4→L7→L10, AdMax Kenya: L5→L9) so
 * promotions read as real career progression, not just bigger numbers.
 * Idempotent: safe to re-run (matches on title / title+employer_name).
 */
class Level4to10Content18to25Seeder extends Seeder
{
    public function run(): void
    {
        $courseIds = [
            'bookkeeping-basics'   => CityCourse::where('slug', 'bookkeeping-basics')->first()?->id,
            'digital-marketing-101'=> CityCourse::where('slug', 'digital-marketing-101')->first()?->id,
            'web-design-basics'    => CityCourse::where('slug', 'web-design-basics')->first()?->id,
            'digital-skills-intro' => CityCourse::where('slug', 'digital-skills-intro')->first()?->id,
            'financial-literacy-101'=> CityCourse::where('slug', 'financial-literacy-101')->first()?->id,
            'sales-101'            => CityCourse::where('slug', 'sales-101')->first()?->id,
            'social-media-hustle'  => CityCourse::where('slug', 'social-media-hustle')->first()?->id,
        ];

        $jobIds = $this->seedJobs($courseIds);
        $this->seedQuests($jobIds);

        $this->command?->info('Level 4-10 (18-25) content seeded: 7 jobs, 14 quests.');
    }

    // ── Jobs ─────────────────────────────────────────────────────────────────

    private function seedJobs(array $courseIds): array
    {
        $hasAgesCol = Schema::hasColumn('city_jobs', 'age_groups');
        $hasReqIds  = Schema::hasColumn('city_jobs', 'required_course_ids');

        $jobs = [
            [
                'key'                => 'loan_officer',
                'title'              => 'Junior Loan Officer',
                'employer_name'      => 'Tumaini Sacco',
                'employer_logo'      => '🏦',
                'career_track'       => 'finance',
                'level'              => 4,
                'salary_kes_month'   => 50000,
                'xp_reward'          => 70,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'required_course_id' => $courseIds['bookkeeping-basics'],
                'description'        => 'Review small loan applications for Sacco members, check repayment ability against real budgets, and learn why character and cash flow matter more than collateral for a small lender.',
            ],
            [
                'key'                => 'marketing_associate',
                'title'              => 'Digital Marketing Associate',
                'employer_name'      => 'AdMax Kenya',
                'employer_logo'      => '📈',
                'career_track'       => 'creative',
                'level'              => 5,
                'salary_kes_month'   => 58000,
                'xp_reward'          => 85,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'required_course_id' => $courseIds['digital-marketing-101'],
                'description'        => 'Plan paid campaigns for real Nairobi brands and track cost-per-click against actual sales. A "viral" post that never converts is just expensive entertainment.',
            ],
            [
                'key'                 => 'jr_developer',
                'title'               => 'Junior Software Developer',
                'employer_name'       => 'CodeHut Nairobi',
                'employer_logo'       => '💻',
                'career_track'        => 'tech',
                'level'               => 6,
                'salary_kes_month'    => 67000,
                'xp_reward'           => 100,
                'is_part_time'        => false,
                'employment_type'     => 'full_time',
                'required_course_id'  => $courseIds['web-design-basics'],
                'required_course_ids' => $hasReqIds ? array_values(array_filter([$courseIds['digital-skills-intro'], $courseIds['web-design-basics']])) : null,
                'description'         => "Ship small features for paying clients under a senior dev's review. Code nobody can maintain is a liability dressed up as an asset.",
            ],
            [
                'key'                => 'branch_supervisor',
                'title'              => 'Branch Operations Supervisor',
                'employer_name'      => 'Tumaini Sacco',
                'employer_logo'      => '🏦',
                'career_track'       => 'finance',
                'level'              => 7,
                'salary_kes_month'   => 77000,
                'xp_reward'          => 115,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'required_course_id' => $courseIds['financial-literacy-101'],
                'description'        => 'Run daily branch operations and approve the larger loans your old desk could only recommend — while managing tellers who once trained you. A promotion is a bigger ledger, not a smaller workload.',
            ],
            [
                'key'                => 'biz_dev_exec',
                'title'              => 'Business Development Executive',
                'employer_name'      => 'Vuka Logistics',
                'employer_logo'      => '🚚',
                'career_track'       => 'business',
                'level'              => 8,
                'salary_kes_month'   => 88000,
                'xp_reward'          => 130,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'required_course_id' => $courseIds['sales-101'],
                'description'        => 'Win freight contracts for a growing logistics firm on base plus commission. Every signed client is recurring revenue, not a one-off sale.',
            ],
            [
                'key'                => 'product_marketing_lead',
                'title'              => 'Product Marketing Lead',
                'employer_name'      => 'AdMax Kenya',
                'employer_logo'      => '📈',
                'career_track'       => 'creative',
                'level'              => 9,
                'salary_kes_month'   => 100000,
                'xp_reward'          => 145,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'required_course_id' => $courseIds['social-media-hustle'],
                'description'        => 'Own the go-to-market plan for every client launch and manage the associates you used to work alongside. The job changed — the accountability changed more.',
            ],
            [
                'key'                => 'regional_ops_manager',
                'title'              => 'Regional Operations Manager',
                'employer_name'      => 'Tumaini Sacco',
                'employer_logo'      => '🏦',
                'career_track'       => 'finance',
                'level'              => 10,
                'salary_kes_month'   => 115000,
                'xp_reward'          => 160,
                'is_part_time'       => false,
                'employment_type'    => 'full_time',
                'required_course_id' => $courseIds['financial-literacy-101'],
                'description'        => "Oversee four branches, set lending policy, and answer for every member's trust in the Sacco. The ledger you once balanced now balances a whole region.",
            ],
        ];

        $ids = [];
        foreach ($jobs as $j) {
            $key = $j['key'];
            unset($j['key']);

            if (!$hasReqIds) {
                unset($j['required_course_ids']);
            }

            $j['age_group'] = '18-25';
            if ($hasAgesCol) {
                $j['age_groups'] = ['18-25'];
            }

            $row = CityJob::updateOrCreate(
                ['title' => $j['title'], 'employer_name' => $j['employer_name']],
                $j + ['is_active' => true]
            );
            $ids[$key] = $row->id;
        }

        return $ids;
    }

    // ── Quests ───────────────────────────────────────────────────────────────

    private function seedQuests(array $jobIds): void
    {
        $quests = [
            // ══ LEVEL 4 ══
            [
                'title'         => 'Emergency Fund Starter',
                'description'   => 'Adulting means your own safety net. Grow your total savings to Ksh 12,000.',
                'icon'          => '🛟',
                'xp_reward'     => 220,
                'kes_reward'    => 200,
                'level_required'=> 4,
                'trigger_type'  => 'reach_savings',
                'trigger_value' => '12000',
                'trigger_label' => 'Reach Ksh 12,000 in total savings',
                'lesson'        => "An emergency fund isn't an investment — it's insurance you pay yourself, so one flat tyre or one missed matatu fare never becomes a debt spiral.",
                'sort_order'    => 4,
            ],
            [
                'title'         => 'Circle of Growth',
                'description'   => 'Sharpen your money knowledge and put it to work in a group: complete Financial Literacy 101 and join a chama.',
                'icon'          => '🤝',
                'xp_reward'     => 310,
                'kes_reward'    => 280,
                'level_required'=> 4,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'take_course', 'values' => ['financial-literacy-101'], 'label' => 'Complete Financial Literacy 101'],
                    ['type' => 'join_chama',  'values' => [], 'label' => 'Join a chama'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => "Chamas work because peer pressure that's pointed at saving instead of spending is one of the strongest financial tools ever invented in Kenya.",
                'sort_order'    => 4,
            ],

            // ══ LEVEL 5 ══
            [
                'title'         => 'Land the Marketing Seat',
                'description'   => 'Get hired as a Digital Marketing Associate at AdMax Kenya.',
                'icon'          => '📈',
                'xp_reward'     => 280,
                'kes_reward'    => 300,
                'level_required'=> 5,
                'trigger_type'  => 'get_job',
                'trigger_value' => (string) ($jobIds['marketing_associate'] ?? ''),
                'trigger_label' => 'Get hired as a Digital Marketing Associate',
                'lesson'        => 'A paid skill that lands a job with upside beats a bigger starting salary with none — check what a role can grow into, not just what it pays on day one.',
                'sort_order'    => 5,
            ],
            [
                'title'         => "Marketer's Nest Egg",
                'description'   => 'Prove the skill and bank the proof: complete Digital Marketing 101 and grow your wallet balance to Ksh 15,000.',
                'icon'          => '🥚',
                'xp_reward'     => 390,
                'kes_reward'    => 420,
                'level_required'=> 5,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'take_course',   'values' => ['digital-marketing-101'], 'label' => 'Complete Digital Marketing 101'],
                    ['type' => 'reach_balance', 'values' => ['15000'], 'label' => 'Grow your wallet balance to Ksh 15,000'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => "Wallet cash isn't the same as wealth — it's just money that hasn't decided what to become yet. Growing it on purpose is the first decision.",
                'sort_order'    => 5,
            ],

            // ══ LEVEL 6 ══
            [
                'title'         => 'Grow It Safely',
                'description'   => 'Not every shilling needs to chase risk. Buy any Fixed Income asset from the Marketplace.',
                'icon'          => '🏛️',
                'xp_reward'     => 340,
                'kes_reward'    => 400,
                'level_required'=> 6,
                'trigger_type'  => 'buy_item_category',
                'trigger_value' => 'fixed_income',
                'trigger_label' => 'Buy a Money Market Fund, T-Bill or T-Bond',
                'lesson'        => "Money Market Funds and Treasury instruments won't make you rich fast — that's the point. Boring and guaranteed beats exciting and uncertain for money you can't afford to lose.",
                'sort_order'    => 6,
            ],
            [
                'title'         => 'Code Your Way Up',
                'description'   => 'Stack the skill and land the seat: complete Web Design Basics, then get hired as a Junior Software Developer.',
                'icon'          => '🚀',
                'xp_reward'     => 480,
                'kes_reward'    => 560,
                'level_required'=> 6,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'take_course', 'values' => ['web-design-basics'], 'label' => 'Complete Web Design Basics'],
                    ['type' => 'get_job',     'values' => [(string) ($jobIds['jr_developer'] ?? '')], 'label' => 'Get hired as a Junior Software Developer'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => 'Stacked skills multiply each other — Digital Skills alone was worth little, paired with Web Design it opened a whole career track.',
                'sort_order'    => 6,
            ],

            // ══ LEVEL 7 ══
            [
                'title'         => 'Building Real Worth',
                'description'   => 'Push your net worth past Ksh 150,000 — savings, assets and income all counted.',
                'icon'          => '📊',
                'xp_reward'     => 400,
                'kes_reward'    => 500,
                'level_required'=> 7,
                'trigger_type'  => 'reach_net_worth',
                'trigger_value' => '150000',
                'trigger_label' => 'Push your net worth past Ksh 150,000',
                'lesson'        => "Net worth is the only number that tells the truth — salary shows what you earn, net worth shows what you kept.",
                'sort_order'    => 7,
            ],
            [
                'title'         => 'Recognition & Reserves',
                'description'   => 'Earn recognition for your progress and back it with cash: earn any badge and deposit Ksh 20,000 into savings.',
                'icon'          => '🏅',
                'xp_reward'     => 560,
                'kes_reward'    => 700,
                'level_required'=> 7,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'earn_badge',      'values' => [], 'label' => 'Earn any badge'],
                    ['type' => 'deposit_savings', 'values' => ['20000'], 'label' => 'Deposit Ksh 20,000 into savings'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => "Badges are proof of a habit; a Ksh 20,000 deposit is proof the habit has a price tag you can actually pay. Do both and you're not just talking about discipline.",
                'sort_order'    => 7,
            ],

            // ══ LEVEL 8 ══
            [
                'title'         => 'Cash Flow Command',
                'description'   => 'Grow your wallet balance to Ksh 25,000 without draining your savings to do it.',
                'icon'          => '💵',
                'xp_reward'     => 470,
                'kes_reward'    => 650,
                'level_required'=> 8,
                'trigger_type'  => 'reach_balance',
                'trigger_value' => '25000',
                'trigger_label' => 'Grow your wallet balance to Ksh 25,000',
                'lesson'        => "Healthy cash flow means bills never eat into savings. A fat wallet balance sitting next to empty savings is a warning, not a win — this quest checks you're not doing that.",
                'sort_order'    => 8,
            ],
            [
                'title'         => 'Executive Track',
                'description'   => 'Get hired as a Business Development Executive at Vuka Logistics and push your net worth past Ksh 300,000.',
                'icon'          => '🚛',
                'xp_reward'     => 660,
                'kes_reward'    => 910,
                'level_required'=> 8,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'get_job',         'values' => [(string) ($jobIds['biz_dev_exec'] ?? '')], 'label' => 'Get hired as a Business Development Executive'],
                    ['type' => 'reach_net_worth', 'values' => ['300000'], 'label' => 'Push your net worth past Ksh 300,000'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => 'Commission-based roles reward the same math as investing: more effort, deployed consistently, compounds into results that a flat salary never will.',
                'sort_order'    => 8,
            ],

            // ══ LEVEL 9 ══
            [
                'title'         => 'Six-Figure Cushion',
                'description'   => 'Grow your total savings to Ksh 55,000 — a real buffer, not just a habit anymore.',
                'icon'          => '🧱',
                'xp_reward'     => 550,
                'kes_reward'    => 800,
                'level_required'=> 9,
                'trigger_type'  => 'reach_savings',
                'trigger_value' => '55000',
                'trigger_label' => 'Reach Ksh 55,000 in total savings',
                'lesson'        => 'At this size, savings stop being an emergency fund and start being the seed capital for your next investment decision.',
                'sort_order'    => 9,
            ],
            [
                'title'         => 'Marketing Ascension',
                'description'   => 'Push your net worth past Ksh 400,000 and get promoted into the Product Marketing Lead seat at AdMax Kenya.',
                'icon'          => '🏆',
                'xp_reward'     => 770,
                'kes_reward'    => 1120,
                'level_required'=> 9,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'reach_net_worth', 'values' => ['400000'], 'label' => 'Push your net worth past Ksh 400,000'],
                    ['type' => 'get_job',         'values' => [(string) ($jobIds['product_marketing_lead'] ?? '')], 'label' => 'Get hired as Product Marketing Lead'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => "Promotions rarely arrive before the results do — your net worth climbing is the evidence that earns the title, not the other way round.",
                'sort_order'    => 9,
            ],

            // ══ LEVEL 10 ══
            [
                'title'         => 'Legacy Foundations',
                'description'   => 'Push your net worth past Ksh 750,000 — the kind of number that starts changing what your family can afford.',
                'icon'          => '🏗️',
                'xp_reward'     => 650,
                'kes_reward'    => 1000,
                'level_required'=> 10,
                'trigger_type'  => 'reach_net_worth',
                'trigger_value' => '750000',
                'trigger_label' => 'Push your net worth past Ksh 750,000',
                'lesson'        => 'Every generational wealth story in Kenya has a first person who crossed a number like this and refused to spend it all back down to zero.',
                'sort_order'    => 10,
            ],
            [
                'title'         => 'The Million Shilling Milestone',
                'description'   => 'The full capstone: complete Financial Literacy 101, get promoted into Regional Operations Manager at Tumaini Sacco, and cross Ksh 1,000,000 in net worth.',
                'icon'          => '👑',
                'xp_reward'     => 910,
                'kes_reward'    => 1400,
                'level_required'=> 10,
                'trigger_type'  => null,
                'trigger_value' => null,
                'trigger_label' => null,
                'triggers'      => [
                    ['type' => 'take_course',     'values' => ['financial-literacy-101'], 'label' => 'Complete Financial Literacy 101'],
                    ['type' => 'get_job',         'values' => [(string) ($jobIds['regional_ops_manager'] ?? '')], 'label' => 'Get hired as Regional Operations Manager'],
                    ['type' => 'reach_net_worth', 'values' => ['1000000'], 'label' => 'Cross Ksh 1,000,000 in net worth'],
                ],
                'trigger_mode'  => 'all',
                'lesson'        => "Knowledge, income and net worth — the same three pillars from your very first week in Pesa City, just at a scale that now changes a real life. The game repeats itself; wealth is just that loop, run patiently for longer.",
                'sort_order'    => 10,
            ],
        ];

        foreach ($quests as $q) {
            $data = $q;
            unset($data['title']);
            $data['age_group'] = '18-25';
            $data['is_active'] = true;

            Quest::updateOrCreate(['title' => $q['title']], $data);
        }
    }
}
