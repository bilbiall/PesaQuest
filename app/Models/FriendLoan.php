<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FriendLoan extends Model
{
    /** Lender rate presets (%) — the 5% floor stops "free transfer" farming between accounts. */
    public const RATE_PRESETS = [5, 10, 15, 20];

    /** Borrower repayment-term presets, in game days. */
    public const TERM_PRESETS = [10, 20, 30];

    /** Amount presets (Ksh) the borrower can pick from. */
    public const AMOUNT_PRESETS = [500, 1000, 2500, 5000, 10000, 20000];

    /** A lender may commit at most this share of their cash to one loan. */
    public const MAX_LEND_SHARE = 0.20;

    /** Open loans allowed per player on each side (borrowing / lending). */
    public const MAX_OPEN_PER_SIDE = 3;

    /** Minimum player level before P2P lending unlocks. */
    public const MIN_LEVEL = 3;

    protected $fillable = [
        'lender_id', 'borrower_id', 'amount', 'term_ticks', 'rate_pct', 'counter_rate_pct',
        'status', 'total_due', 'amount_repaid', 'disbursed_at_tick', 'due_at_tick',
        'negotiation_expires_at',
    ];

    protected $casts = ['negotiation_expires_at' => 'datetime'];

    public function lender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lender_id');
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['requested', 'offered', 'countered', 'active'], true);
    }

    public function remaining(): int
    {
        return max(0, (int) $this->total_due - (int) $this->amount_repaid);
    }

    /** The rate a pending agreement would settle at (counter overrides offer). */
    public function effectiveRate(): ?int
    {
        return $this->counter_rate_pct ?? $this->rate_pct;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'requested' => 'Waiting for rate offer',
            'offered'   => 'Rate offered',
            'countered' => 'Counter-offer made',
            'active'    => 'Active',
            'declined'  => 'Declined',
            'expired'   => 'Expired',
            'repaid'    => 'Repaid',
            'defaulted' => 'Defaulted',
            default     => ucfirst($this->status),
        };
    }

    /** Count of a user's open loans on one side ('lender_id' or 'borrower_id'). */
    public static function openCountFor(int $userId, string $side): int
    {
        return static::where($side, $userId)
            ->whereIn('status', ['requested', 'offered', 'countered', 'active'])
            ->count();
    }
}
