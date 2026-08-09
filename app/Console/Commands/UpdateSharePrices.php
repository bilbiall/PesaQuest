<?php

namespace App\Console\Commands;

use App\Services\ShareMarketService;
use Illuminate\Console\Command;

class UpdateSharePrices extends Command
{
    protected $signature = 'game:update-share-prices';

    protected $description = 'Step every active share\'s price by one random-walk tick (Equity Square trading)';

    public function handle(ShareMarketService $market)
    {
        $market->stepAll();
        $this->info('Share prices updated.');
    }
}
