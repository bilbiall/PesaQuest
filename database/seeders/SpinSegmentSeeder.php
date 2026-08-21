<?php

namespace Database\Seeders;

use App\Models\SpinSegment;
use Illuminate\Database\Seeder;

class SpinSegmentSeeder extends Seeder
{
    /**
     * The wheel had no level gating at all — every player, level 1 or 10, spun
     * the identical prize pool. These 6 higher-value/higher-risk wedges only
     * unlock for players who've actually progressed, so the wheel gets more
     * interesting (bigger upside, bigger downside) as you level up.
     */
    private const HIGH_LEVEL_SEGMENTS = [
        ['label' => 'Ksh 20,000',       'emoji' => '💎', 'color' => '#6d28d9', 'type' => 'balance', 'value' => 20000,  'weight' => 3, 'tier' => 'great', 'min_level' => 5],
        ['label' => 'Ksh 30,000 Jackpot','emoji' => '🎊', 'color' => '#ca8a04', 'type' => 'balance', 'value' => 30000,  'weight' => 1, 'tier' => 'great', 'min_level' => 8],
        ['label' => '+75 Credit Surge', 'emoji' => '🚀', 'color' => '#047857', 'type' => 'credit',  'value' => 75,     'weight' => 3, 'tier' => 'great', 'min_level' => 6],
        ['label' => '5,000 XP Boost',   'emoji' => '💫', 'color' => '#1e40af', 'type' => 'xp',      'value' => 5000,   'weight' => 3, 'tier' => 'great', 'min_level' => 7],
        ['label' => 'Ksh 5,500 Fine',   'emoji' => '🥶', 'color' => '#7f1d1d', 'type' => 'balance', 'value' => -5500,  'weight' => 10, 'tier' => 'bad',   'min_level' => 5],
        ['label' => 'Ksh 9,000 Fine',   'emoji' => '💀', 'color' => '#450a0a', 'type' => 'balance', 'value' => -9000,  'weight' => 6, 'tier' => 'bad',   'min_level' => 8],
    ];

    /** Seeds the original hardcoded wheel plus the level-gated additions — safe to re-run (keyed by label). */
    public function run(): void
    {
        foreach (SpinSegment::DEFAULTS as $i => $seg) {
            SpinSegment::updateOrCreate(
                ['label' => $seg['label']],
                $seg + ['sort_order' => $i, 'min_level' => 1, 'is_active' => true]
            );
        }

        foreach (self::HIGH_LEVEL_SEGMENTS as $i => $seg) {
            SpinSegment::updateOrCreate(
                ['label' => $seg['label']],
                $seg + ['sort_order' => 100 + $i, 'is_active' => true]
            );
        }
    }
}
