<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Node;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulkScenarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedKids();
            $this->seedTeens();
            $this->seedYoungAdults();
            $this->seedAdults();
        });
    }

    // ─────────────────────────────────────────────
    // Helper: create a full scenario tree in one call
    // $choices = [[ label, desc, points, balance, result_text, lesson, type ], ...]
    // ─────────────────────────────────────────────
    private function scenario(
        string $ageGroup,
        string $title,
        string $text,
        string $icon,
        string $color,
        bool $isFree,
        ?int $startBalance,
        array $choices
    ): Node {
        $meta = $startBalance ? ['starting_balance' => $startBalance] : null;

        $start = Node::create([
            'title'         => $title,
            'scenario_text' => $text,
            'age_group'     => $ageGroup,
            'type'          => 'scenario',
            'is_start'      => true,
            'is_free'       => $isFree,
            'icon'          => $icon,
            'theme_color'   => $color,
            'sort_order'    => 0,
            'metadata'      => $meta,
        ]);

        foreach ($choices as $i => $c) {
            $rMeta = !empty($c[5]) ? ['final_lesson' => $c[5]] : null;
            $result = Node::create([
                'title'         => $c[0] . ' — Outcome',
                'scenario_text' => $c[4],
                'age_group'     => $ageGroup,
                'type'          => $c[6] ?? 'result',
                'is_start'      => false,
                'is_free'       => $isFree,
                'icon'          => '📍',
                'theme_color'   => '#a5b4fc',
                'sort_order'    => $i,
                'metadata'      => $rMeta,
            ]);

            Choice::create([
                'node_id'      => $start->id,
                'next_node_id' => $result->id,
                'label'        => $c[0],
                'description'  => $c[1],
                'points'       => $c[2],
                'sort_order'   => $i,
                'effect_data'  => ['balance_change' => $c[3], 'lesson' => $c[5] ?? ''],
            ]);
        }

        return $start;
    }

    // ─────────────────────────────────────────────
    // AGES 8–12
    // ─────────────────────────────────────────────
    private function seedKids(): void
    {
        $g = '8-12';

        $this->scenario($g, 'The Chores Deal', 'Your parent offers to pay you Ksh 50 for each chore you complete this week. You can do 5 chores (Ksh 250) but it means less TV time. What do you choose?', '🧹', '#6366f1', true, 0, [
            ['Do all 5 chores and save the full Ksh 250', 'Hard work pays off', 15, 250, 'You complete all 5 chores and earn Ksh 250! You put it all in your piggy bank. By the end of the month you have saved Ksh 1,000 — enough for that toy you wanted.', 'Consistent small income adds up to big savings over time.'],
            ['Do 2 chores then stop', 'A little effort, some reward', 5, 100, 'You earn Ksh 100 but stop when the work feels boring. Your sibling does the rest and earns Ksh 150 more. You realize you missed out by not finishing.', 'Half effort earns half the reward. Commitment to a task means more earnings.'],
            ['Skip all chores — watch TV instead', 'Rest now, regret later', 0, 0, 'You have a fun day but earn nothing. When your friend shows off a cool new toy, you wish you had worked. You now have to wait another whole month.', 'Delayed gratification: short-term sacrifice for long-term reward.', 'ending'],
        ]);

        $this->scenario($g, 'The Tuck Shop Temptation', 'Every day at lunch, you pass the school tuck shop. Your daily pocket money is Ksh 30. A snack costs Ksh 20. If you skip the snack for 5 days you can save Ksh 100. What do you do?', '🍭', '#ec4899', true, 30, [
            ['Save the snack money for 5 days', 'Small daily savings', 15, 100, 'You resist the tuck shop all week. On Friday you have Ksh 100 saved! You use it to buy a book you\'ve been eyeing. Your teacher praises your discipline.', 'Skipping small daily treats can build significant savings quickly.'],
            ['Buy the snack every day', 'Enjoy now', 0, -100, 'You enjoy your snack daily but spend all Ksh 150 by Friday. When your friend proposes a fun weekend activity that costs Ksh 100, you have nothing left.', 'Small daily spending drains your money fast without you noticing.', 'ending'],
            ['Buy a snack only 2 days out of 5', 'Balance treat and save', 8, 60, 'You treat yourself twice and save Ksh 60. It\'s a good balance — you enjoy some snacks and still have money left over for the weekend.', 'Budgeting means enjoying a little now while still building savings.'],
        ]);

        $this->scenario($g, 'The Broken Toy Business', 'Your neighbour has an old toy car that broke. You know how to fix it with Ksh 80 worth of glue. You can sell the fixed toy for Ksh 250. Should you invest your savings to fix and resell it?', '🚗', '#f59e0b', false, 80, [
            ['Spend Ksh 80 to fix it and sell for Ksh 250', 'Young entrepreneur', 20, 170, 'You fix the toy and sell it for Ksh 250. You make a Ksh 170 profit! You repeat the trick two more times and earn Ksh 510 extra this month.', 'Investing small amounts to create bigger returns is the foundation of business.'],
            ['Don\'t risk your savings', 'Play it safe', 5, 0, 'You keep your Ksh 80 safe. The neighbour\'s child fixes the toy themselves and sells it. You watch and wish you had taken the chance.', 'Being too cautious can mean missing profitable opportunities.'],
        ]);

        $this->scenario($g, 'The Class Fund Raiser', 'Your class needs to raise Ksh 5,000 for a trip. Your teacher suggests each student contribute Ksh 200. You have Ksh 350 saved. Should you contribute?', '🏫', '#10b981', false, 350, [
            ['Contribute Ksh 200 and keep Ksh 150', 'Invest in experiences', 15, -200, 'You contribute Ksh 200. The trip is funded and you go on the most fun day of the school year! You also still have Ksh 150 left.', 'Contributing to a group goal can unlock experiences money alone cannot buy.'],
            ['Contribute only Ksh 100', 'Partial contribution', 5, -100, 'You contribute Ksh 100. The class is still Ksh 1,000 short and the trip nearly gets cancelled. Other students cover the gap. You feel guilty.', 'In group projects, contributing your fair share matters.'],
            ['Keep all your savings', 'Protect savings', 0, 0, 'You keep all your money. The trip is barely funded without your contribution. You go on the trip but your classmates know you didn\'t contribute your share.', 'Saving for yourself is good, but sometimes community investment benefits everyone.', 'ending'],
        ]);

        $this->scenario($g, 'The Market Errand', 'Your mum sends you to the market with Ksh 500 to buy vegetables that cost Ksh 420. She says you can keep the change. On the way you see a cool pen for Ksh 60.', '🥦', '#8b5cf6', false, 500, [
            ['Buy only the vegetables and keep Ksh 80 change', 'Responsible errand running', 15, 80, 'You complete the errand perfectly and pocket Ksh 80. Your mum is pleased. You start a "market savings jar" and earn Ksh 80 every week!', 'Responsible handling of money builds trust and creates recurring income.'],
            ['Buy vegetables and the pen (Ksh 480 total)', 'Small treat', 5, 20, 'You buy both and have Ksh 20 left over. Your mum notices you spent slightly more than needed but understands. You now have a great pen for school.', 'Small treats are fine when they fit within a budget.'],
            ['Buy the pen first, then find vegetables are too expensive', 'Impulse buying fail', 0, -60, 'You buy the pen first for Ksh 60. The vegetables cost Ksh 420 so you only have Ksh 440 left — not enough. You have to go back home empty-handed and return the pen.', 'Impulse buying before completing your main task can cause problems.', 'ending'],
        ]);

        $this->scenario($g, 'The Savings Goal', 'You want a bicycle that costs Ksh 3,000. You get Ksh 200 pocket money every week. Your uncle says he will match half of whatever you save. How do you plan?', '🚲', '#14b8a6', false, 0, [
            ['Save all Ksh 200 every week — reach goal in 10 weeks', 'Committed saver', 20, 0, 'After 10 weeks you have Ksh 2,000 saved. Your uncle adds Ksh 1,000. You buy the bicycle! You ride it to school every day and save on transport too.', 'A clear savings goal with a deadline makes it easier to stay disciplined.'],
            ['Save Ksh 100 per week, spend rest on snacks', 'Moderate saver', 10, 0, 'After 10 weeks you have Ksh 1,000. Uncle adds Ksh 500. But you still need Ksh 1,500 more — it takes 5 more weeks. Delayed but you get there.', 'Saving less means it takes longer to reach your goals.'],
            ['Spend it all and hope someone buys the bicycle for you', 'No plan', 0, 0, 'You spend all your money on treats. After 3 months you have nothing saved. Your uncle\'s matching offer expires. No bicycle this year.', 'Hoping for gifts is not a savings strategy.', 'ending'],
        ]);

        $this->scenario($g, 'Lending to a Friend', 'Your best friend asks to borrow Ksh 100 until next week. You have Ksh 200 saved. They promise to pay back Ksh 120 as a thank you. What do you do?', '🤝', '#f97316', false, 200, [
            ['Lend Ksh 100 and trust your friend to pay back', 'Friendly lending', 10, 0, 'Your friend pays back Ksh 120 as promised! You earned Ksh 20 interest. They thank you and you now have Ksh 320.', 'Lending to trusted people can earn you interest income.'],
            ['Say no — you need your savings', 'Protect savings', 5, 0, 'You politely decline. Your friend understands and finds another solution. Your Ksh 200 stays safe.', 'It\'s okay to say no to lending if you can\'t afford to lose the money.'],
            ['Lend all Ksh 200 to be extra helpful', 'Over-generous lending', 0, -200, 'You lend all Ksh 200. Your friend\'s parents go through a hard time and the friend can\'t pay back. You lose all your savings.', 'Never lend more than you can afford to lose.', 'ending'],
        ]);

        $this->scenario($g, 'The Group Buying Club', 'You and 4 friends each chip in Ksh 100 to buy a Ksh 500 board game to share. But one friend says they will pay you back later instead of paying now. Do you still join?', '🎲', '#a855f7', false, 100, [
            ['Only join if all 5 pay upfront now', 'Fair rules', 15, -100, 'You insist everyone pays upfront. They agree. You buy the game and everyone enjoys it equally. Clear rules prevented conflict.', 'Clear financial agreements prevent future misunderstandings.'],
            ['Join and trust the friend to pay later', 'Trust but verify', 5, -100, 'You join and trust the friend. They take 3 weeks to pay, which causes some tension. Eventually they pay and everything is fine.', 'Trust is good but written agreements or deadlines make things smoother.'],
            ['Refuse to join because of the uncertain friend', 'Protect yourself', 5, 0, 'You don\'t join. Your 4 friends buy the game without you. You miss out on the fun. Later the friend does pay — you made a fine choice but missed the game.', 'Protecting yourself financially is wise, but sometimes trust is worth the small risk.'],
        ]);

        $this->scenario($g, 'The School Project Budget', 'Your class project needs materials: chart paper, glue and markers. The group budget is Ksh 300. A fancy glitter glue costs Ksh 150 extra. Your share of the budget is Ksh 75.', '📚', '#06b6d4', false, 75, [
            ['Stick to the Ksh 75 budget — buy only what\'s needed', 'Budget discipline', 15, -75, 'Your group buys the essentials. The project looks great and you win second prize! You have no extra costs.', 'Staying within budget delivers good results without financial stress.'],
            ['Chip in extra Ksh 50 for the glitter glue', 'Going the extra mile', 8, -125, 'The glitter glue makes your project look amazing. You win first prize! The Ksh 50 extra was worth it this time.', 'Sometimes a small extra investment delivers outsized returns.'],
            ['Spend nothing and let others cover costs', 'Free-rider', 0, 0, 'You contribute nothing. Your group mates are frustrated. Even though the project finishes, you lose your friends\' trust.', 'In group projects, financial free-riding damages relationships.', 'ending'],
        ]);

        $this->scenario($g, 'The Tooth Fairy Fund', 'You lost a tooth and the tooth fairy left Ksh 200! Your older sibling says you should put half in a savings account at the sacco so it grows. What do you do?', '🦷', '#ec4899', false, 200, [
            ['Put Ksh 100 in sacco, keep Ksh 100 to spend', 'Split wisely', 15, 0, 'You save Ksh 100 in the sacco. After 6 months, with interest, it grows to Ksh 115! You also enjoyed the Ksh 100 you kept. Smart split!', 'Saving even a small part of every income builds a habit that lasts a lifetime.'],
            ['Spend the full Ksh 200 on stationery', 'Practical spending', 5, 0, 'You buy good pens, a ruler and coloured pencils. They help you with school all year. Useful spending is valid!', 'Spending on things that help you improve or learn is a form of investment.'],
            ['Hide the Ksh 200 under your mattress', 'Safety hoarding', 2, 0, 'You hide it under your mattress. Six months later, your sibling finds Ksh 200 in the sacco earning interest. Your Ksh 200 is still exactly Ksh 200 — no growth!', 'Cash under a mattress doesn\'t grow. Savings accounts earn interest over time.'],
        ]);
    }

    // ─────────────────────────────────────────────
    // AGES 13–17
    // ─────────────────────────────────────────────
    private function seedTeens(): void
    {
        $g = '13-17';

        $this->scenario($g, 'The Side Hustle', 'You\'re 15 and talented at graphic design on your phone. A local shopkeeper wants a flyer for Ksh 500. It\'ll take 3 hours of your weekend. Your friends want you at a hangout. What do you do?', '🎨', '#6366f1', true, 0, [
            ['Complete the design and earn Ksh 500', 'Side hustle over hangout', 20, 500, 'You deliver a beautiful flyer. The shopkeeper is impressed and refers you to 3 more clients that month — earning Ksh 2,000 total!', 'Turning a skill into income is one of the most powerful financial moves you can make.'],
            ['Go to the hangout and skip the job', 'Social over hustle', 0, 0, 'You have a fun afternoon but earn nothing. The shopkeeper finds another designer. You miss a referral chain that could have paid school fees.', 'Social fun is important, but one afternoon of work could change your financial trajectory.', 'ending'],
            ['Negotiate to do it Monday after school', 'Balanced approach', 15, 500, 'You ask for a deadline extension, go to the hangout, then deliver the flyer on Monday. Client is happy, you earned Ksh 500 and didn\'t miss the fun.', 'Good time management lets you balance social life and income.'],
        ]);

        $this->scenario($g, 'Peer Pressure Spending', 'Your friend group has started buying designer shoes worth Ksh 4,500. You have Ksh 5,000 in savings (3 months of work). Everyone is asking why you\'re not buying them.', '👟', '#ef4444', true, 5000, [
            ['Keep your savings — ignore the pressure', 'Financial confidence', 20, 0, 'You politely decline and keep your Ksh 5,000 saved. 2 months later, the shoes are already out of fashion. Your friends move on to the next trend while you still have your money.', 'Peer pressure spending drains savings on things that quickly lose value.'],
            ['Buy the shoes to fit in', 'Social pressure surrender', 0, -4500, 'You buy the shoes. For 2 weeks you feel great. Then they scuff and go out of style. You spent 3 months of work on shoes you wear for 2 weeks.', 'Buying things purely for social approval almost always leads to regret.', 'ending'],
            ['Buy a Ksh 2,000 alternative and invest the rest', 'Smart compromise', 15, -2000, 'You find a good-looking pair for Ksh 2,000. You look great and still have Ksh 3,000 saved. Best of both worlds!', 'You don\'t need to spend the maximum to meet social expectations.'],
        ]);

        $this->scenario($g, 'M-Pesa Savings Jar', 'Your older cousin shows you M-Pesa M-Shwari. You can lock savings and earn 7.35% interest per year. You have Ksh 3,000 saved in cash at home. Should you open an account?', '📱', '#10b981', false, 3000, [
            ['Open M-Shwari and lock all Ksh 3,000', 'Digital saver', 20, 0, 'You open M-Shwari. After 6 months your Ksh 3,000 has grown to Ksh 3,110 — you earned Ksh 110 just for keeping money in a safe place!', 'Mobile savings accounts keep money safe AND grow it with interest.'],
            ['Keep cash at home — don\'t trust digital', 'Cash hoarder', 2, 0, 'You keep the cash at home. After 6 months it\'s still Ksh 3,000. No growth. You also realise it\'s tempting to dip into it.', 'Cash at home doesn\'t earn interest and is easier to spend impulsively.'],
            ['Put Ksh 2,000 in M-Shwari, keep Ksh 1,000 for emergencies', 'Balanced approach', 15, 0, 'Smart split! You earn interest on Ksh 2,000 while keeping emergency cash accessible. A balanced financial strategy.', 'A good financial plan keeps some liquidity for emergencies while the rest earns interest.'],
        ]);

        $this->scenario($g, 'The Exam Fees Crisis', 'School fees for the national exam are Ksh 2,000 due next week. You have Ksh 1,200 saved. Your aunt says she can lend you Ksh 800 but expects you to pay back Ksh 1,000 in 2 months.', '📝', '#f59e0b', false, 1200, [
            ['Borrow Ksh 800 from aunt and pay fees in full', 'Strategic borrowing', 15, -200, 'You borrow and pay fees in full. You sit the exam and pass. The Ksh 200 "interest" on the loan is a small price for not missing the exam.', 'Borrowing for education with a clear repayment plan is justified.'],
            ['Only pay partial fees and hope for an extension', 'Hope over action', 3, -1200, 'You pay Ksh 1,200. The school does not give extensions — you are barred from the exam room. A devastating consequence of incomplete payment.', 'Incomplete payment of critical fees can have irreversible consequences.', 'ending'],
            ['Hustle for Ksh 800 before the deadline', 'Earn your way', 18, -200, 'You offer to wash 10 neighbours\' cars for Ksh 100 each and earn Ksh 1,000 in 5 days. You pay fees in full without borrowing! Great financial resourcefulness.', 'When you need money quickly, selling a service is the fastest legitimate path.'],
        ]);

        $this->scenario($g, 'The Boda Boda Business', 'Your father has an idle motorbike. A boda boda rider offers to lease it for Ksh 300/day and give you Ksh 150/day (the other half for fuel/maintenance). Over 30 days that\'s Ksh 4,500 passive income.', '🏍️', '#8b5cf6', false, 0, [
            ['Agree to the lease — earn Ksh 4,500 passively', 'Asset income', 20, 4500, 'The rider is reliable. After 30 days you earn Ksh 4,500 with zero effort. You use it to pay 2 months of school fees! The bike earns while you study.', 'Assets that generate income while you sleep are the foundation of wealth building.'],
            ['Say no — scared the bike will be damaged', 'Risk avoidance', 3, 0, 'You decline. The bike sits unused for 3 months. You missed Ksh 13,500 of potential income from an idle asset.', 'An idle asset generates nothing. Calculated risk can unlock significant income.'],
            ['Lease it but negotiate Ksh 200/day instead of Ksh 150', 'Negotiate better terms', 18, 6000, 'The rider accepts Ksh 200/day after negotiation. Over 30 days you earn Ksh 6,000! Negotiation skills add Ksh 1,500 extra.', 'Negotiating better terms for your assets always pays off.'],
        ]);

        $this->scenario($g, 'First Bank Account', 'You turn 16 and can now open a junior savings account. Equity\'s Jijenge account requires Ksh 500 to open and pays 7% interest. You have Ksh 2,000 saved in an envelope.', '🏦', '#0ea5e9', false, 2000, [
            ['Open the Jijenge account and deposit all Ksh 2,000', 'Official saver', 20, 0, 'You open the account. After 1 year your Ksh 2,000 grows to Ksh 2,140 — Ksh 140 earned for free! You also build a banking history for future loans.', 'A savings account is the first building block of a financial identity.'],
            ['Keep money in envelope — banks are risky', 'Distrust of banks', 0, 0, 'You keep the envelope. Your money stays exactly Ksh 2,000 after 1 year — no growth. You miss the banking track record you\'ll need for a loan someday.', 'Banks are insured — your money is safer there than in an envelope at home.', 'ending'],
            ['Open account with Ksh 500, keep Ksh 1,500 accessible', 'Cautious start', 12, 0, 'You open with Ksh 500 minimum. Good start! You build banking history while keeping most funds accessible. Over time you add more to the account.', 'Starting small with a bank account builds habits and history, even if deposits are initially small.'],
        ]);

        $this->scenario($g, 'The Group Contribution (Chama)', 'Your class forms a savings group: 10 members each contribute Ksh 200/week. Each week one member gets the whole pot (Ksh 2,000). You\'re member 7 in the rotation.', '👥', '#d946ef', false, 200, [
            ['Join and commit to weekly Ksh 200 contributions', 'Community savings', 18, 0, 'You join faithfully. When your turn comes in week 7, you receive Ksh 2,000 — which covers your exam registration fee completely!', 'Rotating savings groups (chamas) are powerful tools for accessing lump sums.'],
            ['Join but miss 2 payment weeks', 'Unreliable member', 2, 0, 'You miss 2 weeks. The group votes to remove you before your turn. You lose Ksh 400 you had contributed and get nothing.', 'In group savings, consistency is not optional — it\'s a contractual obligation.', 'ending'],
            ['Don\'t join — you prefer to save alone', 'Solo saver', 5, 0, 'You save alone. After 7 weeks you have Ksh 1,400 vs the Ksh 2,000 the group payout would have given you at that point. Groups accelerate access to capital.', 'Group savings often give you faster access to larger amounts than saving alone.'],
        ]);

        $this->scenario($g, 'The Phone Upgrade Dilemma', 'Your phone works fine but is 2 years old. A newer model costs Ksh 12,000. You could sell your current phone for Ksh 5,000 and save the Ksh 7,000 difference. You have Ksh 20,000 in savings.', '📲', '#06b6d4', false, 20000, [
            ['Keep your working phone — protect savings', 'Financial discipline', 20, 0, 'You keep your phone. Your Ksh 20,000 stays intact and continues earning interest. Your "old" phone still works perfectly for 2 more years.', 'Upgrading working items to keep up with trends is a major drain on savings.'],
            ['Upgrade using savings only (Ksh 12,000)', 'Controlled spending', 10, -12000, 'You upgrade by spending Ksh 12,000. Your savings drop to Ksh 8,000 — you\'ve lost 60% of your savings on a phone. It stings when you see your bank balance.', 'Spending more than 30% of your savings on a single non-essential purchase is risky.'],
            ['Sell old phone (Ksh 5,000) + add Ksh 7,000 savings', 'Trade-up strategy', 15, -7000, 'You sell your old phone and top up Ksh 7,000 from savings. Smart trade-up strategy — you limit the dip to Ksh 7,000 and still have Ksh 13,000 in savings.', 'Selling your current asset to fund an upgrade minimizes the cost of upgrading.'],
        ]);

        $this->scenario($g, 'The Exam Holiday Hustle', 'You have 3 weeks holiday after exams. A neighbour needs someone to water plants and feed cats for Ksh 200/day while they travel for 2 weeks. That\'s Ksh 2,800 for easy work.', '🌱', '#22c55e', false, 0, [
            ['Take the job — earn Ksh 2,800', 'Holiday income', 18, 2800, 'You take the job. 14 days of light work earns you Ksh 2,800. You save Ksh 2,000 and use Ksh 800 to buy textbooks for next term. Perfect holiday hustle!', 'Holidays are income opportunities. Light work now sets you up for a better term.'],
            ['Enjoy the full holiday — rest is important', 'Rest wins', 3, 0, 'You rest all holiday. You come back refreshed but with no money. You borrow Ksh 500 from your mum for term 1 supplies.', 'Rest is important, but a few hours of light work per day can earn significant holiday income.'],
        ]);

        $this->scenario($g, 'The Reselling Hustle', 'You buy popular snacks in bulk from a wholesaler: 100 packets at Ksh 8 each (Ksh 800 total). You sell them at school for Ksh 15 each. If you sell all 100 you make Ksh 700 profit.', '🍿', '#f97316', false, 800, [
            ['Invest Ksh 800 and sell at school consistently', 'Micro-entrepreneur', 22, 700, 'You sell all 100 packets in one week! Ksh 700 profit on Ksh 800 investment — an 87.5% return. You reinvest and scale up. By month end you\'re making Ksh 2,100/week.', 'Buying wholesale and selling retail is the core principle of all trade businesses.'],
            ['Invest Ksh 800 but eat some stock yourself', 'Self-sabotage', 5, 200, 'You eat 20 packets yourself. You only sell 80 (Ksh 1,200). After deducting Ksh 800 cost you make Ksh 400 profit — not Ksh 700. Consuming your own inventory kills margin.', 'Never consume your business inventory unless you account for it as a business expense.'],
            ['Sell only to close friends at a discount', 'Friendship pricing', 8, 300, 'You give friends a deal at Ksh 12 each. You sell all 100 but only make Ksh 400 profit. Underpricing keeps customers happy but shrinks your income.', 'Discounts for friends should be small — your time and capital deserve fair compensation.'],
        ]);
    }

    // ─────────────────────────────────────────────
    // AGES 18–25
    // ─────────────────────────────────────────────
    private function seedYoungAdults(): void
    {
        $g = '18-25';

        $this->scenario($g, 'First Paycheck', 'You just got your first job paying Ksh 25,000/month. Rent is Ksh 8,000, food costs Ksh 5,000 and transport Ksh 3,000. You have Ksh 9,000 left. What do you do first?', '💼', '#6366f1', true, 25000, [
            ['Create a budget and save Ksh 5,000 immediately', 'Budget-first mindset', 25, -16000, 'You automate Ksh 5,000 to M-Shwari on payday before spending anything else. After 6 months you have Ksh 30,000 saved — an emergency fund! You feel incredibly secure.', 'Pay yourself first: save before spending. It\'s the single most powerful financial habit.'],
            ['Pay bills, then spend freely on what\'s left', 'Bill-pay only', 8, -16000, 'You pay all bills (Ksh 16,000) and spend the remaining Ksh 9,000 freely. By month end you have zero saved. Month 2 is the same cycle.', 'Without intentional saving, extra money always disappears into lifestyle inflation.', 'ending'],
            ['Save Ksh 2,500 and invest Ksh 2,500 in MMF', 'Save and invest split', 22, -16000, 'You save Ksh 2,500 (liquid) and invest Ksh 2,500 in a Money Market Fund (earning ~10% p.a.). Smart split — liquidity for emergencies, growth for the future!', 'Splitting savings between a liquid account and an investment fund balances safety and growth.'],
        ]);

        $this->scenario($g, 'The Personal Loan Trap', 'You want a Ksh 30,000 smartphone. Your bank offers a 12-month personal loan at 14% interest. Total repayment: Ksh 34,200. Monthly payment: Ksh 2,850. You earn Ksh 30,000.', '💳', '#ef4444', true, 0, [
            ['Decline loan — save for 4 months instead', 'Patience over debt', 25, 0, 'You save Ksh 7,500/month for 4 months and buy the phone cash — no interest. You saved Ksh 4,200 vs the loan cost. Your credit score is also untouched.', 'Saving to buy eliminates interest charges and builds discipline. Debt always costs more.'],
            ['Take the loan — get the phone now', 'Instant gratification debt', 2, 30000, 'You take the loan. Ksh 2,850/month disappears for 12 months. With rent and food, you are broke every month. One missed payment triggers penalty fees.', 'A personal loan for a depreciating asset like a phone is poor financial decision making.', 'ending'],
            ['Take the loan but plan strictly to repay early', 'Managed debt', 12, 30000, 'You take the loan and pay Ksh 4,000/month instead of Ksh 2,850. You clear it in 8 months, saving Ksh 1,200 in interest. Debt managed well can work.', 'If you must borrow, always overpay to clear debt early and minimize interest.'],
        ]);

        $this->scenario($g, 'The Roommate Decision', 'Your single room in Nairobi costs Ksh 8,000/month. A colleague suggests sharing a 2-bedroom at Ksh 16,000 (Ksh 8,000 each). The shared house is bigger and in a better area.', '🏠', '#10b981', false, 0, [
            ['Share the house — same cost, better quality', 'Smart sharing', 18, 0, 'You share the house. Same Ksh 8,000 rent but you get a bedroom in a secure estate with a kitchen and living room. Your quality of life improves at no extra cost.', 'Sharing resources can dramatically improve quality of life at the same cost.'],
            ['Stay in single room — value independence', 'Solo living', 8, 0, 'You keep your single room. Privacy is important but you\'re confined to one tiny space. You later realize a better lifestyle was available at the same price.', 'Independence has value but don\'t pay a hidden premium for worse living conditions.'],
            ['Move to a nicer single room for Ksh 12,000 instead', 'Lifestyle upgrade', 5, 0, 'You upgrade to a Ksh 12,000 room. Nice, but Ksh 4,000 extra/month adds up to Ksh 48,000/year more. That\'s a laptop, a month\'s emergency fund, or a flight home.', 'Every lifestyle upgrade has an opportunity cost — what else could that money do for you?'],
        ]);

        $this->scenario($g, 'The HELB Repayment', 'You received HELB (student loan) of Ksh 40,000 per year during 4 years of university — total Ksh 160,000. Now employed, HELB is sending you repayment notices. Monthly installment: Ksh 1,200.', '🎓', '#f59e0b', false, 0, [
            ['Start repaying Ksh 1,200/month immediately', 'Responsible repayment', 20, -1200, 'You start repaying. After 11 years you\'ll have paid it off and your credit record is clean. HELB issues a clearance certificate you need for future government contracts.', 'Student loan repayment builds credit history and keeps you eligible for government opportunities.'],
            ['Ignore HELB notices for now', 'Avoidance', 0, 0, 'You ignore notices. After 6 months HELB adds penalties and reports you to CRB (Credit Reference Bureau). Your credit score drops — banks refuse your future loan applications.', 'Ignoring debt never makes it disappear. It grows and damages your credit score.', 'ending'],
            ['Negotiate a reduced installment with HELB', 'Proactive negotiation', 15, -600, 'You call HELB and explain your situation. They allow Ksh 600/month for 2 years then full payments. You repay without stress.', 'Government lenders often have hardship repayment plans. Always call before defaulting.'],
        ]);

        $this->scenario($g, 'The Emergency Fund Test', 'You\'ve been saving Ksh 3,000/month for 6 months — you have Ksh 18,000 saved. Then your laptop breaks and repair costs Ksh 15,000. Without a laptop you can\'t work remotely.', '💻', '#8b5cf6', false, 18000, [
            ['Use emergency fund to repair laptop — get back to work', 'Emergency fund purpose', 20, -15000, 'You use Ksh 15,000 from your emergency fund to fix the laptop. You\'re back to work in 2 days. This is EXACTLY what an emergency fund is for. You rebuild the fund over 5 months.', 'An emergency fund removes the need for debt during unexpected crises. This is its entire purpose.'],
            ['Take a quick loan at 20% monthly interest to avoid touching savings', 'Wrong debt', 0, 0, 'You take a Ksh 15,000 loan at 20%/month (predatory apps). One month later you owe Ksh 18,000. Your emergency fund sits untouched while debt destroys you.', 'An emergency fund exists SPECIFICALLY so you don\'t need expensive emergency debt.', 'ending'],
            ['Buy a second-hand laptop for Ksh 8,000', 'Budget solution', 15, -8000, 'You find a reliable second-hand laptop for Ksh 8,000. You\'re working again and still have Ksh 10,000 in your emergency fund. Practical problem-solving!', 'A good enough solution at a lower cost is often smarter than the perfect solution at full price.'],
        ]);

        $this->scenario($g, 'Money Market Fund vs Crypto', 'You have Ksh 50,000 to invest. Your friend swears crypto will 10x in 3 months. A CIC Money Market Fund offers a safer 10% per year. You\'re 23 years old.', '📈', '#14b8a6', false, 50000, [
            ['Invest in MMF — safe, steady 10% per year', 'Conservative investor', 20, 0, 'Your Ksh 50,000 earns 10% over 12 months — Ksh 5,000 profit. Modest but guaranteed. Three months later your friend\'s crypto drops 70% (Ksh 35,000 loss). You celebrate your boring choice.', 'For money you can\'t afford to lose, predictable returns beat speculative ones every time.'],
            ['Put all Ksh 50,000 in crypto', 'High-risk gamble', 0, 0, 'Crypto drops 70% three months later. Your Ksh 50,000 is now Ksh 15,000. You lost Ksh 35,000 in 90 days. Your savings for rent are gone.', 'High-risk investments with money you need are financial gambling, not investing.', 'ending'],
            ['Ksh 40,000 in MMF + Ksh 10,000 in crypto', 'Balanced risk', 18, 0, 'Crypto drops 70% so your Ksh 10,000 becomes Ksh 3,000 (Ksh 7,000 loss). But your Ksh 40,000 in MMF earns Ksh 4,000. Net result: near break-even. Diversification protected you.', 'The 90/10 rule: put 90% in safe investments, 10% in speculative ones. Never bet your safety net.'],
        ]);

        $this->scenario($g, 'The Business Idea', 'You have a side hustle selling chapati near your house, earning Ksh 3,000/month profit. A friend suggests you scale up — rent a kiosk for Ksh 5,000/month to sell more. Risk or opportunity?', '🥙', '#f97316', false, 0, [
            ['Rent the kiosk — invest in growth', 'Entrepreneur mindset', 22, -5000, 'You rent the kiosk. With more visibility your chapati sales triple. Monthly profit grows from Ksh 3,000 to Ksh 12,000. Your Ksh 5,000 rent investment pays off 3x over.', 'Investing in infrastructure to scale a proven business is a calculated and rewarding risk.'],
            ['Keep the informal setup — no overhead', 'Steady but small', 10, 0, 'You keep things small. Steady Ksh 3,000/month profit. But when the neighbourhood is upgraded, a better-equipped competitor opens and takes 60% of your customers.', 'Staying informal limits growth. Formalising and investing in your business builds competitive resilience.'],
            ['Find a partner to share kiosk costs', 'Risk-sharing', 18, -2500, 'You find a business partner — each pays Ksh 2,500/month for the kiosk. Sales grow and profit per person becomes Ksh 8,000/month. Partnership reduces risk while enabling growth.', 'Business partnerships can share costs and risk while still significantly growing your income.'],
        ]);

        $this->scenario($g, 'The NHIF Decision', 'Your employer doesn\'t deduct NHIF for you. Monthly NHIF contribution is Ksh 500. You\'re young and healthy — you haven\'t visited a hospital in 3 years. Is it worth registering?', '🏥', '#0ea5e9', false, 0, [
            ['Register for NHIF — Ksh 500/month', 'Health insurance first', 20, -500, 'You register. 14 months later you get an emergency appendix removal — hospital bill: Ksh 80,000. NHIF covers Ksh 70,000. You only pay Ksh 10,000 vs Ksh 80,000.', 'Health insurance is worth every shilling. Medical emergencies are not if — they are when.'],
            ['Skip NHIF — you\'re young and healthy', 'Risky optimism', 0, 0, 'You skip it. Two years later you break your leg in an accident. Hospital bill: Ksh 45,000. You have no cover and drain your entire emergency fund.', 'Young and healthy is not the same as immune to medical emergencies.', 'ending'],
        ]);

        $this->scenario($g, 'The Salary Negotiation', 'You got a job offer for Ksh 40,000/month. Market rate for your role is Ksh 50,000–55,000 based on Glassdoor. The HR says "this is our standard offer."', '🤝', '#a855f7', false, 0, [
            ['Negotiate — ask for Ksh 50,000 with supporting data', 'Confident negotiation', 25, 0, 'You show your research and ask for Ksh 50,000. HR counter-offers at Ksh 47,000. You accept. That extra Ksh 7,000/month = Ksh 84,000/year extra throughout your career.', 'Salary negotiation is the highest ROI action you can take. Employers always expect some negotiation.'],
            ['Accept Ksh 40,000 — grateful for any offer', 'Under-value yourself', 2, 0, 'You accept without negotiating. Over 5 years, you earn Ksh 420,000 less than a colleague who negotiated. The "grateful" mindset costs you hundreds of thousands.', 'Accepting the first offer without negotiating costs you significant cumulative income.', 'ending'],
            ['Ask for Ksh 55,000 — aim high', 'Ambitious ask', 18, 0, 'You ask for Ksh 55,000. HR is initially taken aback but counters at Ksh 48,000. You settle at Ksh 50,000. Aiming high anchors the negotiation in your favour.', 'Ask for more than you expect — anchoring high always results in a better final outcome.'],
        ]);

        $this->scenario($g, 'The SACCO Membership', 'A teacher\'s SACCO in your county lets young professionals join with Ksh 2,000 shares/month. After 12 months you can take a loan at 1% per month. Commercial banks charge 13% per year.', '🏛️', '#06b6d4', false, 0, [
            ['Join SACCO — invest Ksh 2,000/month', 'SACCO member', 22, -2000, 'After 12 months you have Ksh 24,000 in shares. You take a Ksh 60,000 loan at 1%/month (vs bank\'s 13%/year) to start a business. SACCO membership unlocks cheap capital.', 'SACCOs offer the cheapest credit in Kenya. Membership is one of the smartest financial decisions you can make.'],
            ['Invest in unit trusts instead — higher returns', 'Alternative savings', 15, -2000, 'You invest Ksh 2,000/month in unit trusts at 12% p.a. After 12 months: Ksh 25,300 (slightly more). But you don\'t have access to a Ksh 60,000 loan at 1%/month.', 'Investment returns matter but so does access to cheap credit. SACCOs offer both.'],
        ]);
    }

    // ─────────────────────────────────────────────
    // AGES 26+
    // ─────────────────────────────────────────────
    private function seedAdults(): void
    {
        $g = '26+';

        $this->scenario($g, 'The Mortgage vs Rent Decision', 'You earn Ksh 80,000/month. Your rent is Ksh 15,000. A mortgage for a 2-bed in Athi River costs Ksh 18,000/month. After 20 years the house is yours. Rent is Ksh 15,000 forever with no ownership.', '🏡', '#6366f1', true, 0, [
            ['Take the mortgage — build equity', 'Homeowner path', 25, -18000, 'You take the mortgage. Ksh 3,000/month more than rent, but after 20 years you own an asset worth Ksh 6 million. Your rent-paying friends have spent Ksh 3.6 million with nothing to show.', 'A mortgage is forced savings. Every payment builds equity in an appreciating asset.'],
            ['Continue renting — maintain flexibility', 'Renter mindset', 8, -15000, 'You rent indefinitely. Flexibility is real but after 20 years you\'ve paid Ksh 3.6 million in rent and own nothing. A home owner has a Ksh 6M asset.', 'Renting is not inherently bad but long-term renters sacrifice wealth accumulation.'],
            ['Rent and invest the Ksh 3,000 difference in NSE stocks', 'Rent + invest hybrid', 20, -15000, 'You rent and invest Ksh 3,000/month in NSE stocks for 20 years at 12% annual growth. Your portfolio grows to Ksh 2.7 million. Not as good as home ownership but better than just renting.', 'If renting, always invest the difference between rent and mortgage cost — otherwise you lose on both fronts.'],
        ]);

        $this->scenario($g, 'The Insurance Gap', 'You\'re 30, earning Ksh 80,000 with a spouse and 1 child. You have no life insurance. A term life policy for Ksh 5 million cover costs Ksh 3,000/month. An investment fund rep is pushing you to buy an endowment policy at Ksh 8,000/month instead.', '🛡️', '#ef4444', true, 0, [
            ['Buy term insurance (Ksh 3,000/month) and invest the rest separately', 'Smart insurance', 22, -3000, 'You buy cheap term insurance for pure protection and invest the Ksh 5,000 difference in a unit trust. In 20 years your policy pays if you die AND your investment fund is worth more than any endowment.', 'Buy pure protection (term) and invest separately. Never mix insurance and investment.'],
            ['Buy the endowment policy — one product does both', 'Endowment mistake', 5, -8000, 'You buy the endowment at Ksh 8,000. In 20 years you receive a lump sum — BUT it\'s less than if you had invested the same amount in index funds. You overpaid for bundled products.', 'Bundled insurance-investment products almost always underperform separate products.'],
            ['Skip insurance entirely — invest all Ksh 8,000', 'Protection gap', 0, 0, 'You invest all Ksh 8,000/month. 3 years later you die in an accident. Your family has Ksh 288,000 in the fund — not Ksh 5 million. They cannot pay school fees or clear the mortgage.', 'No amount of investments can replace life insurance when dependants are involved.', 'ending'],
        ]);

        $this->scenario($g, 'The Rental Property Decision', 'You\'ve saved Ksh 800,000. A plot in Ruiru costs Ksh 700,000. Building will cost Ksh 300,000 more (total Ksh 1M needed). A property developer offers to build a 2-unit rental for Ksh 1.2M and charge rent of Ksh 12,000 per unit = Ksh 24,000/month gross income.', '🏘️', '#10b981', false, 800000, [
            ['Secure the plot now and save for the build over 2 years', 'Stage your investment', 22, -700000, 'You buy the plot for Ksh 700,000. Over 2 years you save Ksh 300,000 for construction. You build and start earning Ksh 24,000/month passive income — Ksh 288,000/year. Land + rental = 36% ROI.', 'Real estate investment staged in phases (land first, build later) reduces risk by spreading capital deployment.'],
            ['Wait and save the full Ksh 1.2M before starting', 'All cash strategy', 15, 0, 'You wait and save. 3 years later you have Ksh 1.2M and start building. But the plot you had your eye on has doubled to Ksh 1.4M. You can no longer afford it. Early land purchase locks in today\'s price.', 'Property prices rise continuously. Buying land early locks in today\'s lower price for tomorrow\'s build.'],
            ['Invest Ksh 800,000 in NSE shares instead', 'Stock market path', 18, 0, 'You invest in NSE. Over 5 years your portfolio grows 60% to Ksh 1.28M. You now have enough for the plot — but land prices have risen too. You broke even relative to the plot buyer.', 'Stocks and property both grow wealth. The choice depends on your income, timeline, and risk tolerance.'],
        ]);

        $this->scenario($g, 'Retirement Planning at 35', 'You\'re 35 and realise you\'ve saved nothing for retirement. NSSF only pays Ksh 6,000/month after retirement. Your current lifestyle needs Ksh 60,000/month. A personal pension scheme requires Ksh 5,000/month contribution.', '👴', '#f59e0b', false, 0, [
            ['Start pension immediately — Ksh 5,000/month', 'Start now', 25, -5000, 'You start at 35 with Ksh 5,000/month. At 10% annual growth, by 60 you\'ll have Ksh 5.9 million — a monthly drawdown of Ksh 49,000 for 10 years. You can retire comfortably.', 'Starting retirement savings at 35 vs 45 can double your retirement pot due to compound interest.'],
            ['Wait until 45 — you have time', 'Delayed start', 3, 0, 'You wait until 45. Same Ksh 5,000/month for 15 years = only Ksh 2.1M at retirement. Half the pot for starting just 10 years later. Compound interest rewards early starters massively.', 'Every decade you wait to start saving for retirement halves your final pot.', 'ending'],
            ['Max out NSSF contributions plus Ksh 5,000 personal pension', 'Dual contribution', 22, -7000, 'You contribute to both NSSF and a personal pension. At 60: NSSF gives Ksh 6,000/month plus your pension fund gives Ksh 49,000/month. Total: Ksh 55,000/month. Comfortable retirement secured!', 'Maximising both formal (NSSF) and personal pension contributions creates a diversified retirement income.'],
        ]);

        $this->scenario($g, 'The School Fees Investment', 'You have a 5-year-old child. Secondary school fees will be Ksh 180,000 in 8 years. You can start a children\'s education plan now — Ksh 2,000/month for 8 years covers the fees with growth.', '📖', '#8b5cf6', false, 0, [
            ['Start education plan now — Ksh 2,000/month', 'Education investment', 22, -2000, 'You start immediately. At 8% annual return for 8 years, Ksh 2,000/month grows to Ksh 238,000 — more than enough for secondary school fees. No stress, no loans needed.', 'Education planning started early makes school fees an investment, not a crisis.'],
            ['Save Ksh 2,000/month in regular savings account (low interest)', 'Standard savings', 12, -2000, 'You save Ksh 2,000/month in a 3% account. After 8 years: Ksh 201,000 — slightly short. You top up Ksh 20,000 at the start of term. Education savings beats not saving.', 'Even low-interest savings for education beats scrambling for fees at the last minute.'],
            ['Invest in your business now — pay fees from profits', 'Business-funded education', 15, 0, 'You invest in your business instead. If business grows well, you can fund fees from profits. But if business has a bad year, fees become a crisis. Single-source funding is risky.', 'Diversify education funding — don\'t rely solely on business performance for fee payment.'],
        ]);

        $this->scenario($g, 'The Retrenchment Shock', 'Your company announces retrenchment and you\'re offered a package of Ksh 180,000 (6 months salary). You have a mortgage of Ksh 18,000/month and family expenses of Ksh 30,000/month. Total monthly burn: Ksh 48,000.', '⚠️', '#dc2626', false, 180000, [
            ['Budget strictly — 3 months expenses + 3 months cushion', 'Crisis management', 22, 0, 'You immediately cut all non-essential spending. Ksh 180,000 covers 3.75 months at Ksh 48,000 burn. You find a new job in 2 months. Emergency fund buys critical time.', 'A retrenchment package is a runway, not a windfall. Every month you don\'t spend is another month of runway.'],
            ['Invest the package in stocks — they\'re low right now', 'Wrong timing', 0, 0, 'You invest Ksh 180,000 in stocks. Stocks drop further for 4 months. Meanwhile your mortgage falls into arrears (3 months = Ksh 54,000 penalties). Bank threatens repossession.', 'Never invest your survival fund. Liquidity is paramount during a financial crisis.', 'ending'],
            ['Use Ksh 100,000 to start a business, live off Ksh 80,000', 'Entrepreneurship leap', 18, 0, 'You start a business with Ksh 100,000. It takes 4 months to generate income. Ksh 80,000 covers 1.6 months. Month 3 you\'re struggling to pay mortgage. High-risk but entrepreneurship eventually succeeds.', 'Using retrenchment to start a business is brave but requires a solid plan and longer runway.'],
        ]);

        $this->scenario($g, 'The Tax Compliance Check', 'You have a rental income of Ksh 40,000/month and forgot to file MRI (Monthly Rental Income) tax. KRA sends a notice for unpaid tax of Ksh 18,000 plus Ksh 9,000 penalty = Ksh 27,000 total.', '📋', '#0ea5e9', false, 0, [
            ['Pay the full Ksh 27,000 and register for MRI tax going forward', 'Compliance corrected', 18, -27000, 'You pay the penalty and register for 10% MRI tax (Ksh 4,000/month). Going forward you\'re compliant and protected from larger penalties or asset seizure.', 'Tax penalties grow rapidly. Resolve compliance issues immediately — they never go away on their own.'],
            ['Ignore KRA — hope they forget', 'Tax avoidance danger', 0, 0, 'You ignore the notice. 6 months later KRA freezes your bank accounts and attaches your rental property. Fines have grown to Ksh 65,000. You lose tenants due to the disruption.', 'KRA never forgets. Tax avoidance always leads to larger penalties and potential asset seizure.', 'ending'],
            ['Apply for a waiver through KRA amnesty programme', 'Smart compliance', 22, 0, 'KRA occasionally runs amnesty programmes that waive penalties. You apply and get the Ksh 9,000 penalty waived — paying only Ksh 18,000. You save Ksh 9,000 and become compliant.', 'Always check for KRA amnesty programmes before paying full penalties. Waivers are available to first-time defaulters.'],
        ]);

        $this->scenario($g, 'The Family Obligation Trap', 'Your brother needs Ksh 150,000 to start a business. He has no collateral. He asks you to be a guarantor for his bank loan. If he defaults, you pay his debt.', '👨‍👩‍👧', '#d946ef', false, 0, [
            ['Decline guarantorship — offer smaller direct help', 'Protect your credit', 20, 0, 'You decline but offer Ksh 20,000 as a direct gift toward the business. You protect your credit score. If the business fails, you lose Ksh 20,000 — not Ksh 150,000.', 'Never guarantee a loan you cannot afford to repay yourself. Your credit score is your financial identity.'],
            ['Sign as guarantor to support family', 'Family over finance', 3, 0, 'You sign. 8 months later your brother\'s business struggles and he defaults. The bank comes after you for Ksh 150,000 plus 14% interest. Your mortgage application is rejected due to your damaged credit score.', 'Loan guarantorship passes 100% of the financial risk to you with none of the ownership benefits.', 'ending'],
            ['Help the brother make a proper business plan and apply himself', 'Mentor, don\'t guarantee', 18, 0, 'You spend 2 weekends helping your brother build a proper business plan. He applies for a KCB Biashara loan independently with the strong plan. He gets the loan and successfully repays it.', 'Helping someone become financially capable is more valuable than exposing yourself to their debt risk.'],
        ]);

        $this->scenario($g, 'The Investment Club', 'You\'re invited to join a Chama investment club of 10 members. Each contributes Ksh 5,000/month. The club invests in real estate. In 2 years (Ksh 120,000/member) the club buys a plot worth Ksh 1.2M.', '🤝', '#22c55e', false, 5000, [
            ['Join and commit to Ksh 5,000/month for 24 months', 'Long-term group investor', 25, -5000, 'After 24 months the club buys a Ksh 1.2M plot. Within 3 more years the plot doubles to Ksh 2.4M. Your Ksh 120,000 investment is now worth Ksh 240,000. Group investment works!', 'Investment clubs pool capital to access real estate that individual members cannot afford alone.'],
            ['Join but withdraw after 12 months', 'Early exit', 5, 0, 'You exit at 12 months and receive your Ksh 60,000 back. But the plot is bought and doubles in 3 years. Your share would have been Ksh 240,000 — you left Ksh 180,000 profit on the table.', 'Long-term commitment to group investments is critical — early exit sacrifices the compounding benefit.'],
            ['Don\'t join — invest Ksh 5,000/month individually in stocks', 'Go solo', 15, -5000, 'You invest in NSE stocks instead. After 5 years at 12% p.a. you have Ksh 405,000 vs the club\'s Ksh 240,000 per share. Individual stock investment slightly outperformed this club.', 'Individual stock investment can outperform group real estate — compare options carefully before choosing.'],
        ]);

        $this->scenario($g, 'The Business Formalisation', 'Your catering business earns Ksh 120,000/month but operates informally. Formalising (business registration + KRA PIN + VAT) costs Ksh 25,000 upfront. Hotels and corporates only work with formal businesses.', '📜', '#14b8a6', false, 0, [
            ['Register the business and become formal', 'Formalise for growth', 22, -25000, 'You spend Ksh 25,000 on registration. Within 3 months you land a corporate contract worth Ksh 80,000/month. Your monthly income grows to Ksh 200,000. The Ksh 25,000 investment pays back in 2 weeks.', 'Business formalisation unlocks contracts with institutions that only engage registered companies.'],
            ['Stay informal — avoid taxes and paperwork', 'Informal comfort', 3, 0, 'You stay informal. A hotel offers a Ksh 200,000/month contract — but requires a Certificate of Incorporation and KRA compliance. You lose the contract. Informal status caps your income ceiling.', 'Informal businesses are permanently excluded from corporate, government, and institutional contracts.', 'ending'],
            ['Formalise using a partner who already has a registered company', 'Joint venture', 15, 0, 'You joint-venture with a registered friend. You execute the contracts under their company and split profits 70/30. You grow quickly while saving the registration cost — but you share margin.', 'Strategic partnerships can accelerate formalisation, though profit-sharing reduces your personal take.'],
        ]);
    }
}
