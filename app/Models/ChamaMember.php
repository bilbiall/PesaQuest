<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChamaMember extends Model
{
    protected $fillable = [
        'chama_id', 'user_id', 'role', 'total_contributed',
        'share_pct', 'joined_at', 'is_active',
    ];

    protected $casts = [
        'joined_at'  => 'datetime',
        'is_active'  => 'boolean',
        'share_pct'  => 'float',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(ChamaLoan::class, 'borrower_member_id');
    }

    public function isChairman(): bool
    {
        return $this->role === 'chairman';
    }

    /** Whether this member's last N monthly contributions were all paid,
     *  consecutively, with no gap — the loan-eligibility gate. */
    public function hasContributionStreak(int $months): bool
    {
        $recent = ChamaContribution::where('chama_id', $this->chama_id)
            ->where('user_id', $this->user_id)
            ->where('status', 'paid')
            ->orderByDesc('game_month')
            ->limit($months)
            ->pluck('game_month');

        if ($recent->count() < $months) return false;

        // game_month is formatted 'GM-0001' — consecutive means each one is
        // exactly one less than the previous when read as an integer.
        $numbers = $recent->map(fn ($gm) => (int) substr($gm, 3))->values();
        for ($i = 0; $i < $numbers->count() - 1; $i++) {
            if ($numbers[$i] - $numbers[$i + 1] !== 1) return false;
        }
        return true;
    }

    /** Total principal this member currently owes across active chama loans. */
    public function outstandingLoanBalance(): float
    {
        return (float) $this->loans()->where('status', 'active')->sum('outstanding_balance');
    }

    /** How much of their own contributed capital a member can withdraw right
     *  now without leaving the chama — their stake minus what they still owe
     *  the pool, so a defaulting borrower can't also drain their "share". */
    public function vestedWithdrawable(): float
    {
        return max(0, (float) $this->total_contributed - $this->outstandingLoanBalance());
    }
}
