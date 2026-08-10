<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Opportunity Hub — Pesa City</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }

        /* Track chip */
        .track-chip { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; border:1px solid currentColor; }

        /* Course card */
        .course-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: transform .2s, border-color .2s, box-shadow .2s;
            cursor: default;
        }
        .course-card:hover { transform: translateY(-3px); border-color: rgba(255,255,255,.14); box-shadow: 0 12px 40px rgba(0,0,0,.4); }
        .course-card.enrolled  { border-color: rgba(77,168,247,.35); }
        .course-card.completed { border-color: rgba(21,199,126,.35); }

        /* Job card */
        .job-card {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform .2s, border-color .2s;
        }
        .job-card:hover { transform: translateY(-2px); border-color: rgba(255,255,255,.13); }
        .job-card.employed { border-color: rgba(21,199,126,.35); background: rgba(21,199,126,.04); }
        .job-card.locked   { opacity: .55; }

        /* Content prose box */
        .content-prose {
            background: rgba(255,255,255,.025);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: .82rem;
            line-height: 1.7;
            color: rgba(255,255,255,.72);
            white-space: pre-line;
        }

        /* Pill tabs */
        .tab-pill { padding: 7px 18px; border-radius: 999px; font-size: 13px; font-weight: 800; letter-spacing: .03em; transition: background .15s, color .15s; cursor: pointer; border: 1.5px solid rgba(255,255,255,.08); color: rgba(255,255,255,.5); }
        .tab-pill:hover { color: #fff; border-color: rgba(255,255,255,.2); }
        .tab-pill.active { color: #fff; border-color: transparent; }

        /* Salary badge */
        .salary { font-size: 13px; font-weight: 900; color: #15C77E; letter-spacing: -.01em; }

        /* Diff badge */
        .diff-badge { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 6px; letter-spacing: .05em; text-transform: uppercase; }
        .diff-beginner     { background: rgba(21,199,126,.12); color: #15C77E; }
        .diff-intermediate { background: rgba(77,168,247,.12); color: #4DA8F7; }
        .diff-advanced     { background: rgba(248,113,113,.12); color: #f87171; }

        /* Status badge */
        .status-badge { font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 8px; letter-spacing: .05em; text-transform: uppercase; }

        /* Detail modal */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.85); backdrop-filter:blur(12px); z-index:200; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
        .modal-box { background:#0f0e1a; border:1px solid rgba(255,255,255,.12); border-radius:24px; max-width:560px; width:100%; max-height:90vh; overflow-y:auto; padding:2rem; position:relative; }

        /* Scrollbar */
        .modal-box::-webkit-scrollbar { width:4px; }
        .modal-box::-webkit-scrollbar-track { background:transparent; }
        .modal-box::-webkit-scrollbar-thumb { background:rgba(255,255,255,.12); border-radius:4px; }

        /* Shine btn */
        .btn-primary { display:inline-flex; align-items:center; justify-content:center; gap:6px; font-size:13px; font-weight:900; padding:10px 22px; border-radius:12px; cursor:pointer; border:none; transition: transform .15s, opacity .15s; }
        .btn-primary:hover { transform:scale(1.03); }
        .btn-primary:disabled { opacity:.45; cursor:not-allowed; transform:none; }

        /* ── Search + suggestions ── */
        .oh-search-wrap { position:relative; flex:1; min-width:180px; max-width:380px; margin-left:auto; }
        .oh-search { width:100%; padding:8px 14px 8px 36px; border-radius:999px; font-size:13px; font-weight:600; color:#fff;
            background:rgba(255,255,255,.05); border:1.5px solid rgba(255,255,255,.1); transition:border-color .15s, background .15s; }
        .oh-search:focus { outline:none; border-color:rgba(77,168,247,.55); background:rgba(77,168,247,.06); }
        .oh-search::placeholder { color:rgba(255,255,255,.3); }
        .oh-search-ico { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,.35); font-size:13px; pointer-events:none; }
        .oh-suggest { position:absolute; top:calc(100% + 8px); right:0; width:min(420px, calc(100vw - 32px)); z-index:60;
            background:#100e1e; border:1px solid rgba(99,102,241,.35); border-radius:16px; overflow:hidden;
            box-shadow:0 22px 60px rgba(0,0,0,.65); }
        .oh-sug-item { display:flex; align-items:center; gap:11px; padding:10px 14px; cursor:pointer; transition:background .1s; }
        .oh-sug-item:hover, .oh-sug-item.hot { background:rgba(99,102,241,.14); }
        .oh-sug-item + .oh-sug-item { border-top:1px solid rgba(255,255,255,.05); }
        .oh-sug-kind { font-size:9px; font-weight:900; letter-spacing:.06em; text-transform:uppercase; padding:2.5px 7px; border-radius:6px; flex-shrink:0; }

        /* ── Job type filter chips + card accents ── */
        .oh-card-hidden { display:none !important; }
        .course-card { border-top:3px solid transparent; }
        .course-card.recommended { border-top-color:rgba(245,158,11,.55); }
        .job-card { position:relative; }
        .job-card .oh-lock { position:absolute; top:-7px; right:14px; font-size:14px; filter:drop-shadow(0 2px 6px rgba(0,0,0,.6)); }
    </style>
</head>
<body class="text-white min-h-screen" x-data="opportunityHub()" x-init="init()">

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/50 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('world') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Pesa City
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold">Opportunity Hub</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-sm font-bold text-white/60">
                <span class="text-emerald-400">{{ $completedIds->count() }}</span> completed
            </div>
        </div>
    </div>
</nav>

{{-- Hero --}}
<div class="relative overflow-hidden" style="background:linear-gradient(135deg,#0a0d1e 0%,#0d1228 60%,#0a0d1e 100%);">
    <div class="absolute inset-0" style="background:radial-gradient(ellipse 80% 60% at 50% -10%,rgba(77,168,247,.15),transparent);"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 relative">
        <div class="text-center max-w-2xl mx-auto">
            <div class="text-6xl mb-4 select-none" style="filter:drop-shadow(0 0 30px rgba(77,168,247,.4))">🎓</div>
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-3" style="background:linear-gradient(135deg,#fff,#4DA8F7 80%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Opportunity Hub</h1>
            <p class="text-white/60 text-base leading-relaxed">Free courses, job listings, and quick gigs. Every career in Pesa City starts right here.</p>

            {{-- Quick stats --}}
            <div class="flex items-center justify-center gap-6 mt-6 text-sm">
                <div class="flex flex-col items-center">
                    <span class="text-2xl font-black text-white">{{ $courses->count() }}</span>
                    <span class="text-white/40 text-xs font-semibold mt-0.5">Courses</span>
                </div>
                <div class="w-px h-8 bg-white/10"></div>
                <div class="flex flex-col items-center">
                    <span class="text-2xl font-black text-white">{{ $jobs->count() }}</span>
                    <span class="text-white/40 text-xs font-semibold mt-0.5">Jobs</span>
                </div>
                <div class="w-px h-8 bg-white/10"></div>
                <div class="flex flex-col items-center">
                    <span class="text-2xl font-black text-emerald-400">{{ $completedIds->count() }}</span>
                    <span class="text-white/40 text-xs font-semibold mt-0.5">Completed</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="sticky top-[57px] z-40 border-b border-white/5" style="background:rgba(7,6,15,.9);backdrop-filter:blur(16px);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-2 overflow-x-auto">
        <button @click="tab='courses'" :class="tab==='courses' ? 'active' : ''"
                class="tab-pill whitespace-nowrap" :style="tab==='courses' ? 'background:rgba(77,168,247,.2);border-color:rgba(77,168,247,.4);color:#fff;' : ''">
            <x-icon name="book" class="w-3.5 h-3.5 inline-block" /> Courses
            <span class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded-full font-black" style="background:rgba(255,255,255,.08);">{{ $courses->count() }}</span>
        </button>
        <button @click="tab='jobs'" :class="tab==='jobs' ? 'active' : ''"
                class="tab-pill whitespace-nowrap" :style="tab==='jobs' ? 'background:rgba(21,199,126,.18);border-color:rgba(21,199,126,.4);color:#fff;' : ''">
            <x-icon name="briefcase" class="w-3.5 h-3.5 inline-block" /> Jobs & Gigs
            <span class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded-full font-black" style="background:rgba(255,255,255,.08);">{{ $jobs->count() }}</span>
        </button>
        {{-- Track filters (visible in courses tab) --}}
        <div x-show="tab==='courses'" class="flex items-center gap-2 ml-2">
            <div class="w-px h-5 bg-white/10"></div>
            @foreach($tracks as $key => $track)
            <button @click="trackFilter = trackFilter === '{{ $key }}' ? '' : '{{ $key }}'"
                    class="tab-pill text-[11px] whitespace-nowrap"
                    :style="trackFilter === '{{ $key }}' ? 'background:{{ $track['color'] }}25;border-color:{{ $track['color'] }}60;color:#fff;' : ''">
                {{ $track['icon'] }} {{ $track['label'] }}
            </button>
            @endforeach
        </div>

        {{-- Job filters (visible in jobs tab) --}}
        <div x-show="tab==='jobs'" class="flex items-center gap-2 ml-2">
            <div class="w-px h-5 bg-white/10"></div>
            @foreach(['full_time' => ['icon'=>'building','label'=>'Full-time'], 'part_time' => ['icon'=>'clock','label'=>'Part-time'], 'freelance' => ['icon'=>'bolt','label'=>'Gigs']] as $jtKey => $jt)
            <button @click="jobType = jobType === '{{ $jtKey }}' ? '' : '{{ $jtKey }}'; applyFilters()"
                    class="tab-pill text-[11px] whitespace-nowrap inline-flex items-center gap-1"
                    :style="jobType === '{{ $jtKey }}' ? 'background:rgba(21,199,126,.18);border-color:rgba(21,199,126,.45);color:#fff;' : ''">
                <x-icon :name="$jt['icon']" class="w-3 h-3" /> {{ $jt['label'] }}
            </button>
            @endforeach
            <button @click="qualifiedOnly = !qualifiedOnly; applyFilters()"
                    class="tab-pill text-[11px] whitespace-nowrap"
                    :style="qualifiedOnly ? 'background:rgba(245,158,11,.18);border-color:rgba(245,158,11,.5);color:#fff;' : ''">
                ✓ Qualified for
            </button>
        </div>

        {{-- Live search with suggestions --}}
        <div class="oh-search-wrap" @click.outside="suggestOpen=false">
            <span class="oh-search-ico">🔍</span>
            <input type="search" class="oh-search" placeholder="Search courses, jobs, employers…"
                   x-model="searchQuery"
                   @input="onSearch()"
                   @focus="onSearch()"
                   @keydown.escape="suggestOpen=false"
                   @keydown.arrow-down.prevent="sugHot = Math.min(sugHot + 1, suggestions.length - 1)"
                   @keydown.arrow-up.prevent="sugHot = Math.max(sugHot - 1, 0)"
                   @keydown.enter.prevent="if (suggestions[sugHot]) pickSuggestion(suggestions[sugHot])">
            <div class="oh-suggest" x-show="suggestOpen && suggestions.length > 0" x-cloak x-transition.opacity.duration.120ms>
                <template x-for="(s, i) in suggestions" :key="s.kind + s.id">
                    <div class="oh-sug-item" :class="{ hot: sugHot === i }" @mouseenter="sugHot = i" @click="pickSuggestion(s)">
                        <span class="text-xl flex-shrink-0" x-text="s.icon"></span>
                        <div class="flex-1 min-w-0">
                            <div class="text-[13px] font-black text-white truncate" x-text="s.title"></div>
                            <div class="text-[11px] text-white/45 truncate" x-text="s.sub"></div>
                        </div>
                        <span class="oh-sug-kind" :style="s.kind === 'course' ? 'background:rgba(77,168,247,.15);color:#7cc0ff;' : 'background:rgba(21,199,126,.15);color:#3ddc97;'"
                              x-text="s.kind === 'course' ? '📚 course' : '💼 job'"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- ── COURSES TAB ── --}}
    <div x-show="tab==='courses'">
        @if($courses->isEmpty())
        <div class="text-center py-20 text-white/40">
            <div class="text-5xl mb-4">📚</div>
            <p class="font-bold text-lg">No courses yet.</p>
            <p class="text-sm mt-2">Check back soon — the Gameset team is adding content!</p>
        </div>
        @else
        {{-- Group by track --}}
        @foreach($tracks as $trackKey => $track)
        @php $trackCourses = $courses->where('career_track', $trackKey); @endphp
        @if($trackCourses->isNotEmpty())
        <div x-show="!trackFilter || trackFilter === '{{ $trackKey }}'" class="mb-10" data-oh-group>
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-lg" style="background:{{ $track['color'] }}22;border:1px solid {{ $track['color'] }}40;">{{ $track['icon'] }}</div>
                <h2 class="text-base font-black text-white/90">{{ $track['label'] }}</h2>
                <span class="text-xs font-bold text-white/30">{{ $trackCourses->count() }} {{ Str::plural('course', $trackCourses->count()) }}</span>
                @if(($recommendedTrack ?? null) === $trackKey)
                <span class="text-[10px] font-black px-2 py-1 rounded-full" style="background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#fbbf24;">⭐ Your career path</span>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($trackCourses as $course)
                @php
                    $isCompleted = $completedIds->contains($course->id);
                    $isEnrolled  = $enrolledIds->contains($course->id);
                    $statusClass = $isCompleted ? 'completed' : ($isEnrolled ? 'enrolled' : '');
                @endphp
                <div class="course-card {{ $statusClass }} {{ ($recommendedTrack ?? null) === $trackKey ? 'recommended' : '' }}"
                     data-oh="course"
                     data-search="{{ Str::lower($course->title . ' ' . $course->description . ' ' . ($track['label'] ?? '') . ' ' . ($course->difficulty ?? '')) }}"
                     @click="openCourse({{ $course->id }})">
                    {{-- Top row --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
                             style="background:{{ $course->color ?? $track['color'] }}20;border:1px solid {{ $course->color ?? $track['color'] }}35;">
                            {{ $course->icon ?? $track['icon'] }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if($isCompleted)
                            <span class="status-badge" style="background:rgba(21,199,126,.12);color:#15C77E;">✓ Done</span>
                            @elseif($isEnrolled)
                            <span class="status-badge" style="background:rgba(77,168,247,.12);color:#4DA8F7;">In Progress</span>
                            @endif
                            <span class="diff-badge diff-{{ $course->difficulty ?? 'beginner' }}">{{ $course->difficulty ?? 'Beginner' }}</span>
                        </div>
                    </div>

                    {{-- Title + desc --}}
                    <div>
                        <h3 class="font-black text-white text-[15px] leading-snug mb-1">{{ $course->title }}</h3>
                        <p class="text-white/50 text-xs leading-relaxed line-clamp-2">{{ $course->description }}</p>
                    </div>

                    {{-- Outcome --}}
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-white/70"
                         style="background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.06);">
                        <span class="text-yellow-400 flex-shrink-0">🎯</span>
                        <span class="line-clamp-1">{{ $course->outcome }}</span>
                    </div>

                    {{-- Footer meta --}}
                    <div class="flex items-center justify-between pt-1 border-t border-white/5">
                        <div class="flex items-center gap-3 text-xs text-white/40">
                            <span>⏱ {{ $course->duration_hours ?? 2 }}h</span>
                            @if($course->xp_reward ?? 0)
                            <span class="text-amber-400/70">+{{ $course->xp_reward }} XP</span>
                            @endif
                        </div>
                        @if($course->is_free)
                        <span class="text-xs font-black text-emerald-400">FREE</span>
                        @else
                        <span class="text-xs font-black text-white/60">KES {{ number_format($course->cost_kes) }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
        @endif

        {{-- No search results --}}
        <div x-show="noCourseResults" x-cloak class="text-center py-16 text-white/40">
            <div class="text-5xl mb-3">🔍</div>
            <p class="font-bold">Nothing matches "<span class="text-white/70" x-text="searchQuery"></span>"</p>
            <p class="text-sm mt-1.5">Try a different word — or clear the search to see everything.</p>
        </div>
    </div>

    {{-- ── JOBS TAB ── --}}
    <div x-show="tab==='jobs'">
        @if($jobs->isEmpty())
        <div class="text-center py-20 text-white/40">
            <div class="text-5xl mb-4">💼</div>
            <p class="font-bold text-lg">No jobs posted yet.</p>
            <p class="text-sm mt-2">The Gameset team will add job listings soon.</p>
        </div>
        @else
        {{-- Group by level --}}
        @foreach([1=>'Entry Level',2=>'Mid Level',3=>'Senior'] as $lvl => $lvlLabel)
        @php $lvlJobs = $jobs->where('level', $lvl); @endphp
        @if($lvlJobs->isNotEmpty())
        <div class="mb-10" data-oh-group>
            <div class="flex items-center gap-3 mb-5">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm"
                     style="background:{{ ['rgba(21,199,126,.15)','rgba(77,168,247,.15)','rgba(167,139,250,.15)'][$lvl-1] }};border:1px solid {{ ['rgba(21,199,126,.3)','rgba(77,168,247,.3)','rgba(167,139,250,.3)'][$lvl-1] }};color:{{ ['#15C77E','#4DA8F7','#a78bfa'][$lvl-1] }};">L{{ $lvl }}</div>
                <h2 class="text-base font-black text-white/90">{{ $lvlLabel }}</h2>
                <span class="text-xs font-bold text-white/30">{{ $lvlJobs->count() }} {{ Str::plural('position', $lvlJobs->count()) }}</span>
            </div>
            <div class="flex flex-col gap-3">
                @foreach($lvlJobs as $job)
                @php
                    $isEmployed = $employedId === $job->id;
                    $reqCourses = $job->requiredCourses();
                    $isLocked   = $reqCourses->isNotEmpty() && !$job->meetsRequirements($completedIds);
                    $cardClass  = $isEmployed ? 'employed' : ($isLocked ? 'locked' : '');
                @endphp
                <div class="job-card {{ $cardClass }}"
                     data-oh="job"
                     data-type="{{ $job->type() }}"
                     data-qualified="{{ $isLocked ? 0 : 1 }}"
                     data-required-course="{{ $reqCourses->count() === 1 ? $reqCourses->first()->id : '' }}"
                     data-search="{{ Str::lower($job->title . ' ' . $job->employer_name . ' ' . $job->typeLabel() . ' ' . collect($job->careerTrackList())->map(fn($tk) => $tracks[$tk]['label'] ?? '')->join(' ')) }}"
                     @click="openJob({{ $job->id }})">
                    @if($isLocked)<span class="oh-lock">🔒</span>@endif
                    {{-- Employer logo --}}
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
                         style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">
                        {{ $job->employer_logo ?? '🏢' }}
                    </div>

                    {{-- Job info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                            <span class="font-black text-white text-[15px]">{{ $job->title }}</span>
                            @php $jt = $job->type(); @endphp
                            <span class="status-badge" style="{{ [
                                'full_time' => 'background:rgba(16,185,129,.12);color:#34d399;',
                                'part_time' => 'background:rgba(245,158,11,.12);color:#fcd34d;',
                                'freelance' => 'background:rgba(139,92,246,.14);color:#c4b5fd;',
                            ][$jt] }}">{{ ['full_time' => '🏢 Full-time', 'part_time' => '⏰ Part-time', 'freelance' => '⚡ Gig'][$jt] }}</span>
                            @if($isEmployed)
                            <span class="status-badge" style="background:rgba(21,199,126,.12);color:#15C77E;">Employed</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-white/50">
                            <span>{{ $job->employer_name }}</span>
                            <span>·</span>
                            @foreach($job->careerTrackList() as $trackKey)
                            @php $t = $tracks[$trackKey] ?? null; @endphp
                            @if($t)
                            <span>{{ $t['icon'] }} {{ $t['label'] }}</span>
                            @endif
                            @endforeach
                        </div>
                        @if($reqCourses->isNotEmpty())
                        <div class="flex items-center gap-1.5 mt-1.5 text-xs flex-wrap">
                            @if($isLocked)
                            <span class="text-amber-400">🔒</span>
                            <span class="text-amber-400/80">Requires: {{ $reqCourses->map(fn($c) => $c->icon.' '.$c->title)->join(' + ') }}</span>
                            @else
                            <span class="text-emerald-400">✓</span>
                            <span class="text-emerald-400/80">Qualified: {{ $reqCourses->map(fn($c) => $c->icon.' '.$c->title)->join(' + ') }}</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Salary --}}
                    <div class="text-right flex-shrink-0">
                        <div class="salary">KES {{ number_format($job->salary_kes_month) }}</div>
                        <div class="text-[10px] text-white/35 font-semibold">{{ $job->type() === 'freelance' ? 'one-off pay' : '/month' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
        @endif

        {{-- No search/filter results --}}
        <div x-show="noJobResults" x-cloak class="text-center py-16 text-white/40">
            <div class="text-5xl mb-3">🔍</div>
            <p class="font-bold">No jobs match your filters.</p>
            <p class="text-sm mt-1.5">Clear the search or filter chips above to see all positions.</p>
        </div>
    </div>

</div>

{{-- ── Course Detail Modal ── --}}
<div x-show="modal.open && modal.type==='course'" class="modal-overlay" @click.self="modal.open=false" @keydown.escape.window="modal.open=false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="modal-box" @click.stop>
        <template x-if="modal.data">
            <div>
                {{-- Close --}}
                <button @click="modal.open=false" class="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center text-white/40 hover:text-white hover:bg-white/10 transition-all">✕</button>

                {{-- Header --}}
                <div class="flex items-start gap-4 mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl flex-shrink-0"
                         :style="`background:${modal.data.color}20;border:1px solid ${modal.data.color}40;`">
                        <span x-text="modal.data.icon"></span>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-black text-white leading-snug" x-text="modal.data.title"></h2>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <template x-if="modal.data.player_status === 'completed'">
                                <span class="status-badge" style="background:rgba(21,199,126,.12);color:#15C77E;">✓ Completed</span>
                            </template>
                            <template x-if="modal.data.player_status === 'enrolled'">
                                <span class="status-badge" style="background:rgba(77,168,247,.12);color:#4DA8F7;">📖 In Progress</span>
                            </template>
                            <span class="diff-badge" :class="`diff-${modal.data.difficulty||'beginner'}`" x-text="modal.data.difficulty||'Beginner'"></span>
                            <span class="text-xs text-white/40" x-text="`⏱ ${modal.data.duration_hours||2}h`"></span>
                            <template x-if="modal.data.xp_reward">
                                <span class="text-xs text-amber-400/80 font-bold" x-text="`+${modal.data.xp_reward} XP`"></span>
                            </template>
                            <template x-if="modal.data.is_free">
                                <span class="text-xs font-black text-emerald-400">FREE</span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <p class="text-white/60 text-sm leading-relaxed mb-4" x-text="modal.data.description"></p>

                {{-- ── STATE 1: NOT ENROLLED — show intro teaser + enroll CTA ── --}}
                <template x-if="modal.data.player_status === 'not_enrolled'">
                    <div>
                        <template x-if="modal.data.intro_content">
                            <div>
                                <h4 class="text-xs font-black text-white/40 uppercase tracking-widest mb-2">About This Course</h4>
                                <div class="content-prose mb-4" x-text="modal.data.intro_content"></div>
                            </div>
                        </template>

                        {{-- Outcome --}}
                        <div class="flex items-start gap-3 px-4 py-3 rounded-xl mb-6" style="background:rgba(255,215,0,.06);border:1px solid rgba(255,215,0,.15);">
                            <span class="text-yellow-400 text-xl flex-shrink-0 mt-0.5">🎯</span>
                            <div>
                                <div class="text-xs font-black text-yellow-400/80 uppercase tracking-wider mb-0.5">What you'll earn</div>
                                <div class="text-sm text-white/80 font-semibold" x-text="modal.data.outcome"></div>
                            </div>
                        </div>

                        <button class="btn-primary w-full" style="background:linear-gradient(135deg,#4DA8F7,#6366f1);" @click="enrollCourse(modal.data.id)" :disabled="modal.loading">
                            <span x-show="!modal.loading">📚 Enroll &amp; Start Learning</span>
                            <span x-show="modal.loading">Enrolling…</span>
                        </button>
                    </div>
                </template>

                {{-- ── STATE 2: ENROLLED — show full content + complete CTA at bottom ── --}}
                <template x-if="modal.data.player_status === 'enrolled'">
                    <div>
                        <template x-if="modal.data.content">
                            <div>
                                <h4 class="text-xs font-black text-white/40 uppercase tracking-widest mb-2">Course Content</h4>
                                <div class="content-prose mb-5" x-text="modal.data.content"></div>
                            </div>
                        </template>

                        {{-- Outcome --}}
                        <div class="flex items-start gap-3 px-4 py-3 rounded-xl mb-5" style="background:rgba(255,215,0,.06);border:1px solid rgba(255,215,0,.15);">
                            <span class="text-yellow-400 text-xl flex-shrink-0 mt-0.5">🎯</span>
                            <div>
                                <div class="text-xs font-black text-yellow-400/80 uppercase tracking-wider mb-0.5">What you'll earn</div>
                                <div class="text-sm text-white/80 font-semibold" x-text="modal.data.outcome"></div>
                            </div>
                        </div>

                        {{-- Complete CTA — at the very bottom after reading content --}}
                        <div class="border-t border-white/10 pt-5">
                            <p class="text-xs text-white/40 text-center mb-3">Read everything above? Mark this course as complete to earn your XP.</p>
                            <button class="btn-primary w-full" style="background:linear-gradient(135deg,#15C77E,#4DA8F7);" @click="completeCourse(modal.data.id)" :disabled="modal.loading">
                                <span x-show="!modal.loading">✅ Complete Course &amp; Earn XP</span>
                                <span x-show="modal.loading">Saving…</span>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- ── STATE 3: COMPLETED — full content read-only with done badge ── --}}
                <template x-if="modal.data.player_status === 'completed'">
                    <div>
                        <template x-if="modal.data.content">
                            <div>
                                <h4 class="text-xs font-black text-white/40 uppercase tracking-widest mb-2">Course Content</h4>
                                <div class="content-prose mb-5" x-text="modal.data.content"></div>
                            </div>
                        </template>

                        {{-- Outcome --}}
                        <div class="flex items-start gap-3 px-4 py-3 rounded-xl mb-5" style="background:rgba(255,215,0,.06);border:1px solid rgba(255,215,0,.15);">
                            <span class="text-yellow-400 text-xl flex-shrink-0 mt-0.5">🎯</span>
                            <div>
                                <div class="text-xs font-black text-yellow-400/80 uppercase tracking-wider mb-0.5">What you earned</div>
                                <div class="text-sm text-white/80 font-semibold" x-text="modal.data.outcome"></div>
                            </div>
                        </div>

                        <div class="btn-primary w-full text-center" style="background:rgba(21,199,126,.12);border:1.5px solid rgba(21,199,126,.35);color:#15C77E;cursor:default;">
                            ✓ Course Completed
                        </div>
                    </div>
                </template>

            </div>
        </template>
    </div>
</div>

{{-- ── Job Detail Modal ── --}}
<div x-show="modal.open && modal.type==='job'" class="modal-overlay" @click.self="modal.open=false" @keydown.escape.window="modal.open=false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="modal-box" @click.stop>
        <template x-if="modal.data">
            <div>
                <button @click="modal.open=false" class="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center text-white/40 hover:text-white hover:bg-white/10 transition-all">✕</button>

                <div class="flex items-start gap-4 mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl flex-shrink-0" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);">
                        <span x-text="modal.data.employer_logo||'🏢'"></span>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-black text-white" x-text="modal.data.title"></h2>
                        <p class="text-sm text-white/50 mt-1" x-text="modal.data.employer_name"></p>
                        <div class="salary text-lg mt-1" x-text="`KES ${Number(modal.data.salary_kes_month).toLocaleString()}` + (modal.data.is_gig ? ' one-off' : '/month')"></div>
                        <p class="text-[11px] mt-1 font-bold" style="color:#c4b5fd;" x-show="modal.data.type_label"
                           x-text="modal.data.is_gig ? '⚡ Freelance gig — paid once on delivery, reopens after 4 game weeks' : (modal.data.employment_type === 'part_time' ? '⏰ Part-time — you can hold up to 2' : '🏢 Full-time — this becomes your only job')"></p>
                    </div>
                </div>

                {{-- Requirements --}}
                <template x-if="modal.data.required_courses && modal.data.required_courses.length > 0">
                    <div class="flex items-start gap-3 px-4 py-3 rounded-xl mb-4"
                         :style="modal.data.has_requirement ? 'background:rgba(21,199,126,.06);border:1px solid rgba(21,199,126,.2);' : 'background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2);'">
                        <span class="text-xl flex-shrink-0" x-text="modal.data.has_requirement ? '✅' : '🔒'"></span>
                        <div>
                            <div class="text-xs font-black uppercase tracking-wider mb-0.5"
                                 :class="modal.data.has_requirement ? 'text-emerald-400/80' : 'text-amber-400/80'"
                                 x-text="modal.data.has_requirement ? 'Qualification met' : (modal.data.required_courses.length > 1 ? 'All courses below required' : 'Prerequisite required')"></div>
                            <template x-for="rc in modal.data.required_courses" :key="rc.id">
                                <div class="text-sm font-semibold flex items-center gap-1.5" :class="rc.done ? 'text-emerald-400/90' : 'text-white/80'">
                                    <span x-text="rc.done ? '✓' : '·'"></span>
                                    <span x-text="`${rc.icon} ${rc.title}`"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex gap-3 mt-6">
                    <template x-if="modal.data.is_employed">
                        <div class="btn-primary w-full text-center" style="background:rgba(21,199,126,.15);border:1.5px solid rgba(21,199,126,.4);color:#15C77E;"
                             x-text="modal.data.is_gig ? '⚡ Gig in progress — deliver it, then Report to Work' : '✓ Currently Employed Here'"></div>
                    </template>
                    <template x-if="!modal.data.is_employed && modal.data.cooldown_days > 0">
                        <button class="btn-primary flex-1 cursor-not-allowed" style="background:rgba(139,92,246,.12);border:1.5px solid rgba(139,92,246,.3);color:#c4b5fd;" disabled
                                x-text="'⏳ Gig delivered — client has new work in ' + modal.data.cooldown_days + ' game day(s)'">
                        </button>
                    </template>
                    <template x-if="!modal.data.is_employed && !(modal.data.cooldown_days > 0) && modal.data.has_requirement">
                        <button class="btn-primary flex-1" style="background:linear-gradient(135deg,#15C77E,#4DA8F7);" @click="applyJob(modal.data.id)" :disabled="modal.loading">
                            <span x-show="!modal.loading" x-text="modal.data.is_gig ? '⚡ Take Gig' : 'Apply Now'"></span>
                            <span x-show="modal.loading">Applying…</span>
                        </button>
                    </template>
                    <template x-if="!modal.data.is_employed && !(modal.data.cooldown_days > 0) && !modal.data.has_requirement">
                        <button class="btn-primary flex-1 cursor-not-allowed" style="background:rgba(245,158,11,.12);border:1.5px solid rgba(245,158,11,.3);color:#fbbf24;" disabled>
                            Complete prerequisite course first
                        </button>
                    </template>
                </div>

            </div>
        </template>
    </div>
</div>

@php
    // Lightweight search index for the autocomplete — everything the player can open
    $ohIndex = $courses->map(fn ($c) => [
        'kind'  => 'course',
        'id'    => $c->id,
        'icon'  => $c->icon ?? '📚',
        'title' => $c->title,
        'sub'   => trim(($tracks[$c->career_track]['label'] ?? '') . ' · ' . ucfirst($c->difficulty ?? 'beginner') . ($c->is_free ? ' · Free' : ' · KES ' . number_format($c->cost_kes ?? 0))),
        'text'  => Str::lower($c->title . ' ' . $c->description . ' ' . ($tracks[$c->career_track]['label'] ?? '')),
    ])->concat($jobs->map(fn ($j) => [
        'kind'  => 'job',
        'id'    => $j->id,
        'icon'  => $j->employer_logo ?? '💼',
        'title' => $j->title,
        'sub'   => $j->employer_name . ' · KES ' . number_format($j->salary_kes_month) . ($j->type() === 'freelance' ? ' one-off' : '/mo'),
        'text'  => Str::lower($j->title . ' ' . $j->employer_name . ' ' . $j->typeLabel()),
    ]))->values();
@endphp
<script>
function opportunityHub() {
    return {
        tab: 'courses',
        trackFilter: '',
        jobType: '',
        qualifiedOnly: false,
        searchQuery: '',
        suggestOpen: false,
        suggestions: [],
        sugHot: 0,
        noCourseResults: false,
        noJobResults: false,
        index: {!! json_encode($ohIndex, JSON_HEX_TAG | JSON_HEX_APOS) !!},
        modal: { open: false, type: null, data: null, loading: false },

        init() {
            // Switch tab via URL hash
            if (window.location.hash === '#jobs') this.tab = 'jobs';
        },

        // ── Top-right toast popups (global system from app.js) ─────────
        toast(msg, ok = true, icon = null) {
            window.pesaToast ? window.pesaToast(msg, ok ? 'success' : 'error', { icon }) : alert(msg);
        },

        // ── Live search + suggestions ───────────────────────────────────
        onSearch() {
            const q = this.searchQuery.trim().toLowerCase();
            this.sugHot = 0;
            if (q.length < 2) {
                this.suggestions = [];
                this.suggestOpen = false;
            } else {
                this.suggestions = this.index
                    .filter(s => s.text.includes(q) || s.title.toLowerCase().includes(q))
                    .slice(0, 8);
                this.suggestOpen = true;
            }
            this.applyFilters();
        },

        pickSuggestion(s) {
            this.suggestOpen = false;
            this.tab = s.kind === 'course' ? 'courses' : 'jobs';
            s.kind === 'course' ? this.openCourse(s.id) : this.openJob(s.id);
        },

        applyFilters() {
            const q = this.searchQuery.trim().toLowerCase();
            document.querySelectorAll('[data-oh]').forEach(el => {
                let show = !q || (el.dataset.search || '').includes(q);
                if (el.dataset.oh === 'job') {
                    if (this.jobType && el.dataset.type !== this.jobType) show = false;
                    if (this.qualifiedOnly && el.dataset.qualified !== '1') show = false;
                }
                el.classList.toggle('oh-card-hidden', !show);
            });
            // Hide group sections with nothing visible in them
            document.querySelectorAll('[data-oh-group]').forEach(grp => {
                const any = grp.querySelector('[data-oh]:not(.oh-card-hidden)');
                grp.classList.toggle('oh-card-hidden', !any);
            });
            this.noCourseResults = !document.querySelector('[data-oh="course"]:not(.oh-card-hidden)');
            this.noJobResults   = !document.querySelector('[data-oh="job"]:not(.oh-card-hidden)');
        },

        async openCourse(id) {
            this.modal.type  = 'course';
            this.modal.open  = true;
            this.modal.data  = null;

            const res  = await fetch('{{ route('opportunities.courses') }}');
            const list = await res.json();
            this.modal.data = list.find(c => c.id === id) || null;
        },

        async openJob(id) {
            this.modal.type  = 'job';
            this.modal.open  = true;
            this.modal.data  = null;

            const res  = await fetch('{{ route('opportunities.jobs') }}');
            const list = await res.json();
            this.modal.data = list.find(j => j.id === id) || null;
        },

        async enrollCourse(id) {
            this.modal.loading = true;
            const res = await fetch(`/opportunities/courses/${id}/enroll`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            });
            const data = await res.json();
            this.modal.loading = false;
            if (data.status === 'enrolled') {
                this.modal.data.player_status = 'enrolled';
                this.toast(`Enrolled in "${this.modal.data.title}" — read it through, then mark it complete.`, true, '📚');
            } else if (data.status === 'completed') {
                this.modal.data.player_status = 'completed';
            } else if (data.error) {
                this.toast(data.error, false);
            }
        },

        async completeCourse(id) {
            this.modal.loading = true;
            const res = await fetch(`/opportunities/courses/${id}/complete`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            });
            const data = await res.json();
            this.modal.loading = false;
            if (data.status === 'completed') {
                this.modal.data.player_status = 'completed';
                const xpMsg = data.xp_awarded > 0 ? ` +${data.xp_awarded} XP earned.` : '';
                this.toast(`Course complete!${xpMsg} Jobs you qualify for are now unlocked.`, true, '🎓');
                if (data.jobs_unlocked && data.jobs_unlocked.length) {
                    const fully = data.jobs_unlocked.filter(j => j.fully_unlocked);
                    if (fully.length) {
                        this.toast(`Unlocked: ${fully.map(j => j.title).join(', ')} — check the Jobs tab!`, true, '🔓');
                    }
                }
                this._unlockJobCards(id);
            } else {
                this.toast(data.error || 'Something went wrong.', false);
            }
        },

        _unlockJobCards(courseId) {
            document.querySelectorAll(`.job-card.locked[data-required-course="${courseId}"]`)
                .forEach(el => { el.classList.remove('locked'); el.dataset.qualified = '1'; el.querySelector('.oh-lock')?.remove(); });
        },

        async applyJob(id) {
            this.modal.loading = true;
            const res = await fetch(`/opportunities/jobs/${id}/apply`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            });
            const data = await res.json();
            this.modal.loading = false;
            if (data.status === 'employed') {
                this.modal.data.is_employed = true;
                const xpMsg = data.xp_awarded > 0 ? ` +${data.xp_awarded} XP earned.` : '';
                this.toast(
                    data.employment_type === 'freelance'
                        ? `Gig landed!${xpMsg} Deliver the work, then Report to Work to collect your pay.`
                        : `You're hired!${xpMsg} Report to work each payday to collect your salary.`,
                    true, data.employment_type === 'freelance' ? '⚡' : '🎉'
                );
            } else {
                this.toast(data.error || 'Could not apply right now.', false, '💼');
            }
        }
    };
}
</script>
<x-mobile-bottom-nav active="city" />
</body>
</html>
