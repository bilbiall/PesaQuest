<div class="fin-bg min-h-screen text-white font-sans antialiased" data-page-title="Finances — PesaQuest">

    {{-- ── Hero ── --}}
    <div class="border-b border-white/5 py-8 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <h1 class="text-2xl sm:text-3xl font-black mb-2">🧾 Your Finances</h1>
            <p class="text-gray-400 text-sm">Everything about your money, in one place — statement, investments, savings.</p>

            <div class="grid grid-cols-3 gap-3 mt-6">
                <div class="rounded-2xl p-3 text-center" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);">
                    <p class="text-lg font-black {{ $netMonthly >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $netMonthly >= 0 ? '+' : '' }}Ksh {{ number_format($netMonthly) }}
                    </p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Net / month</p>
                </div>
                <div class="rounded-2xl p-3 text-center" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);">
                    <p class="text-lg font-black text-indigo-400">{{ $savingsRate }}%</p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Savings rate</p>
                </div>
                <div class="rounded-2xl p-3 text-center" style="background:rgba(56,189,248,0.08);border:1px solid rgba(56,189,248,0.2);">
                    <p class="text-lg font-black text-sky-400">Ksh {{ number_format($totalValue + $totalSaved) }}</p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Assets + Savings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        {{-- ── Monthly Statement ── --}}
        <div class="glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-white">📊 Monthly Statement</h2>
                <span class="text-[10px] text-gray-600 bg-white/5 px-2 py-0.5 rounded-full">Game Month</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Income --}}
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-emerald-500/60 mb-2">Income</div>
                    <div class="space-y-2">
                        @if($salaryPerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5"><span class="text-base">💼</span> {{ $progress->career_title ?? 'Salary' }}</span>
                            <span class="text-xs font-bold text-emerald-400">+{{ number_format($salaryPerMonth) }}</span>
                        </div>
                        @endif
                        @if($assetIncomePerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5"><span class="text-base">🏢</span> Passive income</span>
                            <span class="text-xs font-bold text-emerald-400">+{{ number_format($assetIncomePerMonth) }}</span>
                        </div>
                        @endif
                        @if($totalIncome === 0)
                        <div class="text-xs text-gray-600 italic py-1">No income sources yet</div>
                        @else
                        <div class="flex items-center justify-between pt-2 border-t border-emerald-500/10">
                            <span class="text-[10px] text-gray-500 font-bold">TOTAL IN</span>
                            <span class="text-sm font-black text-emerald-400">Ksh {{ number_format($totalIncome) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Expenses --}}
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-red-500/60 mb-2">Expenses</div>
                    <div class="space-y-2">
                        @if($billsBurnPerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5"><span class="text-base">🏠</span> Bills &amp; Living</span>
                            <span class="text-xs font-bold text-red-400">-{{ number_format($billsBurnPerMonth) }}</span>
                        </div>
                        @endif
                        @if($assetCostsPerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5"><span class="text-base">🔧</span> Asset Running Costs</span>
                            <span class="text-xs font-bold text-red-400">-{{ number_format($assetCostsPerMonth) }}</span>
                        </div>
                        @endif
                        @if($loanPaymentsPerMonth > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400 flex items-center gap-1.5"><span class="text-base">🏦</span> Loan Installments</span>
                            <span class="text-xs font-bold text-red-400">-{{ number_format($loanPaymentsPerMonth) }}</span>
                        </div>
                        @endif
                        @if($totalExpenses === 0)
                        <div class="text-xs text-gray-600 italic py-1">No recurring expenses yet</div>
                        @else
                        <div class="flex items-center justify-between pt-2 border-t border-red-500/10">
                            <span class="text-[10px] text-gray-500 font-bold">TOTAL OUT</span>
                            <span class="text-sm font-black text-red-400">Ksh {{ number_format($totalExpenses) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Transaction ledger --}}
            <div class="mt-5 pt-4 border-t border-white/5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-gray-300">Recent Transactions</h3>
                    <div class="flex gap-1.5">
                        @foreach(['all' => 'All', 'income' => 'Income', 'expenses' => 'Expenses'] as $fKey => $fLabel)
                        <a href="{{ route('life.finances', ['stmt_filter' => $fKey]) }}" data-life-tab="finances"
                           class="text-[10px] font-bold px-2.5 py-1 rounded-lg transition-colors {{ $statementFilter === $fKey ? 'bg-indigo-500/20 border border-indigo-500/40 text-indigo-300' : 'bg-white/5 border border-white/8 text-gray-500 hover:text-white' }}">
                            {{ $fLabel }}
                        </a>
                        @endforeach
                    </div>
                </div>

                @if($statement->isEmpty())
                <p class="text-xs text-gray-600 italic py-3 text-center">Nothing here yet — transactions will appear as you play.</p>
                @else
                <div class="space-y-1.5">
                    @foreach($statement as $s)
                    <div class="flex items-center justify-between gap-3 py-1.5">
                        <span class="text-xs text-gray-400 flex items-center gap-2 min-w-0">
                            <span class="flex-shrink-0">{{ $s->icon ?? '💳' }}</span>
                            <span class="truncate">{{ $s->title }}</span>
                        </span>
                        <span class="text-[10px] text-gray-600 flex-shrink-0">{{ $s->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ── Portfolio + Savings snapshots ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Portfolio snapshot --}}
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5" style="background:linear-gradient(135deg,rgba(99,102,241,0.1),rgba(99,102,241,0.03));">
                    <h2 class="text-sm font-bold text-indigo-300">💼 Portfolio</h2>
                    <p class="text-[10px] text-gray-500 mt-0.5">Assets, deals &amp; loans</p>
                </div>
                <div class="p-5">
                    @if($playerAssets->isEmpty() && $activeDealsCount === 0)
                    <p class="text-xs text-gray-500 mb-4">You don't own any assets or deals yet. The marketplace is where money starts working for you.</p>
                    @else
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">Holdings Value</p>
                            <p class="text-lg font-black text-white">Ksh {{ number_format($totalValue) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">Unrealised P/L</p>
                            <p class="text-lg font-black {{ $unrealisedPL >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $unrealisedPL >= 0 ? '+' : '' }}Ksh {{ number_format($unrealisedPL) }}
                            </p>
                        </div>
                    </div>
                    @if($topHoldings->isNotEmpty())
                    <div class="space-y-1.5 mb-4">
                        @foreach($topHoldings as $h)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-400 flex items-center gap-1.5 truncate"><x-icon :name="$h->asset->icon ?? 'store'" class="w-3.5 h-3.5" /> {{ $h->asset->name ?? 'Asset' }}</span>
                            <span class="font-bold text-white flex-shrink-0">Ksh {{ number_format($h->current_value) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @if($activeDealsCount > 0)
                    <p class="text-xs text-gray-500 mb-4">{{ $activeDealsCount }} active investment deal{{ $activeDealsCount === 1 ? '' : 's' }} maturing.</p>
                    @endif
                    @if($totalDebt > 0)
                    <p class="text-xs text-amber-400 mb-4">⚠️ Ksh {{ number_format($totalDebt) }} outstanding across {{ $activeLoans->count() }} loan{{ $activeLoans->count() === 1 ? '' : 's' }}.</p>
                    @endif
                    @endif
                    <a href="{{ route('portfolio') }}" class="fin-cta inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                        View full portfolio →
                    </a>
                </div>
            </div>

            {{-- Savings snapshot --}}
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/5" style="background:linear-gradient(135deg,rgba(16,185,129,0.1),rgba(16,185,129,0.03));">
                    <h2 class="text-sm font-bold text-emerald-300">🏦 Savings</h2>
                    <p class="text-[10px] text-gray-500 mt-0.5">Goals &amp; interest</p>
                </div>
                <div class="p-5">
                    @if($savingsSchemes->isEmpty())
                    <p class="text-xs text-gray-500 mb-4">No savings goals yet. Starting one — even a small one — is the first habit that compounds.</p>
                    @else
                    <div class="mb-4">
                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">Total Saved</p>
                        <p class="text-lg font-black text-emerald-400">Ksh {{ number_format($totalSaved) }}</p>
                        <p class="text-[10px] text-gray-600 mt-0.5">across {{ $savingsSchemes->count() }} goal{{ $savingsSchemes->count() === 1 ? '' : 's' }}</p>
                    </div>
                    @if($closestGoal)
                    @php $pct = $closestGoal->target_amount > 0 ? min(100, (int) (($closestGoal->current_amount / $closestGoal->target_amount) * 100)) : 0; @endphp
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-gray-400 truncate">{{ $closestGoal->name ?? 'Savings goal' }}</span>
                            <span class="font-bold text-white flex-shrink-0">{{ $pct }}%</span>
                        </div>
                        <div class="h-1.5 rounded-full" style="background:rgba(255,255,255,0.06);">
                            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:linear-gradient(90deg,#10b981,#059669);"></div>
                        </div>
                    </div>
                    @endif
                    @endif
                    <a href="{{ route('savings.index') }}" class="fin-cta inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white" style="background:linear-gradient(135deg,#10b981,#059669);">
                        Manage savings →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
