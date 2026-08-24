<?php

namespace App\Console\Commands;

use App\Services\MarketJitterService;
use Illuminate\Console\Command;

class WarnMarketJitters extends Command
{
    protected $signature = 'game:warn-market-jitters';

    protected $description = 'Post the vague heads-up for every Market Jitter whose warning window has come due';

    public function handle(MarketJitterService $service): int
    {
        $warned = $service->sendDueWarnings();

        $this->info($warned > 0
            ? "Posted {$warned} market jitter warning(s)."
            : 'No market jitter warnings due this run.');

        return self::SUCCESS;
    }
}
