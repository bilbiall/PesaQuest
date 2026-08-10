<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $pageTitle    = trim($__env->yieldContent('title'));
            $fullTitle    = $pageTitle ? "$pageTitle – PesaQuest" : 'PesaQuest by Moski – Learn Financial Literacy Through Play';
            $pageDesc     = trim($__env->yieldContent('meta_description'))
                            ?: 'PesaQuest is Kenya\'s #1 financial literacy game. Explore Pesa City, earn a virtual salary, pay bills, invest, and build real money skills — all without any financial risk. Learn budgeting, saving, investing and credit management through play.';
            $canonicalUrl = url()->current();
            $ogImage      = asset('img/game/pwa-og.jpg');
            $ogImageW     = 1200;
            $ogImageH     = 1200;
        @endphp

        <title>{{ $fullTitle }}</title>
        <meta name="description" content="{{ $pageDesc }}">
        <meta name="keywords"    content="financial literacy Kenya, money game Kenya, PesaQuest, Moski, learn to save Kenya, budgeting game, Pesa City, investment game Africa, fintech education, personal finance game, KES, Kenyan youth finance">
        <meta name="author"      content="Moski – It's Possible">
        <link rel="canonical"    href="{{ $canonicalUrl }}">

        {{-- Open Graph (WhatsApp, Facebook, LinkedIn use these) --}}
        <meta property="og:type"          content="website">
        <meta property="og:url"           content="{{ $canonicalUrl }}">
        <meta property="og:title"         content="{{ $fullTitle }}">
        <meta property="og:description"   content="{{ $pageDesc }}">
        <meta property="og:image"         content="{{ $ogImage }}">
        <meta property="og:image:width"   content="{{ $ogImageW }}">
        <meta property="og:image:height"  content="{{ $ogImageH }}">
        <meta property="og:image:type"    content="image/jpeg">
        <meta property="og:image:alt"     content="PesaQuest — Kenya's Financial Literacy Game by Moski">
        <meta property="og:site_name"     content="PesaQuest by Moski">
        <meta property="og:locale"        content="en_KE">

        {{-- Twitter / X card --}}
        <meta name="twitter:card"         content="summary_large_image">
        <meta name="twitter:site"         content="@MoskiApp">
        <meta name="twitter:title"        content="{{ $fullTitle }}">
        <meta name="twitter:description"  content="{{ $pageDesc }}">
        <meta name="twitter:image"        content="{{ $ogImage }}">
        <meta name="twitter:image:alt"    content="PesaQuest — Kenya's Financial Literacy Game">

        {{-- Authenticated pages: don't index; PWA pages still share nicely via OG --}}
        <meta name="robots" content="noindex, nofollow">

        {{-- PWA --}}
        <link rel="manifest"              href="/manifest.json">
        <meta name="theme-color"          content="#6366f1">
        <meta name="mobile-web-app-capable"               content="yes">
        <meta name="apple-mobile-web-app-capable"         content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title"           content="PesaQuest">
        <link rel="apple-touch-icon"      href="{{ asset('img/game/pwa-180.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/game/pwa-180.png') }}">
        <link rel="icon" sizes="192x192" href="{{ asset('img/game/pwa-192.png') }}" type="image/png">
        <link rel="icon" sizes="512x512" href="{{ asset('img/game/pwa-512.png') }}" type="image/png">
        <link rel="icon"                  href="{{ asset('img/game/pwa-192.png') }}" type="image/png">

        {{-- Splash screens (iOS) --}}
        <meta name="msapplication-TileImage" content="{{ asset('img/game/pwa-512.png') }}">
        <meta name="msapplication-TileColor" content="#07060f">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('head')

        @include('partials.trackers')
    </head>

    {{-- ── Page loader — see-through overlay with cycling messages ── --}}
    <style>
    #pq-loader {
        position: fixed; inset: 0; z-index: 99999;
        background: rgba(7,6,15,0.78);
        backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        transition: opacity .65s ease;
    }
    #pq-loader.pq-fade { opacity: 0; pointer-events: none; }

    /* Spinning arc ring */
    .pq-ring {
        position: relative; width: 100px; height: 100px;
    }
    .pq-ring svg {
        position: absolute; inset: 0; width: 100%; height: 100%;
        animation: pqSpin 1.6s linear infinite;
    }
    @keyframes pqSpin { to { transform: rotate(360deg); } }

    .pq-ring-logo {
        position: absolute; inset: 14px; border-radius: 50%;
        object-fit: cover;
        animation: pqLogoBreathe 2.4s ease-in-out infinite;
    }
    @keyframes pqLogoBreathe {
        0%,100% { transform: scale(1);    opacity: .88; }
        50%      { transform: scale(1.06); opacity: 1;   }
    }

    /* Brand name */
    .pq-loader-brand {
        margin-top: 20px;
        font-family: 'Figtree', sans-serif;
        font-size: 1.45rem; font-weight: 900; letter-spacing: -.02em;
        background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,.6) 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Bouncing dots */
    .pq-dots { display: flex; gap: 5px; margin-top: 12px; }
    .pq-dot {
        width: 7px; height: 7px; border-radius: 50%;
        animation: pqDot 1.5s ease-in-out infinite;
    }
    .pq-dot:nth-child(1){ animation-delay: 0s;    background: #15C77E; }
    .pq-dot:nth-child(2){ animation-delay: .18s;  background: #6366f1; }
    .pq-dot:nth-child(3){ animation-delay: .36s;  background: #f59e0b; }
    @keyframes pqDot {
        0%,80%,100% { transform: scale(.7); opacity: .35; }
        40%          { transform: scale(1.3); opacity: 1;   }
    }

    /* Cycling message */
    .pq-msg {
        margin-top: 30px;
        font-family: 'Figtree', sans-serif;
        font-size: .82rem; font-weight: 500;
        color: rgba(255,255,255,.55);
        letter-spacing: .04em; text-align: center;
        min-height: 1.5em;
        transition: opacity .3s ease, transform .3s ease;
    }
    .pq-msg.pq-msg-out { opacity: 0; transform: translateY(-10px); }
    </style>

    <div id="pq-loader" aria-live="polite" aria-label="Loading PesaQuest">
        <div class="pq-ring">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="44" stroke="rgba(255,255,255,0.07)" stroke-width="4.5"/>
                <circle cx="50" cy="50" r="44"
                    stroke="url(#pqArcGrad)" stroke-width="4.5"
                    stroke-linecap="round"
                    stroke-dasharray="88 189"/>
                <defs>
                    <linearGradient id="pqArcGrad" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                        <stop offset="0%"   stop-color="#15C77E"/>
                        <stop offset="50%"  stop-color="#6366f1"/>
                        <stop offset="100%" stop-color="#f59e0b"/>
                    </linearGradient>
                </defs>
            </svg>
            <img src="{{ asset('img/game/screenloader.png') }}" class="pq-ring-logo" alt="">
        </div>
        <div class="pq-loader-brand">PesaQuest</div>
        <div class="pq-dots">
            <div class="pq-dot"></div>
            <div class="pq-dot"></div>
            <div class="pq-dot"></div>
        </div>
        <p class="pq-msg" id="pq-msg">💰 Growing Your Wealth...</p>
    </div>

    <script>
    (function(){
        var msgs = [
            '💰 Growing Your Wealth...',
            '🏦 Opening the Markets...',
            '📈 Investing in Your Future...',
            '🏡 Building Your Dream Life...',
            '💼 Starting Your Career...',
            '💳 Managing Your Budget...',
            '🚗 Shopping Smart...',
            '🏢 Expanding Your Business...',
            '🌍 Preparing Your Financial World...',
            '💎 Creating Generational Wealth...',
            '📊 Calculating Your Net Worth...',
            '🎯 Unlocking New Opportunities...'
        ];
        var el = document.getElementById('pq-msg');
        var loader = document.getElementById('pq-loader');
        var idx = 0, timer, removed = false;

        function cycleMsg() {
            el.classList.add('pq-msg-out');
            setTimeout(function(){
                idx = (idx + 1) % msgs.length;
                el.textContent = msgs[idx];
                el.classList.remove('pq-msg-out');
            }, 310);
        }
        timer = setInterval(cycleMsg, 1800);

        function dismiss() {
            if (removed) return;
            removed = true;
            clearInterval(timer);
            loader.classList.add('pq-fade');
            setTimeout(function(){ if (loader.parentNode) loader.parentNode.removeChild(loader); }, 700);
        }
        if (document.readyState === 'complete') { dismiss(); }
        else { window.addEventListener('load', dismiss); }
        setTimeout(dismiss, 5500); // hard failsafe
    })();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function(){
            navigator.serviceWorker.register('/sw.js', {scope:'/'}).catch(function(){});
        });
    }
    </script>

    {{-- ── PWA Install Pill ── --}}
    <style>
    #pwa-pill {
        position:fixed; bottom:80px; left:50%; transform:translateX(-50%) translateY(120px);
        z-index:9998; display:flex; align-items:center; gap:10px;
        background:linear-gradient(135deg,rgba(18,15,42,.97),rgba(14,12,34,.97));
        border:1px solid rgba(99,102,241,.35); border-radius:9999px;
        padding:10px 18px 10px 12px; box-shadow:0 8px 32px rgba(0,0,0,.6),0 0 0 1px rgba(99,102,241,.1);
        backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px);
        cursor:pointer; white-space:nowrap; max-width:calc(100vw - 32px);
        transition:transform .45s cubic-bezier(.34,1.56,.64,1), opacity .45s ease, box-shadow .2s;
        opacity:0; pointer-events:none;
    }
    #pwa-pill.pwa-pill-show {
        transform:translateX(-50%) translateY(0);
        opacity:1; pointer-events:auto;
    }
    #pwa-pill:hover { box-shadow:0 12px 40px rgba(0,0,0,.7),0 0 0 1px rgba(99,102,241,.3); }
    #pwa-pill:hover .pwa-pill-icon { transform:scale(1.12); }
    .pwa-pill-icon { width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;transition:transform .2s; }
    .pwa-pill-text { display:flex;flex-direction:column;line-height:1.2; }
    .pwa-pill-label { font-size:.82rem;font-weight:800;color:#fff;font-family:'Figtree',sans-serif; }
    .pwa-pill-sub   { font-size:.68rem;color:rgba(165,180,252,.7);font-family:'Figtree',sans-serif; }
    .pwa-pill-cta   { margin-left:4px;padding:5px 14px;border-radius:9999px;font-size:.75rem;
        font-weight:800;border:none;cursor:pointer;white-space:nowrap;font-family:'Figtree',sans-serif;
        background:linear-gradient(135deg,#6366f1,#a78bfa);color:#fff;
        transition:opacity .2s; flex-shrink:0; }
    .pwa-pill-cta:hover { opacity:.88 }
    .pwa-pill-close { margin-left:2px;width:22px;height:22px;border-radius:50%;border:none;
        background:rgba(255,255,255,.08);color:rgba(255,255,255,.5);
        display:flex;align-items:center;justify-content:center;
        cursor:pointer;font-size:.8rem;line-height:1;flex-shrink:0;transition:background .2s; }
    .pwa-pill-close:hover { background:rgba(255,255,255,.16);color:#fff; }

    /* iOS tooltip */
    #pwa-ios-tip {
        position:fixed;bottom:148px;left:50%;transform:translateX(-50%) scale(.92);
        z-index:9999;background:rgba(18,15,42,.98);border:1px solid rgba(99,102,241,.3);
        border-radius:16px;padding:16px 18px;max-width:280px;width:calc(100vw - 32px);
        box-shadow:0 12px 40px rgba(0,0,0,.7);
        transition:opacity .3s,transform .3s;opacity:0;pointer-events:none;
    }
    #pwa-ios-tip.show { opacity:1;transform:translateX(-50%) scale(1);pointer-events:auto; }
    #pwa-ios-tip::after {
        content:''; position:absolute;bottom:-8px;left:50%;transform:translateX(-50%);
        border:8px solid transparent;border-bottom:none;
        border-top-color:rgba(18,15,42,.98);
    }
    .ios-tip-step { display:flex;align-items:flex-start;gap:10px;margin-bottom:10px; }
    .ios-tip-step:last-child { margin-bottom:0; }
    .ios-tip-num  { width:22px;height:22px;border-radius:50%;background:rgba(99,102,241,.25);
        border:1px solid rgba(99,102,241,.4);color:#a5b4fc;
        font-size:.68rem;font-weight:900;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .ios-tip-txt  { font-size:.78rem;color:#d1d5db;line-height:1.4;font-family:'Figtree',sans-serif; }
    .ios-tip-txt strong { color:#fff; }
    .ios-tip-close-row { display:flex;justify-content:flex-end;margin-top:12px; }
    .ios-tip-close-btn { background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.25);
        color:#a5b4fc;border-radius:8px;padding:5px 14px;font-size:.73rem;font-weight:700;
        cursor:pointer;font-family:'Figtree',sans-serif; }
    </style>

    {{-- iOS tooltip --}}
    <div id="pwa-ios-tip" role="tooltip" aria-hidden="true">
        <p style="font-size:.72rem;font-weight:800;color:#a5b4fc;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;font-family:'Figtree',sans-serif;">Install PesaQuest on iOS</p>
        <div class="ios-tip-step">
            <div class="ios-tip-num">1</div>
            <div class="ios-tip-txt">Tap the <strong>Share</strong> button <span style="font-size:1rem">⬆</span> at the bottom of Safari</div>
        </div>
        <div class="ios-tip-step">
            <div class="ios-tip-num">2</div>
            <div class="ios-tip-txt">Scroll down and tap <strong>"Add to Home Screen"</strong></div>
        </div>
        <div class="ios-tip-step">
            <div class="ios-tip-num">3</div>
            <div class="ios-tip-txt">Tap <strong>Add</strong> — PesaQuest will appear on your home screen like an app</div>
        </div>
        <div class="ios-tip-close-row">
            <button class="ios-tip-close-btn" onclick="document.getElementById('pwa-ios-tip').classList.remove('show')">Got it</button>
        </div>
    </div>

    {{-- Install pill --}}
    <div id="pwa-pill" role="button" aria-label="Install PesaQuest app">
        <img src="{{ asset('img/game/pwa-192.png') }}" class="pwa-pill-icon" alt="PesaQuest">
        <div class="pwa-pill-text">
            <span class="pwa-pill-label">PesaQuest</span>
            <span class="pwa-pill-sub">Install for the best experience</span>
        </div>
        <button class="pwa-pill-cta" id="pwa-pill-btn" type="button">Install</button>
        <button class="pwa-pill-close" id="pwa-pill-dismiss" type="button" aria-label="Dismiss">&times;</button>
    </div>

    <script>
    (function(){
        var DISMISSED_KEY = 'pq_pwa_dismissed';
        var pill    = document.getElementById('pwa-pill');
        var pillBtn = document.getElementById('pwa-pill-btn');
        var dismiss = document.getElementById('pwa-pill-dismiss');
        var iosTip  = document.getElementById('pwa-ios-tip');
        var deferredPrompt = null;

        // Already installed (standalone mode) — never show
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches
                        || window.navigator.standalone === true;
        if (isStandalone) return;

        // User already dismissed — never show again this session
        if (sessionStorage.getItem(DISMISSED_KEY)) return;

        var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

        // Chrome/Edge/Android: capture the deferred install prompt
        window.addEventListener('beforeinstallprompt', function(e){
            e.preventDefault();
            deferredPrompt = e;
        });

        // Show pill after 5 seconds
        setTimeout(function(){
            if (isStandalone) return;
            if (sessionStorage.getItem(DISMISSED_KEY)) return;
            // Only show if either we have a deferred prompt (Chrome) or it's iOS
            if (!deferredPrompt && !isIOS) return;
            pill.classList.add('pwa-pill-show');
        }, 5000);

        // Click Install button
        pillBtn.addEventListener('click', function(){
            if (isIOS) {
                // Toggle iOS instructions tooltip
                iosTip.classList.toggle('show');
                iosTip.setAttribute('aria-hidden', iosTip.classList.contains('show') ? 'false' : 'true');
            } else if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(result){
                    deferredPrompt = null;
                    hidePill();
                    if (result.outcome === 'accepted') {
                        sessionStorage.setItem(DISMISSED_KEY, '1');
                    }
                });
            }
        });

        // Dismiss ×
        dismiss.addEventListener('click', function(e){
            e.stopPropagation();
            hidePill();
            sessionStorage.setItem(DISMISSED_KEY, '1');
        });

        // Hide if installed while page is open
        window.matchMedia('(display-mode: standalone)').addEventListener('change', function(e){
            if (e.matches) hidePill();
        });

        function hidePill(){
            pill.classList.remove('pwa-pill-show');
            iosTip.classList.remove('show');
        }

        // Close iOS tip when clicking outside it
        document.addEventListener('click', function(e){
            if (iosTip.classList.contains('show')
                && !iosTip.contains(e.target)
                && !pill.contains(e.target)) {
                iosTip.classList.remove('show');
            }
        });
    })();
    </script>

    <style>body { background: #07060f; } [x-cloak]{display:none!important}</style>
    <body class="font-sans antialiased" style="background:#07060f;">
        <div class="min-h-screen" style="background:#07060f;">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <x-mobile-bottom-nav active="city" />
    </body>
</html>
