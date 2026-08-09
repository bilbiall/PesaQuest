<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>{{ $mode === 'create' ? 'New Course' : 'Edit: '.$course->title }} — Gameset</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .form-section { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:20px; padding:1.5rem; margin-bottom:1.25rem; }
        .form-label { display:block; font-size:11px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:rgba(255,255,255,.5); margin-bottom:.5rem; }
        .form-input { width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:12px; padding:.7rem 1rem; font-size:.875rem; color:#fff; font-family:inherit; transition:border-color .15s; outline:none; }
        .form-input:focus { border-color:rgba(99,102,241,.6); box-shadow:0 0 0 3px rgba(99,102,241,.12); }
        .form-input::placeholder { color:rgba(255,255,255,.2); }
        textarea.form-input { resize:vertical; min-height:120px; }
        select.form-input option { background:#1a1a2e; }
        .error-msg { font-size:.78rem; color:#f87171; margin-top:.35rem; }
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3 text-sm">
        <a href="{{ route('gameset.courses.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Courses
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold">{{ $mode === 'create' ? 'New Course' : 'Edit Course' }}</span>
    </div>
</nav>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-black mb-6">{{ $mode === 'create' ? '📚 New Course' : '✏️ Edit Course' }}</h1>

    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-red-300" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('gameset.courses.store') : route('gameset.courses.update', $course) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        {{-- Basic Info --}}
        <div class="form-section">
            <h2 class="text-sm font-black text-white/80 mb-4">Basic Info</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Course Title *
                        <x-help-tip text="The name players see on the course card and in the Opportunity Hub. On creation it also generates the course's stable internal slug, so avoid a drastic rename later — job requirements and quests can reference it." example="Excel for Financial Analysis" />
                    </label>
                    <input type="text" name="title" value="{{ old('title', $course?->title) }}" class="form-input" placeholder="e.g. Excel for Financial Analysis" required>
                    @error('title')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Career Track *
                        <x-help-tip text="Files this course under one career track so it's shown as 'recommended' to players whose career-quiz result matches that same track. Tracks themselves are managed in GameSet Hub → Career Fields & Tracks." example="finance" />
                    </label>
                    <select name="career_track" class="form-input" required>
                        @foreach(\App\Services\CareerService::tracks() as $t)
                        <option value="{{ $t['key'] }}" {{ old('career_track', $course?->career_track) === $t['key'] ? 'selected' : '' }}>{{ $t['icon'] }} {{ $t['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] mt-1" style="color:rgba(255,255,255,.35);">Tracks are managed in GameSet Hub → Career Fields &amp; Tracks.</p>
                </div>
                <div>
                    <label class="form-label">Difficulty
                        <x-help-tip text="A display-only label shown to players to signal how challenging the course is — it does not gate access or change XP." example="beginner" />
                    </label>
                    <select name="difficulty" class="form-input">
                        @foreach(['beginner'=>'Beginner','intermediate'=>'Intermediate','advanced'=>'Advanced'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('difficulty', $course?->difficulty ?? 'beginner') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Icon (emoji)
                        <x-help-tip text="The emoji shown next to the course title on cards and in listings." example="📚" />
                    </label>
                    <input type="text" name="icon" value="{{ old('icon', $course?->icon ?? '📚') }}" class="form-input" maxlength="10" placeholder="📚">
                </div>
                <div>
                    <label class="form-label">Color (hex)
                        <x-help-tip text="The accent color used for this course's card and icon background in the Opportunity Hub." example="#4DA8F7" />
                    </label>
                    <input type="text" name="color" value="{{ old('color', $course?->color ?? '#4DA8F7') }}" class="form-input" placeholder="#4DA8F7">
                </div>
            </div>
        </div>

        {{-- Description & Content --}}
        <div class="form-section">
            <h2 class="text-sm font-black text-white/80 mb-4">Content</h2>
            <div class="flex flex-col gap-4">
                <div>
                    <label class="form-label">Short Description * <span class="font-normal normal-case text-white/30">(shown on card, max 500 chars)</span>
                        <x-help-tip text="The pitch players read before enrolling — sell why this skill is worth learning, in one sentence." example="Learn float management and spot reversal scams as an M-Pesa agent." />
                    </label>
                    <textarea name="description" class="form-input" maxlength="500" rows="3" required placeholder="What does this course cover in one sentence?">{{ old('description', $course?->description) }}</textarea>
                    @error('description')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Full Course Content <span class="font-normal normal-case text-white/30">(shown in detail modal — plain text or bullet points)</span>
                        <x-help-tip text="The actual lesson text players read once enrolled. Aim for 300-700 words, age-appropriate, with concrete Kenyan examples." example="Lesson 1: Float vs cash. In this course you will learn how agents balance their till each day..." />
                    </label>
                    <textarea name="content" class="form-input" rows="10" placeholder="Lesson 1: Introduction&#10;&#10;In this course you will learn...&#10;&#10;Key topics:&#10;• Topic 1&#10;• Topic 2&#10;&#10;Lesson 2: Practical Skills...">{{ old('content', $course?->content) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Learning Outcome * <span class="font-normal normal-case text-white/30">(what skill/badge is unlocked)</span>
                        <x-help-tip text="Shown on the completion screen to state exactly what skill or badge the player has earned by finishing this course." example="Earn the 'Excel Pro' badge + unlock Finance jobs" />
                    </label>
                    <input type="text" name="outcome" value="{{ old('outcome', $course?->outcome) }}" class="form-input" placeholder="e.g. Earn the 'Excel Pro' badge + unlock Finance jobs" required>
                    @error('outcome')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Jobs Intro <span class="font-normal normal-case text-white/30">(shown in completion popup — what doors this opens, max 300 chars)</span>
                        <x-help-tip text="A teaser in the completion popup naming the kinds of jobs this course's certificate unlocks, to make the payoff feel real." example="Tech companies across Pesa City are hiring — this course qualifies you for 3 entry-level roles." />
                    </label>
                    <input type="text" name="jobs_intro" value="{{ old('jobs_intro', $course?->jobs_intro) }}" class="form-input" maxlength="300" placeholder="e.g. Tech companies across Pesa City are hiring — this course qualifies you for 3 entry-level roles.">
                </div>
                <div>
                    <label class="form-label">Financial Education Tip <span class="font-normal normal-case text-white/30">(shown in completion popup — specific to this course's career path)</span>
                        <x-help-tip text="The takeaway money lesson shown on completion, tied to this course's career path — this is where the real teaching happens, so never leave it blank." example="A developer earns KES 60,000-200,000/month — skills never depreciate like a car does." />
                    </label>
                    <textarea name="financial_tip" class="form-input" rows="4" placeholder="e.g. Skills are the highest-return investment. A developer earns KES 60K–200K/month. Every hour learning today compounds into future earnings — skills never depreciate.">{{ old('financial_tip', $course?->financial_tip) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Meta --}}
        <div class="form-section">
            <h2 class="text-sm font-black text-white/80 mb-4">Settings</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Duration (hours)
                        <x-help-tip text="A display-only estimate of how long the course takes to read, shown to players before they enroll." example="2" />
                    </label>
                    <input type="number" name="duration_hours" value="{{ old('duration_hours', $course?->duration_hours ?? 2) }}" class="form-input" min="1" max="200">
                </div>
                <div>
                    <label class="form-label">XP Reward
                        <x-help-tip text="XP awarded to the player when they complete the course. Keep it small so leveling stays paced by real effort, not any single course." example="30" />
                    </label>
                    <input type="number" name="xp_reward" value="{{ old('xp_reward', $course?->xp_reward ?? 50) }}" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Cost (KES)
                        <x-help-tip text="The wallet amount deducted when a player enrolls — ignored while 'Free course' below is checked. Level-1 courses should generally be free." example="1500" />
                    </label>
                    <input type="number" name="cost_kes" value="{{ old('cost_kes', $course?->cost_kes ?? 0) }}" class="form-input" min="0">
                </div>
                <div>
                    <label class="form-label">Age Group
                        <x-help-tip text="Restricts which player age band sees this course. Leave blank or use 'all' so every player can enroll." example="18+" />
                    </label>
                    <input type="text" name="age_group" value="{{ old('age_group', $course?->age_group) }}" class="form-input" placeholder="all / 13-17 / 18+">
                </div>
            </div>
            <div class="flex items-center gap-6 mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_free" value="0">
                    <input type="checkbox" name="is_free" value="1" {{ old('is_free', $course?->is_free ?? true) ? 'checked' : '' }} class="w-4 h-4 accent-indigo-500">
                    <span class="text-sm font-bold text-white/70">Free course
                        <x-help-tip text="When checked, players enroll with no fee deducted at all, regardless of whatever value is set in the Cost field above." />
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $course?->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 accent-emerald-500">
                    <span class="text-sm font-bold text-white/70">Active (visible to players)
                        <x-help-tip text="Controls whether this course appears anywhere in the game. Uncheck to keep a draft in the database without deleting it." />
                    </span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.4);">
                {{ $mode === 'create' ? 'Create Course' : 'Save Changes' }}
            </button>
            <a href="{{ route('gameset.courses.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-white/50 hover:text-white hover:bg-white/5 transition-all">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
