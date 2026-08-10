{{-- $bar=false renders ONLY the themed menu sheet (#pq-menu) + its JS, no
     tab bar / FAB — for pages (world map) that already have their own bottom
     tab bar and just need a "Menu" button wired to pqMenuOpen(). --}}
@props(['active' => 'home', 'bar' => true])
@php
$tabs = [
    ['key'=>'home',      'href'=>route('dashboard'),        'label'=>'Home',    'path'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['key'=>'city',      'href'=>route('world'),             'label'=>'City',    'path'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
    ['key'=>'arcade',    'href'=>route('arcade.snakes.lobby'), 'label'=>'Arcade',  'svg'=>'<line x1="6" y1="11" x2="10" y2="11"/><line x1="8" y1="9" x2="8" y2="13"/><line x1="15" y1="12" x2="15" y2="12"/><line x1="18" y1="10" x2="18" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.544-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z"/>'],
    ['key'=>'life',      'href'=>route('life.board'),        'label'=>'Life',    'path'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
];
$pqUser = auth()->user();
@endphp
<style>
.pq-bottom-nav{display:none;}
@media(max-width:767px){
    @if($bar) body{padding-bottom:64px!important;} @endif
    .pq-bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;z-index:9000;background:rgba(7,6,15,.98);border-top:1px solid rgba(255,255,255,.07);backdrop-filter:blur(16px);}
    .pq-bottom-nav-inner{display:flex;width:100%;}
    .pq-bn-tab{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.18rem;padding:.6rem .25rem .5rem;text-decoration:none;color:rgba(255,255,255,.35);transition:color .2s;font-size:.58rem;font-weight:700;letter-spacing:.02em;background:none;border:none;cursor:pointer;}
    .pq-bn-tab svg{width:20px;height:20px;stroke-width:1.8;}
    .pq-bn-tab.pq-bn-active{color:#818cf8;}
    .pq-bn-tab.pq-bn-active svg{stroke:#818cf8;}
    .pq-menu-fab{display:none !important;}
}
/* Desktop: floating menu pill (mobile uses the ☰ tab instead) */
.pq-menu-fab{position:fixed;right:18px;bottom:18px;z-index:9001;display:inline-flex;align-items:center;gap:7px;
    padding:9px 16px;border-radius:999px;cursor:pointer;border:1px solid rgba(129,140,248,.4);
    background:rgba(10,9,20,.85);backdrop-filter:blur(12px);color:#c7d2fe;font-size:12.5px;font-weight:900;
    box-shadow:0 10px 32px rgba(0,0,0,.5);transition:transform .15s,border-color .15s;}
.pq-menu-fab:hover{transform:translateY(-2px) scale(1.03);border-color:rgba(129,140,248,.8);}

/* ── The themed menu sheet — a compact drawer, NOT a full takeover ── */
#pq-menu{position:fixed;inset:0;z-index:9500;display:none;}
#pq-menu.pq-open{display:block;}
.pq-menu-backdrop{position:absolute;inset:0;background:rgba(4,3,10,.55);backdrop-filter:blur(4px);animation:pqFade .2s ease;}
.pq-menu-sheet{position:absolute;left:50%;transform:translateX(-50%);
    bottom:calc(12px + env(safe-area-inset-bottom));width:min(400px, calc(100% - 24px));max-height:min(46vh,420px);overflow-y:auto;
    background:linear-gradient(180deg,#141127,#0b0918);border:1px solid rgba(129,140,248,.28);
    border-radius:22px;padding:14px;box-shadow:0 20px 50px rgba(0,0,0,.5);
    animation:pqSheetUp .28s cubic-bezier(.22,1.1,.36,1);}
@media(min-width:768px){ .pq-menu-sheet{bottom:18px;left:auto;right:18px;top:auto;transform:none;border-radius:20px;animation:pqSheetPop .24s cubic-bezier(.22,1.2,.36,1);box-shadow:0 20px 60px rgba(0,0,0,.55);} }
@keyframes pqFade{from{opacity:0;}}
@keyframes pqSheetUp{from{transform:translateX(-50%) translateY(40px);opacity:0;}}
@keyframes pqSheetPop{from{transform:translateY(16px) scale(.96);opacity:0;}}

.pq-menu-head{display:flex;align-items:center;gap:9px;margin-bottom:9px;}
.pq-menu-ava{width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;
    font-weight:900;font-size:13px;color:#fff;background:linear-gradient(135deg,#4f46e5,#a78bfa);box-shadow:0 0 0 2px rgba(129,140,248,.4);}
.pq-menu-ava img{width:100%;height:100%;object-fit:cover;}
.pq-menu-close{margin-left:auto;width:26px;height:26px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);
    color:#9ca3af;font-size:12px;cursor:pointer;transition:all .15s;}
.pq-menu-close:hover{color:#fff;transform:rotate(90deg);}

/* Standalone hero tiles (How to Play + Spin — deliberately outside the themes) */
.pq-heroes{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:9px;}
.pq-hero{display:flex;align-items:center;gap:7px;padding:8px 10px;border-radius:12px;text-decoration:none;
    font-size:11px;font-weight:900;color:#fff;transition:transform .14s,filter .14s;animation:pqItemIn .35s both;}
.pq-hero:hover{transform:translateY(-1px) scale(1.02);filter:brightness(1.15);}
.pq-hero .pq-hero-ic{display:inline-flex;animation:pqWiggle 3.2s ease-in-out infinite;}
.pq-hero .pq-hero-ic svg{width:18px;height:18px;display:block;}
.pq-hero small{display:block;font-size:8.5px;font-weight:700;opacity:.75;}
@keyframes pqWiggle{0%,88%,100%{transform:rotate(0);}92%{transform:rotate(-12deg);}96%{transform:rotate(10deg);}}

.pq-groups{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:7px;}
.pq-group{border-radius:13px;padding:7px 8px;border:1px solid var(--pq-gc,rgba(255,255,255,.09));background:rgba(255,255,255,.025);animation:pqItemIn .4s both;}
.pq-group-title{display:flex;align-items:center;gap:5px;font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;color:var(--pq-gt,#9ca3af);}
.pq-group-title svg{width:12px;height:12px;flex-shrink:0;}
.pq-item{display:flex;align-items:center;gap:7px;padding:4px 6px;border-radius:8px;text-decoration:none;
    font-size:11px;font-weight:800;color:#e5e7eb;transition:background .12s,transform .12s;cursor:pointer;background:none;border:none;width:100%;text-align:left;}
.pq-item:hover{background:rgba(255,255,255,.06);transform:translateX(2px);}
.pq-item:active{transform:scale(.97);}
.pq-item .pq-item-ic{display:inline-flex;width:18px;justify-content:center;flex-shrink:0;}
.pq-item .pq-item-ic svg{width:15px;height:15px;display:block;}
.pq-item small{margin-left:auto;font-size:8px;color:#6b7280;font-weight:600;}

/* Staggered pop-in */
@keyframes pqItemIn{from{opacity:0;transform:translateY(14px);}}
.pq-menu-sheet [data-stagger]{animation-delay:calc(var(--i) * 45ms);}
@media (prefers-reduced-motion: reduce){ .pq-menu-sheet, .pq-menu-sheet *{animation:none !important;} }
</style>

@if($bar)
<nav class="pq-bottom-nav">
    <div class="pq-bottom-nav-inner">
        @foreach($tabs as $tab)
        <a href="{{ $tab['href'] }}"
           class="pq-bn-tab {{ $active === $tab['key'] ? 'pq-bn-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                @if(isset($tab['svg'])){!! $tab['svg'] !!}@else<path d="{{ $tab['path'] }}"/>@endif
            </svg>
            {{ $tab['label'] }}
        </a>
        @endforeach
        <button type="button" class="pq-bn-tab" onclick="pqMenuOpen()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            Menu
        </button>
    </div>
</nav>

<button type="button" class="pq-menu-fab" onclick="pqMenuOpen()">☰ <span>Menu</span></button>
@endif

{{-- ── Themed menu sheet ── --}}
<div id="pq-menu" role="dialog" aria-label="Game menu">
    <div class="pq-menu-backdrop" onclick="pqMenuClose()"></div>
    <div class="pq-menu-sheet">
        <div class="pq-menu-head">
            <span class="pq-menu-ava">
                @if($pqUser?->avatar_url)<img src="{{ $pqUser->avatar_url }}" alt="" onerror="this.remove()">@else{{ strtoupper(substr($pqUser?->name ?? 'P', 0, 1)) }}@endif
            </span>
            <div>
                <div style="font-size:13px;font-weight:900;color:#fff;">{{ strtok($pqUser?->name ?? 'Player', ' ') }}</div>
                <div style="font-size:10px;color:#818cf8;font-weight:700;">Level {{ $pqUser?->progress?->level ?? 1 }} · Pesa City</div>
            </div>
            <button type="button" class="pq-menu-close" onclick="pqMenuClose()">✕</button>
        </div>

        {{-- Standalone: How to Play + Spin (deliberately outside the themes) --}}
        <div class="pq-heroes">
            <a href="{{ route('how-to') }}" class="pq-hero" data-stagger style="--i:0;background:linear-gradient(135deg,rgba(99,102,241,.3),rgba(79,70,229,.18));border:1px solid rgba(99,102,241,.4);" onclick="pqGo(event,this)">
                <span class="pq-hero-ic"><x-icon name="compass" /></span>
                <span>How to Play<small>the full guide</small></span>
            </a>
            <a href="{{ route('spin.index') }}" class="pq-hero" data-stagger style="--i:1;background:linear-gradient(135deg,rgba(245,158,11,.28),rgba(217,119,6,.16));border:1px solid rgba(245,158,11,.4);" onclick="pqGo(event,this)">
                <span class="pq-hero-ic"><x-icon name="spin" /></span>
                <span>Spin the Wheel<small>daily luck, zero risk</small></span>
            </a>
        </div>

        <div class="pq-groups">
            <div class="pq-group" data-stagger style="--i:2;--pq-gc:rgba(16,185,129,.25);--pq-gt:#6ee7b7;">
                <div class="pq-group-title"><x-icon name="wallet" /> Money</div>
                <a class="pq-item" href="{{ route('savings.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="bank" /></span> Savings</a>
                <a class="pq-item" href="{{ route('portfolio') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="bar-chart" /></span> Portfolio</a>
                <a class="pq-item" href="{{ route('marketplace') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="shopping-bag" /></span> Marketplace</a>
                <a class="pq-item" href="{{ route('money-toolkit') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="toolbox" /></span> Money Toolkit <small>premium</small></a>
            </div>
            <div class="pq-group" data-stagger style="--i:3;--pq-gc:rgba(77,168,247,.25);--pq-gt:#7cc0ff;">
                <div class="pq-group-title"><x-icon name="graduation" /> Grow</div>
                <a class="pq-item" href="{{ route('opportunities.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="book" /></span> Courses &amp; Jobs</a>
                <a class="pq-item" href="{{ route('world', ['open' => 'quests']) }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="checklist" /></span> Quests</a>
                <a class="pq-item" href="{{ route('game.leaderboard') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="trophy" /></span> Leaderboard</a>
            </div>
            <div class="pq-group" data-stagger style="--i:4;--pq-gc:rgba(245,158,11,.28);--pq-gt:#fbbf24;">
                <div class="pq-group-title"><x-icon name="gamepad" /> Play</div>
                <a class="pq-item" href="{{ route('arcade.snakes.lobby') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="gamepad" /></span> Arcade <small>Pesa Trail</small></a>
                <a class="pq-item" href="{{ route('dreams.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="star" /></span> Dreams</a>
                <a class="pq-item" href="{{ route('challenges.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="shield" /></span> Champions' Court</a>
            </div>
            <div class="pq-group" data-stagger style="--i:5;--pq-gc:rgba(167,139,250,.28);--pq-gt:#c4b5fd;">
                <div class="pq-group-title"><x-icon name="people" /> People</div>
                <a class="pq-item" href="{{ route('friends.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="people" /></span> Friends &amp; Loans</a>
                <a class="pq-item" href="{{ route('chama.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="group" /></span> Chamas</a>
                <a class="pq-item" href="{{ route('forums.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="speech" /></span> Forums</a>
            </div>
            <div class="pq-group" data-stagger style="--i:6;--pq-gc:rgba(245,158,11,.25);--pq-gt:#fcd34d;">
                <div class="pq-group-title"><x-icon name="house" /> Life</div>
                <a class="pq-item" href="{{ route('life.board') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="clipboard" /></span> Life HQ</a>
                <a class="pq-item" href="{{ route('life.career') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="briefcase" /></span> Career &amp; Work</a>
                <a class="pq-item" href="{{ route('life.timeline') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="calendar" /></span> My Timeline</a>
                <a class="pq-item" href="{{ route('inbox.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="mail" /></span> Life Inbox</a>
            </div>
            <div class="pq-group" data-stagger style="--i:7;--pq-gc:rgba(236,72,153,.25);--pq-gt:#f9a8d4;">
                <div class="pq-group-title"><x-icon name="id-card" /> Profile</div>
                @if($pqUser)
                <a class="pq-item" href="{{ route('players.show', $pqUser) }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="id-card" /></span> My Profile</a>
                @endif
                <a class="pq-item" href="{{ route('profile.edit') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="gear" /></span> Settings</a>
                <a class="pq-item" href="{{ route('subscribe.index') }}" onclick="pqGo(event,this)"><span class="pq-item-ic"><x-icon name="badge" /></span> Subscription</a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="pq-item" onclick="pqBlip()"><span class="pq-item-ic"><x-icon name="logout" /></span> Log out</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let ctx = null;
    function audio() {
        try { ctx = ctx || new (window.AudioContext || window.webkitAudioContext)(); if (ctx.state === 'suspended') ctx.resume(); return ctx; }
        catch (e) { return null; }
    }
    function tone(freq, dur, gain, delay = 0, type = 'sine', slide = 0) {
        const c = audio(); if (!c) return;
        const t = c.currentTime + delay;
        const g = c.createGain();
        g.gain.setValueAtTime(0, t);
        g.gain.linearRampToValueAtTime(gain, t + 0.012);
        g.gain.exponentialRampToValueAtTime(0.001, t + dur);
        g.connect(c.destination);
        const o = c.createOscillator();
        o.type = type;
        o.frequency.setValueAtTime(freq, t);
        if (slide) o.frequency.exponentialRampToValueAtTime(slide, t + dur);
        o.connect(g); o.start(t); o.stop(t + dur + 0.03);
    }

    window.pqBlip = function () { tone(880, 0.09, 0.05, 0, 'triangle', 1320); };

    // Smooth, duration-controlled scroll (native "smooth" scroll speed isn't
    // adjustable across browsers, so we tween scrollTop ourselves).
    function scrollTween(el, to, duration) {
        const from = el.scrollTop;
        const delta = to - from;
        if (!delta) return;
        const start = performance.now();
        (function step(now) {
            const p = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - p, 3); // ease-out cubic
            el.scrollTop = from + delta * eased;
            if (p < 1) requestAnimationFrame(step);
        })(start);
    }

    window.pqMenuOpen = function () {
        document.getElementById('pq-menu').classList.add('pq-open');
        document.body.style.overflow = 'hidden';
        tone(392, 0.14, 0.045, 0,    'sine', 587);   // rising two-note swoosh
        tone(587, 0.18, 0.045, 0.09, 'sine', 784);

        // Scroll affordance: once the sheet has popped in, peek down to its
        // bottom then ease back up — a quiet hint that it scrolls, without
        // requiring the player to discover that by accident.
        const sheet = document.querySelector('.pq-menu-sheet');
        if (sheet && sheet.scrollHeight > sheet.clientHeight + 4) {
            sheet.scrollTop = 0;
            setTimeout(() => {
                scrollTween(sheet, sheet.scrollHeight - sheet.clientHeight, 420);
                setTimeout(() => scrollTween(sheet, 0, 900), 420 + 550);
            }, 380);
        }
    };

    window.pqMenuClose = function () {
        document.getElementById('pq-menu').classList.remove('pq-open');
        document.body.style.overflow = '';
        tone(500, 0.1, 0.035, 0, 'sine', 330);       // soft descending thunk
    };

    // Item select: playful blip, tiny squash, then navigate
    window.pqGo = function (e, el) {
        e.preventDefault();
        pqBlip();
        el.style.transform = 'scale(.94)';
        setTimeout(() => { window.location.href = el.href; }, 140);
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.getElementById('pq-menu').classList.contains('pq-open')) pqMenuClose();
    });
})();
</script>

{{-- Redirect flash messages surface as top-right toasts (window.pesaToast from app.js) --}}
@if(session('error') || session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.pesaToast) return;
    @if(session('error'))window.pesaToast(@json(session('error')), 'error');@endif
    @if(session('success'))window.pesaToast(@json(session('success')), 'success');@endif
});
</script>
@endif

{{-- Rivals Trail invite popup — this component is included on every main game
     page (dashboard, world, life, marketplace, friends…), so this is the one
     place that reaches a player "wherever they are in the game" per the ask,
     rather than only when they happen to open the arcade lobby themselves. --}}
@auth
<div id="pq-invite-popup" style="display:none;position:fixed;inset:0;z-index:99990;align-items:center;justify-content:center;padding:1rem;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);">
    <div style="background:#14121f;border:1px solid rgba(236,72,153,.35);border-radius:1.25rem;padding:1.5rem;max-width:340px;width:100%;text-align:center;font-family:'Figtree',sans-serif;">
        <p style="font-size:2rem;margin-bottom:.4rem;">🎲</p>
        <p style="font-size:.95rem;font-weight:900;color:#fff;margin-bottom:.3rem;" id="pq-invite-text"></p>
        <p style="font-size:.7rem;color:#6b7280;margin-bottom:.3rem;" id="pq-invite-meta"></p>
        <p style="font-size:.75rem;color:#9ca3af;margin-bottom:1.2rem;">Rivals Trail — head-to-head Pesa Trail round</p>
        <div style="display:flex;gap:.6rem;">
            <button id="pq-invite-decline" style="flex:1;padding:.7rem;border-radius:.8rem;font-size:.8rem;font-weight:800;color:#9ca3af;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);cursor:pointer;">Decline</button>
            <button id="pq-invite-accept" style="flex:1;padding:.7rem;border-radius:.8rem;font-size:.8rem;font-weight:800;color:#fff;background:linear-gradient(135deg,#ec4899,#be185d);border:none;cursor:pointer;">Accept →</button>
        </div>
    </div>
</div>
<script>
(function () {
    const SEEN_KEY = 'pq_invite_dismissed';
    const POLL_MS = 45000;

    function dismissed() {
        try { return JSON.parse(sessionStorage.getItem(SEEN_KEY) || '[]'); } catch (_) { return []; }
    }
    function dismiss(id) {
        const list = dismissed();
        if (!list.includes(id)) list.push(id);
        try { sessionStorage.setItem(SEEN_KEY, JSON.stringify(list)); } catch (_) {}
    }

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    // Plain POST navigation (not fetch) — accepting takes the player straight
    // into the game, same as every other accept action in this app.
    function postNavigate(url) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        const token = document.createElement('input');
        token.type = 'hidden'; token.name = '_token'; token.value = csrf();
        form.appendChild(token);
        document.body.appendChild(form);
        form.submit();
    }

    let currentInvite = null;

    function showInvitePopup(invite) {
        currentInvite = invite;
        document.getElementById('pq-invite-text').textContent =
            `${invite.inviter_name} invited you to a round — entry KES ${Number(invite.stake_amount).toLocaleString()}`;
        const bits = [];
        if (invite.sent_at) bits.push(`Sent ${invite.sent_at}`);
        bits.push(invite.inviter_online ? '🟢 Online now' : '⚪ Offline');
        document.getElementById('pq-invite-meta').textContent = bits.join(' · ');
        document.getElementById('pq-invite-popup').style.display = 'flex';
    }

    async function checkInvites() {
        // Never interrupt an actual game in progress with a popup over the board.
        if (window.location.pathname.includes('/arcade/')) return;
        if (document.hidden) return;
        try {
            const res = await fetch('{{ route("arcade.snakes.invites.pending") }}', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            const seen = dismissed();
            const next = (data.invites || []).find(i => !seen.includes(i.id));
            if (next && !currentInvite) showInvitePopup(next);
        } catch (_) { /* silent — this is a background nicety, not core functionality */ }
    }

    const DECLINE_URL_TEMPLATE = '{{ route("arcade.snakes.wager.invite.decline", ["invite" => "__ID__"]) }}';
    document.getElementById('pq-invite-decline')?.addEventListener('click', () => {
        if (currentInvite) {
            const id = currentInvite.id;
            dismiss(id); // instant local hide, in case the request below is slow
            // A real decline, not just a client-side hide — otherwise the
            // same invite keeps resurfacing in a new tab/session, and it
            // never leaves the Arcade lobby's own invite list either.
            fetch(DECLINE_URL_TEMPLATE.replace('__ID__', id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
            }).catch(() => {});
        }
        document.getElementById('pq-invite-popup').style.display = 'none';
        currentInvite = null;
    });
    const ACCEPT_URL_TEMPLATE = '{{ route("arcade.snakes.wager.invite.accept", ["invite" => "__ID__"]) }}';
    document.getElementById('pq-invite-accept')?.addEventListener('click', () => {
        if (!currentInvite) return;
        postNavigate(ACCEPT_URL_TEMPLATE.replace('__ID__', currentInvite.id));
    });

    setTimeout(checkInvites, 3000); // let the page settle before the first check
    setInterval(checkInvites, POLL_MS);
})();
</script>
@endauth
