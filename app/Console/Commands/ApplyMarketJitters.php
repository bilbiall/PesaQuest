<?php

namespace App\Console\Commands;

use App\Services\MarketJitterService;
use Illuminate\Console\Command;

class ApplyMarketJitters extends Command
{
    protected $signature = 'game:apply-market-jitters';

    protected $description = 'Apply every preset Market Jitter whose game-time schedule has come due';

    public function handle(MarketJitterService $service): int
    {
        $applied = $service->applyDue();

        $this->info($applied > 0
            ? "Applied {$applied} market jitter(s)."
            : 'No market jitters due this run.');

        return self::SUCCESS;
    }
}
