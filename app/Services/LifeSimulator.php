<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Bill;
use App\Models\Chama;
use App\Models\ChamaLoan;
use App\Models\ChamaProposal;
use App\Models\CityJob;
use App\Models\GameNotification;
use App\Models\InvestmentDeal;
use App\Models\LifeEvent;
use App\Models\PlayerAsset;
use App\Models\PlayerBill;
use App\Models\PlayerCityCourse;
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

        // Market Watch bulletins — a login-driven fallback for the scheduled
        // artisan commands, so a misconfigured or missing cPanel cron doesn't
        // mean zero bulletins ever, silently, forever. Resolving/announcing
        // due bulletins is cheap and idempotent so it's safe on every login;
        // the publish dice-roll is gated by game days elapsed (server-wide,
        // via ShareNewsService::rollDueBulletins()), not real calendar days.
        $this->checkMarketWatch();

        // Market Jitters — same login-driven fallback, for the bulk-preset,
        // game-time-scheduled share market shocks. See MarketJitterService.
        $this->checkMarketJitters();

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

            // Process chama loan payments — interest flows back to the pool
            $this->settleChamaLoans($user, $progress, $events);

            $this->settleFriendLoans($user, $progress, $events);

            // Pay bank interest into savings schemes (monthly, compounding)
            $this->settleSavingsInterest($user, $progress, $events);

            // Compound daily MMF interest, then release any withdrawals whose
            // 1-3 game-day delay has elapsed
            $this->settleMmfInterest($user, $progress, $events);
            $this->settleMmfWithdrawals($user, $progress, $events);

            // Pay Pesa City job salaries
            $this->settleJobSalaries($user, $progress, $ticks, $events);

            // Raises and title promotions for players who've stuck at a job
            $this->settlePromotions($user, $progress, $events);

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

            // One row per player per tick actually reached (not per real
            // login) -- lets the "vs last game month" header stats look
            // back to the closest snapshot ~30 ticks ago. See
            // PlayerFinancialSnapshot::asOf().
            \App\Models\PlayerFinancialSnapshot::updateOrCreate(
                ['user_id' => $user->id, 'tick' => $progress->tick_count],
                ['balance' => $progress->balance, 'net_worth' => $progress->net_worth_cache, 'recorded_at' => now()]
            );
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

        // Re-price on every tick, not just a chapter flip — a chapter's own
        // tiers only ever move on the same net-worth boundaries anyway, so
        // this is a no-op most ticks, but it's what actually catches bills
        // that were assigned (or last repriced) before an admin edited the
        // tier table, or a custom tier that doesn't line up with a chapter
        // boundary at all. Gating this to chapter changes only meant a bill
        // could sit at a stale price indefinitely once a player had already
        // passed the relevant threshold under an older tier config.
        $this->reassessBillPricing($progress, $events);
    }

    /** Re-prices every net-worth-tiered bill the player already holds against
     *  their current net worth. Can move a bill's amount up or down, matching
     *  how net worth itself can rise or fall. */
    private function reassessBillPricing(UserProgress $progress, array &$events): void
    {
        $netWorth = (int) $progress->net_worth_cache;

        $playerBills = PlayerBill::where('user_id', $progress->user_id)
            ->whereIn('status', ['active', 'overdue'])
            ->with('bill')
            ->get()
            ->filter(fn ($pb) => $pb->bill && !empty($pb->bill->net_worth_tiers['tiers'] ?? []));

        foreach ($playerBills as $playerBill) {
            $newAmount = $playerBill->bill->resolveAmount($netWorth);
            if ($newAmount === (int) $playerBill->amount) {
                continue;
            }

            $oldAmount = (int) $playerBill->amount;
            $playerBill->update(['amount' => $newAmount]);

            $risen = $newAmount > $oldAmount;
            $verb  = $risen ? 'risen' : 'fallen';
            $range = 'Ksh ' . number_format($oldAmount) . ' → Ksh ' . number_format($newAmount);

            GameNotification::create([
                'user_id' => $progress->user_id,
                'type'    => 'bill_repriced',
                'title'   => "{$playerBill->bill->icon} {$playerBill->bill->name} has {$verb}",
                'body'    => "{$range} every {$playerBill->frequency_ticks} game days — your lifestyle costs now match your net worth.",
                'icon'    => $playerBill->bill->icon,
                'data'    => ['bill_id' => $playerBill->bill_id, 'old_amount' => $oldAmount, 'new_amount' => $newAmount],
            ]);

            $events[] = [
                'icon'  => $playerBill->bill->icon ?? '🧾',
                'type'  => 'bill_repriced',
                'text'  => "{$playerBill->bill->name} {$verb}: {$range}",
                'sub'   => "Every {$playerBill->frequency_ticks} game days, matching your new net worth.",
                'delta' => 0,
            ];
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

    // ── MMF settlement ───────────────────────────────────────────────────────

    /**
     * Compound one MMF position's daily interest up to the current tick,
     * merging any pending top-up whose cutoff-delayed start day has arrived.
     * Public so MmfController can settle a position on-demand right before a
     * top-up/withdraw, using the exact same math as the bulk per-login pass
     * below — Daily Rate = Yearly Rate / 365, applied once per elapsed game
     * day with a freshly-rolled rate each day (real MMF returns vary day to
     * day; a fixed rate would just be another savings account).
     */
    public function settleMmfPosition(PlayerAsset $pa, UserProgress $progress, array &$events = []): void
    {
        $asset = $pa->asset;
        if (!$asset || ($asset->product_type ?? null) !== 'money_market_fund') return;

        $now = (int) ($progress->tick_count ?? 0);

        if (($pa->mmf_pending_topup_amount ?? 0) > 0 && $pa->mmf_topup_ready_tick !== null && $now >= $pa->mmf_topup_ready_tick) {
            $pa->mmf_principal            = ($pa->mmf_principal ?? 0) + $pa->mmf_pending_topup_amount;
            $pa->current_value            = ($pa->current_value ?? 0) + $pa->mmf_pending_topup_amount;
            $pa->mmf_pending_topup_amount = 0;
            $pa->mmf_topup_ready_tick     = null;
        }

        $anchor = $pa->mmf_last_interest_tick ?? $now;
        $days   = max(0, $now - $anchor);

        if ($days > 0 && $pa->current_value > 0) {
            [$minRate, $maxRate] = $asset->mmfRateBand();
            $value         = (float) $pa->current_value;
            $totalInterest = 0.0;

            for ($d = 0; $d < $days; $d++) {
                $annualRate   = $minRate + lcg_value() * max(0, $maxRate - $minRate);
                $dayInterest  = $value * ($annualRate / 100 / self::TICKS_PER_YEAR);
                $value        += $dayInterest;
                $totalInterest += $dayInterest;
            }

            $totalInterest = (int) round($totalInterest);
            if ($totalInterest > 0) {
                $pa->current_value       = (int) round($value);
                $pa->mmf_interest_earned = ($pa->mmf_interest_earned ?? 0) + $totalInterest;

                $avgRate = round(($minRate + $maxRate) / 2, 1);
                $events[] = [
                    'icon'        => $asset->icon ?? '💰',
                    'type'        => 'mmf_interest',
                    'text'        => "{$asset->name}: daily interest",
                    'sub'         => 'Ksh ' . number_format($totalInterest) . " over {$days} game day(s), ~{$avgRate}% p.a. average",
                    'delta'       => 0, // stays inside the fund, not the wallet
                    'is_positive' => true,
                    'edu'         => 'MMFs compound daily — interest earns interest every single day, including weekends.',
                ];
            }
        }

        $pa->mmf_last_interest_tick = $now;
        $pa->save();
    }

    private function settleMmfInterest(User $user, UserProgress $progress, array &$events): void
    {
        if (!Schema::hasColumn('player_assets', 'mmf_last_interest_tick')) return;

        PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('asset')
            ->get()
            ->filter(fn ($pa) => $pa->isMmf())
            ->each(fn ($pa) => $this->settleMmfPosition($pa, $progress, $events));
    }

    /** Credits any MMF withdrawal whose 1-3 game-day delay has elapsed. */
    private function settleMmfWithdrawals(User $user, UserProgress $progress, array &$events): void
    {
        if (!Schema::hasColumn('player_assets', 'mmf_pending_withdrawal_amount')) return;

        $now = (int) ($progress->tick_count ?? 0);

        $due = PlayerAsset::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('mmf_pending_withdrawal_amount', '>', 0)
            ->where('mmf_withdrawal_ready_tick', '<=', $now)
            ->with('asset')
            ->get();

        foreach ($due as $pa) {
            $payout = (int) $pa->mmf_pending_withdrawal_amount;
            $progress->balance += $payout;

            $pa->mmf_pending_withdrawal_amount = 0;
            $pa->mmf_withdrawal_ready_tick     = null;
            if ((int) $pa->current_value <= 0) {
                $pa->status       = 'sold';
                $pa->sold_price   = 0;
                $pa->sold_at_tick = $now;
            }
            $pa->save();

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'mmf_withdrawal_settled',
                'title'   => "✅ MMF withdrawal landed",
                'body'    => "Ksh " . number_format($payout) . " from {$pa->asset->name} is now in your wallet.",
                'icon'    => '✅',
                'data'    => ['asset_id' => $pa->asset_id, 'amount' => $payout],
            ]);

            $events[] = [
                'icon'  => '✅', 'type' => 'mmf_withdrawal_settled',
                'text'  => "{$pa->asset->name}: withdrawal landed",
                'sub'   => 'Ksh ' . number_format($payout),
                'delta' => $payout,
            ];
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

            // MMF positions compound daily via settleMmfInterest() instead of
            // the flat monthly_income path below — skip to avoid double-paying.
            if (($asset->product_type ?? null) === 'money_market_fund') continue;

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

            // ── Maturity redemption (Treasury Bills / Treasury Bonds) ─────
            // Fixed-income instruments with a maturity_ticks value close out
            // in one lump sum instead of appreciating like a car or house —
            // discount instruments (T-Bills) also pay a maturity_bonus_pct
            // on top of current_value, the "face value vs. discount price"
            // spread. Runs before the generic appreciation block so a
            // matured holding never also drifts in value this pass.
            if ($asset->hasMaturity() && $pa->isMatured($now)) {
                $payout = (int) round($pa->current_value * (1 + $asset->maturity_bonus_pct / 100));
                $pa->status       = 'matured';
                $pa->sold_price   = $payout;
                $pa->sold_at_tick = $now;
                $progress->balance += $payout;
                $totalIncome       += $payout;

                GameNotification::create([
                    'user_id' => $user->id,
                    'type'    => 'asset_matured',
                    'title'   => "{$asset->icon} {$asset->name} Matured!",
                    'body'    => "Your {$asset->name} matured and paid out Ksh " . number_format($payout) . ". It's been credited to your balance.",
                    'icon'    => $asset->icon,
                    'data'    => ['asset_id' => $asset->id, 'payout' => $payout],
                ]);

                $events[] = [
                    'icon'  => $asset->icon, 'type' => 'asset_matured',
                    'text'  => "{$asset->name} matured — payout credited",
                    'sub'   => 'Ksh ' . number_format($payout),
                    'delta' => $payout,
                    'edu'   => 'Fixed-income instruments like Treasury Bills return your money plus a fixed return on a set date — no guessing, no volatility, that\'s the trade-off for a lower ceiling than stocks.',
                ];

                $pa->save();
                continue;
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

    // ── Chama loan settlement ───────────────────────────────────────────────
    //
    // Same tick-driven amortized-instalment shape as settleLoans(), with two
    // real differences: repayments (principal AND interest) land in the
    // chama's pool_balance instead of vanishing, with the interest portion
    // also credited to undistributed_gains for the next dividend; and a
    // default shrinks the defaulting member's own total_contributed (their
    // stake absorbs the unpaid amount) rather than just hurting their credit
    // score, plus repeated defaults auto-raise a remove_member vote.

    private function settleChamaLoans(User $user, UserProgress $progress, array &$events): void
    {
        if (!Schema::hasTable('chama_loans')) return;

        $loans = ChamaLoan::where('status', 'active')
            ->whereHas('borrowerMember', fn ($q) => $q->where('user_id', $user->id))
            ->with('borrowerMember.chama')
            ->get();

        foreach ($loans as $loan) {
            $chama = $loan->borrowerMember?->chama;
            if (!$chama) continue;

            $endTick = $progress->tick_count;
            $due     = $loan->next_payment_tick;

            while ($due <= $endTick) {
                $periodicRate    = ($loan->interest_rate / 100 / 365) * $loan->payment_period_ticks;
                $interestPortion = (int) ceil($loan->outstanding_balance * $periodicRate);
                $loan->outstanding_balance += $interestPortion;

                if ($progress->balance >= $loan->payment_amount) {
                    $actual = min($loan->payment_amount, $loan->outstanding_balance);
                    $progress->balance         -= $actual;
                    $loan->outstanding_balance  = max(0, $loan->outstanding_balance - $actual);
                    $loan->payments_made++;

                    $chama->pool_balance        += $actual;
                    $chama->undistributed_gains += $interestPortion;
                    $chama->save();

                    $progress->adjustCreditScoreWithLog(+5, 'Chama loan payment made on time', ['kind' => 'chama_loan_paid', 'loan_id' => $loan->id]);

                    $events[] = [
                        'icon'  => '🤝', 'type' => 'chama_loan_payment',
                        'text'  => 'Chama loan instalment paid — ' . $chama->name,
                        'sub'   => 'Ksh ' . number_format($actual) . ' (' . $loan->payments_made . ' of ' . $loan->totalInstallments() . ') · Balance: Ksh ' . number_format($loan->outstanding_balance),
                        'delta' => -$actual,
                    ];
                } else {
                    $loan->payments_missed++;
                    $progress->adjustCreditScoreWithLog(-20, 'Missed a chama loan payment', ['kind' => 'chama_loan_missed', 'loan_id' => $loan->id]);

                    $events[] = [
                        'icon'        => '⚠️', 'type' => 'chama_loan_missed',
                        'text'        => 'Missed chama loan instalment — ' . $chama->name,
                        'sub'         => 'Keep at least Ksh ' . number_format($loan->payment_amount) . ' for the next payment.',
                        'delta'       => 0, 'is_positive' => false,
                    ];
                }

                if ($loan->outstanding_balance <= 0) {
                    $loan->status = 'paid';
                    $progress->adjustCreditScoreWithLog(+15, 'Chama loan fully paid off', ['kind' => 'chama_loan_cleared', 'loan_id' => $loan->id]);
                    $events[] = [
                        'icon'        => '🎉', 'type' => 'chama_loan_paid',
                        'text'        => 'Chama loan fully paid off!',
                        'sub'         => '+15 credit score for clearing it with your chama.',
                        'delta'       => 0, 'is_positive' => true,
                    ];
                    break;
                }

                $due += $loan->payment_period_ticks;
            }

            if ($loan->status === 'active' && $progress->tick_count > $loan->due_at_tick && $loan->outstanding_balance > 0) {
                $this->defaultChamaLoan($loan, $chama, $progress, $events);
            }

            $loan->next_payment_tick = $due;
            $loan->save();
        }
    }

    private function defaultChamaLoan(ChamaLoan $loan, Chama $chama, UserProgress $progress, array &$events): void
    {
        $loan->status = 'defaulted';
        $loan->save(); // persist before the default-count check below, or this default undercounts itself
        $progress->adjustCreditScoreWithLog(-60, 'Chama loan defaulted', ['kind' => 'chama_loan_default', 'loan_id' => $loan->id]);

        $member = $loan->borrowerMember;
        $member->total_contributed = max(0, $member->total_contributed - $loan->outstanding_balance);
        $member->save();
        $chama->recalculateShares();

        $events[] = [
            'icon'        => '🔴', 'type' => 'chama_loan_default',
            'text'        => 'Chama loan defaulted — ' . $chama->name,
            'sub'         => 'Ksh ' . number_format($loan->outstanding_balance) . ' unpaid. Your chama stake shrank to cover it, and your credit score took a hit.',
            'delta'       => 0, 'is_positive' => false,
        ];

        $defaultCount = ChamaLoan::where('borrower_member_id', $member->id)->where('status', 'defaulted')->count();
        if ($defaultCount >= Chama::DEFAULTS_BEFORE_REMOVAL_VOTE) {
            $alreadyProposed = ChamaProposal::where('chama_id', $chama->id)
                ->where('type', 'remove_member')
                ->where('status', 'voting')
                ->whereJsonContains('proposal_data->user_id', $member->user_id)
                ->exists();

            if (!$alreadyProposed) {
                ChamaProposal::create([
                    'chama_id'      => $chama->id,
                    'proposer_id'   => $chama->creator_id,
                    'type'          => 'remove_member',
                    'title'         => $member->user->name . ' has defaulted on ' . $defaultCount . ' chama loans',
                    'proposal_data' => ['user_id' => $member->user_id],
                    'status'        => 'voting',
                    'votes_yes'     => 0,
                    'votes_no'      => 0,
                    'expires_at'    => now()->addSeconds($this->clock->realSecondsForTicks(7)),
                ]);
            }
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
                $salary = $pj->effectiveSalary();

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
                        $wasClean = (int) $pj->missed_paydays === 0;
                        $pj->missed_paydays = (int) $pj->missed_paydays + $newMisses;

                        // Temporary promotion disqualification — triggers on either
                        // (a) 2+ missed paydays piling up in one unresolved stretch,
                        // or (b) a second separate miss-incident for this job, even
                        // if the first one was fully cleared via Report to Work. It
                        // lifts automatically after a full probation window (one
                        // game year, same as the tenure requirement) of staying
                        // clean — settlePromotions() does the clearing. A fresh
                        // miss while already disqualified pushes the window back
                        // out instead of leaving it stale.
                        if (Schema::hasColumn('player_city_jobs', 'promotion_disqualified')) {
                            if ($wasClean) {
                                $pj->miss_incidents = (int) $pj->miss_incidents + 1;
                            }
                            if ($pj->promotion_disqualified || (int) $pj->missed_paydays >= 2 || (int) $pj->miss_incidents >= 2) {
                                $pj->promotion_disqualified          = true;
                                $pj->promotion_probation_until_tick  = (int) $pj->ticks_employed + self::TITLE_INTERVAL_TICKS;
                            }
                        }
                    }

                    // 2 consecutive misses → the employer issues a final notice
                    if ((int) $pj->missed_paydays >= 2 && !$pj->removal_warned_at_tick) {
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
                    // The 3rd-miss requirement guarantees the notice always lands
                    // at least one full payday before the dismissal can.
                    if ($pj->removal_warned_at_tick
                        && (int) $pj->missed_paydays >= 3
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

    // ── Promotions & raises ──────────────────────────────────────────────────
    //
    // Gated on tenure ("how long you've stayed") + conduct (missed_paydays==0,
    // reset clean by every successful Report to Work) rather than XP level —
    // the old WorldController stub gated purely on level and nothing ever
    // consumed it, which is why players saw "promotion eligible" forever with
    // no actual promotion. Freelance gigs are excluded: each gig is a fresh
    // one-off negotiation, not a persistent role to grow inside.

    /** Real ticks between raise reviews — roughly one game quarter. */
    private const RAISE_INTERVAL_TICKS = 90;

    /** Raise size per review, compounding on the existing multiplier. */
    private const RAISE_PCT = 10;

    /** Real ticks between title-tier reviews — roughly one game year. */
    private const TITLE_INTERVAL_TICKS = 360;

    /** Salary bump when a title review has no next-tier job to promote into,
     *  so tenure alone still grows the role — larger than a plain raise since
     *  it's standing in for a real promotion. */
    private const TITLE_BUMP_PCT = 20;

    private function settlePromotions(User $user, UserProgress $progress, array &$events): void
    {
        if (!Schema::hasColumn('player_city_jobs', 'salary_multiplier')) return;

        $jobs = PlayerCityJob::where('user_id', $user->id)
            ->where('status', 'employed')
            ->with('job')
            ->get();

        foreach ($jobs as $pj) {
            if (!$pj->job) continue;

            $type = $pj->employment_type ?: $pj->job->type();
            if ($type === 'freelance') continue;

            $isClean = (int) $pj->missed_paydays === 0;

            // Promotion disqualification is a probation, not a life sentence —
            // once the job has stayed clean all the way through the probation
            // window (set/extended in settleJobSalaries whenever a disqualifying
            // miss happens), eligibility is restored automatically.
            if ($pj->promotion_disqualified && $isClean
                && $pj->promotion_probation_until_tick !== null
                && (int) $pj->ticks_employed >= (int) $pj->promotion_probation_until_tick) {
                $pj->promotion_disqualified         = false;
                $pj->miss_incidents                 = 0;
                $pj->promotion_probation_until_tick = null;
                $pj->save();
            }

            $sinceReview = (int) $pj->ticks_employed - (int) $pj->ticks_employed_at_last_review;
            if (!$isClean || $sinceReview < self::RAISE_INTERVAL_TICKS) continue;

            // Title review takes priority over a plain raise the same cycle —
            // once a year, only if the employer actually has a next tier for them,
            // and only if this job has never had 2 missed paydays in one stretch
            // (or a second separate miss-incident) — a permanent flag, unaffected
            // by a currently-clean streak. Raises above are unaffected by this.
            $titleEligible = $sinceReview >= self::TITLE_INTERVAL_TICKS && !$pj->promotion_disqualified;
            if ($titleEligible) {
                $nextJob = $pj->job->promotes_to_job_id
                    ? CityJob::find($pj->job->promotes_to_job_id)
                    : $this->findNextTierJob($pj->job, $user);

                if ($nextJob) {
                    $oldTitle  = $pj->job->title;
                    $oldSalary = $pj->effectiveSalary();

                    $pj->city_job_id                  = $nextJob->id;
                    $pj->salary_multiplier            = 1.0;
                    $pj->ticks_employed_at_last_review = $pj->ticks_employed;
                    $pj->promotions_count             = (int) $pj->promotions_count + 1;
                    $pj->save();

                    if (($progress->career_title ?? null) === $oldTitle) {
                        $progress->career_title = $nextJob->title;
                    }

                    $newSalary = (int) round($nextJob->salary_kes_month ?? 0);
                    $events[] = [
                        'icon'        => $nextJob->employer_logo ?? '🎉',
                        'type'        => 'job_promotion',
                        'text'        => "Promoted to {$nextJob->title}!",
                        'sub'         => "{$nextJob->employer_name} moved you up from {$oldTitle} after a year of reliable work. Salary: Ksh " . number_format($oldSalary) . ' → Ksh ' . number_format($newSalary) . '/month.',
                        'delta'       => 0,
                        'is_positive' => true,
                        'is_milestone'=> true,
                    ];

                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'job_promotion',
                        'title'   => "🎉 Promoted to {$nextJob->title}!",
                        'body'    => "{$nextJob->employer_name} moved you up from {$oldTitle}. New salary: Ksh " . number_format($newSalary) . '/month.',
                        'icon'    => $nextJob->employer_logo ?? '🎉',
                        'data'    => ['amount' => $newSalary, 'url' => '/life/career'],
                    ]);

                    continue; // one advancement per review cycle — no raise on top this round
                }

                // No better-paying job exists anywhere for them right now — give
                // an in-place title bump instead, up to Senior-equivalent (the
                // ceiling of the level system). Once capped, tenure alone can't
                // grow this role further; stop bumping and point them at the
                // Opportunity Hub for a genuinely new Senior role — which their
                // now-Senior effective level makes them a real fit for.
                if ($pj->effectiveLevel() < 3) {
                    $oldTitle  = $pj->displayTitle();
                    $oldSalary = $pj->effectiveSalary();

                    $pj->title_bumps                   = (int) $pj->title_bumps + 1;
                    $pj->salary_multiplier             = round(($pj->salary_multiplier ?: 1.0) * (1 + self::TITLE_BUMP_PCT / 100), 3);
                    $pj->ticks_employed_at_last_review  = $pj->ticks_employed;
                    $pj->promotions_count              = (int) $pj->promotions_count + 1;
                    $pj->save();

                    $newTitle  = $pj->displayTitle();
                    $newSalary = $pj->effectiveSalary();

                    if (($progress->career_title ?? null) === $oldTitle) {
                        $progress->career_title = $newTitle;
                    }

                    $capNote = $pj->effectiveLevel() >= 3
                        ? " You've reached Senior-level tenure here — check the Opportunity Hub for a real Senior role to keep climbing."
                        : '';

                    $events[] = [
                        'icon'         => $pj->job->employer_logo ?? '🎉',
                        'type'         => 'job_promotion',
                        'text'         => "Promoted to {$newTitle}!",
                        'sub'          => "{$pj->job->employer_name} recognized your reliability with a title bump — no bigger role was open there yet. Salary: Ksh " . number_format($oldSalary) . ' → Ksh ' . number_format($newSalary) . "/month.{$capNote}",
                        'delta'        => 0,
                        'is_positive'  => true,
                        'is_milestone' => true,
                    ];

                    GameNotification::create([
                        'user_id' => $user->id,
                        'type'    => 'job_promotion',
                        'title'   => "🎉 Promoted to {$newTitle}!",
                        'body'    => "New salary: Ksh " . number_format($newSalary) . "/month.{$capNote}",
                        'icon'    => $pj->job->employer_logo ?? '🎉',
                        'data'    => ['amount' => $newSalary, 'url' => '/life/career'],
                    ]);

                    continue;
                }
            }

            // Plain raise — may compound multiple times if the player was away
            // long enough to cross the interval more than once in one catch-up.
            $intervalsElapsed = intdiv($sinceReview, self::RAISE_INTERVAL_TICKS);
            if ($intervalsElapsed < 1) continue;

            $oldSalary = $pj->effectiveSalary();
            $pj->salary_multiplier = round(
                ($pj->salary_multiplier ?: 1.0) * ((1 + self::RAISE_PCT / 100) ** $intervalsElapsed),
                3
            );
            $pj->ticks_employed_at_last_review += $intervalsElapsed * self::RAISE_INTERVAL_TICKS;
            $pj->save();
            $newSalary = $pj->effectiveSalary();

            $events[] = [
                'icon'  => $pj->job->employer_logo ?? '📈',
                'type'  => 'salary_raise',
                'text'  => "Pay raise at {$pj->job->employer_name}!",
                'sub'   => "Steady, reliable work as {$pj->displayTitle()} earned you a raise. Ksh " . number_format($oldSalary) . ' → Ksh ' . number_format($newSalary) . '/month.',
                'delta' => 0,
                'is_positive' => true,
            ];

            GameNotification::create([
                'user_id' => $user->id,
                'type'    => 'salary_raise',
                'title'   => "📈 Pay raise as {$pj->displayTitle()}!",
                'body'    => "Ksh " . number_format($oldSalary) . ' → Ksh ' . number_format($newSalary) . '/month at ' . $pj->job->employer_name . '.',
                'icon'    => $pj->job->employer_logo ?? '📈',
                'data'    => ['amount' => $newSalary, 'url' => '/life/career'],
            ]);
        }
    }

    /** Same career_track, next level tier up, open to the player's age group and
     *  with any required course already completed — the automatic fallback when
     *  a job has no admin-curated promotes_to_job_id. Picks the best-paying match
     *  if more than one job fits. Public: WorldController reuses this to preview
     *  "what you'd be promoted into" without duplicating the matching logic. */
    public function findNextTierJob(CityJob $current, User $user): ?CityJob
    {
        $tracks = $current->careerTrackList();
        if (empty($tracks)) return null;

        $completedIds = PlayerCityCourse::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('city_course_id');

        return CityJob::active()
            ->where('level', (int) $current->level + 1)
            ->get()
            ->filter(fn (CityJob $c) => !empty(array_intersect($tracks, $c->careerTrackList())))
            ->filter(fn (CityJob $c) => $c->matchesAgeGroup($user->age_group))
            ->filter(fn (CityJob $c) => $c->meetsRequirements($completedIds))
            ->sortByDesc('salary_kes_month')
            ->first();
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
                    'icon'   => '⚠️',
                    'type'   => 'bill_missed',
                    'text'   => "Overdue: {$pb->bill->name} — Ksh " . number_format($pb->amount) . ($missed > 1 ? " ({$missed} cycles unpaid)" : ''),
                    'sub'    => trim(($pb->bill->consequence_text ? $pb->bill->consequence_text . ' ' : '') . 'Pay it from your Life HQ to stop the credit damage.'),
                    'delta'  => 0,
                    // Not folded into 'delta' — bills are never auto-paid, so
                    // this is what's now owed, not a balance change. Used to
                    // total up the grouped "N bills overdue" summary.
                    'amount' => $pb->amount * $missed,
                ];
            }

            $pb->next_due_tick = $due;
            $pb->save();
        }
    }

    // ── Market Watch — login-driven fallback for the cron-only bulletin engine ──

    private function checkMarketWatch(): void
    {
        if (!Schema::hasTable('share_news_items')) return;

        $service = app(ShareNewsService::class);

        // Resolving/announcing due bulletins is just a timestamp check — safe
        // and cheap to repeat on every login, same as the hourly
        // `game:resolve-share-news`.
        try {
            $service->resolveDue();
            $service->announceDue();
        } catch (\Throwable $e) {
            \Log::warning('Market Watch resolveDue failed: ' . $e->getMessage());
        }

        // The publish dice-roll is gated by game days elapsed (not real
        // calendar days) so the cadence stays "roughly 3 a game week" no
        // matter how fast the admin has the clock configured — see
        // ShareNewsService::rollDueBulletins().
        try {
            $service->rollDueBulletins();
        } catch (\Throwable $e) {
            \Log::warning('Market Watch rollDueBulletins failed: ' . $e->getMessage());
        }
    }

    // ── Market Jitters — login-driven fallback for the cron-only shock engine ──

    private function checkMarketJitters(): void
    {
        if (!Schema::hasTable('market_jitters')) return;

        $service = app(\App\Services\MarketJitterService::class);

        try {
            $service->sendDueWarnings();
        } catch (\Throwable $e) {
            \Log::warning('Market Jitters sendDueWarnings failed: ' . $e->getMessage());
        }

        try {
            $service->applyDue();
        } catch (\Throwable $e) {
            \Log::warning('Market Jitters applyDue failed: ' . $e->getMessage());
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

        // Freshest possible net worth for tier resolution below — cheap
        // relative to the rest of this method, and not guaranteed current at
        // the login call site (only the tick-loop call site recalculates it
        // just before this runs).
        $netWorth = $progress->recalculateNetWorth();

        $templates = Bill::where('is_active', true)
            ->where('auto_assign', true)
            ->where(function ($q) use ($ageGroup) {
                $q->where('age_group', $ageGroup)->orWhere('age_group', 'all');
            })
            ->whereNotIn('id', $existingBillIds)
            ->get()
            ->filter(fn ($b) => UserProgress::chapterOrdinal($b->min_chapter ?: 'student') <= $chapterOrdinal);

        foreach ($templates as $bill) {
            $amount = $bill->resolveAmount($netWorth);

            PlayerBill::create([
                'user_id'         => $user->id,
                'bill_id'         => $bill->id,
                'amount'          => $amount,
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
                    'body'    => 'Ksh ' . number_format($amount) . ' every ' . $bill->frequency_ticks . ' game days. '
                               . ($bill->flavor_text ?: $bill->description ?: "Life as a {$progress->chapterName()} comes with new responsibilities."),
                    'icon'    => $bill->icon,
                    'data'    => ['bill_id' => $bill->id, 'amount' => $amount],
                ]);

                $events[] = [
                    'icon'  => $bill->icon ?? '🧾',
                    'type'  => 'bill_assigned',
                    'text'  => "New bill: {$bill->name}",
                    'sub'   => 'Ksh ' . number_format($amount) . ' every ' . $bill->frequency_ticks . ' game days — a new cost of your ' . $progress->chapterName() . ' chapter.',
                    'delta' => 0,
                ];
            }
        }
    }
}
