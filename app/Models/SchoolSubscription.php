<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSubscription extends Model
{
    protected $fillable = [
        'school_name', 'contact_email', 'seats', 'max_classes', 'starts_at', 'ends_at',
        'status', 'portal_token', 'price_kes', 'notes', 'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function members()
    {
        return $this->hasMany(SchoolMember::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'school_subscription_id');
    }

    public function teachers()
    {
        return $this->hasMany(SchoolTeacher::class);
    }

    public function activeTeachers()
    {
        return $this->hasMany(SchoolTeacher::class)->where('status', 'active');
    }

    /** The active SchoolTeacher row for a user at this school, if any. */
    public function teacherFor(?User $user): ?SchoolTeacher
    {
        if (!$user) return null;
        return $this->activeTeachers()->where('user_id', $user->id)->first();
    }

    public function activeMembers()
    {
        return $this->hasMany(SchoolMember::class)->where('status', 'active');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function usedSeats(): int
    {
        return $this->activeMembers()->count();
    }

    public function availableSeats(): int
    {
        return max(0, $this->seats - $this->usedSeats());
    }

    public function availableClassSlots(): int
    {
        return max(0, $this->max_classes - $this->classes()->count());
    }

    public function statusLabel(): string
    {
        if ($this->status !== 'active') return ucfirst($this->status);
        return $this->ends_at->isFuture() ? 'Active' : 'Expired';
    }
}
