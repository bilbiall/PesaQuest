<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'icon', 'district_slug',
        'sequence_order', 'requirements', 'rewards', 'badge_slug',
        'age_group', 'is_active',
    ];

    protected $casts = [
        'requirements' => 'array',
        'rewards'      => 'array',
        'is_active'    => 'boolean',
    ];

    public function playerMissions(): HasMany
    {
        return $this->hasMany(PlayerMission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
