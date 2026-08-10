<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Deals — GameSet</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#07060f;font-family:'Figtree',sans-serif;}[x-cloak]{display:none!important;}</style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'deals'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">📈 Investment Deals</span>
        </div>
        <a href="{{ route('gameset.deals.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#059669,#047857);box-shadow:0 4px 14px rgba(5,150,105,.35);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Deal
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-semibold text-emerald-300 border border-emerald-500/30" style="background:rgba(16,185,129,0.1);">
        ✓ {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="rounded-2xl p-5 text-center" style="background:rgba(5,150,105,0.12);border:1px solid rgba(5,150,105,0.25);">
            <div class="text-3xl font-black text-emerald-300">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Total Deals</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);">
            <div class="text-3xl font-black text-emerald-400">{{ $stats['active'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Active</div>
        </div>
    </div>

    <div class="mb-6 p-4 rounded-xl text-sm text-amber-300/80 border border-amber-500/20" style="background:rgba(251,191,36,0.05);">
        💡 <strong>Quick Deals</strong> are time-limited investments that resolve after <code>maturity_ticks</code> game days. Success/failure is determined by probability — teaching players that not all deals win.
    </div>

    @forelse($deals as $deal)
    <div class="rounded-2xl border mb-3 overflow-hidden"
         style="background:linear-gradient(135deg,rgba(15,13,30,0.9),rgba(9,7,20,0.95));border-color:rgba(255,255,255,0.07);"
         x-data="{ deleting: false }">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <div class="flex-shrink-0 text-center" style="width:52px;">
                <div style="height:48px;display:flex;align-items:center;justify-content:center;"><x-icon :name="$deal->icon" class="w-8 h-8" /></div>
                <div class="text-[9px] font-black mt-1 rounded-full px-1.5 py-0.5"
                     style="background:rgba({{ $deal->risk_level >= 4 ? '239,68,68' : ($deal->risk_level >= 3 ? '251,191,36' : '16,185,129') }},0.2);color:{{ $deal->riskColor() }};border:1px solid {{ $deal->riskColor() }}44;">
                    R{{ $deal->risk_level }}
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <span class="font-black text-white text-sm">{{ $deal->title }}</span>
                        @if(!$deal->is_active)
                        <span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.2);">INACTIVE</span>
                        @else
                        <span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.2);">ACTIVE</span>
                        @endif
                        <span class="ml-1 text-[10px] font-bold px-2 py-0.5 rounded-full capitalize" style="background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.2);">{{ $deal->category }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 text-[11px] font-semibold">
                        <span class="text-gray-400">KES {{ number_format($deal->cost) }}</span>
                        <span class="text-emerald-400">+{{ $deal->min_return_pct }}–{{ $deal->max_return_pct }}%</span>
                        <span class="text-amber-400">{{ round($deal->success_probability * 100) }}% win</span>
                        <span class="text-indigo-400">{{ $deal->maturity_ticks }}t</span>
                    </div>
                </div>
                @if($deal->description)
                <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $deal->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button onclick="toggleDealActive({{ $deal->id }}, this)"
                        data-active="{{ $deal->is_active ? '1' : '0' }}"
                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                    <span class="text-sm">{{ $deal->is_active ? '✓' : '○' }}</span>
                </button>
                <a href="{{ route('gameset.deals.edit', $deal) }}"
                   class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/10"
                   style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">✏️</a>
                <button @click="deleting=true"
                        class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-red-500/10"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">🗑️</button>
            </div>
        </div>
        <div x-show="deleting" x-cloak class="border-t border-red-500/20 bg-red-500/5 px-5 py-3 flex items-center justify-between gap-4">
            <span class="text-sm text-red-400 font-semibold">Delete "{{ $deal->title }}"?</span>
            <div class="flex gap-2">
                <button @click="deleting=false" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-gray-400 border border-white/10 hover:bg-white/5">Cancel</button>
                <form method="POST" action="{{ route('gameset.deals.destroy', $deal) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white" style="background:rgba(239,68,68,0.7);">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-gray-500">
        <div class="text-5xl mb-4">📈</div>
        <div class="text-lg font-bold mb-2">No deals yet</div>
        <a href="{{ route('gameset.deals.create') }}" class="text-emerald-400 hover:text-emerald-300 text-sm">Create the first deal →</a>
    </div>
    @endforelse
</div>

<script>
function toggleDealActive(id, btn) {
    fetch(`/gameset/deals/${id}/toggle-active`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(r => r.json()).then(data => {
        btn.dataset.active = data.is_active ? '1' : '0';
        btn.querySelector('span').textContent = data.is_active ? '✓' : '○';
        location.reload();
    });
}
</script>
</body>
</html>
