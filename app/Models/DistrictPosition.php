<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-calibrated world-map district geometry — one rectangle (in % of the
 * map image) per district slug. Both the tap-area on /world and the player
 * pin's destination (the rectangle's center) are derived from this single
 * source, so a remapped image only ever needs re-dragging here.
 */
class DistrictPosition extends Model
{
    protected $fillable = ['slug', 'pos_left', 'pos_top', 'pos_width', 'pos_height'];

    /** Sensible starting geometry — seeds the table and covers any district
     *  never dragged yet, so the map never renders a blank/missing zone. */
    public const DEFAULTS = [
        'opportunity-hub' => ['left' => 12, 'top' => 14, 'width' => 15, 'height' => 16],
        'workplace'       => ['left' => 36, 'top' => 12, 'width' => 15, 'height' => 18],
        'marketplace'     => ['left' => 60, 'top' => 15, 'width' => 15, 'height' => 17],
        'fun-world'       => ['left' => 84, 'top' => 18, 'width' => 20, 'height' => 20],
        'car-yard'        => ['left' => 12, 'top' => 38, 'width' => 18, 'height' => 17],
        'quests'          => ['left' => 44, 'top' => 43, 'width' => 10, 'height' => 14],
        'bank'            => ['left' => 68, 'top' => 39, 'width' => 12, 'height' => 15],
        'community'       => ['left' => 60, 'top' => 55, 'width' => 12, 'height' => 16],
        'estates'         => ['left' => 26, 'top' => 60, 'width' => 25, 'height' => 25],
        'savings'         => ['left' => 79, 'top' => 70, 'width' => 19, 'height' => 19],
        // Approximates the previously-empty building the admin pointed out
        // beside Fun World — nudge with the GameSet World calibrator if needed.
        'champions-court' => ['left' => 90, 'top' => 28, 'width' => 14, 'height' => 16],
    ];

    /** Every district slug's rectangle, as floats (DB rows override defaults). */
    public static function allBySlug(): array
    {
        $rows = Schema::hasTable('district_positions')
            ? static::all()->keyBy('slug')
            : collect();

        $out = [];
        foreach (self::DEFAULTS as $slug => $rect) {
            $row = $rows->get($slug);
            $out[$slug] = $row
                ? ['left' => (float) $row->pos_left, 'top' => (float) $row->pos_top, 'width' => (float) $row->pos_width, 'height' => (float) $row->pos_height]
                : $rect;
        }
        // Any slug saved in the DB but not (yet) in DEFAULTS — e.g. a brand new district — still surfaces.
        foreach ($rows as $slug => $row) {
            if (!isset($out[$slug])) {
                $out[$slug] = ['left' => (float) $row->pos_left, 'top' => (float) $row->pos_top, 'width' => (float) $row->pos_width, 'height' => (float) $row->pos_height];
            }
        }

        return $out;
    }

    /** Center point of a rectangle — where the player pin walks to. */
    public static function centerOf(array $rect): array
    {
        return [
            'left' => round($rect['left'] + $rect['width'] / 2, 1),
            'top'  => round($rect['top'] + $rect['height'] / 2, 1),
        ];
    }
}
