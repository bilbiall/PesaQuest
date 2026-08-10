<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'New Share' : 'Edit Share' }} — GameSet</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#07060f;font-family:'Figtree',sans-serif;}[x-cloak]{display:none!important;}
    .field-label{font-size:12px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;display:block;}
    .field-input{width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px 14px;color:#fff;font-size:14px;}
    .field-input:focus{outline:none;border-color:rgba(8,145,178,0.5);}
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
        <a href="{{ route('gameset.shares.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Shares
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold text-sm">{{ $mode === 'create' ? '+ New Share' : 'Edit: '.$share->name }}</span>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    @if($errors->any())
    <div class="mb-6 px-4 py-3 rounded-xl text-sm text-red-300 border border-red-500/30" style="background:rgba(239,68,68,0.1);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    @if($mode === 'edit')
    <div class="mb-6 p-4 rounded-xl text-sm text-cyan-300/80 border border-cyan-500/20" style="background:rgba(8,145,178,0.05);">
        Current live price: <strong>KES {{ number_format($share->current_price, 2) }}</strong>
        (previous step: {{ $share->previous_price !== null ? 'KES '.number_format($share->previous_price, 2) : '—' }}).
        Changing "Current Price" here jumps the price immediately — the next automatic step still applies drift/volatility from wherever you set it.
    </div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('gameset.shares.store') : route('gameset.shares.update', $share) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Left column --}}
            <div class="space-y-5">
                <div>
                    <label class="field-label">Icon (name)
                        <x-help-tip text="Icon shown next to the share in the Equity Square Shares tab." example="trend-up" />
                    </label>
                    <input type="text" name="icon" value="{{ old('icon', $share->icon ?? 'trend-up') }}" class="field-input" maxlength="30">
                </div>
                <div>
                    <label class="field-label">Company Name *
                        <x-help-tip text="Full name shown to players — real or fictional, this is a simulated market." example="Safaricom PLC" />
                    </label>
                    <input type="text" name="name" value="{{ old('name', $share->name ?? '') }}" required class="field-input" placeholder="e.g. Equity Group">
                </div>
                <div>
                    <label class="field-label">Ticker Symbol *
                        <x-help-tip text="Short unique code shown next to the price — like a real stock ticker." example="SCOM" />
                    </label>
                    <input type="text" name="symbol" value="{{ old('symbol', $share->symbol ?? '') }}" required maxlength="12" class="field-input" placeholder="e.g. EQTY" style="text-transform:uppercase;">
                </div>
                <div>
                    <label class="field-label">Sector
                        <x-help-tip text="Free-text label shown as a small tag — purely flavor, doesn't change price behavior." example="Banking" />
                    </label>
                    <input type="text" name="sector" value="{{ old('sector', $share->sector ?? '') }}" class="field-input" placeholder="e.g. Telecoms">
                </div>
                <div>
                    <label class="field-label">Sort Order
                        <x-help-tip text="Controls display order in the Shares tab — lower numbers show first." example="0" />
                    </label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $share->sort_order ?? 0) }}" min="0" class="field-input">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $share->is_active ?? true) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500/20">
                        <span class="text-sm font-semibold text-gray-300">Listed (active)
                            <x-help-tip text="Inactive shares disappear from the Shares tab and can't be bought — but existing holders can still sell out of them." />
                        </span>
                    </label>
                </div>
            </div>

            {{-- Right column --}}
            <div class="space-y-5">
                <div>
                    <label class="field-label">Current Price (KES) *
                        <x-help-tip text="The live price players buy/sell at right now. On create, this is also used as the starting 'previous price' so the first arrow shows flat." example="18.50" />
                    </label>
                    <input type="number" name="current_price" value="{{ old('current_price', $share->current_price ?? 20) }}" required min="0.01" step="0.01" class="field-input">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Min Price (floor)
                            <x-help-tip text="The price can never random-walk below this — prevents a share drifting to zero over time." example="6.50" />
                        </label>
                        <input type="number" name="min_price" value="{{ old('min_price', $share->min_price ?? 5) }}" required min="0.01" step="0.01" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Max Price (ceiling)
                            <x-help-tip text="The price can never random-walk above this — keeps a fast-rising share from becoming unrealistic." example="55" />
                        </label>
                        <input type="number" name="max_price" value="{{ old('max_price', $share->max_price ?? 60) }}" required min="0.01" step="0.01" class="field-input">
                    </div>
                </div>
                <div>
                    <label class="field-label">Volatility (0–1)
                        <x-help-tip text="How big each price step can swing, as a fraction of price. 0.03 ≈ a calm blue-chip, 0.08+ ≈ a wild, risky mover." example="0.03" />
                    </label>
                    <input type="number" name="volatility" value="{{ old('volatility', $share->volatility ?? 0.03) }}" required min="0" max="1" step="0.005" class="field-input">
                    <p class="text-xs text-gray-500 mt-1">There's also a ~6% chance of a bigger "news" jolt (2–4× normal) on any step.</p>
                </div>
                <div>
                    <label class="field-label">Drift (-0.05 to 0.05)
                        <x-help-tip text="A tiny per-step directional bias — positive nudges the price up over time on average, negative nudges it down, zero is a pure random walk." example="0.0004" />
                    </label>
                    <input type="number" name="drift" value="{{ old('drift', $share->drift ?? 0) }}" required min="-0.05" max="0.05" step="0.0001" class="field-input">
                    <p class="text-xs text-gray-500 mt-1">0.0004 ≈ a gentle long-run uptrend. Keep this small — volatility should still dominate day to day.</p>
                </div>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit"
                    class="px-8 py-3 rounded-xl font-black text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#0891b2,#0e7490);box-shadow:0 4px 14px rgba(8,145,178,.35);">
                {{ $mode === 'create' ? 'List Share' : 'Save Changes' }}
            </button>
            <a href="{{ route('gameset.shares.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-400 border border-white/10 hover:bg-white/5">
                Cancel
            </a>
        </div>
    </form>
</div>
</body>
</html>
