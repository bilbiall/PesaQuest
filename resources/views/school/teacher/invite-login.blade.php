<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Invite — {{ $invite->school->school_name }} — PesaQuest</title>
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
        <h1 class="text-xl font-black mb-2">Teacher Invite</h1>
        <p class="text-gray-400 text-sm mb-1">You've been invited as a
            <span class="font-bold text-amber-400">{{ $invite->role === 'owner' ? 'school owner' : 'teacher' }}</span> at</p>
        <p class="text-lg font-black text-white mb-6">{{ $invite->school->school_name }}</p>

        <p class="text-gray-500 text-xs mb-6 leading-relaxed">
            Log in or create a free PesaQuest account with <b class="text-gray-300">{{ $invite->email }}</b>,
            then come back to this exact link to unlock the teacher portal —
            a roster of your students' progress, credit scores and net worth.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('login') }}" class="w-full py-3 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
               style="background:linear-gradient(135deg,#f59e0b,#d97706);">Log In</a>
            <a href="{{ route('register') }}" class="w-full py-3 rounded-xl text-sm font-bold transition-colors"
               style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);color:#e5e7eb;">Create an Account</a>
        </div>

        <p class="text-gray-600 text-[11px] mt-6">After logging in, open this same invite link again to accept.</p>
    </div>
</body>
</html>
