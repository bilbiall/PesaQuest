<?php

namespace App\Services;

use App\Models\Node;
use App\Models\UserProgress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * EventEngine — filter pipeline selecting contextual world events for Pesa City.
 *
 * Scenarios live in the existing `nodes` table (type='scenario', is_start=true).
 * Event-specific metadata is stored in each node's `metadata` JSON column:
 *   - event_type           : 'opportunity' | 'cost' | 'career' | 'asset'
 *   - career_track         : matches UserProgress.career_field (technology, finance, etc.)
 *   - required_asset_category : 'property' | 'vehicle' | 'stock' | null
 *   - min_chapter          : 1–6 (maps to life_chapter stages)
 *   - is_dismissable       : true/false
 *
 * Filter chain:
 *   1. career      — match player's career_field (or no requirement)
 *   2. asset       — match player's owned asset categories (or no requirement)
 *   3. stage       — match player's chapter level
 *   4. dedup       — exclude seen within last 14 game days (player_event_log table)
 *   5. lifestyle   — weight by balance-to-income ratio
 *   6. pick N
 *
 * To add a new filter: write a private method and add its name to the pipe() call in get().
 */
class EventEngine
{
    // life_chapter string → numeric stage for min_chapter comparison
    private const CHAPTER_ORDER = [
        'student'  => 1,
        'graduate' => 2,
        'hustler'  => 3,
        'settler'  => 4,
        'builder'  => 5,
        'elder'    => 6,
    ];

    private UserProgress $progress;
    private int $limit;

    public function __construct(UserProgress $progress, int $limit = 3)
    {
        $this->progress = $progress;
        $this->limit    = $limit;
    }

    /**
     * Run the full filter pipeline and return events as formatted arrays
     * ready for Alpine's worldEvent state object.
     */
    public function get(): array
    {
        if (!Schema::hasTable('nodes')) {
            return [];
        }

        $candidates = Node::query()
            ->where('type', 'scenario')
            ->where('is_start', true)
            ->with('choices')
            ->get();

        if ($candidates->isEmpty()) {
            return [];
        }

        $events = $this->pipe($candidates, [
            'filterByCareer',
            'filterByAsset',
            'filterByStage',
            'deduplicateRecent',
            'weightByLifestyle',
        ]);

        return $events
            ->take($this->limit)
            ->values()
            ->map(fn($n) => $this->format($n))
            ->all();
    }

    // ── Filter pipeline ──────────────────────────────────────────────

    private function pipe(Collection $items, array $filters): Collection
    {
        foreach ($filters as $filter) {
            $items = $this->$filter($items);
        }
        return $items;
    }

    /**
     * Keep nodes that match the player's career field (or have no career requirement).
     * Uses UserProgress.career_field — the value set during the career quiz onboarding.
     */
    private function filterByCareer(Collection $items): Collection
    {
        $field = $this->progress->career_field ?? null;
        if (!$field) return $items;

        return $items->filter(function ($node) use ($field) {
            $required = $node->metadata['career_track'] ?? null;
            return !$required || $required === $field;
        });
    }

    /**
     * Keep nodes that require an asset category the player owns (or have no requirement).
     */
    private function filterByAsset(Collection $items): Collection
    {
        $owned = $this->getOwnedAssetCategories();

        return $items->filter(function ($node) use ($owned) {
            $required = $node->metadata['required_asset_category'] ?? null;
            if (!$required) return true;
            return in_array($required, $owned);
        });
    }

    /**
     * Keep nodes appropriate for the player's current life chapter.
     * metadata.min_chapter (1–6) mapped via CHAPTER_ORDER.
     */
    private function filterByStage(Collection $items): Collection
    {
        $chapter = self::CHAPTER_ORDER[$this->progress->life_chapter ?? 'student'] ?? 1;

        return $items->filter(function ($node) use ($chapter) {
            $min = (int) ($node->metadata['min_chapter'] ?? 1);
            return $chapter >= $min;
        });
    }

    /**
     * Exclude nodes seen within the last 14 game days.
     * Gracefully skips if player_event_log table doesn't exist yet.
     */
    private function deduplicateRecent(Collection $items): Collection
    {
        if (!Schema::hasTable('player_event_log')) {
            return $items;
        }

        $recentIds = \DB::table('player_event_log')
            ->where('player_id', $this->progress->user_id)
            ->where('seen_at', '>=', now()->subDays(14))
            ->pluck('node_id')
            ->all();

        if (empty($recentIds)) return $items;

        return $items->reject(fn($n) => in_array($n->id, $recentIds));
    }

    /**
     * Sort events by lifestyle tier so the right kinds appear first.
     * High earner with low balance → luxury events first.
     * High balance relative to income → cost events (emergency fund matters).
     * Otherwise → full shuffle.
     */
    private function weightByLifestyle(Collection $items): Collection
    {
        $tier = $this->getLifestyleTier();

        $preferred = match ($tier) {
            'luxury' => ['opportunity', 'asset'],
            'frugal' => ['cost'],
            default  => null,
        };

        if (!$preferred) {
            return $items->shuffle();
        }

        return $items->sortByDesc(function ($node) use ($preferred) {
            $type = $node->metadata['event_type'] ?? '';
            return in_array($type, $preferred) ? 1 : 0;
        })->values();
    }

    // ── Formatters ───────────────────────────────────────────────────

    private function format($node): array
    {
        $metadata = $node->metadata ?? [];
        $type     = $metadata['event_type'] ?? 'opportunity';

        // Opportunity events carry a game-day expiry for urgency UI
        $expiresInDays = ($type === 'opportunity')
            ? (int) ($metadata['expires_in_days'] ?? 3)
            : null;

        return [
            'event_id'       => $node->id,
            'type'           => $type,
            'category_label' => $this->categoryLabel($type),
            'icon'           => $node->icon ?? '⚡',
            'title'          => $node->title,
            'description'    => $node->scenario_text,
            'impact_chips'   => $this->buildImpactChips($node),
            'choices'        => $this->buildChoices($node),
            'dismissable'    => $metadata['is_dismissable'] ?? true,
            'expires_in_days'=> $expiresInDays,
        ];
    }

    private function buildChoices($node): array
    {
        return $node->choices->map(fn($c) => [
            'id'           => $c->id,
            'icon'         => $c->effect_data['icon'] ?? ($c->balance_change >= 0 ? '✅' : '⚠️'),
            'label'        => $c->label,
            'outcome_hint' => $c->description ?? '',
            'delta'        => $c->balance_change,
        ])->values()->all();
    }

    private function buildImpactChips($node): array
    {
        if ($node->choices->isEmpty()) return [];

        $deltas = $node->choices->map(fn($c) => $c->balance_change);
        $max    = $deltas->max();
        $min    = $deltas->min();

        if ($max === 0 && $min === 0) return [];

        return [
            [
                'value' => ($max >= 0 ? '+' : '') . 'KES ' . number_format(abs($max)),
                'label' => 'Best Case',
                'color' => '#15C77E',
            ],
            [
                'value' => ($min >= 0 ? '+' : '') . 'KES ' . number_format(abs($min)),
                'label' => 'Worst Case',
                'color' => '#EF5350',
            ],
        ];
    }

    private function categoryLabel(string $type): string
    {
        return match ($type) {
            'opportunity' => '💰 Opportunity',
            'cost'        => '⚠️ Unexpected Cost',
            'career'      => '💼 Career Event',
            'asset'       => '🏠 Asset Event',
            default       => '🌍 City Event',
        };
    }

    // ── Profile helpers ──────────────────────────────────────────────

    private function getOwnedAssetCategories(): array
    {
        if (!Schema::hasTable('assets')) return [];

        return \DB::table('assets')
            ->where('player_id', $this->progress->user_id)
            ->pluck('category')
            ->unique()
            ->values()
            ->all();
    }

    private function getLifestyleTier(): string
    {
        $income  = (int) ($this->progress->career_income_rate ?? 0);
        $balance = (int) ($this->progress->balance ?? 0);

        if ($income <= 0) return 'moderate';

        // High earner spending down their balance = luxury lifestyle
        if ($income >= 80000 && $balance < $income) return 'luxury';

        // Balance is 6+ months of salary = frugal saver
        if ($balance > $income * 6) return 'frugal';

        return 'moderate';
    }
}
