<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeTemplate;
use App\Models\ChamaMember;
use App\Models\GameNotification;
use App\Models\SchoolMember;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Fair PvP/team/broadcast competitions. Progress is BASELINE + DELTA (same
 * mechanism as ContractService, via the shared GameMetrics class) so
 * existing wealth never decides a winner — only what a player earns/grows
 * DURING the challenge window counts.
 *
 * Two shapes:
 *   - duel:      1v1 or equal-size team vs team. Starts 'pending' until every
 *                invitee accepts, at which point everyone's baseline is
 *                snapshotted at the SAME real moment (fair start line).
 *   - broadcast: active immediately. Anyone eligible opts in (joinBroadcast),
 *                or — for a school Class Challenge — the whole roster is
 *                auto-enrolled (enrollSchoolRoster). Ranked at the deadline.
 */
class ChallengeService
{
    private const WINNER_XP      = 250;
    private const PARTICIPANT_XP = 40;

    /** Winner(s) split this % of the entry-fee pool; the rest is forfeited (a risk premium, not redistributed). */
    private const WINNER_POOL_PCT = 90;

    /** Templates a player is allowed to spin up a duel from. */
    public function templatesForPlayer(User $user): Collection
    {
        $level = $user->getOrCreateProgress()->level ?? 1;

        return ChallengeTemplate::active()
            ->where('allow_player_created', true)
            ->where('level_min', '<=', $level)
            ->where('level_max', '>=', $level)
            ->orderBy('name')
            ->get();
    }

    /** Duration input, falling back to the template default only when nothing was
     *  explicitly chosen — 0 is a deliberate "All-Time" choice, not "unset", so this
     *  must not use `?:` (which would treat 0 as falsy and silently override it). */
    private function resolveDurationDays(?int $durationDays, ChallengeTemplate $template): int
    {
        return $durationDays !== null ? $durationDays : $template->default_duration_days;
    }

    /** 0 days = All-Time — the challenge never hits a deadline (null ends_at is
     *  already excluded from sweepDeadlines()'s `ends_at <= now()` SQL comparison). */
    private function computeEndsAt(int $days, \Illuminate\Support\Carbon $now): ?\Illuminate\Support\Carbon
    {
        return $days === 0 ? null : $now->copy()->addDays(max(1, $days));
    }

    /** A soft (never-blocking) fairness heads-up for lopsided player-created duels. */
    public function bracketWarning(array $creatorIds, array $opponentIds): ?string
    {
        $levelOf = fn (int $id) => (\App\Models\UserProgress::where('user_id', $id)->value('level')) ?? 1;
        $creatorAvg  = collect($creatorIds)->avg($levelOf);
        $opponentAvg = collect($opponentIds)->avg($levelOf);

        if (abs($creatorAvg - $opponentAvg) >= 3) {
            return 'Heads up — these levels are pretty far apart, which may make this matchup uneven. You can still send it.';
        }
        return null;
    }

    /**
     * @return array{ok: bool, error?: string, challenge?: Challenge, warning?: ?string}
     */
    public function createDuel(User $creator, ChallengeTemplate $template, array $creatorTeamIds, array $opponentTeamIds, ?int $stakeAmount, ?int $durationDays, ?string $customTitle = null, ?array $requirements = null, ?float $goal = null, ?ChallengeTemplate $template2 = null, ?float $goal2 = null): array
    {
        if (count($creatorTeamIds) !== count($opponentTeamIds)) {
            return ['ok' => false, 'error' => 'Both sides must have the same number of players.'];
        }
        if (in_array($creator->id, $opponentTeamIds, true) || array_intersect($creatorTeamIds, $opponentTeamIds)) {
            return ['ok' => false, 'error' => 'A player can\'t be on both sides.'];
        }

        $creatorProgress = $creator->getOrCreateProgress();
        if ($stakeAmount && ($creatorProgress->balance ?? 0) < $stakeAmount) {
            return ['ok' => false, 'error' => 'You need KES ' . number_format($stakeAmount) . ' to cover your own entry fee.'];
        }

        $days  = $this->resolveDurationDays($durationDays, $template);
        $now   = now();
        $title = $customTitle ?: $template->name;

        $challenge = Challenge::create([
            'template_id' => $template->id,
            'mode'        => 'duel',
            'scope'       => 'friends',
            'is_official' => false,
            'creator_id'  => $creator->id,
            'title'       => $title,
            'slug'        => Challenge::freshSlug($title),
            'metric'      => $template->metric,
            'style'       => $template->style,
            'goal'        => $goal ?? $this->defaultGoal($template),
            'metric_2'    => $template2?->metric,
            'style_2'     => $template2?->style,
            'goal_2'      => $template2 ? ($goal2 ?? $this->defaultGoal($template2)) : null,
            'requirements'=> $requirements,
            'stake_amount'=> $stakeAmount,
            'level_min'   => $template->level_min,
            'level_max'   => $template->level_max,
            'starts_at'   => $now,
            'ends_at'     => $this->computeEndsAt($days, $now),
            'status'      => 'pending',
        ]);

        // The creator's own row auto-accepts — creating the challenge IS their
        // commitment, so their entry fee is charged right now (same fee every
        // other accepter pays), not left free while everyone else pays in.
        if ($stakeAmount) {
            $creatorProgress->balance -= $stakeAmount;
            $creatorProgress->save();
        }

        foreach ([1 => $creatorTeamIds, 2 => $opponentTeamIds] as $teamId => $ids) {
            foreach ($ids as $uid) {
                ChallengeParticipant::create([
                    'challenge_id' => $challenge->id,
                    'user_id'      => $uid,
                    'team_id'      => $teamId,
                    'status'       => $uid === $creator->id ? 'accepted' : 'invited',
                    'baseline'     => 0,
                    'progress'     => 0,
                    'stake_paid'   => $uid === $creator->id && $stakeAmount ? true : false,
                ]);
            }
        }

        $allInvited = array_merge($creatorTeamIds, $opponentTeamIds);
        foreach ($allInvited as $uid) {
            if ($uid === $creator->id) continue;
            GameNotification::create([
                'user_id' => $uid,
                'type'    => 'challenge_invite',
                'title'   => "⚔️ {$creator->name} challenged you — {$template->name}",
                'body'    => 'A ' . ($stakeAmount ? 'KES ' . number_format($stakeAmount) . ' entry fee ' : '') . "challenge is waiting for your answer. Accept to lock in your starting line.",
                'icon'    => '⚔️',
                'data'    => ['challenge_id' => $challenge->id],
            ]);
        }

        return [
            'ok'        => true,
            'challenge' => $challenge,
            'warning'   => $this->bracketWarning($creatorTeamIds, $opponentTeamIds),
        ];
    }

    /**
     * Free-for-all: one creator vs 2+ individual opponents, ranked by their own
     * progress (no teams). Reuses the duel invite/accept flow via mode='duel',
     * is_team_based=false — settle() then falls back to individual ranking.
     *
     * @return array{ok: bool, error?: string, challenge?: Challenge}
     */
    public function createFfa(User $creator, ChallengeTemplate $template, array $opponentIds, ?int $stakeAmount, ?int $durationDays, ?string $customTitle = null, ?array $requirements = null, ?float $goal = null, ?ChallengeTemplate $template2 = null, ?float $goal2 = null): array
    {
        $opponentIds = array_values(array_unique(array_diff($opponentIds, [$creator->id])));
        if (count($opponentIds) < 2) {
            return ['ok' => false, 'error' => 'Pick at least 2 opponents for a free-for-all — for just one, use a 1v1 duel instead.'];
        }

        $creatorProgress = $creator->getOrCreateProgress();
        if ($stakeAmount && ($creatorProgress->balance ?? 0) < $stakeAmount) {
            return ['ok' => false, 'error' => 'You need KES ' . number_format($stakeAmount) . ' to cover your own entry fee.'];
        }

        $days  = $this->resolveDurationDays($durationDays, $template);
        $now   = now();
        $title = $customTitle ?: $template->name;

        $challenge = Challenge::create([
            'template_id'   => $template->id,
            'mode'          => 'duel',
            'is_team_based' => false,
            'scope'         => 'friends',
            'is_official'   => false,
            'creator_id'    => $creator->id,
            'title'         => $title,
            'slug'          => Challenge::freshSlug($title),
            'metric'        => $template->metric,
            'style'         => $template->style,
            'goal'          => $goal ?? $this->defaultGoal($template),
            'metric_2'      => $template2?->metric,
            'style_2'       => $template2?->style,
            'goal_2'        => $template2 ? ($goal2 ?? $this->defaultGoal($template2)) : null,
            'requirements'  => $requirements,
            'stake_amount'  => $stakeAmount,
            'level_min'     => $template->level_min,
            'level_max'     => $template->level_max,
            'starts_at'     => $now,
            'ends_at'       => $this->computeEndsAt($days, $now),
            'status'        => 'pending',
        ]);

        // Same commit-on-create convention as createDuel(): the creator's own
        // entry fee is charged now, everyone else pays when they accept.
        if ($stakeAmount) {
            $creatorProgress->balance -= $stakeAmount;
            $creatorProgress->save();
        }

        foreach (array_merge([$creator->id], $opponentIds) as $uid) {
            ChallengeParticipant::create([
                'challenge_id' => $challenge->id,
                'user_id'      => $uid,
                'team_id'      => null,
                'status'       => $uid === $creator->id ? 'accepted' : 'invited',
                'baseline'     => 0,
                'progress'     => 0,
                'stake_paid'   => $uid === $creator->id && $stakeAmount ? true : false,
            ]);
        }

        foreach ($opponentIds as $uid) {
            GameNotification::create([
                'user_id' => $uid,
                'type'    => 'challenge_invite',
                'title'   => "⚔️ {$creator->name} challenged you — {$template->name}",
                'body'    => 'A free-for-all challenge is waiting for your answer' . ($stakeAmount ? ' (KES ' . number_format($stakeAmount) . ' entry fee)' : '') . '. Accept to lock in your starting line.',
                'icon'    => '⚔️',
                'data'    => ['challenge_id' => $challenge->id],
            ]);
        }

        return ['ok' => true, 'challenge' => $challenge];
    }

    /**
     * A PLAYER (not an admin/teacher/chairman) launching an open challenge —
     * live immediately, anyone eligible can join via joinBroadcast(), no
     * invite/accept step. The creator auto-joins their own challenge through
     * the same joinBroadcast() path everyone else uses, so eligibility/stake
     * rules never diverge between the creator and later joiners.
     *
     * @return array{ok: bool, error?: string, challenge?: Challenge}
     */
    public function createOpenChallenge(User $creator, ChallengeTemplate $template, ?int $stakeAmount, ?int $durationDays, ?string $customTitle = null, ?array $requirements = null, ?float $goal = null, ?ChallengeTemplate $template2 = null, ?float $goal2 = null): array
    {
        $challenge = $this->createBroadcast($template, [
            'scope'         => 'open',
            'is_official'   => false,
            'creator_id'    => $creator->id,
            'title'         => $customTitle,
            'requirements'  => $requirements,
            'stake_amount'  => $stakeAmount,
            'duration_days' => $durationDays,
            'goal'          => $goal,
            'template_2'    => $template2,
            'goal_2'        => $goal2,
        ]);

        $join = $this->joinBroadcast($challenge, $creator);
        if (!$join['ok']) {
            $challenge->delete();
            return $join;
        }

        return ['ok' => true, 'challenge' => $challenge];
    }

    public function acceptInvite(ChallengeParticipant $participant): array
    {
        if ($participant->status !== 'invited') {
            return ['ok' => false, 'error' => 'This invite is no longer pending.'];
        }

        $challenge = $participant->challenge;
        $user      = $participant->user;
        $progress  = $user->getOrCreateProgress();

        if ($challenge->stake_amount && ($progress->balance ?? 0) < $challenge->stake_amount) {
            return ['ok' => false, 'error' => 'You need KES ' . number_format($challenge->stake_amount) . ' to cover the entry fee.'];
        }

        if ($challenge->stake_amount) {
            $progress->balance -= $challenge->stake_amount;
            $progress->save();
        }

        $participant->update(['status' => 'accepted', 'stake_paid' => (bool) $challenge->stake_amount]);

        $stillWaiting = ChallengeParticipant::where('challenge_id', $challenge->id)->where('status', 'invited')->exists();
        if (!$stillWaiting) {
            $this->activateDuel($challenge);
        }

        return ['ok' => true];
    }

    public function declineInvite(ChallengeParticipant $participant): array
    {
        if ($participant->status !== 'invited') {
            return ['ok' => false, 'error' => 'This invite is no longer pending.'];
        }

        $participant->update(['status' => 'declined']);
        $challenge = $participant->challenge;

        if (!$challenge->is_team_based) {
            // FFA: one decline just drops that player — the rest of the pack carries on.
            $remaining = $challenge->participants()->whereIn('status', ['invited', 'accepted'])->count();
            if ($remaining < 2) {
                $this->cancelAndRefund($challenge, $participant, 'Not enough players left to compete. Any entry fee has been refunded.');
            } elseif (!$challenge->participants()->where('status', 'invited')->exists()) {
                $this->activateDuel($challenge);
            }
            return ['ok' => true];
        }

        // Team duel: one decline sinks the whole thing — refund anyone who already paid in.
        $this->cancelAndRefund($challenge, $participant, 'The other side declined. Any entry fee has been refunded.');

        return ['ok' => true];
    }

    /**
     * Cancel an in-progress (pending or active) challenge outright — refunds any
     * paid stakes and notifies every participant. Shared by the creator's own
     * "cancel my challenge" action, the GameSet admin's oversight cancel, and
     * (via cancelAndRefund()'s thin wrapper) the decline-triggered auto-cancel.
     */
    public function cancelChallenge(Challenge $challenge, string $notifyBody, ?int $excludeUserId = null): void
    {
        $challenge->update(['status' => 'cancelled']);
        foreach ($challenge->participants as $p) {
            if ($p->stake_paid) {
                $prog = $p->user->getOrCreateProgress();
                $prog->balance += $challenge->stake_amount;
                $prog->save();
                $p->update(['stake_paid' => false]);
            }
            if ($p->user_id !== $excludeUserId && $p->status !== 'declined') {
                GameNotification::create([
                    'user_id' => $p->user_id,
                    'type'    => 'challenge_cancelled',
                    'title'   => "Challenge cancelled — {$challenge->title}",
                    'body'    => $notifyBody,
                    'icon'    => '🚫',
                    'data'    => ['challenge_id' => $challenge->id],
                ]);
            }
        }
    }

    private function cancelAndRefund(Challenge $challenge, ChallengeParticipant $decliner, string $notifyBody): void
    {
        $this->cancelChallenge($challenge, $notifyBody, $decliner->user_id);
    }

    private function activateDuel(Challenge $challenge): void
    {
        $now = now();

        if ($challenge->ends_at === null) {
            // All-Time duel — no deadline to restart the countdown against.
            $challenge->update(['status' => 'active', 'starts_at' => $now]);
        } else {
            $days = max(1, $challenge->starts_at->diffInDays($challenge->ends_at));
            $challenge->update(['status' => 'active', 'starts_at' => $now, 'ends_at' => $now->copy()->addDays($days)]);
        }

        foreach ($challenge->participants()->where('status', 'accepted')->get() as $p) {
            $user     = $p->user;
            $progress = $user->getOrCreateProgress();
            $p->update($this->baselineFields($challenge, $user, $progress, $now) + [
                'progress'   => 0,
                'progress_2' => $challenge->hasSecondMetric() ? 0 : null,
                'joined_at'  => $now,
            ]);
        }
    }

    /** Publish an admin "PesaCity Challenge" or a teacher "Class Challenge" — join-to-compete, ranked at the deadline. */
    public function createBroadcast(ChallengeTemplate $template, array $opts): Challenge
    {
        // $opts is a plain untyped array (no scalar coercion at a call boundary
        // like createDuel()'s typed ?int params get) — form input arrives as a
        // numeric string, which Carbon's addDays() now rejects outright.
        $days     = $this->resolveDurationDays(isset($opts['duration_days']) ? (int) $opts['duration_days'] : null, $template);
        $now      = now();
        $title    = $opts['title'] ?? $template->name;
        $template2 = $opts['template_2'] ?? null;

        return Challenge::create([
            'template_id'             => $template->id,
            'mode'                    => 'broadcast',
            'scope'                   => $opts['scope'] ?? 'open',
            'is_official'             => $opts['is_official'] ?? false,
            'is_chama_battle'         => $opts['is_chama_battle'] ?? false,
            'creator_id'              => $opts['creator_id'] ?? null,
            'school_subscription_id'  => $opts['school_subscription_id'] ?? null,
            'school_class_id'         => $opts['school_class_id'] ?? null,
            'chama_id'                => $opts['chama_id'] ?? null,
            'title'                   => $title,
            'slug'                    => Challenge::freshSlug($title),
            'metric'                  => $template->metric,
            'style'                   => $template->style,
            'goal'                    => $opts['goal'] ?? $this->defaultGoal($template),
            'metric_2'                => $template2?->metric,
            'style_2'                 => $template2?->style,
            'goal_2'                  => $template2 ? ($opts['goal_2'] ?? $this->defaultGoal($template2)) : null,
            'requirements'            => $opts['requirements'] ?? null,
            'stake_amount'            => $opts['stake_amount'] ?? null,
            'level_min'               => $opts['level_min'] ?? $template->level_min,
            'level_max'               => $opts['level_max'] ?? $template->level_max,
            'starts_at'               => $now,
            'ends_at'                 => $this->computeEndsAt($days, $now),
            'status'                  => 'active',
        ]);
    }

    public function joinBroadcast(Challenge $challenge, User $user): array
    {
        if (!$challenge->isBroadcast() || $challenge->status !== 'active') {
            return ['ok' => false, 'error' => 'This challenge isn\'t open for joining.'];
        }
        if ($challenge->ends_at !== null && now()->greaterThanOrEqualTo($challenge->ends_at)) {
            return ['ok' => false, 'error' => 'This challenge has already ended.'];
        }
        if (ChallengeParticipant::where('challenge_id', $challenge->id)->where('user_id', $user->id)->exists()) {
            return ['ok' => false, 'error' => 'You\'re already in this challenge.'];
        }

        $progress = $user->getOrCreateProgress();
        $level    = $progress->level ?? 1;
        if ($level < $challenge->level_min || $level > $challenge->level_max) {
            return ['ok' => false, 'error' => "This challenge is for levels {$challenge->level_min}\u{2013}{$challenge->level_max}."];
        }
        if ($challenge->stake_amount && ($progress->balance ?? 0) < $challenge->stake_amount) {
            return ['ok' => false, 'error' => 'You need KES ' . number_format($challenge->stake_amount) . ' to cover the entry fee.'];
        }

        if ($challenge->stake_amount) {
            $progress->balance -= $challenge->stake_amount;
            $progress->save();
        }

        $now = now();
        ChallengeParticipant::create($this->baselineFields($challenge, $user, $progress, $now) + [
            'challenge_id' => $challenge->id,
            'user_id'      => $user->id,
            'status'       => 'accepted',
            'progress'     => 0,
            'progress_2'   => $challenge->hasSecondMetric() ? 0 : null,
            'stake_paid'   => (bool) $challenge->stake_amount,
            'joined_at'    => $now,
        ]);

        return ['ok' => true];
    }

    /**
     * Auto-enroll every active student — a Class Challenge is assigned, not opt-in. Never
     * charges a stake. When the challenge has a school_class_id, only that class's roster
     * is enrolled; null (the default before Classes existed) still enrolls the whole school.
     */
    public function enrollSchoolRoster(Challenge $challenge): int
    {
        $now      = now();
        $existing = ChallengeParticipant::where('challenge_id', $challenge->id)->pluck('user_id')->all();

        $roster = SchoolMember::where('school_subscription_id', $challenge->school_subscription_id)
            ->where('status', 'active')
            ->when($challenge->school_class_id, fn ($q) => $q->where('school_class_id', $challenge->school_class_id))
            ->whereNotIn('user_id', $existing)
            ->with('user.progress')
            ->get();

        $count = 0;
        foreach ($roster as $member) {
            $user = $member->user;
            if (!$user) continue;
            $progress = $user->getOrCreateProgress();

            ChallengeParticipant::create($this->baselineFields($challenge, $user, $progress, $now) + [
                'challenge_id' => $challenge->id,
                'user_id'      => $user->id,
                'status'       => 'accepted',
                'progress'     => 0,
                'progress_2'   => $challenge->hasSecondMetric() ? 0 : null,
                'stake_paid'   => false,
                'joined_at'    => $now,
            ]);

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'class_challenge',
                'title'   => "🏫 New Class Challenge — {$challenge->title}",
                'body'    => 'Your teacher started a class challenge. Check the school leaderboard to see where you stand.',
                'icon'    => '🏫',
                'data'    => ['challenge_id' => $challenge->id],
            ]);

            $count++;
        }

        return $count;
    }

    /** Auto-enroll every active chama member — same shape as enrollSchoolRoster(), for a chairman-launched Chama Challenge. */
    public function enrollChamaRoster(Challenge $challenge): int
    {
        $now      = now();
        $existing = ChallengeParticipant::where('challenge_id', $challenge->id)->pluck('user_id')->all();

        $roster = ChamaMember::where('chama_id', $challenge->chama_id)
            ->where('is_active', true)
            ->whereNotIn('user_id', $existing)
            ->with('user.progress')
            ->get();

        $count = 0;
        foreach ($roster as $member) {
            $user = $member->user;
            if (!$user) continue;
            $progress = $user->getOrCreateProgress();

            ChallengeParticipant::create($this->baselineFields($challenge, $user, $progress, $now) + [
                'challenge_id' => $challenge->id,
                'user_id'      => $user->id,
                'status'       => 'accepted',
                'progress'     => 0,
                'progress_2'   => $challenge->hasSecondMetric() ? 0 : null,
                'stake_paid'   => false,
                'joined_at'    => $now,
            ]);

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'chama_challenge',
                'title'   => "🤝 New Chama Challenge — {$challenge->title}",
                'body'    => 'Your chama chairman started a challenge. Check the leaderboard to see where your chama stands.',
                'icon'    => '🤝',
                'data'    => ['challenge_id' => $challenge->id],
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Enter a whole chama into an open, multi-chama battle (challenges.is_chama_battle).
     * Unlike enrollChamaRoster() (a chairman-launched, chama-scoped Class-Challenge-style
     * broadcast), this challenge isn't owned by any one chama — challenges.chama_id stays
     * null, and each participant instead carries its OWN chama_id so settle() can group
     * and rank by chama (average progress) rather than by individual.
     *
     * @return array{ok: bool, error?: string, enrolled?: int}
     */
    public function enrollChamaIntoBattle(Challenge $challenge, ChamaMember $chairman, \App\Models\Chama $chama): array
    {
        if (!$challenge->is_chama_battle || $challenge->status !== 'active') {
            return ['ok' => false, 'error' => 'This battle isn\'t open for entry.'];
        }
        if (!$chairman->isChairman() || $chairman->chama_id !== $chama->id) {
            return ['ok' => false, 'error' => 'Only that chama\'s chairman can enter it.'];
        }
        if (ChallengeParticipant::where('challenge_id', $challenge->id)->where('chama_id', $chama->id)->exists()) {
            return ['ok' => false, 'error' => 'Your chama has already entered this battle.'];
        }

        $now      = now();
        $existing = ChallengeParticipant::where('challenge_id', $challenge->id)->pluck('user_id')->all();

        $roster = ChamaMember::where('chama_id', $chama->id)
            ->where('is_active', true)
            ->whereNotIn('user_id', $existing)
            ->with('user.progress')
            ->get();

        $count = 0;
        foreach ($roster as $member) {
            $user = $member->user;
            if (!$user) continue;
            $progress = $user->getOrCreateProgress();

            ChallengeParticipant::create($this->baselineFields($challenge, $user, $progress, $now) + [
                'challenge_id' => $challenge->id,
                'user_id'      => $user->id,
                'chama_id'     => $chama->id,
                'status'       => 'accepted',
                'progress'     => 0,
                'progress_2'   => $challenge->hasSecondMetric() ? 0 : null,
                'stake_paid'   => false,
                'joined_at'    => $now,
            ]);

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'chama_battle',
                'title'   => "⚔️ {$chama->name} entered a Chama Battle — {$challenge->title}",
                'body'    => 'Your chairman entered your chama into an inter-chama battle. Check the leaderboard to see how your chama stacks up.',
                'icon'    => '⚔️',
                'data'    => ['challenge_id' => $challenge->id],
            ]);

            $count++;
        }

        return ['ok' => true, 'enrolled' => $count];
    }

    /** Recompute progress for every active challenge this user is in; settles duels the instant someone hits the goal. */
    public function refresh(User $user): void
    {
        $participants = ChallengeParticipant::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->whereHas('challenge', fn ($q) => $q->where('status', 'active'))
            ->with('challenge')
            ->get();

        if ($participants->isEmpty()) return;

        $progress = $user->getOrCreateProgress();

        foreach ($participants as $p) {
            $challenge = $p->challenge;
            $value     = $this->progressValue($challenge->metric, $challenge->style, (int) $p->baseline, $user, $progress);

            $updates = [];
            if ((float) $p->progress !== (float) $value) {
                $updates['progress'] = $value;
            }

            if ($challenge->hasSecondMetric()) {
                $value2 = $this->progressValue($challenge->metric_2, $challenge->style_2, (int) $p->baseline_2, $user, $progress);
                if ((float) ($p->progress_2 ?? 0) !== (float) $value2) {
                    $updates['progress_2'] = $value2;
                }
            }

            if ($updates) {
                $p->update($updates);
            }

            if ($challenge->isDuel() && $value >= $challenge->goal) {
                $this->settle($challenge->fresh());
            }
        }
    }

    /** Called by the nightly sweep for every challenge whose deadline has passed. */
    public function sweepDeadlines(): array
    {
        $settled = 0;
        foreach (Challenge::where('status', 'active')->where('ends_at', '<=', now())->get() as $challenge) {
            $this->settle($challenge);
            $settled++;
        }

        // Duels nobody ever fully accepted — treat the same window as an "accept by" deadline.
        $cancelled = 0;
        foreach (Challenge::where('status', 'pending')->where('ends_at', '<=', now())->get() as $challenge) {
            foreach ($challenge->participants as $p) {
                if ($p->stake_paid) {
                    $prog = $p->user->getOrCreateProgress();
                    $prog->balance += $challenge->stake_amount;
                    $prog->save();
                }
            }
            $challenge->update(['status' => 'cancelled']);
            $cancelled++;
        }

        return ['settled' => $settled, 'cancelled_unaccepted' => $cancelled];
    }

    /**
     * True ordinal rank (1 = best) for EVERY participant passed in, based purely on
     * progress — team duels rank by team average, chama battles by chama average,
     * everything else (broadcast/FFA) by individual progress. Ignores win-requirement
     * eligibility (that's a separate concern handled by settle()'s $eligible filter —
     * this is just "where do you currently stand"), so it's safe to reuse for a plain
     * leaderboard snapshot on challenges that haven't settled yet.
     *
     * @return array<int,int> participant_id => rank
     */
    public function rankParticipants(Challenge $challenge, Collection $participants): array
    {
        if ($challenge->is_chama_battle) {
            return $this->rankByGroup($participants, 'chama_id');
        }
        if ($challenge->isDuel() && $challenge->is_team_based) {
            return $this->rankByGroup($participants, 'team_id');
        }

        $rankMap = [];
        foreach ($participants->sortByDesc('progress')->values() as $i => $p) {
            $rankMap[$p->id] = $i + 1;
        }
        return $rankMap;
    }

    /** Rank participants by the AVERAGE progress of whichever group key they belong to (team_id/chama_id). */
    private function rankByGroup(Collection $participants, string $groupKey): array
    {
        $order = $participants->groupBy($groupKey)->map(fn ($grp) => $grp->avg('progress'))->sortDesc()->keys()->values();

        $rankOfGroup = [];
        foreach ($order as $i => $key) {
            $rankOfGroup[$key] = $i + 1;
        }

        return $participants->mapWithKeys(fn ($p) => [$p->id => $rankOfGroup[$p->{$groupKey}] ?? (count($rankOfGroup) + 1)])->all();
    }

    /**
     * Approximate KES payout the eventual winner(s) will split, based on the
     * CURRENT paid participant count — shown on the challenge page before
     * settlement so players know what's at stake, not just the entry fee.
     * Mirrors settle()'s pool/payout math but estimates the winning group's
     * size instead of using the (not yet known) real winners.
     */
    public function estimatedWinnerPayout(Challenge $challenge): int
    {
        if (!$challenge->stake_amount) return 0;

        $paid = $challenge->participants()->where('stake_paid', true)->get();
        if ($paid->isEmpty()) return 0;

        $pool   = (int) $challenge->stake_amount * $paid->count();
        $payout = (int) round($pool * self::WINNER_POOL_PCT / 100);

        $groupKey = $challenge->is_chama_battle ? 'chama_id' : (($challenge->isDuel() && $challenge->is_team_based) ? 'team_id' : null);
        if ($groupKey === null) {
            // Individual ranking — normally a single winner takes the payout (ties split it).
            return $payout;
        }

        $groups       = $paid->groupBy($groupKey)->filter(fn ($g) => $g->count() > 0);
        $avgGroupSize = $groups->isEmpty() ? 1 : max(1, (int) round($groups->avg(fn ($g) => $g->count())));

        return intdiv($payout, $avgGroupSize);
    }

    public function settle(Challenge $challenge): void
    {
        if ($challenge->status !== 'active') return;

        $participants = $challenge->participants()->where('status', 'accepted')->with('user.progress')->get();
        if ($participants->isEmpty()) {
            $challenge->update(['status' => 'completed']);
            return;
        }

        // Win restrictions (e.g. "all bills paid", "2+ assets") gate who is even
        // eligible to be crowned — a fast-growing participant who let it all
        // fall apart during the window shouldn't win just for having the number.
        $requirements = $challenge->requirements ?? [];
        $eligibleIds  = empty($requirements)
            ? $participants->pluck('id')->all()
            : $participants->filter(fn ($p) => $p->user && app(ChallengeRequirementChecker::class)->passes($p->user, $requirements))->pluck('id')->all();
        $eligible = $participants->whereIn('id', $eligibleIds);

        // True ordinal rank for everyone (team/chama average or individual progress) —
        // shared with the daily snapshot command so challenge leaderboards can show
        // the same green ↑ / red ↓ / gold — trend arrows as the main leaderboard.
        $rankMap = $this->rankParticipants($challenge, $participants);

        if ($challenge->is_chama_battle) {
            // Winner by AVERAGE progress per chama (not sum) so team size never decides it —
            // same fairness principle as the team-duel branch below, keyed by chama_id.
            $byChama = $eligible->groupBy('chama_id')->map(fn ($grp) => $grp->avg('progress'));
            $winningChama = $byChama->isEmpty() ? null : $byChama->sort()->keys()->last();
            foreach ($participants as $p) {
                $p->update([
                    'is_winner' => $winningChama !== null && $p->chama_id === $winningChama,
                    'rank'      => $rankMap[$p->id] ?? null,
                ]);
            }
        } elseif ($challenge->isDuel() && $challenge->is_team_based) {
            $byTeam = $eligible->groupBy('team_id')->map(fn ($grp) => $grp->avg('progress'));
            $winningTeam = $byTeam->isEmpty() ? null : $byTeam->sort()->keys()->last();
            foreach ($participants as $p) {
                $p->update([
                    'is_winner' => $winningTeam !== null && $p->team_id === $winningTeam,
                    'rank'      => $rankMap[$p->id] ?? null,
                ]);
            }
        } else {
            $topProg = $eligible->sortByDesc('progress')->first()?->progress;
            foreach ($participants as $p) {
                $p->update([
                    'rank'      => $rankMap[$p->id] ?? null,
                    'is_winner' => $eligible->contains('id', $p->id) && $topProg !== null && (float) $p->progress === (float) $topProg,
                ]);
            }
        }

        $winners   = ChallengeParticipant::where('challenge_id', $challenge->id)->where('is_winner', true)->with('user.progress')->get();

        // Nobody met the win requirements — refund any stakes rather than let
        // them vanish, since there's no winner left to award the pool to.
        if ($winners->isEmpty() && !empty($requirements) && $challenge->stake_amount) {
            foreach ($participants->where('stake_paid', true) as $p) {
                $prog = $p->user->getOrCreateProgress();
                $prog->balance = ($prog->balance ?? 0) + $challenge->stake_amount;
                $prog->save();
                $p->update(['stake_paid' => false]);
            }
        }

        $paidCount = $participants->where('stake_paid', true)->count();
        $pool      = (int) $challenge->stake_amount * $paidCount;
        $payout    = (int) round($pool * self::WINNER_POOL_PCT / 100);
        $share     = $winners->count() > 0 ? intdiv($payout, $winners->count()) : 0;

        foreach ($participants as $p) {
            $isWinner = $winners->contains('id', $p->id);
            $prog     = $p->user->getOrCreateProgress();
            $prog->points_total = ($prog->points_total ?? 0) + ($isWinner ? self::WINNER_XP : self::PARTICIPANT_XP);
            if ($isWinner && $share > 0) {
                $prog->balance = ($prog->balance ?? 0) + $share;
            }
            $prog->level = $prog->calculateLevel();
            $prog->save();

            GameNotification::create([
                'user_id' => $p->user_id,
                'type'    => 'challenge_result',
                'title'   => $isWinner ? "🏆 You won — {$challenge->title}" : "Challenge over — {$challenge->title}",
                'body'    => $isWinner
                    ? ('+' . self::WINNER_XP . ' XP' . ($share > 0 ? ", +KES " . number_format($share) . ' prize' : '') . '. New trophy in your Trophy Case!')
                    : ('+' . self::PARTICIPANT_XP . " XP for competing. Final progress: " . $p->progress . $challenge->styleSuffix()),
                'icon'    => $isWinner ? '🏆' : '🎗️',
                'data'    => ['challenge_id' => $challenge->id, 'is_winner' => $isWinner],
            ]);
        }

        $challenge->update(['status' => 'completed']);
    }

    private function defaultGoal(ChallengeTemplate $template): float
    {
        return match ($template->style) {
            'percent' => 10.0,
            'count'   => 3.0,
            default   => 500.0,
        };
    }

    /** baseline + baseline_2 (null if the challenge has no second metric) for a freshly-joining participant. */
    private function baselineFields(Challenge $challenge, User $user, \App\Models\UserProgress $progress, $now): array
    {
        $fields = ['baseline' => GameMetrics::current($challenge->metric, $user, $progress, $now)];
        if ($challenge->hasSecondMetric()) {
            $fields['baseline_2'] = GameMetrics::current($challenge->metric_2, $user, $progress, $now);
        }
        return $fields;
    }

    /** Baseline+delta progress value for one metric, applying the same percent-floor rule refresh() uses for the primary metric. */
    private function progressValue(string $metric, string $style, int $baseline, User $user, \App\Models\UserProgress $progress): float
    {
        $current = GameMetrics::current($metric, $user, $progress, null);
        $delta   = $current - $baseline;

        if ($style === 'percent') {
            $floor = GameMetrics::PERCENT_FLOOR[$metric] ?? 100;
            $denom = max($baseline, $floor);
            return round(($delta / $denom) * 100, 2);
        }

        return $delta;
    }
}
