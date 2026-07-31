<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loan Products — GameSet</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#07060f;font-family:'Figtree',sans-serif;}[x-cloak]{display:none!important;}</style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'loans'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">🏦 Loan Products</span>
        </div>
        <a href="{{ route('gameset.loans.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 4px 14px rgba(29,78,216,.35);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Loan Product
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
        <div class="rounded-2xl p-5 text-center" style="background:rgba(29,78,216,0.12);border:1px solid rgba(29,78,216,0.25);">
            <div class="text-3xl font-black text-blue-300">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Total Products</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);">
            <div class="text-3xl font-black text-emerald-400">{{ $stats['active'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Active</div>
        </div>
    </div>

    <div class="mb-6 p-4 rounded-xl text-sm text-blue-300/80 border border-blue-500/20" style="background:rgba(29,78,216,0.05);">
        🏦 <strong>Credit Score Logic:</strong> Taking a loan = -5 score. On-time payment = +5. Missed payment = -20. Full payoff = +30. Default = -100.
    </div>

    @forelse($products as $product)
    <div class="rounded-2xl border mb-3 overflow-hidden"
         style="background:linear-gradient(135deg,rgba(15,13,30,0.9),rgba(9,7,20,0.95));border-color:rgba(255,255,255,0.07);"
         x-data="{ deleting: false }">
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <div class="text-3xl flex-shrink-0" style="width:40px;text-align:center;">{{ $product->icon }}</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <span class="font-black text-white text-sm">{{ $product->name }}</span>
                        @if(!$product->is_active)
                        <span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.2);">INACTIVE</span>
                        @else
                        <span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(16,185,129,0.15);color:#34d399;border:1px solid rgba(16,185,129,0.2);">ACTIVE</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                        <span class="text-gray-400">KES {{ number_format($product->min_amount) }}–{{ number_format($product->max_amount) }}</span>
                        <span class="text-amber-400">{{ $product->annual_interest_rate }}% p.a.</span>
                        <span class="text-indigo-400">{{ $product->term_ticks }}t term</span>
                        <span class="text-blue-400">Pay every {{ $product->payment_period_ticks }}t</span>
                        <span class="text-gray-500">Min score: {{ $product->min_credit_score }}</span>
                    </div>
                </div>
                @if($product->description)
                <p class="text-xs text-gray-400 mt-1">{{ $product->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button onclick="toggleLoanActive({{ $product->id }}, this)"
                        data-active="{{ $product->is_active ? '1' : '0' }}"
                        class="w-8 h-8 rounded-lg flex items-center justify-center"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                    <span class="text-sm">{{ $product->is_active ? '✓' : '○' }}</span>
                </button>
                <a href="{{ route('gameset.loans.edit', $product) }}"
                   class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/10"
                   style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">✏️</a>
                <button @click="deleting=true"
                        class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-red-500/10"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">🗑️</button>
            </div>
        </div>
        <div x-show="deleting" x-cloak class="border-t border-red-500/20 bg-red-500/5 px-5 py-3 flex items-center justify-between gap-4">
            <span class="text-sm text-red-400 font-semibold">Delete "{{ $product->name }}"?</span>
            <div class="flex gap-2">
                <button @click="deleting=false" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-gray-400 border border-white/10 hover:bg-white/5">Cancel</button>
                <form method="POST" action="{{ route('gameset.loans.destroy', $product) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-white" style="background:rgba(239,68,68,0.7);">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-gray-500">
        <div class="text-5xl mb-4">🏦</div>
        <div class="text-lg font-bold mb-2">No loan products yet</div>
        <a href="{{ route('gameset.loans.create') }}" class="text-blue-400 hover:text-blue-300 text-sm">Create the first loan product →</a>
    </div>
    @endforelse
</div>

<script>
function toggleLoanActive(id, btn) {
    fetch(`/gameset/loans/${id}/toggle-active`, {
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
