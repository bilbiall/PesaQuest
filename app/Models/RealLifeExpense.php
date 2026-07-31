<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealLifeExpense extends Model
{
    protected $fillable = ['user_id', 'amount', 'category', 'note', 'spent_on'];

    protected $casts = ['spent_on' => 'date'];

    /** Shares categories with RealLifeBill so the two tools feel like one system. */
    const CATEGORIES = [
        'food'      => ['label' => 'Food',            'icon' => '🍲'],
        'transport' => ['label' => 'Transport',        'icon' => '🚌'],
        'airtime'   => ['label' => 'Airtime / Data',   'icon' => '📱'],
        'clothes'   => ['label' => 'Clothes',          'icon' => '👕'],
        'school'    => ['label' => 'School',           'icon' => '📚'],
        'fun'       => ['label' => 'Fun',              'icon' => '🎮'],
        'health'    => ['label' => 'Health',           'icon' => '💊'],
        'other'     => ['label' => 'Other',            'icon' => '📋'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
