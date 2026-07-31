<?php

namespace App\Mail;

use App\Models\GameNotification;
use App\Models\Investment;
use App\Models\PlayerBill;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $summary;

    public function __construct(public User $player)
    {
        $progress = $player->getOrCreateProgress();
        $streak   = $player->streak;

        $activeInvestments = Investment::where('user_id', $player->id)
            ->where('status', 'pending')
            ->orderBy('mature_at')
            ->get();

        $maturedCount = $activeInvestments->filter(fn($i) => $i->mature_at <= now())->count();

        $overdueBills = PlayerBill::where('user_id', $player->id)
            ->where('status', 'overdue')
            ->count();

        $weekNotifs = GameNotification::where('user_id', $player->id)
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        $totalUsers = UserProgress::count();
        $rank       = UserProgress::where('points_total', '>', $progress->points_total)->count();
        $percentile = $totalUsers > 1 ? (int) (100 - ($rank / ($totalUsers - 1) * 100)) : 100;

        $this->summary = [
            'name'               => $player->name,
            'balance'            => $progress->balance,
            'net_worth'          => $progress->net_worth_cache ?? $progress->balance,
            'xp'                 => $progress->points_total,
            'level'              => $progress->level,
            'streak'             => $streak?->current_streak ?? 0,
            'longest_streak'     => $streak?->longest_streak ?? 0,
            'active_investments' => $activeInvestments->count(),
            'matured_count'      => $maturedCount,
            'overdue_bills'      => $overdueBills,
            'week_events'        => $weekNotifs,
            'percentile'         => $percentile,
            'chapter'            => $progress->chapterName(),
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your PesaQuest Week in Review 📊",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-summary',
        );
    }
}
