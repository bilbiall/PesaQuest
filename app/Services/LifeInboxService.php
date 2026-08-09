<?php

namespace App\Services;

use App\Models\LifeDecision;
use App\Models\LifeDecisionChoice;
use App\Models\PlayerDecision;
use App\Models\PlayerNpcRelationship;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LifeInboxService
{
    private const INBOX_SIZE = 4; // how many pending decisions to keep queued

    public function getPendingDecisions(User $user): Collection
    {
        $this->refillInbox($user);

        return PlayerDecision::where('user_id', $user->id)
            ->whereNull('choice_id')
            ->with(['decision.choices', 'decision.npc'])
            ->orderBy('created_at')
            ->get();
    }

    public function getRecentResolved(User $user, int $limit = 3): Collection
    {
        return PlayerDecision::where('user_id', $user->id)
            ->whereNotNull('choice_id')
            ->with(['decision.npc', 'choice'])
            ->orderByDesc('resolved_at')
            ->limit($limit)
            ->get();
    }

    public function resolve(User $user, int $playerDecisionId, int $choiceId): array
    {
        $pd = PlayerDecision::where('user_id', $user->id)
            ->whereNull('choice_id')
            ->findOrFail($playerDecisionId);

        $choice = LifeDecisionChoice::where('decision_id', $pd->decision_id)->findOrFail($choiceId);
        $progress = $user->getOrCreateProgress();

        DB::transaction(function () use ($pd, $choice, $progress, $user) {
            $balanceBefore = $progress->balance;

            // Apply balance — never clamp to 0, a costly decision on an empty
            // balance must still go negative (real debt), not be discarded.
            $progress->balance += $choice->balance_delta;

            // Apply credit score
            if ($choice->credit_score_delta !== 0) {
                $progress->adjustCreditScore($choice->credit_score_delta);
            }

            // Apply XP / points
            if ($choice->xp_delta > 0) {
                $progress->points_total = ($progress->points_total ?? 0) + $choice->xp_delta;
                $progress->level = $progress->calculateLevel();
            }

            $progress->save();

            // Apply NPC relationship
            if ($pd->decision->npc_id && $choice->relationship_delta !== 0) {
                $rel = PlayerNpcRelationship::firstOrCreate(
                    ['user_id' => $user->id, 'npc_id' => $pd->decision->npc_id],
                    ['score' => $pd->decision->npc->initial_relationship ?? 50, 'total_interactions' => 0]
                );
                $rel->score = max(0, min(100, $rel->score + $choice->relationship_delta));
                $rel->total_interactions++;
                $rel->save();
            }

            // Mark resolved
            $pd->choice_id     = $choice->id;
            $pd->balance_before = $balanceBefore;
            $pd->balance_after  = $progress->balance;
            $pd->resolved_at    = now();
            $pd->save();
        });

        return [
            'outcome'          => $choice->outcome_text,
            'financial_lesson' => $choice->financial_lesson,
            'balance_delta'    => $choice->balance_delta,
            'credit_delta'     => $choice->credit_score_delta,
            'xp_delta'         => $choice->xp_delta,
            'new_balance'      => $progress->fresh()->balance,
            'badge_slug'       => $choice->badge_slug,
        ];
    }

    private function refillInbox(User $user): void
    {
        $pendingCount = PlayerDecision::where('user_id', $user->id)->whereNull('choice_id')->count();

        if ($pendingCount >= self::INBOX_SIZE) return;

        $needed  = self::INBOX_SIZE - $pendingCount;
        $progress = $user->getOrCreateProgress();

        // IDs already queued or seen (non-repeatable)
        $seenIds = PlayerDecision::where('user_id', $user->id)->pluck('decision_id')->toArray();

        $candidates = LifeDecision::where('is_active', true)
            ->where('min_tick', '<=', $progress->tick_count ?? 0)
            ->where(fn($q) => $q->whereNull('max_tick')->orWhere('max_tick', '>=', $progress->tick_count ?? 0))
            ->where(fn($q) => $q->whereNull('min_balance')->orWhere('min_balance', '<=', $progress->balance ?? 0))
            ->where(fn($q) => $q->whereNull('max_balance')->orWhere('max_balance', '>=', $progress->balance ?? 0))
            ->where(fn($q) => $q
                ->whereNull('required_career_fields')
                ->orWhereJsonContains('required_career_fields', $progress->career_field ?? '')
            )
            ->where(fn($q) => $q
                ->where('is_repeatable', true)
                ->orWhereNotIn('id', $seenIds)
            )
            ->get();

        if ($candidates->isEmpty()) {
            // Fall back: repeatable decisions or ignore seen filter
            $candidates = LifeDecision::where('is_active', true)->get();
        }

        // Weighted random selection
        $selected = $this->weightedRandom($candidates, $needed);

        foreach ($selected as $decision) {
            PlayerDecision::create([
                'user_id'       => $user->id,
                'decision_id'   => $decision->id,
                'balance_before'=> $progress->balance ?? 0,
            ]);
        }
    }

    private function weightedRandom(Collection $items, int $count): array
    {
        if ($items->isEmpty()) return [];

        $pool   = [];
        foreach ($items as $item) {
            for ($i = 0; $i < ($item->weight ?? 10); $i++) {
                $pool[] = $item;
            }
        }

        shuffle($pool);
        $picked = [];
        $seenIds = [];

        foreach ($pool as $item) {
            if (count($picked) >= $count) break;
            if (in_array($item->id, $seenIds)) continue;
            $picked[]  = $item;
            $seenIds[] = $item->id;
        }

        return $picked;
    }
}
