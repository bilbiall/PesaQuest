<?php

namespace Database\Seeders;

use App\Models\SpinSegment;
use Illuminate\Database\Seeder;

class SpinSegmentSeeder extends Seeder
{
    /**
     * Higher-value/higher-risk wedges that only unlock for players who've
     * actually progressed, so the wheel gets more interesting (bigger
     * upside, bigger downside) as you level up.
     *
     * Rebalanced (Aug 2026) alongside the base pool below — trimmed from 6
     * to 2 wedges and re-weighted so the jackpot doesn't dominate: at
     * min_level 6+, the fine's weight×magnitude slightly outweighs the
     * jackpot's, so reaching this tier isn't just "more free money."
     */
    private const HIGH_LEVEL_SEGMENTS = [
        ['label' => 'Ksh 20,000 Jackpot', 'emoji' => '🎊', 'color' => '#ca8a04', 'type' => 'balance', 'value' => 20000, 'weight' => 2, 'tier' => 'great', 'min_level' => 6],
        ['label' => 'Ksh 7,000 Fine',     'emoji' => '🥶', 'color' => '#7f1d1d', 'type' => 'balance', 'value' => -7000, 'weight' => 6, 'tier' => 'bad',   'min_level' => 6],
    ];

    /**
     * Labels from the pre-rebalance roster that are no longer part of the
     * curated set above. Deactivated rather than deleted -- SpinResult logs
     * store a denormalized snapshot of the prize at spin time (not a
     * foreign key), so this is safe, and it stays reversible via the
     * GameSet Spin Wheel admin page's Show/Hide toggle if ever wanted back.
     */
    private const RETIRED_LABELS = [
        'Ksh 2,500', 'Ksh 5,000', 'Ksh 10,000', 'Ksh 4,000 Fine', '1,500 XP',
        'Ksh 3,000', 'Ksh 7,500', 'Lucky Badge', 'Ksh 1,000', 'Ksh 6,000',
        'Ksh 15,000', 'Ksh 800 Fine', 'Ksh 2,800 Fine', '+50 Credit', '-40 Credit',
        '500 XP', '3,000 XP',
        'Ksh 20,000', 'Ksh 30,000 Jackpot', '+75 Credit Surge', '5,000 XP Boost',
        'Ksh 5,500 Fine', 'Ksh 9,000 Fine',
    ];

    /** Seeds the current wheel roster plus the level-gated additions — safe to re-run (keyed by label). */
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

        SpinSegment::whereIn('label', self::RETIRED_LABELS)->update(['is_active' => false]);
    }
}
