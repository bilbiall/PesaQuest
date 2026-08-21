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
            ShareSeeder::class,
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
            ShareNewsTemplateSeeder::class,
            Level4to10Content8to12Seeder::class,
            Level4to10Content13to17Seeder::class,
            Level4to10Content18to25Seeder::class,
            Level4to10Content26PlusSeeder::class,
            QuestExpansion8to12Band1Seeder::class,
            QuestExpansion8to12Band2Seeder::class,
            QuestExpansion8to12Band3Seeder::class,
            QuestExpansion13to17Band1Seeder::class,
            QuestExpansion13to17Band2Seeder::class,
            QuestExpansion13to17Band3Seeder::class,
            QuestExpansion18to25Band1Seeder::class,
            QuestExpansion18to25Band2Seeder::class,
            QuestExpansion18to25Band3Seeder::class,
            QuestExpansion26PlusBand1Seeder::class,
            QuestExpansion26PlusBand2Seeder::class,
            QuestExpansion26PlusBand3Seeder::class,
        ]);
    }
}
