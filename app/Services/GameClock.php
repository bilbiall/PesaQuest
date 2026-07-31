<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class GameClock
{
    /** Real hours that equal one game week (admin-configurable). */
    public function realHoursPerGameWeek(): float
    {
        return max(0.1, (float) Setting::get('game_clock_real_hours_per_game_week', 1.0));
    }

    /** Seconds of real time that equal one game day (1 tick). */
    public function secondsPerTick(): float
    {
        return ($this->realHoursPerGameWeek() * 3600) / 7;
    }

    /**
     * The most game days a single login's "While You Were Away" catch-up will
     * ever simulate (admin-configurable, in plain game days — NOT real hours;
     * how fast a game day passes is a separate setting, see realHoursPerGameWeek()).
     * No matter how long a player was actually away, only this many game days
     * get simulated — the rest of the absence is simply dropped, never banked
     * for a later visit, so a long-away player never returns to a huge jump.
     */
    public function maxCatchupTicks(): int
    {
        return max(1, min(3650, (int) Setting::get('max_catchup_game_days', 60)));
    }

    /** Number of ticks (game days) that have elapsed since the given timestamp. */
    public function ticksSince(Carbon $since): int
    {
        // Carbon 3 diffs are SIGNED (now()->diffInSeconds($past) is negative),
        // so compute elapsed time from raw timestamps — immune to either version.
        $elapsed = max(0, now()->getTimestamp() - $since->getTimestamp());
        return (int) floor($elapsed / $this->secondsPerTick());
    }

    /** Real seconds that a number of game days (ticks) takes at current clock speed. */
    public function realSecondsForTicks(int $ticks): int
    {
        return (int) round($ticks * $this->secondsPerTick());
    }

    /** Whole game days remaining until a future real timestamp (0 if past). */
    public function gameDaysUntil(Carbon $future): int
    {
        $seconds = $future->getTimestamp() - now()->getTimestamp();
        if ($seconds <= 0) return 0;
        return (int) ceil($seconds / $this->secondsPerTick());
    }

    /** Whole game days elapsed since a real timestamp. */
    public function gameDaysSince(Carbon $past): int
    {
        return $this->ticksSince($past);
    }

    /** Approximate real-world duration for N game days, e.g. "≈2.5 real hrs". */
    public function approxRealLabel(int $ticks): string
    {
        $s = $this->realSecondsForTicks(max(0, $ticks));
        if ($s < 90)    return 'under 2 real min';
        if ($s < 3600)  return '≈' . round($s / 60) . ' real min';
        if ($s < 86400) return '≈' . round($s / 3600, 1) . ' real hrs';
        return '≈' . round($s / 86400, 1) . ' real days';
    }

    /** Human-readable game time label for a number of ticks. */
    public function formatTicks(int $ticks): string
    {
        if ($ticks <= 0) return '0 game days';
        if ($ticks < 7)  return "{$ticks} game day" . ($ticks === 1 ? '' : 's');

        $months = intdiv($ticks, 30);
        $weeks  = intdiv($ticks % 30, 7);
        $days   = $ticks % 7;

        $parts = [];
        if ($months) $parts[] = "{$months} game month" . ($months > 1 ? 's' : '');
        if ($weeks)  $parts[] = "{$weeks} game week"  . ($weeks  > 1 ? 's' : '');
        if ($days)   $parts[] = "{$days} game day"    . ($days   > 1 ? 's' : '');

        return implode(', ', $parts);
    }

    /** Returns a description of what the current admin rate means. */
    public function rateDescription(): string
    {
        $hrs = $this->realHoursPerGameWeek();
        $mins = (int) round($hrs * 60);
        $label = $mins < 60 ? "{$mins} real minutes" : round($hrs, 2) . " real hour" . ($hrs == 1 ? '' : 's');
        return "{$label} = 1 game week";
    }
}
