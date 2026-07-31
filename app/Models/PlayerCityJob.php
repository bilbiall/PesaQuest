<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerCityJob extends Model
{
    protected $fillable = [
        'user_id', 'city_job_id', 'status', 'xp_awarded', 'employment_type',
        'started_at', 'ended_at', 'ticks_employed',
        'pending_salary', 'unpaid_ticks', 'gig_ends_tick', 'cooldown_until_tick',
        'missed_paydays', 'removal_warned_at_tick',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(CityJob::class, 'city_job_id');
    }
}
