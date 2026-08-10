<?php

namespace App\Services;

use App\Models\ForumTopic;
use App\Models\Share;
use App\Models\ShareNewsItem;
use App\Models\ShareNewsTemplate;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * "Market Watch" — occasional, indirect news bulletins that telegraph a
 * future share price move without spelling it out. Published to the Forums
 * (Community) as the one place a keen player can catch wind of them; the
 * real effect lands days later, so acting on a hunch is genuinely a bet, not
 * a certainty — some bulletins are duds by design.
 */
class ShareNewsService
{
    /** At most this many news items unresolved at once — keeps it rare and
     *  each one worth noticing, not a constant ticker. */
    private const MAX_CONCURRENT_LIVE = 1;

    /** Fraction of published news that's actually true — high enough that
     *  paying attention is worth it, low enough that it's never a no-brainer. */
    private const TRUTH_RATE = 0.65;

    /** How many of the most recently published items' templates to avoid
     *  repeating, so the pool needs to be genuinely varied to never repeat. */
    private const NO_REPEAT_WINDOW = 15;

    /** Real game-days between publish and the effect landing — the window a
     *  keen player has to act before the price actually moves. */
    private const EFFECT_DELAY_MIN_TICKS = 2;
    private const EFFECT_DELAY_MAX_TICKS = 5;

    /** The hidden, actual size of the move once it lands. */
    private const MAGNITUDE_MIN_PCT = 4.0;
    private const MAGNITUDE_MAX_PCT = 14.0;

    public function __construct(
        private GameClock $clock,
        private ShareMarketService $market,
    ) {}

    /** Rolls the dice on whether today's the day a new bulletin drops. Call
     *  this once a day; it decides internally whether anything happens.
     *  Kept low deliberately — this is a rare, special event to notice, not
     *  a routine feed a player could learn to lean on. */
    public function maybePublish(float $chance = 0.12): ?ShareNewsItem
    {
        if (lcg_value() > $chance) {
            return null;
        }

        return $this->publish();
    }

    public function publish(): ?ShareNewsItem
    {
        if (ShareNewsItem::where('status', 'scheduled')->count() >= self::MAX_CONCURRENT_LIVE) {
            return null;
        }

        $recentTemplateIds = ShareNewsItem::latest('created_at')
            ->take(self::NO_REPEAT_WINDOW)
            ->pluck('template_id')
            ->filter()
            ->all();

        $pool = ShareNewsTemplate::active()->whereNotIn('id', $recentTemplateIds)->get();
        if ($pool->isEmpty()) {
            $pool = ShareNewsTemplate::active()->get(); // pool exhausted — fall back rather than go silent
        }
        if ($pool->isEmpty()) {
            return null;
        }

        $template = $pool->random();

        $subjectShare = null;
        $sector       = null;

        if ($template->scope === 'company') {
            $subjectShare = Share::active()->inRandomOrder()->first();
            if (!$subjectShare) return null;
            $name = $subjectShare->name;
        } else {
            $sector = $template->sector;
            if (!$sector || !Share::active()->where('sector', $sector)->exists()) {
                return null; // sector isn't populated right now — skip quietly
            }
            $name = "the {$sector} sector";
        }

        $rendered = $template->render($name);
        $isTrue   = lcg_value() < self::TRUTH_RATE;
        $ticks    = rand(self::EFFECT_DELAY_MIN_TICKS, self::EFFECT_DELAY_MAX_TICKS);

        $item = ShareNewsItem::create([
            'template_id'    => $template->id,
            'headline'       => $rendered['headline'],
            'flavor'         => $rendered['flavor'],
            'lesson'         => $template->lesson,
            'scope'          => $template->scope,
            'share_id'       => $subjectShare?->id,
            'sector'         => $sector,
            'is_true'        => $isTrue,
            'direction'      => $template->sentiment,
            'magnitude_pct'  => round(self::MAGNITUDE_MIN_PCT + lcg_value() * (self::MAGNITUDE_MAX_PCT - self::MAGNITUDE_MIN_PCT), 2),
            'published_at'   => now(),
            'effect_at'      => now()->addSeconds($this->clock->realSecondsForTicks($ticks)),
            'status'         => 'scheduled',
        ]);

        $topic = ForumTopic::create([
            'user_id'         => $this->wireUser()->id,
            'posted_by_name'  => 'Pesa City Wire',
            'title'           => $item->headline,
            'slug'            => Str::slug($item->headline) . '-' . $item->id,
            'body'            => $item->flavor . "\n\nNot every story pans out — trade on your own judgement.",
            'category'        => 'market-watch',
            'is_pinned'       => true,
            'last_activity_at'=> now(),
        ]);

        $item->update(['forum_topic_id' => $topic->id]);

        return $item;
    }

    /** Applies the outcome of every item whose effect has come due — moves
     *  the price for real bulletins, does nothing (by design) for duds, and
     *  posts the outcome back to the forum topic either way. */
    public function resolveDue(): int
    {
        $due = ShareNewsItem::where('status', 'scheduled')
            ->where('effect_at', '<=', now())
            ->with(['share', 'forumTopic'])
            ->get();

        foreach ($due as $item) {
            if ($item->is_true) {
                foreach ($item->affectedShares() as $share) {
                    // Sector-wide moves jitter per share so it never reads as
                    // one suspiciously uniform push across the whole sector.
                    $jitter = $item->scope === 'sector' ? (0.8 + lcg_value() * 0.4) : 1.0;
                    $this->market->applyNewsDrift($share, $item->direction, $item->magnitude_pct * $jitter);
                }
            }

            $item->update(['status' => 'resolved', 'resolved_at' => now()]);
            $this->postOutcomeReply($item);
        }

        return $due->count();
    }

    private function postOutcomeReply(ShareNewsItem $item): void
    {
        if (!$item->forum_topic_id) return;

        $outcome = $item->is_true
            ? 'Looks like this one was real — ' . ($item->direction === 'up' ? 'expect a gradual climb over the next while, not an overnight jump. 📈' : 'expect a gradual slide over the next while, not an overnight crash. 📉')
            : "In the end, nothing came of it — the price just does its normal thing. Not every story pans out.";

        \App\Models\ForumReply::create([
            'topic_id' => $item->forum_topic_id,
            'user_id'  => $this->wireUser()->id,
            'body'     => "📰 Update: {$outcome}\n\n{$item->lesson}",
        ]);

        $item->forumTopic?->increment('replies_count');
        // Pinned only while the story is still live and actionable — once it
        // resolves it un-pins itself and quietly recedes into the ordinary
        // feed (still findable via the Market Watch filter, just no longer
        // shouting for attention). That's the "expires" behaviour.
        $item->forumTopic?->update(['last_activity_at' => now(), 'is_pinned' => false]);
    }

    /** The system "author" behind every Market Watch post — created once,
     *  reused forever, same self-healing pattern the rest of this app uses
     *  for anything that must exist but has no natural setup step. */
    private function wireUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'market-watch@pesaquest.system'],
            [
                'name'     => 'Pesa City Wire',
                'username' => 'pesacitywire',
                'password' => bcrypt(Str::random(40)),
                'is_active'=> true,
            ]
        );
    }
}
