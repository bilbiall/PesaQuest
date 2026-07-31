<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Node;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AdultScenariosSeeder — 25 Kenya-specific scenarios for the 26+ age group.
 * Topics: SACCOs, debt, estate planning, digital lending, NSE, side income,
 * marriage finance, property due diligence, crypto, business growth and more.
 */
class AdultScenariosSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seed();
        });
    }

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
        $meta  = $startBalance ? ['starting_balance' => $startBalance] : null;
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
            $rMeta  = !empty($c[5]) ? ['final_lesson' => $c[5]] : null;
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

    private function seed(): void
    {
        $g = '26+';

        // 1 — SACCO LOAN STRATEGY
        $this->scenario($g, 'The SACCO Loan Opportunity', 'Your SACCO offers a development loan at 1%/month on reducing balance. A bank is offering a personal loan at 2.5%/month. You need Ksh 200,000 to renovate your rental unit. SACCO requires 3-month waiting period.', '🏛️', '#06b6d4', true, 0, [
            ['Wait 3 months for the SACCO loan at 1%/month', 'Patient and disciplined', 25, 0, 'You wait. SACCO loan at 1%/month: total interest on Ksh 200,000 over 24 months = Ksh 26,000. Bank loan would have cost Ksh 65,000. You save Ksh 39,000 in interest by being patient.', 'SACCO loans are the cheapest credit in Kenya. Waiting 3 months to access them saves tens of thousands.'],
            ['Take the bank loan now — can\'t afford to wait', 'Expensive impatience', 5, 0, 'You take the bank loan at 2.5%/month. After 24 months you\'ve paid Ksh 65,000 in interest — Ksh 39,000 more than the SACCO option. The renovation is done, but at a much higher cost.', 'Impatience with borrowing is expensive. Always exhaust cheaper options before approaching commercial lenders.'],
            ['Defer renovation and save Ksh 8,000/month for 25 months', 'Self-fund the renovation', 18, 0, 'You save instead of borrow. After 25 months you have Ksh 200,000 in cash. You renovate with zero interest cost. Rental income increases by Ksh 5,000/month from the improved unit.', 'Self-funding through disciplined saving eliminates interest costs entirely and builds financial muscle.'],
        ]);

        // 2 — MOBILE LOAN TRAP
        $this->scenario($g, 'The Digital Lending Spiral', 'You took Ksh 5,000 on M-Shwari at 7.5% interest (30 days). When it was due you couldn\'t pay so you borrowed Ksh 7,000 on Fuliza to repay it. Now Fuliza interest is accumulating at Ksh 30/day. Your salary is 15 days away.', '📱', '#ef4444', true, 0, [
            ['Borrow from a friend or family to clear Fuliza immediately', 'Cut the compounding', 22, 0, 'You borrow Ksh 7,500 from your aunt and clear Fuliza entirely. You save ~Ksh 450 in interest (15 days × Ksh 30). You repay your aunt on salary day — no interest, no compound damage.', 'Fuliza and overdraft products compound daily. Break the cycle with zero-interest social borrowing — repay promptly.'],
            ['Wait for salary — 15 more days of Fuliza interest', 'Procrastinate the pain', 3, 0, 'You wait. Fuliza charges Ksh 30/day for 15 days = Ksh 450 extra. Your credit score on the M-Pesa credit bureau drops. Future M-Shwari limits are reduced.', 'Daily-compounding debt like Fuliza punishes every day of delay. Always prioritise clearing it first.', 'ending'],
            ['Sell something or do a side gig to raise Ksh 7,500', 'Hustle to escape', 20, 0, 'You sell old clothes on OLX and do 3 days of freelance work, raising Ksh 8,000. You clear Fuliza 12 days early (saving Ksh 360) and have Ksh 500 left over.', 'When cash is tight, activate income (gig work, sell assets) rather than layering more debt.'],
        ]);

        // 3 — EMERGENCY FUND SIZING
        $this->scenario($g, 'The Emergency Fund Question', 'You earn Ksh 75,000/month and have Ksh 40,000 in savings. Your monthly expenses are Ksh 55,000. A financial advisor says you need 3–6 months of expenses (Ksh 165,000–330,000) in liquid savings before investing.', '🛡️', '#10b981', false, 40000, [
            ['Build emergency fund to Ksh 165,000 (3 months) before investing', 'Safety-first', 22, 0, 'You focus on building to Ksh 165,000 over 5 months (saving Ksh 25,000/month). Then you start investing. When your car breaks down (Ksh 45,000 repair), the fund absorbs the shock with zero debt.', 'An emergency fund is not savings — it is insurance against life\'s certainties. Build it before investing.'],
            ['Start investing now — emergency fund feels like wasted money', 'Invest-first mindset', 5, 0, 'You invest Ksh 15,000/month. When your employer delays payroll by 3 weeks and the rent is due, you have no liquid cash. You sell investments at a loss to cover expenses.', 'Investing without an emergency fund forces you to sell investments at the worst possible time — in crisis.', 'ending'],
            ['Keep 2 months (Ksh 110,000) and invest the rest', 'Balanced approach', 18, 0, 'You build to Ksh 110,000 and start investing. A Ksh 90,000 hospital bill (your child needs surgery) stretches the fund thin but holds. You pause investing for 2 months to replenish.', 'A 2-month emergency fund is better than nothing but leaves you exposed to larger shocks. Work up to 3–6 months.'],
        ]);

        // 4 — PROPERTY DUE DILIGENCE
        $this->scenario($g, 'The Land Title Shortcut', 'You\'ve found a half-acre plot in Kiambu for Ksh 600,000. The seller says they can close in 2 days if you pay cash — no need for a lawyer. Your cousin says the same plot next to it went for Ksh 900,000 — it looks like a steal.', '📜', '#f59e0b', false, 600000, [
            ['Insist on a lawyer, title search, and 30 days to close', 'Due diligence first', 25, 0, 'You insist on a proper process. The title search reveals the plot has a caution registered on it (a court dispute). You walk away. The next buyer without a search loses Ksh 600,000 in a fraud.', 'There is no such thing as a genuine rush on land deals. Pressure to close fast is the oldest fraud signal.'],
            ['Pay in cash to secure the deal quickly', 'Speed over safety', 0, -600000, 'You pay. Two months later you discover the seller used a forged title. The true owner has been abroad. You\'ve lost Ksh 600,000 and face a multi-year court case to recover it.', 'Kenya has widespread land fraud. Every land deal requires an official title search at the Ministry of Lands before any payment.', 'ending'],
            ['Pay a 10% deposit (Ksh 60,000) to hold it for 30 days', 'Partial commitment', 8, -60000, 'You pay Ksh 60,000 deposit. During due diligence the title search reveals encumbrances. The seller refuses to refund your deposit. You lose Ksh 60,000 but not Ksh 600,000.', 'Deposits without proper agreements offer limited protection. Never pay even a deposit without a witnessed sale agreement.'],
        ]);

        // 5 — CRYPTO TEMPTATION
        $this->scenario($g, 'The Crypto Windfall Pitch', 'A friend shows you a WhatsApp screenshot: he turned Ksh 50,000 into Ksh 300,000 in 2 months on a crypto platform you\'ve never heard of. He offers to refer you — the minimum investment is Ksh 100,000.', '₿', '#f97316', false, 0, [
            ['Invest Ksh 100,000 to try the platform', 'Chasing easy returns', 0, 0, 'You transfer Ksh 100,000. For 3 weeks the dashboard shows stellar returns. When you try to withdraw, the platform demands a "tax clearance fee" of Ksh 20,000. You pay. Then they disappear. You\'ve lost Ksh 120,000.', 'Platforms promising 300–600% returns in weeks are almost always scams. Verify any platform on the CMA Kenya website before investing a single shilling.', 'ending'],
            ['Research the platform on CMA Kenya website first', 'Verify before investing', 22, 0, 'You check the Capital Markets Authority website. The platform is not licensed. You decline. Your friend eventually loses his "profits" too — the platform runs away with deposits after 3 months.', 'Any investment platform must be licensed by the CMA or CBK. If it is not listed on their websites, it is not legitimate.'],
            ['Invest a small amount (Ksh 10,000) to test', 'Small test bet', 5, -10000, 'You test with Ksh 10,000. The dashboard shows impressive gains. Emboldened, your friend invests Ksh 200,000 — and loses it all when the platform disappears. Your Ksh 10,000 is gone too.', 'Even small amounts in fraudulent schemes are lost. Testing fraud with "small" money normalises the relationship with scammers.'],
        ]);

        // 6 — SIDE INCOME STRUCTURING
        $this->scenario($g, 'The Freelance Tax Question', 'Your corporate salary is Ksh 80,000/month. You\'ve started making Ksh 30,000/month freelancing on Upwork. Your employer doesn\'t know. Should you declare this income to KRA?', '💻', '#6366f1', false, 30000, [
            ['Declare all freelance income on your annual return', 'Tax compliant', 22, 0, 'You declare it. KRA taxes freelance income separately under turnover tax at 3% for amounts under Ksh 1M/year. You pay Ksh 10,800 in annual tax — a small cost for full compliance and the ability to show official income for loans.', 'Undeclared income blocks access to mortgages and business loans. Compliance costs less than it seems.'],
            ['Don\'t declare — it\'s too small for KRA to notice', 'Unreported income', 3, 0, 'You don\'t declare. 2 years later KRA runs a data match between M-Pesa and Upwork payments. They send a notice for Ksh 72,000 in back taxes and Ksh 36,000 in penalties — total Ksh 108,000.', 'KRA has increasingly sophisticated data-matching tools. Undeclared income is a ticking clock, not a free pass.', 'ending'],
            ['Restructure as a registered freelance business for tax benefits', 'Smart structuring', 25, 0, 'You register a sole proprietorship. Your Ksh 30,000/month = Ksh 360,000/year freelance income. After legitimate deductions (internet, equipment, home office), taxable income drops to Ksh 180,000. Tax: Ksh 18,000 — half of the alternative.', 'Business registration enables legitimate expense deductions that dramatically reduce your tax burden on side income.'],
        ]);

        // 7 — JOINT ACCOUNTS AFTER MARRIAGE
        $this->scenario($g, 'The Joint Finances Decision', 'You\'ve just married. You earn Ksh 90,000/month and your spouse earns Ksh 60,000/month. A financial counsellor presents three systems: fully joint accounts, fully separate accounts, or the "mine + yours + ours" model.', '💍', '#ec4899', false, 0, [
            ['Fully joint — merge all income and expenses', 'One pot', 15, 0, 'You merge everything. Decision-making becomes slow — every purchase needs discussion. When your spouse wants to send Ksh 10,000 to their parents, it causes friction. Full merging suits couples with identical spending values.', 'Fully joint finances work best when both partners have identical financial habits and complete trust. Friction is common early on.'],
            ['Keep all finances separate — split bills 50/50', 'Fully separate', 8, 0, 'You split all bills equally. On Ksh 60,000 vs Ksh 90,000, a 50/50 split feels unfair to your spouse. Common goals (house deposit, holiday) require constant negotiation with no shared fund.', 'Fully separate finances create fairness challenges in couples with unequal incomes and no shared savings vehicle.'],
            ['Mine + yours + ours model — contribute proportionally to a joint account', 'Structured hybrid', 25, 0, 'You each contribute 40% of income to a joint account for shared expenses and savings (you: Ksh 36,000; spouse: Ksh 24,000 = Ksh 60,000/month combined). Each keeps their remainder as personal money. All expenses, saving, and investing are funded proportionally.', 'The proportional joint account model respects income differences, maintains personal autonomy, and still builds shared wealth effectively.'],
        ]);

        // 8 — WILL & ESTATE PLANNING
        $this->scenario($g, 'The No-Will Risk', 'You\'re 38 with a house, car, savings, and two children. A colleague\'s sudden death leaves his family in a legal battle because he died intestate (no will). Writing a will costs Ksh 15,000 with a lawyer.', '⚖️', '#8b5cf6', false, 0, [
            ['Write a comprehensive will — name beneficiaries and guardians', 'Protected estate', 25, -15000, 'You write the will, naming your spouse as primary beneficiary, your children\'s guardian, and a trusted friend as executor. When you pass years later, the estate transfer takes 3 weeks instead of 3 years.', 'A will costs Ksh 15,000 to write. Not having one can cost your family 3+ years in court and hundreds of thousands in legal fees.'],
            ['Designate beneficiaries on your bank and insurance accounts only', 'Partial protection', 12, 0, 'You update beneficiaries on financial accounts. But your house (land title) and car logbook have no beneficiaries — these require probate court, a process that can take 2+ years without a will.', 'Account beneficiaries only cover liquid assets. Land, cars, and businesses require a will to transfer cleanly.'],
            ['Wait until you\'re older to write a will', 'Tomorrow thinking', 0, 0, 'You delay. 5 years later you\'re in a road accident. Your assets freeze in a 4-year probate case costing Ksh 200,000 in legal fees. Your children\'s guardian is assigned by a court that doesn\'t know your family.', 'Death does not wait for you to feel old enough. Write your will now — it is the most important Ksh 15,000 you will ever spend.', 'ending'],
        ]);

        // 9 — INHERITANCE DECISION
        $this->scenario($g, 'The Unexpected Inheritance', 'Your grandmother passes and leaves you Ksh 500,000. You have a high-interest personal loan (Ksh 180,000 at 2%/month), no emergency fund, a car you want to upgrade, and a business idea that needs Ksh 150,000 seed capital.', '📦', '#14b8a6', false, 500000, [
            ['Clear the loan first (Ksh 180,000), build emergency fund (Ksh 150,000), invest the rest', 'Debt-first order', 25, -180000, 'You clear the loan (saving Ksh 3,600/month in interest), build 3-month emergency fund (Ksh 150,000), and invest the remaining Ksh 170,000. Your monthly cash flow improves immediately — no debt payments to make.', 'Windfalls should be deployed in order: high-interest debt → emergency fund → investment. Never reorder this.'],
            ['Invest all Ksh 500,000 in stocks — greatest growth potential', 'Invest everything', 5, 0, 'You invest Ksh 500,000. Your loan continues at 2%/month. The following month a medical emergency forces you to sell your new investments at a loss to service the debt. The windfall is gone.', 'Investing while carrying high-interest debt is financially irrational. Clearing 2%/month debt is a guaranteed 24% annual return.', 'ending'],
            ['Upgrade the car (Ksh 200,000 top-up) and invest the rest', 'Treat first', 3, -200000, 'You upgrade the car. The loan continues. The car depreciates immediately by Ksh 40,000. You\'ve used Ksh 200,000 of a windfall to buy a depreciating asset while a 2%/month loan eats your cash flow.', 'Using a windfall on depreciating assets while holding high-interest debt is a double loss.'],
        ]);

        // 10 — NSE DIRECT INVESTING
        $this->scenario($g, 'First Time on the NSE', 'You\'ve opened a CDSC account and want to invest Ksh 50,000 on the Nairobi Securities Exchange for the first time. A broker recommends going all-in on Safaricom shares. A financial advisor recommends a diversified basket across 5 sectors.', '📊', '#6366f1', false, 50000, [
            ['Diversify across 5 sectors: telecoms, banking, energy, manufacturing, REIT', 'Diversified portfolio', 22, -50000, 'You invest Ksh 10,000 each in 5 sectors. When energy stocks drop 20%, your banking and telecoms holdings are up 15%. Net loss: just 5% vs a concentrated investor who loses 20%. Diversification is free risk insurance.', 'On the NSE, sector diversification reduces single-stock or sector risk. Never put all your capital in one company.'],
            ['Go all-in on Safaricom — it\'s the safest blue chip', 'Concentration risk', 8, -50000, 'You buy Safaricom at Ksh 35. The government announces a new telecom entrant and Safaricom drops to Ksh 27. Your Ksh 50,000 is now worth Ksh 38,571 — a 23% loss on the "safest" stock.', 'Even blue-chip stocks carry concentration risk. Diversify even within perceived safe companies.'],
            ['Put Ksh 30,000 in NSE and Ksh 20,000 in a money market fund', 'Hybrid approach', 20, -50000, 'You split: Ksh 30,000 in diversified NSE basket, Ksh 20,000 in a money market fund at 8% p.a. When the stock market dips, your MMF is steady and earns consistent interest. Volatility is buffered.', 'Combining growth assets (equities) with stable income assets (MMFs) reduces portfolio volatility while maintaining growth potential.'],
        ]);

        // 11 — T-BILLS VS SAVINGS
        $this->scenario($g, 'The Savings Account Trap', 'You have Ksh 300,000 sitting in a fixed deposit earning 4% annually. A Treasury Bill (91-day T-Bill) is currently offering 14.5% on the CBK website. A unit trust is offering 10–12% per year.', '💰', '#22c55e', false, 300000, [
            ['Move to T-Bills — government-guaranteed and tax-free for individuals', 'T-Bill investor', 25, 0, 'You invest Ksh 300,000 in 91-day T-Bills at 14.5%. After 91 days you earn Ksh 10,875 net (vs Ksh 3,000 in the fixed deposit over the same period). T-Bills are risk-free and pay 3.5× more.', 'T-Bills are Kenya\'s best risk-free investment for individuals. They are government-guaranteed and consistently outperform bank fixed deposits.'],
            ['Stay in the fixed deposit — it\'s simple and familiar', 'Familiarity bias', 3, 0, 'You leave the money at 4%. Over 5 years at Ksh 300,000, you earn Ksh 66,000. Had you rolled T-Bills at 14%, you\'d have earned Ksh 288,000 — Ksh 222,000 more. Familiarity cost you a fortune.', 'Familiarity with a financial product is not the same as it being the best option. Always compare rates.', 'ending'],
            ['Split: Ksh 150,000 in T-Bills, Ksh 150,000 in unit trust', 'Balanced mix', 20, 0, 'T-Bills provide guaranteed short-term returns; unit trust provides slightly higher long-term potential. Combined, your Ksh 300,000 generates more income than the fixed deposit with manageable risk.', 'A T-Bills + unit trust combination beats fixed deposits on return while maintaining liquidity and diversification.'],
        ]);

        // 12 — SALARY ADVANCE ADDICTION
        $this->scenario($g, 'The Monthly Salary Advance Habit', 'You\'ve been taking a Ksh 10,000 salary advance every month from your employer at 10% interest (Ksh 1,000/month in fees). You\'ve done this for 12 months — spent Ksh 12,000 just in fees. You\'re always broke by the 20th.', '💳', '#dc2626', false, 0, [
            ['Stop advances, build a Ksh 30,000 "payday buffer" fund over 3 months', 'Break the cycle', 25, 0, 'You stop advances cold turkey. Month 1 is tough. Month 2 you save Ksh 10,000. By month 3 you have Ksh 30,000 as a buffer. You never need an advance again and save Ksh 12,000/year in fees.', 'A payday buffer fund is the permanent cure for salary advance addiction. Build it once, benefit forever.'],
            ['Keep the advance — it\'s just Ksh 1,000/month, not worth changing', 'Normalise the fee', 0, 0, 'You keep the habit. Over 5 years you pay Ksh 60,000 in salary advance fees — enough for a solid emergency fund. The "small" fee compounds into a significant wealth leak.', 'Ksh 1,000/month in avoidable fees = Ksh 60,000 over 5 years. There are no small leaks in a budget.', 'ending'],
            ['Negotiate to receive salary in two instalments (mid-month + end-month)', 'Restructure payroll', 18, 0, 'You negotiate with HR. They agree to pay 50% mid-month and 50% end-month. Cash flow smooths out. You no longer need advances. HR is happy — you\'re not the only one who had this request.', 'Structural cash flow problems need structural solutions. Mid-month payroll is a legitimate request and solves the root cause.'],
        ]);

        // 13 — PARENTAL SUPPORT OBLIGATIONS
        $this->scenario($g, 'The Parent Care Budget', 'Your parents are retired with no pension. They need Ksh 15,000/month for rent, food, and medication. You earn Ksh 85,000/month. Your sibling earns Ksh 120,000 but contributes nothing. You\'re covering everything alone.', '👨‍👩‍👦', '#f59e0b', false, 0, [
            ['Have a frank conversation with your sibling about shared responsibility', 'Family accountability', 22, 0, 'You sit your sibling down with a budget. You agree on Ksh 8,000 each per month. Your personal cash flow improves by Ksh 7,000/month (Ksh 84,000/year). The parents are still fully covered.', 'Elder care is a shared family responsibility. Having the difficult conversation is worth Ksh 84,000/year to your household.'],
            ['Continue covering alone — avoid family conflict', 'Silent martyrdom', 3, -15000, 'You continue. Over 5 years you spend Ksh 900,000 on parental support solo. Your sibling saves and builds wealth. You fall behind on your own retirement savings with no one to thank you for it.', 'Unspoken family financial inequity compounds into massive wealth gaps. Have the money conversation — the cost of silence is too high.', 'ending'],
            ['Set a sustainable budget (Ksh 10,000) and communicate your limits', 'Managed contribution', 18, -10000, 'You tell your parents you can reliably provide Ksh 10,000/month — not Ksh 15,000. Your sibling picks up Ksh 5,000. You can only give what your budget allows without self-sacrifice.', 'Sustainable contributions at a lower amount beat unsustainable contributions that lead to burnout and resentment.'],
        ]);

        // 14 — BUSINESS PARTNER EXIT
        $this->scenario($g, 'The Business Partnership Split', 'Your business partner of 3 years wants to exit your joint hardware shop (valued at Ksh 800,000). You have 50/50 ownership. Partner wants Ksh 400,000 cash. You have Ksh 200,000 savings.', '🤝', '#a855f7', false, 200000, [
            ['Negotiate a structured buyout over 12 months (Ksh 33,333/month)', 'Structured exit', 22, 0, 'You negotiate a 12-month buyout at Ksh 33,333/month. The business generates income to fund the payments. After 12 months you own 100% of a Ksh 800,000 business that earns Ksh 50,000/month net profit.', 'Structured buyouts preserve business continuity and avoid the need to liquidate an otherwise profitable business.'],
            ['Liquidate the business and split Ksh 400,000 each', 'Clean break', 5, 400000, 'You liquidate. You both walk away with Ksh 400,000. But the business was generating Ksh 50,000/month profit — you\'ve traded a Ksh 600,000/year income stream for a one-time Ksh 400,000 payment.', 'Liquidating a profitable business at book value is often a bad deal. The income multiple is almost always worth more than the asset value.', 'ending'],
            ['Bring in a third investor to buy out partner\'s share', 'Investor buyout', 18, 0, 'You find an investor willing to pay Ksh 400,000 for a 50% stake. You retain 50%, the partner exits. You have a new investor and the business continues. New partner also brings additional capital for expansion.', 'A third-party investor can fund a partner buyout without you needing the full cash — the business itself becomes the asset that attracts capital.'],
        ]);

        // 15 — CREDIT CARD DANGER
        $this->scenario($g, 'The Credit Card Balance', 'You have a Ksh 80,000 credit card balance on a card charging 3%/month (36% annually). You also have Ksh 80,000 in a savings account earning 4% annually. Should you use savings to clear the card?', '💳', '#ef4444', false, 80000, [
            ['Use all savings to clear the credit card balance immediately', 'Clear the most expensive debt', 25, -80000, 'You clear the Ksh 80,000 balance. You stop paying Ksh 2,400/month in card interest. That\'s Ksh 28,800/year freed up. Your savings earned Ksh 3,200/year — you\'ve gained a net Ksh 25,600/year improvement.', 'Credit card debt at 36% p.a. is 9× more expensive than savings at 4%. Always use savings to clear credit card debt.'],
            ['Make minimum payments — keep savings intact', 'Minimum payment trap', 0, 0, 'You make minimum payments. After 24 months of minimum payments on Ksh 80,000 at 3%/month, you\'ve paid Ksh 58,000 in interest alone and still owe Ksh 60,000. Your Ksh 80,000 savings earned Ksh 6,500. Net loss: Ksh 51,500.', 'Minimum payments on credit cards are designed to maximise bank profits and your interest costs. Never carry a balance if you have savings to clear it.', 'ending'],
            ['Transfer balance to a 0% introductory card and pay aggressively', 'Balance transfer', 20, 0, 'You find a bank offering a 6-month balance transfer at 0%. You transfer Ksh 80,000 and clear it in 6 months (Ksh 13,333/month). Zero interest paid. Keep savings for emergencies.', 'Balance transfer to a 0% card is the optimal strategy: pay zero interest, retain your emergency savings.'],
        ]);

        // 16 — RENTAL INCOME MANAGEMENT
        $this->scenario($g, 'Managing Rental Income', 'You own a rental property earning Ksh 28,000/month. Maintenance, agency fees and taxes cost Ksh 8,000. Net: Ksh 20,000/month. Should you spend it, reinvest it, or save it separately?', '🏠', '#10b981', false, 28000, [
            ['Reinvest the full Ksh 20,000/month into a property savings account', 'Compound the income', 25, 20000, 'After 3 years you\'ve saved Ksh 720,000. You use it as a deposit on a second rental unit. You now earn Ksh 40,000/month in rental income. Reinvesting passive income compounds your passive income.', 'The fastest way to grow rental income is to reinvest 100% of it into another income-producing asset. Spend your salary, reinvest your passive income.'],
            ['Use rental income as a lifestyle top-up — weekend trips and dining', 'Lifestyle inflation', 3, 0, 'You spend the Ksh 20,000 each month on lifestyle. After 3 years you\'ve enjoyed an extra Ksh 720,000 in spending but have no new assets. The property value grows, but your wealth only grows as slowly as wages.', 'Passive income spent on lifestyle does not compound. You miss the exponential wealth growth of reinvestment.', 'ending'],
            ['Pay off your mortgage faster — direct rental income to mortgage overpayment', 'Debt-free sooner', 20, 0, 'You redirect Ksh 20,000/month to your mortgage overpayment. A 20-year mortgage at Ksh 18,000/month is cleared in 12 years instead. You save hundreds of thousands in interest and own both properties free and clear sooner.', 'Using rental income to accelerate mortgage repayment reduces total interest paid and builds net worth faster.'],
        ]);

        // 17 — HEALTH INSURANCE UPGRADE
        $this->scenario($g, 'The Health Cover Upgrade', 'You\'re 40 with NHIF (Ksh 500/month). A private comprehensive health insurance plan costs Ksh 4,500/month with a Ksh 2M inpatient limit and covers pre-existing conditions after 6 months. NHIF covers basic public hospital care.', '🏥', '#0ea5e9', false, 0, [
            ['Upgrade to private comprehensive cover at Ksh 4,500/month', 'Full health protection', 25, -4500, 'You upgrade. 18 months later you\'re diagnosed with a condition requiring hospitalisation — bill: Ksh 450,000. The private plan covers Ksh 445,000. You pay Ksh 5,000. Without it you would have liquidated investments.', 'Private health insurance is the single highest-leverage financial protection for adults over 35. Medical bills are the leading cause of middle-class financial ruin in Kenya.'],
            ['Stay on NHIF — Ksh 500/month is more affordable', 'NHIF only', 5, -500, 'You stay on NHIF. When you need a private specialist and private hospital (NHIF doesn\'t cover many costs), your out-of-pocket bill is Ksh 180,000. You dip into your retirement savings.', 'NHIF covers basic public hospital services. For private hospitals, specialists, and serious illness, it is insufficient for a household earning over Ksh 50,000.'],
            ['Get NHIF plus a hospital cash benefit plan (Ksh 1,000/month)', 'Layered cover', 18, -1500, 'You add a Ksh 1,000/month hospital cash plan — you receive Ksh 2,000/day in hospital (public or private). Combined with NHIF this gives decent coverage at Ksh 1,500 total. Not comprehensive but meaningfully better than NHIF alone.', 'Layering NHIF with an affordable cash-plan bridge is a cost-effective middle path between full private cover and basic NHIF.'],
        ]);

        // 18 — BUSINESS LOAN DECISION
        $this->scenario($g, 'The Business Expansion Loan', 'Your wholesale business turns over Ksh 400,000/month with Ksh 60,000 net profit. A supplier offers a contract worth Ksh 1.2M if you can supply in bulk — you need Ksh 300,000 to stock inventory. Your SACCO offers Ksh 300,000 at 1%/month.', '📦', '#6366f1', false, 60000, [
            ['Take the SACCO loan and execute the contract', 'Calculated expansion', 22, -300000, 'You take the loan at 1%/month. Total interest over 6 months: Ksh 9,000. The Ksh 1.2M contract net profit: Ksh 180,000. Net after interest: Ksh 171,000 in 6 months. ROI of 57%. The SACCO loan pays for itself 19 times.', 'Low-cost debt used to fund high-return contracts is the engine of business growth. The key is knowing your margin before borrowing.'],
            ['Decline the contract — don\'t take on debt for business', 'Risk-averse stance', 5, 0, 'You decline. The contract goes to a competitor who becomes the supplier\'s preferred partner. Over 3 years they grow their business by Ksh 3M from this single relationship. Fear of calculated debt kept you small.', 'There is good debt and bad debt. Business loans for contracts with known margins and secured buyers are textbook good debt.'],
            ['Take a bank loan at 2.5%/month (more accessible)', 'Expensive capital', 12, -300000, 'You take the bank loan at 2.5%/month. Total interest over 6 months: Ksh 22,500 (vs Ksh 9,000 from SACCO). Still profitable (Ksh 157,500 net), but Ksh 13,500 avoidably spent on higher interest.', 'Always exhaust the cheapest capital source first. The difference between SACCO and bank interest rates on Ksh 300,000 over 6 months is Ksh 13,500.'],
        ]);

        // 19 — JOB OFFER ABROAD
        $this->scenario($g, 'The Job Abroad Decision', 'You earn Ksh 120,000/month as an IT manager in Nairobi. A company in Qatar offers USD 2,800/month (Ksh 362,000). Benefits include free housing and flights. You\'d leave your spouse and 2 young children.', '✈️', '#0ea5e9', false, 0, [
            ['Negotiate a 2-year contract with clear repatriation and family visit terms', 'Structured diaspora move', 20, 0, 'You negotiate a 2-year contract with 3 flight tickets home per year. You remit Ksh 250,000/month while living on Ksh 112,000. In 2 years you save Ksh 6M — enough for a house deposit and business capital.', 'Diaspora income is most powerful when time-bound, remittance-disciplined, and tied to a specific repatriation goal.'],
            ['Accept without negotiation — the offer is already great', 'Under-negotiated', 10, 0, 'You accept without negotiating visits or contract length. Year 2 you miss important family events. The relationship strains. Without a clear exit timeline the 2-year plan becomes indefinite expatriate life.', 'Always negotiate contract length, family benefits, and repatriation terms before accepting any international role.'],
            ['Decline — family and Kenyan career trajectory are priority', 'Local focus', 15, 0, 'You decline the Qatar offer. You invest in Kenyan certifications and apply for senior roles. Within 18 months you secure a Ksh 180,000/month role in Nairobi. Financial growth without family disruption.', 'Not all high-income opportunities require leaving home. Developing skills and negotiating locally can bridge significant income gaps.'],
        ]);

        // 20 — INSURANCE CLAIM PROCESS
        $this->scenario($g, 'The Car Accident Claim', 'Your car is involved in an accident — Ksh 85,000 in repairs needed. Your insurance has a Ksh 20,000 excess clause. The insurer\'s approved garage quotes Ksh 85,000. An independent garage quotes Ksh 65,000.', '🚗', '#dc2626', false, 0, [
            ['Use insurer\'s approved garage — pay Ksh 20,000 excess', 'Standard claim process', 15, -20000, 'You use the approved garage. Total cost to you: Ksh 20,000 (excess). The insurer pays Ksh 65,000. Car is repaired to manufacturer standard with approved parts. Warranty on work is guaranteed.', 'Insurance claims are worth using when the damage exceeds your excess. Approved garages guarantee workmanship and parts quality.'],
            ['Use independent garage and pay the full Ksh 65,000 yourself', 'Skip the claim', 18, -65000, 'You pay Ksh 65,000 yourself to avoid a premium increase. But your comprehensive premium only increases by Ksh 3,000/year after a claim — it would take 15 years for the premium increase to equal Ksh 45,000 you saved the insurer.', 'Avoid insurance claims for minor damages that slightly exceed your excess. The premium increase for major claims usually costs far less than the repair.'],
            ['Negotiate — ask insurer to approve the independent garage\'s lower quote', 'Smart negotiation', 22, -20000, 'You negotiate with the insurer, presenting the independent garage quote. They approve the independent garage at Ksh 65,000. You pay Ksh 20,000 excess. Car repaired for same cost, insurer saves Ksh 20,000 and appreciates you.', 'Insurers will often approve lower-cost alternatives. Always negotiate — the worst they can say is no.'],
        ]);

        // 21 — STOCK MARKET CRASH RESPONSE
        $this->scenario($g, 'The Market Crash Panic', 'You have Ksh 200,000 invested in NSE stocks. The market drops 30% — your portfolio is now worth Ksh 140,000. You\'re worried it will drop further. A friend says to sell before it gets worse.', '📉', '#ef4444', false, 0, [
            ['Hold steady — do not sell during the crash', 'Long-term investor', 25, 0, 'You hold. The market recovers 35% over the next 18 months. Your Ksh 140,000 grows back to Ksh 189,000 — almost fully recovered. Those who sold at Ksh 140,000 locked in their losses permanently.', 'Stock market crashes are temporary. Selling during a crash locks in your loss permanently. Time in market beats timing the market.'],
            ['Sell everything — protect what\'s left', 'Panic sell', 0, 0, 'You sell at Ksh 140,000 — a Ksh 60,000 loss. The market recovers 35% over 18 months. If you\'d held, your Ksh 140,000 would be Ksh 189,000. By selling, you crystallised a Ksh 11,000 permanent loss.', 'Panic selling during market downturns is how most retail investors destroy wealth. The crash is not the loss — selling during it is.', 'ending'],
            ['Buy more while the market is down — double down at lower prices', 'Contrarian investor', 22, 0, 'You invest an additional Ksh 50,000 during the crash. Your average price drops significantly. When the market recovers 35% from the bottom, your total portfolio (Ksh 190,000 invested) is worth Ksh 256,500.', 'Investing more during a market crash (if you have the cash) is one of the highest-return opportunities in investing. Great businesses on sale.'],
        ]);

        // 22 — GOODWILL AND BUSINESS PURCHASE
        $this->scenario($g, 'Buying an Existing Business', 'A pharmacy is for sale at Ksh 900,000 (goodwill + stock). It earns Ksh 80,000/month net profit. Payback period: ~11 months. The seller is moving abroad and needs a quick sale. No audited accounts — only informal records.', '💊', '#10b981', false, 0, [
            ['Request 3 years of audited accounts and do full due diligence before paying', 'Due diligence first', 25, 0, 'You insist on audited accounts. The seller can only provide handwritten books. An accountant reviews them and finds revenue is overstated by 40% — actual net profit is Ksh 48,000/month. Real payback: 18+ months. You renegotiate to Ksh 540,000 or walk.', 'Never buy a business without audited accounts. Informal records can be manipulated. Insist on professional verification before any payment.'],
            ['Pay immediately — the deal looks too good to miss', 'Speed over diligence', 0, -900000, 'You pay Ksh 900,000. After taking over, you find the business\'s regular customers were personal friends of the previous owner — most don\'t return. Real revenue is Ksh 45,000/month. Payback extends to 20 months. You overpaid by Ksh 360,000.', 'Goodwill that depends on the previous owner\'s personal relationships can evaporate at transfer. Verify customer base quality, not just revenue numbers.', 'ending'],
            ['Propose a 6-month earnout — pay Ksh 500,000 now, balance from profits', 'Risk-sharing structure', 22, -500000, 'You propose an earnout: Ksh 500,000 now, Ksh 400,000 paid from profits over 6 months. If revenue is as stated, you pay in 5 months. If revenue is overstated, you never pay the full amount. Seller accepts — a confident seller would.', 'Earnout structures protect buyers from overstated revenue claims. A seller who refuses an earnout signals concern about the business\'s true performance.'],
        ]);

        // 23 — CHAMA GOVERNANCE
        $this->scenario($g, 'Chama Governance Crisis', 'You\'re chairperson of a 12-member Chama investing Ksh 5,000/person/month (Ksh 60,000/month total). A member wants to withdraw their Ksh 120,000 accumulated shares mid-cycle claiming family emergency. The constitution doesn\'t address early withdrawal.', '🤲', '#f59e0b', false, 0, [
            ['Convene an emergency meeting — vote on a formal early-withdrawal policy', 'Governance first', 22, 0, 'You call a meeting. Members vote: early withdrawals allowed up to 50% of personal shares with 60-day notice. The member accesses Ksh 60,000 after 60 days. A governance gap becomes a policy that protects everyone.', 'Chama governance gaps discovered in crisis are opportunities to create rules. Every well-run Chama needs an early-withdrawal policy.'],
            ['Allow the full withdrawal — family emergency is legitimate', 'Ad-hoc exception', 5, 0, 'You allow full withdrawal. This sets a precedent. Three months later two more members cite "emergencies" and withdraw. The Chama\'s investment capital drops by 40% and the property purchase plan collapses.', 'Individual exceptions in group investment vehicles cascade into crises. Governance rules must apply consistently — exceptions unravel cohesion.', 'ending'],
            ['Deny the withdrawal — the constitution doesn\'t allow it', 'Strict rules', 12, 0, 'You cite the constitution. The member leaves the Chama in anger, forfeiting their shares. The remaining 11 members benefit from the unclaimed shares — but lose trust. A better constitution would have prevented the conflict.', 'Zero flexibility also fails. Strong governance anticipates edge cases and codifies fair responses rather than defaulting to either strict refusal or ad-hoc exceptions.'],
        ]);

        // 24 — DIGITAL SAVINGS DISCIPLINE
        $this->scenario($g, 'The Savings Automation Test', 'You earn Ksh 95,000/month and have been "trying to save" manually for 2 years with minimal success. Your bank offers standing orders — automatic transfer on salary day before you see the money. Target: Ksh 15,000/month.', '🏦', '#6366f1', false, 0, [
            ['Set up a standing order for Ksh 15,000 on the same day salary arrives', 'Pay yourself first', 25, 15000, 'The first month feels tight but manageable. By month 3 your lifestyle has adjusted to Ksh 80,000. After 12 months you have Ksh 180,000 saved — more than the previous 2 years of manual saving combined.', 'Automated savings that move before you can spend are 5–10× more effective than manual savings from whatever is left. Pay yourself first.'],
            ['Save manually at end of the month — whatever is left', 'Leftover savings', 3, 0, 'You keep trying to save manually. Life happens — wedding contribution, car repair, holiday — and you save an average of Ksh 2,000/month. After 12 months: Ksh 24,000. The standing order would have saved Ksh 180,000.', 'Manual savings from end-of-month leftovers is the least effective savings strategy. Automation removes the decision (and the temptation) entirely.', 'ending'],
            ['Save Ksh 10,000 via standing order and invest Ksh 5,000 in a unit trust', 'Save + invest', 22, 15000, 'You automate Ksh 10,000 into savings and Ksh 5,000 into a unit trust at 11% p.a. After 12 months: Ksh 120,000 in savings + Ksh 63,000 in unit trust = Ksh 183,000 total. Automation works whether it goes to savings or investment.', 'Automated savings and automated investing together are the most powerful personal finance system. Combine them for maximum wealth building.'],
        ]);

        // 25 — NSE DIVIDEND STRATEGY
        $this->scenario($g, 'The Dividend Income Plan', 'You have Ksh 250,000 to invest. Safaricom pays a dividend yield of ~4.5% annually. KCB Group pays ~6.5%. An MMF is currently at 9% p.a. You want passive income in 2 years.', '💵', '#22c55e', false, 250000, [
            ['Invest in KCB Group for 6.5% dividend yield + capital growth', 'Dividend investor', 20, -250000, 'You buy KCB shares. Annual dividend: Ksh 16,250. Share price also grows 8–12% p.a. After 5 years: dividend income + capital growth = Ksh 137,000 total return on Ksh 250,000. Dividends are paid into your M-Pesa quarterly.', 'Dividend-paying stocks on the NSE provide passive income and capital growth. KCB, Equity, Safaricom, and Co-op Bank have consistent dividend histories.'],
            ['Put all in a Money Market Fund at 9% p.a.', 'Income fund', 18, -250000, 'MMF at 9% p.a. gives Ksh 22,500/year in interest — more than dividends. But zero capital growth. After 5 years: Ksh 136,000 income but your capital is still worth Ksh 250,000 (less inflation). Dividends beat MMF for long-term wealth.', 'MMFs beat dividends on short-term income but dividend stocks + capital growth beats MMFs on total return over 5+ years.'],
            ['Split: Ksh 125,000 in KCB shares, Ksh 125,000 in MMF', 'Balanced income portfolio', 22, -250000, 'KCB gives Ksh 8,125/year in dividends + capital growth. MMF gives Ksh 11,250/year in interest. Combined: Ksh 19,375/year in passive income plus share capital growth. Best of both worlds — steady income now, capital growth over time.', 'Combining dividend stocks and MMFs creates a passive income portfolio with stability (MMF) and growth (equities).'],
        ]);
    }
}
