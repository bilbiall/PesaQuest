<?php

namespace App\Services;

use App\Models\CityCourse;
use App\Models\CityJob;
use App\Models\Quest;
use Illuminate\Support\Facades\Schema;

/**
 * Quest Mixer — the one-button quest composer. 🎲
 *
 * Where blueprints repeat ONE authored recipe up a ladder, the Mixer INVENTS
 * recipes: for every level in range it checks how many quests exist, and
 * composes unique ones to top the level up to the target — no authoring.
 *
 *  - Ingredients come from what the game actually has at that level
 *    (courses, jobs, marketplace, chama…), so quests never point at nothing.
 *  - Money values derive from the economy: average salary of jobs at that
 *    tier sets savings/wallet thresholds, so difficulty self-balances and
 *    adapts as admins add better-paying jobs.
 *  - Uniqueness is enforced by signature (level + sorted step types):
 *    pressing Generate twice never duplicates; it only fills gaps.
 *  - Copy comes from config/pesa_mixer.php — deep per-theme pools crossed
 *    with the NPC cast's greetings/signoffs, so no two quests read alike.
 *
 * Output is ALWAYS drafts (is_active=false, source='mixer') — the admin
 * reviews in GameSet → Quests before anything reaches players.
 */
class QuestMixer
{
    /** Difficulty templates: steps per quest + XP key. */
    private const DIFFICULTY = [
        'easy'    => ['steps' => 1, 'xp' => 'easy'],
        'semi'    => ['steps' => 2, 'xp' => 'semi'],
        'complex' => ['steps' => 3, 'xp' => 'complex'],
    ];

    /** Mix presets: weighted difficulty draw. */
    private const MIXES = [
        'gentle'   => ['easy' => 6, 'semi' => 3, 'complex' => 1],
        'balanced' => ['easy' => 4, 'semi' => 4, 'complex' => 2],
        'spicy'    => ['easy' => 2, 'semi' => 4, 'complex' => 4],
    ];

    /** Age group → voice band for the copy banks. */
    private const GROUP_BAND = ['8-12' => '8-12', '13-17' => '13-17', '18-25' => 'adult', '26+' => 'adult'];

    /**
     * Compose quests for every (level × age group) cell, topping each up to
     * $perLevel. $ageGroups: any of 'all', '8-12', '13-17', '18-25', '26+' —
     * mass-generate for one group or several at once.
     * Returns ['created' => n, 'skipped_full' => n, 'per_level' => [..]].
     */
    public function generate(
        int $levelMin,
        int $levelMax,
        int $perLevel,
        string $mix = 'auto',
        int $xpEasy = 15,
        int $xpSemi = 25,
        int $xpComplex = 50,
        int $xpGrowthPct = 10,
        array $ageGroups = ['all'],
    ): array {
        $summary = ['created' => 0, 'skipped_full' => 0, 'per_level' => []];
        if (!Schema::hasTable('quests') || !Schema::hasColumn('quests', 'level_required')) return $summary;

        $ageGroups  = array_values(array_intersect($ageGroups ?: ['all'], ['all', '8-12', '13-17', '18-25', '26+']));
        $signatures = $this->existingSignatures();
        $usedTitles = Quest::pluck('title')->map(fn ($t) => mb_strtolower($t))->flip()->all();

        foreach ($ageGroups as $group) {
            for ($level = $levelMin; $level <= $levelMax; $level++) {
                // Target counts per (level × group) cell — 'all' quests count for everyone
                $existing = Quest::where('level_required', $level)
                    ->where(fn ($q) => $group === 'all'
                        ? $q->where(fn ($x) => $x->where('age_group', 'all')->orWhereNull('age_group'))
                        : $q->where('age_group', $group))
                    ->count();
                $needed = max(0, $perLevel - $existing);
                if ($needed === 0) { $summary['skipped_full']++; continue; }

                $pool    = $this->ingredientPool($level);
                $salary  = $this->salaryAt($level);
                $mixKey  = $mix === 'auto' ? ($level <= 2 ? 'gentle' : ($level <= 6 ? 'balanced' : 'spicy')) : $mix;
                $weights = self::MIXES[$mixKey] ?? self::MIXES['balanced'];

                $made = 0;
                $attempts = 0;
                while ($made < $needed && $attempts < $needed * 12) {
                    $attempts++;

                    $difficulty = $this->weightedPick($weights);
                    $stepCount  = min(self::DIFFICULTY[$difficulty]['steps'], count($pool));
                    if ($stepCount < 1) break;

                    $types = $this->pickTypes($pool, $stepCount);
                    $sig   = self::signature($level, $group, $types);
                    if (isset($signatures[$sig])) continue;

                    $quest = $this->compose($level, $group, $types, $difficulty, $salary, [
                        'easy' => $xpEasy, 'semi' => $xpSemi, 'complex' => $xpComplex,
                    ], $xpGrowthPct, $usedTitles);

                    if (!$quest) continue;

                    $signatures[$sig] = true;
                    $usedTitles[mb_strtolower($quest->title)] = true;
                    $made++;
                    $summary['created']++;
                }
                $key = $group === 'all' ? "L{$level}" : "L{$level}·{$group}";
                $summary['per_level'][$key] = $made;
            }
        }

        return $summary;
    }

    /** Canonical similarity signature: level + age group + sorted step types. */
    public static function signature(int $level, ?string $ageGroup, array $types): string
    {
        sort($types);
        return $level . '|' . ($ageGroup ?: 'all') . '|' . implode('+', $types);
    }

    // ── Ingredients ────────────────────────────────────────────────────────

    /** Trigger types that make sense at this level, given actual game content. */
    private function ingredientPool(int $level): array
    {
        $pool = ['reach_savings', 'reach_balance', 'deposit_savings'];

        if (Schema::hasTable('city_courses') && CityCourse::where('is_active', true)->exists()) {
            $pool[] = 'take_course';
        }
        if (Schema::hasTable('city_jobs') && CityJob::where('is_active', true)->exists()) {
            $pool[] = 'get_job';
        }
        if ($level >= 2 && Schema::hasTable('assets')) $pool[] = 'buy_item_category';
        if ($level >= 2 && Schema::hasTable('badges')) $pool[] = 'earn_badge';
        if ($level >= 3)                               $pool[] = 'reach_net_worth';
        if ($level >= 3 && Schema::hasTable('chamas')) $pool[] = 'join_chama';
        if ($level <= 3 && Schema::hasTable('spin_segments')) $pool[] = 'spin_wheel';

        return $pool;
    }

    /** Economy probe: average monthly salary of jobs at this level's tier. */
    private function salaryAt(int $level): int
    {
        $tier = min(3, intdiv($level - 1, 3) + 1); // L1-3 → tier 1, L4-6 → 2, L7+ → 3

        $avg = Schema::hasTable('city_jobs')
            ? (int) CityJob::where('is_active', true)->where('level', $tier)->avg('salary_kes_month')
            : 0;

        return $avg > 0 ? $avg : 2500 + 2500 * $tier; // sensible fallback curve
    }

    /** Pick N distinct step types; at most one savings-flavored threshold per quest. */
    private function pickTypes(array $pool, int $count): array
    {
        $moneyKin = ['reach_savings', 'deposit_savings']; // near-duplicates — never pair them
        $picked   = [];
        $bag      = $pool;
        shuffle($bag);

        foreach ($bag as $type) {
            if (count($picked) >= $count) break;
            if (in_array($type, $moneyKin, true) && array_intersect($picked, $moneyKin)) continue;
            $picked[] = $type;
        }

        sort($picked); // signature stability
        return $picked;
    }

    // ── Composition ────────────────────────────────────────────────────────

    private function compose(int $level, string $group, array $types, string $difficulty, int $salary, array $xpBases, int $growthPct, array $usedTitles): ?Quest
    {
        // Threshold values scale off the local economy, rounded friendly
        $values = [];
        foreach ($types as $t) {
            $values[$t] = match ($t) {
                'reach_savings'   => $this->round50((int) ($salary * $this->rnd(0.15, 0.35))),
                'reach_balance'   => $this->round50((int) ($salary * $this->rnd(0.25, 0.50))),
                'deposit_savings' => $this->round50((int) ($salary * $this->rnd(0.08, 0.18))),
                'reach_net_worth' => $this->round50((int) ($salary * $this->rnd(1.0, 2.0))),
                default           => null,
            };
        }

        // The most "themed" step fronts the copy (prefer a money theme, else first)
        $primary    = collect($types)->first(fn ($t) => $values[$t] !== null) ?? $types[0];
        $primaryVal = $values[$primary];

        // Voice matches the audience: kids get sunshine, teens get Sheng,
        // adults get dry wit; 'all' quests alternate teen/adult for spread
        $band = self::GROUP_BAND[$group] ?? ['13-17', 'adult'][random_int(0, 1)];
        $copy = $this->voice($primary, $band, ['amount' => $primaryVal, 'name' => 'rafiki']);
        if (!$copy) return null;

        // Titles must not repeat across the whole quest table
        $title = $copy['title'];
        for ($i = 0; $i < 6 && isset($usedTitles[mb_strtolower($title)]); $i++) {
            $copy  = $this->voice($primary, $band, ['amount' => $primaryVal, 'name' => 'rafiki']);
            $title = $copy['title'];
        }
        if (isset($usedTitles[mb_strtolower($title)])) {
            $title .= ' · Lv' . $level; // last resort disambiguator
        }

        $triggers = [];
        foreach ($types as $t) {
            $meta = QuestFactory::TRIGGER_META[$t] ?? [];
            $tmpl = $values[$t] === null
                ? ($meta['anyLabel'] ?? $meta['label'] ?? 'Complete this step')
                : ($meta['label'] ?? 'Complete this step');
            $triggers[] = [
                'type'   => $t,
                'values' => $values[$t] !== null ? [(string) $values[$t]] : [],
                'label'  => PesaVoice::fill($tmpl, ['amount' => $values[$t], 'n' => $values[$t] ?? 1, 'value' => (string) $values[$t]]),
            ];
        }

        $xp  = (int) round($xpBases[$difficulty] * (1 + $growthPct / 100 * max(0, $level - 1)));
        $kes = (int) (round($xp * $this->rnd(1.6, 2.4) / 10) * 10);

        return Quest::create([
            'title'          => mb_substr($title, 0, 150),
            'description'    => $copy['intro'],
            'instructions'   => implode(' → ', array_column($triggers, 'label')),
            'lesson'         => trim($copy['lesson'] . ' ' . $copy['signoff']),
            'icon'           => $copy['icon'],
            'age_group'      => $group,
            'career_fields'  => null,
            'level_required' => $level,
            'xp_reward'      => $xp,
            'kes_reward'     => $kes,
            'triggers'       => $triggers,
            'trigger_mode'   => 'all',
            'trigger_type'   => $triggers[0]['type'],
            'trigger_value'  => implode(',', $triggers[0]['values']) ?: null,
            'trigger_label'  => $triggers[0]['label'],
            'is_active'      => false,          // ALWAYS a draft — admin reviews first
            'source'         => 'mixer',
            'sort_order'     => 0,
        ]);
    }

    /** Compose copy from the deep mixer bank × the pesa_voice NPC cast. */
    private function voice(string $theme, string $band, array $vars): ?array
    {
        $bank = config("pesa_mixer.themes.{$theme}");
        if (!$bank) return null;

        $npcs   = config('pesa_voice.npcs', []);
        $npcKey = $bank['npcs'][array_rand($bank['npcs'])] ?? array_rand($npcs);
        $npc    = $npcs[$npcKey] ?? reset($npcs);

        $pick = fn (array $byBand) => ($byBand[$band] ?? reset($byBand))[array_rand($byBand[$band] ?? reset($byBand))];

        $greeting = ($npc['greetings'][$band] ?? reset($npc['greetings']))[array_rand($npc['greetings'][$band] ?? reset($npc['greetings']))];
        $signoff  = ($npc['signoffs'][$band] ?? reset($npc['signoffs']))[array_rand($npc['signoffs'][$band] ?? reset($npc['signoffs']))];

        return [
            'icon'    => $bank['icon'] ?? '🎯',
            'title'   => PesaVoice::fill($pick($bank['titles']),  $vars),
            'intro'   => PesaVoice::fill($greeting . ' ' . $pick($bank['pitches']), $vars),
            'lesson'  => PesaVoice::fill($pick($bank['lessons']), $vars),
            'signoff' => PesaVoice::fill($signoff, $vars),
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Signatures of every existing quest: level + age group + sorted types. */
    private function existingSignatures(): array
    {
        $out = [];
        Quest::get(['level_required', 'age_group', 'triggers', 'trigger_type'])->each(function ($q) use (&$out) {
            $types = collect($q->triggers ?? [])->pluck('type')->filter()->all();
            if (empty($types) && $q->trigger_type) $types = [$q->trigger_type];
            if (empty($types)) return;
            $out[self::signature((int) ($q->level_required ?? 1), $q->age_group, $types)] = true;
        });
        return $out;
    }

    private function weightedPick(array $weights): string
    {
        $total = array_sum($weights);
        $roll  = random_int(1, max(1, $total));
        foreach ($weights as $key => $w) {
            if (($roll -= $w) <= 0) return $key;
        }
        return array_key_first($weights);
    }

    private function rnd(float $a, float $b): float
    {
        return $a + (random_int(0, 1000) / 1000) * ($b - $a);
    }

    private function round50(int $n): int
    {
        return max(50, (int) (round($n / 50) * 50));
    }
}
