<?php

namespace App\Console\Commands;

use App\Services\CrisisService;
use Illuminate\Console\Command;

class ProcessFinancialCrises extends Command
{
    protected $signature = 'game:process-crises';

    protected $description = 'Send crisis warnings and apply active crisis effects';

    public function handle(CrisisService $crises): int
    {
        $warned  = $crises->sendWarnings();
        $applied = $crises->applyEffects();

        $this->info("Crisis warnings sent: {$warned} · crisis effects applied: {$applied}.");

        return self::SUCCESS;
    }
}
