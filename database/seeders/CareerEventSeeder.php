<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Node;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * CareerEventSeeder — seeds targeted world-event scenario nodes for each career track.
 *
 * Each career track gets 3 scenarios: one opportunity, one career/growth, one cost.
 * EventEngine's filterByCareer() will surface these only to players with the matching
 * career_field set on their UserProgress (from the career quiz).
 *
 * Tracks: technology, business, healthcare, agriculture, finance, education, engineering, creative
 *
 * cPanel deploy: php artisan db:seed --class=CareerEventSeeder
 */
class CareerEventSeeder extends Seeder
{
    private const SCENARIOS = [

        // ══════════════════════════════════
        //  TECHNOLOGY  (developer / tech)
        // ══════════════════════════════════
        [
            'title'         => 'Freelance App Build Offer',
            'scenario_text' => 'A WhatsApp contact forwards you a message — a matatu Sacco wants a simple mobile app to track daily collections. They are offering KES 15,000 and need it in 3 weekends. Your day job is full-on. Do you take it?',
            'icon'          => '💻',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'technology', 'min_chapter' => 2, 'expires_in_days' => 3],
            'choices'       => [
                ['label' => 'Accept the gig', 'description' => 'Grind three weekends, deliver the app, collect KES 15,000. Tight but doable.', 'balance_change' => 15000, 'points' => 150, 'icon' => '🚀', 'lesson' => 'Side income from your skill set is the fastest way to grow your savings rate. One gig a month can change your financial picture in a year.'],
                ['label' => 'Decline — protect your energy', 'description' => 'Rest and sharpen your main skills instead. Some money is too expensive.', 'balance_change' => 0, 'points' => 80, 'icon' => '😌', 'lesson' => 'Saying no to money is also a financial decision. Burnout costs more in the long run.'],
            ],
        ],
        [
            'title'         => 'Upwork Profile Upgrade',
            'scenario_text' => 'Your Upwork profile is getting views but no connects convert. A paid "Rising Talent" profile boost costs KES 2,800 for 3 months and promises more visibility. Freelance income could jump if it works.',
            'icon'          => '📈',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'technology', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Pay for the upgrade', 'description' => 'Invest in your profile visibility. Treat it like marketing spend.', 'balance_change' => -2800, 'points' => 200, 'icon' => '💼', 'lesson' => 'Spending money to make money is real — when the ROI is measurable. Track whether your connects improve within 30 days.'],
                ['label' => 'Optimize the free profile', 'description' => 'Rewrite your bio, add portfolio pieces, apply for jobs consistently.', 'balance_change' => 0, 'points' => 150, 'icon' => '✏️', 'lesson' => 'Before paying for visibility, make sure your profile does its job. Conversion is a content problem, not always a reach problem.'],
            ],
        ],
        [
            'title'         => 'Dev Tools License Expiry',
            'scenario_text' => 'Your JetBrains IDE annual license lapsed last night. You need it daily. The full renewal is KES 8,500. A student discount would bring it to KES 2,100 but you graduated two years ago.',
            'icon'          => '🔧',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'technology', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Pay full renewal', 'description' => 'Keep your workflow uninterrupted. Professional tools are a legit work expense.', 'balance_change' => -8500, 'points' => 50, 'icon' => '✅', 'lesson' => 'Professional tools are deductible business expenses. Keep your receipts — they matter come tax season.'],
                ['label' => 'Switch to VS Code (free)', 'description' => 'Open source, fast, extensible. Many senior devs swear by it.', 'balance_change' => 0, 'points' => 100, 'icon' => '🆓', 'lesson' => 'Zero-cost alternatives can match paid tools. Evaluating the real cost-benefit before paying is always the smart move.'],
            ],
        ],

        // ══════════════════════════════════
        //  BUSINESS  (entrepreneur / sales)
        // ══════════════════════════════════
        [
            'title'         => 'Referral Commission Waiting',
            'scenario_text' => 'You referred a contact to your insurance broker six months ago. They signed a policy — your referral commission of KES 12,000 is sitting in the broker\'s account. You can withdraw now or leave it to compound as store credit for future referrals.',
            'icon'          => '💰',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'business', 'min_chapter' => 2, 'expires_in_days' => 5],
            'choices'       => [
                ['label' => 'Withdraw and save it', 'description' => 'KES 12,000 hits your M-Pesa today. Move it to your savings account before you spend it.', 'balance_change' => 12000, 'points' => 100, 'icon' => '💵', 'lesson' => 'Referral income is passive income. When it arrives, the first move is always: save before you spend.'],
                ['label' => 'Leave as credit for more referrals', 'description' => 'Use it to unlock a bigger commission tier. Think long game.', 'balance_change' => 3000, 'points' => 250, 'icon' => '♻️', 'lesson' => 'Reinvesting income into an income stream is compounding. This is how passive income grows beyond your salary.'],
            ],
        ],
        [
            'title'         => 'Supplier Bulk Deal — 30 Days Only',
            'scenario_text' => 'Your stock supplier has excess inventory and is offering a 25% discount if you order in bulk this week. Minimum order is KES 25,000. You could resell it all — but it ties up your cash for 6–8 weeks.',
            'icon'          => '📦',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'business', 'min_chapter' => 2, 'expires_in_days' => 7],
            'choices'       => [
                ['label' => 'Go bulk — lock in the discount', 'description' => 'Tie up cash now, sell within 8 weeks, pocket the margin.', 'balance_change' => -25000, 'points' => 300, 'icon' => '📊', 'lesson' => 'Bulk buying only works if you have reliable demand. Never tie up cash you cannot afford to lock away for 60 days.'],
                ['label' => 'Normal order — keep cash flexible', 'description' => 'Miss the discount but keep your liquidity intact.', 'balance_change' => -8000, 'points' => 100, 'icon' => '🛡️', 'lesson' => 'Cash flow is king in small business. A good deal that kills your liquidity is still a bad deal.'],
            ],
        ],
        [
            'title'         => 'Single Business Permit Due',
            'scenario_text' => 'Your annual single business permit is expiring in 7 days. Renewal is KES 1,050 at the county office. Let it lapse and you risk a KES 5,000 fine if county inspectors visit.',
            'icon'          => '📋',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'business', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Renew now (KES 1,050)', 'description' => 'Pay the annual fee and operate legally. No surprises.', 'balance_change' => -1050, 'points' => 100, 'icon' => '✅', 'lesson' => 'Compliance costs are predictable. Fines are not. Always budget for licenses, insurance, and permits as fixed monthly costs.'],
                ['label' => 'Delay and hope for the best', 'description' => 'Save the cash now, deal with it if inspectors show up.', 'balance_change' => 0, 'points' => -50, 'icon' => '⚠️', 'lesson' => 'This is a false saving. Trading KES 1,050 in certainty for up to KES 5,000 in risk is bad probability math.'],
            ],
        ],

        // ══════════════════════════════════
        //  HEALTHCARE  (nurse / CHO / doctor)
        // ══════════════════════════════════
        [
            'title'         => 'Locum Shift This Weekend',
            'scenario_text' => 'A private clinic in Westlands lost two staff to a bug and is calling for locum cover — KES 4,500 per day, Sat–Sun. You are off duty. It is good money but you have been tired all week.',
            'icon'          => '🏥',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'healthcare', 'min_chapter' => 2, 'expires_in_days' => 2],
            'choices'       => [
                ['label' => 'Take both shifts (KES 9,000)', 'description' => 'Show up, do your best, bank the extra income.', 'balance_change' => 9000, 'points' => 100, 'icon' => '💪', 'lesson' => 'Extra income is most valuable when you save it before lifestyle inflation can absorb it. What will you do with this KES 9,000?'],
                ['label' => 'Take one shift only (KES 4,500)', 'description' => 'Balance income with recovery. Half is better than none.', 'balance_change' => 4500, 'points' => 150, 'icon' => '⚖️', 'lesson' => 'Sustainable income requires sustainable energy. Rest is an investment in your ability to keep earning.'],
                ['label' => 'Decline — rest is non-negotiable', 'description' => 'Your wellbeing is not for sale this weekend.', 'balance_change' => 0, 'points' => 80, 'icon' => '😴', 'lesson' => 'Preventing burnout in healthcare is critical. Your long-term earning ability depends on your health — and that includes rest.'],
            ],
        ],
        [
            'title'         => 'CPD Workshop — Paid Sponsorship',
            'scenario_text' => 'A pharmaceutical company is sponsoring a 2-day Continuous Professional Development (CPD) workshop. Attendance is free — they cover costs. But you will need to request two days\' leave from your facility.',
            'icon'          => '📚',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'healthcare', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Attend and invest in skills', 'description' => 'Log the CPD hours, build your professional network, and learn.', 'balance_change' => 0, 'points' => 400, 'icon' => '🎓', 'lesson' => 'CPD points are currency in healthcare careers. Skills that take you from Grade C to Grade B are worth more than any single weekend shift.'],
                ['label' => 'Skip this round', 'description' => 'Stay on shift and collect your regular income instead.', 'balance_change' => 0, 'points' => 20, 'icon' => '📅', 'lesson' => 'Every CPD opportunity skipped is a career advancement delayed. Your salary band depends partly on your credentials.'],
            ],
        ],
        [
            'title'         => 'Stethoscope Calibration Required',
            'scenario_text' => 'Your stethoscope failed the quarterly equipment check. The approved calibration center charges KES 4,500. Until it passes, you cannot use it in your ward — borrowing from colleagues creates friction.',
            'icon'          => '🩺',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'healthcare', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Service it this week', 'description' => 'Pay, fix it, move on. Professional tools need maintenance.', 'balance_change' => -4500, 'points' => 80, 'icon' => '🔧', 'lesson' => 'Equipment maintenance is a recurring cost of doing professional work. Budget for it monthly so it never surprises you.'],
                ['label' => 'Use the ward\'s shared set', 'description' => 'Borrow for now, service it next pay cycle.', 'balance_change' => 0, 'points' => 30, 'icon' => '🤝', 'lesson' => 'Deferring a necessary expense saves money now but costs goodwill and efficiency. Weigh both.'],
            ],
        ],

        // ══════════════════════════════════
        //  AGRICULTURE  (farmer / agri manager)
        // ══════════════════════════════════
        [
            'title'         => 'Pre-Harvest SACCO Loan Available',
            'scenario_text' => 'Your agri-SACCO is offering a pre-harvest bridging loan of up to KES 30,000 at 8% per annum — backed by your projected crop yield. The rains have been decent. Should you leverage it to expand your inputs?',
            'icon'          => '🌾',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'agriculture', 'min_chapter' => 2, 'expires_in_days' => 10],
            'choices'       => [
                ['label' => 'Take the loan — scale up inputs', 'description' => 'Buy better seed, top-dressing fertilizer, and hire casual labour. Bigger input = bigger yield.', 'balance_change' => 25000, 'points' => 150, 'icon' => '🌱', 'lesson' => 'A well-structured agri-loan tied to a known cash flow (harvest) is calculated risk, not recklessness. The 8% pa cost vs expected yield uplift is the maths you need to do first.'],
                ['label' => 'Self-finance this season', 'description' => 'Use your savings and stay debt-free. Slower growth but no repayment pressure.', 'balance_change' => 0, 'points' => 200, 'icon' => '💰', 'lesson' => 'Debt-free farming is not inefficient — it is capital-efficient when you lack collateral cushion. Know your risk tolerance before leveraging.'],
            ],
        ],
        [
            'title'         => 'Subsidized Fertilizer Through Cooperative',
            'scenario_text' => 'The county cooperative has fertilizer at the government-subsidized price — KES 1,500 per 50kg bag vs market rate of KES 3,800. Stock is limited. You need at least 5 bags for the current season.',
            'icon'          => '🌿',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'agriculture', 'min_chapter' => 1, 'expires_in_days' => 4],
            'choices'       => [
                ['label' => 'Buy 5 bags now (KES 7,500)', 'description' => 'Grab the subsidy before it runs out. You save KES 11,500 vs market rate.', 'balance_change' => -7500, 'points' => 200, 'icon' => '🛒', 'lesson' => 'A KES 11,500 saving on a KES 7,500 spend is a 153% return on spend. Cooperative membership often unlocks deals like this — another reason to join and participate.'],
                ['label' => 'Buy 2 bags now, the rest later', 'description' => 'Preserve cash but risk missing the subsidy on the remaining 3 bags.', 'balance_change' => -3000, 'points' => 80, 'icon' => '⚖️', 'lesson' => 'Partial action on a time-limited opportunity is still better than none. Always ask: can I afford the minimum viable purchase?'],
            ],
        ],
        [
            'title'         => 'Crop Insurance Premium Due',
            'scenario_text' => 'Dry season is here. The NHIF-linked crop insurance premium for your half-acre is KES 2,500 for the season. Without it, a drought or pest attack leaves you with nothing. Last dry season, your neighbour lost everything.',
            'icon'          => '⛈️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'agriculture', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Pay the premium (KES 2,500)', 'description' => 'Protect your season. Worst case: it rains and you lost KES 2,500. Best case: it saves your livelihood.', 'balance_change' => -2500, 'points' => 150, 'icon' => '🛡️', 'lesson' => 'Insurance is not a cost — it is the price of certainty. KES 2,500 to protect a KES 80,000 season is a no-brainer when you run the numbers.'],
                ['label' => 'Skip it and hope the rains come', 'description' => 'Save the premium. You have been farming 10 years — you know the signs.', 'balance_change' => 0, 'points' => 0, 'icon' => '🌤️', 'lesson' => 'Lived experience is valuable — but climate unpredictability is rising every year. Overconfidence in weather forecasting has bankrupted many experienced farmers.'],
            ],
        ],

        // ══════════════════════════════════
        //  FINANCE  (banker / microfinance officer)
        // ══════════════════════════════════
        [
            'title'         => 'Weekend Financial Literacy Gig',
            'scenario_text' => 'A fintech startup running youth financial literacy bootcamps wants you to facilitate two Saturday sessions. Pay: KES 4,000 per session. It aligns perfectly with your field — but it is your rest weekend.',
            'icon'          => '🏦',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'finance', 'min_chapter' => 2, 'expires_in_days' => 3],
            'choices'       => [
                ['label' => 'Accept both sessions (KES 8,000)', 'description' => 'Side income that builds your personal brand and your CV simultaneously.', 'balance_change' => 8000, 'points' => 150, 'icon' => '🎤', 'lesson' => 'Income that builds your professional reputation is double-value income. Track these engagements — they often open bigger doors.'],
                ['label' => 'Take one session (KES 4,000)', 'description' => 'Balance income with recovery. Commit to what you can deliver well.', 'balance_change' => 4000, 'points' => 100, 'icon' => '⚖️', 'lesson' => 'Half-time engagement beats full-time burnout every time. Know your sustainable output level.'],
            ],
        ],
        [
            'title'         => 'CPA Section Exam Registration',
            'scenario_text' => 'KASNEB registration for the next CPA sitting is open. Section 4 fees are KES 15,000. Pass it and your salary band could jump by KES 12,000/month. Miss this sitting and you wait another 6 months.',
            'icon'          => '📊',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'finance', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Register and commit to studying', 'description' => 'Pay the fees, block weekend study time for 3 months, sit the exam.', 'balance_change' => -15000, 'points' => 600, 'icon' => '🎓', 'lesson' => 'A KES 15,000 investment that unlocks a KES 144,000/year salary increase has a 9-month payback period. Run the numbers before skipping professional exams.'],
                ['label' => 'Skip this sitting — not ready', 'description' => 'Wait until you have studied enough. Failing wastes both money and time.', 'balance_change' => 0, 'points' => 50, 'icon' => '📅', 'lesson' => 'Only sit a professional exam when you are genuinely ready. But also be honest with yourself about whether you are delaying out of fear or preparation.'],
            ],
        ],
        [
            'title'         => 'Client Investment Portfolio Review',
            'scenario_text' => 'Your manager asks you to present a risk analysis on a client\'s portfolio at Friday\'s board meeting. It is not strictly your role — but doing it well could accelerate your promotion review.',
            'icon'          => '📈',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'finance', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Prepare a thorough analysis', 'description' => 'Put in the extra hours, produce quality work, be visible at the right moment.', 'balance_change' => 0, 'points' => 500, 'icon' => '🌟', 'lesson' => 'Promotions in finance are often earned in moments of discretionary effort — not in your job description, but in your ambition. This is one of those moments.'],
                ['label' => 'Pass it back — not your scope', 'description' => 'Boundaries are important. Scope creep without compensation is real.', 'balance_change' => 0, 'points' => 50, 'icon' => '🛑', 'lesson' => 'Knowing your worth and protecting your time is important. But strategic visibility at the right moment can matter more than strict scope management.'],
            ],
        ],

        // ══════════════════════════════════
        //  EDUCATION  (teacher / tutor)
        // ══════════════════════════════════
        [
            'title'         => 'Form 4 Tutoring Request',
            'scenario_text' => 'A parent in your community finds your number through a colleague. Their Form 4 student is struggling with Maths and Biology. They can pay KES 3,000/month per subject. You have Tuesday and Thursday evenings free.',
            'icon'          => '📐',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'education', 'min_chapter' => 1, 'expires_in_days' => 4],
            'choices'       => [
                ['label' => 'Take both subjects (KES 6,000/month)', 'description' => 'Two evenings per week, meaningful side income, and you are genuinely helping.', 'balance_change' => 6000, 'points' => 150, 'icon' => '✏️', 'lesson' => 'Skills-based side income is the most sustainable kind — you are already qualified, no extra training needed. KES 6,000/month is KES 72,000/year.'],
                ['label' => 'One subject only (KES 3,000/month)', 'description' => 'Keep balance — one evening per week is manageable alongside your teaching load.', 'balance_change' => 3000, 'points' => 100, 'icon' => '⚖️', 'lesson' => 'A sustainable side income beats a burnout pace every time. Start with what you can maintain, then scale.'],
                ['label' => 'Refer to a trusted colleague', 'description' => 'You are too full. Pass the referral — your colleague owes you one.', 'balance_change' => 0, 'points' => 60, 'icon' => '🤝', 'lesson' => 'Referrals build networks. The colleague you helped today may return the favour when a bigger opportunity comes.'],
            ],
        ],
        [
            'title'         => 'TSC Digital Skills Conference',
            'scenario_text' => 'The Teachers Service Commission is sponsoring 50 teachers for a digital skills conference in Nairobi — free accommodation and training. Your application is due Friday. Two days of approved leave required.',
            'icon'          => '🖥️',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'education', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Apply and attend if selected', 'description' => 'Free professional development is rare. Apply and let the outcome decide.', 'balance_change' => 0, 'points' => 350, 'icon' => '🎓', 'lesson' => 'Free professional development opportunities compound your value over time. Every new skill is leverage for a future salary negotiation.'],
                ['label' => 'Skip — teaching load is heavy right now', 'description' => 'The timing is bad. Your students need you in class.', 'balance_change' => 0, 'points' => 30, 'icon' => '📅', 'lesson' => 'Present commitments matter. But make sure skipping professional growth does not become a pattern — your career stalls when you stop learning.'],
            ],
        ],
        [
            'title'         => 'Term-Start Classroom Supplies',
            'scenario_text' => 'New term, new class. Your school lacks budget for basic supplies: markers, chart paper, and math sets for 38 students. You can buy from pocket, ask parents to contribute, or apply to a donor NGO.',
            'icon'          => '📏',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'education', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Buy from pocket (KES 4,000)', 'description' => 'Get what you need immediately. Your students will not suffer the lack.', 'balance_change' => -4000, 'points' => 100, 'icon' => '❤️', 'lesson' => 'Spending on students is noble — but unsustainable if it becomes a pattern. Track what you spend out of pocket and advocate for institutional budget.'],
                ['label' => 'Propose a parents\' contribution', 'description' => 'Circulate a contribution memo. Modest amounts from each parent add up.', 'balance_change' => 0, 'points' => 150, 'icon' => '🤝', 'lesson' => 'Building community ownership of school resources is a skill. Systemic problems need community solutions, not individual sacrifice.'],
            ],
        ],

        // ══════════════════════════════════
        //  ENGINEERING  (civil / mechanical / electrical)
        // ══════════════════════════════════
        [
            'title'         => 'Weekend Site Assessment Gig',
            'scenario_text' => 'A housing developer needs a quick structural integrity assessment of a 3-bedroom house before purchase. It is a Saturday job — 4 hours, KES 18,000 flat fee. Your supervisor has no issue with you taking it.',
            'icon'          => '🏗️',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'engineering', 'min_chapter' => 3, 'expires_in_days' => 2],
            'choices'       => [
                ['label' => 'Accept the assessment (KES 18,000)', 'description' => 'Four hours on a Saturday, professional report delivered, payment cleared.', 'balance_change' => 18000, 'points' => 150, 'icon' => '💼', 'lesson' => 'Consulting income at KES 4,500/hour highlights the premium on expertise. Your professional stamp has market value — know what it is worth.'],
                ['label' => 'Refer to a colleague', 'description' => 'You are too tired, but pass the referral and maintain the professional relationship.', 'balance_change' => 0, 'points' => 80, 'icon' => '🤝', 'lesson' => 'Not every opportunity needs to be yours. Referrals build long-term professional goodwill that often returns 10x the original value.'],
            ],
        ],
        [
            'title'         => 'NCA Registration Renewal',
            'scenario_text' => 'Your National Construction Authority (NCA) registration expires in 14 days. Renewal is KES 5,500. Without it, you legally cannot sign off on any projects — and your employer cannot list you on their NCA certification.',
            'icon'          => '🏛️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'engineering', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Renew now (KES 5,500)', 'description' => 'Keep your practice current. The cost of not renewing is your entire income.', 'balance_change' => -5500, 'points' => 100, 'icon' => '✅', 'lesson' => 'Professional licenses are the price of staying in the game. Budget them as a fixed annual cost — they are not optional in regulated fields.'],
                ['label' => 'Renew next month', 'description' => 'Tight month — delay by 30 days and risk a 2-week lapse in certification.', 'balance_change' => 0, 'points' => -30, 'icon' => '⚠️', 'lesson' => 'A lapse in professional certification can cause project delays that cost your employer far more than the renewal fee. The risk is not proportional.'],
            ],
        ],
        [
            'title'         => 'County Tender Notice — Roads Rehab',
            'scenario_text' => 'A county government tender for road maintenance is published. Your firm qualifies under the NCA Category NCA 5. Preparing the bid requires 3 evenings of documentation work. No guarantee of winning — but a KES 2M contract is the prize.',
            'icon'          => '🛣️',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'engineering', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Prepare and submit the bid', 'description' => 'Invest the time, submit a quality bid, and let merit decide. The odds improve with every submission.', 'balance_change' => 0, 'points' => 500, 'icon' => '📝', 'lesson' => 'Government tenders reward persistence. Many SMEs win their first contract on the third or fourth submission. Each bid sharpens your documentation skills and firm profile.'],
                ['label' => 'Skip this round — too much to prepare', 'description' => 'The documentation is complex. Focus on private client work instead.', 'balance_change' => 0, 'points' => 20, 'icon' => '📅', 'lesson' => 'Private and public work both have their place. But public contracts provide cash flow predictability that private work often cannot.'],
            ],
        ],

        // ══════════════════════════════════
        //  CREATIVE  (designer / content creator / artist)
        // ══════════════════════════════════
        [
            'title'         => 'Brand Sponsorship Offer',
            'scenario_text' => 'A fintech brand DMs you for a sponsored post on your Instagram. They offer KES 15,000 for one post + 3 stories. It is a real product you actually use — but you have kept your feed "brand-free" so far.',
            'icon'          => '🎨',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'creative', 'min_chapter' => 2, 'expires_in_days' => 3],
            'choices'       => [
                ['label' => 'Accept the deal (KES 15,000)', 'description' => 'Monetize your audience. The product is legit and you would use it anyway.', 'balance_change' => 15000, 'points' => 100, 'icon' => '💰', 'lesson' => 'Creator income is legitimate income. The key is transparency and only promoting products you would recommend for free. Your trust is your most valuable asset.'],
                ['label' => 'Decline — protect authenticity', 'description' => 'Your credibility with your audience is worth more than one deal.', 'balance_change' => 0, 'points' => 200, 'icon' => '🛡️', 'lesson' => 'Audience trust takes years to build and days to lose. Saying no to money that could damage that trust is a sound long-term financial decision.'],
            ],
        ],
        [
            'title'         => 'Adobe Creative Cloud Auto-Renewal',
            'scenario_text' => 'Your Creative Cloud annual plan auto-renewed — KES 3,200 charged to your card. You did not set a reminder. You use Photoshop and Premiere daily for client work, so it is legitimate — but the surprise spend stings.',
            'icon'          => '🎬',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'creative', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Keep the subscription (KES 3,200 gone)', 'description' => 'Accept it. Cancel the auto-renewal but keep the plan for this year.', 'balance_change' => -3200, 'points' => 50, 'icon' => '✅', 'lesson' => 'Subscription surprises are budget leaks. Audit all your auto-renewals twice a year. Set a calendar reminder 7 days before each annual renewal.'],
                ['label' => 'Challenge the charge and cancel', 'description' => 'Request a refund if within the cancellation window and switch to open-source tools.', 'balance_change' => 0, 'points' => 100, 'icon' => '🆓', 'lesson' => 'Krita and DaVinci Resolve are professional-grade and free. Review whether your creative tools justify their annual cost relative to your client income.'],
            ],
        ],
        [
            'title'         => 'Streaming Royalties Available',
            'scenario_text' => 'DistroKid deposits KES 4,500 in streaming royalties from your music distributed on Spotify and Apple Music. You can withdraw to M-Pesa now or leave it to accumulate for another quarter.',
            'icon'          => '🎵',
            'metadata'      => ['event_type' => 'asset', 'career_track' => 'creative', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Withdraw and allocate (KES 4,500)', 'description' => 'Move to savings or reinvest into production costs for new content.', 'balance_change' => 4500, 'points' => 100, 'icon' => '💵', 'lesson' => 'Passive royalty income is one of the few income streams that works while you sleep. Withdraw it, allocate it intentionally — do not let it sit as idle digital cash.'],
                ['label' => 'Leave it to accumulate', 'description' => 'Let it grow to KES 10,000 before withdrawing — larger amounts feel more real.', 'balance_change' => 1000, 'points' => 150, 'icon' => '📈', 'lesson' => 'Compounding royalties is a valid strategy — but only if you trust the platform\'s payment reliability. Review annually whether accumulation or withdrawal serves you better.'],
            ],
        ],

    ]; // end SCENARIOS

    public function run(): void
    {
        if (!Schema::hasTable('nodes') || !Schema::hasTable('choices')) {
            $this->command->warn('CareerEventSeeder: nodes or choices table not found — skipping. Run after migrations.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach (self::SCENARIOS as $scenario) {
            // Idempotent: skip if a scenario with this exact title already exists
            if (Node::where('title', $scenario['title'])->where('type', 'scenario')->exists()) {
                $skipped++;
                continue;
            }

            $node = Node::create([
                'title'         => $scenario['title'],
                'scenario_text' => $scenario['scenario_text'],
                'type'          => 'scenario',
                'is_start'      => true,
                'is_free'       => true,
                'icon'          => $scenario['icon'],
                'metadata'      => $scenario['metadata'],
            ]);

            foreach ($scenario['choices'] as $i => $choice) {
                Choice::create([
                    'node_id'     => $node->id,
                    'next_node_id'=> null,
                    'label'       => $choice['label'],
                    'description' => $choice['description'],
                    'points'      => $choice['points'] ?? 0,
                    'sort_order'  => $i + 1,
                    'effect_data' => [
                        'balance_change' => $choice['balance_change'] ?? 0,
                        'icon'           => $choice['icon']   ?? ($choice['balance_change'] >= 0 ? '✅' : '⚠️'),
                        'lesson'         => $choice['lesson'] ?? null,
                    ],
                ]);
            }

            $created++;
        }

        $total = count(self::SCENARIOS);
        $this->command->info("CareerEventSeeder: {$created} scenarios created, {$skipped} already existed ({$total} total).");
    }
}
