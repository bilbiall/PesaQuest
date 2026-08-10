<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Marketplace Assets — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        .cat-vehicle    { background:linear-gradient(135deg,#92400e,#1c1917); }
        .cat-property   { background:linear-gradient(135deg,#064e3b,#0f172a); }
        .cat-business   { background:linear-gradient(135deg,#3730a3,#1e1b4b); }
        .cat-investment { background:linear-gradient(135deg,#1e3a8a,#0f172a); }
        .cat-gadget     { background:linear-gradient(135deg,#831843,#1a1025); }
        @keyframes popIn { from{opacity:0;transform:translateY(12px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
        .card-in { animation:popIn .35s cubic-bezier(.34,1.56,.64,1) both; }
        .card-in:nth-child(1){animation-delay:.03s} .card-in:nth-child(2){animation-delay:.06s}
        .card-in:nth-child(3){animation-delay:.09s} .card-in:nth-child(4){animation-delay:.12s}
        .card-in:nth-child(5){animation-delay:.15s} .card-in:nth-child(6){animation-delay:.18s}
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'assets'])

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">🛒 Marketplace Assets</span>
        </div>
        <a href="{{ route('gameset.assets.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.4);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Asset
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 flex items-center gap-3 text-sm font-bold text-emerald-300"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span>{{ session('success') }}
    </div>
    @endif

    {{-- Hero stats --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black text-white mb-2">Marketplace Assets</h1>
        <p class="text-gray-400 text-sm mb-6">Manage the items players can buy, sell, and build wealth with</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @php
                $statCards = [
                    ['label'=>'Total',       'val'=>$stats['total'],       'color'=>'text-white',          'bg'=>'rgba(255,255,255,.05)',       'border'=>'rgba(255,255,255,.1)'],
                    ['label'=>'Active',      'val'=>$stats['active'],      'color'=>'text-emerald-400',    'bg'=>'rgba(16,185,129,.08)',        'border'=>'rgba(16,185,129,.2)'],
                    ['label'=>'Vehicles',    'val'=>$stats['vehicle'],     'color'=>'text-amber-400',      'bg'=>'rgba(245,158,11,.08)',        'border'=>'rgba(245,158,11,.2)'],
                    ['label'=>'Property',    'val'=>$stats['property'],    'color'=>'text-emerald-400',    'bg'=>'rgba(16,185,129,.08)',        'border'=>'rgba(16,185,129,.2)'],
                    ['label'=>'Business',    'val'=>$stats['business'],    'color'=>'text-violet-400',     'bg'=>'rgba(139,92,246,.08)',        'border'=>'rgba(139,92,246,.2)'],
                    ['label'=>'Investments', 'val'=>$stats['investment'],  'color'=>'text-blue-400',       'bg'=>'rgba(96,165,250,.08)',        'border'=>'rgba(96,165,250,.2)'],
                    ['label'=>'Gadgets',     'val'=>$stats['gadget'],      'color'=>'text-pink-400',       'bg'=>'rgba(244,114,182,.08)',       'border'=>'rgba(244,114,182,.2)'],
                ];
            @endphp
            @foreach($statCards as $s)
            <div class="rounded-2xl p-4 text-center" style="background:{{ $s['bg'] }};border:1px solid {{ $s['border'] }};">
                <p class="text-xl font-black {{ $s['color'] }}">{{ $s['val'] }}</p>
                <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <form method="GET" action="{{ route('gameset.assets.index') }}" class="flex gap-2 flex-1">
            <input type="text" name="q" value="{{ $search }}"
                   placeholder="Search assets..."
                   class="flex-1 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none focus:ring-2 focus:ring-indigo-500/50"
                   style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
            <select name="category"
                    class="rounded-xl px-3 py-2.5 text-sm text-white outline-none"
                    style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);"
                    onchange="this.form.submit()">
                <option value="all"        {{ $category==='all'        ? 'selected':'' }}>All Categories</option>
                <option value="vehicle"    {{ $category==='vehicle'    ? 'selected':'' }}>🚗 Vehicles</option>
                <option value="property"   {{ $category==='property'   ? 'selected':'' }}>🏠 Property</option>
                <option value="business"   {{ $category==='business'   ? 'selected':'' }}>💼 Business</option>
                <option value="investment" {{ $category==='investment' ? 'selected':'' }}>📈 Investments</option>
                <option value="gadget"     {{ $category==='gadget'     ? 'selected':'' }}>📱 Gadgets</option>
            </select>
            <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all"
                    style="background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.3);">Search</button>
        </form>
    </div>

    {{-- Asset grid --}}
    @if($assets->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-4">🔍</div>
        <p class="font-bold">No assets found</p>
        <a href="{{ route('gameset.assets.create') }}" class="text-indigo-400 text-sm mt-2 block hover:text-indigo-300">Create the first one →</a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" x-data="assetManager()">

        @foreach($assets as $asset)
        @php
            $accentMap = ['vehicle'=>'#f59e0b','property'=>'#10b981','business'=>'#8b5cf6','investment'=>'#60a5fa','gadget'=>'#f472b6'];
            $accent = $accentMap[$asset->category] ?? '#9ca3af';
        @endphp
        <div class="card-in rounded-2xl overflow-hidden flex flex-col" id="asset-card-{{ $asset->id }}"
             style="background:linear-gradient(160deg,rgba(12,18,38,.95),rgba(20,16,52,.85));border:1px solid {{ $asset->is_active ? 'rgba(255,255,255,.08)' : 'rgba(255,255,255,.03)' }};{{ $asset->is_active ? '' : 'opacity:0.55;' }}">

            {{-- Image panel --}}
            <div class="cat-{{ $asset->category }} relative h-32 overflow-hidden flex items-center justify-center">
                @if($asset->image_url)
                <img src="{{ $asset->image_url }}" class="absolute inset-0 w-full h-full object-cover" style="opacity:0.4;mix-blend-mode:overlay;" loading="lazy" alt="" onerror="this.style.display='none'"/>
                @endif
                <span class="relative z-10" style="filter:drop-shadow(0 0 10px rgba(255,255,255,.2));"><x-icon :name="$asset->icon" class="w-10 h-10" /></span>
                {{-- Image indicator --}}
                @if($asset->image_url)
                <div class="absolute top-2 right-2 text-[9px] font-black px-1.5 py-0.5 rounded-full"
                     style="background:rgba(0,0,0,.6);color:{{ $accent }};">IMG</div>
                @endif
                {{-- Status toggle --}}
                <button @click="toggleActive({{ $asset->id }}, $el)"
                        class="absolute top-2 left-2 text-[9px] font-black px-2 py-0.5 rounded-full transition-all"
                        style="background:rgba(0,0,0,.6);color:{{ $asset->is_active ? '#34d399' : '#6b7280' }};"
                        title="{{ $asset->is_active ? 'Active — click to disable' : 'Inactive — click to enable' }}">
                    {{ $asset->is_active ? '● ON' : '○ OFF' }}
                </button>
                <div class="absolute bottom-2 left-2 flex gap-1">
                    @for($i=1;$i<=5;$i++)<div class="w-1.5 h-1.5 rounded-full {{ $i<=$asset->tier ? '' : 'opacity-20' }}" style="{{ $i<=$asset->tier ? 'background:'.$accent : 'background:rgba(255,255,255,.2)' }}"></div>@endfor
                </div>
                <div class="absolute bottom-2 right-2 text-[9px] text-gray-300 font-semibold px-1.5 py-0.5 rounded-full" style="background:rgba(0,0,0,.5);">
                    {{ strtoupper($asset->category) }}
                </div>
            </div>

            {{-- Content --}}
            <div class="p-4 flex flex-col flex-1">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <p class="font-black text-white text-sm leading-tight">{{ $asset->name }}</p>
                        @if($asset->brand)
                        <p class="text-[10px] text-gray-500">{{ $asset->brand }}</p>
                        @endif
                    </div>
                    <span class="text-xs font-black text-white shrink-0"
                          style="color:{{ $accent }};">Ksh {{ number_format($asset->base_price) }}</span>
                </div>

                <div class="grid grid-cols-3 gap-1.5 mb-3 mt-1">
                    <div class="rounded-lg p-1.5 text-center" style="background:rgba(255,255,255,.03);">
                        <p class="text-[9px] text-gray-600 uppercase">Income</p>
                        <p class="text-xs font-bold text-emerald-400">+{{ number_format($asset->monthly_income) }}</p>
                    </div>
                    <div class="rounded-lg p-1.5 text-center" style="background:rgba(255,255,255,.03);">
                        <p class="text-[9px] text-gray-600 uppercase">Cost</p>
                        <p class="text-xs font-bold text-red-400">-{{ number_format($asset->monthly_cost) }}</p>
                    </div>
                    <div class="rounded-lg p-1.5 text-center" style="background:rgba(255,255,255,.03);">
                        <p class="text-[9px] text-gray-600 uppercase">Growth</p>
                        <p class="text-xs font-bold {{ $asset->appreciation_rate >= 0 ? 'text-cyan-400' : 'text-orange-400' }}">{{ $asset->appreciation_rate > 0 ? '+' : '' }}{{ $asset->appreciation_rate }}%</p>
                    </div>
                </div>

                <div class="flex-1"></div>

                <div class="flex gap-2 mt-3">
                    <a href="{{ route('gameset.assets.edit', $asset) }}"
                       class="flex-1 py-2 rounded-lg text-xs font-black text-center transition-all hover:scale-[1.02]"
                       style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;">
                        ✏️ Edit
                    </a>
                    <form method="POST" action="{{ route('gameset.assets.destroy', $asset) }}"
                          onsubmit="return confirm('Delete {{ addslashes($asset->name) }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="py-2 px-3 rounded-lg text-xs font-black transition-all hover:scale-[1.02]"
                                style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#fca5a5;">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
function assetManager() {
    return {
        async toggleActive(id, btn) {
            const card = document.getElementById('asset-card-' + id);
            try {
                const res = await fetch(`/gameset/assets/${id}/toggle-active`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                const isOn = data.is_active;
                btn.style.color = isOn ? '#34d399' : '#6b7280';
                btn.textContent = isOn ? '● ON' : '○ OFF';
                card.style.opacity = isOn ? '1' : '0.55';
                card.style.borderColor = isOn ? 'rgba(255,255,255,.08)' : 'rgba(255,255,255,.03)';
            } catch(e) { console.error(e); }
        }
    };
}
</script>

</body>
</html>
