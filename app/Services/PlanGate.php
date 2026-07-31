<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;

/**
 * Central freemium gate. Free players can always play and learn — premium
 * unlocks depth and pace. Every limit check in the codebase goes through
 * this class so the rules live in ONE place.
 *
 * Limits are admin-tunable via the `plan_limits` Setting (JSON) — anything
 * not present falls back to the defaults below. A limit of 0 = unlimited.
 */
class PlanGate
{
    /** New accounts get full premium for this many real days (taste first, wall second). */
    const TRIAL_DAYS = 7;

    const DEFAULTS = [
        'free' => [
            'max_assets'            => 3,   // distinct active assets owned
            'max_active_deals'      => 1,   // pending investment deals
            'max_savings_schemes'   => 1,   // open savings schemes
            'max_loans'             => 1,   // active loans
            'catchup_ticks'         => 7,   // game days simulated per login
            'ai_per_day'            => 3,   // pesAI questions per real day
            'fun_per_game_month'    => 2,   // Fun World activities per game month
            'forum_topic_min_level' => 5,   // free players need this level to open topics
            'chama_create'          => 0,   // 0 = cannot create (joining is always free)
            'quests_per_day'        => 0,   // 0 = no free-specific override — falls back to
                                            // the GameSet Hub → Game Rules global pace value
                                            // (see PlanGate::maxQuestsPerDay())
            'spin_cooldown_days'    => 7,   // days between Spin Wheel plays
            'smart_tools_access'    => 0,   // 0 = locked — Bajeti/Lengo/Matumizi/Ukuaji/Mkopo/Faida
            'send_money_access'     => 0,   // 0 = cannot send friends money (borrowing/lending stays free)
            'pesatrail_games_per_day' => 3, // Pesa Trail games started per real day; 0 = unlimited
        ],
        'premium' => [
            'max_assets'            => 0,
            'max_active_deals'      => 0,
            'max_savings_schemes'   => 0,
            'max_loans'             => 2,   // matches existing game rule
            'catchup_ticks'         => 0,   // unused: premium catch-up is governed solely by
                                            // GameClock::maxCatchupTicks() (GameSet Hub → Game
                                            // Clock Speed), not this per-plan limit
            'ai_per_day'            => 0,
            'fun_per_game_month'    => 0,
            'forum_topic_min_level' => 0,
            'chama_create'          => 1,
            'quests_per_day'        => 0,   // unused: premium always uses the global pace value
            'spin_cooldown_days'    => 0,   // 0 = no extra cooldown (can spin every real day)
            'smart_tools_access'    => 1,   // premium always unlocked
            'send_money_access'     => 1,   // premium always unlocked
            'pesatrail_games_per_day' => 0, // 0 = unlimited — can also invite others unlimited times
        ],
    ];

    /** Full access: admins, active subs, school seats, free-for-all mode, or trial window. */
    public function isPremium(User $user): bool
    {
        if ($user->is_admin || $user->is_gameset) return true;
        if (Setting::get('free_for_all', '0') === '1') return true;
        if ($user->hasActiveSubscription()) return true;
        if ($user->hasActiveSchoolMembership()) return true;

        return $this->onTrial($user);
    }

    /** Trial length in real days — admin-tunable via the `trial_days` Setting. */
    public function trialDays(): int
    {
        return max(0, (int) Setting::get('trial_days', self::TRIAL_DAYS));
    }

    public function onTrial(User $user): bool
    {
        return $user->created_at && $user->created_at->gt(now()->subDays($this->trialDays()));
    }

    public function trialDaysLeft(User $user): int
    {
        if (!$user->created_at) return 0;
        // Carbon 3 diffInDays is a float — ceil so a partial day still counts as "left"
        $left = (int) ceil($this->trialDays() - $user->created_at->diffInDays(now()));
        return max(0, $left);
    }

    /** The limit that applies to this user for a key. 0 = unlimited. */
    public function limit(User $user, string $key): int
    {
        $tier   = $this->isPremium($user) ? 'premium' : 'free';
        $limits = $this->limits();

        return (int) ($limits[$tier][$key] ?? self::DEFAULTS[$tier][$key] ?? 0);
    }

    /** True when the user may add one more of something they currently have $currentCount of. */
    public function allows(User $user, string $key, int $currentCount): bool
    {
        $limit = $this->limit($user, $key);
        return $limit === 0 || $currentCount < $limit;
    }

    /**
     * Daily quest-start cap. GameSet Hub → Game Rules sets a global pace value
     * that applies to everyone by default; Admin → Free Plan Gates can set a
     * free-tier-specific override that TIGHTENS it further for unsubscribed
     * players only. Premium/trial always uses the global value, unchanged.
     * 0 = unlimited.
     */
    public function maxQuestsPerDay(User $user): int
    {
        $global = (int) Setting::get('max_quests_per_day', 0);
        if ($this->isPremium($user)) return $global;

        $override = (int) ($this->limits()['free']['quests_per_day'] ?? 0);
        return $override > 0 ? $override : $global;
    }

    /**
     * Standard JSON-ready denial payload. Frontends already render `error`;
     * `upgrade` lets them add a subscribe link.
     */
    public function deny(string $feature, int $limit): array
    {
        $messages = [
            'max_assets'          => "Free accounts can own {$limit} assets. Subscribe to grow your empire without limits!",
            'max_active_deals'    => "Free accounts can run {$limit} investment deal at a time. Subscribe to diversify like a real investor!",
            'max_savings_schemes' => "Free accounts get {$limit} savings goal. Subscribe to save toward everything at once!",
            'max_loans'           => "Free accounts can hold {$limit} loan. Subscribe to unlock more borrowing power!",
            'ai_per_day'          => "You've used your {$limit} free Mama Pesa questions today. Subscribe for unlimited coaching!",
            'fun_per_game_month'  => "Free accounts enjoy {$limit} Fun World treats per game month. Subscribe for unlimited fun!",
            'chama_create'        => 'Creating a chama is a premium feature — joining one is always free. Subscribe to lead your own!',
            'forum_topics'        => 'Reach the required level or subscribe to start new discussions. Replying is always free!',
            'spin_cooldown_days'  => "Free accounts spin once every {$limit} days. Subscribe to spin every day!",
            'smart_tools_access'  => 'The Money Toolkit (budget, savings, expense, loan & compounding calculators) is a premium feature. Subscribe to unlock it!',
            'send_money_access'   => 'Sending money to friends is a premium feature. Subscribe to unlock it!',
            'pesatrail_games_per_day' => "Free accounts can play {$limit} Pesa Trail game(s) a day. Subscribe to play — and invite friends — unlimited times!",
        ];

        return [
            'error'         => $messages[$feature] ?? 'This is a premium feature. Subscribe to unlock it!',
            'upgrade'       => true,
            'subscribe_url' => route('subscribe.index'),
        ];
    }

    /**
     * Free-vs-Premium feature comparison, computed live from the SAME limits
     * enforced in code — the single source of truth for the pricing page, so
     * marketing copy can never drift out of sync with what's actually gated.
     */
    public function comparisonRows(): array
    {
        $limits = $this->limits();
        $free   = $limits['free'];
        $prem   = $limits['premium'];
        $fmt    = fn (int $n, string $unit = '') => $n === 0 ? 'Unlimited' : "{$n}{$unit}";

        $questsGlobal = (int) Setting::get('max_quests_per_day', 0);
        $questsFree   = $free['quests_per_day'] > 0 ? $free['quests_per_day'] : $questsGlobal;

        $clock        = app(\App\Services\GameClock::class);
        $catchupPrem  = $clock->maxCatchupTicks();

        return [
            ['icon' => '📜', 'label' => 'Quests started per day',        'free' => $fmt($questsFree),              'premium' => $fmt($questsGlobal)],
            ['icon' => '⏳', 'label' => 'Catch-up after time away',       'free' => $fmt($free['catchup_ticks'], ' game days'), 'premium' => $fmt($catchupPrem, ' game days')],
            ['icon' => '🛒', 'label' => 'Marketplace assets owned',       'free' => $fmt($free['max_assets']),      'premium' => $fmt($prem['max_assets'])],
            ['icon' => '📈', 'label' => 'Active investment deals',        'free' => $fmt($free['max_active_deals']),'premium' => $fmt($prem['max_active_deals'])],
            ['icon' => '🏦', 'label' => 'Savings goals',                  'free' => $fmt($free['max_savings_schemes']), 'premium' => $fmt($prem['max_savings_schemes'])],
            ['icon' => '💳', 'label' => 'Active loans',                   'free' => $fmt($free['max_loans']),       'premium' => $fmt($prem['max_loans'])],
            ['icon' => '🤖', 'label' => 'pesAI coaching questions/day',   'free' => $fmt($free['ai_per_day']),      'premium' => $fmt($prem['ai_per_day'])],
            ['icon' => '🎡', 'label' => 'Fun World treats/game month',    'free' => $fmt($free['fun_per_game_month']), 'premium' => $fmt($prem['fun_per_game_month'])],
            ['icon' => '🎰', 'label' => 'Spin Wheel',                     'free' => $free['spin_cooldown_days'] > 0 ? "Every {$free['spin_cooldown_days']} days" : 'Every day', 'premium' => $prem['spin_cooldown_days'] > 0 ? "Every {$prem['spin_cooldown_days']} days" : 'Every day'],
            ['icon' => '💬', 'label' => 'Start forum discussions',        'free' => $free['forum_topic_min_level'] > 0 ? "From Level {$free['forum_topic_min_level']}" : 'Available immediately', 'premium' => $prem['forum_topic_min_level'] > 0 ? "From Level {$prem['forum_topic_min_level']}" : 'Available immediately'],
            ['icon' => '👥', 'label' => 'Create a Chama',                 'free' => $free['chama_create'] > 0 ? 'Yes' : 'Joining only', 'premium' => $prem['chama_create'] > 0 ? 'Yes' : 'Joining only'],
            ['icon' => '🧰', 'label' => 'Money Toolkit (6 calculators)',  'free' => $free['smart_tools_access'] > 0 ? 'Included' : 'Locked', 'premium' => $prem['smart_tools_access'] > 0 ? 'Included' : 'Locked'],
            ['icon' => '💸', 'label' => 'Send money to friends',          'free' => $free['send_money_access'] > 0 ? 'Included' : 'Locked', 'premium' => $prem['send_money_access'] > 0 ? 'Included' : 'Locked'],
            ['icon' => '🐍', 'label' => 'Pesa Trail games/day',           'free' => $fmt($free['pesatrail_games_per_day']), 'premium' => $fmt($prem['pesatrail_games_per_day'])],
        ];
    }

    /**
     * The same live comparisonRows() data, reframed as attractive Premium
     * perk cards (icon/title/desc) for the pricing page's "what you get"
     * section — no bare free-vs-premium table, just what Premium unlocks,
     * with the free-tier limit woven naturally into the sentence. Rows where
     * Premium doesn't actually differ from Free are skipped — never claim a
     * perk that isn't real.
     */
    public function pricingPerkCards(): array
    {
        $titles = [
            'Quests started per day'      => 'Unlimited Quests',
            'Catch-up after time away'    => 'Your City Never Waits',
            'Marketplace assets owned'    => 'Build a Real Empire',
            'Active investment deals'     => 'Diversify Freely',
            'Savings goals'               => 'Save Toward Everything',
            'Active loans'                => 'More Borrowing Power',
            'pesAI coaching questions/day'=> 'Unlimited AI Coaching',
            'Fun World treats/game month' => 'Unlimited Fun',
            'Spin Wheel'                  => 'Spin Every Day',
            'Start forum discussions'     => 'Start Discussions Instantly',
            'Create a Chama'              => 'Lead Your Own Chama',
            'Money Toolkit (6 calculators)'=> 'The Full Money Toolkit',
            'Send money to friends'       => 'Send Friends Money',
            'Pesa Trail games/day'        => 'Unlimited Pesa Trail',
        ];

        $descriptions = [
            'Quests started per day'      => fn($f,$p) => "Play as many quests as you want, whenever you want. Free accounts are capped at {$f} per day.",
            'Catch-up after time away'    => fn($f,$p) => "Come back after a week and your city catches up {$p} — free accounts only catch up {$f}.",
            'Marketplace assets owned'    => fn($f,$p) => "Own as many assets as you can afford. Free accounts are capped at {$f}.",
            'Active investment deals'     => fn($f,$p) => "Run as many investment deals at once as you like. Free accounts are limited to {$f} at a time.",
            'Savings goals'               => fn($f,$p) => "Open as many savings goals as you need. Free accounts get {$f}.",
            'Active loans'                => fn($f,$p) => "Hold up to {$p} loans at once — more borrowing power to grow faster. Free accounts get {$f}.",
            'pesAI coaching questions/day'=> fn($f,$p) => "Ask your AI money coach anything, anytime. Free accounts get {$f} questions a day.",
            'Fun World treats/game month' => fn($f,$p) => "Keep your mood — and your work income — topped up with unlimited Fun World visits. Free accounts get {$f} a game month.",
            'Spin Wheel'                  => fn($f,$p) => "Spin the wheel every single day. Free accounts only get one spin {$f}.",
            'Start forum discussions'     => fn($f,$p) => "Start new forum discussions right away, no level requirement. Free accounts need to reach a level first.",
            'Create a Chama'              => fn($f,$p) => "Start and lead your own savings chama, not just join one. That's a Premium-only feature.",
            'Money Toolkit (6 calculators)'=> fn($f,$p) => 'Bajeti, Lengo, Matumizi, Ukuaji, Mkopo & Faida — 6 real-world calculators, plus real-life bill reminders and savings tracking, all unlocked.',
            'Send money to friends'       => fn($f,$p) => 'Gift your friends money straight from your balance — a Premium-only feature.',
            'Pesa Trail games/day'        => fn($f,$p) => "Play Pesa Trail — and invite friends to join you — as many times as you want. Free accounts get {$f} games a day.",
        ];

        $cards = [];
        foreach ($this->comparisonRows() as $row) {
            if ($row['free'] === $row['premium']) continue; // no real perk here — skip rather than pad the list

            $descFn = $descriptions[$row['label']] ?? null;
            $cards[] = [
                'icon' => $row['icon'],
                'title' => $titles[$row['label']] ?? $row['label'],
                'desc'  => $descFn ? $descFn($row['free'], $row['premium']) : "{$row['label']}: {$row['premium']} with Premium (Free: {$row['free']}).",
            ];
        }

        return $cards;
    }

    /** Merged limits config (Setting `plan_limits` JSON over defaults). */
    public function limits(): array
    {
        $json   = Setting::get('plan_limits');
        $custom = $json ? (json_decode($json, true) ?: []) : [];

        return [
            'free'    => array_merge(self::DEFAULTS['free'],    $custom['free'] ?? []),
            'premium' => array_merge(self::DEFAULTS['premium'], $custom['premium'] ?? []),
        ];
    }
}
