{{-- Life section tab bar — replaces the old life-topnav. Four tabs share one
     page shell (see partials.life-spa): tapping a tab swaps #life-panel's
     content via fetch, no full page reload. Direct links/reloads still work
     normally since each tab is also a real route. --}}
@props(['active' => 'board'])
@php
$ltTabs = [
    ['key' => 'board',     'icon' => '🌍', 'label' => 'Life HQ',   'href' => route('life.board')],
    ['key' => 'career',    'icon' => '💼', 'label' => 'Career',    'href' => route('life.career')],
    ['key' => 'timeline',  'icon' => '📖', 'label' => 'Story',     'href' => route('life.timeline')],
    ['key' => 'finances',  'icon' => '🧾', 'label' => 'Finances',  'href' => route('life.finances')],
];
$ltUser = auth()->user();
$ltInitials = strtoupper(substr($ltUser->name, 0, 1)) . strtoupper(substr(explode(' ', $ltUser->name)[1] ?? '', 0, 1));
@endphp

<style>
    .lt-bar { position:sticky; top:0; z-index:8000; background:rgba(7,6,15,.92); border-bottom:1px solid rgba(255,255,255,.06); backdrop-filter:blur(16px); }
    .lt-top { max-width:80rem; margin:0 auto; padding:.55rem 1rem; display:flex; align-items:center; gap:.6rem; }
    .lt-logo img { height:2.1rem; width:auto; border-radius:.6rem; display:block; }
    .lt-avatar { width:2rem; height:2rem; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-left:auto;
                 font-size:.68rem; font-weight:900; color:#fff; text-decoration:none; flex-shrink:0;
                 background:linear-gradient(135deg,#6366f1,#a78bfa); }
    .lt-tabs { max-width:80rem; margin:0 auto; padding:0 1rem .5rem; display:flex; gap:.35rem; overflow-x:auto; scrollbar-width:none; }
    .lt-tabs::-webkit-scrollbar { display:none; }
    .lt-tab { display:flex; align-items:center; gap:.4rem; flex-shrink:0; padding:.5rem .9rem; border-radius:.75rem;
               font-size:.8rem; font-weight:800; color:#9ca3af; text-decoration:none; border:1px solid transparent; transition:all .15s; cursor:pointer; }
    .lt-tab:hover { color:#fff; background:rgba(255,255,255,.05); }
    .lt-tab.lt-active { color:#a5b4fc; background:rgba(99,102,241,.16); border-color:rgba(99,102,241,.4); }
</style>

<nav class="lt-bar">
    <div class="lt-top">
        <a href="{{ route('dashboard') }}" class="lt-logo">
            <img src="{{ asset('moski-logo.png') }}" alt="Moski">
        </a>
        @include('partials.game-calendar', ['inline' => true])
        <a href="{{ route('profile.edit') }}" class="lt-avatar" title="Profile">{{ $ltInitials }}</a>
    </div>
    <div class="lt-tabs">
        @foreach($ltTabs as $t)
            <a href="{{ $t['href'] }}" data-life-tab="{{ $t['key'] }}" class="lt-tab {{ $active === $t['key'] ? 'lt-active' : '' }}">
                <span>{{ $t['icon'] }}</span> {{ $t['label'] }}
            </a>
        @endforeach
    </div>
</nav>
