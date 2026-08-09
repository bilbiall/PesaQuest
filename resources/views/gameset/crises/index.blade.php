<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crisis Events — Gameset</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak] { display:none !important; }
        .cx-wrap { max-width:70rem; margin:0 auto; padding:1.5rem 1rem 4rem; }
        .cx-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:1.25rem; }
        .cx-lbl { font-size:.68rem; font-weight:800; color:#6b7280; text-transform:uppercase; letter-spacing:.08em; display:block; margin-bottom:.35rem; }
        .cx-field { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:.75rem; color:#fff;
                    padding:.6rem .85rem; width:100%; font-size:.875rem; font-family:inherit; }
        .cx-field:focus { outline:none; border-color:rgba(245,158,11,.55); background:rgba(245,158,11,.05); }
        .cx-field option { background:#1a1a2e; color:#fff; }
        textarea.cx-field { resize:vertical; min-height:70px; }
        .cx-preset { text-align:left; border-radius:.9rem; padding:.65rem .8rem; cursor:pointer; transition:all .15s;
                     background:rgba(245,158,11,.06); border:1px solid rgba(245,158,11,.2); }
        .cx-preset:hover { background:rgba(245,158,11,.14); border-color:rgba(245,158,11,.45); }
        .cx-preset b { display:block; color:#fcd34d; font-size:.8rem; }
        .cx-preset span { color:#9ca3af; font-size:.66rem; }
        .cx-badge { font-size:.62rem; font-weight:900; letter-spacing:.05em; padding:.22rem .55rem; border-radius:999px; white-space:nowrap; }
        .st-scheduled { background:rgba(99,102,241,.15); color:#a5b4fc; border:1px solid rgba(99,102,241,.35); }
        .st-warned    { background:rgba(245,158,11,.15); color:#fcd34d; border:1px solid rgba(245,158,11,.35); }
        .st-active    { background:rgba(239,68,68,.15);  color:#f87171; border:1px solid rgba(239,68,68,.35); }
        .st-done      { background:rgba(107,114,128,.15); color:#9ca3af; border:1px solid rgba(107,114,128,.3); }
        .cx-step { display:flex; gap:.65rem; align-items:flex-start; }
        .cx-step .n { width:1.5rem; height:1.5rem; border-radius:50%; background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.4);
                      color:#fcd34d; font-size:.7rem; font-weight:900; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .cx-step p { color:#9ca3af; font-size:.78rem; line-height:1.4; }
        .cx-step p b { color:#e5e7eb; }
        .cx-submit { width:100%; padding:.8rem; border-radius:.9rem; font-weight:900; font-size:.9rem; color:#fff; cursor:pointer;
                     background:linear-gradient(135deg,#f59e0b,#d97706); border:none; transition:opacity .15s; }
        .cx-submit:disabled { opacity:.5; cursor:wait; }
        .cx-del { font-size:12px; font-weight:700; padding:.35rem .85rem; border-radius:.6rem; background:rgba(248,113,113,.1);
                  border:1px solid rgba(248,113,113,.2); color:#fca5a5; cursor:pointer; }
        .cx-del:hover { background:rgba(248,113,113,.2); }
        .cx-grid { display:grid; grid-template-columns:1fr; gap:1.25rem; }
        @media (min-width:900px) { .cx-grid { grid-template-columns:5fr 7fr; } }
    </style>
</head>
<body class="text-white min-h-screen">

@include('gameset.partials.topnav', ['active' => 'crises'])

<div class="cx-wrap" x-data="crisisMgr()">

    {{-- Header --}}
    <div style="margin-bottom:1.5rem;">
        <h1 style="font-size:1.45rem;font-weight:900;color:#fff;">🌪️ Financial Crisis Events</h1>
        <p style="color:#9ca3af;font-size:.85rem;margin-top:.3rem;max-width:44rem;">
            Trigger server-wide economic shocks. Every player gets a warning notification first, then the effect hits
            their wallet, assets, investments or salary — and the moment lands on their <b style="color:#e5e7eb;">Life Story timeline</b>.
        </p>
    </div>

    {{-- How it works --}}
    <div class="cx-card" style="padding:1.1rem 1.25rem;margin-bottom:1.5rem;display:grid;gap:.8rem;grid-template-columns:repeat(auto-fit,minmax(14rem,1fr));">
        <div class="cx-step"><span class="n">1</span><p><b>📢 Warning time</b> — at this moment every player receives a "crisis incoming" notification so they can prepare (sell, save, stock up). Classic: 48 hours before the hit.</p></div>
        <div class="cx-step"><span class="n">2</span><p><b>💥 Hit time</b> — the effect applies once to all players when the active window opens. Salary cuts also keep reducing salaries until the window closes.</p></div>
        <div class="cx-step"><span class="n">3</span><p><b>📖 Aftermath</b> — each impacted player sees the crisis in notifications and on their /life/timeline with a financial lesson attached.</p></div>
    </div>

    <div class="cx-grid">
        {{-- CREATE FORM --}}
        <div>
            <div class="cx-card" style="padding:1.25rem;">
                <h2 style="font-weight:900;font-size:1rem;color:#fff;margin-bottom:.75rem;">⚡ Schedule a Crisis</h2>

                {{-- Presets --}}
                <span class="cx-lbl">Start from a preset
                    <x-help-tip text="One click pre-fills the name, icon, description, effect type, and severity below with a ready-made scenario — you still set the three schedule times yourself before scheduling." example="NSE Market Crash pre-fills Investment Drop at 25%" />
                </span>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem;">
                    <template x-for="(p, i) in presets" :key="i">
                        <button type="button" class="cx-preset" @click="applyPreset(p)">
                            <b><span x-text="p.icon"></span> <span x-text="p.name"></span></b>
                            <span x-text="effectTypes[p.effect_type].label + ' · ' + p.effect_amount + '%'"></span>
                        </button>
                    </template>
                </div>

                <div style="display:grid;grid-template-columns:4rem 1fr;gap:.6rem;margin-bottom:.75rem;">
                    <div>
                        <span class="cx-lbl">Icon
                            <x-help-tip text="The emoji shown next to the crisis name in player notifications and on the scheduled-crisis list — defaults to a warning sign if left blank." example="📉" />
                        </span>
                        <input type="text" x-model="form.icon" maxlength="4" class="cx-field" style="text-align:center;font-size:1.1rem;">
                    </div>
                    <div>
                        <span class="cx-lbl">Crisis name
                            <x-help-tip text="The headline players see in their crisis warning notification and on their Life Story timeline entry." example="NSE Market Crash" />
                        </span>
                        <input type="text" x-model="form.name" maxlength="120" class="cx-field" placeholder="e.g. NSE Market Crash">
                    </div>
                </div>

                <div style="margin-bottom:.75rem;">
                    <span class="cx-lbl">Description (players see this in the warning)
                        <x-help-tip text="The explanation shown to players in the warning notification — describe what's happening in the economy and why it matters so they can prepare." example="Foreign investors pull out of the NSE, and pending investment deals lose a quarter of their value overnight." />
                    </span>
                    <textarea x-model="form.description" maxlength="500" class="cx-field" placeholder="What is happening in the economy and why it matters..."></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.75rem;">
                    <div>
                        <span class="cx-lbl">Effect
                            <x-help-tip text="Chooses which part of a player's economy takes the hit: Investment Drop shrinks pending investment deals, Asset Crash cuts the value of owned assets, Balance Drain skims a percentage off cash wallets, and Salary Cut reduces job pay for as long as the crisis window stays open." example="Balance Drain" />
                        </span>
                        <select x-model="form.effect_type" class="cx-field">
                            <template x-for="(meta, key) in effectTypes" :key="key">
                                <option :value="key" x-text="meta.icon + ' ' + meta.label"></option>
                            </template>
                        </select>
                        <p style="font-size:.66rem;color:#6b7280;margin-top:.3rem;" x-text="effectTypes[form.effect_type]?.hint"></p>
                    </div>
                    <div>
                        <span class="cx-lbl">Severity (%)
                            <x-help-tip text="The percentage of value removed when the crisis hits — 5-10% reads as a mild squeeze, 20% and up is a painful shock players will feel for the rest of the game week." example="15" />
                        </span>
                        <input type="number" x-model.number="form.effect_amount" min="0.5" max="90" step="0.5" class="cx-field">
                        <p style="font-size:.66rem;color:#6b7280;margin-top:.3rem;">Percentage lost. 5–10% = mild, 20%+ = painful.</p>
                    </div>
                </div>

                <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.6rem;">
                    <button type="button" @click="quickTimes(48)" style="font-size:.68rem;font-weight:800;padding:.35rem .7rem;border-radius:.6rem;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;cursor:pointer;">⏱️ Classic: warn now, hit in 48h</button>
                    <button type="button" @click="quickTimes(24)" style="font-size:.68rem;font-weight:800;padding:.35rem .7rem;border-radius:.6rem;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;cursor:pointer;">⚡ Fast: warn now, hit in 24h</button>
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:.6rem;margin-bottom:1rem;">
                    <div>
                        <span class="cx-lbl">📢 Warning goes out at
                            <x-help-tip text="The moment every player receives the 'crisis incoming' notification so they can prepare — sell assets, stash savings, or stock up — before the hit lands." example="48 hours before the hit time" />
                        </span>
                        <input type="datetime-local" x-model="form.warning_at" class="cx-field">
                    </div>
                    <div>
                        <span class="cx-lbl">💥 Crisis hits at
                            <x-help-tip text="The moment the effect actually applies to every player at once — instant for Investment Drop, Asset Crash, and Balance Drain, or the start of the pay-cut window for Salary Cut." example="2 days after the warning" />
                        </span>
                        <input type="datetime-local" x-model="form.active_from" class="cx-field">
                    </div>
                    <div>
                        <span class="cx-lbl">🕊️ Crisis ends at
                            <x-help-tip text="Closes the crisis window. Only Salary Cut keeps acting until this moment — it reduces every salary collected while active — the other effect types are one-shot at hit time." example="24 hours after the crisis hits" />
                        </span>
                        <input type="datetime-local" x-model="form.active_until" class="cx-field">
                    </div>
                </div>

                <div x-show="error" x-cloak style="margin-bottom:.75rem;color:#f87171;font-size:.78rem;font-weight:700;" x-text="error"></div>

                <button type="button" class="cx-submit" :disabled="saving" @click="submit()">
                    <span x-show="!saving">🌪️ Schedule Crisis</span>
                    <span x-show="saving">Scheduling…</span>
                </button>
            </div>
        </div>

        {{-- LIST --}}
        <div>
            <div class="cx-card" style="padding:1.25rem;">
                <h2 style="font-weight:900;font-size:1rem;color:#fff;margin-bottom:.9rem;">📅 Scheduled & Past Crises</h2>

                @forelse($crises as $crisis)
                    <div style="display:flex;gap:.8rem;align-items:flex-start;padding:.85rem 0;border-bottom:1px solid rgba(255,255,255,.05);" id="crisis-row-{{ $crisis->id }}">
                        <span style="font-size:1.5rem;flex-shrink:0;">{{ $crisis->icon }}</span>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                                <b style="color:#fff;font-size:.9rem;">{{ $crisis->name }}</b>
                                <span class="cx-badge st-{{ $crisis->statusKey() }}">{{ $crisis->statusLabel() }}</span>
                            </div>
                            <div style="color:#9ca3af;font-size:.72rem;margin-top:.2rem;">{{ $crisis->effectLabel() }}</div>
                            <div style="color:#6b7280;font-size:.68rem;margin-top:.25rem;">
                                📢 {{ $crisis->warning_at->format('d M H:i') }}
                                · 💥 {{ $crisis->active_from->format('d M H:i') }}
                                · 🕊️ {{ $crisis->active_until->format('d M H:i') }}
                            </div>
                        </div>
                        @if(!$crisis->is_processed)
                            <button type="button" class="cx-del" @click="destroy({{ $crisis->id }})">Cancel</button>
                        @endif
                    </div>
                @empty
                    <div style="text-align:center;padding:2.5rem 1rem;">
                        <div style="font-size:2.5rem;">🌤️</div>
                        <p style="color:#9ca3af;font-size:.85rem;margin-top:.5rem;">No crises scheduled. The economy is calm.</p>
                        <p style="color:#6b7280;font-size:.72rem;margin-top:.25rem;">Use a preset on the left to schedule your first economic event.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function crisisMgr() {
    return {
        presets: @json($presets),
        effectTypes: @json($effectTypes),
        saving: false,
        error: '',
        form: { name: '', icon: '⚠️', description: '', effect_type: 'balance_drain', effect_amount: 10, warning_at: '', active_from: '', active_until: '' },

        applyPreset(p) {
            this.form.name          = p.name;
            this.form.icon          = p.icon;
            this.form.description   = p.description;
            this.form.effect_type   = p.effect_type;
            this.form.effect_amount = p.effect_amount;
            if (!this.form.warning_at) this.quickTimes(48);
        },

        fmt(d) {
            const p = n => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
        },

        quickTimes(hoursToHit) {
            const now  = new Date();
            const hit  = new Date(now.getTime() + hoursToHit * 3600 * 1000);
            const ends = new Date(hit.getTime() + 24 * 3600 * 1000);
            this.form.warning_at   = this.fmt(new Date(now.getTime() + 5 * 60 * 1000));
            this.form.active_from  = this.fmt(hit);
            this.form.active_until = this.fmt(ends);
        },

        async submit() {
            this.error = '';
            if (!this.form.name.trim() || !this.form.description.trim()) { this.error = 'Name and description are required.'; return; }
            if (!this.form.warning_at || !this.form.active_from || !this.form.active_until) { this.error = 'Set all three times (use a quick-schedule button).'; return; }
            this.saving = true;
            try {
                const res = await fetch('{{ route('gameset.crises.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(this.form),
                });
                if (res.ok) { window.location.reload(); }
                else {
                    const err = await res.json();
                    this.error = Object.values(err.errors || {}).flat().join(' ') || err.message || 'Could not schedule crisis.';
                }
            } catch (e) { this.error = 'Network error.'; }
            finally { this.saving = false; }
        },

        async destroy(id) {
            if (!confirm('Cancel this crisis? Players will not be affected.')) return;
            const res = await fetch(`/gameset/crises/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            });
            if (res.ok) document.getElementById(`crisis-row-${id}`)?.remove();
        },
    }
}
</script>
</body>
</html>
