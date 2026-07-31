<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Life Timeline — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
        .timeline-line { width: 2px; background: linear-gradient(to bottom, rgba(139,92,246,0.6), rgba(99,102,241,0.1)); }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- ── Nav ── --}}
<x-life-topnav active="timeline" title="Life Story" icon="📖" />

{{-- ── Hero ── --}}
<div class="border-b border-white/5 py-10"
     style="background: linear-gradient(135deg, rgba(139,92,246,0.08) 0%, rgba(99,102,241,0.05) 100%);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl sm:text-4xl font-black mb-2">📖 Your Life Story</h1>
        <p class="text-gray-400">Every event, every chapter. Your virtual financial journey so far.</p>

        {{-- Stats row --}}
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mt-6">
            <div class="rounded-2xl p-3 text-center" style="background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.2);">
                <p class="text-lg font-black text-purple-400">{{ $progress->level ?? 1 }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Level</p>
            </div>
            <div class="rounded-2xl p-3 text-center" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                @if(($progress->tick_count ?? 0) > 0)
                <p class="text-lg font-black text-emerald-400">{{ $progress->tick_count }}</p>
                @else
                <p class="text-xs font-black text-emerald-400 leading-7">New player</p>
                @endif
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Game Days</p>
            </div>
            <div class="rounded-2xl p-3 text-center" style="background:rgba(96,165,250,0.08);border:1px solid rgba(96,165,250,0.2);">
                <p class="text-lg font-black text-blue-400">{{ $totalEvents }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Events</p>
            </div>
            <div class="rounded-2xl p-3 text-center hidden sm:block" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);">
                <p class="text-lg font-black text-amber-400">Ksh {{ number_format($progress->net_worth_cache ?? 0) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Net Worth</p>
            </div>
            <div class="rounded-2xl p-3 text-center hidden sm:block" style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);">
                <p class="text-lg font-black {{ ($progress->credit_score ?? 500) >= 550 ? 'text-emerald-400' : 'text-red-400' }}">{{ $progress->credit_score ?? 500 }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Credit</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Journey Milestones ── --}}
@if(!empty($journeyMilestones))
<div class="border-b border-white/5 py-8" style="background:rgba(99,102,241,0.03);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <h2 class="text-base font-black text-gray-300 mb-4">🏁 Your Journey Goals</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($journeyMilestones as $ms)
            <div class="rounded-2xl p-3 flex items-start gap-2 relative overflow-hidden"
                 style="background:{{ $ms['achieved'] ? 'rgba(16,185,129,0.08)' : 'rgba(255,255,255,0.03)' }};border:1px solid {{ $ms['achieved'] ? 'rgba(16,185,129,0.25)' : 'rgba(255,255,255,0.07)' }};">
                @if($ms['achieved'])
                <div class="absolute top-2 right-2">
                    <span class="text-emerald-400 text-xs font-black">✓</span>
                </div>
                @endif
                <span class="text-xl flex-shrink-0 {{ $ms['achieved'] ? '' : 'opacity-40' }}">{{ $ms['icon'] }}</span>
                <div>
                    <p class="text-xs font-black {{ $ms['achieved'] ? 'text-white' : 'text-gray-500' }} leading-tight">{{ $ms['title'] }}</p>
                    @if(!empty($ms['description']))
                    <p class="text-[10px] text-gray-600 mt-0.5 leading-tight">{{ $ms['description'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @php
            $doneCount = collect($journeyMilestones)->where('achieved', true)->count();
            $totalMs   = count($journeyMilestones);
        @endphp
        <div class="mt-4 flex items-center gap-3">
            <div class="flex-1 h-1.5 rounded-full" style="background:rgba(255,255,255,0.06);">
                <div class="h-full rounded-full" style="width:{{ $totalMs > 0 ? round($doneCount/$totalMs*100) : 0 }}%;background:linear-gradient(90deg,#10b981,#059669);"></div>
            </div>
            <span class="text-xs text-gray-400 flex-shrink-0">{{ $doneCount }}/{{ $totalMs }} achieved</span>
        </div>
    </div>
</div>
@endif

{{-- ── Timeline ── --}}
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

    @if($groupedEvents->isEmpty())
    <div class="text-center py-20">
        <p class="text-5xl mb-4">🌱</p>
        <p class="text-xl font-black text-gray-300">Your story is just beginning</p>
        <p class="text-gray-500 mt-2 text-sm">Life events will appear here as you play. Keep logging in to advance your virtual life.</p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-block px-6 py-3 rounded-xl text-sm font-black text-white"
           style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">Go to Dashboard</a>
    </div>
    @else

    {{-- Chapter group loop --}}
    @foreach($groupedEvents as $chapter => $chapterEvents)
    @php
        $chapterConfig = collect(\App\Models\UserProgress::chapters());
        $chapterIcons  = $chapterConfig->pluck('icon', 'key')->all();
        $chapterNames  = $chapterConfig->pluck('name', 'key')->all();
        $chapterColors = [
            'student'  => ['text' => 'text-sky-400',     'bg' => 'rgba(56,189,248,0.1)',   'border' => 'rgba(56,189,248,0.3)'],
            'graduate' => ['text' => 'text-emerald-400', 'bg' => 'rgba(16,185,129,0.1)',   'border' => 'rgba(16,185,129,0.3)'],
            'hustler'  => ['text' => 'text-orange-400',  'bg' => 'rgba(251,146,60,0.1)',   'border' => 'rgba(251,146,60,0.3)'],
            'settler'  => ['text' => 'text-violet-400',  'bg' => 'rgba(167,139,250,0.1)',  'border' => 'rgba(167,139,250,0.3)'],
            'builder'  => ['text' => 'text-amber-400',   'bg' => 'rgba(245,158,11,0.1)',   'border' => 'rgba(245,158,11,0.3)'],
            'elder'    => ['text' => 'text-rose-400',    'bg' => 'rgba(251,113,133,0.1)',  'border' => 'rgba(251,113,133,0.3)'],
        ];
        $cc = $chapterColors[$chapter] ?? $chapterColors['graduate'];
    @endphp

    {{-- Chapter heading --}}
    <div class="flex items-center gap-4 mb-6 {{ !$loop->first ? 'mt-10' : '' }}">
        <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-xl flex-shrink-0"
             style="background:{{ $cc['bg'] }};border:1px solid {{ $cc['border'] }};">
            {{ $chapterIcons[$chapter] ?? '⭐' }}
        </div>
        <div>
            <h2 class="text-lg font-black {{ $cc['text'] }}">{{ $chapterNames[$chapter] ?? ucfirst($chapter) }}</h2>
            <p class="text-xs text-gray-500">{{ $chapterEvents->count() }} event{{ $chapterEvents->count() == 1 ? '' : 's' }}</p>
        </div>
    </div>

    {{-- Events in chapter --}}
    <div class="relative pl-8">
        {{-- Vertical line --}}
        <div class="timeline-line absolute left-3 top-0 bottom-0"></div>

        <div class="space-y-4">
            @foreach($chapterEvents as $ple)
            @php
                $balChange  = $ple->effect_applied['balance_change'] ?? $ple->effect_applied['delta'] ?? 0;
                $isCrisis   = ($ple->effect_applied['kind'] ?? '') === 'crisis';
                $isPositive = $isCrisis ? false : ($ple->lifeEvent->is_positive ?? true);
            @endphp
            <div class="relative">
                {{-- Dot --}}
                <div class="absolute -left-5 top-3 w-4 h-4 rounded-full border-2 border-current flex items-center justify-center
                    {{ $isPositive ? 'text-emerald-400 bg-emerald-400' : 'text-red-400 bg-red-400' }}"
                     style="background:{{ $isPositive ? 'rgba(16,185,129,0.3)' : 'rgba(248,113,113,0.3)' }};border-color:{{ $isPositive ? '#10b981' : '#f87171' }};">
                </div>

                <div class="rounded-2xl p-4" style="background:{{ $isCrisis ? 'rgba(245,158,11,0.05)' : 'rgba(255,255,255,0.03)' }};border:1px solid {{ $isCrisis ? 'rgba(245,158,11,0.3)' : 'rgba(255,255,255,0.07)' }};">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl flex-shrink-0">{{ $ple->lifeEvent->icon ?? '⚡' }}</span>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-black text-white text-sm">
                                    {{ $ple->lifeEvent->title ?? 'Life Event' }}
                                    @if($isCrisis)
                                    <span style="display:inline-block;font-size:.58rem;font-weight:900;letter-spacing:.06em;padding:.15rem .5rem;border-radius:999px;background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.4);color:#fcd34d;vertical-align:middle;margin-left:.3rem;">🌪️ CRISIS</span>
                                    @endif
                                </p>
                                @if($balChange != 0)
                                <span class="text-sm font-black flex-shrink-0 {{ $balChange >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $balChange >= 0 ? '+' : '' }}Ksh {{ number_format($balChange) }}
                                </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-1 leading-snug">{{ $ple->lifeEvent->description ?? '' }}</p>
                            @if($isCrisis && !empty($ple->effect_applied['note']))
                            <p class="text-xs mt-1 leading-snug" style="color:#fcd34d;">{{ $ple->effect_applied['note'] }}</p>
                            @endif
                            @if(!empty($ple->lifeEvent->flavor_text))
                            <p class="text-xs text-indigo-300/70 italic mt-1.5">"{{ $ple->lifeEvent->flavor_text }}"</p>
                            @endif
                            @if(!empty($ple->lifeEvent->educational_note))
                            <div class="mt-2 flex items-start gap-1.5">
                                <span class="text-xs text-indigo-400 flex-shrink-0">💡</span>
                                <p class="text-xs text-indigo-300/80 leading-snug">{{ $ple->lifeEvent->educational_note }}</p>
                            </div>
                            @endif
                            <p class="text-[10px] text-gray-600 mt-2">Level {{ $ple->game_age_at_trigger }} · Game Day {{ $ple->tick_triggered }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    @endif
</div>

<x-mobile-bottom-nav active="life" />
</body>
</html>
