<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Converts every seeded emoji in the icon columns of assets, quests, badges,
 * shares and investment_deals to a name from the <x-icon> component's set,
 * so these five catalogs render real icons instead of emoji everywhere they
 * appear (cards, quest board, badge shelf, Equity Square, deal board).
 *
 * Several distinct emoji intentionally collapse onto the same icon name
 * (every vehicle body style onto 'car', both agricultural shares onto
 * 'leaf', etc.) — a handful of closely related real-world variants sharing
 * one icon reads fine in practice and keeps the icon set from ballooning.
 * That collapse also means this migration can't be perfectly reversed back
 * to the original specific emoji per row, so down() is a documented no-op
 * rather than a lossy best-effort guess.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->applyMap('assets', [
            '🏍️' => 'motorcycle', '🚗' => 'car', '🚌' => 'bus', '🚙' => 'car',
            '🚘' => 'car', '🏎️' => 'car', '🌍' => 'land', '🏠' => 'house',
            '🏢' => 'building', '🏡' => 'house', '🏪' => 'store', '🥬' => 'store',
            '🖥️' => 'monitor', '📱' => 'phone', '💈' => 'scissors', '⛽' => 'fuel',
            '🚀' => 'rocket', '💰' => 'coin', '🏛️' => 'bank', '📜' => 'scroll',
            '📈' => 'trend-up', '🤝' => 'handshake', '🏗️' => 'crane', '💎' => 'diamond',
            '💻' => 'laptop', '🛺' => 'motorcycle', '🛻' => 'car', '🏭' => 'factory',
            '🛎️' => 'bell', '🐔' => 'bird', '👗' => 'shirt', '🚿' => 'droplet',
            '🔧' => 'wrench', '📊' => 'bar-chart', '📃' => 'document', '🥇' => 'medal',
            '🚁' => 'drone', '📦' => 'store',
        ]);

        $this->applyMap('quests', [
            '🐷' => 'piggy-bank', '📝' => 'pencil', '📱' => 'phone', '💼' => 'briefcase',
            '📊' => 'bar-chart', '🏦' => 'bank', '🤝' => 'handshake', '📉' => 'trend-down',
            '📋' => 'clipboard', '📈' => 'trend-up', '🏠' => 'house', '🏙️' => 'city',
            '🛒' => 'cart', '💰' => 'coin', '🚀' => 'rocket', '🪙' => 'coin',
            '🛵' => 'motorcycle', '💻' => 'laptop', '🏪' => 'store', '📣' => 'megaphone',
            '🗣️' => 'speech', '📒' => 'clipboard', '🧾' => 'receipt', '📚' => 'book',
            '🏗️' => 'crane', '🔑' => 'key', '🎡' => 'spin', '🎯' => 'target',
        ]);

        $this->applyMap('badges', [
            '👣' => 'footprint', '🐷' => 'piggy-bank', '💡' => 'bulb', '📊' => 'bar-chart',
            '📈' => 'trend-up', '🏗️' => 'crane', '🏆' => 'trophy', '🧠' => 'brain',
            '⭐' => 'star', '📱' => 'phone', '🎓' => 'graduation', '💼' => 'briefcase',
        ]);

        $this->applyMap('shares', [
            '📱' => 'phone', '🏦' => 'bank', '🍺' => 'drink', '🚬' => 'factory',
            '✈️' => 'plane', '🧱' => 'factory', '⚡' => 'bolt', '🛡️' => 'shield',
            '🍃' => 'leaf', '🌱' => 'leaf',
        ]);

        $this->applyMap('investment_deals', [
            '📊' => 'bar-chart', '🪙' => 'coin', '🏗️' => 'crane', '🍳' => 'pan',
            '💼' => 'briefcase',
        ]);
    }

    private function applyMap(string $table, array $map): void
    {
        foreach ($map as $emoji => $iconName) {
            DB::table($table)->where('icon', $emoji)->update(['icon' => $iconName]);
        }
    }

    public function down(): void
    {
        // Intentionally irreversible — see class docblock. Restore from a
        // database backup taken before this migration if you need the
        // original emoji back.
    }
};
