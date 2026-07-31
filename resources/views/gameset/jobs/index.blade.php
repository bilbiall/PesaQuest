<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jobs — Gameset</title>
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
        .section-heading { font-size:11px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; padding:.5rem 1rem; background:rgba(255,255,255,.025); border-bottom:1px solid rgba(255,255,255,.05); color:#6366f1; }
        .level-1 { background:rgba(21,199,126,.12); color:#15C77E; }
        .level-2 { background:rgba(77,168,247,.12); color:#4DA8F7; }
        .level-3 { background:rgba(167,139,250,.12); color:#a78bfa; }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'jobs'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Gameset
            </a>
            <span class="text-white/20">/</span>
            <a href="{{ route('gameset.courses.index') }}" class="text-gray-400 hover:text-white transition-colors">Courses</a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">Jobs</span>
        </div>
        <a href="{{ route('gameset.jobs.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
           style="background:linear-gradient(135deg,#15C77E,#4DA8F7);box-shadow:0 4px 14px rgba(21,199,126,.3);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Job
        </a>
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
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        <div class="rounded-2xl p-4 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
            <p class="text-xl font-black text-white">{{ $jobs->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Total</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(21,199,126,.06);border:1px solid rgba(21,199,126,.18);">
            <p class="text-xl font-black text-emerald-400">{{ $jobs->where('level',1)->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Entry</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(77,168,247,.06);border:1px solid rgba(77,168,247,.18);">
            <p class="text-xl font-black text-blue-400">{{ $jobs->where('level',2)->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Mid</p>
        </div>
        <div class="rounded-2xl p-4 text-center" style="background:rgba(167,139,250,.06);border:1px solid rgba(167,139,250,.18);">
            <p class="text-xl font-black text-violet-400">{{ $jobs->where('level',3)->count() }}</p>
            <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider mt-0.5">Senior</p>
        </div>
    </div>

    @if($jobs->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-4">💼</div>
        <p class="font-bold">No jobs yet.</p>
        <a href="{{ route('gameset.jobs.create') }}" class="text-emerald-400 text-sm mt-2 block hover:text-emerald-300">Post the first job &rarr;</a>
    </div>
    @else

    {{-- Age-group tabs (drive the same age filter below) --}}
    <div class="flex flex-wrap gap-2 mb-4" id="job-age-tabs">
        <button type="button" data-age="" class="age-tab px-4 py-2 rounded-xl text-sm font-bold" style="background:rgba(21,199,126,0.2);border:1px solid rgba(21,199,126,0.5);color:#6ee7b7;">🌍 All ages</button>
        @foreach(\App\Models\CityJob::AGE_GROUPS as $key => $ag)
        <button type="button" data-age="{{ $key }}" class="age-tab px-4 py-2 rounded-xl text-sm font-bold" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);color:#9ca3af;">{{ $ag['icon'] }} {{ $ag['label'] }}</button>
        @endforeach
    </div>

    {{-- Search & filters --}}
    <div class="flex flex-wrap gap-3 mb-6 items-center">
        <input type="search" id="job-search" list="job-suggestions" placeholder="🔍 Search jobs or employers…"
               autocomplete="off" data-lpignore="true" data-1p-ignore spellcheck="false"
               class="flex-1 min-w-[220px] rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500/60"
               oninput="applyJobFilters()">
        <datalist id="job-suggestions">
            @foreach($jobs->pluck('title')->merge($jobs->pluck('employer_name'))->unique()->sort() as $s)
            <option value="{{ $s }}"></option>
            @endforeach
        </datalist>
        <select id="job-filter-track" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none" style="background-color:#12101f;" onchange="applyJobFilters()">
            <option value="">All Tracks</option>
            @foreach(\App\Services\CareerService::tracks() as $t)
            <option value="{{ $t['key'] }}">{{ $t['icon'] }} {{ $t['label'] }}</option>
            @endforeach
        </select>
        <select id="job-filter-type" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none" style="background-color:#12101f;" onchange="applyJobFilters()">
            <option value="">All Types</option>
            <option value="full_time">🏢 Full-time</option>
            <option value="part_time">⏰ Part-time</option>
            <option value="freelance">⚡ Freelance gig</option>
        </select>
        <select id="job-filter-level" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none" style="background-color:#12101f;" onchange="applyJobFilters()">
            <option value="">All Levels</option>
            <option value="1">L1 — Entry</option>
            <option value="2">L2 — Mid</option>
            <option value="3">L3 — Senior</option>
        </select>
        <select id="job-filter-age" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none" style="background-color:#12101f;" onchange="applyJobFilters()">
            <option value="">All Ages</option>
            @foreach(\App\Models\CityJob::AGE_GROUPS as $key => $ag)
            <option value="{{ $key }}">{{ $ag['icon'] }} {{ $ag['label'] }}</option>
            @endforeach
        </select>
        <button type="button" onclick="clearJobFilters()" class="px-4 py-2 rounded-xl border border-white/10 text-sm font-bold text-gray-500 hover:text-white transition-colors">Clear</button>
    </div>

    <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.07);">
        @foreach([1=>'Entry Level',2=>'Mid Level',3=>'Senior'] as $lvl => $lvlLabel)
        @php $group = $jobs->where('level', $lvl); @endphp
        @if($group->isNotEmpty())
        <div class="job-level-group">
        <div class="section-heading">L{{ $lvl }} — {{ $lvlLabel }} ({{ $group->count() }})</div>
        <table class="tbl w-full">
            <thead>
                <tr>
                    <th class="text-left">Job</th>
                    <th class="text-left">Employer</th>
                    <th class="text-left">Track</th>
                    <th class="text-left">Ages</th>
                    <th class="text-left">Type</th>
                    <th class="text-right">Salary</th>
                    <th class="text-left">Requires</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group as $job)
                @php $jobAges = $job->ageGroupList(); @endphp
                <tr class="job-row"
                    data-search="{{ strtolower($job->title . ' ' . $job->employer_name) }}"
                    data-tracks="{{ implode(' ', $job->careerTrackList()) }}"
                    data-type="{{ $job->type() }}"
                    data-level="{{ $job->level }}"
                    data-ages="{{ empty($jobAges) ? 'all' : implode(' ', $jobAges) }}">
                    <td>
                        <span class="text-lg mr-2">{{ $job->employer_logo ?? '🏢' }}</span>
                        <span class="font-bold text-white">{{ $job->title }}</span>
                    </td>
                    <td class="text-white/60">{{ $job->employer_name }}</td>
                    <td>
                        @php $tracksByKey = \App\Services\CareerService::tracksByKey(); @endphp
                        <div class="flex flex-wrap gap-1">
                            @foreach($job->careerTrackList() as $trackKey)
                            @php $tc = $tracksByKey[$trackKey] ?? ['icon' => '🏢', 'color' => '#aaa', 'label' => ucfirst($trackKey)]; @endphp
                            <span class="text-xs font-bold" style="color:{{ $tc['color'] }};">{{ $tc['icon'] }} {{ $tc['label'] }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if(empty($jobAges))
                        <span class="text-xs text-white/40">All ages</span>
                        @else
                        <div class="flex flex-wrap gap-1">
                            @foreach($jobAges as $agKey)
                            <span class="badge" style="background:rgba(251,191,36,.1);color:#fbbf24;">{{ \App\Models\CityJob::AGE_GROUPS[$agKey]['icon'] ?? '' }} {{ $agKey }}</span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td>
                        @php $jt = $job->type(); @endphp
                        <span class="badge" style="{{ [
                            'full_time' => 'background:rgba(16,185,129,.12);color:#34d399;',
                            'part_time' => 'background:rgba(245,158,11,.12);color:#fcd34d;',
                            'freelance' => 'background:rgba(139,92,246,.14);color:#c4b5fd;',
                        ][$jt] }}">{{ ['full_time' => '🏢 Full-time', 'part_time' => '⏰ Part-time', 'freelance' => '⚡ Gig'][$jt] }}</span>
                    </td>
                    <td class="text-right font-black text-emerald-400">KES {{ number_format($job->salary_kes_month) }}{{ $jt === 'freelance' ? ' (one-off)' : '' }}</td>
                    <td class="text-xs text-white/50">
                        @php $reqCourses = $job->requiredCourses(); @endphp
                        @if($reqCourses->isNotEmpty())
                        {{ $reqCourses->map(fn($c) => ($c->icon ?? '📚') . ' ' . $c->title)->join(' + ') }}
                        @else
                        <span class="text-white/25">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($job->is_active)
                        <span class="badge" style="background:rgba(16,185,129,.12);color:#34d399;">Active</span>
                        @else
                        <span class="badge" style="background:rgba(255,255,255,.06);color:#6b7280;">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('gameset.jobs.edit', $job) }}" class="btn-edit">Edit</a>
                            <form method="POST" action="{{ route('gameset.jobs.toggle-active', $job) }}">
                                @csrf
                                <button type="submit" class="btn-toggle">{{ $job->is_active ? 'Pause' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('gameset.jobs.destroy', $job) }}"
                                  onsubmit="return confirm('Delete this job?')">
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
        <div id="job-no-results" class="text-center py-12 text-gray-500" style="display:none;">
            <div class="text-4xl mb-3">🔍</div>
            <p class="font-bold">No jobs match your search or filters.</p>
        </div>
    </div>
    @endif

</div>
<script>
function applyJobFilters() {
    const q     = document.getElementById('job-search').value.trim().toLowerCase();
    const track = document.getElementById('job-filter-track').value;
    const type  = document.getElementById('job-filter-type').value;
    const level = document.getElementById('job-filter-level').value;
    const age   = document.getElementById('job-filter-age').value;
    let anyVisible = false;

    document.querySelectorAll('.job-row').forEach(row => {
        const ages = row.dataset.ages;
        const show = (!q     || row.dataset.search.includes(q))
                  && (!track || row.dataset.tracks.split(' ').includes(track))
                  && (!type  || row.dataset.type === type)
                  && (!level || row.dataset.level === level)
                  && (!age   || ages === 'all' || ages.split(' ').includes(age));
        row.style.display = show ? '' : 'none';
        if (show) anyVisible = true;
    });

    // Hide level groups whose rows are all filtered out
    document.querySelectorAll('.job-level-group').forEach(group => {
        const visible = [...group.querySelectorAll('.job-row')].some(r => r.style.display !== 'none');
        group.style.display = visible ? '' : 'none';
    });

    document.getElementById('job-no-results').style.display = anyVisible ? 'none' : '';
}
function clearJobFilters() {
    ['job-search','job-filter-track','job-filter-type','job-filter-level','job-filter-age'].forEach(id => document.getElementById(id).value = '');
    syncAgeTabs('');
    applyJobFilters();
}

/* Age tabs drive the age dropdown (and vice-visual) */
function syncAgeTabs(age) {
    document.querySelectorAll('#job-age-tabs .age-tab').forEach(tab => {
        const on = tab.dataset.age === age;
        tab.style.cssText = on
            ? 'background:rgba(21,199,126,0.2);border:1px solid rgba(21,199,126,0.5);color:#6ee7b7;'
            : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.09);color:#9ca3af;';
    });
}
document.querySelectorAll('#job-age-tabs .age-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.getElementById('job-filter-age').value = tab.dataset.age;
        syncAgeTabs(tab.dataset.age);
        applyJobFilters();
    });
});
document.getElementById('job-filter-age').addEventListener('change', e => syncAgeTabs(e.target.value));
</script>
</body>
</html>
