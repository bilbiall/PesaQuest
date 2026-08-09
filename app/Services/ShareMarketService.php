<?php

namespace App\Services;

use App\Models\Share;

class ShareMarketService
{
    /** Minimum real seconds between price steps — keeps the market lively
     *  without rewriting every row on every page view. */
    private const STEP_INTERVAL_SECONDS = 180;

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
        $pctChange = $share->drift + (lcg_value() * 2 - 1) * $share->volatility;

        // ~6% chance of a bigger real-world-style shock (earnings surprise,
        // scandal, market-wide swing) — 2-4x the normal daily swing.
        if (lcg_value() < 0.06) {
            $pctChange += (lcg_value() * 2 - 1) * $share->volatility * (2 + lcg_value() * 2);
        }

        $newPrice = $share->current_price * (1 + $pctChange);
        $newPrice = max($share->min_price, min($share->max_price, $newPrice));

        $share->previous_price = $share->current_price;
        $share->current_price  = round($newPrice, 2);
        $share->save();
    }
}
