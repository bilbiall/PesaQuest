<div class="max-w-[1400px] mx-auto px-3 sm:px-5 pb-8 mt-6" id="smart-tools">
    <div class="relative mb-7">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-white/5"></div></div>
        <div class="relative flex justify-center">
            <span class="px-5 text-xs text-gray-500 uppercase tracking-widest font-bold" style="background:#07060f;">🧰 Smart Money Tools</span>
        </div>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-black mb-1 text-white">Your Money Toolkit</h2>
            <p class="text-gray-400 text-sm max-w-xl leading-relaxed">Real-world financial tools that turn PesaQuest lessons into daily habits.</p>
        </div>
        @if($smartToolsUnlocked)
        <span class="flex-shrink-0 self-start inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full"
              style="background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">
            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background:#818cf8;"></span>
            Premium Feature — Unlocked
        </span>
        @else
        <span class="flex-shrink-0 self-start inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full"
              style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);color:#fcd34d;">
            💎 Premium Feature — Preview Only
        </span>
        @endif
    </div>

    {{-- Calculators are always visible — locked accounts see everything but can't type into
         them, with a clear unlock CTA. Your data in the Real-Life tools below is NEVER hidden,
         even if your subscription lapses — only adding/editing/deleting is gated. --}}
    <div class="relative">
        @unless($smartToolsUnlocked)
        <div class="absolute inset-0 z-10 flex items-center justify-center rounded-3xl" style="background:rgba(7,6,15,0.72);backdrop-filter:blur(2px);">
            <div class="text-center px-6">
                <div class="text-4xl mb-3">🔒</div>
                <p class="text-white font-black mb-1">Subscribe to interact with these calculators</p>
                <p class="text-gray-400 text-xs mb-4 max-w-xs mx-auto">You can see what's here — Premium unlocks typing your own numbers into them.</p>
                <a href="{{ route('pricing') }}" class="inline-block text-black font-black px-6 py-2.5 rounded-2xl text-sm transition-all hover:scale-105" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">💎 See Premium Plans</a>
            </div>
        </div>
        @endunless
        <div class="grid md:grid-cols-2 gap-4 {{ $smartToolsUnlocked ? '' : 'opacity-50 pointer-events-none select-none' }}">

        {{-- BAJETI SMART --}}
        <div x-data="bajetiTool()" x-init="load()" class="rounded-3xl overflow-hidden" style="background:linear-gradient(135deg,rgba(16,185,129,0.07),rgba(5,150,105,0.03));border:1px solid rgba(16,185,129,0.2);">
            <div class="p-5 cursor-pointer select-none" @click="open=!open">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:linear-gradient(135deg,rgba(16,185,129,0.25),rgba(5,150,105,0.15));border:1px solid rgba(16,185,129,0.3);">💼</div>
                        <div>
                            <div class="flex items-center gap-2"><h3 class="font-black text-white">Bajeti Smart</h3><span class="hidden sm:inline text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#34d399;">Budget Builder</span></div>
                            <p class="text-xs text-gray-400 mt-0.5">Split your income the smart way</p>
                        </div>
                    </div>
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center transition-transform" :class="open?'rotate-180':''" style="background:rgba(16,185,129,0.1);">
                        <svg class="w-4 h-4" style="color:#34d399;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3 leading-relaxed">Enter your monthly income and get a personalised 50/30/20 budget plan with Kenyan categories.</p>
            </div>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pb-5" style="border-top:1px solid rgba(16,185,129,0.12);">
                <div class="mt-4"><label class="text-xs font-bold uppercase tracking-widest mb-2 block" style="color:#6b7280;">Monthly Income</label>
                    <div class="relative"><span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold" style="color:#34d399;">Ksh</span>
                        <input type="number" x-model.number="income" placeholder="e.g. 50000" class="w-full pl-14 pr-4 py-3 rounded-xl text-white text-lg font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);">
                    </div>
                </div>
                <template x-if="income > 0">
                    <div>
                        <div class="mt-4 h-5 rounded-full overflow-hidden flex" style="gap:2px;">
                            <div class="transition-all duration-500 flex items-center justify-center text-[9px] font-black text-white" style="background:linear-gradient(90deg,#10b981,#059669);" :style="'width:'+needsPct+'%'"><span x-show="needsPct>=18" x-text="needsPct+'%'"></span></div>
                            <div class="transition-all duration-500 flex items-center justify-center text-[9px] font-black text-white" style="background:linear-gradient(90deg,#8b5cf6,#6366f1);" :style="'width:'+wantsPct+'%'"><span x-show="wantsPct>=12" x-text="wantsPct+'%'"></span></div>
                            <div class="transition-all duration-500 flex items-center justify-center text-[9px] font-black text-white" style="background:linear-gradient(90deg,#f59e0b,#d97706);" :style="'width:'+savingsPct+'%'"><span x-show="savingsPct>=10" x-text="savingsPct+'%'"></span></div>
                        </div>
                        <div class="flex justify-between text-[10px] font-bold mt-1"><span style="color:#34d399;">Needs</span><span style="color:#a78bfa;">Wants</span><span style="color:#fbbf24;">Savings</span></div>
                        <div class="grid grid-cols-3 gap-2 mt-3">
                            <div class="rounded-2xl p-3 text-center" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);"><div class="text-base mb-0.5">🏠</div><div class="text-xs font-bold mb-1" style="color:#34d399;">Needs</div><div class="text-sm font-black text-white" x-text="'Ksh '+fmt(Math.round(income*needsPct/100))"></div><input type="range" min="10" max="80" x-model.number="needsPct" @input="clamp()" class="w-full mt-1" style="accent-color:#10b981;height:3px;"></div>
                            <div class="rounded-2xl p-3 text-center" style="background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.2);"><div class="text-base mb-0.5">🎮</div><div class="text-xs font-bold mb-1" style="color:#a78bfa;">Wants</div><div class="text-sm font-black text-white" x-text="'Ksh '+fmt(Math.round(income*wantsPct/100))"></div><input type="range" min="0" max="60" x-model.number="wantsPct" @input="clamp()" class="w-full mt-1" style="accent-color:#8b5cf6;height:3px;"></div>
                            <div class="rounded-2xl p-3 text-center" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);"><div class="text-base mb-0.5">💰</div><div class="text-xs font-bold mb-1" style="color:#fbbf24;">Savings</div><div class="text-sm font-black text-white" x-text="'Ksh '+fmt(Math.round(income*savingsPct/100))"></div><input type="range" min="0" max="60" x-model.number="savingsPct" @input="clamp()" class="w-full mt-1" style="accent-color:#f59e0b;height:3px;"></div>
                        </div>
                        <div class="mt-4 rounded-2xl p-3 flex gap-3" style="background:rgba(99,102,241,0.07);border:1px solid rgba(99,102,241,0.2);"><span class="text-xl flex-shrink-0">💡</span><div><div class="text-xs font-bold mb-0.5" style="color:#a5b4fc;">PesaQuest Wisdom</div><div class="text-xs text-gray-400 leading-relaxed" x-text="wisdom()"></div></div></div>
                        <button @click="saveRatio()" :disabled="saving" class="w-full mt-3 py-2.5 rounded-xl text-sm font-bold transition-all disabled:opacity-50" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35);color:#34d399;">
                            <span x-show="!saving && !saved">💾 Save as My Default Split</span>
                            <span x-show="saving">Saving…</span>
                            <span x-show="saved">✓ Saved!</span>
                        </button>
                    </div>
                </template>
                <template x-if="!income || income <= 0"><div class="text-center py-8 text-gray-500 text-sm"><div class="text-4xl mb-2">💼</div>Enter your monthly income above to see your smart budget breakdown.</div></template>
            </div>
        </div>

        {{-- LENGO SAVER --}}
        <div x-data="lengoTool()" x-init="load()" class="rounded-3xl overflow-hidden" style="background:linear-gradient(135deg,rgba(99,102,241,0.07),rgba(139,92,246,0.03));border:1px solid rgba(99,102,241,0.2);">
            <div class="p-5 cursor-pointer select-none" @click="open=!open">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:linear-gradient(135deg,rgba(99,102,241,0.25),rgba(139,92,246,0.15));border:1px solid rgba(99,102,241,0.3);">🎯</div>
                        <div><div class="flex items-center gap-2 flex-wrap"><h3 class="font-black text-white">Lengo Saver</h3><span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">Multi-Goal</span></div><p class="text-xs text-gray-400 mt-0.5">Track multiple savings goals</p></div>
                    </div>
                    <div class="flex items-center gap-2"><span x-show="sessions.length" class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:rgba(99,102,241,0.15);color:#a5b4fc;" x-text="sessions.length+' goal'+(sessions.length!==1?'s':'')"></span>
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center transition-transform" :class="open?'rotate-180':''" style="background:rgba(99,102,241,0.1);"><svg class="w-4 h-4" style="color:#a5b4fc;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></div>
                    </div>
                </div>
            </div>
            <div x-show="open" x-cloak class="pb-5" style="border-top:1px solid rgba(99,102,241,0.12);">
                <div x-show="!activeId" class="px-5 pt-4">
                    <div x-show="!newOpen" class="flex items-center justify-between mb-3"><p class="text-xs text-gray-500">Your active savings goals</p><button @click="newOpen=true" class="text-xs font-bold px-3 py-1.5 rounded-xl" style="background:rgba(99,102,241,0.2);border:1px solid rgba(99,102,241,0.35);color:#a5b4fc;">+ New Goal</button></div>
                    <div x-show="newOpen" class="rounded-2xl p-4 mb-4 space-y-3" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.25);">
                        <p class="text-xs font-bold" style="color:#a5b4fc;">Create New Savings Goal</p>
                        <input type="text" x-model="nName" placeholder="What are you saving for?" class="w-full px-3 py-2 rounded-xl text-white text-sm focus:outline-none" style="background:rgba(0,0,0,0.3);border:1px solid rgba(99,102,241,0.2);">
                        <div class="grid grid-cols-2 gap-2">
                            <div><p class="text-xs mb-1" style="color:#6b7280;">Target (Ksh)</p><input type="number" x-model.number="nTarget" placeholder="30000" class="w-full px-3 py-2 rounded-xl text-white text-sm focus:outline-none" style="background:rgba(0,0,0,0.3);border:1px solid rgba(99,102,241,0.2);"></div>
                            <div><p class="text-xs mb-1" style="color:#6b7280;">Target Date</p><input type="date" x-model="nDate" class="w-full px-3 py-2 rounded-xl text-white text-sm focus:outline-none" style="background:rgba(0,0,0,0.3);border:1px solid rgba(99,102,241,0.2);"></div>
                        </div>
                        <div><p class="text-xs mb-1" style="color:#6b7280;">Already saved (Ksh)</p><input type="number" x-model.number="nAlready" placeholder="0" class="w-full px-3 py-2 rounded-xl text-white text-sm focus:outline-none" style="background:rgba(0,0,0,0.3);border:1px solid rgba(99,102,241,0.2);"></div>
                        <div class="flex gap-2"><button @click="addSession()" class="flex-1 py-2 rounded-xl text-sm font-bold" style="background:linear-gradient(135deg,#6366f1,#a78bfa);color:white;">Create Goal</button><button @click="newOpen=false;nName='';nTarget=null;nDate='';nAlready=0" class="px-4 py-2 rounded-xl text-sm" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#6b7280;">Cancel</button></div>
                    </div>
                    <div class="space-y-3">
                        <template x-if="sessions.length === 0"><div class="text-center py-8 text-gray-500 text-sm"><div class="text-5xl mb-3">🎯</div><p>No savings goals yet.</p><p class="text-xs mt-1">Create your first goal above.</p></div></template>
                        <template x-for="(s, si) in sessions" :key="s.id">
                            <div class="rounded-2xl p-4 cursor-pointer transition-all hover:scale-[1.01]" :style="sessionStyle(si)" @click="activeId = s.id">
                                <div class="flex items-center justify-between mb-2"><div><p class="font-bold text-white text-sm" x-text="s.name"></p><p class="text-xs mt-0.5" style="color:#9ca3af;"><span class="font-bold" x-text="'Ksh '+fmt(sessionSaved(s))"></span> of <span x-text="'Ksh '+fmt(s.target)"></span></p></div><div class="text-right"><p class="text-xl font-black text-white" x-text="sessionPct(s)+'%'"></p><p class="text-xs" style="color:#9ca3af;" x-text="s.logs.length+' log'+(s.logs.length!==1?'s':'')"></p></div></div>
                                <div class="h-1.5 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.08);"><div class="h-full rounded-full transition-all duration-700" :style="'width:'+sessionPct(s)+'%;background:'+sessionColor(si)"></div></div>
                                <div class="flex items-center justify-between mt-2"><span class="text-xs" style="color:#6b7280;" x-text="s.targetDate ? 'Due '+s.targetDate : 'No deadline'"></span><span x-show="sessionPct(s) >= 100" class="text-xs font-bold" style="color:#34d399;">🎉 Achieved!</span></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="activeId" class="px-5 pt-4">
                    <template x-if="activeSession()">
                        <div>
                            <div class="flex items-center justify-between mb-4"><button @click="activeId=null" class="text-xs flex items-center gap-1" style="color:#6b7280;"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>All Goals</button><button @click="deleteSession(activeSession()); activeId=null" class="text-xs px-2 py-1 rounded-lg" style="color:#ef4444;background:rgba(239,68,68,0.1);">Delete Goal</button></div>
                            <div class="flex items-center gap-5 mb-5">
                                <div class="relative flex-shrink-0" style="width:80px;height:80px;"><svg width="80" height="80" viewBox="0 0 90 90"><circle cx="45" cy="45" r="38" fill="none" stroke="rgba(99,102,241,0.12)" stroke-width="7"/><circle cx="45" cy="45" r="38" fill="none" stroke="url(#lGrad2)" stroke-width="7" stroke-linecap="round" :stroke-dasharray="2*Math.PI*38" :stroke-dashoffset="2*Math.PI*38*(1-(activePct()/100))" transform="rotate(-90 45 45)" style="transition:stroke-dashoffset 0.8s cubic-bezier(0.34,1.56,0.64,1)"/><defs><linearGradient id="lGrad2" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#a78bfa"/></linearGradient></defs></svg><div class="absolute inset-0 flex flex-col items-center justify-center"><div class="text-lg font-black text-white" x-text="activePct()+'%'"></div><div class="text-[9px]" style="color:#6b7280;">done</div></div></div>
                                <div class="flex-1"><p class="font-black text-white text-base" x-text="activeSession().name"></p><p class="text-sm mt-1" style="color:#9ca3af;"><span class="font-bold" style="color:#818cf8;" x-text="'Ksh '+fmt(activeSaved())"></span> of <span class="font-bold text-white" x-text="'Ksh '+fmt(activeSession().target)"></span></p><p x-show="activePct() >= 100" class="text-xs font-bold animate-pulse mt-1" style="color:#34d399;">🎉 Goal achieved!</p></div>
                            </div>
                            <div class="mb-4"><p class="text-xs font-bold mb-2" style="color:#6b7280;">LOG A CONTRIBUTION</p><div class="rounded-2xl p-4 space-y-3" style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.18);"><div class="grid grid-cols-2 gap-2"><div><p class="text-xs mb-1" style="color:#6b7280;">Amount (Ksh)</p><input type="number" x-model.number="logAmount" placeholder="500" class="w-full px-3 py-2 rounded-xl text-white text-sm font-bold focus:outline-none" style="background:rgba(0,0,0,0.3);border:1px solid rgba(99,102,241,0.2);"></div><div><p class="text-xs mb-1" style="color:#6b7280;">Date</p><input type="date" x-model="logDate" class="w-full px-3 py-2 rounded-xl text-white text-sm focus:outline-none" style="background:rgba(0,0,0,0.3);border:1px solid rgba(99,102,241,0.2);"></div></div><input type="text" x-model="logNote" placeholder="Note (e.g. From side hustle)" class="w-full px-3 py-2 rounded-xl text-white text-sm focus:outline-none" style="background:rgba(0,0,0,0.3);border:1px solid rgba(99,102,241,0.2);"><button @click="addLog()" :disabled="!logAmount" class="w-full py-2 rounded-xl text-sm font-bold transition-all disabled:opacity-40" style="background:linear-gradient(135deg,#6366f1,#a78bfa);color:white;">+ Log Savings</button></div></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- UKUAJI GROW --}}
        <div x-data="ukuajiTool()" class="rounded-3xl overflow-hidden" style="background:linear-gradient(135deg,rgba(245,158,11,0.07),rgba(251,191,36,0.03));border:1px solid rgba(245,158,11,0.2);">
            <div class="p-5 cursor-pointer select-none" @click="open=!open">
                <div class="flex items-center justify-between"><div class="flex items-center gap-3"><div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:linear-gradient(135deg,rgba(245,158,11,0.25),rgba(251,191,36,0.15));border:1px solid rgba(245,158,11,0.3);">📈</div><div><div class="flex items-center gap-2"><h3 class="font-black text-white">Ukuaji Grow</h3><span class="hidden sm:inline text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;">Wealth Calc</span></div><p class="text-xs text-gray-400 mt-0.5">Watch your savings compound</p></div></div><div class="w-7 h-7 rounded-xl flex items-center justify-center transition-transform" :class="open?'rotate-180':''" style="background:rgba(245,158,11,0.1);"><svg class="w-4 h-4" style="color:#fbbf24;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></div></div>
                <p class="text-xs text-gray-400 mt-3 leading-relaxed">See how Ksh 1,000/month grows into serious wealth. Compare M-Pesa, Bank FD, SACCO, T-Bills, and NSE stocks side by side.</p>
            </div>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pb-5" style="border-top:1px solid rgba(245,158,11,0.12);">
                <div class="mt-4 space-y-4"><div><label class="text-xs font-bold uppercase tracking-wider mb-1 block" style="color:#6b7280;">Monthly Savings Amount</label><div class="relative"><span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold" style="color:#fbbf24;">Ksh</span><input type="number" x-model.number="monthly" min="100" class="w-full pl-14 pr-4 py-3 rounded-xl text-white text-lg font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(245,158,11,0.3);"></div></div><div><div class="flex justify-between mb-1"><label class="text-xs font-bold uppercase tracking-wider" style="color:#6b7280;">Time Period</label><span class="text-xs font-black" style="color:#fbbf24;" x-text="years+' year'+(years!==1?'s':'')"></span></div><input type="range" min="1" max="20" x-model.number="years" class="w-full" style="accent-color:#f59e0b;"></div></div>
                <div class="mt-4 space-y-2"><div class="text-xs font-bold uppercase tracking-wider mb-2" style="color:#6b7280;">Growth by Investment Vehicle</div><template x-for="v in vehicles" :key="v.name"><div class="rounded-2xl p-3" :style="v.bg"><div class="flex items-center justify-between mb-2"><div class="flex items-center gap-2"><span x-text="v.icon" class="text-lg"></span><div><div class="text-sm font-bold text-white" x-text="v.name"></div><div class="text-[10px] text-gray-400" x-text="v.rate+'% p.a. (est.)'"></div></div></div><div class="text-right"><div class="text-lg font-black" :style="'color:'+v.color" x-text="fmtVal(fv(v.rate))"></div></div></div><div class="h-1.5 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.05);"><div class="h-full rounded-full transition-all duration-700" :style="'width:'+barW(v.rate)+';background:'+v.color+';'"></div></div></div></template></div>
            </div>
        </div>

        {{-- MKOPO PLANNER --}}
        <div x-data="mkopoTool()" class="rounded-3xl overflow-hidden" style="background:linear-gradient(135deg,rgba(239,68,68,0.07),rgba(220,38,38,0.03));border:1px solid rgba(239,68,68,0.2);">
            <div class="p-5 cursor-pointer select-none" @click="open=!open"><div class="flex items-center justify-between"><div class="flex items-center gap-3"><div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:linear-gradient(135deg,rgba(239,68,68,0.25),rgba(220,38,38,0.15));border:1px solid rgba(239,68,68,0.3);">🏦</div><div><div class="flex items-center gap-2"><h3 class="font-black text-white">Mkopo Planner</h3><span class="hidden sm:inline text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#fca5a5;">Loan Calc</span></div><p class="text-xs text-gray-400 mt-0.5">See your true loan cost before you borrow</p></div></div><div class="w-7 h-7 rounded-xl flex items-center justify-center transition-transform" :class="open?'rotate-180':''" style="background:rgba(239,68,68,0.1);"><svg class="w-4 h-4" style="color:#f87171;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></div></div></div>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pb-5" style="border-top:1px solid rgba(239,68,68,0.12);">
                <div class="mt-4 grid sm:grid-cols-3 gap-3"><div><label class="text-xs font-bold uppercase tracking-widest mb-2 block" style="color:#6b7280;">Loan Amount (Ksh)</label><input type="number" x-model.number="principal" min="1000" placeholder="e.g. 100000" class="w-full px-4 py-3 rounded-xl text-white font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(239,68,68,0.25);"></div><div><label class="text-xs font-bold uppercase tracking-widest mb-2 block" style="color:#6b7280;">Annual Rate (%)</label><input type="number" x-model.number="rate" min="0.1" max="200" step="0.1" placeholder="e.g. 14" class="w-full px-4 py-3 rounded-xl text-white font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(239,68,68,0.25);"></div><div><label class="text-xs font-bold uppercase tracking-widest mb-2 block" style="color:#6b7280;">Months</label><input type="number" x-model.number="months" min="1" max="360" placeholder="e.g. 24" class="w-full px-4 py-3 rounded-xl text-white font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(239,68,68,0.25);"></div></div>
                <template x-if="principal > 0 && rate > 0 && months > 0"><div class="mt-4 grid grid-cols-3 gap-3"><div class="rounded-2xl p-4 text-center" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);"><div class="text-[10px] font-black uppercase tracking-wider mb-1" style="color:#f87171;">Monthly Payment</div><div class="text-lg font-black text-white" x-text="'Ksh '+fmt(monthlyPayment())"></div></div><div class="rounded-2xl p-4 text-center" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);"><div class="text-[10px] font-black uppercase tracking-wider mb-1" style="color:#9ca3af;">Total Repaid</div><div class="text-lg font-black text-white" x-text="'Ksh '+fmt(monthlyPayment()*months)"></div></div><div class="rounded-2xl p-4 text-center" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);"><div class="text-[10px] font-black uppercase tracking-wider mb-1" style="color:#fbbf24;">Total Interest</div><div class="text-lg font-black text-yellow-400" x-text="'Ksh '+fmt(monthlyPayment()*months - principal)"></div></div></div></template>
            </div>
        </div>

        {{-- FAIDA COMPOUNDER --}}
        <div x-data="faidaTool()" class="rounded-3xl overflow-hidden" style="background:linear-gradient(135deg,rgba(99,102,241,0.07),rgba(139,92,246,0.03));border:1px solid rgba(99,102,241,0.2);">
            <div class="p-5 cursor-pointer select-none" @click="open=!open"><div class="flex items-center justify-between"><div class="flex items-center gap-3"><div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:linear-gradient(135deg,rgba(99,102,241,0.25),rgba(139,92,246,0.15));border:1px solid rgba(99,102,241,0.3);">💹</div><div><div class="flex items-center gap-2"><h3 class="font-black text-white">Faida Compounder</h3><span class="hidden sm:inline text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;">Compound Interest</span></div><p class="text-xs text-gray-400 mt-0.5">The 8th wonder of the world — visualised</p></div></div><div class="w-7 h-7 rounded-xl flex items-center justify-center transition-transform" :class="open?'rotate-180':''" style="background:rgba(99,102,241,0.1);"><svg class="w-4 h-4" style="color:#a5b4fc;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></div></div></div>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-5 pb-5" style="border-top:1px solid rgba(99,102,241,0.12);">
                <div class="mt-4 grid sm:grid-cols-3 gap-3"><div><label class="text-xs font-bold uppercase tracking-widest mb-2 block" style="color:#6b7280;">Initial Amount (Ksh)</label><input type="number" x-model.number="principal" min="0" placeholder="e.g. 50000" class="w-full px-4 py-3 rounded-xl text-white font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(99,102,241,0.25);"></div><div><label class="text-xs font-bold uppercase tracking-widest mb-2 block" style="color:#6b7280;">Monthly Addition (Ksh)</label><input type="number" x-model.number="monthly" min="0" placeholder="e.g. 5000" class="w-full px-4 py-3 rounded-xl text-white font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(99,102,241,0.25);"></div><div><label class="text-xs font-bold uppercase tracking-widest mb-2 block" style="color:#6b7280;">Years</label><input type="number" x-model.number="years" min="1" max="30" placeholder="e.g. 10" class="w-full px-4 py-3 rounded-xl text-white font-black focus:outline-none" style="background:rgba(0,0,0,0.35);border:1px solid rgba(99,102,241,0.25);"></div></div>
                <template x-if="(principal > 0 || monthly > 0) && years > 0"><div class="mt-4 space-y-2"><template x-for="opt in options()" :key="opt.label"><div class="rounded-2xl px-4 py-3 flex items-center justify-between" :style="'background:'+opt.bg+';border:1px solid '+opt.border+';'"><div><span class="text-sm font-black" :style="'color:'+opt.color" x-text="opt.label"></span><span class="text-xs text-gray-500 ml-2" x-text="opt.rate+'% p.a.'"></span></div><div class="text-right"><div class="font-black text-white" x-text="'Ksh '+fmt(compound(opt.rate))"></div><div class="text-[10px]" :style="'color:'+opt.color" x-text="'+Ksh '+fmt(compound(opt.rate)-(principal+(monthly*years*12)))"></div></div></div></template></div></template>
            </div>
        </div>

        </div>{{-- /tools grid --}}
    </div>{{-- /calculators lock wrapper --}}

    {{-- ── REAL-LIFE TOOLS — external, real calendar dates, zero effect on the game ── --}}
    <div class="mt-8 rounded-3xl p-5 sm:p-6" style="background:linear-gradient(135deg,rgba(16,185,129,0.05),rgba(6,182,212,0.03));border:1px dashed rgba(16,185,129,0.3);">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-lg">🌍</span>
            <h3 class="font-black text-white text-base">Real-Life Tools</h3>
            <span class="text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35);color:#6ee7b7;">Real dates, not game days</span>
        </div>
        <p class="text-gray-400 text-xs leading-relaxed mb-5">These use YOUR actual bills and goals on the real calendar — completely separate from Pesa City. Nothing here touches your in-game balance, credit score, or progress. This is where you put the lessons into practice.</p>

        <div class="grid sm:grid-cols-2 gap-4">
            {{-- REAL BILL REMINDERS --}}
            <div x-data="realBillsTool()" x-init="load()">
                <div @click="open=true" class="rounded-2xl p-5 cursor-pointer transition-all hover:scale-[1.01]" style="background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.2);">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:rgba(16,185,129,0.18);border:1px solid rgba(16,185,129,0.3);">🔔</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-black text-white">Real Bill Reminders</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Get notified before your real bills are due</p>
                        </div>
                        <span x-show="bills.length" class="text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0" style="background:rgba(16,185,129,0.18);color:#6ee7b7;" x-text="bills.length"></span>
                    </div>
                </div>

                <template x-teleport="body">
                    <div x-show="open" x-cloak class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9980;overflow-y:auto;" @click="open=false">
                        <div class="max-w-lg w-full my-auto bg-[#12111f] border border-emerald-500/25 rounded-3xl p-4 sm:p-6" @click.stop>
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-black text-white text-sm sm:text-lg">🔔 Real Bill Reminders</h3>
                                <button @click="open=false" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/10"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">Real dates, real push notifications. Not connected to your Pesa City balance.</p>
                            <div x-show="!unlocked" class="rounded-xl px-3.5 py-2.5 mb-4 text-xs flex items-center gap-2" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);color:#fcd34d;">
                                <span>🔒</span><span>Viewing only — <a href="{{ route('pricing') }}" class="underline font-bold">subscribe</a> to add, edit or mark bills paid.</span>
                            </div>

                            <div x-show="!showForm" class="space-y-2 max-h-80 overflow-y-auto pr-1">
                                <template x-if="bills.length === 0"><p class="text-center py-8 text-gray-500 text-sm">No bills yet. Add your first real bill below.</p></template>
                                <template x-for="b in bills" :key="b.id">
                                    <div class="rounded-2xl p-3.5" :style="b.is_overdue ? 'background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);' : (b.days_until_due<=b.reminder_lead_days ? 'background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);' : 'background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);')">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span x-text="b.icon" class="text-base sm:text-lg flex-shrink-0"></span>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-bold text-white truncate" x-text="b.name"></p>
                                                    <p class="text-[11px] text-gray-500" x-text="'Ksh '+fmt(b.amount)+' · '+(b.is_overdue ? 'Overdue' : (b.days_until_due===0?'Due today':'Due in '+b.days_until_due+'d'))+(b.is_recurring ? ' · '+b.frequency_label : ' · One-off')"></p>
                                                </div>
                                            </div>
                                            <div x-show="unlocked" class="flex items-center gap-1.5 flex-shrink-0">
                                                <button @click="markPaid(b)" class="text-[11px] font-bold px-2.5 py-1.5 rounded-lg" style="background:rgba(16,185,129,0.15);color:#6ee7b7;">Paid</button>
                                                <button @click="remove(b)" class="text-[11px] px-2 py-1.5 rounded-lg text-gray-500 hover:text-red-400">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div x-show="showForm && unlocked" class="space-y-3">
                                <input type="text" x-model="form.name" placeholder="Bill name (e.g. House Rent)" class="w-full px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);">
                                <div class="grid grid-cols-2 gap-2">
                                    <select x-model="form.category" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);">
                                        <template x-for="(c,key) in categories" :key="key"><option :value="key" x-text="c.icon+' '+c.label"></option></template>
                                    </select>
                                    <input type="number" x-model.number="form.amount" placeholder="Amount (Ksh)" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);">
                                </div>
                                <div><label class="text-[11px] text-gray-500 mb-1 block">Next due date</label><input type="date" x-model="form.next_due_date" class="w-full px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);"></div>
                                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer"><input type="checkbox" x-model="form.is_recurring" class="w-4 h-4 accent-emerald-500"> This bill repeats</label>
                                <div x-show="form.is_recurring" class="grid grid-cols-2 gap-2">
                                    <select x-model.number="form.frequency_days" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);">
                                        <template x-for="(label,days) in frequencies" :key="days"><option :value="days" x-text="label"></option></template>
                                    </select>
                                    <div><label class="text-[11px] text-gray-500 mb-1 block">Remind me (days before)</label><input type="number" x-model.number="form.reminder_lead_days" min="0" max="30" class="w-full px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(16,185,129,0.25);"></div>
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <button @click="save()" :disabled="!form.name||!form.amount||!form.next_due_date" class="flex-1 py-2.5 rounded-xl text-sm font-bold disabled:opacity-40" style="background:linear-gradient(135deg,#10b981,#059669);color:white;">Save Bill</button>
                                    <button @click="showForm=false" class="px-4 py-2.5 rounded-xl text-sm text-gray-400" style="background:rgba(255,255,255,0.05);">Cancel</button>
                                </div>
                            </div>

                            <button x-show="!showForm && unlocked" @click="openForm()" class="w-full mt-4 py-2.5 rounded-xl text-sm font-bold" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#6ee7b7;">+ Add Real Bill</button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- REAL SAVINGS GOALS --}}
            <div x-data="realGoalsTool()" x-init="load()">
                <div @click="open=true" class="rounded-2xl p-5 cursor-pointer transition-all hover:scale-[1.01]" style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.2);">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:rgba(6,182,212,0.18);border:1px solid rgba(6,182,212,0.3);">🎯</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-black text-white">Real Savings Goals</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Track real deposits toward real goals</p>
                        </div>
                        <span x-show="goals.length" class="text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0" style="background:rgba(6,182,212,0.18);color:#67e8f9;" x-text="goals.length"></span>
                    </div>
                </div>

                <template x-teleport="body">
                    <div x-show="open" x-cloak class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9980;overflow-y:auto;" @click="close()">
                        <div class="max-w-lg w-full my-auto bg-[#12111f] border border-cyan-500/25 rounded-3xl p-4 sm:p-6" @click.stop>
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-black text-white text-sm sm:text-lg">🎯 Real Savings Goals</h3>
                                <button @click="close()" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/10"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">Real money, real dates. Not connected to your Pesa City balance.</p>
                            <div x-show="!unlocked" class="rounded-xl px-3.5 py-2.5 mb-4 text-xs flex items-center gap-2" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);color:#fcd34d;">
                                <span>🔒</span><span>Viewing only — <a href="{{ route('pricing') }}" class="underline font-bold">subscribe</a> to create goals or log deposits.</span>
                            </div>

                            <div x-show="!activeId && !showForm" class="space-y-2 max-h-80 overflow-y-auto pr-1">
                                <template x-if="goals.length === 0"><p class="text-center py-8 text-gray-500 text-sm">No savings goals yet. Create your first one below.</p></template>
                                <template x-for="g in goals" :key="g.id">
                                    <div class="rounded-2xl p-3.5 cursor-pointer transition-all hover:scale-[1.01]" :style="g.status==='completed' ? 'background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.3);' : 'background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.2);'" @click="activeId=g.id">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-2"><span x-text="g.icon" class="text-base sm:text-lg"></span><p class="text-sm font-bold text-white" x-text="g.name"></p><span x-show="g.status==='completed'" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:rgba(16,185,129,0.2);color:#6ee7b7;">✓ Done</span></div>
                                            <p class="text-xs font-black" style="color:#67e8f9;" x-text="g.progress_pct+'%'"></p>
                                        </div>
                                        <div class="h-1.5 rounded-full overflow-hidden" style="background:rgba(255,255,255,0.08);"><div class="h-full rounded-full" :style="'width:'+g.progress_pct+'%;background:'+(g.status==='completed'?'#10b981':'#06b6d4')"></div></div>
                                        <p class="text-[11px] text-gray-500 mt-1.5" x-text="'Ksh '+fmt(g.total_saved)+' of '+fmt(g.target_amount)"></p>
                                    </div>
                                </template>
                            </div>

                            <div x-show="showForm && unlocked" class="space-y-3">
                                <input type="text" x-model="form.name" placeholder="What are you saving for?" class="w-full px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(6,182,212,0.25);">
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" x-model.number="form.target_amount" placeholder="Target (Ksh)" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(6,182,212,0.25);">
                                    <input type="date" x-model="form.target_date" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(6,182,212,0.25);">
                                </div>
                                <div class="flex gap-2">
                                    <button @click="saveGoal()" :disabled="!form.name||!form.target_amount" class="flex-1 py-2.5 rounded-xl text-sm font-bold disabled:opacity-40" style="background:linear-gradient(135deg,#06b6d4,#0891b2);color:white;">Create Goal</button>
                                    <button @click="showForm=false" class="px-4 py-2.5 rounded-xl text-sm text-gray-400" style="background:rgba(255,255,255,0.05);">Cancel</button>
                                </div>
                            </div>

                            <button x-show="!activeId && !showForm && unlocked" @click="showForm=true" class="w-full mt-4 py-2.5 rounded-xl text-sm font-bold" style="background:rgba(6,182,212,0.15);border:1px solid rgba(6,182,212,0.3);color:#67e8f9;">+ New Goal</button>

                            <template x-if="activeId && activeGoal()">
                                <div>
                                    <button @click="activeId=null" class="text-xs flex items-center gap-1 text-gray-500 hover:text-white mb-4"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>All Goals</button>
                                    <div class="flex items-center gap-5 mb-5">
                                        <div class="relative flex-shrink-0" style="width:80px;height:80px;">
                                            <svg width="80" height="80" viewBox="0 0 90 90"><circle cx="45" cy="45" r="38" fill="none" stroke="rgba(6,182,212,0.12)" stroke-width="7"/><circle cx="45" cy="45" r="38" fill="none" stroke="#06b6d4" stroke-width="7" stroke-linecap="round" :stroke-dasharray="2*Math.PI*38" :stroke-dashoffset="2*Math.PI*38*(1-(activeGoal().progress_pct/100))" transform="rotate(-90 45 45)" style="transition:stroke-dashoffset 0.8s cubic-bezier(0.34,1.56,0.64,1)"/></svg>
                                            <div class="absolute inset-0 flex flex-col items-center justify-center"><div class="text-base sm:text-lg font-black text-white" x-text="activeGoal().progress_pct+'%'"></div></div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-black text-white text-base" x-text="activeGoal().name"></p>
                                            <p class="text-sm mt-1 text-gray-400"><span class="font-bold" style="color:#67e8f9;" x-text="'Ksh '+fmt(activeGoal().total_saved)"></span> of <span class="font-bold text-white" x-text="'Ksh '+fmt(activeGoal().target_amount)"></span></p>
                                            <p x-show="activeGoal().status==='completed'" class="text-xs font-bold mt-1" style="color:#6ee7b7;">🎉 Goal achieved!</p>
                                        </div>
                                        <button x-show="unlocked" @click="removeGoal(activeGoal())" class="text-xs px-2 py-1 rounded-lg text-red-400 self-start" style="background:rgba(239,68,68,0.1);">Delete</button>
                                    </div>

                                    <div x-show="unlocked" class="rounded-2xl p-4 mb-4 space-y-2" style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.18);">
                                        <p class="text-xs font-bold text-gray-400 mb-1">LOG A DEPOSIT</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="number" x-model.number="depAmount" placeholder="Amount (Ksh)" class="px-3 py-2 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.3);border:1px solid rgba(6,182,212,0.2);">
                                            <input type="date" x-model="depDate" class="px-3 py-2 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.3);border:1px solid rgba(6,182,212,0.2);">
                                        </div>
                                        <input type="text" x-model="depNote" placeholder="Note (optional)" class="w-full px-3 py-2 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.3);border:1px solid rgba(6,182,212,0.2);">
                                        <button @click="addDeposit()" :disabled="!depAmount" class="w-full py-2 rounded-xl text-sm font-bold disabled:opacity-40" style="background:linear-gradient(135deg,#06b6d4,#0891b2);color:white;">+ Log Deposit</button>
                                    </div>

                                    <p class="text-xs font-bold text-gray-400 mb-2">DEPOSIT HISTORY</p>
                                    <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                                        <template x-if="activeGoal().deposits.length===0"><p class="text-center py-3 text-xs text-gray-500">No deposits logged yet.</p></template>
                                        <template x-for="d in activeGoal().deposits" :key="d.id">
                                            <div class="flex items-center justify-between py-2 px-3 rounded-xl" style="background:rgba(255,255,255,0.03);">
                                                <div><div class="text-xs text-gray-300 font-semibold" x-text="d.note||'Deposit'"></div><div class="text-[10px] text-gray-600" x-text="d.deposited_on"></div></div>
                                                <div class="flex items-center gap-2"><span class="text-sm font-black" style="color:#67e8f9;" x-text="'Ksh '+fmt(d.amount)"></span><button x-show="unlocked" @click="removeDeposit(d)" class="text-xs text-gray-500 hover:text-red-400">✕</button></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- REAL EXPENSES --}}
            <div x-data="realExpensesTool()" x-init="load()">
                <div @click="open=true" class="rounded-2xl p-5 cursor-pointer transition-all hover:scale-[1.01]" style="background:rgba(249,115,22,0.06);border:1px solid rgba(249,115,22,0.2);">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:rgba(249,115,22,0.18);border:1px solid rgba(249,115,22,0.3);">📊</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-black text-white">Real Expenses</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Log your real spending, day by day</p>
                        </div>
                    </div>
                </div>

                <template x-teleport="body">
                    <div x-show="open" x-cloak class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9980;overflow-y:auto;" @click="open=false">
                        <div class="max-w-lg w-full my-auto bg-[#12111f] border border-orange-500/25 rounded-3xl p-4 sm:p-6" @click.stop>
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-black text-white text-sm sm:text-lg">📊 Real Expenses</h3>
                                <button @click="open=false" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/10"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">Real spending, real dates. Feeds your Monthly Report.</p>
                            <div x-show="!unlocked" class="rounded-xl px-3.5 py-2.5 mb-4 text-xs flex items-center gap-2" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);color:#fcd34d;">
                                <span>🔒</span><span>Viewing only — <a href="{{ route('pricing') }}" class="underline font-bold">subscribe</a> to log new expenses.</span>
                            </div>

                            <div x-show="unlocked" class="space-y-3 mb-4 rounded-2xl p-4" style="background:rgba(249,115,22,0.06);border:1px solid rgba(249,115,22,0.18);">
                                <div class="grid grid-cols-2 gap-2">
                                    <select x-model="form.category" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);">
                                        <template x-for="(c,key) in categories" :key="key"><option :value="key" x-text="c.icon+' '+c.label"></option></template>
                                    </select>
                                    <input type="number" x-model.number="form.amount" placeholder="Amount (Ksh)" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" x-model="form.spent_on" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);">
                                    <input type="text" x-model="form.note" placeholder="Note (optional)" class="px-3 py-2.5 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);">
                                </div>
                                <button @click="save()" :disabled="!form.amount" class="w-full py-2.5 rounded-xl text-sm font-bold disabled:opacity-40" style="background:linear-gradient(135deg,#f97316,#ea580c);color:white;">+ Log Expense</button>
                            </div>

                            {{-- Custom categories — your own list, not a fixed one --}}
                            <div x-show="unlocked" class="mb-4">
                                <button type="button" @click="catMgrOpen=!catMgrOpen" class="text-xs font-bold flex items-center gap-1" style="color:#fb923c;">
                                    <span x-text="catMgrOpen ? '▾' : '▸'"></span> Manage Categories
                                </button>
                                <div x-show="catMgrOpen" x-cloak class="mt-2 space-y-1.5 rounded-2xl p-3" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
                                    <template x-for="(c,key) in categories" :key="key">
                                        <div class="flex items-center gap-2">
                                            <template x-if="editingCat !== key">
                                                <div class="flex items-center justify-between flex-1 py-1">
                                                    <span class="text-xs text-gray-300" x-text="c.icon+' '+c.label"></span>
                                                    <div class="flex items-center gap-2">
                                                        <button @click="startEditCat(key,c)" class="text-[11px]" style="color:#93c5fd;">Edit</button>
                                                        <button @click="deleteCat(key)" class="text-[11px]" style="color:#f87171;">Delete</button>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="editingCat === key">
                                                <div class="flex items-center gap-1.5 flex-1">
                                                    <input type="text" x-model="editIcon" maxlength="4" class="w-10 text-center rounded-lg px-1 py-1.5 text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);color:#fff;">
                                                    <input type="text" x-model="editLabel" maxlength="60" class="flex-1 rounded-lg px-2 py-1.5 text-xs" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);color:#fff;">
                                                    <button @click="saveEditCat(key)" class="text-[11px] font-bold" style="color:#34d399;">Save</button>
                                                    <button @click="editingCat=null" class="text-[11px]" style="color:#6b7280;">✕</button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <div class="flex items-center gap-1.5 pt-2 mt-1" style="border-top:1px solid rgba(255,255,255,0.06);">
                                        <input type="text" x-model="newCatIcon" placeholder="📋" maxlength="4" class="w-10 text-center rounded-lg px-1 py-1.5 text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);color:#fff;">
                                        <input type="text" x-model="newCatLabel" placeholder="New category name" maxlength="60" @keydown.enter="addCat()" class="flex-1 rounded-lg px-2 py-1.5 text-xs" style="background:rgba(0,0,0,0.35);border:1px solid rgba(249,115,22,0.25);color:#fff;">
                                        <button @click="addCat()" :disabled="!newCatLabel" class="text-[11px] font-bold disabled:opacity-40" style="color:#34d399;">+ Add</button>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs font-bold text-gray-400 mb-2">RECENT EXPENSES</p>
                            <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                                <template x-if="expenses.length===0"><p class="text-center py-6 text-xs text-gray-500">No expenses logged yet.</p></template>
                                <template x-for="e in expenses" :key="e.id">
                                    <div class="flex items-center justify-between py-2 px-3 rounded-xl" style="background:rgba(255,255,255,0.03);">
                                        <div class="flex items-center gap-2"><span x-text="e.icon" class="text-sm"></span><div><div class="text-xs text-gray-300 font-semibold" x-text="e.note||e.category_label"></div><div class="text-[10px] text-gray-600" x-text="e.spent_on"></div></div></div>
                                        <div class="flex items-center gap-2"><span class="text-sm font-black" style="color:#fb923c;" x-text="'Ksh '+fmt(e.amount)"></span><button x-show="unlocked" @click="remove(e)" class="text-xs text-gray-500 hover:text-red-400">✕</button></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- MONTHLY REPORT & SNAPSHOT --}}
            <div x-data="realReportTool()">
                <div @click="open=true" class="rounded-2xl p-5 cursor-pointer transition-all hover:scale-[1.01]" style="background:rgba(168,85,247,0.06);border:1px solid rgba(168,85,247,0.2);">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0" style="background:rgba(168,85,247,0.18);border:1px solid rgba(168,85,247,0.3);">📸</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-black text-white">Monthly Report</h4>
                            <p class="text-xs text-gray-400 mt-0.5">Snapshot your real month — spent, saved, paid</p>
                        </div>
                    </div>
                </div>

                <template x-teleport="body">
                    <div x-show="open" x-cloak class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9980;overflow-y:auto;" @click="open=false">
                        <div class="max-w-md w-full my-auto bg-[#12111f] border border-purple-500/25 rounded-3xl p-4 sm:p-6" @click.stop>
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-black text-white text-sm sm:text-lg">📸 Monthly Report</h3>
                                <button @click="open=false" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/10"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">Real numbers, any month. Not connected to your Pesa City report card.</p>

                            <div class="flex items-center justify-between gap-2 mb-4">
                                <button @click="shiftMonth(-1)" class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-white" style="background:rgba(255,255,255,0.05);">‹</button>
                                <input type="month" x-model="month" class="flex-1 text-center px-3 py-2 rounded-xl text-white text-sm" style="background:rgba(0,0,0,0.35);border:1px solid rgba(168,85,247,0.25);">
                                <button @click="shiftMonth(1)" class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-white" style="background:rgba(255,255,255,0.05);">›</button>
                            </div>

                            <button @click="generate()" :disabled="loading" class="w-full py-2.5 rounded-xl text-sm font-bold mb-4 disabled:opacity-50" style="background:linear-gradient(135deg,#a855f7,#7c3aed);color:white;">
                                <span x-show="!loading">📸 Generate Snapshot</span>
                                <span x-show="loading">Generating…</span>
                            </button>

                            <template x-if="report">
                                <div class="rounded-2xl p-5 text-center" style="background:rgba(168,85,247,0.06);border:1px solid rgba(168,85,247,0.2);">
                                    <template x-if="report.grade">
                                        <div class="text-4xl sm:text-6xl font-black leading-none mb-1" :style="'color:'+gradeColor(report.grade)" x-text="report.grade"></div>
                                    </template>
                                    <template x-if="!report.grade">
                                        <div class="text-xl sm:text-2xl mb-1">🤷</div>
                                    </template>
                                    <p class="text-sm font-black text-white mb-4" x-text="report.month_label"></p>
                                    <div class="space-y-2 text-left text-xs bg-black/20 rounded-xl p-4 mb-3">
                                        <div class="flex justify-between"><span class="text-gray-400">💰 Saved</span><span class="font-black" style="color:#67e8f9;" x-text="'+Ksh '+fmt(report.total_saved)"></span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">📊 Expenses</span><span class="font-black text-orange-400" x-text="'-Ksh '+fmt(report.total_expenses)"></span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">🔔 Bills Paid</span><span class="font-black text-red-400" x-text="'-Ksh '+fmt(report.total_bills)"></span></div>
                                        <div class="flex justify-between pt-2" style="border-top:1px solid rgba(255,255,255,0.08);"><span class="text-gray-300 font-bold">Savings Rate</span><span class="font-black" :style="'color:'+gradeColor(report.grade)" x-text="report.savings_rate+'%'"></span></div>
                                    </div>
                                    <template x-if="report.by_category.length">
                                        <div class="text-left">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1.5">Top Expense Categories</p>
                                            <template x-for="c in report.by_category.slice(0,4)" :key="c.category">
                                                <div class="flex justify-between text-xs py-1"><span x-text="c.icon+' '+c.label"></span><span class="text-gray-400" x-text="'Ksh '+fmt(c.total)"></span></div>
                                            </template>
                                        </div>
                                    </template>
                                    <p x-show="report.total_tracked===0" class="text-xs text-gray-500 mt-2">No real-life activity logged this month yet.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
/* ─── BAJETI SMART ─── */
function bajetiTool() {
    return {
        open:false,income:'',needsPct:50,wantsPct:30,savingsPct:20,saving:false,saved:false,
        fmt(n){return new Intl.NumberFormat('en-KE').format(n||0);},
        headers(){ return { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content }; },
        async load(){
            try{
                const res = await fetch('{{ route('real-life.index') }}', { headers:{'Accept':'application/json'} });
                if(res.ok){
                    const data = await res.json();
                    if(data.budget_ratio){
                        this.needsPct   = data.budget_ratio.needs_pct;
                        this.wantsPct   = data.budget_ratio.wants_pct;
                        this.savingsPct = data.budget_ratio.savings_pct;
                    }
                }
            }catch(e){}
        },
        async saveRatio(){
            this.clamp();
            this.saving = true;
            try{
                const res = await fetch('{{ route('real-life.budget-ratio.save') }}', {
                    method:'POST', headers:this.headers(),
                    body: JSON.stringify({ needs_pct:this.needsPct, wants_pct:this.wantsPct, savings_pct:this.savingsPct }),
                });
                if(res.ok){ this.saved = true; setTimeout(()=>this.saved=false, 2000); }
            }catch(e){}
            finally{ this.saving = false; }
        },
        clamp(){
            let n=parseInt(this.needsPct)||0,w=parseInt(this.wantsPct)||0,s=parseInt(this.savingsPct)||0,total=n+w+s;
            if(total>100){let ex=total-100;if(w>ex){w-=ex;}else{ex-=w;w=0;s=Math.max(0,s-ex);}}
            this.needsPct=n;this.wantsPct=w;this.savingsPct=s;
        },
        cats(){
            let n=this.needsPct,w=this.wantsPct,s=this.savingsPct;
            return [{name:'Pango/Nyumba',icon:'🏠',r:n*0.30/100,g:'needs'},{name:'Chakula',icon:'🍲',r:n*0.25/100,g:'needs'},{name:'Nauli',icon:'🚌',r:n*0.15/100,g:'needs'},{name:'Umeme/Maji',icon:'💡',r:n*0.10/100,g:'needs'},{name:'Mavazi & Starehe',icon:'🎮',r:w/100,g:'wants'},{name:'Akiba/SACCO',icon:'🏦',r:s*0.60/100,g:'savings'},{name:'Dharura Fund',icon:'🛡️',r:s*0.40/100,g:'savings'}];
        },
        wisdom(){
            let s=this.savingsPct;
            if(s>=25)return'Saving '+s+'% is exceptional — you\'re building real wealth.';
            if(s>=20)return'The 20% savings rule is the golden foundation of personal finance.';
            if(s>=10)return'Every Ksh saved today is two Ksh working for you tomorrow.';
            return'Even 5% saved consistently beats saving nothing. Build the habit first.';
        }
    }
}

/* ─── LENGO SAVER ─── */
function lengoTool() {
    const COLORS=['#6366f1','#10b981','#f59e0b','#ec4899','#06b6d4','#8b5cf6','#ef4444','#84cc16'];
    return {
        open:false,sessions:[],activeId:null,newOpen:false,
        nName:'',nTarget:null,nDate:'',nAlready:0,
        logAmount:null,logNote:'',logDate:new Date().toISOString().split('T')[0],
        load(){try{const s=localStorage.getItem('pq_lengo');if(s)this.sessions=JSON.parse(s);}catch(e){}},
        save(){try{localStorage.setItem('pq_lengo',JSON.stringify(this.sessions));}catch(e){}},
        activeSession(){return this.sessions.find(s=>s.id===this.activeId)??null;},
        sessionSaved(s){return s.logs.reduce((a,l)=>a+l.amount,0);},
        sessionPct(s){return s.target>0?Math.min(100,Math.round(this.sessionSaved(s)/s.target*100)):0;},
        sessionColor(i){return COLORS[i%COLORS.length];},
        sessionStyle(i){const c=COLORS[i%COLORS.length];return`background:rgba(${parseInt(c.slice(1,3),16)},${parseInt(c.slice(3,5),16)},${parseInt(c.slice(5,7),16)},0.06);border:1px solid rgba(${parseInt(c.slice(1,3),16)},${parseInt(c.slice(3,5),16)},${parseInt(c.slice(5,7),16)},0.2);`;},
        activeSaved(){const s=this.activeSession();return s?this.sessionSaved(s):0;},
        activePct(){const s=this.activeSession();return s?this.sessionPct(s):0;},
        daysLeft(s){if(!s?.targetDate)return null;const t=new Date(s.targetDate),d=new Date();d.setHours(0,0,0,0);return Math.ceil((t-d)/864e5);},
        activeDailySave(){const s=this.activeSession();if(!s)return 0;const dl=this.daysLeft(s);if(!dl||dl<=0)return 0;const rem=s.target-this.activeSaved();return rem<=0?0:Math.ceil(rem/dl);},
        addSession(){if(!this.nName||!this.nTarget)return;const s={id:Date.now()+'',name:this.nName,target:this.nTarget,targetDate:this.nDate,logs:this.nAlready>0?[{date:new Date().toISOString().split('T')[0],amount:this.nAlready,note:'Initial savings'}]:[]};this.sessions.push(s);this.save();this.nName='';this.nTarget=null;this.nDate='';this.nAlready=0;this.newOpen=false;this.activeId=s.id;},
        addLog(){const s=this.activeSession();if(!s||!this.logAmount)return;s.logs.push({date:this.logDate||new Date().toISOString().split('T')[0],amount:this.logAmount,note:this.logNote});s.logs.sort((a,b)=>a.date.localeCompare(b.date));this.save();this.logAmount=null;this.logNote='';},
        deleteLog(i){const s=this.activeSession();if(s){s.logs.splice(i,1);this.save();}},
        deleteSession(s){this.sessions=this.sessions.filter(x=>x.id!==s.id);this.save();},
        fmt(n){return new Intl.NumberFormat('en-KE').format(Math.round(n||0));},
        _chartData(s){if(!s||!s.logs.length)return[];let cum=0;return s.logs.map(l=>({date:new Date(l.date).getTime(),cum:(cum+=l.amount)}));},
        _scale(pts,w,h,maxY){if(!pts.length)return[];const dates=pts.map(p=>p.date);const minD=Math.min(...dates),maxD=Math.max(...dates);const rangeD=maxD-minD||86400000;return pts.map(p=>({x:Math.round(10+(p.date-minD)/rangeD*(w-20)),y:Math.round(h-5-(p.cum/maxY)*(h-15)),cum:p.cum}));},
        activeChartPoints(w,h){const s=this.activeSession();if(!s)return'';const pts=this._chartData(s);const scaled=this._scale(pts,w,h,s.target||this.activeSaved()||1);return scaled.map(p=>p.x+','+p.y).join(' ');},
        activeChartDots(w,h){const s=this.activeSession();if(!s)return[];const pts=this._chartData(s);return this._scale(pts,w,h,s.target||this.activeSaved()||1);},
    }
}

/* ─── UKUAJI GROW ─── */
function ukuajiTool() {
    return {
        open:false,monthly:2000,years:5,
        vehicles:[{name:'M-Pesa Lock Savings',rate:4,icon:'📱',color:'#2dd4bf',bg:'background:rgba(20,184,166,0.07);border:1px solid rgba(20,184,166,0.2);'},{name:'Bank Fixed Deposit',rate:9,icon:'🏦',color:'#60a5fa',bg:'background:rgba(59,130,246,0.07);border:1px solid rgba(59,130,246,0.2);'},{name:'SACCO / Chama',rate:12,icon:'🤝',color:'#a78bfa',bg:'background:rgba(139,92,246,0.07);border:1px solid rgba(139,92,246,0.2);'},{name:'T-Bills (Govt.)',rate:14,icon:'📜',color:'#fbbf24',bg:'background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);'},{name:'NSE Stocks (avg.)',rate:18,icon:'📈',color:'#34d399',bg:'background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.2);'}],
        fv(rate){const r=rate/100/12,n=(this.years||1)*12,m=this.monthly||0;if(r===0)return m*n;return m*((Math.pow(1+r,n)-1)/r);},
        contributed(){return(this.monthly||0)*(this.years||1)*12;},
        maxFV(){return Math.max(...this.vehicles.map(v=>this.fv(v.rate)));},
        barW(rate){let mx=this.maxFV();return mx>0?Math.round(this.fv(rate)/mx*100)+'%':'0%';},
        fmtVal(n){if(n>=1e6)return'Ksh '+(n/1e6).toFixed(2)+'M';if(n>=1000)return'Ksh '+(n/1000).toFixed(1)+'K';return'Ksh '+Math.round(n||0);},
        fmt(n){return new Intl.NumberFormat('en-KE').format(Math.round(n||0));}
    }
}

/* ─── MKOPO PLANNER ─── */
function mkopoTool() {
    return {
        open:false,principal:'',rate:14,months:24,
        fmt(n){return new Intl.NumberFormat('en-KE').format(Math.round(n||0));},
        monthlyPayment(){const p=this.principal||0,r=(this.rate||0)/100/12,n=this.months||1;if(r===0)return p/n;return p*r*Math.pow(1+r,n)/(Math.pow(1+r,n)-1);}
    }
}

/* ─── FAIDA COMPOUNDER ─── */
function faidaTool() {
    return {
        open:false,principal:'',monthly:'',years:10,
        fmt(n){return new Intl.NumberFormat('en-KE').format(Math.round(n||0));},
        compound(rate){const r=rate/100/12,n=(this.years||1)*12,p=this.principal||0,m=this.monthly||0;return p*Math.pow(1+r,n)+(r>0?m*(Math.pow(1+r,n)-1)/r:m*n);},
        options(){return[{label:'Bank FD',rate:9,color:'#60a5fa',bg:'rgba(59,130,246,0.07)',border:'rgba(59,130,246,0.2)'},{label:'SACCO',rate:12,color:'#a78bfa',bg:'rgba(139,92,246,0.07)',border:'rgba(139,92,246,0.2)'},{label:'T-Bills',rate:15,color:'#fbbf24',bg:'rgba(245,158,11,0.07)',border:'rgba(245,158,11,0.2)'},{label:'NSE Stocks',rate:18,color:'#34d399',bg:'rgba(16,185,129,0.07)',border:'rgba(16,185,129,0.2)'}];}
    }
}

/* ─── REAL-LIFE BILL REMINDERS (external, real dates) ─── */
function realBillsTool() {
    return {
        open: false, showForm: false, bills: [], unlocked: {{ $smartToolsUnlocked ? 'true' : 'false' }},
        categories: @json(\App\Models\RealLifeBill::CATEGORIES),
        frequencies: @json(\App\Models\RealLifeBill::FREQUENCIES),
        form: { name:'', category:'other', amount:null, next_due_date:'', is_recurring:true, frequency_days:30, reminder_lead_days:2 },
        fmt(n){ return new Intl.NumberFormat('en-KE').format(Math.round(n||0)); },
        headers(){ return { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }; },
        async load() {
            try {
                const res = await fetch('{{ route('real-life.index') }}', { headers: this.headers() });
                if (res.ok) { const d = await res.json(); this.bills = d.bills; this.unlocked = d.unlocked; }
            } catch (e) {}
        },
        openForm() {
            this.form = { name:'', category:'other', amount:null, next_due_date:'', is_recurring:true, frequency_days:30, reminder_lead_days:2 };
            this.showForm = true;
        },
        async save() {
            try {
                const res = await fetch('{{ route('real-life.bills.store') }}', { method:'POST', headers:this.headers(), body: JSON.stringify(this.form) });
                if (res.ok) { await this.load(); this.showForm = false; }
            } catch (e) {}
        },
        async markPaid(b) {
            await fetch(`/real-life/bills/${b.id}/mark-paid`, { method:'POST', headers:this.headers() });
            this.load();
        },
        async remove(b) {
            if (!confirm(`Delete "${b.name}"?`)) return;
            await fetch(`/real-life/bills/${b.id}`, { method:'DELETE', headers:this.headers() });
            this.load();
        },
    }
}

/* ─── REAL-LIFE SAVINGS GOALS (external, real dates) ─── */
function realGoalsTool() {
    return {
        open: false, showForm: false, activeId: null, goals: [], unlocked: {{ $smartToolsUnlocked ? 'true' : 'false' }},
        form: { name:'', target_amount:null, target_date:'' },
        depAmount: null, depDate: new Date().toISOString().slice(0,10), depNote: '',
        fmt(n){ return new Intl.NumberFormat('en-KE').format(Math.round(n||0)); },
        headers(){ return { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }; },
        activeGoal() { return this.goals.find(g => g.id === this.activeId) || null; },
        close() { this.open = false; this.activeId = null; this.showForm = false; },
        async load() {
            try {
                const res = await fetch('{{ route('real-life.index') }}', { headers: this.headers() });
                if (res.ok) { const d = await res.json(); this.goals = d.goals; this.unlocked = d.unlocked; }
            } catch (e) {}
        },
        async saveGoal() {
            try {
                const res = await fetch('{{ route('real-life.goals.store') }}', { method:'POST', headers:this.headers(), body: JSON.stringify(this.form) });
                if (res.ok) { await this.load(); this.showForm = false; this.form = { name:'', target_amount:null, target_date:'' }; }
            } catch (e) {}
        },
        async removeGoal(g) {
            if (!confirm(`Delete "${g.name}"? This also deletes its deposit history.`)) return;
            await fetch(`/real-life/goals/${g.id}`, { method:'DELETE', headers:this.headers() });
            this.activeId = null;
            this.load();
        },
        async addDeposit() {
            if (!this.depAmount || !this.activeId) return;
            const res = await fetch(`/real-life/goals/${this.activeId}/deposits`, {
                method:'POST', headers:this.headers(),
                body: JSON.stringify({ amount:this.depAmount, note:this.depNote, deposited_on: this.depDate || new Date().toISOString().slice(0,10) })
            });
            if (res.ok) { this.depAmount=null; this.depNote=''; await this.load(); }
        },
        async removeDeposit(d) {
            await fetch(`/real-life/deposits/${d.id}`, { method:'DELETE', headers:this.headers() });
            this.load();
        },
    }
}

/* ─── REAL-LIFE EXPENSES (external, real dates) ─── */
function realExpensesTool() {
    return {
        open: false, expenses: [], unlocked: {{ $smartToolsUnlocked ? 'true' : 'false' }},
        categories: @json(\App\Models\UserExpenseCategory::mapForUser(auth()->id())),
        form: { category:'other', amount:null, note:'', spent_on: new Date().toISOString().slice(0,10) },
        catMgrOpen:false, editingCat:null, editLabel:'', editIcon:'', newCatLabel:'', newCatIcon:'',
        fmt(n){ return new Intl.NumberFormat('en-KE').format(Math.round(n||0)); },
        headers(){ return { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }; },
        async load() {
            try {
                const [idxRes, expRes] = await Promise.all([
                    fetch('{{ route('real-life.index') }}', { headers: this.headers() }),
                    fetch('{{ route('real-life.expenses.index') }}', { headers: this.headers() }),
                ]);
                if (idxRes.ok) {
                    const idx = await idxRes.json();
                    this.unlocked = idx.unlocked;
                    if (idx.categories) this.categories = idx.categories;
                }
                if (expRes.ok) { this.expenses = (await expRes.json()).expenses; }
            } catch (e) {}
        },
        async save() {
            try {
                const res = await fetch('{{ route('real-life.expenses.store') }}', { method:'POST', headers:this.headers(), body: JSON.stringify(this.form) });
                if (res.ok) {
                    this.form = { category:'other', amount:null, note:'', spent_on: new Date().toISOString().slice(0,10) };
                    await this.load();
                }
            } catch (e) {}
        },
        async remove(e) {
            await fetch(`/real-life/expenses/${e.id}`, { method:'DELETE', headers:this.headers() });
            this.load();
        },
        startEditCat(key, c) { this.editingCat = key; this.editLabel = c.label; this.editIcon = c.icon; },
        async saveEditCat(key) {
            if (!this.editLabel) return;
            const id = this.categories[key]?.id;
            try {
                const res = await fetch(`/real-life/categories/${id}`, { method:'PUT', headers:this.headers(), body: JSON.stringify({ label:this.editLabel, icon:this.editIcon }) });
                if (res.ok) { this.editingCat = null; await this.load(); }
            } catch (e) {}
        },
        async addCat() {
            if (!this.newCatLabel) return;
            try {
                const res = await fetch('{{ route('real-life.categories.store') }}', { method:'POST', headers:this.headers(), body: JSON.stringify({ label:this.newCatLabel, icon:this.newCatIcon || '📋' }) });
                if (res.ok) { this.newCatLabel = ''; this.newCatIcon = ''; await this.load(); }
                else { const data = await res.json(); alert(data.message || 'Could not add category.'); }
            } catch (e) {}
        },
        async deleteCat(key) {
            if (!confirm('Delete this category? Already-logged expenses keep their amounts.')) return;
            const id = this.categories[key]?.id;
            await fetch(`/real-life/categories/${id}`, { method:'DELETE', headers:this.headers() });
            if (this.form.category === key) this.form.category = 'other';
            await this.load();
        },
    }
}

/* ─── MONTHLY REPORT & SNAPSHOT (real-life tools) ─── */
function realReportTool() {
    return {
        open: false, loading: false, report: null,
        month: new Date().toISOString().slice(0,7),
        headers(){ return { 'Accept':'application/json' }; },
        fmt(n){ return new Intl.NumberFormat('en-KE').format(Math.round(n||0)); },
        gradeColor(g){ return { A:'#10b981', B:'#22c55e', C:'#f59e0b', D:'#ef4444' }[g] || '#9ca3af'; },
        shiftMonth(delta) {
            const [y,m] = this.month.split('-').map(Number);
            const d = new Date(y, m - 1 + delta, 1);
            this.month = d.toISOString().slice(0,7);
        },
        async generate() {
            this.loading = true;
            try {
                const res = await fetch(`/real-life/report?month=${this.month}`, { headers: this.headers() });
                if (res.ok) { this.report = await res.json(); }
            } catch (e) {}
            finally { this.loading = false; }
        },
    }
}
</script>
