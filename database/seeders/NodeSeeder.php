<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ============================================================
        // PesaQuest – Preteens Tree (age group: 8–12)
        // Full branching decision graph, 3 free + gated premium nodes
        // ============================================================

        // --- FREE NODES ---

        // Node 1 – START (free)
        $n1 = \App\Models\Node::create([
            'title'         => 'The Birthday Money',
            'scenario_text' => 'It\'s your birthday! Grandma gives you Ksh 500. You\'ve been wanting a new football, but your friend Amara says there\'s a cool toy shop in town with limited-stock items. What do you do with the money?',
            'age_group'     => '8-12',
            'type'          => 'scenario',
            'is_start'      => true,
            'is_free'       => true,
            'sort_order'    => 1,
            'icon'          => '🎂',
            'theme_color'   => '#6366f1',
        ]);

        // Node 2 – Save it (free, leads to another choice)
        $n2 = \App\Models\Node::create([
            'title'         => 'The Piggy Bank Decision',
            'scenario_text' => 'You put the Ksh 500 in your piggy bank. Two weeks later, you\'ve saved up Ksh 850 total. Your school trip is coming up and costs Ksh 600. Do you use your savings for the trip, or keep saving for something bigger?',
            'age_group'     => '8-12',
            'type'          => 'scenario',
            'is_start'      => false,
            'is_free'       => true,
            'sort_order'    => 2,
            'icon'          => '🐷',
            'theme_color'   => '#ec4899',
        ]);

        // Node 3 – Spend it all (free, leads to result)
        $n3 = \App\Models\Node::create([
            'title'         => 'Buyer\'s Remorse',
            'scenario_text' => 'You spent all Ksh 500 on toys at the shop. The next day, the school trip sign-ups open — it costs Ksh 600. You have zero money left and have to miss it. Your classmates come back with amazing stories.',
            'age_group'     => '8-12',
            'type'          => 'result',
            'is_start'      => false,
            'is_free'       => true,
            'sort_order'    => 3,
            'icon'          => '😞',
            'theme_color'   => '#ef4444',
        ]);

        // --- PREMIUM NODES (gated after first 3) ---

        // Node 4 – Used savings for trip (premium)
        $n4 = \App\Models\Node::create([
            'title'         => 'The Trip Dilemma',
            'scenario_text' => 'You paid Ksh 600 for the school trip. It was amazing! But now you only have Ksh 250 left. There\'s a bake sale at school where you can sell cupcakes for profit. Do you use your Ksh 250 to buy ingredients and try to earn more?',
            'age_group'     => '8-12',
            'type'          => 'scenario',
            'is_start'      => false,
            'is_free'       => false,
            'sort_order'    => 4,
            'icon'          => '🚌',
            'theme_color'   => '#f59e0b',
        ]);

        // Node 5 – Keep saving (premium)
        $n5 = \App\Models\Node::create([
            'title'         => 'Goal Setter',
            'scenario_text' => 'You decide to skip the trip and keep saving. Three months later, you have Ksh 2,000! Your parents offer to match whatever you save towards a bicycle. Do you keep saving, or buy something fun now?',
            'age_group'     => '8-12',
            'type'          => 'scenario',
            'is_start'      => false,
            'is_free'       => false,
            'sort_order'    => 5,
            'icon'          => '🎯',
            'theme_color'   => '#10b981',
        ]);

        // Node 6 – Try bake sale (premium)
        $n6 = \App\Models\Node::create([
            'title'         => 'Young Entrepreneur',
            'scenario_text' => 'You spent Ksh 200 on ingredients and baked 20 cupcakes. You sold 18 at Ksh 30 each, earning Ksh 540! After costs, you made Ksh 340 profit. Your teacher is impressed. Do you reinvest to make more, or celebrate your win?',
            'age_group'     => '8-12',
            'type'          => 'result',
            'is_start'      => false,
            'is_free'       => false,
            'sort_order'    => 6,
            'icon'          => '🧁',
            'theme_color'   => '#f97316',
        ]);

        // Node 7 – Skip bake sale (premium)
        $n7 = \App\Models\Node::create([
            'title'         => 'Safe But Slow',
            'scenario_text' => 'You kept your Ksh 250 safe. Wise! But you watched your friend earn Ksh 340 from the bake sale while you stood by. Sometimes safe choices mean missed growth. What\'s your next move?',
            'age_group'     => '8-12',
            'type'          => 'result',
            'is_start'      => false,
            'is_free'       => false,
            'sort_order'    => 7,
            'icon'          => '🤔',
            'theme_color'   => '#8b5cf6',
        ]);

        // Node 8 – Match savings, get bike (premium)
        $n8 = \App\Models\Node::create([
            'title'         => 'The Power of Matching',
            'scenario_text' => 'Your parents match your Ksh 2,000. You now have Ksh 4,000 — enough for the bicycle! You ride to school every day, saving time. Your smart saving + parent support = WINNING. This is how investments work!',
            'age_group'     => '8-12',
            'type'          => 'ending',
            'is_start'      => false,
            'is_free'       => false,
            'sort_order'    => 8,
            'icon'          => '🚲',
            'theme_color'   => '#06b6d4',
            'metadata'      => ['ending_type' => 'great', 'final_message' => 'Smart Saver Achievement Unlocked!'],
        ]);

        // Node 9 – Spend it all, no match (premium)
        $n9 = \App\Models\Node::create([
            'title'         => 'The Spending Trap',
            'scenario_text' => 'You spent your Ksh 2,000 on games and snacks. Your parents\' matching offer expires. You\'re back to zero, and the bicycle is still just a dream. Spending feels great for a moment — but saving builds the future.',
            'age_group'     => '8-12',
            'type'          => 'ending',
            'is_start'      => false,
            'is_free'       => false,
            'sort_order'    => 9,
            'icon'          => '💸',
            'theme_color'   => '#ef4444',
            'metadata'      => ['ending_type' => 'lesson', 'final_message' => 'Restart and make smarter choices!'],
        ]);

        // ============================================================
        // Now wire up choices (the edges of the graph)
        // ============================================================

        // Node 1 → choices
        \App\Models\Choice::create([
            'node_id'      => $n1->id,
            'next_node_id' => $n2->id,
            'label'        => 'Save it in my piggy bank',
            'description'  => 'Keep it safe for later',
            'points'       => 20,
            'sort_order'   => 1,
            'effect_data'  => ['balance_change' => 500, 'lesson' => 'Saving is the first step to financial freedom!'],
        ]);
        \App\Models\Choice::create([
            'node_id'      => $n1->id,
            'next_node_id' => $n3->id,
            'label'        => 'Spend it all at the toy shop',
            'description'  => 'Buy everything you want now',
            'points'       => 5,
            'sort_order'   => 2,
            'effect_data'  => ['balance_change' => -500, 'lesson' => 'Spending all at once leaves nothing for later needs.'],
        ]);
        \App\Models\Choice::create([
            'node_id'      => $n1->id,
            'next_node_id' => $n2->id,
            'label'        => 'Split it: save Ksh 300, spend Ksh 200',
            'description'  => 'Balance saving and spending',
            'points'       => 15,
            'sort_order'   => 3,
            'effect_data'  => ['balance_change' => 300, 'lesson' => 'Splitting money between needs and savings is smart budgeting!'],
        ]);

        // Node 2 → choices
        \App\Models\Choice::create([
            'node_id'      => $n2->id,
            'next_node_id' => $n4->id,
            'label'        => 'Use savings for the school trip',
            'description'  => 'Experience the trip, spend your savings',
            'points'       => 10,
            'sort_order'   => 1,
            'effect_data'  => ['balance_change' => -600, 'lesson' => 'Experiences have value too. But always know the cost!'],
        ]);
        \App\Models\Choice::create([
            'node_id'      => $n2->id,
            'next_node_id' => $n5->id,
            'label'        => 'Keep saving for something bigger',
            'description'  => 'Miss the trip but build toward a bigger goal',
            'points'       => 25,
            'sort_order'   => 2,
            'effect_data'  => ['balance_change' => 0, 'lesson' => 'Delayed gratification is a superpower!'],
        ]);

        // Node 3 (result) → loop back to start
        \App\Models\Choice::create([
            'node_id'      => $n3->id,
            'next_node_id' => $n1->id,
            'label'        => 'Try again with a smarter plan',
            'description'  => 'Learn from the mistake',
            'points'       => 10,
            'sort_order'   => 1,
            'effect_data'  => ['lesson' => 'Every mistake is a lesson. Try again!'],
        ]);

        // Node 4 → choices
        \App\Models\Choice::create([
            'node_id'      => $n4->id,
            'next_node_id' => $n6->id,
            'label'        => 'Invest in the bake sale',
            'description'  => 'Risk Ksh 250 to earn more',
            'points'       => 30,
            'sort_order'   => 1,
            'effect_data'  => ['balance_change' => 340, 'lesson' => 'Investing small amounts can yield big returns!'],
        ]);
        \App\Models\Choice::create([
            'node_id'      => $n4->id,
            'next_node_id' => $n7->id,
            'label'        => 'Keep the money safe',
            'description'  => 'Don\'t risk what you have',
            'points'       => 10,
            'sort_order'   => 2,
            'effect_data'  => ['balance_change' => 0, 'lesson' => 'Safety is good, but some risks are worth taking.'],
        ]);

        // Node 5 → choices
        \App\Models\Choice::create([
            'node_id'      => $n5->id,
            'next_node_id' => $n8->id,
            'label'        => 'Keep saving to get the match',
            'description'  => 'Wait for the parent matching bonus',
            'points'       => 40,
            'sort_order'   => 1,
            'effect_data'  => ['balance_change' => 2000, 'lesson' => 'Matching contributions double your money — this is how employer pensions work!'],
        ]);
        \App\Models\Choice::create([
            'node_id'      => $n5->id,
            'next_node_id' => $n9->id,
            'label'        => 'Spend it all on fun stuff',
            'description'  => 'Enjoy the money now',
            'points'       => 5,
            'sort_order'   => 2,
            'effect_data'  => ['balance_change' => -2000, 'lesson' => 'Spending forfeited the matching bonus. Patience pays off!'],
        ]);

        // --- Node Results ---
        \App\Models\NodeResult::create([
            'node_id'     => $n3->id,
            'result_text' => 'You spent everything and missed the trip. Your friends had a great time without you.',
            'lesson_text' => 'Money spent impulsively cannot be recovered. Always think ahead before spending.',
        ]);

        \App\Models\NodeResult::create([
            'node_id'     => $n6->id,
            'result_text' => 'You turned Ksh 200 into Ksh 540! A Ksh 340 profit in one day.',
            'lesson_text' => 'Entrepreneurship means taking calculated risks with money. When costs are low and returns high, it\'s worth trying!',
        ]);

        \App\Models\NodeResult::create([
            'node_id'     => $n7->id,
            'result_text' => 'Your Ksh 250 is safe. But your friend made Ksh 340 from the bake sale.',
            'lesson_text' => 'Sometimes playing it too safe costs you growth. Calculated risks can lead to big rewards.',
        ]);

        \App\Models\NodeResult::create([
            'node_id'     => $n8->id,
            'result_text' => 'You saved Ksh 2,000, got it matched, and bought a bicycle for Ksh 4,000!',
            'lesson_text' => 'This is the magic of matching contributions — like pension schemes and employer matches. Save more, get more!',
        ]);

        \App\Models\NodeResult::create([
            'node_id'     => $n9->id,
            'result_text' => 'You spent your savings and missed the matching bonus. The bicycle stays a dream.',
            'lesson_text' => 'Patience is a financial superpower. Delayed gratification builds wealth.',
        ]);

        // ============================================================
        // Teen Tree (age group: 13–17) — starter set
        // ============================================================

        $t1 = \App\Models\Node::create([
            'title'         => 'The Side Hustle Opportunity',
            'scenario_text' => 'You\'re 15. Your neighbour offers to pay you Ksh 800 to wash his car every weekend. Your friends want you to join a gaming tournament that costs Ksh 500 to enter, with a Ksh 3,000 prize. You can\'t do both this weekend. What do you choose?',
            'age_group'     => '13-17',
            'type'          => 'scenario',
            'is_start'      => true,
            'is_free'       => true,
            'sort_order'    => 1,
            'icon'          => '💼',
            'theme_color'   => '#6366f1',
        ]);

        $t2 = \App\Models\Node::create([
            'title'         => 'Steady Income Beats Chance',
            'scenario_text' => 'You washed the car and earned Ksh 800. Your friend won the tournament! He got Ksh 3,000. But next week there\'s no tournament — and you still have your car washing job. You\'ve earned Ksh 800 consistently for 3 weeks. Do you save all of it, or use Ksh 200/week to invest in a small phone-charging business?',
            'age_group'     => '13-17',
            'type'          => 'scenario',
            'is_start'      => false,
            'is_free'       => true,
            'sort_order'    => 2,
            'icon'          => '🚗',
            'theme_color'   => '#10b981',
        ]);

        $t3 = \App\Models\Node::create([
            'title'         => 'The Gamble Pays Off',
            'scenario_text' => 'You entered the tournament and won! You now have Ksh 2,500 after entry fees. But your neighbour found someone else for the car job. Do you use your winnings wisely, or enter the next tournament for a Ksh 5,000 prize?',
            'age_group'     => '13-17',
            'type'          => 'scenario',
            'is_start'      => false,
            'is_free'       => true,
            'sort_order'    => 3,
            'icon'          => '🎮',
            'theme_color'   => '#f59e0b',
        ]);

        \App\Models\Choice::create([
            'node_id'      => $t1->id,
            'next_node_id' => $t2->id,
            'label'        => 'Take the car washing job',
            'points'       => 20,
            'sort_order'   => 1,
            'effect_data'  => ['balance_change' => 800, 'lesson' => 'Reliable income is the foundation of financial stability.'],
        ]);
        \App\Models\Choice::create([
            'node_id'      => $t1->id,
            'next_node_id' => $t3->id,
            'label'        => 'Enter the gaming tournament',
            'points'       => 10,
            'sort_order'   => 2,
            'effect_data'  => ['balance_change' => -500, 'lesson' => 'High risk can mean high reward — but also high loss.'],
        ]);
    }
}
