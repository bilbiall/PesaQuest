<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'scenario_text', 'age_group', 'type',
        'is_start', 'is_free', 'sort_order', 'story_id', 'icon', 'image_url',
        'theme_color', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_start' => 'boolean',
        'is_free' => 'boolean',
    ];

    // This node's outgoing choices
    public function choices()
    {
        return $this->hasMany(Choice::class)->orderBy('sort_order');
    }

    // Choices that lead TO this node
    public function incomingChoices()
    {
        return $this->hasMany(Choice::class, 'next_node_id');
    }

    // Result/lesson card for this node
    public function result()
    {
        return $this->hasOne(NodeResult::class);
    }

    // Users currently on this node
    public function usersOnNode()
    {
        return $this->hasMany(UserProgress::class, 'current_node_id');
    }

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    // Computed level (for monetization gate)
    public function getNodeIndexAttribute(): int
    {
        return static::where('age_group', $this->age_group)
            ->where('id', '<=', $this->id)
            ->count();
    }

    public function scopeForAgeGroup($query, string $ageGroup)
    {
        return $query->where('age_group', $ageGroup);
    }

    public function scopeStartNodes($query)
    {
        return $query->where('is_start', true);
    }
}
