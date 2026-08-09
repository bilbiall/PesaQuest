<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'New Bill' : 'Edit: '.$bill->name }} — GameSet</title>
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
        .form-input:focus { border-color:rgba(16,185,129,.6); box-shadow:0 0 0 3px rgba(16,185,129,.1); }
        .form-label { display:block; font-size:11px; font-weight:700; letter-spacing:.06em; color:#6b7280; text-transform:uppercase; margin-bottom:.35rem; }
        .section-card { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.07); border-radius:1.25rem; padding:1.5rem; margin-bottom:1.25rem; }
        .section-title { font-size:.75rem; font-weight:900; letter-spacing:.1em; color:#10b981; text-transform:uppercase; margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; }
        select.form-input option { background:#0f172a; color:#fff; }
        textarea.form-input { min-height:80px; resize:vertical; }
        .toggle-track { width:2.5rem; height:1.25rem; border-radius:9999px; transition:background .2s; cursor:pointer; position:relative; }
        .toggle-thumb { position:absolute; top:.125rem; left:.125rem; width:1rem; height:1rem; border-radius:50%; background:#fff; transition:transform .2s; }
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('gameset.bills.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Bills
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">{{ $mode === 'create' ? 'New Bill' : 'Edit: '.$bill->name }}</span>
        </div>
        @if($mode === 'edit')
        <form method="POST" action="{{ route('gameset.bills.destroy', $bill) }}"
              onsubmit="return confirm('Delete this bill template?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-400 border border-red-500/20 hover:border-red-500/40 px-3 py-1.5 rounded-lg transition-colors">
                🗑 Delete
            </button>
        </form>
        @endif
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8" x-data="billForm()">

    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm text-red-300" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);">
        <div class="font-bold mb-1">Please fix these errors:</div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $mode === 'create' ? route('gameset.bills.store') : route('gameset.bills.update', $bill) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="grid lg:grid-cols-3 gap-5">

            {{-- LEFT COLUMN --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Basic info --}}
                <div class="section-card">
                    <div class="section-title">🗓 Bill Details</div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Bill Name *
                                <x-help-tip text="What the player sees on their Life HQ Bills Board — name it like a real Kenyan household bill." example="Bedsitter Rent" />
                            </label>
                            <input name="name" value="{{ old('name', $bill?->name) }}" required
                                   class="form-input" placeholder="e.g. Monthly Rent">
                        </div>
                        <div>
                            <label class="form-label">Slug (auto-generated if blank)
                                <x-help-tip text="Stable internal key used to link this bill to assets and quests — leave blank to auto-generate from the name." example="bedsitter-rent" />
                            </label>
                            <input name="slug" value="{{ old('slug', $bill?->slug) }}"
                                   class="form-input" placeholder="monthly-rent">
                        </div>
                        <div>
                            <label class="form-label">Icon (emoji) *
                                <x-help-tip text="Small emoji shown next to the bill name on the Bills Board and payment notifications." example="🏠" />
                            </label>
                            <input name="icon" value="{{ old('icon', $bill?->icon ?? '💸') }}"
                                   class="form-input" placeholder="🏠" maxlength="10">
                        </div>
                        <div>
                            <label class="form-label">Category *
                                <x-help-tip text="Groups the bill for filtering and stats, and links it to the matching asset type — e.g. buying a vehicle auto-attaches a transport-category bill." example="housing" />
                            </label>
                            <select name="category" class="form-input" required>
                                @foreach(['housing'=>'🏠 Housing','transport'=>'🚗 Transport','utilities'=>'💡 Utilities','food'=>'🍽 Food','healthcare'=>'🏥 Healthcare','education'=>'🎒 Education','social'=>'🤝 Social','entertainment'=>'🎮 Entertainment','tax'=>'🧾 Tax'] as $k => $v)
                                <option value="{{ $k }}" {{ old('category', $bill?->category) === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Age Group *
                                <x-help-tip text="Restricts who can be assigned this bill — younger players get pocket-money-scale bills, adults get rent and utilities." example="18-25" />
                            </label>
                            <select name="age_group" class="form-input" required>
                                @foreach(['all'=>'All ages','8-12'=>'8-12','13-17'=>'13-17','18-25'=>'18-25','26+'=>'26+'] as $k => $v)
                                <option value="{{ $k }}" {{ old('age_group', $bill?->age_group ?? 'all') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Amount & Frequency --}}
                <div class="section-card">
                    <div class="section-title">💰 Amount & Schedule</div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Amount (Ksh) *
                                <x-help-tip text="How much the player is charged each time this bill is due — scale it to the age band's typical income." example="6500" />
                            </label>
                            <input name="amount" type="number" min="0" required
                                   value="{{ old('amount', $bill?->amount ?? 0) }}"
                                   class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Frequency *
                                <x-help-tip text="How often the bill repeats, in game days (ticks) — 30 for a monthly bill like rent, 7 for a weekly one like airtime." example="30" />
                            </label>
                            <select name="frequency_ticks" class="form-input" required>
                                @foreach([7=>'Weekly (7 days)',14=>'Fortnightly (14 days)',30=>'Monthly (30 days)',90=>'Termly (90 days)',182=>'Every 6 months',365=>'Annually (365 days)'] as $ticks => $lbl)
                                <option value="{{ $ticks }}" {{ old('frequency_ticks', $bill?->frequency_ticks ?? 30) == $ticks ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Credit Score on Pay
                                <x-help-tip text="Credit score points awarded when the player pays this bill on time — leave at 0 if this bill shouldn't affect credit." example="5" />
                            </label>
                            <input name="credit_impact_pay" type="number" min="-100" max="100"
                                   value="{{ old('credit_impact_pay', $bill?->credit_impact_pay ?? 5) }}"
                                   class="form-input">
                            <p class="text-[10px] text-gray-600 mt-1">+5 to +15 = reward for paying. Leave 0 if not credit-linked.</p>
                        </div>
                        <div>
                            <label class="form-label">Credit Score on Miss
                                <x-help-tip text="Credit score points deducted when the player lets this bill go overdue — make essentials sting harder than optional ones." example="-20" />
                            </label>
                            <input name="credit_impact_miss" type="number" min="-100" max="100"
                                   value="{{ old('credit_impact_miss', $bill?->credit_impact_miss ?? -20) }}"
                                   class="form-input">
                            <p class="text-[10px] text-gray-600 mt-1">-20 to -50 = penalty for missing. Usually negative.</p>
                        </div>
                    </div>
                </div>

                {{-- Trigger & Chapter --}}
                <div class="section-card">
                    <div class="section-title">⚙️ Trigger & Assignment</div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Trigger Type
                                <x-help-tip text="Planning metadata describing how this bill is meant to start — actual assignment is driven by Auto-assign, Age Group and Minimum Chapter below (or the asset's category for asset-triggered bills)." example="immediate" />
                            </label>
                            <select name="trigger" class="form-input">
                                <option value="immediate" {{ old('trigger', $bill?->trigger ?? 'immediate') === 'immediate' ? 'selected' : '' }}>Immediate (on game start)</option>
                                <option value="chapter"   {{ old('trigger', $bill?->trigger) === 'chapter'   ? 'selected' : '' }}>Chapter unlock</option>
                                <option value="net_worth" {{ old('trigger', $bill?->trigger) === 'net_worth' ? 'selected' : '' }}>Net worth threshold</option>
                                <option value="asset"     {{ old('trigger', $bill?->trigger) === 'asset'     ? 'selected' : '' }}>Asset purchase</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Minimum Chapter
                                <x-help-tip text="The life chapter a player must reach before this bill can be assigned to them — combined with Auto-assign, this is what actually gates the bill." example="The Student" />
                            </label>
                            <select name="min_chapter" class="form-input">
                                @foreach(\App\Models\UserProgress::chapters() as $c)
                                <option value="{{ $c['key'] }}" {{ old('min_chapter', $bill?->min_chapter ?? 'student') === $c['key'] ? 'selected' : '' }}>
                                    {{ $c['icon'] }} {{ $c['name'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-3 leading-relaxed">
                        <strong class="text-gray-400">Minimum Chapter</strong> is what actually gates this bill (as long as <b>Auto-assign</b> below is checked) — the player only picks it up once their life chapter reaches this stage or later, so bills grow as the player grows. Leave at "The Student" for a bill that's relevant from day one.<br>
                        <strong class="text-gray-400">Trigger Type</strong> below is descriptive/planning metadata for this content team — actual assignment logic is Auto-assign + age group + Minimum Chapter (or, for <b>Asset</b>-triggered bills, the asset's <code class="text-amber-400">creates_bill_slug</code> field).
                    </p>
                </div>

                {{-- Descriptions --}}
                <div class="section-card">
                    <div class="section-title">📝 Text & Flavour</div>
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Description
                                <x-help-tip text="Internal notes for the content team about what this bill represents — not shown to players." example="Standard room rent for an 18-25 bedsitter tenant." />
                            </label>
                            <textarea name="description" class="form-input">{{ old('description', $bill?->description) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Flavour Text (shown to player)
                                <x-help-tip text="The 'why this bill?' line the player sees on the Bills Board — teach the real-life lesson, don't just restate the charge." example="Your landlord Mama Otis expects rent by the 5th. Housing takes the biggest slice of most Kenyan budgets." />
                            </label>
                            <textarea name="flavor_text" class="form-input">{{ old('flavor_text', $bill?->flavor_text) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Consequence Text (shown when missed)
                                <x-help-tip text="The warning shown once this bill goes overdue — state the real-life parallel to make the credit-score hit feel earned." example="Late rent strains the one relationship that keeps a roof over you." />
                            </label>
                            <textarea name="consequence_text" class="form-input">{{ old('consequence_text', $bill?->consequence_text) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Toggles + Preview --}}
            <div class="space-y-4">

                {{-- Toggles --}}
                <div class="section-card">
                    <div class="section-title">🔧 Behaviour</div>
                    <div class="space-y-4">

                        @foreach([
                            ['field'=>'is_essential', 'label'=>'Essential Bill', 'desc'=>'Missing this bill has severe consequences — e.g. eviction, power cut.', 'default'=>false, 'help'=>'Flags this bill as a life essential, so missing it should trigger a harsher in-fiction consequence and a bigger Credit Score on Miss penalty than an optional bill.'],
                            ['field'=>'auto_assign',  'label'=>'Auto-Assign',    'desc'=>'Automatically attach this bill to eligible players when they log in.', 'default'=>true, 'help'=>'When on, eligible players (matching Age Group and Minimum Chapter) get this bill automatically — turn it off for bills that should only appear through a specific quest or event.'],
                            ['field'=>'is_active',    'label'=>'Active',         'desc'=>'Inactive bills are hidden from players and ignored by the simulator.', 'default'=>true, 'help'=>'Lets you pause a bill without deleting it — inactive bills stop appearing for new assignments and are skipped by the billing simulator.'],
                        ] as $toggle)
                        @php $val = old($toggle['field'], $bill ? (bool)$bill->{$toggle['field']} : $toggle['default']); @endphp
                        <div x-data="{ on: {{ $val ? 'true' : 'false' }} }">
                            <div class="flex items-center justify-between mb-1">
                                <div>
                                    <div class="text-sm font-bold text-white">{{ $toggle['label'] }}<x-help-tip :text="$toggle['help']" /></div>
                                    <div class="text-[10px] text-gray-500 leading-relaxed mt-0.5">{{ $toggle['desc'] }}</div>
                                </div>
                                <button type="button" @click="on = !on"
                                        class="toggle-track shrink-0 ml-3"
                                        :style="on ? 'background:#10b981' : 'background:rgba(255,255,255,.15)'">
                                    <span class="toggle-thumb" :style="on ? 'transform:translateX(1.25rem)' : ''"></span>
                                </button>
                            </div>
                            <input type="hidden" name="{{ $toggle['field'] }}" :value="on ? '1' : '0'">
                        </div>
                        @endforeach

                    </div>
                </div>

                {{-- Live preview --}}
                <div class="section-card">
                    <div class="section-title">👁 Bill Preview</div>
                    <div class="rounded-xl p-3 border border-white/10" style="background:rgba(239,68,68,.06);">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="text-xl" x-text="icon">💸</span>
                            <div>
                                <div class="text-xs font-bold text-white">Monthly Rent</div>
                                <div class="text-[10px] text-amber-400">Due in 3 days</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-gray-500">Amount</span>
                            <span class="text-sm font-black text-white">Ksh …</span>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-600 mt-3 leading-relaxed">
                        This is how the bill appears in the player's Bills Board on their Life Board.
                        Overdue bills show a red border and Pay button.
                    </p>
                </div>

                {{-- Save --}}
                <button type="submit"
                        class="w-full py-3.5 rounded-2xl font-black text-white text-sm transition-all hover:scale-[1.02]"
                        style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 20px rgba(16,185,129,.35);">
                    {{ $mode === 'create' ? '✅ Create Bill' : '💾 Save Changes' }}
                </button>
                <a href="{{ route('gameset.bills.index') }}"
                   class="block text-center text-sm text-gray-500 hover:text-white mt-2 transition-colors">
                    Cancel
                </a>
            </div>

        </div>
    </form>
</div>

<script>
function billForm() {
    return {
        icon: '{{ old('icon', $bill?->icon ?? '💸') }}',
    };
}
</script>
</body>
</html>
