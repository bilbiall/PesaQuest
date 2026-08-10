@if($topics->isEmpty())
<div class="text-center py-20 rounded-2xl" style="background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.1);">
    <p class="text-5xl mb-4">🌱</p>
    <p class="text-xl font-black text-gray-300">No discussions here yet</p>
    <p class="text-gray-500 mt-2 text-sm">Be the first to break the ice — ask a question or share a money win.</p>
    <button @click="newTopicOpen = true" class="mt-6 inline-block px-6 py-3 rounded-xl text-sm font-black text-white"
            style="background:linear-gradient(135deg,#7c3aed,#4f46e5);">✍️ Start a Discussion{{ ($showXp ?? true) ? ' · +40 XP' : '' }}</button>
</div>
@else
{{-- data-newest tracks the true max(created_at) among what's on screen, not
     whichever topic happens to rank #1 in the feed order — otherwise the
     "new discussions" poll compares against a stale cursor whenever the top
     spot is held by an older, more-active topic instead of the newest one. --}}
<div class="space-y-2.5" id="pf-topic-list" data-newest="{{ optional($topics->max('created_at'))->toIso8601String() }}">
    @foreach($topics as $topic)
    @include('forums.partials._topic-card')
    @endforeach
</div>

{{-- Pagination — fetched no-reload via pfGoToPage() in the parent page --}}
@if($topics->hasPages())
<div class="mt-8 flex items-center justify-between text-sm">
    @if($topics->onFirstPage())
    <span class="px-4 py-2 rounded-xl text-gray-600" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">← Prev</span>
    @else
    <button type="button" onclick="pfGoToPage('{{ $topics->previousPageUrl() }}')" class="px-4 py-2 rounded-xl font-bold text-gray-300 hover:text-white" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">← Prev</button>
    @endif
    <span class="text-xs text-gray-500">Page {{ $topics->currentPage() }} of {{ $topics->lastPage() }}</span>
    @if($topics->hasMorePages())
    <button type="button" onclick="pfGoToPage('{{ $topics->nextPageUrl() }}')" class="px-4 py-2 rounded-xl font-bold text-gray-300 hover:text-white" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">Next →</button>
    @else
    <span class="px-4 py-2 rounded-xl text-gray-600" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">Next →</span>
    @endif
</div>
@endif
@endif
