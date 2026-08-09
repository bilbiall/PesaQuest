<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Chama;
use App\Models\ChamaAsset;
use App\Models\ChamaInvite;
use App\Models\ChamaMember;
use App\Models\ChamaContribution;
use App\Models\ChamaProposal;
use App\Models\ChamaVote;
use App\Models\Challenge;
use App\Models\ChallengeTemplate;
use App\Models\GameNotification;
use App\Services\ChallengeService;
use App\Services\GameClock;
use App\Services\PlanGate;
use App\Services\QuestTriggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChamaController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Index — list user's chamas + open chamas to join
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $user = auth()->user();

        // Chamas where user is an active member
        $myChamas = Chama::whereHas('activeMembers', fn($q) => $q->where('user_id', $user->id))
            ->with(['activeMembers.user', 'chamaAssets.asset'])
            ->latest()
            ->get();

        // Public chamas the user can join (not full, forming/active, not already member).
        // Private chamas never appear here — entry is by invite or join code only.
        $openChamas = Chama::whereIn('status', ['forming', 'active'])
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('chamas', 'visibility'),
                fn ($q) => $q->where('visibility', 'public'))
            ->whereDoesntHave('activeMembers', fn($q) => $q->where('user_id', $user->id))
            ->where(function ($q) {
                $q->whereRaw('(SELECT COUNT(*) FROM chama_members WHERE chama_members.chama_id = chamas.id AND chama_members.is_active = 1) < chamas.max_members');
            })
            ->with(['activeMembers', 'creator'])
            ->latest()
            ->get();

        // Stats
        $totalPool    = $myChamas->sum('pool_balance');
        $totalMonthly = $myChamas->sum('monthly_contribution');

        // Per-chama user's member record
        $myMemberRecords = ChamaMember::where('user_id', $user->id)
            ->whereIn('chama_id', $myChamas->pluck('id'))
            ->get()
            ->keyBy('chama_id');

        return view('chama.index', compact(
            'user', 'myChamas', 'openChamas', 'totalPool', 'totalMonthly', 'myMemberRecords'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create form
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        return view('chama.create');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Store new chama
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'                 => 'required|string|max:80',
            'description'          => 'nullable|string|max:500',
            'goal_text'            => 'nullable|string|max:200',
            'target_amount'        => 'nullable|integer|min:0',
            'monthly_contribution' => 'required|integer|min:500',
            'max_members'          => 'required|integer|min:3|max:10',
            'visibility'           => 'nullable|in:public,private',
            'min_level'            => 'nullable|integer|in:0,3,5,10',
            'min_credit_score'     => 'nullable|integer|in:0,550,650,750',
            'min_savings'          => 'nullable|integer|in:0,1000,5000,10000',
        ]);

        // Plan gate: creating a chama is premium-only (joining stays free)
        $gate = app(PlanGate::class);
        if ($gate->limit($user, 'chama_create') < 1) {
            return back()->with('error', $gate->deny('chama_create', 0)['error']);
        }

        if ($err = $this->membershipCapError($user)) {
            return back()->with('error', $err);
        }

        $visibilityEnabled = \Illuminate\Support\Facades\Schema::hasColumn('chamas', 'visibility');

        $chama = DB::transaction(function () use ($user, $validated, $visibilityEnabled) {
            $extra = [];
            if ($visibilityEnabled) {
                $visibility = $validated['visibility'] ?? 'public';
                $extra = [
                    'visibility'       => $visibility,
                    'join_code'        => $visibility === 'private' ? $this->freshJoinCode() : null,
                    'min_level'        => (int) ($validated['min_level'] ?? 0),
                    'min_credit_score' => (int) ($validated['min_credit_score'] ?? 0),
                    'min_savings'      => (int) ($validated['min_savings'] ?? 0),
                ];
            }

            $chama = Chama::create([
                'name'                 => $validated['name'],
                'slug'                 => Chama::freshSlug($validated['name']),
                'description'          => $validated['description'] ?? null,
                'goal_text'            => $validated['goal_text'] ?? null,
                'target_amount'        => $validated['target_amount'] ?? 0,
                'monthly_contribution' => $validated['monthly_contribution'],
                'max_members'          => $validated['max_members'],
                'status'               => 'forming',
                'creator_id'           => $user->id,
                'pool_balance'         => 0,
            ] + $extra);

            ChamaMember::create([
                'chama_id'          => $chama->id,
                'user_id'           => $user->id,
                'role'              => 'chairman',
                'total_contributed' => 0,
                'share_pct'         => 100.00,
                'joined_at'         => now(),
                'is_active'         => true,
            ]);

            return $chama;
        });

        return redirect()->route('chama.show', $chama)
            ->with('success', "Chama \"{$chama->name}\" created! Invite members to get started.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Show chama detail
    // ─────────────────────────────────────────────────────────────────────────
    public function show(Chama $chama)
    {
        $user      = auth()->user();
        $progress  = $user->getOrCreateProgress();
        // Game-month key: 1 game month = 30 ticks (game days) on the member's own clock.
        $gameMonth = 'GM-' . str_pad((string) intdiv($progress->tick_count ?? 0, 30), 4, '0', STR_PAD_LEFT);

        $chama->load([
            'activeMembers.user',
            'proposals.proposer',
            'proposals.votes',
            'chamaAssets.asset',
            'creator',
        ]);

        $myMember   = $chama->activeMembers->firstWhere('user_id', $user->id);
        $isChairman = $myMember?->role === 'chairman';

        // Has user contributed this month?
        $hasContributedThisMonth = ChamaContribution::where('chama_id', $chama->id)
            ->where('user_id', $user->id)
            ->where('game_month', $gameMonth)
            ->where('status', 'paid')
            ->exists();

        // All contributions for history
        $allContributions = ChamaContribution::where('chama_id', $chama->id)
            ->with('user')
            ->latest()
            ->get();

        // Available assets to propose for purchase
        $availableAssets = Asset::active()->orderBy('tier')->orderBy('base_price')->get();

        // Progress toward target
        $targetPct = $chama->target_amount > 0
            ? min(100, round(($chama->pool_balance / $chama->target_amount) * 100))
            : 0;

        $monthlyIncome = $chama->monthlyAssetIncome();

        // Friends a member can invite directly (not already in this chama)
        $invitableFriends = collect();
        if ($myMember && Schema::hasTable('friendships') && !$chama->isFull()) {
            $memberIds = $chama->activeMembers->pluck('user_id')->all();
            $invitableFriends = $user->friends()->reject(fn ($f) => in_array($f->id, $memberIds, true))->values();
        }

        // Chama Challenges — chairman-only, launches once the challenges tables exist.
        $challengeTemplates = $isChairman && Schema::hasTable('challenge_templates')
            ? ChallengeTemplate::active()->where('allow_broadcast', true)->orderBy('name')->get()
            : collect();
        $chamaChallenges = Schema::hasTable('challenges')
            ? Challenge::where('chama_id', $chama->id)->withCount('participants')->latest()->take(5)->get()
            : collect();

        return view('chama.show', compact(
            'chama', 'user', 'myMember', 'isChairman',
            'hasContributedThisMonth', 'allContributions',
            'availableAssets', 'targetPct', 'monthlyIncome',
            'gameMonth', 'progress', 'invitableFriends',
            'challengeTemplates', 'chamaChallenges'
        ));
    }

    /** Chairman-only: launch a broadcast Challenge scoped to this chama, auto-enrolling every active member. */
    public function createChallenge(Request $request, Chama $chama, ChallengeService $service)
    {
        $member = $chama->getMemberRecord(auth()->user());
        abort_unless($member && $member->isChairman(), 403);

        $data = $request->validate([
            'template_id'   => 'required|exists:challenge_templates,id',
            'duration_days' => 'nullable|integer|min:1|max:60',
        ]);

        $template = ChallengeTemplate::where('allow_broadcast', true)->findOrFail($data['template_id']);

        $challenge = $service->createBroadcast($template, [
            'title'          => "🤝 Chama Challenge — {$template->name}",
            'scope'          => 'chama',
            'chama_id'       => $chama->id,
            'creator_id'     => auth()->id(),
            'duration_days'  => $data['duration_days'] ?? null,
        ]);

        $enrolled = $service->enrollChamaRoster($challenge);

        return back()->with('success', "Chama Challenge launched — {$enrolled} member(s) enrolled automatically.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Join a chama
    // ─────────────────────────────────────────────────────────────────────────
    public function join(Chama $chama)
    {
        $user = auth()->user();

        if (!in_array($chama->status, ['forming', 'active'])) {
            return back()->with('error', 'This chama is not accepting new members.');
        }
        if ($chama->isFull()) {
            return back()->with('error', 'This chama is full.');
        }
        if ($chama->isMember($user)) {
            return back()->with('error', 'You are already a member of this chama.');
        }
        if ($chama->isPrivate()) {
            return back()->with('error', 'This chama is private — you need an invite or its join code.');
        }
        if ($err = $chama->entryRequirementError($user)) {
            return back()->with('error', $err);
        }
        if ($err = $this->membershipCapError($user)) {
            return back()->with('error', $err);
        }

        DB::transaction(function () use ($chama, $user) {
            ChamaMember::create([
                'chama_id'          => $chama->id,
                'user_id'           => $user->id,
                'role'              => 'member',
                'total_contributed' => 0,
                'share_pct'         => 0,
                'joined_at'         => now(),
                'is_active'         => true,
            ]);

            // Move to active when 3+ members
            if ($chama->memberCount() >= 3 && $chama->status === 'forming') {
                $chama->update(['status' => 'active']);
            }

            $chama->recalculateShares();
        });

        app(QuestTriggerService::class)->fire($user, 'join_chama');

        return back()->with('success', "You joined \"{$chama->name}\"!");
    }

    /**
     * Join a PRIVATE chama with its 6-char join code (great for classrooms —
     * a teacher writes the code on the board). Entry requirements still apply.
     */
    public function joinByCode(Request $request)
    {
        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Schema::hasColumn('chamas', 'join_code')) {
            return back()->with('error', 'Join codes are not enabled yet.');
        }

        $code  = strtoupper(trim((string) $request->validate(['code' => 'required|string|max:12'])['code']));
        $chama = Chama::where('join_code', $code)->first();

        if (!$chama) {
            return back()->with('error', 'No chama found with that code — check it and try again.');
        }
        if (!in_array($chama->status, ['forming', 'active'])) {
            return back()->with('error', 'This chama is not accepting new members.');
        }
        if ($chama->isFull()) {
            return back()->with('error', 'This chama is full.');
        }
        if ($chama->isMember($user)) {
            return redirect()->route('chama.show', $chama)->with('success', 'You are already a member!');
        }
        if ($err = $chama->entryRequirementError($user)) {
            return back()->with('error', $err);
        }
        if ($err = $this->membershipCapError($user)) {
            return back()->with('error', $err);
        }

        DB::transaction(function () use ($chama, $user) {
            ChamaMember::create([
                'chama_id'          => $chama->id,
                'user_id'           => $user->id,
                'role'              => 'member',
                'total_contributed' => 0,
                'share_pct'         => 0,
                'joined_at'         => now(),
                'is_active'         => true,
            ]);

            if ($chama->memberCount() >= 3 && $chama->status === 'forming') {
                $chama->update(['status' => 'active']);
            }

            $chama->recalculateShares();
        });

        app(QuestTriggerService::class)->fire($user, 'join_chama');

        return redirect()->route('chama.show', $chama)->with('success', "Code accepted — welcome to \"{$chama->name}\"!");
    }

    /** Any active member can invite a FRIEND directly — the friend gets a bell/push with the invite link. */
    public function inviteFriend(Request $request, Chama $chama)
    {
        $user = auth()->user();

        if (!$chama->isMember($user)) {
            return back()->with('error', 'Members only.');
        }
        if ($chama->isFull()) {
            return back()->with('error', 'This chama is full — no spots to invite to.');
        }

        $friendId = (int) $request->validate(['friend_id' => 'required|integer|exists:users,id'])['friend_id'];

        if (!\App\Models\Friendship::areFriends($user->id, $friendId)) {
            return back()->with('error', 'You can only invite accepted friends.');
        }
        $friend = \App\Models\User::findOrFail($friendId);
        if ($chama->isMember($friend)) {
            return back()->with('error', "{$friend->name} is already a member.");
        }

        $invite = ChamaInvite::create([
            'chama_id'   => $chama->id,
            'invited_by' => $user->id,
            'token'      => Str::random(24),
            'expires_at' => now()->addSeconds(app(GameClock::class)->realSecondsForTicks(7)),
        ]);

        GameNotification::create([
            'user_id' => $friend->id,
            'type'    => 'chama_invite',
            'title'   => '🤝 ' . $user->name . ' invited you to "' . $chama->name . '"',
            'body'    => 'Monthly contribution Ksh ' . number_format($chama->monthly_contribution) . '. Tap to view the chama and accept.',
            'icon'    => '🤝',
            'data'    => ['url' => route('chama.invite.show', $invite->token, false)],
        ]);

        return back()->with('success', "Invite sent to {$friend->name}!");
    }

    /** Players may belong to at most MAX_MEMBERSHIPS chamas — obligations must stay payable. */
    private function membershipCapError($user): ?string
    {
        $count = ChamaMember::where('user_id', $user->id)->where('is_active', true)->count();
        return $count >= Chama::MAX_MEMBERSHIPS
            ? 'You can only be in ' . Chama::MAX_MEMBERSHIPS . ' chamas at once — leave one before joining another.'
            : null;
    }

    /** A unique, unambiguous 6-char join code (no 0/O or 1/I confusion). */
    private function freshJoinCode(): string
    {
        do {
            $code = '';
            $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
            for ($i = 0; $i < 6; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        } while (Chama::where('join_code', $code)->exists());

        return $code;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Leave a chama
    // ─────────────────────────────────────────────────────────────────────────
    public function leave(Chama $chama)
    {
        $user   = auth()->user();
        $member = $chama->getMemberRecord($user);

        if (!$member) {
            return back()->with('error', 'You are not a member of this chama.');
        }

        // Chairman cannot leave if others exist
        if ($member->isChairman() && $chama->memberCount() > 1) {
            return back()->with('error', 'Chairman cannot leave while other members remain. Transfer chairmanship first.');
        }

        DB::transaction(function () use ($chama, $member) {
            // Apply 10% penalty: only refund 90% of contributed amount
            $penalty = (int) round($member->total_contributed * 0.10);
            $refund  = $member->total_contributed - $penalty;

            if ($refund > 0 && $chama->pool_balance >= $refund) {
                $progress = $member->user->getOrCreateProgress();
                $progress->balance += $refund;
                $progress->save();
                $chama->pool_balance -= $refund;
                $chama->save();
            }

            $member->update(['is_active' => false]);
            $chama->recalculateShares();
        });

        return redirect()->route('chama.index')
            ->with('success', 'You have left the chama. A 10% exit penalty was applied.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Monthly contribution
    // ─────────────────────────────────────────────────────────────────────────
    public function contribute(Chama $chama)
    {
        $user      = auth()->user();
        $progress  = $user->getOrCreateProgress();
        // Game-month key: 1 game month = 30 ticks (game days) on the member's own clock.
        $gameMonth = 'GM-' . str_pad((string) intdiv($progress->tick_count ?? 0, 30), 4, '0', STR_PAD_LEFT);

        if (!$chama->isMember($user)) {
            return back()->with('error', 'You are not a member of this chama.');
        }

        $alreadyPaid = ChamaContribution::where('chama_id', $chama->id)
            ->where('user_id', $user->id)
            ->where('game_month', $gameMonth)
            ->where('status', 'paid')
            ->exists();

        if ($alreadyPaid) {
            return back()->with('error', 'You have already contributed for this game month.');
        }

        $amount = $chama->monthly_contribution;

        if ($progress->balance < $amount) {
            $shortfall = number_format($amount - $progress->balance);
            return back()->with('error', "Insufficient balance. You need Ksh {$shortfall} more.");
        }

        DB::transaction(function () use ($chama, $user, $progress, $amount, $gameMonth) {
            $progress->balance -= $amount;

            ChamaContribution::create([
                'chama_id'   => $chama->id,
                'user_id'    => $user->id,
                'amount'     => $amount,
                'game_month' => $gameMonth,
                'status'     => 'paid',
            ]);

            $chama->pool_balance += $amount;
            $chama->save();

            // Update member's total_contributed
            $member = $chama->getMemberRecord($user);
            if ($member) {
                $member->total_contributed += $amount;
                $member->save();
            }

            $chama->recalculateShares();

            // Cash moved into the pool — the member's chama share offsets it in net worth
            $progress->recalculateNetWorth();
            $progress->save();

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'chama_contribution',
                'title'   => 'Chama Contribution Made',
                'body'    => "Ksh " . number_format($amount) . " contributed to \"{$chama->name}\" for Game Month " . ((int) substr($gameMonth, 3) + 1) . ". Pool: Ksh " . number_format($chama->pool_balance),
                'icon'    => '🤝',
                'data'    => ['chama_id' => $chama->id, 'amount' => $amount, 'game_month' => $gameMonth],
            ]);
        });

        return back()->with('success', 'Contribution of Ksh ' . number_format($amount) . ' made successfully!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Create a proposal
    // ─────────────────────────────────────────────────────────────────────────
    public function propose(Request $request, Chama $chama)
    {
        $user = auth()->user();

        if (!$chama->isMember($user)) {
            return back()->with('error', 'You must be a member to create a proposal.');
        }

        $validated = $request->validate([
            'type'          => 'required|in:buy_asset,sell_asset,change_contribution,remove_member',
            'title'         => 'required|string|max:120',
            'proposal_data' => 'required|array',
        ]);

        ChamaProposal::create([
            'chama_id'      => $chama->id,
            'proposer_id'   => $user->id,
            'type'          => $validated['type'],
            'title'         => $validated['title'],
            'proposal_data' => $validated['proposal_data'],
            'status'        => 'voting',
            'votes_yes'     => 0,
            'votes_no'      => 0,
            // 7 GAME days (converted to real time via the global game clock rate)
            'expires_at'    => now()->addSeconds(app(GameClock::class)->realSecondsForTicks(7)),
        ]);

        return back()->with('success', 'Proposal created. Members can now vote.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Vote on a proposal
    // ─────────────────────────────────────────────────────────────────────────
    public function vote(Request $request, ChamaProposal $proposal)
    {
        $user  = auth()->user();
        $chama = $proposal->chama;

        if (!$chama->isMember($user)) {
            return back()->with('error', 'You must be a chama member to vote.');
        }

        if ($proposal->status !== 'voting') {
            return back()->with('error', 'This proposal is no longer accepting votes.');
        }

        if ($proposal->isExpired()) {
            $proposal->update(['status' => 'rejected']);
            return back()->with('error', 'This proposal has expired.');
        }

        if ($proposal->userVoted($user->id)) {
            return back()->with('error', 'You have already voted on this proposal.');
        }

        $validated = $request->validate([
            'vote' => 'required|in:yes,no',
        ]);

        DB::transaction(function () use ($proposal, $user, $validated) {
            ChamaVote::create([
                'proposal_id' => $proposal->id,
                'user_id'     => $user->id,
                'vote'        => $validated['vote'],
                'cast_at'     => now(),
            ]);

            if ($validated['vote'] === 'yes') {
                $proposal->increment('votes_yes');
            } else {
                $proposal->increment('votes_no');
            }

            $proposal->refresh();

            // Check quorum (>50% of active members voted yes)
            if ($proposal->quorum()) {
                $proposal->update(['status' => 'passed']);
                // Auto-execute buy_asset proposals immediately
                if ($proposal->type === 'buy_asset') {
                    $this->executeBuyAsset($proposal);
                }
            } else {
                // Check if all members have voted and quorum not reached
                $activeMemberCount = $proposal->chama->activeMembers()->count();
                $allVoted = ($proposal->votes_yes + $proposal->votes_no) >= $activeMemberCount;
                if ($allVoted) {
                    $proposal->update(['status' => 'rejected']);
                }
            }
        });

        return back()->with('success', 'Your vote has been cast.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Execute a passed proposal manually
    // ─────────────────────────────────────────────────────────────────────────
    public function executeProposal(ChamaProposal $proposal)
    {
        $user  = auth()->user();
        $chama = $proposal->chama;

        if (!$chama->isMember($user)) {
            return back()->with('error', 'Not authorised.');
        }

        if ($proposal->status !== 'passed') {
            return back()->with('error', 'Only passed proposals can be executed.');
        }

        DB::transaction(function () use ($proposal) {
            match ($proposal->type) {
                'buy_asset'           => $this->executeBuyAsset($proposal),
                'sell_asset'          => $this->executeSellAsset($proposal),
                'change_contribution' => $this->executeChangeContribution($proposal),
                'remove_member'       => $this->executeRemoveMember($proposal),
                default               => $proposal->update(['status' => 'executed']),
            };
        });

        return back()->with('success', 'Proposal executed successfully.');
    }

    /** Apply a passed change_contribution proposal to the chama. */
    private function executeChangeContribution(ChamaProposal $proposal): void
    {
        $chama     = $proposal->chama;
        $newAmount = (int) ($proposal->proposal_data['new_amount'] ?? 0);

        if ($newAmount >= 100) {
            $old = $chama->monthly_contribution;
            $chama->update(['monthly_contribution' => $newAmount]);

            foreach ($chama->activeMembers()->get() as $m) {
                GameNotification::create([
                    'user_id' => $m->user_id,
                    'type'    => 'chama_contribution',
                    'title'   => "🤝 {$chama->name}: Contribution Changed",
                    'body'    => 'Monthly contribution is now Ksh ' . number_format($newAmount) . ' (was Ksh ' . number_format($old) . ') — voted by the members.',
                    'icon'    => '🤝',
                    'data'    => ['chama_id' => $chama->id, 'old' => $old, 'new' => $newAmount],
                ]);
            }
        }

        $proposal->update(['status' => 'executed']);
    }

    /** Apply a passed sell_asset proposal — sell at 95% of market value into the pool. */
    private function executeSellAsset(ChamaProposal $proposal): void
    {
        $chama = $proposal->chama;
        $ca    = ChamaAsset::where('chama_id', $chama->id)
            ->find((int) ($proposal->proposal_data['chama_asset_id'] ?? 0));

        if ($ca) {
            $market    = $ca->asset?->base_price ?? $ca->purchase_price;
            $salePrice = (int) round($market * $ca->quantity * 0.95);

            $chama->increment('pool_balance', $salePrice);
            $ca->delete();

            foreach ($chama->activeMembers()->get() as $m) {
                GameNotification::create([
                    'user_id' => $m->user_id,
                    'type'    => 'chama_income',
                    'title'   => "🤝 {$chama->name}: Asset Sold",
                    'body'    => 'Sold for Ksh ' . number_format($salePrice) . ' (after 5% fee). Funds added to the pool.',
                    'icon'    => '💰',
                    'data'    => ['chama_id' => $chama->id, 'amount' => $salePrice],
                ]);
            }
        }

        $proposal->update(['status' => 'executed']);
    }

    /** Apply a passed remove_member proposal — deactivate the member and recalc shares. */
    private function executeRemoveMember(ChamaProposal $proposal): void
    {
        $chama    = $proposal->chama;
        $targetId = (int) ($proposal->proposal_data['user_id'] ?? 0);

        $member = ChamaMember::where('chama_id', $chama->id)
            ->where('user_id', $targetId)
            ->where('is_active', true)
            ->first();

        if ($member && !$member->isChairman()) {
            $member->update(['is_active' => false]);
            $chama->recalculateShares();

            GameNotification::create([
                'user_id' => $targetId,
                'type'    => 'chama_member_joined',
                'title'   => "🤝 Removed from {$chama->name}",
                'body'    => 'The members voted to remove you from the chama. Your contributions remain in the pool.',
                'icon'    => '👋',
                'data'    => ['chama_id' => $chama->id],
            ]);
        }

        $proposal->update(['status' => 'executed']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Distribute asset income to members (chairman or admin only)
    // ─────────────────────────────────────────────────────────────────────────
    public function distribute(Chama $chama)
    {
        $user   = auth()->user();
        $member = $chama->getMemberRecord($user);

        if (!$member?->isChairman() && !$user->is_admin) {
            return back()->with('error', 'Only the chairman can distribute income.');
        }

        $monthlyIncome = $chama->monthlyAssetIncome();

        if ($monthlyIncome <= 0) {
            return back()->with('error', 'No asset income to distribute this month.');
        }

        DB::transaction(function () use ($chama, $monthlyIncome) {
            $members = $chama->activeMembers()->with('user')->get();

            foreach ($members as $m) {
                $share = (int) round($monthlyIncome * ($m->share_pct / 100));
                if ($share <= 0) continue;

                $progress = $m->user->getOrCreateProgress();
                $progress->balance += $share;
                $progress->save();

                GameNotification::create([
                    'user_id' => $m->user_id,
                    'type'    => 'chama_income',
                    'title'   => 'Chama Income Distributed',
                    'body'    => "You received Ksh " . number_format($share) . " from \"{$chama->name}\" (your {$m->share_pct}% share of Ksh " . number_format($monthlyIncome) . " monthly income).",
                    'icon'    => '💰',
                    'data'    => ['chama_id' => $chama->id, 'amount' => $share],
                ]);
            }
        });

        return back()->with('success', 'Income of Ksh ' . number_format($monthlyIncome) . ' distributed to all members!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal: execute a buy_asset proposal
    // ─────────────────────────────────────────────────────────────────────────
    protected function executeBuyAsset(ChamaProposal $proposal): void
    {
        $chama    = $proposal->chama;
        $data     = $proposal->proposal_data;
        $assetId  = $data['asset_id'] ?? null;
        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        if (!$assetId) {
            $proposal->update(['status' => 'rejected']);
            return;
        }

        $asset     = Asset::find($assetId);
        $totalCost = ($asset?->base_price ?? 0) * $quantity;

        if (!$asset || $chama->pool_balance < $totalCost) {
            $proposal->update(['status' => 'rejected']);
            return;
        }

        ChamaAsset::create([
            'chama_id'       => $chama->id,
            'asset_id'       => $asset->id,
            'purchase_price' => $asset->base_price,
            'quantity'       => $quantity,
            'purchased_at'   => now(),
        ]);

        $chama->pool_balance -= $totalCost;
        $chama->save();

        $proposal->update(['status' => 'executed']);

        // Notify all active members
        $chama->activeMembers()->each(function ($m) use ($chama, $asset, $quantity, $totalCost) {
            GameNotification::create([
                'user_id' => $m->user_id,
                'type'    => 'chama_asset_purchased',
                'title'   => "Chama Bought {$asset->name}!",
                'body'    => "\"{$chama->name}\" acquired {$quantity}x {$asset->name} for Ksh " . number_format($totalCost) . ". Pool remaining: Ksh " . number_format($chama->pool_balance),
                'icon'    => $asset->icon ?? '🏢',
                'data'    => ['chama_id' => $chama->id, 'asset_id' => $asset->id],
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Invite links
    // ─────────────────────────────────────────────────────────────────────────

    public function generateInvite(Chama $chama)
    {
        $user = auth()->user();

        if (!$chama->isMember($user)) {
            return response()->json(['error' => 'Members only.'], 403);
        }

        if ($chama->isFull()) {
            return response()->json(['error' => 'Chama is full — no spots to invite to.'], 422);
        }

        // Replace any existing invite from this user for this chama
        ChamaInvite::where('chama_id', $chama->id)
            ->where('invited_by', $user->id)
            ->delete();

        $invite = ChamaInvite::create([
            'chama_id'   => $chama->id,
            'invited_by' => $user->id,
            'token'      => Str::random(24),
            // 7 GAME days (converted to real time via the global game clock rate)
            'expires_at' => now()->addSeconds(app(GameClock::class)->realSecondsForTicks(7)),
        ]);

        return response()->json([
            'url' => route('chama.invite.show', $invite->token),
        ]);
    }

    public function showInvite(string $token)
    {
        $invite = ChamaInvite::with('chama.activeMembers.user', 'inviter')->where('token', $token)->firstOrFail();

        if (!$invite->isValid()) {
            return view('chama.invite-expired');
        }

        $chama = $invite->chama;
        $user  = auth()->user();

        if ($user && $chama->isMember($user)) {
            return redirect()->route('chama.show', $chama)->with('success', 'You are already a member of this chama!');
        }

        return view('chama.invite', compact('invite', 'chama'));
    }

    public function acceptInvite(Request $request, string $token)
    {
        $invite = ChamaInvite::with('chama')->where('token', $token)->firstOrFail();

        if (!$invite->isValid()) {
            return back()->with('error', 'This invite link has expired.');
        }

        $chama = $invite->chama;
        $user  = auth()->user();

        if ($chama->isMember($user)) {
            return redirect()->route('chama.show', $chama)->with('success', 'You are already a member!');
        }

        if (!in_array($chama->status, ['forming', 'active'])) {
            return back()->with('error', 'This chama is no longer accepting new members.');
        }

        if ($chama->isFull()) {
            return back()->with('error', 'This chama is full — no spots remaining.');
        }

        // Invites vouch for you (no entry requirements) but the chama cap still applies
        if ($err = $this->membershipCapError($user)) {
            return back()->with('error', $err);
        }

        ChamaMember::create([
            'chama_id'  => $chama->id,
            'user_id'   => $user->id,
            'role'      => 'member',
            'joined_at' => now(),
            'is_active' => true,
        ]);

        if ($chama->memberCount() >= 3 && $chama->status === 'forming') {
            $chama->update(['status' => 'active']);
        }

        $chama->recalculateShares();

        GameNotification::create([
            'user_id' => $invite->invited_by,
            'type'    => 'chama_member_joined',
            'title'   => 'New Chama Member!',
            'body'    => "{$user->name} joined \"{$chama->name}\" via your invite link.",
            'icon'    => '🤝',
        ]);

        return redirect()->route('chama.show', $chama)->with('success', "Welcome to {$chama->name}! Make your first contribution to get started.");
    }
}
