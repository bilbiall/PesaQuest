<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Spin Wheel — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .seg-input { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:10px; padding:.45rem .6rem; font-size:.8rem; color:#fff; font-family:inherit; outline:none; width:100%; }
        .seg-input:focus { border-color:rgba(245,158,11,.6); }
        select.seg-input option { background:#1a1a2e; }
        .seg-row { display:grid; grid-template-columns: 44px 1.6fr .7fr 1.1fr .9fr .6fr .8fr .6fr auto; gap:8px; align-items:center; padding:.6rem .9rem; border-bottom:1px solid rgba(255,255,255,.05); }
        .seg-head { font-size:10px; font-weight:800; letter-spacing:.08em; color:#6b7280; text-transform:uppercase; }
        .btn-mini { font-size:11px; font-weight:800; padding:.4rem .7rem; border-radius:.55rem; cursor:pointer; white-space:nowrap; }
        @media (max-width: 900px) { .seg-row { grid-template-columns: 1fr 1fr; } .seg-head { display:none; } }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'spin'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3 text-sm">
        <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            GameSet
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold">🎡 Spin Wheel</span>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-emerald-300" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-red-300" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        <div class="rounded-2xl p-4 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
            <p class="text-xl font-black text-white">{{ $stats['total'] }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Segments</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.18);">
            <p class="text-xl font-black text-emerald-400">{{ $stats['active'] }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">On the wheel</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(77,168,247,.06);border:1px solid rgba(77,168,247,.18);">
            <p class="text-xl font-black text-blue-400">{{ $stats['weight'] > 0 ? round($stats['good'] / $stats['weight'] * 100) : 0 }}%</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Win chance</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(248,113,113,.06);border:1px solid rgba(248,113,113,.18);">
            <p class="text-xl font-black text-red-400">{{ $stats['weight'] > 0 ? round($stats['bad'] / $stats['weight'] * 100) : 0 }}%</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Risk chance</p>
        </div>
    </div>

    @if($segments->isEmpty())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm text-amber-300" style="background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);">
        ⚠️ No segments in the database yet — the wheel is running on its built-in defaults.
        Run <code class="font-mono text-amber-200">php artisan db:seed --class=SpinSegmentSeeder</code> (or Seed All) to import them here for editing.
    </div>
    @endif

    {{-- Add new segment --}}
    <div class="rounded-2xl p-5 mb-8" style="background:rgba(245,158,11,.05);border:1px solid rgba(245,158,11,.18);">
        <h2 class="text-sm font-black text-amber-300 mb-3">➕ Add Segment</h2>
        <form method="POST" action="{{ route('gameset.spin.store') }}" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 items-end">
            @csrf
            <div class="col-span-2"><label class="seg-head">Label</label><input name="label" class="seg-input" placeholder="Ksh 2,000" required maxlength="40"></div>
            <div><label class="seg-head">Emoji</label><input name="emoji" class="seg-input" placeholder="💰" required maxlength="10"></div>
            <div><label class="seg-head">Color</label><input name="color" type="color" value="#6366f1" class="seg-input" style="height:34px;padding:2px;"></div>
            <div>
                <label class="seg-head">Type</label>
                <select name="type" class="seg-input">
                    @foreach(\App\Models\SpinSegment::TYPES as $k => $lbl)
                    <option value="{{ $k }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="seg-head">Value</label><input name="value" type="number" class="seg-input" placeholder="2000" required></div>
            <div><label class="seg-head">Weight</label><input name="weight" type="number" class="seg-input" value="10" min="1" max="100" required></div>
            <div>
                <label class="seg-head">Tier</label>
                <select name="tier" class="seg-input">
                    <option value="good">🙂 Good</option>
                    <option value="great">🤩 Great</option>
                    <option value="bad">😬 Bad</option>
                </select>
            </div>
            <button type="submit" class="btn-mini text-white col-span-2 sm:col-span-1" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Add</button>
        </form>
        <p class="text-[11px] mt-2" style="color:rgba(255,255,255,.35);">
            <b>Weight</b> = how likely it is to land, relative to the others. <b>Value</b>: KES for money (use a negative number for a fine), points for credit/XP.
            Higher chance of landing = bigger weight. The wheel needs at least {{ \App\Models\SpinSegment::MIN_SEGMENTS }} active segments.
        </p>
    </div>

    {{-- Segment list --}}
    @if($segments->isNotEmpty())
    @php $totalWeight = max(1, $segments->where('is_active', true)->sum('weight')); @endphp
    <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);">
        <div class="seg-row" style="background:rgba(255,255,255,.025);">
            <span class="seg-head">Wedge</span><span class="seg-head">Label</span><span class="seg-head">Emoji</span>
            <span class="seg-head">Type</span><span class="seg-head">Value</span><span class="seg-head">Weight</span>
            <span class="seg-head">Tier</span><span class="seg-head">Chance</span><span class="seg-head text-right">Actions</span>
        </div>
        @foreach($segments as $seg)
        <form method="POST" action="{{ route('gameset.spin.update', $seg) }}" class="seg-row {{ $seg->is_active ? '' : 'opacity-40' }}">
            @csrf @method('PUT')
            <input type="hidden" name="sort_order" value="{{ $seg->sort_order }}">
            <input name="color" type="color" value="{{ $seg->color }}" class="seg-input" style="height:34px;padding:2px;" title="Wedge color">
            <input name="label" value="{{ $seg->label }}" class="seg-input" required maxlength="40">
            <input name="emoji" value="{{ $seg->emoji }}" class="seg-input" required maxlength="10">
            <select name="type" class="seg-input">
                @foreach(\App\Models\SpinSegment::TYPES as $k => $lbl)
                <option value="{{ $k }}" {{ $seg->type === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
                @if($seg->type === 'badge')<option value="badge" selected>Badge (legacy)</option>@endif
            </select>
            <input name="value" type="number" value="{{ $seg->value }}" class="seg-input" required>
            <input name="weight" type="number" value="{{ $seg->weight }}" min="1" max="100" class="seg-input" required>
            <select name="tier" class="seg-input">
                <option value="good"  {{ $seg->tier === 'good' ? 'selected' : '' }}>🙂 Good</option>
                <option value="great" {{ $seg->tier === 'great' ? 'selected' : '' }}>🤩 Great</option>
                <option value="bad"   {{ $seg->tier === 'bad' ? 'selected' : '' }}>😬 Bad</option>
            </select>
            <span class="text-xs font-black {{ $seg->tier === 'bad' ? 'text-red-400' : 'text-emerald-400' }}">
                {{ $seg->is_active ? round($seg->weight / $totalWeight * 100, 1) . '%' : '—' }}
            </span>
            <div class="flex items-center justify-end gap-1.5">
                <button type="submit" class="btn-mini" style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;">Save</button>
                <button type="button" onclick="toggleSeg({{ $seg->id }}, this)" class="btn-mini" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);color:#fbbf24;">
                    {{ $seg->is_active ? 'Hide' : 'Show' }}
                </button>
                <button type="submit" form="del-{{ $seg->id }}" onclick="return confirm('Delete this wheel segment?')"
                        class="btn-mini" style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#fca5a5;">✕</button>
            </div>
        </form>
        <form id="del-{{ $seg->id }}" method="POST" action="{{ route('gameset.spin.destroy', $seg) }}">@csrf @method('DELETE')</form>
        @endforeach
    </div>
    @endif

</div>
<script>
function toggleSeg(id, btn) {
    fetch(`/gameset/spin-wheel/${id}/toggle-active`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json().then(data => ({ok: r.ok, data})))
    .then(({ok, data}) => {
        if (!ok) { alert(data.error || 'Could not toggle.'); return; }
        location.reload();
    });
}
</script>
</body>
</html>
