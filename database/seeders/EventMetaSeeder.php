<?php

namespace Database\Seeders;

use App\Models\Node;
use Illuminate\Database\Seeder;

/**
 * EventMetaSeeder — tags existing scenario nodes with event metadata
 * so the EventEngine can categorize, filter, and show them in Pesa City.
 *
 * Tags added into each node's `metadata` JSON field:
 *   - event_type           : 'opportunity' | 'cost' | 'career' | 'asset'
 *   - career_track         : matches UserProgress.career_field (or null = universal)
 *   - required_asset_category : 'property' | 'vehicle' | 'stock' | null
 *   - min_chapter          : 1 (student) to 6 (elder)
 *   - is_dismissable       : bool
 *
 * Run: php artisan db:seed --class=EventMetaSeeder
 *
 * Safe to re-run — uses array_merge so it won't overwrite unrelated metadata.
 */
class EventMetaSeeder extends Seeder
{
    // Map of partial title keywords → event metadata tags.
    // Order matters: first match wins. Keep specific keywords before generic ones.
    private const TAG_MAP = [
        // ── Career events ─────────────────────────────────────────────
        'tutoring'      => ['event_type' => 'career', 'career_track' => 'education',   'min_chapter' => 2],
        'conference'    => ['event_type' => 'career', 'career_track' => 'education',   'min_chapter' => 3],
        'consulting'    => ['event_type' => 'career', 'career_track' => 'engineering', 'min_chapter' => 3],
        'certification' => ['event_type' => 'cost',   'career_track' => 'engineering', 'min_chapter' => 2],
        'freelance'     => ['event_type' => 'opportunity', 'career_track' => 'technology', 'min_chapter' => 2],
        'diagnosis'     => ['event_type' => 'opportunity', 'career_track' => 'healthcare', 'min_chapter' => 3],
        'license'       => ['event_type' => 'cost',   'career_track' => 'healthcare',  'min_chapter' => 3],
        'referral'      => ['event_type' => 'opportunity', 'career_track' => 'business',  'min_chapter' => 2],
        'commission'    => ['event_type' => 'opportunity', 'career_track' => 'business',  'min_chapter' => 2],
        'bonus'         => ['event_type' => 'opportunity', 'min_chapter' => 2],

        // ── Asset events ──────────────────────────────────────────────
        'rental income' => ['event_type' => 'asset', 'required_asset_category' => 'property', 'min_chapter' => 4],
        'maintenance'   => ['event_type' => 'cost',  'required_asset_category' => 'property', 'min_chapter' => 4],
        'dividend'      => ['event_type' => 'asset', 'required_asset_category' => 'stock',    'min_chapter' => 3],
        'market'        => ['event_type' => 'asset', 'required_asset_category' => 'stock',    'min_chapter' => 3],
        'repair'        => ['event_type' => 'cost',  'required_asset_category' => 'vehicle',  'min_chapter' => 3],
        'fuel'          => ['event_type' => 'cost',  'required_asset_category' => 'vehicle',  'min_chapter' => 2],
        'transport'     => ['event_type' => 'opportunity', 'career_track' => 'business', 'min_chapter' => 2],

        // ── Opportunity events (universal) ────────────────────────────
        'scholarship'   => ['event_type' => 'opportunity', 'min_chapter' => 1],
        'cashback'      => ['event_type' => 'opportunity', 'min_chapter' => 1],
        'side hustle'   => ['event_type' => 'opportunity', 'min_chapter' => 2],
        'gig'           => ['event_type' => 'opportunity', 'min_chapter' => 2],
        'business'      => ['event_type' => 'opportunity', 'min_chapter' => 3],
        'investment'    => ['event_type' => 'opportunity', 'min_chapter' => 3],
        'sale'          => ['event_type' => 'opportunity', 'min_chapter' => 2],

        // ── Cost events (universal) ───────────────────────────────────
        'medical'       => ['event_type' => 'cost', 'min_chapter' => 1],
        'hospital'      => ['event_type' => 'cost', 'min_chapter' => 1],
        'fine'          => ['event_type' => 'cost', 'min_chapter' => 1],
        'penalty'       => ['event_type' => 'cost', 'min_chapter' => 2],
        'emergency'     => ['event_type' => 'cost', 'min_chapter' => 1],
        'phone'         => ['event_type' => 'cost', 'min_chapter' => 1],
        'utility'       => ['event_type' => 'cost', 'min_chapter' => 2],
        'loan'          => ['event_type' => 'cost', 'min_chapter' => 3],
    ];

    public function run(): void
    {
        $nodes = Node::where('type', 'scenario')
            ->where('is_start', true)
            ->get();

        $tagged = 0;

        foreach ($nodes as $node) {
            $tag = $this->matchTag($node->title, $node->scenario_text);
            if (!$tag) continue;

            $existing = $node->metadata ?? [];
            // Only set event fields — don't overwrite starting_balance or other existing keys
            if (!isset($existing['event_type'])) {
                $node->metadata = array_merge($existing, array_merge(
                    ['event_type' => 'opportunity', 'min_chapter' => 1, 'is_dismissable' => true],
                    $tag
                ));
                $node->save();
                $tagged++;
            }
        }

        $this->command->info("EventMetaSeeder: tagged {$tagged} of {$nodes->count()} scenario nodes.");
    }

    private function matchTag(string $title, string $text): ?array
    {
        $haystack = strtolower($title . ' ' . $text);

        foreach (self::TAG_MAP as $keyword => $meta) {
            if (str_contains($haystack, $keyword)) {
                return $meta;
            }
        }

        // Default: universal opportunity event (visible to everyone from chapter 1)
        return ['event_type' => 'opportunity', 'min_chapter' => 1, 'is_dismissable' => true];
    }
}
