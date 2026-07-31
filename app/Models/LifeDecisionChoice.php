<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifeDecisionChoice extends Model
{
    protected $fillable = [
        'decision_id', 'sort_order', 'label', 'description',
        'outcome_text', 'financial_lesson',
        'balance_delta', 'credit_score_delta', 'relationship_delta',
        'xp_delta', 'badge_slug',
    ];

    protected $casts = [
        'balance_delta'       => 'integer',
        'credit_score_delta'  => 'integer',
        'relationship_delta'  => 'integer',
        'xp_delta'            => 'integer',
    ];

    public function decision(): BelongsTo
    {
        return $this->belongsTo(LifeDecision::class, 'decision_id');
    }

    public function formattedDelta(): string
    {
        if ($this->balance_delta === 0) return '';
        $sign = $this->balance_delta > 0 ? '+' : '';
        return $sign . 'Ksh ' . number_format(abs($this->balance_delta));
    }
}
