<?php

namespace App\Services;

use App\Models\ContractRule;
use App\Models\GameNotification;
use App\Models\PlayerContract;
use App\Models\PlayerContractObjective;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Personal contracts — the self-generating quest layer.
 *
 * Contracts are assembled from the player's OWN state: which archetypes are
 * relevant right now (overdue bills? low mood? uncollected pay?), with targets
 * sized as ratios of the player's own numbers, so difficulty self-tunes and
 * age/level appropriateness is inherited rather than authored. Copy comes
 * from the NPC voice packs (config/pesa_voice.php) so every contract reads
 * like an errand from a character, never like "trigger: course_complete".
 *
 * Progress is measured by BASELINE + DELTA: each objective snapshots its
 * metric at issue time and completion is a pure state diff on refresh —
 * no event hooks, nothing to desync, works retroactively for offline play.
 */
class ContractService
{
    /** metric key => [style, evaluator] — style: count|amount|absolute|clear */
    public const METRIC_STYLES = [
        'courses_completed'   => 'count',
        'jobs_started'        => 'count',
        'gigs_completed'      => 'count',
        'paydays_collected'   => 'count',
        'assets_owned'        => 'count',
        'chama_contributions' => 'count',
        'friends_count'       => 'count',
        'forum_posts'         => 'count',
        'bills_paid'          => 'count',
        'savings_balance'     => 'amount',
        'wallet_balance'      => 'amount',
        'net_worth'           => 'amount',
        'xp_points'           => 'amount',
        'mood_level'          => 'absolute',
        'overdue_cleared'     => 'clear',
    ];

    /** Refresh, settle and top-up a player's contracts. Returns active ones for display. */
    public function refresh(User $user): Collection
    {
        if (!Schema::hasTable('player_contracts') || !Schema::hasTable('contract_rules')) {
            return collect();
        }

        $reqKey = 'contracts_refreshed_' . $user->id;
        if (request()->attributes->has($reqKey)) {
            return request()->attributes->get($reqKey);
        }

        $progress = $user->getOrCreateProgress();
        $tick     = (int) ($progress->tick_count ?? 0);
        $level    = (int) ($progress->level ?? 1);

        $active = PlayerContract::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('objectives')
            ->get();

        foreach ($active as $contract) {
            $this->settle($user, $progress, $contract, $tick);
        }
        $active = $active->where('status', 'active');

        // Top up to the rule's contract count
        $rule = ContractRule::for($user->age_group, $level);
        if ($rule) {
            $needed = max(0, (int) $rule->active_contracts - $active->count());
            for ($i = 0; $i < $needed; $i++) {
                $new = $this->generate($user, $progress, $rule, $tick, $active);
                if (!$new) break;
                $active->push($new);
            }
        }

        $result = $active->sortBy('expires_at_tick')->values();
        request()->attributes->set($reqKey, $result);

        return $result;
    }

    // ── Settlement ─────────────────────────────────────────────────────────

    private function settle(User $user, UserProgress $progress, PlayerContract $contract, int $tick): void
    {
        $changed = false;

        foreach ($contract->objectives as $obj) {
            if ($obj->is_complete) continue;

            $current = $this->measure($obj->metric, $user, $progress, $contract);

            [$progressVal, $done] = match ($obj->style) {
                'amount', 'count' => [max(0, $current - (int) $obj->baseline), max(0, $current - (int) $obj->baseline) >= (int) $obj->goal],
                'absolute'        => [$current, $current >= (int) $obj->goal],
                'clear'           => [max(0, min((int) $obj->goal, (int) $obj->baseline - $current)), $current <= 0],
                default           => [0, false],
            };

            if ($progressVal !== (int) $obj->progress || $done) {
                $obj->progress    = $progressVal;
                $obj->is_complete = $done;
                $obj->save();
                $changed = true;
            }
        }

        if ($changed) $contract->load('objectives');

        if ($contract->isSatisfied()) {
            $this->award($user, $progress, $contract);
            return;
        }

        if ($tick >= (int) $contract->expires_at_tick) {
            $contract->update(['status' => 'expired']);
        }
    }

    private function award(User $user, UserProgress $progress, PlayerContract $contract): void
    {
        $contract->update(['status' => 'completed', 'completed_at' => now()]);

        $progress->points_total = (int) $progress->points_total + (int) $contract->reward_xp;
        $progress->balance      = (int) $progress->balance + (int) $contract->reward_kes;
        if (method_exists($progress, 'calculateLevel')) {
            $progress->level = $progress->calculateLevel();
        }
        $progress->save();

        $npc    = $contract->npc();
        $lesson = $contract->objectives->pluck('lesson')->filter()->first();

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'contract_completed',
            'title'   => ($npc['emoji'] ?? '🎯') . ' Contract complete — ' . $contract->title,
            'body'    => trim(($contract->signoff ?: 'Well done!') . ' +'
                . number_format($contract->reward_xp) . ' XP'
                . ($contract->reward_kes > 0 ? ', +Ksh ' . number_format($contract->reward_kes) : '')
                . ($lesson ? ' 💡 ' . $lesson : '')),
            'icon'    => $npc['emoji'] ?? '🎯',
            'data'    => ['contract_id' => $contract->id, 'xp' => $contract->reward_xp, 'kes' => $contract->reward_kes],
        ]);
    }

    // ── Generation ─────────────────────────────────────────────────────────

    private function generate(User $user, UserProgress $progress, ContractRule $rule, int $tick, Collection $existing): ?PlayerContract
    {
        $level = (int) ($progress->level ?? 1);

        // Archetypes already in play stay out of new contracts — variety guaranteed
        $inPlay = $existing->flatMap(fn ($c) => $c->objectives->pluck('archetype'))->unique()->all();

        $candidates = $this->eligibleArchetypes($user, $progress);
        $pool       = array_values(array_diff($candidates, $inPlay));
        if (count($pool) < 2) $pool = $candidates; // small worlds still get contracts

        $count = random_int((int) $rule->objectives_min, max((int) $rule->objectives_min, (int) $rule->objectives_max));
        if (count($pool) < max(2, $count)) $count = count($pool);
        if ($count < 2) return null; // not enough game surface yet — try again another day

        // Urgent problems always make the cut; the rest is randomized
        $picked = [];
        foreach (['clean_slate', 'payday_pro'] as $urgent) {
            if (in_array($urgent, $pool, true) && count($picked) < $count) $picked[] = $urgent;
        }
        $rest = array_values(array_diff($pool, $picked));
        shuffle($rest);
        $picked = array_merge($picked, array_slice($rest, 0, $count - count($picked)));
        shuffle($picked); // urgent-first selection, random display order — keeps it non-obvious

        // The first archetype fronts the contract (title/NPC/pitch); all provide objectives
        $band     = $user->age_group;
        $firstKey = $picked[0];
        $vars     = $this->varsFor($firstKey, $user, $progress, $level);
        $copy     = PesaVoice::compose($firstKey, $band, $vars + ['name' => strtok($user->name, ' ')]);
        if (!$copy) return null;

        $mode     = $rule->completion_mode;
        $required = $mode === 'all' ? count($picked) : min((int) $rule->required_count, count($picked));

        $contract = PlayerContract::create([
            'user_id'         => $user->id,
            'npc_key'         => $copy['npc_key'],
            'icon'            => $copy['icon'],
            'title'           => $copy['title'],
            'intro'           => $copy['intro'],
            'signoff'         => $copy['signoff'],
            'completion_mode' => $mode,
            'required_count'  => $required,
            'status'          => 'active',
            'issued_at_tick'  => $tick,
            'expires_at_tick' => $tick + (int) $rule->duration_days,
            'reward_xp'       => (int) $rule->reward_xp,
            'reward_kes'      => (int) $rule->reward_kes,
        ]);

        foreach ($picked as $key) {
            $arch   = PesaVoice::archetype($key);
            $metric = $arch['metric'];
            $vars   = $this->varsFor($key, $user, $progress, $level);
            $goal   = $this->goalFor($key, $vars);

            PlayerContractObjective::create([
                'contract_id' => $contract->id,
                'archetype'   => $key,
                'metric'      => $metric,
                'style'       => self::METRIC_STYLES[$metric] ?? 'count',
                'label'       => PesaVoice::objectiveLabel($key, $vars),
                'icon'        => $arch['icon'] ?? '✅',
                'lesson'      => PesaVoice::fill(($arch['lessons'][PesaVoice::band($band)] ?? reset($arch['lessons']))[0] ?? '', $vars),
                'baseline'    => $this->measure($metric, $user, $progress, $contract),
                'goal'        => $goal,
            ]);
        }

        return $contract->load('objectives');
    }

    /** Archetypes that make sense for this player RIGHT NOW. */
    private function eligibleArchetypes(User $user, UserProgress $progress): array
    {
        $level = (int) ($progress->level ?? 1);
        $out   = ['balance_builder', 'level_head'];

        if (Schema::hasTable('city_courses') && \App\Models\CityCourse::where('is_active', true)->exists()) {
            $out[] = 'study_up';
        }
        if (Schema::hasTable('city_jobs') && \App\Models\CityJob::where('is_active', true)->exists()) {
            $out[] = 'get_hired';
            if (\App\Models\CityJob::where('is_active', true)->where('employment_type', 'freelance')->exists() && $level >= 2) {
                $out[] = 'hustle_harder';
            }
        }
        if (Schema::hasTable('savings_schemes')) $out[] = 'stash_it';

        if (Schema::hasTable('player_bills')) {
            if (\App\Models\PlayerBill::where('user_id', $user->id)->where('status', 'active')->exists())  $out[] = 'bill_boss';
            if (\App\Models\PlayerBill::where('user_id', $user->id)->where('status', 'overdue')->exists()) $out[] = 'clean_slate';
        }

        // Payday contract only when collectible within the window (monthly pay is a 30-tick cycle)
        if (Schema::hasTable('player_city_jobs') && Schema::hasColumn('player_city_jobs', 'pending_salary')) {
            $collectible = \App\Models\PlayerCityJob::where('user_id', $user->id)
                ->where(fn ($q) => $q->where('pending_salary', '>', 0)
                    ->orWhere(fn ($q2) => $q2->where('status', 'employed')->whereRaw('(unpaid_ticks % 30) >= 23')))
                ->exists();
            if ($collectible) $out[] = 'payday_pro';
        }

        if ($level >= 2) $out[] = 'first_brick';
        if ((int) ($progress->net_worth_cache ?? 0) > 500) $out[] = 'worth_climb';

        if (Schema::hasTable('chama_members') &&
            \App\Models\ChamaMember::where('user_id', $user->id)->where('is_active', true)->exists()) {
            $out[] = 'circle_up';
        }
        if (Schema::hasTable('friendships')) $out[] = 'squad_up';
        if (Schema::hasTable('forum_topics') && $level >= 2) $out[] = 'street_voice';
        if ((int) ($progress->mood ?? 70) < 70) $out[] = 'good_vibes';

        return array_values(array_unique($out));
    }

    /** Copy/goal variables — targets are ratios of the player's own numbers (self-tuning difficulty). */
    private function varsFor(string $archetype, User $user, UserProgress $progress, int $level): array
    {
        $balance  = max(0, (int) ($progress->balance ?? 0));
        $netWorth = max(0, (int) ($progress->net_worth_cache ?? 0));
        $scale    = 1 + min(2.0, $level * 0.1);

        return match ($archetype) {
            'study_up'        => ['n' => $level >= 5 ? random_int(1, 2) : 1],
            'get_hired'       => ['n' => 1],
            'hustle_harder'   => ['n' => $level >= 4 ? random_int(1, 2) : 1],
            'stash_it'        => ['amount' => $this->round50(max(200, (int) ($balance * 0.10 * $scale)))],
            'balance_builder' => ['amount' => $this->round50(max(300, (int) ($balance * 0.15 * $scale)))],
            'bill_boss'       => ['n' => min(3, max(1, \App\Models\PlayerBill::where('user_id', $user->id)->where('status', 'active')->count()))],
            'clean_slate'     => ['n' => max(1, \App\Models\PlayerBill::where('user_id', $user->id)->where('status', 'overdue')->count())],
            'payday_pro'      => ['n' => 1],
            'first_brick'     => ['n' => 1],
            'circle_up'       => ['n' => 1],
            'worth_climb'     => ['amount' => $this->round50(max(500, (int) ($netWorth * 0.07 * $scale)))],
            'level_head'      => ['amount' => 100 + $level * 50],
            'squad_up'        => ['n' => 1],
            'street_voice'    => ['n' => 2],
            'good_vibes'      => ['amount' => min(90, (int) ($progress->mood ?? 50) + 15)],
            default           => ['n' => 1],
        };
    }

    private function goalFor(string $archetype, array $vars): int
    {
        return (int) ($vars['amount'] ?? $vars['n'] ?? 1);
    }

    /** Current absolute value of a metric — the single source of truth for progress. */
    private function measure(string $metric, User $user, UserProgress $progress, PlayerContract $contract): int
    {
        $since = $contract->created_at ?? now();

        return (int) match ($metric) {
            'courses_completed'   => \App\Models\PlayerCityCourse::where('user_id', $user->id)->where('status', 'completed')->count(),
            'jobs_started'        => \App\Models\PlayerCityJob::where('user_id', $user->id)->count(),
            'gigs_completed'      => \App\Models\PlayerCityJob::where('user_id', $user->id)->where('employment_type', 'freelance')->where('status', 'completed')->count(),
            // Counted from contract start (baseline stays 0) — the bell purges
            // rows older than 10 days, which would corrupt an all-time baseline
            'paydays_collected'   => GameNotification::where('user_id', $user->id)->where('type', 'salary')->where('created_at', '>=', $since)->count(),
            'bills_paid'          => GameNotification::where('user_id', $user->id)->where('type', 'bill_paid')->where('created_at', '>=', $since)->count(),
            'assets_owned'        => \App\Models\PlayerAsset::where('user_id', $user->id)->count(),
            'chama_contributions' => Schema::hasTable('chama_contributions')
                ? \App\Models\ChamaContribution::where('user_id', $user->id)->where('status', 'paid')->count() : 0,
            'friends_count'       => count($user->friendIds()),
            'forum_posts'         => \App\Models\ForumTopic::where('user_id', $user->id)->count()
                                   + \App\Models\ForumReply::where('user_id', $user->id)->count(),
            'savings_balance'     => Schema::hasTable('savings_schemes')
                ? \App\Models\SavingsScheme::where('user_id', $user->id)->sum('current_amount') : 0,
            'wallet_balance'      => (int) ($progress->balance ?? 0),
            'net_worth'           => (int) ($progress->net_worth_cache ?? 0),
            'xp_points'           => (int) ($progress->points_total ?? 0),
            'mood_level'          => (int) ($progress->mood ?? 0),
            'overdue_cleared'     => \App\Models\PlayerBill::where('user_id', $user->id)->where('status', 'overdue')->count(),
            default               => 0,
        };
    }

    private function round50(int $n): int
    {
        return (int) (round($n / 50) * 50);
    }
}
