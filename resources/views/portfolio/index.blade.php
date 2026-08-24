<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Investments &amp; Assets — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/icons.js') }}"></script>
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }

        .pf-card {
            border: 1px solid rgba(255,255,255,0.07);
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .pf-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.14);
            box-shadow: 0 14px 40px rgba(0,0,0,0.45);
        }
        @keyframes popIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .pf-appear { animation: popIn .45s cubic-bezier(0.34,1.4,0.64,1) both; }
        .pf-appear:nth-child(2){animation-delay:.06s} .pf-appear:nth-child(3){animation-delay:.12s}
        .pf-appear:nth-child(4){animation-delay:.18s} .pf-appear:nth-child(5){animation-delay:.24s}
        .pf-appear:nth-child(6){animation-delay:.3s}
        .pf-tag {
            display:inline-flex; align-items:center; gap:4px;
            font-size:10px; font-weight:800; letter-spacing:.04em;
            padding:2px 8px; border-radius:99px;
        }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- ── Nav ── --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500 hidden sm:block inline-flex items-center gap-1"><x-icon name="bar-chart" class="w-3 h-3" /> Investments &amp; Assets</span>
            <a href="{{ route('marketplace') }}"
               class="text-xs text-cyan-400 hover:text-cyan-300 border border-cyan-500/30 hover:border-cyan-500/60 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1">
                <x-icon name="cart" class="w-3.5 h-3.5" /> Marketplace
            </a>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    {{-- ── Header ── --}}
    <div class="mb-4">
        <h1 class="text-xl sm:text-2xl font-black mb-1 inline-flex items-center gap-1.5"><x-icon name="bar-chart" class="w-5 h-5" /> Your Portfolio</h1>
        <p class="text-gray-400 text-xs">Assets, investments, savings and debts — your whole money picture on day {{ number_format($tick) }}.</p>
    </div>

    {{-- ── Summary bar ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 mb-5">
        <div class="rounded-xl p-3" style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.18);">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1.5">Asset Value</p>
            <p class="text-lg font-black text-emerald-400">Ksh {{ number_format($totalValue) }}</p>
        </div>
        <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1.5">Invested</p>
            <p class="text-lg font-black text-gray-200">Ksh {{ number_format($totalInvested) }}</p>
        </div>
        <div class="rounded-xl p-3" style="background:{{ $unrealisedPL >= 0 ? 'rgba(16,185,129,0.07)' : 'rgba(248,113,113,0.07)' }};border:1px solid {{ $unrealisedPL >= 0 ? 'rgba(16,185,129,0.18)' : 'rgba(248,113,113,0.18)' }};">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1.5">Unrealised P/L</p>
            <p class="text-lg font-black {{ $unrealisedPL >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $unrealisedPL >= 0 ? '+' : '−' }}Ksh {{ number_format(abs($unrealisedPL)) }}
            </p>
        </div>
        <div class="rounded-xl p-3" style="background:rgba(6,182,212,0.07);border:1px solid rgba(6,182,212,0.18);">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1.5">Income /mo</p>
            <p class="text-lg font-black text-cyan-400">+Ksh {{ number_format($monthlyIncome) }}</p>
        </div>
        <div class="rounded-xl p-3" style="background:{{ $monthlyCost > 0 ? 'rgba(245,158,11,0.07)' : 'rgba(255,255,255,0.03)' }};border:1px solid {{ $monthlyCost > 0 ? 'rgba(245,158,11,0.18)' : 'rgba(255,255,255,0.08)' }};">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1.5">Costs /mo</p>
            <p class="text-lg font-black {{ $monthlyCost > 0 ? 'text-amber-400' : 'text-gray-300' }}">−Ksh {{ number_format($monthlyCost) }}</p>
        </div>
        <div class="rounded-xl p-3" style="background:rgba(139,92,246,0.07);border:1px solid rgba(139,92,246,0.18);">
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1.5">Holdings</p>
            <p class="text-lg font-black text-purple-400">{{ $portfolioCount }}</p>
        </div>
    </div>

    {{-- ── "What do these numbers mean?" — hidden until asked for, then
         explains each figure using the player's own holdings, not a
         generic dictionary definition. ── --}}
    <div class="mb-5" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="text-xs font-bold text-gray-400 hover:text-white inline-flex items-center gap-1.5 transition-colors">
            <span>❓ What do these numbers mean?</span>
            <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-cloak x-transition class="mt-3 rounded-xl p-4 space-y-2.5" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
            <p class="text-xs text-gray-300 leading-relaxed"><b class="text-emerald-400">Asset Value</b> — {{ $assetInsights['asset_value'] }}</p>
            <p class="text-xs text-gray-300 leading-relaxed"><b class="text-gray-200">Invested</b> — {{ $assetInsights['invested'] }}</p>
            <p class="text-xs text-gray-300 leading-relaxed"><b class="{{ $unrealisedPL >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Unrealised P/L</b> — {{ $assetInsights['unrealised_pl'] }}</p>
            <p class="text-xs text-gray-300 leading-relaxed"><b class="text-cyan-400">Income /mo</b> — {{ $assetInsights['income'] }}</p>
            <p class="text-xs text-gray-300 leading-relaxed"><b class="text-amber-400">Costs /mo</b> — {{ $assetInsights['costs'] }}</p>
            <p class="text-xs text-gray-300 leading-relaxed"><b class="text-purple-400">Holdings</b> — {{ $assetInsights['holdings'] }}</p>
        </div>
    </div>

    {{-- ── Net worth sparkline ── --}}
    <div class="pf-card rounded-2xl p-4 sm:p-5 mb-5" style="background:linear-gradient(160deg,rgba(12,18,38,0.95),rgba(20,16,52,0.85));">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-black text-white uppercase tracking-widest inline-flex items-center gap-1"><x-icon name="trend-up" class="w-3.5 h-3.5" /> Asset Value Over Time</h2>
                <p class="text-xs text-gray-500 mt-0.5">Combined value of everything you own, day by day</p>
            </div>
            @if(count($netWorthSeries) >= 2)
            @php
                $first = $netWorthSeries[0]['value'];
                $last  = end($netWorthSeries)['value'];
                $trendUp = $last >= $first;
                $trendPct = $first > 0 ? round(($last - $first) / $first * 100, 1) : 0;
            @endphp
            <span class="pf-tag {{ $trendUp ? 'text-emerald-400 border border-emerald-500/30 bg-emerald-500/10' : 'text-red-400 border border-red-500/30 bg-red-500/10' }}">
                {{ $trendUp ? '▲' : '▼' }} {{ abs($trendPct) }}%
            </span>
            @endif
        </div>

        @if(count($netWorthSeries) >= 2)
        @php
            $vals   = array_column($netWorthSeries, 'value');
            $minV   = min($vals); $maxV = max($vals);
            $rangeV = max($maxV - $minV, 1);
            $W = 600; $H = 140; $pad = 8;
            $n = count($netWorthSeries);
            $pts = [];
            foreach ($netWorthSeries as $i => $pt) {
                $x = round($i / ($n - 1) * ($W - 2 * $pad) + $pad, 1);
                $y = round($H - $pad - (($pt['value'] - $minV) / $rangeV) * ($H - 2 * $pad), 1);
                $pts[] = "$x,$y";
            }
            $polyline = implode(' ', $pts);
            $areaPoly = "$pad," . ($H - $pad) . ' ' . $polyline . ' ' . ($W - $pad) . ',' . ($H - $pad);
            $lineColor = $trendUp ? '#34d399' : '#f87171';
        @endphp
        <div class="rounded-2xl overflow-hidden" style="background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.05);">
            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full" style="height:150px;" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="nwGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="{{ $lineColor }}" stop-opacity="0.28"/>
                        <stop offset="100%" stop-color="{{ $lineColor }}" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <polygon points="{{ $areaPoly }}" fill="url(#nwGrad)"/>
                <polyline points="{{ $polyline }}" fill="none" stroke="{{ $lineColor }}"
                          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="flex justify-between mt-2 text-[11px] text-gray-500">
            <span>Day {{ number_format($netWorthSeries[0]['tick']) }} · Ksh {{ number_format($first) }}</span>
            <span class="font-bold {{ $trendUp ? 'text-emerald-400' : 'text-red-400' }}">Day {{ number_format(end($netWorthSeries)['tick']) }} · Ksh {{ number_format($last) }}</span>
        </div>
        @else
        <div class="rounded-2xl p-8 text-center" style="background:rgba(0,0,0,0.25);border:1px dashed rgba(255,255,255,0.1);">
            <div class="text-3xl mb-2">🌱</div>
            <p class="text-sm font-bold text-gray-300">Not enough history yet</p>
            <p class="text-xs text-gray-500 mt-1">Own assets for a few game days and your value chart will grow here.</p>
        </div>
        @endif
    </div>

    {{-- ── Active investments (cyan) — deals + shares ── --}}
    <div class="mb-10" x-data="pfShareSell()">
        <div class="flex items-center gap-3 mb-4">
            <x-icon name="trend-up" class="w-5 h-5 text-cyan-300" />
            <h2 class="text-sm font-black text-cyan-300 uppercase tracking-widest">Active Investments</h2>
            <div class="flex-1 h-px" style="background:rgba(6,182,212,0.15);"></div>
            @if($activeDeals->isNotEmpty())
            <span class="text-xs text-gray-500 font-semibold">Ksh {{ number_format($totalDealCapital) }} in deals</span>
            @endif
            @if($myShares->isNotEmpty())
            <span class="text-xs text-gray-500 font-semibold">Ksh {{ number_format($totalSharesValue) }} in shares</span>
            @endif
        </div>

        @if($activeDeals->isEmpty() && $myShares->isEmpty())
        <div class="rounded-xl p-4 text-center" style="background:rgba(6,182,212,0.04);border:1px dashed rgba(6,182,212,0.2);">
            <p class="text-sm text-gray-400 mb-2">No money working for you right now.</p>
            <a href="{{ route('marketplace') }}" class="text-xs font-black text-cyan-400 hover:text-cyan-300">Find a deal or trade shares at Equity Square →</a>
        </div>
        @else

        @if($activeDeals->isNotEmpty())
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            @foreach($activeDeals as $pd)
            @php
                $deal      = $pd->deal;
                $daysLeft  = max(0, ($pd->resolve_at_tick ?? 0) - $tick);
                $minReturn = (int) round($pd->amount_invested * (($deal->min_return_pct ?? 0) / 100));
                $maxReturn = (int) round($pd->amount_invested * (($deal->max_return_pct ?? 0) / 100));
            @endphp
            <div class="pf-card pf-appear rounded-2xl p-5" style="background:linear-gradient(160deg,rgba(8,28,40,0.95),rgba(12,18,38,0.9));border-color:rgba(6,182,212,0.2);">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <x-icon :name="$deal->icon ?? 'briefcase'" class="w-7 h-7" />
                        <div>
                            <p class="font-black text-white text-sm leading-tight">{{ $deal->title ?? 'Investment Deal' }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">Ksh {{ number_format($pd->amount_invested) }} invested</p>
                        </div>
                    </div>
                    @if($deal && $deal->risk_level)
                    <span class="pf-tag border flex-shrink-0"
                          style="color:{{ $deal->riskColor() }};border-color:{{ $deal->riskColor() }}44;background:{{ $deal->riskColor() }}11;">
                        {{ $deal->riskLabel() }} risk
                    </span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl p-3" style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.15);">
                        <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider mb-1">Expected Return</p>
                        <p class="text-sm font-black text-cyan-300">+{{ $deal->min_return_pct ?? 0 }}% – {{ $deal->max_return_pct ?? 0 }}%</p>
                        <p class="text-[10px] text-gray-500">Ksh {{ number_format($minReturn) }} – {{ number_format($maxReturn) }}</p>
                    </div>
                    <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                        <p class="text-[10px] text-gray-500 font-black uppercase tracking-wider mb-1">Matures In</p>
                        <p class="text-sm font-black {{ $daysLeft <= 3 ? 'text-amber-400' : 'text-white' }}">
                            {{ $daysLeft }} game day{{ $daysLeft === 1 ? '' : 's' }}
                        </p>
                        <p class="text-[10px] text-gray-500">{{ $daysLeft === 0 ? 'resolving soon' : 'hold tight' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($myShares->isNotEmpty())
        <div class="flex items-center gap-2 mb-3">
            <span class="text-xs font-black text-cyan-300/80 uppercase tracking-wider inline-flex items-center gap-1"><x-icon name="bar-chart" class="w-3 h-3" /> Shares</span>
            <span class="text-[11px] font-bold {{ $sharesUnrealisedPL >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $sharesUnrealisedPL >= 0 ? '+' : '−' }}Ksh {{ number_format(abs($sharesUnrealisedPL)) }} unrealised
            </span>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($myShares as $h)
            @php [$riskName, $riskDesc] = array_pad(explode(' — ', $h->share->riskLabel(), 2), 2, ''); @endphp
            <div id="pf-share-{{ $h->share_id }}" class="pf-card pf-appear rounded-2xl p-4" style="background:linear-gradient(160deg,rgba(8,28,40,0.95),rgba(12,18,38,0.9));border-color:rgba(6,182,212,0.2);">
                <div class="flex items-start justify-between gap-2.5 mb-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($h->share->image_url)
                            <img src="{{ $h->share->image_url }}" alt="" class="w-11 h-11 rounded-xl object-cover flex-shrink-0" style="box-shadow:0 3px 10px rgba(0,0,0,.25);">
                        @else
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                                <x-icon :name="$h->share->icon" class="w-5 h-5" />
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-black text-white text-[13px] leading-tight truncate">{{ $h->share->name }} <span class="text-gray-500 font-bold">({{ $h->share->symbol }})</span></p>
                            <p class="text-[10.5px] text-gray-500 mt-0.5">{{ $h->quantity }} shares · avg Ksh {{ number_format($h->avg_cost, 2) }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 rounded-xl px-2.5 py-1.5" style="border:1px solid {{ $h->share->riskColor() }}44;background:{{ $h->share->riskColor() }}11;">
                        <div class="text-[10.5px] font-black" style="color:{{ $h->share->riskColor() }};">{{ $riskName }}</div>
                        <div class="text-[9px] text-gray-500 capitalize">{{ $riskDesc }}</div>
                    </div>
                </div>
                <div class="h-px mb-3" style="background:rgba(255,255,255,0.06);"></div>
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div>
                        <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-1">Current Value</p>
                        <p class="text-[13px] font-black text-cyan-300">Ksh {{ number_format($h->currentValue()) }}</p>
                        <p class="text-[9px] text-gray-500">Ksh {{ number_format($h->share->current_price, 2) }}/share</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-1">Gain / Loss</p>
                        <p class="text-[13px] font-black {{ $h->gainLoss() >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $h->gainLoss() >= 0 ? '+' : '−' }}Ksh {{ number_format(abs($h->gainLoss())) }}
                        </p>
                        <p class="text-[9px] text-gray-500">{{ $h->gainLossPct() >= 0 ? '+' : '' }}{{ $h->gainLossPct() }}%</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-1">Qty</p>
                        <p class="text-[13px] font-black text-white">{{ $h->quantity }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" x-model.number="qty[{{ $h->share_id }}]" min="1" max="{{ $h->quantity }}"
                           placeholder="Qty (up to {{ $h->quantity }})"
                           class="flex-1 text-xs rounded-lg px-3 py-2 min-w-0" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:#fff;">
                    <button @click="sell({{ $h->share_id }}, {{ $h->quantity }})" :disabled="loading"
                            class="text-xs font-black px-4 py-2 rounded-lg flex-shrink-0" style="background:rgba(239,68,68,0.18);color:#f87171;border:1px solid rgba(239,68,68,0.3);">
                        Sell →
                    </button>
                </div>
                <p x-show="msg[{{ $h->share_id }}]" x-cloak x-text="msg[{{ $h->share_id }}]" class="text-[11px] font-semibold mt-2" :class="ok[{{ $h->share_id }}] ? 'text-emerald-400' : 'text-red-400'"></p>
            </div>
            @endforeach
        </div>
        @endif

        @endif
    </div>

    {{-- ── Owned assets by category ── --}}
    <div class="mb-10" x-data="pfSell()">
        <div class="flex items-center gap-3 mb-4">
            <x-icon name="house" class="w-5 h-5" />
            <h2 class="text-sm font-black text-white uppercase tracking-widest">Owned Assets</h2>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.06);"></div>
            <span class="text-xs text-gray-500 font-semibold">{{ $portfolioCount }} owned</span>
        </div>

        @if($playerAssets->isEmpty())
        <div class="rounded-2xl p-8 text-center" style="background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.1);">
            <div class="text-4xl mb-2">🏦</div>
            <p class="text-sm font-bold text-gray-300 mb-1">You don't own anything yet</p>
            <p class="text-xs text-gray-500 mb-4">Every empire begins with one asset.</p>
            <a href="{{ route('marketplace') }}" class="inline-block px-6 py-2.5 rounded-xl text-sm font-black text-white"
               style="background:linear-gradient(135deg,#7c3aed,#2563eb);">Browse Marketplace →</a>
        </div>
        @else
        @php
            $catMeta = [
                'property'     => ['icon' => '🏠', 'label' => 'Property'],
                'vehicle'      => ['icon' => '🚗', 'label' => 'Vehicles'],
                'business'     => ['icon' => '💼', 'label' => 'Business'],
                'investment'   => ['icon' => '📈', 'label' => 'Investments'],
                'fixed_income' => ['icon' => '🏛️', 'label' => 'Fixed Income'],
                'gadget'       => ['icon' => '📱', 'label' => 'Gadgets'],
                'other'        => ['icon' => '📦', 'label' => 'Other'],
            ];
        @endphp
        @foreach($assetsByCategory as $cat => $catAssets)
        <div class="mb-7">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-base">{{ $catMeta[$cat]['icon'] ?? '📦' }}</span>
                <h3 class="text-xs font-black text-gray-300 uppercase tracking-widest">{{ $catMeta[$cat]['label'] ?? ucfirst($cat) }}</h3>
                <span class="text-[11px] text-gray-600 font-semibold">· {{ $catAssets->count() }}</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($catAssets as $pa)
                @php
                    $apprPct    = $pa->purchase_price > 0 ? round(($pa->current_value - $pa->purchase_price) / $pa->purchase_price * 100, 1) : 0;
                    $condition  = $pa->condition ?? 100;
                    $maturesIn  = $pa->ticksUntilMaturity($tick);
                    $isLocked   = $pa->isLockedForSale($tick);
                    $exitPenalty= $pa->asset->early_exit_penalty_pct ?? 0;
                @endphp
                <div class="pf-card pf-appear rounded-2xl overflow-hidden" id="pf-asset-{{ $pa->id }}"
                     style="background:linear-gradient(160deg,rgba(12,18,38,0.95),rgba(20,16,52,0.85));border-color:rgba(139,92,246,0.2);">
                    @if($pa->asset->image_url)
                    <div class="relative h-28 overflow-hidden">
                        <img src="{{ $pa->asset->image_url }}" class="absolute inset-0 w-full h-full object-cover" style="opacity:0.55;" loading="lazy" alt="" onerror="this.parentElement.style.display='none'"/>
                        <div class="absolute inset-0" style="background:linear-gradient(to bottom, rgba(7,6,15,0.1), rgba(7,6,15,0.85));"></div>
                        <span class="absolute bottom-2 left-3" style="filter:drop-shadow(0 0 10px rgba(0,0,0,0.6));"><x-icon :name="$pa->asset->icon" class="w-7 h-7 text-white" /></span>
                        @if($pa->quantity > 1)
                        <span class="absolute top-2 right-2 bg-black/60 backdrop-blur text-white text-[11px] font-black px-2 py-0.5 rounded-full">×{{ $pa->quantity }}</span>
                        @endif
                    </div>
                    @endif
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                @if(!$pa->asset->image_url)
                                <x-icon :name="$pa->asset->icon" class="w-6 h-6 flex-shrink-0" />
                                @endif
                                <p class="font-black text-white text-sm leading-tight truncate">{{ $pa->asset->name }}</p>
                            </div>
                            <span class="pf-tag flex-shrink-0 {{ $apprPct >= 0 ? 'text-emerald-400 border border-emerald-500/30 bg-emerald-500/10' : 'text-red-400 border border-red-500/30 bg-red-500/10' }}">
                                {{ $apprPct >= 0 ? '▲' : '▼' }} {{ abs($apprPct) }}%
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-3">
                            @if($maturesIn !== null)
                            <span class="pf-tag {{ $isLocked ? 'text-amber-400 border border-amber-500/30 bg-amber-500/10' : 'text-cyan-400 border border-cyan-500/30 bg-cyan-500/10' }}">
                                {{ $isLocked ? '🔒 Locked' : '⏳' }} matures in {{ $maturesIn }} game day{{ $maturesIn == 1 ? '' : 's' }}
                            </span>
                            @endif
                            <span class="pf-tag" style="color:{{ $pa->asset->appreciationColor() }};border:1px solid {{ $pa->asset->appreciationColor() }}4D;background:{{ $pa->asset->appreciationColor() }}1A;"
                                  title="{{ $pa->asset->appreciationNote() }}">
                                {{ $pa->asset->appreciationIcon() }} {{ $pa->asset->appreciationLabel() }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="rounded-xl p-2.5" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                                <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-0.5">Bought At</p>
                                <p class="text-xs font-black text-gray-300">Ksh {{ number_format($pa->purchase_price) }}</p>
                            </div>
                            <div class="rounded-xl p-2.5" style="background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15);">
                                <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-0.5">Worth Now</p>
                                <p class="text-xs font-black text-emerald-400">Ksh {{ number_format($pa->current_value) }}</p>
                            </div>
                        </div>

                        {{-- Income / cost row --}}
                        <div class="flex items-center justify-between text-[11px] mb-3">
                            <span class="text-gray-500">
                                @if(($pa->asset->monthly_income ?? 0) > 0)
                                <span class="text-emerald-400 font-bold">+Ksh {{ number_format($pa->asset->monthly_income * $pa->quantity) }}</span>
                                @else
                                <span class="text-gray-600">No income</span>
                                @endif
                                @if(($pa->asset->monthly_cost ?? 0) > 0)
                                <span class="text-red-400 font-bold"> · −Ksh {{ number_format($pa->asset->monthly_cost * $pa->quantity) }}</span>
                                @endif
                                <span class="text-gray-600">/mo</span>
                            </span>
                        </div>

                        {{-- Condition bar --}}
                        <div class="mb-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[9px] text-gray-500 font-black uppercase tracking-wider">Condition</span>
                                <span class="text-[10px] font-bold" style="color:{{ $pa->conditionColor() }};">{{ $pa->conditionLabel() }} · {{ $condition }}%</span>
                            </div>
                            <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width:{{ min(100, max(0, $condition)) }}%;background:{{ $pa->conditionColor() }};"></div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('marketplace') }}"
                               class="flex-1 py-2 rounded-xl text-[11px] font-black text-center transition-all hover:scale-[1.02]"
                               style="background:rgba(6,182,212,0.08);border:1px solid rgba(6,182,212,0.2);color:#67e8f9;">
                                Buy More
                            </a>
                            @if($isLocked)
                            <span class="flex-1 py-2 rounded-xl text-[11px] font-black text-center cursor-not-allowed"
                                  style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);color:#6b7280;"
                                  title="Locked until it matures in {{ $maturesIn }} game day(s)">
                                🔒 Locked
                            </span>
                            @else
                            <button
                                @click="openSell({{ json_encode(['id' => $pa->id, 'name' => $pa->asset->name, 'icon' => $pa->asset->icon, 'value' => $pa->current_value, 'cost' => $pa->purchase_price]) }})"
                                class="flex-1 py-2 rounded-xl text-[11px] font-black transition-all hover:scale-[1.02]"
                                style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:#fca5a5;">
                                @if($exitPenalty > 0 && $maturesIn !== null)
                                    Sell early · -{{ $exitPenalty }}% penalty
                                @else
                                    Sell · 5% fee
                                @endif
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif

        {{-- ── Sell modal ── --}}
        <div x-show="selling" x-cloak
             class="fixed inset-0 flex items-center justify-center p-4"
             style="z-index:9990;background:rgba(0,0,0,0.85);overflow-y:auto;overscroll-behavior:contain;" @click.self="selling = null">
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
                        <div class="p-4 sm:p-6 border-b border-white/5">
                            <div class="flex items-center gap-2.5 sm:gap-3">
                                <div class="w-7 h-7 sm:w-9 sm:h-9 flex-shrink-0" x-html="pqIcon(selling.icon, 'w-7 h-7 sm:w-9 sm:h-9')"></div>
                                <div class="min-w-0">
                                    <p class="font-black text-white text-sm sm:text-base truncate" x-text="selling.name"></p>
                                    <p class="text-[.7rem] sm:text-xs text-gray-400 leading-snug">Confirm sale — proceeds go to your balance</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="rounded-2xl p-3 sm:p-4 mb-3 sm:mb-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                                <div class="space-y-2 text-xs sm:text-sm">
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-400">Current value</span>
                                        <span class="font-bold text-white truncate" x-text="'Ksh ' + selling.value.toLocaleString()"></span>
                                    </div>
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-400">Platform fee (5%)</span>
                                        <span class="font-bold text-red-400 truncate" x-text="'− Ksh ' + Math.round(selling.value * 0.05).toLocaleString()"></span>
                                    </div>
                                    <div class="border-t border-white/10 pt-2 mt-2 flex justify-between gap-2 font-black">
                                        <span class="text-white">You receive</span>
                                        <span class="text-emerald-400 truncate" x-text="'Ksh ' + Math.round(selling.value * 0.95).toLocaleString()"></span>
                                    </div>
                                </div>
                            </div>
                            <div x-show="sellMsg" class="rounded-xl px-3 py-2 sm:px-4 text-[.7rem] sm:text-xs font-bold text-center mb-3"
                                 :class="sellOk ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' : 'text-red-400 bg-red-500/10 border border-red-500/20'"
                                 x-text="sellMsg"></div>
                            <div class="flex gap-2 sm:gap-3">
                                <button @click="selling = null; sellMsg = '';"
                                        class="flex-1 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-bold text-gray-400 hover:text-white transition-colors"
                                        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                                    Keep it
                                </button>
                                <button @click="confirmSell()" :disabled="isSelling"
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
    </div>

    {{-- ── Savings + Loans (2-col on desktop) ── --}}
    <div class="grid lg:grid-cols-2 gap-4 mb-6 items-start">

        {{-- Savings schemes (emerald) --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <x-icon name="bank" class="w-5 h-5 text-emerald-300" />
                <h2 class="text-sm font-black text-emerald-300 uppercase tracking-widest">Savings Goals</h2>
                <div class="flex-1 h-px" style="background:rgba(16,185,129,0.15);"></div>
            </div>

            @if($savingsSchemes->isEmpty())
            <div class="rounded-xl p-4 text-center" style="background:rgba(16,185,129,0.04);border:1px dashed rgba(16,185,129,0.2);">
                <p class="text-sm text-gray-400 mb-2">No savings goals yet.</p>
                <a href="{{ route('savings.index') }}" class="text-xs font-black text-emerald-400 hover:text-emerald-300">Start one at the bank →</a>
            </div>
            @else
            <div class="space-y-3">
                @foreach($savingsSchemes as $scheme)
                @php $pct = $scheme->progressPercent(); @endphp
                <div class="pf-card rounded-2xl p-4" style="background:linear-gradient(160deg,rgba(8,32,24,0.9),rgba(12,18,38,0.9));border-color:rgba(16,185,129,0.18);">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-2xl flex-shrink-0">{{ $scheme->emoji ?? '💰' }}</span>
                            <p class="font-black text-white text-sm truncate">{{ $scheme->name }}</p>
                        </div>
                        <span class="text-xs font-black text-emerald-400 flex-shrink-0">{{ $pct }}%</span>
                    </div>
                    <div class="h-2 bg-white/5 rounded-full overflow-hidden mb-2">
                        <div class="h-full rounded-full transition-all duration-700"
                             style="width:{{ $pct }}%;background:linear-gradient(90deg,#059669,#34d399);"></div>
                    </div>
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-gray-400">Ksh {{ number_format($scheme->current_amount) }} <span class="text-gray-600">of {{ number_format($scheme->target_amount) }}</span></span>
                        <span class="text-gray-500 font-semibold">{{ $scheme->projection ?? '—' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Loans tracker (amber/red) --}}
        <div>
            <div class="flex items-center gap-3 mb-4">
                <x-icon name="card" class="w-5 h-5 text-amber-300" />
                <h2 class="text-sm font-black text-amber-300 uppercase tracking-widest">Loans Tracker</h2>
                <div class="flex-1 h-px" style="background:rgba(245,158,11,0.15);"></div>
                @if($loans->isNotEmpty())
                <span class="text-xs text-red-400 font-bold">Ksh {{ number_format($totalDebt) }} owed</span>
                @endif
            </div>

            @if($loans->isEmpty())
            <div class="rounded-xl p-4 text-center" style="background:rgba(245,158,11,0.04);border:1px dashed rgba(245,158,11,0.2);">
                <div class="text-2xl mb-1">🎉</div>
                <p class="text-sm text-gray-400">Debt-free! No active loans.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($loans as $loan)
                @php $dueIn = max(0, ($loan->next_payment_tick ?? 0) - $tick); @endphp
                <div class="pf-card rounded-2xl p-4" style="background:linear-gradient(160deg,rgba(40,24,8,0.9),rgba(12,18,38,0.9));border-color:rgba(245,158,11,0.2);">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">{{ $loan->loanProduct->icon ?? '💳' }}</span>
                            <div>
                                <p class="font-black text-white text-sm leading-tight">{{ $loan->loanProduct->name ?? 'Loan' }}</p>
                                <p class="text-[11px] text-gray-500">{{ $loan->annual_interest_rate }}% APR</p>
                            </div>
                        </div>
                        <span class="pf-tag flex-shrink-0 {{ $dueIn <= 2 ? 'text-red-400 border border-red-500/30 bg-red-500/10' : 'text-amber-400 border border-amber-500/30 bg-amber-500/10' }}">
                            Due in {{ $dueIn }} day{{ $dueIn === 1 ? '' : 's' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-xl p-2.5" style="background:rgba(248,113,113,0.06);border:1px solid rgba(248,113,113,0.15);">
                            <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-0.5">Outstanding</p>
                            <p class="text-xs font-black text-red-400">Ksh {{ number_format($loan->outstanding_balance) }}</p>
                        </div>
                        <div class="rounded-xl p-2.5" style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.15);">
                            <p class="text-[9px] text-gray-500 font-black uppercase tracking-wider mb-0.5">Next Payment</p>
                            <p class="text-xs font-black text-amber-400">Ksh {{ number_format($loan->payment_amount) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="mt-3 flex items-start gap-2 rounded-xl px-4 py-3 text-[11px] text-gray-400 leading-relaxed"
                 style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">
                <span class="text-sm mt-0.5">💡</span>
                <span>On-time payments build credit <span class="text-emerald-400 font-bold">(+5)</span>, missed payments hurt <span class="text-red-400 font-bold">(−20)</span>.</span>
            </div>
        </div>
    </div>

    {{-- ── Completed deals history ── --}}
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <x-icon name="folder" class="w-5 h-5" />
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-widest">Deal History</h2>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.06);"></div>
        </div>

        @if($completedDeals->isEmpty())
        <p class="text-sm text-gray-600 text-center py-4">No completed deals yet — your investment wins and lessons will show here.</p>
        @else
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($completedDeals as $pd)
            @php
                $won    = $pd->status === 'success';
                $retPct = $pd->amount_invested > 0 ? round($pd->profit_loss / $pd->amount_invested * 100, 1) : 0;
            @endphp
            <div class="rounded-2xl p-4 {{ $won ? '' : 'opacity-80' }}"
                 style="background:rgba(255,255,255,0.02);border:1px solid {{ $won ? 'rgba(16,185,129,0.15)' : 'rgba(248,113,113,0.15)' }};">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <x-icon :name="$pd->deal->icon ?? 'briefcase'" class="w-6 h-6 {{ $won ? '' : 'grayscale' }}" />
                        <div class="min-w-0">
                            <p class="font-bold text-gray-200 text-sm leading-tight truncate">{{ $pd->deal->title ?? 'Deal' }}</p>
                            <p class="text-[10px] text-gray-600">Ksh {{ number_format($pd->amount_invested) }} in · {{ $won ? 'Paid off' : 'Went south' }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-black {{ $pd->profit_loss >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $pd->profit_loss >= 0 ? '+' : '−' }}Ksh {{ number_format(abs($pd->profit_loss)) }}
                        </p>
                        <p class="text-[10px] font-bold {{ $retPct >= 0 ? 'text-emerald-500' : 'text-red-500' }}">{{ $retPct >= 0 ? '+' : '' }}{{ $retPct }}%</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Shares trade history — realised sells with profit/loss ── --}}
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <x-icon name="bar-chart" class="w-5 h-5" />
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-widest">Shares History</h2>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.06);"></div>
        </div>

        @if($shareTradeHistory->isEmpty())
        <p class="text-sm text-gray-600 text-center py-4">No sold shares yet — profits (and losses) from your sales will show here.</p>
        @else
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($shareTradeHistory as $trade)
            <div class="rounded-2xl p-4"
                 style="background:rgba(255,255,255,0.02);border:1px solid {{ $trade->profit_loss >= 0 ? 'rgba(16,185,129,0.15)' : 'rgba(248,113,113,0.15)' }};">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <x-icon :name="$trade->share->icon ?? 'bar-chart'" class="w-6 h-6" />
                        <div class="min-w-0">
                            <p class="font-bold text-gray-200 text-sm leading-tight truncate">{{ $trade->share->name ?? $trade->share->symbol ?? 'Share' }}</p>
                            <p class="text-[10px] text-gray-600">{{ $trade->quantity }} sold @ Ksh {{ number_format($trade->price, 2) }} · {{ $trade->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-black {{ $trade->profit_loss >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $trade->profit_loss >= 0 ? '+' : '−' }}Ksh {{ number_format(abs($trade->profit_loss)) }}
                        </p>
                        <p class="text-[10px] text-gray-500">Ksh {{ number_format($trade->total) }} total</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

<script>
function pfShareSell() {
    return {
        qty: {},
        loading: false,
        msg: {},
        ok: {},

        async sell(shareId, maxQty) {
            const quantity = parseInt(this.qty[shareId]) || maxQty;
            if (quantity < 1 || this.loading) return;
            this.loading = true;
            this.msg[shareId] = '';
            try {
                const res = await fetch('/shares/sell', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ share_id: shareId, quantity }),
                });
                const data = await res.json();
                if (data.error) {
                    this.ok[shareId]  = false;
                    this.msg[shareId] = data.error;
                } else {
                    this.ok[shareId]  = true;
                    this.msg[shareId] = data.message;
                    setTimeout(() => window.location.reload(), 1400);
                }
            } catch (e) {
                this.ok[shareId]  = false;
                this.msg[shareId] = 'Network error. Please retry.';
            }
            this.loading = false;
        }
    };
}

function pfSell() {
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
                    const row = document.getElementById(`pf-asset-${this.selling.id}`);
                    if (row) { row.style.opacity = '0'; row.style.transform = 'scale(0.95)'; row.style.transition = 'all .3s ease'; }
                    setTimeout(() => { this.selling = null; window.location.reload(); }, 1500);
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

<x-mobile-bottom-nav active="portfolio" />
</body>
</html>
