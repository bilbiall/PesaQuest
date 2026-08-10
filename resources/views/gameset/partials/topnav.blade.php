{{-- Shared GameSet navigation — grouped menus + mobile hamburger.
     Self-contained: brings its own CSS, safe to @include on any gameset page.
     Usage: @include('gameset.partials.topnav', ['active' => 'assets']) --}}
@php
$active = $active ?? '';
$gsnGroups = [
    'economy' => ['label' => 'Economy', 'icon' => '💰', 'items' => [
        ['key' => 'assets', 'icon' => '🛒', 'label' => 'Marketplace Assets', 'route' => 'gameset.assets.index'],
        ['key' => 'bills',  'icon' => '🧾', 'label' => 'Bills',              'route' => 'gameset.bills.index'],
        ['key' => 'deals',  'icon' => '📈', 'label' => 'Investment Deals',   'route' => 'gameset.deals.index'],
        ['key' => 'shares', 'icon' => '📊', 'label' => 'Equity Square Shares','route' => 'gameset.shares.index'],
        ['key' => 'loans',  'icon' => '🏦', 'label' => 'Loan Products',      'route' => 'gameset.loans.index'],
    ]],
    'learning' => ['label' => 'Learning', 'icon' => '🎓', 'items' => [
        ['key' => 'courses', 'icon' => '🎓', 'label' => 'Courses', 'route' => 'gameset.courses.index'],
        ['key' => 'jobs',    'icon' => '💼', 'label' => 'Jobs',    'route' => 'gameset.jobs.index'],
        ['key' => 'quests',  'icon' => '📜', 'label' => 'Quests',  'route' => 'gameset.quests.index'],
        ['key' => 'badges',  'icon' => '🏅', 'label' => 'Badges',  'route' => 'gameset.badges.index'],
    ]],
    'world' => ['label' => 'World & Events', 'icon' => '🌍', 'items' => [
        ['key' => 'world-map',   'icon' => '🗺️', 'label' => 'World Map',      'route' => 'gameset.world.index'],
        ['key' => 'life-events', 'icon' => '🎲', 'label' => 'Life Events',    'route' => 'gameset.life-events.index'],
        ['key' => 'crises',      'icon' => '🌪️', 'label' => 'Crisis Events',  'route' => 'gameset.crises.index'],
        ['key' => 'fun-world',   'icon' => '🎢', 'label' => 'Fun World',      'route' => 'gameset.fun-world.index'],
        ['key' => 'spin',        'icon' => '🎡', 'label' => 'Spin Wheel',     'route' => 'gameset.spin.index'],
        ['key' => 'arcade',      'icon' => '🐍', 'label' => 'Arcade',         'route' => 'gameset.arcade.index'],
        ['key' => 'dreams',      'icon' => '🌟', 'label' => 'Dreams',         'route' => 'gameset.dreams.index'],
        ['key' => 'challenges',  'icon' => '🏆', 'label' => 'Challenges',     'route' => 'gameset.challenges.index'],
    ]],
    'settings' => ['label' => 'Settings', 'icon' => '⚙️', 'items' => [
        ['key' => 'game-rules',        'icon' => '📏', 'label' => 'Game Rules',            'url' => route('gameset.index') . '#gs-game-rules'],
        ['key' => 'life-chapters',     'icon' => '🌱', 'label' => 'Life Chapters',         'url' => route('gameset.index') . '#gs-life-chapters'],
        ['key' => 'xp-levels',         'icon' => '⚙️', 'label' => 'XP Levels',             'url' => route('gameset.index') . '#gs-xp-levels'],
        ['key' => 'hustle-tips',       'icon' => '💡', 'label' => 'Hustle Tips',           'url' => route('gameset.index') . '#gs-hustle-tips'],
        ['key' => 'journey',          'icon' => '🗺️', 'label' => 'Journey Milestones',    'url' => route('gameset.index') . '#gs-journey-milestones'],
        ['key' => 'career',           'icon' => '🧭', 'label' => 'Career Fields & Tracks','url' => route('gameset.index') . '#gs-career'],
        ['key' => 'quiz',             'icon' => '🎯', 'label' => 'Career Quiz',           'url' => route('gameset.index') . '#gs-quiz'],
        ['key' => 'financing',        'icon' => '🏦', 'label' => 'Asset Financing',       'url' => route('gameset.index') . '#gs-financing'],
        ['key' => 'onboarding',       'icon' => '🧭', 'label' => 'Onboarding Wizard',     'url' => route('gameset.index') . '#gs-onboarding'],
    ]],
];
@endphp

<style>
    .gsn-bar { background:rgba(8,7,16,.92); border-bottom:1px solid rgba(255,255,255,.06); backdrop-filter:blur(14px); }
    .gsn-inner { max-width:80rem; margin:0 auto; padding:.65rem 1rem; display:flex; align-items:center; gap:.75rem; }
    .gsn-home { display:flex; align-items:center; gap:.55rem; text-decoration:none; flex-shrink:0; }
    .gsn-home img { height:2.1rem; width:auto; border-radius:.6rem; }
    .gsn-home span { font-weight:900; font-size:.9rem; color:#fff; letter-spacing:-.01em; white-space:nowrap; }
    .gsn-home small { display:block; font-size:.6rem; font-weight:700; color:#818cf8; text-transform:uppercase; letter-spacing:.12em; }
    .gsn-desktop { display:none; align-items:center; gap:.25rem; flex:1; min-width:0; }
    .gsn-grp { position:relative; }
    .gsn-grp-btn { display:flex; align-items:center; gap:.4rem; padding:.5rem .8rem; border-radius:.75rem; font-size:.82rem; font-weight:700; color:#9ca3af; background:transparent; border:1px solid transparent; cursor:pointer; transition:all .15s; white-space:nowrap; }
    .gsn-grp-btn:hover { color:#fff; background:rgba(255,255,255,.05); }
    .gsn-grp-btn.gsn-on { color:#a5b4fc; background:rgba(99,102,241,.12); border-color:rgba(99,102,241,.3); }
    .gsn-caret { font-size:.55rem; opacity:.6; }
    .gsn-drop { position:absolute; top:calc(100% + .4rem); left:0; min-width:14rem; background:#12101f; border:1px solid rgba(99,102,241,.25); border-radius:1rem; padding:.4rem; box-shadow:0 18px 40px rgba(0,0,0,.55); z-index:9600; }
    .gsn-item { display:flex; align-items:center; gap:.6rem; padding:.55rem .7rem; border-radius:.65rem; font-size:.82rem; font-weight:600; color:#c7cad4; text-decoration:none; transition:all .12s; }
    .gsn-item:hover { background:rgba(99,102,241,.14); color:#fff; }
    .gsn-item.gsn-on { background:rgba(99,102,241,.18); color:#a5b4fc; }
    .gsn-right { margin-left:auto; display:flex; align-items:center; gap:.4rem; flex-shrink:0; }
    .gsn-chip { display:none; align-items:center; gap:.35rem; padding:.45rem .75rem; border-radius:.7rem; font-size:.78rem; font-weight:700; color:#9ca3af; text-decoration:none; border:1px solid rgba(255,255,255,.08); transition:all .15s; white-space:nowrap; }
    .gsn-chip:hover { color:#fff; background:rgba(255,255,255,.05); }
    .gsn-burger { display:flex; align-items:center; justify-content:center; width:2.4rem; height:2.4rem; border-radius:.7rem; background:rgba(99,102,241,.12); border:1px solid rgba(99,102,241,.3); color:#a5b4fc; cursor:pointer; }
    /* Mobile drawer */
    .gsn-overlay { position:fixed; inset:0; z-index:9700; background:rgba(0,0,0,.72); backdrop-filter:blur(4px); }
    .gsn-drawer { position:fixed; top:0; right:0; bottom:0; z-index:9710; width:min(20rem,86vw); background:#0d0b1a; border-left:1px solid rgba(99,102,241,.25); padding:1.1rem; overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
    .gsn-drawer h4 { font-size:.62rem; font-weight:900; text-transform:uppercase; letter-spacing:.14em; color:#6b7280; margin:1.1rem 0 .35rem; }
    .gsn-drawer .gsn-item { padding:.7rem .75rem; font-size:.9rem; }
    @media (min-width:900px) {
        .gsn-desktop { display:flex; }
        .gsn-chip { display:flex; }
        .gsn-burger { display:none; }
    }
</style>

<div class="gsn-bar" x-data="{ gsnOpen: null, gsnDrawer: false }">
    <div class="gsn-inner">
        <a href="{{ route('gameset.index') }}" class="gsn-home">
            <img src="{{ asset('moski-logo.png') }}" alt="Moski">
            <span>GameSet<small>Content Hub</small></span>
        </a>

        {{-- Desktop grouped dropdowns --}}
        <div class="gsn-desktop">
            @foreach($gsnGroups as $gk => $grp)
                @php $grpActive = collect($grp['items'])->pluck('key')->contains($active); @endphp
                <div class="gsn-grp" @click.away="gsnOpen === '{{ $gk }}' && (gsnOpen = null)">
                    <button type="button" class="gsn-grp-btn {{ $grpActive ? 'gsn-on' : '' }}"
                            @click="gsnOpen = (gsnOpen === '{{ $gk }}' ? null : '{{ $gk }}')">
                        <span>{{ $grp['icon'] }}</span> {{ $grp['label'] }} <span class="gsn-caret">▼</span>
                    </button>
                    <div class="gsn-drop" x-show="gsnOpen === '{{ $gk }}'" x-transition.opacity.duration.120ms x-cloak>
                        @foreach($grp['items'] as $item)
                            <a href="{{ $item['url'] ?? route($item['route']) }}" class="gsn-item {{ $active === $item['key'] ? 'gsn-on' : '' }}">
                                <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="gsn-right">
            <a href="{{ route('gameset.docs') }}" class="gsn-chip {{ $active === 'docs' ? 'gsn-on' : '' }}">📚 Guide</a>
            <a href="{{ route('players.search') }}" class="gsn-chip">🔍 Players</a>
            <a href="{{ route('dashboard') }}" class="gsn-chip">🏠 Dashboard</a>
            <button type="button" class="gsn-burger" @click="gsnDrawer = true" aria-label="Open menu">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <template x-teleport="body">
        <div x-show="gsnDrawer" x-cloak style="position:relative;">
            <div class="gsn-overlay" x-show="gsnDrawer" x-transition.opacity.duration.150ms @click="gsnDrawer = false"></div>
            <div class="gsn-drawer" x-show="gsnDrawer" x-transition.opacity.duration.180ms>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;">
                    <span style="font-weight:900;color:#fff;">⚙️ GameSet Menu</span>
                    <button type="button" @click="gsnDrawer = false" style="color:#9ca3af;font-size:1.2rem;background:none;border:none;cursor:pointer;padding:.3rem .5rem;">✕</button>
                </div>
                <a href="{{ route('gameset.index') }}" class="gsn-item {{ $active === 'hub' ? 'gsn-on' : '' }}">🏛️ GameSet Hub</a>
                @foreach($gsnGroups as $grp)
                    <h4>{{ $grp['icon'] }} {{ $grp['label'] }}</h4>
                    @foreach($grp['items'] as $item)
                        <a href="{{ $item['url'] ?? route($item['route']) }}" class="gsn-item {{ $active === $item['key'] ? 'gsn-on' : '' }}">
                            <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                        </a>
                    @endforeach
                @endforeach
                <h4>🧭 Shortcuts</h4>
                <a href="{{ route('gameset.docs') }}" class="gsn-item {{ $active === 'docs' ? 'gsn-on' : '' }}">📚 GameSet Guide</a>
                <a href="{{ route('players.search') }}" class="gsn-item">🔍 Player Search</a>
                <a href="{{ route('dashboard') }}" class="gsn-item">🏠 Dashboard</a>
            </div>
        </div>
    </template>
</div>
