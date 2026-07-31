<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumTopic extends Model
{
    protected $fillable = [
        'user_id', 'school_subscription_id', 'title', 'slug', 'body', 'category',
        'is_pinned', 'is_locked', 'is_challenge', 'posted_by_name',
        'replies_count', 'views', 'last_activity_at',
    ];

    protected $casts = [
        'is_pinned'        => 'boolean',
        'is_locked'        => 'boolean',
        'is_challenge'     => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(SchoolSubscription::class, 'school_subscription_id');
    }

    public function isSchoolBoard(): bool
    {
        return $this->school_subscription_id !== null;
    }

    public function replies()
    {
        return $this->hasMany(ForumReply::class, 'topic_id');
    }

    /**
     * Topics visible in listings. Placeholder for future soft-hide support.
     */
    public function scopeVisible($query)
    {
        return $query;
    }
}
