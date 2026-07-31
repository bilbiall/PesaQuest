<?php

namespace Database\Seeders;

use App\Models\CityCourse;
use Illuminate\Database\Seeder;

class PesaCityOrientationSeeder extends Seeder
{
    public function run(): void
    {
        // ── Intro orientation course (unlocks first quest) ────────────────────
        CityCourse::updateOrCreate(
            ['slug' => 'pesa-city-orientation'],
            [
                'title'         => 'Welcome to Pesa City',
                'icon'          => '🏙️',
                'description'   => 'Your first stop in Pesa City. Learn how the game works, what each district does, and how to start building real wealth from day one.',
                'intro_content' => "Pesa City is a virtual financial world where you grow your wealth by making smart decisions.\n\nYou'll take courses to unlock jobs, earn a salary, save money, invest in assets, and complete quests — all while learning real financial skills.\n\nThis short orientation explains how everything works so you can hit the ground running. Enroll to get started — it takes about 5 minutes.",
                'content'       => "🏙️ WELCOME TO PESA CITY\n═══════════════════════\n\nPesa City is your personal financial simulation. Every action you take mirrors real-world money decisions — and every good decision builds your wealth.\n\n\n📍 THE CITY DISTRICTS\n──────────────────────\nPesa City has 10 districts, each with a different financial purpose:\n\n• Opportunity Hub — Take courses and apply for jobs\n• Workplace — Manage your career and income\n• Bank & Savings — Open savings schemes and deposit money\n• Marketplace — Buy assets: phones, furniture, investments\n• Equity Square — Make investments and grow passive income\n• Estates — Buy or rent property\n• Car Yard — Purchase vehicles\n• Fun World — Spend wisely on lifestyle\n• Community — Join chamas and group savings\n• Quest Board — Track and complete your quests\n\nClick any district on the city map to visit it.\n\n\n💰 YOUR FINANCES\n──────────────────\nYou have three key financial numbers:\n\n1. Balance — Your spending money (KES in hand)\n2. Savings — Money locked in savings schemes\n3. Net Worth — Balance + savings + value of assets you own\n\nYour goal: grow all three over time.\n\n\n🎓 HOW TO EARN\n────────────────\nYou earn money in Pesa City through:\n• Job salaries — Get hired, earn monthly pay\n• Quest rewards — Complete quests for KES bonuses\n• Investment returns — Assets and deals that pay over time\n• Course XP — Each course you complete earns XP points\n\nXP points level you up, which unlocks higher-paying jobs and bigger quests.\n\n\n📋 QUESTS: YOUR MAIN DRIVER\n─────────────────────────────\nQuests are missions that guide your financial journey. Each quest has:\n• A goal (e.g. open a savings account, get hired)\n• An XP reward to level you up\n• A KES bonus deposited into your balance\n\nQuests complete automatically when you take the right action. No need to submit manually — just play!\n\nSome quests have multiple steps. You'll see progress notifications as you go.\n\n\n🏦 SAVINGS BASICS\n───────────────────\nHead to the Bank district to open a savings scheme. Once open:\n• Deposit money regularly to grow your savings\n• Hit savings milestones to unlock quest rewards\n• Watch your net worth climb\n\nRule of thumb: save at least 20% of every income you earn.\n\n\n💡 THREE THINGS TO DO FIRST\n─────────────────────────────\n1. Complete this course to earn your first XP\n2. Go to the Bank and open a savings account\n3. Visit the Opportunity Hub to enroll in a course and get a job\n\nReady? Scroll down and mark this course as complete to earn your XP reward and unlock your first quest!",
                'career_track'  => 'finance',
                'color'         => '#15C77E',
                'cost_kes'      => 0,
                'is_free'       => true,
                'duration_hours'=> 1,
                'difficulty'    => 'beginner',
                'xp_reward'     => 75,
                'outcome'       => 'Understand how Pesa City works and earn your first 75 XP',
                'financial_tip' => 'The best investment you can make is in your own financial education.',
                'jobs_intro'    => null,
                'is_active'     => true,
            ]
        );

        // ── Add intro_content to existing courses ────────────────────────────
        $intros = [
            'web-dev-fundamentals' =>
                "Technology is one of the fastest-growing careers in Kenya — and you don't need a degree to get started.\n\nThis course covers the foundations of web development: how websites are built, how to write basic code, and what skills employers look for in entry-level tech roles.\n\nComplete it to unlock three tech jobs in Pesa City, including Junior Web Developer and Freelance Developer roles paying up to KES 65,000/month.",

            'business-fundamentals' =>
                "Every business — from a roadside kiosk to a corporation — runs on the same core principles.\n\nThis course covers how businesses make money, how to sell effectively, and how to read basic financial statements. It's the foundation for any career in commerce or entrepreneurship.\n\nComplete it to unlock sales, business development, and retail management roles in Pesa City.",

            'personal-finance-investing' =>
                "Most Kenyans earn money — but few know how to keep it and grow it.\n\nThis course teaches the 50/30/20 budgeting rule, how savings accounts work, what compound interest means for your future, and the basics of investing in stocks and unit trusts.\n\nComplete it to unlock finance careers including Bank Teller, Financial Analyst, and Investment Advisor roles.",

            'digital-marketing-essentials' =>
                "Every business in Kenya needs an online presence — and those who can build and manage it are in high demand.\n\nThis course covers social media strategy, content creation, how digital advertising works, and how to measure what's actually driving results.\n\nComplete it to unlock creative roles including Social Media Manager, Content Creator, and Brand Strategist positions.",
        ];

        foreach ($intros as $slug => $intro) {
            CityCourse::where('slug', $slug)->update(['intro_content' => $intro]);
        }
    }
}
