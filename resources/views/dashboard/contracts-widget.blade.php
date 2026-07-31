{{-- ── City Contracts widget ────────────────────────────────────────────────
     NPC-issued personal contracts, generated from the player's own state by
     ContractService. Expects $contracts (collection) + $currentTick. --}}
@if(!empty($contracts) && $contracts->isNotEmpty())
<div class="card rounded-2xl p-4">
    <div class="flex items-center justify-between mb-3">
        <div class="text-[10px] font-black uppercase tracking-wider text-gray-500">📜 City Contracts</div>
        <div class="text-[9px] text-gray-600 font-semibold">Word on the street: Pesa City needs you</div>
    </div>

    <div class="grid gap-3 {{ $contracts->count() > 1 ? 'md:grid-cols-2' : '' }}">
        @foreach($contracts as $contract)
        @php
            $npc      = $contract->npc();
            $daysLeft = $contract->daysLeft((int) ($currentTick ?? 0));
            $doneN    = $contract->completedObjectivesCount();
            $needN    = $contract->neededCount();
            $totalN   = $contract->objectives->count();
        @endphp
        <div class="rounded-xl p-3.5" style="background:rgba(255,255,255,0.03);border:1px solid {{ $daysLeft <= 1 ? 'rgba(245,158,11,0.4)' : 'rgba(139,92,246,0.22)' }};">
            {{-- NPC header --}}
            <div class="flex items-start gap-2.5 mb-2">
                <span class="text-2xl flex-shrink-0" title="{{ $npc['role'] ?? '' }}">{{ $npc['emoji'] ?? '🏙️' }}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-black text-violet-300">{{ $npc['name'] ?? 'Pesa City' }}</span>
                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded-full {{ $daysLeft <= 1 ? 'text-amber-300' : 'text-gray-400' }}"
                              style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                            ⏳ {{ $daysLeft }} game day{{ $daysLeft === 1 ? '' : 's' }} left
                        </span>
                        <span class="ml-auto text-[9px] font-black text-indigo-300">+{{ number_format($contract->reward_xp) }} XP{{ $contract->reward_kes > 0 ? ' · +Ksh ' . number_format($contract->reward_kes) : '' }}</span>
                    </div>
                    <div class="text-xs font-black text-white mt-0.5">{{ $contract->icon }} {{ $contract->title }}</div>
                    <p class="text-[10px] text-gray-500 mt-1 leading-snug">{{ $contract->intro }}</p>
                </div>
            </div>

            {{-- Objectives --}}
            <div class="space-y-1.5 mt-2.5">
                @foreach($contract->objectives as $obj)
                <div class="flex items-center gap-2">
                    <span class="text-[11px] flex-shrink-0">{{ $obj->is_complete ? '✅' : '⬜' }}</span>
                    <span class="text-[10.5px] flex-1 min-w-0 {{ $obj->is_complete ? 'text-gray-500 line-through' : 'text-gray-300' }}">{{ $obj->label }}</span>
                    @if(!$obj->is_complete && (int) $obj->goal > 1)
                    <div class="w-14 h-1.5 rounded-full overflow-hidden flex-shrink-0" style="background:rgba(255,255,255,0.08);">
                        <div class="h-full rounded-full" style="width:{{ $obj->progressPercent() }}%;background:linear-gradient(90deg,#6366f1,#a78bfa);"></div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Footer: completion rule --}}
            <div class="mt-2.5 pt-2 flex items-center justify-between" style="border-top:1px solid rgba(255,255,255,0.06);">
                <span class="text-[9px] font-black {{ $doneN >= $needN ? 'text-emerald-400' : 'text-gray-500' }}">
                    {{ $contract->completion_mode === 'all' ? "Complete all {$totalN}" : "Complete any {$needN} of {$totalN}" }}
                    · {{ $doneN }}/{{ $needN }} done
                </span>
                <div class="flex gap-1">
                    @for($i = 0; $i < $needN; $i++)
                    <span style="width:6px;height:6px;border-radius:50%;display:block;background:{{ $i < $doneN ? '#34d399' : 'rgba(255,255,255,0.12)' }};"></span>
                    @endfor
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <p class="text-[9px] text-gray-600 mt-2.5">Contracts refresh themselves — finish these and new ones find you. Progress counts automatically, wherever you play.</p>
</div>
@endif
