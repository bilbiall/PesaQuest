<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PesaQuest') }} – Kenya's Financial Literacy Game</title>
    <meta name="description" content="PesaQuest is Kenya's #1 financial literacy game. Explore Pesa City, earn a virtual salary, invest, and build real money skills through play. Free to start.">
    <meta name="keywords"    content="financial literacy Kenya, money game Kenya, PesaQuest, Moski, learn to save Kenya, budgeting game, Pesa City, investment game Africa, fintech education, personal finance game">
    <meta name="author"      content="Moski – It's Possible">

    {{-- Open Graph / WhatsApp / Facebook --}}
    <meta property="og:type"         content="website">
    <meta property="og:url"          content="{{ url()->current() }}">
    <meta property="og:title"        content="PesaQuest – Kenya's Financial Literacy Game">
    <meta property="og:description"  content="Explore Pesa City, earn salary, pay bills, invest and build wealth in Kenya's most addictive financial literacy game. Free to start.">
    <meta property="og:image"        content="{{ asset('img/game/pwa-og.jpg') }}">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="1200">
    <meta property="og:image:type"   content="image/jpeg">
    <meta property="og:site_name"    content="PesaQuest by Moski">
    <meta property="og:locale"       content="en_KE">

    {{-- Twitter / X --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="PesaQuest – Kenya's Financial Literacy Game">
    <meta name="twitter:description" content="Explore Pesa City, earn salary, pay bills, invest and build wealth in Kenya's most addictive financial literacy game.">
    <meta name="twitter:image"       content="{{ asset('img/game/pwa-og.jpg') }}">

    {{-- PWA --}}
    <link rel="manifest"             href="/manifest.json">
    <meta name="theme-color"         content="#6366f1">
    <meta name="mobile-web-app-capable"              content="yes">
    <meta name="apple-mobile-web-app-capable"        content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title"          content="PesaQuest">
    <link rel="apple-touch-icon"     href="{{ asset('img/game/pwa-180.png') }}">
    <link rel="icon" sizes="192x192" href="{{ asset('img/game/pwa-192.png') }}" type="image/png">
    <link rel="icon"                 href="{{ asset('img/game/pwa-192.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }

        body { background: #0f0e17; }

        @keyframes floatBlob {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-24px) scale(1.04); }
        }
        @keyframes floatBlobSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-16px) rotate(8deg); }
        }
        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes shimmerBar {
            0%   { background-position: -200% 0; }
            100% { background-position:  200% 0; }
        }

        .blob-1 { animation: floatBlob 9s ease-in-out infinite; }
        .blob-2 { animation: floatBlob 11s ease-in-out 2s infinite; }
        .blob-3 { animation: floatBlobSlow 13s ease-in-out 1s infinite; }

        .form-panel { animation: slideUpFade 0.55s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .brand-panel { animation: fadeIn 0.7s ease forwards; }

        .auth-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 12px 16px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .auth-input::placeholder { color: #6b7280; }
        .auth-input:focus {
            border-color: rgba(99,102,241,0.7);
            background: rgba(99,102,241,0.06);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }
        .auth-input.has-icon-right { padding-right: 44px; }

        .auth-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #9ca3af;
            margin-bottom: 6px;
        }
        .auth-error {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
            font-size: 12px;
            color: #f87171;
        }

        .gradient-text-auth {
            background: linear-gradient(135deg, #6366f1, #ec4899, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-divider {
            width: 1px; height: 36px;
            background: rgba(255,255,255,0.1);
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.5);
            cursor: pointer;
        }
    </style>

    @include('partials.trackers')
</head>
<body class="font-sans text-white antialiased" style="min-height:100vh;">

    <div class="flex min-h-screen">

        {{-- ── LEFT BRAND PANEL ─────────────────────────────────────── --}}
        <div class="brand-panel hidden lg:flex lg:w-[44%] xl:w-[42%] flex-col justify-between p-10 xl:p-14 relative overflow-hidden"
             style="background: linear-gradient(145deg, #1e1b4b 0%, #0f0e17 55%, #1a0d2e 100%);">

            {{-- Animated blobs --}}
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="blob-1 absolute -top-20 -left-20 w-80 h-80 rounded-full"
                     style="background: radial-gradient(circle, rgba(99,102,241,0.22) 0%, transparent 70%);"></div>
                <div class="blob-2 absolute -bottom-16 -right-16 w-96 h-96 rounded-full"
                     style="background: radial-gradient(circle, rgba(168,85,247,0.18) 0%, transparent 70%);"></div>
                <div class="blob-3 absolute top-1/2 -right-10 w-64 h-64 rounded-full"
                     style="background: radial-gradient(circle, rgba(236,72,153,0.12) 0%, transparent 70%);"></div>
                {{-- Grid overlay --}}
                <div class="absolute inset-0 opacity-10"
                     style="background-image: linear-gradient(rgba(99,102,241,0.2) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,0.2) 1px, transparent 1px); background-size: 48px 48px;"></div>
            </div>

            {{-- Top: Logo --}}
            <div class="relative z-10">
                <a href="/" class="inline-flex items-center gap-3 group">
                    <img src="{{ asset('moski-logo.png') }}" alt="Moski"
                         class="h-11 w-auto rounded-xl object-cover shadow-xl transition-transform group-hover:scale-105"
                         style="box-shadow: 0 0 24px rgba(99,102,241,0.35);">
                    <div>
                        <div class="text-lg font-black text-white leading-none">PesaQuest</div>
                        <div class="text-xs text-indigo-400 font-medium tracking-wide">by Moski</div>
                    </div>
                </a>
            </div>

            {{-- Middle: Copy + Features --}}
            <div class="relative z-10 my-auto py-10">
                <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30 rounded-full px-3 py-1 mb-6">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full" style="animation: pulse 2s infinite;"></span>
                    <span class="text-xs text-indigo-300 font-semibold tracking-wide">Season 1 — Now Live</span>
                </div>

                <h2 class="text-3xl xl:text-4xl font-black leading-snug text-white mb-4">
                    Your Money.<br>
                    <span class="gradient-text-auth">Your Moves.</span><br>
                    Your Future.
                </h2>
                <p class="text-gray-400 text-sm xl:text-base leading-relaxed mb-8 max-w-xs">
                    Learn real financial skills through story-driven decisions. Built for Kenya. Made to be addictive.
                </p>

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="feature-icon" style="background:rgba(99,102,241,0.18);border:1px solid rgba(99,102,241,0.3);">🎮</div>
                        <div>
                            <div class="text-white font-semibold text-sm">50+ Real Scenarios</div>
                            <div class="text-gray-500 text-xs">Budgeting, investing, debt & more</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="feature-icon" style="background:rgba(168,85,247,0.18);border:1px solid rgba(168,85,247,0.3);">🏆</div>
                        <div>
                            <div class="text-white font-semibold text-sm">Earn XP & Badges</div>
                            <div class="text-gray-500 text-xs">Progress at your own pace</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="feature-icon" style="background:rgba(16,185,129,0.18);border:1px solid rgba(16,185,129,0.3);">🌍</div>
                        <div>
                            <div class="text-white font-semibold text-sm">Kenya-Specific Content</div>
                            <div class="text-gray-500 text-xs">M-Pesa, NHIF, NSSF & local context</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="feature-icon" style="background:rgba(245,158,11,0.18);border:1px solid rgba(245,158,11,0.3);">📈</div>
                        <div>
                            <div class="text-white font-semibold text-sm">4 Age Groups</div>
                            <div class="text-gray-500 text-xs">Content tailored to your life stage</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom: Stats --}}
            <div class="relative z-10 flex items-center gap-6 pt-6 border-t" style="border-color:rgba(255,255,255,0.08);">
                <div class="text-center">
                    <div class="text-2xl font-black text-white">10K+</div>
                    <div class="text-[10px] text-indigo-400 uppercase tracking-widest font-semibold mt-0.5">Players</div>
                </div>
                <div class="stat-divider"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-white">50+</div>
                    <div class="text-[10px] text-purple-400 uppercase tracking-widest font-semibold mt-0.5">Scenarios</div>
                </div>
                <div class="stat-divider"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-white">Free</div>
                    <div class="text-[10px] text-emerald-400 uppercase tracking-widest font-semibold mt-0.5">To Start</div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT FORM PANEL ─────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col items-center justify-center min-h-screen py-10 px-5 sm:px-8 overflow-y-auto"
             style="background: #0f0e17;">

            {{-- Mobile logo --}}
            <div class="lg:hidden mb-8 flex items-center gap-2.5">
                <img src="{{ asset('moski-logo.png') }}" alt="Moski" class="h-10 w-auto rounded-xl object-cover">
                <div>
                    <div class="text-base font-black text-white leading-none">PesaQuest</div>
                    <div class="text-xs text-indigo-400 font-medium">by Moski</div>
                </div>
            </div>

            {{-- Form card --}}
            <div class="form-panel w-full max-w-md rounded-3xl p-7 sm:p-8"
                 style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); box-shadow: 0 32px 80px rgba(0,0,0,0.5);">
                {{ $slot }}
            </div>

            <p class="mt-5 text-xs text-gray-700">
                <a href="{{ route('landing') }}" class="hover:text-gray-400 transition-colors">← Back to homepage</a>
            </p>
        </div>

    </div>

</body>
</html>
