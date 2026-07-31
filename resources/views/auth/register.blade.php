<x-guest-layout>
<div x-data="{ ageGroup: '{{ old('age_group', '') }}' }">

    {{-- Heading --}}
    <div class="mb-7">
        <h1 class="text-2xl font-black text-white">Create Your Account</h1>
        <p class="text-sm text-gray-400 mt-1.5">Choose your age group to get a personalized game world</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- ── DATE OF BIRTH (private — derives the age group) ─────── --}}
        <div x-data="dobPicker()">
            <label for="date_of_birth" class="auth-label">
                Date of Birth
                <span class="text-red-400 ml-0.5">*</span>
            </label>
            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                   class="auth-input" required max="{{ now()->subYears(5)->format('Y-m-d') }}" min="1920-01-01"
                   x-model="dob" @change="update()" style="color-scheme:dark;">
            <p class="text-[11px] text-gray-500 mt-1.5 flex items-center gap-1">
                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                Kept private — never shown to anyone. We use it for birthday surprises 🎁 and to keep your game world age-appropriate.
            </p>
            <div x-show="world" x-cloak class="mt-2 rounded-xl px-3 py-2 text-sm font-bold border border-white/10 bg-white/5">
                <span x-text="worldIcon" class="mr-1"></span>You'll play in the <span x-text="world" class="text-indigo-300"></span> world
            </div>
            @error('date_of_birth')
                <p class="auth-error">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Legacy age-group picker (hidden — age group now derives from DOB server-side) --}}
        <div style="display:none;">
            <label class="auth-label">
                Your Age Group
            </label>
            <div class="grid grid-cols-2 gap-2.5">

                {{-- 8-12 --}}
                <button type="button" @click="ageGroup = '8-12'"
                        :class="ageGroup === '8-12'
                            ? 'border-indigo-500 bg-indigo-500/15 text-white scale-[1.02] shadow-lg shadow-indigo-500/20'
                            : 'border-white/10 bg-white/4 text-gray-400 hover:border-indigo-400/50 hover:bg-white/6'"
                        class="relative flex flex-col items-center py-4 px-3 rounded-2xl border-2 transition-all duration-200 cursor-pointer">
                    <span class="text-2xl mb-1.5">🧒</span>
                    <span class="font-bold text-sm">Ages 8–12</span>
                    <span class="text-[11px] mt-0.5 opacity-60">Preteens</span>
                    <div x-show="ageGroup === '8-12'"
                         class="absolute top-2 right-2 w-4 h-4 rounded-full bg-indigo-500 flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </button>

                {{-- 13-17 --}}
                <button type="button" @click="ageGroup = '13-17'"
                        :class="ageGroup === '13-17'
                            ? 'border-purple-500 bg-purple-500/15 text-white scale-[1.02] shadow-lg shadow-purple-500/20'
                            : 'border-white/10 bg-white/4 text-gray-400 hover:border-purple-400/50 hover:bg-white/6'"
                        class="relative flex flex-col items-center py-4 px-3 rounded-2xl border-2 transition-all duration-200 cursor-pointer">
                    <span class="text-2xl mb-1.5">🎒</span>
                    <span class="font-bold text-sm">Ages 13–17</span>
                    <span class="text-[11px] mt-0.5 opacity-60">Teens</span>
                    <div x-show="ageGroup === '13-17'"
                         class="absolute top-2 right-2 w-4 h-4 rounded-full bg-purple-500 flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </button>

                {{-- 18-25 --}}
                <button type="button" @click="ageGroup = '18-25'"
                        :class="ageGroup === '18-25'
                            ? 'border-pink-500 bg-pink-500/15 text-white scale-[1.02] shadow-lg shadow-pink-500/20'
                            : 'border-white/10 bg-white/4 text-gray-400 hover:border-pink-400/50 hover:bg-white/6'"
                        class="relative flex flex-col items-center py-4 px-3 rounded-2xl border-2 transition-all duration-200 cursor-pointer">
                    <span class="text-2xl mb-1.5">🎓</span>
                    <span class="font-bold text-sm">Ages 18–25</span>
                    <span class="text-[11px] mt-0.5 opacity-60">Young Adults</span>
                    <div x-show="ageGroup === '18-25'"
                         class="absolute top-2 right-2 w-4 h-4 rounded-full bg-pink-500 flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </button>

                {{-- 26+ --}}
                <button type="button" @click="ageGroup = '26+'"
                        :class="ageGroup === '26+'
                            ? 'border-amber-500 bg-amber-500/15 text-white scale-[1.02] shadow-lg shadow-amber-500/20'
                            : 'border-white/10 bg-white/4 text-gray-400 hover:border-amber-400/50 hover:bg-white/6'"
                        class="relative flex flex-col items-center py-4 px-3 rounded-2xl border-2 transition-all duration-200 cursor-pointer">
                    <span class="text-2xl mb-1.5">💼</span>
                    <span class="font-bold text-sm">Ages 26+</span>
                    <span class="text-[11px] mt-0.5 opacity-60">Adults</span>
                    <div x-show="ageGroup === '26+'"
                         class="absolute top-2 right-2 w-4 h-4 rounded-full bg-amber-500 flex items-center justify-center">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </button>

            </div>
            <input type="hidden" name="age_group" :value="ageGroup">
            @error('age_group')
                <p class="auth-error">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── NAME ────────────────────────────────────────────────── --}}
        <div>
            <label for="name" class="auth-label">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="auth-input" placeholder="Your full name"
                   required autofocus autocomplete="name">
            @error('name')
                <p class="auth-error">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── EMAIL ───────────────────────────────────────────────── --}}
        <div>
            <label for="email" class="auth-label">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="auth-input" placeholder="you@example.com"
                   required autocomplete="username">
            @error('email')
                <p class="auth-error">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── PASSWORD ────────────────────────────────────────────── --}}
        <div x-data="{ show: false }">
            <label for="password" class="auth-label">Password</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password"
                       class="auth-input has-icon-right" placeholder="At least 8 characters"
                       required autocomplete="new-password">
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

        {{-- ── CONFIRM PASSWORD ────────────────────────────────────── --}}
        <div x-data="{ show: false }">
            <label for="password_confirmation" class="auth-label">Confirm Password</label>
            <div class="relative">
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation"
                       class="auth-input has-icon-right" placeholder="Repeat your password"
                       required autocomplete="new-password">
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
            @error('password_confirmation')
                <p class="auth-error">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- ── EMAIL VERIFICATION NOTICE ───────────────────────────── --}}
        <div class="flex items-start gap-2.5 rounded-xl px-3.5 py-3 text-sm text-indigo-300"
             style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2);">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>A verification link will be sent to your email. You must verify before playing.</span>
        </div>

        {{-- ── SUBMIT ──────────────────────────────────────────────── --}}
        <button type="submit"
                class="w-full font-bold py-3.5 rounded-xl text-white text-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); box-shadow: 0 8px 24px rgba(99,102,241,0.3);">
            Create Account &amp; Start Playing
        </button>

        @if(config('services.google.client_id'))
        <div class="flex items-center gap-3 py-1">
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.07);"></div>
            <span class="text-xs text-gray-600">or</span>
            <div class="flex-1 h-px" style="background:rgba(255,255,255,0.07);"></div>
        </div>
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
        <p class="text-[11px] text-gray-600 text-center">Google sign-up still asks for your date of birth on the next step — we use it to keep your game world age-appropriate.</p>
        @endif

        <p class="text-center text-sm text-gray-500 pt-1">
            Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors ml-1">Sign In</a>
        </p>

    </form>
</div>
<script>
function dobPicker() {
    return {
        dob: @json(old('date_of_birth', '')),
        world: '',
        worldIcon: '',
        init() { this.update(); },
        update() {
            if (!this.dob) { this.world = ''; return; }
            const birth = new Date(this.dob);
            if (isNaN(birth)) { this.world = ''; return; }
            const t = new Date();
            let age = t.getFullYear() - birth.getFullYear();
            if (t.getMonth() < birth.getMonth() || (t.getMonth() === birth.getMonth() && t.getDate() < birth.getDate())) age--;
            if (age <= 12)      { this.world = 'Preteens (8–12)';      this.worldIcon = '🧒'; }
            else if (age <= 17) { this.world = 'Teens (13–17)';        this.worldIcon = '🎒'; }
            else if (age <= 25) { this.world = 'Young Adults (18–25)'; this.worldIcon = '🎓'; }
            else                { this.world = 'Adults (26+)';         this.worldIcon = '💼'; }
        }
    };
}
</script>
</x-guest-layout>
