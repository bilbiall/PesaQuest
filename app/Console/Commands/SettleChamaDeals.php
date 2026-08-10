<?php

namespace App\Console\Commands;

use App\Models\ChamaDeal;
use App\Models\GameNotification;
use Illuminate\Console\Command;

class SettleChamaDeals extends Command
{
    protected $signature = 'game:settle-chama-deals';

    protected $description = 'Resolve matured chama investment deals (win/loss) and notify members';

    public function handle(): int
    {
        $matured = ChamaDeal::where('status', 'pending')
            ->where('resolve_at', '<=', now())
            ->with(['deal', 'chama.activeMembers'])
            ->get();

        if ($matured->isEmpty()) {
            $this->info('No matured chama deals to process.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($matured as $cd) {
            $chama = $cd->chama;
            $deal  = $cd->deal;

            if (!$chama || !$deal) {
                $this->warn("Chama deal #{$cd->id} is missing its chama or deal — skipping.");
                continue;
            }

            $success = (lcg_value() < ($deal->success_probability ?? 0.5));

            if ($success) {
                $returnPct = $deal->min_return_pct + lcg_value() * max(0, $deal->max_return_pct - $deal->min_return_pct);
                $profit    = (int) round($cd->amount_invested * $returnPct / 100);
                $payout    = $cd->amount_invested + $profit;

                $chama->pool_balance        += $payout;
                $chama->undistributed_gains += $profit;
                $chama->save();

                $cd->update(['status' => 'success', 'profit_loss' => $profit, 'resolved_at' => now()]);

                $title = "🎉 {$chama->name}'s deal paid off!";
                $body  = "\"{$deal->title}\" returned +" . number_format($returnPct, 1) . "% — Ksh " . number_format($payout) . ' back in the pool.';
                $icon  = $deal->icon ?? '📈';
            } else {
                $loss = (int) round($cd->amount_invested * ($deal->loss_pct ?? 100) / 100);

                $cd->update(['status' => 'failed', 'profit_loss' => -$loss, 'resolved_at' => now()]);

                $title = "📉 {$chama->name}'s deal didn't pan out";
                $body  = "\"{$deal->title}\" lost Ksh " . number_format($loss) . ' — not every bet wins.';
                $icon  = $deal->icon ?? '📉';
            }

            foreach ($chama->activeMembers as $m) {
                GameNotification::create([
                    'user_id' => $m->user_id,
                    'type'    => $success ? 'chama_deal_success' : 'chama_deal_failed',
                    'title'   => $title,
                    'body'    => $body,
                    'icon'    => $icon,
                    'data'    => ['chama_id' => $chama->id, 'deal_id' => $deal->id],
                ]);
            }

            $this->info("Settled chama deal #{$cd->id} ({$chama->name} / {$deal->title}): " . ($success ? 'success' : 'failed'));
            $count++;
        }

        $this->info("Done. Settled {$count} chama deal(s).");

        return self::SUCCESS;
    }
}
