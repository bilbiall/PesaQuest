<?php

namespace App\Services;

use App\Models\LifeEvent;
use Illuminate\Support\Facades\Schema;

/**
 * Life Events Mixer — one button, a season of surprises. 🎲
 *
 * Composes DRAFT life events (is_active=false — the admin approves each in
 * GameSet → Life Events) from a deep template bank spanning every effect
 * type the engine actually supports:
 *
 *   balance_delta — cash windfalls & shocks
 *   compound      — cash AND credit move together (career moments)
 *   market_event  — asset values swing by category
 *   credit_delta  — credit score moves alone
 *   narrative     — pure story + lesson, no mechanical effect
 *
 * Chapters use the game's life stages (student → elder), and amounts scale
 * to each — a student's windfall is pocket money; a builder's is rent-sized.
 * Every (template × chapter) pair gets a deterministic slug, so regenerating
 * never duplicates — it only composes what doesn't exist yet.
 */
class LifeEventMixer
{
    /** Ksh scale per life chapter (multiplies template base amounts). */
    private const CHAPTER_SCALE = [
        'student' => 0.25, 'graduate' => 0.6, 'hustler' => 1.0,
        'settler' => 1.4,  'builder'  => 1.8, 'elder'   => 1.5, 'all' => 0.8,
    ];

    public function generate(int $count = 20): array
    {
        $summary = ['created' => 0, 'already' => 0];
        if (!Schema::hasTable('life_events')) return $summary;

        $existing = LifeEvent::pluck('slug')->flip()->all();

        $candidates = [];
        foreach ($this->bank() as $tpl) {
            foreach ($tpl['chapters'] as $chapter) {
                $slug = 'mix-' . $tpl['key'] . '-' . $chapter;
                if (isset($existing[$slug])) { $summary['already']++; continue; }
                $candidates[] = [$tpl, $chapter, $slug];
            }
        }

        shuffle($candidates);

        foreach (array_slice($candidates, 0, max(0, $count)) as [$tpl, $chapter, $slug]) {
            LifeEvent::create([
                'slug'             => $slug,
                'chapter'          => $chapter,
                'asset_category'   => $tpl['asset_category'] ?? null,
                'title'            => $tpl['title'],
                'description'      => $tpl['description'],
                'flavor_text'      => $tpl['flavors'][array_rand($tpl['flavors'])],
                'educational_note' => $tpl['lessons'][array_rand($tpl['lessons'])],
                'effect_type'      => $tpl['effect_type'],
                'effect_data'      => $this->buildEffect($tpl, self::CHAPTER_SCALE[$chapter] ?? 1.0),
                'probability'      => $tpl['probability'],
                'icon'             => $tpl['icon'],
                'is_positive'      => $tpl['is_positive'],
                'is_active'        => false, // ALWAYS drafts — approve in GameSet → Life Events
            ]);
            $summary['created']++;
        }

        return $summary;
    }

    /** Scale cash bands to the chapter; credit points stay universal. */
    private function buildEffect(array $tpl, float $scale): array
    {
        $d = $tpl['effect_data'];
        foreach (['balance_min', 'balance_max'] as $k) {
            if (isset($d[$k])) {
                $sign  = $d[$k] < 0 ? -1 : 1;
                $d[$k] = $sign * max(10, (int) (round(abs($d[$k]) * $scale / 10) * 10));
            }
        }
        return $d;
    }

    /** The template bank — base cash amounts written for the 'hustler' chapter. */
    private function bank(): array
    {
        return [

            // ── Windfalls (balance_delta, positive) ─────────────────────
            [
                'key' => 'jacket-money', 'title' => 'Forgotten Money Found!', 'icon' => '🧥',
                'chapters' => ['student', 'graduate', 'hustler', 'settler'], 'is_positive' => true,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => 150, 'balance_max' => 600],
                'probability' => 0.10,
                'description' => 'You found money you completely forgot about.',
                'flavors' => [
                    'Old jacket, inside pocket, folded notes. Past-you was looking out for present-you.',
                    'Deep in the backpack, under old receipts — cash! The best archaeology there is.',
                    'You swept the room and the room paid you. Cleaning: officially profitable.',
                ],
                'lessons' => [
                    'Money you can\'t see is money you can\'t plan with — track every shilling.',
                    'Surprise money is still money: bank half before it evaporates.',
                    'If losing track of cash surprised you, imagine what a budget would reveal.',
                ],
            ],
            [
                'key' => 'shagz-gift', 'title' => 'A Gift From Up-Country', 'icon' => '🧓',
                'chapters' => ['student', 'graduate'], 'is_positive' => true,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => 200, 'balance_max' => 800],
                'probability' => 0.08,
                'description' => 'A relative visited and left you a little something.',
                'flavors' => [
                    'Cucu pressed folded notes into your hand and whispered "usiambie mtu." A national tradition.',
                    'Uncle from shagz slipped you something after the handshake. The classic move, executed perfectly.',
                    'A visitor left, and where they sat — an envelope. Blessings come quietly.',
                ],
                'lessons' => [
                    'Gift money tests your habits: spenders spend it, builders bank it.',
                    'The fastest way to honour a gift is to make it grow.',
                    'Generosity received is a chance to practice gratitude AND good management.',
                ],
            ],
            [
                'key' => 'hustle-tip', 'title' => 'Hustle Paid Extra', 'icon' => '🤑',
                'chapters' => ['graduate', 'hustler', 'settler'], 'is_positive' => true,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => 300, 'balance_max' => 1200],
                'probability' => 0.08,
                'description' => 'A client was so impressed they added a bonus on top.',
                'flavors' => [
                    'Client checked the work twice, nodded slowly, and added a tip. Quality ni marketing.',
                    '"Umefanya kazi safi" — plus a little extra in the envelope. Reputation pays interest.',
                    'The job was small; the impression was big. They rounded UP. May all your clients be like this one.',
                ],
                'lessons' => [
                    'Excellence is the only advertising you never pay for.',
                    'Tips and bonuses are variance — enjoy them, never budget around them.',
                    'Do ordinary work extraordinarily well and the market notices.',
                ],
            ],
            [
                'key' => 'refund-surprise', 'title' => 'Refund Came Through!', 'icon' => '📲',
                'chapters' => ['hustler', 'settler', 'builder'], 'is_positive' => true,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => 150, 'balance_max' => 700],
                'probability' => 0.07,
                'description' => 'An overcharge you disputed finally got refunded.',
                'flavors' => [
                    'You followed up on that wrong deduction — politely, twice — and the money came back. Persistence pays literally.',
                    'The refund landed with a quiet ping. David 1, Goliath 0.',
                    'They said "within 7 working days" and for once it WAS 7 working days.',
                ],
                'lessons' => [
                    'Check your statements — errors favour whoever is paying attention.',
                    'Polite persistence recovers more money than anger ever has.',
                    'Every overcharge you ignore trains someone to overcharge you again.',
                ],
            ],
            [
                'key' => 'chore-bonus', 'title' => 'Chore Champion Bonus', 'icon' => '🧹',
                'chapters' => ['student'], 'is_positive' => true,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => 300, 'balance_max' => 900],
                'probability' => 0.10,
                'description' => 'You helped out big time — and got rewarded for it.',
                'flavors' => [
                    'You washed, swept, and organized like a hero. The reward found you!',
                    'The whole compound sparkled and so did your pocket. Work seen is work paid!',
                ],
                'lessons' => [
                    'Helping hands attract lucky pockets!',
                    'Work first, reward after — the order never changes.',
                ],
            ],

            // ── Shocks (balance_delta, negative) ─────────────────────────
            [
                'key' => 'phone-screen', 'title' => 'Cracked Screen Blues', 'icon' => '📱',
                'chapters' => ['graduate', 'hustler', 'settler'], 'is_positive' => false,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => -900, 'balance_max' => -300],
                'probability' => 0.09,
                'description' => 'Your phone had a brief argument with gravity. Gravity won.',
                'flavors' => [
                    'It slipped in slow motion. You watched. The screen now looks like abstract art you didn\'t order.',
                    'One bounce. Two bounces. Spider-web. The fundi\'s smile said "nice to see you again."',
                    'The phone survived the fall — the screen chose violence instead.',
                ],
                'lessons' => [
                    'Repairs are why emergency funds exist — accidents don\'t book appointments.',
                    'Fragile things carry hidden monthly costs. Price that in before buying.',
                    'A small buffer turns "disaster" into "annoying Tuesday".',
                ],
            ],
            [
                'key' => 'harambee-call', 'title' => 'Harambee Contribution', 'icon' => '🫱',
                'chapters' => ['settler', 'builder', 'elder'], 'is_positive' => false,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => -800, 'balance_max' => -250],
                'probability' => 0.08,
                'description' => 'A community fundraiser needs your contribution.',
                'flavors' => [
                    'The WhatsApp group went quiet, then the paybill appeared. You know what to do.',
                    'A wedding, a hospital bill, a send-off — the community shows up, and so do you.',
                    'The M-Pesa message writes itself. Harambee is a verb, and it conjugates through your wallet.',
                ],
                'lessons' => [
                    'Social obligations are real expenses — give them a budget line, not a surprise.',
                    'Generosity is sustainable only when it\'s planned.',
                    'Giving from a budget blesses twice: the receiver, and your peace of mind.',
                ],
            ],
            [
                'key' => 'fare-hike', 'title' => 'Matatu Fare Hike', 'icon' => '🚌',
                'chapters' => ['student', 'graduate', 'hustler'], 'is_positive' => false,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => -400, 'balance_max' => -100],
                'probability' => 0.10,
                'description' => 'Fares went up overnight — apparently it rained.',
                'flavors' => [
                    'Two drops of rain and the tout doubled the fare with a straight face. Economics, street edition.',
                    '"Ni mia mbili leo" — no explanation, no receipt, no appeal process.',
                    'Fuel prices moved and the matatu moved faster. Your commute budget felt it first.',
                ],
                'lessons' => [
                    'Transport costs swing — pad that budget line by 20%.',
                    'Inflation shows up in small daily prices before it makes the news.',
                    'The costs you can\'t negotiate are the ones to plan hardest for.',
                ],
            ],
            [
                'key' => 'data-bundle-leak', 'title' => 'The Bundle Vanished', 'icon' => '📶',
                'chapters' => ['student', 'graduate', 'hustler'], 'is_positive' => false,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => -300, 'balance_max' => -100],
                'probability' => 0.10,
                'description' => 'Auto-updates ate your data bundle in the background.',
                'flavors' => [
                    'You watched three videos. The phone downloaded three THOUSAND things. Bundle: deceased.',
                    'Background apps held a feast and your data was the menu.',
                    '"Data yangu imeenda wapi?" — the eternal question. The settings page knows the answer.',
                ],
                'lessons' => [
                    'Small automatic costs are still costs — audit your subscriptions and settings.',
                    'What drains quietly, drains completely. Check the leaks.',
                    'Convenience is billed in tiny amounts so you never do the total. Do the total.',
                ],
            ],
            [
                'key' => 'sweet-tooth-tax', 'title' => 'The Sweet Tooth Strikes', 'icon' => '🍬',
                'chapters' => ['student'], 'is_positive' => false,
                'effect_type' => 'balance_delta', 'effect_data' => ['balance_min' => -400, 'balance_max' => -100],
                'probability' => 0.10,
                'description' => 'Snacks called your name — and your coins answered.',
                'flavors' => [
                    'One sweet became five sweets became an empty pocket. It happens to the best of us!',
                    'The shop\'s candy shelf won today\'s battle. Tomorrow, YOU win.',
                ],
                'lessons' => [
                    'Small treats add up fast — count before you buy!',
                    'It\'s okay to enjoy — just decide the amount BEFORE you enter the shop.',
                ],
            ],

            // ── Markets (market_event) ───────────────────────────────────
            [
                'key' => 'market-rally', 'title' => 'Markets On The Up!', 'icon' => '📊',
                'chapters' => ['hustler', 'settler', 'builder', 'all'], 'is_positive' => true,
                'effect_type' => 'market_event',
                'effect_data' => ['market_categories' => [['category' => 'investment', 'pct' => 8]]],
                'probability' => 0.07,
                'description' => 'Good economic news lifted investment values.',
                'flavors' => [
                    'Green arrows everywhere. Investors are walking with a little extra bounce today.',
                    'The market smiled. Portfolios everywhere quietly gained weight.',
                    'Good harvests, good news, good numbers — your investments caught the updraft.',
                ],
                'lessons' => [
                    'Rallies reward those who were already invested — time IN the market beats timing the market.',
                    'Don\'t chase a rally; be positioned before it.',
                    'Booms are the time to keep discipline, not abandon it.',
                ],
            ],
            [
                'key' => 'market-dip', 'title' => 'Market Wobble', 'icon' => '📉',
                'chapters' => ['hustler', 'settler', 'builder', 'all'], 'is_positive' => false,
                'effect_type' => 'market_event',
                'effect_data' => ['market_categories' => [['category' => 'investment', 'pct' => -7]]],
                'probability' => 0.07,
                'description' => 'Jittery news knocked investment values down a notch.',
                'flavors' => [
                    'Red numbers this morning. Deep breaths — paper losses only become real when you panic-sell.',
                    'The market caught a cold. Seasoned investors made tea and waited.',
                    'Headlines shouted, prices dipped. The plan you made on a calm day is the one to follow now.',
                ],
                'lessons' => [
                    'Volatility is the fee the market charges for long-term returns.',
                    'A dip is only a loss if you sell into it.',
                    'Decide your strategy on calm days; execute it on stormy ones.',
                ],
            ],
            [
                'key' => 'property-boom', 'title' => 'Plot Prices Climbing', 'icon' => '🏘️',
                'chapters' => ['settler', 'builder', 'elder'], 'is_positive' => true, 'asset_category' => 'property',
                'effect_type' => 'market_event',
                'effect_data' => ['market_categories' => [['category' => 'property', 'pct' => 6]]],
                'probability' => 0.06,
                'description' => 'A new road project pushed property values up.',
                'flavors' => [
                    'They broke ground on the bypass and suddenly everyone remembers your area exists. Values up!',
                    'Surveyors were spotted. Prices heard the rumour before you did.',
                ],
                'lessons' => [
                    'Infrastructure moves property value — buy where the roads are GOING, not where they already are.',
                    'Land appreciates loudest where development whispers first.',
                ],
            ],
            [
                'key' => 'boda-demand', 'title' => 'Boda Business Booming', 'icon' => '🏍️',
                'chapters' => ['hustler', 'settler', 'all'], 'is_positive' => true, 'asset_category' => 'vehicle',
                'effect_type' => 'market_event',
                'effect_data' => ['market_categories' => [['category' => 'vehicle', 'pct' => 5]]],
                'probability' => 0.06,
                'description' => 'Delivery demand spiked — vehicles are earning more.',
                'flavors' => [
                    'Everyone\'s ordering everything. Two wheels have never been this valuable.',
                    'Rainy season plus online shopping equals boda season. Your machine appreciates the appreciation.',
                ],
                'lessons' => [
                    'Assets tied to real demand ride real waves — know what drives your asset\'s earnings.',
                    'Serve a need people have daily and your asset never sleeps hungry.',
                ],
            ],

            // ── Credit score (credit_delta) ──────────────────────────────
            [
                'key' => 'clean-record', 'title' => 'Clean Payment Record Noticed', 'icon' => '🌟',
                'chapters' => ['graduate', 'hustler', 'settler', 'builder'], 'is_positive' => true,
                'effect_type' => 'credit_delta', 'effect_data' => ['credit' => 12],
                'probability' => 0.07,
                'description' => 'Months of on-time payments just paid off — score up.',
                'flavors' => [
                    'Somewhere in a quiet office, a system flagged you as "reliable". Doors you can\'t see just unlocked.',
                    'Your name came up and the ledger smiled. Consistency has entered the chat.',
                    'No drama, no late fees, no stories — just a rising score. Boring is beautiful.',
                ],
                'lessons' => [
                    'Credit scores are built in silence and destroyed in noise.',
                    'On-time is a habit; the score is just the receipt.',
                    'Your payment history is a CV that money reads before people do.',
                ],
            ],
            [
                'key' => 'lender-error', 'title' => 'Wrongly Reported — Fixed', 'icon' => '🧾',
                'chapters' => ['hustler', 'settler', 'builder'], 'is_positive' => false,
                'effect_type' => 'credit_delta', 'effect_data' => ['credit' => -10],
                'probability' => 0.05,
                'description' => 'A reporting error dinged your score before you caught it.',
                'flavors' => [
                    'A loan you cleared showed as "pending". The score dipped before your email did its work.',
                    'Paperwork ghosts: paid in full, reported as late. The dispute is filed; the lesson is free.',
                ],
                'lessons' => [
                    'Check your credit record regularly — errors don\'t fix themselves.',
                    'Keep payment receipts; your records are your defence.',
                    'The score follows the data, and the data sometimes needs a lawyer\'s tone.',
                ],
            ],

            // ── Career moments (compound: cash + credit together) ────────
            [
                'key' => 'raise-earned', 'title' => 'A Raise, Well Earned', 'icon' => '📈',
                'chapters' => ['graduate', 'hustler', 'settler'], 'is_positive' => true,
                'effect_type' => 'compound',
                'effect_data' => ['balance_min' => 400, 'balance_max' => 1500, 'credit' => 6],
                'probability' => 0.05,
                'description' => 'Your consistency got noticed — a bonus landed, and your profile strengthened.',
                'flavors' => [
                    'The boss called you in. You prepared excuses. They offered a bonus. Plot twist of the year.',
                    'HR letter, official stamp, bigger number. Showing up daily finally showed up for you.',
                    '"We\'ve reviewed your contribution…" — the good version of that sentence.',
                ],
                'lessons' => [
                    'Raises follow documented value — keep receipts of your wins.',
                    'The raise is step one; not inflating your lifestyle is step two.',
                    'Bank the difference: same life, bigger savings rate, quiet wealth.',
                ],
            ],
            [
                'key' => 'sector-squeeze', 'title' => 'Hard Times At Work', 'icon' => '🪫',
                'chapters' => ['hustler', 'settler', 'builder'], 'is_positive' => false,
                'effect_type' => 'compound',
                'effect_data' => ['balance_min' => -1200, 'balance_max' => -400, 'credit' => -4],
                'probability' => 0.04,
                'description' => 'Company belt-tightening hit this month\'s finances — and strained some payments.',
                'flavors' => [
                    'A memo with the word "restructuring". Everyone\'s month got a haircut nobody booked.',
                    'The sector sneezed and wallets caught the flu. Time to lean on the budget.',
                ],
                'lessons' => [
                    'One income is one point of failure — build a second stream before you need it.',
                    'Squeezes reward the prepared: low fixed costs are quiet superpowers.',
                    'Your emergency fund is exactly for this sentence.',
                ],
            ],

            // ── Pure narrative (story + lesson, no mechanical effect) ────
            [
                'key' => 'mentor-words', 'title' => 'Words From A Mentor', 'icon' => '🗣️',
                'chapters' => ['student', 'graduate', 'hustler', 'all'], 'is_positive' => true,
                'effect_type' => 'narrative', 'effect_data' => [],
                'probability' => 0.06,
                'description' => 'A chance conversation left you thinking differently about money.',
                'flavors' => [
                    'An elder at the stage told you: "Kijana, money is a good servant but a terrible master." You\'ve been quiet since.',
                    'Your boss\'s boss, in the lift, one floor of wisdom: "Earn like it\'s urgent, spend like it\'s optional."',
                    'Mama Pesa, unprompted: "The market rewards patience and punishes noise." Then she went back to arranging tomatoes.',
                ],
                'lessons' => [
                    'Free advice from someone ahead of you is the highest-yield asset there is.',
                    'Wisdom compounds too — collect it deliberately.',
                    'Listen to people whose results you want, not whose noise you like.',
                ],
            ],
            [
                'key' => 'window-shopping-win', 'title' => 'The Purchase You Didn\'t Make', 'icon' => '🛍️',
                'chapters' => ['student', 'graduate', 'hustler', 'settler', 'all'], 'is_positive' => true,
                'effect_type' => 'narrative', 'effect_data' => [],
                'probability' => 0.06,
                'description' => 'You wanted it. You walked away. A week later, you didn\'t even remember it.',
                'flavors' => [
                    'You held it. You put it back. Outside, the sun felt like a trophy.',
                    'The cart stayed abandoned at checkout. Three days later the urge was gone — and the money wasn\'t.',
                    'You slept on it, and woke up richer than the version of you who didn\'t.',
                ],
                'lessons' => [
                    'The 48-hour rule beats willpower: park the urge, keep the cash.',
                    'Most wants expire on their own if you give them a deadline.',
                    'Every purchase you skip is income you gave yourself.',
                ],
            ],
        ];
    }
}
