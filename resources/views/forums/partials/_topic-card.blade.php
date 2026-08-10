@php $topBadges = $topic->user?->badges?->sortByDesc(fn ($b) => $b->pivot->earned_at)->take(2) ?? collect(); @endphp
<a href="{{ route('forums.show', $topic->slug) }}"
   class="pf-card block {{ $topic->is_pinned ? 'pinned' : '' }}">
    <div class="flex items-start justify-between gap-2 mb-2">
        <div class="flex items-center gap-2 min-w-0">
            {{-- Author avatar --}}
            @if($topic->user?->profile_photo)
            <img src="{{ $topic->user->profile_photo }}" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0" style="box-shadow:0 0 0 2px rgba(139,92,246,0.25);">
            @else
            <span class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-black text-violet-300" style="background:rgba(139,92,246,0.2);box-shadow:0 0 0 2px rgba(139,92,246,0.25);">{{ strtoupper(substr($topic->user?->name ?? '?', 0, 1)) }}</span>
            @endif
            <div class="min-w-0 leading-tight">
                <div class="flex items-center gap-1.5 flex-wrap text-[11.5px]">
                    <span class="font-black text-white truncate">{{ $topic->is_challenge && $topic->posted_by_name ? $topic->posted_by_name . ' (Teacher)' : ($topic->user?->name ?? 'Player') }}</span>
                    @if($topic->user?->username && !$topic->is_challenge)
                    <span class="text-gray-500 font-bold">{{ $topic->user->handle }}</span>
                    @endif
                    @if($topic->user?->progress)
                    <span class="font-black px-1.5 py-0.5 rounded text-[9px] text-amber-300" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);">Lv{{ $topic->user->progress->level ?? 1 }}</span>
                    @endif
                    @foreach($topBadges as $b)
                    <span title="{{ $b->name }}" class="text-[13px]">@if($b->image_url)<img src="{{ $b->image_url }}" class="w-4 h-4 rounded-full inline object-cover" alt="{{ $b->name }}">@else<x-icon :name="$b->icon" class="w-4 h-4 inline-block" />@endif</span>
                    @endforeach
                </div>
                <div class="text-[10.5px] text-gray-500 mt-0.5">
                    {{ ($topic->last_activity_at ?? $topic->created_at)?->diffForHumans(short: true) }}
                    @if($votesEnabled && ($topic->user->forum_karma ?? 0) != 0)
                    <span class="text-gray-600">·</span> ✦ {{ number_format($topic->user->forum_karma) }}
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1.5 flex-shrink-0">
            @if($topic->is_pinned)
            <span class="text-[10px] font-black px-2 py-1 rounded-full" style="background:rgba(139,92,246,0.18);border:1px solid rgba(139,92,246,0.4);color:#c4b5fd;"><x-icon name="pin" class="w-2.5 h-2.5" /></span>
            @endif
            <span class="text-[10px] font-black px-2.5 py-1 rounded-full text-gray-400 whitespace-nowrap" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">{{ $categories[$topic->category]['icon'] ?? '💬' }} {{ $categories[$topic->category]['label'] ?? ucfirst($topic->category) }}</span>
        </div>
    </div>

    {{-- Flags --}}
    @if($topic->is_challenge || $topic->isFriendsOnly() || $topic->is_locked)
    <div class="flex items-center gap-2 flex-wrap mb-1.5">
        @if($topic->is_challenge)
        <span class="text-[10px] font-black px-2 py-0.5 rounded-full inline-flex items-center gap-1" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.4);color:#6ee7b7;"><x-icon name="target" class="w-2.5 h-2.5" /> Teacher Challenge</span>
        @endif
        @if($topic->isFriendsOnly())
        <span class="text-[10px] font-black px-2 py-0.5 rounded-full inline-flex items-center gap-1" style="background:rgba(236,72,153,0.12);border:1px solid rgba(236,72,153,0.35);color:#f9a8d4;"><x-icon name="lock" class="w-2.5 h-2.5" /> Friends only</span>
        @endif
        @if($topic->is_locked)
        <span class="text-[10px] font-black px-2 py-0.5 rounded-full inline-flex items-center gap-1" style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fcd34d;"><x-icon name="lock" class="w-2.5 h-2.5" /> Locked</span>
        @endif
    </div>
    @endif

    <h2 class="text-sm sm:text-base font-black text-white leading-snug">{{ $topic->title }}</h2>
    <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ Str::limit($topic->body, 150) }}</p>
    @if($topic->image_path)
    <img src="{{ $topic->image_path }}" alt="" class="mt-2 rounded-xl w-full object-contain" style="max-height:20rem;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.08);">
    @endif

    {{-- Action row --}}
    <div class="flex items-center gap-3 mt-2.5 text-[11px] text-gray-500 flex-wrap">
        @if($votesEnabled)
        <span class="fv-wrap" data-type="topic" data-id="{{ $topic->id }}" data-no-loader>
            <button type="button" class="fv-btn fv-up {{ ($myTopicVotes[$topic->id] ?? 0) === 1 ? 'fv-on' : '' }}" title="Upvote" onclick="event.preventDefault();event.stopPropagation();fvVote(this,'up')">▲</button>
            <b class="fv-score">{{ number_format($topic->score ?? 0) }}</b>
            <button type="button" class="fv-btn fv-down {{ ($myTopicVotes[$topic->id] ?? 0) === -1 ? 'fv-dn' : '' }}" title="Downvote" onclick="event.preventDefault();event.stopPropagation();fvVote(this,'down')">▼</button>
        </span>
        @endif
        <span>💬 {{ $topic->replies_count }} {{ Str::plural('reply', $topic->replies_count) }}</span>
        <span>👁️ {{ number_format($topic->views) }}</span>
    </div>
</a>
