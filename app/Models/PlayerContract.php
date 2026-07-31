<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerContract extends Model
{
    protected $fillable = [
        'user_id', 'npc_key', 'icon', 'title', 'intro', 'signoff',
        'completion_mode', 'required_count', 'status',
        'issued_at_tick', 'expires_at_tick', 'reward_xp', 'reward_kes', 'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(PlayerContractObjective::class, 'contract_id');
    }

    public function completedObjectivesCount(): int
    {
        return $this->objectives->where('is_complete', true)->count();
    }

    /** How many objectives must be done (mode 'all' needs every one). */
    public function neededCount(): int
    {
        $total = $this->objectives->count();
        return $this->completion_mode === 'all' ? $total : min((int) $this->required_count, $total);
    }

    public function isSatisfied(): bool
    {
        return $this->completedObjectivesCount() >= $this->neededCount();
    }

    public function daysLeft(int $currentTick): int
    {
        return max(0, (int) $this->expires_at_tick - $currentTick);
    }

    public function npc(): array
    {
        $npc = config("pesa_voice.npcs.{$this->npc_key}");
        return $npc ? (['key' => $this->npc_key] + $npc) : ['key' => $this->npc_key, 'name' => 'Pesa City', 'emoji' => '🏙️'];
    }
}
