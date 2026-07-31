<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'title', 'description', 'age_group', 'cover_image',
        'color', 'icon', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }

    public function startNodes()
    {
        return $this->hasMany(Node::class)->where('is_start', true);
    }
}
