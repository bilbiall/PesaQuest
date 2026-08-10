<?php

namespace Database\Seeders;

use App\Models\ShareNewsTemplate;
use Illuminate\Database\Seeder;

class ShareNewsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $t) {
            ShareNewsTemplate::updateOrCreate(
                ['headline' => $t['headline']],
                $t
            );
        }
    }

    private function templates(): array
    {
        return [
            // ── Company-scope: something happens to ONE business ───────────
            [
                'headline'  => "{name}'s longtime CEO just announced they're stepping down, effective immediately.",
                'flavor'    => "No successor has been named yet. Boardrooms don't love surprises like this.",
                'lesson'    => "Sudden leadership exits often unsettle a share for a while — but a strong company usually survives a change at the top.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'down',
            ],
            [
                'headline'  => "Analysts have noticed unusually heavy insider buying at {name} this week.",
                'flavor'    => "When the people who know a company best start buying its own shares, the market pays attention.",
                'lesson'    => "Insider buying is a real signal of confidence — but insiders can be wrong too, and buybacks don't always move prices.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'up',
            ],
            [
                'headline'  => "A logistics dispute is quietly disrupting {name}'s supply chain, sources say.",
                'flavor'    => "Nothing official yet, but deliveries are said to be running behind for a second week.",
                'lesson'    => "Supply chain trouble is one of the quietest ways a healthy business can miss its numbers.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'down',
            ],
            [
                'headline'  => "{name} has signed a multi-year supply deal with a major distributor.",
                'flavor'    => "Terms weren't disclosed, but it's described internally as the biggest contract in years.",
                'lesson'    => "A big new contract can lift a company's future earnings — but the real payoff often takes quarters to show up.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'up',
            ],
            [
                'headline'  => "There's takeover chatter swirling around {name} again.",
                'flavor'    => "Nobody's named a buyer. This kind of talk has come and gone before without anything happening.",
                'lesson'    => "Takeover rumours move prices fast because a buyout usually pays a premium — but most rumours never become a real deal.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'up',
            ],
            [
                'headline'  => "A product recall notice has reportedly been filed against {name}.",
                'flavor'    => "Details are thin so far on how many units are affected.",
                'lesson'    => "Recalls hurt short-term sentiment, but the real cost depends on scale — a small one can blow over fast.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'down',
            ],
            [
                'headline'  => "{name}'s biggest rival just slashed prices across its whole product line.",
                'flavor'    => "Industry watchers expect a response within days.",
                'lesson'    => "A price war can squeeze everyone's margins at once — it's rarely good news for anyone in the fight.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'down',
            ],
            [
                'headline'  => "Word around town is {name} is about to unveil something big at next week's industry expo.",
                'flavor'    => "The company isn't commenting, which is somehow making people more curious, not less.",
                'lesson'    => "Anticipation alone can move a price before anything is even announced — and just as easily deflate if the reveal disappoints.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'up',
            ],
            [
                'headline'  => "Auditors reportedly flagged some unusual numbers in {name}'s latest filing.",
                'flavor'    => "The company says it's routine. Not everyone is convinced.",
                'lesson'    => "Accounting red flags don't always mean fraud, but they're exactly the kind of thing that makes cautious investors step back.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'down',
            ],
            [
                'headline'  => "{name} has quietly rehired one of its most respected former executives.",
                'flavor'    => "They left three years ago under good terms. Staff are reportedly thrilled to see them back.",
                'lesson'    => "Bringing back proven leadership can restore market confidence — reputation counts for a lot in how a company is valued.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'up',
            ],
            [
                'headline'  => "A workers' strike looks increasingly likely at {name}'s main site.",
                'flavor'    => "Talks between staff and management reportedly broke down again this week.",
                'lesson'    => "A strike doesn't just cost wages — it can stall production for weeks, and markets price that risk in early.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'down',
            ],
            [
                'headline'  => "{name} just posted its strongest customer growth numbers in years.",
                'flavor'    => "The company hasn't given full-year guidance yet, but the early numbers are turning heads.",
                'lesson'    => "One good quarter is encouraging, but it's the trend over several quarters that really tells the story.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'up',
            ],
            [
                'headline'  => "A former {name} employee has gone public with claims about the company's internal culture.",
                'flavor'    => "The company has issued a short statement denying the claims.",
                'lesson'    => "Reputation stories can spook a market fast — even when nothing about the actual business has changed yet.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'down',
            ],
            [
                'headline'  => "{name} has been named a finalist for a major industry award.",
                'flavor'    => "Winners are announced next month. Being shortlisted alone is already good PR.",
                'lesson'    => "Awards and recognition are mostly noise for a share price — nice for morale, rarely a real driver of value.",
                'scope'     => 'company', 'sector' => null, 'sentiment' => 'up',
            ],

            // ── Sector-scope: something happens to a WHOLE industry ────────
            [
                'headline'  => "A rival network is rolling out an aggressive price war across {name}.",
                'flavor'    => "Smaller players are already matching the discounts. Bigger ones are staying quiet — for now.",
                'lesson'    => "Price wars can pull down an entire sector at once, even the companies that didn't start the fight.",
                'scope'     => 'sector', 'sector' => 'Telecoms', 'sentiment' => 'down',
            ],
            [
                'headline'  => "Newly opened spectrum licenses have cracked open fresh growth room across {name}.",
                'flavor'    => "Regulators approved the release faster than most expected.",
                'lesson'    => "Regulatory changes can quietly reshape a whole sector's growth prospects overnight.",
                'scope'     => 'sector', 'sector' => 'Telecoms', 'sentiment' => 'up',
            ],
            [
                'headline'  => "Rumors of tighter lending rules are swirling around {name}.",
                'flavor'    => "Nothing's official from the regulator yet, but bank executives are said to be nervous.",
                'lesson'    => "Tighter lending rules can shrink how much profit banks make on every loan — that hits the whole sector, not just one bank.",
                'scope'     => 'sector', 'sector' => 'Banking', 'sentiment' => 'down',
            ],
            [
                'headline'  => "Strong loan demand this quarter has analysts turning bullish on {name}.",
                'flavor'    => "More borrowing generally means more interest income for lenders.",
                'lesson'    => "Loan growth is good for banks in good times — but it's also exactly what turns risky in a downturn.",
                'scope'     => 'sector', 'sector' => 'Banking', 'sentiment' => 'up',
            ],
            [
                'headline'  => "Shipping delays at the port are piling up extra costs across {name}.",
                'flavor'    => "Container backlogs are reportedly worse than the same period last year.",
                'lesson'    => "Anything that raises input costs squeezes margins for everyone downstream — the effect often shows up sector-wide.",
                'scope'     => 'sector', 'sector' => 'Manufacturing', 'sentiment' => 'down',
            ],
            [
                'headline'  => "A surge in export orders is lifting sentiment across {name}.",
                'flavor'    => "Buyers overseas are reportedly locking in orders earlier than usual this year.",
                'lesson'    => "Export demand can lift an entire manufacturing sector at once — it isn't tied to any single firm's own effort.",
                'scope'     => 'sector', 'sector' => 'Manufacturing', 'sentiment' => 'up',
            ],
            [
                'headline'  => "Fuel prices spiked again this week, and it's squeezing {name} hard.",
                'flavor'    => "Fuel is one of the biggest single costs in this business — every spike hurts.",
                'lesson'    => "For fuel-heavy sectors, an oil price move somewhere else in the world can hit local shares within days.",
                'scope'     => 'sector', 'sector' => 'Aviation', 'sentiment' => 'down',
            ],
            [
                'headline'  => "New route approvals just opened fresh revenue lanes across {name}.",
                'flavor'    => "Regulators cleared a backlog of pending applications all at once.",
                'lesson'    => "New routes mean new revenue, but they also mean new costs — the payoff isn't always immediate.",
                'scope'     => 'sector', 'sector' => 'Aviation', 'sentiment' => 'up',
            ],
            [
                'headline'  => "Planned maintenance outages are drawing sharp public criticism across {name}.",
                'flavor'    => "Households in several areas have gone without power for hours at a stretch.",
                'lesson'    => "Public anger over service failures can pressure regulators into rulings that hit a sector's profits later.",
                'scope'     => 'sector', 'sector' => 'Energy', 'sentiment' => 'down',
            ],
            [
                'headline'  => "A new tariff review looks set to favor firms across {name}.",
                'flavor'    => "The regulator's draft proposal leaked earlier than expected.",
                'lesson'    => "Tariff decisions directly set how much a utility is allowed to earn — one ruling can move a whole sector.",
                'scope'     => 'sector', 'sector' => 'Energy', 'sentiment' => 'up',
            ],
            [
                'headline'  => "An unusually heavy claims season is hitting reserves across {name}.",
                'flavor'    => "It's been a rough few months for claims volume industry-wide.",
                'lesson'    => "Insurers set aside money for claims they expect — a season worse than expected eats straight into profit.",
                'scope'     => 'sector', 'sector' => 'Insurance', 'sentiment' => 'down',
            ],
            [
                'headline'  => "New policy sales are running hot across {name} this quarter.",
                'flavor'    => "Several firms have reported their best sign-up numbers in years.",
                'lesson'    => "More policies sold now means more premium income — but also more future claims to eventually pay out.",
                'scope'     => 'sector', 'sector' => 'Insurance', 'sentiment' => 'up',
            ],
            [
                'headline'  => "Dry weather forecasts are worrying growers across {name}.",
                'flavor'    => "Meteorologists are pointing to a below-average rainy season ahead.",
                'lesson'    => "Weather is the one input no company controls — a bad season can hit an entire agricultural sector at once.",
                'scope'     => 'sector', 'sector' => 'Agriculture', 'sentiment' => 'down',
            ],
            [
                'headline'  => "A bumper harvest is lifting export hopes across {name}.",
                'flavor'    => "Early yield estimates are coming in well above the five-year average.",
                'lesson'    => "A good harvest doesn't just help one farm — it lifts volumes and revenue across the whole sector.",
                'scope'     => 'sector', 'sector' => 'Agriculture', 'sentiment' => 'up',
            ],
            [
                'headline'  => "A credit ratings agency is reportedly reviewing several firms across {name}.",
                'flavor'    => "A ratings review can go either way once it's actually published.",
                'lesson'    => "A credit rating affects how cheaply a company can borrow — a downgrade review alone can spook a whole sector.",
                'scope'     => 'sector', 'sector' => 'Banking', 'sentiment' => 'down',
            ],
            [
                'headline'  => "International investors are said to be quietly increasing exposure to {name}.",
                'flavor'    => "Foreign inflows into local markets have picked up over the past month.",
                'lesson'    => "Foreign investor interest can lift an entire sector's valuations, regardless of any one company's own results.",
                'scope'     => 'sector', 'sector' => 'Telecoms', 'sentiment' => 'up',
            ],
        ];
    }
}
