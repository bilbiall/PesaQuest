<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PesaQuest – {{ $node->title }}</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { background: #07060f; }
        [x-cloak] { display: none !important; }

        .particle-bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
        .particle { position: absolute; border-radius: 50%; opacity: 0; animation: floatUp linear infinite; }
        @keyframes floatUp {
            0%   { opacity: 0; transform: translateY(100vh) scale(0.5); }
            10%  { opacity: 0.6; }
            90%  { opacity: 0.2; }
            100% { opacity: 0; transform: translateY(-10vh) scale(1.2); }
        }
        .particle:nth-child(1)  { left: 5%;  width: 4px;  height: 4px;  background: #6366f1; animation-duration: 12s; animation-delay: 0s; }
        .particle:nth-child(2)  { left: 15%; width: 6px;  height: 6px;  background: #a78bfa; animation-duration: 18s; animation-delay: 2s; }
        .particle:nth-child(3)  { left: 25%; width: 3px;  height: 3px;  background: #f59e0b; animation-duration: 14s; animation-delay: 4s; }
        .particle:nth-child(4)  { left: 35%; width: 5px;  height: 5px;  background: #10b981; animation-duration: 16s; animation-delay: 1s; }
        .particle:nth-child(5)  { left: 45%; width: 4px;  height: 4px;  background: #8b5cf6; animation-duration: 20s; animation-delay: 6s; }
        .particle:nth-child(6)  { left: 55%; width: 6px;  height: 6px;  background: #6366f1; animation-duration: 11s; animation-delay: 3s; }
        .particle:nth-child(7)  { left: 65%; width: 3px;  height: 3px;  background: #f59e0b; animation-duration: 17s; animation-delay: 5s; }
        .particle:nth-child(8)  { left: 75%; width: 5px;  height: 5px;  background: #a78bfa; animation-duration: 13s; animation-delay: 8s; }
        .particle:nth-child(9)  { left: 85%; width: 4px;  height: 4px;  background: #10b981; animation-duration: 19s; animation-delay: 2s; }
        .particle:nth-child(10) { left: 92%; width: 6px;  height: 6px;  background: #6366f1; animation-duration: 15s; animation-delay: 7s; }
        .particle:nth-child(11) { left: 8%;  width: 3px;  height: 3px;  background: #ec4899; animation-duration: 22s; animation-delay: 9s; }
        .particle:nth-child(12) { left: 48%; width: 5px;  height: 5px;  background: #f59e0b; animation-duration: 10s; animation-delay: 0.5s; }

        .bg-orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
        .bg-orb-1 { width: 500px; height: 500px; top: -150px; left: -100px;  background: rgba(99,102,241,0.12); }
        .bg-orb-2 { width: 400px; height: 400px; bottom: -100px; right: -80px; background: rgba(139,92,246,0.10); }
        .bg-orb-3 { width: 300px; height: 300px; top: 40%; left: 50%; background: rgba(245,158,11,0.06); transform: translateX(-50%); }

        .game-content { position: relative; z-index: 10; }

        .hud-bar {
            background: rgba(7,6,15,0.8);
            backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .xp-badge {
            background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(139,92,246,0.15));
            border: 1px solid rgba(99,102,241,0.4);
            box-shadow: 0 0 12px rgba(99,102,241,0.2);
        }
        .level-bar-fill {
            transition: width 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: linear-gradient(90deg, #6366f1, #a78bfa, #f59e0b);
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
        }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        .scenario-card {
            background: linear-gradient(145deg, rgba(99,102,241,0.10) 0%, rgba(139,92,246,0.07) 40%, rgba(15,14,30,0.6) 100%);
            border: 1px solid rgba(99,102,241,0.22);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.03), 0 25px 50px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .choice-btn {
            background: linear-gradient(135deg, rgba(255,255,255,0.035), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.09);
            transition: all 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative; overflow: hidden;
        }
        .choice-btn::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.10)); opacity: 0; transition: opacity 0.25s; }
        .choice-btn:hover::before { opacity: 1; }
        .choice-btn:hover { border-color: rgba(99,102,241,0.55); transform: translateY(-3px) scale(1.01); box-shadow: 0 15px 40px -8px rgba(99,102,241,0.35), 0 0 0 1px rgba(99,102,241,0.15); }
        .choice-btn:active { transform: scale(0.98) translateY(0); }
        .choice-btn.selected { background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(139,92,246,0.20)); border-color: rgba(99,102,241,0.7); box-shadow: 0 0 0 2px rgba(99,102,241,0.3), 0 15px 35px rgba(99,102,241,0.3); }
        .choice-btn.choice-disabled { opacity: 0.35; pointer-events: none; }
        .choice-index-badge { background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.2)); border: 1px solid rgba(99,102,241,0.4); }

        .lesson-card { background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(5,150,105,0.06)); border: 1px solid rgba(16,185,129,0.28); box-shadow: 0 0 30px rgba(16,185,129,0.08); }
        .invest-banner { background: linear-gradient(135deg, rgba(245,158,11,0.18), rgba(251,191,36,0.10)); border: 1px solid rgba(245,158,11,0.4); box-shadow: 0 0 30px rgba(245,158,11,0.12); animation: investPulse 2s ease-in-out infinite; }
        @keyframes investPulse { 0%, 100% { box-shadow: 0 0 20px rgba(245,158,11,0.12); } 50% { box-shadow: 0 0 40px rgba(245,158,11,0.25); } }

        .points-pop-anim { animation: pointsFly 1.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 200; pointer-events: none; }
        @keyframes pointsFly { 0% { opacity: 0; transform: translate(-50%, -30%) scale(0.5); } 20% { opacity: 1; transform: translate(-50%, -60%) scale(1.4); } 70% { opacity: 1; transform: translate(-50%, -90%) scale(1.1); } 100% { opacity: 0; transform: translate(-50%, -130%) scale(0.9); } }

        .confetti-piece { position: fixed; top: -20px; z-index: 300; pointer-events: none; border-radius: 3px; animation: confettiFall linear forwards; }
        @keyframes confettiFall { 0% { transform: translateY(0) rotateZ(0deg) rotateX(0deg); opacity: 1; } 80% { opacity: 1; } 100% { transform: translateY(110vh) rotateZ(720deg) rotateX(180deg); opacity: 0; } }

        .coin-anim { position: fixed; z-index: 250; pointer-events: none; animation: coinDrop 1s cubic-bezier(0.22, 1, 0.36, 1) forwards; font-size: 1.5rem; }
        @keyframes coinDrop { 0% { opacity: 0; transform: translateY(-30px) scale(0.5); } 30% { opacity: 1; transform: translateY(10px) scale(1.3); } 80% { opacity: 1; transform: translateY(40px) scale(1); } 100% { opacity: 0; transform: translateY(80px) scale(0.8); } }

        .slide-in-up { animation: slideInUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        @keyframes slideInUp { from { opacity: 0; transform: translateY(32px); } to { opacity: 1; transform: translateY(0); } }
        .slide-in-right { animation: slideInRight 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        .fade-in { animation: fadeIn 0.4s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .result-card { background: linear-gradient(145deg, rgba(139,92,246,0.14) 0%, rgba(99,102,241,0.08) 50%, rgba(15,14,30,0.7) 100%); border: 1px solid rgba(139,92,246,0.28); }
        .stat-box { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); }

        .bottom-hud { background: rgba(7,6,15,0.88); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 -4px 30px rgba(0,0,0,0.4); }

        .notif-drawer { background: rgba(12,11,22,0.97); backdrop-filter: blur(24px); border-left: 1px solid rgba(99,102,241,0.25); box-shadow: -20px 0 60px rgba(0,0,0,0.5); }
        .notif-item { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 0.875rem; transition: background 0.2s; }
        .notif-item.investment-notif { background: rgba(16,185,129,0.07); border-color: rgba(16,185,129,0.2); }
        .notif-bell { position: relative; cursor: pointer; transition: transform 0.2s; }
        .notif-bell:hover { transform: scale(1.1) rotate(-15deg); }
        .notif-badge { position: absolute; top: -6px; right: -6px; background: linear-gradient(135deg, #ef4444, #f97316); border: 2px solid #07060f; border-radius: 50%; min-width: 18px; height: 18px; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; animation: pulseBadge 1.5s ease-in-out infinite; }
        @keyframes pulseBadge { 0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); } 50% { box-shadow: 0 0 0 6px rgba(239,68,68,0); } }

        .node-badge { background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.35); }
        .continue-btn { background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa); background-size: 200% 100%; transition: all 0.3s; position: relative; overflow: hidden; }
        .continue-btn::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1), transparent); transform: translateX(-100%); transition: transform 0.6s; }
        .continue-btn:hover::after { transform: translateX(100%); }
        .continue-btn:hover { background-position: 100% 0; transform: translateY(-2px); box-shadow: 0 15px 40px rgba(99,102,241,0.45); }
        .final-lesson-card { background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08)); border: 1px solid rgba(99,102,241,0.3); box-shadow: 0 0 40px rgba(99,102,241,0.1); }
        .streak-fire { animation: fireFlicker 1.5s ease-in-out infinite alternate; }
        @keyframes fireFlicker { 0% { transform: scale(1) rotate(-2deg); filter: brightness(1); } 100% { transform: scale(1.15) rotate(2deg); filter: brightness(1.3); } }

        /* ─── Floating Mentor Bubble ───── */
        .mentor-bubble {
            background: rgba(12,11,22,0.96);
            border: 1px solid rgba(245,158,11,0.35);
            box-shadow: 0 20px 60px rgba(0,0,0,0.55), 0 0 0 1px rgba(245,158,11,0.08), 0 0 40px rgba(245,158,11,0.12);
            backdrop-filter: blur(20px);
        }
        .mentor-avatar-float {
            background: linear-gradient(135deg, rgba(245,158,11,0.25), rgba(251,191,36,0.12));
            border: 2px solid rgba(245,158,11,0.45);
            box-shadow: 0 0 20px rgba(245,158,11,0.25), 0 0 40px rgba(245,158,11,0.1);
            animation: mentorPulse 3s ease-in-out infinite;
        }
        @keyframes mentorPulse {
            0%,100% { box-shadow: 0 0 15px rgba(245,158,11,0.2), 0 0 35px rgba(245,158,11,0.08); }
            50%      { box-shadow: 0 0 25px rgba(245,158,11,0.4), 0 0 55px rgba(245,158,11,0.15); }
        }
        .mentor-close-btn { transition: all 0.2s; }
        .mentor-close-btn:hover { transform: scale(1.2) rotate(90deg); color: white; }

        /* ─── Market event popup ──────── */
        .market-popup { background: rgba(12,11,22,0.97); border: 1px solid rgba(99,102,241,0.3); backdrop-filter: blur(24px); box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04); }
        .market-bonus { border-color: rgba(16,185,129,0.4) !important; }
        .market-penalty { border-color: rgba(239,68,68,0.4) !important; }

        /* ─── Challenge panel ─────────── */
        .challenge-drawer { background: rgba(12,11,22,0.97); backdrop-filter: blur(24px); border-right: 1px solid rgba(99,102,241,0.2); box-shadow: 20px 0 60px rgba(0,0,0,0.5); }
        .challenge-item { background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.06); border-radius: 0.875rem; }
        .challenge-item.completed { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.2); }
        .challenge-progress-bar { background: rgba(255,255,255,0.06); border-radius: 9999px; overflow: hidden; }
        .challenge-fill { background: linear-gradient(90deg, #6366f1, #a78bfa); height: 100%; border-radius: 9999px; transition: width 0.8s cubic-bezier(0.34,1.56,0.64,1); }

        /* ─── Rating panel ────────────── */
        .rating-btn { border: 1px solid rgba(255,255,255,0.1); transition: all 0.2s; }
        .rating-btn:hover { transform: scale(1.1); }
        .rating-btn.active-up { background: rgba(16,185,129,0.2); border-color: rgba(16,185,129,0.5); }
        .rating-btn.active-down { background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.5); }

        /* ─── Investment claim ────────── */
        .claim-btn { background: linear-gradient(135deg, #10b981, #059669); transition: all 0.2s; }
        .claim-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.4); }

        /* ─── Diary modal ─────────────── */
        .diary-modal { background: rgba(12,11,22,0.98); border: 1px solid rgba(99,102,241,0.2); backdrop-filter: blur(24px); }
        .diary-step { border-left: 2px solid rgba(99,102,241,0.25); }
        .diary-step:last-child { border-left: 2px solid transparent; }

        /* ─── PesaMali card ───────────── */
        .pesamali-card { background: linear-gradient(135deg, rgba(16,185,129,0.14), rgba(5,150,105,0.07)); border: 1px solid rgba(16,185,129,0.3); }

        /* ─── Replay btn ──────────────── */
        .replay-btn { background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(99,102,241,0.15)); border: 1px solid rgba(139,92,246,0.35); transition: all 0.2s; }
        .replay-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(139,92,246,0.3); border-color: rgba(139,92,246,0.6); }

        /* ─── Invest mature claim ─────── */
        .mature-invest-card { background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(5,150,105,0.06)); border: 1px solid rgba(16,185,129,0.35); animation: matGlow 2s ease-in-out infinite; }
        @keyframes matGlow { 0%,100% { box-shadow: 0 0 15px rgba(16,185,129,0.15); } 50% { box-shadow: 0 0 35px rgba(16,185,129,0.35); } }

        /* ─── Payslip modal ──────────── */
        .payslip-modal { background: rgba(10,9,22,0.98); border: 1px solid rgba(245,158,11,0.3); backdrop-filter: blur(24px); box-shadow: 0 30px 80px rgba(0,0,0,0.7); }
        .payslip-row { border-bottom: 1px solid rgba(255,255,255,0.05); }
        .payslip-row:last-child { border-bottom: none; }
        @keyframes slideUp { from { opacity:0; transform:translateY(40px) scale(0.97); } to { opacity:1; transform:translateY(0) scale(1); } }
        .slide-up { animation: slideUp 0.45s cubic-bezier(0.22,1,0.36,1) forwards; }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased" x-data="pesaquest()" x-cloak>

    <div class="particle-bg">
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
    </div>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    {{-- Points pop --}}
    <div x-show="showPointsPop" x-cloak class="points-pop-anim">
        <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-amber-400 text-white font-black text-3xl sm:text-4xl px-8 py-4 rounded-3xl shadow-2xl shadow-indigo-500/40 border border-white/20">
            +<span x-text="earnedPoints"></span> pts!
        </div>
    </div>

    <div id="confetti-container" class="fixed inset-0 z-[300] pointer-events-none overflow-hidden"></div>

    {{-- ══ MARKET EVENT POPUP ══ --}}
    @if($marketEvent)
    <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 700)" x-show="show"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 z-[400] flex items-center justify-center px-4 bg-black/60 backdrop-blur-sm">
        <div class="market-popup rounded-3xl p-7 max-w-sm w-full text-center
                    {{ $marketEvent->effect_type === 'bonus' ? 'market-bonus' : 'market-penalty' }}">
            <div class="text-6xl mb-4">{{ $marketEvent->icon }}</div>
            <h3 class="font-black text-xl mb-2 {{ $marketEvent->effect_type === 'bonus' ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $marketEvent->title }}
            </h3>
            <p class="text-gray-300 text-sm leading-relaxed mb-4">{{ $marketEvent->description }}</p>
            @if($marketEvent->effect_amount > 0)
            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-xl mb-5
                        {{ $marketEvent->effect_type === 'bonus' ? 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-400' : 'bg-red-500/15 border border-red-500/30 text-red-400' }}">
                <span class="font-black text-lg">{{ $marketEvent->effect_type === 'bonus' ? '+' : '-' }}Ksh {{ number_format($marketEvent->effect_amount) }}</span>
                <span class="text-sm opacity-70">to your balance</span>
            </div>
            @endif
            <p class="text-xs text-gray-600 mb-5">This is a real-world event affecting your game balance</p>
            <button @click="show = false"
                    class="w-full py-3 rounded-xl font-bold text-white {{ $marketEvent->effect_type === 'bonus' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-red-700 hover:bg-red-600' }} transition-colors">
                Got it! Continue →
            </button>
        </div>
    </div>
    @endif

    {{-- ══ MATURE INVESTMENT BANNER ══ --}}
    @if($pendingInvestments->count() > 0)
    <div class="fixed top-0 left-0 right-0 z-[60] px-4 pt-20" id="invest-banner-area">
        @foreach($pendingInvestments as $inv)
        <div class="mature-invest-card rounded-2xl px-4 py-3 mb-2 max-w-2xl mx-auto flex items-center gap-3"
             id="invest-banner-{{ $inv->id }}">
            <span class="text-2xl flex-shrink-0">💰</span>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-emerald-400 text-sm">Investment Matured!</div>
                <div class="text-xs text-gray-400">{{ $inv->label }} — Ksh {{ number_format($inv->amount) }} + returns ready</div>
            </div>
            <button @click="claimInvestment({{ $inv->id }})"
                    :disabled="claimingInvest === {{ $inv->id }}"
                    class="claim-btn text-white text-xs font-black px-4 py-2 rounded-xl flex-shrink-0">
                <span x-show="claimingInvest !== {{ $inv->id }}">💎 Claim</span>
                <span x-show="claimingInvest === {{ $inv->id }}">...</span>
            </button>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══ TOP HUD BAR ══ --}}
    <header class="hud-bar sticky top-0 z-40">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between gap-3">

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition-colors p-1.5 rounded-xl hover:bg-white/5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <a href="{{ route('landing') }}">
                    <img src="{{ asset('moski-logo.png') }}" alt="Moski"
                         class="h-9 w-auto rounded-lg object-cover opacity-90 hover:opacity-100 transition-opacity">
                </a>
            </div>

            <div class="flex items-center gap-2">
                <div class="xp-badge flex items-center gap-1.5 px-3 py-1.5 rounded-xl">
                    <span class="text-sm">⭐</span>
                    <span class="text-sm font-bold text-indigo-200" x-text="totalPoints.toLocaleString()">{{ $progress->points_total }}</span>
                    <span class="text-xs text-indigo-400/70">XP</span>
                </div>
                <div class="text-xs text-gray-500 font-medium px-2">Lv.<span class="text-purple-400 font-bold" x-text="currentLevel">{{ $progress->level }}</span></div>
            </div>

            <div class="flex items-center gap-3">
                @if($streak->current_streak > 0)
                <div class="flex items-center gap-1">
                    <span class="streak-fire text-lg">🔥</span>
                    <span class="text-sm font-bold text-orange-400">{{ $streak->current_streak }}</span>
                </div>
                @endif

                {{-- Daily challenges button --}}
                <button @click="showChallenges = !showChallenges"
                        class="relative text-gray-400 hover:text-amber-400 transition-colors p-1.5 rounded-xl hover:bg-white/5"
                        title="Daily Challenges">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    @if($challenges->where('user_completed', false)->count() > 0)
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-amber-400 rounded-full border border-[#07060f]"></div>
                    @endif
                </button>

                {{-- Notification bell --}}
                <div class="notif-bell" @click="toggleNotifications()">
                    <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <div class="notif-badge" x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount"></div>
                </div>
            </div>
        </div>

        <div class="h-1 bg-white/5">
            <div class="level-bar-fill h-1" :style="`width: ${levelProgress}%`" style="width: {{ $progress->level_progress_percent }}%"></div>
        </div>
    </header>

    {{-- ══ MAIN GAME AREA ══ --}}
    <main class="game-content max-w-2xl mx-auto px-4 py-8 pb-32">

        {{-- Story context banner --}}
        @if($story)
        <div class="rounded-2xl px-4 py-2.5 mb-4 flex items-center gap-3"
             style="background:linear-gradient(135deg,{{ $story->color ?? '#6366f1' }}18,{{ $story->color ?? '#6366f1' }}08);border:1px solid {{ $story->color ?? '#6366f1' }}35;">
            <span class="text-xl flex-shrink-0">{{ $story->icon ?? '📖' }}</span>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-black uppercase tracking-widest" style="color:{{ $story->color ?? '#a5b4fc' }}80;">Story Arc</div>
                <div class="text-sm font-black leading-tight" style="color:{{ $story->color ?? '#a5b4fc' }}">{{ $story->title }}</div>
            </div>
            <div class="text-[10px] text-gray-600 flex-shrink-0 text-right">Ages {{ $node->age_group }}</div>
        </div>
        @endif

        {{-- Age badge + node type --}}
        <div class="flex items-center justify-between mb-5">
            <div class="node-badge px-3 py-1.5 rounded-full flex items-center gap-2">
                <span class="text-lg leading-none">{{ $node->icon ?? '📍' }}</span>
                <span class="text-xs font-bold text-indigo-300 uppercase tracking-widest">
                    @if($node->type === 'ending') Final Chapter
                    @elseif($node->type === 'result') Result
                    @else Decision Point
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-white/5 border border-white/10 px-2.5 py-1 rounded-full text-gray-400">Ages {{ $node->age_group }}</span>
            </div>
        </div>

        {{-- ─── SCENARIO CARD ─── --}}
        <div class="scenario-card rounded-3xl mb-7 slide-in-up overflow-hidden">

            @if($node->image_url)
            <div class="relative w-full" style="height:clamp(160px,28vw,260px);">
                <img src="{{ $node->image_url }}" alt="{{ $node->title }}" class="w-full h-full object-cover" onerror="this.parentElement.style.display='none'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                <h2 class="absolute bottom-4 left-6 right-6 text-2xl sm:text-3xl font-black leading-tight drop-shadow-lg" style="color: {{ $node->theme_color ?? '#a5b4fc' }}">{{ $node->title }}</h2>
            </div>
            <div class="p-7">
            @else
            <div class="p-7">
            <h2 class="text-2xl sm:text-3xl font-black mb-4 leading-tight" style="color: {{ $node->theme_color ?? '#a5b4fc' }}">{{ $node->title }}</h2>
            @endif

            <p class="text-gray-200 text-base sm:text-lg leading-relaxed {{ $node->image_url ? 'mt-4' : '' }}">{{ $node->scenario_text }}</p>

            @if(in_array($node->type, ['result', 'ending']) && isset($node->metadata['final_lesson']))
            <div class="final-lesson-card rounded-2xl p-5 mt-4">
                <div class="flex items-start gap-3">
                    <span class="text-2xl flex-shrink-0">🌟</span>
                    <div>
                        <div class="font-bold text-indigo-300 mb-1 text-sm uppercase tracking-wide">Key Takeaway</div>
                        <p class="text-gray-200 text-sm leading-relaxed">{{ $node->metadata['final_lesson'] }}</p>
                    </div>
                </div>
            </div>
            @endif
            </div>
        </div>

        {{-- ══ CHOICE SECTION ══ --}}
        @if(!in_array($node->type, ['result', 'ending']))

        <div x-show="!choiceMade">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 px-1">What do you do?</h3>
            <div class="space-y-3">
                @foreach($choices as $i => $choice)
                <button class="choice-btn w-full text-left rounded-2xl px-5 py-4 flex items-center gap-4 group"
                        :class="{ 'selected': selectedChoice === {{ $choice->id }}, 'choice-disabled': choiceMade && selectedChoice !== {{ $choice->id }} }"
                        :disabled="choiceMade"
                        @click="makeChoice({{ $choice->id }}, {{ $choice->points }})">
                    <div class="choice-index-badge w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-sm font-black text-indigo-300">{{ $loop->iteration }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-white text-sm sm:text-base leading-snug">{{ $choice->label }}</div>
                        @if($choice->description)
                        <div class="text-xs text-gray-400 mt-0.5 leading-relaxed">{{ $choice->description }}</div>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-600 group-hover:text-indigo-400 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                @endforeach
            </div>
            <p class="text-center text-xs text-gray-600 mt-4">Press <kbd class="bg-white/5 border border-white/10 px-1.5 py-0.5 rounded text-gray-400">1</kbd>–<kbd class="bg-white/5 border border-white/10 px-1.5 py-0.5 rounded text-gray-400">{{ $choices->count() }}</kbd> to choose</p>
        </div>

        <div x-show="choiceMade" x-cloak class="slide-in-up space-y-4">
            <div x-show="earnedPoints > 0" class="text-center">
                <div class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-3 rounded-2xl shadow-xl shadow-indigo-500/30 border border-white/10">
                    <span class="text-2xl font-black text-white">+<span x-text="earnedPoints"></span> XP</span>
                    <span class="text-lg">⭐</span>
                </div>
            </div>

            <div x-show="lessonText" class="lesson-card rounded-2xl p-6">
                <div class="flex items-start gap-3">
                    <span class="text-2xl flex-shrink-0">💡</span>
                    <div>
                        <div class="font-bold text-emerald-400 mb-2 text-sm uppercase tracking-wide">Financial Insight</div>
                        <p class="text-gray-200 leading-relaxed" x-text="lessonText"></p>
                    </div>
                </div>
            </div>

            <div x-show="investmentData" x-cloak class="invest-banner rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <span class="text-3xl flex-shrink-0">📈</span>
                    <div>
                        <div class="font-black text-amber-400 text-base mb-1">Investment Created!</div>
                        <p class="text-amber-200/80 text-sm" x-text="investmentData ? `${investmentData.label} — returns in ${investmentData.return_days} game day(s) at ${investmentData.return_rate}% interest.` : ''"></p>
                    </div>
                </div>
            </div>

            <button @click="continueGame()" class="continue-btn w-full text-white font-bold text-lg py-4 rounded-2xl shadow-xl flex items-center justify-center gap-3" :disabled="isLoading">
                <template x-if="isLoading"><span class="flex items-center gap-2"><svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Loading...</span></template>
                <template x-if="!isLoading"><span class="flex items-center gap-2">Continue <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span></template>
            </button>
        </div>

        @else
        {{-- ══ RESULT / ENDING NODE VIEW ══ --}}
        <div class="slide-in-up space-y-5">

            <div class="text-center">
                <div class="text-7xl mb-3 animate-bounce" style="animation-duration: 2s">{{ $node->icon ?? '🏆' }}</div>
                <h3 class="text-2xl sm:text-3xl font-black text-white">
                    @if($node->type === 'ending'){{ ($node->metadata['ending_type'] ?? 'great') === 'great' ? 'You crushed it!' : 'Lesson Learned!'}}
                    @else{{ $node->title }}@endif
                </h3>
            </div>

            {{-- Stats --}}
            <div class="result-card rounded-3xl p-6">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Your Journey Stats</h4>
                <div class="grid grid-cols-2 gap-3">
                    <div class="stat-box rounded-2xl p-4"><div class="text-xs text-gray-500 mb-1">Balance</div><div class="text-xl font-black text-emerald-400">Ksh {{ number_format($progress->balance) }}</div></div>
                    <div class="stat-box rounded-2xl p-4"><div class="text-xs text-gray-500 mb-1">Total XP</div><div class="text-xl font-black text-indigo-400">{{ number_format($progress->points_total) }}</div></div>
                    <div class="stat-box rounded-2xl p-4"><div class="text-xs text-gray-500 mb-1">Level</div><div class="text-xl font-black text-purple-400">{{ $progress->level }}</div></div>
                    <div class="stat-box rounded-2xl p-4"><div class="text-xs text-gray-500 mb-1">Decisions Made</div><div class="text-xl font-black text-amber-400">{{ count($progress->path_history ?? []) }}</div></div>
                </div>
            </div>

            {{-- Scenario Rating --}}
            <div class="rounded-2xl p-5" style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.07);">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Rate this scenario</div>
                <div class="flex items-center gap-4"
                     x-data="ratingPanel({{ $node->id }}, {{ $myRating ?? 'null' }}, {{ $ratingsSummary['up'] ?? 0 }}, {{ $ratingsSummary['down'] ?? 0 }})">
                    <button @click="rate(1)" :disabled="submitting"
                            class="rating-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-gray-300"
                            :class="myRating === 1 ? 'active-up' : ''">
                        <span class="text-lg">👍</span>
                        <span x-text="up"></span>
                    </button>
                    <button @click="rate(-1)" :disabled="submitting"
                            class="rating-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-gray-300"
                            :class="myRating === -1 ? 'active-down' : ''">
                        <span class="text-lg">👎</span>
                        <span x-text="down"></span>
                    </button>
                    <span class="text-xs text-gray-600 ml-auto">Your feedback helps us improve!</span>
                </div>
            </div>

            {{-- Financial Diary button --}}
            <button @click="openDiary()"
                    class="w-full flex items-center gap-3 rounded-2xl px-5 py-4 text-left transition-all"
                    style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2);">
                <span class="text-2xl">📖</span>
                <div>
                    <div class="font-bold text-indigo-300 text-sm">Your Financial Diary</div>
                    <div class="text-xs text-gray-500">See your journey narrative with insights</div>
                </div>
                <svg class="w-4 h-4 text-gray-600 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>

            {{-- PesaMali savings challenge --}}
            <div class="pesamali-card rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <span class="text-3xl flex-shrink-0">🌍</span>
                    <div>
                        <div class="font-black text-emerald-400 text-base mb-1">Real-World Challenge</div>
                        <p class="text-gray-300 text-sm leading-relaxed mb-3">You just finished a financial scenario — now try it in real life! Start a savings challenge today.</p>
                        <div class="bg-white/5 border border-white/10 rounded-xl p-3 text-xs text-gray-400 leading-relaxed">
                            💡 <strong class="text-gray-300">PesaMali Tip:</strong> Save at least <strong class="text-emerald-400">Ksh 50 daily</strong> this week. That's Ksh 350 by Sunday — your first real emergency fund building block!
                        </div>
                    </div>
                </div>
            </div>

            {{-- Zero balance panel --}}
            @if($progress->balance <= 0)
            <div class="rounded-2xl p-4" style="background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(220,38,38,0.06)); border:1px solid rgba(239,68,68,0.25);">
                <div class="flex items-start gap-3 mb-3">
                    <span class="text-2xl flex-shrink-0">😬</span>
                    <div>
                        <div class="font-bold text-red-300 text-sm">Balance at zero!</div>
                        <p class="text-xs text-gray-400 mt-0.5">Every great investor has faced setbacks. Here's how to recover:</p>
                    </div>
                </div>
                @php $canBonus = !auth()->user()->getOrCreateProgress()->last_bonus_at || !\Carbon\Carbon::parse(auth()->user()->getOrCreateProgress()->last_bonus_at)->isToday(); @endphp
                @if($canBonus)
                <button @click="claimBonus()" :disabled="claimingBonus" class="w-full flex items-center gap-2 bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/30 text-emerald-300 text-sm font-bold px-4 py-3 rounded-xl transition-all text-left">
                    <span>🎁</span><span x-show="!claimingBonus">Claim Daily Bonus — Ksh 1,000</span><span x-show="claimingBonus">Claiming...</span>
                </button>
                @endif
            </div>
            @endif

            {{-- Action buttons --}}
            <div class="grid grid-cols-2 gap-3">
                <form action="{{ route('game.next-scenario') }}" method="POST">
                    @csrf
                    <button type="submit" class="continue-btn w-full text-white font-bold py-4 rounded-2xl shadow-xl flex items-center justify-center gap-2">
                        <span>🚀</span> Next Story
                    </button>
                </form>
                <button @click="replayScenario({{ $progress->current_scenario_start_id ?? $node->id }})"
                        class="replay-btn w-full text-purple-300 font-bold py-4 rounded-2xl flex items-center justify-center gap-2">
                    <span>🔄</span> Replay
                </button>
            </div>

            <a href="{{ route('dashboard') }}" class="flex items-center justify-center gap-2 bg-white/5 hover:bg-white/8 border border-white/10 hover:border-white/20 text-white font-semibold py-4 rounded-2xl transition-all w-full">
                <span>📊</span> View Dashboard
            </a>
        </div>
        @endif

    </main>

    {{-- ══ BOTTOM HUD ══ --}}
    <div class="fixed bottom-0 left-0 right-0 z-30 pointer-events-none">
        <div class="max-w-2xl mx-auto px-4 pb-4">
            <div class="bottom-hud rounded-2xl px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="text-xs text-gray-500">Level <span class="text-purple-400 font-black" x-text="currentLevel">{{ $progress->level }}</span></div>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-1.5 bg-white/8 rounded-full overflow-hidden">
                            <div class="level-bar-fill h-full rounded-full" :style="`width: ${levelProgress}%`" style="width: {{ $progress->level_progress_percent }}%"></div>
                        </div>
                        <div class="text-xs text-gray-600">{{ $progress->points_to_next_level }} to next</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-sm">💰</span>
                    <span class="text-xs text-gray-400">Ksh</span>
                    <span class="text-sm font-black text-emerald-400" x-text="currentBalance.toLocaleString()">{{ number_format($progress->balance) }}</span>
                    @if($progress->career_field)
                    @php $cm = \App\Services\CareerService::fieldsByKey()[$progress->career_field] ?? ['icon'=>'💼','label'=>'Career','color'=>'#f59e0b']; @endphp
                    <div class="ml-2 flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold"
                         style="background:{{ $cm['color'] }}18;border:1px solid {{ $cm['color'] }}30;color:{{ $cm['color'] }};">
                        {{ $cm['icon'] }} <span class="hidden sm:inline">{{ $progress->career_title ?? $cm['label'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MONTHLY PAYSLIP MODAL ══ --}}
    @if($payslip)
    <div x-data="{ open: true }" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-[450] flex items-center justify-center px-4 py-4 bg-black/75 backdrop-blur-sm"
         style="overflow-y:auto;overscroll-behavior:contain;">
        <div class="payslip-modal rounded-3xl w-full max-w-sm slide-up my-auto">
            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-white/8 text-center">
                <div class="text-4xl mb-2">💰</div>
                <h3 class="text-white font-black text-xl">Monthly Pay Day!</h3>
                @php $cm = \App\Services\CareerService::fieldsByKey()[$progress->career_field] ?? ['icon'=>'💼','label'=>'Career','color'=>'#f59e0b']; @endphp
                <p class="text-sm mt-1" style="color:{{ $cm['color'] }};">{{ $cm['icon'] }} {{ $progress->career_title }}</p>
            </div>

            {{-- Pay slip breakdown --}}
            <div class="px-6 py-4 space-y-0">
                <div class="payslip-row flex justify-between py-2.5">
                    <span class="text-gray-400 text-sm">Gross Salary</span>
                    <span class="text-white font-bold text-sm">Ksh {{ number_format($payslip['gross']) }}</span>
                </div>
                <div class="payslip-row flex justify-between py-2.5">
                    <div>
                        <span class="text-gray-400 text-sm">PAYE Tax</span>
                        <span class="text-gray-600 text-xs ml-1">(Kenya Revenue Authority)</span>
                    </div>
                    <span class="text-red-400 text-sm font-semibold">- Ksh {{ number_format($payslip['paye']) }}</span>
                </div>
                <div class="payslip-row flex justify-between py-2.5">
                    <div>
                        <span class="text-gray-400 text-sm">NHIF</span>
                        <span class="text-gray-600 text-xs ml-1">(Health Insurance)</span>
                    </div>
                    <span class="text-red-400 text-sm font-semibold">- Ksh {{ number_format($payslip['nhif']) }}</span>
                </div>
                <div class="payslip-row flex justify-between py-2.5">
                    <div>
                        <span class="text-gray-400 text-sm">NSSF</span>
                        <span class="text-gray-600 text-xs ml-1">(Pension Fund)</span>
                    </div>
                    <span class="text-red-400 text-sm font-semibold">- Ksh {{ number_format($payslip['nssf']) }}</span>
                </div>
                @if($payslip['loans'] > 0)
                <div class="payslip-row flex justify-between py-2.5">
                    <span class="text-gray-400 text-sm">Loan Installments</span>
                    <span class="text-orange-400 text-sm font-semibold">- Ksh {{ number_format($payslip['loans']) }}</span>
                </div>
                @endif
                <div class="flex justify-between py-3 mt-1 rounded-xl px-3"
                     style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">
                    <span class="text-emerald-400 font-black text-base">NET PAY</span>
                    <span class="text-emerald-400 font-black text-base">Ksh {{ number_format($payslip['net']) }}</span>
                </div>
            </div>

            <div class="px-6 pb-4 text-center">
                <p class="text-gray-600 text-xs mb-4">This has been added to your game balance. Deductions mirror real Kenyan statutory requirements.</p>
                <button @click="open = false; currentBalance += {{ $payslip['net'] }}"
                        class="w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold py-3 rounded-xl hover:opacity-90 transition-all">
                    Received! Let's Go 🚀
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ CAREER UNLOCK CELEBRATION ══ --}}
    <div x-show="showCareerModal" x-cloak
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 z-[460] flex items-center justify-center px-4 py-4 bg-black/80 backdrop-blur-md"
         style="display:none;overflow-y:auto;overscroll-behavior:contain;">
        <div class="relative w-full max-w-sm rounded-3xl overflow-hidden my-auto"
             style="background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);border:1px solid rgba(139,92,246,0.4);box-shadow:0 0 80px rgba(139,92,246,0.3);">

            {{-- Animated glow ring --}}
            <div class="absolute inset-0 rounded-3xl pointer-events-none"
                 style="background:radial-gradient(ellipse at 50% 0%,rgba(139,92,246,0.25) 0%,transparent 70%);"></div>

            {{-- Stars burst --}}
            <div class="absolute top-4 left-4 text-lg opacity-40">✦</div>
            <div class="absolute top-6 right-8 text-sm opacity-30">✦</div>
            <div class="absolute top-10 left-12 text-xs opacity-20">✦</div>

            <div class="relative px-6 py-8 text-center">
                {{-- Icon --}}
                <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl"
                     style="background:linear-gradient(135deg,rgba(139,92,246,0.3),rgba(99,102,241,0.2));border:2px solid rgba(139,92,246,0.5);box-shadow:0 0 30px rgba(139,92,246,0.4);">
                    <span x-text="careerUnlocked?.icon ?? '💼'"></span>
                </div>

                <div class="text-xs font-bold tracking-widest uppercase mb-2"
                     style="color:#a78bfa;">Career Interest Unlocked</div>

                <h2 class="text-white font-black text-2xl mb-1" x-text="careerUnlocked?.title ?? ''"></h2>
                <p class="text-gray-400 text-sm mb-5" x-text="careerUnlocked?.field ? careerUnlocked.field.charAt(0).toUpperCase() + careerUnlocked.field.slice(1) + ' Sector' : ''"></p>

                {{-- Next-step badge (no phantom salary — real pay only comes from an actual Pesa City job) --}}
                <div class="rounded-2xl px-4 py-3 mb-6 inline-block"
                     style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);">
                    <div class="text-gray-400 text-xs mb-0.5">Next Step</div>
                    <div class="text-emerald-400 font-black text-base">Find a job in Pesa City 🏙️</div>
                </div>

                <p class="text-gray-500 text-xs mb-6 leading-relaxed">
                    You've discovered where your interests lie. Head into Pesa City's Opportunity Hub to actually get hired and start earning a real, payable salary.
                </p>

                <button @click="showCareerModal = false; continueGame()"
                        class="w-full text-white font-bold py-3.5 rounded-2xl text-base transition-all hover:opacity-90 active:scale-95"
                        style="background:linear-gradient(135deg,#7c3aed,#6366f1);box-shadow:0 8px 24px rgba(99,102,241,0.35);">
                    Start My Career Journey 🚀
                </button>
            </div>
        </div>
    </div>

    {{-- ══ FLOATING MENTOR BUBBLE ══ --}}
    <div x-data="{ open: true }"
         class="fixed bottom-24 left-4 z-[35] select-none"
         x-cloak>

        {{-- Collapsed: glowing avatar orb --}}
        <div x-show="!open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100"
             @click="open = true"
             class="mentor-avatar-float w-14 h-14 rounded-full cursor-pointer flex items-center justify-center text-2xl relative">
            {{ $mentor['avatar'] }}
            <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-amber-400 rounded-full border-2 border-[#07060f]"
                 style="animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite;"></div>
        </div>

        {{-- Expanded: speech bubble card --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-350"
             x-transition:enter-start="opacity-0 translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-3 scale-95"
             class="flex items-end gap-2.5" style="max-width:280px;">

            {{-- Avatar (click to collapse) --}}
            <div class="mentor-avatar-float w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center text-2xl cursor-pointer"
                 @click="open = false"
                 title="Collapse">
                {{ $mentor['avatar'] }}
            </div>

            {{-- Bubble --}}
            <div class="mentor-bubble rounded-2xl rounded-bl-sm p-4 relative flex-1">
                <button @click="open = false"
                        class="mentor-close-btn absolute top-2 right-2.5 w-6 h-6 flex items-center justify-center rounded-full text-[11px] text-amber-500/50 hover:bg-amber-500/10">
                    ✕
                </button>
                <div class="flex items-baseline gap-1.5 mb-1.5 pr-6">
                    <span class="text-[11px] font-black text-amber-400">{{ $mentor['name'] }}</span>
                    <span class="text-[9px] text-amber-700/70 leading-tight">{{ $mentor['title'] }}</span>
                </div>
                <p class="text-[11px] text-amber-200/75 leading-relaxed">{{ $mentor['tip'] }}</p>
                <button @click="open = false"
                        class="mt-2.5 text-[10px] text-amber-600/50 hover:text-amber-400 transition-colors flex items-center gap-1">
                    Got it <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ══ NOTIFICATION DRAWER ══ --}}
    <div x-show="showNotifications"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm" @click="showNotifications = false" x-cloak></div>

    <div x-show="showNotifications"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-full"
         class="notif-drawer fixed top-0 right-0 h-full w-80 sm:w-96 z-50 flex flex-col" x-cloak>

        <div class="flex items-center justify-between px-5 py-4 border-b border-white/8">
            <div><h3 class="font-black text-white">Notifications</h3><p class="text-xs text-gray-500 mt-0.5"><span x-text="unreadCount"></span> unread</p></div>
            <div class="flex items-center gap-2">
                <button @click="markAllRead()" x-show="unreadCount > 0" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors px-2 py-1 rounded-lg hover:bg-indigo-500/10">Mark all read</button>
                <button @click="showNotifications = false" class="text-gray-400 hover:text-white p-1.5 rounded-xl hover:bg-white/5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <template x-if="notifications.length === 0">
                <div class="text-center py-16"><div class="text-4xl mb-3">🔔</div><p class="text-gray-500 text-sm">No new notifications</p></div>
            </template>
            <template x-for="notif in notifications" :key="notif.id">
                <div class="notif-item p-4" :class="notif.type === 'investment' ? 'investment-notif' : ''">
                    <div class="flex items-start gap-3">
                        <span class="text-xl flex-shrink-0" x-text="notif.icon">💡</span>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-white text-sm leading-tight" x-text="notif.title"></div>
                            <p class="text-gray-400 text-xs leading-relaxed mt-1" x-text="notif.body"></p>
                            <div class="text-xs text-gray-600 mt-2" x-text="new Date(notif.created_at).toLocaleDateString('en-KE', {day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'})"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ══ DAILY CHALLENGES DRAWER ══ --}}
    <div x-show="showChallenges"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm" @click="showChallenges = false" x-cloak></div>

    <div x-show="showChallenges"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-full"
         class="challenge-drawer fixed top-0 left-0 h-full w-80 sm:w-96 z-50 flex flex-col" x-cloak>

        <div class="flex items-center justify-between px-5 py-4 border-b border-white/8">
            <div>
                <h3 class="font-black text-white flex items-center gap-2">🏅 Daily Challenges</h3>
                <p class="text-xs text-gray-500 mt-0.5">Reset at midnight · Complete for bonus XP</p>
            </div>
            <button @click="showChallenges = false" class="text-gray-400 hover:text-white p-1.5 rounded-xl hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            @forelse($challenges as $challenge)
            <div class="challenge-item p-4 {{ $challenge->user_completed ? 'completed' : '' }}">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm flex-shrink-0
                                {{ $challenge->user_completed ? 'bg-emerald-500/20 border border-emerald-500/30' : 'bg-amber-500/15 border border-amber-500/25' }}">
                        {{ $challenge->user_completed ? '✅' : '🎯' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-white text-sm">{{ $challenge->title }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $challenge->description }}</div>
                    </div>
                    <div class="text-xs font-black text-amber-400 flex-shrink-0">+{{ $challenge->xp_bonus }} XP</div>
                </div>
                {{-- Progress bar --}}
                @php
                    $pct = $challenge->target_value > 0
                        ? min(100, round(($challenge->user_progress / $challenge->target_value) * 100))
                        : 0;
                @endphp
                <div class="challenge-progress-bar h-1.5">
                    <div class="challenge-fill" style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex justify-between mt-1.5 text-xs text-gray-600">
                    <span>{{ number_format($challenge->user_progress) }} / {{ number_format($challenge->target_value) }}</span>
                    <span>{{ $pct }}%</span>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-500 text-sm">No challenges active today</div>
            @endforelse
        </div>
    </div>

    {{-- ══ FINANCIAL DIARY MODAL ══ --}}
    <div x-show="showDiary"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center px-4 py-4 bg-black/70 backdrop-blur-sm" style="z-index:9500;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;" @click.self="showDiary = false" x-cloak>
        <div class="diary-modal rounded-3xl max-w-lg w-full my-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/8">
                <div>
                    <h3 class="font-black text-white">📖 Your Financial Diary</h3>
                    <p class="text-xs text-gray-500">Your journey so far, narrated</p>
                </div>
                <button @click="showDiary = false" class="text-gray-400 hover:text-white p-1.5 rounded-xl hover:bg-white/5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <template x-if="diaryLoading">
                    <div class="flex items-center justify-center py-12">
                        <svg class="w-8 h-8 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </template>
                <template x-if="!diaryLoading && diary">
                    <div>
                        <div class="text-center mb-6">
                            <div class="text-3xl mb-2">📊</div>
                            <div class="text-lg font-black text-white">Balance: <span class="text-emerald-400" x-text="'Ksh ' + (diary.balance || 0).toLocaleString()"></span></div>
                            <div class="text-sm text-indigo-400 mt-1" x-text="diary.verdict"></div>
                        </div>
                        <div class="space-y-0 relative">
                            <template x-for="(step, i) in diary.narrative" :key="i">
                                <div class="diary-step pl-5 pb-6 relative">
                                    <div class="absolute left-0 top-1 w-2 h-2 rounded-full bg-indigo-500 border-2 border-[#0c0b16]" style="transform: translateX(-3px)"></div>
                                    <div class="text-xs text-gray-600 mb-1" x-text="'Step ' + step.step + ' · ' + new Date(step.at).toLocaleDateString('en-KE', {day:'numeric',month:'short'})"></div>
                                    <div class="text-sm font-semibold text-white" x-text="step.node_title"></div>
                                    <div class="text-xs text-indigo-300 mt-1" x-text="'→ You chose: ' + step.choice_label"></div>
                                    <div class="flex gap-3 mt-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full" :class="step.points > 0 ? 'bg-purple-500/15 text-purple-300' : 'bg-gray-500/10 text-gray-500'" x-text="'+' + step.points + ' XP'"></span>
                                        <span class="text-xs px-2 py-0.5 rounded-full" :class="step.balance_effect > 0 ? 'bg-emerald-500/15 text-emerald-400' : (step.balance_effect < 0 ? 'bg-red-500/15 text-red-400' : 'bg-gray-500/10 text-gray-500')" x-text="(step.balance_effect >= 0 ? '+' : '') + 'Ksh ' + step.balance_effect.toLocaleString()"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <template x-if="diary.narrative && diary.narrative.length === 0">
                            <div class="text-center py-8 text-gray-500 text-sm">No decisions yet — start playing!</div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- AlpineJS component --}}
    <script>
    function pesaquest() {
        return {
            choiceMade: false,
            selectedChoice: null,
            earnedPoints: 0,
            lessonText: '',
            showPointsPop: false,
            isLoading: false,
            investmentData: null,
            claimingInvest: null,
            claimingBonus: false,

            totalPoints: {{ $progress->points_total }},
            currentLevel: {{ $progress->level }},
            levelProgress: {{ $progress->level_progress_percent }},
            currentBalance: {{ $progress->balance }},

            showNotifications: false,
            notifications: [],
            unreadCount: 0,

            showChallenges: false,
            showDiary: false,
            diaryLoading: false,

            careerUnlocked: null,
            showCareerModal: false,
            diary: null,
            showAssessment: false,
            assessmentAnswers: {},
            assessmentStep: 0,
            assessmentResult: null,

            init() {
                this.fetchNotifications();
                setInterval(() => this.fetchNotifications(), 30000);

                window.addEventListener('keydown', (e) => {
                    if (this.choiceMade) return;
                    const num = parseInt(e.key);
                    if (num >= 1 && num <= 9) {
                        const buttons = document.querySelectorAll('.choice-btn:not([disabled])');
                        if (buttons[num - 1]) buttons[num - 1].click();
                    }
                });
            },

            makeChoice(choiceId, points) {
                if (this.choiceMade || this.isLoading) return;
                this.selectedChoice = choiceId;
                this.earnedPoints   = points;
                this.isLoading      = true;
                gameSounds.select();

                fetch('{{ route('game.choose') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ choice_id: choiceId })
                })
                .then(r => r.json())
                .then(data => {
                    this.isLoading      = false;
                    this.choiceMade     = true;
                    this.lessonText     = data.lesson || '';
                    this.investmentData = data.investment || null;
                    this.totalPoints    += data.points || 0;
                    this.currentBalance = data.newBalance ?? this.currentBalance;

                    if (points > 0) { this.showPointsPop = true; setTimeout(() => this.showPointsPop = false, 1800); gameSounds.win(); }
                    if (points >= 4) this.triggerConfetti();
                    if (data.newBalance > {{ $progress->balance }}) this.coinRain();
                    if (data.careerUnlocked) {
                        this.careerUnlocked = data.careerUnlocked;
                        setTimeout(() => {
                            this.showCareerModal = true;
                            this.earnedPoints = 0;
                            this.showPointsPop = false;
                            gameSounds.levelUp();
                            this.triggerConfetti();
                        }, 600);
                    }
                    if (data.showAssessment) {
                        setTimeout(() => { this.showAssessment = true; this.assessmentStep = 0; this.assessmentAnswers = {}; }, 1200);
                    }
                })
                .catch(() => { this.isLoading = false; this.choiceMade = true; });
            },

            continueGame() { this.isLoading = true; window.location.href = '{{ route('game.play') }}'; },

            completeAssessment(type) {
                this.assessmentAnswers.q4 = type;
                const result = this.computePersonality(this.assessmentAnswers);
                this.assessmentResult = result;
                fetch('{{ route('game.personality') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ personality: result.name }),
                }).catch(() => {});
            },

            computePersonality(answers) {
                const types = {
                    Saver:        { name: 'The Careful Saver',        icon: '🛡️', description: 'You value security and prefer to have money saved before you spend. Your discipline is a superpower in Kenya\'s unpredictable economy.', tip: 'Push savings into investments. M-Pesa savings lose value to inflation — let your money work harder in T-Bills or money market funds.' },
                    Investor:     { name: 'The Strategic Investor',   icon: '📈', description: 'You think long-term and want your money to multiply. You\'re building wealth systematically — exactly what Kenya\'s NSE, SACCOs, and T-Bills reward.', tip: 'Diversification is key. Spread across stocks, bonds, and real assets so one bad bet doesn\'t erase your gains.' },
                    RiskTaker:    { name: 'The Bold Risk-Taker',      icon: '🚀', description: 'You\'re not afraid to make big moves for big rewards. Entrepreneurship and high-growth investments call to you. Channel this energy wisely.', tip: 'Always have a fallback. Even Kenya\'s boldest entrepreneurs keep 3 months of expenses liquid.' },
                    Balanced:     { name: 'The Balanced Builder',     icon: '⚖️', description: 'You weigh options carefully then act decisively. You\'re neither reckless nor paralysed — a genuinely rare and valuable trait.', tip: 'Trust your research. When you\'ve done the analysis, act — indecision has an opportunity cost too.' },
                    Conservative: { name: 'The Conservative Thinker', icon: '🔒', description: 'You protect what you have and avoid unnecessary risk. This foundation is strong, but don\'t let caution keep you from growing.', tip: 'Start small with investments. A Ksh 500/month money market fund builds confidence without big risk.' },
                    Giver:        { name: 'The Community Builder',    icon: '🤝', description: 'You invest in relationships. In Kenya\'s Chama culture, this is a genuine strategy — collective wealth is real wealth.', tip: 'Formalize your generosity. A Chama with clear contribution rules protects both the group and your friendships.' },
                    Analyst:      { name: 'The Financial Analyst',    icon: '🔍', description: 'You research before you invest. You\'re rarely surprised by market moves because you\'ve already read the signals. Patience is your edge.', tip: 'Set a research deadline. Over-analysis costs opportunity — give yourself 48 hours max, then decide.' },
                    Spender:      { name: 'The Experience Seeker',    icon: '🌟', description: 'You value experiences over accumulation. Money is a tool for living. The key is balancing joy today with security tomorrow.', tip: 'Budget your fun. A "guilt-free" spending envelope lets you enjoy freely without derailing your goals.' },
                };
                const counts = {};
                Object.values(answers).forEach(v => { counts[v] = (counts[v] || 0) + 1; });
                let best = 'Balanced', bestCount = 0;
                for (const [t, c] of Object.entries(counts)) {
                    if (c > bestCount) { best = t; bestCount = c; }
                }
                return types[best] || types['Balanced'];
            },

            toggleNotifications() { this.showNotifications = !this.showNotifications; },

            fetchNotifications() {
                fetch('{{ route('game.notifications') }}', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                .then(r => r.json()).then(data => { this.notifications = data; this.unreadCount = data.length; }).catch(() => {});
            },

            markAllRead() {
                fetch('{{ route('game.notifications.read') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
                .then(() => { this.notifications = []; this.unreadCount = 0; }).catch(() => {});
            },

            claimInvestment(investId) {
                this.claimingInvest = investId;
                fetch(`/game/investments/${investId}/claim`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.claimingInvest = null;
                    if (data.returns !== undefined) {
                        this.currentBalance = data.newBalance;
                        gameSounds.invest();
                        this.coinRain();
                        this.triggerConfetti();
                        const banner = document.getElementById('invest-banner-' + investId);
                        if (banner) banner.remove();
                        this.fetchNotifications();
                    }
                })
                .catch(() => { this.claimingInvest = null; });
            },

            rateScenario(rating) {
                // Alpine data access via event from the nested component
            },

            replayScenario(nodeId) {
                fetch('{{ route('game.replay') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ node_id: nodeId })
                })
                .then(r => r.json())
                .then(data => { if (data.redirect) window.location.href = data.redirect; })
                .catch(() => {});
            },

            openDiary() {
                this.showDiary    = true;
                this.diaryLoading = true;
                this.diary        = null;

                fetch('{{ route('game.diary') }}', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                .then(r => r.json())
                .then(data => { this.diary = data; this.diaryLoading = false; })
                .catch(() => { this.diaryLoading = false; });
            },

            claimBonus() {
                this.claimingBonus = true;
                fetch('{{ route('game.claim-bonus') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
                .then(r => r.json()).then(data => { this.claimingBonus = false; if (!data.error) { this.currentBalance = data.newBalance; this.coinRain(); setTimeout(() => window.location.reload(), 1500); } })
                .catch(() => { this.claimingBonus = false; });
            },

            triggerConfetti() {
                const container = document.getElementById('confetti-container');
                const colors = ['#6366f1','#8b5cf6','#f59e0b','#10b981','#ec4899','#a78bfa','#fbbf24'];
                for (let i = 0; i < 60; i++) {
                    setTimeout(() => {
                        const el = document.createElement('div');
                        el.className = 'confetti-piece';
                        el.style.cssText = `left:${Math.random()*100}vw;width:${6+Math.random()*8}px;height:${6+Math.random()*8}px;background:${colors[Math.floor(Math.random()*colors.length)]};animation-duration:${1.5+Math.random()*2}s;animation-delay:0s;border-radius:${Math.random()>0.5?'50%':'2px'};`;
                        container.appendChild(el);
                        setTimeout(() => el.remove(), 3500);
                    }, i * 40);
                }
            },

            coinRain() {
                gameSounds.coins();
                const coins = ['💰','🪙','💵'];
                for (let i = 0; i < 12; i++) {
                    setTimeout(() => {
                        const el = document.createElement('div');
                        el.className = 'coin-anim';
                        el.textContent = coins[Math.floor(Math.random() * coins.length)];
                        el.style.cssText = `left:${20+Math.random()*60}%;top:${30+Math.random()*30}%;animation-delay:0s;`;
                        document.body.appendChild(el);
                        setTimeout(() => el.remove(), 1200);
                    }, i * 80);
                }
            },
        }
    }
    </script>

    {{-- ══ GAME SOUNDS ENGINE ══ --}}
    <script>
    const gameSounds = (() => {
        let _ctx = null;
        const ctx = () => {
            if (!_ctx) _ctx = new (window.AudioContext || window.webkitAudioContext)();
            if (_ctx.state === 'suspended') _ctx.resume();
            return _ctx;
        };
        const tone = (freq, dur, type = 'sine', vol = 0.07, delay = 0) => {
            try {
                const c = ctx(), o = c.createOscillator(), g = c.createGain();
                o.connect(g); g.connect(c.destination);
                o.type = type;
                o.frequency.setValueAtTime(freq, c.currentTime + delay);
                g.gain.setValueAtTime(0.001, c.currentTime + delay);
                g.gain.linearRampToValueAtTime(vol, c.currentTime + delay + 0.01);
                g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + delay + dur);
                o.start(c.currentTime + delay);
                o.stop(c.currentTime + delay + dur + 0.05);
            } catch(e) {}
        };
        return {
            select() {
                tone(660, 0.07, 'sine', 0.04);
            },
            win() {
                [523, 659, 784, 1047].forEach((f, i) => tone(f, 0.18, 'triangle', 0.065, i * 0.07));
            },
            coins() {
                [784, 1047, 1319, 1047, 1568].forEach((f, i) => tone(f, 0.14, 'sine', 0.06, i * 0.055));
            },
            invest() {
                // Fanfare: two-part ascending + resolution
                [523, 659, 784, 1047, 1319].forEach((f, i) => tone(f, 0.22, 'triangle', 0.075, i * 0.08));
                [1047, 1319, 1568].forEach((f, i) => tone(f, 0.3, 'sine', 0.05, 0.5 + i * 0.1));
            },
            levelUp() {
                [262, 330, 392, 523, 659, 784, 1047].forEach((f, i) => tone(f, 0.25, 'sine', 0.07, i * 0.065));
                setTimeout(() => tone(1047, 0.5, 'triangle', 0.09), 500);
            },
            bonus() {
                [440, 550, 660, 880].forEach((f, i) => tone(f, 0.2, 'sine', 0.065, i * 0.06));
            },
        };
    })();
    </script>

    {{-- Rating panel Alpine component --}}
    <script>
    function ratingPanel(nodeId, initialRating, initialUp, initialDown) {
        return {
            nodeId: nodeId,
            myRating: initialRating,
            up: initialUp,
            down: initialDown,
            submitting: false,
            rate(rating) {
                if (this.submitting) return;
                this.submitting = true;
                const prev = this.myRating;
                // Optimistic update
                if (prev === 1) this.up--;
                if (prev === -1) this.down--;
                this.myRating = (prev === rating) ? null : rating;
                if (this.myRating === 1) this.up++;
                if (this.myRating === -1) this.down++;

                fetch('{{ route('game.rate') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ node_id: nodeId, rating: (prev === rating ? 1 : rating) })
                })
                .then(r => r.json())
                .then(data => { this.submitting = false; if (data.summary) { this.up = data.summary.up; this.down = data.summary.down; } })
                .catch(() => { this.submitting = false; this.myRating = prev; });
            }
        }
    }
    </script>

    {{-- ── Financial Personality Assessment Modal ── --}}
    <div x-show="showAssessment" x-cloak
         style="position:fixed;inset:0;z-index:9500;background:rgba(0,0,0,0.88);overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;"
         class="flex items-center justify-center p-4">
        <div style="max-width:460px;width:100%;margin:auto;background:linear-gradient(145deg,#1a1830,#12112a);border:1px solid rgba(139,92,246,0.35);border-radius:1.5rem;padding:2rem;"
             @click.stop>

            <template x-if="!assessmentResult">
                <div>
                    <div style="text-align:center;margin-bottom:1.5rem;">
                        <div style="font-size:3rem;">🧠</div>
                        <div style="font-weight:900;font-size:1.1rem;color:white;margin-top:0.25rem;">Money Mind Check</div>
                        <div style="font-size:0.78rem;color:#9ca3af;margin-top:0.25rem;">Every 10 decisions we check how you think about money</div>
                    </div>

                    {{-- Q1 --}}
                    <template x-if="assessmentStep === 0">
                        <div>
                            <p style="font-size:0.85rem;font-weight:700;color:white;margin-bottom:1rem;">1. You have extra Ksh 5,000 at month end. You…</p>
                            @foreach([['a','Spend it on something fun','Spender'],['b','Save it in M-Pesa Savings','Saver'],['c','Invest in money market funds','Investor'],['d','Share with family who need it','Giver']] as [$key,$label,$type])
                            <button @click="assessmentAnswers.q1 = '{{ $type }}'; assessmentStep = 1"
                                    style="display:block;width:100%;text-align:left;padding:0.75rem 1rem;margin-bottom:0.5rem;border-radius:0.875rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e2e8f0;font-size:0.82rem;cursor:pointer;transition:all 0.15s;"
                                    onmouseover="this.style.borderColor='rgba(139,92,246,0.4)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </template>

                    {{-- Q2 --}}
                    <template x-if="assessmentStep === 1">
                        <div>
                            <p style="font-size:0.85rem;font-weight:700;color:white;margin-bottom:1rem;">2. An investment promises 30% returns in 2 months. You…</p>
                            @foreach([['a','Invest most of your savings — big reward!','RiskTaker'],['b','Invest a small test amount only','Balanced'],['c','Research it thoroughly before deciding','Analyst'],['d','Avoid it — sounds too good to be true','Conservative']] as [$key,$label,$type])
                            <button @click="assessmentAnswers.q2 = '{{ $type }}'; assessmentStep = 2"
                                    style="display:block;width:100%;text-align:left;padding:0.75rem 1rem;margin-bottom:0.5rem;border-radius:0.875rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e2e8f0;font-size:0.82rem;cursor:pointer;transition:all 0.15s;"
                                    onmouseover="this.style.borderColor='rgba(139,92,246,0.4)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </template>

                    {{-- Q3 --}}
                    <template x-if="assessmentStep === 2">
                        <div>
                            <p style="font-size:0.85rem;font-weight:700;color:white;margin-bottom:1rem;">3. Your #1 financial goal is…</p>
                            @foreach([['a','Owning my own home or land','Saver'],['b','Building passive income streams','Investor'],['c','Freedom to live and travel freely','Spender'],['d','Owning a successful business','RiskTaker']] as [$key,$label,$type])
                            <button @click="assessmentAnswers.q3 = '{{ $type }}'; assessmentStep = 3"
                                    style="display:block;width:100%;text-align:left;padding:0.75rem 1rem;margin-bottom:0.5rem;border-radius:0.875rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e2e8f0;font-size:0.82rem;cursor:pointer;transition:all 0.15s;"
                                    onmouseover="this.style.borderColor='rgba(139,92,246,0.4)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </template>

                    {{-- Q4 --}}
                    <template x-if="assessmentStep === 3">
                        <div>
                            <p style="font-size:0.85rem;font-weight:700;color:white;margin-bottom:1rem;">4. An unexpected Ksh 10,000 expense hits. You…</p>
                            @foreach([['a',"Use my emergency fund — that's what it's for",'Saver'],['b','Pay with Fuliza or a quick loan','RiskTaker'],['c','Ask family to chip in temporarily','Giver'],['d','Cut other spending until it\'s covered','Conservative']] as [$key,$label,$type])
                            <button @click="completeAssessment('{{ $type }}')"
                                    style="display:block;width:100%;text-align:left;padding:0.75rem 1rem;margin-bottom:0.5rem;border-radius:0.875rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e2e8f0;font-size:0.82rem;cursor:pointer;transition:all 0.15s;"
                                    onmouseover="this.style.borderColor='rgba(139,92,246,0.4)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </template>
                </div>
            </template>

            {{-- Result screen --}}
            <template x-if="assessmentResult">
                <div style="text-align:center;">
                    <div style="font-size:4rem;margin-bottom:0.5rem;" x-text="assessmentResult.icon"></div>
                    <div style="font-size:0.75rem;font-weight:700;color:#a78bfa;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.25rem;">Your Money Mind</div>
                    <div style="font-size:1.5rem;font-weight:900;color:white;margin-bottom:0.75rem;" x-text="assessmentResult.name"></div>
                    <p style="font-size:0.82rem;color:#9ca3af;line-height:1.6;margin-bottom:1.5rem;" x-text="assessmentResult.description"></p>
                    <div style="padding:0.75rem 1rem;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);border-radius:0.875rem;font-size:0.78rem;color:#a5b4fc;margin-bottom:1.5rem;" x-text="'💡 ' + assessmentResult.tip"></div>
                    <button @click="showAssessment = false; assessmentResult = null"
                            style="width:100%;padding:0.875rem;border-radius:1rem;background:linear-gradient(135deg,#6366f1,#a78bfa);color:white;font-weight:900;border:none;cursor:pointer;font-size:0.95rem;">
                        Keep Playing!
                    </button>
                </div>
            </template>
        </div>
    </div>


@include('components.mama-pesa-chat')
</body>
</html>
