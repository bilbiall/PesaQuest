<x-app-layout>
<style>
body{background:#07060f;}
.profile-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:1.1rem;padding:1rem;}
.ch-row{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:.85rem;padding:.7rem .85rem;display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;}
.ch-track{height:6px;background:rgba(255,255,255,.07);border-radius:9999px;overflow:hidden;flex:1;min-width:100px;}
.ch-fill{height:100%;border-radius:9999px;background:linear-gradient(90deg,#6366f1,#a78bfa);transition:width .6s ease;}
.status-chip{font-size:.62rem;font-weight:800;padding:.2rem .55rem;border-radius:9999px;flex-shrink:0;white-space:nowrap;}
.status-chip.waiting{background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.3);}
.status-chip.live{background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.3);}
.trend{font-size:.68rem;font-weight:800;flex-shrink:0;}
.trend.up{color:#34d399;} .trend.down{color:#f87171;} .trend.flat{color:#fbbf24;} .trend.none{color:#6b7280;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
</style>

<div class="min-h-screen px-4 py-6 max-w-4xl mx-auto" style="background:#07060f;">
    <a href="{{ route('world') }}" class="text-gray-400 hover:text-white text-sm mb-3 inline-flex items-center gap-2">← Back to Game</a>
    <div class="flex items-center justify-between flex-wrap gap-3 mb-4" style="animation:fadeUp .4s ease both;">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white">🏆 Champions' Court</h1>
            <p class="text-xs text-gray-400 mt-1">Fair challenges — everyone races on progress made <em>during</em> the challenge, never on wealth you already had.</p>
            <p class="text-[.65rem] text-gray-500 mt-0.5">⏱️ Durations run on real calendar days — not your in-game clock.</p>
        </div>
        <a href="{{ route('challenges.create') }}" class="px-3.5 py-2 rounded-lg text-xs font-black text-white transition-all hover:scale-[1.02]" style="background:linear-gradient(135deg,#6366f1,#4338ca);">⚔️ Challenge a Friend</a>
    </div>

    @if(session('success'))
    <div class="mb-4 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-emerald-300 border border-emerald-500/30" style="background:rgba(16,185,129,0.1);">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-red-300 border border-red-500/30" style="background:rgba(239,68,68,0.1);">⚠ {{ session('error') }}</div>
    @endif

    {{-- Pending invites — need MY response --}}
    @if($pendingInvites->count())
    <div class="profile-card mb-4" style="animation:fadeUp .4s .05s ease both;">
        <h2 class="text-xs font-black text-white mb-3">✉️ Pending Invites ({{ $pendingInvites->count() }})</h2>
        <div class="space-y-2">
            @foreach($pendingInvites as $p)
            <div class="ch-row">
                <span class="text-xl">{{ $p->challenge->template?->icon ?? '⚔️' }}</span>
                <div class="flex-1 min-w-[140px]">
                    <div class="font-bold text-white text-xs">{{ $p->challenge->title }}</div>
                    <div class="text-[.65rem] text-gray-500">from {{ $p->challenge->creator?->name ?? 'a player' }}{{ $p->challenge->stake_amount ? ' · KES '.number_format($p->challenge->stake_amount).' entry fee' : '' }}</div>
                </div>
                <form method="POST" action="{{ route('challenges.accept', $p) }}">@csrf<button class="px-2.5 py-1 rounded-md text-[.68rem] font-black text-white" style="background:#059669;">Accept</button></form>
                <form method="POST" action="{{ route('challenges.decline', $p) }}">@csrf<button class="px-2.5 py-1 rounded-md text-[.68rem] font-bold text-gray-300" style="background:rgba(255,255,255,.06);">Decline</button></form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- My Challenges — waiting-to-start AND fully-live shown together, so
         creating/sending one shows up here right away instead of hiding in a
         separate "waiting" section until the other side accepts. --}}
    <div class="profile-card mb-4" style="animation:fadeUp .4s .1s ease both;">
        <h2 class="text-xs font-black text-white mb-3">⚡ My Challenges ({{ $myChallenges->count() }})</h2>
        @if($myChallenges->isEmpty())
            <p class="text-xs text-gray-500">Nothing here yet — join an open challenge below or send one to a friend.</p>
        @else
        <div class="space-y-2">
            @foreach($myChallenges as $p)
            @php
                $c        = $p->challenge;
                $isLive   = $c->status === 'active';
                $pct      = $isLive && $c->goal > 0 ? max(0, min(100, ($p->progress / $c->goal) * 100)) : 0;
                $others   = $c->participants->where('id', '!=', $p->id)->values();
                $change   = $p->rank_change ?? null;
            @endphp
            <a href="{{ route('challenges.show', $c) }}" class="ch-row" style="text-decoration:none;">
                <span class="text-xl">{{ $c->template?->icon ?? '⚔️' }}</span>
                <div class="flex-1 min-w-[140px]">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-white text-xs">{{ $c->title }}</span>
                        <span class="status-chip {{ $isLive ? 'live' : 'waiting' }}">
                            @if($isLive) 🔴 Live @else ⏳ Waiting @endif
                        </span>
                    </div>
                    <div class="text-[.65rem] text-gray-500 mt-0.5">
                        @if($isLive)
                            {{ $c->endsLabel() }}
                        @else
                            @foreach($others as $other)
                                {{ $other->user?->name ?? 'Player' }}: {{ $other->status === 'accepted' ? '✓ accepted' : ($other->status === 'declined' ? '✗ declined' : '… waiting') }}@if(!$loop->last), @endif
                            @endforeach
                        @endif
                    </div>
                    @if($c->describeRequirements())
                    <div class="text-[.6rem] text-amber-400 font-semibold mt-0.5">{{ $c->describeRequirements() }}</div>
                    @endif
                </div>
                @if($isLive)
                <div class="ch-track"><div class="ch-fill" style="width:{{ $pct }}%;"></div></div>
                <span class="text-xs font-black text-indigo-300">{{ number_format($p->progress, 1) }}{{ $c->styleSuffix() }} / {{ number_format($c->goal, 0) }}{{ $c->styleSuffix() }}</span>
                <span class="trend {{ $change === null ? 'none' : ($change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat')) }}">
                    @if($change === null) – @elseif($change > 0) ↑{{ $change }} @elseif($change < 0) ↓{{ abs($change) }} @else — @endif
                </span>
                @endif
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Open PesaCity / public challenges to join --}}
    <div class="profile-card mb-4" style="animation:fadeUp .4s .15s ease both;">
        <h2 class="text-xs font-black text-white mb-3">📢 Open Challenges</h2>
        @if($openBroadcasts->isEmpty())
            <p class="text-xs text-gray-500">No open challenges right now — check back soon.</p>
        @else
        <div class="space-y-2">
            @foreach($openBroadcasts as $c)
            <div class="ch-row">
                <span class="text-xl">{{ $c->template?->icon ?? '🏆' }}</span>
                <a href="{{ route('challenges.show', $c) }}" class="flex-1 min-w-[140px]" style="text-decoration:none;">
                    <div class="font-bold text-white text-xs">{{ $c->is_official ? '🏙️ ' : '' }}{{ $c->title }}</div>
                    <div class="text-[.65rem] text-gray-500">{{ $c->participants_count }} joined · levels {{ $c->level_min }}–{{ $c->level_max }} · {{ \Illuminate\Support\Str::lower($c->endsLabel()) }}{{ $c->stake_amount ? ' · KES '.number_format($c->stake_amount).' entry' : '' }}</div>
                    @if($c->describeRequirements())
                    <div class="text-[.6rem] text-amber-400 font-semibold mt-0.5">{{ $c->describeRequirements() }}</div>
                    @endif
                </a>
                <form method="POST" action="{{ route('challenges.join', $c) }}">
                    @csrf
                    <button class="px-2.5 py-1.5 rounded-md text-[.68rem] font-black text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);">Join</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Inter-chama battles --}}
    @if($chamaBattles->isNotEmpty())
    <div class="profile-card mb-4" style="animation:fadeUp .4s .18s ease both;">
        <h2 class="text-xs font-black text-white mb-1">⚔️ Inter-Chama Battles</h2>
        <p class="text-[.65rem] text-gray-500 mb-3">A chairman enters their WHOLE chama at once — ranked chama-vs-chama by average member progress, so team size never decides it.</p>
        <div class="space-y-2">
            @foreach($chamaBattles as $c)
            @php
                $enteredChamaIds = $c->participants->pluck('chama_id')->filter()->unique();
                $available = $myChairmanships->reject(fn ($m) => $enteredChamaIds->contains($m->chama_id));
            @endphp
            <div class="ch-row">
                <span class="text-xl">{{ $c->template?->icon ?? '⚔️' }}</span>
                <a href="{{ route('challenges.show', $c) }}" class="flex-1 min-w-[140px]" style="text-decoration:none;">
                    <div class="font-bold text-white text-xs">{{ $c->title }}</div>
                    <div class="text-[.65rem] text-gray-500">{{ $c->participants_count }} players across {{ $enteredChamaIds->count() }} chama(s) · {{ \Illuminate\Support\Str::lower($c->endsLabel()) }}</div>
                </a>
                @if($available->isEmpty())
                    @if($myChairmanships->isEmpty())
                    <span class="text-[.65rem] text-gray-600">Chair a chama to enter</span>
                    @else
                    <span class="text-[.65rem] text-emerald-400 font-bold">✓ Your chama is in</span>
                    @endif
                @elseif($available->count() === 1)
                <form method="POST" action="{{ route('challenges.enter-chama', $c) }}">
                    @csrf
                    <input type="hidden" name="chama_id" value="{{ $available->first()->chama_id }}">
                    <button class="px-2.5 py-1.5 rounded-md text-[.68rem] font-black text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);">Enter {{ $available->first()->chama->name }}</button>
                </form>
                @else
                <form method="POST" action="{{ route('challenges.enter-chama', $c) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="chama_id" class="rounded-md text-[.68rem] px-2 py-1.5" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:#fff;">
                        @foreach($available as $m)
                            <option value="{{ $m->chama_id }}">{{ $m->chama->name }}</option>
                        @endforeach
                    </select>
                    <button class="px-2.5 py-1.5 rounded-md text-[.68rem] font-black text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);">Enter</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent results --}}
    @if($myCompleted->count())
    <div class="profile-card" style="animation:fadeUp .4s .2s ease both;">
        <h2 class="text-xs font-black text-white mb-3">📋 Recent Results</h2>
        <div class="space-y-1.5">
            @foreach($myCompleted as $p)
            <div class="ch-row" style="padding:.5rem .75rem;">
                <span class="text-lg">{{ $p->is_winner ? '🏆' : '🎗️' }}</span>
                <div class="flex-1 text-xs">
                    <span class="font-bold text-white">{{ $p->challenge->title }}</span>
                    <span class="text-gray-500"> — {{ $p->is_winner ? 'You won!' : 'Final: '.number_format($p->progress,1).$p->challenge->styleSuffix() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</x-app-layout>
