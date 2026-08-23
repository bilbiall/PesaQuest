<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Upgrade to Premium
        </h2>
    </x-slot>

    <div class="py-10 bg-gradient-to-br from-slate-900 via-green-950 to-slate-900 min-h-screen"
         x-data="subscriptionPage()" x-init="init()">

        {{-- Active subscription banner --}}
        @if($activeSub)
        <div class="max-w-5xl mx-auto px-4 mb-6">
            <div class="bg-green-500/20 border border-green-400/40 rounded-2xl p-4 flex items-center gap-4">
                <span class="text-2xl">✅</span>
                <div>
                    <p class="text-green-300 font-semibold text-lg">You have an active subscription!</p>
                    <p class="text-green-400/80 text-sm">
                        {{ ucfirst($activeSub->plan) }} plan — expires
                        <strong>{{ $activeSub->ends_at?->format('d M Y') ?? 'Never' }}</strong>.
                        Renewing now won't waste any time — it'll queue up and start the moment this one ends.
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Upcoming (stacked renewal) banner --}}
        @if($upcomingSub)
        <div class="max-w-5xl mx-auto px-4 mb-6">
            <div class="bg-indigo-500/20 border border-indigo-400/40 rounded-2xl p-4 flex items-center gap-4">
                <span class="text-2xl">📅</span>
                <div>
                    <p class="text-indigo-300 font-semibold text-lg">You already have a renewal scheduled!</p>
                    <p class="text-indigo-400/80 text-sm">
                        {{ ucfirst($upcomingSub->plan) }} plan is paid for and queued — it will automatically start on
                        <strong>{{ $upcomingSub->starts_at?->format('d M Y') }}</strong> and run until
                        <strong>{{ $upcomingSub->ends_at?->format('d M Y') }}</strong>. No gap in your access.
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Section heading --}}
        <div class="max-w-5xl mx-auto px-4 text-center mb-10">
            <div class="inline-flex items-center gap-2 bg-green-500/20 border border-green-400/30 rounded-full px-4 py-1.5 mb-4">
                <span class="text-green-400 text-sm font-semibold">💎 PREMIUM ACCESS</span>
            </div>
            <h1 class="text-xl md:text-2xl font-black text-white mb-2">
                Unlock Everything in <span class="text-green-400">PesaQuest</span>
            </h1>
            <p class="text-slate-300 text-sm max-w-xl mx-auto">
                Get unlimited scenarios, Smart Money Tools, leaderboard ranking, and priority support — all via M-Pesa.
            </p>
        </div>

        {{-- ── INDIVIDUAL PLANS ────────────────────────────────────────────── --}}
        @if($individualPlans->count())
        <div class="max-w-5xl mx-auto px-4 mb-10">
            <h2 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-sm">👤</span>
                Individual Plans
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($individualPlans as $plan)
                @php
                    $savings = match($plan->months) {
                        3  => '11%',
                        6  => '16%',
                        12 => '30%',
                        default => null,
                    };
                @endphp
                <div
                    @click="selectPlan({{ $plan->id }}, '{{ $plan->key }}', '{{ addslashes($plan->name) }}', {{ $plan->price_kes }}, {{ $plan->months }}, 'individual', null)"
                    :class="selectedPlanId === {{ $plan->id }}
                        ? 'ring-2 ring-indigo-400 bg-indigo-900/40 border-indigo-400/60'
                        : 'border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20'"
                    class="relative border rounded-xl p-4 cursor-pointer transition-all duration-200"
                >
                    @if($plan->is_featured)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-400 to-purple-500 text-white text-xs font-black px-3 py-1 rounded-full shadow-lg whitespace-nowrap">
                        BEST VALUE
                    </div>
                    @endif
                    @if($savings)
                    <div class="absolute top-3 right-3 bg-orange-500/80 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        Save {{ $savings }}
                    </div>
                    @endif
                    <div class="text-center mt-2">
                        <p class="text-slate-400 text-xs font-semibold uppercase tracking-widest mb-1">{{ $plan->durationLabel() }}</p>
                        <p class="text-white font-black text-xl mb-0.5">Ksh {{ number_format($plan->price_kes) }}</p>
                        <p class="text-slate-400 text-xs mb-3">
                            @if($plan->months === 1) per month
                            @else Ksh {{ number_format(round($plan->price_kes / $plan->months)) }}/mo
                            @endif
                        </p>
                        @if($plan->description)
                        <p class="text-slate-300 text-xs leading-snug">{{ $plan->description }}</p>
                        @endif
                    </div>
                    <div x-show="selectedPlanId === {{ $plan->id }}"
                         class="mt-3 text-center text-indigo-400 text-sm font-bold flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Selected
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── SCHOOL PLANS ────────────────────────────────────────────────── --}}
        @if($schoolPlans->count())
        <div class="max-w-5xl mx-auto px-4 mb-10">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-7 h-7 rounded-lg bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-sm">🏫</span>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">School Plans</h2>
                    <p class="text-slate-500 text-xs">One subscription covers all your students — they get full access while active</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($schoolPlans as $plan)
                <div
                    @click="selectPlan({{ $plan->id }}, '{{ $plan->key }}', '{{ addslashes($plan->name) }}', {{ $plan->price_kes }}, {{ $plan->months }}, 'school', {{ $plan->seats ?? 0 }})"
                    :class="selectedPlanId === {{ $plan->id }}
                        ? 'ring-2 ring-emerald-400 bg-emerald-900/30 border-emerald-400/60'
                        : 'border-emerald-900/40 bg-emerald-950/30 hover:bg-emerald-900/20 hover:border-emerald-700/50'"
                    class="relative border rounded-xl p-4 cursor-pointer transition-all duration-200"
                >
                    @if($plan->is_featured)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-emerald-400 to-green-500 text-slate-900 text-xs font-black px-3 py-1 rounded-full shadow-lg whitespace-nowrap">
                        POPULAR CHOICE
                    </div>
                    @endif

                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-400">
                            🏫 {{ $plan->durationLabel() }}
                        </span>
                        <span class="text-emerald-300 font-black text-xs">{{ number_format($plan->seats ?? 0) }} seats</span>
                    </div>

                    <p class="text-white font-bold text-base mb-0.5">{{ $plan->name }}</p>
                    <p class="text-white font-black text-2xl mb-1">Ksh {{ number_format($plan->price_kes) }}</p>
                    <p class="text-slate-400 text-xs mb-3">
                        Ksh {{ number_format(round($plan->price_kes / ($plan->seats ?? 1))) }} per student
                    </p>

                    @if($plan->description)
                    <div class="text-slate-300 text-xs leading-snug whitespace-pre-line">{{ $plan->description }}</div>
                    @endif

                    <div x-show="selectedPlanId === {{ $plan->id }}"
                         class="mt-3 text-center text-emerald-400 text-sm font-bold flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Selected
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── PAYMENT MODAL — opens when a plan is clicked ─────────────────── --}}
        <div x-show="payModal" x-cloak x-transition.opacity
             class="fixed inset-0 flex items-center justify-center p-4"
             style="z-index:9990;background:rgba(0,0,0,.85);backdrop-filter:blur(12px);overflow-y:auto;overscroll-behavior:contain;"
             @click.self="closePayModal()"
             @keydown.escape.window="closePayModal()">
        <div class="w-full max-w-md" style="margin:auto;">

            {{-- Step 1: select --}}
            <div x-show="step === 'select'" class="bg-slate-900 border border-white/15 rounded-2xl p-4 sm:p-6 relative"
                 style="background:linear-gradient(160deg,#0f172a,#111c33);max-height:92vh;overflow-y:auto;">
                <button @click="closePayModal()"
                        class="absolute top-4 right-4 w-8 h-8 rounded-xl flex items-center justify-center text-slate-500 hover:text-white"
                        style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">✕</button>

                <h3 class="text-white font-bold text-lg sm:text-xl mb-1">Pay via M-Pesa</h3>
                <p class="text-slate-400 text-sm mb-4">
                    <span class="text-emerald-400 font-bold" x-text="selectedPlanName"></span>
                    <span x-show="selectedMonths"> · <span x-text="selectedMonths"></span> month<span x-show="selectedMonths > 1">s</span></span>
                    <span x-show="selectedSeats"> · <span x-text="selectedSeats"></span> seats</span>
                </p>

                {{-- School name — only shown for school plans --}}
                <div x-show="selectedPlanType === 'school'" x-transition class="mb-4">
                    <label class="text-slate-300 text-sm font-semibold block mb-1.5">School Name</label>
                    <input
                        type="text"
                        x-model="schoolName"
                        placeholder="e.g. Nairobi Academy"
                        class="w-full bg-slate-800 border border-emerald-700/50 focus:border-emerald-400 text-white placeholder-slate-500 rounded-xl px-4 py-3 outline-none transition-colors"
                    >
                    <p class="text-slate-500 text-xs mt-1">This name will appear on your school portal.</p>
                </div>

                <div class="mb-4">
                    <label class="text-slate-300 text-sm font-semibold block mb-1.5">Safaricom Number</label>
                    <div class="flex items-center gap-2 bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 focus-within:border-green-400 transition-colors">
                        <span class="text-slate-400 text-sm font-mono shrink-0">🇰🇪</span>
                        <input
                            type="tel"
                            x-model="phone"
                            @keydown.enter="initiatePay()"
                            placeholder="0712 345 678"
                            class="bg-transparent text-white placeholder-slate-500 flex-1 outline-none text-base sm:text-lg font-mono"
                            maxlength="13"
                        >
                    </div>
                    <p class="text-slate-500 text-xs mt-1">Format: 07XXXXXXXX or 254XXXXXXXXX</p>
                </div>

                {{-- Coupon --}}
                <div class="mb-4">
                    <button type="button" @click="couponOpen = !couponOpen"
                            class="flex items-center gap-2 text-sm font-semibold text-amber-400 hover:text-amber-300 transition-colors">
                        <span>🎟️ Have a coupon?</span>
                        <svg class="w-4 h-4 transition-transform" :class="couponOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="couponOpen" x-transition x-cloak class="mt-3">
                        <div class="flex gap-2">
                            <input type="text" x-model="couponCode"
                                   @input="couponApplied = null; couponError = null"
                                   @keydown.enter.prevent="checkCoupon()"
                                   placeholder="COUPON CODE" maxlength="30"
                                   class="flex-1 bg-slate-800 border border-amber-700/40 focus:border-amber-400 text-white placeholder-slate-600 rounded-xl px-4 py-2.5 outline-none transition-colors font-mono uppercase text-sm min-w-0">
                            <button type="button" @click="checkCoupon()"
                                    :disabled="!couponCode.trim() || couponChecking"
                                    class="px-4 py-2.5 rounded-xl text-sm font-bold text-amber-300 shrink-0 transition-all disabled:opacity-40"
                                    style="background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.35);">
                                <span x-show="!couponChecking">Apply</span>
                                <span x-show="couponChecking">…</span>
                            </button>
                        </div>
                        <p x-show="couponError" x-cloak class="text-red-400 text-xs mt-1.5" x-text="couponError"></p>
                        <p x-show="couponApplied" x-cloak class="text-emerald-400 text-xs mt-1.5 font-bold">
                            ✓ <span x-text="couponApplied?.label"></span> applied — you save Ksh <span x-text="(couponApplied?.discount ?? 0).toLocaleString()"></span>
                        </p>
                    </div>
                </div>

                {{-- Order total --}}
                <div class="mb-4 rounded-xl p-4" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);">
                    <div class="flex items-center justify-between text-sm text-slate-400">
                        <span x-text="selectedPlanName"></span>
                        <span>Ksh <span x-text="selectedPrice.toLocaleString()"></span></span>
                    </div>
                    <div x-show="couponApplied" x-cloak class="flex items-center justify-between text-sm text-amber-400 mt-1.5">
                        <span>Coupon (<span x-text="couponCode.toUpperCase()"></span>)</span>
                        <span>− Ksh <span x-text="(couponApplied?.discount ?? 0).toLocaleString()"></span></span>
                    </div>
                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-white/10">
                        <span class="text-white font-bold text-sm">Total</span>
                        <span class="text-emerald-400 font-black text-base sm:text-xl truncate">Ksh <span x-text="totalPrice.toLocaleString()"></span></span>
                    </div>
                </div>

                <template x-if="error">
                    <div class="mb-4 bg-red-500/20 border border-red-400/40 rounded-xl p-3 text-red-300 text-sm flex items-start gap-2">
                        <span>⚠️</span><span x-text="error"></span>
                    </div>
                </template>

                <button
                    @click="initiatePay()"
                    :disabled="!selectedPlanId || !phone || paying || (selectedPlanType === 'school' && !schoolName.trim())"
                    :class="(!selectedPlanId || !phone || paying || (selectedPlanType === 'school' && !schoolName.trim())) ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-green-500/30 hover:shadow-lg hover:-translate-y-0.5'"
                    class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3.5 sm:py-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-lg"
                >
                    <template x-if="paying">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </template>
                    <template x-if="!paying">
                        <span>📱</span>
                    </template>
                    <span x-text="paying ? 'Sending prompt…' : (totalPrice === 0 ? 'Activate Free 🎉' : 'Pay Ksh ' + totalPrice.toLocaleString())"></span>
                </button>

                <p class="text-slate-500 text-xs text-center mt-3">
                    🔒 Secured by Safaricom Daraja API. No card required.
                </p>
            </div>

            {{-- Step 2: waiting --}}
            <div x-show="step === 'waiting'" class="bg-white/8 border border-white/15 rounded-2xl p-5 sm:p-8 text-center">
                <div class="mb-3 sm:mb-4 flex justify-center"><x-icon name="phone" class="w-12 h-12 sm:w-16 sm:h-16 text-white animate-bounce" /></div>
                <h3 class="text-white font-bold text-lg sm:text-xl mb-2">Check your phone!</h3>
                <p class="text-slate-300 text-sm sm:text-base mb-2">
                    A payment request of <strong class="text-green-400">Ksh <span x-text="totalPrice.toLocaleString()"></span></strong>
                    has been sent to <strong class="text-white" x-text="phone"></strong>.
                </p>
                <p class="text-slate-400 text-sm mb-6">Enter your M-Pesa PIN to complete the payment.</p>
                <div class="flex items-center justify-center gap-2 text-slate-400 text-sm mb-6">
                    <svg class="animate-spin w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Waiting for confirmation… (<span x-text="pollCountdown"></span>s)
                </div>
                <button @click="cancelPoll()" class="text-slate-500 hover:text-slate-300 text-sm underline transition-colors">
                    Cancel / Try again
                </button>
            </div>

            {{-- Step 3: individual success --}}
            <div x-show="step === 'success'" class="bg-green-900/30 border border-green-400/40 rounded-2xl p-5 sm:p-8 text-center">
                <div class="text-5xl sm:text-7xl mb-3 sm:mb-4">🎉</div>
                <h3 class="text-green-300 font-black text-lg sm:text-2xl mb-2">Payment Confirmed!</h3>
                <p class="text-slate-300 text-sm sm:text-base mb-2">
                    Your <strong class="text-white" x-text="selectedPlanName"></strong> subscription is now <strong class="text-green-400">active</strong>.
                </p>
                <p class="text-slate-400 text-sm mb-1" x-show="receiptCode">
                    Receipt: <span class="font-mono text-slate-200" x-text="receiptCode"></span>
                </p>
                <p class="text-slate-400 text-sm mb-6">
                    You now have full access to all PesaQuest features. Happy learning! 💪
                </p>
                <a href="{{ route('game.play') }}"
                   class="inline-block bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold px-6 py-2.5 sm:px-8 sm:py-3 rounded-xl hover:shadow-lg hover:shadow-green-500/30 transition-all text-sm sm:text-base">
                    Start Playing →
                </a>
            </div>

            {{-- Step 3b: school success --}}
            <div x-show="step === 'school-success'" class="bg-emerald-900/30 border border-emerald-400/40 rounded-2xl p-5 sm:p-8 text-center">
                <div class="mb-3 sm:mb-4 flex justify-center"><x-icon name="graduation" class="w-14 h-14 sm:w-20 sm:h-20 text-emerald-300" /></div>
                <h3 class="text-emerald-300 font-black text-lg sm:text-2xl mb-2">School Subscription Active!</h3>
                <p class="text-slate-300 text-sm sm:text-base mb-2">
                    <strong class="text-white" x-text="schoolName"></strong> — <strong class="text-emerald-400" x-text="selectedPlanName"></strong> is now active.
                </p>
                <p class="text-slate-400 text-sm mb-1" x-show="receiptCode">
                    Receipt: <span class="font-mono text-slate-200" x-text="receiptCode"></span>
                </p>
                <template x-if="portalUrl">
                    <div class="my-5 bg-emerald-950/60 border border-emerald-500/30 rounded-xl p-4">
                        <p class="text-emerald-300 text-sm font-bold mb-2">Your School Portal</p>
                        <p class="text-slate-400 text-xs mb-3">Share this link with your school admin to add students:</p>
                        <div class="flex items-center gap-2">
                            <input type="text" :value="portalUrl" readonly
                                   class="flex-1 bg-slate-800 border border-emerald-700/40 text-emerald-300 text-xs font-mono rounded-lg px-3 py-2 outline-none min-w-0">
                            <button @click="copyPortal()" class="px-3 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors whitespace-nowrap"
                                    x-text="copied ? '✓ Copied!' : 'Copy'"></button>
                        </div>
                    </div>
                </template>
                <div class="flex gap-3 justify-center mt-4">
                    <template x-if="portalUrl">
                        <a :href="portalUrl" class="inline-block bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold px-4 py-2.5 sm:px-6 sm:py-3 rounded-xl hover:shadow-lg transition-all text-sm">
                            Open School Portal →
                        </a>
                    </template>
                    <a href="{{ route('dashboard') }}" class="inline-block bg-slate-700 hover:bg-slate-600 text-white font-semibold px-4 py-2.5 sm:px-6 sm:py-3 rounded-xl transition-colors text-sm">
                        Back to Dashboard
                    </a>
                </div>
            </div>

            {{-- Step 4: failed --}}
            <div x-show="step === 'failed'" class="bg-red-900/30 border border-red-400/40 rounded-2xl p-5 sm:p-8 text-center">
                <div class="mb-3 sm:mb-4 flex justify-center"><x-icon name="x-circle" class="w-12 h-12 sm:w-16 sm:h-16 text-red-400" /></div>
                <h3 class="text-red-300 font-bold text-lg sm:text-xl mb-2">Payment Failed</h3>
                <p class="text-slate-400 text-sm mb-2" x-show="failReason" x-text="failReason"></p>
                <p class="text-slate-400 text-sm mb-6">Please check your M-Pesa balance and try again.</p>
                <button @click="retry()"
                    class="bg-slate-700 hover:bg-slate-600 text-white font-semibold px-4 py-2.5 sm:px-6 sm:py-3 rounded-xl transition-colors text-sm">
                    Try Again
                </button>
            </div>
        </div>
        </div>

        {{-- Features list --}}
        <div class="max-w-5xl mx-auto px-4 mt-12">
            <h2 class="text-white font-bold text-xl text-center mb-6">What's included in Premium</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach([
                    ['🎮', 'Unlimited Scenarios', 'Play all financial scenarios without limits or paywalls.'],
                    ['🧮', 'Smart Money Tools', 'Access Bajeti, Lengo, Matumizi & Ukuaji financial calculators.'],
                    ['🏆', 'Leaderboard Ranking', 'Compete with players nationwide and see your ranking.'],
                    ['🔔', 'Priority Notifications', 'Get alerts for new scenarios, bonus rounds, and challenges.'],
                    ['📊', 'Progress Analytics', 'See detailed stats on your financial decision-making.'],
                    ['💬', 'Premium Support', 'Get direct help from the PesaQuest team via email.'],
                ] as [$icon, $title, $desc])
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 flex gap-3">
                    <span class="text-2xl shrink-0 mt-0.5">{{ $icon }}</span>
                    <div>
                        <p class="text-white font-semibold text-sm">{{ $title }}</p>
                        <p class="text-slate-400 text-xs mt-0.5 leading-snug">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-10 pb-10">
            <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-slate-300 text-sm transition-colors">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <script>
    function subscriptionPage() {
        return {
            step: 'select',
            selectedPlanId: null,
            selectedPlanKey: '',
            selectedPlanName: '',
            selectedPlanType: 'individual',
            selectedPrice: 0,
            selectedMonths: 0,
            selectedSeats: null,
            phone: '',
            schoolName: '',
            paying: false,
            error: null,
            checkoutRequestId: null,
            pollInterval: null,
            pollCountdown: 120,
            receiptCode: null,
            failReason: null,
            portalUrl: null,
            copied: false,
            payModal: false,
            couponOpen: false,
            couponCode: '',
            couponChecking: false,
            couponApplied: null,   // { label, discount, total }
            couponError: null,

            init() {},

            get totalPrice() {
                return this.couponApplied ? this.couponApplied.total : this.selectedPrice;
            },

            selectPlan(id, key, name, price, months, type, seats) {
                this.selectedPlanId   = id;
                this.selectedPlanKey  = key;
                this.selectedPlanName = name;
                this.selectedPrice    = price;
                this.selectedMonths   = months;
                this.selectedPlanType = type;
                this.selectedSeats    = seats;
                this.error = null;
                // Coupon validity is plan-specific — reset on plan change
                this.couponApplied = null;
                this.couponError   = null;
                this.step     = 'select';
                this.payModal = true;
                document.body.style.overflow = 'hidden';
            },

            closePayModal() {
                if (this.step === 'waiting') this.cancelPoll();
                this.payModal = false;
                this.error = null;
                document.body.style.overflow = '';
            },

            async checkCoupon() {
                const code = this.couponCode.trim();
                if (!code || !this.selectedPlanKey || this.couponChecking) return;

                this.couponChecking = true;
                this.couponApplied  = null;
                this.couponError    = null;

                try {
                    const res = await fetch(`/subscribe/${this.selectedPlanKey}/coupon-check`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ code }),
                    });
                    const data = await res.json();

                    if (data.valid) {
                        this.couponApplied = { label: data.label, discount: data.discount, total: data.total };
                    } else {
                        this.couponError = data.reason || 'Invalid coupon.';
                    }
                } catch (e) {
                    this.couponError = 'Could not check the coupon. Try again.';
                } finally {
                    this.couponChecking = false;
                }
            },

            async initiatePay() {
                if (!this.selectedPlanId || !this.phone || this.paying) return;
                if (this.selectedPlanType === 'school' && !this.schoolName.trim()) return;

                this.paying = true;
                this.error  = null;

                try {
                    const payload = { phone: this.phone };
                    if (this.selectedPlanType === 'school') {
                        payload.school_name = this.schoolName.trim();
                    }
                    if (this.couponApplied && this.couponCode.trim()) {
                        payload.coupon = this.couponCode.trim();
                    }

                    const res = await fetch(`/subscribe/${this.selectedPlanKey}/pay`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (!res.ok || data.error) {
                        this.error = data.error || Object.values(data.errors ?? {}).flat().join(' ') || 'Payment initiation failed. Please try again.';
                        return;
                    }

                    this.checkoutRequestId = data.checkout_request_id;

                    // 100%-off coupon — subscription is already active, no M-Pesa prompt
                    if (data.free) {
                        if (this.selectedPlanType === 'school') {
                            try {
                                const s = await fetch(`/subscribe/status?checkout_request_id=${this.checkoutRequestId}`, { headers: { 'Accept': 'application/json' } });
                                const sd = await s.json();
                                if (sd.portal_url) this.portalUrl = sd.portal_url;
                            } catch (e) {}
                            this.step = 'school-success';
                        } else {
                            this.step = 'success';
                        }
                        return;
                    }

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
                            this.cancelPoll();
                            if (data.portal_url) {
                                this.portalUrl = data.portal_url;
                                this.step = 'school-success';
                            } else {
                                this.step = 'success';
                            }
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

            async copyPortal() {
                if (!this.portalUrl) return;
                try {
                    await navigator.clipboard.writeText(this.portalUrl);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2500);
                } catch {}
            },

            retry() {
                this.step        = 'select';
                this.failReason  = null;
                this.receiptCode = null;
                this.portalUrl   = null;
                this.error       = null;
            },
        };
    }
    </script>

        {{-- ── M-PESA TRANSACTION HISTORY ──────────────────────────────── --}}
        @if($transactions->isNotEmpty())
        <div class="max-w-5xl mx-auto px-4 pb-12 mt-8">
            <h2 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-green-500/20 border border-green-500/30 flex items-center justify-center text-sm">📋</span>
                M-Pesa Transaction History
            </h2>
            <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="border-b border-white/10">
                        <tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Plan</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3">Receipt</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $txn)
                        <tr class="border-b border-white/5 last:border-0 hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3 text-slate-300 whitespace-nowrap">
                                {{ $txn->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-white font-medium">
                                {{ $txn->plan?->name ?? '—' }}
                                @if($txn->school_name)
                                    <span class="text-xs text-slate-400 block">{{ $txn->school_name }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-emerald-400 font-bold whitespace-nowrap">
                                KES {{ number_format($txn->amount) }}
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-300 text-xs">
                                {{ $txn->mpesa_receipt ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($txn->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-500/15 text-green-400 border border-green-500/30">
                                        ✓ Paid
                                    </span>
                                @elseif($txn->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                        ⏳ Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-500/15 text-red-400 border border-red-500/30">
                                        ✗ Failed
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-slate-600 mt-2">Showing your most recent 20 transactions. Pending transactions update once M-Pesa confirms payment.</p>
        </div>
        @endif
</x-app-layout>
