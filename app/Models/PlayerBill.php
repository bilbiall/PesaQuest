<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerBill extends Model
{
    protected $fillable = [
        'user_id', 'bill_id', 'amount', 'frequency_ticks',
        'next_due_tick', 'last_paid_tick', 'status',
        'missed_count', 'overdue_since_tick',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    /** Ticks until this bill is due (negative = overdue). */
    public function ticksUntilDue(int $currentTick): int
    {
        return $this->next_due_tick - $currentTick;
    }

    public function isOverdue(int $currentTick): bool
    {
        return $this->next_due_tick <= $currentTick || $this->status === 'overdue';
    }

    public function urgencyClass(int $currentTick): string
    {
        $ticks = $this->ticksUntilDue($currentTick);
        if ($ticks <= 0)  return 'overdue';
        if ($ticks <= 5)  return 'urgent';
        if ($ticks <= 15) return 'soon';
        return 'ok';
    }
}
