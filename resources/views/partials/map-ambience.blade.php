{{-- ── Pesa City Map Ambience ─────────────────────────────────────────────
     Living-world layer: bird flocks, drifting clouds, real-time day/night
     tint (stars + fireflies at night), crisis storm weather, rare fly-overs
     (balloon / kite / plane with admin banner), district activity puffs,
     strolling NPCs with speech bubbles, and event bursts (birthday balloons,
     savings-interest coin fountain over the Bank).

     Include ONCE inside #pc-map (position:relative). Everything is
     pointer-events:none except NPC walkers. No assets — pure CSS/SVG,
     matching the procedural-audio philosophy. Honors prefers-reduced-motion,
     pauses on hidden tabs, and caps concurrent actors.
     Admin: GameSet Hub → Game Settings (map_ambience: off|calm|lively,
     ambience_banner text). --}}
@php
    $ambMode = 'lively';
    $ambBanner = '';
    $ambStorm = false;
    $ambBirthday = false;
    $ambInterest = false;
    $ambNpcs = [];
    try {
        $ambMode   = \App\Models\Setting::get('map_ambience', 'lively') ?: 'lively';
        $ambBanner = trim((string) \App\Models\Setting::get('ambience_banner', ''));

        // Storm clouds gather when a crisis has been announced (or is live)
        if (\Illuminate\Support\Facades\Schema::hasTable('financial_crises')) {
            $ambStorm = \App\Models\FinancialCrisis::where('is_processed', false)
                ->where('active_until', '>=', now())
                ->where(fn ($q) => $q->whereNotNull('warning_sent_at')->orWhere('active_from', '<=', now()))
                ->exists();
        }

        $ambUser = auth()->user();
        if ($ambUser?->date_of_birth) {
            $ambBirthday = \Illuminate\Support\Carbon::parse($ambUser->date_of_birth)->format('m-d') === now()->format('m-d');
        }
        if ($ambUser && \Illuminate\Support\Facades\Schema::hasTable('game_notifications')) {
            $ambInterest = \App\Models\GameNotification::where('user_id', $ambUser->id)
                ->where('type', 'savings_interest')
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();
        }

        // Strolling cast: emoji + one adult-band greeting each, name pre-filled
        $ambFirst = strtok($ambUser?->name ?? 'rafiki', ' ');
        foreach (config('pesa_voice.npcs', []) as $npcKey => $npc) {
            $greetings = $npc['greetings']['adult'] ?? reset($npc['greetings']);
            $ambNpcs[] = [
                'key'   => $npcKey,
                'emoji' => $npc['emoji'] ?? '🙂',
                'name'  => $npc['name'] ?? 'Friend',
                'line'  => \App\Services\PesaVoice::fill($greetings[array_rand($greetings)] ?? 'Habari!', ['name' => $ambFirst]),
            ];
        }
    } catch (\Throwable $e) { /* ambience must never break the map */ }
@endphp
@if($ambMode !== 'off')
<style>
    #pc-amb { position:absolute; inset:0; overflow:hidden; pointer-events:none; z-index:34; }
    #pc-amb.amb-paused *, #pc-amb.amb-paused { animation-play-state:paused !important; }

    /* ── Day/night tint ── */
    .amb-tint { position:absolute; inset:0; transition:background 12s linear; }
    .amb-dawn  .amb-tint { background:linear-gradient(180deg, rgba(255,170,80,.10), rgba(255,120,60,.04) 55%, transparent); }
    .amb-dusk  .amb-tint { background:linear-gradient(180deg, rgba(140,80,200,.14), rgba(255,110,70,.08) 60%, transparent); }
    .amb-night .amb-tint { background:radial-gradient(ellipse 90% 70% at 50% 20%, rgba(20,28,70,.20), rgba(8,12,40,.34)); }
    .amb-storm .amb-tint { box-shadow:inset 0 0 140px 40px rgba(45,52,70,.35); }

    /* ── Stars (night only) ── */
    .amb-stars { position:absolute; inset:0 0 55% 0; opacity:0; transition:opacity 8s; }
    .amb-night .amb-stars { opacity:1; }
    .amb-star { position:absolute; width:2px; height:2px; border-radius:50%; background:#fff;
        animation:ambTwinkle 3.2s ease-in-out infinite; }
    @keyframes ambTwinkle { 0%,100% { opacity:.15; } 50% { opacity:.85; } }

    /* ── Clouds ── */
    .amb-cloud { position:absolute; width:130px; height:34px; border-radius:999px; filter:blur(1px);
        background:rgba(255,255,255,.13); animation:ambDrift linear infinite; }
    .amb-cloud::before, .amb-cloud::after { content:''; position:absolute; border-radius:50%; background:inherit; }
    .amb-cloud::before { width:56px; height:56px; left:20px; top:-24px; }
    .amb-cloud::after  { width:40px; height:40px; right:24px; top:-14px; }
    .amb-night .amb-cloud { background:rgba(190,200,255,.07); }
    .amb-cloud.amb-dark { background:rgba(50,58,78,.42); filter:blur(1.5px); }
    .amb-night .amb-cloud.amb-dark { background:rgba(30,34,52,.55); }
    @keyframes ambDrift { from { transform:translateX(-160px); } to { transform:translateX(calc(100vw + 160px)); } }

    /* ── Drizzle (crisis weather bursts) ── */
    .amb-rain { position:absolute; inset:0; display:none; }
    .amb-raining .amb-rain { display:block; }
    .amb-drop { position:absolute; top:-8%; width:1px; height:11px; border-radius:1px;
        background:linear-gradient(rgba(160,190,230,0), rgba(160,190,230,.5));
        animation:ambFall linear infinite; }
    @keyframes ambFall { to { transform:translateY(115vh); } }

    /* ── Birds ── */
    .amb-flock { position:absolute; display:flex; gap:7px; transition-property:left; transition-timing-function:linear; will-change:left; }
    .amb-flock.amb-far { opacity:.45; transform:scale(.6); }
    .amb-bird svg { display:block; animation:ambFlap .5s ease-in-out infinite; }
    .amb-flock.amb-far .amb-bird svg { animation-duration:.7s; }
    .amb-bird:nth-child(2n) svg { animation-delay:.15s; }
    .amb-bird:nth-child(3n) svg { animation-delay:.28s; }
    .amb-flip { transform:scaleX(-1); }
    .amb-flock.amb-far.amb-flip { transform:scale(.6) scaleX(-1); }
    @keyframes ambFlap { 0%,100% { transform:scaleY(1); } 50% { transform:scaleY(.35); } }

    /* ── Fireflies (night only) ── */
    .amb-fly { position:absolute; width:4px; height:4px; border-radius:50%; background:#d9f99d; opacity:0;
        box-shadow:0 0 8px 2px rgba(217,249,157,.7); }
    .amb-night .amb-fly { animation:ambFirefly 9s ease-in-out infinite; }
    @keyframes ambFirefly {
        0%, 100% { opacity:0; transform:translate(0,0); }
        15%      { opacity:.9; }
        40%      { opacity:.2; transform:translate(26px,-18px); }
        60%      { opacity:.85; transform:translate(-14px,-30px); }
        85%      { opacity:.15; transform:translate(10px,-6px); }
    }

    /* ── Fly-overs ── */
    .amb-flyover { position:absolute; transition-property:left, top; transition-timing-function:linear; will-change:left, top; }
    .amb-balloon { width:30px; }
    .amb-balloon .amb-envelope { width:30px; height:34px; border-radius:50% 50% 42% 42%;
        background:radial-gradient(circle at 35% 30%, #fbbf24, #f97316 55%, #dc2626); animation:ambSway 5s ease-in-out infinite; }
    .amb-balloon .amb-basket { width:10px; height:8px; margin:3px auto 0; background:#92400e; border-radius:2px; position:relative; }
    .amb-balloon .amb-basket::before, .amb-balloon .amb-basket::after { content:''; position:absolute; top:-4px; width:1px; height:4px; background:#a16207; }
    .amb-balloon .amb-basket::before { left:1px; } .amb-balloon .amb-basket::after { right:1px; }
    .amb-kite { font-size:20px; animation:ambSway 3.5s ease-in-out infinite; }
    .amb-plane { display:flex; align-items:center; gap:2px; }
    .amb-plane .amb-jet { font-size:19px; }
    .amb-banner-text { font-size:10px; font-weight:800; color:#fff; white-space:nowrap; padding:2px 9px;
        background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.35); border-radius:3px;
        letter-spacing:.03em; backdrop-filter:blur(2px); }
    @keyframes ambSway { 0%,100% { transform:translateY(0) rotate(-2deg); } 50% { transform:translateY(-7px) rotate(3deg); } }

    /* ── District activity puffs ── */
    .amb-puff { position:absolute; }
    .amb-smoke { position:absolute; bottom:0; left:0; width:9px; height:9px; border-radius:50%;
        background:rgba(200,205,220,.4); animation:ambSmoke 4s ease-out infinite; }
    .amb-smoke:nth-child(2) { animation-delay:1.3s; left:3px; }
    .amb-smoke:nth-child(3) { animation-delay:2.6s; left:-3px; }
    @keyframes ambSmoke { from { opacity:.5; transform:translateY(0) scale(.6); } to { opacity:0; transform:translateY(-30px) scale(1.6); } }
    .amb-spark { position:absolute; font-size:9px; color:#fde68a; animation:ambSpark 2.8s ease-in-out infinite; }
    .amb-spark:nth-child(2) { animation-delay:.9s; left:10px; top:5px; }
    .amb-spark:nth-child(3) { animation-delay:1.8s; left:-8px; top:8px; }
    @keyframes ambSpark { 0%,100% { opacity:0; transform:scale(.5); } 50% { opacity:.95; transform:scale(1.15); } }
    .amb-note { position:absolute; font-size:11px; color:rgba(255,160,220,.9); animation:ambNote 3.6s ease-out infinite; }
    .amb-note:nth-child(2) { animation-delay:1.2s; left:8px; }
    .amb-note:nth-child(3) { animation-delay:2.4s; left:-6px; }
    @keyframes ambNote { from { opacity:0; transform:translateY(0) rotate(-8deg); } 25% { opacity:.9; } to { opacity:0; transform:translateY(-26px) rotate(10deg); } }

    /* ── NPC strollers ── */
    .amb-npc { position:absolute; pointer-events:auto; cursor:pointer; z-index:36;
        transition-property:left, top; transition-timing-function:ease-in-out; will-change:left, top;
        font-size:19px; filter:drop-shadow(0 3px 4px rgba(0,0,0,.5)); }
    .amb-npc .amb-npc-inner { animation:ambBob .7s ease-in-out infinite; }
    @keyframes ambBob { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-3px); } }
    .amb-bubble { position:absolute; bottom:calc(100% + 8px); left:50%; transform:translateX(-50%);
        min-width:150px; max-width:210px; padding:7px 10px; border-radius:11px; pointer-events:none;
        background:rgba(16,14,30,.95); border:1px solid rgba(167,139,250,.45); box-shadow:0 10px 26px rgba(0,0,0,.5);
        font-size:10.5px; font-weight:700; color:#e5e7eb; line-height:1.45; text-align:left; white-space:normal;
        animation:ambBubbleIn .2s ease; }
    .amb-bubble b { color:#c4b5fd; display:block; font-size:9px; text-transform:uppercase; letter-spacing:.05em; margin-bottom:1px; }
    @keyframes ambBubbleIn { from { opacity:0; transform:translateX(-50%) translateY(5px); } }

    /* ── Event bursts ── */
    .amb-rise { position:absolute; bottom:-8%; font-size:19px; animation:ambRise 11s ease-in forwards; }
    @keyframes ambRise { 0% { opacity:0; transform:translateY(0) rotate(-4deg); } 8% { opacity:1; }
        60% { transform:translateY(-55vh) rotate(5deg); } 100% { opacity:0; transform:translateY(-95vh) rotate(-6deg); } }
    .amb-coin { position:absolute; font-size:13px; animation:ambCoin 1.6s cubic-bezier(.2,.6,.4,1) forwards; }
    @keyframes ambCoin { 0% { opacity:0; transform:translate(0,0) scale(.5); } 15% { opacity:1; }
        55% { transform:translate(var(--cx), -46px) scale(1); } 100% { opacity:0; transform:translate(calc(var(--cx) * 1.6), 14px) scale(.85); } }

    @media (prefers-reduced-motion: reduce) { #pc-amb { display:none; } }
</style>

<div id="pc-amb"
     class="{{ $ambStorm ? 'amb-storm' : '' }}"
     data-mode="{{ $ambMode }}"
     data-storm="{{ $ambStorm ? 1 : 0 }}"
     data-birthday="{{ $ambBirthday ? 1 : 0 }}"
     data-interest="{{ $ambInterest ? 1 : 0 }}"
     data-banner="{{ $ambBanner }}">
    <div class="amb-tint"></div>
    <div class="amb-stars"></div>
    <div class="amb-clouds"></div>
    <div class="amb-rain"></div>
    <div class="amb-sky"></div>

    {{-- District activity puffs (anchored to the district map coordinates) --}}
    <div class="amb-puff" style="left:43%; top:12.5%;"><i class="amb-smoke"></i><i class="amb-smoke"></i><i class="amb-smoke"></i></div>
    <div class="amb-puff" style="left:73.5%; top:42%;"><span class="amb-spark">✦</span><span class="amb-spark">✦</span><span class="amb-spark">✦</span></div>
    <div class="amb-puff" style="left:91%; top:22%;"><span class="amb-note">♪</span><span class="amb-note">♫</span><span class="amb-note">♪</span></div>

    <div class="amb-ground"></div>
</div>

<script>
(function () {
    const layer = document.getElementById('pc-amb');
    if (!layer || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const CALM   = layer.dataset.mode === 'calm';
    const NPCS   = {!! json_encode($ambNpcs, JSON_HEX_TAG | JSON_HEX_APOS) !!};
    const BANNER = layer.dataset.banner || '';
    const STORM  = layer.dataset.storm === '1';

    const CFG = CALM
        ? { birdMin: 100, birdMax: 220, flyMin: 900, flyMax: 1600, npcMin: 360, npcMax: 640, maxActors: 2 }
        : { birdMin: 45,  birdMax: 120, flyMin: 480, flyMax: 900,  npcMin: 180, npcMax: 380, maxActors: 3 };

    const sky    = layer.querySelector('.amb-sky');
    const ground = layer.querySelector('.amb-ground');
    let actors = 0;
    const timers = [];
    const later = (fn, ms) => { const t = setTimeout(fn, ms); timers.push(t); return t; };
    const rnd   = (a, b) => a + Math.random() * (b - a);
    const pick  = arr => arr[Math.floor(Math.random() * arr.length)];

    /* ── Day/night phase from local time ─────────────────────────── */
    function applyPhase() {
        const h = new Date().getHours();
        layer.classList.remove('amb-night', 'amb-dusk', 'amb-dawn');
        if (h >= 19 || h < 6)      layer.classList.add('amb-night');
        else if (h >= 17)          layer.classList.add('amb-dusk');
        else if (h < 8)            layer.classList.add('amb-dawn');
    }
    applyPhase();
    setInterval(applyPhase, 300000);
    const isNight = () => layer.classList.contains('amb-night');

    /* ── Static scenery: stars, fireflies, clouds, rain drops ────── */
    const stars = layer.querySelector('.amb-stars');
    for (let i = 0; i < 26; i++) {
        const s = document.createElement('i');
        s.className = 'amb-star';
        s.style.cssText = `left:${rnd(1, 99)}%;top:${rnd(2, 95)}%;animation-delay:${rnd(0, 3)}s;`;
        stars.appendChild(s);
    }
    for (let i = 0; i < 5; i++) {
        const f = document.createElement('i');
        f.className = 'amb-fly';
        f.style.cssText = `left:${rnd(10, 90)}%;top:${rnd(55, 88)}%;animation-delay:${rnd(0, 8)}s;`;
        ground.appendChild(f);
    }
    const clouds = layer.querySelector('.amb-clouds');
    const cloudSpecs = [[8, 300, .8], [22, 420, 1.15], [15, 520, .6]];
    if (STORM) cloudSpecs.push([12, 260, 1.35, true], [26, 340, 1.05, true]);
    cloudSpecs.forEach(([top, dur, scale, dark], i) => {
        const c = document.createElement('div');
        c.className = 'amb-cloud' + (dark ? ' amb-dark' : '');
        c.style.cssText = `top:${top}%;animation-duration:${dur}s;animation-delay:-${i * 90}s;scale:${scale};`;
        clouds.appendChild(c);
    });
    const rain = layer.querySelector('.amb-rain');
    for (let i = 0; i < 24; i++) {
        const d = document.createElement('i');
        d.className = 'amb-drop';
        d.style.cssText = `left:${rnd(0, 100)}%;animation-duration:${rnd(0.9, 1.5)}s;animation-delay:${rnd(0, 1.5)}s;`;
        rain.appendChild(d);
    }
    if (STORM) {
        (function drizzleCycle() {
            later(() => {
                layer.classList.add('amb-raining');
                later(() => { layer.classList.remove('amb-raining'); drizzleCycle(); }, 25000);
            }, rnd(60, 180) * 1000);
        })();
    }

    /* ── Birds (day) ──────────────────────────────────────────────── */
    const BIRD_SVG = '<svg width="22" height="11" viewBox="0 0 24 12" fill="none">' +
        '<path d="M2 9 Q7 2 12 8 Q17 2 22 9" stroke="rgba(15,20,35,0.75)" stroke-width="2.2" stroke-linecap="round"/></svg>';

    function spawnFlock() {
        if (!document.hidden && !isNight() && actors < CFG.maxActors) {
            actors++;
            const flock = document.createElement('div');
            const far   = Math.random() < 0.4;
            const l2r   = Math.random() < 0.5;
            flock.className = 'amb-flock' + (far ? ' amb-far' : '') + (l2r ? '' : ' amb-flip');
            const count = 1 + Math.floor(Math.random() * 6);
            for (let i = 0; i < count; i++) {
                const b = document.createElement('span');
                b.className = 'amb-bird';
                b.style.marginTop = `${Math.abs(i - count / 2) * 5}px`;
                b.innerHTML = BIRD_SVG;
                flock.appendChild(b);
            }
            const dur = rnd(far ? 26 : 18, far ? 40 : 30);
            flock.style.top  = `${rnd(4, 42)}%`;
            flock.style.left = l2r ? '-14%' : '110%';
            flock.style.transitionDuration = `${dur}s`;
            sky.appendChild(flock);
            requestAnimationFrame(() => requestAnimationFrame(() => { flock.style.left = l2r ? '110%' : '-14%'; }));
            later(() => { flock.remove(); actors--; }, dur * 1000 + 500);
            if (Math.random() < 0.3) { try { window.PesaSound?.play('chirp'); } catch (e) {} }
        }
        later(spawnFlock, rnd(CFG.birdMin, CFG.birdMax) * 1000);
    }
    later(spawnFlock, rnd(4, 15) * 1000);

    /* ── Rare fly-overs ───────────────────────────────────────────── */
    function spawnFlyover() {
        if (!document.hidden && actors < CFG.maxActors + 1) {
            actors++;
            const kinds = ['balloon', 'kite'];
            if (BANNER) kinds.push('plane', 'plane'); // banner plane twice as likely when text is set
            const kind = pick(kinds);
            const el = document.createElement('div');
            el.className = 'amb-flyover';
            let dur = 60, fromTop = 70, toTop = 6;
            if (kind === 'balloon') {
                el.innerHTML = '<div class="amb-balloon"><div class="amb-envelope"></div><div class="amb-basket"></div></div>';
                dur = rnd(55, 80);
            } else if (kind === 'kite') {
                el.innerHTML = '<span class="amb-kite">🪁</span>';
                dur = rnd(35, 50); fromTop = 30; toTop = 12;
            } else {
                el.innerHTML = '<div class="amb-plane"><span class="amb-jet">✈️</span><span class="amb-banner-text"></span></div>';
                el.querySelector('.amb-banner-text').textContent = BANNER;
                dur = rnd(24, 32); fromTop = rnd(6, 16); toTop = fromTop;
            }
            el.style.left = '-18%';
            el.style.top  = `${fromTop}%`;
            el.style.transitionDuration = `${dur}s`;
            sky.appendChild(el);
            requestAnimationFrame(() => requestAnimationFrame(() => { el.style.left = '112%'; el.style.top = `${toTop}%`; }));
            later(() => { el.remove(); actors--; }, dur * 1000 + 500);
        }
        later(spawnFlyover, rnd(CFG.flyMin, CFG.flyMax) * 1000);
    }
    later(spawnFlyover, rnd(90, 240) * 1000);

    /* ── NPC strollers ────────────────────────────────────────────── */
    const WAYPOINTS = [[48, 47], [66, 63], [67, 23], [19, 22], [74, 46], [88, 79], [38, 72], [43, 21]];
    function spawnNpc() {
        if (!document.hidden && NPCS.length && !layer.querySelector('.amb-npc')) {
            const npc  = pick(NPCS);
            const from = pick(WAYPOINTS);
            let to     = pick(WAYPOINTS);
            while (to === from) to = pick(WAYPOINTS);
            const dur  = rnd(24, 40);

            const el = document.createElement('div');
            el.className = 'amb-npc';
            el.title = npc.name;
            el.innerHTML = '<div class="amb-npc-inner"></div>';
            el.querySelector('.amb-npc-inner').textContent = npc.emoji;
            el.style.left = `${from[0]}%`;
            el.style.top  = `${from[1]}%`;
            el.style.transitionDuration = `${dur}s`;
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                if (el.querySelector('.amb-bubble')) return;
                const bubble = document.createElement('div');
                bubble.className = 'amb-bubble';
                const who = document.createElement('b');
                who.textContent = `${npc.emoji} ${npc.name}`;
                bubble.appendChild(who);
                bubble.appendChild(document.createTextNode(npc.line));
                el.appendChild(bubble);
                setTimeout(() => bubble.remove(), 5000);
            });
            layer.appendChild(el);
            requestAnimationFrame(() => requestAnimationFrame(() => { el.style.left = `${to[0]}%`; el.style.top = `${to[1]}%`; }));
            later(() => { el.style.opacity = '0'; el.style.transition = 'opacity 1.5s'; later(() => el.remove(), 1600); }, dur * 1000);
        }
        later(spawnNpc, rnd(CFG.npcMin, CFG.npcMax) * 1000);
    }
    later(spawnNpc, rnd(20, 60) * 1000);

    /* ── Event bursts ─────────────────────────────────────────────── */
    if (layer.dataset.birthday === '1') {
        later(() => {
            for (let i = 0; i < 7; i++) {
                later(() => {
                    const b = document.createElement('span');
                    b.className = 'amb-rise';
                    b.textContent = '🎈';
                    b.style.left = `${rnd(8, 90)}%`;
                    ground.appendChild(b);
                    later(() => b.remove(), 11500);
                }, i * 900);
            }
        }, 6000);
    }
    if (layer.dataset.interest === '1') {
        later(() => {
            for (let i = 0; i < 10; i++) {
                later(() => {
                    const c = document.createElement('span');
                    c.className = 'amb-coin';
                    c.textContent = '🪙';
                    c.style.cssText = `left:73.5%;top:45%;--cx:${rnd(-40, 40)}px;`;
                    ground.appendChild(c);
                    later(() => c.remove(), 1700);
                }, i * 140);
            }
        }, 5000);
    }

    /* ── Tab-hidden pause ─────────────────────────────────────────── */
    document.addEventListener('visibilitychange', () => {
        layer.classList.toggle('amb-paused', document.hidden);
    });
})();
</script>
@endif
