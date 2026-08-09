<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeParticipantSnapshot;
use App\Models\ChallengeTemplate;
use App\Models\Chama;
use App\Models\ChamaMember;
use App\Services\ChallengeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ChallengeController extends Controller
{
    public function index(ChallengeService $service)
    {
        $user = auth()->user();
        $service->refresh($user);

        $pendingInvites = ChallengeParticipant::where('user_id', $user->id)
            ->where('status', 'invited')
            ->with('challenge.creator', 'challenge.participants.user')
            ->get();

        // Created/accepted a duel that's still waiting on the other side, PLUS
        // everything fully active — shown together as ONE "My Challenges" list
        // (each card carries its own status chip) so sending/creating a challenge
        // shows up immediately instead of hiding in a separate section until
        // someone else accepts.
        $myChallenges = ChallengeParticipant::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->whereHas('challenge', fn ($q) => $q->whereIn('status', ['pending', 'active']))
            ->with('challenge.participants.user')
            ->get()
            ->sortBy(fn ($p) => $p->challenge->status === 'active' ? 0 : 1)
            ->values();

        $this->attachRankChanges($myChallenges);

        $myCompleted = ChallengeParticipant::where('user_id', $user->id)
            ->whereHas('challenge', fn ($q) => $q->where('status', 'completed'))
            ->with('challenge')
            ->latest('updated_at')
            ->take(10)
            ->get();

        $joinedIds = ChallengeParticipant::where('user_id', $user->id)->pluck('challenge_id')->all();

        $openBroadcasts = Challenge::where('mode', 'broadcast')
            ->where('status', 'active')
            ->where('scope', 'open')
            ->where('is_chama_battle', false)
            ->whereNotIn('id', $joinedIds)
            ->withCount('participants')
            ->orderByDesc('is_official')
            ->orderBy('ends_at')
            ->get();

        // Inter-chama battles: shown separately, entered by a chairman on behalf of
        // their WHOLE chama rather than joined individually — see enterChamaBattle().
        $chamaBattles = Challenge::where('is_chama_battle', true)
            ->where('status', 'active')
            ->withCount('participants')
            ->with(['participants:id,challenge_id,chama_id'])
            ->orderBy('ends_at')
            ->get();
        $myChairmanships = ChamaMember::where('user_id', $user->id)
            ->where('role', 'chairman')
            ->where('is_active', true)
            ->with('chama')
            ->get()
            ->filter(fn ($m) => $m->chama !== null);

        $templates = $service->templatesForPlayer($user);
        $friends   = $user->friends();

        return view('challenges.index', compact(
            'pendingInvites', 'myChallenges', 'myCompleted', 'openBroadcasts', 'templates', 'friends',
            'chamaBattles', 'myChairmanships'
        ));
    }

    public function create(ChallengeService $service)
    {
        $user      = auth()->user();
        $templates = $service->templatesForPlayer($user);
        $friends   = $user->friends();

        return view('challenges.create', compact('templates', 'friends'));
    }

    public function store(Request $request, ChallengeService $service)
    {
        $data = $request->validate([
            'template_id'       => 'required|exists:challenge_templates,id',
            'title'             => 'nullable|string|min:3|max:150',
            'scope'             => 'nullable|in:friends,public',
            'battle_mode'       => 'nullable|in:duel,ffa',
            'opponent_ids'      => 'required_if:scope,friends|nullable|array|min:1',
            'opponent_ids.*'    => 'integer|exists:users,id',
            'teammate_ids'      => 'nullable|array',
            'teammate_ids.*'    => 'integer|exists:users,id',
            'stake_amount'      => 'nullable|integer|min:0',
            'duration_days'     => 'nullable|integer|min:1|max:60',
            'goal'              => 'nullable|numeric|min:0.01|max:1000000',
            'template_id_2'     => 'nullable|different:template_id|exists:challenge_templates,id',
            'goal_2'            => 'nullable|numeric|min:0.01|max:1000000',
            'requirements'      => 'nullable|array',
            'requirements.*'    => 'string|in:bills_paid_all,min_assets,min_savings,debt_free',
            'min_assets_value'  => 'nullable|integer|min:1|max:50',
            'min_savings_value' => 'nullable|integer|min:0',
        ]);

        $user      = auth()->user();
        $template  = ChallengeTemplate::findOrFail($data['template_id']);
        $template2 = !empty($data['template_id_2']) ? ChallengeTemplate::find($data['template_id_2']) : null;
        $scope     = $data['scope'] ?? 'friends';

        $requirements = null;
        foreach ($data['requirements'] ?? [] as $type) {
            $requirements[] = match ($type) {
                'min_assets'  => ['type' => 'min_assets', 'value' => $data['min_assets_value'] ?? 2],
                'min_savings' => ['type' => 'min_savings', 'value' => $data['min_savings_value'] ?? 0],
                default       => ['type' => $type],
            };
        }

        if ($scope === 'public') {
            $result = $service->createOpenChallenge(
                $user,
                $template,
                $data['stake_amount'] ?? null,
                $data['duration_days'] ?? null,
                $data['title'] ?? null,
                $requirements,
                $data['goal'] ?? null,
                $template2,
                $data['goal_2'] ?? null,
            );
        } elseif (($data['battle_mode'] ?? 'duel') === 'ffa') {
            $result = $service->createFfa(
                $user,
                $template,
                $data['opponent_ids'],
                $data['stake_amount'] ?? null,
                $data['duration_days'] ?? null,
                $data['title'] ?? null,
                $requirements,
                $data['goal'] ?? null,
                $template2,
                $data['goal_2'] ?? null,
            );
        } else {
            $creatorTeam = array_merge([$user->id], $data['teammate_ids'] ?? []);

            $result = $service->createDuel(
                $user,
                $template,
                $creatorTeam,
                $data['opponent_ids'],
                $data['stake_amount'] ?? null,
                $data['duration_days'] ?? null,
                $data['title'] ?? null,
                $requirements,
                $data['goal'] ?? null,
                $template2,
                $data['goal_2'] ?? null,
            );
        }

        if (!$result['ok']) {
            return back()->withInput()->with('error', $result['error']);
        }

        $msg = $scope === 'public'
            ? "Your public challenge is live — anyone eligible can join!"
            : 'Challenge sent — waiting on the other side to accept.';
        if (!empty($result['warning'])) $msg .= ' ' . $result['warning'];

        return redirect()->route('challenges.index')->with('success', $msg);
    }

    public function accept(ChallengeParticipant $participant, ChallengeService $service)
    {
        abort_unless($participant->user_id === auth()->id(), 403);
        $result = $service->acceptInvite($participant);

        return back()->with($result['ok'] ? 'success' : 'error', $result['ok'] ? 'Challenge accepted — your starting line is locked in!' : $result['error']);
    }

    public function decline(ChallengeParticipant $participant, ChallengeService $service)
    {
        abort_unless($participant->user_id === auth()->id(), 403);
        $result = $service->declineInvite($participant);

        return back()->with($result['ok'] ? 'success' : 'error', $result['ok'] ? 'Challenge declined.' : $result['error']);
    }

    public function join(Challenge $challenge, ChallengeService $service, Request $request)
    {
        $result = $service->joinBroadcast($challenge, auth()->user());

        // The Champions' Court world-map popup joins inline via fetch() instead
        // of a real form submission — give it JSON instead of a redirect.
        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with($result['ok'] ? 'success' : 'error', $result['ok'] ? "You're in — good luck!" : $result['error']);
    }

    /** Creator-only: deactivate their own challenge before it's settled — refunds any paid stakes. */
    public function cancel(Challenge $challenge, ChallengeService $service)
    {
        abort_unless($challenge->creator_id === auth()->id(), 403);

        if (!in_array($challenge->status, ['pending', 'active'], true)) {
            return back()->with('error', 'This challenge has already ended.');
        }

        $service->cancelChallenge($challenge, 'The challenge creator cancelled it. Any entry fee has been refunded.', auth()->id());

        return redirect()->route('challenges.index')->with('success', 'Challenge cancelled.');
    }

    /** Chairman-only: enter their whole chama into an open inter-chama battle. */
    public function enterChamaBattle(Request $request, Challenge $challenge, ChallengeService $service)
    {
        $data = $request->validate(['chama_id' => 'required|exists:chamas,id']);

        $chama     = Chama::findOrFail($data['chama_id']);
        $chairman  = $chama->getMemberRecord(auth()->user());

        if (!$chairman || !$chairman->isChairman()) {
            return back()->with('error', 'Only that chama\'s chairman can enter it.');
        }

        $result = $service->enrollChamaIntoBattle($challenge, $chairman, $chama);

        return back()->with($result['ok'] ? 'success' : 'error', $result['ok']
            ? "{$chama->name} is in — {$result['enrolled']} member(s) enrolled automatically."
            : $result['error']);
    }

    /**
     * Public, unauthenticated invite/preview page — the URL the "Share" button
     * copies. `challenges.show` sits behind `auth`, so a logged-out recipient
     * clicking a shared link would hit a login wall with zero context and no
     * link-preview bots would ever see the challenge's own title/description
     * (they fetch pages anonymously). This page is what WhatsApp/Twitter/etc.
     * actually unfurl, and is genuinely indexable (unlike every auth-walled page).
     */
    public function invite(Challenge $challenge)
    {
        $challenge->loadCount('participants');

        return view('challenges.invite', compact('challenge'));
    }

    public function show(Challenge $challenge)
    {
        $challenge->load(['participants.user', 'participants.chama', 'template']);

        $this->attachRankChanges($challenge->participants->where('status', 'accepted'));

        $ranked = $challenge->participants->sortByDesc('progress')->values();

        $chamaRanked = null;
        if ($challenge->is_chama_battle) {
            $chamaRanked = $challenge->participants
                ->groupBy('chama_id')
                ->map(fn ($grp) => [
                    'chama'        => $grp->first()->chama,
                    'avg_progress' => $grp->avg('progress'),
                    'is_winner'    => (bool) $grp->first()->is_winner,
                    'rank'         => $grp->first()->rank,
                    'rank_change'  => $grp->first()->rank_change ?? null,
                    'members'      => $grp->sortByDesc('progress')->values(),
                ])
                ->filter(fn ($row) => $row['chama'] !== null)
                ->sortByDesc('avg_progress')
                ->values();
        }

        return view('challenges.show', compact('challenge', 'ranked', 'chamaRanked'));
    }

    /**
     * A participant's public stats, for the "click an opponent" popup on the
     * challenge page. Scoped to participants of THIS SAME challenge only —
     * not a general "peek at anyone's profile" endpoint — so it only ever
     * reveals what a rival in a shared challenge could already infer.
     */
    public function participantStats(Challenge $challenge, ChallengeParticipant $participant)
    {
        abort_unless($participant->challenge_id === $challenge->id, 404);
        abort_unless(
            $challenge->participants()->where('user_id', auth()->id())->exists() || $challenge->creator_id === auth()->id(),
            403
        );

        $user     = $participant->user;
        $progress = $user?->getOrCreateProgress();

        return response()->json([
            'name'          => $user?->name ?? 'Player',
            'profile_photo' => $user?->profile_photo,
            'level'         => $progress?->level ?? 1,
            'xp'            => (int) ($progress?->points_total ?? 0),
            'net_worth'     => (int) ($progress?->net_worth_cache ?? $progress?->balance ?? 0),
            'played_label'  => $this->gamePlayedLabel((int) ($progress?->tick_count ?? 0)),
            'badges_count'  => $user ? $user->badges()->count() : 0,
        ]);
    }

    /** Same "played for" label GameController::leaderboard() uses — game-simulated time, not real signup age. */
    private function gamePlayedLabel(int $tickCount): string
    {
        $years  = intdiv($tickCount, 365);
        $months = intdiv($tickCount % 365, 30);

        if ($years > 0) return $years . ' game yr' . ($years === 1 ? '' : 's');
        if ($months > 0) return $months . ' game mo' . ($months === 1 ? '' : 's');
        return 'New player';
    }

    /**
     * Attaches a dynamic ->rank_change to each ChallengeParticipant — a nullable int
     * (positive = climbed, negative = dropped, 0 = held, null = no prior snapshot yet)
     * diffing the LIVE current rank (via ChallengeService::rankParticipants, same
     * math the daily snapshot command uses) against the most recent PRIOR day's
     * snapshot — same "null until a second day exists" semantics as the main
     * leaderboard's rank_change (see GameController::leaderboard()). Works across a
     * mixed list spanning several challenges (one row per challenge, e.g. "My
     * Challenges") as well as every participant of a single challenge.
     */
    private function attachRankChanges(Collection $participants): void
    {
        $service = app(ChallengeService::class);

        foreach ($participants->groupBy('challenge_id') as $rows) {
            $challenge = $rows->first()->challenge;
            if (!$challenge || $challenge->status !== 'active') {
                foreach ($rows as $p) $p->rank_change = null;
                continue;
            }

            $all          = $challenge->participants()->where('status', 'accepted')->get();
            $currentRanks = $service->rankParticipants($challenge, $all);
            $ids          = $all->pluck('id')->all();

            $prevDate = ChallengeParticipantSnapshot::whereIn('challenge_participant_id', $ids)
                ->where('snapshot_date', '<', now()->toDateString())
                ->max('snapshot_date');
            $prevRanks = $prevDate
                ? ChallengeParticipantSnapshot::whereIn('challenge_participant_id', $ids)->where('snapshot_date', $prevDate)->pluck('rank', 'challenge_participant_id')
                : collect();

            foreach ($rows as $p) {
                $current = $currentRanks[$p->id] ?? null;
                $prev    = $prevRanks[$p->id] ?? null;
                $p->rank_change = ($current !== null && $prev !== null) ? $prev - $current : null;
            }
        }
    }
}
