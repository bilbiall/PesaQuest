<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pesa Trail — Arcade</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#0b0a16; font-family:'Figtree',sans-serif; color:#fff; }
        .sc-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); border-radius:1.25rem; }
        .sc-input { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); border-radius:.7rem; padding:.6rem .8rem; color:#fff; font-size:.85rem; width:100%; }
        .sc-btn { font-weight:800; border-radius:.85rem; padding:.75rem 1.1rem; font-size:.85rem; cursor:pointer; transition:transform .12s; }
        .sc-btn:active { transform:scale(.97); }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex items-center gap-3 mb-6">
            <img src="{{ asset('moski-logo.png') }}" class="w-9 h-9 rounded-xl">
            <div>
                <h1 class="text-xl font-black">🐍 Pesa Trail</h1>
                <p class="text-xs text-gray-500">Play smart, grow wealth!</p>
            </div>
            <a href="{{ url('/world') }}" class="ml-auto text-xs font-bold text-gray-400 hover:text-white">← Back</a>
        </div>

        @if(session('success'))
        <div class="sc-card p-4 mb-5 text-sm font-bold text-emerald-300" style="border-color:rgba(16,185,129,.3);">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="sc-card p-4 mb-5 text-sm font-bold text-red-300" style="border-color:rgba(248,113,113,.3);">⚠️ {{ session('error') }}</div>
        @endif

        @if($myInvites->isNotEmpty())
        <div class="sc-card p-5 mb-6" style="border-color:rgba(245,158,11,.4);">
            <p class="text-sm font-bold mb-3">🎲 Invited to a Rivals Trail round</p>
            <div class="space-y-2">
                @foreach($myInvites as $invite)
                <div class="flex items-center justify-between gap-3 p-3 rounded-xl flex-wrap" style="background:rgba(255,255,255,.02);">
                    <span class="text-xs text-gray-400">{{ $invite->inviter->name ?? 'A friend' }} invited you — entry KES {{ number_format($invite->match->stake_amount) }}</span>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('arcade.snakes.wager.invite.accept', $invite) }}">
                            @csrf
                            <button type="submit" class="sc-btn" style="background:rgba(16,185,129,.25);border:1px solid rgba(16,185,129,.4);color:#6ee7b7;padding:.4rem .9rem;">Accept</button>
                        </form>
                        <form method="POST" action="{{ route('arcade.snakes.wager.invite.decline', $invite) }}">
                            @csrf
                            <button type="submit" class="sc-btn" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);color:#9ca3af;padding:.4rem .9rem;">Decline</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($activeSession)
        <div class="sc-card p-5 mb-6" style="border-color:rgba(245,158,11,.4);">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('arcade.snakes.play', $activeSession) }}" class="flex-1 flex items-center justify-between hover:opacity-80 transition-opacity">
                    <div>
                        <p class="font-black text-amber-300">▶ Resume your game in progress</p>
                        <p class="text-xs text-gray-400 mt-0.5">Tile {{ $activeSession->position }} of {{ $game->tile_count }} — savings KES {{ number_format($activeSession->pot_amount) }}</p>
                    </div>
                    <span class="text-2xl ml-3">→</span>
                </a>
            </div>
            {{-- A stuck/abandoned session (e.g. left mid-game without using the
                 in-game Quit button) shouldn't need re-opening play just to end it —
                 Rivals Trail rounds settle automatically instead of by manual quit,
                 so this is standard-mode only. --}}
            @unless($activeSession->match && $activeSession->match->isWager())
            <button type="button" onclick="quitActiveSession('{{ route('arcade.snakes.cash-out', $activeSession) }}')" id="quitActiveBtn" class="text-xs font-bold text-gray-500 hover:text-red-300 mt-3">🚪 Quit this game and start fresh</button>
            @endunless
        </div>
        @endif

        <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-6">
            <div class="sc-card p-2.5 sm:p-4 text-center"><p class="text-lg sm:text-2xl font-black">{{ $stats['games_played'] }}</p><p class="text-[9px] sm:text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Games Played</p></div>
            <div class="sc-card p-2.5 sm:p-4 text-center"><p class="text-lg sm:text-2xl font-black text-emerald-400">{{ $stats['win_rate'] }}%</p><p class="text-[9px] sm:text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Win Rate</p></div>
            <div class="sc-card p-2.5 sm:p-4 text-center"><p class="text-lg sm:text-2xl font-black text-amber-400">{{ number_format($stats['best_pot']) }}</p><p class="text-[9px] sm:text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-1">Best Savings (KES)</p></div>
        </div>

        <div class="sc-card p-5 mb-6">
            <p class="text-sm font-bold mb-1">Your starting savings</p>
            <p class="text-xs text-gray-400 mb-4">{{ $tier ? $tier->label . ' — KES ' . number_format($tier->stake_amount) : 'No starting-savings tier configured yet.' }}. This leaves your wallet and becomes your in-game savings for this round — grow it by playing well and bank it anytime. If it runs out, the round ends early.</p>
            <form method="POST" action="{{ route('arcade.snakes.solo') }}">
                @csrf
                <button type="submit" class="sc-btn text-white w-full" style="background:linear-gradient(135deg,#f59e0b,#d97706);">🎲 Play Solo</button>
            </form>
        </div>

        <div class="grid sm:grid-cols-2 gap-4 mb-6">
            <div class="sc-card p-5">
                <p class="text-sm font-bold mb-3">👥 Start a match with friends</p>
                <form method="POST" action="{{ route('arcade.snakes.match.create') }}" class="space-y-2">
                    @csrf
                    <select name="visibility" class="sc-input">
                        <option value="private">Private (join by code)</option>
                        <option value="public">Public (anyone can join)</option>
                    </select>
                    <select name="max_players" class="sc-input">
                        <option value="2">2 players</option>
                        <option value="4" selected>4 players</option>
                        <option value="6">6 players</option>
                    </select>
                    <p class="text-[11px] text-gray-500">🎲 Players take turns, one roll at a time.</p>
                    <button type="submit" class="sc-btn text-white w-full" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">Create Match</button>
                </form>
            </div>
            <div class="sc-card p-5">
                <p class="text-sm font-bold mb-3">🔑 Join with a code</p>
                <form method="POST" action="{{ route('arcade.snakes.match.join') }}" class="space-y-2">
                    @csrf
                    <input id="joinCodeInput" name="code" maxlength="8" value="{{ request('join') }}" placeholder="e.g. AB12CD" class="sc-input uppercase" style="letter-spacing:.15em;">
                    <button type="submit" class="sc-btn text-white w-full" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">Join</button>
                </form>
            </div>
        </div>

        <div class="sc-card p-5 mb-6">
            <p class="text-sm font-bold mb-3">🌍 Open public matches</p>
            @if($openMatches->isNotEmpty())
            <div class="space-y-2">
                @foreach($openMatches as $match)
                <form method="POST" action="{{ route('arcade.snakes.match.join') }}" class="flex items-center justify-between gap-3 p-3 rounded-xl" style="background:rgba(255,255,255,.02);">
                    @csrf
                    <input type="hidden" name="match_id" value="{{ $match->id }}">
                    <span class="text-xs text-gray-400">Match #{{ $match->id }} — {{ $match->sessions_count }}/{{ $match->max_players }} players</span>
                    <button type="submit" class="sc-btn text-white" style="background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.35);color:#a5b4fc;padding:.4rem .9rem;">Join</button>
                </form>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-500 text-center py-3">No public matches waiting for players right now — create one above, or check back soon.</p>
            @endif
        </div>

        {{-- ── RIVALS TRAIL — head-to-head money round ── --}}
        <div class="sc-card p-5 mb-6" style="border-color:rgba(236,72,153,.3);">
            <p class="text-sm font-bold mb-1">⚔️ Rivals Trail — head-to-head round</p>
            <p class="text-xs text-gray-400 mb-4">Set an entry amount every player matches. Win, and you bring in 60% of what each other player has built up in the round; lose, and you keep 40% of your own. Miss too many turns in a row and you're withdrawn — you keep 70% of your savings, and the rest joins the round's bonus pool for whoever wins.</p>
            <form method="POST" action="{{ route('arcade.snakes.wager.create') }}" class="space-y-2">
                @csrf
                <input type="number" name="stake_amount" min="{{ $minWagerStake }}" max="{{ $maxWagerStake }}" value="{{ $minWagerStake }}" placeholder="Entry amount (KES)" class="sc-input">
                <select name="visibility" class="sc-input">
                    <option value="private">Private (invite friends or share a code)</option>
                    <option value="public">Public (anyone can join)</option>
                </select>
                <select name="max_players" class="sc-input">
                    <option value="2" selected>2 players</option>
                    <option value="4">4 players</option>
                    <option value="6">6 players</option>
                </select>
                @if($friends->isNotEmpty())
                <div>
                    <p class="text-[11px] text-gray-500 mb-1">Invite friends (optional):</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($friends as $friend)
                        <label class="text-[11px] flex items-center gap-1 px-2 py-1 rounded-lg" style="background:rgba(255,255,255,.04);">
                            <input type="checkbox" name="invite_ids[]" value="{{ $friend->id }}"> {{ $friend->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif
                <button type="submit" class="sc-btn text-white w-full" style="background:linear-gradient(135deg,#ec4899,#be185d);">Create Rivals Trail Round</button>
            </form>
        </div>

        <div class="sc-card p-5 mb-6">
            <p class="text-sm font-bold mb-3">🔑 Join a Rivals Trail round with a code</p>
            <form method="POST" action="{{ route('arcade.snakes.wager.join') }}" class="space-y-2">
                @csrf
                <input name="code" maxlength="8" placeholder="e.g. AB12CD" class="sc-input uppercase" style="letter-spacing:.15em;">
                <button type="submit" class="sc-btn text-white w-full" style="background:linear-gradient(135deg,#ec4899,#be185d);">Join Round</button>
            </form>
        </div>

        <div class="sc-card p-5">
            <p class="text-sm font-bold mb-3">🌍 Open Rivals Trail rounds</p>
            @if($openWagerMatches->isNotEmpty())
            <div class="space-y-2">
                @foreach($openWagerMatches as $wm)
                <form method="POST" action="{{ route('arcade.snakes.wager.join') }}" class="flex items-center justify-between gap-3 p-3 rounded-xl" style="background:rgba(255,255,255,.02);">
                    @csrf
                    <input type="hidden" name="match_id" value="{{ $wm->id }}">
                    <span class="text-xs text-gray-400">Round #{{ $wm->id }} — KES {{ number_format($wm->stake_amount) }} entry — {{ $wm->sessions_count }}/{{ $wm->max_players }} players</span>
                    <button type="submit" class="sc-btn text-white" style="background:rgba(236,72,153,.2);border:1px solid rgba(236,72,153,.35);color:#f9a8d4;padding:.4rem .9rem;">Join</button>
                </form>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-500 text-center py-3">No public Rivals Trail rounds waiting for players right now — create one above, or check back soon.</p>
            @endif
        </div>
    </div>
    <script>
        async function quitActiveSession(cashOutUrl) {
            const btn = document.getElementById('quitActiveBtn');
            if (btn) { btn.disabled = true; btn.textContent = 'Quitting…'; }
            try {
                const res = await fetch(cashOutUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                }).then(r => r.json());
                if (!res.success) { alert(res.message || 'Could not quit this game.'); if (btn) { btn.disabled = false; btn.textContent = '🚪 Quit this game and start fresh'; } return; }
            } catch (e) {
                if (btn) { btn.disabled = false; btn.textContent = '🚪 Quit this game and start fresh'; }
                alert('Network error — try again.');
                return;
            }
            window.location.reload();
        }
    </script>
</body>
</html>
