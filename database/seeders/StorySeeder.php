<?php

namespace Database\Seeders;

use App\Models\Node;
use App\Models\Story;
use Illuminate\Database\Seeder;

class StorySeeder extends Seeder
{
    public function run(): void
    {
        // ── Stories for each age group ─────────────────────────────────

        $stories = [
            // 18-25 age group stories
            [
                'title'       => 'The Side Hustle Dilemma',
                'description' => 'Amina has a salary but discovers she can earn more. Will she burn out or build an empire?',
                'age_group'   => '18-25',
                'color'       => '#6366f1',
                'icon'        => '💼',
                'sort_order'  => 10,
                'nodes'       => $this->sideHustleNodes(),
            ],
            [
                'title'       => 'The Debt Trap',
                'description' => 'Mobile loans are easy to get. Too easy. Navigate the debt spiral or escape it.',
                'age_group'   => '18-25',
                'color'       => '#ef4444',
                'icon'        => '⚠️',
                'sort_order'  => 11,
                'nodes'       => $this->debtTrapNodes(),
            ],
            // 13-17 age group
            [
                'title'       => 'The Online Scam',
                'description' => 'Shawn gets a too-good-to-be-true offer online. Will he spot the red flags?',
                'age_group'   => '13-17',
                'color'       => '#f59e0b',
                'icon'        => '🚨',
                'sort_order'  => 10,
                'nodes'       => $this->onlineScamNodes(),
            ],
            // 26+ age group
            [
                'title'       => 'Emergency Fund Crisis',
                'description' => 'Mama Njeri faces a medical emergency with no savings. Can she recover?',
                'age_group'   => '26+',
                'color'       => '#10b981',
                'icon'        => '🏥',
                'sort_order'  => 10,
                'nodes'       => $this->emergencyFundNodes(),
            ],
        ];

        foreach ($stories as $storyData) {
            $nodeDefs = $storyData['nodes'];
            unset($storyData['nodes']);

            $story = Story::create($storyData);
            $this->createStoryNodes($story, $nodeDefs);
        }
    }

    private function createStoryNodes(Story $story, array $nodeDefs): void
    {
        $created = [];
        // First pass: create nodes
        foreach ($nodeDefs as $key => $def) {
            $node = Node::create(array_merge($def['node'], ['story_id' => $story->id]));
            $created[$key] = $node;
        }
        // Second pass: create choices
        foreach ($nodeDefs as $key => $def) {
            foreach ($def['choices'] ?? [] as $choiceDef) {
                $nextKey = $choiceDef['next_key'] ?? null;
                $created[$key]->choices()->create([
                    'label'       => $choiceDef['label'],
                    'description' => $choiceDef['description'] ?? null,
                    'points'      => $choiceDef['points'] ?? 10,
                    'sort_order'  => $choiceDef['sort_order'] ?? 0,
                    'next_node_id'=> $nextKey ? $created[$nextKey]->id : null,
                    'effect_data' => $choiceDef['effect_data'] ?? [],
                ]);
            }
        }
    }

    // ── SIDE HUSTLE STORY (18-25) ──────────────────────────────────────────

    private function sideHustleNodes(): array
    {
        return [
            'start' => [
                'node' => [
                    'title'         => 'The Freelance Offer',
                    'scenario_text' => 'You work a 9-5 job earning Ksh 35,000/month. A client offers you a Ksh 15,000 graphic design project on the side. It will take most of your evenings for 3 weeks. Your manager doesn\'t know about freelancing policies at your company. What do you do?',
                    'age_group'     => '18-25',
                    'type'          => 'scenario',
                    'is_start'      => true,
                    'is_free'       => true,
                    'sort_order'    => 50,
                    'icon'          => '💼',
                    'theme_color'   => '#6366f1',
                    'image_url'     => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&q=80',
                ],
                'choices' => [
                    ['label'=>'Accept the project — extra income is extra income','description'=>'Take the freelance job without telling your employer.','points'=>15,'sort_order'=>1,'next_key'=>'no_policy_check','effect_data'=>['balance_change'=>0]],
                    ['label'=>'Check company policy first, then decide','description'=>'Review your employment contract before accepting.','points'=>25,'sort_order'=>2,'next_key'=>'policy_check','effect_data'=>['balance_change'=>0]],
                    ['label'=>'Decline — too risky to mix work and freelancing','description'=>'Stay focused on your primary job.','points'=>10,'sort_order'=>3,'next_key'=>'decline_hustle','effect_data'=>['balance_change'=>0]],
                ],
            ],
            'no_policy_check' => [
                'node' => [
                    'title'         => 'The Risk Pays Off… For Now',
                    'scenario_text' => 'You complete the project and earn Ksh 15,000! But 2 months later, your manager finds out about the freelancing. The company has a conflict-of-interest clause. You\'re put on a performance review. What do you do with the extra money you earned?',
                    'age_group'     => '18-25',
                    'type'          => 'scenario',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 51,
                    'icon'          => '⚖️',
                    'theme_color'   => '#f59e0b',
                ],
                'choices' => [
                    ['label'=>'Save it as an emergency fund in case I lose my job','points'=>30,'sort_order'=>1,'next_key'=>'good_ending','effect_data'=>['balance_change'=>15000]],
                    ['label'=>'Spend it — I already earned it, why worry','points'=>5,'sort_order'=>2,'next_key'=>'bad_ending','effect_data'=>['balance_change'=>0]],
                ],
            ],
            'policy_check' => [
                'node' => [
                    'title'         => 'The Smart Move',
                    'scenario_text' => 'You checked your contract — there\'s no clause against freelancing outside your industry! You take the project legally. You finish it, earn Ksh 15,000, and even get a referral to another client worth Ksh 22,000. You now have a growing side income!',
                    'age_group'     => '18-25',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 52,
                    'icon'          => '🌟',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'Always verify the rules before starting a side hustle. Legal freelancing can snowball into a full business.'],
                ],
                'choices' => [],
            ],
            'decline_hustle' => [
                'node' => [
                    'title'         => 'The Missed Opportunity',
                    'scenario_text' => 'You declined and focused on your job. Six months later, the freelancer who took that project launched their own design studio. Meanwhile, your salary only went up Ksh 2,000 in your annual review. The fear of risk cost you the launch of something great.',
                    'age_group'     => '18-25',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 53,
                    'icon'          => '😐',
                    'theme_color'   => '#94a3b8',
                    'metadata'      => ['final_lesson'=>'Calculated risk-taking is not recklessness. A verified side hustle, managed well, is one of the fastest paths to financial freedom.'],
                ],
                'choices' => [],
            ],
            'good_ending' => [
                'node' => [
                    'title'         => 'Crisis Averted',
                    'scenario_text' => 'You saved the Ksh 15,000 just in time. Your employer investigated and found the freelancing was a one-time issue. They gave you a warning instead of firing you. Your emergency fund cushioned the stress. You learned a critical lesson about risk and preparation.',
                    'age_group'     => '18-25',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 54,
                    'icon'          => '💪',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'An emergency fund is your financial airbag. It doesn\'t prevent accidents — it keeps you alive after one.','ending_type'=>'great'],
                ],
                'choices' => [],
            ],
            'bad_ending' => [
                'node' => [
                    'title'         => 'Spending the Safety Net',
                    'scenario_text' => 'You spent the Ksh 15,000 on a weekend getaway. Two weeks later, your employer suspended you pending investigation. With no savings, you had to borrow money from family to cover rent. It took 4 months to recover financially.',
                    'age_group'     => '18-25',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 55,
                    'icon'          => '📉',
                    'theme_color'   => '#ef4444',
                    'metadata'      => ['final_lesson'=>'When income is uncertain, save first. Every bonus or windfall is an opportunity to build your financial resilience.','ending_type'=>'lesson'],
                ],
                'choices' => [],
            ],
        ];
    }

    // ── DEBT TRAP STORY (18-25) ────────────────────────────────────────────

    private function debtTrapNodes(): array
    {
        return [
            'start' => [
                'node' => [
                    'title'         => 'Easy Money — The Loan Offer',
                    'scenario_text' => 'You\'re between jobs and your phone buzzes: "Borrow up to Ksh 30,000 instantly! 0% interest first month!" Your rent of Ksh 12,000 is due tomorrow. You have Ksh 3,000 in your account. The loan app is just 2 taps away. What do you do?',
                    'age_group'     => '18-25',
                    'type'          => 'scenario',
                    'is_start'      => true,
                    'is_free'       => true,
                    'sort_order'    => 60,
                    'icon'          => '📱',
                    'theme_color'   => '#ef4444',
                    'image_url'     => 'https://images.unsplash.com/photo-1556742400-b5d6d3b4b3e6?w=800&q=80',
                    'metadata'      => ['starting_balance'=>3000],
                ],
                'choices' => [
                    ['label'=>'Borrow exactly Ksh 12,000 for rent only','description'=>'Borrow the minimum needed — just enough for rent.','points'=>20,'sort_order'=>1,'next_key'=>'minimal_loan','effect_data'=>['balance_change'=>12000]],
                    ['label'=>'Borrow Ksh 25,000 — get breathing room + groceries','description'=>'Take more to cover more expenses.','points'=>5,'sort_order'=>2,'next_key'=>'big_loan','effect_data'=>['balance_change'=>25000]],
                    ['label'=>'Call 3 relatives first before touching the loan app','description'=>'Explore alternatives before borrowing at high interest.','points'=>30,'sort_order'=>3,'next_key'=>'ask_family','effect_data'=>['balance_change'=>0]],
                ],
            ],
            'minimal_loan' => [
                'node' => [
                    'title'         => 'The Repayment Crunch',
                    'scenario_text' => 'You borrowed Ksh 12,000 and paid rent. But the "0% first month" means interest kicks in month 2 at 15% — that\'s Ksh 1,800 extra. You get a small gig paying Ksh 8,000. Do you repay the loan immediately, or use the money for expenses and repay later?',
                    'age_group'     => '18-25',
                    'type'          => 'scenario',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 61,
                    'icon'          => '💸',
                    'theme_color'   => '#f59e0b',
                ],
                'choices' => [
                    ['label'=>'Repay the loan in full immediately','points'=>35,'sort_order'=>1,'next_key'=>'loan_cleared','effect_data'=>['balance_change'=>-12000]],
                    ['label'=>'Pay partial — keep some for expenses','points'=>15,'sort_order'=>2,'next_key'=>'partial_payment','effect_data'=>['balance_change'=>-6000]],
                ],
            ],
            'big_loan' => [
                'node' => [
                    'title'         => 'The Spiral Begins',
                    'scenario_text' => 'You borrowed Ksh 25,000. Rent is paid, you bought groceries, and even treated yourself. Month 2 arrives: you owe Ksh 28,750 (15% interest). You still don\'t have stable income. The app sends daily SMS reminders. Your credit score is dropping. What do you do?',
                    'age_group'     => '18-25',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 62,
                    'icon'          => '😰',
                    'theme_color'   => '#ef4444',
                    'metadata'      => ['final_lesson'=>'Borrowing more than you need creates more problems than it solves. Always borrow the minimum required and have a clear repayment plan before tapping "Apply".'],
                ],
                'choices' => [],
            ],
            'ask_family' => [
                'node' => [
                    'title'         => 'Family to the Rescue',
                    'scenario_text' => 'Your cousin sends you Ksh 5,000 and your aunt tops up another Ksh 7,000 — no interest, just love. You pay rent and have Ksh 3,000 for food. You land a job interview that week. You avoided the debt trap entirely.',
                    'age_group'     => '18-25',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 63,
                    'icon'          => '❤️',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'Your network is your net worth — literally. Asking trusted family before high-interest loans is almost always the smarter first step.'],
                ],
                'choices' => [],
            ],
            'loan_cleared' => [
                'node' => [
                    'title'         => 'Debt-Free and Smarter',
                    'scenario_text' => 'You repaid the Ksh 12,000 immediately and avoided interest. It was tight — but you were debt-free within a month. You promised yourself: next time, build a Ksh 20,000 emergency fund first so you never need to borrow for rent again.',
                    'age_group'     => '18-25',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 64,
                    'icon'          => '🏆',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'Repay loans immediately when you can. The interest clock is always ticking. Better yet — save 3 months of expenses as an emergency fund so you never need to borrow for basics.','ending_type'=>'great'],
                ],
                'choices' => [],
            ],
            'partial_payment' => [
                'node' => [
                    'title'         => 'The Rollover Trap',
                    'scenario_text' => 'You paid Ksh 6,000 but the remaining Ksh 6,000 rolled over with interest. By month 3, you owe Ksh 8,280. The app is now charging daily penalties. You realize the "small" loan has doubled in real cost. This is how debt spirals start.',
                    'age_group'     => '18-25',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 65,
                    'icon'          => '📉',
                    'theme_color'   => '#ef4444',
                    'metadata'      => ['final_lesson'=>'Partial payments on high-interest loans often cost more in the long run. When you can repay — repay fully. Every day of delay costs you money.','ending_type'=>'lesson'],
                ],
                'choices' => [],
            ],
        ];
    }

    // ── ONLINE SCAM STORY (13-17) ──────────────────────────────────────────

    private function onlineScamNodes(): array
    {
        return [
            'start' => [
                'node' => [
                    'title'         => 'The "Free Money" DM',
                    'scenario_text' => 'You\'re on Instagram when a verified-looking account DMs you: "Send Ksh 500 and get Ksh 5,000 back in 24 hours! 50 people already paid!" The profile looks legit — 10,000 followers, nice pictures. Your friend says he already sent money and is "waiting for returns." What do you do?',
                    'age_group'     => '13-17',
                    'type'          => 'scenario',
                    'is_start'      => true,
                    'is_free'       => true,
                    'sort_order'    => 50,
                    'icon'          => '🚨',
                    'theme_color'   => '#f59e0b',
                    'image_url'     => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=800&q=80',
                    'metadata'      => ['starting_balance'=>500],
                ],
                'choices' => [
                    ['label'=>'Send the Ksh 500 — my friend says it works!','description'=>'Trust the social proof and send money.','points'=>0,'sort_order'=>1,'next_key'=>'sent_money','effect_data'=>['balance_change'=>-500]],
                    ['label'=>'Research the account first — Google the username','description'=>'Verify before you send.','points'=>20,'sort_order'=>2,'next_key'=>'researched','effect_data'=>['balance_change'=>0]],
                    ['label'=>'Block and report — this is a scam','description'=>'Your gut says no.','points'=>30,'sort_order'=>3,'next_key'=>'blocked_it','effect_data'=>['balance_change'=>0]],
                    ['label'=>'Ask a parent or teacher about this first','description'=>'Seek advice from a trusted adult.','points'=>25,'sort_order'=>4,'next_key'=>'asked_adult','effect_data'=>['balance_change'=>0]],
                ],
            ],
            'sent_money' => [
                'node' => [
                    'title'         => 'The Receipt Never Comes',
                    'scenario_text' => 'You sent Ksh 500. The account sends you a "confirmation screenshot." 24 hours later — nothing. 48 hours — the account is gone. Your friend admits he never got money either. He was a shill account. You lost Ksh 500 in 2 minutes.',
                    'age_group'     => '13-17',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 51,
                    'icon'          => '😢',
                    'theme_color'   => '#ef4444',
                    'metadata'      => ['final_lesson'=>'If it sounds too good to be true, it is. Doubling money in 24 hours is impossible. "Friends who already paid" are often fake accounts. Never send money to strangers online.'],
                ],
                'choices' => [],
            ],
            'researched' => [
                'node' => [
                    'title'         => 'The Red Flags Appear',
                    'scenario_text' => 'You Google the username — and find 3 blog posts calling it a scam that has stolen over Ksh 200,000 from students. The followers are fake bots. The "friends" are fake profiles. You didn\'t lose a shilling. You also warn your friend.',
                    'age_group'     => '13-17',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 52,
                    'icon'          => '🔍',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'Researching before sending money is the simplest fraud protection. 5 minutes of Googling saves you from years of regret.'],
                ],
                'choices' => [],
            ],
            'blocked_it' => [
                'node' => [
                    'title'         => 'Your Gut Was Right',
                    'scenario_text' => 'You blocked and reported the account. Instagram removes it 3 days later — it had already scammed 40+ teens. Your friend (who sent money) lost Ksh 500. You kept yours. Your financial instinct saved you.',
                    'age_group'     => '13-17',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 53,
                    'icon'          => '🛡️',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'Your gut is a fraud detector. When something feels wrong online — block, report, and tell an adult. You just saved Ksh 500 and protected others.','ending_type'=>'great'],
                ],
                'choices' => [],
            ],
            'asked_adult' => [
                'node' => [
                    'title'         => 'Wisdom From Experience',
                    'scenario_text' => 'You show your mum the DM. She recognizes it immediately — she received the same message 3 years ago. "This is a pyramid scheme," she explains. She shows you how to identify scam accounts: no real followers, copy-paste captions, disappeared stories. You become the fraud-spotter in your class.',
                    'age_group'     => '13-17',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 54,
                    'icon'          => '👨‍👩‍👧',
                    'theme_color'   => '#8b5cf6',
                    'metadata'      => ['final_lesson'=>'Asking trusted adults about financial decisions is never embarrassing — it\'s smart. Adults have seen more scams and can help you identify patterns.','ending_type'=>'great'],
                ],
                'choices' => [],
            ],
        ];
    }

    // ── EMERGENCY FUND STORY (26+) ────────────────────────────────────────

    private function emergencyFundNodes(): array
    {
        return [
            'start' => [
                'node' => [
                    'title'         => 'Hospital at Midnight',
                    'scenario_text' => 'It\'s 11 PM. Your mother calls — she\'s been rushed to hospital with severe chest pains. The doctor says she needs tests costing Ksh 18,000 tonight. Your salary was Ksh 45,000 last month but you spent most of it. Your M-Pesa shows Ksh 2,340. What\'s your first move?',
                    'age_group'     => '26+',
                    'type'          => 'scenario',
                    'is_start'      => true,
                    'is_free'       => true,
                    'sort_order'    => 50,
                    'icon'          => '🏥',
                    'theme_color'   => '#ef4444',
                    'image_url'     => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=800&q=80',
                    'metadata'      => ['starting_balance'=>2340],
                ],
                'choices' => [
                    ['label'=>'Call family members immediately — rally the network','description'=>'Mobilize your family circle for emergency funds.','points'=>25,'sort_order'=>1,'next_key'=>'family_network','effect_data'=>['balance_change'=>0]],
                    ['label'=>'Take a mobile loan — deal with consequences later','description'=>'Borrow fast and worry about repayment later.','points'=>10,'sort_order'=>2,'next_key'=>'emergency_loan','effect_data'=>['balance_change'=>18000]],
                    ['label'=>'Ask the hospital if they can start care and bill you','description'=>'Negotiate payment terms with the hospital.','points'=>20,'sort_order'=>3,'next_key'=>'negotiate_hospital','effect_data'=>['balance_change'=>0]],
                ],
            ],
            'family_network' => [
                'node' => [
                    'title'         => 'Ubuntu In Action',
                    'scenario_text' => 'Within 45 minutes, your siblings and cousins have M-Pesad you Ksh 15,000. Treatment begins. Your mother is stable by 3 AM. The relief is indescribable. But during those 45 minutes, you made a silent vow: you would never be in this position again.',
                    'age_group'     => '26+',
                    'type'          => 'scenario',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 51,
                    'icon'          => '👨‍👩‍👧‍👦',
                    'theme_color'   => '#10b981',
                ],
                'choices' => [
                    ['label'=>'Start a Ksh 3,000/month emergency fund — non-negotiable','points'=>40,'sort_order'=>1,'next_key'=>'fund_built','effect_data'=>['balance_change'=>0]],
                    ['label'=>'Thank everyone and move on — life is expensive','points'=>5,'sort_order'=>2,'next_key'=>'no_change','effect_data'=>['balance_change'=>0]],
                ],
            ],
            'emergency_loan' => [
                'node' => [
                    'title'         => 'Relief Now, Regret Later',
                    'scenario_text' => 'Treatment started immediately. Your mother is okay. But now you owe Ksh 20,700 (18,000 + 15% interest). Your salary this month will barely cover it. You ate ugali and tea for 3 weeks. The stress affected your work performance. You got the Ksh — but at what cost?',
                    'age_group'     => '26+',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 52,
                    'icon'          => '😓',
                    'theme_color'   => '#f59e0b',
                    'metadata'      => ['final_lesson'=>'Mobile loans in emergencies aren\'t wrong — but they expose how vulnerable zero savings makes you. Start a dedicated medical emergency fund today.'],
                ],
                'choices' => [],
            ],
            'negotiate_hospital' => [
                'node' => [
                    'title'         => 'The Hospital Finance Officer',
                    'scenario_text' => 'The hospital has a payment plan — they can start care and you settle within 30 days with no interest. It\'s a policy many hospitals have but few people know about. Treatment begins. Your mother recovers. And you now know to always ask about payment options before panicking.',
                    'age_group'     => '26+',
                    'type'          => 'result',
                    'is_start'      => false,
                    'is_free'       => true,
                    'sort_order'    => 53,
                    'icon'          => '🤝',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'Always ask. Many hospitals, landlords, and creditors have hardship programs that most people never access because they never ask.'],
                ],
                'choices' => [],
            ],
            'fund_built' => [
                'node' => [
                    'title'         => 'One Year Later',
                    'scenario_text' => 'Twelve months later, your emergency fund has grown to Ksh 36,000. When your car breaks down costing Ksh 22,000, you pay cash — no loans, no panic, no borrowed money. You realize your emergency fund didn\'t just save money. It saved your peace of mind.',
                    'age_group'     => '26+',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 54,
                    'icon'          => '🛡️',
                    'theme_color'   => '#10b981',
                    'metadata'      => ['final_lesson'=>'An emergency fund is peace of mind in a savings account. Start with Ksh 500/week. By year end, you could have Ksh 26,000 ready for anything life throws at you.','ending_type'=>'great'],
                ],
                'choices' => [],
            ],
            'no_change' => [
                'node' => [
                    'title'         => 'History Repeats',
                    'scenario_text' => 'Life went on. Eighteen months later, your child needed urgent school fees of Ksh 25,000 the same week your landlord was threatening eviction. The panic was the same. The scrambling was the same. Nothing had changed because nothing was built.',
                    'age_group'     => '26+',
                    'type'          => 'ending',
                    'is_start'      => false,
                    'is_free'       => false,
                    'sort_order'    => 55,
                    'icon'          => '🔄',
                    'theme_color'   => '#ef4444',
                    'metadata'      => ['final_lesson'=>'Financial security doesn\'t happen by accident. Every crisis you survive without building savings will repeat. The only escape is deliberate preparation.','ending_type'=>'lesson'],
                ],
                'choices' => [],
            ],
        ];
    }
}
