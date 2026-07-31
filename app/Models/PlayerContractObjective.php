<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerContractObjective extends Model
{
    protected $fillable = [
        'contract_id', 'archetype', 'metric', 'style', 'label', 'icon', 'lesson',
        'baseline', 'goal', 'progress', 'is_complete',
    ];

    protected $casts = ['is_complete' => 'boolean'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(PlayerContract::class, 'contract_id');
    }

    public function progressPercent(): int
    {
        if ($this->is_complete) return 100;
        if ((int) $this->goal <= 0) return 0;
        return (int) min(99, floor(((int) $this->progress / (int) $this->goal) * 100));
    }
}
