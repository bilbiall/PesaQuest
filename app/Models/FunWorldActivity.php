<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunWorldActivity extends Model
{
    protected $fillable = [
        'name', 'icon', 'description', 'price',
        'mood_boost_base', 'xp_reward', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Actual mood boost using the game formula, seeded from price. */
    public function moodBoost(): int
    {
        return (int) min(25, max(5, max($this->mood_boost_base, (int) ($this->price / 200))));
    }
}
