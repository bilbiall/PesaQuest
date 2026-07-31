<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} — {{ $school->school_name }} — PesaQuest</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .glass { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 1.25rem; }
    </style>
</head>
<body class="min-h-screen text-white">

    <header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
            <a href="{{ route('school.teacher.dashboard', $school) }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Roster
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">{{ $user->name }}</span>
        </div>
    </header>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        {{-- Identity + top stats --}}
        <div class="glass p-5 flex items-center gap-4 flex-wrap">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-black text-white flex-shrink-0" style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-[10rem]">
                <p class="font-black text-lg text-white">{{ $user->name }}</p>
                <p class="text-xs text-gray-500">{{ $progress?->chapterIcon() }} {{ $progress?->chapterName() ?? 'The Student' }} · Level {{ $progress->level ?? 1 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="glass p-4 text-center">
                <p class="text-lg font-black text-emerald-400">Ksh {{ number_format($progress->balance ?? 0) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Wallet</p>
            </div>
            <div class="glass p-4 text-center">
                <p class="text-lg font-black text-purple-400">Ksh {{ number_format($progress->net_worth_cache ?? 0) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Net Worth</p>
            </div>
            <div class="glass p-4 text-center">
                <p class="text-lg font-black {{ ($progress->credit_score ?? 500) >= 650 ? 'text-emerald-400' : (($progress->credit_score ?? 500) >= 500 ? 'text-amber-400' : 'text-red-400') }}">{{ $progress->credit_score ?? 500 }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Credit Score</p>
            </div>
            <div class="glass p-4 text-center">
                <p class="text-lg font-black text-indigo-400">{{ $progress->tick_count ?? 0 }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Game Days</p>
            </div>
        </div>

        {{-- Bills --}}
        <div class="glass p-5">
            <h2 class="font-black text-white mb-4">🧾 Bills</h2>
            @if($bills->isEmpty())
            <p class="text-sm text-gray-500 text-center py-4">No bills assigned yet.</p>
            @else
            <div class="space-y-2">
                @foreach($bills as $pb)
                <div class="flex items-center justify-between p-3 rounded-xl" style="background:rgba(255,255,255,0.02);">
                    <div class="flex items-center gap-2">
                        <span>{{ $pb->bill->icon ?? '💸' }}</span>
                        <span class="text-sm font-bold text-white">{{ $pb->bill->name ?? 'Bill' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-white">Ksh {{ number_format($pb->amount) }}</span>
                        <span class="text-[10px] font-bold ml-2 px-2 py-0.5 rounded-full {{ $pb->status === 'overdue' ? 'text-red-400 bg-red-500/10' : 'text-emerald-400 bg-emerald-500/10' }}">{{ ucfirst($pb->status) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent timeline --}}
        <div class="glass p-5">
            <h2 class="font-black text-white mb-4">📖 Recent Life Story</h2>
            @if($timeline->isEmpty())
            <p class="text-sm text-gray-500 text-center py-4">No life events recorded yet.</p>
            @else
            <div class="space-y-2">
                @foreach($timeline as $ple)
                <div class="flex items-start gap-3 p-3 rounded-xl" style="background:rgba(255,255,255,0.02);">
                    <span class="text-lg flex-shrink-0">{{ $ple->lifeEvent->icon ?? '⚡' }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white">{{ $ple->lifeEvent->title ?? 'Life Event' }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Game Day {{ $ple->tick_triggered }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <p class="text-center text-[11px] text-gray-600">Read-only view — teachers cannot modify a student's money, progress or password.</p>
    </div>
</body>
</html>
