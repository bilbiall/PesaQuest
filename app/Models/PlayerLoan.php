<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerLoan extends Model
{
    protected $fillable = [
        'user_id', 'loan_product_id', 'player_asset_id', 'label',
        'principal', 'annual_interest_rate',
        'outstanding_balance', 'payment_amount', 'payment_period_ticks',
        'disbursed_at_tick', 'due_at_tick', 'next_payment_tick',
        'payments_made', 'payments_missed', 'status',
    ];

    protected $casts = [
        'annual_interest_rate' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    public function playerAsset(): BelongsTo
    {
        return $this->belongsTo(PlayerAsset::class, 'player_asset_id');
    }

    /** Display name — asset financing label, or the bank product name. */
    public function displayName(): string
    {
        return $this->label ?: ($this->loanProduct?->name ?? 'Loan');
    }

    /** Total number of installments over the loan term. */
    public function totalInstallments(): int
    {
        $term = $this->loanProduct?->term_ticks
            ?? max(1, ($this->due_at_tick ?? 0) - ($this->disbursed_at_tick ?? 0));
        return (int) ceil($term / max(1, $this->payment_period_ticks));
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'active'    => 'Active',
            'paid'      => 'Paid Off',
            'defaulted' => 'Defaulted',
            default     => 'Unknown',
        };
    }
}
