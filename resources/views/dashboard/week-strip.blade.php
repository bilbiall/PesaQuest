{{-- ── This Week cashflow strip ─────────────────────────────────────────────
     The next 7 game days of money events (paydays, bills, loans, chama,
     interest, crises) derived by GameCalendarService. Teaches the core
     habit: look ahead at your cashflow, don't get surprised by it.
     Expects $weekCal = ['days' => [...], 'in' => int, 'out' => int, 'net' => int] --}}
@if(!empty($weekCal))
<div class="card rounded-2xl p-4">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <div class="text-[10px] font-black uppercase tracking-wider text-gray-500">📆 This Week's Money</div>
        <div class="flex items-center gap-2 text-[10px] font-black">
            <span class="px-2 py-0.5 rounded-full text-emerald-300" style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);">↓ In Ksh {{ number_format($weekCal['in']) }}</span>
            <span class="px-2 py-0.5 rounded-full text-red-300" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);">↑ Out Ksh {{ number_format($weekCal['out']) }}</span>
            <span class="px-2 py-0.5 rounded-full {{ $weekCal['net'] >= 0 ? 'text-emerald-300' : 'text-amber-300' }}" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);">
                Net {{ $weekCal['net'] >= 0 ? '+' : '−' }}Ksh {{ number_format(abs($weekCal['net'])) }}
            </span>
        </div>
    </div>
    <div class="grid grid-cols-7 gap-1.5">
        @foreach($weekCal['days'] as $d)
        @php
            $dayTip = collect($d['events'])->map(function ($ev) {
                $amt = $ev['amount'] ?? null;
                $amtTxt = $amt === null ? '' : ($amt < 0 ? ' −' : ' +') . number_format(abs($amt));
                return ($ev['icon'] ?? '') . ' ' . ($ev['label'] ?? '') . $amtTxt;
            })->join("\n");
            $dayNet = collect($d['events'])->sum(fn ($ev) => (int) ($ev['amount'] ?? 0));
        @endphp
        <div class="rounded-xl px-1 py-2 text-center {{ $d['is_today'] ? 'ring-1 ring-emerald-500/50' : '' }}"
             style="background:{{ $d['is_today'] ? 'rgba(21,199,126,0.08)' : 'rgba(255,255,255,0.03)' }};border:1px solid rgba(255,255,255,0.06);"
             @if($dayTip) title="{{ $dayTip }}" @endif>
            <div class="text-[8px] font-black uppercase tracking-wide text-gray-500">{{ substr($d['weekday'], 0, 3) }}</div>
            <div class="text-[11px] font-black text-white leading-tight">{{ $d['is_today'] ? '★' : number_format($d['day']) }}</div>
            <div class="flex items-center justify-center gap-0.5 mt-1 min-h-[10px]">
                @foreach(array_slice($d['events'], 0, 3) as $ev)
                @php
                    $dot = ($ev['kind'] ?? '') === 'crisis' ? '#fbbf24'
                         : (($ev['kind'] ?? '') === 'chama' ? '#a78bfa'
                         : (($ev['kind'] ?? '') === 'birthday' ? '#f472b6'
                         : ((($ev['amount'] ?? 0) < 0) ? '#f87171' : '#34d399')));
                @endphp
                <span style="width:5px;height:5px;border-radius:50%;background:{{ $dot }};display:block;"></span>
                @endforeach
            </div>
            @if($dayNet !== 0)
            <div class="text-[8px] font-black mt-0.5 {{ $dayNet > 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ $dayNet > 0 ? '+' : '−' }}{{ number_format(abs($dayNet)) }}</div>
            @endif
        </div>
        @endforeach
    </div>
    <p class="text-[9px] text-gray-600 mt-2">Hover a day for details · full picture on the 📅 calendar (bottom-left)</p>
</div>
@endif
