<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PesaQuest — Dashboard</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PesaQuest">
    <link rel="apple-touch-icon" href="/moski-logo.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; }
        [x-cloak] { display: none !important; }

        .dash-bg {
            background:
                radial-gradient(ellipse at top left,    rgba(99,102,241,0.10) 0%, transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(139,92,246,0.08) 0%, transparent 50%),
                #07060f;
        }

        /* Cards */
        .card {
            background: #110f28;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .card-hover { transition: all 0.22s ease; }
        .card-hover:hover {
            border-color: rgba(99,102,241,0.4);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px -6px rgba(99,102,241,0.25);
        }

        /* Desktop game grid — explicit media query guarantees visibility */
        .desktop-game-grid { display: none; }
        @media (min-width: 768px) {
            .desktop-game-grid {
                display: grid !important;
                grid-template-columns: 220px 1fr 1fr;
                gap: 1rem;
            }
        }
        .desktop-stats-strip { display: none; }
        @media (min-width: 768px) {
            .desktop-stats-strip {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr);
                gap: 0.75rem;
            }
        }
        @media (min-width: 1280px) {
            .desktop-stats-strip { grid-template-columns: repeat(8, 1fr); }
        }
        /* Below-map 4-col strip — cards sit at natural height, don't stretch */
        .desktop-bottom-strip { display: none; }
        @media (min-width: 768px) {
            .desktop-bottom-strip {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr);
                gap: 0.75rem;
                align-items: start;
            }
        }
        /* Chapter + City News 2-col row */
        .desktop-2col-strip { display: none; }
        @media (min-width: 768px) {
            .desktop-2col-strip {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
                align-items: start;
            }
        }
        /* Hide scrollbar but allow scroll */
        .scrollbar-hide { scrollbar-width: none; -ms-overflow-style: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .desktop-only { display: none; }
        @media (min-width: 768px) { .desktop-only { display: block !important; } }
        .mobile-game-layout { display: block; }
        @media (min-width: 768px) { .mobile-game-layout { display: none !important; } }

        /* HUD bar */
        .hud-bar { background: rgba(7,6,15,0.96); border-bottom: 1px solid rgba(255,255,255,0.06); }

        /* Stat chip */
        .stat-chip { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); }

        /* XP bar shimmer */
        .xp-fill {
            background: linear-gradient(90deg, #6366f1, #a78bfa, #f59e0b);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        /* Play button */
        .play-btn {
            background: linear-gradient(135deg, #6366f1, #a78bfa);
            box-shadow: 0 0 18px rgba(99,102,241,0.5);
            animation: playGlow 2.5s ease-in-out infinite;
        }
        @keyframes playGlow {
            0%,100%{box-shadow:0 0 14px rgba(99,102,241,0.4);}
            50%{box-shadow:0 0 32px rgba(99,102,241,0.7);}
        }
        .play-btn:hover { box-shadow:0 0 40px rgba(99,102,241,0.8); transform:scale(1.04); }

        /* Nav tabs */
        .nav-tab { border-bottom: 2px solid transparent; transition: all 0.18s; white-space:nowrap; }
        .nav-tab:hover { color:white; border-bottom-color: rgba(99,102,241,0.5); }
        .nav-tab.active { color:white; border-bottom-color: #6366f1; }

        /* Room items */
        .room-item { background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.12); transition:all 0.2s; }
        .room-item:hover { background:rgba(99,102,241,0.2); border-color:rgba(99,102,241,0.5); transform:scale(1.06); box-shadow:0 0 16px rgba(99,102,241,0.3); }

        /* City card */
        .city-card { background: linear-gradient(160deg, #0d1f16, #091510); border:1px solid rgba(16,185,129,0.4); }

        /* Bonus btn */
        .bonus-btn { background:linear-gradient(135deg,#10b981,#059669); animation:bonusPulse 2s ease-in-out infinite; }
        @keyframes bonusPulse { 0%,100%{box-shadow:0 0 16px rgba(16,185,129,0.35);} 50%{box-shadow:0 0 32px rgba(16,185,129,0.55);} }
        .bonus-btn:hover { box-shadow:0 0 40px rgba(16,185,129,0.7); transform:scale(1.03); }

        /* Toast */
        .toast { background:rgba(16,185,129,0.95); backdrop-filter:blur(12px); animation:slideIn .4s ease, fadeOut .5s ease 2.5s forwards; }
        @keyframes slideIn  { from{transform:translateX(120%);opacity:0} to{transform:translateX(0);opacity:1} }
        @keyframes fadeOut  { to{opacity:0;transform:translateX(120%)} }

        /* Streak dot */
        .streak-dot.lit { background:linear-gradient(to top,#ea580c,#fbbf24); box-shadow:0 0 8px rgba(245,158,11,0.5); }

        /* Invest card */
        .invest-card { background:linear-gradient(135deg,rgba(245,158,11,0.1),rgba(251,191,36,0.04)); border:1px solid rgba(245,158,11,0.25); }
        .invest-bar  { background:linear-gradient(90deg,#f59e0b,#fbbf24); transition:width 1.5s cubic-bezier(0.34,1.56,0.64,1); }

        /* Notification items */
        .notif-item { border-left:3px solid; }
        .notif-investment       { border-color:#f59e0b; background:rgba(245,158,11,0.06); }
        .notif-investment_ready { border-color:#f59e0b; background:rgba(245,158,11,0.06); }
        .notif-success  { border-color:#10b981; background:rgba(16,185,129,0.06); }
        .notif-badge    { border-color:#8b5cf6; background:rgba(139,92,246,0.06); }
        .notif-info     { border-color:#6366f1; background:rgba(99,102,241,0.06); }
        .notif-warning  { border-color:#ef4444; background:rgba(239,68,68,0.06); }

        /* Pulse dot */
        .pulse-dot { animation:pulseGlow 2s ease-in-out infinite; }
        @keyframes pulseGlow { 0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,0.5);} 50%{box-shadow:0 0 0 6px rgba(16,185,129,0);} }

        /* Modal */
        .modal-overlay { background:rgba(0,0,0,0.78); backdrop-filter:blur(8px); }
        .modal-box { background:#12111f; border:1px solid rgba(99,102,241,0.3); border-radius:1.5rem; }

        /* Confetti */
        @keyframes confettiFall { to{transform:translateY(110vh) rotate(720deg);opacity:0;} }

        /* Level bounce */
        @keyframes levelBounce { from{opacity:0;transform:scale(0.3) rotate(-15deg)} to{opacity:1;transform:scale(1) rotate(0)} }

        /* Bottom nav */
        .bottom-nav { background:rgba(7,6,15,0.98); border-top:1px solid rgba(255,255,255,0.07); }

        /* Quick action tile */
        .qa-tile { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); transition:all 0.18s; }
        .qa-tile:hover { background:rgba(99,102,241,0.18); border-color:rgba(99,102,241,0.45); transform:translateY(-2px); }

        /* Stats strip */
        .stat-tile { background:#110f28; border:1px solid rgba(255,255,255,0.12); }
        .stat-tile:hover { border-color:rgba(99,102,241,0.5); background:#1a1740; }

        /* Mobile padding for bottom nav */
        @media (max-width: 767px) { .page-body { padding-bottom: 5.5rem; } }

        /* Character avatar pulse ring */
        @keyframes avatarRing { 0%,100%{box-shadow:0 0 0 3px rgba(99,102,241,0.25),0 0 28px rgba(99,102,241,0.5);}  50%{box-shadow:0 0 0 5px rgba(99,102,241,0.15),0 0 40px rgba(99,102,241,0.65);} }
        .avatar-pulse { animation:avatarRing 3s ease-in-out infinite; }

        /* City card hover */
        .city-card img { transition:transform 0.5s ease; }
        .city-card:hover img { transform:scale(1.04); }

        /* Room section dark floor */
        .room-floor { position:absolute;bottom:0;left:0;right:0;height:30%;background:linear-gradient(to top,rgba(16,185,129,0.06),transparent);pointer-events:none; }

        /* Pulse spin */
        @keyframes pulse-spin { 0%,100%{transform:scale(1) rotate(0)} 50%{transform:scale(1.2) rotate(15deg)} }

        /* Chapter bar */
        .chapter-bar { transition: width 0.7s ease; }
    </style>
</head>

<body class="dash-bg min-h-screen text-white font-sans antialiased page-body"
      x-data="dashboard()" x-init="init()">

{{-- FIXED OVERLAYS & MODALS --}}

{{-- Life Sim Catch-Up --}}
@include('game.partials.life-sim-catchup')

{{-- Toast --}}
<div x-show="toast" x-cloak
     class="toast fixed top-4 right-4 z-[9999] text-white font-bold px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-2">
    <span>✅</span><span x-text="toastMsg"></span>
</div>

{{-- Notification side panel --}}
<div x-show="showNotifPanel" x-cloak @click.self="showNotifPanel=false"
     class="modal-overlay fixed inset-0 z-[200] flex justify-end"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="w-full max-w-sm h-full bg-[#0d0c1e] border-l border-white/10 overflow-y-auto p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-250" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="font-black text-sm sm:text-lg">Notifications</h2>
            <button @click="showNotifPanel=false" class="text-gray-400 hover:text-white p-1">✕</button>
        </div>
        @forelse($recentNotifications as $notif)
        <div class="notif-{{ $notif->type }} notif-item rounded-xl px-3 py-3 sm:py-4 mb-3 flex items-start gap-3">
            <span class="text-base sm:text-xl flex-shrink-0">{{ $notif->icon }}</span>
            <div class="flex-1">
                <div class="text-xs sm:text-sm font-bold">{{ $notif->title }}</div>
                <div class="text-[11px] sm:text-xs text-gray-400 mt-1">{{ $notif->body }}</div>
                <div class="text-[9px] sm:text-[10px] text-gray-600 mt-1.5">{{ $notif->created_at->diffForHumans() }}</div>
            </div>
            @if(!$notif->is_read)<div class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0 mt-1 pulse-dot"></div>@endif
        </div>
        @empty
        <div class="text-center py-16 text-gray-500">
            <div class="flex justify-center mb-3"><x-icon name="bell" class="w-9 h-9 sm:w-12 sm:h-12 text-gray-600" /></div>
            <p class="text-xs sm:text-sm">All caught up!</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Confetti container --}}
<div id="dash-confetti" class="fixed inset-0 z-[300] pointer-events-none overflow-hidden"></div>

{{-- First-time onboarding wizard --}}
@if($showOnboardingWizard ?? false)
<div x-data="onboardingWizard(@json($onboardingSteps))" x-show="visible" x-cloak
     class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9995;overflow-y:auto;overscroll-behavior:contain;">
    <div class="max-w-lg w-full bg-[#12111f] border border-indigo-500/35 rounded-3xl p-5 sm:p-8 my-auto relative">
        <button @click="close()" title="Close wizard"
                class="absolute top-3 right-3 sm:top-4 sm:right-4 w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/10 transition-colors">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 sm:w-4 sm:h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Step progress dots --}}
        <div class="flex items-center gap-1.5 mb-4 sm:mb-5">
            <template x-for="(s, i) in steps" :key="i">
                <div class="h-1.5 flex-1 rounded-full transition-colors" :style="i <= step ? 'background:#6366f1;' : 'background:rgba(255,255,255,.1);'"></div>
            </template>
        </div>

        <template x-for="(s, i) in steps" :key="'step-'+i">
            <div x-show="step === i">
                <div class="text-4xl sm:text-6xl mb-3 sm:mb-4 text-center" x-text="s.icon"></div>
                <p class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest text-indigo-400 text-center mb-1.5" x-text="s.category"></p>
                <h2 class="text-lg sm:text-2xl font-black mb-2 sm:mb-3 text-center" x-text="s.title"></h2>
                <p class="text-gray-400 text-xs sm:text-sm leading-relaxed text-center" x-text="s.body"></p>
            </div>
        </template>

        <div class="flex items-center justify-between gap-3 mt-6 sm:mt-8">
            <button @click="close()" class="px-4 py-2.5 sm:px-5 sm:py-3 rounded-2xl text-xs sm:text-sm font-bold text-gray-500 hover:text-white hover:bg-white/5 transition-colors">
                Close wizard
            </button>
            <button @click="next()"
                    class="flex-1 max-w-[12rem] bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black py-2.5 sm:py-3 rounded-2xl text-xs sm:text-sm shadow-xl shadow-indigo-500/30 hover:scale-105 transition-transform">
                <span x-text="step < steps.length - 1 ? 'Next' : 'Start Playing!'"></span>
            </button>
        </div>
        <p class="text-[10px] sm:text-[11px] text-gray-600 text-center mt-3">Step <span x-text="step+1"></span> of <span x-text="steps.length"></span></p>
    </div>
</div>
@endif

{{-- Level-up overlay --}}
@if($leveledUp)
@php $levelIcons=['1'=>'🌱','2'=>'🌿','3'=>'🍀','4'=>'⭐','5'=>'🌟','6'=>'🔥','7'=>'💎','8'=>'👑','9'=>'🏆','10'=>'✨']; @endphp
<div id="levelup-overlay" class="modal-overlay fixed inset-0 flex items-center justify-center" style="z-index:9991;"
     onclick="document.getElementById('levelup-overlay').style.display='none'">
    <div class="text-center max-w-xs px-5 py-6 sm:px-8 sm:py-10" onclick="event.stopPropagation()">
        <div class="text-5xl sm:text-8xl mb-3" style="animation:levelBounce .6s cubic-bezier(0.34,1.56,0.64,1) both;">
            {{ $levelIcons[(string)min($progress->level,10)] ?? '🌟' }}
        </div>
        <div class="text-xs sm:text-sm font-black text-purple-400 uppercase tracking-widest mb-2">Level Up!</div>
        <div class="text-3xl sm:text-5xl font-black mb-3" style="background:linear-gradient(135deg,#6366f1,#a78bfa,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
            Level {{ $progress->level }}
        </div>
        <p class="text-gray-400 text-xs sm:text-sm mb-5 sm:mb-6 leading-relaxed">Your financial wisdom is growing. Keep making smart choices!</p>
        <button onclick="document.getElementById('levelup-overlay').style.display='none'"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black px-6 py-2.5 sm:px-8 sm:py-3 rounded-2xl text-xs sm:text-sm hover:scale-105 transition-transform">
            Let's keep going!
        </button>
    </div>
</div>
@endif

{{-- Onboarding overlay --}}
@if($needsOnboarding)
<div class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9990;overflow-y:auto;overscroll-behavior:contain;">
    <div class="max-w-md w-full bg-[#12111f] border border-indigo-500/35 rounded-3xl p-6 sm:p-10 text-center my-auto">
        <div class="mb-3 sm:mb-4 animate-bounce flex justify-center"><x-icon name="rocket" class="w-11 h-11 sm:w-16 sm:h-16 text-indigo-400" /></div>
        <h2 class="text-lg sm:text-2xl font-black mb-2">Start Your Career Journey</h2>
        <p class="text-gray-400 text-xs sm:text-sm leading-relaxed mb-5 sm:mb-6">Take a quick 5-question quiz and we'll match you to the perfect career in PesaQuest's world.</p>
        <a href="{{ route('life.quiz') }}"
           class="block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black py-3 sm:py-4 rounded-2xl text-sm sm:text-base shadow-xl shadow-indigo-500/40 hover:scale-105 transition-transform flex items-center justify-center gap-2">
            <x-icon name="pencil" class="w-3.5 h-3.5 sm:w-4 sm:h-4" /> Take the Career Quiz
        </a>
        <p class="text-[10px] sm:text-[11px] text-gray-600 mt-3">Takes 2 minutes — Fully personalised</p>
    </div>
</div>
@endif

{{-- Monthly report card --}}
@if(!empty($monthlyReport))
@php
    $rpt = $monthlyReport;
    $hasGrade = $rpt['has_grade'] ?? !empty($rpt['grade']);
    $gradeColor = !$hasGrade ? '#6b7280' : match($rpt['grade']) { 'A'=>'#10b981','B'=>'#22c55e','C'=>'#f59e0b',default=>'#ef4444' };
    $gradeMsg   = !$hasGrade ? "Not much financial activity this game month — too little to grade meaningfully. Get a job or open a savings goal to see a real report next month." : match($rpt['grade']) {
        'A' => "Excellent! You saved 30%+ of your income. You're building real wealth.",
        'B' => 'Good job. 15–30% savings keeps you ahead. Now aim for 30%.',
        'C' => 'Break-even month. Look at your expenses and find one thing to cut.',
        default => 'Spending exceeded income. Review bills and reduce where possible.',
    };
@endphp
<div id="report-overlay" class="modal-overlay fixed inset-0 flex items-center justify-center p-4"
     style="z-index:9990;overflow-y:auto;overscroll-behavior:contain;"
     onclick="document.getElementById('report-overlay').style.display='none'"
     x-data="{ showIncome: false, showExpense: false }">
    <div class="max-w-sm w-full my-auto bg-[#12111f] border border-white/10 rounded-3xl p-5 sm:p-8 text-center" onclick="event.stopPropagation()">
        <div class="text-4xl sm:text-7xl font-black leading-none mb-1" style="color:{{ $gradeColor }};">{{ $hasGrade ? $rpt['grade'] : '–' }}</div>
        <div class="text-sm sm:text-base font-black text-white mb-1">Monthly Report Card</div>
        <div class="text-[11px] sm:text-xs text-gray-500 mb-4 sm:mb-5">{{ $rpt['months'] }} game month{{ $rpt['months']>1?'s':'' }} completed</div>
        <div class="bg-white/4 border border-white/7 rounded-2xl p-3 sm:p-4 mb-4 text-left space-y-2 text-[11px] sm:text-xs">
            <div class="flex justify-between items-center">
                <button type="button" @click="showIncome = !showIncome" class="flex items-center gap-1 text-gray-400 hover:text-white transition-colors" @if(empty($rpt['income_items'])) disabled @endif>
                    <span>Total Income</span>
                    @if(!empty($rpt['income_items']))
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" class="transition-transform" :class="showIncome ? 'rotate-180' : ''"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    @endif
                </button>
                <span class="font-black text-emerald-400">+Ksh {{ number_format($rpt['total_in']) }}</span>
            </div>
            @if(!empty($rpt['income_items']))
            <div x-show="showIncome" x-transition x-cloak class="!mt-0 space-y-1.5 pl-2 border-l border-emerald-500/20">
                @foreach($rpt['income_items'] as $item)
                <div class="flex justify-between items-start gap-2 py-1">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-300 text-[11px] font-semibold truncate">{{ $item['icon'] }} {{ $item['text'] }}</p>
                        @if($item['sub'])<p class="text-gray-600 text-[10px] leading-snug">{{ $item['sub'] }}</p>@endif
                    </div>
                    <span class="text-emerald-400/90 text-[11px] font-bold flex-shrink-0">+{{ number_format($item['amount']) }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="flex justify-between items-center">
                <button type="button" @click="showExpense = !showExpense" class="flex items-center gap-1 text-gray-400 hover:text-white transition-colors" @if(empty($rpt['expense_items'])) disabled @endif>
                    <span>Total Expenses</span>
                    @if(!empty($rpt['expense_items']))
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" class="transition-transform" :class="showExpense ? 'rotate-180' : ''"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                    @endif
                </button>
                <span class="font-black text-red-400">-Ksh {{ number_format($rpt['total_out']) }}</span>
            </div>
            @if(!empty($rpt['expense_items']))
            <div x-show="showExpense" x-transition x-cloak class="!mt-0 space-y-1.5 pl-2 border-l border-red-500/20">
                @foreach($rpt['expense_items'] as $item)
                <div class="flex justify-between items-start gap-2 py-1">
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-300 text-[11px] font-semibold truncate">{{ $item['icon'] }} {{ $item['text'] }}</p>
                        @if($item['sub'])<p class="text-gray-600 text-[10px] leading-snug">{{ $item['sub'] }}</p>@endif
                    </div>
                    <span class="text-red-400/90 text-[11px] font-bold flex-shrink-0">-{{ number_format($item['amount']) }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="flex justify-between"><span class="text-gray-400">Net</span><span class="font-black" style="color:{{ $rpt['net']>=0?'#10b981':'#ef4444' }};">{{ $rpt['net']>=0?'+':'' }}Ksh {{ number_format($rpt['net']) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-400">Savings Rate</span><span class="font-black" style="color:{{ $gradeColor }};">{{ $rpt['savings_rate'] }}%</span></div>
        </div>
        <div class="h-1.5 bg-white/5 rounded-full overflow-hidden mb-4"><div class="h-full rounded-full" style="width:{{ min(100,$rpt['savings_rate']) }}%;background:{{ $gradeColor }};transition:width 1.2s;"></div></div>
        <p class="text-[11px] sm:text-xs text-gray-300 leading-relaxed mb-4 sm:mb-5">{{ $gradeMsg }}</p>
        <button onclick="document.getElementById('report-overlay').style.display='none'"
                class="w-full text-white font-black py-2.5 sm:py-3 rounded-2xl text-xs sm:text-sm" style="background:{{ $gradeColor }};">
            Got it — keep going!
        </button>
    </div>
</div>
@endif

{{-- HUD BAR — sticky top --}}
@php
    $xp        = $progress->points_total ?? 0;
    $level     = $progress->level ?? 1;
    $xpPct     = max(0, min(100, $progress->level_progress_percent ?? 0));
    $xpToNext  = $progress->points_to_next_level ?? 0;
    $balance   = $progress->balance ?? 0;
    $creditScore = $progress->credit_score ?? 500;
    $netWorth  = $balance + $activeInvestments->sum('amount');
    $energyPct = min(100, max(20, 50 + ($streak->current_streak ?? 0) * 5));
    $firstName = explode(' ', auth()->user()->name)[0];
    $charTitle = $progress->level_name ?? 'Novice';
    $canSpin   = $canSpin ?? false;
    $levelIcons = ['1'=>'🌱','2'=>'🌿','3'=>'🍀','4'=>'⭐','5'=>'🌟','6'=>'🔥','7'=>'💎','8'=>'👑','9'=>'🏆','10'=>'✨'];
    $lvlIcon   = $levelIcons[(string)min($level,10)] ?? '🌟';
    $initials  = strtoupper(substr(auth()->user()->name,0,1)) . strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '',0,1));
@endphp

<nav class="hud-bar sticky top-0 z-50 backdrop-blur-xl">
    <div class="max-w-[1400px] mx-auto px-3 sm:px-5 h-14 flex items-center gap-2 sm:gap-3">

        {{-- Logo --}}
        <a href="{{ route('landing') }}" class="flex-shrink-0">
            <img src="{{ asset('moski-logo.png') }}" alt="PesaQuest" class="h-8 w-auto rounded-lg">
        </a>

        {{-- Character pill (desktop) --}}
        <a href="{{ route('profile.edit') }}"
           class="hidden lg:flex items-center gap-2.5 px-3 py-1.5 rounded-xl hover:bg-white/5 transition-colors flex-shrink-0">
            @if(auth()->user()->profile_photo)
            <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0" style="border:1.5px solid rgba(99,102,241,0.45);">
                <img src="{{ auth()->user()->profile_photo }}" alt="" class="w-full h-full object-cover">
            </div>
            @else
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366f1,#a78bfa);">{{ $initials }}</div>
            @endif
            <div class="leading-none">
                <div class="text-sm font-black text-white">{{ $firstName }}</div>
                <div class="text-[10px] text-indigo-400 font-semibold">{{ $charTitle }}</div>
            </div>
        </a>

        {{-- Level + XP (desktop) --}}
        <div class="hidden md:flex items-center gap-2 flex-shrink-0">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 0 10px rgba(99,102,241,0.4);">
                {{ $level }}
            </div>
            <div class="w-28">
                <div class="flex justify-between text-[9px] text-gray-500 mb-0.5">
                    <span>{{ $xpToNext > 0 ? number_format($xpToNext).' to next' : 'Max level' }}</span>
                    <span>{{ $xpPct }}%</span>
                </div>
                <div class="h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.07);">
                    <div class="xp-fill h-full rounded-full" style="width:{{ $xpPct }}%;"></div>
                </div>
            </div>
        </div>

        {{-- Stat chips (desktop) --}}
        <div class="hidden xl:flex items-center gap-1.5 flex-shrink-0">
            {{-- Cash --}}
            <div class="stat-chip flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg">
                <x-icon name="coin" class="w-3.5 h-3.5 text-emerald-400" />
                <div class="leading-none">
                    <div class="text-[9px] text-gray-500 font-semibold uppercase">Cash</div>
                    <div class="text-xs font-black {{ $balance < 500 ? 'text-red-400' : 'text-emerald-400' }}">
                        Ksh {{ number_format($balance) }}
                    </div>
                </div>
            </div>
            {{-- Net Worth --}}
            <div class="stat-chip flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg">
                <x-icon name="bar-chart" class="w-3.5 h-3.5 text-blue-400" />
                <div class="leading-none">
                    <div class="text-[9px] text-gray-500 font-semibold uppercase">Net Worth</div>
                    <div class="text-xs font-black text-blue-400">Ksh {{ number_format($netWorth) }}</div>
                </div>
            </div>
            {{-- Credit Score --}}
            <div class="stat-chip flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg">
                <x-icon name="target" class="w-3.5 h-3.5 text-amber-400" />
                <div class="leading-none">
                    <div class="text-[9px] text-gray-500 font-semibold uppercase">Credit</div>
                    <div class="text-xs font-black {{ $creditScore >= 650 ? 'text-emerald-400' : ($creditScore >= 500 ? 'text-amber-400' : 'text-red-400') }}">
                        {{ $creditScore }}
                    </div>
                </div>
            </div>
            {{-- Energy --}}
            <div class="stat-chip flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg">
                <x-icon name="bolt" class="w-3.5 h-3.5 text-yellow-400" />
                <div class="leading-none">
                    <div class="text-[9px] text-gray-500 font-semibold uppercase">Energy</div>
                    <div class="text-xs font-black text-yellow-400">{{ $energyPct }}/100</div>
                </div>
            </div>
        </div>

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Admin/GameSet badges (desktop) --}}
        @if(auth()->user()->is_admin)
        <a href="{{ route('admin.index') }}" class="hidden sm:inline-flex text-xs text-orange-400 border border-orange-500/30 px-2.5 py-1 rounded-lg hover:border-orange-500/60 transition-colors items-center gap-1 flex-shrink-0"><x-icon name="wrench" class="w-3.5 h-3.5" /> Admin</a>
        @endif
        @if(auth()->user()->is_gameset || auth()->user()->is_admin)
        <a href="{{ route('gameset.index') }}" class="hidden sm:inline-flex text-xs text-purple-400 border border-purple-500/30 px-2.5 py-1 rounded-lg hover:border-purple-500/60 transition-colors items-center gap-1 flex-shrink-0"><x-icon name="gear" class="w-3.5 h-3.5" /> GameSet</a>
        @endif

        {{-- Daily Spin chip --}}
        <a href="{{ route('spin.index') }}" class="hidden sm:flex flex-shrink-0 items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-black transition-all hover:scale-105"
           style="{{ $canSpin ? 'background:rgba(245,158,11,0.18);border:1px solid rgba(245,158,11,0.45);color:#fbbf24;' : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#4b5563;' }}">
            <span style="{{ $canSpin ? 'animation:pulse-spin 1.5s ease-in-out infinite;' : '' }}"><x-icon name="spin" class="w-3.5 h-3.5" /></span>
            <span>{{ $canSpin ? 'Spin!' : 'Spun ✓' }}</span>
        </a>

        {{-- Notification bell — opening the panel marks everything read immediately --}}
        <button @click="showNotifPanel ? (showNotifPanel=false) : openNotifPanel()"
                class="relative p-2 text-gray-400 hover:text-white transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span x-show="unreadCount > 0" x-cloak x-text="unreadCount" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full text-[9px] font-black flex items-center justify-center"></span>
        </button>

        {{-- Play button --}}
        <a href="{{ route('game.play') }}"
           class="play-btn flex-shrink-0 flex items-center gap-1.5 text-white font-black px-4 py-2 rounded-xl text-sm transition-all">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            <span class="hidden sm:inline">Play</span>
        </a>

        {{-- Mobile avatar --}}
        @if(auth()->user()->profile_photo)
        <a href="{{ route('profile.edit') }}"
           class="lg:hidden flex-shrink-0 w-8 h-8 rounded-full overflow-hidden"
           style="border:1.5px solid rgba(99,102,241,0.45);">
            <img src="{{ auth()->user()->profile_photo }}" alt="" class="w-full h-full object-cover">
        </a>
        @else
        <a href="{{ route('profile.edit') }}"
           class="lg:hidden flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-black"
           style="background:linear-gradient(135deg,#6366f1,#a78bfa);">{{ $initials }}</a>
        @endif

        {{-- Hamburger — opens the themed menu sheet (shared component, bottom of page) --}}
        <button onclick="pqMenuOpen()"
                class="lg:hidden flex-shrink-0 p-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/8 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

    </div>
</nav>

{{-- Legacy mobile slide-out menu + desktop nav-tabs bar removed — both are
     fully superseded by the shared themed menu (<x-mobile-bottom-nav>,
     included at the bottom of this page): same destinations, grouped by
     theme, reachable via this page's hamburger button above and the
     bottom "Menu" tab / desktop floating button everywhere else. --}}

{{-- MAIN PAGE CONTENT --}}
@php
    $chapterKey   = $progress->chapterKey();
    $chapterName  = $progress->chapterName();
    $chapterIcon  = $progress->chapterIcon();
    $chapterNetWorth = (int)($progress->net_worth_cache ?? 0);
    $chapterBandsNw  = \App\Models\UserProgress::chapterBands();
    [$bandStart,$bandEnd] = $chapterBandsNw[$chapterKey] ?? [0,50000];
    $nextChapterWorth = $progress->nextChapterNetWorth();
    $chapterPctVal    = $nextChapterWorth ? min(100,(int)(max(0,$chapterNetWorth-$bandStart)/max(1,$bandEnd-$bandStart)*100)) : 100;
    $chapterColor   = match($chapterKey){
        'student'  => ['bar'=>'#38bdf8','text'=>'text-sky-400','border'=>'rgba(56,189,248,0.3)','bg'=>'rgba(56,189,248,0.08)'],
        'graduate' => ['bar'=>'#10b981','text'=>'text-emerald-400','border'=>'rgba(16,185,129,0.3)','bg'=>'rgba(16,185,129,0.08)'],
        'hustler'  => ['bar'=>'#fb923c','text'=>'text-orange-400','border'=>'rgba(251,146,60,0.3)','bg'=>'rgba(251,146,60,0.08)'],
        'settler'  => ['bar'=>'#a78bfa','text'=>'text-violet-400','border'=>'rgba(167,139,250,0.3)','bg'=>'rgba(167,139,250,0.08)'],
        'builder'  => ['bar'=>'#f59e0b','text'=>'text-amber-400','border'=>'rgba(245,158,11,0.3)','bg'=>'rgba(245,158,11,0.08)'],
        'elder'    => ['bar'=>'#fb7185','text'=>'text-rose-400','border'=>'rgba(251,113,133,0.3)','bg'=>'rgba(251,113,133,0.08)'],
        default    => ['bar'=>'#9ca3af','text'=>'text-gray-400','border'=>'rgba(156,163,175,0.3)','bg'=>'rgba(156,163,175,0.08)'],
    };
    $todayQuest = $challenges->where('user_claimed', false)->first();
    $recentLifeEvents = $recentLifeEvents ?? collect();
@endphp

<div class="max-w-[1400px] mx-auto px-3 sm:px-5 py-5">

    {{-- DESKTOP LAYOUT (md+) --}}
    {{-- 3-COLUMN MAIN GRID: Col 1 Character (row-span-2), Col 2 Your Home
         + City Map (span 2), Col 3 Cash + Quest --}}
    <div class="desktop-game-grid">

        {{-- COL 1: YOUR CHARACTER --}}
        <div class="card rounded-2xl overflow-hidden flex flex-col" style="border-color:rgba(99,102,241,0.25);">
            {{-- Avatar hero area --}}
            <div class="relative text-center px-4 pt-5 pb-4"
                 style="background:linear-gradient(160deg,rgba(99,102,241,0.22) 0%,rgba(139,92,246,0.12) 60%,rgba(7,6,15,0) 100%);">
                <div class="absolute top-2 right-2 text-[10px] font-black text-amber-400 px-2 py-0.5 rounded-full" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);">{{ $lvlIcon }} Lv.{{ $level }}</div>
                <div class="relative inline-block mb-2">
                    @if($user->profile_photo)
                    <div class="w-24 h-24 rounded-full mx-auto overflow-hidden avatar-pulse"
                         style="border:2.5px solid rgba(99,102,241,0.55);">
                        <img src="{{ $user->profile_photo }}" alt="{{ $user->name }}"
                             class="w-full h-full object-cover">
                    </div>
                    @else
                    <div class="w-24 h-24 rounded-full mx-auto flex items-center justify-center text-3xl font-black avatar-pulse"
                         style="background:linear-gradient(135deg,#4f46e5,#7c3aed,#a78bfa);">
                        {{ $initials }}
                    </div>
                    @endif
                    @if(($streak->current_streak ?? 0) > 1)
                    <div class="absolute -top-1 -right-1 text-xl animate-bounce" style="animation-duration:1.5s;">🔥</div>
                    @endif
                </div>
                <h3 class="font-black text-base text-white leading-tight">{{ explode(' ', auth()->user()->name)[0] }}</h3>
                <p class="text-xs font-bold mt-0.5" style="color:#a78bfa;">{{ $charTitle }}</p>
                <div class="mt-3">
                    <div class="flex justify-between text-[9px] text-gray-500 mb-1">
                        <span>XP Progress</span><span>{{ $xpPct }}%</span>
                    </div>
                    <div class="h-1.5 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.07);">
                        <div class="xp-fill h-full rounded-full" style="width:{{ $xpPct }}%;"></div>
                    </div>
                </div>
                @if($questGate['blocked'] ?? false)
                <div class="mt-2.5 px-2.5 py-2 rounded-lg text-left" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.35);">
                    <div class="text-[10px] font-black" style="color:#fbbf24;">⛰️ Level {{ $questGate['xp_level'] }} is waiting for you!</div>
                    <div class="text-[9.5px] mt-0.5 leading-relaxed" style="color:rgba(251,191,36,0.75);">
                        You've earned the XP — finish {{ $questGate['remaining'] }} more quest{{ $questGate['remaining'] === 1 ? '' : 's' }} at Level {{ $questGate['gate_level'] }} to unlock it.
                    </div>
                </div>
                @endif
            </div>
            <div class="px-4 py-4 flex-1 flex flex-col gap-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-xl p-2.5 text-center" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">
                        <div class="text-[9px] text-gray-500 font-semibold uppercase mb-1">Cash</div>
                        <div class="text-xs font-black text-emerald-400">Ksh {{ number_format($balance) }}</div>
                    </div>
                    <div class="rounded-xl p-2.5 text-center" style="background:rgba(96,165,250,0.1);border:1px solid rgba(96,165,250,0.2);">
                        <div class="text-[9px] text-gray-500 font-semibold uppercase mb-1">Net Worth</div>
                        <div class="text-xs font-black text-blue-400">Ksh {{ number_format($netWorth) }}</div>
                    </div>
                </div>
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center justify-between py-1.5 border-b" style="border-color:rgba(255,255,255,0.06);">
                        <span class="text-[11px] text-gray-500">Level</span>
                        <span class="text-[11px] font-bold text-white">Lv {{ $progress->level ?? 1 }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b" style="border-color:rgba(255,255,255,0.06);">
                        <span class="text-[11px] text-gray-500">Career</span>
                        <span class="text-[11px] font-bold text-white truncate max-w-[95px] text-right">{{ $progress->career_field ?? 'None set' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b" style="border-color:rgba(255,255,255,0.06);">
                        <span class="text-[11px] text-gray-500">Salary</span>
                        <span class="text-[11px] font-bold text-emerald-400">Ksh {{ number_format($salaryAmount) }}/mo</span>
                    </div>
                    @php $charMood = $progress->mood ?? 70; @endphp
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-[11px] text-gray-500">Mood</span>
                        <span class="text-[11px] font-bold {{ $charMood >= 55 ? 'text-emerald-400' : ($charMood >= 40 ? 'text-amber-400' : 'text-red-400') }}">
                            {{ $charMood >= 80 ? '😄' : ($charMood >= 55 ? '🙂' : ($charMood >= 35 ? '😐' : '😟')) }} {{ $charMood }}/100
                        </span>
                    </div>
                </div>
            </div>
            <div class="px-4 pb-4 flex flex-col gap-2">
                @if($overdueBills->count() > 0)
                <a href="{{ route('life.board') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold animate-pulse"
                   style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#f87171;">
                    🚨 OVERDUE — {{ $overdueBills->count() }} bill{{ $overdueBills->count()>1?'s':'' }}, pay now
                </a>
                @endif
                @php $nextTwoBills = $upcomingBills->take(2); @endphp
                @if($nextTwoBills->count() > 0 || $monthlyBurn > 0)
                <a href="{{ route('life.board') }}" class="rounded-xl px-3 py-2.5 flex flex-col gap-1.5"
                   style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.18);">
                    @foreach($nextTwoBills as $nb)
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-gray-400 truncate">{{ $nb->bill->icon ?? '🧾' }} {{ $nb->bill->name ?? 'Bill' }}</span>
                        <span class="font-bold text-amber-300 flex-shrink-0 ml-2">
                            Ksh {{ number_format($nb->amount) }} — {{ max(0, $nb->next_due_tick - ($progress->tick_count ?? 0)) }}d
                        </span>
                    </div>
                    @endforeach
                    <div class="flex items-center justify-between text-[10px] pt-1 border-t" style="border-color:rgba(245,158,11,0.15);">
                        <span class="text-gray-500 uppercase tracking-wider font-semibold">Bills / game month</span>
                        <span class="font-black text-amber-400">Ksh {{ number_format($monthlyBurn) }}</span>
                    </div>
                </a>
                @endif
                <a href="{{ route('profile.edit') }}"
                   class="text-center py-2.5 rounded-xl text-sm font-black transition-all hover:scale-105"
                   style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">
                    <span class="inline-flex items-center gap-1"><x-icon name="gear" class="w-3.5 h-3.5" /> Customize</span>
                </a>
            </div>
        </div>

        {{-- COL 2 ROW 1: YOUR HOME --}}
        @php
        $roomItems = [
            ['icon'=>'💻','label'=>'Laptop','sub'=>'Portfolio','href'=>route('portfolio'),'color'=>'#6366f1'],
            ['icon'=>'📱','label'=>'Phone','sub'=>'Messages','href'=>route('inbox.index'),'color'=>'#a78bfa'],
            ['icon'=>'📺','label'=>'TV','sub'=>'City News','href'=>route('world'),'color'=>'#06b6d4'],
            ['icon'=>'📚','label'=>'Desk','sub'=>'Study','href'=>route('money-toolkit'),'color'=>'#10b981'],
            ['icon'=>'👔','label'=>'Wardrobe','sub'=>'Inventory','href'=>route('marketplace'),'color'=>'#f59e0b'],
            ['icon'=>'🚪','label'=>'Door','sub'=>'Enter City','href'=>route('world'),'color'=>'#34d399'],
        ];
        @endphp
        <div class="card rounded-2xl overflow-hidden flex flex-col" style="border-color:rgba(99,102,241,0.25);">
            {{-- Header --}}
            <div class="px-4 py-3 flex items-center justify-between flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <div>
                    <h3 class="font-black text-white text-sm leading-none">YOUR HOME</h3>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $progress->home_tier ?? 'Apartment' }} — Day {{ $progress->tick_count ?? 0 }}</p>
                </div>
                <a href="{{ route('life.board') }}" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-semibold transition-colors">Edit →</a>
            </div>
            {{-- Room photo grows to fill all available space --}}
            <div class="relative flex-1 overflow-hidden" style="min-height:185px;">
                <img src="{{ asset('img/game/moski_home_room.webp') }}" alt="Your Room"
                     class="w-full h-full object-cover"
                     onerror="this.style.display='none'; this.nextElementSibling.style.background='linear-gradient(160deg,#0d0b1e,#0a0820)';">
                <div class="absolute inset-0" style="background:linear-gradient(to bottom,rgba(0,0,0,0.0) 40%,rgba(7,6,15,0.75) 100%);"></div>
            </div>
            {{-- Badges earned — pinned at the very bottom --}}
            <div class="flex-shrink-0 px-3 py-2" style="background:linear-gradient(to right,#0d0b1e,#110f28);border-top:1px solid rgba(99,102,241,0.18);">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-400 inline-flex items-center gap-1"><x-icon name="medal" class="w-3 h-3" /> Badges Earned</span>
                    <span class="text-[9px] text-gray-500">{{ $badges->count() }} total</span>
                </div>
                @if($badges->isEmpty())
                <div class="text-[9px] text-gray-500 text-center py-1">No badges yet — keep going!</div>
                @else
                <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                    @foreach($badges as $badge)
                    <div class="relative rounded-xl py-2 px-1 flex-shrink-0 w-12 text-center group transition-all hover:scale-105"
                         style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.22);">
                        @if($badge->image_url)
                        <img src="{{ $badge->image_url }}" alt="{{ $badge->name }}"
                             class="w-7 h-7 rounded-full mx-auto object-cover">
                        @else
                        <div class="text-lg leading-none"><x-icon :name="$badge->icon ?? 'medal'" class="w-4 h-4" /></div>
                        @endif
                        <div class="text-[8px] font-bold mt-0.5 leading-none text-amber-400 truncate">{{ Str::limit($badge->name, 7) }}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- COL 3: flex column — Cash+Quest at top, city map fills the rest --}}
        <div class="flex flex-col gap-3">

        {{-- Cash + Quest side-by-side, content height --}}
        <div class="grid grid-cols-2 gap-3 flex-shrink-0">

            {{-- Cash Balance --}}
            <div class="rounded-2xl p-3"
                 style="{{ $balance < 500 ? 'background:linear-gradient(135deg,#1f0f0f,#150a0a);border:1px solid rgba(239,68,68,0.45);' : 'background:linear-gradient(135deg,#0d1f14,#091510);border:1px solid rgba(16,185,129,0.45);' }}">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1 {{ $balance < 500 ? 'text-red-400' : 'text-emerald-400' }}">
                        @if($balance < 500)<x-icon name="warning" class="w-3 h-3" /> Low Balance @else<x-icon name="coin" class="w-3 h-3" /> Cash Balance @endif
                    </span>
                    @if($canClaimBonus)<span class="text-[9px] font-bold text-emerald-400 animate-pulse px-1.5 py-0.5 rounded-full" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);">Bonus!</span>@endif
                </div>
                <div class="text-xl font-black {{ $balance < 500 ? 'text-red-400' : 'text-emerald-400' }} mb-0.5 leading-tight">
                    Ksh {{ number_format($balance) }}
                </div>
                <p class="text-[9px] text-gray-500 mb-2">{{ $investmentCount }} investment{{ $investmentCount!==1?'s':'' }}</p>
                @if($canClaimBonus)
                <button @click="claimBonus()" :disabled="claimingBonus"
                        class="bonus-btn w-full text-white font-bold py-1.5 rounded-xl text-[10px] transition-all flex items-center justify-center gap-1">
                    <span x-show="!claimingBonus" class="inline-flex items-center gap-1"><x-icon name="gift" class="w-3 h-3" /> Claim Bonus</span>
                    <span x-show="claimingBonus" class="flex items-center gap-1">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>...
                    </span>
                </button>
                @else
                <div class="text-[9px] text-center text-gray-500 bg-white/4 rounded-xl py-1.5 inline-flex items-center justify-center gap-1 w-full"><x-icon name="check-circle" class="w-3 h-3" /> Claimed today</div>
                @endif
                <a href="{{ route('portfolio') }}" class="block text-center mt-1.5 text-[9px] text-emerald-400 font-semibold hover:text-emerald-300 transition-colors">View Wallet →</a>
            </div>

            {{-- Active Quest (from UserQuest, not daily challenge) --}}
            @if($activeQuest)
            <div class="rounded-2xl p-3" style="background:linear-gradient(135deg,#110f28,#0d0b20);border:1px solid rgba(99,102,241,0.45);">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-400 inline-flex items-center gap-1"><x-icon name="checklist" class="w-3 h-3" /> Active Quest</span>
                    @if($activeQuest->isPending())
                    <span class="text-[9px] font-bold text-amber-400 px-1.5 py-0.5 rounded-full inline-flex items-center gap-1" style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.25);"><x-icon name="clock" class="w-2.5 h-2.5" /> Reviewing</span>
                    @endif
                </div>
                <div class="flex items-start gap-2 mb-2">
                    <span class="text-xl flex-shrink-0 leading-none"><x-icon :name="$activeQuest->quest->icon ?? 'checklist'" class="w-5 h-5" /></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-black text-white leading-snug">{{ $activeQuest->quest->title }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5 leading-snug">{{ Str::limit($activeQuest->quest->description ?? '', 75) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-2 text-[10px]">
                    <span class="text-indigo-300 font-bold">+{{ number_format($activeQuest->quest->xp_reward ?? 50) }} XP</span>
                </div>
                <a href="{{ route('world', ['open' => 'quests']) }}"
                   class="flex items-center justify-center gap-1.5 w-full py-2 rounded-xl text-xs font-black text-white transition-all hover:scale-105"
                   style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                    {{ $activeQuest->isPending() ? 'Check Status →' : 'Continue Quest →' }}
                </a>
            </div>
            @else
            <div class="rounded-2xl p-3 flex flex-col" style="background:linear-gradient(135deg,#110f28,#0d0b20);border:1px solid rgba(99,102,241,0.3);">
                <div class="text-[10px] font-black uppercase tracking-wider text-indigo-400 mb-2 inline-flex items-center gap-1"><x-icon name="checklist" class="w-3 h-3" /> Current Quest</div>
                <div class="flex-1 flex flex-col items-center justify-center text-center py-2">
                    <div class="mb-2 flex justify-center"><x-icon name="target" class="w-8 h-8 text-indigo-300" /></div>
                    <div class="text-[10px] text-gray-400 mb-2">No active quest — head to Pesa City to start one</div>
                </div>
                <a href="{{ route('world') }}"
                   class="flex items-center justify-center gap-1.5 w-full py-2 rounded-xl text-xs font-black text-white transition-all hover:scale-105 mt-auto"
                   style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                    <x-icon name="target" class="w-3.5 h-3.5" /> Start a Quest
                </a>
            </div>
            @endif

        </div>{{-- /cash+quest --}}

        {{-- ENTER PESA CITY — fills remaining height in col 3 --}}
        <div class="city-card rounded-2xl overflow-hidden flex flex-col flex-1" style="min-height:200px;">
            <div class="px-4 py-2 flex items-center justify-between flex-shrink-0" style="background:rgba(16,185,129,0.07);">
                <div class="flex items-center gap-2">
                    <h3 class="font-black text-white text-sm">ENTER PESA CITY</h3>
                    <span class="text-[9px] font-black text-emerald-400 px-2 py-0.5 rounded-full inline-flex items-center gap-1" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> LIVE</span>
                </div>
                <span class="text-[10px] text-emerald-400/70 font-semibold">Your world. Your rules.</span>
            </div>
            <div class="relative flex-1 overflow-hidden">
                <img src="{{ asset('img/game/worldmap.webp') }}" alt="Pesa City"
                     class="w-full h-full object-cover"
                     onerror="this.style.opacity='0'">
                <div class="absolute inset-0" style="background:linear-gradient(to bottom,transparent 45%,rgba(0,0,0,0.65) 100%);"></div>
                <div class="absolute bottom-3 left-0 right-0 flex justify-center">
                    <a href="{{ route('world') }}" class="flex items-center gap-1.5 px-5 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-105"
                       style="background:rgba(5,46,22,0.62);backdrop-filter:blur(10px);border:1px solid rgba(16,185,129,0.6);text-shadow:0 1px 5px rgba(0,0,0,0.9);">
                        <x-icon name="city" class="w-4 h-4" /> Enter Pesa City
                    </a>
                </div>
            </div>
        </div>

        </div>{{-- /col 3 flex wrapper --}}

    </div>{{-- /main grid --}}

    {{-- This Week cashflow strip (desktop) --}}
    <div class="desktop-only mt-4">
        @include('dashboard.week-strip')
    </div>

    {{-- City Contracts — NPC-issued personal missions (desktop) --}}
    <div class="desktop-only mt-4">
        @include('dashboard.contracts-widget')
    </div>

    {{-- BOTTOM STRIP: Quick Actions, Streak, Daily Reward, Today's Goals --}}
    <div class="desktop-bottom-strip mt-4">

        {{-- Quick Actions --}}
        <div class="card rounded-2xl p-3">
            <div class="text-[9px] font-black uppercase tracking-wider text-gray-500 mb-2 inline-flex items-center gap-1"><x-icon name="bolt" class="w-3 h-3" /> Quick Actions</div>
            <div class="grid grid-cols-3 gap-1.5">
                @php
                $qActions = [
                    ['icon'=>'bank','label'=>'Bank','href'=>route('savings.index')],
                    ['icon'=>'shopping-bag','label'=>'Market','href'=>route('marketplace')],
                    ['icon'=>'briefcase','label'=>'Jobs','href'=>route('life.career')],
                    ['icon'=>'people','label'=>'Friends','href'=>route('friends.index')],
                    ['icon'=>'trend-up','label'=>'Invest','href'=>route('portfolio')],
                    ['icon'=>'house','label'=>'Home','href'=>route('life.board')],
                ];
                @endphp
                @foreach($qActions as $qa)
                <a href="{{ $qa['href'] }}" class="qa-tile rounded-xl py-2 text-center flex flex-col items-center gap-0.5">
                    <x-icon :name="$qa['icon']" class="w-4 h-4" />
                    <span class="text-[8px] font-bold text-gray-400">{{ $qa['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Streak --}}
        <div class="rounded-2xl p-3" style="background:linear-gradient(135deg,#1a1008,#120b05);border:1px solid rgba(249,115,22,0.4);">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5">
                    <x-icon name="fire" class="w-4 h-4 text-orange-400" />
                    <div>
                        <div class="text-xs font-black text-white leading-none">Login Streak</div>
                        <div class="text-[9px] text-gray-500">Keep it going!</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-black text-orange-400 leading-none">{{ $streak->current_streak ?? 0 }}</div>
                    <div class="text-[9px] text-gray-500">day{{ ($streak->current_streak ?? 0) !== 1 ? 's' : '' }}</div>
                </div>
            </div>
            <div class="flex gap-1 mb-1.5">
                @for($i = 1; $i <= 7; $i++)
                <div class="streak-dot flex-1 h-4 rounded {{ $i <= ($streak->current_streak ?? 0) ? 'lit' : 'bg-white/5 border border-white/10' }} flex items-center justify-center text-[8px]">
                    @if($i <= ($streak->current_streak ?? 0))✓@endif
                </div>
                @endfor
            </div>
            <div class="text-[9px] text-gray-600">Best: {{ $streak->longest_streak ?? 0 }} days</div>
            @if($streakAtRisk)
            <div class="mt-1 text-[9px] font-bold text-red-400 animate-pulse inline-flex items-center gap-1"><x-icon name="warning" class="w-3 h-3" /> Play today or lose your streak!</div>
            @endif
        </div>

        {{-- Daily Reward --}}
        <div class="rounded-2xl p-3" style="background:linear-gradient(135deg,#0d1f14,#091510);border:1px solid rgba(16,185,129,0.4);">
            <div class="flex items-center gap-1.5 mb-2">
                <x-icon name="gift" class="w-4 h-4 text-emerald-400" />
                <div>
                    <div class="text-xs font-black text-white leading-none">Daily Reward</div>
                    <div class="text-[9px] text-gray-500">Log in daily</div>
                </div>
            </div>
            <div class="flex items-center gap-0.5 mb-2">
                @for($d = 1; $d <= 6; $d++)
                <div class="flex-1 flex flex-col items-center gap-0.5">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[9px]
                        {{ $d <= ($streak->current_streak ?? 0) ? 'bg-emerald-500/20 border border-emerald-500/50' : ($d === min(6,($streak->current_streak ?? 0)+1) ? 'bg-indigo-500/20 border border-indigo-500/50 animate-pulse' : 'bg-white/4 border border-white/10') }}">
                        @if($d <= ($streak->current_streak ?? 0))✓
                        @elseif($d === 6)🎁
                        @else🔒@endif
                    </div>
                    <span class="text-[7px] text-gray-600">D{{ $d }}</span>
                </div>
                @endfor
            </div>
            @if($canClaimBonus)
            <button @click="claimBonus()" :disabled="claimingBonus"
                    class="w-full py-1.5 rounded-xl text-[10px] font-black text-white bonus-btn transition-all">
                <span x-show="!claimingBonus" class="inline-flex items-center justify-center gap-1"><x-icon name="gift" class="w-3 h-3" /> Claim +200 XP</span>
                <span x-show="claimingBonus">Claiming...</span>
            </button>
            @else
            <div class="text-center text-[9px] text-emerald-400 font-bold bg-emerald-500/10 rounded-xl py-1.5 inline-flex items-center justify-center gap-1 w-full"><x-icon name="check-circle" class="w-3 h-3" /> Claimed today!</div>
            @endif
        </div>

        {{-- Today's Goals — from active user quests --}}
        <div class="rounded-2xl p-3" style="background:linear-gradient(135deg,#110f28,#0d0b20);border:1px solid rgba(99,102,241,0.4);">
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs font-black text-white">Today's Goals</div>
                <a href="{{ route('world', ['open' => 'quests']) }}" class="text-[9px] text-indigo-400 font-semibold hover:text-indigo-300">View All</a>
            </div>
            @if($questGoals->isNotEmpty())
            <div class="space-y-1.5">
                @foreach($questGoals as $uq)
                <a href="{{ route('world', ['open' => 'quests']) }}" class="flex items-center gap-2 group">
                    <span class="text-base flex-shrink-0 leading-none"><x-icon :name="$uq->quest->icon ?? 'checklist'" class="w-4 h-4" /></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-semibold text-white truncate group-hover:text-indigo-300 transition-colors">{{ $uq->quest->title ?? 'Quest' }}</div>
                        <div class="text-[8px] {{ $uq->isPending() ? 'text-amber-400' : 'text-indigo-400' }}">
                            {{ $uq->isPending() ? '⏳ Reviewing' : '📜 Active' }} — +{{ $uq->quest->xp_reward ?? 50 }} XP
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="space-y-2">
                @forelse($challenges->take(4) as $ch)
                @php $cp = ($ch->target_count ?? 0) > 0 ? min(100, round(($ch->user_progress ?? 0)/($ch->target_count)*100)) : 0; @endphp
                <div class="flex items-center gap-2 {{ ($ch->user_claimed ?? false) ? 'opacity-50' : '' }}">
                    <div class="w-4 h-4 rounded flex items-center justify-center flex-shrink-0 text-[10px]"
                         style="{{ ($ch->user_claimed ?? false) ? 'background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.5);' : 'background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);' }}">
                        {{ ($ch->user_claimed ?? false) ? '✓' : '' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-semibold text-white truncate">{{ $ch->title }}</div>
                        <div class="h-1 bg-white/5 rounded-full mt-0.5">
                            <div class="h-full rounded-full bg-indigo-500" style="width:{{ $cp }}%;"></div>
                        </div>
                    </div>
                    <span class="text-[9px] text-indigo-400 font-bold flex-shrink-0">+{{ $ch->reward_points ?? 50 }}XP</span>
                </div>
                @empty
                <div class="text-center py-4">
                    <div class="text-2xl mb-1">🎯</div>
                    <div class="text-[10px] text-gray-500">Start a quest to see your goals here</div>
                    <a href="{{ route('world', ['open' => 'quests']) }}" class="text-[10px] text-indigo-400 font-semibold mt-1 block">Browse Quests →</a>
                </div>
                @endforelse
            </div>
            @endif
        </div>

    </div>{{-- /bottom strip --}}

    {{-- 2-COL ROW: Current Chapter + City News --}}
    <div class="desktop-2col-strip mt-4">

        {{-- Current Chapter --}}
        <div class="rounded-2xl p-3" style="background:{{ $chapterColor['bg'] }};border:1px solid {{ $chapterColor['border'] }};">
            <div class="text-[9px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Current Chapter</div>
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-black {{ $chapterColor['text'] }}">{{ $chapterIcon }} {{ $chapterName }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $progress->chapterTagline() }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xl font-black {{ $chapterColor['text'] }}">Lv {{ $progress->level ?? 1 }}</div>
                    <div class="text-[9px] text-gray-500">level</div>
                </div>
            </div>
            <div class="mt-1.5 h-1.5 bg-white/5 rounded-full">
                <div class="h-1.5 rounded-full chapter-bar" style="width:{{ $chapterPctVal }}%;background:{{ $chapterColor['bar'] }};"></div>
            </div>
            @if($nextChapterWorth)
            <div class="text-[9px] text-gray-500 mt-1">Ksh {{ number_format($progress->netWorthToNextChapter()) }} to next chapter</div>
            @endif
            <a href="{{ route('life.timeline') }}" class="text-[9px] {{ $chapterColor['text'] }} font-semibold mt-1 block hover:opacity-80 transition-opacity">Timeline →</a>
        </div>

        {{-- City News --}}
        <div class="card rounded-2xl p-3">
            <div class="flex items-center justify-between mb-2">
                <div class="text-xs font-black text-white">City News</div>
                <button @click="showNotifPanel=true" class="text-[9px] text-indigo-400 font-semibold hover:text-indigo-300 transition-colors">View All →</button>
            </div>
            @if($recentNotifications->count() > 0)
            <div class="space-y-1">
                @foreach($recentNotifications->take(4) as $notif)
                <div class="notif-{{ $notif->type }} notif-item rounded-lg px-2 py-1.5 flex items-center gap-2">
                    <span class="text-sm flex-shrink-0 leading-none">{{ $notif->icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-bold text-white truncate">{{ $notif->title }}</div>
                        <div class="text-[8px] text-gray-500">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                    @if(!$notif->is_read)<div class="w-1.5 h-1.5 bg-indigo-500 rounded-full flex-shrink-0 pulse-dot"></div>@endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-3 text-gray-500 text-xs">No city news yet</div>
            @endif
        </div>

    </div>{{-- /2-col row --}}

    {{-- MATURED INVESTMENTS (desktop — shown when ready) --}}
    @if($maturedInvestments->count() > 0)
    <div class="desktop-only mt-4 rounded-2xl p-5"
         style="background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(5,150,105,0.07));border:1px solid rgba(16,185,129,0.4);animation:bonusPulse 2s ease-in-out infinite;">
        <h3 class="font-black text-emerald-400 mb-3 flex items-center gap-2">
            <span class="animate-bounce" style="animation-duration:1.5s"><x-icon name="coin" class="w-5 h-5" /></span>
            Investment{{ $maturedInvestments->count()>1?'s':'' }} Ready to Claim!
            <span class="ml-auto bg-emerald-500/20 text-emerald-300 text-xs font-bold px-2 py-0.5 rounded-full">{{ $maturedInvestments->count() }}</span>
        </h3>
        <div class="grid md:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach($maturedInvestments as $inv)
            @php $returnAmt = round($inv->amount * (1 + $inv->return_rate / 100), 2); @endphp
            <div class="rounded-xl p-4 flex items-center gap-3" id="matured-inv-{{ $inv->id }}"
                 style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);">
                <div class="flex-shrink-0"><x-icon name="coin" class="w-6 h-6 text-emerald-400" /></div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-emerald-300 text-xs truncate">{{ $inv->label }}</div>
                    <div class="text-xs text-gray-400 inline-flex items-center gap-1"><x-icon name="trend-up" class="w-3 h-3" /> <span class="text-emerald-400 font-black">Ksh {{ number_format($returnAmt) }}</span></div>
                </div>
                <button onclick="claimDashInvestment({{ $inv->id }}, this)"
                        class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-black text-white inline-flex items-center gap-1"
                        style="background:linear-gradient(135deg,#10b981,#059669);"><x-icon name="coin" class="w-3 h-3" /> Claim</button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- YOUR STATS STRIP (desktop) --}}
    <div class="desktop-stats-strip grid-cols-4 xl:grid-cols-8 gap-3 mt-4">
        @php
        $statsRow = [
            ['icon'=>'star','val'=>number_format($xp),'label'=>'Total XP','color'=>'text-indigo-400'],
            ['icon'=>'layers','val'=>$level,'label'=>'Level','color'=>'text-purple-400'],
            ['icon'=>'fire','val'=>$streak->current_streak ?? 0,'label'=>'Day Streak','color'=>'text-orange-400'],
            ['icon'=>'trophy','val'=>'Top '.max(1,100-$percentile+1).'%','label'=>'Ranking','color'=>'text-emerald-400'],
            ['icon'=>'coin','val'=>'Ksh '.number_format($balance),'label'=>'Cash Balance','color'=>'text-emerald-400'],
            ['icon'=>'bar-chart','val'=>$investmentCount,'label'=>'Investments','color'=>'text-amber-400'],
            ['icon'=>'target','val'=>$creditScore,'label'=>'Credit Score','color'=>$creditScore>=650?'text-emerald-400':($creditScore>=500?'text-amber-400':'text-red-400')],
            ['icon'=>'medal','val'=>$badges->count(),'label'=>'Badges','color'=>'text-purple-400'],
        ];
        @endphp
        @foreach($statsRow as $s)
        <a href="{{ route('portfolio') }}" class="stat-tile rounded-2xl p-4 text-center transition-all hover:scale-105 card-hover">
            <div class="mb-1 flex justify-center"><x-icon :name="$s['icon']" class="w-5 h-5 {{ $s['color'] }}" /></div>
            <div class="text-lg font-black {{ $s['color'] }}">{{ $s['val'] }}</div>
            <div class="text-[10px] text-gray-500 uppercase tracking-wider mt-0.5">{{ $s['label'] }}</div>
        </a>
        @endforeach
    </div>

    {{-- MOBILE LAYOUT (hidden md+) --}}
    <div class="mobile-game-layout space-y-4">

        {{-- Mobile character card --}}
        <div class="rounded-2xl overflow-hidden" style="background:linear-gradient(135deg,rgba(99,102,241,0.18),rgba(139,92,246,0.08));border:1px solid rgba(99,102,241,0.3);">
            <div class="p-4 flex items-center gap-4">
                <div class="relative flex-shrink-0">
                    @if($user->profile_photo)
                    <div class="w-16 h-16 rounded-full overflow-hidden"
                         style="box-shadow:0 0 0 3px rgba(99,102,241,0.35),0 0 24px rgba(99,102,241,0.45);">
                        <img src="{{ $user->profile_photo }}" alt="{{ $user->name }}"
                             class="w-full h-full object-cover">
                    </div>
                    @else
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-black"
                         style="background:linear-gradient(135deg,#4f46e5,#7c3aed,#a78bfa);box-shadow:0 0 0 3px rgba(99,102,241,0.25),0 0 24px rgba(99,102,241,0.45);">
                        {{ $initials }}
                    </div>
                    @endif
                    <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black"
                         style="background:linear-gradient(135deg,#f59e0b,#fbbf24);border:2px solid #07060f;box-shadow:0 0 8px rgba(245,158,11,0.5);">{{ $level }}</div>
                    @if(($streak->current_streak ?? 0) > 1)
                    <div class="absolute -top-1 -right-1"><x-icon name="fire" class="w-4 h-4 text-orange-400" /></div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-black text-white text-base leading-tight">{{ $firstName }} <span>👋</span></div>
                    <div class="text-xs font-bold" style="color:#a78bfa;">{{ $charTitle }}</div>
                    <div class="mt-2 flex items-center gap-1.5">
                        <span class="text-[10px] text-gray-500">LVL {{ $level }}</span>
                        <div class="flex-1 h-1.5 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.07);">
                            <div class="xp-fill h-full rounded-full" style="width:{{ $xpPct }}%;"></div>
                        </div>
                        <span class="text-[10px] text-indigo-400 font-bold">{{ $xpPct }}%</span>
                    </div>
                </div>
                <a href="{{ route('game.play') }}" class="play-btn flex-shrink-0 flex items-center gap-1 text-white font-black px-3 py-2 rounded-xl text-sm transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Play
                </a>
            </div>
            {{-- Mini stat row --}}
            <div class="grid grid-cols-3 gap-0 border-t" style="border-color:rgba(255,255,255,0.06);">
                <div class="py-2.5 text-center border-r" style="border-color:rgba(255,255,255,0.06);">
                    <div class="text-[9px] text-gray-500 uppercase font-semibold">Career</div>
                    <div class="text-xs font-black text-white truncate px-1">{{ $progress->career_field ?? 'No career' }}</div>
                </div>
                <div class="py-2.5 text-center border-r" style="border-color:rgba(255,255,255,0.06);">
                    <div class="text-[9px] text-gray-500 uppercase font-semibold">Chapter</div>
                    <div class="text-xs font-black {{ $chapterColor['text'] }}">{{ $chapterIcon }} {{ $chapterName }}</div>
                </div>
                <div class="py-2.5 text-center">
                    <div class="text-[9px] text-gray-500 uppercase font-semibold">Streak</div>
                    <div class="text-xs font-black text-orange-400">🔥 {{ $streak->current_streak ?? 0 }}d</div>
                </div>
            </div>
        </div>

        {{-- This Week cashflow strip (mobile) --}}
        @include('dashboard.week-strip')

        {{-- City Contracts (mobile) --}}
        @include('dashboard.contracts-widget')

        {{-- Mobile cash balance --}}
        <div class="rounded-2xl p-4 flex items-center justify-between"
             style="{{ $balance < 500 ? 'background:linear-gradient(135deg,#1f0f0f,#150a0a);border:1px solid rgba(239,68,68,0.45);' : 'background:linear-gradient(135deg,#0d1f14,#091510);border:1px solid rgba(16,185,129,0.45);' }}">
            <div>
                <div class="text-xs {{ $balance < 500 ? 'text-red-400' : 'text-emerald-400' }} font-semibold uppercase tracking-wider mb-0.5 inline-flex items-center gap-1">
                    @if($balance < 500)<x-icon name="warning" class="w-3 h-3" /> Low Balance @else<x-icon name="coin" class="w-3 h-3" /> Cash Balance @endif
                </div>
                <div class="text-2xl font-black {{ $balance < 500 ? 'text-red-400' : 'text-emerald-400' }}">Ksh {{ number_format($balance) }}</div>
            </div>
            @if($canClaimBonus)
            <button @click="claimBonus()" class="bonus-btn text-white font-bold px-4 py-2 rounded-xl text-xs inline-flex items-center gap-1"><x-icon name="gift" class="w-3 h-3" /> Claim</button>
            @else
            <div class="text-xs text-emerald-400 font-bold inline-flex items-center gap-1"><x-icon name="check-circle" class="w-3 h-3" /> Claimed</div>
            @endif
        </div>

        {{-- Mobile character details --}}
        @php $mMood = $progress->mood ?? 70; @endphp
        <div class="rounded-2xl p-3" style="background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(139,92,246,0.05));border:1px solid rgba(99,102,241,0.25);">
            <div class="grid grid-cols-2 gap-2">
                <div class="rounded-xl px-3 py-2" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);">
                    <div class="text-[9px] text-gray-500 uppercase font-semibold">Level</div>
                    <div class="text-xs font-black text-white">Lv {{ $progress->level ?? 1 }}</div>
                </div>
                <div class="rounded-xl px-3 py-2" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);">
                    <div class="text-[9px] text-gray-500 uppercase font-semibold">Career</div>
                    <div class="text-xs font-black text-white truncate">{{ $progress->career_field ?? 'None set' }}</div>
                </div>
                <div class="rounded-xl px-3 py-2" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);">
                    <div class="text-[9px] text-gray-500 uppercase font-semibold">Salary</div>
                    <div class="text-xs font-black text-emerald-400">Ksh {{ number_format($salaryAmount) }}/mo</div>
                </div>
                <div class="rounded-xl px-3 py-2" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);">
                    <div class="text-[9px] text-gray-500 uppercase font-semibold">Mood</div>
                    <div class="text-xs font-black {{ $mMood >= 55 ? 'text-emerald-400' : ($mMood >= 40 ? 'text-amber-400' : 'text-red-400') }}">
                        {{ $mMood >= 80 ? '😄' : ($mMood >= 55 ? '🙂' : ($mMood >= 35 ? '😐' : '😟')) }} {{ $mMood }}/100
                    </div>
                </div>
            </div>
            @if($overdueBills->count() > 0)
            <a href="{{ route('life.board') }}" class="mt-2 flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold animate-pulse"
               style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#f87171;">
                🚨 OVERDUE — {{ $overdueBills->count() }} bill{{ $overdueBills->count()>1?'s':'' }}, pay now
            </a>
            @endif
            @php $mNextTwoBills = $upcomingBills->take(2); @endphp
            @if($mNextTwoBills->count() > 0 || $monthlyBurn > 0)
            <a href="{{ route('life.board') }}" class="mt-2 rounded-xl px-3 py-2.5 flex flex-col gap-1.5"
               style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.18);">
                @foreach($mNextTwoBills as $nb)
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-gray-400 truncate">{{ $nb->bill->icon ?? '🧾' }} {{ $nb->bill->name ?? 'Bill' }}</span>
                    <span class="font-bold text-amber-300 flex-shrink-0 ml-2">
                        Ksh {{ number_format($nb->amount) }} — {{ max(0, $nb->next_due_tick - ($progress->tick_count ?? 0)) }}d
                    </span>
                </div>
                @endforeach
                <div class="flex items-center justify-between text-[10px] pt-1 border-t" style="border-color:rgba(245,158,11,0.15);">
                    <span class="text-gray-500 uppercase tracking-wider font-semibold">Bills / game month</span>
                    <span class="font-black text-amber-400">Ksh {{ number_format($monthlyBurn) }}</span>
                </div>
            </a>
            @endif
            <a href="{{ route('profile.edit') }}"
               class="mt-2 block text-center py-2 rounded-xl text-xs font-black"
               style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">
                <span class="inline-flex items-center gap-1"><x-icon name="gear" class="w-3.5 h-3.5" /> Customize</span>
            </a>
        </div>

        {{-- Mobile active quest --}}
        <div class="rounded-2xl p-4" style="background:linear-gradient(135deg,#110f28,#0d0b20);border:1px solid rgba(99,102,241,0.45);">
            <div class="text-[10px] font-black uppercase tracking-wider text-indigo-400 mb-2 inline-flex items-center gap-1"><x-icon name="checklist" class="w-3 h-3" /> Current Quest</div>
            @if($activeQuest && $activeQuest->quest)
            @php $mAq = $activeQuest->quest; $mAqPending = $activeQuest->submitted_at && !$activeQuest->completed_at; @endphp
            <div class="flex items-center gap-3 mb-3">
                <span class="text-3xl flex-shrink-0"><x-icon :name="$mAq->icon ?? 'checklist'" class="w-7 h-7" /></span>
                <div class="min-w-0">
                    <div class="font-black text-white text-sm leading-tight">{{ $mAq->title }}</div>
                    <div class="text-xs text-gray-400 mt-0.5 leading-tight">{{ Str::limit($mAq->description, 70) }}</div>
                    <div class="flex items-center gap-2 mt-1.5 text-xs flex-wrap">
                        @if($mAqPending)
                        <span style="color:#f59e0b;font-weight:800;" class="inline-flex items-center gap-1"><x-icon name="clock" class="w-3 h-3" /> Pending review</span>
                        @else
                        <span style="color:#10b981;font-weight:800;" class="inline-flex items-center gap-1"><x-icon name="checklist" class="w-3 h-3" /> In progress</span>
                        @endif
                        @if($mAq->xp_reward)
                        <span class="text-indigo-400 font-bold">+{{ $mAq->xp_reward }} XP</span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('world') }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-black text-white"
               style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                <x-icon name="checklist" class="w-4 h-4" /> Continue Quest
            </a>
            @else
            <div class="flex flex-col items-center text-center py-3 gap-2">
                <x-icon name="target" class="w-8 h-8 text-indigo-300" />
                <div class="text-xs text-gray-400">No active quest — head to Pesa City to start one</div>
            </div>
            <a href="{{ route('world') }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-sm font-black text-white mt-2"
               style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                <x-icon name="target" class="w-4 h-4" /> Start a Quest
            </a>
            @endif
        </div>

        {{-- Mobile Pesa City --}}
        <div class="city-card rounded-2xl overflow-hidden">
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="font-black text-white text-sm">ENTER PESA CITY</div>
                    <div class="text-xs text-emerald-400/70">Your world. Your rules.</div>
                </div>
            </div>
            <div class="relative" style="height:220px;">
                <img src="{{ asset('img/game/worldmap.webp') }}" alt="Pesa City" class="w-full h-full object-cover" onerror="this.style.opacity='0'">
                <div class="absolute inset-0" style="background:linear-gradient(to bottom,transparent 60%,rgba(0,0,0,0.35) 100%);"></div>
                <div class="absolute bottom-3 left-0 right-0 flex justify-center">
                    <a href="{{ route('world') }}"
                       class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-black text-white transition-all hover:scale-105"
                       style="background:rgba(5,46,22,0.62);backdrop-filter:blur(10px);border:1px solid rgba(16,185,129,0.6);text-shadow:0 1px 5px rgba(0,0,0,0.9);">
                        <x-icon name="city" class="w-4 h-4" /> Enter Pesa City
                    </a>
                </div>
            </div>
        </div>

        {{-- Mobile daily reward --}}
        <div class="rounded-2xl p-4" style="background:linear-gradient(135deg,#0d1f14,#091510);border:1px solid rgba(16,185,129,0.4);">
            <div class="font-black text-white text-sm mb-1">Daily Reward</div>
            <div class="text-xs text-gray-500 mb-3">Log in daily and build your streak!</div>
            <div class="flex items-center gap-2 mb-3">
                @for($d=1; $d<=6; $d++)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $d <= ($streak->current_streak ?? 0) ? 'bg-emerald-500/25 border-2 border-emerald-500/70 text-emerald-400' : ($d===6?'bg-purple-500/15 border border-purple-500/40 text-purple-400':'bg-white/5 border border-white/10 text-gray-600') }}">
                        @if($d <= ($streak->current_streak ?? 0))✓
                        @elseif($d===6)🎁
                        @else🔒@endif
                    </div>
                    <span class="text-[9px] text-gray-600">D{{ $d }}</span>
                </div>
                @endfor
            </div>
            @if($canClaimBonus)
            <button @click="claimBonus()" :disabled="claimingBonus"
                    class="w-full py-3 rounded-xl text-sm font-black text-white bonus-btn">
                <span x-show="!claimingBonus" class="inline-flex items-center justify-center gap-1"><x-icon name="gift" class="w-3.5 h-3.5" /> Claim Reward +100 XP</span>
                <span x-show="claimingBonus">Claiming...</span>
            </button>
            @else
            <div class="text-center text-xs text-emerald-400 font-bold bg-emerald-500/10 rounded-xl py-2.5 inline-flex items-center justify-center gap-1 w-full"><x-icon name="check-circle" class="w-3.5 h-3.5" /> Daily Reward Claimed!</div>
            @endif
        </div>

        {{-- Mobile today's goals --}}
        @if($challenges->isNotEmpty())
        <div class="card rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-black text-white text-sm">Today's Goals</div>
                <a href="{{ route('world', ['open' => 'quests']) }}" class="text-xs text-indigo-400 font-semibold">View All</a>
            </div>
            <div class="space-y-3">
                @foreach($challenges->take(3) as $ch)
                @php $cp2 = $ch->target_count > 0 ? min(100,round($ch->user_progress/$ch->target_count*100)) : 0; @endphp
                <div class="flex items-center gap-3 {{ $ch->user_claimed ? 'opacity-50' : '' }}">
                    <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0 text-xs"
                         style="{{ $ch->user_claimed ? 'background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.5);' : 'background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);' }}">
                        {{ $ch->user_claimed ? '✓' : '' }}
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-white">{{ $ch->title }}</div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <div class="flex-1 h-1.5 bg-white/5 rounded-full">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500" style="width:{{ $cp2 }}%;"></div>
                            </div>
                            <span class="text-[10px] text-gray-500">{{ $ch->user_progress }}/{{ $ch->target_count }}</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-indigo-400 font-bold">+{{ $ch->reward_points ?? 50 }}XP</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Mobile stats grid --}}
        <div class="grid grid-cols-3 gap-2.5">
            @foreach([
                ['star','XP',$xp,'text-indigo-400'],
                ['layers','Level',$level,'text-purple-400'],
                ['fire','Streak',($streak->current_streak??0),'text-orange-400'],
                ['trophy','Ranking','Top '.max(1,100-$percentile+1).'%','text-emerald-400'],
                ['target','Credit',$creditScore,'text-amber-400'],
                ['medal','Badges',$badges->count(),'text-purple-400'],
            ] as [$ico,$lbl,$v,$col])
            <div class="stat-tile rounded-xl p-3 text-center">
                <div class="mb-0.5 flex justify-center"><x-icon :name="$ico" class="w-4 h-4 {{ $col }}" /></div>
                <div class="font-black text-sm {{ $col }}">{{ is_int($v) ? number_format($v) : $v }}</div>
                <div class="text-[9px] text-gray-500 uppercase tracking-wider">{{ $lbl }}</div>
            </div>
            @endforeach
        </div>

        {{-- Mobile current chapter --}}
        <div class="rounded-2xl p-4" style="background:{{ $chapterColor['bg'] }};border:1px solid {{ $chapterColor['border'] }};">
            <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-2">Current Chapter</div>
            <div class="flex items-center justify-between mb-2">
                <div>
                    <div class="text-base font-black {{ $chapterColor['text'] }}">{{ $chapterIcon }} {{ $chapterName }}</div>
                    <div class="text-xs text-gray-400">{{ $progress->chapterTagline() }}</div>
                </div>
                <div class="text-3xl font-black {{ $chapterColor['text'] }}">{{ $chapterIcon }}</div>
            </div>
            <div class="h-1.5 bg-white/5 rounded-full mb-1.5">
                <div class="h-1.5 rounded-full chapter-bar" style="width:{{ $chapterPctVal }}%;background:{{ $chapterColor['bar'] }};"></div>
            </div>
            <div class="flex items-center justify-between">
                @if($nextChapterWorth)<span class="text-[10px] text-gray-500">Ksh {{ number_format($progress->netWorthToNextChapter()) }} to next</span>@endif
                <a href="{{ route('life.timeline') }}" class="text-[10px] {{ $chapterColor['text'] }} font-semibold ml-auto hover:opacity-80">Timeline →</a>
            </div>
        </div>

        {{-- Mobile city news --}}
        @if($recentNotifications->count() > 0)
        <div class="card rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="font-black text-white text-sm">City News</div>
                <div class="text-xs text-gray-500">See what's happening in Pesa City</div>
            </div>
            <div class="space-y-2">
                @foreach($recentNotifications->take(4) as $notif)
                <div class="notif-{{ $notif->type }} notif-item rounded-lg px-3 py-2.5 flex items-center gap-2.5">
                    <span class="text-base flex-shrink-0">{{ $notif->icon }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-white truncate">{{ $notif->title }}</div>
                        <div class="text-[10px] text-gray-500">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            <button @click="showNotifPanel=true" class="mt-3 text-xs text-indigo-400 font-semibold hover:text-indigo-300 transition-colors">View All News →</button>
        </div>
        @endif

        {{-- Mobile quick actions --}}
        <div class="card rounded-2xl p-4">
            <div class="text-xs font-black text-white mb-3">Quick Actions</div>
            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    ['bank','Bank',route('savings.index')],
                    ['shopping-bag','Market',route('marketplace')],
                    ['briefcase','Jobs',route('life.career')],
                    ['book','Study',route('money-toolkit')],
                    ['trend-up','Invest',route('portfolio')],
                    ['house','Home',route('life.board')],
                ] as [$ico,$lbl,$url])
                <a href="{{ $url }}" class="qa-tile rounded-xl py-3 text-center flex flex-col items-center gap-1.5">
                    <x-icon :name="$ico" class="w-5 h-5" />
                    <span class="text-[10px] font-bold text-gray-400">{{ $lbl }}</span>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Mobile matured investments --}}
        @if($maturedInvestments->count() > 0)
        <div class="rounded-2xl p-4" style="background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(5,150,105,0.07));border:1px solid rgba(16,185,129,0.4);animation:bonusPulse 2s ease-in-out infinite;">
            <h3 class="font-black text-emerald-400 mb-3 flex items-center gap-2">
                <span class="animate-bounce"><x-icon name="coin" class="w-4 h-4" /></span> Investment{{ $maturedInvestments->count()>1?'s':'' }} Ready!
                <span class="ml-auto bg-emerald-500/20 text-emerald-300 text-xs font-bold px-2 py-0.5 rounded-full">{{ $maturedInvestments->count() }}</span>
            </h3>
            <div class="space-y-2">
                @foreach($maturedInvestments as $inv)
                @php $returnAmt = round($inv->amount * (1 + $inv->return_rate / 100), 2); @endphp
                <div class="rounded-xl p-3 flex items-center gap-3" id="matured-inv-mob-{{ $inv->id }}"
                     style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);">
                    <x-icon name="coin" class="w-5 h-5 text-emerald-400" />
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-emerald-300 truncate">{{ $inv->label }}</div>
                        <div class="text-xs text-gray-400 inline-flex items-center gap-1"><x-icon name="trend-up" class="w-3 h-3" /> <span class="text-emerald-400 font-black">Ksh {{ number_format($returnAmt) }}</span></div>
                    </div>
                    <button onclick="claimDashInvestment({{ $inv->id }}, this)"
                            class="px-3 py-1.5 rounded-lg text-xs font-black text-white inline-flex items-center gap-1"
                            style="background:linear-gradient(135deg,#10b981,#059669);"><x-icon name="coin" class="w-3 h-3" /> Claim</button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /mobile layout --}}

</div>{{-- /max-w container --}}

{{-- SMART MONEY TOOLS — summary card (full toolkit lives on its own page) --}}
<div class="max-w-[1400px] mx-auto px-3 sm:px-5 pb-8 mt-6">
    <a href="{{ route('money-toolkit') }}"
       class="flex items-center gap-4 rounded-3xl p-5 sm:p-6 transition-all hover:scale-[1.01]"
       style="background:linear-gradient(135deg,rgba(16,185,129,0.09),rgba(6,182,212,0.04));border:1px solid rgba(16,185,129,0.25);">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,rgba(16,185,129,0.25),rgba(5,150,105,0.15));border:1px solid rgba(16,185,129,0.3);"><x-icon name="toolbox" class="w-6 h-6 text-emerald-400" /></div>
        <div class="flex-1 min-w-0">
            <h2 class="text-lg sm:text-xl font-black text-white">Smart Money Tools</h2>
            <p class="text-gray-400 text-sm mt-0.5">Bajeti, Lengo, real bills, savings goals &amp; expense tracking — all in one place.</p>
        </div>
        <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>


{{-- Legacy mobile bottom nav removed — <x-mobile-bottom-nav> below is now the
     only bottom bar (avoids two competing fixed-bottom navs on mobile). --}}

<script>
/* ONBOARDING WIZARD */
function onboardingWizard(steps) {
    return {
        steps: steps && steps.length ? steps : [],
        step: 0,
        // Closing early hides for THIS session only (it used to permanently
        // mark onboarding complete — one stray click killed the tour forever).
        // Only finishing the last step persists completion.
        visible: sessionStorage.getItem('wizard_snoozed') !== '1',
        finish() {
            this.visible = false;
            fetch('{{ route('onboarding.complete') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            });
        },
        next() {
            if (this.step < this.steps.length - 1) { this.step++; }
            else { this.finish(); }
        },
        close() {
            this.visible = false;
            sessionStorage.setItem('wizard_snoozed', '1');
        },
    }
}

/* DASHBOARD CONTROLLER */
function dashboard() {
    return {
        showNotifPanel:false,
        toast:false,
        toastMsg:'',
        claimingBonus:false,
        unreadCount: {{ (int) ($unreadCount ?? 0) }},

        init() {
            // Notifications are marked read the moment the player actually opens
            // the bell panel (openNotifPanel), not silently in the background —
            // so the unread badge only clears once they've genuinely seen them.
            @if($leveledUp)
            window.addEventListener('DOMContentLoaded', () => { if(document.getElementById('levelup-overlay')) dashboardConfetti(); });
            @endif
        },

        openNotifPanel() {
            this.showNotifPanel = true;
            if (this.unreadCount === 0) return;
            this.unreadCount = 0;
            fetch('{{ route('game.notifications.read') }}', {
                method:'POST',
                headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
            });
        },

        claimBonus() {
            this.claimingBonus=true;
            fetch('{{ route('game.claim-bonus') }}',{
                method:'POST',
                headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'}
            })
            .then(r=>r.json())
            .then(data=>{
                this.claimingBonus=false;
                if(data.error){this.showToast('⚠️ '+data.error);}
                else{this.playSound('bonus');this.showToast('✅ '+data.message);setTimeout(()=>window.location.reload(),1800);}
            })
            .catch(()=>{this.claimingBonus=false;this.showToast('Something went wrong. Try again.');});
        },

        showToast(msg){this.toastMsg=msg;this.toast=true;setTimeout(()=>this.toast=false,3200);},

        playSound(type) {
            try {
                const ctx=new(window.AudioContext||window.webkitAudioContext)();
                if(type==='bonus'){
                    [[659,.0],[784,.15],[1047,.3]].forEach(([freq,t])=>{
                        const o=ctx.createOscillator(),g=ctx.createGain();
                        o.connect(g);g.connect(ctx.destination);o.type='sine';
                        o.frequency.setValueAtTime(freq,ctx.currentTime+t);
                        g.gain.setValueAtTime(.25,ctx.currentTime+t);
                        g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+t+.35);
                        o.start(ctx.currentTime+t);o.stop(ctx.currentTime+t+.35);
                    });
                }
            } catch(e) {}
        }
    }
}

/* INVESTMENT CLAIM */
function claimDashInvestment(investId, btn) {
    btn.disabled=true; btn.textContent='⏳';
    fetch(`/game/investments/${investId}/claim`,{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}
    })
    .then(r=>r.json())
    .then(data=>{
        if(data.returns!==undefined){
            ['matured-inv-'+investId,'matured-inv-mob-'+investId].forEach(id=>{
                const card=document.getElementById(id);
                if(card){card.style.transition='all .5s ease';card.style.opacity='0';card.style.transform='scale(0.95) translateY(-10px)';setTimeout(()=>card.remove(),500);}
            });
            dashboardConfetti();
        }
    })
    .catch(()=>{btn.disabled=false;btn.textContent='💰 Claim';});
}

/* CONFETTI */
function dashboardConfetti() {
    const container=document.getElementById('dash-confetti')||document.body;
    const colors=['#10b981','#34d399','#6ee7b7','#f59e0b','#fbbf24','#a78bfa','#6366f1'];
    for(let i=0;i<80;i++){
        setTimeout(()=>{
            const el=document.createElement('div');
            el.style.cssText=`position:fixed;top:-20px;z-index:9999;pointer-events:none;left:${Math.random()*100}vw;width:${5+Math.random()*10}px;height:${5+Math.random()*10}px;background:${colors[Math.floor(Math.random()*colors.length)]};border-radius:${Math.random()>.5?'50%':'2px'};animation:confettiFall ${1.5+Math.random()*2}s linear forwards;`;
            document.body.appendChild(el);
            setTimeout(()=>el.remove(),3500);
        },i*30);
    }
}
</script>

<script>
if('serviceWorker' in navigator){navigator.serviceWorker.register('/sw.js').catch(()=>{});}
</script>
@include('partials.game-calendar')
<x-mobile-bottom-nav active="home" />
</body>
</html>
