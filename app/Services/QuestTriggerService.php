<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\UserQuest;
use App\Models\GameNotification;
use Illuminate\Support\Facades\Schema;

/**
 * Centralised quest auto-completion engine.
 *
 * Call QuestTriggerService::fire() from anywhere in the codebase after a
 * game action takes place.  It finds every active quest whose trigger_type
 * matches the action, checks the trigger_value threshold/condition, awards
 * XP + Ksh, and stores completion data in the session so the world view
 * can show the celebration overlay.
 *
 * Usage:
 *   app(QuestTriggerService::class)->fire($user, 'buy_item_category', ['category' => 'electronics']);
 *   app(QuestTriggerService::class)->fire($user, 'open_savings');
 *   app(QuestTriggerService::class)->fire($user, 'reach_savings',    ['amount' => $scheme->current_amount]);
 */
class QuestTriggerService
{
    /** Guards the complete_quest cascade (quest A completes B completes C…). */
    private static int $chainDepth = 0;

    /**
     * Fire a trigger event and auto-complete matching quests.
     *
     * @param  \App\Models\User  $user
     * @param  string            $triggerType  — e.g. 'buy_item_category'
     * @param  array             $context      — additional data (category, slug, amount, level…)
     * @return array             Array of completed quest data objects (empty if none matched)
     */
    public function fire($user, string $triggerType, array $context = []): array
    {
        if (!Schema::hasTable('quests') || !Schema::hasTable('user_quests')) {
            return [];
        }

        $progress = $user->getOrCreateProgress();
        $level    = $progress->level ?? 1;
        $levelCol = Schema::hasColumn('quests', 'level_required') ? 'level_required' : 'sort_order';

        // Find all active quests the player qualifies for by level.
        // We check BOTH the new triggers JSON column and the legacy single trigger columns.
        $hasTriggerCol    = Schema::hasColumn('quests', 'trigger_type');
        $hasTriggersCol   = Schema::hasColumn('quests', 'triggers');
        $hasTriggerMode   = Schema::hasColumn('quests', 'trigger_mode');
        $hasStepProgress  = Schema::hasColumn('user_quests', 'step_progress');

        // Broad candidate set: quests at/below player level with any trigger configured
        $query = Quest::where('is_active', true)->where($levelCol, '<=', $level);

        if (Schema::hasColumn('quests', 'career_fields')) {
            $query->forCareerField($progress->career_field ?? null);
        }

        if ($hasTriggersCol) {
            // Quest matches if it has the trigger in its JSON triggers array OR in legacy column
            $query->where(function ($q) use ($triggerType, $hasTriggerCol) {
                $q->whereRaw("JSON_SEARCH(`triggers`, 'one', ?) IS NOT NULL", [$triggerType]);
                if ($hasTriggerCol) {
                    $q->orWhere('trigger_type', $triggerType);
                }
            });
        } elseif ($hasTriggerCol) {
            $query->where('trigger_type', $triggerType);
        } else {
            return [];
        }

        $candidates = $query->get();
        if ($candidates->isEmpty()) return [];

        // Already-completed quest IDs (skip them)
        $completedIds = UserQuest::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('quest_id')
            ->flip();

        $completed  = [];
        $stepFired  = [];   // step-fired but not yet complete

        foreach ($candidates as $quest) {
            if (isset($completedIds[$quest->id])) continue;

            $triggers = $quest->triggers ?? [];

            // ── MULTI-TRIGGER QUEST ─────────────────────────────────────
            if ($hasTriggersCol && !empty($triggers) && count($triggers) > 1) {

                // Find which step(s) in the triggers array match the fired event
                $matchedStepIdx = null;
                foreach ($triggers as $idx => $t) {
                    if (($t['type'] ?? '') !== $triggerType) continue;
                    $values = $t['values'] ?? [];
                    $matches = empty($values);
                    if (!$matches) {
                        foreach ($values as $v) {
                            if ($this->_valueMatches($triggerType, $v, $context)) { $matches = true; break; }
                        }
                    }
                    if ($matches) { $matchedStepIdx = $idx; break; }
                }

                if ($matchedStepIdx === null) continue; // no matching step

                // Get or create user_quest row
                $uq = UserQuest::firstOrCreate(
                    ['user_id' => $user->id, 'quest_id' => $quest->id],
                    ['submitted_at' => now()]
                );

                if ($uq->completed_at) continue;

                // Load step_progress
                $stepProgress = [];
                if ($hasStepProgress) {
                    $raw = $uq->getRawOriginal('step_progress');
                    if ($raw) {
                        $stepProgress = is_array($raw) ? $raw : json_decode($raw, true) ?? [];
                    }
                }

                // If this step already fired, skip
                if (!empty($stepProgress[$matchedStepIdx])) continue;

                // Mark step as done
                $stepProgress[$matchedStepIdx] = true;

                // Chain steps ('complete_quest') the player satisfied BEFORE this
                // quest existed count as done — blueprints print ladders after the
                // fact, so history must honour them.
                foreach ($triggers as $idx => $t) {
                    if ($idx === $matchedStepIdx || ($t['type'] ?? '') !== 'complete_quest') continue;
                    if (!empty($stepProgress[$idx]) || empty($t['values'])) continue;
                    $prereqDone = UserQuest::where('user_id', $user->id)
                        ->whereIn('quest_id', $t['values'])
                        ->whereNotNull('completed_at')
                        ->exists();
                    if ($prereqDone) $stepProgress[$idx] = true;
                }

                // Check completion condition
                $triggerMode  = $hasTriggerMode ? ($quest->trigger_mode ?? 'all') : 'all';
                $totalSteps   = count($triggers);
                $stepsNowDone = count(array_filter($stepProgress));
                $questDone    = ($triggerMode === 'any') || ($stepsNowDone >= $totalSteps);

                if ($hasStepProgress) {
                    $uq->step_progress = $stepProgress;
                }

                if ($questDone) {
                    if (!$uq->submitted_at) $uq->submitted_at = now();
                    $uq->completed_at = now();
                }
                $uq->save();

                // Build step label
                $stepLabel = $triggers[$matchedStepIdx]['label'] ?? ($triggers[$matchedStepIdx]['type'] ?? 'Step complete');

                if ($questDone) {
                    // High mood (> 80) boosts quest XP by 10%
                    $moodBoost = (($progress->mood ?? 70) > 80) ? 1.1 : 1.0;
                    $scaledXp = (int) ($quest->xp_reward * (1 + ($level - 1) * 0.1) * $moodBoost);
                    $progress->points_total = ($progress->points_total ?? 0) + $scaledXp;

                    $kesReward = (int) ($quest->kes_reward ?? 0);
                    if ($kesReward > 0) {
                        $progress->balance = ($progress->balance ?? 0) + $kesReward;
                    }

                    if (Schema::hasTable('game_notifications')) {
                        GameNotification::create([
                            'user_id' => $user->id,
                            'type'    => 'quest_completed',
                            'title'   => "{$quest->icon} Quest Complete: {$quest->title}",
                            'body'    => "+{$scaledXp} XP" . ($kesReward > 0 ? " · +Ksh " . number_format($kesReward) : ''),
                            'icon'    => $quest->icon ?? '📜',
                            'data'    => ['quest_id' => $quest->id, 'xp' => $scaledXp, 'kes' => $kesReward],
                        ]);
                    }

                    $completed[] = [
                        'quest_id'    => $quest->id,
                        'id'          => $quest->id,
                        'title'       => $quest->title,
                        'icon'        => $quest->icon ?? '📜',
                        'xp_earned'   => $scaledXp,
                        'kes_earned'  => $kesReward,
                        'lesson'      => $quest->lesson ?? $quest->description ?? '',
                        'completed'   => true,
                        'step_fired'  => $matchedStepIdx,
                        'step_label'  => $stepLabel,
                        'steps_total' => $totalSteps,
                        'steps_done'  => $stepsNowDone,
                    ];
                } else {
                    // Step fired but quest not yet complete
                    $stepFired[] = [
                        'quest_id'    => $quest->id,
                        'id'          => $quest->id,
                        'title'       => $quest->title,
                        'icon'        => $quest->icon ?? '📜',
                        'xp_earned'   => 0,
                        'kes_earned'  => 0,
                        'lesson'      => '',
                        'completed'   => false,
                        'step_fired'  => $matchedStepIdx,
                        'step_label'  => $stepLabel,
                        'steps_total' => $totalSteps,
                        'steps_done'  => $stepsNowDone,
                    ];
                }

            // ── SINGLE-TRIGGER QUEST (existing logic) ─────────────────
            } else {

                // Check if any trigger in the triggers JSON matches — or fall back to legacy columns
                if (!$this->_questMatchesTrigger($quest, $triggerType, $context)) {
                    continue;
                }

                // Auto-start + complete
                $uq = UserQuest::firstOrCreate(
                    ['user_id' => $user->id, 'quest_id' => $quest->id],
                    ['submitted_at' => now()]
                );

                if ($uq->completed_at) continue;

                if (!$uq->submitted_at) {
                    $uq->submitted_at = now();
                }
                $uq->completed_at = now();
                $uq->save();

                // Award XP (scaled by level; high mood > 80 adds +10%)
                $moodBoost          = (($progress->mood ?? 70) > 80) ? 1.1 : 1.0;
                $scaledXp           = (int) ($quest->xp_reward * (1 + ($level - 1) * 0.1) * $moodBoost);
                $progress->points_total = ($progress->points_total ?? 0) + $scaledXp;

                // Award Ksh if any
                $kesReward = (int) ($quest->kes_reward ?? 0);
                if ($kesReward > 0) {
                    $progress->balance = ($progress->balance ?? 0) + $kesReward;
                }

                // In-game notification
                if (Schema::hasTable('game_notifications')) {
                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'quest_completed',
                        'title'   => "{$quest->icon} Quest Complete: {$quest->title}",
                        'body'    => "+{$scaledXp} XP" . ($kesReward > 0 ? " · +Ksh " . number_format($kesReward) : ''),
                        'icon'    => $quest->icon ?? '📜',
                        'data'    => ['quest_id' => $quest->id, 'xp' => $scaledXp, 'kes' => $kesReward],
                    ]);
                }

                $completed[] = [
                    'quest_id'    => $quest->id,
                    'id'          => $quest->id,
                    'title'       => $quest->title,
                    'icon'        => $quest->icon ?? '📜',
                    'xp_earned'   => $scaledXp,
                    'kes_earned'  => $kesReward,
                    'lesson'      => $quest->lesson ?? $quest->description ?? '',
                    'completed'   => true,
                    'step_fired'  => null,
                    'step_label'  => null,
                    'steps_total' => 1,
                    'steps_done'  => 1,
                ];
            }
        }

        if (!empty($completed)) {
            // Completions may have opened the Quest Gate — recompute fresh
            QuestGate::forget($user->id);
            $progress->level = $progress->calculateLevel();
            $progress->save();
            foreach ($completed as $item) {
                session()->push('pending_quest_completions', $item);
            }
        }

        // Step-fired events (not complete yet) stored in session for JS flash
        if (!empty($stepFired)) {
            foreach ($stepFired as $item) {
                session()->push('pending_step_fires', $item);
            }
        }

        // Cascade: completing a quest is itself a trigger event — chained
        // quests (blueprint ladders) advance the moment their predecessor
        // lands. Depth-guarded so a mis-authored loop can't recurse forever.
        $cascaded = [];
        if (!empty($completed) && self::$chainDepth < 3) {
            self::$chainDepth++;
            try {
                foreach ($completed as $item) {
                    $cascaded = array_merge(
                        $cascaded,
                        $this->fire($user, 'complete_quest', ['quest_id' => $item['quest_id']])
                    );
                }
            } finally {
                self::$chainDepth--;
            }
        }

        return array_merge($completed, $stepFired, $cascaded);
    }

    // ── Match a quest against the fired trigger ──────────────────────────────

    private function _questMatchesTrigger(Quest $quest, string $triggerType, array $ctx): bool
    {
        // Try new triggers JSON array first
        $triggers = $quest->triggers ?? [];
        if (!empty($triggers)) {
            foreach ($triggers as $t) {
                if (($t['type'] ?? '') !== $triggerType) continue;
                $values = $t['values'] ?? [];
                // Empty values array = matches any value of that type
                if (empty($values)) {
                    return true;
                }
                // Check if any of the configured values matches the context
                foreach ($values as $v) {
                    if ($this->_valueMatches($triggerType, $v, $ctx)) return true;
                }
            }
            return false;
        }

        // Fall back to legacy single trigger columns
        return $this->_valueMatches($triggerType, $quest->trigger_value, $ctx);
    }

    // ── Trigger value matching logic ─────────────────────────────────────────

    private function _valueMatches(string $type, ?string $questValue, array $ctx): bool
    {
        // No value set on quest = matches anything of that type
        if ($questValue === null || $questValue === '') {
            return true;
        }

        return match ($type) {
            // Exact category or slug match
            'buy_item_category' => isset($ctx['category']) && strtolower($ctx['category']) === strtolower($questValue),
            'buy_item_slug'     => isset($ctx['slug'])     && strtolower($ctx['slug'])     === strtolower($questValue),

            // Threshold checks (numeric)
            'reach_balance',
            'reach_savings',
            'reach_net_worth',
            'deposit_savings'   => isset($ctx['amount'])   && (float) $ctx['amount'] >= (float) $questValue,

            // Exact match or empty = any
            'take_course'       => isset($ctx['slug']) && strtolower($ctx['slug']) === strtolower($questValue),
            // get_job value is job ID (string)
            'get_job'           => (isset($ctx['id'])   && (string)$ctx['id']   === (string)$questValue)
                                || (isset($ctx['slug']) && strtolower($ctx['slug']) === strtolower($questValue)),
            'earn_badge'        => isset($ctx['slug']) && strtolower($ctx['slug']) === strtolower($questValue),
            'play_scenario'     => isset($ctx['slug']) && strtolower($ctx['slug']) === strtolower($questValue),

            // Chain link: fired when another quest completes (value = its id)
            'complete_quest'    => isset($ctx['quest_id']) && (string) $ctx['quest_id'] === (string) $questValue,

            // Level threshold
            'reach_level'       => isset($ctx['level']) && (int) $ctx['level'] >= (int) $questValue,

            // These always fire (no value needed)
            'open_savings',
            'join_chama',
            'spin_wheel'        => true,

            default             => true,
        };
    }
}
