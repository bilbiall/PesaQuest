<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>How To Play — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #07060f; font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="text-white min-h-screen">

<div class="border-b border-white/8 sticky top-0 z-10" style="background:rgba(7,6,15,0.9);backdrop-filter:blur(12px);">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <span class="text-sm font-black text-white">❓ How To Play</span>
        <span class="w-10"></span>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl sm:text-4xl font-black mb-2">How PesaQuest Works</h1>
    <p class="text-gray-400 mb-8">Everything the first-time wizard shows you — come back here anytime you need a refresher.</p>

    <div class="space-y-4">
        @foreach($steps as $step)
        <div class="rounded-2xl p-5 flex gap-4" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);">
            <div class="text-4xl flex-shrink-0">{{ $step['icon'] ?? '💡' }}</div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-1">{{ $step['category'] ?? '' }}</p>
                <h2 class="text-lg font-black text-white mb-1.5">{{ $step['title'] ?? '' }}</h2>
                <p class="text-sm text-gray-400 leading-relaxed">{{ $step['body'] ?? '' }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-10 text-center">
        <a href="{{ route('dashboard') }}"
           class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black px-8 py-3 rounded-2xl text-sm hover:scale-105 transition-transform">
            Back to Dashboard
        </a>
    </div>
</div>

<x-mobile-bottom-nav active="home" />
</body>
</html>
