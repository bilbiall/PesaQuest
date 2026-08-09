<?php

namespace Database\Seeders;

use App\Models\Dream;
use Illuminate\Database\Seeder;

class DreamSeeder extends Seeder
{
    public function run(): void
    {
        $img = fn (string $file) => asset('img/trophies/' . $file);

        $dreams = [
            ['slug' => 'trophy-room',      'name' => 'Trophy Room',          'tagline' => 'A shelf for every win',            'icon' => '🏆', 'image_url' => $img('trophy-cup.svg'),    'price' => 1_000_000,  'category' => 'lifestyle', 'min_level' => 3],
            ['slug' => 'diamond-crown',    'name' => 'Diamond Crown',        'tagline' => 'Pesa City royalty',                'icon' => '👑', 'image_url' => $img('crown.svg'),         'price' => 2_500_000,  'category' => 'lifestyle', 'min_level' => 4],
            ['slug' => 'world-tour',       'name' => 'Round-the-World Tour', 'tagline' => 'Every continent, no budget left behind', 'icon' => '🌍', 'image_url' => $img('dream-travel.svg'), 'price' => 3_000_000,  'category' => 'travel',    'min_level' => 5],
            ['slug' => 'penthouse-suite',  'name' => 'Penthouse Suite',      'tagline' => 'The whole skyline as your view',   'icon' => '🏙️', 'image_url' => $img('dream-house.svg'),   'price' => 6_000_000,  'category' => 'property',  'min_level' => 6],
            ['slug' => 'supercar-collection', 'name' => 'Supercar Collection', 'tagline' => 'One for every day of the week', 'icon' => '🏎️', 'image_url' => $img('dream-car.svg'),     'price' => 8_000_000,  'category' => 'vehicle',   'min_level' => 7],
            ['slug' => 'charitable-foundation', 'name' => 'Charitable Foundation', 'tagline' => 'Wealth that outlives you', 'icon' => '❤️', 'image_url' => $img('dream-legacy.svg'), 'price' => 12_000_000, 'category' => 'legacy',    'min_level' => 8],
            ['slug' => 'lakeside-mansion', 'name' => 'Lakeside Mansion',     'tagline' => 'Wake up to the water every morning', 'icon' => '🏡', 'image_url' => $img('dream-house.svg'), 'price' => 15_000_000, 'category' => 'property',  'min_level' => 8],
            ['slug' => 'business-empire',  'name' => 'Global Business Empire', 'tagline' => 'Your name on buildings, not just paychecks', 'icon' => '🏢', 'image_url' => $img('dream-business.svg'), 'price' => 20_000_000, 'category' => 'business', 'min_level' => 9],
            ['slug' => 'luxury-yacht',     'name' => 'Luxury Yacht',         'tagline' => 'International waters, zero worries', 'icon' => '🛥️', 'image_url' => $img('dream-yacht.svg'), 'price' => 25_000_000, 'category' => 'lifestyle', 'min_level' => 9],
            ['slug' => 'private-jet',      'name' => 'Private Jet',         'tagline' => 'Nairobi to anywhere, on your schedule', 'icon' => '✈️', 'image_url' => $img('dream-jet.svg'), 'price' => 40_000_000, 'category' => 'travel',    'min_level' => 10],
        ];

        foreach ($dreams as $i => $d) {
            Dream::updateOrCreate(
                ['slug' => $d['slug']],
                $d + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
