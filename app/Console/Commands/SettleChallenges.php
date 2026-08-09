<?php

namespace App\Console\Commands;

use App\Services\ChallengeService;
use Illuminate\Console\Command;

class SettleChallenges extends Command
{
    protected $signature   = 'game:settle-challenges';
    protected $description = 'Settle every challenge whose deadline has passed, and cancel duels nobody ever fully accepted';

    public function handle(ChallengeService $service): int
    {
        $result = $service->sweepDeadlines();

        $this->info("Settled {$result['settled']} challenge(s); cancelled {$result['cancelled_unaccepted']} unaccepted duel(s).");

        return self::SUCCESS;
    }
}
