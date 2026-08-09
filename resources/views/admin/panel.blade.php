<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel – PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0a0a12; }
        .panel { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); }
        .stat-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 1.25rem 1.5rem; }
        .input-field { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.75rem; color: white; padding: 0.5rem 0.875rem; font-size: 0.875rem; transition: border-color 0.2s; width: 100%; }
        .input-field:focus { outline: none; border-color: rgba(99,102,241,0.6); }
        select.input-field option { background: #1a1a2e; }
        .badge-pill { font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 9999px; letter-spacing: 0.05em; text-transform: uppercase; }
        .badge-admin   { background: rgba(239,68,68,0.15);   color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .badge-gameset { background: rgba(99,102,241,0.15);  color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
        .badge-player  { background: rgba(16,185,129,0.1);   color: #6ee7b7; border: 1px solid rgba(16,185,129,0.2); }
        .badge-sub     { background: rgba(245,158,11,0.15);  color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
        .badge-pending { background: rgba(99,102,241,0.15);  color: #a5b4fc; border: 1px solid rgba(99,102,241,0.3); }
        .badge-failed  { background: rgba(239,68,68,0.12);   color: #f87171; border: 1px solid rgba(239,68,68,0.25); }
        .toggle-btn { font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 0.5rem; transition: all 0.2s; cursor: pointer; }
        .toggle-on  { background: rgba(99,102,241,0.2); border: 1px solid rgba(99,102,241,0.4); color: #a5b4fc; }
        .toggle-on:hover { background: rgba(99,102,241,0.35); }
        .toggle-off { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); color: #6b7280; }
        .toggle-off:hover { border-color: rgba(99,102,241,0.3); color: #a5b4fc; }
        .sub-active { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); color: #fbbf24; }
        .sub-active:hover { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.3); color: #f87171; }
        .sub-none { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); color: #6b7280; }
        .sub-none:hover { border-color: rgba(245,158,11,0.3); color: #fbbf24; }
        tr:hover td { background: rgba(255,255,255,0.015); }
        .tab-active   { background: rgba(99,102,241,0.2); border-bottom: 2px solid #6366f1; color: white; }
        .tab-inactive { color: #6b7280; border-bottom: 2px solid transparent; }
        .tab-inactive:hover { color: #a5b4fc; }
        [x-cloak] { display: none !important; }
        .modal-backdrop { background: rgba(0,0,0,0.75); backdrop-filter: blur(6px); }
        .modal-box { background: #12111f; border: 1px solid rgba(99,102,241,0.25); border-radius: 1.5rem; max-width: 460px; width: 100%; }
        .settings-section { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 1rem; padding: 1.5rem; }
        .plan-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 1rem; }
        .plan-card.featured { border-color: rgba(245,158,11,0.35); background: rgba(245,158,11,0.04); }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased" x-data="adminPanel()" x-cloak>

    {{-- ── Header ── --}}
    <header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm mr-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Dashboard
                </a>
                <img src="{{ asset('moski-logo.png') }}" alt="Moski" class="h-11 w-auto rounded-xl object-cover">
                <div>
                    <h1 class="text-white font-bold text-lg leading-none">Admin Panel</h1>
                    <p class="text-gray-500 text-xs mt-0.5">PesaQuest Platform</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->is_gameset || auth()->user()->is_admin)
                <a href="{{ route('gameset.index') }}"
                   class="hidden sm:flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-600/30 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Scenario Builder
                </a>
                @endif
                <a href="{{ route('subscribe.index') }}"
                   class="hidden sm:flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-xl bg-amber-600/20 border border-amber-500/30 text-amber-300 hover:bg-amber-600/30 transition-all">
                    💳 Plans Page
                </a>
                <a href="{{ route('admin.analytics') }}"
                   class="hidden sm:flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-xl bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30 transition-all">
                    📊 Analytics
                </a>
                <a href="{{ route('admin.docs') }}"
                   class="hidden sm:flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-600/30 transition-all">
                    📚 Guide
                </a>
                <span class="text-sm text-gray-400">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            @foreach([
                ['Total Users',    $stats['total_users'],        'text-white'],
                ['Total XP',       number_format($stats['total_points']),  'text-indigo-400'],
                ['Active Today',   $stats['active_today'],       'text-emerald-400'],
                ['Badges Given',   $stats['badges_awarded'],     'text-amber-400'],
                ['Subscribers',    $stats['active_subscribers'], 'text-purple-400'],
                ['Pending Pays',   $stats['pending_payments'],   'text-orange-400'],
            ] as [$label, $val, $color])
            <div class="stat-card">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">{{ $label }}</p>
                <p class="text-3xl font-extrabold {{ $color }}">{{ $val }}</p>
            </div>
            @endforeach
        </div>

        {{-- Tab Nav --}}
        <div class="flex gap-1 border-b border-white/10 overflow-x-auto">
            @foreach([
                ['users',         'Users & Roles'],
                ['subscriptions', 'Subscriptions'],
                ['schools',       '🏫 Schools'],
                ['plans',         'Plans & Pricing'],
                ['coupons',       '🎟️ Coupons'],
                ['sponsors',      '🏆 Sponsors'],
                ['payments',      'M-Pesa Payments'],
                ['crises',        '🌪 Crises'],
                ['broadcast',     '📢 Broadcast'],
                ['lifesim',       '🎮 Life Sim'],
                ['inbox',         '📬 Inbox'],
                ['settings',      'Settings'],
                ['activity',      'Activity'],
                ['roadmap',       '🗺 Roadmap'],
                ['artisan',       '🛠 Artisan'],
            ] as [$key, $label])
            <button @click="activeTab='{{ $key }}'"
                    :class="activeTab==='{{ $key }}' ? 'tab-active' : 'tab-inactive'"
                    class="px-4 py-3 text-sm font-semibold transition-all whitespace-nowrap flex-shrink-0">
                {{ $label }}
                @if($key === 'payments' && $stats['pending_payments'] > 0)
                <span class="ml-1 bg-orange-500/20 text-orange-400 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $stats['pending_payments'] }}</span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ════════════════════════════════════════
             TAB: USERS & ROLES
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='users'">
            <div class="panel rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-bold text-white text-base">All Users ({{ $users->count() }})</h2>
                    <div class="flex items-center gap-2">
                        {{-- type=search + autocomplete=off + readonly-until-focus stops Chrome/password managers
                             autofilling the saved admin login (admin@moski.org) into this filter box --}}
                        <input type="search" x-model="search" placeholder="Search name or email…" class="input-field" style="max-width:200px;"
                               name="admin_user_filter" autocomplete="off" autocapitalize="off" spellcheck="false"
                               data-lpignore="true" data-1p-ignore data-form-type="other"
                               readonly onfocus="this.removeAttribute('readonly')" />
                        <button @click="openCreateUser()"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New User
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5 text-left">
                                <th class="px-6 py-3 text-xs text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Age</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Level / XP</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Subscription</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Roles</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Last Active</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @foreach($users as $user)
                            @php $sub = $user->subscription; $hasSub = $sub && $sub->isActive(); @endphp
                            <tr x-show="matchesSearch('{{ addslashes($user->name) }}','{{ addslashes($user->email) }}')"
                                data-user-id="{{ $user->id }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $user->is_admin ? 'bg-red-500/20 text-red-400' : ($user->is_gameset ? 'bg-indigo-500/20 text-indigo-400' : 'bg-emerald-500/10 text-emerald-400') }}">
                                            {{ strtoupper(substr($user->name,0,1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white leading-none">{{ $user->name }}</p>
                                            <p class="text-gray-500 text-xs mt-0.5">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-400 text-xs font-mono">{{ $user->age_group ?? '—' }}</td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-white text-sm">Lv {{ $user->progress?->level ?? 1 }}</div>
                                    <div class="text-xs text-indigo-300">{{ number_format($user->progress?->points_total ?? 0) }} XP</div>
                                </td>
                                <td class="px-4 py-4">
                                    @if($hasSub)
                                        <span class="badge-pill badge-sub">{{ ucfirst($sub->plan) }}</span>
                                        <div class="text-[10px] text-gray-500 mt-1">Until {{ $sub->ends_at?->format('d M Y') }}</div>
                                    @else
                                        <span class="text-xs text-gray-600">No subscription</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if($user->is_admin)  <span class="badge-pill badge-admin">Admin</span> @endif
                                        @if($user->is_gameset)<span class="badge-pill badge-gameset">GameSet</span>@endif
                                        @if(!$user->is_admin && !$user->is_gameset)<span class="badge-pill badge-player">Player</span>@endif
                                        @if($usersHaveActiveColumn && !$user->is_active)<span class="badge-pill badge-failed">Deactivated</span>@endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-500 text-xs">{{ $user->progress?->last_played_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="px-4 py-4">
                                    @if($user->id !== auth()->id())
                                    <div class="flex flex-wrap gap-1.5">
                                        <button @click="toggleRole({{ $user->id }},'gameset',$el)"
                                                class="toggle-btn {{ $user->is_gameset ? 'toggle-on' : 'toggle-off' }}"
                                                data-active="{{ $user->is_gameset ? '1' : '0' }}">GameSet</button>
                                        <button @click="toggleRole({{ $user->id }},'admin',$el)"
                                                class="toggle-btn {{ $user->is_admin ? 'toggle-on' : 'toggle-off' }}"
                                                data-active="{{ $user->is_admin ? '1' : '0' }}">Admin</button>
                                        <button @click="openSubscribeModal({{ $user->id }},'{{ addslashes($user->name) }}',{{ $hasSub ? 'true' : 'false' }})"
                                                class="toggle-btn {{ $hasSub ? 'sub-active' : 'sub-none' }}">
                                            {{ $hasSub ? 'Sub ✓' : 'Subscribe' }}</button>
                                        <button @click="resetPassword({{ $user->id }},'{{ addslashes($user->name) }}')"
                                                class="toggle-btn toggle-off" title="Reset password">🔑 Reset PW</button>
                                        @if($usersHaveActiveColumn)
                                        <button @click="toggleActive({{ $user->id }},'{{ addslashes($user->name) }}',{{ $user->is_active ? 'true' : 'false' }},$el)"
                                                class="toggle-btn {{ $user->is_active ? 'toggle-off' : 'sub-active' }}"
                                                title="{{ $user->is_active ? 'Block this account from logging in' : 'Restore login access' }}">
                                            {{ $user->is_active ? '⏸️ Deactivate' : '▶️ Reactivate' }}
                                        </button>
                                        @endif
                                        @if(!$user->is_admin)
                                        <button @click="deleteUser({{ $user->id }},'{{ addslashes($user->name) }}')"
                                                class="toggle-btn" style="background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.3);color:#fca5a5;"
                                                title="Permanently delete this account and all its data">🗑️ Delete</button>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-xs text-gray-600 italic">You</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: SUBSCRIPTIONS
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='subscriptions'">

            {{-- ── Quick Grant Subscription ── --}}
            <div class="panel rounded-2xl p-5 mb-5" x-data="quickGrantForm()">
                <h3 class="font-bold text-white text-sm mb-3 flex items-center gap-2">
                    <span class="text-lg">⚡</span> Grant Subscription to a Player
                </h3>
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[180px]">
                        <label class="text-xs text-gray-500 block mb-1">Player</label>
                        <select x-model="userId" class="input-field w-full text-sm">
                            <option value="">— select player —</option>
                            @foreach($users as $u)
                            @if(!$u->is_admin)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[130px]">
                        <label class="text-xs text-gray-500 block mb-1">Plan</label>
                        <select x-model="plan" class="input-field w-full text-sm">
                            @foreach($plans as $p)
                            <option value="{{ $p->key }}">{{ $p->name }} — Ksh {{ number_format($p->price_kes) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="text-xs text-gray-500 block mb-1">Reference (optional)</label>
                        <input type="text" x-model="reference" placeholder="e.g. MPESA-XXXXXX" class="input-field w-full text-sm">
                    </div>
                    <div class="flex items-end">
                        <button @click="grant()" :disabled="!userId || saving"
                                class="px-5 py-2 rounded-xl text-sm font-bold transition-all"
                                style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#000;">
                            <span x-show="!saving">Grant →</span>
                            <span x-show="saving">Granting…</span>
                        </button>
                    </div>
                </div>
                <div x-show="msg" x-transition class="mt-3 text-xs font-semibold" :class="ok ? 'text-green-400' : 'text-red-400'" x-text="msg"></div>
            </div>

            <div class="panel rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5 flex flex-wrap items-center gap-4">
                    <h2 class="font-bold text-white text-base flex-1">All Subscriptions</h2>
                    <div class="flex gap-2 text-xs font-bold">
                        @foreach(['all','active','pending','cancelled','expired'] as $f)
                        <button @click="subFilter='{{ $f }}'"
                                :class="subFilter==='{{ $f }}' ? 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40' : 'text-gray-500 border-white/10'"
                                class="px-3 py-1 rounded-full border transition-colors">{{ ucfirst($f) }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5 text-left">
                                <th class="px-6 py-3 text-xs text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Starts</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Expires</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse($allSubscriptions as $sub)
                            <tr x-show="subFilter==='all' || subFilter==='{{ $sub->status }}'">
                                <td class="px-6 py-3">
                                    <p class="font-semibold text-white text-sm">{{ $sub->user?->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $sub->user?->email }}</p>
                                </td>
                                <td class="px-4 py-3"><span class="badge-pill badge-sub">{{ ucfirst($sub->plan) }}</span></td>
                                <td class="px-4 py-3">
                                    <span class="badge-pill
                                        {{ $sub->status === 'active' && $sub->isUpcoming() ? 'badge-pending' : ($sub->status === 'active' ? 'badge-sub' : ($sub->status === 'pending' ? 'badge-pending' : ($sub->status === 'paused' ? 'badge-pending' : 'badge-failed'))) }}">
                                        {{ $sub->status === 'active' && $sub->isUpcoming() ? '📅 Upcoming' : ($sub->status === 'paused' ? '⏸️ Paused' : ucfirst($sub->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ ucfirst($sub->payment_method ?? 'manual') }}</td>
                                <td class="px-4 py-3 text-xs text-emerald-400 font-bold">
                                    {{ $sub->amount_paid ? 'Ksh '.number_format($sub->amount_paid) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $sub->starts_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs {{ $sub->ends_at?->isPast() ? 'text-red-400' : 'text-gray-400' }}">
                                    {{ $sub->ends_at?->format('d M Y') ?? 'Forever' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1.5 flex-wrap">
                                        @if($sub->status === 'pending')
                                        <button @click="approveSubscription({{ $sub->id }},'{{ addslashes($sub->user?->name) }}')"
                                                class="text-xs text-emerald-400 hover:text-emerald-300 border border-emerald-500/30 hover:border-emerald-500/50 px-2 py-1 rounded-lg transition-colors">
                                            ✅ Approve
                                        </button>
                                        @endif
                                        @if($sub->isActive())
                                        <button @click="pauseSubscription({{ $sub->id }},'{{ addslashes($sub->user?->name) }}')"
                                                class="text-xs text-amber-400 hover:text-amber-300 border border-amber-500/20 hover:border-amber-500/40 px-2 py-1 rounded-lg transition-colors">
                                            ⏸️ Pause
                                        </button>
                                        <button @click="revokeSubscription({{ $sub->user_id }},'{{ addslashes($sub->user?->name) }}')"
                                                class="text-xs text-red-400 hover:text-red-300 border border-red-500/20 hover:border-red-500/40 px-2 py-1 rounded-lg transition-colors">
                                            Revoke
                                        </button>
                                        @endif
                                        @if($sub->isPaused())
                                        <button @click="resumeSubscription({{ $sub->id }},'{{ addslashes($sub->user?->name) }}')"
                                                class="text-xs text-emerald-400 hover:text-emerald-300 border border-emerald-500/30 hover:border-emerald-500/50 px-2 py-1 rounded-lg transition-colors">
                                            ▶️ Resume
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="px-6 py-10 text-center text-gray-500 text-sm">No subscriptions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: SCHOOLS
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='schools'" x-data="schoolsPanel()">

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-white text-base">School Subscriptions ({{ $schools->count() }})</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Schools get a shared pool of seats. Students access the game like premium subscribers while their school's plan is active.</p>
                </div>
                <button @click="openCreate()"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New School
                </button>
            </div>

            @if($schools->count() === 0)
            <div class="panel rounded-2xl p-10 text-center">
                <div class="text-5xl mb-3 opacity-30">🏫</div>
                <p class="text-gray-400 font-semibold mb-1">No school subscriptions yet</p>
                <p class="text-sm text-gray-600 mb-4">Create one to give a school a pool of seats for their students.</p>
                <button @click="openCreate()" class="px-4 py-2 rounded-xl text-sm font-bold" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;">+ Create First School</button>
            </div>
            @else
            <div class="space-y-3">
                @foreach($schools as $school)
                @php
                    $isActive = $school->status === 'active' && $school->ends_at->isFuture();
                    $used     = $school->active_members_count ?? 0;
                    $pct      = $school->seats > 0 ? round(($used / $school->seats) * 100) : 0;
                @endphp
                <div class="panel rounded-2xl p-5">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-black text-base text-white">{{ $school->school_name }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $isActive ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/25' : 'text-red-400 bg-red-500/10 border-red-500/25' }}">
                                    {{ $isActive ? '● Active' : '○ ' . $school->statusLabel() }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">{{ $school->contact_email }}</p>

                            <div class="flex flex-wrap gap-4 text-xs">
                                <div>
                                    <span class="text-gray-600">Seats used</span>
                                    <span class="font-bold text-white ml-1">{{ $used }}/{{ $school->seats }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Classes</span>
                                    <span class="font-bold text-white ml-1">{{ $schoolClassesTableExists ? $school->classes()->count() : 0 }}/{{ $school->max_classes }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Valid until</span>
                                    <span class="font-bold text-amber-400 ml-1">{{ $school->ends_at->format('d M Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Paid</span>
                                    <span class="font-bold text-emerald-400 ml-1">Ksh {{ number_format($school->price_kes) }}</span>
                                </div>
                                @if($school->notes)
                                <div class="text-gray-600 italic">{{ $school->notes }}</div>
                                @endif
                            </div>

                            {{-- Seat usage bar --}}
                            <div class="mt-3 h-1.5 rounded-full bg-white/5 overflow-hidden" style="max-width:200px;">
                                <div class="h-full rounded-full transition-all"
                                     style="width:{{ $pct }}%;background:{{ $pct > 90 ? '#ef4444' : ($pct > 70 ? '#f59e0b' : '#10b981') }};"></div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 flex-shrink-0">
                            @php $teacherTableReady = \Illuminate\Support\Facades\Schema::hasTable('school_teachers'); @endphp
                            @php $owner = $teacherTableReady ? $school->teachers->firstWhere('role', 'owner') : null; @endphp
                            @if(!$teacherTableReady)
                            <span title="Run Migrations in the Artisan tab to enable the Teacher Portal"
                                  class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-xl border border-white/10 bg-white/5 text-gray-500 cursor-not-allowed">
                                👩‍🏫 Teacher Portal (migrate first)
                            </span>
                            @elseif($owner && $owner->status === 'invited')
                            <button @click="copyPortalUrl('{{ route('school.teacher.invite', $owner->invite_token) }}')"
                                    class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 transition-colors font-bold">
                                👩‍🏫 Copy Teacher Invite
                            </button>
                            @else
                            <a href="{{ route('school.teacher.dashboard', $school) }}" target="_blank"
                               class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 transition-colors font-bold">
                                👩‍🏫 Teacher Portal
                            </a>
                            @endif
                            <a href="{{ route('school.portal', $school->portal_token) }}" target="_blank"
                               class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-xl border border-indigo-500/30 bg-indigo-500/10 text-indigo-300 hover:bg-indigo-500/20 transition-colors font-bold">
                                🔗 Open Portal
                            </a>
                            <button @click="copyPortalUrl('{{ route('school.portal', $school->portal_token) }}')"
                                    class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-xl border border-white/10 bg-white/5 text-gray-400 hover:text-white transition-colors">
                                📋 Copy Student URL
                            </button>
                            <button @click="confirmDelete({{ $school->id }}, {{ Js::from($school->school_name) }})"
                                    class="text-xs px-3 py-1.5 rounded-xl border border-red-500/25 bg-red-500/8 text-red-400 hover:bg-red-500/15 transition-colors">
                                🗑 Delete
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Create School Modal --}}
            <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop" @click.self="createModal = false">
                <div class="modal-box p-6 w-full max-w-md">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-black text-lg">New School Subscription</h3>
                        <button @click="createModal = false" class="text-gray-400 hover:text-white w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/5">✕</button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">School Name
                                <x-help-tip text="The institution's name as it appears on their portal, in this admin list and on the school leaderboard." example="Starehe Boys Centre" />
                            </label>
                            <input type="text" x-model="form.school_name" class="input-field" placeholder="e.g. Starehe Boys Centre">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Contact Email
                                <x-help-tip text="The teacher or buyer who runs this account. They automatically become the school's owner teacher and get an invite link to the teacher portal, where they can bring on colleagues." example="librarian@starehe.ac.ke" />
                            </label>
                            <input type="email" x-model="form.contact_email" class="input-field" placeholder="librarian@starehe.ac.ke">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Student Seats
                                    <x-help-tip text="How many of this school's students can hold an active membership at once. Each member plays with full premium features; removing a member frees their seat." example="100" />
                                </label>
                                <input type="number" x-model.number="form.seats" class="input-field" min="1" max="2000" placeholder="100">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Duration (months)
                                    <x-help-tip text="How long this school's access lasts — the expiry date is set this many months from today. When it lapses, every student on the seats drops back to the free plan." example="12" />
                                </label>
                                <input type="number" x-model.number="form.months" class="input-field" min="1" max="60" placeholder="12">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Max Classes
                                    <x-help-tip text="How many classes or cohorts this school can create, each with its own teacher, roster and class challenges. Leave blank for the default of 3." example="3 for Form 1, 2 and 3" />
                                </label>
                                <input type="number" x-model.number="form.max_classes" class="input-field" min="1" max="100" placeholder="3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Price Paid (Ksh)
                                    <x-help-tip text="What this school actually paid, recorded for your revenue reporting. Set 0 for donor-sponsored or pilot schools." example="15000" />
                                </label>
                                <input type="number" x-model.number="form.price_kes" class="input-field" min="0" placeholder="15000">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Notes (optional)
                                <x-help-tip text="An internal memo about this school, shown on its card in this list. Admins only — the school never sees it." example="Term 1 2026, paid by cheque" />
                            </label>
                            <input type="text" x-model="form.notes" class="input-field" placeholder="e.g. Term 1 2026">
                        </div>
                    </div>
                    <template x-if="createError">
                        <p class="text-sm text-red-400 mt-3" x-text="createError"></p>
                    </template>
                    <div class="flex gap-3 mt-6">
                        <button @click="saveSchool()" :disabled="saving"
                                class="flex-1 py-2.5 rounded-xl font-bold text-white transition-all"
                                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                            <span x-show="!saving">Create School</span>
                            <span x-show="saving">Creating…</span>
                        </button>
                        <button @click="createModal = false" class="flex-1 py-2.5 rounded-xl border border-white/10 text-gray-400 hover:text-white">Cancel</button>
                    </div>
                </div>
            </div>

        </div>

        {{-- ════════════════════════════════════════
             TAB: FINANCIAL CRISES
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='crises'" x-data="crisesPanel()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-white text-base">Financial Crisis Events</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Trigger server-wide economic events. Players get a warning notification first, then the effect hits and lands on their Life Story timeline. Gameset users can also schedule these from <a href="{{ route('gameset.crises.index') }}" class="text-indigo-400 hover:underline">GameSet → Crisis Events</a>.</p>
                </div>
                <button @click="showForm = !showForm"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-bold transition-all"
                        style="background:linear-gradient(135deg,#ef4444,#dc2626);color:white;">
                    + New Crisis
                </button>
            </div>

            {{-- Create form --}}
            <div x-show="showForm" x-cloak class="panel rounded-2xl p-5 mb-5">
                <h3 class="font-bold text-white mb-2">Create Financial Crisis</h3>

                {{-- Presets --}}
                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-2">Start from a preset
                    <x-help-tip text="One click fills the whole form below with a ready-made Kenyan crisis — name, icon, description, effect type and severity. You can still edit any field afterwards; the preset is only a starting point." example="NSE Market Crash, Property Market Slump, Drought Food Inflation, Fuel Price Shock, Economic Recession, Currency Devaluation" />
                </p>
                <div class="flex flex-wrap gap-2 mb-3">
                    <template x-for="(p, i) in presets" :key="i">
                        <button type="button" @click="applyPreset(p)"
                                class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all"
                                style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);color:#fcd34d;">
                            <span x-text="p.icon + ' ' + p.name"></span>
                        </button>
                    </template>
                </div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <button type="button" @click="quickTimes(48)" class="text-[11px] font-bold px-3 py-1.5 rounded-lg" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">⏱️ Classic: warn now, hit in 48h</button>
                    <button type="button" @click="quickTimes(24)" class="text-[11px] font-bold px-3 py-1.5 rounded-lg" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">⚡ Fast: warn now, hit in 24h</button>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Name
                            <x-help-tip text="The crisis headline players see in their warning notification and on their Life Story timeline. Name the real-world event, not the mechanic — it's what makes the shock feel like news rather than a penalty. Max 80 characters." example="Kenyan Recession 2026" />
                        </label>
                        <input x-model="form.name" type="text" placeholder="e.g. Kenyan Recession 2026" class="input-field mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Icon (emoji)
                            <x-help-tip text="Single emoji that fronts this crisis in notifications and on the timeline. Pick one that signals the kind of shock at a glance, so a player scanning their timeline can tell a market crash from a drought." example="📉" />
                        </label>
                        <input x-model="form.icon" type="text" placeholder="⚠️" maxlength="4" class="input-field mt-1">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs text-gray-400 font-bold uppercase">Description
                            <x-help-tip text="The story behind the shock, shown in the warning notification and the timeline entry. This is where the financial lesson lands — say what happened and what a prepared player could have done. Max 400 characters." example="Inflation has pushed food prices up nationwide. Everyone's wallet takes a hit — but savings accounts are untouched, so anyone with an emergency fund rides it out." />
                        </label>
                        <textarea x-model="form.description" rows="2" placeholder="What happened and how it affects players" class="input-field mt-1 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Effect Type
                            <x-help-tip text="Which part of every player's finances the shock attacks. Investment Value Drop hits pending deals, Asset Value Drop hits owned assets, Balance Drain takes a cut of wallets (savings are spared — that's the lesson), and Salary Cut reduces every pay packet collected for the whole active window rather than firing once." example="Balance Drain — teaches why an emergency fund in savings beats cash in the wallet" />
                        </label>
                        <select x-model="form.effect_type" class="input-field mt-1">
                            <option value="investment_drop">Investment Value Drop</option>
                            <option value="asset_drop">Asset Value Drop</option>
                            <option value="balance_drain">Balance Drain</option>
                            <option value="salary_cut">Salary Cut</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Effect Amount (%)
                            <x-help-tip text="Severity — the percentage of the targeted value that is wiped out for every player at once. 5–10% is a mild scare that starts a conversation; 20%+ genuinely hurts and can end a careless player's run. Allowed range is 0.1 to 100." example="10 (a mild drought-inflation drain) or 20 (a painful recession)" />
                        </label>
                        <input x-model.number="form.effect_amount" type="number" min="1" max="100" placeholder="e.g. 20" class="input-field mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Warning At (48hr before)
                            <x-help-tip text="Real-world date/time the “crisis incoming” notification goes out to every player. The gap between this and Active From is the whole game — it's the window in which players can move cash to savings or sell exposed assets. Set it in the past (or use the quick buttons above) to warn immediately." example="Warn now, hit in 48 hours — the classic setup" />
                        </label>
                        <input x-model="form.warning_at" type="datetime-local" class="input-field mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Active From
                            <x-help-tip text="The hit moment — when the effect actually applies and a 🌪️ entry lands on every affected player's timeline. Must be after Warning At. Crises are processed hourly by the cron job (or on demand via Artisan → Process Crises Now)." example="Wednesday 10:00, two days after the Monday warning" />
                        </label>
                        <input x-model="form.active_from" type="datetime-local" class="input-field mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Active Until
                            <x-help-tip text="When the crisis window closes and the status flips to ✅ Completed. For one-shot effects (investment drop, asset drop, balance drain) this mostly just controls how long it reads as “active”; for Salary Cut it matters a lot — every salary collected inside this window stays reduced. Must be after Active From." example="Friday 10:00 — a two-day salary-cut window covering one payday" />
                        </label>
                        <input x-model="form.active_until" type="datetime-local" class="input-field mt-1">
                    </div>
                </div>
                <div x-show="error" class="text-red-400 text-xs mt-3" x-text="error"></div>
                <div class="flex gap-3 mt-4">
                    <button @click="createCrisis()"
                            class="px-4 py-2 rounded-xl text-sm font-bold text-white transition-all"
                            style="background:linear-gradient(135deg,#ef4444,#dc2626);" :disabled="saving">
                        <span x-text="saving ? 'Creating…' : '🌪 Launch Crisis'"></span>
                    </button>
                    <button @click="showForm = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-400 hover:text-white transition-colors">Cancel</button>
                </div>
            </div>

            {{-- Existing crises --}}
            <div class="space-y-3">
                @forelse($crises as $crisis)
                <div class="panel rounded-2xl px-5 py-4 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">{{ $crisis->icon }}</span>
                        <div>
                            <div class="font-bold text-white text-sm">{{ $crisis->name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $crisis->description }}</div>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(239,68,68,0.12);color:#f87171;">
                                    {{ $crisis->effect_type }} · {{ $crisis->effect_amount }}%
                                </span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(255,255,255,0.05);color:#9ca3af;">
                                    Active: {{ $crisis->active_from->format('M d H:i') }} – {{ $crisis->active_until->format('M d H:i') }}
                                </span>
                                @php $stKey = $crisis->statusKey(); @endphp
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $stKey === 'active' ? 'animate-pulse' : '' }}"
                                      style="{{ [
                                          'done'      => 'background:rgba(107,114,128,0.15);color:#9ca3af;',
                                          'active'    => 'background:rgba(239,68,68,0.15);color:#f87171;',
                                          'warned'    => 'background:rgba(245,158,11,0.15);color:#fcd34d;',
                                          'scheduled' => 'background:rgba(99,102,241,0.15);color:#a5b4fc;',
                                      ][$stKey] }}">{{ $crisis->statusLabel() }}</span>
                            </div>
                        </div>
                    </div>
                    <button onclick="deleteCrisis({{ $crisis->id }}, this)"
                            class="text-red-400 hover:text-red-300 text-xs font-bold flex-shrink-0 transition-colors">Delete</button>
                </div>
                @empty
                <div class="text-center py-10 text-gray-500">
                    <div class="text-4xl mb-2">🌤️</div>
                    <p>No crises scheduled. The economy is calm.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: BROADCAST COMPOSER
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='broadcast'" x-data="broadcastPanel()">
            <div class="mb-4">
                <h2 class="font-bold text-white text-base">📢 Broadcast Composer</h2>
                <p class="text-xs text-gray-500 mt-0.5">Send an announcement to players' notification bell — and their phone via push, if they're subscribed and it's not quiet hours (9:30pm–6am) or over their daily cap.</p>
            </div>

            <div class="rounded-2xl p-4 mb-4 max-w-2xl text-xs leading-relaxed" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);color:#fcd34d;">
                <p class="font-bold mb-1">⚠️ "Success" here means the in-app notifications were created — it does NOT mean push actually arrived on anyone's phone.</p>
                <p class="text-amber-200/80">A broadcast silently produces zero pushes if: the recipient never enabled push (Profile → Notification Settings), you tested during quiet hours (9:30pm–6am Nairobi), they'd already hit today's 4-push cap, or VAPID keys aren't configured (Settings tab). To rule all of that out at once, use <b>🔔 Send Test Push to Myself</b> in the Settings tab — it bypasses every one of those checks and gives you a direct success/failure reason.</p>
            </div>

            <div class="panel rounded-2xl p-5 max-w-2xl">
                <label class="text-xs text-gray-400 font-bold uppercase mb-1 block">Title
                    <x-help-tip text="Headline for the announcement — it becomes the bold first line of the in-app notification bell entry and the title of the phone push. Max 100 characters, but phones truncate around 40, so front-load the point." example="New feature: Freelance Gigs are live!" />
                </label>
                <input type="text" x-model="title" maxlength="100" class="input-field mb-3" placeholder="e.g. New feature: Freelance Gigs are live!">

                <label class="text-xs text-gray-400 font-bold uppercase mb-1 block">Message
                    <x-help-tip text="Body of the announcement, shown under the title in the notification bell and inside the phone push. Max 300 characters — one clear sentence plus a call to action beats a paragraph, since most players only ever see the push preview." example="Head to the Opportunity Hub — short gigs now pay out the same game day, with no long contract." />
                </label>
                <textarea x-model="body" maxlength="300" rows="3" class="input-field mb-3" placeholder="Keep it short — this also has to fit in a phone notification."></textarea>
                <p class="text-[11px] text-gray-600 mb-4" x-text="body.length + ' / 300'"></p>

                <label class="text-xs text-gray-400 font-bold uppercase mb-2 block">Audience
                    <x-help-tip text="Who receives this broadcast. Every option creates in-app notifications for the matching players immediately; push delivery on top of that still respects each recipient's own quiet hours, daily cap and category preferences, so a “success” message never guarantees every phone buzzed. Picking anything other than Everyone reveals a follow-up field below." example="Free players only — for a conversion nudge before a promo ends" />
                </label>
                <div class="grid sm:grid-cols-2 gap-2 mb-4">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl cursor-pointer text-sm" :style="audience==='all' ? 'background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.4);' : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);'">
                        <input type="radio" x-model="audience" value="all" class="accent-indigo-500"> <span class="text-gray-300">🌍 Everyone<x-help-tip text="Sends to every registered player on the platform with no filter — free and paid, all ages, schools included. Use it only for genuine platform-wide news; over-using it trains players to ignore the bell." example="Scheduled maintenance tonight, or a new district going live" /></span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl cursor-pointer text-sm" :style="audience==='free_only' ? 'background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.4);' : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);'">
                        <input type="radio" x-model="audience" value="free_only" class="accent-indigo-500"> <span class="text-gray-300">🆓 Free players only<x-help-tip text="Targets only players with no active subscription — anyone whose subscription is missing, inactive or already expired. This is the conversion-nudge audience, so paying subscribers never see the upsell. No extra field is needed." example="Your free trial ends Sunday — upgrade now and keep your career progress" /></span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl cursor-pointer text-sm" :style="audience==='age_group' ? 'background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.4);' : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);'">
                        <input type="radio" x-model="audience" value="age_group" class="accent-indigo-500"> <span class="text-gray-300">🎂 One age group<x-help-tip text="Sends only to players whose profile age group matches the one you pick in the dropdown that appears below. Use it when the news is age-specific — content, jobs and quests are age-gated, so announcing a teen feature to 8–12s just causes confusion." example="Pick 13–17 when new secondary-school career tracks go live" /></span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl cursor-pointer text-sm" :style="audience==='school' ? 'background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.4);' : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);'">
                        <input type="radio" x-model="audience" value="school" class="accent-indigo-500"> <span class="text-gray-300">🏫 One school<x-help-tip text="Sends only to the active members of the one school subscription you choose in the dropdown that appears below. Removed or inactive seat-holders are skipped automatically, so a school that has rotated its roster won't get messages to ex-students." example="Pick the school, then announce Friday's class challenge to that cohort only" /></span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl cursor-pointer text-sm sm:col-span-2" :style="audience==='single_user' ? 'background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.4);' : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);'">
                        <input type="radio" x-model="audience" value="single_user" class="accent-indigo-500"> <span class="text-gray-300">👤 One player (by email)<x-help-tip text="Sends to exactly one account, matched on the email address you type into the field that appears below. This is the support-reply channel — the address must match a registered account exactly or the broadcast reaches nobody." example="player@example.com — following up on a “my salary disappeared” ticket" /></span>
                    </label>
                </div>

                <div x-show="audience==='age_group'" x-cloak class="mb-4">
                    <select x-model="ageGroup" class="input-field">
                        <option value="8-12">8–12</option>
                        <option value="13-17">13–17</option>
                        <option value="18-25">18–25</option>
                        <option value="26+">26+</option>
                    </select>
                </div>
                <div x-show="audience==='school'" x-cloak class="mb-4">
                    <select x-model="schoolId" class="input-field">
                        <option value="">— Select school —</option>
                        @foreach($schools as $s)
                        <option value="{{ $s->id }}">{{ $s->school_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="audience==='single_user'" x-cloak class="mb-4">
                    <input type="email" x-model="email" class="input-field" placeholder="player@example.com">
                </div>

                <div x-show="error" x-cloak class="text-red-400 text-xs font-bold mb-3" x-text="error"></div>
                <div x-show="success" x-cloak class="text-emerald-400 text-xs font-bold mb-3" x-text="success"></div>

                <button @click="send()" :disabled="sending"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <span x-show="!sending">📢 Send Broadcast</span>
                    <span x-show="sending">Sending…</span>
                </button>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: PLANS & PRICING
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='plans'" x-data="schoolPlanCreator()">

            {{-- Free Plan Gates — what unsubscribed players can do --}}
            <div class="panel rounded-2xl p-5 mb-6" x-data="gatesPanel()">
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <h2 class="font-bold text-white text-base">🚧 Free Plan Gates</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Everything an unsubscribed player is limited to — nothing is hardcoded, changes apply instantly. 0 = unlimited.</p>
                    </div>
                    <button @click="save()" :disabled="saving"
                            class="px-4 py-2 rounded-xl text-sm font-bold text-white"
                            style="background:linear-gradient(135deg,#10b981,#059669);">
                        <span x-text="saving ? 'Saving…' : '💾 Save Gates'"></span>
                    </button>
                </div>
                <div x-show="msg" x-cloak class="text-xs font-bold mt-2" :class="ok ? 'text-emerald-400' : 'text-red-400'" x-text="msg"></div>

                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mt-4 mb-2">Free-player limits</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div><label class="text-[11px] text-gray-400 font-bold">Max assets owned<x-help-tip text="How many distinct active assets an unsubscribed player may own at once in the Marketplace. Hitting the cap prompts them to subscribe. 0 = unlimited." example="3 (default)" /></label><input type="number" min="0" x-model.number="free.max_assets" class="input-field mt-1"></div>
                    <div><label class="text-[11px] text-gray-400 font-bold">Max active deals<x-help-tip text="Pending investment deals a free player can run at the same time in Equity Square. 0 = unlimited." example="1 (default)" /></label><input type="number" min="0" x-model.number="free.max_active_deals" class="input-field mt-1"></div>
                    <div><label class="text-[11px] text-gray-400 font-bold">Max savings goals<x-help-tip text="Open savings schemes a free player can keep running at once. 0 = unlimited." example="1 (default)" /></label><input type="number" min="0" x-model.number="free.max_savings_schemes" class="input-field mt-1"></div>
                    <div><label class="text-[11px] text-gray-400 font-bold">Max loans<x-help-tip text="Active loans a free player may hold at once — premium players get 2. 0 = unlimited." example="1 (default)" /></label><input type="number" min="0" x-model.number="free.max_loans" class="input-field mt-1"></div>
                    <div><label class="text-[11px] text-gray-400 font-bold">Catch-up game days / visit<x-help-tip text="How many game days are simulated for a free player when they return after time away. This is the strongest pace lever in the game — never set it below about 5 or returning players feel frozen." example="7 (default)" /></label><input type="number" min="1" max="60" x-model.number="free.catchup_ticks" class="input-field mt-1"></div>
                    <div><label class="text-[11px] text-gray-400 font-bold">pesAI questions / day<x-help-tip text="How many questions a free player can ask the pesAI money coach per real day. Also caps your AI API spend. 0 = unlimited." example="3 (default)" /></label><input type="number" min="0" x-model.number="free.ai_per_day" class="input-field mt-1"></div>
                    <div><label class="text-[11px] text-gray-400 font-bold">Fun World / game month<x-help-tip text="Leisure activities a free player can enjoy per game month. Mood — and therefore work income — depends on these, so this bites. 0 = unlimited." example="2 (default)" /></label><input type="number" min="0" x-model.number="free.fun_per_game_month" class="input-field mt-1"></div>
                    <div><label class="text-[11px] text-gray-400 font-bold">Forum topics min level<x-help-tip text="The level a free player must reach before they can start a new forum topic — an anti-spam gate. Replying to others is always free. 0 = no level needed." example="5 (default)" /></label><input type="number" min="0" x-model.number="free.forum_topic_min_level" class="input-field mt-1"></div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Can create Chama?<x-help-tip text="Whether free players may start their own savings chama. Joining someone else's chama is always free either way." example="No — premium only (default)" /></label>
                        <select x-model.number="free.chama_create" class="input-field mt-1">
                            <option value="0">No — premium only</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Quests started / day override<x-help-tip text="A tighter daily quest-start cap that applies to free accounts only. Leave at 0 and they follow the global pace value; set it above 0 to throttle them below everyone else." example="3 while the global pace is 5" /></label>
                        <input type="number" min="0" max="100" x-model.number="free.quests_per_day" class="input-field mt-1">
                        <p class="text-[10px] text-gray-600 mt-1">0 = no override, uses the global pace value below. Set &gt;0 to throttle free accounts tighter than everyone else.</p>
                    </div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Spin Wheel cooldown (real days)<x-help-tip text="How many real days a free player must wait between Spin Wheel plays. 0 lets them spin daily just like premium." example="7 (default — once a week)" /></label>
                        <input type="number" min="0" max="90" x-model.number="free.spin_cooldown_days" class="input-field mt-1">
                        <p class="text-[10px] text-gray-600 mt-1">0 = spin every day like premium. Default 7 = once a week.</p>
                    </div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Money Toolkit access?<x-help-tip text="Whether free players can open the six real-world calculators plus the real-life bill reminders and savings tracking that come with them." example="No — premium only (default)" /></label>
                        <select x-model.number="free.smart_tools_access" class="input-field mt-1">
                            <option value="0">No — premium only</option>
                            <option value="1">Yes</option>
                        </select>
                        <p class="text-[10px] text-gray-600 mt-1">Bajeti, Lengo, Matumizi, Ukuaji, Mkopo & Faida calculators on the dashboard.</p>
                    </div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Send money to friends?<x-help-tip text="Whether free players may gift a friend cash straight from their balance. Friend Loans (borrowing and lending) stay free either way — this gate only covers no-strings transfers." example="No — premium only (default)" /></label>
                        <select x-model.number="free.send_money_access" class="input-field mt-1">
                            <option value="0">No — premium only</option>
                            <option value="1">Yes</option>
                        </select>
                        <p class="text-[10px] text-gray-600 mt-1">Gifting a friend money straight from balance (separate from structured loans, which stay free).</p>
                    </div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Pesa Trail games / day<x-help-tip text="How many Pesa Trail arcade games a free player can start per real day — inviting someone else to a match counts the same as starting one. 0 = unlimited." example="3 (default)" /></label>
                        <input type="number" min="0" max="1000" x-model.number="free.pesatrail_games_per_day" class="input-field mt-1">
                        <p class="text-[10px] text-gray-600 mt-1">0 = unlimited. Premium always unlimited and can invite others unlimited times.</p>
                    </div>
                </div>

                <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mt-5 mb-2">Trial, upsell nudges & quest pace</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div><label class="text-[11px] text-gray-400 font-bold">Free trial (real days)<x-help-tip text="How long a brand-new account gets full premium before any gate applies — taste first, wall second. 0 turns the trial off completely." example="7 (default)" /></label><input type="number" min="0" max="365" x-model.number="trial_days" class="input-field mt-1"></div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Subscribe nudges<x-help-tip text="Master switch for the periodic upgrade reminders shown to free players. Off silences them platform-wide." example="On" /></label>
                        <select x-model="upsell_nag_enabled" class="input-field mt-1">
                            <option :value="true">On</option>
                            <option :value="false">Off</option>
                        </select>
                    </div>
                    <div><label class="text-[11px] text-gray-400 font-bold">Nudge every N real days<x-help-tip text="How many real days pass between subscribe reminders for the same free player. Lower is pushier — and easier to get tuned out." example="3 (default)" /></label><input type="number" min="1" max="90" x-model.number="upsell_nag_days" class="input-field mt-1"></div>
                    <div>
                        <label class="text-[11px] text-gray-400 font-bold">Max quests started / day (global pace)<x-help-tip text="The game-wide daily cap on starting new quests, premium included. Re-opening a quest already started never counts against it. 0 = unlimited." example="0 for unlimited, or 5 to slow the whole game down" /></label>
                        <input type="number" min="0" max="100" x-model.number="max_quests_per_day" class="input-field mt-1">
                        <p class="text-[10px] text-gray-600 mt-1">Applies to everyone, including premium, unless overridden above for free accounts.</p>
                    </div>
                </div>
                <p class="text-[10px] text-gray-600 mt-3">💡 Free players always keep the core loop — playing, learning, one of everything. Gates limit <b>scale and pace</b>, not access. "Free for all" mode (Settings tab) overrides every gate. These exact numbers are shown live on the <a href="{{ route('pricing') }}" target="_blank" class="underline hover:text-emerald-400">Pricing page</a> — no separate copy to keep in sync.</p>
            </div>

            {{-- Individual Plans --}}
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base" style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);">👤</div>
                    <div>
                        <h2 class="font-bold text-white text-base">Individual Plans</h2>
                        <p class="text-xs text-gray-500">Monthly subscriptions for individual players</p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($plans->where('plan_type', 'individual') as $plan)
                    <div class="plan-card {{ $plan->is_featured ? 'featured' : '' }} p-5"
                         x-data="planEditor({{ Js::from($plan->key) }}, {{ Js::from($plan->name) }}, {{ $plan->price_kes }}, {{ Js::from($plan->description) }}, {{ $plan->is_active ? 'true' : 'false' }}, {{ $plan->is_featured ? 'true' : 'false' }}, null)">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-xs text-gray-500 font-bold uppercase tracking-widest">{{ $plan->durationLabel() }}</span>
                                @if($plan->is_featured)<span class="ml-2 text-[10px] bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full font-bold">FEATURED</span>@endif
                            </div>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="isActive" class="rounded" style="accent-color:#6366f1;">
                                <span class="text-xs text-gray-400">Active<x-help-tip text="Uncheck to pull this plan off the public subscribe page without deleting it. Players already on it keep their access until it expires." example="Uncheck an old price tier after launching a new one" /></span>
                            </label>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase tracking-wider mb-1 block">Plan Name
                                    <x-help-tip text="The name players see on the subscribe page and on their M-Pesa receipt." example="Monthly Premium" />
                                </label>
                                <input type="text" x-model="name" class="input-field text-sm" placeholder="Plan name">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase tracking-wider mb-1 block">Price (Ksh)
                                    <x-help-tip text="What a player is charged via M-Pesa for this plan, in Kenyan Shillings — once per plan period, not per month unless the plan is monthly." example="300" />
                                </label>
                                <input type="number" x-model.number="price" min="1" class="input-field text-lg font-black" placeholder="0">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase tracking-wider mb-1 block">Description & Perks
                                    <x-help-tip text="The selling copy shown under this plan on the subscribe page — list what the player actually unlocks by paying." example="Full premium for 30 days • unlimited quests • Money Toolkit" />
                                </label>
                                <textarea x-model="desc" class="input-field text-xs resize-none" style="min-height:100px;font-family:inherit;" placeholder="Full access for X period…"></textarea>
                            </div>
                        </div>
                        <button @click="save()" :disabled="saving"
                                class="mt-4 w-full py-2.5 rounded-xl text-sm font-bold transition-all"
                                style="background:linear-gradient(135deg,rgba(99,102,241,0.3),rgba(139,92,246,0.2));border:1px solid rgba(99,102,241,0.4);color:#c7d2fe;">
                            <span x-show="!saving">💾 Save</span>
                            <span x-show="saving">Saving…</span>
                        </button>
                        <div x-show="saved" x-transition class="mt-2 text-center text-xs text-emerald-400 font-bold">✓ Saved!</div>
                        <div x-show="error" x-transition class="mt-2 text-center text-xs text-red-400 font-bold" x-text="error"></div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- School Plans --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);">🏫</div>
                        <div>
                            <h2 class="font-bold text-white text-base">School Plans</h2>
                            <p class="text-xs text-gray-500">Pooled seat subscriptions for schools — students get premium access while the school plan is active</p>
                        </div>
                    </div>
                    <button @click="openCreate()"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                            style="background:linear-gradient(135deg,#10b981,#059669);color:white;">
                        + New School Plan
                    </button>
                </div>

                @if($plans->where('plan_type', 'school')->count() === 0)
                <div class="panel rounded-2xl p-8 text-center">
                    <div class="text-4xl mb-2 opacity-30">🏫</div>
                    <p class="text-gray-400 mb-1">No school plans defined yet</p>
                    <p class="text-sm text-gray-600 mb-4">Create tiered plans (Small, Medium, Large) with different seat counts and prices.</p>
                    <button @click="openCreate()" class="px-4 py-2 rounded-xl text-sm font-bold" style="background:linear-gradient(135deg,#10b981,#059669);color:white;">Create First School Plan</button>
                </div>
                @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($plans->where('plan_type', 'school') as $plan)
                    <div class="plan-card p-5 relative" style="border-color:rgba(16,185,129,0.25);background:rgba(16,185,129,0.04);"
                         x-data="planEditor({{ Js::from($plan->key) }}, {{ Js::from($plan->name) }}, {{ $plan->price_kes }}, {{ Js::from($plan->description) }}, {{ $plan->is_active ? 'true' : 'false' }}, {{ $plan->is_featured ? 'true' : 'false' }}, {{ $plan->seats ?? 0 }}, {{ $plan->max_classes ?? 0 }})">
                        {{-- School badge --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.4);color:#34d399;">
                                    🏫 {{ $plan->durationLabel() }}
                                </span>
                                @if($plan->is_featured)
                                <span class="text-[10px] bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full font-bold">FEATURED</span>
                                @endif
                            </div>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" x-model="isActive" class="rounded" style="accent-color:#10b981;">
                                <span class="text-xs text-gray-400">Active<x-help-tip text="Uncheck to stop offering this school tier to new buyers without deleting it. Schools already on the plan keep their seats until it expires." example="Uncheck last year's term pricing" /></span>
                            </label>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase tracking-wider mb-1 block">Plan Name
                                    <x-help-tip text="The tier name schools see on the pricing page and on their invoice — make the size tier obvious." example="Small School — 50 Students" />
                                </label>
                                <input type="text" x-model="name" class="input-field text-sm" placeholder="e.g. Small School">
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="text-[10px] text-gray-500 uppercase tracking-wider mb-1 block">Price (Ksh)
                                        <x-help-tip text="Total the school pays for the whole plan period, in Kenyan Shillings — not per student." example="15000" />
                                    </label>
                                    <input type="number" x-model.number="price" min="0" class="input-field font-black" placeholder="0">
                                </div>
                                <div>
                                    <label class="text-[10px] text-emerald-500 uppercase tracking-wider mb-1 block">Student Seats
                                        <x-help-tip text="How many students can hold an active membership at the same time on this tier. Removing a member frees their seat for someone else." example="50" />
                                    </label>
                                    <input type="number" x-model.number="seats" min="1" max="5000" class="input-field font-black" placeholder="30" style="border-color:rgba(16,185,129,0.35);">
                                </div>
                                <div>
                                    <label class="text-[10px] text-emerald-500 uppercase tracking-wider mb-1 block">Max Classes
                                        <x-help-tip text="How many classes or cohorts a school on this tier may create. Each class has its own teacher, roster and class challenges — so this decides how many streams a school can run." example="3" />
                                    </label>
                                    <input type="number" x-model.number="maxClasses" min="1" max="100" class="input-field font-black" placeholder="3" style="border-color:rgba(16,185,129,0.35);">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase tracking-wider mb-1 block">Description
                                    <x-help-tip text="Shown under this tier on the pricing page — spell out what the school gets for the money." example="Up to 50 students • school portal • teacher dashboards" />
                                </label>
                                <textarea x-model="desc" class="input-field text-xs resize-none" style="min-height:70px;font-family:inherit;" placeholder="e.g. For schools with up to 30 students&#10;• All premium features&#10;• Dedicated school portal"></textarea>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4">
                            <button @click="save()" :disabled="saving"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                                    style="background:linear-gradient(135deg,rgba(16,185,129,0.3),rgba(5,150,105,0.2));border:1px solid rgba(16,185,129,0.4);color:#6ee7b7;">
                                <span x-show="!saving">💾 Save</span>
                                <span x-show="saving">Saving…</span>
                            </button>
                            <button @click="deletePlan()"
                                    :disabled="saving"
                                    class="px-3 py-2.5 rounded-xl text-sm transition-all"
                                    style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#f87171;">
                                🗑
                            </button>
                        </div>
                        <div x-show="saved" x-transition class="mt-2 text-center text-xs text-emerald-400 font-bold">✓ Saved!</div>
                        <div x-show="error" x-transition class="mt-2 text-center text-xs text-red-400 font-bold" x-text="error"></div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Create School Plan Modal --}}
            <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop" @click.self="createModal = false">
                <div class="modal-box p-6 w-full max-w-md">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="font-black text-lg">New School Plan</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Define a tier based on school size</p>
                        </div>
                        <button @click="createModal = false" class="text-gray-400 hover:text-white w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/5">✕</button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Plan Name
                                <x-help-tip text="The tier name schools see on the pricing page and on their invoice. Its URL key is generated from this name plus the seat count." example="Small School, 100 Students" />
                            </label>
                            <input type="text" x-model="createForm.name" class="input-field" placeholder="e.g. Small School, 100 Students">
                            <p class="text-[10px] text-gray-600 mt-1">Use a descriptive name that shows the size tier</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Student Seats
                                    <x-help-tip text="How many students a school on this tier can have active at once. A seat frees up when a member is removed from the school portal." example="50" />
                                </label>
                                <input type="number" x-model.number="createForm.seats" class="input-field" min="1" max="5000" placeholder="50">
                                <p class="text-[10px] text-gray-600 mt-1">Max students at once</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Duration (months)
                                    <x-help-tip text="How long access lasts once a school buys this plan — their expiry date is set this many months from the purchase date." example="12 for a full academic year" />
                                </label>
                                <input type="number" x-model.number="createForm.months" class="input-field" min="1" max="60" placeholder="12">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Max Classes
                                    <x-help-tip text="How many classes or cohorts a school on this tier may create. Each class gets its own teacher, roster and class challenges. Leave blank to use the default of 3." example="3" />
                                </label>
                                <input type="number" x-model.number="createForm.max_classes" class="input-field" min="1" max="100" placeholder="3">
                                <p class="text-[10px] text-gray-600 mt-1">How many classes/cohorts the school can create</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Price (Ksh)
                                    <x-help-tip text="Total the school pays for the whole period, in Kenyan Shillings. Set 0 for a free or donor-sponsored tier." example="15000" />
                                </label>
                                <input type="number" x-model.number="createForm.price_kes" class="input-field" min="0" placeholder="15000">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Description
                                <x-help-tip text="Shown under this tier on the pricing page — what the school gets for the price." example="Up to 50 students • school portal • teacher dashboards" />
                            </label>
                            <textarea x-model="createForm.description" class="input-field text-xs resize-none" rows="3"
                                      placeholder="e.g. For schools with up to 50 students&#10;• All students get full premium access&#10;• School portal to manage members"></textarea>
                        </div>
                        <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-xl p-3 text-xs text-emerald-400">
                            💡 When a school subscribes to this plan, they receive a <strong>private portal URL</strong> to add up to <span x-text="createForm.seats || '?'"></span> students. Students get full access while the plan is active.
                        </div>
                    </div>

                    <template x-if="createError">
                        <p class="text-sm text-red-400 mt-3" x-text="createError"></p>
                    </template>

                    <div class="flex gap-3 mt-6">
                        <button @click="saveNewPlan()" :disabled="creating"
                                class="flex-1 py-2.5 rounded-xl font-bold text-white transition-all"
                                style="background:linear-gradient(135deg,#10b981,#059669);">
                            <span x-show="!creating">Create School Plan</span>
                            <span x-show="creating">Creating…</span>
                        </button>
                        <button @click="createModal = false" class="flex-1 py-2.5 rounded-xl border border-white/10 text-gray-400 hover:text-white">Cancel</button>
                    </div>
                </div>
            </div>

        </div>

        {{-- ════════════════════════════════════════
             TAB: COUPONS
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='coupons'" x-data="couponsPanel()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-white text-base">Coupon Codes</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Discount codes players can redeem at checkout. Restrict to a plan, cap redemptions, or set an expiry.</p>
                </div>
                <button @click="openCreate()"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap"
                        style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;">
                    + New Coupon
                </button>
            </div>

            {{-- Create / edit form --}}
            <div x-show="showForm" x-cloak class="panel rounded-2xl p-5 mb-5">
                <h3 class="font-bold text-white mb-4" x-text="editingId ? 'Edit Coupon' : 'Create Coupon'"></h3>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Code
                            <x-help-tip text="What a player types at checkout to get the discount. Letters, numbers, dashes and underscores only — it is stored uppercase and must be unique." example="KARIBU20" />
                        </label>
                        <input x-model="form.code" @input="form.code = form.code.toUpperCase()" type="text" placeholder="e.g. KARIBU20" class="input-field mt-1 font-mono uppercase">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Type
                            <x-help-tip text="Percent takes a share off the plan price, so the saving scales with the plan. Fixed takes a flat number of shillings off whatever the plan costs." example="Percent for 20% off; Fixed for Ksh 100 off" />
                        </label>
                        <select x-model="form.type" class="input-field mt-1">
                            <option value="percent">Percent (%)</option>
                            <option value="fixed">Fixed (KES)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase" x-text="form.type === 'percent' ? 'Value (%)' : 'Value (KES)'"></label><x-help-tip text="How big the discount is, read according to the Type above — a percentage of the price (never more than 100) or a flat shilling amount." example="20 with Percent = 20% off" />
                        <input x-model.number="form.value" type="number" min="1" placeholder="e.g. 20" class="input-field mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Max Redemptions
                            <x-help-tip text="Total times this code can be redeemed across all players before it stops working — your budget cap on the campaign. Leave empty for unlimited." example="100 for a capped launch promo" />
                        </label>
                        <input x-model="form.max_redemptions" type="number" min="1" placeholder="Empty = unlimited" class="input-field mt-1">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Plan
                            <x-help-tip text="Restrict the code so it only discounts one specific plan. Leave on Any plan and it works on every plan, including school tiers." example="Annual Premium only" />
                        </label>
                        <select x-model="form.plan_id" class="input-field mt-1">
                            <option value="">Any plan</option>
                            @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-bold uppercase">Expires At
                            <x-help-tip text="After this moment the code is refused at checkout. Leave empty and it never expires." example="31 Dec 2026, 23:59" />
                        </label>
                        <input x-model="form.expires_at" type="datetime-local" class="input-field mt-1">
                        <p class="text-[10px] text-gray-600 mt-1">Empty = never expires</p>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="text-xs text-gray-400 font-bold uppercase">Note
                            <x-help-tip text="A reminder of what this code was created for, shown only in this admin table. Players never see it." example="Back-to-school campaign, Term 1 2026" />
                        </label>
                        <input x-model="form.note" type="text" placeholder="Internal note, e.g. Back-to-school campaign" class="input-field mt-1">
                    </div>
                </div>
                <div x-show="error" class="text-red-400 text-xs mt-3" x-text="error"></div>
                <div class="flex gap-3 mt-4">
                    <button @click="save()"
                            class="px-4 py-2 rounded-xl text-sm font-bold text-white transition-all"
                            style="background:linear-gradient(135deg,#f59e0b,#d97706);" :disabled="saving">
                        <span x-text="saving ? 'Saving…' : (editingId ? '💾 Update Coupon' : '🎟️ Create Coupon')"></span>
                    </button>
                    <button @click="showForm = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-400 hover:text-white transition-colors">Cancel</button>
                </div>
            </div>

            {{-- Coupons table --}}
            @if($coupons->count())
            <div class="panel rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5 text-left">
                                <th class="px-6 py-3 text-xs text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Discount</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Redemptions</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Expires</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Note</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @foreach($coupons as $c)
                            <tr>
                                <td class="px-6 py-3 font-mono font-black text-white text-sm">{{ $c->code }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-amber-300">{{ $c->label() }}</td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $c->plan?->name ?? 'Any plan' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $c->redemptions_count }}/{{ $c->max_redemptions ?? '∞' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $c->expires_at?->format('d M Y H:i') ?? 'Never' }}
                                    @if($c->expires_at && $c->expires_at->isPast())
                                    <span class="ml-1 text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(239,68,68,0.15);color:#f87171;">EXPIRED</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($c->is_active)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(16,185,129,0.12);color:#34d399;">Active</span>
                                    @else
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:rgba(255,255,255,0.05);color:#9ca3af;">Paused</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 max-w-[160px] truncate" title="{{ $c->note }}">{{ $c->note ?? '—' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button @click="edit({{ Js::from([
                                                'id' => $c->id,
                                                'code' => $c->code,
                                                'type' => $c->type,
                                                'value' => $c->value,
                                                'max_redemptions' => $c->max_redemptions,
                                                'plan_id' => $c->plan_id,
                                                'expires_at' => $c->expires_at?->format('Y-m-d\TH:i'),
                                                'note' => $c->note,
                                            ]) }})"
                                            class="text-indigo-400 hover:text-indigo-300 text-xs font-bold transition-colors">Edit</button>
                                    <button @click="toggle({{ $c->id }})"
                                            class="ml-3 text-amber-400 hover:text-amber-300 text-xs font-bold transition-colors">{{ $c->is_active ? 'Pause' : 'Resume' }}</button>
                                    <button @click="destroy({{ $c->id }})"
                                            class="ml-3 text-red-400 hover:text-red-300 text-xs font-bold transition-colors">Delete</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="panel rounded-2xl p-8 text-center">
                <div class="text-4xl mb-2 opacity-30">🎟️</div>
                <p class="text-gray-400 mb-1">No coupons yet</p>
                <p class="text-sm text-gray-600 mb-4">Create a code to run a discount campaign on subscriptions.</p>
                <button @click="openCreate()" class="px-4 py-2 rounded-xl text-sm font-bold" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;">Create First Coupon</button>
            </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════
             TAB: M-PESA PAYMENTS
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='payments'">
            <div class="panel rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5 flex flex-wrap items-center gap-4">
                    <h2 class="font-bold text-white text-base flex-1">M-Pesa Transactions</h2>
                    <div class="flex gap-2 text-xs font-bold">
                        @foreach(['all','pending','completed','failed'] as $f)
                        <button @click="payFilter='{{ $f }}'"
                                :class="payFilter==='{{ $f }}' ? 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40' : 'text-gray-500 border-white/10'"
                                class="px-3 py-1 rounded-full border transition-colors">{{ ucfirst($f) }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5 text-left">
                                <th class="px-6 py-3 text-xs text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Receipt</th>
                                <th class="px-4 py-3 text-xs text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @forelse($payments as $pay)
                            <tr x-show="payFilter==='all' || payFilter==='{{ $pay->status }}'">
                                <td class="px-6 py-3">
                                    <p class="font-semibold text-white text-sm">{{ $pay->user?->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $pay->user?->email }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-amber-300 font-bold">{{ $pay->plan?->name ?? ucfirst($pay->plan ?? '—') }}</td>
                                <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $pay->phone }}</td>
                                <td class="px-4 py-3 text-sm font-black text-emerald-400">Ksh {{ number_format($pay->amount) }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge-pill {{ $pay->status === 'completed' ? 'badge-sub' : ($pay->status === 'pending' ? 'badge-pending' : 'badge-failed') }}">
                                        {{ ucfirst($pay->status) }}
                                    </span>
                                    @if($pay->failure_reason)
                                    <div class="text-[10px] text-red-400 mt-1 max-w-[120px] truncate" title="{{ $pay->failure_reason }}">{{ $pay->failure_reason }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $pay->mpesa_receipt ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $pay->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500 text-sm">No M-Pesa transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: LIFE SIM CONFIG
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='lifesim'">
            <div class="p-6">
                <h2 class="text-xl font-black text-white mb-1">🎮 Life Simulation Configuration</h2>
                <p class="text-gray-400 text-sm mb-8 leading-relaxed">
                    Manage the rules that govern the life simulator — what assets players can own, what bills they incur,
                    and what events can happen to them. Changes take effect immediately for all players.
                </p>

                <div class="grid sm:grid-cols-3 gap-5 mb-8">

                    {{-- Assets card --}}
                    <a href="{{ route('gameset.assets.index') }}"
                       class="group block rounded-2xl p-6 border transition-all hover:-translate-y-1"
                       style="background:linear-gradient(135deg,rgba(245,158,11,.12),rgba(234,88,12,.04));border-color:rgba(245,158,11,.25);">
                        <div class="text-4xl mb-3">🏢</div>
                        <h3 class="text-base font-black text-white mb-1">Marketplace Assets</h3>
                        <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                            Add, edit, or remove buyable assets. Set each asset's price, monthly income, running cost,
                            appreciation rate, condition decay, and which bill it triggers on purchase.
                        </p>
                        <div class="flex items-center gap-2 flex-wrap text-[10px]">
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 font-bold">base_price</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold">monthly_income</span>
                            <span class="px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 font-bold">monthly_cost</span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 font-bold">appreciation_rate</span>
                        </div>
                        <div class="mt-4 text-[11px] font-bold text-amber-400 group-hover:text-amber-300 transition-colors">
                            Manage Assets →
                        </div>
                    </a>

                    {{-- Bills card --}}
                    <a href="{{ route('gameset.bills.index') }}"
                       class="group block rounded-2xl p-6 border transition-all hover:-translate-y-1"
                       style="background:linear-gradient(135deg,rgba(16,185,129,.12),rgba(5,150,105,.04));border-color:rgba(16,185,129,.25);">
                        <div class="text-4xl mb-3">🗓</div>
                        <h3 class="text-base font-black text-white mb-1">Bill Templates</h3>
                        <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                            Create recurring bills that auto-assign to players. Control amounts, frequencies, which life chapter
                            triggers them, and the credit score impact of paying vs missing.
                        </p>
                        <div class="flex items-center gap-2 flex-wrap text-[10px]">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold">amount</span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 font-bold">frequency_ticks</span>
                            <span class="px-2 py-0.5 rounded-full bg-pink-500/15 text-pink-400 font-bold">credit_impact_pay</span>
                            <span class="px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 font-bold">credit_impact_miss</span>
                        </div>
                        <div class="mt-4 text-[11px] font-bold text-emerald-400 group-hover:text-emerald-300 transition-colors">
                            Manage Bills →
                        </div>
                    </a>

                    {{-- Fun World activities card --}}
                    <a href="{{ route('gameset.fun-world.index') }}"
                       class="group block rounded-2xl p-6 border transition-all hover:-translate-y-1"
                       style="background:linear-gradient(135deg,rgba(255,107,53,.12),rgba(245,158,11,.04));border-color:rgba(255,107,53,.25);">
                        <div class="text-4xl mb-3">🎡</div>
                        <h3 class="text-base font-black text-white mb-1">Fun World Activities</h3>
                        <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                            Entertainment experiences players buy to recharge their character's mood. Set Kenyan-realistic
                            prices, mood boosts, and XP rewards. Mood affects income and quest XP.
                        </p>
                        <div class="flex items-center gap-2 flex-wrap text-[10px]">
                            <span class="px-2 py-0.5 rounded-full bg-orange-500/15 text-orange-400 font-bold">price</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 font-bold">mood_boost_base</span>
                            <span class="px-2 py-0.5 rounded-full bg-violet-500/15 text-violet-400 font-bold">xp_reward</span>
                        </div>
                        <div class="mt-4 text-[11px] font-bold text-orange-400 group-hover:text-orange-300 transition-colors">
                            Manage Activities →
                        </div>
                    </a>

                    {{-- Quests card --}}
                    <a href="{{ route('gameset.quests.index') }}"
                       class="group block rounded-2xl p-6 border transition-all hover:-translate-y-1"
                       style="background:linear-gradient(135deg,rgba(124,58,237,.12),rgba(91,33,182,.04));border-color:rgba(124,58,237,.25);">
                        <div class="text-4xl mb-3">📜</div>
                        <h3 class="text-base font-black text-white mb-1">Quest Board</h3>
                        <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                            Create quests with level gates so players unlock them as they progress. Set XP and cash rewards.
                            Configure auto-detect triggers (e.g. buying a phone auto-completes "Get Connected").
                        </p>
                        <div class="flex items-center gap-2 flex-wrap text-[10px]">
                            <span class="px-2 py-0.5 rounded-full bg-violet-500/15 text-violet-400 font-bold">level_required</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 font-bold">trigger_type</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold">xp_reward</span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 font-bold">kes_reward</span>
                        </div>
                        <div class="mt-4 text-[11px] font-bold text-violet-400 group-hover:text-violet-300 transition-colors">
                            Manage Quests →
                        </div>
                    </a>

                    {{-- Investment Deals card --}}
                    <a href="{{ route('gameset.deals.index') }}"
                       class="group block rounded-2xl p-6 border transition-all hover:-translate-y-1"
                       style="background:linear-gradient(135deg,rgba(5,150,105,.12),rgba(4,120,87,.04));border-color:rgba(5,150,105,.25);">
                        <div class="text-4xl mb-3">📈</div>
                        <h3 class="text-base font-black text-white mb-1">Investment Deals</h3>
                        <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                            Create quick deals that resolve after set ticks. Some succeed, some fail — teaching risk vs reward.
                            Players invest from Equity Square; outcomes are probabilistic.
                        </p>
                        <div class="flex items-center gap-2 flex-wrap text-[10px]">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 font-bold">success_probability</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 font-bold">maturity_ticks</span>
                            <span class="px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 font-bold">loss_pct</span>
                        </div>
                        <div class="mt-4 text-[11px] font-bold text-emerald-400 group-hover:text-emerald-300 transition-colors">
                            Manage Deals →
                        </div>
                    </a>

                    {{-- Loan Products card --}}
                    <a href="{{ route('gameset.loans.index') }}"
                       class="group block rounded-2xl p-6 border transition-all hover:-translate-y-1"
                       style="background:linear-gradient(135deg,rgba(29,78,216,.12),rgba(30,64,175,.04));border-color:rgba(29,78,216,.25);">
                        <div class="text-4xl mb-3">🏦</div>
                        <h3 class="text-base font-black text-white mb-1">Loan Products</h3>
                        <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                            Configure loan products available at the Bank district. Set interest rates, terms, and minimum credit score.
                            Repayments compound and affect players' credit scores.
                        </p>
                        <div class="flex items-center gap-2 flex-wrap text-[10px]">
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 font-bold">annual_interest_rate</span>
                            <span class="px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-400 font-bold">term_ticks</span>
                            <span class="px-2 py-0.5 rounded-full bg-violet-500/15 text-violet-400 font-bold">min_credit_score</span>
                        </div>
                        <div class="mt-4 text-[11px] font-bold text-blue-400 group-hover:text-blue-300 transition-colors">
                            Manage Loans →
                        </div>
                    </a>

                    {{-- Life Events card --}}
                    <a href="{{ route('gameset.life-events.index') }}"
                       class="group block rounded-2xl p-6 border transition-all hover:-translate-y-1"
                       style="background:linear-gradient(135deg,rgba(139,92,246,.12),rgba(109,40,217,.04));border-color:rgba(139,92,246,.25);">
                        <div class="text-4xl mb-3">⚡</div>
                        <h3 class="text-base font-black text-white mb-1">Life Events</h3>
                        <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                            Probabilistic events that fire during tick processing. Configure the narrative, effect type
                            (balance delta, market shift, credit change), probability, and which asset category triggers them.
                        </p>
                        <div class="flex items-center gap-2 flex-wrap text-[10px]">
                            <span class="px-2 py-0.5 rounded-full bg-violet-500/15 text-violet-400 font-bold">balance_delta</span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 font-bold">market_event</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 font-bold">probability</span>
                            <span class="px-2 py-0.5 rounded-full bg-pink-500/15 text-pink-400 font-bold">asset_category</span>
                        </div>
                        <div class="mt-4 text-[11px] font-bold text-violet-400 group-hover:text-violet-300 transition-colors">
                            Manage Events →
                        </div>
                    </a>
                </div>

                {{-- How the sim works --}}
                <div class="rounded-2xl p-6" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);">
                    <h3 class="text-sm font-black text-white mb-4">⚙️ How the Life Simulator Uses These Configs</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 text-xs text-gray-400 leading-relaxed">
                        <div>
                            <div class="text-emerald-400 font-bold mb-1.5">Assets → Income & Costs</div>
                            Every game-month, each active PlayerAsset earns <code class="text-amber-300">monthly_income × conditionFactor()</code>
                            and incurs <code class="text-red-300">monthly_cost</code> (always deducted in full).
                            Condition degrades 3pts/month. Below 70% = income penalty, below 20% = no income.
                        </div>
                        <div>
                            <div class="text-emerald-400 font-bold mb-1.5">Bills → Cash Drain</div>
                            PlayerBills track <code class="text-amber-300">next_due_tick</code>. When the tick passes without payment,
                            <code class="text-red-300">credit_impact_miss</code> is applied. When paid on time,
                            <code class="text-emerald-300">credit_impact_pay</code> is applied. Bills drive
                            the Bills Board urgency colours.
                        </div>
                        <div>
                            <div class="text-emerald-400 font-bold mb-1.5">Life Events → Surprises</div>
                            On each login tick, <code class="text-violet-300">rollLifeEvents()</code> iterates all active events,
                            rolls <code class="text-amber-300">rand(0,1) &lt; probability</code>, and fires those that pass.
                            Asset-linked events only fire if the player owns that asset category.
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/5 text-[11px] text-gray-600">
                        <strong class="text-gray-400">Tip:</strong> Keep total active event probability per tick below ~0.20 to avoid event spam.
                        A player with 5 events each at 0.02 probability will see roughly 1 event per tick on average.
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: SETTINGS
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='settings'" x-data="settingsPanel()">
            <div class="grid lg:grid-cols-2 gap-6">

                {{-- SMTP --}}
                <div class="settings-section">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);">📧</div>
                        <div>
                            <h3 class="font-black text-white">SMTP Email Settings</h3>
                            <p class="text-xs text-gray-400">Configure outgoing emails (subscription confirmations, password resets)</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Host
                                    <x-help-tip text="Address of the mail server PesaQuest hands every outgoing email to — password resets, subscription confirmations and weekly summaries. Get it wrong and those emails silently never arrive; players then can't recover accounts." example="smtp.gmail.com" />
                                </label>
                                <input type="text" x-model="smtp.host" placeholder="smtp.gmail.com" class="input-field">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Port
                                    <x-help-tip text="Network port on that mail server. It has to match the Encryption setting below — 587 goes with TLS and 465 with SSL. A mismatched pair is the most common reason mail hangs and then fails." example="587" />
                                </label>
                                <input type="number" x-model="smtp.port" placeholder="587" class="input-field">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Username / Email
                                <x-help-tip text="The account PesaQuest logs into the mail server as. For most providers this is the full mailbox address, not a short username — and it is separate from the From Email below, though they are usually the same." example="you@gmail.com" />
                            </label>
                            <input type="text" x-model="smtp.username" placeholder="you@gmail.com" class="input-field">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Password / App Password
                                <x-help-tip text="Secret for that mailbox. Gmail and most modern providers reject your normal login password here — you must generate a dedicated app password with 2FA enabled. Leave blank when re-saving to keep the stored value." example="A 16-character Google app password, not your Gmail login" />
                            </label>
                            <input type="password" x-model="smtp.password" placeholder="••••••••••••" class="input-field">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Encryption
                                    <x-help-tip text="How the connection to the mail server is secured. Pair TLS with port 587 or SSL with port 465 — those are the two combinations providers actually accept. Only pick None for a local test relay that has no TLS at all." example="TLS (with port 587)" />
                                </label>
                                <select x-model="smtp.encryption" class="input-field">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="">None</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">From Name
                                    <x-help-tip text="The sender name players see in their inbox before they open anything. A recognisable brand name here measurably improves whether password-reset mails get opened instead of ignored as spam." example="PesaQuest" />
                                </label>
                                <input type="text" x-model="smtp.from_name" placeholder="PesaQuest" class="input-field">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">From Email
                                <x-help-tip text="Return address stamped on every outgoing mail, and where player replies land. Use an address on a domain your mail server is actually authorised to send for — otherwise spam filters reject the mail even though the send looks successful here." example="hello@moski.org" />
                            </label>
                            <input type="email" x-model="smtp.from_email" placeholder="hello@moski.org" class="input-field">
                        </div>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button @click="saveSmtp()" :disabled="smtpSaving" class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all" style="background:linear-gradient(135deg,rgba(99,102,241,0.3),rgba(139,92,246,0.2));border:1px solid rgba(99,102,241,0.4);color:#c7d2fe;">
                            <span x-show="!smtpSaving">💾 Save SMTP</span><span x-show="smtpSaving">Saving…</span>
                        </button>
                        <div class="flex gap-2">
                            <input type="email" x-model="testEmail" placeholder="test@email.com" class="input-field" style="width:160px;"><x-help-tip text="Where the 📤 Test button delivers a one-line proof email. Save your SMTP settings first — the test sends using whatever is currently stored, so testing before saving only checks the old config. If nothing arrives, check the spam folder before assuming the credentials are wrong." example="your.own@email.com" />
                            <button @click="testSmtp()" :disabled="smtpTesting" class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all text-white whitespace-nowrap" style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.3);">
                                <span x-show="!smtpTesting">📤 Test</span><span x-show="smtpTesting">Sending…</span>
                            </button>
                        </div>
                    </div>
                    <div x-show="smtpMsg" x-transition class="mt-2 text-xs font-bold text-center" :class="smtpOk ? 'text-emerald-400' : 'text-red-400'" x-text="smtpMsg"></div>
                </div>

                {{-- Google Sign-In --}}
                <div class="settings-section">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(234,67,53,0.15);border:1px solid rgba(234,67,53,0.3);">🔑</div>
                        <div>
                            <h3 class="font-black text-white">Google Sign-In</h3>
                            <p class="text-xs text-gray-400">Let players sign up / log in with their Google account</p>
                        </div>
                    </div>
                    <div class="mb-4 p-3 rounded-xl text-xs font-mono" style="background:rgba(234,67,53,0.07);border:1px solid rgba(234,67,53,0.2);">
                        <span class="text-gray-400">Authorized redirect URI (set this in Google Cloud Console):</span><br>
                        <span class="text-red-300 break-all">{{ config('app.url') }}/auth/google/callback</span>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Client ID
                                <x-help-tip text="Public identifier for your OAuth app from Google Cloud Console. It only works if that app lists the redirect URI shown above — a mismatch produces a Google error page instead of a login, for every player." example="123456789-abc.apps.googleusercontent.com" />
                            </label>
                            <input type="text" x-model="google.client_id" placeholder="xxxx.apps.googleusercontent.com" class="input-field">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Client Secret
                                <x-help-tip text="Private half of the same Google OAuth credential — treat it like a password and never paste it anywhere public. If it leaks, rotate it in Google Cloud Console and re-save here." example="GOCSPX-xxxxxxxxxxxxxxxx" />
                            </label>
                            <input type="password" x-model="google.client_secret" placeholder="••••••••••••" class="input-field">
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold text-gray-300 cursor-pointer">
                            <input type="checkbox" x-model="google.enabled" style="width:1.1rem;height:1.1rem;">
                            Show "Continue with Google" on login/register
                            <x-help-tip text="Master switch for the Google button on the login and register pages. Turning it off hides the button without deleting the credentials above, so you can pull it instantly if Google OAuth starts failing — existing Google-created accounts keep working via password reset." example="Leave OFF until the Client ID and Secret above are saved and tested" />
                        </label>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button @click="saveGoogle()" :disabled="googleSaving" class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all" style="background:linear-gradient(135deg,rgba(234,67,53,0.3),rgba(251,188,5,0.2));border:1px solid rgba(234,67,53,0.4);color:#fecaca;">
                            <span x-show="!googleSaving">💾 Save Google Sign-In</span><span x-show="googleSaving">Saving…</span>
                        </button>
                    </div>
                    <div x-show="googleMsg" x-transition class="mt-2 text-xs font-bold text-center" :class="googleOk ? 'text-emerald-400' : 'text-red-400'" x-text="googleMsg"></div>
                </div>

                {{-- Daraja / M-Pesa --}}
                <div class="settings-section">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);">📱</div>
                        <div>
                            <h3 class="font-black text-white">Safaricom Daraja API</h3>
                            <p class="text-xs text-gray-400">M-Pesa STK Push credentials for subscription payments</p>
                        </div>
                    </div>
                    <div class="mb-4 p-3 rounded-xl text-xs font-mono" style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.2);">
                        <span class="text-gray-400">Callback URL (auto-detected):</span><br>
                        <span class="text-emerald-400 break-all">{{ config('app.url') }}/mpesa/callback</span>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Environment
                                <x-help-tip text="Which Safaricom Daraja endpoint every STK Push is sent to. Sandbox uses test credentials and moves no real money — nothing you do there charges anyone. Switching to Production makes subscription payments real and irreversible, so only flip it once a sandbox payment has completed end to end." example="Sandbox (Testing) while setting up; Production (Live) on launch day" />
                            </label>
                            <select x-model="mpesa.env" class="input-field">
                                <option value="sandbox">Sandbox (Testing)</option>
                                <option value="production">Production (Live)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Consumer Key
                                    <x-help-tip text="Public half of your Daraja app credential — PesaQuest trades it for the access token behind every STK Push. Sandbox and Production keys are different; copying a sandbox key into live mode makes every payment fail with an auth error." example="Copied from your app on developer.safaricom.co.ke" />
                                </label>
                                <input type="text" x-model="mpesa.consumer_key" placeholder="From Daraja portal" class="input-field">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Consumer Secret
                                    <x-help-tip text="Private partner to the Consumer Key above — together they authenticate PesaQuest to Daraja. Anyone holding both can transact against your shortcode, so never share it; rotate it in the Daraja portal if it leaks." example="Copied from the same Daraja app as the Consumer Key" />
                                </label>
                                <input type="password" x-model="mpesa.consumer_secret" placeholder="••••••••••••" class="input-field">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Business Shortcode
                                    <x-help-tip text="The Paybill or Till number subscription money actually lands in — this is where your revenue goes, so double-check it before going live. 174379 is Safaricom's shared sandbox test shortcode; replace it with your own registered number for Production." example="174379 in sandbox; your own Paybill in production" />
                                </label>
                                <input type="text" x-model="mpesa.shortcode" placeholder="174379" class="input-field">
                            </div>
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Account Reference
                                    <x-help-tip text="Short label attached to every STK Push — it shows on the player's phone prompt and on your M-Pesa statement, so it is how you recognise PesaQuest income among everything else hitting that shortcode. Keep it short; long values get truncated by Safaricom." example="PesaQuest" />
                                </label>
                                <input type="text" x-model="mpesa.account_ref" placeholder="PesaQuest" class="input-field">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Passkey (LipaNaMpesa)
                                <x-help-tip text="Lipa Na M-Pesa Online passkey, issued per shortcode. It signs the password on each STK Push request, so it must belong to the exact shortcode above — a passkey from a different shortcode makes every prompt fail even when the keys are correct." example="The Lipa Na M-Pesa Online passkey for your shortcode" />
                            </label>
                            <input type="password" x-model="mpesa.passkey" placeholder="••••••••••••" class="input-field">
                        </div>
                    </div>
                    <button @click="saveMpesa()" :disabled="mpesaSaving" class="mt-4 w-full py-2.5 rounded-xl text-sm font-bold transition-all" style="background:linear-gradient(135deg,rgba(16,185,129,0.2),rgba(5,150,105,0.1));border:1px solid rgba(16,185,129,0.35);color:#6ee7b7;">
                        <span x-show="!mpesaSaving">💾 Save Daraja Settings</span><span x-show="mpesaSaving">Saving…</span>
                    </button>
                    <div x-show="mpesaMsg" x-transition class="mt-2 text-xs font-bold text-center" :class="mpesaOk ? 'text-emerald-400' : 'text-red-400'" x-text="mpesaMsg"></div>
                </div>

                {{-- Game Clock (spans full row) --}}
                <div class="settings-section lg:col-span-2">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);">⏱️</div>
                        <div>
                            <h3 class="font-black text-white">Game Clock Speed</h3>
                            <p class="text-xs text-gray-400">Controls how fast virtual life passes for all players simultaneously.</p>
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6 items-start">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Real Hours Per Game Week
                                <x-help-tip text="The single most powerful knob in the game: how many real hours it takes to burn through one game week (7 ticks — one tick is one game day). Divide the value by 7 to get the real time per tick: 24 real hours per game week means one real day = 7 game days, so 1 tick ≈ 3.4 real hours; 56 real hours per game week means one real day = 3 game days, so 1 tick = 8 real hours. Every tick-based mechanic scales with this at once — bill cycles, salary months, savings interest, loan installments, asset appreciation, gig cooldowns, deal maturities and quest deadlines all arrive proportionally faster or slower. Faster settings pack years of financial life into a school term but bury daily visitors in obligations; slower settings suit younger cohorts." example="2 hrs for a normal pace; 12 hrs for a calmer class group; 24 hrs so one real day = one game week" />
                            </label>
                            <select x-model="clock.rate" @change="updateClockDesc()" class="input-field">
                                <option value="0.25">0.25 hrs — 15 min real = 1 game week (Ultra Fast)</option>
                                <option value="0.5">0.5 hrs — 30 min real = 1 game week (Very Fast)</option>
                                <option value="1">1 hr — 1 hour real = 1 game week (Fast)</option>
                                <option value="2">2 hrs — 2 hours real = 1 game week (Normal)</option>
                                <option value="3">3 hrs — 3 hours real = 1 game week (Slow)</option>
                                <option value="6">6 hrs — 6 hours real = 1 game week (Very Slow)</option>
                                <option value="12">12 hrs — 12 hours real = 1 game week (Daily)</option>
                                <option value="24">24 hrs — 1 day real = 1 game week (Real Life)</option>
                            </select>
                            <p class="text-xs text-amber-400 mt-2 font-semibold" x-text="clockDesc"></p>
                        </div>
                        <div class="p-4 rounded-xl text-sm space-y-1" style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);">
                            <p class="text-amber-300 font-bold text-xs uppercase tracking-wide mb-2">What this means</p>
                            <p class="text-gray-300">At current speed, <span class="text-amber-300 font-bold" x-text="clockDesc"></span></p>
                            <p class="text-gray-400 text-xs mt-1">1 game month = 30 game days. Salary deposits every game month.</p>
                            <p class="text-gray-400 text-xs">Bills deducted monthly. Investments mature faster at higher speeds.</p>
                        </div>
                    </div>
                    <div class="mt-5 pt-5" style="border-top:1px solid rgba(255,255,255,.06);">
                        <label class="text-xs text-gray-400 mb-1 block font-bold">Max "While You Were Away" Catch-up (game days)
                            <x-help-tip text="Ceiling on how many game days a single login may simulate, no matter how long the player was actually gone. Anything beyond it is discarded, never banked — so a returning player faces a manageable catch-up instead of months of stacked bills. A hard engine ceiling of 60 ticks still applies on top, and free accounts are further limited by their own catch-up gate. This is a flat game-days count and does not change when you change the clock speed above." example="30 — a returning player replays at most one game month, however long they were away" />
                        </label>
                        <input type="number" min="1" max="3650" step="1" x-model.number="clock.max_catchup" @input="updateClockDesc()" class="input-field max-w-xs">
                        <p class="text-xs text-gray-500 mt-1.5">No matter how long a player was actually away — an hour or a year — a single login only ever simulates up to this many game days. The rest of the absence is simply not simulated (never banked for later). This is a flat game-days number, independent of the clock speed above.</p>
                        <p class="text-xs text-amber-400 mt-1.5 font-semibold" x-show="catchupDesc" x-text="catchupDesc"></p>
                    </div>
                    <button @click="saveClock()" :disabled="clockSaving" class="mt-4 w-full py-2.5 rounded-xl text-sm font-bold transition-all" style="background:linear-gradient(135deg,rgba(245,158,11,0.3),rgba(217,119,6,0.2));border:1px solid rgba(245,158,11,0.4);color:#fde68a;">
                        <span x-show="!clockSaving">⏱️ Save Game Clock</span><span x-show="clockSaving">Saving…</span>
                    </button>
                    <div x-show="clockMsg" x-transition class="mt-2 text-xs font-bold text-center" :class="clockOk ? 'text-emerald-400' : 'text-red-400'" x-text="clockMsg"></div>
                </div>

                {{-- Free-for-All Toggle --}}
                <div class="settings-section lg:col-span-2">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);">🔓</div>
                            <div>
                                <h3 class="font-black text-white">Free-for-All Mode
                                    <x-help-tip text="One switch that overrides every paywall gate at once — while ON, every player is treated as premium regardless of subscription status, so no feature limit, quest cap, catch-up limit or upsell nag applies to anyone. It sits above the Free Plan Gates panel, meaning nothing you configure there has any effect until this is turned back OFF. Built for school events, demos and launch weeks; leaving it ON in normal operation means the platform earns nothing." example="Turn ON for a school demo day, OFF again the same evening" />
                                </h3>
                                <p class="text-xs text-gray-400">When ON, all players get full access — subscription paywall is completely disabled.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <div class="relative">
                                    <input type="checkbox" x-model="freeForAll" class="sr-only peer">
                                    <div class="w-12 h-6 rounded-full transition-colors" :class="freeForAll ? 'bg-emerald-500' : 'bg-white/10'"></div>
                                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="freeForAll ? 'translate-x-6' : ''"></div>
                                </div>
                                <span class="text-sm font-bold" :class="freeForAll ? 'text-emerald-400' : 'text-gray-500'" x-text="freeForAll ? 'FREE FOR ALL — ON' : 'Subscription required — OFF'"></span>
                            </label>
                            <button @click="saveFreeForAll()" :disabled="freeForAllSaving"
                                    class="px-5 py-2 rounded-xl text-sm font-bold transition-all"
                                    style="background:linear-gradient(135deg,rgba(16,185,129,0.3),rgba(5,150,105,0.2));border:1px solid rgba(16,185,129,0.4);color:#6ee7b7;">
                                <span x-show="!freeForAllSaving">Save</span>
                                <span x-show="freeForAllSaving">Saving…</span>
                            </button>
                        </div>
                    </div>
                    <div x-show="freeForAllMsg" x-transition class="mt-3 text-xs font-bold text-center text-emerald-400" x-text="freeForAllMsg"></div>
                </div>

                {{-- Contact Us channels --}}
                <div class="settings-section lg:col-span-2" x-data="contactSettingsPanel()">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(37,211,102,0.15);border:1px solid rgba(37,211,102,0.3);">💬</div>
                        <div>
                            <h3 class="font-black text-white">Contact Us Channels</h3>
                            <p class="text-xs text-gray-400">Shown as buttons at the bottom of the landing page. Leave any field blank to hide that button — nothing forces all three.</p>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">📧 Support Email
                                <x-help-tip text="Public support address behind the Email button on the landing page. Leave it blank and that button simply doesn't render — so only fill it in for an inbox someone actually watches." example="support@moski.org" />
                            </label>
                            <input type="email" x-model="email" placeholder="support@moski.org" class="input-field">
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">💬 WhatsApp Number
                                <x-help-tip text="Number the landing page's WhatsApp button opens a chat with — usually the fastest support channel for Kenyan parents and teachers. Must be in international form with the country code and no plus sign or spaces, or the wa.me link silently opens an empty chat. Blank hides the button." example="254712345678" />
                            </label>
                            <input type="text" x-model="whatsapp" placeholder="254712345678" class="input-field">
                            <p class="text-xs text-gray-500 mt-1">Include country code, no + or spaces needed — e.g. 2547XXXXXXXX.</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">📞 Phone Number
                                <x-help-tip text="Callable number behind the landing page's Call button — displayed as typed, so format it for humans to read. Mostly used by schools evaluating a seat package. Blank hides the button." example="+254 7XX XXX XXX" />
                            </label>
                            <input type="text" x-model="phone" placeholder="+254 7XX XXX XXX" class="input-field">
                        </div>
                    </div>
                    <button @click="save()" :disabled="saving" class="mt-4 px-5 py-2 rounded-xl text-sm font-bold transition-all" style="background:linear-gradient(135deg,rgba(37,211,102,0.25),rgba(16,163,74,0.15));border:1px solid rgba(37,211,102,0.4);color:#6ee7b7;">
                        <span x-show="!saving">💾 Save Contact Channels</span>
                        <span x-show="saving">Saving…</span>
                    </button>
                    <div x-show="msg" x-transition class="mt-3 text-xs font-bold text-center text-emerald-400" x-text="msg"></div>
                </div>

                {{-- Push Notifications (VAPID) --}}
                <div class="settings-section lg:col-span-2" x-data="pushSettingsPanel()">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);">🔔</div>
                        <div>
                            <h3 class="font-black text-white">Push Notifications</h3>
                            <p class="text-xs text-gray-400">One-time setup so PesaQuest can notify players even when the game is closed — bill deadlines, payday, crisis warnings and celebrations.</p>
                        </div>
                    </div>

                    <template x-if="hasKeys">
                        <div class="rounded-xl p-4 mb-4" style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);">
                            <p class="text-sm font-bold text-emerald-300">✅ Push notifications are configured and live.</p>
                            <p class="text-xs text-gray-400 mt-1">Quiet hours: 9:30pm–6am (Africa/Nairobi) · Max 4 pushes/player/day · Minors &amp; school accounts never receive subscription-nudge pushes.</p>
                        </div>
                    </template>
                    <template x-if="!hasKeys">
                        <div class="rounded-xl p-4 mb-4" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);">
                            <p class="text-sm font-bold text-amber-300">⚠️ Not configured yet — players cannot receive push notifications until you generate keys below.</p>
                        </div>
                    </template>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Contact email (VAPID subject)
                                <x-help-tip text="Required by the web-push spec: a real contact address stored as a mailto: subject, which browser push services (Google, Mozilla, Apple) use to reach you about your push traffic — for example if your sends start looking abusive. Never shown to players. It is saved as part of generating the keys, so set it before clicking Generate." example="support@moski.org" />
                            </label>
                            <input type="email" x-model="subject" placeholder="support@moski.org" class="input-field">
                            <p class="text-xs text-gray-500 mt-1">Browsers may use this to contact you about your push traffic. Never shown to players.</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400 mb-1 block font-bold">Public key</label>
                            <input type="text" :value="publicKey" readonly class="input-field font-mono text-xs" style="opacity:.7;" placeholder="Generate keys to see this">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-4 flex-wrap">
                        <button @click="generate()" :disabled="generating"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all"
                                style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;">
                            <span x-show="!generating" x-text="hasKeys ? '🔄 Regenerate Keys' : '🔑 Generate VAPID Keys'"></span>
                            <span x-show="generating">Generating…</span>
                        </button>
                        <x-help-tip text="One-time setup: creates the public/private VAPID key pair that lets browsers accept push from this server, and stores the contact email above as the mailto: subject. Until you press this, no player can receive any push at all. Pressing it again later regenerates the pair and wipes every existing player subscription — harmless the first time, disruptive afterwards, so only repeat it if the keys have leaked." example="Click once on Day 1, right after filling in the contact email" />
                        <button x-show="hasKeys" @click="testPush()" :disabled="testing"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all"
                                style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#6ee7b7;">
                            <span x-show="!testing">🔔 Send Test Push to Myself</span>
                            <span x-show="testing">Sending…</span>
                        </button>
                    </div>
                    <p x-show="hasKeys" class="text-xs text-red-400/80 mt-2">⚠️ Regenerating invalidates every player's existing push subscription — they'll need to re-enable notifications. Only do this if keys have leaked.</p>
                    <p x-show="hasKeys" class="text-xs text-gray-500 mt-2">"Send Test Push to Myself" delivers directly to YOUR account right now, ignoring quiet hours and the daily cap — the fastest way to confirm push actually works end-to-end. Requires you to have enabled push notifications on this device already (Profile → Notification Settings).
                        <x-help-tip text="Diagnostic button, not a real send: it pushes straight to your own logged-in device, deliberately ignoring quiet hours (9:30pm–6am Nairobi), the 4-per-day cap and category preferences. That makes it the one test that isolates a genuine configuration fault from normal filtering — if this fails, push is broken; if this works but a broadcast reached nobody, the recipients were simply filtered out." example="Use it right after generating keys, and again whenever a broadcast seems to have vanished" />
                    </p>
                    <div x-show="msg" x-transition class="mt-3 text-xs font-bold" :class="msgOk ? 'text-emerald-400' : 'text-red-400'" x-text="msg"></div>
                    <div x-show="testMsg" x-transition class="mt-2 text-xs font-bold" :class="testOk ? 'text-emerald-400' : 'text-red-400'" x-text="testMsg"></div>
                </div>

                {{-- pesAI Settings --}}
                <div class="settings-section lg:col-span-2" x-data="{ showKey: false }">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
                            @php $aiIcon = \App\Models\Setting::get('ai_agent_icon','🤖'); @endphp
                            @if(str_starts_with($aiIcon,'http'))
                                <img src="{{ $aiIcon }}" class="w-full h-full object-cover">
                            @else
                                {{ $aiIcon }}
                            @endif
                        </div>
                        <div>
                            <h3 class="font-black text-white">pesAI Settings</h3>
                            <p class="text-xs text-gray-400">Configure the AI financial mentor — model, API key, persona icon, and daily limits.</p>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">

                        {{-- Left column --}}
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">OpenRouter API Key
                                    <x-help-tip text="Credential PesaQuest uses to reach the AI mentor's language model through OpenRouter. With no key saved, pesAI simply refuses to answer and the test button below reports it. Billing follows the model you pick — a free model costs nothing even with a key attached, a paid one bills this account per message." example="sk-or-v1-… — created free at openrouter.ai/keys" />
                                </label>
                                <div class="flex gap-2">
                                    <input :type="showKey ? 'text' : 'password'" x-model="ai.api_key"
                                           placeholder="sk-or-v1-••••••••••••" class="input-field flex-1">
                                    <button @click="showKey = !showKey" class="px-2 rounded-lg text-xs text-gray-400 hover:text-white transition-colors" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);" title="Show/hide">
                                        <span x-text="showKey ? '🙈' : '👁'"></span>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Get free key at <span class="text-violet-400 font-semibold">openrouter.ai/keys</span></p>
                            </div>

                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">AI Model
                                    <x-help-tip text="Which language model answers every player question. This is the direct cost-versus-quality trade-off: the free-tier models cost nothing but share a 200-request-per-day ceiling across the whole platform, while paid models give better financial explanations and bill your OpenRouter account per message with no daily ceiling. Changing it takes effect on the very next question — no redeploy." example="Llama 3.1 8B (Free) for everyday use; Llama 3.1 70B (Paid) if answer quality matters more than cost" />
                                </label>
                                <select x-model="ai.model" class="input-field">
                                    <option value="meta-llama/llama-3.1-8b-instruct:free">⭐ Llama 3.1 8B (Free — Recommended)</option>
                                    <option value="meta-llama/llama-3.2-3b-instruct:free">Llama 3.2 3B (Free — Fastest)</option>
                                    <option value="google/gemma-2-9b-it:free">Google Gemma 2 9B (Free)</option>
                                    <option value="mistralai/mistral-7b-instruct:free">Mistral 7B (Free)</option>
                                    <option value="qwen/qwen-2-7b-instruct:free">Qwen 2 7B (Free)</option>
                                    <option value="microsoft/phi-3-mini-128k-instruct:free">Microsoft Phi-3 Mini (Free)</option>
                                    <option value="meta-llama/llama-3.1-70b-instruct">Llama 3.1 70B (Paid — Best quality)</option>
                                    <option value="anthropic/claude-3-haiku">Claude 3 Haiku (Paid)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">All ":free" models have 0 cost — 200 req/day limit on free tier.</p>
                            </div>

                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Daily Message Limit Per User
                                    <x-help-tip text="How many questions one player may ask pesAI in a single real day before being cut off until midnight. It is the cost brake on the whole feature: a low number keeps a classroom of players inside the free tier's shared daily quota, while a high number risks one enthusiastic user exhausting the API for everyone. Accepts 1–100." example="10 — enough for genuine curiosity, low enough that 20 players can't drain a free-tier quota" />
                                </label>
                                <input type="number" x-model="ai.daily_limit" min="1" max="100" class="input-field" style="max-width:120px;">
                                <p class="text-xs text-gray-500 mt-1">Resets at midnight. Prevents API exhaustion.</p>
                            </div>
                        </div>

                        {{-- Right column: icon + test --}}
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-gray-400 mb-1 block font-bold">Agent Icon / Avatar
                                    <x-help-tip text="The face of the AI mentor — shown on the floating chat button and beside every pesAI reply. Accepts either a single emoji or a public image URL; a URL must be reachable from the player's browser or the avatar renders blank. Purely cosmetic, but it is how younger players recognise the mentor as a character rather than a settings menu." example="🤖 — or https://moski.org/img/mama-pesa.png" />
                                </label>
                                <input type="text" x-model="ai.icon"
                                       placeholder="🤖 or https://… image URL"
                                       class="input-field">
                                <p class="text-xs text-gray-500 mt-1">Paste an emoji or a public image URL. Shows on the chat button.</p>
                                {{-- Live preview --}}
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Preview:</span>
                                    <div class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center text-xl font-bold shadow-lg" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
                                        <template x-if="ai.icon && ai.icon.startsWith('http')">
                                            <img :src="ai.icon" class="w-full h-full object-cover" onerror="this.style.display='none'">
                                        </template>
                                        <template x-if="!(ai.icon && ai.icon.startsWith('http'))">
                                            <span x-text="ai.icon || '🤖'"></span>
                                        </template>
                                    </div>
                                    <span class="text-xs text-gray-400 font-bold">pesAI</span>
                                </div>
                            </div>

                            {{-- Test configuration --}}
                            <div>
                                <label class="text-xs text-gray-400 mb-2 block font-bold">Test Configuration
                                    <x-help-tip text="Sends one real message to the saved key and model and prints pesAI's actual reply below — the quickest way to tell a bad key from a bad model choice, since each failure comes back with the provider's own error text. It uses the stored settings, so press Save first or you'll be testing the previous configuration." example="Run it once after saving a new key, and again after switching models" />
                                </label>
                                <button @click="testAi()" :disabled="aiTesting"
                                        class="w-full py-2 rounded-xl text-sm font-bold transition-all"
                                        style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#34d399;">
                                    <span x-show="!aiTesting">🧪 Send Test Message</span>
                                    <span x-show="aiTesting">Testing…</span>
                                </button>
                                <div x-show="aiTestMsg" x-transition class="mt-2 rounded-xl p-3 text-xs" :class="aiTestOk ? 'text-emerald-300' : 'text-red-300'"
                                     :style="aiTestOk ? 'background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2)' : 'background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2)'">
                                    <p class="font-bold mb-1" x-text="aiTestOk ? '✅ pesAI replied:' : '❌ Error:'"></p>
                                    <p class="leading-relaxed" x-text="aiTestMsg"></p>
                                    <p x-show="aiTestModel" class="text-gray-500 mt-1 text-[10px]" x-text="'Model: ' + aiTestModel"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button @click="saveAi()" :disabled="aiSaving" class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                                style="background:linear-gradient(135deg,rgba(124,58,237,0.35),rgba(219,39,119,0.25));border:1px solid rgba(124,58,237,0.45);color:#c4b5fd;">
                            <span x-show="!aiSaving">💾 Save pesAI Settings</span>
                            <span x-show="aiSaving">Saving…</span>
                        </button>
                    </div>
                    <div x-show="aiMsg" x-transition class="mt-2 text-xs font-bold text-center" :class="aiOk ? 'text-emerald-400' : 'text-red-400'" x-text="aiMsg"></div>
                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: ACTIVITY
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='activity'">
            <div class="panel rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="font-bold text-white text-base">Recent Player Activity</h2>
                </div>
                <div class="divide-y divide-white/[0.04]">
                    @forelse($recentActivity as $activity)
                    <div class="px-6 py-3 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold bg-emerald-500/10 text-emerald-400">
                                {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-white font-semibold truncate">{{ $activity->user->name }}</p>
                                <p class="text-xs text-gray-500">Level {{ $activity->level }} · {{ number_format($activity->points_total) }} pts · Ksh {{ number_format($activity->balance) }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600 flex-shrink-0">{{ $activity->last_played_at?->diffForHumans() }}</span>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-gray-600 text-sm">No activity yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TAB: ROADMAP
             ════════════════════════════════════════ --}}
        <div x-show="activeTab==='roadmap'">
            <div class="panel rounded-2xl p-6">
                <h2 class="font-black text-xl text-white mb-2">PesaQuest Feature Roadmap</h2>
                <p class="text-sm text-gray-400 mb-6">Planned creative improvements to level up the platform. Check off as implemented.</p>

                @php
                $roadmap = [
                    ['done' => true,  'label' => 'Landing page hero side-by-side phone mock',         'cat' => 'UI'],
                    ['done' => true,  'label' => 'Dark overlay on hero image for text readability',    'cat' => 'UI'],
                    ['done' => true,  'label' => 'Smart Money Tools dashboard section (4 interactive tools)', 'cat' => 'Feature'],
                    ['done' => true,  'label' => 'Subscription plans (1mo / 3mo / 6mo / 12mo)',       'cat' => 'Monetization'],
                    ['done' => true,  'label' => 'Safaricom Daraja M-Pesa STK Push integration',      'cat' => 'Payments'],
                    ['done' => true,  'label' => 'Auto-approve subscription via Daraja callback',     'cat' => 'Payments'],
                    ['done' => true,  'label' => 'Manual subscription approval by admin',             'cat' => 'Admin'],
                    ['done' => true,  'label' => 'Admin user password reset',                         'cat' => 'Admin'],
                    ['done' => true,  'label' => 'SMTP email settings in admin panel',                'cat' => 'Admin'],
                    ['done' => true,  'label' => 'M-Pesa transaction log in admin',                   'cat' => 'Admin'],
                    ['done' => true,  'label' => 'Subscription plan price/name editing in admin',     'cat' => 'Admin'],
                    ['done' => false, 'label' => 'Leaderboard page — top 10 players with animated rank cards', 'cat' => 'Feature'],
                    ['done' => false, 'label' => 'Scenario analytics — decision heatmap for GameSet admins',   'cat' => 'Analytics'],
                    ['done' => false, 'label' => 'Referral system — earn bonus Ksh for inviting friends',      'cat' => 'Growth'],
                    ['done' => false, 'label' => 'Seasonal challenges — time-limited scenarios with special badges', 'cat' => 'Game'],
                    ['done' => false, 'label' => 'Progress sharing — auto-generate shareable "Level X" card image', 'cat' => 'Social'],
                    ['done' => false, 'label' => 'GameSet JSON import/export for node tree backup/sharing',    'cat' => 'Admin'],
                ];
                $catColors = [
                    'UI' => '#a5b4fc', 'Feature' => '#34d399', 'Monetization' => '#fbbf24',
                    'Payments' => '#6ee7b7', 'Admin' => '#f472b6', 'Analytics' => '#60a5fa',
                    'Growth' => '#fb923c', 'Game' => '#c084fc', 'Social' => '#f9a8d4',
                ];
                $done  = count(array_filter($roadmap, fn($r) => $r['done']));
                $total = count($roadmap);
                @endphp

                {{-- Progress --}}
                <div class="mb-6">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-400">{{ $done }} of {{ $total }} features shipped</span>
                        <span class="font-bold text-emerald-400">{{ round($done/$total*100) }}%</span>
                    </div>
                    <div class="h-3 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.05);">
                        <div class="h-full rounded-full transition-all" style="width:{{ round($done/$total*100) }}%;background:linear-gradient(90deg,#10b981,#6366f1);"></div>
                    </div>
                </div>

                <div class="space-y-2">
                    @foreach($roadmap as $item)
                    <div class="flex items-center gap-4 px-4 py-3 rounded-xl {{ $item['done'] ? '' : '' }}"
                         style="{{ $item['done'] ? 'background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.12);' : 'background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);' }}">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-sm"
                             style="{{ $item['done'] ? 'background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.4);' : 'background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);' }}">
                            {{ $item['done'] ? '✓' : '○' }}
                        </div>
                        <span class="flex-1 text-sm {{ $item['done'] ? 'text-white' : 'text-gray-400' }}">{{ $item['label'] }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0"
                              style="background:{{ $catColors[$item['cat']] ?? '#9ca3af' }}18;border:1px solid {{ $catColors[$item['cat']] ?? '#9ca3af' }}35;color:{{ $catColors[$item['cat']] ?? '#9ca3af' }};">
                            {{ $item['cat'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ── Create User Modal ── --}}
    <div x-show="cuModal.open" x-cloak @click.self="cuModal.open=false"
         class="modal-backdrop fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="modal-box p-6 space-y-4" x-transition style="max-width:480px;">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-lg">➕ Create New User</h3>
                <button @click="cuModal.open=false" class="text-gray-500 hover:text-white w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white/5 text-xl leading-none">✕</button>
            </div>

            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-400 font-semibold mb-1 block">Full Name
                            <x-help-tip text="The player's display name — it appears on their profile, on leaderboards and anywhere their progress is shown to others." example="Jane Wanjiku" />
                        </label>
                        <input type="text" x-model="cuModal.name" placeholder="e.g. Jane Wanjiku" class="input-field w-full" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 font-semibold mb-1 block">Age Group
                            <x-help-tip text="Decides which age-banded jobs, courses and quests this player is shown. Leave blank and they choose it themselves during the first-login wizard." example="13-17 for a high-school student" />
                        </label>
                        <select x-model="cuModal.age_group" class="input-field w-full">
                            <option value="">— optional —</option>
                            <option value="8-12">🧒 Ages 8–12</option>
                            <option value="13-17">🎒 Ages 13–17</option>
                            <option value="18-25">🎓 Ages 18–25</option>
                            <option value="26+">💼 Ages 26+</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-400 font-semibold mb-1 block">Email Address
                        <x-help-tip text="Their login ID and the address password resets are sent to. Must be unique — no two accounts can share an email." example="jane.wanjiku@example.com" />
                    </label>
                    <input type="email" x-model="cuModal.email" placeholder="user@example.com" class="input-field w-full" />
                </div>
                <div>
                    <label class="text-xs text-gray-400 font-semibold mb-1 block">Password
                        <x-help-tip text="The starting password you hand to the player — they can change it later from their profile. Minimum 8 characters; use Generate for a strong random one." example="Kx7m2Qp9" />
                    </label>
                    <div class="flex gap-2">
                        <input :type="cuModal.showPw ? 'text' : 'password'" x-model="cuModal.password"
                               placeholder="Min. 8 characters" class="input-field flex-1" />
                        <button type="button" @click="cuModal.showPw = !cuModal.showPw"
                                class="px-3 rounded-xl text-gray-400 hover:text-white transition-colors"
                                style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);"
                                x-text="cuModal.showPw ? '🙈' : '👁'"></button>
                        <button type="button" @click="cuModal.password = genPw()"
                                class="px-3 rounded-xl text-xs font-bold whitespace-nowrap transition-all"
                                style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">
                            Generate
                        </button>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-400 font-semibold mb-1 block">Role
                        <x-help-tip text="Player is a normal game account. GameSet unlocks the content portal (courses, quests, jobs, economy tuning). Admin unlocks this whole panel — users, money and plans." example="Player for students, GameSet for your content team" />
                    </label>
                    <div class="flex gap-2">
                        <template x-for="r in [{v:'player',l:'🎮 Player'},{v:'gameset',l:'🎛 GameSet'},{v:'admin',l:'🛡 Admin'}]">
                            <button type="button"
                                    @click="cuModal.role = r.v"
                                    :class="cuModal.role === r.v
                                        ? 'bg-indigo-500/25 border-indigo-500/60 text-indigo-200'
                                        : 'border-white/10 text-gray-400 hover:border-white/25 hover:text-white'"
                                    class="flex-1 py-2 rounded-xl text-xs font-bold border transition-all"
                                    x-text="r.l"></button>
                        </template>
                    </div>
                </div>
            </div>

            <div x-show="cuModal.error" x-transition class="text-xs text-red-400 font-semibold bg-red-500/10 border border-red-500/20 rounded-xl px-3 py-2" x-text="cuModal.error"></div>

            <div class="flex gap-3 pt-1">
                <button @click="cuModal.open=false" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-white/10 text-gray-400 hover:text-white transition-colors">Cancel</button>
                <button @click="doCreateUser()" :disabled="cuModal.saving"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;">
                    <span x-show="!cuModal.saving">Create User</span>
                    <span x-show="cuModal.saving">Creating…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Subscribe Modal ── --}}
    <div x-show="subModal.open" x-cloak @click.self="subModal.open=false"
         class="modal-backdrop fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="modal-box p-6 space-y-4" x-transition>
            <h3 class="font-black text-lg">Grant Subscription</h3>
            <p class="text-sm text-gray-400">Granting premium access to <strong x-text="subModal.userName" class="text-white"></strong></p>
            <div>
                <label class="text-xs text-gray-400 font-semibold mb-1.5 block">Subscription Plan
                    <x-help-tip text="Which plan to hand this player for free. Access starts now and ends after the plan's duration, and any subscription they already have is cancelled and replaced." example="Monthly — premium until 30 days from today" />
                </label>
                <select x-model="subModal.plan" class="input-field w-full">
                    @foreach($plans as $plan)
                    <option value="{{ $plan->key }}">{{ $plan->name }} ({{ $plan->durationLabel() }}) – {{ $plan->formattedPrice() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-400 font-semibold mb-1.5 block">Payment Reference (optional)
                    <x-help-tip text="Your own record of how this was paid for, stored on the subscription so payments can be reconciled later. Leave blank for comped or sponsored accounts." example="MPESA-QK12ZX9P" />
                </label>
                <input type="text" x-model="subModal.reference" placeholder="e.g. MPESA-XXXXXXX" class="input-field w-full" />
            </div>
            <div class="flex gap-3 pt-2">
                <button @click="subModal.open=false" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-white/10 text-gray-400 hover:text-white transition-colors">Cancel</button>
                <button x-show="subModal.hasSub" @click="revokeSubscription(subModal.userId,subModal.userName);subModal.open=false;" class="py-2.5 px-4 rounded-xl text-sm font-semibold bg-red-500/15 border border-red-500/30 text-red-400">Revoke</button>
                <button @click="grantSubscription()" class="flex-1 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-amber-500 to-yellow-500 text-black hover:from-amber-400 hover:to-yellow-400 transition-all shadow-lg shadow-amber-500/20">Grant Access</button>
            </div>
        </div>
    </div>

    {{-- ── Password Reset Modal ── --}}
    <div x-show="pwModal.open" x-cloak @click.self="pwModal.open=false"
         class="modal-backdrop fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="modal-box p-6 space-y-4" x-transition>
            <h3 class="font-black text-lg">🔑 Password Reset</h3>
            <template x-if="!pwModal.result">
                <div>
                    <p class="text-sm text-gray-400">Reset password for <strong x-text="pwModal.userName" class="text-white"></strong>?</p>
                    <p class="text-xs text-gray-500 mt-2">A temporary password will be generated and emailed to the user (if SMTP is configured).</p>
                    <div class="flex gap-3 mt-4">
                        <button @click="pwModal.open=false" class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-white/10 text-gray-400">Cancel</button>
                        <button @click="doReset()" :disabled="pwModal.loading"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all" style="background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.35);color:#fca5a5;">
                            <span x-show="!pwModal.loading">Reset Password</span>
                            <span x-show="pwModal.loading">Resetting…</span>
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="pwModal.result">
                <div>
                    <div class="text-center py-2">
                        <div class="text-4xl mb-3">✅</div>
                        <div class="text-sm text-white font-bold mb-3">Password Reset</div>
                        <div class="text-xs text-gray-400 mb-2">Temporary password:</div>
                        <div class="font-mono text-lg font-black text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3 tracking-widest" x-text="pwModal.result"></div>
                        <div class="text-xs text-gray-500 mt-2" x-text="pwModal.emailSent ? '✉️ Email sent to user' : '⚠️ Email not sent (check SMTP settings)'"></div>
                    </div>
                    <button @click="pwModal.open=false;pwModal.result=null;" class="mt-4 w-full py-2.5 rounded-xl text-sm font-semibold border border-white/10 text-gray-400">Close</button>
                </div>
            </template>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition.opacity
         class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl text-sm font-semibold shadow-xl"
         :class="toast.ok ? 'bg-emerald-500/20 border border-emerald-500/40 text-emerald-300' : 'bg-red-500/20 border border-red-500/40 text-red-300'"
         x-text="toast.message">
    </div>

<script>
const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

function adminPanel() {
    return {
        activeTab: 'users',
        search: '',
        subFilter: 'all',
        payFilter: 'all',
        toast: { show: false, ok: true, message: '' },
        subModal: { open: false, userId: null, userName: '', plan: {{ Js::from($plans->first()?->key ?? 'monthly') }}, reference: '', hasSub: false },
        pwModal:  { open: false, userId: null, userName: '', loading: false, result: null, emailSent: false },
        cuModal:  { open: false, name: '', email: '', password: '', age_group: '', role: 'player', saving: false, error: '', showPw: false },

        matchesSearch(name, email) {
            if (!this.search) return true;
            const q = this.search.toLowerCase();
            return name.toLowerCase().includes(q) || email.toLowerCase().includes(q);
        },
        showToast(message, ok = true) {
            this.toast = { show: true, ok, message };
            setTimeout(() => this.toast.show = false, 3500);
        },

        async toggleRole(userId, role, btn) {
            try {
                const res = await fetch(`/admin/users/${userId}/${role}`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.error || 'Error.', false); return; }
                const field = role === 'admin' ? 'is_admin' : 'is_gameset';
                const isActive = data[field];
                btn.dataset.active = isActive ? '1' : '0';
                btn.className = 'toggle-btn ' + (isActive ? 'toggle-on' : 'toggle-off');
                this.showToast('Role updated.');
            } catch { this.showToast('Network error.', false); }
        },

        openSubscribeModal(userId, userName, hasSub) {
            this.subModal = { open: true, userId, userName, plan: {{ Js::from($plans->first()?->key ?? 'monthly') }}, reference: '', hasSub };
        },
        async grantSubscription() {
            try {
                const res = await fetch(`/admin/users/${this.subModal.userId}/subscribe`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ plan: this.subModal.plan, reference: this.subModal.reference })
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.message || 'Error.', false); return; }
                this.subModal.open = false;
                this.showToast(`Subscription granted until ${data.ends_at_human}`);
                setTimeout(() => window.location.reload(), 1500);
            } catch { this.showToast('Network error.', false); }
        },
        async revokeSubscription(userId, userName) {
            if (!confirm(`Revoke subscription for ${userName}?`)) return;
            try {
                const res = await fetch(`/admin/users/${userId}/subscribe`, {
                    method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast('Error.', false); return; }
                this.showToast(`Subscription revoked.`);
                setTimeout(() => window.location.reload(), 1500);
            } catch { this.showToast('Network error.', false); }
        },
        async approveSubscription(subId, userName) {
            if (!confirm(`Approve subscription for ${userName}?`)) return;
            try {
                const res = await fetch(`/admin/subscriptions/${subId}/approve`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.error || 'Error.', false); return; }
                this.showToast(data.stacked ? `Approved — scheduled to start ${data.starts_at_human}` : `Subscription approved until ${data.ends_at_human}`);
                setTimeout(() => window.location.reload(), 1500);
            } catch { this.showToast('Network error.', false); }
        },
        async pauseSubscription(subId, userName) {
            if (!confirm(`Pause ${userName}'s subscription? No days will be used while paused.`)) return;
            try {
                const res = await fetch(`/admin/subscriptions/${subId}/pause`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.error || 'Error.', false); return; }
                this.showToast('Subscription paused.');
                setTimeout(() => window.location.reload(), 1500);
            } catch { this.showToast('Network error.', false); }
        },
        async resumeSubscription(subId, userName) {
            if (!confirm(`Resume ${userName}'s subscription?`)) return;
            try {
                const res = await fetch(`/admin/subscriptions/${subId}/resume`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.error || 'Error.', false); return; }
                this.showToast(`Resumed — now expires ${data.ends_at_human}`);
                setTimeout(() => window.location.reload(), 1500);
            } catch { this.showToast('Network error.', false); }
        },

        openCreateUser() {
            this.cuModal = { open: true, name: '', email: '', password: '', age_group: '', role: 'player', saving: false, error: '', showPw: false };
        },
        genPw() {
            const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$';
            return Array.from({length: 12}, () => chars[Math.floor(Math.random() * chars.length)]).join('');
        },
        async doCreateUser() {
            this.cuModal.error = '';
            if (!this.cuModal.name.trim() || !this.cuModal.email.trim() || !this.cuModal.password) {
                this.cuModal.error = 'Name, email and password are required.'; return;
            }
            if (this.cuModal.password.length < 8) {
                this.cuModal.error = 'Password must be at least 8 characters.'; return;
            }
            this.cuModal.saving = true;
            try {
                const res = await fetch('/admin/users', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name:      this.cuModal.name,
                        email:     this.cuModal.email,
                        password:  this.cuModal.password,
                        age_group: this.cuModal.age_group || null,
                        role:      this.cuModal.role,
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    const errs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Error creating user.');
                    this.cuModal.error = errs;
                } else {
                    this.cuModal.open = false;
                    this.showToast(`User "${data.user.name}" created!`);
                    setTimeout(() => window.location.reload(), 1200);
                }
            } catch { this.cuModal.error = 'Network error.'; }
            this.cuModal.saving = false;
        },

        resetPassword(userId, userName) {
            this.pwModal = { open: true, userId, userName, loading: false, result: null, emailSent: false };
        },
        async doReset() {
            this.pwModal.loading = true;
            try {
                const res = await fetch(`/admin/users/${this.pwModal.userId}/password-reset`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.error || 'Error.', false); this.pwModal.open = false; return; }
                this.pwModal.result    = data.temp_pw;
                this.pwModal.emailSent = data.email_sent;
            } catch { this.showToast('Network error.', false); this.pwModal.open = false; }
            this.pwModal.loading = false;
        },
        async toggleActive(userId, userName, currentlyActive, el) {
            const verb = currentlyActive ? 'deactivate' : 'reactivate';
            if (!confirm(`${currentlyActive ? 'Deactivate' : 'Reactivate'} ${userName}? ${currentlyActive ? 'They will be immediately blocked from logging in — this is fully reversible.' : 'They will be able to log in again.'}`)) return;
            try {
                const res = await fetch(`/admin/users/${userId}/active`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.error || 'Error.', false); return; }
                this.showToast(`${userName} ${data.is_active ? 'reactivated' : 'deactivated'}.`);
                setTimeout(() => window.location.reload(), 1200);
            } catch { this.showToast('Network error.', false); }
        },
        async deleteUser(userId, userName) {
            if (!confirm(`Permanently delete ${userName}? This deletes their ENTIRE account — progress, subscriptions, everything. This CANNOT be undone. Consider Deactivate instead unless you're sure.`)) return;
            if (!confirm(`Really sure? Type nothing needed — just confirm once more to permanently delete ${userName}.`)) return;
            try {
                const res = await fetch(`/admin/users/${userId}`, {
                    method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok) { this.showToast(data.error || 'Error.', false); return; }
                this.showToast(data.message || 'Deleted.');
                setTimeout(() => window.location.reload(), 1200);
            } catch { this.showToast('Network error.', false); }
        },
    };
}

function planEditor(id, name, price, desc, isActive, isFeatured, seats, maxClasses) {
    return {
        id, name, price, desc, isActive, isFeatured, seats: seats ?? null, maxClasses: maxClasses ?? null,
        saving: false, saved: false, error: '',
        async save() {
            this.saving = true; this.saved = false; this.error = '';
            try {
                const payload = { name: this.name, price_kes: this.price, description: this.desc, is_active: this.isActive, is_featured: this.isFeatured };
                if (this.seats !== null) payload.seats = this.seats;
                if (this.maxClasses !== null) payload.max_classes = this.maxClasses;
                const res = await fetch(`/admin/plans/${this.id}`, {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok) {
                    this.saved = true;
                    this.price = data.plan.price_kes;
                    setTimeout(() => this.saved = false, 3000);
                } else {
                    this.error = data.message || 'Save failed';
                }
            } catch(e) { this.error = 'Network error'; }
            this.saving = false;
        },
        async deletePlan() {
            if (!confirm(`Delete "${this.name}"? This cannot be undone.`)) return;
            this.saving = true;
            try {
                const res = await fetch(`/admin/plans/${this.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    this.error = data.message || 'Delete failed';
                }
            } catch(e) { this.error = 'Network error'; }
            this.saving = false;
        }
    };
}

function schoolPlanCreator() {
    return {
        createModal: false,
        creating: false,
        createError: '',
        createForm: { name: '', seats: '', max_classes: 3, months: 12, price_kes: '', description: '' },
        openCreate() {
            this.createForm = { name: '', seats: '', max_classes: 3, months: 12, price_kes: '', description: '' };
            this.createError = '';
            this.createModal = true;
        },
        async saveNewPlan() {
            this.creating = true; this.createError = '';
            try {
                const res = await fetch('/admin/plans/school', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.createForm)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.reload();
                } else {
                    this.createError = data.message || Object.values(data.errors ?? {}).flat().join(' ') || 'Failed to create plan.';
                }
            } catch(e) { this.createError = 'Network error.'; }
            this.creating = false;
        }
    };
}

function settingsPanel() {
    return {
        smtp: {
            host:       {{ Js::from(\App\Models\Setting::get('smtp_host', '')) }},
            port:       {{ Js::from(\App\Models\Setting::get('smtp_port', '587')) }},
            username:   {{ Js::from(\App\Models\Setting::get('smtp_username', '')) }},
            password:   '',
            encryption: {{ Js::from(\App\Models\Setting::get('smtp_encryption', 'tls')) }},
            from_email: {{ Js::from(\App\Models\Setting::get('smtp_from_email', '')) }},
            from_name:  {{ Js::from(\App\Models\Setting::get('smtp_from_name', 'PesaQuest')) }},
        },
        google: {
            client_id:     {{ Js::from(\App\Models\Setting::get('google_client_id', '')) }},
            client_secret: '',
            enabled:       {{ \App\Models\Setting::get('google_oauth_enabled', '0') === '1' ? 'true' : 'false' }},
        },
        googleSaving: false, googleMsg: '', googleOk: true,
        mpesa: {
            env:             {{ Js::from(\App\Models\Setting::get('mpesa_env', 'sandbox')) }},
            consumer_key:    '',
            consumer_secret: '',
            shortcode:       {{ Js::from(\App\Models\Setting::get('mpesa_shortcode', '174379')) }},
            passkey:         '',
            account_ref:     {{ Js::from(\App\Models\Setting::get('mpesa_account_ref', 'PesaQuest')) }},
        },
        clock: {
            rate:         {{ Js::from(\App\Models\Setting::get('game_clock_real_hours_per_game_week', '1')) }},
            max_catchup:  {{ Js::from(\App\Models\Setting::get('max_catchup_game_days', '60')) }},
        },
        freeForAll: {{ \App\Models\Setting::get('free_for_all', '0') === '1' ? 'true' : 'false' }},
        freeForAllSaving: false, freeForAllMsg: '',
        ai: {
            api_key:     '',
            model:       {{ Js::from(\App\Models\Setting::get('ai_model', 'meta-llama/llama-3.1-8b-instruct:free')) }},
            daily_limit: {{ Js::from(\App\Models\Setting::get('ai_daily_limit', '15')) }},
            icon:        {{ Js::from(\App\Models\Setting::get('ai_agent_icon', '🤖')) }},
        },
        clockSaving: false, clockMsg: '', clockOk: true,
        clockDesc: '', catchupDesc: '',
        testEmail: '', smtpSaving: false, smtpTesting: false, smtpMsg: '', smtpOk: true,
        mpesaSaving: false, mpesaMsg: '', mpesaOk: true,
        aiSaving: false, aiMsg: '', aiOk: true,
        aiTesting: false, aiTestMsg: '', aiTestOk: true, aiTestModel: '',

        init() { this.updateClockDesc(); },

        async saveSmtp() {
            this.smtpSaving = true; this.smtpMsg = '';
            try {
                const res = await fetch('/admin/settings', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ group: 'smtp', smtp_host: this.smtp.host, smtp_port: this.smtp.port, smtp_username: this.smtp.username, smtp_password: this.smtp.password, smtp_encryption: this.smtp.encryption, smtp_from_email: this.smtp.from_email, smtp_from_name: this.smtp.from_name })
                });
                const data = await res.json();
                this.smtpOk = res.ok; this.smtpMsg = data.message || data.error || 'Saved!';
            } catch { this.smtpOk = false; this.smtpMsg = 'Network error.'; }
            this.smtpSaving = false;
        },
        async saveGoogle() {
            this.googleSaving = true; this.googleMsg = '';
            const payload = { group: 'google_oauth', google_client_id: this.google.client_id, google_oauth_enabled: this.google.enabled ? '1' : '0' };
            // Blank secret is left OUT of the payload entirely (rather than sent
            // as '') so resaving without retyping it never wipes what's already
            // stored — the field is never echoed back to the browser, same as
            // smtp.password/mpesa.consumer_secret above.
            if (this.google.client_secret) payload.google_client_secret = this.google.client_secret;
            try {
                const res = await fetch('/admin/settings', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                this.googleOk = res.ok; this.googleMsg = data.message || data.error || 'Saved!';
                if (res.ok) this.google.client_secret = '';
            } catch { this.googleOk = false; this.googleMsg = 'Network error.'; }
            this.googleSaving = false;
        },
        async testSmtp() {
            if (!this.testEmail) return;
            this.smtpTesting = true; this.smtpMsg = '';
            try {
                const res = await fetch('/admin/settings/smtp-test', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ to: this.testEmail })
                });
                const data = await res.json();
                this.smtpOk = res.ok; this.smtpMsg = data.message || data.error || 'Done.';
            } catch { this.smtpOk = false; this.smtpMsg = 'Network error.'; }
            this.smtpTesting = false;
        },
        updateClockDesc() {
            const r = parseFloat(this.clock.rate);
            const mins = Math.round(r * 60);
            const label = mins < 60 ? `${mins} real minutes` : `${r} real hour${r === 1 ? '' : 's'}`;
            const secPerTick = (r * 3600) / 7;
            const tickLabel = secPerTick < 60
                ? `${Math.round(secPerTick)}s per game day`
                : secPerTick < 3600
                    ? `${Math.round(secPerTick/60)} min per game day`
                    : `${(secPerTick/3600).toFixed(1)} hr per game day`;
            this.clockDesc = `${label} = 1 game week (${tickLabel})`;

            // Flat game-days ceiling — deliberately independent of clock speed above.
            const maxDays = parseInt(this.clock.max_catchup) || 0;
            const months = maxDays / 30;
            this.catchupDesc = maxDays > 0
                ? `A single login never simulates more than ${maxDays} game day${maxDays === 1 ? '' : 's'} (≈${months % 1 === 0 ? months : months.toFixed(1)} game month${months === 1 ? '' : 's'}) — no matter how long the player was actually away.`
                : '';
        },
        async saveClock() {
            this.clockSaving = true; this.clockMsg = '';
            try {
                const res = await fetch('/admin/settings', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        group: 'game_clock',
                        game_clock_real_hours_per_game_week: this.clock.rate,
                        max_catchup_game_days: this.clock.max_catchup,
                    })
                });
                const data = await res.json();
                this.clockOk = res.ok; this.clockMsg = data.message || data.error || 'Saved!';
            } catch { this.clockOk = false; this.clockMsg = 'Network error.'; }
            this.clockSaving = false;
        },
        async saveMpesa() {
            this.mpesaSaving = true; this.mpesaMsg = '';
            try {
                const res = await fetch('/admin/settings', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ group: 'mpesa', mpesa_env: this.mpesa.env, mpesa_consumer_key: this.mpesa.consumer_key, mpesa_consumer_secret: this.mpesa.consumer_secret, mpesa_shortcode: this.mpesa.shortcode, mpesa_passkey: this.mpesa.passkey, mpesa_account_ref: this.mpesa.account_ref })
                });
                const data = await res.json();
                this.mpesaOk = res.ok; this.mpesaMsg = data.message || data.error || 'Saved!';
            } catch { this.mpesaOk = false; this.mpesaMsg = 'Network error.'; }
            this.mpesaSaving = false;
        },
        async saveFreeForAll() {
            this.freeForAllSaving = true; this.freeForAllMsg = '';
            try {
                const res = await fetch('/admin/settings', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ group: 'general', free_for_all: this.freeForAll ? '1' : '0' })
                });
                const data = await res.json();
                this.freeForAllMsg = data.message || data.error || 'Saved!';
            } catch { this.freeForAllMsg = 'Network error.'; }
            this.freeForAllSaving = false;
        },
        async saveAi() {
            this.aiSaving = true; this.aiMsg = '';
            try {
                const payload = { group: 'ai', ai_model: this.ai.model, ai_daily_limit: this.ai.daily_limit, ai_agent_icon: this.ai.icon };
                if (this.ai.api_key) payload.openrouter_api_key = this.ai.api_key;
                const res = await fetch('/admin/settings', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                this.aiOk = res.ok; this.aiMsg = data.message || data.error || 'Saved!';
                if (res.ok) this.ai.api_key = '';
            } catch { this.aiOk = false; this.aiMsg = 'Network error.'; }
            this.aiSaving = false;
        },
        async testAi() {
            this.aiTesting = true; this.aiTestMsg = '';
            try {
                const res = await fetch('/admin/settings/ai-test', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                });
                const data = await res.json();
                this.aiTestOk = res.ok;
                if (res.ok) {
                    this.aiTestMsg = data.reply || 'pesAI is working!';
                    this.aiTestModel = data.model || '';
                } else {
                    this.aiTestMsg = data.error || 'Test failed.';
                    this.aiTestModel = '';
                }
            } catch { this.aiTestOk = false; this.aiTestMsg = 'Network error — could not reach pesAI.'; this.aiTestModel = ''; }
            this.aiTesting = false;
        }
    };
}

function schoolsPanel() {
    return {
        createModal: false,
        saving: false,
        createError: '',
        form: { school_name: '', contact_email: '', seats: 50, max_classes: 3, months: 12, price_kes: 0, notes: '' },

        openCreate() {
            this.form = { school_name: '', contact_email: '', seats: 50, max_classes: 3, months: 12, price_kes: 0, notes: '' };
            this.createError = '';
            this.createModal = true;
        },

        async saveSchool() {
            if (!this.form.school_name.trim() || !this.form.contact_email.trim()) {
                this.createError = 'School name and contact email are required.';
                return;
            }
            this.saving = true; this.createError = '';
            try {
                const res = await fetch('/admin/schools', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (data.success) {
                    this.createModal = false;
                    if (data.school?.teacher_invite_url) {
                        navigator.clipboard.writeText(data.school.teacher_invite_url).catch(() => {});
                        alert('School created!\n\nTeacher invite link (copied to clipboard) — send this to the school contact so they can access the Teacher Portal:\n\n' + data.school.teacher_invite_url);
                    }
                    window.location.reload();
                } else {
                    this.createError = data.message ?? 'Error creating school.';
                }
            } catch { this.createError = 'Network error.'; }
            this.saving = false;
        },

        async confirmDelete(id, name) {
            if (!confirm(`Delete school "${name}"? All member records will also be removed.`)) return;
            try {
                const res = await fetch(`/admin/schools/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                });
                if ((await res.json()).success) window.location.reload();
            } catch { alert('Failed to delete school.'); }
        },

        copyPortalUrl(url) {
            navigator.clipboard.writeText(url).then(() => alert('Portal URL copied to clipboard!'));
        },
    };
}

function quickGrantForm() {
    return {
        userId: '', plan: {{ Js::from($plans->first()?->key ?? 'monthly') }}, reference: '',
        saving: false, msg: '', ok: false,

        async grant() {
            if (!this.userId) return;
            this.saving = true; this.msg = '';
            try {
                const res = await fetch(`/admin/users/${this.userId}/subscribe`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ plan: this.plan, reference: this.reference }),
                });
                const data = await res.json();
                if (data.success) {
                    this.ok = true;
                    this.msg = `✓ Subscription granted until ${data.ends_at_human}. Page will refresh…`;
                    setTimeout(() => window.location.reload(), 1800);
                } else {
                    this.ok = false;
                    this.msg = data.error || 'Failed to grant subscription.';
                }
            } catch(e) { this.ok = false; this.msg = 'Network error.'; }
            finally   { this.saving = false; }
        }
    };
}

function gatesPanel() {
    return {
        saving: false, msg: '', ok: true,
        free: @json($freeGates),
        trial_days: {{ (int) $gateMeta['trial_days'] }},
        upsell_nag_enabled: {{ $gateMeta['upsell_nag_enabled'] ? 'true' : 'false' }},
        upsell_nag_days: {{ (int) $gateMeta['upsell_nag_days'] }},
        max_quests_per_day: {{ (int) $gateMeta['max_quests_per_day'] }},

        async save() {
            this.saving = true; this.msg = '';
            try {
                const res = await fetch('/admin/gates', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({
                        free: this.free,
                        trial_days: this.trial_days,
                        upsell_nag_enabled: (this.upsell_nag_enabled === true || this.upsell_nag_enabled === 'true'),
                        upsell_nag_days: this.upsell_nag_days,
                        max_quests_per_day: this.max_quests_per_day,
                    }),
                });
                const data = await res.json();
                this.ok  = !!data.success;
                this.msg = data.success ? '✓ ' + data.message : (Object.values(data.errors || {}).flat().join(' ') || 'Save failed.');
            } catch (e) { this.ok = false; this.msg = 'Network error.'; }
            finally { this.saving = false; setTimeout(() => this.msg = '', 5000); }
        },
    };
}

function broadcastPanel() {
    return {
        title: '', body: '', audience: 'all', ageGroup: '8-12', schoolId: '', email: '',
        sending: false, error: '', success: '',

        async send() {
            this.error = ''; this.success = '';
            if (!this.title.trim() || !this.body.trim()) { this.error = 'Title and message are required.'; return; }
            if (this.audience === 'school' && !this.schoolId) { this.error = 'Pick a school.'; return; }
            if (this.audience === 'single_user' && !this.email.trim()) { this.error = 'Enter a player email.'; return; }
            if (!confirm(`Send this broadcast to "${this.audience.replace('_',' ')}"? This cannot be undone.`)) return;

            this.sending = true;
            try {
                const res = await fetch('{{ route('admin.broadcast.send') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({
                        title: this.title, body: this.body, audience: this.audience,
                        age_group: this.ageGroup, school_id: this.schoolId, email: this.email,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.success = `✓ Sent to ${data.recipients} player(s).`;
                    this.title = ''; this.body = '';
                } else {
                    this.error = data.error || Object.values(data.errors || {}).flat().join(' ') || 'Could not send broadcast.';
                }
            } catch (e) { this.error = 'Network error.'; }
            finally { this.sending = false; }
        },
    };
}

function contactSettingsPanel() {
    return {
        email:    {{ Js::from(\App\Models\Setting::get('contact_email', '')) }},
        whatsapp: {{ Js::from(\App\Models\Setting::get('contact_whatsapp', '')) }},
        phone:    {{ Js::from(\App\Models\Setting::get('contact_phone', '')) }},
        saving: false, msg: '',
        async save() {
            this.saving = true; this.msg = '';
            try {
                const res = await fetch('/admin/settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ group: 'contact', contact_email: this.email, contact_whatsapp: this.whatsapp, contact_phone: this.phone }),
                });
                const data = await res.json();
                this.msg = data.message || (res.ok ? 'Saved!' : 'Could not save.');
            } catch (e) { this.msg = 'Network error.'; }
            finally { this.saving = false; setTimeout(() => this.msg = '', 4000); }
        },
    }
}

function pushSettingsPanel() {
    return {
        hasKeys:   {{ (bool) \App\Models\Setting::get('vapid_public_key') ? 'true' : 'false' }},
        publicKey: {{ Js::from(\App\Models\Setting::get('vapid_public_key', '')) }},
        subject:   {{ Js::from(str_replace('mailto:', '', \App\Models\Setting::get('vapid_subject', ''))) }},
        generating: false, msg: '', msgOk: true,
        testing: false, testMsg: '', testOk: true,

        async testPush() {
            this.testing = true; this.testMsg = '';
            try {
                const res = await fetch('{{ route('admin.push.test') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                });
                const data = await res.json();
                this.testOk = res.ok && !!data.success;
                this.testMsg = data.message || data.error || 'Something went wrong.';
            } catch (e) { this.testOk = false; this.testMsg = 'Network error.'; }
            finally { this.testing = false; }
        },

        async generate() {
            if (!this.subject) { this.msgOk = false; this.msg = 'Enter a contact email first.'; return; }
            if (this.hasKeys && !confirm('Regenerate VAPID keys? Every player currently subscribed will need to re-enable notifications.')) return;

            this.generating = true; this.msg = '';
            try {
                const res = await fetch('{{ route('admin.push.vapid-generate') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ subject: this.subject }),
                });
                const data = await res.json();
                if (data.success) {
                    this.hasKeys = true; this.publicKey = data.publicKey;
                    this.msgOk = true; this.msg = '✓ Push notifications are now configured.';
                } else {
                    this.msgOk = false; this.msg = data.message || 'Could not generate keys.';
                }
            } catch (e) { this.msgOk = false; this.msg = 'Network error.'; }
            finally { this.generating = false; }
        },
    };
}

function crisesPanel() {
    return {
        showForm: false, saving: false, error: '',
        presets: @json(\App\Http\Controllers\GamesetCrisisController::PRESETS),
        form: { name: '', description: '', icon: '⚠️', effect_type: 'investment_drop', effect_amount: 20, warning_at: '', active_from: '', active_until: '' },

        applyPreset(p) {
            this.form.name          = p.name;
            this.form.icon          = p.icon;
            this.form.description   = p.description;
            this.form.effect_type   = p.effect_type;
            this.form.effect_amount = p.effect_amount;
            if (!this.form.warning_at) this.quickTimes(48);
        },

        fmtDT(d) {
            const p = n => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
        },

        quickTimes(hoursToHit) {
            const now  = new Date();
            const hit  = new Date(now.getTime() + hoursToHit * 3600 * 1000);
            const ends = new Date(hit.getTime() + 24 * 3600 * 1000);
            this.form.warning_at   = this.fmtDT(new Date(now.getTime() + 5 * 60 * 1000));
            this.form.active_from  = this.fmtDT(hit);
            this.form.active_until = this.fmtDT(ends);
        },

        async createCrisis() {
            this.error = '';
            if (!this.form.name || !this.form.description || !this.form.warning_at || !this.form.active_from || !this.form.active_until) {
                this.error = 'All fields are required.'; return;
            }
            this.saving = true;
            try {
                const res = await fetch('/admin/crises', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ ...this.form, is_percentage: true }),
                });
                const data = await res.json();
                if (data.success) { this.showForm = false; window.location.reload(); }
                else { this.error = data.message ?? 'Error creating crisis.'; }
            } catch { this.error = 'Network error.'; }
            finally { this.saving = false; }
        },
    };
}

function couponsPanel() {
    const blank = () => ({ code: '', type: 'percent', value: '', max_redemptions: '', plan_id: '', expires_at: '', note: '' });
    return {
        showForm: false, saving: false, error: '', editingId: null,
        form: blank(),

        openCreate() {
            this.editingId = null;
            this.form = blank();
            this.error = '';
            this.showForm = true;
        },

        edit(c) {
            this.editingId = c.id;
            this.form = {
                code: c.code ?? '',
                type: c.type ?? 'percent',
                value: c.value ?? '',
                max_redemptions: c.max_redemptions ?? '',
                plan_id: c.plan_id ? String(c.plan_id) : '',
                expires_at: c.expires_at ?? '',
                note: c.note ?? '',
            };
            this.error = '';
            this.showForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        async save() {
            this.error = '';
            if (!this.form.code || !this.form.value) { this.error = 'Code and value are required.'; return; }
            this.saving = true;
            const payload = {
                code: this.form.code.toUpperCase().trim(),
                type: this.form.type,
                value: Number(this.form.value),
                max_redemptions: this.form.max_redemptions === '' ? null : Number(this.form.max_redemptions),
                plan_id: this.form.plan_id === '' ? null : Number(this.form.plan_id),
                expires_at: this.form.expires_at === '' ? null : this.form.expires_at,
                note: this.form.note || null,
            };
            try {
                const url = this.editingId ? `/admin/coupons/${this.editingId}` : `{{ route('admin.coupons.create') }}`;
                const res = await fetch(url, {
                    method: this.editingId ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (data.success) { window.location.reload(); }
                else { this.error = data.error ?? data.message ?? 'Error saving coupon.'; }
            } catch { this.error = 'Network error.'; }
            finally { this.saving = false; }
        },

        async toggle(id) {
            try {
                const res = await fetch(`/admin/coupons/${id}/toggle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success) window.location.reload();
                else alert(data.error ?? 'Could not toggle coupon.');
            } catch { alert('Network error.'); }
        },

        async destroy(id) {
            if (!confirm('Delete this coupon? This cannot be undone.')) return;
            try {
                const res = await fetch(`/admin/coupons/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.success) window.location.reload();
                else alert(data.error ?? 'Could not delete coupon.');
            } catch { alert('Network error.'); }
        },
    };
}

async function deleteCrisis(id, btn) {
    if (!confirm('Delete this crisis? This cannot be undone.')) return;
    btn.disabled = true;
    try {
        const res = await fetch(`/admin/crises/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
        });
        if ((await res.json()).success) btn.closest('.panel')?.remove();
    } catch { btn.disabled = false; }
}
</script>
{{-- ════════════════════════════════════════
     TAB: INBOX (NPCs & Life Decisions)
     ════════════════════════════════════════ --}}
<template x-if="activeTab==='inbox'">
<div x-data="inboxAdmin()" class="space-y-6">

    {{-- Sub-tabs --}}
    <div class="flex gap-2 border-b border-white/10 pb-0">
        <button @click="sub='npcs'"    :class="sub==='npcs'    ? 'tab-active' : 'tab-inactive'" class="px-4 py-2.5 text-sm font-semibold transition-all">👥 NPCs</button>
        <button @click="sub='decisions'" :class="sub==='decisions' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2.5 text-sm font-semibold transition-all">🃏 Life Decisions</button>
    </div>

    {{-- ── NPCs ── --}}
    <div x-show="sub==='npcs'" class="space-y-4">
        <div class="panel rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-white text-base">NPC Characters</h2>
                <button @click="showNpcForm=!showNpcForm" class="px-4 py-2 rounded-xl text-sm font-bold text-white transition-all" style="background:rgba(99,102,241,0.2);border:1px solid rgba(99,102,241,0.3);">
                    <span x-text="showNpcForm ? '✕ Cancel' : '+ Add NPC'"></span>
                </button>
            </div>

            {{-- NPC Create Form --}}
            <div x-show="showNpcForm" x-cloak x-transition class="mb-6 p-5 rounded-2xl space-y-4" style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.2);">
                <h3 class="text-sm font-bold text-indigo-300 mb-3">New NPC</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="text-xs text-gray-400 block mb-1">Name</label><input x-model="npcForm.name" class="input-field" placeholder="Kamau Njoroge"></div>
                    <div><label class="text-xs text-gray-400 block mb-1">Nickname / Display Name</label><input x-model="npcForm.nickname" class="input-field" placeholder="Kamau"></div>
                    <div>
                        <label class="text-xs text-gray-400 block mb-1">Role</label>
                        <select x-model="npcForm.role" class="input-field">
                            <option value="friend">Friend</option>
                            <option value="boss">Boss</option>
                            <option value="parent">Parent</option>
                            <option value="landlord">Landlord</option>
                            <option value="investor">Investor/Mentor</option>
                            <option value="relative">Relative</option>
                            <option value="colleague">Colleague</option>
                        </select>
                    </div>
                    <div><label class="text-xs text-gray-400 block mb-1">Cover Color (hex)</label><input x-model="npcForm.cover_color" class="input-field" placeholder="#6366f1" type="color" style="height:42px;"></div>
                    <div class="sm:col-span-2"><label class="text-xs text-gray-400 block mb-1">Avatar URL</label><input x-model="npcForm.avatar_url" class="input-field" placeholder="https://ui-avatars.com/api/?name=K&background=6366f1&color=fff"></div>
                    <div class="sm:col-span-2"><label class="text-xs text-gray-400 block mb-1">Description</label><textarea x-model="npcForm.description" class="input-field" rows="2" placeholder="Short character bio..."></textarea></div>
                    <div class="sm:col-span-2"><label class="text-xs text-gray-400 block mb-1">Personality</label><input x-model="npcForm.personality" class="input-field" placeholder="Friendly but reckless with money..."></div>
                    <div><label class="text-xs text-gray-400 block mb-1">Starting Relationship (0–100)</label><input x-model="npcForm.initial_relationship" type="number" min="0" max="100" class="input-field" value="50"></div>
                </div>
                <button @click="saveNpc()" class="mt-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">
                    Save NPC
                </button>
                <p x-show="npcMsg" x-text="npcMsg" class="text-sm text-emerald-400 mt-2"></p>
            </div>

            {{-- NPC List --}}
            <div class="space-y-3">
                @foreach(\App\Models\Npc::orderBy('name')->get() as $npc)
                <div class="flex items-center gap-4 p-4 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                    <img src="{{ $npc->avatar_url }}" alt="{{ $npc->displayName() }}" class="w-11 h-11 rounded-full flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-bold text-white">{{ $npc->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold capitalize" style="background:{{ $npc->cover_color }}22;color:{{ $npc->cover_color }};border:1px solid {{ $npc->cover_color }}33;">{{ $npc->role }}</span>
                            @if(!$npc->is_active)<span class="text-[10px] px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 border border-red-500/25">Inactive</span>@endif
                        </div>
                        <p class="text-xs text-gray-500 truncate">{{ $npc->personality }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-gray-600">{{ $npc->decisions()->count() }} decisions</span>
                        <button onclick="toggleNpc({{ $npc->id }}, this)"
                                class="text-xs px-3 py-1.5 rounded-lg font-semibold transition-all {{ $npc->is_active ? 'bg-red-500/15 text-red-400 hover:bg-red-500/25 border border-red-500/25' : 'bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 border border-emerald-500/25' }}">
                            {{ $npc->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── LIFE DECISIONS ── --}}
    <div x-show="sub==='decisions'" class="space-y-4">
        <div class="panel rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-white text-base">Life Decisions</h2>
                <button @click="showDecForm=!showDecForm" class="px-4 py-2 rounded-xl text-sm font-bold text-white transition-all" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);">
                    <span x-text="showDecForm ? '✕ Cancel' : '+ Add Decision'"></span>
                </button>
            </div>

            {{-- Decision Create Form --}}
            <div x-show="showDecForm" x-cloak x-transition class="mb-6 p-5 rounded-2xl space-y-4" style="background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15);">
                <h3 class="text-sm font-bold text-emerald-300 mb-2">New Life Decision</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2"><label class="text-xs text-gray-400 block mb-1">Title</label><input x-model="decForm.title" class="input-field" placeholder="Kamau Needs Ksh 3,000"></div>
                    <div class="sm:col-span-2"><label class="text-xs text-gray-400 block mb-1">Body (the scenario description)</label><textarea x-model="decForm.body" class="input-field" rows="3"></textarea></div>
                    <div><label class="text-xs text-gray-400 block mb-1">NPC</label>
                        <select x-model="decForm.npc_id" class="input-field">
                            <option value="">— No NPC —</option>
                            @foreach(\App\Models\Npc::where('is_active',true)->get() as $n)
                            <option value="{{ $n->id }}">{{ $n->displayName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="text-xs text-gray-400 block mb-1">Category</label>
                        <select x-model="decForm.category" class="input-field">
                            <option value="social">Social</option>
                            <option value="career">Career</option>
                            <option value="family">Family</option>
                            <option value="emergency">Emergency</option>
                            <option value="opportunity">Opportunity</option>
                            <option value="housing">Housing</option>
                            <option value="market">Market</option>
                        </select>
                    </div>
                    <div><label class="text-xs text-gray-400 block mb-1">Icon (emoji)</label><input x-model="decForm.icon" class="input-field" placeholder="💬" maxlength="4"></div>
                    <div><label class="text-xs text-gray-400 block mb-1">Weight (1–30, higher = more frequent)</label><input x-model="decForm.weight" type="number" min="1" max="30" class="input-field" value="10"></div>
                    <div><label class="text-xs text-gray-400 block mb-1">Min Tick (game day to start appearing)</label><input x-model="decForm.min_tick" type="number" min="0" class="input-field" value="0"></div>
                    <div><label class="text-xs text-gray-400 block mb-1">Max Tick (0 = no limit)</label><input x-model="decForm.max_tick" type="number" min="0" class="input-field" value="0"></div>
                    <div><label class="text-xs text-gray-400 block mb-1">Image URL</label><input x-model="decForm.image_url" class="input-field" placeholder="https://loremflickr.com/800/420/keyword"></div>
                    <div class="flex items-center gap-3 pt-4">
                        <label class="text-xs text-gray-400">Repeatable?</label>
                        <input type="checkbox" x-model="decForm.is_repeatable" class="w-4 h-4">
                    </div>
                </div>

                {{-- Choices --}}
                <div class="border-t border-white/10 pt-4 space-y-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Choices (2–3)</p>
                    <template x-for="(choice, idx) in decForm.choices" :key="idx">
                        <div class="p-4 rounded-xl space-y-2" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold text-gray-300" x-text="'Choice ' + (idx+1)"></p>
                                <button x-show="decForm.choices.length > 2" @click="decForm.choices.splice(idx,1)" class="text-xs text-red-400 hover:text-red-300">Remove</button>
                            </div>
                            <input x-model="choice.label" class="input-field" placeholder="Choice label (e.g. Lend Ksh 3,000)">
                            <input x-model="choice.description" class="input-field" placeholder="Sub-description (optional)">
                            <textarea x-model="choice.outcome_text" class="input-field" rows="2" placeholder="What happens after this choice..."></textarea>
                            <textarea x-model="choice.financial_lesson" class="input-field" rows="2" placeholder="Financial lesson (optional — shown as 💡 tip)"></textarea>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div><label class="text-[10px] text-gray-500 block mb-1">Balance Δ (Ksh)</label><input x-model="choice.balance_delta" type="number" class="input-field" placeholder="0"></div>
                                <div><label class="text-[10px] text-gray-500 block mb-1">Credit Score Δ</label><input x-model="choice.credit_score_delta" type="number" class="input-field" placeholder="0"></div>
                                <div><label class="text-[10px] text-gray-500 block mb-1">Relationship Δ</label><input x-model="choice.relationship_delta" type="number" class="input-field" placeholder="0"></div>
                                <div><label class="text-[10px] text-gray-500 block mb-1">XP Δ</label><input x-model="choice.xp_delta" type="number" class="input-field" placeholder="10"></div>
                            </div>
                        </div>
                    </template>
                    <button x-show="decForm.choices.length < 3" @click="decForm.choices.push({label:'',description:'',outcome_text:'',financial_lesson:'',balance_delta:0,credit_score_delta:0,relationship_delta:0,xp_delta:10})"
                            class="text-xs text-emerald-400 font-semibold hover:text-emerald-300">+ Add Choice</button>
                </div>

                <button @click="saveDecision()" class="mt-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white" style="background:linear-gradient(135deg,#059669,#10b981);">
                    Save Decision
                </button>
                <p x-show="decMsg" x-text="decMsg" class="text-sm text-emerald-400 mt-2"></p>
            </div>

            {{-- Decision List --}}
            <div class="space-y-2">
                @foreach(\App\Models\LifeDecision::with('npc')->orderByDesc('created_at')->get() as $dec)
                <div class="flex items-start gap-4 p-4 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                    @if($dec->image_url)
                    <img src="{{ $dec->image_url }}" alt="" class="w-16 h-12 rounded-lg object-cover flex-shrink-0">
                    @else
                    <div class="w-16 h-12 rounded-lg flex items-center justify-center text-2xl flex-shrink-0" style="background:rgba(255,255,255,0.04);">{{ $dec->icon }}</div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-0.5">
                            <p class="text-sm font-bold text-white truncate">{{ $dec->title }}</p>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold" style="background:{{ $dec->categoryColor() }}22;color:{{ $dec->categoryColor() }};border:1px solid {{ $dec->categoryColor() }}33;">{{ $dec->categoryLabel() }}</span>
                            @if($dec->npc)<span class="text-[10px] text-gray-500">via {{ $dec->npc->displayName() }}</span>@endif
                            @if(!$dec->is_active)<span class="text-[10px] px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 border border-red-500/25">Inactive</span>@endif
                        </div>
                        <p class="text-xs text-gray-500">{{ $dec->choices()->count() }} choices · weight {{ $dec->weight }} · from tick {{ $dec->min_tick }}</p>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <button onclick="toggleDecision({{ $dec->id }}, this)"
                                class="text-xs px-3 py-1.5 rounded-lg font-semibold transition-all {{ $dec->is_active ? 'bg-red-500/15 text-red-400 hover:bg-red-500/25 border border-red-500/25' : 'bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 border border-emerald-500/25' }}">
                            {{ $dec->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
</template>

<script>
function inboxAdmin() {
    return {
        sub: 'npcs',
        showNpcForm: false,
        showDecForm: false,
        npcMsg: '',
        decMsg: '',
        npcForm: { name:'', nickname:'', role:'friend', cover_color:'#6366f1', avatar_url:'', description:'', personality:'', initial_relationship:50 },
        decForm: {
            title:'', body:'', npc_id:'', category:'social', icon:'💬', weight:10, min_tick:0, max_tick:null, image_url:'', is_repeatable:false,
            choices: [
                { label:'', description:'', outcome_text:'', financial_lesson:'', balance_delta:0, credit_score_delta:0, relationship_delta:0, xp_delta:10 },
                { label:'', description:'', outcome_text:'', financial_lesson:'', balance_delta:0, credit_score_delta:0, relationship_delta:0, xp_delta:10 },
            ]
        },

        async saveNpc() {
            const res = await fetch('/admin/npcs', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' }, body: JSON.stringify(this.npcForm) });
            const data = await res.json();
            this.npcMsg = data.message ?? (data.success ? 'NPC saved!' : 'Error saving NPC.');
            if (data.success) { this.showNpcForm = false; setTimeout(() => window.location.reload(), 800); }
        },

        async saveDecision() {
            const res = await fetch('/admin/decisions', { method:'POST', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' }, body: JSON.stringify(this.decForm) });
            const data = await res.json();
            this.decMsg = data.message ?? (data.success ? 'Decision saved!' : 'Error saving decision.');
            if (data.success) { this.showDecForm = false; setTimeout(() => window.location.reload(), 800); }
        },
    };
}

async function toggleNpc(id, btn) {
    btn.disabled = true;
    try {
        const res = await fetch(`/admin/npcs/${id}/toggle`, { method:'POST', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' } });
        if ((await res.json()).success) window.location.reload();
    } catch { btn.disabled = false; }
}

async function toggleDecision(id, btn) {
    btn.disabled = true;
    try {
        const res = await fetch(`/admin/decisions/${id}/toggle`, { method:'POST', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' } });
        if ((await res.json()).success) window.location.reload();
    } catch { btn.disabled = false; }
}
</script>

{{-- ════════════════════════════════════════
     TAB: ARTISAN COMMANDS
     ════════════════════════════════════════ --}}
<template x-if="activeTab==='artisan'">
<div x-data="artisanRunner()" class="space-y-5">

    {{-- ⏰ Cron Setup Guide --}}
    <div class="panel rounded-2xl p-6" x-data="{ cronOpen: {{ \App\Models\Setting::get('cron_configured', '0') === '1' ? 'false' : 'true' }} }">
        <button type="button" @click="cronOpen = !cronOpen" class="w-full flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0" style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);">⏰</div>
                <div class="text-left">
                    <h2 class="font-bold text-white text-base">Set Up the Server Cron Job</h2>
                    <p class="text-gray-400 text-xs mt-0.5">One-time setup so crises, teacher digests and offline push warnings fire automatically on a timer.</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform" :class="cronOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="cronOpen" x-transition x-cloak class="mt-5 space-y-5">

            <div class="rounded-xl p-4 text-sm text-gray-300 leading-relaxed" style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.2);">
                <b class="text-indigo-300">Good news:</b> PesaQuest doesn't <i>need</i> this — the game clock, bills, salaries and crises all run automatically whenever a player logs in, and every scheduled task below also has a manual "Run Now" button in the command list below. This cron job just makes three things happen <b>without anyone visiting the site</b>: hourly crisis processing, weekly teacher digests, and — the big one — <b>push notifications sent while players are offline</b> (bill due soon, payday about to be forfeited).
            </div>

            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Step 1 — Copy this command</p>
                @php
                    $cronAppPath = base_path();
                    $cronLine    = "* * * * * cd {$cronAppPath} && php artisan schedule:run >> /dev/null 2>&1";
                @endphp
                <div x-data="{ copied: false }" class="flex items-stretch gap-2">
                    <code id="cron-line-code" class="flex-1 rounded-xl px-4 py-3 text-xs sm:text-sm text-emerald-300 overflow-x-auto whitespace-nowrap" style="background:#0a0912;border:1px solid rgba(255,255,255,.1);">{{ $cronLine }}</code>
                    <button type="button"
                            @click="navigator.clipboard.writeText({{ Js::from($cronLine) }}); copied = true; setTimeout(() => copied = false, 2000)"
                            class="flex-shrink-0 px-4 rounded-xl text-xs font-bold transition-all"
                            :style="copied ? 'background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;' : 'background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#9ca3af;'">
                        <span x-show="!copied">📋 Copy</span>
                        <span x-show="copied">✅ Copied!</span>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">The path above (<code class="text-gray-400">{{ $cronAppPath }}</code>) was detected automatically from this server — you shouldn't need to change it.</p>
            </div>

            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Step 2 — Add it in cPanel</p>
                <ol class="text-sm text-gray-300 space-y-2 list-decimal list-inside leading-relaxed">
                    <li>Log in to <b>cPanel</b> → search for <b>"Cron Jobs"</b> (under Advanced) and open it.</li>
                    <li>Under <b>Add New Cron Job</b>, set the schedule to <b>"Once Per Minute"</b> — or manually set every field (Minute/Hour/Day/Month/Weekday) to <code class="text-indigo-300">*</code>. That matches the <code class="text-indigo-300">* * * * *</code> at the start of the command above.</li>
                    <li>Paste the <b>whole command</b> from Step 1 into the <b>Command</b> field (cPanel already provides the schedule fields separately — you can paste just the part after the five stars: <code class="text-indigo-300">cd {{ $cronAppPath }} && php artisan schedule:run >> /dev/null 2>&1</code>).</li>
                    <li>Click <b>Add New Cron Job</b>. Done — no restart needed.</li>
                </ol>
            </div>

            <div class="rounded-xl p-4 text-xs text-amber-300/90 leading-relaxed" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);">
                ⚠️ <b>If it doesn't seem to run:</b> some hosts default cron to an old PHP version. Open cPanel's <b>"MultiPHP Manager"</b> or the PHP version selector shown on the Cron Jobs page itself and make sure the account/cron uses <b>PHP 8.2 or newer</b> — this app requires it. If your host needs an explicit PHP binary path instead of just <code>php</code> (visible via a <b>"PHP Selector"</b> or by asking your host's support), replace <code>php artisan</code> above with e.g. <code>/usr/local/bin/ea-php82 artisan</code>.
            </div>

            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">What this one cron line actually runs</p>
                <div class="grid sm:grid-cols-2 gap-2 text-xs text-gray-400">
                    <div class="rounded-lg px-3 py-2" style="background:rgba(255,255,255,.03);">🌪️ Financial crises — hourly</div>
                    <div class="rounded-lg px-3 py-2" style="background:rgba(255,255,255,.03);">🔮 Predictive push warnings — every 30 min</div>
                    <div class="rounded-lg px-3 py-2" style="background:rgba(255,255,255,.03);">🏫 Teacher weekly digest — Mondays 7:30am</div>
                    <div class="rounded-lg px-3 py-2" style="background:rgba(255,255,255,.03);">💳 Subscription reminders — daily 8am</div>
                    <div class="rounded-lg px-3 py-2" style="background:rgba(255,255,255,.03);">📈 Investment processing — hourly</div>
                    <div class="rounded-lg px-3 py-2" style="background:rgba(255,255,255,.03);">📧 Weekly summary emails — Mondays 9am</div>
                </div>
            </div>

            <button type="button" @click="fetch('{{ route('admin.settings.save') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}, body: JSON.stringify({group:'general', cron_configured:'1'})}).then(() => cronOpen = false)"
                    class="text-xs font-bold text-emerald-400 hover:text-emerald-300">
                ✓ I've set this up — collapse this by default from now on
            </button>
        </div>
    </div>

    <div class="panel rounded-2xl p-6">
        <h2 class="font-bold text-white text-base mb-1">Artisan Command Runner</h2>
        <p class="text-gray-400 text-sm mb-6">Run Laravel artisan commands directly from the browser — useful when SSH or terminal access is unavailable.</p>

        {{-- Command Groups --}}
        <div class="space-y-5">

            {{-- Cache --}}
            <div class="settings-section">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Cache Management</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="cmd in cacheCommands" :key="cmd.key">
                        <button @click="run(cmd)"
                                :disabled="running"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-40"
                                :class="cmd.danger ? 'bg-red-600/20 border border-red-500/30 text-red-300 hover:bg-red-600/30' : 'bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-600/30'">
                            <span x-text="cmd.icon"></span>
                            <span x-text="cmd.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Database --}}
            <div class="settings-section">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Database</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="cmd in dbCommands" :key="cmd.key">
                        <button @click="run(cmd)"
                                :disabled="running"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-40"
                                :class="cmd.danger ? 'bg-red-600/20 border border-red-500/30 text-red-300 hover:bg-red-600/30' : 'bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-600/30'">
                            <span x-text="cmd.icon"></span>
                            <span x-text="cmd.label"></span>
                        </button>
                    </template>
                </div>
                <p class="text-xs text-amber-400/70 mt-2">⚠ <code>migrate</code> runs with <code>--force</code> (safe for production).</p>
            </div>

            {{-- Seeders --}}
            <div class="settings-section">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Seeders</p>
                <p class="text-xs text-gray-500 mb-3">Safe seeders use <code>updateOrCreate</code> (won't wipe data). Destructive seeders truncate first — highlighted in red.</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="cmd in seederCommands" :key="cmd.key">
                        <button @click="run(cmd)"
                                :disabled="running"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-40"
                                :class="cmd.danger ? 'bg-red-600/20 border border-red-500/30 text-red-300 hover:bg-red-600/30' : 'bg-teal-600/20 border border-teal-500/30 text-teal-300 hover:bg-teal-600/30'">
                            <span x-text="cmd.icon"></span>
                            <span x-text="cmd.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Queue & Optimize --}}
            <div class="settings-section">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Queue & Optimization</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="cmd in otherCommands" :key="cmd.key">
                        <button @click="run(cmd)"
                                :disabled="running"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-all disabled:opacity-40"
                                :class="cmd.danger ? 'bg-red-600/20 border border-red-500/30 text-red-300 hover:bg-red-600/30' : 'bg-purple-600/20 border border-purple-500/30 text-purple-300 hover:bg-purple-600/30'">
                            <span x-text="cmd.icon"></span>
                            <span x-text="cmd.label"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Output Terminal --}}
    <div class="panel rounded-2xl p-6">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Output</p>
            <div class="flex items-center gap-3">
                <span x-show="running" class="text-xs text-indigo-400 animate-pulse">Running...</span>
                <span x-show="!running && lastCommand" class="text-xs" :class="lastSuccess ? 'text-emerald-400' : 'text-red-400'" x-text="lastSuccess ? '✓ Success' : '✗ Failed'"></span>
                <button x-show="output" @click="output=''; lastCommand=''" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Clear</button>
            </div>
        </div>
        <div x-show="!output && !running" class="text-gray-600 text-sm italic">No command run yet — pick one above.</div>
        <div x-show="output || running">
            <p class="text-xs text-gray-500 mb-2" x-text="lastCommand ? '$ php artisan ' + lastCommand : ''"></p>
            <pre class="bg-black/60 border border-white/5 rounded-xl p-4 text-xs text-gray-300 overflow-x-auto whitespace-pre-wrap leading-relaxed font-mono"
                 x-text="running ? 'Running…' : output"></pre>
        </div>
    </div>

</div>
</template>

{{-- ════════════════════════════════════════
     TAB: ARCADE SPONSORS
     ════════════════════════════════════════ --}}
<div x-show="activeTab==='sponsors'">
    <div class="panel rounded-2xl overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="font-bold text-white text-base">🏆 Arcade Sponsors</h2>
            <p class="text-gray-400 text-xs mt-0.5">Business/monetization branding for arcade reward tiles (Pesa Trail). Kept here, separate from GameSet's game-design tools.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.sponsors.store') }}" class="grid grid-cols-2 sm:grid-cols-5 gap-2 items-end mb-6">
                @csrf
                <div class="col-span-2">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Name</label>
                    <input name="name" required maxlength="60" class="w-full rounded-lg px-3 py-2 text-sm text-white" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Logo path (in public/)</label>
                    <input name="logo_path" required maxlength="255" placeholder="moski-logo.png" class="w-full rounded-lg px-3 py-2 text-sm text-white" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                </div>
                <button type="submit" class="rounded-lg px-4 py-2 text-sm font-bold text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Add</button>
                <div class="col-span-4">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Tagline (optional)</label>
                    <input name="tagline" maxlength="120" class="w-full rounded-lg px-3 py-2 text-sm text-white" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                </div>
            </form>

            <div class="space-y-2">
                @foreach($sponsors as $sponsor)
                <form method="POST" action="{{ route('admin.sponsors.update', $sponsor) }}" class="flex flex-wrap items-center gap-2 p-3 rounded-xl {{ $sponsor->is_active ? '' : 'opacity-40' }}" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);">
                    @csrf @method('PUT')
                    <img src="{{ asset($sponsor->logo_path) }}" alt="" class="w-8 h-8 rounded-lg object-cover flex-shrink-0" style="background:rgba(255,255,255,.05);">
                    <input name="name" value="{{ $sponsor->name }}" class="rounded-lg px-2 py-1.5 text-sm text-white w-32" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                    <input name="logo_path" value="{{ $sponsor->logo_path }}" class="rounded-lg px-2 py-1.5 text-sm text-white flex-1 min-w-[8rem]" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                    <input name="tagline" value="{{ $sponsor->tagline }}" class="rounded-lg px-2 py-1.5 text-sm text-white flex-1 min-w-[8rem]" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                    <span class="text-[10px] text-gray-500">{{ $sponsor->tiles_count }} tile{{ $sponsor->tiles_count === 1 ? '' : 's' }}</span>
                    <label class="flex items-center gap-1 text-xs text-gray-400"><input type="checkbox" name="is_active" value="1" {{ $sponsor->is_active ? 'checked' : '' }}> Active</label>
                    <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg" style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;">Save</button>
                    <button type="submit" form="del-sponsor-{{ $sponsor->id }}" onclick="return confirm('Delete this sponsor? Tiles carrying its branding revert to unsponsored.')" class="text-xs font-bold px-3 py-1.5 rounded-lg" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#fca5a5;">✕</button>
                </form>
                <form id="del-sponsor-{{ $sponsor->id }}" method="POST" action="{{ route('admin.sponsors.destroy', $sponsor) }}">@csrf @method('DELETE')</form>
                @endforeach
                @if($sponsors->isEmpty())
                <p class="text-sm text-gray-500 italic">No sponsors yet — add one above (arcade migrations may not have run).</p>
                @endif
            </div>
        </div>
    </div>

    <div class="panel rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="font-bold text-white text-base">Reward Tile Branding</h2>
            <p class="text-gray-400 text-xs mt-0.5">Assign a sponsor to any Pesa Trail reward tile — its icon shows the sponsor's branding in-game.</p>
        </div>
        <div class="p-6 grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($sponsorableTiles as $tile)
            <form method="POST" action="{{ route('admin.sponsors.tiles.assign', $tile) }}" class="flex items-center gap-2 p-2 rounded-lg" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);">
                @csrf
                <span class="text-xs font-black text-amber-300 w-10 flex-shrink-0">#{{ $tile->number }}</span>
                <select name="arcade_sponsor_id" onchange="this.form.submit()" class="flex-1 rounded-lg px-2 py-1.5 text-xs text-white" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                    <option value="">— No sponsor —</option>
                    @foreach($sponsors as $sponsor)
                    <option value="{{ $sponsor->id }}" {{ $tile->arcade_sponsor_id === $sponsor->id ? 'selected' : '' }}>{{ $sponsor->name }}</option>
                    @endforeach
                </select>
            </form>
            @endforeach
            @if($sponsorableTiles->isEmpty())
            <p class="text-sm text-gray-500 italic col-span-3">No reward tiles found — arcade migrations may not have run.</p>
            @endif
        </div>
    </div>
</div>

<script>
function artisanRunner() {
    return {
        running: false,
        output: '',
        lastCommand: '',
        lastSuccess: false,

        cacheCommands: [
            { key: 'cache:clear',    label: 'Clear App Cache',    icon: '🗑', danger: false },
            { key: 'config:clear',   label: 'Clear Config Cache', icon: '🗑', danger: false },
            { key: 'config:cache',   label: 'Cache Config',       icon: '⚡', danger: false },
            { key: 'route:clear',    label: 'Clear Route Cache',  icon: '🗑', danger: false },
            { key: 'route:cache',    label: 'Cache Routes',       icon: '⚡', danger: false },
            { key: 'view:clear',     label: 'Clear View Cache',   icon: '🗑', danger: false },
            { key: 'optimize:clear', label: 'Clear All Caches',   icon: '💥', danger: true  },
            { key: 'optimize',       label: 'Optimize (Cache All)',icon: '🚀', danger: false },
        ],

        dbCommands: [
            { key: 'migrate',        label: 'Run Migrations',     icon: '🗄', danger: false },
            { key: 'migrate:status', label: 'Migration Status',   icon: '📋', danger: false },
        ],

        otherCommands: [
            { key: 'queue:restart',      label: 'Restart Queue',           icon: '🔄', danger: false },
            { key: 'storage:link',       label: 'Create Storage Link',     icon: '🔗', danger: false },
            { key: 'fix:storage-images', label: 'Fix & Audit Image URLs',  icon: '🖼', danger: false },
            { key: 'crises:process',     label: 'Process Crises Now',      icon: '🌪', danger: false },
            { key: 'teachers:digest',    label: 'Send Teacher Digests Now', icon: '🏫', danger: false },
            { key: 'push:predictive',    label: 'Run Predictive Push Check Now', icon: '🔮', danger: false },
        ],

        seederCommands: [
            { key: 'seed:all',             label: 'SEED ALL (run every seeder)', icon: '🌱', danger: true  },
            { key: 'seed:content-l123',    label: 'Level 1-3 Content (courses/jobs/quests)', icon: '🎒', danger: false },
            { key: 'seed:npcs',            label: 'NPCs',             icon: '👥', danger: false },
            { key: 'seed:life-decisions',  label: 'Life Decisions',   icon: '🃏', danger: false },
            { key: 'seed:brand-gadgets',   label: 'Brand Gadgets',    icon: '📱', danger: false },
            { key: 'seed:life-events',     label: 'Life Events',      icon: '🎲', danger: false },
            { key: 'seed:market-events',   label: 'Market Events',    icon: '📈', danger: false },
            { key: 'seed:career-events',   label: 'Career Events',    icon: '💼', danger: false },
            { key: 'seed:missions',        label: 'Missions',         icon: '🗺', danger: false },
            { key: 'seed:fun-world',       label: 'Fun World Activities', icon: '🎡', danger: false },
            { key: 'seed:dreams',          label: 'Dreams Catalog',   icon: '🏆', danger: false },
            { key: 'seed:challenge-templates', label: 'Challenge Templates', icon: '⚔️', danger: false },
            { key: 'seed:scenarios-bulk',  label: 'Bulk Scenarios',   icon: '📖', danger: false },
            { key: 'seed:scenarios-adult', label: 'Adult Scenarios',  icon: '📋', danger: false },
            { key: 'seed:asset-events',    label: 'Asset Events',     icon: '💼', danger: false },
            { key: 'seed:assets',          label: 'Assets (⚠ truncates)', icon: '🗑', danger: true  },
            { key: 'seed:bills',           label: 'Bills (⚠ truncates)',  icon: '🗑', danger: true  },
            { key: 'seed:nodes',           label: 'Nodes (⚠ truncates)',  icon: '🗑', danger: true  },
        ],

        async run(cmd) {
            if (this.running) return;
            if (cmd.danger && !confirm(`Run "php artisan db:seed --class=... (${cmd.label})"?\n\n⚠ This is DESTRUCTIVE — it will truncate and re-seed data.`)) return;
            this.running = true;
            this.lastCommand = cmd.key;
            this.output = '';
            try {
                const res = await fetch('/admin/artisan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ command: cmd.key }),
                });
                const data = await res.json();
                this.output = data.output ?? '(no output)';
                this.lastSuccess = data.success;
            } catch (e) {
                this.output = 'Network error: ' + e.message;
                this.lastSuccess = false;
            } finally {
                this.running = false;
            }
        },
    };
}
</script>
</body>
</html>
