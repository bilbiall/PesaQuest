<?php

namespace App\Http\Controllers;

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
    public const CATEGORIES = [
        'general'      => ['icon' => '💬', 'label' => 'General Banter'],
        'money-talk'   => ['icon' => '💰', 'label' => 'Money Talk'],
        'side-hustles' => ['icon' => '🚀', 'label' => 'Side Hustles'],
        'saving-tips'  => ['icon' => '🏦', 'label' => 'Saving & Chamas'],
        'school'       => ['icon' => '🎓', 'label' => 'School Corner'],
        'help'         => ['icon' => '🆘', 'label' => 'Help & Questions'],
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
        $sort = in_array($request->query('sort'), ['hot', 'new', 'top'], true) ? $request->query('sort') : 'hot';
        if (!$votesEnabled) $sort = 'new';

        $authorCols = 'user:id,name,profile_photo'
            . ($votesEnabled ? ',forum_karma' : '')
            . (User::usernamesEnabled() ? ',username' : '');

        $query = ForumTopic::visible()
            ->with([$authorCols, 'user.badges', 'user.progress:id,user_id,level,life_chapter'])
            ->orderByDesc('is_pinned');

        // Hot = votes + chatter, decayed by hours since last activity (X-style feed).
        // New = latest. Top = highest score of all time.
        match ($sort) {
            'new' => $query->orderByDesc('created_at'),
            'top' => $votesEnabled ? $query->orderByDesc('score')->orderByDesc('replies_count') : $query->orderByDesc('last_activity_at'),
            default => $votesEnabled
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

        return view('forums.index', [
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
        ]);
    }

    public function show(ForumTopic $topic)
    {
        $this->authorizeSchoolTopic($topic, request()->user());

        $votesEnabled = \Illuminate\Support\Facades\Schema::hasColumn('forum_topics', 'score');

        $usernameCol = User::usernamesEnabled() ? ',username' : '';

        $topic->increment('views');
        $topic->load(['user:id,name,profile_photo' . ($votesEnabled ? ',forum_karma' : '') . $usernameCol, 'user.badges', 'user.progress:id,user_id,level,life_chapter']);

        $replies = $topic->replies()
            ->with(['user:id,name,profile_photo' . $usernameCol, 'user.progress:id,user_id,level'])
            ->oldest()
            ->paginate(20);

        $userId       = request()->user()?->id;
        $myTopicVotes = $votesEnabled ? \App\Models\ForumVote::mapFor($userId, 'topic', [$topic->id]) : [];
        $myReplyVotes = $votesEnabled ? \App\Models\ForumVote::mapFor($userId, 'reply', $replies->pluck('id')->all()) : [];

        return view('forums.show', [
            'topic'        => $topic,
            'replies'      => $replies,
            'categories'   => self::CATEGORIES,
            'votesEnabled' => $votesEnabled,
            'myTopicVotes' => $myTopicVotes,
            'myReplyVotes' => $myReplyVotes,
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

        $this->authorizeSchoolTopic($data['type'] === 'topic' ? $votable : $votable->topic, $user);

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

        return response()->json(['score' => (int) $votable->fresh()->score, 'my_vote' => $myVote]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => ['required', 'string', 'min:5', 'max:150'],
            'body'     => ['required', 'string', 'min:10', 'max:5000'],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'board'    => ['nullable', 'in:school'],
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

        $topic = ForumTopic::create([
            'user_id'                => $user->id,
            'school_subscription_id' => $schoolId,
            'title'                  => $data['title'],
            'slug'                   => Str::slug(Str::limit($data['title'], 100, '')) . '-' . Str::lower(Str::random(4)),
            'body'                   => $data['body'],
            'category'               => $data['category'],
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

        if ($topic->is_locked) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This discussion is locked.'], 422);
            }
            return back()->with('error', 'This discussion is locked. No new replies allowed.');
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:3000'],
        ]);

        $user = $request->user();

        $reply = ForumReply::create([
            'topic_id' => $topic->id,
            'user_id'  => $user->id,
            'body'     => $data['body'],
        ]);

        $topic->increment('replies_count');
        $topic->forceFill(['last_activity_at' => now()])->save();

        $awarded = $this->awardForumXp($user, self::XP_REPLY, exclude: ['reply_id' => $reply->id]);

        if ($topic->user_id !== $user->id) {
            GameNotification::create([
                'user_id' => $topic->user_id,
                'type'    => 'forum_reply',
                'title'   => '💬 New reply on your discussion',
                'body'    => $user->name . ' replied to "' . Str::limit($topic->title, 60) . '"',
                'icon'    => '💬',
                'data'    => ['topic_id' => $topic->id, 'slug' => $topic->slug, 'reply_id' => $reply->id],
            ]);
        }

        $lastPage = (int) ceil($topic->replies()->count() / 20);

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
        $reply->delete();

        if ($topic) {
            $topic->replies_count = max(0, $topic->replies_count - 1);
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
}
