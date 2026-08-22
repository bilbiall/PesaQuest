<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Chamas — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        @keyframes popIn { from{opacity:0;transform:scale(.93) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
        @keyframes glowpulse { 0%,100%{opacity:.5} 50%{opacity:1} }
        @keyframes shimmer { 0%{background-position:200% center} 100%{background-position:-200% center} }
        .card-appear { animation: popIn .35s cubic-bezier(.34,1.56,.64,1) both; }
        .card-appear:nth-child(1){animation-delay:.04s} .card-appear:nth-child(2){animation-delay:.09s}
        .card-appear:nth-child(3){animation-delay:.14s} .card-appear:nth-child(4){animation-delay:.19s}
        .chama-card { transition: transform .2s ease, box-shadow .2s ease; }
        .chama-card:hover { transform: translateY(-3px); box-shadow: 0 18px 50px rgba(0,0,0,.45); }
        .shimmer-text {
            background: linear-gradient(90deg,#fff 20%,#a78bfa 50%,#fff 80%);
            background-size:200% auto; -webkit-background-clip:text;
            -webkit-text-fill-color:transparent; animation:shimmer 3s linear infinite;
        }
        .pool-bar { height:6px; border-radius:3px; background:rgba(255,255,255,.08); overflow:hidden; }
        .pool-fill { height:100%; border-radius:3px; background:linear-gradient(90deg,#6366f1,#8b5cf6); transition:width .8s cubic-bezier(.4,0,.2,1); }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- ── Nav ── --}}
<nav class="border-b border-white/5 bg-black/50 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('game.play') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span class="hidden sm:inline">Back to Game</span>
        </a>
        <h1 class="text-lg font-black tracking-tight inline-flex items-center gap-2"><x-icon name="group" class="w-4 h-4" /> Chamas</h1>
        <a href="{{ route('chama.create') }}"
           class="flex items-center gap-1.5 text-sm font-bold px-4 py-2 rounded-xl transition-all"
           style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.3));border:1px solid rgba(139,92,246,.4);">
            <span class="text-lg leading-none">+</span> New Chama
        </a>
    </div>
</nav>

{{-- ── Hero / Stats bar ── --}}
<div class="border-b border-white/5 py-5"
     style="background:radial-gradient(ellipse at 20% 50%,rgba(99,102,241,.1) 0%,transparent 60%),radial-gradient(ellipse at 80% 20%,rgba(139,92,246,.08) 0%,transparent 50%),#07060f;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 text-[10px] font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-full mb-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                    Cooperative Investment Groups
                </div>
                <h2 class="text-xl sm:text-2xl font-black"><span class="shimmer-text">My Chamas</span></h2>
                <p class="text-gray-400 mt-1 max-w-lg text-xs">Pool resources with friends. Vote on investments. Share the rewards — or the lessons.</p>
            </div>
            <div class="grid grid-cols-2 gap-2.5 sm:w-64 shrink-0">
                <div class="rounded-xl p-3 text-center" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-0.5">Total Pool</p>
                    <p class="text-base font-black text-indigo-300">Ksh {{ number_format($totalPool) }}</p>
                </div>
                <div class="rounded-xl p-3 text-center" style="background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.2);">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-0.5">Monthly Out</p>
                    <p class="text-base font-black text-violet-300">Ksh {{ number_format($totalMonthly) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-8">

    {{-- ── Section 1: My Chamas ── --}}
    <section>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-black text-white">My Chamas</h3>
            <span class="text-xs text-gray-500 font-semibold">{{ $myChamas->count() }} group{{ $myChamas->count() !== 1 ? 's' : '' }}</span>
        </div>

        @if($myChamas->isEmpty())
        <div class="rounded-2xl text-center py-10 px-5" style="background:rgba(255,255,255,.03);border:1px dashed rgba(255,255,255,.1);">
            <div class="text-4xl mb-3" style="animation:glowpulse 3s ease-in-out infinite;">🤝</div>
            <p class="text-sm font-black text-gray-300">You're not in any chama yet</p>
            <p class="text-gray-500 mt-1.5 text-xs">Create one or join an existing group below.</p>
            <a href="{{ route('chama.create') }}"
               class="inline-flex items-center gap-2 mt-4 px-5 py-2 rounded-xl text-xs font-bold transition-all"
               style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.3));border:1px solid rgba(139,92,246,.4);">
                + Start a Chama
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($myChamas as $chama)
            @php
                $myRec = $myMemberRecords[$chama->id] ?? null;
                $statusColors = ['forming'=>'#f59e0b','active'=>'#10b981','dissolved'=>'#6b7280'];
                $statusColor  = $statusColors[$chama->status] ?? '#6b7280';
            @endphp
            <div class="chama-card card-appear rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                {{-- Gradient header --}}
                <div class="relative h-14 flex items-center px-4 gap-2.5 overflow-hidden"
                     style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.25),rgba(15,14,26,.8));">
                    <div class="absolute inset-0 opacity-20" style="background:radial-gradient(circle at 20% 50%,#6366f1,transparent 60%);"></div>
                    <div class="relative"><x-icon name="group" class="w-6 h-6" /></div>
                    <div class="relative flex-1 min-w-0">
                        <p class="font-black text-white truncate leading-tight">{{ $chama->name }}</p>
                        <p class="text-xs text-indigo-300 font-semibold inline-flex items-center gap-1">
                            @if($myRec?->role === 'chairman') <x-icon name="crown" class="w-3 h-3" /> Chairman
                            @elseif($myRec?->role === 'secretary') <x-icon name="clipboard" class="w-3 h-3" /> Secretary
                            @else <x-icon name="user" class="w-3 h-3" /> Member
                            @endif
                        </p>
                    </div>
                    @if($chama->isPrivate())
                    <div class="relative shrink-0 text-xs font-black px-2 py-1 rounded-lg" style="background:rgba(139,92,246,.2);color:#c4b5fd;" title="Private — invite or join code only">🔒</div>
                    @endif
                    <div class="relative shrink-0 text-xs font-black px-2 py-1 rounded-lg"
                         style="background:rgba(0,0,0,.3);color:{{ $statusColor }};">
                        {{ ucfirst($chama->status) }}
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-4 space-y-3">
                    {{-- Pool balance --}}
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="text-gray-400 font-semibold">Pool Balance</span>
                            <span class="font-black text-indigo-300">Ksh {{ number_format($chama->pool_balance) }}</span>
                        </div>
                        @if($chama->target_amount > 0)
                        @php $pct = min(100, round(($chama->pool_balance / $chama->target_amount)*100)); @endphp
                        <div class="pool-bar"><div class="pool-fill" style="width:{{ $pct }}%;"></div></div>
                        <p class="text-xs text-gray-500 mt-1">{{ $pct }}% of Ksh {{ number_format($chama->target_amount) }} goal</p>
                        @endif
                    </div>

                    {{-- Stats row --}}
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl p-2" style="background:rgba(255,255,255,.04);">
                            <p class="text-xs text-gray-500 leading-tight">Members</p>
                            <p class="font-black text-white text-sm">{{ $chama->memberCount() }}/{{ $chama->max_members }}</p>
                        </div>
                        <div class="rounded-xl p-2" style="background:rgba(255,255,255,.04);">
                            <p class="text-xs text-gray-500 leading-tight">Monthly</p>
                            <p class="font-black text-white text-sm">{{ number_format($chama->monthly_contribution) }}</p>
                        </div>
                        <div class="rounded-xl p-2" style="background:rgba(255,255,255,.04);">
                            <p class="text-xs text-gray-500 leading-tight">My Share</p>
                            <p class="font-black text-indigo-300 text-sm">{{ number_format($myRec?->share_pct ?? 0, 1) }}%</p>
                        </div>
                    </div>

                    <a href="{{ route('chama.show', $chama) }}"
                       class="block w-full text-center py-2.5 rounded-xl text-xs font-bold transition-all"
                       style="background:linear-gradient(135deg,rgba(99,102,241,.25),rgba(139,92,246,.2));border:1px solid rgba(139,92,246,.35);">
                        View Chama →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

    {{-- ── Join a private chama by code ── --}}
    <section class="mb-6">
        <div class="rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-3"
             style="background:rgba(139,92,246,.06);border:1px solid rgba(139,92,246,.25);">
            <div class="flex-1">
                <p class="font-black text-white text-xs inline-flex items-center gap-1"><x-icon name="key" class="w-3.5 h-3.5" /> Have a join code?</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Private chamas don't appear below — enter the 6-character code a friend (or your teacher) shared.</p>
            </div>
            <form action="{{ route('chama.join-code') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="code" required maxlength="12" placeholder="e.g. K7M2XP"
                       class="w-32 rounded-lg px-3 py-2 text-xs text-white uppercase tracking-widest placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                       style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                <button type="submit" class="px-4 py-2 rounded-lg text-xs font-black text-white transition-transform hover:scale-[1.02]"
                        style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">Join →</button>
            </form>
        </div>
    </section>

    {{-- ── Section 2: Open Chamas to Join ── --}}
    <section>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-black text-white">Open Chamas to Join</h3>
            <span class="text-xs text-gray-500 font-semibold">{{ $openChamas->count() }} available</span>
        </div>

        @if($openChamas->isEmpty())
        <div class="rounded-2xl text-center py-8 px-5" style="background:rgba(255,255,255,.03);border:1px dashed rgba(255,255,255,.1);">
            <div class="text-3xl mb-2">🔍</div>
            <p class="text-gray-400 font-semibold text-sm">No open chamas right now</p>
            <p class="text-gray-500 text-xs mt-1">Start your own and invite others!</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($openChamas as $chama)
            @php
                $spotsLeft = $chama->max_members - $chama->memberCount();
                $statusColors = ['forming'=>'#f59e0b','active'=>'#10b981'];
                $statusColor  = $statusColors[$chama->status] ?? '#6b7280';
            @endphp
            <div class="chama-card card-appear rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                <div class="relative h-14 flex items-center px-4 gap-2.5 overflow-hidden"
                     style="background:linear-gradient(135deg,rgba(16,185,129,.2),rgba(99,102,241,.15),rgba(15,14,26,.8));">
                    <div class="relative text-2xl">🤝</div>
                    <div class="relative flex-1 min-w-0">
                        <p class="font-black text-white truncate leading-tight">{{ $chama->name }}</p>
                        <p class="text-xs text-gray-400">by {{ $chama->creator->name }}</p>
                    </div>
                    <div class="relative text-xs font-black px-2 py-1 rounded-lg"
                         style="background:rgba(0,0,0,.3);color:{{ $statusColor }};">
                        {{ ucfirst($chama->status) }}
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    @if($chama->goal_text)
                    <p class="text-xs text-gray-400 leading-relaxed line-clamp-2">{{ $chama->goal_text }}</p>
                    @endif

                    @if((int)($chama->min_level ?? 0) > 0 || (int)($chama->min_credit_score ?? 0) > 0 || (int)($chama->min_savings ?? 0) > 0)
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-[10px] text-gray-500 font-bold self-center">Entry:</span>
                        @if((int)($chama->min_level ?? 0) > 0)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full text-amber-300 inline-flex items-center gap-1" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);"><x-icon name="star" class="w-2.5 h-2.5" /> Lvl {{ $chama->min_level }}+</span>
                        @endif
                        @if((int)($chama->min_credit_score ?? 0) > 0)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full text-sky-300 inline-flex items-center gap-1" style="background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.25);"><x-icon name="card" class="w-2.5 h-2.5" /> Credit {{ $chama->min_credit_score }}+</span>
                        @endif
                        @if((int)($chama->min_savings ?? 0) > 0)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full text-emerald-300 inline-flex items-center gap-1" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);"><x-icon name="bank" class="w-2.5 h-2.5" /> Savings {{ number_format($chama->min_savings) }}+</span>
                        @endif
                    </div>
                    @endif

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-xl p-2" style="background:rgba(255,255,255,.04);">
                            <p class="text-xs text-gray-500 leading-tight">Members</p>
                            <p class="font-black text-white text-sm">{{ $chama->memberCount() }}/{{ $chama->max_members }}</p>
                        </div>
                        <div class="rounded-xl p-2" style="background:rgba(255,255,255,.04);">
                            <p class="text-xs text-gray-500 leading-tight">Monthly</p>
                            <p class="font-black text-white text-sm">{{ number_format($chama->monthly_contribution) }}</p>
                        </div>
                        <div class="rounded-xl p-2" style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.15);">
                            <p class="text-xs text-emerald-400 leading-tight">Spots</p>
                            <p class="font-black text-emerald-300 text-sm">{{ $spotsLeft }} left</p>
                        </div>
                    </div>

                    <form action="{{ route('chama.join', $chama) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full py-2.5 rounded-xl text-xs font-bold transition-all"
                                style="background:linear-gradient(135deg,rgba(16,185,129,.25),rgba(16,185,129,.15));border:1px solid rgba(16,185,129,.35);color:#6ee7b7;">
                            Join Chama →
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>

</div>

<x-mobile-bottom-nav active="people" />
</body>
</html>
