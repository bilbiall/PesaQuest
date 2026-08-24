<?php

namespace Database\Seeders;

use App\Models\MarketJitter;
use App\Services\GameClock;
use Illuminate\Database\Seeder;

/**
 * Bulk-presets the whole "Market Jitters" roster a continuous game will ever
 * need — 30 sudden, broad share-price shocks spread across game years 1-15,
 * so no admin ever has to trigger one by hand. Each entry's `game_day_offset`
 * (365 game days = 1 game year, matching GameCalendarService) is resolved
 * ONCE at seed time into a real `scheduled_at` via the CURRENT game-clock
 * speed (GameClock::realSecondsForTicks) — i.e. relative to game time, not
 * real-world dates. Re-running this seeder never pushes an already-scheduled
 * jitter's date around (firstOrCreate, keyed by name).
 */
class MarketJitterSeeder extends Seeder
{
    // Generic lesson banks, reused across entries by scope+direction so every
    // jitter doesn't need a hand-written takeaway — same reuse pattern as
    // ShareMarketService::EVENT_REASONS.
    private const LESSONS = [
        'all_down'    => "Broad market drops feel scary, but they hit every share at once — a diversified holder rides it out better than someone all-in on one company.",
        'all_up'      => "Broad rallies lift almost everything — resist the urge to chase it all in at the top; the same market that rallies also corrects.",
        'sector_down' => "A sector-wide shock hits every company in that line of business, even the well-run ones — spreading your shares across sectors softens any single sector's bad day.",
        'sector_up'   => "A sector rally can look like easy money, but it rarely lasts forever — enjoy the gain without betting your whole portfolio on it repeating.",
    ];

    /** Game days before scheduled_at that the vague heads-up posts — same
     *  lead used to backfill jitters seeded before this column existed, see
     *  the 2026_08_24_150000_add_warning_to_market_jitters migration. */
    private const WARNING_LEAD_TICKS = 3;

    public function run(GameClock $clock): void
    {
        $added = 0;
        foreach ($this->jitters() as $j) {
            $lessonKey = ($j['scope'] === 'sector' ? 'sector' : 'all') . '_' . $j['direction'];

            $existing = MarketJitter::where('name', $j['name'])->first();
            if ($existing) continue;

            MarketJitter::create([
                'name'            => $j['name'],
                'description'     => $j['description'],
                'lesson'          => self::LESSONS[$lessonKey],
                'scope'           => $j['scope'],
                'sector'          => $j['sector'] ?? null,
                'direction'       => $j['direction'],
                'magnitude_pct'   => $j['magnitude_pct'],
                'window_steps'    => $j['window_steps'] ?? 8,
                'game_day_offset' => $j['game_day_offset'],
                'scheduled_at'    => now()->addSeconds($clock->realSecondsForTicks($j['game_day_offset'])),
                'warn_at'         => now()->addSeconds($clock->realSecondsForTicks(max(0, $j['game_day_offset'] - self::WARNING_LEAD_TICKS))),
                'status'          => 'scheduled',
            ]);
            $added++;
        }

        $this->command?->info("Market Jitters: added {$added} new preset jitter(s), " . MarketJitter::count() . ' total.');
    }

    private function jitters(): array
    {
        return [
            ['name' => 'First Market Wobble',              'description' => "New investors get spooked by a perfectly ordinary bout of volatility — prices dip across the board.", 'scope' => 'all',    'direction' => 'down', 'magnitude_pct' => 6,  'game_day_offset' => 60],
            ['name' => 'Telecom Tariff Shake-Up',          'description' => "A regulator-mandated tariff review squeezes telecom margins overnight.", 'scope' => 'sector', 'sector' => 'Telecoms',      'direction' => 'down', 'magnitude_pct' => 8,  'game_day_offset' => 240],
            ['name' => 'Bumper Harvest Boom',              'description' => "Unusually good rains deliver a bumper harvest — agriculture shares rally on strong export volumes.", 'scope' => 'sector', 'sector' => 'Agriculture',   'direction' => 'up',   'magnitude_pct' => 10, 'game_day_offset' => 455],
            ['name' => 'Interbank Rate Hike',              'description' => "The central bank hikes the base rate — banks brace for a wave of loan defaults.", 'scope' => 'sector', 'sector' => 'Banking',       'direction' => 'down', 'magnitude_pct' => 9,  'game_day_offset' => 635],
            ['name' => 'Global Oil Price Spike',           'description' => "A spike in global oil prices hits every energy producer's cost base at once.", 'scope' => 'sector', 'sector' => 'Energy',        'direction' => 'down', 'magnitude_pct' => 12, 'game_day_offset' => 775],
            ['name' => 'Manufacturing Export Surge',       'description' => "A new regional trade deal opens fresh export markets for local manufacturers.", 'scope' => 'sector', 'sector' => 'Manufacturing', 'direction' => 'up',   'magnitude_pct' => 11, 'game_day_offset' => 930],
            ['name' => 'Currency Devaluation Scare',       'description' => "The shilling slides sharply against major currencies, rattling the whole market at once.", 'scope' => 'all', 'direction' => 'down', 'magnitude_pct' => 10, 'game_day_offset' => 1095],
            ['name' => 'Aviation Fuel Relief',              'description' => "Jet fuel prices ease for the first time in months — airline margins breathe again.", 'scope' => 'sector', 'sector' => 'Aviation',      'direction' => 'up',   'magnitude_pct' => 9,  'game_day_offset' => 1345],
            ['name' => 'Insurance Sector Consolidation Rally', 'description' => "Two mid-size insurers merge, and the whole sector re-rates on hopes of more consolidation.", 'scope' => 'sector', 'sector' => 'Insurance', 'direction' => 'up', 'magnitude_pct' => 8, 'game_day_offset' => 1460],
            ['name' => 'Regional Drought Warning',          'description' => "Forecasters warn of a severe regional drought — farm output projections get slashed.", 'scope' => 'sector', 'sector' => 'Agriculture', 'direction' => 'down', 'magnitude_pct' => 14, 'game_day_offset' => 1660],
            ['name' => 'Tech-Led Market Rally',             'description' => "A wave of digital-payments optimism lifts sentiment across the entire market.", 'scope' => 'all', 'direction' => 'up', 'magnitude_pct' => 12, 'game_day_offset' => 1825],
            ['name' => 'Banking Sector Bad Debt Scare',     'description' => "A leaked audit shows rising bad debt provisions across the banking sector.", 'scope' => 'sector', 'sector' => 'Banking', 'direction' => 'down', 'magnitude_pct' => 10, 'game_day_offset' => 1985],
            ['name' => 'Nationwide Blackout Fallout',       'description' => "A multi-day nationwide blackout puts the spotlight on grid reliability.", 'scope' => 'sector', 'sector' => 'Energy', 'direction' => 'down', 'magnitude_pct' => 11, 'game_day_offset' => 2140],
            ['name' => 'Construction Boom Lifts Manufacturing', 'description' => "A government infrastructure push sends orders flooding into cement and steel producers.", 'scope' => 'sector', 'sector' => 'Manufacturing', 'direction' => 'up', 'magnitude_pct' => 9, 'game_day_offset' => 2360],
            ['name' => 'Global Recession Fears',            'description' => "Weak data from major trading partners stokes fears of a global slowdown — everything sells off.", 'scope' => 'all', 'direction' => 'down', 'magnitude_pct' => 15, 'game_day_offset' => 2555],
            ['name' => '5G Rollout Buzz',                   'description' => "A nationwide 5G rollout announcement sends telecom shares soaring on future-data-revenue hopes.", 'scope' => 'sector', 'sector' => 'Telecoms', 'direction' => 'up', 'magnitude_pct' => 13, 'game_day_offset' => 2745],
            ['name' => 'Aviation Strike Disruption',        'description' => "A ground-staff strike grounds flights for days, denting airline revenue.", 'scope' => 'sector', 'sector' => 'Aviation', 'direction' => 'down', 'magnitude_pct' => 12, 'game_day_offset' => 2915],
            ['name' => 'Insurance Claims Spike After Floods', 'description' => "Severe flooding triggers a wave of property claims across the insurance sector.", 'scope' => 'sector', 'sector' => 'Insurance', 'direction' => 'down', 'magnitude_pct' => 9, 'game_day_offset' => 3110],
            ['name' => 'Decade Bull Run',                   'description' => "A decade of steady growth culminates in a market-wide celebration rally.", 'scope' => 'all', 'direction' => 'up', 'magnitude_pct' => 16, 'game_day_offset' => 3285],
            ['name' => 'Agri-Tech Investment Wave',         'description' => "A wave of foreign investment into agri-tech startups lifts the whole farming sector's outlook.", 'scope' => 'sector', 'sector' => 'Agriculture', 'direction' => 'up', 'magnitude_pct' => 10, 'game_day_offset' => 3605],
            ['name' => 'Banking Digital Transformation Rally', 'description' => "Banks report record mobile-banking adoption, and the sector re-rates on lower cost-to-serve.", 'scope' => 'sector', 'sector' => 'Banking', 'direction' => 'up', 'magnitude_pct' => 9, 'game_day_offset' => 3800],
            ['name' => 'Energy Tariff Review Shock',        'description' => "An unfavorable tariff review caps utility pricing power overnight.", 'scope' => 'sector', 'sector' => 'Energy', 'direction' => 'down', 'magnitude_pct' => 8, 'game_day_offset' => 3990],
            ['name' => 'Global Supply Chain Crunch',        'description' => "A global shipping crunch delays imports and exports alike, unsettling the whole market.", 'scope' => 'all', 'direction' => 'down', 'magnitude_pct' => 13, 'game_day_offset' => 4015],
            ['name' => 'Manufacturing Input Cost Surge',    'description' => "The cost of imported raw materials jumps sharply, squeezing manufacturer margins.", 'scope' => 'sector', 'sector' => 'Manufacturing', 'direction' => 'down', 'magnitude_pct' => 9, 'game_day_offset' => 4380],
            ['name' => 'Telecom Price War',                 'description' => "Two rival telecoms slash data prices in a bruising fight for market share.", 'scope' => 'sector', 'sector' => 'Telecoms', 'direction' => 'down', 'magnitude_pct' => 10, 'game_day_offset' => 4525],
            ['name' => 'Insurance Sector Profit Boom',      'description' => "A rare claims-free quarter across the board sends insurance profits — and shares — up sharply.", 'scope' => 'sector', 'sector' => 'Insurance', 'direction' => 'up', 'magnitude_pct' => 9, 'game_day_offset' => 4745],
            ['name' => 'Market Correction',                 'description' => "After a long run-up, the whole market pulls back in a routine, healthy correction.", 'scope' => 'all', 'direction' => 'down', 'magnitude_pct' => 11, 'game_day_offset' => 4930],
            ['name' => 'Aviation Route Expansion Rally',    'description' => "A new wave of international routes gets approved, lifting airline growth prospects.", 'scope' => 'sector', 'sector' => 'Aviation', 'direction' => 'up', 'magnitude_pct' => 10, 'game_day_offset' => 5115],
            ['name' => 'Fifteen-Year Market Milestone Rally', 'description' => "The market marks fifteen years of history with its biggest single rally yet.", 'scope' => 'all', 'direction' => 'up', 'magnitude_pct' => 18, 'game_day_offset' => 5290],
            ['name' => 'Agriculture Export Ban Shock',      'description' => "A sudden export ban on a key crop leaves agriculture shares reeling.", 'scope' => 'sector', 'sector' => 'Agriculture', 'direction' => 'down', 'magnitude_pct' => 12, 'game_day_offset' => 5490],
        ];
    }
}
