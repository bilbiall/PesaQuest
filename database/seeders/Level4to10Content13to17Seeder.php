<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * Fills the level 4-10 gap for the 13-17 age group: quests and jobs stopped
 * dead at level 3 across the whole game (see content-coverage audit,
 * Aug 2026) — this is the teen slice of closing that gap.
 */
class Level4to10Content13to17Seeder extends Seeder
{
    public function run(): void
    {
        // ── Level 4 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Level Up Your Skills'], [
            'description'    => 'Complete the Digital Skills Intro course — every good hustle now runs through a phone or a computer.',
            'icon'            => '💻',
            'xp_reward'       => 220,
            'kes_reward'      => 200,
            'level_required'  => 4,
            'trigger_type'    => 'take_course',
            'trigger_value'   => 'digital-skills-intro',
            'trigger_label'   => 'Complete the Digital Skills Intro course',
            'lesson'          => "Digital skills aren't optional anymore — even a posho mill records its books on a phone these days.",
            'sort_order'      => 40,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        $circleOfTrust = Quest::updateOrCreate(['title' => 'Circle of Trust'], [
            'description'    => 'Join a chama with friends, then grow your slice of it to Ksh 1,500 — teen chamas are how many big businesses in Kenya actually started.',
            'icon'            => '🤝',
            'xp_reward'       => 310,
            'kes_reward'      => 280,
            'level_required'  => 4,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'join_chama',      'values' => [],       'label' => 'Join a chama'],
                ['type' => 'deposit_savings', 'values' => ['1500'], 'label' => 'Grow your chama savings to Ksh 1,500'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'A chama turns small, regular contributions into money nobody could save alone. Trust is the real currency.',
            'sort_order'      => 41,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        // ── Level 5 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Weekend Hustle Fund'], [
            'description'    => "Push your total savings to Ksh 2,500 — prove the weekend hustle money isn't just for spending.",
            'icon'            => '📈',
            'xp_reward'       => 280,
            'kes_reward'      => 300,
            'level_required'  => 5,
            'trigger_type'    => 'reach_savings',
            'trigger_value'   => '2500',
            'trigger_label'   => 'Get your total savings to Ksh 2,500',
            'lesson'          => 'Savings you can see becomes savings you protect. The number itself starts changing your decisions.',
            'sort_order'      => 50,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        Quest::updateOrCreate(['title' => 'First Real Payday'], [
            'description'    => 'Land any job in Pesa City, then bank Ksh 2,000 from your first paychecks before you spend a shilling on wants.',
            'icon'            => '💼',
            'xp_reward'       => 390,
            'kes_reward'      => 420,
            'level_required'  => 5,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'get_job',         'values' => [],       'label' => 'Get hired for any job'],
                ['type' => 'deposit_savings', 'values' => ['2000'], 'label' => 'Bank Ksh 2,000 of your earnings'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => "The habit that separates people who build wealth from people who don't: pay your savings before you pay your wants.",
            'sort_order'      => 51,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        // ── Level 6 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Smart Spender'], [
            'description'    => 'Buy a gadget from the Marketplace — but only one that actually holds value or helps you earn, not just one that looks cool.',
            'icon'            => '🎧',
            'xp_reward'       => 340,
            'kes_reward'      => 400,
            'level_required'  => 6,
            'trigger_type'    => 'buy_item_category',
            'trigger_value'   => 'gadget',
            'trigger_label'   => 'Buy any gadget item',
            'lesson'          => 'Not all spending is wasted spending. A gadget that helps you study, work, or sell is an investment in disguise.',
            'sort_order'      => 60,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        Quest::updateOrCreate(['title' => 'Sales Apprentice'], [
            'description'    => 'Complete Sales 101, then put it to work until it earns you a badge.',
            'icon'            => '🗣️',
            'xp_reward'       => 480,
            'kes_reward'      => 560,
            'level_required'  => 6,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'take_course', 'values' => ['sales-101'], 'label' => 'Complete the "Sales 101" course'],
                ['type' => 'earn_badge',  'values' => [],            'label' => 'Earn any badge'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'Selling is a skill, not a personality trait. Every shilling in this economy passes through someone who knows how to close.',
            'sort_order'      => 61,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        // ── Level 7 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Badge Hunter'], [
            'description'    => 'Earn any badge in Pesa City — recognition that your habits, not just your luck, are building results.',
            'icon'            => '🏅',
            'xp_reward'       => 400,
            'kes_reward'      => 500,
            'level_required'  => 7,
            'trigger_type'    => 'earn_badge',
            'trigger_value'   => '',
            'trigger_label'   => 'Earn any badge',
            'lesson'          => "Badges track consistency. In real life, a good credit score works the same way — it's proof you kept showing up.",
            'sort_order'      => 70,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        Quest::updateOrCreate(['title' => 'Lucky Break Fund'], [
            'description'    => "Grow your wallet to Ksh 3,000, then take one spin on the Lucky Wheel — with money already saved, a spin is fun, not a gamble you need to win.",
            'icon'            => '🎡',
            'xp_reward'       => 560,
            'kes_reward'      => 700,
            'level_required'  => 7,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'reach_balance', 'values' => ['3000'], 'label' => 'Grow your wallet to Ksh 3,000'],
                ['type' => 'spin_wheel',    'values' => [],       'label' => 'Spin the Lucky Wheel'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => "Games of chance feel very different when you've already secured your basics. Never spin what you can't afford to lose.",
            'sort_order'      => 71,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        // ── Level 8 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Building Something Real'], [
            'description'    => 'Push your net worth past Ksh 15,000 — savings, assets, and income all counted together.',
            'icon'            => '🏗️',
            'xp_reward'       => 470,
            'kes_reward'      => 650,
            'level_required'  => 8,
            'trigger_type'    => 'reach_net_worth',
            'trigger_value'   => '15000',
            'trigger_label'   => 'Push your net worth past Ksh 15,000',
            'lesson'          => "Net worth is the real scoreboard, not your wallet balance. It's everything you own minus everything you owe.",
            'sort_order'      => 80,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        Quest::updateOrCreate(['title' => 'Tech Starter Kit'], [
            'description'    => 'Complete Web Design Basics, then buy a gadget to actually practice on — skills need tools.',
            'icon'            => '🌐',
            'xp_reward'       => 660,
            'kes_reward'      => 910,
            'level_required'  => 8,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'take_course',       'values' => ['web-design-basics'], 'label' => 'Complete the "Web Design Basics" course'],
                ['type' => 'buy_item_category', 'values' => ['gadget'],            'label' => 'Buy any gadget item'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'A skill without a tool to use it on stays theoretical. Pair every course with something you can practice on.',
            'sort_order'      => 81,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        // ── Level 9 ─────────────────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Wallet Watch'], [
            'description'    => 'Keep Ksh 4,000 sitting in your wallet balance without blowing it — discipline is holding money you could spend.',
            'icon'            => '💰',
            'xp_reward'       => 550,
            'kes_reward'      => 800,
            'level_required'  => 9,
            'trigger_type'    => 'reach_balance',
            'trigger_value'   => '4000',
            'trigger_label'   => 'Grow your wallet to Ksh 4,000',
            'lesson'          => 'Having money and keeping money are two different skills. The second one is harder and matters more.',
            'sort_order'      => 90,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        Quest::updateOrCreate(['title' => 'Second Job, Second Wind'], [
            'description'    => "Take on a new job and grow your savings by another Ksh 4,000 — stacking income sources is how teens in Nairobi's estates build real capital before 18.",
            'icon'            => '🧭',
            'xp_reward'       => 770,
            'kes_reward'      => 1120,
            'level_required'  => 9,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'get_job',         'values' => [],       'label' => 'Get hired for any job'],
                ['type' => 'deposit_savings', 'values' => ['4000'], 'label' => 'Grow your savings by Ksh 4,000'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => "One income stream is fragile. A second one — even a small one — is what protects you when the first one wobbles.",
            'sort_order'      => 91,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        // ── Level 10 (capstone) ─────────────────────────────────────────────
        Quest::updateOrCreate(['title' => 'Teen Tycoon Fund'], [
            'description'    => "Grow your total savings to Ksh 8,000 — the biggest number you've saved yet in Pesa City.",
            'icon'            => '🏦',
            'xp_reward'       => 650,
            'kes_reward'      => 1000,
            'level_required'  => 10,
            'trigger_type'    => 'reach_savings',
            'trigger_value'   => '8000',
            'trigger_label'   => 'Get your total savings to Ksh 8,000',
            'lesson'          => 'Ksh 8,000 saved by 17 compounds for decades. Time in the market usually beats timing the market.',
            'sort_order'      => 100,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        Quest::updateOrCreate(['title' => 'Full Circle'], [
            'description'    => "Prove the loop works: have your chama savings from \"Circle of Trust\" still active, and push your net worth past Ksh 25,000.",
            'icon'            => '🔁',
            'xp_reward'       => 910,
            'kes_reward'      => 1400,
            'level_required'  => 10,
            'trigger_type'    => null,
            'trigger_value'   => null,
            'trigger_label'   => null,
            'triggers'        => [
                ['type' => 'complete_quest',   'values' => [(string) $circleOfTrust->id], 'label' => 'Have completed "Circle of Trust"'],
                ['type' => 'reach_net_worth',  'values' => ['25000'],                     'label' => 'Push your net worth past Ksh 25,000'],
            ],
            'trigger_mode'    => 'all',
            'lesson'          => 'The biggest financial wins rarely come from one clever move — they come from old habits compounding quietly in the background.',
            'sort_order'      => 101,
            'is_active'       => true,
            'age_group'       => '13-17',
        ]);

        // ── Jobs, one per level ──────────────────────────────────────────────
        $digitalSkills = CityCourse::where('slug', 'digital-skills-intro')->first()?->id;
        $webDesign     = CityCourse::where('slug', 'web-design-basics')->first()?->id;
        $bookkeeping   = CityCourse::where('slug', 'bookkeeping-basics')->first()?->id;
        $socialMedia   = CityCourse::where('slug', 'social-media-hustle')->first()?->id;
        $bodaSafety    = CityCourse::where('slug', 'boda-road-safety')->first()?->id;

        $jobs = [
            [
                'title'              => 'Cyber Café Shift Supervisor',
                'employer_name'      => 'ClickPoint Cyber',
                'employer_logo'      => '🖱️',
                'career_track'       => 'tech',
                'salary_kes_month'   => 22000,
                'level'              => 4,
                'xp_reward'          => 70,
                'is_part_time'       => true,
                'employment_type'    => 'part_time',
                'required_course_id' => $digitalSkills,
                'description'        => 'Open and close the café, run the shift till, and train the newest junior assistant — more trust than the average Saturday job.',
            ],
            [
                'title'              => 'Tuition Circle Organizer',
                'employer_name'      => 'Bright Steps Tuition',
                'employer_logo'      => '📘',
                'career_track'       => 'business',
                'salary_kes_month'   => 24000,
                'level'              => 5,
                'xp_reward'          => 85,
                'is_part_time'       => true,
                'employment_type'    => 'part_time',
                'required_course_id' => null,
                'description'        => 'Organize and charge for weekend tuition sessions for younger kids in your estate — book the room, collect fees, pay the volunteer teacher.',
            ],
            [
                'title'              => 'Junior Event MC & Sound Hire',
                'employer_name'      => 'Vibe Setters Events',
                'employer_logo'      => '🎤',
                'career_track'       => 'creative',
                'salary_kes_month'   => 26000,
                'level'              => 6,
                'xp_reward'          => 100,
                'is_part_time'       => true,
                'employment_type'    => 'part_time',
                'required_course_id' => $socialMedia,
                'description'        => 'MC small estate events and hire out a basic speaker system on weekends — book gigs, collect deposits, deliver on the day.',
            ],
            [
                'title'              => 'Boda Dispatch Coordinator',
                'employer_name'      => 'Haraka Deliveries',
                'employer_logo'      => '🛵',
                'career_track'       => 'business',
                'salary_kes_month'   => 28000,
                'level'              => 7,
                'xp_reward'          => 115,
                'is_part_time'       => true,
                'employment_type'    => 'part_time',
                'required_course_id' => $bodaSafety,
                'description'        => 'Coordinate delivery riders from a phone-based dispatch board — match orders to riders, track late deliveries, reconcile rider payouts.',
            ],
            [
                'title'              => 'Web Intern',
                'employer_name'      => 'Savanna Digital Agency',
                'employer_logo'      => '🦁',
                'career_track'       => 'tech',
                'salary_kes_month'   => 30500,
                'level'              => 8,
                'xp_reward'          => 130,
                'is_part_time'       => true,
                'employment_type'    => 'part_time',
                'required_course_id' => $webDesign,
                'description'        => 'Shadow a senior developer and fix small bugs on live client sites — your first taste of a tech career track.',
            ],
            [
                'title'              => 'Small Business Bookkeeper',
                'employer_name'      => 'Duka la Mama Njeri',
                'employer_logo'      => '🏪',
                'career_track'       => 'finance',
                'salary_kes_month'   => 33000,
                'level'              => 9,
                'xp_reward'          => 145,
                'is_part_time'       => true,
                'employment_type'    => 'part_time',
                'required_course_id' => $bookkeeping,
                'description'        => "Take over the shop's daily cash reconciliation from Mama Njeri herself — every shilling must balance before you go home.",
            ],
            [
                'title'              => 'Junior Social Media Strategist',
                'employer_name'      => 'Nairobi Bites Kitchen',
                'employer_logo'      => '🍲',
                'career_track'       => 'creative',
                'salary_kes_month'   => 36000,
                'level'              => 10,
                'xp_reward'          => 160,
                'is_part_time'       => true,
                'employment_type'    => 'part_time',
                'required_course_id' => $socialMedia,
                'description'        => 'Run paid ad campaigns on small budgets for a growing restaurant brand — plan the spend, track what each shilling of ad budget returns.',
            ],
        ];

        foreach ($jobs as $j) {
            CityJob::updateOrCreate(
                ['title' => $j['title'], 'employer_name' => $j['employer_name']],
                $j + ['is_active' => true, 'age_group' => '13-17']
            );
        }
    }
}
