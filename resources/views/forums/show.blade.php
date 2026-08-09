<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $topic->title }} — Pesa Forums</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
        .rx-chip { display:inline-flex; align-items:center; gap:.4rem; font-size:11.5px; font-weight:900; padding:.5rem .9rem;
                   border-radius:999px; cursor:pointer; transition:all .15s; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.09); color:#9ca3af; }
        .rx-chip:hover { color:#fff; background:rgba(255,255,255,0.08); transform:translateY(-1px); }
        .rx-chip.rx-on { color:#fcd34d; background:rgba(245,158,11,0.14); border-color:rgba(245,158,11,0.4); }
        .composer-icon-btn { width:42px; height:42px; display:flex; align-items:center; justify-content:center; font-size:22px;
                              border-radius:12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.09); cursor:pointer; transition:all .15s; flex-shrink:0; }
        .composer-icon-btn:hover { background:rgba(255,255,255,0.09); transform:translateY(-1px); }
        #emoji-pop { position:absolute; bottom:calc(100% + 8px); left:0; z-index:60; width:264px;
                     background:#14121f; border:1px solid rgba(139,92,246,0.3); border-radius:16px; padding:10px;
                     box-shadow:0 16px 40px rgba(0,0,0,0.5); display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
        #emoji-pop button { font-size:20px; padding:5px 0; border-radius:8px; background:none; border:none; cursor:pointer; }
        #emoji-pop button:hover { background:rgba(255,255,255,0.08); }
    </style>
</head>
@php
    $me = auth()->user();
    $isMod = $me && ($me->is_admin || $me->is_gameset);
    $isAuthor = $me && $me->id === $topic->user_id;
    $catMeta = $categories[$topic->category] ?? ['icon' => '💬', 'label' => ucfirst($topic->category)];
@endphp
<body class="text-white min-h-screen" x-data="{ editing: {{ $errors->any() && old('title') !== null ? 'true' : 'false' }} }">

{{-- ── Nav ── --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="{{ route('forums.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Forums
        </a>
        <div class="flex items-center gap-4 text-xs font-bold">
            <a href="{{ route('world') }}" class="text-gray-400 hover:text-white transition-colors">Community</a>
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition-colors">Dashboard</a>
        </div>
    </div>
</nav>

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
    @if($errors->any())
    <div class="mb-6 rounded-2xl px-4 py-3 text-xs font-bold text-red-300" style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.25);">
        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
    </div>
    @endif

    {{-- ── Topic header ── --}}
    <div class="mb-6">
        <div class="flex items-center justify-between gap-2 flex-wrap mb-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[11px] font-black px-3 py-1 rounded-full" style="background:rgba(139,92,246,0.14);border:1px solid rgba(139,92,246,0.35);color:#c4b5fd;">{{ $catMeta['icon'] }} {{ $catMeta['label'] }}</span>
                @if($topic->is_challenge)
                <span class="text-[11px] font-black px-3 py-1 rounded-full" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.4);color:#6ee7b7;">🎯 Teacher Challenge</span>
                @endif
                @if($topic->isSchoolBoard())
                <span class="text-[11px] font-black px-3 py-1 rounded-full" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#34d399;">🏫 {{ $topic->school?->school_name ?? 'School' }} only</span>
                @endif
                @if($topic->isFriendsOnly())
                <span class="text-[11px] font-black px-3 py-1 rounded-full" style="background:rgba(236,72,153,0.12);border:1px solid rgba(236,72,153,0.35);color:#f9a8d4;">🔒 Friends only</span>
                @endif
                @if($topic->is_locked)
                <span class="text-[11px] font-black px-3 py-1 rounded-full" style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fcd34d;">🔒 Locked</span>
                @endif
            </div>
            @if($topic->is_pinned)
            <span class="text-[11px] font-black px-3 py-1 rounded-full" style="background:rgba(139,92,246,0.18);border:1px solid rgba(139,92,246,0.4);color:#c4b5fd;">📌 Pinned</span>
            @endif
        </div>
        <h1 class="text-2xl sm:text-3xl font-black leading-tight" x-show="!editing">{{ $topic->title }}</h1>
        <div class="flex items-center gap-3 mt-3 text-xs text-gray-500 flex-wrap">
            @php $topBadges = $topic->user?->badges?->sortByDesc(fn ($b) => $b->pivot->earned_at)->take(3) ?? collect(); @endphp
            <span class="flex items-center gap-1.5">
                @if($topic->user?->profile_photo)
                <img src="{{ $topic->user->profile_photo }}" alt="" class="w-6 h-6 rounded-full object-cover">
                @else
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black text-violet-300" style="background:rgba(139,92,246,0.2);">{{ strtoupper(substr($topic->user?->name ?? '?', 0, 1)) }}</span>
                @endif
                @if($topic->user)
                <a href="{{ route('players.show', $topic->user) }}" class="font-bold text-gray-300 hover:text-violet-300 transition-colors">{{ $topic->is_challenge && $topic->posted_by_name ? $topic->posted_by_name . ' (Teacher)' : $topic->user->name }}
                    @if($topic->user->username && !$topic->is_challenge)<span class="text-gray-600 font-semibold">{{ $topic->user->handle }}</span>@endif
                </a>
                @else
                <span class="font-bold text-gray-300">{{ $topic->posted_by_name ?? 'Player' }}</span>
                @endif
                @if($topic->user?->progress)
                <span class="font-black px-1.5 py-0.5 rounded text-[9px] text-amber-300" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);">Lv{{ $topic->user->progress->level ?? 1 }}</span>
                @endif
                @foreach($topBadges as $b)
                <span title="{{ $b->name }}" class="text-sm">@if($b->image_url)<img src="{{ $b->image_url }}" class="w-4 h-4 rounded-full inline object-cover" alt="{{ $b->name }}">@else{{ $b->icon }}@endif</span>
                @endforeach
                @if($votesEnabled && ($topic->user->forum_karma ?? 0) != 0)
                <span class="text-gray-500 font-bold" title="Forum karma">✦ {{ number_format($topic->user->forum_karma) }}</span>
                @endif
            </span>
            <span>🕒 {{ $topic->created_at?->diffForHumans() }}</span>
            <span>👁️ {{ number_format($topic->views) }} views</span>
            <span>💬 {{ $topic->replies_count }} {{ Str::plural('reply', $topic->replies_count) }}</span>
            @if($votesEnabled)
            <span class="fv-wrap ml-auto" data-type="topic" data-id="{{ $topic->id }}" data-no-loader>
                <button type="button" class="fv-btn fv-up {{ ($myTopicVotes[$topic->id] ?? 0) === 1 ? 'fv-on' : '' }}" title="Upvote" onclick="fvVote(this,'up')">▲</button>
                <b class="fv-score">{{ number_format($topic->score ?? 0) }}</b>
                <button type="button" class="fv-btn fv-down {{ ($myTopicVotes[$topic->id] ?? 0) === -1 ? 'fv-dn' : '' }}" title="Downvote" onclick="fvVote(this,'down')">▼</button>
            </span>
            @endif
        </div>

        {{-- Moderation controls --}}
        @if($isAuthor || $isMod)
        <div class="flex items-center gap-2 flex-wrap mt-4">
            <button @click="editing = !editing"
                    class="text-[11px] font-black px-3 py-1.5 rounded-lg text-gray-300 hover:text-white transition-colors"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">✏️ Edit</button>
            <form method="POST" action="{{ route('forums.destroy', $topic) }}" onsubmit="return confirm('Delete this discussion and all its replies?');">
                @csrf @method('DELETE')
                <button type="submit" class="text-[11px] font-black px-3 py-1.5 rounded-lg text-red-300 hover:text-red-200 transition-colors"
                        style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);">🗑️ Delete</button>
            </form>
            @if($isMod)
            <form method="POST" action="{{ route('forums.pin', $topic) }}">
                @csrf
                <button type="submit" class="text-[11px] font-black px-3 py-1.5 rounded-lg text-violet-300 hover:text-violet-200 transition-colors"
                        style="background:rgba(139,92,246,0.1);border:1px solid rgba(139,92,246,0.25);">📌 {{ $topic->is_pinned ? 'Unpin' : 'Pin' }}</button>
            </form>
            <form method="POST" action="{{ route('forums.lock', $topic) }}">
                @csrf
                <button type="submit" class="text-[11px] font-black px-3 py-1.5 rounded-lg text-amber-300 hover:text-amber-200 transition-colors"
                        style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);">🔒 {{ $topic->is_locked ? 'Unlock' : 'Lock' }}</button>
            </form>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Edit form (author/mod) ── --}}
    @if($isAuthor || $isMod)
    <div x-show="editing" x-cloak class="mb-6 rounded-2xl p-5" style="background:rgba(139,92,246,0.05);border:1px solid rgba(139,92,246,0.25);">
        <h2 class="text-sm font-black text-violet-300 mb-4">✏️ Edit Discussion</h2>
        <form method="POST" action="{{ route('forums.update', $topic) }}" class="space-y-3">
            @csrf @method('PUT')
            <input type="text" name="title" value="{{ old('title', $topic->title) }}" required minlength="5" maxlength="150"
                   class="w-full rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
            <textarea name="body" rows="6" required minlength="10" maxlength="5000"
                      class="w-full rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/40 resize-y"
                      style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">{{ old('body', $topic->body) }}</textarea>
            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-black text-white" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">Save Changes</button>
                <button type="button" @click="editing = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-400 hover:text-white" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">Cancel</button>
            </div>
        </form>
    </div>
    @endif

    {{-- ── Topic body ── --}}
    <div class="rounded-2xl p-5 sm:p-6 mb-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);" x-show="!editing">
        <p class="text-sm sm:text-[15px] text-gray-200 leading-relaxed whitespace-pre-line" style="border-left:2px solid rgba(139,92,246,0.35);padding-left:.9rem;">{{ $topic->body }}</p>
        @if($topic->image_path)
        <img src="{{ $topic->image_path }}" alt="" class="mt-4 rounded-xl max-h-[28rem] w-full object-cover" style="border:1px solid rgba(255,255,255,0.08);">
        @endif
    </div>

    {{-- ── Reactions ── --}}
    <div class="flex flex-wrap gap-2 mb-8" id="rx-wrap" data-topic="{{ $topic->id }}">
        @foreach($reactionTypes as $type => $meta)
        @php $count = (int) ($reactionCounts[$type] ?? 0); @endphp
        <button type="button" class="rx-chip {{ in_array($type, $myReactions) ? 'rx-on' : '' }}" data-type="{{ $type }}" onclick="rxToggle(this)">
            <span>{{ $meta['emoji'] }}</span>
            <span>{{ $meta['label'] }}</span>
            <span class="rx-count">{{ $count > 0 ? $count : '' }}</span>
        </button>
        @endforeach
    </div>

    {{-- ── Replies ── --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-black text-gray-400 uppercase tracking-wider">💬 Replies ({{ $topic->replies_count }})</h2>
        <span class="text-[11px] font-bold text-gray-600">Oldest first</span>
    </div>

    @if($replyTree->isEmpty())
    <div class="text-center py-10 rounded-2xl mb-8" style="background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.1);">
        <p class="text-3xl mb-2">🦗</p>
        <p class="text-sm font-bold text-gray-400">No replies yet — be the first to weigh in!</p>
    </div>
    @else
    <div class="space-y-1 mb-6">
        @foreach($replyTree as $reply)
            @include('forums.partials.reply', ['reply' => $reply, 'depth' => 0])
        @endforeach
    </div>

    {{-- Replies pagination --}}
    @if($replies->hasPages())
    <div class="mb-8 flex items-center justify-between text-sm">
        @if($replies->onFirstPage())
        <span class="px-4 py-2 rounded-xl text-gray-600" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">← Prev</span>
        @else
        <a href="{{ $replies->previousPageUrl() }}" class="px-4 py-2 rounded-xl font-bold text-gray-300 hover:text-white" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">← Prev</a>
        @endif
        <span class="text-xs text-gray-500">Page {{ $replies->currentPage() }} of {{ $replies->lastPage() }}</span>
        @if($replies->hasMorePages())
        <a href="{{ $replies->nextPageUrl() }}" class="px-4 py-2 rounded-xl font-bold text-gray-300 hover:text-white" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">Next →</a>
        @else
        <span class="px-4 py-2 rounded-xl text-gray-600" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">Next →</span>
        @endif
    </div>
    @endif
    @endif

    {{-- ── Reply form / locked state ── --}}
    @if($topic->is_locked)
    <div class="rounded-2xl p-6 text-center" style="background:rgba(245,158,11,0.05);border:1px solid rgba(245,158,11,0.2);">
        <p class="text-2xl mb-2">🔒</p>
        <p class="text-sm font-black text-amber-300">This discussion is locked</p>
        <p class="text-xs text-gray-500 mt-1">No new replies can be added.</p>
    </div>
    @else
    <div class="rounded-2xl p-5" style="background:rgba(139,92,246,0.05);border:1px solid rgba(139,92,246,0.2);">
        <h3 class="text-sm font-black text-gray-200 mb-3">✍️ Join the conversation</h3>
        <form method="POST" action="{{ route('forums.reply', $topic) }}" enctype="multipart/form-data" class="space-y-3" x-data="{ fileName: '' }">
            @csrf
            <textarea name="body" id="reply-body" rows="3" required minlength="2" maxlength="3000"
                      placeholder="Share your thoughts…"
                      class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 resize-y"
                      style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">{{ old('body') }}</textarea>
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <button type="button" class="composer-icon-btn" title="Add emoji" onclick="emojiToggle(event, 'reply-body')">😊</button>
                </div>
                <label class="composer-icon-btn" title="Attach an image">
                    📎
                    <input type="file" name="image" accept="image/*" class="hidden" @change="fileName = $event.target.files[0]?.name ?? ''">
                </label>
                <span class="text-[11px] text-gray-500 truncate flex-1" x-text="fileName || 'No image attached'"></span>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-black text-white transition-transform hover:scale-[1.02] flex-shrink-0"
                        style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 20px rgba(124,58,237,0.3);">
                    Reply · +25 XP
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

{{-- Shared emoji popover — one instance reused for whichever composer opened it --}}
<div id="emoji-pop" class="hidden"></div>

@if($votesEnabled)
@include('forums.partials.vote-assets')
@endif

<script>
    const EMOJI_SET = ['😀','😂','😊','😍','🤔','😢','😡','🔥','💯','👍','👎','🙏','💰','💸','🎉','🚀','⭐','❤️','😭','🤯','💪','🎯','👏','🙌'];
    let emojiTargetId = null;

    function emojiToggle(e, targetId) {
        e.stopPropagation();
        const pop = document.getElementById('emoji-pop');
        if (!pop.classList.contains('hidden') && emojiTargetId === targetId) {
            pop.classList.add('hidden');
            return;
        }
        emojiTargetId = targetId;
        pop.innerHTML = EMOJI_SET.map(em => `<button type="button" onclick="emojiInsert('${em}')">${em}</button>`).join('');
        const btn = e.currentTarget;
        btn.parentElement.appendChild(pop);
        pop.classList.remove('hidden');
    }
    function emojiInsert(emoji) {
        const ta = document.getElementById(emojiTargetId);
        if (ta) {
            const start = ta.selectionStart ?? ta.value.length;
            const end = ta.selectionEnd ?? ta.value.length;
            ta.value = ta.value.slice(0, start) + emoji + ta.value.slice(end);
            ta.focus();
            ta.selectionStart = ta.selectionEnd = start + emoji.length;
        }
        document.getElementById('emoji-pop').classList.add('hidden');
    }
    document.addEventListener('click', function (e) {
        const pop = document.getElementById('emoji-pop');
        if (!pop.classList.contains('hidden') && !pop.contains(e.target)) pop.classList.add('hidden');
    });

    async function rxToggle(btn) {
        const wrap = document.getElementById('rx-wrap');
        const type = btn.dataset.type;
        try {
            const res = await fetch('{{ route('forums.react', $topic) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ type }),
            });
            if (!res.ok) return;
            const d = await res.json();
            btn.classList.toggle('rx-on', d.active);
            wrap.querySelectorAll('.rx-chip').forEach(chip => {
                const c = d.counts[chip.dataset.type] || 0;
                chip.querySelector('.rx-count').textContent = c > 0 ? c : '';
            });
        } catch (e) {}
    }
</script>

<x-mobile-bottom-nav active="city" />
</body>
</html>
