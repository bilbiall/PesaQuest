<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quest;

class QuestSeeder extends Seeder
{
    public function run(): void
    {
        $quests = [
            ['age_group' => '8-12',  'icon' => '🐷', 'title' => 'Start a Piggy Bank', 'description' => 'Get a physical piggy bank or savings container at home', 'instructions' => 'Take a photo of your piggy bank or savings container and tell us what you are saving for.', 'xp_reward' => 100, 'sort_order' => 1],
            ['age_group' => '8-12',  'icon' => '📝', 'title' => 'Write a Savings Goal', 'description' => 'Write down one thing you want to save money for', 'instructions' => 'Write your savings goal on paper, take a photo, and tell us how much you need and how long it will take.', 'xp_reward' => 75, 'sort_order' => 2],
            ['age_group' => '13-17', 'icon' => '📱', 'title' => 'Register M-Pesa', 'description' => 'Set up your own M-Pesa account for the first time', 'instructions' => 'If you do not have M-Pesa yet, register at any Safaricom agent. Take a screenshot showing your registered M-Pesa number (hide last 4 digits for privacy).', 'xp_reward' => 150, 'sort_order' => 1],
            ['age_group' => '13-17', 'icon' => '💼', 'title' => 'First Side Hustle', 'description' => 'Do a small job or service to earn money', 'instructions' => 'Wash a car, do laundry, sell something, or help a neighbor. Tell us what you did, how much you earned, and what you plan to do with the money.', 'xp_reward' => 200, 'sort_order' => 2],
            ['age_group' => '13-17', 'icon' => '📊', 'title' => 'Track Spending for a Week', 'description' => 'Write down every shilling you spend for 7 days', 'instructions' => 'Keep a spending diary for 7 days — every purchase no matter how small. Share what surprised you most about your spending.', 'xp_reward' => 175, 'sort_order' => 3],
            ['age_group' => '18-25', 'icon' => '🏦', 'title' => 'Open a Bank Account', 'description' => 'Open your first savings account at a bank or SACCO', 'instructions' => 'Visit any bank (Equity, KCB, Co-op, etc.) and open a savings account. Share which bank and what made you choose it — no account numbers needed!', 'xp_reward' => 300, 'sort_order' => 1],
            ['age_group' => '18-25', 'icon' => '🤝', 'title' => 'Join a Chama', 'description' => 'Join or start a savings group with friends or family', 'instructions' => 'Join an existing chama or start a new one with at least 3 people. Tell us the group name, contribution amount, and your savings goal.', 'xp_reward' => 250, 'sort_order' => 2],
            ['age_group' => '18-25', 'icon' => '📉', 'title' => 'Clear a Small Debt', 'description' => 'Pay off a small loan or money you owe someone', 'instructions' => 'Pay off any small debt — M-Shwari, Fuliza, or money owed to a friend. Tell us how it felt and what you will do differently going forward.', 'xp_reward' => 200, 'sort_order' => 3],
            ['age_group' => '26+',   'icon' => '📋', 'title' => 'Make a Monthly Budget', 'description' => 'Create a full income and expenses budget for this month', 'instructions' => 'List all income sources and all expenses for this month. Share your biggest expense category and what you plan to cut to save more.', 'xp_reward' => 350, 'sort_order' => 1],
            ['age_group' => '26+',   'icon' => '📈', 'title' => 'Invest Ksh 1,000', 'description' => 'Make your first real investment of at least Ksh 1,000', 'instructions' => 'Invest in MMFS, Treasury Bills, NSE stocks, or any legitimate investment. Share what you invested in and why you chose it.', 'xp_reward' => 500, 'sort_order' => 2],
            ['age_group' => '26+',   'icon' => '🏠', 'title' => 'Start an Emergency Fund', 'description' => 'Set aside 3 months of expenses as emergency savings', 'instructions' => 'Open a separate savings account purely for emergencies and deposit at least Ksh 5,000 to start. Share your target and your timeline.', 'xp_reward' => 400, 'sort_order' => 3],
        ];

        foreach ($quests as $q) {
            Quest::firstOrCreate(
                ['title' => $q['title'], 'age_group' => $q['age_group']],
                array_merge($q, ['is_active' => true])
            );
        }
    }
}
