<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Automation — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        .au-card { background:#110f28; border:1px solid rgba(255,255,255,0.08); border-radius:1rem; }
        .au-input { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:.6rem;
                    color:#fff; font-size:12px; font-weight:700; padding:.4rem .5rem; width:100%; }
        .au-input:focus { outline:none; border-color:rgba(99,102,241,.6); }
        .au-th { font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; padding:.4rem .5rem; text-align:left; }
        .au-td { padding:.35rem .35rem; vertical-align:middle; }
        .au-btn { font-size:11px; font-weight:900; padding:.45rem .9rem; border-radius:.6rem; cursor:pointer; transition:all .15s; }
    </style>
</head>
<body class="text-white min-h-screen">

@include('gameset.partials.topnav', ['active' => 'quests'])

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div>
            <h1 class="text-2xl font-black">🤖 Automation</h1>
            <p class="text-xs text-gray-500 mt-1">The game generating itself: NPC contracts from player state + auto-drafted quests from your content. Tune it here, then walk away.</p>
        </div>
        <a href="{{ route('gameset.quests.index') }}" class="text-xs font-bold text-indigo-300 hover:text-white transition-colors">← Back to Quests</a>
    </div>

    @if(session('success'))
    <div class="my-4 rounded-2xl px-4 py-3 text-sm font-bold text-emerald-300" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="my-4 rounded-2xl px-4 py-3 text-xs font-bold text-red-300" style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.25);">
        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
    </div>
    @endif

    @if(!$enabled)
    <div class="my-6 rounded-2xl px-5 py-4 text-sm font-bold text-amber-300" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);">
        ⚠️ The contracts migration hasn't run yet — run <code class="text-amber-200">php artisan migrate</code> to enable this page.
    </div>
    @endif

    {{-- ── Onboarding guide: how this control room works ── --}}
    <details class="au-card my-5" style="overflow:hidden;">
        <summary class="p-4 cursor-pointer select-none flex items-center gap-2 text-sm font-black text-white" style="list-style:none;">
            📖 New here? How the automation control room works <span class="text-[10px] font-bold text-gray-500">(click to expand)</span>
        </summary>
        <div class="px-5 pb-5 text-[12px] leading-relaxed text-gray-400 space-y-3">
            <p><b class="text-white">The golden rule:</b> everything generated on this page lands as a <b class="text-amber-300">draft</b> first (quests in the review queue, life events switched OFF). Nothing reaches players until you approve it. When you trust a generator, flip on Auto-publish in the Factory card — quests only.</p>

            <div class="grid sm:grid-cols-2 gap-3">
                <div class="rounded-xl p-3" style="background:rgba(124,58,237,0.06);border:1px solid rgba(124,58,237,0.2);">
                    <b class="text-violet-300">🎲 Quest Mixer</b> — the one-button composer. Pick a level range and a target per level; it <i>invents</i> unique quests (combos from your real content, money values sized off your job salaries, NPC-voiced copy). Press it any time — it only fills levels below target, never duplicates.
                </div>
                <div class="rounded-xl p-3" style="background:rgba(5,150,105,0.06);border:1px solid rgba(5,150,105,0.2);">
                    <b class="text-emerald-300">🎲 Life Events Mixer</b> — composes surprise events (windfalls, shocks, market swings, credit moves, career moments, story moments) for every life chapter, amounts scaled to each. Approve them in GameSet → Life Events by toggling ON.
                </div>
                <div class="rounded-xl p-3" style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.2);">
                    <b class="text-indigo-300">🧬 Blueprints</b> — reusable recipes for <i>ladders</i>: one recipe repeats up the levels with growing values (Saver's Staircase Lv 1→9). Chain 🔗 makes each rung require the previous one. The nightly sweep prints missing rungs automatically.
                </div>
                <div class="rounded-xl p-3" style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);">
                    <b class="text-amber-300">🏭 Quest Factory</b> — reactive line: every course or job you create auto-drafts its own quest ("study X", "get hired at Y"). Runs by itself while the Factory switch is on.
                </div>
                <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
                    <b class="text-gray-200">📜 Contract Rules</b> — personal NPC contracts generated per player from their own situation (overdue bills, low mood, uncollected pay). You tune the recipe per age/level band; the game does the rest. No approval needed — they're ephemeral side-tasks.
                </div>
                <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
                    <b class="text-gray-200">⛰️ Quest Gate</b> — the switch in the Factory card that makes quests <i>required</i>: players can't level past a level with unfinished quests (XP banks up and applies once cleared; nobody is ever demoted).
                </div>
            </div>

            <p><b class="text-white">A healthy weekly routine:</b> ① add any new courses/jobs (Factory drafts their quests) → ② press both Mixers → ③ review the <a href="{{ route('gameset.quests.generated') }}" class="text-indigo-300 font-bold">🎲 Generated Quests page</a> and <a href="{{ route('gameset.life-events.index') }}" class="text-emerald-300 font-bold">life event drafts</a>, publish the good ones → ④ done — sweeps and contracts run themselves overnight.</p>
        </div>
    </details>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-5">
        @foreach([
            ['💼', number_format($stats['active']), 'Active contracts right now'],
            ['🎉', number_format($stats['completed_week']), 'Completed this week'],
            ['🌫️', number_format($stats['expired_week']), 'Expired this week'],
            ['📝', number_format($pendingDrafts), 'Factory drafts awaiting review'],
        ] as [$ic, $val, $lbl])
        <div class="au-card p-4">
            <div class="text-xl">{{ $ic }}</div>
            <div class="text-xl font-black text-white mt-1">{{ $val }}</div>
            <div class="text-[10px] text-gray-500 font-semibold">{{ $lbl }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Quest Factory settings ── --}}
    <div class="au-card p-5 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex-1 min-w-[16rem]">
                <h2 class="text-sm font-black text-white">🏭 Quest Factory</h2>
                <p class="text-[11px] text-gray-500 mt-1">Every course or job you create auto-drafts its quest — copy written by the NPC cast, difficulty and age targeting inherited from the content itself. Jobs with required courses become a two-step "study → get hired" chain.</p>
                @if($pendingDrafts > 0)
                <a href="{{ route('gameset.quests.generated') }}"
                   class="inline-block mt-2 text-[11px] font-black px-3 py-1.5 rounded-lg text-amber-300" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);">
                    📝 {{ $pendingDrafts }} draft{{ $pendingDrafts === 1 ? '' : 's' }} waiting — review & publish →
                </a>
                @endif
            </div>
            <form method="POST" action="{{ route('gameset.automation.factory') }}" class="flex flex-col gap-2">
                @csrf
                <label class="flex items-center gap-2 text-xs font-bold text-gray-300 cursor-pointer">
                    <input type="checkbox" name="factory_enabled" value="1" @checked($factoryEnabled) class="rounded border-white/20 bg-white/5">
                    Factory on — draft quests from new content
                </label>
                <label class="flex items-center gap-2 text-xs font-bold text-gray-300 cursor-pointer">
                    <input type="checkbox" name="factory_autopublish" value="1" @checked($factoryAutopublish) class="rounded border-white/20 bg-white/5">
                    Auto-publish drafts (skip the review queue)
                </label>
                <label class="flex items-center gap-2 text-xs font-bold text-gray-300 cursor-pointer" title="Players cannot level past a level that still has unfinished quests — XP banks up and applies the moment the quests are cleared. Never demotes anyone.">
                    <input type="checkbox" name="quest_gate" value="1" @checked($questGateEnabled) class="rounded border-white/20 bg-white/5">
                    ⛰️ Quest Gate — quests must be completed to level up
                </label>
                <button class="au-btn text-white mt-1" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">Save Factory Settings</button>
            </form>
        </div>
    </div>

    {{-- ── The Mixers: one-button generation, always drafts ── --}}
    <div class="grid lg:grid-cols-2 gap-4 mb-6">

        {{-- Quest Mixer --}}
        <div class="au-card p-5">
            <h2 class="text-sm font-black text-white">🎲 Quest Mixer</h2>
            <p class="text-[11px] text-gray-500 mt-1 mb-4">The quest composer: for every level in range it checks what exists and <b>invents unique quests</b> to top the level up to your target — combos picked from real game content, money values sized off your own job salaries, copy drawn from deep NPC voice pools so nothing reads twice. Everything lands as a <b class="text-amber-300">draft for your review</b>, always.</p>

            <form method="POST" action="{{ route('gameset.automation.mix-quests') }}">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-3">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Level from</label>
                        <input type="number" name="level_min" value="1" min="1" max="20" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Level to</label>
                        <input type="number" name="level_max" value="10" min="1" max="20" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Quests / level</label>
                        <input type="number" name="per_level" value="4" min="1" max="12" class="au-input" title="Levels already at or above this count are skipped">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Difficulty mix</label>
                        <select name="mix" class="au-input">
                            <option value="auto">Auto (harder w/ level)</option>
                            <option value="gentle">Gentle</option>
                            <option value="balanced">Balanced</option>
                            <option value="spicy">Spicy</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">XP easy</label>
                        <input type="number" name="xp_easy" value="15" min="1" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">XP semi</label>
                        <input type="number" name="xp_semi" value="25" min="1" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">XP complex</label>
                        <input type="number" name="xp_complex" value="50" min="1" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">XP growth %/lvl</label>
                        <input type="number" name="xp_growth" value="10" min="0" max="100" class="au-input" title="Each level above 1 adds this % to the XP bases">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Audience — mass-generate for one age group or several <span class="text-gray-600 normal-case font-bold">(each selected group gets its own full set, voiced for that age)</span></label>
                    <div class="flex flex-wrap gap-3 mt-1.5">
                        @foreach(['all' => '🌍 Everyone', '8-12' => '🧒 8–12', '13-17' => '🎒 13–17', '18-25' => '🎓 18–25', '26+' => '💼 26+'] as $agKey => $agLabel)
                        <label class="flex items-center gap-1.5 text-[11px] font-bold text-gray-300 cursor-pointer">
                            <input type="checkbox" name="age_groups[]" value="{{ $agKey }}" @checked($agKey === 'all') class="rounded border-white/20 bg-white/5"> {{ $agLabel }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <button class="au-btn text-white w-full" style="background:linear-gradient(135deg,#7c3aed,#6366f1);">🎲 Generate Quest Drafts</button>
                <p class="text-[10px] text-gray-600 mt-2">Idempotent: pressing again only fills levels still below target — never duplicates a combo. Review on the <a class="text-indigo-300 font-bold" href="{{ route('gameset.quests.generated') }}">🎲 Generated Quests page</a>.</p>
            </form>
        </div>

        {{-- Life Events Mixer --}}
        <div class="au-card p-5">
            <h2 class="text-sm font-black text-white">🎲 Life Events Mixer</h2>
            <p class="text-[11px] text-gray-500 mt-1 mb-4">Composes surprise life events across <b>every effect type</b> — windfalls &amp; shocks (cash), market swings, credit score moves, new bills and career changes — for every age group, amounts scaled to each. Kenyan flavour, a real lesson in every one. Events arrive <b class="text-amber-300">switched OFF</b>; you approve each in Life Events.</p>

            <form method="POST" action="{{ route('gameset.automation.mix-life-events') }}">
                @csrf
                <div class="mb-3" style="max-width:12rem;">
                    <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">How many to compose</label>
                    <input type="number" name="count" value="20" min="1" max="60" class="au-input">
                </div>
                <button class="au-btn text-white w-full" style="background:linear-gradient(135deg,#059669,#0d9488);">🎲 Generate Life Event Drafts</button>
                <p class="text-[10px] text-gray-600 mt-2">Each story × age-group variation is composed once — regenerate any time to fill what's missing. Approve in <a class="text-emerald-300 font-bold" href="{{ route('gameset.life-events.index') }}">GameSet → Life Events</a> (toggle ON to publish).</p>
            </form>
        </div>
    </div>

    {{-- ── Quest Blueprints: the quest printing press ── --}}
    @if($blueprintsEnabled)
    <div class="au-card p-5 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
            <div class="flex-1 min-w-[16rem]">
                <h2 class="text-sm font-black text-white">🧬 Quest Blueprints <span class="text-gray-500 font-bold">· the quest printing press</span></h2>
                <p class="text-[11px] text-gray-500 mt-1">A blueprint is a quest <b>recipe</b>: one or more triggers (save Ksh X, take any course + deposit Y, buy an asset…) repeated across a level ladder with growing values and rewards — so quests don't need a course behind them, and the ladder never has gaps. <b>Chain</b> threads the rungs together: the level-5 quest asks you to finish the level-3 one first. The sweep prints every missing rung as a draft (or publishes directly if auto-publish is on).</p>
            </div>
            <form method="POST" action="{{ route('gameset.automation.sweep') }}" class="text-right">
                @csrf
                <button class="au-btn text-white" style="background:linear-gradient(135deg,#059669,#10b981);">🧵 Run sweep now</button>
                <div class="text-[10px] text-gray-600 mt-1.5">Also runs automatically every night.</div>
            </form>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full" style="min-width:860px;">
            <thead>
                <tr>
                    <th class="au-th">Blueprint</th>
                    <th class="au-th">Voice</th>
                    <th class="au-th">Age</th>
                    <th class="au-th">Ladder</th>
                    <th class="au-th">Recipe</th>
                    <th class="au-th">Rewards</th>
                    <th class="au-th">Printed</th>
                    <th class="au-th">On</th>
                    <th class="au-th"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($blueprints as $bp)
                @php
                    $bpSlots = $bp->slots();
                    $bpEdit  = [
                        'id' => $bp->id, 'name' => $bp->name, 'archetype' => $bp->archetype,
                        'icon' => $bp->icon, 'age_group' => $bp->age_group,
                        'career_fields' => $bp->career_fields ?? [],
                        'level_min' => $bp->level_min, 'level_max' => $bp->level_max, 'level_step' => $bp->level_step,
                        'chain' => (bool) $bp->chain, 'is_active' => (bool) $bp->is_active,
                        'steps' => $bp->steps ?? [],
                        'xp_base' => $bp->xp_base, 'xp_per_level' => $bp->xp_per_level,
                        'kes_base' => $bp->kes_base, 'kes_per_level' => $bp->kes_per_level,
                    ];
                @endphp
                <tr style="border-top:1px solid rgba(255,255,255,0.05);" class="{{ $bp->is_active ? '' : 'opacity-50' }}">
                    <td class="au-td">
                        <div class="text-xs font-black text-white whitespace-nowrap">{{ $bp->icon ?: ($archetypes[$bp->archetype]['icon'] ?? '📜') }} {{ $bp->name }}</div>
                        @if($bp->chain)<span class="text-[9px] font-black text-violet-300" title="Each rung requires finishing the previous one">🔗 chained series</span>@endif
                    </td>
                    <td class="au-td text-[10px] font-bold text-gray-400 whitespace-nowrap">{{ $archetypes[$bp->archetype]['icon'] ?? '' }} {{ \Illuminate\Support\Str::headline($bp->archetype) }}</td>
                    <td class="au-td text-[10px] font-bold text-gray-400">{{ $bp->age_group === 'all' ? '🌍 All' : $bp->age_group }}</td>
                    <td class="au-td text-[10px] font-bold text-gray-300 whitespace-nowrap" title="Levels {{ $bp->level_min }}–{{ $bp->level_max }}, one quest every {{ $bp->level_step }} level(s)">
                        Lv {{ $bp->level_min }}→{{ $bp->level_max }} <span class="text-gray-600">/{{ $bp->level_step }}</span>
                        <span class="text-gray-600">· {{ count($bpSlots) }} rung{{ count($bpSlots) === 1 ? '' : 's' }}</span>
                    </td>
                    <td class="au-td">
                        <div class="flex flex-wrap gap-1" style="max-width:260px;">
                            @foreach(($bp->steps ?? []) as $step)
                            @php
                                $stepSummary = match ($step['value_mode'] ?? 'none') {
                                    'curve' => number_format((int) ($step['value_base'] ?? 0)) . ' +' . number_format((int) ($step['value_per_level'] ?? 0)) . '/lvl',
                                    'fixed' => $step['value_fixed'] ?? '',
                                    'any'   => 'any',
                                    default => '',
                                };
                            @endphp
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded text-gray-300" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.09);">
                                {{ $triggerMeta[$step['type']]['name'] ?? $step['type'] }}{{ $stepSummary !== '' ? ' · ' . $stepSummary : '' }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="au-td text-[10px] font-bold text-gray-400 whitespace-nowrap">{{ number_format($bp->xp_base) }}xp +{{ $bp->xp_per_level }}/lvl<br>Ksh {{ number_format($bp->kes_base) }} +{{ $bp->kes_per_level }}/lvl</td>
                    <td class="au-td">
                        <span class="text-xs font-black {{ $bp->quests_count >= count($bpSlots) ? 'text-emerald-300' : 'text-amber-300' }}" title="Quests printed / ladder rungs">
                            {{ $bp->quests_count }}/{{ count($bpSlots) }}
                        </span>
                    </td>
                    <td class="au-td">
                        <form method="POST" action="{{ route('gameset.automation.blueprints.toggle', $bp) }}">
                            @csrf
                            <button class="au-btn" style="padding:.2rem .5rem;background:{{ $bp->is_active ? 'rgba(16,185,129,0.15)' : 'rgba(255,255,255,0.06)' }};border:1px solid {{ $bp->is_active ? 'rgba(16,185,129,0.4)' : 'rgba(255,255,255,0.12)' }};color:{{ $bp->is_active ? '#6ee7b7' : '#9ca3af' }};" title="{{ $bp->is_active ? 'Pause — sweeps skip this blueprint' : 'Activate' }}">{{ $bp->is_active ? 'ON' : 'off' }}</button>
                        </form>
                    </td>
                    <td class="au-td whitespace-nowrap">
                        <button type="button" data-bp="{{ json_encode($bpEdit) }}" onclick="bpEdit(this)"
                                class="au-btn text-indigo-300" style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.35);">Edit</button>
                        <form method="POST" action="{{ route('gameset.automation.blueprints.destroy', $bp) }}" class="inline"
                              onsubmit="return confirm('Delete this blueprint? Quests it already printed stay in the game as normal quests.')">
                            @csrf @method('DELETE')
                            <button class="au-btn text-red-300/80 hover:text-red-300" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);">✕</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($blueprints->isEmpty())
                <tr><td colspan="9" class="au-td text-center text-xs text-gray-500 py-6">No blueprints yet — add one below and the sweep does the rest.</td></tr>
                @endif
            </tbody>
        </table>
        </div>

        <button type="button" id="bp-new-btn" onclick="bpNew()"
                class="au-btn text-emerald-300 mt-4" style="background:rgba(16,185,129,0.1);border:1px dashed rgba(16,185,129,0.4);">➕ New blueprint</button>

        {{-- Create / edit form (hidden until opened) --}}
        <div id="bp-form-wrap" style="display:none;" class="mt-4 rounded-2xl p-4" data-form-open="0" >
            <form id="bp-form" method="POST" action="{{ route('gameset.automation.blueprints.store') }}">
                @csrf
                <input type="hidden" name="_method" id="bp-method" value="POST">
                <div class="flex items-center justify-between mb-3">
                    <h3 id="bp-form-title" class="text-xs font-black text-white">➕ New Blueprint</h3>
                    <button type="button" onclick="bpCloseForm()" class="text-gray-500 hover:text-white text-sm font-black">✕</button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                    <div class="col-span-2">
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Name (admin only)</label>
                        <input name="name" id="bp-name" required maxlength="100" class="au-input" placeholder="e.g. Saver's Staircase">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Voice archetype</label>
                        <select name="archetype" id="bp-archetype" class="au-input">
                            @foreach($archetypes as $key => $arch)
                            <option value="{{ $key }}">{{ $arch['icon'] }} {{ \Illuminate\Support\Str::headline($key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Icon (optional)</label>
                        <input name="icon" id="bp-icon" maxlength="10" class="au-input" placeholder="🏦">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-3">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Age group</label>
                        <select name="age_group" id="bp-age" class="au-input">
                            @foreach(['all' => '🌍 All', '8-12' => '8–12', '13-17' => '13–17', '18-25' => '18–25', '26+' => '26+'] as $v => $lbl)
                            <option value="{{ $v }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Level from</label>
                        <input type="number" name="level_min" id="bp-lmin" value="1" min="1" max="20" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Level to</label>
                        <input type="number" name="level_max" id="bp-lmax" value="9" min="1" max="20" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Every N levels</label>
                        <input type="number" name="level_step" id="bp-lstep" value="2" min="1" max="10" class="au-input" title="Print one quest every N levels within the range">
                    </div>
                    <div class="col-span-2 flex items-end gap-4 pb-1">
                        <label class="flex items-center gap-1.5 text-[11px] font-bold text-gray-300 cursor-pointer" title="Each rung requires finishing the previous rung first (adds a 'Finish X first' step)">
                            <input type="checkbox" name="chain" id="bp-chain" value="1" class="rounded border-white/20 bg-white/5"> 🔗 Chain rungs
                        </label>
                        <label class="flex items-center gap-1.5 text-[11px] font-bold text-gray-300 cursor-pointer">
                            <input type="checkbox" name="is_active" id="bp-active" value="1" checked class="rounded border-white/20 bg-white/5"> Active
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">XP base</label>
                        <input type="number" name="xp_base" id="bp-xpb" value="100" min="0" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">XP + per level</label>
                        <input type="number" name="xp_per_level" id="bp-xpl" value="40" min="0" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Ksh base</label>
                        <input type="number" name="kes_base" id="bp-kesb" value="50" min="0" class="au-input">
                    </div>
                    <div>
                        <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Ksh + per level</label>
                        <input type="number" name="kes_per_level" id="bp-kesl" value="25" min="0" class="au-input">
                    </div>
                </div>

                <div class="mb-1 flex items-center justify-between">
                    <label class="text-[9px] font-black uppercase tracking-wider text-gray-500">Recipe steps <span class="text-gray-600 normal-case font-bold">(the player must do ALL of them · values with a curve grow per rung)</span></label>
                </div>
                <div id="bp-steps"></div>
                <button type="button" onclick="bpAddStep()" class="au-btn text-violet-300 mt-1" style="background:rgba(124,58,237,0.1);border:1px dashed rgba(124,58,237,0.4);">+ Add step</button>

                <div class="mt-4 flex gap-2">
                    <button class="au-btn text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);" id="bp-submit">Save Blueprint</button>
                    <button type="button" onclick="bpCloseForm()" class="au-btn text-gray-400" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const META = {!! json_encode(collect($triggerMeta)->map(fn ($m) => ['value' => $m['value'], 'name' => $m['name']])) !!};
        const STORE_URL  = '{{ route('gameset.automation.blueprints.store') }}';
        const UPDATE_URL = '{{ route('gameset.automation.blueprints.update', ['blueprint' => 0]) }}';
        let stepIdx = 0;

        const MODE_OPTIONS = {
            money: [['curve', 'Curve: base + per-level'], ['fixed', 'Fixed amount']],
            level: [['fixed', 'Fixed level']],
            pick:  [['any', 'Any — no specific value'], ['fixed', 'Specific value (slug / id)']],
            none:  [['none', 'No value needed']],
        };

        function typeOptions(selected) {
            return Object.entries(META).map(([k, m]) =>
                `<option value="${k}" ${k === selected ? 'selected' : ''}>${m.name}</option>`).join('');
        }

        window.bpAddStep = function (step) {
            step = step || {};
            const i = stepIdx++;
            const row = document.createElement('div');
            row.className = 'bp-step';
            row.style.cssText = 'display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;padding:10px;border-radius:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);margin-bottom:6px;';
            row.innerHTML =
                `<select name="steps[${i}][type]" class="au-input" style="width:230px;" onchange="bpTypeChanged(this)">${typeOptions(step.type || 'reach_savings')}</select>` +
                `<select name="steps[${i}][value_mode]" class="au-input" style="width:190px;" onchange="bpModeChanged(this)"></select>` +
                `<input name="steps[${i}][value_fixed]" class="au-input bp-fixed" style="width:130px;" placeholder="value" value="${step.value_fixed ?? ''}">` +
                `<input type="number" name="steps[${i}][value_base]" class="au-input bp-base" style="width:110px;" min="0" placeholder="base Ksh" value="${step.value_base ?? ''}" title="Value at the first rung">` +
                `<input type="number" name="steps[${i}][value_per_level]" class="au-input bp-perlvl" style="width:110px;" min="0" placeholder="+/level" value="${step.value_per_level ?? ''}" title="Added per level above 1">` +
                `<input name="steps[${i}][label]" class="au-input" style="flex:1;min-width:180px;" maxlength="200" placeholder="Custom player label (optional — {amount}/{value} auto-fill)" value="${(step.label ?? '').replace(/"/g, '&quot;')}">` +
                `<button type="button" onclick="this.closest('.bp-step').remove()" style="width:24px;height:24px;border-radius:50%;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.25);color:#f87171;font-size:11px;cursor:pointer;">✕</button>`;
            document.getElementById('bp-steps').appendChild(row);
            bpTypeChanged(row.querySelector('select'), step.value_mode);
        };

        window.bpTypeChanged = function (typeSel, keepMode) {
            const row  = typeSel.closest('.bp-step');
            const kind = (META[typeSel.value] || {value: 'none'}).value;
            const modeSel = row.querySelectorAll('select')[1];
            modeSel.innerHTML = MODE_OPTIONS[kind].map(([v, l]) => `<option value="${v}">${l}</option>`).join('');
            if (keepMode && MODE_OPTIONS[kind].some(([v]) => v === keepMode)) modeSel.value = keepMode;
            bpModeChanged(modeSel);
        };

        window.bpModeChanged = function (modeSel) {
            const row  = modeSel.closest('.bp-step');
            const mode = modeSel.value;
            row.querySelector('.bp-fixed').style.display  = mode === 'fixed' ? '' : 'none';
            row.querySelector('.bp-base').style.display   = mode === 'curve' ? '' : 'none';
            row.querySelector('.bp-perlvl').style.display = mode === 'curve' ? '' : 'none';
        };

        function openForm() {
            const wrap = document.getElementById('bp-form-wrap');
            wrap.style.display = 'block';
            wrap.style.background = 'rgba(99,102,241,0.05)';
            wrap.style.border = '1px solid rgba(99,102,241,0.25)';
            wrap.scrollIntoView({behavior: 'smooth', block: 'nearest'});
        }

        window.bpCloseForm = function () {
            document.getElementById('bp-form-wrap').style.display = 'none';
        };

        window.bpNew = function () {
            const f = document.getElementById('bp-form');
            f.action = STORE_URL;
            document.getElementById('bp-method').value = 'POST';
            document.getElementById('bp-form-title').textContent = '➕ New Blueprint';
            document.getElementById('bp-submit').textContent = 'Create Blueprint';
            f.reset();
            document.getElementById('bp-active').checked = true;
            document.getElementById('bp-steps').innerHTML = '';
            bpAddStep({type: 'reach_savings', value_mode: 'curve', value_base: 300, value_per_level: 250});
            openForm();
        };

        window.bpEdit = function (btn) {
            const bp = JSON.parse(btn.dataset.bp);
            const f  = document.getElementById('bp-form');
            f.action = UPDATE_URL.replace(/0$/, bp.id);
            document.getElementById('bp-method').value = 'PUT';
            document.getElementById('bp-form-title').textContent = '✏️ Edit: ' + bp.name;
            document.getElementById('bp-submit').textContent = 'Save Changes';
            document.getElementById('bp-name').value  = bp.name;
            document.getElementById('bp-archetype').value = bp.archetype;
            document.getElementById('bp-icon').value  = bp.icon || '';
            document.getElementById('bp-age').value   = bp.age_group;
            document.getElementById('bp-lmin').value  = bp.level_min;
            document.getElementById('bp-lmax').value  = bp.level_max;
            document.getElementById('bp-lstep').value = bp.level_step;
            document.getElementById('bp-chain').checked  = !!bp.chain;
            document.getElementById('bp-active').checked = !!bp.is_active;
            document.getElementById('bp-xpb').value  = bp.xp_base;
            document.getElementById('bp-xpl').value  = bp.xp_per_level;
            document.getElementById('bp-kesb').value = bp.kes_base;
            document.getElementById('bp-kesl').value = bp.kes_per_level;
            document.getElementById('bp-steps').innerHTML = '';
            (bp.steps || []).forEach(s => bpAddStep(s));
            openForm();
        };
    })();
    </script>
    @else
    <div class="my-6 rounded-2xl px-5 py-4 text-sm font-bold text-amber-300" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.3);">
        ⚠️ The quest blueprints migration hasn't run yet — run <code class="text-amber-200">php artisan migrate</code> to unlock the printing press.
    </div>
    @endif

    {{-- ── Contract rules ── --}}
    <div class="au-card p-5 mb-6 overflow-x-auto">
        <h2 class="text-sm font-black text-white">📜 Contract Rules</h2>
        <p class="text-[11px] text-gray-500 mt-1 mb-4">How NPC contracts assemble per age group and level band: how many objectives each contract bundles (randomized in the min–max range), whether players must finish <b>all</b> of them or <b>any N</b>, how long they get, and the payout. The narrowest matching rule wins; a specific age group beats "All".</p>

        {{-- Row forms live OUTSIDE the table (a <form> can't span <td>s);
             inputs reference them via the form="" attribute --}}
        @foreach($rules as $rule)
        <form id="rule-{{ $rule->id }}" method="POST" action="{{ route('gameset.automation.rules.update', $rule) }}">@csrf @method('PUT')</form>
        @endforeach
        <form id="rule-new" method="POST" action="{{ route('gameset.automation.rules.store') }}">@csrf</form>

        <table class="w-full" style="min-width:900px;">
            <thead>
                <tr>
                    <th class="au-th">Age</th>
                    <th class="au-th">Levels</th>
                    <th class="au-th">Objectives</th>
                    <th class="au-th">Mode</th>
                    <th class="au-th">Need</th>
                    <th class="au-th">Days</th>
                    <th class="au-th">Held</th>
                    <th class="au-th">XP</th>
                    <th class="au-th">Ksh</th>
                    <th class="au-th">On</th>
                    <th class="au-th"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rules as $rule)
                @php $fid = 'rule-' . $rule->id; @endphp
                <tr style="border-top:1px solid rgba(255,255,255,0.05);" class="{{ $rule->is_active ? '' : 'opacity-50' }}">
                    <td class="au-td" style="width:90px;">
                        <select name="age_group" form="{{ $fid }}" class="au-input">
                            @foreach(['all' => '🌍 All', '8-12' => '8–12', '13-17' => '13–17', '18-25' => '18–25', '26+' => '26+'] as $v => $lbl)
                            <option value="{{ $v }}" @selected($rule->age_group === $v)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="au-td" style="width:110px;">
                        <div class="flex items-center gap-1">
                            <input type="number" name="level_min" form="{{ $fid }}" value="{{ $rule->level_min }}" min="1" max="99" class="au-input" style="width:44px;">
                            <span class="text-gray-600">–</span>
                            <input type="number" name="level_max" form="{{ $fid }}" value="{{ $rule->level_max }}" min="1" max="99" class="au-input" style="width:44px;">
                        </div>
                    </td>
                    <td class="au-td" style="width:110px;">
                        <div class="flex items-center gap-1">
                            <input type="number" name="objectives_min" form="{{ $fid }}" value="{{ $rule->objectives_min }}" min="2" max="6" class="au-input" style="width:44px;">
                            <span class="text-gray-600">–</span>
                            <input type="number" name="objectives_max" form="{{ $fid }}" value="{{ $rule->objectives_max }}" min="2" max="6" class="au-input" style="width:44px;">
                        </div>
                    </td>
                    <td class="au-td" style="width:86px;">
                        <select name="completion_mode" form="{{ $fid }}" class="au-input">
                            <option value="any" @selected($rule->completion_mode === 'any')>Any N</option>
                            <option value="all" @selected($rule->completion_mode === 'all')>All</option>
                        </select>
                    </td>
                    <td class="au-td" style="width:56px;"><input type="number" name="required_count" form="{{ $fid }}" value="{{ $rule->required_count }}" min="1" max="6" class="au-input" title="Objectives needed when mode is Any N"></td>
                    <td class="au-td" style="width:56px;"><input type="number" name="duration_days" form="{{ $fid }}" value="{{ $rule->duration_days }}" min="2" max="60" class="au-input" title="Game days before the contract expires"></td>
                    <td class="au-td" style="width:52px;"><input type="number" name="active_contracts" form="{{ $fid }}" value="{{ $rule->active_contracts }}" min="1" max="4" class="au-input" title="Contracts a player holds at once"></td>
                    <td class="au-td" style="width:76px;"><input type="number" name="reward_xp" form="{{ $fid }}" value="{{ $rule->reward_xp }}" min="0" class="au-input"></td>
                    <td class="au-td" style="width:76px;"><input type="number" name="reward_kes" form="{{ $fid }}" value="{{ $rule->reward_kes }}" min="0" class="au-input"></td>
                    <td class="au-td"><input type="checkbox" name="is_active" form="{{ $fid }}" value="1" @checked($rule->is_active) class="rounded border-white/20 bg-white/5"></td>
                    <td class="au-td whitespace-nowrap">
                        <button form="{{ $fid }}" class="au-btn text-indigo-300" style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.35);">Save</button>
                        <form method="POST" action="{{ route('gameset.automation.rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Delete this rule?')">
                            @csrf @method('DELETE')
                            <button class="au-btn text-red-300/80 hover:text-red-300" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);">✕</button>
                        </form>
                    </td>
                </tr>
                @endforeach

                {{-- Add new rule --}}
                <tr style="border-top:1px dashed rgba(255,255,255,0.12);">
                    <td class="au-td">
                        <select name="age_group" form="rule-new" class="au-input">
                            @foreach(['all' => '🌍 All', '8-12' => '8–12', '13-17' => '13–17', '18-25' => '18–25', '26+' => '26+'] as $v => $lbl)
                            <option value="{{ $v }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="au-td"><div class="flex items-center gap-1"><input type="number" name="level_min" form="rule-new" value="1" min="1" max="99" class="au-input" style="width:44px;"><span class="text-gray-600">–</span><input type="number" name="level_max" form="rule-new" value="99" min="1" max="99" class="au-input" style="width:44px;"></div></td>
                    <td class="au-td"><div class="flex items-center gap-1"><input type="number" name="objectives_min" form="rule-new" value="3" min="2" max="6" class="au-input" style="width:44px;"><span class="text-gray-600">–</span><input type="number" name="objectives_max" form="rule-new" value="4" min="2" max="6" class="au-input" style="width:44px;"></div></td>
                    <td class="au-td"><select name="completion_mode" form="rule-new" class="au-input"><option value="any">Any N</option><option value="all">All</option></select></td>
                    <td class="au-td"><input type="number" name="required_count" form="rule-new" value="2" min="1" max="6" class="au-input"></td>
                    <td class="au-td"><input type="number" name="duration_days" form="rule-new" value="7" min="2" max="60" class="au-input"></td>
                    <td class="au-td"><input type="number" name="active_contracts" form="rule-new" value="2" min="1" max="4" class="au-input"></td>
                    <td class="au-td"><input type="number" name="reward_xp" form="rule-new" value="200" min="0" class="au-input"></td>
                    <td class="au-td"><input type="number" name="reward_kes" form="rule-new" value="250" min="0" class="au-input"></td>
                    <td class="au-td"><input type="checkbox" name="is_active" form="rule-new" value="1" checked class="rounded border-white/20 bg-white/5"></td>
                    <td class="au-td"><button form="rule-new" class="au-btn text-emerald-300" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);">+ Add</button></td>
                </tr>
            </tbody>
        </table>
        <p class="text-[10px] text-gray-600 mt-3">💡 <b>Need</b> only applies to "Any N" mode. Objectives are picked from what's relevant to each player right now (overdue bills, uncollected pay, low mood…) with urgent problems prioritized — targets scale to the player's own numbers, so the same rule stays fair at every wealth level.</p>
    </div>

    {{-- ── The cast (read-only showcase) ── --}}
    <div class="grid md:grid-cols-2 gap-4 mb-8">
        <div class="au-card p-5">
            <h2 class="text-sm font-black text-white mb-3">🎭 The Cast</h2>
            <div class="space-y-2.5">
                @foreach($npcs as $key => $npc)
                <div class="flex items-start gap-3">
                    <span class="text-2xl">{{ $npc['emoji'] }}</span>
                    <div>
                        <div class="text-xs font-black text-white">{{ $npc['name'] }}</div>
                        <div class="text-[10px] text-gray-500">{{ $npc['role'] }} · issues: {{ collect($npc['domains'])->map(fn ($d) => $archetypes[$d]['icon'] ?? '')->join(' ') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            <p class="text-[10px] text-gray-600 mt-3">Voices live in <code>config/pesa_voice.php</code> — edit copy there; no database needed.</p>
        </div>
        <div class="au-card p-5">
            <h2 class="text-sm font-black text-white mb-3">🧩 Objective Archetypes ({{ count($archetypes) }})</h2>
            <div class="flex flex-wrap gap-1.5">
                @foreach($archetypes as $key => $arch)
                <span class="text-[10px] font-bold px-2 py-1 rounded-lg text-gray-300" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);" title="metric: {{ $arch['metric'] }}">
                    {{ $arch['icon'] }} {{ \Illuminate\Support\Str::headline($key) }}
                </span>
                @endforeach
            </div>
            <p class="text-[10px] text-gray-600 mt-3">Each archetype watches a live metric (courses finished, savings balance, paydays collected…). Progress is measured against a snapshot taken when the contract is issued — nothing to wire up, nothing to desync.</p>
        </div>
    </div>
</div>

</body>
</html>
