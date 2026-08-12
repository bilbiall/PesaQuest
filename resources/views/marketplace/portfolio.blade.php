<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Portfolio — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/icons.js') }}"></script>
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }

        /* ── category gradients ── */
        .cat-vehicle    { background: linear-gradient(135deg,#1a3a6b 0%,#0f172a 55%,#1e1b4b 100%); }
        .cat-property   { background: linear-gradient(135deg,#14532d 0%,#0f172a 55%,#1c1917 100%); }
        .cat-business   { background: linear-gradient(135deg,#7c2d12 0%,#1c1917 55%,#1e1b4b 100%); }
        .cat-investment { background: linear-gradient(135deg,#164e63 0%,#0f172a 55%,#064e3b 100%); }
        .cat-gadget     { background: linear-gradient(135deg,#4c1d95 0%,#0f172a 55%,#1e1b4b 100%); }

        /* ── particle float animations ── */
        @keyframes drift1 { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(6px,-8px) scale(1.1)} 66%{transform:translate(-4px,5px) scale(0.9)} }
        @keyframes drift2 { 0%,100%{transform:translate(0,0) rotate(0deg)} 50%{transform:translate(-8px,-6px) rotate(180deg)} }
        @keyframes drift3 { 0%,100%{transform:translate(0,0) scale(0.9)} 40%{transform:translate(5px,8px) scale(1.15)} 80%{transform:translate(-6px,3px) scale(0.95)} }
        @keyframes iconbob { 0%,100%{transform:translateY(0) scale(1)} 50%{transform:translateY(-5px) scale(1.04)} }
        @keyframes popIn   { from{opacity:0;transform:translateY(20px) scale(0.96)} to{opacity:1;transform:translateY(0) scale(1)} }
        @keyframes shimmer { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        @keyframes glowpulse { 0%,100%{opacity:.5} 50%{opacity:1} }
        @keyframes countup { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

        .particle { position:absolute; border-radius:50%; opacity:0.35; }
        .p1 { width:36px;height:36px;top:18%;left:12%;background:rgba(255,255,255,0.12);animation:drift1 4.5s ease-in-out infinite; }
        .p2 { width:22px;height:22px;top:58%;right:16%;background:rgba(255,255,255,0.08);animation:drift2 3.5s ease-in-out infinite; animation-delay:-1.8s; }
        .p3 { width:14px;height:14px;bottom:18%;left:48%;background:rgba(255,255,255,0.15);animation:drift3 5s ease-in-out infinite; animation-delay:-3.2s; }
        .p4 { width:10px;height:10px;top:35%;right:35%;background:rgba(255,255,255,0.1);animation:drift1 6s ease-in-out infinite; animation-delay:-2s; }

        .iconbob { animation: iconbob 3s ease-in-out infinite; display:inline-block; }

        .card-appear { animation: popIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both; }
        .card-appear:nth-child(1){animation-delay:.05s} .card-appear:nth-child(2){animation-delay:.1s}
        .card-appear:nth-child(3){animation-delay:.15s} .card-appear:nth-child(4){animation-delay:.2s}
        .card-appear:nth-child(5){animation-delay:.25s} .card-appear:nth-child(6){animation-delay:.3s}

        .asset-card-port {
            border: 1px solid rgba(255,255,255,0.07);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .asset-card-port:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 48px rgba(0,0,0,0.5);
            border-color: rgba(255,255,255,0.14);
        }

        .shimmer-text {
            background: linear-gradient(90deg, #a78bfa, #38bdf8, #34d399, #a78bfa);
            background-size: 300% 300%;
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 4s ease infinite;
        }

        .sparkline-wrap { position:relative; }
        .sparkline-wrap::before {
            content:''; position:absolute; inset:0;
            background: linear-gradient(to right, rgba(7,6,15,0) 70%, rgba(7,6,15,0.6));
            pointer-events:none; z-index:1;
        }

        .earn-tag {
            display:inline-flex; align-items:center; gap:4px;
            font-size:10px; font-weight:700; letter-spacing:.04em;
            padding:2px 8px; border-radius:99px;
        }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- ── Nav ── --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500 hidden sm:block">💼 Portfolio</span>
            <a href="{{ route('marketplace') }}"
               class="text-xs text-cyan-400 hover:text-cyan-300 border border-cyan-500/30 hover:border-cyan-500/60 px-3 py-1.5 rounded-lg transition-colors">
                🛒 Marketplace
            </a>
        </div>
    </div>
</nav>

{{-- ── Hero ── --}}
<div class="relative overflow-hidden border-b border-white/5 py-10"
     style="background:linear-gradient(160deg,rgba(139,92,246,0.07) 0%,rgba(6,182,212,0.04) 100%);">
    <div class="absolute top-0 right-0 w-96 h-96 rounded-full opacity-5" style="background:radial-gradient(circle,#a78bfa,transparent 70%);transform:translate(30%,-30%);animation:glowpulse 5s ease infinite;"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full opacity-5" style="background:radial-gradient(circle,#38bdf8,transparent 70%);transform:translate(-30%,30%);animation:glowpulse 5s ease infinite;animation-delay:-2.5s;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-baseline gap-3 mb-2">
            <h1 class="text-3xl sm:text-4xl font-black shimmer-text">💼 My Portfolio</h1>
        </div>
        <p class="text-gray-400 text-sm mb-8">Everything you own — and how it's working for you</p>

        @if($playerAssets->isEmpty())
        <div class="rounded-2xl p-8 text-center max-w-md" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.07);">
            <div class="text-5xl mb-3">🏦</div>
            <p class="font-bold text-gray-300 mb-1">Your portfolio is empty</p>
            <p class="text-sm text-gray-500 mb-4">Start building wealth — every empire begins with one asset.</p>
            <a href="{{ route('marketplace') }}" class="inline-block px-6 py-2.5 rounded-xl text-sm font-black text-white"
               style="background:linear-gradient(135deg,#7c3aed,#2563eb);">
                Browse Marketplace →
            </a>
        </div>
        @else

        @php
            $totalGL = $totalValue - $totalCost;
            $glPct   = $totalCost > 0 ? round(($totalGL / $totalCost) * 100, 1) : 0;
            $portfolioYield = $totalValue > 0 ? round(($monthlyIncome * 12 / $totalValue) * 100, 1) : 0;
            $healthPct = $monthlyGross > 0 ? round(($monthlyCashFlow / $monthlyGross) * 100) : 0;
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Asset Value --}}
            <div class="rounded-2xl p-4" style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.18);">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2">Total Asset Value</p>
                <p class="text-2xl font-black text-emerald-400">Ksh {{ number_format($totalValue) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $playerAssets->count() }} asset{{ $playerAssets->count() !== 1 ? 's' : '' }} owned</p>
            </div>

            {{-- Unrealised P&L --}}
            <div class="rounded-2xl p-4" style="background:{{ $totalGL >= 0 ? 'rgba(16,185,129,0.07)' : 'rgba(248,113,113,0.07)' }};border:1px solid {{ $totalGL >= 0 ? 'rgba(16,185,129,0.18)' : 'rgba(248,113,113,0.18)' }};">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2">Unrealised P&L</p>
                <p class="text-2xl font-black {{ $totalGL >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $totalGL >= 0 ? '+' : '' }}Ksh {{ number_format($totalGL) }}
                </p>
                <p class="text-xs {{ $totalGL >= 0 ? 'text-emerald-500' : 'text-red-500' }} mt-0.5">
                    {{ $totalGL >= 0 ? '▲' : '▼' }} {{ abs($glPct) }}% vs cost
                </p>
            </div>

            {{-- Monthly Cash Flow --}}
            <div class="rounded-2xl p-4" style="background:{{ $monthlyCashFlow >= 0 ? 'rgba(6,182,212,0.07)' : 'rgba(248,113,113,0.07)' }};border:1px solid {{ $monthlyCashFlow >= 0 ? 'rgba(6,182,212,0.18)' : 'rgba(248,113,113,0.18)' }};">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2">Net Cash Flow</p>
                <p class="text-2xl font-black {{ $monthlyCashFlow >= 0 ? 'text-cyan-400' : 'text-red-400' }}">
                    {{ $monthlyCashFlow >= 0 ? '+' : '' }}Ksh {{ number_format($monthlyCashFlow) }}/mo
                </p>
                <p class="text-xs text-gray-500 mt-0.5">In: {{ number_format($monthlyIncome) }} · Out: {{ number_format($monthlyCost) }}</p>
            </div>

            {{-- Portfolio Yield --}}
            <div class="rounded-2xl p-4" style="background:rgba(168,85,247,0.07);border:1px solid rgba(168,85,247,0.18);">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2">Annual Yield</p>
                <p class="text-2xl font-black text-purple-400">{{ $portfolioYield }}%</p>
                <p class="text-xs text-gray-500 mt-0.5">Return on asset value</p>
            </div>
        </div>

        {{-- Portfolio health bar --}}
        @if($monthlyGross > 0)
        <div class="mt-5 rounded-2xl p-4" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-black text-gray-300">
                    Portfolio contribution to monthly income
                </p>
                <span class="text-xs font-bold {{ $healthPct >= 20 ? 'text-emerald-400' : ($healthPct >= 5 ? 'text-yellow-400' : 'text-gray-400') }}">
                    {{ $healthPct }}% of salary
                </span>
            </div>
            <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-1000"
                     style="width:{{ min($healthPct, 100) }}%;background:{{ $healthPct >= 20 ? 'linear-gradient(90deg,#10b981,#34d399)' : ($healthPct >= 5 ? 'linear-gradient(90deg,#f59e0b,#fbbf24)' : 'rgba(99,102,241,0.6)') }};">
                </div>
            </div>
            <p class="text-[11px] text-gray-500 mt-1.5">
                @if($healthPct >= 50) 🔥 Your assets are your primary income — true financial freedom territory!
                @elseif($healthPct >= 20) 💡 Strong! Your assets cover 20%+ of your salary. Keep compounding.
                @elseif($healthPct >= 5) 📈 Growing. Reinvest your cash flow to accelerate.
                @else 🌱 Just starting. Every purchase that earns is a step toward passive income.
                @endif
            </p>
        </div>
        @endif

        @endif
    </div>
</div>

{{-- ── Educational banner ── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-8" x-data="{open:false}">
    <button @click="open=!open"
            class="w-full flex items-center justify-between px-5 py-4 rounded-2xl text-left transition-all"
            style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.07);">
        <div class="flex items-center gap-3">
            <span class="text-lg">🏫</span>
            <div>
                <p class="font-black text-white text-sm">How your portfolio makes money</p>
                <p class="text-xs text-gray-500">3 ways assets build wealth — tap to learn</p>
            </div>
        </div>
        <span class="text-gray-400 text-lg transition-transform duration-200" :class="open ? 'rotate-180' : ''">▾</span>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mt-3">
        <div class="grid sm:grid-cols-3 gap-3 pb-1">
            <div class="rounded-2xl p-5" style="background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15);">
                <div class="text-2xl mb-2">📈</div>
                <p class="font-black text-emerald-400 text-sm mb-1">Appreciation</p>
                <p class="text-xs text-gray-400 leading-relaxed">Your asset's market value grows over time. A plot bought for Ksh 500K might be worth Ksh 750K in 2 years — that's Ksh 250K earned without doing anything.</p>
                <div class="mt-3 rounded-xl px-3 py-2 text-xs font-mono text-gray-300" style="background:rgba(0,0,0,0.3);">
                    Buy → Hold → Value rises → Sell higher
                </div>
            </div>
            <div class="rounded-2xl p-5" style="background:rgba(6,182,212,0.05);border:1px solid rgba(6,182,212,0.15);">
                <div class="text-2xl mb-2">💰</div>
                <p class="font-black text-cyan-400 text-sm mb-1">Cash Flow</p>
                <p class="text-xs text-gray-400 leading-relaxed">Monthly income from your asset minus its running costs. A rental property earning Ksh 25K but costing Ksh 8K leaves Ksh 17K in your pocket every month.</p>
                <div class="mt-3 rounded-xl px-3 py-2 text-xs font-mono text-gray-300" style="background:rgba(0,0,0,0.3);">
                    Income − Costs = Net/mo → adds up
                </div>
            </div>
            <div class="rounded-2xl p-5" style="background:rgba(168,85,247,0.05);border:1px solid rgba(168,85,247,0.15);">
                <div class="text-2xl mb-2">🔄</div>
                <p class="font-black text-purple-400 text-sm mb-1">Compounding</p>
                <p class="text-xs text-gray-400 leading-relaxed">Reinvest cash flow into new assets. Ksh 17K/mo × 12 months = Ksh 204K — enough to buy another investment. More assets = more income = more assets.</p>
                <div class="mt-3 rounded-xl px-3 py-2 text-xs font-mono text-gray-300" style="background:rgba(0,0,0,0.3);">
                    Cash flow → new asset → more cash flow
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Asset Cards ── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8" x-data="portfolio()">

    @if(!$playerAssets->isEmpty())
    @php
        $byCategory  = $playerAssets->groupBy(fn($pa) => $pa->asset->category);
        $catIcons    = ['vehicle'=>'🚗','property'=>'🏠','business'=>'💼','investment'=>'📈','gadget'=>'📱'];
        $catLabels   = ['vehicle'=>'Vehicles','property'=>'Property','business'=>'Business','investment'=>'Investments','gadget'=>'Gadgets'];
        $catAccents  = ['vehicle'=>'#60a5fa','property'=>'#34d399','business'=>'#fb923c','investment'=>'#22d3ee','gadget'=>'#a78bfa'];
        $catEdu      = [
            'vehicle'    => 'Personal vehicles depreciate ~1.5%/month and cost you fuel + insurance. Commercial vehicles (matatu, boda) earn income. The key: only buy a personal car when the costs are &lt;10% of income.',
            'property'   => 'Real estate typically appreciates 0.3–0.5%/month. Rental income is consistent. Your goal: rental income > mortgage cost = positive cash flow from day one.',
            'business'   => 'Business returns are variable and driven by events. High risk, high reward. Diversify across multiple businesses to smooth out volatility.',
            'investment' => 'T-Bills and MMFs are safe (3–8% annual return). Stocks and REITs are higher risk but historically outperform savings over 5+ years. Mix them.',
            'gadget'     => 'Gadgets depreciate fast and generate no income — they\'re tools, not investments. Buy them from salary, not savings.',
        ];
    @endphp

    @foreach($byCategory as $cat => $catAssets)
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-5">
            <span class="text-xl">{{ $catIcons[$cat] ?? '📦' }}</span>
            <h2 class="text-sm font-black text-white uppercase tracking-widest">{{ $catLabels[$cat] ?? ucfirst($cat) }}</h2>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.06);"></div>
            <span class="text-xs text-gray-500 font-semibold">{{ $catAssets->count() }} owned</span>
        </div>

        {{-- Category insight --}}
        @if(isset($catEdu[$cat]))
        <div class="mb-4 flex items-start gap-3 rounded-xl px-4 py-3 text-xs text-gray-400 leading-relaxed"
             style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">
            <span class="text-base mt-0.5">💡</span>
            <span>{!! $catEdu[$cat] !!}</span>
        </div>
        @endif

        <div class="space-y-4">
            @foreach($catAssets as $pa)
            @php
                $gl       = $pa->gainLoss();
                $glPct    = $pa->gainLossPct();
                $cashFlow = $pa->monthlyCashFlow();
                $prices   = $priceHistories[$pa->id] ?? collect([]);
                $hasChart = $prices->count() >= 2;

                // Compute SVG sparkline
                $sparkPoints = '';
                $sparkUp = true;
                if ($hasChart) {
                    $pts   = $prices->values();
                    $min   = $pts->min(); $max = $pts->max();
                    $range = max($max - $min, 1);
                    $W = 200; $H = 36;
                    $sparkPoints = $pts->map(function($p, $i) use ($pts, $min, $range, $W, $H) {
                        $x = round($i / max($pts->count()-1, 1) * $W, 1);
                        $y = round($H - (($p - $min) / $range * ($H - 6)) - 3, 1);
                        return "$x,$y";
                    })->implode(' ');
                    $sparkUp = $pts->last() >= $pts->first();
                }

                // Annual yield on this asset
                $netMonthly = ($pa->asset->monthly_income - $pa->asset->monthly_cost) * $pa->quantity;
                $assetYield = $pa->current_value > 0 ? round(($netMonthly * 12 / $pa->current_value) * 100, 1) : 0;
                // Payback period (months to recover cost from net income)
                $paybackMo = $netMonthly > 0 ? ceil($pa->purchase_price / $netMonthly) : null;

                // Image for card header (use stored URL or fall back to loremflickr)
                $nm2 = strtolower($pa->asset->name);
                $imgKw2 = match($pa->asset->category) {
                    'vehicle'    => (str_contains($nm2,'boda')||str_contains($nm2,'bajaj')||str_contains($nm2,'tvs')||str_contains($nm2,'apache')) ? 'motorcycle' : (str_contains($nm2,'matatu') ? 'minibus,transport' : (str_contains($nm2,'porsche') ? 'porsche' : (str_contains($nm2,'bmw') ? 'bmw,sedan' : (str_contains($nm2,'prado') ? 'toyota,suv' : 'automobile')))),
                    'property'   => (str_contains($nm2,'plot')||str_contains($nm2,'land')) ? 'land,landscape' : (str_contains($nm2,'stall') ? 'market,stall' : (str_contains($nm2,'bedsitter')||str_contains($nm2,'studio') ? 'apartment,room' : 'house,apartment')),
                    'business'   => str_contains($nm2,'fuel')||str_contains($nm2,'petrol') ? 'petrolstation,fuel' : (str_contains($nm2,'tech')||str_contains($nm2,'startup') ? 'technology,startup' : (str_contains($nm2,'salon') ? 'salon,beauty' : (str_contains($nm2,'cyber') ? 'computer,cafe' : 'market,entrepreneur'))),
                    'investment' => str_contains($nm2,'safaricom')||str_contains($nm2,'nse')||str_contains($nm2,'shares') ? 'stockmarket,trading' : 'finance,money',
                    'gadget'     => str_contains($nm2,'laptop') ? 'laptop,computer' : 'smartphone,mobile',
                    default      => 'finance',
                };
                $cardImgUrl = $pa->asset->image_url ?: ('https://loremflickr.com/800/500/'.$imgKw2.'?lock='.$pa->asset->id);
            @endphp

            <div class="asset-card-port card-appear rounded-2xl overflow-hidden" id="pa-row-{{ $pa->id }}"
                 style="background:linear-gradient(160deg,rgba(12,18,38,0.95),rgba(20,16,52,0.85));">
                <div class="flex flex-col lg:flex-row">

                    {{-- ── Gradient image panel ── --}}
                    <div class="cat-{{ $pa->asset->category }} relative lg:w-52 h-40 lg:h-auto flex items-center justify-center overflow-hidden flex-shrink-0">
                        <img src="{{ $cardImgUrl }}" class="absolute inset-0 w-full h-full object-cover pointer-events-none"
                             style="opacity:0.5;" loading="lazy" alt="" onerror="this.style.display='none'"/>
                        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(to right, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);"></div>
                        <div class="absolute inset-0 opacity-10" style="background-image:repeating-linear-gradient(0deg,transparent,transparent 19px,rgba(255,255,255,0.05) 20px),repeating-linear-gradient(90deg,transparent,transparent 19px,rgba(255,255,255,0.05) 20px);"></div>
                        <div class="particle p1"></div>
                        <div class="particle p2"></div>
                        <div class="particle p3"></div>
                        <div class="particle p4"></div>
                        @if($pa->asset->icon)
                        <span class="iconbob z-10 relative" style="filter:drop-shadow(0 0 14px rgba(255,255,255,0.25));"><x-icon :name="$pa->asset->icon" class="w-16 h-16" /></span>
                        @endif

                        {{-- Quantity badge --}}
                        @if($pa->quantity > 1)
                        <div class="absolute top-3 right-3 bg-black/60 backdrop-blur text-white text-xs font-black px-2 py-0.5 rounded-full">
                            ×{{ $pa->quantity }}
                        </div>
                        @endif

                        {{-- P&L pill overlay --}}
                        <div class="absolute bottom-3 left-0 right-0 flex justify-center">
                            <span class="text-[11px] font-black px-3 py-1 rounded-full backdrop-blur-sm
                                {{ $gl >= 0 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30' }}">
                                {{ $gl >= 0 ? '▲' : '▼' }} {{ abs($glPct) }}%
                            </span>
                        </div>
                    </div>

                    {{-- ── Content panel ── --}}
                    <div class="flex-1 p-5">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <p class="font-black text-white text-lg leading-tight">{{ $pa->asset->name }}</p>
                                @if($pa->asset->brand)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $pa->asset->brand }}</p>
                                @endif
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @if($pa->asset->risk_level)
                                    <span class="earn-tag border"
                                          style="color:{{ $pa->asset->riskColor() }};border-color:{{ $pa->asset->riskColor() }}44;background:{{ $pa->asset->riskColor() }}11;opacity:0.9;">
                                        {{ $pa->asset->riskLabel() }}
                                    </span>
                                    @endif
                                    @if($assetYield > 0)
                                    <span class="earn-tag text-cyan-400 border border-cyan-500/30 bg-cyan-500/10">
                                        {{ $assetYield }}% yield/yr
                                    </span>
                                    @endif
                                    @if($paybackMo)
                                    <span class="earn-tag text-purple-400 border border-purple-500/30 bg-purple-500/10">
                                        Pays back in {{ $paybackMo }}mo
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Sell + Trade buttons (desktop) --}}
                            <div class="hidden lg:flex gap-2 flex-shrink-0">
                                <button
                                    @click="openSell({{ json_encode(['id' => $pa->id, 'name' => $pa->asset->name, 'icon' => $pa->asset->icon, 'value' => $pa->current_value, 'cost' => $pa->purchase_price, 'gl' => $gl]) }})"
                                    class="px-4 py-2 rounded-xl text-xs font-black transition-all hover:scale-[1.04] active:scale-[0.97]"
                                    style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:#fca5a5;">
                                    Sell Asset
                                </button>
                                <button onclick="openListModal({{ $pa->id }}, '{{ addslashes($pa->asset->name) }}', {{ $pa->current_value }})"
                                        class="px-4 py-2 rounded-xl text-xs font-black transition-all hover:scale-[1.04] active:scale-[0.97]"
                                        style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:#6ee7b7;">
                                    🤝 Trade
                                </button>
                            </div>
                        </div>

                        {{-- Financial grid --}}
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                                <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider mb-1">Current Value</p>
                                <p class="text-sm font-black text-white">Ksh {{ number_format($pa->current_value) }}</p>
                                <p class="text-[10px] {{ $gl >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                    {{ $gl >= 0 ? '+' : '' }}Ksh {{ number_format($gl) }}
                                </p>
                            </div>
                            <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                                <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider mb-1">Bought At</p>
                                <p class="text-sm font-black text-gray-300">Ksh {{ number_format($pa->purchase_price) }}</p>
                                <p class="text-[10px] text-gray-600">purchase price</p>
                            </div>
                            <div class="rounded-xl p-3" style="background:{{ $cashFlow >= 0 ? 'rgba(16,185,129,0.05)' : 'rgba(248,113,113,0.05)' }};border:1px solid {{ $cashFlow >= 0 ? 'rgba(16,185,129,0.15)' : 'rgba(248,113,113,0.15)' }};">
                                <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider mb-1">Net / Month</p>
                                <p class="text-sm font-black {{ $cashFlow >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $cashFlow >= 0 ? '+' : '' }}Ksh {{ number_format($cashFlow) }}
                                </p>
                                <p class="text-[10px] text-gray-600">after costs</p>
                            </div>
                        </div>

                        {{-- Earnings breakdown + sparkline row --}}
                        <div class="flex flex-col sm:flex-row gap-3">

                            {{-- Earnings breakdown --}}
                            <div class="flex-1 rounded-xl p-3" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">
                                <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider mb-2">💸 How this earns</p>
                                <div class="space-y-1.5">
                                    @if($pa->asset->monthly_income > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-400">Monthly income</span>
                                        <span class="text-xs font-bold text-emerald-400">+Ksh {{ number_format($pa->asset->monthly_income * $pa->quantity) }}</span>
                                    </div>
                                    @endif
                                    @if($pa->asset->monthly_cost > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-400">Monthly costs</span>
                                        <span class="text-xs font-bold text-red-400">−Ksh {{ number_format($pa->asset->monthly_cost * $pa->quantity) }}</span>
                                    </div>
                                    @endif
                                    @if($pa->asset->appreciation_rate != 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-400">Value change/mo</span>
                                        <span class="text-xs font-bold {{ $pa->asset->appreciation_rate > 0 ? 'text-cyan-400' : 'text-orange-400' }}">
                                            {{ $pa->asset->appreciation_rate > 0 ? '+' : '' }}{{ $pa->asset->appreciation_rate }}%
                                        </span>
                                    </div>
                                    @endif
                                    @if($pa->asset->monthly_income == 0 && $pa->asset->monthly_cost == 0 && $pa->asset->appreciation_rate == 0)
                                    <p class="text-xs text-gray-600 italic">No recurring income — value only</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Sparkline chart --}}
                            @if($hasChart)
                            <div class="sm:w-44 rounded-xl overflow-hidden relative" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">
                                <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider px-3 pt-2.5 mb-1">Value history</p>
                                <div class="sparkline-wrap px-1 pb-2">
                                    <svg viewBox="0 0 200 36" class="w-full" style="height:36px;" preserveAspectRatio="none">
                                        <defs>
                                            <linearGradient id="sg{{ $pa->id }}" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="{{ $sparkUp ? '#10b981' : '#ef4444' }}" stop-opacity="0.3"/>
                                                <stop offset="100%" stop-color="{{ $sparkUp ? '#10b981' : '#ef4444' }}" stop-opacity="0"/>
                                            </linearGradient>
                                        </defs>
                                        <polyline points="{{ $sparkPoints }}"
                                                  fill="none"
                                                  stroke="{{ $sparkUp ? '#10b981' : '#ef4444' }}"
                                                  stroke-width="1.8"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <p class="text-[10px] text-center pb-2 {{ $sparkUp ? 'text-emerald-500' : 'text-red-500' }}">
                                    {{ $sparkUp ? '↑ trending up' : '↓ trending down' }}
                                </p>
                            </div>
                            @endif
                        </div>

                        {{-- Sell button (mobile) --}}
                        <button
                            @click="openSell({{ json_encode(['id' => $pa->id, 'name' => $pa->asset->name, 'icon' => $pa->asset->icon, 'value' => $pa->current_value, 'cost' => $pa->purchase_price, 'gl' => $gl]) }})"
                            class="lg:hidden w-full mt-4 py-2.5 rounded-xl text-xs font-black transition-all hover:scale-[1.02] active:scale-[0.97]"
                            style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:#fca5a5;">
                            Sell Asset · 5% fee
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- ── Sold History ── --}}
    @if(isset($soldAssets) && $soldAssets->count() > 0)
    <div class="mt-4 mb-8">
        <div class="flex items-center gap-3 mb-5">
            <span class="text-xl">🏷️</span>
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-widest">Recent Sales</h2>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.06);"></div>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($soldAssets as $sa)
            @php
                $saGL     = ($sa->sold_price ?? 0) - $sa->purchase_price;
                $saGLPct  = $sa->purchase_price > 0 ? round($saGL / $sa->purchase_price * 100, 1) : 0;
            @endphp
            <div class="rounded-2xl p-4 opacity-70" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);">
                <div class="flex items-center gap-3 mb-3">
                    <div class="opacity-60"><x-icon :name="$sa->asset->icon" class="w-7 h-7" /></div>
                    <div>
                        <p class="font-bold text-gray-300 text-sm leading-tight">{{ $sa->asset->name }}</p>
                        <p class="text-[10px] text-gray-600">Sold</p>
                    </div>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-500">Ksh {{ number_format($sa->purchase_price) }} → Ksh {{ number_format($sa->sold_price ?? 0) }}</span>
                    <span class="font-bold {{ $saGL >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $saGL >= 0 ? '+' : '' }}{{ $saGLPct }}%
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Sell Modal ── --}}
    <div x-show="selling" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
         style="background:rgba(0,0,0,0.85);" @click.self="selling = null">
        <div x-show="selling"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-6"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-sm rounded-3xl overflow-hidden"
             style="background:linear-gradient(160deg,rgba(12,18,38,0.99),rgba(20,16,52,0.97));border:1px solid rgba(248,113,113,0.25);">
            <template x-if="selling">
                <div>
                    {{-- Modal header --}}
                    <div class="p-4 sm:p-6 border-b border-white/5">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <div class="w-7 h-7 sm:w-9 sm:h-9 flex-shrink-0" x-html="pqIcon(selling.icon, 'w-7 h-7 sm:w-9 sm:h-9')"></div>
                            <div class="min-w-0">
                                <p class="font-black text-white text-sm sm:text-base truncate" x-text="selling.name"></p>
                                <p class="text-[.68rem] sm:text-xs text-gray-400">Confirm sale — proceeds go to your balance</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        {{-- P&L preview --}}
                        <div class="rounded-2xl p-3 sm:p-4 mb-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                            <div class="space-y-2 text-xs sm:text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Current value</span>
                                    <span class="font-bold text-white" x-text="'Ksh ' + selling.value.toLocaleString()"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Platform fee (5%)</span>
                                    <span class="font-bold text-red-400" x-text="'− Ksh ' + Math.round(selling.value * 0.05).toLocaleString()"></span>
                                </div>
                                <div class="border-t border-white/10 pt-2 mt-2 flex justify-between font-black">
                                    <span class="text-white">You receive</span>
                                    <span class="text-emerald-400" x-text="'Ksh ' + Math.round(selling.value * 0.95).toLocaleString()"></span>
                                </div>
                            </div>
                        </div>

                        {{-- P&L vs purchase --}}
                        <div class="rounded-xl px-3 py-2.5 sm:px-4 sm:py-3 mb-4 text-[.68rem] sm:text-xs"
                             style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">Vs. what you paid (Ksh <span x-text="selling.cost.toLocaleString()"></span>)</span>
                                <span class="font-bold"
                                      :class="(Math.round(selling.value*0.95) - selling.cost) >= 0 ? 'text-emerald-400' : 'text-red-400'"
                                      x-text="((Math.round(selling.value*0.95)-selling.cost)>=0?'+':'') + 'Ksh ' + Math.abs(Math.round(selling.value*0.95)-selling.cost).toLocaleString()">
                                </span>
                            </div>
                        </div>

                        {{-- Message --}}
                        <div x-show="sellMsg" class="rounded-xl px-3 py-2 sm:px-4 text-[.68rem] sm:text-xs font-bold text-center mb-3"
                             :class="sellOk ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' : 'text-red-400 bg-red-500/10 border border-red-500/20'"
                             x-text="sellMsg"></div>

                        <div class="flex gap-2.5 sm:gap-3">
                            <button @click="selling = null; sellMsg = '';"
                                    class="flex-1 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-bold text-gray-400 hover:text-white transition-colors"
                                    style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                Keep it
                            </button>
                            <button @click="confirmSell()"
                                    :disabled="isSelling"
                                    class="flex-1 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-black transition-all hover:scale-[1.02] active:scale-[0.97] disabled:opacity-50"
                                    style="background:rgba(248,113,113,0.12);border:1px solid rgba(248,113,113,0.3);color:#fca5a5;">
                                <span x-show="!isSelling">Confirm Sale</span>
                                <span x-show="isSelling" x-cloak>Selling…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @endif

    {{-- Empty state if no assets --}}
    @if($playerAssets->isEmpty())
    <div class="text-center py-10 text-gray-500 text-sm">
        No assets yet. <a href="{{ route('marketplace') }}" class="text-cyan-400 hover:text-cyan-300 underline">Start investing →</a>
    </div>
    @endif

</div>

<script>
function portfolio() {
    return {
        selling: null,
        isSelling: false,
        sellMsg: '',
        sellOk: true,

        openSell(asset) {
            this.selling = asset;
            this.sellMsg = '';
            this.isSelling = false;
        },

        async confirmSell() {
            if (!this.selling || this.isSelling) return;
            this.isSelling = true;
            this.sellMsg = '';
            try {
                const res = await fetch(`/portfolio/${this.selling.id}/sell`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (res.ok) {
                    this.sellOk = true;
                    this.sellMsg = data.message;
                    const row = document.getElementById(`pa-row-${this.selling.id}`);
                    if (row) { row.style.opacity = '0'; row.style.transform = 'scale(0.95)'; row.style.transition = 'all .3s ease'; }
                    setTimeout(() => { this.selling = null; window.location.reload(); }, 1600);
                } else {
                    this.sellOk = false;
                    this.sellMsg = data.error || 'Sale failed.';
                    this.isSelling = false;
                }
            } catch (e) {
                this.sellOk = false;
                this.sellMsg = 'Network error. Please retry.';
                this.isSelling = false;
            }
        }
    };
}
</script>

    {{-- ── List-for-Trade Modal ── --}}
    <div id="list-trade-modal"
         style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,0.85);"
         onclick="if(event.target===this)closeListModal()">
        <div class="p-5 sm:p-7" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);max-width:380px;width:calc(100%-2rem);background:linear-gradient(145deg,#1a1830,#12112a);border:1px solid rgba(16,185,129,0.3);border-radius:1.5rem;">
            <h3 class="text-sm sm:text-base" style="font-weight:900;color:white;margin-bottom:0.25rem;">🤝 List for Trade</h3>
            <p id="list-asset-name" style="font-size:0.8rem;color:#9ca3af;margin-bottom:1.25rem;"></p>
            <label style="font-size:0.72rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:0.5rem;">Asking Price (Ksh)</label>
            <div style="position:relative;margin-bottom:0.5rem;">
                <span style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#34d399;font-weight:700;font-size:0.875rem;">Ksh</span>
                <input id="list-price-input" type="number" min="1" placeholder="e.g. 5000"
                       style="width:100%;padding:0.75rem 1rem 0.75rem 3.5rem;border-radius:0.75rem;background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);color:white;font-size:1rem;font-weight:900;outline:none;box-sizing:border-box;">
            </div>
            <p id="list-market-hint" style="font-size:0.72rem;color:#9ca3af;margin-bottom:1.25rem;"></p>
            <div id="list-error" style="display:none;color:#f87171;font-size:0.78rem;margin-bottom:0.75rem;"></div>
            <div style="display:flex;gap:0.75rem;">
                <button onclick="closeListModal()"
                        style="flex:1;padding:0.75rem;border-radius:0.875rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#9ca3af;font-weight:700;cursor:pointer;">
                    Cancel
                </button>
                <button id="list-submit-btn" onclick="submitListing()"
                        style="flex:1;padding:0.75rem;border-radius:0.875rem;background:linear-gradient(135deg,#059669,#10b981);color:white;font-weight:900;border:none;cursor:pointer;">
                    List Asset
                </button>
            </div>
        </div>
    </div>
    <script>
    let _listAssetId = null;
    function openListModal(assetId, name, marketValue) {
        _listAssetId = assetId;
        document.getElementById('list-asset-name').textContent = name;
        document.getElementById('list-market-hint').textContent = 'Market value: Ksh ' + marketValue.toLocaleString();
        document.getElementById('list-price-input').value = marketValue;
        document.getElementById('list-error').style.display = 'none';
        document.getElementById('list-trade-modal').style.display = 'block';
    }
    function closeListModal() {
        document.getElementById('list-trade-modal').style.display = 'none';
        _listAssetId = null;
    }
    async function submitListing() {
        const price = parseInt(document.getElementById('list-price-input').value);
        if (!price || price < 1) { document.getElementById('list-error').textContent = 'Enter a valid price.'; document.getElementById('list-error').style.display = 'block'; return; }
        const btn = document.getElementById('list-submit-btn');
        btn.disabled = true; btn.textContent = 'Listing…';
        try {
            const res = await fetch(`/trade/${_listAssetId}/list`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ asking_price: price }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message ?? 'Failed to list');
            closeListModal();
            alert('✅ Listed! Other players can now buy your asset in the Trade Market.');
        } catch (e) {
            document.getElementById('list-error').textContent = e.message;
            document.getElementById('list-error').style.display = 'block';
            btn.disabled = false; btn.textContent = 'List Asset';
        }
    }
    </script>

@include('components.mama-pesa-chat')
<x-mobile-bottom-nav active="city" />
</body>
</html>
