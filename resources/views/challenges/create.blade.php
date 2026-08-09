<x-app-layout>
<style>
body{background:#07060f;}
.profile-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:1.1rem;padding:1rem;}
label{display:block;font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:.35rem;}
input:not([type=checkbox]):not([type=radio]),select{width:100%;padding:.5rem .7rem;border-radius:.6rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);color:#fff;font-size:.82rem;}
.friend-check{display:flex;align-items:center;gap:.5rem;padding:.4rem .6rem;border-radius:.6rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);cursor:pointer;}
.friend-check:hover{background:rgba(99,102,241,.08);}
.tpl-card{border-radius:.8rem;padding:.6rem;background:rgba(255,255,255,.03);border:2px solid rgba(255,255,255,.08);cursor:pointer;text-align:center;}
.tpl-card.tpl-on{border-color:#818cf8;background:rgba(99,102,241,.12);}
</style>

<div class="min-h-screen px-4 py-6 max-w-2xl mx-auto" style="background:#07060f;">
    <div class="flex items-center gap-4 mb-4 text-sm">
        <a href="{{ route('challenges.index') }}" class="text-gray-400 hover:text-white inline-flex items-center gap-2">← Back to Champions' Court</a>
        <a href="{{ route('world') }}" class="text-gray-500 hover:text-white inline-flex items-center gap-2">🎮 Back to Game</a>
    </div>
    <h1 class="text-xl font-black text-white mb-5">⚔️ Start a Challenge</h1>

    <form method="POST" action="{{ route('challenges.store') }}" class="space-y-4" x-data="{
            scope: '{{ old('scope', $friends->isEmpty() ? 'public' : 'friends') }}',
            mode: 'duel',
            picked: {{ old('template_id', $templates->first()?->id ?? 'null') }},
            templates: {{ $templates->map(fn ($t) => ['id' => $t->id, 'style' => $t->style, 'metric' => $t->metric])->values()->toJson() }},
            get tpl() { return this.templates.find(t => t.id === this.picked) || {}; },
            get goalUnit() {
                if (this.tpl.style === 'percent') return '%';
                if (this.tpl.style === 'count') return '';
                return this.tpl.metric === 'xp_points' ? 'XP' : 'KES';
            },
            get goalPlaceholder() {
                if (this.tpl.style === 'percent') return '10';
                if (this.tpl.style === 'count') return '3';
                return '500';
            },
            get goalHint() {
                const metricLabel = (this.tpl.metric || '').replaceAll('_', ' ');
                if (this.tpl.style === 'percent') return 'How much your ' + metricLabel + ' must grow — a % increase from where you stand when the challenge starts.';
                if (this.tpl.style === 'count') return 'How many you need to reach to win.';
                return 'How much ' + (this.tpl.metric === 'xp_points' ? 'XP' : 'KES') + ' you need to earn during the challenge.';
            },
        }">
        @csrf

        <div class="profile-card">
            <label class="mb-3">Who's this for?</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="tpl-card" :class="scope === 'friends' ? 'tpl-on' : ''">
                    <input type="radio" name="scope" value="friends" x-model="scope" class="hidden">
                    <div class="text-xl mb-0.5">🤝</div>
                    <div class="text-[.72rem] font-black text-white">Friends Duel</div>
                    <div class="text-[.6rem] text-gray-500 mt-0.5">Invite specific friends or teammates</div>
                </label>
                <label class="tpl-card" :class="scope === 'public' ? 'tpl-on' : ''">
                    <input type="radio" name="scope" value="public" x-model="scope" class="hidden">
                    <div class="text-xl mb-0.5">🌍</div>
                    <div class="text-[.72rem] font-black text-white">Public Challenge</div>
                    <div class="text-[.6rem] text-gray-500 mt-0.5">Anyone eligible can join</div>
                </label>
            </div>
            <p x-show="scope === 'public'" x-cloak class="text-[.6rem] text-gray-500 mt-2.5">
                Goes live immediately on the Champions' Court "Open Challenges" list for anyone in the challenge type's level range — join anytime until it ends, ranked by progress at the deadline.
            </p>
            @if($friends->isEmpty())
            <p x-show="scope === 'friends'" x-cloak class="text-[.6rem] text-amber-400 mt-2.5">
                You don't have any friends yet — <a href="{{ route('friends.index') }}" class="underline font-bold">add some</a> to duel them directly, or switch to a Public Challenge above.
            </p>
            @endif
        </div>

        <div class="profile-card">
            <label>Give it a name (optional)</label>
            <input type="text" name="title" value="{{ old('title') }}" minlength="3" maxlength="150" placeholder="e.g. Savings Showdown">
            <p class="text-[.6rem] text-gray-500 mt-1.5">Leave blank to use the challenge type's default name.</p>
        </div>

        <div class="profile-card">
            <label class="mb-2">Choose a Challenge Type</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($templates as $t)
                <label class="tpl-card" :class="picked === {{ $t->id }} ? 'tpl-on' : ''">
                    <input type="radio" name="template_id" value="{{ $t->id }}" x-model.number="picked" class="hidden">
                    <div class="text-xl mb-0.5">{{ $t->icon }}</div>
                    <div class="text-[.72rem] font-black text-white">{{ $t->name }}</div>
                    <div class="text-[.6rem] text-gray-500 mt-0.5">{{ $t->default_duration_days }} days</div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="profile-card">
            <label>Set Your Target (optional)</label>
            <div style="display:flex;align-items:center;gap:.5rem;">
                <input type="number" name="goal" value="{{ old('goal') }}" step="0.01" min="0.01" :placeholder="goalPlaceholder">
                <span class="text-xs font-black text-gray-400" style="flex-shrink:0;min-width:1.6rem;" x-text="goalUnit"></span>
            </div>
            <p class="text-[.6rem] text-gray-500 mt-1.5" x-text="goalHint"></p>
            <p class="text-[.58rem] text-gray-500 mt-1">Leave blank to use the challenge type's default target.</p>
        </div>

        <div class="profile-card" x-show="scope === 'friends'" x-cloak>
            <label class="mb-2">Battle Format</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="tpl-card" :class="mode === 'duel' ? 'tpl-on' : ''">
                    <input type="radio" name="battle_mode" value="duel" x-model="mode" class="hidden">
                    <div class="text-xl mb-0.5">⚔️</div>
                    <div class="text-[.72rem] font-black text-white">1v1 / Team Battle</div>
                    <div class="text-[.6rem] text-gray-500 mt-0.5">Even sides, team average wins</div>
                </label>
                <label class="tpl-card" :class="mode === 'ffa' ? 'tpl-on' : ''">
                    <input type="radio" name="battle_mode" value="ffa" x-model="mode" class="hidden">
                    <div class="text-xl mb-0.5">🎯</div>
                    <div class="text-[.72rem] font-black text-white">Free-for-All</div>
                    <div class="text-[.6rem] text-gray-500 mt-0.5">Everyone races solo, top progress wins</div>
                </label>
            </div>
            <p class="text-[.6rem] text-gray-500 mt-2.5">⚡ This is a <b>race</b> — the instant anyone hits the target above, the challenge ends right there and they're crowned the winner, even if there's time left. (Public Challenges work the opposite way — they always run the full duration, ranked by best progress at the deadline.)</p>
        </div>

        <div x-show="scope === 'friends'" x-cloak>
            <x-friend-picker :friends="$friends" name="opponent_ids[]" label="Opponent(s) — who you're racing against">
                <x-slot:hint>
                    <span x-show="mode === 'duel'">Pick one for a 1v1, or several for a team battle (recruit an equal number of teammates below).</span>
                    <span x-show="mode === 'ffa'" x-cloak>Pick 2 or more — no teams, everyone races on their own progress.</span>
                </x-slot:hint>
            </x-friend-picker>

            <div x-show="mode === 'duel'" class="mt-4" x-cloak>
                <x-friend-picker :friends="$friends" name="teammate_ids[]" label="Recruit Teammates (optional)"
                    hint="Leave empty for a simple 1v1. Both sides must end up with the same number of players." />
            </div>
        </div>

        <div class="profile-card grid grid-cols-2 gap-3">
            <div>
                <label>Entry Fee (KES, optional)</label>
                <input type="number" name="stake_amount" min="0" placeholder="none">
                <p class="text-[.58rem] text-gray-500 mt-1">Winner(s) split 90% of the pool — 10% is forfeited.</p>
            </div>
            <div>
                <label>Duration — real days</label>
                <input type="number" name="duration_days" min="1" max="60" placeholder="template default">
                <p class="text-[.58rem] text-gray-500 mt-1">Real calendar days — not your in-game clock. A 7-day challenge ends 7 real days from now, however fast you play.</p>
            </div>
        </div>

        <div class="profile-card">
            <label class="mb-1">Win Requirements (optional)</label>
            <p class="text-[.6rem] text-gray-500 mb-2.5">Only participants who meet ALL checked requirements can be crowned the winner — makes it genuinely competitive instead of a pure numbers race.</p>
            <div class="space-y-1.5">
                <label class="friend-check">
                    <input type="checkbox" name="requirements[]" value="bills_paid_all" class="!w-auto" style="width:1rem;flex-shrink:0;">
                    <span class="text-xs text-white font-semibold">All bills paid (no overdue bills)</span>
                </label>
                <label class="friend-check">
                    <input type="checkbox" name="requirements[]" value="min_assets" class="!w-auto" style="width:1rem;flex-shrink:0;">
                    <span class="text-xs text-white font-semibold">Own at least</span>
                    <input type="number" name="min_assets_value" value="2" min="1" max="50" style="width:3.5rem;padding:.25rem .4rem;">
                    <span class="text-xs text-white font-semibold">assets</span>
                </label>
                <label class="friend-check">
                    <input type="checkbox" name="requirements[]" value="min_savings" class="!w-auto" style="width:1rem;flex-shrink:0;">
                    <span class="text-xs text-white font-semibold">Save at least KES</span>
                    <input type="number" name="min_savings_value" value="500" min="0" style="width:5rem;padding:.25rem .4rem;">
                </label>
                <label class="friend-check">
                    <input type="checkbox" name="requirements[]" value="debt_free" class="!w-auto" style="width:1rem;flex-shrink:0;">
                    <span class="text-xs text-white font-semibold">Debt-free (no active loans)</span>
                </label>
            </div>
        </div>

        <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-black text-white transition-all hover:scale-[1.01]" style="background:linear-gradient(135deg,#6366f1,#4338ca);">
            <span x-show="scope === 'public'">Launch Public Challenge</span>
            <span x-show="scope === 'friends'" x-cloak>Send Challenge</span>
        </button>
    </form>
</div>
</x-app-layout>
