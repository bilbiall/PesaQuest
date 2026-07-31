<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quest Board — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
        @keyframes shimmer { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        @keyframes popIn   { from{opacity:0;transform:translateY(18px) scale(0.97)} to{opacity:1;transform:translateY(0) scale(1)} }
        @keyframes questPulse { 0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,0)} 50%{box-shadow:0 0 0 6px rgba(99,102,241,0.18)} }
        .shimmer-text {
            background: linear-gradient(90deg, #a78bfa, #38bdf8, #34d399, #a78bfa);
            background-size: 300% 300%;
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 4s ease infinite;
        }
        .quest-card { animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both; }
        .quest-card:nth-child(1){animation-delay:.04s} .quest-card:nth-child(2){animation-delay:.08s}
        .quest-card:nth-child(3){animation-delay:.12s} .quest-card:nth-child(4){animation-delay:.16s}
        .quest-card:nth-child(5){animation-delay:.20s} .quest-card:nth-child(6){animation-delay:.24s}
        .quest-available { animation: questPulse 2.5s ease infinite; }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500 hidden sm:block">🗺️ Quest Board</span>
            <a href="{{ route('game.play') }}"
               class="text-xs text-indigo-400 hover:text-indigo-300 border border-indigo-500/30 hover:border-indigo-500/60 px-3 py-1.5 rounded-lg transition-colors">
                ▶ Play
            </a>
        </div>
    </div>
</nav>

{{-- Hero --}}
<div class="relative overflow-hidden border-b border-white/5 py-10"
     style="background:linear-gradient(160deg,rgba(99,102,241,0.08) 0%,rgba(139,92,246,0.05) 100%);">
    <div class="absolute top-0 right-0 w-80 h-80 rounded-full opacity-6"
         style="background:radial-gradient(circle,#a78bfa,transparent 70%);transform:translate(30%,-30%);"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl sm:text-4xl font-black shimmer-text mb-2">🗺️ Quest Board</h1>
        <p class="text-gray-400 text-sm mb-6">Complete real-world financial challenges to earn bonus XP</p>

        {{-- Summary stats --}}
        <div class="flex flex-wrap gap-4">
            <div class="rounded-2xl px-5 py-3 flex items-center gap-3"
                 style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                <span class="text-2xl">✅</span>
                <div>
                    <div class="text-xl font-black text-emerald-400">{{ $completedCount }}</div>
                    <div class="text-xs text-gray-400">Completed</div>
                </div>
            </div>
            <div class="rounded-2xl px-5 py-3 flex items-center gap-3"
                 style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);">
                <span class="text-2xl">⭐</span>
                <div>
                    <div class="text-xl font-black text-amber-400">{{ number_format($totalPoints) }}</div>
                    <div class="text-xs text-gray-400">XP Earned</div>
                </div>
            </div>
            <div class="rounded-2xl px-5 py-3 flex items-center gap-3"
                 style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);">
                <span class="text-2xl">🎯</span>
                <div>
                    <div class="text-xl font-black text-indigo-400">{{ $quests->where('user_status', 'available')->count() }}</div>
                    <div class="text-xs text-gray-400">Available</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quest cards --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8" x-data="questBoard()">

    @if($quests->isEmpty())
    <div class="text-center py-16">
        <div class="text-6xl mb-4">🗺️</div>
        <p class="text-gray-400 font-semibold mb-2">No quests available for your age group yet.</p>
        <p class="text-gray-600 text-sm">Check back soon — new challenges are added regularly.</p>
    </div>
    @else

    {{-- Filter tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach(['all' => 'All', 'available' => '🎯 Available', 'pending' => '⏳ In Progress', 'approved' => '✅ Completed'] as $key => $label)
        <button @click="filter='{{ $key }}'"
                :class="filter==='{{ $key }}' ? 'bg-indigo-500/20 border-indigo-500/50 text-indigo-300' : 'border-white/10 text-gray-400 hover:text-white'"
                class="px-4 py-2 rounded-xl text-xs font-bold border transition-all">
            {{ $label }}
            <span class="ml-1 opacity-60">
                @if($key === 'all') {{ $quests->count() }}
                @elseif($key === 'available') {{ $quests->whereIn('user_status', ['available'])->count() }}
                @elseif($key === 'pending') {{ $quests->where('user_status', 'pending')->count() }}
                @elseif($key === 'approved') {{ $quests->where('user_status', 'approved')->count() }}
                @endif
            </span>
        </button>
        @endforeach
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($quests as $quest)
        @php
            $statusColor = match($quest->user_status) {
                'approved' => ['bg' => 'rgba(16,185,129,0.08)', 'border' => 'rgba(16,185,129,0.25)', 'pill' => 'text-emerald-400 bg-emerald-500/15 border-emerald-500/30'],
                'pending'  => ['bg' => 'rgba(245,158,11,0.08)', 'border' => 'rgba(245,158,11,0.25)', 'pill' => 'text-amber-400 bg-amber-500/15 border-amber-500/30'],
                default    => ['bg' => 'rgba(99,102,241,0.05)', 'border' => 'rgba(99,102,241,0.18)', 'pill' => 'text-indigo-400 bg-indigo-500/10 border-indigo-500/30'],
            };
        @endphp
        <div class="quest-card rounded-3xl p-5 flex flex-col gap-4 {{ $quest->user_status === 'available' ? 'quest-available' : '' }}"
             style="background:{{ $statusColor['bg'] }};border:1px solid {{ $statusColor['border'] }};"
             x-show="filter === 'all' || filter === '{{ $quest->user_status }}'">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ $quest->icon ?? '🎯' }}</span>
                    <div>
                        <p class="font-black text-white leading-tight">{{ $quest->title }}</p>
                        @if($quest->age_group)
                        <span class="text-[10px] text-gray-500 uppercase tracking-widest">{{ $quest->age_group }}</span>
                        @endif
                    </div>
                </div>
                <span class="shrink-0 text-[10px] font-black px-2 py-1 rounded-full border {{ $statusColor['pill'] }}">
                    @if($quest->user_status === 'approved') ✓ Done
                    @elseif($quest->user_status === 'pending') Pending
                    @else Available
                    @endif
                </span>
            </div>

            {{-- Description --}}
            <p class="text-xs text-gray-400 leading-relaxed flex-1">{{ $quest->description }}</p>

            {{-- Instructions --}}
            @if($quest->instructions)
            <div class="rounded-xl px-3 py-2.5 text-xs text-gray-300 leading-relaxed"
                 style="background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.06);">
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-bold mb-1">How to complete</p>
                {{ $quest->instructions }}
            </div>
            @endif

            {{-- Footer --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5 text-amber-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span class="text-sm font-black">{{ number_format($quest->xp_reward ?? 0) }} XP</span>
                </div>

                @if($quest->user_status === 'available')
                <a href="{{ route('world') }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition-all hover:scale-[1.04] inline-block"
                   style="background:linear-gradient(135deg,rgba(99,102,241,0.25),rgba(139,92,246,0.2));border:1px solid rgba(139,92,246,0.4);color:#c4b5fd;">
                    Go to Quest Board →
                </a>
                @elseif($quest->user_status === 'pending')
                <span class="text-xs text-amber-500 font-semibold">⏳ In Progress</span>
                @else
                <span class="text-xs text-emerald-400 font-semibold">🏆 Completed!</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Toast --}}
    <div x-show="toast" x-cloak x-transition
         class="fixed bottom-6 left-1/2 -translate-x-1/2 px-5 py-3 rounded-2xl text-sm font-bold z-50"
         :style="toastOk ? 'background:rgba(16,185,129,0.9);color:white;' : 'background:rgba(239,68,68,0.9);color:white;'"
         x-text="toastMsg">
    </div>
</div>

@include('components.mama-pesa-chat')

<script>
function questBoard() {
    return {
        filter: 'all',
        submitting: null,
        toast: false,
        toastOk: true,
        toastMsg: '',

        async submit(questId, btn) {
            if (this.submitting) return;
            this.submitting = questId;
            try {
                const res = await fetch(`/game/quests/${questId}/submit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (res.ok) {
                    this.showToast(data.message || 'Quest submitted!', true);
                    setTimeout(() => window.location.reload(), 1800);
                } else {
                    this.showToast(data.message || 'Could not submit quest.', false);
                }
            } catch {
                this.showToast('Network error. Please try again.', false);
            }
            this.submitting = null;
        },

        showToast(msg, ok = true) {
            this.toastMsg = msg;
            this.toastOk  = ok;
            this.toast    = true;
            setTimeout(() => this.toast = false, 3000);
        }
    };
}
</script>
<x-mobile-bottom-nav active="quests" />
</body>
</html>
