<?php

namespace Database\Seeders;

use App\Models\FunWorldActivity;
use Illuminate\Database\Seeder;

class FunWorldActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            ['name' => 'Cinema Night',        'icon' => '🎬', 'description' => 'Catch the latest blockbuster at the mall cinema.',            'price' => 800,  'mood_boost_base' => 8,  'xp_reward' => 15, 'sort_order' => 1],
            ['name' => 'Nyama Choma Hangout', 'icon' => '🍖', 'description' => 'Grilled meat and good vibes with friends at the local joint.', 'price' => 1200, 'mood_boost_base' => 10, 'xp_reward' => 18, 'sort_order' => 2],
            ['name' => 'Gaming Lounge',       'icon' => '🎮', 'description' => 'Two hours of FIFA and racing games at the gaming café.',       'price' => 500,  'mood_boost_base' => 6,  'xp_reward' => 12, 'sort_order' => 3],
            ['name' => 'Gym Session',         'icon' => '💪', 'description' => 'A solid workout — good for the body and the mind.',            'price' => 400,  'mood_boost_base' => 6,  'xp_reward' => 12, 'sort_order' => 4],
            ['name' => 'Live Music Gig',      'icon' => '🎵', 'description' => 'Local bands and gengetone night at the club downtown.',        'price' => 1500, 'mood_boost_base' => 12, 'xp_reward' => 20, 'sort_order' => 5],
            ['name' => 'Beach Day Trip',      'icon' => '🏖️', 'description' => 'Matatu to the coast, sun, sand and swimming all day.',        'price' => 3000, 'mood_boost_base' => 18, 'xp_reward' => 28, 'sort_order' => 6],
            ['name' => 'Spa & Self-Care',     'icon' => '💆', 'description' => 'Massage and self-care afternoon. You earned it.',              'price' => 2500, 'mood_boost_base' => 15, 'xp_reward' => 24, 'sort_order' => 7],
            ['name' => 'Road Trip Weekend',   'icon' => '🚙', 'description' => 'Fuel up and explore — Naivasha, Nanyuki or the Rift Valley.',  'price' => 5000, 'mood_boost_base' => 25, 'xp_reward' => 35, 'sort_order' => 8],
            ['name' => 'Street Food Crawl',   'icon' => '🌽', 'description' => 'Mutura, smokies, roast maize — a tour of the best street eats.', 'price' => 200, 'mood_boost_base' => 5, 'xp_reward' => 10, 'sort_order' => 9],
            ['name' => 'Karura Forest Walk',  'icon' => '🌳', 'description' => 'Nature walk and picnic in the forest. Cheap therapy.',          'price' => 300,  'mood_boost_base' => 6,  'xp_reward' => 12, 'sort_order' => 10],

            // The list above never grew past level 1 — a level-10 player with a
            // fat balance saw the same Ksh 5,000 road trip as their ceiling.
            // These unlock progressively so Fun World scales with progress.
            ['name' => 'Weekend Glamping Safari', 'icon' => '⛺', 'description' => 'A guided overnight safari glamp near Amboseli — game drive included.', 'price' => 9000,  'mood_boost_base' => 22, 'xp_reward' => 30, 'sort_order' => 11, 'min_level' => 4],
            ['name' => 'VIP Concert Table',        'icon' => '🎤', 'description' => 'Front-row table service at a major Nairobi concert.',                  'price' => 12000, 'mood_boost_base' => 25, 'xp_reward' => 32, 'sort_order' => 12, 'min_level' => 5],
            ['name' => 'Diani Beach Weekend',      'icon' => '🌴', 'description' => 'Fly to the coast — two nights at a beachfront resort in Diani.',        'price' => 25000, 'mood_boost_base' => 25, 'xp_reward' => 45, 'sort_order' => 13, 'min_level' => 6],
            ['name' => 'Private Chef Dinner',      'icon' => '👨‍🍳', 'description' => 'A private chef cooks a five-course meal at home for you and friends.',   'price' => 18000, 'mood_boost_base' => 25, 'xp_reward' => 38, 'sort_order' => 14, 'min_level' => 7],
            ['name' => 'Maasai Mara Fly-In Safari','icon' => '🦁', 'description' => 'A two-night fly-in luxury safari camp in the Maasai Mara.',            'price' => 60000, 'mood_boost_base' => 25, 'xp_reward' => 55, 'sort_order' => 15, 'min_level' => 8],
            ['name' => 'International Getaway',    'icon' => '✈️', 'description' => 'A short international trip — Zanzibar, Dubai or Cape Town.',           'price' => 150000,'mood_boost_base' => 25, 'xp_reward' => 70, 'sort_order' => 16, 'min_level' => 10],
        ];

        foreach ($activities as $a) {
            FunWorldActivity::updateOrCreate(['name' => $a['name']], $a + ['min_level' => $a['min_level'] ?? 1, 'is_active' => true]);
        }
    }
}
