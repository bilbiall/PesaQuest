<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $chama->name }} — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        @keyframes popIn    { from{opacity:0;transform:scale(.94) translateY(10px)} to{opacity:1;transform:scale(1) translateY(0)} }
        @keyframes glowpulse{ 0%,100%{opacity:.5} 50%{opacity:1} }
        @keyframes shimmer  { 0%{background-position:200% center} 100%{background-position:-200% center} }
        @keyframes heroNum  { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }

        .tab-pill { cursor:pointer; padding:.5rem 1rem; border-radius:.75rem; font-size:.8rem; font-weight:700;
            color:#9ca3af; border:1px solid transparent; transition:all .2s; white-space:nowrap; }
        .tab-pill:hover { color:#fff; background:rgba(255,255,255,.05); }
        .tab-pill.active { background:linear-gradient(135deg,rgba(99,102,241,.35),rgba(139,92,246,.25));
            border-color:rgba(139,92,246,.5); color:#fff; }

        .glass-card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:1.5rem; }
        .glass-card-inner { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:1rem; }

        .pool-bar { height:8px; border-radius:4px; background:rgba(255,255,255,.08); overflow:hidden; }
        .pool-fill { height:100%; border-radius:4px; background:linear-gradient(90deg,#6366f1,#8b5cf6); transition:width 1s cubic-bezier(.4,0,.2,1); }

        .vote-bar { height:6px; border-radius:3px; overflow:hidden; background:rgba(255,255,255,.08); }
        .vote-yes  { height:100%; border-radius:3px; background:#10b981; }
        .vote-no   { height:100%; border-radius:3px; background:#f87171; }

        .field-input {
            width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            border-radius:.875rem; padding:.65rem .875rem; color:#fff; font-size:.8rem; font-family:'Figtree',sans-serif;
            transition:border-color .2s; outline:none;
        }
        .field-input:focus { border-color:rgba(139,92,246,.5); box-shadow:0 0 0 3px rgba(139,92,246,.1); }
        .field-input::placeholder { color:#4b5563; }

        select.field-input option { background:#1e1b2e; color:#fff; }

        .shimmer-text {
            background:linear-gradient(90deg,#fff 20%,#a78bfa 50%,#fff 80%);
            background-size:200% auto; -webkit-background-clip:text;
            -webkit-text-fill-color:transparent; animation:shimmer 3s linear infinite;
        }

        .badge-chairman { background:rgba(245,158,11,.15); color:#fbbf24; border:1px solid rgba(245,158,11,.25); }
        .badge-secretary { background:rgba(99,102,241,.15); color:#a5b4fc; border:1px solid rgba(99,102,241,.25); }
        .badge-member { background:rgba(255,255,255,.07); color:#9ca3af; border:1px solid rgba(255,255,255,.1); }

        .cat-vehicle    { background:linear-gradient(145deg,#92400e 0%,#b45309 35%,#1c1917 100%); }
        .cat-property   { background:linear-gradient(145deg,#064e3b 0%,#065f46 35%,#0f172a 100%); }
        .cat-business   { background:linear-gradient(145deg,#3730a3 0%,#4c1d95 35%,#1e1b4b 100%); }
        .cat-investment { background:linear-gradient(145deg,#1e3a8a 0%,#1e40af 35%,#0f172a 100%); }
        .cat-gadget     { background:linear-gradient(145deg,#831843 0%,#9d174d 35%,#1a1025 100%); }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- ── Nav ── --}}
<nav class="border-b border-white/5 bg-black/50 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-4">
        <a href="{{ route('chama.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            My Chamas
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="font-black text-base truncate">{{ $chama->name }}</h1>
        </div>
        @php $statusColors = ['forming'=>'#f59e0b','active'=>'#10b981','dissolved'=>'#6b7280']; @endphp
        <div class="shrink-0 text-xs font-black px-2.5 py-1 rounded-lg"
             style="background:rgba(0,0,0,.4);color:{{ $statusColors[$chama->status] ?? '#9ca3af' }};">
            {{ ucfirst($chama->status) }}
        </div>
        <div class="shrink-0 text-xs rounded-xl px-3 py-1.5 font-semibold"
             style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#6ee7b7;">
            Ksh {{ number_format($progress->balance) }}
        </div>
    </div>
</nav>

{{-- ── Flash Messages ── --}}
@if(session('success'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 mt-4">
    <div class="rounded-2xl px-5 py-3 text-sm font-semibold text-emerald-300 flex items-center gap-3"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span> {{ session('success') }}
    </div>
</div>
@endif
@if(session('error'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 mt-4">
    <div class="rounded-2xl px-5 py-3 text-sm font-semibold text-red-300 flex items-center gap-3"
         style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);">
        <span>❌</span> {{ session('error') }}
    </div>
</div>
@endif

{{-- ── Main Alpine Tabs ── --}}
<div x-data="{ activeTab: 'overview', showProposalForm: false, proposalType: 'buy_asset' }" class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    {{-- Tab bar --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-8 scrollbar-hide">
        <button @click="activeTab='overview'"  :class="activeTab==='overview'  ? 'active' : ''" class="tab-pill">Overview</button>
        <button @click="activeTab='members'"   :class="activeTab==='members'   ? 'active' : ''" class="tab-pill">
            Members <span class="ml-1 text-gray-600 font-normal text-xs">{{ $chama->memberCount() }}</span>
        </button>
        <button @click="activeTab='proposals'" :class="activeTab==='proposals' ? 'active' : ''" class="tab-pill">
            Proposals
            @php $activeProps = $chama->proposals->where('status','voting')->count(); @endphp
            @if($activeProps > 0)
            <span class="ml-1 text-xs font-black px-1.5 py-0.5 rounded-full" style="background:rgba(99,102,241,.3);color:#c7d2fe;">{{ $activeProps }}</span>
            @endif
        </button>
        <button @click="activeTab='assets'"    :class="activeTab==='assets'    ? 'active' : ''" class="tab-pill">Assets</button>
        <button @click="activeTab='history'"   :class="activeTab==='history'   ? 'active' : ''" class="tab-pill">History</button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: OVERVIEW --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab==='overview'" x-cloak>

        {{-- Hero pool number --}}
        <div class="glass-card p-6 sm:p-8 mb-6" style="animation:heroNum .5s .05s ease both;">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div>
                    <p class="text-sm text-gray-400 font-semibold uppercase tracking-wider mb-2">Chama Pool Balance</p>
                    <p class="text-5xl sm:text-6xl font-black leading-none">
                        <span class="shimmer-text">Ksh {{ number_format($chama->pool_balance) }}</span>
                    </p>
                    @if($chama->goal_text)
                    <p class="text-gray-400 mt-3 text-sm italic">{{ $chama->goal_text }}</p>
                    @endif
                    @if($chama->target_amount > 0)
                    <div class="mt-4 max-w-sm">
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="text-gray-400">Progress to target</span>
                            <span class="text-indigo-300 font-bold">{{ $targetPct }}% of Ksh {{ number_format($chama->target_amount) }}</span>
                        </div>
                        <div class="pool-bar"><div class="pool-fill" style="width:{{ $targetPct }}%;"></div></div>
                    </div>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3 sm:w-64 shrink-0">
                    <div class="glass-card-inner p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Members</p>
                        <p class="text-xl font-black text-white">{{ $chama->memberCount() }}/{{ $chama->max_members }}</p>
                    </div>
                    <div class="glass-card-inner p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Monthly In</p>
                        <p class="text-xl font-black text-indigo-300">Ksh {{ number_format($chama->monthly_contribution) }}</p>
                    </div>
                    <div class="glass-card-inner p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Asset Income</p>
                        <p class="text-xl font-black text-emerald-400">Ksh {{ number_format($monthlyIncome) }}</p>
                    </div>
                    <div class="glass-card-inner p-4 text-center">
                        <p class="text-xs text-gray-500 mb-1">Total Value</p>
                        <p class="text-xl font-black text-violet-300">Ksh {{ number_format($chama->totalValue()) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contribute this month --}}
        @if($myMember)
        <div class="glass-card p-5 mb-6">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <p class="font-black text-white">Monthly Contribution</p>
                    <p class="text-sm text-gray-400 mt-0.5">
                        @php
                            $gameMonthLabel = str_starts_with($gameMonth, 'GM-')
                                ? 'Game Month ' . ((int) substr($gameMonth, 3) + 1)
                                : $gameMonth;
                        @endphp
                        @if($hasContributedThisMonth)
                            You've contributed for {{ $gameMonthLabel }}.
                        @else
                            Ksh {{ number_format($chama->monthly_contribution) }} due for {{ $gameMonthLabel }}
                        @endif
                    </p>
                </div>
                @if(!$hasContributedThisMonth)
                <form action="{{ route('chama.contribute', $chama) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-6 py-3 rounded-2xl text-sm font-bold transition-all"
                            style="background:linear-gradient(135deg,rgba(16,185,129,.3),rgba(16,185,129,.2));border:1px solid rgba(16,185,129,.4);color:#6ee7b7;">
                        💰 Contribute Ksh {{ number_format($chama->monthly_contribution) }}
                    </button>
                </form>
                @else
                <div class="flex items-center gap-2 text-sm font-bold text-emerald-400">
                    <span class="text-base">✅</span> Paid this game month
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- My share card --}}
        @if($myMember)
        <div class="glass-card p-5 mb-6">
            <p class="font-bold text-gray-300 text-sm mb-4">My Position</p>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="glass-card-inner p-3">
                    <p class="text-xs text-gray-500 mb-1">My Share</p>
                    <p class="text-2xl font-black text-indigo-300">{{ number_format($myMember->share_pct, 1) }}%</p>
                </div>
                <div class="glass-card-inner p-3">
                    <p class="text-xs text-gray-500 mb-1">Contributed</p>
                    <p class="text-xl font-black text-white">Ksh {{ number_format($myMember->total_contributed) }}</p>
                </div>
                <div class="glass-card-inner p-3">
                    <p class="text-xs text-gray-500 mb-1">Pool Value</p>
                    <p class="text-xl font-black text-violet-300">
                        Ksh {{ number_format($chama->pool_balance * ($myMember->share_pct / 100)) }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Description --}}
        @if($chama->description)
        <div class="glass-card p-5">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">About this Chama</p>
            <p class="text-gray-300 text-sm leading-relaxed">{{ $chama->description }}</p>
        </div>
        @endif

        {{-- Leave chama --}}
        @if($myMember)
        <div class="mt-6 pt-6 border-t border-white/5">
            <form action="{{ route('chama.leave', $chama) }}" method="POST"
                  onsubmit="return confirm('Leave this chama? A 10% exit penalty on your contributed amount will apply.');">
                @csrf
                <button type="submit" class="text-xs text-gray-600 hover:text-red-400 transition-colors font-semibold">
                    Leave chama (10% exit penalty applies)
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: MEMBERS --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab==='members'" x-cloak>

        {{-- Private chama: join code (visible to all members — share it!) --}}
        @if($myMember && $chama->isPrivate() && $chama->join_code)
        <div class="glass-card p-5 mb-6 flex flex-wrap items-center justify-between gap-4"
             style="border:1px solid rgba(139,92,246,.35);">
            <div class="flex-1 min-w-0">
                <p class="font-black text-white text-sm">🔒 Private chama · Join code</p>
                <p class="text-xs text-gray-400 mt-0.5">Anyone with this code can join from the Chamas page (spots permitting). Great for writing on a classroom board.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-black tracking-[.3em] text-violet-300 px-4 py-2 rounded-xl" style="background:rgba(139,92,246,.12);border:1px dashed rgba(139,92,246,.45);">{{ $chama->join_code }}</span>
                <button onclick="navigator.clipboard.writeText('{{ $chama->join_code }}').then(() => { this.textContent = '✅'; setTimeout(() => this.textContent = '📋', 1500); })"
                        class="text-lg hover:scale-110 transition-transform" title="Copy code">📋</button>
            </div>
        </div>
        @endif

        {{-- Invite friends directly (bell + push notification with the link) --}}
        @if($myMember && !$chama->isFull() && ($invitableFriends ?? collect())->isNotEmpty())
        <div class="glass-card p-5 mb-6">
            <p class="font-black text-white text-sm mb-1">👥 Invite your friends</p>
            <p class="text-xs text-gray-400 mb-3">They get a notification with a one-tap invite — no link sharing needed.</p>
            <div class="flex flex-wrap gap-2">
                @foreach($invitableFriends as $friend)
                <form action="{{ route('chama.invite.friend', $chama) }}" method="POST">
                    @csrf
                    <input type="hidden" name="friend_id" value="{{ $friend->id }}">
                    <button type="submit" class="flex items-center gap-1.5 text-[11px] font-black px-3 py-1.5 rounded-xl text-gray-200 transition-all hover:scale-[1.03]"
                            style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);">
                        @if($friend->profile_photo)<img src="{{ $friend->profile_photo }}" class="w-4 h-4 rounded-full object-cover" alt="">@endif
                        {{ $friend->name }} <span class="text-indigo-300">+ Invite</span>
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Invite link card --}}
        @if($myMember && !$chama->isFull())
        <div class="glass-card p-5 mb-6"
             x-data="{ generating: false, inviteUrl: '', copied: false, error: '' }">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex-1 min-w-0">
                    <p class="font-black text-white text-sm">Invite a Friend</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ max(0, $chama->max_members - $chama->memberCount()) }} spot{{ ($chama->max_members - $chama->memberCount()) !== 1 ? 's' : '' }} remaining.
                        Share a 7-day invite link with anyone.
                    </p>
                </div>
                <button
                    @click="
                        generating = true; error = '';
                        fetch('{{ route('chama.invite.generate', $chama) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                            }
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.url) { inviteUrl = d.url; }
                            else { error = d.error || 'Could not generate link.'; }
                            generating = false;
                        })
                        .catch(() => { error = 'Network error.'; generating = false; });
                    "
                    :disabled="generating"
                    class="shrink-0 px-4 py-2.5 rounded-2xl text-xs font-bold transition-all"
                    style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.25));border:1px solid rgba(139,92,246,.4);">
                    <span x-show="!generating">🔗 Generate Invite Link</span>
                    <span x-show="generating">Generating…</span>
                </button>
            </div>

            {{-- Error --}}
            <div x-show="error" class="mt-3 text-xs text-red-400 font-semibold" x-text="error"></div>

            {{-- Generated link --}}
            <div x-show="inviteUrl" x-transition class="mt-4">
                <div class="flex items-center gap-2">
                    <input type="text" :value="inviteUrl" readonly
                           class="flex-1 rounded-xl px-3 py-2.5 text-xs text-gray-300 select-all"
                           style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);"
                           @click="$el.select()">
                    <button
                        @click="navigator.clipboard.writeText(inviteUrl).then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                        class="shrink-0 px-3 py-2.5 rounded-xl text-xs font-bold transition-all"
                        :style="copied ? 'background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;' : 'background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#9ca3af;'">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied">✅ Copied!</span>
                    </button>
                </div>
                <p class="text-xs text-gray-600 mt-1.5">Link valid for 7 days · Anyone with this link can join if spots remain.</p>
            </div>
        </div>
        @endif

        {{-- Chairman: distribute button --}}
        @if($isChairman && $monthlyIncome > 0)
        <div class="glass-card p-5 mb-6 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="font-black text-white">Asset Income Ready</p>
                <p class="text-sm text-gray-400">Ksh {{ number_format($monthlyIncome) }}/month from {{ $chama->chamaAssets->count() }} asset(s)</p>
            </div>
            <form action="{{ route('chama.distribute', $chama) }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-6 py-3 rounded-2xl text-sm font-bold transition-all"
                        style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.25));border:1px solid rgba(139,92,246,.4);">
                    💸 Distribute Income to Members
                </button>
            </form>
        </div>
        @endif

        {{-- Members table --}}
        <div class="glass-card overflow-hidden">
            <div class="p-5 border-b border-white/5">
                <h3 class="font-black text-white">{{ $chama->memberCount() }} Active Members</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider px-5 py-3">Member</th>
                            <th class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider px-3 py-3">Role</th>
                            <th class="text-right text-xs text-gray-500 font-semibold uppercase tracking-wider px-3 py-3">Contributed</th>
                            <th class="text-right text-xs text-gray-500 font-semibold uppercase tracking-wider px-3 py-3">Share %</th>
                            <th class="text-left text-xs text-gray-500 font-semibold uppercase tracking-wider px-3 py-3 hidden sm:table-cell">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chama->activeMembers->sortByDesc('total_contributed') as $mem)
                        <tr class="border-b border-white/5 hover:bg-white/2 transition-colors {{ $mem->user_id === $user->id ? 'bg-indigo-500/5' : '' }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm shrink-0"
                                         style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.25));">
                                        {{ strtoupper(substr($mem->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-white text-sm leading-tight">
                                            {{ $mem->user->name }}
                                            @if($mem->user_id === $user->id)
                                            <span class="text-xs text-indigo-400">(you)</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-4">
                                @if($mem->role === 'chairman')
                                <span class="text-xs font-bold px-2 py-1 rounded-lg badge-chairman">👑 Chairman</span>
                                @elseif($mem->role === 'secretary')
                                <span class="text-xs font-bold px-2 py-1 rounded-lg badge-secretary">📋 Secretary</span>
                                @else
                                <span class="text-xs font-bold px-2 py-1 rounded-lg badge-member">👤 Member</span>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-right">
                                <span class="font-black text-white text-sm">Ksh {{ number_format($mem->total_contributed) }}</span>
                            </td>
                            <td class="px-3 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 h-1.5 rounded-full" style="background:rgba(255,255,255,.08);">
                                        <div class="h-full rounded-full" style="width:{{ min(100, $mem->share_pct) }}%;background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
                                    </div>
                                    <span class="font-black text-indigo-300 text-sm w-12 text-right">{{ number_format($mem->share_pct, 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-3 py-4 hidden sm:table-cell">
                                <span class="text-xs text-gray-500">{{ $mem->joined_at?->format('d M Y') ?? 'N/A' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: PROPOSALS --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab==='proposals'" x-cloak>

        {{-- New proposal toggle --}}
        @if($myMember)
        <div class="mb-6">
            <button @click="showProposalForm = !showProposalForm"
                    class="flex items-center gap-2 px-5 py-3 rounded-2xl text-sm font-bold transition-all"
                    style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);">
                <span x-text="showProposalForm ? '✕ Cancel' : '+ New Proposal'"></span>
            </button>
        </div>

        {{-- Proposal form --}}
        <div x-show="showProposalForm" x-cloak class="glass-card p-6 mb-8">
            <h3 class="font-black text-white mb-5">Create Proposal</h3>
            <form action="{{ route('chama.propose', $chama) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Proposal Type</label>
                    <select name="type" x-model="proposalType" class="field-input">
                        <option value="buy_asset">Buy Asset</option>
                        <option value="sell_asset">Sell Asset</option>
                        <option value="change_contribution">Change Contribution</option>
                        <option value="remove_member">Remove Member</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Title</label>
                    <input type="text" name="title" class="field-input" placeholder="Proposal title..." maxlength="120" required>
                </div>

                {{-- Buy asset fields --}}
                <div x-show="proposalType === 'buy_asset'" class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Asset</label>
                        <select name="proposal_data[asset_id]" class="field-input">
                            <option value="">Select an asset...</option>
                            @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->icon ?? '' }} {{ $asset->name }} — Ksh {{ number_format($asset->base_price) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Quantity</label>
                        <input type="number" name="proposal_data[quantity]" class="field-input" value="1" min="1" max="10">
                    </div>
                </div>

                {{-- Sell asset fields --}}
                <div x-show="proposalType === 'sell_asset'">
                    <label class="block text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Chama Asset to Sell</label>
                    <select name="proposal_data[chama_asset_id]" class="field-input">
                        <option value="">Select asset...</option>
                        @foreach($chama->chamaAssets as $ca)
                        <option value="{{ $ca->id }}">{{ $ca->asset->name ?? 'Asset' }} ({{ $ca->quantity }}x)</option>
                        @endforeach
                    </select>
                </div>

                {{-- Change contribution fields --}}
                <div x-show="proposalType === 'change_contribution'">
                    <label class="block text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">New Monthly Contribution (Ksh)</label>
                    <input type="number" name="proposal_data[new_amount]" class="field-input" placeholder="e.g. 3000" min="500">
                </div>

                {{-- Remove member fields --}}
                <div x-show="proposalType === 'remove_member'">
                    <label class="block text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Member to Remove</label>
                    <select name="proposal_data[user_id]" class="field-input">
                        <option value="">Select member...</option>
                        @foreach($chama->activeMembers as $mem)
                        @if($mem->user_id !== $user->id)
                        <option value="{{ $mem->user_id }}">{{ $mem->user->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="w-full py-3 rounded-2xl text-sm font-bold transition-all mt-2"
                        style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.2));border:1px solid rgba(139,92,246,.4);">
                    Submit Proposal (7-day voting window)
                </button>
            </form>
        </div>
        @endif

        {{-- Active proposals --}}
        @php $activeProposals = $chama->proposals->where('status', 'voting')->sortByDesc('created_at'); @endphp
        @if($activeProposals->count() > 0)
        <div class="mb-8">
            <h3 class="font-black text-white mb-4">Active Voting</h3>
            <div class="space-y-4">
                @foreach($activeProposals as $prop)
                @php
                    $totalVotes     = $prop->votes_yes + $prop->votes_no;
                    $activeMCount   = $chama->memberCount();
                    $yesPct         = $totalVotes > 0 ? round(($prop->votes_yes / $totalVotes)*100) : 0;
                    $noPct          = $totalVotes > 0 ? round(($prop->votes_no  / $totalVotes)*100) : 0;
                    $userVoted      = $prop->userVoted($user->id);
                    $userVoteVal    = $prop->userVoteValue($user->id);
                    $isExpired      = $prop->isExpired();
                    $quorumPct      = $activeMCount > 0 ? round(($prop->votes_yes / $activeMCount)*100) : 0;
                @endphp
                <div class="glass-card p-5">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-black px-2 py-0.5 rounded-lg"
                                      style="background:rgba(99,102,241,.2);color:#a5b4fc;">
                                    {{ $prop->typeLabel() }}
                                </span>
                                @if($isExpired)
                                <span class="text-xs font-bold text-red-400">Expired</span>
                                @else
                                @php $propGameDays = app(\App\Services\GameClock::class)->gameDaysUntil($prop->expires_at); @endphp
                                <span class="text-xs text-gray-500">
                                    {{ $propGameDays > 0 ? 'Expires in ' . $propGameDays . ' game day' . ($propGameDays === 1 ? '' : 's') : 'Expired' }}
                                </span>
                                @endif
                            </div>
                            <p class="font-black text-white">{{ $prop->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">by {{ $prop->proposer->name }}</p>
                        </div>
                    </div>

                    {{-- Vote bars --}}
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-emerald-400 font-bold w-8">Yes</span>
                            <div class="vote-bar flex-1"><div class="vote-yes" style="width:{{ $yesPct }}%;"></div></div>
                            <span class="text-emerald-400 font-black w-10 text-right">{{ $prop->votes_yes }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-red-400 font-bold w-8">No</span>
                            <div class="vote-bar flex-1"><div class="vote-no" style="width:{{ $noPct }}%;"></div></div>
                            <span class="text-red-400 font-black w-10 text-right">{{ $prop->votes_no }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">
                        {{ $totalVotes }} of {{ $activeMCount }} voted — need >50% yes ({{ $quorumPct }}% reached)
                    </p>

                    {{-- Vote / Execute buttons --}}
                    @if($myMember && !$isExpired)
                        @if(!$userVoted)
                        <div class="flex gap-2">
                            <form action="{{ route('chama.vote', $prop) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="vote" value="yes">
                                <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold transition-all"
                                        style="background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;">
                                    ✅ Vote Yes
                                </button>
                            </form>
                            <form action="{{ route('chama.vote', $prop) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="vote" value="no">
                                <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold transition-all"
                                        style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;">
                                    ❌ Vote No
                                </button>
                            </form>
                        </div>
                        @else
                        <div class="text-xs font-bold py-2 text-center rounded-xl"
                             style="background:rgba(255,255,255,.05);">
                            You voted <span class="{{ $userVoteVal === 'yes' ? 'text-emerald-400' : 'text-red-400' }}">{{ strtoupper($userVoteVal) }}</span>
                        </div>
                        @endif
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Passed proposals awaiting execution --}}
        @php $passedProposals = $chama->proposals->where('status', 'passed'); @endphp
        @if($passedProposals->count() > 0)
        <div class="mb-8">
            <h3 class="font-black text-white mb-4">Passed — Awaiting Execution</h3>
            <div class="space-y-3">
                @foreach($passedProposals as $prop)
                <div class="glass-card p-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-white text-sm">{{ $prop->title }}</p>
                        <p class="text-xs text-emerald-400 mt-0.5">✅ Passed ({{ $prop->votes_yes }} yes / {{ $prop->votes_no }} no)</p>
                    </div>
                    @if($myMember)
                    <form action="{{ route('chama.execute', $prop) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                                style="background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;">
                            Execute
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Closed proposals --}}
        @php $closedProposals = $chama->proposals->whereIn('status', ['rejected', 'executed'])->sortByDesc('updated_at'); @endphp
        @if($closedProposals->count() > 0)
        <div x-data="{ showClosed: false }">
            <button @click="showClosed = !showClosed"
                    class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-300 transition-colors font-semibold mb-3">
                <span x-text="showClosed ? '▲' : '▼'"></span>
                Closed Proposals ({{ $closedProposals->count() }})
            </button>
            <div x-show="showClosed" x-cloak class="space-y-2">
                @foreach($closedProposals as $prop)
                <div class="glass-card-inner p-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-300">{{ $prop->title }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $prop->typeLabel() }} · {{ $prop->updated_at->format('d M Y') }}</p>
                    </div>
                    <span class="text-xs font-black px-2 py-1 rounded-lg"
                          style="background:rgba(0,0,0,.3);color:{{ $prop->statusColor() }};">
                        {{ ucfirst($prop->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($chama->proposals->isEmpty())
        <div class="text-center py-12 rounded-3xl" style="background:rgba(255,255,255,.02);border:1px dashed rgba(255,255,255,.08);">
            <div class="text-4xl mb-3">🗳️</div>
            <p class="text-gray-400 font-semibold">No proposals yet</p>
            <p class="text-gray-600 text-sm mt-1">Create the first proposal for this chama.</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: ASSETS --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab==='assets'" x-cloak>

        {{-- Total income header --}}
        @if($monthlyIncome > 0)
        <div class="glass-card p-5 mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Total Monthly Income</p>
                <p class="text-3xl font-black text-emerald-400">Ksh {{ number_format($monthlyIncome) }}/mo</p>
            </div>
            <div class="text-4xl" style="animation:glowpulse 3s ease-in-out infinite;">📊</div>
        </div>
        @endif

        {{-- Asset grid --}}
        @if($chama->chamaAssets->isEmpty())
        <div class="text-center py-16 rounded-3xl" style="background:rgba(255,255,255,.02);border:1px dashed rgba(255,255,255,.08);">
            <div class="text-5xl mb-4">🏢</div>
            <p class="text-lg font-black text-gray-300">No assets yet</p>
            <p class="text-gray-500 text-sm mt-2 mb-6">Create a proposal to buy assets for the chama.</p>
            @if($myMember)
            <button @click="activeTab='proposals'; showProposalForm=true; proposalType='buy_asset'"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-bold transition-all"
                    style="background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(139,92,246,.25));border:1px solid rgba(139,92,246,.4);">
                Propose Asset Purchase
            </button>
            @endif
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
            @foreach($chama->chamaAssets as $ca)
            @php
                $asset   = $ca->asset;
                $catClass = 'cat-' . ($asset->category ?? 'business');
                $netMonth = (($asset->monthly_income ?? 0) - ($asset->monthly_cost ?? 0)) * $ca->quantity;
            @endphp
            <div class="rounded-3xl overflow-hidden" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                {{-- Category header --}}
                <div class="relative h-20 flex items-center px-5 gap-3 overflow-hidden {{ $catClass }}">
                    <div class="text-3xl" style="text-shadow:0 2px 8px rgba(0,0,0,.5);">{{ $asset->icon ?? '🏢' }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-white truncate leading-tight text-sm">{{ $asset->name }}</p>
                        <p class="text-xs text-white/60">{{ ucfirst($asset->category ?? 'asset') }} · Qty {{ $ca->quantity }}</p>
                    </div>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="rounded-xl p-2.5" style="background:rgba(255,255,255,.04);">
                            <p class="text-xs text-gray-500">Paid</p>
                            <p class="font-black text-white text-sm">Ksh {{ number_format($ca->purchase_price * $ca->quantity) }}</p>
                        </div>
                        <div class="rounded-xl p-2.5" style="background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.15);">
                            <p class="text-xs text-emerald-400">Monthly Net</p>
                            <p class="font-black text-sm {{ $netMonth >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $netMonth >= 0 ? '+' : '' }}Ksh {{ number_format($netMonth) }}
                            </p>
                        </div>
                    </div>
                    @if($ca->purchased_at)
                    <p class="text-xs text-gray-600 text-center">Acquired {{ $ca->purchased_at->format('d M Y') }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Propose another purchase --}}
        @if($myMember)
        <button @click="activeTab='proposals'; showProposalForm=true; proposalType='buy_asset'"
                class="flex items-center gap-2 px-5 py-3 rounded-2xl text-sm font-bold transition-all"
                style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.25);">
            + Propose Another Asset Purchase
        </button>
        @endif
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- TAB: HISTORY --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div x-show="activeTab==='history'" x-cloak>

        @php
            // Merge contributions and proposals into a unified timeline
            $timeline = collect();

            foreach($allContributions as $c) {
                $timeline->push([
                    'time' => $c->created_at,
                    'type' => 'contribution',
                    'icon' => '💰',
                    'color' => '#10b981',
                    'title' => $c->user->name . ' contributed Ksh ' . number_format($c->amount),
                    'sub'   => (str_starts_with($c->game_month, 'GM-') ? 'Game Month ' . ((int) substr($c->game_month, 3) + 1) : $c->game_month) . ' · ' . ucfirst($c->status),
                ]);
            }

            foreach($chama->proposals as $p) {
                if($p->status !== 'voting') {
                    $timeline->push([
                        'time' => $p->updated_at,
                        'type' => 'proposal',
                        'icon' => $p->status === 'executed' ? '✅' : ($p->status === 'passed' ? '🗳️' : '❌'),
                        'color' => $p->statusColor(),
                        'title' => $p->title,
                        'sub'   => $p->typeLabel() . ' · ' . ucfirst($p->status),
                    ]);
                }
            }

            $timeline = $timeline->sortByDesc('time');
        @endphp

        @if($timeline->isEmpty())
        <div class="text-center py-16 rounded-3xl" style="background:rgba(255,255,255,.02);border:1px dashed rgba(255,255,255,.08);">
            <div class="text-4xl mb-3">📜</div>
            <p class="text-gray-400 font-semibold">No activity yet</p>
            <p class="text-gray-600 text-sm mt-1">History will appear here as contributions and proposals are made.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($timeline as $item)
            <div class="glass-card p-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
                     style="background:rgba(0,0,0,.3);">
                    {{ $item['icon'] }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-white text-sm leading-tight">{{ $item['title'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['sub'] }}</p>
                </div>
                <span class="text-xs text-gray-600 shrink-0">{{ $item['time']->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>{{-- /x-data --}}

<x-mobile-bottom-nav active="people" />
</body>
</html>
