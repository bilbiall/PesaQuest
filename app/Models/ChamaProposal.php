<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChamaProposal extends Model
{
    protected $fillable = [
        'chama_id', 'proposer_id', 'type', 'proposal_data',
        'title', 'status', 'votes_yes', 'votes_no', 'expires_at',
    ];

    protected $casts = [
        'proposal_data' => 'array',
        'expires_at'    => 'datetime',
    ];

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposer_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ChamaVote::class, 'proposal_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function totalVotes(): int
    {
        return $this->votes_yes + $this->votes_no;
    }

    /** Returns true if yes votes exceed 50% of active member count */
    public function quorum(): bool
    {
        $activeMemberCount = $this->chama->activeMembers()->count();
        if ($activeMemberCount === 0) return false;
        return $this->votes_yes > ($activeMemberCount / 2);
    }

    public function userVoted(int $userId): bool
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    public function userVoteValue(int $userId): ?string
    {
        return $this->votes()->where('user_id', $userId)->value('vote');
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'buy_asset'           => 'Buy Asset',
            'sell_asset'          => 'Sell Asset',
            'change_contribution' => 'Change Contribution',
            'remove_member'       => 'Remove Member',
            default               => ucfirst($this->type),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'voting'   => '#6366f1',
            'passed'   => '#10b981',
            'rejected' => '#f87171',
            'executed' => '#9ca3af',
            default    => '#9ca3af',
        };
    }
}
