<div class="life-bg min-h-screen text-white font-sans antialiased" x-data="lifeBoard()" data-page-title="Life HQ — PesaQuest">

    {{-- ── TOAST ── --}}
    <div x-show="toast" x-cloak
         class="toast-wrap fixed top-4 right-4 z-[9999] px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-2 font-bold text-sm"
         :class="toastOk ? 'bg-emerald-500/95 text-white' : 'bg-red-500/95 text-white'">
        <span x-text="toastMsg"></span>
    </div>

    {{-- ── CHARACTER BANNER ── --}}
    <section class="char-banner py-5 sm:py-7"
        @if($chapterBg ?? null)
        style="background-image: linear-gradient(100deg, rgba(7,6,15,.96) 0%, rgba(7,6,15,.85) 38%, rgba(7,6,15,.4) 72%, rgba(7,6,15,.12) 100%), url('{{ $chapterBg }}'); background-size: cover; background-position: center right;"
        @endif>
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-start gap-3 mb-4">
                {{-- Avatar --}}
                @if(auth()->user()->profile_photo)
                <img src="{{ auth()->user()->profile_photo }}" alt="{{ auth()->user()->name }}"
                     class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover shrink-0 ring-2 ring-indigo-400/60 ring-offset-2 ring-offset-black">
                @else
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full flex items-center justify-center text-lg sm:text-xl font-black text-white shrink-0 ring-2 ring-indigo-400/60 ring-offset-2 ring-offset-black"
                     style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                    {{ strtoupper(substr(auth()->user()->name,0,1)) }}{{ strtoupper(substr(explode(' ',auth()->user()->name)[1]??'',0,1)) }}
                </div>
                @endif

                <div class="min-w-0">
                    {{-- Location + chapter breadcrumb --}}
                    <div class="flex items-center gap-2 flex-wrap mb-1 text-[11px]">
                        <span class="text-gray-400">📍 {{ $location }}</span>
                        <span class="text-gray-700">·</span>
                        <span class="text-indigo-300">{{ $progress->chapterIcon() }} {{ $progress->chapterName() }}</span>
                    </div>

                    <h1 class="text-lg sm:text-xl font-black text-white tracking-tight truncate">
                        {{ auth()->user()->name }}
                    </h1>

                    {{-- Tags row --}}
                    <div class="flex items-center gap-1.5 flex-wrap mt-1.5">
                        <span class="inline-flex items-center gap-1 text-[11px] bg-indigo-500/10 border border-indigo-500/25 text-indigo-300 px-2 py-0.5 rounded-full font-bold">
                            ⭐ Lv {{ $progress->level ?? 1 }}
                        </span>
                        @if($progress->career_title)
                        <span class="inline-flex items-center gap-1 text-[11px] bg-amber-500/10 border border-amber-500/20 text-amber-300 px-2 py-0.5 rounded-full font-medium">
                            💼 {{ $progress->career_title }}
                        </span>
                        @endif
                        @if($progress->financial_personality)
                        <span class="inline-flex items-center gap-1 text-[11px] bg-purple-500/10 border border-purple-500/20 text-purple-300 px-2 py-0.5 rounded-full font-medium">
                            🧠 {{ $progress->financial_personality }}
                        </span>
                        @endif
                        @if($playerAssets->count() > 0)
                        <span class="inline-flex items-center gap-1 text-[11px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full font-medium">
                            🏢 {{ $playerAssets->count() }} asset{{ $playerAssets->count() !== 1 ? 's' : '' }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Key financial stats --}}
            <div class="grid grid-cols-3 gap-3 sm:max-w-md">
                <div>
                    <div class="text-[11px] text-gray-500 mb-0.5">Balance</div>
                    <div class="text-sm sm:text-base font-black text-emerald-400 whitespace-nowrap" data-balance>Ksh {{ number_format($progress->balance) }}</div>
                    @if(($balanceDeltaPct ?? null) !== null)
                    <div class="text-[10px] font-bold whitespace-nowrap {{ $balanceDeltaPct >= 0 ? 'text-emerald-400' : 'text-red-400' }}" title="vs last game month">
                        {{ $balanceDeltaPct >= 0 ? '↑' : '↓' }} {{ number_format(abs($balanceDeltaPct), 1) }}%
                    </div>
                    @endif
                </div>
                <div>
                    <div class="text-[11px] text-gray-500 mb-0.5">Net Worth</div>
                    <div class="text-sm sm:text-base font-black text-indigo-400 whitespace-nowrap">Ksh {{ number_format($netWorth) }}</div>
                    @if(($netWorthDeltaPct ?? null) !== null)
                    <div class="text-[10px] font-bold whitespace-nowrap {{ $netWorthDeltaPct >= 0 ? 'text-emerald-400' : 'text-red-400' }}" title="vs last game month">
                        {{ $netWorthDeltaPct >= 0 ? '↑' : '↓' }} {{ number_format(abs($netWorthDeltaPct), 1) }}%
                    </div>
                    @endif
                </div>
                <div>
                    <div class="text-[11px] text-gray-500 mb-0.5">Monthly Net</div>
                    <div class="text-sm sm:text-base font-black whitespace-nowrap {{ $netMonthly >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $netMonthly >= 0 ? '+' : '' }}Ksh {{ number_format($netMonthly) }}
                    </div>
                    <div class="text-[10px] font-bold text-gray-500 whitespace-nowrap">{{ $savingsRate }}% savings rate</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── MAIN GRID ── --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- ═══ LEFT PANEL ═══ --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Life Chapter Progress --}}
                @php
                    $chapterKey  = $progress->chapterKey();
                    $kwLeft      = $progress->netWorthToNextChapter();
                    $nextKw      = $progress->nextChapterNetWorth();
                    $bands       = \App\Models\UserProgress::chapterBands();
                    [$bMin,$bMax] = $bands[$chapterKey] ?? [0,50000];
                    $chapterProgress = $nextKw ? min(100, (int)((max(0, $netWorth - $bMin)) / max(1, $bMax - $bMin) * 100)) : 100;
                    $chapterStages = array_map(fn ($c) => [
                        'key'  => $c['key'],
                        'icon' => $c['icon'],
                        'name' => preg_replace('/^The\s+/i', '', $c['name']),
                    ], \App\Models\UserProgress::chapters());
                    $stageIdx = array_search($chapterKey, array_column($chapterStages, 'key'));
                    $stageIdx = $stageIdx === false ? 0 : $stageIdx;
                @endphp
                <div class="glass rounded-xl p-4 border border-indigo-500/15">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-white">📖 Life Chapter</h3>
                        <a href="{{ route('life.timeline') }}" class="text-[10px] text-indigo-400 hover:text-indigo-300 transition-colors">Timeline →</a>
                    </div>

                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-2xl leading-none">{{ $progress->chapterIcon() }}</span>
                        <div>
                            <div class="text-base font-black text-white leading-tight">{{ $progress->chapterName() }}</div>
                            <div class="text-[11px] text-gray-500">{{ $progress->chapterTagline() }}</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="flex items-center justify-between text-[10px] text-gray-500 mb-1">
                            <span class="uppercase tracking-widest font-bold">Net Worth Progress</span>
                            @if($kwLeft !== null)
                            <span class="text-indigo-300 font-bold">Ksh {{ number_format($kwLeft) }} to next</span>
                            @else
                            <span class="text-amber-300 font-bold">🌟 Final chapter</span>
                            @endif
                        </div>
                        <div class="h-2 bg-white/5 rounded-full overflow-hidden">
                            <div class="chapter-bar h-full rounded-full transition-all duration-700" style="width: {{ $chapterProgress }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-600 mt-1">
                            <span>Ksh {{ number_format($netWorth) }}</span>
                            @if($nextKw)
                            <span>Next: Ksh {{ number_format($nextKw) }}</span>
                            @else
                            <span>You've reached the top</span>
                            @endif
                        </div>
                    </div>

                    {{-- Chapter mini-map --}}
                    <div class="flex items-center gap-1 mt-4">
                        @foreach($chapterStages as $idx => $stage)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                                {{ $idx < $stageIdx ? 'bg-emerald-500/15 border border-emerald-500/30' : ($idx === $stageIdx ? 'bg-indigo-500/20 border border-indigo-400/50 ring-2 ring-indigo-500/30' : 'bg-white/5 border border-white/10 opacity-30') }}">
                                @if($idx < $stageIdx)
                                <span class="text-emerald-400 text-xs font-black">✓</span>
                                @else
                                {{ $stage['icon'] }}
                                @endif
                            </div>
                            <span class="text-[8px] {{ $idx === $stageIdx ? 'text-indigo-300 font-bold' : 'text-gray-600' }}">{{ $stage['name'] }}</span>
                        </div>
                        @if($idx < count($chapterStages) - 1)
                        <div class="w-2 h-px shrink-0 {{ $idx < $stageIdx ? 'bg-emerald-500/40' : 'bg-white/10' }}"></div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- M-Pesa Wallet --}}
                <div class="wallet-card {{ $netMonthly < 0 ? 'deficit' : '' }} rounded-xl p-4">
                    <div class="text-[10px] font-bold uppercase tracking-widest mb-1
                                {{ $netMonthly >= 0 ? 'text-emerald-400/70' : 'text-red-400/70' }}">
                        💳 Game Balance
                    </div>
                    <div class="text-2xl font-black {{ $netMonthly >= 0 ? 'text-emerald-400' : 'text-red-400' }} mb-0.5 leading-none" data-balance>
                        Ksh {{ number_format($progress->balance) }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        Net Worth: <span class="text-indigo-300 font-bold">Ksh {{ number_format($netWorth) }}</span>
                    </div>

                    @if($netMonthly > 0)
                    <div class="mt-3 flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1 text-xs bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 px-3 py-1 rounded-full font-bold">
                            ↑ +Ksh {{ number_format($netMonthly) }}/mo
                        </span>
                        <span class="text-xs text-gray-500">{{ $savingsRate }}% savings rate</span>
                    </div>
                    @elseif($netMonthly < 0)
                    <div class="mt-3 inline-flex items-center gap-1 text-xs bg-red-500/10 border border-red-500/25 text-red-400 px-3 py-1 rounded-full font-bold">
                        ↓ Ksh {{ number_format(abs($netMonthly)) }}/mo deficit
                    </div>
                    @elseif($totalIncome === 0)
                    <div class="mt-3 text-xs text-gray-600 italic">Play scenarios to unlock career income</div>
                    @endif
                </div>

                {{-- Monthly Statement --}}
                <div class="glass rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-white">📊 Monthly Statement</h3>
                        <span class="text-[10px] text-gray-600 bg-white/5 px-2 py-0.5 rounded-full">Game Month</span>
                    </div>

                    {{-- Income section --}}
                    <div class="text-[10px] font-bold uppercase tracking-widest text-emerald-500/60 mb-2">Income</div>
                    <div class="space-y-2 mb-3">
                        @if($salaryPerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                <span class="text-base">💼</span>
                                <span>{{ $progress->career_title ?? 'Salary' }}</span>
                            </span>
                            <span class="text-xs font-bold text-emerald-400">+{{ number_format($salaryPerMonth) }}</span>
                        </div>
                        @endif
                        @if($assetIncomePerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                <span class="text-base">🏢</span>
                                <span>Passive ({{ $playerAssets->where('asset.monthly_income', '>', 0)->count() }} assets)</span>
                            </span>
                            <span class="text-xs font-bold text-emerald-400">+{{ number_format($assetIncomePerMonth) }}</span>
                        </div>
                        @endif
                        @if($totalIncome === 0)
                        <div class="text-xs text-gray-600 italic py-1">No income sources yet</div>
                        @else
                        <div class="flex items-center justify-between pt-1 border-t border-emerald-500/10">
                            <span class="text-[10px] text-gray-500 font-bold">TOTAL IN</span>
                            <span class="text-sm font-black text-emerald-400">Ksh {{ number_format($totalIncome) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-white/5 my-3"></div>

                    {{-- Expenses section --}}
                    <div class="text-[10px] font-bold uppercase tracking-widest text-red-500/60 mb-2">Expenses</div>
                    <div class="space-y-2 mb-3">
                        @if($billsBurnPerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                <span class="text-base">🏠</span>
                                <span>Bills & Living</span>
                            </span>
                            <span class="text-xs font-bold text-red-400">-{{ number_format($billsBurnPerMonth) }}</span>
                        </div>
                        @endif
                        @if($assetCostsPerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                <span class="text-base">🔧</span>
                                <span>Asset Running Costs</span>
                            </span>
                            <span class="text-xs font-bold text-red-400">-{{ number_format($assetCostsPerMonth) }}</span>
                        </div>
                        @endif
                        @if(($loanPaymentsPerMonth ?? 0) > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5">
                                <span class="text-base">🏦</span>
                                <span>Loan Installments</span>
                            </span>
                            <span class="text-xs font-bold text-red-400">-{{ number_format($loanPaymentsPerMonth) }}</span>
                        </div>
                        @endif
                        @if($totalExpenses === 0)
                        <div class="text-xs text-gray-600 italic py-1">No expenses yet</div>
                        @else
                        <div class="flex items-center justify-between pt-1 border-t border-red-500/10">
                            <span class="text-[10px] text-gray-500 font-bold">TOTAL OUT</span>
                            <span class="text-sm font-black text-red-400">Ksh {{ number_format($totalExpenses) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Net result --}}
                    @if($totalIncome > 0 || $totalExpenses > 0)
                    <div class="border-t border-white/10 pt-3 mt-2">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-gray-400 font-bold">Monthly Net</span>
                            <span class="text-base font-black {{ $netMonthly >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $netMonthly >= 0 ? '+' : '' }}Ksh {{ number_format($netMonthly) }}
                            </span>
                        </div>
                        @if($totalIncome > 0)
                        @php $expPct = min(100, (int)(($totalExpenses / $totalIncome) * 100)); @endphp
                        <div class="h-2 bg-white/5 rounded-full overflow-hidden flex">
                            <div class="h-full bg-red-500/70 rounded-l-full" style="width:{{ $expPct }}%"></div>
                            <div class="h-full bg-emerald-500/70 rounded-r-full" style="width:{{ 100-$expPct }}%"></div>
                        </div>
                        <div class="flex justify-between mt-1.5 text-[10px]">
                            <span class="text-red-400">{{ $expPct }}% out</span>
                            <span class="{{ 100-$expPct >= 20 ? 'text-emerald-400' : 'text-amber-400' }}">{{ 100-$expPct }}% saved</span>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Payday banner — salaries only land when the player reports to work --}}
                @if(($pendingPay ?? 0) > 0)
                <div class="rounded-2xl p-4 flex flex-wrap items-center gap-3"
                     style="background:linear-gradient(135deg, rgba(16,185,129,0.12), rgba(5,150,105,0.04));border:1px solid rgba(16,185,129,0.4);">
                    <span class="text-2xl">🧾</span>
                    <div class="flex-1 min-w-[10rem]">
                        <p class="text-xs font-black text-white">Payday waiting: <span class="text-emerald-400">Ksh {{ number_format($pendingPay) }}</span></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Report to work to collect — uncollected pay is lost on the next payday.</p>
                    </div>
                    <button type="button" onclick="pqBoardCheckin(this)"
                            class="px-4 py-2 rounded-xl text-xs font-black text-white"
                            style="background:linear-gradient(135deg,#10b981,#059669);">💼 Report to Work</button>
                </div>
                <script>
                async function pqBoardCheckin(btn) {
                    btn.disabled = true; btn.textContent = 'Checking in…';
                    try {
                        const res  = await fetch('{{ route('life.work.checkin') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        });
                        const data = await res.json();
                        btn.textContent = (data.paid || 0) > 0 ? '✅ Collected!' : 'Nothing due';
                        setTimeout(() => window.location.reload(), 1200);
                    } catch (e) { btn.disabled = false; btn.textContent = '💼 Report to Work'; }
                }
                </script>
                @endif

                {{-- Bills Board --}}
                <div class="glass rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-white">🗓 Bills Board</h3>
                        @if($overdueBills->count() > 0)
                        <span id="bills-overdue-badge" class="text-[10px] font-bold text-red-400 bg-red-500/10 border border-red-500/20 px-2 py-0.5 rounded-full animate-pulse">
                            <span id="bills-overdue-count">{{ $overdueBills->count() }}</span> OVERDUE
                        </span>
                        @elseif($allBills->isEmpty())
                        <span class="text-[10px] text-gray-600">No bills yet</span>
                        @else
                        <span class="text-[10px] text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full">All OK</span>
                        @endif
                    </div>
                    <p class="text-[10px] text-gray-500 -mt-2 mb-3 leading-snug">💳 Bills are <b class="text-gray-300">not</b> paid for you — hit <b class="text-gray-300">Pay Now</b> before the due date. Unpaid bills go overdue and your credit score drops.</p>

                    @php
                        // Convert game days to a real-time hint using the admin game clock
                        $fmtReal = fn (int $ticks) => $clock->approxRealLabel($ticks);
                    @endphp

                    @if($allBills->isEmpty() && ($activeLoans ?? collect())->isEmpty())
                    <div class="text-center py-5 text-xs text-gray-600">
                        Bills are assigned automatically as your game progresses.<br>
                        <a href="{{ route('game.play') }}" class="text-indigo-400 hover:underline mt-1 inline-block">Play more to unlock ↗</a>
                    </div>
                    @else
                    <div class="space-y-2">
                        @foreach($allBills as $pb)
                        @php
                            $ticks = max(0, $pb->next_due_tick - $currentTick);
                            if ($pb->status === 'overdue') {
                                $urgClass = 'bill-overdue';
                                $urgLabel = '⚠️ OVERDUE';
                                $urgColor = 'text-red-400';
                            } elseif ($ticks <= 5) {
                                $urgClass = 'bill-urgent';
                                $urgLabel = "Due in {$ticks} game day" . ($ticks === 1 ? '' : 's') . " ({$fmtReal($ticks)})";
                                $urgColor = 'text-amber-400';
                            } else {
                                $urgClass = 'bill-ok';
                                $urgLabel = "Due in {$ticks} game days ({$fmtReal($ticks)})";
                                $urgColor = 'text-emerald-400';
                            }
                        @endphp
                        <div x-data="{ open: false }" id="bill-row-{{ $pb->id }}" data-overdue="{{ $pb->status === 'overdue' ? '1' : '0' }}"
                             x-show="showAllBills || {{ $loop->index }} < 8" x-cloak
                             class="{{ $urgClass }} border rounded-xl overflow-hidden">
                            <div class="px-3 py-2.5 flex items-center gap-2.5 cursor-pointer select-none" @click="open = !open">
                                <span class="text-lg shrink-0">{{ $pb->bill->icon ?? '💸' }}</span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-white truncate flex items-center gap-1.5">
                                        {{ $pb->bill->name }}
                                        @if($pb->bill->is_essential ?? false)
                                        <span class="text-[8px] font-bold text-gray-500 border border-white/10 rounded px-1 py-px uppercase tracking-wider">Essential</span>
                                        @endif
                                    </div>
                                    <div class="js-bill-due-label text-[10px] {{ $urgColor }}">{{ $urgLabel }}</div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-xs font-bold text-white">{{ number_format($pb->amount) }}</span>
                                    <button @click.stop="payBill({{ $pb->id }}, '{{ addslashes($pb->bill->name) }}', {{ $pb->amount }})"
                                            class="js-bill-pay-btn text-[10px] font-bold {{ $pb->status === 'overdue' ? 'bg-red-500/20 hover:bg-red-500/30 text-red-300' : 'bg-white/10 hover:bg-white/20 text-white' }} px-2.5 py-1 rounded-lg transition-colors">
                                        Pay Now
                                    </button>
                                    <svg class="w-3 h-3 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            {{-- Expandable: Why this bill? --}}
                            <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="px-3 pb-3">
                                <div class="bg-black/20 border border-white/5 rounded-lg px-3 py-2.5 space-y-1.5">
                                    <div class="text-[9px] font-bold uppercase tracking-widest text-indigo-400">💡 Why this bill?</div>
                                    @if($pb->bill->description)
                                    <p class="text-[11px] text-gray-400 leading-snug">{{ $pb->bill->description }}</p>
                                    @endif
                                    @if($pb->bill->flavor_text)
                                    <p class="text-[11px] text-indigo-300/80 italic leading-snug">"{{ $pb->bill->flavor_text }}"</p>
                                    @endif
                                    @if($pb->bill->consequence_text)
                                    <p class="text-[11px] text-amber-300/80 leading-snug">⚠ If missed: {{ $pb->bill->consequence_text }}</p>
                                    @endif
                                    <p class="text-[10px] text-gray-600">Repeats every {{ $pb->frequency_ticks }} game days</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($allBills->count() > 8)
                    <button type="button" @click="showAllBills = !showAllBills"
                            class="w-full mt-2 text-[10px] font-bold text-indigo-400 hover:text-indigo-300 py-1.5 rounded-lg transition-colors" style="background:rgba(99,102,241,0.06);">
                        <span x-show="!showAllBills">Show all {{ $allBills->count() }} bills ↓</span>
                        <span x-show="showAllBills" x-cloak>Show less ↑</span>
                    </button>
                    @endif
                    @endif

                    {{-- Loan installments — auto-deducted like bills every game month --}}
                    @if(($activeLoans ?? collect())->isNotEmpty())
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-[11px] font-black uppercase tracking-widest text-amber-300">🏦 Loan Installments</h4>
                            <span class="text-[9px] text-gray-500">Auto-deducted when due</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($activeLoans as $loan)
                            @php
                                $lt = $loan->due_in_days;
                                $loanUrgColor = $lt <= 0 ? 'text-red-400' : ($lt <= 5 ? 'text-amber-400' : 'text-emerald-400');
                                $loanUrgLabel = $lt <= 0
                                    ? '⏳ Deducting now — keep your balance topped up'
                                    : "Next installment in {$lt} game day" . ($lt === 1 ? '' : 's') . " ({$fmtReal($lt)})";
                            @endphp
                            <div x-data="{ open: false }" class="bill-ok border rounded-xl overflow-hidden" style="border-color:rgba(245,158,11,0.25);">
                                <div class="px-3 py-2.5 flex items-center gap-2.5 cursor-pointer select-none" @click="open = !open">
                                    <span class="text-lg shrink-0">{{ $loan->loanProduct?->icon ?? '🏦' }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-white truncate flex items-center gap-1.5">
                                            {{ $loan->displayName() }}
                                            @if($loan->is_financing)
                                            <span class="text-[8px] font-bold text-amber-400 border border-amber-500/25 rounded px-1 py-px uppercase tracking-wider">Financing</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] {{ $loanUrgColor }}">{{ $loanUrgLabel }}</div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 text-right">
                                        <div>
                                            <div class="text-xs font-bold text-white">{{ number_format($loan->payment_amount) }}</div>
                                            <div class="text-[9px] text-gray-500">{{ min($loan->payments_made, $loan->installments) }}/{{ $loan->installments }} paid</div>
                                        </div>
                                        <svg class="w-3 h-3 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                                <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="px-3 pb-3">
                                    <div class="bg-black/20 border border-white/5 rounded-lg px-3 py-2.5 space-y-1.5">
                                        <div class="flex justify-between text-[11px]"><span class="text-gray-400">Outstanding balance</span><span class="font-bold text-white">Ksh {{ number_format($loan->outstanding_balance) }}</span></div>
                                        <div class="flex justify-between text-[11px]"><span class="text-gray-400">Interest rate</span><span class="font-bold text-white">{{ $loan->annual_interest_rate }}% p.a.</span></div>
                                        <div class="flex justify-between text-[11px]"><span class="text-gray-400">Repeats every</span><span class="font-bold text-white">{{ $loan->payment_period_ticks }} game days</span></div>
                                        @if($loan->payments_missed > 0)
                                        <p class="text-[11px] text-red-400">⚠ {{ $loan->payments_missed }} missed payment{{ $loan->payments_missed === 1 ? '' : 's' }} — each costs −20 credit score.</p>
                                        @endif
                                        <p class="text-[10px] text-gray-600">Installments are deducted automatically when due. Repay early at Equity Square to save on interest.</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Credit Score --}}
                <div class="glass rounded-xl p-4">
                    <h3 class="text-sm font-bold text-white mb-4">💳 Credit Score</h3>
                    @php
                        $cs     = $progress->credit_score ?? 500;
                        $csPct  = min(100, max(0, ($cs - 300) / 550 * 100));
                        $csLbl  = $progress->creditScoreLabel();
                        $csClr  = $cs < 450 ? '#ef4444' : ($cs < 550 ? '#f59e0b' : ($cs < 650 ? '#eab308' : '#10b981'));
                    @endphp
                    <div class="flex items-end justify-between mb-3">
                        <div>
                            <div class="text-xl font-black leading-none" style="color:{{ $csClr }}">{{ $cs }}</div>
                            <div class="text-xs font-bold mt-1" style="color:{{ $csClr }}">{{ $csLbl }}</div>
                        </div>
                        <div class="text-right text-[10px] text-gray-600 leading-relaxed">
                            Range: 300–850<br>
                            Affects loan access
                        </div>
                    </div>
                    {{-- Gradient gauge bar --}}
                    <div class="relative h-3 rounded-full overflow-hidden mb-1">
                        <div class="absolute inset-0 flex">
                            <div class="h-full opacity-70" style="width:18%;background:#ef4444"></div>
                            <div class="h-full opacity-70" style="width:18%;background:#f97316"></div>
                            <div class="h-full opacity-70" style="width:18%;background:#eab308"></div>
                            <div class="h-full opacity-70" style="width:18%;background:#22c55e"></div>
                            <div class="h-full opacity-70" style="width:28%;background:#10b981"></div>
                        </div>
                        {{-- Needle --}}
                        <div class="absolute top-0 bottom-0 w-0.5 bg-white rounded-full"
                             style="left:calc({{ $csPct }}% - 1px);box-shadow:0 0 6px 2px rgba(255,255,255,.7)"></div>
                    </div>
                    <div class="flex justify-between text-[9px] text-gray-600 mb-3">
                        <span>Very Poor</span><span>Poor</span><span>Fair</span><span>Good</span><span>Excellent</span>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        Pay bills on time to raise your score. Higher scores unlock better loan rates and larger credit limits.
                    </p>
                </div>

                {{-- Credit Score History --}}
                <div class="glass rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-white">📈 Credit History</h3>
                        <span class="text-[10px] text-gray-600 bg-white/5 px-2 py-0.5 rounded-full">Last {{ $creditHistory->count() > 0 ? $creditHistory->count() : 8 }} changes</span>
                    </div>

                    @if($creditHistory->isEmpty())
                    <div class="text-center py-5">
                        <div class="text-2xl mb-2">🌱</div>
                        <p class="text-xs text-gray-600 leading-relaxed max-w-[240px] mx-auto">
                            Your credit history will appear here as you pay bills, repay loans and grow savings.
                        </p>
                    </div>
                    @else
                    <div class="space-y-1.5">
                        @foreach($creditHistory as $ch)
                        @php
                            $delta = (int)($ch->data['delta'] ?? 0);
                            $score = $ch->data['score'] ?? null;
                            $reason = $ch->data['reason'] ?? $ch->title;
                        @endphp
                        <div class="flex items-center gap-2.5 bg-white/2 border border-white/5 rounded-xl px-3 py-2">
                            <span class="text-[10px] font-black shrink-0 px-2 py-0.5 rounded-full
                                {{ $delta >= 0 ? 'bg-emerald-500/10 border border-emerald-500/25 text-emerald-400' : 'bg-red-500/10 border border-red-500/25 text-red-400' }}">
                                {{ $delta >= 0 ? '+' : '' }}{{ $delta }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] font-bold text-gray-300 truncate">{{ $reason }}</div>
                                <div class="text-[9px] text-gray-600">{{ $ch->created_at->diffForHumans() }}</div>
                            </div>
                            @if($score !== null)
                            <span class="text-xs font-black text-white shrink-0">{{ $score }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Mood --}}
                @php
                    $mood      = (int)($progress->mood ?? 70);
                    $moodEmoji = $mood >= 80 ? '😄' : ($mood >= 55 ? '😊' : ($mood >= 35 ? '😐' : '😔'));
                    $moodClr   = $mood >= 80 ? '#10b981' : ($mood >= 55 ? '#34d399' : ($mood >= 35 ? '#f59e0b' : '#ef4444'));
                    $moodBoost = $progress->mood_last_boosted_at ? 'Boosted ' . $progress->mood_last_boosted_at->diffForHumans() : 'Not boosted yet';
                @endphp
                <div class="glass rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-white">{{ $moodEmoji }} Mood</h3>
                        <span class="text-[10px] text-gray-600">{{ $moodBoost }}</span>
                    </div>

                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-2xl leading-none">{{ $moodEmoji }}</span>
                        <div class="flex-1">
                            <div class="flex items-center justify-between text-[10px] mb-1">
                                <span class="text-gray-500 uppercase tracking-widest font-bold">Happiness</span>
                                <span class="font-black" style="color:{{ $moodClr }}">{{ $mood }}/100</span>
                            </div>
                            <div class="h-2.5 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                     style="width:{{ $mood }}%;background:{{ $moodClr }}"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Mood effects --}}
                    @if($mood < 40)
                    <div class="mt-3 text-[11px] font-bold text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
                        ⚠ Low mood: income reduced 10%
                    </div>
                    @elseif($mood > 80)
                    <div class="mt-3 text-[11px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-3 py-2">
                        ✓ Mood bonus: +10% quest XP
                    </div>
                    @endif

                    @if($mood < 60)
                    <a href="{{ route('world') }}"
                       class="mt-3 w-full inline-flex items-center justify-center gap-1.5 text-[11px] font-bold bg-pink-500/10 hover:bg-pink-500/20 text-pink-300 border border-pink-500/25 hover:border-pink-500/40 px-3 py-2 rounded-lg transition-colors">
                        🎡 Visit Fun World →
                    </a>
                    <p class="text-[10px] text-gray-600 mt-1.5 text-center">Spend a little on fun to lift your mood</p>
                    @endif
                </div>

            </div>
            {{-- END LEFT PANEL --}}

            {{-- ═══ RIGHT PANEL ═══ --}}
            <div class="lg:col-span-3 space-y-4">

                {{-- Next Milestone --}}
                @if($nextAsset)
                <div class="glass rounded-xl p-4 border border-indigo-500/20 relative overflow-hidden">
                    {{-- Glow accent --}}
                    <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>

                    <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-400 mb-3">🎯 Next Unlock</div>

                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div><x-icon :name="$nextAsset->icon" class="w-9 h-9" /></div>
                            <div>
                                <div class="text-base font-bold text-white">{{ $nextAsset->name }}</div>
                                <div class="text-xs text-gray-400">Ksh {{ number_format($nextAsset->base_price) }} · {{ $nextAsset->categoryLabel() }}</div>
                                @if($nextAsset->monthly_income > 0)
                                <div class="text-[11px] text-emerald-400 mt-0.5">
                                    Earns +Ksh {{ number_format($nextAsset->monthly_income - $nextAsset->monthly_cost) }}/mo net
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            @if($daysToNextAsset)
                            <div class="text-xl font-black text-indigo-400 leading-none">{{ number_format($daysToNextAsset) }}</div>
                            <div class="text-[10px] text-gray-500 mt-0.5">game days away</div>
                            @elseif($netMonthly <= 0)
                            <div class="text-xs text-red-400 font-bold">Fix deficit first</div>
                            @else
                            <div class="text-xs text-gray-500">—</div>
                            @endif
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="h-2.5 bg-white/5 rounded-full overflow-hidden mb-1.5">
                        <div class="milestone-bar h-full rounded-full" style="width:{{ $progressToNextAsset }}%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-gray-500">
                        <span>Ksh {{ number_format($progress->balance) }} saved</span>
                        <span>{{ $progressToNextAsset }}% of Ksh {{ number_format($nextAsset->base_price) }}</span>
                    </div>

                    <a href="{{ route('marketplace') }}"
                       class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-bold bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 hover:border-indigo-500/40 px-3 py-1.5 rounded-lg transition-colors">
                        🛒 Go to Marketplace →
                    </a>
                </div>
                @endif

                {{-- Your World (Asset Cards) --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-white">🌍 Your World</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-gray-500">{{ $playerAssets->count() }} asset{{ $playerAssets->count() !== 1 ? 's' : '' }}</span>
                            @if($assetIncomePerMonth > 0)
                            <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full">
                                +Ksh {{ number_format($assetIncomePerMonth) }}/mo passive
                            </span>
                            @endif
                            <a href="{{ route('marketplace') }}" class="text-[10px] font-bold text-cyan-400 hover:text-cyan-300 transition-colors">🛒 Marketplace →</a>
                        </div>
                    </div>

                    @if($playerAssets->isEmpty())
                    <div class="glass rounded-2xl p-10 text-center border border-dashed border-white/10">
                        <div class="text-6xl mb-4">🌱</div>
                        <h4 class="text-lg font-bold text-white mb-2">Your World is Empty</h4>
                        <p class="text-sm text-gray-400 max-w-xs mx-auto mb-5 leading-relaxed">
                            Assets earn you passive income while you sleep.
                            Every asset you own makes your money work for you.
                        </p>
                        <a href="{{ route('marketplace') }}"
                           class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-500/30">
                            🛒 Visit Marketplace
                        </a>
                    </div>
                    @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($playerAssets as $pa)
                        @php
                            $net = $pa->monthlyCashFlow();
                            $hasNoFlow = ($pa->asset->monthly_income ?? 0) === 0 && ($pa->asset->monthly_cost ?? 0) === 0;
                            [$statusLabel, $statusColor, $dotClass] = match(true) {
                                $hasNoFlow   => ['Appreciating', 'text-blue-400',    'bg-blue-400'],
                                $net > 0     => ['Earning',      'text-emerald-400', 'bg-emerald-400 dot-earning'],
                                $net === 0   => ['Break Even',   'text-amber-400',   'bg-amber-400'],
                                default      => ['Costly',       'text-red-400',     'bg-red-400'],
                            };
                            $catClass = 'cat-' . ($pa->asset->category ?? 'gadget');
                            $gain     = $pa->gainLoss();
                            $gainPct  = $pa->gainLossPct();
                        @endphp
                        <div class="asset-card {{ $catClass }} border rounded-2xl p-4">

                            {{-- Card header --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <x-icon :name="$pa->asset->icon" class="w-7 h-7" />
                                    <div>
                                        <div class="text-sm font-bold text-white leading-tight">{{ $pa->asset->name }}</div>
                                        <div class="text-[10px] text-gray-500 mt-0.5">
                                            {{ $pa->asset->categoryLabel() }}@if($pa->quantity > 1) · {{ $pa->quantity }}×@endif
                                        </div>
                                    </div>
                                </div>
                                {{-- Status badge --}}
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></div>
                                    <span class="text-[10px] font-bold {{ $statusColor }}">{{ $statusLabel }}</span>
                                </div>
                            </div>

                            {{-- Cash flow rows --}}
                            @if(($pa->asset->monthly_income ?? 0) > 0 || ($pa->asset->monthly_cost ?? 0) > 0)
                            <div class="space-y-1.5 pb-3 mb-3 border-b border-white/5">
                                @if(($pa->asset->monthly_income ?? 0) > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-gray-500">Income / mo</span>
                                    <span class="text-[11px] font-bold text-emerald-400">
                                        +Ksh {{ number_format(($pa->asset->monthly_income ?? 0) * $pa->quantity) }}
                                    </span>
                                </div>
                                @endif
                                @if(($pa->asset->monthly_cost ?? 0) > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-gray-500">Running costs</span>
                                    <span class="text-[11px] font-bold text-red-400">
                                        -Ksh {{ number_format(($pa->asset->monthly_cost ?? 0) * $pa->quantity) }}
                                    </span>
                                </div>
                                @endif
                                <div class="flex items-center justify-between pt-1 border-t border-white/5">
                                    <span class="text-[10px] text-gray-500 font-bold">NET / MONTH</span>
                                    <span class="text-xs font-black {{ $net >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $net >= 0 ? '+' : '' }}Ksh {{ number_format($net) }}
                                    </span>
                                </div>
                            </div>
                            @endif

                            {{-- Value + gain --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] text-gray-500">Current value</div>
                                    <div class="text-sm font-bold text-white">Ksh {{ number_format($pa->current_value) }}</div>
                                </div>
                                @if($gain !== 0)
                                <div class="text-right">
                                    <div class="text-[10px] text-gray-500">vs. purchase</div>
                                    <div class="text-[11px] font-bold {{ $gain >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $gain >= 0 ? '+' : '' }}{{ $gainPct }}%
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Condition health bar (only if has income) --}}
                            @php
                                $cond      = $pa->condition ?? 100;
                                $condLabel = $pa->conditionLabel();
                                $condColor = $pa->conditionColor();
                                $maintCost = $pa->maintenanceCost();
                            @endphp
                            @if(($pa->asset->monthly_income ?? 0) > 0)
                            <div class="mt-2 pt-2 border-t border-white/5">
                                <div class="flex items-center justify-between text-[10px] mb-1">
                                    <span class="text-gray-500">Condition</span>
                                    <span class="font-bold" style="color:{{ $condColor }}">{{ $condLabel }} ({{ $cond }}%)</span>
                                </div>
                                <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         style="width:{{ $cond }}%;background:{{ $condColor }}"></div>
                                </div>
                                @if($cond < 95)
                                <button @click="maintain({{ $pa->id }}, '{{ addslashes($pa->asset->name) }}', {{ $maintCost }})"
                                        class="mt-2 w-full text-[10px] font-bold py-1.5 rounded-lg border transition-colors"
                                        style="background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.25);color:#fbbf24;"
                                        onmouseover="this.style.background='rgba(245,158,11,0.15)'"
                                        onmouseout="this.style.background='rgba(245,158,11,0.08)'">
                                    🔧 Maintain — Ksh {{ number_format($maintCost) }}
                                </button>
                                @endif
                            </div>
                            @endif

                            {{-- Action row --}}
                            <div class="flex gap-1.5 mt-3 pt-3 border-t border-white/5">
                                <a href="{{ route('portfolio') }}"
                                   class="flex-1 text-center text-[10px] font-bold text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 py-1.5 rounded-lg transition-colors">
                                    📊 Portfolio
                                </a>
                                <a href="{{ route('trade.index') }}"
                                   class="flex-1 text-center text-[10px] font-bold text-teal-400 hover:text-teal-300 bg-teal-500/5 hover:bg-teal-500/10 py-1.5 rounded-lg border border-teal-500/15 hover:border-teal-500/30 transition-colors">
                                    🤝 Trade
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Life Events --}}
                <div class="glass rounded-xl p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-white">⚡ Life Events</h3>
                        <a href="{{ route('life.timeline') }}" class="text-[10px] text-violet-400 hover:text-violet-300 transition-colors">Full story →</a>
                    </div>

                    @if($lifeEvents->isEmpty())
                    <div class="text-center py-5 text-xs text-gray-600">
                        Life happens as game time passes — surprises, windfalls and lessons will show up here.
                    </div>
                    @else
                    @php
                        $feedChapterNames = collect(\App\Models\UserProgress::chapters())->pluck('name', 'key')->all();
                    @endphp
                    <div class="space-y-2">
                        @foreach($lifeEvents as $ple)
                        @php
                            $balChange  = $ple->effect_applied['balance_change'] ?? 0;
                            $isPositive = $ple->lifeEvent->is_positive ?? true;
                        @endphp
                        <div class="flex items-start gap-3 rounded-xl px-3 py-2.5 border
                                    {{ $isPositive ? 'bg-emerald-500/4 border-emerald-500/15' : 'bg-red-500/4 border-red-500/15' }}">
                            <span class="text-xl shrink-0 leading-none mt-0.5">{{ $ple->lifeEvent->icon ?? '⚡' }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="text-xs font-bold text-white">{{ $ple->lifeEvent->title ?? 'Life Event' }}</div>
                                    @if($balChange != 0)
                                    <span class="text-[11px] font-black shrink-0 {{ $balChange >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $balChange >= 0 ? '+' : '' }}Ksh {{ number_format($balChange) }}
                                    </span>
                                    @endif
                                </div>
                                @if($ple->lifeEvent->description ?? false)
                                <p class="text-[11px] text-gray-400 mt-0.5 leading-snug">{{ $ple->lifeEvent->description }}</p>
                                @endif
                                <p class="text-[10px] text-gray-600 mt-1.5">
                                    {{ $ple->calendarDateLabel() }} · {{ $feedChapterNames[$ple->chapter_at_trigger] ?? ucfirst($ple->chapter_at_trigger ?? 'Journey') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
            {{-- END RIGHT PANEL --}}

        </div>

        {{-- ── STATEMENT SECTION ── --}}
        <div class="mt-6" id="statement">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-white">📄 Account Statement</h3>
                <div class="flex gap-1">
                    @foreach(['all'=>'All','income'=>'Income','expenses'=>'Expenses'] as $fKey=>$fLabel)
                    <a href="{{ route('life.board', array_merge(request()->except('stmt_filter'), ['stmt_filter'=>$fKey])) }}#statement"
                       class="text-[10px] font-bold px-2.5 py-1 rounded-lg transition-colors {{ $statementFilter === $fKey ? 'bg-indigo-500/20 border border-indigo-500/40 text-indigo-300' : 'bg-white/5 border border-white/8 text-gray-500 hover:text-white' }}">
                        {{ $fLabel }}
                    </a>
                    @endforeach
                </div>
            </div>
            @if($statement->isEmpty())
            <div class="glass rounded-2xl p-5 text-center text-xs text-gray-600">
                No transactions yet. Play more to generate activity.
            </div>
            @else
            <div class="glass rounded-2xl overflow-hidden divide-y divide-white/5">
                @foreach($statement as $tx)
                @php
                    $isIncome  = in_array($tx->type, ['salary','asset_income','arcade_stake_won','arcade_forfeit_bonus','share_sell','chama_loan_disbursed','chama_withdrawal']);
                    $isExpense = in_array($tx->type, ['bill_paid','bill_missed','arcade_stake_joined','arcade_stake_lost','share_buy']);
                    $isEvent   = !$isIncome && !$isExpense;
                    $rowBg     = $isIncome ? 'bg-emerald-500/4' : ($isExpense ? 'bg-red-500/4' : 'bg-transparent');
                    $amtColor  = $isIncome ? 'text-emerald-400' : ($isExpense ? 'text-red-400' : 'text-gray-400');
                    $borderL   = $isIncome ? 'border-l-2 border-emerald-500/50' : ($isExpense ? 'border-l-2 border-red-500/50' : 'border-l-2 border-indigo-500/20');
                    $typeLabel = match($tx->type) {
                        'salary'=>'Salary','asset_income'=>'Asset Income','bill_paid'=>'Bill Paid','bill_missed'=>'Bill Missed','life_sim'=>'Event','life_event'=>'Life Event',
                        'arcade_stake_joined'=>'Rivals Trail Entry','arcade_stake_won'=>'Rivals Trail Win','arcade_stake_lost'=>'Rivals Trail Loss',
                        'arcade_forfeit_penalty'=>'Rivals Trail Withdrawal','arcade_forfeit_bonus'=>'Rivals Trail Bonus',
                        'share_buy'=>'Bought Shares','share_sell'=>'Sold Shares',
                        'job_promotion'=>'Promotion','salary_raise'=>'Pay Raise',
                        'chama_loan_disbursed'=>'Chama Loan','chama_withdrawal'=>'Chama Withdrawal','chama_dividend'=>'Chama Dividend',
                        default=>ucfirst(str_replace('_',' ',$tx->type)),
                    };
                @endphp
                <div class="{{ $rowBg }} {{ $borderL }} px-4 py-3 flex items-center gap-3">
                    <span class="text-lg shrink-0">{{ $tx->icon ?? ($isIncome ? '💚' : ($isExpense ? '💸' : '📌')) }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-white">{{ $tx->title }}</div>
                        <div class="text-[10px] text-gray-500 mt-0.5">{{ $typeLabel }} · {{ $tx->created_at->format('d M') }}</div>
                    </div>
                    <span class="text-[11px] font-bold {{ $amtColor }} shrink-0">
                        @if($tx->data && isset($tx->data['amount']))
                            {{ $isIncome ? '+' : '-' }}KES {{ number_format($tx->data['amount']) }}
                        @else
                            &mdash;
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── LIFE FEED ── --}}
        @if($lifeFeed->isNotEmpty())
        <div class="mt-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-white">📱 Life Feed</h3>
                <a href="{{ route('life.timeline') }}" class="text-[11px] text-indigo-400 hover:text-indigo-300 transition-colors">
                    Full Timeline →
                </a>
            </div>
            <div class="glass rounded-2xl overflow-hidden divide-y divide-white/5">
                @foreach($lifeFeed as $notif)
                @php
                    $isPos = in_array($notif->type, ['salary','asset_income','life_sim']) && ($notif->type !== 'bill_missed');
                    $isNeg = str_contains($notif->title ?? '', 'Missed') || $notif->type === 'bill_missed';
                    $rowBg = $isPos ? 'bg-emerald-500/5' : ($isNeg ? 'bg-red-500/5' : 'bg-transparent');
                    $leftBorder = $isPos ? 'border-l-2 border-emerald-500/40' : ($isNeg ? 'border-l-2 border-red-500/40' : 'border-l-2 border-indigo-500/20');
                @endphp
                <div class="{{ $rowBg }} {{ $leftBorder }} px-4 py-3 flex items-start gap-3">
                    <span class="text-xl shrink-0 leading-none mt-0.5">{{ $notif->icon ?? '📝' }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-bold text-white">{{ $notif->title }}</div>
                        @if($notif->body)
                        <div class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $notif->body }}</div>
                        @endif
                    </div>
                    <span class="text-[10px] text-gray-600 shrink-0 mt-0.5">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── BOTTOM QUICK NAV ── --}}
        <div class="mt-8 pt-6 border-t border-white/5 flex flex-wrap gap-2 justify-center pb-8">
            @foreach([
                ['route' => 'dashboard',       'label' => '🏠 Dashboard'],
                ['route' => 'marketplace',     'label' => '🛒 Marketplace'],
                ['route' => 'portfolio',       'label' => '💼 Portfolio'],
                ['route' => 'life.timeline',   'label' => '📜 Life Timeline'],
                ['route' => 'game.play',       'label' => '▶ Play Scenarios'],
                ['route' => 'game.leaderboard','label' => '🏆 Leaderboard'],
                ['route' => 'chama.index',     'label' => '🤝 Chama'],
            ] as $link)
            <a href="{{ route($link['route']) }}"
               class="text-xs text-gray-500 hover:text-white bg-white/3 hover:bg-white/8 border border-white/8 hover:border-white/15 px-3 py-1.5 rounded-lg transition-colors">
                {{ $link['label'] }}
            </a>
            @endforeach
        </div>

    </div>
    {{-- END MAIN --}}

    <script>
    function lifeBoard() {
        return {
            toast: false,
            toastMsg: '',
            toastOk: true,
            showAllBills: false,

            showToast(msg, ok = true) {
                this.toastMsg = msg;
                this.toastOk = ok;
                this.toast = true;
                setTimeout(() => this.toast = false, 3200);
            },

            async maintain(assetId, assetName, cost) {
                const fmt = n => new Intl.NumberFormat('en-KE').format(n);
                if (!confirm(`Maintain ${assetName}?\nCost: Ksh ${fmt(cost)}\nRestores condition by +40 points.`)) return;
                try {
                    const res = await fetch(`/life/assets/${assetId}/maintain`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showToast(`🔧 ${assetName} maintained! Condition: ${data.condition}%. Balance: Ksh ${fmt(data.new_balance)}`, true);
                        setTimeout(() => location.reload(), 1800);
                    } else {
                        this.showToast(`❌ ${data.error ?? 'Maintenance failed'}`, false);
                    }
                } catch(e) {
                    this.showToast('❌ Network error — try again', false);
                }
            },

            async payBill(billId, billName, amount) {
                const fmt = n => new Intl.NumberFormat('en-KE').format(n);
                if (!confirm(`Pay ${billName} — Ksh ${fmt(amount)}?\nThis will deduct from your balance immediately.`)) return;

                try {
                    const res = await fetch(`/life/bills/${billId}/pay`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showToast(`✅ ${billName} paid! Balance: Ksh ${fmt(data.new_balance)}`, true);
                        pesaSound('bill');

                        // Stays on the board (not removed) so a player can still
                        // pay the next cycle early if they want — just re-flavor
                        // it as paid/not-overdue with its new due date.
                        const row = document.getElementById(`bill-row-${billId}`);
                        const wasOverdue = row?.dataset.overdue === '1';
                        if (row) {
                            row.dataset.overdue = '0';
                            row.classList.remove('bill-overdue', 'bill-urgent', 'bill-ok');
                            row.classList.add(data.urgent ? 'bill-urgent' : 'bill-ok');

                            const dueEl = row.querySelector('.js-bill-due-label');
                            if (dueEl) {
                                dueEl.textContent = data.due_label;
                                dueEl.className = 'text-[10px] ' + (data.urgent ? 'text-amber-400' : 'text-emerald-400');
                            }

                            const payBtn = row.querySelector('.js-bill-pay-btn');
                            if (payBtn) {
                                payBtn.className = 'text-[10px] font-bold bg-white/10 hover:bg-white/20 text-white px-2.5 py-1 rounded-lg transition-colors';
                            }
                        }

                        document.querySelectorAll('[data-balance]').forEach(el => el.textContent = `Ksh ${fmt(data.new_balance)}`);

                        if (wasOverdue) {
                            const countEl = document.getElementById('bills-overdue-count');
                            if (countEl) {
                                const remaining = Math.max(0, parseInt(countEl.textContent, 10) - 1);
                                if (remaining === 0) document.getElementById('bills-overdue-badge')?.remove();
                                else countEl.textContent = remaining;
                            }
                        }
                    } else {
                        this.showToast(`❌ ${data.error ?? 'Payment failed'}`, false);
                    }
                } catch(e) {
                    this.showToast('❌ Network error — try again', false);
                }
            }
        };
    }
    </script>

<script>
function pesaSound(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        if (type === 'bill') {
            const o=ctx.createOscillator(),g=ctx.createGain();
            o.connect(g);g.connect(ctx.destination);
            o.type='sine';
            o.frequency.setValueAtTime(440,ctx.currentTime);
            o.frequency.exponentialRampToValueAtTime(220,ctx.currentTime+.18);
            g.gain.setValueAtTime(.2,ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+.22);
            o.start();o.stop(ctx.currentTime+.22);
        } else if (type === 'purchase') {
            [880,1047,1319].forEach((freq,i) => {
                const o=ctx.createOscillator(),g=ctx.createGain();
                o.connect(g);g.connect(ctx.destination);
                o.type='triangle';
                o.frequency.setValueAtTime(freq,ctx.currentTime+i*.07);
                g.gain.setValueAtTime(.18,ctx.currentTime+i*.07);
                g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+i*.07+.15);
                o.start(ctx.currentTime+i*.07);o.stop(ctx.currentTime+i*.07+.15);
            });
        }
    } catch(e) {}
}
</script>
</div>
