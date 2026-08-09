<?php

namespace App\Models;

use App\Models\GameNotification;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    protected $fillable = [
        'user_id', 'current_node_id', 'current_scenario_start_id', 'path_history',
        'points_total', 'balance', 'level', 'last_played_at', 'node_unlocked_at', 'last_bonus_at',
        'total_decisions', 'consecutive_save_choices', 'financial_personality', 'last_assessment_at_decision',
        'career_field', 'career_title', 'career_income_rate', 'career_income_claimed_at', 'pending_salary', 'active_loans',
        'last_tick_at', 'tick_count', 'credit_score', 'net_worth_cache', 'life_chapter', 'game_age',
        'mood', 'mood_last_boosted_at',
    ];

    protected $casts = [
        'path_history' => 'array',
        'last_played_at' => 'datetime',
        'node_unlocked_at' => 'datetime',
        'last_bonus_at' => 'datetime',
        'career_income_claimed_at' => 'datetime',
        'last_tick_at' => 'datetime',
        'active_loans'             => 'array',
        'mood_last_boosted_at'     => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentNode()
    {
        return $this->belongsTo(Node::class, 'current_node_id');
    }

    public function addPoints(int $points): void
    {
        $this->points_total += $points;
        $this->level = $this->calculateLevel();
        $this->last_played_at = now();
        $this->save();
    }

    public function incrementTick(): void
    {
        $this->tick_count = ($this->tick_count ?? 0) + 1;
    }

    public function adjustCreditScore(int $delta): void
    {
        $this->credit_score = max(300, min(850, ($this->credit_score ?? 500) + $delta));
    }

    /**
     * Adjust credit score AND record the change in the player's credit history
     * (GameNotification type `credit_change` — consumed by the /life history panel).
     */
    public function adjustCreditScoreWithLog(int $delta, string $reason, array $extra = []): void
    {
        $before = $this->credit_score ?? 500;
        $this->adjustCreditScore($delta);
        $applied = $this->credit_score - $before;
        if ($applied === 0) return;

        GameNotification::create([
            'user_id' => $this->user_id,
            'type'    => 'credit_change',
            'title'   => ($applied > 0 ? '📈' : '📉') . " Credit Score " . ($applied > 0 ? '+' : '') . $applied,
            'body'    => $reason . ". New score: {$this->credit_score} ({$this->creditScoreLabel()})",
            'icon'    => $applied > 0 ? '📈' : '📉',
            'data'    => array_merge(['delta' => $applied, 'reason' => $reason, 'score' => $this->credit_score], $extra),
        ]);
    }

    public function creditScoreLabel(): string
    {
        return match(true) {
            $this->credit_score >= 750 => 'Excellent',
            $this->credit_score >= 650 => 'Good',
            $this->credit_score >= 550 => 'Fair',
            $this->credit_score >= 450 => 'Poor',
            default                    => 'Very Poor',
        };
    }

    // ── Net worth ─────────────────────────────────────────────────────────────

    /**
     * Realistic net worth: everything the player owns minus what they owe.
     *
     *   + cash balance
     *   + market value of active assets
     *   + savings scheme balances
     *   + capital locked in pending investment deals
     *   + share of chama pools (share % × pool balance)
     *   + market value of Equity Square share holdings
     *   − outstanding loan balances
     *
     * Updates net_worth_cache (does NOT save — callers save the model).
     */
    public function recalculateNetWorth(): int
    {
        $userId = $this->user_id;

        $assets = (int) \App\Models\PlayerAsset::where('user_id', $userId)
            ->where('status', 'active')
            ->sum('current_value');

        $savings = (int) \App\Models\SavingsScheme::where('user_id', $userId)
            ->where('is_archived', false)
            ->sum('current_amount');

        $deals = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('player_deals')) {
            $deals = (int) \App\Models\PlayerDeal::where('user_id', $userId)
                ->where('status', 'pending')
                ->sum('amount_invested');
        }

        $chama = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('chama_members')) {
            $chama = (int) \App\Models\ChamaMember::where('user_id', $userId)
                ->where('is_active', true)
                ->join('chamas', 'chamas.id', '=', 'chama_members.chama_id')
                ->selectRaw('COALESCE(SUM(chamas.pool_balance * chama_members.share_pct / 100), 0) as share')
                ->value('share');
        }

        $debts = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('player_loans')) {
            $debts = (int) \App\Models\PlayerLoan::where('user_id', $userId)
                ->where('status', 'active')
                ->sum('outstanding_balance');
        }

        $shares = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('player_share_holdings')) {
            $shares = (int) \App\Models\PlayerShareHolding::where('user_id', $userId)
                ->join('shares', 'shares.id', '=', 'player_share_holdings.share_id')
                ->selectRaw('COALESCE(SUM(player_share_holdings.quantity * shares.current_price), 0) as v')
                ->value('v');
        }

        $this->net_worth_cache = (int) ($this->balance ?? 0) + $assets + $savings + $deals + $chama + $shares - $debts;

        return $this->net_worth_cache;
    }

    // ── Life Chapter helpers ──────────────────────────────────────────────────

    /**
     * The six life stages of a PesaQuest player. The KEYS are fixed (database
     * enums, timeline grouping and life-event scoping depend on them) but the
     * names, icons, taglines and net-worth thresholds are fully editable from
     * GameSet Hub → Life Chapters (stored in the `life_chapters` Setting).
     */
    public const CHAPTER_DEFAULTS = [
        ['key' => 'student',  'name' => 'The Student',  'icon' => '🎒', 'tagline' => 'Pocket money, savings goals, small hustles',       'min_net_worth' => 0],
        ['key' => 'graduate', 'name' => 'The Graduate', 'icon' => '🎓', 'tagline' => 'First job, renting, starting from zero',           'min_net_worth' => 50_000],
        ['key' => 'hustler',  'name' => 'The Hustler',  'icon' => '💪', 'tagline' => 'Career growth, first investment, first loan',      'min_net_worth' => 200_000],
        ['key' => 'settler',  'name' => 'The Settler',  'icon' => '🏠', 'tagline' => 'Mortgage, family costs, business stakes',          'min_net_worth' => 1_000_000],
        ['key' => 'builder',  'name' => 'The Builder',  'icon' => '📊', 'tagline' => 'Passive income, portfolio, wealth transfer',       'min_net_worth' => 5_000_000],
        ['key' => 'elder',    'name' => 'The Elder',    'icon' => '🌟', 'tagline' => 'Retirement planning, legacy',                      'min_net_worth' => 20_000_000],
    ];

    private static ?array $chaptersCache = null;

    /** Chapters config: admin overrides merged over defaults, sorted by threshold. */
    public static function chapters(): array
    {
        if (self::$chaptersCache !== null) return self::$chaptersCache;

        $saved = [];
        try {
            $saved = json_decode(\App\Models\Setting::get('life_chapters', '') ?: '[]', true) ?: [];
        } catch (\Throwable $e) {
            // settings table unavailable (fresh install) — use defaults
        }
        $savedByKey = collect($saved)->keyBy('key');

        $chapters = array_map(function ($def) use ($savedByKey) {
            $s = $savedByKey->get($def['key'], []);
            return [
                'key'           => $def['key'],
                'name'          => trim((string) ($s['name'] ?? '')) !== ''    ? $s['name']    : $def['name'],
                'icon'          => trim((string) ($s['icon'] ?? '')) !== ''    ? $s['icon']    : $def['icon'],
                'tagline'       => trim((string) ($s['tagline'] ?? '')) !== '' ? $s['tagline'] : $def['tagline'],
                'min_net_worth' => max(0, (int) ($s['min_net_worth'] ?? $def['min_net_worth'])),
            ];
        }, self::CHAPTER_DEFAULTS);

        usort($chapters, fn ($a, $b) => $a['min_net_worth'] <=> $b['min_net_worth']);
        $chapters[0]['min_net_worth'] = 0; // the first stage always starts at zero

        return self::$chaptersCache = $chapters;
    }

    /** Metadata for one chapter key (name/icon/tagline/min_net_worth). */
    public static function chapterMeta(string $key): array
    {
        foreach (self::chapters() as $c) {
            if ($c['key'] === $key) return $c;
        }
        return ['key' => $key, 'name' => ucfirst($key), 'icon' => '⭐', 'tagline' => '', 'min_net_worth' => 0];
    }

    /** key => [band_start, band_end] map for progress bars (last band closes on itself). */
    public static function chapterBands(): array
    {
        $chapters = self::chapters();
        $bands    = [];
        foreach ($chapters as $i => $c) {
            $next = $chapters[$i + 1]['min_net_worth'] ?? $c['min_net_worth'];
            $bands[$c['key']] = [$c['min_net_worth'], $next];
        }
        return $bands;
    }

    // Net worth thresholds for each chapter (KES) — admin-tunable, see chapters()
    public static function chapterFromNetWorth(int $netWorth): string
    {
        $chapters = self::chapters();
        $current  = $chapters[0]['key'];
        foreach ($chapters as $c) {
            if ($netWorth >= $c['min_net_worth']) $current = $c['key'];
        }
        return $current;
    }

    /** 1-based position of a chapter key in the ordered chapter list (student=1, ...). */
    public static function chapterOrdinal(string $key): int
    {
        foreach (self::chapters() as $i => $c) {
            if ($c['key'] === $key) return $i + 1;
        }
        return 1;
    }

    // Kept for backward compatibility (bill age_group seeding)
    public static function chapterFromAge(int $age): string
    {
        return match(true) {
            $age <= 17 => 'student',
            $age <= 22 => 'graduate',
            $age <= 28 => 'hustler',
            $age <= 40 => 'settler',
            $age <= 55 => 'builder',
            default    => 'elder',
        };
    }

    public static function startingAgeFromGroup(string $ageGroup): int
    {
        return match($ageGroup) {
            '8-12'  => 10,
            '13-17' => 14,
            '18-25' => 18,
            '26+'   => 26,
            default => 18,
        };
    }

    public function chapterKey(): string
    {
        return static::chapterFromNetWorth((int)($this->net_worth_cache ?? 0));
    }

    public function chapterName(): string
    {
        return static::chapterMeta($this->chapterKey())['name'];
    }

    public function chapterIcon(): string
    {
        return static::chapterMeta($this->chapterKey())['icon'];
    }

    public function chapterTagline(): string
    {
        return static::chapterMeta($this->chapterKey())['tagline'];
    }

    public function nextChapterNetWorth(): ?int
    {
        $chapters = static::chapters();
        $key      = $this->chapterKey();
        foreach ($chapters as $i => $c) {
            if ($c['key'] === $key) {
                return $chapters[$i + 1]['min_net_worth'] ?? null;
            }
        }
        return null;
    }

    public function netWorthToNextChapter(): ?int
    {
        $next = $this->nextChapterNetWorth();
        if ($next === null) return null;
        return max(0, $next - (int)($this->net_worth_cache ?? 0));
    }

    // Legacy aliases — kept so old callers that only check null still compile
    public function nextChapterAge(): ?int    { return $this->nextChapterNetWorth(); }
    public function yearsToNextChapter(): ?int { return $this->netWorthToNextChapter(); }

    /**
     * Real per-player journey milestones — admin-configured via GameSet Hub
     * (Setting `journey_milestones`, falling back to GameSetController's
     * defaults), each decorated with an `achieved` flag computed from the
     * player's actual course/job/asset/quest/net-worth/level progress. This
     * is the single source of truth for both the World map's bottom journey
     * bar and the /life timeline — never hardcode a separate milestone chain.
     */
    public static function journeyMilestonesFor(\App\Models\User $user, self $progress): array
    {
        $json       = \App\Models\Setting::get('journey_milestones', null);
        $milestones = $json ? (json_decode($json, true) ?: []) : [];
        if (empty($milestones)) {
            $milestones = \App\Http\Controllers\GameSetController::defaultMilestones();
        }

        $courseDone = \App\Models\PlayerCityCourse::where('user_id', $user->id)->where('status', 'completed')->count();
        $jobsHired  = \App\Models\PlayerCityJob::where('user_id', $user->id)->count();
        $assetCount = \App\Models\PlayerAsset::where('user_id', $user->id)->count();
        $questsDone = \App\Models\UserQuest::where('user_id', $user->id)->whereNotNull('completed_at')->count();
        $netWorth   = ($progress->balance ?? 0) + \App\Models\PlayerAsset::where('user_id', $user->id)->sum('purchase_price');

        foreach ($milestones as &$ms) {
            $ms['achieved'] = match ($ms['type'] ?? 'manual') {
                'level'     => ($progress->level ?? 1) >= ($ms['threshold'] ?? 0),
                'balance'   => ($progress->balance ?? 0) >= ($ms['threshold'] ?? 0),
                'net_worth' => $netWorth >= ($ms['threshold'] ?? 0),
                'job'       => $jobsHired >= ($ms['threshold'] ?? 1),
                'course'    => $courseDone >= ($ms['threshold'] ?? 1),
                'quest'     => $questsDone >= ($ms['threshold'] ?? 1),
                'asset'     => $assetCount >= ($ms['threshold'] ?? 1),
                default     => false,
            };
        }
        unset($ms);

        return $milestones;
    }

    public static function getLevelThresholds(): array
    {
        $json = \App\Models\Setting::get('level_config');
        if ($json) {
            $config = json_decode($json, true);
            if (is_array($config) && count($config) >= 2) {
                return array_column($config, 'xp');
            }
        }
        return [0, 100, 300, 600, 1000, 1500, 2100, 2800, 3600, 4500, 5500];
    }

    /** Full level config (xp + name pairs) — admin-set via GameSet Hub → XP Levels, or defaults. */
    public static function getLevelConfig(): array
    {
        $json = \App\Models\Setting::get('level_config');
        if ($json) {
            $config = json_decode($json, true);
            if (is_array($config) && count($config) >= 2) {
                return $config;
            }
        }
        return \App\Http\Controllers\GameSetController::defaultLevels();
    }

    /**
     * The player's current level NAME — e.g. "Investor" — from the SAME
     * admin-configured level_config every other level display should read.
     * Single source of truth: this, getPointsToNextLevelAttribute() and
     * getLevelProgressPercentAttribute() are the only correct way to show
     * level/XP progress anywhere in the app — never hardcode thresholds or
     * names locally, they silently drift from whatever the admin sets.
     */
    public function getLevelNameAttribute(): string
    {
        $config = static::getLevelConfig();
        $idx    = max(0, min($this->level - 1, count($config) - 1));
        return $config[$idx]['name'] ?? 'PesaQuest Legend';
    }

    /** Name of the NEXT level up — for "XP to become X" style displays. Null at max level. */
    public function getNextLevelNameAttribute(): ?string
    {
        $config = static::getLevelConfig();
        return $config[$this->level]['name'] ?? null;
    }

    /**
     * The player's effective level: XP thresholds, capped by the Quest Gate
     * (you can't level past a level that still has unfinished quests).
     */
    public function calculateLevel(): int
    {
        return \App\Services\QuestGate::apply($this, $this->calculateXpLevel());
    }

    /** Raw XP level from the thresholds only — ignores the Quest Gate. */
    public function calculateXpLevel(): int
    {
        $thresholds = static::getLevelThresholds();
        foreach (array_reverse($thresholds, true) as $level => $threshold) {
            if ($this->points_total >= $threshold) {
                return $level + 1;
            }
        }
        return 1;
    }

    public function getPointsToNextLevelAttribute(): int
    {
        $thresholds = static::getLevelThresholds();
        $level = $this->level - 1;
        if (!isset($thresholds[$level + 1])) return 0;
        return $thresholds[$level + 1] - $this->points_total;
    }

    public function getLevelProgressPercentAttribute(): int
    {
        $thresholds = static::getLevelThresholds();
        $level = $this->level - 1;
        $prev = $thresholds[$level - 1] ?? 0;
        $curr = $thresholds[$level]     ?? 0;
        $next = $thresholds[$level + 1] ?? ($curr + 1000);
        if ($next === $prev) return 100;
        return (int)(($this->points_total - $prev) / ($next - $prev) * 100);
    }
}
