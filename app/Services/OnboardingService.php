<?php

namespace App\Services;

use App\Models\Setting;

/**
 * The first-time-user wizard shown once on the dashboard, explaining how
 * PesaQuest works. Steps are admin-configurable via GameSet Hub → Onboarding
 * Wizard, stored as Settings JSON and merged over these defaults — exactly
 * like CareerService's fields/tracks. Nothing about the step count or
 * content is hardcoded anywhere else in the app.
 */
class OnboardingService
{
    /** Fallback steps — used only until an admin saves custom ones. */
    const DEFAULT_STEPS = [
        [
            'icon' => '🎮', 'category' => 'Welcome',
            'title' => 'Welcome to Pesa City!',
            'body' => "Pesa City is a living world that moves in \"game days\" (ticks) even while you're away. Every choice you make — a job, a bill, a purchase — plays out over time, just like real life. Your goal: grow your net worth and climb through six life chapters, from Student to Elder.",
        ],
        [
            'icon' => '💼', 'category' => 'Earn',
            'title' => 'Build Your Career',
            'body' => "Head to the Opportunity Hub to take free courses and get hired. Full-time jobs are your main income, part-time jobs let you juggle up to two at once, and freelance gigs pay a one-off amount for quick work. Pay only arrives when you \"Report to Work\" — miss too many paydays and it's forfeited, so stay active!",
        ],
        [
            'icon' => '🛒', 'category' => 'Spend & Own',
            'title' => 'Buy Assets in the Marketplace',
            'body' => "Spend your KES on things that matter — from small gadgets to a car or a plot of land. Big purchases like vehicles and property can be financed: pay a deposit now, then a monthly installment with interest. It's convenient, but credit always costs more than cash — the Marketplace shows you exactly how much.",
        ],
        [
            'icon' => '🏦', 'category' => 'Grow & Borrow',
            'title' => 'Save, Invest & Borrow Wisely',
            'body' => "Open a savings goal at the bank and it earns interest automatically. Equity Square lets you back investment deals for a shot at real returns (with real risk). Loans are available too — but every missed installment hurts your credit score, and every on-time payment builds it.",
        ],
        [
            'icon' => '🧾', 'category' => 'Stay On Top',
            'title' => 'Bills & Credit Score',
            'body' => "Bills — rent, data, insurance — auto-deduct from your balance on schedule. Falling behind hurts your credit score, which controls what loans and financing you can access later. Check your dashboard's Bills panel often so nothing sneaks up on you.",
        ],
        [
            'icon' => '📜', 'category' => 'Level Up',
            'title' => 'Quests Guide Your Progress',
            'body' => "Quests are goals the game detects automatically the moment you complete them — no menus to submit. They reward XP (and sometimes KES), and finishing them is the fastest way to level up and unlock new parts of the city.",
        ],
        [
            'icon' => '🤝', 'category' => 'Connect',
            'title' => "You're Not Playing Alone",
            'body' => "Join a chama to save as a group, earn badges for milestones, and check the leaderboard to see how you rank. Whenever you're unsure what to do next, your dashboard's \"Current Quest\" card always points you to your next step.",
        ],
        [
            'icon' => '🧮', 'category' => 'Smart Money Tools',
            'title' => 'Real Calculators, Not Just a Game',
            'body' => "Your dashboard has a Smart Money Tools panel with six real calculators: Bajeti Smart (build a budget), Lengo Saver (track multiple savings goals), Matumizi Track (log your expenses), Ukuaji Grow (see savings compound over time), Mkopo Planner (find a loan's true cost before you borrow) and Faida Compounder (visualise compound interest). Use them on real numbers from your own life, not just Pesa City.",
        ],
        [
            'icon' => '📲', 'category' => 'Install',
            'title' => 'Install PesaQuest in 3 Easy Steps',
            'body' => "Get the full-screen, app-like experience — no browser bars, faster loading, and offline-friendly. 1) Open your browser menu — tap the ⋮ or Share icon. 2) Choose \"Install app\" (Android/Chrome) or \"Add to Home Screen\" (iPhone/Safari). 3) Confirm — PesaQuest now sits on your home screen like any other app.",
        ],
    ];

    private static ?array $stepsCache = null;

    /** All onboarding wizard steps, admin-configured or defaults. */
    public static function steps(): array
    {
        if (self::$stepsCache !== null) return self::$stepsCache;

        $saved = [];
        try {
            $saved = json_decode(Setting::get('onboarding_wizard_steps', '') ?: '[]', true) ?: [];
        } catch (\Throwable $e) {
            // settings table unavailable (fresh install) — use defaults
        }

        return self::$stepsCache = !empty($saved) ? $saved : self::DEFAULT_STEPS;
    }

    /**
     * Shared gate used by every page that can render the wizard (Dashboard,
     * World, ...) so the decision never drifts between them. $needsCareerQuiz
     * is passed in rather than computed here since each caller already has
     * its own UserProgress loaded differently.
     */
    public static function shouldShow(\App\Models\User $user, bool $needsCareerQuiz): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('users', 'onboarding_completed_at')
            && is_null($user->onboarding_completed_at)
            && !$needsCareerQuiz;
    }
}
