<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Money Tools — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="text-white min-h-screen">

<div class="border-b border-white/8 sticky top-0 z-40" style="background:rgba(7,6,15,0.9);backdrop-filter:blur(12px);">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Dashboard
        </a>
        <span class="text-sm font-black text-white">🧰 Smart Money Tools</span>
        <span class="w-24"></span>
    </div>
</div>

@include('partials.smart-money-tools')

</body>
</html>
