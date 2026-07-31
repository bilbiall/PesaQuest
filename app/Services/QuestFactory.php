<?php

namespace App\Services;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\Quest;
use App\Models\QuestBlueprint;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Quest Factory — one content row in, a playable quest out.
 *
 * Two production lines:
 *
 * 1. CONTENT LINE (draftForCourse / draftForJob) — when an admin creates a
 *    course or job in GameSet, the factory drafts its quest automatically:
 *    NPC-voiced copy from config/pesa_voice.php, targeting inherited from the
 *    content row, triggers wired to the quest engine.
 *
 * 2. BLUEPRINT LINE (sweep) — quest_blueprints are level-spanning RECIPES
 *    ("reach Ksh X savings", "take any course + deposit Y") with value curves.
 *    The sweep walks every active blueprint and prints one quest per level
 *    rung that doesn't exist yet, so the ladder is always complete without
 *    every quest needing a course behind it. chain=true threads consecutive
 *    rungs together with a complete_quest step — a generated story arc.
 *
 * Drafts land with is_active=false + source='factory'/'blueprint' and queue
 * for approval in GameSet → Quests, unless the auto-publish Setting is on.
 */
class QuestFactory
{
    public const AUTOPUBLISH_SETTING = 'quest_factory_autopublish';
    public const ENABLED_SETTING     = 'quest_factory_enabled';

    /**
     * Everything the blueprint system needs to know about each trigger type:
     * value    — how the quest value is authored: money (curve/fixed number),
     *            level (fixed number), pick (fixed slug/id or any), none
     * arch     — default pesa_voice archetype fronting the copy
     * name     — admin-facing description
     * label    — player-facing step line ({amount}/{n}/{value} filled at print)
     * anyLabel — step line when value_mode = any / none
     */
    public const TRIGGER_META = [
        'reach_savings'     => ['value' => 'money', 'arch' => 'stash_it',        'name' => 'Total savings reaches Ksh X',
                                'label' => 'Get your total savings to Ksh {amount} 🏦'],
        'deposit_savings'   => ['value' => 'money', 'arch' => 'stash_it',        'name' => 'One savings pocket reaches Ksh X',
                                'label' => 'Grow one savings pocket to Ksh {amount} 🏦'],
        'reach_balance'     => ['value' => 'money', 'arch' => 'balance_builder', 'name' => 'Wallet cash reaches Ksh X',
                                'label' => 'Grow your wallet to Ksh {amount} 💰'],
        'reach_net_worth'   => ['value' => 'money', 'arch' => 'worth_climb',     'name' => 'Net worth reaches Ksh X',
                                'label' => 'Push your net worth past Ksh {amount} 📈'],
        'reach_level'       => ['value' => 'level', 'arch' => 'level_head',      'name' => 'Reach player level N',
                                'label' => 'Reach Level {value} ⭐'],
        'take_course'       => ['value' => 'pick',  'arch' => 'study_up',        'name' => 'Complete a course',
                                'label' => 'Complete the "{value}" course 📚',   'anyLabel' => 'Complete any course at Skill Campus 📚'],
        'get_job'           => ['value' => 'pick',  'arch' => 'get_hired',       'name' => 'Get hired',
                                'label' => 'Land the {value} job 💼',            'anyLabel' => 'Get hired for any job 💼'],
        'buy_item_category' => ['value' => 'pick',  'arch' => 'first_brick',     'name' => 'Buy from a marketplace category',
                                'label' => 'Buy any {value} item 🏗️',            'anyLabel' => 'Buy anything from the Marketplace 🏗️'],
        'buy_item_slug'     => ['value' => 'pick',  'arch' => 'first_brick',     'name' => 'Buy a specific item',
                                'label' => 'Buy the {value} 🏗️'],
        'earn_badge'        => ['value' => 'pick',  'arch' => 'level_head',      'name' => 'Earn a badge',
                                'label' => 'Earn the "{value}" badge 🏅',        'anyLabel' => 'Earn any badge 🏅'],
        'open_savings'      => ['value' => 'none',  'arch' => 'open_account',    'name' => 'Open a savings account',
                                'anyLabel' => 'Open your bank savings account 🔑'],
        'join_chama'        => ['value' => 'none',  'arch' => 'circle_up',       'name' => 'Join a chama',
                                'anyLabel' => 'Join a chama 🤝'],
        'spin_wheel'        => ['value' => 'none',  'arch' => 'lucky_spin',      'name' => 'Spin the Lucky Wheel',
                                'anyLabel' => 'Spin the Lucky Wheel 🎡'],
    ];

    public static function enabled(): bool
    {
        return Schema::hasTable('quests')
            && Schema::hasColumn('quests', 'source')
            && Setting::get(self::ENABLED_SETTING, '1') === '1';
    }

    public static function autopublish(): bool
    {
        return Setting::get(self::AUTOPUBLISH_SETTING, '0') === '1';
    }

    public function draftForCourse(CityCourse $course): ?Quest
    {
        if (!self::enabled() || !$course->slug) return null;
        if ($this->alreadyCovered('take_course', $course->slug)) return null;

        $ageGroup = $course->age_group ?: 'all';
        $level    = match ($course->difficulty ?? null) {
            'intermediate' => 3,
            'advanced'     => 5,
            default        => 1,
        };

        $copy = PesaVoice::compose('study_up', $ageGroup === 'all' ? null : $ageGroup,
            ['course' => $course->title, 'n' => 1, 'name' => 'rafiki'], targeted: true);
        if (!$copy) return null;

        return $this->createDraft([
            'title'          => mb_substr($copy['title'] . ': ' . $course->title, 0, 150),
            'description'    => $copy['intro'],
            'instructions'   => $copy['label'],
            'lesson'         => trim($copy['lesson'] . ' ' . $copy['signoff']),
            'icon'           => '📚',
            'age_group'      => $ageGroup,
            'career_fields'  => $course->career_track ? [$course->career_track] : null,
            'level_required' => $level,
            'xp_reward'      => 100 + $level * 50,
            'kes_reward'     => 50 + $level * 25,
            'triggers'       => [['type' => 'take_course', 'values' => [$course->slug], 'label' => $copy['label']]],
            'trigger_type'   => 'take_course',
            'trigger_value'  => $course->slug,
            'trigger_label'  => $copy['label'],
        ]);
    }

    public function draftForJob(CityJob $job): ?Quest
    {
        if (!self::enabled()) return null;
        if ($this->alreadyCovered('get_job', (string) $job->id)) return null;

        // Single-group jobs inherit their group; multi/none target everyone
        $groups   = is_array($job->age_groups) ? array_values(array_filter($job->age_groups)) : [];
        $ageGroup = count($groups) === 1 ? $groups[0] : 'all';
        $level    = max(1, (int) ($job->level ?? 1));

        $copy = PesaVoice::compose('get_hired', $ageGroup === 'all' ? null : $ageGroup,
            ['job' => $job->title, 'employer' => $job->employer_name ?: 'Pesa City', 'n' => 1, 'name' => 'rafiki'], targeted: true);
        if (!$copy) return null;

        // Study → get hired: chain the first required course as step 1
        $triggers = [];
        $courseIds = is_array($job->required_course_ids) ? $job->required_course_ids : [];
        $course    = $courseIds ? CityCourse::whereIn('id', $courseIds)->where('is_active', true)->first() : null;
        if ($course) {
            $studyLabel = PesaVoice::fill('Complete the "{course}" course 📚', ['course' => $course->title]);
            $triggers[] = ['type' => 'take_course', 'values' => [$course->slug], 'label' => $studyLabel];
        }
        $triggers[] = ['type' => 'get_job', 'values' => [(string) $job->id], 'label' => $copy['label']];

        $careerTracks = is_array($job->career_tracks) ? array_values(array_filter($job->career_tracks)) : [];

        return $this->createDraft([
            'title'          => mb_substr($copy['title'] . ': ' . $job->title, 0, 150),
            'description'    => $copy['intro'],
            'instructions'   => implode(' → ', array_column($triggers, 'label')),
            'lesson'         => trim($copy['lesson'] . ' ' . $copy['signoff']),
            'icon'           => '💼',
            'age_group'      => $ageGroup,
            'career_fields'  => $careerTracks ?: null,
            'level_required' => $level,
            'xp_reward'      => 150 + $level * 50,
            'kes_reward'     => 100 + $level * 25,
            'triggers'       => $triggers,
            'trigger_type'   => $triggers[0]['type'],
            'trigger_value'  => implode(',', $triggers[0]['values']),
            'trigger_label'  => $triggers[0]['label'],
        ]);
    }

    // ── Blueprint line: the level-ladder printing press ──────────────────

    /**
     * Print every missing blueprint quest. Called by the nightly
     * game:sweep-quests command and the "Run sweep" button in Automation.
     * Returns a summary: ['blueprints' => n, 'created' => n, 'existing' => n].
     */
    public function sweep(): array
    {
        $summary = ['blueprints' => 0, 'created' => 0, 'existing' => 0];

        if (!self::enabled() || !Schema::hasTable('quest_blueprints') || !Schema::hasColumn('quests', 'blueprint_id')) {
            return $summary;
        }

        foreach (QuestBlueprint::where('is_active', true)->orderBy('id')->get() as $bp) {
            $summary['blueprints']++;
            $prev = null; // previous rung for chain linking (existing or freshly printed)

            foreach ($bp->slots() as $i => $level) {
                $existing = Quest::where('blueprint_id', $bp->id)->where('blueprint_slot', $level)->first();
                if ($existing) {
                    $prev = $existing;
                    $summary['existing']++;
                    continue;
                }

                $quest = $this->draftFromBlueprint($bp, $level, $i, $bp->chain ? $prev : null);
                if ($quest) {
                    $prev = $quest;
                    $summary['created']++;
                }
            }
        }

        return $summary;
    }

    /** Print one rung of a blueprint's ladder as a quest draft. */
    public function draftFromBlueprint(QuestBlueprint $bp, int $level, int $slotIndex, ?Quest $prev): ?Quest
    {
        $steps = array_values(array_filter((array) $bp->steps, fn ($s) => !empty($s['type'])));
        if (empty($steps)) return null;

        $ageGroup = $bp->age_group ?: 'all';

        // Voice: the blueprint's archetype fronts the copy. Quests are shared
        // by all players, so {name} gets the universal "rafiki"; {amount}
        // quotes the first step that carries a real number at this rung.
        $vars = ['n' => 1, 'name' => 'rafiki'];
        foreach ($steps as $s) {
            $v = $bp->valueFor($s, $level);
            if (is_numeric($v)) {
                $vars['amount'] = (int) $v;
                $vars['n']      = (int) $v;
                break;
            }
        }

        $archetype = $bp->archetype ?: (self::TRIGGER_META[$steps[0]['type']]['arch'] ?? 'stash_it');
        $copy = PesaVoice::compose($archetype, $ageGroup === 'all' ? null : $ageGroup, $vars);
        if (!$copy) return null;

        // Chapter numbering: multi-rung ladders read as a series (I, II, III…)
        $title = $copy['title'];
        if (count($bp->slots()) > 1) {
            $title .= ' ' . $this->roman($slotIndex + 1);
        }

        $triggers = [];
        if ($prev) {
            $triggers[] = [
                'type'   => 'complete_quest',
                'values' => [(string) $prev->id],
                'label'  => 'Finish "' . $prev->title . '" first 🔗',
            ];
        }
        foreach ($steps as $step) {
            $value = $bp->valueFor($step, $level);
            $triggers[] = [
                'type'   => $step['type'],
                'values' => $value !== null ? [(string) $value] : [],
                'label'  => $this->stepLabel($step, $value),
            ];
        }

        // Legacy single-trigger mirror uses the first REAL objective (not the chain link)
        $legacy = $triggers[$prev ? 1 : 0];

        return $this->createDraft([
            'title'          => mb_substr($title, 0, 150),
            'description'    => $copy['intro'],
            'instructions'   => implode(' → ', array_column($triggers, 'label')),
            'lesson'         => trim($copy['lesson'] . ' ' . $copy['signoff']),
            'icon'           => $bp->icon ?: $copy['icon'],
            'age_group'      => $ageGroup,
            'career_fields'  => $bp->career_fields ?: null,
            'level_required' => $level,
            'xp_reward'      => (int) $bp->xp_base + (int) $bp->xp_per_level * max(0, $level - 1),
            'kes_reward'     => (int) $bp->kes_base + (int) $bp->kes_per_level * max(0, $level - 1),
            'triggers'       => $triggers,
            'trigger_type'   => $legacy['type'],
            'trigger_value'  => implode(',', $legacy['values']) ?: null,
            'trigger_label'  => $legacy['label'],
            'source'         => 'blueprint',
            'blueprint_id'   => $bp->id,
            'blueprint_slot' => $level,
        ]);
    }

    /** Player-facing step line: custom template if the admin wrote one, else per-type default. */
    private function stepLabel(array $step, ?string $value): string
    {
        $meta   = self::TRIGGER_META[$step['type']] ?? [];
        $pretty = $value !== null ? $this->prettyValue($step['type'], $value) : null;

        $template = trim((string) ($step['label'] ?? ''));
        if ($template === '') {
            $template = $value === null
                ? ($meta['anyLabel'] ?? $meta['label'] ?? 'Complete this step')
                : ($meta['label'] ?? $meta['anyLabel'] ?? 'Complete this step');
        }

        return PesaVoice::fill($template, [
            'amount' => is_numeric($value) ? (int) $value : $value,
            'n'      => is_numeric($value) ? (int) $value : 1,
            'value'  => $pretty ?? (string) $value,
        ]);
    }

    /** Turn stored trigger values (slugs, ids) into human names for labels. */
    private function prettyValue(string $type, string $value): string
    {
        try {
            return match ($type) {
                'take_course'   => (string) (DB::table('city_courses')->where('slug', $value)->value('title') ?: $value),
                'get_job'       => (string) (DB::table('city_jobs')->where('id', $value)->value('title') ?: $value),
                'buy_item_slug' => (string) (DB::table('assets')->where('slug', $value)->value('name') ?: $value),
                'earn_badge'    => (string) (DB::table('badges')->where('slug', $value)->value('name') ?: $value),
                default         => $value,
            };
        } catch (\Throwable) {
            return $value;
        }
    }

    private function roman(int $n): string
    {
        $map = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV'];
        return $map[$n] ?? (string) $n;
    }

    // ── Shared plumbing ───────────────────────────────────────────────────

    /** Count of drafts waiting for approval (all three production lines). */
    public static function pendingDrafts(): int
    {
        if (!Schema::hasTable('quests') || !Schema::hasColumn('quests', 'source')) return 0;
        return Quest::whereIn('source', ['factory', 'blueprint', 'mixer'])->where('is_active', false)->count();
    }

    private function createDraft(array $attrs): Quest
    {
        return Quest::create($attrs + [
            'is_active'    => self::autopublish(),
            'source'       => 'factory',
            'trigger_mode' => 'all',
            'sort_order'   => 0,
        ]);
    }

    /** Don't double-draft: skip if any quest already targets this content. */
    private function alreadyCovered(string $type, string $value): bool
    {
        $query = Quest::where(fn ($x) => $x->where('trigger_type', $type)->where('trigger_value', 'like', "%{$value}%"));
        if (Schema::hasColumn('quests', 'triggers')) {
            $query->orWhere('triggers', 'like', '%"' . $type . '"%"' . $value . '"%');
        }
        return $query->exists();
    }
}
