<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Life Events — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        @keyframes popIn { from{opacity:0;transform:translateY(12px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
        .card-in { animation:popIn .3s cubic-bezier(.34,1.56,.64,1) both; }
        .cat-vehicle    { background:linear-gradient(135deg,rgba(245,158,11,.12),rgba(28,25,23,1));  border-color:rgba(245,158,11,.25); }
        .cat-property   { background:linear-gradient(135deg,rgba(59,130,246,.12),rgba(15,23,42,1));   border-color:rgba(59,130,246,.25); }
        .cat-business   { background:linear-gradient(135deg,rgba(139,92,246,.12),rgba(30,27,75,1)); border-color:rgba(139,92,246,.25); }
        .cat-investment { background:linear-gradient(135deg,rgba(16,185,129,.12),rgba(15,23,42,1));   border-color:rgba(16,185,129,.25); }
        .cat-gadget     { background:linear-gradient(135deg,rgba(244,114,182,.12),rgba(26,16,37,1));  border-color:rgba(244,114,182,.25); }
        .cat-general    { background:rgba(255,255,255,.03); border-color:rgba(255,255,255,.08); }
        .toggle-track { width:2.25rem; height:1.1rem; border-radius:9999px; transition:background .2s; cursor:pointer; position:relative; display:inline-block; vertical-align:middle; }
        .toggle-thumb { position:absolute; top:.1rem; left:.1rem; width:.9rem; height:.9rem; border-radius:50%; background:#fff; transition:transform .2s; }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'life-events'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">⚡ Life Events</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('gameset.bills.index') }}"
               class="hidden sm:flex items-center gap-1.5 text-xs text-emerald-400 border border-emerald-500/25 hover:border-emerald-500/50 px-3 py-1.5 rounded-lg transition-colors">
                🗓 Bills
            </a>
            <a href="{{ route('gameset.assets.index') }}"
               class="hidden sm:flex items-center gap-1.5 text-xs text-amber-400 border border-amber-500/25 hover:border-amber-500/50 px-3 py-1.5 rounded-lg transition-colors">
                🏢 Assets
            </a>
            <a href="{{ route('gameset.life-events.create') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
               style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);box-shadow:0 4px 14px rgba(139,92,246,.35);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Event
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 flex items-center gap-3 text-sm font-bold text-emerald-300"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span>{{ session('success') }}
    </div>
    @endif

    {{-- Header + Stats --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black text-white mb-1">Life Events</h1>
        <p class="text-gray-400 text-sm mb-6">
            Probabilistic events that fire during the life simulator. Asset-linked events only fire when a player owns the relevant asset type.
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php $statCards = [
                ['label'=>'Total',    'val'=>$stats['total'],    'color'=>'text-white',       'bg'=>'rgba(255,255,255,.05)', 'border'=>'rgba(255,255,255,.1)'],
                ['label'=>'Active',   'val'=>$stats['active'],   'color'=>'text-emerald-400', 'bg'=>'rgba(16,185,129,.08)', 'border'=>'rgba(16,185,129,.2)'],
                ['label'=>'Positive', 'val'=>$stats['positive'], 'color'=>'text-emerald-400', 'bg'=>'rgba(16,185,129,.06)', 'border'=>'rgba(16,185,129,.15)'],
                ['label'=>'Negative', 'val'=>$stats['negative'], 'color'=>'text-red-400',     'bg'=>'rgba(239,68,68,.06)',  'border'=>'rgba(239,68,68,.15)'],
                ['label'=>'General',  'val'=>$stats['general'],  'color'=>'text-gray-300',    'bg'=>'rgba(255,255,255,.03)','border'=>'rgba(255,255,255,.08)'],
                ['label'=>'Asset-linked','val'=>$stats['asset'], 'color'=>'text-violet-400',  'bg'=>'rgba(139,92,246,.06)', 'border'=>'rgba(139,92,246,.15)'],
            ]; @endphp
            @foreach($statCards as $s)
            <div class="rounded-2xl p-4 text-center" style="background:{{ $s['bg'] }};border:1px solid {{ $s['border'] }};">
                <p class="text-xl font-black {{ $s['color'] }}">{{ $s['val'] }}</p>
                <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2 mb-6 items-center">
        <input name="search" value="{{ request('search') }}" placeholder="Search events…"
               class="text-sm bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 outline-none focus:border-indigo-500/60 w-48">
        <select name="chapter" class="text-sm bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2 outline-none focus:border-indigo-500/60">
            <option value="">All chapters</option>
            @foreach(['all','8-12','13-17','18-25','26+'] as $ch)
            <option value="{{ $ch }}" {{ request('chapter') === $ch ? 'selected' : '' }}>{{ $ch }}</option>
            @endforeach
        </select>
        <select name="asset_category" class="text-sm bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2 outline-none focus:border-indigo-500/60">
            <option value="">All types</option>
            <option value="general"    {{ request('asset_category') === 'general'    ? 'selected' : '' }}>General (no asset)</option>
            @foreach(['vehicle','property','business','investment','fixed_income','gadget'] as $cat)
            <option value="{{ $cat }}" {{ request('asset_category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>
        <select name="polarity" class="text-sm bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2 outline-none focus:border-indigo-500/60">
            <option value="">All polarities</option>
            <option value="positive" {{ request('polarity') === 'positive' ? 'selected' : '' }}>Positive</option>
            <option value="negative" {{ request('polarity') === 'negative' ? 'selected' : '' }}>Negative</option>
        </select>
        <button type="submit" class="text-sm bg-violet-500/15 hover:bg-violet-500/25 text-violet-400 border border-violet-500/25 px-4 py-2 rounded-xl transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['search','chapter','asset_category','polarity']))
        <a href="{{ route('gameset.life-events.index') }}" class="text-xs text-gray-500 hover:text-white">Clear</a>
        @endif
        <span class="ml-auto text-[11px] text-gray-600">{{ $events->count() }} event{{ $events->count() !== 1 ? 's' : '' }}</span>
    </form>

    @if($events->isEmpty())
    <div class="text-center py-20">
        <div class="text-5xl mb-4">⚡</div>
        <h3 class="text-lg font-bold text-white mb-2">No life events found</h3>
        <p class="text-gray-500 text-sm mb-6">Adjust filters or create the first event.</p>
        <a href="{{ route('gameset.life-events.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white"
           style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">
            + Create first event
        </a>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($events as $ev)
        @php
            $catKey = $ev->asset_category ?? 'general';
            $catColor = match($catKey) {
                'vehicle'    => '#f59e0b',
                'property'   => '#3b82f6',
                'business'   => '#8b5cf6',
                'investment' => '#10b981',
                'fixed_income' => '#2dd4bf',
                'gadget'     => '#f472b6',
                default      => '#6b7280',
            };
        @endphp
        <div class="card-in cat-{{ $catKey }} border rounded-2xl overflow-hidden" x-data="{ active: {{ $ev->is_active ? 'true' : 'false' }} }">
            <div class="px-4 pt-4 pb-3 flex items-start justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">{{ $ev->icon ?? '⚡' }}</span>
                    <div>
                        <div class="text-sm font-bold text-white leading-tight">{{ $ev->title }}</div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            @if($ev->asset_category)
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full" style="background:{{ $catColor }}22;color:{{ $catColor }}">
                                {{ ucfirst($ev->asset_category) }}
                            </span>
                            @else
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-white/8 text-gray-400">General</span>
                            @endif
                            <span class="text-[9px] text-gray-600">{{ $ev->chapter }}</span>
                        </div>
                    </div>
                </div>
                <button @click="active = !active; toggleActive({{ $ev->id }})"
                        class="shrink-0 toggle-track"
                        :style="active ? 'background:#10b981' : 'background:rgba(255,255,255,.15)'">
                    <span class="toggle-thumb" :style="active ? 'transform:translateX(1.15rem)' : ''"></span>
                </button>
            </div>

            <div class="px-4 pb-3 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Effect type</span>
                    <span class="text-[11px] font-bold text-gray-300">{{ $ev->effect_type }}</span>
                </div>
                @if($ev->effect_type === 'balance_delta')
                @php $ed = $ev->effect_data ?? []; @endphp
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Range</span>
                    <span class="text-[11px] font-bold {{ $ev->is_positive ? 'text-emerald-400' : 'text-red-400' }}">
                        Ksh {{ number_format($ed['balance_min'] ?? 0) }} → {{ number_format($ed['balance_max'] ?? 0) }}
                    </span>
                </div>
                @elseif($ev->effect_type === 'market_event')
                @php $pct = ($ev->effect_data['market_categories'][0]['pct'] ?? 0) * 100; @endphp
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Market impact</span>
                    <span class="text-[11px] font-bold {{ $pct >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $pct >= 0 ? '+' : '' }}{{ $pct }}%
                    </span>
                </div>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Probability</span>
                    <span class="text-[11px] font-bold text-indigo-400">{{ round($ev->probability * 100, 1) }}%/tick</span>
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                    @if($ev->is_positive)
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">Positive</span>
                    @else
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-red-500/15 text-red-400">Negative</span>
                    @endif
                </div>
            </div>

            @if($ev->educational_note)
            <div class="px-4 pb-3">
                <p class="text-[10px] text-gray-600 leading-relaxed line-clamp-2">💡 {{ $ev->educational_note }}</p>
            </div>
            @endif

            <div class="border-t border-white/5 flex">
                <a href="{{ route('gameset.life-events.edit', $ev) }}"
                   class="flex-1 text-center text-[11px] font-bold text-indigo-400 hover:bg-indigo-500/10 py-2.5 transition-colors">
                    ✏️ Edit
                </a>
                <form method="POST" action="{{ route('gameset.life-events.destroy', $ev) }}"
                      onsubmit="return confirm('Delete this event?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-[11px] font-bold text-red-400 hover:bg-red-500/10 px-4 py-2.5 transition-colors">
                        🗑
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

<script>
async function toggleActive(id) {
    await fetch(`/gameset/life-events/${id}/toggle-active`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    });
}
</script>
</body>
</html>
