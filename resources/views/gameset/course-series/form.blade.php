<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>{{ $mode === 'create' ? 'New Series' : 'Edit: '.$series->title }} — Gameset</title>
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
        textarea.form-input { resize:vertical; min-height:90px; }
        .error-msg { font-size:.78rem; color:#f87171; margin-top:.35rem; }
        .tbl th { font-size:10px; font-weight:800; letter-spacing:.08em; color:#6b7280; text-transform:uppercase; padding:.5rem .75rem; border-bottom:1px solid rgba(255,255,255,.06); }
        .tbl td { padding:.6rem .75rem; border-bottom:1px solid rgba(255,255,255,.04); font-size:.8rem; }
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3 text-sm">
        <a href="{{ route('gameset.course-series.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Course Series
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold">{{ $mode === 'create' ? 'New Series' : 'Edit Series' }}</span>
    </div>
</nav>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-black mb-6">{{ $mode === 'create' ? '🧭 New Series' : '✏️ Edit Series' }}</h1>

    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-red-300" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('gameset.course-series.store') : route('gameset.course-series.update', $series) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="form-section">
            <h2 class="text-sm font-black text-white/80 mb-4">Series Info</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Title *
                        <x-help-tip text="The learning path's name, shown as a heading grouping its courses together in the Opportunity Hub." example="Money Basics" />
                    </label>
                    <input type="text" name="title" value="{{ old('title', $series?->title) }}" class="form-input" placeholder="e.g. Money Basics" required>
                    @error('title')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Description <span class="font-normal normal-case text-white/30">(optional)</span>
                        <x-help-tip text="A one-sentence pitch for the whole path, shown under its heading." example="Master the fundamentals before tackling investing." />
                    </label>
                    <textarea name="description" class="form-input" maxlength="500" rows="2" placeholder="What does completing this whole path get you?">{{ old('description', $series?->description) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Icon (emoji)
                        <x-help-tip text="Shown next to the series title." example="🧭" />
                    </label>
                    <input type="text" name="icon" value="{{ old('icon', $series?->icon ?? '🧭') }}" class="form-input" maxlength="10" placeholder="🧭">
                </div>
                <div>
                    <label class="form-label">Color (hex)
                        <x-help-tip text="Accent color used for this series' heading/badge." example="#8b5cf6" />
                    </label>
                    <input type="text" name="color" value="{{ old('color', $series?->color ?? '#8b5cf6') }}" class="form-input" placeholder="#8b5cf6">
                </div>
                <div>
                    <label class="form-label">Age Group <span class="font-normal normal-case text-white/30">(optional)</span>
                        <x-help-tip text="Restricts which player age band sees this series. Leave blank so every player can see it." example="13-17" />
                    </label>
                    <input type="text" name="age_group" value="{{ old('age_group', $series?->age_group) }}" class="form-input" placeholder="all / 13-17 / 18+">
                </div>
                <div>
                    <label class="form-label">Display Order
                        <x-help-tip text="Where this series sits among other series, lowest first." example="1" />
                    </label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $series?->sort_order ?? 0) }}" class="form-input" min="0">
                </div>
            </div>
            <div class="flex items-center gap-6 mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $series?->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 accent-emerald-500">
                    <span class="text-sm font-bold text-white/70">Active (visible to players)
                        <x-help-tip text="Controls whether this series appears anywhere in the game. Uncheck to keep a draft without deleting it." />
                    </span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 14px rgba(99,102,241,.4);">
                {{ $mode === 'create' ? 'Create Series' : 'Save Changes' }}
            </button>
            <a href="{{ route('gameset.course-series.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-white/50 hover:text-white hover:bg-white/5 transition-all">Cancel</a>
        </div>
    </form>

    @if($mode === 'edit')
    <div class="form-section mt-6">
        <h2 class="text-sm font-black text-white/80 mb-4">Courses in this Series</h2>
        @php $courses = $series->courses; @endphp
        @if($courses->isEmpty())
        <p class="text-sm text-gray-500">No courses assigned yet — open a course's edit page and pick this series from the "Series" dropdown.</p>
        @else
        <table class="tbl w-full">
            <thead><tr><th class="text-left">Topic #</th><th class="text-left">Course</th><th class="text-left">Age Group</th><th class="text-right"></th></tr></thead>
            <tbody>
                @foreach($courses as $c)
                <tr>
                    <td class="text-gray-500">{{ $c->topic_number ?? $c->sort_order }}</td>
                    <td class="text-white font-bold">{{ $c->icon ?? '📚' }} {{ $c->title }}</td>
                    <td class="text-gray-500">{{ $c->age_group ?: 'All Ages' }}</td>
                    <td class="text-right"><a href="{{ route('gameset.courses.edit', $c) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-bold">Edit →</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
</div>
</body>
</html>
