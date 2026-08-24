<?php

namespace App\Console\Commands;

use App\Services\ShareNewsService;
use Illuminate\Console\Command;

class PublishShareNews extends Command
{
    protected $signature = 'game:publish-share-news';

    protected $description = 'Catch up on every game day of Market Watch bulletin rolls missed since the last check';

    public function handle(ShareNewsService $service): int
    {
        $published = $service->rollDueBulletins();

        $this->info($published > 0
            ? "Published {$published} bulletin(s)."
            : 'No bulletin published this run.');

        return self::SUCCESS;
    }
}
