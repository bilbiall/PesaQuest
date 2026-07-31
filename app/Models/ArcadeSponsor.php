<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArcadeSponsor extends Model
{
    protected $fillable = [
        'name', 'logo_path', 'tagline', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tiles(): HasMany
    {
        return $this->hasMany(ArcadeTile::class, 'arcade_sponsor_id');
    }

    public function logoUrl(): string
    {
        return asset($this->logo_path);
    }
}
