@props(['friends', 'name', 'label', 'hint' => null])

<div class="profile-card" x-data="{ search: '', count: 0 }" x-init="count = $el.querySelectorAll('input[type=checkbox]:checked').length">
    <div class="flex items-center justify-between mb-2">
        <label class="!mb-0">{{ $label }}</label>
        <span class="text-[.6rem] font-bold text-indigo-300" x-show="count > 0" x-text="count + ' selected'"></span>
    </div>
    <input type="text" x-model="search" placeholder="Search friends…" class="mb-2">
    <div class="max-h-56 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-1.5 pr-1">
        @forelse($friends as $f)
        <label class="friend-check" data-name="{{ strtolower($f->name) }}" x-show="search === '' || $el.dataset.name.includes(search.toLowerCase())">
            <input type="checkbox" name="{{ $name }}" value="{{ $f->id }}" class="!w-auto" style="width:1rem;flex-shrink:0;" @change="count = $root.querySelectorAll('input[type=checkbox]:checked').length">
            <div class="w-7 h-7 rounded-full overflow-hidden flex items-center justify-center font-black text-white text-[.68rem] flex-shrink-0" style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                @if($f->profile_photo)
                    <img src="{{ $f->profile_photo }}" alt="" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($f->name, 0, 1)) }}
                @endif
            </div>
            <span class="text-xs text-white font-semibold truncate">{{ $f->name }}</span>
        </label>
        @empty
        <p class="text-xs text-gray-500 col-span-2">No friends yet.</p>
        @endforelse
    </div>
    @if($hint)
    <p class="text-[.62rem] text-gray-500 mt-1.5">{{ $hint }}</p>
    @endif
</div>
