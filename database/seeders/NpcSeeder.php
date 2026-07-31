<?php

namespace Database\Seeders;

use App\Models\Npc;
use Illuminate\Database\Seeder;

class NpcSeeder extends Seeder
{
    public function run(): void
    {
        $npcs = [
            [
                'name'                 => 'Kamau Njoroge',
                'nickname'             => 'Kamau',
                'role'                 => 'friend',
                'cover_color'          => '#6366f1',
                'avatar_url'           => 'https://ui-avatars.com/api/?name=KN&background=6366f1&color=fff&bold=true&size=200&rounded=true',
                'description'          => 'Your best friend from school. Loud, funny, always broke — but has your back when it matters.',
                'personality'          => 'Funny and loyal, but financially reckless. Borrows money often, sometimes pays back.',
                'initial_relationship' => 65,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Njeri Wanjiku',
                'nickname'             => 'Njeri',
                'role'                 => 'friend',
                'cover_color'          => '#ec4899',
                'avatar_url'           => 'https://ui-avatars.com/api/?name=NW&background=ec4899&color=fff&bold=true&size=200&rounded=true',
                'description'          => 'Your ambitious friend. She bought a new car last month. Her Instagram is full of trips and restaurants you can\'t afford.',
                'personality'          => 'Status-conscious, creates social pressure and FOMO. Means well but lifestyle inflation is real with her.',
                'initial_relationship' => 55,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Mama Grace',
                'nickname'             => 'Mama',
                'role'                 => 'parent',
                'cover_color'          => '#f59e0b',
                'avatar_url'           => 'https://ui-avatars.com/api/?name=MG&background=f59e0b&color=fff&bold=true&size=200&rounded=true',
                'description'          => 'Your mother. She prays every morning, sends airtime randomly, and occasionally drops a surprise M-Pesa. Also needs help with school fees and shopping.',
                'personality'          => 'Supportive and loving. Has real financial needs — school fees, unga, medical. Occasionally blesses you financially too.',
                'initial_relationship' => 80,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Mr. Ochieng',
                'nickname'             => 'The Boss',
                'role'                 => 'boss',
                'cover_color'          => '#475569',
                'avatar_url'           => 'https://ui-avatars.com/api/?name=OC&background=475569&color=fff&bold=true&size=200&rounded=true',
                'description'          => 'Your manager at work. Professional, demanding, and holds the keys to your salary increments. Can make or break your career.',
                'personality'          => 'Fair but demanding. Offers overtime, gives performance reviews. A good relationship = promotions. A bad one = PIP.',
                'initial_relationship' => 50,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Aisha Mwangi',
                'nickname'             => 'Aisha',
                'role'                 => 'investor',
                'cover_color'          => '#10b981',
                'avatar_url'           => 'https://ui-avatars.com/api/?name=AM&background=10b981&color=fff&bold=true&size=200&rounded=true',
                'description'          => 'The hustler in your circle. Runs two businesses, always has a new investment tip. Some of her tips are gold — others are dodgy pyramid schemes.',
                'personality'          => 'Resourceful entrepreneur. Tips range from legitimate (SACCOs, NSE) to risky (pyramid schemes, crypto pump-and-dump). You have to know the difference.',
                'initial_relationship' => 60,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Bwana Kariuki',
                'nickname'             => 'Landlord',
                'role'                 => 'landlord',
                'cover_color'          => '#dc2626',
                'avatar_url'           => 'https://ui-avatars.com/api/?name=BK&background=dc2626&color=fff&bold=true&size=200&rounded=true',
                'description'          => 'Your landlord. Owns several rental properties in the estate. Business-minded and unsentimental about rent.',
                'personality'          => 'Transactional and firm. Raises rent annually. Doesn\'t accept excuses for late payment. Occasionally offers the unit for sale.',
                'initial_relationship' => 40,
                'is_active'            => true,
            ],
            [
                'name'                 => 'Uncle Mwangi',
                'nickname'             => 'Uncle',
                'role'                 => 'relative',
                'cover_color'          => '#7c3aed',
                'avatar_url'           => 'https://ui-avatars.com/api/?name=UM&background=7c3aed&color=fff&bold=true&size=200&rounded=true',
                'description'          => 'Your wealthy uncle. He made money in the 90s and has opinions about everything. Has real investment opportunities — and some that are clearly just schemes.',
                'personality'          => 'Well-meaning but unpredictable. Brings family financial pressure, harambee requests, and "sure deals." Teaches you to filter advice.',
                'initial_relationship' => 60,
                'is_active'            => true,
            ],
        ];

        foreach ($npcs as $npc) {
            Npc::updateOrCreate(['name' => $npc['name']], $npc);
        }

        $this->command->info('NpcSeeder: ' . count($npcs) . ' NPCs seeded.');
    }
}
