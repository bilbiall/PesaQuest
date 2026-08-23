<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daily Spin – PesaQuest</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { background: #07060f; font-family: 'Figtree', sans-serif; overflow-x: hidden; }
        [x-cloak] { display: none !important; }

        .stars-bg {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99,102,241,0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(139,92,246,0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(245,158,11,0.06) 0%, transparent 70%),
                #07060f;
        }
        .star { position:absolute; border-radius:50%; background:white; animation:twinkle linear infinite; }
        @keyframes twinkle {
            0%,100% { opacity:0.1; transform:scale(1); }
            50%      { opacity:0.8; transform:scale(1.4); }
        }

        /* ── outer decorative frame ── */
        .wheel-frame {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                #7c5c00 0deg, #f5d060 30deg, #b8860b 60deg, #f5d060 90deg,
                #7c5c00 120deg, #f5d060 150deg, #b8860b 180deg, #f5d060 210deg,
                #7c5c00 240deg, #f5d060 270deg, #b8860b 300deg, #f5d060 330deg,
                #7c5c00 360deg
            );
            animation: frame-glow 3s ease-in-out infinite;
        }
        @keyframes frame-glow {
            0%,100% { box-shadow: 0 0 40px rgba(245,200,80,0.35), 0 0 80px rgba(245,200,80,0.15); }
            50%      { box-shadow: 0 0 65px rgba(245,200,80,0.6),  0 0 120px rgba(245,200,80,0.25); }
        }
        .wheel-frame canvas { border-radius:50%; display:block; }

        /* ── spin button ── */
        .spin-btn {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none; border-radius: 9999px;
            color: #07060f; font-family: 'Figtree', sans-serif;
            font-size: 1.1rem; font-weight: 900; padding: 1rem 3rem;
            cursor: pointer; letter-spacing: .04em; text-transform: uppercase;
            box-shadow: 0 0 30px rgba(245,158,11,.5), 0 8px 24px rgba(0,0,0,.4);
            transition: all .2s;
        }
        .spin-btn:hover:not(:disabled) { transform:scale(1.05); box-shadow:0 0 50px rgba(245,158,11,.7),0 12px 32px rgba(0,0,0,.4); }
        .spin-btn:disabled { background:linear-gradient(135deg,#374151,#1f2937); color:#6b7280; cursor:not-allowed; box-shadow:none; }
        .spin-btn.spinning { animation:btn-pulse .5s ease-in-out infinite alternate; }
        @keyframes btn-pulse {
            from { box-shadow: 0 0 20px rgba(245,158,11,.4); }
            to   { box-shadow: 0 0 60px rgba(245,158,11,.9); }
        }

        /* ── prize overlay modal ── */
        .prize-overlay {
            position:fixed; inset:0; z-index:9000; display:flex; align-items:center; justify-content:center;
            background: rgba(7,6,15,.88); backdrop-filter:blur(18px);
            animation:overlay-in .3s ease forwards;
        }
        @keyframes overlay-in {
            from { opacity:0; }
            to   { opacity:1; }
        }
        .prize-card {
            position:relative; max-width:420px; width:90vw; padding:2.5rem 2rem;
            border-radius:2rem; text-align:center;
            animation:card-pop .55s cubic-bezier(.175,.885,.32,1.275) forwards;
            overflow:hidden;
        }
        @keyframes card-pop {
            0%   { transform:scale(.55) translateY(40px); opacity:0; }
            60%  { transform:scale(1.04) translateY(-4px); opacity:1; }
            100% { transform:scale(1)  translateY(0);     opacity:1; }
        }
        .prize-emoji-wrap {
            font-size:5rem; line-height:1; margin-bottom:.75rem;
            animation:emoji-bounce 1.2s ease infinite;
        }
        @keyframes emoji-bounce {
            0%,100% { transform:translateY(0) rotate(-4deg) scale(1); }
            50%     { transform:translateY(-10px) rotate(4deg) scale(1.08); }
        }
        .prize-delta-badge {
            display:inline-flex; align-items:center; gap:.5rem;
            padding:.5rem 1.4rem; border-radius:9999px;
            font-size:1.3rem; font-weight:900; margin:.75rem 0;
            animation:delta-in .5s .35s cubic-bezier(.175,.885,.32,1.275) both;
        }
        @keyframes delta-in {
            from { transform:scale(0) rotate(-15deg); opacity:0; }
            to   { transform:scale(1) rotate(0deg);   opacity:1; }
        }
        .prize-rays {
            position:absolute; inset:0; pointer-events:none; overflow:hidden;
        }
        .prize-rays::before {
            content:''; position:absolute; top:50%; left:50%;
            width:120%; height:120%; transform:translate(-50%,-50%);
            background:conic-gradient(transparent 0deg,rgba(255,255,255,.025) 15deg,transparent 30deg,rgba(255,255,255,.025) 45deg,transparent 60deg,rgba(255,255,255,.025) 75deg,transparent 90deg,rgba(255,255,255,.025) 105deg,transparent 120deg,rgba(255,255,255,.025) 135deg,transparent 150deg,rgba(255,255,255,.025) 165deg,transparent 180deg,rgba(255,255,255,.025) 195deg,transparent 210deg,rgba(255,255,255,.025) 225deg,transparent 240deg,rgba(255,255,255,.025) 255deg,transparent 270deg,rgba(255,255,255,.025) 285deg,transparent 300deg,rgba(255,255,255,.025) 315deg,transparent 330deg,rgba(255,255,255,.025) 345deg,transparent 360deg);
            animation:rays-spin 12s linear infinite;
        }
        @keyframes rays-spin { to { transform:translate(-50%,-50%) rotate(360deg); } }

        /* ── confetti ── */
        .confetti-piece {
            position:fixed; border-radius:2px; pointer-events:none; z-index:9999;
            animation:confetti-drop linear forwards;
        }
        @keyframes confetti-drop {
            0%   { transform:translateY(-20px) rotate(0deg);    opacity:1; }
            100% { transform:translateY(100vh) rotate(720deg);  opacity:0; }
        }

        .glass-card {
            background: linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.02));
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 1.5rem;
        }
        .prize-list-item {
            display:flex; align-items:center; gap:.75rem;
            padding:.5rem .75rem; border-radius:.75rem; transition:background .15s;
        }
        .prize-list-item:hover { background:rgba(255,255,255,.04); }
    </style>
</head>
<body x-data="spinApp()" @keydown.window="handleKey">

<div class="stars-bg" id="stars"></div>

{{-- Nav --}}
<nav class="sticky top-0 z-40 border-b border-white/5" style="background:rgba(7,6,15,.85);backdrop-filter:blur(20px);">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <span class="text-xl">🎰</span>
            <span class="font-black text-white">Daily Spin</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-1.5 rounded-xl text-xs font-bold" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:#6ee7b7;">
                💰 Ksh {{ number_format($progress->balance ?? 0) }}
            </div>
            @if(!$canSpin)
            <div class="px-3 py-1.5 rounded-xl text-xs font-bold" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5;">
                🔒 Spun Today
            </div>
            @endif
        </div>
    </div>
</nav>

<div class="relative z-10 max-w-5xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8 items-start">

        {{-- ══ LEFT ══ --}}
        <div class="flex-1 flex flex-col items-center gap-6">

            <div class="text-center">
                <h1 class="text-xl sm:text-2xl font-black text-white leading-tight">
                    @if($canSpin) Spin to Win 🎰 @else Come Back Tomorrow @endif
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    @if($canSpin)
                        One free spin every 24 hours — the flapper decides your fate.
                    @else
                        Your next spin resets at midnight.
                        @if($lastSpin) Last prize: <span class="text-amber-400 font-bold">{{ $lastSpin->prize_emoji }} {{ $lastSpin->prize_label }}</span>@endif
                    @endif
                </p>
            </div>

            {{-- Wheel --}}
            <div class="wheel-frame">
                <canvas id="spinWheel"
                    class="cursor-pointer"
                    @click="canSpin && !spinning && startSpin()">
                </canvas>
            </div>

            {{-- Spin Button --}}
            <div class="flex flex-col items-center gap-3 w-full max-w-xs">
                <button class="spin-btn w-full"
                    :class="{'spinning':spinning}"
                    @click="startSpin()"
                    :disabled="!canSpin || spinning || revealed">
                    <span x-show="!spinning && !revealed">🎰 Spin Now</span>
                    <span x-show="spinning">Spinning…</span>
                    <span x-show="revealed && !spinning">✓ Done for Today</span>
                </button>
                @if(!$canSpin)
                <p class="text-xs text-gray-500 text-center">Reset at midnight · Come back for tomorrow's spin</p>
                @else
                <p class="text-xs text-gray-500 text-center">Click the wheel or press the button</p>
                @endif
            </div>

            {{-- spacer so wheel button area doesn't look empty after spin --}}
            <div x-show="revealed" x-cloak class="h-20"></div>
        </div>

        {{-- ══ RIGHT SIDEBAR ══ --}}
        <div class="w-full lg:w-80 flex-shrink-0 space-y-5">

            <div class="glass-card p-4">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Possible Prizes</h3>
                <div class="space-y-0.5">
                    @foreach($segments as $seg)
                    <div class="prize-list-item">
                        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $seg['color'] }}"></span>
                        <span class="text-sm text-white flex-1">{{ $seg['emoji'] }} {{ $seg['label'] }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0
                            {{ $seg['tier']==='great' ? 'bg-amber-500/15 text-amber-400' : ($seg['tier']==='bad' ? 'bg-red-500/15 text-red-400' : 'bg-indigo-500/15 text-indigo-300') }}">
                            {{ $seg['tier']==='great' ? 'Rare' : ($seg['tier']==='bad' ? 'Risky' : 'Common') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                <p class="text-[10px] text-gray-600 mt-3 leading-relaxed">Rare prizes have lower probability. Bad outcomes simulate real financial risk.</p>
            </div>

            @if($history->isNotEmpty())
            <div class="glass-card p-4">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Recent Spins</h3>
                <div class="space-y-2">
                    @foreach($history as $h)
                    <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
                        <span class="text-xl flex-shrink-0">{{ $h->prize_emoji }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-300">{{ $h->prize_label }}</p>
                            <p class="text-[10px] text-gray-600">{{ $h->created_at->diffForHumans() }}</p>
                        </div>
                        @if($h->prize_type === 'balance')
                        <span class="text-xs font-black flex-shrink-0 {{ $h->prize_value > 0 ? 'text-emerald-500' : 'text-red-500' }}">
                            {{ $h->prize_value > 0 ? '+' : '' }}Ksh {{ number_format(abs($h->prize_value)) }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="glass-card p-4">
                <div class="flex gap-3">
                    <span class="text-2xl">🎲</span>
                    <div>
                        <p class="text-xs font-black text-amber-400 uppercase tracking-wider mb-1">Why Random Events?</p>
                        <p class="text-xs text-gray-400 leading-relaxed">Real life has unexpected wins and losses — a bonus, a fine, a windfall. This wheel simulates financial volatility. Even bad outcomes teach you to build buffers for when life surprises you.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══ PRIZE REVEAL OVERLAY MODAL ══ --}}
<div x-show="revealed" x-cloak class="prize-overlay" @click.self="goBack()" style="display:none">
    <div class="prize-card"
         :style="prize.tier==='great'
             ? 'background:linear-gradient(160deg,#1a1208,#0d0b06);border:2px solid rgba(245,158,11,.55);box-shadow:0 0 80px rgba(245,158,11,.25),0 30px 80px rgba(0,0,0,.6)'
             : prize.tier==='bad'
                 ? 'background:linear-gradient(160deg,#180a0a,#0d0606);border:2px solid rgba(239,68,68,.45);box-shadow:0 0 60px rgba(239,68,68,.2),0 30px 80px rgba(0,0,0,.6)'
                 : 'background:linear-gradient(160deg,#0d0e1c,#080912);border:2px solid rgba(99,102,241,.45);box-shadow:0 0 60px rgba(99,102,241,.18),0 30px 80px rgba(0,0,0,.6)'">

        <div class="prize-rays"></div>

        {{-- Tier badge --}}
        <div class="mb-4">
            <span class="text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-[.15em]"
                  :style="prize.tier==='great'
                      ? 'background:rgba(245,158,11,.18);color:#fbbf24;border:1px solid rgba(245,158,11,.45)'
                      : prize.tier==='bad'
                          ? 'background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.4)'
                          : 'background:rgba(99,102,241,.15);color:#a5b4fc;border:1px solid rgba(99,102,241,.4)'"
                  x-text="prize.tier==='great' ? '🌟  J A C K P O T' : prize.tier==='bad' ? '💸  O U C H' : '🎉  Y O U   W O N'">
            </span>
        </div>

        {{-- Big emoji --}}
        <div class="prize-emoji-wrap" x-text="prize.emoji"></div>

        {{-- Prize name --}}
        <h2 class="text-2xl font-black text-white mb-1" x-text="prize.label"></h2>
        <p class="text-sm mb-4" :class="prize.tier==='bad' ? 'text-red-300' : 'text-gray-300'" x-text="prizeDescription()"></p>

        {{-- Delta badge --}}
        <div x-show="balanceDelta !== 0">
            <div class="prize-delta-badge"
                 :style="balanceDelta>0
                     ? 'background:rgba(16,185,129,.14);border:1.5px solid rgba(16,185,129,.4);color:#34d399'
                     : 'background:rgba(239,68,68,.12);border:1.5px solid rgba(239,68,68,.4);color:#fca5a5'"
                 x-text="balanceDelta>0 ? '↑ +Ksh ' + Math.abs(balanceDelta).toLocaleString() : '↓ −Ksh ' + Math.abs(balanceDelta).toLocaleString()">
            </div>
        </div>

        {{-- Financial tip --}}
        <p class="text-xs text-gray-500 mb-6 leading-relaxed px-2"
           x-text="prize.tier==='bad'
               ? 'Real life has unexpected costs. Emergency funds exist for moments like this.'
               : prize.tier==='great'
                   ? 'A windfall is best put to work — invest, save, or clear debt before spending.'
                   : 'Every win, big or small, is a step toward your financial goals.'">
        </p>

        {{-- Actions — navigation is driven explicitly via JS (not a bare native
             anchor click) because on mobile PWA installs, taps inside this
             Alpine-toggled overlay were unreliably reaching the browser's
             default anchor handling and re-rendering the same page instead
             of navigating. window.location.href is deterministic regardless. --}}
        <div class="flex gap-3">
            <a href="{{ route('world') }}" @click.prevent="goCity()"
               class="flex-1 py-3 rounded-xl font-bold text-sm text-white text-center transition-all hover:opacity-90"
               style="background:linear-gradient(135deg,#15C77E,#059669);">
                🗺 Back to City
            </a>
            <a href="{{ route('dashboard') }}" @click.prevent="goBack()"
               class="flex-1 py-3 rounded-xl font-bold text-sm text-white text-center transition-all hover:opacity-90"
               style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
                Dashboard →
            </a>
        </div>

        <p class="text-[10px] text-gray-700 mt-4">Tap outside to dismiss</p>
    </div>
</div>

<script>
const SEGMENTS = @json($segments);

// ─── Alpine app ────────────────────────────────────────────────────────────
function spinApp() {
    return {
        canSpin:      {{ $canSpin ? 'true' : 'false' }},
        spinning:     false,
        revealed:     false,
        prize:        {},
        balanceDelta: 0,
        wheel:        null,

        init() {
            this.wheel = new CasinoWheel(document.getElementById('spinWheel'), SEGMENTS);
            this.wheel.draw();
            this.spawnStars();
        },

        async startSpin() {
            if (!this.canSpin || this.spinning || this.revealed) return;
            this.spinning = true;
            try {
                const res = await fetch('{{ route('spin.do') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'X-CSRF-TOKEN':  document.querySelector('meta[name=csrf-token]').content,
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                if (!data.success) {
                    alert(data.message ?? 'Could not spin.');
                    this.spinning = false;
                    return;
                }
                this.prize        = data.prize;
                this.balanceDelta = data.balance_delta ?? 0;

                await this.wheel.spinTo(data.segment_index);

                this.spinning = false;
                this.revealed = true;
                this.canSpin  = false;

                if (this.prize.tier === 'great') { this.spawnConfetti(120); this.playJackpotSound(); }
                else if (this.prize.tier === 'bad') { this.playLoseSound(); }
                else { this.spawnConfetti(40); this.playWinSound(); }

            } catch (e) {
                console.error(e);
                this.spinning = false;
            }
        },

        prizeDescription() {
            const p = this.prize;
            if (!p) return '';
            if (p.type === 'balance'   && p.value > 0) return `Ksh ${p.value.toLocaleString()} added to your balance.`;
            if (p.type === 'balance'   && p.value < 0) return `Ksh ${Math.abs(p.value).toLocaleString()} deducted — unexpected expense!`;
            if (p.type === 'credit'    && p.value > 0) return `Credit score improved by ${p.value} points.`;
            if (p.type === 'credit'    && p.value < 0) return `Credit score dropped by ${Math.abs(p.value)} points.`;
            if (p.type === 'xp')                       return `You earned ${p.value.toLocaleString()} XP!`;
            if (p.type === 'salary_2x')                return `Your next salary will be doubled!`;
            if (p.type === 'badge')                    return `A special badge was added to your profile!`;
            return '';
        },

        goBack() { window.location.href = '{{ route('dashboard') }}'; },
        goCity() { window.location.href = '{{ route('world') }}'; },

        handleKey(e) {
            if (e.key === 'Escape' && this.revealed) { this.goBack(); return; }
            if ((e.key === 'Enter' || e.key === ' ') && !this.revealed) this.startSpin();
        },

        spawnStars() {
            const c = document.getElementById('stars');
            for (let i = 0; i < 80; i++) {
                const s  = document.createElement('div');
                s.className = 'star';
                const sz = Math.random() * 2.5 + 0.5;
                s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;animation-duration:${2+Math.random()*4}s;animation-delay:${Math.random()*4}s;`;
                c.appendChild(s);
            }
        },

        spawnConfetti(count) {
            const colors = ['#6366f1','#ec4899','#f59e0b','#10b981','#8b5cf6','#ef4444','#0ea5e9'];
            for (let i = 0; i < count; i++) {
                const el = document.createElement('div');
                el.className = 'confetti-piece';
                el.style.cssText = `left:${Math.random()*100}vw;top:-10px;background:${colors[~~(Math.random()*colors.length)]};width:${6+Math.random()*8}px;height:${6+Math.random()*8}px;border-radius:${Math.random()>.5?'50%':'2px'};animation-duration:${2+Math.random()*2}s;animation-delay:${Math.random()*0.8}s;`;
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 4000);
            }
        },

        _bell(ctx, freq, startAt, dur, gain) {
            // Marimba-style: triangle + 2nd harmonic envelope
            [[1, 'triangle', 1], [2.756, 'sine', 0.32]].forEach(([ratio, type, mix]) => {
                const g = ctx.createGain();
                g.gain.setValueAtTime(gain * mix, startAt);
                g.gain.exponentialRampToValueAtTime(0.001, startAt + dur);
                g.connect(ctx.destination);
                const o = ctx.createOscillator(); o.type = type;
                o.frequency.value = freq * ratio;
                o.connect(g); o.start(startAt); o.stop(startAt + dur + .05);
            });
        },

        playWinSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const t = ctx.currentTime;
                // Bright pentatonic run then sustained chord
                [523,659,784,1047,1319].forEach((f,i) => this._bell(ctx, f, t+i*.09, 0.75, 0.14));
                setTimeout(() => {
                    const t2 = ctx.currentTime;
                    [523,659,784,1047].forEach(f => this._bell(ctx, f, t2, 1.5, 0.09));
                }, 520);
            } catch(e) {}
        },

        playJackpotSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const t = ctx.currentTime;
                // Full fanfare: ascending run + chord + shimmer
                [262,330,392,494,523,659,784,1047].forEach((f,i) => this._bell(ctx, f, t+i*.07, 0.8, 0.13));
                setTimeout(() => {
                    const t2 = ctx.currentTime;
                    [523,659,784,1047,1319].forEach(f => this._bell(ctx, f, t2, 2.0, 0.10));
                }, 620);
            } catch(e) {}
        },

        playLoseSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const t = ctx.currentTime;
                // Descending minor melody — sad but musical
                [[392,0],[349,0.18],[294,0.38],[220,0.6]].forEach(([f,d]) => {
                    this._bell(ctx, f, t+d, 0.6, 0.11);
                });
            } catch(e) {}
        },
    };
}

// ─── Casino Wheel ──────────────────────────────────────────────────────────
class CasinoWheel {
    constructor(canvas, segments) {
        this.canvas   = canvas;
        this.ctx      = canvas.getContext('2d');
        this.segments = segments;
        this.n        = segments.length;
        this.arc      = (2 * Math.PI) / segments.length;
        this.rotation = 0;

        // Flapper spring state
        this.flapAngle    = 0;   // radians deflection (+ = clockwise)
        this.flapVelocity = 0;   // rad/s
        this.lastPeg      = -1;

        this._audioCtx = null;
        this.dpr       = window.devicePixelRatio || 1;
        this._resize();
    }

    // Full DB labels ("Ksh 1,500 Fine", "+30 Credit", "2× Next Salary") are too
    // long to fit a 28-segment wheel without truncating into unreadable
    // fragments. The emoji + segment color already signal the prize TYPE
    // (📈/🌟/🚀 = credit gain, 📉 = credit loss, ⭐/✨/🌠/💫 = XP, 😬/😩/💀 = fine),
    // so on-wheel text only needs the AMOUNT — shortened, never truncated.
    static shortLabel(label) {
        const compact = (numStr) => {
            const n = parseInt(numStr.replace(/,/g, ''), 10);
            if (n >= 1000) { const v = n / 1000; return (v % 1 === 0 ? v : v.toFixed(1)) + 'K'; }
            return String(n);
        };
        let m;
        if ((m = label.match(/^Ksh\s([\d,]+)(?:\s(?:Fine|Jackpot))?$/))) return compact(m[1]);
        if ((m = label.match(/^([+-]\d+)\sCredit(?:\sSurge)?$/))) return m[1];
        if ((m = label.match(/^([\d,]+)\sXP(?:\sBoost)?$/))) return compact(m[1]) + ' XP';
        if (label === '2× Next Salary') return '2× Salary';
        if (label === 'Lucky Badge') return 'Badge';
        return label.length > 10 ? label.slice(0, 9) + '…' : label;
    }

    _resize() {
        // Canvas is slightly taller so flapper pivot clears the top edge
        const w  = Math.min(window.innerWidth - 56, 420);
        const h  = w + 28;  // 28px extra at top for the flapper
        this.canvas.style.width  = w + 'px';
        this.canvas.style.height = h + 'px';
        this.canvas.width  = w * this.dpr;
        this.canvas.height = h * this.dpr;
        this.ctx.scale(this.dpr, this.dpr);
        this.W  = w;
        this.H  = h;
        // Wheel centre is shifted down so flapper has room
        this.cx = w / 2;
        this.cy = h / 2 + 10;
        // Radii
        this.outerR = w / 2 - 10;
        this.rimW   = 16; // slimmer gold rim so more of the wheel is visible segments
        this.innerR = this.outerR - this.rimW;
        this.pegR   = this.outerR - this.rimW / 2;
        this.hubR   = 36;
    }

    draw() { this._render(this.rotation, this.flapAngle); }

    // ── Main render ─────────────────────────────────────────────────────────
    _render(rotation, flapAngle) {
        const ctx = this.ctx;
        const { W, H, cx, cy, outerR, rimW, innerR, pegR, hubR, n, arc } = this;

        ctx.clearRect(0, 0, W, H);

        // 1 · Ambient outer glow
        const glow = ctx.createRadialGradient(cx, cy, innerR * .6, cx, cy, outerR * 1.15);
        glow.addColorStop(0,   'rgba(245,200,80,0)');
        glow.addColorStop(.75, 'rgba(245,200,80,.06)');
        glow.addColorStop(1,   'rgba(99,102,241,.08)');
        ctx.beginPath(); ctx.arc(cx, cy, outerR * 1.15, 0, Math.PI*2);
        ctx.fillStyle = glow; ctx.fill();

        // 2 · Segments
        for (let i = 0; i < n; i++) {
            const sa = i * arc + rotation - Math.PI / 2;
            const ea = sa + arc;

            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, innerR, sa, ea);
            ctx.closePath();
            ctx.fillStyle = this.segments[i].color;
            ctx.fill();

            // Radial shading
            const rg = ctx.createRadialGradient(cx, cy, hubR, cx, cy, innerR);
            rg.addColorStop(0,   'rgba(0,0,0,.28)');
            rg.addColorStop(.6,  'rgba(0,0,0,0)');
            rg.addColorStop(1,   'rgba(255,255,255,.16)');
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, innerR, sa, ea);
            ctx.closePath();
            ctx.fillStyle = rg; ctx.fill();

            // Separator line
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, innerR, sa, ea);
            ctx.closePath();
            ctx.strokeStyle = 'rgba(0,0,0,.4)'; ctx.lineWidth = 1.5; ctx.stroke();
        }

        // 3 · Spokes
        for (let i = 0; i < n; i++) {
            const a = i * arc + rotation - Math.PI / 2;
            const cos = Math.cos(a), sin = Math.sin(a);
            ctx.beginPath();
            ctx.moveTo(cx + hubR*cos, cy + hubR*sin);
            ctx.lineTo(cx + innerR*cos, cy + innerR*sin);
            ctx.strokeStyle = 'rgba(0,0,0,.5)'; ctx.lineWidth = 3; ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(cx + hubR*cos, cy + hubR*sin);
            ctx.lineTo(cx + innerR*cos, cy + innerR*sin);
            ctx.strokeStyle = 'rgba(255,255,255,.07)'; ctx.lineWidth = 1; ctx.stroke();
        }

        // 4 · Labels — emoji signals prize TYPE, short text carries the AMOUNT.
        // Font auto-shrinks per label so long ones (e.g. "2× Salary") never
        // spill into a neighbouring segment, no matter how many segments exist.
        for (let i = 0; i < n; i++) {
            const mid  = i * arc + rotation - Math.PI / 2 + arc / 2;
            const dist = hubR + (innerR - hubR) * .68;
            ctx.save();
            ctx.translate(cx, cy); ctx.rotate(mid);
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.shadowColor = 'rgba(0,0,0,.9)'; ctx.shadowBlur = 5;
            ctx.font = `${Math.floor(W/20)}px Segoe UI Emoji, Apple Color Emoji, sans-serif`;
            ctx.fillStyle = 'white';
            ctx.fillText(this.segments[i].emoji, dist, -9);

            const lbl = CasinoWheel.shortLabel(this.segments[i].label);
            const availWidth = 2 * dist * Math.sin(arc / 2) * 0.92;
            let fontSize = Math.floor(W / 22);
            ctx.font = `bold ${fontSize}px Figtree, sans-serif`;
            while (fontSize > 7 && ctx.measureText(lbl).width > availWidth) {
                fontSize -= 1;
                ctx.font = `bold ${fontSize}px Figtree, sans-serif`;
            }
            ctx.fillStyle = 'rgba(255,255,255,.95)'; ctx.shadowBlur = 3;
            ctx.fillText(lbl, dist, 10);
            ctx.restore();
        }

        // 5 · Metallic rim (annulus over segments)
        // Dark metal base
        ctx.beginPath();
        ctx.arc(cx, cy, outerR,     0, Math.PI*2, false);
        ctx.arc(cx, cy, innerR,     0, Math.PI*2, true);
        ctx.fillStyle = '#0e0d1c';
        ctx.fill('evenodd');

        // Gold outer bevel
        const gold = ctx.createLinearGradient(cx - outerR, cy, cx + outerR, cy);
        gold.addColorStop(0,    'rgba(140,105,35,.88)');
        gold.addColorStop(.25,  'rgba(245,200,80,.92)');
        gold.addColorStop(.5,   'rgba(190,150,55,.88)');
        gold.addColorStop(.75,  'rgba(245,200,80,.92)');
        gold.addColorStop(1,    'rgba(140,105,35,.88)');
        ctx.beginPath();
        ctx.arc(cx, cy, outerR,   0, Math.PI*2, false);
        ctx.arc(cx, cy, outerR-6, 0, Math.PI*2, true);
        ctx.fillStyle = gold; ctx.fill('evenodd');

        // Inner rim highlight strip
        ctx.beginPath();
        ctx.arc(cx, cy, innerR+3, 0, Math.PI*2, false);
        ctx.arc(cx, cy, innerR,   0, Math.PI*2, true);
        ctx.fillStyle = 'rgba(255,255,255,.11)'; ctx.fill('evenodd');

        // Inner rim shadow
        ctx.beginPath();
        ctx.arc(cx, cy, outerR-6,   0, Math.PI*2, false);
        ctx.arc(cx, cy, innerR+3,   0, Math.PI*2, true);
        ctx.fillStyle = 'rgba(0,0,0,.25)'; ctx.fill('evenodd');

        // 6 · Pegs (rotate with wheel)
        for (let i = 0; i < n; i++) {
            const a  = i * arc + rotation - Math.PI / 2;
            const px = cx + pegR * Math.cos(a);
            const py = cy + pegR * Math.sin(a);

            // Shadow
            ctx.beginPath(); ctx.arc(px, py, 5.5, 0, Math.PI*2);
            ctx.fillStyle = 'rgba(0,0,0,.55)'; ctx.fill();

            // Metallic gold peg
            const pg = ctx.createRadialGradient(px-1.5, py-1.5, 0, px, py, 5);
            pg.addColorStop(0,   '#fff9cc');
            pg.addColorStop(.4,  '#f5d060');
            pg.addColorStop(1,   '#6b4c00');
            ctx.beginPath(); ctx.arc(px, py, 4.5, 0, Math.PI*2);
            ctx.fillStyle = pg; ctx.fill();

            // Specular dot
            ctx.beginPath(); ctx.arc(px-1.2, py-1.2, 1.5, 0, Math.PI*2);
            ctx.fillStyle = 'rgba(255,250,200,.7)'; ctx.fill();
        }

        // 7 · Hub
        const hs = ctx.createRadialGradient(cx, cy, hubR-4, cx, cy, hubR+14);
        hs.addColorStop(0, 'rgba(0,0,0,.7)'); hs.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.beginPath(); ctx.arc(cx, cy, hubR+12, 0, Math.PI*2);
        ctx.fillStyle = hs; ctx.fill();

        const hb = ctx.createRadialGradient(cx-5, cy-5, 2, cx, cy, hubR);
        hb.addColorStop(0,   '#3730a3');
        hb.addColorStop(.5,  '#1e1b4b');
        hb.addColorStop(1,   '#0a0918');
        ctx.beginPath(); ctx.arc(cx, cy, hubR, 0, Math.PI*2);
        ctx.fillStyle = hb; ctx.fill();

        ctx.beginPath(); ctx.arc(cx, cy, hubR, 0, Math.PI*2);
        ctx.strokeStyle = 'rgba(245,200,80,.8)'; ctx.lineWidth = 2.5; ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, hubR-8, 0, Math.PI*2);
        ctx.strokeStyle = 'rgba(99,102,241,.45)'; ctx.lineWidth = 1.5; ctx.stroke();

        ctx.font = `${Math.floor(W/19)}px Segoe UI Emoji, Apple Color Emoji, sans-serif`;
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.shadowColor = 'rgba(245,200,80,.6)'; ctx.shadowBlur = 10;
        ctx.fillStyle = 'white';
        ctx.fillText('🎰', cx, cy+1);
        ctx.shadowBlur = 0; ctx.textBaseline = 'alphabetic';

        // 8 · Flapper — drawn last so it sits over the rim
        this._drawFlapper(cx, cy, outerR, rimW, flapAngle);
    }

    // ── Flapper ─────────────────────────────────────────────────────────────
    _drawFlapper(cx, cy, outerR, rimW, angle) {
        const ctx      = this.ctx;
        // Pivot lives above outer rim; since cy is shifted down 10px and outerR = W/2-10,
        // pivotY = cy - outerR - 10 = (H/2+10) - (W/2-10) - 10 = H/2 - W/2 + 10
        // With H = W+28 → = (W+28)/2 - W/2 + 10 = 14 + 10 = 24px from canvas top ✓
        const pivotX   = cx;
        const pivotY   = cy - outerR - 10;
        const flapLen  = rimW + 18;  // tip reaches into segment area for drama

        ctx.save();
        ctx.translate(pivotX, pivotY);
        ctx.rotate(angle);

        ctx.shadowColor = 'rgba(0,0,0,.55)';
        ctx.shadowBlur  = 10;

        // Body — teardrop pointing downward
        ctx.beginPath();
        ctx.moveTo(-5, 2);
        ctx.bezierCurveTo(-9, flapLen*.35, -6, flapLen*.75, 0, flapLen);
        ctx.bezierCurveTo( 6, flapLen*.75,  9, flapLen*.35, 5, 2);
        ctx.closePath();

        const fg = ctx.createLinearGradient(-8, 0, 8, flapLen);
        fg.addColorStop(0,   '#fef9e4');
        fg.addColorStop(.25, '#f5c842');
        fg.addColorStop(.65, '#d97706');
        fg.addColorStop(1,   '#7c3d00');
        ctx.fillStyle = fg; ctx.fill();
        ctx.strokeStyle = 'rgba(0,0,0,.35)'; ctx.lineWidth = 1; ctx.stroke();

        // Highlight streak
        ctx.beginPath();
        ctx.moveTo(-1.5, 5);
        ctx.bezierCurveTo(-3, flapLen*.3, -2.5, flapLen*.55, 0, flapLen*.78);
        ctx.strokeStyle = 'rgba(255,255,255,.28)'; ctx.lineWidth = 2; ctx.stroke();

        // Tip cap (small disc)
        ctx.beginPath(); ctx.arc(0, flapLen, 4, 0, Math.PI*2);
        ctx.fillStyle = '#92400e'; ctx.fill();

        // Pivot cap
        ctx.shadowBlur = 0;
        ctx.beginPath(); ctx.arc(0, 0, 7, 0, Math.PI*2);
        const cap = ctx.createRadialGradient(-2,-2,0, 0,0,7);
        cap.addColorStop(0, '#fff8cc'); cap.addColorStop(1, '#a06000');
        ctx.fillStyle = cap; ctx.fill();
        ctx.strokeStyle = 'rgba(0,0,0,.5)'; ctx.lineWidth = 1.5; ctx.stroke();

        // Pivot highlight
        ctx.beginPath(); ctx.arc(-2,-2,2.5,0,Math.PI*2);
        ctx.fillStyle = 'rgba(255,252,200,.55)'; ctx.fill();

        ctx.restore();
    }

    // ── Spin animation ──────────────────────────────────────────────────────
    async spinTo(targetIndex) {
        const { n, arc } = this;
        // Land with target segment centre under the pointer (12 o'clock)
        const segCentre  = targetIndex * arc + arc / 2;
        const extraSpins = (6 + Math.floor(Math.random() * 4)) * Math.PI * 2;
        const totalAngle = extraSpins + (Math.PI * 2 - segCentre);

        const DURATION  = 7500;
        const startRot  = this.rotation;
        const startTime = performance.now();
        let prevTime    = startTime;
        let prevEased   = 0;

        return new Promise(resolve => {
            const frame = (now) => {
                const elapsed  = now - startTime;
                const dt       = Math.max(1, now - prevTime); prevTime = now;
                const progress = Math.min(elapsed / DURATION, 1);

                // Two-phase: fast ease-in (15%) then long dramatic ease-out (85%)
                let eased;
                if (progress < .15) {
                    const t = progress / .15;
                    eased   = t * t * .15;
                } else {
                    const t2 = (progress - .15) / .85;
                    eased    = .15 + (1 - Math.pow(1 - t2, 4)) * .85;
                }

                const currentRot = startRot + totalAngle * eased;

                // Angular velocity for sound/flapper scaling
                const dEased = eased - prevEased;
                const angVel = Math.abs((totalAngle * dEased) / (dt / 1000));
                prevEased    = eased;

                // Detect peg crossing
                const norm       = ((currentRot % (Math.PI*2)) + Math.PI*2) % (Math.PI*2);
                const currentPeg = Math.floor(norm / arc) % n;
                if (currentPeg !== this.lastPeg) {
                    this.lastPeg = currentPeg;
                    const sf = Math.min(1, angVel / 25);
                    this.flapVelocity += 3.5 + sf * 9;  // impulse
                    this._playTick(sf);
                }

                // Spring physics on flapper
                const dtS = dt / 1000;
                this.flapVelocity += (-300 * this.flapAngle - 16 * this.flapVelocity) * dtS;
                this.flapAngle    += this.flapVelocity * dtS;
                this.flapAngle     = Math.max(-.04, Math.min(.7, this.flapAngle));

                this._render(currentRot, this.flapAngle);

                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    this.rotation  = currentRot % (Math.PI * 2);
                    this.flapAngle = 0; this.flapVelocity = 0;
                    this._render(this.rotation, 0);
                    resolve();
                }
            };
            requestAnimationFrame(frame);
        });
    }

    // ── Tick sound (pitch + duration vary with speed) ────────────────────────
    _playTick(speedFactor) {
        try {
            if (!this._audioCtx) this._audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const ac = this._audioCtx;
            const o  = ac.createOscillator(), g = ac.createGain();
            o.connect(g); g.connect(ac.destination);
            o.type = 'triangle';
            // Fast → high ping; slow → deep clunk
            const startHz = 150 + speedFactor * 700;
            const endHz   = 60  + speedFactor * 40;
            const dur     = .025 + (1 - speedFactor) * .07;
            o.frequency.setValueAtTime(startHz, ac.currentTime);
            o.frequency.exponentialRampToValueAtTime(endHz, ac.currentTime + dur);
            const vol = .04 + speedFactor * .1;
            g.gain.setValueAtTime(vol, ac.currentTime);
            g.gain.exponentialRampToValueAtTime(.001, ac.currentTime + dur);
            o.start(); o.stop(ac.currentTime + dur);
        } catch(e) {}
    }
}
</script>
<script>
if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(() => {});
</script>
<x-mobile-bottom-nav active="city" />
</body>
</html>
