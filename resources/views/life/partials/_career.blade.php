<div class="career-bg min-h-screen text-white font-sans antialiased" data-page-title="Career — PesaQuest">

    <style>
        /* Secondary/reference sections collapse to an accordion on every screen
           size — closed by default so the Career tab isn't a wall of cards.
           Report to Work + current job/payslip status stay outside this and
           are always fully visible up top. */
        .acc-mobile > summary { cursor: pointer; list-style: none; }
        .acc-mobile > summary::-webkit-details-marker { display: none; }
        .acc-mobile .acc-chevron { transition: transform .2s; flex-shrink: 0; }
        .acc-mobile[open] .acc-chevron { transform: rotate(180deg); }
    </style>

    {{-- CAREER BANNER --}}
    <section class="border-b border-white/5 py-7 sm:py-10"
             style="background: radial-gradient(ellipse at 60% 50%, rgba(245,158,11,0.12) 0%, transparent 60%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            @if($fieldMeta)
            <div class="flex items-center gap-3 mb-3 flex-wrap">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl"
                     style="background: {{ $fieldMeta['color'] }}20; border: 1px solid {{ $fieldMeta['color'] }}40;">
                    {{ $fieldMeta['icon'] }}
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-widest" style="color:{{ $fieldMeta['color'] }}">
                        {{ $fieldMeta['label'] }}
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white leading-tight">
                        {{ $progress->career_title ?? 'Unnamed Role' }}
                    </h1>
                </div>
            </div>
            @else
            <h1 class="text-xl sm:text-2xl font-black text-white mb-2">Career Overview</h1>
            @endif

            <div class="flex items-center gap-6 flex-wrap">
                <div>
                    <div class="text-xs text-gray-500">Monthly Salary (Gross)</div>
                    <div class="text-xl font-black text-amber-400">
                        Ksh {{ number_format($salary ?? 0) }}
                    </div>
                </div>
                @if(!empty($payslip))
                <div>
                    <div class="text-xs text-gray-500">Take-Home (Net)</div>
                    <div class="text-xl font-black text-emerald-400">Ksh {{ number_format($payslip['net']) }}</div>
                </div>
                @endif
                <div>
                    <div class="text-xs text-gray-500">Career Ladder</div>
                    <div class="text-sm font-bold text-white">
                        {{ $careerLadder[$currentRung]['icon'] }} {{ $careerLadder[$currentRung]['title'] }}
                        (Rung {{ $currentRung + 1 }}/{{ count($careerLadder) }})
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-5">

        {{-- Report to Work (check-in) — the most actionable card, always first --}}
        @if($activePesaJobs->isNotEmpty() || ($pendingPay ?? 0) > 0)
        @php
            $warnedJobs  = $activePesaJobs->filter(fn ($j) => !empty($j->removal_warned_at_tick));
            $missedJobs  = $activePesaJobs->filter(fn ($j) => (int) ($j->missed_paydays ?? 0) > 0 && empty($j->removal_warned_at_tick));
        @endphp
        <div class="rounded-xl p-4" id="pq-checkin-card"
             style="background:linear-gradient(135deg, rgba(16,185,129,{{ ($pendingPay ?? 0) > 0 ? '0.12' : '0.05' }}), rgba(5,150,105,0.03));border:1px solid rgba(16,185,129,{{ ($pendingPay ?? 0) > 0 ? '0.4' : '0.15' }});">
            @if($warnedJobs->isNotEmpty())
            <div class="mb-4 rounded-xl px-4 py-3 flex items-start gap-3"
                 style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.45);">
                <span class="text-2xl">🚨</span>
                <div>
                    <p class="text-xs font-black text-red-300">Final notice from {{ $warnedJobs->map(fn ($j) => $j->job?->employer_name)->filter()->join(', ') }}</p>
                    <p class="text-[11px] text-red-200/80 mt-0.5">You've skipped {{ $warnedJobs->max('missed_paydays') }} paydays in a row. <b>Report to Work now</b> or you'll be dismissed on the next payday. Your stacked pay stays safe either way — but the job won't.</p>
                </div>
            </div>
            @elseif($missedJobs->isNotEmpty())
            <div class="mb-4 rounded-xl px-4 py-3 flex items-start gap-3"
                 style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.35);">
                <span class="text-2xl">😟</span>
                <p class="text-[11px] text-amber-200/90 mt-0.5"><b class="text-amber-300">Your employer has noticed.</b> {{ $missedJobs->max('missed_paydays') }} missed payday{{ $missedJobs->max('missed_paydays') > 1 ? 's' : '' }} in a row — at 3 you get a final notice, and one more month of silence means dismissal. Checking in resets the count.</p>
            </div>
            @endif
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-2xl">{{ ($pendingPay ?? 0) > 0 ? '🧾' : '🕔' }}</span>
                <div class="flex-1 min-w-[12rem]">
                    @if(($pendingPay ?? 0) > 0)
                    <h3 class="text-sm font-black text-white">Payday is waiting: <span class="text-emerald-400">Ksh {{ number_format($pendingPay) }}</span></h3>
                    <p class="text-xs text-gray-400 mt-1">Report to work to collect your pay. Uncollected paychecks <b class="text-emerald-300">stack up safely</b> — but every payday you skip counts against your attendance. 3 misses in a row and your employer starts dismissal proceedings.</p>
                    @else
                    <h3 class="text-sm font-black text-white">Report to Work</h3>
                    <p class="text-xs text-gray-400 mt-1">Salaries are never deposited automatically. Check in here every payday (each 30 game days) to collect your wages. Your money never expires — but skip 3 paydays in a row and the job itself is at risk.</p>
                    @endif
                </div>
                <button type="button" onclick="pqWorkCheckin(this)"
                        class="px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
                        style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 14px rgba(16,185,129,.35);">
                    💼 Report to Work
                </button>
            </div>
            <p id="pq-checkin-msg" class="text-xs font-bold mt-3 hidden"></p>
        </div>
        <script>
        async function pqWorkCheckin(btn) {
            btn.disabled = true; btn.textContent = 'Checking in…';
            const msg = document.getElementById('pq-checkin-msg');
            try {
                const res  = await fetch('{{ route('life.work.checkin') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                });
                const data = await res.json();
                msg.classList.remove('hidden');
                msg.style.color = (data.paid || 0) > 0 ? '#34d399' : '#fcd34d';
                msg.textContent = data.message || 'Checked in.';
                if ((data.paid || 0) > 0) setTimeout(() => window.location.reload(), 1600);
                else { btn.disabled = false; btn.textContent = '💼 Report to Work'; }
            } catch (e) {
                msg.classList.remove('hidden'); msg.style.color = '#f87171'; msg.textContent = 'Network error — try again.';
                btn.disabled = false; btn.textContent = '💼 Report to Work';
            }
        }
        </script>
        @endif

        {{-- Current status: Payslip + Pesa City Jobs, side by side --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Payslip Card --}}
            <div>
                @if(!empty($payslip))
                <div class="glass rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-white/5"
                         style="background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(251,191,36,0.04));">
                        <h3 class="text-sm font-bold text-amber-300">📄 Monthly Payslip</h3>
                        <p class="text-[10px] text-gray-500 mt-0.5">Estimated — Kenya tax rates</p>
                    </div>
                    <div class="px-5 py-4 space-y-0">
                        @php
                            $rows = [
                                ['label' => 'Gross Salary',       'value' => $payslip['gross'],  'color' => 'text-white',     'sign' => ''],
                                ['label' => 'PAYE (Tax)',          'value' => $payslip['paye'],   'color' => 'text-red-400',   'sign' => '-'],
                                ['label' => 'NHIF',               'value' => $payslip['nhif'],   'color' => 'text-red-400',   'sign' => '-'],
                                ['label' => 'NSSF',               'value' => $payslip['nssf'],   'color' => 'text-red-400',   'sign' => '-'],
                                ['label' => 'Loan Deductions',    'value' => $payslip['loans'],  'color' => 'text-orange-400','sign' => '-'],
                            ];
                        @endphp
                        @foreach($rows as $row)
                        <div class="payslip-row flex items-center justify-between py-2.5">
                            <span class="text-xs text-gray-400">{{ $row['label'] }}</span>
                            <span class="text-xs font-bold {{ $row['color'] }}">
                                @if($row['sign'] && $row['value'] > 0)-@endif Ksh {{ number_format($row['value']) }}
                            </span>
                        </div>
                        @endforeach
                        {{-- NET --}}
                        <div class="flex items-center justify-between pt-3 mt-1">
                            <span class="text-sm font-black text-white">NET PAY</span>
                            <span class="text-xl font-black text-emerald-400">Ksh {{ number_format($payslip['net']) }}</span>
                        </div>
                    </div>

                    {{-- Tax rate visual --}}
                    @php
                        $deductions = $payslip['paye'] + $payslip['nhif'] + $payslip['nssf'] + $payslip['loans'];
                        $taxPct = $payslip['gross'] > 0 ? (int)(($deductions / $payslip['gross']) * 100) : 0;
                    @endphp
                    <div class="px-5 pb-4">
                        <div class="h-2 bg-white/5 rounded-full overflow-hidden flex mt-3">
                            <div class="h-full bg-red-500/70" style="width:{{ $taxPct }}%"></div>
                            <div class="h-full bg-emerald-500/70" style="width:{{ 100-$taxPct }}%"></div>
                        </div>
                        <div class="flex justify-between mt-1.5 text-[10px]">
                            <span class="text-red-400">{{ $taxPct }}% deductions</span>
                            <span class="text-emerald-400">{{ 100-$taxPct }}% take-home</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-3 leading-relaxed">
                            Paid every 30 game days. PAYE follows KRA bands, NHIF and NSSF are statutory.
                        </p>
                    </div>
                </div>
                @else
                <div class="glass rounded-xl p-4 text-center">
                    <div class="text-2xl mb-2">💼</div>
                    @if(($progress->career_field ?? null))
                    <p class="text-sm text-gray-400 mb-3">You're not earning a salary yet. Take a course at the Opportunity Hub, qualify, and get hired — that's how careers start in Pesa City.</p>
                    <a href="{{ route('opportunities.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-4 py-2 rounded-xl">
                        🎓 Go to Opportunity Hub →
                    </a>
                    @else
                    <p class="text-sm text-gray-400 mb-3">No career path set yet. Take the quiz to discover the path that fits you.</p>
                    <a href="{{ route('life.quiz') }}"
                       class="inline-flex items-center gap-1.5 text-xs bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-4 py-2 rounded-xl">
                        🎯 Take the Career Quiz →
                    </a>
                    @endif
                </div>
                @endif
            </div>

            {{-- Active Pesa City Jobs --}}
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between"
                     style="background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(99,102,241,0.04));">
                    <div>
                        <h3 class="text-sm font-bold text-indigo-300">🏙️ Pesa City Jobs</h3>
                        <p class="text-[10px] text-gray-500 mt-0.5">{{ $activePesaJobs->count() }} active — 1 full-time, or up to 2 part-time + 3 gigs</p>
                    </div>
                    <a href="{{ route('opportunities.index') }}"
                       class="text-xs font-bold px-3 py-1.5 rounded-xl text-indigo-300 hover:text-white transition-colors"
                       style="background:rgba(99,102,241,0.2);border:1px solid rgba(99,102,241,0.3);">
                        Find Jobs →
                    </a>
                </div>
                @if($activePesaJobs->isEmpty())
                <div class="px-5 py-6 text-center">
                    <p class="text-2xl mb-2">🔍</p>
                    <p class="text-xs text-gray-400 mb-3">No jobs yet in Pesa City. Visit the Opportunity Hub to apply.</p>
                    <a href="{{ route('opportunities.index') }}"
                       class="inline-block text-xs font-bold px-4 py-2 rounded-xl text-white"
                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">Browse Jobs</a>
                </div>
                @else

                {{-- Ready to claim — front and center, above the countdown list --}}
                @if($readyPesaJobs->isNotEmpty())
                <div class="px-5 py-3" style="background:rgba(16,185,129,0.08);border-bottom:1px solid rgba(16,185,129,0.2);">
                    <p class="text-[10px] font-black uppercase tracking-wider text-emerald-400 mb-2">🧾 Ready to claim now</p>
                    <div class="space-y-1.5">
                        @foreach($readyPesaJobs as $rj)
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg flex-shrink-0">{{ $rj->job->employer_logo ?? '🏢' }}</span>
                            <span class="text-xs font-bold text-white flex-1 min-w-0 truncate">{{ $rj->job ? $rj->displayTitle() : 'Job' }}</span>
                            <span class="text-xs font-black text-emerald-400 flex-shrink-0">Ksh {{ number_format($rj->pending_salary) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="divide-y divide-white/5">
                    @foreach($activePesaJobs as $pj)
                    @if($pj->job)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="text-2xl flex-shrink-0">{{ $pj->job->employer_logo ?? '🏢' }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-white truncate">{{ $pj->displayTitle() }}</p>
                            <p class="text-[10px] text-gray-500">{{ $pj->job->employer_name }} · KES {{ number_format($pj->effectiveSalary()) }}/mo</p>
                        </div>
                        @if(($pj->pending_salary ?? 0) > 0)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full flex-shrink-0 text-emerald-300" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35);">
                            🧾 Ksh {{ number_format($pj->pending_salary) }} ready
                        </span>
                        @elseif($pj->days_until_pay !== null)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full flex-shrink-0 text-sky-300" style="background:rgba(56,189,248,0.12);border:1px solid rgba(56,189,248,0.3);" title="{{ $pj->employment_type === 'freelance' ? 'Gig delivers, then pays out' : 'Next payday' }}">
                            ⏳ Ready in {{ $pj->days_until_pay }}d
                        </span>
                        @endif
                        @if(!empty($pj->removal_warned_at_tick))
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full flex-shrink-0 text-red-300" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);">
                            🚨 Final notice
                        </span>
                        @elseif((int) ($pj->missed_paydays ?? 0) > 0)
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full flex-shrink-0 text-amber-300" style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);" title="Missed paydays in a row — resets when you Report to Work">
                            {{ str_repeat('●', min(3, (int) $pj->missed_paydays)) }}{{ str_repeat('○', max(0, 3 - (int) $pj->missed_paydays)) }} {{ $pj->missed_paydays }} missed
                        </span>
                        @endif
                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full flex-shrink-0
                            {{ ['full_time' => 'text-emerald-400', 'part_time' => 'text-amber-400', 'freelance' => 'text-violet-300'][$pj->employment_type] ?? 'text-gray-400' }}"
                              style="background:{{ ['full_time' => 'rgba(16,185,129,0.12)', 'part_time' => 'rgba(245,158,11,0.12)', 'freelance' => 'rgba(139,92,246,0.15)'][$pj->employment_type] ?? 'rgba(255,255,255,0.06)' }}">
                            {{ ['full_time' => 'Full-time', 'part_time' => 'Part-time', 'freelance' => '⚡ Gig'][$pj->employment_type] ?? ucfirst($pj->employment_type) }}
                        </span>
                    </div>
                    @endif
                    @endforeach
                </div>
                @php $pesaTotalSalary = $activePesaJobs->sum(fn($pj) => $pj->job?->salary_kes_month ?? 0); @endphp
                @if($pesaTotalSalary > 0)
                <div class="px-5 py-3 border-t border-white/5">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Combined monthly salary</span>
                        <span class="font-black text-emerald-400">KES {{ number_format($pesaTotalSalary) }}</span>
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>

        {{-- Reference / secondary info — closed by default, tap to expand --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- LEFT: Career Ladder + Career Fields --}}
            <div class="space-y-4">

                {{-- Career Ladder --}}
                <details class="glass rounded-2xl overflow-hidden acc-mobile">
                    <summary class="p-4 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-white">🪜 Career Ladder</h3>
                            @if($nextRung)
                            <span class="text-[11px] text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-full">
                                +Ksh {{ number_format($salaryToNextRung) }} to next rung
                            </span>
                            @endif
                        </div>
                        <svg class="acc-chevron w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="acc-body px-5 pb-5 -mt-1">

                    <div class="space-y-2">
                        @foreach(array_reverse($careerLadder, true) as $i => $rung)
                        @php
                            $isCurrent = $i === $currentRung;
                            $isDone    = $salary > ($rung['max'] ?? PHP_INT_MAX);
                            $cls = $isCurrent ? 'current' : ($isDone ? 'done' : 'future');
                        @endphp
                        <div class="ladder-rung {{ $cls }} border rounded-xl px-4 py-3 flex items-center gap-3">
                            <span class="text-2xl">{{ $rung['icon'] }}</span>
                            <div class="flex-1">
                                <div class="text-sm font-bold {{ $isCurrent ? 'text-amber-300' : ($isDone ? 'text-emerald-400' : 'text-gray-500') }}">
                                    {{ $rung['title'] }}
                                    @if($isCurrent) <span class="text-[10px] font-black text-amber-400 bg-amber-400/15 px-1.5 py-0.5 rounded-full ml-1">YOU ARE HERE</span> @endif
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5">
                                    Ksh {{ number_format($rung['min']) }}{{ $rung['max'] ? '–' . number_format($rung['max']) : '+' }} /mo
                                </div>
                            </div>
                            @if($isDone)
                            <span class="text-emerald-400 text-lg">✓</span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    @if($nextRung)
                    <div class="mt-4 p-4 rounded-xl" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);">
                        <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-400 mb-1">How to advance</div>
                        <p class="text-xs text-gray-300 leading-relaxed">
                            To reach <strong>{{ $nextRung['title'] }}</strong>, your career income needs to reach
                            <strong class="text-indigo-300">Ksh {{ number_format($nextRung['min']) }}/mo</strong>.
                            Grow it by completing more courses at the Opportunity Hub and qualifying for
                            better-paying jobs — you can hold one full-time job plus part-time hustles.
                        </p>
                    </div>
                    @else
                    <div class="mt-4 p-4 rounded-xl text-center" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                        <div class="text-xl mb-1">🌟</div>
                        <p class="text-xs text-emerald-400 font-bold">You're at the top of the career ladder.</p>
                    </div>
                    @endif
                    </div>
                </details>

                {{-- Career Fields Reference --}}
                <details class="glass rounded-2xl overflow-hidden acc-mobile">
                    <summary class="p-4 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-white">🗂 Career Fields</h3>
                        <svg class="acc-chevron w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="acc-body px-5 pb-5 -mt-1">
                        <div class="space-y-2">
                            @foreach($allFields as $key => $field)
                            <div class="field-card rounded-xl px-3 py-2.5 flex items-center gap-2.5"
                                 style="{{ $progress->career_field === $key ? "background:{$field['color']}18;border-color:{$field['color']}40;" : '' }}">
                                <span class="text-xl">{{ $field['icon'] }}</span>
                                <span class="text-xs font-bold {{ $progress->career_field === $key ? 'text-white' : 'text-gray-400' }}">
                                    {{ $field['label'] }}
                                </span>
                                @if($progress->career_field === $key)
                                <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                      style="background:{{ $field['color'] }}30;color:{{ $field['color'] }}">Current</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-gray-600 mt-3 leading-relaxed">
                            Career field changes happen through scenario choices — look for job offer events in the game.
                        </p>
                    </div>
                </details>

            </div>

            {{-- RIGHT: Payslip tips + history + courses --}}
            <div class="space-y-4">

                {{-- Payslip tips --}}
                <details class="glass rounded-2xl overflow-hidden acc-mobile">
                    <summary class="p-4 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-white">💡 Maximise Your Take-Home</h3>
                        <svg class="acc-chevron w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="acc-body px-5 pb-5 -mt-1">
                        <div class="space-y-3">
                            <div class="flex items-start gap-2.5 text-xs text-gray-400">
                                <span class="text-base shrink-0 mt-0.5">🏦</span>
                                <span>SACCO membership lets you borrow at 1–2% interest/month vs 8–15% from banks and Fuliza.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs text-gray-400">
                                <span class="text-base shrink-0 mt-0.5">📊</span>
                                <span>Investing 10% of your salary from day one (before lifestyle inflation) builds wealth exponentially.</span>
                            </div>
                            <div class="flex items-start gap-2.5 text-xs text-gray-400">
                                <span class="text-base shrink-0 mt-0.5">🛡️</span>
                                <span>NHIF and NSSF deductions protect you — NHIF covers hospitalisation, NSSF builds your retirement savings.</span>
                            </div>
                            @if($salary < 50000)
                            <div class="flex items-start gap-2.5 text-xs text-gray-400">
                                <span class="text-base shrink-0 mt-0.5">🏍</span>
                                <span>At your income level, a single income-generating asset (bodaboda, small stall) can increase your monthly net by 30–60%.</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </details>

                {{-- Salary History --}}
                @if($salaryHistory->isNotEmpty())
                <details class="glass rounded-2xl overflow-hidden acc-mobile">
                    <summary class="p-4 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-white">📅 Recent Paydays</h3>
                        <svg class="acc-chevron w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="acc-body px-5 pb-5 -mt-1">
                        <div class="space-y-2">
                            @foreach($salaryHistory as $n)
                            <div class="flex items-center gap-3 border-l-2 border-emerald-500/30 bg-emerald-500/4 px-3 py-2 rounded-r-xl">
                                <span class="text-base">{{ $n->icon ?? '💼' }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-white truncate">{{ $n->title }}</div>
                                    @if($n->body)<div class="text-[10px] text-gray-400 truncate">{{ $n->body }}</div>@endif
                                </div>
                                <span class="text-[10px] text-gray-600 shrink-0">{{ $n->created_at->diffForHumans() }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </details>
                @endif

                {{-- Completed Courses --}}
                <details class="glass rounded-2xl overflow-hidden acc-mobile">
                    <summary class="p-4 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold text-white">📚 Completed Courses</h3>
                        <svg class="acc-chevron w-4 h-4 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="acc-body px-5 pb-5 -mt-1">
                        <a href="{{ route('opportunities.index') }}"
                           class="inline-block text-xs font-bold px-3 py-1.5 rounded-xl text-emerald-300 hover:text-white transition-colors mb-3"
                           style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.25);">
                            More Courses →
                        </a>
                        @if($completedCourses->isEmpty())
                        <div class="py-6 text-center">
                            <p class="text-2xl mb-2">🎓</p>
                            <p class="text-xs text-gray-400 mb-3">No courses completed yet. Learning boosts job eligibility and XP.</p>
                            <a href="{{ route('opportunities.index') }}"
                               class="inline-block text-xs font-bold px-4 py-2 rounded-xl text-white"
                               style="background:linear-gradient(135deg,#10b981,#059669);">Enroll Now</a>
                        </div>
                        @else
                        <div class="divide-y divide-white/5">
                            @foreach($completedCourses as $pc)
                            @if($pc->course)
                            <div class="py-3 flex items-center gap-3">
                                <span class="text-2xl flex-shrink-0">{{ $pc->course->icon ?? '📘' }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-white truncate">{{ $pc->course->title }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $pc->course->career_track ?? 'General' }} · +{{ $pc->course->xp_reward ?? 50 }} XP</p>
                                </div>
                                <span class="text-emerald-400 text-sm flex-shrink-0">✓</span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                </details>

            </div>
        </div>
    </div>
</div>
