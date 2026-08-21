<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Node;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * CareerEventExpansionSeeder — CareerEventSeeder left min_chapter 5 (Builder,
 * net worth 5M+) and 6 (Elder, net worth 20M+) completely empty across all 8
 * career tracks, and chapter 4 (Settler, 1M+) with only 2 scenarios total.
 * A player who actually builds serious net worth ran out of career-track
 * content precisely when they reached the wealthiest stages of the game.
 *
 * These 22 scenarios give every track at least one scenario at chapters 4,
 * 5 and 6, scaled to the money stakes appropriate for each net-worth tier
 * (tens of thousands at chapter 4, hundreds of thousands at 5, six figures
 * to low millions at 6 — legacy-scale decisions for an "elder" player).
 */
class CareerEventExpansionSeeder extends Seeder
{
    private const SCENARIOS = [

        // ══════════════════════════════════
        //  CHAPTER 4 — SETTLER (net worth 1M+)
        // ══════════════════════════════════
        [
            'title'         => 'Scaling the Dev Shop',
            'scenario_text' => 'You have been freelancing for years and now have more client work than you can handle alone. A junior developer wants to join as your first hire at KES 45,000/month, freeing you to take on bigger contracts.',
            'icon'          => '👨‍💻',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'technology', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Hire the junior developer', 'description' => 'Take on payroll and free yourself to chase bigger contracts.', 'balance_change' => -45000, 'points' => 350, 'icon' => '🧑‍💻', 'lesson' => 'Your first hire is the moment a freelance hustle becomes a real business — the cost only pays off if it actually frees up billable hours you can sell at a higher rate.'],
                ['label' => 'Stay solo, turn down bigger contracts', 'description' => 'Keep full control and no payroll responsibility.', 'balance_change' => 0, 'points' => 150, 'icon' => '🙅', 'lesson' => 'Staying solo caps your income at what one person can bill in a month — sometimes the safer choice, but it is a ceiling you are choosing on purpose.'],
            ],
        ],
        [
            'title'         => 'Second Branch Decision',
            'scenario_text' => 'Your shop is thriving in one location. A second location in a growing estate is available for KES 80,000 in deposit and stock.',
            'icon'          => '🏪',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'business', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Open the second branch', 'description' => 'Replicate the model in a new, growing area.', 'balance_change' => -80000, 'points' => 350, 'icon' => '🏗️', 'lesson' => 'Replicating a proven business model is lower-risk than starting from scratch — but a second location means double the operating headaches, not double the free time.'],
                ['label' => 'Reinvest in the existing branch instead', 'description' => 'Deepen stock and marketing at the location you already know works.', 'balance_change' => -30000, 'points' => 200, 'icon' => '📦', 'lesson' => 'Deepening one location\'s stock and marketing is a safer bet than expanding before you have fully proven the model works twice.'],
            ],
        ],
        [
            'title'         => 'Private Clinic Equipment Upgrade',
            'scenario_text' => 'As a clinician with a growing private practice, a digital diagnostic machine (KES 120,000) would let you handle more patients per day and stop referring cases elsewhere.',
            'icon'          => '🩺',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'healthcare', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Buy the equipment on a payment plan', 'description' => 'Spread the KES 120,000 cost while the machine pays for itself.', 'balance_change' => -40000, 'points' => 300, 'icon' => '💳', 'lesson' => 'Spreading a big equipment cost across a payment plan protects your cash flow, as long as the extra patients it lets you see cover the installments.'],
                ['label' => 'Keep referring those cases out', 'description' => 'No new cost, but no new capacity either.', 'balance_change' => 0, 'points' => 100, 'icon' => '📋', 'lesson' => 'Referring cases costs you nothing upfront, but it caps your practice\'s growth and sends paying patients to someone else\'s clinic.'],
            ],
        ],
        [
            'title'         => 'Signing With a Talent Agency',
            'scenario_text' => 'A talent agency offers to represent you and negotiate bigger brand deals — for a 20% commission on everything they book.',
            'icon'          => '🎬',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'creative', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Sign with the agency', 'description' => 'Trade a commission for access to bigger deals than you could land alone.', 'balance_change' => 0, 'points' => 300, 'icon' => '✍️', 'lesson' => 'Paying a commission for access to deals you could not land alone is a trade, not a loss — the math only works if the deals they bring are genuinely bigger than what you would get solo.'],
                ['label' => 'Keep negotiating your own deals', 'description' => 'Keep 100% of smaller deals rather than 80% of bigger ones.', 'balance_change' => 0, 'points' => 150, 'icon' => '💬', 'lesson' => 'Keeping 100% of smaller deals can beat 80% of bigger ones, depending on how much bigger "bigger" really is — do the math before deciding on principle alone.'],
            ],
        ],
        [
            'title'         => 'CFA Certification Investment',
            'scenario_text' => 'Enrolling in a CFA (Chartered Financial Analyst) program costs KES 180,000 over the program but significantly raises your ceiling in financial services roles.',
            'icon'          => '📜',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'finance', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Enroll and pay in installments', 'description' => 'A globally recognized certification for your career ceiling.', 'balance_change' => -60000, 'points' => 400, 'icon' => '🎓', 'lesson' => 'A globally recognized certification is one of the few expenses that reliably raises your income ceiling for the rest of your career — but only if you finish it.'],
                ['label' => 'Skip it, focus on work experience instead', 'description' => 'Bet on hands-on experience over paper qualifications.', 'balance_change' => 0, 'points' => 150, 'icon' => '💼', 'lesson' => 'Experience alone can get you far, but in finance, credentials often decide who gets considered for the senior roles at all.'],
            ],
        ],
        [
            'title'         => 'Opening a Tuition Center',
            'scenario_text' => 'You have built a reputation as a tutor. Renting a small space and hiring one assistant tutor to open a formal tuition center costs KES 60,000 to set up.',
            'icon'          => '🏫',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'education', 'min_chapter' => 4],
            'choices'       => [
                ['label' => 'Open the tuition center', 'description' => 'Hire an assistant tutor and stop trading only your own hours.', 'balance_change' => -60000, 'points' => 350, 'icon' => '📚', 'lesson' => 'Turning personal tutoring into a center with staff is how a skill-based income stops being capped by your own hours in the day.'],
                ['label' => 'Stay independent, keep tutoring one-on-one', 'description' => 'Lower overhead, no staff to manage.', 'balance_change' => 0, 'points' => 150, 'icon' => '🧑‍🏫', 'lesson' => 'One-on-one tutoring has lower overhead and no staff to manage — a deliberately smaller, simpler business is a legitimate choice.'],
            ],
        ],

        // ══════════════════════════════════
        //  CHAPTER 5 — BUILDER (net worth 5M+)
        // ══════════════════════════════════
        [
            'title'         => 'Angel Investment Offer',
            'scenario_text' => 'A former client wants you to angel-invest KES 500,000 into their logistics startup in exchange for 8% equity.',
            'icon'          => '🚀',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'technology', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Invest the KES 500,000', 'description' => 'Take an 8% stake in a startup you have some inside knowledge of.', 'balance_change' => -500000, 'points' => 450, 'icon' => '💰', 'lesson' => 'Angel investing can pay off enormously, but most startups fail — only invest money you could genuinely afford to lose entirely.'],
                ['label' => 'Decline, keep the capital liquid', 'description' => 'Preserve flexibility over a single high-risk bet.', 'balance_change' => 0, 'points' => 150, 'icon' => '🏦', 'lesson' => 'Turning down a single opportunity because it does not fit your risk tolerance is not fear — it is discipline, as long as you are saying yes to something else with that capital.'],
            ],
        ],
        [
            'title'         => 'Franchise Expansion Offer',
            'scenario_text' => 'A regional retail chain wants to franchise your business concept — for a KES 300,000 franchise fee, they handle three new locations, and you get 5% of their revenue.',
            'icon'          => '🏬',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'business', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Accept the franchise deal', 'description' => 'Let someone else run three new sites for a revenue share.', 'balance_change' => -300000, 'points' => 450, 'icon' => '🤝', 'lesson' => 'Franchising trades a big share of upside for someone else doing the operational heavy lifting — a real way to scale without personally running every new location.'],
                ['label' => 'Decline and expand the business yourself', 'description' => 'Keep full control and full profit, and full workload.', 'balance_change' => -150000, 'points' => 350, 'icon' => '🏗️', 'lesson' => 'Keeping full control and full profit means also carrying the full operational burden of every new site — growth by your own hands is slower but keeps more of the reward.'],
            ],
        ],
        [
            'title'         => 'Opening a Second Clinic Branch',
            'scenario_text' => 'Your private clinic is at capacity. Opening a second branch across town costs KES 800,000 for licensing, equipment and staff.',
            'icon'          => '🏥',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'healthcare', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Open the second clinic', 'description' => 'Real capital for real growth in a licensing-heavy field.', 'balance_change' => -800000, 'points' => 500, 'icon' => '🏨', 'lesson' => 'Healthcare businesses scale slower than most because licensing and equipment costs are real and non-negotiable — but demand for care rarely disappears.'],
                ['label' => 'Hire more staff at the current clinic instead', 'description' => 'A cheaper way to raise capacity at one site.', 'balance_change' => -200000, 'points' => 250, 'icon' => '👩‍⚕️', 'lesson' => 'Increasing capacity at one location is usually cheaper and lower-risk than opening a second site from scratch.'],
            ],
        ],
        [
            'title'         => 'Cold Storage Investment',
            'scenario_text' => 'Post-harvest losses are eating your farm\'s margins. A cold storage unit costs KES 600,000 but would cut spoilage losses by half.',
            'icon'          => '🧊',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'agriculture', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Build the cold storage unit', 'description' => 'Real infrastructure against a real, recurring loss.', 'balance_change' => -600000, 'points' => 450, 'icon' => '🏭', 'lesson' => 'Post-harvest loss is one of the biggest hidden costs in farming — infrastructure that reduces spoilage often pays for itself within a few seasons.'],
                ['label' => 'Keep selling immediately at harvest', 'description' => 'Avoid the storage cost, accept the spoilage losses.', 'balance_change' => 0, 'points' => 150, 'icon' => '🌾', 'lesson' => 'Selling everything fast avoids storage costs but forces you to accept whatever price the market offers at harvest time, when supply — and your bargaining power — is at its weakest.'],
            ],
        ],
        [
            'title'         => 'Launching a Small Investment Advisory',
            'scenario_text' => 'With your track record, clients want to pay you to manage small portfolios. Getting licensed and insured costs KES 250,000 but opens a management-fee income stream.',
            'icon'          => '📈',
            'metadata'      => ['event_type' => 'career', 'career_track' => 'finance', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Get licensed and launch the advisory', 'description' => 'Pay upfront for the legal right to manage others\' money.', 'balance_change' => -250000, 'points' => 450, 'icon' => '🪪', 'lesson' => 'Managing other people\'s money legally requires licensing for a reason — the upfront cost buys you the credibility and legal protection to actually charge for it.'],
                ['label' => 'Keep giving informal advice for free', 'description' => 'No cost, but no income and real liability if it goes wrong.', 'balance_change' => 0, 'points' => 100, 'icon' => '💬', 'lesson' => 'Unlicensed financial advice, even well-meant, carries real legal and reputational risk if a client loses money and blames you.'],
            ],
        ],
        [
            'title'         => 'Publishing a Study Curriculum',
            'scenario_text' => 'A publisher offers to turn your tutoring materials into a printed revision series — you cover KES 200,000 in production costs but keep royalties on every copy sold.',
            'icon'          => '📗',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'education', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Fund the production run', 'description' => 'Turn your material into a product you sell forever.', 'balance_change' => -200000, 'points' => 450, 'icon' => '🖨️', 'lesson' => 'Turning expertise into a product you sell repeatedly (a book, a course) is how teaching income stops being capped by hours spent in a room.'],
                ['label' => 'Decline and stick to in-person teaching', 'description' => 'Zero production risk, but no scale beyond your own hours.', 'balance_change' => 0, 'points' => 150, 'icon' => '🧑‍🏫', 'lesson' => 'In-person teaching is reliable income with zero production risk — but it never scales beyond the hours in your day.'],
            ],
        ],
        [
            'title'         => 'Bidding on a County Infrastructure Tender',
            'scenario_text' => 'Your engineering firm can bid on a county road-repair tender worth KES 15,000,000 — but the bid bond alone costs KES 400,000, non-refundable if you lose.',
            'icon'          => '🚧',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'engineering', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Submit the bid', 'description' => 'Risk the bond for a shot at a transformative contract.', 'balance_change' => -400000, 'points' => 500, 'icon' => '📐', 'lesson' => 'Government tenders can be transformative for a firm\'s revenue, but bid bonds are a real, sunk cost — only bid on tenders you have a genuine, realistic chance of winning.'],
                ['label' => 'Skip this tender, stick to private contracts', 'description' => 'Smaller but faster and lower-risk.', 'balance_change' => 0, 'points' => 150, 'icon' => '🏗️', 'lesson' => 'Private contracts are smaller but faster-paying and carry none of a public tender\'s bidding risk or payment delays.'],
            ],
        ],
        [
            'title'         => 'Producing Your Own Show',
            'scenario_text' => 'Instead of waiting for brand deals, you could self-fund a KES 700,000 original series and own 100% of the resulting IP and revenue.',
            'icon'          => '🎥',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'creative', 'min_chapter' => 5],
            'choices'       => [
                ['label' => 'Self-fund the series', 'description' => 'Own everything the show ever earns, but absorb all the risk.', 'balance_change' => -700000, 'points' => 500, 'icon' => '🎬', 'lesson' => 'Owning your own intellectual property means all future revenue is yours — but it also means you personally absorb all the production risk if it does not land.'],
                ['label' => 'Keep taking brand-sponsored deals instead', 'description' => 'Someone else funds the content; you never own it.', 'balance_change' => 0, 'points' => 150, 'icon' => '🤝', 'lesson' => 'Brand deals are lower-risk income because someone else is paying for the content upfront — the trade-off is you never own what you make.'],
            ],
        ],

        // ══════════════════════════════════
        //  CHAPTER 6 — ELDER (net worth 20M+, legacy-scale decisions)
        // ══════════════════════════════════
        [
            'title'         => 'Exiting the Company',
            'scenario_text' => 'A larger firm offers to acquire your tech company for KES 12,000,000. Staying independent might be worth more long-term, but selling locks in certainty now.',
            'icon'          => '🤝',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'technology', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Accept the acquisition', 'description' => 'Convert years of uncertain future earnings into one certain sum.', 'balance_change' => 12000000, 'points' => 600, 'icon' => '💵', 'lesson' => 'An exit converts years of uncertain future earnings into one certain, spendable sum today — a legitimate strategy, especially once you have already proven the model works.'],
                ['label' => 'Decline and keep building independently', 'description' => 'Bet on your own future execution over a buyer\'s offer.', 'balance_change' => 0, 'points' => 300, 'icon' => '🏢', 'lesson' => 'Turning down a buyout is a bet that your own future execution will beat what someone else is offering to pay you right now — sometimes right, sometimes very wrong.'],
            ],
        ],
        [
            'title'         => 'Passing the Business to the Next Generation',
            'scenario_text' => 'Your business is thriving. Setting up a formal succession plan and trust costs KES 300,000 in legal fees but protects it for your children.',
            'icon'          => '👨‍👩‍👧‍👦',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'business', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Set up the succession plan', 'description' => 'Legal structure that outlives you.', 'balance_change' => -300000, 'points' => 500, 'icon' => '📜', 'lesson' => 'A business without a succession plan often collapses or gets fought over the moment its founder steps back — legal structure is how wealth actually survives a generation.'],
                ['label' => 'Leave it informal for now', 'description' => 'Save the fee, defer the risk.', 'balance_change' => 0, 'points' => 100, 'icon' => '🤷', 'lesson' => 'Skipping succession planning saves money today but risks family disputes and even business collapse later — it is a cost deferred, not avoided.'],
            ],
        ],
        [
            'title'         => 'Funding a Community Health Fund',
            'scenario_text' => 'With your practice established, you could set up a KES 1,000,000 endowment fund that subsidizes care for patients who cannot pay.',
            'icon'          => '❤️‍🩹',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'healthcare', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Fund the community health endowment', 'description' => 'A gift that keeps giving off its own investment returns.', 'balance_change' => -1000000, 'points' => 550, 'icon' => '🏥', 'lesson' => 'A well-structured endowment can keep giving back for years off its investment returns alone — legacy giving that outlives a single donation.'],
                ['label' => 'Keep the capital in your own practice instead', 'description' => 'Grow your own asset rather than a public one.', 'balance_change' => 0, 'points' => 200, 'icon' => '🏦', 'lesson' => 'Reinvesting in your own practice grows your personal asset — a fully reasonable choice, just a different kind of legacy than public giving.'],
            ],
        ],
        [
            'title'         => 'Land Trust for Future Generations',
            'scenario_text' => 'You could place your farmland in a family trust (KES 250,000 legal cost) to prevent it from ever being subdivided and sold off after you are gone.',
            'icon'          => '🌾',
            'metadata'      => ['event_type' => 'cost', 'career_track' => 'agriculture', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Set up the land trust', 'description' => 'Protect the farm from ever being fragmented.', 'balance_change' => -250000, 'points' => 500, 'icon' => '🔒', 'lesson' => 'Kenyan farmland gets fragmented across generations through subdivision — a trust is one of the few tools that actually prevents that and keeps a farm viable long-term.'],
                ['label' => 'Leave the land as a simple inheritance', 'description' => 'Cheaper and simpler now, riskier for the land later.', 'balance_change' => 0, 'points' => 150, 'icon' => '📄', 'lesson' => 'A simple inheritance is cheaper and simpler now, but land split among multiple heirs often becomes too small per person to farm profitably.'],
            ],
        ],
        [
            'title'         => 'Establishing a Scholarship Fund',
            'scenario_text' => 'You could commit KES 2,000,000 to a scholarship fund for students from your old neighborhood — a permanent legacy, but a large one-time cost.',
            'icon'          => '🎓',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'finance', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Establish the scholarship fund', 'description' => 'A legacy that compounds across generations of students.', 'balance_change' => -2000000, 'points' => 600, 'icon' => '🏆', 'lesson' => 'Large-scale giving is a legitimate use of wealth once your own security is settled — the return is not financial, but the impact compounds across generations of students.'],
                ['label' => 'Keep the capital invested for family wealth instead', 'description' => 'Prioritize your own family\'s long-term security.', 'balance_change' => 0, 'points' => 250, 'icon' => '👨‍👩‍👧', 'lesson' => 'There is no single right answer between giving and keeping — the discipline is making the choice deliberately, not by default.'],
            ],
        ],
        [
            'title'         => 'Building a Community Library',
            'scenario_text' => 'Your tutoring legacy could fund a small community library and study center (KES 1,500,000) in your hometown.',
            'icon'          => '📚',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'education', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Fund the community library', 'description' => 'Shared infrastructure that serves a community for decades.', 'balance_change' => -1500000, 'points' => 550, 'icon' => '🏛️', 'lesson' => 'Physical, shared infrastructure like a library serves a community for decades — a different kind of return than any single investment can offer.'],
                ['label' => 'Set up a smaller scholarship instead', 'description' => 'Reach fewer people, but reach them directly and immediately.', 'balance_change' => -400000, 'points' => 350, 'icon' => '🎓', 'lesson' => 'A scholarship fund reaches fewer people at lower cost than a library, but gets money directly and immediately to individual students.'],
            ],
        ],
        [
            'title'         => 'Mentorship Institute',
            'scenario_text' => 'You could fund a KES 1,200,000 engineering mentorship institute to train the next generation of engineers from your community.',
            'icon'          => '🛠️',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'engineering', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Fund the mentorship institute', 'description' => 'A legacy that keeps generating value long after you stop working.', 'balance_change' => -1200000, 'points' => 550, 'icon' => '🏗️', 'lesson' => 'Passing on hard-won technical skill to the next generation is a legacy that keeps generating value long after you have stopped actively working.'],
                ['label' => 'Take on individual apprentices informally instead', 'description' => 'Far cheaper, reaches fewer people.', 'balance_change' => -100000, 'points' => 250, 'icon' => '🔧', 'lesson' => 'Informal mentorship costs far less and still transfers real skill — just to fewer people than a formal institute could reach.'],
            ],
        ],
        [
            'title'         => 'Archiving and Licensing Your Life\'s Work',
            'scenario_text' => 'A cultural archive wants to formally preserve and license your body of work for KES 900,000 upfront plus ongoing royalties, keeping it accessible for future generations.',
            'icon'          => '🗄️',
            'metadata'      => ['event_type' => 'opportunity', 'career_track' => 'creative', 'min_chapter' => 6],
            'choices'       => [
                ['label' => 'Sign the archiving and licensing deal', 'description' => 'Turn a body of work into a lasting cultural and financial asset.', 'balance_change' => 900000, 'points' => 550, 'icon' => '🏛️', 'lesson' => 'Formally archiving creative work turns a personal body of work into a lasting cultural and financial asset for both you and whoever inherits the rights.'],
                ['label' => 'Keep full informal control of your work instead', 'description' => 'No rights given up, but no formal preservation either.', 'balance_change' => 0, 'points' => 200, 'icon' => '📦', 'lesson' => 'Keeping informal control avoids giving up any rights, but without formal archiving, creative work can simply be lost or forgotten over time.'],
            ],
        ],

    ]; // end SCENARIOS

    public function run(): void
    {
        if (!Schema::hasTable('nodes') || !Schema::hasTable('choices')) {
            $this->command->warn('CareerEventExpansionSeeder: nodes or choices table not found — skipping. Run after migrations.');
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach (self::SCENARIOS as $scenario) {
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
                    'node_id'      => $node->id,
                    'next_node_id' => null,
                    'label'        => $choice['label'],
                    'description'  => $choice['description'],
                    'points'       => $choice['points'] ?? 0,
                    'sort_order'   => $i + 1,
                    'effect_data'  => [
                        'balance_change' => $choice['balance_change'] ?? 0,
                        'icon'           => $choice['icon']   ?? ($choice['balance_change'] >= 0 ? '✅' : '⚠️'),
                        'lesson'         => $choice['lesson'] ?? null,
                    ],
                ]);
            }

            $created++;
        }

        $total = count(self::SCENARIOS);
        $this->command->info("CareerEventExpansionSeeder: {$created} scenarios created, {$skipped} already existed ({$total} total).");
    }
}
