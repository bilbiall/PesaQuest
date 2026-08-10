<?php

namespace App\Services;

use App\Models\Share;

class ShareMarketService
{
    /** Minimum real seconds between price steps — keeps the market lively
     *  without rewriting every row on every page view. */
    private const STEP_INTERVAL_SECONDS = 180;

    /** How many recent prices to keep for the sparkline. */
    public const HISTORY_LENGTH = 15;

    /** Sector-flavored reasons for a big "news jolt" move — generic fallback
     *  covers any sector not listed. Purely cosmetic context, doesn't affect
     *  the actual math; just turns "a random number changed" into something
     *  that reads like real market cause-and-effect. */
    private const EVENT_REASONS = [
        'Telecoms' => [
            'up'   => ['📱 New data bundle drives subscriber growth', '📶 Network expansion news lifts investor confidence'],
            'down' => ['📉 Regulatory fine dents earnings outlook', '⚡ Price war with a rival spooks investors'],
        ],
        'Banking' => [
            'up'   => ['🏦 Strong quarterly profit beats expectations', '💳 Loan book growth impresses analysts'],
            'down' => ['📉 Bad debt provisions spook the market', '🏦 Interest rate hike worries investors'],
        ],
        'Manufacturing' => [
            'up'   => ['🏭 Export orders surge', '📈 Cost-cutting drive lifts margins'],
            'down' => ['⚡ Rising input costs squeeze margins', '🏭 Factory downtime hits output'],
        ],
        'Aviation' => [
            'up'   => ['✈️ New route announcement excites investors', '⛽ Easing fuel prices boost outlook'],
            'down' => ['⛽ Fuel price spike hits margins hard', '✈️ Flight disruptions dent confidence'],
        ],
        'Energy' => [
            'up'   => ['⚡ Tariff review favors the utility', '🔌 Grid expansion deal signed'],
            'down' => ['⚡ Outages spark public criticism', '🔧 Maintenance costs balloon'],
        ],
        'Insurance' => [
            'up'   => ['🛡️ Lower claims ratio boosts profit', '📈 New policy sales grow fast'],
            'down' => ['🌧️ Heavy claims season hits reserves', '🛡️ Capital review worries investors'],
        ],
        'Agriculture' => [
            'up'   => ['🌱 Bumper harvest lifts export volumes', '☀️ Favorable weather boosts yield outlook'],
            'down' => ['🌧️ Drought fears hit crop forecasts', '🐛 Pest outbreak threatens yields'],
        ],
    ];

    private const GENERIC_REASONS = [
        'up'   => ['📈 Broad market rally lifts sentiment', '💹 Heavy buying pushes the price up'],
        'down' => ['📉 Broad sell-off drags the price down', '💹 Profit-taking triggers a dip'],
    ];

    /** Called opportunistically whenever a player views the market — makes
     *  the price feed self-healing if the scheduled command's cron entry
     *  ever goes missing on production (a recurring failure mode in this
     *  app's shared-hosting history), instead of silently going stale. */
    public function refreshIfStale(): void
    {
        $lastUpdate = Share::max('updated_at');
        if ($lastUpdate && now()->diffInSeconds($lastUpdate) < self::STEP_INTERVAL_SECONDS) {
            return;
        }
        $this->stepAll();
    }

    /** One random-walk step for every active share — drift + a volatility-scaled
     *  swing, with an occasional bigger "news" jolt, clamped to each share's
     *  band. Same multiplicative appreciation+volatility shape LifeSimulator
     *  already uses for PlayerAsset value swings, just on a shared/global price
     *  instead of a per-holder one. */
    public function stepAll(): void
    {
        Share::active()->get()->each(fn (Share $share) => $this->step($share));
    }

    public function step(Share $share): void
    {
        // A Market Watch drift boost has run its course — clear it before
        // this step, so it doesn't linger past its intended window.
        if ($share->temp_drift_expires_at && $share->temp_drift_expires_at->isPast()) {
            $share->temp_drift            = 0;
            $share->temp_drift_expires_at = null;
        }

        $effectiveDrift = $share->drift + ($share->temp_drift ?? 0);
        $pctChange      = $effectiveDrift + (lcg_value() * 2 - 1) * $share->volatility;

        // ~6% chance of a bigger real-world-style shock (earnings surprise,
        // scandal, market-wide swing) — 2-4x the normal daily swing.
        $isJolt = lcg_value() < 0.06;
        if ($isJolt) {
            $pctChange += (lcg_value() * 2 - 1) * $share->volatility * (2 + lcg_value() * 2);
        }

        $newPrice = $share->current_price * (1 + $pctChange);
        $newPrice = max($share->min_price, min($share->max_price, $newPrice));
        $newPrice = round($newPrice, 2);

        $history   = $share->price_history ?? [];
        $history[] = $newPrice;
        if (count($history) > self::HISTORY_LENGTH) {
            $history = array_slice($history, -self::HISTORY_LENGTH);
        }

        $share->previous_price     = $share->current_price;
        $share->current_price      = $newPrice;
        $share->price_history      = $history;
        $share->last_event_reason  = $isJolt ? $this->pickReason($share->sector, $newPrice >= $share->previous_price) : null;
        $share->save();
    }

    /** Sets up the resolved outcome of a Market Watch news item as a
     *  temporary drift boost rather than an instant jump — the regular
     *  step() cron then trends the price toward it over several real-time
     *  steps, each with its own ordinary randomness. That's deliberate: the
     *  move should look like an organic few-day trend, not a scripted jump,
     *  and the total realized move staying uncertain (some steps go the
     *  "wrong" way, same as any real trend) is what keeps a correct read
     *  from being a guaranteed payout. */
    public function applyNewsDrift(Share $share, string $direction, float $magnitudePct, int $windowSteps = 8): void
    {
        $sign          = $direction === 'up' ? 1 : -1;
        $totalFraction = $magnitudePct / 100;

        $share->temp_drift            = $sign * ($totalFraction / max(1, $windowSteps));
        $share->temp_drift_expires_at = now()->addSeconds(self::STEP_INTERVAL_SECONDS * $windowSteps);
        $share->save();
    }

    private function pickReason(?string $sector, bool $up): string
    {
        $key    = $up ? 'up' : 'down';
        $bucket = self::EVENT_REASONS[$sector][$key] ?? self::GENERIC_REASONS[$key];
        return $bucket[array_rand($bucket)];
    }
}
