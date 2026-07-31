<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketEvent;

class MarketEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            // 8-12
            ['age_group' => '8-12', 'title' => 'Pocket Money Bonus!', 'description' => 'Your aunt gave you extra pocket money for helping at home!', 'effect_type' => 'bonus', 'effect_amount' => 50, 'icon' => '💰', 'probability' => 20],
            ['age_group' => '8-12', 'title' => 'School Trip Fee', 'description' => 'A school trip was announced. You need to contribute Ksh 30.', 'effect_type' => 'penalty', 'effect_amount' => 30, 'icon' => '🏫', 'probability' => 15],
            ['age_group' => '8-12', 'title' => 'Found Money!', 'description' => 'You found Ksh 20 on the ground near the market!', 'effect_type' => 'bonus', 'effect_amount' => 20, 'icon' => '🎉', 'probability' => 10],

            // 13-17
            ['age_group' => '13-17', 'title' => 'M-Pesa Promotion', 'description' => 'Safaricom gave you a free Ksh 50 airtime bonus for being an active user!', 'effect_type' => 'bonus', 'effect_amount' => 50, 'icon' => '📱', 'probability' => 25],
            ['age_group' => '13-17', 'title' => 'Matatu Fare Increase', 'description' => 'Matatu fares went up by Ksh 10 per trip this week.', 'effect_type' => 'penalty', 'effect_amount' => 70, 'icon' => '🚐', 'probability' => 20],
            ['age_group' => '13-17', 'title' => 'Side Hustle Win', 'description' => 'A neighbor paid you Ksh 150 to wash their car!', 'effect_type' => 'bonus', 'effect_amount' => 150, 'icon' => '💪', 'probability' => 15],
            ['age_group' => '13-17', 'title' => 'Unga Price Rise', 'description' => 'Food prices went up. Your household spending increased by Ksh 100.', 'effect_type' => 'penalty', 'effect_amount' => 100, 'icon' => '🌽', 'probability' => 20],

            // 18-25
            ['age_group' => '18-25', 'title' => 'Fuliza Offer', 'description' => 'Safaricom offered you a Fuliza limit increase. You resisted temptation — smart!', 'effect_type' => 'bonus', 'effect_amount' => 0, 'icon' => '🧠', 'probability' => 30],
            ['age_group' => '18-25', 'title' => 'Rent Due Early', 'description' => 'Your landlord asked for rent three days early this month.', 'effect_type' => 'penalty', 'effect_amount' => 500, 'icon' => '🏠', 'probability' => 15],
            ['age_group' => '18-25', 'title' => 'Chama Dividend', 'description' => 'Your chama distributed monthly dividends — Ksh 300 to your wallet!', 'effect_type' => 'bonus', 'effect_amount' => 300, 'icon' => '🤝', 'probability' => 20],
            ['age_group' => '18-25', 'title' => 'Fuel Prices Rise', 'description' => 'Petrol increased by Ksh 8/litre. Transport costs up by Ksh 200.', 'effect_type' => 'penalty', 'effect_amount' => 200, 'icon' => '⛽', 'probability' => 25],
            ['age_group' => '18-25', 'title' => 'Freelance Bonus', 'description' => 'A client paid you Ksh 500 for a small design job!', 'effect_type' => 'bonus', 'effect_amount' => 500, 'icon' => '💻', 'probability' => 15],

            // 26+
            ['age_group' => '26+', 'title' => 'KRA Tax Rebate', 'description' => 'Your tax returns came in — Ksh 1,200 back in your account!', 'effect_type' => 'bonus', 'effect_amount' => 1200, 'icon' => '🏛️', 'probability' => 10],
            ['age_group' => '26+', 'title' => 'Medical Bill', 'description' => 'A family member needed urgent medical attention. Ksh 800 from your savings.', 'effect_type' => 'penalty', 'effect_amount' => 800, 'icon' => '🏥', 'probability' => 15],
            ['age_group' => '26+', 'title' => 'NSE Market Rally', 'description' => 'Nairobi Stock Exchange had a good day — your portfolio rose by Ksh 600!', 'effect_type' => 'bonus', 'effect_amount' => 600, 'icon' => '📈', 'probability' => 15],
            ['age_group' => '26+', 'title' => 'KPLC Token Price Up', 'description' => 'Electricity tariffs increased. Extra Ksh 400 electricity bill this month.', 'effect_type' => 'penalty', 'effect_amount' => 400, 'icon' => '💡', 'probability' => 20],
            ['age_group' => '26+', 'title' => 'Business Deal Closed', 'description' => 'You closed a deal — Ksh 2,000 commission in your pocket!', 'effect_type' => 'bonus', 'effect_amount' => 2000, 'icon' => '🤑', 'probability' => 12],
        ];

        foreach ($events as $e) {
            MarketEvent::firstOrCreate(
                ['title' => $e['title'], 'age_group' => $e['age_group']],
                array_merge($e, ['is_active' => true])
            );
        }
    }
}
