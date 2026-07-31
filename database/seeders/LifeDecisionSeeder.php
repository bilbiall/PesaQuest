<?php

namespace Database\Seeders;

use App\Models\LifeDecision;
use App\Models\LifeDecisionChoice;
use App\Models\Npc;
use Illuminate\Database\Seeder;

class LifeDecisionSeeder extends Seeder
{
    public function run(): void
    {
        $npcs = Npc::pluck('id', 'nickname')->toArray();

        $decisions = [

            // ── SOCIAL: KAMAU ──────────────────────────────────────────────────
            [
                'npc_id'    => $npcs['Kamau'] ?? null,
                'title'     => 'Kamau Needs Ksh 3,000',
                'body'      => 'It\'s the 25th of the month. Kamau texts: "Bro, landlord is on my case. Can you lend me 3k till Friday? I\'ll M-Pesa you back as soon as I get paid." You\'ve heard this before.',
                'image_url' => 'https://loremflickr.com/800/420/mpesa,phone,kenya?lock=401',
                'category'  => 'social',
                'icon'      => '📱',
                'weight'    => 15,
                'min_tick'  => 10,
                'choices'   => [
                    [
                        'label'            => 'Lend Ksh 3,000',
                        'description'      => 'Help him out. He\'s a good friend.',
                        'outcome_text'     => 'You sent Kamau Ksh 3,000 via M-Pesa. He sends back a "❤️ asante sana bro." It\'s now Day 5 after the promised payback date. Your phone is silent.',
                        'financial_lesson' => 'Lending money to friends is a gift in disguise. Only lend what you can afford to lose — and never from your emergency fund. "A loan to a friend makes an enemy." — Kenyan proverb.',
                        'balance_delta'    => -3000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 10,
                        'xp_delta'         => 5,
                    ],
                    [
                        'label'            => 'Decline gently',
                        'description'      => 'Tell him you\'re tight this month.',
                        'outcome_text'     => 'You tell Kamau you\'re also managing a tight month. He goes quiet for a day, then comes back with a "no worries bro, sorted it." Your emergency fund stays intact.',
                        'financial_lesson' => 'Saying no to borrowers — even friends — is a financial boundary, not a betrayal. Protecting your emergency fund means you stay afloat when your own crisis hits.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -5,
                        'xp_delta'         => 10,
                    ],
                    [
                        'label'            => 'Lend Ksh 1,000 only',
                        'description'      => 'Meet halfway — something is better than nothing.',
                        'outcome_text'     => 'You send Ksh 1,000 and explain you\'re managing your own bills. Kamau appreciates it. He pays back Ksh 500 two weeks later. Progress.',
                        'financial_lesson' => 'Partial lending sets a clear boundary while maintaining the friendship. Always lend the amount you\'d be okay not getting back.',
                        'balance_delta'    => -1000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 5,
                        'xp_delta'         => 8,
                    ],
                ],
            ],

            // ── SOCIAL: NJERI ──────────────────────────────────────────────────
            [
                'npc_id'    => $npcs['Njeri'] ?? null,
                'title'     => 'Njeri\'s Birthday Dinner',
                'body'      => 'Njeri is turning 26 and has booked a table at a rooftop restaurant in Westlands. "It\'s my day, no cheapskates!" she posts on the WhatsApp group. The bill will easily be Ksh 4,000–8,000 per person.',
                'image_url' => 'https://loremflickr.com/800/420/restaurant,nairobi,dinner?lock=402',
                'category'  => 'social',
                'icon'      => '🥂',
                'weight'    => 12,
                'min_tick'  => 5,
                'choices'   => [
                    [
                        'label'            => 'Go and enjoy it',
                        'description'      => 'YOLO — it\'s her birthday!',
                        'outcome_text'     => 'A great evening. Cocktails, fancy food, TikToks. The bill came to Ksh 6,500. You pay, smile, and quietly cry when you check your M-Pesa balance on the way home.',
                        'financial_lesson' => 'Social spending is the silent budget killer. Ksh 6,500 at one dinner = 2+ months of electricity. Track lifestyle spend separately from your budget — it\'s where most overspending hides.',
                        'balance_delta'    => -6500,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 15,
                        'xp_delta'         => 5,
                    ],
                    [
                        'label'            => 'Skip it, send a gift',
                        'description'      => 'Send Ksh 1,000 M-Pesa and a heartfelt message.',
                        'outcome_text'     => 'You send Ksh 1,000 and a voice note. Njeri is briefly annoyed but gets over it. Your savings account gains a small win.',
                        'financial_lesson' => 'You don\'t have to attend every event to maintain friendships. Financial boundaries are not social failures. The right friends respect them.',
                        'balance_delta'    => -1000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -8,
                        'xp_delta'         => 12,
                    ],
                    [
                        'label'            => 'Suggest a cheaper plan',
                        'description'      => 'Counter-propose a casual lunch instead.',
                        'outcome_text'     => 'Half the group agrees. You end up at a nyama choma spot — Ksh 1,500 each. Njeri pretends to be upset but admits it was more fun.',
                        'financial_lesson' => 'Proposing cheaper alternatives is a financial superpower. Social experiences don\'t need to be expensive to be memorable. You can lead the culture of your friend group.',
                        'balance_delta'    => -1500,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 5,
                        'xp_delta'         => 15,
                    ],
                ],
            ],

            // ── FAMILY: MAMA ───────────────────────────────────────────────────
            [
                'npc_id'    => $npcs['Mama'] ?? null,
                'title'     => 'Mama Needs School Fees',
                'body'      => 'Your younger sister is about to be sent home from school — Ksh 8,500 balance on fees. Mama calls: "Mtoto ataenda nyumbani. Unaeza nisaidia kidogo?" Her voice carries everything she\'s not saying.',
                'image_url' => 'https://loremflickr.com/800/420/school,kenya,children?lock=403',
                'category'  => 'family',
                'icon'      => '🏫',
                'weight'    => 14,
                'min_tick'  => 15,
                'choices'   => [
                    [
                        'label'            => 'Send the full Ksh 8,500',
                        'description'      => 'Family comes first.',
                        'outcome_text'     => 'You send the full amount. Your sister stays in school. Mama prays for you. Your balance takes a hit, but the peace of mind is real.',
                        'financial_lesson' => 'Family financial obligations are real and valid — but they need to be planned for. A separate "family support" budget line prevents these moments from derailing your entire month.',
                        'balance_delta'    => -8500,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 20,
                        'xp_delta'         => 10,
                    ],
                    [
                        'label'            => 'Send Ksh 4,000 now, rest next week',
                        'description'      => 'Part-pay to keep her in school.',
                        'outcome_text'     => 'The school accepts a partial payment. Your sister attends. You send the balance 8 days later. Mama says "Mungu akubariki." Balance restored gradually.',
                        'financial_lesson' => 'Negotiating payment plans — even with family obligations — is smart cash flow management. Most schools, landlords, and creditors prefer partial payment to nothing.',
                        'balance_delta'    => -4000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 12,
                        'xp_delta'         => 15,
                    ],
                    [
                        'label'            => 'Can\'t help right now',
                        'description'      => 'You\'re stretched thin this month.',
                        'outcome_text'     => 'Your sister stays home for 3 days until Mama arranges the money elsewhere. You feel guilty. Mama doesn\'t say it but the silence is heavy.',
                        'financial_lesson' => 'Sometimes you genuinely cannot help — and that\'s real. The long-term lesson: family obligations are predictable expenses, not surprises. Budget for them monthly so you\'re never in this position.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -15,
                        'xp_delta'         => 5,
                    ],
                ],
            ],

            // ── CAREER: BOSS ───────────────────────────────────────────────────
            [
                'npc_id'    => $npcs['The Boss'] ?? null,
                'title'     => 'Weekend Overtime Offer',
                'body'      => 'Mr. Ochieng pulls you aside on Friday: "We have a deadline for Monday. I need two people to come in Saturday and Sunday. Ksh 4,500 each day. You in?" It\'s your free weekend — you had plans.',
                'image_url' => 'https://loremflickr.com/800/420/office,work,nairobi?lock=404',
                'category'  => 'career',
                'icon'      => '💼',
                'weight'    => 12,
                'min_tick'  => 20,
                'choices'   => [
                    [
                        'label'            => 'Accept both days (Ksh 9,000)',
                        'description'      => 'Extra money AND boss impression.',
                        'outcome_text'     => 'You work the weekend. It\'s exhausting but productive. Mr. Ochieng notices. On Monday he says "Good work." Your bank account grows by Ksh 9,000. Your relationship score with the boss rises.',
                        'financial_lesson' => 'Overtime at the right time can dramatically accelerate savings goals. Ksh 9,000 extra = 3 months of NHIF contributions, or a month toward an emergency fund. Time-value of effort is real.',
                        'balance_delta'    => 9000,
                        'credit_score_delta' => 2,
                        'relationship_delta' => 15,
                        'xp_delta'         => 20,
                    ],
                    [
                        'label'            => 'Decline — you need the rest',
                        'description'      => 'Mental health matters too.',
                        'outcome_text'     => 'You politely decline. You rest, recharge, and come back Monday sharper. Mr. Ochieng notes it but doesn\'t make a scene. Some weeks, rest IS the investment.',
                        'financial_lesson' => 'Burnout is a financial risk. Overworking in the short term can lead to productivity collapse or worse — health costs. Sustainable income comes from sustainable work habits.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -5,
                        'xp_delta'         => 8,
                    ],
                    [
                        'label'            => 'Accept Saturday only',
                        'description'      => 'One day — balance work and life.',
                        'outcome_text'     => 'You do Saturday, protect Sunday for yourself. Ksh 4,500 richer, and you still got a real break. Mr. Ochieng respects the boundary.',
                        'financial_lesson' => 'Work-life balance is a financial strategy too. Earning extra while protecting your wellbeing is the optimal play — not always the maximum hustle.',
                        'balance_delta'    => 4500,
                        'credit_score_delta' => 1,
                        'relationship_delta' => 8,
                        'xp_delta'         => 15,
                    ],
                ],
            ],

            // ── OPPORTUNITY: AISHA ─────────────────────────────────────────────
            [
                'npc_id'    => $npcs['Aisha'] ?? null,
                'title'     => 'Aisha\'s Investment Tip',
                'body'      => 'Aisha calls excitedly: "Babe, I found a group — you invest Ksh 10,000 and they double it in 21 days. My cousin already got Ksh 20K back! Only a few slots left." She sends you the WhatsApp group link.',
                'image_url' => 'https://loremflickr.com/800/420/investment,money,kenya?lock=405',
                'category'  => 'opportunity',
                'icon'      => '⚠️',
                'weight'    => 13,
                'min_tick'  => 12,
                'choices'   => [
                    [
                        'label'            => 'Invest Ksh 10,000',
                        'description'      => 'Her cousin got paid — maybe it\'s real?',
                        'outcome_text'     => 'You send the Ksh 10,000. Week 1: silence. Week 2: the admin says "withdrawals on hold, reinvesting for bigger returns." Week 3: the WhatsApp group is deleted. Aisha is also silent — she lost money too.',
                        'financial_lesson' => 'If it promises guaranteed returns above 12-15% in a short time — it\'s a pyramid scheme. The first people out get paid; everyone else loses. In Kenya, this is called "chama ya kulipwa mara moja." Run.',
                        'balance_delta'    => -10000,
                        'credit_score_delta' => -5,
                        'relationship_delta' => -10,
                        'xp_delta'         => 20,
                    ],
                    [
                        'label'            => 'Decline — smells like a scam',
                        'description'      => 'No legitimate investment doubles in 21 days.',
                        'outcome_text'     => 'You decline and explain your reasoning to Aisha. She\'s annoyed. Three weeks later she comes back: "Bro you were right. I lost 15K." You help her understand what just happened.',
                        'financial_lesson' => 'The red flags of a pyramid scheme: (1) guaranteed high returns, (2) urgency/limited slots, (3) returns come from recruiting not investing, (4) unregulated. Always verify with the CMA Kenya website before investing.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 3,
                        'relationship_delta' => 5,
                        'xp_delta'         => 25,
                        'badge_slug'       => 'scam-detector',
                    ],
                    [
                        'label'            => 'Research it first',
                        'description'      => 'Ask for a CMA registration number.',
                        'outcome_text'     => 'You ask for the company\'s CMA (Capital Markets Authority) registration. Aisha checks — there is none. The admin of the group becomes hostile when pressed. You don\'t invest. Smart move.',
                        'financial_lesson' => 'Every legitimate investment firm in Kenya is registered with the CMA, CBK, or IRA. If they can\'t provide a registration number, walk away. Financial due diligence is not paranoia — it\'s survival.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 5,
                        'relationship_delta' => 8,
                        'xp_delta'         => 30,
                        'badge_slug'       => 'due-diligence',
                    ],
                ],
            ],

            // ── HOUSING: LANDLORD ──────────────────────────────────────────────
            [
                'npc_id'    => $npcs['Landlord'] ?? null,
                'title'     => 'Landlord Raises Rent Ksh 2,000',
                'body'      => 'You receive a letter under your door: "Dear Tenant, effective next month, monthly rent increases from Ksh 14,000 to Ksh 16,000 due to rising maintenance costs and market rates. Regards, Bwana Kariuki."',
                'image_url' => 'https://loremflickr.com/800/420/apartment,nairobi,housing?lock=406',
                'category'  => 'housing',
                'icon'      => '🏠',
                'weight'    => 11,
                'min_tick'  => 25,
                'choices'   => [
                    [
                        'label'            => 'Accept the increase',
                        'description'      => 'Easier than the hassle of moving.',
                        'outcome_text'     => 'You accept. Your monthly expenses just increased by Ksh 2,000 permanently. Over a year that\'s Ksh 24,000 more in rent — equivalent to a 3-month gym membership you never used.',
                        'financial_lesson' => 'Rent increases compound over time. Ksh 2,000/month × 12 = Ksh 24,000/year. If you accept increases without negotiating, in 5 years you\'re paying Ksh 10,000 more per month than you started. Always negotiate.',
                        'balance_delta'    => -2000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 5,
                        'xp_delta'         => 5,
                    ],
                    [
                        'label'            => 'Negotiate — propose Ksh 500 increase',
                        'description'      => 'Politely push back with a counter-offer.',
                        'outcome_text'     => 'You knock on Bwana Kariuki\'s door with a printed letter countering at Ksh 500 increase, citing your 18 months of on-time payments. After a tense discussion, he agrees to Ksh 1,000. You save Ksh 12,000 this year.',
                        'financial_lesson' => 'Negotiation is a financial skill worth thousands of shillings. Being a good tenant (paying on time) gives you leverage. Document your payment history — it\'s your bargaining chip with landlords and future creditors.',
                        'balance_delta'    => -1000,
                        'credit_score_delta' => 2,
                        'relationship_delta' => -5,
                        'xp_delta'         => 20,
                        'badge_slug'       => 'negotiator',
                    ],
                    [
                        'label'            => 'Start looking for a new place',
                        'description'      => 'Use this as a trigger to upgrade or downgrade.',
                        'outcome_text'     => 'You start viewing houses. You find a nicer place in Kasarani at Ksh 13,500. Moving costs Ksh 5,000 but saves Ksh 2,500/month long-term. In 2 months you\'re ahead.',
                        'financial_lesson' => 'Housing is your biggest monthly expense. Optimising it — even by Ksh 1,000/month — saves Ksh 12,000/year. Moving costs have a payback period: calculate months to recoup moving costs at the monthly saving.',
                        'balance_delta'    => -5000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -15,
                        'xp_delta'         => 15,
                    ],
                ],
            ],

            // ── OPPORTUNITY: AISHA - SACCO ─────────────────────────────────────
            [
                'npc_id'    => $npcs['Aisha'] ?? null,
                'title'     => 'Aisha Recommends a SACCO',
                'body'      => 'Aisha texts: "I just got a Ksh 90,000 loan from Stima SACCO at 12% per year to buy stock for my shop. Their savings account earns 9% on deposits. You should join — minimum Ksh 2,000/month contribution."',
                'image_url' => 'https://loremflickr.com/800/420/bank,savings,cooperative?lock=407',
                'category'  => 'opportunity',
                'icon'      => '🏦',
                'weight'    => 11,
                'min_tick'  => 30,
                'choices'   => [
                    [
                        'label'            => 'Join and start saving Ksh 2,000/month',
                        'description'      => 'SACCOs beat banks — it\'s a good move.',
                        'outcome_text'     => 'You join. After 12 months you have Ksh 24,000 in contributions earning 9% interest. You also qualify for a 3× loan (Ksh 72,000) at just 12% per year — less than half what banks charge.',
                        'financial_lesson' => 'SACCOs (Savings & Credit Cooperatives) are the most underutilised financial tool in Kenya. They pay higher savings rates (8-12%) and offer loans at 1% per month vs banks at 2-3%. Every Kenyan should belong to one.',
                        'balance_delta'    => -2000,
                        'credit_score_delta' => 5,
                        'relationship_delta' => 10,
                        'xp_delta'         => 25,
                        'badge_slug'       => 'sacco-member',
                    ],
                    [
                        'label'            => 'Not now — I\'ll think about it',
                        'description'      => 'Ksh 2,000/month is too much right now.',
                        'outcome_text'     => 'You delay. Six months pass. You\'ve spent that Ksh 2,000 each month on things you can\'t remember. You could have Ksh 12,000 saved by now.',
                        'financial_lesson' => 'Delayed saving is the most expensive financial habit. Every month you don\'t save is a month of compound interest you can never get back. Start small — even Ksh 500/month in a SACCO is a foundation.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => -2,
                        'relationship_delta' => 0,
                        'xp_delta'         => 8,
                    ],
                ],
            ],

            // ── EMERGENCY ──────────────────────────────────────────────────────
            [
                'npc_id'    => $npcs['Mama'] ?? null,
                'title'     => 'Medical Emergency',
                'body'      => 'Mama calls crying. Your father collapsed at work. The doctor at KNH says he needs urgent tests and admission — estimated Ksh 25,000 before insurance kicks in. "Uko na pesa kidogo?"',
                'image_url' => 'https://loremflickr.com/800/420/hospital,medical,emergency?lock=408',
                'category'  => 'emergency',
                'icon'      => '🏥',
                'weight'    => 8,
                'min_tick'  => 20,
                'choices'   => [
                    [
                        'label'            => 'Pay from emergency fund',
                        'description'      => 'This is exactly what it\'s for.',
                        'outcome_text'     => 'You immediately M-Pesa the Ksh 25,000 from your emergency fund. Your father gets care. Crisis managed. Your fund drops — but that\'s what it\'s there for.',
                        'financial_lesson' => 'An emergency fund is not savings — it\'s insurance. The whole point is that it exists when you need it most. Target 3-6 months of expenses. When you use it, replenish it immediately as a financial priority.',
                        'balance_delta'    => -25000,
                        'credit_score_delta' => 3,
                        'relationship_delta' => 25,
                        'xp_delta'         => 20,
                    ],
                    [
                        'label'            => 'No emergency fund — call everyone',
                        'description'      => 'Scramble to raise money from family and friends.',
                        'outcome_text'     => 'You spend 3 hours calling relatives, posting on WhatsApp groups, and even considering an M-Shwari loan at 7.5% interest. Eventually you raise enough — but your father waits 4 hours for care.',
                        'financial_lesson' => 'Medical emergencies are the #1 reason Kenyan families fall into debt. NHIF only covers hospitalisation — not upfront costs. Without an emergency fund, you borrow expensive money under stress. Start building one today: target Ksh 50,000 as a first milestone.',
                        'balance_delta'    => -5000,
                        'credit_score_delta' => -8,
                        'relationship_delta' => 10,
                        'xp_delta'         => 10,
                    ],
                ],
            ],

            // ── MARKET: NSE ────────────────────────────────────────────────────
            [
                'npc_id'    => null,
                'title'     => 'Safaricom Shares Drop 12%',
                'body'      => 'The NSE app notification pings: Safaricom shares fell from Ksh 18.50 to Ksh 16.30 after a challenging quarter earnings report. Your portfolio shows a paper loss of Ksh 8,200. Financial news is calling it a "buying opportunity."',
                'image_url' => 'https://loremflickr.com/800/420/stock,market,investing?lock=409',
                'category'  => 'market',
                'icon'      => '📉',
                'weight'    => 10,
                'min_tick'  => 35,
                'choices'   => [
                    [
                        'label'            => 'Buy more — buy the dip',
                        'description'      => 'Lower price = better entry point.',
                        'outcome_text'     => 'You invest Ksh 15,000 buying more Safaricom shares at Ksh 16.30. Six game months later, shares recover to Ksh 19.80. Your Ksh 15,000 is now Ksh 18,220. Return: 21.5%.',
                        'financial_lesson' => 'Buying the dip works for fundamentally strong companies. Safaricom is a blue-chip stock — market leader with consistent dividends. Short-term price drops from good companies are often buying opportunities, not sell signals.',
                        'balance_delta'    => -15000,
                        'credit_score_delta' => 2,
                        'relationship_delta' => 0,
                        'xp_delta'         => 20,
                    ],
                    [
                        'label'            => 'Hold steady — don\'t panic sell',
                        'description'      => 'Stay the course.',
                        'outcome_text'     => 'You hold your position. Your paper loss is uncomfortable to watch. But 4 months later shares recover to Ksh 18.90. Your portfolio is green again.',
                        'financial_lesson' => '"The stock market is a device for transferring money from the impatient to the patient." — Warren Buffett. Panic-selling locks in losses. Long-term investors in the NSE have historically earned 12-15% annually by staying invested.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 1,
                        'relationship_delta' => 0,
                        'xp_delta'         => 15,
                    ],
                    [
                        'label'            => 'Sell everything — limit losses',
                        'description'      => 'Get out while you can.',
                        'outcome_text'     => 'You sell at Ksh 16.30, realising the loss. Two months later Safaricom announces a special dividend and shares hit Ksh 20.10. You missed the recovery entirely and locked in your loss.',
                        'financial_lesson' => 'Selling in a panic is how most retail investors destroy wealth. The loss only becomes real when you sell. Long-term investing means riding the volatility — not running from it.',
                        'balance_delta'    => -8200,
                        'credit_score_delta' => -3,
                        'relationship_delta' => 0,
                        'xp_delta'         => 10,
                    ],
                ],
            ],

            // ── CAREER: SALARY NEGOTIATION ─────────────────────────────────────
            [
                'npc_id'    => $npcs['The Boss'] ?? null,
                'title'     => 'Annual Review — Ask for a Raise?',
                'body'      => 'It\'s your 12-month anniversary at work. Mr. Ochieng schedules your annual performance review. You\'ve done well this year. The question hangs in the air: do you ask for a salary increase?',
                'image_url' => 'https://loremflickr.com/800/420/meeting,office,professional?lock=410',
                'category'  => 'career',
                'icon'      => '💰',
                'weight'    => 10,
                'min_tick'  => 40,
                'choices'   => [
                    [
                        'label'            => 'Ask boldly — request 20% raise',
                        'description'      => 'You have the results to back it up.',
                        'outcome_text'     => 'You walk in prepared with a list of your achievements, market salary data, and a clear ask for 20%. Mr. Ochieng counters at 12%. You negotiate to 15%. Your salary increases by Ksh 6,000/month.',
                        'financial_lesson' => 'The single highest-ROI financial move is a salary negotiation. A 15% raise on Ksh 40,000/month = Ksh 6,000 more per month = Ksh 72,000 per year — compounding with every future raise. Never leave this money on the table.',
                        'balance_delta'    => 6000,
                        'credit_score_delta' => 5,
                        'relationship_delta' => 8,
                        'xp_delta'         => 30,
                        'badge_slug'       => 'salary-negotiator',
                    ],
                    [
                        'label'            => 'Don\'t ask — don\'t want to seem greedy',
                        'description'      => 'Just wait for them to offer something.',
                        'outcome_text'     => 'You say nothing. Mr. Ochieng gives you a 3% "cost of living adjustment" — Ksh 1,200/month. Inflation this year was 8.5%. In real terms, your salary just went down.',
                        'financial_lesson' => 'Companies don\'t volunteer money — you have to ask. Inflation erodes salary value every year. If your raise is below inflation, you got a pay cut. Research market rates on BrighterMonday or LinkedIn before any review.',
                        'balance_delta'    => 1200,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 2,
                        'xp_delta'         => 5,
                    ],
                    [
                        'label'            => 'Ask for Ksh 5,000 increase specifically',
                        'description'      => 'A specific, researched number.',
                        'outcome_text'     => 'You name a specific figure backed by market research. Mr. Ochieng appreciates the preparation and agrees to Ksh 4,000 increase. Less than asked but more than you\'d have gotten in silence.',
                        'financial_lesson' => 'Specific asks are taken more seriously than vague requests. "I\'d like a raise" gets less traction than "Based on market research, I\'m asking for Ksh 5,000/month increase." Numbers signal seriousness.',
                        'balance_delta'    => 4000,
                        'credit_score_delta' => 3,
                        'relationship_delta' => 10,
                        'xp_delta'         => 20,
                    ],
                ],
            ],

            // ── OPPORTUNITY: SIDE HUSTLE ───────────────────────────────────────
            [
                'npc_id'    => $npcs['Aisha'] ?? null,
                'title'     => 'Side Hustle Opportunity',
                'body'      => 'Aisha is overwhelmed with orders for her online boutique. "I need someone to handle deliveries on weekends — Ksh 3,000 per weekend plus tips. Are you interested? It\'ll take 4-5 hours Saturday morning."',
                'image_url' => 'https://loremflickr.com/800/420/delivery,hustle,nairobi?lock=411',
                'category'  => 'opportunity',
                'icon'      => '🚀',
                'weight'    => 11,
                'min_tick'  => 15,
                'choices'   => [
                    [
                        'label'            => 'Say yes — extra income!',
                        'description'      => 'Ksh 3,000/weekend = Ksh 12,000/month extra.',
                        'outcome_text'     => 'You spend 5 Saturdays delivering. You earn Ksh 15,500 (tips included). You put Ksh 10,000 into savings. Aisha is impressed. This might become a recurring arrangement.',
                        'financial_lesson' => 'Side income is the fastest way to accelerate financial goals. Ksh 12,000/month extra = Ksh 144,000/year — enough to fund an emergency fund, start investing, or pay a year of rent. Protect time for side hustles.',
                        'balance_delta'    => 15500,
                        'credit_score_delta' => 3,
                        'relationship_delta' => 15,
                        'xp_delta'         => 25,
                    ],
                    [
                        'label'            => 'Decline — weekends are sacred',
                        'description'      => 'Rest matters for productivity.',
                        'outcome_text'     => 'You decline. Your weekends stay free but your savings stay flat. Aisha finds someone else — that person now has a part-time income stream you don\'t.',
                        'financial_lesson' => 'There\'s no perfect time to start a side hustle. Every "not now" is compounded income foregone. Even 1-2 hours a week on a side skill (design, coding, writing, tutoring) can grow into meaningful income.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -5,
                        'xp_delta'         => 5,
                    ],
                ],
            ],

            // ── FAMILY: UNCLE MWANGI ───────────────────────────────────────────
            [
                'npc_id'    => $npcs['Uncle'] ?? null,
                'title'     => 'Uncle Wants Harambee Contribution',
                'body'      => 'Uncle Mwangi calls: "Kijana, your cousin Agnes is getting married. We need everyone to contribute to the harambee — at least Ksh 5,000 minimum. Family is family." You weren\'t even invited to the wedding.',
                'image_url' => 'https://loremflickr.com/800/420/african,family,celebration?lock=412',
                'category'  => 'family',
                'icon'      => '🎊',
                'weight'    => 10,
                'min_tick'  => 20,
                'choices'   => [
                    [
                        'label'            => 'Contribute Ksh 5,000',
                        'description'      => 'Family obligations are real.',
                        'outcome_text'     => 'You send Ksh 5,000. Your name is read out at the harambee. Family unity is maintained. Your bank balance winces slightly. This is part of the Kenyan social fabric.',
                        'financial_lesson' => 'Harambees and family financial obligations are a feature of Kenyan life, not a bug. Budget for them. "Family support" should be a monthly budget line — Ksh 1,000–3,000 — so when the call comes, you\'re prepared, not blindsided.',
                        'balance_delta'    => -5000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 15,
                        'xp_delta'         => 10,
                    ],
                    [
                        'label'            => 'Send Ksh 2,000 — what you can manage',
                        'description'      => 'Contribute what you can afford.',
                        'outcome_text'     => 'You explain to Uncle Mwangi that your budget is tight and you can only do Ksh 2,000. It\'s accepted, even if there\'s a slight undercurrent of disappointment. You gave what you could.',
                        'financial_lesson' => 'It\'s always better to contribute what you can afford than to contribute what you can\'t and build debt or resentment. Honest communication about financial limits is a sign of maturity.',
                        'balance_delta'    => -2000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 5,
                        'xp_delta'         => 12,
                    ],
                    [
                        'label'            => 'Decline — you weren\'t invited',
                        'description'      => 'Hard but valid boundary.',
                        'outcome_text'     => 'You politely decline. Uncle Mwangi is visibly displeased. Whispers at the next family gathering. But your budget survives intact. Family politics are complex.',
                        'financial_lesson' => 'Not every harambee obligation is equally yours to carry. It\'s okay to prioritise. The key is having clear criteria for which family obligations you will and won\'t fund, rather than saying yes to everything and going into debt.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -20,
                        'xp_delta'         => 8,
                    ],
                ],
            ],

            // ── SOCIAL: PEER PRESSURE ──────────────────────────────────────────
            [
                'npc_id'    => $npcs['Kamau'] ?? null,
                'title'     => 'The Friday Drink-Up',
                'body'      => 'Kamau\'s message: "Bro it\'s been mad stressful. A few of us are going Westlands tonight. First round on me — just need everyone to chip in Ksh 2,000 cover charge. You in?" The group has already said yes.',
                'image_url' => 'https://loremflickr.com/800/420/bar,nairobi,nightlife?lock=413',
                'category'  => 'social',
                'icon'      => '🍺',
                'weight'    => 12,
                'min_tick'  => 8,
                'choices'   => [
                    [
                        'label'            => 'Go — stress needs release',
                        'description'      => 'Life balance matters.',
                        'outcome_text'     => 'Cover: Ksh 2,000. Drinks: Ksh 3,500. Late night snacks: Ksh 800. Uber home: Ksh 650. Total: Ksh 6,950. Good night though.',
                        'financial_lesson' => 'The real cost of a night out is always more than the entry fee. Always estimate the TOTAL cost (transport, drinks, food, impulse spending) before agreeing. Ksh 2k cover nights often end at Ksh 7k.',
                        'balance_delta'    => -6950,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 12,
                        'xp_delta'         => 5,
                    ],
                    [
                        'label'            => 'Skip it this time',
                        'description'      => 'Protect the budget this month.',
                        'outcome_text'     => 'You sit this one out. Kamau sends FOMO videos. But Sunday morning you feel rested and your account is intact. You suggest a cheaper hangout next weekend that everyone ends up preferring.',
                        'financial_lesson' => 'FOMO (fear of missing out) is one of the most expensive emotions. Social media makes others\' spending look like a standard you must meet. It isn\'t. Build friendships that don\'t require financial performance.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 0,
                        'relationship_delta' => -3,
                        'xp_delta'         => 15,
                    ],
                ],
            ],

            // ── OPPORTUNITY: MAMA BLESSING ─────────────────────────────────────
            [
                'npc_id'    => $npcs['Mama'] ?? null,
                'title'     => 'Mama Sends a Surprise M-Pesa',
                'body'      => 'Your phone buzzes. M-Pesa alert: "You have received Ksh 5,000 from Grace Wanjiku." A text follows: "Kijana, I sold some of my tomatoes. Take care of yourself. Don\'t forget to save."',
                'image_url' => 'https://loremflickr.com/800/420/mpesa,phone,message,happy?lock=414',
                'category'  => 'opportunity',
                'icon'      => '💝',
                'weight'    => 8,
                'min_tick'  => 5,
                'choices'   => [
                    [
                        'label'            => 'Save it all — honour Mama\'s advice',
                        'description'      => 'She worked hard for this money.',
                        'outcome_text'     => 'You transfer the full Ksh 5,000 to your savings. Mama\'s hard work goes into your future. You text her a photo of the M-Pesa confirmation. She replies with a prayer emoji.',
                        'financial_lesson' => 'Windfalls (unexpected money) are savings acceleration opportunities. Most people spend them on consumption. The habit of saving windfalls is what separates slow savers from fast ones. Every bonus, gift, and windfall — save first.',
                        'balance_delta'    => 5000,
                        'credit_score_delta' => 2,
                        'relationship_delta' => 10,
                        'xp_delta'         => 20,
                    ],
                    [
                        'label'            => 'Treat yourself — you deserve it',
                        'description'      => 'You\'ve been working hard.',
                        'outcome_text'     => 'You spend Ksh 3,000 treating yourself. It feels great for 48 hours. The remaining Ksh 2,000 disappears into a few days of Ubers and impulse food. By Friday: Ksh 0 remains.',
                        'financial_lesson' => '"A little treat" is often the trigger for full windfall consumption. The pattern: spend some → feel good → spend more → nothing left. Reverse it: save 80% first, then treat yourself with the 20%. Saving first is the discipline.',
                        'balance_delta'    => 2000,
                        'credit_score_delta' => 0,
                        'relationship_delta' => 5,
                        'xp_delta'         => 5,
                    ],
                    [
                        'label'            => 'Split: Ksh 3,000 save, Ksh 2,000 enjoy',
                        'description'      => 'Balance is the sweet spot.',
                        'outcome_text'     => 'You save Ksh 3,000 and use Ksh 2,000 for a nice meal with a friend. You honour Mama\'s advice and still enjoy the moment. Balanced financial wellness.',
                        'financial_lesson' => 'The 80/20 savings rule for windfalls: save at least 80%, spend up to 20% without guilt. This approach builds wealth faster than pure discipline while avoiding the resentment that comes from never enjoying your money.',
                        'balance_delta'    => 5000,
                        'credit_score_delta' => 1,
                        'relationship_delta' => 8,
                        'xp_delta'         => 18,
                    ],
                ],
            ],

            // ── MARKET: M-SHWARI LOAN ──────────────────────────────────────────
            [
                'npc_id'    => null,
                'title'     => 'M-Shwari Is Offering You a Loan',
                'body'      => 'Your phone buzzes: "Hongera! You qualify for an M-Shwari loan of up to Ksh 30,000. Apply now and get instant credit to your M-Pesa. Facilitation fee: 7.5% (one-time)." Your balance is a bit low this month.',
                'image_url' => 'https://loremflickr.com/800/420/mobile,banking,kenya,phone?lock=415',
                'category'  => 'opportunity',
                'icon'      => '📲',
                'weight'    => 10,
                'min_tick'  => 10,
                'choices'   => [
                    [
                        'label'            => 'Borrow Ksh 10,000',
                        'description'      => 'Just to tide over this month.',
                        'outcome_text'     => 'You borrow Ksh 10,000. The actual amount received: Ksh 9,250 (after 7.5% fee). You must repay Ksh 10,000 within 30 days. You use it on day-to-day expenses. On day 29, you\'re scrambling to repay.',
                        'financial_lesson' => 'M-Shwari\'s 7.5% fee sounds small but annualises to 90% per year. Never use mobile loans for consumption (food, transport, fun) — only for genuine emergencies with a clear repayment source. The debt cycle starts here.',
                        'balance_delta'    => 9250,
                        'credit_score_delta' => -5,
                        'relationship_delta' => 0,
                        'xp_delta'         => 10,
                    ],
                    [
                        'label'            => 'Decline — no loans for daily expenses',
                        'description'      => 'Cut expenses instead.',
                        'outcome_text'     => 'You decline. You review your spending and cut Ksh 3,000 from discretionary items. Tight but you manage. No debt added to your balance sheet.',
                        'financial_lesson' => 'The best financial tool when you\'re short is a spending review, not a loan. Before borrowing, always ask: "what can I cut?" Mobile loans at 7.5%/month create a cycle that\'s hard to exit. Emergency fund > mobile loan.',
                        'balance_delta'    => 0,
                        'credit_score_delta' => 3,
                        'relationship_delta' => 0,
                        'xp_delta'         => 18,
                        'badge_slug'       => 'debt-avoider',
                    ],
                ],
            ],
        ];

        foreach ($decisions as $decisionData) {
            $choices = $decisionData['choices'];
            unset($decisionData['choices']);

            // Set defaults
            $decisionData['is_repeatable'] = $decisionData['is_repeatable'] ?? false;
            $decisionData['cooldown_ticks'] = $decisionData['cooldown_ticks'] ?? 90;
            $decisionData['max_tick'] = $decisionData['max_tick'] ?? null;
            $decisionData['min_balance'] = $decisionData['min_balance'] ?? null;
            $decisionData['max_balance'] = $decisionData['max_balance'] ?? null;
            $decisionData['required_career_fields'] = $decisionData['required_career_fields'] ?? null;
            $decisionData['is_active'] = $decisionData['is_active'] ?? true;

            $decision = LifeDecision::updateOrCreate(
                ['title' => $decisionData['title']],
                $decisionData
            );

            // Reseed choices
            $decision->choices()->delete();
            foreach ($choices as $idx => $choiceData) {
                $choiceData['decision_id'] = $decision->id;
                $choiceData['sort_order'] = $idx;
                $choiceData['badge_slug'] = $choiceData['badge_slug'] ?? null;
                LifeDecisionChoice::create($choiceData);
            }
        }

        $this->command->info('LifeDecisionSeeder: ' . count($decisions) . ' decisions seeded with choices.');
    }
}
