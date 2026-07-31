<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Node;
use App\Models\NodeResult;
use Illuminate\Database\Seeder;

class ErrandScenarioSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['8-12', '13-17', '18-25', '26+'] as $ageGroup) {
            $this->seedForAgeGroup($ageGroup);
        }
    }

    private function seedForAgeGroup(string $ageGroup): void
    {
        $prefix = "[$ageGroup] ";

        // ── Scenario nodes ──────────────────────────────────────

        $e1 = Node::updateOrCreate(
            ['title' => $prefix . 'The Errand Money', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You have received Ksh. 10,000 as payment for running errands for a relative. What do you do with the money?"
                ),
                'type'          => 'scenario',
                'is_start'      => true,
                'is_free'       => true,
                'theme_color'   => '#f59e0b',
                'icon'          => '💰',
                'sort_order'    => 100,
                'metadata'      => ['starting_balance' => 10000],
            ]
        );

        $e2 = Node::updateOrCreate(
            ['title' => $prefix . 'The Saving Path', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You've decided to save the Ksh 10,000. Great financial thinking! But HOW you save it makes all the difference."
                ),
                'type'        => 'scenario',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#10b981',
                'icon'        => '🏦',
                'sort_order'  => 101,
                'metadata'    => ['final_lesson' => 'Saving today ensures that you have something for tomorrow. Kudos!'],
            ]
        );

        $e3 = Node::updateOrCreate(
            ['title' => $prefix . 'The Spending Path', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You've decided to spend the Ksh 10,000. Before you do, consider: will you spend it on things you NEED or things you WANT?"
                ),
                'type'        => 'scenario',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#f97316',
                'icon'        => '🛍️',
                'sort_order'  => 102,
                'metadata'    => ['final_lesson' => 'It is good to spend on yourself but remember to always save and donate some. Good!'],
            ]
        );

        $e4 = Node::updateOrCreate(
            ['title' => $prefix . 'The Giving Path', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "Your heart is in the right place! You want to donate the Ksh 10,000 to charity. But HOW you donate matters too."
                ),
                'type'        => 'scenario',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#8b5cf6',
                'icon'        => '❤️',
                'sort_order'  => 103,
                'metadata'    => ['final_lesson' => 'It is more blessed to give, and everyone needs someone in their times of need. Thanks for being that someone to somebody else\'s need!'],
            ]
        );

        // ── Result nodes ────────────────────────────────────────

        $ra1 = Node::updateOrCreate(
            ['title' => $prefix . 'Piggy Bank Saver', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You saved the Ksh 10,000 in a piggy bank at home. Your money is safe but it won't grow over time."
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#6366f1',
                'icon'        => '🐷',
                'sort_order'  => 110,
                'metadata'    => [],
            ]
        );

        $ra2 = Node::updateOrCreate(
            ['title' => $prefix . 'Smart Banker', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You saved the Ksh 10,000 in a bank account. It's safe and will earn interest over time — a solid financial move!"
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#10b981',
                'icon'        => '🏦',
                'sort_order'  => 111,
                'metadata'    => [],
            ]
        );

        $ra3 = Node::updateOrCreate(
            ['title' => $prefix . 'The Loan Shark Friend', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You lent the Ksh 10,000 to a friend at 30% interest. It's risky but could pay off big if your friend repays!"
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#f59e0b',
                'icon'        => '🤝',
                'sort_order'  => 112,
                'metadata'    => [],
            ]
        );

        $rb1 = Node::updateOrCreate(
            ['title' => $prefix . 'Wants Spender', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You spent the Ksh 10,000 on things you wanted — treats and entertainment. You had fun, but the money is gone."
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#ef4444',
                'icon'        => '🛍️',
                'sort_order'  => 113,
                'metadata'    => [],
            ]
        );

        $rb2 = Node::updateOrCreate(
            ['title' => $prefix . 'Needs Spender', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You spent the Ksh 10,000 on essential needs — school supplies, food, and utilities. Responsible and practical!"
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#f97316',
                'icon'        => '✅',
                'sort_order'  => 114,
                'metadata'    => [],
            ]
        );

        $rb3 = Node::updateOrCreate(
            ['title' => $prefix . 'Balanced Spender', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You split the Ksh 10,000 between needs and wants. You were responsible AND enjoyed yourself. Perfect balance!"
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#22c55e',
                'icon'        => '⚖️',
                'sort_order'  => 115,
                'metadata'    => [],
            ]
        );

        $rc1 = Node::updateOrCreate(
            ['title' => $prefix . 'Kind Gift Giver', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You donated Ksh 10,000 worth of items in kind. Your thoughtful gift ensures the money is used well!"
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#8b5cf6',
                'icon'        => '🎁',
                'sort_order'  => 116,
                'metadata'    => [],
            ]
        );

        $rc2 = Node::updateOrCreate(
            ['title' => $prefix . 'Cash Donor', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You donated the full Ksh 10,000 in cash. Cash gives the recipient maximum flexibility to meet their needs."
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#a78bfa',
                'icon'        => '💸',
                'sort_order'  => 117,
                'metadata'    => [],
            ]
        );

        $rc3 = Node::updateOrCreate(
            ['title' => $prefix . 'Wise Donor', 'age_group' => $ageGroup],
            [
                'scenario_text' => $this->adaptText($ageGroup,
                    "You split the donation: Ksh 5,000 in cash and Ksh 5,000 in items. A wise, thoughtful combination!"
                ),
                'type'        => 'result',
                'is_start'    => false,
                'is_free'     => true,
                'theme_color' => '#7c3aed',
                'icon'        => '🌟',
                'sort_order'  => 118,
                'metadata'    => [],
            ]
        );

        // ── NodeResults (lesson cards on result nodes) ───────────

        NodeResult::updateOrCreate(
            ['node_id' => $ra1->id],
            [
                'result_text' => 'Piggy Bank Saver — you kept your money at home.',
                'lesson_text' => "Piggy banks are safe but your money doesn't grow. In the future, consider putting savings in a bank where it earns interest!",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $ra2->id],
            [
                'result_text' => 'Smart Banker — you trusted the bank with your money.',
                'lesson_text' => "Good choice! Bank savings are safe and earn small interest. You'll receive a notification when your interest is credited.",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $ra3->id],
            [
                'result_text' => 'The Loan Shark Friend — you lent money at high interest.',
                'lesson_text' => "Bold move! You've lent Ksh 10,000 to earn interest. This is risky — your friend must pay back. You'll be notified when returns come in.",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $rb1->id],
            [
                'result_text' => 'Wants Spender — you spent on things you desired.',
                'lesson_text' => "You enjoyed yourself, but your Ksh 10,000 is gone. Remember: needs come before wants next time!",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $rb2->id],
            [
                'result_text' => 'Needs Spender — you spent responsibly on essentials.',
                'lesson_text' => "Well done for being responsible! Next time, set aside a little for wants too — you deserve to enjoy your money!",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $rb3->id],
            [
                'result_text' => 'Balanced Spender — you split between needs and wants.',
                'lesson_text' => "Perfect balance! You handled your money like a pro — responsible AND enjoyed a bit. This is the ideal approach!",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $rc1->id],
            [
                'result_text' => 'Kind Gift Giver — you donated items worth Ksh 10,000.',
                'lesson_text' => "Your generosity ensured the recipient got exactly what they needed. Giving in kind prevents misuse!",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $rc2->id],
            [
                'result_text' => 'Cash Donor — you gave the full amount in cash.',
                'lesson_text' => "Cash gives freedom but requires trust. Your generosity could change someone's life!",
            ]
        );

        NodeResult::updateOrCreate(
            ['node_id' => $rc3->id],
            [
                'result_text' => 'Wise Donor — you split cash and items for maximum impact.',
                'lesson_text' => "The wisest donation approach! Combining cash and items shows financial wisdom AND compassion.",
            ]
        );

        // ── Choices ──────────────────────────────────────────────

        // From E1 → E2, E3, E4
        Choice::updateOrCreate(
            ['node_id' => $e1->id, 'label' => 'Save the money'],
            [
                'next_node_id' => $e2->id,
                'points'       => 5,
                'sort_order'   => 1,
                'description'  => null,
                'effect_data'  => ['lesson' => 'Smart! Saving is the foundation of financial health.'],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e1->id, 'label' => 'Spend everything'],
            [
                'next_node_id' => $e3->id,
                'points'       => 3,
                'sort_order'   => 2,
                'description'  => null,
                'effect_data'  => ['lesson' => 'Spending is natural, but how you spend matters.'],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e1->id, 'label' => 'Donate to charity'],
            [
                'next_node_id' => $e4->id,
                'points'       => 4,
                'sort_order'   => 3,
                'description'  => null,
                'effect_data'  => ['lesson' => 'Generosity is admirable! You thought of others.'],
            ]
        );

        // From E2 → RA1, RA2, RA3
        Choice::updateOrCreate(
            ['node_id' => $e2->id, 'label' => 'Save in a piggy bank'],
            [
                'next_node_id' => $ra1->id,
                'points'       => 2,
                'sort_order'   => 1,
                'description'  => 'Keep it at home where I can see it',
                'effect_data'  => [
                    'balance_change' => 0,
                    'lesson'         => "Safe but it doesn't grow. Your money needs to work for you!",
                ],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e2->id, 'label' => 'Save in a bank account'],
            [
                'next_node_id' => $ra2->id,
                'points'       => 4,
                'sort_order'   => 2,
                'description'  => 'Let the bank keep it safe with some interest',
                'effect_data'  => [
                    'balance_change' => 0,
                    'type'           => 'investment',
                    'investment'     => [
                        'return_rate' => 5,
                        'return_days' => 7,
                        'label'       => 'Bank savings interest',
                    ],
                    'lesson' => 'Safe but generates little interest. Better than home savings though!',
                ],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e2->id, 'label' => 'Lend to a friend at high interest'],
            [
                'next_node_id' => $ra3->id,
                'points'       => 6,
                'sort_order'   => 3,
                'description'  => 'A friend needs it urgently and will repay with 30% interest in 3 days',
                'effect_data'  => [
                    'balance_change' => -10000,
                    'type'           => 'investment',
                    'investment'     => [
                        'return_rate' => 30,
                        'return_days' => 3,
                        'label'       => 'Friend loan repayment',
                    ],
                    'lesson' => 'Risky but increases your savings if your friend pays back!',
                ],
            ]
        );

        // From E3 → RB1, RB2, RB3
        Choice::updateOrCreate(
            ['node_id' => $e3->id, 'label' => 'Spend on wants'],
            [
                'next_node_id' => $rb1->id,
                'points'       => 2,
                'sort_order'   => 1,
                'description'  => "Buy things I enjoy but don't necessarily need",
                'effect_data'  => [
                    'balance_change' => -10000,
                    'lesson'         => 'Life comprises of both needs and wants, take care of your needs too.',
                ],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e3->id, 'label' => 'Spend on needs'],
            [
                'next_node_id' => $rb2->id,
                'points'       => 3,
                'sort_order'   => 2,
                'description'  => 'Use it only for essential necessities',
                'effect_data'  => [
                    'balance_change' => -10000,
                    'lesson'         => 'It is very responsible of you to take care of your needs but remember to enjoy a few wants as well. Life is for living!',
                ],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e3->id, 'label' => 'Spend on needs and wants'],
            [
                'next_node_id' => $rb3->id,
                'points'       => 5,
                'sort_order'   => 3,
                'description'  => 'Balance between necessities and enjoyment',
                'effect_data'  => [
                    'balance_change' => -10000,
                    'lesson'         => 'Life has both needs and wants, it was smart of you to prioritize your needs without forgetting to enjoy some of your wants.',
                ],
            ]
        );

        // From E4 → RC1, RC2, RC3
        Choice::updateOrCreate(
            ['node_id' => $e4->id, 'label' => 'Donate in kind (items worth Ksh 10,000)'],
            [
                'next_node_id' => $rc1->id,
                'points'       => 2,
                'sort_order'   => 1,
                'description'  => 'Buy and donate physical items',
                'effect_data'  => [
                    'balance_change' => -10000,
                    'lesson'         => 'This is great as the money will not be misused by the recipient however the items may not meet some of their needs.',
                ],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e4->id, 'label' => 'Donate full cash (Ksh 10,000)'],
            [
                'next_node_id' => $rc2->id,
                'points'       => 3,
                'sort_order'   => 2,
                'description'  => 'Give the full amount in cash',
                'effect_data'  => [
                    'balance_change' => -10000,
                    'lesson'         => 'Money is amazing because it gives the recipient the flexibility to budget for it and buy what they really need. However, if the recipient is careless, they might waste it.',
                ],
            ]
        );

        Choice::updateOrCreate(
            ['node_id' => $e4->id, 'label' => 'Split: Ksh 5,000 cash + Ksh 5,000 items'],
            [
                'next_node_id' => $rc3->id,
                'points'       => 4,
                'sort_order'   => 3,
                'description'  => 'A wise combination of cash and items',
                'effect_data'  => [
                    'balance_change' => -10000,
                    'lesson'         => 'Splitting the donation in both cash and kind is very wise of you. The items ensure that money is not wasted, and the cash gives the recipient the freedom to plan for some of their needs that might be very personal or private.',
                ],
            ]
        );

        $this->command->info("Errand scenario seeded for age group: {$ageGroup}");
    }

    /**
     * Adapt scenario text tone to the age group.
     */
    private function adaptText(string $ageGroup, string $text): string
    {
        return match ($ageGroup) {
            '8-12'  => $text . " (Think carefully — this is a big decision for you!)",
            '13-17' => $text . " (This is your moment to show what you've learned!)",
            '18-25' => $text,
            '26+'   => $text . " (Your experience should guide you here.)",
            default => $text,
        };
    }
}
