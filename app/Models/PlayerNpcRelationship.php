<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerNpcRelationship extends Model
{
    protected $fillable = ['user_id', 'npc_id', 'score', 'total_interactions'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    public function label(): string
    {
        return match(true) {
            $this->score >= 80 => 'Close',
            $this->score >= 60 => 'Friendly',
            $this->score >= 40 => 'Neutral',
            $this->score >= 20 => 'Strained',
            default            => 'Bad',
        };
    }

    public function emoji(): string
    {
        return match(true) {
            $this->score >= 80 => '❤️',
            $this->score >= 60 => '😊',
            $this->score >= 40 => '😐',
            $this->score >= 20 => '😟',
            default            => '😠',
        };
    }
}
