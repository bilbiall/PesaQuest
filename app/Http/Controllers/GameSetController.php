<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UploadsImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GameSetController extends Controller
{
    use UploadsImages;

    /**
     * GameSet Hub — the content-manager landing page. Scenario/node tooling
     * was retired; this now routes managers to every live game system.
     */
    public function index()
    {
        $count = function (string $table, string $model, array $activeWhere = ['is_active', true]) {
            if (!Schema::hasTable($table)) return ['total' => 0, 'active' => 0];
            $total  = $model::count();
            $active = $model::where($activeWhere[0], $activeWhere[1])->count();
            return ['total' => $total, 'active' => $active];
        };

        $stats = [
            'assets'      => $count('assets', \App\Models\Asset::class),
            'bills'       => $count('bills', \App\Models\Bill::class),
            'deals'       => $count('investment_deals', \App\Models\InvestmentDeal::class),
            'loans'       => $count('loan_products', \App\Models\LoanProduct::class),
            'courses'     => $count('city_courses', \App\Models\CityCourse::class),
            'jobs'        => $count('city_jobs', \App\Models\CityJob::class),
            'quests'      => $count('quests', \App\Models\Quest::class),
            'badges'      => Schema::hasTable('badges') ? ['total' => \App\Models\Badge::count(), 'active' => \App\Models\Badge::count()] : ['total' => 0, 'active' => 0],
            'life_events' => $count('life_events', \App\Models\LifeEvent::class),
            'fun_world'   => $count('fun_world_activities', \App\Models\FunWorldActivity::class),
            'dreams'      => $count('dreams', \App\Models\Dream::class),
            'challenges'  => $count('challenge_templates', \App\Models\ChallengeTemplate::class),
        ];

        $crises = Schema::hasTable('financial_crises')
            ? \App\Models\FinancialCrisis::orderByDesc('active_from')->take(5)->get()
            : collect();

        $hustleTipsJson = \App\Models\Setting::get('hustle_tips', null);
        $existingTips   = $hustleTipsJson ? (json_decode($hustleTipsJson, true) ?: []) : [];
        $defaultTips    = [
            ['icon' => '💡', 'text' => 'Save at least 20% of every income you earn — even KES 100 saved consistently builds a habit stronger than saving KES 1,000 once.'],
            ['icon' => '📈', 'text' => "Investing means putting money to work so you don't have to work every shilling. Start small with what you have today."],
            ['icon' => '🎯', 'text' => 'Set a specific savings goal with a deadline — goals without deadlines are just wishes.'],
            ['icon' => '🏦', 'text' => 'Your credit score opens doors. Pay bills on time, avoid overlending, and it will improve over time.'],
            ['icon' => '⚡', 'text' => "Multiple income streams reduce risk. Your salary is stream #1 — what's stream #2?"],
        ];
        $hustleTips = !empty($existingTips) ? $existingTips : $defaultTips;

        $milestonesJson    = \App\Models\Setting::get('journey_milestones', null);
        $journeyMilestones = $milestonesJson ? (json_decode($milestonesJson, true) ?: self::defaultMilestones()) : self::defaultMilestones();

        $quizJson      = \App\Models\Setting::get('quiz_questions', null);
        $quizQuestions = $quizJson ? (json_decode($quizJson, true) ?: self::defaultQuizQuestions()) : self::defaultQuizQuestions();

        $maxQuestsPerDay = (int) \App\Models\Setting::get('max_quests_per_day', 0);
        $wywaMinTicks    = (int) \App\Models\Setting::get('wywa_min_ticks', 7);
        $wywaCooldownMin = (int) \App\Models\Setting::get('wywa_cooldown_minutes', 45);
        $mapAmbience     = \App\Models\Setting::get('map_ambience', 'lively') ?: 'lively';
        $ambienceBanner  = (string) \App\Models\Setting::get('ambience_banner', '');
        $forumShowXp     = \App\Models\Setting::get('forum_show_xp', '1') !== '0';
        $forumXpTopic    = (int) \App\Models\Setting::get('forum_xp_topic', 40);
        $forumXpReply    = (int) \App\Models\Setting::get('forum_xp_reply', 25);
        $forumDailyXpCap = (int) \App\Models\Setting::get('forum_daily_xp_cap', 5);
        $lifeChapters    = \App\Models\UserProgress::chapters();
        $careerFields    = \App\Services\CareerService::fields();
        $careerTracks    = \App\Services\CareerService::tracks();
        $financingTerms  = \App\Services\AssetFinancingService::terms();
        $onboardingSteps = \App\Services\OnboardingService::steps();

        return view('admin.gameset', compact(
            'stats', 'crises', 'hustleTips', 'journeyMilestones', 'quizQuestions',
            'maxQuestsPerDay', 'wywaMinTicks', 'wywaCooldownMin', 'lifeChapters',
            'careerFields', 'careerTracks', 'financingTerms', 'onboardingSteps',
            'mapAmbience', 'ambienceBanner', 'forumShowXp',
            'forumXpTopic', 'forumXpReply', 'forumDailyXpCap'
        ));
    }

    // ── Game rules (gameset-editable pace settings) ────────────────────────

    public function saveGameRules(Request $request)
    {
        $data = $request->validate([
            'max_quests_per_day'    => 'required|integer|min:0|max:100',
            'wywa_min_ticks'        => 'required|integer|min:1|max:60',
            'wywa_cooldown_minutes' => 'required|integer|min:0|max:1440',
            'map_ambience'          => 'nullable|in:off,calm,lively',
            'ambience_banner'       => 'nullable|string|max:60',
            'forum_show_xp'         => 'required|boolean',
            'forum_xp_topic'        => 'required|integer|min:0|max:1000',
            'forum_xp_reply'        => 'required|integer|min:0|max:1000',
            'forum_daily_xp_cap'    => 'required|integer|min:1|max:100',
        ]);

        \App\Models\Setting::set('max_quests_per_day',    (string) $data['max_quests_per_day'],    'game');
        \App\Models\Setting::set('wywa_min_ticks',        (string) $data['wywa_min_ticks'],        'game');
        \App\Models\Setting::set('wywa_cooldown_minutes', (string) $data['wywa_cooldown_minutes'], 'game');
        \App\Models\Setting::set('map_ambience',          $data['map_ambience'] ?? 'lively',       'game');
        \App\Models\Setting::set('ambience_banner',       trim((string) ($data['ambience_banner'] ?? '')), 'game');
        \App\Models\Setting::set('forum_show_xp',         $data['forum_show_xp'] ? '1' : '0',      'game');
        \App\Models\Setting::set('forum_xp_topic',        (string) $data['forum_xp_topic'],        'game');
        \App\Models\Setting::set('forum_xp_reply',        (string) $data['forum_xp_reply'],        'game');
        \App\Models\Setting::set('forum_daily_xp_cap',    (string) $data['forum_daily_xp_cap'],     'game');
        return response()->json(['success' => true]);
    }

    // ── Life Chapters (names, icons, taglines & net-worth thresholds) ──────

    public function saveChapters(Request $request)
    {
        $validKeys = array_column(\App\Models\UserProgress::CHAPTER_DEFAULTS, 'key');

        $data = $request->validate([
            'chapters'                    => 'required|array|size:6',
            'chapters.*.key'              => 'required|string|in:' . implode(',', $validKeys),
            'chapters.*.name'             => 'required|string|max:40',
            'chapters.*.icon'             => 'required|string|max:8',
            'chapters.*.tagline'          => 'nullable|string|max:120',
            'chapters.*.min_net_worth'    => 'required|integer|min:0|max:2000000000',
            'chapters.*.location'         => 'nullable|string|max:60',
            'chapters.*.background_image' => 'nullable|string|max:500',
        ]);

        // Every stage key exactly once
        $keys = array_column($data['chapters'], 'key');
        if (count(array_unique($keys)) !== 6) {
            return response()->json(['message' => 'Each of the six chapter stages must appear exactly once.'], 422);
        }

        // Thresholds must strictly ascend in the given order; the first stage is always 0
        $chapters = array_values($data['chapters']);
        $chapters[0]['min_net_worth'] = 0;
        for ($i = 1; $i < count($chapters); $i++) {
            if ($chapters[$i]['min_net_worth'] <= $chapters[$i - 1]['min_net_worth']) {
                return response()->json(['message' => "\"{$chapters[$i]['name']}\" must have a higher net-worth trigger than \"{$chapters[$i - 1]['name']}\"."], 422);
            }
        }

        \App\Models\Setting::set('life_chapters', json_encode($chapters), 'game');
        return response()->json(['success' => true]);
    }

    /** Uploads a chapter backdrop image and hands back its /uploads/ URL —
     *  the chapter row's URL field then holds that URL like any other, so
     *  saveChapters() above needs no changes to accept it. */
    public function uploadChapterBackdrop(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ]);

        $path = $this->resizeContain($request->file('image'), 'life-chapters', 1600, 900, 80);

        return response()->json(['url' => '/uploads/' . $path]);
    }

    // ── Career Fields & Tracks (quiz interests + course/job groupings) ─────

    public function saveCareerFields(Request $request)
    {
        $validTracks = array_column(\App\Services\CareerService::tracks(), 'key');

        $data = $request->validate([
            'fields'              => 'required|array|min:1|max:20',
            'fields.*.key'        => 'required|string|max:30|regex:/^[a-z0-9_]+$/',
            'fields.*.label'      => 'required|string|max:40',
            'fields.*.icon'       => 'required|string|max:8',
            'fields.*.color'      => 'required|string|max:9',
            'fields.*.track'      => 'required|string|in:' . implode(',', $validTracks),
            'fields.*.desc'       => 'nullable|string|max:400',
        ]);

        $keys = array_column($data['fields'], 'key');
        if (count($keys) !== count(array_unique($keys))) {
            return response()->json(['message' => 'Career field keys must be unique.'], 422);
        }

        \App\Models\Setting::set('career_fields', json_encode(array_values($data['fields'])), 'game');
        return response()->json(['success' => true]);
    }

    public function saveCareerTracks(Request $request)
    {
        $data = $request->validate([
            'tracks'          => 'required|array|min:1|max:12',
            'tracks.*.key'    => 'required|string|max:30|regex:/^[a-z0-9_]+$/',
            'tracks.*.label'  => 'required|string|max:40',
            'tracks.*.icon'   => 'required|string|max:8',
            'tracks.*.color'  => 'required|string|max:9',
        ]);

        $keys = array_column($data['tracks'], 'key');
        if (count($keys) !== count(array_unique($keys))) {
            return response()->json(['message' => 'Career track keys must be unique.'], 422);
        }

        // A field cannot reference a track that no longer exists — reassign orphans to the first track
        $fields = \App\Services\CareerService::fields();
        $fallbackTrack = $data['tracks'][0]['key'];
        foreach ($fields as &$f) {
            if (!in_array($f['track'] ?? null, $keys, true)) {
                $f['track'] = $fallbackTrack;
            }
        }
        unset($f);

        \App\Models\Setting::set('career_tracks', json_encode(array_values($data['tracks'])), 'game');
        \App\Models\Setting::set('career_fields', json_encode($fields), 'game');
        return response()->json(['success' => true]);
    }

    // ── Asset financing (vehicle/property deposit, rate & term) ────────────

    public function saveAssetFinancing(Request $request)
    {
        $data = $request->validate([
            'vehicle.deposit_pct'   => 'required|numeric|min:0|max:0.9',
            'vehicle.annual_rate'   => 'required|numeric|min:0|max:100',
            'vehicle.term_ticks'    => 'required|integer|min:30|max:3650',
            'property.deposit_pct'  => 'required|numeric|min:0|max:0.9',
            'property.annual_rate'  => 'required|numeric|min:0|max:100',
            'property.term_ticks'   => 'required|integer|min:30|max:3650',
        ]);

        \App\Models\Setting::set('asset_financing_terms', json_encode($data), 'game');
        return response()->json(['success' => true]);
    }

    // ── Onboarding wizard (first-time-user tutorial steps) ─────────────────

    public function saveOnboardingWizard(Request $request)
    {
        $data = $request->validate([
            'steps'            => 'required|array|min:1|max:20',
            'steps.*.icon'     => 'required|string|max:8',
            'steps.*.category' => 'required|string|max:30',
            'steps.*.title'    => 'required|string|max:80',
            'steps.*.body'     => 'required|string|max:600',
        ]);

        \App\Models\Setting::set('onboarding_wizard_steps', json_encode(array_values($data['steps'])), 'game');
        return response()->json(['success' => true]);
    }

    // ── Level config ───────────────────────────────────────────────────────

    public function getLevelConfig()
    {
        $json = \App\Models\Setting::get('level_config');
        if ($json) {
            $config = json_decode($json, true);
            if (is_array($config)) return response()->json($config);
        }
        return response()->json(self::defaultLevels());
    }

    public function saveLevelConfig(Request $request)
    {
        $data = $request->validate([
            'levels'        => 'required|array|min:2|max:20',
            'levels.*.xp'   => 'required|integer|min:0',
            'levels.*.name' => 'required|string|max:50',
        ]);

        \App\Models\Setting::set('level_config', json_encode($data['levels']), 'game');
        return response()->json(['success' => true]);
    }

    public static function defaultLevels(): array
    {
        return [
            ['xp' => 0,    'name' => 'Novice'],
            ['xp' => 100,  'name' => 'Apprentice'],
            ['xp' => 300,  'name' => 'Explorer'],
            ['xp' => 600,  'name' => 'Saver'],
            ['xp' => 1000, 'name' => 'Investor'],
            ['xp' => 1500, 'name' => 'Entrepreneur'],
            ['xp' => 2100, 'name' => 'Wealth Builder'],
            ['xp' => 2800, 'name' => 'Money Master'],
            ['xp' => 3600, 'name' => 'Finance Pro'],
            ['xp' => 4500, 'name' => 'Millionaire Mindset'],
            ['xp' => 5500, 'name' => 'PesaQuest Legend'],
        ];
    }

    // ── Quiz default data ──────────────────────────────────────────────────

    public static function defaultQuizQuestions(): array
    {
        return [
            [
                'question' => 'What sounds most exciting to do every day?',
                'options'  => [
                    ['emoji' => '💻', 'label' => 'Write code and build apps',          'sub' => 'Creating digital products & solving tech puzzles',    'fields' => ['technology' => 3]],
                    ['emoji' => '🏥', 'label' => 'Treat patients & save lives',        'sub' => 'Working in hospitals, clinics, or community health',   'fields' => ['healthcare' => 3]],
                    ['emoji' => '💰', 'label' => 'Manage money & grow investments',    'sub' => 'Banking, SACCOs, stock market, financial planning',    'fields' => ['finance' => 3]],
                    ['emoji' => '🎨', 'label' => 'Create art, videos & designs',       'sub' => 'Graphic design, photography, film, music',             'fields' => ['creative' => 3]],
                    ['emoji' => '📚', 'label' => 'Teach & inspire the next generation','sub' => 'Classrooms, workshops, training programs',             'fields' => ['education' => 3]],
                    ['emoji' => '🌾', 'label' => 'Grow crops & run an agribusiness',   'sub' => 'Farming, food production, agricultural tech',          'fields' => ['agriculture' => 3]],
                    ['emoji' => '✍️', 'label' => 'Write news & tell powerful stories',  'sub' => 'Journalism, blogging, social media content',           'fields' => ['media' => 3]],
                    ['emoji' => '🔧', 'label' => 'Design & build structures',          'sub' => 'Civil, mechanical or electrical engineering',          'fields' => ['engineering' => 3]],
                ],
            ],
            [
                'question' => 'Where would your dream office be?',
                'options'  => [
                    ['emoji' => '🏢', 'label' => 'A cool tech startup in Nairobi',  'sub' => 'Open plan office, hackathons, free lunch',       'fields' => ['technology' => 3]],
                    ['emoji' => '🏨', 'label' => 'A hospital or health clinic',      'sub' => 'Helping patients every single day',              'fields' => ['healthcare' => 3]],
                    ['emoji' => '🏦', 'label' => 'A bank or investment firm',        'sub' => 'Suits, spreadsheets, making money work',         'fields' => ['finance' => 3]],
                    ['emoji' => '🎬', 'label' => 'A creative studio or agency',      'sub' => 'Art everywhere, big screens, creative briefs',   'fields' => ['creative' => 3]],
                    ['emoji' => '🏫', 'label' => 'A school or training campus',      'sub' => 'Classrooms, students, making a difference',      'fields' => ['education' => 3]],
                    ['emoji' => '🌿', 'label' => 'An open farm or greenhouse',       'sub' => 'Fresh air, hands in the soil, growing things',   'fields' => ['agriculture' => 3]],
                    ['emoji' => '📺', 'label' => 'A media house or newsroom',        'sub' => 'Breaking news, cameras, live broadcasts',        'fields' => ['media' => 3]],
                    ['emoji' => '🏗️', 'label' => 'A construction site or firm',      'sub' => 'Hard hats, blueprints, building things',         'fields' => ['engineering' => 3]],
                ],
            ],
            [
                'question' => 'Which school subject lights you up?',
                'options'  => [
                    ['emoji' => '🖥️', 'label' => 'Mathematics & Computer Science', 'sub' => 'Numbers, algorithms, coding problems',               'fields' => ['technology' => 3, 'finance' => 1]],
                    ['emoji' => '🧬', 'label' => 'Biology & Chemistry',            'sub' => 'Living things, reactions, the human body',           'fields' => ['healthcare' => 3]],
                    ['emoji' => '📊', 'label' => 'Business Studies & Economics',   'sub' => 'Markets, money, how companies work',                  'fields' => ['finance' => 2, 'business' => 2]],
                    ['emoji' => '🎭', 'label' => 'Fine Arts, Music & Drama',       'sub' => 'Creativity, expression, performance',                 'fields' => ['creative' => 3]],
                    ['emoji' => '📖', 'label' => 'Languages & Social Studies',     'sub' => 'Communication, history, understanding people',        'fields' => ['media' => 2, 'education' => 2]],
                    ['emoji' => '🌱', 'label' => 'Agriculture & Home Science',     'sub' => 'Soil, crops, nutrition, food systems',                'fields' => ['agriculture' => 3]],
                    ['emoji' => '⚙️', 'label' => 'Physics & Technical Drawing',   'sub' => 'Forces, machines, how things are built',              'fields' => ['engineering' => 3]],
                    ['emoji' => '⚖️', 'label' => 'Law & Government / CRE',        'sub' => 'Rules, justice, ethics, governance',                  'fields' => ['law' => 3, 'education' => 1]],
                ],
            ],
            [
                'question' => "What's your biggest dream achievement?",
                'options'  => [
                    ['emoji' => '🚀', 'label' => 'Launch an app millions use',              'sub' => 'A product that changes how people live',            'fields' => ['technology' => 3]],
                    ['emoji' => '🩺', 'label' => 'Become a top doctor or surgeon',         'sub' => 'Save lives and lead medical breakthroughs',          'fields' => ['healthcare' => 3]],
                    ['emoji' => '🏆', 'label' => 'Retire as a millionaire investor',        'sub' => 'Passive income, property, a fat portfolio',          'fields' => ['finance' => 3]],
                    ['emoji' => '🎨', 'label' => 'Win a major international award',         'sub' => 'Grammy, Oscar, Cannes Lion, Pulitzer',               'fields' => ['creative' => 2, 'media' => 2]],
                    ['emoji' => '🌟', 'label' => 'Transform how a generation learns',       'sub' => 'Reform schools, publish a curriculum',               'fields' => ['education' => 3]],
                    ['emoji' => '🌍', 'label' => "Build Kenya's biggest agribusiness",      'sub' => 'Export food, employ thousands, feed Africa',         'fields' => ['agriculture' => 3]],
                    ['emoji' => '🏗️', 'label' => 'Design an iconic building or bridge',    'sub' => 'Leave a physical mark on the skyline',               'fields' => ['engineering' => 3]],
                    ['emoji' => '⚖️', 'label' => 'Win a landmark court case',              'sub' => 'Change laws and fight for justice',                  'fields' => ['law' => 3]],
                ],
            ],
            [
                'question' => 'A friend gives you Ksh 10,000 with no strings. What do you do?',
                'options'  => [
                    ['emoji' => '💻', 'label' => 'Buy a coding course or gear',        'sub' => 'Udemy, headphones, a mechanical keyboard',        'fields' => ['technology' => 3]],
                    ['emoji' => '💊', 'label' => 'Donate to a health cause or NGO',    'sub' => 'Help a community clinic or health drive',          'fields' => ['healthcare' => 2, 'education' => 1]],
                    ['emoji' => '📈', 'label' => 'Put it in a money market fund',       'sub' => 'Let it grow quietly at 11% per year',             'fields' => ['finance' => 3]],
                    ['emoji' => '🎨', 'label' => 'Buy art supplies or creative tools', 'sub' => 'Adobe subscription, camera accessories',          'fields' => ['creative' => 3]],
                    ['emoji' => '📚', 'label' => 'Buy educational books & courses',    'sub' => 'Self-improvement, professional certificates',      'fields' => ['education' => 3]],
                    ['emoji' => '🌱', 'label' => 'Invest in farm inputs or a seedling','sub' => 'Try hydroponics, start a small kitchen garden',    'fields' => ['agriculture' => 3]],
                    ['emoji' => '📱', 'label' => 'Start a blog or YouTube channel',    'sub' => 'Content creation, growing an audience',            'fields' => ['media' => 3]],
                    ['emoji' => '🔩', 'label' => 'Buy tools or technical materials',   'sub' => 'Arduino kit, construction materials, lab gear',    'fields' => ['engineering' => 3]],
                ],
            ],
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
    }

    // ── Quiz Questions ─────────────────────────────────────────────────────

    public function saveQuizQuestions(Request $request)
    {
        $validFieldKeys = array_column(\App\Services\CareerService::fields(), 'key');

        $data = $request->validate([
            'questions'                    => 'required|array|min:1|max:10',
            'questions.*.question'         => 'required|string|max:200',
            'questions.*.options'          => 'required|array|min:2|max:8',
            'questions.*.options.*.emoji'  => 'required|string|max:10',
            'questions.*.options.*.label'  => 'required|string|max:80',
            'questions.*.options.*.sub'    => 'nullable|string|max:120',
            'questions.*.options.*.fields' => 'nullable|array',
            'questions.*.options.*.fields.*' => 'integer|min:1|max:5',
        ]);

        // Drop any weight keyed on a career field that no longer exists (renamed/deleted)
        $questions = array_map(function ($q) use ($validFieldKeys) {
            $q['options'] = array_map(function ($opt) use ($validFieldKeys) {
                $opt['fields'] = collect($opt['fields'] ?? [])
                    ->filter(fn ($weight, $key) => in_array($key, $validFieldKeys, true))
                    ->all();
                return $opt;
            }, $q['options']);
            return $q;
        }, $data['questions']);

        \App\Models\Setting::set('quiz_questions', json_encode($questions), 'game');
        return response()->json(['success' => true]);
    }

    // ── Journey Milestones ─────────────────────────────────────────────────

    public function saveJourneyMilestones(Request $request)
    {
        $data = $request->validate([
            'milestones'              => 'required|array',
            'milestones.*.icon'       => 'required|string|max:10',
            'milestones.*.title'      => 'required|string|max:80',
            'milestones.*.description'=> 'nullable|string|max:200',
            'milestones.*.type'       => 'required|in:level,balance,net_worth,job,course,quest,asset,game_day,manual',
            'milestones.*.threshold'  => 'nullable|integer|min:0',
        ]);

        \App\Models\Setting::set('journey_milestones', json_encode($data['milestones']), 'game');
        return response()->json(['success' => true]);
    }

    /**
     * In-app rendering of docs/GAMESET_GUIDE.md — so content-team users get
     * the full setup manual without leaving the portal or needing repo access.
     */
    public function docs()
    {
        $doc = \App\Services\DocsRenderer::render('docs/GAMESET_GUIDE.md');
        return view('gameset.docs', $doc);
    }

    public static function defaultMilestones(): array
    {
        return [
            ['icon' => '🌱', 'title' => 'First Steps',          'description' => 'Begin your PesaQuest journey',          'type' => 'manual',   'threshold' => 0],
            ['icon' => '💼', 'title' => 'First Job',             'description' => 'Get hired for the first time',           'type' => 'job',      'threshold' => 1],
            ['icon' => '📚', 'title' => 'Student of Finance',    'description' => 'Complete your first course',             'type' => 'course',   'threshold' => 1],
            ['icon' => '💰', 'title' => 'First Savings Goal',    'description' => 'Save KES 10,000',                        'type' => 'balance',  'threshold' => 10000],
            ['icon' => '🗺️', 'title' => 'Quest Seeker',          'description' => 'Complete 3 quests',                      'type' => 'quest',    'threshold' => 3],
            ['icon' => '🏘️', 'title' => 'Property Owner',        'description' => 'Buy your first asset',                   'type' => 'asset',    'threshold' => 1],
            ['icon' => '📈', 'title' => 'Investor',              'description' => 'Reach Level 5',                          'type' => 'level',    'threshold' => 5],
            ['icon' => '🏦', 'title' => 'Wealth Builder',        'description' => 'Reach a net worth of KES 200,000',       'type' => 'net_worth','threshold' => 200000],
            // ── Added: deeper progression ladder across every existing trigger
            // type, plus a new time-based "game_day" type for tenure milestones.
            ['icon' => '🎯', 'title' => 'Rising Star',          'description' => 'Reach Level 10',                         'type' => 'level',    'threshold' => 10],
            ['icon' => '🏅', 'title' => 'Money Mind',           'description' => 'Reach Level 15',                         'type' => 'level',    'threshold' => 15],
            ['icon' => '👑', 'title' => 'Financial Guru',       'description' => 'Reach Level 20',                         'type' => 'level',    'threshold' => 20],
            ['icon' => '🚀', 'title' => 'PesaQuest Elite',      'description' => 'Reach Level 25',                         'type' => 'level',    'threshold' => 25],
            ['icon' => '💵', 'title' => 'Steady Saver',         'description' => 'Save KES 50,000',                        'type' => 'balance',  'threshold' => 50000],
            ['icon' => '🏆', 'title' => 'Six-Figure Saver',     'description' => 'Save KES 250,000',                       'type' => 'balance',  'threshold' => 250000],
            ['icon' => '📖', 'title' => 'Lifelong Learner',     'description' => 'Complete 3 courses',                     'type' => 'course',   'threshold' => 3],
            ['icon' => '🎓', 'title' => 'Scholar of Money',     'description' => 'Complete 5 courses',                     'type' => 'course',   'threshold' => 5],
            ['icon' => '🏛️', 'title' => 'Finance Graduate',     'description' => 'Complete 10 courses',                    'type' => 'course',   'threshold' => 10],
            ['icon' => '🗺️', 'title' => 'Quest Champion',       'description' => 'Complete 10 quests',                     'type' => 'quest',    'threshold' => 10],
            ['icon' => '⚔️', 'title' => 'Quest Legend',         'description' => 'Complete 25 quests',                     'type' => 'quest',    'threshold' => 25],
            ['icon' => '🌟', 'title' => 'Quest Master',         'description' => 'Complete 50 quests',                     'type' => 'quest',    'threshold' => 50],
            ['icon' => '👔', 'title' => 'Career Climber',       'description' => 'Get hired 2 times',                      'type' => 'job',      'threshold' => 2],
            ['icon' => '🏠', 'title' => 'Asset Collector',      'description' => 'Own 3 assets',                           'type' => 'asset',    'threshold' => 3],
            ['icon' => '🏙️', 'title' => 'Portfolio Builder',    'description' => 'Own 5 assets',                           'type' => 'asset',    'threshold' => 5],
            ['icon' => '🏰', 'title' => 'Empire Builder',       'description' => 'Own 10 assets',                          'type' => 'asset',    'threshold' => 10],
            ['icon' => '💰', 'title' => 'Half-Millionaire',     'description' => 'Reach a net worth of KES 500,000',       'type' => 'net_worth','threshold' => 500000],
            ['icon' => '💎', 'title' => 'PesaQuest Millionaire','description' => 'Reach a net worth of KES 1,000,000',     'type' => 'net_worth','threshold' => 1000000],
            ['icon' => '🏔️', 'title' => 'Wealth Titan',         'description' => 'Reach a net worth of KES 5,000,000',     'type' => 'net_worth','threshold' => 5000000],
            ['icon' => '🎂', 'title' => 'One Year in Pesa City','description' => 'Play for 365 game days',                 'type' => 'game_day', 'threshold' => 365],
        ];
    }
}
