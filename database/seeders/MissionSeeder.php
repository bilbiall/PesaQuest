<?php

namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $missions = [
            [
                'title'          => 'Get Connected',
                'slug'           => 'get-connected',
                'description'    => 'Every hustler needs a device. Head to Mama Mboga Market and buy your first phone — your gateway to gigs, M-Pesa, and the digital economy.',
                'icon'           => '📱',
                'district_slug'  => 'marketplace',
                'sequence_order' => 1,
                'requirements'   => ['type' => 'asset_category', 'value' => 'devices', 'deadline_game_days' => 30],
                'rewards'        => ['xp' => 250, 'kes' => 1000, 'badge_slug' => 'connected'],
                'badge_slug'     => 'connected',
                'is_active'      => true,
            ],
            [
                'title'          => 'Level Up Your Skills',
                'slug'           => 'level-up-skills',
                'description'    => 'Knowledge is capital. Walk to the Opportunity Hub and complete any free course — your certificate opens the door to Pesa City\'s job board.',
                'icon'           => '🎓',
                'district_slug'  => 'opportunity-hub',
                'sequence_order' => 2,
                'requirements'   => ['type' => 'course_completed', 'deadline_game_days' => 45],
                'rewards'        => ['xp' => 400, 'kes' => 0, 'badge_slug' => 'scholar'],
                'badge_slug'     => 'scholar',
                'is_active'      => true,
            ],
            [
                'title'          => 'First Hustle',
                'slug'           => 'first-hustle',
                'description'    => 'Time to clock in. Return to the Opportunity Hub, open the Jobs tab, and land your first position. Salary starts flowing on your next login.',
                'icon'           => '💼',
                'district_slug'  => 'opportunity-hub',
                'sequence_order' => 3,
                'requirements'   => ['type' => 'job_employed', 'deadline_game_days' => 60],
                'rewards'        => ['xp' => 500, 'kes' => 5000, 'badge_slug' => 'first-hustle'],
                'badge_slug'     => 'first-hustle',
                'is_active'      => true,
            ],
            [
                'title'          => 'First Investment',
                'slug'           => 'first-investment',
                'description'    => 'Money in a wallet earns nothing. Head to Mama Mboga Market, open the Shares tab, and buy your first investment asset. Watch your money work while you sleep.',
                'icon'           => '📈',
                'district_slug'  => 'marketplace',
                'sequence_order' => 4,
                'requirements'   => ['type' => 'asset_category', 'value' => 'investments', 'deadline_game_days' => 75],
                'rewards'        => ['xp' => 700, 'kes' => 10000, 'badge_slug' => 'investor'],
                'badge_slug'     => 'investor',
                'is_active'      => true,
            ],
            [
                'title'          => 'First Property',
                'slug'           => 'first-property',
                'description'    => 'Generational wealth starts with land. Visit Kiambu Estates and purchase your first property — a plot, a bedsitter, or a studio. Your first asset that appreciates while you sleep.',
                'icon'           => '🏠',
                'district_slug'  => 'estates',
                'sequence_order' => 5,
                'requirements'   => ['type' => 'asset_category', 'value' => 'property', 'deadline_game_days' => 90],
                'rewards'        => ['xp' => 1000, 'kes' => 25000, 'badge_slug' => 'property-owner'],
                'badge_slug'     => 'property-owner',
                'is_active'      => true,
            ],
            [
                'title'          => 'First Business',
                'slug'           => 'first-business',
                'description'    => 'You\'ve got income, assets, and skills. Now build something that earns beyond your own hours. Visit the Community Centre and register your first Pesa City business.',
                'icon'           => '🏪',
                'district_slug'  => 'community',
                'sequence_order' => 6,
                'requirements'   => ['type' => 'asset_category', 'value' => 'business', 'deadline_game_days' => 120],
                'rewards'        => ['xp' => 1500, 'kes' => 50000, 'badge_slug' => 'entrepreneur'],
                'badge_slug'     => 'entrepreneur',
                'is_active'      => true,
            ],
        ];

        foreach ($missions as $mission) {
            Mission::updateOrCreate(['slug' => $mission['slug']], $mission);
        }
    }
}
