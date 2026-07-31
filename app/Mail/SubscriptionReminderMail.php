<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $reminderType,  // '7d', '3d', '1d', 'due', '4d_overdue', '14d_overdue'
        public ?string $expiresAt = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            '7d'          => '⏰ Your PesaQuest Premium expires in 7 days',
            '3d'          => '⚠️ 3 days left on your PesaQuest subscription',
            '1d'          => '🚨 Last day — your PesaQuest Premium expires tomorrow',
            'due'         => '📅 Your PesaQuest Premium has expired today',
            '4d_overdue'  => '💔 Your PesaQuest Premium expired 4 days ago',
            '14d_overdue' => '🏙️ Pesa City misses you — your subscription expired 2 weeks ago',
        ];
        return new Envelope(subject: $subjects[$this->reminderType] ?? 'Your PesaQuest Subscription');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.subscription-reminder');
    }
}
