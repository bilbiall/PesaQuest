{{-- ── Game Calendar HUD ──────────────────────────────────────────────────
     Ambient date chip → week strip popover → fortnight modal, both anchored
     to the chip's actual position at open-time (not hardcoded coordinates),
     so this works whether the chip floats or sits inline in a nav bar.
     Self-contained (own CSS/JS, no Alpine dependency). Events are fetched
     lazily from /game/calendar the first time the chip is opened, so pages
     pay ZERO extra queries until the player actually looks at the calendar.

     Two placement modes:
     - Default (floating): a small ambient pill fixed top-left of the page.
       Include once per page: @include('partials.game-calendar')
     - Inline: pass ['inline' => true] to render the chip as a normal flex
       item inside a nav bar instead (e.g. life-topnav) — avoids the chip
       floating on top of page content that starts right below the nav.
       @include('partials.game-calendar', ['inline' => true]) --}}
@php
    $gcUser = auth()->user();
    if (!$gcUser) return;
    $gcInline = $inline ?? false;
    $gcToday = app(\App\Services\GameCalendarService::class)->today($gcUser);
    // One cheap query for the attention dot: anything due within 3 game days?
    $gcUrgent = false;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('player_bills')) {
            $gcNext = \App\Models\PlayerBill::where('user_id', $gcUser->id)->where('status', 'active')->min('next_due_tick');
            $gcUrgent = $gcNext !== null && ($gcNext - $gcToday['tick']) <= 3;
        }
    } catch (\Throwable $e) { /* pre-migration — no dot */ }
    // Payday-at-a-glance: uncollected pay, or days until the next payslip lands
    $gcPayReady = false; $gcPayday = null;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('player_city_jobs') && \Illuminate\Support\Facades\Schema::hasColumn('player_city_jobs', 'unpaid_ticks')) {
            $gcJobs = \App\Models\PlayerCityJob::where('user_id', $gcUser->id)
                ->where(fn ($q) => $q->where('status', 'employed')->orWhere('pending_salary', '>', 0))
                ->get(['status', 'employment_type', 'unpaid_ticks', 'pending_salary']);
            $gcPayReady = $gcJobs->sum('pending_salary') > 0;
            $gcPayday   = $gcJobs->where('status', 'employed')
                ->whereIn('employment_type', ['full_time', 'part_time'])
                ->map(fn ($j) => 30 - (((int) $j->unpaid_ticks) % 30))
                ->min();
        }
    } catch (\Throwable $e) { /* pre-migration — no chip */ }
@endphp

<style>
    #gc-chip { position:fixed; left:14px; top:66px; z-index:9980; display:flex; align-items:center; gap:8px;
        padding:8px 14px; border-radius:999px; cursor:pointer; user-select:none;
        background:rgba(10,9,20,.72); border:1px solid rgba(255,255,255,.12); backdrop-filter:blur(12px);
        color:#e5e7eb; font-size:12.5px; font-weight:800; letter-spacing:.01em;
        opacity:.55; transition:opacity .25s, transform .15s; }
    #gc-chip:hover { opacity:1; transform:translateY(1px); }
    /* Inline mode: a normal flex item inside the caller's own nav bar, not a
       floating overlay — so it can never sit on top of in-flow page content. */
    #gc-chip.gc-inline { position:static; left:auto; top:auto; opacity:1; padding:.42rem .75rem; gap:6px; flex-shrink:0; }
    #gc-chip.gc-inline .gc-bar { display:none; }
    /* Narrow phones: collapse to an icon-only tap target (logo + hamburger already
       fill most of the row) — full info is one tap away in the popover. */
    @media (max-width:640px) {
        #gc-chip.gc-inline { padding:.5rem; }
        #gc-chip.gc-inline .gc-daytext, #gc-chip.gc-inline .gc-paytext { display:none; }
    }
    #gc-chip .gc-dot { width:7px; height:7px; border-radius:50%; background:#f59e0b; box-shadow:0 0 8px rgba(245,158,11,.8); animation:gcPulse 1.6s infinite; }
    #gc-chip .gc-bar { width:46px; height:3px; border-radius:2px; background:rgba(255,255,255,.12); overflow:hidden; }
    #gc-chip .gc-bar i { display:block; height:100%; border-radius:2px; background:linear-gradient(90deg,#15C77E,#4DA8F7); }
    @keyframes gcPulse { 50% { opacity:.35; } }

    #gc-strip { position:fixed; left:14px; top:112px; z-index:9981; width:min(480px, calc(100vw - 28px));
        background:#100e1e; border:1px solid rgba(99,102,241,.3); border-radius:18px; padding:14px;
        box-shadow:0 22px 60px rgba(0,0,0,.6); display:none; font-family:inherit; }
    #gc-strip.gc-open { display:block; animation:gcIn .18s ease; }
    @keyframes gcIn { from { opacity:0; transform:translateY(-8px); } }
    .gc-days { display:grid; grid-template-columns:repeat(7,1fr); gap:6px; }
    .gc-day { border-radius:12px; padding:7px 4px; text-align:center; border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.03); position:relative; cursor:default; }
    .gc-day.gc-today { border-color:rgba(21,199,126,.55); background:rgba(21,199,126,.1); }
    .gc-day .gc-wd { font-size:8.5px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; }
    .gc-day .gc-num { font-size:14px; font-weight:900; color:#fff; line-height:1.25; }
    .gc-day .gc-ev { display:flex; justify-content:center; gap:2px; min-height:8px; margin-top:2px; }
    .gc-day .gc-ev b { width:6px; height:6px; border-radius:50%; display:block; }
    .gc-tip { display:none; position:absolute; bottom:calc(100% + 6px); left:50%; transform:translateX(-50%); z-index:5;
        min-width:170px; max-width:230px; background:#1a1830; border:1px solid rgba(255,255,255,.14); border-radius:10px;
        padding:8px 10px; text-align:left; font-size:11px; color:#d1d5db; box-shadow:0 10px 26px rgba(0,0,0,.55); }
    .gc-day:hover .gc-tip { display:block; }
    .gc-ev-line { display:flex; justify-content:space-between; gap:8px; padding:1.5px 0; white-space:nowrap; }
    .gc-ev-line .neg { color:#fca5a5; font-weight:800; } .gc-ev-line .pos { color:#6ee7b7; font-weight:800; }

    /* Fortnight view: anchored panel below the chip — the dark surface is only
       as big as the calendar itself, never a full-screen scrim. */
    #gc-modal { position:fixed; left:14px; top:112px; z-index:9990; display:none;
        width:min(560px, calc(100vw - 28px)); }
    #gc-modal.gc-open { display:block; animation:gcIn .18s ease; }
    #gc-modal .gc-sheet { background:#100e1e; border:1px solid rgba(99,102,241,.3); border-radius:22px; padding:22px;
        max-height:min(560px, calc(100vh - 130px)); overflow-y:auto; box-shadow:0 26px 70px rgba(0,0,0,.65); backdrop-filter:blur(6px); }
    .gc-row { display:flex; align-items:center; gap:10px; padding:9px 10px; border-radius:12px; margin-bottom:4px; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); font-size:13px; color:#d1d5db; }
    .gc-row.gc-today-row { border-color:rgba(21,199,126,.4); background:rgba(21,199,126,.07); }
    .gc-row .gc-when { width:86px; flex-shrink:0; font-weight:900; color:#fff; font-size:12px; }
    .gc-row .gc-when small { display:block; font-weight:700; color:#6b7280; font-size:9.5px; text-transform:uppercase; letter-spacing:.05em; }
    @media (max-width:640px){ #gc-chip { top:60px; } #gc-strip { top:106px; } #gc-modal { top:106px; } }
</style>

<div id="gc-chip" class="{{ $gcInline ? 'gc-inline' : '' }}" title="Pesa City calendar" onclick="gcToggle()">
    <span>📅</span>
    <span class="gc-daytext">Day {{ number_format($gcToday['day']) }} · {{ substr($gcToday['weekday'], 0, 3) }} · Yr {{ $gcToday['year'] }}</span>
    <span class="gc-bar" title="Month progress"><i style="width:{{ $gcToday['month_progress'] }}%"></i></span>
    @if($gcPayReady)
    <span class="gc-paytext" style="color:#34d399;font-weight:900;" title="You have uncollected pay — Report to Work on the Career page">💰 Pay ready!</span>
    @elseif($gcPayday !== null)
    <span class="gc-paytext" style="color:#a7f3d0;" title="Game days until your next payslip lands">💰 Payday in {{ $gcPayday }}d</span>
    @endif
    @if($gcUrgent)<span class="gc-dot" title="Something is due within 3 game days"></span>@endif
</div>

<div id="gc-strip">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <div style="font-size:12px;font-weight:900;color:#fff;">📅 This Week in Pesa City <span style="color:#6b7280;font-weight:700;">· Month {{ $gcToday['month'] }}, Year {{ $gcToday['year'] }}</span></div>
        <button onclick="gcOpenModal()" style="font-size:11px;font-weight:800;color:#a5b4fc;background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.3);border-radius:8px;padding:4px 10px;cursor:pointer;">Next 2 weeks →</button>
    </div>
    <div class="gc-days" id="gc-week"><div style="grid-column:1/-1;text-align:center;color:#6b7280;font-size:12px;padding:12px 0;">Loading…</div></div>
    <p style="font-size:10px;color:#4b5563;margin-top:8px;">🟥 money out · 🟩 money in · 🟪 chama · 🟨 crisis warning · Hover a day for details. One game day ≈ {{ app(\App\Services\GameClock::class)->rateDescription() }}.</p>
</div>

<div id="gc-modal">
    <div class="gc-sheet">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <h3 style="font-size:16px;font-weight:900;color:#fff;">🗓️ The Next Two Weeks</h3>
            <button onclick="gcCloseModal()" style="color:#9ca3af;background:none;border:none;font-size:20px;cursor:pointer;">✕</button>
        </div>
        <div id="gc-fortnight"><p style="color:#6b7280;font-size:13px;">Loading…</p></div>
    </div>
</div>

<script>
(function () {
    let gcData = null, gcLoading = false;

    // Anchor a popover to the chip's ACTUAL rendered position rather than the
    // hardcoded CSS coordinates — the chip floats on some pages and sits
    // inline in a nav bar on others, so its real position varies.
    function gcAnchor(el) {
        const chip = document.getElementById('gc-chip');
        const r = chip.getBoundingClientRect();
        el.style.top = Math.round(r.bottom + 8) + 'px';
        requestAnimationFrame(() => {
            const w = el.offsetWidth || 300;
            let left = Math.min(r.left, window.innerWidth - w - 8);
            left = Math.max(8, left);
            el.style.left = Math.round(left) + 'px';
        });
    }

    window.gcToggle = function () {
        const strip = document.getElementById('gc-strip');
        const opening = !strip.classList.contains('gc-open');
        strip.classList.toggle('gc-open', opening);
        if (opening) { gcAnchor(strip); gcLoad(); }
    };
    window.gcOpenModal  = function () {
        document.getElementById('gc-strip').classList.remove('gc-open'); // panel replaces the week strip in place
        const modal = document.getElementById('gc-modal');
        modal.classList.add('gc-open');
        gcAnchor(modal);
        gcLoad();
    };
    window.gcCloseModal = function () { document.getElementById('gc-modal').classList.remove('gc-open'); };
    document.addEventListener('click', e => {
        const strip = document.getElementById('gc-strip');
        const modal = document.getElementById('gc-modal');
        const chip  = document.getElementById('gc-chip');
        const inside = strip.contains(e.target) || modal.contains(e.target) || chip.contains(e.target);
        if (!inside) {
            strip.classList.remove('gc-open');
            modal.classList.remove('gc-open');
        }
    });

    function dotColor(ev) {
        if (ev.kind === 'crisis') return '#fbbf24';
        if (ev.kind === 'birthday') return '#f472b6';
        if (ev.kind === 'chama') return '#a78bfa';
        return (ev.amount ?? 0) < 0 ? '#f87171' : '#34d399';
    }
    function fmtAmt(a) {
        if (a === null || a === undefined) return '';
        const s = a < 0 ? '−' : '+';
        return `<span class="${a < 0 ? 'neg' : 'pos'}">${s}${Math.abs(a).toLocaleString()}</span>`;
    }

    function gcLoad() {
        if (gcData) { render(); return; }
        if (gcLoading) return;
        gcLoading = true;
        fetch('{{ route('game.calendar') }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => { gcData = d; render(); })
            .catch(() => { document.getElementById('gc-week').innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#6b7280;font-size:12px;">Could not load calendar.</div>'; })
            .finally(() => gcLoading = false);
    }

    function render() {
        // Week strip: first 7 days
        const week = gcData.days.slice(0, 7).map(d => {
            const dots = d.events.slice(0, 4).map(ev => `<b style="background:${dotColor(ev)}"></b>`).join('');
            const tip  = d.events.length
                ? `<div class="gc-tip"><div style="font-weight:900;color:#fff;margin-bottom:3px;">Day ${d.day.toLocaleString()} · ${d.weekday}</div>` +
                  d.events.map(ev => `<div class="gc-ev-line"><span>${ev.icon} ${ev.label}</span>${fmtAmt(ev.amount)}</div>`).join('') + `</div>`
                : '';
            return `<div class="gc-day ${d.is_today ? 'gc-today' : ''}">
                        <div class="gc-wd">${d.weekday.slice(0, 3)}</div>
                        <div class="gc-num">${d.is_today ? '★' : d.day.toLocaleString()}</div>
                        <div class="gc-ev">${dots}</div>${tip}
                    </div>`;
        }).join('');
        document.getElementById('gc-week').innerHTML = week;

        // Fortnight list: only days that have events (plus today)
        const rows = gcData.days.filter(d => d.is_today || d.events.length).map(d => {
            const evs = d.events.length
                ? d.events.map(ev => `<div class="gc-ev-line" style="white-space:normal;"><span>${ev.icon} ${ev.label}</span>${fmtAmt(ev.amount)}</div>`).join('')
                : '<span style="color:#4b5563;">Nothing scheduled — a good day to save 😉</span>';
            return `<div class="gc-row ${d.is_today ? 'gc-today-row' : ''}">
                        <div class="gc-when">${d.is_today ? 'TODAY' : 'Day ' + d.day.toLocaleString()}<small>${d.weekday}</small></div>
                        <div style="flex:1;min-width:0;">${evs}</div>
                    </div>`;
        }).join('');
        document.getElementById('gc-fortnight').innerHTML = rows ||
            '<p style="color:#6b7280;font-size:13px;">Nothing scheduled in the next two weeks.</p>';
    }
})();
</script>
