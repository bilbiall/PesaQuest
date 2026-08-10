<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PesaQuest Leaderboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/icons.js') }}"></script>
    <style>
        body { background: #07060f; }
        [x-cloak] { display: none !important; }
        .bg-orb { position:fixed; border-radius:50%; filter:blur(80px); pointer-events:none; z-index:0; }
        .bg-orb-1 { width:500px; height:500px; top:-150px; left:-100px; background:rgba(99,102,241,0.12); }
        .bg-orb-2 { width:400px; height:400px; bottom:-100px; right:-80px; background:rgba(139,92,246,0.10); }
        .page-content { position:relative; z-index:10; }

        /* Decorative header */
        .lb-header { position:relative; text-align:center; padding:1.4rem .5rem 1rem; margin-bottom:.4rem; overflow:hidden; }
        .lb-deco { font-size:1.1rem; letter-spacing:.5em; opacity:.5; margin-bottom:.2rem; }
        .lb-title { font-size:1.9rem; font-weight:900; margin:0; line-height:1.1;
            background:linear-gradient(90deg,#f472b6,#a78bfa,#818cf8); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .lb-subtitle { font-size:.78rem; color:#9ca3af; margin:.3rem 0 0; }
        .lb-subtitle .accent { color:#a78bfa; font-weight:700; }
        .lb-sparkle { position:absolute; font-size:.85rem; opacity:.55; pointer-events:none; }
        .lb-sparkle.s1 { top:6px; left:8%; }
        .lb-sparkle.s2 { top:2px; right:12%; font-size:1.1rem; }
        .lb-sparkle.s3 { bottom:2px; left:20%; font-size:.7rem; }
        .lb-sparkle.s4 { bottom:6px; right:22%; }

        /* Rank-1 glow */
        @keyframes rank1-glow { 0%,100% { box-shadow:0 0 14px rgba(245,158,11,0.35), inset 0 0 0 1px rgba(245,158,11,0.4); } 50% { box-shadow:0 0 26px rgba(245,158,11,0.6), inset 0 0 0 1px rgba(245,158,11,0.55); } }
        .rank-1 { animation: rank1-glow 2.6s ease-in-out infinite; }

        /* Bio + badge chip on each row */
        .rank-bio { font-size:.66rem; color:#7c8394; margin-top:.15rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .top-badge-chip { display:inline-flex; align-items:center; gap:.25rem; background:rgba(139,92,246,0.14); border:1px solid rgba(139,92,246,0.3); color:#c4b5fd; font-size:.62rem; font-weight:700; padding:.1rem .45rem; border-radius:999px; flex-shrink:0; }

        /* Trend, folded into the score column so it doesn't eat its own fixed-width slot */
        .trend-inline { font-size:.72rem; font-weight:800; }
        .trend-inline.up { color:#34d399; } .trend-inline.down { color:#f87171; }
        .trend-inline.flat { color:#fbbf24; } .trend-inline.none { color:#6b7280; }

        /* Narrow phones: rank/avatar/gaps/padding were eating almost all the
           row width, leaving name+bio only a sliver to work with (truncated
           to "B…"). Shrink the fixed columns instead of the flexible one. */
        @media (max-width:480px) {
            .rank-card { padding:.55rem .65rem; gap:.5rem; }
            .rank-card .rank-col { width:1.4rem; }
            .rank-card .avatar-col { width:1.8rem !important; height:1.8rem !important; }
            .rank-card .score-col { min-width:0; }
        }

        /* Expandable player-stats dropdown */
        .drop-panel { background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-top:none; border-radius:0 0 .8rem .8rem; padding:.7rem .9rem .8rem; margin-top:-2px; margin-bottom:.4rem; }
        .drop-label { font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:.35rem; }
        .drop-chips { display:flex; flex-wrap:wrap; gap:.35rem; }
        .drop-chip { background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.25); color:#e5e7eb; font-size:.68rem; font-weight:600; padding:.25rem .55rem; border-radius:.6rem; }
        .drop-chip.dream { background:rgba(236,72,153,0.12); border-color:rgba(236,72,153,0.28); }

        /* Slim pill filters */
        .pill-row { display:flex; gap:.4rem; flex-wrap:wrap; justify-content:center; }
        .age-tab { border:1px solid rgba(255,255,255,0.08); transition:all 0.2s; padding:.4rem .85rem; border-radius:.7rem; font-size:.72rem; font-weight:700; }
        .age-tab.active { background:rgba(99,102,241,0.2); border-color:rgba(99,102,241,0.5); color:white; }
        .age-tab:not(.active) { background:rgba(255,255,255,0.03); color: #9ca3af; }
        .age-tab:not(.active):hover { background:rgba(255,255,255,0.06); }

        /* Rank rows */
        .rank-card { background:linear-gradient(145deg,rgba(255,255,255,0.03),rgba(255,255,255,0.01)); border:1px solid rgba(255,255,255,0.07); transition:all 0.3s; }
        .rank-card:hover { transform:translateX(3px); border-color:rgba(99,102,241,0.3); }
        .rank-card.mine { background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(139,92,246,0.08)); border-color:rgba(99,102,241,0.4); box-shadow:0 0 24px rgba(99,102,241,0.15); }
        .rank-1 { background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(251,191,36,0.08)); border-color:rgba(245,158,11,0.4); }
        .rank-2 { background:linear-gradient(135deg,rgba(148,163,184,0.10),rgba(148,163,184,0.05)); border-color:rgba(148,163,184,0.3); }
        .rank-3 { background:linear-gradient(135deg,rgba(180,120,80,0.12),rgba(180,120,80,0.06)); border-color:rgba(180,120,80,0.3); }

        /* Goal card */
        .goal-card { background:linear-gradient(135deg,rgba(99,102,241,0.14),rgba(236,72,153,0.06)); border:1px solid rgba(139,92,246,0.3); border-radius:1.1rem; padding:1rem 1.1rem; display:flex; align-items:center; gap:.8rem; margin-top:1.3rem; flex-wrap:wrap; }
        .goal-icon { font-size:1.8rem; flex-shrink:0; }
        .goal-copy { flex:1; min-width:140px; }
        .goal-label { font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#c4b5fd; }
        .goal-title { font-size:.85rem; font-weight:800; color:#fff; margin-top:.1rem; }
        .goal-sub { font-size:.68rem; color:#9ca3af; margin-top:.1rem; }
        .goal-rank { text-align:center; flex-shrink:0; }
        .goal-rank b { font-size:1.2rem; color:#fbbf24; }
        .spark { display:flex; align-items:flex-end; gap:3px; height:28px; flex-shrink:0; }
        .spark i { display:block; width:6px; border-radius:3px 3px 1px 1px; background:linear-gradient(180deg,#a78bfa,#6366f1); }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased" x-data="leaderboardPage()">
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>

    <div class="page-content max-w-2xl mx-auto px-4 py-8">

        <div class="mb-4">
            <a href="{{ route('game.play') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Game
            </a>
        </div>

        {{-- Decorative header --}}
        <div class="lb-header">
            <span class="lb-sparkle s1">✨</span>
            <span class="lb-sparkle s2">⭐</span>
            <span class="lb-sparkle s3">💫</span>
            <span class="lb-sparkle s4">✨</span>
            <div class="lb-deco">🌿 🏆 🌿</div>
            <h1 class="lb-title">Leaderboard</h1>
            <p class="lb-subtitle">
                @if($sort === 'networth') Top players by Net Worth @else Top players by XP @endif
                · <span class="accent">{{ ($scope ?? 'global') === 'school' ? ($mySchoolName ?? 'Your School') : 'Your age group' }}</span>
            </p>
        </div>

        @if($mySchoolName ?? null)
        {{-- Global / My School scope tabs --}}
        <div class="pill-row mb-3">
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => $sort, 'scope' => 'global']) }}"
               class="age-tab {{ ($scope ?? 'global') !== 'school' ? 'active' : '' }}">
                <x-icon name="globe" class="w-3.5 h-3.5 inline-block" /> Global
            </a>
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => $sort, 'scope' => 'school']) }}"
               class="age-tab {{ ($scope ?? 'global') === 'school' ? 'active' : '' }}">
                <x-icon name="graduation" class="w-3.5 h-3.5 inline-block" /> {{ $mySchoolName }}
            </a>
        </div>
        @endif

        {{-- Sort tabs --}}
        <div class="pill-row mb-3">
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => 'xp', 'scope' => $scope ?? 'global']) }}"
               class="age-tab {{ $sort !== 'networth' ? 'active' : '' }}">
                <x-icon name="star" class="w-3.5 h-3.5 inline-block" /> XP
            </a>
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => 'networth', 'scope' => $scope ?? 'global']) }}"
               class="age-tab {{ $sort === 'networth' ? 'active' : '' }}">
                <x-icon name="coin" class="w-3.5 h-3.5 inline-block" /> Net Worth
            </a>
        </div>

        {{-- Age group tabs (global scope only — a school roster spans mixed ages) --}}
        @if(($scope ?? 'global') !== 'school')
        <div class="pill-row mb-6">
            @foreach(['8-12','13-17','18-25','26+'] as $ag)
            <a href="{{ route('game.leaderboard', ['age_group' => $ag, 'sort' => $sort]) }}"
               class="age-tab {{ $ageGroup === $ag ? 'active' : '' }}">
                Ages {{ $ag }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- Rankings --}}
        <div class="space-y-2">
            @forelse($leaders as $leader)
            <div class="rank-card rounded-xl px-4 py-3 flex items-center gap-3 cursor-pointer
                        {{ $leader['rank'] === 1 ? 'rank-1' : ($leader['rank'] === 2 ? 'rank-2' : ($leader['rank'] === 3 ? 'rank-3' : '')) }}
                        {{ $leader['is_me'] ? 'mine' : '' }}"
                 @click="toggle({{ $leader['user_id'] }})">
                {{-- Rank --}}
                <div class="rank-col w-8 text-center flex-shrink-0">
                    @if($leader['rank'] === 1) <span class="text-xl">🥇</span>
                    @elseif($leader['rank'] === 2) <span class="text-xl">🥈</span>
                    @elseif($leader['rank'] === 3) <span class="text-xl">🥉</span>
                    @else <span class="text-sm font-black text-gray-500">#{{ $leader['rank'] }}</span>
                    @endif
                </div>
                {{-- Avatar --}}
                <img src="{{ $leader['profile_photo'] ?: 'data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27%3E%3Ccircle cx=%2712%27 cy=%2712%27 r=%2712%27 fill=%27%232d3348%27/%3E%3Ccircle cx=%2712%27 cy=%279.4%27 r=%273.6%27 fill=%27%236b7280%27/%3E%3Cpath d=%27M4.4 20c0-3.9 3.7-6.4 7.6-6.4s7.6 2.5 7.6 6.4%27 fill=%27%236b7280%27/%3E%3C/svg%3E' }}"
                     alt="" class="avatar-col w-9 h-9 rounded-full object-cover flex-shrink-0"
                     style="box-shadow:0 0 0 2px {{ $leader['rank'] === 1 ? 'rgba(245,158,11,0.5)' : 'rgba(99,102,241,0.25)' }};">
                {{-- Name, level, title chip & bio --}}
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-white text-sm flex items-center gap-2">
                        <span class="truncate">{{ $leader['name'] }}</span>
                        @if($leader['is_me']) <span class="text-[.65rem] bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 px-1.5 py-0.5 rounded-full flex-shrink-0">You</span>@endif
                        @if($leader['top_badge']) <span class="top-badge-chip inline-flex items-center gap-1"><x-icon :name="$leader['top_badge']['icon']" class="w-3 h-3" /> {{ $leader['top_badge']['name'] }}</span>@endif
                    </div>
                    <div class="text-[.68rem] text-gray-500">
                        Level {{ $leader['level'] }} · {{ $leader['played_label'] }}
                    </div>
                    @if($leader['bio'])
                    <div class="rank-bio">{{ $leader['bio'] }}</div>
                    @endif
                </div>
                {{-- Score, with the trend folded in so it doesn't need its own fixed-width column --}}
                <div class="score-col text-right flex-shrink-0">
                    <div class="font-black text-sm {{ $leader['rank'] === 1 ? 'text-amber-400' : 'text-indigo-400' }} flex items-center justify-end gap-1.5">
                        <span>@if($sort === 'networth') Ksh @endif{{ number_format($leader['points']) }}</span>
                        @if($leader['rank_change'] === null)
                        <span class="trend-inline none">–</span>
                        @elseif($leader['rank_change'] > 0)
                        <span class="trend-inline up">↑{{ $leader['rank_change'] }}</span>
                        @elseif($leader['rank_change'] < 0)
                        <span class="trend-inline down">↓{{ abs($leader['rank_change']) }}</span>
                        @else
                        <span class="trend-inline flat">—</span>
                        @endif
                    </div>
                    <div class="text-[.65rem] text-gray-600">{{ $sort === 'networth' ? 'Net Worth' : 'XP' }}</div>
                </div>
            </div>
            {{-- Expandable stats dropdown --}}
            <div class="drop-panel" x-show="expanded === {{ $leader['user_id'] }}" x-cloak>
                <template x-if="!cache[{{ $leader['user_id'] }}]">
                    <div class="text-xs text-gray-500 py-1">Loading…</div>
                </template>
                <template x-if="cache[{{ $leader['user_id'] }}]">
                    <div>
                        <div class="drop-label inline-flex items-center gap-1"><x-icon name="medal" class="w-3 h-3" /> Badges Earned</div>
                        <div class="drop-chips mb-1">
                            <template x-for="b in cache[{{ $leader['user_id'] }}].badges" :key="b.name">
                                <span class="drop-chip inline-flex items-center gap-1"><span class="w-3 h-3" x-html="pqIcon(b.icon, 'w-3 h-3')"></span> <span x-text="b.name"></span></span>
                            </template>
                            <template x-if="cache[{{ $leader['user_id'] }}].badges.length === 0">
                                <span class="text-xs text-gray-500">No badges yet</span>
                            </template>
                        </div>
                        <div class="drop-label inline-flex items-center gap-1" style="margin-top:.55rem;"><x-icon name="star" class="w-3 h-3" /> Dreams Achieved</div>
                        <div class="drop-chips">
                            <template x-for="d in cache[{{ $leader['user_id'] }}].dreams" :key="d.name">
                                <span class="drop-chip dream" x-text="d.icon + ' ' + d.name"></span>
                            </template>
                            <template x-if="cache[{{ $leader['user_id'] }}].dreams.length === 0">
                                <span class="text-xs text-gray-500">None yet</span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            @empty
            <div class="text-center py-16 text-gray-500">
                <div class="text-4xl mb-3">🏜️</div>
                <p>No players yet for this age group</p>
                <p class="text-xs mt-1">Be the first to claim the top spot!</p>
            </div>
            @endforelse
        </div>

        {{-- Your Goal card --}}
        <div class="goal-card">
            <div class="goal-icon">🎯</div>
            <div class="goal-copy">
                <div class="goal-label">Your Goal</div>
                <div class="goal-title">
                    @if($myRank === 1) You're #1 — keep your lead!
                    @elseif($myRank <= 3) So close — push for #1!
                    @else Break into the Top 3
                    @endif
                </div>
                <div class="goal-sub">Keep playing and growing 💪</div>
            </div>
            <div class="goal-rank">
                <b>#{{ $myRank }}</b>
            </div>
            @if($mySparkline->count() >= 2)
            <div class="spark">
                @php $max = max($mySparkline->max(), 1); @endphp
                @foreach($mySparkline as $v)
                <i style="height:{{ max(15, round(($v / $max) * 100)) }}%;"></i>
                @endforeach
            </div>
            @endif
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('game.play') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold px-6 py-3 rounded-xl shadow-xl hover:shadow-indigo-500/30 transition-all hover:-translate-y-1 text-sm">
                <x-icon name="rocket" class="w-3.5 h-3.5 inline-block" /> Keep Playing to Climb
            </a>
        </div>
    </div>

    <script>
    function leaderboardPage() {
        return {
            expanded: null,
            cache: {},
            async toggle(userId) {
                this.expanded = this.expanded === userId ? null : userId;
                if (this.expanded === null || this.cache[userId]) return;
                try {
                    const res = await fetch(`/game/leaderboard/players/${userId}/details`);
                    this.cache[userId] = await res.json();
                } catch (e) {
                    this.cache[userId] = { badges: [], dreams: [] };
                }
            }
        };
    }
    </script>
<x-mobile-bottom-nav active="home" />
</body>
</html>
