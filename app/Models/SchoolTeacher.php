<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolTeacher extends Model
{
    protected $fillable = [
        'school_subscription_id', 'school_class_id', 'user_id', 'email', 'name', 'role',
        'invite_token', 'status', 'invited_by', 'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(SchoolSubscription::class, 'school_subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }
}
