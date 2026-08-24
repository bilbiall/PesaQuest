<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SpinSegment extends Model
{
    protected $fillable = [
        'label', 'emoji', 'color', 'type', 'value', 'weight', 'tier', 'min_level', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'balance'   => 'KES (negative = fine)',
        'credit'    => 'Credit score ±',
        'xp'        => 'XP points',
        'salary_2x' => 'Double next salary',
    ];

    /** The wheel needs at least this many wedges to look/spin right. */
    public const MIN_SEGMENTS = 4;

    /**
     * Pre-CRUD defaults — also the fallback until the table is migrated/seeded.
     *
     * Rebalanced (Aug 2026): the previous 22-wedge pool was ~72% win-weighted
     * with an expected value of roughly +1,180 KES per free spin -- every
     * category except balance/credit had zero downside wedges at all, so it
     * read more as a guaranteed daily gift than a game of chance. This pool
     * targets ~55% win / ~36% loss / ~9% "nothing" by weight, and a modest
     * near-zero KES expected value, while staying meaningfully rewarding.
     */
    public const DEFAULTS = [
        ['label' => 'Ksh 500',            'emoji' => '🪙', 'color' => '#10b981', 'type' => 'balance',   'value' => 500,    'weight' => 22, 'tier' => 'good'],
        ['label' => 'Ksh 1,500',          'emoji' => '💸', 'color' => '#22c55e', 'type' => 'balance',   'value' => 1500,   'weight' => 16, 'tier' => 'good'],
        ['label' => 'Ksh 3,500',          'emoji' => '💰', 'color' => '#4f46e5', 'type' => 'balance',   'value' => 3500,   'weight' => 10, 'tier' => 'good'],
        ['label' => 'Ksh 7,000',          'emoji' => '💎', 'color' => '#5b21b6', 'type' => 'balance',   'value' => 7000,   'weight' => 4,  'tier' => 'great'],
        ['label' => 'Ksh 500 Fine',       'emoji' => '😕', 'color' => '#ef4444', 'type' => 'balance',   'value' => -500,   'weight' => 20, 'tier' => 'bad'],
        ['label' => 'Ksh 1,500 Fine',     'emoji' => '😬', 'color' => '#dc2626', 'type' => 'balance',   'value' => -1500,  'weight' => 14, 'tier' => 'bad'],
        ['label' => 'Ksh 3,500 Fine',     'emoji' => '😩', 'color' => '#991b1b', 'type' => 'balance',   'value' => -3500,  'weight' => 8,  'tier' => 'bad'],
        ['label' => 'Ksh 6,000 Fine',     'emoji' => '💀', 'color' => '#7f1d1d', 'type' => 'balance',   'value' => -6000,  'weight' => 3,  'tier' => 'bad'],
        ['label' => 'Nothing This Time',  'emoji' => '🌀', 'color' => '#64748b', 'type' => 'balance',   'value' => 0,      'weight' => 14, 'tier' => 'good'],
        ['label' => '+30 Credit',         'emoji' => '📈', 'color' => '#059669', 'type' => 'credit',    'value' => 30,     'weight' => 10, 'tier' => 'good'],
        ['label' => '-20 Credit',         'emoji' => '📉', 'color' => '#7f1d1d', 'type' => 'credit',    'value' => -20,    'weight' => 10, 'tier' => 'bad'],
        ['label' => '800 XP',             'emoji' => '✨', 'color' => '#38bdf8', 'type' => 'xp',        'value' => 800,    'weight' => 14, 'tier' => 'good'],
        ['label' => '2,000 XP',           'emoji' => '🌠', 'color' => '#1d4ed8', 'type' => 'xp',        'value' => 2000,   'weight' => 4,  'tier' => 'great'],
        ['label' => '2× Next Salary',     'emoji' => '⚡', 'color' => '#d97706', 'type' => 'salary_2x', 'value' => 2,      'weight' => 3,  'tier' => 'great'],
    ];

    /**
     * The wheel's segments, in render order, as plain arrays (the shape both
     * the canvas JS and the prize logic consume). Falls back to DEFAULTS until
     * the table exists and holds enough active rows to draw a sane wheel.
     */
    public static function wheelSegments(int $level = 1): array
    {
        try {
            if (!Schema::hasTable('spin_segments')) {
                return self::DEFAULTS;
            }
            $query = self::where('is_active', true);
            if (Schema::hasColumn('spin_segments', 'min_level')) {
                $query->where('min_level', '<=', $level);
            }
            $rows = $query->orderBy('sort_order')->orderBy('id')
                ->get(['label', 'emoji', 'color', 'type', 'value', 'weight', 'tier']);

            // A high min_level filter could shrink the wheel below a sane size
            // for a low-level player — fall back to every active segment rather
            // than serving a broken 2-3 wedge wheel.
            if ($rows->count() < self::MIN_SEGMENTS) {
                $rows = self::where('is_active', true)
                    ->orderBy('sort_order')->orderBy('id')
                    ->get(['label', 'emoji', 'color', 'type', 'value', 'weight', 'tier']);
            }
            if ($rows->count() < self::MIN_SEGMENTS) {
                return self::DEFAULTS;
            }
            return $rows->map(fn ($r) => [
                'label'  => $r->label,
                'emoji'  => $r->emoji,
                'color'  => $r->color,
                'type'   => $r->type,
                'value'  => (int) $r->value,
                'weight' => max(1, (int) $r->weight),
                'tier'   => $r->tier,
            ])->all();
        } catch (\Throwable $e) {
            return self::DEFAULTS;
        }
    }
}
