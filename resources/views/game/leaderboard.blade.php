<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PesaQuest Leaderboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; }
        [x-cloak] { display: none !important; }
        .bg-orb { position:fixed; border-radius:50%; filter:blur(80px); pointer-events:none; z-index:0; }
        .bg-orb-1 { width:500px; height:500px; top:-150px; left:-100px; background:rgba(99,102,241,0.12); }
        .bg-orb-2 { width:400px; height:400px; bottom:-100px; right:-80px; background:rgba(139,92,246,0.10); }
        .page-content { position:relative; z-index:10; }

        /* Hero card */
        .hero-card { background:linear-gradient(145deg,rgba(99,102,241,0.16),rgba(139,92,246,0.08)); border:1px solid rgba(139,92,246,0.25); border-radius:1.25rem; padding:1.1rem 1.2rem; display:flex; align-items:center; gap:.9rem; flex-wrap:wrap; margin-bottom:1.1rem; }
        .hero-trophy { font-size:2.4rem; line-height:1; flex-shrink:0; }
        .hero-copy { flex:1; min-width:160px; }
        .hero-copy h1 { font-size:1.05rem; font-weight:800; color:#fff; margin:0 0 .2rem; }
        .hero-copy p { font-size:.75rem; color:#9ca3af; margin:0; }
        .hero-copy .gold { color:#fbbf24; font-weight:700; }
        .hero-stats { display:flex; gap:.5rem; flex-wrap:wrap; }
        .stat-pill { background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.08); border-radius:.85rem; padding:.5rem .7rem; display:flex; align-items:center; gap:.5rem; min-width:118px; }
        .stat-pill .ico { font-size:1rem; }
        .stat-pill b { display:block; font-size:.85rem; color:#fff; font-weight:800; line-height:1.1; }
        .stat-pill small { display:block; font-size:.62rem; color:#9ca3af; }
        .stat-pill.up b { color:#34d399; }
        .stat-pill.down b { color:#f87171; }

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

        {{-- Hero card --}}
        <div class="hero-card">
            <div class="hero-trophy">🏆</div>
            <div class="hero-copy">
                <h1>Rise. Learn. Lead.</h1>
                <p>Climb the leaderboard and become a <span class="gold">money champion</span>.</p>
            </div>
            <div class="hero-stats">
                <div class="stat-pill">
                    <span class="ico">👥</span>
                    <div><b>{{ number_format($playerCount) }}</b><small>Players on board</small></div>
                </div>
                <div class="stat-pill {{ $weekChangePct === null ? '' : ($weekChangePct >= 0 ? 'up' : 'down') }}">
                    @if($weekChangePct === null)
                        <span class="ico">🌱</span>
                        <div><b>New</b><small>Tracking from today</small></div>
                    @else
                        <span class="ico">{{ $weekChangePct >= 0 ? '📈' : '📉' }}</span>
                        <div><b>{{ $weekChangePct >= 0 ? '+' : '' }}{{ $weekChangePct }}%</b><small>vs last week</small></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-center mb-5">
            <p class="text-gray-400 text-xs">
                @if($sort === 'networth') Top players by Net Worth @else Top players by XP @endif
                · <span class="text-indigo-400">{{ ($scope ?? 'global') === 'school' ? ($mySchoolName ?? 'Your School') : 'Your age group' }}</span>
            </p>
        </div>

        @if($mySchoolName ?? null)
        {{-- Global / My School scope tabs --}}
        <div class="pill-row mb-3">
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => $sort, 'scope' => 'global']) }}"
               class="age-tab {{ ($scope ?? 'global') !== 'school' ? 'active' : '' }}">
                🌍 Global
            </a>
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => $sort, 'scope' => 'school']) }}"
               class="age-tab {{ ($scope ?? 'global') === 'school' ? 'active' : '' }}">
                🏫 {{ $mySchoolName }}
            </a>
        </div>
        @endif

        {{-- Sort tabs --}}
        <div class="pill-row mb-3">
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => 'xp', 'scope' => $scope ?? 'global']) }}"
               class="age-tab {{ $sort !== 'networth' ? 'active' : '' }}">
                ⭐ XP
            </a>
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => 'networth', 'scope' => $scope ?? 'global']) }}"
               class="age-tab {{ $sort === 'networth' ? 'active' : '' }}">
                💰 Net Worth
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
            <div class="rank-card rounded-xl px-4 py-3 flex items-center gap-3
                        {{ $leader['rank'] === 1 ? 'rank-1' : ($leader['rank'] === 2 ? 'rank-2' : ($leader['rank'] === 3 ? 'rank-3' : '')) }}
                        {{ $leader['is_me'] ? 'mine' : '' }}">
                {{-- Rank --}}
                <div class="w-8 text-center flex-shrink-0">
                    @if($leader['rank'] === 1) <span class="text-xl">🥇</span>
                    @elseif($leader['rank'] === 2) <span class="text-xl">🥈</span>
                    @elseif($leader['rank'] === 3) <span class="text-xl">🥉</span>
                    @else <span class="text-sm font-black text-gray-500">#{{ $leader['rank'] }}</span>
                    @endif
                </div>
                {{-- Avatar --}}
                @if($leader['profile_photo'])
                <img src="{{ $leader['profile_photo'] }}" alt="" class="w-9 h-9 rounded-lg object-cover flex-shrink-0"
                     style="box-shadow:0 0 0 2px {{ $leader['rank'] === 1 ? 'rgba(245,158,11,0.5)' : 'rgba(99,102,241,0.25)' }};">
                @else
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-base font-black flex-shrink-0"
                     style="background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.25);">
                    {{ mb_strtoupper(mb_substr($leader['name'], 0, 1)) }}
                </div>
                @endif
                {{-- Name & level --}}
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-white text-sm flex items-center gap-2">
                        <span class="truncate">{{ $leader['name'] }}</span>
                        @if($leader['is_me']) <span class="text-[.65rem] bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 px-1.5 py-0.5 rounded-full flex-shrink-0">You</span>@endif
                    </div>
                    <div class="text-[.68rem] text-gray-500">
                        Level {{ $leader['level'] }} · {{ $leader['played_label'] }}
                    </div>
                </div>
                {{-- Score --}}
                <div class="text-right flex-shrink-0">
                    <div class="font-black text-sm {{ $leader['rank'] === 1 ? 'text-amber-400' : 'text-indigo-400' }}">
                        @if($sort === 'networth') Ksh @endif{{ number_format($leader['points']) }}
                    </div>
                    <div class="text-[.65rem] text-gray-600">{{ $sort === 'networth' ? 'Net Worth' : 'XP' }}</div>
                </div>
                {{-- Rank change --}}
                <div class="w-9 text-center flex-shrink-0">
                    @if($leader['rank_change'] === null)
                    <span class="text-gray-600 text-xs font-bold">–</span>
                    @elseif($leader['rank_change'] > 0)
                    <span class="text-emerald-400 text-xs font-black flex items-center justify-center gap-0.5">↑{{ $leader['rank_change'] }}</span>
                    @elseif($leader['rank_change'] < 0)
                    <span class="text-red-400 text-xs font-black flex items-center justify-center gap-0.5">↓{{ abs($leader['rank_change']) }}</span>
                    @else
                    <span class="text-amber-400 text-xs font-black">—</span>
                    @endif
                </div>
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
                🚀 Keep Playing to Climb
            </a>
        </div>
    </div>

    <script>
    function leaderboardPage() { return {} }
    </script>
<x-mobile-bottom-nav active="home" />
</body>
</html>
