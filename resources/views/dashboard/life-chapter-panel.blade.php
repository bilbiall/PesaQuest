{{-- ══════════════════════════════════════════════════════════
     LIFE CHAPTER PANEL
     Shows: chapter, net worth progress to next chapter, recent events
     ══════════════════════════════════════════════════════════ --}}

@php
    $chapterKey     = $progress->chapterKey();
    $chapterName    = $progress->chapterName();
    $chapterIcon    = $progress->chapterIcon();
    $chapterTagline = $progress->chapterTagline();
    $netWorth       = (int)($progress->net_worth_cache ?? 0);
    $nextThreshold  = $progress->nextChapterNetWorth();
    $amountLeft     = $progress->netWorthToNextChapter();

    // Chapter net worth bands [min, max] for progress bar (gameset-configured)
    $chapterBands = \App\Models\UserProgress::chapterBands();
    [$bandMin, $bandMax] = $chapterBands[$chapterKey] ?? [0, 50_000];
    $chapterPct = $nextThreshold
        ? min(100, (int)(($netWorth - $bandMin) / max(1, $bandMax - $bandMin) * 100))
        : 100;

    $chapterColor = match($chapterKey) {
        'student'  => ['text' => 'text-sky-400',    'bar' => '#38bdf8', 'bg' => 'rgba(56,189,248,0.1)',  'border' => 'rgba(56,189,248,0.25)'],
        'graduate' => ['text' => 'text-emerald-400','bar' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)', 'border' => 'rgba(16,185,129,0.25)'],
        'hustler'  => ['text' => 'text-orange-400', 'bar' => '#fb923c', 'bg' => 'rgba(251,146,60,0.1)',  'border' => 'rgba(251,146,60,0.25)'],
        'settler'  => ['text' => 'text-violet-400', 'bar' => '#a78bfa', 'bg' => 'rgba(167,139,250,0.1)','border' => 'rgba(167,139,250,0.25)'],
        'builder'  => ['text' => 'text-amber-400',  'bar' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)', 'border' => 'rgba(245,158,11,0.25)'],
        'elder'    => ['text' => 'text-rose-400',   'bar' => '#fb7185', 'bg' => 'rgba(251,113,133,0.1)','border' => 'rgba(251,113,133,0.25)'],
        default    => ['text' => 'text-gray-400',   'bar' => '#9ca3af', 'bg' => 'rgba(156,163,175,0.1)','border' => 'rgba(156,163,175,0.25)'],
    };

    // Recent life events
    $recentLifeEvents = \App\Models\PlayerLifeEvent::where('user_id', $user->id)
        ->with('lifeEvent')
        ->orderByDesc('tick_triggered')
        ->take(3)
        ->get();
@endphp

@php
$chapterImgKw = match($chapterKey) {
    'student'  => 'university,student,africa',
    'graduate' => 'graduation,career,young',
    'hustler'  => 'entrepreneur,hustle,nairobi',
    'settler'  => 'family,home,suburban',
    'builder'  => 'wealth,success,skyline',
    'elder'    => 'wisdom,retirement,peaceful',
    default    => 'life,journey,africa',
};
@endphp
<div class="rounded-3xl overflow-hidden relative isolate" style="background:linear-gradient(160deg,rgba(15,23,42,0.9),rgba(30,27,75,0.7));border:1px solid {{ $chapterColor['border'] }};">
    <img src="https://loremflickr.com/800/600/{{ $chapterImgKw }}?lock=ch{{ $chapterKey }}"
         class="absolute inset-0 w-full h-full object-cover pointer-events-none"
         style="z-index:-1;opacity:0.12;mix-blend-mode:luminosity;"
         loading="lazy" alt="" onerror="this.style.display='none'"/>

    {{-- Header --}}
    <div class="px-5 py-4 flex items-center justify-between" style="border-bottom:1px solid rgba(255,255,255,0.05);">
        <div class="flex items-center gap-2">
            <span class="text-lg">{{ $chapterIcon }}</span>
            <span class="font-black text-white text-sm">Life Chapter</span>
        </div>
        <a href="{{ route('life.timeline') }}"
           class="text-xs font-semibold {{ $chapterColor['text'] }} hover:opacity-80 transition-opacity">
            Timeline →
        </a>
    </div>

    <div class="p-5 space-y-4">

        {{-- Chapter card --}}
        <div class="rounded-2xl p-4" style="background:{{ $chapterColor['bg'] }};border:1px solid {{ $chapterColor['border'] }};">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Current Chapter</p>
                    <p class="text-xl font-black {{ $chapterColor['text'] }}">{{ $chapterName }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $chapterTagline }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-black {{ $chapterColor['text'] }}">Ksh {{ number_format($netWorth) }}</p>
                    <p class="text-xs text-gray-500">net worth</p>
                </div>
            </div>
            {{-- Chapter progress bar --}}
            <div class="h-1.5 rounded-full" style="background:rgba(255,255,255,0.08);">
                <div class="h-1.5 rounded-full transition-all duration-700" style="width:{{ $chapterPct }}%;background:{{ $chapterColor['bar'] }};"></div>
            </div>
            @if($nextThreshold)
            <p class="text-xs text-gray-500 mt-2">Ksh {{ number_format($amountLeft) }} more to reach the next chapter ({{ number_format($nextThreshold) }})</p>
            @else
            <p class="text-xs text-gray-500 mt-2">You've reached the final chapter. The elder's wisdom is yours.</p>
            @endif
        </div>

        {{-- Chapter progression timeline (mini) --}}
        <div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Your Journey</p>
            <div class="flex items-center gap-1">
                @php
                    $chapters = [
                        ['key' => 'student',  'icon' => '🎒', 'label' => '0'],
                        ['key' => 'graduate', 'icon' => '🎓', 'label' => '50K'],
                        ['key' => 'hustler',  'icon' => '💪', 'label' => '200K'],
                        ['key' => 'settler',  'icon' => '🏠', 'label' => '1M'],
                        ['key' => 'builder',  'icon' => '📊', 'label' => '5M'],
                        ['key' => 'elder',    'icon' => '🌟', 'label' => '20M'],
                    ];
                    $chapterOrder = array_column($chapters, 'key');
                    $currentIdx = array_search($chapterKey, $chapterOrder);
                @endphp
                @foreach($chapters as $idx => $ch)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm transition-all
                        {{ $idx < $currentIdx ? 'opacity-60' : ($idx === $currentIdx ? 'ring-2 ring-offset-1 ring-offset-transparent' : 'opacity-25') }}"
                         style="{{ $idx === $currentIdx ? 'background:' . $chapterColor['bg'] . ';border:1px solid ' . $chapterColor['border'] . ';ring-color:' . $chapterColor['bar'] . ';' : 'background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);' }}">
                        {{ $ch['icon'] }}
                    </div>
                    <p class="text-[9px] text-gray-600 text-center hidden sm:block">{{ $ch['label'] }}</p>
                </div>
                @if($idx < count($chapters) - 1)
                <div class="flex-1 h-px" style="background:{{ $idx < $currentIdx ? $chapterColor['bar'] : 'rgba(255,255,255,0.1)' }};opacity:{{ $idx < $currentIdx ? '0.5' : '1' }};"></div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Recent life events --}}
        @if($recentLifeEvents->count() > 0)
        <div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Recent Events</p>
            <div class="space-y-1.5">
                @foreach($recentLifeEvents as $ple)
                <div class="flex items-start gap-2 px-3 py-2 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                    <span class="text-base flex-shrink-0">{{ $ple->lifeEvent->icon ?? '⚡' }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-300 truncate">{{ $ple->lifeEvent->title ?? 'Event' }}</p>
                        <p class="text-[10px] text-gray-500">Level {{ $ple->game_age_at_trigger }} · Game Day {{ $ple->tick_triggered }}</p>
                    </div>
                    @if(!empty($ple->effect_applied['balance_change']) && $ple->effect_applied['balance_change'] != 0)
                    <span class="text-[10px] font-black flex-shrink-0 {{ $ple->effect_applied['balance_change'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $ple->effect_applied['balance_change'] >= 0 ? '+' : '' }}{{ number_format($ple->effect_applied['balance_change']) }}
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
