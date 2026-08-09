<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $school->school_name }} — Teacher Portal — PesaQuest</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak] { display:none !important; }
        .glass { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 1.25rem; }
        .ifield { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 0.75rem; color: white; padding: 0.6rem 0.85rem; font-size: 0.85rem; width: 100%; font-family: inherit; }
        .ifield:focus { outline:none; border-color: rgba(245,158,11,0.5); }
        .btn-p { background: linear-gradient(135deg,#f59e0b,#d97706); color:white; font-weight:700; padding:.6rem 1.2rem; border-radius:.75rem; font-size:.85rem; }
        .btn-p:hover { opacity:.9; }
        .roster-row { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 1rem; transition: all .15s; }
        .roster-row:hover { border-color: rgba(245,158,11,.3); background: rgba(245,158,11,.03); }
    </style>
</head>
<body class="min-h-screen text-white" x-data="teacherPortal()" x-cloak>

    <header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('moski-logo.png') }}" alt="Moski" class="h-10 w-auto rounded-xl object-cover">
                <div>
                    <h1 class="font-black text-base leading-none text-white">{{ $school->school_name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">👩‍🏫 Teacher Portal @if($myRole==='owner')<span class="text-amber-400">· Owner</span>@endif</p>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs font-bold text-gray-400 hover:text-white transition-colors">← Back to my game</a>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        @if(session('success'))
        <div class="rounded-2xl px-5 py-3 text-sm font-bold text-emerald-300" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
            ✅ {{ session('success') }}
        </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="glass p-4 text-center">
                <p class="text-2xl font-black text-white">{{ $stats['students'] }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Students</p>
            </div>
            <div class="glass p-4 text-center">
                <p class="text-2xl font-black {{ $stats['overdue_total'] > 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $stats['overdue_total'] }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Overdue Bills</p>
            </div>
            <div class="glass p-4 text-center">
                <p class="text-2xl font-black text-indigo-400">{{ $stats['avg_credit'] }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Avg Credit Score</p>
            </div>
        </div>

        @if($stats['overdue_total'] > 0)
        <div class="rounded-2xl p-4 text-sm" style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2);">
            💡 <b class="text-amber-300">Discussion moment:</b> {{ $stats['overdue_total'] }} bill(s) are overdue across your class right now — a good week to revisit budgeting and deadlines.
        </div>
        @endif

        {{-- Classes --}}
        <div class="glass p-5">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <div>
                    <h2 class="font-black text-white">🧑‍🏫 Classes</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $classes->count() }}/{{ $school->max_classes }} classes used · split your roster so each teacher's challenges & evaluation scope to their own students.</p>
                </div>
                @if($myRole === 'owner')
                <button @click="classOpen = true" class="btn-p" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">+ New Class</button>
                @endif
            </div>
            @if($classes->isEmpty())
            <p class="text-sm text-gray-500">No classes yet — everything is scoped to the whole school. {{ $myRole === 'owner' ? 'Create one to split your roster up.' : 'Ask the school owner to create one.' }}</p>
            @else
            <div class="space-y-2">
                @foreach($classes as $c)
                <div class="flex items-center justify-between p-3 rounded-xl" style="background:rgba(255,255,255,0.02);">
                    <div>
                        <p class="text-sm font-bold text-white">{{ $c->name }}</p>
                        <p class="text-[11px] text-gray-500">{{ $c->members_count }} student(s) · teacher: {{ $c->teacher?->name ?? $c->teacher?->email ?? 'Unassigned' }}</p>
                    </div>
                    @if($myRole === 'owner')
                    <div class="flex items-center gap-2">
                        <select class="ifield text-xs" style="width:auto;" onchange="assignClassTeacher({{ $c->id }}, this.value)">
                            <option value="">— Assign teacher —</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" @selected($c->teacher_id === $t->id)>{{ $t->name ?? $t->email }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="deleteClass({{ $c->id }}, {{ Js::from($c->name) }})" class="text-gray-600 hover:text-red-400 transition-colors flex-shrink-0 p-1">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Class Challenges --}}
        @if($challengeTemplates->isNotEmpty())
        <div class="glass p-5">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <h2 class="font-black text-white">🏆 Class Challenge</h2>
                <span class="text-xs text-gray-500">A shared leaderboard — students are enrolled automatically, no opt-in needed.</span>
            </div>
            @if($myRole !== 'owner' && !$myTeacher?->school_class_id)
            <p class="text-sm text-amber-300 mb-3">Ask the school owner to assign you to a class before launching a Class Challenge.</p>
            @else
            <form method="POST" action="{{ route('school.teacher.challenge.create', $school) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                @csrf
                <div class="sm:col-span-2">
                    <label class="text-xs font-bold text-gray-400 block mb-1">Challenge Type</label>
                    <select name="template_id" class="w-full rounded-xl px-3 py-2 text-sm" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);color:#fff;">
                        @foreach($challengeTemplates as $t)
                            <option value="{{ $t->id }}">{{ $t->icon }} {{ $t->name }} ({{ $t->default_duration_days }}d)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 block mb-1">Duration (days)</label>
                    <input type="number" name="duration_days" min="1" max="60" class="w-full rounded-xl px-3 py-2 text-sm" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);color:#fff;" placeholder="uses template default">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 block mb-1">Scope</label>
                    @if($myRole === 'owner')
                    <select name="school_class_id" class="w-full rounded-xl px-3 py-2 text-sm" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);color:#fff;">
                        <option value="">Whole School</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="hidden" name="school_class_id" value="{{ $myTeacher->school_class_id }}">
                    <div class="w-full rounded-xl px-3 py-2 text-sm text-gray-400" style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.08);">{{ $myTeacher->schoolClass?->name }}</div>
                    @endif
                </div>
                <button type="submit" class="btn-p sm:col-span-4">🚀 Launch Class Challenge</button>
            </form>
            @endif
            @if($classChallenges->isNotEmpty())
            <div class="mt-4 space-y-2">
                @foreach($classChallenges as $c)
                <a href="{{ route('challenges.show', $c) }}" class="flex items-center justify-between p-3 rounded-xl text-sm transition-colors" style="background:rgba(255,255,255,0.02);text-decoration:none;">
                    <span class="font-bold text-white">{{ $c->title }}</span>
                    <span class="text-gray-500">{{ $c->participants_count }} enrolled · {{ ucfirst($c->status) }}</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- Roster --}}
        <div class="glass p-5">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <h2 class="font-black text-white">📋 Student Roster</h2>
                <button @click="addOpen = true" class="btn-p">+ Add Student</button>
            </div>

            @if($roster->isEmpty())
            <div class="text-center py-10 text-gray-500">
                <div class="text-4xl mb-2">🎓</div>
                <p>No students yet. Add one by their PesaQuest account email.</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($roster as $row)
                <a href="{{ route('school.teacher.student', [$school, $row['member_id']]) }}" class="roster-row flex items-center gap-3 p-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-black text-white flex-shrink-0" style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                        {{ strtoupper(substr($row['user']->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ $row['user']->name ?? 'Unknown' }}</p>
                        <p class="text-[11px] text-gray-500">{{ $row['chapter'] }} · Level {{ $row['level'] }} · Net worth Ksh {{ number_format($row['net_worth']) }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs font-black {{ $row['credit_score'] >= 650 ? 'text-emerald-400' : ($row['credit_score'] >= 500 ? 'text-amber-400' : 'text-red-400') }}">{{ $row['credit_score'] }} credit</p>
                        @if($row['overdue_bills'] > 0)
                        <p class="text-[10px] font-bold text-red-400 mt-0.5">⚠️ {{ $row['overdue_bills'] }} overdue</p>
                        @else
                        <p class="text-[10px] text-gray-600 mt-0.5">All bills current</p>
                        @endif
                    </div>
                    @if($classes->isNotEmpty())
                    <select class="ifield text-xs flex-shrink-0" style="width:auto;" @click.prevent.stop onchange="assignStudentClass({{ $row['member_id'] }}, this.value)">
                        <option value="">Whole school</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" @selected($row['school_class_id'] === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <button type="button" @click.prevent.stop="removeStudent({{ $row['member_id'] }}, {{ Js::from($row['user']->name ?? 'this student') }})"
                            class="text-gray-600 hover:text-red-400 transition-colors flex-shrink-0 p-1">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Teachers --}}
        <div class="glass p-5">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <h2 class="font-black text-white">👩‍🏫 Teachers</h2>
                @if($myRole === 'owner')
                <button @click="inviteOpen = true" class="btn-p" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">+ Invite Teacher</button>
                @endif
            </div>
            <div class="space-y-2">
                @foreach($teachers as $t)
                <div class="flex items-center justify-between p-3 rounded-xl" style="background:rgba(255,255,255,0.02);">
                    <a href="{{ route('school.teacher.teachers.profile', [$school, $t]) }}" class="min-w-0">
                        <p class="text-sm font-bold text-white hover:text-amber-300 transition-colors">{{ $t->name ?? $t->email }} @if($t->role === 'owner')<span class="text-[10px] font-bold text-amber-400 ml-1">OWNER</span>@endif</p>
                        <p class="text-[11px] text-gray-500">{{ $t->email }} · {{ $t->status === 'active' ? '✅ Active' : '⏳ Invited' }} · class: {{ $t->schoolClass?->name ?? 'Unassigned' }}</p>
                    </a>
                    @if($myRole === 'owner' && $t->role !== 'owner')
                    <button type="button" @click="removeTeacher({{ $t->id }}, {{ Js::from($t->name ?? $t->email) }})" class="text-xs font-bold text-red-400/70 hover:text-red-400">Remove</button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Add Student Modal --}}
    <div x-show="addOpen" style="position:fixed;inset:0;z-index:9990;display:flex;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;overscroll-behavior:contain;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);" @click.self="addOpen=false">
        <div class="glass p-6 w-full max-w-sm" style="margin:auto;">
            <h3 class="font-black text-lg mb-4">+ Add Student</h3>
            <label class="text-xs text-gray-400 font-bold uppercase mb-1 block">Student's PesaQuest email</label>
            <input type="email" x-model="addEmail" class="ifield mb-3" placeholder="student@example.com">
            <p x-show="addError" x-cloak class="text-xs text-red-400 mb-3" x-text="addError"></p>
            <div class="flex gap-3">
                <button @click="addStudent()" :disabled="adding" class="btn-p flex-1"><span x-show="!adding">Add Student</span><span x-show="adding">Adding…</span></button>
                <button @click="addOpen=false" class="flex-1 py-2.5 rounded-xl text-sm text-gray-400 hover:text-white" style="border:1px solid rgba(255,255,255,.1);">Cancel</button>
            </div>
        </div>
    </div>

    {{-- New Class Modal --}}
    <div x-show="classOpen" style="position:fixed;inset:0;z-index:9990;display:flex;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;overscroll-behavior:contain;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);" @click.self="classOpen=false">
        <div class="glass p-6 w-full max-w-sm" style="margin:auto;">
            <h3 class="font-black text-lg mb-4">+ New Class</h3>
            <label class="text-xs text-gray-400 font-bold uppercase mb-1 block">Class name</label>
            <input type="text" x-model="className" class="ifield mb-3" placeholder="e.g. Grade 7 Blue">
            <p x-show="classError" x-cloak class="text-xs text-red-400 mb-3" x-text="classError"></p>
            <div class="flex gap-3">
                <button @click="createClass()" :disabled="classSaving" class="btn-p flex-1" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"><span x-show="!classSaving">Create Class</span><span x-show="classSaving">Creating…</span></button>
                <button @click="classOpen=false" class="flex-1 py-2.5 rounded-xl text-sm text-gray-400 hover:text-white" style="border:1px solid rgba(255,255,255,.1);">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Invite Teacher Modal --}}
    <div x-show="inviteOpen" style="position:fixed;inset:0;z-index:9990;display:flex;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;overscroll-behavior:contain;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);" @click.self="inviteOpen=false">
        <div class="glass p-6 w-full max-w-sm" style="margin:auto;">
            <h3 class="font-black text-lg mb-4">+ Invite Teacher</h3>
            <label class="text-xs text-gray-400 font-bold uppercase mb-1 block">Colleague's email</label>
            <input type="email" x-model="inviteEmail" class="ifield mb-3" placeholder="colleague@school.ac.ke">
            <p x-show="inviteError" x-cloak class="text-xs text-red-400 mb-3" x-text="inviteError"></p>
            <p x-show="inviteLink" x-cloak class="text-xs text-emerald-400 mb-3 break-all">Invite link (copied): <span x-text="inviteLink"></span></p>
            <div class="flex gap-3">
                <button @click="inviteTeacher()" :disabled="inviting" class="btn-p flex-1" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"><span x-show="!inviting">Send Invite</span><span x-show="inviting">Sending…</span></button>
                <button @click="inviteOpen=false" class="flex-1 py-2.5 rounded-xl text-sm text-gray-400 hover:text-white" style="border:1px solid rgba(255,255,255,.1);">Close</button>
            </div>
        </div>
    </div>

<script>
function teacherPortal() {
    return {
        addOpen: false, addEmail: '', addError: '', adding: false,
        inviteOpen: false, inviteEmail: '', inviteError: '', inviteLink: '', inviting: false,
        classOpen: false, className: '', classError: '', classSaving: false,
        csrf() { return document.querySelector('meta[name=csrf-token]').content; },

        async createClass() {
            this.classError = '';
            if (!this.className.trim()) { this.classError = 'Enter a class name.'; return; }
            this.classSaving = true;
            try {
                const res = await fetch('{{ route('school.teacher.classes.store', $school) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ name: this.className }),
                });
                const data = await res.json();
                if (data.success) { window.location.reload(); }
                else { this.classError = data.error || 'Could not create class.'; }
            } catch (e) { this.classError = 'Network error.'; }
            finally { this.classSaving = false; }
        },

        async deleteClass(id, name) {
            if (!confirm(`Delete class "${name}"? Its students and teacher fall back to whole-school scope — nobody is removed.`)) return;
            const res = await fetch(`{{ route('school.teacher.dashboard', $school) }}/classes/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
            });
            if ((await res.json()).success) window.location.reload();
        },

        async addStudent() {
            this.addError = '';
            if (!this.addEmail.trim()) { this.addError = 'Enter an email.'; return; }
            this.adding = true;
            try {
                const res = await fetch('{{ route('school.teacher.students.add', $school) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ email: this.addEmail }),
                });
                const data = await res.json();
                if (data.success) { window.location.reload(); }
                else { this.addError = data.error || 'Could not add student.'; }
            } catch (e) { this.addError = 'Network error.'; }
            finally { this.adding = false; }
        },

        async removeStudent(memberId, name) {
            if (!confirm(`Remove ${name} from this school?`)) return;
            const res = await fetch(`{{ route('school.teacher.dashboard', $school) }}/students/${memberId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
            });
            if ((await res.json()).success) window.location.reload();
        },

        async inviteTeacher() {
            this.inviteError = ''; this.inviteLink = '';
            if (!this.inviteEmail.trim()) { this.inviteError = 'Enter an email.'; return; }
            this.inviting = true;
            try {
                const res = await fetch('{{ route('school.teacher.teachers.invite', $school) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ email: this.inviteEmail }),
                });
                const data = await res.json();
                if (data.success) {
                    this.inviteLink = data.invite_url;
                    navigator.clipboard.writeText(data.invite_url).catch(() => {});
                } else { this.inviteError = data.error || 'Could not send invite.'; }
            } catch (e) { this.inviteError = 'Network error.'; }
            finally { this.inviting = false; }
        },

        async removeTeacher(id, name) {
            if (!confirm(`Remove ${name} as a teacher?`)) return;
            const res = await fetch(`{{ route('school.teacher.dashboard', $school) }}/teachers/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' },
            });
            if ((await res.json()).success) window.location.reload();
        },
    }
}

function assignClassTeacher(classId, teacherId) {
    fetch(`{{ route('school.teacher.dashboard', $school) }}/classes/${classId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ teacher_id: teacherId || null }),
    }).then(() => window.location.reload());
}

function assignStudentClass(memberId, classId) {
    fetch(`{{ route('school.teacher.dashboard', $school) }}/students/${memberId}/class`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ school_class_id: classId || null }),
    }).then(() => window.location.reload());
}
</script>
</body>
</html>
