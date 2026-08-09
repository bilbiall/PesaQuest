<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dreams — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'dreams'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">🌟 Dreams</span>
        </div>
        <a href="{{ route('gameset.dreams.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#f59e0b,#b45309);box-shadow:0 4px 14px rgba(245,158,11,.35);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Dream
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    @if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-semibold text-emerald-300 border border-emerald-500/30" style="background:rgba(16,185,129,0.1);">
        ✓ {{ session('success') }}
    </div>
    @endif

    <p class="text-sm text-gray-400 mb-6 max-w-2xl">Dreams are expensive, aspirational, one-time purchases — never resellable, never counted toward net worth. They exist purely as a flex on a player's profile Trophy Case.</p>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl p-5 text-center" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);">
            <div class="text-3xl font-black text-amber-400">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Total Dreams</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);">
            <div class="text-3xl font-black text-emerald-400">{{ $stats['active'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Active</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);">
            <div class="text-3xl font-black text-indigo-300">{{ $stats['owned'] }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Total Claimed</div>
        </div>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(236,72,153,0.1);border:1px solid rgba(236,72,153,0.25);">
            <div class="text-3xl font-black text-pink-300">KES {{ number_format($stats['avg_price']) }}</div>
            <div class="text-xs text-gray-400 font-semibold mt-1 uppercase tracking-wide">Avg Price</div>
        </div>
    </div>

    <div class="rounded-2xl overflow-hidden border border-white/10">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-white/5 text-left text-xs uppercase tracking-wide text-gray-400">
                    <th class="px-4 py-3">Dream</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Min Level</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($dreams as $dream)
                <tr class="hover:bg-white/[0.03] transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($dream->image_url)
                                <img src="{{ $dream->image_url }}" class="w-9 h-9 rounded-full" alt="">
                            @else
                                <span class="text-2xl">{{ $dream->icon }}</span>
                            @endif
                            <div>
                                <div class="font-bold text-white">{{ $dream->name }}</div>
                                <div class="text-xs text-gray-500">{{ $dream->tagline }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-300">{{ $dream->categoryLabel() }}</td>
                    <td class="px-4 py-3 font-bold text-amber-300">KES {{ number_format($dream->price) }}</td>
                    <td class="px-4 py-3 text-gray-300">{{ $dream->min_level ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <button onclick="toggleDream({{ $dream->id }}, this)"
                                class="px-3 py-1 rounded-full text-xs font-black {{ $dream->is_active ? 'text-emerald-300' : 'text-gray-500' }}"
                                style="background:{{ $dream->is_active ? 'rgba(16,185,129,0.15)' : 'rgba(255,255,255,0.05)' }};border:1px solid {{ $dream->is_active ? 'rgba(16,185,129,0.35)' : 'rgba(255,255,255,0.1)' }};">
                            {{ $dream->is_active ? 'Active' : 'Hidden' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('gameset.dreams.edit', $dream) }}" class="text-indigo-300 hover:text-white text-xs font-bold mr-3">Edit</a>
                        <form action="{{ route('gameset.dreams.destroy', $dream) }}" method="POST" class="inline" onsubmit="return confirm('Delete this dream?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($dreams->isEmpty())
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">No dreams yet — create the first one.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleDream(id, btn) {
    fetch(`/gameset/dreams/${id}/toggle`, {
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
