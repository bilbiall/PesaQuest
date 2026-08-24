<?php

namespace App\Services;

use App\Models\ForumTopic;
use App\Models\MarketJitter;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * "Market Jitters" — sudden, broad share-price shocks that fire entirely on
 * their own once game time reaches their preset moment, so the whole roster
 * of shocks a player will ever see is bulk-seeded once (by game year) rather
 * than hand-triggered by an admin every time. Unlike Market Watch (a slow
 * hinted story with a separate later reveal), a jitter applies its price
 * move and posts its explanation to the Forums in the same pass — there's
 * nothing to "catch" in advance, it's just weather.
 */
class MarketJitterService
{
    public function __construct(private ShareMarketService $market)
    {
    }

    /** Applies every jitter whose scheduled_at has come due. Safe to call as
     *  often as you like — a no-op once nothing is due. */
    public function applyDue(): int
    {
        $due = MarketJitter::due()->get();

        foreach ($due as $jitter) {
            foreach ($jitter->affectedShares() as $share) {
                // Sector-wide moves jitter per share so it never reads as one
                // suspiciously uniform push across the whole sector.
                $variance = $jitter->scope === 'sector' ? (0.8 + lcg_value() * 0.4) : 1.0;
                $this->market->applyNewsDrift($share, $jitter->direction, $jitter->magnitude_pct * $variance, $jitter->window_steps);
            }

            $topic = ForumTopic::create([
                'user_id'          => $this->wireUser()->id,
                'posted_by_name'   => 'Pesa City News',
                'title'            => "📉 Market Jitters: {$jitter->name}",
                'slug'             => Str::slug($jitter->name) . '-jitter-' . $jitter->id,
                'body'             => $jitter->description . ($jitter->lesson ? "\n\n{$jitter->lesson}" : ''),
                'category'         => 'market-watch',
                'is_pinned'        => false,
                'last_activity_at' => now(),
            ]);

            $jitter->update([
                'status'         => 'applied',
                'applied_at'     => now(),
                'forum_topic_id' => $topic->id,
            ]);
        }

        return $due->count();
    }

    /** The system "author" behind every Market Jitters post — same shared
     *  Pesa City News account Market Watch posts under. */
    private function wireUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'market-watch@pesaquest.system'],
            [
                'name'      => 'Pesa City News',
                'username'  => 'pesacitynews',
                'password'  => bcrypt(Str::random(40)),
                'is_active' => true,
            ]
        );
    }
}
