<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use App\Models\CityJob;
use Illuminate\Database\Seeder;

class CityCourseSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent — skip if courses already exist
        if (CityCourse::count() > 0) {
            $this->command->info('CityCourseSeeder: courses already exist, skipping.');
            return;
        }

        $courses = [
            [
                'title'          => 'Web Development Fundamentals',
                'slug'           => 'web-dev-fundamentals',
                'description'    => 'Learn HTML, CSS and JavaScript basics to build websites and launch a career in tech.',
                'content'        => "MODULE 1 — HOW THE WEB WORKS\n• What browsers do and how servers respond\n• HTML: the skeleton of every webpage\n• CSS: making things look good\n• JavaScript: making things interactive\n\nMODULE 2 — BUILD YOUR FIRST PAGE\n• Create a personal portfolio page\n• Add links, images and buttons\n• Style with colors and fonts\n\nMODULE 3 — FREELANCING IN TECH\n• Where to find your first client (Upwork, Fiverr, local referrals)\n• How to price your work\n• Delivering projects professionally\n\nKEY TAKEAWAY: Tech skills pay — a junior dev earns KES 45K+/month even fresh out of training.",
                'icon'           => '💻',
                'career_track'   => 'tech',
                'color'          => '#4DA8F7',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 3,
                'difficulty'     => 'beginner',
                'xp_reward'      => 60,
                'outcome'        => 'Unlocks Junior Developer, Freelance Dev & UI/UX Intern roles',
                'financial_tip'  => 'Skills are the highest-return investment you can make. A web developer in Nairobi earns KES 60,000–200,000/month. Every hour you spend learning code today compounds into future earnings — unlike a car or phone, skills never depreciate.',
                'jobs_intro'     => 'Tech companies and startups are hiring — and this course qualifies you for 3 entry-level roles in Pesa City.',
                'is_active'      => true,
            ],
            [
                'title'          => 'Business Fundamentals',
                'slug'           => 'business-fundamentals',
                'description'    => 'Understand markets, customers, pricing and the basics of running a profitable business.',
                'content'        => "MODULE 1 — HOW BUSINESSES MAKE MONEY\n• Revenue vs profit — the difference that kills most businesses\n• Understanding your target customer\n• Pricing: cost-plus vs value-based\n\nMODULE 2 — SALES & CUSTOMER ACQUISITION\n• The sales funnel: awareness → interest → decision → action\n• How to pitch confidently\n• Building repeat customers\n\nMODULE 3 — FINANCIAL BASICS FOR BUSINESS OWNERS\n• Separating personal and business money (this one matters A LOT)\n• Cash flow forecasting: will you survive next month?\n• Reinvesting profit vs paying yourself\n\nKEY TAKEAWAY: Understanding business finances is what separates owners who grow from owners who close.",
                'icon'           => '💼',
                'career_track'   => 'business',
                'color'          => '#A78BFA',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 2,
                'difficulty'     => 'beginner',
                'xp_reward'      => 55,
                'outcome'        => 'Unlocks Sales Representative, Business Development & Retail roles',
                'financial_tip'  => 'Cash flow is the #1 killer of businesses — not bad products. 82% of small businesses that fail cite cash flow problems. Always separate your business account from your personal account from day one. Pay yourself a salary, never just draw whenever you feel like it.',
                'jobs_intro'     => 'Businesses across Pesa City need people who understand commerce. This course opens 3 roles in sales and business development.',
                'is_active'      => true,
            ],
            [
                'title'          => 'Personal Finance & Investing',
                'slug'           => 'personal-finance-investing',
                'description'    => 'Master budgeting, saving strategies and the basics of growing your money through smart investments.',
                'content'        => "MODULE 1 — BUDGETING THAT ACTUALLY WORKS\n• The 50/30/20 rule explained with Kenyan salary examples\n• Fixed vs variable expenses\n• Why budgets fail — and how to fix yours\n\nMODULE 2 — SAVING WITH PURPOSE\n• Emergency fund: 3–6 months of expenses, non-negotiable\n• Goal-based saving: label every shilling before you spend it\n• Money market funds vs savings accounts in Kenya\n\nMODULE 3 — INVESTING BASICS\n• Compound interest: the most powerful force in personal finance\n• NSE shares, Treasury bills, unit trusts explained simply\n• How to start investing with KES 1,000/month\n\nKEY TAKEAWAY: KES 5,000/month invested at 15% annual return = KES 1.4 million in 10 years.",
                'icon'           => '📊',
                'career_track'   => 'finance',
                'color'          => '#15C77E',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 2,
                'difficulty'     => 'beginner',
                'xp_reward'      => 55,
                'outcome'        => 'Unlocks Bank Teller, Financial Analyst Intern & Investment Advisor roles',
                'financial_tip'  => 'The 50/30/20 rule: 50% needs, 30% wants, 20% savings. But the real magic is starting early. If you invest KES 5,000/month at 15% annual return from age 22, you\'ll have KES 1.4M by 32. Wait until 30 to start and you\'ll have only KES 438K at 40 — the same number of years, but starting late cost you KES 960,000.',
                'jobs_intro'     => 'Financial institutions in Pesa City reward those who understand money. Complete this course to qualify for 3 finance sector roles.',
                'is_active'      => true,
            ],
            [
                'title'          => 'Digital Marketing Essentials',
                'slug'           => 'digital-marketing-essentials',
                'description'    => 'Learn social media strategy, content creation and how to grow brands online — skills every business needs.',
                'content'        => "MODULE 1 — THE DIGITAL LANDSCAPE\n• Where Kenyan consumers spend time online (Instagram, TikTok, X, LinkedIn)\n• Organic vs paid marketing\n• Understanding algorithms: what gets shown and why\n\nMODULE 2 — CONTENT CREATION\n• Writing copy that converts: headlines, hooks, calls to action\n• Basic graphic design for social media (Canva walkthrough)\n• Short-form video: why it wins and how to make it\n\nMODULE 3 — MEASURING RESULTS\n• Impressions, reach, clicks, conversions — what actually matters\n• Setting up Meta Business Suite and Google Analytics\n• Reporting your work to a client or employer\n\nKEY TAKEAWAY: Marketers who can prove ROI earn 40% more than those who can't. Track everything.",
                'icon'           => '🎨',
                'career_track'   => 'creative',
                'color'          => '#FF6B35',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 2,
                'difficulty'     => 'beginner',
                'xp_reward'      => 55,
                'outcome'        => 'Unlocks Social Media Manager, Content Creator & Brand Strategist roles',
                'financial_tip'  => 'Creative skills have exploded in market value. A social media manager in Nairobi earns KES 40,000–120,000/month. The financial secret: build a personal brand while employed. Every post you create is an asset that keeps attracting clients even while you sleep — it\'s the closest thing to passive income for creatives.',
                'jobs_intro'     => 'Every brand in Pesa City needs a digital presence. This course qualifies you for 3 creative and marketing roles.',
                'is_active'      => true,
            ],
        ];

        $createdCourses = [];
        foreach ($courses as $courseData) {
            $createdCourses[$courseData['career_track']] = CityCourse::create($courseData);
        }

        // Jobs linked to each course
        $jobs = [
            // Tech jobs — linked to Web Dev course
            'tech' => [
                ['title' => 'Junior Web Developer',     'employer_name' => 'Andela Kenya',          'employer_logo' => '💻', 'salary_kes_month' => 45000, 'level' => 1],
                ['title' => 'Freelance Developer',       'employer_name' => 'Self-Employed',          'employer_logo' => '🧑‍💻', 'salary_kes_month' => 65000, 'level' => 2],
                ['title' => 'UI/UX Design Intern',       'employer_name' => 'Safaricom PLC',          'employer_logo' => '📱', 'salary_kes_month' => 30000, 'level' => 1],
            ],
            // Business jobs — linked to Business Fundamentals
            'business' => [
                ['title' => 'Sales Representative',      'employer_name' => 'Equity Bank Kenya',      'employer_logo' => '💰', 'salary_kes_month' => 35000, 'level' => 1],
                ['title' => 'Business Dev Associate',    'employer_name' => 'NCBA Group',             'employer_logo' => '📈', 'salary_kes_month' => 50000, 'level' => 2],
                ['title' => 'Retail Supervisor Trainee', 'employer_name' => 'Naivas Supermarket',     'employer_logo' => '🛒', 'salary_kes_month' => 38000, 'level' => 1],
            ],
            // Finance jobs — linked to Personal Finance course
            'finance' => [
                ['title' => 'Bank Teller',               'employer_name' => 'KCB Group',              'employer_logo' => '🏦', 'salary_kes_month' => 32000, 'level' => 1],
                ['title' => 'Financial Analyst Intern',  'employer_name' => 'Britam Holdings',        'employer_logo' => '📊', 'salary_kes_month' => 42000, 'level' => 1],
                ['title' => 'Investment Advisor Trainee','employer_name' => 'Old Mutual Kenya',       'employer_logo' => '💹', 'salary_kes_month' => 48000, 'level' => 2],
            ],
            // Creative jobs — linked to Digital Marketing course
            'creative' => [
                ['title' => 'Social Media Manager',      'employer_name' => 'Jumia Kenya',            'employer_logo' => '📱', 'salary_kes_month' => 40000, 'level' => 1],
                ['title' => 'Content Creator',           'employer_name' => 'iHub Nairobi',           'employer_logo' => '🎬', 'salary_kes_month' => 35000, 'level' => 1],
                ['title' => 'Brand Strategist Junior',   'employer_name' => 'Nation Media Group',     'employer_logo' => '📰', 'salary_kes_month' => 47000, 'level' => 2],
            ],
        ];

        foreach ($jobs as $track => $trackJobs) {
            $courseId = $createdCourses[$track]?->id;
            foreach ($trackJobs as $job) {
                CityJob::create(array_merge($job, [
                    'career_track'       => $track,
                    'required_course_id' => $courseId,
                    'is_active'          => true,
                ]));
            }
        }

        $this->command->info('CityCourseSeeder: seeded 4 courses and 12 jobs.');
    }
}
