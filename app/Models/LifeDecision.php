<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifeDecision extends Model
{
    protected $fillable = [
        'npc_id', 'title', 'body', 'image_url', 'category', 'icon',
        'weight', 'min_tick', 'max_tick', 'min_balance', 'max_balance',
        'required_career_fields', 'is_repeatable', 'cooldown_ticks',
        'is_active', 'created_by', 'gameset_id',
    ];

    protected $casts = [
        'required_career_fields' => 'array',
        'is_repeatable'          => 'boolean',
        'is_active'              => 'boolean',
    ];

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(LifeDecisionChoice::class, 'decision_id')->orderBy('sort_order');
    }

    public function playerDecisions(): HasMany
    {
        return $this->hasMany(PlayerDecision::class, 'decision_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'social'      => 'Social',
            'career'      => 'Career',
            'emergency'   => 'Emergency',
            'opportunity' => 'Opportunity',
            'housing'     => 'Housing',
            'market'      => 'Markets',
            'family'      => 'Family',
            default       => ucfirst($this->category),
        };
    }

    public function categoryColor(): string
    {
        return match($this->category) {
            'social'      => '#6366f1',
            'career'      => '#0ea5e9',
            'emergency'   => '#ef4444',
            'opportunity' => '#10b981',
            'housing'     => '#f59e0b',
            'market'      => '#8b5cf6',
            'family'      => '#ec4899',
            default       => '#6366f1',
        };
    }
}
