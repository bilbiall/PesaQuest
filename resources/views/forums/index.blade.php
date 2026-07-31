<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pesa Forums — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="text-white min-h-screen" x-data="{ newTopicOpen: {{ $errors->any() ? 'true' : 'false' }} }">

{{-- ── Nav ── --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="{{ route('world') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Community
        </a>
        <div class="flex items-center gap-4">
            <a href="{{ route('friends.index') }}" class="text-xs font-bold text-gray-400 hover:text-white transition-colors">👥 Friends</a>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-gray-400 hover:text-white transition-colors">Dashboard</a>
        </div>
    </div>
</nav>

{{-- ── Hero ── --}}
<div class="border-b border-white/5 py-10"
     style="background: linear-gradient(135deg, rgba(139,92,246,0.10) 0%, rgba(99,102,241,0.05) 100%);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl sm:text-4xl font-black mb-2">🗣️ Pesa Forums</h1>
        <p class="text-gray-400">Talk money with the community — questions, wins, hustles and hard lessons.</p>
        <div class="flex flex-wrap gap-2 mt-4">
            <span class="text-[11px] font-black px-3 py-1.5 rounded-full" style="background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.3);color:#c4b5fd;">✍️ New discussion +40 XP</span>
            <span class="text-[11px] font-black px-3 py-1.5 rounded-full" style="background:rgba(16,185,129,0.10);border:1px solid rgba(16,185,129,0.25);color:#6ee7b7;">💬 Reply +25 XP</span>
            <span class="text-[11px] font-bold px-3 py-1.5 rounded-full" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);color:#fcd34d;">XP on your first 5 posts each day</span>
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-6 rounded-2xl px-4 py-3 text-sm font-bold text-emerald-300" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 rounded-2xl px-4 py-3 text-sm font-bold text-amber-300" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);">
        ⚠️ {{ session('error') }}
    </div>
    @endif

    {{-- Toolbar: search + new discussion --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <form method="GET" action="{{ route('forums.index') }}" class="flex-1 flex gap-2">
            @if($activeCategory)
            <input type="hidden" name="category" value="{{ $activeCategory }}">
            @endif
            <input type="text" name="q" value="{{ $search }}" placeholder="Search discussions…"
                   class="flex-1 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
            <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-black text-gray-300 hover:text-white transition-colors"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);">🔍</button>
        </form>
        <button @click="newTopicOpen = true"
                class="px-5 py-2.5 rounded-xl text-sm font-black text-white transition-transform hover:scale-[1.02]"
                style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 20px rgba(124,58,237,0.3);">
            ✍️ New Discussion
        </button>
    </div>

    {{-- School board banner --}}
    @if($schoolBoard && $mySchool)
    <div class="mb-4 rounded-2xl px-4 py-3 flex items-center gap-3"
         style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.25);">
        <span class="text-2xl">🏫</span>
        <div class="min-w-0">
            <p class="text-sm font-black text-emerald-300">{{ $mySchool->school_name }} — Private Board</p>
            <p class="text-[11px] text-gray-500">Only members of your school can see these discussions. Teacher challenges are marked 🎯.</p>
        </div>
    </div>
    @endif

    {{-- Category pills --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-6 -mx-1 px-1">
        <a href="{{ route('forums.index', array_filter(['q' => $search])) }}"
           class="flex-shrink-0 text-xs font-black px-3.5 py-2 rounded-full transition-colors {{ !$activeCategory && !$schoolBoard ? 'text-white' : 'text-gray-400 hover:text-white' }}"
           style="{{ !$activeCategory && !$schoolBoard ? 'background:rgba(139,92,246,0.25);border:1px solid rgba(139,92,246,0.5);' : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);' }}">
            ✨ All
        </a>
        @if($mySchool)
        <a href="{{ route('forums.index', ['board' => 'school']) }}"
           class="flex-shrink-0 text-xs font-black px-3.5 py-2 rounded-full transition-colors {{ $schoolBoard ? 'text-white' : 'text-emerald-400 hover:text-emerald-300' }}"
           style="{{ $schoolBoard ? 'background:rgba(16,185,129,0.25);border:1px solid rgba(16,185,129,0.5);' : 'background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.25);' }}">
            🏫 My School
            @if($schoolTopicCount > 0)
            <span class="text-gray-500 font-bold ml-1">{{ $schoolTopicCount }}</span>
            @endif
        </a>
        @endif
        @foreach($categories as $key => $meta)
        <a href="{{ route('forums.index', array_filter(['category' => $key, 'q' => $search])) }}"
           class="flex-shrink-0 text-xs font-black px-3.5 py-2 rounded-full transition-colors {{ $activeCategory === $key ? 'text-white' : 'text-gray-400 hover:text-white' }}"
           style="{{ $activeCategory === $key ? 'background:rgba(139,92,246,0.25);border:1px solid rgba(139,92,246,0.5);' : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);' }}">
            {{ $meta['icon'] }} {{ $meta['label'] }}
            @if(($counts[$key] ?? 0) > 0)
            <span class="text-gray-500 font-bold ml-1">{{ $counts[$key] }}</span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- Sort tabs (X-style feed) --}}
    @if($votesEnabled)
    <div class="flex gap-1 mb-5 rounded-2xl p-1 w-fit" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
        @foreach(['hot' => '🔥 Hot', 'new' => '✨ New', 'top' => '🏆 Top'] as $key => $lbl)
        <a href="{{ route('forums.index', array_filter(['sort' => $key, 'category' => $activeCategory, 'q' => $search ?: null, 'board' => $schoolBoard ? 'school' : null])) }}"
           class="text-xs font-black px-4 py-2 rounded-xl transition-colors {{ $sort === $key ? 'text-white' : 'text-gray-500 hover:text-white' }}"
           style="{{ $sort === $key ? 'background:rgba(139,92,246,0.25);border:1px solid rgba(139,92,246,0.45);' : '' }}">
            {{ $lbl }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Topic list --}}
    @if($topics->isEmpty())
    <div class="text-center py-20 rounded-2xl" style="background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.1);">
        <p class="text-5xl mb-4">🌱</p>
        <p class="text-xl font-black text-gray-300">No discussions here yet</p>
        <p class="text-gray-500 mt-2 text-sm">Be the first to break the ice — ask a question or share a money win.</p>
        <button @click="newTopicOpen = true" class="mt-6 inline-block px-6 py-3 rounded-xl text-sm font-black text-white"
                style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">✍️ Start a Discussion · +40 XP</button>
    </div>
    @else
    <div class="space-y-3">
        @foreach($topics as $topic)
        @php $topBadges = $topic->user?->badges?->sortByDesc(fn ($b) => $b->pivot->earned_at)->take(2) ?? collect(); @endphp
        <a href="{{ route('forums.show', $topic->slug) }}"
           class="block rounded-2xl p-4 transition-colors hover:bg-white/[0.05]"
           style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,{{ $topic->is_pinned ? '0.14' : '0.07' }});{{ $topic->is_pinned ? 'border-color:rgba(139,92,246,0.35);' : '' }}">
            <div class="flex items-start gap-3">
                {{-- Author avatar --}}
                @if($topic->user?->profile_photo)
                <img src="{{ $topic->user->profile_photo }}" alt="" class="w-10 h-10 rounded-full object-cover flex-shrink-0" style="box-shadow:0 0 0 2px rgba(139,92,246,0.25);">
                @else
                <span class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-black text-violet-300" style="background:rgba(139,92,246,0.2);box-shadow:0 0 0 2px rgba(139,92,246,0.25);">{{ strtoupper(substr($topic->user?->name ?? '?', 0, 1)) }}</span>
                @endif

                <div class="min-w-0 flex-1">
                    {{-- Author line --}}
                    <div class="flex items-center gap-1.5 flex-wrap text-[11px]">
                        <span class="font-black text-white">{{ $topic->is_challenge && $topic->posted_by_name ? $topic->posted_by_name . ' (Teacher)' : ($topic->user?->name ?? 'Player') }}</span>
                        @if($topic->user?->username && !$topic->is_challenge)
                        <span class="text-gray-500 font-bold">{{ $topic->user->handle }}</span>
                        @endif
                        @if($topic->user?->progress)
                        <span class="font-black px-1.5 py-0.5 rounded text-[9px] text-amber-300" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);">Lv{{ $topic->user->progress->level ?? 1 }}</span>
                        @endif
                        @foreach($topBadges as $b)
                        <span title="{{ $b->name }}" class="text-[13px]">@if($b->image_url)<img src="{{ $b->image_url }}" class="w-4 h-4 rounded-full inline object-cover" alt="{{ $b->name }}">@else{{ $b->icon }}@endif</span>
                        @endforeach
                        @if($votesEnabled && ($topic->user->forum_karma ?? 0) != 0)
                        <span class="text-gray-500 font-bold" title="Forum karma">✦ {{ number_format($topic->user->forum_karma) }}</span>
                        @endif
                        <span class="text-gray-600">·</span>
                        <span class="text-gray-500">{{ ($topic->last_activity_at ?? $topic->created_at)?->diffForHumans(short: true) }}</span>
                        <span class="ml-auto text-[10px] font-black px-2 py-0.5 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">{{ $categories[$topic->category]['icon'] ?? '💬' }} {{ $categories[$topic->category]['label'] ?? ucfirst($topic->category) }}</span>
                    </div>

                    {{-- Flags + title + preview --}}
                    <div class="flex items-center gap-2 flex-wrap mt-1.5">
                        @if($topic->is_challenge)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.4);color:#6ee7b7;">🎯 Teacher Challenge</span>
                        @endif
                        @if($topic->is_pinned)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full" style="background:rgba(139,92,246,0.18);border:1px solid rgba(139,92,246,0.4);color:#c4b5fd;">📌 Pinned</span>
                        @endif
                        @if($topic->is_locked)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full" style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fcd34d;">🔒 Locked</span>
                        @endif
                        <h2 class="text-sm sm:text-base font-black text-white leading-snug">{{ $topic->title }}</h2>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ Str::limit($topic->body, 170) }}</p>

                    {{-- Action row --}}
                    <div class="flex items-center gap-3 mt-2.5 text-[11px] text-gray-500 flex-wrap">
                        @if($votesEnabled)
                        <span class="fv-wrap" data-type="topic" data-id="{{ $topic->id }}">
                            <button type="button" class="fv-btn fv-up {{ ($myTopicVotes[$topic->id] ?? 0) === 1 ? 'fv-on' : '' }}" title="Upvote" onclick="event.preventDefault();event.stopPropagation();fvVote(this,'up')">▲</button>
                            <b class="fv-score">{{ number_format($topic->score ?? 0) }}</b>
                            <button type="button" class="fv-btn fv-down {{ ($myTopicVotes[$topic->id] ?? 0) === -1 ? 'fv-dn' : '' }}" title="Downvote" onclick="event.preventDefault();event.stopPropagation();fvVote(this,'down')">▼</button>
                        </span>
                        @endif
                        <span>💬 {{ $topic->replies_count }} {{ Str::plural('reply', $topic->replies_count) }}</span>
                        <span>👁️ {{ number_format($topic->views) }}</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($topics->hasPages())
    <div class="mt-8 flex items-center justify-between text-sm">
        @if($topics->onFirstPage())
        <span class="px-4 py-2 rounded-xl text-gray-600" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">← Prev</span>
        @else
        <a href="{{ $topics->previousPageUrl() }}" class="px-4 py-2 rounded-xl font-bold text-gray-300 hover:text-white" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">← Prev</a>
        @endif
        <span class="text-xs text-gray-500">Page {{ $topics->currentPage() }} of {{ $topics->lastPage() }}</span>
        @if($topics->hasMorePages())
        <a href="{{ $topics->nextPageUrl() }}" class="px-4 py-2 rounded-xl font-bold text-gray-300 hover:text-white" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">Next →</a>
        @else
        <span class="px-4 py-2 rounded-xl text-gray-600" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">Next →</span>
        @endif
    </div>
    @endif
    @endif
</div>

{{-- ── New Discussion Modal ── --}}
<div x-show="newTopicOpen" x-cloak
     x-effect="document.body.style.overflow = newTopicOpen ? 'hidden' : ''"
     class="fixed inset-0 flex items-center justify-center p-4 sm:p-6"
     style="z-index:9500;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);overflow-y:auto;overscroll-behavior:contain;"
     @click.self="newTopicOpen = false"
     @keydown.escape.window="newTopicOpen = false">
    <div class="w-full sm:max-w-lg rounded-3xl p-6 my-auto"
         style="background:#0d0b1a;border:1px solid rgba(139,92,246,0.25);">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-black">✍️ New Discussion</h2>
            <button @click="newTopicOpen = false" class="text-gray-500 hover:text-white text-xl leading-none">&times;</button>
        </div>

        @if($errors->any())
        <div class="mb-4 rounded-xl px-4 py-3 text-xs font-bold text-red-300" style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.25);">
            @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('forums.store') }}" class="space-y-4">
            @if($schoolBoard)
            <input type="hidden" name="board" value="school">
            <p class="rounded-xl px-3 py-2 text-[11px] font-bold text-emerald-300" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                🏫 Posting to your school's private board — only {{ $mySchool->school_name }} members will see this.
            </p>
            @endif
            @csrf
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required minlength="5" maxlength="150"
                       placeholder="What do you want to talk about?"
                       class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                       style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Category</label>
                <select name="category"
                        class="w-full rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
                    @foreach($categories as $key => $meta)
                    <option value="{{ $key }}" style="background:#0d0b1a;" {{ old('category', $activeCategory) === $key ? 'selected' : '' }}>{{ $meta['icon'] }} {{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Your post</label>
                <textarea name="body" rows="5" required minlength="10" maxlength="5000"
                          placeholder="Share your question, story or tip…"
                          class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 resize-y"
                          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">{{ old('body') }}</textarea>
            </div>
            <button type="submit" class="w-full py-3 rounded-xl text-sm font-black text-white transition-transform hover:scale-[1.01]"
                    style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 20px rgba(124,58,237,0.3);">
                Post Discussion · +40 XP
            </button>
        </form>
    </div>
</div>

@if($votesEnabled)
@include('forums.partials.vote-assets')
@endif

<x-mobile-bottom-nav active="city" />
</body>
</html>
