<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Challenges — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        label { display:block; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:.3rem; }
        select, input { padding:.5rem .7rem; border-radius:.6rem; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.12); color:#fff; font-size:.85rem; width:100%; }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'challenges'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">🏆 Challenges</span>
        </div>
        <a href="{{ route('gameset.challenges.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#6366f1,#4338ca);box-shadow:0 4px 14px rgba(99,102,241,.35);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Template
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    @if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-semibold text-emerald-300 border border-emerald-500/30" style="background:rgba(16,185,129,0.1);">
        ✓ {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl p-5 text-center" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);">
            <div class="text-3xl font-black text-indigo-300">{{ $stats['templates'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Templates</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);">
            <div class="text-3xl font-black text-emerald-400">{{ $stats['active'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Active Now</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);">
            <div class="text-3xl font-black text-amber-400">{{ $stats['official'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">PesaCity Official</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
            <div class="text-3xl font-black text-gray-300">{{ $stats['completed'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Completed</div>
        </div>
    </div>

    {{-- Launch a PesaCity Official Challenge --}}
    <div class="rounded-2xl p-5 mb-8" style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.25);">
        <h2 class="text-lg font-black mb-1">🏆 Launch a PesaCity Challenge</h2>
        <p class="text-xs text-gray-400 mb-4">Publishes an official, open-to-everyone broadcast challenge. Players join it from Champions' Court and get ranked on a live leaderboard until the deadline.</p>
        <form method="POST" action="{{ route('gameset.challenges.launch') }}" class="grid grid-cols-2 sm:grid-cols-6 gap-3 items-end">
            @csrf
            <div class="col-span-2 sm:col-span-2">
                <label>Template</label>
                <select name="template_id" required>
                    @foreach($templates->where('is_active', true) as $t)
                        <option value="{{ $t->id }}">{{ $t->icon }} {{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Target (optional)</label>
                <input type="number" name="goal" step="0.01" min="0.01" placeholder="template default">
            </div>
            <div>
                <label>Duration (real days)</label>
                <input type="number" name="duration_days" min="1" max="60" placeholder="template default">
            </div>
            <div>
                <label>Entry Fee (KES, optional)</label>
                <input type="number" name="stake_amount" min="0" placeholder="none">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-black text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);">Launch</button>
            </div>
            <div class="col-span-2 sm:col-span-6 flex items-center gap-2 mt-1">
                <input type="checkbox" name="is_chama_battle" value="1" id="is_chama_battle" style="width:auto;">
                <label for="is_chama_battle" class="!mb-0 text-xs text-gray-300">⚔️ Make this an <b>Inter-Chama Battle</b> — chairmen enter their whole chama, ranked chama-vs-chama by average member progress, instead of individual players joining.</label>
            </div>
        </form>
    </div>

    {{-- All in-progress challenges (official AND player/teacher/chairman-created) — admin can deactivate any of them --}}
    <h2 class="text-sm font-black uppercase tracking-wide text-gray-400 mb-3">Recent Challenges</h2>
    <div class="rounded-2xl overflow-hidden border border-white/10 mb-8">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-white/5 text-left text-xs uppercase tracking-wide text-gray-400">
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Source</th>
                    <th class="px-4 py-3">Participants</th>
                    <th class="px-4 py-3">Ends</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($challenges as $c)
                <tr class="hover:bg-white/[0.03]">
                    <td class="px-4 py-3 font-bold">{{ $c->title }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $c->is_official ? '🏙️ Official' : ($c->creator?->name ? '👤 '.$c->creator->name : '👤 Player') }}</td>
                    <td class="px-4 py-3 text-gray-300">{{ $c->participants_count }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $c->ends_at?->format('M j, Y') ?? 'All-Time' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 rounded-full text-xs font-black" style="background:{{ $c->status === 'active' ? 'rgba(16,185,129,.15)' : 'rgba(255,255,255,.05)' }};color:{{ $c->status === 'active' ? '#6ee7b7' : '#9ca3af' }};">{{ ucfirst($c->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form action="{{ route('gameset.challenges.cancel', $c) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this challenge? Any entry fee will be refunded.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold">Deactivate</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($challenges->isEmpty())
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No challenges in progress right now.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Templates --}}
    <h2 class="text-sm font-black uppercase tracking-wide text-gray-400 mb-3">Challenge Templates</h2>
    <div class="rounded-2xl overflow-hidden border border-white/10">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-white/5 text-left text-xs uppercase tracking-wide text-gray-400">
                    <th class="px-4 py-3">Template</th>
                    <th class="px-4 py-3">Metric</th>
                    <th class="px-4 py-3">Duration</th>
                    <th class="px-4 py-3">Levels</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($templates as $t)
                <tr class="hover:bg-white/[0.03]">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $t->icon }}</span>
                            <div>
                                <div class="font-bold text-white">{{ $t->name }}</div>
                                <div class="text-xs text-gray-500">{{ $t->description }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-300">{{ $t->metric }} ({{ $t->style }})</td>
                    <td class="px-4 py-3 text-gray-300">{{ $t->default_duration_days == 0 ? 'All-Time' : $t->default_duration_days.'d' }}</td>
                    <td class="px-4 py-3 text-gray-300">{{ $t->level_min }}–{{ $t->level_max }}</td>
                    <td class="px-4 py-3">
                        <button onclick="toggleTemplate({{ $t->id }}, this)"
                                class="px-3 py-1 rounded-full text-xs font-black {{ $t->is_active ? 'text-emerald-300' : 'text-gray-500' }}"
                                style="background:{{ $t->is_active ? 'rgba(16,185,129,0.15)' : 'rgba(255,255,255,0.05)' }};border:1px solid {{ $t->is_active ? 'rgba(16,185,129,0.35)' : 'rgba(255,255,255,0.1)' }};">
                            {{ $t->is_active ? 'Active' : 'Hidden' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('gameset.challenges.edit', $t) }}" class="text-indigo-300 hover:text-white text-xs font-bold mr-3">Edit</a>
                        <form action="{{ route('gameset.challenges.destroy', $t) }}" method="POST" class="inline" onsubmit="return confirm('Delete this template?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleTemplate(id, btn) {
    fetch(`/gameset/challenges/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => {
        btn.textContent = data.is_active ? 'Active' : 'Hidden';
        btn.style.color = data.is_active ? '#6ee7b7' : '#6b7280';
        btn.style.background = data.is_active ? 'rgba(16,185,129,0.15)' : 'rgba(255,255,255,0.05)';
    });
}
</script>
</body>
</html>
