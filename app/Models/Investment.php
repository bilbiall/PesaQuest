<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'user_id', 'choice_id', 'amount', 'return_rate', 'return_days',
        'return_amount', 'mature_at', 'credited_at', 'status', 'label',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'return_rate'  => 'decimal:2',
        'return_amount'=> 'decimal:2',
        'mature_at'    => 'datetime',
        'credited_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function choice()
    {
        return $this->belongsTo(Choice::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeMatured($query)
    {
        return $query->where('status', 'pending')
                     ->where('mature_at', '<=', now());
    }

    /**
     * Credit the investment returns to the user's progress balance.
     */
    public function credit(UserProgress $progress): void
    {
        $returnAmount = round((float) $this->amount * (1 + (float) $this->return_rate / 100), 2);

        $this->update([
            'return_amount' => $returnAmount,
            'status'        => 'credited',
            'credited_at'   => now(),
        ]);

        $progress->balance += $returnAmount;
        $progress->save();

        GameNotification::create([
            'user_id' => $this->user_id,
            'type'    => 'investment',
            'title'   => 'Investment Returns Credited!',
            'body'    => "Your investment \"{$this->label}\" has matured! Ksh " . number_format($returnAmount, 2) . " has been added to your balance.",
            'icon'    => '📈',
            'data'    => [
                'investment_id' => $this->id,
                'amount'        => $this->amount,
                'return_amount' => $returnAmount,
                'return_rate'   => $this->return_rate,
                'label'         => $this->label,
            ],
        ]);
    }
}
