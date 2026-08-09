<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = ['school_subscription_id', 'name', 'teacher_id'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(SchoolSubscription::class, 'school_subscription_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(SchoolTeacher::class, 'teacher_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(SchoolMember::class);
    }
}
