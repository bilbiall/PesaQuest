<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UploadsImages;
use App\Models\ForumReply;
use App\Models\ForumTopic;
use App\Models\GameNotification;
use App\Models\User;
use App\Services\PlanGate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ForumController extends Controller
{
    use UploadsImages;

    public const CATEGORIES = [
        'general'      => ['icon' => '💬', 'label' => 'General Banter'],
        'money-talk'   => ['icon' => '💰', 'label' => 'Money Talk'],
        'side-hustles' => ['icon' => '🚀', 'label' => 'Side Hustles'],
        'saving-tips'  => ['icon' => '🏦', 'label' => 'Saving & Chamas'],
        'school'       => ['icon' => '🎓', 'label' => 'School Corner'],
        'help'         => ['icon' => '🆘', 'label' => 'Help & Questions'],
    ];

    public const REACTIONS = [
        'helpful'    => ['emoji' => '👍', 'label' => 'Helpful'],
        'facts'      => ['emoji' => '💯', 'label' => 'Facts'],
        'funny'      => ['emoji' => '😂', 'label' => 'Funny'],
        'inspired'   => ['emoji' => '🔥', 'label' => 'Inspired'],
        'mind_blown' => ['emoji' => '🤯', 'label' => 'Mind blown'],
    ];

    /** XP awards */
    private const XP_TOPIC = 40;
    private const XP_REPLY = 25;

    /** Max XP-earning forum posts (topics + replies) per user per real day. */
    private const DAILY_XP_POST_CAP = 5;

    public function index(Request $request)
    {
        $category = $request->query('category');
        $q        = trim((string) $request->query('q', ''));

        $mySchool     = $this->userSchool($request->user());
        $schoolBoard  = $request->query('board') === 'school' && $mySchool !== null;

        $votesEnabled = \Illuminate\Support\Facades\Schema::hasColumn('forum_topics', 'score');
        $sort = in_array($request->query('sort'), ['hot', 'new', 'top', 'activity'], true) ? $request->query('sort') : 'hot';
        if (!$votesEnabled) $sort = 'new';

        $authorCols = 'user:id,name,profile_photo'
            . ($votesEnabled ? ',forum_karma' : '')
            . (User::usernamesEnabled() ? ',username' : '');

        $query = ForumTopic::visible()
            ->with([$authorCols, 'user.badges', 'user.progress:id,user_id,level,life_chapter'])
            ->orderByDesc('is_pinned');

        // Hot = votes + chatter, decayed by hours since last activity (X-style feed).
        // New = latest. Top = highest score of all time. Activity = most recent
        // reply/upvote, unweighted (see vote()/reply() for what bumps last_activity_at).
        match ($sort) {
            'new'      => $query->orderByDesc('created_at'),
            'top'      => $votesEnabled ? $query->orderByDesc('score')->orderByDesc('replies_count') : $query->orderByDesc('last_activity_at'),
            'activity' => $query->orderByDesc('last_activity_at'),
            default    => $votesEnabled
                ? $query->orderByRaw('(score + replies_count) / POW(GREATEST(TIMESTAMPDIFF(HOUR, COALESCE(last_activity_at, created_at), NOW()), 0) + 2, 1.3) DESC')
                : $query->orderByDesc('last_activity_at'),
        };

        if ($schoolBoard) {
            // Private board: only this school's topics, challenges first
            $query->where('school_subscription_id', $mySchool->id)
                  ->orderByDesc('is_challenge');
        } else {
            // Public forum never shows school-private topics
            $query->whereNull('school_subscription_id');
        }

        // Friends-only topics are hidden from anyone who isn't the author or a friend.
        $viewer = $request->user();
        if ($viewer) {
            $friendIds = $viewer->friendIds();
            $query->where(function ($q) use ($viewer, $friendIds) {
                $q->where('visibility', 'general')
                  ->orWhere('user_id', $viewer->id)
                  ->orWhereIn('user_id', $friendIds);
            });
        } else {
            $query->where('visibility', 'general');
        }

        if ($category && array_key_exists($category, self::CATEGORIES)) {
            $query->where('category', $category);
        } else {
            $category = null;
        }

        if ($q !== '') {
            $query->where('title', 'like', '%' . $q . '%');
        }

        $topics = $query->paginate(15)->withQueryString();

        $myTopicVotes = $votesEnabled
            ? \App\Models\ForumVote::mapFor($request->user()?->id, 'topic', $topics->pluck('id')->all())
            : [];

        $counts = ForumTopic::visible()
            ->whereNull('school_subscription_id')
            ->selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category');

        $schoolTopicCount = $mySchool
            ? ForumTopic::where('school_subscription_id', $mySchool->id)->count()
            : 0;

        return view('forums.index', array_merge([
            'topics'           => $topics,
            'categories'       => self::CATEGORIES,
            'counts'           => $counts,
            'activeCategory'   => $category,
            'search'           => $q,
            'mySchool'         => $mySchool,
            'schoolBoard'      => $schoolBoard,
            'schoolTopicCount' => $schoolTopicCount,
            'sort'             => $sort,
            'votesEnabled'     => $votesEnabled,
            'myTopicVotes'     => $myTopicVotes,
        ], $this->communityStats()));
    }

    /**
     * Header stat strip + trending category chips — cheap aggregate queries,
     * all scoped to the public board (school-private activity stays out of
     * the public "pulse" widgets).
     */
    private function communityStats(): array
    {
        $today = now()->toDateString();

        $onlineNow = \Illuminate\Support\Facades\Schema::hasTable('sessions')
            ? \Illuminate\Support\Facades\DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
                ->distinct('user_id')
                ->count('user_id')
            : 0;

        $discussionsToday = ForumTopic::visible()->whereNull('school_subscription_id')->whereDate('created_at', $today)->count();
        $repliesToday     = ForumReply::whereHas('topic', fn ($q) => $q->whereNull('school_subscription_id'))
            ->whereDate('created_at', $today)->count();

        $topContributor = \Illuminate\Support\Facades\Schema::hasColumn('users', 'forum_karma')
            ? User::where('forum_karma', '>', 0)->orderByDesc('forum_karma')->first(['id', 'name', 'profile_photo', 'forum_karma'])
            : null;

        $trending = ForumTopic::visible()
            ->whereNull('school_subscription_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->orderByDesc('c')
            ->limit(5)
            ->pluck('c', 'category')
            ->keys()
            ->map(fn ($key) => ['key' => $key, 'meta' => self::CATEGORIES[$key] ?? ['icon' => '💬', 'label' => ucfirst($key)]])
            ->values();

        return [
            'onlineNow'        => $onlineNow,
            'discussionsToday' => $discussionsToday,
            'repliesToday'     => $repliesToday,
            'topContributor'   => $topContributor,
            'trending'         => $trending,
        ];
    }

    /**
     * Polled by the forum list's "New discussions" pill (X/Twitter-style) —
     * cheap existence check, scoped the same way the index listing is, so
     * the count the pill shows always matches what "Refresh" would reveal.
     */
    public function checkNew(Request $request)
    {
        $since = $request->query('since');
        if (!$since) {
            return response()->json(['count' => 0]);
        }

        $category = $request->query('category');
        $mySchool = $this->userSchool($request->user());
        $schoolBoard = $request->query('board') === 'school' && $mySchool !== null;

        $query = ForumTopic::visible()->where('created_at', '>', $since);

        if ($schoolBoard) {
            $query->where('school_subscription_id', $mySchool->id);
        } else {
            $query->whereNull('school_subscription_id');
        }

        $viewer = $request->user();
        if ($viewer) {
            $friendIds = $viewer->friendIds();
            $query->where(function ($q) use ($viewer, $friendIds) {
                $q->where('visibility', 'general')->orWhere('user_id', $viewer->id)->orWhereIn('user_id', $friendIds);
            });
        } else {
            $query->where('visibility', 'general');
        }

        if ($category && array_key_exists($category, self::CATEGORIES)) {
            $query->where('category', $category);
        }

        return response()->json(['count' => min($query->count(), 20)]);
    }

    public function show(ForumTopic $topic)
    {
        $this->authorizeSchoolTopic($topic, request()->user());
        $this->authorizeFriendsTopic($topic, request()->user());

        $votesEnabled = \Illuminate\Support\Facades\Schema::hasColumn('forum_topics', 'score');

        $usernameCol = User::usernamesEnabled() ? ',username' : '';
        $replyUserCols = ['user:id,name,profile_photo' . $usernameCol, 'user.progress:id,user_id,level'];

        $topic->increment('views');
        $topic->load(['user:id,name,profile_photo' . ($votesEnabled ? ',forum_karma' : '') . $usernameCol, 'user.badges', 'user.progress:id,user_id,level,life_chapter']);

        // Root replies are paginated as before; every visible root's full subtree
        // is then pulled in level-by-level (bounded by actual thread depth) so a
        // reply-to-a-reply never gets stranded on a different page than its parent.
        $rootReplies = $topic->replies()
            ->whereNull('parent_id')
            ->with($replyUserCols)
            ->oldest()
            ->paginate(20);

        $descendants = collect();
        $frontier    = $rootReplies->pluck('id');
        while ($frontier->isNotEmpty()) {
            $next = ForumReply::whereIn('parent_id', $frontier)->with($replyUserCols)->oldest()->get();
            if ($next->isEmpty()) break;
            $descendants = $descendants->concat($next);
            $frontier    = $next->pluck('id');
        }

        $allLoadedReplies = $rootReplies->getCollection()->concat($descendants);
        $tree = ForumReply::buildTree($allLoadedReplies);

        $userId       = request()->user()?->id;
        $myTopicVotes = $votesEnabled ? \App\Models\ForumVote::mapFor($userId, 'topic', [$topic->id]) : [];
        $myReplyVotes = $votesEnabled ? \App\Models\ForumVote::mapFor($userId, 'reply', $allLoadedReplies->pluck('id')->all()) : [];

        $reactionCounts = \App\Models\ForumReaction::where('topic_id', $topic->id)
            ->selectRaw('type, COUNT(*) as c')
            ->groupBy('type')
            ->pluck('c', 'type');
        $myReactions = $userId
            ? \App\Models\ForumReaction::where('topic_id', $topic->id)->where('user_id', $userId)->pluck('type')->all()
            : [];

        return view('forums.show', [
            'topic'          => $topic,
            'replies'        => $rootReplies,
            'replyTree'      => $tree,
            'categories'     => self::CATEGORIES,
            'votesEnabled'   => $votesEnabled,
            'myTopicVotes'   => $myTopicVotes,
            'myReplyVotes'   => $myReplyVotes,
            'reactionTypes'  => self::REACTIONS,
            'reactionCounts' => $reactionCounts,
            'myReactions'    => $myReactions,
        ]);
    }

    /**
     * X-style voting: ▲/▼ once per post, tap again to remove, tap the other
     * to switch. The votable's `score` and the author's karma move together.
     */
    public function vote(Request $request)
    {
        abort_unless(\Illuminate\Support\Facades\Schema::hasTable('forum_votes'), 503);

        $data = $request->validate([
            'type' => ['required', 'in:topic,reply'],
            'id'   => ['required', 'integer'],
            'dir'  => ['required', 'in:up,down'],
        ]);

        $user    = $request->user();
        $votable = $data['type'] === 'topic'
            ? ForumTopic::findOrFail($data['id'])
            : ForumReply::findOrFail($data['id']);

        $topic = $data['type'] === 'topic' ? $votable : $votable->topic;
        $this->authorizeSchoolTopic($topic, $user);
        $this->authorizeFriendsTopic($topic, $user);

        if ($votable->user_id === $user->id) {
            return response()->json(['error' => "You can't vote on your own post."], 422);
        }

        $value    = $data['dir'] === 'up' ? 1 : -1;
        $existing = \App\Models\ForumVote::where('user_id', $user->id)
            ->where('votable_type', $data['type'])
            ->where('votable_id', $votable->id)
            ->first();

        if ($existing && (int) $existing->value === $value) {
            $existing->delete();                                  // un-vote
            $delta = -$value; $myVote = 0;
        } elseif ($existing) {
            $existing->update(['value' => $value]);               // switch sides
            $delta = 2 * $value; $myVote = $value;
        } else {
            \App\Models\ForumVote::create([
                'user_id'      => $user->id,
                'votable_type' => $data['type'],
                'votable_id'   => $votable->id,
                'value'        => $value,
            ]);
            $delta = $value; $myVote = $value;
        }

        $votable->increment('score', $delta);
        if ($votable->user_id && \Illuminate\Support\Facades\Schema::hasColumn('users', 'forum_karma')) {
            User::where('id', $votable->user_id)->increment('forum_karma', $delta);
        }

        // A fresh upvote (or a downvote→upvote switch) counts as activity for
        // the "Activity" tab — the topic bubbles up whether it was voted on
        // directly or one of its replies was.
        if ($delta > 0 && $topic) {
            $topic->forceFill(['last_activity_at' => now()])->save();
        }

        return response()->json(['score' => (int) $votable->fresh()->score, 'my_vote' => $myVote]);
    }

    /**
     * Lightweight multi-emoji reactions on a topic's opening post (Helpful,
     * Facts, Funny, …) — separate from the ▲/▼ vote used for ranking/karma.
     * A user may hold several reaction types on the same topic at once;
     * tapping an active one again removes it.
     */
    public function react(Request $request, ForumTopic $topic)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::REACTIONS))],
        ]);

        $this->authorizeSchoolTopic($topic, $request->user());
        $this->authorizeFriendsTopic($topic, $request->user());

        $user = $request->user();

        $existing = \App\Models\ForumReaction::where('user_id', $user->id)
            ->where('topic_id', $topic->id)
            ->where('type', $data['type'])
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
        } else {
            \App\Models\ForumReaction::create([
                'user_id'  => $user->id,
                'topic_id' => $topic->id,
                'type'     => $data['type'],
            ]);
            $active = true;
        }

        $counts = \App\Models\ForumReaction::where('topic_id', $topic->id)
            ->selectRaw('type, COUNT(*) as c')
            ->groupBy('type')
            ->pluck('c', 'type');

        return response()->json(['active' => $active, 'counts' => $counts]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'min:5', 'max:150'],
            'body'       => ['required', 'string', 'min:10', 'max:5000'],
            'category'   => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'board'      => ['nullable', 'in:school'],
            'visibility' => ['nullable', 'in:general,friends'],
            'image'      => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
        ]);

        $user = $request->user();

        // School board posts require an active membership in that school
        $schoolId = null;
        if (($data['board'] ?? null) === 'school') {
            $school = $this->userSchool($user);
            if (!$school) {
                return back()->with('error', 'You need an active school membership to post on a school board.');
            }
            $schoolId = $school->id;
        }

        // Plan gate: free players need a minimum level to open new topics (replies stay free).
        // School boards are exempt — the school seat already makes these players premium anyway.
        $gate = app(PlanGate::class);
        if (!$schoolId && !$gate->isPremium($user)) {
            $minLevel = $gate->limit($user, 'forum_topic_min_level');
            if ($minLevel > 0 && (($user->getOrCreateProgress()->level ?? 1) < $minLevel)) {
                return back()->with('error', $gate->deny('forum_topics', $minLevel)['error']);
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = '/uploads/' . $this->resizeAndStore($request->file('image'), 'forums/topics', 800, 500, 80);
        }

        $topic = ForumTopic::create([
            'user_id'                => $user->id,
            'school_subscription_id' => $schoolId,
            'title'                  => $data['title'],
            'slug'                   => Str::slug(Str::limit($data['title'], 100, '')) . '-' . Str::lower(Str::random(4)),
            'body'                   => $data['body'],
            'category'               => $data['category'],
            'image_path'             => $imagePath,
            // School-board posts are already private to that school — the
            // general/friends toggle only makes sense on the public forum.
            'visibility'             => $schoolId ? 'general' : ($data['visibility'] ?? 'general'),
            'last_activity_at'       => now(),
        ]);

        $awarded = $this->awardForumXp($user, self::XP_TOPIC, exclude: ['topic_id' => $topic->id]);

        return redirect()
            ->route('forums.show', $topic->slug)
            ->with('success', $awarded ? 'Posted! +' . self::XP_TOPIC . ' XP' : 'Posted!');
    }

    public function reply(Request $request, ForumTopic $topic)
    {
        $this->authorizeSchoolTopic($topic, $request->user());
        $this->authorizeFriendsTopic($topic, $request->user());

        if ($topic->is_locked) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This discussion is locked.'], 422);
            }
            return back()->with('error', 'This discussion is locked. No new replies allowed.');
        }

        $data = $request->validate([
            'body'      => ['required', 'string', 'min:2', 'max:3000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('forum_replies', 'id')->where('topic_id', $topic->id)],
            'image'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
        ]);

        $user = $request->user();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = '/uploads/' . $this->resizeAndStore($request->file('image'), 'forums/replies', 600, 400, 78);
        }

        $reply = ForumReply::create([
            'topic_id'   => $topic->id,
            'parent_id'  => $data['parent_id'] ?? null,
            'user_id'    => $user->id,
            'body'       => $data['body'],
            'image_path' => $imagePath,
        ]);

        $topic->increment('replies_count');
        $topic->forceFill(['last_activity_at' => now()])->save();

        $awarded = $this->awardForumXp($user, self::XP_REPLY, exclude: ['reply_id' => $reply->id]);

        $parent = $reply->parent_id ? $reply->parent()->first() : null;

        if ($parent && $parent->user_id !== $user->id) {
            GameNotification::create([
                'user_id' => $parent->user_id,
                'type'    => 'forum_reply',
                'title'   => '💬 New reply to your comment',
                'body'    => $user->name . ' replied to your comment on "' . Str::limit($topic->title, 60) . '"',
                'icon'    => '💬',
                'data'    => ['topic_id' => $topic->id, 'slug' => $topic->slug, 'reply_id' => $reply->id],
            ]);
        } elseif (!$parent && $topic->user_id !== $user->id) {
            GameNotification::create([
                'user_id' => $topic->user_id,
                'type'    => 'forum_reply',
                'title'   => '💬 New reply on your discussion',
                'body'    => $user->name . ' replied to "' . Str::limit($topic->title, 60) . '"',
                'icon'    => '💬',
                'data'    => ['topic_id' => $topic->id, 'slug' => $topic->slug, 'reply_id' => $reply->id],
            ]);
        }

        // Root replies are what's paginated on the show page — find which page
        // this reply's top-level ancestor lands on so the redirect anchor works.
        $rootAncestor = $reply;
        while ($rootAncestor->parent_id) {
            $rootAncestor = $rootAncestor->parent()->first();
        }
        $rootPosition = $topic->replies()->whereNull('parent_id')->where('created_at', '<', $rootAncestor->created_at)->count();
        $lastPage = intdiv($rootPosition, 20) + 1;

        return redirect()
            ->to(route('forums.show', $topic->slug) . ($lastPage > 1 ? '?page=' . $lastPage : '') . '#reply-' . $reply->id)
            ->with('success', $awarded ? 'Reply posted! +' . self::XP_REPLY . ' XP' : 'Reply posted!');
    }

    public function update(Request $request, ForumTopic $topic)
    {
        $user = $request->user();
        if ($topic->user_id !== $user->id && !$this->canModerate($user)) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'body'  => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $topic->update($data);

        return redirect()->route('forums.show', $topic->slug)->with('success', 'Discussion updated.');
    }

    public function destroy(Request $request, ForumTopic $topic)
    {
        $user = $request->user();
        if ($topic->user_id !== $user->id && !$this->canModerate($user)) {
            abort(403);
        }

        $this->deleteStoredImage($topic->image_path);
        foreach ($topic->replies as $reply) {
            $this->deleteStoredImage($reply->image_path);
        }

        $topic->delete();

        return redirect()->route('forums.index')->with('success', 'Discussion deleted.');
    }

    public function destroyReply(Request $request, ForumReply $reply)
    {
        $user = $request->user();
        if ($reply->user_id !== $user->id && !$this->canModerate($user)) {
            abort(403);
        }

        $topic = $reply->topic;

        // parent_id cascades in the DB, so deleting a reply with children wipes
        // its whole subtree — count it (and clean up their images) up front so
        // replies_count stays accurate and nothing gets orphaned on disk.
        $this->deleteStoredImage($reply->image_path);
        $deletedCount = 1;
        $frontier = collect([$reply->id]);
        while ($frontier->isNotEmpty()) {
            $children = ForumReply::whereIn('parent_id', $frontier)->get(['id', 'image_path']);
            if ($children->isEmpty()) break;
            foreach ($children as $child) {
                $this->deleteStoredImage($child->image_path);
            }
            $deletedCount += $children->count();
            $frontier = $children->pluck('id');
        }

        $reply->delete();

        if ($topic) {
            $topic->replies_count = max(0, $topic->replies_count - $deletedCount);
            $topic->save();
        }

        return back()->with('success', 'Reply deleted.');
    }

    public function togglePin(Request $request, ForumTopic $topic)
    {
        if (!$this->canModerate($request->user())) {
            abort(403);
        }

        $topic->update(['is_pinned' => !$topic->is_pinned]);

        return back()->with('success', $topic->is_pinned ? 'Discussion pinned.' : 'Discussion unpinned.');
    }

    public function toggleLock(Request $request, ForumTopic $topic)
    {
        if (!$this->canModerate($request->user())) {
            abort(403);
        }

        $topic->update(['is_locked' => !$topic->is_locked]);

        return back()->with('success', $topic->is_locked ? 'Discussion locked.' : 'Discussion unlocked.');
    }

    /**
     * Anti-farm XP: only the first DAILY_XP_POST_CAP forum posts
     * (topics + replies combined) per real day earn XP.
     *
     * $exclude lets us not count the post we just created.
     * Returns true if XP was awarded.
     */
    private function awardForumXp(User $user, int $xp, array $exclude = []): bool
    {
        $topicQuery = ForumTopic::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString());
        if (!empty($exclude['topic_id'])) {
            $topicQuery->where('id', '!=', $exclude['topic_id']);
        }

        $replyQuery = ForumReply::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString());
        if (!empty($exclude['reply_id'])) {
            $replyQuery->where('id', '!=', $exclude['reply_id']);
        }

        $postsToday = $topicQuery->count() + $replyQuery->count();

        if ($postsToday >= self::DAILY_XP_POST_CAP) {
            return false;
        }

        $user->getOrCreateProgress()->addPoints($xp);

        return true;
    }

    private function canModerate(User $user): bool
    {
        return (bool) ($user->is_admin || $user->is_gameset);
    }

    /** The user's active school (if any) — used for private school boards. */
    private function userSchool(?User $user): ?\App\Models\SchoolSubscription
    {
        if (!$user) return null;

        $member = \App\Models\SchoolMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('schoolSubscription', fn($q) => $q->where('status', 'active')->where('ends_at', '>', now()))
            ->with('schoolSubscription')
            ->first();

        return $member?->schoolSubscription;
    }

    /** School-private topics are only readable by that school's members (and moderators). */
    private function authorizeSchoolTopic(ForumTopic $topic, ?User $user): void
    {
        if (!$topic->isSchoolBoard()) return;
        if ($user && $this->canModerate($user)) return;

        $school = $this->userSchool($user);
        if (!$school || $school->id !== $topic->school_subscription_id) {
            abort(403, 'This discussion is private to its school.');
        }
    }

    /** Friends-only topics are only readable by the author, their friends, and moderators. */
    private function authorizeFriendsTopic(ForumTopic $topic, ?User $user): void
    {
        if (!$topic->isFriendsOnly()) return;
        if ($user && $this->canModerate($user)) return;
        if ($user && $user->id === $topic->user_id) return;

        if (!$user || !$user->isFriendsWith($topic->user)) {
            abort(403, 'This discussion is private to friends of the poster.');
        }
    }
}
