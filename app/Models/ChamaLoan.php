<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamaLoan extends Model
{
    protected $fillable = [
        'chama_id', 'borrower_member_id', 'principal', 'interest_rate',
        'outstanding_balance', 'payment_amount', 'payment_period_ticks',
        'disbursed_at_tick', 'due_at_tick', 'next_payment_tick',
        'payments_made', 'payments_missed', 'status',
    ];

    protected $casts = [
        'interest_rate' => 'float',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function borrowerMember(): BelongsTo
    {
        return $this->belongsTo(ChamaMember::class, 'borrower_member_id');
    }

    public function totalInstallments(): int
    {
        $term = max(1, $this->due_at_tick - $this->disbursed_at_tick);
        return (int) ceil($term / max(1, $this->payment_period_ticks));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active'    => 'Active',
            'paid'      => 'Paid Off',
            'defaulted' => 'Defaulted',
            default     => 'Unknown',
        };
    }
}
