<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Marketplace Assets — Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .tbl th { font-size:10px; font-weight:800; letter-spacing:.08em; color:#6b7280; text-transform:uppercase; padding:.65rem 1rem; border-bottom:1px solid rgba(255,255,255,.06); }
        .tbl td { padding:.75rem 1rem; border-bottom:1px solid rgba(255,255,255,.04); font-size:.875rem; vertical-align:middle; }
        .tbl tr:last-child td { border-bottom:none; }
        .tbl tr:hover td { background:rgba(255,255,255,.02); }
        .badge { display:inline-block; font-size:10px; font-weight:700; padding:.2rem .55rem; border-radius:.5rem; }
        .cat-vehicle    { background:rgba(245,158,11,.12); color:#fbbf24; }
        .cat-property   { background:rgba(16,185,129,.12); color:#34d399; }
        .cat-business   { background:rgba(139,92,246,.12); color:#a78bfa; }
        .cat-investment { background:rgba(96,165,250,.12); color:#93c5fd; }
        .cat-gadget     { background:rgba(244,114,182,.12); color:#f9a8d4; }
        .btn-edit   { font-size:12px; font-weight:700; padding:.35rem .85rem; border-radius:.6rem; background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3); color:#a5b4fc; text-decoration:none; }
        .btn-edit:hover { background:rgba(99,102,241,.25); }
        .btn-del    { font-size:12px; font-weight:700; padding:.35rem .85rem; border-radius:.6rem; background:rgba(248,113,113,.1); border:1px solid rgba(248,113,113,.2); color:#fca5a5; cursor:pointer; }
        .btn-del:hover { background:rgba(248,113,113,.2); }
        .section-heading { font-size:11px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; padding:.5rem 1rem; background:rgba(255,255,255,.025); border-bottom:1px solid rgba(255,255,255,.05); color:#6366f1; }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('admin.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Admin Panel
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">Marketplace Assets</span>
        </div>
        <a href="{{ route('admin.assets.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.4);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Asset
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 flex items-center gap-3 text-sm font-bold text-emerald-300"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    {{-- Summary stats --}}
    @php
        $cats = ['vehicle','property','business','investment','gadget'];
        $total  = $assets->count();
        $active = $assets->where('is_active', true)->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-8">
        <div class="rounded-2xl p-4 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
            <p class="text-xl font-black text-white">{{ $total }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Total</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.18);">
            <p class="text-xl font-black text-emerald-400">{{ $active }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Active</p>
        </div>
        @foreach($cats as $cat)
        @php $accentMap = ['vehicle'=>['text-amber-400','rgba(245,158,11,.06)','rgba(245,158,11,.18)'],'property'=>['text-emerald-400','rgba(16,185,129,.06)','rgba(16,185,129,.18)'],'business'=>['text-violet-400','rgba(139,92,246,.06)','rgba(139,92,246,.18)'],'investment'=>['text-blue-400','rgba(96,165,250,.06)','rgba(96,165,250,.18)'],'gadget'=>['text-pink-400','rgba(244,114,182,.06)','rgba(244,114,182,.18)']]; $a = $accentMap[$cat]; @endphp
        <div class="rounded-2xl p-4 text-center" style="background:{{ $a[1] }};border:1px solid {{ $a[2] }};">
            <p class="text-xl font-black {{ $a[0] }}">{{ $assets->where('category',$cat)->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">{{ ucfirst($cat) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Table grouped by category --}}
    @if($assets->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-4">📦</div>
        <p class="font-bold">No assets yet.</p>
        <a href="{{ route('admin.assets.create') }}" class="text-indigo-400 text-sm mt-2 block hover:text-indigo-300">Create the first one &rarr;</a>
    </div>
    @else
    <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);">
        @foreach($cats as $cat)
        @php $group = $assets->where('category', $cat); @endphp
        @if($group->isNotEmpty())
        <div class="section-heading">{{ ['vehicle'=>'🚗 Vehicles','property'=>'🏠 Property','business'=>'💼 Business','investment'=>'📈 Investments','gadget'=>'📱 Gadgets'][$cat] }} ({{ $group->count() }})</div>
        <table class="tbl w-full">
            <thead>
                <tr>
                    <th class="text-left">Asset</th>
                    <th class="text-left">Slug</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Income</th>
                    <th class="text-right">Cost</th>
                    <th class="text-center">Tier</th>
                    <th class="text-center">Age</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group as $asset)
                <tr>
                    <td>
                        <span class="mr-2"><x-icon :name="$asset->icon" class="w-4 h-4 inline-block" /></span>
                        <span class="font-bold text-white">{{ $asset->name }}</span>
                    </td>
                    <td class="text-gray-500 font-mono text-xs">{{ $asset->slug }}</td>
                    <td class="text-right font-bold text-white">Ksh {{ number_format($asset->base_price) }}</td>
                    <td class="text-right text-emerald-400 font-semibold">+{{ number_format($asset->monthly_income ?? 0) }}</td>
                    <td class="text-right text-red-400 font-semibold">-{{ number_format($asset->monthly_cost ?? 0) }}</td>
                    <td class="text-center">
                        <span class="font-black text-indigo-400">T{{ $asset->tier }}</span>
                    </td>
                    <td class="text-center text-gray-400 text-xs">{{ $asset->age_group ?? 'all' }}</td>
                    <td class="text-center">
                        @if($asset->is_active)
                            <span class="badge" style="background:rgba(16,185,129,.12);color:#34d399;">Active</span>
                        @else
                            <span class="badge" style="background:rgba(255,255,255,.06);color:#6b7280;">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.assets.edit', $asset) }}" class="btn-edit">Edit</a>
                            <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($asset->name) }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endforeach
    </div>
    @endif

</div>
</body>
</html>
