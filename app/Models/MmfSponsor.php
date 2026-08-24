<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MmfSponsor extends Model
{
    protected $fillable = [
        'name', 'logo_path', 'tagline', 'website_url', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'mmf_sponsor_id');
    }

    /** Null when no logo has been uploaded yet — callers fall back to a
     *  plain badge/initial, same as Asset does for a missing image_url. */
    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset($this->logo_path) : null;
    }
}
