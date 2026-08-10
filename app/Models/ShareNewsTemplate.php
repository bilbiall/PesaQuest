<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareNewsTemplate extends Model
{
    protected $fillable = ['headline', 'flavor', 'lesson', 'scope', 'sector', 'sentiment', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ShareNewsItem::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Fills in {name} with the given subject — a share's name for company
     *  scope, or a friendly sector phrase for sector scope. */
    public function render(string $name): array
    {
        return [
            'headline' => str_replace('{name}', $name, $this->headline),
            'flavor'   => str_replace('{name}', $name, $this->flavor),
        ];
    }
}
