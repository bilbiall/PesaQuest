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
        [
            'title'         => 'Cloud Hosting Bill Shock',
            'scenario_text' => 'A side-project app you built went briefly viral. Your AWS bill jumped from KES 800/month to KES 22,000 for the month — unexpected traffic charges you never budgeted for. The invoice is due in 5 days.',
            'icon'          => '☁️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'technology', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Pay it and add billing alerts', 'description' => 'Clear the bill, then set up spending caps so it never happens silently again.', 'balance_change' => -22000, 'points' => 150, 'icon' => '🔔', 'lesson' => 'Usage-based cloud costs can spike without warning. Billing alerts are the financial equivalent of a smoke detector — cheap insurance against a nasty surprise.'],
                ['label' => 'Migrate to a free-tier host', 'description' => 'Move the app to a cheaper platform this week and accept a rougher month while you cut over.', 'balance_change' => -6000, 'points' => 100, 'icon' => '🔀', 'lesson' => 'Sometimes the right fix for a cost problem is redesigning the system, not just paying the invoice. Cheaper infrastructure is a valid trade against convenience.'],
            ],
        ],
        [
            'title'         => 'Remote Contract from Abroad',
            'scenario_text' => 'A startup in Germany found your GitHub profile and offers a 3-month remote contract at $1,200/month, paid via Wise. It is real money in hard currency — but means late-night calls to match their time zone.',
            'icon'          => '🌍',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'technology', 'min_chapter' => 3, 'expires_in_days' => 5],
            'choices'       => [
                ['label' => 'Sign the contract', 'description' => 'Three months of dollar income, converted to KES at a strong rate, on top of your day job.', 'balance_change' => 45000, 'points' => 300, 'icon' => '💱', 'lesson' => 'Foreign-currency income is a real hedge against local inflation and currency depreciation — but check your tax obligations on foreign earnings before you spend it all.'],
                ['label' => 'Decline — protect your sleep and day job', 'description' => 'The time-zone strain risks burning you out at your main job for a short-term gig.', 'balance_change' => 0, 'points' => 100, 'icon' => '😴', 'lesson' => 'Not all income is worth its true cost. A contract that quietly damages your primary income source is a bad trade even at a good rate.'],
            ],
        ],
        [
            'title'         => 'Hackathon Prize Money',
            'scenario_text' => 'Your 3-person team just won a fintech hackathon — KES 90,000 prize pot. You built most of the winning feature. The team agreed to split evenly beforehand, but one teammate barely contributed on the final day.',
            'icon'          => '🏆',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'technology', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Split evenly as agreed (KES 30,000)', 'description' => 'Honour the upfront agreement even though the effort was uneven.', 'balance_change' => 30000, 'points' => 200, 'icon' => '🤝', 'lesson' => 'Honouring agreements you made before the outcome was known builds a reputation that outlasts any single prize. People remember who kept their word.'],
                ['label' => 'Renegotiate the split', 'description' => 'Raise it directly and propose a weighted split based on actual contribution.', 'balance_change' => 45000, 'points' => 100, 'icon' => '⚖️', 'lesson' => 'Fair does not always mean equal — but renegotiating after the fact, even fairly, carries real relationship risk. Weigh the extra cash against future collaboration.'],
            ],
        ],
        [
            'title'         => 'Certification Exam — AWS Solutions Architect',
            'scenario_text' => 'The AWS Solutions Architect exam costs KES 20,000 to sit. Employers in Nairobi\'s tech scene pay a visible premium for it, and your manager hinted it could fast-track your next raise.',
            'icon'          => '📜',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'technology', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Pay and commit to 6 weeks of study', 'description' => 'Treat the fee as an investment in a credential the market visibly rewards.', 'balance_change' => -20000, 'points' => 500, 'icon' => '🎓', 'lesson' => 'A certification that reliably moves your salary band pays for itself within months. The real cost is rarely the exam fee — it is the study hours you must actually protect.'],
                ['label' => 'Learn the material for free first', 'description' => 'Study using free tutorials for a few months, then decide whether the exam is worth paying for.', 'balance_change' => 0, 'points' => 150, 'icon' => '📖', 'lesson' => 'Testing your commitment with free resources before paying for a credential is a smart way to avoid wasting money on an exam you are not ready for.'],
            ],
        ],
        [
            'title'         => 'Laptop Dying Mid-Project',
            'scenario_text' => 'Your 5-year-old laptop is overheating and shutting down mid-deploy. A new mid-range machine costs KES 65,000. A refurbished one from a trusted importer is KES 30,000 with a 6-month warranty.',
            'icon'          => '💻',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'technology', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Buy new (KES 65,000)', 'description' => 'Pay more for a full warranty and years of runway before this decision comes up again.', 'balance_change' => -65000, 'points' => 100, 'icon' => '🆕', 'lesson' => 'A work tool that fails mid-task has a hidden cost beyond its price — lost income, missed deadlines, client trust. Sometimes paying more upfront is the cheaper option overall.'],
                ['label' => 'Buy refurbished (KES 30,000)', 'description' => 'Save KES 35,000 now, accept a shorter warranty window.', 'balance_change' => -30000, 'points' => 150, 'icon' => '♻️', 'lesson' => 'Refurbished professional equipment from a reputable source is a legitimate way to cut a major expense in half — just verify the warranty terms before you commit.'],
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
        [
            'title'         => 'Trade Fair Stall Booking',
            'scenario_text' => 'A three-day trade expo at KICC has a vendor stall going for KES 6,000. Foot traffic is expected to be huge. Your neighbouring stall-holder from last year says she made 4x her stall fee in sales.',
            'icon'          => '🎪',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'business', 'min_chapter' => 2, 'expires_in_days' => 6],
            'choices'       => [
                ['label' => 'Book the stall (KES 6,000)', 'description' => 'Take the exposure and the sales chance. Bring extra stock.', 'balance_change' => -6000, 'points' => 200, 'icon' => '🛍️', 'lesson' => 'A stall fee is a marketing spend with a measurable audience size attached. Compare it to your average customer value before committing — the maths usually favours showing up.'],
                ['label' => 'Skip it — save the fee', 'description' => 'Stick to your usual sales channels this month.', 'balance_change' => 0, 'points' => 40, 'icon' => '🛡️', 'lesson' => 'Not every promotional opportunity is worth chasing. Protecting cash is also a valid strategy when your regular channels are already working.'],
            ],
        ],
        [
            'title'         => 'Till Shortfall Discovered',
            'scenario_text' => 'Your monthly stock reconciliation shows a KES 4,200 shortfall — small but recurring for three months running. You suspect an employee, but you have no proof. A basic CCTV system costs KES 12,000 installed.',
            'icon'          => '🕵️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'business', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Install CCTV (KES 12,000)', 'description' => 'Pay upfront for visibility — the shortfall alone costs more than this within 3 months.', 'balance_change' => -12000, 'points' => 250, 'icon' => '📹', 'lesson' => 'A recurring KES 4,200 monthly leak costs KES 50,400 a year — far more than a one-time KES 12,000 fix. Solving a repeating problem is almost always cheaper than living with it.'],
                ['label' => 'Tighten manual stock checks instead', 'description' => 'Free, but relies on your own daily discipline to catch it.', 'balance_change' => 0, 'points' => 80, 'icon' => '📋', 'lesson' => 'A free fix that depends entirely on your own consistency is fragile. Systems that work without your constant attention are worth paying for.'],
            ],
        ],
        [
            'title'         => 'POS Machine for Card Payments',
            'scenario_text' => 'Customers keep asking if you take card payments. A basic POS terminal costs KES 8,000 plus a small transaction fee. Cash-only shops nearby report losing sales to competitors who accept cards and M-Pesa till.',
            'icon'          => '💳',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'business', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Buy the POS terminal (KES 8,000)', 'description' => 'Widen your payment options and capture sales you were quietly losing.', 'balance_change' => -8000, 'points' => 200, 'icon' => '✅', 'lesson' => 'Payment friction loses sales silently — customers who cannot pay easily simply leave instead of complaining. Removing that friction is often the cheapest growth lever available.'],
                ['label' => 'Stick to cash and M-Pesa till only', 'description' => 'Avoid the upfront cost and transaction fees for now.', 'balance_change' => 0, 'points' => 60, 'icon' => '💵', 'lesson' => 'M-Pesa till already covers most digital payments in Kenya — a card reader is not always essential. Know your specific customer base before spending on it.'],
            ],
        ],
        [
            'title'         => 'Free Business Mentorship Program',
            'scenario_text' => 'A commercial bank is running a free 8-week SME mentorship program — weekly 2-hour sessions on cash flow, marketing, and scaling. It is unpaid time, but past graduates report real revenue growth afterward.',
            'icon'          => '🧭',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'business', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Join the program', 'description' => 'Block 2 hours weekly for 8 weeks — a real time cost, but structured expert input is rare and free.', 'balance_change' => 0, 'points' => 400, 'icon' => '📈', 'lesson' => 'Time invested in structured business education compounds — it is not lost income, it is deferred income. Free expert mentorship is a resource most businesses never get.'],
                ['label' => 'Skip it — focus on daily operations', 'description' => 'Protect the 2 hours weekly for running the shop instead.', 'balance_change' => 0, 'points' => 30, 'icon' => '📅', 'lesson' => 'Staying "too busy" to learn is one of the most common ways small businesses plateau. Growth usually requires stepping back from daily operations occasionally.'],
            ],
        ],
        [
            'title'         => 'Landlord Raises the Rent',
            'scenario_text' => 'Your shop landlord is raising rent by 20% at the next lease renewal — from KES 15,000 to KES 18,000/month. A similar space two streets away is available at your old rate, but relocating means losing walk-in customers who know your current spot.',
            'icon'          => '🏬',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'business', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Accept the increase and stay', 'description' => 'Pay more to keep your established location and existing customer flow.', 'balance_change' => -3000, 'points' => 100, 'icon' => '📍', 'lesson' => 'Location value is real and hard to rebuild — but always check whether the increase is actually justified by comparable rates nearby before agreeing.'],
                ['label' => 'Negotiate or threaten to relocate', 'description' => 'Push back with the cheaper alternative as leverage before signing anything.', 'balance_change' => 0, 'points' => 250, 'icon' => '💬', 'lesson' => 'Landlords often have room to negotiate, especially with a reliable long-term tenant. The worst outcome of asking is simply "no" — silence costs you the full increase by default.'],
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
        [
            'title'         => 'Annual Malpractice Insurance Due',
            'scenario_text' => 'Your professional indemnity insurance renewal notice arrived — KES 6,500 for the year. It has never been claimed on, and money is tight this month, but practicing without it is against your board\'s rules.',
            'icon'          => '📄',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'healthcare', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Pay in full now (KES 6,500)', 'description' => 'Stay compliant and covered without a gap in protection.', 'balance_change' => -6500, 'points' => 100, 'icon' => '✅', 'lesson' => 'Indemnity insurance is not optional risk management in healthcare — one uncovered incident can cost far more than a lifetime of premiums.'],
                ['label' => 'Ask about a monthly payment plan', 'description' => 'Spread the cost across the year instead of paying it all at once.', 'balance_change' => -600, 'points' => 80, 'icon' => '📆', 'lesson' => 'Spreading a fixed annual cost into monthly instalments (even at a small premium) can protect your cash flow without skipping the coverage itself.'],
            ],
        ],
        [
            'title'         => 'Rural Posting with Hardship Allowance',
            'scenario_text' => 'The Ministry is offering a transfer to a hardship-area health centre — a 40% hardship allowance on top of your salary. It means relocating away from family for at least a year.',
            'icon'          => '🏞️',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'healthcare', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Accept the posting', 'description' => 'A full year of significantly higher take-home pay, with real sacrifice attached.', 'balance_change' => 18000, 'points' => 300, 'icon' => '💼', 'lesson' => 'Hardship and remote allowances exist because the trade-off is real, not free money. Run the numbers on a full year, not just the monthly figure, before deciding.'],
                ['label' => 'Stay in your current posting', 'description' => 'Keep your support network and current routine intact.', 'balance_change' => 0, 'points' => 100, 'icon' => '🏠', 'lesson' => 'Quality of life has a financial value even when it does not show up as a number. The highest-paying option is not automatically the right one.'],
            ],
        ],
        [
            'title'         => 'Specialization Scholarship Application',
            'scenario_text' => 'A KMTC-affiliated scholarship covers 80% of tuition for a specialized nursing diploma (ICU/theatre). The remaining 20% is KES 40,000, plus a year of reduced working hours while you study.',
            'icon'          => '🎓',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'healthcare', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Apply and pay your share (KES 40,000)', 'description' => 'Invest in a specialization that pays significantly more once qualified.', 'balance_change' => -40000, 'points' => 600, 'icon' => '🩺', 'lesson' => 'Specialization is one of the most reliable salary jumps available in healthcare. An 80%-subsidized scholarship is a rare discount on a career upgrade — worth stretching for.'],
                ['label' => 'Wait for a fully-funded scholarship', 'description' => 'Keep saving and reapply next cycle in case a 100%-funded slot opens up.', 'balance_change' => 0, 'points' => 50, 'icon' => '⏳', 'lesson' => 'Waiting for the perfect version of an opportunity sometimes means missing the good-enough version entirely. Fully-funded slots are rarer than partially-funded ones.'],
            ],
        ],
        [
            'title'         => 'Free Medical Camp vs Paid Weekend Shift',
            'scenario_text' => 'A church-organized free medical camp needs volunteer clinicians this Saturday — unpaid, but high-visibility community work. Your facility is also offering a paid weekend shift at KES 3,500 the same day. You cannot do both.',
            'icon'          => '⛑️',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'healthcare', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Volunteer at the medical camp', 'description' => 'Skip the pay, build community goodwill and visibility instead.', 'balance_change' => 0, 'points' => 250, 'icon' => '❤️', 'lesson' => 'Unpaid work that builds reputation and network is a real investment — just make sure it does not become a permanent substitute for paid income.'],
                ['label' => 'Take the paid weekend shift', 'description' => 'Choose the guaranteed KES 3,500 over the intangible goodwill.', 'balance_change' => 3500, 'points' => 80, 'icon' => '💵', 'lesson' => 'There is no wrong answer here — but be honest with yourself about which currency (cash now vs. reputation later) you actually need more of right now.'],
            ],
        ],
        [
            'title'         => 'Buying Your Own PPE',
            'scenario_text' => 'Your facility is out of gloves and masks again — third time this quarter. A pharmacy nearby sells a personal supply for KES 1,800 that would last you a month. Management says a new order is "coming soon."',
            'icon'          => '🧤',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'healthcare', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Buy your own supply (KES 1,800)', 'description' => 'Protect yourself immediately rather than waiting on procurement.', 'balance_change' => -1800, 'points' => 100, 'icon' => '🛡️', 'lesson' => 'Your health is the asset your entire income depends on. Sometimes the "employer\'s responsibility" argument is correct but slow — and slow can be dangerous.'],
                ['label' => 'Escalate to management instead', 'description' => 'Push formally for the facility to resupply rather than covering the gap yourself.', 'balance_change' => 0, 'points' => 150, 'icon' => '📢', 'lesson' => 'Repeatedly covering systemic employer gaps out of pocket trains the system to rely on you doing so. Escalating protects both your wallet and your colleagues.'],
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
        [
            'title'         => 'Greenhouse Kit Investment',
            'scenario_text' => 'A basic greenhouse kit for tomatoes costs KES 45,000 installed. Greenhouse tomatoes fetch nearly double the open-field price and are less exposed to weather swings. A neighbour recouped their cost in one season.',
            'icon'          => '🍅',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'agriculture', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Install the greenhouse (KES 45,000)', 'description' => 'A significant upfront cost for higher, steadier returns going forward.', 'balance_change' => -45000, 'points' => 400, 'icon' => '🏗️', 'lesson' => 'Capital investment in controlled-environment farming can transform a farm\'s income stability — but only pencil it in if you have realistically modelled at least two seasons, not just one lucky neighbour\'s result.'],
                ['label' => 'Continue open-field farming', 'description' => 'Keep costs low and stay flexible without the upfront commitment.', 'balance_change' => 0, 'points' => 100, 'icon' => '🌱', 'lesson' => 'Not every proven upgrade fits every budget or risk appetite. Growing capital slowly through savings before a big farm investment reduces the downside if the season disappoints.'],
            ],
        ],
        [
            'title'         => 'Weevils in the Grain Store',
            'scenario_text' => 'Two months after harvest, you notice weevil damage starting in your maize store — untreated, you could lose 20-30% of it. Hermetic storage bags cost KES 350 each; you need 15 for your full harvest.',
            'icon'          => '🌽',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'agriculture', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Buy hermetic bags (KES 5,250)', 'description' => 'Protect the harvest now before losses compound further.', 'balance_change' => -5250, 'points' => 250, 'icon' => '🛡️', 'lesson' => 'Post-harvest losses silently destroy more farmer income than most people realize. KES 5,250 spent to protect a harvest worth many times that is one of the highest-return purchases in farming.'],
                ['label' => 'Sell the grain quickly at a lower price', 'description' => 'Offload now at a discount to avoid further pest damage.', 'balance_change' => -8000, 'points' => 60, 'icon' => '📉', 'lesson' => 'A rushed sale to dodge a problem usually costs more than solving the problem directly. Compare the discount you are accepting against the cost of the actual fix.'],
            ],
        ],
        [
            'title'         => 'Free County Extension Training',
            'scenario_text' => 'The county agricultural extension officer is running a free two-day training on drip irrigation and soil testing. It clashes with two market days, meaning lost sales time at the stall.',
            'icon'          => '👩‍🌾',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'agriculture', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Attend the training', 'description' => 'Miss two market days for knowledge that could raise yields for years.', 'balance_change' => -2000, 'points' => 350, 'icon' => '📗', 'lesson' => 'Free technical training is a rare, high-leverage resource — the short-term sales you miss are usually recovered many times over by better techniques applied for years afterward.'],
                ['label' => 'Skip it — protect this week\'s sales', 'description' => 'Stay at the market and keep this week\'s income intact.', 'balance_change' => 2000, 'points' => 40, 'icon' => '🧺', 'lesson' => 'Protecting immediate cash flow is sometimes the right call — but watch for a pattern of always choosing today\'s shilling over tomorrow\'s skill.'],
            ],
        ],
        [
            'title'         => 'Middleman Offer vs Market Trip',
            'scenario_text' => 'A middleman offers to buy your entire cabbage harvest today at KES 15/head, right at the farm gate. Taking it to the open market yourself would fetch KES 22/head — but costs KES 2,500 in transport and a full day\'s time.',
            'icon'          => '🥬',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'agriculture', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Sell to the middleman on the spot', 'description' => 'Take the lower price for guaranteed, immediate, zero-effort payment.', 'balance_change' => 3000, 'points' => 80, 'icon' => '🤝', 'lesson' => 'Convenience has a price, and sometimes it is worth paying — especially if your time has other high-value uses that day, or cash-in-hand solves a more urgent problem.'],
                ['label' => 'Transport it to market yourself', 'description' => 'Spend the day and the transport cost for a meaningfully better price per head.', 'balance_change' => 5000, 'points' => 200, 'icon' => '🚚', 'lesson' => 'Cutting out the middleman raises your margin, but only if you correctly account for your own time and transport cost — do the full math, not just the headline price difference.'],
            ],
        ],
        [
            'title'         => 'Borehole Drilling Decision',
            'scenario_text' => 'A hydrogeologist survey confirms good water at 40 metres on your land. Drilling and pump installation costs KES 180,000 — a huge sum, but it would end your dependence on unpredictable rain-fed irrigation permanently.',
            'icon'          => '💧',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'agriculture', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Take a SACCO loan and drill', 'description' => 'Borrow against future harvests to secure water independence now.', 'balance_change' => -60000, 'points' => 500, 'icon' => '🏦', 'lesson' => 'Water reliability can be the single biggest yield-stabilizing investment a farm makes — but only if the loan terms and expected harvest income genuinely support the repayment schedule.'],
                ['label' => 'Save toward it over several seasons', 'description' => 'Avoid debt and build the fund gradually, season by season.', 'balance_change' => -10000, 'points' => 150, 'icon' => '💰', 'lesson' => 'Debt-free growth is slower but carries no repayment risk if a season fails. Match your financing strategy to how much risk your household can actually absorb.'],
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
        [
            'title'         => 'Client Error Compensation',
            'scenario_text' => 'You processed a client\'s standing order at the wrong amount for two months before catching it. The bank\'s policy lets you cover the KES 3,000 discrepancy quietly from a discretionary error fund, or escalate it formally and risk a mark on your record.',
            'icon'          => '⚠️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'finance', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Escalate and correct it properly', 'description' => 'Report the error through the correct channel, accept the process, and fix the client\'s account transparently.', 'balance_change' => 0, 'points' => 300, 'icon' => '📋', 'lesson' => 'In regulated finance roles, transparency about errors protects your license and reputation far more than it damages your career. Cover-ups compound risk; disclosure limits it.'],
                ['label' => 'Quietly cover it from the error fund', 'description' => 'Use the discretionary fund to fix it fast, avoiding the paperwork.', 'balance_change' => -3000, 'points' => -100, 'icon' => '🤫', 'lesson' => 'Bypassing formal error-reporting processes, even with good intentions, can violate compliance rules that exist specifically to protect clients and the institution — and you.'],
            ],
        ],
        [
            'title'         => 'Forex Trading Course Pitch',
            'scenario_text' => 'A social media ad promises to teach you forex trading for KES 25,000, showing screenshots of huge weekly profits. Your colleague who took it says results have been "mixed." No regulator oversight is mentioned anywhere.',
            'icon'          => '📉',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'finance', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Decline and research independently', 'description' => 'Skip the paid pitch and study forex fundamentals through free, credible sources first.', 'balance_change' => 0, 'points' => 250, 'icon' => '📖', 'lesson' => 'As a finance professional, you already know the red flags: guaranteed returns, unregulated platforms, screenshot-only proof. The same scepticism you apply for clients applies to yourself too.'],
                ['label' => 'Pay for the course (KES 25,000)', 'description' => 'Take the risk on the promised training and mentorship.', 'balance_change' => -25000, 'points' => -50, 'icon' => '🎰', 'lesson' => 'Unregulated trading "courses" that emphasize lifestyle over risk disclosure are a classic pattern. Losing this money teaches the lesson your professional training should have already taught for free.'],
            ],
        ],
        [
            'title'         => 'Loan Disbursement Target Bonus',
            'scenario_text' => 'You hit your quarterly microfinance loan disbursement target early and unlocked a KES 15,000 performance bonus. Your manager hints that exceeding it further this quarter could unlock an even bigger one.',
            'icon'          => '🎯',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'finance', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Bank the bonus and ease off', 'description' => 'Take the win, maintain quality underwriting rather than chasing volume.', 'balance_change' => 15000, 'points' => 200, 'icon' => '💰', 'lesson' => 'Loan officer incentives that reward volume alone can quietly encourage weaker underwriting. Protecting loan quality protects your job and your clients long after this bonus is spent.'],
                ['label' => 'Push hard for the bigger bonus', 'description' => 'Chase the stretch target aggressively this quarter.', 'balance_change' => 15000, 'points' => 50, 'icon' => '🚀', 'lesson' => 'Aggressive pursuit of disbursement bonuses has caused real default crises in microfinance before. A bigger bonus this quarter is not worth a portfolio of bad loans next quarter.'],
            ],
        ],
        [
            'title'         => 'Weekend Audit Overtime',
            'scenario_text' => 'Year-end internal audit needs extra hands this weekend — time-and-a-half pay, roughly KES 6,000 for two days. You already worked last weekend and had plans with family.',
            'icon'          => '🧾',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'finance', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Take the overtime (KES 6,000)', 'description' => 'Prioritize the extra income this cycle.', 'balance_change' => 6000, 'points' => 100, 'icon' => '💵', 'lesson' => 'Overtime pay is genuine extra income, but two consecutive weekends is a pattern worth watching — track whether it is becoming a habit rather than an occasional boost.'],
                ['label' => 'Decline and protect your time off', 'description' => 'Keep your family plans and rest instead.', 'balance_change' => 0, 'points' => 150, 'icon' => '👨‍👩‍👧', 'lesson' => 'Consistently trading personal time for overtime pay erodes the relationships and rest that sustain a long career. Some weekends are worth more unpaid than paid.'],
            ],
        ],
        [
            'title'         => 'Diversifying a Year-End Bonus',
            'scenario_text' => 'Your year-end bonus of KES 40,000 just landed. As a finance professional you know the textbook answer is diversification — but everything you personally own right now is just an M-Pesa savings wallet.',
            'icon'          => '📊',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'finance', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Split across an MMF, a T-Bill, and savings', 'description' => 'Practice what you preach to clients — spread the bonus across a few asset types.', 'balance_change' => -40000, 'points' => 400, 'icon' => '🧩', 'lesson' => 'Diversification is easy to explain to clients and surprisingly easy to skip for yourself. Applying your own professional advice to your own money is a discipline worth building deliberately.'],
                ['label' => 'Leave it all in savings for now', 'description' => 'Keep it simple and liquid until you have time to plan properly.', 'balance_change' => 0, 'points' => 100, 'icon' => '🏦', 'lesson' => 'Liquidity while you plan is not a bad instinct — but "for now" quietly becoming "for years" is how finance professionals end up under-invested in their own portfolios.'],
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
        [
            'title'         => 'Selling a Course on an Edtech Platform',
            'scenario_text' => 'A local edtech platform lets teachers upload and sell revision courses — you would keep 70% of sales. Recording a full KCSE revision course for your subject would take roughly 20 unpaid hours.',
            'icon'          => '🎥',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'education', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Record the course', 'description' => 'Invest 20 hours upfront for an income stream that can sell repeatedly afterward.', 'balance_change' => 0, 'points' => 350, 'icon' => '📹', 'lesson' => 'Content you create once and sell many times is one of the few ways a teacher\'s income can become partly passive. The upfront time cost is real, but it does not repeat per sale.'],
                ['label' => 'Skip it — not enough time this term', 'description' => 'Focus fully on your current teaching load instead.', 'balance_change' => 0, 'points' => 40, 'icon' => '📅', 'lesson' => 'Passive-income ideas are only valuable if you can actually protect the time to build them. A busy term is a legitimate reason to defer, not abandon, the idea.'],
            ],
        ],
        [
            'title'         => 'School Trip Chaperone Stipend',
            'scenario_text' => 'The school needs a teacher to chaperone a weekend nature-club trip — a small KES 1,500 stipend for giving up your Saturday, plus all expenses covered.',
            'icon'          => '🚌',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'education', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Volunteer as chaperone', 'description' => 'Give up the Saturday for the stipend and goodwill with school leadership.', 'balance_change' => 1500, 'points' => 100, 'icon' => '🌳', 'lesson' => 'Small, low-effort income opportunities add up over a year, and volunteering for visible school duties often factors into how leadership rates you for future opportunities.'],
                ['label' => 'Decline — protect your weekend', 'description' => 'Keep the day for yourself instead.', 'balance_change' => 0, 'points' => 40, 'icon' => '😌', 'lesson' => 'A small stipend is not always worth a full day of your only rest time. Value your weekend honestly against what it is actually being traded for.'],
            ],
        ],
        [
            'title'         => 'Master\'s in Education Application',
            'scenario_text' => 'A part-time Master\'s in Education program would move you up the TSC salary scale on completion. Tuition is KES 180,000 over two years, payable per semester, while you keep teaching full-time.',
            'icon'          => '🎓',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'education', 'min_chapter' => 3],
            'choices'       => [
                ['label' => 'Enroll and pay per semester', 'description' => 'Commit to two years of study while working, for a permanent salary scale jump.', 'balance_change' => -30000, 'points' => 600, 'icon' => '📘', 'lesson' => 'A TSC salary scale jump is permanent and compounds every year afterward — the two-year cost is finite, but the raise is not. Compare the total tuition against 10+ years of higher pay.'],
                ['label' => 'Wait for a sponsored slot', 'description' => 'Apply for county or NGO sponsorship instead of self-funding.', 'balance_change' => 0, 'points' => 60, 'icon' => '⏳', 'lesson' => 'Sponsored postgraduate slots exist but are competitive and slow. Waiting is free but has a real opportunity cost — every year delayed is a year of the higher salary you do not yet have.'],
            ],
        ],
        [
            'title'         => 'Association Membership for Referrals',
            'scenario_text' => 'A private tutors\' association charges KES 3,000/year membership but lists vetted members on a parent-facing directory that generates real referrals for its members.',
            'icon'          => '🗂️',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'education', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Join the association (KES 3,000)', 'description' => 'Pay for visibility and credibility with prospective parents.', 'balance_change' => -3000, 'points' => 150, 'icon' => '📇', 'lesson' => 'A directory listing that reliably generates even one new client a year already pays for itself. Professional association fees are a marketing cost, not a pure expense.'],
                ['label' => 'Rely on word-of-mouth referrals', 'description' => 'Keep growing your client base organically without the membership fee.', 'balance_change' => 0, 'points' => 60, 'icon' => '🗣️', 'lesson' => 'Word-of-mouth is free and powerful but slower and less predictable. Weigh a guaranteed marketing channel against an unpredictable free one based on how much growth you actually need.'],
            ],
        ],
        [
            'title'         => 'Buying Marking Pens and Printing Credit',
            'scenario_text' => 'Report-writing season means printing 40 report forms and buying fresh red pens for marking — small costs the school does not reimburse, roughly KES 900 this term.',
            'icon'          => '🖊️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'education', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Pay out of pocket (KES 900)', 'description' => 'Cover the small recurring cost yourself, as most terms require.', 'balance_change' => -900, 'points' => 60, 'icon' => '✅', 'lesson' => 'Small recurring out-of-pocket costs add up over a career — track them. If they total a meaningful sum annually, that is worth raising with school administration.'],
                ['label' => 'Ask the school to reimburse this term', 'description' => 'Submit a receipt and request reimbursement through the official channel.', 'balance_change' => 0, 'points' => 120, 'icon' => '🧾', 'lesson' => 'Formally requesting reimbursement for legitimate work costs, even small ones, builds a paper trail that can shift school policy over time — and gets your money back.'],
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
        [
            'title'         => 'CAD Software License Renewal',
            'scenario_text' => 'Your AutoCAD annual license renewal is due — KES 45,000 for the full suite, or KES 12,000/year for a lighter plan that drops 3D modelling you rarely use anyway.',
            'icon'          => '📐',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'engineering', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Renew the full suite (KES 45,000)', 'description' => 'Keep every feature available even if most go unused.', 'balance_change' => -45000, 'points' => 60, 'icon' => '✅', 'lesson' => 'Paying for capability you do not use is a common professional-software trap. Audit which features you actually touch before renewing at the top tier out of habit.'],
                ['label' => 'Downgrade to the lighter plan (KES 12,000)', 'description' => 'Drop the unused 3D modelling tier and save KES 33,000.', 'balance_change' => -12000, 'points' => 200, 'icon' => '✂️', 'lesson' => 'Reviewing software subscriptions against actual usage before renewal is one of the easiest ways to reclaim money without changing your work at all.'],
            ],
        ],
        [
            'title'         => 'Mandatory Site Safety Gear Upgrade',
            'scenario_text' => 'A new site safety audit flags your hard hat and harness as past their recommended replacement date. Compliant replacements cost KES 7,500. Without them, you cannot legally access the active site.',
            'icon'          => '🦺',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'engineering', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Replace the gear immediately (KES 7,500)', 'description' => 'Stay compliant and safe — site access depends on it.', 'balance_change' => -7500, 'points' => 120, 'icon' => '✅', 'lesson' => 'Safety equipment is non-negotiable operating cost in engineering, not a discretionary spend. The real cost of an incident dwarfs the price of the gear that prevents it.'],
                ['label' => 'Ask the contractor to cover it', 'description' => 'Request the employer supply compliant gear as part of site safety obligations.', 'balance_change' => 0, 'points' => 150, 'icon' => '📋', 'lesson' => 'Many site safety costs are legally the contractor\'s responsibility, not the individual engineer\'s. It is worth knowing which costs you should never have to personally absorb.'],
            ],
        ],
        [
            'title'         => 'Startup Feasibility Report Request',
            'scenario_text' => 'A startup building modular housing wants a paid feasibility and structural report before their investor pitch — KES 35,000 flat fee, due in 10 days alongside your regular job.',
            'icon'          => '📑',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'engineering', 'min_chapter' => 3, 'expires_in_days' => 4],
            'choices'       => [
                ['label' => 'Take the consulting job (KES 35,000)', 'description' => 'Work evenings for 10 days to deliver a thorough independent report.', 'balance_change' => 35000, 'points' => 300, 'icon' => '💼', 'lesson' => 'Independent consulting income at this scale is a strong hourly rate compared to salaried work — but only sustainable if it does not chronically eat into rest or your main job\'s performance.'],
                ['label' => 'Decline — too tight a timeline', 'description' => 'Protect your current workload and turn down the rushed deadline.', 'balance_change' => 0, 'points' => 60, 'icon' => '⏱️', 'lesson' => 'A rushed engineering report carries real professional liability risk. Turning down work you cannot do properly in the time given protects your license and reputation.'],
            ],
        ],
        [
            'title'         => 'Professional Engineer (PE) Registration',
            'scenario_text' => 'You are eligible to apply for full Professional Engineer registration with the Engineers Board of Kenya — KES 15,000 in fees and assessments. Full registration unlocks signing authority on larger contracts.',
            'icon'          => '🏅',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'engineering', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Apply for full registration (KES 15,000)', 'description' => 'Unlock signing authority and access to higher-value contracts.', 'balance_change' => -15000, 'points' => 600, 'icon' => '📜', 'lesson' => 'Full professional registration is often the single biggest unlock in an engineering career — it changes which contracts you can even bid for, not just your salary.'],
                ['label' => 'Delay a year to build more experience', 'description' => 'Wait until you feel more confident in the assessment portfolio.', 'balance_change' => 0, 'points' => 40, 'icon' => '⏳', 'lesson' => 'Some professional milestones benefit from more seasoning first — but check the board\'s actual minimum requirements rather than assuming you need more time than you do.'],
            ],
        ],
        [
            'title'         => 'Weekend Solar Installation Gig',
            'scenario_text' => 'A homeowner wants a residential solar backup system installed — you have the electrical skills but not a solar-specific certificate. The job pays KES 20,000 but carries liability risk if something goes wrong without proper certification.',
            'icon'          => '☀️',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'engineering', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Get certified first, then take similar jobs', 'description' => 'Spend KES 8,000 and a weekend on a solar certification course before accepting this type of work.', 'balance_change' => -8000, 'points' => 300, 'icon' => '🎓', 'lesson' => 'Working outside your certified scope for quick cash is a real liability exposure. Investing in the right certification opens the same income stream safely and repeatedly.'],
                ['label' => 'Take the job now, uncertified', 'description' => 'Accept the KES 20,000 based on your existing electrical competence.', 'balance_change' => 20000, 'points' => -50, 'icon' => '⚠️', 'lesson' => 'Uncertified work in a regulated trade can void insurance and create personal legal liability if anything fails. The immediate KES 20,000 does not cover that downside risk.'],
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
        [
            'title'         => 'Wedding Photography Booking',
            'scenario_text' => 'A couple wants to book you for their wedding — KES 35,000 for the full day. Your current lens struggles in low light, and the reception is indoors. A better lens rental for the day costs KES 4,000.',
            'icon'          => '📷',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'creative', 'min_chapter' => 2, 'expires_in_days' => 5],
            'choices'       => [
                ['label' => 'Book it and rent the better lens', 'description' => 'Take the job and spend KES 4,000 to guarantee quality results in low light.', 'balance_change' => 31000, 'points' => 250, 'icon' => '✅', 'lesson' => 'Spending a small fraction of a booking fee to guarantee the quality that booking depends on is a smart trade — protecting your reputation protects every future booking too.'],
                ['label' => 'Book it and use your current gear', 'description' => 'Skip the rental cost and hope the lighting works out.', 'balance_change' => 35000, 'points' => 60, 'icon' => '🎲', 'lesson' => 'Cutting a small preparation cost on a high-stakes, one-take event (a wedding cannot be reshot) risks the far larger cost of a disappointed client and a damaged reputation.'],
            ],
        ],
        [
            'title'         => 'Trendy NFT Platform Pitch',
            'scenario_text' => 'A new platform claims your digital art could sell as NFTs for real money, but charges a KES 5,000 "minting fee" upfront with no guarantee of any sale. Some creators online claim huge wins; most posts about it go quiet after a few weeks.',
            'icon'          => '🖼️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'creative', 'min_chapter' => 1],
            'choices'       => [
                ['label' => 'Skip it — sell through established channels', 'description' => 'Keep building your audience on platforms with a proven track record instead.', 'balance_change' => 0, 'points' => 200, 'icon' => '🛡️', 'lesson' => 'An upfront fee with no guaranteed return and a market full of quiet failures is a classic speculative-hype pattern. Building steadily on proven channels beats chasing every new trend.'],
                ['label' => 'Pay the minting fee and try it', 'description' => 'Take the gamble on the new platform.', 'balance_change' => -5000, 'points' => -30, 'icon' => '🎰', 'lesson' => 'Speculative platforms that require payment before any proof of return should be treated the same as gambling — only risk what you can fully afford to lose.'],
            ],
        ],
        [
            'title'         => 'Someone Used Your Work Without Credit',
            'scenario_text' => 'A local brand used one of your designs in their ad campaign without asking or paying you — you have screenshots proving it is your original file. A consultation with an IP-focused lawyer costs KES 3,000.',
            'icon'          => '⚖️',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'creative', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Get legal advice (KES 3,000)', 'description' => 'Understand your actual rights and options before approaching the brand.', 'balance_change' => -3000, 'points' => 250, 'icon' => '📋', 'lesson' => 'Knowing your intellectual property rights before you negotiate changes your leverage completely. A small consultation fee can be the difference between a fair settlement and none at all.'],
                ['label' => 'Message the brand directly yourself', 'description' => 'Reach out informally first and ask for credit or payment.', 'balance_change' => 0, 'points' => 100, 'icon' => '✉️', 'lesson' => 'A direct, professional approach sometimes resolves these situations quickly and for free — but knowing when to escalate to formal advice if ignored matters just as much.'],
            ],
        ],
        [
            'title'         => 'Professional Portfolio Website',
            'scenario_text' => 'A custom portfolio website with your own domain costs KES 15,000 to build, versus staying free-only on Instagram. Two recent clients said they almost did not book you because you "looked unprofessional online."',
            'icon'          => '🌐',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'creative', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Invest in a portfolio site (KES 15,000)', 'description' => 'Build a professional home base you fully control, separate from any single social platform.', 'balance_change' => -15000, 'points' => 300, 'icon' => '💻', 'lesson' => 'A portfolio you own is not subject to an algorithm change or a platform ban. For a working creative, it is a business asset, not a vanity purchase.'],
                ['label' => 'Stay Instagram-only for now', 'description' => 'Keep costs at zero and rely entirely on your social presence.', 'balance_change' => 0, 'points' => 50, 'icon' => '📱', 'lesson' => 'Free platforms are a reasonable starting point, but building a client-facing business entirely on rented digital land carries real long-term risk if that platform changes its rules.'],
            ],
        ],
        [
            'title'         => 'Collab with a Bigger Creator',
            'scenario_text' => 'A creator with 5x your following wants to collaborate on a paid content series — but expects you to split the KES 40,000 production costs (studio, props) evenly despite the unequal audience size.',
            'icon'          => '🤝',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'creative', 'min_chapter' => 2],
            'choices'       => [
                ['label' => 'Negotiate a smaller cost share', 'description' => 'Propose splitting costs by audience share instead of evenly, given the exposure imbalance.', 'balance_change' => -10000, 'points' => 250, 'icon' => '💬', 'lesson' => 'In any collaboration, cost-sharing should reflect actual value exchanged, not just an easy 50/50 default. Negotiating this upfront prevents resentment later.'],
                ['label' => 'Accept the even split', 'description' => 'Pay half the production cost for the exposure to a much bigger audience.', 'balance_change' => -20000, 'points' => 150, 'icon' => '📈', 'lesson' => 'Sometimes paying a premium for access to a bigger audience is worth it — but only if you have genuinely estimated the follower and client growth it is likely to bring you.'],
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
