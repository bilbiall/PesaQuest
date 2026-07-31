<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Primary SEO --}}
    <title>PesaQuest by Moski – Learn Financial Literacy Through Play | Kenya</title>
    <meta name="description" content="PesaQuest is a virtual financial world for all ages. Live in Pesa City: earn a salary, pay bills, build credit, buy assets and survive economic crises — no real risk. Built for Kenya.">
    <meta name="keywords" content="financial literacy Kenya, money management game, PesaQuest, Moski, learn finance Kenya, personal finance Africa, SACCO Kenya, investing Kenya, budgeting adults Kenya, financial literacy all ages">
    <meta name="author" content="Moski">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    {{-- Open Graph (Facebook, WhatsApp, LinkedIn) --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="PesaQuest – Learn Financial Literacy Through Play">
    <meta property="og:description" content="Make real money decisions in a safe, gamified environment. Budgeting, saving, investing, careers — for ages 8 to 60+. Built for Kenya by Moski.">
    <meta property="og:image" content="{{ asset('moski-logo.png') }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:site_name" content="PesaQuest by Moski">
    <meta property="og:locale" content="en_KE">

    {{-- Twitter / X Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PesaQuest – Financial Literacy Game for Kenya">
    <meta name="twitter:description" content="Live a virtual financial life in Pesa City — jobs, bills, savings, assets, credit and crises. Free to start. All ages.">
    <meta name="twitter:image" content="{{ asset('moski-logo.png') }}">

    {{-- Fetch the hero image before anything else — it paints the first screen --}}
    <link rel="preload" as="image" href="{{ asset('img/pesaquestheader.webp') }}" fetchpriority="high">

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "name": "Moski",
                "url": "{{ url('/') }}",
                "logo": {
                    "@type": "ImageObject",
                    "url": "{{ asset('moski-logo.png') }}"
                },
                "description": "Moski is a Kenyan NGO using gamification to improve financial literacy across all age groups.",
                "areaServed": "KE",
                "sameAs": []
            },
            {
                "@type": "WebApplication",
                "name": "PesaQuest",
                "url": "{{ url('/') }}",
                "applicationCategory": "EducationalGame",
                "operatingSystem": "Web",
                "offers": {
                    "@type": "Offer",
                    "price": "0",
                    "priceCurrency": "KES",
                    "description": "Free to play with optional premium subscription"
                },
                "description": "PesaQuest is a financial life simulator where players live a virtual financial life in Pesa City — earning salaries, paying bills, saving, investing, borrowing and building credit in a safe, Kenya-specific world.",
                "audience": {
                    "@type": "Audience",
                    "audienceType": "All ages — children, teenagers, young adults, and adults"
                },
                "inLanguage": "en-KE",
                "screenshot": "{{ asset('moski-logo.png') }}"
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "Is PesaQuest free?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Yes. PesaQuest is free to start and every new account gets a full-featured free trial. A premium subscription unlocks the full pace of the simulation — unlimited assets, savings goals, investment deals and complete time catch-up."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What age groups does PesaQuest support?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "PesaQuest supports ages 8–12, 13–17, 18–25, and 26+. Each group experiences Pesa City tailored to their financial realities — from pocket money and first savings goals to salaries, loans and mortgages."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What financial topics does PesaQuest cover?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "PesaQuest covers budgeting, saving, debt management, investments, side hustles, career planning, taxes (PAYE, NHIF, NSSF), loans, and emergency funds — all in a Kenya-specific context."
                        }
                    }
                ]
            }
        ]
    }
    </script>

    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('moski-logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --brand-purple: #6366f1;
            --brand-violet: #8b5cf6;
            --brand-pink: #ec4899;
            --brand-gold: #f59e0b;
        }

        .gradient-text {
            background: linear-gradient(135deg, #6366f1, #ec4899, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-bg {
            background: radial-gradient(ellipse at top left, rgba(99,102,241,0.15) 0%, transparent 60%),
                        radial-gradient(ellipse at bottom right, rgba(236,72,153,0.12) 0%, transparent 60%),
                        radial-gradient(ellipse at center, rgba(245,158,11,0.05) 0%, transparent 80%),
                        #0f0e17;
        }

        .card-glow {
            box-shadow: 0 0 0 1px rgba(99,102,241,0.2), 0 25px 50px -12px rgba(0,0,0,0.6);
        }

        .card-glow:hover {
            box-shadow: 0 0 0 1px rgba(99,102,241,0.5), 0 30px 60px -12px rgba(99,102,241,0.3);
            transform: translateY(-4px);
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        .floating-delayed {
            animation: float 6s ease-in-out 2s infinite;
        }

        .floating-slow {
            animation: float 8s ease-in-out 1s infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-16px); }
        }

        .shimmer {
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.05) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .pulse-ring {
            animation: pulse-ring 2s ease-out infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99,102,241,0.5); }
            70% { transform: scale(1); box-shadow: 0 0 0 20px rgba(99,102,241,0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99,102,241,0); }
        }

        .stat-counter {
            font-variant-numeric: tabular-nums;
        }

        .nav-blur {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .phone-mock {
            background: linear-gradient(145deg, #1e1b4b, #0f0e17);
            border: 1px solid rgba(99,102,241,0.3);
        }

        /* Pesa City map inside the hero phone */
        .lp-map-pin {
            position: absolute; width: 24px; height: 24px; z-index: 2;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; transform: translate(-50%, -50%);
            background: rgba(7,6,15,0.88); border: 1px solid rgba(99,102,241,0.6);
            border-radius: 50%;
            animation: lpPinPulse 2.4s ease-out infinite;
        }
        @keyframes lpPinPulse {
            0%   { box-shadow: 0 0 0 0 rgba(99,102,241,0.55); }
            70%  { box-shadow: 0 0 0 10px rgba(99,102,241,0); }
            100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .lp-map-pin { animation: none; }
        }

        .choice-preview {
            background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.05));
            border: 1px solid rgba(99,102,241,0.2);
            transition: all 0.3s ease;
        }

        .choice-preview:hover {
            background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.15));
            border-color: rgba(99,102,241,0.5);
            transform: translateX(4px);
        }

        .section-line {
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.5), transparent);
        }

        .hero-image {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: transform 0.3s ease;
        }

        @media (max-width: 640px) {
            .hero-image {
                background-position: center;
            }
        }

        .image-responsive {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        @keyframes image-float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-10px) scale(1.02); }
        }

        .image-float {
            animation: image-float 5s ease-in-out infinite;
        }

        .kid-card {
            background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.05));
            border: 2px solid rgba(99,102,241,0.2);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .kid-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: rgba(99,102,241,0.5);
            box-shadow: 0 20px 40px rgba(99,102,241,0.2);
        }

        .image-container {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
        }

        .image-container img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .image-container:hover img {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .image-container {
                min-height: 250px;
            }
        }

        @media (min-width: 769px) {
            .image-container {
                min-height: 300px;
            }
        }

        @media (max-width: 640px) {
            section {
                padding-top: 60px !important;
                padding-bottom: 60px !important;
            }
        }

        [x-cloak] { display: none !important; }

        @keyframes marquee {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }
    </style>
</head>
<body class="hero-bg text-white font-sans antialiased overflow-x-hidden" x-data="landingPage()">

    {{-- NAVBAR --}}
    <nav class="fixed top-0 left-0 right-0 z-50 nav-blur border-b border-white/5 transition-all duration-300"
         :class="scrolled ? 'bg-black/60 py-3' : 'bg-transparent py-5'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <a href="/" class="group">
                <img src="{{ asset('moski-logo.png') }}" alt="Moski"
                     class="h-8 sm:h-11 w-auto rounded-xl object-cover group-hover:opacity-90 transition-opacity">
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="#about" class="text-sm text-gray-400 hover:text-white transition-colors">About</a>
                <a href="#game" class="text-sm text-gray-400 hover:text-white transition-colors">PesaQuest</a>
                <a href="#pesamali" class="text-sm text-gray-400 hover:text-white transition-colors">PesaMali</a>
                <a href="#how" class="text-sm text-gray-400 hover:text-white transition-colors">How It Works</a>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3">
                <a href="{{ route('pricing') }}" class="text-xs sm:text-sm text-gray-300 hover:text-white px-2 py-1.5 sm:px-4 sm:py-2 transition-colors">Pricing</a>
                <div class="w-px h-6 bg-white/10 hidden sm:block"></div>
                @auth
                    <a href="{{ route('game.play') }}" class="text-xs sm:text-sm font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white px-3 py-1.5 sm:px-5 sm:py-2 rounded-lg transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-105 whitespace-nowrap">
                        Play Now
                    </a>
                    <div class="w-px h-6 bg-white/10 hidden sm:block"></div>
                    <a href="{{ route('dashboard') }}" class="text-xs sm:text-sm font-semibold text-gray-300 hover:text-white hover:bg-white/10 px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg transition-all whitespace-nowrap">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-xs sm:text-sm text-gray-300 hover:text-white px-2 py-1.5 sm:px-4 sm:py-2 transition-colors whitespace-nowrap">Sign In</a>
                    <a href="{{ route('register') }}" class="text-xs sm:text-sm font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white px-3 py-1.5 sm:px-5 sm:py-2 rounded-lg transition-all shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:scale-105 whitespace-nowrap">
                        Start Playing
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <section class="relative min-h-screen flex items-center pt-24 pb-8 overflow-hidden">

        {{-- Hero Background Image --}}
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('img/pesaquestheader.webp') }}" alt="PesaQuest Hero"
                 width="1920" height="1080" fetchpriority="high" decoding="async"
                 class="w-full h-full object-cover scale-100 sm:scale-100 lg:scale-110"
                 style="min-height: 100%; object-position: center;">
            <div class="absolute inset-0 bg-gradient-to-r from-black/92 via-black/80 to-black/60"></div>
        </div>

        {{-- Background particles --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden z-10">
            <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-600/20 rounded-full blur-3xl floating"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl floating-delayed"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-pink-600/5 rounded-full blur-3xl floating-slow"></div>
            <span class="absolute text-2xl opacity-10 floating" style="top:15%;left:8%;animation-delay:0s;">💰</span>
            <span class="absolute text-xl opacity-10 floating-delayed" style="top:60%;left:5%;animation-delay:1s;">📈</span>
            <span class="absolute text-2xl opacity-10 floating-slow" style="top:30%;right:5%;animation-delay:2s;">🏆</span>
            <span class="absolute text-xl opacity-10 floating" style="top:75%;right:8%;animation-delay:3s;">💎</span>
        </div>

        {{-- Grid overlay --}}
        <div class="absolute inset-0 pointer-events-none opacity-20 z-5"
             style="background-image: linear-gradient(rgba(99,102,241,0.15) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,0.15) 1px, transparent 1px); background-size: 60px 60px;">
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full z-20">

            {{-- Marquee ticker --}}
            <div class="overflow-hidden border border-indigo-500/20 bg-indigo-500/5 rounded-full px-4 py-2 mb-10 hidden sm:flex items-center gap-3">
                <span class="text-indigo-400 text-xs font-bold uppercase tracking-widest whitespace-nowrap flex-shrink-0">PesaQuest</span>
                <div class="overflow-hidden flex-1">
                    <div style="animation: marquee 30s linear infinite; display:flex; gap: 3rem; width: max-content;">
                        <span class="text-xs text-gray-400 whitespace-nowrap">🏙️ Walk through Pesa City · 7 live districts</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">💰 Earn a salary · Pay bills · Build wealth</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🏠 Kiambu Estates · Buy plots · Earn rental income</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🚗 Jua Kali Car Yard · Finance a boda or salon car</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">📈 Invest in the NSE · SACCOs · T-Bills</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">⏰ While You Were Away — your city never sleeps</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">💳 Build your credit score · Avoid debt traps</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🎯 8 career paths · Technology · Healthcare · Business</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🌍 100% Kenyan context · M-Pesa · NHIF · PAYE</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">👑 Ages 8 to 60+ · All life stages covered</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🏙️ Walk through Pesa City · 7 live districts</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">💰 Earn a salary · Pay bills · Build wealth</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🏠 Kiambu Estates · Buy plots · Earn rental income</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🚗 Jua Kali Car Yard · Finance a boda or salon car</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">📈 Invest in the NSE · SACCOs · T-Bills</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">⏰ While You Were Away — your city never sleeps</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">💳 Build your credit score · Avoid debt traps</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🎯 8 career paths · Technology · Healthcare · Business</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">🌍 100% Kenyan context · M-Pesa · NHIF · PAYE</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">👑 Ages 8 to 60+ · All life stages covered</span>
                    </div>
                </div>
            </div>

            {{-- Hero layout --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 md:gap-8 items-center">

                {{-- Left: Copy --}}
                <div class="md:col-span-3">
                    <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30 rounded-full px-3 py-1.5 mb-5 sm:mb-6">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full pulse-ring"></span>
                        <span class="text-xs sm:text-sm text-indigo-300 font-medium">Now Live — Pesa City is Open · 8 Districts</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-6xl xl:text-6xl font-black leading-tight tracking-tight mb-4 sm:mb-6">
                        Live a Real Life.<br>
                        <span class="gradient-text">Learn Real Money.</span><br>
                        No Real Risk.
                    </h1>

                    <p class="text-sm sm:text-base lg:text-lg text-gray-300 leading-relaxed max-w-lg mb-6 sm:mb-8 hidden sm:block">
                        PesaQuest is a <span class="text-white font-semibold">virtual financial world</span> where you earn a salary, pay real bills, buy assets, build credit, and grow wealth — all in a safe, gamified environment built for Kenya.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-8 sm:mb-10">
                        <a href="{{ auth()->check() ? route('game.play') : route('register') }}"
                           class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-400 hover:via-purple-400 hover:to-pink-400 text-white font-bold text-sm sm:text-base px-6 sm:px-8 py-3 rounded-xl shadow-2xl shadow-indigo-500/40 hover:shadow-indigo-500/60 transition-all duration-300 hover:scale-105">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <span>Start Playing Free</span>
                        </a>
                    </div>

                    {{-- Social proof --}}
                    <div class="flex items-center gap-4 sm:gap-8 pt-6 sm:pt-8 border-t border-white/5">
                        <div class="stat-counter text-center">
                            <div class="text-xl sm:text-2xl font-black text-white">100+</div>
                            <div class="text-[9px] sm:text-xs text-indigo-400 uppercase tracking-wider mt-1 font-semibold">Players</div>
                        </div>
                        <div class="w-px h-8 sm:h-10 bg-white/10"></div>
                        <div class="stat-counter text-center">
                            <div class="text-xl sm:text-2xl font-black text-white">8</div>
                            <div class="text-[9px] sm:text-xs text-purple-400 uppercase tracking-wider mt-1 font-semibold">City Districts</div>
                        </div>
                        <div class="w-px h-8 sm:h-10 bg-white/10"></div>
                        <div class="stat-counter text-center">
                            <div class="text-xl sm:text-2xl font-black text-white">8</div>
                            <div class="text-[9px] sm:text-xs text-emerald-400 uppercase tracking-wider mt-1 font-semibold">Career Paths</div>
                        </div>
                    </div>
                </div>

                {{-- Right: Phone Mock --}}
                <div class="md:col-span-2 flex justify-center md:justify-end w-full">
                    <div class="relative px-4 py-3 sm:px-6 sm:py-4">
                        <div class="phone-mock rounded-[1.5rem] p-3 shadow-2xl card-glow floating"
                             style="width: clamp(200px, 22vw, 260px)">
                            {{-- Status bar --}}
                            <div class="flex justify-between items-center px-2.5 py-1 mb-2.5">
                                <span class="text-[9px] text-gray-400">9:41</span>
                                <div class="flex items-center gap-1">
                                    <div class="w-2 h-0.5 bg-indigo-400 rounded"></div>
                                    <div class="w-1.5 h-0.5 bg-indigo-400 rounded opacity-60"></div>
                                    <div class="w-1 h-0.5 bg-indigo-400 rounded opacity-30"></div>
                                    <svg class="w-2.5 h-2.5 ml-0.5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0119 12.55M5 12.55a10.94 10.94 0 015.17-2.39M10.71 5.05A16 16 0 0122.56 9M1.42 9a15.91 15.91 0 014.7-2.88M8.53 16.11a6 6 0 016.95 0M12 20h.01"/></svg>
                                </div>
                            </div>

                            {{-- HUD bar --}}
                            <div class="bg-black/40 rounded-lg p-2 mb-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <span class="text-base">💵</span>
                                    <div>
                                        <div class="text-[8px] text-gray-400">Balance</div>
                                        <div class="text-xs font-bold text-emerald-400">45,200</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm">⚡</span>
                                    <span class="text-[10px] font-bold text-indigo-400">LV 5</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-[8px] text-gray-400">XP 68%</div>
                                    <div class="w-12 bg-white/10 rounded-full h-0.5 mt-0.5">
                                        <div class="bg-gradient-to-r from-emerald-500 to-indigo-500 h-0.5 rounded-full" style="width: 68%"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- The REAL Pesa City map --}}
                            <div class="relative rounded-lg overflow-hidden mb-2.5" style="height: 210px; border: 1px solid rgba(99,102,241,0.3);">
                                <img src="{{ asset('img/game/worldmap.webp') }}"
                                     alt="Pesa City — the living world of PesaQuest"
                                     loading="lazy" decoding="async"
                                     class="absolute inset-0 w-full h-full"
                                     style="object-fit: cover; object-position: 45% 40%; transform: scale(1.3);">
                                {{-- District pins --}}
                                <div class="lp-map-pin" style="top: 24%; left: 30%;">🏦</div>
                                <div class="lp-map-pin" style="top: 44%; left: 66%; animation-delay: .6s;">💼</div>
                                <div class="lp-map-pin" style="top: 68%; left: 40%; animation-delay: 1.2s;">🎡</div>
                                {{-- Live moment toast --}}
                                <div style="position:absolute; left: 6px; right: 6px; bottom: 6px; z-index: 3;
                                            background: rgba(7,6,15,0.9); border: 1px solid rgba(16,185,129,0.4);
                                            border-radius: 8px; padding: 5px 8px; backdrop-filter: blur(6px);">
                                    <div class="text-[9px] font-bold text-emerald-300">🧾 Payday ready — report to work to collect</div>
                                    <div class="text-[8px] text-gray-400 mt-0.5">Pesa City · 7 districts · a living economy</div>
                                </div>
                            </div>

                            {{-- Net worth bar --}}
                            <div class="flex items-center justify-between px-1 text-[9px]">
                                <span class="text-gray-500">💵 KES <span class="text-emerald-400 font-bold">45,200</span></span>
                                <span class="text-gray-500">📊 NW <span class="text-purple-400 font-bold">67,500</span></span>
                            </div>
                        </div>

                        {{-- Floating badges --}}
                        <div class="absolute top-0 right-0 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg px-2 py-1 text-[10px] font-bold text-black shadow-lg floating-delayed whitespace-nowrap">
                            🏠 Estates Unlocked!
                        </div>
                        <div class="absolute bottom-2 left-0 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-lg px-2 py-1 text-[10px] font-bold text-black shadow-lg floating whitespace-nowrap">
                            +Ksh 18K Salary!
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ABOUT MOSKI --}}
    <section id="about" class="py-24 relative">
        <div class="h-px section-line mb-24"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 text-indigo-400 text-sm font-semibold uppercase tracking-widest mb-4">
                        <div class="w-8 h-px bg-indigo-400"></div>
                        About Moski
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-black leading-tight mb-6">
                        We make financial<br>
                        <span class="gradient-text">education feel like play.</span>
                    </h2>
                    <p class="text-gray-400 text-lg leading-relaxed mb-6">
                        Moski is a non-profit empowering young Kenyans and Africans with the financial skills schools don't teach —
                        through a living virtual world that mirrors real financial life.
                    </p>
                    <p class="text-gray-400 text-lg leading-relaxed mb-8">
                        We believe the best way to learn money is to live it. In PesaQuest you earn, spend, save and recover from mistakes — every consequence is a lesson.
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                            <div class="text-2xl mb-2">🎯</div>
                            <div class="font-semibold text-white mb-1">Decision-First</div>
                            <div class="text-sm text-gray-400">Every lesson starts with a choice, not a lecture</div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                            <div class="text-2xl mb-2">🌍</div>
                            <div class="font-semibold text-white mb-1">Africa-Focused</div>
                            <div class="text-sm text-gray-400">Built on real Kenyan financial life — M-Pesa, chamas, boda gigs</div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                            <div class="text-2xl mb-2">📈</div>
                            <div class="font-semibold text-white mb-1">Adaptive</div>
                            <div class="text-sm text-gray-400">Content grows with your age group</div>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4">
                            <div class="text-2xl mb-2">🔓</div>
                            <div class="font-semibold text-white mb-1">Open Access</div>
                            <div class="text-sm text-gray-400">Free entry for all, premium for more</div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="relative space-y-6">
                        <div class="image-container shadow-2xl card-glow">
                            <img src="{{ asset('img/kids/financial-literacy-kids.jpg') }}" alt="Financial Literacy for Kids" loading="lazy" decoding="async">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-indigo-500/20 to-purple-500/10 border border-indigo-500/20 rounded-2xl p-4 card-glow transition-all duration-300 hover:scale-105">
                                <div class="text-3xl mb-2">🧠</div>
                                <div class="font-bold text-sm text-white">Critical Thinking</div>
                                <div class="text-xs text-gray-400 mt-1">Real consequences</div>
                            </div>
                            <div class="bg-gradient-to-br from-pink-500/20 to-orange-500/10 border border-pink-500/20 rounded-2xl p-4 card-glow transition-all duration-300 hover:scale-105">
                                <div class="text-3xl mb-2">💰</div>
                                <div class="font-bold text-sm text-white">Real Money Math</div>
                                <div class="text-xs text-gray-400 mt-1">Real numbers, real skills</div>
                            </div>
                            <div class="bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/20 rounded-2xl p-4 card-glow transition-all duration-300 hover:scale-105">
                                <div class="text-3xl mb-2">🏆</div>
                                <div class="font-bold text-sm text-white">Achievements</div>
                                <div class="text-xs text-gray-400 mt-1">Badges that matter</div>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-500/20 to-amber-500/10 border border-yellow-500/20 rounded-2xl p-4 card-glow transition-all duration-300 hover:scale-105">
                                <div class="text-3xl mb-2">🔥</div>
                                <div class="font-bold text-sm text-white">Daily Streaks</div>
                                <div class="text-xs text-gray-400 mt-1">Build good habits</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ALL AGES IN ACTION --}}
    <section class="py-24 relative">
        <div class="h-px section-line mb-24"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 text-pink-400 text-sm font-semibold uppercase tracking-widest mb-4">
                    <div class="w-8 h-px bg-pink-400"></div>
                    For Every Stage of Life
                    <div class="w-8 h-px bg-pink-400"></div>
                </div>
                <h2 class="text-4xl sm:text-5xl font-black leading-tight">
                    Real Money Skills <span class="gradient-text">At Every Age</span>
                </h2>
                <p class="text-lg text-gray-400 mt-4 max-w-2xl mx-auto">
                    From pocket money to pensions — PesaQuest meets you where you are.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="group relative image-container shadow-2xl card-glow transform transition-all duration-300 hover:scale-105 cursor-pointer">
                    <img src="{{ asset('img/kids/money-lessons-parents.jpg') }}" alt="Money Lessons for All Ages" loading="lazy" decoding="async" onerror="this.style.display='none'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end p-6">
                        <div>
                            <h3 class="text-2xl font-black text-white mb-2">🧒 Ages 8–17</h3>
                            <p class="text-gray-300 text-sm">Pocket money · Saving goals · First side hustles</p>
                        </div>
                    </div>
                </div>

                <div class="group relative image-container shadow-2xl card-glow transform transition-all duration-300 hover:scale-105 cursor-pointer">
                    <img src="{{ asset('img/kids/financial-responsibility-kids.jpg') }}" alt="Young Adults Finance" loading="lazy" decoding="async" onerror="this.style.display='none'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end p-6">
                        <div>
                            <h3 class="text-2xl font-black text-white mb-2">🎓 Ages 18–25</h3>
                            <p class="text-gray-300 text-sm">Salary negotiation · NHIF · First investments</p>
                        </div>
                    </div>
                </div>

                <div class="group relative image-container shadow-2xl card-glow transform transition-all duration-300 hover:scale-105 cursor-pointer">
                    <img src="{{ asset('img/kids/family-fun.jpg') }}" alt="Adult Financial Literacy" loading="lazy" decoding="async" onerror="this.style.display='none'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex items-end p-6">
                        <div>
                            <h3 class="text-2xl font-black text-white mb-2">👑 Ages 26+</h3>
                            <p class="text-gray-300 text-sm">Mortgages · SACCO loans · Retirement planning</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-16">
                <p class="text-lg text-gray-400 mb-6">
                    Join players of all ages discovering that <span class="text-white font-bold">financial freedom is a learnable skill</span>
                </p>
                <a href="{{ auth()->check() ? route('game.play') : route('register') }}"
                   class="inline-flex items-center gap-3 bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 hover:from-pink-400 hover:via-purple-400 hover:to-indigo-400 text-white font-bold text-lg px-8 py-4 rounded-2xl shadow-2xl shadow-pink-500/40 transition-all duration-300 hover:scale-105 hover:shadow-pink-500/60">
                    <span>🎮 Start Playing Free</span>
                </a>
            </div>
        </div>
    </section>

    {{-- PESAQUEST GAME --}}
    <section id="game" class="py-24 relative">
        <div class="h-px section-line mb-24"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 text-purple-400 text-sm font-semibold uppercase tracking-widest mb-4">
                    <div class="w-8 h-px bg-purple-400"></div>
                    The Virtual World
                    <div class="w-8 h-px bg-purple-400"></div>
                </div>
                <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight mb-4">
                    Your Virtual Life.<br><span class="gradient-text">Real Money Skills.</span>
                </h2>
                <p class="text-lg text-gray-400 max-w-3xl mx-auto leading-relaxed">
                    PesaQuest isn't just a quiz or a story game — it's a <strong class="text-white">full virtual financial life</strong>.
                    You pick a career, earn a salary, pay bills, buy assets, build credit, and watch your net worth grow.
                    All the reality of managing money — none of the real-world risk.
                </p>
            </div>

            {{-- Virtual World Feature Grid --}}
            <div class="grid md:grid-cols-3 gap-6 mb-16">
                <div class="group bg-gradient-to-br from-emerald-500/10 to-transparent border border-emerald-500/20 hover:border-emerald-500/50 rounded-3xl p-8 card-glow transition-all duration-300">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-300">💼</div>
                    <h3 class="text-xl font-bold mb-3">Real Career & Salary</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Take a career quiz, pick your job — Software Developer, Doctor, Teacher, Farmer — and receive a monthly salary with real deductions (PAYE, NHIF, NSSF). Just like real life.
                    </p>
                </div>
                <div class="group bg-gradient-to-br from-red-500/10 to-transparent border border-red-500/20 hover:border-red-500/50 rounded-3xl p-8 card-glow transition-all duration-300">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-300">📋</div>
                    <h3 class="text-xl font-bold mb-3">Bills & Expenses</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Pay rent, electricity, internet, and phone bills every month. Miss them and your credit score drops. Learn to budget before the real consequences kick in.
                    </p>
                </div>
                <div class="group bg-gradient-to-br from-amber-500/10 to-transparent border border-amber-500/20 hover:border-amber-500/50 rounded-3xl p-8 card-glow transition-all duration-300">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-300">🏠</div>
                    <h3 class="text-xl font-bold mb-3">Assets & Investments</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Buy a Bajaj motorcycle or a Porsche. Own a plot in Embakasi or a penthouse in Karen. Invest in Money Market Funds or NSE stocks. Watch your net worth grow.
                    </p>
                </div>
                <div class="group bg-gradient-to-br from-indigo-500/10 to-transparent border border-indigo-500/20 hover:border-indigo-500/50 rounded-3xl p-8 card-glow transition-all duration-300">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-300">💼</div>
                    <h3 class="text-xl font-bold mb-3">Careers, Gigs & Payday</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Study real courses, get hired full-time, juggle part-time jobs or hustle freelance gigs — then report to work every payday to collect your salary. Skip it and the wages are gone.
                    </p>
                </div>
                <div class="group bg-gradient-to-br from-cyan-500/10 to-transparent border border-cyan-500/20 hover:border-cyan-500/50 rounded-3xl p-8 card-glow transition-all duration-300">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-300">📊</div>
                    <h3 class="text-xl font-bold mb-3">Credit Score & Net Worth</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Track your credit score (300–850) and net worth in real time. Make smart decisions to climb from Eastleigh to Karen — the virtual Nairobi lifestyle ladder.
                    </p>
                </div>
                <div class="group bg-gradient-to-br from-purple-500/10 to-transparent border border-purple-500/20 hover:border-purple-500/50 rounded-3xl p-8 card-glow transition-all duration-300">
                    <div class="text-5xl mb-6 group-hover:scale-110 transition-transform duration-300">🤝</div>
                    <h3 class="text-xl font-bold mb-3">Chama & Social Finance</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Form or join a Chama with friends. Pool resources, propose investments, vote on decisions. Experience cooperative finance the Kenyan way.
                    </p>
                </div>
            </div>

            <div class="bg-white/3 border border-white/8 rounded-3xl p-8">
                <h3 class="text-center text-2xl font-bold mb-8">Your Virtual World — Tailored by Age</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/10 border border-blue-500/30 rounded-2xl p-5 text-center card-glow transition-all duration-300">
                        <div class="text-3xl mb-3">🧒</div>
                        <div class="font-bold text-sm mb-1">Ages 8-12</div>
                        <div class="text-xs text-gray-500 font-medium mb-2">Preteens</div>
                        <div class="text-xs text-gray-400">Pocket money, saving goals, first decisions</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-500/20 to-violet-500/10 border border-purple-500/30 rounded-2xl p-5 text-center card-glow transition-all duration-300">
                        <div class="text-3xl mb-3">🎒</div>
                        <div class="font-bold text-sm mb-1">Ages 13-17</div>
                        <div class="text-xs text-gray-500 font-medium mb-2">Teens</div>
                        <div class="text-xs text-gray-400">Side hustles, school fees, peer pressure</div>
                    </div>
                    <div class="bg-gradient-to-br from-pink-500/20 to-rose-500/10 border border-pink-500/30 rounded-2xl p-5 text-center card-glow transition-all duration-300">
                        <div class="text-3xl mb-3">🎓</div>
                        <div class="font-bold text-sm mb-1">Ages 18-25</div>
                        <div class="text-xs text-gray-500 font-medium mb-2">Young Adults</div>
                        <div class="text-xs text-gray-400">Salary, rent, loans, investing, career</div>
                    </div>
                    <div class="bg-gradient-to-br from-amber-500/20 to-yellow-500/10 border border-amber-500/30 rounded-2xl p-5 text-center card-glow transition-all duration-300">
                        <div class="text-3xl mb-3">💼</div>
                        <div class="font-bold text-sm mb-1">Ages 26+</div>
                        <div class="text-xs text-gray-500 font-medium mb-2">Adults</div>
                        <div class="text-xs text-gray-400">Property, business, wealth building, retirement</div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="{{ auth()->check() ? route('game.play') : route('register') }}"
                   class="inline-flex items-center gap-3 bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 hover:from-indigo-400 hover:via-purple-500 hover:to-pink-500 text-white font-bold text-xl px-10 py-5 rounded-2xl shadow-2xl shadow-purple-500/40 transition-all duration-300 hover:scale-105 hover:shadow-purple-500/60">
                    <span>Enter the Virtual World — Free</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="how" class="py-24 relative">
        <div class="h-px section-line mb-24"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 text-emerald-400 text-sm font-semibold uppercase tracking-widest mb-4">
                    <div class="w-8 h-px bg-emerald-400"></div>
                    How It Works
                    <div class="w-8 h-px bg-emerald-400"></div>
                </div>
                <h2 class="text-4xl sm:text-5xl font-black">
                    Start Your Virtual Life in <span class="gradient-text">4 Steps.</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <div class="relative">
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-px bg-gradient-to-r from-indigo-500/50 to-transparent"></div>
                    <div class="relative bg-white/5 border border-white/10 rounded-3xl p-6 text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg shadow-indigo-500/30">🎯</div>
                        <div class="absolute -top-3 -right-3 w-7 h-7 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-xs font-black">1</div>
                        <h3 class="font-bold text-lg mb-2">Take the Career Quiz</h3>
                        <p class="text-sm text-gray-400">Answer 5 fun questions. We match you to a career field — tech, healthcare, finance, and more.</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-px bg-gradient-to-r from-indigo-500/50 to-transparent"></div>
                    <div class="relative bg-white/5 border border-white/10 rounded-3xl p-6 text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg shadow-indigo-500/30">💼</div>
                        <div class="absolute -top-3 -right-3 w-7 h-7 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-xs font-black">2</div>
                        <h3 class="font-bold text-lg mb-2">Pick Your Starting Career</h3>
                        <p class="text-sm text-gray-400">Choose from careers matched to your personality. Receive your starting salary and bonus.</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="hidden md:block absolute top-8 left-1/2 w-full h-px bg-gradient-to-r from-indigo-500/50 to-transparent"></div>
                    <div class="relative bg-white/5 border border-white/10 rounded-3xl p-6 text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg shadow-indigo-500/30">🌍</div>
                        <div class="absolute -top-3 -right-3 w-7 h-7 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-xs font-black">3</div>
                        <h3 class="font-bold text-lg mb-2">Live Your Virtual Life</h3>
                        <p class="text-sm text-gray-400">Earn salary, pay bills, buy assets, face life events. Time passes even when you're offline.</p>
                    </div>
                </div>
                <div class="relative">
                    <div class="relative bg-white/5 border border-white/10 rounded-3xl p-6 text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg shadow-indigo-500/30">📈</div>
                        <div class="absolute -top-3 -right-3 w-7 h-7 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-xs font-black">4</div>
                        <h3 class="font-bold text-lg mb-2">Grow Your Wealth</h3>
                        <p class="text-sm text-gray-400">Make smarter decisions, climb the career ladder, build net worth — and learn real financial skills.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PESAMALI --}}
    <section id="pesamali" class="py-24 relative overflow-hidden">
        <div class="h-px section-line mb-24"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative rounded-3xl overflow-hidden">
                <div class="absolute inset-0 z-0">
                    <img src="{{ asset('img/kids/pesamali-learning.jpg') }}" alt="PesaMali Learning"
                         loading="lazy" decoding="async"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/70 to-black/60"></div>
                </div>

                <div class="relative z-10 grid lg:grid-cols-2 gap-10 items-center p-8 sm:p-12 md:p-16">
                    <div>
                        <div class="inline-flex items-center gap-2 text-emerald-400 text-sm font-semibold uppercase tracking-widest mb-4">
                            <div class="w-8 h-px bg-emerald-400"></div>
                            PesaMali <span class="text-yellow-400">~ From Moski MoneySpace</span>
                        </div>
                        <h2 class="text-4xl font-black mb-4 leading-tight">
                            Master Your Money<br>
                            <span class="text-emerald-400">Through Play</span>
                        </h2>
                        <p class="text-gray-300 text-lg leading-relaxed mb-4 font-semibold">
                            A fun, competitive board game that teaches you real financial skills — saving, spending, investing — through exciting real-life money challenges.
                        </p>
                        <p class="text-gray-400 text-base leading-relaxed mb-8">
                            Challenge friends. Build wealth. Learn the game of money. PesaMali brings financial learning to life through interactive, engaging gameplay designed for the African context.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="https://pesamali.moski.money/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-black font-bold px-6 py-3 rounded-xl transition-all hover:scale-105">
                                <span>Explore PesaMali</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <a href="https://pesamali.moski.money/#how-to-play" class="inline-flex items-center gap-2 text-gray-300 hover:text-white border border-white/10 hover:border-emerald-500/50 px-6 py-3 rounded-xl transition-all">
                                Learn More
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 hidden lg:grid">
                        <div class="bg-white/10 backdrop-blur-sm border border-emerald-500/20 rounded-2xl p-4 text-center hover:border-emerald-500/50 hover:bg-white/20 transition-all">
                            <div class="text-3xl mb-2">🎮</div>
                            <div class="text-sm font-medium text-white">Challenge Friends</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm border border-emerald-500/20 rounded-2xl p-4 text-center hover:border-emerald-500/50 hover:bg-white/20 transition-all">
                            <div class="text-3xl mb-2">💰</div>
                            <div class="text-sm font-medium text-white">Build Wealth</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm border border-emerald-500/20 rounded-2xl p-4 text-center hover:border-emerald-500/50 hover:bg-white/20 transition-all">
                            <div class="text-3xl mb-2">🌍</div>
                            <div class="text-sm font-medium text-white">Real Money Skills</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm border border-emerald-500/20 rounded-2xl p-4 text-center hover:border-emerald-500/50 hover:bg-white/20 transition-all">
                            <div class="text-3xl mb-2">🤝</div>
                            <div class="text-sm font-medium text-white">Learn Together</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA FINAL --}}
    <section class="py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-6xl mb-6">🚀</div>
            <h2 class="text-5xl sm:text-6xl font-black mb-6">
                Ready to Master<br>
                <span class="gradient-text">Your Money?</span>
            </h2>
            <p class="text-xl text-gray-400 mb-10 max-w-xl mx-auto">
                Join thousands of young Kenyans leveling up their financial IQ through play.
                Free to start — your full trial begins the moment you sign up.
            </p>
            <a href="{{ auth()->check() ? route('game.play') : route('register') }}"
               class="inline-flex items-center gap-3 bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 hover:from-indigo-400 hover:via-purple-500 hover:to-pink-500 text-white font-black text-2xl px-12 py-6 rounded-2xl shadow-2xl shadow-purple-500/40 transition-all duration-300 hover:scale-105">
                Start PesaQuest Free
            </a>
            <p class="text-gray-500 text-sm mt-4">No credit card. No gimmicks. Just growth.</p>
        </div>
    </section>

    {{-- CONTACT US --}}
    @if($contact['email'] || $contact['whatsapp'] || $contact['phone'])
    <section class="py-20 border-t border-white/5">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-5xl mb-4">💬</div>
            <h2 class="text-3xl sm:text-4xl font-black mb-3">Get in Touch</h2>
            <p class="text-gray-400 mb-10 max-w-lg mx-auto">Questions about PesaQuest, a school partnership, or something not working right? Reach us however's easiest for you.</p>

            <div class="flex flex-col sm:flex-row items-stretch justify-center gap-4 max-w-2xl mx-auto">
                @if($contact['whatsapp'])
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $contact['whatsapp']) }}" target="_blank" rel="noopener"
                   class="group flex-1 flex items-center justify-center gap-3 rounded-2xl px-6 py-5 font-bold text-white transition-all duration-300 hover:scale-105 shadow-lg"
                   style="background:#25D366;box-shadow:0 8px 24px rgba(37,211,102,0.35);">
                    <svg viewBox="0 0 32 32" width="26" height="26" fill="currentColor" class="flex-shrink-0"><path d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.68 4.611 1.85 6.487L4 29l7.71-1.822A11.94 11.94 0 0016 27c6.627 0 12-5.373 12-12S22.628 3 16.001 3zm.001 21.7a9.65 9.65 0 01-4.926-1.348l-.353-.21-3.62.856.86-3.594-.23-.368A9.65 9.65 0 015.3 15c0-5.9 4.8-10.7 10.701-10.7S26.7 9.1 26.7 15 21.902 24.7 16.002 24.7zm5.34-7.442c-.293-.146-1.734-.856-2.003-.954-.269-.098-.464-.146-.66.147-.195.293-.757.953-.928 1.148-.171.196-.342.22-.635.073-.293-.146-1.236-.456-2.354-1.454-.87-.776-1.458-1.735-1.629-2.028-.171-.293-.018-.451.128-.597.132-.131.293-.342.44-.513.146-.171.195-.293.293-.489.098-.196.049-.367-.024-.513-.073-.146-.66-1.59-.904-2.178-.238-.572-.48-.494-.66-.503l-.562-.01c-.196 0-.513.073-.782.367-.269.293-1.026 1.003-1.026 2.446 0 1.442 1.05 2.836 1.196 3.032.146.195 2.066 3.156 5.008 4.427.7.302 1.246.483 1.672.618.702.223 1.342.192 1.848.116.564-.084 1.734-.709 1.978-1.393.244-.684.244-1.27.171-1.393-.073-.122-.269-.195-.562-.342z"/></svg>
                    <span>WhatsApp</span>
                </a>
                @endif
                @if($contact['email'])
                <a href="mailto:{{ $contact['email'] }}"
                   class="group flex-1 flex items-center justify-center gap-3 rounded-2xl px-6 py-5 font-bold text-white transition-all duration-300 hover:scale-105 bg-white/8 border border-white/15 hover:border-indigo-400/50 hover:bg-white/12">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-indigo-300"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Email Us</span>
                </a>
                @endif
                @if($contact['phone'])
                <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}"
                   class="group flex-1 flex items-center justify-center gap-3 rounded-2xl px-6 py-5 font-bold text-white transition-all duration-300 hover:scale-105 bg-white/8 border border-white/15 hover:border-pink-400/50 hover:bg-white/12">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-pink-300"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>Call Us</span>
                </a>
                @endif
            </div>
        </div>
    </section>
    @endif

    {{-- FOOTER --}}
    <footer class="border-t border-white/5 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-10 mb-12">
                <div class="col-span-2 md:col-span-1">
                    <div class="mb-4">
                        <img src="{{ asset('moski-logo.png') }}" alt="Moski"
                             class="h-10 w-auto rounded-xl object-cover opacity-80">
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed mb-4">
                        Empowering Africa's next generation with financial literacy through play.
                    </p>
                    <p class="text-gray-600 text-xs">© {{ date('Y') }} Moski. All rights reserved.</p>
                </div>

                <div>
                    <h4 class="font-semibold text-sm uppercase tracking-wider text-gray-400 mb-4">Platform</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#game" class="hover:text-white transition-colors">PesaQuest</a></li>
                        <li><a href="#pesamali" class="hover:text-white transition-colors">PesaMali</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Create Account</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-sm uppercase tracking-wider text-gray-400 mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="hover:text-white transition-colors">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-sm uppercase tracking-wider text-gray-400 mb-4">Contact</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li>📧 hello@moski.org</li>
                        <li>📍 Nairobi, Kenya</li>
                        <li class="pt-2 flex gap-3">
                            <a href="#" class="hover:text-white transition-colors">Twitter</a>
                            <a href="#" class="hover:text-white transition-colors">Instagram</a>
                            <a href="#" class="hover:text-white transition-colors">LinkedIn</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/5 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-gray-600">Built with ❤️ for Africa's financial future.</p>
                <div class="flex items-center gap-2 text-xs text-gray-600">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    All systems operational
                </div>
            </div>
        </div>
    </footer>

    <script>
        function landingPage() {
            return {
                scrolled: false,
                init() {
                    window.addEventListener('scroll', () => {
                        this.scrolled = window.scrollY > 20;
                    });
                }
            }
        }
    </script>
</body>
</html>
