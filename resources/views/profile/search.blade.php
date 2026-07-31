<x-app-layout>
<div class="min-h-screen py-10 px-4" style="background:linear-gradient(135deg,rgba(99,102,241,0.07) 0%,transparent 50%),#07060f;">
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-black text-white">Find Players</h1>
        <a href="{{ route('profile.edit') }}" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">← My Profile</a>
    </div>

    {{-- Search box --}}
    <form action="{{ route('players.search') }}" method="GET">
        <div class="flex gap-3">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search players by name…"
                   autofocus
                   class="flex-1 rounded-xl px-4 py-3 text-white text-sm outline-none focus:ring-2 focus:ring-indigo-500"
                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);">
            <button type="submit" class="px-6 py-3 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 shrink-0"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">Search</button>
        </div>
    </form>

    {{-- Results --}}
    @if(strlen($q) >= 2)
    @if($users->isEmpty())
    <div class="text-center py-16 text-gray-500">
        <div class="text-5xl mb-3">🔍</div>
        <p class="text-base">No players found for "<strong class="text-gray-300">{{ $q }}</strong>"</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($users as $u)
        <a href="{{ route('players.show', $u) }}"
           class="flex items-center gap-4 rounded-2xl p-4 transition-all hover:-translate-y-1"
           style="background:rgba(255,255,255,0.025);border:1px solid rgba(255,255,255,0.07);">

            {{-- Avatar --}}
            <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center font-black text-white text-xl"
                 style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                @if($u->profile_photo)
                    <img src="{{ $u->profile_photo }}" alt="{{ $u->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($u->name,0,1)) }}{{ strtoupper(substr(explode(' ',$u->name)[1]??'',0,1)) }}
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="font-bold text-white text-sm truncate">{{ $u->name }}
                    @if($u->username)<span class="text-indigo-300/80 font-semibold">{{ $u->handle }}</span>@endif
                </div>
                <div class="text-xs text-gray-500 truncate">{{ $u->progress?->chapterIcon() }} {{ $u->progress?->chapterName() ?? 'New Player' }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-indigo-400">Lv.{{ $u->progress?->level ?? 1 }}</span>
                    <span class="text-xs text-gray-600">·</span>
                    <span class="text-xs text-amber-400">{{ $u->badges->count() }} badge{{ $u->badges->count()!==1?'s':'' }}</span>
                </div>
            </div>

            <svg class="w-4 h-4 text-gray-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endforeach
    </div>
    @endif
    @else
    <div class="text-center py-16 text-gray-600">
        <div class="text-5xl mb-3">👥</div>
        <p class="text-base">Type at least 2 characters to search</p>
    </div>
    @endif

</div>
</div>
</x-app-layout>
