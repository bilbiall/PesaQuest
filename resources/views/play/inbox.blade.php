<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Your Life Today – PesaQuest</title>
    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PesaQuest">
    <link rel="apple-touch-icon" href="/moski-logo.png">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }

        /* ── Background ── */
        .inbox-bg {
            background:
                radial-gradient(ellipse at top left,    rgba(99,102,241,0.13) 0%, transparent 55%),
                radial-gradient(ellipse at bottom right, rgba(139,92,246,0.10) 0%, transparent 55%),
                #07060f;
            min-height: 100vh;
        }

        /* ── Card base ── */
        .decision-card {
            background: linear-gradient(160deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 1.5rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .decision-card:hover { transform: translateY(-3px); box-shadow: 0 20px 60px -15px rgba(99,102,241,0.25); }

        /* ── Card image ── */
        .card-image {
            position: relative;
            height: 180px;
            overflow: hidden;
        }
        .card-image img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .decision-card:hover .card-image img { transform: scale(1.04); }
        .card-image-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to bottom, rgba(7,6,15,0.2) 0%, rgba(7,6,15,0.85) 100%);
        }

        /* ── NPC badge in image ── */
        .npc-badge {
            position: absolute;
            bottom: -20px;
            left: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .npc-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.2);
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        .npc-name-pill {
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 9999px;
            padding: 0.2rem 0.7rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: white;
            white-space: nowrap;
        }

        /* ── Category tag ── */
        .category-tag {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            backdrop-filter: blur(6px);
        }

        /* ── Choice buttons ── */
        .choice-btn {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.875rem;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: white;
            font-size: 0.82rem;
            font-weight: 700;
            text-align: left;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .choice-btn:hover {
            background: rgba(99,102,241,0.15);
            border-color: rgba(99,102,241,0.4);
            transform: translateX(3px);
        }
        .choice-btn.selected {
            background: rgba(99,102,241,0.25);
            border-color: rgba(99,102,241,0.6);
        }

        /* ── Outcome reveal ── */
        .outcome-panel {
            background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(99,102,241,0.06));
            border: 1px solid rgba(16,185,129,0.2);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
        }
        .lesson-panel {
            background: rgba(245,158,11,0.07);
            border: 1px solid rgba(245,158,11,0.25);
            border-radius: 0.875rem;
            padding: 0.875rem 1.1rem;
        }

        /* ── NPC relationship bar ── */
        .rel-bar-outer {
            height: 5px;
            border-radius: 9999px;
            background: rgba(255,255,255,0.08);
            overflow: hidden;
        }
        .rel-bar-inner {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.6s ease;
        }

        /* ── Pulse dot ── */
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }
        .pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }

        /* ── Card flip animation ── */
        @keyframes card-resolve {
            0%   { transform: scale(1); }
            30%  { transform: scale(0.97) rotate(-0.5deg); }
            60%  { transform: scale(1.01); }
            100% { transform: scale(1); }
        }
        .card-resolving { animation: card-resolve 0.5s ease; }

        /* ── Coin fly ── */
        @keyframes coin-fly {
            0%   { transform: translateY(0) scale(1); opacity: 1; }
            100% { transform: translateY(-60px) scale(0.5); opacity: 0; }
        }
        .coin-fly { animation: coin-fly 0.7s ease forwards; }

        /* ── Scroll snap on mobile ── */
        @media (max-width: 640px) {
            .cards-scroll { scroll-snap-type: x mandatory; overflow-x: auto; display: flex; gap: 1rem; padding-bottom: 0.5rem; }
            /* width + max-width (a hard cap), not min-width (a floor) — a floor lets
               unwrapped text inside push the card wider than the screen. */
            .cards-scroll .decision-card { width: 88vw; max-width: 88vw; scroll-snap-align: start; flex-shrink: 0; }
        }

        /* ── Glass sidebar ── */
        .glass-sidebar {
            background: linear-gradient(160deg, rgba(255,255,255,0.04), rgba(255,255,255,0.015));
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 1.25rem;
        }
    </style>
</head>
<body class="inbox-bg" x-data="inboxApp()">

{{-- ══════════════════════ TOP NAV ══════════════════════ --}}
<nav class="sticky top-0 z-40 border-b border-white/5" style="background:rgba(7,6,15,0.85);backdrop-filter:blur(20px);">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="flex items-center gap-2">
                <span class="text-lg">🎮</span>
                <span class="font-black text-white text-sm">Your Life Today</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Game Day --}}
            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold" style="background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.2);color:#a5b4fc;">
                <span>📅</span>
                <span>Day {{ $progress->tick_count ?? 0 }}</span>
            </div>
            {{-- Balance --}}
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);color:#6ee7b7;">
                <span>💰</span>
                <span>Ksh {{ number_format($progress->balance ?? 0) }}</span>
            </div>
            {{-- Scenarios link --}}
            <a href="{{ route('game.play') }}"
               class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all hover:scale-105"
               style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);color:#fcd34d;">
                <span>📖</span>
                <span>Scenarios</span>
            </a>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex gap-6">

        {{-- ══════════════════════ MAIN COLUMN ══════════════════════ --}}
        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-dot"></span>
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Live</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                    @php
                        $hour = now()->hour;
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                        $firstName = explode(' ', auth()->user()->name)[0];
                    @endphp
                    {{ $greeting }}, {{ $firstName }}. 👋
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    @if($pending->isNotEmpty())
                        <span class="text-amber-400 font-bold">{{ $pending->count() }} decision{{ $pending->count() > 1 ? 's' : '' }}</span> waiting in your virtual life.
                    @else
                        You're all caught up — new events arrive as your game progresses.
                    @endif
                </p>
            </div>

            {{-- ── PENDING DECISION CARDS ── --}}
            @if($pending->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8 cards-scroll sm:grid">

                @foreach($pending as $pd)
                @php
                    $decision   = $pd->decision;
                    $npc        = $decision->npc;
                    $catColor   = $decision->categoryColor();
                    $catLabel   = $decision->categoryLabel();
                @endphp

                <div
                    class="decision-card"
                    x-data="decisionCard({{ $pd->id }}, {{ json_encode($decision->choices->map(fn($c) => ['id'=>$c->id,'label'=>$c->label,'description'=>$c->description,'balance_delta'=>$c->balance_delta])) }})"
                    :class="{ 'card-resolving': resolving }"
                    x-ref="card{{ $pd->id }}"
                >
                    {{-- Image area --}}
                    <div class="card-image">
                        @if($decision->image_url)
                        <img src="{{ $decision->image_url }}" alt="{{ $decision->title }}" loading="lazy"
                             onerror="this.src='https://picsum.photos/seed/{{ $pd->id }}/800/420'">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-6xl" style="background:linear-gradient(135deg,{{ $catColor }}22,{{ $catColor }}08);">{{ $decision->icon }}</div>
                        @endif
                        <div class="card-image-overlay"></div>

                        {{-- Category tag --}}
                        <div class="category-tag" style="background:{{ $catColor }}22;border:1px solid {{ $catColor }}44;color:{{ $catColor }};">
                            {{ $decision->icon }} {{ $catLabel }}
                        </div>

                        {{-- NPC badge --}}
                        @if($npc)
                        <div class="npc-badge">
                            <img src="{{ $npc->avatar_url }}" alt="{{ $npc->displayName() }}" class="npc-avatar">
                            <span class="npc-name-pill">{{ $npc->displayName() }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="p-5" style="padding-top: {{ $npc ? '2rem' : '1.25rem' }};">

                        {{-- Title --}}
                        <h3 class="font-black text-white text-base leading-snug mb-2">{{ $decision->title }}</h3>
                        <p class="text-gray-400 text-sm leading-relaxed mb-4">{{ $decision->body }}</p>

                        {{-- Choices (pre-resolve) --}}
                        <div x-show="!resolved" class="space-y-2">
                            @foreach($decision->choices as $choice)
                            <button
                                class="choice-btn"
                                :class="{ 'selected': selectedChoice === {{ $choice->id }}, 'opacity-50 cursor-not-allowed': resolving && selectedChoice !== {{ $choice->id }} }"
                                @click="pick({{ $choice->id }})"
                                :disabled="resolving"
                            >
                                <span class="flex-1 min-w-0">
                                    <span class="block font-bold text-white text-sm">{{ $choice->label }}</span>
                                    @if($choice->description)
                                    <span class="block text-gray-400 text-xs mt-0.5 font-normal">{{ $choice->description }}</span>
                                    @endif
                                </span>
                                @if($choice->balance_delta !== 0)
                                <span class="text-xs font-black whitespace-nowrap {{ $choice->balance_delta > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $choice->balance_delta > 0 ? '+' : '' }}Ksh {{ number_format(abs($choice->balance_delta)) }}
                                </span>
                                @endif
                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                            @endforeach

                            {{-- Loading spinner --}}
                            <div x-show="resolving" class="flex items-center justify-center gap-2 py-2 text-indigo-400 text-xs font-semibold animate-pulse">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Processing your choice…
                            </div>
                        </div>

                        {{-- Outcome (post-resolve) --}}
                        <div x-show="resolved" x-cloak x-transition:enter="transition ease-out duration-400" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3">

                            {{-- Balance change badge --}}
                            <div x-show="outcome.balance_delta !== 0" class="flex items-center gap-2">
                                <span class="text-xs font-black px-3 py-1 rounded-full"
                                      :class="outcome.balance_delta > 0 ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30'">
                                    <span x-text="outcome.balance_delta > 0 ? '↑ +Ksh ' + Math.abs(outcome.balance_delta).toLocaleString() : '↓ -Ksh ' + Math.abs(outcome.balance_delta).toLocaleString()"></span>
                                </span>
                                <span x-show="outcome.credit_delta !== 0"
                                      class="text-xs font-black px-3 py-1 rounded-full"
                                      :class="outcome.credit_delta > 0 ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' : 'bg-orange-500/15 text-orange-400 border border-orange-500/30'">
                                    <span x-text="'Credit ' + (outcome.credit_delta > 0 ? '+' : '') + outcome.credit_delta"></span>
                                </span>
                            </div>

                            {{-- Outcome text --}}
                            <div class="outcome-panel">
                                <p class="text-sm text-gray-200 leading-relaxed" x-text="outcome.outcome_text"></p>
                            </div>

                            {{-- Financial lesson --}}
                            <div x-show="outcome.financial_lesson" class="lesson-panel">
                                <div class="flex gap-2">
                                    <span class="text-lg flex-shrink-0">💡</span>
                                    <div>
                                        <p class="text-xs font-black text-amber-400 uppercase tracking-wider mb-1">Financial Lesson</p>
                                        <p class="text-xs text-amber-200/80 leading-relaxed" x-text="outcome.financial_lesson"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Badge earned --}}
                            <div x-show="outcome.badge_slug" x-cloak
                                 class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold"
                                 style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fcd34d;">
                                🏅 Badge earned: <span class="capitalize" x-text="(outcome.badge_slug||'').replace(/-/g,' ')"></span>
                            </div>

                            <button @click="dismiss()"
                                    class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:scale-[1.02]"
                                    style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 16px rgba(124,58,237,0.3);">
                                Continue →
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
            @else
            {{-- Empty state --}}
            <div class="text-center py-16 mb-8">
                <div class="text-6xl mb-4">🎉</div>
                <h3 class="text-xl font-black text-white mb-2">You're all caught up!</h3>
                <p class="text-gray-400 text-sm max-w-xs mx-auto">New life events arrive as your game clock ticks. Keep playing to unlock more of your virtual story.</p>
                <a href="{{ route('life.board') }}"
                   class="inline-flex items-center gap-2 mt-6 px-6 py-3 rounded-xl font-bold text-sm text-white transition-all hover:scale-105"
                   style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">
                    🏠 Visit Life Board
                </a>
            </div>
            @endif

            {{-- ── RECENTLY RESOLVED ── --}}
            @if($recent->isNotEmpty())
            <div class="mb-6">
                <h2 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Recent Decisions</h2>
                <div class="space-y-3">
                    @foreach($recent as $rd)
                    @php $rc = $rd->choice; $rd_decision = $rd->decision; @endphp
                    <div class="flex items-start gap-3 p-4 rounded-2xl" style="background:rgba(255,255,255,0.025);border:1px solid rgba(255,255,255,0.06);">
                        @if($rd_decision->npc)
                        <img src="{{ $rd_decision->npc->avatar_url }}" alt="" class="w-9 h-9 rounded-full flex-shrink-0 opacity-70">
                        @else
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-lg flex-shrink-0 opacity-70" style="background:rgba(99,102,241,0.15);">{{ $rd_decision->icon }}</div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-300 leading-snug">{{ $rd_decision->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Chose: <span class="text-gray-400">{{ $rc->label ?? '—' }}</span></p>
                        </div>
                        @if($rc && $rc->balance_delta !== 0)
                        <span class="text-xs font-black flex-shrink-0 {{ $rc->balance_delta > 0 ? 'text-emerald-500' : 'text-red-500' }}">
                            {{ $rc->balance_delta > 0 ? '+' : '' }}Ksh {{ number_format(abs($rc->balance_delta)) }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── QUICK ACTIONS ── --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['href' => route('life.board'),      'icon' => '🏠', 'label' => 'Life Board',   'color' => '#10b981'],
                    ['href' => route('spin.index'),       'icon' => '🎰', 'label' => 'Daily Spin',   'color' => '#f59e0b'],
                    ['href' => route('marketplace'),      'icon' => '🛒', 'label' => 'Marketplace',  'color' => '#06b6d4'],
                    ['href' => route('game.play'),        'icon' => '📖', 'label' => 'Scenarios',    'color' => '#8b5cf6'],
                ] as $action)
                <a href="{{ $action['href'] }}"
                   class="flex flex-col items-center gap-2 py-4 px-3 rounded-2xl text-center transition-all hover:scale-105 hover:-translate-y-1"
                   style="background:{{ $action['color'] }}0f;border:1px solid {{ $action['color'] }}22;">
                    <span class="text-2xl">{{ $action['icon'] }}</span>
                    <span class="text-xs font-bold" style="color:{{ $action['color'] }}">{{ $action['label'] }}</span>
                </a>
                @endforeach
            </div>

        </div>

        {{-- ══════════════════════ SIDEBAR ══════════════════════ --}}
        <aside class="hidden lg:flex flex-col gap-4 w-72 flex-shrink-0">

            {{-- Progress snapshot --}}
            <div class="glass-sidebar p-5">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Your Finances</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Balance</span>
                        <span class="text-sm font-black text-white">Ksh {{ number_format($progress->balance ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Credit Score</span>
                        <span class="text-sm font-black {{ ($progress->credit_score ?? 500) >= 650 ? 'text-emerald-400' : (($progress->credit_score ?? 500) >= 500 ? 'text-amber-400' : 'text-red-400') }}">
                            {{ $progress->credit_score ?? 500 }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Net Worth</span>
                        <span class="text-sm font-black text-purple-300">Ksh {{ number_format($progress->net_worth_cache ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Career</span>
                        <span class="text-xs font-bold text-indigo-300 capitalize">{{ str_replace('_', ' ', $progress->career_title ?? 'None') }}</span>
                    </div>

                    {{-- Credit score bar --}}
                    @php $cs = $progress->credit_score ?? 500; $csPercent = (($cs - 300) / 550) * 100; @endphp
                    <div class="pt-2">
                        <div class="flex justify-between text-[10px] text-gray-600 mb-1">
                            <span>300</span><span>Credit Score</span><span>850</span>
                        </div>
                        <div class="rel-bar-outer">
                            <div class="rel-bar-inner {{ $cs >= 650 ? 'bg-emerald-400' : ($cs >= 500 ? 'bg-amber-400' : 'bg-red-400') }}"
                                 style="width: {{ round($csPercent) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NPC Relationships --}}
            @if($npcs->isNotEmpty())
            <div class="glass-sidebar p-5">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">People in Your Life</h3>
                <div class="space-y-3">
                    @foreach($npcs as $npc)
                    @php $score = $relationships[$npc->id] ?? $npc->initial_relationship; @endphp
                    <div class="flex items-center gap-3">
                        <div class="relative flex-shrink-0">
                            <img src="{{ $npc->avatar_url }}" alt="{{ $npc->displayName() }}" class="w-9 h-9 rounded-full">
                            <span class="absolute -bottom-0.5 -right-0.5 text-[10px]">{{ $npc->relationshipLabel($score) === 'Close' ? '❤️' : ($npc->relationshipLabel($score) === 'Friendly' ? '😊' : ($npc->relationshipLabel($score) === 'Neutral' ? '😐' : '😟')) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center mb-0.5">
                                <p class="text-xs font-bold text-gray-300 truncate">{{ $npc->displayName() }}</p>
                                <p class="text-[10px] text-gray-600 ml-1">{{ $npc->relationshipLabel($score) }}</p>
                            </div>
                            <div class="rel-bar-outer">
                                <div class="rel-bar-inner" style="width:{{ $score }}%;background:{{ $npc->cover_color }};"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-[10px] text-gray-600 mt-4 leading-relaxed">Your choices affect these relationships. Better relationships unlock better opportunities.</p>
            </div>
            @endif

            {{-- Daily tip --}}
            @php
            $tips = [
                ['icon'=>'💡','tip'=>'Save before you spend. Transfer to savings the moment your salary hits — not what\'s left at month-end.'],
                ['icon'=>'📊','tip'=>'The 50/30/20 rule: 50% needs, 30% wants, 20% savings. It\'s simple — and it works.'],
                ['icon'=>'🏦','tip'=>'A SACCO can give you loans at 1% per month. Banks charge 2-3%. Join one.'],
                ['icon'=>'📈','tip'=>'Compound interest doubles money at ~72 ÷ interest rate years. At 12%, your money doubles in 6 years.'],
                ['icon'=>'🛡️','tip'=>'Your emergency fund should cover 3-6 months of expenses. It\'s not savings — it\'s insurance.'],
                ['icon'=>'💳','tip'=>'A credit score above 700 unlocks better loan rates. Pay bills on time. Every time.'],
            ];
            $tip = $tips[($progress->tick_count ?? 0) % count($tips)];
            @endphp
            <div class="glass-sidebar p-5">
                <div class="flex gap-3">
                    <span class="text-2xl flex-shrink-0">{{ $tip['icon'] }}</span>
                    <div>
                        <p class="text-xs font-black text-amber-400 uppercase tracking-wider mb-1">Financial Tip</p>
                        <p class="text-xs text-gray-400 leading-relaxed">{{ $tip['tip'] }}</p>
                    </div>
                </div>
            </div>

        </aside>
    </div>
</div>

{{-- ══════════════════════ SCRIPTS ══════════════════════ --}}
<script>
function inboxApp() {
    return {
        init() {}
    };
}

function decisionCard(playerDecisionId, choices) {
    return {
        playerDecisionId,
        choices,
        selectedChoice: null,
        resolving: false,
        resolved: false,
        outcome: {},

        async pick(choiceId) {
            if (this.resolving || this.resolved) return;
            this.selectedChoice = choiceId;
            this.resolving = true;
            this.playChoiceSound();

            try {
                const res = await fetch('{{ route('inbox.resolve') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        player_decision_id: this.playerDecisionId,
                        choice_id: choiceId,
                    }),
                });

                const data = await res.json();
                if (data.success) {
                    this.outcome = data;
                    this.resolved = true;
                    this.playOutcomeSound(data.balance_delta);
                    this.maybeSpawnCoins(data.balance_delta);
                    // Update balance in nav (re-fetch would be clean but this is fast)
                    const balEl = document.querySelector('[data-balance]');
                    if (balEl && data.new_balance !== undefined) {
                        balEl.textContent = 'Ksh ' + Number(data.new_balance).toLocaleString();
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.resolving = false;
            }
        },

        dismiss() {
            // Remove card with animation then reload to get fresh decisions
            this.$el.style.transition = 'all 0.4s ease';
            this.$el.style.opacity = '0';
            this.$el.style.transform = 'scale(0.95) translateY(-10px)';
            setTimeout(() => {
                this.$el.remove();
                const remaining = document.querySelectorAll('.decision-card:not([style*="opacity: 0"])');
                if (remaining.length === 0) {
                    window.location.reload();
                }
            }, 400);
        },

        playChoiceSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const o = ctx.createOscillator(), g = ctx.createGain();
                o.connect(g); g.connect(ctx.destination);
                o.type = 'sine'; o.frequency.setValueAtTime(440, ctx.currentTime);
                g.gain.setValueAtTime(0.1, ctx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                o.start(); o.stop(ctx.currentTime + 0.15);
            } catch(e) {}
        },

        playOutcomeSound(delta) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const freqs = delta >= 0
                    ? [523, 659, 784, 1047]   // positive: ascending chime
                    : [523, 440, 349, 294];    // negative: descending
                freqs.forEach((freq, i) => {
                    const o = ctx.createOscillator(), g = ctx.createGain();
                    o.connect(g); g.connect(ctx.destination);
                    o.type = delta >= 0 ? 'sine' : 'triangle';
                    o.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.1);
                    g.gain.setValueAtTime(0.12, ctx.currentTime + i * 0.1);
                    g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.1 + 0.25);
                    o.start(ctx.currentTime + i * 0.1);
                    o.stop(ctx.currentTime + i * 0.1 + 0.25);
                });
            } catch(e) {}
        },

        maybeSpawnCoins(delta) {
            if (delta <= 0) return;
            const count = Math.min(8, Math.ceil(delta / 5000));
            const rect = this.$el.getBoundingClientRect();
            for (let i = 0; i < count; i++) {
                const coin = document.createElement('div');
                coin.textContent = '💰';
                coin.style.cssText = `
                    position:fixed;
                    left:${rect.left + Math.random() * rect.width}px;
                    top:${rect.top + rect.height * 0.6}px;
                    font-size:${14 + Math.random()*10}px;
                    pointer-events:none;
                    z-index:9999;
                    animation:coin-fly ${0.5 + Math.random()*0.4}s ease forwards;
                    animation-delay:${i*0.06}s;
                `;
                document.body.appendChild(coin);
                setTimeout(() => coin.remove(), 1200);
            }
        },
    };
}
</script>

</body>
</html>
