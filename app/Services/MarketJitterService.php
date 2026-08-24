<?php

namespace App\Services;

use App\Models\ForumReply;
use App\Models\ForumTopic;
use App\Models\MarketJitter;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * "Market Jitters" — sudden, broad share-price shocks that fire entirely on
 * their own once game time reaches their preset moment, so the whole roster
 * of shocks a player will ever see is bulk-seeded once (by game year) rather
 * than hand-triggered by an admin every time.
 *
 * Two phases, both posted to the Forums in the same "Market Watch" category
 * regular news bulletins use: sendDueWarnings() posts a vague, numbers-free
 * heads-up a few game days ahead (mirroring the Financial Crisis engine's
 * warning_at step) — direction-flavored but never revealing the exact size
 * or timing precisely enough to game it. applyDue() then actually moves the
 * price and replies to that same thread with what really happened, exactly
 * like ShareNewsService's publish-then-announce split.
 */
class MarketJitterService
{
    public function __construct(private ShareMarketService $market)
    {
    }

    /** Posts the vague heads-up for every jitter whose warn_at has come due.
     *  Safe to call as often as you like — a no-op once nothing is due. */
    public function sendDueWarnings(): int
    {
        $due = MarketJitter::warnDue()->get();

        foreach ($due as $jitter) {
            [$headline, $body] = $this->warningCopy($jitter);

            $topic = ForumTopic::create([
                'user_id'          => $this->wireUser()->id,
                'posted_by_name'   => 'Pesa City News',
                'title'            => $headline,
                'slug'             => Str::slug($headline) . '-' . $jitter->id,
                'body'             => $body,
                'category'         => 'market-watch',
                'is_pinned'        => true,
                'last_activity_at' => now(),
            ]);

            $jitter->update([
                'warned_at'      => now(),
                'forum_topic_id' => $topic->id,
            ]);
        }

        return $due->count();
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

            $topicId = $jitter->forum_topic_id;

            if ($topicId) {
                // A warning already posted — reveal the outcome as a reply on
                // that same thread, same pattern as ShareNewsService's
                // publish-then-announce, and unpin it now that it's resolved.
                ForumReply::create([
                    'topic_id'         => $topicId,
                    'user_id'          => $this->wireUser()->id,
                    'body'             => $this->outcomeCopy($jitter),
                    'is_market_update' => true,
                ]);
                $jitter->forumTopic?->update(['last_activity_at' => now(), 'is_pinned' => false]);
                $jitter->forumTopic?->increment('replies_count');
            } else {
                // No warning went out (e.g. a jitter added after its own
                // warn window already passed) — fall back to a single
                // reveal-everything-at-once post, same as before this
                // feature existed.
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
                $topicId = $topic->id;
            }

            $jitter->update([
                'status'         => 'applied',
                'applied_at'     => now(),
                'forum_topic_id' => $topicId,
            ]);
        }

        return $due->count();
    }

    /** Vague, numbers-free heads-up — hints at scope/sector/direction only,
     *  never the size or exact day, so it's a genuine "something's brewing"
     *  moment rather than a spoiler. */
    private function warningCopy(MarketJitter $jitter): array
    {
        if ($jitter->scope === 'sector') {
            $headline = $jitter->direction === 'up'
                ? "📊 Market Watch: Buzz is building around the {$jitter->sector} sector"
                : "📊 Market Watch: Whispers of trouble in the {$jitter->sector} sector";
            $body = $jitter->direction === 'up'
                ? "Word on the street is something's brewing for {$jitter->sector} shares. Nothing confirmed yet — but it's worth watching if you're holding any."
                : "Nothing official, but murmurs of trouble are swirling around {$jitter->sector} shares. If you're holding any, keep a close eye over the next few days.";
        } else {
            $headline = $jitter->direction === 'up'
                ? '📊 Market Watch: Momentum is quietly building across the market'
                : '📊 Market Watch: Analysts are flagging rising uncertainty across the board';
            $body = $jitter->direction === 'up'
                ? "Traders are buzzing about something market-wide brewing. Nothing confirmed yet — but it might be worth watching your whole portfolio over the next few days."
                : "There's a nervous mood spreading across the market. Nothing official yet, but a broad move could be coming.";
        }

        $body .= "\n\nNobody can time these moves perfectly — a diversified portfolio always weathers uncertainty better than a concentrated one.";

        return [$headline, $body];
    }

    /** The reveal reply, posted once the move has already happened —
     *  reading it can only ever tell a player what they missed, same
     *  philosophy as ShareNewsService::postOutcomeReply. */
    private function outcomeCopy(MarketJitter $jitter): string
    {
        $moved = $jitter->direction === 'up' ? 'climbed' : 'slid';
        $where = $jitter->scope === 'sector' ? "the {$jitter->sector} sector" : 'the market';

        return "📰 Update: It happened — {$jitter->name}. {$jitter->description} "
            . "{$where} has already {$moved}." . ($jitter->lesson ? "\n\n{$jitter->lesson}" : '');
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
