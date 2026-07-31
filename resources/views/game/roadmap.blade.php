<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PesaQuest Roadmap – What's Coming</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { background: #07060f; }
        [x-cloak] { display: none !important; }

        .particle-bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }
        .particle { position: absolute; border-radius: 50%; opacity: 0; animation: floatUp linear infinite; }
        @keyframes floatUp { 0% { opacity:0; transform:translateY(100vh) scale(0.5); } 10% { opacity:0.5; } 90% { opacity:0.15; } 100% { opacity:0; transform:translateY(-10vh) scale(1.2); } }
        .particle:nth-child(1)  { left:5%;  width:4px;  height:4px;  background:#6366f1; animation-duration:14s; animation-delay:0s; }
        .particle:nth-child(2)  { left:20%; width:5px;  height:5px;  background:#a78bfa; animation-duration:18s; animation-delay:3s; }
        .particle:nth-child(3)  { left:40%; width:3px;  height:3px;  background:#f59e0b; animation-duration:12s; animation-delay:5s; }
        .particle:nth-child(4)  { left:60%; width:6px;  height:6px;  background:#10b981; animation-duration:20s; animation-delay:1s; }
        .particle:nth-child(5)  { left:80%; width:4px;  height:4px;  background:#ec4899; animation-duration:16s; animation-delay:7s; }
        .particle:nth-child(6)  { left:90%; width:3px;  height:3px;  background:#6366f1; animation-duration:11s; animation-delay:2s; }

        .bg-orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
        .bg-orb-1 { width:600px; height:600px; top:-200px; left:-150px; background:rgba(99,102,241,0.10); }
        .bg-orb-2 { width:500px; height:500px; bottom:-150px; right:-100px; background:rgba(139,92,246,0.09); }
        .bg-orb-3 { width:350px; height:350px; top:50%; left:60%; background:rgba(245,158,11,0.05); }

        .page-content { position: relative; z-index: 10; }

        /* ── Header ── */
        .hero-title { background: linear-gradient(135deg, #c7d2fe 0%, #a78bfa 40%, #f59e0b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* ── Feature card ── */
        .feature-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.07);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .feature-card:hover { transform: translateY(-4px); border-color: rgba(99,102,241,0.35); box-shadow: 0 20px 50px rgba(99,102,241,0.15); }
        .feature-card.live { border-color: rgba(16,185,129,0.3); background: linear-gradient(145deg, rgba(16,185,129,0.06), rgba(5,150,105,0.03)); }
        .feature-card.live:hover { border-color: rgba(16,185,129,0.55); box-shadow: 0 20px 50px rgba(16,185,129,0.15); }
        .feature-card.wip { border-color: rgba(245,158,11,0.3); background: linear-gradient(145deg, rgba(245,158,11,0.06), rgba(251,191,36,0.03)); }
        .feature-card.wip:hover { border-color: rgba(245,158,11,0.55); box-shadow: 0 20px 50px rgba(245,158,11,0.15); }
        .feature-card.planned { border-color: rgba(99,102,241,0.2); }

        /* ── Status badge ── */
        .badge-live    { background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); }
        .badge-wip     { background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3); }
        .badge-planned { background:rgba(99,102,241,0.15); color:#a5b4fc; border:1px solid rgba(99,102,241,0.3); }
        .badge-idea    { background:rgba(139,92,246,0.12); color:#c4b5fd; border:1px solid rgba(139,92,246,0.25); }

        /* ── Timeline line ── */
        .timeline-line { position:absolute; left:50%; top:0; bottom:0; width:2px; background:linear-gradient(180deg, rgba(99,102,241,0.5), rgba(139,92,246,0.3), rgba(99,102,241,0.1)); transform:translateX(-50%); }

        /* ── Section headers ── */
        .section-chip { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); }

        /* ── Stat strip ── */
        .stat-card { background:rgba(255,255,255,0.025); border:1px solid rgba(255,255,255,0.07); }

        /* ── Pulse dot for live ── */
        .pulse-dot { position:relative; }
        .pulse-dot::before { content:''; position:absolute; inset:0; border-radius:50%; background:rgba(16,185,129,0.5); animation:pingPulse 1.5s cubic-bezier(0,0,0.2,1) infinite; }
        @keyframes pingPulse { 0% { transform:scale(1); opacity:0.8; } 75%,100% { transform:scale(2.5); opacity:0; } }

        /* ── Upvote ── */
        .upvote-btn { background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.2); transition:all 0.2s; }
        .upvote-btn:hover { background:rgba(99,102,241,0.18); border-color:rgba(99,102,241,0.45); transform:scale(1.05); }
        .upvote-btn.voted { background:rgba(99,102,241,0.25); border-color:rgba(99,102,241,0.6); }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased" x-data="roadmap()" x-cloak>

    <div class="particle-bg">
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
    </div>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    <div class="page-content max-w-4xl mx-auto px-4 py-12">

        {{-- Back nav --}}
        <div class="mb-8">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-white transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Dashboard
            </a>
        </div>

        {{-- Hero --}}
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 section-chip px-4 py-2 rounded-full text-sm text-gray-400 mb-6">
                <span class="text-base">🗺️</span>
                PesaQuest Feature Roadmap
            </div>
            <h1 class="hero-title text-4xl sm:text-5xl md:text-6xl font-black mb-4 leading-tight">
                The Journey<br>Ahead
            </h1>
            <p class="text-gray-400 text-lg max-w-xl mx-auto leading-relaxed">
                Everything we're building to make PesaQuest the most engaging financial literacy game in Africa — tracking real-world impact, one decision at a time.
            </p>
        </div>

        {{-- Stats strip --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-16">
            @php
                $liveCount    = 10;
                $wipCount     = 3;
                $plannedCount = 4;
                $ideaCount    = 2;
            @endphp
            <div class="stat-card rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-emerald-400">{{ $liveCount }}</div>
                <div class="text-xs text-gray-500 mt-1">Live Features</div>
            </div>
            <div class="stat-card rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-amber-400">{{ $wipCount }}</div>
                <div class="text-xs text-gray-500 mt-1">In Progress</div>
            </div>
            <div class="stat-card rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-indigo-400">{{ $plannedCount }}</div>
                <div class="text-xs text-gray-500 mt-1">Planned</div>
            </div>
            <div class="stat-card rounded-2xl p-4 text-center">
                <div class="text-3xl font-black text-purple-400">{{ $ideaCount }}</div>
                <div class="text-xs text-gray-500 mt-1">Ideas</div>
            </div>
        </div>

        {{-- ══ LIVE SECTION ══ --}}
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="relative w-3 h-3 flex-shrink-0">
                    <div class="pulse-dot w-3 h-3 bg-emerald-400 rounded-full"></div>
                </div>
                <h2 class="text-2xl font-black text-emerald-400">Live Now</h2>
                <div class="flex-1 h-px bg-gradient-to-r from-emerald-500/30 to-transparent"></div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                @php
                $liveFeatures = [
                    ['icon'=>'🎮', 'title'=>'Node-Based Decision Game', 'desc'=>'Branching story scenarios with financial choices, XP, and balance tracking. The core PesaQuest engine.', 'tag'=>'Core'],
                    ['icon'=>'🌿', 'title'=>'Story Continuation Wizard', 'desc'=>'GameSet admins can extend any node into a full branching storyline with the one-click Branch Wizard.', 'tag'=>'Admin'],
                    ['icon'=>'⭐', 'title'=>'Dynamic XP & Level System', 'desc'=>'Configurable level names and XP thresholds stored in settings. Levels 1–11 with custom badges.', 'tag'=>'Progression'],
                    ['icon'=>'📈', 'title'=>'Investment Returns System', 'desc'=>'Investment choices mature over time. Players claim returns with coin rain animation.', 'tag'=>'Finance'],
                    ['icon'=>'🎯', 'title'=>'Daily Challenges', 'desc'=>'Age-grouped daily goals (earn Ksh, make decisions, reach balance) with 2× XP bonuses.', 'tag'=>'Engagement'],
                    ['icon'=>'📰', 'title'=>'Market Events', 'desc'=>'Real Kenyan economic events (M-Pesa promos, matatu fare hikes, chama dividends) that affect balance.', 'tag'=>'Immersion'],
                    ['icon'=>'👩‍🏫', 'title'=>'Character Mentors', 'desc'=>'Age-group guides: Zawadi (8-12), Shawn (13-17), Amina (18-25), Mama Njeri (26+).', 'tag'=>'Education'],
                    ['icon'=>'📖', 'title'=>'Financial Diary', 'desc'=>'Narrative view of your entire path history with balance insights and a personalized verdict.', 'tag'=>'Reflection'],
                    ['icon'=>'👍', 'title'=>'Scenario Ratings', 'desc'=>'Thumb up/down ratings on result nodes. Powers the content team feedback loop.', 'tag'=>'Feedback'],
                    ['icon'=>'🔄', 'title'=>'What Would Happen? Replay', 'desc'=>'Replay any start scenario from its beginning — try different choices, see how decisions compound.', 'tag'=>'Learning'],
                ];
                @endphp
                @foreach($liveFeatures as $f)
                <div class="feature-card live rounded-2xl p-5">
                    <div class="flex items-start gap-3">
                        <div class="text-2xl flex-shrink-0 mt-0.5">{{ $f['icon'] }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-white text-sm">{{ $f['title'] }}</h3>
                                <span class="badge-live text-xs px-2 py-0.5 rounded-full flex-shrink-0">{{ $f['tag'] }}</span>
                            </div>
                            <p class="text-gray-400 text-xs leading-relaxed">{{ $f['desc'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══ PHASE 15 SPRINT (IN PROGRESS) ══ --}}
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-3 h-3 bg-amber-400 rounded-full flex-shrink-0" style="animation: spin 3s linear infinite"></div>
                <h2 class="text-2xl font-black text-amber-400">Phase 15 Sprint — Active</h2>
                <div class="flex-1 h-px bg-gradient-to-r from-amber-500/30 to-transparent"></div>
            </div>
            <p class="text-gray-500 text-sm mb-6 ml-6">Tasks confirmed for this sprint. ✅ = shipped, 🔄 = in progress, 📋 = queued</p>

            @php
            $phase15 = [
                // DONE
                ['status'=>'done', 'icon'=>'🗺️', 'area'=>'Map',         'title'=>'Worldmap PNG background',          'desc'=>'Real illustrated city PNG replaces procedural canvas. Stickman walks along road waypoints to each district.',  'priority'=>'P0'],
                ['status'=>'done', 'icon'=>'🏠', 'area'=>'Map',         'title'=>'Home as default spawn position',   'desc'=>'Stickman starts at HOME (bottom-center of map) and returns there after closing a district panel.',             'priority'=>'P0'],
                ['status'=>'done', 'icon'=>'💰', 'area'=>'Savings',     'title'=>'Savings page — full UI',           'desc'=>'/savings now renders a proper page with scheme cards, create form, deposit modal, and progress bars.',         'priority'=>'P1'],
                ['status'=>'done', 'icon'=>'🛒', 'area'=>'Marketplace', 'title'=>'Buy button always reachable',      'desc'=>'Modal inner flex layout fixed so footer with buy/cancel buttons is always visible regardless of content height.','priority'=>'P1'],
                // IN PROGRESS
                ['status'=>'wip',  'icon'=>'🎯', 'area'=>'Quests',      'title'=>'In-map quest popup (no redirect)', 'desc'=>'Clicking Quest Board shows a level-gated popup. Active quests listed with locked/available states. XP scales with level.',    'priority'=>'P0'],
                ['status'=>'wip',  'icon'=>'⚡', 'area'=>'Quests',      'title'=>'Auto-completion via game actions', 'desc'=>'Budget quest completes when budget is set. First Phone quest completes when phone bought. Celebration + lesson popup.', 'priority'=>'P0'],
                ['status'=>'wip',  'icon'=>'🔔', 'area'=>'Notify',      'title'=>'Notification bell + system',      'desc'=>'Bell icon in HUD. Admin creates notifications per career path. Players see unread count + dropdown list.',              'priority'=>'P1'],
                ['status'=>'wip',  'icon'=>'📊', 'area'=>'HUD',         'title'=>'Bottom journey bar',               'desc'=>'"Your Journey" progress strip at bottom of all screens. Shows badge milestone chain (First Step → Millionaire Mind).',  'priority'=>'P1'],
                // QUEUED
                ['status'=>'todo', 'icon'=>'🏛️', 'area'=>'Map',         'title'=>'Equity Square inline logic',       'desc'=>'Bank actions (savings, investments, credit) work inside the city panel — no external page redirect needed.',          'priority'=>'P2'],
                ['status'=>'todo', 'icon'=>'💼', 'area'=>'Map',         'title'=>'Workplace District experience',    'desc'=>'Check for pending promotions/salary raises. Career event log. Random workplace encounters with financial lessons.',      'priority'=>'P2'],
                ['status'=>'todo', 'icon'=>'🛍️', 'area'=>'Marketplace', 'title'=>'Marketplace visual redesign',     'desc'=>'Beautiful item cards with category images. Richer layout, better filtering. Primary interaction zone for players.',      'priority'=>'P2'],
                ['status'=>'todo', 'icon'=>'💼', 'area'=>'Portfolio',   'title'=>'Portfolio with real images',       'desc'=>'Asset cards with representative images (phone, car, plot, etc.). More visual and realistic feel.',                      'priority'=>'P2'],
                ['status'=>'todo', 'icon'=>'📲', 'area'=>'Notify',      'title'=>'Activity reminders (notifications)','desc'=>'Smart reminders: "You have a salary due", "Your investment matured", "New course in your career track".',            'priority'=>'P2'],
                ['status'=>'todo', 'icon'=>'📱', 'area'=>'UI',          'title'=>'Responsive bottom status bar',     'desc'=>'Mobile-optimised journey strip. On small screens: compact chip row with scroll. Portrait stacks into 2 rows.',         'priority'=>'P2'],
            ];
            @endphp
            <div class="grid sm:grid-cols-2 gap-4">
            @foreach($phase15 as $f)
            @php
                $statusColor = $f['status']==='done' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : ($f['status']==='wip' ? 'text-amber-400 bg-amber-500/10 border-amber-500/20' : 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20');
                $statusLabel = $f['status']==='done' ? '✅ Shipped' : ($f['status']==='wip' ? '🔄 In Progress' : '📋 Queued');
                $cardBg      = $f['status']==='done' ? 'feature-card live' : ($f['status']==='wip' ? 'feature-card wip' : 'feature-card');
            @endphp
            <div class="{{ $cardBg }} rounded-2xl p-5">
                <div class="flex items-start gap-3">
                    <div class="text-2xl flex-shrink-0 mt-0.5">{{ $f['icon'] }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h3 class="font-bold text-white text-sm">{{ $f['title'] }}</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full border flex-shrink-0 {{ $statusColor }}">{{ $statusLabel }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded bg-white/5 text-gray-500 flex-shrink-0">{{ $f['area'] }}</span>
                        </div>
                        <p class="text-gray-400 text-xs leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
            </div>
        </div>

        {{-- ══ PLANNED SECTION ══ --}}
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-3 h-3 bg-indigo-400 rounded-full flex-shrink-0"></div>
                <h2 class="text-2xl font-black text-indigo-400">Phase 16 — Planned</h2>
                <div class="flex-1 h-px bg-gradient-to-r from-indigo-500/30 to-transparent"></div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                @php
                $plannedFeatures = [
                    ['icon'=>'🎒', 'title'=>'School Mode', 'desc'=>'Classroom-ready version for teachers. Class codes, group leaderboards, teacher dashboard with student progress tracking.', 'votes'=>24, 'key'=>'school'],
                    ['icon'=>'💬', 'title'=>'Swahili Language Mode', 'desc'=>'Full game experience in Kiswahili. "Hifadhi au Tuma?" instead of "Save or Send?" — true localization.', 'votes'=>19, 'key'=>'swahili'],
                    ['icon'=>'📲', 'title'=>'Push Notifications', 'desc'=>'Daily reminder nudges, challenge reset alerts, investment maturity notices via web push and email.', 'votes'=>15, 'key'=>'push'],
                    ['icon'=>'🏅', 'title'=>'Physical Badges & Certificates', 'desc'=>'Print-ready certificate PDFs for level milestones. Shareable on WhatsApp, LinkedIn, or as physical achievement cards.', 'votes'=>11, 'key'=>'certs'],
                    ['icon'=>'🤝', 'title'=>'Chama Group Challenges', 'desc'=>'Group investment goals with real-time contribution tracking. Group leaderboards and shared milestones.', 'votes'=>9, 'key'=>'chama'],
                    ['icon'=>'🌐', 'title'=>'Offline Mode (PWA)', 'desc'=>'Install PesaQuest as a PWA. Core gameplay, scenarios, and quests accessible without internet.', 'votes'=>8, 'key'=>'pwa'],
                ];
                @endphp
                @foreach($plannedFeatures as $f)
                <div class="feature-card planned rounded-2xl p-5">
                    <div class="flex items-start gap-3">
                        <div class="text-2xl flex-shrink-0 mt-0.5">{{ $f['icon'] }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-white text-sm">{{ $f['title'] }}</h3>
                                <span class="badge-planned text-xs px-2 py-0.5 rounded-full">Planned</span>
                            </div>
                            <p class="text-gray-400 text-xs leading-relaxed mb-3">{{ $f['desc'] }}</p>
                            <button @click="vote('{{ $f['key'] }}')"
                                    class="upvote-btn text-xs font-bold text-indigo-300 px-3 py-1.5 rounded-xl flex items-center gap-1.5"
                                    :class="voted.includes('{{ $f['key'] }}') ? 'voted' : ''">
                                <span>⬆️</span>
                                <span x-text="votes['{{ $f['key'] }}'] || {{ $f['votes'] }}"></span> votes
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══ IDEAS SECTION ══ --}}
        <div class="mb-16">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-3 h-3 bg-purple-400 rounded-full flex-shrink-0"></div>
                <h2 class="text-2xl font-black text-purple-400">Big Ideas</h2>
                <div class="flex-1 h-px bg-gradient-to-r from-purple-500/30 to-transparent"></div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                @php
                $ideaFeatures = [
                    ['icon'=>'🤝', 'title'=>'Multi-Player Chama Mode', 'desc'=>'3-5 players form a chama, pool decisions, vote on financial choices together. Real chama dynamics simulated in-game.', 'votes'=>32, 'key'=>'chama'],
                    ['icon'=>'🌐', 'title'=>'East Africa Expansion', 'desc'=>'Uganda shillings, Tanzania, Rwanda — same game engine, local currencies, local market events, local mentors.', 'votes'=>28, 'key'=>'eastafrica'],
                ];
                @endphp
                @foreach($ideaFeatures as $f)
                <div class="feature-card rounded-2xl p-5" style="border-color:rgba(139,92,246,0.2); background:linear-gradient(145deg,rgba(139,92,246,0.06),rgba(99,102,241,0.03));">
                    <div class="flex items-start gap-3">
                        <div class="text-3xl flex-shrink-0 mt-0.5">{{ $f['icon'] }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-white text-sm">{{ $f['title'] }}</h3>
                                <span class="badge-idea text-xs px-2 py-0.5 rounded-full">Dream Big</span>
                            </div>
                            <p class="text-gray-400 text-xs leading-relaxed mb-3">{{ $f['desc'] }}</p>
                            <button @click="vote('{{ $f['key'] }}')"
                                    class="upvote-btn text-xs font-bold text-purple-300 px-3 py-1.5 rounded-xl flex items-center gap-1.5"
                                    :class="voted.includes('{{ $f['key'] }}') ? 'voted' : ''">
                                <span>🚀</span>
                                <span x-text="votes['{{ $f['key'] }}'] || {{ $f['votes'] }}"></span> wants this
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══ IMPACT NUMBERS ══ --}}
        <div class="rounded-3xl p-8 mb-12 text-center"
             style="background:linear-gradient(145deg,rgba(99,102,241,0.10),rgba(139,92,246,0.07)); border:1px solid rgba(99,102,241,0.2);">
            <h2 class="text-2xl font-black text-white mb-2">Why It Matters</h2>
            <p class="text-gray-400 text-sm mb-8 max-w-lg mx-auto">Every feature we ship brings us closer to a Kenya where young people make confident financial decisions.</p>
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <div class="text-4xl font-black text-indigo-400 mb-1">73%</div>
                    <div class="text-xs text-gray-500">of Kenyans aged 15–34 lack formal financial literacy training</div>
                </div>
                <div>
                    <div class="text-4xl font-black text-emerald-400 mb-1">M-Pesa</div>
                    <div class="text-xs text-gray-500">covers 96% of adult population — the most logical financial tool to teach around</div>
                </div>
                <div>
                    <div class="text-4xl font-black text-amber-400 mb-1">1 game</div>
                    <div class="text-xs text-gray-500">can teach more in 20 minutes than a semester of classroom lectures</div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-600 pb-8">
            <p>PesaQuest by <span class="text-indigo-400">Moski</span> — empowering financial futures across Kenya</p>
            <p class="mt-1">Roadmap updates monthly · <a href="{{ route('game.play') }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">Play now</a> · <a href="{{ route('dashboard') }}" class="text-indigo-400 hover:text-indigo-300 transition-colors">Dashboard</a></p>
        </div>

    </div>

    <script>
    function roadmap() {
        return {
            voted: JSON.parse(localStorage.getItem('pq_votes') || '[]'),
            votes: {},

            vote(key) {
                if (this.voted.includes(key)) {
                    this.voted = this.voted.filter(v => v !== key);
                    this.votes[key] = (this.votes[key] || 0) - 1;
                } else {
                    this.voted.push(key);
                    this.votes[key] = (this.votes[key] || 0) + 1;
                }
                localStorage.setItem('pq_votes', JSON.stringify(this.voted));
            }
        }
    }
    </script>
</body>
</html>
