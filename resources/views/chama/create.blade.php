<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>New Chama — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        @keyframes popIn { from{opacity:0;transform:scale(.95) translateY(8px)} to{opacity:1;transform:scale(1) translateY(0)} }
        .form-panel { animation:popIn .4s cubic-bezier(.34,1.56,.64,1) both; }
        .field-label { display:block; font-size:.75rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.5rem; }
        .field-input {
            width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            border-radius:1rem; padding:.75rem 1rem; color:#fff; font-size:.875rem; font-family:'Figtree',sans-serif;
            transition:border-color .2s,box-shadow .2s; outline:none;
        }
        .field-input:focus { border-color:rgba(139,92,246,.5); box-shadow:0 0 0 3px rgba(139,92,246,.1); }
        .field-input::placeholder { color:#4b5563; }
        .preview-card { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:1.5rem; overflow:hidden; }
        input[type=range] { -webkit-appearance:none; appearance:none; height:6px; background:rgba(255,255,255,.12); border-radius:3px; outline:none; }
        input[type=range]::-webkit-slider-thumb { -webkit-appearance:none; width:18px; height:18px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#8b5cf6); cursor:pointer; }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/50 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-4">
        <a href="{{ route('chama.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
        <h1 class="text-lg font-black">Create New Chama</h1>
    </div>
</nav>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10"
     x-data="{
         name: '',
         description: '',
         goalText: '',
         targetAmount: 0,
         monthlyContribution: 2000,
         maxMembers: 5,
         visibility: '{{ old('visibility', 'public') }}'
     }">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

        {{-- ── Form ── --}}
        <div class="form-panel rounded-2xl p-4 sm:p-5" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
            <h2 class="text-lg font-black mb-4">Chama Details</h2>

            @if($errors->any())
            <div class="rounded-2xl px-5 py-4 mb-6 text-sm text-red-300" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);">
                <p class="font-bold mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('chama.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="field-label">Chama Name *</label>
                    <input x-model="name" type="text" name="name" class="field-input"
                           placeholder="e.g. Nairobi Investors Circle" maxlength="80" value="{{ old('name') }}" required>
                    <p class="text-xs text-gray-600 mt-1">Max 80 characters</p>
                </div>

                <div>
                    <label class="field-label">Description</label>
                    <textarea x-model="description" name="description" class="field-input" rows="2"
                              placeholder="What is this chama about?">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="field-label">Investment Goal</label>
                    <input x-model="goalText" type="text" name="goal_text" class="field-input"
                           placeholder="e.g. Buy a Nairobi rental property by Dec 2027" maxlength="200" value="{{ old('goal_text') }}">
                </div>

                <div>
                    <label class="field-label">Target Amount (Ksh)</label>
                    <input x-model.number="targetAmount" type="number" name="target_amount" class="field-input"
                           placeholder="0" min="0" value="{{ old('target_amount', 0) }}">
                </div>

                <div>
                    <label class="field-label">Monthly Contribution per Member (Ksh) *</label>
                    <input x-model.number="monthlyContribution" type="number" name="monthly_contribution" class="field-input"
                           placeholder="2000" min="500" value="{{ old('monthly_contribution', 2000) }}" required>
                    <p class="text-xs text-gray-600 mt-1">Minimum Ksh 500/month</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="field-label mb-0">Max Members *</label>
                        <span class="font-black text-indigo-300" x-text="maxMembers + ' members'"></span>
                    </div>
                    <input x-model.number="maxMembers" type="range" name="max_members" min="3" max="10" step="1"
                           class="w-full" value="{{ old('max_members', 5) }}">
                    <div class="flex justify-between text-xs text-gray-600 mt-1">
                        <span>3</span><span>5</span><span>7</span><span>10</span>
                    </div>
                </div>

                <div>
                    <label class="field-label">Who can find this chama? *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="rounded-2xl p-3 cursor-pointer transition-all"
                               :style="visibility === 'public' ? 'background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.45)' : 'background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1)'">
                            <input type="radio" name="visibility" value="public" x-model="visibility" class="hidden">
                            <span class="text-sm font-black text-white block">🌍 Public</span>
                            <span class="text-[11px] text-gray-500">Listed in the directory — anyone who meets your requirements can join</span>
                        </label>
                        <label class="rounded-2xl p-3 cursor-pointer transition-all"
                               :style="visibility === 'private' ? 'background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.5)' : 'background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1)'">
                            <input type="radio" name="visibility" value="private" x-model="visibility" class="hidden">
                            <span class="text-sm font-black text-white block">🔒 Private</span>
                            <span class="text-[11px] text-gray-500">Invisible — entry only by friend invite or your join code</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-600 mt-1" x-show="visibility === 'private'" x-cloak>You'll get a 6-character join code — perfect for a classroom board ✏️</p>
                </div>

                <div x-show="visibility === 'public'">
                    <label class="field-label">Entry Requirements <span class="normal-case font-semibold text-gray-600">(optional — raise the bar for who joins)</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <p class="text-[10px] text-gray-500 mb-1">Min level</p>
                            <select name="min_level" class="field-input" style="padding:.5rem .6rem;">
                                @foreach([0 => 'Anyone', 3 => 'Level 3+', 5 => 'Level 5+', 10 => 'Level 10+'] as $v => $lbl)
                                <option value="{{ $v }}" @selected(old('min_level', 0) == $v)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 mb-1">Min credit</p>
                            <select name="min_credit_score" class="field-input" style="padding:.5rem .6rem;">
                                @foreach([0 => 'Any', 550 => '550+ Fair', 650 => '650+ Good', 750 => '750+ Excellent'] as $v => $lbl)
                                <option value="{{ $v }}" @selected(old('min_credit_score', 0) == $v)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 mb-1">Min savings</p>
                            <select name="min_savings" class="field-input" style="padding:.5rem .6rem;">
                                @foreach([0 => 'Any', 1000 => '1,000+', 5000 => '5,000+', 10000 => '10,000+'] as $v => $lbl)
                                <option value="{{ $v }}" @selected(old('min_savings', 0) == $v)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-4 rounded-2xl font-black text-sm tracking-wide transition-all mt-2"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 8px 24px rgba(99,102,241,.35);">
                    🤝 Create Chama
                </button>
            </form>
        </div>

        {{-- ── Live Preview ── --}}
        <div class="form-panel lg:sticky lg:top-24" style="animation-delay:.1s">
            <div class="preview-card">
                {{-- Header --}}
                <div class="relative h-20 flex items-center px-5 gap-3 overflow-hidden"
                     style="background:linear-gradient(135deg,rgba(99,102,241,.35),rgba(139,92,246,.3),rgba(15,14,26,.8));">
                    <div class="text-2xl">🤝</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-white leading-tight"
                           x-text="name || 'Your Chama Name'"></p>
                        <p class="text-xs text-indigo-300 mt-0.5">👑 Chairman (you)</p>
                    </div>
                    <div class="text-xs font-black px-2 py-1 rounded-lg"
                         style="background:rgba(0,0,0,.3);color:#f59e0b;">
                        Forming
                    </div>
                </div>

                {{-- Goal text --}}
                <div class="px-5 pt-4">
                    <p class="text-xs text-gray-500 italic"
                       x-text="goalText || 'No goal set yet'"
                       :class="goalText ? 'text-gray-300' : 'text-gray-600'"></p>
                </div>

                {{-- Stats --}}
                <div class="p-5 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl p-3" style="background:rgba(255,255,255,.04);">
                        <p class="text-xs text-gray-500">Members</p>
                        <p class="font-black text-white text-sm" x-text="'1/' + maxMembers"></p>
                    </div>
                    <div class="rounded-xl p-3" style="background:rgba(255,255,255,.04);">
                        <p class="text-xs text-gray-500">Monthly</p>
                        <p class="font-black text-white text-sm" x-text="'Ksh ' + Number(monthlyContribution).toLocaleString()"></p>
                    </div>
                    <div class="rounded-xl p-3" style="background:rgba(255,255,255,.04);">
                        <p class="text-xs text-gray-500">Pool</p>
                        <p class="font-black text-white text-sm">Ksh 0</p>
                    </div>
                </div>

                {{-- Pool progress --}}
                <div class="px-5 pb-5" x-show="targetAmount > 0">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="text-gray-400">Progress to goal</span>
                        <span class="text-indigo-300 font-bold" x-text="'Ksh ' + Number(targetAmount).toLocaleString()"></span>
                    </div>
                    <div style="height:6px;border-radius:3px;background:rgba(255,255,255,.08);">
                        <div style="width:0%;height:100%;border-radius:3px;background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">0% of goal reached</p>
                </div>

                {{-- Monthly potential --}}
                <div class="px-5 pb-5">
                    <div class="rounded-2xl p-4 text-center" style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.15);">
                        <p class="text-xs text-gray-400 mb-1">Monthly pool growth (full chama)</p>
                        <p class="text-2xl font-black text-indigo-300"
                           x-text="'Ksh ' + Number(monthlyContribution * maxMembers).toLocaleString()"></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="maxMembers + ' members × Ksh ' + Number(monthlyContribution).toLocaleString()"></p>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-600 text-center mt-4">
                You'll be the chairman. The chama activates once 3+ members join.
            </p>
        </div>

    </div>
</div>

</body>
</html>
