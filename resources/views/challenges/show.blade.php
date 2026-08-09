@php
    // Raw plain-text values only — the layout's @yield output is wrapped in a
    // single {{ }} escape, so these must NOT be pre-escaped here or special
    // characters (quotes, "&") would double-encode.
    $challengeSeoTitle = $challenge->title . ' — Challenge';
    $challengeSeoDesc  = "Track {$challenge->title} on PesaQuest Champions' Court — race to "
        . number_format($challenge->goal, 0) . $challenge->styleSuffix() . ' '
        . str_replace('_', ' ', $challenge->metric) . ' growth.';
@endphp
@section('title'){!! $challengeSeoTitle !!}@endsection
@section('meta_description'){!! $challengeSeoDesc !!}@endsection
<x-app-layout>
<style>
body{background:#07060f;}
.profile-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:1.1rem;padding:1.1rem;}
.rank-row{display:flex;align-items:center;gap:.7rem;padding:.65rem .85rem;border-radius:.85rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);margin-bottom:.5rem;position:relative;cursor:pointer;transition:background .15s;}
.rank-row:hover{background:rgba(255,255,255,.06);}
.rank-row.win{border-color:rgba(245,158,11,.6);background:linear-gradient(135deg,rgba(245,158,11,.14),rgba(251,191,36,.05));animation:winGlow 2.4s ease-in-out infinite;}
.rank-row.me{border-color:rgba(99,102,241,.5);}
.win-avatar-wrap{position:relative;flex-shrink:0;}
.win-crown{position:absolute;top:-9px;left:50%;transform:translateX(-50%);font-size:13px;filter:drop-shadow(0 1px 2px rgba(0,0,0,.6));}
@keyframes winGlow{0%,100%{box-shadow:0 0 10px rgba(245,158,11,.25);}50%{box-shadow:0 0 22px rgba(245,158,11,.55);}}
.win-banner{border-radius:1rem;padding:.9rem 1rem;display:flex;align-items:center;gap:.7rem;margin-bottom:1rem;}
.win-banner.mine{background:linear-gradient(135deg,rgba(245,158,11,.18),rgba(251,191,36,.08));border:1px solid rgba(245,158,11,.4);animation:winGlow 2.4s ease-in-out infinite;}
.win-banner.other{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.1);}
.win-banner.live{background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.2);}
.ch-track{height:6px;background:rgba(255,255,255,.07);border-radius:9999px;overflow:hidden;flex:1;min-width:80px;}
.ch-fill{height:100%;border-radius:9999px;background:linear-gradient(90deg,#6366f1,#a78bfa);}
.trend{font-size:.68rem;font-weight:800;flex-shrink:0;width:2.2rem;text-align:center;}
.trend.up{color:#34d399;} .trend.down{color:#f87171;} .trend.flat{color:#fbbf24;} .trend.none{color:#6b7280;}
</style>

<div class="min-h-screen px-4 py-6 max-w-2xl mx-auto" style="background:#07060f;">
    <div class="flex items-center gap-4 mb-4 text-sm">
        <a href="{{ route('challenges.index') }}" class="text-gray-400 hover:text-white inline-flex items-center gap-2">← Back to Champions' Court</a>
        <a href="{{ route('world') }}" class="text-gray-500 hover:text-white inline-flex items-center gap-2">🎮 Back to Game</a>
    </div>

    @php
        $iAmParticipant = $ranked->contains('user_id', auth()->id());
        $canJoin = !$iAmParticipant && $challenge->isBroadcast() && $challenge->status === 'active' && $challenge->scope === 'open' && !$challenge->is_chama_battle;
        $canCancel = $challenge->creator_id === auth()->id() && in_array($challenge->status, ['pending', 'active'], true);

        $iWon = false;
        $winnerLabel = null;
        if ($challenge->status === 'completed') {
            if ($challenge->is_chama_battle) {
                $winningRow = $chamaRanked->firstWhere('is_winner', true);
                $winnerLabel = $winningRow ? $winningRow['chama']->name : null;
                $iWon = $winningRow ? $winningRow['members']->contains('user_id', auth()->id()) : false;
            } else {
                $winners = $ranked->where('is_winner', true);
                $winnerLabel = $winners->pluck('user.name')->filter()->implode(' & ') ?: null;
                $iWon = $winners->contains('user_id', auth()->id());
            }
        }
    @endphp

    <div class="profile-card mb-4">
        <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex items-center gap-3">
                <span class="text-2xl">{{ $challenge->template?->icon ?? '🏆' }}</span>
                <div>
                    <h1 class="text-lg font-black text-white">{{ $challenge->title }}</h1>
                    <p class="text-xs text-gray-500">
                        {{ ucfirst($challenge->status) }} ·
                        {{ $challenge->status === 'active' ? 'Ends '.$challenge->ends_at->diffForHumans().' (real time)' : ($challenge->status === 'pending' ? 'Waiting for accept' : 'Finished '.$challenge->ends_at->diffForHumans()) }}
                        {{ $challenge->stake_amount ? ' · KES '.number_format($challenge->stake_amount).' entry fee' : '' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <button type="button" onclick="pqShareChallenge(this)" class="px-2.5 py-1.5 rounded-md text-[.68rem] font-black text-indigo-300" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);">🔗 Share</button>
                @if($canJoin)
                <form method="POST" action="{{ route('challenges.join', $challenge) }}">
                    @csrf
                    <button type="submit" class="px-2.5 py-1.5 rounded-md text-[.68rem] font-black text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);">Join</button>
                </form>
                @endif
                @if($canCancel)
                <form method="POST" action="{{ route('challenges.cancel', $challenge) }}" onsubmit="return confirm('Cancel this challenge? Any entry fee will be refunded.')">
                    @csrf
                    <button type="submit" class="px-2.5 py-1.5 rounded-md text-[.68rem] font-bold text-red-400" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);">🚫 Cancel</button>
                </form>
                @endif
            </div>
        </div>
        <p class="text-xs text-gray-500">Goal: {{ number_format($challenge->goal, 0) }}{{ $challenge->styleSuffix() }} {{ str_replace('_', ' ', $challenge->metric) }} growth since the challenge started.</p>
        @if($challenge->hasSecondMetric())
        <p class="text-xs text-gray-500">Also tracked: {{ number_format($challenge->goal_2, 0) }}{{ $challenge->styleSuffix($challenge->style_2) }} {{ str_replace('_', ' ', $challenge->metric_2) }} growth (doesn't decide the winner).</p>
        @endif
        @if($challenge->describeRequirements())
        <p class="text-xs text-amber-400 font-semibold mt-1.5">{{ $challenge->describeRequirements() }} — only participants meeting these can win.</p>
        @endif
    </div>

    @if($challenge->status === 'completed')
        @if($iWon)
        <div class="win-banner mine">
            <span style="font-size:1.8rem;">🎉</span>
            <div>
                <div class="text-sm font-black text-white">You won this challenge!</div>
                <div class="text-xs text-amber-300 mt-0.5">Trophy added to your Trophy Case — nice work.</div>
            </div>
        </div>
        @elseif($winnerLabel)
        <div class="win-banner other">
            <span style="font-size:1.8rem;">🏆</span>
            <div>
                <div class="text-sm font-black text-white">{{ $winnerLabel }} won this challenge</div>
                <div class="text-xs text-gray-500 mt-0.5">Final standings below — the race is done.</div>
            </div>
        </div>
        @else
        <div class="win-banner other">
            <span style="font-size:1.8rem;">🎗️</span>
            <div class="text-sm font-black text-white">Challenge over — nobody met the win requirements.</div>
        </div>
        @endif
    @endif

    <div class="profile-card">
        @if($challenge->is_chama_battle)
        @foreach($chamaRanked as $i => $row)
        @php
            $pct = $challenge->goal > 0 ? max(0, min(100, ($row['avg_progress'] / $challenge->goal) * 100)) : 0;
            $iAmIn = $row['members']->contains('user_id', auth()->id());
            $change = $row['rank_change'] ?? null;
        @endphp
        <details class="rank-row {{ $row['is_winner'] ? 'win' : '' }} {{ $iAmIn ? 'me' : '' }}" style="display:block;padding:.65rem .85rem;">
            <summary style="display:flex;align-items:center;gap:.7rem;cursor:pointer;list-style:none;">
                <span class="font-black text-gray-400 w-5 text-center text-sm">{{ $row['rank'] ?? ($i + 1) }}</span>
                <span class="text-lg">{{ $row['is_winner'] ? '🏆' : '🤝' }}</span>
                <div class="flex-1 min-w-[90px]">
                    <div class="text-xs font-bold text-white">{{ $row['chama']->name }}{{ $iAmIn ? ' (yours)' : '' }}</div>
                    <div class="text-[.62rem] text-gray-500">{{ $row['members']->count() }} member(s)</div>
                </div>
                <div class="ch-track"><div class="ch-fill" style="width:{{ $pct }}%;"></div></div>
                <span class="text-xs font-black text-indigo-300">{{ number_format($row['avg_progress'], 1) }}{{ $challenge->styleSuffix() }}</span>
                <span class="trend {{ $change === null ? 'none' : ($change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat')) }}">
                    @if($change === null) – @elseif($change > 0) ↑{{ $change }} @elseif($change < 0) ↓{{ abs($change) }} @else — @endif
                </span>
            </summary>
            <div class="mt-3 pl-8 space-y-1.5">
                @foreach($row['members'] as $m)
                <div class="flex items-center gap-2 text-xs" style="cursor:pointer;" onclick="event.stopPropagation();openStatsModal({{ $challenge->id }}, {{ $m->id }})">
                    @if($m->user?->profile_photo)
                    <img src="{{ $m->user->profile_photo }}" alt="" class="w-5 h-5 rounded-full object-cover flex-shrink-0">
                    @else
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[.55rem] font-black flex-shrink-0" style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.25);">{{ mb_strtoupper(mb_substr($m->user?->name ?? 'P', 0, 1)) }}</span>
                    @endif
                    <span class="text-gray-300 flex-1">{{ $m->user?->name ?? 'Player' }}{{ $m->user_id === auth()->id() ? ' (you)' : '' }}</span>
                    <span class="font-bold text-indigo-300">{{ number_format($m->progress, 1) }}{{ $challenge->styleSuffix() }}</span>
                </div>
                @endforeach
            </div>
        </details>
        @endforeach
        @else
        @foreach($ranked as $i => $p)
        @php
            $pct = $challenge->goal > 0 ? max(0, min(100, ($p->progress / $challenge->goal) * 100)) : 0;
            $change = $p->rank_change ?? null;
        @endphp
        <div class="rank-row {{ $p->is_winner ? 'win' : '' }} {{ $p->user_id === auth()->id() ? 'me' : '' }}" onclick="openStatsModal({{ $challenge->id }}, {{ $p->id }})">
            <span class="font-black text-gray-400 w-5 text-center text-sm">{{ $p->rank ?? ($i + 1) }}</span>
            <span class="win-avatar-wrap">
                @if($p->is_winner)<span class="win-crown">👑</span>@endif
                @if($p->user?->profile_photo)
                <img src="{{ $p->user->profile_photo }}" alt="" class="w-7 h-7 rounded-lg object-cover" style="box-shadow:0 0 0 2px {{ $p->is_winner ? '#fbbf24' : 'rgba(99,102,241,.2)' }};">
                @else
                <span class="text-lg">{{ $p->is_winner ? '🏆' : '👤' }}</span>
                @endif
            </span>
            <div class="flex-1 min-w-[90px]">
                <div class="text-xs font-bold text-white">{{ $p->user?->name ?? 'Player' }}{{ $p->user_id === auth()->id() ? ' (you)' : '' }}</div>
                <div class="text-[.62rem] text-gray-500">{{ $p->status === 'invited' ? 'Invited — not yet accepted' : ($p->status === 'declined' ? 'Declined' : '') }}</div>
                @if($p->status === 'accepted' && $challenge->hasSecondMetric())
                <div class="text-[.62rem] text-gray-500 mt-0.5">📊 {{ str_replace('_', ' ', $challenge->metric_2) }}: <span class="text-gray-300 font-bold">{{ number_format($p->progress_2 ?? 0, 1) }}{{ $challenge->styleSuffix($challenge->style_2) }}</span> / {{ number_format($challenge->goal_2, 0) }}{{ $challenge->styleSuffix($challenge->style_2) }}</div>
                @endif
            </div>
            @if($p->status === 'accepted')
            <div class="ch-track"><div class="ch-fill" style="width:{{ $pct }}%;"></div></div>
            <span class="text-xs font-black text-indigo-300">{{ number_format($p->progress, 1) }}{{ $challenge->styleSuffix() }}</span>
            <span class="trend {{ $change === null ? 'none' : ($change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat')) }}">
                @if($change === null) – @elseif($change > 0) ↑{{ $change }} @elseif($change < 0) ↓{{ abs($change) }} @else — @endif
            </span>
            @endif
        </div>
        @endforeach
        @endif
    </div>
</div>

<div id="statsModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);align-items:center;justify-content:center;padding:1rem;" onclick="if(event.target===this) closeStatsModal()">
    <div class="profile-card" style="max-width:300px;width:100%;text-align:center;position:relative;">
        <button type="button" onclick="closeStatsModal()" style="position:absolute;top:.4rem;right:.6rem;background:none;border:none;color:#9ca3af;font-size:1.1rem;cursor:pointer;">✕</button>
        <div id="statsModalBody" style="padding-top:.5rem;"><p class="text-xs text-gray-500">Loading…</p></div>
    </div>
</div>

<script>
function openStatsModal(challengeId, participantId) {
    const modal = document.getElementById('statsModal');
    const body = document.getElementById('statsModalBody');
    body.innerHTML = '<p class="text-xs text-gray-500">Loading…</p>';
    modal.style.display = 'flex';
    fetch(`/challenges/${challengeId}/participants/${participantId}/stats`, { headers: { 'Accept': 'application/json' } })
        .then(r => { if (!r.ok) throw new Error(); return r.json(); })
        .then(s => {
            const initial = (s.name || 'P').charAt(0).toUpperCase();
            body.innerHTML = `
                <div style="width:56px;height:56px;border-radius:.85rem;margin:0 auto .6rem;overflow:hidden;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.3rem;color:#fff;">
                    ${s.profile_photo ? `<img src="${s.profile_photo}" style="width:100%;height:100%;object-fit:cover;">` : initial}
                </div>
                <div style="font-weight:900;color:#fff;font-size:.95rem;">${s.name}</div>
                <div style="font-size:.68rem;color:#9ca3af;margin-bottom:.75rem;">Level ${s.level} · ${s.played_label}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                    <div style="background:rgba(255,255,255,.04);border-radius:.6rem;padding:.5rem;">
                        <div style="font-weight:900;color:#a78bfa;font-size:.85rem;">${Number(s.xp).toLocaleString()}</div>
                        <div style="font-size:.6rem;color:#9ca3af;text-transform:uppercase;font-weight:700;">XP</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04);border-radius:.6rem;padding:.5rem;">
                        <div style="font-weight:900;color:#34d399;font-size:.85rem;">KES ${Number(s.net_worth).toLocaleString()}</div>
                        <div style="font-size:.6rem;color:#9ca3af;text-transform:uppercase;font-weight:700;">Net Worth</div>
                    </div>
                    <div style="background:rgba(255,255,255,.04);border-radius:.6rem;padding:.5rem;grid-column:1/-1;">
                        <div style="font-weight:900;color:#fbbf24;font-size:.85rem;">🏅 ${s.badges_count}</div>
                        <div style="font-size:.6rem;color:#9ca3af;text-transform:uppercase;font-weight:700;">Badges</div>
                    </div>
                </div>`;
        })
        .catch(() => { body.innerHTML = '<p class="text-xs" style="color:#f87171;">Could not load stats.</p>'; });
}
function closeStatsModal() {
    document.getElementById('statsModal').style.display = 'none';
}

function pqShareChallenge(btn) {
    // The public invite page, not the auth-walled show page — a logged-out
    // recipient can actually see it, and link-preview bots can unfurl it.
    const url = window.location.origin + '{{ route('challenges.invite', $challenge, false) }}';
    const orig = btn.textContent;
    navigator.clipboard.writeText(url).then(() => {
        btn.textContent = '✓ Copied!';
        setTimeout(() => btn.textContent = orig, 1800);
    }).catch(() => {
        btn.textContent = 'Copy failed';
        setTimeout(() => btn.textContent = orig, 1800);
    });
}
</script>
</x-app-layout>
