<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcadeMatchInvite extends Model
{
    protected $fillable = [
        'arcade_match_id', 'invited_by', 'invited_user_id', 'status',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(ArcadeMatch::class, 'arcade_match_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }
}
