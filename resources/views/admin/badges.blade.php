<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<title>Badge Management – PesaQuest</title>
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
@vite(['resources/css/app.css','resources/js/app.js'])
<style>
body{background:#08070f;}
[x-cloak]{display:none!important}
.ifield{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:0.75rem;color:white;padding:0.55rem 0.8rem;width:100%;font-size:0.8rem;outline:none;transition:border-color 0.2s;}
.ifield:focus{border-color:rgba(99,102,241,0.6);}
.ifield option{background:#1a1a2e;}
.badge-card{background:rgba(255,255,255,0.025);border:1px solid rgba(255,255,255,0.07);border-radius:1.25rem;transition:border-color 0.2s;}
.badge-card:hover{border-color:rgba(99,102,241,0.35);}
.modal-bg{background:rgba(12,11,22,0.97);border:1px solid rgba(99,102,241,0.3);backdrop-filter:blur(20px);}
</style>
</head>
<body class="text-white font-sans antialiased" x-data="badgeManager()" x-cloak>
@include('gameset.partials.topnav', ['active' => 'badges'])

<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-white">🏅 Badge Management</h1>
            <p class="text-gray-500 text-sm mt-0.5">Create, edit, and award badges to players</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-sm text-indigo-400 border border-indigo-500/30 px-4 py-2 rounded-xl hover:border-indigo-500/60 transition-colors">← Gameset</a>
            <button @click="openCreate()" class="text-sm font-bold text-white px-4 py-2 rounded-xl transition-all hover:opacity-90"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                + New Badge
            </button>
        </div>
    </div>

    {{-- Trigger-type guide --}}
    <div class="rounded-2xl p-5 mb-7" style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);">
        <div class="text-xs font-bold text-amber-400 mb-2 uppercase tracking-widest">Badge Trigger Types Guide</div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 text-xs text-gray-400">
            <div class="flex items-start gap-1.5"><span class="text-amber-400 flex-shrink-0">⭐</span><div><strong class="text-gray-300">Level</strong> — reaches level N</div></div>
            <div class="flex items-start gap-1.5"><span class="text-purple-400 flex-shrink-0">🏆</span><div><strong class="text-gray-300">Points</strong> — total XP ≥ N</div></div>
            <div class="flex items-start gap-1.5"><span class="text-blue-400 flex-shrink-0">🎯</span><div><strong class="text-gray-300">Decisions</strong> — total choices made ≥ N</div></div>
            <div class="flex items-start gap-1.5"><span class="text-orange-400 flex-shrink-0">🔥</span><div><strong class="text-gray-300">Streak</strong> — consecutive daily logins ≥ N</div></div>
            <div class="flex items-start gap-1.5"><span class="text-green-400 flex-shrink-0">📈</span><div><strong class="text-gray-300">Investment</strong> — investments claimed ≥ N</div></div>
            <div class="flex items-start gap-1.5"><span class="text-emerald-400 flex-shrink-0">💰</span><div><strong class="text-gray-300">Balance</strong> — game balance ≥ Ksh N at any point</div></div>
            <div class="flex items-start gap-1.5"><span class="text-cyan-400 flex-shrink-0">🗺️</span><div><strong class="text-gray-300">Quest Complete</strong> — quests submitted ≥ N</div></div>
            <div class="flex items-start gap-1.5"><span class="text-lime-400 flex-shrink-0">🐷</span><div><strong class="text-gray-300">Save Choices</strong> — consecutive save-positive decisions ≥ N</div></div>
            <div class="flex items-start gap-1.5"><span class="text-yellow-400 flex-shrink-0">💼</span><div><strong class="text-gray-300">Career Unlocked</strong> — awarded when user completes an education arc</div></div>
            <div class="flex items-start gap-1.5"><span class="text-indigo-400 flex-shrink-0">📖</span><div><strong class="text-gray-300">Story Complete</strong> — admin confirms story arc done</div></div>
            <div class="flex items-start gap-1.5"><span class="text-pink-400 flex-shrink-0">👑</span><div><strong class="text-gray-300">Manual</strong> — only via the Award button</div></div>
        </div>
    </div>

    {{-- Badge grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($badges as $badge)
        <div class="badge-card p-4 flex items-start gap-4">
            {{-- Icon / Image --}}
            <div class="w-16 h-16 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden"
                 style="background:linear-gradient(135deg,{{ $badge->color ?? '#f59e0b' }}22,{{ $badge->color ?? '#f59e0b' }}11);border:2px solid {{ $badge->color ?? '#f59e0b' }}44;">
                @if($badge->image_url)
                    <img src="{{ $badge->image_url }}" alt="{{ $badge->name }}" class="w-12 h-12 object-contain">
                @else
                    <x-icon :name="$badge->icon ?? 'medal'" class="w-7 h-7" />
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-white text-sm">{{ $badge->name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $badge->description }}</div>
                    </div>
                    @if(!$badge->is_active)
                    <span class="text-[10px] bg-red-500/15 border border-red-500/25 text-red-400 px-2 py-0.5 rounded-full flex-shrink-0">Off</span>
                    @endif
                </div>

                <div class="flex items-center gap-2 mt-2 flex-wrap">
                    @php
                        $triggerColors = ['level'=>'#a5b4fc','points'=>'#c4b5fd','decisions'=>'#7dd3fc','streak'=>'#fb923c','investment'=>'#6ee7b7','story_complete'=>'#818cf8','manual'=>'#f9a8d4'];
                        $col = $triggerColors[$badge->trigger_type] ?? '#a5b4fc';
                    @endphp
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:{{ $col }}18;border:1px solid {{ $col }}44;color:{{ $col }};">
                        {{ ucfirst(str_replace('_',' ',$badge->trigger_type)) }} {{ $badge->trigger_value > 0 ? '≥ '.$badge->trigger_value : '' }}
                    </span>
                    <span class="text-[10px] text-gray-600">{{ $badge->users_count ?? '' }} holders</span>
                </div>

                <div class="flex items-center gap-2 mt-3">
                    <button @click="openEdit({{ json_encode($badge) }})"
                            class="text-xs text-indigo-400 border border-indigo-500/25 px-3 py-1 rounded-lg hover:border-indigo-500/50 transition-colors">Edit</button>
                    <button @click="openAward({{ $badge->id }}, '{{ $badge->name }}')"
                            class="text-xs text-emerald-400 border border-emerald-500/25 px-3 py-1 rounded-lg hover:border-emerald-500/50 transition-colors">Award</button>
                    <button @click="deleteBadge({{ $badge->id }})"
                            class="text-xs text-red-400 border border-red-500/25 px-3 py-1 rounded-lg hover:border-red-500/50 transition-colors">Delete</button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-16 text-gray-600">
            <div class="text-5xl mb-3">🏅</div>
            <p>No badges yet — create the first one!</p>
        </div>
        @endforelse
    </div>
</div>

{{-- ── CREATE / EDIT MODAL ── --}}
<div x-show="showModal"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70 backdrop-blur-sm" @click.self="showModal=false" x-cloak>
    <div class="modal-bg rounded-3xl p-7 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-black text-xl text-white" x-text="editId ? 'Edit Badge' : 'Create Badge'"></h3>
            <button @click="showModal=false" class="text-gray-400 hover:text-white p-1 rounded-xl hover:bg-white/5">✕</button>
        </div>

        <form @submit.prevent="saveBadge()" class="space-y-4" enctype="multipart/form-data">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">Badge Name<x-help-tip text="The title shown on the player's profile and in award notifications when they earn this badge." example="First Step" /></label>
                    <input type="text" x-model="form.name" required maxlength="60" class="ifield" placeholder="e.g. First Step">
                </div>
                <div class="col-span-2">
                    <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">Description<x-help-tip text="The short caption players see under the badge explaining what they did to earn it." example="You made your very first savings deposit!" /></label>
                    <input type="text" x-model="form.description" required maxlength="255" class="ifield" placeholder="Short motivating description">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">Icon (name)<x-help-tip text="A name from the app's icon set (e.g. medal, trophy, star) shown as the badge's icon on the grid and player profile — used only if no image is uploaded below." example="medal" /></label>
                    <input type="text" x-model="form.icon" maxlength="30" class="ifield" placeholder="medal">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">Color<x-help-tip text="The accent color used for the badge card's glow, border, and icon background so it stands out in its own theme." example="#f59e0b" /></label>
                    <input type="color" x-model="form.color" class="ifield h-10 cursor-pointer p-1">
                </div>
                <div class="col-span-2">
                    <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">Badge Image (replaces emoji)<x-help-tip text="An optional custom picture that overrides the emoji icon everywhere the badge is displayed, for a more polished look." example="badge-first-step.png" /></label>
                    <input type="file" id="badge-img-input" accept="image/png,image/jpeg,image/svg+xml,image/gif,image/webp"
                           class="ifield cursor-pointer" @change="previewBadgeImg">
                    <p class="text-[10px] text-gray-600 mt-1">📐 Best: <strong class="text-gray-400">200×200 px square</strong> PNG or SVG with transparent background. Max 2 MB. The image will be shown at 56×56 px on the profile.</p>
                    <div x-show="form.previewUrl" class="mt-2 flex items-center gap-3">
                        <img :src="form.previewUrl" class="w-14 h-14 rounded-xl object-contain border border-white/10 bg-white/5">
                        <span class="text-xs text-gray-500">Preview</span>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">Trigger Type<x-help-tip text="The player milestone that automatically awards this badge. Pick 'Manually Awarded' if it should only ever be granted by an admin via the Award button." example="Reach Level X" /></label>
                    <select x-model="form.trigger_type" class="ifield">
                        <optgroup label="── Progress ──" style="color:#6b7280;">
                        <option value="level">⭐ Reach Level X</option>
                        <option value="points">🏆 Earn X Total XP</option>
                        <option value="streak">🔥 X-Day Login Streak</option>
                        </optgroup>
                        <optgroup label="── Financial ──" style="color:#6b7280;">
                        <option value="balance">💰 Save KES X</option>
                        <option value="net_worth">🏦 Reach Net Worth KES X</option>
                        <option value="investment">📈 Make X Investments</option>
                        <option value="asset_purchased">🏘️ Buy X Assets</option>
                        </optgroup>
                        <optgroup label="── Career & Learning ──" style="color:#6b7280;">
                        <option value="job_hired">💼 Get Hired X Times</option>
                        <option value="course_complete">📚 Complete X Courses</option>
                        </optgroup>
                        <optgroup label="── Quests ──" style="color:#6b7280;">
                        <option value="quest_complete">🗺️ Complete X Quests</option>
                        </optgroup>
                        <optgroup label="── Community ──" style="color:#6b7280;">
                        <option value="forum_karma">💬 Earn X Forum Karma</option>
                        </optgroup>
                        <optgroup label="── Manual ──" style="color:#6b7280;">
                        <option value="manual">👑 Manually Awarded</option>
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">
                        Trigger Value
                        <span class="normal-case text-gray-600" x-text="triggerHelp()"></span>
                        <x-help-tip text="The threshold number the player must reach for the chosen trigger type before the badge is auto-awarded. Ignored when Trigger Type is 'Manually Awarded'." example="5" />
                    </label>
                    <input type="number" x-model.number="form.trigger_value" min="0" class="ifield" placeholder="e.g. 5">
                </div>
                <div class="col-span-2 flex items-center gap-3">
                    <input type="checkbox" id="is_active" x-model="form.is_active" class="rounded">
                    <label for="is_active" class="text-gray-300 text-sm">Badge is active (auto-awarded when triggered)<x-help-tip text="Turn off to pause automatic awarding of this badge without deleting it — players who already earned it keep it." example="On for most badges" /></label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="showModal=false" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-gray-400 border border-white/10 hover:border-white/20 transition-colors">Cancel</button>
                <button type="submit" :disabled="saving" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <span x-text="saving ? 'Saving…' : (editId ? 'Update Badge' : 'Create Badge')"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── AWARD MODAL ── --}}
<div x-show="showAward"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/70 backdrop-blur-sm" @click.self="showAward=false" x-cloak>
    <div class="modal-bg rounded-3xl p-7 w-full max-w-sm">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-black text-lg text-white">Award Badge</h3>
            <button @click="showAward=false" class="text-gray-400 hover:text-white">✕</button>
        </div>
        <p class="text-sm text-gray-400 mb-4">Award "<strong class="text-white" x-text="awardBadgeName"></strong>" to a player.</p>
        <div class="space-y-3">
            <div>
                <label class="block text-gray-400 text-xs font-bold mb-1 uppercase tracking-wide">Player User ID<x-help-tip text="The numeric account ID of the player who should manually receive this badge, bypassing its automatic trigger condition." example="482" /></label>
                <input type="number" x-model.number="awardUserId" class="ifield" placeholder="Enter user ID">
            </div>
            <button @click="awardBadge()" :disabled="awarding" class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                    style="background:linear-gradient(135deg,#10b981,#059669);">
                <span x-text="awarding ? 'Awarding…' : 'Award Badge'"></span>
            </button>
            <div x-show="awardMsg" class="text-sm text-emerald-400 text-center" x-text="awardMsg"></div>
        </div>
    </div>
</div>

<script>
function badgeManager() {
    return {
        showModal: false,
        showAward: false,
        editId: null,
        saving: false,
        awarding: false,
        awardBadgeId: null,
        awardBadgeName: '',
        awardUserId: '',
        awardMsg: '',

        form: {
            name: '', description: '', icon: 'medal', color: '#f59e0b',
            trigger_type: 'level', trigger_value: 1, is_active: true,
            previewUrl: '', imageFile: null,
        },

        triggerHelp() {
            const hints = {
                level:           '— enter level number, e.g. 5',
                points:          '— total XP, e.g. 1000',
                streak:          '— consecutive days, e.g. 7',
                balance:         '— KES amount, e.g. 50000',
                net_worth:       '— KES net worth, e.g. 200000',
                investment:      '— investments made, e.g. 3',
                asset_purchased: '— assets owned, e.g. 5',
                job_hired:       '— times hired, e.g. 1',
                course_complete: '— courses completed, e.g. 3',
                quest_complete:  '— quests completed, e.g. 2',
                forum_karma:     '— karma points, e.g. 10',
                manual:          '— set 0; admin-only via Award button',
            };
            return hints[this.form.trigger_type] || '';
        },

        openCreate() {
            this.editId = null;
            this.form = { name:'', description:'', icon:'medal', color:'#f59e0b', trigger_type:'level', trigger_value:1, is_active:true, previewUrl:'', imageFile:null };
            this.showModal = true;
        },

        openEdit(badge) {
            this.editId = badge.id;
            this.form = {
                name: badge.name, description: badge.description, icon: badge.icon || 'medal',
                color: badge.color || '#f59e0b', trigger_type: badge.trigger_type || 'level',
                trigger_value: badge.trigger_value || 1, is_active: badge.is_active,
                previewUrl: badge.image_url || '', imageFile: null,
            };
            this.showModal = true;
        },

        openAward(id, name) {
            this.awardBadgeId = id;
            this.awardBadgeName = name;
            this.awardUserId = '';
            this.awardMsg = '';
            this.showAward = true;
        },

        previewBadgeImg(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.form.imageFile = file;
            const reader = new FileReader();
            reader.onload = ev => this.form.previewUrl = ev.target.result;
            reader.readAsDataURL(file);
        },

        async saveBadge() {
            this.saving = true;
            const fd = new FormData();
            const fields = ['name','description','icon','color','trigger_type'];
            fields.forEach(k => fd.append(k, this.form[k]));
            fd.append('is_active', this.form.is_active ? '1' : '0');
            fd.append('trigger_value', this.form.trigger_value);
            if (this.form.imageFile) fd.append('image', this.form.imageFile);

            const url  = this.editId ? `/gameset/badges/${this.editId}` : '/gameset/badges';
            const method = this.editId ? 'POST' : 'POST';
            if (this.editId) fd.append('_method', 'PUT');

            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: fd,
                });
                if (res.ok) { window.location.reload(); }
                else { const d = await res.json(); alert(Object.values(d.errors||{}).flat().join('\n')); }
            } catch(e) { alert('Error saving badge'); }
            this.saving = false;
        },

        async deleteBadge(id) {
            if (!confirm('Delete this badge? Players who earned it will keep it.')) return;
            await fetch(`/gameset/badges/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            });
            window.location.reload();
        },

        async awardBadge() {
            if (!this.awardUserId) { this.awardMsg = 'Enter a user ID'; return; }
            this.awarding = true;
            const res = await fetch('/gameset/badges/award', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ badge_id: this.awardBadgeId, user_id: this.awardUserId }),
            });
            const d = await res.json();
            this.awarding = false;
            this.awardMsg = d.already_had ? 'Player already has this badge.' : '✓ Badge awarded!';
        },
    }
}
</script>
</body>
</html>
