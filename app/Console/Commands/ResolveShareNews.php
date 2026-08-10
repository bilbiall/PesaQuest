<?php

namespace App\Console\Commands;

use App\Services\ShareNewsService;
use Illuminate\Console\Command;

class ResolveShareNews extends Command
{
    protected $signature = 'game:resolve-share-news';

    protected $description = 'Apply the outcome of any Market Watch bulletin whose effect has come due';

    public function handle(ShareNewsService $service): int
    {
        $count = $service->resolveDue();

        $this->info($count > 0 ? "Resolved {$count} bulletin(s)." : 'No bulletins due.');
        return self::SUCCESS;
    }
}
