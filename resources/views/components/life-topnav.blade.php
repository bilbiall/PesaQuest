@props(['active' => 'board', 'title' => 'Life HQ', 'icon' => '🌍'])
@php
$links = [
    ['key' => 'dashboard', 'icon' => '🏠', 'label' => 'Dashboard',   'href' => route('dashboard')],
    ['key' => 'world',     'icon' => '🏙️', 'label' => 'Pesa City',   'href' => route('world')],
    ['key' => 'board',     'icon' => '🌍', 'label' => 'Life HQ',     'href' => route('life.board')],
    ['key' => 'career',    'icon' => '💼', 'label' => 'Career',      'href' => route('life.career')],
    ['key' => 'timeline',  'icon' => '📖', 'label' => 'Life Story',  'href' => route('life.timeline')],
    ['key' => 'market',    'icon' => '🛒', 'label' => 'Marketplace', 'href' => route('marketplace')],
    ['key' => 'portfolio', 'icon' => '📊', 'label' => 'Portfolio',   'href' => route('portfolio')],
    ['key' => 'savings',   'icon' => '🏦', 'label' => 'Savings',     'href' => route('savings.index')],
    ['key' => 'quests',    'icon' => '📜', 'label' => 'Quests',      'href' => route('world', ['open' => 'quests'])],
    ['key' => 'chama',     'icon' => '🤝', 'label' => 'Chama',       'href' => route('chama.index')],
    ['key' => 'howto',     'icon' => '❓', 'label' => 'How To',      'href' => route('how-to')],
];
$user = auth()->user();
$initials = strtoupper(substr($user->name, 0, 1)) . strtoupper(substr(explode(' ', $user->name)[1] ?? '', 0, 1));
@endphp

<style>
    .lnv-bar { position:sticky; top:0; z-index:8000; background:rgba(7,6,15,.88); border-bottom:1px solid rgba(255,255,255,.06); backdrop-filter:blur(16px); }
    .lnv-inner { max-width:80rem; margin:0 auto; padding:.6rem 1rem; display:flex; align-items:center; gap:.7rem; }
    .lnv-logo img { height:2.3rem; width:auto; border-radius:.65rem; display:block; }
    .lnv-title { display:flex; align-items:center; gap:.4rem; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.25);
                 border-radius:999px; padding:.28rem .8rem; font-size:.72rem; font-weight:800; color:#34d399; white-space:nowrap; }
    .lnv-dot { width:.4rem; height:.4rem; border-radius:50%; background:#34d399; animation:lnvPulse 2s infinite; }
    @keyframes lnvPulse { 0%,100%{opacity:1;} 50%{opacity:.35;} }
    .lnv-links { display:none; align-items:center; gap:.3rem; margin-left:auto; }
    .lnv-pill { display:inline-flex; align-items:center; gap:.35rem; font-size:.75rem; font-weight:700; color:#9ca3af;
                padding:.42rem .75rem; border-radius:.7rem; border:1px solid rgba(255,255,255,.08); text-decoration:none; transition:all .15s; white-space:nowrap; }
    .lnv-pill:hover { color:#fff; background:rgba(255,255,255,.05); }
    .lnv-pill.lnv-on { color:#a5b4fc; background:rgba(99,102,241,.14); border-color:rgba(99,102,241,.4); }
    .lnv-avatar { width:2.1rem; height:2.1rem; border-radius:50%; display:flex; align-items:center; justify-content:center;
                  font-size:.7rem; font-weight:900; color:#fff; text-decoration:none; flex-shrink:0;
                  background:linear-gradient(135deg,#6366f1,#a78bfa); }
    .lnv-burger { margin-left:auto; display:flex; align-items:center; justify-content:center; width:2.4rem; height:2.4rem;
                  border-radius:.7rem; background:rgba(99,102,241,.12); border:1px solid rgba(99,102,241,.3); color:#a5b4fc; cursor:pointer; }
    .lnv-overlay { position:fixed; inset:0; z-index:9800; background:rgba(0,0,0,.72); backdrop-filter:blur(4px); }
    .lnv-drawer { position:fixed; top:0; right:0; bottom:0; z-index:9810; width:min(19rem,84vw); background:#0d0b1a;
                  border-left:1px solid rgba(99,102,241,.25); padding:1.1rem; overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
    .lnv-ditem { display:flex; align-items:center; gap:.7rem; padding:.75rem .8rem; border-radius:.8rem; font-size:.9rem;
                 font-weight:700; color:#c7cad4; text-decoration:none; transition:all .12s; margin-bottom:.15rem; }
    .lnv-ditem:hover { background:rgba(99,102,241,.14); color:#fff; }
    .lnv-ditem.lnv-on { background:rgba(99,102,241,.18); color:#a5b4fc; }
    .lnv-duser { display:flex; align-items:center; gap:.7rem; padding:.6rem .3rem 1rem; border-bottom:1px solid rgba(255,255,255,.07); margin-bottom:.8rem; }
    @media (min-width:900px) {
        .lnv-links { display:flex; }
        .lnv-burger { display:none; }
    }
    @media (max-width:520px) { .lnv-title { display:none; } }
</style>

<nav class="lnv-bar" x-data="{ lnvDrawer: false }">
    <div class="lnv-inner">
        <a href="{{ route('dashboard') }}" class="lnv-logo">
            <img src="{{ asset('moski-logo.png') }}" alt="Moski">
        </a>
        <span class="lnv-title"><span class="lnv-dot"></span> {{ $icon }} {{ $title }}</span>

        {{-- Desktop pills --}}
        <div class="lnv-links">
            @foreach($links as $l)
                @if(in_array($l['key'], ['dashboard','world','board','career','timeline','market','portfolio','chama','howto']))
                    <a href="{{ $l['href'] }}" class="lnv-pill {{ $active === $l['key'] ? 'lnv-on' : '' }}">{{ $l['icon'] }} {{ $l['label'] }}</a>
                @endif
            @endforeach
            <a href="{{ route('profile.edit') }}" class="lnv-avatar" title="Profile">{{ $initials }}</a>
        </div>

        {{-- Mobile hamburger --}}
        <button type="button" class="lnv-burger" @click="lnvDrawer = true" aria-label="Open menu">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
    </div>

    {{-- Mobile drawer --}}
    <template x-teleport="body">
        <div x-show="lnvDrawer" x-cloak>
            <div class="lnv-overlay" x-show="lnvDrawer" x-transition.opacity.duration.150ms @click="lnvDrawer = false"></div>
            <div class="lnv-drawer" x-show="lnvDrawer" x-transition.opacity.duration.180ms>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-weight:900;color:#fff;font-size:.95rem;">{{ $icon }} {{ $title }}</span>
                    <button type="button" @click="lnvDrawer = false" style="color:#9ca3af;font-size:1.2rem;background:none;border:none;cursor:pointer;padding:.3rem .5rem;">✕</button>
                </div>
                <a href="{{ route('profile.edit') }}" class="lnv-duser">
                    <span class="lnv-avatar">{{ $initials }}</span>
                    <span style="min-width:0;">
                        <span style="display:block;font-weight:800;color:#fff;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user->name }}</span>
                        <span style="display:block;font-size:.68rem;color:#6b7280;">View profile →</span>
                    </span>
                </a>
                @foreach($links as $l)
                    <a href="{{ $l['href'] }}" class="lnv-ditem {{ $active === $l['key'] ? 'lnv-on' : '' }}">
                        <span style="font-size:1.1rem;">{{ $l['icon'] }}</span> {{ $l['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </template>
</nav>
{{-- Game calendar HUD — every /life page carries the date chip (bills & paydays live here) --}}
@include('partials.game-calendar')
