<x-guest-layout>

    <style>
        @keyframes envelopeBounce {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50%       { transform: translateY(-10px) rotate(2deg); }
        }
        @keyframes dotPulse {
            0%, 100% { opacity: 0.4; transform: scale(0.8); }
            50%       { opacity: 1; transform: scale(1.1); }
        }
        .envelope-anim { animation: envelopeBounce 3s ease-in-out infinite; }
        .dot-1 { animation: dotPulse 1.4s ease-in-out 0s infinite; }
        .dot-2 { animation: dotPulse 1.4s ease-in-out 0.2s infinite; }
        .dot-3 { animation: dotPulse 1.4s ease-in-out 0.4s infinite; }
    </style>

    <div class="text-center">

        {{-- Envelope icon --}}
        <div class="flex justify-center mb-6">
            <div class="envelope-anim relative inline-flex">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl shadow-2xl"
                     style="background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(139,92,246,0.15)); border: 1px solid rgba(99,102,241,0.35); box-shadow: 0 0 40px rgba(99,102,241,0.2);">
                    📧
                </div>
                <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-amber-400 flex items-center justify-center">
                    <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Heading --}}
        <h1 class="text-2xl font-black text-white mb-2">Check Your Inbox</h1>
        <p class="text-gray-400 text-sm leading-relaxed mb-1">
            We sent a verification link to
        </p>
        <p class="text-indigo-400 font-semibold text-sm mb-5">{{ auth()->user()->email }}</p>

        {{-- Steps --}}
        <div class="rounded-2xl p-4 mb-6 text-left space-y-3"
             style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07);">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                     style="background:rgba(99,102,241,0.3); color:#a5b4fc;">1</div>
                <p class="text-sm text-gray-300">Open the email from <span class="text-white font-medium">PesaQuest</span></p>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                     style="background:rgba(99,102,241,0.3); color:#a5b4fc;">2</div>
                <p class="text-sm text-gray-300">Click the <span class="text-white font-medium">"Verify Email"</span> button</p>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                     style="background:rgba(16,185,129,0.3); color:#6ee7b7;">3</div>
                <p class="text-sm text-gray-300">You'll be redirected to your <span class="text-white font-medium">PesaQuest dashboard</span></p>
            </div>
        </div>

        {{-- Status: resend success --}}
        @if (session('status') == 'verification-link-sent')
            <div class="mb-5 flex items-center gap-2.5 rounded-xl px-3.5 py-3 text-sm text-emerald-300 text-left"
                 style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2);">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                A new verification link has been sent to your email.
            </div>
        @endif

        {{-- Resend form --}}
        <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
            @csrf
            <button type="submit"
                    class="w-full font-bold py-3.5 rounded-xl text-white text-sm transition-all duration-300 hover:scale-[1.02]"
                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 8px 24px rgba(99,102,241,0.25);">
                Resend Verification Email
            </button>
        </form>

        {{-- Already verified button --}}
        <div class="mb-5">
            <button id="checkVerifiedBtn" type="button"
                    onclick="checkVerified()"
                    class="w-full font-semibold py-3.5 rounded-xl text-sm transition-all duration-300 hover:scale-[1.02]"
                    style="background: rgba(255,255,255,0.04); border: 1px solid rgba(99,102,241,0.3); color: #a5b4fc;">
                <span id="checkVerifiedLabel">I've already verified my email →</span>
                <span id="checkVerifiedSpinner" class="hidden">
                    <svg class="inline w-4 h-4 animate-spin mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Checking…
                </span>
            </button>
            <div id="checkVerifiedError" class="hidden mt-2 rounded-xl px-3.5 py-2.5 text-sm text-red-300 text-left"
                 style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2);">
            </div>
        </div>

        <script>
        function checkVerified() {
            const btn    = document.getElementById('checkVerifiedBtn');
            const label  = document.getElementById('checkVerifiedLabel');
            const spin   = document.getElementById('checkVerifiedSpinner');
            const errBox = document.getElementById('checkVerifiedError');

            btn.disabled = true;
            label.classList.add('hidden');
            spin.classList.remove('hidden');
            errBox.classList.add('hidden');

            fetch('{{ route('verification.check') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.verified) {
                    window.location.href = data.redirect;
                } else {
                    errBox.textContent = data.message;
                    errBox.classList.remove('hidden');
                    btn.disabled = false;
                    label.classList.remove('hidden');
                    spin.classList.add('hidden');
                }
            })
            .catch(() => {
                errBox.textContent = 'Something went wrong. Please try again.';
                errBox.classList.remove('hidden');
                btn.disabled = false;
                label.classList.remove('hidden');
                spin.classList.add('hidden');
            });
        }
        </script>

        {{-- Waiting indicator --}}
        <div class="flex items-center justify-center gap-1.5 mb-5 text-xs text-gray-600">
            <span>Waiting for verification</span>
            <span class="dot-1 w-1 h-1 rounded-full bg-gray-500 inline-block"></span>
            <span class="dot-2 w-1 h-1 rounded-full bg-gray-500 inline-block"></span>
            <span class="dot-3 w-1 h-1 rounded-full bg-gray-500 inline-block"></span>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs text-gray-600 hover:text-gray-400 transition-colors underline underline-offset-2">
                Not your account? Log out
            </button>
        </form>

    </div>

</x-guest-layout>
