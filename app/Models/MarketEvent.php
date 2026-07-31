<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketEvent extends Model
{
    protected $fillable = [
        'age_group', 'title', 'description', 'effect_type',
        'effect_amount', 'icon', 'probability', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function userEvents()
    {
        return $this->hasMany(UserMarketEvent::class, 'event_id');
    }

    public static function rollForUser(User $user, string $ageGroup): ?self
    {
        $today = now()->toDateString();

        $triggered = UserMarketEvent::where('user_id', $user->id)
            ->where('triggered_date', $today)
            ->pluck('event_id');

        $events = self::where('is_active', true)
            ->where('age_group', $ageGroup)
            ->whereNotIn('id', $triggered)
            ->get();

        foreach ($events as $event) {
            if (rand(1, 100) <= $event->probability) {
                UserMarketEvent::create([
                    'user_id'        => $user->id,
                    'event_id'       => $event->id,
                    'triggered_date' => $today,
                ]);
                return $event;
            }
        }
        return null;
    }
}
