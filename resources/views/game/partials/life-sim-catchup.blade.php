@if(!empty($lifeSim) && ($lifeSim['show_wywa'] ?? (!empty($lifeSim['ticks']) || !empty($lifeSim['events']))))
<div
    x-data="{ open: true, activeEdu: null }"
    x-init="$nextTick(() => { playWywaSound(); })"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 flex items-center justify-center p-4"
    style="z-index:9990;background:rgba(0,0,0,0.8);backdrop-filter:blur(8px);overflow-y:auto;overscroll-behavior:contain;"
>
    <div class="w-full max-w-lg rounded-2xl overflow-hidden shadow-2xl my-auto" style="background:linear-gradient(160deg,#0f172a,#1e1b4b);border:1px solid rgba(139,92,246,0.3);">

        {{-- Header --}}
        <div class="px-4 pt-4 pb-3 sm:px-6 sm:pt-6 sm:pb-4" style="background:linear-gradient(135deg,rgba(139,92,246,0.2),rgba(99,102,241,0.1));border-bottom:1px solid rgba(139,92,246,0.2);">
            <div class="flex items-center gap-2.5 sm:gap-3 mb-1">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(139,92,246,0.2);border:1px solid rgba(139,92,246,0.4);">
                    <x-icon name="globe" class="w-4 h-4 sm:w-5 sm:h-5 text-purple-300" />
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-white font-black text-sm sm:text-lg leading-tight">
                        @if(($lifeSim['ticks'] ?? 0) === 0) Welcome Back! @else While You Were Away… @endif
                    </h2>
                    <p class="text-purple-300 text-[.68rem] sm:text-xs font-semibold leading-snug">
                        @if(($lifeSim['ticks'] ?? 0) === 0)
                            Daily bonus ready — claim your streak reward
                        @else
                            {{ $lifeSim['game_time'] }} passed in your virtual life
                            <span class="text-purple-400/60">· ⏱ 1 game day ≈ {{ app(\App\Services\GameClock::class)->approxRealLabel(1) }} real time</span>
                        @endif
                    </p>
                </div>
                {{-- Chapter badge --}}
                @if(!empty($lifeSim['chapter']))
                <div class="text-right flex-shrink-0">
                    <p class="text-sm sm:text-lg leading-none">{{ $lifeSim['chapter_icon'] ?? '' }}</p>
                    <p class="text-[.62rem] sm:text-xs font-black text-purple-300 mt-0.5">{{ $lifeSim['chapter'] }}</p>
                </div>
                @endif
            </div>
            @if($lifeSim['capped'] ?? false)
            <div class="mt-2.5 sm:mt-3 px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-lg text-[.68rem] sm:text-xs font-semibold text-amber-300 leading-snug" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);">
                ⏱️ Pesa City's clock runs fast — we simulated the first {{ $lifeSim['ticks'] ?? 60 }} game days of your absence and paused the rest so nothing overwhelms you.
            </div>
            @endif
        </div>

        {{-- Event list --}}
        <div class="px-3 py-3 sm:px-6 sm:py-4 space-y-1.5 sm:space-y-2 max-h-72 overflow-y-auto">
            @forelse($lifeSim['events'] as $idx => $event)
            @php $evType = $event['type'] ?? ''; @endphp
            <div class="rounded-xl overflow-hidden"
                 style="{{ $evType === 'wages_lost'
                        ? 'background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(190,18,60,0.06));border:1px solid rgba(239,68,68,0.4);'
                        : ($evType === 'salary_ready'
                            ? 'background:linear-gradient(135deg,rgba(16,185,129,0.10),rgba(245,158,11,0.06));border:1px solid rgba(16,185,129,0.35);'
                            : (isset($event['is_milestone']) && $event['is_milestone'] ? 'background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(251,146,60,0.08));border:1px solid rgba(245,158,11,0.35);' : 'background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);')) }}">

                <div class="flex items-start gap-2 sm:gap-3 py-2 px-2.5 sm:py-2.5 sm:px-3">
                    <span class="text-sm sm:text-lg leading-none mt-0.5 flex-shrink-0">{{ $event['icon'] ?? '📌' }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[.78rem] sm:text-sm font-bold {{ $evType === 'wages_lost' ? 'text-red-300' : (isset($event['is_milestone']) && $event['is_milestone'] ? 'text-amber-300' : 'text-gray-200') }} leading-snug">
                                {{ $event['text'] }}
                                @if($evType === 'wages_lost')
                                <span class="ml-1 text-[9px] sm:text-[10px] font-black text-red-400 bg-red-400/15 px-1.5 py-0.5 rounded-full whitespace-nowrap">WAGES LOST</span>
                                @elseif($evType === 'salary_ready')
                                <span class="ml-1 text-[9px] sm:text-[10px] font-black text-emerald-400 bg-emerald-400/15 px-1.5 py-0.5 rounded-full whitespace-nowrap">COLLECT AT WORK</span>
                                @endif
                                @if(isset($event['is_milestone']) && $event['is_milestone'])
                                <span class="ml-1 text-[9px] sm:text-[10px] font-black text-amber-400 bg-amber-400/15 px-1.5 py-0.5 rounded-full whitespace-nowrap">MILESTONE</span>
                                @endif
                            </p>
                            @if(isset($event['delta']) && $event['delta'] != 0)
                            <span class="text-[.7rem] sm:text-xs font-black whitespace-nowrap flex-shrink-0 {{ $event['delta'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $event['delta'] >= 0 ? '+' : '' }}Ksh {{ number_format(abs($event['delta'])) }}
                            </span>
                            @endif
                        </div>
                        @if(!empty($event['sub']))
                        <p class="text-[.7rem] sm:text-xs text-gray-400 mt-0.5 leading-snug">{{ $event['sub'] }}</p>
                        @endif
                    </div>
                    {{-- Edu toggle (only on life_events) --}}
                    @if(!empty($event['edu']))
                    <button @click="activeEdu = (activeEdu === {{ $idx }}) ? null : {{ $idx }}"
                            class="text-indigo-400 hover:text-indigo-300 flex-shrink-0 mt-0.5 transition-colors"
                            title="Financial lesson">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </button>
                    @endif
                </div>

                @if(!empty($event['edu']))
                <div x-show="activeEdu === {{ $idx }}" x-cloak x-transition
                     class="px-2.5 pb-2.5 sm:px-3 sm:pb-3">
                    <p class="text-[.7rem] sm:text-xs text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 rounded-lg px-2.5 py-2 sm:px-3 leading-snug">
                        💡 {{ $event['edu'] }}
                    </p>
                </div>
                @endif
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm py-4">Time passed quietly. Nothing major happened.</p>
            @endforelse
        </div>

        {{-- Net worth summary --}}
        <div class="mx-3 mb-3 px-3 py-2.5 sm:mx-6 sm:mb-4 sm:px-4 sm:py-3 rounded-xl grid grid-cols-2 gap-2 sm:gap-4" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);">
            <div class="min-w-0">
                <p class="text-[.62rem] sm:text-xs text-gray-400 font-semibold uppercase tracking-wide">Balance</p>
                <p class="text-white font-black text-sm sm:text-xl truncate">Ksh {{ number_format($lifeSim['balance'] ?? 0) }}</p>
            </div>
            <div class="text-right min-w-0">
                <p class="text-[.62rem] sm:text-xs text-gray-400 font-semibold uppercase tracking-wide">Net Worth</p>
                <p class="text-purple-300 font-black text-sm sm:text-xl truncate">Ksh {{ number_format($lifeSim['net_worth'] ?? 0) }}</p>
            </div>
        </div>

        {{-- Continue button --}}
        <div class="px-3 pb-3 sm:px-6 sm:pb-6 space-y-2">
            @php $hasPayReady = collect($lifeSim['events'] ?? [])->contains(fn($e) => in_array($e['type'] ?? '', ['salary_ready', 'job_warning'], true)); @endphp
            @if($hasPayReady)
            <a href="{{ route('life.career') }}"
               class="block w-full py-2.5 sm:py-3 rounded-xl font-black text-white text-xs sm:text-sm text-center transition-all hover:scale-[1.02]"
               style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 20px rgba(16,185,129,0.35);">
                💼 Report to Work — collect your pay
            </a>
            @endif
            <button
                @click="open = false; playWywaSound('close')"
                class="w-full py-2.5 sm:py-3 rounded-xl font-black text-white text-xs sm:text-sm transition-all hover:scale-[1.02]"
                style="background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 20px rgba(124,58,237,0.4);"
            >
                Continue My Life &rarr;
            </button>
        </div>
    </div>
</div>
@endif
<script>
function playWywaSound(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const hasSalary = {{ json_encode(collect($lifeSim['events'] ?? [])->contains(fn($e) => ($e['delta'] ?? 0) > 5000)) }};
        const hasLostWages = {{ json_encode(collect($lifeSim['events'] ?? [])->contains(fn($e) => ($e['type'] ?? '') === 'wages_lost')) }};
        if (!type) {
            [523, 659, 784].forEach((freq, i) => {
                const o = ctx.createOscillator(), g = ctx.createGain();
                o.connect(g); g.connect(ctx.destination);
                o.type = 'sine';
                o.frequency.setValueAtTime(freq, ctx.currentTime + i * .12);
                g.gain.setValueAtTime(.15, ctx.currentTime + i * .12);
                g.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + i * .12 + .25);
                o.start(ctx.currentTime + i * .12); o.stop(ctx.currentTime + i * .12 + .25);
            });
            if (hasLostWages) {
                // Missed shifts — a slow descending "you missed out" motif
                setTimeout(() => {
                    try {
                        const ctx2 = new (window.AudioContext || window.webkitAudioContext)();
                        [659,587,523,392].forEach((freq,i) => {
                            const o=ctx2.createOscillator(),g=ctx2.createGain();
                            o.connect(g);g.connect(ctx2.destination);
                            o.type='triangle';
                            o.frequency.setValueAtTime(freq,ctx2.currentTime+i*.18);
                            g.gain.setValueAtTime(.16,ctx2.currentTime+i*.18);
                            g.gain.exponentialRampToValueAtTime(.001,ctx2.currentTime+i*.18+.3);
                            o.start(ctx2.currentTime+i*.18);o.stop(ctx2.currentTime+i*.18+.3);
                        });
                    } catch(e2) {}
                }, 400);
            } else if (hasSalary) {
                setTimeout(() => {
                    try {
                        const ctx2 = new (window.AudioContext || window.webkitAudioContext)();
                        [784,1047,1319,1047,1319,1568].forEach((freq,i) => {
                            const o=ctx2.createOscillator(),g=ctx2.createGain();
                            o.connect(g);g.connect(ctx2.destination);
                            o.type='sine';
                            o.frequency.setValueAtTime(freq,ctx2.currentTime+i*.09);
                            g.gain.setValueAtTime(.2,ctx2.currentTime+i*.09);
                            g.gain.exponentialRampToValueAtTime(.001,ctx2.currentTime+i*.09+.2);
                            o.start(ctx2.currentTime+i*.09);o.stop(ctx2.currentTime+i*.09+.2);
                        });
                    } catch(e2) {}
                }, 400);
            }
        } else if (type === 'close') {
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.type = 'sine';
            o.frequency.setValueAtTime(523, ctx.currentTime);
            g.gain.setValueAtTime(.12, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + .15);
            o.start(); o.stop(ctx.currentTime + .15);
        }
    } catch(e) {}
}
</script>
