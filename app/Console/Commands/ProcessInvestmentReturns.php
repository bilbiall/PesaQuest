<?php

namespace App\Console\Commands;

use App\Models\Investment;
use Illuminate\Console\Command;

class ProcessInvestmentReturns extends Command
{
    protected $signature = 'game:process-investments';

    protected $description = 'Process matured investment returns and notify users';

    public function handle(): int
    {
        $matured = Investment::with(['user.progress'])
            ->where('status', 'pending')
            ->where('mature_at', '<=', now())
            ->get();

        if ($matured->isEmpty()) {
            $this->info('No matured investments to process.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($matured as $investment) {
            $user = $investment->user;

            if (!$user) {
                $this->warn("Investment #{$investment->id} has no associated user — skipping.");
                continue;
            }

            $progress = $user->getOrCreateProgress();

            $investment->credit($progress);

            $this->info(
                "Credited investment #{$investment->id} for user {$user->email}: " .
                "Ksh {$investment->return_amount} (was Ksh {$investment->amount} @ {$investment->return_rate}%)"
            );

            $count++;
        }

        $this->info("Done. Processed {$count} investment(s).");

        return self::SUCCESS;
    }
}
