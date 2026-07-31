<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\Quest;
use Illuminate\Database\Seeder;

/**
 * Interlinked rollout content for levels 1–3:
 *   10 courses → 10 jobs (each job requires a course) → 10 auto-triggered quests
 *   that fire on completing those exact courses/jobs plus savings actions.
 *
 * XP philosophy: keep rewards LOW (courses 20–35, jobs 25–60, simple quests
 * 35–60) — only complex multi-trigger chains pay 130–260.
 * Idempotent: safe to re-run (matches on slug / title).
 */
class Level123ContentSeeder extends Seeder
{
    public function run(): void
    {
        $courseIds = $this->seedCourses();
        $jobIds    = $this->seedJobs($courseIds);
        $this->seedQuests($courseIds, $jobIds);

        $this->command?->info('Level 1-3 content seeded: ' . count($courseIds) . ' courses, ' . count($jobIds) . ' jobs, 10 quests.');
    }

    // ── Courses ───────────────────────────────────────────────────────────────

    private function seedCourses(): array
    {
        $courses = [
            // ── LEVEL 1 — free foundations ──
            [
                'slug' => 'money-basics', 'title' => 'Money Basics: Needs vs Wants', 'career_track' => 'finance',
                'icon' => '🪙', 'color' => '#15C77E', 'is_free' => true, 'cost_kes' => 0, 'duration_hours' => 1,
                'difficulty' => 'beginner', 'xp_reward' => 25,
                'description' => 'The first money lesson everyone needs: telling the difference between what you need and what you want.',
                'intro_content' => "Every shilling you ever earn will face one question: is this for a NEED or a WANT?\n\nThis short course gives you the single habit that separates people who always have money from people who are always broke — and it takes less than a game day to learn.",
                'content' => "NEEDS are things your life stops without: food, rent, transport to work, school costs. WANTS are things that feel urgent but aren't: the newest phone, snacks every hour, another pair of shoes.\n\nThe trick is that sellers work hard to make WANTS feel like NEEDS. \"Everyone has this phone\" is marketing, not survival.\n\nThe habit: before you spend, pause and ask — \"If I don't buy this today, what actually breaks?\" If the answer is \"nothing\", it's a want. Wants are fine! But wants come AFTER needs are covered and something is saved.\n\nA simple rule used across Kenya: 50% of income on needs, 30% on wants, 20% saved. Even on pocket money, the ratios build the habit.",
                'outcome' => 'You can sort any expense into need vs want in 5 seconds',
                'financial_tip' => 'Money doesn\'t disappear — it follows your choices. Choose before it chooses for you.',
                'jobs_intro' => 'Employers love people who understand value. This unlocks your first hustle:',
            ],
            [
                'slug' => 'mpesa-agent-pro', 'title' => 'M-Pesa Agent Skills', 'career_track' => 'finance',
                'icon' => '📱', 'color' => '#15C77E', 'is_free' => true, 'cost_kes' => 0, 'duration_hours' => 2,
                'difficulty' => 'beginner', 'xp_reward' => 30,
                'description' => 'How mobile money really works — float, commissions, fraud checks — from the agent\'s side of the counter.',
                'intro_content' => "M-Pesa moves more money in Kenya than most banks. Behind every green shop is an agent managing FLOAT — and float management is real financial skill.\n\nLearn how the agent business works and you'll never look at a withdrawal the same way.",
                'content' => "FLOAT is the agent's working capital — e-money and cash they swap all day. Customer deposits cash? The agent's e-float goes down, cash drawer goes up. Withdrawals do the reverse. Run out of either and the shop is dead until rebalancing.\n\nAgents earn COMMISSION per transaction — small amounts that stack up with volume. That's a core business lesson: many small profits beat one big score.\n\nFraud checks: always confirm the SMS, never reverse without the till record, and know the common cons (fake confirmation messages, \"wrong number\" refund tricks).\n\nThe bigger lesson: mobile money works on TRUST and RECORDS. Every shilling is logged. Your own money deserves the same treatment — track it like an agent tracks float.",
                'outcome' => 'You understand float, commissions and mobile-money safety',
                'financial_tip' => 'Small commissions, high volume — that\'s how kiosks quietly out-earn salaries.',
                'jobs_intro' => 'Agents need trustworthy float assistants. Your counter awaits:',
            ],
            [
                'slug' => 'customer-care-101', 'title' => 'Customer Care 101', 'career_track' => 'business',
                'icon' => '🤝', 'color' => '#A78BFA', 'is_free' => true, 'cost_kes' => 0, 'duration_hours' => 1,
                'difficulty' => 'beginner', 'xp_reward' => 20,
                'description' => 'The service skills that make customers come back — the cheapest marketing any business has.',
                'intro_content' => "A duka with rude service loses customers to the duka 20 metres away with the same prices. Service IS the product.\n\nThis course teaches the habits that turn one-time buyers into regulars.",
                'content' => "Rule 1 — Greet everyone. A \"karibu customer\" costs nothing and is remembered.\n\nRule 2 — Listen before you sell. Customers tell you exactly what they want to buy if you let them finish.\n\nRule 3 — Handle complaints as gold. A complaining customer is giving you free business advice; most unhappy customers just leave silently and never return.\n\nRule 4 — Know your stock and prices cold. \"Ngoja niulize\" (let me ask) twice in a row loses the sale.\n\nThe money lesson: getting a NEW customer costs 5× more than keeping an existing one. Businesses that master service spend less on marketing and keep steadier income — the same way YOU keep money by maintaining what you already have instead of always buying new.",
                'outcome' => 'You can serve, upsell and keep customers coming back',
                'financial_tip' => 'Repeat customers are compound interest for a business.',
                'jobs_intro' => 'Shops hire people who can hold a counter. Start here:',
            ],
            [
                'slug' => 'boda-road-safety', 'title' => 'Boda Road Safety & Delivery Basics', 'career_track' => 'business',
                'icon' => '🛵', 'color' => '#A78BFA', 'is_free' => true, 'cost_kes' => 0, 'duration_hours' => 2,
                'difficulty' => 'beginner', 'xp_reward' => 30,
                'description' => 'Ride safe, deliver on time, and understand the real costs of the boda business.',
                'intro_content' => "Boda work is Kenya's biggest youth employer — but the riders who last treat it as a BUSINESS, not a gig.\n\nLearn the safety rules that keep you earning and the maths that keeps the earnings yours.",
                'content' => "Safety first, always: helmet + reflector isn't just the law — one accident wipes out months of profit in repairs and hospital bills. The cheapest insurance is caution.\n\nDelivery basics: confirm the package, confirm the destination, communicate delays early. Reliability is what apps and customers rate — and ratings ARE your income.\n\nNow the business maths riders skip: your day's cash is NOT your profit. Subtract fuel, subtract a daily share of service costs (oil, brakes, tyres wear every single day even if you pay monthly), subtract loan payment if financed. What remains is real income — pay yourself from THAT.\n\nRiders who save one day's profit each week own their second bike within two years. Riders who spend the gross never own anything.",
                'outcome' => 'You can ride safely and compute real daily profit',
                'financial_tip' => 'Gross is vanity, net is sanity — subtract costs before you celebrate.',
                'jobs_intro' => 'Courier companies want certified riders. Get moving:',
            ],
            [
                'slug' => 'digital-skills-intro', 'title' => 'Digital Skills: Computer & Internet Basics', 'career_track' => 'tech',
                'icon' => '💻', 'color' => '#4DA8F7', 'is_free' => true, 'cost_kes' => 0, 'duration_hours' => 3,
                'difficulty' => 'beginner', 'xp_reward' => 30,
                'description' => 'Files, browsers, email, online safety — the baseline skills every modern job assumes.',
                'intro_content' => "Every decent job now assumes you can drive a computer. Three hours here saves you embarrassment in every interview after.\n\nThis is also the gateway course — tech jobs in Pesa City require it.",
                'content' => "The essentials: creating and organising FILES and folders (an employer's first test is often \"save this as PDF\"), using a BROWSER well (tabs, search terms that work, downloading safely), and professional EMAIL (clear subject line, greeting, short body, your name — no \"hi pls check\" one-liners).\n\nOnline safety is financial safety: never enter your M-Pesa PIN on a website, a \"you have won\" message is a con 100% of the time, and public Wi-Fi is not the place for banking.\n\nPasswords: long beats clever. Three random words beat your birth year.\n\nWhy this matters for money: digital skills are the cheapest income multiplier in Kenya. The same person earns more at the same duka if they can also run the till software, type the stock list, and answer the business email.",
                'outcome' => 'You can handle files, email and the internet like a professional',
                'financial_tip' => 'Digital skill is a salary multiplier that costs nothing but practice.',
                'jobs_intro' => 'Cyber cafés and offices need digital hands. Log in:',
            ],

            // ── LEVEL 2 — specialise (some paid) ──
            [
                'slug' => 'social-media-hustle', 'title' => 'Social Media for Business', 'career_track' => 'creative',
                'icon' => '📣', 'color' => '#FF6B35', 'is_free' => false, 'cost_kes' => 1500, 'duration_hours' => 3,
                'difficulty' => 'intermediate', 'xp_reward' => 30,
                'description' => 'Turn scrolling into a skill: content, consistency and turning followers into customers.',
                'intro_content' => "You already spend hours on social media. This course flips you from consumer to earner — the person businesses PAY to post.\n\nPaying Ksh 1,500 for a skill that pays Ksh 18,000/month is the kind of maths this city rewards.",
                'content' => "Businesses don't want \"viral\" — they want CONSISTENT: three good posts a week beats one lucky hit a month. Learn the content triangle: show the product, show the people, show the proof (happy customers).\n\nCaptions sell when they answer one question: \"what's in it for me?\" Price, location, how to order — every post, no exceptions. A beautiful photo without a call to action is decoration, not marketing.\n\nMeasure what matters: likes are applause, MESSAGES are money. Track how many DMs each post type earns and do more of what converts.\n\nThe career lesson: you just paid for this course. Skills you pay for are investments with payback periods — this one pays back in your first week of employment. That's how education maths should always be done.",
                'outcome' => 'You can run a small business\'s social pages and convert followers to sales',
                'financial_tip' => 'Paid skills have payback periods — calculate them like an investor.',
                'jobs_intro' => 'Brands want posters who convert. Your feed becomes a payslip:',
            ],
            [
                'slug' => 'bookkeeping-basics', 'title' => 'Bookkeeping Basics', 'career_track' => 'finance',
                'icon' => '📒', 'color' => '#15C77E', 'is_free' => false, 'cost_kes' => 2000, 'duration_hours' => 4,
                'difficulty' => 'intermediate', 'xp_reward' => 35,
                'description' => 'Record money in, money out, and know if a business is actually making profit.',
                'intro_content' => "Most Kenyan small businesses die not from lack of customers but from unrecorded money. The cure is bookkeeping — and people who can do it get paid in every market in the country.",
                'content' => "The core is one honest notebook (or spreadsheet) with four columns: DATE, WHAT, IN, OUT. Every shilling gets a line — including what the owner takes for lunch. That last part kills most businesses: unrecorded owner withdrawals eat profits invisibly.\n\nWeekly ritual: total IN, total OUT, and the difference is your week's truth. Compare four weeks and you see the trend no one can argue with.\n\nSeparate the money: business cash is NOT personal cash. The day a shopkeeper stops mixing pockets is the day the shop starts growing.\n\nStock is money sitting on a shelf — count it monthly, because missing stock is missing cash.\n\nThis exact discipline works on personal money too. Your M-Pesa statement is a ledger someone else keeps for you; a bookkeeper is just someone who reads it and acts.",
                'outcome' => 'You can keep a simple ledger and read whether a business is profitable',
                'financial_tip' => 'A business that isn\'t recorded is a business that\'s leaking.',
                'jobs_intro' => 'Every duka, salon and hardware needs a records person:',
            ],
            [
                'slug' => 'sales-101', 'title' => 'Sales 101: The Art of the Close', 'career_track' => 'business',
                'icon' => '🗣️', 'color' => '#A78BFA', 'is_free' => true, 'cost_kes' => 0, 'duration_hours' => 2,
                'difficulty' => 'intermediate', 'xp_reward' => 25,
                'description' => 'Selling is a learnable skill: openings, objections and closing without being pushy.',
                'intro_content' => "Every income in the world starts with a sale — of a product, a service, or yourself in an interview. Learn to sell and you're never fully broke again.",
                'content' => "Open with the customer, not the product: \"what are you looking for today?\" beats \"buy this\". People buy solutions to THEIR problem, not features of YOUR product.\n\nObjections are requests for information. \"It's too expensive\" usually means \"show me why it's worth it\" — answer with value (how long it lasts, what it saves), not discounts. Discounting first is paying customers to doubt you.\n\nThe close is a small question: \"cash or M-Pesa?\" — asked when interest peaks, not after it cools.\n\nCommission maths: a rep on 5% commission selling Ksh 8,000/day earns Ksh 400/day — Ksh 12,000/month on top of base. Volume × small percentage = real income, the same formula as the M-Pesa agent. Notice the pattern yet? Nearly all wealth is a small edge, repeated many times.",
                'outcome' => 'You can open, handle objections and close a sale',
                'financial_tip' => 'Commission is income you control with effort — rare and valuable.',
                'jobs_intro' => 'Distributors hire closers on commission. Start talking:',
            ],

            // ── LEVEL 3 — advanced (paid, bigger payback) ──
            [
                'slug' => 'web-design-basics', 'title' => 'Web Design Basics', 'career_track' => 'tech',
                'icon' => '🌐', 'color' => '#4DA8F7', 'is_free' => false, 'cost_kes' => 2500, 'duration_hours' => 6,
                'difficulty' => 'advanced', 'xp_reward' => 35,
                'description' => 'HTML, CSS and page structure — build simple sites businesses will pay for.',
                'intro_content' => "Every business in town wants a web page and almost nobody local can build one. That gap is your salary.\n\nRequires Digital Skills first — walk before you fly.",
                'content' => "HTML is the skeleton: headings, paragraphs, images, links. Learn ten tags and you can structure any page. CSS is the clothing: colours, spacing, layout. Together they're 80% of what a small business site needs.\n\nStructure sells: a business page needs exactly five things — what we do, photos, prices, location, contact button. Fancy animations impress developers; WhatsApp buttons make sales.\n\nMobile first is not optional in Kenya: over 90% of your visitors are on phones. Test every page on a small screen before showing a client.\n\nBusiness model: a simple site takes a beginner a weekend and local businesses pay Ksh 10,000–30,000. Do the payback maths on this course fee — then notice that SKILL is the asset class with the best returns in this entire city. It never depreciates, can't be stolen, and pays dividends every month.",
                'outcome' => 'You can build and structure a simple business website',
                'financial_tip' => 'Skills are assets: no maintenance costs, no depreciation, monthly dividends.',
                'jobs_intro' => 'Agencies need junior builders. Your first dev seat:',
            ],
            [
                'slug' => 'photography-basics', 'title' => 'Photography & Content Creation', 'career_track' => 'creative',
                'icon' => '📷', 'color' => '#FF6B35', 'is_free' => false, 'cost_kes' => 1800, 'duration_hours' => 4,
                'difficulty' => 'advanced', 'xp_reward' => 30,
                'description' => 'Shoot clean photos with any camera — events, products and content that clients pay for.',
                'intro_content' => "Weddings, graduations, product shoots for Instagram sellers — Kenya pays for photos every single weekend. The camera matters less than the eye, and the eye is trainable.",
                'content' => "Light is 80% of the photo: shoot with the light behind YOU, use golden hour (early morning, late afternoon), and never fight the midday sun — find shade.\n\nComposition in one rule: don't centre everything. Put the subject a third from the edge and photos instantly look professional.\n\nProduct photography for sellers: plain background, three angles, one photo showing size (product in a hand). That set sells items online — sellers know it and pay per product.\n\nEvents are a reliability business: arrive early, shoot the arrivals, deliver ON THE DAY you promised. A photographer who delivers late once is a photographer who worked once.\n\nPricing lesson: charge for the JOB, not the hours — clients buy outcomes. And always take a deposit; it filters serious clients and funds your transport. Deposits are working capital, the same concept the M-Pesa agent calls float.",
                'outcome' => 'You can shoot and deliver paid photo work with any decent phone',
                'financial_tip' => 'Charge for outcomes, collect deposits — cash flow is oxygen.',
                'jobs_intro' => 'Studios need second shooters every weekend:',
            ],
        ];

        $ids = [];
        foreach ($courses as $c) {
            $c += ['is_active' => true, 'age_group' => 'all'];
            $row = CityCourse::firstOrCreate(['slug' => $c['slug']], $c);
            $ids[$c['slug']] = $row->id;
        }
        return $ids;
    }

    // ── Jobs ─────────────────────────────────────────────────────────────────

    private function seedJobs(array $courseIds): array
    {
        // Salaries are tuned per age group so a player who pays all their
        // group's bills still keeps roughly KES 15,000/game-month:
        //   8-12  bills ≈ 1,900  → salaries ~17k
        //   13-17 bills ≈ 3,800  → salaries ~19k
        //   18-25 bills ≈ 11,450 → salaries ~26.5k
        //   26+   bills ≈ 28,000 → salaries ~43k
        // (freelance gigs are one-off payments, not monthly, so they sit lower)
        $jobs = [
            // LEVEL 1 — kid hustles (8-12)
            ['key' => 'kiosk',   'title' => 'Family Kiosk Helper',       'employer_name' => 'Mama Achieng\'s Kiosk',  'employer_logo' => '🍬', 'career_track' => 'business', 'level' => 1, 'salary_kes_month' => 16500, 'xp_reward' => 20, 'is_part_time' => true,  'employment_type' => 'part_time', 'course' => 'money-basics', 'ages' => ['8-12'],
             'description' => 'Help at the family kiosk after school — arrange stock, count change twice, and learn why every shilling in the cashbox must be explained.'],
            ['key' => 'carwash', 'title' => 'Weekend Car Wash Junior',   'employer_name' => 'Splash Bros Car Wash',   'employer_logo' => '🚗', 'career_track' => 'business', 'level' => 1, 'salary_kes_month' => 17000, 'xp_reward' => 20, 'is_part_time' => true,  'employment_type' => 'part_time', 'course' => 'money-basics', 'ages' => ['8-12'],
             'description' => 'Buckets, sponges and shiny rims every Saturday. Hard work you can see — and the first payslip you\'ll ever budget.'],
            ['key' => 'chicks',  'title' => 'Chicken Coop Assistant',    'employer_name' => 'Wafula Poultry Farm',    'employer_logo' => '🐔', 'career_track' => 'finance',  'level' => 1, 'salary_kes_month' => 17500, 'xp_reward' => 25, 'is_part_time' => true,  'employment_type' => 'part_time', 'course' => 'money-basics', 'ages' => ['8-12'],
             'description' => 'Feed the flock, collect and count eggs, record the numbers. Farming teaches the oldest money lesson: you harvest what you consistently feed.'],

            // LEVEL 1 — teen entry hustles (13-17)
            ['key' => 'posho',   'title' => 'Posho Mill Attendant',      'employer_name' => 'Wamalwa Millers',        'employer_logo' => '🌽', 'career_track' => 'finance',  'level' => 1, 'salary_kes_month' => 19000, 'xp_reward' => 25, 'is_part_time' => true,  'employment_type' => 'part_time', 'course' => 'money-basics', 'ages' => ['13-17'],
             'description' => 'Weigh, mill and charge correctly. Handle cash all day and reconcile the drawer at closing — needs vs wants starts with counting right.'],
            ['key' => 'duka',    'title' => 'Duka Shop Attendant',       'employer_name' => 'Duka la Mama Njeri',     'employer_logo' => '🏪', 'career_track' => 'business', 'level' => 1, 'salary_kes_month' => 18500, 'xp_reward' => 25, 'is_part_time' => false, 'employment_type' => 'full_time', 'course' => 'customer-care-101', 'ages' => ['13-17'],
             'description' => 'Serve customers, manage the counter, and keep regulars coming back. Mama Njeri promotes people her customers ask for by name.'],
            ['key' => 'cyber',   'title' => 'Cyber Café Assistant',      'employer_name' => 'ClickPoint Cyber',       'employer_logo' => '🖱️', 'career_track' => 'tech',     'level' => 1, 'salary_kes_month' => 19000, 'xp_reward' => 25, 'is_part_time' => true,  'employment_type' => 'part_time', 'course' => 'digital-skills-intro', 'ages' => ['13-17'],
             'description' => 'Help customers print, scan, apply for HELB and beat KRA deadlines. Patience plus digital skills equals tips.'],

            // LEVEL 1 — young adult (18-25)
            ['key' => 'float',   'title' => 'M-Pesa Float Assistant',    'employer_name' => 'Mtaani Cash Point',      'employer_logo' => '💚', 'career_track' => 'finance',  'level' => 1, 'salary_kes_month' => 26500, 'xp_reward' => 30, 'is_part_time' => false, 'employment_type' => 'full_time', 'course' => 'mpesa-agent-pro', 'ages' => ['18-25'],
             'description' => 'Keep the float balanced, verify transactions, and spot the cons before they cost the till. Trust is the job description.'],
            ['key' => 'boda',    'title' => 'Boda Courier Rider',        'employer_name' => 'Haraka Deliveries',      'employer_logo' => '🛵', 'career_track' => 'business', 'level' => 1, 'salary_kes_month' => 5000,  'xp_reward' => 30, 'is_part_time' => false, 'employment_type' => 'freelance', 'course' => 'boda-road-safety', 'ages' => ['18-25', '26+'],
             'description' => 'A week of same-day parcel runs across Pesa City, paid on delivery of the batch. Helmets on, promises kept — the client calls again in a few weeks.'],

            // LEVEL 2 — skilled roles (18-25)
            ['key' => 'social',  'title' => 'Social Media Assistant',    'employer_name' => 'Nairobi Bites Kitchen',  'employer_logo' => '🍲', 'career_track' => 'creative', 'level' => 2, 'salary_kes_month' => 26500, 'xp_reward' => 40, 'is_part_time' => true,  'employment_type' => 'part_time', 'course' => 'social-media-hustle', 'ages' => ['18-25'],
             'description' => 'Run the restaurant\'s pages: three posts a week, answer DMs, count the orders each post brings. Likes are nice, orders are the job.'],
            ['key' => 'books',   'title' => 'Bookkeeping Clerk',         'employer_name' => 'Jenga Hardware',         'employer_logo' => '🧱', 'career_track' => 'finance',  'level' => 2, 'salary_kes_month' => 27000, 'xp_reward' => 45, 'is_part_time' => false, 'employment_type' => 'full_time', 'course' => 'bookkeeping-basics', 'ages' => ['18-25'],
             'description' => 'Record every sale and purchase, reconcile cash daily, flag missing stock. The owner sleeps because your ledger is honest.'],
            ['key' => 'sales',   'title' => 'Airtime & Data Sales Rep',  'employer_name' => 'SimuConnect Distributors','employer_logo' => '📶', 'career_track' => 'business', 'level' => 2, 'salary_kes_month' => 26000, 'xp_reward' => 35, 'is_part_time' => true,  'employment_type' => 'part_time', 'course' => 'sales-101', 'ages' => ['18-25'],
             'description' => 'Base plus commission selling airtime and data bundles to shops on a route. Every close grows the payslip.'],

            // LEVEL 3 — professional entries (26+)
            ['key' => 'web',     'title' => 'Junior Web Assistant',      'employer_name' => 'Savanna Digital Agency', 'employer_logo' => '🦁', 'career_track' => 'tech',     'level' => 3, 'salary_kes_month' => 43000, 'xp_reward' => 60, 'is_part_time' => false, 'employment_type' => 'full_time', 'course' => 'web-design-basics', 'also_requires' => 'digital-skills-intro', 'ages' => ['26+'],
             'description' => 'Build and update client pages under a senior dev. Two courses got you here — the portfolio you build now sets the next salary.'],
            ['key' => 'photo',   'title' => 'Event Photo Assistant',     'employer_name' => 'Lulu Studios',           'employer_logo' => '📸', 'career_track' => 'creative', 'level' => 3, 'salary_kes_month' => 12000, 'xp_reward' => 50, 'is_part_time' => false, 'employment_type' => 'freelance', 'course' => 'photography-basics', 'ages' => ['18-25', '26+'],
             'description' => 'Second shooter for one wedding weekend — deliver the shots, get paid per gig. Reliable freelancers get called back.'],
        ];

        $ids = [];
        foreach ($jobs as $j) {
            $key  = $j['key'];
            $also = $j['also_requires'] ?? null;
            $ages = $j['ages'] ?? [];
            unset($j['key'], $j['course'], $j['also_requires'], $j['ages']);

            // Guard for pre-migration runs: drop the column if it doesn't exist yet
            if (!\Illuminate\Support\Facades\Schema::hasColumn('city_jobs', 'employment_type')) {
                unset($j['employment_type']);
            }

            // Age-group targeting: JSON list + legacy single column kept in sync
            $j['age_group'] = !empty($ages) ? $ages[0] : 'all';
            if (\Illuminate\Support\Facades\Schema::hasColumn('city_jobs', 'age_groups')) {
                $j['age_groups'] = !empty($ages) ? $ages : null;
            }

            $row = CityJob::updateOrCreate(
                ['title' => $j['title'], 'employer_name' => $j['employer_name']],
                $j + [
                    'is_active'           => true,
                    'required_course_id'  => $courseIds[$this->jobCourse($key)] ?? null,
                    'required_course_ids' => $also ? [ $courseIds[$this->jobCourse($key)], $courseIds[$also] ] : null,
                ]
            );
            $ids[$key] = $row->id;
        }
        return $ids;
    }

    /** Maps job key → course slug it requires. */
    private function jobCourse(string $key): string
    {
        return [
            'kiosk' => 'money-basics',       'carwash' => 'money-basics',
            'chicks'=> 'money-basics',
            'posho' => 'money-basics',       'float' => 'mpesa-agent-pro',
            'duka'  => 'customer-care-101',  'boda'  => 'boda-road-safety',
            'cyber' => 'digital-skills-intro','social'=> 'social-media-hustle',
            'books' => 'bookkeeping-basics', 'sales' => 'sales-101',
            'web'   => 'web-design-basics',  'photo' => 'photography-basics',
        ][$key];
    }

    // ── Quests ───────────────────────────────────────────────────────────────

    private function seedQuests(array $courseIds, array $jobIds): void
    {
        $quests = [
            // ══ LEVEL 1 — simple single triggers, low XP ══
            [
                'title' => 'Back to Class', 'icon' => '🪙', 'level' => 1, 'xp' => 40, 'kes' => 0,
                'description' => 'Take your first step: complete the Money Basics course at the Opportunity Hub.',
                'lesson' => 'Needs come first, wants come second, savings always get a share. That order is the whole game.',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['money-basics'], 'label' => 'Complete Money Basics: Needs vs Wants'],
                ],
            ],
            [
                'title' => 'Street Smart Rider', 'icon' => '🛵', 'level' => 1, 'xp' => 40, 'kes' => 0,
                'description' => 'Get road-ready — complete the Boda Road Safety course.',
                'lesson' => 'One accident costs more than a year of helmets. Protecting your ability to earn IS a financial decision.',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['boda-road-safety'], 'label' => 'Complete Boda Road Safety & Delivery Basics'],
                ],
            ],
            [
                'title' => 'Digital Doorway', 'icon' => '💻', 'level' => 1, 'xp' => 45, 'kes' => 0,
                'description' => 'Every modern job assumes computer skills. Complete the Digital Skills intro course.',
                'lesson' => 'Digital skill multiplies every other skill you have — it\'s the cheapest raise you\'ll ever get.',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['digital-skills-intro'], 'label' => 'Complete Digital Skills: Computer & Internet Basics'],
                ],
            ],
            [
                'title' => 'Behind the Counter', 'icon' => '🏪', 'level' => 1, 'xp' => 50, 'kes' => 0,
                'description' => 'Get hired at a real Pesa City shop — the Duka, the posho mill, or the cyber café.',
                'lesson' => 'First jobs aren\'t about the salary — they\'re about proving you show up. Reputation compounds faster than interest.',
                'triggers' => [
                    ['type' => 'get_job', 'values' => [(string) $jobIds['duka'], (string) $jobIds['posho'], (string) $jobIds['cyber']], 'label' => 'Get hired at the Duka, Posho Mill or Cyber Café'],
                ],
            ],

            // ══ LEVEL 2 — targeted + first multi-trigger chains ══
            [
                'title' => 'Brand Builder', 'icon' => '📣', 'level' => 2, 'xp' => 55, 'kes' => 0,
                'description' => 'Invest in a paid skill: complete the Social Media for Business course.',
                'lesson' => 'You paid Ksh 1,500 for a skill that pays Ksh 18,000 a month. Always calculate the payback period before AND after.',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['social-media-hustle'], 'label' => 'Complete Social Media for Business'],
                ],
            ],
            [
                'title' => 'Saver\'s Discipline', 'icon' => '🏦', 'level' => 2, 'xp' => 60, 'kes' => 0,
                'description' => 'Grow any savings scheme to Ksh 3,000. Interest is already working on every shilling.',
                'lesson' => 'The habit matters more than the amount — Ksh 3,000 saved on a small income is harder (and worth more) than Ksh 30,000 on a big one.',
                'triggers' => [
                    ['type' => 'reach_savings', 'values' => ['3000'], 'label' => 'Reach Ksh 3,000 in a savings scheme'],
                ],
            ],
            [
                'title' => 'The Hustler\'s Toolkit', 'icon' => '🗣️', 'level' => 2, 'xp' => 130, 'kes' => 0,
                'description' => 'Learn to sell, then get paid to sell: complete Sales 101 AND get hired as a Sales Rep.',
                'lesson' => 'Skill → job → commission. Income you can grow with effort beats income that\'s capped — hunt for roles with upside.',
                'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['sales-101'], 'label' => 'Complete Sales 101: The Art of the Close'],
                    ['type' => 'get_job', 'values' => [(string) $jobIds['sales']], 'label' => 'Get hired as an Airtime & Data Sales Rep'],
                ],
            ],
            [
                'title' => 'Money In, Money Tracked', 'icon' => '📒', 'level' => 2, 'xp' => 140, 'kes' => 0,
                'description' => 'Learn the ledger AND practice it: complete Bookkeeping Basics and grow your savings to Ksh 1,000.',
                'lesson' => 'Recording money changes how you spend it. Every serious fortune has a boring ledger behind it.',
                'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['bookkeeping-basics'], 'label' => 'Complete Bookkeeping Basics'],
                    ['type' => 'reach_savings', 'values' => ['1000'], 'label' => 'Reach Ksh 1,000 in savings'],
                ],
            ],

            // ══ LEVEL 3 — complex multi-trigger chains (bigger XP allowed) ══
            [
                'title' => 'The Ledger Master', 'icon' => '🧾', 'level' => 3, 'xp' => 220, 'kes' => 300,
                'description' => 'The full finance path: learn bookkeeping, get hired as a Bookkeeping Clerk, and hold Ksh 5,000 in savings.',
                'lesson' => 'Skills got you the job, the job funds the savings, the savings earn interest. That loop — learn, earn, save — is the engine of every fortune.',
                'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['bookkeeping-basics'], 'label' => 'Complete Bookkeeping Basics'],
                    ['type' => 'get_job', 'values' => [(string) $jobIds['books']], 'label' => 'Get hired as a Bookkeeping Clerk'],
                    ['type' => 'reach_savings', 'values' => ['5000'], 'label' => 'Reach Ksh 5,000 in a savings scheme'],
                ],
            ],
            [
                'title' => 'Tech Career Launchpad', 'icon' => '🚀', 'level' => 3, 'xp' => 260, 'kes' => 500,
                'description' => 'The two-course tech ladder: complete Digital Skills, then Web Design, then land the Junior Web Assistant seat.',
                'lesson' => 'Stacked skills multiply: each course alone was worth little, together they doubled your salary. Plan skills in ladders, not one-offs.',
                'trigger_mode' => 'all',
                'triggers' => [
                    ['type' => 'take_course', 'values' => ['digital-skills-intro'], 'label' => 'Complete Digital Skills: Computer & Internet Basics'],
                    ['type' => 'take_course', 'values' => ['web-design-basics'], 'label' => 'Complete Web Design Basics'],
                    ['type' => 'get_job', 'values' => [(string) $jobIds['web']], 'label' => 'Get hired as a Junior Web Assistant'],
                ],
            ],
        ];

        foreach ($quests as $q) {
            Quest::firstOrCreate(
                ['title' => $q['title']],
                [
                    'description'    => $q['description'],
                    'lesson'         => $q['lesson'],
                    'icon'           => $q['icon'],
                    'xp_reward'      => $q['xp'],
                    'kes_reward'     => $q['kes'],
                    'age_group'      => 'all',
                    'is_active'      => true,
                    'sort_order'     => $q['level'],
                    'level_required' => $q['level'],
                    'triggers'       => $q['triggers'],
                    'trigger_mode'   => $q['trigger_mode'] ?? 'all',
                ]
            );
        }
    }
}
