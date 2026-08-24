<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareNewsItem extends Model
{
    protected $fillable = [
        'template_id', 'headline', 'flavor', 'lesson', 'scope',
        'share_id', 'sector', 'is_true', 'direction', 'magnitude_pct',
        'published_at', 'effect_at', 'status', 'resolved_at', 'forum_topic_id',
        'announce_at', 'announced_at',
    ];

    protected $casts = [
        'is_true'       => 'boolean',
        'magnitude_pct' => 'float',
        'published_at'  => 'datetime',
        'effect_at'     => 'datetime',
        'resolved_at'   => 'datetime',
        'announce_at'   => 'datetime',
        'announced_at'  => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ShareNewsTemplate::class, 'template_id');
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class);
    }

    public function forumTopic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isDue(): bool
    {
        return $this->isPending() && $this->effect_at->isPast();
    }

    /** Every share this item touches once resolved — one for company scope,
     *  every active share in the sector for sector scope. */
    public function affectedShares()
    {
        if ($this->scope === 'company') {
            return $this->share ? collect([$this->share]) : collect();
        }

        return Share::active()->where('sector', $this->sector)->get();
    }
}
