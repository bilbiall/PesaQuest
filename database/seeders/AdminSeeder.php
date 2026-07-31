<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@moski.org'],
            [
                'name'              => 'Moski Admin',
                'password'          => Hash::make('admin123'),
                'date_of_birth'     => '1990-01-01',
                'age_group'         => '26+',
                'is_admin'          => true,
                'is_gameset'        => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
