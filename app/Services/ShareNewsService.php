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
 * (Community) as the one place a keen player can catch wind of them and
 * discuss it while it's still live; the real effect lands days later, so
 * acting on a hunch is genuinely a bet, not a certainty — some bulletins
 * are duds by design. Resolution happens in two separate steps, on purpose:
 * resolveDue() moves the price the moment its effect_at comes due, but the
 * outcome reply doesn't get posted until announceDue() — a further delay
 * later — picks it up. By the time the forum finds out, the price has
 * already been sitting at its new level for a while, so reading the update
 * can only ever tell you what you missed, never give you time to act on it.
 */
class ShareNewsService
{
    /** At most this many news items unresolved at once — high enough to
     *  sustain a steady ~3-a-week cadence without ever feeling like a
     *  constant ticker (each one still gets its own moment). */
    private const MAX_CONCURRENT_LIVE = 2;

    /** Fraction of published news that's actually true — high enough that
     *  paying attention is worth it, low enough that it's never a no-brainer. */
    private const TRUTH_RATE = 0.65;

    /** How many of the most recently published items' templates to avoid
     *  repeating, so the pool needs to be genuinely varied to never repeat. */
    private const NO_REPEAT_WINDOW = 15;

    /** Backstop: if it's been this many real days since the last bulletin,
     *  force the next roll to publish instead of leaving it to chance — keeps
     *  a run of bad luck from going silent for weeks on a low-probability roll. */
    private const MAX_SILENT_DAYS = 7;

    /** Real game-days between publish and the effect landing — the window a
     *  keen player has to act before the price actually moves. */
    private const EFFECT_DELAY_MIN_TICKS = 2;
    private const EFFECT_DELAY_MAX_TICKS = 5;

    /** Real game-days between the price actually moving and the forum finding
     *  out about it. The outcome reply is the "spoiler" — waiting until the
     *  move has plainly already happened means reading it can never help you
     *  catch the move, only regret missing it (or celebrate having acted). */
    private const ANNOUNCE_DELAY_MIN_TICKS = 2;
    private const ANNOUNCE_DELAY_MAX_TICKS = 4;

    /** The hidden, actual size of the move once it lands. */
    private const MAGNITUDE_MIN_PCT = 4.0;
    private const MAGNITUDE_MAX_PCT = 14.0;

    public function __construct(
        private GameClock $clock,
        private ShareMarketService $market,
    ) {}

    /** Rolls the dice on whether today's the day a new bulletin drops. Call
     *  this once a day; it decides internally whether anything happens.
     *  Combined with MAX_CONCURRENT_LIVE=2 and a ~3.5-tick average resolve
     *  time, this settles into roughly 3 bulletins per game week — frequent
     *  enough to feel alive without becoming background noise — with a
     *  long dry streak forcing a publish rather than staying silent. */
    public function maybePublish(float $chance = 0.45): ?ShareNewsItem
    {
        if (!$this->isOverdue() && lcg_value() > $chance) {
            return null;
        }

        return $this->publish();
    }

    /** True once too many real days have passed since the last bulletin —
     *  including if none has ever been published. */
    private function isOverdue(): bool
    {
        $lastPublishedAt = ShareNewsItem::max('published_at');

        return !$lastPublishedAt || now()->diffInDays($lastPublishedAt) >= self::MAX_SILENT_DAYS;
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
            \Log::warning('Market Watch: no active share_news_templates — has ShareNewsTemplateSeeder ever run?');
            return null;
        }

        if (!Share::active()->exists()) {
            \Log::warning('Market Watch: no active shares to write news about.');
            return null;
        }

        $activeSectors = Share::active()->pluck('sector')->unique();

        // Pick from whichever templates actually have a valid subject right
        // now, rather than randomly landing on one template and giving up —
        // one incompatible pick out of the pool shouldn't cost the whole roll.
        $template = $pool->shuffle()->first(
            fn ($t) => $t->scope === 'company' || $activeSectors->contains($t->sector)
        );

        if (!$template) {
            \Log::warning('Market Watch: no template in the active pool matches any sector currently populated with shares.');
            return null;
        }

        if ($template->scope === 'company') {
            $subjectShare = Share::active()->inRandomOrder()->first();
            $sector       = null;
            $name         = $subjectShare->name;
        } else {
            $subjectShare = null;
            $sector       = $template->sector;
            $name         = "the {$sector} sector";
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
            'posted_by_name'  => 'Pesa City News',
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
     *  the price for real bulletins, does nothing (by design) for duds — but
     *  does NOT tell the forum yet. That happens later, in announceDue(),
     *  once the move has had time to actually show up in the price. */
    public function resolveDue(): int
    {
        $due = ShareNewsItem::where('status', 'scheduled')
            ->where('effect_at', '<=', now())
            ->with(['share'])
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

            $ticks = rand(self::ANNOUNCE_DELAY_MIN_TICKS, self::ANNOUNCE_DELAY_MAX_TICKS);
            $item->update([
                'status'      => 'resolved',
                'resolved_at' => now(),
                'announce_at' => now()->addSeconds($this->clock->realSecondsForTicks($ticks)),
            ]);
        }

        return $due->count();
    }

    /** Posts the outcome reply for every resolved item whose announce_at has
     *  come due — by now the price has already sat at its new level for a
     *  while, so a player only finds out here whether their hunch (or lack
     *  of one) paid off, never in time to act on it. */
    public function announceDue(): int
    {
        $due = ShareNewsItem::where('status', 'resolved')
            ->whereNull('announced_at')
            ->where('announce_at', '<=', now())
            ->with(['forumTopic'])
            ->get();

        foreach ($due as $item) {
            $this->postOutcomeReply($item);
            $item->update(['announced_at' => now()]);
        }

        return $due->count();
    }

    private function postOutcomeReply(ShareNewsItem $item): void
    {
        if (!$item->forum_topic_id) return;

        $outcome = $item->is_true
            ? 'Looks like this one was real — ' . ($item->direction === 'up' ? 'the price has already climbed. 📈' : 'the price has already slid. 📉')
            : "In the end, nothing came of it — the price just did its normal thing. Not every story pans out.";

        \App\Models\ForumReply::create([
            'topic_id'         => $item->forum_topic_id,
            'user_id'          => $this->wireUser()->id,
            'body'             => "📰 Update: {$outcome}\n\n{$item->lesson}",
            'is_market_update' => true,
        ]);

        $item->forumTopic?->increment('replies_count');
        // Pinned only while the story is still live and actionable — once it
        // resolves (and the outcome/lesson above has just been posted, when
        // the move has already happened and it's no longer something to act
        // on) it un-pins itself and quietly recedes into the ordinary feed.
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
                'name'     => 'Pesa City News',
                'username' => 'pesacitynews',
                'password' => bcrypt(Str::random(40)),
                'is_active'=> true,
            ]
        );
    }
}
