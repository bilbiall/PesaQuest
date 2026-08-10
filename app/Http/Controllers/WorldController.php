<?php

namespace App\Http\Controllers;

use App\Models\DistrictPosition;
use App\Models\Mission;
use App\Models\Node;
use App\Models\PlayerAsset;
use App\Models\PlayerMission;
use App\Models\Quest;
use App\Models\Setting;
use App\Models\UserQuest;
use App\Services\EventEngine;
use App\Services\LifeSimulator;
use App\Services\QuestTriggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class WorldController extends Controller
{
    private const DISTRICTS = [
        'marketplace' => [
            'slug'         => 'marketplace',
            'name'         => 'Marketplace',
            'tagline'      => 'Buy, Sell & Invest',
            'icon'         => 'cart',
            'color'        => '#15C77E',
            'description'  => 'The heart of Pesa City. Buy devices, assets, shares, and more from the ever-changing market. Every hustle starts with the right tools.',
            'status'       => 'active',
            'mission_slug' => 'get-connected',
            'actions'      => [
                ['label' => 'Browse Market', 'url' => '/marketplace', 'style' => 'primary'],
                ['label' => 'My Portfolio',  'url' => '/portfolio',   'style' => 'secondary'],
            ],
        ],
        'opportunity-hub' => [
            'slug'         => 'opportunity-hub',
            'name'         => 'Opportunity Hub',
            'tagline'      => 'Courses, Gigs & Jobs',
            'icon'         => 'graduation',
            'color'        => '#4DA8F7',
            'description'  => 'Your launchpad in Pesa City. Free courses, job listings, and quick gigs. Every career in this city starts here.',
            'status'       => 'active',
            'mission_slug' => 'level-up-skills',
            'actions'      => [
                ['label' => 'Browse Courses & Gigs', 'url' => '/opportunities', 'style' => 'primary'],
            ],
        ],
        'bank' => [
            'slug'         => 'bank',
            'name'         => 'Equity Square',
            'tagline'      => 'Investment Deals',
            'icon'         => 'trend-up',
            'color'        => '#35C3F0',
            'description'  => 'Risk-based investment deals with real returns. Enter positions, track outcomes, and learn how capital markets work.',
            'status'       => 'active',
            'mission_slug' => null,
            'actions'      => [
                ['label' => 'My Portfolio', 'url' => '/portfolio', 'style' => 'primary'],
            ],
        ],
        'savings' => [
            'slug'         => 'savings',
            'name'         => 'Bank & Savings',
            'tagline'      => 'Loans, Savings & Credit',
            'icon'         => 'bank',
            'color'        => '#F59E0B',
            'description'  => 'Your financial headquarters. Open savings schemes, take or repay loans, and track your credit score.',
            'status'       => 'active',
            'mission_slug' => null,
            'actions'      => [
                ['label' => 'Manage Savings', 'url' => '/savings', 'style' => 'primary'],
                ['label' => 'View Statement', 'url' => '/life',    'style' => 'secondary'],
            ],
        ],
        'estates' => [
            'slug'        => 'estates',
            'name'        => 'Property Quarter',
            'tagline'     => 'Own Land & Build Wealth',
            'icon'        => 'house',
            'color'       => '#A3E635',
            'description' => 'Residential properties — plots, bedsitters, and apartments. Own your first piece of Pesa City land.',
            'status'      => 'locked',
            'unlock_hint' => 'Complete Mission 3 and save KES 200,000 to unlock the Property Quarter.',
        ],
        'car-yard' => [
            'slug'        => 'car-yard',
            'name'        => 'Car Yard',
            'tagline'     => 'Vehicles & Transport',
            'icon'        => 'car',
            'color'       => '#FFBC00',
            'description' => 'Everything on wheels — Bajaj, salon cars, and SUVs. Vehicles are income-generating assets too.',
            'status'      => 'locked',
            'unlock_hint' => 'Get your first job and earn KES 100,000 to unlock the Car Yard.',
        ],
        'fun-world' => [
            'slug'        => 'fun-world',
            'name'        => 'Fun World',
            'tagline'     => 'Relax & Budget for Joy',
            'icon'        => 'ticket',
            'color'       => '#FF6B35',
            'description' => 'Nairobi\'s most electric entertainment district. Ferris wheel, matatu rally tracks, street food arenas, and live music stages. Every ticket teaches you about budgeting for joy — because a good life isn\'t only about saving.',
            'status'      => 'active',
            'experiences' => [
                ['icon' => '🎡', 'name' => 'Ferris Wheel Ride',   'price' => 500,  'lesson' => 'Small treats keep you sane. Budget for them.'],
                ['icon' => '🍖', 'name' => 'Street Food Feast',   'price' => 1200, 'lesson' => 'Social spending builds networks — not all spending is waste.'],
                ['icon' => '🎵', 'name' => 'Live Music Night',    'price' => 2500, 'lesson' => 'Experiences > things. Research backs this up.'],
                ['icon' => '🚐', 'name' => 'Matatu Rally VIP',   'price' => 5000, 'lesson' => 'Premium experiences have premium costs. Plan ahead.'],
            ],
            'rule' => '50 / 30 / 20',
            'rule_desc' => '50% Needs · 30% Wants · 20% Savings. Fun World lives in your 30%.',
            'actions' => [
                ['label' => 'Track My Spending', 'url' => '/life', 'style' => 'primary'],
            ],
        ],
        'community' => [
            'slug'        => 'community',
            'name'        => 'Community Centre',
            'tagline'     => 'Connect & Collaborate',
            'icon'        => 'megaphone',
            'color'       => '#A78BFA',
            'description' => 'The shoutouts feed, Dreams Board, and school leaderboard. Your story belongs here.',
            'status'      => 'active',
            'dreams'      => [
                ['icon' => '💻', 'dream' => 'Laptop for my business'],
                ['icon' => '🏠', 'dream' => 'My own bedsitter in Nairobi'],
                ['icon' => '💰', 'dream' => 'KES 500,000 in savings'],
                ['icon' => '🚗', 'dream' => 'My own car — paid in full'],
                ['icon' => '🌍', 'dream' => 'Sending money home every month, stress-free'],
            ],
            'actions' => [
                ['label' => 'School Portal', 'url' => '/school', 'style' => 'primary'],
            ],
        ],
        'quests' => [
            'slug'        => 'quests',
            'name'        => 'Quest Board',
            'tagline'     => 'Challenges & Rewards',
            'icon'        => 'scroll',
            'color'       => '#FFD700',
            'description' => 'Pick up challenges, earn badges, and prove your financial skills. Every quest completed makes you sharper, richer, and more respected in Pesa City.',
            'status'      => 'active',
            'actions'     => [
                ['label' => 'Browse Quests', 'url' => '/game/quests', 'style' => 'primary'],
            ],
        ],
        'workplace' => [
            'slug'         => 'workplace',
            'name'         => 'Workplace',
            'tagline'      => 'Career & Performance',
            'icon'         => 'building',
            'color'        => '#6366F1',
            'description'  => 'Your career hub in Pesa City. Check today\'s work encounter, review your performance score, and pick up real financial lessons from the office floor.',
            'status'       => 'active',
            'mission_slug' => null,
            'actions'      => [
                ['label' => 'Browse Jobs', 'url' => '/opportunities', 'style' => 'primary'],
            ],
        ],
        'champions-court' => [
            'slug'        => 'champions-court',
            'name'        => "Champions' Court",
            'tagline'     => 'Chase Dreams. Win Challenges.',
            'icon'        => 'trophy',
            'color'       => '#F59E0B',
            'description' => 'Claim expensive, aspirational Dreams as a permanent flex on your profile, or duel friends and the whole city in fair challenges — everyone races on progress made DURING the challenge, never on wealth they already had.',
            'status'      => 'active',
            'actions'     => [
                ['label' => 'Browse Dreams',       'url' => '/dreams',     'style' => 'primary'],
                ['label' => 'Browse Challenges',   'url' => '/challenges', 'style' => 'secondary'],
            ],
        ],
    ];

    /** Read-only district metadata (name/icon/color) for the GameSet map calibrator. */
    public static function districtMeta(): array
    {
        return self::DISTRICTS;
    }

    public function index()
    {
        $user = auth()->user();

        // Run life-sim catchup on world entry (same guard as DashboardController)
        $lifeSim = [];
        try {
            $lifeSim = app(LifeSimulator::class)->processLogin($user);
            $user->progress?->refresh();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WorldController LifeSimulator failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        $progress = $user->progress;

        // "Last Played" + streak must reflect every real visit, not only visits
        // where XP happens to be earned (addPoints() is the only other writer
        // of last_played_at, which made genuinely-active players look stale).
        if ($progress) {
            $progress->last_played_at = now();
            $progress->save();
        }
        $user->streak()->firstOrCreate([])->recordActivity();

        // First-time tutorial wizard — the career quiz redirects here after
        // completion, so this is often the FIRST page a player sees post-quiz;
        // must be able to show the wizard here too, not just on the Dashboard.
        $needsCareerQuiz = ($progress?->career_field === null || $progress?->career_field === '')
            && (($progress?->career_income_rate ?? 0) === 0);
        $showOnboardingWizard = \App\Services\OnboardingService::shouldShow($user, $needsCareerQuiz);
        $onboardingSteps      = \App\Services\OnboardingService::steps();

        // ── Dynamic district unlocks ──────────────────────────────────────────
        // Compute player-specific unlock eligibility for Phase 11 districts
        $balance           = $progress?->balance ?? 0;
        $completedMissions = 0;
        if (Schema::hasTable('player_missions')) {
            $completedMissions = PlayerMission::where('user_id', $user->id)
                ->where('status', 'completed')->count();
        }
        // Kiambu Estates: balance ≥ 200 000 OR 3+ completed missions
        $estatesUnlocked = $balance >= 200000 || $completedMissions >= 3;
        // Jua Kali Car Yard: balance ≥ 100 000 AND career set
        $carYardUnlocked  = $balance >= 100000 && !empty($progress?->career_field);

        // Build districts with live status overrides (never mutate the const directly)
        $districts = self::DISTRICTS;
        if ($estatesUnlocked) {
            $districts['estates']['status']  = 'active';
            $districts['estates']['actions'] = [
                ['label' => 'Browse Properties', 'url' => '#', 'style' => 'primary'],
                ['label' => 'Calculate Mortgage', 'url' => '/savings', 'style' => 'secondary'],
            ];
        }
        if ($carYardUnlocked) {
            $districts['car-yard']['status']  = 'active';
            $districts['car-yard']['actions'] = [
                ['label' => 'Browse Vehicles', 'url' => '#', 'style' => 'primary'],
                ['label' => 'My Portfolio',    'url' => '/portfolio', 'style' => 'secondary'],
            ];
        }

        // Colors are a pure visual accent with no admin config surface; name comes
        // from the SAME admin-configurable source as everywhere else (GameSet Hub
        // → Life Chapters via UserProgress::chapters()) so it never drifts.
        $chapterColors = ['student' => '#4DA8F7', 'graduate' => '#35C3F0', 'hustler' => '#15C77E', 'settler' => '#FFBC00', 'builder' => '#FF6B35', 'elder' => '#A78BFA'];

        $chapter     = $progress?->life_chapter ?? 'student';
        $chapterRow  = \App\Models\UserProgress::chapterMeta($chapter);
        $chapterMeta = ['label' => preg_replace('/^The\s+/i', '', $chapterRow['name']), 'color' => $chapterColors[$chapter] ?? '#4DA8F7'];

        $xpPercent = max(0, min(100, $progress?->level_progress_percent ?? 0));

        // Bottom journey bar — same admin-configured milestones (GameSet Hub →
        // Journey Milestones) and achievement logic as the /life timeline.
        $journeyMilestones = $progress ? \App\Models\UserProgress::journeyMilestonesFor($user, $progress) : [];

        // Mission engine — safe behind Schema check so the page works before migrations run
        $activeMission  = null;
        $missionDistricts = [];
        if (Schema::hasTable('missions') && Schema::hasTable('player_missions')) {
            $this->bootFirstMission($user);
            $activeMission    = $this->getActiveMission($user);
            $missionDistricts = $this->getMissionDistrictSlugs($user);
        }

        // EventEngine — load contextual world events for this session
        // Graceful: never breaks the page, returns [] if nodes table is empty
        $sessionEvents = [];
        if ($progress) {
            try {
                $sessionEvents = (new EventEngine($progress))->get();
            } catch (\Throwable $e) {
                // Silently continue — events are enhancement, not core
            }
        }

        // Pull any quest completions triggered from other pages (savings, marketplace, etc.)
        $pendingQuestCompletions = session()->pull('pending_quest_completions', []);

        // Pull step-fired notifications (multi-trigger quests, step fired but not yet complete)
        $pendingStepFires = session()->pull('pending_step_fires', []);

        // Active + recently completed quests, level-gated, in_progress first
        $activeQuests = [];
        if (Schema::hasTable('user_quests') && Schema::hasTable('quests')) {
            try {
                $playerLevel  = $progress?->level ?? 1;
                $recentCutoff = now()->subDays(3);

                $activeQuests = UserQuest::where('user_id', $user->id)
                    ->where(fn($q) => $q
                        ->whereNull('completed_at')
                        ->orWhere('completed_at', '>=', $recentCutoff)
                    )
                    ->with('quest')
                    ->get()
                    ->filter(fn($uq) => $uq->quest && ($uq->quest->level_required ?? 1) <= $playerLevel)
                    ->map(fn($uq) => [
                        'id'            => $uq->quest->id,
                        'title'         => $uq->quest->title ?? '',
                        'description'   => $uq->quest->description ?? '',
                        'instructions'  => preg_replace('/\[TRIGGER:[^\]]+\]\s*/', '', $uq->quest->instructions ?? ''),
                        'lesson'        => $uq->quest->lesson ?? '',
                        'icon'          => $uq->quest->icon ?? '📜',
                        'image'         => $uq->quest->image ? asset('storage/'.$uq->quest->image) : null,
                        'xp_reward'     => $uq->quest->xp_reward ?? 0,
                        'kes_reward'    => $uq->quest->kes_reward ?? 0,
                        'trigger_label' => $uq->quest->trigger_label,
                        'trigger_type'  => $uq->quest->trigger_type,
                        'trigger_value' => $uq->quest->trigger_value,
                        'status'        => $uq->completed_at ? 'completed' : ($uq->isPending() ? 'reviewing' : 'in_progress'),
                        '_sort'         => $uq->completed_at ? 2 : ($uq->isPending() ? 1 : 0),
                    ])
                    ->filter(fn($q) => $q['title'])
                    ->sortBy('_sort')
                    ->map(fn($q) => array_diff_key($q, ['_sort' => '']))
                    ->values()
                    ->toArray();
            } catch (\Throwable $e) {}
        }

        // Quest Gate: is banked XP waiting behind unfinished quests?
        $questGate = ['blocked' => false];
        try {
            if ($progress) $questGate = \App\Services\QuestGate::status($progress);
        } catch (\Throwable $e) {}

        // Active investments for sidebar Quick Actions card
        $activeInvestments = [];
        try {
            $activeInvestments = PlayerAsset::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('asset')
                ->get()
                ->filter(fn($pa) => in_array($pa->asset?->category ?? '', ['investment','vehicle','property','business']))
                ->take(8)
                ->map(fn($pa) => [
                    'name'           => $pa->asset?->name ?? 'Asset',
                    'icon'           => $pa->asset?->icon ?? 'briefcase',
                    'category'       => $pa->asset?->category ?? '',
                    'purchase_price' => $pa->purchase_price,
                    'current_value'  => $pa->current_value,
                    'quantity'       => $pa->quantity,
                    'condition'      => $pa->condition ?? 100,
                    'gain_pct'       => $pa->gainLossPct(),
                    'monthly_income' => ($pa->asset?->monthly_income ?? 0) * $pa->quantity,
                ])
                ->values()
                ->toArray();
        } catch (\Throwable $e) {}


        // Seed daily smart reminders into notification bell (once per day)
        try { $this->_seedSmartReminders($user, $progress); } catch (\Throwable $e) {}

        // Hustle tips — load from settings, fall back to defaults
        $hustleTipsJson = Setting::get('hustle_tips', null);
        $hustleTips = $hustleTipsJson ? (json_decode($hustleTipsJson, true) ?: []) : [];
        if (empty($hustleTips)) {
            $hustleTips = [
                ['icon' => '💡', 'text' => 'Save at least 20% of every income you earn — even KES 100 saved consistently builds a habit stronger than saving KES 1,000 once.'],
                ['icon' => '📈', 'text' => 'Investing means putting money to work so you don\'t have to work every shilling. Start small with what you have today.'],
                ['icon' => '🎯', 'text' => 'Set a specific savings goal with a deadline — goals without deadlines are just wishes.'],
                ['icon' => '🏦', 'text' => 'Your credit score opens doors. Pay bills on time, avoid overlending, and it will improve over time.'],
                ['icon' => '⚡', 'text' => 'Multiple income streams reduce risk. Your salary is stream #1 — what\'s stream #2?'],
            ];
        }

        return view('world.index', [
            'user'                    => $user,
            'progress'                => $progress,
            'balance'                 => $progress?->balance ?? 0,
            'level'                   => $progress?->level ?? 1,
            'xpPercent'               => $xpPercent,
            'netWorth'                => $progress?->net_worth_cache ?? 0,
            'creditScore'             => $progress?->credit_score ?? 500,
            'mood'                    => $progress?->mood ?? 70,
            'chapter'                 => $chapterMeta['label'],
            'chapterColor'            => $chapterMeta['color'],
            'recentBadges'            => $user->badges()->latest()->take(3)->get(),
            'districts'               => $districts,
            'districtPositions'       => DistrictPosition::allBySlug(),
            'activeMission'           => $activeMission,
            'missionDistricts'        => $missionDistricts,
            'sessionEvents'           => $sessionEvents,
            'lifeSim'                 => $lifeSim,
            'pendingQuestCompletions' => $pendingQuestCompletions,
            'pendingStepFires'        => $pendingStepFires,
            'activeQuests'            => $activeQuests,
            'questGate'               => $questGate,
            'activeInvestments'       => $activeInvestments,
            'hustleTips'              => $hustleTips,
            'showOnboardingWizard'    => $showOnboardingWizard,
            'onboardingSteps'         => $onboardingSteps,
            'journeyMilestones'       => $journeyMilestones,
            'needsCareerQuiz'         => $needsCareerQuiz,
        ]);
    }

    public function district(string $slug)
    {
        if (!isset(self::DISTRICTS[$slug])) {
            return response()->json(['error' => 'District not found'], 404);
        }

        $district = self::DISTRICTS[$slug];
        $user     = auth()->user();

        // Apply dynamic unlock overrides same as index() does
        $progress   = $user->progress;
        $balance    = $progress?->balance ?? 0;
        $completedMissions = Schema::hasTable('player_missions')
            ? \App\Models\PlayerMission::where('user_id', $user->id)->where('status', 'completed')->count()
            : 0;

        if ($slug === 'marketplace') {
            // A small taste of what's on offer — full browsing still happens
            // on the real Marketplace page, this is just enough to make the
            // district panel feel alive instead of two bare buttons.
            $district['featured_assets'] = \App\Models\Asset::active()
                ->orderBy('base_price')
                ->limit(4)
                ->get(['id', 'name', 'icon', 'image_url', 'base_price', 'monthly_income'])
                ->map(fn ($a) => [
                    'id'             => $a->id,
                    'name'           => $a->name,
                    'icon'           => $a->icon,
                    'image_url'      => $a->image_url,
                    'base_price'     => $a->base_price,
                    'monthly_income' => $a->monthly_income,
                ])->values();
        }

        if ($slug === 'estates') {
            $district['unlock_balance']  = $balance;
            $district['unlock_required'] = 200000;
            $district['unlock_pct']      = min(100, (int) ($balance / 200000 * 100));
            $district['unlock_missions'] = $completedMissions;
            if ($balance >= 200000 || $completedMissions >= 3) {
                $district['status'] = 'active';
            } else {
                $district['unlock_hint'] = 'Save KES 200,000 OR complete 3 missions to unlock Properties. You\'re ' . min(100, (int)($balance/2000)) . '% there by savings.';
            }
        }
        if ($slug === 'car-yard') {
            $career = $progress?->career_field;
            if ($balance >= 100000 && !empty($career)) {
                $district['status'] = 'active';
            } else {
                $hint = '';
                if (empty($career)) $hint .= 'Set a career track first. ';
                if ($balance < 100000) $hint .= 'Need KES ' . number_format(100000 - $balance) . ' more savings.';
                $district['unlock_hint'] = trim($hint) ?: 'Keep building your balance and career.';
            }
        }

        if ($slug === 'bank' || $slug === 'savings') {
            $progress = $user->progress;
            $district['credit_score'] = $progress?->credit_score ?? 500;
            $district['balance']      = $progress?->balance ?? 0;
            $score = $district['credit_score'];
            $district['credit_label'] = $score >= 750 ? 'Excellent' : ($score >= 650 ? 'Good' : ($score >= 580 ? 'Fair' : ($score >= 500 ? 'Poor' : 'Very Poor')));
            $district['credit_color'] = $score >= 650 ? '#15C77E' : ($score >= 500 ? '#FFBC00' : '#EF5350');

            if (Schema::hasTable('savings_schemes')) {
                $schemes = \DB::table('savings_schemes')
                    ->where('user_id', $user->id)
                    ->where('is_archived', false)
                    ->get(['id', 'name', 'emoji', 'target_amount', 'current_amount']);
                $district['savings_schemes'] = $schemes->map(fn($s) => [
                    'name'         => $s->name,
                    'emoji'        => $s->emoji ?? '💰',
                    'progress_pct' => $s->target_amount > 0 ? min(100, round($s->current_amount / $s->target_amount * 100)) : 0,
                    'current'      => (float) $s->current_amount,
                    'target'       => (float) $s->target_amount,
                ])->values();
                $district['total_savings'] = (float) $schemes->sum('current_amount');
            }

            // Investment Deals
            if (Schema::hasTable('investment_deals')) {
                $deals = \DB::table('investment_deals')
                    ->where('is_active', true)
                    ->orderBy('sort_order')->orderBy('risk_level')
                    ->get();
                $district['deals'] = $deals->map(fn($d) => [
                    'id'                  => $d->id,
                    'title'               => $d->title,
                    'description'         => $d->description,
                    'category'            => $d->category,
                    'icon'                => $d->icon,
                    'cost'                => (int) $d->cost,
                    'min_return_pct'      => (float) $d->min_return_pct,
                    'max_return_pct'      => (float) $d->max_return_pct,
                    'success_probability' => (float) $d->success_probability,
                    'maturity_ticks'      => (int) $d->maturity_ticks,
                    'risk_level'          => (int) $d->risk_level,
                    'lesson'              => $d->lesson,
                ])->values();
            }

            // Pending player deals
            if (Schema::hasTable('player_deals')) {
                $myDeals = \DB::table('player_deals')
                    ->join('investment_deals', 'player_deals.deal_id', '=', 'investment_deals.id')
                    ->where('player_deals.user_id', $user->id)
                    ->orderByDesc('player_deals.created_at')
                    ->limit(10)
                    ->get(['player_deals.*', 'investment_deals.title as deal_title', 'investment_deals.icon as deal_icon']);
                $district['my_deals'] = $myDeals->map(fn($pd) => [
                    'id'             => $pd->id,
                    'title'          => $pd->deal_title,
                    'icon'           => $pd->deal_icon,
                    'amount'         => (int) $pd->amount_invested,
                    'status'         => $pd->status,
                    'profit_loss'    => (int) $pd->profit_loss,
                    'resolve_at'     => (int) $pd->resolve_at_tick,
                ])->values();
            }

            // Active loans
            if (Schema::hasTable('player_loans')) {
                $myLoans = \App\Models\PlayerLoan::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->with('loanProduct')
                    ->get();
                $currentTick = (int) ($user->progress?->tick_count ?? 0);
                $district['my_loans'] = $myLoans->map(fn($l) => [
                    'id'                  => $l->id,
                    'name'                => $l->displayName(),
                    'icon'                => $l->loanProduct?->icon ?? '🏦',
                    'is_financing'        => (bool) $l->player_asset_id,
                    'outstanding_balance' => $l->outstanding_balance,
                    'payment_amount'      => $l->payment_amount,
                    'payments_made'       => $l->payments_made,
                    'total_installments'  => $l->totalInstallments(),
                    'next_payment_tick'   => $l->next_payment_tick,
                    'next_due_in_days'    => max(0, ($l->next_payment_tick ?? 0) - $currentTick),
                    'due_at_tick'         => $l->due_at_tick,
                ])->values();
            }

            // Available loan products
            if (Schema::hasTable('loan_products')) {
                $loanProducts = \App\Models\LoanProduct::where('is_active', true)
                    ->orderBy('sort_order')->orderBy('min_credit_score')
                    ->get();
                $district['loan_products'] = $loanProducts->map(fn($lp) => [
                    'id'                   => $lp->id,
                    'name'                 => $lp->name,
                    'icon'                 => $lp->icon,
                    'description'          => $lp->description,
                    'min_amount'           => $lp->min_amount,
                    'max_amount'           => $lp->max_amount,
                    'annual_interest_rate' => $lp->annual_interest_rate,
                    'term_ticks'           => $lp->term_ticks,
                    'payment_period_ticks' => $lp->payment_period_ticks,
                    'min_credit_score'     => $lp->min_credit_score,
                    'eligible'             => $score >= $lp->min_credit_score,
                ])->values();
            }

            // Tradable shares (Equity Square "Shares" tab) — random-walk priced,
            // shared across all players, distinct from the one-shot Investment Deals above.
            if (Schema::hasTable('shares')) {
                app(\App\Services\ShareMarketService::class)->refreshIfStale();

                $shares = \App\Models\Share::active()->orderBy('sort_order')->get();
                $district['shares'] = $shares->map(fn($s) => $s->toMarketPayload())->values();

                // "Today's movers" — biggest gainer/loser among shares that have
                // actually moved at least once (skip brand-new flat-lined ones).
                $moved = $shares->filter(fn($s) => $s->previous_price !== null && $s->priceChangePct() != 0);
                $topGainer = $moved->where('current_price', '>', 0)->sortByDesc(fn($s) => $s->priceChangePct())->first();
                $topLoser  = $moved->sortBy(fn($s) => $s->priceChangePct())->first();
                $district['top_gainer'] = ($topGainer && $topGainer->priceChangePct() > 0) ? [
                    'symbol' => $topGainer->symbol, 'icon' => $topGainer->icon, 'change_pct' => $topGainer->priceChangePct(),
                ] : null;
                $district['top_loser'] = ($topLoser && $topLoser->priceChangePct() < 0) ? [
                    'symbol' => $topLoser->symbol, 'icon' => $topLoser->icon, 'change_pct' => $topLoser->priceChangePct(),
                ] : null;

                $district['my_shares'] = \App\Models\PlayerShareHolding::where('user_id', $user->id)
                    ->where('quantity', '>', 0)
                    ->with('share')
                    ->get()
                    ->filter(fn($h) => $h->share !== null)
                    ->map(fn($h) => $h->toHoldingPayload())->values();
            }

            $district['credit_tips'] = $score < 650
                ? [
                    'Pay all bills on time — even one late payment drops your score by 50+ points.',
                    'Keep your debt-to-income ratio below 30%. Lenders check this first.',
                    'Repay loans early to unlock better products and higher credit limits.',
                ]
                : [
                    'Your score is ' . $district['credit_label'] . '. Maintain it by keeping payments on time.',
                    'A score above 750 unlocks the best loan rates in Kenya — that\'s your next target.',
                ];
        }

        if ($slug === 'fun-world') {
            $income  = $user->progress?->career_income_rate ?? 0;
            $mood    = $user->progress?->mood ?? 70;
            $district['monthly_income']       = $income;
            $district['entertainment_budget'] = (int) ($income * 0.15);

            // Fun spend so far this game month (30-tick window, converted to real time)
            $tick          = $user->progress?->tick_count ?? 0;
            $ticksIntoMonth = $tick % 30;
            $clock         = app(\App\Services\GameClock::class);
            $monthStartAt  = now()->subSeconds($clock->realSecondsForTicks($ticksIntoMonth));
            $district['fun_spent_month'] = (int) \App\Models\GameNotification::where('user_id', $user->id)
                ->where('type', 'fun_world')
                ->where('created_at', '>=', $monthStartAt)
                ->get()
                ->sum(fn($n) => (int) ($n->data['price'] ?? 0));

            $district['balance']              = $user->progress?->balance ?? 0;
            $district['mood']                 = $mood;
            $district['mood_last_boosted_at'] = $user->progress?->mood_last_boosted_at?->diffForHumans() ?? null;
            $district['mood_bonus_active']    = $mood > 80;
            $district['mood_penalty_active']  = $mood < 40;

            // Admin-managed activities replace the hardcoded experience list
            if (Schema::hasTable('fun_world_activities')) {
                $dbActivities = \App\Models\FunWorldActivity::active()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn($a) => [
                        'id'     => $a->id,
                        'icon'   => $a->icon,
                        'name'   => $a->name,
                        'price'  => (int) $a->price,
                        'lesson' => $a->description ?? '',
                        'boost'  => $a->moodBoost(),
                        'xp'     => (int) $a->xp_reward,
                    ])
                    ->values()
                    ->all();
                if (!empty($dbActivities)) {
                    $district['experiences'] = $dbActivities;
                }
            }
        }

        if ($slug === 'estates') {
            $balance = $user->progress?->balance ?? 0;
            $district['balance'] = $balance;
            $district['properties'] = $this->financedListings([
                [
                    'icon'           => '🌳',
                    'name'           => '1/8 Acre Plot — Mavoko',
                    'type'           => 'plot',
                    'asset_id'       => 10,
                    'price'          => 180000,
                    'rental_income'  => 0,
                    'lesson'         => 'Land appreciates 8–12% per year in Nairobi\'s peri-urban areas. Own the land, own the future.',
                ],
                [
                    'icon'           => '🛏️',
                    'name'           => 'Bedsitter — Umoja',
                    'type'           => 'bedsitter',
                    'asset_id'       => 13,
                    'price'          => 950000,
                    'rental_income'  => 18000,
                    'lesson'         => 'A bedsitter earning KES 18,000/month is a KES 216,000 annual return on a KES 950K property.',
                ],
                [
                    'icon'           => '🏠',
                    'name'           => 'Studio Apartment — Ruaka',
                    'type'           => 'studio',
                    'asset_id'       => 12,
                    'price'          => 2850000,
                    'rental_income'  => 42000,
                    'lesson'         => 'Ruaka is one of Nairobi\'s fastest-growing zones. Rental demand is consistently high from tech workers.',
                ],
            ], $balance);
        }

        if ($slug === 'car-yard') {
            $balance      = $user->progress?->balance ?? 0;
            $career       = $user->progress?->career_field ?? '';
            $district['balance'] = $balance;
            $district['career']  = $career;
            $district['vehicles'] = $this->financedListings([
                [
                    'icon'           => '🛵',
                    'name'           => 'Bajaj Boxer — Boda Boda',
                    'type'           => 'boda',
                    'asset_id'       => 1,
                    'price'          => 80000,
                    'gig_income'     => 18000,
                    'lesson'         => 'A Bajaj boda earns KES 600–900/day. After fuel and loan, net income can reach KES 15,000/month.',
                ],
                [
                    'icon'           => '🚗',
                    'name'           => 'Toyota Fielder — Taxi / Personal',
                    'type'           => 'salon',
                    'asset_id'       => 5,
                    'price'          => 1650000,
                    'gig_income'     => 65000,
                    'lesson'         => 'A Fielder on Uber/Bolt earns KES 1,200–1,800/day. Run it yourself or hire a driver — both models pay.',
                ],
                [
                    'icon'           => '🚐',
                    'name'           => 'Matatu 14-Seater — Shuttle / Route',
                    'type'           => 'van',
                    'asset_id'       => 6,
                    'price'          => 1200000,
                    'gig_income'     => 90000,
                    'lesson'         => 'A matatu on a busy Nairobi route collects KES 3,000–5,000 per day. With a good tout and driver, you can run it passively.',
                ],
            ], $balance);
        }

        if ($slug === 'community') {
            $district['total_players']  = \App\Models\User::count();
            $district['badges_earned']  = Schema::hasTable('user_badges')
                ? \DB::table('user_badges')->count()
                : 0;
        }

        if ($slug === 'workplace') {
            $district['career_field']   = $progress?->career_field ?? null;
            $district['monthly_income'] = $progress?->career_income_rate ?? 0;

            // Current jobs with full details
            $activeJobs = \App\Models\PlayerCityJob::where('user_id', $user->id)
                ->where('status', 'employed')
                ->with('job:id,title,employer_name,employer_logo,salary_kes_month,level,career_track,career_tracks,is_part_time')
                ->get();

            $district['active_jobs'] = $activeJobs->map(fn($pj) => [
                'player_job_id'   => $pj->id,
                'job_id'          => $pj->job?->id,
                'title'           => $pj->job ? $pj->displayTitle() : 'Unknown',
                'employer_name'   => $pj->job?->employer_name ?? '',
                'employer_logo'   => $pj->job?->employer_logo ?? '🏢',
                'salary'          => $pj->job ? $pj->effectiveSalary() : 0,
                'level'           => $pj->job ? $pj->effectiveLevel() : 1,
                'level_label'     => match($pj->job ? $pj->effectiveLevel() : 1) { 1=>'Entry', 2=>'Mid', 3=>'Senior', default=>'Pro' },
                'career_track'    => $pj->job?->career_track ?? '',
                'career_tracks'   => $pj->job?->careerTrackList() ?? [],
                'employment_type' => $pj->employment_type,
                'ticks_employed'  => $pj->ticks_employed,
            ])->values()->toArray();

            $district['job_count']     = $activeJobs->count();
            $district['max_jobs']      = 3;
            $district['is_employed']   = $activeJobs->count() > 0;
            $district['total_salary']  = $activeJobs->sum(fn($pj) => $pj->job ? $pj->effectiveSalary() : 0);

            $encounters = [
                ['icon' => '📋', 'title' => 'Performance Review',    'lesson' => 'Documenting achievements increases your salary negotiation power by 40%. Keep records of every win.'],
                ['icon' => '☕', 'title' => 'Office Chat',           'lesson' => 'Your colleague bought a house on a teacher\'s salary. Their secret: 5-year SACCO membership. Consistent saving works.'],
                ['icon' => '📧', 'title' => 'Salary Negotiation',    'lesson' => 'Professionals who negotiate their salary earn KES 1M+ more over their careers than those who never ask.'],
                ['icon' => '🏆', 'title' => 'Employee of the Month', 'lesson' => 'Consistent performance fast-tracks promotions and salary bumps. Employers notice who shows up every day.'],
                ['icon' => '📊', 'title' => 'Budget Meeting',        'lesson' => 'Understanding your company\'s finances signals leadership potential. Ask to sit in — it opens doors.'],
                ['icon' => '🤝', 'title' => 'Networking Lunch',      'lesson' => '70% of jobs are never advertised. Your next opportunity comes through who you know — invest in relationships.'],
                ['icon' => '💡', 'title' => 'Side Project Idea',     'lesson' => 'Many successful businesses started as weekend projects. Your skills have value beyond your employer\'s paycheque.'],
                ['icon' => '📈', 'title' => 'Salary Growth Check',   'lesson' => 'Your salary should grow 10–15% per year. If it\'s not, negotiate now or start looking — you have leverage.'],
            ];
            $seed = abs(crc32(date('Y-m-d') . $user->id));
            $district['today_encounter'] = $encounters[$seed % count($encounters)];

            // Real raise/promotion progress — replaces an old level-gated stub
            // that showed "eligible" forever with nothing ever acting on it.
            // Raises/promotions themselves are applied automatically during the
            // tick catch-up (LifeSimulator::settlePromotions), so what's shown
            // here is always "progress toward the next one", not a dead flag.
            $primaryJob = $activeJobs->sortByDesc(fn($pj) => $pj->job?->salary_kes_month ?? 0)->first();
            if ($primaryJob && $primaryJob->job) {
                $sinceReview   = (int) $primaryJob->ticks_employed - (int) $primaryJob->ticks_employed_at_last_review;
                $isClean       = (int) $primaryJob->missed_paydays === 0;
                $disqualified  = (bool) ($primaryJob->promotion_disqualified ?? false);
                $raiseTicks    = 90;
                $titleTicks    = 360;
                $nextJob       = $primaryJob->job->promotes_to_job_id
                    ? \App\Models\CityJob::find($primaryJob->job->promotes_to_job_id)
                    : app(\App\Services\LifeSimulator::class)->findNextTierJob($primaryJob->job, $user);

                $district['perf_score']              = $isClean ? min(100, (int) round($sinceReview / $raiseTicks * 100)) : 25;
                // "Eligible" now means an actual promotion review is imminent —
                // over a year of tenure, currently clean, and never disqualified —
                // not just "not delinquent right now", which used to make a
                // day-1 hire look promotion-ready.
                $district['promotion_eligible']       = $isClean && !$disqualified && $sinceReview >= $titleTicks;
                $district['promotion_disqualified']   = $disqualified;
                $district['promotions_count']         = (int) $primaryJob->promotions_count;

                if ($disqualified) {
                    $probationLeft = max(0, (int) ($primaryJob->promotion_probation_until_tick ?? 0) - (int) $primaryJob->ticks_employed);
                    $district['next_milestone'] = $probationLeft > 0
                        ? "This job's promotion track is on probation for missing too many paydays — stay clean for {$probationLeft} more game day" . ($probationLeft === 1 ? '' : 's') . " to earn it back. Raises still apply either way."
                        : 'Your promotion probation is served — back on track as soon as your next clean review lands.';
                } elseif (!$isClean) {
                    $district['next_milestone'] = 'Report to Work to clear your missed paydays — raises pause until your attendance is clean.';
                } else {
                    $raiseIn = max(0, $raiseTicks - $sinceReview);
                    $district['next_milestone'] = $raiseIn > 0
                        ? "Next pay raise in {$raiseIn} game day" . ($raiseIn === 1 ? '' : 's') . '.'
                        : 'A pay raise applies at your next payday review.';

                    $titleIn = max(0, $titleTicks - $sinceReview);
                    $atSenior = $primaryJob->effectiveLevel() >= 3;
                    if ($nextJob) {
                        $district['next_milestone'] .= $titleIn > 0
                            ? " Promotion to {$nextJob->title} in {$titleIn} game days — keep a clean attendance record until then."
                            : " Promotion to {$nextJob->title} due at your next review.";
                    } elseif ($atSenior) {
                        $district['next_milestone'] .= " You've hit Senior tenure here — check the Opportunity Hub for a real Senior role to keep climbing.";
                    } else {
                        $district['next_milestone'] .= $titleIn > 0
                            ? " Title bump in {$titleIn} game days (no bigger role open there yet) — keep a clean attendance record until then."
                            : ' A title bump is due at your next review.';
                    }
                }
            } else {
                $district['perf_score']             = 40;
                $district['promotion_eligible']     = false;
                $district['promotion_disqualified'] = false;
                $district['promotions_count']       = 0;
                $district['next_milestone']         = 'Get hired at the Opportunity Hub to start earning raises and promotions.';
            }
            if ($district['monthly_income'] > 0) {
                $inv = (int) ($district['monthly_income'] * 0.20);
                $district['invest_tip'] = 'Investing 20% of your KES ' . number_format($district['monthly_income']) . '/month salary adds KES ' . number_format($inv) . '/month to your wealth.';
            }
        }

        if ($slug === 'champions-court' && Schema::hasTable('dreams')) {
            $district['dreams_count']      = \App\Models\Dream::active()->count();
            $district['owned_dreams']      = \App\Models\PlayerDream::where('user_id', $user->id)->count();
            $district['featured_dream']    = \App\Models\Dream::active()->orderBy('sort_order')->first();
            $district['open_challenges']   = \App\Models\Challenge::where('mode', 'broadcast')->where('status', 'active')->where('scope', 'open')->count();
            $district['my_active_challenges'] = \App\Models\ChallengeParticipant::where('user_id', $user->id)
                ->where('status', 'accepted')
                ->whereHas('challenge', fn ($q) => $q->where('status', 'active'))
                ->count();
            $district['pending_invites']   = \App\Models\ChallengeParticipant::where('user_id', $user->id)->where('status', 'invited')->count();

            // Available-to-join list, right in the popup — same eligibility shape
            // as ChallengeController::index()'s "Open Challenges" section.
            $joinedIds = \App\Models\ChallengeParticipant::where('user_id', $user->id)->pluck('challenge_id')->all();
            $district['open_challenges_list'] = \App\Models\Challenge::where('mode', 'broadcast')
                ->where('status', 'active')
                ->where('scope', 'open')
                ->where('is_chama_battle', false)
                ->whereNotIn('id', $joinedIds)
                ->withCount('participants')
                ->with('template')
                ->orderByDesc('is_official')
                ->orderBy('ends_at')
                ->limit(6)
                ->get()
                ->map(fn ($c) => [
                    'id'       => $c->id,
                    'icon'     => $c->template?->icon ?? '🏆',
                    'title'    => ($c->is_official ? '🏙️ ' : '') . $c->title,
                    'subtitle' => "{$c->participants_count} joined · ends {$c->ends_at->diffForHumans()}"
                        . ($c->stake_amount ? ' · KES ' . number_format($c->stake_amount) . ' entry' : ''),
                ]);

            // The player's OWN challenges — friends duels/FFA and anything
            // they've already joined, public or private — so the popup always
            // shows everything they have access to, not just what's open to
            // join. Same "pending + active shown together" shape as
            // ChallengeController::index()'s "My Challenges" list.
            $district['my_challenges_list'] = \App\Models\ChallengeParticipant::where('user_id', $user->id)
                ->where('status', 'accepted')
                ->whereHas('challenge', fn ($q) => $q->whereIn('status', ['pending', 'active']))
                ->with('challenge.template')
                ->get()
                ->sortBy(fn ($p) => $p->challenge->status === 'active' ? 0 : 1)
                ->values()
                ->map(fn ($p) => [
                    'id'       => $p->challenge->id,
                    'icon'     => $p->challenge->template?->icon ?? '⚔️',
                    'title'    => $p->challenge->title,
                    'live'     => $p->challenge->status === 'active',
                    'subtitle' => $p->challenge->status === 'active'
                        ? 'Live · ends ' . $p->challenge->ends_at->diffForHumans()
                        : 'Waiting for the other side to accept',
                ]);
        }

        return response()->json($district);
    }

    // ── Mission helpers ───────────────────────────────────────────────────────

    /**
     * Enrich Car Yard / Estates listings with real financing numbers.
     * Prices come from the actual marketplace asset; deposit + monthly
     * installment come from AssetFinancingService so what the player sees
     * is exactly what they'll pay. can_afford = can cover the DEPOSIT.
     */
    private function financedListings(array $listings, int $balance): array
    {
        $financing = app(\App\Services\AssetFinancingService::class);

        foreach ($listings as &$item) {
            $asset = \App\Models\Asset::find($item['asset_id']);
            $quote = $asset ? $financing->quote($asset) : null;

            if ($asset && $quote) {
                $item['price']       = (int) $asset->base_price;
                $item['deposit']     = $quote['deposit'];
                $item['monthly']     = $quote['monthly'];
                $item['months']      = $quote['months'];
                $item['total_cost'] = $quote['total_cost'];
            } else {
                // Asset missing (unseeded DB) — approximate with the same terms
                $pct              = isset($item['gig_income']) ? 0.20 : 0.10;
                $item['deposit']  = (int) ceil($item['price'] * $pct);
                $item['monthly']  = (int) ceil(($item['price'] - $item['deposit']) / 24 * 1.08);
                $item['months']   = isset($item['gig_income']) ? 24 : 36;
                $item['total_cost'] = $item['deposit'] + $item['monthly'] * $item['months'];
            }

            $item['can_afford'] = $balance >= $item['deposit'];
        }
        unset($item);

        return $listings;
    }

    private function bootFirstMission($user): void
    {
        if (PlayerMission::where('user_id', $user->id)->exists()) return;

        // Only boot a mission whose required assets actually exist in the gameset
        $first = Mission::active()
            ->orderBy('sequence_order')
            ->get()
            ->first(fn($m) => $this->missionAssetsExist($m));

        if (!$first) return;

        PlayerMission::create([
            'user_id'      => $user->id,
            'mission_id'   => $first->id,
            'status'       => 'active',
            'activated_at' => now(),
        ]);
    }

    private function getActiveMission($user): ?array
    {
        $pm = PlayerMission::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('mission')
            ->first();

        if (!$pm) return null;
        if (!$this->missionAssetsExist($pm->mission)) return null;

        $m = $pm->mission;
        return [
            'id'            => $m->id,
            'title'         => $m->title,
            'description'   => $m->description,
            'icon'          => $m->icon,
            'district_slug' => $m->district_slug,
            'sequence'      => $m->sequence_order,
        ];
    }

    /**
     * Returns true if the mission has no asset-category requirement,
     * or if at least one active asset matches the required category.
     * Maps legacy category names (e.g. "devices") to current gameset names.
     */
    private function missionAssetsExist(?\App\Models\Mission $mission): bool
    {
        if (!$mission) return false;

        $req = is_array($mission->requirements) ? $mission->requirements : (array) json_decode((string) $mission->requirements, true);
        if (empty($req) || ($req['type'] ?? '') !== 'asset_category') return true;

        $categoryMap = ['devices' => 'gadget', 'phone' => 'gadget', 'phones' => 'gadget', 'stocks' => 'investment'];
        $cat = $req['value'] ?? '';
        $mapped = $categoryMap[$cat] ?? $cat;

        return \App\Models\Asset::where('is_active', true)
            ->where(fn($q) => $q->where('category', $mapped)->orWhere('category', $cat))
            ->exists();
    }

    private function getMissionDistrictSlugs($user): array
    {
        $pm = PlayerMission::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('mission:id,district_slug')
            ->first();

        return $pm ? [$pm->mission->district_slug] : [];
    }

    // ── Phase 15: Quest System (in-map panel, auto-completion) ───────

    /**
     * GET /world/quests
     * Returns all quests for the player, decorated with level-gate and trigger info.
     * sort_order doubles as the minimum player level required.
     */
    public function quests()
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $level    = $progress->level ?? 1;
        $ageGroup = $user->age_group ?? '18-25';

        if (!Schema::hasTable('quests')) {
            return response()->json([]);
        }

        $levelCol   = Schema::hasColumn('quests', 'level_required') ? 'level_required' : 'sort_order';
        $careerField = $progress->career_field ?? null;

        $quests = Quest::where('is_active', true)
            ->where(fn($q) => $q->where('age_group', $ageGroup)->orWhere('age_group', 'all')->orWhereNull('age_group'))
            ->when(Schema::hasColumn('quests', 'career_fields'), fn ($q) => $q->forCareerField($careerField))
            ->orderBy($levelCol)
            ->orderBy('sort_order')
            ->get();

        $completedIds  = UserQuest::where('user_id', $user->id)->whereNotNull('completed_at')->pluck('quest_id')->toArray();
        $inProgressIds = UserQuest::where('user_id', $user->id)->whereNull('completed_at')->whereNotNull('submitted_at')->pluck('quest_id')->toArray();
        $fieldMeta     = \App\Services\CareerService::fieldsByKey();

        return response()->json(
            $quests->map(function (Quest $q) use ($level, $completedIds, $inProgressIds, $fieldMeta) {
                $minLevel = max(1, (int) ($q->level_required ?? $q->sort_order ?? 1));
                $isLocked = $level < $minLevel;
                // Quests below the player's current level were never excluded from
                // this list (only ones ABOVE it get $isLocked) — this just flags
                // that fact explicitly so the quest board can surface a dedicated
                // "from earlier levels" filter instead of leaving them buried,
                // unlabeled, in the full list.
                $isPreviousLevel = !$isLocked && $minLevel < $level;

                $status = match(true) {
                    in_array($q->id, $completedIds)  => 'completed',
                    in_array($q->id, $inProgressIds) => 'in_progress',
                    default                           => 'available',
                };

                // Parse optional [TRIGGER:action_type] from instructions
                $instructions = $q->instructions ?? '';
                $trigger      = null;
                if (preg_match('/\[TRIGGER:([^\]]+)\]/', $instructions, $m)) {
                    $trigger      = trim($m[1]);
                    $instructions = trim(preg_replace('/\[TRIGGER:[^\]]+\]\s*/', '', $instructions));
                }

                // XP scales with level: base_xp * (1 + (level-1) * 0.1)
                $scaledXp = (int) ($q->xp_reward * (1 + ($level - 1) * 0.1));

                $careerBadge = null;
                if (!empty($q->career_fields)) {
                    $careerBadge = collect($q->career_fields)
                        ->map(fn ($k) => $fieldMeta[$k]['icon'] ?? '💼')
                        ->implode(' ');
                }

                return [
                    'id'            => $q->id,
                    'title'         => $q->title,
                    'description'   => $q->description,
                    'instructions'  => $instructions,
                    'lesson'        => $q->lesson ?? '',
                    'icon'          => $q->icon,
                    'xp_reward'     => $scaledXp,
                    'kes_reward'    => $q->kes_reward ?? 0,
                    'min_level'     => $minLevel,
                    'is_locked'     => $isLocked,
                    'is_previous_level' => $isPreviousLevel,
                    'user_status'   => $status,
                    'trigger_type'  => $q->trigger_type,
                    'trigger_value' => $q->trigger_value,
                    'trigger_label' => $q->trigger_label,
                    'career_badge'  => $careerBadge,
                ];
            })
        );
    }

    /**
     * POST /world/quests/{quest}/start
     * Marks a quest as "in progress" (submitted_at set, completed_at null).
     */
    public function startQuest(Quest $quest)
    {
        if (!Schema::hasTable('user_quests')) {
            return response()->json(['error' => 'Not available yet.'], 503);
        }
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $level    = $progress->level ?? 1;
        $levelCol = Schema::hasColumn('quests', 'level_required') ? 'level_required' : 'sort_order';
        $minLevel = max(1, (int) $quest->$levelCol);

        if ($level < $minLevel) {
            return response()->json(['error' => 'Reach level ' . $minLevel . ' to unlock this quest.'], 403);
        }

        if (Schema::hasColumn('quests', 'career_fields') && !$quest->matchesCareerField($progress->career_field)) {
            return response()->json(['error' => 'This quest is for a different career path.'], 403);
        }

        // Admin-tunable daily quest cap — global pace value, with an optional
        // tighter free-tier override (PlanGate::maxQuestsPerDay). 0 = unlimited.
        // Re-opening a quest already started today doesn't count against it.
        $alreadyStarted = UserQuest::where('user_id', $user->id)->where('quest_id', $quest->id)->exists();
        $dailyCap       = app(\App\Services\PlanGate::class)->maxQuestsPerDay($user);
        if (!$alreadyStarted && $dailyCap > 0) {
            $startedToday = UserQuest::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();
            if ($startedToday >= $dailyCap) {
                return response()->json(['error' => "You've taken on {$dailyCap} quest(s) today — the daily maximum. Come back tomorrow with fresh energy!"], 429);
            }
        }

        $uq = UserQuest::firstOrCreate(
            ['user_id' => $user->id, 'quest_id' => $quest->id],
            ['submitted_at' => now()]
        );

        if (!$uq->submitted_at) {
            $uq->submitted_at = now();
            $uq->save();
        }

        // Full quest brief so the frontend can show the "how to complete" popup
        return response()->json([
            'started'      => true,
            'quest_id'     => $quest->id,
            'title'        => $quest->title,
            'icon'         => $quest->icon ?? '📜',
            'description'  => $quest->description ?? '',
            'instructions' => preg_replace('/\[TRIGGER:[^\]]+\]\s*/', '', $quest->instructions ?? ''),
            'lesson'       => $quest->lesson ?? '',
            'trigger_label'=> $quest->trigger_label ?? null,
            'xp_reward'    => (int) $quest->xp_reward,
            'kes_reward'   => (int) ($quest->kes_reward ?? 0),
        ]);
    }

    /**
     * POST /world/quests/{quest}/complete
     * Auto-completes a quest (no admin approval needed). Awards scaled XP.
     */
    public function completeQuest(Quest $quest)
    {
        if (!Schema::hasTable('user_quests')) {
            return response()->json(['error' => 'Not available yet.'], 503);
        }
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();
        $level    = $progress->level ?? 1;
        $levelCol = Schema::hasColumn('quests', 'level_required') ? 'level_required' : 'sort_order';
        $minLevel = max(1, (int) $quest->$levelCol);

        if ($level < $minLevel) {
            return response()->json(['error' => 'Quest locked.'], 403);
        }

        $uq = UserQuest::firstOrCreate(
            ['user_id' => $user->id, 'quest_id' => $quest->id],
            ['submitted_at' => now()]
        );

        if ($uq->completed_at) {
            return response()->json(['error' => 'Already completed.'], 409);
        }

        if (!$uq->submitted_at) {
            $uq->submitted_at = now();
        }
        $uq->completed_at = now();
        $uq->save();

        // High mood (> 80) boosts quest XP by 10%
        $moodBonus = (($progress->mood ?? 70) > 80);
        $scaledXp  = (int) ($quest->xp_reward * (1 + ($level - 1) * 0.1) * ($moodBonus ? 1.1 : 1.0));
        $kesReward = (int) ($quest->kes_reward ?? 0);
        $progress->points_total = ($progress->points_total ?? 0) + $scaledXp;
        $progress->level = $progress->calculateLevel();
        if ($kesReward > 0) {
            $progress->balance = ($progress->balance ?? 0) + $kesReward;
        }
        $progress->save();

        return response()->json([
            'completed'   => true,
            'xp_earned'   => $scaledXp,
            'kes_earned'  => $kesReward,
            'mood_bonus'  => $moodBonus,
            'lesson'      => $quest->lesson ?? $quest->description ?? '',
            'title'       => $quest->title,
            'icon'        => $quest->icon,
        ]);
    }

    /**
     * POST /world/quests/check-action
     * Called when the player completes a game action (e.g. savings_created, phone_purchased).
     * Auto-completes any in-progress quests whose instructions contain [TRIGGER:action].
     * Stores completions in session so they appear as celebrations on next world visit.
     */
    public function checkQuestAction(Request $request)
    {
        $triggerType  = $request->input('trigger_type') ?? $request->input('action');
        $triggerValue = $request->input('trigger_value');
        $context      = $request->input('context', []);

        if (!$triggerType) return response()->json([]);

        if ($triggerValue !== null) {
            $context['slug']     = $triggerValue;
            $context['category'] = $triggerValue;
            $context['amount']   = $triggerValue;
        }

        $completed = app(QuestTriggerService::class)->fire(auth()->user(), $triggerType, $context);

        return response()->json($completed);
    }

    // ── Smart Reminders — seeds daily notifications into bell ──────

    private function _seedSmartReminders($user, $progress): void
    {
        if (!Schema::hasTable('game_notifications')) return;

        $today = now()->toDateString();

        // Gate: only run once per day per user
        $hasToday = \DB::table('game_notifications')
            ->where('user_id', $user->id)
            ->where('type', 'smart_reminder')
            ->whereDate('created_at', $today)
            ->exists();
        if ($hasToday) return;

        $reminders = [];

        // Quest activity reminder
        if (Schema::hasTable('user_quests')) {
            $inProgressCount = UserQuest::where('user_id', $user->id)
                ->whereNull('completed_at')
                ->whereNotNull('submitted_at')
                ->count();
            if ($inProgressCount > 0) {
                $reminders[] = [
                    'user_id'    => $user->id,
                    'type'       => 'smart_reminder',
                    'title'      => $inProgressCount . ' quest' . ($inProgressCount > 1 ? 's' : '') . ' in progress!',
                    'body'       => 'Head to the Quest Board and mark your progress to earn XP.',
                    'icon'       => '📜',
                    'data'       => json_encode(['url' => '/world']),
                    'is_read'    => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Low balance warning
        $balance = $progress?->balance ?? 0;
        if ($balance < 5000) {
            $reminders[] = [
                'user_id'    => $user->id,
                'type'       => 'smart_reminder',
                'title'      => 'Low balance — KES ' . number_format($balance),
                'body'       => 'Your balance is running low. Visit the Opportunity Hub to pick up a course or apply for a job.',
                'icon'       => '⚠️',
                'data'       => json_encode(['url' => '/opportunities']),
                'is_read'    => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Daily encouragement
        $reminders[] = [
            'user_id'    => $user->id,
            'type'       => 'smart_reminder',
            'title'      => 'Daily bonus received!',
            'body'       => 'Keep exploring Pesa City. Every day you play, you\'re building real financial skills.',
            'icon'       => '🌟',
            'data'       => json_encode([]),
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (!empty($reminders)) {
            \DB::table('game_notifications')->insert($reminders);
        }
    }

    // ── Pending quest completions poll endpoint ──────────────────────

    /**
     * GET /world/quests/pending-completions
     * Returns any quest completions waiting in session — used by world.js to surface
     * celebration popups immediately when a trigger fires while the world page is open.
     */
    public function pendingCompletions()
    {
        $completions = session()->pull('pending_quest_completions', []);
        $stepFires   = session()->pull('pending_step_fires', []);

        return response()->json([
            'completions' => array_values($completions),
            'step_fires'  => array_values($stepFires),
        ]);
    }

    /**
     * GET /world/challenges/pending-results
     * Unread challenge_result/challenge_cancelled notifications, for a
     * celebration popup. Unlike pending_quest_completions (a session flash
     * queue), this reads from GameNotification — settlement can happen while
     * the player is offline (the nightly game:settle-challenges sweep, or
     * another participant's page load triggering an early duel win), so a
     * session-scoped queue would miss it entirely.
     */
    public function pendingChallengeResults()
    {
        $notifications = \App\Models\GameNotification::where('user_id', auth()->id())
            ->whereIn('type', ['challenge_result', 'challenge_cancelled'])
            ->where('is_read', false)
            ->latest()
            ->limit(5)
            ->get();

        \App\Models\GameNotification::whereIn('id', $notifications->pluck('id'))->update(['is_read' => true]);

        return response()->json($notifications->map(fn ($n) => [
            'title'     => $n->title,
            'body'      => $n->body,
            'icon'      => $n->icon,
            'is_winner' => (bool) ($n->data['is_winner'] ?? false),
        ])->values());
    }

    // ── Fun World spend endpoint ────────────────────────────────────

    /**
     * POST /world/fun-world/spend
     * Deducts balance for a Fun World experience, boosts mood, awards small XP.
     */
    public function funWorldSpend(Request $request)
    {
        $user     = auth()->user();
        $progress = $user->getOrCreateProgress();

        // Activities are admin-managed in the DB — the request must reference a real one
        $activity = null;
        if (Schema::hasTable('fun_world_activities')) {
            $activityId = (int) $request->input('activity_id', 0);
            $activity   = $activityId
                ? \App\Models\FunWorldActivity::active()->find($activityId)
                : \App\Models\FunWorldActivity::active()->where('name', (string) $request->input('name', ''))->first();

            if (!$activity && \App\Models\FunWorldActivity::active()->exists()) {
                return response()->json(['error' => 'Unknown activity — refresh and try again.'], 422);
            }
        }

        $price = $activity ? (int) $activity->price : (int) $request->input('price', 0);
        $name  = $activity ? $activity->name : (string) $request->input('name', 'Experience');
        $icon  = $activity ? $activity->icon : (string) $request->input('icon', '🎉');

        if ($price <= 0) {
            return response()->json(['error' => 'Invalid experience price.'], 422);
        }

        // Plan gate: free accounts get a few Fun World treats per game month
        $gate     = app(\App\Services\PlanGate::class);
        $funLimit = $gate->limit($user, 'fun_per_game_month');
        if ($funLimit > 0) {
            $tick           = $progress->tick_count ?? 0;
            $clock          = app(\App\Services\GameClock::class);
            $monthStartAt   = now()->subSeconds($clock->realSecondsForTicks($tick % 30));
            $funThisMonth   = \App\Models\GameNotification::where('user_id', $user->id)
                ->where('type', 'fun_world')
                ->where('created_at', '>=', $monthStartAt)
                ->count();
            if ($funThisMonth >= $funLimit) {
                return response()->json($gate->deny('fun_per_game_month', $funLimit), 422);
            }
        }

        if ($progress->balance < $price) {
            $shortfall = number_format($price - $progress->balance);
            return response()->json(['error' => "Need KES {$shortfall} more to enjoy this."], 422);
        }

        $progress->balance -= $price;

        // Boost mood (cap at 100), award XP for spending wisely
        $moodBoost    = $activity ? $activity->moodBoost() : min(25, max(5, (int) ($price / 200)));
        $progress->mood = min(100, ($progress->mood ?? 70) + $moodBoost);
        $progress->mood_last_boosted_at = now();

        // Small XP reward for fun (30% of 50/30/20 lesson)
        $xpReward = $activity && $activity->xp_reward > 0 ? (int) $activity->xp_reward : max(10, (int) ($price / 50));
        $progress->points_total = ($progress->points_total ?? 0) + $xpReward;
        $progress->level = $progress->calculateLevel();
        $progress->save();

        \App\Models\GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'fun_world',
            'title'   => "{$icon} {$name} — Enjoyed!",
            'body'    => "You spent KES " . number_format($price) . " on fun. Mood +{$moodBoost}! +{$xpReward} XP for budgeting wisely.",
            'icon'    => $icon,
            'data'    => ['price' => $price, 'mood_boost' => $moodBoost],
        ]);

        app(\App\Services\QuestTriggerService::class)->fire($user, 'fun_world_spend', ['amount' => $price]);

        return response()->json([
            'success'     => true,
            'name'        => $name,
            'icon'        => $icon,
            'new_balance' => $progress->balance,
            'new_mood'    => $progress->mood,
            'xp_earned'   => $xpReward,
            'mood_boost'  => $moodBoost,
            'mood_bonus_active'   => $progress->mood > 80,
            'mood_penalty_active' => $progress->mood < 40,
        ]);
    }

    // ── Phase 8: Event Engine resolve endpoint ─────────────────────

    /**
     * POST /world/events/resolve
     * Accepts a player's choice on a world event, applies the balance delta,
     * logs the event (if player_event_log exists), and returns the result.
     */
    public function resolveEvent(Request $request)
    {
        $user = auth()->user();

        $eventId  = $request->integer('event_id');
        $choiceId = $request->integer('choice_id');

        // Scenarios are Node records (type='scenario') — guard if nodes table missing
        if (!Schema::hasTable('nodes')) {
            return response()->json(['error' => 'Game nodes not yet migrated.'], 503);
        }

        $node = Node::with('choices')->find($eventId);
        if (!$node) {
            return response()->json(['error' => 'Event not found.'], 404);
        }

        // Find the chosen option by Choice record ID
        $choice = $node->choices->firstWhere('id', $choiceId);

        if (!$choice) {
            return response()->json(['error' => 'Choice not found.'], 422);
        }

        $delta      = $choice->balance_change;
        $isPositive = $delta >= 0;

        // Apply delta to player balance (user_progress table)
        if (Schema::hasTable('user_progress')) {
            \DB::table('user_progress')
                ->where('user_id', $user->id)
                ->increment('balance', $delta);
        }

        // Log event to prevent repeats within 14 game days
        if (Schema::hasTable('player_event_log')) {
            \DB::table('player_event_log')->updateOrInsert(
                ['player_id' => $user->id, 'node_id' => $eventId],
                ['seen_at' => now(), 'choice_id' => $choiceId, 'delta' => $delta]
            );
        }

        return response()->json([
            'result_icon'  => $choice->effect_data['result_icon']  ?? ($isPositive ? '💵' : '😬'),
            'result_title' => $choice->effect_data['result_title'] ?? ($isPositive ? 'Nice one!' : 'Tough break.'),
            'delta'        => $delta,
        ]);
    }
}
