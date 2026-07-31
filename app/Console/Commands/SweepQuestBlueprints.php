<?php

namespace App\Console\Commands;

use App\Services\QuestFactory;
use Illuminate\Console\Command;

class SweepQuestBlueprints extends Command
{
    protected $signature   = 'game:sweep-quests';
    protected $description = 'Print every missing quest from the active quest blueprints (the level-ladder sweep)';

    public function handle(QuestFactory $factory): int
    {
        $s = $factory->sweep();

        $this->info("Sweep done: {$s['created']} quest(s) printed, {$s['existing']} rung(s) already covered, {$s['blueprints']} active blueprint(s).");

        return self::SUCCESS;
    }
}
