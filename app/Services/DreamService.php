<?php

namespace App\Services;

use App\Models\Dream;
use App\Models\GameNotification;
use App\Models\PlayerDream;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Dreams — expensive, purely cosmetic purchases. Never resellable (no sell
 * path exists) and never added to net worth (would let players launder cash
 * into net-worth-gated unlocks — see Dream model / GameSet decision).
 */
class DreamService
{
    /**
     * Catalog visible to this player: every active dream, ordered — including
     * ones the player's level isn't high enough for yet. Those are decorated
     * with `level_locked` rather than excluded, so the catalog can show what a
     * player is working toward instead of just leaving a gap where it'd be.
     */
    public function eligibleFor(User $user): Collection
    {
        $level = $user->getOrCreateProgress()->level ?? 1;

        return Dream::active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get()
            ->each(fn (Dream $d) => $d->level_locked = (bool) ($d->min_level && $level < $d->min_level));
    }

    public function alreadyOwns(User $user, Dream $dream): bool
    {
        return PlayerDream::where('user_id', $user->id)->where('dream_id', $dream->id)->exists();
    }

    /**
     * @return array{ok: bool, error?: string, player_dream?: PlayerDream}
     */
    public function purchase(User $user, Dream $dream): array
    {
        if (!$dream->is_active) {
            return ['ok' => false, 'error' => 'This dream is no longer available.'];
        }
        if ($this->alreadyOwns($user, $dream)) {
            return ['ok' => false, 'error' => 'You already own this dream.'];
        }

        $progress = $user->getOrCreateProgress();
        $level    = $progress->level ?? 1;
        if ($dream->min_level && $level < $dream->min_level) {
            return ['ok' => false, 'error' => "Reach level {$dream->min_level} to afford this dream."];
        }
        if (($progress->balance ?? 0) < $dream->price) {
            $shortfall = number_format($dream->price - $progress->balance);
            return ['ok' => false, 'error' => "Need KES {$shortfall} more to claim this dream."];
        }

        $progress->balance -= $dream->price;
        $progress->save();

        $playerDream = PlayerDream::create([
            'user_id'      => $user->id,
            'dream_id'     => $dream->id,
            'price_paid'   => $dream->price,
            'purchased_at' => now(),
        ]);

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'dream_purchased',
            'title'   => "{$dream->icon} Dream Achieved — {$dream->name}",
            'body'    => "You claimed \"{$dream->name}\" for KES " . number_format($dream->price) . ". It's yours forever — flex it on your profile.",
            'icon'    => $dream->icon,
            'data'    => ['dream_id' => $dream->id, 'price' => $dream->price],
        ]);

        return ['ok' => true, 'player_dream' => $playerDream];
    }
}
