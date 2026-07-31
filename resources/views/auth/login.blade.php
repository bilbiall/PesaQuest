<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-7">
        <h1 class="text-2xl font-black text-white">Welcome Back</h1>
        <p class="text-sm text-gray-400 mt-1.5">Sign in to continue your PesaQuest journey</p>
    </div>

    {{-- Session status --}}
    @if (session('status'))
        <div class="mb-5 flex items-center gap-2.5 rounded-xl px-3.5 py-3 text-sm text-emerald-300"
             style="background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2);">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- ── EMAIL ───────────────────────────────────────────────── --}}
        <div>
            <label for="email" class="auth-label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="auth-input" placeholder="you@example.com"
                   required autofocus autocomplete="username">
            @error('email')
                <p class="auth-error">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── PASSWORD ────────────────────────────────────────────── --}}
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="auth-label" style="margin-bottom:0;">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors font-medium">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password"
                       class="auth-input has-icon-right" placeholder="Your password"
                       required autocomplete="current-password">
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors p-0.5">
                    <svg x-show="!show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="auth-error">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── REMEMBER ME ─────────────────────────────────────────── --}}
        <div class="flex items-center gap-2.5">
            <input id="remember_me" type="checkbox" name="remember"
                   class="w-4 h-4 rounded text-indigo-500 border-white/20 focus:ring-indigo-500 focus:ring-offset-0"
                   style="background:rgba(255,255,255,0.05); accent-color:#6366f1;">
            <label for="remember_me" class="text-sm text-gray-400 cursor-pointer select-none">Remember me for 30 days</label>
        </div>

        {{-- ── SUBMIT ──────────────────────────────────────────────── --}}
        <button type="submit"
                class="w-full font-bold py-3.5 rounded-xl text-white text-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); box-shadow: 0 8px 24px rgba(99,102,241,0.3);">
            Sign In to PesaQuest
        </button>

        @if(config('services.google.client_id'))
        <a href="{{ route('auth.google.redirect') }}"
           class="w-full flex items-center justify-center gap-2.5 font-semibold py-3 rounded-xl text-sm text-gray-200 hover:text-white transition-all duration-200 hover:scale-[1.01]"
           style="border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04);">
            <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M23.52 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.47c-.28 1.5-1.13 2.77-2.4 3.62v3h3.88c2.27-2.09 3.58-5.17 3.58-8.81z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.96-1.07 7.95-2.92l-3.88-3c-1.08.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.27v3.11C3.25 21.3 7.31 24 12 24z"/>
                <path fill="#FBBC05" d="M5.27 14.27a7.2 7.2 0 010-4.54V6.62H1.27a12 12 0 000 10.76l4-3.11z"/>
                <path fill="#EA4335" d="M12 4.77c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.2 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.62l4 3.11C6.22 6.88 8.87 4.77 12 4.77z"/>
            </svg>
            Continue with Google
        </a>
        @endif

        {{-- ── DIVIDER ─────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 py-1">
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.07);"></div>
            <span class="text-xs text-gray-600">New to PesaQuest?</span>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.07);"></div>
        </div>

        <a href="{{ route('register') }}"
           class="w-full flex items-center justify-center gap-2 font-semibold py-3 rounded-xl text-sm text-gray-300 hover:text-white transition-all duration-200 hover:scale-[1.01]"
           style="border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03);">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Create a Free Account
        </a>

    </form>

</x-guest-layout>
