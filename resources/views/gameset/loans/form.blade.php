<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'New Loan Product' : 'Edit Loan' }} — GameSet</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#07060f;font-family:'Figtree',sans-serif;}
    .field-label{font-size:12px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;display:block;}
    .field-input{width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px 14px;color:#fff;font-size:14px;}
    .field-input:focus{outline:none;border-color:rgba(29,78,216,0.5);}
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
        <a href="{{ route('gameset.loans.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Loan Products
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold text-sm">{{ $mode === 'create' ? '+ New Loan Product' : 'Edit: '.$product->name }}</span>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    @if($errors->any())
    <div class="mb-6 px-4 py-3 rounded-xl text-sm text-red-300 border border-red-500/30" style="background:rgba(239,68,68,0.1);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <div class="mb-6 p-4 rounded-xl text-sm text-blue-300/80 border border-blue-500/20" style="background:rgba(29,78,216,0.05);">
        💡 <strong>Ticks</strong> = game days. 7 ticks ≈ 1 game week. 30 ticks ≈ 1 game month. Interest compounds every <em>payment_period_ticks</em>.
    </div>

    <form method="POST" action="{{ $mode === 'create' ? route('gameset.loans.store') : route('gameset.loans.update', $product) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-5">
                <div>
                    <label class="field-label">Icon (emoji)
                        <x-help-tip text="Emoji shown next to the loan product on the Bank's loan menu." example="⚡" />
                    </label>
                    <input type="text" name="icon" value="{{ old('icon', $product->icon ?? '🏦') }}" class="field-input" maxlength="8">
                </div>
                <div>
                    <label class="field-label">Loan Name *
                        <x-help-tip text="The loan's name on the Bank menu — name it around what the money is for." example="Hustler Boost Loan" />
                    </label>
                    <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required class="field-input" placeholder="e.g. Student Starter Loan">
                </div>
                <div>
                    <label class="field-label">Description
                        <x-help-tip text="The pitch shown to the player when browsing this loan — explain what it's meant to be used for." example="Quick capital for a working asset. Borrow for things that EARN — if the asset's monthly profit beats the installment, debt is a tool; if not, it's a trap." />
                    </label>
                    <textarea name="description" class="field-input" rows="3" placeholder="What is this loan for?">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Min Amount (KES)
                            <x-help-tip text="The smallest amount a player can borrow from this loan product." example="1000" />
                        </label>
                        <input type="number" name="min_amount" value="{{ old('min_amount', $product->min_amount ?? 1000) }}" min="100" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Max Amount (KES)
                            <x-help-tip text="The largest amount a player can borrow from this loan product in one go." example="50000" />
                        </label>
                        <input type="number" name="max_amount" value="{{ old('max_amount', $product->max_amount ?? 50000) }}" min="100" class="field-input">
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="field-label">Annual Interest Rate (%)
                        <x-help-tip text="The yearly interest rate charged on the outstanding balance, compounded every payment period — higher rates mean bigger installments and a stronger 'cost of credit' lesson." example="14" />
                    </label>
                    <input type="number" name="annual_interest_rate" value="{{ old('annual_interest_rate', $product->annual_interest_rate ?? 18) }}" min="1" max="200" step="0.5" class="field-input">
                    <p class="text-xs text-gray-500 mt-1">Compounded every payment period</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Loan Term (ticks)
                            <x-help-tip text="How many game days the player has to fully repay this loan before it's considered defaulted." example="180" />
                        </label>
                        <input type="number" name="term_ticks" value="{{ old('term_ticks', $product->term_ticks ?? 90) }}" min="7" max="3650" class="field-input">
                        <p class="text-xs text-gray-500 mt-1">90 ≈ 3 game months</p>
                    </div>
                    <div>
                        <label class="field-label">Payment Period (ticks)
                            <x-help-tip text="How often (in game days) an installment auto-deducts from the player's cash — a standing order, unlike most other bills which are manual." example="30" />
                        </label>
                        <input type="number" name="payment_period_ticks" value="{{ old('payment_period_ticks', $product->payment_period_ticks ?? 7) }}" min="1" max="90" class="field-input">
                        <p class="text-xs text-gray-500 mt-1">7 = pay weekly</p>
                    </div>
                </div>
                <div>
                    <label class="field-label">Min Credit Score Required
                        <x-help-tip text="The minimum credit score a player needs before this loan product appears as available to them — set it to 300 to make it open to anyone." example="450" />
                    </label>
                    <input type="number" name="min_credit_score" value="{{ old('min_credit_score', $product->min_credit_score ?? 300) }}" min="300" max="850" class="field-input">
                    <p class="text-xs text-gray-500 mt-1">300 = anyone. Higher = better credit required.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Sort Order
                            <x-help-tip text="Controls display order on the Bank's loan menu — lower numbers show first." example="0" />
                        </label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" min="0" class="field-input">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/20">
                            <span class="text-sm font-semibold text-gray-300">Active
                                <x-help-tip text="Inactive loan products are hidden from the Bank menu and can't be newly borrowed against, but existing player loans keep repaying normally." />
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit"
                    class="px-8 py-3 rounded-xl font-black text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#1d4ed8,#1e40af);box-shadow:0 4px 14px rgba(29,78,216,.35);">
                {{ $mode === 'create' ? 'Create Loan Product' : 'Save Changes' }}
            </button>
            <a href="{{ route('gameset.loans.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-400 border border-white/10 hover:bg-white/5">
                Cancel
            </a>
        </div>
    </form>
</div>
</body>
</html>
