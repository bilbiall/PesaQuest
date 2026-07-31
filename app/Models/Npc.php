<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Npc extends Model
{
    protected $fillable = [
        'name', 'nickname', 'role', 'avatar_url', 'cover_color',
        'description', 'personality', 'initial_relationship',
        'is_active', 'created_by', 'gameset_id',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function decisions(): HasMany
    {
        return $this->hasMany(LifeDecision::class);
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(PlayerNpcRelationship::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function displayName(): string
    {
        return $this->nickname ?? $this->name;
    }

    public function relationshipLabel(int $score): string
    {
        return match(true) {
            $score >= 80 => 'Close',
            $score >= 60 => 'Friendly',
            $score >= 40 => 'Neutral',
            $score >= 20 => 'Strained',
            default      => 'Bad',
        };
    }
}
