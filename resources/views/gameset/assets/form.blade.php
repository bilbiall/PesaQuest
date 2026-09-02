<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'New Asset' : 'Edit: '.$asset->name }} — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/icons.js') }}"></script>
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }

        .cat-vehicle    { background:linear-gradient(135deg,#92400e 0%,#b45309 35%,#1c1917 100%); }
        .cat-property   { background:linear-gradient(135deg,#064e3b 0%,#065f46 35%,#0f172a 100%); }
        .cat-business   { background:linear-gradient(135deg,#3730a3 0%,#4c1d95 35%,#1e1b4b 100%); }
        .cat-investment { background:linear-gradient(135deg,#1e3a8a 0%,#1e40af 35%,#0f172a 100%); }
        .cat-fixed_income { background:linear-gradient(135deg,#134e4a 0%,#0f766e 35%,#0f172a 100%); }
        .cat-gadget     { background:linear-gradient(135deg,#831843 0%,#9d174d 35%,#1a1025 100%); }

        @keyframes iconbob { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
        .iconbob { animation:iconbob 2.5s ease-in-out infinite; display:inline-block; }

        .form-input {
            width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            color:#fff; border-radius:.75rem; padding:.6rem 1rem; font-size:.875rem;
            outline:none; transition:border-color .2s;
        }
        .form-input:focus { border-color:rgba(99,102,241,.6); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
        .form-label { display:block; font-size:11px; font-weight:700; letter-spacing:.06em; color:#6b7280; text-transform:uppercase; margin-bottom:.35rem; }

        .section-card { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.07); border-radius:1.25rem; padding:1.5rem; margin-bottom:1.25rem; }
        .section-title { font-size:.75rem; font-weight:900; letter-spacing:.1em; color:#6366f1; text-transform:uppercase; margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; }

        /* Drop zone */
        .drop-zone { border:2px dashed rgba(99,102,241,.3); border-radius:1rem; padding:2rem; text-align:center; transition:all .2s; cursor:pointer; }
        .drop-zone:hover, .drop-zone.drag-over { border-color:rgba(99,102,241,.7); background:rgba(99,102,241,.05); }

        @keyframes popIn { from{opacity:0;transform:scale(.96) translateY(8px)} to{opacity:1;transform:scale(1) translateY(0)} }
        .pop-in { animation:popIn .3s cubic-bezier(.34,1.56,.64,1) both; }

        input[type=range] { -webkit-appearance:none; height:4px; border-radius:2px; background:rgba(255,255,255,.1); }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance:none; width:16px; height:16px; border-radius:50%; background:#6366f1; cursor:pointer; box-shadow:0 0 0 3px rgba(99,102,241,.3); }

        select.form-input option { background:#0f172a; color:#fff; }
        textarea.form-input { min-height:80px; resize:vertical; }
    </style>
</head>
<body class="text-white min-h-screen" x-data="assetForm()">

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('gameset.assets.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Assets
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">{{ $mode === 'create' ? '✨ New Asset' : '✏️ '.$asset->name }}</span>
        </div>
        @if($mode === 'edit')
        <div class="flex items-center gap-2 text-xs">
            <span class="text-gray-500">Status:</span>
            <span class="{{ $asset->is_active ? 'text-emerald-400' : 'text-gray-500' }} font-bold">
                {{ $asset->is_active ? '● Active' : '○ Inactive' }}
            </span>
        </div>
        @endif
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-6 pop-in rounded-2xl px-5 py-3 flex items-center gap-3 text-sm font-bold text-emerald-300"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span>{{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-3 text-sm font-bold text-red-300"
         style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.25);">
        <p class="mb-2 font-black">⚠️ Please fix these errors:</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- ── LEFT: Live Preview ── --}}
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Live Preview</p>

                <div class="rounded-3xl overflow-hidden" style="background:linear-gradient(160deg,rgba(15,23,42,.95),rgba(30,27,75,.8));border:1px solid rgba(255,255,255,.1);">
                    {{-- Preview header --}}
                    <div class="relative h-44 overflow-hidden flex items-center justify-center"
                         :class="'cat-' + (form.category || 'investment')">
                        {{-- Photo preview --}}
                        <template x-if="previewImage">
                            <img :src="previewImage" class="absolute inset-0 w-full h-full object-cover" style="opacity:.38;mix-blend-mode:overlay;" />
                        </template>
                        <div class="absolute inset-0" style="background:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:28px 28px;"></div>
                        <template x-if="form.icon">
                            <div class="iconbob relative z-10 w-16 h-16" x-html="pqIcon(form.icon, 'w-16 h-16')"></div>
                        </template>
                        <div class="absolute top-3 right-3 text-xs font-black px-2 py-0.5 rounded-xl"
                             style="background:rgba(0,0,0,.5);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);"
                             x-text="(form.category || 'investment').charAt(0).toUpperCase() + (form.category || 'investment').slice(1)"></div>
                        <div class="absolute bottom-3 left-3 flex gap-1">
                            <template x-for="i in 5" :key="i">
                                <div class="w-1.5 h-1.5 rounded-full" :style="i <= parseInt(form.tier||1) ? 'background:#a78bfa' : 'background:rgba(255,255,255,.2)'"></div>
                            </template>
                        </div>
                    </div>

                    {{-- Preview content --}}
                    <div class="p-5">
                        <h3 class="font-black text-white text-base leading-tight mb-0.5" x-text="form.name || 'Asset Name'"></h3>
                        <p class="text-xs text-gray-500 mb-3" x-text="form.brand || 'Brand'"></p>

                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div class="rounded-xl p-2.5 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);">
                                <p class="text-[9px] text-gray-500 font-semibold uppercase mb-0.5">Price</p>
                                <p class="text-xs font-black text-white" x-text="'Ksh ' + (parseInt(form.base_price)||0).toLocaleString()"></p>
                            </div>
                            <div class="rounded-xl p-2.5 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);">
                                <p class="text-[9px] text-gray-500 font-semibold uppercase mb-0.5">Net/mo</p>
                                <p class="text-xs font-black" :class="netMonthly >= 0 ? 'text-emerald-400' : 'text-red-400'" x-text="(netMonthly >= 0 ? '+' : '') + 'Ksh ' + Math.abs(netMonthly).toLocaleString()"></p>
                            </div>
                            <div class="rounded-xl p-2.5 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);">
                                <p class="text-[9px] text-gray-500 font-semibold uppercase mb-0.5">%/mo</p>
                                <p class="text-xs font-black" :class="parseFloat(form.appreciation_rate||0) >= 0 ? 'text-emerald-400' : 'text-red-400'"
                                   x-text="(parseFloat(form.appreciation_rate||0) > 0 ? '+' : '') + (form.appreciation_rate||0) + '%'"></p>
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 leading-relaxed line-clamp-2 mb-3" x-text="form.description || 'Asset description will appear here...'"></p>

                        {{-- Risk indicator --}}
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-[10px] text-gray-500 uppercase font-semibold">Risk:</span>
                            <div class="flex gap-1">
                                <template x-for="i in 5" :key="i">
                                    <div class="w-3 h-1.5 rounded-full transition-all"
                                         :style="i <= parseInt(form.risk_level||2) ? 'background:' + riskColor : 'background:rgba(255,255,255,.1)'"></div>
                                </template>
                                <span class="text-[10px] ml-1" :style="'color:' + riskColor" x-text="riskLabel"></span>
                            </div>
                        </div>

                        <div class="w-full py-3 rounded-xl text-sm font-black text-center text-white"
                             style="background:linear-gradient(135deg,rgba(99,102,241,.4),rgba(139,92,246,.3));border:1px solid rgba(139,92,246,.4);">
                            🛒 View & Buy
                        </div>
                    </div>
                </div>

                {{-- Quick meta --}}
                <div class="mt-4 rounded-2xl p-4 space-y-2" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Age Group</span>
                        <span class="text-white font-bold" x-text="form.age_group || '—'"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Max per Player</span>
                        <span class="text-white font-bold" x-text="form.max_per_player || 1"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Volatility</span>
                        <span class="text-white font-bold" x-text="(parseFloat(form.volatility||0)*100).toFixed(0) + '%'"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Auto Bill</span>
                        <span class="font-bold" :class="form.creates_bill_slug ? 'text-orange-400' : 'text-gray-600'" x-text="form.creates_bill_slug || 'none'"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Form ── --}}
        <div class="lg:col-span-2">
            <form method="POST"
                  action="{{ $mode === 'create' ? route('gameset.assets.store') : route('gameset.assets.update', $asset) }}"
                  enctype="multipart/form-data"
                  @submit.prevent="submitForm">
                @csrf
                @if($mode === 'edit') @method('PUT') @endif

                {{-- ── Section 1: Identity ── --}}
                <div class="section-card">
                    <p class="section-title">📋 Identity & Appearance</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Asset Name *
                                <x-help-tip text="The product name shown on the asset card and detail view in the marketplace. Be specific and realistic — this is what sells the purchase to players." example="Honda Fit 2015" />
                            </label>
                            <input type="text" name="name" class="form-input"
                                   x-model="form.name" placeholder="e.g. Honda Fit 2015"
                                   value="{{ old('name', $asset?->name) }}" required>
                        </div>
                        <div>
                            <label class="form-label">Brand / Issuer
                                <x-help-tip text="The manufacturer, company, or institution behind the asset — shown as a small subtitle under the name on the marketplace card. Leave blank if it doesn't apply." example="Toyota" />
                            </label>
                            <input type="text" name="brand" class="form-input"
                                   x-model="form.brand" placeholder="e.g. Honda, CBK, Safaricom"
                                   value="{{ old('brand', $asset?->brand) }}">
                        </div>
                        <div>
                            <label class="form-label">Icon (name) <span class="text-gray-600 font-normal normal-case">— optional, leave blank to hide</span>
                                <x-help-tip text="A name from the app's icon set (e.g. car, house, briefcase) shown as the big icon on this asset's marketplace card and live preview. Leave blank and the icon area is simply hidden." example="car" />
                            </label>
                            <input type="text" name="icon" class="form-input text-center"
                                   x-model="form.icon" placeholder="car"
                                   value="{{ old('icon', $asset?->icon ?? '') }}" maxlength="30">
                        </div>
                        <div>
                            <label class="form-label">Category *
                                <x-help-tip text="Sets which marketplace section the asset lists under and which auto-bills attach on purchase — vehicle and property assets automatically attach follow-up bills like insurance and fuel." example="vehicle" />
                            </label>
                            <select name="category" class="form-input" x-model="form.category" required>
                                <option value="vehicle"    {{ old('category',$asset?->category)==='vehicle'    ? 'selected':'' }}>🚗 Vehicle</option>
                                <option value="property"   {{ old('category',$asset?->category)==='property'   ? 'selected':'' }}>🏠 Property</option>
                                <option value="business"   {{ old('category',$asset?->category)==='business'   ? 'selected':'' }}>💼 Business</option>
                                <option value="investment" {{ old('category',$asset?->category)==='investment' ? 'selected':'' }}>📈 Investment</option>
                                <option value="fixed_income" {{ old('category',$asset?->category)==='fixed_income' ? 'selected':'' }}>🏛️ Fixed Income</option>
                                <option value="gadget"     {{ old('category',$asset?->category)==='gadget'     ? 'selected':'' }}>📱 Gadget</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Product Type <span class="text-gray-600 font-normal normal-case">— Fixed Income only</span>
                                <x-help-tip text="Sub-classifies a Fixed Income asset (Money Market Fund, Treasury Bill, ...) so the Marketplace can group and compare them as real product types instead of one flat list. Ignored for every other category." example="money_market_fund" />
                            </label>
                            <select name="product_type" class="form-input">
                                <option value="" {{ old('product_type',$asset?->product_type)==='' || old('product_type',$asset?->product_type)===null ? 'selected':'' }}>— None —</option>
                                <option value="money_market_fund" {{ old('product_type',$asset?->product_type)==='money_market_fund' ? 'selected':'' }}>Money Market Fund</option>
                                <option value="treasury_bill"     {{ old('product_type',$asset?->product_type)==='treasury_bill'     ? 'selected':'' }}>Treasury Bill</option>
                                <option value="treasury_bond"     {{ old('product_type',$asset?->product_type)==='treasury_bond'     ? 'selected':'' }}>Treasury Bond</option>
                                <option value="fixed_deposit"     {{ old('product_type',$asset?->product_type)==='fixed_deposit'     ? 'selected':'' }}>Fixed Deposit</option>
                                <option value="corporate_bond"    {{ old('product_type',$asset?->product_type)==='corporate_bond'    ? 'selected':'' }}>Corporate Bond</option>
                                <option value="sacco_deposit"     {{ old('product_type',$asset?->product_type)==='sacco_deposit'     ? 'selected':'' }}>SACCO Deposit</option>
                                <option value="endowment"         {{ old('product_type',$asset?->product_type)==='endowment'         ? 'selected':'' }}>Endowment Plan</option>
                                <option value="sukuk"             {{ old('product_type',$asset?->product_type)==='sukuk'             ? 'selected':'' }}>Sukuk Bond</option>
                            </select>
                            @if($asset?->mmf_sponsor_id)
                            <p class="text-xs text-gray-500 mt-1">🏆 Sponsored by <b class="text-gray-300">{{ $asset->mmfSponsor->name }}</b> — manage sponsor assignment from Admin → Sponsors.</p>
                            @endif
                        </div>
                        <div>
                            <label class="form-label">MMF Rate Band (% p.a.) <span class="text-gray-600 font-normal normal-case">— Money Market Fund only</span>
                                <x-help-tip text="The annual rate this fund fluctuates within — a fresh rate is rolled every game day between these two numbers, so returns vary daily like a real MMF. Leave blank to fall back to the global default band set in Game Clock Speed." example="9.0 – 13.0" />
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="mmf_min_rate" class="form-input" step="0.1" min="0" max="100"
                                       value="{{ old('mmf_min_rate', $asset?->mmf_min_rate ?? '') }}" placeholder="Min e.g. 9.0">
                                <span class="text-gray-500">–</span>
                                <input type="number" name="mmf_max_rate" class="form-input" step="0.1" min="0" max="100"
                                       value="{{ old('mmf_max_rate', $asset?->mmf_max_rate ?? '') }}" placeholder="Max e.g. 13.0">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Age Group *
                                <x-help-tip text="Restricts which players can see and buy this asset in the marketplace. Choose 'All Ages' unless the item only makes sense for a specific age bracket." example="18-25" />
                            </label>
                            <select name="age_group" class="form-input" x-model="form.age_group" required>
                                <option value="all"   {{ old('age_group',$asset?->age_group)==='all'   ? 'selected':'' }}>All Ages</option>
                                <option value="8-12"  {{ old('age_group',$asset?->age_group)==='8-12'  ? 'selected':'' }}>8–12</option>
                                <option value="13-17" {{ old('age_group',$asset?->age_group)==='13-17' ? 'selected':'' }}>13–17</option>
                                <option value="18-25" {{ old('age_group',$asset?->age_group)==='18-25' ? 'selected':'' }}>18–25</option>
                                <option value="26+"   {{ old('age_group',$asset?->age_group)==='26+'   ? 'selected':'' }}>26+</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tier (1 = Starter, 5 = Elite) *
                                <x-help-tip text="Sets the asset's prestige level, shown as dots on its marketplace card. Higher tiers should generally cost more and mark a bigger financial milestone, not just a random label." example="3" />
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="range" name="tier" min="1" max="5" class="flex-1"
                                       x-model="form.tier"
                                       value="{{ old('tier', $asset?->tier ?? 1) }}">
                                <span class="text-indigo-400 font-black text-lg w-6 text-center" x-text="form.tier || 1"></span>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Max per Player *
                                <x-help-tip text="The most units of this asset a single player can own at once. Use 1 for unique big-ticket items like a house; raise it for stackable items like shares." example="1" />
                            </label>
                            <input type="number" name="max_per_player" class="form-input" min="1" max="20"
                                   x-model="form.max_per_player"
                                   value="{{ old('max_per_player', $asset?->max_per_player ?? 1) }}" required>
                        </div>
                    </div>
                </div>

                {{-- ── Section 2: Image ── --}}
                <div class="section-card">
                    <p class="section-title">🖼️ Product Image</p>

                    {{-- Current image preview --}}
                    @if($mode === 'edit' && $asset?->image_url)
                    <div class="mb-4 rounded-xl overflow-hidden relative" style="height:120px;">
                        <img src="{{ $asset->image_url }}" class="w-full h-full object-cover" alt="Current image">
                        <div class="absolute inset-0 flex items-end p-2" style="background:linear-gradient(to top,rgba(0,0,0,.6),transparent);">
                            <span class="text-xs text-white font-bold">Current image</span>
                        </div>
                    </div>
                    @endif

                    {{-- Upload zone --}}
                    <label class="form-label">Upload Photo
                        <x-help-tip text="The product photo shown on this asset's marketplace card. Uploading a file here always overrides the pasted URL below. Accepts PNG, JPG or WEBP up to 5MB." />
                    </label>
                    <div class="drop-zone mb-4" id="dropZone"
                         @dragover.prevent="$el.classList.add('drag-over')"
                         @dragleave.prevent="$el.classList.remove('drag-over')"
                         @drop.prevent="handleDrop($event)"
                         @click="$refs.fileInput.click()">
                        <input type="file" name="image_file" accept="image/*"
                               class="hidden" x-ref="fileInput"
                               @change="handleFileSelect($event)">
                        <template x-if="!previewImage || !uploadedFile">
                            <div>
                                <div class="text-3xl mb-2">📸</div>
                                <p class="text-sm font-bold text-gray-300">Drop an image here or click to upload</p>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP — max 5MB</p>
                            </div>
                        </template>
                        <template x-if="uploadedFile">
                            <div>
                                <div class="text-3xl mb-2">✅</div>
                                <p class="text-sm font-bold text-emerald-400" x-text="'Ready: ' + uploadedFile.name"></p>
                                <p class="text-xs text-gray-500 mt-1">Click to change</p>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-3 mb-1">
                        <div class="flex-1 h-px bg-white/10"></div>
                        <span class="text-xs text-gray-500 font-semibold">OR paste a URL</span>
                        <div class="flex-1 h-px bg-white/10"></div>
                    </div>

                    <label class="form-label mt-3 mb-0">Or Paste an Image URL
                        <x-help-tip text="An alternative to uploading a file above — paste a direct link to a photo hosted elsewhere. Leave blank on edit to keep the asset's current image." example="https://images.example.com/boda-boda.jpg" />
                    </label>
                    <input type="text" name="image_url" class="form-input mt-3"
                           x-model="form.image_url"
                           @input="previewFromUrl()"
                           placeholder="https://images.example.com/photo.jpg"
                           value="{{ old('image_url', $asset?->image_url) }}">
                    <p class="text-[10px] text-gray-600 mt-1.5">Upload takes priority over URL. Leave blank to keep current image.</p>
                </div>

                {{-- ── Section 3: Pricing ── --}}
                <div class="section-card">
                    <p class="section-title">💰 Pricing & Economics</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Base Price (Ksh) *
                                <x-help-tip text="What players pay upfront to buy this asset — the top half of the Payback Preview below (Price ÷ net monthly income). Keep it real-world plausible for the item." example="85000" />
                            </label>
                            <input type="number" name="base_price" class="form-input" min="0"
                                   x-model="form.base_price"
                                   value="{{ old('base_price', $asset?->base_price) }}" required>
                        </div>
                        <div>
                            <label class="form-label">Monthly Income (Ksh) *
                                <x-help-tip text="How much this asset automatically pays the player every income period (set below) — the core of the investment case. Set to 0 for pure status/luxury items with no payout." example="12000" />
                            </label>
                            <input type="number" name="monthly_income" class="form-input" min="0"
                                   x-model="form.monthly_income"
                                   value="{{ old('monthly_income', $asset?->monthly_income ?? 0) }}" required>
                            <label class="form-label mt-2">Income Source Description
                                <x-help-tip text="A short flavor line shown to players describing where the income comes from. Purely descriptive — it does not affect the actual payout." example="Rent from 2 tenants" />
                            </label>
                            <input type="text" name="income_description" class="form-input mt-2"
                                   placeholder="Income source description"
                                   value="{{ old('income_description', $asset?->income_description) }}">
                        </div>
                        <div>
                            <label class="form-label">Monthly Cost (Ksh) *
                                <x-help-tip text="How much this asset automatically charges the player every income period (set below) — fuel, maintenance, fees. Real businesses and vehicles should never be 0; only true passive investments are cost-free." example="3500" />
                            </label>
                            <input type="number" name="monthly_cost" class="form-input" min="0"
                                   x-model="form.monthly_cost"
                                   value="{{ old('monthly_cost', $asset?->monthly_cost ?? 0) }}" required>
                            <label class="form-label mt-2">Cost Breakdown Description
                                <x-help-tip text="A short flavor line shown to players describing what the running cost covers. Purely descriptive — it does not affect the actual charge." example="Fuel, repairs & insurance" />
                            </label>
                            <input type="text" name="cost_description" class="form-input mt-2"
                                   placeholder="Cost breakdown description"
                                   value="{{ old('cost_description', $asset?->cost_description) }}">
                        </div>
                        <div>
                            <label class="form-label">Income Period (game days / ticks) *
                                <x-help-tip text="How often, in game days, the Monthly Income and Monthly Cost above actually get paid/charged — not literally every calendar month. E.g. 7 pays out roughly 4 times a game month; 30 pays out once." example="7" />
                            </label>
                            <input type="number" name="income_period_ticks" class="form-input" min="1" max="365"
                                   value="{{ old('income_period_ticks', $asset?->income_period_ticks ?? 7) }}" required>
                            <p class="text-[10px] text-gray-600 mt-1">1 = daily · 7 = weekly · 30 = monthly. Lower = faster returns.</p>
                        </div>
                        <div>
                            <label class="form-label">Appreciation Rate (%/mo)
                                <x-help-tip text="How much the asset's resale value drifts every game month, compounding over time. Positive grows value (investments, property); negative shrinks it (vehicles, gadgets wear out)." example="-1.5" />
                            </label>
                            <input type="number" name="appreciation_rate" class="form-input" step="0.01" min="-20" max="20"
                                   x-model="form.appreciation_rate"
                                   value="{{ old('appreciation_rate', $asset?->appreciation_rate ?? 0) }}" required>
                            <p class="text-[10px] text-gray-600 mt-1">Negative = depreciation (e.g. -1.5 for cars)</p>
                        </div>
                        <div>
                            <label class="form-label">Volatility (0 = stable, 1 = wild)
                                <x-help-tip text="Adds random swings to the asset's value on top of the Appreciation Rate trend. Near 0 keeps the price smooth and predictable; higher (this slider caps at 50%) causes sharp, unpredictable jumps up or down, like a risky stock." example="0.05" />
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="range" name="volatility" min="0" max="0.5" step="0.01" class="flex-1"
                                       x-model="form.volatility"
                                       value="{{ old('volatility', $asset?->volatility ?? 0.05) }}">
                                <span class="text-indigo-400 font-black text-sm w-12 text-center" x-text="(parseFloat(form.volatility||0)*100).toFixed(0) + '%'"></span>
                            </div>
                            <input type="hidden" name="volatility" :value="form.volatility">
                        </div>
                        <div>
                            <label class="form-label">Risk Level (1-5) *
                                <x-help-tip text="The risk badge shown to players (Very Low to Very High) on the asset card — purely a signal, it doesn't itself change payouts or volatility. Keep it honest relative to the volatility and appreciation rate you actually set." example="2" />
                            </label>
                            <div class="flex items-center gap-3 mt-1">
                                <input type="range" min="1" max="5" class="flex-1"
                                       x-model="form.risk_level">
                                <span class="font-black text-sm w-20 text-right" :style="'color:' + riskColor" x-text="riskLabel"></span>
                            </div>
                            <input type="hidden" name="risk_level" :value="form.risk_level">
                        </div>
                        <div>
                            <label class="form-label">Matures In (game days)
                                <x-help-tip text="Leave blank for a normal asset that never matures. Set a value to make this a fixed-income instrument (like a T-Bill or T-Bond): it auto-redeems for a lump sum this many game days after purchase, and Appreciation Rate above should usually be 0 since maturity handles the payout instead." example="91" />
                            </label>
                            <input type="number" name="maturity_ticks" class="form-input" min="1" max="3650"
                                   value="{{ old('maturity_ticks', $asset?->maturity_ticks ?? '') }}" placeholder="Leave blank = never matures">
                        </div>
                        <div>
                            <label class="form-label">Locked Until Maturity
                                <x-help-tip text="If checked, the player cannot sell this at all before it matures — use for short-term discount instruments like a T-Bill. Leave unchecked to allow early exit (optionally with the penalty below) — use for longer-term instruments like a T-Bond." />
                            </label>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input type="checkbox" name="locked" value="1" class="w-4 h-4"
                                       {{ old('locked', $asset?->locked ?? false) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-300">Block selling before maturity</span>
                            </label>
                        </div>
                        <div>
                            <label class="form-label">Early Exit Penalty (%)
                                <x-help-tip text="Only applies if 'Locked' above is unchecked and the asset has a maturity date. The % haircut taken off the sale price if the player sells before maturity — represents forfeiting future coupons/value for cashing out early." example="20" />
                            </label>
                            <input type="number" name="early_exit_penalty_pct" class="form-input" step="0.1" min="0" max="100"
                                   value="{{ old('early_exit_penalty_pct', $asset?->early_exit_penalty_pct ?? 0) }}" required>
                        </div>
                        <div>
                            <label class="form-label">Maturity Bonus (%)
                                <x-help-tip text="For discount instruments (like a T-Bill): the % paid on top of current value as a lump sum when it matures — this is how the 'buy at a discount, redeem at face value' return gets modeled. Leave 0 for instruments that pay their return via Monthly Income instead (like a coupon bond)." example="4.31" />
                            </label>
                            <input type="number" name="maturity_bonus_pct" class="form-input" step="0.001" min="0" max="100"
                                   value="{{ old('maturity_bonus_pct', $asset?->maturity_bonus_pct ?? 0) }}" required>
                        </div>
                        <div>
                            <label class="form-label">Auto-Create Bill (slug)
                                <x-help-tip text="When set, buying this asset automatically attaches a recurring bill (e.g. insurance, service fee) — it must exactly match an existing slug in GameSet → Bills. Leave blank if this asset shouldn't create a follow-up bill." example="car-insurance" />
                            </label>
                            <input type="text" name="creates_bill_slug" class="form-input"
                                   x-model="form.creates_bill_slug"
                                   placeholder="e.g. car-insurance"
                                   value="{{ old('creates_bill_slug', $asset?->creates_bill_slug) }}">
                            <p class="text-[10px] text-gray-600 mt-1">Leave blank for no auto-bill. Must match a bill slug.</p>
                        </div>

                        {{-- Live payback preview — this is exactly what players see on the asset card --}}
                        <div class="sm:col-span-2 rounded-xl px-4 py-3"
                             style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);">
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-400 mb-1.5">⚡ Payback Preview (auto-calculated)</p>
                            <template x-if="netMonthly > 0">
                                <p class="text-xs text-gray-300 leading-relaxed">
                                    Players will see: <span class="font-black text-emerald-400"
                                        x-text="'Pays itself off in ~' + Math.ceil((parseInt(form.base_price)||0) / netMonthly) + ' game months'"></span>
                                    — Base Price ÷ (Monthly Income − Monthly Cost) =
                                    <span x-text="(parseInt(form.base_price)||0).toLocaleString() + ' ÷ ' + netMonthly.toLocaleString()"></span>.
                                    Adjust the three fields above to change it.
                                </p>
                            </template>
                            <template x-if="netMonthly <= 0">
                                <p class="text-xs text-gray-400 leading-relaxed">
                                    No payback badge — net cash flow is
                                    <span class="font-bold" :class="netMonthly < 0 ? 'text-red-400' : 'text-gray-300'" x-text="netMonthly.toLocaleString() + '/mo'"></span>.
                                    Set Monthly Income above Monthly Cost and the card will show
                                    "Pays itself off in ~N game months" automatically.
                                </p>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ── Section 4: Copy ── --}}
                <div class="section-card">
                    <p class="section-title">📝 Descriptions & Education</p>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Main Description *
                                <x-help-tip text="The main sales-pitch paragraph shown on the asset's detail view. Sell it honestly, including any real downsides players should weigh before buying." example="A dependable TVS boda with 40,000km on the clock — fuel, repairs and insurance eat into every fare." />
                            </label>
                            <textarea name="description" class="form-input" rows="3"
                                      x-model="form.description" required>{{ old('description', $asset?->description) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Flavor Text * <span class="text-gray-600 font-normal normal-case">(short, memorable quote)</span>
                                <x-help-tip text="A short punchy quote shown on the asset card below the name. It adds personality, not information — keep it to a single sentence." example="The first car is always a Honda Fit." />
                            </label>
                            <input type="text" name="flavor_text" class="form-input"
                                   x-model.lazy="form.flavor_text"
                                   placeholder='"The first car is always a Honda Fit."'
                                   value="{{ old('flavor_text', $asset?->flavor_text) }}" required>
                        </div>
                        <div>
                            <label class="form-label">Educational Note * <span class="text-gray-600 font-normal normal-case">(financial lesson)</span>
                                <x-help-tip text="The financial-literacy takeaway shown to players after buying — spell out the real-world lesson this purchase teaches, e.g. depreciation, cash flow, or leverage." example="Vehicles lose value the moment you drive them off the lot — budget for depreciation, not just the purchase price." />
                            </label>
                            <textarea name="educational_note" class="form-input" rows="3"
                                      required>{{ old('educational_note', $asset?->educational_note) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Section 5: Status + Lucrative ── --}}
                <div class="section-card">
                    <p class="section-title">⚙️ Status</p>
                    <label class="flex items-center gap-4 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" class="sr-only" id="is_active_check"
                                   {{ old('is_active', $asset?->is_active ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 rounded-full transition-colors"
                                 style="background:{{ old('is_active', $asset?->is_active ?? true) ? '#6366f1' : 'rgba(255,255,255,.1)' }}"
                                 id="toggle-bg"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform"
                                 id="toggle-knob"
                                 style="transform:{{ old('is_active', $asset?->is_active ?? true) ? 'translateX(20px)' : 'translateX(0)' }}"></div>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-white" id="toggle-label">
                                {{ old('is_active', $asset?->is_active ?? true) ? 'Active — visible in marketplace' : 'Inactive — hidden from players' }}
                            </span>
                            <p class="text-xs text-gray-500">Players can only see and buy active assets
                                <x-help-tip text="When off, the asset is completely hidden from the marketplace and players can't buy it — use this to stage new items or retire old ones without deleting their purchase history." />
                            </p>
                        </div>
                    </label>

                    {{-- Lucrative flag (stored as is_luxury) --}}
                    <label class="flex items-center gap-3 mt-5 cursor-pointer select-none">
                        <input type="hidden" name="is_luxury" value="0">
                        <input type="checkbox" name="is_luxury" value="1"
                               {{ old('is_luxury', $asset?->is_luxury ?? false) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-white/20 bg-white/5 accent-amber-500">
                        <div>
                            <span class="text-sm font-bold text-amber-400">✨ Lucrative Asset</span>
                            <p class="text-xs text-gray-500 mt-0.5">Lucrative assets show in a premium section of the marketplace with a gold crown badge.
                                <x-help-tip text="Marks zero-or-low-income status items (flashy cars, gadgets) meant to teach players about opportunity cost by contrast with real cash-flowing assets. Use it deliberately for luxuries, not for genuine investments." example="true for a designer watch with no income" />
                            </p>
                        </div>
                    </label>

                    {{-- Badge --}}
                    <div class="mt-5">
                        <label class="form-label">Marketplace Badge
                            <x-help-tip text="An optional coloured pill shown on the marketplace card to draw attention or signal a vibe (e.g. hot, safe, risky). Purely cosmetic marketing — it doesn't change any of the asset's stats." example="trending" />
                        </label>
                        <select name="badge" class="form-input mt-1">
                            <option value="" {{ old('badge', $asset?->badge) == '' ? 'selected' : '' }}>— None —</option>
                            @foreach(['popular'=>'🔥 Popular','trending'=>'📈 Trending','new'=>'✨ New','stable'=>'🛡 Stable','risky'=>'⚡ Risky'] as $val=>$lbl)
                                <option value="{{ $val }}" {{ old('badge', $asset?->badge) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-600 mt-1">Shown as a coloured pill on the asset card.</p>
                    </div>

                    {{-- Featured Section --}}
                    <div class="mt-5">
                        <label class="form-label">Featured Section
                            <x-help-tip text="Places this asset into one of the marketplace's curated editorial rows so players discover it there in addition to normal browsing and search." example="high_growth" />
                        </label>
                        <select name="featured_section" class="form-input mt-1">
                            <option value="" {{ old('featured_section', $asset?->featured_section) == '' ? 'selected' : '' }}>— None —</option>
                            <option value="starter_moves"      {{ old('featured_section', $asset?->featured_section) === 'starter_moves'      ? 'selected' : '' }}>🎯 Starter Moves</option>
                            <option value="serious_money"      {{ old('featured_section', $asset?->featured_section) === 'serious_money'      ? 'selected' : '' }}>♟️ Serious Money</option>
                            <option value="high_growth"        {{ old('featured_section', $asset?->featured_section) === 'high_growth'        ? 'selected' : '' }}>🚀 High Growth</option>
                            <option value="dividend_builders"  {{ old('featured_section', $asset?->featured_section) === 'dividend_builders'  ? 'selected' : '' }}>💰 Dividend Builders</option>
                            <option value="lifestyle_upgrades" {{ old('featured_section', $asset?->featured_section) === 'lifestyle_upgrades' ? 'selected' : '' }}>🎧 Lifestyle Upgrades</option>
                        </select>
                        <p class="text-[10px] text-gray-600 mt-1">Determines which editorial section this asset appears in on the marketplace.</p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex gap-4">
                    <a href="{{ route('gameset.assets.index') }}"
                       class="flex-1 py-4 rounded-2xl text-sm font-bold text-center text-gray-400 transition-all hover:text-white"
                       style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 py-4 rounded-2xl text-sm font-black text-white transition-all hover:scale-[1.01] hover:brightness-110"
                            style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 20px rgba(99,102,241,.4);">
                        {{ $mode === 'create' ? '✨ Create Asset' : '💾 Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function assetForm() {
    return {
        form: {
            name:             '{{ old('name',     $asset?->name     ?? '') }}',
            brand:            '{{ old('brand',    $asset?->brand    ?? '') }}',
            icon:             '{{ old('icon',     $asset?->icon     ?? '') }}',
            category:         '{{ old('category', $asset?->category ?? 'investment') }}',
            age_group:        '{{ old('age_group',$asset?->age_group?? 'all') }}',
            tier:             {{ old('tier',     $asset?->tier       ?? 1) }},
            max_per_player:   {{ old('max_per_player', $asset?->max_per_player ?? 1) }},
            base_price:       {{ old('base_price', $asset?->base_price ?? 0) }},
            monthly_income:   {{ old('monthly_income', $asset?->monthly_income ?? 0) }},
            monthly_cost:     {{ old('monthly_cost',   $asset?->monthly_cost   ?? 0) }},
            appreciation_rate: {{ old('appreciation_rate', $asset?->appreciation_rate ?? 0) }},
            volatility:       {{ old('volatility', $asset?->volatility ?? 0.05) }},
            risk_level:       {{ old('risk_level', $asset?->risk_level ?? 2) }},
            creates_bill_slug:'{{ old('creates_bill_slug', $asset?->creates_bill_slug ?? '') }}',
            description:      @json(old('description', $asset?->description ?? '')),
            flavor_text:      @json(old('flavor_text',  $asset?->flavor_text  ?? '')),
            image_url:        '{{ old('image_url', $asset?->image_url ?? '') }}',
        },

        previewImage: '{{ $asset?->image_url ?? '' }}',
        uploadedFile: null,

        get netMonthly() {
            return (parseInt(this.form.monthly_income) || 0) - (parseInt(this.form.monthly_cost) || 0);
        },
        get riskLabel() {
            const labels = ['','Very Low','Low','Medium','High','Very High'];
            return labels[parseInt(this.form.risk_level)] || 'Medium';
        },
        get riskColor() {
            const colors = ['','#10b981','#60a5fa','#f59e0b','#fb923c','#f87171'];
            return colors[parseInt(this.form.risk_level)] || '#f59e0b';
        },

        previewFromUrl() {
            if (this.form.image_url && !this.uploadedFile) {
                this.previewImage = this.form.image_url;
            }
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.uploadedFile = file;
            const reader = new FileReader();
            reader.onload = (ev) => { this.previewImage = ev.target.result; };
            reader.readAsDataURL(file);
        },

        handleDrop(e) {
            document.getElementById('dropZone').classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            // Put file into the input
            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.fileInput.files = dt.files;
            this.uploadedFile = file;
            const reader = new FileReader();
            reader.onload = (ev) => { this.previewImage = ev.target.result; };
            reader.readAsDataURL(file);
        },

        submitForm(e) {
            e.target.submit();
        },
    };
}

// Toggle switch
document.getElementById('is_active_check').addEventListener('change', function() {
    const bg    = document.getElementById('toggle-bg');
    const knob  = document.getElementById('toggle-knob');
    const label = document.getElementById('toggle-label');
    if (this.checked) {
        bg.style.background = '#6366f1';
        knob.style.transform = 'translateX(20px)';
        label.textContent = 'Active — visible in marketplace';
    } else {
        bg.style.background = 'rgba(255,255,255,.1)';
        knob.style.transform = 'translateX(0)';
        label.textContent = 'Inactive — hidden from players';
    }
});
</script>

</body>
</html>
