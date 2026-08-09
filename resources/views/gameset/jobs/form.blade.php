<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>{{ $mode === 'create' ? 'New Job' : 'Edit: '.$job->title }} — Gameset</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .form-section { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:20px; padding:1.5rem; margin-bottom:1.25rem; }
        .form-label { display:block; font-size:11px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:rgba(255,255,255,.5); margin-bottom:.5rem; }
        .form-input { width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:12px; padding:.7rem 1rem; font-size:.875rem; color:#fff; font-family:inherit; transition:border-color .15s; outline:none; }
        .form-input:focus { border-color:rgba(21,199,126,.6); box-shadow:0 0 0 3px rgba(21,199,126,.1); }
        .form-input::placeholder { color:rgba(255,255,255,.2); }
        select.form-input option { background:#1a1a2e; }
        .error-msg { font-size:.78rem; color:#f87171; margin-top:.35rem; }
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3 text-sm">
        <a href="{{ route('gameset.jobs.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Jobs
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold">{{ $mode === 'create' ? 'New Job' : 'Edit Job' }}</span>
    </div>
</nav>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-black mb-6">{{ $mode === 'create' ? '💼 New Job Listing' : '✏️ Edit Job' }}</h1>

    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-red-300" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('gameset.jobs.store') : route('gameset.jobs.update', $job) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="form-section">
            <h2 class="text-sm font-black text-white/80 mb-4">Job Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Job Title *
                        <x-help-tip text="The role name players see on the job listing and their Career page once hired." example="Junior Financial Analyst" />
                    </label>
                    <input type="text" name="title" value="{{ old('title', $job?->title) }}" class="form-input" placeholder="e.g. Junior Financial Analyst" required>
                    @error('title')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Employer Name *
                        <x-help-tip text="The company or person players see as their employer on the job card and their payslip." example="Equity Bank Kenya" />
                    </label>
                    <input type="text" name="employer_name" value="{{ old('employer_name', $job?->employer_name) }}" class="form-input" placeholder="e.g. Equity Bank Kenya" required>
                </div>
                <div>
                    <label class="form-label">Employer Logo (emoji)
                        <x-help-tip text="The emoji shown as the employer's logo on the job card and Career page." example="🏢" />
                    </label>
                    <input type="text" name="employer_logo" value="{{ old('employer_logo', $job?->employer_logo ?? '🏢') }}" class="form-input" maxlength="10" placeholder="🏢">
                </div>
                <div class="sm:col-span-2">
                    @php $selectedTracks = old('career_tracks', $job?->careerTrackList() ?? []); @endphp
                    <label class="form-label">Career Track(s) * <span class="font-normal normal-case text-white/30">(select one or more)</span>
                        <x-help-tip text="This job is shown as 'recommended' to a player if ANY one of the tracks you tick here matches their career-quiz result. Tick one for a narrowly-focused role, or several for a cross-disciplinary one." example="finance + business for a bookkeeping role" />
                    </label>
                    <div class="rounded-xl p-3 grid sm:grid-cols-2 gap-1.5" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);">
                        @foreach(\App\Services\CareerService::tracks() as $t)
                        <label class="flex items-center gap-2 px-2.5 py-2 rounded-lg cursor-pointer transition-colors hover:bg-white/5">
                            <input type="checkbox" name="career_tracks[]" value="{{ $t['key'] }}"
                                   {{ in_array($t['key'], $selectedTracks) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-emerald-500 flex-shrink-0">
                            <span class="text-sm text-white/80">{{ $t['icon'] }} {{ $t['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-[11px] mt-1" style="color:rgba(255,255,255,.35);">Selecting more than one means the job is "recommended" to players on any of those tracks. Tracks are managed in GameSet Hub → Career Fields &amp; Tracks.</p>
                    @error('career_tracks')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Level *
                        <x-help-tip text="The difficulty ladder rung this job sits on — players typically need to be around this level (and hold any required course certificate) before the job feels attainable." example="2" />
                    </label>
                    <select name="level" class="form-input" required>
                        <option value="1" {{ old('level', $job?->level ?? 1) == 1 ? 'selected' : '' }}>Level 1 — Entry</option>
                        <option value="2" {{ old('level', $job?->level) == 2 ? 'selected' : '' }}>Level 2 — Mid</option>
                        <option value="3" {{ old('level', $job?->level) == 3 ? 'selected' : '' }}>Level 3 — Senior</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Promotes To
                        <x-help-tip text="After ~1 game year of reliable, on-time work, a player here is moved to this exact job (title + salary change). Leave blank to fall back to an automatic match: the same career track, one level up, that the player is already eligible for." example="Senior Accountant at the same employer" />
                    </label>
                    <select name="promotes_to_job_id" class="form-input">
                        <option value="">— Auto-match (same track, next level) —</option>
                        @foreach($jobs as $j)
                        <option value="{{ $j->id }}" {{ (string) old('promotes_to_job_id', $job?->promotes_to_job_id) === (string) $j->id ? 'selected' : '' }}>
                            {{ $j->title }} · {{ $j->employer_name }} (Level {{ $j->level }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Employment Type *
                        <x-help-tip text="The single biggest choice on this form — it changes how many the player can hold at once and how they get paid. Full-time is their ONLY job (highest pay, blocks all other jobs); Part-time allows up to 2 at once; Freelance gig pays a one-off amount after ~7 game days and can be re-taken after a cooldown, up to 3 running at once." example="full_time for a career role, freelance for a one-off gig" />
                    </label>
                    <select name="employment_type" id="employment-type-select" class="form-input" required onchange="toggleJobTypeFields(this.value)">
                        @php $currentType = old('employment_type', $job?->employment_type ?? ($job?->is_part_time ? 'part_time' : 'full_time')); @endphp
                        <option value="full_time" {{ $currentType === 'full_time' ? 'selected' : '' }}>🏢 Full-time — the player's only job (must resign to change)</option>
                        <option value="part_time" {{ $currentType === 'part_time' ? 'selected' : '' }}>⏰ Part-time — players can hold up to 2 at once</option>
                        <option value="freelance" {{ $currentType === 'freelance' ? 'selected' : '' }}>⚡ Freelance gig — one-off payment, re-doable after a cooldown</option>
                    </select>
                    @error('employment_type')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div id="gig-cooldown-field" style="{{ $currentType === 'freelance' ? '' : 'display:none;' }}">
                    <label class="form-label">Gig Reopens After (game days)
                        <x-help-tip text="Freelance-only: how many game days pass before this SAME gig becomes available again to a player who already did it. It is independent of the ~7-day delivery time — use a short cooldown for a quick low-paying odd job, and a long one to keep a prestigious gig scarce. Leave blank to use the game-wide default of 28 days." example="28" />
                    </label>
                    <input type="number" name="gig_cooldown_ticks" value="{{ old('gig_cooldown_ticks', $job?->gig_cooldown_ticks) }}" class="form-input" min="1" max="365" placeholder="28 (default — 4 game weeks)">
                    <p class="text-[11px] mt-1" style="color:rgba(255,255,255,.35);">Leave blank to use the game default (28 days). This gig takes 7 game days to deliver, then this many days pass before the same client offers it again.</p>
                    @error('gig_cooldown_ticks')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2 class="text-sm font-black text-white/80 mb-4">Compensation & Requirements</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label" id="pay-field-label"><span id="pay-field-label-text">{{ $currentType === 'freelance' ? 'Gig Payment (KES, one-off) *' : 'Monthly Salary (KES) *' }}</span>
                        <x-help-tip text="For full-time/part-time jobs this is what the player earns per game month (collected only when they Report to Work). For a freelance gig it's a single one-off payment for the whole gig, not a monthly rate — price it at roughly a quarter to a third of what a monthly job in the same track pays." example="25000 salary, or 6000 for a one-off gig" />
                    </label>
                    <input type="number" name="salary_kes_month" value="{{ old('salary_kes_month', $job?->salary_kes_month) }}" class="form-input" min="1000" placeholder="25000" required>
                    <p class="text-[11px] mt-1" style="color:rgba(255,255,255,.35);" id="pay-field-hint">{{ $currentType === 'freelance' ? 'The one-off amount the player earns for delivering this gig — not a monthly rate.' : '' }}</p>
                    @error('salary_kes_month')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    @php $selectedAges = old('age_groups', $job?->ageGroupList() ?? []); @endphp
                    <label class="form-label">Age Group(s) <span class="font-normal normal-case text-white/30">(select one or more — leave all unchecked for every age)</span>
                        <x-help-tip text="Only players in the ticked age group(s) will see and can take this job. Use the bill-burden hints shown per group to price a salary that leaves a healthy margin after their typical bills." example="13-17 for a first part-time gig" />
                    </label>
                    <div class="rounded-xl p-3 grid sm:grid-cols-2 gap-1.5" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);">
                        @foreach(\App\Models\CityJob::AGE_GROUPS as $key => $ag)
                        <label class="flex items-center gap-2 px-2.5 py-2 rounded-lg cursor-pointer transition-colors hover:bg-white/5">
                            <input type="checkbox" name="age_groups[]" value="{{ $key }}"
                                   {{ in_array($key, $selectedAges) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded accent-emerald-500 flex-shrink-0">
                            <span class="text-sm text-white/80 flex-1">{{ $ag['icon'] }} {{ $ag['label'] }}</span>
                            @if(!empty($billBurden[$key]))
                            <span class="text-[10px] font-bold text-amber-300/70" title="Approximate bills this age group pays per game month">bills ≈ KES {{ number_format($billBurden[$key]) }}/mo</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                    <p class="text-[11px] mt-1" style="color:rgba(255,255,255,.35);">Only players in the selected age group(s) will see this job. Use the bill estimates to set a salary that leaves them a healthy margin (e.g. ~KES 15,000) after paying their bills.</p>
                    @error('age_groups')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    @php $selectedCourseIds = old('required_course_ids', $job?->requiredCourseIdList() ?? []); @endphp
                    <label class="form-label">Required Courses <span class="font-normal normal-case text-white/30">(select one or more — leave all unchecked for no prerequisite)</span>
                        <x-help-tip text="The certificate gate: a player must complete every course ticked here before they can apply. Selecting more than one stacks the requirement (ALL must be done) — use that for higher-paying jobs needing layered skills." example="bookkeeping-basics course for a Bookkeeping Clerk role" />
                    </label>
                    <div class="rounded-xl p-3 space-y-3" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);max-height:16rem;overflow-y:auto;">
                        @forelse($courses->groupBy('career_track') as $track => $trackCourses)
                        @php $tOpt = \App\Services\CareerService::tracksByKey()[$track] ?? null; @endphp
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-1.5">{{ $tOpt ? $tOpt['icon'].' '.$tOpt['label'] : $track }}</p>
                            <div class="grid sm:grid-cols-2 gap-1.5">
                                @foreach($trackCourses as $c)
                                <label class="flex items-center gap-2 px-2.5 py-2 rounded-lg cursor-pointer transition-colors hover:bg-white/5">
                                    <input type="checkbox" name="required_course_ids[]" value="{{ $c->id }}"
                                           {{ in_array($c->id, $selectedCourseIds) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded accent-emerald-500 flex-shrink-0">
                                    <span class="text-sm text-white/80 truncate">{{ $c->icon ?? '📚' }} {{ $c->title }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-white/30 text-center py-2">No courses yet — create one first.</p>
                        @endforelse
                    </div>
                    <p class="text-[11px] mt-1.5" style="color:rgba(255,255,255,.35);">Selecting more than one requires the player to complete <b>all</b> of them before applying — use this for higher-paying jobs that need layered skills.</p>
                    @error('required_course_ids')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $job?->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 accent-emerald-500">
                    <span class="text-sm font-bold text-white/70">Active (visible to players)
                        <x-help-tip text="Controls whether this job listing appears anywhere in the game. Uncheck to keep it in the database without deleting it." />
                    </span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#15C77E,#4DA8F7);box-shadow:0 4px 14px rgba(21,199,126,.3);">
                {{ $mode === 'create' ? 'Post Job' : 'Save Changes' }}
            </button>
            <a href="{{ route('gameset.jobs.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-white/50 hover:text-white hover:bg-white/5 transition-all">Cancel</a>
        </div>
    </form>
</div>
<script>
function toggleJobTypeFields(type) {
    const cooldownField = document.getElementById('gig-cooldown-field');
    const payLabel = document.getElementById('pay-field-label-text');
    const payHint  = document.getElementById('pay-field-hint');
    if (type === 'freelance') {
        cooldownField.style.display = '';
        payLabel.textContent = 'Gig Payment (KES, one-off) *';
        payHint.textContent = 'The one-off amount the player earns for delivering this gig — not a monthly rate.';
    } else {
        cooldownField.style.display = 'none';
        payLabel.textContent = 'Monthly Salary (KES) *';
        payHint.textContent = '';
    }
}
</script>
</body>
</html>
