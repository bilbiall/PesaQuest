<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeTemplate extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'metric', 'style', 'icon', 'image_url',
        'default_duration_days', 'level_min', 'level_max',
        'allow_player_created', 'allow_broadcast', 'is_active',
    ];

    protected $casts = [
        'allow_player_created' => 'boolean',
        'allow_broadcast'      => 'boolean',
        'is_active'            => 'boolean',
    ];

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
