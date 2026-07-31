<x-guest-layout>

    {{-- Heading --}}
    <div class="mb-7">
        <h1 class="text-2xl font-black text-white">One Last Step</h1>
        <p class="text-sm text-gray-400 mt-1.5">Welcome, {{ auth()->user()->name }} — we just need your date of birth to set up your game world.</p>
    </div>

    <form method="POST" action="{{ route('auth.google.complete-profile.save') }}" class="space-y-5" x-data="dobPicker()">
        @csrf

        <div>
            <label for="date_of_birth" class="auth-label">
                Date of Birth
                <span class="text-red-400 ml-0.5">*</span>
            </label>
            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                   class="auth-input" required max="{{ now()->subYears(5)->format('Y-m-d') }}" min="1920-01-01"
                   x-model="dob" @change="update()" style="color-scheme:dark;" autofocus>
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

        <button type="submit"
                class="w-full font-bold py-3.5 rounded-xl text-white text-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); box-shadow: 0 8px 24px rgba(99,102,241,0.3);">
            Continue to PesaQuest
        </button>
    </form>

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
