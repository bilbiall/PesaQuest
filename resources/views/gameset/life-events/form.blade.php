<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'New Life Event' : 'Edit: '.($event?->title ?? '') }} — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        .form-input {
            width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            color:#fff; border-radius:.75rem; padding:.6rem 1rem; font-size:.875rem;
            outline:none; transition:border-color .2s;
        }
        .form-input:focus { border-color:rgba(139,92,246,.6); box-shadow:0 0 0 3px rgba(139,92,246,.1); }
        .form-label { display:block; font-size:11px; font-weight:700; letter-spacing:.06em; color:#6b7280; text-transform:uppercase; margin-bottom:.35rem; }
        .section-card { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.07); border-radius:1.25rem; padding:1.5rem; margin-bottom:1.25rem; }
        .section-title { font-size:.75rem; font-weight:900; letter-spacing:.1em; color:#8b5cf6; text-transform:uppercase; margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; }
        select.form-input option { background:#0f172a; color:#fff; }
        textarea.form-input { resize:vertical; }
        input[type=range] { -webkit-appearance:none; width:100%; height:5px; border-radius:3px; background:rgba(255,255,255,.1); }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance:none; width:16px; height:16px; border-radius:50%; background:#8b5cf6; cursor:pointer; box-shadow:0 0 0 3px rgba(139,92,246,.3); }
        .toggle-track { width:2.5rem; height:1.25rem; border-radius:9999px; transition:background .2s; cursor:pointer; position:relative; display:inline-block; }
        .toggle-thumb { position:absolute; top:.125rem; left:.125rem; width:1rem; height:1rem; border-radius:50%; background:#fff; transition:transform .2s; }
        .effect-section { transition:all .2s; }
        .edu-tip { background:rgba(139,92,246,.06); border:1px solid rgba(139,92,246,.15); border-radius:.75rem; padding:.75rem 1rem; font-size:.75rem; color:#c4b5fd; line-height:1.6; }
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('gameset.life-events.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Life Events
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">{{ $mode === 'create' ? 'New Event' : 'Edit Event' }}</span>
        </div>
        @if($mode === 'edit')
        <form method="POST" action="{{ route('gameset.life-events.destroy', $event) }}"
              onsubmit="return confirm('Delete this event?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-400 border border-red-500/20 hover:border-red-500/40 px-3 py-1.5 rounded-lg transition-colors">
                🗑 Delete
            </button>
        </form>
        @endif
    </div>
</nav>

@php
    $ed   = $event?->effect_data ?? [];
    $etype = old('effect_type', $event?->effect_type ?? 'balance_delta');
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8"
     x-data="eventForm('{{ $etype }}', {{ (float)old('probability', $event?->probability ?? 0.015) }})">

    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm text-red-300" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);">
        <div class="font-bold mb-1">Please fix:</div>
        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $mode === 'create' ? route('gameset.life-events.store') : route('gameset.life-events.update', $event) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="grid lg:grid-cols-3 gap-5">

            {{-- LEFT: Main fields --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Identity --}}
                <div class="section-card">
                    <div class="section-title">⚡ Event Identity</div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Event Title *<x-help-tip text="The headline players see when this event fires — write it like a mini-news alert, in plain Kenyan English." example="Tenant Missed Rent This Month" /></label>
                            <input name="title" value="{{ old('title', $event?->title) }}" required
                                   class="form-input" placeholder="e.g. Tenant Missed Rent This Month">
                        </div>
                        <div>
                            <label class="form-label">Slug (auto-generated if blank)<x-help-tip text="A unique machine-readable ID for this event; leave blank and it is auto-generated from the title. Changing an existing slug on a live event can break anything referencing it." example="matatu-fare-hike" /></label>
                            <input name="slug" value="{{ old('slug', $event?->slug) }}"
                                   class="form-input" placeholder="tenant-missed-rent">
                        </div>
                        <div>
                            <label class="form-label">Icon (emoji)<x-help-tip text="The emoji shown next to this event in the Life Feed and player timeline — pick one that hints at the story." example="🚌" /></label>
                            <input name="icon" value="{{ old('icon', $event?->icon ?? '⚡') }}"
                                   class="form-input" placeholder="🏠" maxlength="10">
                        </div>
                        <div>
                            <label class="form-label">Chapter *<x-help-tip text="Which life stage this event is eligible to fire for — pick a specific age band to match the hardship to that stage, or All chapters to fire for every player." example="13-17" /></label>
                            <select name="chapter" class="form-input" required>
                                @foreach(['all'=>'All chapters','8-12'=>'8-12','13-17'=>'13-17','18-25'=>'18-25','26+'=>'26+'] as $k => $v)
                                <option value="{{ $k }}" {{ old('chapter', $event?->chapter ?? 'all') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Asset Category (leave blank = fires for all)<x-help-tip text="Restricts this event to players who currently own an asset in this category — leave blank so the event can fire for every player regardless of what they own." example="vehicle" /></label>
                            <select name="asset_category" class="form-input">
                                <option value="">General (no asset required)</option>
                                @foreach(['vehicle','property','business','investment','fixed_income','gadget'] as $cat)
                                <option value="{{ $cat }}" {{ old('asset_category', $event?->asset_category) === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Effect --}}
                <div class="section-card">
                    <div class="section-title">💥 Effect Configuration</div>

                    <div class="mb-4">
                        <label class="form-label">Effect Type *<x-help-tip text="Chooses which kind of consequence this event applies to the player and switches on the matching effect-data inputs below — each type touches a different part of the player's game state (wallet, credit, assets, bills, or salary)." example="balance_delta" /></label>
                        <select name="effect_type" class="form-input" x-model="effectType" required>
                            <option value="balance_delta"  {{ $etype === 'balance_delta'  ? 'selected' : '' }}>balance_delta — add/remove Ksh from balance</option>
                            <option value="market_event"   {{ $etype === 'market_event'   ? 'selected' : '' }}>market_event — adjust asset values by %</option>
                            <option value="credit_adjust"  {{ $etype === 'credit_adjust'  ? 'selected' : '' }}>credit_adjust — change credit score</option>
                            <option value="bill_assign"    {{ $etype === 'bill_assign'    ? 'selected' : '' }}>bill_assign — add a bill to the player</option>
                            <option value="career_change"  {{ $etype === 'career_change'  ? 'selected' : '' }}>career_change — modify career income</option>
                        </select>
                    </div>

                    {{-- balance_delta fields --}}
                    <div x-show="effectType === 'balance_delta'" class="effect-section">
                        <div class="edu-tip mb-4">
                            The simulator picks a random amount between <strong>balance_min</strong> and <strong>balance_max</strong> on each fire.
                            Use negative numbers for expenses (e.g. -12000, -5000). Positive for income windfalls.
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Minimum (Ksh) *<x-help-tip text="Saved as effect_data.balance_min — the low end of the random Ksh amount added to (or removed from) the player's wallet when this event fires." example="-12000" /></label>
                                <input name="ed_balance_min" type="number"
                                       value="{{ old('ed_balance_min', $ed['balance_min'] ?? 0) }}"
                                       class="form-input" placeholder="-12000">
                            </div>
                            <div>
                                <label class="form-label">Maximum (Ksh) *<x-help-tip text="Saved as effect_data.balance_max — the high end of the random Ksh range; the simulator rolls a random value between min and max each time this event fires." example="-5000" /></label>
                                <input name="ed_balance_max" type="number"
                                       value="{{ old('ed_balance_max', $ed['balance_max'] ?? 0) }}"
                                       class="form-input" placeholder="-5000">
                            </div>
                        </div>
                    </div>

                    {{-- market_event fields --}}
                    <div x-show="effectType === 'market_event'" class="effect-section">
                        <div class="edu-tip mb-4">
                            Multiplies the current_value of all player assets in the given category by (1 + pct).
                            Use 0.09 for +9%, -0.08 for -8%. Only fires if player owns assets in that category.
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Asset Category to affect<x-help-tip text="Saved inside effect_data.market_categories as the category key — only players holding assets in this category feel the price swing." example="investment" /></label>
                                <select name="ed_market_category" class="form-input">
                                    @foreach(['vehicle','property','business','investment','fixed_income','gadget'] as $cat)
                                    <option value="{{ $cat }}" {{ old('ed_market_category', $event?->effect_data['market_categories'][0]['category'] ?? 'investment') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label">% Change (decimal, e.g. 0.09 = +9%)<x-help-tip text="Saved as effect_data.market_categories[].pct — multiplies affected assets' current_value by (1 + this number); use a negative decimal for a crash, positive for a rally." example="0.09" /></label>
                                <input name="ed_market_pct" type="number" step="0.01" min="-1" max="1"
                                       value="{{ old('ed_market_pct', $event?->effect_data['market_categories'][0]['pct'] ?? 0) }}"
                                       class="form-input" placeholder="0.09">
                            </div>
                        </div>
                    </div>

                    {{-- credit_adjust fields --}}
                    <div x-show="effectType === 'credit_adjust'" class="effect-section">
                        <div class="edu-tip mb-4">
                            Adjusts credit_score by a random amount in the range. Negative = credit damage. Range: -200 to +200.
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Min credit change<x-help-tip text="Saved as effect_data.credit_min — the low end of the random credit_score adjustment; use a negative number for credit damage." example="-30" /></label>
                                <input name="ed_credit_min" type="number"
                                       value="{{ old('ed_credit_min', $ed['credit_min'] ?? 0) }}"
                                       class="form-input" placeholder="-30">
                            </div>
                            <div>
                                <label class="form-label">Max credit change<x-help-tip text="Saved as effect_data.credit_max — the high end of the random credit_score adjustment; the simulator rolls a random value between min and max." example="-10" /></label>
                                <input name="ed_credit_max" type="number"
                                       value="{{ old('ed_credit_max', $ed['credit_max'] ?? 0) }}"
                                       class="form-input" placeholder="-10">
                            </div>
                        </div>
                    </div>

                    {{-- bill_assign fields --}}
                    <div x-show="effectType === 'bill_assign'" class="effect-section">
                        <div class="edu-tip mb-4">
                            Attaches a bill template to the player by slug. The bill must exist in the Bills table.
                        </div>
                        <div>
                            <label class="form-label">Bill slug to assign<x-help-tip text="Saved as effect_data.bill_slug — attaches an existing bill template to the player by its slug; the slug must already exist in the Bills table or nothing gets assigned." example="monthly-rent" /></label>
                            <input name="ed_bill_slug" type="text"
                                   value="{{ old('ed_bill_slug', $ed['bill_slug'] ?? '') }}"
                                   class="form-input" placeholder="monthly-rent">
                        </div>
                    </div>

                    {{-- career_change fields --}}
                    <div x-show="effectType === 'career_change'" class="effect-section">
                        <div class="edu-tip mb-4">
                            Changes career_income_rate by a random amount. Use positive for promotion, negative for pay cut.
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Min income delta (Ksh)<x-help-tip text="Saved as effect_data.income_delta_min — the low end of the random change to the player's career_income_rate; use a negative number for a pay cut." example="5000" /></label>
                                <input name="ed_income_min" type="number"
                                       value="{{ old('ed_income_min', $ed['income_delta_min'] ?? 0) }}"
                                       class="form-input" placeholder="5000">
                            </div>
                            <div>
                                <label class="form-label">Max income delta (Ksh)<x-help-tip text="Saved as effect_data.income_delta_max — the high end of the random change to the player's career_income_rate; the simulator rolls a random value between min and max." example="15000" /></label>
                                <input name="ed_income_max" type="number"
                                       value="{{ old('ed_income_max', $ed['income_delta_max'] ?? 0) }}"
                                       class="form-input" placeholder="15000">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Probability --}}
                <div class="section-card">
                    <div class="section-title">🎲 Probability</div>
                    <label class="form-label">Fire rate per tick (0 – 1)<x-help-tip text="The chance this event fires for an eligible player on any given game day — higher values mean players run into it far more often." example="0.012" /></label>
                    <input type="range" min="0" max="0.1" step="0.001"
                           x-model="prob" @input="prob = parseFloat($event.target.value).toFixed(3)"
                           class="mb-3">
                    <input type="hidden" name="probability" :value="prob">
                    <div class="flex items-center gap-3 text-sm">
                        <span class="font-black text-violet-400" x-text="prob"></span>
                        <span class="text-gray-500">= approx. <span x-text="Math.round(prob * 100 * 30)"></span> fires per 100 players per game-month</span>
                    </div>
                    <p class="text-[11px] text-gray-600 mt-2 leading-relaxed">
                        0.01 = fires ~30 times per 100 players per month. 0.003 = rare (once every few months).
                        Keep harambee events ≈ 0.015, asset disasters ≈ 0.012, lucky windfalls ≈ 0.008.
                    </p>
                </div>

                {{-- Descriptions --}}
                <div class="section-card">
                    <div class="section-title">📝 Narrative & Education</div>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Description (shown to player)<x-help-tip text="The plain-language explanation shown to the player when the event fires — describe what happened in a sentence or two." example="Matatu fares doubled this week because of a fuel price spike." /></label>
                            <textarea name="description" class="form-input" style="min-height:80px">{{ old('description', $event?->description) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Flavour Text (Swahili/local voice)<x-help-tip text="A short quoted voice-line shown alongside the event for local flavor — keep it Kenyan, human, and brief." example="Beba beba! Mafuta imepanda, si mimi." /></label>
                            <textarea name="flavor_text" class="form-input" style="min-height:70px">{{ old('flavor_text', $event?->flavor_text) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Educational Note (Mama Pesa tip)<x-help-tip text="The financial-literacy lesson tied to this event — this is what the player actually learns, so keep it concrete and actionable." example="Keep a small transport buffer so a fare hike never touches your savings." /></label>
                            <textarea name="educational_note" class="form-input" style="min-height:90px">{{ old('educational_note', $event?->educational_note) }}</textarea>
                            <p class="text-[10px] text-gray-600 mt-1">This is what the player learns. Keep it concrete, Kenyan, actionable.</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-4">

                {{-- Toggles --}}
                <div class="section-card">
                    <div class="section-title">🔧 Flags</div>
                    <div class="space-y-4">
                        @foreach([
                            ['field'=>'is_positive', 'label'=>'Positive Event', 'desc'=>'Green highlight in Life Feed. Use for income, windfalls, good news.', 'default'=>false, 'tip'=>'Colours this event green in the Life Feed and marks it as good news for stats — use for income, windfalls, and other positive outcomes.', 'example'=>'Chama dividend payout'],
                            ['field'=>'is_active',   'label'=>'Active',          'desc'=>'Inactive events are skipped by rollLifeEvents(). Safe to disable without deleting.', 'default'=>true, 'tip'=>'Inactive events are skipped by the event roller entirely — turn this off to pause an event without deleting it or its history.', 'example'=>'off to pause a seasonal event'],
                        ] as $t)
                        @php $val = old($t['field'], $event ? (bool)$event->{$t['field']} : $t['default']); @endphp
                        <div x-data="{ on: {{ $val ? 'true' : 'false' }} }">
                            <div class="flex items-center justify-between mb-1">
                                <div>
                                    <div class="text-sm font-bold text-white">{{ $t['label'] }}<x-help-tip :text="$t['tip']" :example="$t['example']" /></div>
                                    <div class="text-[10px] text-gray-500 leading-relaxed mt-0.5">{{ $t['desc'] }}</div>
                                </div>
                                <button type="button" @click="on = !on"
                                        class="toggle-track shrink-0 ml-3"
                                        :style="on ? 'background:#8b5cf6' : 'background:rgba(255,255,255,.15)'">
                                    <span class="toggle-thumb" :style="on ? 'transform:translateX(1.25rem)' : ''"></span>
                                </button>
                            </div>
                            <input type="hidden" name="{{ $t['field'] }}" :value="on ? '1' : '0'">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Effect type guide --}}
                <div class="section-card">
                    <div class="section-title">📖 Effect Types</div>
                    <div class="space-y-3 text-[11px] text-gray-400 leading-relaxed">
                        <div><span class="font-bold text-amber-400">balance_delta</span> — instant Ksh add/remove. Most common. Use for bills, windfalls, emergencies.</div>
                        <div><span class="font-bold text-blue-400">market_event</span> — multiplies asset values. Good for NSE rallies, property crashes.</div>
                        <div><span class="font-bold text-pink-400">credit_adjust</span> — raises or lowers credit score. Use for late payment events.</div>
                        <div><span class="font-bold text-emerald-400">bill_assign</span> — attaches a recurring bill. E.g. "You moved to a bigger house" → rent bill assigned.</div>
                        <div><span class="font-bold text-violet-400">career_change</span> — adjusts monthly salary. Use for promotions, pay cuts, job changes.</div>
                    </div>
                </div>

                {{-- Save --}}
                <button type="submit"
                        class="w-full py-3.5 rounded-2xl font-black text-white text-sm transition-all hover:scale-[1.02]"
                        style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);box-shadow:0 4px 20px rgba(139,92,246,.35);">
                    {{ $mode === 'create' ? '⚡ Create Event' : '💾 Save Changes' }}
                </button>
                <a href="{{ route('gameset.life-events.index') }}"
                   class="block text-center text-sm text-gray-500 hover:text-white mt-2 transition-colors">
                    Cancel
                </a>
            </div>

        </div>
    </form>
</div>

<script>
function eventForm(initialType, initialProb) {
    return {
        effectType: initialType,
        prob: initialProb.toFixed(3),
    };
}
</script>
</body>
</html>
