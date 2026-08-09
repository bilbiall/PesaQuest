<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dream extends Model
{
    protected $fillable = [
        'slug', 'name', 'tagline', 'description', 'icon', 'image_url',
        'price', 'category', 'min_level', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price'     => 'integer',
    ];

    public function playerDreams(): HasMany
    {
        return $this->hasMany(PlayerDream::class);
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'property' => 'Property',
            'vehicle'  => 'Vehicle',
            'travel'   => 'Travel',
            'legacy'   => 'Legacy',
            'business' => 'Business',
            default    => 'Lifestyle',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
