<?php

namespace App\Console\Commands;

use App\Services\ShareNewsService;
use Illuminate\Console\Command;

class ResolveShareNews extends Command
{
    protected $signature = 'game:resolve-share-news';

    protected $description = 'Apply the outcome of any Market Watch bulletin whose effect has come due, and post the outcome reply for any whose announcement delay has since elapsed';

    public function handle(ShareNewsService $service): int
    {
        $resolved  = $service->resolveDue();
        $announced = $service->announceDue();

        $this->info($resolved > 0 ? "Resolved {$resolved} bulletin(s)." : 'No bulletins due to resolve.');
        $this->info($announced > 0 ? "Announced {$announced} bulletin(s)." : 'No bulletins due to announce.');
        return self::SUCCESS;
    }
}
