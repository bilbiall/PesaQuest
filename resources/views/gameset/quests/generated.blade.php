<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Generated Quests — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        .gq-input { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:.7rem;
                    color:#fff; font-size:12.5px; font-weight:700; padding:.5rem .8rem; }
        .gq-input:focus { outline:none; border-color:rgba(124,58,237,.6); }
        .gq-hidden { display:none !important; }
        .gq-badge { font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; padding:2.5px 8px; border-radius:7px; }
        .gq-src-factory   { background:rgba(245,158,11,.14); color:#fcd34d; border:1px solid rgba(245,158,11,.3); }
        .gq-src-blueprint { background:rgba(99,102,241,.14); color:#a5b4fc; border:1px solid rgba(99,102,241,.3); }
        .gq-src-mixer     { background:rgba(124,58,237,.16); color:#c4b5fd; border:1px solid rgba(124,58,237,.35); }
        .gq-suggest { position:absolute; top:calc(100% + 6px); left:0; right:0; z-index:50; background:#100e1e;
                      border:1px solid rgba(124,58,237,.4); border-radius:14px; overflow:hidden; box-shadow:0 18px 50px rgba(0,0,0,.6); }
        .gq-sug-item { padding:9px 13px; font-size:12.5px; font-weight:700; color:#e5e7eb; cursor:pointer; display:flex; gap:8px; align-items:center; }
        .gq-sug-item:hover, .gq-sug-item.hot { background:rgba(124,58,237,.15); }
        .gq-sug-item + .gq-sug-item { border-top:1px solid rgba(255,255,255,.05); }
        .gq-flash { animation:gqFlash 1.6s ease; }
        @keyframes gqFlash { 0%,60% { box-shadow:0 0 0 2px rgba(124,58,237,.8); } 100% { box-shadow:none; } }
    </style>
</head>
<body class="text-white min-h-screen">

@include('gameset.partials.topnav', ['active' => 'quests'])

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div>
            <h1 class="text-2xl font-black">🤖 Generated Quests</h1>
            <p class="text-xs text-gray-500 mt-1">Everything the machines wrote — Factory, Blueprint sweeps and the Mixer. Filters apply instantly; publish or discard one by one or in bulk.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('gameset.automation.index') }}" class="text-xs font-bold text-indigo-300 hover:text-white transition-colors">🤖 Automation</a>
            <span class="text-gray-700">·</span>
            <a href="{{ route('gameset.quests.index') }}" class="text-xs font-bold text-indigo-300 hover:text-white transition-colors">← All Quests</a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 my-5">
        @foreach([
            ['🗂', $stats['total'],     'Generated total', ''],
            ['📝', $stats['drafts'],    'Awaiting review', 'draft'],
            ['✅', $stats['published'], 'Published',       'published'],
            ['🏭', $stats['factory'],   'Factory',         'src:factory'],
            ['🧬', $stats['blueprint'], 'Blueprints',      'src:blueprint'],
            ['🎲', $stats['mixer'],     'Mixer',           'src:mixer'],
        ] as [$ic, $val, $lbl, $jump])
        <button type="button" onclick="gqJump('{{ $jump }}')" class="rounded-2xl p-4 text-left transition-transform hover:scale-[1.02]"
                style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);cursor:pointer;">
            <div class="text-lg">{{ $ic }}</div>
            <div class="text-xl font-black text-white">{{ $val }}</div>
            <div class="text-[10px] text-gray-500 font-semibold">{{ $lbl }}</div>
        </button>
        @endforeach
    </div>

    {{-- Instant filters + live search --}}
    <div class="flex flex-wrap gap-2.5 mb-2 items-center">
        <div class="relative flex-1 min-w-[220px]">
            <input type="search" id="gq-search" class="gq-input w-full" placeholder="🔍 Search generated quests — suggestions as you type…" autocomplete="off">
            <div class="gq-suggest gq-hidden" id="gq-suggest"></div>
        </div>
        <select id="gq-source" class="gq-input" onchange="gqFilter()">
            <option value="">All sources</option>
            <option value="factory">🏭 Factory</option>
            <option value="blueprint">🧬 Blueprint</option>
            <option value="mixer">🎲 Mixer</option>
        </select>
        <select id="gq-status" class="gq-input" onchange="gqFilter()">
            <option value="">Any status</option>
            <option value="draft">📝 Drafts</option>
            <option value="published">✅ Published</option>
        </select>
        <select id="gq-level" class="gq-input" onchange="gqFilter()">
            <option value="">All levels</option>
            @for($i = 1; $i <= 20; $i++)<option value="{{ $i }}">Level {{ $i }}</option>@endfor
        </select>
        <select id="gq-age" class="gq-input" onchange="gqFilter()">
            <option value="">All audiences</option>
            <option value="all">🌍 Everyone</option>
            <option value="8-12">🧒 8–12</option>
            <option value="13-17">🎒 13–17</option>
            <option value="18-25">🎓 18–25</option>
            <option value="26+">💼 26+</option>
        </select>
    </div>

    {{-- Bulk bar --}}
    <div class="flex flex-wrap items-center gap-2.5 mb-5 text-xs">
        <span class="text-gray-500 font-bold">Showing <b class="text-white" id="gq-count">{{ $quests->count() }}</b> quest(s)</span>
        <button type="button" id="gq-family-chip" class="gq-hidden px-3 py-1.5 rounded-lg font-black text-pink-300"
                style="background:rgba(236,72,153,0.1);border:1px solid rgba(236,72,153,0.35);cursor:pointer;"
                onclick="gqFamily('')" title="Click to clear the family filter">👯 family: <span id="gq-family-name"></span> ✕</button>
        <span class="flex-1"></span>
        <button type="button" onclick="gqBulk('activate')" class="px-3.5 py-1.5 rounded-lg font-black text-emerald-300"
                style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);">✅ Publish all visible drafts</button>
        <button type="button" onclick="gqBulk('delete')" class="px-3.5 py-1.5 rounded-lg font-black text-red-300/90"
                style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);">🗑 Discard all visible</button>
    </div>

    {{-- Cards --}}
    <div id="gq-list">
        @forelse($quests as $q)
        @php
            $stepLabels = collect($q->triggers ?? [])->pluck('label')->filter()->join('  →  ') ?: ($q->trigger_label ?? '');
            $srcMeta = ['factory' => '🏭 Factory', 'blueprint' => '🧬 Blueprint', 'mixer' => '🎲 Mixer'][$q->source] ?? $q->source;
        @endphp
        <div class="gq-card rounded-2xl mb-3 p-4 sm:p-5"
             id="gq-quest-{{ $q->id }}"
             data-id="{{ $q->id }}"
             data-source="{{ $q->source }}"
             data-status="{{ $q->is_active ? 'published' : 'draft' }}"
             data-level="{{ $q->level_required ?? 1 }}"
             data-sig="{{ $q->combo_sig }}"
             data-age="{{ $q->age_group ?? 'all' }}"
             data-search="{{ Str::lower($q->title . ' ' . $q->description . ' ' . $stepLabels . ' ' . ($q->lesson ?? '')) }}"
             data-title="{{ $q->title }}"
             data-icon="{{ $q->icon }}"
             style="background:linear-gradient(135deg,rgba(15,13,30,0.9),rgba(9,7,20,0.95));border:1px solid {{ $q->is_active ? 'rgba(16,185,129,0.25)' : 'rgba(245,158,11,0.25)' }};">
            <div class="flex items-start gap-4">
                <div class="text-3xl flex-shrink-0" style="width:44px;text-align:center;">{{ $q->icon }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="font-black text-white text-sm">{{ $q->title }}</span>
                        <span class="gq-badge gq-src-{{ $q->source }}">{{ $srcMeta }}</span>
                        <span class="gq-badge" style="background:rgba(124,58,237,0.15);color:#a78bfa;border:1px solid rgba(124,58,237,0.3);">Lv {{ $q->level_required ?? 1 }}</span>
                        <span class="gq-badge" style="background:rgba(251,191,36,0.1);color:#fcd34d;border:1px solid rgba(251,191,36,0.25);">+{{ $q->xp_reward }} XP · Ksh {{ number_format($q->kes_reward) }}</span>
                        <span class="gq-badge gq-status-badge" style="{{ $q->is_active ? 'background:rgba(16,185,129,0.15);color:#34d399;' : 'background:rgba(245,158,11,0.14);color:#fcd34d;' }}">{{ $q->is_active ? '✅ Published' : '📝 Draft' }}</span>
                        @if(($q->age_group ?? 'all') !== 'all')
                        <span class="gq-badge" style="background:rgba(56,189,248,0.12);color:#7dd3fc;border:1px solid rgba(56,189,248,0.3);">{{ $q->age_group }}</span>
                        @endif
                        @if($q->family_count > 1)
                        <button type="button" onclick="gqFamily('{{ $q->combo_sig }}')"
                                class="gq-badge" style="background:rgba(236,72,153,0.12);color:#f9a8d4;border:1px solid rgba(236,72,153,0.35);cursor:pointer;"
                                title="Same recipe ({{ str_replace('+', ' + ', $q->combo_sig) }}) — click to see the whole family">
                            👯 {{ $q->family_count - 1 }} similar
                        </button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 leading-relaxed">{{ $q->description }}</p>
                    @if($stepLabels)
                    <p class="text-[11px] text-emerald-300/80 font-semibold mt-1.5">{{ $stepLabels }}</p>
                    @endif
                    @if($q->lesson)
                    <p class="text-[11px] text-indigo-300/70 italic mt-1">💡 {{ $q->lesson }}</p>
                    @endif
                </div>
                <div class="flex flex-col gap-1.5 flex-shrink-0">
                    <button type="button" onclick="gqToggle({{ $q->id }}, this)"
                            class="gq-toggle px-3 py-1.5 rounded-lg text-[11px] font-black transition-colors"
                            style="{{ $q->is_active ? 'background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);color:#9ca3af;' : 'background:rgba(16,185,129,0.14);border:1px solid rgba(16,185,129,0.4);color:#6ee7b7;' }}">
                        {{ $q->is_active ? 'Unpublish' : '✅ Publish' }}
                    </button>
                    <a href="{{ route('gameset.quests.edit', $q) }}" class="px-3 py-1.5 rounded-lg text-[11px] font-black text-indigo-300 text-center"
                       style="background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.3);">✏️ Edit</a>
                    <button type="button" onclick="gqDiscard({{ $q->id }})" class="px-3 py-1.5 rounded-lg text-[11px] font-black text-red-300/80"
                            style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);">🗑 Discard</button>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16 text-gray-500">
            <div class="text-5xl mb-3">🎲</div>
            <p class="font-bold">Nothing generated yet.</p>
            <p class="text-sm mt-1.5">Head to <a class="text-indigo-300 font-bold" href="{{ route('gameset.automation.index') }}">Automation</a> and press the Quest Mixer or Run Sweep.</p>
        </div>
        @endforelse

        <div id="gq-none" class="gq-hidden text-center py-14 text-gray-500">
            <div class="text-4xl mb-2">🔍</div>
            <p class="font-bold text-sm">Nothing matches those filters.</p>
        </div>
    </div>
</div>

<script>
(function () {
    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const cards = () => Array.from(document.querySelectorAll('.gq-card'));
    const visible = () => cards().filter(c => !c.classList.contains('gq-hidden'));
    let sugHot = 0;

    /* ── Instant filtering (no submit button anywhere) ─────────────── */
    let familySig = '';

    window.gqFilter = function () {
        const q      = document.getElementById('gq-search').value.trim().toLowerCase();
        const source = document.getElementById('gq-source').value;
        const status = document.getElementById('gq-status').value;
        const level  = document.getElementById('gq-level').value;
        const age    = document.getElementById('gq-age').value;

        let shown = 0;
        cards().forEach(c => {
            const ok = (!q || c.dataset.search.includes(q))
                && (!source || c.dataset.source === source)
                && (!status || c.dataset.status === status)
                && (!level  || c.dataset.level === level)
                && (!age    || c.dataset.age === age)
                && (!familySig || c.dataset.sig === familySig);
            c.classList.toggle('gq-hidden', !ok);
            if (ok) shown++;
        });
        document.getElementById('gq-count').textContent = shown;
        document.getElementById('gq-none').classList.toggle('gq-hidden', shown > 0 || cards().length === 0);
    };

    /* Similarity families: show every quest built from the same recipe */
    window.gqFamily = function (sig) {
        familySig = sig;
        const chip = document.getElementById('gq-family-chip');
        chip.classList.toggle('gq-hidden', !sig);
        if (sig) document.getElementById('gq-family-name').textContent = sig.replaceAll('+', ' + ');
        gqFilter();
        if (sig) window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    /* Stat tiles jump straight into a filter */
    window.gqJump = function (spec) {
        document.getElementById('gq-source').value = spec.startsWith('src:') ? spec.slice(4) : '';
        document.getElementById('gq-status').value = (spec === 'draft' || spec === 'published') ? spec : '';
        gqFilter();
    };

    /* ── Live search with suggestions ──────────────────────────────── */
    const searchEl  = document.getElementById('gq-search');
    const suggestEl = document.getElementById('gq-suggest');

    function renderSuggestions() {
        const q = searchEl.value.trim().toLowerCase();
        if (q.length < 2) { suggestEl.classList.add('gq-hidden'); return; }
        const hits = cards().filter(c => c.dataset.search.includes(q)).slice(0, 8);
        if (!hits.length) { suggestEl.classList.add('gq-hidden'); return; }
        suggestEl.innerHTML = '';
        hits.forEach((c, i) => {
            const item = document.createElement('div');
            item.className = 'gq-sug-item' + (i === sugHot ? ' hot' : '');
            const ico = document.createElement('span'); ico.textContent = c.dataset.icon;
            const txt = document.createElement('span'); txt.textContent = c.dataset.title;
            const lvl = document.createElement('span'); lvl.textContent = 'Lv ' + c.dataset.level;
            lvl.style.cssText = 'margin-left:auto;font-size:10px;color:#6b7280;';
            item.append(ico, txt, lvl);
            item.onclick = () => gqReveal(c.dataset.id);
            suggestEl.appendChild(item);
        });
        suggestEl.classList.remove('gq-hidden');
    }

    window.gqReveal = function (id) {
        suggestEl.classList.add('gq-hidden');
        const el = document.getElementById('gq-quest-' + id);
        if (!el) return;
        el.classList.remove('gq-hidden');
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.classList.add('gq-flash');
        setTimeout(() => el.classList.remove('gq-flash'), 1700);
    };

    searchEl.addEventListener('input', () => { sugHot = 0; gqFilter(); renderSuggestions(); });
    searchEl.addEventListener('keydown', (e) => {
        const items = suggestEl.querySelectorAll('.gq-sug-item');
        if (e.key === 'ArrowDown') { e.preventDefault(); sugHot = Math.min(sugHot + 1, items.length - 1); renderSuggestions(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); sugHot = Math.max(sugHot - 1, 0); renderSuggestions(); }
        if (e.key === 'Enter' && items[sugHot]) { e.preventDefault(); items[sugHot].click(); }
        if (e.key === 'Escape') suggestEl.classList.add('gq-hidden');
    });
    document.addEventListener('click', (e) => {
        if (!searchEl.contains(e.target) && !suggestEl.contains(e.target)) suggestEl.classList.add('gq-hidden');
    });

    /* ── Publish / discard (single + bulk) ─────────────────────────── */
    window.gqToggle = async function (id, btn) {
        const res  = await fetch(`/gameset/quests/${id}/toggle-active`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        const card = document.getElementById('gq-quest-' + id);
        const on   = !!data.is_active;
        card.dataset.status = on ? 'published' : 'draft';
        card.style.borderColor = on ? 'rgba(16,185,129,0.25)' : 'rgba(245,158,11,0.25)';
        const badge = card.querySelector('.gq-status-badge');
        badge.textContent = on ? '✅ Published' : '📝 Draft';
        badge.style.cssText = on ? 'background:rgba(16,185,129,0.15);color:#34d399;' : 'background:rgba(245,158,11,0.14);color:#fcd34d;';
        btn.textContent = on ? 'Unpublish' : '✅ Publish';
        btn.style.cssText = on
            ? 'background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);color:#9ca3af;'
            : 'background:rgba(16,185,129,0.14);border:1px solid rgba(16,185,129,0.4);color:#6ee7b7;';
        if (window.pesaToast) pesaToast(on ? 'Quest published — live for players.' : 'Quest unpublished — back to drafts.', 'success', on ? '✅' : '📝');
        gqFilter();
    };

    window.gqDiscard = async function (id) {
        if (!confirm('Discard this generated quest? The Mixer can always compose a fresh one.')) return;
        await bulkCall('delete', [id]);
        document.getElementById('gq-quest-' + id)?.remove();
        gqFilter();
    };

    window.gqBulk = async function (action) {
        const pool = visible().filter(c => action === 'delete' || c.dataset.status === 'draft');
        if (!pool.length) { if (window.pesaToast) pesaToast('Nothing visible to ' + (action === 'delete' ? 'discard' : 'publish') + '.', 'warning'); return; }
        const verb = action === 'delete' ? `Discard ${pool.length} visible quest(s)? This cannot be undone.` : `Publish ${pool.length} visible draft(s) to players?`;
        if (!confirm(verb)) return;
        const ids = pool.map(c => parseInt(c.dataset.id));
        const n   = await bulkCall(action, ids);
        if (action === 'delete') { pool.forEach(c => c.remove()); }
        else { pool.forEach(c => { const b = c.querySelector('.gq-toggle'); if (c.dataset.status === 'draft') gqPaint(c, true); }); }
        if (window.pesaToast) pesaToast(`${n} quest(s) ${action === 'delete' ? 'discarded' : 'published'}.`, 'success', action === 'delete' ? '🗑' : '✅');
        gqFilter();
    };

    function gqPaint(card, on) {
        card.dataset.status = on ? 'published' : 'draft';
        card.style.borderColor = on ? 'rgba(16,185,129,0.25)' : 'rgba(245,158,11,0.25)';
        const badge = card.querySelector('.gq-status-badge');
        badge.textContent = on ? '✅ Published' : '📝 Draft';
        badge.style.cssText = on ? 'background:rgba(16,185,129,0.15);color:#34d399;' : 'background:rgba(245,158,11,0.14);color:#fcd34d;';
        const btn = card.querySelector('.gq-toggle');
        btn.textContent = on ? 'Unpublish' : '✅ Publish';
    }

    async function bulkCall(action, ids) {
        const url = action === 'delete' ? '{{ route('gameset.quests.bulk-delete') }}' : '{{ route('gameset.quests.bulk-activate') }}';
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ ids }),
        });
        const data = await res.json();
        return data.deleted ?? data.activated ?? 0;
    }
})();
</script>
</body>
</html>
