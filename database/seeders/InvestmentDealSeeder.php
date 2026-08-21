<?php

namespace Database\Seeders;

use App\Models\InvestmentDeal;
use Illuminate\Database\Seeder;

/**
 * Equity Square deal progression — the original 4 admin-authored deals
 * (NSE Blue Chip Buy, Crypto Quick Flip, Nairobi Property Flip, Side Hustle
 * Seed) had no level/age gating at all, so a level-1 newcomer and a
 * level-10 veteran saw the identical menu forever. These 14 fill out a
 * real cost/risk/return curve from level 1 through 10, matched by
 * updateOrCreate(title) so re-running never touches the original 4 or
 * duplicates its own rows.
 */
class InvestmentDealSeeder extends Seeder
{
    public function run(): void
    {
        $deals = [
            [
                'title' => 'Piggy Bank Boost Pool', 'min_level' => 1, 'age_group' => '8-12',
                'category' => 'sacco_pool', 'icon' => '🐷', 'cost' => 500,
                'min_return_pct' => 10, 'max_return_pct' => 25, 'loss_pct' => 20, 'success_probability' => 0.75,
                'maturity_ticks' => 5, 'risk_level' => 1,
                'description' => 'Pool your savings with a few classmates into a joint fund the class treasurer manages.',
                'lesson' => 'Pooling small amounts with people you trust is the same idea behind a chama — group discipline beats solo willpower.',
            ],
            [
                'title' => 'Sacco Contribution Boost', 'min_level' => 1, 'age_group' => 'all',
                'category' => 'sacco_pool', 'icon' => '🤝', 'cost' => 1500,
                'min_return_pct' => 15, 'max_return_pct' => 30, 'loss_pct' => 25, 'success_probability' => 0.70,
                'maturity_ticks' => 10, 'risk_level' => 1,
                'description' => 'A one-time top-up contribution to a Sacco lending pool — steady, member-backed returns.',
                'lesson' => 'Sacco pools spread risk across many members\' contributions, which is why they rarely fail as badly as a single stock can.',
            ],
            [
                'title' => 'Duka Restock Fund', 'min_level' => 2, 'age_group' => '13-17',
                'category' => 'side_hustle', 'icon' => '🏪', 'cost' => 3000,
                'min_return_pct' => 20, 'max_return_pct' => 45, 'loss_pct' => 40, 'success_probability' => 0.65,
                'maturity_ticks' => 7, 'risk_level' => 2,
                'description' => 'Fund a neighbourhood duka\'s restock run ahead of the school-opening rush.',
                'lesson' => 'Timing inventory around predictable demand spikes (school opening, festive season) is a real small-business edge.',
            ],
            [
                'title' => 'Agri-Input Financing', 'min_level' => 3, 'age_group' => 'all',
                'category' => 'agri', 'icon' => '🌽', 'cost' => 6000,
                'min_return_pct' => 25, 'max_return_pct' => 55, 'loss_pct' => 45, 'success_probability' => 0.60,
                'maturity_ticks' => 14, 'risk_level' => 3,
                'description' => 'Finance a smallholder farmer\'s seed and fertiliser costs ahead of planting season, repaid at harvest.',
                'lesson' => 'Agricultural returns depend on rainfall and market prices at harvest — real risk that no amount of hard work alone controls.',
            ],
            [
                'title' => 'NSE Growth Stock Pick', 'min_level' => 4, 'age_group' => '18-25',
                'category' => 'stocks', 'icon' => '📈', 'cost' => 10000,
                'min_return_pct' => 18, 'max_return_pct' => 45, 'loss_pct' => 55, 'success_probability' => 0.62,
                'maturity_ticks' => 21, 'risk_level' => 3,
                'description' => 'A smaller, faster-growing NSE-listed company — more upside than a blue chip, more room to fall too.',
                'lesson' => 'Growth stocks trade stability for potential — a young company can double or can also miss earnings badly. Diversify, don\'t bet the whole pot.',
            ],
            [
                'title' => 'Second-Hand Import Flip', 'min_level' => 5, 'age_group' => 'all',
                'category' => 'import_flip', 'icon' => '📦', 'cost' => 18000,
                'min_return_pct' => 25, 'max_return_pct' => 60, 'loss_pct' => 45, 'success_probability' => 0.58,
                'maturity_ticks' => 21, 'risk_level' => 3,
                'description' => 'Fund a container-share of second-hand electronics/clothing (mitumba) landing at Mombasa port.',
                'lesson' => 'Import margins look great on paper until customs delays and exchange-rate swings eat into the timeline and the return.',
            ],
            [
                'title' => 'Startup Equity Stake', 'min_level' => 5, 'age_group' => '18-25',
                'category' => 'startup_equity', 'icon' => '🚀', 'cost' => 20000,
                'min_return_pct' => 30, 'max_return_pct' => 90, 'loss_pct' => 70, 'success_probability' => 0.45,
                'maturity_ticks' => 30, 'risk_level' => 4,
                'description' => 'Take a small equity stake in a Nairobi tech startup\'s seed round.',
                'lesson' => 'Startup equity is a real asset, not a gamble — but most startups fail, which is exactly why the payout has to be huge for the ones that work.',
            ],
            [
                'title' => 'Rental Unit Renovation Flip', 'min_level' => 6, 'age_group' => '26+',
                'category' => 'property_flip', 'icon' => '🏚️', 'cost' => 35000,
                'min_return_pct' => 22, 'max_return_pct' => 50, 'loss_pct' => 40, 'success_probability' => 0.60,
                'maturity_ticks' => 30, 'risk_level' => 3,
                'description' => 'Fund a cosmetic renovation on a run-down rental unit, then re-let it at a higher rent.',
                'lesson' => 'Renovation flips work when the improvement cost is genuinely less than the rent increase it unlocks — measure both before committing.',
            ],
            [
                'title' => 'Matatu Sacco Fleet Share', 'min_level' => 7, 'age_group' => '26+',
                'category' => 'sacco_pool', 'icon' => '🚌', 'cost' => 55000,
                'min_return_pct' => 20, 'max_return_pct' => 40, 'loss_pct' => 30, 'success_probability' => 0.68,
                'maturity_ticks' => 21, 'risk_level' => 2,
                'description' => 'Buy a fractional share in a matatu Sacco\'s fleet earnings on a busy CBD route.',
                'lesson' => 'A fleet share diversifies you across many vehicles\' daily takings at once — one breakdown doesn\'t sink your whole return.',
            ],
            [
                'title' => 'Crypto Altcoin Position', 'min_level' => 7, 'age_group' => '18-25',
                'category' => 'crypto', 'icon' => '🪙', 'cost' => 45000,
                'min_return_pct' => 60, 'max_return_pct' => 180, 'loss_pct' => 100, 'success_probability' => 0.35,
                'maturity_ticks' => 14, 'risk_level' => 5,
                'description' => 'A higher-risk altcoin position beyond the usual blue-chip crypto — bigger swings both ways.',
                'lesson' => 'The higher the promised return, the higher the chance of losing everything — a 100% loss probability line item is not a rounding error, it is the real risk.',
            ],
            [
                'title' => 'Commercial Plot Flip', 'min_level' => 8, 'age_group' => '26+',
                'category' => 'property_flip', 'icon' => '🏗️', 'cost' => 90000,
                'min_return_pct' => 25, 'max_return_pct' => 55, 'loss_pct' => 45, 'success_probability' => 0.55,
                'maturity_ticks' => 45, 'risk_level' => 4,
                'description' => 'Fund the purchase and title transfer of a commercial plot on an up-and-coming bypass road, sold on before development.',
                'lesson' => 'Land value is driven by infrastructure that hasn\'t been built yet — the bet is on a road or a mall arriving on schedule, which is never guaranteed.',
            ],
            [
                'title' => 'Tech Startup Series Seed', 'min_level' => 9, 'age_group' => '18-25',
                'category' => 'startup_equity', 'icon' => '💡', 'cost' => 130000,
                'min_return_pct' => 40, 'max_return_pct' => 120, 'loss_pct' => 90, 'success_probability' => 0.35,
                'maturity_ticks' => 30, 'risk_level' => 5,
                'description' => 'A larger seed-round stake in a startup that already has paying customers, not just an idea.',
                'lesson' => 'Traction (real paying customers) lowers startup risk versus a pure idea — but "lower" is not "low"; most seed-stage companies still don\'t make it to their next round.',
            ],
            [
                'title' => 'Private Equity Buy-In', 'min_level' => 9, 'age_group' => '26+',
                'category' => 'private_equity', 'icon' => '💼', 'cost' => 150000,
                'min_return_pct' => 35, 'max_return_pct' => 100, 'loss_pct' => 80, 'success_probability' => 0.40,
                'maturity_ticks' => 45, 'risk_level' => 5,
                'description' => 'Buy into a private equity fund\'s stake in an established mid-sized Kenyan company.',
                'lesson' => 'Private equity locks your money away far longer than a listed stock — you cannot sell out early if you change your mind.',
            ],
            [
                'title' => 'Warehouse Development Syndicate', 'min_level' => 10, 'age_group' => '26+',
                'category' => 'property_flip', 'icon' => '🏭', 'cost' => 300000,
                'min_return_pct' => 30, 'max_return_pct' => 70, 'loss_pct' => 50, 'success_probability' => 0.50,
                'maturity_ticks' => 60, 'risk_level' => 4,
                'description' => 'Join a syndicate of investors funding a logistics warehouse development near the Nairobi-Mombasa highway.',
                'lesson' => 'Syndicated deals let ordinary investors reach institutional-scale projects — but you have no control once your money is in, only a contract.',
            ],
        ];

        foreach ($deals as $i => $d) {
            InvestmentDeal::updateOrCreate(
                ['title' => $d['title']],
                $d + [
                    'success_probability' => $d['success_probability'],
                    'is_active'  => true,
                    'sort_order' => 10 + $i,
                ]
            );
        }
    }
}
