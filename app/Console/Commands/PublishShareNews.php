<?php

namespace App\Console\Commands;

use App\Services\ShareNewsService;
use Illuminate\Console\Command;

class PublishShareNews extends Command
{
    protected $signature = 'game:publish-share-news';

    protected $description = 'Roll the daily chance of a new Market Watch bulletin and post it to the Forums if it hits';

    public function handle(ShareNewsService $service): int
    {
        $item = $service->maybePublish();

        if (!$item) {
            $this->info('No bulletin published today.');
            return self::SUCCESS;
        }

        $this->info("Published: \"{$item->headline}\" (is_true={$item->is_true}, effect_at={$item->effect_at})");
        return self::SUCCESS;
    }
}
