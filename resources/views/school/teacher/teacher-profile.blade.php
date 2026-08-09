<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>{{ $teacher->name ?? $teacher->email }} — Teacher Profile — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0a0a12; font-family: 'Figtree', sans-serif; }
        .stat-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 1.25rem 1.5rem; }
        .panel { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 1.5rem; }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased">

    <header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ route('school.teacher.dashboard', $school) }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ $school->school_name }} Teacher Portal
            </a>
        </div>
    </header>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        {{-- Identity --}}
        <div class="panel flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-black text-white">{{ $teacher->name ?? $teacher->email }} @if($teacher->role === 'owner')<span class="text-[10px] font-bold text-amber-400 ml-1 align-middle">OWNER</span>@endif</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $teacher->email }} · {{ $teacher->status === 'active' ? '✅ Active' : '⏳ Invited' }}</p>
                <p class="text-sm text-gray-400 mt-1">Class: <span class="font-bold text-white">{{ $teacher->schoolClass?->name ?? 'Unassigned' }}</span></p>
            </div>
            @if($teacher->accepted_at)
            <p class="text-xs text-gray-600">Joined {{ $teacher->accepted_at->format('d M Y') }}</p>
            @endif
        </div>

        @if(!$teacher->school_class_id)
        <div class="panel text-center py-10">
            <div class="text-4xl mb-3 opacity-40">📊</div>
            <p class="text-gray-400 font-semibold mb-1">No performance data yet</p>
            <p class="text-sm text-gray-600">Assign this teacher to a class from the dashboard's Classes panel to see performance data here.</p>
        </div>
        @else
        <div>
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-3">📊 Class Performance</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="stat-card">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Students</p>
                    <p class="text-2xl font-extrabold text-white">{{ $classStats['roster_size'] }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Avg Net Worth</p>
                    <p class="text-2xl font-extrabold text-cyan-400">Ksh {{ number_format($classStats['avg_net_worth']) }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Avg Credit Score</p>
                    <p class="text-2xl font-extrabold text-amber-400">{{ $classStats['avg_credit_score'] }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Avg Level</p>
                    <p class="text-2xl font-extrabold text-purple-400">{{ $classStats['avg_level'] }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Quest Completion</p>
                    <p class="text-2xl font-extrabold text-indigo-400">{{ $classStats['quest_completion_rate'] }}%</p>
                    <p class="text-xs text-gray-600 mt-1">{{ $classStats['completed_quests'] }}/{{ $classStats['total_quests'] }} quests</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Challenge Entries</p>
                    <p class="text-2xl font-extrabold text-white">{{ $classStats['challenge_entries'] }}</p>
                </div>
                <div class="stat-card">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Challenge Wins</p>
                    <p class="text-2xl font-extrabold text-emerald-400">{{ $classStats['challenge_wins'] }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
