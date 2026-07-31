<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    protected $fillable = [
        'node_id', 'next_node_id', 'label', 'description',
        'points', 'sort_order', 'effect_data',
    ];

    protected $casts = [
        'effect_data' => 'array',
    ];

    // The node this choice belongs to
    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    // The node this choice leads to
    public function nextNode()
    {
        return $this->belongsTo(Node::class, 'next_node_id');
    }

    public function getBalanceChangeAttribute(): int
    {
        return $this->effect_data['balance_change'] ?? 0;
    }

    public function getLessonAttribute(): ?string
    {
        return $this->effect_data['lesson'] ?? null;
    }
}
