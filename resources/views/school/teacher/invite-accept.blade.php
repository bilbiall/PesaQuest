<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accept Teacher Invite — {{ $invite->school->school_name }} — PesaQuest</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { background:#080710; font-family:'Figtree',sans-serif; }</style>
</head>
<body class="min-h-screen text-white flex items-center justify-center px-4"
      style="background: radial-gradient(ellipse at top, rgba(245,158,11,0.1) 0%, transparent 50%), #080710;">

    <div class="w-full max-w-md rounded-3xl p-8 text-center" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
        <div class="text-5xl mb-4">🏫</div>
        <h1 class="text-xl font-black mb-2">Accept Teacher Invite?</h1>
        <p class="text-gray-400 text-sm mb-1">You'll join
            <span class="font-bold text-amber-400">{{ $invite->school->school_name }}</span> as a</p>
        <p class="text-lg font-black text-white mb-6">{{ $invite->role === 'owner' ? '👑 School Owner' : '👩‍🏫 Teacher' }}</p>

        @if(session('error'))
        <div class="mb-4 rounded-xl px-4 py-3 text-sm font-bold text-red-300" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);">
            {{ session('error') }}
        </div>
        @endif

        <p class="text-gray-500 text-xs mb-6 leading-relaxed">
            You'll be able to see every student's level, credit score, net worth and overdue bills —
            read-only, no access to their money or password.
            @if($invite->role === 'owner')
            As the owner you can also invite other teachers.
            @endif
        </p>

        <form method="POST" action="{{ route('school.teacher.invite.accept', $invite->invite_token) }}">
            @csrf
            <button type="submit" class="w-full py-3 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                    style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                ✓ Accept &amp; Open Teacher Portal
            </button>
        </form>

        <p class="text-gray-600 text-[11px] mt-6">Logged in as {{ auth()->user()->email }} — not you?
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('lo').submit();" class="text-amber-400 hover:underline">Log out</a>
            <form id="lo" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
        </p>
    </div>
</body>
</html>
