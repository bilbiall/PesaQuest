<?php

namespace App\Console\Commands;

use App\Mail\WeeklySummaryMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyEmails extends Command
{
    protected $signature = 'game:weekly-emails {--limit=0 : Max users to email (0 = all)}';

    protected $description = 'Send weekly progress summary emails to all active players';

    public function handle(): int
    {
        $query = User::whereHas('progress')
            ->whereNotNull('email_verified_at');

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No active players to email.');
            return self::SUCCESS;
        }

        $sent   = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new WeeklySummaryMail($user));
                $sent++;
                $this->info("Sent to {$user->email}");
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("Failed for {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Sent: {$sent} · Failed: {$failed}");
        return self::SUCCESS;
    }
}
