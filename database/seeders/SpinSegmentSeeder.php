<?php

namespace Database\Seeders;

use App\Models\SpinSegment;
use Illuminate\Database\Seeder;

class SpinSegmentSeeder extends Seeder
{
    /** Seeds the original hardcoded wheel — safe to re-run (keyed by label). */
    public function run(): void
    {
        foreach (SpinSegment::DEFAULTS as $i => $seg) {
            SpinSegment::updateOrCreate(
                ['label' => $seg['label']],
                $seg + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
