<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Career Match Quiz — PesaQuest</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }

        @keyframes fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        @keyframes popIn  { from { opacity:0; transform:scale(.88); } to { opacity:1; transform:scale(1); } }
        @keyframes shimmer{ 0%{background-position:200% center} 100%{background-position:-200% center} }
        @keyframes pulse  { 0%,100%{opacity:1} 50%{opacity:.6} }
        @keyframes spin   { to { transform:rotate(360deg); } }
        @keyframes bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        @keyframes confetti-fall { to { transform: translateY(110vh) rotate(720deg); opacity:0; } }

        .fade-up    { animation: fadeUp .45s cubic-bezier(.34,1.56,.64,1) both; }
        .fade-in    { animation: fadeIn .3s ease both; }
        .pop-in     { animation: popIn .35s cubic-bezier(.34,1.56,.64,1) both; }
        .bounce-in  { animation: bounce 1s ease infinite; }

        .option-card {
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 1rem;
            transition: all .22s cubic-bezier(.34,1.56,.64,1);
            cursor: pointer;
        }
        .option-card:hover {
            transform: translateY(-4px) scale(1.02);
            border-color: rgba(99,102,241,0.6);
            background: rgba(99,102,241,0.12);
            box-shadow: 0 16px 40px rgba(99,102,241,0.2);
        }
        .option-card.selected {
            border-color: rgba(99,102,241,0.8) !important;
            background: rgba(99,102,241,0.18) !important;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.3), 0 16px 40px rgba(99,102,241,0.25) !important;
        }

        .progress-bar { transition: width .6s cubic-bezier(.4,0,.2,1); }

        .field-card {
            border-radius: 1.25rem;
            border: 2px solid transparent;
            transition: all .25s ease;
            cursor: pointer;
        }
        .field-card:hover, .field-card.chosen {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }

        .shimmer-text {
            background: linear-gradient(90deg, #fff 20%, #a78bfa 50%, #f59e0b 70%, #fff 90%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s linear infinite;
        }

        .confetti-piece {
            position: fixed;
            width: 10px; height: 10px;
            border-radius: 2px;
            animation: confetti-fall 2.5s ease-in forwards;
            pointer-events: none;
        }

        .star-pulse { animation: pulse 2s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen text-white" x-data="careerQuiz()" x-init="init()">

{{-- ── STARS BACKGROUND ── --}}
<div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
    <div class="absolute inset-0" style="background:radial-gradient(ellipse at 20% 20%,rgba(99,102,241,.15) 0%,transparent 60%),radial-gradient(ellipse at 80% 80%,rgba(139,92,246,.12) 0%,transparent 60%),#07060f;"></div>
    @for($i=0;$i<40;$i++)
    <div class="absolute w-1 h-1 rounded-full star-pulse"
         style="left:{{ rand(0,100) }}%;top:{{ rand(0,100) }}%;background:rgba(255,255,255,{{ rand(1,4)/10 }});animation-delay:{{ rand(0,30)/10 }}s;"></div>
    @endfor
</div>

{{-- ── CONFETTI CONTAINER ── --}}
<div id="confetti-container" class="fixed inset-0 pointer-events-none z-50"></div>

{{-- ── MAIN CONTAINER ── --}}
<div class="relative min-h-screen flex flex-col">

    {{-- Nav --}}
    <div class="flex items-center justify-between px-6 py-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
        <img src="{{ asset('moski-logo.png') }}" alt="Moski" class="h-9 w-auto rounded-xl">
    </div>

    {{-- PROGRESS BAR --}}
    <div class="px-6 mb-2">
        <div class="h-1.5 bg-white/5 rounded-full overflow-hidden max-w-2xl mx-auto">
            <div class="progress-bar h-full rounded-full"
                 :style="`width:${progressPct}%;background:linear-gradient(90deg,#6366f1,#a78bfa,#f59e0b)`"></div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1 max-w-2xl mx-auto">
            <span x-text="step > 0 && step <= questions.length ? `Question ${step} of ${questions.length}` : ''"></span>
            <span x-text="`${progressPct}%`"></span>
        </div>
    </div>

    {{-- ── STEP 0: INTRO ── --}}
    <div x-show="step === 0" x-cloak class="fade-up flex-1 flex items-center justify-center px-6 py-12">
        <div class="max-w-lg w-full text-center">
            <div class="text-7xl mb-6 bounce-in">🎯</div>
            <h1 class="text-4xl sm:text-5xl font-black mb-4 leading-tight">
                Find Your
                <span class="shimmer-text">Dream Career</span>
            </h1>
            <p class="text-gray-300 text-lg mb-8 leading-relaxed">
                Answer 5 quick questions and we'll match you to a career path that fits your personality.
                Your virtual life starts here!
            </p>
            <div class="grid grid-cols-3 gap-3 mb-8 text-sm text-gray-400">
                <div class="bg-white/5 rounded-2xl p-3 border border-white/8">
                    <div class="text-2xl mb-1">⏱️</div>
                    <div class="font-semibold text-white">2 min</div>
                    <div>to complete</div>
                </div>
                <div class="bg-white/5 rounded-2xl p-3 border border-white/8">
                    <div class="text-2xl mb-1">🎯</div>
                    <div class="font-semibold text-white">5 questions</div>
                    <div>personalised</div>
                </div>
                <div class="bg-white/5 rounded-2xl p-3 border border-white/8">
                    <div class="text-2xl mb-1">💼</div>
                    <div class="font-semibold text-white">10+ careers</div>
                    <div>to discover</div>
                </div>
            </div>
            <button @click="step = 1; playSound('click')"
                    class="w-full py-4 rounded-2xl font-black text-white text-lg shadow-2xl transition-all hover:scale-105"
                    style="background:linear-gradient(135deg,#6366f1,#a78bfa);box-shadow:0 8px 32px rgba(99,102,241,.5);">
                Let's Go! 🚀
            </button>
        </div>
    </div>

    {{-- ── STEPS 1-5: QUESTIONS ── --}}
    <template x-for="(q, qi) in questions" :key="qi">
        <div x-show="step === qi + 1" x-cloak class="fade-up flex-1 flex items-center justify-center px-4 py-8">
            <div class="max-w-2xl w-full">
                {{-- Question --}}
                <div class="text-center mb-8">
                    <div class="inline-flex items-center gap-2 bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 text-xs font-bold px-3 py-1.5 rounded-full mb-4">
                        Question <span x-text="qi + 1"></span> of <span x-text="questions.length"></span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-white leading-tight" x-text="q.question"></h2>
                </div>

                {{-- Options Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="(opt, oi) in q.options" :key="oi">
                        <button
                            @click="pickAnswer(qi, oi)"
                            :class="answers[qi] === oi ? 'option-card selected' : 'option-card'"
                            class="text-left p-4 flex items-center gap-3">
                            <span class="text-3xl flex-shrink-0" x-text="opt.emoji"></span>
                            <div>
                                <div class="font-bold text-white text-sm leading-snug" x-text="opt.label"></div>
                                <div class="text-xs text-gray-400 mt-0.5 leading-snug" x-text="opt.sub"></div>
                            </div>
                            {{-- Checkmark --}}
                            <div class="ml-auto flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                 :class="answers[qi] === oi ? 'border-indigo-400 bg-indigo-500' : 'border-white/20'">
                                <svg x-show="answers[qi] === oi" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Next/Skip --}}
                <div class="flex gap-3 mt-6">
                    <button x-show="qi > 0" @click="step--; playSound('click')"
                            class="px-6 py-3 rounded-xl border border-white/10 text-gray-400 hover:text-white hover:border-white/20 transition-all text-sm">
                        ← Back
                    </button>
                    <button @click="nextQuestion(qi)"
                            :disabled="answers[qi] === null"
                            class="flex-1 py-3 rounded-xl font-bold text-sm transition-all"
                            :class="answers[qi] !== null
                                ? 'text-white shadow-lg hover:scale-[1.02]'
                                : 'text-gray-600 cursor-not-allowed'"
                            :style="answers[qi] !== null ? 'background:linear-gradient(135deg,#6366f1,#a78bfa);box-shadow:0 4px 20px rgba(99,102,241,.4)' : 'background:rgba(255,255,255,0.05)'">
                        <span x-text="qi === questions.length - 1 ? 'See My Results! 🎉' : 'Next Question →'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── STEP N+1: RESULT ── --}}
    <div x-show="step === questions.length + 1" x-cloak class="pop-in flex-1 flex items-center justify-center px-4 py-8">
        <div class="max-w-lg w-full text-center">
            <div class="text-6xl mb-4" x-text="matchedField?.icon"></div>
            <div class="inline-flex items-center gap-2 text-xs font-bold px-3 py-1.5 rounded-full border mb-4"
                 :style="`color:${matchedField?.color};border-color:${matchedField?.color}40;background:${matchedField?.color}15`">
                Your Career Match
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-white mb-2" x-text="matchedField?.label"></h2>
            <p class="text-gray-300 mb-6 leading-relaxed" x-text="matchedField?.desc"></p>

            {{-- Score breakdown --}}
            <div class="bg-white/4 border border-white/8 rounded-2xl p-4 mb-6">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-3">How you scored</p>
                <div class="space-y-2">
                    <template x-for="(score, field) in topScores" :key="field">
                        <div class="flex items-center gap-3">
                            <span class="text-sm w-32 text-left text-gray-300" x-text="fieldLabels[field]?.label ?? field"></span>
                            <div class="flex-1 h-2 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000"
                                     :style="`width:${Math.round(score/maxScore*100)}%;background:${fieldLabels[field]?.color ?? '#6366f1'}`"></div>
                            </div>
                            <span class="text-xs text-gray-400 w-6 text-right" x-text="score"></span>
                        </div>
                    </template>
                </div>
            </div>

            <button @click="step = questions.length + 2; playSound('levelup')"
                    class="w-full py-4 rounded-2xl font-black text-white text-lg shadow-2xl transition-all hover:scale-105"
                    :style="`background:linear-gradient(135deg,${matchedField?.color ?? '#6366f1'},${matchedField?.color ?? '#a78bfa'}88);box-shadow:0 8px 32px ${matchedField?.color ?? '#6366f1'}50`">
                Continue →
            </button>
        </div>
    </div>

    {{-- ── STEP 7: CONFIRM CAREER PATH ── --}}
    <div x-show="step === questions.length + 2" x-cloak class="fade-up flex-1 flex items-center justify-center px-4 py-8">
        <div class="max-w-2xl w-full">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-black text-white">
                    Lock In Your Career Path
                </h2>
                <p class="text-gray-400 text-sm mt-2 leading-relaxed">
                    Your path shapes the courses and jobs that fit you in Pesa City.
                    <span class="text-amber-300 font-bold">Jobs aren't handed out</span> — you'll take a course at the
                    Opportunity Hub, qualify, and get hired to start earning.
                </p>
            </div>

            <div id="onboard-error-quiz"
                 style="display:none;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;padding:.75rem 1rem;border-radius:.75rem;font-size:.8rem;margin-bottom:1rem;text-align:center;"></div>

            {{-- Matched path — primary confirm --}}
            <button @click="pickField(matchedField?.key)"
                    :disabled="submitting"
                    class="field-card w-full text-left p-5 flex items-center gap-4 border mb-3"
                    :style="`background:${matchedField?.color}12;border-color:${matchedField?.color}60`">
                <div class="text-4xl flex-shrink-0" x-text="matchedField?.icon"></div>
                <div class="flex-1 min-w-0">
                    <div class="text-[10px] font-black uppercase tracking-wider" :style="`color:${matchedField?.color}`">Your Quiz Match</div>
                    <div class="font-black text-white text-lg leading-tight" x-text="matchedField?.label"></div>
                    <div class="text-gray-400 text-xs mt-1 leading-relaxed">Start on this path — courses and jobs on your path will be highlighted for you.</div>
                </div>
                <svg x-show="!submitting || pickingKey !== matchedField?.key" class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <svg x-show="submitting && pickingKey === matchedField?.key" class="w-5 h-5 animate-spin text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </button>

            {{-- Pick a different path --}}
            <div class="text-center mt-4 mb-3">
                <button @click="showAllCareers = !showAllCareers" class="text-xs text-gray-500 hover:text-gray-300 transition-colors underline">
                    <span x-text="showAllCareers ? 'Hide other paths' : 'Prefer a different path? See all options'"></span>
                </button>
            </div>

            <div x-show="showAllCareers" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <template x-for="(meta, fkey) in fieldMeta" :key="fkey">
                    <button @click="pickField(fkey)"
                            :disabled="submitting"
                            x-show="fkey !== matchedField?.key"
                            class="text-left p-3 rounded-xl border border-white/8 bg-white/3 hover:border-indigo-500/40 hover:bg-indigo-500/8 transition-all flex items-center gap-3">
                        <span class="text-xl" x-text="meta.icon"></span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-white truncate" x-text="meta.label"></div>
                        </div>
                        <svg x-show="submitting && pickingKey === fkey" class="w-4 h-4 animate-spin text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </template>
            </div>

            <div class="mt-6 rounded-2xl p-4 text-xs text-gray-400 leading-relaxed" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                🎓 <span class="font-bold text-white">How you'll earn:</span> Course → Qualification → Job → Salary.
                Head to the <span class="text-indigo-300 font-bold">Opportunity Hub</span> in Pesa City after this.
            </div>

            <button @click="step = questions.length + 1" class="w-full mt-4 py-2.5 text-xs text-gray-500 hover:text-gray-300 transition-colors">
                ← Back to results
            </button>
        </div>
    </div>

</div>{{-- /main --}}

<script>
function careerQuiz() {
    return {
        step: 0,
        answers: [],
        submitting: false,
        pickingKey: null,
        showAllCareers: false,
        scores: {},
        matchedField: null,
        topScores: {},
        maxScore: 1,

        questions: @json($quizQuestions ?? \App\Http\Controllers\GameSetController::defaultQuizQuestions()),

        fieldMeta: @json($fieldMeta),

        get fieldLabels() { return this.fieldMeta; },
        get progressPct() {
            const total = this.questions.length;
            if (this.step === 0) return 0;
            if (this.step > total) return 100;
            return Math.round((this.step / total) * 100);
        },

        init() {
            this.answers = Array(this.questions.length).fill(null);
            this.scores  = {};
            Object.keys(this.fieldMeta).forEach(f => this.scores[f] = 0);
        },

        pickAnswer(qi, oi) {
            this.playSound('click');
            this.answers[qi] = oi;
        },

        nextQuestion(qi) {
            if (this.answers[qi] === null) return;
            this.playSound('click');
            const opt = this.questions[qi].options[this.answers[qi]];
            Object.entries(opt.fields).forEach(([field, pts]) => {
                this.scores[field] = (this.scores[field] ?? 0) + pts;
            });
            if (qi === this.questions.length - 1) {
                this.calculateResult();
            } else {
                this.step++;
            }
        },

        calculateResult() {
            // Sort fields by score
            const sorted = Object.entries(this.scores).sort(([,a],[,b]) => b - a);
            const topField = sorted[0][0];
            this.matchedField = { key: topField, ...this.fieldMeta[topField] };

            // Top 5 scores for display
            this.topScores = Object.fromEntries(sorted.slice(0, 5));
            this.maxScore = sorted[0][1] || 1;

            this.step = this.questions.length + 1;
            this.playSound('result');
            this.$nextTick(() => this.spawnConfetti());
        },

        async pickField(fieldKey) {
            if (!fieldKey) return;
            this.submitting = true;
            this.pickingKey = fieldKey;
            const errEl = document.getElementById('onboard-error-quiz');
            errEl.style.display = 'none';

            try {
                const res = await fetch('{{ route('life.onboard') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: fieldKey }),
                });
                const data = await res.json();
                if (data.success) {
                    this.playSound('levelup');
                    this.spawnConfetti();
                    // Send them straight to Pesa City — the Opportunity Hub is where earning starts
                    setTimeout(() => { window.location.href = '{{ route('world') }}'; }, 1200);
                } else {
                    errEl.textContent = data.error ?? 'Something went wrong.';
                    errEl.style.display = 'block';
                    this.submitting = false;
                    this.pickingKey = null;
                }
            } catch(e) {
                errEl.textContent = 'Network error — please try again.';
                errEl.style.display = 'block';
                this.submitting = false;
                this.pickingKey = null;
            }
        },

        spawnConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#6366f1','#a78bfa','#f59e0b','#10b981','#ec4899','#f43f5e','#0ea5e9'];
            for (let i = 0; i < 60; i++) {
                const el = document.createElement('div');
                el.className = 'confetti-piece';
                el.style.left = Math.random() * 100 + 'vw';
                el.style.top = '-10px';
                el.style.background = colors[Math.floor(Math.random() * colors.length)];
                el.style.width = (6 + Math.random() * 8) + 'px';
                el.style.height = (6 + Math.random() * 8) + 'px';
                el.style.animationDuration = (1.5 + Math.random() * 2) + 's';
                el.style.animationDelay = (Math.random() * 0.8) + 's';
                container.appendChild(el);
                setTimeout(() => el.remove(), 4000);
            }
        },

        playSound(type) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';

                if (type === 'click') {
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0.12, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
                    osc.start(); osc.stop(ctx.currentTime + 0.08);
                } else if (type === 'result') {
                    // Ascending arpeggio
                    [523, 659, 784, 1047].forEach((freq, i) => {
                        const o = ctx.createOscillator();
                        const g = ctx.createGain();
                        o.connect(g); g.connect(ctx.destination);
                        o.type = 'sine';
                        o.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.12);
                        g.gain.setValueAtTime(0.18, ctx.currentTime + i * 0.12);
                        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.12 + 0.25);
                        o.start(ctx.currentTime + i * 0.12);
                        o.stop(ctx.currentTime + i * 0.12 + 0.25);
                    });
                } else if (type === 'levelup') {
                    [523, 659, 784, 1047, 1319].forEach((freq, i) => {
                        const o = ctx.createOscillator();
                        const g = ctx.createGain();
                        o.connect(g); g.connect(ctx.destination);
                        o.type = 'triangle';
                        o.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.1);
                        g.gain.setValueAtTime(0.2, ctx.currentTime + i * 0.1);
                        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.1 + 0.3);
                        o.start(ctx.currentTime + i * 0.1);
                        o.stop(ctx.currentTime + i * 0.1 + 0.3);
                    });
                } else if (type === 'salary') {
                    [523, 784, 1047, 784, 1047, 1319].forEach((freq, i) => {
                        const o = ctx.createOscillator();
                        const g = ctx.createGain();
                        o.connect(g); g.connect(ctx.destination);
                        o.type = 'sine';
                        o.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.09);
                        g.gain.setValueAtTime(0.22, ctx.currentTime + i * 0.09);
                        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.09 + 0.2);
                        o.start(ctx.currentTime + i * 0.09);
                        o.stop(ctx.currentTime + i * 0.09 + 0.2);
                    });
                }
            } catch(e) {}
        },
    };
}
</script>
</body>
</html>
