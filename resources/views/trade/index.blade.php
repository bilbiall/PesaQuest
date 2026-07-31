<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PesaQuest — Trade Market</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; }
        [x-cloak] { display:none !important; }
        .bg-orb { position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0; }
        .bg-orb-1 { width:500px;height:500px;top:-150px;left:-100px;background:rgba(16,185,129,0.10); }
        .bg-orb-2 { width:400px;height:400px;bottom:-100px;right:-80px;background:rgba(99,102,241,0.08); }
        .page-content { position:relative;z-index:10; }
        .hero-gradient { background:linear-gradient(135deg,#34d399,#10b981,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
        .glass { background:linear-gradient(145deg,rgba(255,255,255,0.03),rgba(255,255,255,0.01));border:1px solid rgba(255,255,255,0.07); }
        .listing-card { background:linear-gradient(145deg,rgba(255,255,255,0.03),rgba(255,255,255,0.01));border:1px solid rgba(255,255,255,0.07);transition:all 0.3s; }
        .listing-card:hover { transform:translateY(-2px);border-color:rgba(16,185,129,0.3);box-shadow:0 8px 30px rgba(16,185,129,0.08); }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased" x-data="tradePage()">
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>

    <div class="page-content max-w-4xl mx-auto px-4 py-10">

        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Portfolio
            </a>
            <a href="{{ route('marketplace') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">Game Market →</a>
        </div>

        <div class="text-center mb-10">
            <div class="text-6xl mb-3">🤝</div>
            <h1 class="hero-gradient text-3xl sm:text-4xl font-black mb-2">Player Trade Market</h1>
            <p class="text-gray-400 text-sm max-w-md mx-auto">Buy assets from other players at agreed prices. Real market dynamics — negotiate value, not just price.</p>
        </div>

        {{-- Toast --}}
        <div x-show="toast" x-cloak x-transition
             class="fixed top-6 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-2xl text-sm font-bold shadow-2xl"
             :class="toastOk ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
             x-text="toast"></div>

        <div class="grid lg:grid-cols-3 gap-6">

            {{-- ── LEFT: My Listings + List an asset ── --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- My listings --}}
                @if($myListings->isNotEmpty())
                <div class="glass rounded-3xl p-5">
                    <h3 class="font-black mb-4 flex items-center gap-2"><span>📦</span> My Listings</h3>
                    <div class="space-y-3">
                        @foreach($myListings as $ml)
                        <div class="rounded-2xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg">{{ $ml->playerAsset->asset->icon ?? '📦' }}</span>
                                <span class="text-xs font-bold text-white flex-1 truncate">{{ $ml->playerAsset->asset->name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-black text-emerald-400">Ksh {{ number_format($ml->asking_price) }}</span>
                                <button onclick="cancelListing({{ $ml->id }}, this)"
                                        class="text-[10px] font-bold text-red-400 hover:text-red-300 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Edu tip --}}
                <div class="rounded-3xl p-5" style="background:linear-gradient(135deg,rgba(99,102,241,0.08),rgba(139,92,246,0.04));border:1px solid rgba(99,102,241,0.2);">
                    <div class="text-2xl mb-2">💡</div>
                    <h4 class="font-black text-sm mb-1 text-indigo-300">Trading 101</h4>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        In real markets, assets are worth what someone will pay. Price too high and it won't sell; price too low and you lose value. Check your asset's current market value before listing.
                    </p>
                </div>
            </div>

            {{-- ── RIGHT: Browse Listings ── --}}
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-black text-lg">Available Assets</h3>
                    <span class="text-xs text-gray-500">{{ $listings->total() }} listing(s)</span>
                </div>

                @forelse($listings as $listing)
                <div class="listing-card rounded-3xl p-5 mb-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
                             style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);">
                            {{ $listing->playerAsset->asset->icon ?? '📦' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-black text-white text-sm">{{ $listing->playerAsset->asset->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Sold by <span class="text-gray-300">{{ $listing->seller->name }}</span></div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="font-black text-lg text-emerald-400">Ksh {{ number_format($listing->asking_price) }}</div>
                                    <div class="text-[10px] text-gray-600">asking price</div>
                                </div>
                            </div>

                            {{-- Asset stats --}}
                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(255,255,255,0.05);color:#9ca3af;">
                                    📊 Market Value: Ksh {{ number_format($listing->playerAsset->current_value) }}
                                </span>
                                @if($listing->playerAsset->asset->monthly_income > 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(16,185,129,0.1);color:#34d399;">
                                    +Ksh {{ number_format($listing->playerAsset->asset->monthly_income) }}/mo income
                                </span>
                                @endif
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(255,255,255,0.04);color:#6b7280;">
                                    {{ $listing->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <button onclick="buyListing({{ $listing->id }}, '{{ $listing->playerAsset->asset->name }}', {{ $listing->asking_price }}, this)"
                                    class="mt-3 w-full py-2.5 rounded-xl text-xs font-black transition-all"
                                    style="background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(5,150,105,0.1));border:1px solid rgba(16,185,129,0.3);color:#34d399;">
                                Buy for Ksh {{ number_format($listing->asking_price) }}
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-16 text-gray-500">
                    <div class="text-5xl mb-3">🏪</div>
                    <p class="font-bold">No listings yet</p>
                    <p class="text-xs mt-1">Be the first to list an asset from your portfolio!</p>
                </div>
                @endforelse

                {{ $listings->links() }}
            </div>
        </div>

        {{-- CTA to list from portfolio --}}
        <div class="mt-8 text-center glass rounded-3xl p-6">
            <div class="text-3xl mb-2">📤</div>
            <h3 class="font-black mb-1">Want to sell an asset?</h3>
            <p class="text-xs text-gray-400 mb-4">Go to your portfolio, click on any asset, and choose "List for Trade".</p>
            <a href="{{ route('portfolio') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold px-6 py-3 rounded-2xl hover:shadow-emerald-500/30 transition-all hover:-translate-y-0.5">
                Go to Portfolio →
            </a>
        </div>
    </div>

    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function tradePage() {
        return { toast: '', toastOk: true };
    }

    function showToast(msg, ok = true) {
        const comp = Alpine.evaluate(document.querySelector('[x-data]'), '$data');
        if (comp) { comp.toast = msg; comp.toastOk = ok; setTimeout(() => comp.toast = '', 3500); }
    }

    async function buyListing(id, name, price, btn) {
        if (!confirm(`Buy "${name}" for Ksh ${price.toLocaleString()}?`)) return;
        btn.disabled = true;
        btn.textContent = 'Processing…';
        try {
            const res = await fetch(`/trade/${id}/buy`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message ?? 'Purchase failed');
            btn.closest('.listing-card')?.remove();
            showToast('✅ ' + data.message);
        } catch (e) {
            btn.disabled = false;
            btn.textContent = `Buy for Ksh ${price.toLocaleString()}`;
            showToast('❌ ' + e.message, false);
        }
    }

    async function cancelListing(id, btn) {
        if (!confirm('Cancel this listing?')) return;
        btn.disabled = true;
        try {
            const res = await fetch(`/trade/${id}/cancel`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message ?? 'Cancel failed');
            btn.closest('.rounded-2xl')?.remove();
            showToast('Listing cancelled.');
        } catch (e) {
            btn.disabled = false;
            showToast('❌ ' + e.message, false);
        }
    }
    </script>
</body>
</html>
