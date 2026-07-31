<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * A player's own real-life expense categories — fully custom (add/rename/
 * remove), seeded from DEFAULTS on first use so the starting set (Food,
 * Transport, ...) exists as real, editable/deletable rows rather than a
 * hardcoded list nobody can change.
 */
class UserExpenseCategory extends Model
{
    protected $fillable = ['user_id', 'key', 'label', 'icon', 'sort_order'];

    const DEFAULTS = [
        ['key' => 'food',      'label' => 'Food',           'icon' => '🍲'],
        ['key' => 'transport', 'label' => 'Transport',      'icon' => '🚌'],
        ['key' => 'airtime',   'label' => 'Airtime / Data', 'icon' => '📱'],
        ['key' => 'clothes',   'label' => 'Clothes',        'icon' => '👕'],
        ['key' => 'school',    'label' => 'School',         'icon' => '📚'],
        ['key' => 'fun',       'label' => 'Fun',             'icon' => '🎮'],
        ['key' => 'health',    'label' => 'Health',          'icon' => '💊'],
        ['key' => 'other',     'label' => 'Other',           'icon' => '📋'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): Collection
    {
        $existing = static::where('user_id', $userId)->orderBy('sort_order')->orderBy('id')->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        return collect(self::DEFAULTS)->map(fn ($d, $i) => static::create([
            'user_id'    => $userId,
            'key'        => $d['key'],
            'label'      => $d['label'],
            'icon'       => $d['icon'],
            'sort_order' => $i,
        ]));
    }

    /**
     * key => ['id'=>..,'label'=>..,'icon'=>..] map for quick lookups (payloads,
     * validation). `id` is the real primary key — needed by the frontend for
     * edit/delete URLs, since `key` is only unique per-user, not globally.
     */
    public static function mapForUser(int $userId): array
    {
        return static::forUser($userId)->keyBy('key')->map(fn ($c) => [
            'id'    => $c->id,
            'label' => $c->label,
            'icon'  => $c->icon,
        ])->all();
    }
}
