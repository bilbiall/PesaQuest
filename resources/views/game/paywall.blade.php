<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PesaQuest – Unlock Full Access</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0a0916; }
        .plan-card { transition: all 0.2s ease; }
        .plan-card.selected {
            background: rgba(99,102,241,0.18);
            border-color: rgba(99,102,241,0.8);
            box-shadow: 0 0 24px rgba(99,102,241,0.25), inset 0 0 12px rgba(99,102,241,0.06);
        }
        .plan-card:not(.selected):hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.2);
        }
        .unlock-glow { animation: unlockPulse 3s ease-in-out infinite; }
        @keyframes unlockPulse {
            0%, 100% { filter: drop-shadow(0 0 12px rgba(99,102,241,0.4)); }
            50%       { filter: drop-shadow(0 0 28px rgba(167,139,250,0.7)); }
        }
        .coin-badge { animation: coinFloat 2s ease-in-out infinite; }
        @keyframes coinFloat {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-4px); }
        }
        @keyframes spinIn { from { opacity:0; transform: scale(0.8) rotate(-8deg); } to { opacity:1; transform: scale(1) rotate(0); } }
        .spin-in { animation: spinIn 0.4s cubic-bezier(.34,1.56,.64,1) forwards; }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased"
      style="background: radial-gradient(ellipse at 20% 0%, rgba(99,102,241,0.12) 0%, transparent 55%), radial-gradient(ellipse at 80% 100%, rgba(139,92,246,0.08) 0%, transparent 50%), #0a0916;"
      x-data="paywallPage()" x-init="init()">

    <div class="min-h-screen flex flex-col">

        {{-- Top nav --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Dashboard
            </a>
            <div class="flex items-center gap-2">
                <img src="{{ asset('moski-logo.png') }}" alt="PesaQuest" class="h-7 w-7 rounded-lg object-cover">
                <span class="text-sm font-bold text-white/80">PesaQuest</span>
            </div>
            <div class="w-20"></div>
        </div>

        <div class="flex-1 overflow-y-auto">

            {{-- Hero --}}
            <div class="text-center px-4 pt-10 pb-6">
                <div class="unlock-glow text-6xl mb-4 inline-block">🔐</div>
                <h1 class="text-3xl sm:text-4xl font-black mb-3 leading-tight">
                    You've used your
                    <span class="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">3 free decisions</span>
                </h1>
                <p class="text-gray-400 max-w-md mx-auto text-base leading-relaxed">
                    Subscribe to keep playing <strong class="text-white">{{ $node->title }}</strong> and unlock unlimited financial scenarios.
                </p>
            </div>

            {{-- Plans grid --}}
            <div class="max-w-2xl mx-auto px-4 mb-6">
                <p class="text-center text-xs text-indigo-400 font-semibold uppercase tracking-widest mb-4">Choose your plan</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($plans as $plan)
                    @php
                        $savings = match($plan->months) {
                            3  => 'Save 11%',
                            6  => 'Save 16%',
                            12 => 'Save 30%',
                            default => null,
                        };
                    @endphp
                    <div
                        @click="selectPlan({{ $plan->id }}, '{{ $plan->key }}', '{{ addslashes($plan->name) }}', {{ $plan->price_kes }}, {{ $plan->months }})"
                        :class="selectedPlanId === {{ $plan->id }} ? 'selected' : ''"
                        class="plan-card relative border border-white/10 bg-white/4 rounded-2xl p-4 cursor-pointer"
                    >
                        @if($plan->is_featured)
                        <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-xs font-black px-2.5 py-0.5 rounded-full whitespace-nowrap shadow-lg">
                            BEST VALUE
                        </div>
                        @endif
                        @if($savings)
                        <div class="absolute top-2 right-2 bg-amber-500/80 text-white text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">
                            {{ $savings }}
                        </div>
                        @endif

                        <div class="text-center mt-1">
                            <p class="text-gray-500 text-xs uppercase tracking-widest mb-1 font-semibold">
                                {{ $plan->durationLabel() }}
                            </p>
                            <p class="text-white font-black text-2xl mb-0.5">
                                {{ number_format($plan->price_kes) }}
                            </p>
                            <p class="text-gray-500 text-xs">
                                @if($plan->months === 1) KES/mo
                                @else KES {{ number_format(round($plan->price_kes / $plan->months)) }}/mo
                                @endif
                            </p>
                        </div>

                        <div x-show="selectedPlanId === {{ $plan->id }}"
                             class="mt-2 text-center text-indigo-400 text-xs font-bold flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Selected
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment area --}}
            <div class="max-w-sm mx-auto px-4 pb-10">

                {{-- Step: select --}}
                <div x-show="step === 'select'">
                    <div class="rounded-2xl p-5 mb-4" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1);">

                        <h3 class="text-white font-bold text-lg mb-1">Pay via M-Pesa</h3>
                        <p class="text-gray-500 text-sm mb-4">Enter your Safaricom number to receive a payment prompt.</p>

                        <div class="mb-4">
                            <label class="text-gray-400 text-xs font-semibold uppercase tracking-wide block mb-1.5">Safaricom Number</label>
                            <div class="flex items-center gap-2 rounded-xl px-4 py-3 focus-within:border-indigo-500/60 transition-colors"
                                 style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12);">
                                <span class="text-gray-400 shrink-0">🇰🇪</span>
                                <input
                                    type="tel"
                                    x-model="phone"
                                    @keydown.enter="initiatePay()"
                                    placeholder="0712 345 678"
                                    class="bg-transparent text-white placeholder-gray-600 flex-1 outline-none font-mono text-base"
                                    maxlength="13"
                                >
                            </div>
                            <p class="text-gray-600 text-xs mt-1">Format: 07XXXXXXXX or 254XXXXXXXXX</p>
                        </div>

                        <template x-if="error">
                            <div class="mb-4 rounded-xl p-3 flex items-start gap-2 text-sm"
                                 style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3);">
                                <span>⚠️</span><span class="text-red-300" x-text="error"></span>
                            </div>
                        </template>

                        <button
                            @click="initiatePay()"
                            :disabled="!selectedPlanId || !phone || paying"
                            :class="(!selectedPlanId || !phone || paying) ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5 hover:shadow-lg hover:shadow-indigo-500/30'"
                            class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 text-base"
                        >
                            <template x-if="paying">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </template>
                            <template x-if="!paying"><span>📱</span></template>
                            <span x-text="paying ? 'Sending prompt…' : (selectedPlanId ? 'Pay Ksh ' + selectedPrice.toLocaleString() : 'Select a plan first')"></span>
                        </button>

                        <p class="text-gray-600 text-xs text-center mt-3">🔒 Secured by Safaricom Daraja API · No card required</p>
                    </div>

                    {{-- Features --}}
                    <div class="space-y-2">
                        @foreach([
                            ['🎮', 'Unlimited scenarios & decisions'],
                            ['🏆', 'Leaderboard ranking & badges'],
                            ['🧮', 'Smart Money Tools (Bajeti, Lengo…)'],
                            ['📊', 'Progress analytics & financial diary'],
                        ] as [$icon, $text])
                        <div class="flex items-center gap-3 text-sm rounded-xl px-3 py-2"
                             style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06);">
                            <span class="text-base coin-badge">{{ $icon }}</span>
                            <span class="text-gray-300">{{ $text }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Step: waiting --}}
                <div x-show="step === 'waiting'" class="text-center rounded-2xl p-8"
                     style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1);">
                    <div class="text-5xl mb-4" style="animation: unlockPulse 1.5s ease-in-out infinite;">📱</div>
                    <h3 class="text-white font-bold text-xl mb-2">Check your phone!</h3>
                    <p class="text-gray-300 text-sm mb-1">
                        Payment request of <strong class="text-indigo-400">Ksh <span x-text="selectedPrice.toLocaleString()"></span></strong>
                        sent to <strong class="text-white" x-text="phone"></strong>.
                    </p>
                    <p class="text-gray-500 text-sm mb-6">Enter your M-Pesa PIN to confirm.</p>

                    <div class="flex items-center justify-center gap-2 text-gray-500 text-sm mb-6">
                        <svg class="animate-spin w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Waiting… (<span x-text="pollCountdown"></span>s)
                    </div>

                    <button @click="cancelPoll()" class="text-gray-600 hover:text-gray-400 text-sm underline transition-colors">
                        Cancel / Try again
                    </button>
                </div>

                {{-- Step: success --}}
                <div x-show="step === 'success'" class="text-center rounded-2xl p-8 spin-in"
                     style="background:rgba(99,102,241,0.12); border:1px solid rgba(99,102,241,0.4);">
                    <div class="text-6xl mb-4">🎉</div>
                    <h3 class="text-indigo-300 font-black text-2xl mb-2">You're unlocked!</h3>
                    <p class="text-gray-300 text-sm mb-2">
                        <strong class="text-white" x-text="selectedPlanName"></strong> subscription is now <strong class="text-indigo-400">active</strong>.
                    </p>
                    <p class="text-gray-500 text-xs mb-1" x-show="receiptCode">
                        Receipt: <span class="font-mono text-gray-300" x-text="receiptCode"></span>
                    </p>
                    <p class="text-gray-400 text-sm mb-6">Time to master your finances! 💪</p>
                    <a href="{{ route('game.play') }}"
                       class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold px-8 py-3 rounded-xl hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 transition-all">
                        Continue Playing →
                    </a>
                </div>

                {{-- Step: failed --}}
                <div x-show="step === 'failed'" class="text-center rounded-2xl p-8"
                     style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.3);">
                    <div class="text-5xl mb-4">❌</div>
                    <h3 class="text-red-300 font-bold text-xl mb-2">Payment Failed</h3>
                    <p class="text-gray-500 text-sm mb-2" x-show="failReason" x-text="failReason"></p>
                    <p class="text-gray-500 text-sm mb-6">Check your M-Pesa balance and try again.</p>
                    <button @click="retry()"
                        class="bg-white/10 hover:bg-white/15 text-white font-semibold px-6 py-3 rounded-xl transition-colors border border-white/15">
                        Try Again
                    </button>
                </div>

                {{-- Back link (shown only on select step) --}}
                <div x-show="step === 'select'" class="flex gap-2 mt-4">
                    <a href="{{ route('dashboard') }}"
                       class="flex-1 text-center text-gray-500 hover:text-gray-300 border border-white/10 hover:border-white/20 py-2.5 rounded-xl text-sm transition-colors">
                        Dashboard
                    </a>
                    <a href="{{ route('landing') }}"
                       class="flex-1 text-center text-gray-500 hover:text-gray-300 border border-white/10 hover:border-white/20 py-2.5 rounded-xl text-sm transition-colors">
                        Learn More
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
    function paywallPage() {
        return {
            step: 'select',
            selectedPlanId: null,
            selectedPlanKey: '',
            selectedPlanName: '',
            selectedPrice: 0,
            selectedMonths: 0,
            phone: '',
            paying: false,
            error: null,
            checkoutRequestId: null,
            pollInterval: null,
            pollCountdown: 120,
            receiptCode: null,
            failReason: null,

            init() {},

            selectPlan(id, key, name, price, months) {
                this.selectedPlanId   = id;
                this.selectedPlanKey  = key;
                this.selectedPlanName = name;
                this.selectedPrice    = price;
                this.selectedMonths   = months;
                this.error = null;
            },

            async initiatePay() {
                if (!this.selectedPlanId || !this.phone || this.paying) return;

                this.paying = true;
                this.error  = null;

                try {
                    const res = await fetch(`/subscribe/${this.selectedPlanKey}/pay`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone: this.phone }),
                    });

                    const data = await res.json();

                    if (!res.ok || data.error) {
                        this.error = data.error || 'Payment initiation failed. Please try again.';
                        return;
                    }

                    this.checkoutRequestId = data.checkout_request_id;
                    this.step = 'waiting';
                    this.startPolling();

                } catch (e) {
                    this.error = 'Network error. Please check your connection and try again.';
                } finally {
                    this.paying = false;
                }
            },

            startPolling() {
                this.pollCountdown = 120;

                const tick = async () => {
                    if (this.step !== 'waiting') return;

                    this.pollCountdown--;

                    if (this.pollCountdown <= 0) {
                        this.cancelPoll();
                        this.step = 'failed';
                        this.failReason = 'Payment timed out. Please try again.';
                        return;
                    }

                    try {
                        const res  = await fetch(`/subscribe/status?checkout_request_id=${this.checkoutRequestId}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json();

                        if (data.status === 'completed') {
                            this.receiptCode = data.receipt;
                            this.step = 'success';
                            this.cancelPoll();
                        } else if (data.status === 'failed' || data.status === 'cancelled') {
                            this.failReason = data.reason || 'Payment was declined or cancelled.';
                            this.step = 'failed';
                            this.cancelPoll();
                        }
                    } catch (e) {
                        // network blip — keep polling
                    }
                };

                this.pollInterval = setInterval(tick, 3000);
            },

            cancelPoll() {
                if (this.pollInterval) {
                    clearInterval(this.pollInterval);
                    this.pollInterval = null;
                }
                if (this.step === 'waiting') {
                    this.step = 'select';
                }
            },

            retry() {
                this.step       = 'select';
                this.failReason = null;
                this.receiptCode = null;
                this.error      = null;
            },
        };
    }
    </script>
</body>
</html>
