<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Bill;
use App\Models\GameNotification;
use App\Models\InvestmentDeal;
use App\Models\LifeEvent;
use App\Models\PlayerAsset;
use App\Models\PlayerBill;
use App\Models\PlayerCityJob;
use App\Models\PlayerDeal;
use App\Models\PlayerLifeEvent;
use App\Models\PlayerLoan;
use App\Models\StockPriceHistory;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LifeSimulator
{
    // Catch-up ceiling is now admin-configurable — see GameClock::maxCatchupTicks()
    // (Setting: max_catchup_game_days, default 60, edited in GameSet Hub → Game Clock Speed).
    const MAX_EVENTS_SESSION = 3;
    const TICKS_PER_YEAR     = 365;

    public function __construct(private GameClock $clock) {}

    public function processLogin(User $user): array
    {
        // Prevent double-processing within the same HTTP request (e.g. Dashboard → World)
        $reqKey = 'life_sim_done_' . $user->id;
        if (request()->attributes->has($reqKey)) {
            return request()->attributes->get($reqKey);
        }

        $progress = $user->getOrCreateProgress();

        // Server-wide crisis engine — shared hosting has no cron, so warnings
        // and effects are also processed opportunistically on login (idempotent).
        app(CrisisService::class)->processIfPending();

        // Birthday gift + automatic age-group transition (private DOB; idempotent per year/day)
        try {
            app(BirthdayService::class)->check($user);
        } catch (\Throwable $e) {
            \Log::warning('Birthday check failed: ' . $e->getMessage());
        }

        // Always ensure bills exist before anything else (chapter-gated — see assignEligibleBills)
        $this->assignEligibleBills($user, $progress);

        // First-ever login: seed life_chapter based on starting net worth (0 = student)
        if (!$progress->last_tick_at) {
            $progress->update([
                'last_tick_at' => now(),
                'life_chapter' => 'student',
            ]);
            return [];
        }

        $events = [];

        // Hitting zero balance damages creditworthiness (checked before ticks run,
        // at most once per 30 game days)
        if (($progress->balance ?? 0) <= 0) {
            $lastHit = GameNotification::where('user_id', $user->id)
                ->where('type', 'credit_change')
                ->whereJsonContains('data->kind', 'zero_balance')
                ->latest()
                ->first();
            $lastTick = (int) ($lastHit->data['tick'] ?? -999);
            if (($progress->tick_count ?? 0) - $lastTick >= 30) {
                $progress->adjustCreditScoreWithLog(-10, 'Account balance hit zero', ['kind' => 'zero_balance', 'tick' => $progress->tick_count ?? 0]);
                $progress->save();
            }
        }

        // Daily login bonus — awarded once per calendar day, regardless of tick count
        $this->checkDailyLoginBonus($user, $progress, $events);

        // How many game days a single login ever catches up on. GameClock::maxCatchupTicks()
        // is the single admin-set ceiling (GameSet Hub → Game Clock Speed → Max Catch-up,
        // in plain game days e.g. 90 = 3 game months) — it applies to every player,
        // premium included. Free accounts get an EXTRA, tighter throttle on top of it
        // (a separate, deliberate monetization pace-limit, unrelated to the ceiling).
        $gate         = app(PlanGate::class);
        $isPremium    = $gate->isPremium($user);
        $adminCeiling = $this->clock->maxCatchupTicks();
        $maxCatchup   = $isPremium
            ? $adminCeiling
            : min($adminCeiling, max(1, $gate->limit($user, 'catchup_ticks') ?: $adminCeiling));

        // Periodic "consider subscribing" nudge for free players (admin-tunable)
        if (!$isPremium) {
            $this->maybeNudgeSubscription($user);
        }

        $rawTicks = $this->clock->ticksSince($progress->last_tick_at);
        if ($rawTicks < 1) {
            // No ticks, but still surface the bonus if one was awarded today
            if (!empty($events)) {
                $progress->save();
                return [
                    'ticks'        => 0,
                    'capped'       => false,
                    'game_time'    => 'your daily check-in',
                    'events'       => $events,
                    'net_worth'    => $progress->net_worth_cache,
                    'balance'      => $progress->balance,
                    'chapter'      => $progress->chapterName(),
                    'chapter_icon' => $progress->chapterIcon(),
                    // Daily bonus self-limits to once per real day — always worth showing
                    'show_wywa'    => true,
                ];
            }
            return [];
        }

        $ticks  = min($rawTicks, $maxCatchup);
        $capped = $rawTicks > $maxCatchup;

        // Gentle upsell: free players see what a full (premium) simulation would have covered
        if ($capped && !$isPremium && $rawTicks > $maxCatchup) {
            $missedDays = min($rawTicks, $adminCeiling) - $maxCatchup;
            if ($missedDays > 0) {
                $events[] = [
                    'icon'        => '⏳',
                    'type'        => 'plan_upsell',
                    'text'        => "{$missedDays} more game days were waiting — Premium simulates them all",
                    'sub'         => 'Free accounts advance up to ' . $maxCatchup . ' game days per visit. Subscribe so your money never sleeps.',
                    'delta'       => 0,
                    'is_positive' => true,
                ];
            }
        }

        // Salary comes ONLY from real Pesa City jobs (settleJobSalaries). The
        // legacy career_income_rate payer is retired — it generated "payslip
        // ready / report to work" notifications for players with NO job.
        // Clear any phantom pending pay + notifications it left behind.
        if (Schema::hasColumn('user_progress', 'pending_salary') && ($progress->pending_salary ?? 0) > 0) {
            $progress->pending_salary = 0;
            $progress->save();

            $hasRealJob = Schema::hasTable('player_city_jobs')
                && PlayerCityJob::where('user_id', $user->id)->where('status', 'employed')->exists();
            if (!$hasRealJob) {
                GameNotification::where('user_id', $user->id)
                    ->whereIn('type', ['salary_ready', 'wages_lost'])
                    ->delete();
            }
        }

        DB::transaction(function () use ($user, $progress, $ticks, &$events) {
            $startTick = $progress->tick_count;

            for ($t = 0; $t < $ticks; $t++) {
                $this->processTick($progress, $events);
            }

            // Settle all bills that fell due during this catch-up period
            $this->settleBills($user, $progress, $startTick, $events);

            // Apply asset income, costs, and value drift
            $this->settleAssets($user, $progress, $ticks, $events);

            // Resolve matured investment deals
            $this->settleDeals($user, $progress, $events);

            // Process loan payments (compound + auto-deduct)
            $this->settleLoans($user, $progress, $ticks, $events);

            $this->settleFriendLoans($user, $progress, $events);

            // Pay bank interest into savings schemes (monthly, compounding)
            $this->settleSavingsInterest($user, $progress, $events);

            // Pay Pesa City job salaries
            $this->settleJobSalaries($user, $progress, $ticks, $events);

            // Behavioural credit signals (savings habit, assets, job loyalty)
            $this->checkCreditSignals($user, $progress, $events);

            // Roll for random life events (Phase 5)
            $this->rollLifeEvents($user, $progress, $ticks, $events);

            // Record asset value snapshots for charts (Phase 4)
            $this->recordStockPrices($user, $progress);

            $progress->last_tick_at = now();
            $progress->recalculateNetWorth();

            // Advance chapter based on new net worth
            $this->updateChapterFromNetWorth($progress, $events);

            // Pick up any bills the new (or unchanged) chapter now qualifies for
            $this->assignEligibleBills($user, $progress, $events);

            $progress->save();
        });

        $gameTimeLabel = $this->clock->formatTicks($ticks);

        if (!empty($events)) {
            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'life_sim',
                'title'   => "Life advanced: {$gameTimeLabel}",
                'body'    => count($events) . ' thing(s) happened while you were away.',
                'icon'    => '🌍',
                'data'    => ['ticks' => $ticks, 'events' => array_slice($events, 0, 8)],
            ]);

            // Individual contextual notifications for salary and missed bills
            foreach ($events as $ev) {
                if (($ev['type'] ?? '') === 'salary_ready') {
                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'salary_ready',
                        'title'   => '🧾 ' . ($ev['text'] ?? 'Payslip ready'),
                        'body'    => $ev['sub'] ?? 'Report to Work to collect your pay.',
                        'icon'    => '🧾',
                        'data'    => ['text' => $ev['text'] ?? ''],
                    ]);
                } elseif (($ev['type'] ?? '') === 'job_warning') {
                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'job_warning',
                        'title'   => '🚨 ' . ($ev['text'] ?? 'Final notice from your employer'),
                        'body'    => $ev['sub'] ?? 'Report to Work within the next game month or you will be dismissed.',
                        'icon'    => '🚨',
                        'data'    => ['text' => $ev['text'] ?? ''],
                    ]);
                } elseif (($ev['type'] ?? '') === 'job_dismissed') {
                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'job_dismissed',
                        'title'   => '📮 ' . ($ev['text'] ?? 'You were dismissed'),
                        'body'    => $ev['sub'] ?? 'You never reported to work after the final notice. Your earned pay is still collectible.',
                        'icon'    => '📮',
                        'data'    => ['text' => $ev['text'] ?? ''],
                    ]);
                } elseif (($ev['type'] ?? '') === 'bill_missed') {
                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'bill_missed',
                        'title'   => '⚠️ ' . ($ev['text'] ?? 'Bill Missed'),
                        'body'    => ($ev['sub'] ?? 'Pay this bill to protect your credit score.'),
                        'icon'    => '⚠️',
                        'data'    => ['text' => $ev['text'] ?? ''],
                    ]);
                } elseif (($ev['type'] ?? '') === 'chapter_unlock') {
                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'chapter_unlock',
                        'title'   => $ev['icon'] . ' ' . ($ev['text'] ?? 'New Chapter!'),
                        'body'    => $ev['sub'] ?? '',
                        'icon'    => $ev['icon'] ?? '🌟',
                        'data'    => ['chapter' => $progress->life_chapter],
                    ]);
                }
            }
        }

        // Warn if any bill is due within 10 ticks (proactive nudge)
        $this->checkBillsDueSoon($user, $progress);

        $result = [
            'ticks'       => $ticks,
            'capped'      => $capped,
            'game_time'   => $gameTimeLabel,
            'events'      => $events,
            'net_worth'   => $progress->net_worth_cache,
            'balance'     => $progress->balance,
            'chapter'     => $progress->chapterName(),
            'chapter_icon'=> $progress->chapterIcon(),
            'show_wywa'   => $this->shouldShowWywa($ticks, $events),
        ];
        request()->attributes->set($reqKey, $result);
        return $result;
    }

    /**
     * The "While You Were Away" popup only interrupts when it's worth it:
     *  · at least `wywa_min_ticks` game days passed (default 7 — a game week), OR
     *  · something urgent happened (overdue bill, payday, employer final
     *    notice or dismissal, new chapter, crisis, matured deal) — urgent
     *    news always shows.
     * After showing, it stays quiet for `wywa_cooldown_minutes` real minutes
     * so quick tab-hops don't re-trigger it. The simulation itself ALWAYS
     * runs — this only gates the popup.
     */
    private function shouldShowWywa(int $ticks, array $events): bool
    {
        $minTicks    = max(1, (int) \App\Models\Setting::get('wywa_min_ticks', 7));
        $cooldownMin = (int) \App\Models\Setting::get('wywa_cooldown_minutes', 45);

        $urgentTypes  = ['bill_missed', 'salary_ready', 'job_warning', 'job_dismissed', 'chapter_unlock', 'crisis', 'deal_matured', 'loan_missed', 'loan_cleared', 'friend_loan_default', 'friend_loan_settled'];
        $hasUrgent    = collect($events)->contains(fn ($e) =>
            in_array($e['type'] ?? '', $urgentTypes, true) || !empty($e['is_milestone'])
        );

        if (!$hasUrgent && $ticks < $minTicks) return false;

        try {
            if (!$hasUrgent && $cooldownMin > 0) {
                $lastShown = (int) session('wywa_shown_at', 0);
                if ($lastShown && (time() - $lastShown) < $cooldownMin * 60) return false;
            }
            session(['wywa_shown_at' => time()]);
        } catch (\Throwable $e) {
            // No session (console context) — just show
        }

        return true;
    }

    // ── Subscription nudge (free players) ─────────────────────────────────────

    /**
     * Every `upsell_nag_days` real days, unsubscribed players get one friendly
     * bell notification about what Premium unlocks. Admin-tunable via Settings:
     * upsell_nag_enabled ('1'/'0') and upsell_nag_days (real days between nags).
     */
    private function maybeNudgeSubscription(User $user): void
    {
        if (\App\Models\Setting::get('upsell_nag_enabled', '1') !== '1') return;

        $everyDays = max(1, (int) \App\Models\Setting::get('upsell_nag_days', 3));

        $recent = GameNotification::where('user_id', $user->id)
            ->where('type', 'subscribe_nudge')
            ->where('created_at', '>=', now()->subDays($everyDays))
            ->exists();
        if ($recent) return;

        $nudges = [
            ['icon' => '⏩', 'title' => 'Your city moves faster with Premium', 'body' => 'Free accounts simulate a few game days per visit — Premium simulates them ALL, so your salary, savings interest and assets never wait for you.'],
            ['icon' => '🏘️', 'title' => 'Room to grow your empire',            'body' => 'Premium removes the caps on assets, savings goals and investment deals. Build the portfolio your hustle deserves.'],
            ['icon' => '🤖', 'title' => 'Mama Pesa misses you',                 'body' => 'Premium unlocks unlimited questions to your AI money coach — ask anything, any time.'],
            ['icon' => '🎡', 'title' => 'More fun, more mood',                  'body' => 'Premium removes the Fun World monthly limit. A happy hustler earns 10% more at work!'],
            ['icon' => '💚', 'title' => 'Support the mission',                  'body' => 'Your subscription keeps PesaQuest free to start for thousands of young Kenyans learning money skills.'],
        ];
        $pick = $nudges[array_rand($nudges)];

        GameNotification::create([
            'user_id' => $user->id,
            'type'    => 'subscribe_nudge',
            'title'   => $pick['icon'] . ' ' . $pick['title'],
            'body'    => $pick['body'] . ' Tap to see plans.',
            'icon'    => $pick['icon'],
            'data'    => ['url' => route('subscribe.index')],
        ]);
    }

    // ── Daily login bonus ─────────────────────────────────────────────────────

    private function checkDailyLoginBonus(User $user, UserProgress $progress, array &$events): void
    {
        // Uses the existing `last_bonus_at` datetime column — no migration needed.
        // "Today" is judged in the players' local timezone (UTC days roll over
        // at 3am Nairobi time, which double-blocked or double-awarded edges).
        $tz        = 'Africa/Nairobi';
        $lastBonus = $progress->last_bonus_at;
        if ($lastBonus && $lastBonus->copy()->tz($tz)->isSameDay(now($tz))) {
            return; // Already awarded today
        }

        // The daily bonus IS the daily check-in — make sure it clocks the login
        // streak too, whichever page triggered the simulator (world/life/dashboard).
        try {
            $user->streak()->firstOrCreate([])->recordActivity();
        } catch (\Throwable $e) {
            // streak table missing shouldn't block the bonus
        }

        $level   = max(1, $progress->level ?? 1);
        $bonusXp = $level * 25;

        $progress->points_total = ($progress->points_total ?? 0) + $bonusXp;
        $progress->level        = $progress->calculateLevel();
        $progress->last_bonus_at = now();

        $events[] = [
            'icon'        => '🎁',
            'type'        => 'daily_bonus',
            'text'        => "Daily login bonus — +{$bonusXp} XP!",
            'sub'         => "Level {$level} reward · Consistency is the real asset",
            'delta'       => 0,
            'is_positive' => true,
            'edu'         => 'Showing up every day is a financial habit. Small daily contributions — whether of money, time, or attention — compound into wealth over time. In this game, money is earned by working, investing and playing smart — not by logging in.',
        ];
    }

    // ── Tick processor ────────────────────────────────────────────────────────

    private function processTick(UserProgress $progress, array &$events): void
    {
        $progress->incrementTick();
        $tick = $progress->tick_count;

        // Mood decays each game day, flooring at 30 (baseline — never miserable)
        $oldMood = $progress->mood ?? 70;
        if ($oldMood > 30) {
            $progress->mood = max(30, $oldMood - 1);

            // First crossing below 50: nudge the player toward Fun World
            if ($oldMood >= 50 && $progress->mood < 50) {
                GameNotification::create([
                    'user_id' => $progress->user_id,
                    'type'    => 'mood_low',
                    'title'   => '😔 Your mood is getting low',
                    'body'    => 'Your mood is low — visit Fun World to recharge. Low mood reduces your work income.',
                    'icon'    => '😔',
                    'data'    => ['mood' => $progress->mood, 'tick' => $tick],
                ]);
            }
        }

        // NOTE: salaries are handled exclusively by settleJobSalaries() from
        // real Pesa City jobs — there is no per-tick salary logic anymore.
    }

    // ── Net-worth-based chapter advancement ───────────────────────────────────

    private function updateChapterFromNetWorth(UserProgress $progress, array &$events): void
    {
        $newChapter = UserProgress::chapterFromNetWorth((int)$progress->net_worth_cache);
        $oldChapter = $progress->life_chapter ?? 'student';

        if ($newChapter !== $oldChapter) {
            $order    = array_column(UserProgress::chapters(), 'key');
            $advanced = array_search($newChapter, $order) > array_search($oldChapter, $order);

            $progress->life_chapter = $newChapter;
            $events[] = [
                'icon'         => $progress->chapterIcon(),
                'type'         => 'chapter_unlock',
                'text'         => 'New Life Chapter: ' . $progress->chapterName(),
                'sub'          => $progress->chapterTagline(),
                'delta'        => 0,
                'is_milestone' => true,
            ];

            // Crossing a net worth milestone upward strengthens creditworthiness
            if ($advanced) {
                $progress->adjustCreditScoreWithLog(+15, 'Net worth milestone reached: ' . $progress->chapterName(), ['kind' => 'chapter_milestone', 'chapter' => $newChapter]);
            }
        }
    }

    // ── Behavioural credit score signals (Phase 20) ───────────────────────────

    /**
     * Bank interest on savings schemes — accrues per 30 game ticks (1 game month),
     * compounding on the scheme balance. Rate is admin-tunable via the
     * `savings_interest_annual` setting (% per year, default 8 — Kenyan bank range).
     */
    private function settleSavingsInterest(User $user, UserProgress $progress, array &$events): void
    {
        if (!Schema::hasTable('savings_schemes') || !Schema::hasColumn('savings_schemes', 'interest_earned')) return;

        $annualRate  = (float) \App\Models\Setting::get('savings_interest_annual', 8);
        $monthlyRate = $annualRate / 100 / 12;
        if ($monthlyRate <= 0) return;

        $currentTick = (int) ($progress->tick_count ?? 0);

        $schemes = \App\Models\SavingsScheme::where('user_id', $user->id)
            ->where('is_archived', false)
            ->where('current_amount', '>', 0)
            ->get();

        foreach ($schemes as $scheme) {
            $lastTick = $scheme->last_interest_tick ?? $currentTick;
            $months   = intdiv(max(0, $currentTick - $lastTick), 30);
            if ($months < 1) {
                if ($scheme->last_interest_tick === null) $scheme->update(['last_interest_tick' => $currentTick]);
                continue;
            }

            // Compound monthly on the running balance
            $interest = (int) floor($scheme->current_amount * (pow(1 + $monthlyRate, $months) - 1));

            $scheme->last_interest_tick = $lastTick + $months * 30;

            if ($interest > 0) {
                $scheme->current_amount  += $interest;
                $scheme->interest_earned = ($scheme->interest_earned ?? 0) + $interest;

                \App\Models\SavingsDeposit::create([
                    'scheme_id' => $scheme->id,
                    'amount'    => $interest,
                    'type'      => 'interest',
                    'note'      => "Bank interest ({$annualRate}% p.a.)",
                ]);

                $events[] = [
                    'icon'        => '🏦',
                    'type'        => 'savings_interest',
                    'text'        => "Interest earned on \"{$scheme->name}\"",
                    'sub'         => 'Ksh ' . number_format($interest) . " for {$months} game month(s) at {$annualRate}% p.a. — your money is working while you sleep.",
                    'delta'       => 0, // stays inside the scheme, not the wallet
                    'is_positive' => true,
                    'edu'         => 'Compound interest pays interest on your interest. The earlier you save, the harder it works.',
                ];
            }

            $scheme->save();
        }
    }

    private function checkCreditSignals(User $user, UserProgress $progress, array &$events): void
    {
        $tick = $progress->tick_count ?? 0;

        // 1) Savings habit: +10 per 30-tick window a scheme is held with money in it
        if (Schema::hasTable('savings_schemes')) {
            $schemes = \App\Models\SavingsScheme::where('user_id', $user->id)
                ->where('is_archived', false)
                ->where('current_amount', '>', 0)
                ->get();

            foreach ($schemes as $scheme) {
                $windows = intdiv($this->clock->ticksSince($scheme->created_at), 30);
                if ($windows < 1) continue;

                $awarded = GameNotification::where('user_id', $user->id)
                    ->where('type', 'credit_change')
                    ->whereJsonContains('data->kind', 'savings_habit')
                    ->whereJsonContains('data->scheme_id', $scheme->id)
                    ->count();

                if ($awarded < $windows) {
                    // Award at most one window per login to avoid burst inflation
                    $progress->adjustCreditScoreWithLog(+10, "Savings discipline: \"{$scheme->name}\" held 30+ game days", ['kind' => 'savings_habit', 'scheme_id' => $scheme->id]);
                    $events[] = [
                        'icon' => '🏦', 'type' => 'credit_up',
                        'text' => 'Credit score +10 — consistent saver',
                        'sub'  => "\"{$scheme->name}\" maintained for 30+ game days without withdrawing.",
                        'delta' => 0, 'is_positive' => true,
                        'edu'  => 'Lenders love a savings habit. Regular, untouched savings prove you can manage money.',
                    ];
                }
            }
        }

        // 2) Asset collector: one-time +5 for holding 3+ active assets
        $assetCount = PlayerAsset::where('user_id', $user->id)->where('status', 'active')->count();
        if ($assetCount >= 3) {
            $already = GameNotification::where('user_id', $user->id)
                ->where('type', 'credit_change')
                ->whereJsonContains('data->kind', 'asset_collector')
                ->exists();
            if (!$already) {
                $progress->adjustCreditScoreWithLog(+5, 'Portfolio builder: 3+ active assets owned', ['kind' => 'asset_collector']);
            }
        }

        // 3) Loyal worker: +10 per Pesa City job held 60+ ticks (once per job)
        if (Schema::hasTable('player_city_jobs')) {
            $loyalJobs = PlayerCityJob::where('user_id', $user->id)
                ->where('status', 'employed')
                ->where('ticks_employed', '>=', 60)
                ->get();

            foreach ($loyalJobs as $pj) {
                $already = GameNotification::where('user_id', $user->id)
                    ->where('type', 'credit_change')
                    ->whereJsonContains('data->kind', 'loyal_worker')
                    ->whereJsonContains('data->job_id', $pj->id)
                    ->exists();
                if (!$already) {
                    $progress->adjustCreditScoreWithLog(+10, 'Stable employment: job held 60+ game days', ['kind' => 'loyal_worker', 'job_id' => $pj->id]);
                }
            }
        }
    }

    // ── Bills due soon — proactive nudge ──────────────────────────────────────

    private function checkBillsDueSoon(User $user, UserProgress $progress): void
    {
        $currentTick = $progress->tick_count ?? 0;
        $dueSoon = PlayerBill::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereBetween('next_due_tick', [$currentTick + 1, $currentTick + 10])
            ->with('bill')
            ->get();

        foreach ($dueSoon as $pb) {
            $ticksLeft = $pb->next_due_tick - $currentTick;
            // Avoid duplicate notifications — only warn once per billing cycle
            $alreadyWarned = \App\Models\GameNotification::where('user_id', $user->id)
                ->where('type', 'bill_due_soon')
                ->where('created_at', '>=', now()->subDays(1))
                ->whereJsonContains('data->bill_id', $pb->bill_id)
                ->exists();

            if (!$alreadyWarned) {
                \App\Models\GameNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'bill_due_soon',
                    'title'   => "📋 Bill Due Soon: {$pb->bill->name}",
                    'body'    => "Ksh " . number_format($pb->amount) . " due in {$ticksLeft} game day(s). Pay it from your Life HQ before it goes overdue — bills are not paid automatically.",
                    'icon'    => $pb->bill->icon ?? '📋',
                    'data'    => ['bill_id' => $pb->bill_id, 'amount' => $pb->amount, 'ticks_left' => $ticksLeft],
                ]);
            }
        }
    }

    // ── Asset settlement ──────────────────────────────────────────────────────

    private function settleAssets(User $user, UserProgress $progress, int $ticksProcessed, array &$events): void
    {
        $playerAssets = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('asset')
            ->get();

        if ($playerAssets->isEmpty()) return;

        $now         = (int) ($progress->tick_count ?? 0);
        $hasAnchors  = Schema::hasColumn('player_assets', 'income_paid_to_tick');
        $totalIncome = 0;

        foreach ($playerAssets as $pa) {
            $asset = $pa->asset;
            if (!$asset) continue;

            // Persistent accrual anchors (same pattern as job unpaid_ticks).
            // The old floor(window / period) discarded the remainder on every
            // settle, so players who checked in often never spanned a full
            // period — assets simply never paid. Anchored accounting can't
            // lose ticks. First run after the migration backfills at most
            // 3 income periods / 1 upkeep month of catch-up.
            $incomePeriod = max(1, (int) ($asset->income_period_ticks ?? 30));
            $fallback     = (int) ($pa->purchased_at_tick ?? max(0, $now - $ticksProcessed));
            $incomeAnchor = ($hasAnchors && $pa->income_paid_to_tick !== null)
                ? (int) $pa->income_paid_to_tick
                : max($fallback, $now - 3 * $incomePeriod);
            $upkeepAnchor = ($hasAnchors && $pa->upkeep_paid_to_tick !== null)
                ? (int) $pa->upkeep_paid_to_tick
                : max($fallback, $now - 30);

            $monthsElapsed  = intdiv(max(0, $now - $upkeepAnchor), 30);
            $periodsElapsed = intdiv(max(0, $now - $incomeAnchor), $incomePeriod);
            if ($hasAnchors) {
                $pa->income_paid_to_tick = $incomeAnchor + $periodsElapsed * $incomePeriod;
                $pa->upkeep_paid_to_tick = $upkeepAnchor + $monthsElapsed * 30;
            }

            // ── Condition degradation (3 pts/month) ──────────────────────
            if ($monthsElapsed > 0) {
                $condBefore = $pa->condition ?? 100;
                $condAfter  = max(0, $condBefore - (3 * $monthsElapsed));
                $pa->condition = $condAfter;

                if ($condBefore >= 20 && $condAfter < 20 && $asset->monthly_income > 0) {
                    $events[] = [
                        'icon'        => '🔴', 'type' => 'asset_broken',
                        'text'        => "{$asset->name} is now broken — no income",
                        'sub'         => "Condition: {$condAfter}%. Maintain it to restore earnings.",
                        'delta'       => 0, 'is_positive' => false,
                        'edu'         => 'Regular asset maintenance protects your income stream. Always budget for upkeep.',
                    ];
                } elseif ($condBefore >= 40 && $condAfter < 40 && $asset->monthly_income > 0) {
                    $events[] = [
                        'icon'        => '⚠️', 'type' => 'asset_worn',
                        'text'        => "{$asset->name}: poor condition — income reduced",
                        'sub'         => "Condition: {$condAfter}%. Earning at 40% capacity.",
                        'delta'       => 0, 'is_positive' => false,
                        'edu'         => 'Neglected assets earn less. Visit the Life Board to maintain your assets.',
                    ];
                } elseif ($condBefore >= 70 && $condAfter < 70 && $asset->monthly_income > 0) {
                    $events[] = [
                        'icon'        => '🔧', 'type' => 'asset_worn',
                        'text'        => "{$asset->name}: showing wear — income slightly reduced",
                        'sub'         => "Condition: {$condAfter}%. Earning at 70% capacity.",
                        'delta'       => 0, 'is_positive' => false,
                        'edu'         => 'Assets need regular care. A small maintenance cost now prevents bigger losses later.',
                    ];
                }
            }

            // ── Income: per-asset income_period_ticks (default 30), anchored ──
            if ($periodsElapsed > 0) {
                if (($asset->monthly_income ?? 0) > 0) {
                    $condFactor = $pa->conditionFactor();
                    $income     = (int) round($asset->monthly_income * $periodsElapsed * $pa->quantity * $condFactor);
                    $progress->balance += $income;
                    $totalIncome       += $income;
                    $condNote   = $condFactor < 1.0 ? ' (reduced — low condition)' : '';
                    $periodLabel = $incomePeriod <= 7 ? 'wk' : 'mo';
                    $events[] = [
                        'icon'  => $asset->icon, 'type' => 'asset_income',
                        'text'  => "{$asset->name}: passive income{$condNote}",
                        'sub'   => 'Ksh ' . number_format((int)round($asset->monthly_income * $condFactor)) . "/{$periodLabel} × {$periodsElapsed}",
                        'delta' => $income,
                    ];
                }

                if (($asset->monthly_cost ?? 0) > 0) {
                    $costPeriod = max(1, $incomePeriod);
                    $cost = $asset->monthly_cost * $periodsElapsed * $pa->quantity;
                    $progress->balance = max(0, $progress->balance - $cost);
                    $periodLabel = $costPeriod <= 7 ? 'wk' : 'mo';
                    $events[] = [
                        'icon'  => '🔧', 'type' => 'asset_cost',
                        'text'  => "{$asset->name}: running costs",
                        'sub'   => 'Ksh ' . number_format($asset->monthly_cost) . "/{$periodLabel} × {$periodsElapsed}",
                        'delta' => -$cost,
                    ];
                }
            }

            // ── Appreciate / depreciate (monthly) ────────────────────────
            if ($monthsElapsed > 0 && ($asset->appreciation_rate ?? 0) != 0) {
                $factor   = 1 + ($asset->appreciation_rate / 100);
                $newValue = (int) round($pa->current_value * pow($factor, $monthsElapsed));
                if (($asset->volatility ?? 0) > 0) {
                    $swing    = $asset->volatility * abs($newValue - $pa->current_value);
                    $newValue += (int) round((lcg_value() * 2 - 1) * $swing);
                }
                $pa->current_value    = max(1, $newValue);
                $pa->last_valued_tick = $progress->tick_count;
            }

            if ($periodsElapsed > 0 || $monthsElapsed > 0 || ($hasAnchors && $pa->isDirty())) {
                $pa->save();
            }
        }

        // Make the income stream VISIBLE: one bell notification per settle
        // (players who never trigger the away-popup still see their money)
        if ($totalIncome > 0 && Schema::hasTable('game_notifications')) {
            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'asset_income',
                'title'   => '🏗️ Your assets paid you Ksh ' . number_format($totalIncome),
                'body'    => 'Passive income from your investments just landed in your wallet. See the breakdown on your Portfolio.',
                'icon'    => '🏗️',
                'data'    => ['amount' => $totalIncome, 'url' => '/portfolio'],
            ]);
        }
    }

    // ── Investment Deal settlement ────────────────────────────────────────────

    private function settleDeals(User $user, UserProgress $progress, array &$events): void
    {
        if (!Schema::hasTable('player_deals')) return;

        $pending = PlayerDeal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('resolve_at_tick', '<=', $progress->tick_count)
            ->with('deal')
            ->get();

        foreach ($pending as $pd) {
            $deal    = $pd->deal;
            $success = (lcg_value() < ($deal->success_probability ?? 0.5));

            if ($success) {
                $returnPct  = $deal->min_return_pct + lcg_value() * max(0, $deal->max_return_pct - $deal->min_return_pct);
                $profit     = (int) round($pd->amount_invested * $returnPct / 100);
                $payout     = $pd->amount_invested + $profit;
                $progress->balance += $payout;
                $pd->status      = 'success';
                $pd->profit_loss = $profit;
                $pd->resolved_at = now();
                $pd->save();

                $events[] = [
                    'icon'        => $deal->icon ?? '📈',
                    'type'        => 'deal_success',
                    'text'        => "Deal paid off: {$deal->title}",
                    'sub'         => "Return: +" . number_format($returnPct, 1) . "% → Ksh " . number_format($payout),
                    'delta'       => $profit,
                    'is_positive' => true,
                    'edu'         => $deal->lesson ?? 'Smart deals reward patience and calculated risk.',
                ];
            } else {
                $lostPct  = $deal->loss_pct ?? 100;
                $loss     = (int) round($pd->amount_invested * $lostPct / 100);
                $pd->status      = 'failed';
                $pd->profit_loss = -$loss;
                $pd->resolved_at = now();
                $pd->save();

                $events[] = [
                    'icon'        => $deal->icon ?? '📉',
                    'type'        => 'deal_failed',
                    'text'        => "Deal didn't pan out: {$deal->title}",
                    'sub'         => "Lost Ksh " . number_format($loss) . " — not every bet wins.",
                    'delta'       => -$loss,
                    'is_positive' => false,
                    'edu'         => $deal->lesson ?? 'Risk means real loss is possible. Never invest money you cannot afford to lose.',
                ];
            }
        }
    }

    // ── Loan settlement ───────────────────────────────────────────────────────

    /**
     * Friend (P2P) loans past their due tick settle on the BORROWER's login:
     * whatever cash exists is garnished to the lender; if it clears the debt
     * the loan is "repaid (late)" with a small credit reward, otherwise the
     * shortfall is written off as a default — heavy credit damage for the
     * borrower, and the lender learns counterparty risk the honest way.
     */
    private function settleFriendLoans(User $user, UserProgress $progress, array &$events): void
    {
        if (!Schema::hasTable('friend_loans')) return;

        $loans = \App\Models\FriendLoan::where('borrower_id', $user->id)
            ->where('status', 'active')
            ->whereNotNull('due_at_tick')
            ->where('due_at_tick', '<', (int) $progress->tick_count)
            ->with('lender')
            ->get();

        foreach ($loans as $loan) {
            $remaining = $loan->remaining();
            $available = max(0, (int) $progress->balance);
            $garnished = min($remaining, $available);

            if ($garnished > 0) {
                $progress->balance -= $garnished;
                $loan->amount_repaid = (int) $loan->amount_repaid + $garnished;

                if ($loan->lender) {
                    $lenderProgress = $loan->lender->getOrCreateProgress();
                    $lenderProgress->balance += $garnished;
                    $lenderProgress->recalculateNetWorth();
                    $lenderProgress->save();
                }
            }

            $lenderName = $loan->lender?->name ?? 'your friend';

            if ($garnished >= $remaining) {
                $loan->status = 'repaid';
                $progress->adjustCreditScoreWithLog(3, "Friend loan to {$lenderName} auto-collected (late)", ['kind' => 'friend_loan_repaid']);
                $events[] = [
                    'icon'        => '🤝',
                    'type'        => 'friend_loan_settled',
                    'text'        => "Friend loan auto-collected — {$lenderName}",
                    'sub'         => 'Ksh ' . number_format($garnished) . ' was taken from your wallet for the overdue loan. Repay on time next round to earn full credit points.',
                    'delta'       => -$garnished,
                    'is_positive' => false,
                ];
            } else {
                $loan->status = 'defaulted';
                $progress->adjustCreditScoreWithLog(-30, "Defaulted on a friend loan from {$lenderName}", ['kind' => 'friend_loan_default']);
                $shortfall = $remaining - $garnished;
                $events[] = [
                    'icon'        => '💔',
                    'type'        => 'friend_loan_default',
                    'text'        => "You defaulted on {$lenderName}'s loan",
                    'sub'         => 'Ksh ' . number_format($garnished) . ' was garnished but Ksh ' . number_format($shortfall) . " couldn't be recovered. Credit −30, and {$lenderName} lost real money trusting you.",
                    'delta'       => -$garnished,
                    'is_positive' => false,
                ];

                if ($loan->lender) {
                    GameNotification::create([
                        'user_id' => $loan->lender_id,
                        'type'    => 'friend_loan',
                        'title'   => '💔 ' . $user->name . ' defaulted on your loan',
                        'body'    => 'You recovered Ksh ' . number_format($garnished) . ' of Ksh ' . number_format($remaining) . '. Lending is a risk — check credit signals before you agree.',
                        'icon'    => '💔',
                        'data'    => ['url' => '/friends'],
                    ]);
                }
            }

            $loan->save();
        }
    }

    private function settleLoans(User $user, UserProgress $progress, int $ticksProcessed, array &$events): void
    {
        if (!Schema::hasTable('player_loans')) return;

        $loans = PlayerLoan::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('loanProduct')
            ->get();

        foreach ($loans as $loan) {
            $endTick = $progress->tick_count;
            $due     = $loan->next_payment_tick;

            while ($due <= $endTick) {
                // Apply periodic compound interest before deducting payment
                $periodicRate           = ($loan->annual_interest_rate / 100 / 365) * $loan->payment_period_ticks;
                $loan->outstanding_balance = (int) ceil($loan->outstanding_balance * (1 + $periodicRate));

                if ($progress->balance >= $loan->payment_amount) {
                    $actual = min($loan->payment_amount, $loan->outstanding_balance);
                    $progress->balance         -= $actual;
                    $loan->outstanding_balance  = max(0, $loan->outstanding_balance - $actual);
                    $loan->payments_made++;
                    $progress->adjustCreditScoreWithLog(+5, 'Loan payment made on time', ['kind' => 'loan_paid', 'loan_id' => $loan->id]);

                    $events[] = [
                        'icon'  => '🏦', 'type' => 'loan_payment',
                        'text'  => 'Installment paid: ' . $loan->displayName(),
                        'sub'   => 'Ksh ' . number_format($actual) . ' (' . $loan->payments_made . ' of ' . $loan->totalInstallments() . ') · Balance: Ksh ' . number_format($loan->outstanding_balance),
                        'delta' => -$actual,
                    ];
                } else {
                    $loan->payments_missed++;
                    $progress->adjustCreditScoreWithLog(-20, 'Missed a loan payment: ' . $loan->displayName(), ['kind' => 'loan_missed', 'loan_id' => $loan->id]);

                    $events[] = [
                        'icon'        => '⚠️', 'type' => 'loan_missed',
                        'text'        => 'Missed installment: ' . $loan->displayName() . ' — credit score hit',
                        'sub'         => 'Keep at least Ksh ' . number_format($loan->payment_amount) . ' for the next payment.',
                        'delta'       => 0, 'is_positive' => false,
                    ];
                }

                if ($loan->outstanding_balance <= 0) {
                    $loan->status = 'paid';
                    $progress->adjustCreditScoreWithLog(+30, 'Loan fully paid off: ' . $loan->displayName(), ['kind' => 'loan_cleared', 'loan_id' => $loan->id]);
                    $events[] = [
                        'icon'        => '🎉', 'type' => 'loan_paid',
                        'text'        => $loan->displayName() . ' fully paid off! It\'s all yours now.',
                        'sub'         => '+30 credit score for completing a loan.',
                        'delta'       => 0, 'is_positive' => true,
                    ];
                    break;
                }

                $due += $loan->payment_period_ticks;
            }

            // Check for default (past due_at_tick with balance remaining)
            if ($loan->status === 'active' && $progress->tick_count > $loan->due_at_tick && $loan->outstanding_balance > 0) {
                $loan->status = 'defaulted';
                $progress->adjustCreditScoreWithLog(-100, 'Loan defaulted', ['kind' => 'loan_default', 'loan_id' => $loan->id]);
                $events[] = [
                    'icon'        => '🔴', 'type' => 'loan_default',
                    'text'        => 'Loan defaulted — major credit score penalty',
                    'sub'         => 'Balance of Ksh ' . number_format($loan->outstanding_balance) . ' could not be collected.',
                    'delta'       => 0, 'is_positive' => false,
                ];
            }

            $loan->next_payment_tick = $due;
            $loan->save();
        }
    }

    // ── Stock price history recording ─────────────────────────────────────────

    private function recordStockPrices(User $user, UserProgress $progress): void
    {
        $investmentAssets = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('asset', fn($q) => $q->whereIn('category', ['investment', 'business', 'property']))
            ->get();

        foreach ($investmentAssets as $pa) {
            // Keep max 20 history points per asset
            $count = StockPriceHistory::where('player_asset_id', $pa->id)->count();
            if ($count >= 20) {
                StockPriceHistory::where('player_asset_id', $pa->id)
                    ->orderBy('tick')
                    ->limit($count - 19)
                    ->delete();
            }

            StockPriceHistory::create([
                'player_asset_id' => $pa->id,
                'tick'            => $progress->tick_count,
                'price'           => $pa->current_value,
                'recorded_at'     => now(),
            ]);
        }
    }

    // ── Life events ───────────────────────────────────────────────────────────

    private function rollLifeEvents(User $user, UserProgress $progress, int $ticks, array &$events): void
    {
        $chapter = $progress->chapterKey();

        // Gather asset categories the player owns (for asset-triggered events)
        $ownedCategories = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('asset:id,category')
            ->get()
            ->pluck('asset.category')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $candidates = LifeEvent::active()
            ->forChapter($chapter)
            ->forAssetCategories($ownedCategories)
            ->get()
            ->shuffle();

        $fired = 0;

        foreach ($candidates as $event) {
            if ($fired >= self::MAX_EVENTS_SESSION) break;

            // Cumulative probability: chance of firing at least once in $ticks ticks
            $prob = 1 - pow(max(0, 1 - $event->probability), $ticks);

            if (lcg_value() > $prob) continue;

            $effectDesc = $this->applyEventEffect($user, $progress, $event);

            PlayerLifeEvent::create([
                'user_id'            => $user->id,
                'life_event_id'      => $event->id,
                'tick_triggered'     => $progress->tick_count,
                'game_age_at_trigger'=> $progress->level ?? 1,
                'chapter_at_trigger' => $chapter,
                'effect_applied'     => $effectDesc,
            ]);

            $events[] = [
                'icon'        => $event->icon,
                'type'        => 'life_event',
                'text'        => $event->title,
                'sub'         => $event->description,
                'delta'       => $effectDesc['balance_change'] ?? 0,
                'is_positive' => $event->is_positive,
                'edu'         => $event->educational_note,
            ];

            $fired++;
        }
    }

    private function applyEventEffect(User $user, UserProgress $progress, LifeEvent $event): array
    {
        $data   = $event->effect_data ?? [];
        $result = ['balance_change' => 0];

        switch ($event->effect_type) {
            case 'balance_delta':
                // Never clamp to 0 — a negative event on an empty balance must still
                // go negative (real debt), not be silently discarded.
                $amount = isset($data['balance']) ? $data['balance']
                    : rand((int) ($data['balance_min'] ?? 0), (int) ($data['balance_max'] ?? 0));
                $progress->balance += $amount;
                $result['balance_change'] = $amount;
                break;

            case 'compound':
                $amount = rand((int) ($data['balance_min'] ?? 0), (int) ($data['balance_max'] ?? 0));
                $progress->balance += $amount;
                $result['balance_change'] = $amount;
                if (isset($data['credit']) && $data['credit'] !== 0) {
                    $progress->adjustCreditScore((int) $data['credit']);
                    $result['credit_change'] = $data['credit'];
                }
                break;

            case 'market_event':
                $categories = $data['market_categories'] ?? [
                    ['category' => $data['market_category'] ?? 'investment', 'pct' => $data['market_pct'] ?? 0],
                ];
                foreach ($categories as $cat) {
                    PlayerAsset::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->whereHas('asset', fn($q) => $q->where('category', $cat['category']))
                        ->get()
                        ->each(function ($pa) use ($cat) {
                            $pa->current_value = max(1, (int) round($pa->current_value * (1 + $cat['pct'])));
                            $pa->save();
                        });
                }
                $result['market_shift'] = $categories;
                break;

            case 'credit_delta':
                $delta = (int) ($data['credit'] ?? 0);
                $progress->adjustCreditScore($delta);
                $result['credit_change'] = $delta;
                break;

            // narrative — no mechanical effect
        }

        return $result;
    }

    // ── Pesa City job salary ──────────────────────────────────────────────────

    /**
     * Salaries are NEVER paid while the player is away. Work done accrues as
     * pending_salary; the player must "Report to Work" (check-in) to bank it.
     * Uncollected paychecks STACK — no money is ever forfeited. Instead,
     * every payday that lands on top of an uncollected one is a missed
     * collection: 3 in a row draws a final notice from the employer, and a
     * further game month of silence ends in dismissal (pay stays collectible
     * as a final settlement). Collecting resets the strike count.
     */
    private function settleJobSalaries(User $user, UserProgress $progress, int $ticks, array &$events): void
    {
        if (!Schema::hasTable('player_city_jobs') || !Schema::hasTable('city_jobs')) return;
        if (!Schema::hasColumn('player_city_jobs', 'pending_salary')) return; // pre-migration: no accrual yet

        $graceTracking = Schema::hasColumn('player_city_jobs', 'missed_paydays');

        $jobs = PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->with('job')
            ->get();

        if ($jobs->isEmpty()) return;

        $currentTick = (int) ($progress->tick_count ?? 0);

        foreach ($jobs as $pj) {
            if (!$pj->job) continue;

            $type = $pj->employment_type ?: $pj->job->type();

            // ── Freelance gig: completes after its duration, pays once ──────
            if ($type === 'freelance') {
                $pj->ticks_employed += $ticks;
                if ($pj->gig_ends_tick && $currentTick >= $pj->gig_ends_tick) {
                    $pay      = (int) ($pj->job->salary_kes_month ?? 0);
                    $cooldown = $pj->job->gigCooldownTicks();
                    $pj->status              = 'completed';
                    $pj->pending_salary      = $pay;
                    $pj->cooldown_until_tick = $pj->gig_ends_tick + $cooldown;

                    $events[] = [
                        'icon'  => $pj->job->employer_logo ?? '⚡',
                        'type'  => 'salary_ready',
                        'text'  => "Gig delivered: {$pj->job->title}",
                        'sub'   => 'Ksh ' . number_format($pay) . " from {$pj->job->employer_name} is ready — Report to Work to collect. Gig reopens in {$cooldown} game days.",
                        'delta' => 0,
                    ];
                }
                $pj->save();
                continue;
            }

            // ── Monthly jobs (full-time / part-time): accrue, don't pay ─────
            $pj->ticks_employed += $ticks;
            $pj->unpaid_ticks   += $ticks;

            $months = intdiv((int) $pj->unpaid_ticks, 30);
            if ($months >= 1) {
                $pj->unpaid_ticks = ((int) $pj->unpaid_ticks) % 30;
                $salary = (int) ($pj->job->salary_kes_month ?? 0);

                // Wages stack — a payday landing on an uncollected one is a "missed collection"
                $hadUncollected     = (int) $pj->pending_salary > 0;
                $pj->pending_salary = (int) $pj->pending_salary + ($months * $salary);

                $events[] = [
                    'icon'  => $pj->job->employer_logo ?? '🧾',
                    'type'  => 'salary_ready',
                    'text'  => "Payslip ready at {$pj->job->employer_name}",
                    'sub'   => 'Ksh ' . number_format($pj->pending_salary) . " is waiting for your work as {$pj->job->title} — Report to Work to bank it. Your pay stacks up safely, but skipping paydays gets you noticed.",
                    'delta' => 0,
                ];

                if ($graceTracking) {
                    $newMisses = ($months - 1) + ($hadUncollected ? 1 : 0);
                    if ($newMisses > 0) {
                        $pj->missed_paydays = (int) $pj->missed_paydays + $newMisses;
                    }

                    // 3 consecutive misses → the employer issues a final notice
                    if ((int) $pj->missed_paydays >= 3 && !$pj->removal_warned_at_tick) {
                        $pj->removal_warned_at_tick = $currentTick;
                        $events[] = [
                            'icon'        => '🚨',
                            'type'        => 'job_warning',
                            'text'        => "Final notice from {$pj->job->employer_name}",
                            'sub'         => "You've skipped {$pj->missed_paydays} paydays in a row as {$pj->job->title}. Report to Work within the next game month or you will be dismissed. Your stacked pay (Ksh " . number_format($pj->pending_salary) . ") is still safe to collect.",
                            'delta'       => 0,
                            'is_positive' => false,
                        ];
                    }

                    // A further game month of silence after the notice → dismissed.
                    // The 4th-miss requirement guarantees the notice always lands
                    // at least one full payday before the dismissal can.
                    if ($pj->removal_warned_at_tick
                        && (int) $pj->missed_paydays >= 4
                        && $currentTick >= (int) $pj->removal_warned_at_tick + 30) {
                        $pj->status   = 'dismissed';
                        $pj->ended_at = now();

                        $progress->mood = max(30, (int) ($progress->mood ?? 70) - 15);
                        $progress->adjustCreditScoreWithLog(-15, "Dismissed from {$pj->job->employer_name} for absenteeism", ['kind' => 'job_dismissed']);
                        if (($progress->career_title ?? null) === $pj->job->title) {
                            $progress->career_title = null;
                        }

                        $events[] = [
                            'icon'        => '📮',
                            'type'        => 'job_dismissed',
                            'text'        => "Dismissed from {$pj->job->employer_name}",
                            'sub'         => "After the final notice you still never reported to work, so they let you go as {$pj->job->title}. Ksh " . number_format($pj->pending_salary) . ' in earned pay is waiting as a final settlement — collect it via Report to Work. Mood −15, credit −15.',
                            'delta'       => 0,
                            'is_positive' => false,
                        ];
                    }
                }
            }

            $pj->save();
        }
    }

    // ── Bill settlement ───────────────────────────────────────────────────────

    private function settleBills(User $user, UserProgress $progress, int $startTick, array &$events): void
    {
        $endTick = $progress->tick_count;

        $playerBills = PlayerBill::where('user_id', $user->id)
            ->whereIn('status', ['active', 'overdue'])
            ->where('next_due_tick', '<=', $endTick)
            ->with('bill')
            ->get();

        // Bills are NEVER auto-paid — the player must pay each one from Life HQ.
        // Every billing cycle that passes unpaid goes overdue and damages credit.
        foreach ($playerBills as $pb) {
            $due    = $pb->next_due_tick;
            $missed = 0;

            while ($due <= $endTick) {
                // Credit damage caps at 2 hits per bill per catch-up so a long
                // absence stings but doesn't wipe a player's score out.
                if ($missed < 2) {
                    $progress->adjustCreditScoreWithLog($pb->bill->credit_impact_miss, "Bill overdue: {$pb->bill->name}", ['kind' => 'bill_missed', 'bill_id' => $pb->bill_id, 'tick' => $due]);
                }

                $pb->missed_count++;
                $pb->status             = 'overdue';
                $pb->overdue_since_tick = $pb->overdue_since_tick ?? $due;
                $missed++;

                $due += $pb->frequency_ticks;
            }

            if ($missed > 0) {
                $events[] = [
                    'icon'  => '⚠️',
                    'type'  => 'bill_missed',
                    'text'  => "Overdue: {$pb->bill->name} — Ksh " . number_format($pb->amount) . ($missed > 1 ? " ({$missed} cycles unpaid)" : ''),
                    'sub'   => trim(($pb->bill->consequence_text ? $pb->bill->consequence_text . ' ' : '') . 'Pay it from your Life HQ to stop the credit damage.'),
                    'delta' => 0,
                ];
            }

            $pb->next_due_tick = $due;
            $pb->save();
        }
    }

    // ── Bill assignment — chapter-gated ────────────────────────────────────────

    /**
     * Attach every eligible bill template the player doesn't already hold.
     * "Eligible" = active, auto_assign, matches age_group, AND the player's
     * CURRENT life chapter has reached the bill's min_chapter — so bills
     * arrive progressively as the player advances (a Student never sees a
     * mortgage-adjacent service charge; a Builder eventually does), instead
     * of every age-matched bill landing all at once on day one.
     *
     * Idempotent and safe to call on every login: called once at signup
     * (chapter = student, so only student-tier bills attach) and again after
     * every chapter advance in processLogin() to pick up newly-unlocked ones.
     */
    private function assignEligibleBills(User $user, UserProgress $progress, array &$events = []): void
    {
        $ageGroup       = $user->age_group ?? '18-25';
        $currentTick    = $progress->tick_count ?? 0;
        $chapterOrdinal = UserProgress::chapterOrdinal($progress->chapterKey());

        $existingBillIds = PlayerBill::where('user_id', $user->id)->pluck('bill_id');

        $templates = Bill::where('is_active', true)
            ->where('auto_assign', true)
            ->where(function ($q) use ($ageGroup) {
                $q->where('age_group', $ageGroup)->orWhere('age_group', 'all');
            })
            ->whereNotIn('id', $existingBillIds)
            ->get()
            ->filter(fn ($b) => UserProgress::chapterOrdinal($b->min_chapter ?: 'student') <= $chapterOrdinal);

        foreach ($templates as $bill) {
            PlayerBill::create([
                'user_id'         => $user->id,
                'bill_id'         => $bill->id,
                'amount'          => $bill->amount,
                'frequency_ticks' => $bill->frequency_ticks,
                'next_due_tick'   => $currentTick + $bill->frequency_ticks,
                'status'          => 'active',
            ]);

            // Skip the notification on the very first-ever assignment (signup) —
            // there's no "while you were away" moment to report yet.
            if ($progress->last_tick_at) {
                GameNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'bill_assigned',
                    'title'   => "{$bill->icon} New Bill: {$bill->name}",
                    'body'    => 'Ksh ' . number_format($bill->amount) . ' every ' . $bill->frequency_ticks . ' game days. '
                               . ($bill->flavor_text ?: $bill->description ?: "Life as a {$progress->chapterName()} comes with new responsibilities."),
                    'icon'    => $bill->icon,
                    'data'    => ['bill_id' => $bill->id, 'amount' => $bill->amount],
                ]);

                $events[] = [
                    'icon'  => $bill->icon ?? '🧾',
                    'type'  => 'bill_assigned',
                    'text'  => "New bill: {$bill->name}",
                    'sub'   => 'Ksh ' . number_format($bill->amount) . ' every ' . $bill->frequency_ticks . ' game days — a new cost of your ' . $progress->chapterName() . ' chapter.',
                    'delta' => 0,
                ];
            }
        }
    }
}
