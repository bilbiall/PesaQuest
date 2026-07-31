<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bills — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        @keyframes popIn { from{opacity:0;transform:translateY(12px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
        .card-in { animation:popIn .3s cubic-bezier(.34,1.56,.64,1) both; }
        .cat-housing       { background:linear-gradient(135deg,#064e3b,#0f172a); border-color:rgba(16,185,129,.3); }
        .cat-transport     { background:linear-gradient(135deg,#92400e,#1c1917); border-color:rgba(245,158,11,.3); }
        .cat-utilities     { background:linear-gradient(135deg,#1e3a8a,#0f172a); border-color:rgba(96,165,250,.3); }
        .cat-food          { background:linear-gradient(135deg,#14532d,#0f172a); border-color:rgba(34,197,94,.3); }
        .cat-healthcare    { background:linear-gradient(135deg,#831843,#1a1025); border-color:rgba(244,114,182,.3); }
        .cat-education     { background:linear-gradient(135deg,#3730a3,#1e1b4b); border-color:rgba(139,92,246,.3); }
        .cat-social        { background:linear-gradient(135deg,#7c2d12,#1c1917); border-color:rgba(251,146,60,.3); }
        .cat-entertainment { background:linear-gradient(135deg,#312e81,#0f172a); border-color:rgba(165,180,252,.3); }
        .cat-tax           { background:linear-gradient(135deg,#4c0519,#1a0011); border-color:rgba(251,113,133,.3); }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'bills'])

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">🗓 Bills</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('gameset.life-events.index') }}"
               class="hidden sm:flex items-center gap-1.5 text-xs text-violet-400 border border-violet-500/25 hover:border-violet-500/50 px-3 py-1.5 rounded-lg transition-colors">
                ⚡ Life Events
            </a>
            <a href="{{ route('gameset.assets.index') }}"
               class="hidden sm:flex items-center gap-1.5 text-xs text-amber-400 border border-amber-500/25 hover:border-amber-500/50 px-3 py-1.5 rounded-lg transition-colors">
                🏢 Assets
            </a>
            <a href="{{ route('gameset.bills.create') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
               style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 14px rgba(16,185,129,.35);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Bill
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
        <h1 class="text-3xl font-black text-white mb-1">Bill Templates</h1>
        <p class="text-gray-400 text-sm mb-6">Manage recurring bills that auto-assign to players based on their life chapter and assets</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @php $statCards = [
                ['label'=>'Total',     'val'=>$stats['total'],     'color'=>'text-white',       'bg'=>'rgba(255,255,255,.05)', 'border'=>'rgba(255,255,255,.1)'],
                ['label'=>'Active',    'val'=>$stats['active'],    'color'=>'text-emerald-400', 'bg'=>'rgba(16,185,129,.08)', 'border'=>'rgba(16,185,129,.2)'],
                ['label'=>'Essential', 'val'=>$stats['essential'], 'color'=>'text-red-400',     'bg'=>'rgba(239,68,68,.08)',  'border'=>'rgba(239,68,68,.2)'],
                ['label'=>'Housing',   'val'=>$stats['housing'],   'color'=>'text-emerald-400', 'bg'=>'rgba(16,185,129,.06)', 'border'=>'rgba(16,185,129,.15)'],
                ['label'=>'Transport', 'val'=>$stats['transport'], 'color'=>'text-amber-400',   'bg'=>'rgba(245,158,11,.06)', 'border'=>'rgba(245,158,11,.15)'],
                ['label'=>'Utilities', 'val'=>$stats['utilities'], 'color'=>'text-blue-400',    'bg'=>'rgba(96,165,250,.06)', 'border'=>'rgba(96,165,250,.15)'],
                ['label'=>'Social',    'val'=>$stats['social'],    'color'=>'text-orange-400',  'bg'=>'rgba(251,146,60,.06)', 'border'=>'rgba(251,146,60,.15)'],
            ]; @endphp
            @foreach($statCards as $s)
            <div class="rounded-2xl p-4 text-center" style="background:{{ $s['bg'] }};border:1px solid {{ $s['border'] }};">
                <p class="text-xl font-black {{ $s['color'] }}">{{ $s['val'] }}</p>
                <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Age-group tabs --}}
    @php
        $ageTabs = ['' => ['🌍','All'], '8-12' => ['🧒','8–12'], '13-17' => ['🎒','13–17'], '18-25' => ['🎓','18–25'], '26+' => ['💼','26+'], 'all' => ['👐','Everyone-bills']];
        $currentAge = request('age_group', '');
    @endphp
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach($ageTabs as $key => [$icon, $label])
        <a href="{{ route('gameset.bills.index', array_filter(array_merge(request()->except('age_group'), $key !== '' ? ['age_group' => $key] : []))) }}"
           class="px-4 py-2 rounded-xl text-sm font-bold transition-colors"
           style="{{ $currentAge === (string) $key
                ? 'background:rgba(99,102,241,0.22);border:1px solid rgba(99,102,241,0.5);color:#a5b4fc;'
                : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);color:#9ca3af;' }}">
            {{ $icon }} {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2 mb-6 items-center">
        <input name="search" value="{{ request('search') }}" placeholder="Search bills…"
               class="text-sm bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 outline-none focus:border-indigo-500/60 w-48">
        <select name="category" class="text-sm bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2 outline-none focus:border-indigo-500/60">
            <option value="">All categories</option>
            @foreach(['housing','transport','utilities','food','healthcare','education','social','entertainment','tax'] as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>
        <select name="age_group" class="text-sm bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2 outline-none focus:border-indigo-500/60">
            <option value="">All ages</option>
            @foreach(['8-12','13-17','18-25','26+','all'] as $ag)
            <option value="{{ $ag }}" {{ request('age_group') === $ag ? 'selected' : '' }}>{{ $ag }}</option>
            @endforeach
        </select>
        <button type="submit" class="text-sm bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-400 border border-indigo-500/25 px-4 py-2 rounded-xl transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['search','category','age_group']))
        <a href="{{ route('gameset.bills.index') }}" class="text-xs text-gray-500 hover:text-white">Clear</a>
        @endif
        <span class="ml-auto text-[11px] text-gray-600">{{ $bills->count() }} bill{{ $bills->count() !== 1 ? 's' : '' }}</span>
    </form>

    {{-- Bills grid --}}
    @if($bills->isEmpty())
    <div class="text-center py-20">
        <div class="text-5xl mb-4">🗓</div>
        <h3 class="text-lg font-bold text-white mb-2">No bills found</h3>
        <p class="text-gray-500 text-sm mb-6">Adjust your filters or create the first bill template.</p>
        <a href="{{ route('gameset.bills.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white"
           style="background:linear-gradient(135deg,#10b981,#059669);">
            + Create first bill
        </a>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($bills as $bill)
        <div class="card-in cat-{{ $bill->category }} border rounded-2xl overflow-hidden" x-data="{ active: {{ $bill->is_active ? 'true' : 'false' }} }">
            {{-- Header --}}
            <div class="px-4 pt-4 pb-3 flex items-start justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">{{ $bill->icon ?? '💸' }}</span>
                    <div>
                        <div class="font-bold text-sm text-white leading-tight">{{ $bill->name }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $bill->slug }}</div>
                    </div>
                </div>
                {{-- Active toggle --}}
                <button @click="toggleActive({{ $bill->id }})"
                        class="shrink-0 w-8 h-4 rounded-full transition-all duration-300 relative"
                        :class="active ? 'bg-emerald-500' : 'bg-white/15'"
                        title="Toggle active">
                    <span class="absolute top-0.5 left-0.5 w-3 h-3 rounded-full bg-white transition-transform duration-300"
                          :class="active ? 'translate-x-4' : 'translate-x-0'"></span>
                </button>
            </div>

            {{-- Details --}}
            <div class="px-4 pb-3 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Amount</span>
                    <span class="text-sm font-black text-white">Ksh {{ number_format($bill->amount) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Frequency</span>
                    <span class="text-[11px] font-bold text-gray-300">{{ $bill->frequencyLabel() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Age group</span>
                    <span class="text-[11px] font-bold text-indigo-400">{{ $bill->age_group }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] text-gray-500">Credit on pay/miss</span>
                    <span class="text-[11px] font-bold">
                        <span class="text-emerald-400">+{{ $bill->credit_impact_pay ?? 0 }}</span>
                        /
                        <span class="text-red-400">{{ $bill->credit_impact_miss ?? 0 }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-2 flex-wrap mt-1">
                    @if($bill->is_essential)
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-500/15 text-red-400">Essential</span>
                    @endif
                    @if($bill->auto_assign)
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-amber-500/15 text-amber-400">Auto-assign</span>
                    @endif
                    @if($bill->min_chapter)
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-white/5 text-gray-500">from {{ $bill->min_chapter }}</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="border-t border-white/5 flex">
                <a href="{{ route('gameset.bills.edit', $bill) }}"
                   class="flex-1 text-center text-[11px] font-bold text-indigo-400 hover:bg-indigo-500/10 py-2.5 transition-colors">
                    ✏️ Edit
                </a>
                <form method="POST" action="{{ route('gameset.bills.destroy', $bill) }}"
                      onsubmit="return confirm('Delete {{ addslashes($bill->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-[11px] font-bold text-red-400 hover:bg-red-500/10 px-4 py-2.5 transition-colors">
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
    const res = await fetch(`/gameset/bills/${id}/toggle-active`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    });
    const data = await res.json();
    return data.is_active;
}
</script>
</body>
</html>
