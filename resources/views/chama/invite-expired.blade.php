<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>Invite Expired — PesaQuest</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,700,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#07060f] text-white font-sans min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-sm">
        <div class="text-6xl mb-4">🔗</div>
        <h1 class="text-2xl font-black mb-2">Invite Link Expired</h1>
        <p class="text-gray-400 text-sm leading-relaxed mb-6">This invite link is no longer valid. It may have expired or been revoked. Ask the person who invited you to generate a fresh link.</p>
        <a href="{{ route('chama.index') }}"
           class="inline-block px-6 py-3 rounded-2xl text-sm font-bold text-white transition-all hover:opacity-90"
           style="background: linear-gradient(135deg, rgba(99,102,241,.4), rgba(139,92,246,.3)); border: 1px solid rgba(139,92,246,.5);">
            Browse Open Chamas
        </a>
    </div>
</body>
</html>
