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
        .pf-stat { background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:14px; padding:10px 12px; }
        .pf-trend { flex-shrink:0; font-size:11.5px; font-weight:900; padding:.4rem .85rem; border-radius:999px; white-space:nowrap; transition:all .15s; }
        .pf-card { background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:16px; padding:13px; transition:background .15s, border-color .15s, transform .15s; }
        .pf-card:hover { background:rgba(255,255,255,0.05); transform:translateY(-1px); }
        .pf-card.pinned { border-color:rgba(139,92,246,0.35); }
        #pf-newpill { position:sticky; top:70px; z-index:40; display:flex; justify-content:center; margin-bottom:.75rem; }
        #pf-newpill button { pointer-events:auto; }

        /* WhatsApp-style attach control on the New Discussion form */
        .pf-attach-zone { cursor:pointer; border-radius:14px; padding:1.1rem; text-align:center; font-size:.75rem; color:#9ca3af; background:rgba(255,255,255,0.04); border:1px dashed rgba(255,255,255,0.18); transition:border-color .15s, color .15s; }
        .pf-attach-zone:hover { border-color:rgba(139,92,246,0.4); color:#e5e7eb; }
        .pf-attach-preview { position:relative; border-radius:14px; overflow:hidden; border:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.3); }
        .pf-attach-preview img { display:block; width:100%; max-height:14rem; object-fit:contain; background:rgba(0,0,0,0.3); }
        .pf-attach-remove { position:absolute; top:.5rem; right:.5rem; width:1.7rem; height:1.7rem; border-radius:999px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.65); color:#fff; font-weight:900; line-height:1; border:none; cursor:pointer; }
        .pf-attach-name { padding:.4rem .7rem; font-size:.68rem; color:#9ca3af; background:rgba(0,0,0,0.45); }
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
            <a href="{{ route('friends.index') }}" class="text-xs font-bold text-gray-400 hover:text-white transition-colors inline-flex items-center gap-1"><x-icon name="people" class="w-3.5 h-3.5" /> Friends</a>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-gray-400 hover:text-white transition-colors">Dashboard</a>
        </div>
    </div>
</nav>

{{-- ── Hero ── --}}
<div class="border-b border-white/5 py-8"
     style="background: linear-gradient(135deg, rgba(139,92,246,0.10) 0%, rgba(99,102,241,0.05) 100%);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl sm:text-4xl font-black mb-2 inline-flex items-center gap-2"><x-icon name="speech" class="w-8 h-8" /> Pesa Forums</h1>
        <p class="text-gray-400 text-sm sm:text-base leading-relaxed">Talk money. Share real stories.<br class="sm:hidden"> Learn together. Level up together. 🚀</p>

        {{-- Stats strip --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 mt-5">
            <div class="pf-stat">
                <div class="flex items-center gap-1.5 text-sm font-black text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" style="box-shadow:0 0 6px 2px rgba(16,185,129,.5);animation:pfPulse 1.8s infinite;"></span>
                    {{ number_format($onlineNow) }}
                </div>
                <div class="text-[10px] text-gray-500 font-bold mt-0.5">Online now</div>
            </div>
            <div class="pf-stat">
                <div class="text-sm font-black text-white inline-flex items-center gap-1"><x-icon name="speech" class="w-3.5 h-3.5" /> {{ number_format($discussionsToday) }}</div>
                <div class="text-[10px] text-gray-500 font-bold mt-0.5">Discussions today</div>
            </div>
            <div class="pf-stat">
                <div class="text-sm font-black text-white inline-flex items-center gap-1"><x-icon name="speech" class="w-3.5 h-3.5" /> {{ number_format($repliesToday) }}</div>
                <div class="text-[10px] text-gray-500 font-bold mt-0.5">Replies today</div>
            </div>
            <div class="pf-stat flex items-center gap-2">
                @if($topContributor)
                    @if($topContributor->profile_photo)
                    <img src="{{ $topContributor->profile_photo }}" alt="" class="w-7 h-7 rounded-full object-cover flex-shrink-0" style="box-shadow:0 0 0 2px rgba(245,158,11,0.4);">
                    @else
                    <span class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-black text-amber-300" style="background:rgba(245,158,11,0.15);box-shadow:0 0 0 2px rgba(245,158,11,0.4);">{{ strtoupper(substr($topContributor->name,0,1)) }}</span>
                    @endif
                    <div class="min-w-0">
                        <div class="text-xs font-black text-white truncate">👑 {{ Str::limit($topContributor->name, 14) }}</div>
                        <div class="text-[10px] text-gray-500 font-bold">Top contributor</div>
                    </div>
                @else
                <div class="text-[11px] text-gray-500 font-bold self-center">🌱 Be the first top contributor</div>
                @endif
            </div>
        </div>

        {{-- Trending topics --}}
        @if($trending->isNotEmpty())
        <div class="mt-5">
            <div class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2 inline-flex items-center gap-1"><x-icon name="fire" class="w-3 h-3" /> Trending Topics</div>
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1">
                @foreach($trending as $t)
                <a href="{{ route('forums.index', ['category' => $t['key']]) }}" class="pf-trend text-amber-200 hover:text-white"
                   style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.28);">
                    {{ $t['meta']['icon'] }} {{ $t['meta']['label'] }}
                </a>
                @endforeach
                <a href="{{ route('forums.index') }}" class="pf-trend text-gray-400 hover:text-white" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">See all →</a>
            </div>
        </div>
        @endif

        <div class="flex flex-wrap gap-2 mt-5">
            <button @click="newTopicOpen = true" class="text-[12px] font-black px-4 py-2 rounded-full text-white transition-transform hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 20px rgba(124,58,237,0.3);">
                <x-icon name="pencil" class="w-3.5 h-3.5 inline-block" /> New Discussion
            </button>
            @if($showXp ?? true)
            <span class="text-[11px] font-black px-3.5 py-2 rounded-full inline-flex items-center gap-1" style="background:rgba(16,185,129,0.10);border:1px solid rgba(16,185,129,0.25);color:#6ee7b7;"><x-icon name="speech" class="w-3 h-3" /> Reply +25 XP</span>
            <span class="text-[11px] font-bold px-3.5 py-2 rounded-full" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);color:#fcd34d;">XP on your first 5 posts each day</span>
            @endif
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

    {{-- Toolbar: search --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <form method="GET" action="{{ route('forums.index') }}" class="flex-1 flex gap-2">
            @if($activeCategory)
            <input type="hidden" name="category" value="{{ $activeCategory }}">
            @endif
            <input type="text" name="q" value="{{ $search }}" placeholder="Search discussions…"
                   class="flex-1 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                   style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
            <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-black text-gray-300 hover:text-white transition-colors"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);"><x-icon name="search" class="w-4 h-4" /></button>
        </form>
    </div>

    {{-- School board banner --}}
    @if($schoolBoard && $mySchool)
    <div class="mb-4 rounded-2xl px-4 py-3 flex items-center gap-3"
         style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.25);">
        <span class="text-2xl"><x-icon name="graduation" class="w-6 h-6 text-emerald-300" /></span>
        <div class="min-w-0">
            <p class="text-sm font-black text-emerald-300">{{ $mySchool->school_name }} — Private Board</p>
            <p class="text-[11px] text-gray-500">Only members of your school can see these discussions. Teacher challenges are marked 🎯.</p>
        </div>
    </div>
    @endif

    {{-- Category pills --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-3 -mx-1 px-1">
        <a href="{{ route('forums.index', array_filter(['q' => $search])) }}"
           class="flex-shrink-0 text-xs font-black px-3.5 py-2 rounded-full transition-colors {{ !$activeCategory && !$schoolBoard ? 'text-white' : 'text-gray-400 hover:text-white' }}"
           style="{{ !$activeCategory && !$schoolBoard ? 'background:rgba(139,92,246,0.25);border:1px solid rgba(139,92,246,0.5);' : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);' }}">
            <x-icon name="star" class="w-3 h-3 inline-block" /> All
        </a>
        @if($mySchool)
        <a href="{{ route('forums.index', ['board' => 'school']) }}"
           class="flex-shrink-0 text-xs font-black px-3.5 py-2 rounded-full transition-colors {{ $schoolBoard ? 'text-white' : 'text-emerald-400 hover:text-emerald-300' }}"
           style="{{ $schoolBoard ? 'background:rgba(16,185,129,0.25);border:1px solid rgba(16,185,129,0.5);' : 'background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.25);' }}">
            <x-icon name="graduation" class="w-3 h-3 inline-block" /> My School
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

    {{-- Feed heading — one always-on order now (likes + replies + reactions,
         decayed by recency), no more Hot/New/Top/Activity tabs to pick between. --}}
    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
        <span class="text-xs font-black text-gray-400 uppercase tracking-wider">Top Discussions</span>
    </div>

    {{-- "New discussions" pill — X/Twitter-style, appears when fresher topics land while browsing --}}
    <div id="pf-newpill">
        <button type="button" onclick="pfLoadNewest(this)"
                class="hidden items-center gap-1.5 text-xs font-black text-white px-4 py-2 rounded-full shadow-lg"
                style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 6px 20px rgba(124,58,237,0.45);" id="pf-newpill-btn">
            ↑ <span id="pf-newpill-text">New discussions</span>
        </button>
    </div>

    {{-- Topic list + pagination — swapped in place by pfGoToPage()/pfLoadNewest(), no full-page reload --}}
    <div id="pf-results">
        @include('forums.partials._topic-results')
    </div>
</div>

{{-- ── New Discussion Modal ── --}}
<div x-show="newTopicOpen" x-cloak
     x-effect="document.body.style.overflow = newTopicOpen ? 'hidden' : ''"
     class="fixed inset-0 flex items-center justify-center p-4 sm:p-6"
     style="z-index:9500;background:rgba(0,0,0,0.7);backdrop-filter:blur(6px);overflow-y:auto;overscroll-behavior:contain;"
     @click.self="newTopicOpen = false"
     @keydown.escape.window="newTopicOpen = false">
    <div class="w-full sm:max-w-lg rounded-3xl p-4 sm:p-5 my-auto"
         style="background:#0d0b1a;border:1px solid rgba(139,92,246,0.25);">
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-base font-black inline-flex items-center gap-2"><x-icon name="pencil" class="w-4 h-4" /> New Discussion</h2>
            <button @click="newTopicOpen = false" class="text-gray-500 hover:text-white text-xl leading-none">&times;</button>
        </div>

        @if($errors->any())
        <div class="mb-3 rounded-xl px-4 py-3 text-xs font-bold text-red-300" style="background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.25);">
            @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('forums.store') }}" enctype="multipart/form-data" class="space-y-3">
            @if($schoolBoard)
            <input type="hidden" name="board" value="school">
            <p class="rounded-xl px-3 py-2 text-[11px] font-bold text-emerald-300 inline-flex items-center gap-1" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                <x-icon name="graduation" class="w-3 h-3" /> Posting to your school's private board — only {{ $mySchool->school_name }} members will see this.
            </p>
            @endif
            @csrf
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required minlength="5" maxlength="150"
                       placeholder="What do you want to talk about?"
                       class="w-full rounded-xl px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                       style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
            </div>
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Category</label>
                <select name="category"
                        class="w-full rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/40"
                        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
                    @foreach($categories as $key => $meta)
                    @continue($key === 'market-watch')
                    <option value="{{ $key }}" style="background:#0d0b1a;" {{ old('category', $activeCategory) === $key ? 'selected' : '' }}>{{ $meta['icon'] }} {{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Your post</label>
                <textarea name="body" rows="4" required minlength="10" maxlength="5000"
                          placeholder="Share your question, story or tip…"
                          class="w-full rounded-xl px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 resize-y"
                          style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">{{ old('body') }}</textarea>
            </div>
            <div x-data="{ preview: null, name: '' }">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Add an image (optional)</label>
                <input type="file" name="image" accept="image/*" x-ref="pfImgInput" class="hidden"
                       @change="
                           const f = $event.target.files[0];
                           if (!f) { preview = null; name = ''; return; }
                           name = f.name;
                           const reader = new FileReader();
                           reader.onload = e => preview = e.target.result;
                           reader.readAsDataURL(f);
                       ">
                <div class="pf-attach-zone" x-show="!preview" @click="$refs.pfImgInput.click()">
                    📷 Tap to attach a photo — you'll see exactly how it'll look
                </div>
                <div class="pf-attach-preview" x-show="preview" x-cloak>
                    <img :src="preview" alt="">
                    <button type="button" class="pf-attach-remove" @click.stop="preview=null; name=''; $refs.pfImgInput.value=''">&times;</button>
                    <div class="pf-attach-name" x-text="name"></div>
                </div>
            </div>
            @unless($schoolBoard)
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">Who can see this?</label>
                <div class="flex gap-3">
                    <label class="flex-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-200 cursor-pointer" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
                        <input type="radio" name="visibility" value="general" {{ old('visibility', 'general') === 'general' ? 'checked' : '' }}>
                        <x-icon name="globe" class="w-3.5 h-3.5 inline-block" /> General
                    </label>
                    <label class="flex-1 flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-gray-200 cursor-pointer" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);">
                        <input type="radio" name="visibility" value="friends" {{ old('visibility') === 'friends' ? 'checked' : '' }}>
                        <x-icon name="lock" class="w-3.5 h-3.5 inline-block" /> Friends only
                    </label>
                </div>
            </div>
            @endunless
            <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-black text-white transition-transform hover:scale-[1.01]"
                    style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 20px rgba(124,58,237,0.3);">
                Post Discussion
            </button>
        </form>
    </div>
</div>

@if($votesEnabled)
@include('forums.partials.vote-assets')
@endif

<style>@keyframes pfPulse { 0%,100% { opacity:1; } 50% { opacity:.35; } }</style>
<script>
// Swap the results container in place — no full-page reload for pagination
// or the "new discussions" pill. Keeps the URL/back-button in sync via
// history.pushState so a shared/bookmarked page link still works.
function pfSwapResults(url, pushUrl) {
    var resultsEl = document.getElementById('pf-results');
    if (!resultsEl) return;
    resultsEl.style.opacity = '.5';
    fetch(url, { headers: { 'X-Forum-Ajax': '1', 'Accept': 'text/html' } })
        .then(function (r) { return r.ok ? r.text() : Promise.reject(); })
        .then(function (html) {
            resultsEl.innerHTML = html;
            resultsEl.style.opacity = '1';
            if (window.Alpine) window.Alpine.initTree(resultsEl);
            if (pushUrl) history.pushState({}, '', url);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(function () { window.location.href = url; }); // fall back to a real navigation if the fetch fails
}

function pfGoToPage(url) { pfSwapResults(url, true); }

// Clicking the pill is a pure scroll — the new topics were already fetched
// and prepended into the DOM in the background by poll() below, so there's
// nothing left to load.
function pfLoadNewest(btn) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    btn.classList.add('hidden');
    btn.classList.remove('flex');
}

(function () {
    var params = new URLSearchParams(window.location.search);
    // New-topic prepending only makes sense against page 1's view.
    if (params.get('page') && params.get('page') !== '1') return;

    function poll() {
        if (document.hidden) return;
        var list = document.getElementById('pf-topic-list');
        var since = list?.dataset.newest;
        if (!since) return;
        var checkUrl = '{{ route('forums.check-new') }}?since=' + encodeURIComponent(since)
            + (params.get('category') ? '&category=' + encodeURIComponent(params.get('category')) : '')
            + (params.get('board') ? '&board=' + encodeURIComponent(params.get('board')) : '');
        fetch(checkUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d || !d.count || !d.html) return;

                // Silently prepend now — clicking the pill (if it even shows)
                // never has to fetch anything itself.
                list.insertAdjacentHTML('afterbegin', d.html);
                if (window.Alpine) window.Alpine.initTree(list);
                list.dataset.newest = d.newest;

                // Only surface the pill if the user has actually scrolled away
                // from the top — if they're already up here, the new topics
                // just quietly appeared where they can already see them.
                if (window.scrollY < 260) return;

                var btn = document.getElementById('pf-newpill-btn');
                var txt = document.getElementById('pf-newpill-text');
                txt.textContent = d.count >= 20 ? '20+ new discussions' : (d.count === 1 ? '1 new discussion' : d.count + ' new discussions');
                btn.classList.remove('hidden');
                btn.classList.add('flex');
            })
            .catch(function () {});
    }
    setInterval(poll, 25000);
})();
</script>

<x-mobile-bottom-nav active="city" />
</body>
</html>
