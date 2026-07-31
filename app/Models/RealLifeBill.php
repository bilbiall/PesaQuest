<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealLifeBill extends Model
{
    protected $fillable = [
        'user_id', 'name', 'icon', 'category', 'amount', 'next_due_date',
        'is_recurring', 'frequency_days', 'reminder_lead_days',
        'last_reminded_at', 'status', 'notes',
    ];

    protected $casts = [
        'next_due_date'     => 'date',
        'is_recurring'      => 'boolean',
        'last_reminded_at'  => 'datetime',
    ];

    const CATEGORIES = [
        'rent'      => ['label' => 'Rent / Housing',  'icon' => '🏠'],
        'utilities' => ['label' => 'Utilities',        'icon' => '💡'],
        'airtime'   => ['label' => 'Airtime / Data',   'icon' => '📱'],
        'subscription' => ['label' => 'Subscription',  'icon' => '📺'],
        'loan'      => ['label' => 'Loan Repayment',   'icon' => '🏦'],
        'insurance' => ['label' => 'Insurance',        'icon' => '🛡️'],
        'school'    => ['label' => 'School Fees',      'icon' => '📚'],
        'other'     => ['label' => 'Other',            'icon' => '🧾'],
    ];

    /** Common recurrence presets, in days. Custom values are allowed too. */
    const FREQUENCIES = [
        7   => 'Weekly',
        14  => 'Fortnightly',
        30  => 'Monthly',
        90  => 'Every 3 months',
        365 => 'Yearly',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDueWithin(int $days): bool
    {
        return $this->next_due_date->lte(now()->addDays($days)->endOfDay());
    }

    public function isOverdue(): bool
    {
        return $this->next_due_date->lt(now()->startOfDay());
    }

    /** Roll the due date forward by its frequency, or mark completed if one-off. Logs the payment either way. */
    public function advanceOrComplete(): void
    {
        RealLifeBillPayment::create([
            'user_id'           => $this->user_id,
            'real_life_bill_id' => $this->id,
            'bill_name'         => $this->name,
            'amount'            => $this->amount,
            'paid_on'           => now()->toDateString(),
        ]);

        if ($this->is_recurring && $this->frequency_days) {
            $this->next_due_date = $this->next_due_date->copy()->addDays($this->frequency_days);
            $this->last_reminded_at = null;
            $this->save();
        } else {
            $this->update(['status' => 'completed']);
        }
    }
}
