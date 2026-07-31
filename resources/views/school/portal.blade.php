<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $school->school_name }} — PesaQuest School Portal</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #080710; }
        [x-cloak] { display: none !important; }
        .glass { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 1.25rem; }
        .ifield { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 0.75rem; color: white; padding: 0.625rem 0.875rem; font-size: 0.875rem; transition: border-color 0.2s; width: 100%; font-family: inherit; }
        .ifield:focus { outline: none; border-color: rgba(99,102,241,0.6); background: rgba(99,102,241,0.05); }
        .btn-p { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: white; font-weight: 700; padding: 0.55rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; transition: all 0.2s; }
        .btn-p:hover { opacity: 0.9; transform: scale(1.02); }
        .btn-danger { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.28); color: #f87171; font-size: 0.75rem; padding: 0.3rem 0.65rem; border-radius: 0.5rem; transition: all 0.2s; }
        .btn-danger:hover { background: rgba(239,68,68,0.22); }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased" x-data="schoolPortal()" x-cloak
      style="background: radial-gradient(ellipse at top, rgba(99,102,241,0.1) 0%, transparent 50%), #080710;">

    {{-- Header --}}
    <header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('moski-logo.png') }}" alt="Moski" class="h-10 w-auto rounded-xl object-cover">
                <div>
                    <h1 class="font-black text-base leading-none text-white">{{ $school->school_name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">PesaQuest School Portal</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $isActive = $school->isActive();
                    $statusColor = $isActive ? 'text-emerald-400' : 'text-red-400';
                    $statusBg    = $isActive ? 'bg-emerald-500/10 border-emerald-500/25' : 'bg-red-500/10 border-red-500/25';
                @endphp
                <span class="text-xs font-bold px-2.5 py-1 rounded-full border {{ $statusBg }} {{ $statusColor }}">
                    {{ $isActive ? '● Active' : '○ ' . $school->statusLabel() }}
                </span>
            </div>
        </div>
    </header>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        {{-- Subscription info cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['Seats Used',   $school->usedSeats() . ' / ' . $school->seats,  'text-indigo-400'],
                ['Available',    $school->availableSeats(),                       'text-emerald-400'],
                ['Valid Until',  $school->ends_at->format('d M Y'),              'text-amber-400'],
                ['Days Left',    max(0, (int) now()->diffInDays($school->ends_at, false)), 'text-purple-400'],
            ] as [$label, $val, $color])
            <div class="glass p-4">
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mb-1">{{ $label }}</p>
                <p class="text-2xl font-black {{ $color }}">{{ $val }}</p>
            </div>
            @endforeach
        </div>

        @if(!$school->isActive())
        <div class="glass p-4 border-red-500/25" style="border-color:rgba(239,68,68,0.25);background:rgba(239,68,68,0.05);">
            <p class="text-sm text-red-300 font-semibold">⚠ This subscription is inactive or has expired. Contact your PesaQuest administrator to renew it. Students currently attached will lose premium access.</p>
        </div>
        @endif

        {{-- Add student form --}}
        @if($school->isActive())
        <div class="glass p-6">
            <h2 class="font-black text-base mb-1">Add a Student</h2>
            <p class="text-xs text-gray-500 mb-4">The student must already have a PesaQuest account. Enter their registered email address.</p>

            <div class="flex gap-3">
                <input
                    type="email"
                    x-model="addEmail"
                    @keydown.enter="addMember()"
                    placeholder="student@email.com"
                    class="ifield flex-1"
                    :disabled="adding"
                >
                <button @click="addMember()" :disabled="adding || !addEmail.trim()" class="btn-p whitespace-nowrap flex items-center gap-2">
                    <template x-if="!adding"><span>+ Add Student</span></template>
                    <template x-if="adding">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Adding…
                        </span>
                    </template>
                </button>
            </div>

            <template x-if="addError">
                <p class="text-sm text-red-400 mt-2 font-medium" x-text="addError"></p>
            </template>
            <template x-if="addSuccess">
                <p class="text-sm text-emerald-400 mt-2 font-medium" x-text="addSuccess"></p>
            </template>

            <p class="text-[10px] text-gray-600 mt-3">
                {{ $school->availableSeats() }} seat{{ $school->availableSeats() === 1 ? '' : 's' }} remaining · Subscription expires {{ $school->ends_at->diffForHumans() }}
            </p>
        </div>
        @endif

        {{-- Member list --}}
        <div class="glass overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                <h2 class="font-black text-base">Students (<span x-text="members.length">{{ $school->members->count() }}</span>)</h2>
                <span class="text-xs text-gray-500">All have full premium access while subscription is active</span>
            </div>

            <template x-if="members.length === 0">
                <div class="py-12 text-center">
                    <div class="text-4xl mb-3 opacity-30">🎒</div>
                    <p class="text-sm text-gray-500">No students added yet. Add their emails above.</p>
                </div>
            </template>

            <div class="divide-y divide-white/5">
                <template x-for="(member, idx) in members" :key="member.id">
                    <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/[0.015] transition-colors">
                        {{-- Avatar --}}
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-base font-black flex-shrink-0 overflow-hidden"
                             style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                            <template x-if="member.avatar_url">
                                <img :src="member.avatar_url" class="w-full h-full object-cover" alt="">
                            </template>
                            <template x-if="!member.avatar_url">
                                <span x-text="member.name.charAt(0).toUpperCase()"></span>
                            </template>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-white truncate" x-text="member.name"></p>
                            <p class="text-xs text-gray-500 truncate" x-text="member.email"></p>
                        </div>

                        {{-- Added date --}}
                        <span class="text-[10px] text-gray-600 flex-shrink-0 hidden sm:block" x-text="'Added ' + member.added_at"></span>

                        {{-- Remove --}}
                        @if($school->isActive())
                        <button @click="removeMember(idx)"
                                class="btn-danger flex-shrink-0"
                                :disabled="removingIdx === idx">
                            <span x-show="removingIdx !== idx">✕ Remove</span>
                            <span x-show="removingIdx === idx">…</span>
                        </button>
                        @endif
                    </div>
                </template>
            </div>
        </div>

        {{-- Teacher Challenges --}}
        <div class="glass overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
                <h2 class="font-black text-base">🎯 Teacher Challenges</h2>
                <span class="text-xs text-gray-500">Posted to your school's private forum board</span>
            </div>

            <div class="p-5">
                @if($school->isActive())
                <div class="rounded-2xl p-4 mb-5" style="background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.2);">
                    <p class="text-xs text-gray-400 mb-3">Set a task for your students — e.g. "Save Ksh 500 in your piggy bank this week and reply with what you learned." Students get a notification and earn XP for replying on the school board.</p>
                    <div class="grid sm:grid-cols-2 gap-3 mb-3">
                        <input type="text" x-model="chTeacher" placeholder="Your name (e.g. Mr. Otieno)" maxlength="80"
                               class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none"
                               style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);">
                        <input type="text" x-model="chTitle" placeholder="Challenge title" maxlength="150"
                               class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none"
                               style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);">
                    </div>
                    <textarea x-model="chBody" rows="3" maxlength="5000"
                              placeholder="Describe the challenge — what should students do, and what should they reply with?"
                              class="w-full rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none mb-3"
                              style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);"></textarea>
                    <div class="flex items-center gap-3 flex-wrap">
                        <button @click="postChallenge()" :disabled="chPosting || !chTeacher.trim() || chTitle.trim().length < 5 || chBody.trim().length < 10"
                                class="btn-p flex items-center gap-2 disabled:opacity-40">
                            <span x-show="!chPosting">🎯 Post Challenge</span>
                            <span x-show="chPosting">Posting…</span>
                        </button>
                        <p class="text-xs text-red-400 font-medium" x-show="chError" x-text="chError"></p>
                        <p class="text-xs text-emerald-400 font-medium" x-show="chSuccess" x-text="chSuccess"></p>
                    </div>
                </div>
                @endif

                @if($challenges->isEmpty())
                <p class="text-sm text-gray-600 text-center py-4">No challenges posted yet.</p>
                @else
                <div class="divide-y divide-white/5">
                    @foreach($challenges as $ch)
                    <div class="py-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white leading-snug">🎯 {{ $ch->title }}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                by {{ $ch->posted_by_name ?? 'Teacher' }} · {{ $ch->created_at->format('d M Y') }} ·
                                💬 {{ $ch->replies_count }} {{ Str::plural('reply', $ch->replies_count) }} · 👁️ {{ $ch->views }} views
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center">
            <p class="text-xs text-gray-600">
                PesaQuest School Portal · {{ $school->school_name }} ·
                <a href="{{ route('landing') }}" class="text-indigo-400 hover:text-indigo-300">PesaQuest Home</a>
            </p>
            <p class="text-[10px] text-gray-700 mt-1">Keep this URL private — it provides management access to your school subscription.</p>
        </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition
         class="fixed bottom-5 right-5 z-[200] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-bold"
         :class="toast.type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'">
        <span x-text="toast.type === 'success' ? '✓' : '✕'"></span>
        <span x-text="toast.message"></span>
    </div>

    @php
    $portalMembers = $school->members->map(fn($m) => [
        'id'         => $m->id,
        'name'       => $m->user?->name ?? 'Unknown',
        'email'      => $m->user?->email ?? '',
        'avatar_url' => $m->user?->profile_photo,
        'added_at'   => $m->created_at->format('d M Y'),
    ])->values();
    @endphp
    <script>
    function schoolPortal() {
        return {
            // Pre-populate from PHP
            members: @json($portalMembers),

            addEmail: '',
            adding: false,
            addError: '',
            addSuccess: '',
            removingIdx: null,
            toast: { show: false, message: '', type: 'success' },
            chTeacher: '',
            chTitle: '',
            chBody: '',
            chPosting: false,
            chError: '',
            chSuccess: '',

            async postChallenge() {
                if (this.chPosting) return;
                this.chPosting = true;
                this.chError = '';
                this.chSuccess = '';
                try {
                    const res = await fetch('{{ route("school.challenges.post", $school->portal_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({
                            teacher_name: this.chTeacher.trim(),
                            title: this.chTitle.trim(),
                            body: this.chBody.trim(),
                        }),
                    });
                    const data = await res.json();
                    if (!res.ok || data.error) {
                        this.chError = data.error || Object.values(data.errors ?? {}).flat().join(' ') || 'Could not post the challenge.';
                    } else {
                        this.chSuccess = data.message || 'Challenge posted!';
                        this.showToast(this.chSuccess);
                        this.chTitle = '';
                        this.chBody = '';
                        setTimeout(() => location.reload(), 1200);
                    }
                } catch (e) {
                    this.chError = 'Network error — try again.';
                } finally {
                    this.chPosting = false;
                }
            },

            showToast(msg, type = 'success') {
                this.toast = { show: true, message: msg, type };
                setTimeout(() => this.toast.show = false, 3500);
            },

            async addMember() {
                if (!this.addEmail.trim() || this.adding) return;
                this.adding    = true;
                this.addError  = '';
                this.addSuccess = '';

                try {
                    const res = await fetch('{{ route("school.members.add", $school->portal_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ email: this.addEmail.trim() }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.members.unshift(data.member);
                        this.addSuccess = `${data.member.name} added successfully!`;
                        this.addEmail   = '';
                        this.showToast(`${data.member.name} added ✓`, 'success');
                    } else {
                        this.addError = data.error ?? 'Something went wrong.';
                    }
                } catch (e) {
                    this.addError = 'Connection error — please try again.';
                }
                this.adding = false;
            },

            async removeMember(idx) {
                if (this.removingIdx !== null) return;
                const member = this.members[idx];
                if (!confirm(`Remove ${member.name} from this school?`)) return;

                this.removingIdx = idx;
                try {
                    const res = await fetch(`{{ url('/school/' . $school->portal_token . '/members') }}/${member.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.members.splice(idx, 1);
                        this.showToast(`${member.name} removed`, 'success');
                    }
                } catch (e) {
                    this.showToast('Failed to remove member', 'error');
                }
                this.removingIdx = null;
            },
        };
    }
    </script>
</body>
</html>
