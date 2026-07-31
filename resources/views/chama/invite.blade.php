<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>Join {{ $chama->name }} — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; min-height: 100vh; }
        @keyframes fadeUp  { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes glow    { 0%,100% { opacity: .6; } 50% { opacity: 1; } }
        @keyframes shimmer { 0% { background-position: 200% center; } 100% { background-position: -200% center; } }
        .fade-up-1 { animation: fadeUp .45s .05s ease both; }
        .fade-up-2 { animation: fadeUp .45s .15s ease both; }
        .fade-up-3 { animation: fadeUp .45s .25s ease both; }
        .fade-up-4 { animation: fadeUp .45s .35s ease both; }
        .shimmer-name {
            background: linear-gradient(90deg, #fff 20%, #a78bfa 50%, #fff 80%);
            background-size: 200% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: shimmer 3s linear infinite;
        }
        .glass { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); border-radius: 1.5rem; }
        .glass-inner { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); border-radius: 1rem; }
        .rule-row { display: flex; align-items: flex-start; gap: .75rem; padding: .75rem 0; border-bottom: 1px solid rgba(255,255,255,.05); }
        .rule-row:last-child { border-bottom: none; }
    </style>
</head>
<body class="text-white flex flex-col items-center justify-center px-4 py-12">

{{-- Ambient glow --}}
<div style="position:fixed;top:-120px;left:50%;transform:translateX(-50%);width:600px;height:600px;background:radial-gradient(circle,rgba(99,102,241,.18) 0%,transparent 70%);pointer-events:none;animation:glow 4s ease-in-out infinite;"></div>

<div class="w-full max-w-lg relative">

    {{-- PesaQuest logo / brand --}}
    <div class="flex justify-center mb-8 fade-up-1">
        <a href="{{ route('landing') }}" class="flex items-center gap-2 opacity-70 hover:opacity-100 transition-opacity">
            <img src="{{ asset('moski-logo.png') }}" alt="PesaQuest" class="h-9 w-9 rounded-xl object-cover">
            <span class="text-sm font-black text-gray-300">PesaQuest</span>
        </a>
    </div>

    {{-- Inviter card --}}
    <div class="glass p-6 mb-4 fade-up-1">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl shrink-0"
                 style="background: linear-gradient(135deg, #6366f1, #a78bfa);">
                {{ strtoupper(substr($invite->inviter->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">You were invited by</p>
                <p class="text-xl font-black text-white">{{ $invite->inviter->name }}</p>
                @php $inviteGameDays = app(\App\Services\GameClock::class)->gameDaysUntil($invite->expires_at); @endphp
                <p class="text-xs text-violet-400 font-semibold mt-0.5">
                    {{ $inviteGameDays > 0 ? 'Link valid for ' . $inviteGameDays . ' more game day' . ($inviteGameDays === 1 ? '' : 's') : 'Link expired' }}
                </p>
            </div>
        </div>
        <p class="text-sm text-gray-400 leading-relaxed">
            <span class="text-white font-semibold">{{ $invite->inviter->name }}</span> has invited you to join their cooperative investment group on PesaQuest. Pool your resources, vote on investments, and build wealth together.
        </p>
    </div>

    {{-- Chama details --}}
    <div class="glass p-6 mb-4 fade-up-2">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Chama Name</p>
                <h1 class="text-3xl font-black shimmer-name">{{ $chama->name }}</h1>
                @if($chama->goal_text)
                <p class="text-sm text-gray-400 mt-2 italic leading-relaxed">{{ $chama->goal_text }}</p>
                @endif
            </div>
            <div class="text-xs font-black px-2.5 py-1 rounded-xl shrink-0"
                 style="background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.3); color: #6ee7b7;">
                {{ ucfirst($chama->status) }}
            </div>
        </div>

        @if($chama->description)
        <p class="text-sm text-gray-300 leading-relaxed mb-5">{{ $chama->description }}</p>
        @endif

        {{-- Stats grid --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="glass-inner p-3 text-center">
                <p class="text-xs text-gray-500 mb-1">Members</p>
                <p class="text-xl font-black text-white">{{ $chama->memberCount() }}<span class="text-gray-600 text-sm">/{{ $chama->max_members }}</span></p>
            </div>
            <div class="glass-inner p-3 text-center">
                <p class="text-xs text-gray-500 mb-1">Pool Balance</p>
                <p class="text-base font-black text-indigo-300">Ksh {{ number_format($chama->pool_balance) }}</p>
            </div>
            <div class="glass-inner p-3 text-center">
                <p class="text-xs text-gray-500 mb-1">Spots Left</p>
                <p class="text-xl font-black {{ ($chama->max_members - $chama->memberCount()) > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ max(0, $chama->max_members - $chama->memberCount()) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Rules --}}
    <div class="glass p-6 mb-6 fade-up-3">
        <p class="text-sm font-black text-white mb-4">📋 Chama Rules</p>
        <div>
            <div class="rule-row">
                <span class="text-xl shrink-0">💰</span>
                <div>
                    <p class="text-sm font-bold text-white">Monthly Contribution Required</p>
                    <p class="text-xs text-gray-400 mt-0.5">Every member must contribute <span class="text-indigo-300 font-semibold">Ksh {{ number_format($chama->monthly_contribution) }}</span> each game month to maintain their share of the pool.</p>
                </div>
            </div>
            <div class="rule-row">
                <span class="text-xl shrink-0">🗳️</span>
                <div>
                    <p class="text-sm font-bold text-white">Democratic Governance</p>
                    <p class="text-xs text-gray-400 mt-0.5">All major decisions — buying assets, changing contributions, removing members — require a majority vote from all active members.</p>
                </div>
            </div>
            <div class="rule-row">
                <span class="text-xl shrink-0">📊</span>
                <div>
                    <p class="text-sm font-bold text-white">Shares Based on Contributions</p>
                    <p class="text-xs text-gray-400 mt-0.5">Your share percentage reflects your total contributions. More you contribute, more of the profits you receive during distributions.</p>
                </div>
            </div>
            <div class="rule-row">
                <span class="text-xl shrink-0">⚠️</span>
                <div>
                    <p class="text-sm font-bold text-white">10% Exit Penalty</p>
                    <p class="text-xs text-gray-400 mt-0.5">If you leave the chama before it's dissolved, a 10% penalty is applied to your contributed amount. Commit with intent!</p>
                </div>
            </div>
            <div class="rule-row">
                <span class="text-xl shrink-0">👑</span>
                <div>
                    <p class="text-sm font-bold text-white">{{ $chama->memberCount() }} Current Member{{ $chama->memberCount() !== 1 ? 's' : '' }}</p>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @foreach($chama->activeMembers->take(5) as $mem)
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                              style="background: rgba(99,102,241,.15); border: 1px solid rgba(99,102,241,.25); color: #c4b5fd;">
                            {{ $mem->user->name }}{{ $mem->role === 'chairman' ? ' 👑' : '' }}
                        </span>
                        @endforeach
                        @if($chama->memberCount() > 5)
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold text-gray-500" style="background: rgba(255,255,255,.05);">+{{ $chama->memberCount() - 5 }} more</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="fade-up-4">
        @if($chama->isFull())
        <div class="glass p-5 text-center">
            <p class="text-2xl mb-2">😔</p>
            <p class="font-bold text-gray-300">This chama is full</p>
            <p class="text-sm text-gray-500 mt-1">All {{ $chama->max_members }} spots have been filled. Ask {{ $invite->inviter->name }} about other opportunities.</p>
        </div>
        @elseif(!auth()->check())
        <div class="glass p-6 text-center">
            <p class="text-sm text-gray-400 mb-4">You need a PesaQuest account to join. It's free!</p>
            <a href="{{ route('register') }}?invite={{ $invite->token }}"
               class="block w-full py-4 rounded-2xl text-base font-black text-white transition-all hover:opacity-90 mb-3"
               style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 8px 32px rgba(99,102,241,.4);">
                Create Free Account & Join
            </a>
            <a href="{{ route('login') }}?invite={{ $invite->token }}"
               class="block w-full py-3 rounded-2xl text-sm font-bold transition-all"
               style="background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); color: #9ca3af;">
                I already have an account → Log in
            </a>
        </div>
        @else
        <form action="{{ route('chama.invite.accept', $invite->token) }}" method="POST">
            @csrf
            @if(session('error'))
            <div class="mb-4 rounded-2xl px-5 py-3 text-sm font-semibold text-red-300 flex items-center gap-3"
                 style="background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25);">
                ❌ {{ session('error') }}
            </div>
            @endif
            <button type="submit"
                    class="block w-full py-4 rounded-2xl text-base font-black text-white text-center transition-all hover:opacity-90 mb-3"
                    style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 8px 32px rgba(99,102,241,.4);">
                🤝 Join {{ $chama->name }}
            </button>
        </form>
        <p class="text-center text-xs text-gray-600">
            Joining as <span class="text-gray-400 font-semibold">{{ auth()->user()->name }}</span> ·
            <a href="{{ route('chama.index') }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">View my chamas</a>
        </p>
        @endif
    </div>

</div>

</body>
</html>
