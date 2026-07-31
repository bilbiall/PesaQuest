<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Story;
use App\Models\Node;
use App\Models\Choice;

class EducationArcSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCodingBootcamp();
        $this->seedBusinessDiploma();
        $this->seedHealthcarePath();
        $this->seedAgriFinance();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORY 1: The Coding Bootcamp (18-25 → Technology career, 55k/mo)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedCodingBootcamp(): void
    {
        $story = Story::create([
            'title'       => 'The Coding Bootcamp',
            'description' => 'Amina, 22, has saved KSh 45,000. A tech recruiter tells her about a 3-month coding bootcamp. Is education an investment or an expense?',
            'age_group'   => '18-25',
            'is_active'   => true,
            'sort_order'  => 10,
        ]);

        // Node 1 — Opening scenario
        $n1 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Digital Skills Fork',
            'scenario_text' => "Amina checks her M-Pesa savings: KSh 45,000. Her cousin Brian, who just landed a developer job at a Nairobi startup, texts her:\n\n\"There's a 3-month coding bootcamp — KSh 30,000. I came out earning KSh 55,000 a month. Slots close Friday.\"\n\nAmina's current job at a cyber café pays KSh 18,000. She has 3 days to decide.",
            'type'     => 'scenario',
            'metadata' => ['market_event' => 'Tech jobs in Kenya grew 34% last year. The demand is real.'],
        ]);

        // Node 2 — Bootcamp is going well
        $n2 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Bootcamp Week 6: The Build-Off',
            'scenario_text' => "Week 6. The instructors announce a 48-hour hackathon — build a working web app or get dropped from the programme.\n\nAmina has been struggling with JavaScript. Her project partner wants to take a shortcut: copy a project from GitHub and claim it's theirs.\n\nThe prize for winning: a paid internship referral.",
            'type'     => 'scenario',
            'metadata' => null,
        ]);

        // Node 3 — Went the free course route first
        $n3 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Six Months of YouTube Tutorials',
            'scenario_text' => "Amina starts free courses on YouTube and Coursera. Four months in, she's learned a lot — but has no certificate, no portfolio, and no callbacks from job applications.\n\nA local tech hub is offering a KSh 6,000 certification exam that could validate her skills and get her in front of hiring managers. She still has KSh 43,000 saved.",
            'type'     => 'scenario',
            'metadata' => ['market_event' => 'Hiring managers get 200+ unqualified applications daily. A certificate helps you stand out.'],
        ]);

        // Node 4 — Opportunity cost ending (lesson)
        $n4 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The KSh 30,000 Phone',
            'scenario_text' => "Amina buys a flagship smartphone. Posting photos on Instagram is fun. A year later, Brian is earning KSh 80,000 with a promotion. Amina is still at the cyber café, and her phone is cracked.\n\nShe googles: \"best coding bootcamps Kenya\" — the same one Brian told her about costs KSh 45,000 now.",
            'type'     => 'result',
            'metadata' => ['final_lesson' => 'Every purchase has an opportunity cost. Ask yourself: what am I choosing NOT to do with this money? Skills appreciate. Gadgets depreciate.'],
        ]);

        // Node 5 — Career unlock: Technology (great ending)
        $n5 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Junior Developer — Career Unlocked',
            'scenario_text' => "Amina's app wins the hackathon. Three weeks later, she gets an offer: Junior Developer, KSh 55,000/month — 3× her old salary.\n\nHer investment of KSh 30,000 will pay back in under a month of her new salary. From here, every financial decision she makes will be from a position of strength.\n\n🎯 Career unlocked: Technology",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'technology',
                'career_title'         => 'Junior Developer',
                'career_income'        => 55000,
                'final_lesson'         => 'Education is the highest-return investment you will ever make. KSh 30,000 in skills can unlock a lifetime of compounding income.',
            ],
        ]);

        // Node 6 — Slow track ending (lesson)
        $n6 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Endless Student',
            'scenario_text' => "Amina keeps learning for free. She is genuinely knowledgeable — but without credentials or a portfolio project, no one calls back. Two years later she finally pays for a certification and gets a job.\n\nFree is not always cheaper. The cost of delay was KSh 18,000/month in lost salary for two years.",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'  => 'lesson',
                'final_lesson' => 'Learning without validation can be a trap. The market rewards demonstrated competence — certifications, portfolios, and referrals bridge the gap between knowing and earning.',
            ],
        ]);

        // Node 7 — Integrity wins (alternate path from bootcamp)
        $n7 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Shortcut That Cost Everything',
            'scenario_text' => "The copied project is detected by the instructors' plagiarism check. Amina's partner is dropped immediately. Because Amina didn't report it when she knew, she gets a final warning.\n\nShe rebuilds on her own in 30 hours, submits at 4 AM — it's basic, but it's hers. She passes. Her partner doesn't.",
            'type'     => 'result',
            'metadata' => ['final_lesson' => 'In tech and in finance, your reputation is your brand. One shortcut can cost more than any exam fee.'],
        ]);

        // ── Choices ──────────────────────────────────────────────────────────
        // From n1
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n2->id, 'label' => 'Enroll in the bootcamp (spend KSh 30,000)', 'description' => 'An investment in high-demand skills.', 'points' => 25, 'sort_order' => 1, 'effect_data' => ['balance_change' => -30000]]);
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n3->id, 'label' => 'Start with free online courses first', 'description' => 'Explore before committing.', 'points' => 15, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n4->id, 'label' => 'Save the money — buy a phone instead', 'description' => 'The bootcamp can wait.', 'points' => 5, 'sort_order' => 3, 'effect_data' => ['balance_change' => -30000]]);

        // From n2
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n5->id, 'label' => 'Pull an all-nighter and build something original', 'description' => 'Hard work and integrity.', 'points' => 35, 'sort_order' => 1, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n7->id, 'label' => 'Go along with copying the GitHub project', 'description' => 'The shortcut looks tempting.', 'points' => -10, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n4->id, 'label' => 'Quit — it is too intense', 'description' => 'The money spent cannot come back.', 'points' => 0, 'sort_order' => 3, 'effect_data' => ['balance_change' => 0]]);

        // From n3
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n5->id, 'label' => 'Pay for the KSh 6,000 certification exam', 'description' => 'Validate your skills and stand out.', 'points' => 30, 'sort_order' => 1, 'effect_data' => ['balance_change' => -6000]]);
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n6->id, 'label' => 'Keep learning free content — no rush', 'description' => 'Knowledge without credentials.', 'points' => 10, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORY 2: The Business Diploma (18-25 → Business career, 42k/mo)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedBusinessDiploma(): void
    {
        $story = Story::create([
            'title'       => 'The Business Diploma',
            'description' => 'Shawn, 24, has KSh 60,000 saved. He must choose between a business diploma, a chama investment, or starting a shop now. The wrong move could wipe him out.',
            'age_group'   => '18-25',
            'is_active'   => true,
            'sort_order'  => 11,
        ]);

        $n1 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Three Paths, One Savings Account',
            'scenario_text' => "Shawn, 24, has KSh 60,000 in his M-Pesa account. Three people give him advice the same week:\n\n🎓 His aunt: \"Take the 6-month Business Management diploma — KSh 35,000. You'll graduate with real credentials.\"\n\n📈 His friend: \"Join our chama. We're buying land. Put in KSh 30,000.\"\n\n🛒 His uncle: \"Forget school. Open a hardware shop now. I'll mentor you.\"",
            'type'     => 'scenario',
            'metadata' => ['market_event' => 'Kenya has 7.4 million MSMEs. Those with business training are 2.7× more likely to survive past year 3.'],
        ]);

        $n2 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Diploma Month 4: The Internship Offer',
            'scenario_text' => "Month 4 of the diploma. A mobile money company offers Shawn a paid internship — KSh 15,000/month — starting immediately. His diploma supervisor says: \"Finish strong. Don't leave early.\"\n\nThe internship is 3 months, which overlaps with the final exams. He can't do both without risking failure.",
            'type'     => 'scenario',
            'metadata' => null,
        ]);

        $n3 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Chama Cracks',
            'scenario_text' => "Month 3. The chama's land deal falls through — the land had a title dispute. Half the members want out. The treasurer has \"gone quiet.\"\n\nShawn's KSh 30,000 is stuck. A lawyer says recovering it will cost KSh 15,000 in legal fees and take 6 months.\n\nHis remaining KSh 30,000 is sitting in M-Pesa. He needs a plan.",
            'type'     => 'scenario',
            'metadata' => ['market_event' => 'Land disputes are Kenya\'s #1 cause of chama losses. Always verify title deeds before investing.'],
        ]);

        $n4 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Shop — Year One',
            'scenario_text' => "The hardware shop opens. Month 1 is exciting. Month 3, a competitor opens 50 metres away. Month 5, Shawn realises he doesn't know how to price for profit — he's been pricing for turnover.\n\nHis uncle suggests: \"Take a short business course — KSh 8,000. Fix your pricing.\"\n\nShawn has KSh 12,000 left in the business account.",
            'type'     => 'scenario',
            'metadata' => null,
        ]);

        $n5 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Sales Executive — Career Unlocked',
            'scenario_text' => "Shawn completes the diploma with a distinction. A fintech company recruits him directly from his graduation ceremony.\n\nRole: Sales Executive — KSh 42,000/month base + commission.\n\nHis diploma cost KSh 35,000. His first month's salary: KSh 42,000. ROI: month one.\n\n🎯 Career unlocked: Business",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'business',
                'career_title'         => 'Sales Executive',
                'career_income'        => 42000,
                'final_lesson'         => 'Business education is not a cost — it is a multiplier. The right knowledge compounds every shilling you earn.',
            ],
        ]);

        $n6 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Intern Who Never Graduated',
            'scenario_text' => "Shawn takes the internship and fails two final exams. Without the diploma, the internship does not lead to a permanent role. He is back to job-hunting with no credential.\n\nSix months later, his friend from the diploma programme is earning KSh 42,000/month. Shawn is applying for data entry jobs at KSh 20,000.",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'  => 'lesson',
                'final_lesson' => 'Short-term income can be the enemy of long-term wealth. Finish what you start — especially when the finish line is close.',
            ],
        ]);

        $n7 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Hard Lessons, Strong Foundation',
            'scenario_text' => "Shawn recovers KSh 18,000 from the chama after legal fees. He uses the remaining KSh 12,000 to enrol in an intensive 2-month business course.\n\nThe experience taught him: verify everything, diversify, never put all savings in one investment. He starts freelancing in business consulting — small, but growing.\n\n🎯 Career unlocked: Business (Consultant Track)",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'business',
                'career_title'         => 'Business Consultant',
                'career_income'        => 35000,
                'final_lesson'         => 'Loss is tuition. The students who learn from losses — and still choose to invest wisely afterward — are the ones who build lasting wealth.',
            ],
        ]);

        $n8 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Course That Turned a Loss Into a Win',
            'scenario_text' => "Shawn pays KSh 8,000 for the pricing course. He reprices everything. Within 60 days, the shop is profitable.\n\nHe starts teaching other shop owners in the market — informally at first, then for a fee. His business education, however small, is now generating income.\n\n🎯 Career unlocked: Business",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'business',
                'career_title'         => 'Entrepreneur',
                'career_income'        => 38000,
                'final_lesson'         => 'The best time to invest in business knowledge is before you need it. The second best time is right now.',
            ],
        ]);

        $n9 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Out of Business in Year Two',
            'scenario_text' => "Without fixing the pricing, the shop bleeds cash. Shawn closes it 14 months after opening.\n\nHis entire KSh 60,000 is gone. He starts over with zero savings, but with a clear lesson: knowledge is not optional in business.",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'  => 'lesson',
                'final_lesson' => 'Running a business without financial literacy is like driving with no headlights. You might go far on a clear road — but one bend in the dark and it is over.',
            ],
        ]);

        // Choices from n1
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n2->id, 'label' => 'Take the Business Management diploma (KSh 35,000)', 'description' => 'Formal education with credentials.', 'points' => 30, 'sort_order' => 1, 'effect_data' => ['balance_change' => -35000]]);
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n3->id, 'label' => 'Invest KSh 30,000 in the chama land deal', 'description' => 'Higher potential returns, higher risk.', 'points' => 10, 'sort_order' => 2, 'effect_data' => ['balance_change' => -30000]]);
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n4->id, 'label' => 'Open the hardware shop now', 'description' => 'Learn by doing.', 'points' => 15, 'sort_order' => 3, 'effect_data' => ['balance_change' => -40000]]);

        // Choices from n2
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n5->id, 'label' => 'Decline the internship and finish the diploma', 'description' => 'Long-term thinking.', 'points' => 35, 'sort_order' => 1, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n6->id, 'label' => 'Take the internship (KSh 15,000/month now)', 'description' => 'Income today vs. qualification tomorrow.', 'points' => 5, 'sort_order' => 2, 'effect_data' => ['balance_change' => 15000]]);

        // Choices from n3
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n7->id, 'label' => 'Pay the lawyer and recover what you can, then pivot', 'description' => 'Cut losses and rebuild.', 'points' => 25, 'sort_order' => 1, 'effect_data' => ['balance_change' => -15000]]);
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n6->id, 'label' => 'Do nothing — maybe the treasurer will come back', 'description' => 'Hope is not a strategy.', 'points' => 0, 'sort_order' => 2, 'effect_data' => ['balance_change' => -30000]]);

        // Choices from n4
        Choice::create(['node_id' => $n4->id, 'next_node_id' => $n8->id, 'label' => 'Take the KSh 8,000 pricing course', 'description' => 'Invest in knowledge to save the business.', 'points' => 30, 'sort_order' => 1, 'effect_data' => ['balance_change' => -8000]]);
        Choice::create(['node_id' => $n4->id, 'next_node_id' => $n9->id, 'label' => 'Skip the course — keep operating as-is', 'description' => 'Survival mode without a plan.', 'points' => 5, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORY 3: The Healthcare Path (13-17 → Healthcare career, 48k/mo)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedHealthcarePath(): void
    {
        $story = Story::create([
            'title'       => 'The Nurse\'s Journey',
            'description' => 'Zawadi, 16, dreams of becoming a nurse. She must make saving and spending decisions today that will fund her nursing college in two years.',
            'age_group'   => '13-17',
            'is_active'   => true,
            'sort_order'  => 12,
        ]);

        $n1 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Dream Board',
            'scenario_text' => "Zawadi, 16, pins a photo of a nurse in uniform above her bed. Nursing college fees: KSh 120,000 over 3 years.\n\nShe currently earns KSh 2,000/month from selling chapati at school. Her parents can contribute KSh 1,500/month.\n\nHer science teacher suggests a HELB loan later, but \"later\" is not a plan.",
            'type'     => 'scenario',
            'metadata' => ['market_event' => 'Kenya needs 100,000 more nurses by 2030. Starting early puts you in a rare group.'],
        ]);

        $n2 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The SACCO Invitation',
            'scenario_text' => "Zawadi's mother is a member of a teachers\' SACCO. She can add Zawadi as a junior member — deposits as low as KSh 500/month earn 12% annual interest and can be withdrawn after 2 years.\n\nAlternatively, Zawadi can open a Stawi Youth M-Pesa savings goal.\n\nHer friend says: \"Just keep it in M-Pesa, it's easier.\"",
            'type'     => 'scenario',
            'metadata' => ['market_event' => 'SACCOs pay 10-14% dividend annually. M-Pesa savings earn near-zero interest.'],
        ]);

        $n3 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Form 4 Results & The Scholarship',
            'scenario_text' => "Zawadi passes Form 4 with a C+ — not eligible for free university, but enough for nursing college. She has saved KSh 34,000 over two years.\n\nA small NGO offers a partial scholarship (KSh 40,000) for community health nursing — a slightly different path from hospital nursing.\n\nThe HELB loan can cover KSh 50,000. She needs to decide which path to take.",
            'type'     => 'scenario',
            'metadata' => null,
        ]);

        $n4 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Spent Before She Could Save',
            'scenario_text' => "Without a savings plan, Zawadi's KSh 2,000/month slowly disappears — new clothes, mobile data, food. By Form 4, she has KSh 4,000.\n\nNursing college requires a KSh 30,000 deposit. She cannot enroll. She takes a shop attendant job at KSh 12,000/month while planning to try again next year.",
            'type'     => 'result',
            'metadata' => ['final_lesson' => 'A goal without a savings plan is just a wish. KSh 500/month invested for 2 years = over KSh 13,000 with interest — enough to change your trajectory.'],
        ]);

        $n5 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Registered Nurse — Career Unlocked',
            'scenario_text' => "Three years later. Zawadi graduates and lands a position at a Level 4 hospital.\n\nMonthly salary: KSh 48,000. Her HELB loan repayments: KSh 2,500/month.\n\nThe chapati savings, the SACCO discipline, the HELB planning — every piece clicked into place.\n\n🎯 Career unlocked: Healthcare",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'healthcare',
                'career_title'         => 'Registered Nurse',
                'career_income'        => 48000,
                'final_lesson'         => 'Dreams are funded by small, consistent choices. KSh 500 a month from age 16 can open doors that take others decades to reach.',
            ],
        ]);

        $n6 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Community Health Officer — Alternative Path',
            'scenario_text' => "Zawadi takes the NGO scholarship and trains as a Community Health Officer. The work is deeply meaningful — rural clinics, maternal care, health education.\n\nHer salary is KSh 35,000/month, lower than hospital nursing but with housing provided.\n\n🎯 Career unlocked: Healthcare (Community Track)",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'healthcare',
                'career_title'         => 'Community Health Officer',
                'career_income'        => 35000,
                'final_lesson'         => 'There is rarely one path to a career. Sometimes the scenic route — the one others overlook — delivers more purpose and still pays the bills.',
            ],
        ]);

        // Choices from n1
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n2->id, 'label' => 'Start saving KSh 1,000/month with a real goal plan', 'description' => 'Discipline now for freedom later.', 'points' => 30, 'sort_order' => 1, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n4->id, 'label' => 'Wait — nursing school is still two years away', 'description' => 'Delay the plan until it feels more urgent.', 'points' => 5, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);

        // Choices from n2
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n3->id, 'label' => 'Join the SACCO — KSh 500/month for 12% returns', 'description' => 'Patient compound growth.', 'points' => 35, 'sort_order' => 1, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n3->id, 'label' => 'Open a Stawi Youth savings goal on M-Pesa', 'description' => 'Accessible but lower returns.', 'points' => 25, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n4->id, 'label' => 'Keep it in M-Pesa as normal — easier to access', 'description' => 'Convenience over discipline.', 'points' => 5, 'sort_order' => 3, 'effect_data' => ['balance_change' => 0]]);

        // Choices from n3
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n5->id, 'label' => 'Take the HELB loan and enrol in hospital nursing', 'description' => 'Higher income potential, manageable debt.', 'points' => 35, 'sort_order' => 1, 'effect_data' => ['balance_change' => 50000]]);
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n6->id, 'label' => 'Accept the NGO scholarship — community nursing', 'description' => 'Lower debt, meaningful work.', 'points' => 30, 'sort_order' => 2, 'effect_data' => ['balance_change' => 40000]]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORY 4: The Green Economy + Finance Track (26+ → Agriculture + Finance)
    // ─────────────────────────────────────────────────────────────────────────
    private function seedAgriFinance(): void
    {
        $story = Story::create([
            'title'       => 'The Land or the Ledger',
            'description' => 'Mama Njeri, 34, must choose between an agribusiness extension training and a microfinance accounting certificate. One leads to the farm, one to the bank — both can change her family\'s future.',
            'age_group'   => '26+',
            'is_active'   => true,
            'sort_order'  => 13,
        ]);

        $n1 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Two Paths, One Season',
            'scenario_text' => "Mama Njeri, 34, has managed her family's half-acre farm for 5 years. She has KSh 28,000 saved from selling vegetables at the local market.\n\nTwo opportunities arrive:\n\n🌾 A 3-month Agribusiness Extension Training at a nearby agricultural college — KSh 18,000.\n\n🏦 A 4-month Microfinance Accounting Certificate (online) — KSh 12,000.\n\nHer children's school fees are due in 6 months: KSh 22,000.",
            'type'     => 'scenario',
            'metadata' => ['market_event' => 'Agri-trained smallholders earn 40% more per acre. Microfinance is Kenya\'s fastest-growing employment sector.'],
        ]);

        $n2 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Extension Training Midway',
            'scenario_text' => "Week 5 of the agribusiness training. Mama Njeri learns about greenhouse farming, drip irrigation, and selling directly to supermarkets instead of brokers.\n\nA training grant is available for women farmers: KSh 15,000 to implement what they've learned. But it requires submitting a 1-page business plan within the next week.",
            'type'     => 'scenario',
            'metadata' => null,
        ]);

        $n3 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Accounting Certificate: Final Assessment',
            'scenario_text' => "Month 3. Mama Njeri passes all her online modules. She applies to a local microfinance institution.\n\nThe HR officer says: \"We're looking for someone who also has basic customer service experience. Do you have anything?\"\n\nShe has been managing market customers for 5 years. She just needs to present it right on her CV.",
            'type'     => 'scenario',
            'metadata' => null,
        ]);

        $n4 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Waiting for "The Right Time"',
            'scenario_text' => "Mama Njeri waits. The agri-training cohort fills up. The certificate programme deadline passes.\n\nAnother season comes and goes. Her farm income stays the same. Her neighbour, who took the training, now supplies tomatoes to a Nairobi supermarket chain.",
            'type'     => 'result',
            'metadata' => ['final_lesson' => 'The right time to invest in yourself was last year. The second best time is today. Waiting for certainty is its own kind of risk.'],
        ]);

        $n5 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Agri Farm Manager — Career Unlocked',
            'scenario_text' => "Mama Njeri submits the business plan and wins the KSh 15,000 grant. She installs drip irrigation and signs a supply contract with a hotel chain.\n\nA year later, the agri-college hires her as a part-time extension trainer — sharing what she learned with other small farmers.\n\nMonthly income: KSh 40,000 from produce + KSh 8,000 from training fees.\n\n🎯 Career unlocked: Agriculture",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'agriculture',
                'career_title'         => 'Agri Farm Manager',
                'career_income'        => 40000,
                'final_lesson'         => 'Training multiplies land. The same half-acre, with the right knowledge and connections, can generate 5× the income it did before.',
            ],
        ]);

        $n6 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Good Training, Missed Grant',
            'scenario_text' => "Mama Njeri skips the grant application — the business plan feels too complicated. She graduates the training but implements changes slowly.\n\nTwo years later, she is earning better, but not as much as her classmates who applied for the grant and scaled faster.",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'lesson',
                'career_field_unlocked' => 'agriculture',
                'career_title'         => 'Market Farmer',
                'career_income'        => 32000,
                'final_lesson'         => 'Opportunities have expiry dates. Training without implementation is just a certificate on the wall. When a door opens — walk through it.',
            ],
        ]);

        $n7 = Node::create([
            'story_id' => $story->id,
            'title'    => 'Microfinance Officer — Career Unlocked',
            'scenario_text' => "Mama Njeri lists her market experience as \"customer relationship management\" and \"cash reconciliation\" on her CV.\n\nShe gets the job. Starting salary: KSh 35,000 + performance bonuses.\n\nBy month 6, she is the top-performing loan officer in the branch.\n\n🎯 Career unlocked: Finance",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'          => 'great',
                'career_field_unlocked' => 'finance',
                'career_title'         => 'Microfinance Officer',
                'career_income'        => 35000,
                'final_lesson'         => 'Your informal experience is real experience. Market traders know cash flow, risk, and customer behaviour better than most MBA graduates. Learn to translate what you already know.',
            ],
        ]);

        $n8 = Node::create([
            'story_id' => $story->id,
            'title'    => 'The Missed Opportunity',
            'scenario_text' => "Mama Njeri undersells herself in the interview. She gets a data-entry role at KSh 18,000 instead.\n\nShe takes it — any step forward is a step. But she knows she left value on the table by not framing her experience correctly.",
            'type'     => 'ending',
            'metadata' => [
                'ending_type'  => 'lesson',
                'final_lesson' => 'How you tell your story matters as much as the story itself. In job interviews, informal experience must be translated into professional language — or it will be overlooked.',
            ],
        ]);

        // Choices from n1
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n2->id, 'label' => 'Take the Agribusiness Extension Training (KSh 18,000)', 'description' => 'Invest in what you already know.', 'points' => 30, 'sort_order' => 1, 'effect_data' => ['balance_change' => -18000]]);
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n3->id, 'label' => 'Take the Microfinance Accounting Certificate (KSh 12,000)', 'description' => 'Pivot to a new sector.', 'points' => 25, 'sort_order' => 2, 'effect_data' => ['balance_change' => -12000]]);
        Choice::create(['node_id' => $n1->id, 'next_node_id' => $n4->id, 'label' => 'Save everything for school fees — training can wait', 'description' => 'Family first, self-investment second.', 'points' => 10, 'sort_order' => 3, 'effect_data' => ['balance_change' => 0]]);

        // Choices from n2
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n5->id, 'label' => 'Write the business plan and apply for the grant', 'description' => 'Extra effort for extra opportunity.', 'points' => 40, 'sort_order' => 1, 'effect_data' => ['balance_change' => 15000]]);
        Choice::create(['node_id' => $n2->id, 'next_node_id' => $n6->id, 'label' => 'Skip the grant — the business plan is too complicated', 'description' => 'Easier path, smaller outcome.', 'points' => 15, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);

        // Choices from n3
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n7->id, 'label' => 'Reframe your market experience as professional skills on the CV', 'description' => 'Own your expertise.', 'points' => 35, 'sort_order' => 1, 'effect_data' => ['balance_change' => 0]]);
        Choice::create(['node_id' => $n3->id, 'next_node_id' => $n8->id, 'label' => 'Mention the market work briefly — it probably doesn\'t count', 'description' => 'Undersell your background.', 'points' => 10, 'sort_order' => 2, 'effect_data' => ['balance_change' => 0]]);
    }
}
