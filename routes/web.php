<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AutoLoginVerifyController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\WorldController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RealLifeToolsController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameSetController;
use App\Http\Controllers\GamesetBillController;
use App\Http\Controllers\GamesetLifeEventController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LifeController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// ── Public migration runner (no auth required — secured by ARTISAN_SECRET in .env) ──
Route::get('/run/{secret}', function (string $secret) {
    if (!$secret || $secret !== env('ARTISAN_SECRET')) {
        abort(403, 'Invalid secret.');
    }

    $results = [];
    foreach (['migrate', 'config:clear', 'view:clear', 'cache:clear'] as $cmd) {
        $params = $cmd === 'migrate' ? ['--force' => true] : [];
        try {
            Artisan::call($cmd, $params);
            $results[$cmd] = ['ok' => true, 'output' => trim(Artisan::output()) ?: '(no output)'];
        } catch (\Throwable $e) {
            $results[$cmd] = ['ok' => false, 'output' => $e->getMessage()];
        }
    }

    $html = '<pre style="font-family:monospace;background:#111;color:#0f0;padding:2rem;min-height:100vh;">';
    $html .= "PesaQuest Artisan Runner\n";
    $html .= str_repeat('─', 40) . "\n\n";
    foreach ($results as $cmd => $r) {
        $icon = $r['ok'] ? '✓' : '✗';
        $html .= "{$icon} php artisan {$cmd}\n";
        $html .= htmlspecialchars($r['output']) . "\n\n";
    }
    $html .= str_repeat('─', 40) . "\n";
    $html .= 'Done at ' . now()->toDateTimeString();
    $html .= '</pre>';
    return $html;
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Pricing page (public)
Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');

// Sitemap
Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'),          'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => url('/pricing'),   'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => url('/register'),  'priority' => '0.7', 'changefreq' => 'yearly'],
        ['loc' => url('/login'),     'priority' => '0.5', 'changefreq' => 'yearly'],
    ];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $xml .= "  <url>\n    <loc>{$u['loc']}</loc>\n    <changefreq>{$u['changefreq']}</changefreq>\n    <priority>{$u['priority']}</priority>\n  </url>\n";
    }
    $xml .= '</urlset>';
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

// Email verification auto-login — public, signed URL, no auth required
Route::get('/email/auto-verify/{id}/{hash}', AutoLoginVerifyController::class)
    ->name('verification.auto-login')
    ->middleware(['throttle:6,1']);

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/onboarding/complete', [DashboardController::class, 'completeOnboarding'])
    ->middleware(['auth', 'verified'])->name('onboarding.complete');
Route::post('/onboarding/replay', [DashboardController::class, 'replayOnboarding'])
    ->middleware(['auth', 'verified'])->name('onboarding.replay');

// Real-life money tools (premium) — bill reminders & savings goals, external to the game
Route::middleware(['auth', 'verified'])->prefix('real-life')->name('real-life.')->group(function () {
    Route::get('/',                         [RealLifeToolsController::class, 'index'])->name('index');
    Route::post('/bills',                   [RealLifeToolsController::class, 'storeBill'])->name('bills.store');
    Route::put('/bills/{bill}',             [RealLifeToolsController::class, 'updateBill'])->name('bills.update');
    Route::delete('/bills/{bill}',          [RealLifeToolsController::class, 'destroyBill'])->name('bills.destroy');
    Route::post('/bills/{bill}/mark-paid',  [RealLifeToolsController::class, 'markBillPaid'])->name('bills.mark-paid');
    Route::post('/goals',                   [RealLifeToolsController::class, 'storeGoal'])->name('goals.store');
    Route::put('/goals/{goal}',             [RealLifeToolsController::class, 'updateGoal'])->name('goals.update');
    Route::delete('/goals/{goal}',          [RealLifeToolsController::class, 'destroyGoal'])->name('goals.destroy');
    Route::post('/goals/{goal}/deposits',   [RealLifeToolsController::class, 'storeDeposit'])->name('goals.deposits.store');
    Route::delete('/deposits/{deposit}',    [RealLifeToolsController::class, 'destroyDeposit'])->name('deposits.destroy');
    Route::get('/expenses',                 [RealLifeToolsController::class, 'expenses'])->name('expenses.index');
    Route::post('/expenses',                [RealLifeToolsController::class, 'storeExpense'])->name('expenses.store');
    Route::delete('/expenses/{expense}',    [RealLifeToolsController::class, 'destroyExpense'])->name('expenses.destroy');
    Route::get('/report',                   [RealLifeToolsController::class, 'report'])->name('report');
    Route::post('/budget-ratio',            [RealLifeToolsController::class, 'saveBudgetRatio'])->name('budget-ratio.save');
    Route::post('/categories',              [RealLifeToolsController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}',    [RealLifeToolsController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [RealLifeToolsController::class, 'destroyCategory'])->name('categories.destroy');
});

// Pesa City world map
Route::middleware(['auth'])->group(function () {
    Route::get('/world', [WorldController::class, 'index'])->name('world');
    Route::get('/world/district/{slug}', [WorldController::class, 'district'])->name('world.district');
    Route::post('/world/events/resolve', [WorldController::class, 'resolveEvent'])->name('world.events.resolve');
    Route::post('/world/fun-world/spend', [WorldController::class, 'funWorldSpend'])->name('world.fun-world.spend');
    Route::get('/world/quests/pending-completions', [WorldController::class, 'pendingCompletions'])->name('world.quests.pending');
    Route::get('/world/challenges/pending-results', [WorldController::class, 'pendingChallengeResults'])->name('world.challenges.pending-results');

    // Game calendar HUD (date chip + week strip) — derived events, no writes
    Route::get('/game/calendar', function () {
        return response()->json(app(\App\Services\GameCalendarService::class)->upcoming(auth()->user()));
    })->name('game.calendar');

    // Mission engine
    Route::get('/missions/active', [MissionController::class, 'active'])->name('missions.active');
    Route::post('/missions/{id}/check', [MissionController::class, 'check'])->name('missions.check');

    // Quest panel API (Phase 15 — in-map quests, auto-completion)
    Route::get('/world/quests',                        [WorldController::class, 'quests'])->name('world.quests');
    Route::post('/world/quests/check-action',          [WorldController::class, 'checkQuestAction'])->name('world.quests.check-action');
    Route::post('/world/quests/{quest}/start',         [WorldController::class, 'startQuest'])->name('world.quests.start');
    Route::post('/world/quests/{quest}/complete',      [WorldController::class, 'completeQuest'])->name('world.quests.complete');

    // Opportunity Hub
    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/courses', [OpportunityController::class, 'courses'])->name('opportunities.courses');
    Route::post('/opportunities/courses/{id}/enroll', [OpportunityController::class, 'enroll'])->name('opportunities.enroll');
    Route::post('/opportunities/courses/{id}/complete', [OpportunityController::class, 'complete'])->name('opportunities.complete');
    Route::get('/opportunities/jobs', [OpportunityController::class, 'jobs'])->name('opportunities.jobs');
    Route::post('/opportunities/jobs/{id}/apply', [OpportunityController::class, 'apply'])->name('opportunities.apply');
    Route::post('/opportunities/jobs/{id}/resign', [OpportunityController::class, 'resign'])->name('opportunities.resign');
});

// Game routes
Route::middleware(['auth'])->prefix('game')->name('game.')->group(function () {
    Route::get('/',                                fn() => redirect()->route('world'))->name('play');
    Route::post('/choose',                         [GameController::class, 'choose'])->name('choose');
    Route::get('/result',                          [GameController::class, 'result'])->name('result');
    Route::post('/restart',                        [GameController::class, 'restart'])->name('restart');
    Route::get('/notifications',                   [GameController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/read',             [GameController::class, 'markNotificationsRead'])->name('notifications.read');
    Route::post('/next-scenario',                  [GameController::class, 'nextScenario'])->name('next-scenario');
    Route::post('/claim-bonus',                    [GameController::class, 'claimDailyBonus'])->name('claim-bonus');
    Route::post('/investments/{investment}/claim', [GameController::class, 'claimInvestment'])->name('investments.claim');
    Route::get('/leaderboard',                     [GameController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/leaderboard/players/{user}/details', [GameController::class, 'leaderboardPlayerDetails'])->name('leaderboard.player-details');
    Route::post('/rate',                           [GameController::class, 'rateScenario'])->name('rate');
    Route::post('/personality',                    [GameController::class, 'savePersonality'])->name('personality');
    Route::post('/replay',                         [GameController::class, 'replayScenario'])->name('replay');
    Route::get('/diary',                           [GameController::class, 'diary'])->name('diary');
    Route::post('/quests/{quest}/submit',          [GameController::class, 'submitQuest'])->name('quests.submit');
    Route::get('/quests',                          [GameController::class, 'questBoard'])->name('quests.index');
    Route::get('/roadmap',                         fn() => view('game.roadmap'))->name('roadmap');
});

// User-facing subscription
Route::middleware(['auth'])->prefix('subscribe')->name('subscribe.')->group(function () {
    Route::get('/',                    [SubscriptionController::class, 'index'])->name('index');
    Route::post('/{plan}/pay',         [SubscriptionController::class, 'pay'])->name('pay');
    Route::post('/{plan}/coupon-check',[SubscriptionController::class, 'couponCheck'])->name('coupon-check');
    Route::get('/status',              [SubscriptionController::class, 'status'])->name('status');
});

// M-Pesa Daraja callback — CSRF-exempt, Safaricom hits this directly
Route::post('/mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// School portal — public, token-based (legacy — being superseded by the
// authenticated teacher portal below, kept working during the transition)
Route::get('/school/{token}', [SchoolController::class, 'portal'])->name('school.portal');
Route::post('/school/{token}/members', [SchoolController::class, 'addMember'])->name('school.members.add');
Route::post('/school/{token}/challenges', [SchoolController::class, 'postChallenge'])->name('school.challenges.post');
Route::delete('/school/{token}/members/{member}', [SchoolController::class, 'removeMember'])->name('school.members.remove');

// School Teacher Portal — real login-gated accounts, multi-teacher
Route::get('/school-invite/{token}', [\App\Http\Controllers\SchoolTeacherController::class, 'showInvite'])->name('school.teacher.invite');
Route::middleware('auth')->post('/school-invite/{token}/accept', [\App\Http\Controllers\SchoolTeacherController::class, 'acceptInvite'])->name('school.teacher.invite.accept');

Route::middleware(['auth', 'school.teacher'])->prefix('school/{school}/teacher')->name('school.teacher.')->group(function () {
    Route::get('/',                            [\App\Http\Controllers\SchoolTeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/students/{member}',           [\App\Http\Controllers\SchoolTeacherController::class, 'student'])->name('student');
    Route::post('/students',                   [\App\Http\Controllers\SchoolTeacherController::class, 'addStudent'])->name('students.add');
    Route::delete('/students/{member}',        [\App\Http\Controllers\SchoolTeacherController::class, 'removeStudent'])->name('students.remove');
    Route::post('/teachers/invite',            [\App\Http\Controllers\SchoolTeacherController::class, 'inviteTeacher'])->name('teachers.invite');
    Route::delete('/teachers/{teacher}',       [\App\Http\Controllers\SchoolTeacherController::class, 'removeTeacher'])->name('teachers.remove');
    Route::get('/teachers/{teacher}/profile',  [\App\Http\Controllers\SchoolTeacherController::class, 'teacherProfile'])->name('teachers.profile');
    Route::post('/class-challenge',            [\App\Http\Controllers\SchoolTeacherController::class, 'createClassChallenge'])->name('challenge.create');
    Route::post('/classes',                    [\App\Http\Controllers\SchoolTeacherController::class, 'storeClass'])->name('classes.store');
    Route::put('/classes/{class}',             [\App\Http\Controllers\SchoolTeacherController::class, 'updateClass'])->name('classes.update');
    Route::delete('/classes/{class}',          [\App\Http\Controllers\SchoolTeacherController::class, 'destroyClass'])->name('classes.destroy');
    Route::post('/students/{member}/class',    [\App\Http\Controllers\SchoolTeacherController::class, 'assignStudentClass'])->name('students.assign-class');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile',           [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',         [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',  [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile',        [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Player search & public profiles
Route::middleware('auth')->group(function () {
    Route::get('/players',        [ProfileController::class, 'search'])->name('players.search');
    Route::get('/players/{user}', [ProfileController::class, 'show'])->name('players.show');
});

// Chama invite landing — public so guests can see the invite before logging in
Route::get('/chama/invite/{token}', [\App\Http\Controllers\ChamaController::class, 'showInvite'])->name('chama.invite.show');

// Chama — cooperative investment groups
Route::middleware(['auth'])->prefix('chama')->name('chama.')->group(function () {
    Route::get('/',                                  [\App\Http\Controllers\ChamaController::class, 'index'])->name('index');
    Route::get('/create',                            [\App\Http\Controllers\ChamaController::class, 'create'])->name('create');
    Route::post('/',                                 [\App\Http\Controllers\ChamaController::class, 'store'])->name('store');
    Route::post('/proposals/{proposal}/vote',        [\App\Http\Controllers\ChamaController::class, 'vote'])->name('vote');
    Route::post('/proposals/{proposal}/execute',     [\App\Http\Controllers\ChamaController::class, 'executeProposal'])->name('execute');
    Route::post('/invite/{token}/join',              [\App\Http\Controllers\ChamaController::class, 'acceptInvite'])->name('invite.accept');
    Route::post('/join-code',                        [\App\Http\Controllers\ChamaController::class, 'joinByCode'])->name('join-code');
    Route::post('/{chama}/invite-link',              [\App\Http\Controllers\ChamaController::class, 'generateInvite'])->name('invite.generate');
    Route::post('/{chama}/invite-friend',            [\App\Http\Controllers\ChamaController::class, 'inviteFriend'])->name('invite.friend');
    Route::get('/{chama}',                           [\App\Http\Controllers\ChamaController::class, 'show'])->name('show');
    Route::post('/{chama}/join',                     [\App\Http\Controllers\ChamaController::class, 'join'])->name('join');
    Route::post('/{chama}/leave',                    [\App\Http\Controllers\ChamaController::class, 'leave'])->name('leave');
    Route::post('/{chama}/contribute',               [\App\Http\Controllers\ChamaController::class, 'contribute'])->name('contribute');
    Route::post('/{chama}/propose',                  [\App\Http\Controllers\ChamaController::class, 'propose'])->name('propose');
    Route::post('/{chama}/distribute',               [\App\Http\Controllers\ChamaController::class, 'distribute'])->name('distribute');
    Route::post('/{chama}/challenge',                [\App\Http\Controllers\ChamaController::class, 'createChallenge'])->name('challenge.create');

    // Chama loans, withdrawals & dividends
    Route::post('/{chama}/loans/request',            [\App\Http\Controllers\ChamaController::class, 'requestLoan'])->name('loans.request');
    Route::post('/loans/{loan}/repay',               [\App\Http\Controllers\ChamaController::class, 'repayChamaLoan'])->name('loans.repay');
    Route::post('/{chama}/withdraw',                 [\App\Http\Controllers\ChamaController::class, 'withdraw'])->name('withdraw');
    Route::post('/{chama}/dividend/declare',         [\App\Http\Controllers\ChamaController::class, 'declareDividend'])->name('dividend.declare');
    Route::post('/dividends/{dividend}/choose',      [\App\Http\Controllers\ChamaController::class, 'chooseDividend'])->name('dividend.choose');
});

// Friends & P2P friend loans
Route::middleware(['auth'])->prefix('friends')->name('friends.')->group(function () {
    Route::get('/',                          [\App\Http\Controllers\FriendController::class, 'index'])->name('index');
    Route::post('/request',                  [\App\Http\Controllers\FriendController::class, 'store'])->name('request');
    Route::post('/{friendship}/accept',      [\App\Http\Controllers\FriendController::class, 'accept'])->name('accept');
    Route::post('/{friendship}/decline',     [\App\Http\Controllers\FriendController::class, 'decline'])->name('decline');
    Route::delete('/{friendship}',           [\App\Http\Controllers\FriendController::class, 'destroy'])->name('destroy');

    Route::post('/loans',                    [\App\Http\Controllers\FriendLoanController::class, 'request'])->name('loans.request');
    Route::post('/loans/{loan}/offer',       [\App\Http\Controllers\FriendLoanController::class, 'offer'])->name('loans.offer');
    Route::post('/loans/{loan}/counter',     [\App\Http\Controllers\FriendLoanController::class, 'counter'])->name('loans.counter');
    Route::post('/loans/{loan}/accept',      [\App\Http\Controllers\FriendLoanController::class, 'accept'])->name('loans.accept');
    Route::post('/loans/{loan}/decline',     [\App\Http\Controllers\FriendLoanController::class, 'decline'])->name('loans.decline');
    Route::post('/loans/{loan}/repay',       [\App\Http\Controllers\FriendLoanController::class, 'repay'])->name('loans.repay');

    Route::post('/gift',                     [\App\Http\Controllers\FriendController::class, 'sendGift'])->name('gift');
});

// Dreams — expensive, non-resellable, cosmetic profile flex purchases
Route::middleware(['auth'])->prefix('dreams')->name('dreams.')->group(function () {
    Route::get('/',                          [\App\Http\Controllers\DreamController::class, 'index'])->name('index');
    Route::post('/{dream}/purchase',         [\App\Http\Controllers\DreamController::class, 'purchase'])->name('purchase');
});

// Public challenge invite/preview — no auth, so shared links actually work for
// logged-out recipients and link-preview bots (WhatsApp/Twitter/etc.) can unfurl it.
Route::get('/challenges/{challenge}/invite', [\App\Http\Controllers\ChallengeController::class, 'invite'])->name('challenges.invite');

// Champions' Court — fair PvP/team/broadcast challenges
Route::middleware(['auth'])->prefix('challenges')->name('challenges.')->group(function () {
    Route::get('/',                          [\App\Http\Controllers\ChallengeController::class, 'index'])->name('index');
    Route::get('/create',                    [\App\Http\Controllers\ChallengeController::class, 'create'])->name('create');
    Route::post('/',                         [\App\Http\Controllers\ChallengeController::class, 'store'])->name('store');
    Route::post('/{participant}/accept',     [\App\Http\Controllers\ChallengeController::class, 'accept'])->name('accept');
    Route::post('/{participant}/decline',    [\App\Http\Controllers\ChallengeController::class, 'decline'])->name('decline');
    Route::post('/{challenge}/join',         [\App\Http\Controllers\ChallengeController::class, 'join'])->name('join');
    Route::post('/{challenge}/enter-chama',  [\App\Http\Controllers\ChallengeController::class, 'enterChamaBattle'])->name('enter-chama');
    Route::post('/{challenge}/cancel',       [\App\Http\Controllers\ChallengeController::class, 'cancel'])->name('cancel');
    Route::get('/{challenge}/participants/{participant}/stats', [\App\Http\Controllers\ChallengeController::class, 'participantStats'])->name('participant-stats');
    Route::get('/{challenge}',               [\App\Http\Controllers\ChallengeController::class, 'show'])->name('show');
});

// Community Forums
Route::middleware(['auth'])->prefix('forums')->name('forums.')->group(function () {
    Route::get('/',                        [\App\Http\Controllers\ForumController::class, 'index'])->name('index');
    Route::get('/check-new',               [\App\Http\Controllers\ForumController::class, 'checkNew'])->name('check-new');
    Route::post('/',                       [\App\Http\Controllers\ForumController::class, 'store'])->name('store');
    Route::post('/vote',                   [\App\Http\Controllers\ForumController::class, 'vote'])->name('vote');
    Route::get('/{topic:slug}',            [\App\Http\Controllers\ForumController::class, 'show'])->name('show');
    Route::put('/{topic}',                 [\App\Http\Controllers\ForumController::class, 'update'])->name('update');
    Route::delete('/{topic}',              [\App\Http\Controllers\ForumController::class, 'destroy'])->name('destroy');
    Route::post('/{topic}/reply',          [\App\Http\Controllers\ForumController::class, 'reply'])->name('reply');
    Route::post('/{topic}/react',          [\App\Http\Controllers\ForumController::class, 'react'])->name('react');
    Route::delete('/replies/{reply}',      [\App\Http\Controllers\ForumController::class, 'destroyReply'])->name('replies.destroy');
    Route::post('/{topic}/pin',            [\App\Http\Controllers\ForumController::class, 'togglePin'])->name('pin');
    Route::post('/{topic}/lock',           [\App\Http\Controllers\ForumController::class, 'toggleLock'])->name('lock');
});

// Investment Deals (player)
Route::middleware(['auth'])->group(function () {
    Route::post('/deals/invest',            [\App\Http\Controllers\DealController::class, 'invest'])->name('deals.invest');
});

// Shares (Equity Square trading tab)
Route::middleware(['auth'])->prefix('shares')->name('shares.')->group(function () {
    Route::post('/buy',                     [\App\Http\Controllers\ShareController::class, 'buy'])->name('buy');
    Route::post('/sell',                    [\App\Http\Controllers\ShareController::class, 'sell'])->name('sell');
});

// Loans (player)
Route::middleware(['auth'])->prefix('loans')->name('loans.')->group(function () {
    Route::post('/take',                    [\App\Http\Controllers\LoanController::class, 'take'])->name('take');
    Route::post('/{loan}/repay',            [\App\Http\Controllers\LoanController::class, 'repay'])->name('repay');
});

// Mama Pesa AI Chat
Route::middleware(['auth'])->prefix('ai')->name('ai.')->group(function () {
    Route::post('/chat',   [\App\Http\Controllers\AiChatController::class, 'chat'])->name('chat');
    Route::post('/clear',  [\App\Http\Controllers\AiChatController::class, 'clear'])->name('clear');
    Route::get('/context', [\App\Http\Controllers\AiChatController::class, 'context'])->name('context');
});

// Web Push notifications (auth only)
Route::middleware('auth')->prefix('push')->name('push.')->group(function () {
    Route::get('/public-key',       [\App\Http\Controllers\PushController::class, 'publicKey'])->name('public-key');
    Route::post('/subscribe',       [\App\Http\Controllers\PushController::class, 'subscribe'])->name('subscribe');
    Route::post('/unsubscribe',     [\App\Http\Controllers\PushController::class, 'unsubscribe'])->name('unsubscribe');
    Route::get('/preferences',      [\App\Http\Controllers\PushController::class, 'getPreferences'])->name('preferences.index');
    Route::post('/preferences',     [\App\Http\Controllers\PushController::class, 'savePreferences'])->name('preferences.save');
});

// Savings schemes (auth only)
Route::middleware('auth')->prefix('savings')->name('savings.')->group(function () {
    Route::get('/',                             [SavingsController::class, 'index'])->name('index');
    Route::post('/',                            [SavingsController::class, 'store'])->name('store');
    Route::post('/{scheme}/deposit',            [SavingsController::class, 'deposit'])->name('deposit');
    Route::post('/{scheme}/withdraw',           [SavingsController::class, 'withdraw'])->name('withdraw');
    Route::delete('/{scheme}',                  [SavingsController::class, 'destroy'])->name('destroy');
});

// Admin panel
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/docs', [AdminController::class, 'docs'])->name('docs');

    // Arcade sponsors — business/monetization, kept out of GameSet
    Route::post('/sponsors',                    [AdminController::class, 'storeSponsor'])->name('sponsors.store');
    Route::put('/sponsors/{sponsor}',           [AdminController::class, 'updateSponsor'])->name('sponsors.update');
    Route::delete('/sponsors/{sponsor}',        [AdminController::class, 'destroySponsor'])->name('sponsors.destroy');
    Route::post('/sponsors/tiles/{tile}',       [AdminController::class, 'assignSponsorTile'])->name('sponsors.tiles.assign');

    // User management
    Route::post('/users',                         [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users/{user}/gameset',         [AdminController::class, 'toggleGameset'])->name('users.gameset');
    Route::post('/users/{user}/admin',           [AdminController::class, 'toggleAdmin'])->name('users.admin');
    Route::post('/users/{user}/active',          [AdminController::class, 'toggleUserActive'])->name('users.toggle-active');
    Route::delete('/users/{user}',               [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{user}/password-reset',  [AdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/subscribe',       [AdminController::class, 'grantSubscription'])->name('users.subscribe');
    Route::delete('/users/{user}/subscribe',     [AdminController::class, 'revokeSubscription'])->name('users.unsubscribe');

    // Subscription management
    Route::post('/subscriptions/{subscription}/approve', [AdminController::class, 'approveSubscription'])->name('subscriptions.approve');
    Route::post('/subscriptions/{subscription}/pause',   [AdminController::class, 'pauseSubscription'])->name('subscriptions.pause');
    Route::post('/subscriptions/{subscription}/resume',  [AdminController::class, 'resumeSubscription'])->name('subscriptions.resume');

    // Plan CRUD
    Route::put('/plans/{plan}',    [AdminController::class, 'updatePlan'])->name('plans.update');
    Route::post('/plans/school',   [AdminController::class, 'createSchoolPlan'])->name('plans.school.create');
    Route::delete('/plans/{plan}', [AdminController::class, 'deletePlan'])->name('plans.delete');

    // Settings
    Route::post('/settings',           [AdminController::class, 'saveSettings'])->name('settings.save');
    Route::post('/gates',              [AdminController::class, 'saveGates'])->name('gates.save');
    Route::post('/trackers',           [AdminController::class, 'saveTrackers'])->name('trackers.save');
    Route::get('/push/vapid-status',   [AdminController::class, 'vapidStatus'])->name('push.vapid-status');
    Route::post('/push/vapid-keys',    [AdminController::class, 'generateVapidKeys'])->name('push.vapid-generate');
    Route::post('/push/test',          [AdminController::class, 'testPush'])->name('push.test');
    Route::post('/broadcast',          [AdminController::class, 'sendBroadcast'])->name('broadcast.send');
    Route::post('/settings/smtp-test', [AdminController::class, 'testSmtp'])->name('settings.smtp-test');
    Route::post('/settings/ai-test',   [AdminController::class, 'testAi'])->name('settings.ai-test');

    // Quest management
    Route::get('/quests/pending',                          [AdminController::class, 'pendingQuests'])->name('quests.pending');
    Route::post('/quests/{userQuest}/approve',             [AdminController::class, 'approveQuest'])->name('quests.approve');

    // School subscriptions
    Route::post('/schools',                                [AdminController::class, 'createSchool'])->name('schools.create');
    Route::put('/schools/{school}',                        [AdminController::class, 'updateSchool'])->name('schools.update');
    Route::delete('/schools/{school}',                     [AdminController::class, 'deleteSchool'])->name('schools.delete');

    // Financial Crisis Events
    Route::post('/crises',                                 [AdminController::class, 'createCrisis'])->name('crises.create');
    Route::delete('/crises/{crisis}',                      [AdminController::class, 'deleteCrisis'])->name('crises.delete');

    // Coupons
    Route::post('/coupons',                                [AdminController::class, 'createCoupon'])->name('coupons.create');
    Route::put('/coupons/{coupon}',                        [AdminController::class, 'updateCoupon'])->name('coupons.update');
    Route::post('/coupons/{coupon}/toggle',                [AdminController::class, 'toggleCoupon'])->name('coupons.toggle');
    Route::delete('/coupons/{coupon}',                     [AdminController::class, 'deleteCoupon'])->name('coupons.delete');

    // Artisan runner
    Route::post('/artisan', [AdminController::class, 'runArtisan'])->name('artisan.run');

    // Marketplace asset management
    Route::get('/assets',                    [AdminController::class, 'assets'])->name('assets');
    Route::get('/assets/create',             [AdminController::class, 'assetCreate'])->name('assets.create');
    Route::post('/assets',                   [AdminController::class, 'assetStore'])->name('assets.store');
    Route::get('/assets/{asset}/edit',       [AdminController::class, 'assetEdit'])->name('assets.edit');
    Route::put('/assets/{asset}',            [AdminController::class, 'assetUpdate'])->name('assets.update');
    Route::delete('/assets/{asset}',         [AdminController::class, 'assetDestroy'])->name('assets.destroy');

    // Inbox / NPC management
    Route::post('/npcs',                         [AdminController::class, 'storeNpc'])->name('npcs.store');
    Route::post('/npcs/{npc}/toggle',            [AdminController::class, 'toggleNpc'])->name('npcs.toggle');
    Route::post('/decisions',                    [AdminController::class, 'storeDecision'])->name('decisions.store');
    Route::post('/decisions/{decision}/toggle',  [AdminController::class, 'toggleDecision'])->name('decisions.toggle');
});

// Smart Money Tools — its own standalone page (calculators + Real-Life
// Tools), gated the same way as the old dashboard section via
// PlanGate::smart_tools_access. /tools is a legacy bookmark redirect.
Route::get('/money-toolkit', function () {
    $smartToolsUnlocked = app(\App\Services\PlanGate::class)->limit(auth()->user(), 'smart_tools_access') > 0;
    return view('money-toolkit', compact('smartToolsUnlocked'));
})->middleware(['auth'])->name('money-toolkit');

Route::get('/tools', fn () => redirect()->route('money-toolkit'))->middleware(['auth'])->name('tools');

// How To — static guide page (same content as the first-time onboarding
// wizard), reachable anytime from the nav, independent of the wizard's
// one-time career-quiz gate.
Route::get('/how-to', fn () => view('how-to', ['steps' => \App\Services\OnboardingService::steps()]))
    ->middleware(['auth'])->name('how-to');

// Life Simulator
Route::middleware(['auth'])->prefix('life')->name('life.')->group(function () {
    Route::get('/',                              [LifeController::class, 'board'])->name('board');
    Route::get('/career',                        [LifeController::class, 'career'])->name('career');
    Route::get('/timeline',                      [LifeController::class, 'timeline'])->name('timeline');
    Route::get('/finances',                      [LifeController::class, 'finances'])->name('finances');
    Route::post('/bills/{playerBill}/pay',       [LifeController::class, 'payBill'])->name('bills.pay');
    Route::post('/work/checkin',                 [LifeController::class, 'workCheckin'])->name('work.checkin');
    Route::post('/assets/{playerAsset}/maintain',[LifeController::class, 'maintain'])->name('assets.maintain');
    Route::post('/onboard',                      [LifeController::class, 'onboard'])->name('onboard');
    Route::get('/quiz',                          [LifeController::class, 'quiz'])->name('quiz');
});

// Daily Spin Wheel
Route::middleware(['auth'])->prefix('spin')->name('spin.')->group(function () {
    Route::get('/',    [\App\Http\Controllers\SpinController::class, 'index'])->name('index');
    Route::post('/do', [\App\Http\Controllers\SpinController::class, 'spin'])->name('do');
});

// Arcade — Snakes & Cash
Route::middleware(['auth'])->prefix('arcade/snakes-and-cash')->name('arcade.snakes.')->group(function () {
    Route::get('/',                     [\App\Http\Controllers\ArcadeSnakesController::class, 'index'])->name('lobby');
    Route::get('/invites/pending',      [\App\Http\Controllers\ArcadeSnakesController::class, 'pendingInvites'])->name('invites.pending');
    Route::post('/solo',                [\App\Http\Controllers\ArcadeSnakesController::class, 'startSolo'])->name('solo');
    Route::post('/match',               [\App\Http\Controllers\ArcadeSnakesController::class, 'createMatch'])->name('match.create');
    Route::post('/match/join',          [\App\Http\Controllers\ArcadeSnakesController::class, 'joinMatch'])->name('match.join');
    Route::post('/wager',               [\App\Http\Controllers\ArcadeSnakesController::class, 'createWagerMatch'])->name('wager.create');
    Route::post('/wager/join',          [\App\Http\Controllers\ArcadeSnakesController::class, 'joinWagerMatch'])->name('wager.join');
    Route::post('/invite/{invite}/accept', [\App\Http\Controllers\ArcadeSnakesController::class, 'acceptInvite'])->name('wager.invite.accept');
    Route::post('/invite/{invite}/decline', [\App\Http\Controllers\ArcadeSnakesController::class, 'declineInvite'])->name('wager.invite.decline');
    Route::get('/play/{session}',       [\App\Http\Controllers\ArcadeSnakesController::class, 'play'])->name('play');
    Route::get('/play/{session}/state', [\App\Http\Controllers\ArcadeSnakesController::class, 'state'])->name('state');
    Route::post('/play/{session}/roll', [\App\Http\Controllers\ArcadeSnakesController::class, 'roll'])->name('roll');
    Route::post('/play/{session}/cash-out',  [\App\Http\Controllers\ArcadeSnakesController::class, 'cashOut'])->name('cash-out');
    Route::post('/play/{session}/react',     [\App\Http\Controllers\ArcadeSnakesController::class, 'sendReaction'])->name('react');
});

// Life Inbox
Route::middleware(['auth'])->prefix('inbox')->name('inbox.')->group(function () {
    Route::get('/',         [\App\Http\Controllers\InboxController::class, 'index'])->name('index');
    Route::post('/resolve', [\App\Http\Controllers\InboxController::class, 'resolve'])->name('resolve');
});

// Marketplace & Portfolio
Route::middleware(['auth'])->group(function () {
    Route::get('/marketplace',                        [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/marketplace/all',                    [MarketplaceController::class, 'all'])->name('marketplace.all');
    Route::get('/marketplace/search',                 [MarketplaceController::class, 'search'])->name('marketplace.search');
    Route::post('/marketplace/{asset}/buy',           [MarketplaceController::class, 'buy'])->name('marketplace.buy');
    Route::get('/portfolio',                          [\App\Http\Controllers\PortfolioController::class, 'index'])->name('portfolio');
    Route::post('/portfolio/{playerAsset}/sell',      [MarketplaceController::class, 'sell'])->name('portfolio.sell');
});

// P2P Trade Market
Route::middleware(['auth'])->prefix('trade')->name('trade.')->group(function () {
    Route::get('/',                             [TradeController::class, 'index'])->name('index');
    Route::post('/{playerAsset}/list',          [TradeController::class, 'list'])->name('list');
    Route::post('/{listing}/buy',               [TradeController::class, 'buy'])->name('buy');
    Route::delete('/{listing}/cancel',          [TradeController::class, 'cancel'])->name('cancel');
});

// Badges management (admin/gameset)
Route::middleware(['auth', 'gameset'])->prefix('gameset')->name('gameset.')->group(function () {
    Route::get('/badges',             [BadgeController::class, 'index'])->name('badges.index');
    Route::post('/badges',            [BadgeController::class, 'store'])->name('badges.store');
    Route::put('/badges/{badge}',     [BadgeController::class, 'update'])->name('badges.update');
    Route::delete('/badges/{badge}',  [BadgeController::class, 'destroy'])->name('badges.destroy');
    Route::post('/badges/award',      [BadgeController::class, 'award'])->name('badges.award');
    Route::post('/badges/revoke',     [BadgeController::class, 'revoke'])->name('badges.revoke');
});

// GameSet admin
Route::middleware(['auth', 'gameset'])->prefix('gameset')->name('gameset.')->group(function () {
    Route::get('/',                    [GameSetController::class, 'index'])->name('index');
    Route::get('/docs',                [GameSetController::class, 'docs'])->name('docs');

    // Marketplace asset management
    Route::get('/assets',                         [\App\Http\Controllers\GamesetAssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create',                  [\App\Http\Controllers\GamesetAssetController::class, 'create'])->name('assets.create');
    Route::post('/assets',                        [\App\Http\Controllers\GamesetAssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{asset}/edit',            [\App\Http\Controllers\GamesetAssetController::class, 'edit'])->name('assets.edit');
    Route::put('/assets/{asset}',                 [\App\Http\Controllers\GamesetAssetController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{asset}',              [\App\Http\Controllers\GamesetAssetController::class, 'destroy'])->name('assets.destroy');
    Route::post('/assets/{asset}/toggle-active',  [\App\Http\Controllers\GamesetAssetController::class, 'toggleActive'])->name('assets.toggle-active');

    // Equity Square share management
    Route::get('/shares',                         [\App\Http\Controllers\GamesetShareController::class, 'index'])->name('shares.index');
    Route::get('/shares/create',                  [\App\Http\Controllers\GamesetShareController::class, 'create'])->name('shares.create');
    Route::post('/shares',                        [\App\Http\Controllers\GamesetShareController::class, 'store'])->name('shares.store');
    Route::get('/shares/{share}/edit',            [\App\Http\Controllers\GamesetShareController::class, 'edit'])->name('shares.edit');
    Route::put('/shares/{share}',                 [\App\Http\Controllers\GamesetShareController::class, 'update'])->name('shares.update');
    Route::delete('/shares/{share}',              [\App\Http\Controllers\GamesetShareController::class, 'destroy'])->name('shares.destroy');
    Route::post('/shares/{share}/toggle-active',  [\App\Http\Controllers\GamesetShareController::class, 'toggleActive'])->name('shares.toggle-active');

    // Bill templates
    Route::get('/bills',                        [GamesetBillController::class, 'index'])->name('bills.index');
    Route::get('/bills/create',                 [GamesetBillController::class, 'create'])->name('bills.create');
    Route::post('/bills',                       [GamesetBillController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}/edit',            [GamesetBillController::class, 'edit'])->name('bills.edit');
    Route::put('/bills/{bill}',                 [GamesetBillController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{bill}',              [GamesetBillController::class, 'destroy'])->name('bills.destroy');
    Route::post('/bills/{bill}/toggle-active',  [GamesetBillController::class, 'toggleActive'])->name('bills.toggle-active');

    // Fun World activities
    Route::get('/fun-world',                              [\App\Http\Controllers\GamesetFunWorldController::class, 'index'])->name('fun-world.index');
    Route::post('/fun-world',                             [\App\Http\Controllers\GamesetFunWorldController::class, 'store'])->name('fun-world.store');
    Route::put('/fun-world/{activity}',                   [\App\Http\Controllers\GamesetFunWorldController::class, 'update'])->name('fun-world.update');
    Route::delete('/fun-world/{activity}',                [\App\Http\Controllers\GamesetFunWorldController::class, 'destroy'])->name('fun-world.destroy');
    Route::post('/fun-world/{activity}/toggle-active',    [\App\Http\Controllers\GamesetFunWorldController::class, 'toggleActive'])->name('fun-world.toggle-active');

    // Spin wheel segments
    Route::get('/spin-wheel',                             [\App\Http\Controllers\GamesetSpinController::class, 'index'])->name('spin.index');
    Route::post('/spin-wheel',                            [\App\Http\Controllers\GamesetSpinController::class, 'store'])->name('spin.store');
    Route::put('/spin-wheel/{segment}',                   [\App\Http\Controllers\GamesetSpinController::class, 'update'])->name('spin.update');
    Route::delete('/spin-wheel/{segment}',                [\App\Http\Controllers\GamesetSpinController::class, 'destroy'])->name('spin.destroy');
    Route::post('/spin-wheel/{segment}/toggle-active',    [\App\Http\Controllers\GamesetSpinController::class, 'toggleActive'])->name('spin.toggle-active');

    // World Map — district tap-area & pin-destination calibration
    Route::get('/world-map',                              [\App\Http\Controllers\GamesetWorldController::class, 'index'])->name('world.index');
    Route::post('/world-map/positions',                   [\App\Http\Controllers\GamesetWorldController::class, 'savePositions'])->name('world.positions.save');

    // Arcade — Snakes & Cash tile registry
    Route::get('/arcade',                                 [\App\Http\Controllers\GamesetArcadeController::class, 'index'])->name('arcade.index');
    Route::post('/arcade/{game}/settings',                [\App\Http\Controllers\GamesetArcadeController::class, 'saveSettings'])->name('arcade.settings.save');
    Route::post('/arcade/{game}/tiles',                   [\App\Http\Controllers\GamesetArcadeController::class, 'saveTiles'])->name('arcade.tiles.save');
    Route::post('/arcade/{game}/tile-positions',           [\App\Http\Controllers\GamesetArcadeController::class, 'saveTilePositions'])->name('arcade.tiles.positions.save');
    Route::post('/arcade/mystery',                        [\App\Http\Controllers\GamesetArcadeController::class, 'storeMystery'])->name('arcade.mystery.store');
    Route::put('/arcade/mystery/{outcome}',                [\App\Http\Controllers\GamesetArcadeController::class, 'updateMystery'])->name('arcade.mystery.update');
    Route::delete('/arcade/mystery/{outcome}',             [\App\Http\Controllers\GamesetArcadeController::class, 'destroyMystery'])->name('arcade.mystery.destroy');
    Route::post('/arcade/{game}/stake-tiers',              [\App\Http\Controllers\GamesetArcadeController::class, 'storeStakeTier'])->name('arcade.stake-tiers.store');
    Route::put('/arcade/stake-tiers/{stakeTier}',          [\App\Http\Controllers\GamesetArcadeController::class, 'updateStakeTier'])->name('arcade.stake-tiers.update');
    Route::delete('/arcade/stake-tiers/{stakeTier}',       [\App\Http\Controllers\GamesetArcadeController::class, 'destroyStakeTier'])->name('arcade.stake-tiers.destroy');
    Route::post('/arcade/{game}/flavor-text',              [\App\Http\Controllers\GamesetArcadeController::class, 'storeFlavorText'])->name('arcade.flavor-text.store');
    Route::put('/arcade/flavor-text/{flavorText}',         [\App\Http\Controllers\GamesetArcadeController::class, 'updateFlavorText'])->name('arcade.flavor-text.update');
    Route::delete('/arcade/flavor-text/{flavorText}',      [\App\Http\Controllers\GamesetArcadeController::class, 'destroyFlavorText'])->name('arcade.flavor-text.destroy');

    // Life event templates
    Route::get('/life-events',                          [GamesetLifeEventController::class, 'index'])->name('life-events.index');
    Route::get('/life-events/create',                   [GamesetLifeEventController::class, 'create'])->name('life-events.create');
    Route::post('/life-events',                         [GamesetLifeEventController::class, 'store'])->name('life-events.store');
    Route::get('/life-events/{lifeEvent}/edit',         [GamesetLifeEventController::class, 'edit'])->name('life-events.edit');
    Route::put('/life-events/{lifeEvent}',              [GamesetLifeEventController::class, 'update'])->name('life-events.update');
    Route::delete('/life-events/{lifeEvent}',           [GamesetLifeEventController::class, 'destroy'])->name('life-events.destroy');
    Route::post('/life-events/{lifeEvent}/toggle-active',[GamesetLifeEventController::class, 'toggleActive'])->name('life-events.toggle-active');

    // Courses & Jobs management (gameset/admin)
    Route::get('/courses',                          [\App\Http\Controllers\GamesetCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create',                   [\App\Http\Controllers\GamesetCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses',                         [\App\Http\Controllers\GamesetCourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit',            [\App\Http\Controllers\GamesetCourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}',                 [\App\Http\Controllers\GamesetCourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}',              [\App\Http\Controllers\GamesetCourseController::class, 'destroy'])->name('courses.destroy');
    Route::post('/courses/{course}/toggle-active',  [\App\Http\Controllers\GamesetCourseController::class, 'toggleActive'])->name('courses.toggle-active');

    Route::get('/jobs',                             [\App\Http\Controllers\GamesetJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/create',                      [\App\Http\Controllers\GamesetJobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs',                            [\App\Http\Controllers\GamesetJobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/edit',                  [\App\Http\Controllers\GamesetJobController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}',                       [\App\Http\Controllers\GamesetJobController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{job}',                    [\App\Http\Controllers\GamesetJobController::class, 'destroy'])->name('jobs.destroy');
    Route::post('/jobs/{job}/toggle-active',        [\App\Http\Controllers\GamesetJobController::class, 'toggleActive'])->name('jobs.toggle-active');

    // Quest management (gameset)
    Route::get('/quests',                         [\App\Http\Controllers\GamesetQuestController::class, 'index'])->name('quests.index');
    Route::get('/quests/create',                  [\App\Http\Controllers\GamesetQuestController::class, 'create'])->name('quests.create');
    Route::get('/quests/trigger-options',         [\App\Http\Controllers\GamesetQuestController::class, 'triggerOptions'])->name('quests.trigger-options');
    Route::get('/quests/generated',               [\App\Http\Controllers\GamesetQuestController::class, 'generated'])->name('quests.generated');
    Route::post('/quests/bulk-activate',          [\App\Http\Controllers\GamesetQuestController::class, 'bulkActivate'])->name('quests.bulk-activate');
    Route::post('/quests/bulk-delete',            [\App\Http\Controllers\GamesetQuestController::class, 'bulkDelete'])->name('quests.bulk-delete');
    Route::post('/quests/reorder',                [\App\Http\Controllers\GamesetQuestController::class, 'reorder'])->name('quests.reorder');
    Route::post('/quests',                        [\App\Http\Controllers\GamesetQuestController::class, 'store'])->name('quests.store');
    Route::get('/quests/{quest}/edit',            [\App\Http\Controllers\GamesetQuestController::class, 'edit'])->name('quests.edit');
    Route::put('/quests/{quest}',                 [\App\Http\Controllers\GamesetQuestController::class, 'update'])->name('quests.update');
    Route::delete('/quests/{quest}',              [\App\Http\Controllers\GamesetQuestController::class, 'destroy'])->name('quests.destroy');
    Route::post('/quests/{quest}/toggle-active',  [\App\Http\Controllers\GamesetQuestController::class, 'toggleActive'])->name('quests.toggle-active');

    // Automation: contract rules + Quest Factory settings
    Route::get('/automation',                     [\App\Http\Controllers\GamesetAutomationController::class, 'index'])->name('automation.index');
    Route::post('/automation/rules',              [\App\Http\Controllers\GamesetAutomationController::class, 'storeRule'])->name('automation.rules.store');
    Route::put('/automation/rules/{rule}',        [\App\Http\Controllers\GamesetAutomationController::class, 'updateRule'])->name('automation.rules.update');
    Route::delete('/automation/rules/{rule}',     [\App\Http\Controllers\GamesetAutomationController::class, 'destroyRule'])->name('automation.rules.destroy');
    Route::post('/automation/rules/{rule}/toggle',[\App\Http\Controllers\GamesetAutomationController::class, 'toggleRule'])->name('automation.rules.toggle');
    Route::post('/automation/factory',            [\App\Http\Controllers\GamesetAutomationController::class, 'factorySettings'])->name('automation.factory');

    // Quest Blueprints: level-ladder quest generation
    Route::post('/automation/blueprints',                    [\App\Http\Controllers\GamesetAutomationController::class, 'storeBlueprint'])->name('automation.blueprints.store');
    Route::put('/automation/blueprints/{blueprint}',         [\App\Http\Controllers\GamesetAutomationController::class, 'updateBlueprint'])->name('automation.blueprints.update');
    Route::delete('/automation/blueprints/{blueprint}',      [\App\Http\Controllers\GamesetAutomationController::class, 'destroyBlueprint'])->name('automation.blueprints.destroy');
    Route::post('/automation/blueprints/{blueprint}/toggle', [\App\Http\Controllers\GamesetAutomationController::class, 'toggleBlueprint'])->name('automation.blueprints.toggle');
    Route::post('/automation/sweep',                         [\App\Http\Controllers\GamesetAutomationController::class, 'runSweep'])->name('automation.sweep');
    Route::post('/automation/mix-quests',                    [\App\Http\Controllers\GamesetAutomationController::class, 'mixQuests'])->name('automation.mix-quests');
    Route::post('/automation/mix-life-events',               [\App\Http\Controllers\GamesetAutomationController::class, 'mixLifeEvents'])->name('automation.mix-life-events');

    // Investment Deals management
    Route::get('/deals',                          [\App\Http\Controllers\GamesetDealController::class, 'index'])->name('deals.index');
    Route::get('/deals/create',                   [\App\Http\Controllers\GamesetDealController::class, 'create'])->name('deals.create');
    Route::post('/deals',                         [\App\Http\Controllers\GamesetDealController::class, 'store'])->name('deals.store');
    Route::get('/deals/{deal}/edit',              [\App\Http\Controllers\GamesetDealController::class, 'edit'])->name('deals.edit');
    Route::put('/deals/{deal}',                   [\App\Http\Controllers\GamesetDealController::class, 'update'])->name('deals.update');
    Route::delete('/deals/{deal}',                [\App\Http\Controllers\GamesetDealController::class, 'destroy'])->name('deals.destroy');
    Route::post('/deals/{deal}/toggle-active',    [\App\Http\Controllers\GamesetDealController::class, 'toggleActive'])->name('deals.toggle-active');

    // Loan Products management
    Route::get('/loans',                          [\App\Http\Controllers\GamesetLoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create',                   [\App\Http\Controllers\GamesetLoanController::class, 'create'])->name('loans.create');
    Route::post('/loans',                         [\App\Http\Controllers\GamesetLoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/{loan}/edit',              [\App\Http\Controllers\GamesetLoanController::class, 'edit'])->name('loans.edit');
    Route::put('/loans/{loan}',                   [\App\Http\Controllers\GamesetLoanController::class, 'update'])->name('loans.update');
    Route::delete('/loans/{loan}',                [\App\Http\Controllers\GamesetLoanController::class, 'destroy'])->name('loans.destroy');
    Route::post('/loans/{loan}/toggle-active',    [\App\Http\Controllers\GamesetLoanController::class, 'toggleActive'])->name('loans.toggle-active');

    // Dreams catalog management
    Route::get('/dreams',                         [\App\Http\Controllers\GamesetDreamController::class, 'index'])->name('dreams.index');
    Route::get('/dreams/create',                  [\App\Http\Controllers\GamesetDreamController::class, 'create'])->name('dreams.create');
    Route::post('/dreams',                        [\App\Http\Controllers\GamesetDreamController::class, 'store'])->name('dreams.store');
    Route::get('/dreams/{dream}/edit',            [\App\Http\Controllers\GamesetDreamController::class, 'edit'])->name('dreams.edit');
    Route::put('/dreams/{dream}',                 [\App\Http\Controllers\GamesetDreamController::class, 'update'])->name('dreams.update');
    Route::delete('/dreams/{dream}',              [\App\Http\Controllers\GamesetDreamController::class, 'destroy'])->name('dreams.destroy');
    Route::patch('/dreams/{dream}/toggle',        [\App\Http\Controllers\GamesetDreamController::class, 'toggleActive'])->name('dreams.toggle-active');

    // Challenge templates + PesaCity Official Challenges
    Route::get('/challenges',                          [\App\Http\Controllers\GamesetChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/challenges/create',                   [\App\Http\Controllers\GamesetChallengeController::class, 'create'])->name('challenges.create');
    Route::post('/challenges',                         [\App\Http\Controllers\GamesetChallengeController::class, 'store'])->name('challenges.store');
    Route::post('/challenges/launch',                  [\App\Http\Controllers\GamesetChallengeController::class, 'launchOfficial'])->name('challenges.launch');
    Route::get('/challenges/{template}/edit',          [\App\Http\Controllers\GamesetChallengeController::class, 'edit'])->name('challenges.edit');
    Route::put('/challenges/{template}',               [\App\Http\Controllers\GamesetChallengeController::class, 'update'])->name('challenges.update');
    Route::delete('/challenges/{template}',            [\App\Http\Controllers\GamesetChallengeController::class, 'destroy'])->name('challenges.destroy');
    Route::patch('/challenges/{template}/toggle',      [\App\Http\Controllers\GamesetChallengeController::class, 'toggleActive'])->name('challenges.toggle-active');
    Route::delete('/challenges/{challenge}/cancel',    [\App\Http\Controllers\GamesetChallengeController::class, 'cancel'])->name('challenges.cancel');

    // Financial Crisis events (server-wide economy events)
    Route::get('/crises',                 [\App\Http\Controllers\GamesetCrisisController::class, 'index'])->name('crises.index');
    Route::post('/crises',                [\App\Http\Controllers\GamesetCrisisController::class, 'store'])->name('crises.store');
    Route::delete('/crises/{crisis}',     [\App\Http\Controllers\GamesetCrisisController::class, 'destroy'])->name('crises.destroy');

    Route::post('/game-rules',           [GameSetController::class, 'saveGameRules'])->name('game-rules.save');
    Route::post('/chapters',             [GameSetController::class, 'saveChapters'])->name('chapters.save');
    Route::post('/career-fields',        [GameSetController::class, 'saveCareerFields'])->name('career-fields.save');
    Route::post('/career-tracks',        [GameSetController::class, 'saveCareerTracks'])->name('career-tracks.save');
    Route::get('/level-config',          [GameSetController::class, 'getLevelConfig'])->name('level-config.index');
    Route::post('/level-config',         [GameSetController::class, 'saveLevelConfig'])->name('level-config.save');
    Route::post('/journey-milestones',   [GameSetController::class, 'saveJourneyMilestones'])->name('journey-milestones.save');
    Route::post('/quiz-questions',       [GameSetController::class, 'saveQuizQuestions'])->name('quiz-questions.save');
    Route::post('/asset-financing',      [GameSetController::class, 'saveAssetFinancing'])->name('asset-financing.save');
    Route::post('/onboarding-wizard',    [GameSetController::class, 'saveOnboardingWizard'])->name('onboarding-wizard.save');
});

require __DIR__.'/auth.php';
