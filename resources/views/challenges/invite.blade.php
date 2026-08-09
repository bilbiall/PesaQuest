<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $metricLabel = str_replace('_', ' ', $challenge->metric);
        $goalLabel   = number_format($challenge->goal, $challenge->style === 'count' ? 0 : (fmod($challenge->goal, 1) ? 1 : 0)) . $challenge->styleSuffix();
        $pageTitle   = "{$challenge->title} — Join the Challenge on PesaQuest";
        $pageDesc    = "You've been challenged: {$challenge->title}. Target — {$goalLabel} {$metricLabel} growth"
                     . ($challenge->status === 'active' ? '. Ends ' . $challenge->ends_at->diffForHumans() . '.' : '.')
                     . ' Join free on PesaQuest, Kenya\'s financial literacy game, and race for it.';
        $ogImage     = asset('img/game/pwa-og.jpg');
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- This page is genuinely public — unlike every auth-walled page in the app, it CAN be indexed. --}}
    <meta name="robots" content="index, follow">

    {{-- Open Graph (WhatsApp, Facebook, LinkedIn unfurl these) --}}
    <meta property="og:type"         content="website">
    <meta property="og:url"          content="{{ url()->current() }}">
    <meta property="og:title"        content="{{ $pageTitle }}">
    <meta property="og:description"  content="{{ $pageDesc }}">
    <meta property="og:image"        content="{{ $ogImage }}">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="1200">
    <meta property="og:site_name"    content="PesaQuest by Moski">
    <meta property="og:locale"       content="en_KE">

    {{-- Twitter / X card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@MoskiApp">
    <meta name="twitter:title"       content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image"       content="{{ $ogImage }}">

    <link rel="icon" href="{{ asset('img/game/pwa-192.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; background:#07060f; color:#fff; font-family:'Figtree',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
        .card { max-width:480px; width:100%; background:linear-gradient(145deg,rgba(99,102,241,.1),rgba(139,92,246,.05)); border:1px solid rgba(139,92,246,.25); border-radius:1.5rem; padding:2rem 1.75rem; text-align:center; }
        .icon { font-size:3.2rem; }
        .brand { font-size:.7rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#a5b4fc; margin-top:.5rem; }
        h1 { font-size:1.3rem; font-weight:900; margin:.5rem 0 .25rem; }
        .sub { font-size:.85rem; color:#9ca3af; line-height:1.5; }
        .stats { display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap; margin:1.25rem 0; }
        .stat { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1); border-radius:.85rem; padding:.6rem .9rem; min-width:100px; }
        .stat b { display:block; font-size:1rem; color:#fbbf24; }
        .stat span { font-size:.62rem; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em; }
        .cta { display:flex; flex-direction:column; gap:.6rem; margin-top:1.5rem; }
        .btn { display:block; padding:.85rem; border-radius:.85rem; font-weight:800; font-size:.9rem; text-decoration:none; }
        .btn-primary { background:linear-gradient(135deg,#6366f1,#4338ca); color:#fff; }
        .btn-secondary { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.15); color:#fff; }
        .footer { margin-top:1.25rem; font-size:.68rem; color:#6b7280; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{{ $challenge->template?->icon ?? '🏆' }}</div>
        <div class="brand">🏙️ PesaQuest · Champions' Court</div>
        <h1>{{ $challenge->title }}</h1>
        <p class="sub">
            @if($challenge->template?->description) {{ $challenge->template->description }} @endif
            You're racing on <b>{{ $metricLabel }}</b> — target <b>{{ $goalLabel }}</b> growth
            @if($challenge->status === 'active') · ends {{ $challenge->ends_at->diffForHumans() }} (real time) @endif.
        </p>

        <div class="stats">
            <div class="stat"><b>{{ $challenge->participants_count }}</b><span>Racing</span></div>
            <div class="stat"><b>{{ $challenge->level_min }}–{{ $challenge->level_max }}</b><span>Levels</span></div>
            <div class="stat"><b>{{ $challenge->stake_amount ? 'KES '.number_format($challenge->stake_amount) : 'Free' }}</b><span>Entry</span></div>
        </div>

        @auth
        <div class="cta">
            <a href="{{ route('challenges.show', $challenge) }}" class="btn btn-primary">Open Challenge →</a>
        </div>
        @else
        <div class="cta">
            <a href="{{ route('challenges.show', $challenge) }}" class="btn btn-primary">🔑 Log In to Join</a>
            <a href="{{ route('register') }}" class="btn btn-secondary">✨ New here? Create a Free Account</a>
        </div>
        @endauth

        <p class="footer">PesaQuest — Kenya's financial literacy game. Learn budgeting, saving & investing by playing.</p>
    </div>
</body>
</html>
