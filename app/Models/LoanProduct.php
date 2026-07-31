<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanProduct extends Model
{
    protected $fillable = [
        'name', 'icon', 'description',
        'min_amount', 'max_amount',
        'annual_interest_rate', 'term_ticks', 'payment_period_ticks',
        'min_credit_score', 'is_active', 'sort_order', 'created_by',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'annual_interest_rate' => 'float',
    ];

    public function playerLoans(): HasMany
    {
        return $this->hasMany(PlayerLoan::class, 'loan_product_id');
    }

    /** Monthly interest rate from annual */
    public function periodicRate(): float
    {
        // Rate per payment_period_ticks (1 tick ≈ 1 game day; 30 ticks = 1 month)
        $dailyRate = $this->annual_interest_rate / 100 / 365;
        return $dailyRate * $this->payment_period_ticks;
    }

    /** Calculate instalment for a given principal */
    public function calculatePayment(int $principal): int
    {
        $r = $this->periodicRate();
        $n = (int) ceil($this->term_ticks / $this->payment_period_ticks);
        if ($r <= 0 || $n <= 0) return (int) ceil($principal / max(1, $n));
        $payment = $principal * $r * pow(1 + $r, $n) / (pow(1 + $r, $n) - 1);
        return (int) ceil($payment);
    }
}
