<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Courses — Gameset</title>
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
        .track-tech      { background:rgba(77,168,247,.12); color:#4DA8F7; }
        .track-business  { background:rgba(167,139,250,.12); color:#a78bfa; }
        .track-finance   { background:rgba(21,199,126,.12); color:#15C77E; }
        .track-creative  { background:rgba(255,107,53,.12); color:#FF6B35; }
        .diff-beginner     { background:rgba(21,199,126,.12); color:#15C77E; }
        .diff-intermediate { background:rgba(77,168,247,.12); color:#4DA8F7; }
        .diff-advanced     { background:rgba(248,113,113,.12); color:#f87171; }
        .section-heading { font-size:11px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; padding:.5rem 1rem; background:rgba(255,255,255,.025); border-bottom:1px solid rgba(255,255,255,.05); color:#6366f1; }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'courses'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Gameset
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">Courses</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.jobs.index') }}" class="text-sm font-bold text-white/50 hover:text-white transition-colors px-3 py-2 rounded-xl hover:bg-white/5">
                Jobs &rarr;
            </a>
            <a href="{{ route('gameset.courses.create') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
               style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.4);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Course
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 flex items-center gap-3 text-sm font-bold text-emerald-300"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    @php
        $tracks = array_column(\App\Services\CareerService::tracks(), 'key');
        $trackLabels = collect(\App\Services\CareerService::tracks())->mapWithKeys(fn ($t) => [$t['key'] => [$t['icon'], $t['label'], $t['color']]])->all();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
        <div class="rounded-2xl p-4 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
            <p class="text-xl font-black text-white">{{ $courses->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Total</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.18);">
            <p class="text-xl font-black text-emerald-400">{{ $courses->where('is_active',true)->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Active</p>
        </div>
        @foreach($tracks as $t)
        @php [$ico,$lbl,$col] = $trackLabels[$t]; @endphp
        <div class="rounded-2xl p-4 text-center" style="background:{{ $col }}0d;border:1px solid {{ $col }}30;">
            <p class="text-xl font-black" style="color:{{ $col }};">{{ $courses->where('career_track',$t)->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">{{ $lbl }}</p>
        </div>
        @endforeach
    </div>

    @if($courses->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-4">📚</div>
        <p class="font-bold">No courses yet.</p>
        <a href="{{ route('gameset.courses.create') }}" class="text-indigo-400 text-sm mt-2 block hover:text-indigo-300">Create the first one &rarr;</a>
    </div>
    @else

    {{-- Career-track tabs (courses have no age targeting — tracks are their natural grouping) --}}
    <div class="flex flex-wrap gap-2 mb-4" id="course-track-tabs">
        <button type="button" data-track="" class="track-tab px-4 py-2 rounded-xl text-sm font-bold" style="background:rgba(99,102,241,0.22);border:1px solid rgba(99,102,241,0.5);color:#a5b4fc;">🌍 All tracks</button>
        @foreach($tracks as $t)
        @php [$ico,$lbl,$col] = $trackLabels[$t]; @endphp
        <button type="button" data-track="{{ $t }}" class="track-tab px-4 py-2 rounded-xl text-sm font-bold" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);color:#9ca3af;">{{ $ico }} {{ $lbl }}</button>
        @endforeach
    </div>

    {{-- Search & filters --}}
    <div class="flex flex-wrap gap-3 mb-6 items-center">
        <input type="search" id="course-search" list="course-suggestions" placeholder="🔍 Search courses…"
               autocomplete="off" data-lpignore="true" data-1p-ignore spellcheck="false"
               class="flex-1 min-w-[220px] rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500/60"
               oninput="applyCourseFilters()">
        <datalist id="course-suggestions">
            @foreach($courses->pluck('title')->unique()->sort() as $s)
            <option value="{{ $s }}"></option>
            @endforeach
        </datalist>
        <select id="course-filter-track" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none" style="background-color:#12101f;" onchange="applyCourseFilters()">
            <option value="">All Tracks</option>
            @foreach($tracks as $t)
            @php [$ico,$lbl,$col] = $trackLabels[$t]; @endphp
            <option value="{{ $t }}">{{ $ico }} {{ $lbl }}</option>
            @endforeach
        </select>
        <select id="course-filter-difficulty" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none" style="background-color:#12101f;" onchange="applyCourseFilters()">
            <option value="">All Difficulties</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
        </select>
        <select id="course-filter-status" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none" style="background-color:#12101f;" onchange="applyCourseFilters()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <button type="button" onclick="clearCourseFilters()" class="px-4 py-2 rounded-xl border border-white/10 text-sm font-bold text-gray-500 hover:text-white transition-colors">Clear</button>
    </div>

    <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);">
        @foreach($tracks as $t)
        @php $group = $courses->where('career_track', $t); @endphp
        @if($group->isNotEmpty())
        @php [$ico,$lbl,$col] = $trackLabels[$t]; @endphp
        <div class="course-track-group">
        <div class="section-heading">{{ $ico }} {{ $lbl }} ({{ $group->count() }})</div>
        <table class="tbl w-full">
            <thead>
                <tr>
                    <th class="text-left">Course</th>
                    <th class="text-left">Difficulty</th>
                    <th class="text-center">Duration</th>
                    <th class="text-center">XP</th>
                    <th class="text-left">Outcome</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group as $course)
                <tr class="course-row"
                    data-search="{{ strtolower($course->title . ' ' . ($course->outcome ?? '')) }}"
                    data-track="{{ $course->career_track }}"
                    data-difficulty="{{ $course->difficulty ?? 'beginner' }}"
                    data-status="{{ $course->is_active ? 'active' : 'inactive' }}">
                    <td>
                        <span class="text-lg mr-2">{{ $course->icon ?? '📚' }}</span>
                        <span class="font-bold text-white">{{ $course->title }}</span>
                        @if($course->is_free)
                        <span class="ml-2 text-[10px] font-black text-emerald-400">FREE</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge diff-{{ $course->difficulty ?? 'beginner' }}">{{ ucfirst($course->difficulty ?? 'beginner') }}</span>
                    </td>
                    <td class="text-center text-white/60">{{ $course->duration_hours ?? 2 }}h</td>
                    <td class="text-center text-amber-400 font-bold">{{ $course->xp_reward ?? 50 }}</td>
                    <td class="text-white/50 text-xs max-w-[200px] truncate">{{ $course->outcome }}</td>
                    <td class="text-center">
                        @if($course->is_active)
                        <span class="badge" style="background:rgba(16,185,129,.12);color:#34d399;">Active</span>
                        @else
                        <span class="badge" style="background:rgba(255,255,255,.06);color:#6b7280;">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('gameset.courses.edit', $course) }}" class="btn-edit">Edit</a>
                            <form method="POST" action="{{ route('gameset.courses.toggle-active', $course) }}">
                                @csrf
                                <button type="submit" class="btn-toggle">{{ $course->is_active ? 'Pause' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('gameset.courses.destroy', $course) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($course->title) }}?')">
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
        @endforeach
        <div id="course-no-results" class="text-center py-12 text-gray-500" style="display:none;">
            <div class="text-4xl mb-3">🔍</div>
            <p class="font-bold">No courses match your search or filters.</p>
        </div>
    </div>
    @endif

</div>
<script>
function applyCourseFilters() {
    const q     = document.getElementById('course-search').value.trim().toLowerCase();
    const track = document.getElementById('course-filter-track').value;
    const diff  = document.getElementById('course-filter-difficulty').value;
    const stat  = document.getElementById('course-filter-status').value;
    let anyVisible = false;

    document.querySelectorAll('.course-row').forEach(row => {
        const show = (!q     || row.dataset.search.includes(q))
                  && (!track || row.dataset.track === track)
                  && (!diff  || row.dataset.difficulty === diff)
                  && (!stat  || row.dataset.status === stat);
        row.style.display = show ? '' : 'none';
        if (show) anyVisible = true;
    });

    // Hide track groups whose rows are all filtered out
    document.querySelectorAll('.course-track-group').forEach(group => {
        const visible = [...group.querySelectorAll('.course-row')].some(r => r.style.display !== 'none');
        group.style.display = visible ? '' : 'none';
    });

    document.getElementById('course-no-results').style.display = anyVisible ? 'none' : '';
}
function clearCourseFilters() {
    ['course-search','course-filter-track','course-filter-difficulty','course-filter-status'].forEach(id => document.getElementById(id).value = '');
    syncTrackTabs('');
    applyCourseFilters();
}

/* Track tabs drive the track dropdown */
function syncTrackTabs(track) {
    document.querySelectorAll('#course-track-tabs .track-tab').forEach(tab => {
        const on = tab.dataset.track === track;
        tab.style.cssText = on
            ? 'background:rgba(99,102,241,0.22);border:1px solid rgba(99,102,241,0.5);color:#a5b4fc;'
            : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);color:#9ca3af;';
    });
}
document.querySelectorAll('#course-track-tabs .track-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.getElementById('course-filter-track').value = tab.dataset.track;
        syncTrackTabs(tab.dataset.track);
        applyCourseFilters();
    });
});
document.getElementById('course-filter-track').addEventListener('change', e => syncTrackTabs(e.target.value));
</script>
</body>
</html>
