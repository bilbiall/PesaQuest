<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GameSet Hub – PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#080710; font-family:'Figtree',sans-serif; }
        [x-cloak] { display:none !important; }
        [id^="gs-"] { scroll-margin-top: 1.5rem; }
        [id^="gs-"]:target { animation: gsJumpFlash 1.8s ease-out; }
        @keyframes gsJumpFlash { 0% { box-shadow: 0 0 0 3px rgba(129,140,248,0.7); } 100% { box-shadow: 0 0 0 0 rgba(129,140,248,0); } }
        .gs-bg {
            background:
                radial-gradient(ellipse at top left, rgba(99,102,241,0.10) 0%, transparent 50%),
                radial-gradient(ellipse at bottom right, rgba(139,92,246,0.08) 0%, transparent 50%),
                #080710;
        }
        .glass { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); }

        .gsh-wrap { max-width:80rem; margin:0 auto; padding:1.5rem 1rem 4rem; }
        .gsh-hero { border-radius:1.5rem; padding:1.75rem 1.5rem; border:1px solid rgba(99,102,241,.22);
                    background:linear-gradient(135deg, rgba(99,102,241,.12), rgba(139,92,246,.05) 60%, transparent); }
        .gsh-hero h1 { font-size:1.55rem; font-weight:900; color:#fff; letter-spacing:-.02em; }
        .gsh-hero p  { color:#9ca3af; font-size:.9rem; margin-top:.35rem; max-width:38rem; }
        .gsh-kicker { display:inline-block; font-size:.62rem; font-weight:900; letter-spacing:.16em; text-transform:uppercase;
                      color:#34d399; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.3);
                      padding:.3rem .8rem; border-radius:999px; margin-bottom:.75rem; }

        .gsh-sec { margin-top:2.25rem; }
        .gsh-sec-head { display:flex; align-items:baseline; gap:.6rem; margin-bottom:.9rem; }
        .gsh-sec-head h2 { font-size:1.05rem; font-weight:900; color:#fff; }
        .gsh-sec-head span { font-size:.75rem; color:#6b7280; font-weight:600; }

        .gsh-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(15rem, 1fr)); gap:.9rem; }
        .gsh-card { display:block; text-decoration:none; border-radius:1.25rem; padding:1.1rem 1.2rem;
                    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); transition:all .18s; }
        .gsh-card:hover { transform:translateY(-2px); border-color:rgba(99,102,241,.45); background:rgba(99,102,241,.07); }
        .gsh-card .ic { font-size:1.6rem; }
        .gsh-card h3 { color:#fff; font-weight:800; font-size:.95rem; margin-top:.55rem; }
        .gsh-card p  { color:#8b8fa3; font-size:.75rem; margin-top:.25rem; line-height:1.35; }
        .gsh-card .cnt { display:inline-flex; align-items:center; gap:.35rem; margin-top:.7rem; font-size:.68rem; font-weight:800;
                         color:#a5b4fc; background:rgba(99,102,241,.12); border:1px solid rgba(99,102,241,.25);
                         padding:.22rem .6rem; border-radius:999px; }
        .gsh-card .go { float:right; color:#6366f1; font-weight:900; font-size:.85rem; margin-top:.7rem; }

        .gsh-crisis { border-radius:1.25rem; padding:1rem 1.2rem; display:flex; flex-wrap:wrap; gap:.9rem; align-items:center;
                      background:rgba(245,158,11,.05); border:1px solid rgba(245,158,11,.22); }
        .gsh-crisis.calm { background:rgba(16,185,129,.05); border-color:rgba(16,185,129,.2); }
        .gsh-badge { font-size:.62rem; font-weight:900; letter-spacing:.05em; padding:.22rem .55rem; border-radius:999px; }
        .st-scheduled { background:rgba(99,102,241,.15); color:#a5b4fc; border:1px solid rgba(99,102,241,.35); }
        .st-warned    { background:rgba(245,158,11,.15); color:#fcd34d; border:1px solid rgba(245,158,11,.35); }
        .st-active    { background:rgba(239,68,68,.15);  color:#f87171; border:1px solid rgba(239,68,68,.35); }
        .st-done      { background:rgba(107,114,128,.15); color:#9ca3af; border:1px solid rgba(107,114,128,.3); }

        .ifield { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.10); border-radius:0.75rem;
                  color:white; padding:0.625rem 0.875rem; width:100%; font-size:0.875rem; font-family:inherit; }
        .ifield:focus { outline:none; border-color:rgba(99,102,241,0.6); background:rgba(99,102,241,0.05); }
    </style>
</head>
<body class="gs-bg min-h-screen text-white font-sans antialiased">

    @include('gameset.partials.topnav', ['active' => 'hub'])

    <div class="gsh-wrap">

        {{-- HERO --}}
        <div class="gsh-hero">
            <span class="gsh-kicker">⚙️ GameSet Hub</span>
            <h1>Manage the living economy of Pesa City</h1>
            <p>Everything players earn, spend, learn and survive is configured from here — assets, bills, courses, jobs, quests, deals, loans, life events and server-wide crises.</p>
            <a href="{{ route('gameset.docs') }}"
               style="display:inline-flex;align-items:center;gap:.5rem;margin-top:1rem;font-weight:800;font-size:.8rem;color:#a5b4fc;
                      text-decoration:none;padding:.55rem 1rem;border-radius:.8rem;background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.35);">
                📚 New here? Open the full GameSet Guide →
            </a>
        </div>

        {{-- CRISIS SNAPSHOT --}}
        @php $nextCrisis = $crises->first(fn($c) => in_array($c->statusKey(), ['scheduled', 'warned', 'active'])); @endphp
        <div class="gsh-sec">
            <div class="gsh-crisis {{ $nextCrisis ? '' : 'calm' }}">
                <span style="font-size:1.6rem;">{{ $nextCrisis->icon ?? '🌤️' }}</span>
                <div style="flex:1;min-width:12rem;">
                    @if($nextCrisis)
                        <div style="font-weight:800;color:#fff;font-size:.9rem;">
                            {{ $nextCrisis->name }}
                            <span class="gsh-badge st-{{ $nextCrisis->statusKey() }}" style="margin-left:.4rem;">{{ $nextCrisis->statusLabel() }}</span>
                        </div>
                        <div style="color:#9ca3af;font-size:.75rem;margin-top:.15rem;">
                            {{ $nextCrisis->effectLabel() }} · hits {{ $nextCrisis->active_from->format('D d M, H:i') }} · warning {{ $nextCrisis->warning_at->format('D d M, H:i') }}
                        </div>
                    @else
                        <div style="font-weight:800;color:#fff;font-size:.9rem;">The economy is calm</div>
                        <div style="color:#9ca3af;font-size:.75rem;margin-top:.15rem;">No crisis scheduled. Shake things up — players get a warning, then the effect hits every wallet, and it lands on their Life Story timeline.</div>
                    @endif
                </div>
                <a href="{{ route('gameset.crises.index') }}"
                   style="font-weight:800;font-size:.8rem;color:#fff;text-decoration:none;padding:.55rem 1rem;border-radius:.8rem;background:linear-gradient(135deg,#f59e0b,#d97706);">
                    🌪️ Manage Crises
                </a>
            </div>
        </div>

        {{-- ECONOMY --}}
        <div class="gsh-sec">
            <div class="gsh-sec-head"><h2>💰 Economy</h2><span>what players buy, owe and invest in</span></div>
            <div class="gsh-grid">
                <a href="{{ route('gameset.assets.index') }}" class="gsh-card">
                    <span class="ic">🛒</span><span class="go">→</span>
                    <h3>Marketplace Assets</h3>
                    <p>Products players buy for passive income — from chapati stands to apartments.</p>
                    <span class="cnt">{{ $stats['assets']['active'] }} live · {{ $stats['assets']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.bills.index') }}" class="gsh-card">
                    <span class="ic">🧾</span><span class="go">→</span>
                    <h3>Bills</h3>
                    <p>Recurring costs players must pay manually — miss one and credit score drops.</p>
                    <span class="cnt">{{ $stats['bills']['active'] }} live · {{ $stats['bills']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.deals.index') }}" class="gsh-card">
                    <span class="ic">📈</span><span class="go">→</span>
                    <h3>Investment Deals</h3>
                    <p>Equity Square offers with risk, return and maturity windows.</p>
                    <span class="cnt">{{ $stats['deals']['active'] }} live · {{ $stats['deals']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.loans.index') }}" class="gsh-card">
                    <span class="ic">🏦</span><span class="go">→</span>
                    <h3>Loan Products</h3>
                    <p>Bank credit lines: interest rates, limits and repayment terms.</p>
                    <span class="cnt">{{ $stats['loans']['active'] }} live · {{ $stats['loans']['total'] }} total</span>
                </a>
            </div>
        </div>

        {{-- LEARNING --}}
        <div class="gsh-sec">
            <div class="gsh-sec-head"><h2>🎓 Learning & Progress</h2><span>how players grow their skills and careers</span></div>
            <div class="gsh-grid">
                <a href="{{ route('gameset.courses.index') }}" class="gsh-card">
                    <span class="ic">🎓</span><span class="go">→</span>
                    <h3>Courses</h3>
                    <p>Opportunity Hub learning content that unlocks jobs.</p>
                    <span class="cnt">{{ $stats['courses']['active'] }} live · {{ $stats['courses']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.jobs.index') }}" class="gsh-card">
                    <span class="ic">💼</span><span class="go">→</span>
                    <h3>Jobs</h3>
                    <p>Pesa City employment — salaries, course requirements, employers.</p>
                    <span class="cnt">{{ $stats['jobs']['active'] }} live · {{ $stats['jobs']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.quests.index') }}" class="gsh-card">
                    <span class="ic">📜</span><span class="go">→</span>
                    <h3>Quests</h3>
                    <p>Timed goals with XP and cash rewards, level-gated.</p>
                    <span class="cnt">{{ $stats['quests']['active'] }} live · {{ $stats['quests']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.badges.index') }}" class="gsh-card">
                    <span class="ic">🏅</span><span class="go">→</span>
                    <h3>Badges</h3>
                    <p>Achievement badges awarded automatically or by hand.</p>
                    <span class="cnt">{{ $stats['badges']['total'] }} total</span>
                </a>
            </div>
        </div>

        {{-- WORLD & EVENTS --}}
        <div class="gsh-sec">
            <div class="gsh-sec-head"><h2>🌍 World & Events</h2><span>the random and scheduled drama of the simulation</span></div>
            <div class="gsh-grid">
                <a href="{{ route('gameset.life-events.index') }}" class="gsh-card">
                    <span class="ic">🎲</span><span class="go">→</span>
                    <h3>Life Events</h3>
                    <p>Random events that hit players as game days pass — fuel hikes, windfalls, chama dividends.</p>
                    <span class="cnt">{{ $stats['life_events']['active'] }} live · {{ $stats['life_events']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.crises.index') }}" class="gsh-card">
                    <span class="ic">🌪️</span><span class="go">→</span>
                    <h3>Crisis Events</h3>
                    <p>Server-wide economic shocks with a 48-hour warning, scheduled by you.</p>
                    <span class="cnt">{{ $crises->count() }} recent</span>
                </a>
                <a href="{{ route('gameset.fun-world.index') }}" class="gsh-card">
                    <span class="ic">🎡</span><span class="go">→</span>
                    <h3>Fun World</h3>
                    <p>Leisure activities that trade money for mood.</p>
                    <span class="cnt">{{ $stats['fun_world']['active'] }} live · {{ $stats['fun_world']['total'] }} total</span>
                </a>
                <a href="{{ route('players.search') }}" class="gsh-card">
                    <span class="ic">🔍</span><span class="go">→</span>
                    <h3>Players</h3>
                    <p>Search player profiles, progress and portfolios.</p>
                    <span class="cnt">lookup tool</span>
                </a>
                <a href="{{ route('gameset.dreams.index') }}" class="gsh-card">
                    <span class="ic">🌟</span><span class="go">→</span>
                    <h3>Dreams</h3>
                    <p>Expensive, non-resellable status purchases — a flex on the profile Trophy Case.</p>
                    <span class="cnt">{{ $stats['dreams']['active'] }} live · {{ $stats['dreams']['total'] }} total</span>
                </a>
                <a href="{{ route('gameset.challenges.index') }}" class="gsh-card">
                    <span class="ic">🏆</span><span class="go">→</span>
                    <h3>Challenges</h3>
                    <p>Fair PvP/team duels &amp; PesaCity broadcast challenges, measured by baseline + delta.</p>
                    <span class="cnt">{{ $stats['challenges']['active'] }} live · {{ $stats['challenges']['total'] }} total</span>
                </a>
            </div>
        </div>

        {{-- ═══ GAME CONFIG PANELS ═══ --}}
        <div class="gsh-sec">
            <div class="gsh-sec-head"><h2>⚙️ Game Configuration</h2><span>global settings that shape every player's journey</span></div>
        </div>

        {{-- Quick-jump strip — nine panels live below; skip straight to one instead of scrolling --}}
        <div class="flex flex-wrap gap-2 mt-3 mb-2">
            @foreach([
                ['gs-game-rules', '📏 Game Rules'], ['gs-life-chapters', '🌱 Life Chapters'], ['gs-xp-levels', '⚙️ XP Levels'],
                ['gs-hustle-tips', '💡 Hustle Tips'], ['gs-journey-milestones', '🗺️ Journey Milestones'], ['gs-career', '🧭 Career Fields & Tracks'],
                ['gs-quiz', '🎯 Career Quiz'], ['gs-financing', '🏦 Asset Financing'], ['gs-onboarding', '🧭 Onboarding Wizard'],
            ] as [$anchor, $label])
            <a href="#{{ $anchor }}" class="text-xs font-bold text-indigo-300 px-3 py-1.5 rounded-full transition-colors hover:text-white hover:bg-indigo-500/20" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);">{{ $label }}</a>
            @endforeach
        </div>

        {{-- Game Rules --}}
        <div id="gs-game-rules" x-data="gameRulesMgr()" class="glass rounded-2xl p-6 mt-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-white font-black text-lg">📏 Game Rules</h2>
                    <p class="text-gray-400 text-sm mt-1">Pace controls that apply to every player.</p>
                </div>
                <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                    <span x-show="!saving">💾 Save Rules</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
            <div x-show="saved" x-cloak class="mt-3 text-emerald-400 text-sm font-semibold">✓ Game rules saved!</div>
            <div x-show="error" x-cloak class="mt-3 text-red-400 text-sm font-semibold" x-text="error"></div>
            <div class="mt-4 grid gap-4" style="grid-template-columns:repeat(auto-fit,minmax(15rem,1fr));">
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">Max quests started per day
                        <x-help-tip text="Caps how many NEW quests a player may start each real day — quests already in progress can still be finished. 2–3 keeps quests a daily ritual instead of a one-sitting binge." example="2 (0 = unlimited)" />
                    </span>
                    <input type="number" min="0" max="100" x-model.number="maxQuests" class="ifield">
                    <p class="text-[11px] text-gray-600 mt-1.5">0 = unlimited. Stops players from burning through all quest content in one sitting — a steady drip keeps them coming back daily.</p>
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">"While You Were Away" — min game days
                        <x-help-tip text="How many game days must pass since the last visit before the catch-up recap popup is allowed to appear at all. Urgent news — overdue bills, payday, crises, a new life chapter — always breaks through this gate." example="7 (a game week)" />
                    </span>
                    <input type="number" min="1" max="60" x-model.number="wywaMinTicks" class="ifield">
                    <p class="text-[11px] text-gray-600 mt-1.5">The catch-up popup only appears after at least this many game days passed (default 7 = a game week). Urgent news — overdue bills, payday, crises, new chapters — always shows regardless.</p>
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">"While You Were Away" — cooldown (real minutes)
                        <x-help-tip text="Real-world quiet period after the recap shows once — it stops popup fatigue when a player closes the tab and comes straight back. Counted in real minutes, not game days." example="45 (0 = no cooldown)" />
                    </span>
                    <input type="number" min="0" max="1440" x-model.number="wywaCooldown" class="ifield">
                    <p class="text-[11px] text-gray-600 mt-1.5">After the popup shows once, it stays quiet for this many real minutes even if more game days pass. 0 = no cooldown.</p>
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">🌤 Map ambience
                        <x-help-tip text="How busy the living-world layer on the city map feels. Turn it down to Calm (or Off) if the animation distracts learners in class or stutters on low-end phones." example="Lively" />
                    </span>
                    <select x-model="ambience" class="ifield">
                        <option value="lively">Lively — birds, clouds, NPCs, weather</option>
                        <option value="calm">Calm — same effects, fewer and slower</option>
                        <option value="off">Off — static map</option>
                    </select>
                    <p class="text-[11px] text-gray-600 mt-1.5">The living-world layer on the city map: bird flocks, drifting clouds, real-time day/night, storm clouds before a crisis, strolling NPCs. Auto-disables for players with reduced-motion set.</p>
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">✈️ Sky banner text
                        <x-help-tip text="A short announcement a plane tows across the city map — handy for a theme week or a school campaign. Max 60 characters; leave it empty and the plane never flies." example="Chama week — team up and save! 🤝" />
                    </span>
                    <input type="text" maxlength="60" x-model="ambBanner" class="ifield" placeholder="e.g. Chama week — team up & save! 🤝">
                    <p class="text-[11px] text-gray-600 mt-1.5">A little plane occasionally tows this banner across the map. Leave empty for no plane (balloon and kite still fly).</p>
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">💬 Forums — show XP amounts
                        <x-help-tip text="When on, forum posts show the '+25 XP' / '+40 XP' chips next to Post/Reply buttons. Turn off to keep Forums feeling like a genuine discussion space rather than a farming ground — XP is still awarded either way, just not advertised." example="Off, if XP-chasing spam becomes a problem" />
                    </span>
                    <label class="flex items-center gap-2.5 mt-2 cursor-pointer" style="width:fit-content;">
                        <input type="checkbox" x-model="forumShowXp" class="w-4 h-4 rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500/20">
                        <span class="text-sm font-semibold text-gray-300">Show XP chips on posts &amp; replies</span>
                    </label>
                    <p class="text-[11px] text-gray-600 mt-1.5">Only hides the on-screen number — XP is still earned in the background either way.</p>
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">💬 Forums — XP per new topic
                        <x-help-tip text="XP awarded for starting a new discussion topic (subject to the daily cap below)." example="40" />
                    </span>
                    <input type="number" min="0" max="1000" x-model.number="forumXpTopic" class="ifield">
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">💬 Forums — XP per reply
                        <x-help-tip text="XP awarded for posting a reply (subject to the daily cap below)." example="25" />
                    </span>
                    <input type="number" min="0" max="1000" x-model.number="forumXpReply" class="ifield">
                </div>
                <div>
                    <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">💬 Forums — daily XP-earning posts
                        <x-help-tip text="Anti-farm cap: only a player's first N forum posts (topics + replies combined) each real day earn XP. Posting beyond this still works, it just stops earning XP for that day." example="5" />
                    </span>
                    <input type="number" min="1" max="100" x-model.number="forumDailyXpCap" class="ifield">
                    <p class="text-[11px] text-gray-600 mt-1.5">Posts beyond this many per day still work fine — they just stop earning XP until the next day.</p>
                </div>
            </div>
        </div>

        {{-- Life Chapters --}}
        <div id="gs-life-chapters" x-data="lifeChaptersMgr()" class="glass rounded-2xl p-6 mt-8">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                <div>
                    <h2 class="text-white font-black text-lg">🌱 Life Chapters
                        <x-help-tip text="Icon column: the emoji shown next to the stage name on the dashboard, Life Timeline and the chapter-up celebration. One emoji reads best." example="🌱" />
                        <x-help-tip text="Chapter name column: what players call this life stage everywhere in the game. The six stage keys underneath are fixed, so renaming is purely cosmetic — nothing breaks. Max 40 characters." example="Campus Hustler" />
                        <x-help-tip text="Tagline column: the one-line description shown under the name when a player enters the stage. Max 120 characters; leave blank for none." example="Small money, big lessons." />
                        <x-help-tip text="From Ksh column: the net worth (cash + assets + savings − debts) that moves a player into this chapter. Stage 1 is locked at 0 and every stage must be strictly higher than the one above it, or saving is rejected. Raise them all if you raise salaries or asset yields." example="Defaults ascend 0 / 50,000 / 200,000 / 1M / 5M / 20M" />
                        <x-help-tip text="Location: the neighbourhood shown next to the player's name on the Life HQ header for this chapter — purely flavor text, doesn't affect gameplay." example="Kilimani, Nairobi" />
                        <x-help-tip text="Backdrop image URL: shown behind the character card on the Life HQ header while a player is in this chapter — a skyline, street or scene that matches the stage. Leave blank to use the plain gradient background instead." example="https://example.com/img/kilimani-sunset.jpg" />
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">The six life stages every player climbs through, triggered by <b class="text-gray-300">net worth</b> (cash + assets + savings − debts). Rename them, restyle them, give each a location + backdrop, and set the net-worth trigger for each.</p>
                </div>
                <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                    <span x-show="!saving">💾 Save Chapters</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
            <p class="text-[11px] text-gray-600 mb-4">A player enters a chapter the moment their net worth reaches its trigger (and drops back if it falls — chapters mirror wealth honestly). Advancing to a new chapter awards +15 credit score, a WYWA milestone and unlocks chapter-scoped life events. The six stage keys are fixed because life events and timelines are tied to them — everything else is yours to shape.</p>
            <div x-show="saved" x-cloak class="mb-3 text-emerald-400 text-sm font-semibold">✓ Chapters saved — live for all players!</div>
            <div x-show="error" x-cloak class="mb-3 text-red-400 text-sm font-semibold" x-text="error"></div>

            <div class="space-y-2">
                <template x-for="(ch, i) in chapters" :key="ch.key">
                    <div class="p-3 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-[10px] font-black w-16 text-center px-2 py-1 rounded-full flex-shrink-0" style="background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;" x-text="'Stage ' + (i+1)"></span>
                            <input type="text" x-model="ch.icon" maxlength="4"
                                   class="w-12 text-center rounded-lg px-1 py-2 text-lg flex-shrink-0"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;" title="Icon">
                            <input type="text" x-model="ch.name" maxlength="40" placeholder="Chapter name"
                                   class="rounded-lg px-3 py-2 text-sm font-bold" style="flex:1;min-width:9rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                            <input type="text" x-model="ch.tagline" maxlength="120" placeholder="Tagline players see"
                                   class="rounded-lg px-3 py-2 text-sm" style="flex:2;min-width:12rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <span class="text-[10px] font-bold text-gray-500 uppercase">from Ksh</span>
                                <input type="number" min="0" step="1000" x-model.number="ch.min_net_worth" :disabled="i === 0"
                                       class="w-32 rounded-lg px-3 py-2 text-sm font-bold text-right"
                                       style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#34d399;outline:none;"
                                       :style="i === 0 ? 'opacity:.5;' : ''"
                                       :title="i === 0 ? 'The first stage always starts at 0' : 'Net worth that unlocks this chapter'">
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center mt-2">
                            <span class="text-[10px] font-bold text-gray-500 uppercase w-16 text-center flex-shrink-0">📍 loc</span>
                            <input type="text" x-model="ch.location" maxlength="60" placeholder="e.g. Kilimani, Nairobi"
                                   class="rounded-lg px-3 py-1.5 text-xs" style="flex:1;min-width:9rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                            <span class="text-[10px] font-bold text-gray-500 uppercase flex-shrink-0">🖼️ backdrop URL</span>
                            <input type="text" x-model="ch.background_image" maxlength="500" placeholder="https://… (blank = plain gradient)"
                                   class="rounded-lg px-3 py-1.5 text-xs" style="flex:2;min-width:12rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- XP Levels --}}
        <div id="gs-xp-levels" x-data="xpLevelsMgr()" x-init="load()" class="glass rounded-2xl p-6 mt-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-white font-black text-lg">⚙️ XP Levels
                        <x-help-tip text="Level name column: the aspirational identity shown on the player's profile, leaderboards and level-up popup — pick words you want learners calling themselves. Max 50 characters." example="Investor" />
                        <x-help-tip text="XP column: total lifetime XP needed to reach that level. Level 1 is fixed at 0 and values must ascend; the gap between two rows decides how long players sit on a rank before promotion." example="1000 to reach level 5" />
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">XP thresholds and level names. Changes apply to all players. Values must be ascending; Level 1 always starts at 0 XP.</p>
                </div>
                <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                    <span x-show="!saving">💾 Save Levels</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
            <div x-show="saved" x-cloak class="mb-3 text-emerald-400 text-sm font-semibold">✓ Level config saved!</div>
            <div x-show="error" x-cloak class="mb-3 text-red-400 text-sm font-semibold" x-text="error"></div>
            <div class="space-y-2">
                <template x-for="(lvl, i) in levels" :key="i">
                    <div class="flex gap-2 items-center p-2 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                        <span class="text-xs font-black w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                              :style="`background:linear-gradient(135deg, ${levelColor(i)});color:white;`" x-text="i+1"></span>
                        <input type="text" class="ifield" style="flex:2;" x-model="lvl.name" placeholder="Level name" maxlength="30">
                        <input type="number" class="ifield" style="flex:1;" x-model.number="lvl.xp" :disabled="i===0" min="0" :placeholder="i===0 ? '0 (start)' : 'XP needed'">
                        <button type="button" @click="levels.splice(i,1)" x-show="levels.length > 2 && i > 0" class="text-red-400 hover:text-red-300 flex-shrink-0 px-1">✕</button>
                    </div>
                </template>
                <button type="button" @click="levels.push({xp: (levels.at(-1)?.xp||0)+500, name: 'New Level'})"
                        class="w-full py-2 text-xs text-gray-500 hover:text-white border border-dashed border-white/10 hover:border-white/25 rounded-xl transition-colors">
                    + Add Level
                </button>
            </div>
        </div>

        {{-- Hustle Tips --}}
        <div id="gs-hustle-tips" x-data="hustleTipsMgr()" class="glass rounded-2xl p-6 mt-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-white font-black text-lg">💡 Hustle Tips
                        <x-help-tip text="Icon column: the emoji that leads the tip in the Pesa City sidebar. Vary them so the rotation does not look repetitive." example="💰" />
                        <x-help-tip text="Tip text column: one piece of advice players see rotating in the sidebar while they explore the city. Keep 8–15 tips live, one idea per tip, Kenyan and concrete — vague tips get ignored." example="Pay yourself first — move 10% to savings the day you are paid." />
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">These financial tips rotate in the Pesa City sidebar for all players.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="addTip()" class="px-4 py-2 rounded-xl text-sm font-bold bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/30 transition-colors">+ Add Tip</button>
                    <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                        <span x-show="!saving">💾 Save Tips</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </div>
            <div x-show="saved" x-cloak class="mb-3 text-emerald-400 text-sm font-semibold">✓ Tips saved successfully!</div>
            <div class="space-y-3">
                <template x-for="(tip, i) in tips" :key="i">
                    <div class="flex gap-3 items-start p-3 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                        <input type="text" x-model="tip.icon" placeholder="💡" maxlength="4"
                               class="w-14 text-center rounded-lg px-2 py-2 text-lg"
                               style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                        <textarea x-model="tip.text" rows="2" placeholder="Enter a financial tip..."
                                  class="flex-1 rounded-lg px-3 py-2 text-sm resize-none"
                                  style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;"></textarea>
                        <button type="button" @click="tips.splice(i,1)" class="text-red-400 hover:text-red-300 mt-1 transition-colors">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <template x-if="tips.length === 0">
                    <p class="text-gray-500 text-sm text-center py-4">No tips yet. Click "Add Tip" to create your first hustle tip.</p>
                </template>
            </div>
        </div>

        {{-- Journey Milestones section --}}
        <div id="gs-journey-milestones" x-data="journeyMilestonesMgr()" class="glass rounded-2xl p-6 mt-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-white font-black text-lg">🗺️ Player Journey Milestones
                        <x-help-tip text="Icon column: the emoji shown on the milestone's row in the Life Timeline." example="🏘️" />
                        <x-help-tip text="Title column: the goal as the player reads it on their Life Timeline. Short and aspirational beats descriptive. Max 80 characters." example="Property Owner" />
                        <x-help-tip text="Description column: optional second line under the title spelling out what the player actually has to do. Max 200 characters." example="Buy your first asset" />
                        <x-help-tip text="Type column: what the game measures to tick this milestone off — see the legend above for what each type counts. Pick manual for a narrative marker that is always shown and never auto-completes." example="asset (number of assets owned)" />
                        <x-help-tip text="Value column: the number the chosen type must reach before the milestone gets its checkmark — a KES amount for balance and net_worth, a plain count for level, job, course, quest and asset. Ignored when the type is manual." example="1 for a first asset; 10000 for Save KES 10,000" />
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Progression goals players see on their Life Timeline. Completed ones get a checkmark.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="addMilestone()" class="px-4 py-2 rounded-xl text-sm font-bold bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/30 transition-colors">+ Add</button>
                    <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                        <span x-show="!saving">💾 Save</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </div>
            <div x-show="saved" x-cloak class="mb-3 text-emerald-400 text-sm font-semibold">✓ Milestones saved!</div>

            {{-- Type legend --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">level = Level X</span>
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">balance = Save KES X</span>
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">net_worth = Net Worth X</span>
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">job = Hired X times</span>
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">course = X courses</span>
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">quest = X quests</span>
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">asset = X assets</span>
                <span class="text-[10px] px-2 py-1 rounded-full text-gray-400" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">manual = always shown</span>
            </div>

            <div class="space-y-2">
                <template x-for="(ms, i) in milestones" :key="i">
                    <div class="flex gap-2 items-center p-3 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);">
                        <input type="text" x-model="ms.icon" placeholder="🌱" maxlength="4"
                               class="w-12 text-center rounded-lg px-1 py-2 text-lg flex-shrink-0"
                               style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                        <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <input type="text" x-model="ms.title" placeholder="Title" maxlength="80"
                                   class="rounded-lg px-3 py-2 text-sm sm:col-span-1"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                            <input type="text" x-model="ms.description" placeholder="Description (optional)" maxlength="200"
                                   class="rounded-lg px-3 py-2 text-sm sm:col-span-1"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                            <select x-model="ms.type" class="rounded-lg px-3 py-2 text-sm"
                                    style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                                <option value="manual">manual</option>
                                <option value="level">level</option>
                                <option value="balance">balance</option>
                                <option value="net_worth">net_worth</option>
                                <option value="job">job</option>
                                <option value="course">course</option>
                                <option value="quest">quest</option>
                                <option value="asset">asset</option>
                            </select>
                            <input type="number" x-model.number="ms.threshold" placeholder="Value" min="0"
                                   :disabled="ms.type === 'manual'"
                                   class="rounded-lg px-3 py-2 text-sm"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                        </div>
                        <button type="button" @click="milestones.splice(i,1)" class="text-red-400 hover:text-red-300 transition-colors flex-shrink-0">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <template x-if="milestones.length === 0">
                    <p class="text-gray-500 text-sm text-center py-4">No milestones yet. Click "+ Add" to create player journey goals.</p>
                </template>
            </div>
        </div>

        {{-- Career Fields & Tracks --}}
        <div id="gs-career" x-data="careerConfigMgr()" class="glass rounded-2xl p-6 mt-8">
            <div class="mb-4">
                <h2 class="text-white font-black text-lg">🧭 Career Fields &amp; Tracks</h2>
                <p class="text-gray-400 text-sm mt-1">
                    <b class="text-gray-300">Fields</b> are the interest categories in the career quiz (e.g. "Finance &amp; Banking").
                    <b class="text-gray-300">Tracks</b> are the coarser groupings Courses and Jobs are filed under (e.g. "finance").
                    Each field recommends one track — that's how a player's quiz result surfaces relevant courses/jobs.
                    Nothing here is hardcoded; renaming or adding either list updates the quiz, Courses and Jobs everywhere at once.
                </p>
            </div>

            {{-- Tracks --}}
            <div class="rounded-xl p-4 mb-5" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-black text-white/90">📚 Course/Job Tracks
                        <x-help-tip text="Icon column: the emoji that stands for this track on Course and Job cards and in their filters." example="💻" />
                        <x-help-tip text="Label column: the human-readable track name learners see in the Courses and Jobs filters. Max 40 characters." example="Technology" />
                        <x-help-tip text="Key column: the internal code each Course and Job stores to say it belongs to this track — lowercase letters, numbers and underscores only, and unique. Rename the label freely, but changing the key leaves existing courses/jobs pointing at the old one, and any career field aimed at a deleted track falls back to the first track in this list." example="tech" />
                        <x-help-tip text="Colour column: tints this track's chips and cards across Courses, Jobs and the quiz result screen. Give each track a distinct colour so learners can scan by it." example="#6366f1" />
                    </h3>
                    <div class="flex gap-2">
                        <button type="button" @click="tracks.push({key:'',label:'',icon:'💼',color:'#6366f1'})" class="text-xs font-bold text-indigo-300 hover:text-indigo-200">+ Add Track</button>
                        <button type="button" @click="saveTracks()" :disabled="savingTracks" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30">
                            <span x-show="!savingTracks">💾 Save Tracks</span><span x-show="savingTracks">Saving…</span>
                        </button>
                    </div>
                </div>
                <div x-show="tracksSaved" x-cloak class="mb-2 text-emerald-400 text-xs font-semibold">✓ Tracks saved!</div>
                <div x-show="tracksError" x-cloak class="mb-2 text-red-400 text-xs font-semibold" x-text="tracksError"></div>
                <div class="space-y-2">
                    <template x-for="(t, i) in tracks" :key="i">
                        <div class="flex gap-2 items-center">
                            <input type="text" x-model="t.icon" maxlength="4" class="w-12 text-center rounded-lg px-1 py-1.5 text-base flex-shrink-0" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                            <input type="text" x-model="t.label" placeholder="Track label e.g. Technology" class="flex-1 rounded-lg px-3 py-1.5 text-sm" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                            <input type="text" x-model="t.key" placeholder="key e.g. tech" @input="t.key = t.key.toLowerCase().replace(/[^a-z0-9_]/g,'_')" class="w-28 rounded-lg px-2 py-1.5 text-xs font-mono flex-shrink-0" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#a5b4fc;outline:none;">
                            <input type="color" x-model="t.color" class="w-9 h-8 rounded-lg flex-shrink-0" style="background:transparent;border:1px solid rgba(255,255,255,0.1);">
                            <button type="button" @click="tracks.splice(i,1)" class="text-red-400/60 hover:text-red-400 flex-shrink-0">
                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Fields --}}
            <div class="rounded-xl p-4" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-black text-white/90">🎯 Quiz Career Fields
                        <x-help-tip text="Icon column: the emoji shown beside this interest on the quiz result screen and on the player's career page." example="🏦" />
                        <x-help-tip text="Label column: the interest category as players read it in the career quiz. Finer-grained than a track — several fields can point at the same track. Max 40 characters." example="Finance and Banking" />
                        <x-help-tip text="Key column: the internal code quiz options weight toward — lowercase letters, numbers and underscores only, and unique. Renaming a key does NOT update quiz options already saved below; re-pick the field in each affected option after saving, or its weight is silently dropped." example="finance" />
                        <x-help-tip text="Recommended track picker: which Course/Job track a player who matches this field gets steered toward on their result screen. It is a recommendation only — nothing is locked away from anyone. The list comes from the Tracks panel above, so add the track first." example="Finance and Banking → finance" />
                        <x-help-tip text="Colour column: tints this field's chips in the quiz option weights and on the result screen." example="#34d399" />
                        <x-help-tip text="📝 description (optional): the blurb shown on the result screen when the quiz matches a player to this field. Write it as encouragement addressed to the player. Max 400 characters." example="You like making numbers behave — banking, accounting and investing reward that." />
                    </h3>
                    <div class="flex gap-2">
                        <button type="button" @click="fields.push({key:'',label:'',icon:'💼',color:'#6366f1',track:tracks[0]?.key||''})" class="text-xs font-bold text-indigo-300 hover:text-indigo-200">+ Add Field</button>
                        <button type="button" @click="saveFields()" :disabled="savingFields" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30">
                            <span x-show="!savingFields">💾 Save Fields</span><span x-show="savingFields">Saving…</span>
                        </button>
                    </div>
                </div>
                <div x-show="fieldsSaved" x-cloak class="mb-2 text-emerald-400 text-xs font-semibold">✓ Fields saved!</div>
                <div x-show="fieldsError" x-cloak class="mb-2 text-red-400 text-xs font-semibold" x-text="fieldsError"></div>
                <div class="space-y-2">
                    <template x-for="(f, i) in fields" :key="i">
                        <div x-data="{ showDesc: !!f.desc }" class="rounded-lg p-2" style="background:rgba(255,255,255,0.015);">
                            <div class="flex gap-2 items-center flex-wrap">
                                <input type="text" x-model="f.icon" maxlength="4" class="w-12 text-center rounded-lg px-1 py-1.5 text-base flex-shrink-0" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                                <input type="text" x-model="f.label" placeholder="Field label e.g. Finance & Banking" style="flex:2;min-width:10rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;" class="rounded-lg px-3 py-1.5 text-sm">
                                <input type="text" x-model="f.key" placeholder="key e.g. finance" @input="f.key = f.key.toLowerCase().replace(/[^a-z0-9_]/g,'_')" class="w-28 rounded-lg px-2 py-1.5 text-xs font-mono flex-shrink-0" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#a5b4fc;outline:none;">
                                <select x-model="f.track" class="rounded-lg px-2 py-1.5 text-xs flex-shrink-0" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                                    <template x-for="t in tracks" :key="t.key"><option :value="t.key" x-text="(t.icon||'')+' '+(t.label||t.key)"></option></template>
                                </select>
                                <input type="color" x-model="f.color" class="w-9 h-8 rounded-lg flex-shrink-0" style="background:transparent;border:1px solid rgba(255,255,255,0.1);">
                                <button type="button" @click="showDesc = !showDesc" class="text-[11px] font-bold px-2 py-1.5 rounded-lg flex-shrink-0" :class="showDesc ? 'text-indigo-300' : 'text-gray-500'" style="background:rgba(255,255,255,0.04);">📝</button>
                                <button type="button" @click="fields.splice(i,1)" class="text-red-400/60 hover:text-red-400 flex-shrink-0">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <textarea x-show="showDesc" x-cloak x-model="f.desc" rows="2" placeholder="Result-screen description shown when a player matches this field..."
                                      class="w-full mt-2 rounded-lg px-3 py-2 text-xs resize-none" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;"></textarea>
                        </div>
                    </template>
                </div>
                <p class="text-[11px] text-gray-600 mt-3">Renaming a field's <b>key</b> here does not rename it inside already-saved quiz options below — re-pick it in the quiz option's field picker after saving.</p>
            </div>
        </div>

        {{-- Career Quiz Questions section --}}
        <div id="gs-quiz" x-data="quizQuestionsMgr()" class="glass rounded-2xl p-6 mt-8">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-white font-black text-lg">🎯 Career Quiz Questions
                        <x-help-tip text="Question text: what the player is asked on that step of the quiz. Keep 4–6 questions in total (max 10) and max 200 characters each — ask about what they enjoy, not what they know." example="Which of these could you happily do all weekend?" />
                        <x-help-tip text="Option emoji: the little icon on the answer card. Each question needs 2–8 options." example="📊" />
                        <x-help-tip text="Option label: the answer exactly as the player reads it on the card. Max 80 characters." example="Balance a shop's books" />
                        <x-help-tip text="Option sub-text (optional): a quieter second line under the label for a hint or example. Hidden on small phones, so never put anything essential here. Max 120 characters." example="Receipts, totals, making it all add up" />
                        <x-help-tip text="Career field chips: tap a field to make this answer count toward it, tap again to drop it, and use the ×1–×5 selector for how strongly. Weights add up across every answered question and the highest-scoring field becomes the recommendation, so give each option 1–2 fields rather than all of them." example="Balance the books → Finance ×3, Business ×1" />
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">These questions appear in the career onboarding quiz. Tap the fields a chosen option should weight toward — the fields list comes from the panel above.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="addQuestion()" class="px-4 py-2 rounded-xl text-sm font-bold bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/30 transition-colors">+ Question</button>
                    <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                        <span x-show="!saving">💾 Save</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </div>
            <div x-show="saved" x-cloak class="mb-3 text-emerald-400 text-sm font-semibold">✓ Quiz questions saved!</div>
            <div x-show="error" x-cloak class="mb-3 text-red-400 text-sm font-semibold" x-text="error"></div>

            <div class="space-y-4">
                <template x-for="(q, qi) in questions" :key="qi">
                    <div class="rounded-xl p-4" style="background:rgba(99,102,241,0.04);border:1px solid rgba(99,102,241,0.15);">
                        {{-- Question header --}}
                        <div class="flex gap-2 items-center mb-3">
                            <span class="text-xs font-black text-indigo-400 w-6 text-center" x-text="qi+1+`)`"></span>
                            <input type="text" x-model="q.question" placeholder="Question text..."
                                   class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                            <button type="button" @click="questions.splice(qi,1)" class="text-red-400 hover:text-red-300 transition-colors">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Options --}}
                        <div class="space-y-2 ml-8">
                            <template x-for="(opt, oi) in q.options" :key="oi">
                                <div class="rounded-lg p-2.5" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.05);">
                                    <div class="flex gap-2 items-center">
                                        <input type="text" x-model="opt.emoji" placeholder="💡" maxlength="4"
                                               class="w-12 text-center rounded-lg px-1 py-1.5 text-base flex-shrink-0"
                                               style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                                        <input type="text" x-model="opt.label" placeholder="Option label"
                                               class="flex-1 rounded-lg px-3 py-1.5 text-sm"
                                               style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                                        <input type="text" x-model="opt.sub" placeholder="Sub-text (optional)"
                                               class="flex-1 rounded-lg px-3 py-1.5 text-sm hidden sm:block"
                                               style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#d1d5db;outline:none;">
                                        <button type="button" @click="q.options.splice(oi,1)" class="text-red-400/60 hover:text-red-400 transition-colors flex-shrink-0">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    {{-- Field weight chips --}}
                                    <div class="flex flex-wrap gap-1.5 mt-2 ml-1">
                                        <template x-for="cf in careerFields" :key="cf.key">
                                            <button type="button"
                                                    @click="opt.fields[cf.key] !== undefined ? delete opt.fields[cf.key] : opt.fields[cf.key] = 3"
                                                    class="text-[11px] font-bold px-2 py-1 rounded-full transition-colors flex items-center gap-1"
                                                    :style="opt.fields[cf.key] !== undefined
                                                        ? `background:${cf.color}26;border:1px solid ${cf.color}88;color:${cf.color};`
                                                        : 'background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:#6b7280;'">
                                                <span x-text="cf.icon"></span><span x-text="cf.label"></span>
                                                <template x-if="opt.fields[cf.key] !== undefined">
                                                    <select @click.stop x-model.number="opt.fields[cf.key]" class="ml-1 rounded text-[10px]" style="background:rgba(0,0,0,.3);border:none;color:inherit;">
                                                        <option value="1">×1</option><option value="2">×2</option><option value="3">×3</option><option value="4">×4</option><option value="5">×5</option>
                                                    </select>
                                                </template>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <button type="button" @click="q.options.push({emoji:'💡',label:'',sub:'',fields:{}})"
                                    class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors mt-1">
                                + Add option
                            </button>
                        </div>
                    </div>
                </template>
                <template x-if="questions.length === 0">
                    <p class="text-gray-500 text-sm text-center py-4">No questions. Click "+ Question" to add the first quiz question.</p>
                </template>
            </div>
        </div>

        {{-- Asset Financing (Estates & Car Yard) --}}
        <div id="gs-financing" x-data="assetFinancingMgr()" class="glass rounded-2xl p-6 mt-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-white font-black text-lg">🏦 Asset Financing (Estates & Car Yard)</h2>
                    <p class="text-gray-400 text-sm mt-1">Deposit %, interest rate, and loan term for financed vehicle and property purchases. See GAMESET_GUIDE.md §9b for the full guide.</p>
                </div>
                <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                    <span x-show="!saving">💾 Save Terms</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
            <div x-show="saved" x-cloak class="mt-3 text-emerald-400 text-sm font-semibold">✓ Financing terms saved! New quotes use these immediately (existing loans keep their original rate).</div>
            <div x-show="error" x-cloak class="mt-3 text-red-400 text-sm font-semibold" x-text="error"></div>

            <div class="grid gap-5 mt-4" style="grid-template-columns:repeat(auto-fit,minmax(20rem,1fr));">
                <template x-for="cat in ['vehicle','property']" :key="cat">
                    <div class="rounded-xl p-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);">
                        <p class="text-sm font-black mb-3" x-text="(cat === 'vehicle' ? '🚗 Vehicle Financing (Car Yard)' : '🏠 Property Mortgage (Estates)')"></p>
                        <div class="space-y-3">
                            <div>
                                <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">Deposit (% of price)
                                    <x-help-tip text="Share of the listed price the player must pay in cash up front; the remaining balance becomes the loan principal. A higher deposit means a smaller loan and a lighter monthly installment, but the player needs more saved before they can buy at all. Max 90%." example="20% vehicle / 10% property (defaults) — 20% of a 800,000 car is a 160,000 deposit and a 640,000 loan" />
                                </span>
                                <input type="number" min="0" max="90" step="1" x-model.number="terms[cat].deposit_pct_display" class="ifield">
                                <p class="text-[11px] text-gray-600 mt-1.5">Paid up front from the player's balance; the rest becomes the loan principal.</p>
                            </div>
                            <div>
                                <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">Annual Interest Rate (%)
                                    <x-help-tip text="Yearly rate charged on whatever is still owed — it compounds every game month, so a small change balloons over a 24–36 month term and drives the total-cost-vs-cash-price figure players are shown at purchase. Changes are NOT retroactive: every existing loan keeps the rate it was signed at, so only purchases made after you save use the new number. Below about 8% credit stops feeling like a real cost." example="14% vehicle / 12% property (defaults) — vehicles are deliberately steeper because they depreciate" />
                                </span>
                                <input type="number" min="0" max="100" step="0.5" x-model.number="terms[cat].annual_rate" class="ifield">
                                <p class="text-[11px] text-gray-600 mt-1.5">Compounds every game month (30 ticks) on the outstanding balance.</p>
                            </div>
                            <div>
                                <span style="font-size:.68rem;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">Loan Term (game months)
                                    <x-help-tip text="How many game months (30 ticks each) the player has to clear the loan. A longer term splits the same principal into smaller, easier installments but charges interest for longer, so the total paid is higher — that trade-off is the lesson. Like the rate, it only applies to new purchases." example="24 vehicle / 36 property (defaults) — 24 game months = 2 game years" />
                                </span>
                                <input type="number" min="1" max="120" step="1" x-model.number="terms[cat].term_months_display" class="ifield">
                                <p class="text-[11px] text-gray-600 mt-1.5">How long the player has to pay it off (e.g. 24 months = 2 game years).</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Onboarding Wizard --}}
        <div id="gs-onboarding" x-data="onboardingWizardMgr()" class="glass rounded-2xl p-6 mt-8">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-white font-black text-lg">🧭 Onboarding Wizard
                        <x-help-tip text="Icon column: the emoji heading that step of the tutorial." example="💼" />
                        <x-help-tip text="Category column: a short tag shown above the title so players can see which system the step is about. Max 30 characters." example="Earn" />
                        <x-help-tip text="Title column: the step's headline — one system per step keeps it skimmable. Max 80 characters." example="Get a job, get paid" />
                        <x-help-tip text="Body: the paragraph explaining that step. Resist cramming two lessons into one step; add another step instead — the progress dots and Step X of N counter adapt automatically. Max 600 characters." example="Study a course, then apply for a job at the Workplace. Report to work each payday to collect your salary." />
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Shown once to every new player after they land on the dashboard, before they've dismissed it. Add, remove or reorder steps freely — there's no fixed count.</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="addStep()" class="px-4 py-2 rounded-xl text-sm font-bold bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/30 transition-colors">+ Step</button>
                    <button type="button" @click="save()" :disabled="saving" class="px-4 py-2 rounded-xl text-sm font-bold bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/30 transition-colors">
                        <span x-show="!saving">💾 Save</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </div>
            <div x-show="saved" x-cloak class="mb-3 text-emerald-400 text-sm font-semibold">✓ Onboarding wizard saved!</div>
            <div x-show="error" x-cloak class="mb-3 text-red-400 text-sm font-semibold" x-text="error"></div>

            <div class="space-y-3">
                <template x-for="(s, i) in steps" :key="i">
                    <div class="rounded-xl p-4" style="background:rgba(99,102,241,0.04);border:1px solid rgba(99,102,241,0.15);">
                        <div class="flex gap-2 items-center mb-3">
                            <span class="text-xs font-black text-indigo-400 w-6 text-center" x-text="i+1+`)`"></span>
                            <input type="text" x-model="s.icon" placeholder="🎮" maxlength="8"
                                   class="w-14 text-center rounded-lg px-1 py-2 text-base flex-shrink-0"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                            <input type="text" x-model="s.category" placeholder="Category (e.g. Earn)"
                                   class="w-40 rounded-lg px-3 py-2 text-sm font-bold flex-shrink-0"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#a5b4fc;outline:none;">
                            <input type="text" x-model="s.title" placeholder="Step title"
                                   class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold"
                                   style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;">
                            <button type="button" @click="steps.splice(i,1)" class="text-red-400 hover:text-red-300 transition-colors flex-shrink-0">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <textarea x-model="s.body" rows="2" placeholder="What this step explains to the player..."
                                  class="w-full ml-8 rounded-lg px-3 py-2 text-sm"
                                  style="width:calc(100% - 2rem);background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);color:#d1d5db;outline:none;resize:vertical;"></textarea>
                    </div>
                </template>
                <template x-if="steps.length === 0">
                    <p class="text-gray-500 text-sm text-center py-4">No steps. Click "+ Step" to add the first one.</p>
                </template>
            </div>
        </div>

    </div>

    <script>
    function gameRulesMgr() {
        return {
            maxQuests:    {{ (int) ($maxQuestsPerDay ?? 0) }},
            wywaMinTicks: {{ (int) ($wywaMinTicks ?? 7) }},
            wywaCooldown: {{ (int) ($wywaCooldownMin ?? 45) }},
            ambience:     @json($mapAmbience ?? 'lively'),
            ambBanner:    @json($ambienceBanner ?? ''),
            forumShowXp:  {{ ($forumShowXp ?? true) ? 'true' : 'false' }},
            forumXpTopic:    {{ (int) ($forumXpTopic ?? 40) }},
            forumXpReply:    {{ (int) ($forumXpReply ?? 25) }},
            forumDailyXpCap: {{ (int) ($forumDailyXpCap ?? 5) }},
            saving: false, saved: false, error: '',
            async save() {
                this.saving = true; this.saved = false; this.error = '';
                try {
                    const res = await fetch('/gameset/game-rules', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({
                            max_quests_per_day:    this.maxQuests,
                            wywa_min_ticks:        this.wywaMinTicks,
                            wywa_cooldown_minutes: this.wywaCooldown,
                            map_ambience:          this.ambience,
                            ambience_banner:       this.ambBanner,
                            forum_show_xp:         this.forumShowXp,
                            forum_xp_topic:        this.forumXpTopic,
                            forum_xp_reply:        this.forumXpReply,
                            forum_daily_xp_cap:    this.forumDailyXpCap,
                        }),
                    });
                    if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else { this.error = 'Could not save — check the value ranges.'; }
                } catch (e) { this.error = 'Network error.'; }
                finally { this.saving = false; }
            },
        }
    }

    function lifeChaptersMgr() {
        return {
            chapters: @json($lifeChapters),
            saving: false, saved: false, error: '',
            async save() {
                this.saving = true; this.saved = false; this.error = '';
                for (let i = 1; i < this.chapters.length; i++) {
                    if ((this.chapters[i].min_net_worth ?? 0) <= (this.chapters[i-1].min_net_worth ?? 0)) {
                        this.error = `"${this.chapters[i].name}" needs a higher net-worth trigger than "${this.chapters[i-1].name}".`;
                        this.saving = false; return;
                    }
                }
                try {
                    const res = await fetch('/gameset/chapters', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ chapters: this.chapters }),
                    });
                    if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else {
                        const err = await res.json();
                        this.error = err.message || Object.values(err.errors || {}).flat().join(' ') || 'Could not save chapters.';
                    }
                } catch (e) { this.error = 'Network error.'; }
                finally { this.saving = false; }
            },
        }
    }

    function xpLevelsMgr() {
        return {
            levels: [],
            saving: false,
            saved: false,
            error: '',
            async load() {
                try {
                    const res = await fetch('/gameset/level-config', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                    this.levels = await res.json();
                } catch(e) { this.error = 'Could not load level config.'; }
            },
            levelColor(i) {
                const colors = ['#6b7280,#4b5563','#6366f1,#8b5cf6','#10b981,#059669','#3b82f6,#2563eb','#8b5cf6,#7c3aed','#f59e0b,#d97706','#ec4899,#db2777','#ef4444,#dc2626','#06b6d4,#0891b2','#84cc16,#65a30d','#fbbf24,#f59e0b'];
                return colors[i % colors.length] || '#6366f1,#8b5cf6';
            },
            async save() {
                this.error = ''; this.saved = false;
                for (let i = 1; i < this.levels.length; i++) {
                    if (this.levels[i].xp <= this.levels[i-1].xp) {
                        this.error = `Level ${i+1} XP must be greater than Level ${i} XP`; return;
                    }
                }
                this.saving = true;
                try {
                    const res = await fetch('/gameset/level-config', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ levels: this.levels }),
                    });
                    if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else { this.error = 'Error saving config'; }
                } catch(e) { this.error = 'Network error'; }
                finally { this.saving = false; }
            },
        }
    }

    function careerConfigMgr() {
        return {
            tracks: @json($careerTracks),
            fields: @json($careerFields),
            savingTracks: false, tracksSaved: false, tracksError: '',
            savingFields: false, fieldsSaved: false, fieldsError: '',

            async saveTracks() {
                this.savingTracks = true; this.tracksSaved = false; this.tracksError = '';
                const keys = this.tracks.map(t => t.key);
                if (keys.some(k => !k) || new Set(keys).size !== keys.length) {
                    this.tracksError = 'Every track needs a unique key.'; this.savingTracks = false; return;
                }
                try {
                    const res = await fetch('/gameset/career-tracks', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ tracks: this.tracks }),
                    });
                    if (res.ok) { this.tracksSaved = true; setTimeout(() => this.tracksSaved = false, 3000); }
                    else { const err = await res.json(); this.tracksError = err.message || Object.values(err.errors||{}).flat().join(' ') || 'Save failed.'; }
                } catch (e) { this.tracksError = 'Network error.'; }
                finally { this.savingTracks = false; }
            },

            async saveFields() {
                this.savingFields = true; this.fieldsSaved = false; this.fieldsError = '';
                const keys = this.fields.map(f => f.key);
                if (keys.some(k => !k) || new Set(keys).size !== keys.length) {
                    this.fieldsError = 'Every field needs a unique key.'; this.savingFields = false; return;
                }
                try {
                    const res = await fetch('/gameset/career-fields', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ fields: this.fields }),
                    });
                    if (res.ok) { this.fieldsSaved = true; setTimeout(() => this.fieldsSaved = false, 3000); }
                    else { const err = await res.json(); this.fieldsError = err.message || Object.values(err.errors||{}).flat().join(' ') || 'Save failed.'; }
                } catch (e) { this.fieldsError = 'Network error.'; }
                finally { this.savingFields = false; }
            },
        }
    }

    function quizQuestionsMgr() {
        return {
            questions: @json($quizQuestions),
            careerFields: @json($careerFields),
            saving: false,
            saved: false,
            error: '',
            addQuestion() {
                this.questions.push({ question: '', options: [
                    { emoji: '💡', label: '', sub: '', fields: {} },
                    { emoji: '💡', label: '', sub: '', fields: {} },
                ]});
            },
            async save() {
                this.saving = true; this.saved = false; this.error = '';
                const payload = this.questions.filter(q => q.question.trim()).map(q => ({
                    ...q,
                    options: q.options.filter(o => o.label.trim()).map(o => ({
                        emoji: o.emoji, label: o.label, sub: o.sub || '', fields: o.fields || {},
                    }))
                }));
                try {
                    const res = await fetch('/gameset/quiz-questions', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ questions: payload }),
                    });
                    if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else {
                        const err = await res.json();
                        this.error = 'Save error: ' + (err.message || Object.values(err.errors||{}).flat().join(' '));
                    }
                } catch(e) { this.error = 'Network error'; }
                finally { this.saving = false; }
            }
        }
    }

    function assetFinancingMgr() {
        const raw = @json($financingTerms);
        // API stores deposit_pct as a 0–1 fraction and term as ticks (30 = 1 game
        // month) — this UI works in whole percent / whole months for admins, then
        // converts back on save.
        const toDisplay = (cat) => ({
            deposit_pct_display: Math.round((raw[cat].deposit_pct ?? 0) * 100),
            annual_rate: raw[cat].annual_rate ?? 0,
            term_months_display: Math.round((raw[cat].term_ticks ?? 0) / 30),
        });
        return {
            terms: { vehicle: toDisplay('vehicle'), property: toDisplay('property') },
            saving: false, saved: false, error: '',
            async save() {
                this.saving = true; this.saved = false; this.error = '';
                const payload = {};
                for (const cat of ['vehicle', 'property']) {
                    payload[cat] = {
                        deposit_pct: (this.terms[cat].deposit_pct_display ?? 0) / 100,
                        annual_rate: this.terms[cat].annual_rate ?? 0,
                        term_ticks: Math.round((this.terms[cat].term_months_display ?? 0) * 30),
                    };
                }
                try {
                    const res = await fetch('/gameset/asset-financing', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify(payload),
                    });
                    if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else { const err = await res.json(); this.error = err.message || Object.values(err.errors||{}).flat().join(' ') || 'Could not save.'; }
                } catch (e) { this.error = 'Network error.'; }
                finally { this.saving = false; }
            },
        }
    }

    function onboardingWizardMgr() {
        return {
            steps: @json($onboardingSteps),
            saving: false, saved: false, error: '',
            addStep() {
                this.steps.push({ icon: '🎯', category: '', title: '', body: '' });
            },
            async save() {
                this.saving = true; this.saved = false; this.error = '';
                const payload = this.steps.filter(s => s.title.trim());
                try {
                    const res = await fetch('/gameset/onboarding-wizard', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ steps: payload }),
                    });
                    if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else { const err = await res.json(); this.error = err.message || Object.values(err.errors||{}).flat().join(' ') || 'Could not save.'; }
                } catch (e) { this.error = 'Network error.'; }
                finally { this.saving = false; }
            },
        }
    }

    function journeyMilestonesMgr() {
        return {
            milestones: @json($journeyMilestones),
            saving: false,
            saved: false,
            addMilestone() {
                this.milestones.push({ icon: '⭐', title: '', description: '', type: 'level', threshold: 1 });
            },
            async save() {
                this.saving = true; this.saved = false;
                try {
                    const res = await fetch('/gameset/journey-milestones', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ milestones: this.milestones.filter(m => m.title.trim()) }),
                    });
                    if (res.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                    else { alert('Error saving milestones'); }
                } catch(e) { alert('Network error'); }
                finally { this.saving = false; }
            }
        }
    }

    function hustleTipsMgr() {
        return {
            tips: @json($hustleTips),
            saving: false,
            saved: false,
            addTip() { this.tips.push({ icon: '💡', text: '' }); },
            async save() {
                this.saving = true; this.saved = false;
                try {
                    const fd = new FormData();
                    fd.append('group', 'hustle_tips');
                    fd.append('hustle_tips', JSON.stringify(this.tips.filter(t => t.text.trim())));
                    fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                    const r = await fetch('/admin/settings', { method: 'POST', body: fd });
                    if (r.ok) { this.saved = true; setTimeout(() => this.saved = false, 3000); }
                } catch(e) { alert('Save failed'); }
                this.saving = false;
            }
        }
    }
    </script>
</body>
</html>
