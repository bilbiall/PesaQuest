<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerLifeEvent extends Model
{
    protected $fillable = [
        'user_id', 'life_event_id', 'tick_triggered',
        'game_age_at_trigger', 'chapter_at_trigger', 'effect_applied',
    ];

    protected $casts = [
        'effect_applied' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lifeEvent(): BelongsTo
    {
        return $this->belongsTo(LifeEvent::class);
    }

    /** Calendar-style label for when this fired, e.g. "Dec 15, Yr 6" — reads
     *  as an actual date instead of a raw game-day tick count. */
    public function calendarDateLabel(): string
    {
        return app(\App\Services\GameCalendarService::class)->calendarDate((int) $this->tick_triggered)['label'];
    }
}
