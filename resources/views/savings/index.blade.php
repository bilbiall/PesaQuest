<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Savings — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }

        @keyframes shimmer {
            0%   { background-position: 0% 50% }
            50%  { background-position: 100% 50% }
            100% { background-position: 0% 50% }
        }
        @keyframes popIn {
            from { opacity:0; transform: translateY(18px) scale(0.97) }
            to   { opacity:1; transform: translateY(0)    scale(1)    }
        }
        @keyframes spin { to { transform: rotate(360deg) } }
        @keyframes barFill { from { width: 0 } }

        .shimmer-text {
            background: linear-gradient(90deg, #15C77E, #38bdf8, #a78bfa, #15C77E);
            background-size: 300% 300%;
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 4s ease infinite;
        }
        .scheme-card { animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1) both; }
        .scheme-card:nth-child(1){animation-delay:.04s} .scheme-card:nth-child(2){animation-delay:.08s}
        .scheme-card:nth-child(3){animation-delay:.12s} .scheme-card:nth-child(4){animation-delay:.16s}
        .scheme-card:nth-child(5){animation-delay:.20s} .scheme-card:nth-child(6){animation-delay:.24s}

        .progress-bar { animation: barFill 0.8s ease both; }
        .spinner {
            width:20px; height:20px;
            border:2px solid rgba(21,199,126,0.25);
            border-top-color:#15C77E;
            border-radius:50%;
            animation: spin 0.7s linear infinite;
            display:inline-block;
        }

        /* Custom scrollbar for deposit history */
        .deposit-list::-webkit-scrollbar { width: 4px; }
        .deposit-list::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); border-radius:4px; }
        .deposit-list::-webkit-scrollbar-thumb { background: rgba(21,199,126,0.3); border-radius:4px; }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Dashboard
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500 hidden sm:block">💰 Savings</span>
            <a href="{{ route('game.play') }}"
               class="text-xs text-emerald-400 hover:text-emerald-300 border border-emerald-500/30 hover:border-emerald-500/60 px-3 py-1.5 rounded-lg transition-colors">
                ▶ Play
            </a>
        </div>
    </div>
</nav>

{{-- Main Alpine component --}}
<div x-data="savingsApp()" x-init="init()" class="max-w-5xl mx-auto px-4 sm:px-6">

    {{-- Hero / header --}}
    <div class="relative overflow-hidden border-b border-white/5 -mx-4 sm:-mx-6 px-4 sm:px-6 py-10 mb-8"
         style="background:linear-gradient(160deg,rgba(21,199,126,0.07) 0%,rgba(56,189,248,0.04) 100%);">
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full opacity-5 pointer-events-none"
             style="background:radial-gradient(circle,#15C77E,transparent 70%);transform:translate(30%,-30%);"></div>

        <div class="relative">
            <h1 class="text-3xl sm:text-4xl font-black shimmer-text mb-1">💰 My Savings</h1>
            <p class="text-gray-400 text-sm mb-6">Track your goals and watch your money grow</p>

            {{-- Summary stats --}}
            <div class="flex flex-wrap gap-3" x-show="!loading">
                <div class="rounded-2xl px-5 py-3 flex items-center gap-3"
                     style="background:rgba(21,199,126,0.08);border:1px solid rgba(21,199,126,0.2);">
                    <span class="text-2xl">🏦</span>
                    <div>
                        <div class="text-xl font-black text-emerald-400" x-text="'KSh ' + totalSaved.toLocaleString()">—</div>
                        <div class="text-xs text-gray-400">Total Saved</div>
                    </div>
                </div>
                <div class="rounded-2xl px-5 py-3 flex items-center gap-3"
                     style="background:rgba(56,189,248,0.08);border:1px solid rgba(56,189,248,0.2);">
                    <span class="text-2xl">🎯</span>
                    <div>
                        <div class="text-xl font-black text-sky-400" x-text="schemes.length">—</div>
                        <div class="text-xs text-gray-400">Active Goals</div>
                    </div>
                </div>
                <div class="rounded-2xl px-5 py-3 flex items-center gap-3"
                     style="background:rgba(167,139,250,0.08);border:1px solid rgba(167,139,250,0.2);">
                    <span class="text-2xl">✅</span>
                    <div>
                        <div class="text-xl font-black text-violet-400" x-text="completedCount">—</div>
                        <div class="text-xs text-gray-400">Completed</div>
                    </div>
                </div>
            </div>

            {{-- New scheme button --}}
            <button @click="showNewForm = !showNewForm"
                    x-show="!loading"
                    class="mt-6 flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all hover:scale-[1.03] active:scale-[0.97]"
                    style="background:linear-gradient(135deg,rgba(21,199,126,0.2),rgba(21,199,126,0.12));border:1px solid rgba(21,199,126,0.4);color:#15C77E;">
                <span x-text="showNewForm ? '✕ Cancel' : '＋ New Scheme'"></span>
            </button>
        </div>
    </div>

    {{-- Loading state --}}
    <div x-show="loading" x-cloak class="flex flex-col items-center justify-center py-20 gap-4">
        <div class="spinner"></div>
        <p class="text-gray-500 text-sm">Loading your savings…</p>
    </div>

    {{-- New Scheme form --}}
    <div x-show="showNewForm" x-cloak x-transition
         class="mb-8 rounded-3xl p-6"
         style="background:rgba(21,199,126,0.05);border:1px solid rgba(21,199,126,0.25);">

        <h2 class="text-lg font-black text-emerald-400 mb-5">🆕 Create a New Savings Goal</h2>

        <div class="grid sm:grid-cols-2 gap-4">
            {{-- Name --}}
            <div class="sm:col-span-2">
                <label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1.5 block">Goal Name</label>
                <input type="text" x-model="newScheme.name" placeholder="e.g. School Trip, New Phone…"
                       maxlength="60"
                       class="w-full px-4 py-3 rounded-xl text-white text-sm font-medium placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                       style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
            </div>

            {{-- Target Amount --}}
            <div>
                <label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1.5 block">Target Amount (KSh)</label>
                <input type="number" x-model.number="newScheme.target_amount" placeholder="5000" min="1"
                       class="w-full px-4 py-3 rounded-xl text-white text-sm font-medium placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                       style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
            </div>

            {{-- Emoji --}}
            <div>
                <label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1.5 block">Emoji</label>
                <input type="text" x-model="newScheme.emoji" placeholder="💰" maxlength="4"
                       class="w-full px-4 py-3 rounded-xl text-white text-sm font-medium placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                       style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                {{-- Quick emoji picks --}}
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <template x-for="e in ['🏠','📱','✈️','🎓','🚗','💻','👟','🎮','🏖️','🎁']">
                        <button type="button" @click="newScheme.emoji = e"
                                :class="newScheme.emoji === e ? 'ring-2 ring-emerald-400' : ''"
                                class="text-lg p-1 rounded-lg hover:bg-white/10 transition" x-text="e"></button>
                    </template>
                </div>
            </div>

            {{-- Color --}}
            <div class="sm:col-span-2">
                <label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1.5 block">Accent Color</label>
                <div class="flex flex-wrap gap-2">
                    <template x-for="c in colorOptions">
                        <button type="button" @click="newScheme.color = c.value"
                                :style="'background:' + c.value"
                                :class="newScheme.color === c.value ? 'ring-2 ring-white ring-offset-2 ring-offset-black scale-110' : ''"
                                class="w-7 h-7 rounded-full transition-all hover:scale-110" :title="c.label"></button>
                    </template>
                </div>
            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button @click="createScheme()"
                    :disabled="saving || !newScheme.name || !newScheme.target_amount"
                    class="flex items-center gap-2 px-6 py-2.5 rounded-xl font-black text-sm transition-all hover:scale-[1.03] active:scale-[0.97] disabled:opacity-50 disabled:cursor-not-allowed"
                    style="background:linear-gradient(135deg,#15C77E,#0fa864);color:#07060f;">
                <span x-show="!saving">Create Goal</span>
                <span x-show="saving" x-cloak class="flex items-center gap-2"><span class="spinner" style="border-top-color:#07060f;border-color:rgba(7,6,15,0.3)"></span> Saving…</span>
            </button>
            <button @click="showNewForm = false" class="text-sm text-gray-500 hover:text-gray-300 transition">Cancel</button>
        </div>
    </div>

    {{-- Schemes grid --}}
    <div x-show="!loading" class="pb-16">

        {{-- Empty state --}}
        <div x-show="schemes.length === 0" x-cloak class="text-center py-20">
            <div class="text-7xl mb-5">🪙</div>
            <p class="text-gray-300 font-black text-xl mb-2">No savings goals yet</p>
            <p class="text-gray-500 text-sm mb-6">Create your first goal to start tracking your progress</p>
            <button @click="showNewForm = true"
                    class="px-6 py-3 rounded-xl font-black text-sm transition-all hover:scale-[1.03]"
                    style="background:rgba(21,199,126,0.15);border:1px solid rgba(21,199,126,0.4);color:#15C77E;">
                ＋ Create My First Goal
            </button>
        </div>

        {{-- Cards --}}
        <div x-show="schemes.length > 0" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(scheme, idx) in schemes" :key="scheme.id">
                <div class="scheme-card rounded-3xl p-5 flex flex-col gap-4 relative overflow-hidden"
                     :style="cardStyle(scheme)">

                    {{-- Decorative glow blob --}}
                    <div class="absolute top-0 right-0 w-32 h-32 rounded-full pointer-events-none opacity-10"
                         :style="'background:radial-gradient(circle,' + (scheme.color || '#15C77E') + ',transparent 70%);transform:translate(30%,-30%)'"></div>

                    {{-- Card header --}}
                    <div class="flex items-start justify-between gap-2 relative">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl" x-text="scheme.emoji || '💰'"></span>
                            <div>
                                <p class="font-black text-white leading-tight text-sm" x-text="scheme.name"></p>
                                <p class="text-[10px] text-gray-500 mt-0.5"
                                   x-text="scheme.deposit_count + ' deposit' + (scheme.deposit_count !== 1 ? 's' : '')"></p>
                            </div>
                        </div>
                        {{-- Completed badge --}}
                        <span x-show="scheme.progress_pct >= 100"
                              class="shrink-0 text-[10px] font-black px-2 py-1 rounded-full"
                              style="background:rgba(21,199,126,0.2);border:1px solid rgba(21,199,126,0.4);color:#15C77E;">
                            ✓ Done!
                        </span>
                    </div>

                    {{-- Amounts --}}
                    <div class="relative">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-wider font-bold">Saved</div>
                                <div class="text-xl font-black text-white"
                                     x-text="'KSh ' + Number(scheme.current_amount).toLocaleString()"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500 uppercase tracking-wider font-bold">Target</div>
                                <div class="text-sm font-bold text-gray-300"
                                     x-text="'KSh ' + Number(scheme.target_amount).toLocaleString()"></div>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="h-2.5 rounded-full overflow-hidden"
                             style="background:rgba(255,255,255,0.07);">
                            <div class="h-full rounded-full progress-bar transition-all duration-700"
                                 :style="'width:' + Math.min(100, scheme.progress_pct) + '%;background:' + (scheme.color || '#15C77E') + ';box-shadow:0 0 8px ' + (scheme.color || '#15C77E') + '60'">
                            </div>
                        </div>
                        <div class="flex justify-between mt-1.5">
                            <span class="text-[11px] font-bold"
                                  :style="'color:' + (scheme.color || '#15C77E')"
                                  x-text="scheme.progress_pct + '%'"></span>
                            <span class="text-[10px] text-gray-600"
                                  x-show="scheme.estimated_date"
                                  x-text="'Est. ' + scheme.estimated_date"></span>
                        </div>
                    </div>

                    {{-- Bank breakdown: deposits vs interest --}}
                    <div class="rounded-xl px-3 py-2 flex items-center justify-between gap-2"
                         style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.18);">
                        <div class="min-w-0">
                            <div class="text-[9px] text-gray-500 font-black uppercase tracking-widest">Deposited</div>
                            <div class="text-[11px] font-bold text-gray-300" x-text="'KSh ' + Number(scheme.total_deposited ?? 0).toLocaleString()"></div>
                        </div>
                        <div class="min-w-0 text-right">
                            <div class="text-[9px] text-amber-400/80 font-black uppercase tracking-widest">✨ Interest earned</div>
                            <div class="text-[11px] font-black text-amber-300"
                                 x-text="'KSh ' + Number(scheme.interest_earned ?? 0).toLocaleString() + ' · ' + (scheme.interest_rate ?? 8) + '% p.a.'"></div>
                        </div>
                    </div>

                    {{-- Account history (collapsible) --}}
                    <div x-show="scheme.deposits && scheme.deposits.length > 0">
                        <button @click="toggleDeposits(scheme.id)"
                                class="text-[10px] text-gray-500 hover:text-gray-300 transition flex items-center gap-1 font-bold uppercase tracking-wider">
                            <span x-text="openDeposits.includes(scheme.id) ? '▲' : '▼'"></span>
                            Account history
                        </button>
                        <div x-show="openDeposits.includes(scheme.id)" x-cloak x-transition
                             class="mt-2 max-h-28 overflow-y-auto deposit-list rounded-xl divide-y divide-white/5"
                             style="background:rgba(0,0,0,0.25);">
                            <template x-for="dep in scheme.deposits" :key="dep.date + dep.amount + dep.type">
                                <div class="flex items-center justify-between px-3 py-2">
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold"
                                              :class="dep.type === 'withdrawal' ? 'text-red-400' : (dep.type === 'interest' ? 'text-amber-300' : 'text-white')"
                                              x-text="(Number(dep.amount) >= 0 ? '+' : '−') + 'KSh ' + Math.abs(Number(dep.amount)).toLocaleString()"></span>
                                        <span class="text-[9px] font-black uppercase tracking-wider ml-1.5"
                                              :class="dep.type === 'withdrawal' ? 'text-red-400/70' : (dep.type === 'interest' ? 'text-amber-400/70' : 'text-gray-600')"
                                              x-text="dep.type === 'interest' ? '✨ interest' : (dep.type === 'withdrawal' ? 'withdrawal' : 'deposit')"></span>
                                        <span x-show="dep.note && dep.type === 'deposit'" class="text-[10px] text-gray-500 ml-1.5" x-text="dep.note"></span>
                                    </div>
                                    <span class="text-[10px] text-gray-600 flex-shrink-0" x-text="dep.date"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex items-center gap-2 mt-auto pt-1">
                        {{-- Deposit button --}}
                        <button x-show="scheme.progress_pct < 100"
                                @click="openDepositModal(scheme)"
                                class="flex-1 py-2 rounded-xl text-xs font-black transition-all hover:scale-[1.03] active:scale-[0.97]"
                                :style="'background:' + (scheme.color || '#15C77E') + '22;border:1px solid ' + (scheme.color || '#15C77E') + '55;color:' + (scheme.color || '#15C77E')">
                            ＋ Deposit
                        </button>

                        {{-- Withdraw button --}}
                        <button x-show="Number(scheme.current_amount) > 0"
                                @click="openWithdrawModal(scheme)"
                                class="flex-1 py-2 rounded-xl text-xs font-black text-sky-300 transition-all hover:scale-[1.03] active:scale-[0.97]"
                                style="background:rgba(56,189,248,0.08);border:1px solid rgba(56,189,248,0.3);">
                            − Withdraw
                        </button>

                        {{-- Archive button --}}
                        <button @click="archiveScheme(scheme)"
                                class="px-3 py-2 rounded-xl text-xs font-medium text-gray-600 hover:text-red-400 transition-colors"
                                style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);"
                                title="Archive goal">
                            🗑
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Deposit Modal --}}
    <div x-show="depositModal.open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click.self="depositModal.open = false"
         class="fixed inset-0 flex items-center justify-center p-4"
         style="z-index:9990;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);overflow-y:auto;overscroll-behavior:contain;">

        <div class="w-full max-w-sm rounded-3xl p-6 my-auto"
             style="background:#0d1117;border:1px solid rgba(21,199,126,0.25);"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl" x-text="depositModal.scheme?.emoji || '💰'"></span>
                <div>
                    <h3 class="font-black text-white" x-text="depositModal.scheme?.name"></h3>
                    <p class="text-xs text-gray-500">Add a deposit to this goal</p>
                </div>
            </div>

            {{-- Amount --}}
            <div class="mb-4">
                <label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1.5 block">Amount (KSh)</label>
                <input type="number" x-model.number="depositModal.amount" placeholder="500" min="1"
                       @keyup.enter="submitDeposit()"
                       class="w-full px-4 py-3 rounded-xl text-white text-sm font-medium placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                       style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                {{-- Quick amounts --}}
                <div class="flex gap-2 mt-2">
                    <template x-for="qa in [100, 500, 1000, 2000]">
                        <button type="button" @click="depositModal.amount = qa"
                                :class="depositModal.amount === qa ? 'border-emerald-500/60 text-emerald-400' : 'border-white/10 text-gray-400'"
                                class="flex-1 py-1.5 rounded-lg text-[11px] font-bold border transition hover:border-emerald-500/40 hover:text-emerald-400"
                                x-text="qa.toLocaleString()"></button>
                    </template>
                </div>
            </div>

            {{-- Note --}}
            <div class="mb-5">
                <label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1.5 block">Note (optional)</label>
                <input type="text" x-model="depositModal.note" placeholder="e.g. Weekly savings" maxlength="120"
                       @keyup.enter="submitDeposit()"
                       class="w-full px-4 py-3 rounded-xl text-white text-sm font-medium placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition"
                       style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
            </div>

            <div class="flex gap-3">
                <button @click="submitDeposit()"
                        :disabled="depositing || !depositModal.amount"
                        class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl font-black text-sm transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                        style="background:linear-gradient(135deg,#15C77E,#0fa864);color:#07060f;">
                    <span x-show="!depositing">💰 Deposit</span>
                    <span x-show="depositing" x-cloak class="flex items-center gap-2">
                        <span class="spinner" style="border-top-color:#07060f;border-color:rgba(7,6,15,0.3);width:16px;height:16px;border-width:2px;"></span>
                        Saving…
                    </span>
                </button>
                <button @click="depositModal.open = false"
                        class="px-5 py-3 rounded-xl text-sm text-gray-400 hover:text-white transition font-medium"
                        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- Withdraw Modal --}}
    <div x-show="withdrawModal.open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click.self="withdrawModal.open = false"
         class="fixed inset-0 flex items-center justify-center p-4"
         style="z-index:9990;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);overflow-y:auto;overscroll-behavior:contain;">

        <div class="w-full max-w-sm rounded-3xl p-6 my-auto"
             style="background:#0d1117;border:1px solid rgba(56,189,248,0.3);"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-8"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl" x-text="withdrawModal.scheme?.emoji || '💰'"></span>
                <div>
                    <h3 class="font-black text-white" x-text="withdrawModal.scheme?.name"></h3>
                    <p class="text-xs text-gray-500">
                        Available: <span class="text-sky-300 font-bold" x-text="'KSh ' + Number(withdrawModal.scheme?.current_amount ?? 0).toLocaleString()"></span>
                    </p>
                </div>
            </div>

            {{-- Amount --}}
            <div class="mb-3">
                <label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1.5 block">Amount to withdraw (KSh)</label>
                <input type="number" x-model.number="withdrawModal.amount" placeholder="500" min="1"
                       :max="withdrawModal.scheme?.current_amount"
                       @keyup.enter="submitWithdraw()"
                       class="w-full px-4 py-3 rounded-xl text-white text-sm font-medium placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 transition"
                       style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);">
                {{-- Quick fractions --}}
                <div class="flex gap-2 mt-2">
                    <template x-for="frac in [[25,'25%'],[50,'50%'],[100,'All']]">
                        <button type="button"
                                @click="withdrawModal.amount = Math.floor(Number(withdrawModal.scheme?.current_amount ?? 0) * frac[0] / 100)"
                                class="flex-1 py-1.5 rounded-lg text-[11px] font-bold border border-white/10 text-gray-400 transition hover:border-sky-500/40 hover:text-sky-300"
                                x-text="frac[1]"></button>
                    </template>
                </div>
            </div>

            <p class="text-[11px] text-amber-300/80 leading-snug rounded-xl px-3 py-2 mb-4"
               style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.18);">
                💡 The money goes back to your wallet — but it stops earning interest, and your goal moves further away. Withdraw only what you need.
            </p>

            <div class="flex gap-3">
                <button @click="submitWithdraw()"
                        :disabled="withdrawing || !withdrawModal.amount"
                        class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl font-black text-sm transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                        style="background:linear-gradient(135deg,#38bdf8,#0284c7);color:#07060f;">
                    <span x-show="!withdrawing">🏧 Withdraw to Wallet</span>
                    <span x-show="withdrawing" x-cloak class="flex items-center gap-2">
                        <span class="spinner" style="border-top-color:#07060f;border-color:rgba(7,6,15,0.3);width:16px;height:16px;border-width:2px;"></span>
                        Processing…
                    </span>
                </button>
                <button @click="withdrawModal.open = false"
                        class="px-5 py-3 rounded-xl text-sm text-gray-400 hover:text-white transition font-medium"
                        style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-cloak x-transition
         class="fixed bottom-6 left-1/2 -translate-x-1/2 px-5 py-3 rounded-2xl text-sm font-bold z-50 whitespace-nowrap"
         :style="toast.ok
             ? 'background:rgba(21,199,126,0.95);color:#07060f;'
             : 'background:rgba(239,68,68,0.9);color:white;'"
         x-text="toast.msg">
    </div>

</div>

@include('components.mama-pesa-chat')

<script>
function savingsApp() {
    return {
        loading: true,
        saving: false,
        depositing: false,
        schemes: [],
        showNewForm: false,
        openDeposits: [],
        toast: { show: false, ok: true, msg: '' },

        newScheme: { name: '', target_amount: '', emoji: '💰', color: '#15C77E' },

        depositModal: {
            open: false,
            scheme: null,
            amount: '',
            note: '',
        },

        withdrawing: false,
        withdrawModal: {
            open: false,
            scheme: null,
            amount: '',
        },

        colorOptions: [
            { label: 'Emerald',  value: '#15C77E' },
            { label: 'Sky',      value: '#38bdf8' },
            { label: 'Violet',   value: '#a78bfa' },
            { label: 'Amber',    value: '#f59e0b' },
            { label: 'Rose',     value: '#fb7185' },
            { label: 'Orange',   value: '#fb923c' },
            { label: 'Teal',     value: '#2dd4bf' },
            { label: 'Indigo',   value: '#818cf8' },
        ],

        get totalSaved() {
            return this.schemes.reduce((s, x) => s + Number(x.current_amount), 0);
        },
        get completedCount() {
            return this.schemes.filter(s => s.progress_pct >= 100).length;
        },

        async init() {
            await this.loadSchemes();
        },

        async loadSchemes() {
            this.loading = true;
            try {
                const res = await fetch('/savings', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                if (!res.ok) throw new Error('Failed to load');
                this.schemes = await res.json();
            } catch (e) {
                this.showToast('Could not load savings. Please refresh.', false);
            } finally {
                this.loading = false;
            }
        },

        cardStyle(scheme) {
            const c = scheme.color || '#15C77E';
            const hex = c.replace('#', '');
            const r = parseInt(hex.slice(0, 2), 16);
            const g = parseInt(hex.slice(2, 4), 16);
            const b = parseInt(hex.slice(4, 6), 16);
            return `background:rgba(${r},${g},${b},0.05);border:1px solid rgba(${r},${g},${b},0.2);`;
        },

        toggleDeposits(id) {
            if (this.openDeposits.includes(id)) {
                this.openDeposits = this.openDeposits.filter(x => x !== id);
            } else {
                this.openDeposits.push(id);
            }
        },

        openDepositModal(scheme) {
            this.depositModal = { open: true, scheme, amount: '', note: '' };
        },

        openWithdrawModal(scheme) {
            this.withdrawModal = { open: true, scheme, amount: '' };
        },

        async submitWithdraw() {
            if (!this.withdrawModal.amount || !this.withdrawModal.scheme) return;
            this.withdrawing = true;
            const id = this.withdrawModal.scheme.id;
            try {
                const res = await fetch(`/savings/${id}/withdraw`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ amount: this.withdrawModal.amount }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    const idx = this.schemes.findIndex(s => s.id === id);
                    if (idx > -1) this.schemes[idx] = data.scheme;
                    this.withdrawModal.open = false;
                    this.showToast('KSh ' + Number(data.withdrawn).toLocaleString() + ' sent to your wallet 🏧', true);
                } else {
                    this.showToast(data.error || 'Could not withdraw.', false);
                }
            } catch {
                this.showToast('Network error. Please try again.', false);
            } finally {
                this.withdrawing = false;
            }
        },

        async createScheme() {
            if (!this.newScheme.name || !this.newScheme.target_amount) return;
            this.saving = true;
            try {
                const res = await fetch('/savings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(this.newScheme),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.schemes.unshift(data.scheme);
                    this.newScheme = { name: '', target_amount: '', emoji: '💰', color: '#15C77E' };
                    this.showNewForm = false;
                    this.showToast('Goal created! 🎉', true);
                } else {
                    this.showToast(data.error || data.message || 'Could not create goal.', false);
                }
            } catch {
                this.showToast('Network error. Please try again.', false);
            } finally {
                this.saving = false;
            }
        },

        async submitDeposit() {
            if (!this.depositModal.amount || !this.depositModal.scheme) return;
            this.depositing = true;
            const id = this.depositModal.scheme.id;
            try {
                const res = await fetch(`/savings/${id}/deposit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        amount: this.depositModal.amount,
                        note: this.depositModal.note || null,
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    // Replace scheme in list with fresh payload
                    const idx = this.schemes.findIndex(s => s.id === id);
                    if (idx > -1) this.schemes[idx] = data.scheme;
                    this.depositModal.open = false;
                    this.showToast('Deposit saved! 💰', true);
                } else {
                    this.showToast(data.error || data.message || 'Could not save deposit.', false);
                }
            } catch {
                this.showToast('Network error. Please try again.', false);
            } finally {
                this.depositing = false;
            }
        },

        async archiveScheme(scheme) {
            if (!confirm(`Archive "${scheme.name}"? It will be hidden from your goals.`)) return;
            try {
                const res = await fetch(`/savings/${scheme.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.schemes = this.schemes.filter(s => s.id !== scheme.id);
                    this.showToast('Goal archived.', true);
                } else {
                    this.showToast('Could not archive goal.', false);
                }
            } catch {
                this.showToast('Network error. Please try again.', false);
            }
        },

        showToast(msg, ok = true) {
            this.toast = { show: true, ok, msg };
            setTimeout(() => this.toast.show = false, 3000);
        },
    };
}
</script>
<x-mobile-bottom-nav active="money" />
</body>
</html>
