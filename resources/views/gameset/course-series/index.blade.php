<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Series — Gameset</title>
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
        .btn-edit { font-size:12px; font-weight:700; padding:.35rem .85rem; border-radius:.6rem; background:rgba(99,102,241,.15); border:1px solid rgba(99,102,241,.3); color:#a5b4fc; text-decoration:none; }
        .btn-edit:hover { background:rgba(99,102,241,.25); }
        .btn-del  { font-size:12px; font-weight:700; padding:.35rem .85rem; border-radius:.6rem; background:rgba(248,113,113,.1); border:1px solid rgba(248,113,113,.2); color:#fca5a5; cursor:pointer; }
        .btn-del:hover { background:rgba(248,113,113,.2); }
        .btn-toggle { font-size:12px; font-weight:700; padding:.35rem .85rem; border-radius:.6rem; background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.2); color:#fbbf24; cursor:pointer; }
        .btn-toggle:hover { background:rgba(245,158,11,.2); }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'course-series'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('gameset.courses.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Courses
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">Course Series</span>
        </div>
        <a href="{{ route('gameset.course-series.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.4);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Series
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <p class="text-sm text-gray-500 mb-6">Group related courses into a learning path — assign courses to a series from the course's edit form. A series can be targeted as a quest's completion condition ("complete this whole series").</p>

    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 flex items-center gap-3 text-sm font-bold text-emerald-300"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    @if($series->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-4">🧭</div>
        <p class="font-bold">No course series yet.</p>
        <a href="{{ route('gameset.course-series.create') }}" class="text-indigo-400 text-sm mt-2 block hover:text-indigo-300">Create the first one &rarr;</a>
    </div>
    @else
    <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);">
        <table class="tbl w-full">
            <thead>
                <tr>
                    <th class="text-left">Series</th>
                    <th class="text-center">Courses</th>
                    <th class="text-center">Age Group</th>
                    <th class="text-center">Order</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($series as $s)
                <tr>
                    <td>
                        <span class="text-lg mr-2">{{ $s->icon ?? '🧭' }}</span>
                        <span class="font-bold text-white">{{ $s->title }}</span>
                        @if($s->description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($s->description, 80) }}</p>
                        @endif
                    </td>
                    <td class="text-center text-white/60">{{ $s->courses_count }}</td>
                    <td class="text-center text-white/60">{{ $s->age_group ?: 'All' }}</td>
                    <td class="text-center text-white/60">{{ $s->sort_order }}</td>
                    <td class="text-center">
                        @if($s->is_active)
                        <span class="badge" style="background:rgba(16,185,129,.12);color:#34d399;">Active</span>
                        @else
                        <span class="badge" style="background:rgba(255,255,255,.06);color:#6b7280;">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('gameset.course-series.edit', $s) }}" class="btn-edit">Edit</a>
                            <form method="POST" action="{{ route('gameset.course-series.toggle-active', $s) }}">
                                @csrf
                                <button type="submit" class="btn-toggle">{{ $s->is_active ? 'Pause' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('gameset.course-series.destroy', $s) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($s->title) }}? Courses in it will be un-assigned, not deleted.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
</body>
</html>
