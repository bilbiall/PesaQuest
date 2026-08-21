<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quests — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        @keyframes popIn { from{opacity:0;transform:translateY(10px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
        .card-in { animation:popIn .25s ease both; }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'quests'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">📜 Quests</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('gameset.quests.generated') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-violet-300 transition-all hover:scale-[1.02] hover:text-white"
               style="background:rgba(124,58,237,0.12);border:1px solid rgba(124,58,237,0.35);">
                🎲 Generated
            </a>
            <a href="{{ route('gameset.automation.index') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-indigo-300 transition-all hover:scale-[1.02] hover:text-white"
               style="background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.35);">
                🤖 Automation
            </a>
            <a href="{{ route('gameset.quests.create') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
               style="background:linear-gradient(135deg,#7c3aed,#6d28d9);box-shadow:0 4px 14px rgba(124,58,237,.35);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Quest
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-semibold text-emerald-300 border border-emerald-500/30"
         style="background:rgba(16,185,129,0.1);">
        ✓ {{ session('success') }}
    </div>
    @endif

    {{-- Quest Factory drafts banner --}}
    @if(($stats['drafts'] ?? 0) > 0 && !request()->boolean('drafts'))
    <a href="{{ route('gameset.quests.generated') }}"
       class="mb-6 px-4 py-3 rounded-xl text-sm font-bold text-amber-300 flex items-center gap-2 transition-all hover:scale-[1.005]"
       style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);display:flex;">
        🤖 The Quest Factory drafted <b>{{ $stats['drafts'] }}</b> quest{{ $stats['drafts'] === 1 ? '' : 's' }} from your new content — review & publish →
    </a>
    @elseif(request()->boolean('drafts'))
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-bold text-amber-300 flex flex-wrap items-center justify-between gap-2"
         style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);">
        <span>🤖 Showing factory drafts. Use each quest's <b>activate toggle</b> to publish, edit to tweak the copy, or delete to discard.</span>
        <a href="{{ route('gameset.quests.index') }}" class="text-xs font-black text-gray-300 hover:text-white">← All quests</a>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="rounded-2xl p-5 text-center" style="background:rgba(124,58,237,0.12);border:1px solid rgba(124,58,237,0.25);">
            <div class="text-3xl font-black text-violet-300">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Total Quests</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);">
            <div class="text-3xl font-black text-emerald-400">{{ $stats['active'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Active</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25);">
            <div class="text-3xl font-black text-amber-400">{{ $stats['triggered'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Auto-Trigger</div>
        </div>
    </div>

    {{-- Age-group tabs --}}
    @php
        $ageTabs = [
            ''      => ['🌍', 'All'],
            '8-12'  => ['🧒', '8–12'],
            '13-17' => ['🎒', '13–17'],
            '18-25' => ['🎓', '18–25'],
            '26+'   => ['💼', '26+'],
            'all'   => ['👐', 'Everyone-quests'],
        ];
        $currentAge = request('age', '');
    @endphp
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach($ageTabs as $key => [$icon, $label])
        <a href="{{ route('gameset.quests.index', array_filter(array_merge(request()->except('age'), $key !== '' ? ['age' => $key] : []))) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition-colors"
           style="{{ $currentAge === (string) $key
                ? 'background:rgba(124,58,237,0.25);border:1px solid rgba(124,58,237,0.5);color:#c4b5fd;'
                : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);color:#9ca3af;' }}">
            {{ $icon }} {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Filters — apply instantly (selects submit on change; search filters live) --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6" id="qi-filter-form">
        @if(request('age'))<input type="hidden" name="age" value="{{ request('age') }}">@endif
        <div class="relative flex-1 min-w-[200px]">
            <input type="text" name="search" id="qi-search" value="{{ request('search') }}" autocomplete="off"
                   placeholder="🔍 Search quests — filters as you type…"
                   class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-violet-500/60"/>
            <div id="qi-suggest" class="absolute left-0 right-0 z-50 rounded-xl overflow-hidden" style="top:calc(100% + 6px);background:#100e1e;border:1px solid rgba(124,58,237,.4);box-shadow:0 18px 50px rgba(0,0,0,.6);display:none;"></div>
        </div>
        <select name="level" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white focus:outline-none focus:border-violet-500/60">
            <option value="">All Levels</option>
            @for($i = 1; $i <= 10; $i++)
            <option value="{{ $i }}" {{ request('level') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
            @endfor
        </select>
        <select name="active" onchange="this.form.submit()" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white focus:outline-none focus:border-violet-500/60">
            <option value="">All Status</option>
            <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        @if(request()->hasAny(['search','level','active']))
        <a href="{{ route('gameset.quests.index') }}" class="px-5 py-2 rounded-xl border border-white/10 text-sm font-semibold text-gray-500 hover:text-white transition-colors">
            Clear
        </a>
        @endif
    </form>

    {{-- Quest list (drag the ⠿ handle to reorder, then Save Order) --}}
    <div class="flex items-center justify-between gap-3 mb-3">
        <p class="text-[11px] text-gray-600">⠿ Drag a quest by its handle to change its sort order — this is scoped per level + age group (the exact bucket a player sees). Filter to one level and one age tab above before reordering.</p>
        <p class="text-[11px] text-gray-500 whitespace-nowrap">Showing {{ $quests->firstItem() ?? 0 }}–{{ $quests->lastItem() ?? 0 }} of {{ $quests->total() }}</p>
    </div>
    <div id="quest-list">
    @forelse($quests as $quest)
    <div class="card-in quest-card rounded-2xl border mb-3 overflow-hidden"
         data-quest-id="{{ $quest->id }}"
         data-search="{{ Str::lower($quest->title . ' ' . $quest->description . ' ' . ($quest->trigger_label ?? '') . ' lv' . ($quest->level_required ?? 1)) }}"
         data-title="{{ $quest->title }}"
         data-icon="{{ $quest->icon }}"
         style="background:linear-gradient(135deg,rgba(15,13,30,0.9),rgba(9,7,20,0.95));border-color:rgba(255,255,255,0.07);"
         x-data="{ deleting: false }">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            {{-- Drag handle --}}
            <div class="drag-handle flex-shrink-0 self-center select-none" draggable="true"
                 title="Drag to reorder"
                 style="cursor:grab;color:#4b5563;font-size:18px;line-height:1;padding:6px 2px;">⠿</div>
            {{-- Icon/Image + level badge --}}
            <div class="flex-shrink-0 text-center" style="width:52px;">
                @if($quest->image)
                <img src="{{ asset('storage/'.$quest->image) }}" alt="{{ $quest->title }}"
                     style="width:48px;height:48px;border-radius:10px;object-fit:cover;border:1px solid rgba(255,255,255,0.1);">
                @else
                <div style="height:48px;display:flex;align-items:center;justify-content:center;"><x-icon :name="$quest->icon" class="w-8 h-8" /></div>
                @endif
                <div class="text-[9px] font-black mt-1 rounded-full px-1.5 py-0.5"
                     style="background:rgba(124,58,237,0.2);color:#a78bfa;border:1px solid rgba(124,58,237,0.3);">
                    Lv.{{ $quest->level_required ?? 1 }}
                </div>
                <div class="text-[9px] font-black mt-1 rounded-full px-1.5 py-0.5"
                     title="Sort order — lower numbers show first in the player quest list"
                     style="background:rgba(255,255,255,0.06);color:#9ca3af;border:1px solid rgba(255,255,255,0.1);">
                    Sort {{ $quest->sort_order ?? 0 }}
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <span class="font-black text-white text-sm">{{ $quest->title }}</span>
                        @if(!$quest->is_active)
                        <span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.2);">INACTIVE</span>
                        @else
                        <span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.2);">ACTIVE</span>
                        @endif
                        @if($quest->trigger_type)
                        <span class="ml-1 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(251,191,36,0.12);color:#fbbf24;border:1px solid rgba(251,191,36,0.2);">🎯 AUTO</span>
                        @endif
                        @if(!empty($quest->career_fields))
                        @php $fieldIcons = collect($quest->career_fields)->map(fn($k) => \App\Services\CareerService::fieldsByKey()[$k]['icon'] ?? '💼')->implode(' '); @endphp
                        <span class="ml-1 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(99,102,241,0.12);color:#a5b4fc;border:1px solid rgba(99,102,241,0.2);" title="Only shown for these career paths">{{ $fieldIcons }} ONLY</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-[11px] font-semibold text-violet-300">+{{ number_format($quest->xp_reward) }} XP</span>
                        @if($quest->kes_reward > 0)
                        <span class="text-[11px] font-semibold text-emerald-400">Ksh {{ number_format($quest->kes_reward) }}</span>
                        @endif
                    </div>
                </div>
                @if($quest->description)
                <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $quest->description }}</p>
                @endif
                @if($quest->trigger_label)
                <div class="mt-2 text-[11px] text-amber-400/80 flex items-center gap-1">
                    <span>🎯</span><span>{{ $quest->trigger_label }}</span>
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- Toggle active --}}
                <button onclick="toggleQuestActive({{ $quest->id }}, this)"
                        data-active="{{ $quest->is_active ? '1' : '0' }}"
                        title="{{ $quest->is_active ? 'Deactivate' : 'Activate' }}"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                    <span class="text-sm">{{ $quest->is_active ? '✓' : '○' }}</span>
                </button>
                <a href="{{ route('gameset.quests.edit', $quest) }}"
                   class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-white/10"
                   style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);"
                   title="Edit">✏️</a>
                <button @click="deleting=true"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-red-500/10"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);"
                        title="Delete">🗑️</button>
            </div>
        </div>

        {{-- Delete confirm --}}
        <div x-show="deleting" x-cloak class="border-t border-red-500/20 bg-red-500/5 px-5 py-3 flex items-center justify-between gap-4">
            <span class="text-sm text-red-400 font-semibold">Delete "{{ $quest->title }}"? This cannot be undone.</span>
            <div class="flex gap-2">
                <button @click="deleting=false" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-gray-400 border border-white/10 hover:bg-white/5">Cancel</button>
                <form method="POST" action="{{ route('gameset.quests.destroy', $quest) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white" style="background:rgba(239,68,68,0.7);">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-gray-500">
        <div class="text-5xl mb-4">📜</div>
        <div class="text-lg font-bold mb-2">No quests yet</div>
        <a href="{{ route('gameset.quests.create') }}" class="text-violet-400 hover:text-violet-300 text-sm">Create the first quest →</a>
    </div>
    @endforelse
    </div>

    {{-- Pagination --}}
    @if($quests->hasPages())
    <div class="flex items-center justify-between gap-3 mt-6 flex-wrap">
        <div>
            @if($quests->onFirstPage())
            <span class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-white/5">← Previous</span>
            @else
            <a href="{{ $quests->previousPageUrl() }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-300 border border-white/10 hover:bg-white/5 transition-colors">← Previous</a>
            @endif
        </div>
        <div class="flex items-center gap-1 flex-wrap justify-center">
            @foreach($quests->getUrlRange(1, $quests->lastPage()) as $page => $url)
                @if($page === $quests->currentPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-black text-white" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-semibold text-gray-400 hover:bg-white/5 transition-colors">{{ $page }}</a>
                @endif
            @endforeach
        </div>
        <div>
            @if($quests->hasMorePages())
            <a href="{{ $quests->nextPageUrl() }}" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-300 border border-white/10 hover:bg-white/5 transition-colors">Next →</a>
            @else
            <span class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-white/5">Next →</span>
            @endif
        </div>
    </div>
    @endif

    {{-- Save-order bar (appears after a drag) --}}
    <div id="save-order-bar" style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:60;background:#12101f;"
         class="items-center gap-3 px-5 py-3 rounded-2xl border border-violet-500/40 shadow-2xl">
        <span class="text-sm font-bold text-violet-200">Order changed</span>
        <button onclick="saveQuestOrder(this)" class="px-4 py-2 rounded-xl text-sm font-black text-white" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">💾 Save Order</button>
        <button onclick="location.reload()" class="px-3 py-2 rounded-xl text-sm font-bold text-gray-400 hover:text-white">Reset</button>
    </div>
</div>

<script>
/* ── Drag-to-reorder (native HTML5 DnD — handle starts the drag, card is the unit) ── */
(function () {
    const list = document.getElementById('quest-list');
    if (!list) return;
    let dragging = null;

    list.querySelectorAll('.drag-handle').forEach(handle => {
        const card = handle.closest('.quest-card');
        handle.addEventListener('dragstart', e => {
            dragging = card;
            card.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setDragImage(card, 20, 20);
        });
        handle.addEventListener('dragend', () => {
            if (dragging) dragging.style.opacity = '';
            dragging = null;
        });
    });

    list.addEventListener('dragover', e => {
        e.preventDefault();
        if (!dragging) return;
        const cards = [...list.querySelectorAll('.quest-card:not([style*="opacity: 0.4"])')];
        const after = cards.find(c => e.clientY < c.getBoundingClientRect().top + c.offsetHeight / 2);
        after ? list.insertBefore(dragging, after) : list.appendChild(dragging);
        document.getElementById('save-order-bar').style.display = 'flex';
    });
})();

function saveQuestOrder(btn) {
    const ids = [...document.querySelectorAll('#quest-list .quest-card')].map(c => parseInt(c.dataset.questId));
    btn.disabled = true; btn.textContent = 'Saving…';
    fetch('{{ route('gameset.quests.reorder') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ids }),
    })
    .then(r => r.json())
    .then(() => location.reload())
    .catch(() => { btn.disabled = false; btn.textContent = '💾 Save Order'; alert('Could not save order.'); });
}

function toggleQuestActive(id, btn) {
    const active = btn.dataset.active === '1';
    fetch(`/gameset/quests/${id}/toggle-active`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        btn.dataset.active = data.is_active ? '1' : '0';
        btn.querySelector('span').textContent = data.is_active ? '✓' : '○';
        btn.title = data.is_active ? 'Deactivate' : 'Activate';
        // Refresh to update badges
        location.reload();
    });
}
</script>

{{-- Live search: filters the loaded list as you type + clickable suggestions --}}
<script>
(function () {
    const input   = document.getElementById('qi-search');
    const suggest = document.getElementById('qi-suggest');
    if (!input || !suggest) return;
    const cards = () => Array.from(document.querySelectorAll('.quest-card[data-search]'));
    let hot = 0;

    function filterLive() {
        const q = input.value.trim().toLowerCase();
        cards().forEach(c => { c.style.display = (!q || c.dataset.search.includes(q)) ? '' : 'none'; });
    }

    function renderSuggest() {
        const q = input.value.trim().toLowerCase();
        if (q.length < 2) { suggest.style.display = 'none'; return; }
        const hits = cards().filter(c => c.dataset.search.includes(q)).slice(0, 8);
        if (!hits.length) { suggest.style.display = 'none'; return; }
        suggest.innerHTML = '';
        hits.forEach((c, i) => {
            const row = document.createElement('div');
            row.style.cssText = 'padding:9px 13px;font-size:12.5px;font-weight:700;color:#e5e7eb;cursor:pointer;display:flex;gap:8px;align-items:center;' +
                (i === hot ? 'background:rgba(124,58,237,.15);' : '') + (i > 0 ? 'border-top:1px solid rgba(255,255,255,.05);' : '');
            const ico = document.createElement('span'); ico.textContent = c.dataset.icon || '📜';
            const txt = document.createElement('span'); txt.textContent = c.dataset.title;
            row.append(ico, txt);
            row.onmouseenter = () => { hot = i; renderSuggest(); };
            row.onclick = () => {
                suggest.style.display = 'none';
                cards().forEach(x => x.style.display = '');
                input.value = '';
                c.scrollIntoView({ behavior: 'smooth', block: 'center' });
                c.style.boxShadow = '0 0 0 2px rgba(124,58,237,.8)';
                setTimeout(() => c.style.boxShadow = '', 1700);
            };
            suggest.appendChild(row);
        });
        suggest.style.display = 'block';
    }

    input.addEventListener('input', () => { hot = 0; filterLive(); renderSuggest(); });
    input.addEventListener('keydown', (e) => {
        const rows = suggest.children;
        if (e.key === 'ArrowDown' && rows.length) { e.preventDefault(); hot = Math.min(hot + 1, rows.length - 1); renderSuggest(); }
        if (e.key === 'ArrowUp'   && rows.length) { e.preventDefault(); hot = Math.max(hot - 1, 0); renderSuggest(); }
        if (e.key === 'Enter'     && rows[hot] && suggest.style.display !== 'none') { e.preventDefault(); rows[hot].click(); }
        if (e.key === 'Escape') suggest.style.display = 'none';
    });
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !suggest.contains(e.target)) suggest.style.display = 'none';
    });
})();
</script>
</body>
</html>
