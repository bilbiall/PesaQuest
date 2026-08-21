<?php

namespace Database\Seeders;

use App\Models\ChallengeTemplate;
use Illuminate\Database\Seeder;

class ChallengeTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $img = fn (string $file) => asset('img/trophies/' . $file);

        $templates = [
            [
                'key'         => 'net-worth-sprint',
                'name'        => 'Net Worth Sprint',
                'description' => 'Grow your net worth the fastest, starting from right now — existing savings don\'t count, only what you build during the challenge.',
                'metric'      => 'net_worth',
                'style'       => 'percent',
                'icon'        => '📈',
                'image_url'   => $img('medal-gold.svg'),
                'default_duration_days' => 7,
            ],
            [
                'key'         => 'savings-streak',
                'name'        => 'Savings Streak',
                'description' => 'The biggest jump in savings balance wins. A pure discipline race.',
                'metric'      => 'savings_balance',
                'style'       => 'percent',
                'icon'        => '💰',
                'image_url'   => $img('medal-silver.svg'),
                'default_duration_days' => 7,
            ],
            [
                'key'         => 'xp-race',
                'name'        => 'XP Race',
                'description' => 'Most XP earned during the challenge window takes the trophy.',
                'metric'      => 'xp_points',
                'style'       => 'amount',
                'icon'        => '⚡',
                'image_url'   => $img('badge-star.svg'),
                'default_duration_days' => 5,
            ],
            [
                'key'         => 'course-marathon',
                'name'        => 'Course Marathon',
                'description' => 'Complete the most courses before time runs out.',
                'metric'      => 'courses_completed',
                'style'       => 'count',
                'icon'        => '🎓',
                'image_url'   => $img('badge-shield.svg'),
                'default_duration_days' => 10,
            ],
            [
                'key'         => 'pesa-trail-showdown',
                'name'        => 'Pesa Trail Showdown',
                'description' => 'Most Pesa Trail wins before time runs out — every race counts, solo or multiplayer.',
                'metric'      => 'arcade_wins',
                'style'       => 'count',
                'icon'        => '🎲',
                'image_url'   => $img('trophy-cup.svg'),
                'default_duration_days' => 7,
            ],
            [
                'key'         => 'rivals-trail-payout',
                'name'        => 'Rivals Trail Payout',
                'description' => 'Biggest Pesa Trail winnings haul wins the trophy.',
                'metric'      => 'arcade_winnings',
                'style'       => 'amount',
                'icon'        => '💰',
                'image_url'   => $img('crown.svg'),
                'default_duration_days' => 7,
            ],
            [
                'key'         => 'shares-buying-spree',
                'name'        => 'Shares Buying Spree',
                'description' => 'Most individual share purchases during the challenge window wins — quantity of trades, not size, counts.',
                'metric'      => 'shares_bought',
                'style'       => 'count',
                'icon'        => '📊',
                'image_url'   => $img('medal-bronze.svg'),
                'default_duration_days' => 7,
            ],
            [
                'key'         => 'trading-profit-showdown',
                'name'        => 'Trading Profit Showdown',
                'description' => 'Whoever banks the biggest realized profit from selling shares wins. Losses count against you — trade smart, not just often.',
                'metric'      => 'shares_profit',
                'style'       => 'amount',
                'icon'        => '💹',
                'image_url'   => $img('medal-platinum.svg'),
                'default_duration_days' => 10,
            ],
        ];

        foreach ($templates as $t) {
            ChallengeTemplate::updateOrCreate(
                ['key' => $t['key']],
                $t + [
                    'level_min' => 1, 'level_max' => 99,
                    'allow_player_created' => true, 'allow_broadcast' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
