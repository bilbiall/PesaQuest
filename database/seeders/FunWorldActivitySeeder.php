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
        ];

        foreach ($activities as $a) {
            FunWorldActivity::updateOrCreate(['name' => $a['name']], $a + ['is_active' => true]);
        }
    }
}
