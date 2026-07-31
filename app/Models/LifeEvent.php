<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifeEvent extends Model
{
    protected $fillable = [
        'slug', 'chapter', 'asset_category', 'title', 'description', 'flavor_text', 'educational_note',
        'effect_type', 'effect_data', 'probability', 'icon', 'is_positive', 'is_active',
    ];

    protected $casts = [
        'effect_data' => 'array',
        'is_positive' => 'boolean',
        'is_active'   => 'boolean',
        'probability' => 'float',
    ];

    public function playerEvents(): HasMany
    {
        return $this->hasMany(PlayerLifeEvent::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForChapter($query, string $chapter)
    {
        return $query->where(function ($q) use ($chapter) {
            $q->where('chapter', $chapter)->orWhere('chapter', 'all');
        });
    }

    /** Scope: only general events (no asset_category restriction). */
    public function scopeGeneral($query)
    {
        return $query->whereNull('asset_category');
    }

    /** Scope: only events tied to specific asset categories the player owns. */
    public function scopeForAssetCategories($query, array $categories)
    {
        if (empty($categories)) {
            return $query->whereNull('asset_category');
        }
        return $query->where(function ($q) use ($categories) {
            $q->whereNull('asset_category')->orWhereIn('asset_category', $categories);
        });
    }
}
