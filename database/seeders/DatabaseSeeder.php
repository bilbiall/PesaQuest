<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            NodeSeeder::class,
            BadgeSeeder::class,
            AdminSeeder::class,
            SampleUsersSeeder::class,
            ErrandScenarioSeeder::class,
            StorySeeder::class,
            BillSeeder::class,
            AssetSeeder::class,
            LifeEventSeeder::class,
            MarketEventSeeder::class,
            FunWorldActivitySeeder::class,
            BulkScenarioSeeder::class,
            AdultScenariosSeeder::class,
            AssetLifeEventsSeeder::class,
            BrandGadgetSeeder::class,
            NpcSeeder::class,
            LifeDecisionSeeder::class,
            // Pesa City world map
            MissionSeeder::class,
            CourseSeeder::class,
            CityJobSeeder::class,
            EventMetaSeeder::class,
            CareerEventSeeder::class,
            PesaCityOrientationSeeder::class,
            Level1QuestSeeder::class,
            Level123ContentSeeder::class,
            SpinSegmentSeeder::class,
            DreamSeeder::class,
            ChallengeTemplateSeeder::class,
        ]);
    }
}
