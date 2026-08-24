<?php

namespace Database\Seeders;

use App\Http\Controllers\GameSetController;
use App\Models\Setting;
use App\Services\CareerService;
use Illuminate\Database\Seeder;

/**
 * Appends 8 new career fields and 3 new onboarding-quiz questions (one
 * answer option per new field) to whatever's already live in the
 * `career_fields` / `quiz_questions` settings — merges by key/question text
 * rather than overwriting, so an admin's own GameSet Hub edits (or a fresh
 * install still on the code defaults) are both respected. Safe to re-run:
 * already-present fields/questions are skipped.
 */
class CareerContentExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $this->expandFields();
        $this->expandQuiz();
    }

    private function expandFields(): void
    {
        $current = CareerService::fields();
        $existingKeys = collect($current)->pluck('key')->all();

        $newFields = [
            ['key' => 'hospitality', 'label' => 'Hospitality & Tourism', 'icon' => '🏨', 'color' => '#14b8a6', 'track' => 'business',
                'desc' => "You make people feel welcome wherever you go. Hotels, travel, tour guiding, and event planning are where your warmth and organizational flair turn into a thriving career."],
            ['key' => 'sports',      'label' => 'Sports & Fitness',   'icon' => '⚽', 'color' => '#16a34a', 'track' => 'business',
                'desc' => "You live for movement, competition, and pushing limits. Coaching, sports management, personal training, and athlete careers are built for your discipline and drive."],
            ['key' => 'trades',      'label' => 'Skilled Trades',     'icon' => '🛠️', 'color' => '#78716c', 'track' => 'tech',
                'desc' => "You'd rather build it with your hands than talk about it. Electrical work, plumbing, motor mechanics, and welding are steady, in-demand trades where real skill always finds work."],
            ['key' => 'logistics',   'label' => 'Logistics & Transport', 'icon' => '🚚', 'color' => '#f97316', 'track' => 'business',
                'desc' => "You think in routes, schedules, and supply chains. Freight, warehousing, fleet management, and import/export are where your knack for keeping things moving pays off."],
            ['key' => 'environment', 'label' => 'Environment & Conservation', 'icon' => '🌍', 'color' => '#059669', 'track' => 'business',
                'desc' => "You care about the planet's future and want a hand in protecting it. Conservation, renewable energy, waste management, and climate work need people exactly like you."],
            ['key' => 'security',    'label' => 'Security & Public Service', 'icon' => '🛡️', 'color' => '#475569', 'track' => 'business',
                'desc' => "You're the one people count on when things get serious. Police, military, private security, and civil service careers reward your discipline and sense of duty."],
            ['key' => 'realestate',  'label' => 'Real Estate',        'icon' => '🏘️', 'color' => '#eab308', 'track' => 'finance',
                'desc' => "You see value in land and buildings before anyone else does. Property sales, valuation, development, and real estate investment are where your eye for opportunity pays off."],
            ['key' => 'science',     'label' => 'Science & Research', 'icon' => '🔬', 'color' => '#06b6d4', 'track' => 'tech',
                'desc' => "You want to understand how the world actually works. Laboratory science, research, environmental testing, and academia are where your curiosity turns into discovery."],
        ];

        $added = 0;
        foreach ($newFields as $field) {
            if (in_array($field['key'], $existingKeys, true)) {
                continue;
            }
            $current[] = $field;
            $added++;
        }

        Setting::set('career_fields', json_encode(array_values($current)), 'game');
        $this->command?->info("Career Fields: added {$added} new field(s), " . count($current) . ' total.');
    }

    private function expandQuiz(): void
    {
        $json    = Setting::get('quiz_questions', null);
        $current = $json ? (json_decode($json, true) ?: null) : null;
        if (empty($current)) {
            $current = GameSetController::defaultQuizQuestions();
        }

        $existingQuestions = collect($current)->pluck('question')->map(fn ($q) => strtolower(trim($q)))->all();

        $newQuestions = [
            [
                'question' => 'Which of these activities would you actually enjoy doing this weekend?',
                'options'  => [
                    ['emoji' => '🏨', 'label' => 'Plan a trip and manage the whole itinerary', 'sub' => 'Bookings, budgets, making sure everyone has fun',    'fields' => ['hospitality' => 3, 'business' => 1]],
                    ['emoji' => '⚽', 'label' => 'Coach a local team or organize a sports day', 'sub' => 'Drills, teamwork, getting the best out of people',   'fields' => ['sports' => 3, 'healthcare' => 1]],
                    ['emoji' => '🛠️', 'label' => 'Fix something broken with your own hands',    'sub' => 'Wiring, pipes, engines — hands-on problem solving',  'fields' => ['trades' => 3, 'engineering' => 1]],
                    ['emoji' => '🚚', 'label' => 'Organize how goods get from A to B efficiently', 'sub' => 'Routes, schedules, delivery timing',               'fields' => ['logistics' => 3, 'business' => 1]],
                    ['emoji' => '🌍', 'label' => 'Clean up a river or plant trees in your estate', 'sub' => 'Conservation, community environmental action',      'fields' => ['environment' => 3, 'agriculture' => 1]],
                    ['emoji' => '🛡️', 'label' => 'Volunteer for neighborhood watch or a safety drive', 'sub' => 'Keeping people and property safe',               'fields' => ['security' => 3, 'law' => 1]],
                    ['emoji' => '🏘️', 'label' => 'Tour houses for sale just to see what they\'re worth', 'sub' => 'Property, land value, spotting a good deal',      'fields' => ['realestate' => 3, 'finance' => 1]],
                    ['emoji' => '🔬', 'label' => 'Run a small experiment just to see what happens', 'sub' => 'Curiosity, testing ideas, figuring things out',     'fields' => ['science' => 3, 'engineering' => 1]],
                ],
            ],
            [
                'question' => 'Pick a Saturday job that actually sounds fun.',
                'options'  => [
                    ['emoji' => '🏨', 'label' => 'Front desk at a busy hotel or lodge',      'sub' => 'Guests, bookings, making every stay memorable',       'fields' => ['hospitality' => 3]],
                    ['emoji' => '⚽', 'label' => 'Assistant coach at a football academy',    'sub' => 'Training sessions, match day, mentoring players',     'fields' => ['sports' => 3]],
                    ['emoji' => '🛠️', 'label' => 'Apprentice electrician or mechanic',       'sub' => 'Learning a trade that always finds work',             'fields' => ['trades' => 3]],
                    ['emoji' => '🚚', 'label' => 'Dispatch rider or fleet coordinator',      'sub' => 'Deliveries, routes, keeping vehicles moving',          'fields' => ['logistics' => 3]],
                    ['emoji' => '🌍', 'label' => 'Junior ranger at a conservancy',           'sub' => 'Wildlife, habitats, protecting nature',                'fields' => ['environment' => 3]],
                    ['emoji' => '🛡️', 'label' => 'Security intern at a corporate estate',    'sub' => 'Patrols, access control, keeping people safe',         'fields' => ['security' => 3]],
                    ['emoji' => '🏘️', 'label' => 'Real estate agent showing plots on weekends', 'sub' => 'Site visits, negotiating, closing deals',           'fields' => ['realestate' => 3]],
                    ['emoji' => '🔬', 'label' => 'Lab assistant at a research institute',    'sub' => 'Samples, data, careful experiments',                   'fields' => ['science' => 3]],
                ],
            ],
            [
                'question' => 'What headline about your future would make you proudest?',
                'options'  => [
                    ['emoji' => '🏨', 'label' => '"Local youth builds a chain of budget lodges"',        'sub' => 'A hospitality empire built from scratch',           'fields' => ['hospitality' => 3]],
                    ['emoji' => '⚽', 'label' => '"Former player now runs a national sports academy"',    'sub' => 'Turning talent into a lasting institution',         'fields' => ['sports' => 3]],
                    ['emoji' => '🛠️', 'label' => '"Self-taught electrician now runs his own firm"',       'sub' => 'A trade skill grown into a real business',          'fields' => ['trades' => 3, 'business' => 1]],
                    ['emoji' => '🚚', 'label' => '"Startup fixes Kenya\'s last-mile delivery problem"',    'sub' => 'Solving a real logistics headache at scale',        'fields' => ['logistics' => 3, 'technology' => 1]],
                    ['emoji' => '🌍', 'label' => '"Young conservationist restores a dying forest"',        'sub' => 'Real, visible environmental impact',                'fields' => ['environment' => 3]],
                    ['emoji' => '🛡️', 'label' => '"Officer promoted for community safety innovation"',    'sub' => 'Recognized for serving and protecting well',        'fields' => ['security' => 3]],
                    ['emoji' => '🏘️', 'label' => '"First-time investor builds a rental property portfolio"', 'sub' => 'Property income that grows year after year',     'fields' => ['realestate' => 3, 'finance' => 1]],
                    ['emoji' => '🔬', 'label' => '"Kenyan researcher publishes a breakthrough study"',      'sub' => 'Discovery that gets the world\'s attention',        'fields' => ['science' => 3, 'education' => 1]],
                ],
            ],
        ];

        $added = 0;
        foreach ($newQuestions as $q) {
            if (in_array(strtolower(trim($q['question'])), $existingQuestions, true)) {
                continue;
            }
            $current[] = $q;
            $added++;
        }

        Setting::set('quiz_questions', json_encode(array_values($current)), 'game');
        $this->command?->info("Onboarding Quiz: added {$added} new question(s), " . count($current) . ' total.');
    }
}
