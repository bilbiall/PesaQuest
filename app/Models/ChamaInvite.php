<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamaInvite extends Model
{
    protected $fillable = ['chama_id', 'invited_by', 'token', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function chama()
    {
        return $this->belongsTo(Chama::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isValid(): bool
    {
        return !$this->expires_at || $this->expires_at->isFuture();
    }
}
