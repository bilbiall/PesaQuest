<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketJitter extends Model
{
    protected $fillable = [
        'name', 'description', 'lesson', 'scope', 'sector',
        'direction', 'magnitude_pct', 'window_steps', 'game_day_offset',
        'scheduled_at', 'status', 'applied_at', 'forum_topic_id',
    ];

    protected $casts = [
        'magnitude_pct'   => 'float',
        'scheduled_at'    => 'datetime',
        'applied_at'      => 'datetime',
    ];

    public function forumTopic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class);
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'scheduled')->where('scheduled_at', '<=', now());
    }

    /** Every share this jitter touches once applied — every active share
     *  for a market-wide jitter, or every active share in the sector. */
    public function affectedShares()
    {
        return $this->scope === 'sector'
            ? Share::active()->where('sector', $this->sector)->get()
            : Share::active()->get();
    }
}
