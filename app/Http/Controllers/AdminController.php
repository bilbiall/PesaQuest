<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Badge;
use App\Models\MpesaTransaction;
use App\Models\SchoolSubscription;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::with('progress', 'streak', 'badges', 'subscription')
            ->latest()->get();

        // Guard against the is_active migration not having run yet on this
        // server — Eloquent's boolean cast would otherwise coerce a genuinely
        // missing column to `false` for every user, falsely flagging everyone
        // as deactivated.
        $usersHaveActiveColumn = \Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active');
        $schoolClassesTableExists = \Illuminate\Support\Facades\Schema::hasTable('school_classes');

        $stats = [
            'total_users'        => User::count(),
            'total_points'       => UserProgress::sum('points_total'),
            'active_today'       => UserProgress::whereDate('last_played_at', today())->count(),
            'badges_awarded'     => UserBadge::count(),
            'active_subscribers' => Subscription::where('status', 'active')
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->count(),
            'pending_payments'   => MpesaTransaction::where('status', 'pending')->count(),
        ];

        $recentActivity = UserProgress::with('user')->whereNotNull('last_played_at')
            ->orderByDesc('last_played_at')->take(12)->get()->filter(fn($p) => $p->user !== null);

        $plans = SubscriptionPlan::orderBy('months')->get();

        $coupons = \App\Models\Coupon::with('plan')->latest()->get();

        $allSubscriptions = Subscription::with('user', 'subscriptionPlan')
            ->latest()->take(100)->get();

        $payments = MpesaTransaction::with('user', 'plan')
            ->latest()->take(100)->get();

        $settings = Setting::all()->keyBy('key');

        $schools = SchoolSubscription::withCount(['members as active_members_count' => fn($q) => $q->where('status', 'active')])
            ->when(\Illuminate\Support\Facades\Schema::hasTable('school_teachers'), fn ($q) => $q->with('teachers'))
            ->latest()->get();

        try {
            $crises = \App\Models\FinancialCrisis::orderByDesc('active_from')->take(20)->get();
        } catch (\Throwable) {
            $crises = collect();
        }

        // Freemium gate values (Settings-backed, defaults from PlanGate)
        $gate      = app(\App\Services\PlanGate::class);
        $freeGates = $gate->limits()['free'];
        $gateMeta  = [
            'trial_days'         => $gate->trialDays(),
            'upsell_nag_enabled' => Setting::get('upsell_nag_enabled', '1') === '1',
            'upsell_nag_days'    => (int) Setting::get('upsell_nag_days', 3),
            'max_quests_per_day' => (int) Setting::get('max_quests_per_day', 0),
        ];

        $sponsors = \Illuminate\Support\Facades\Schema::hasTable('arcade_sponsors')
            ? \App\Models\ArcadeSponsor::withCount('tiles')->latest()->get()
            : collect();

        $sponsorableTiles = \Illuminate\Support\Facades\Schema::hasTable('arcade_tiles')
            ? \App\Models\ArcadeTile::with('sponsor')->where('money_effect', 'reward')->orderBy('number')->get()
            : collect();

        return view('admin.panel', compact(
            'users', 'stats', 'recentActivity', 'plans',
            'allSubscriptions', 'payments', 'settings', 'schools', 'crises', 'coupons',
            'freeGates', 'gateMeta', 'usersHaveActiveColumn', 'sponsors', 'sponsorableTiles',
            'schoolClassesTableExists'
        ));
    }

    /**
     * In-app rendering of docs/ADMIN-GUIDE.md — so any admin (including one
     * you're onboarding remotely) gets the full operations manual without
     * repo access.
     */
    public function docs()
    {
        $doc = \App\Services\DocsRenderer::render('docs/ADMIN-GUIDE.md');
        return view('admin.docs', $doc);
    }

    /**
     * In-house operational dashboard — the numbers no third-party analytics
     * tool (PostHog/GA4/Clarity) understands, because they're specific to
     * this game's own tables (jobs, bills, savings, credit score, quests).
     */
    public function analytics()
    {
        $activeToday = UserProgress::whereDate('last_played_at', today())->count();
        $liveOnline  = UserProgress::where('last_played_at', '>=', now()->subMinutes(15))->count();

        $totalQuests     = \Illuminate\Support\Facades\Schema::hasTable('user_quests') ? \App\Models\UserQuest::count() : 0;
        $completedQuests = \Illuminate\Support\Facades\Schema::hasTable('user_quests') ? \App\Models\UserQuest::whereNotNull('completed_at')->count() : 0;
        $questCompletionRate = $totalQuests > 0 ? round($completedQuests / $totalQuests * 100, 1) : 0;

        $popularJobs = \Illuminate\Support\Facades\Schema::hasTable('player_city_jobs')
            ? \App\Models\PlayerCityJob::where('status', 'employed')
                ->select('city_job_id', \Illuminate\Support\Facades\DB::raw('count(*) as cnt'))
                ->groupBy('city_job_id')->orderByDesc('cnt')->take(8)->get()
                ->map(function ($row) {
                    $job = \App\Models\CityJob::find($row->city_job_id);
                    return ['label' => $job?->title ?? 'Unknown', 'count' => $row->cnt];
                })
            : collect();

        $savingsAccountsTotal = \Illuminate\Support\Facades\Schema::hasTable('savings_schemes') ? \App\Models\SavingsScheme::count() : 0;
        $savingsAccountsToday = \Illuminate\Support\Facades\Schema::hasTable('savings_schemes') ? \App\Models\SavingsScheme::whereDate('created_at', today())->count() : 0;
        $totalSavings         = \Illuminate\Support\Facades\Schema::hasTable('savings_schemes') ? \App\Models\SavingsScheme::sum('current_amount') : 0;

        $missedBills = \Illuminate\Support\Facades\Schema::hasTable('player_bills')
            ? \App\Models\PlayerBill::select('bill_id', \Illuminate\Support\Facades\DB::raw('sum(missed_count) as total_missed'))
                ->groupBy('bill_id')->orderByDesc('total_missed')->take(8)->get()
                ->filter(fn ($row) => $row->total_missed > 0)
                ->map(function ($row) {
                    $bill = \App\Models\Bill::find($row->bill_id);
                    return ['label' => $bill?->name ?? 'Unknown', 'count' => (int) $row->total_missed];
                })
            : collect();

        $avgCreditScore = round(UserProgress::avg('credit_score') ?? 500);
        $highestLevel   = UserProgress::max('level') ?? 1;
        $highestLevelPlayer = UserProgress::where('level', $highestLevel)->orderByDesc('points_total')->first();

        $byCounty = User::whereNotNull('county')->select('county', \Illuminate\Support\Facades\DB::raw('count(*) as cnt'))
            ->groupBy('county')->orderByDesc('cnt')->get();
        $unknownCounty = User::whereNull('county')->count();

        $trackers = [
            'posthog_key'         => Setting::get('posthog_key', ''),
            'posthog_host'        => Setting::get('posthog_host', 'https://us.i.posthog.com'),
            'ga4_measurement_id'  => Setting::get('ga4_measurement_id', ''),
            'clarity_project_id'  => Setting::get('clarity_project_id', ''),
        ];

        return view('admin.analytics', [
            'activeToday'          => $activeToday,
            'liveOnline'           => $liveOnline,
            'questCompletionRate'  => $questCompletionRate,
            'totalQuests'          => $totalQuests,
            'completedQuests'      => $completedQuests,
            'popularJobs'          => $popularJobs,
            'savingsAccountsTotal' => $savingsAccountsTotal,
            'savingsAccountsToday' => $savingsAccountsToday,
            'totalSavings'         => $totalSavings,
            'missedBills'          => $missedBills,
            'avgCreditScore'       => $avgCreditScore,
            'highestLevel'         => $highestLevel,
            'highestLevelPlayer'   => $highestLevelPlayer?->user,
            'byCounty'             => $byCounty,
            'unknownCounty'        => $unknownCounty,
            'trackers'             => $trackers,
        ]);
    }

    /**
     * Save third-party tracker IDs (PostHog / GA4 / Clarity). Storing these
     * as Settings (not .env) means they go live immediately without a
     * redeploy, matching the plan_limits/gates pattern above.
     */
    public function saveTrackers(Request $request)
    {
        $data = $request->validate([
            'posthog_key'        => 'nullable|string|max:255',
            'posthog_host'       => 'nullable|string|max:255',
            'ga4_measurement_id' => 'nullable|string|max:50',
            'clarity_project_id' => 'nullable|string|max:50',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, trim((string) $value), 'trackers');
        }

        return response()->json(['success' => true, 'message' => 'Tracker settings saved — live on next page load.']);
    }

    // ── Arcade Sponsors (business/monetization — kept out of GameSet) ──────

    public function storeSponsor(Request $request)
    {
        \App\Models\ArcadeSponsor::create($this->validatedSponsor($request));
        return back()->with('success', 'Sponsor added.');
    }

    public function updateSponsor(Request $request, \App\Models\ArcadeSponsor $sponsor)
    {
        $sponsor->update($this->validatedSponsor($request));
        return back()->with('success', 'Sponsor updated.');
    }

    public function destroySponsor(\App\Models\ArcadeSponsor $sponsor)
    {
        $sponsor->delete(); // tile assignments null out via nullOnDelete
        return back()->with('success', 'Sponsor deleted. Any tiles carrying its branding revert to unsponsored.');
    }

    public function assignSponsorTile(Request $request, \App\Models\ArcadeTile $tile)
    {
        $data = $request->validate(['arcade_sponsor_id' => 'nullable|exists:arcade_sponsors,id']);
        $tile->update(['arcade_sponsor_id' => $data['arcade_sponsor_id'] ?? null]);
        return back()->with('success', "Tile {$tile->number} sponsor updated.");
    }

    private function validatedSponsor(Request $request): array
    {
        $data = $request->validate([
            'name'      => 'required|string|max:60',
            'logo_path' => 'required|string|max:255',
            'tagline'   => 'nullable|string|max:120',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    /**
     * Save the freemium gates: what unsubscribed players can do, trial length,
     * upsell nag cadence and the daily quest cap. Nothing is hardcoded —
     * PlanGate reads plan_limits from Settings on every check.
     */
    // ── Web Push (VAPID) setup ──────────────────────────────────────────────

    public function vapidStatus()
    {
        return response()->json([
            'hasKeys'   => (bool) Setting::get('vapid_public_key'),
            'publicKey' => Setting::get('vapid_public_key', ''),
            'subject'   => Setting::get('vapid_subject', ''),
        ]);
    }

    public function generateVapidKeys(Request $request)
    {
        $data = $request->validate(['subject' => 'required|email']);

        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();

        Setting::set('vapid_public_key', $keys['publicKey'], 'push');
        Setting::set('vapid_private_key', $keys['privateKey'], 'push');
        Setting::set('vapid_subject', 'mailto:' . $data['subject'], 'push');

        // Old keys are now invalid — every existing subscription would fail
        // silently forever, so drop them and let players re-opt-in cleanly.
        \App\Models\PushSubscription::query()->delete();

        return response()->json(['success' => true, 'publicKey' => $keys['publicKey']]);
    }

    /**
     * Send a real push to the CURRENT admin's own device right now, bypassing
     * quiet-hours/daily-cap/category-preference checks (deliberate — this is
     * a manual diagnostic, not a real notification). Broadcasts, by contrast,
     * correctly respect those checks per recipient, which is why a broadcast
     * can silently produce zero pushes (quiet hours, no subscription, cap
     * reached) while still returning "success" — it succeeded at creating
     * the notifications, push delivery is a separate, filtered step.
     */
    public function testPush(Request $request)
    {
        $user = auth()->user();

        if (empty(Setting::get('vapid_public_key')) || empty(Setting::get('vapid_private_key'))) {
            return response()->json(['error' => 'No VAPID keys configured yet — generate them above first.'], 422);
        }

        if (!\App\Models\PushSubscription::where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Your OWN account has no push subscription on this device/browser. Go to Profile → Notification Settings and enable push notifications first, then try this button again.'], 422);
        }

        $sent = app(\App\Services\PushService::class)->send(
            $user,
            '🔔 Test Push',
            'If you see this, push notifications are working correctly!',
            ['type' => 'announcement']
        );

        if (!$sent) {
            return response()->json(['error' => 'Send attempt failed — your subscription may have expired. Try disabling and re-enabling push in Profile → Notification Settings, then test again.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Sent! It should arrive on this device within a few seconds — this bypassed quiet hours and the daily cap since it\'s a manual test.']);
    }

    // ── Broadcast composer — announcement + push, with an audience picker ──

    public function sendBroadcast(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:100',
            'body'      => 'required|string|max:300',
            'audience'  => 'required|in:all,age_group,school,free_only,single_user',
            'age_group' => 'required_if:audience,age_group|nullable|string|in:8-12,13-17,18-25,26+',
            'school_id' => 'required_if:audience,school|nullable|exists:school_subscriptions,id',
            'email'     => 'required_if:audience,single_user|nullable|email',
        ]);

        $query = User::query();

        switch ($data['audience']) {
            case 'age_group':
                $query->where('age_group', $data['age_group']);
                break;
            case 'school':
                $query->whereIn('id', \App\Models\SchoolMember::where('school_subscription_id', $data['school_id'])
                    ->where('status', 'active')->pluck('user_id'));
                break;
            case 'free_only':
                $query->whereDoesntHave('subscription', fn ($q) => $q->where('status', 'active')
                    ->where(fn ($q2) => $q2->whereNull('ends_at')->orWhere('ends_at', '>', now())));
                break;
            case 'single_user':
                $query->where('email', $data['email']);
                break;
            // 'all' — no filter
        }

        $userIds = $query->pluck('id');
        if ($userIds->isEmpty()) {
            return response()->json(['error' => 'No matching players found for that audience.'], 422);
        }

        // A manual, rare admin action — allow it more time than a normal request.
        // GameNotification::create() (not insert()) is used deliberately so each
        // recipient's own push mirror runs — quiet hours, daily cap and category
        // preferences are still respected per player, exactly like any other
        // in-game notification. Push is a no-op for anyone not yet subscribed,
        // so real cost scales with actual push subscribers, not audience size.
        @set_time_limit(120);

        foreach ($userIds as $uid) {
            \App\Models\GameNotification::create([
                'user_id' => $uid,
                'type'    => 'announcement',
                'title'   => '📢 ' . $data['title'],
                'body'    => $data['body'],
                'icon'    => '📢',
            ]);
        }

        return response()->json(['success' => true, 'recipients' => $userIds->count()]);
    }

    public function saveGates(Request $request)
    {
        $data = $request->validate([
            'free'                        => 'required|array',
            'free.max_assets'             => 'required|integer|min:0|max:1000',
            'free.max_active_deals'       => 'required|integer|min:0|max:1000',
            'free.max_savings_schemes'    => 'required|integer|min:0|max:1000',
            'free.max_loans'              => 'required|integer|min:0|max:1000',
            'free.catchup_ticks'          => 'required|integer|min:1|max:60',
            'free.ai_per_day'             => 'required|integer|min:0|max:1000',
            'free.fun_per_game_month'     => 'required|integer|min:0|max:1000',
            'free.forum_topic_min_level'  => 'required|integer|min:0|max:100',
            'free.chama_create'           => 'required|integer|in:0,1',
            'free.quests_per_day'         => 'required|integer|min:0|max:100',
            'free.spin_cooldown_days'     => 'required|integer|min:0|max:90',
            'free.smart_tools_access'     => 'required|integer|in:0,1',
            'free.send_money_access'      => 'required|integer|in:0,1',
            'free.pesatrail_games_per_day'=> 'required|integer|min:0|max:1000',
            'trial_days'                  => 'required|integer|min:0|max:365',
            'upsell_nag_enabled'          => 'required|boolean',
            'upsell_nag_days'             => 'required|integer|min:1|max:90',
            'max_quests_per_day'          => 'required|integer|min:0|max:100',
        ]);

        // Merge over any existing custom limits so premium overrides survive
        $existing         = json_decode(Setting::get('plan_limits', '{}'), true) ?: [];
        $existing['free'] = array_map('intval', $data['free']);
        Setting::set('plan_limits', json_encode($existing), 'plans');

        Setting::set('trial_days',         (string) $data['trial_days'],                'plans');
        Setting::set('upsell_nag_enabled', $data['upsell_nag_enabled'] ? '1' : '0',     'plans');
        Setting::set('upsell_nag_days',    (string) $data['upsell_nag_days'],           'plans');
        Setting::set('max_quests_per_day', (string) $data['max_quests_per_day'],        'game');

        return response()->json(['success' => true, 'message' => 'Freemium gates saved — live immediately for all players.']);
    }

    // ── Create user ───────────────────────────────────────────────────────

    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'age_group' => 'nullable|in:8-12,13-17,18-25,26+',
            'role'      => 'nullable|in:player,gameset,admin',
        ]);

        $role = $data['role'] ?? 'player';

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'age_group'  => $data['age_group'] ?? null,
            'is_admin'   => $role === 'admin',
            'is_gameset' => $role === 'gameset',
        ]);

        $user->ensureUsername();
        $user->getOrCreateProgress();

        return response()->json([
            'success' => true,
            'user'    => $user->only(['id', 'name', 'email', 'age_group', 'is_admin', 'is_gameset']),
        ]);
    }

    // ── User role toggles ──────────────────────────────────────────────────

    public function toggleGameset(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot change your own role.'], 403);
        }
        $user->update(['is_gameset' => !$user->is_gameset]);
        return response()->json(['success' => true, 'is_gameset' => $user->is_gameset]);
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot change your own role.'], 403);
        }
        $user->update(['is_admin' => !$user->is_admin]);
        return response()->json(['success' => true, 'is_admin' => $user->is_admin]);
    }

    /** Deactivate/reactivate — blocks future logins without deleting any data. Reversible. */
    public function toggleUserActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot deactivate your own account.'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json(['success' => true, 'is_active' => $user->is_active]);
    }

    /**
     * Permanent delete — removes the user and (via cascading foreign keys)
     * everything tied to their account: progress, badges, subscriptions,
     * transactions, quests, etc. This cannot be undone. Prefer Deactivate
     * for anything reversible; use this only for genuine removal requests.
     */
    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot delete your own account.'], 403);
        }
        if ($user->is_admin) {
            return response()->json(['error' => 'Remove admin access before deleting this account.'], 403);
        }

        $name = $user->name;

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // Some related table doesn't cascade-delete — fail loudly rather
            // than leave a partially-deleted account. Deactivate is always
            // the safe fallback if this happens.
            return response()->json([
                'error' => "Could not fully delete \"{$name}\" — some related data blocks it (foreign key constraint). Use Deactivate instead, or ask a developer to check the constraint.",
            ], 422);
        }

        return response()->json(['success' => true, 'message' => "\"{$name}\" was permanently deleted."]);
    }

    // ── Password reset ─────────────────────────────────────────────────────

    public function resetPassword(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot reset your own password here.'], 403);
        }

        $tempPassword = Str::random(10);
        $user->update(['password' => Hash::make($tempPassword)]);

        try {
            Mail::raw(
                "Hello {$user->name},\n\nAn admin has reset your PesaQuest password.\n\nTemporary password: {$tempPassword}\n\nPlease log in and change your password immediately.\n\nLogin: " . config('app.url') . "/login\n\nMoski Team",
                fn($m) => $m->to($user->email)->subject('PesaQuest Password Reset')
            );
            $sent = true;
        } catch (\Throwable $e) {
            Log::warning('Admin password reset email failed', ['error' => $e->getMessage()]);
            $sent = false;
        }

        return response()->json([
            'success'  => true,
            'temp_pw'  => $tempPassword,
            'email_sent' => $sent,
        ]);
    }

    // ── Subscriptions ──────────────────────────────────────────────────────

    public function grantSubscription(Request $request, User $user)
    {
        $plans   = SubscriptionPlan::pluck('key')->toArray();
        $data    = $request->validate([
            'plan'        => 'required|in:' . implode(',', $plans),
            'reference'   => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:255',
        ]);

        $plan   = SubscriptionPlan::where('key', $data['plan'])->firstOrFail();
        $endsAt = now()->addMonths($plan->months);

        Subscription::where('user_id', $user->id)->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $sub = Subscription::create([
            'user_id'           => $user->id,
            'plan'              => $data['plan'],
            'plan_id'           => $plan->id,
            'status'            => 'active',
            'starts_at'         => now(),
            'ends_at'           => $endsAt,
            'payment_method'    => 'manual',
            'payment_reference' => $data['reference'] ?? null,
            'approved_by'       => auth()->id(),
        ]);

        $portalUrl = null;

        // For school plans — auto-create school subscription record
        if ($plan->isSchool()) {
            $schoolName = $data['school_name'] ?? ($user->name . '\'s School');
            $school = \App\Models\SchoolSubscription::create([
                'school_name'   => $schoolName,
                'contact_email' => $user->email,
                'seats'         => $plan->seats ?? 30,
                'max_classes'   => $plan->max_classes ?? 3,
                'starts_at'     => now(),
                'ends_at'       => $endsAt,
                'status'        => 'active',
                'portal_token'  => Str::random(48),
                'price_kes'     => $plan->price_kes,
                'created_by'    => $user->id,
            ]);
            $portalUrl = route('school.portal', $school->portal_token);
        }

        // In-game notification
        $notifBody = $plan->isSchool()
            ? "An admin activated your {$plan->name} school subscription. Portal: {$portalUrl}"
            : "An admin activated your {$plan->name} subscription. Enjoy full access!";

        \App\Models\GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'success',
            'icon'    => $plan->isSchool() ? '🏫' : '🎉',
            'title'   => $plan->isSchool() ? 'School Subscription Activated!' : 'Subscription Activated!',
            'body'    => $notifBody,
        ]);

        return response()->json([
            'success'       => true,
            'plan'          => $data['plan'],
            'ends_at_human' => $sub->ends_at->format('d M Y'),
            'portal_url'    => $portalUrl,
        ]);
    }

    public function revokeSubscription(User $user)
    {
        Subscription::where('user_id', $user->id)->where('status', 'active')
            ->update(['status' => 'cancelled']);
        return response()->json(['success' => true]);
    }

    public function approveSubscription(Subscription $subscription)
    {
        if ($subscription->status !== 'pending') {
            return response()->json(['error' => 'Subscription is not pending.'], 422);
        }

        $plan = $subscription->subscriptionPlan
            ?? SubscriptionPlan::where('key', $subscription->plan)->first();

        // Stack behind any subscription already active for this user — same
        // no-lost-days rule as the M-Pesa auto-renewal path.
        $existingActive = $subscription->user?->activeSubscription();
        $startsAt = $existingActive ? $existingActive->ends_at->copy() : now();
        $endsAt   = $startsAt->copy()->addMonths($plan?->months ?? 1);

        $subscription->update([
            'status'      => 'active',
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
            'approved_by' => auth()->id(),
        ]);

        $isStacked = $existingActive !== null;
        \App\Models\GameNotification::create([
            'user_id' => $subscription->user_id,
            'type'    => 'success',
            'icon'    => $isStacked ? '📅' : '✅',
            'title'   => $isStacked ? 'Renewal Approved & Scheduled!' : 'Subscription Approved!',
            'body'    => $isStacked
                ? "Your renewal was approved! Your current plan stays active until {$startsAt->format('d M Y')}, then this one takes over until {$endsAt->format('d M Y')}."
                : "Your subscription has been manually approved by our team. Enjoy full access!",
        ]);

        return response()->json(['success' => true, 'ends_at_human' => $endsAt->format('d M Y'), 'starts_at_human' => $startsAt->format('d M Y'), 'stacked' => $isStacked]);
    }

    /** Freeze a subscription's countdown — e.g. a player reports an issue and shouldn't burn paid days. */
    public function pauseSubscription(Subscription $subscription)
    {
        if (!$subscription->isActive()) {
            return response()->json(['error' => 'Only a currently active subscription can be paused.'], 422);
        }

        $subscription->pause();

        \App\Models\GameNotification::create([
            'user_id' => $subscription->user_id,
            'type'    => 'success',
            'icon'    => '⏸️',
            'title'   => 'Subscription Paused',
            'body'    => 'Your subscription has been paused by our team — no days are being used while paused. It will pick back up right where it left off once resumed.',
        ]);

        return response()->json(['success' => true]);
    }

    /** Resume a paused subscription — ends_at shifts forward by exactly how long it was paused. */
    public function resumeSubscription(Subscription $subscription)
    {
        if (!$subscription->isPaused()) {
            return response()->json(['error' => 'This subscription is not paused.'], 422);
        }

        $subscription->resume();

        \App\Models\GameNotification::create([
            'user_id' => $subscription->user_id,
            'type'    => 'success',
            'icon'    => '▶️',
            'title'   => 'Subscription Resumed',
            'body'    => "Your subscription is active again. New expiry: {$subscription->ends_at->format('d M Y')} — every paused day was added back.",
        ]);

        return response()->json(['success' => true, 'ends_at_human' => $subscription->ends_at->format('d M Y')]);
    }

    // ── Subscription Plans CRUD ────────────────────────────────────────────

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:60',
            'price_kes'   => 'required|integer|min:1',
            'description' => 'nullable|string|max:300',
            'is_active'   => 'boolean',
            'is_featured' => 'boolean',
            'seats'       => 'nullable|integer|min:1|max:5000',
            'max_classes' => 'nullable|integer|min:1|max:100',
        ]);

        $plan->update($data);
        return response()->json(['success' => true, 'plan' => $plan->fresh()]);
    }

    public function createSchoolPlan(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:60',
            'months'      => 'required|integer|min:1|max:60',
            'seats'       => 'required|integer|min:1|max:5000',
            'max_classes' => 'nullable|integer|min:1|max:100',
            'price_kes'   => 'required|integer|min:0',
            'description' => 'nullable|string|max:300',
            'is_featured' => 'boolean',
        ]);

        $key = Str::slug($data['name']) . '-' . $data['seats'] . 'seats';
        // Ensure uniqueness
        $base = $key;
        $i = 2;
        while (SubscriptionPlan::where('key', $key)->exists()) {
            $key = $base . '-' . $i++;
        }

        $plan = SubscriptionPlan::create([
            'key'         => $key,
            'plan_type'   => 'school',
            'name'        => $data['name'],
            'months'      => $data['months'],
            'seats'       => $data['seats'],
            'max_classes' => $data['max_classes'] ?? 3,
            'price_kes'   => $data['price_kes'],
            'description' => $data['description'] ?? null,
            'is_active'   => true,
            'is_featured' => $data['is_featured'] ?? false,
        ]);

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    public function deletePlan(SubscriptionPlan $plan)
    {
        if ($plan->plan_type !== 'school') {
            return response()->json(['error' => 'Only school plans can be deleted.'], 422);
        }

        $plan->delete();
        return response()->json(['success' => true]);
    }

    // ── Settings ───────────────────────────────────────────────────────────

    public function saveSettings(Request $request)
    {
        $group = $request->validate(['group' => 'required|in:smtp,mpesa,game_clock,ai,general,hustle_tips,contact,google_oauth'])['group'];

        $fields = match ($group) {
            'smtp'        => ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'smtp_from_email', 'smtp_from_name'],
            'mpesa'       => ['mpesa_env', 'mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_shortcode', 'mpesa_passkey', 'mpesa_account_ref'],
            'game_clock'  => ['game_clock_real_hours_per_game_week', 'max_catchup_game_days'],
            'ai'          => ['openrouter_api_key', 'ai_model', 'ai_daily_limit', 'ai_agent_icon'],
            'general'     => ['free_for_all', 'cron_configured'],
            'hustle_tips' => ['hustle_tips'],
            'contact'     => ['contact_email', 'contact_whatsapp', 'contact_phone'],
            // Group name matches AppServiceProvider::configureGoogleOAuthFromDatabase()'s
            // Setting::group('google_oauth') lookup exactly — must stay in sync.
            'google_oauth' => ['google_client_id', 'google_client_secret', 'google_oauth_enabled'],
        };

        foreach ($fields as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key), $group);
            }
        }

        return response()->json(['success' => true, 'message' => ucfirst(str_replace('_', ' ', $group)) . ' settings saved.']);
    }

    public function testAi(Request $request)
    {
        $apiKey = Setting::get('openrouter_api_key', '');
        $model  = Setting::get('ai_model', 'meta-llama/llama-3.1-8b-instruct:free');

        if (empty($apiKey)) {
            return response()->json(['error' => 'No API key configured. Save your OpenRouter key first.'], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'HTTP-Referer'  => config('app.url', 'http://localhost'),
                'X-Title'       => 'PesaQuest - pesAI Test',
                'Content-Type'  => 'application/json',
            ])->timeout(20)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model'      => $model,
                'messages'   => [
                    ['role' => 'system', 'content' => 'You are pesAI, a friendly Kenyan financial mentor. Respond in exactly one short sentence.'],
                    ['role' => 'user',   'content' => 'Say hello and confirm you are working.'],
                ],
                'max_tokens' => 80,
            ]);

            if ($response->failed()) {
                $err = $response->json('error.message') ?? $response->body();
                return response()->json(['error' => 'API error: ' . $err], 422);
            }

            $reply = $response->json('choices.0.message.content') ?? 'No reply';
            return response()->json([
                'success' => true,
                'message' => 'pesAI is working!',
                'reply'   => $reply,
                'model'   => $model,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Connection failed: ' . $e->getMessage()], 422);
        }
    }

    public function testSmtp(Request $request)
    {
        $data = $request->validate(['to' => 'required|email']);

        try {
            Mail::raw(
                "This is a test email from PesaQuest Admin panel. Your SMTP is configured correctly!",
                fn($m) => $m->to($data['to'])->subject('PesaQuest SMTP Test ✅')
            );
            return response()->json(['success' => true, 'message' => 'Test email sent to ' . $data['to']]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ── Quest approval ──────────────────────────────────────────────────────

    public function approveQuest(\App\Models\UserQuest $userQuest)
    {
        abort_if($userQuest->isApproved(), 422);

        $userQuest->update([
            'completed_at' => now(),
            'approved_by'  => auth()->id(),
        ]);

        $progress = $userQuest->user->getOrCreateProgress();
        $progress->addPoints($userQuest->quest->xp_reward);

        \App\Models\GameNotification::create([
            'user_id' => $userQuest->user_id,
            'type'    => 'quest',
            'title'   => '🌍 Quest Approved!',
            'body'    => "Your quest \"{$userQuest->quest->title}\" was approved — {$userQuest->quest->xp_reward} XP added!",
            'icon'    => $userQuest->quest->icon ?? '🌍',
            'data'    => ['xp' => $userQuest->quest->xp_reward],
        ]);

        return response()->json(['success' => true]);
    }

    public function pendingQuests()
    {
        $pending = \App\Models\UserQuest::with('user', 'quest')
            ->whereNotNull('submitted_at')
            ->whereNull('completed_at')
            ->orderBy('submitted_at')
            ->get();

        return response()->json($pending);
    }

    // ── School Subscriptions ──────────────────────────────────────────────────

    public function createSchool(Request $request)
    {
        $data = $request->validate([
            'school_name'   => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'seats'         => 'required|integer|min:1|max:2000',
            'max_classes'   => 'nullable|integer|min:1|max:100',
            'months'        => 'required|integer|min:1|max:60',
            'price_kes'     => 'nullable|integer|min:0',
            'notes'         => 'nullable|string|max:500',
        ]);

        $school = SchoolSubscription::create([
            'school_name'   => $data['school_name'],
            'contact_email' => $data['contact_email'],
            'seats'         => $data['seats'],
            'max_classes'   => $data['max_classes'] ?? 3,
            'starts_at'     => now(),
            'ends_at'       => now()->addMonths($data['months']),
            'status'        => 'active',
            'portal_token'  => Str::random(48),
            'price_kes'     => $data['price_kes'] ?? 0,
            'notes'         => $data['notes'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        // The buyer's contact email becomes the school's first teacher account
        // (role=owner) — they accept the invite to unlock the teacher portal
        // and can bring on colleagues from there. Guarded: schools can still
        // be created before the school_teachers migration has run.
        $teacherInviteUrl = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('school_teachers')) {
            $existingUser = User::where('email', $data['contact_email'])->first();
            $owner = \App\Models\SchoolTeacher::create([
                'school_subscription_id' => $school->id,
                'user_id'                => $existingUser?->id,
                'email'                  => $data['contact_email'],
                'role'                   => 'owner',
                'invite_token'           => Str::random(48),
                'status'                 => 'invited',
                'invited_by'             => auth()->id(),
            ]);
            $teacherInviteUrl = route('school.teacher.invite', $owner->invite_token);
        }

        return response()->json([
            'success'    => true,
            'school'     => [
                'id'                   => $school->id,
                'school_name'          => $school->school_name,
                'contact_email'        => $school->contact_email,
                'seats'                => $school->seats,
                'max_classes'          => $school->max_classes,
                'ends_at'              => $school->ends_at->format('d M Y'),
                'status'               => $school->statusLabel(),
                'portal_url'           => route('school.portal', $school->portal_token),
                'teacher_invite_url'   => $teacherInviteUrl,
                'active_members_count' => 0,
                'price_kes'            => $school->price_kes,
            ],
        ]);
    }

    public function updateSchool(Request $request, SchoolSubscription $school)
    {
        $data = $request->validate([
            'school_name'   => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'seats'         => 'required|integer|min:1|max:2000',
            'max_classes'   => 'nullable|integer|min:1|max:100',
            'status'        => 'required|in:active,suspended',
            'notes'         => 'nullable|string|max:500',
        ]);

        $school->update($data);

        return response()->json(['success' => true, 'status' => $school->statusLabel()]);
    }

    public function deleteSchool(SchoolSubscription $school)
    {
        $school->members()->delete();
        $school->delete();
        return response()->json(['success' => true]);
    }

    // ── Financial Crisis Events ────────────────────────────────────────────

    public function createCrisis(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:80',
            'description'   => 'required|string|max:400',
            'icon'          => 'required|string|max:10',
            'effect_type'   => 'required|in:investment_drop,asset_drop,balance_drain,salary_cut',
            'effect_amount' => 'required|numeric|min:0.1|max:100',
            'is_percentage' => 'boolean',
            'warning_at'    => 'required|date',
            'active_from'   => 'required|date|after:warning_at',
            'active_until'  => 'required|date|after:active_from',
        ]);

        $data['created_by']    = auth()->id();
        $data['is_percentage'] = $request->boolean('is_percentage', true);

        $crisis = \App\Models\FinancialCrisis::create($data);

        return response()->json(['success' => true, 'crisis' => $crisis]);
    }

    public function deleteCrisis(\App\Models\FinancialCrisis $crisis)
    {
        $crisis->delete();
        return response()->json(['success' => true]);
    }

    // ── Coupon Management ─────────────────────────────────────────────────────

    public function createCoupon(Request $request)
    {
        $data = $this->validateCoupon($request);
        $data['code'] = strtoupper(trim($data['code']));

        if (\App\Models\Coupon::where('code', $data['code'])->exists()) {
            return response()->json(['error' => 'A coupon with that code already exists.'], 422);
        }

        $coupon = \App\Models\Coupon::create($data);

        return response()->json(['success' => true, 'coupon' => $coupon]);
    }

    public function updateCoupon(Request $request, \App\Models\Coupon $coupon)
    {
        $data = $this->validateCoupon($request);
        $data['code'] = strtoupper(trim($data['code']));

        $clash = \App\Models\Coupon::where('code', $data['code'])->where('id', '!=', $coupon->id)->exists();
        if ($clash) {
            return response()->json(['error' => 'Another coupon already uses that code.'], 422);
        }

        $coupon->update($data);

        return response()->json(['success' => true, 'coupon' => $coupon->fresh()]);
    }

    /** Pause / resume a coupon. */
    public function toggleCoupon(\App\Models\Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return response()->json(['success' => true, 'is_active' => $coupon->is_active]);
    }

    public function deleteCoupon(\App\Models\Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(['success' => true]);
    }

    private function validateCoupon(Request $request): array
    {
        $data = $request->validate([
            'code'            => 'required|string|min:3|max:30|regex:/^[A-Za-z0-9\-_]+$/',
            'type'            => 'required|in:percent,fixed',
            'value'           => 'required|integer|min:1',
            'max_redemptions' => 'nullable|integer|min:1',
            'plan_id'         => 'nullable|integer|exists:subscription_plans,id',
            'expires_at'      => 'nullable|date',
            'note'            => 'nullable|string|max:150',
        ], [
            'code.regex' => 'Codes may only contain letters, numbers, dashes and underscores.',
        ]);

        if ($data['type'] === 'percent' && $data['value'] > 100) {
            abort(response()->json(['error' => 'Percent discount cannot exceed 100.'], 422));
        }

        return $data;
    }

    // ── NPC Management ────────────────────────────────────────────────────────

    public function storeNpc(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'nickname'             => 'nullable|string|max:60',
            'role'                 => 'required|in:friend,boss,parent,landlord,investor,relative,colleague',
            'cover_color'          => 'nullable|string|max:20',
            'avatar_url'           => 'nullable|string|max:500',
            'description'          => 'nullable|string',
            'personality'          => 'nullable|string|max:255',
            'initial_relationship' => 'nullable|integer|min:0|max:100',
        ]);
        $data['created_by'] = auth()->id();
        $npc = \App\Models\Npc::create($data);
        return response()->json(['success' => true, 'message' => 'NPC created.', 'id' => $npc->id]);
    }

    public function toggleNpc(\App\Models\Npc $npc)
    {
        $npc->update(['is_active' => !$npc->is_active]);
        return response()->json(['success' => true]);
    }

    // ── Life Decision Management ───────────────────────────────────────────────

    public function storeDecision(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'body'        => 'required|string',
            'npc_id'      => 'nullable|exists:npcs,id',
            'category'    => 'required|in:social,career,family,emergency,opportunity,housing,market',
            'icon'        => 'nullable|string|max:10',
            'weight'      => 'nullable|integer|min:1|max:30',
            'min_tick'    => 'nullable|integer|min:0',
            'max_tick'    => 'nullable|integer|min:0',
            'image_url'   => 'nullable|string|max:500',
            'is_repeatable' => 'nullable|boolean',
            'choices'     => 'required|array|min:2|max:3',
            'choices.*.label'            => 'required|string|max:100',
            'choices.*.description'      => 'nullable|string|max:300',
            'choices.*.outcome_text'     => 'required|string',
            'choices.*.financial_lesson' => 'nullable|string',
            'choices.*.balance_delta'    => 'nullable|integer',
            'choices.*.credit_score_delta' => 'nullable|integer',
            'choices.*.relationship_delta' => 'nullable|integer',
            'choices.*.xp_delta'         => 'nullable|integer',
        ]);

        $choicesData = $data['choices'];
        unset($data['choices']);
        $data['created_by'] = auth()->id();
        $data['npc_id'] = $data['npc_id'] ?: null;
        $data['max_tick'] = $data['max_tick'] ?: null;

        $decision = \App\Models\LifeDecision::create($data);

        foreach ($choicesData as $idx => $c) {
            $decision->choices()->create([
                'sort_order'          => $idx,
                'label'               => $c['label'],
                'description'         => $c['description'] ?? null,
                'outcome_text'        => $c['outcome_text'],
                'financial_lesson'    => $c['financial_lesson'] ?? null,
                'balance_delta'       => $c['balance_delta'] ?? 0,
                'credit_score_delta'  => $c['credit_score_delta'] ?? 0,
                'relationship_delta'  => $c['relationship_delta'] ?? 0,
                'xp_delta'            => $c['xp_delta'] ?? 10,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Decision created.', 'id' => $decision->id]);
    }

    public function toggleDecision(\App\Models\LifeDecision $decision)
    {
        $decision->update(['is_active' => !$decision->is_active]);
        return response()->json(['success' => true]);
    }

    // ── Marketplace Asset CRUD ────────────────────────────────────────────────

    public function assets()
    {
        $assets = Asset::orderBy('category')->orderBy('tier')->orderBy('base_price')->get();
        return view('admin.assets.index', compact('assets'));
    }

    public function assetCreate()
    {
        return view('admin.assets.create', ['asset' => new Asset()]);
    }

    public function assetStore(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:120',
            'slug'             => 'required|string|max:80|unique:assets,slug',
            'category'         => 'required|in:vehicle,property,business,investment,gadget,fixed_income',
            'tier'             => 'required|integer|min:1|max:5',
            'age_group'        => 'nullable|string|max:20',
            'base_price'       => 'required|integer|min:0',
            'monthly_income'   => 'nullable|integer|min:0',
            'monthly_cost'     => 'nullable|integer|min:0',
            'icon'             => 'nullable|string|max:10',
            'image_url'        => 'nullable|url|max:500',
            'description'      => 'nullable|string|max:500',
            'educational_note' => 'nullable|string|max:500',
            'max_per_player'   => 'nullable|integer|min:1',
            'creates_bill_slug'=> 'nullable|string|max:80',
            'is_active'        => 'boolean',
            'is_luxury'        => 'boolean',
            'badge'            => 'nullable|string|in:popular,trending,new,stable,risky',
            'featured_section' => 'nullable|string|in:starter_moves,serious_money,high_growth,dividend_builders,lifestyle_upgrades',
        ]);
        $data['is_active']        = $request->boolean('is_active', true);
        $data['is_luxury']        = $request->boolean('is_luxury', false);
        $data['badge']            = $request->input('badge') ?: null;
        $data['featured_section'] = $request->input('featured_section') ?: null;
        Asset::create($data);
        return redirect()->route('admin.assets')->with('success', 'Asset created.');
    }

    public function assetEdit(Asset $asset)
    {
        return view('admin.assets.create', compact('asset'));
    }

    public function assetUpdate(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:120',
            'slug'             => 'required|string|max:80|unique:assets,slug,' . $asset->id,
            'category'         => 'required|in:vehicle,property,business,investment,gadget,fixed_income',
            'tier'             => 'required|integer|min:1|max:5',
            'age_group'        => 'nullable|string|max:20',
            'base_price'       => 'required|integer|min:0',
            'monthly_income'   => 'nullable|integer|min:0',
            'monthly_cost'     => 'nullable|integer|min:0',
            'icon'             => 'nullable|string|max:10',
            'image_url'        => 'nullable|url|max:500',
            'description'      => 'nullable|string|max:500',
            'educational_note' => 'nullable|string|max:500',
            'max_per_player'   => 'nullable|integer|min:1',
            'creates_bill_slug'=> 'nullable|string|max:80',
            'is_active'        => 'boolean',
            'is_luxury'        => 'boolean',
            'badge'            => 'nullable|string|in:popular,trending,new,stable,risky',
            'featured_section' => 'nullable|string|in:starter_moves,serious_money,high_growth,dividend_builders,lifestyle_upgrades',
        ]);
        $data['is_active']        = $request->boolean('is_active', true);
        $data['is_luxury']        = $request->boolean('is_luxury', false);
        $data['badge']            = $request->input('badge') ?: null;
        $data['featured_section'] = $request->input('featured_section') ?: null;
        $asset->update($data);
        return redirect()->route('admin.assets')->with('success', 'Asset updated.');
    }

    public function assetDestroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('admin.assets')->with('success', 'Asset removed.');
    }

    // ── Artisan Runner ─────────────────────────────────────────────────────────

    public function runArtisan(Request $request)
    {
        // Maps UI key → [artisan command, args]
        $allowed = [
            'cache:clear'         => ['cmd' => 'cache:clear',    'args' => []],
            'config:clear'        => ['cmd' => 'config:clear',   'args' => []],
            'config:cache'        => ['cmd' => 'config:cache',   'args' => []],
            'route:clear'         => ['cmd' => 'route:clear',    'args' => []],
            'route:cache'         => ['cmd' => 'route:cache',    'args' => []],
            'view:clear'          => ['cmd' => 'view:clear',     'args' => []],
            'optimize'            => ['cmd' => 'optimize',       'args' => []],
            'optimize:clear'      => ['cmd' => 'optimize:clear', 'args' => []],
            'migrate'             => ['cmd' => 'migrate',        'args' => ['--force' => true]],
            'migrate:status'      => ['cmd' => 'migrate:status', 'args' => []],
            'storage:link'           => ['cmd' => 'storage:link',             'args' => ['--force' => true]],
            'fix:storage-images'     => ['cmd' => 'app:fix-storage-images',  'args' => []],
            'queue:restart'          => ['cmd' => 'queue:restart',           'args' => []],
            // Seeders (safe — use updateOrCreate, won't wipe data)
            'seed:brand-gadgets'  => ['cmd' => 'db:seed', 'args' => ['--class' => 'BrandGadgetSeeder',       '--force' => true]],
            'seed:npcs'           => ['cmd' => 'db:seed', 'args' => ['--class' => 'NpcSeeder',               '--force' => true]],
            'seed:life-decisions' => ['cmd' => 'db:seed', 'args' => ['--class' => 'LifeDecisionSeeder',       '--force' => true]],
            'seed:life-events'    => ['cmd' => 'db:seed', 'args' => ['--class' => 'LifeEventSeeder',          '--force' => true]],
            'seed:scenarios-bulk' => ['cmd' => 'db:seed', 'args' => ['--class' => 'BulkScenarioSeeder',       '--force' => true]],
            'seed:scenarios-adult'=> ['cmd' => 'db:seed', 'args' => ['--class' => 'AdultScenariosSeeder',     '--force' => true]],
            'seed:asset-events'   => ['cmd' => 'db:seed', 'args' => ['--class' => 'AssetLifeEventsSeeder',    '--force' => true]],
            'seed:content-l123'   => ['cmd' => 'db:seed', 'args' => ['--class' => 'Level123ContentSeeder',    '--force' => true]],
            'seed:market-events'  => ['cmd' => 'db:seed', 'args' => ['--class' => 'MarketEventSeeder',        '--force' => true]],
            'seed:career-events'  => ['cmd' => 'db:seed', 'args' => ['--class' => 'CareerEventSeeder',        '--force' => true]],
            'seed:missions'       => ['cmd' => 'db:seed', 'args' => ['--class' => 'MissionSeeder',            '--force' => true]],
            'seed:fun-world'      => ['cmd' => 'db:seed', 'args' => ['--class' => 'FunWorldActivitySeeder',   '--force' => true]],
            'seed:dreams'         => ['cmd' => 'db:seed', 'args' => ['--class' => 'DreamSeeder',              '--force' => true]],
            'seed:challenge-templates' => ['cmd' => 'db:seed', 'args' => ['--class' => 'ChallengeTemplateSeeder', '--force' => true]],
            'seed:share-news'     => ['cmd' => 'db:seed', 'args' => ['--class' => 'ShareNewsTemplateSeeder',     '--force' => true]],
            // Destructive seeders (truncate first — danger!)
            'seed:assets'         => ['cmd' => 'db:seed', 'args' => ['--class' => 'AssetSeeder',             '--force' => true]],
            'seed:bills'          => ['cmd' => 'db:seed', 'args' => ['--class' => 'BillSeeder',              '--force' => true]],
            'seed:nodes'          => ['cmd' => 'db:seed', 'args' => ['--class' => 'NodeSeeder',              '--force' => true]],
            // Everything at once — runs DatabaseSeeder (all registered seeders)
            'seed:all'            => ['cmd' => 'db:seed', 'args' => ['--force' => true]],
            // Crisis engine — send due warnings + apply active effects right now
            'crises:process'      => ['cmd' => 'game:process-crises', 'args' => []],
            // Teacher digest — run manually if server cron isn't configured
            'teachers:digest'     => ['cmd' => 'teachers:weekly-digest', 'args' => []],
            // Predictive push check — normally every 30min via cron; manual trigger for testing
            'push:predictive'     => ['cmd' => 'push:predictive-check', 'args' => []],
        ];

        $key = $request->input('command');

        if (!array_key_exists($key, $allowed)) {
            return response()->json(['success' => false, 'output' => 'Command not allowed.'], 422);
        }

        $entry = $allowed[$key];

        try {
            $exitCode = Artisan::call($entry['cmd'], $entry['args']);
            $output   = trim(Artisan::output()) ?: '(done — no output)';
            return response()->json(['success' => $exitCode === 0, 'output' => $output, 'exit_code' => $exitCode]);
        } catch (\Throwable $e) {
            Log::error("Admin artisan [{$key}] failed: " . $e->getMessage());
            return response()->json(['success' => false, 'output' => $e->getMessage()], 500);
        }
    }
}
