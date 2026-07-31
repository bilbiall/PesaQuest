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
        .hero-gradient { background:linear-gradient(135deg,#c7d2fe,#a78bfa,#f59e0b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .rank-card { background:linear-gradient(145deg,rgba(255,255,255,0.03),rgba(255,255,255,0.01)); border:1px solid rgba(255,255,255,0.07); transition:all 0.3s; }
        .rank-card:hover { transform:translateX(4px); border-color:rgba(99,102,241,0.3); }
        .rank-card.mine { background:linear-gradient(135deg,rgba(99,102,241,0.12),rgba(139,92,246,0.08)); border-color:rgba(99,102,241,0.4); box-shadow:0 0 30px rgba(99,102,241,0.15); }
        .rank-1 { background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(251,191,36,0.08)); border-color:rgba(245,158,11,0.4); }
        .rank-2 { background:linear-gradient(135deg,rgba(148,163,184,0.10),rgba(148,163,184,0.05)); border-color:rgba(148,163,184,0.3); }
        .rank-3 { background:linear-gradient(135deg,rgba(180,120,80,0.12),rgba(180,120,80,0.06)); border-color:rgba(180,120,80,0.3); }
        .age-tab { border:1px solid rgba(255,255,255,0.08); transition:all 0.2s; }
        .age-tab.active { background:rgba(99,102,241,0.2); border-color:rgba(99,102,241,0.5); color:white; }
        .age-tab:not(.active) { background:rgba(255,255,255,0.03); color: #9ca3af; }
        .age-tab:not(.active):hover { background:rgba(255,255,255,0.06); }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased" x-data="leaderboardPage()">
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>

    <div class="page-content max-w-2xl mx-auto px-4 py-10">

        <div class="mb-6">
            <a href="{{ route('game.play') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Game
            </a>
        </div>

        <div class="text-center mb-10">
            <div class="text-6xl mb-3">🏆</div>
            <h1 class="hero-gradient text-3xl sm:text-4xl font-black mb-2">Leaderboard</h1>
            <p class="text-gray-400 text-sm">
                @if($sort === 'networth') Top players by Net Worth @else Top players by XP @endif
                · <span class="text-indigo-400">Your age group</span>
            </p>
        </div>

        {{-- Sort tabs --}}
        <div class="flex gap-2 mb-5 justify-center">
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => 'xp']) }}"
               class="age-tab px-5 py-2 rounded-xl text-sm font-bold {{ $sort !== 'networth' ? 'active' : '' }}">
                ⭐ XP
            </a>
            <a href="{{ route('game.leaderboard', ['age_group' => $ageGroup, 'sort' => 'networth']) }}"
               class="age-tab px-5 py-2 rounded-xl text-sm font-bold {{ $sort === 'networth' ? 'active' : '' }}">
                💰 Net Worth
            </a>
        </div>

        {{-- Age group tabs --}}
        <div class="flex gap-2 mb-8 flex-wrap justify-center">
            @foreach(['8-12','13-17','18-25','26+'] as $ag)
            <a href="{{ route('game.leaderboard', ['age_group' => $ag, 'sort' => $sort]) }}"
               class="age-tab px-4 py-2 rounded-xl text-sm font-bold {{ $ageGroup === $ag ? 'active' : '' }}">
                Ages {{ $ag }}
            </a>
            @endforeach
        </div>

        {{-- Rankings --}}
        <div class="space-y-3">
            @forelse($leaders as $leader)
            <div class="rank-card rounded-2xl px-5 py-4 flex items-center gap-4
                        {{ $leader['rank'] === 1 ? 'rank-1' : ($leader['rank'] === 2 ? 'rank-2' : ($leader['rank'] === 3 ? 'rank-3' : '')) }}
                        {{ $leader['is_me'] ? 'mine' : '' }}">
                {{-- Rank --}}
                <div class="w-10 text-center flex-shrink-0">
                    @if($leader['rank'] === 1) <span class="text-2xl">🥇</span>
                    @elseif($leader['rank'] === 2) <span class="text-2xl">🥈</span>
                    @elseif($leader['rank'] === 3) <span class="text-2xl">🥉</span>
                    @else <span class="text-lg font-black text-gray-500">#{{ $leader['rank'] }}</span>
                    @endif
                </div>
                {{-- Avatar placeholder --}}
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-black flex-shrink-0"
                     style="background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.25);">
                    {{ mb_strtoupper(mb_substr($leader['name'], 0, 1)) }}
                </div>
                {{-- Name & level --}}
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-white text-sm flex items-center gap-2">
                        {{ $leader['name'] }}
                        @if($leader['is_me']) <span class="text-xs bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 px-2 py-0.5 rounded-full">You</span>@endif
                    </div>
                    <div class="text-xs text-gray-500">
                        Level {{ $leader['level'] }} · {{ $leader['played_label'] }}
                    </div>
                </div>
                {{-- Score --}}
                <div class="text-right flex-shrink-0">
                    <div class="font-black text-sm {{ $leader['rank'] === 1 ? 'text-amber-400' : 'text-indigo-400' }}">
                        @if($sort === 'networth') Ksh @endif{{ number_format($leader['points']) }}
                    </div>
                    <div class="text-xs text-gray-600">{{ $sort === 'networth' ? 'Net Worth' : 'XP' }}</div>
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

        <div class="text-center mt-10">
            <a href="{{ route('game.play') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold px-8 py-4 rounded-2xl shadow-xl hover:shadow-indigo-500/30 transition-all hover:-translate-y-1">
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
