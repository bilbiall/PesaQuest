<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Money Tools – PesaQuest</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        body{background:#080710;}
        [x-cloak]{display:none!important}
        .tools-bg{background:radial-gradient(ellipse at top left,rgba(16,185,129,0.10) 0%,transparent 55%),radial-gradient(ellipse at bottom right,rgba(99,102,241,0.10) 0%,transparent 55%),#080710;}
        .ifield{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.10);border-radius:0.75rem;color:white;padding:0.625rem 0.875rem;width:100%;font-size:0.875rem;transition:border-color 0.2s;outline:none;}
        .ifield:focus{border-color:rgba(99,102,241,0.6);}
        .ifield option{background:#1a1a2e;}
        .lbl{font-size:0.7rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:0.35rem;}
        .tab-btn{padding:0.5rem 1.1rem;border-radius:0.875rem;font-size:0.8rem;font-weight:700;transition:all 0.2s;cursor:pointer;border:1px solid transparent;white-space:nowrap;}
        .tab-btn.active{background:rgba(99,102,241,0.2);border-color:rgba(99,102,241,0.5);color:#a5b4fc;}
        .tab-btn:not(.active){color:#6b7280;}
        .tab-btn:not(.active):hover{color:#9ca3af;background:rgba(255,255,255,0.03);}
        .calc-btn{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;font-weight:700;padding:0.625rem 1.5rem;border-radius:0.75rem;transition:all 0.2s;width:100%;}
        .calc-btn:hover{opacity:0.9;transform:scale(1.01);}
        .tool-card{background:rgba(255,255,255,0.025);border:1px solid rgba(255,255,255,0.07);border-radius:1.5rem;}
        .result-box{background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(5,150,105,0.06));border:1px solid rgba(16,185,129,0.3);border-radius:1rem;}
        .result-box.neg{background:linear-gradient(135deg,rgba(239,68,68,0.12),rgba(220,38,38,0.06));border-color:rgba(239,68,68,0.3);}
        .result-box.amber{background:linear-gradient(135deg,rgba(245,158,11,0.12),rgba(251,191,36,0.06));border-color:rgba(245,158,11,0.3);}
        .budget-row{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;padding:0.45rem 0.65rem;display:flex;align-items:center;gap:0.4rem;}
        .budget-row input{background:transparent;border:none;color:white;font-size:0.8rem;flex:1;outline:none;}
        .budget-row input::placeholder{color:#4b5563;}
        .budget-row select{background:transparent;border:none;color:#9ca3af;font-size:0.7rem;outline:none;cursor:pointer;}
        .budget-row select option{background:#1a1a2e;}
        .tip-card{background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.18);border-radius:1rem;}
        .pbar-bg{background:rgba(255,255,255,0.06);border-radius:9999px;overflow:hidden;}
        .pbar-fill{background:linear-gradient(90deg,#10b981,#34d399);border-radius:9999px;transition:width 0.8s cubic-bezier(0.34,1.56,0.64,1);}
        .pbar-fill.danger{background:linear-gradient(90deg,#ef4444,#f97316);}
        .pbar-fill.amber{background:linear-gradient(90deg,#f59e0b,#fbbf24);}
        .scheme-card{background:rgba(255,255,255,0.025);border:1px solid rgba(255,255,255,0.08);border-radius:1.25rem;transition:border-color 0.2s;}
        .scheme-card:hover{border-color:rgba(99,102,241,0.3);}
        /* Ring chart */
        .ring-chart{position:relative;display:inline-flex;align-items:center;justify-content:center;}
        .ring-chart svg{transform:rotate(-90deg);}
        .ring-center{position:absolute;text-align:center;}
        /* 50/30/20 bars */
        .ratio-bar{height:12px;border-radius:6px;transition:width 0.7s cubic-bezier(0.34,1.56,0.64,1);}
        .needs-bar{background:linear-gradient(90deg,#6366f1,#8b5cf6);}
        .wants-bar{background:linear-gradient(90deg,#f59e0b,#fbbf24);}
        .savings-bar{background:linear-gradient(90deg,#10b981,#34d399);}
    </style>
</head>
<body class="tools-bg min-h-screen text-white font-sans antialiased" x-data="smartTools()" x-cloak>

    <header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('landing') }}" class="hover:opacity-80 transition-opacity">
                    <img src="{{ asset('moski-logo.png') }}" alt="Moski" class="h-10 w-auto rounded-xl object-cover">
                </a>
                <div>
                    <h1 class="font-black text-lg leading-none">Smart Money Tools</h1>
                    <p class="text-xs text-gray-500">Financial tools to level up your decisions</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('game.play') }}" class="hidden sm:flex items-center gap-1.5 text-xs text-indigo-400 border border-indigo-500/30 hover:border-indigo-500/60 px-3 py-1.5 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg> Play
                </a>
                <a href="{{ route('dashboard') }}" class="hidden sm:flex items-center gap-1.5 text-xs text-gray-400 border border-white/10 hover:border-white/25 px-3 py-1.5 rounded-lg transition-colors">Dashboard</a>
            </div>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

        {{-- Hero --}}
        <div class="rounded-3xl mb-7 p-6 sm:p-8 relative overflow-hidden"
             style="background:linear-gradient(135deg,rgba(16,185,129,0.12),rgba(99,102,241,0.10),rgba(245,158,11,0.08));border:1px solid rgba(16,185,129,0.2);">
            <h2 class="text-2xl sm:text-3xl font-black text-white mb-1">Calculate Before You Commit</h2>
            <p class="text-sm text-gray-400 max-w-lg">Real financial tools for Kenya. Plan, track, and grow your money.</p>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-2 mb-7 overflow-x-auto pb-1">
            @foreach([
                ['savings_schemes','💳 My Savings'],
                ['budget','📊 Budget 50/30/20'],
                ['loan','🏦 Loan'],
                ['calculator','💰 Savings Calc'],
                ['goal','🎯 Goal'],
            ] as [$key,$label])
            <button class="tab-btn" :class="activeTool==='{{ $key }}' ? 'active' : ''" @click="activeTool='{{ $key }}'">{{ $label }}</button>
            @endforeach
        </div>

        {{-- ════════════════════════════════
             MY SAVINGS SCHEMES
        ════════════════════════════════ --}}
        <div x-show="activeTool==='savings_schemes'" class="space-y-5">

            {{-- Create scheme --}}
            <div class="tool-card p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-500/15 border border-emerald-500/30 rounded-xl flex items-center justify-center text-xl">💳</div>
                    <div>
                        <h3 class="font-black text-sm">My Savings Schemes</h3>
                        <p class="text-xs text-gray-500">Create named savings goals and track every deposit</p>
                    </div>
                </div>

                <div x-show="!showNewScheme">
                    <button @click="showNewScheme=true" class="w-full py-3 rounded-xl text-sm font-bold border border-dashed border-emerald-500/40 text-emerald-400 hover:border-emerald-500/70 hover:bg-emerald-500/5 transition-all flex items-center justify-center gap-2">
                        + Create New Savings Scheme
                    </button>
                </div>

                <div x-show="showNewScheme" x-cloak class="space-y-3">
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <span class="lbl">Scheme Name</span>
                            <input type="text" x-model="newScheme.name" class="ifield" placeholder="e.g. New Phone, Emergency Fund">
                        </div>
                        <div>
                            <span class="lbl">Target Amount (Ksh)</span>
                            <input type="number" x-model.number="newScheme.target" class="ifield" min="1" placeholder="e.g. 50000">
                        </div>
                        <div>
                            <span class="lbl">Icon / Emoji</span>
                            <input type="text" x-model="newScheme.emoji" class="ifield" maxlength="4" placeholder="💰">
                        </div>
                        <div>
                            <span class="lbl">Color Theme</span>
                            <input type="color" x-model="newScheme.color" class="ifield h-10 p-1 cursor-pointer">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="createScheme()" :disabled="creatingScheme" class="calc-btn flex-1">
                            <span x-text="creatingScheme ? 'Creating…' : 'Create Scheme'"></span>
                        </button>
                        <button @click="showNewScheme=false" class="px-4 py-2 rounded-xl text-sm text-gray-400 border border-white/10 hover:border-white/20 transition-colors">Cancel</button>
                    </div>
                </div>
            </div>

            {{-- Schemes list --}}
            <div x-show="loadingSchemes" class="text-center py-12 text-gray-500">
                <svg class="w-8 h-8 animate-spin mx-auto mb-2 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Loading your schemes…
            </div>

            <template x-if="!loadingSchemes && schemes.length===0">
                <div class="text-center py-12 text-gray-600">
                    <div class="text-5xl mb-3">🎯</div>
                    <p class="text-sm">No savings schemes yet — create your first one above!</p>
                </div>
            </template>

            <template x-for="scheme in schemes" :key="scheme.id">
                <div class="scheme-card p-5">
                    <div class="flex items-start gap-4">
                        {{-- Ring chart --}}
                        <div class="ring-chart flex-shrink-0">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="32" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="10"/>
                                <circle cx="40" cy="40" r="32" fill="none"
                                        :stroke="scheme.color || '#10b981'"
                                        stroke-width="10"
                                        :stroke-dasharray="`${scheme.progress_pct * 2.01} 201`"
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="ring-center">
                                <div class="text-lg font-black" :style="`color:${scheme.color||'#10b981'}`" x-text="scheme.emoji||'💰'"></div>
                                <div class="text-[10px] font-bold text-gray-300" x-text="scheme.progress_pct+'%'"></div>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-black text-white text-base" x-text="scheme.name"></div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span class="font-bold text-white">Ksh <span x-text="fmtK(scheme.current_amount)"></span></span>
                                        <span> of Ksh </span>
                                        <span class="font-bold" x-text="fmtK(scheme.target_amount)"></span>
                                    </div>
                                </div>
                                <button @click="archiveScheme(scheme.id)" title="Archive" class="text-gray-600 hover:text-red-400 transition-colors text-sm">✕</button>
                            </div>

                            {{-- Progress bar --}}
                            <div class="pbar-bg h-2 mt-3">
                                <div class="pbar-fill h-2" :style="`width:${scheme.progress_pct}%;background:linear-gradient(90deg,${scheme.color||'#10b981'},${scheme.color||'#10b981'}aa)`"></div>
                            </div>

                            <div class="flex items-center justify-between mt-2 text-xs">
                                <span class="text-gray-500">Ksh <span x-text="fmtK(scheme.target_amount - scheme.current_amount)"></span> to go</span>
                                <span x-show="scheme.estimated_date" class="text-gray-500">Est. <span class="font-bold text-gray-300" x-text="scheme.estimated_date"></span></span>
                            </div>

                            {{-- Recent deposits --}}
                            <div x-show="scheme.deposits && scheme.deposits.length>0" class="mt-3 space-y-1">
                                <div class="text-xs text-gray-600 uppercase tracking-widest font-bold mb-1">Recent deposits</div>
                                <template x-for="d in scheme.deposits" :key="d.date+d.amount">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-400" x-text="d.note || 'Deposit'"></span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-emerald-400">+Ksh <span x-text="fmtK(d.amount)"></span></span>
                                            <span class="text-gray-600" x-text="d.date"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Add deposit --}}
                            <div x-show="depositingId===scheme.id" x-cloak class="mt-3 flex gap-2">
                                <input type="number" x-model.number="depositAmount" class="ifield flex-1 text-xs py-2" min="1" placeholder="Amount (Ksh)">
                                <input type="text" x-model="depositNote" class="ifield flex-1 text-xs py-2" placeholder="Note (optional)">
                                <button @click="addDeposit(scheme.id)" :disabled="addingDeposit"
                                        class="px-3 py-2 rounded-xl text-xs font-bold text-white flex-shrink-0 transition-all hover:opacity-90"
                                        style="background:linear-gradient(135deg,#10b981,#059669);">
                                    <span x-text="addingDeposit?'…':'+Add'"></span>
                                </button>
                                <button @click="depositingId=null" class="px-2 py-2 rounded-xl text-xs text-gray-400 border border-white/10 hover:border-white/20">✕</button>
                            </div>

                            <button x-show="depositingId!==scheme.id" @click="depositingId=scheme.id;depositAmount=0;depositNote=''"
                                    class="mt-3 text-xs text-emerald-400 border border-emerald-500/25 px-3 py-1.5 rounded-lg hover:border-emerald-500/50 transition-colors">
                                + Add Deposit
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="tip-card p-4">
                <div class="text-xs font-bold text-indigo-400 mb-2">💡 Tip: Give every shilling a name</div>
                <p class="text-xs text-gray-400 leading-relaxed">Named savings goals are funded <strong class="text-white">3× faster</strong> than vague "save money" plans. Create a separate scheme for every goal — phone, emergency fund, school fees.</p>
            </div>
        </div>

        {{-- ════════════════════════════════
             BUDGET PLANNER 50/30/20
        ════════════════════════════════ --}}
        <div x-show="activeTool==='budget'" x-cloak class="space-y-5">
            <div class="tool-card p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 bg-purple-500/15 border border-purple-500/30 rounded-xl flex items-center justify-center text-xl">📊</div>
                    <div>
                        <h3 class="font-black text-sm">Monthly Budget Planner</h3>
                        <p class="text-xs text-gray-500">50% Needs · 30% Wants · 20% Savings — customizable</p>
                    </div>
                </div>

                {{-- Income streams --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Income Streams</span>
                        <button @click="addIncomeRow()" class="text-xs text-emerald-400 hover:text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded-lg">+ Add</button>
                    </div>
                    <div class="space-y-1.5">
                        <template x-for="(row,i) in budget.income" :key="i">
                            <div class="budget-row">
                                <span class="text-emerald-400 flex-shrink-0 text-sm">+</span>
                                <input x-model="row.label" placeholder="Source" @input="calcBudget()" class="min-w-0">
                                <select x-model="row.type" @change="calcBudget()" class="shrink-0 text-[10px]">
                                    <option value="salary">Salary</option>
                                    <option value="business">Business</option>
                                    <option value="side_hustle">Side Hustle</option>
                                    <option value="rental">Rental</option>
                                    <option value="other">Other</option>
                                </select>
                                <span class="text-gray-500 text-xs flex-shrink-0">Ksh</span>
                                <input type="number" x-model.number="row.amount" placeholder="0" min="0" style="width:80px;text-align:right;" @input="calcBudget()">
                                <button @click="removeIncomeRow(i)" x-show="budget.income.length>1" class="text-gray-600 hover:text-red-400 flex-shrink-0 text-sm">✕</button>
                            </div>
                        </template>
                    </div>
                    <div class="text-xs text-emerald-400 font-bold mt-2 text-right">Total Income: Ksh <span x-text="fmtK(budget.totalIncome)"></span></div>
                </div>

                {{-- Ratio customiser --}}
                <div class="rounded-xl p-4 mb-5" style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.15);">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-indigo-300">Budget Ratio</span>
                        <button @click="resetRatio()" class="text-xs text-gray-600 hover:text-gray-400 transition-colors">Reset to 50/30/20</button>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-indigo-300 w-24 flex-shrink-0">🏠 Needs</span>
                            <input type="range" x-model.number="ratio.needs" min="10" max="80" step="5" @input="clampRatio('needs')" class="flex-1 accent-indigo-500">
                            <input type="number" x-model.number="ratio.needs" min="10" max="80" @input="clampRatio('needs')" class="w-12 text-xs text-center rounded-lg border border-white/10 bg-white/5 text-white p-1 outline-none">
                            <span class="text-xs text-gray-500">%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-amber-300 w-24 flex-shrink-0">🎮 Wants</span>
                            <input type="range" x-model.number="ratio.wants" min="5" max="60" step="5" @input="clampRatio('wants')" class="flex-1 accent-amber-500">
                            <input type="number" x-model.number="ratio.wants" min="5" max="60" @input="clampRatio('wants')" class="w-12 text-xs text-center rounded-lg border border-white/10 bg-white/5 text-white p-1 outline-none">
                            <span class="text-xs text-gray-500">%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-emerald-300 w-24 flex-shrink-0">💰 Savings</span>
                            <div class="flex-1 h-2 rounded-full flex overflow-hidden" style="background:rgba(255,255,255,0.06);">
                                <div class="h-full" :style="`width:${ratio.needs}%;background:rgba(99,102,241,0.6);`"></div>
                                <div class="h-full" :style="`width:${ratio.wants}%;background:rgba(245,158,11,0.6);`"></div>
                                <div class="h-full" :style="`width:${ratio.savings}%;background:rgba(16,185,129,0.6);`"></div>
                            </div>
                            <span class="w-12 text-xs text-center text-emerald-300 font-bold" x-text="ratio.savings+'%'"></span>
                            <span class="text-xs text-gray-500">%</span>
                        </div>
                    </div>
                </div>

                {{-- Expense rows grouped by category --}}
                @foreach(['needs'=>['color'=>'#818cf8','label'=>'🏠 Needs','examples'=>'Rent, Food, Transport, Utilities, Insurance'],'wants'=>['color'=>'#fbbf24','label'=>'🎮 Wants','examples'=>'Entertainment, Eating out, Shopping, Subscriptions'],'savings'=>['color'=>'#34d399','label'=>'💰 Savings / Investment','examples'=>'Emergency fund, Investments, Pension, Goals']] as $cat=>$info)
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest" style="color:{{ $info['color'] }}">{{ $info['label'] }}</span>
                            <span class="text-[10px] text-gray-600 ml-2">{{ $info['examples'] }}</span>
                        </div>
                        <button @click="addExpenseRow('{{ $cat }}')" class="text-xs hover:opacity-80 border px-2 py-0.5 rounded-lg transition-colors"
                                style="color:{{ $info['color'] }};border-color:{{ $info['color'] }}44;">+ Add</button>
                    </div>
                    <div class="space-y-1.5">
                        <template x-for="(row,i) in budget.expenses.filter(e=>e.cat==='{{ $cat }}')" :key="i+'{{ $cat }}'">
                            <div class="budget-row">
                                <span class="flex-shrink-0 text-sm" style="color:{{ $info['color'] }}">−</span>
                                <input x-model="row.label" placeholder="Category" @input="calcBudget()">
                                <span class="text-gray-500 text-xs flex-shrink-0">Ksh</span>
                                <input type="number" x-model.number="row.amount" placeholder="0" min="0" style="width:80px;text-align:right;" @input="calcBudget()">
                                <button @click="removeExpenseRow('{{ $cat }}',i)" class="text-gray-600 hover:text-red-400 flex-shrink-0 text-sm">✕</button>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center justify-between mt-1.5 text-xs">
                        <span style="color:{{ $info['color'] }}">Spent: Ksh <span x-text="fmtK(categoryTotal('{{ $cat }}'))"></span></span>
                        <span class="text-gray-600">Target: Ksh <span x-text="fmtK(categoryTarget('{{ $cat }}'))"></span> (<span x-text="ratio.{{ $cat }}"></span>%)</span>
                    </div>
                    <div class="pbar-bg h-1.5 mt-1">
                        <div class="ratio-bar {{ $cat }}-bar h-1.5"
                             :class="categoryTotal('{{ $cat }}')>categoryTarget('{{ $cat }}') ? 'danger' : ''"
                             :style="`width:${Math.min(100,categoryTarget('{{ $cat }}')>0 ? Math.round(categoryTotal('{{ $cat }}')/categoryTarget('{{ $cat }}')*100) : 0)}%;background:{{ $info['color'] }}88`"></div>
                    </div>
                </div>
                @endforeach

                {{-- Summary --}}
                <div class="result-box p-5 space-y-4" :class="budget.surplus<0?'neg':budget.surplus<budget.totalIncome*0.05?'amber':''">
                    <div class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Budget Summary</div>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div><div class="text-xs text-gray-500 mb-1">Income</div><div class="text-base font-black text-emerald-300">Ksh <span x-text="fmtK(budget.totalIncome)"></span></div></div>
                        <div><div class="text-xs text-gray-500 mb-1">Expenses</div><div class="text-base font-black text-red-300">Ksh <span x-text="fmtK(budget.totalExpenses)"></span></div></div>
                        <div><div class="text-xs text-gray-500 mb-1">Surplus</div><div class="text-xl font-black" :class="budget.surplus>=0?'text-emerald-300':'text-red-300'"><span x-text="budget.surplus>=0?'Ksh '+fmtK(budget.surplus):'−Ksh '+fmtK(Math.abs(budget.surplus))"></span></div></div>
                    </div>

                    {{-- 3-bucket visual --}}
                    <div>
                        <div class="text-xs text-gray-500 mb-2">50/30/20 health check</div>
                        <div class="space-y-2">
                            <template x-for="b in buckets" :key="b.cat">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs w-20 flex-shrink-0" :style="`color:${b.color}`" x-text="b.label"></span>
                                    <div class="flex-1 pbar-bg h-2.5">
                                        <div class="h-2.5 rounded-full transition-all duration-700"
                                             :style="`width:${Math.min(100,b.target>0?Math.round(b.spent/b.target*100):0)}%;background:${b.color}99`"></div>
                                    </div>
                                    <span class="text-xs w-14 text-right flex-shrink-0" :style="`color:${b.color}`" x-text="(b.target>0?Math.round(b.spent/b.target*100):0)+'%'"></span>
                                    <span class="text-[10px] w-8 text-right text-gray-600 flex-shrink-0" :class="b.spent>b.target?'text-red-400 font-bold':''">
                                        <span x-text="b.spent<=b.target?'✓':'over'"></span>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tip-card p-4">
                <div class="text-xs font-bold text-indigo-400 mb-2">💡 The 50/30/20 Rule</div>
                <p class="text-xs text-gray-400 leading-relaxed"><strong class="text-white">50% Needs</strong> (rent, food, bills) · <strong class="text-white">30% Wants</strong> (fun, dining out) · <strong class="text-white">20% Savings</strong>. In Kenya, rent often takes more — adjust the sliders to match your reality.</p>
            </div>
        </div>

        {{-- ════════════════════════════════
             LOAN CALCULATOR
        ════════════════════════════════ --}}
        <div x-show="activeTool==='loan'" x-cloak class="space-y-5">
            <div class="tool-card p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 bg-indigo-500/15 border border-indigo-500/30 rounded-xl flex items-center justify-center text-xl">🏦</div>
                    <div><h3 class="font-black text-sm">Loan Repayment Calculator</h3><p class="text-xs text-gray-500">Know your monthly payment before you borrow</p></div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4 mb-5">
                    <div><span class="lbl">Loan Amount (Ksh)</span><input type="number" class="ifield" x-model.number="loan.principal" min="0" placeholder="e.g. 50000" @input="calcLoan()"></div>
                    <div><span class="lbl">Annual Interest Rate (%)</span><input type="number" class="ifield" x-model.number="loan.rate" min="0" step="0.5" placeholder="e.g. 13" @input="calcLoan()"></div>
                    <div><span class="lbl">Loan Term (months)</span><input type="number" class="ifield" x-model.number="loan.months" min="1" max="360" placeholder="e.g. 12" @input="calcLoan()"></div>
                    <div><span class="lbl">Loan Type</span><select class="ifield" x-model="loan.type" @change="calcLoan()"><option value="reducing">Reducing Balance (Bank)</option><option value="flat">Flat Rate (Mobile/SACCO)</option></select></div>
                </div>
                <button class="calc-btn" @click="calcLoan()">Calculate Repayment</button>
                <div x-show="loan.monthly>0" x-cloak class="mt-5 space-y-3">
                    <div class="result-box amber p-5">
                        <div class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-3">Repayment Summary</div>
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div><div class="text-xs text-gray-500 mb-1">Monthly</div><div class="text-xl font-black text-amber-300">Ksh <span x-text="fmtK(loan.monthly)"></span></div></div>
                            <div><div class="text-xs text-gray-500 mb-1">Total Repaid</div><div class="text-base font-black text-white">Ksh <span x-text="fmtK(loan.totalPaid)"></span></div></div>
                            <div><div class="text-xs text-gray-500 mb-1">Total Interest</div><div class="text-base font-black text-red-400">Ksh <span x-text="fmtK(loan.totalInterest)"></span></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════
             SAVINGS GROWTH CALC
        ════════════════════════════════ --}}
        <div x-show="activeTool==='calculator'" x-cloak class="space-y-5">
            <div class="tool-card p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 bg-emerald-500/15 border border-emerald-500/30 rounded-xl flex items-center justify-center text-xl">💰</div>
                    <div><h3 class="font-black text-sm">Savings Growth Calculator</h3><p class="text-xs text-gray-500">See how regular savings grow with compound interest</p></div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4 mb-5">
                    <div><span class="lbl">Monthly Savings (Ksh)</span><input type="number" class="ifield" x-model.number="sav.monthly" min="0" placeholder="e.g. 2000" @input="calcSavings()"></div>
                    <div><span class="lbl">Annual Interest Rate (%)</span><input type="number" class="ifield" x-model.number="sav.rate" min="0" max="100" step="0.5" placeholder="e.g. 8" @input="calcSavings()"></div>
                    <div><span class="lbl">Duration (months)</span><input type="number" class="ifield" x-model.number="sav.months" min="1" max="600" placeholder="e.g. 24" @input="calcSavings()"></div>
                    <div><span class="lbl">Initial Deposit (Ksh)</span><input type="number" class="ifield" x-model.number="sav.initial" min="0" placeholder="Optional" @input="calcSavings()"></div>
                </div>
                <button class="calc-btn" @click="calcSavings()">Calculate</button>
                <div x-show="sav.result>0" x-cloak class="mt-5 result-box p-5 space-y-3">
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div><div class="text-xs text-gray-500 mb-1">Deposited</div><div class="text-base font-black text-white">Ksh <span x-text="fmtK(sav.totalDeposited)"></span></div></div>
                        <div><div class="text-xs text-gray-500 mb-1">Interest</div><div class="text-base font-black text-emerald-400">Ksh <span x-text="fmtK(sav.interestEarned)"></span></div></div>
                        <div><div class="text-xs text-gray-500 mb-1">Final</div><div class="text-xl font-black text-emerald-300">Ksh <span x-text="fmtK(sav.result)"></span></div></div>
                    </div>
                    <div class="pbar-bg h-3"><div class="pbar-fill h-3" :style="`width:${sav.depositPct}%`"></div></div>
                    <div class="flex justify-between text-[10px] text-gray-600"><span>Principal (<span x-text="sav.depositPct"></span>%)</span><span>Interest (<span x-text="100-sav.depositPct"></span>%)</span></div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════
             SAVINGS GOAL
        ════════════════════════════════ --}}
        <div x-show="activeTool==='goal'" x-cloak class="space-y-5">
            <div class="tool-card p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 bg-amber-500/15 border border-amber-500/30 rounded-xl flex items-center justify-center text-xl">🎯</div>
                    <div><h3 class="font-black text-sm">Savings Goal Calculator</h3><p class="text-xs text-gray-500">How long to reach your target?</p></div>
                </div>
                <div class="flex gap-3 mb-5">
                    <button class="flex-1 py-2 rounded-xl text-sm font-bold transition-all" :class="goal.mode==='time'?'bg-amber-500/20 border border-amber-500/40 text-amber-300':'bg-white/5 border border-white/10 text-gray-400'" @click="goal.mode='time';calcGoal()">⏱ How long?</button>
                    <button class="flex-1 py-2 rounded-xl text-sm font-bold transition-all" :class="goal.mode==='amount'?'bg-amber-500/20 border border-amber-500/40 text-amber-300':'bg-white/5 border border-white/10 text-gray-400'" @click="goal.mode='amount';calcGoal()">💸 How much/month?</button>
                </div>
                <div class="grid sm:grid-cols-2 gap-4 mb-5">
                    <div><span class="lbl">Goal (Ksh)</span><input type="number" class="ifield" x-model.number="goal.target" min="0" placeholder="e.g. 100000" @input="calcGoal()"></div>
                    <div><span class="lbl">Already Saved (Ksh)</span><input type="number" class="ifield" x-model.number="goal.saved" min="0" placeholder="0" @input="calcGoal()"></div>
                    <div x-show="goal.mode==='time'"><span class="lbl">Monthly Saving (Ksh)</span><input type="number" class="ifield" x-model.number="goal.monthly" min="0" placeholder="e.g. 5000" @input="calcGoal()"></div>
                    <div x-show="goal.mode==='amount'" x-cloak><span class="lbl">Target months</span><input type="number" class="ifield" x-model.number="goal.months" min="1" placeholder="e.g. 18" @input="calcGoal()"></div>
                    <div><span class="lbl">Annual Interest (%)</span><input type="number" class="ifield" x-model.number="goal.rate" min="0" max="100" step="0.5" placeholder="0" @input="calcGoal()"></div>
                </div>
                <button class="calc-btn" @click="calcGoal()">Calculate Goal</button>
                <div x-show="goal.answered" x-cloak class="mt-5 result-box amber p-5 space-y-3">
                    <div x-show="goal.mode==='time'" class="grid grid-cols-2 gap-3 text-center">
                        <div><div class="text-xs text-gray-500 mb-1">Months to Goal</div><div class="text-3xl font-black text-amber-300" x-text="goal.resultMonths"></div><div class="text-xs text-gray-500" x-text="Math.ceil(goal.resultMonths/12*10)/10+' years'"></div></div>
                        <div><div class="text-xs text-gray-500 mb-1">Completion Date</div><div class="text-lg font-black text-white" x-text="goal.targetDate"></div></div>
                    </div>
                    <div x-show="goal.mode==='amount'" x-cloak class="text-center">
                        <div class="text-xs text-gray-500 mb-1">Monthly Saving Needed</div>
                        <div class="text-3xl font-black text-amber-300">Ksh <span x-text="fmtK(goal.resultMonthly)"></span></div>
                    </div>
                    <div class="pbar-bg h-3"><div class="pbar-fill h-3 amber" :style="`width:${goal.progressPct}%`"></div></div>
                    <div class="text-xs text-gray-500 text-right">Progress: <span class="text-amber-300 font-bold" x-text="goal.progressPct+'%'"></span></div>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-xs text-gray-600">
            For educational purposes only. Consult a financial advisor for personal decisions.
            <br>Part of <span class="text-indigo-400">PesaQuest</span> by <span class="text-emerald-400">Moski</span>.
        </div>
    </div>

    <script>
    function smartTools() {
        return {
            activeTool: 'savings_schemes',

            // ── Savings Schemes ─────────────────────────────
            schemes: [],
            loadingSchemes: true,
            showNewScheme: false,
            creatingScheme: false,
            depositingId: null,
            depositAmount: 0,
            depositNote: '',
            addingDeposit: false,
            newScheme: { name:'', target:0, emoji:'💰', color:'#10b981' },

            async fetchSchemes() {
                this.loadingSchemes = true;
                try {
                    const r = await fetch('/savings', { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                    this.schemes = await r.json();
                } catch(e) {}
                this.loadingSchemes = false;
            },

            async createScheme() {
                if (!this.newScheme.name || !this.newScheme.target) return;
                this.creatingScheme = true;
                const r = await fetch('/savings', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ name: this.newScheme.name, target_amount: this.newScheme.target, emoji: this.newScheme.emoji, color: this.newScheme.color }),
                });
                const d = await r.json();
                if (d.scheme) { this.schemes.unshift(d.scheme); this.showNewScheme = false; this.newScheme = { name:'', target:0, emoji:'💰', color:'#10b981' }; }
                this.creatingScheme = false;
            },

            async addDeposit(schemeId) {
                if (!this.depositAmount || this.depositAmount <= 0) return;
                this.addingDeposit = true;
                const r = await fetch(`/savings/${schemeId}/deposit`, {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ amount: this.depositAmount, note: this.depositNote }),
                });
                const d = await r.json();
                if (d.scheme) {
                    const idx = this.schemes.findIndex(s => s.id===schemeId);
                    if (idx >= 0) this.schemes[idx] = d.scheme;
                    this.depositingId = null;
                }
                this.addingDeposit = false;
            },

            async archiveScheme(schemeId) {
                if (!confirm('Archive this savings scheme?')) return;
                await fetch(`/savings/${schemeId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' },
                });
                this.schemes = this.schemes.filter(s => s.id !== schemeId);
            },

            // ── Budget 50/30/20 ──────────────────────────────
            ratio: { needs:50, wants:30, savings:20 },

            budget: {
                income: [
                    { label:'Salary', type:'salary', amount:30000 },
                    { label:'Side Hustle', type:'side_hustle', amount:5000 },
                ],
                expenses: [
                    { label:'Rent', cat:'needs', amount:10000 },
                    { label:'Food', cat:'needs', amount:7000 },
                    { label:'Transport', cat:'needs', amount:3000 },
                    { label:'Airtime/Data', cat:'needs', amount:1500 },
                    { label:'Entertainment', cat:'wants', amount:3000 },
                    { label:'Eating out', cat:'wants', amount:2500 },
                    { label:'Emergency Fund', cat:'savings', amount:4000 },
                    { label:'Investment', cat:'savings', amount:2000 },
                ],
                totalIncome:0, totalExpenses:0, surplus:0, usagePct:0,
            },

            get buckets() {
                const inc = this.budget.totalIncome;
                return [
                    { cat:'needs',   label:'🏠 Needs',   color:'#818cf8', spent:this.categoryTotal('needs'),   target:this.categoryTarget('needs') },
                    { cat:'wants',   label:'🎮 Wants',   color:'#fbbf24', spent:this.categoryTotal('wants'),   target:this.categoryTarget('wants') },
                    { cat:'savings', label:'💰 Savings', color:'#34d399', spent:this.categoryTotal('savings'), target:this.categoryTarget('savings') },
                ];
            },

            categoryTotal(cat) { return this.budget.expenses.filter(e=>e.cat===cat).reduce((s,e)=>s+(e.amount||0),0); },
            categoryTarget(cat) { return Math.round(this.budget.totalIncome * (this.ratio[cat]||0) / 100); },

            clampRatio(changed) {
                const total = (this.ratio.needs||0) + (this.ratio.wants||0) + (this.ratio.savings||0);
                const diff = 100 - total;
                if (changed === 'needs') this.ratio.savings = Math.max(0, this.ratio.savings + diff);
                else if (changed === 'wants') this.ratio.savings = Math.max(0, this.ratio.savings + diff);
                else this.ratio.savings = Math.max(0, 100 - (this.ratio.needs||0) - (this.ratio.wants||0));
                this.calcBudget();
            },

            resetRatio() { this.ratio = { needs:50, wants:30, savings:20 }; this.calcBudget(); },

            addIncomeRow() { this.budget.income.push({ label:'', type:'other', amount:0 }); this.calcBudget(); },
            removeIncomeRow(i) { this.budget.income.splice(i,1); this.calcBudget(); },
            addExpenseRow(cat) { this.budget.expenses.push({ label:'', cat, amount:0 }); this.calcBudget(); },
            removeExpenseRow(cat,i) {
                const idx = this.budget.expenses.reduce((acc,e,ii)=>{if(e.cat===cat)acc.push(ii);return acc;},[]);
                if (idx[i] !== undefined) this.budget.expenses.splice(idx[i],1);
                this.calcBudget();
            },

            calcBudget() {
                const inc = this.budget.income.reduce((s,r)=>s+(r.amount||0),0);
                const exp = this.budget.expenses.reduce((s,r)=>s+(r.amount||0),0);
                this.budget.totalIncome   = inc;
                this.budget.totalExpenses = exp;
                this.budget.surplus       = inc - exp;
                this.budget.usagePct      = inc>0 ? Math.round(exp/inc*100) : 0;
            },

            // ── Savings Calc ─────────────────────────────────
            sav:{ monthly:2000, rate:8, months:24, initial:0, result:0, totalDeposited:0, interestEarned:0, depositPct:100 },
            calcSavings() {
                const P=this.sav.initial||0, pmt=this.sav.monthly||0, r=(this.sav.rate||0)/100/12, n=this.sav.months||0;
                if(n<=0) return;
                const fv=r===0 ? P+pmt*n : P*Math.pow(1+r,n)+pmt*((Math.pow(1+r,n)-1)/r);
                const dep=P+pmt*n;
                this.sav.result=Math.round(fv); this.sav.totalDeposited=Math.round(dep);
                this.sav.interestEarned=Math.round(fv-dep); this.sav.depositPct=Math.round(dep/fv*100);
            },

            // ── Loan ─────────────────────────────────────────
            loan:{ principal:50000, rate:13, months:12, type:'reducing', monthly:0, totalPaid:0, totalInterest:0 },
            calcLoan() {
                const P=this.loan.principal||0, n=this.loan.months||1, annualRate=(this.loan.rate||0)/100;
                let monthly,totalPaid;
                if(this.loan.type==='flat'){
                    const ti=P*annualRate*(n/12); totalPaid=P+ti; monthly=Math.round(totalPaid/n); this.loan.totalInterest=Math.round(ti);
                } else {
                    const r=annualRate/12;
                    if(r===0){monthly=Math.round(P/n);totalPaid=monthly*n;}
                    else{monthly=Math.round(P*r*Math.pow(1+r,n)/(Math.pow(1+r,n)-1));totalPaid=monthly*n;}
                    this.loan.totalInterest=Math.round(totalPaid-P);
                }
                this.loan.monthly=monthly; this.loan.totalPaid=Math.round(totalPaid);
            },

            // ── Goal ─────────────────────────────────────────
            goal:{ target:100000, saved:10000, monthly:5000, months:18, rate:0, mode:'time', answered:false, resultMonths:0, resultMonthly:0, targetDate:'', progressPct:0 },
            calcGoal() {
                const rem=Math.max(0,(this.goal.target||0)-(this.goal.saved||0)), r=(this.goal.rate||0)/100/12;
                this.goal.progressPct=this.goal.target>0?Math.min(100,Math.round((this.goal.saved||0)/this.goal.target*100)):0;
                if(this.goal.mode==='time'){
                    const pmt=this.goal.monthly||0; if(pmt<=0) return;
                    let months=r===0?Math.ceil(rem/pmt):Math.ceil(Math.log(1+rem*r/pmt)/Math.log(1+r));
                    if(!isFinite(months)||months<0) return;
                    this.goal.resultMonths=months;
                    const d=new Date(); d.setMonth(d.getMonth()+months);
                    this.goal.targetDate=d.toLocaleDateString('en-KE',{month:'short',year:'numeric'});
                } else {
                    const n=this.goal.months||0; if(n<=0) return;
                    this.goal.resultMonthly=r===0?Math.ceil(rem/n):Math.ceil(rem*r/(Math.pow(1+r,n)-1));
                }
                this.goal.answered=true;
            },

            fmtK(n){ return Math.round(n).toLocaleString('en-KE'); },

            init() {
                this.fetchSchemes();
                this.calcSavings(); this.calcLoan(); this.calcBudget(); this.calcGoal();
            },
        };
    }
    </script>
</body>
</html>
