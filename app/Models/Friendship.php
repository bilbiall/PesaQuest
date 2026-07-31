<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    protected $fillable = ['requester_id', 'addressee_id', 'status', 'responded_at'];

    protected $casts = ['responded_at' => 'datetime'];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    /** The friendship row between two users, whichever direction it was sent. */
    public static function between(int $userA, int $userB): ?self
    {
        return static::where(fn ($q) => $q->where('requester_id', $userA)->where('addressee_id', $userB))
            ->orWhere(fn ($q) => $q->where('requester_id', $userB)->where('addressee_id', $userA))
            ->first();
    }

    public static function areFriends(int $userA, int $userB): bool
    {
        $f = static::between($userA, $userB);
        return $f !== null && $f->status === 'accepted';
    }

    /** The other party of this friendship, from $userId's point of view. */
    public function otherUser(int $userId): ?User
    {
        return $this->requester_id === $userId ? $this->addressee : $this->requester;
    }
}
