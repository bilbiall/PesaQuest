@php
    // Cap visual indentation so deep threads don't overflow on mobile. Each
    // reply nests INSIDE its parent's div, so margin/padding here is a
    // per-level contribution that stacks with every ancestor's — it must be
    // zero past the cap, not just clamped, or the total offset still grows
    // without bound (a depth-40 thread pushed content off-screen before this).
    // Past the cap we stop indenting entirely and just flag who's being
    // replied to via the "↳ replying to" label below.
    $maxNestedDepth = 4;
    $isIndented = $depth > 0 && $depth <= $maxNestedDepth;
    $marginLeft = $isIndented ? '0.7rem' : '0';

    // Long back-and-forth chains get bundled behind a "Show N more replies"
    // toggle instead of auto-expanding forever — collapse once, at the first
    // node past the cutoff depth, rather than re-checking at every level
    // (which would nest a toggle inside a toggle).
    $collapseCutoffDepth   = 2;
    $childrenList          = $reply->children ?? collect();
    $shouldCollapseChildren = $depth === $collapseCutoffDepth && $childrenList->isNotEmpty();
    if ($shouldCollapseChildren) {
        $descendantCount = $childrenList->sum(fn ($c) => 1 + $c->totalDescendantCount());
    }
@endphp
<div id="reply-{{ $reply->id }}" class="mt-2" style="margin-left:{{ $marginLeft }};{{ $isIndented ? 'border-left:2px solid rgba(139,92,246,0.18);padding-left:.6rem;' : '' }}"
     x-data="{ replying: false }">
    <div class="rounded-xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
        <div class="flex items-center justify-between gap-2 mb-1.5">
            <div class="flex items-center gap-1.5">
                @if($reply->user?->profile_photo)
                <img src="{{ $reply->user->profile_photo }}" alt="" class="w-6 h-6 rounded-full object-cover">
                @else
                <span class="w-6 h-6 rounded-full flex items-center justify-center text-[9px] font-black text-violet-300" style="background:rgba(139,92,246,0.2);">{{ strtoupper(substr($reply->user?->name ?? '?', 0, 1)) }}</span>
                @endif
                <div>
                    <p class="text-xs font-black text-gray-200 leading-tight">
                        @if($reply->user)
                        <a href="{{ route('players.show', $reply->user) }}" class="hover:text-violet-300 transition-colors">{{ $reply->user->name }}</a>
                        @else
                        Player
                        @endif
                        @if($reply->user?->progress)
                        <span class="text-[9px] font-black px-1 py-0.5 rounded ml-0.5 text-amber-300" style="background:rgba(245,158,11,0.1);">Lv{{ $reply->user->progress->level ?? 1 }}</span>
                        @endif
                        @if($reply->user_id === $topic->user_id)
                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded ml-1" style="background:rgba(139,92,246,0.15);color:#c4b5fd;">OP</span>
                        @endif
                        @if($depth > 0 && $reply->parent?->user)
                        <span class="text-[10px] text-gray-500 font-semibold">↳ replying to <a href="{{ route('players.show', $reply->parent->user) }}" class="text-violet-300/80 hover:text-violet-300">{{ $reply->parent->user->name }}</a></span>
                        @endif
                    </p>
                    <p class="text-[9.5px] text-gray-600">{{ $reply->created_at?->diffForHumans() }}</p>
                </div>
            </div>
            @if($isMod || ($me && $me->id === $reply->user_id))
            <form method="POST" action="{{ route('forums.replies.destroy', $reply) }}" onsubmit="return confirm('Delete this reply?');">
                @csrf @method('DELETE')
                <button type="submit" class="text-[10px] font-black px-2 py-1 rounded-lg text-red-300/70 hover:text-red-300 transition-colors"
                        style="background:rgba(248,113,113,0.06);border:1px solid rgba(248,113,113,0.15);">🗑️</button>
            </form>
            @endif
        </div>
        <p class="text-[13px] text-gray-300 leading-snug whitespace-pre-line">{{ $reply->body }}</p>
        @if($reply->image_path)
        <img src="{{ $reply->image_path }}" alt="" class="mt-2 rounded-xl w-full object-contain" style="max-height:18rem;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.08);">
        @endif
        <div class="flex items-center gap-3 mt-2">
            @if($votesEnabled)
            <span class="fv-wrap" data-type="reply" data-id="{{ $reply->id }}" data-no-loader>
                <button type="button" class="fv-btn fv-up {{ ($myReplyVotes[$reply->id] ?? 0) === 1 ? 'fv-on' : '' }}" title="Upvote" onclick="fvVote(this,'up')">▲</button>
                <b class="fv-score">{{ number_format($reply->score ?? 0) }}</b>
                <button type="button" class="fv-btn fv-down {{ ($myReplyVotes[$reply->id] ?? 0) === -1 ? 'fv-dn' : '' }}" title="Downvote" onclick="fvVote(this,'down')">▼</button>
            </span>
            @endif
            @if(!$topic->is_locked && $me)
            <button type="button" @click="replying = !replying" class="text-[11px] font-black text-gray-400 hover:text-violet-300 transition-colors">💬 Reply</button>
            @endif
        </div>

        @if(!$topic->is_locked && $me)
        <form x-show="replying" x-cloak method="POST" action="{{ route('forums.reply', $topic) }}" enctype="multipart/form-data" class="mt-3 space-y-2"
              x-data="{ fileName: '', previewUrl: null, clearImage() { this.previewUrl = null; this.fileName = ''; this.$refs.imageInput.value = ''; } }">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $reply->id }}">
            <textarea name="body" id="reply-body-{{ $reply->id }}" rows="2" required minlength="2" maxlength="3000"
                      placeholder="Reply to {{ $reply->user?->name ?? 'this comment' }}…"
                      class="w-full rounded-xl px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500/40 resize-y"
                      style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);"></textarea>
            <div x-show="previewUrl" x-cloak class="relative inline-block">
                <img :src="previewUrl" class="rounded-lg object-cover" style="max-width:160px;max-height:120px;">
                <button type="button" @click="clearImage()" title="Remove image"
                        class="absolute flex items-center justify-center text-white text-xs font-black rounded-full"
                        style="top:-7px;right:-7px;width:20px;height:20px;background:rgba(0,0,0,0.85);border:1px solid rgba(255,255,255,0.2);">✕</button>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="composer-icon-btn" style="width:36px;height:36px;font-size:18px;" title="Add emoji" onclick="emojiToggle(event, 'reply-body-{{ $reply->id }}')">😊</button>
                <label class="composer-icon-btn" style="width:36px;height:36px;font-size:18px;" title="Attach a photo">
                    📷
                    <input type="file" name="image" x-ref="imageInput" accept="image/*" class="hidden"
                           @change="fileName = $event.target.files[0]?.name ?? ''; previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                </label>
                <span class="text-[10px] text-gray-500 flex-1" x-show="!previewUrl">No image</span>
                <span class="text-[10px] text-emerald-400 font-bold flex-1" x-show="previewUrl" x-cloak>📷 Photo attached</span>
                <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-black text-white flex-shrink-0" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">Reply{{ ($showXp ?? true) ? ' · +' . ($forumXpReply ?? 25) . ' XP' : '' }}</button>
            </div>
        </form>
        @endif
    </div>

    @if($shouldCollapseChildren)
    <div x-data="{ threadOpen: false }" style="margin-left:0.6rem;">
        <button type="button" @click="threadOpen = !threadOpen"
                class="mt-1.5 text-[11px] font-black text-violet-300/80 hover:text-violet-300 transition-colors inline-flex items-center gap-1">
            <template x-if="!threadOpen">
                <span>💬 Show {{ $descendantCount }} more {{ Str::plural('reply', $descendantCount) }} ▾</span>
            </template>
            <template x-if="threadOpen">
                <span>▲ Hide replies</span>
            </template>
        </button>
        <div x-show="threadOpen" x-cloak>
            @foreach($childrenList as $child)
                @include('forums.partials.reply', ['reply' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    </div>
    @else
        @foreach($childrenList as $child)
            @include('forums.partials.reply', ['reply' => $child, 'depth' => $depth + 1])
        @endforeach
    @endif
</div>
