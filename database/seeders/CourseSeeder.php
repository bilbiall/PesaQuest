<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $courses = [
            [
                'title'          => 'Communication Basics',
                'slug'           => 'communication-basics',
                'description'    => 'Master professional communication — emails, presentations, and client calls. The single skill every employer asks for on day one.',
                'icon'           => '💬',
                'career_track'   => 'creative',
                'color'          => '#A78BFA',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 2,
                'outcome'        => 'Unlocks: Content Creator, Customer Service Rep',
                'is_active'      => true,
            ],
            [
                'title'          => 'Digital Marketing 101',
                'slug'           => 'digital-marketing-101',
                'description'    => 'Social media strategy, SEO, and paid ads — learn how brands grow online and how you can monetize those same skills from your phone.',
                'icon'           => '📣',
                'career_track'   => 'business',
                'color'          => '#FF6B35',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 3,
                'outcome'        => 'Unlocks: Social Media Manager, Market Researcher',
                'is_active'      => true,
            ],
            [
                'title'          => 'Hustle Basics',
                'slug'           => 'hustle-basics',
                'description'    => 'From freelancing to side-gigs: pricing your work, finding clients, and managing your time when you are both the boss and the employee.',
                'icon'           => '⚡',
                'career_track'   => 'business',
                'color'          => '#FFBC00',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 2,
                'outcome'        => 'Unlocks: Data Entry Clerk, Market Researcher',
                'is_active'      => true,
            ],
            [
                'title'          => 'Financial Literacy 101',
                'slug'           => 'financial-literacy-101',
                'description'    => 'Budgeting, saving, compound interest, and M-Pesa literacy. The foundation every Kenyan youth needs before their first payslip.',
                'icon'           => '📊',
                'career_track'   => 'finance',
                'color'          => '#15C77E',
                'cost_kes'       => 0,
                'is_free'        => true,
                'duration_hours' => 2,
                'outcome'        => 'Unlocks: M-Pesa Agent, Bank Teller',
                'is_active'      => true,
            ],
        ];

        foreach ($courses as $course) {
            CityCourse::updateOrCreate(['slug' => $course['slug']], $course);
        }
    }
}
