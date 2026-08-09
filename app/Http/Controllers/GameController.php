<?php

namespace App\Http\Controllers;

use App\Models\DailyChallenge;
use App\Models\GameNotification;
use App\Models\Investment;
use App\Models\MarketEvent;
use App\Models\Quest;
use App\Models\ScenarioRating;
use App\Models\UserDailyChallenge;
use App\Models\UserQuest;
use App\Services\CareerService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        if (!$progress->current_node_id) {
            $startNode = \App\Models\Node::forAgeGroup($user->age_group ?? '18-25')
                ->startNodes()
                ->first();
            if ($startNode) {
                $progress->current_node_id          = $startNode->id;
                $progress->current_scenario_start_id = $startNode->id;
                $progress->save();
            }
        }

        $node = $progress->currentNode;

        if ($node && $node->is_start) {
            $progress->current_scenario_start_id = $node->id;
            if (isset($node->metadata['starting_balance']) && $progress->balance == 0) {
                $progress->balance = $node->metadata['starting_balance'];
            }
            $progress->save();
        }

        // Legacy scenario-driven "career income" payslip retired — real salary
        // now comes exclusively from actually being hired in Pesa City
        // (OpportunityController::apply + LifeSimulator::settleJobSalaries).
        // Kept as null (not removed from the view) so the payslip modal simply
        // never renders rather than requiring a template rewrite.
        $payslip = null;

        if (!$node) {
            return view('game.no-content');
        }

        if (!$user->canAccessNode($node)) {
            $plans = \App\Models\SubscriptionPlan::active()->get();
            return view('game.paywall', compact('node', 'progress', 'plans'));
        }

        $choices = $node->choices;
        $streak  = $user->streak ?? new \App\Models\UserStreak(['current_streak' => 0]);

        // Daily challenges for sidebar
        $ageGroup  = $user->age_group ?? '18-25';
        $today     = now()->toDateString();
        $challenges = DailyChallenge::activeToday($ageGroup)->get()->map(function ($c) use ($user, $today) {
            $udc = UserDailyChallenge::firstOrCreate(
                ['user_id' => $user->id, 'challenge_id' => $c->id, 'date' => $today],
                ['progress' => 0]
            );
            $c->user_progress  = $udc->progress;
            $c->user_completed = $udc->isCompleted();
            $c->user_claimed   = $udc->isClaimed();
            $c->udc_id         = $udc->id;
            return $c;
        });

        // Pending investment returns — create notification on first mature
        $pendingInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('mature_at', '<=', now())
            ->get();

        foreach ($pendingInvestments as $inv) {
            $alreadyNotified = GameNotification::where('user_id', $user->id)
                ->where('type', 'investment_ready')
                ->whereJsonContains('data->investment_id', $inv->id)
                ->exists();

            if (!$alreadyNotified) {
                $expectedReturn = round((float) $inv->amount * (1 + (float) $inv->return_rate / 100), 2);
                GameNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'investment_ready',
                    'title'   => '💰 Investment Matured!',
                    'body'    => "Your {$inv->label} investment is ready! Claim Ksh " . number_format($expectedReturn) . " now.",
                    'icon'    => '💰',
                    'data'    => ['investment_id' => $inv->id, 'returns' => $expectedReturn],
                ]);
            }
        }

        // Market event (roll once per page load, only ~20% chance total)
        $marketEvent = null;
        if (rand(1, 5) === 1) {
            $marketEvent = MarketEvent::rollForUser($user, $ageGroup);
            if ($marketEvent) {
                if ($marketEvent->effect_type === 'bonus') {
                    $progress->balance += abs($marketEvent->effect_amount);
                } elseif ($marketEvent->effect_type === 'penalty') {
                    $progress->balance = max(0, $progress->balance - abs($marketEvent->effect_amount));
                }
                $progress->save();
            }
        }

        // Active quests for user
        $activeQuests = Quest::where('is_active', true)
            ->where('age_group', $ageGroup)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($q) use ($user) {
                $uq = $q->userQuestFor($user->id);
                $q->user_status = $uq ? ($uq->isApproved() ? 'approved' : 'pending') : 'available';
                return $q;
            });

        // Scenario rating for current node (if ending/result)
        $myRating = null;
        if (in_array($node->type, ['result', 'ending'])) {
            $existing  = ScenarioRating::where('node_id', $node->id)->where('user_id', $user->id)->first();
            $myRating  = $existing?->rating;
        }
        $ratingsSummary = in_array($node->type, ['result', 'ending'])
            ? ScenarioRating::summaryFor($node->id)
            : null;

        // Mentor character for age group
        $mentor = $this->getMentor($ageGroup);

        $story = $node->story_id ? $node->story : null;

        return view('game.play', compact(
            'node', 'choices', 'progress', 'streak',
            'challenges', 'pendingInvestments', 'marketEvent',
            'activeQuests', 'myRating', 'ratingsSummary', 'mentor', 'story',
            'payslip'
        ));
    }

    public function choose(Request $request)
    {
        $validated = $request->validate(['choice_id' => 'required|exists:choices,id']);

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $choice   = \App\Models\Choice::with('nextNode')->findOrFail($validated['choice_id']);

        abort_if($choice->node_id !== $progress->current_node_id, 403);

        $currentNode = \App\Models\Node::find($progress->current_node_id);
        $history = $progress->path_history ?? [];
        $history[] = [
            'node_id'        => $progress->current_node_id,
            'node_title'     => $currentNode?->title ?? '',
            'choice_id'      => $choice->id,
            'choice_label'   => $choice->label,
            'points'         => $choice->points,
            'balance_effect' => (int) ($choice->effect_data['balance_change'] ?? 0),
            'at'             => now()->toDateTimeString(),
        ];

        $progress->path_history    = $history;
        $progress->total_decisions = ($progress->total_decisions ?? 0) + 1;

        // Track save choices (positive balance_change or save-type choices)
        $balanceChange = (int) ($choice->effect_data['balance_change'] ?? 0);
        if ($balanceChange > 0 || str_contains(strtolower($choice->label), 'save') || str_contains(strtolower($choice->label), 'invest')) {
            $progress->consecutive_save_choices = ($progress->consecutive_save_choices ?? 0) + 1;
        } else {
            $progress->consecutive_save_choices = 0;
        }

        $progress->addPoints($choice->points);

        if (isset($choice->effect_data['balance_change'])) {
            $progress->balance += $balanceChange;
        }

        // Handle investment
        $investmentInfo = null;
        $effectData = $choice->effect_data ?? [];
        if (isset($effectData['type']) && $effectData['type'] === 'investment') {
            $investData     = $effectData['investment'] ?? [];
            $investedAmount = abs((int) ($effectData['balance_change'] ?? 0));

            if ($investedAmount > 0 && $investedAmount <= $progress->balance && !empty($investData)) {
                Investment::create([
                    'user_id'     => $user->id,
                    'choice_id'   => $choice->id,
                    'amount'      => $investedAmount,
                    'return_rate' => $investData['return_rate'],
                    'return_days' => $investData['return_days'],
                    // return_days are GAME days — convert to real time via the game clock
                    'mature_at'   => now()->addSeconds(app(\App\Services\GameClock::class)->realSecondsForTicks((int) $investData['return_days'])),
                    'label'       => $investData['label'] ?? $choice->label,
                    'status'      => 'pending',
                ]);

                $investmentInfo = [
                    'label'       => $investData['label'] ?? $choice->label,
                    'return_days' => $investData['return_days'],
                    'return_rate' => $investData['return_rate'],
                    'amount'      => $investedAmount,
                ];
            }
        }

        if ($choice->nextNode) {
            $progress->current_node_id = $choice->next_node_id;
        }
        $progress->save();

        // Streak
        $streak = $user->streak ?? \App\Models\UserStreak::create(['user_id' => $user->id]);
        $streak->recordActivity();

        // Badges
        $this->checkAndAwardBadges($user, $progress);

        // Career unlock: if next node has career_field_unlocked in metadata
        if ($choice->nextNode) {
            $meta = $choice->nextNode->metadata ?? [];
            if (!empty($meta['career_field_unlocked']) && !$progress->career_field) {
                $this->unlockCareer($user, $progress, $meta);
            }
        }

        // No 'income' key — this is a career FIELD/interest unlock, not a real
        // job. Real salary only ever comes from Pesa City employment.
        $careerUnlocked = null;
        if (!empty($choice->nextNode->metadata['career_field_unlocked'])) {
            $cmeta = app(CareerService::class)->fieldMeta($choice->nextNode->metadata['career_field_unlocked']);
            $careerUnlocked = [
                'field' => $choice->nextNode->metadata['career_field_unlocked'],
                'title' => $choice->nextNode->metadata['career_title'] ?? $cmeta['label'],
                'icon'  => $cmeta['icon'],
            ];
        }

        // Daily challenge progress
        $this->updateDailyChallenges($user, $progress, $choice);

        $decisions = $progress->total_decisions ?? 0;
        $lastAssessment = $progress->last_assessment_at_decision ?? 0;
        $showAssessment = $decisions > 0
            && $decisions % 10 === 0
            && $decisions > $lastAssessment;

        if ($request->wantsJson()) {
            return response()->json([
                'points'         => $choice->points,
                'nextNode'       => $choice->nextNode ? [
                    'id'   => $choice->nextNode->id,
                    'type' => $choice->nextNode->type,
                ] : null,
                'lesson'         => $choice->effect_data['lesson'] ?? null,
                'newBalance'     => $progress->balance,
                'totalPoints'    => $progress->points_total,
                'investment'     => $investmentInfo,
                'careerUnlocked' => $careerUnlocked,
                'showAssessment' => $showAssessment,
                'totalDecisions' => $decisions,
            ]);
        }

        return redirect()->route('game.play');
    }

    public function result()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $node     = $progress->currentNode;

        return view('game.result', compact('node', 'progress'));
    }

    public function restart()
    {
        $user      = auth()->user();
        $progress  = $user->getOrCreateProgress();
        $startNode = \App\Models\Node::forAgeGroup($user->age_group ?? '18-25')
            ->startNodes()->first();

        $newBalance = $startNode && isset($startNode->metadata['starting_balance'])
            ? $startNode->metadata['starting_balance']
            : 0;

        $progress->update([
            'current_node_id'        => $startNode?->id,
            'path_history'           => [],
            'balance'                => $newBalance,
            'consecutive_save_choices' => 0,
        ]);

        return redirect()->route('game.play');
    }

    // ── Investment Claim ───────────────────────────────────────────────────

    public function claimInvestment(Request $request, Investment $investment)
    {
        $user = auth()->user();
        abort_if($investment->user_id !== $user->id, 403);
        abort_if($investment->status !== 'pending', 422);
        abort_if($investment->mature_at > now(), 422);

        $progress = $user->getOrCreateProgress();
        $returnAmount = round((float) $investment->amount * (1 + (float) $investment->return_rate / 100), 2);

        $investment->update([
            'return_amount' => $returnAmount,
            'status'        => 'credited',
            'credited_at'   => now(),
        ]);

        $progress->balance += $returnAmount;
        $progress->save();

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'investment',
            'title'   => '💰 Returns Claimed!',
            'body'    => "You claimed Ksh " . number_format($returnAmount) . " from your {$investment->label} investment!",
            'icon'    => '💰',
            'data'    => ['returns' => $returnAmount, 'investment_id' => $investment->id],
        ]);

        return response()->json([
            'returns'    => $returnAmount,
            'newBalance' => $progress->balance,
            'message'    => "Ksh " . number_format($returnAmount) . " credited to your balance!",
        ]);
    }

    // ── Leaderboard ────────────────────────────────────────────────────────

    public function leaderboard(Request $request)
    {
        $ageGroup = $request->input('age_group', auth()->user()->age_group ?? '18-25');
        $sort     = $request->input('sort', 'xp'); // 'xp' or 'networth'

        // "My School" scope — same ranking, filtered to the player's own active
        // school roster instead of the global age-group pool.
        $mySchoolMember = \App\Models\SchoolMember::where('user_id', auth()->id())
            ->where('status', 'active')
            ->whereHas('schoolSubscription', fn ($q) => $q->where('status', 'active')->where('ends_at', '>', now()))
            ->with('schoolSubscription')
            ->first();
        $scope = $request->input('scope', 'global') === 'school' && $mySchoolMember ? 'school' : 'global';

        $rosterIds = $scope === 'school'
            ? \App\Models\SchoolMember::where('school_subscription_id', $mySchoolMember->school_subscription_id)->where('status', 'active')->pluck('user_id')
            : collect();

        $scopeFilter = function ($query) use ($scope, $rosterIds, $ageGroup) {
            return $scope === 'school'
                ? $query->whereIn('user_id', $rosterIds)
                : $query->whereHas('user', fn ($q) => $q->where('age_group', $ageGroup));
        };

        $baseQuery = $scopeFilter(\App\Models\UserProgress::with('user'));

        // Rank-change arrows need yesterday's (or the last available) snapshot
        // to compare against — see SnapshotLeaderboard, which populates this
        // table nightly. First day ever run, this is simply empty (all "—").
        $scopeKey = $scope === 'school'
            ? "school:{$sort}:{$mySchoolMember->schoolSubscription->id}"
            : "global:{$sort}:{$ageGroup}";
        $prevDate = \App\Models\LeaderboardSnapshot::where('scope_key', $scopeKey)
            ->where('snapshot_date', '<', now()->toDateString())
            ->max('snapshot_date');
        $prevRanks = $prevDate
            ? \App\Models\LeaderboardSnapshot::where('scope_key', $scopeKey)->where('snapshot_date', $prevDate)->pluck('rank', 'user_id')
            : collect();
        $rankChange = fn (int $userId, int $currentRank) => $prevRanks->has($userId) ? $prevRanks[$userId] - $currentRank : null;

        if ($sort === 'networth') {
            $leaders = $baseQuery
                ->selectRaw('user_progress.*, COALESCE(net_worth_cache, balance) as sort_value')
                ->orderByDesc('sort_value')
                ->limit(10)
                ->get()
                ->values()
                ->map(fn($p, $i) => [
                    'rank'          => $i + 1,
                    'user_id'       => $p->user_id,
                    'name'          => $p->user->name,
                    'profile_photo' => $p->user->profile_photo,
                    'bio'           => $p->user->bio,
                    'points'        => $p->net_worth_cache ?? $p->balance,
                    'level'         => $p->level,
                    'is_me'         => $p->user_id === auth()->id(),
                    'played_label'  => $this->gamePlayedLabel($p->tick_count ?? 0),
                    'sort_type'     => 'networth',
                    'rank_change'   => $rankChange($p->user_id, $i + 1),
                ]);

            $myNetWorth = \App\Models\UserProgress::where('user_id', auth()->id())
                ->selectRaw('COALESCE(net_worth_cache, balance) as sort_value')
                ->value('sort_value') ?? 0;
            $myRank = $scopeFilter(\App\Models\UserProgress::query())
                ->whereRaw('COALESCE(net_worth_cache, balance) > ?', [$myNetWorth])
                ->count() + 1;
        } else {
            $leaders = $baseQuery
                ->orderByDesc('points_total')
                ->limit(10)
                ->get()
                ->values()
                ->map(fn($p, $i) => [
                    'rank'          => $i + 1,
                    'user_id'       => $p->user_id,
                    'name'          => $p->user->name,
                    'profile_photo' => $p->user->profile_photo,
                    'bio'           => $p->user->bio,
                    'points'        => $p->points_total,
                    'level'         => $p->level,
                    'is_me'         => $p->user_id === auth()->id(),
                    'played_label'  => $this->gamePlayedLabel($p->tick_count ?? 0),
                    'sort_type'     => 'xp',
                    'rank_change'   => $rankChange($p->user_id, $i + 1),
                ]);

            $myPoints = \App\Models\UserProgress::where('user_id', auth()->id())->value('points_total') ?? 0;
            $myRank   = $scopeFilter(\App\Models\UserProgress::query())
                ->where('points_total', '>', $myPoints)
                ->count() + 1;
        }

        $mySchoolName = $mySchoolMember?->schoolSubscription?->school_name;

        // Attach each leader's most recently earned badge (one query for the
        // whole page, not per-row) so the row can show a small badge chip
        // beside their name without an N+1.
        $badgeByUser = \Illuminate\Support\Facades\DB::table('user_badges')
            ->join('badges', 'badges.id', '=', 'user_badges.badge_id')
            ->whereIn('user_badges.user_id', $leaders->pluck('user_id'))
            ->orderByDesc('user_badges.earned_at')
            ->get(['user_badges.user_id', 'badges.icon', 'badges.name'])
            ->groupBy('user_id')
            ->map(fn($rows) => $rows->first());
        $leaders = $leaders->map(function ($leader) use ($badgeByUser) {
            $badge = $badgeByUser->get($leader['user_id']);
            $leader['top_badge'] = $badge ? ['icon' => $badge->icon, 'name' => $badge->name] : null;
            return $leader;
        });

        // Tiny real sparkline for the "Your Goal" card — my own last few days of
        // snapshotted points in this exact scope, oldest first.
        $mySparkline = \App\Models\LeaderboardSnapshot::where('scope_key', $scopeKey)
            ->where('user_id', auth()->id())
            ->orderByDesc('snapshot_date')
            ->limit(4)
            ->pluck('points')
            ->reverse()
            ->values();

        return view('game.leaderboard', compact(
            'leaders', 'ageGroup', 'myRank', 'sort', 'scope', 'mySchoolName', 'mySparkline'
        ));
    }

    /** Lazy-loaded dropdown content for a leaderboard row — badges earned and
     *  dreams achieved. Kept out of the main leaderboard payload so the page
     *  stays light; fetched only when a player expands a row. */
    public function leaderboardPlayerDetails(\App\Models\User $user)
    {
        $badges = $user->badges()
            ->orderByDesc('user_badges.earned_at')
            ->get(['badges.icon', 'badges.name']);

        $dreams = \App\Models\PlayerDream::where('user_id', $user->id)
            ->with('dream:id,icon,name')
            ->orderByDesc('purchased_at')
            ->get()
            ->pluck('dream')
            ->filter()
            ->values();

        return response()->json([
            'badges' => $badges->map(fn($b) => ['icon' => $b->icon, 'name' => $b->name]),
            'dreams' => $dreams->map(fn($d) => ['icon' => $d->icon, 'name' => $d->name]),
        ]);
    }

    /** "Played for" duration in the game's OWN simulated calendar (1 tick = 1
     *  game day, 365 ticks = 1 game year — same constant GameCalendarService
     *  uses), not real-world signup age — the leaderboard is comparing how much
     *  of the game world someone has actually lived through, not how long ago
     *  they made an account. */
    private function gamePlayedLabel(int $tickCount): string
    {
        $years  = intdiv($tickCount, 365);
        $months = intdiv($tickCount % 365, 30);

        if ($years > 0) return $years . ' game yr' . ($years === 1 ? '' : 's');
        if ($months > 0) return $months . ' game mo' . ($months === 1 ? '' : 's');
        return 'New player';
    }

    // ── Financial Personality Assessment ──────────────────────────────────

    public function savePersonality(Request $request)
    {
        $data = $request->validate(['personality' => 'required|string|max:60']);

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $progress->financial_personality        = $data['personality'];
        $progress->last_assessment_at_decision  = $progress->total_decisions ?? 0;
        $progress->save();

        \App\Models\GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'success',
            'title'   => '🧠 Personality Unlocked!',
            'body'    => "You are a \"{$data['personality']}\". This shapes how you approach money.",
            'icon'    => '🧠',
            'data'    => ['personality' => $data['personality']],
        ]);

        return response()->json(['personality' => $data['personality']]);
    }

    // ── Scenario Rating ────────────────────────────────────────────────────

    public function rateScenario(Request $request)
    {
        $data = $request->validate([
            'node_id' => 'required|exists:nodes,id',
            'rating'  => 'required|in:1,-1',
        ]);

        $user = auth()->user();

        ScenarioRating::updateOrCreate(
            ['node_id' => $data['node_id'], 'user_id' => $user->id],
            ['rating'  => (int) $data['rating']]
        );

        $summary = ScenarioRating::summaryFor((int) $data['node_id']);
        return response()->json(['success' => true, 'summary' => $summary]);
    }

    // ── Replay Scenario ────────────────────────────────────────────────────

    public function replayScenario(Request $request)
    {
        $data = $request->validate(['node_id' => 'required|exists:nodes,id']);

        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $node     = \App\Models\Node::findOrFail($data['node_id']);

        abort_if(!$node->is_start, 422);

        $newBalance = isset($node->metadata['starting_balance'])
            ? $node->metadata['starting_balance']
            : $progress->balance;

        $progress->update([
            'current_node_id'          => $node->id,
            'balance'                  => $newBalance,
            'consecutive_save_choices' => 0,
        ]);

        return response()->json(['success' => true, 'redirect' => route('game.play')]);
    }

    // ── Financial Diary ────────────────────────────────────────────────────

    public function diary()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $history  = $progress->path_history ?? [];

        $narrative = collect($history)->map(function ($step, $i) {
            return [
                'step'          => $i + 1,
                'node_title'    => $step['node_title'],
                'choice_label'  => $step['choice_label'],
                'points'        => $step['points'],
                'balance_effect'=> $step['balance_effect'],
                'at'            => $step['at'],
            ];
        });

        if ($progress->balance > 10000) {
            $verdict = 'Outstanding! You are managing money like a pro. 🏆';
        } elseif ($progress->balance > 3000) {
            $verdict = 'Great work! Your financial decisions are paying off. 🌟';
        } elseif ($progress->balance > 500) {
            $verdict = 'You are on the right track. Keep making smart choices! 💪';
        } else {
            $verdict = 'Every journey starts with a single step. Keep going! 🌱';
        }

        return response()->json([
            'narrative' => $narrative,
            'balance'   => $progress->balance,
            'points'    => $progress->points_total,
            'verdict'   => $verdict,
        ]);
    }

    // ── Quests ─────────────────────────────────────────────────────────────

    public function questBoard()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $ageGroup = $user->age_group ?? '18-25';

        $quests = Quest::where('is_active', true)
            ->where(fn($q) => $q->where('age_group', $ageGroup)->orWhereNull('age_group'))
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('quests', 'career_fields'),
                fn ($q) => $q->forCareerField($progress->career_field ?? null))
            ->orderBy('sort_order')
            ->get()
            ->map(function ($q) use ($user) {
                $uq = $q->userQuestFor($user->id);
                $q->user_status = $uq
                    ? ($uq->isApproved() ? 'approved' : 'pending')
                    : 'available';
                $q->user_quest  = $uq;
                return $q;
            });

        $completedCount = $quests->where('user_status', 'approved')->count();
        $totalPoints    = $quests->where('user_status', 'approved')->sum('xp_reward');

        return view('game.quests', compact('quests', 'progress', 'ageGroup', 'completedCount', 'totalPoints'));
    }

    public function submitQuest(Quest $quest)
    {
        $user = auth()->user();

        abort_if(!$quest->is_active, 422);
        abort_if(
            !empty($quest->age_group) && $quest->age_group !== $user->age_group,
            403,
            'This quest is not available for your age group.'
        );
        abort_if(
            !$quest->matchesCareerField($user->progress?->career_field),
            403,
            'This quest is for a different career path.'
        );

        // Admin-tunable daily quest cap — global pace value, with an optional
        // tighter free-tier override (PlanGate::maxQuestsPerDay). 0 = unlimited.
        $alreadyStarted = UserQuest::where('user_id', $user->id)->where('quest_id', $quest->id)->exists();
        $dailyCap       = app(\App\Services\PlanGate::class)->maxQuestsPerDay($user);
        if (!$alreadyStarted && $dailyCap > 0) {
            $startedToday = UserQuest::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();
            if ($startedToday >= $dailyCap) {
                return response()->json(['error' => "Daily quest limit reached ({$dailyCap}/day). Come back tomorrow!"], 429);
            }
        }

        UserQuest::firstOrCreate(
            ['user_id' => $user->id, 'quest_id' => $quest->id],
            ['submitted_at' => now()]
        );

        return response()->json(['success' => true, 'message' => 'Quest submitted! Awaiting admin approval.']);
    }

    // ── Daily Bonus ────────────────────────────────────────────────────────

    public function claimDailyBonus()
    {
        $user = auth()->user();

        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
            $progress = \App\Models\UserProgress::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$progress) {
                return ['error' => 'No progress found.', 'status' => 422];
            }
            if ($progress->last_bonus_at && $progress->last_bonus_at->copy()->tz('Africa/Nairobi')->isSameDay(now('Africa/Nairobi'))) {
                return ['error' => 'Already claimed today. Come back tomorrow!', 'status' => 422];
            }

            $bonusXp                  = (int) \App\Models\Setting::get('daily_bonus_amount', 200);
            $progress->points_total   = ($progress->points_total ?? 0) + $bonusXp;
            $progress->level          = $progress->calculateLevel();
            $progress->last_bonus_at  = now();
            $progress->save();

            return ['bonus' => $bonusXp, 'newBalance' => $progress->balance, 'status' => 200];
        });

        if ($result['status'] !== 200) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'success',
            'title'   => '🎁 Daily Bonus Claimed!',
            'body'    => "+{$result['bonus']} XP added! Keep logging in daily to build your streak.",
            'icon'    => '🎁',
            'data'    => ['bonus_xp' => $result['bonus']],
        ]);

        return response()->json([
            'bonus'      => $result['bonus'],
            'newBalance' => $result['newBalance'],
            'message'    => "+{$result['bonus']} XP added to your total!",
        ]);
    }

    // ── Notifications ──────────────────────────────────────────────────────

    public function notifications()
    {
        $user    = auth()->user();
        $cutoff  = now()->subDays(10);

        // Purge expired notifications silently
        GameNotification::where('user_id', $user->id)
            ->where('created_at', '<', $cutoff)
            ->delete();

        $notifications = GameNotification::where('user_id', $user->id)
            ->where('created_at', '>=', $cutoff)
            ->latest()
            ->take(20)
            ->get();

        // Every notification links somewhere useful: explicit data.url wins,
        // otherwise the type decides (friend stuff → /friends, bills → /life…)
        $notifications->each(function ($n) {
            $n->url = $n->data['url'] ?? $this->notificationUrl($n->type ?? '');
        });

        return response()->json($notifications);
    }

    /** Default landing page per notification type (used when data.url is absent). */
    private function notificationUrl(string $type): ?string
    {
        return match (true) {
            str_starts_with($type, 'friend')                                  => '/friends',
            str_starts_with($type, 'chama')                                   => '/chama',
            str_starts_with($type, 'quest') || $type === 'contract_completed' => '/world?open=quests',
            str_starts_with($type, 'job') || in_array($type, ['salary_ready', 'salary'], true) => '/life/career',
            str_starts_with($type, 'bill') || str_starts_with($type, 'loan')
                || in_array($type, ['credit_up', 'credit_change', 'interest', 'savings_interest', 'crisis_warning', 'crisis_impact', 'life_event', 'life_sim'], true) => '/life',
            str_starts_with($type, 'deal') || str_starts_with($type, 'asset') || $type === 'investment' => '/portfolio',
            str_starts_with($type, 'forum')                                   => '/forums',
            in_array($type, ['subscribe_nudge', 'plan_upsell'], true)         => '/subscribe',
            in_array($type, ['chapter_unlock', 'birthday', 'age_group_up', 'daily_bonus', 'fun_world', 'smart_reminder'], true) => '/world',
            default => null,
        };
    }

    public function markNotificationsRead()
    {
        GameNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function nextScenario()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        $visitedNodeIds = collect($progress->path_history ?? [])
            ->pluck('node_id')->unique()->toArray();

        $nextStart = \App\Models\Node::forAgeGroup($user->age_group ?? '18-25')
            ->where('is_start', true)
            ->whereNotIn('id', $visitedNodeIds)
            ->orderBy('sort_order')
            ->first();

        if ($nextStart) {
            $newBalance = isset($nextStart->metadata['starting_balance'])
                ? $nextStart->metadata['starting_balance']
                : $progress->balance;

            $progress->current_node_id           = $nextStart->id;
            $progress->current_scenario_start_id  = $nextStart->id;
            $progress->balance                     = $newBalance;
            $progress->save();

            return redirect()->route('game.play');
        }

        return redirect()->route('dashboard')->with('message', 'You have completed all scenarios! Amazing!');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function getMentor(string $ageGroup): array
    {
        return match ($ageGroup) {
            '8-12'  => ['name' => 'Zawadi',    'title' => 'Your Money Friend',       'avatar' => '🐣', 'tip' => 'Saving even Ksh 10 a day adds up to Ksh 3,650 a year!'],
            '13-17' => ['name' => 'Shawn',     'title' => 'Teen Finance Coach',      'avatar' => '🎓', 'tip' => 'Every side hustle you start is building your future.'],
            '18-25' => ['name' => 'Amina',     'title' => 'Young Professional Guide','avatar' => '💼', 'tip' => 'Pay yourself first — save before you spend.'],
            default => ['name' => 'Mama Njeri','title' => 'Wealth Wisdom Advisor',   'avatar' => '👑', 'tip' => 'A budget is not a restriction — it is a plan for your freedom.'],
        };
    }

    private function checkAndAwardBadges(\App\Models\User $user, \App\Models\UserProgress $progress): void
    {
        $ownedIds = $user->badges->pluck('id');

        $badges = \App\Models\Badge::where('is_active', true)
            ->whereNotIn('id', $ownedIds)
            ->get();

        $streak         = $user->streak?->current_streak ?? 0;
        $investments    = \App\Models\Investment::where('user_id', $user->id)->where('status', 'credited')->count();
        $quests         = \App\Models\UserQuest::where('user_id', $user->id)->where('status', 'completed')->count();
        $courseDone     = \App\Models\PlayerCityCourse::where('user_id', $user->id)->where('status', 'completed')->count();
        $jobsHired      = \App\Models\PlayerCityJob::where('user_id', $user->id)->count();
        $assetsBought   = \App\Models\PlayerAsset::where('user_id', $user->id)->count();
        $netWorth       = ($progress->balance ?? 0) + \App\Models\PlayerAsset::where('user_id', $user->id)->sum('purchase_price');

        foreach ($badges as $badge) {
            $earned = match ($badge->trigger_type) {
                'level'           => $progress->level >= $badge->trigger_value,
                'points'          => $progress->points_total >= $badge->trigger_value,
                'streak'          => $streak >= $badge->trigger_value,
                'balance'         => $progress->balance >= $badge->trigger_value,
                'net_worth'       => $netWorth >= $badge->trigger_value,
                'investment'      => $investments >= $badge->trigger_value,
                'asset_purchased' => $assetsBought >= $badge->trigger_value,
                'job_hired'       => $jobsHired >= $badge->trigger_value,
                'course_complete' => $courseDone >= $badge->trigger_value,
                'quest_complete'  => $quests >= $badge->trigger_value,
                'forum_karma'     => (int) ($user->forum_karma ?? 0) >= $badge->trigger_value,
                'manual'          => false,
                default           => $progress->level >= ($badge->required_level ?? 0)
                                     && $progress->points_total >= ($badge->required_points ?? 0),
            };

            if ($earned) {
                $user->badges()->attach($badge->id, ['earned_at' => now()]);

                \App\Models\GameNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'badge',
                    'title'   => '🏅 Badge Earned!',
                    'body'    => "You earned the \"{$badge->name}\" badge! {$badge->description}",
                    'icon'    => $badge->icon ?? '🏅',
                    'data'    => ['badge_id' => $badge->id, 'badge_name' => $badge->name],
                ]);
            }
        }

        // Refresh badges collection so next call is accurate
        $user->load('badges');
    }

    private function unlockCareer(\App\Models\User $user, \App\Models\UserProgress $progress, array $meta): void
    {
        $field = $meta['career_field_unlocked'];
        $cmeta = app(CareerService::class)->fieldMeta($field);

        // Only the career FIELD (interest area) is set here — it feeds job
        // recommendations and quest career-targeting. Salary/title are
        // deliberately NOT set from a scenario choice anymore: real income
        // only ever comes from actually getting hired in Pesa City
        // (OpportunityController::apply). Setting career_income_rate here
        // used to pay a phantom "salary" via CareerService::claimIncome()
        // to players who had never taken a real Pesa City job.
        $progress->career_field = $field;
        $progress->save();

        \App\Models\GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'badge',
            'title'   => "{$cmeta['icon']} Career Interest Unlocked: {$cmeta['label']}!",
            'body'    => "You've discovered an interest in {$cmeta['label']}! Head to the Opportunity Hub in Pesa City to find real jobs in this field.",
            'icon'    => $cmeta['icon'],
            'data'    => ['career_field' => $field],
        ]);

        $this->checkAndAwardBadges($user, $progress);
    }

    private function updateDailyChallenges(\App\Models\User $user, \App\Models\UserProgress $progress, \App\Models\Choice $choice): void
    {
        $today      = now()->toDateString();
        $ageGroup   = $user->age_group ?? '18-25';
        $challenges = DailyChallenge::activeToday($ageGroup)->get();

        foreach ($challenges as $challenge) {
            $udc = UserDailyChallenge::firstOrCreate(
                ['user_id' => $user->id, 'challenge_id' => $challenge->id, 'date' => $today],
                ['progress' => 0]
            );

            if ($udc->isClaimed()) continue;

            $newProgress = $udc->progress;
            $balanceChange = (int) ($choice->effect_data['balance_change'] ?? 0);

            switch ($challenge->challenge_type) {
                case 'make_decisions':
                    $newProgress++;
                    break;
                case 'save_choices':
                    if ($balanceChange > 0 || str_contains(strtolower($choice->label), 'save')) $newProgress++;
                    break;
                case 'earn_ksh':
                    if ($balanceChange > 0) $newProgress += $balanceChange;
                    break;
                case 'reach_balance':
                    $newProgress = $progress->balance;
                    break;
            }

            $completed = $newProgress >= $challenge->target_value && !$udc->isCompleted();
            $udc->progress     = min($newProgress, $challenge->target_value * 2);
            if ($completed) {
                $udc->completed_at = now();
                $progress->addPoints($challenge->xp_bonus);
                GameNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'challenge',
                    'title'   => '🏆 Daily Challenge Complete!',
                    'body'    => "You completed \"{$challenge->title}\" and earned {$challenge->xp_bonus} XP!",
                    'icon'    => '🏆',
                    'data'    => ['challenge_id' => $challenge->id, 'xp' => $challenge->xp_bonus],
                ]);
            }
            $udc->save();
        }
    }
}
