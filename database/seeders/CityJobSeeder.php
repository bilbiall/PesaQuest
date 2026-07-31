<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use App\Models\CityJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CityJobSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $comm    = CityCourse::where('slug', 'communication-basics')->first()?->id;
        $digital = CityCourse::where('slug', 'digital-marketing-101')->first()?->id;
        $hustle  = CityCourse::where('slug', 'hustle-basics')->first()?->id;
        $finlit  = CityCourse::where('slug', 'financial-literacy-101')->first()?->id;

        // Salaries tuned so each age group keeps ~KES 15k/month after its bills
        // (8-12 ≈1.9k bills, 13-17 ≈3.8k, 18-25 ≈11.45k, 26+ ≈28k).
        $jobs = [
            [
                'title'              => 'Customer Service Rep',
                'employer_name'      => 'Safaricom',
                'employer_logo'      => '📞',
                'career_track'       => 'creative',
                'salary_kes_month'   => 26500,
                'level'              => 1,
                'required_course_id' => $comm,
                'is_active'          => true,
                'ages'               => ['18-25'],
            ],
            [
                'title'              => 'Social Media Manager',
                'employer_name'      => 'Jumia Kenya',
                'employer_logo'      => '🛒',
                'career_track'       => 'business',
                'salary_kes_month'   => 43000,
                'level'              => 2,
                'required_course_id' => $digital,
                'is_active'          => true,
                'ages'               => ['26+'],
            ],
            [
                'title'              => 'Data Entry Clerk',
                'employer_name'      => 'KCB Group',
                'employer_logo'      => '🏦',
                'career_track'       => 'business',
                'salary_kes_month'   => 26000,
                'level'              => 1,
                'required_course_id' => $hustle,
                'is_active'          => true,
                'ages'               => ['18-25'],
            ],
            [
                'title'              => 'Market Researcher',
                'employer_name'      => 'Unilever EA',
                'employer_logo'      => '🔬',
                'career_track'       => 'business',
                'salary_kes_month'   => 44000,
                'level'              => 2,
                'required_course_id' => $digital,
                'is_active'          => true,
                'ages'               => ['26+'],
            ],
            [
                'title'              => 'Content Creator',
                'employer_name'      => 'YouTube Kenya',
                'employer_logo'      => '🎬',
                'career_track'       => 'creative',
                'salary_kes_month'   => 19000,
                'level'              => 1,
                'required_course_id' => $comm,
                'is_active'          => true,
                'ages'               => ['13-17'],
            ],
            [
                'title'              => 'M-Pesa Agent',
                'employer_name'      => 'Equity Bank',
                'employer_logo'      => '💸',
                'career_track'       => 'finance',
                'salary_kes_month'   => 26500,
                'level'              => 1,
                'required_course_id' => $finlit,
                'is_active'          => true,
                'ages'               => ['18-25', '26+'],
            ],
        ];

        $hasAgesCol = \Illuminate\Support\Facades\Schema::hasColumn('city_jobs', 'age_groups');

        foreach ($jobs as $job) {
            $ages = $job['ages'];
            unset($job['ages']);

            $job['age_group'] = $ages[0];
            if ($hasAgesCol) {
                $job['age_groups'] = $ages;
            }

            // updateOrCreate so re-seeding on cPanel retunes existing rows
            // instead of duplicating them.
            CityJob::updateOrCreate(
                ['title' => $job['title'], 'employer_name' => $job['employer_name']],
                $job
            );
        }
    }
}
