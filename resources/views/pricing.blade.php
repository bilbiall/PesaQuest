<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing — PesaQuest Premium</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
        .tool-card { background: linear-gradient(135deg,rgba(255,255,255,0.04),rgba(255,255,255,0.01)); border: 1px solid rgba(255,255,255,0.08); }
        .plan-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); transition: all 0.2s; }
        .plan-card:hover { background: rgba(255,255,255,0.07); transform: translateY(-2px); }
        .plan-featured { background: linear-gradient(135deg,rgba(16,185,129,0.12),rgba(5,150,105,0.06)); border-color: rgba(16,185,129,0.4); }
    </style>
</head>
<body class="text-white antialiased">

    {{-- ── Navbar ── --}}
    <nav class="border-b border-white/8 sticky top-0 z-50" style="background:rgba(7,6,15,0.9);backdrop-filter:blur(16px);">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="group flex items-center gap-2">
                <img src="{{ asset('moski-logo.png') }}" alt="PesaQuest" class="h-10 w-auto rounded-xl"
                     onerror="this.style.display='none'">
                <span class="text-white font-black text-xl hidden sm:inline">PesaQuest</span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('subscribe.index') }}"
                   class="text-sm font-bold px-5 py-2.5 rounded-xl text-white transition-all hover:opacity-90"
                   style="background:linear-gradient(135deg,#10b981,#059669);">
                    Subscribe Now →
                </a>
                @else
                <a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-white transition-colors px-3 py-2">Sign In</a>
                <a href="{{ route('register') }}"
                   class="text-sm font-bold px-5 py-2.5 rounded-xl text-white transition-all hover:opacity-90"
                   style="background:linear-gradient(135deg,#10b981,#059669);">
                    Get Started →
                </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ── Hero ── --}}
    <section class="text-center py-16 sm:py-20 px-4">
        <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 mb-5 text-sm font-semibold"
             style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#6ee7b7;">
            💎 PesaQuest Premium
        </div>
        <h1 class="text-4xl sm:text-5xl font-black text-white max-w-2xl mx-auto leading-tight mb-4">
            Master Your Money.<br>
            <span style="background:linear-gradient(135deg,#10b981,#6ee7b7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                One Plan, All Tools.
            </span>
        </h1>
        <p class="text-slate-400 text-lg max-w-xl mx-auto">
            Get unlimited financial scenarios, Smart Money Tools, and leaderboard access — all powered by Safaricom M-Pesa.
        </p>
    </section>

    {{-- ── Plans ── --}}
    <section class="max-w-5xl mx-auto px-4 pb-16">
        @php
            $individualPlans = $plans->where('plan_type', 'individual')->values();
            $schoolPlans     = $plans->where('plan_type', 'school')->values();
        @endphp

        @if($individualPlans->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($individualPlans as $plan)
            @php
                $savings = match($plan->months) { 3=>'Save 11%', 6=>'Save 16%', 12=>'Save 30%', default=>null };
            @endphp
            <div class="plan-card {{ $plan->is_featured ? 'plan-featured' : '' }} rounded-2xl p-6 relative flex flex-col">
                @if($plan->is_featured)
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-xs font-black px-3 py-1 rounded-full shadow-lg"
                     style="background:linear-gradient(135deg,#10b981,#059669);color:white;">
                    MOST POPULAR
                </div>
                @endif
                @if($savings)
                <div class="absolute top-4 right-4 text-xs font-bold px-2 py-0.5 rounded-full"
                     style="background:rgba(245,158,11,0.2);border:1px solid rgba(245,158,11,0.3);color:#fcd34d;">
                    {{ $savings }}
                </div>
                @endif

                <p class="text-slate-400 text-xs font-semibold uppercase tracking-widest mb-2">{{ $plan->durationLabel() }}</p>
                <p class="text-4xl font-black text-white">Ksh {{ number_format($plan->price_kes) }}</p>
                <p class="text-slate-500 text-xs mt-0.5 mb-4">
                    @if($plan->months === 1) per month
                    @else Ksh {{ number_format(round($plan->price_kes / $plan->months)) }}/mo billed {{ $plan->durationLabel() }}
                    @endif
                </p>
                @if($plan->description)
                <p class="text-slate-400 text-sm mb-4 flex-1">{{ $plan->description }}</p>
                @else
                <div class="flex-1"></div>
                @endif

                @auth
                <a href="{{ route('subscribe.index') }}"
                   class="block text-center text-sm font-bold py-3 rounded-xl transition-all mt-auto {{ $plan->is_featured ? 'text-white hover:opacity-90' : 'hover:bg-white/10' }}"
                   style="{{ $plan->is_featured ? 'background:linear-gradient(135deg,#10b981,#059669);' : 'background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#cbd5e1;' }}">
                    Choose Plan →
                </a>
                @else
                <a href="{{ route('register') }}"
                   class="block text-center text-sm font-bold py-3 rounded-xl transition-all mt-auto {{ $plan->is_featured ? 'text-white hover:opacity-90' : 'hover:bg-white/10' }}"
                   style="{{ $plan->is_featured ? 'background:linear-gradient(135deg,#10b981,#059669);' : 'background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#cbd5e1;' }}">
                    Sign Up & Subscribe →
                </a>
                @endauth
            </div>
            @endforeach
        </div>
        @endif

        @if($schoolPlans->isNotEmpty())
        <div class="text-center mt-14 mb-6">
            <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);color:#a5b4fc;">
                🏫 For Schools
            </div>
            <p class="text-slate-400 text-sm mt-3 max-w-md mx-auto">Pooled seat subscriptions — every enrolled student gets full Premium access while the school plan is active.</p>
        </div>
        @php
            // Literal class strings only — Tailwind's build-time scanner can't
            // see a class name assembled at runtime (e.g. "lg:grid-cols-{{ $n }}"
            // never gets its CSS generated even though the HTML looks correct).
            $schoolGridClass = match(min(4, max(1, $schoolPlans->count()))) {
                1 => 'lg:grid-cols-1',
                2 => 'lg:grid-cols-2',
                3 => 'lg:grid-cols-3',
                default => 'lg:grid-cols-4',
            };
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $schoolGridClass }} gap-5">
            @foreach($schoolPlans as $plan)
            <div class="plan-card {{ $plan->is_featured ? 'plan-featured' : '' }} rounded-2xl p-6 relative flex flex-col" style="border-color:rgba(99,102,241,0.3);">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-xs font-black px-3 py-1 rounded-full shadow-lg flex items-center gap-1"
                     style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;">
                    🏫 SCHOOL PLAN
                </div>
                @if($plan->is_featured)
                <div class="absolute top-4 right-4 text-xs font-bold px-2 py-0.5 rounded-full"
                     style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.3);color:#6ee7b7;">
                    Most Popular
                </div>
                @endif

                <p class="text-slate-400 text-xs font-semibold uppercase tracking-widest mb-2 mt-2">{{ $plan->durationLabel() }} · {{ $plan->seats ?? '—' }} seats</p>
                <p class="text-4xl font-black text-white">Ksh {{ number_format($plan->price_kes) }}</p>
                <p class="text-slate-500 text-xs mt-0.5 mb-4">
                    total for {{ $plan->seats ?? 'all' }} student{{ ($plan->seats ?? 0) === 1 ? '' : 's' }}
                </p>
                @if($plan->description)
                <p class="text-slate-400 text-sm mb-4 flex-1">{{ $plan->description }}</p>
                @else
                <div class="flex-1"></div>
                @endif

                <a href="{{ auth()->check() ? route('subscribe.index') : route('register') }}"
                   class="block text-center text-sm font-bold py-3 rounded-xl transition-all mt-auto hover:opacity-90"
                   style="background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;">
                    Contact Us to Enroll →
                </a>
            </div>
            @endforeach
        </div>
        @endif

        <p class="text-center text-slate-600 text-sm mt-10">
            🔒 All plans paid securely via <strong class="text-slate-400">Safaricom M-Pesa</strong>. No credit card needed.
        </p>
    </section>

    {{-- ── What's included ── --}}
    <section class="max-w-5xl mx-auto px-4 pb-16">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-black text-white mb-2">Everything Included in Premium</h2>
            <p class="text-slate-400">One subscription unlocks the full PesaQuest experience. Free accounts always keep the core game — Premium removes the pace limits.</p>
            @if($trialDays > 0)
            <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 mt-4 text-sm font-semibold" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#6ee7b7;">
                🎁 New accounts get {{ $trialDays }} day{{ $trialDays === 1 ? '' : 's' }} of full Premium free, automatically.
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-12">
            @foreach($perks as $perk)
            <div class="tool-card rounded-xl p-5 flex gap-4">
                <span class="text-2xl shrink-0 mt-0.5">{{ $perk['icon'] }}</span>
                <div>
                    <p class="text-white font-bold text-sm mb-1">{{ $perk['title'] }}</p>
                    <p class="text-slate-500 text-xs leading-relaxed">{{ $perk['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Smart Money Tools deep-dive ── --}}
        <div class="rounded-2xl p-6 sm:p-8 mb-8"
             style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(139,92,246,0.04));border:1px solid rgba(99,102,241,0.2);">
            <div class="flex items-start gap-4 mb-6">
                <span class="text-4xl">🧠</span>
                <div>
                    <h3 class="text-white font-black text-xl">Smart Money Tools</h3>
                    <p class="text-slate-400 text-sm mt-1">
                        Premium members get exclusive access to 6 real-world financial calculators built directly into the dashboard.
                        These aren't just games — they're practical tools that help you manage actual money.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach([
                    [
                        'icon' => '📊',
                        'name' => 'Bajeti — Budget Builder',
                        'color_start' => 'rgba(99,102,241,0.15)',
                        'color_end'   => 'rgba(99,102,241,0.05)',
                        'border'      => 'rgba(99,102,241,0.25)',
                        'text'        => '#a5b4fc',
                        'desc' => 'Build your personal budget using Kenya\'s 50/30/20 rule. Allocate income across needs, wants, and savings with interactive sliders. Get personalized Kenyan wisdom based on your ratios.',
                        'features' => ['50/30/20 rule engine', 'Kenyan expense categories', 'Monthly savings projection', 'Budget health score'],
                    ],
                    [
                        'icon' => '🎯',
                        'name' => 'Lengo — Savings Goal',
                        'color_start' => 'rgba(16,185,129,0.15)',
                        'color_end'   => 'rgba(16,185,129,0.05)',
                        'border'      => 'rgba(16,185,129,0.25)',
                        'text'        => '#6ee7b7',
                        'desc' => 'Set a savings goal, track progress with a live SVG ring chart, and calculate exactly how much to save daily/weekly/monthly to hit your target on time.',
                        'features' => ['Visual progress ring', 'Daily/weekly/monthly breakdown', 'Days-to-goal counter', 'Smart motivational tips'],
                    ],
                    [
                        'icon' => '📱',
                        'name' => 'Matumizi — Expense Tracker',
                        'color_start' => 'rgba(245,158,11,0.15)',
                        'color_end'   => 'rgba(245,158,11,0.05)',
                        'border'      => 'rgba(245,158,11,0.25)',
                        'text'        => '#fcd34d',
                        'desc' => 'Log your daily expenses by category and see exactly where your money goes. Category breakdown bars show spending patterns at a glance. Data persists on your device.',
                        'features' => ['Category-based logging', 'Spending breakdown bars', 'Daily/all-time totals', 'Saved across sessions'],
                    ],
                    [
                        'icon' => '📈',
                        'name' => 'Ukuaji — Growth Calculator',
                        'color_start' => 'rgba(239,68,68,0.12)',
                        'color_end'   => 'rgba(239,68,68,0.04)',
                        'border'      => 'rgba(239,68,68,0.2)',
                        'text'        => '#fca5a5',
                        'desc' => 'See how compound interest grows your money across Kenya\'s top investment vehicles: M-Pesa, Bank, SACCO, Treasury Bills, and NSE stocks — side by side.',
                        'features' => ['5 investment vehicles', 'Compound growth engine', 'Side-by-side comparison', 'M-Pesa vs bank vs T-Bills'],
                    ],
                    [
                        'icon' => '🏦',
                        'name' => 'Mkopo — Loan Planner',
                        'color_start' => 'rgba(220,38,38,0.15)',
                        'color_end'   => 'rgba(220,38,38,0.05)',
                        'border'      => 'rgba(220,38,38,0.25)',
                        'text'        => '#fca5a5',
                        'desc' => 'See the TRUE cost of any loan before you borrow — enter the amount, rate and term to get your monthly payment and total interest, so credit decisions are never a surprise.',
                        'features' => ['Monthly payment calculator', 'Total repayment & interest', 'Any rate or term', 'Decide before you borrow'],
                    ],
                    [
                        'icon' => '💹',
                        'name' => 'Faida — Compound Interest',
                        'color_start' => 'rgba(99,102,241,0.15)',
                        'color_end'   => 'rgba(99,102,241,0.05)',
                        'border'      => 'rgba(99,102,241,0.25)',
                        'text'        => '#a5b4fc',
                        'desc' => "Add a starting amount plus monthly contributions and watch compound interest do the work — Albert Einstein's \"8th wonder of the world\", visualised for your own numbers.",
                        'features' => ['Initial + monthly contributions', 'Multi-year projection', 'Multiple rate comparison', 'See compounding in action'],
                    ],
                ] as $tool)
                <div class="rounded-xl p-5"
                     style="background:linear-gradient(135deg,{{ $tool['color_start'] }},{{ $tool['color_end'] }});border:1px solid {{ $tool['border'] }};">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-2xl">{{ $tool['icon'] }}</span>
                        <h4 class="font-bold text-sm" style="color:{{ $tool['text'] }};">{{ $tool['name'] }}</h4>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed mb-3">{{ $tool['desc'] }}</p>
                    <ul class="space-y-1">
                        @foreach($tool['features'] as $feat)
                        <li class="text-xs flex items-center gap-2" style="color:{{ $tool['text'] }};">
                            <span class="w-1 h-1 rounded-full shrink-0" style="background:{{ $tool['text'] }};"></span>
                            {{ $feat }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── FAQ ── --}}
    <section class="max-w-2xl mx-auto px-4 pb-16" x-data="{open:null}">
        <h2 class="text-2xl font-black text-white text-center mb-8">Common Questions</h2>
        <div class="space-y-3">
            @foreach([
                ['How do I pay?', 'Select a plan, enter your Safaricom number, and you\'ll receive an M-Pesa PIN prompt on your phone. No cards or bank account needed.'],
                ['Can I cancel anytime?', 'Yes. Your subscription runs until the end of the paid period. Contact us or use the admin area to manage your plan.'],
                ['What happens when it expires?', 'You\'ll revert to the free plan with limited scenario access. Your progress and badges are always saved.'],
                ['Is my data safe?', 'Absolutely. Payments go directly through Safaricom Daraja — we never see your PIN or M-Pesa account details.'],
                ['Can I upgrade my plan?', 'Yes. Upgrading activates a new plan immediately and cancels the old one. Contact support for proration queries.'],
            ] as $i => [$q, $a])
            <div class="rounded-xl overflow-hidden" style="border:1px solid rgba(255,255,255,0.08);">
                <button @click="open = (open === {{ $i }}) ? null : {{ $i }}"
                        class="w-full text-left px-5 py-4 flex items-center justify-between text-sm font-semibold text-white transition-colors hover:bg-white/4">
                    <span>{{ $q }}</span>
                    <svg class="w-4 h-4 text-slate-500 shrink-0 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === {{ $i }}" x-collapse class="px-5 pb-4 text-sm text-slate-400 leading-relaxed">
                    {{ $a }}
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── CTA Banner ── --}}
    <section class="max-w-3xl mx-auto px-4 pb-20 text-center">
        <div class="rounded-2xl p-8 sm:p-12"
             style="background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(5,150,105,0.06));border:1px solid rgba(16,185,129,0.25);">
            <h2 class="text-3xl font-black text-white mb-3">Ready to take control of your money?</h2>
            <p class="text-slate-400 mb-6">Join thousands of Kenyans building better financial habits with PesaQuest.</p>
            @auth
            <a href="{{ route('subscribe.index') }}"
               class="inline-block text-white font-black px-8 py-4 rounded-2xl text-lg transition-all hover:shadow-xl hover:-translate-y-1"
               style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 0 40px rgba(16,185,129,0.3);">
                Choose a Plan →
            </a>
            @else
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('register') }}"
                   class="inline-block text-white font-black px-8 py-4 rounded-2xl text-lg transition-all hover:shadow-xl hover:-translate-y-1"
                   style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 0 40px rgba(16,185,129,0.3);">
                    Create Account & Subscribe →
                </a>
                <a href="{{ route('login') }}"
                   class="inline-block font-bold px-8 py-4 rounded-2xl text-lg transition-all hover:bg-white/10"
                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#cbd5e1;">
                    Sign In
                </a>
            </div>
            @endauth
        </div>
    </section>

    <footer class="text-center pb-10 text-slate-600 text-sm">
        © {{ date('Y') }} PesaQuest by Moski. · <a href="{{ route('landing') }}" class="hover:text-slate-400">Home</a>
    </footer>

</body>
</html>
