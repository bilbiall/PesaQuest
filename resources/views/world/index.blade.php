<x-layouts.world>

{{-- ══ While You Were Away — life sim catchup (shown before city loads) ══ --}}
@include('game.partials.life-sim-catchup')

{{-- Session events from EventEngine — picked up by Alpine init() --}}
<script>
  window.__PESA_EVENTS__               = @json($sessionEvents ?? []);
  window.__PESA_BALANCE__              = {{ (int)($balance ?? 0) }};
  window.__PESA_DISTRICTS__            = @json($districts ?? []);
  window.__PESA_DISTRICT_POS__         = @json($districtPositions ?? []);
  window.__PESA_QUEST_COMPLETIONS__    = @json($pendingQuestCompletions ?? []);
  window.__PESA_STEP_FIRES__           = @json($pendingStepFires ?? []);
  window.__PESA_PLAYER_LEVEL__         = {{ (int)($level ?? 1) }};
  window.__PESA_ACTIVE_QUESTS__        = @json($activeQuests ?? []);
  window.__PESA_TIPS__                 = @json($hustleTips ?? []);
  window.__PESA_ASSET_BASE__           = "{{ rtrim(asset(''), '/') }}";
</script>

{{-- First-time onboarding wizard — the career quiz redirects here, so this
     page must be able to show it too, not just the Dashboard. --}}
@if($showOnboardingWizard ?? false)
<div x-data="onboardingWizard(@json($onboardingSteps))" x-show="visible" x-cloak
     class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9995;overflow-y:auto;overscroll-behavior:contain;background:rgba(0,0,0,0.78);backdrop-filter:blur(8px);">
    <div class="max-w-lg w-full bg-[#12111f] border border-indigo-500/35 rounded-3xl p-5 sm:p-8 my-auto relative">
        <button @click="close()" title="Close wizard"
                class="absolute top-3 right-3 sm:top-4 sm:right-4 w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-white/10 transition-colors">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5 sm:w-4 sm:h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex items-center gap-1.5 mb-4 sm:mb-5">
            <template x-for="(s, i) in steps" :key="i">
                <div class="h-1.5 flex-1 rounded-full transition-colors" :style="i <= step ? 'background:#6366f1;' : 'background:rgba(255,255,255,.1);'"></div>
            </template>
        </div>

        <template x-for="(s, i) in steps" :key="'step-'+i">
            <div x-show="step === i">
                <div class="text-4xl sm:text-6xl mb-3 sm:mb-4 text-center" x-text="s.icon"></div>
                <p class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest text-indigo-400 text-center mb-1.5" x-text="s.category"></p>
                <h2 class="text-lg sm:text-2xl font-black mb-2 sm:mb-3 text-center" x-text="s.title"></h2>
                <p class="text-gray-400 text-xs sm:text-sm leading-relaxed text-center" x-text="s.body"></p>
            </div>
        </template>

        <div class="flex items-center justify-between gap-3 mt-6 sm:mt-8">
            <button @click="close()" class="px-4 py-2.5 sm:px-5 sm:py-3 rounded-2xl text-xs sm:text-sm font-bold text-gray-500 hover:text-white hover:bg-white/5 transition-colors">
                Close wizard
            </button>
            <button @click="next()"
                    class="flex-1 max-w-[12rem] bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black py-2.5 sm:py-3 rounded-2xl text-xs sm:text-sm shadow-xl shadow-indigo-500/30 hover:scale-105 transition-transform">
                <span x-text="step < steps.length - 1 ? 'Next' : 'Start Playing!'"></span>
            </button>
        </div>
        <p class="text-[10px] sm:text-[11px] text-gray-600 text-center mt-3">Step <span x-text="step+1"></span> of <span x-text="steps.length"></span></p>
    </div>
</div>
<script>
    function onboardingWizard(steps) {
        return {
            steps: steps && steps.length ? steps : [],
            step: 0,
            // Closing early (X / "Close wizard") hides it for THIS session only —
            // it used to permanently mark onboarding complete, so one stray click
            // meant the tour never appeared again. Only finishing the last step
            // persists completion.
            visible: sessionStorage.getItem('wizard_snoozed') !== '1',
            finish() {
                this.visible = false;
                fetch('{{ route('onboarding.complete') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                });
            },
            next() {
                if (this.step < this.steps.length - 1) { this.step++; }
                else { this.finish(); }
            },
            close() {
                this.visible = false;
                sessionStorage.setItem('wizard_snoozed', '1');
            },
        }
    }
</script>
@endif

{{-- Career-quiz CTA — a player can land here directly (e.g. the PWA's own
     "Pesa City" home-screen shortcut points at /world, not /dashboard), so
     this page needs its own prompt too. Without it, career_field never gets
     set for that entry point and the onboarding wizard above can never
     unlock (shouldShow() requires career_field to be set first). --}}
@if($needsCareerQuiz ?? false)
<div class="modal-overlay fixed inset-0 flex items-center justify-center p-4" style="z-index:9990;overflow-y:auto;overscroll-behavior:contain;">
    <div class="max-w-md w-full bg-[#12111f] border border-indigo-500/35 rounded-3xl p-6 sm:p-10 text-center my-auto">
        <div class="mb-3 sm:mb-4 animate-bounce flex justify-center"><x-icon name="target" class="w-11 h-11 sm:w-16 sm:h-16 text-indigo-400" /></div>
        <h2 class="text-lg sm:text-2xl font-black mb-2 text-white">Start Your Career Journey</h2>
        <p class="text-gray-400 text-xs sm:text-sm leading-relaxed mb-5 sm:mb-6">Take a quick 5-question quiz and we'll match you to the perfect career in PesaQuest's world.</p>
        <a href="{{ route('life.quiz') }}"
           class="block bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black py-3 sm:py-4 rounded-2xl text-sm sm:text-base shadow-xl shadow-indigo-500/40 hover:scale-105 transition-transform">
            🚀 Take the Career Quiz
        </a>
        <p class="text-[10px] sm:text-[11px] text-gray-600 mt-3">Takes 2 minutes · Fully personalised</p>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════
     PESA CITY — WORLD VIEW
     Phase 1: Map Shell + HUD + Sidebar
══════════════════════════════════════ --}}

<div class="pc-root"
     x-data="pesaCity()"
     x-init="init()"
     @keydown.escape.window="closePanel()">

    {{-- ── TOP HUD ── --}}
    <div class="pc-hud">
        {{-- Left: logo + level --}}
        <div class="pc-hud-left">
            <a href="{{ route('dashboard') }}" class="pc-back-btn" title="Back to Dashboard">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="pc-city-name" style="color:#f59e0b;text-shadow:0 0 8px rgba(251,191,36,0.95),0 0 20px rgba(245,158,11,0.6),0 0 40px rgba(245,158,11,0.25);font-weight:900;letter-spacing:0.05em;">PesaQuest</div>
        </div>

        {{-- Center: stats --}}
        <div class="pc-hud-center">
            <div class="pc-stat">
                <span class="pc-stat-icon"><x-icon name="bolt" /></span>
                <span class="pc-stat-label">LV {{ $level }}</span>
                <div class="pc-xp-track">
                    <div class="pc-xp-fill" style="width: {{ $xpPercent }}%"></div>
                </div>
                <span class="pc-stat-val">{{ $xpPercent }}%</span>
            </div>
            <div class="pc-stat-divider"></div>
            <div class="pc-stat">
                <span class="pc-stat-icon"><x-icon name="coin" /></span>
                <span class="pc-stat-label">Balance</span>
                <span class="pc-stat-val pc-balance"
                      x-text="'KES ' + liveBalance.toLocaleString()"
                      x-cloak>KES {{ number_format($balance) }}</span>
            </div>
            <div class="pc-stat-divider"></div>
            <div class="pc-stat">
                <span class="pc-stat-icon"><x-icon name="bar-chart" /></span>
                <span class="pc-stat-label">Net Worth</span>
                <span class="pc-stat-val">KES {{ number_format($netWorth) }}</span>
            </div>
            <div class="pc-stat-divider"></div>
            <div class="pc-stat">
                <span class="pc-stat-icon"><x-icon name="medal" /></span>
                <span class="pc-stat-label">Credit</span>
                <span class="pc-stat-val" style="color: {{ $creditScore >= 650 ? '#15C77E' : ($creditScore >= 500 ? '#FFBC00' : '#EF5350') }}">
                    {{ $creditScore }}
                </span>
            </div>
            <div class="pc-stat-divider"></div>
            <div class="pc-stat" title="Character mood — recharge in Fun World">
                <span class="pc-stat-icon">{{ $mood >= 80 ? '😄' : ($mood >= 55 ? '😊' : ($mood >= 35 ? '😐' : '😔')) }}</span>
                <span class="pc-stat-label">Mood</span>
                <span class="pc-stat-val" style="color: {{ $mood >= 70 ? '#FF6B35' : ($mood >= 40 ? '#FFBC00' : '#EF5350') }}">
                    {{ $mood }}
                </span>
            </div>
        </div>

        {{-- Right: actions --}}
        <div class="pc-hud-right">
            <a href="{{ route('marketplace') }}" class="pc-hud-btn" title="Marketplace"><x-icon name="cart" /></a>
            <a href="{{ route('portfolio') }}" class="pc-hud-btn" title="Portfolio"><x-icon name="bar-chart" /></a>
            <a href="{{ route('life.board') }}" class="pc-hud-btn" title="Life Board"><x-icon name="house" /></a>
            <a href="{{ route('dashboard') }}" class="pc-hud-btn" title="Dashboard"><x-icon name="monitor" /></a>

            {{-- ── NOTIFICATION BELL ── --}}
            <div class="pc-notif-wrap" @click.outside="notifOpen = false">
                <button class="pc-hud-btn pc-notif-btn"
                        @click="toggleNotifications()"
                        title="Notifications">
                    <x-icon name="bell" />
                    <span class="pc-notif-badge"
                          x-show="notifUnread > 0"
                          x-text="notifUnread > 9 ? '9+' : notifUnread"
                          x-cloak></span>
                </button>

                {{-- Notification dropdown --}}
                <div class="pc-notif-dropdown"
                     x-show="notifOpen"
                     x-cloak
                     x-transition:enter="pc-panel-enter"
                     x-transition:enter-start="pc-panel-enter-start"
                     x-transition:enter-end="pc-panel-enter-end">

                    <div class="pc-notif-header">
                        <span class="pc-notif-title">Notifications</span>
                        <button class="pc-notif-read-all"
                                @click="markAllRead()"
                                x-show="notifUnread > 0"
                                x-cloak>Mark all read</button>
                    </div>

                    <div class="pc-notif-body">
                        <template x-if="notifLoading">
                            <div class="pc-notif-empty">
                                <div class="pc-spinner" style="width:20px;height:20px;border-width:2px;"></div>
                            </div>
                        </template>
                        <template x-if="!notifLoading && notifications.length === 0">
                            <div class="pc-notif-empty">
                                <span style="font-size:24px;">🔔</span>
                                <span>All caught up!</span>
                            </div>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <div class="pc-notif-item" :class="{ 'pc-notif-unread': !n.is_read, 'pc-notif-link': !!n.url }"
                                 @click="if (n.url) window.location.href = n.url"
                                 :title="n.url ? 'Open' : ''">
                                <span class="pc-notif-item-icon" x-text="n.icon"></span>
                                <div class="pc-notif-item-body">
                                    <div class="pc-notif-item-title" x-text="n.title"></div>
                                    <div class="pc-notif-item-text" x-text="n.body"></div>
                                </div>
                                <span class="pc-notif-item-go" x-show="n.url">›</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <button class="pc-hud-btn pc-sound-btn"
                    @click="toggleSound()"
                    :title="soundOn ? 'Sound On (click to mute)' : 'Sound Off (click to unmute)'"
                    x-text="soundOn ? '🔊' : '🔇'"
                    x-cloak>🔊</button>
            <button class="pc-sidebar-toggle"
                    @click="toggleSidebar()"
                    :title="sidebarOpen ? 'Close Profile' : 'Open Profile'">☰</button>
        </div>
    </div>

    {{-- ── MAIN AREA (Map + Left Nav + Sidebar) ── --}}
    <div class="pc-main">

        {{-- ── LEFT NAV ── --}}
        <nav class="pc-left-nav" aria-label="City navigation">
            <a href="{{ route('world') }}" class="pc-lnav-item pc-lnav-active" title="City Map">
                <span class="pc-lnav-icon"><x-icon name="map" /></span>
                <span class="pc-lnav-label">Map</span>
            </a>
            <a href="{{ route('marketplace') }}" class="pc-lnav-item" title="Marketplace">
                <span class="pc-lnav-icon"><x-icon name="cart" /></span>
                <span class="pc-lnav-label">Market</span>
            </a>
            <a href="/opportunities" class="pc-lnav-item" title="Courses & Jobs">
                <span class="pc-lnav-icon"><x-icon name="graduation" /></span>
                <span class="pc-lnav-label">Skills</span>
            </a>
            <button class="pc-lnav-item" @click="walkToDistrict('quests')" title="Quests" style="background:none;border:none;width:100%;cursor:pointer;">
                <span class="pc-lnav-icon"><x-icon name="checklist" /></span>
                <span class="pc-lnav-label">Quests</span>
            </button>
            <div class="pc-lnav-divider"></div>
            <a href="{{ route('savings.index') }}" class="pc-lnav-item" title="Savings & Bank">
                <span class="pc-lnav-icon"><x-icon name="bank" /></span>
                <span class="pc-lnav-label">Bank</span>
            </a>
            <a href="{{ route('life.board') }}" class="pc-lnav-item" title="Life Board">
                <span class="pc-lnav-icon"><x-icon name="house" /></span>
                <span class="pc-lnav-label">Home</span>
            </a>
            <a href="{{ route('portfolio') }}" class="pc-lnav-item" title="Portfolio">
                <span class="pc-lnav-icon"><x-icon name="trend-up" /></span>
                <span class="pc-lnav-label">Invest</span>
            </a>
            <a href="{{ route('chama.index') }}" class="pc-lnav-item" title="Chama">
                <span class="pc-lnav-icon"><x-icon name="group" /></span>
                <span class="pc-lnav-label">Chama</span>
            </a>
            <div class="pc-lnav-divider"></div>
            <button class="pc-lnav-item" onclick="pqMenuOpen()" title="Full menu — Friends, Forums, Premium & more" style="background:none;border:none;width:100%;cursor:pointer;">
                <span class="pc-lnav-icon">☰</span>
                <span class="pc-lnav-label">Menu</span>
            </button>
        </nav>

        {{-- ── MAP CANVAS ── --}}
        <div class="pc-map" id="pc-map"
             style="background-image: url('{{ asset('img/game/worldmap.webp') }}'); background-size: 100% 100%; background-repeat: no-repeat; background-position: top left;">

            {{-- CSS placeholder background is in world.css.
                 Swap for the real WebP in Phase 0 by setting:
                 background-image: url('/images/world/pesa-city.webp');
                 on .pc-map in world.css --}}

            {{-- ── NEIGHBORHOOD TINT ZONES (atmospheric glow per district) ── --}}
            <div class="pc-neighborhood" style="left:56%;top:13%;width:140px;height:115px;background:radial-gradient(circle,rgba(21,199,126,0.07) 0%,transparent 100%);"></div>
            <div class="pc-neighborhood" style="left:8%;top:10%;width:130px;height:110px;background:radial-gradient(circle,rgba(77,168,247,0.07) 0%,transparent 100%);"></div>
            <div class="pc-neighborhood" style="left:66%;top:38%;width:120px;height:100px;background:radial-gradient(circle,rgba(53,195,240,0.06) 0%,transparent 100%);"></div>
            <div class="pc-neighborhood" style="left:28%;top:63%;width:115px;height:95px;background:radial-gradient(circle,rgba(163,230,53,0.05) 0%,transparent 100%);"></div>
            <div class="pc-neighborhood" style="left:5%;top:52%;width:110px;height:90px;background:radial-gradient(circle,rgba(255,188,0,0.06) 0%,transparent 100%);"></div>
            <div class="pc-neighborhood" style="left:80%;top:9%;width:120px;height:100px;background:radial-gradient(circle,rgba(255,107,53,0.07) 0%,transparent 100%);"></div>
            <div class="pc-neighborhood" style="left:70%;top:58%;width:110px;height:90px;background:radial-gradient(circle,rgba(167,139,250,0.05) 0%,transparent 100%);"></div>
            <div class="pc-neighborhood" style="left:30%;top:25%;width:115px;height:95px;background:radial-gradient(circle,rgba(99,102,241,0.07) 0%,transparent 100%);"></div>

            {{-- ── ROADS ── --}}
            <div class="pc-road pc-road-h" style="top: 47%"></div>
            <div class="pc-road pc-road-v" style="left: 48%"></div>
            <div class="pc-road pc-road-h" style="top: 72%"></div>
            <div class="pc-road pc-road-h pc-road-sm" style="top: 22%"></div>
            <div class="pc-road pc-road-v pc-road-sm" style="left: 22%"></div>
            <div class="pc-road pc-road-v pc-road-sm" style="left: 72%"></div>

            {{-- ── CENTRAL PLAZA ── --}}
            <div class="pc-plaza">
                <div class="pc-fountain">
                    <span class="pc-fountain-icon">⛲</span>
                </div>
            </div>

            {{-- ── WATER STRIP ── --}}
            <div class="pc-water">
                <span class="pc-water-label">~ Nairobi River ~</span>
            </div>

            {{-- ── DISTRICT ZONES ── --}}
            @foreach($districts as $slug => $district)
            @php
                $pos = $districtPositions[$slug] ?? ['left'=>45,'top'=>45,'width'=>10,'height'=>10];
                $isMissionDistrict = in_array($slug, $missionDistricts ?? []);
                $isActionable = in_array($slug, $actionableSlugs ?? []);
            @endphp
            <div class="pc-district
                        {{ $district['status'] === 'active' ? 'pc-district-active' : '' }}
                        {{ $district['status'] === 'locked' ? 'pc-district-locked' : '' }}
                        {{ $district['status'] === 'coming-soon' ? 'pc-district-soon' : '' }}
                        {{ $isMissionDistrict ? 'pc-district-mission' : '' }}
                        {{ $isActionable ? 'pc-district-actionable' : '' }}"
                 style="left: {{ $pos['left'] }}%; top: {{ $pos['top'] }}%; width: {{ $pos['width'] }}%; height: {{ $pos['height'] }}%; --district-color: {{ $district['color'] }};"
                 data-slug="{{ $slug }}"
                 data-name="{{ $district['name'] }}"
                 @click="visitDistrict('{{ $slug }}', $el)"
                 title="{{ $district['name'] }}">
                <div class="pc-district-icon"><x-icon :name="$district['icon']" class="w-7 h-7" /></div>
                <div class="pc-district-label">
                    <div class="pc-district-name">{{ Str::limit($district['name'], 16) }}</div>
                    @if(isset($district['tagline']))
                    <div class="pc-district-sub">{{ $district['tagline'] }}</div>
                    @endif
                </div>
                @if($district['status'] === 'locked')
                <div class="pc-district-lock">🔒</div>
                @endif
            </div>
            @endforeach

            {{-- ── PLAYER LOCATION PIN (replaces walking stickman) ── --}}
            <div class="pc-player-pin" id="pc-player-pin" style="left: 48%; top: 70%;">
                <div class="pc-pin-label" x-show="headingTo" x-cloak>
                    <span x-text="headingTo"></span>
                </div>
                <div class="pc-pin-inner">
                    <div class="pc-pin-frame">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ auth()->user()->profile_photo }}" alt="" class="pc-pin-img">
                        @else
                            <div class="pc-pin-initials">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        @endif
                    </div>
                    <div class="pc-pin-tail"></div>
                </div>
            </div>

            {{-- ── DECORATIVE TREES ── --}}
            @foreach([[12,8],[30,6],[42,4],[65,8],[76,5],[92,10],[8,35],[18,38],[85,35],[90,42],[15,58],[35,72],[60,68],[88,65],[53,4],[24,17],[38,28]] as $t)
            <div class="pc-tree" style="left:{{ $t[0] }}%;top:{{ $t[1] }}%;"></div>
            @endforeach

            {{-- ── STREETLAMPS (road intersections + district approaches) ── --}}
            <div class="pc-lamp" style="left:48%;top:30%;"></div>
            <div class="pc-lamp" style="left:48%;top:22%;"></div>
            <div class="pc-lamp" style="left:22%;top:47%;"></div>
            <div class="pc-lamp" style="left:35%;top:47%;"></div>
            <div class="pc-lamp" style="left:62%;top:47%;"></div>
            <div class="pc-lamp" style="left:72%;top:47%;"></div>
            <div class="pc-lamp" style="left:22%;top:72%;"></div>
            <div class="pc-lamp" style="left:48%;top:64%;"></div>
            <div class="pc-lamp" style="left:72%;top:72%;"></div>

            {{-- ── MATATUS (animated city buses on roads) ── --}}
            <div class="pc-matatu pc-matatu-orange" style="left:30%;top:47%;animation-delay:-3s;"></div>
            <div class="pc-matatu pc-matatu-green"  style="left:62%;top:47%;animation-delay:-7s;"></div>
            <div class="pc-matatu pc-matatu-yellow" style="left:55%;top:72%;animation-delay:-1s;"></div>
            <div class="pc-matatu-v pc-matatu-blue" style="left:48%;top:25%;animation-delay:-5s;"></div>

            {{-- ── LIVING-WORLD AMBIENCE (birds, clouds, day/night, weather, NPCs) ── --}}
            @include('partials.map-ambience')

            {{-- ── HOME MARKER — your house: Life HQ shortcuts (the piece no longer
                 auto-returns here; tap Home to walk back + see your home links) ── --}}
            <div class="pc-home-spot" x-data="{ homeOpen: false }" @click.outside="homeOpen = false">
                <button type="button" class="pc-home-chip" title="Your home — life shortcuts"
                        @click="homeOpen = !homeOpen; if (homeOpen) _returnToPlaza()">
                    <x-icon name="house" class="w-4 h-4 inline-block" /> <span>Home</span>
                </button>
                <div class="pc-home-panel" x-show="homeOpen" x-cloak
                     x-transition:enter="pc-panel-enter" x-transition:enter-start="pc-panel-enter-start" x-transition:enter-end="pc-panel-enter-end">
                    <div class="pc-home-title"><x-icon name="house" class="w-4 h-4 inline-block" /> Karibu nyumbani!</div>
                    <a href="{{ route('life.board') }}"><x-icon name="clipboard" class="w-3.5 h-3.5 inline-block" /> Life HQ <small>bills · assets · mood</small></a>
                    <a href="{{ route('life.timeline') }}"><x-icon name="calendar" class="w-3.5 h-3.5 inline-block" /> My Timeline <small>your story so far</small></a>
                    <a href="{{ route('life.career') }}"><x-icon name="briefcase" class="w-3.5 h-3.5 inline-block" /> Career &amp; Work <small>report to work · payslips</small></a>
                    <a href="{{ route('portfolio') }}"><x-icon name="bar-chart" class="w-3.5 h-3.5 inline-block" /> Portfolio <small>investments &amp; net worth</small></a>
                    <a href="{{ route('dashboard') }}"><x-icon name="monitor" class="w-3.5 h-3.5 inline-block" /> Dashboard <small>your command centre</small></a>
                    <a href="{{ route('how-to') }}"><x-icon name="compass" class="w-3.5 h-3.5 inline-block" /> How to Play <small>the full guide</small></a>
                </div>
            </div>

            {{-- ── SCROLL HINT (portrait mobile — auto-hides after 4.5s or first scroll) ── --}}
            <div class="pc-scroll-hint" id="pc-scroll-hint" aria-hidden="true">
                <div class="pc-scroll-hint-pill">
                    <span>👆</span>
                    <span>Scroll to explore the city</span>
                </div>
                <div class="pc-scroll-hint-arrow">↕</div>
            </div>

            {{-- ── ARRIVAL CELEBRATION POPUP ── --}}
            <div class="pc-arrival"
                 x-show="arrival.show"
                 x-cloak
                 :style="`--ac: ${arrival.color}`"
                 @click="arrival.show = false">
                <span class="pc-arrival-icon" x-html="pqIcon(arrival.icon, 'w-10 h-10')" style="display:flex;justify-content:center;"></span>
                <div class="pc-arrival-name" x-text="arrival.name"></div>
                <div class="pc-arrival-tag"  x-text="arrival.tagline"></div>
                <div class="pc-arrival-hint">tap to dismiss</div>
            </div>

        </div>{{-- /pc-map --}}

        {{-- ══════════════════════════════════════════════════════════
             COURSE COMPLETION POPUP
        ══════════════════════════════════════════════════════════ --}}
        <template x-if="coursePopup.show && coursePopup.course">
            <div class="pc-cpop-overlay" @click.self="closeCoursePopup()">
                <div class="pc-cpop-box">

                    {{-- Close --}}
                    <button class="pc-cpop-close" @click="closeCoursePopup()">✕</button>

                    {{-- Header glow --}}
                    <div class="pc-cpop-glow"
                         :style="`background: radial-gradient(circle at 50% 0%, ${coursePopup.course.color}28 0%, transparent 70%)`"></div>

                    {{-- Badge + icon --}}
                    <div class="pc-cpop-icon-wrap">
                        <div class="pc-cpop-icon"
                             :style="`background: ${coursePopup.course.color}18; border-color: ${coursePopup.course.color}50; box-shadow: 0 0 24px ${coursePopup.course.color}35;`"
                             x-text="coursePopup.course.icon"></div>
                        <div class="pc-cpop-confetti">🎉</div>
                    </div>

                    <div class="pc-cpop-eyebrow">Course Complete</div>
                    <h2 class="pc-cpop-title" x-text="coursePopup.course.title"></h2>

                    {{-- XP badge --}}
                    <div class="pc-cpop-xp" x-show="coursePopup.xp > 0" x-cloak>
                        <span>⚡</span>
                        <span x-text="`+${coursePopup.xp} XP earned`"></span>
                    </div>

                    {{-- Jobs intro --}}
                    <p class="pc-cpop-jobs-intro"
                       x-show="coursePopup.course.jobs_intro" x-cloak
                       x-text="coursePopup.course.jobs_intro"></p>

                    {{-- Jobs unlocked --}}
                    <template x-if="coursePopup.jobs && coursePopup.jobs.length > 0">
                        <div class="pc-cpop-jobs">
                            <div class="pc-cpop-section-label"><x-icon name="briefcase" class="w-3 h-3 inline-block" /> Jobs Progress</div>
                            <template x-for="job in coursePopup.jobs" :key="job.title">
                                <div class="pc-cpop-job-row">
                                    <span class="pc-cpop-job-logo" x-text="job.employer_logo"></span>
                                    <div class="pc-cpop-job-info">
                                        <div class="pc-cpop-job-title" x-text="job.title"></div>
                                        <div class="pc-cpop-job-emp" x-text="job.fully_unlocked ? job.employer_name : job.employer_name + ' · needs more courses'"></div>
                                    </div>
                                    <div class="pc-cpop-job-salary" x-text="job.fully_unlocked ? ('✅ KES ' + (job.salary_kes_month ?? 0).toLocaleString() + '/mo') : '🔒 Locked'"></div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Financial tip --}}
                    <template x-if="coursePopup.course.financial_tip">
                        <div class="pc-cpop-tip">
                            <div class="pc-cpop-tip-label"><x-icon name="bulb" class="w-3 h-3 inline-block" /> Financial Insight</div>
                            <p class="pc-cpop-tip-text" x-text="coursePopup.course.financial_tip"></p>
                        </div>
                    </template>

                    {{-- CTA --}}
                    <div class="pc-cpop-actions">
                        <button class="pc-cpop-btn-jobs" @click="oppTab = 'jobs'; closeCoursePopup()">
                            View Jobs Board →
                        </button>
                        <button class="pc-cpop-btn-close" @click="closeCoursePopup()">
                            Continue Exploring
                        </button>
                    </div>

                </div>
            </div>
        </template>

        {{-- ══════════════════════════════════════════════════════════
             COURSE READER — enroll, study the content, then complete
        ══════════════════════════════════════════════════════════ --}}
        <template x-if="courseReader.show && courseReader.course">
            <div class="pc-cpop-overlay" style="overflow-y:auto;" @click.self="closeCourseReader()">
                <div class="pc-cpop-box" style="margin:auto;text-align:left;">

                    <button class="pc-cpop-close" @click="closeCourseReader()">✕</button>

                    <div class="pc-cpop-glow"
                         :style="`background: radial-gradient(circle at 50% 0%, ${courseReader.course.color}28 0%, transparent 70%)`"></div>

                    {{-- Header --}}
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                        <div style="width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0;"
                             :style="`background: ${courseReader.course.color}18; border:1px solid ${courseReader.course.color}50;`"
                             x-text="courseReader.course.icon"></div>
                        <div style="min-width:0;">
                            <div style="font-size:17px;font-weight:900;color:#fff;line-height:1.25;" x-text="courseReader.course.title"></div>
                            <div style="display:flex;align-items:center;gap:8px;margin-top:4px;flex-wrap:wrap;">
                                <span style="font-size:10px;font-weight:800;color:rgba(255,255,255,.5);" x-text="trackLabel(courseReader.course.career_track)"></span>
                                <span style="font-size:10px;color:rgba(255,255,255,.35);" x-text="`⏱ ${courseReader.course.duration_hours || 2}h`"></span>
                                <span style="font-size:10px;font-weight:800;color:#fbbf24;" x-text="`+${courseReader.course.xp_reward || 50} XP`"></span>
                                <span x-show="courseReader.course.recommended" x-cloak style="font-size:10px;font-weight:900;color:#fbbf24;">⭐ Your path</span>
                            </div>
                        </div>
                    </div>

                    <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.6;margin-bottom:14px;" x-text="courseReader.course.description"></p>

                    {{-- Error --}}
                    <div x-show="courseReader.error" x-cloak
                         style="margin-bottom:12px;padding:9px 12px;border-radius:10px;font-size:12px;font-weight:700;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#f87171;"
                         x-text="courseReader.error"></div>

                    {{-- NOT ENROLLED: intro teaser + enroll CTA --}}
                    <template x-if="courseReader.course.player_status === 'not_enrolled'">
                        <div>
                            <template x-if="courseReader.course.intro_content">
                                <div style="padding:14px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);margin-bottom:12px;">
                                    <div style="font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.4);margin-bottom:8px;"><x-icon name="book" class="w-2.5 h-2.5 inline-block" /> What you'll learn</div>
                                    <div style="font-size:13px;color:rgba(255,255,255,.75);line-height:1.7;white-space:pre-line;" x-text="courseReader.course.intro_content"></div>
                                </div>
                            </template>
                            <template x-if="courseReader.course.outcome">
                                <div style="display:flex;gap:8px;align-items:flex-start;padding:11px 13px;border-radius:12px;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);margin-bottom:14px;">
                                    <span style="flex-shrink:0;">🎯</span>
                                    <span style="font-size:12px;color:rgba(255,255,255,.8);font-weight:600;line-height:1.5;" x-text="courseReader.course.outcome"></span>
                                </div>
                            </template>
                            <button @click="enrollCourse(courseReader.course.id)" :disabled="courseReader.busy"
                                    style="width:100%;padding:13px;border-radius:14px;font-size:13px;font-weight:900;cursor:pointer;border:none;color:#fff;background:linear-gradient(135deg,#4DA8F7,#6366f1);box-shadow:0 4px 18px rgba(77,168,247,.35);">
                                <span x-show="!courseReader.busy"
                                      x-text="courseReader.course.is_free ? '📚 Enroll & Start Learning — Free' : '💳 Enroll · KES ' + (courseReader.course.cost_kes ?? 0).toLocaleString()"></span>
                                <span x-show="courseReader.busy">Enrolling…</span>
                            </button>
                        </div>
                    </template>

                    {{-- ENROLLED: full content + complete CTA at the bottom --}}
                    <template x-if="courseReader.course.player_status === 'enrolled'">
                        <div>
                            <template x-if="courseReader.course.content">
                                <div style="padding:14px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);margin-bottom:12px;">
                                    <div style="font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.4);margin-bottom:8px;"><x-icon name="book" class="w-2.5 h-2.5 inline-block" /> Course Content</div>
                                    <div style="font-size:13px;color:rgba(255,255,255,.78);line-height:1.75;white-space:pre-line;" x-text="courseReader.course.content"></div>
                                </div>
                            </template>
                            <template x-if="!courseReader.course.content && courseReader.course.intro_content">
                                <div style="padding:14px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);margin-bottom:12px;">
                                    <div style="font-size:13px;color:rgba(255,255,255,.78);line-height:1.75;white-space:pre-line;" x-text="courseReader.course.intro_content"></div>
                                </div>
                            </template>
                            <template x-if="courseReader.course.outcome">
                                <div style="display:flex;gap:8px;align-items:flex-start;padding:11px 13px;border-radius:12px;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);margin-bottom:14px;">
                                    <span style="flex-shrink:0;">🎯</span>
                                    <span style="font-size:12px;color:rgba(255,255,255,.8);font-weight:600;line-height:1.5;" x-text="courseReader.course.outcome"></span>
                                </div>
                            </template>
                            <p style="font-size:11px;color:rgba(255,255,255,.4);text-align:center;margin-bottom:10px;">Read everything above? Mark the course complete to earn your XP.</p>
                            <button @click="completeCourse(courseReader.course.id)" :disabled="courseReader.busy"
                                    style="width:100%;padding:13px;border-radius:14px;font-size:13px;font-weight:900;cursor:pointer;border:none;color:#04110B;background:linear-gradient(135deg,#15C77E,#4DA8F7);box-shadow:0 4px 18px rgba(21,199,126,.35);">
                                <span x-show="!courseReader.busy" x-text="`✅ Complete Course & Earn +${courseReader.course.xp_reward || 50} XP`"></span>
                                <span x-show="courseReader.busy">Saving…</span>
                            </button>
                        </div>
                    </template>

                </div>
            </div>
        </template>

        {{-- Backdrop — mobile-only (sidebar is an off-canvas drawer there); tap
             anywhere outside the drawer to close it, same as the Menu sheet. --}}
        <div class="pc-sidebar-backdrop" x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"></div>

        {{-- ── RIGHT SIDEBAR ── --}}
        <div class="pc-sidebar" :class="{ 'pc-sidebar-open': sidebarOpen }">

            {{-- Profile Card --}}
            <div class="pc-card pc-profile-card">
                <div class="pc-player-header">
                    {{-- Avatar with XP ring --}}
                    <div class="pc-avatar-wrap">
                        <svg class="pc-xp-ring-svg" viewBox="0 0 52 52" fill="none">
                            <circle cx="26" cy="26" r="23" stroke="rgba(21,199,126,0.1)" stroke-width="3"/>
                            <circle cx="26" cy="26" r="23" stroke="#15C77E" stroke-width="3"
                                    stroke-dasharray="{{ round($xpPercent * 1.445) }} 144"
                                    stroke-linecap="round" transform="rotate(-90 26 26)"/>
                        </svg>
                        <div class="pc-avatar">
                            @if(auth()->user()->profile_photo)
                                <img src="{{ auth()->user()->profile_photo }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </div>
                    </div>
                    {{-- Name + chapter + XP bar --}}
                    <div class="pc-player-meta">
                        <div class="pc-player-name">{{ explode(' ', auth()->user()->name)[0] }}</div>
                        <div class="pc-chapter-badge" style="background: {{ $chapterColor }}22; color: {{ $chapterColor }}; border-color: {{ $chapterColor }}44; margin-bottom: 4px;">
                            {{ $chapter }}
                        </div>
                        <div class="pc-xp-bar-row">
                            <div class="pc-xp-bar-mini">
                                <div class="pc-xp-bar-mini-fill" style="width: {{ $xpPercent }}%"></div>
                            </div>
                            <span class="pc-xp-pct-label">{{ $xpPercent }}%</span>
                        </div>
                    </div>
                    {{-- Level badge --}}
                    <div class="pc-lv-badge">LV<br>{{ $level }}</div>
                </div>

                {{-- Financial overview with mini bars --}}
                <div class="pc-fin-overview">
                    <div class="pc-fin-title">Financial Overview</div>
                    <div class="pc-fin-row">
                        <span class="pc-fin-name">Balance</span>
                        <div class="pc-fin-bar">
                            <div class="pc-fin-fill" style="width:{{ min(100, max(2, $balance/200000*100)) }}%;background:#15C77E"></div>
                        </div>
                        <span class="pc-fin-val" x-text="'KES '+liveBalance.toLocaleString()" x-cloak>KES {{ number_format($balance) }}</span>
                    </div>
                    <div class="pc-fin-row">
                        <span class="pc-fin-name">Net Worth</span>
                        <div class="pc-fin-bar">
                            <div class="pc-fin-fill" style="width:{{ min(100, max(2, $netWorth/1000000*100)) }}%;background:#4DA8F7"></div>
                        </div>
                        <span class="pc-fin-val">KES {{ number_format($netWorth) }}</span>
                    </div>
                    <div class="pc-fin-row">
                        <span class="pc-fin-name">Credit</span>
                        <div class="pc-fin-bar">
                            <div class="pc-fin-fill" style="width:{{ min(100, max(2, $creditScore/850*100)) }}%;background:{{ $creditScore >= 650 ? '#15C77E' : ($creditScore >= 500 ? '#FFBC00' : '#EF5350') }}"></div>
                        </div>
                        <span class="pc-fin-val" style="color:{{ $creditScore >= 650 ? '#15C77E' : ($creditScore >= 500 ? '#FFBC00' : '#EF5350') }}">{{ $creditScore }}</span>
                    </div>
                </div>
            </div>

            {{-- ── QUEST TRACKER (gameset quests only, pending first) ── --}}
            <div class="pc-card pc-aq-card"
                 x-data="{
                     quests: {{ json_encode(array_values($activeQuests)) }},
                     idx: 0,
                     get current() { return this.quests[this.idx] ?? null; },
                     prev() { this.idx = (this.idx - 1 + this.quests.length) % this.quests.length; },
                     next() { this.idx = (this.idx + 1) % this.quests.length; }
                 }">
                <div class="pc-card-label" style="display:flex;align-items:center;justify-content:space-between;">
                    <span class="inline-flex items-center gap-1"><x-icon name="checklist" class="w-3 h-3" /> ACTIVE QUESTS</span>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <template x-if="quests.length > 0">
                            <span style="font-size:9px;color:var(--pc-gold);font-weight:700;"
                                  x-text="(idx+1) + ' / ' + quests.length"></span>
                        </template>
                        <template x-if="quests.length > 1">
                            <div style="display:flex;gap:3px;">
                                <button @click="prev()"
                                        style="width:22px;height:22px;border-radius:6px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.14);color:#fff;font-size:13px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-weight:700;">‹</button>
                                <button @click="next()"
                                        style="width:22px;height:22px;border-radius:6px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.14);color:#fff;font-size:13px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-weight:700;">›</button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Quest Gate: banked XP is waiting behind unfinished quests --}}
                @if(($questGate['blocked'] ?? false))
                <div style="margin:6px 0 4px;padding:8px 10px;border-radius:10px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.35);">
                    <div style="font-size:10.5px;font-weight:900;color:#fbbf24;">⛰️ Level {{ $questGate['xp_level'] }} is waiting for you!</div>
                    <div style="font-size:10px;color:rgba(251,191,36,0.75);margin-top:2px;line-height:1.45;">
                        You've earned the XP — finish {{ $questGate['remaining'] }} more quest{{ $questGate['remaining'] === 1 ? '' : 's' }} at Level {{ $questGate['gate_level'] }} to unlock it.
                    </div>
                </div>
                @endif

                {{-- Slide dots --}}
                <template x-if="quests.length > 1">
                    <div style="display:flex;gap:4px;margin:4px 0 6px;justify-content:center;">
                        <template x-for="(q, i) in quests" :key="i">
                            <div @click="idx=i"
                                 :style="'width:' + (i===idx?'14':'6') + 'px;height:6px;border-radius:3px;cursor:pointer;transition:all .25s;background:' + (i===idx?'#818cf8':'rgba(255,255,255,0.2)')"></div>
                        </template>
                    </div>
                </template>

                {{-- Current quest --}}
                <template x-if="current">
                    <div class="pc-aq-item" style="margin-top:4px;">
                        <span class="pc-aq-icon" x-html="pqIcon(current.icon, 'w-5 h-5')"></span>
                        <div class="pc-aq-body" style="flex:1;min-width:0;">
                            <div class="pc-aq-title" x-text="current.title"></div>
                            <div class="pc-aq-status"
                                 :style="current.status==='completed'?'color:#10b981;':current.status==='reviewing'?'color:#fbbf24;':''"
                                 x-text="current.status==='completed'?'✓ Completed':current.status==='reviewing'?'⏳ Under Review':'⟳ In Progress'"></div>
                            <template x-if="current.status !== 'completed'">
                                <div style="margin-top:6px;">
                                    <button class="pc-go-btn" style="width:100%;" @click="$dispatch('open-quest-popup', current)">View Quest →</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Empty state --}}
                <template x-if="quests.length === 0">
                    <div style="text-align:center;padding:12px 8px;">
                        <div style="font-size:24px;margin-bottom:6px;">🗺️</div>
                        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.5);">No active quests</div>
                        <div style="font-size:10px;color:rgba(255,255,255,0.3);margin-top:3px;">Explore the city to discover quests</div>
                    </div>
                </template>
            </div>

            {{-- ── HUSTLE TIP CARD (contextual, rotating) ── --}}
            <div class="pc-card pc-tip-card" x-show="tips.length > 0">
                <div class="pc-tip-header">
                    <span class="pc-card-label inline-flex items-center gap-1" style="margin-bottom:0;"><x-icon name="bulb" class="w-3 h-3" /> HUSTLE TIP</span>
                    <div class="pc-tip-nav">
                        <button class="pc-tip-prev" @click="nextTip()" title="Next tip">→</button>
                        <span class="pc-tip-count"
                              x-text="(tipIdx + 1) + ' / ' + tips.length"></span>
                    </div>
                </div>
                <div class="pc-tip-body" x-show="tips[tipIdx]">
                    <div class="pc-tip-icon" x-text="tips[tipIdx].icon"></div>
                    <div class="pc-tip-text" x-text="tips[tipIdx].text"></div>
                </div>
                <div class="pc-tip-dots">
                    <template x-for="(t, i) in tips" :key="i">
                        <div class="pc-tip-dot" :class="{ active: i === tipIdx }"></div>
                    </template>
                </div>
            </div>

            {{-- Active Investments --}}
            <div class="pc-card">
                <div class="pc-card-label" style="display:flex;align-items:center;justify-content:space-between;">
                    <span class="inline-flex items-center gap-1"><x-icon name="trend-up" class="w-3 h-3" /> ACTIVE INVESTMENTS</span>
                    <a href="{{ route('portfolio') }}" style="font-size:10px;color:#818cf8;font-weight:700;text-decoration:none;">View All →</a>
                </div>

                @if(!empty($activeInvestments))
                <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;scrollbar-width:none;-ms-overflow-style:none;" class="pc-inv-scroll">
                    @foreach($activeInvestments as $inv)
                    <div style="flex:0 0 130px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:10px 10px 8px;min-width:130px;">
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                            <span style="flex-shrink:0;"><x-icon :name="$inv['icon']" class="w-4 h-4" /></span>
                            <div style="font-size:11px;font-weight:700;color:#e2e8f0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">{{ Str::limit($inv['name'], 14) }}</div>
                        </div>
                        {{-- Value progress bar: current vs purchase --}}
                        @php
                            $pct = $inv['purchase_price'] > 0 ? min(100, round(($inv['current_value'] / $inv['purchase_price']) * 100)) : 100;
                            $gainPct = $inv['gain_pct'];
                            $barColor = $gainPct >= 0 ? '#10b981' : '#ef4444';
                        @endphp
                        <div style="font-size:9px;color:#6b7280;margin-bottom:3px;">Value vs Cost</div>
                        <div style="height:5px;border-radius:3px;background:rgba(255,255,255,0.08);overflow:hidden;margin-bottom:6px;">
                            <div style="height:100%;border-radius:3px;background:{{ $barColor }};width:{{ $pct }}%;transition:width .4s;"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:10px;font-weight:700;color:{{ $barColor }};">{{ $gainPct >= 0 ? '+' : '' }}{{ $gainPct }}%</span>
                            @if($inv['monthly_income'] > 0)
                            <span style="font-size:9px;color:#34d399;">+{{ number_format($inv['monthly_income']) }}/mo</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="text-align:center;padding:14px 8px;">
                    <div style="font-size:24px;margin-bottom:6px;">💼</div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);line-height:1.5;">No active investments yet.</div>
                    <a href="{{ route('marketplace') }}" class="pc-go-btn" style="display:block;margin-top:8px;text-decoration:none;text-align:center;">Browse Market →</a>
                </div>
                @endif
            </div>


        </div>{{-- /pc-sidebar --}}

    </div>{{-- /pc-main --}}

    {{-- ── QUEST DETAIL POPUP (fixed, truly centred — outside overflow:hidden ancestor) ── --}}
    <div x-show="questPopup.show" x-cloak
         style="position:fixed;inset:0;z-index:9000;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(5,3,20,0.88);backdrop-filter:blur(14px);overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;"
         @click.self="questPopup.show=false">
        <div style="background:linear-gradient(155deg,#110e2a,#0c0a1e);border:1px solid rgba(99,102,241,0.28);border-radius:18px;width:100%;max-width:440px;padding:24px 20px 20px;position:relative;margin:auto;">
            <button @click="questPopup.show=false"
                    style="position:absolute;top:12px;right:14px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.6);font-size:14px;display:flex;align-items:center;justify-content:center;cursor:pointer;line-height:1;">✕</button>

            <div style="text-align:center;margin-bottom:16px;">
                <template x-if="questPopup.image">
                    <img :src="questPopup.image" :alt="questPopup.title"
                         style="width:72px;height:72px;border-radius:14px;object-fit:cover;margin:0 auto 8px;border:2px solid rgba(124,58,237,0.4);">
                </template>
                <template x-if="!questPopup.image">
                    <div x-html="pqIcon(questPopup.icon, 'w-11 h-11')" style="margin-bottom:8px;display:flex;justify-content:center;"></div>
                </template>
                <div x-text="questPopup.title" style="font-size:17px;font-weight:800;color:#e2e8f0;line-height:1.3;"></div>
                <div x-show="questPopup.status === 'reviewing'"
                     style="margin-top:6px;display:inline-block;padding:2px 10px;border-radius:20px;background:rgba(251,191,36,0.12);border:1px solid rgba(251,191,36,0.3);color:#fbbf24;font-size:11px;font-weight:700;">⏳ UNDER REVIEW</div>
                <div x-show="questPopup.status === 'in_progress' || !questPopup.status"
                     style="margin-top:6px;display:inline-block;padding:2px 10px;border-radius:20px;background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.3);color:#a5b4fc;font-size:11px;font-weight:700;">⟳ IN PROGRESS</div>
            </div>

            <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:12px 14px;margin-bottom:14px;">
                <div style="font-size:10px;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">About this Quest</div>
                <div x-text="questPopup.description" style="font-size:13px;color:#c4c9d6;line-height:1.55;"></div>
            </div>

            <template x-if="questPopup.instructions">
                <div style="background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.18);border-radius:10px;padding:12px 14px;margin-bottom:14px;">
                    <div style="font-size:10px;color:#10b981;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;"><x-icon name="bulb" class="w-2.5 h-2.5 inline-block" /> How to Complete</div>
                    <div x-text="questPopup.instructions" style="font-size:13px;color:#a7f3d0;line-height:1.55;"></div>
                </div>
            </template>

            <template x-if="questPopup.lesson">
                <div style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.18);border-radius:10px;padding:12px 14px;margin-bottom:14px;">
                    <div style="font-size:10px;color:#818cf8;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;"><x-icon name="graduation" class="w-2.5 h-2.5 inline-block" /> Lesson</div>
                    <div x-text="questPopup.lesson" style="font-size:13px;color:#c7d2fe;line-height:1.55;font-style:italic;"></div>
                </div>
            </template>

            <template x-if="questPopup.trigger_label">
                <div style="background:rgba(251,191,36,0.06);border:1px solid rgba(251,191,36,0.18);border-radius:10px;padding:10px 14px;margin-bottom:14px;display:flex;gap:10px;align-items:flex-start;">
                    <span style="font-size:18px;flex-shrink:0;">🎯</span>
                    <div>
                        <div style="font-size:10px;color:#fbbf24;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Auto-Detected Trigger</div>
                        <div x-text="questPopup.trigger_label" style="font-size:12px;color:#fde68a;line-height:1.5;"></div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:3px;">This quest completes automatically when you take the required action.</div>
                    </div>
                </div>
            </template>

            <div style="display:flex;gap:8px;margin-bottom:16px;">
                <div style="flex:1;background:rgba(168,85,247,0.1);border:1px solid rgba(168,85,247,0.2);border-radius:10px;padding:10px;text-align:center;">
                    <div style="font-size:10px;color:#a855f7;font-weight:700;text-transform:uppercase;margin-bottom:4px;">XP Reward</div>
                    <div x-text="'+' + questPopup.xp_reward + ' XP'" style="font-size:15px;font-weight:800;color:#c084fc;"></div>
                </div>
                <template x-if="questPopup.kes_reward > 0">
                    <div style="flex:1;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:10px;padding:10px;text-align:center;">
                        <div style="font-size:10px;color:#10b981;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Ksh Reward</div>
                        <div x-text="'Ksh ' + questPopup.kes_reward.toLocaleString()" style="font-size:15px;font-weight:800;color:#34d399;"></div>
                    </div>
                </template>
            </div>

            <button @click="questPopup.show=false"
                    style="width:100%;padding:11px;border-radius:10px;background:linear-gradient(135deg,rgba(99,102,241,0.25),rgba(139,92,246,0.2));border:1px solid rgba(99,102,241,0.35);color:#a5b4fc;font-size:13px;font-weight:700;cursor:pointer;letter-spacing:0.03em;">
                Close
            </button>
        </div>
    </div>

    {{-- ── DISTRICT PANEL (bottom sheet) ── --}}
    <div class="pc-panel" id="pc-panel"
         x-show="panelOpen"
         x-transition:enter="pc-panel-enter"
         x-transition:enter-start="pc-panel-enter-start"
         x-transition:enter-end="pc-panel-enter-end"
         x-transition:leave="pc-panel-enter"
         x-transition:leave-start="pc-panel-enter-end"
         x-transition:leave-end="pc-panel-enter-start"
         :class="{ 'pc-panel-dragging': panelDragging, 'pc-panel-snapping': panelSnapping }"
         :style="panelDragY > 0 ? ('transform:translateY(' + panelDragY + 'px)') : ''"
         @touchstart="panelTouchStart($event)"
         @touchmove="panelTouchMove($event)"
         @touchend="panelTouchEnd($event)"
         x-cloak>

        {{-- Drag handle --}}
        <div class="pc-panel-handle"></div>

        {{-- Panel header --}}
        <div class="pc-panel-header">
            <div class="pc-panel-title" style="display:flex;align-items:center;gap:8px;">
                <span x-show="district" x-html="pqIcon(district ? district.icon : '', 'w-5 h-5')" style="display:inline-flex;flex-shrink:0;"></span>
                <span x-text="district ? district.name : ''"></span>
            </div>
            <button class="pc-panel-close" @click="closePanel()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Panel content --}}
        <div class="pc-panel-body" x-show="!loading">

            {{-- Locked district panel (generic — estates/car-yard have their own) --}}
            <template x-if="district && district.status === 'locked' && district.slug !== 'estates' && district.slug !== 'car-yard'">
                <div style="text-align:center;padding:24px 16px 16px;">
                    <div style="font-size:44px;margin-bottom:10px;">🔒</div>
                    <div style="font-size:15px;font-weight:800;color:#e2e8f0;margin-bottom:8px;" x-text="district.name + ' — Locked'"></div>
                    <div x-text="district.unlock_hint || 'Keep progressing to unlock this district.'"
                         style="font-size:13px;color:#9ca3af;line-height:1.6;"></div>
                    <div style="margin-top:18px;display:flex;gap:8px;justify-content:center;">
                        <button @click="closePanel()"
                                style="padding:9px 18px;border-radius:10px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);color:#9ca3af;font-size:12px;font-weight:700;cursor:pointer;">
                            Back to Map
                        </button>
                    </div>
                </div>
            </template>

            {{-- Standard active district (marketplace only — other districts have dedicated templates) --}}
            <template x-if="district && district.status === 'active' && district.slug !== 'opportunity-hub' && district.slug !== 'fun-world' && district.slug !== 'community' && district.slug !== 'estates' && district.slug !== 'car-yard' && district.slug !== 'bank' && district.slug !== 'savings' && district.slug !== 'workplace' && district.slug !== 'quests' && district.slug !== 'champions-court'">
                <div>
                    <p class="pc-panel-desc" x-text="district.description"></p>
                    <div class="pc-panel-actions">
                        <template x-for="action in (district.actions || [])" :key="action.label">
                            <a :href="action.url"
                               :class="'pc-action-btn pc-action-' + action.style"
                               x-text="action.label"></a>
                        </template>
                    </div>

                    {{-- A taste of what's actually for sale — full browsing on /marketplace --}}
                    <template x-if="district.slug === 'marketplace' && district.featured_assets && district.featured_assets.length > 0">
                        <div style="margin-top:14px;">
                            <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">
                                <x-icon name="store" class="w-2.5 h-2.5 inline-block" /> A Few Things on Sale
                            </div>
                            <div class="pc-card-grid" style="grid-template-columns:repeat(auto-fill,minmax(150px,1fr));">
                                <template x-for="a in district.featured_assets" :key="a.id">
                                    <a :href="'/marketplace?highlight=' + a.id" style="display:block;border-radius:12px;border:1px solid rgba(255,255,255,0.08);padding:10px;background:rgba(255,255,255,0.03);text-decoration:none;transition:border-color .15s;">
                                        <div style="width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,0.05);display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:6px;">
                                            <template x-if="a.image_url"><img :src="a.image_url" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                            <template x-if="!a.image_url"><span x-html="pqIcon(a.icon, 'w-4 h-4')" style="color:#9ca3af;"></span></template>
                                        </div>
                                        <div style="font-size:11px;font-weight:800;color:#e5e7eb;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="a.name"></div>
                                        <div style="font-size:10px;color:#67e8f9;font-weight:700;margin-top:2px;" x-text="'KES ' + a.base_price.toLocaleString()"></div>
                                        <div x-show="a.monthly_income > 0" style="font-size:9px;color:#34d399;margin-top:1px;" x-text="'+KES ' + (a.monthly_income ?? 0).toLocaleString() + '/mo'"></div>
                                    </a>
                                </template>
                            </div>
                            <a href="/marketplace" style="display:block;text-align:center;font-size:11px;font-weight:700;color:#67e8f9;margin-top:10px;">See all assets in Marketplace →</a>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ══════════════════════════════════════════
                 EQUITY SQUARE (bank slug) — Investment Deals
            ══════════════════════════════════════════ --}}
            <template x-if="district && district.slug === 'bank'">
                <div x-data="equitySquare()">

                    {{-- Identity header --}}
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:10px 12px;border-radius:12px;background:rgba(53,195,240,.07);border:1px solid rgba(53,195,240,.22);">
                        <div style="flex-shrink:0;color:#35C3F0;"><x-icon name="trend-up" class="w-7 h-7" /></div>
                        <div>
                            <div style="font-size:12px;font-weight:900;color:#35C3F0;letter-spacing:.04em;text-transform:uppercase;">Equity Square</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.45);line-height:1.4;margin-top:2px;">Risk-based investment deals — put your money to work</div>
                        </div>
                    </div>

                    {{-- Available balance (investable) --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:10px;background:rgba(53,195,240,.05);border:1px solid rgba(53,195,240,.15);margin-bottom:12px;">
                        <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);">Available to Invest</span>
                        <span style="font-size:14px;font-weight:900;color:#35C3F0;" x-text="'KES ' + (district.balance ?? 0).toLocaleString()"></span>
                    </div>

                    {{-- Trade celebration card + toast — kept up top, above the tabs,
                         so a buy/sell/invest confirmation is never buried below a long
                         list or hidden behind whichever tab happens to be open. --}}
                    <div x-show="shareTradeResult" x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-90 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="share-trade-card" :class="shareTradeResult && shareTradeResult.ok ? 'ok' : 'bad'" style="margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="stc-icon w-6 h-6" x-html="pqIcon(shareTradeResult ? shareTradeResult.icon : 'trend-up', 'w-6 h-6')"></span>
                            <span style="font-size:13px;font-weight:800;color:#f9fafb;flex:1;" x-text="shareTradeResult ? shareTradeResult.message : ''"></span>
                        </div>
                        <div x-show="shareTradeResult && shareTradeResult.basics" class="stc-basics">
                            <x-icon name="book" class="w-3.5 h-3.5 inline-block" /> <strong>First trade!</strong> <span x-text="shareTradeResult ? shareTradeResult.basics : ''"></span>
                        </div>
                        <div x-show="shareTradeResult && shareTradeResult.education" class="stc-edu">
                            💡 <span x-text="shareTradeResult ? shareTradeResult.education : ''"></span>
                        </div>
                        <div class="stc-bar"><div class="stc-bar-fill"></div></div>
                    </div>

                    <div x-show="bankMsg" x-cloak
                         :style="bankMsgOk ? 'background:rgba(53,195,240,0.1);border-color:rgba(53,195,240,0.3);color:#67e8f9;' : 'background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);color:#f87171;'"
                         style="margin-bottom:12px;padding:8px 12px;border-radius:10px;border:1px solid;font-size:12px;font-weight:600;"
                         x-text="bankMsg"></div>

                    {{-- Tab navigation --}}
                    <div style="display:flex;gap:4px;margin:0 0 12px;background:rgba(255,255,255,0.04);border-radius:12px;padding:4px;">
                        <button @click="eqTab='deals'"
                                :style="eqTab==='deals' ? 'background:rgba(53,195,240,0.18);color:#67e8f9;font-weight:700;' : 'color:#9ca3af;'"
                                style="flex:1;padding:6px 4px;border-radius:8px;font-size:11px;border:none;cursor:pointer;transition:all .15s;">
                            <x-icon name="target" class="w-3 h-3 inline-block" /> Deals
                        </button>
                        <button @click="eqTab='my-shares'"
                                :style="eqTab==='my-shares' ? 'background:rgba(53,195,240,0.18);color:#67e8f9;font-weight:700;' : 'color:#9ca3af;'"
                                style="flex:1;padding:6px 4px;border-radius:8px;font-size:11px;border:none;cursor:pointer;transition:all .15s;">
                            <x-icon name="bar-chart" class="w-3 h-3 inline-block" /> My Shares
                        </button>
                        <button @click="eqTab='market'"
                                :style="eqTab==='market' ? 'background:rgba(53,195,240,0.18);color:#67e8f9;font-weight:700;' : 'color:#9ca3af;'"
                                style="flex:1;padding:6px 4px;border-radius:8px;font-size:11px;border:none;cursor:pointer;transition:all .15s;">
                            <x-icon name="trend-up" class="w-3 h-3 inline-block" /> Market
                        </button>
                    </div>

                    {{-- TAB: Deals --}}
                    <div x-show="eqTab==='deals'">

                    {{-- Active positions --}}
                    <template x-if="district.my_deals && district.my_deals.filter(d=>d.status==='pending').length > 0">
                        <div style="margin-bottom:12px;">
                            <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;"><x-icon name="clock" class="w-2.5 h-2.5 inline-block" /> Your Active Positions</div>
                            <template x-for="md in district.my_deals.filter(d=>d.status==='pending')" :key="md.id">
                                <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:rgba(53,195,240,.04);border:1px solid rgba(53,195,240,.15);margin-bottom:4px;">
                                    <span x-html="pqIcon(md.icon, 'w-4 h-4')" style="display:inline-flex;"></span>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:12px;font-weight:700;color:#e5e7eb;" x-text="md.title"></div>
                                        <div style="font-size:11px;color:#6b7280;" x-text="'KES ' + md.amount.toLocaleString() + ' · resolves at tick ' + md.resolve_at"></div>
                                    </div>
                                    <span style="font-size:10px;font-weight:800;padding:2px 7px;border-radius:20px;background:rgba(251,191,36,0.15);color:#fbbf24;border:1px solid rgba(251,191,36,0.25);">ACTIVE</span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Recently resolved --}}
                    <template x-if="district.my_deals && district.my_deals.filter(d=>d.status!=='pending').length > 0">
                        <div style="margin-bottom:12px;">
                            <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;"><x-icon name="clipboard" class="w-2.5 h-2.5 inline-block" /> Recent Results</div>
                            <template x-for="md in district.my_deals.filter(d=>d.status!=='pending').slice(0,3)" :key="md.id">
                                <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,.06);margin-bottom:4px;">
                                    <span x-html="pqIcon(md.icon, 'w-4 h-4')" style="display:inline-flex;"></span>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:12px;font-weight:700;color:#e5e7eb;" x-text="md.title"></div>
                                    </div>
                                    <span :style="md.profit_loss >= 0 ? 'color:#34d399' : 'color:#f87171'"
                                          style="font-size:12px;font-weight:800;"
                                          x-text="(md.profit_loss >= 0 ? '+' : '') + 'KES ' + Math.abs(md.profit_loss).toLocaleString()"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Available deals --}}
                    <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;"><x-icon name="target" class="w-2.5 h-2.5 inline-block" /> Open Deals</div>
                    <template x-if="!district.deals || district.deals.length === 0">
                        <div style="text-align:center;padding:20px;color:#6b7280;font-size:13px;">No deals available right now. Check back soon.</div>
                    </template>
                    <div class="pc-card-grid">
                    <template x-for="deal in (district.deals ?? [])" :key="deal.id">
                        <div style="border-radius:12px;border:1px solid rgba(53,195,240,.15);padding:10px 12px;margin-bottom:8px;background:rgba(53,195,240,.03);">
                            <div style="display:flex;align-items:flex-start;gap:10px;">
                                <span x-html="pqIcon(deal.icon, 'w-5 h-5')" style="flex-shrink:0;display:inline-flex;"></span>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:800;color:#f9fafb;" x-text="deal.title"></div>
                                    <div style="font-size:11px;color:#9ca3af;margin-top:2px;" x-text="deal.description"></div>
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;">
                                        <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;background:rgba(16,185,129,0.12);color:#34d399;border:1px solid rgba(16,185,129,0.25);"
                                              x-text="'Return: ' + deal.min_return_pct + '–' + deal.max_return_pct + '%'"></span>
                                        <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;background:rgba(251,191,36,0.12);color:#fbbf24;border:1px solid rgba(251,191,36,0.25);"
                                              x-text="Math.round(deal.success_probability * 100) + '% win chance'"></span>
                                        <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;background:rgba(53,195,240,0.1);color:#67e8f9;border:1px solid rgba(53,195,240,0.2);"
                                              x-text="deal.maturity_ticks + ' day(s)'"></span>
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,0.06);">
                                <span style="font-size:13px;font-weight:800;color:#f9fafb;" x-text="'KES ' + deal.cost.toLocaleString()"></span>
                                <button @click="enterDeal(deal, district)"
                                        :disabled="dealLoading || (district.balance ?? 0) < deal.cost"
                                        style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:800;background:linear-gradient(135deg,#0891b2,#0e7490);color:#fff;border:none;cursor:pointer;transition:opacity .15s;"
                                        :style="(dealLoading || (district.balance ?? 0) < deal.cost) ? 'opacity:.4;cursor:not-allowed;' : ''">
                                    Enter Deal →
                                </button>
                            </div>
                        </div>
                    </template>
                    </div>

                    </div>
                    {{-- /TAB: Deals --}}

                    {{-- TAB: My Shares — quick progress glance, no need to open Portfolio --}}
                    <div x-show="eqTab==='my-shares'">

                        {{-- How it works — always visible so the mechanics are never a mystery --}}
                        <div style="border-radius:12px;padding:10px 12px;margin-bottom:12px;background:rgba(53,195,240,.05);border:1px solid rgba(53,195,240,.15);">
                            <div style="font-size:11px;font-weight:800;color:#67e8f9;margin-bottom:4px;">📚 How shares work</div>
                            <div style="font-size:10.5px;color:#9ca3af;line-height:1.5;">
                                Share prices move up and down on their own — nobody controls them. Try to <strong style="color:#e5e7eb;">buy low, sell high</strong>.
                                Buying costs a little more than the price shown, and selling gets a little less — that small gap is normal. Shares you own add to your net worth right away, even before you sell.
                            </div>
                        </div>

                        <template x-if="district.my_shares && district.my_shares.length > 0">
                            <div style="margin-bottom:12px;">
                                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;gap:8px;">
                                    <span style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;">💼 My Holdings</span>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-size:11px;font-weight:800;color:#e5e7eb;"
                                             x-text="'Worth KES ' + portfolioTotals(district.my_shares).value.toLocaleString() + ' today'"></div>
                                        <div style="font-size:9.5px;font-weight:700;" :style="portfolioTotals(district.my_shares).gain_loss >= 0 ? 'color:#34d399' : 'color:#f87171'"
                                             x-text="(portfolioTotals(district.my_shares).gain_loss >= 0 ? '+' : '') + 'KES ' + portfolioTotals(district.my_shares).gain_loss.toLocaleString() + ' vs. what you paid'"></div>
                                    </div>
                                </div>
                                <div style="font-size:9.5px;color:#6b7280;margin-top:-4px;margin-bottom:8px;">
                                    What you'd gain or lose if you sold right now — it moves with the price, so red today can turn green later.
                                </div>
                                <div class="pc-card-grid">
                                <template x-for="h in district.my_shares" :key="h.share_id">
                                    <div class="share-card">
                                        <div class="share-card-top">
                                            <div class="share-icon-badge" style="background:rgba(53,195,240,.12);border-color:rgba(53,195,240,.32);">
                                                <template x-if="h.image_url"><img :src="h.image_url" alt=""></template>
                                                <template x-if="!h.image_url"><span x-html="pqIcon(h.icon, 'w-6 h-6')"></span></template>
                                            </div>
                                            <div class="share-card-info">
                                                <div class="share-name" x-text="h.symbol + ' · ' + h.quantity + ' shares'"></div>
                                                <div class="share-card-tags">
                                                    <span class="share-tag" x-text="'Avg KES ' + h.avg_cost.toLocaleString()"></span>
                                                    <span class="share-tag" x-text="'Now KES ' + h.price.toLocaleString()"></span>
                                                </div>
                                            </div>
                                            <div class="share-gain-pill">
                                                <div class="share-gain-val" :style="h.gain_loss >= 0 ? 'color:#34d399' : 'color:#f87171'"
                                                     x-text="(h.gain_loss >= 0 ? '+' : '') + 'KES ' + h.gain_loss.toLocaleString()"></div>
                                                <div class="share-gain-pct" x-text="(h.gain_loss_pct >= 0 ? '+' : '') + h.gain_loss_pct + '%'"></div>
                                            </div>
                                        </div>
                                        <div class="share-card-mid">
                                            <span class="share-trend-label">Recent trend</span>
                                            <div class="share-candles">
                                                <template x-for="(c, idx) in candles(h.history)" :key="idx">
                                                    <div style="position:relative;width:8px;height:100%;">
                                                        <div :style="'position:absolute;left:50%;top:' + c.wickTop + '%;height:' + c.wickHeight + '%;width:1px;background:' + c.color + ';transform:translateX(-50%);'"></div>
                                                        <div :style="'position:absolute;left:0;top:' + c.bodyTop + '%;height:' + c.bodyHeight + '%;width:100%;background:' + c.color + ';border-radius:1.5px;'"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="share-card-actions">
                                            <input type="number" x-model.number="shareQty[h.share_id]" min="1" :max="h.quantity"
                                                   :placeholder="'up to ' + h.quantity" class="share-qty-input">
                                            <button @click="sellShare(h, district)" :disabled="shareLoading" class="share-sell-btn">Sell →</button>
                                        </div>
                                        <div class="share-estimate">
                                            You'll receive ≈ <strong x-text="'KES ' + ((shareQty[h.share_id] || h.quantity) * h.sell_price).toLocaleString(undefined, {maximumFractionDigits: 0})"></strong>
                                            for <span x-text="shareQty[h.share_id] || h.quantity"></span> share<span x-show="(shareQty[h.share_id] || h.quantity) !== 1">s</span>
                                        </div>
                                    </div>
                                </template>
                                </div>
                                <a href="/portfolio" style="display:block;text-align:center;font-size:11px;font-weight:700;color:#67e8f9;margin-top:4px;">See full details in Portfolio →</a>
                            </div>
                        </template>
                        <template x-if="!district.my_shares || district.my_shares.length === 0">
                            <div style="text-align:center;padding:20px;color:#6b7280;font-size:13px;">
                                You don't own any shares yet.
                                <button @click="eqTab='market'" style="display:block;margin:8px auto 0;font-size:12px;font-weight:800;color:#67e8f9;background:none;border:none;cursor:pointer;">Browse the Market →</button>
                            </div>
                        </template>
                    </div>

                    {{-- TAB: Market — browse & buy --}}
                    <div x-show="eqTab==='market'">

                        {{-- Today's movers --}}
                        <template x-if="district.top_gainer || district.top_loser">
                            <div style="display:flex;gap:6px;margin-bottom:10px;">
                                <template x-if="district.top_gainer">
                                    <div style="flex:1;border-radius:10px;padding:6px 9px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);font-size:11px;font-weight:700;color:#34d399;">
                                        🔥 <span class="inline-flex items-center gap-1"><span class="w-3 h-3" x-html="pqIcon(district.top_gainer.icon, 'w-3 h-3')"></span> <span x-text="district.top_gainer.symbol"></span></span>
                                        <span x-text="'↑' + district.top_gainer.change_pct + '%'"></span>
                                    </div>
                                </template>
                                <template x-if="district.top_loser">
                                    <div style="flex:1;border-radius:10px;padding:6px 9px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);font-size:11px;font-weight:700;color:#f87171;">
                                        🥶 <span class="inline-flex items-center gap-1"><span class="w-3 h-3" x-html="pqIcon(district.top_loser.icon, 'w-3 h-3')"></span> <span x-text="district.top_loser.symbol"></span></span>
                                        <span x-text="'↓' + Math.abs(district.top_loser.change_pct) + '%'"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">📈 Market — Buy Low, Sell High</div>
                        <div style="font-size:10px;color:#6b7280;margin-bottom:8px;">Each mini chart is 4 candles of recent price movement — green candle = that period ended up, red = ended down. Buy executes slightly above and sell slightly below the price shown; that gap is the spread.</div>
                        <template x-if="!district.shares || district.shares.length === 0">
                            <div style="text-align:center;padding:20px;color:#6b7280;font-size:13px;">No shares listed right now. Check back soon.</div>
                        </template>
                        <div class="pc-card-grid">
                        <template x-for="s in (district.shares ?? [])" :key="s.id">
                            <div class="share-card">
                                <div class="share-card-top">
                                    <div class="share-icon-badge" :style="'background:' + s.risk_color + '18;border-color:' + s.risk_color + '40;'">
                                        <template x-if="s.image_url"><img :src="s.image_url" alt=""></template>
                                        <template x-if="!s.image_url"><span x-html="pqIcon(s.icon, 'w-6 h-6')"></span></template>
                                    </div>
                                    <div class="share-card-info">
                                        <div class="share-name" x-text="s.name + ' (' + s.symbol + ')'"></div>
                                        <div class="share-card-tags">
                                            <span class="share-tag" x-text="s.sector"></span>
                                            <span class="share-tag" :style="'color:' + s.risk_color + ';border-color:' + s.risk_color + '40;background:' + s.risk_color + '14;'" x-text="s.risk_label"></span>
                                        </div>
                                    </div>
                                    <div class="share-price-block">
                                        <div class="share-price" x-text="'KES ' + s.price.toLocaleString()"></div>
                                        <div class="share-change-chip" :class="s.direction === 'up' ? 'up' : (s.direction === 'down' ? 'down' : 'flat')"
                                             x-text="(s.direction === 'up' ? '↑' : (s.direction === 'down' ? '↓' : '—')) + ' ' + Math.abs(s.change_pct) + '%'"></div>
                                    </div>
                                </div>
                                <div class="share-card-mid">
                                    <span class="share-trend-label">Recent trend</span>
                                    <div class="share-candles">
                                        <template x-for="(c, idx) in candles(s.history)" :key="idx">
                                            <div style="position:relative;width:8px;height:100%;">
                                                <div :style="'position:absolute;left:50%;top:' + c.wickTop + '%;height:' + c.wickHeight + '%;width:1px;background:' + c.color + ';transform:translateX(-50%);'"></div>
                                                <div :style="'position:absolute;left:0;top:' + c.bodyTop + '%;height:' + c.bodyHeight + '%;width:100%;background:' + c.color + ';border-radius:1.5px;'"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="s.event_reason">
                                    <div class="share-event" x-text="s.event_reason"></div>
                                </template>
                                <div class="share-card-actions">
                                    <input type="number" x-model.number="shareQty[s.id]" min="1" placeholder="Qty" class="share-qty-input">
                                    <button @click="buyShare(s, district)" :disabled="shareLoading" class="share-buy-btn">Buy →</button>
                                </div>
                                <div class="share-estimate">
                                    Costs ≈ <strong x-text="'KES ' + ((shareQty[s.id] || 1) * s.buy_price).toLocaleString(undefined, {maximumFractionDigits: 0})"></strong>
                                    for <span x-text="shareQty[s.id] || 1"></span> share<span x-show="(shareQty[s.id] || 1) !== 1">s</span>
                                </div>
                            </div>
                        </template>
                        </div>

                    </div>
                    {{-- /TAB: Market --}}

                    <div class="pc-panel-actions" style="margin-top:14px;">
                        <a href="/portfolio" class="pc-action-btn pc-action-primary"><x-icon name="bar-chart" class="w-3.5 h-3.5 inline-block" /> My Portfolio</a>
                    </div>
                </div>
            </template>

            {{-- ══════════════════════════════════════════
                 BANK & SAVINGS (savings slug) — Everyday Banking
            ══════════════════════════════════════════ --}}
            <template x-if="district && district.slug === 'savings'">
                <div x-data="equitySquare()">

                    {{-- Identity header --}}
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:10px 12px;border-radius:12px;background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.22);">
                        <div style="flex-shrink:0;color:#F59E0B;"><x-icon name="bank" class="w-7 h-7" /></div>
                        <div>
                            <div style="font-size:12px;font-weight:900;color:#F59E0B;letter-spacing:.04em;text-transform:uppercase;">Bank &amp; Savings</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.45);line-height:1.4;margin-top:2px;">Savings, loans, credit score &amp; account management</div>
                        </div>
                    </div>

                    {{-- Credit Score Gauge --}}
                    <div class="pc-eq-credit-card">
                        <div class="pc-eq-credit-header">
                            <span class="pc-eq-credit-label">Credit Score</span>
                            <span class="pc-eq-credit-badge"
                                  :style="'background:' + (district.credit_color ?? '#F59E0B') + '22; color:' + (district.credit_color ?? '#F59E0B') + '; border-color:' + (district.credit_color ?? '#F59E0B') + '44;'"
                                  x-text="district.credit_label ?? 'Fair'"></span>
                        </div>
                        <div class="pc-eq-score-row">
                            <div class="pc-eq-score-num" x-text="district.credit_score ?? 500"
                                 :style="'color:' + (district.credit_color ?? '#F59E0B')"></div>
                            <div class="pc-eq-score-track">
                                <div class="pc-eq-score-fill"
                                     :style="'width:' + Math.round(((district.credit_score ?? 500) / 850) * 100) + '%; background:' + (district.credit_color ?? '#F59E0B')"></div>
                            </div>
                            <span class="pc-eq-score-max">/ 850</span>
                        </div>
                        <div class="pc-eq-balance-row">
                            <span>Balance</span>
                            <strong x-text="'KES ' + (district.balance ?? 0).toLocaleString()"></strong>
                        </div>
                    </div>

                    {{-- Tab navigation --}}
                    <div style="display:flex;gap:4px;margin:12px 0 0;background:rgba(255,255,255,0.04);border-radius:12px;padding:4px;">
                        <button @click="bankTab='savings'"
                                :style="bankTab==='savings' ? 'background:rgba(245,158,11,0.18);color:#fbbf24;font-weight:700;' : 'color:#9ca3af;'"
                                style="flex:1;padding:6px 4px;border-radius:8px;font-size:11px;border:none;cursor:pointer;transition:all .15s;">
                            <x-icon name="coin" class="w-3 h-3 inline-block" /> Savings
                        </button>
                        <button @click="bankTab='loans'"
                                :style="bankTab==='loans' ? 'background:rgba(59,130,246,0.18);color:#93c5fd;font-weight:700;' : 'color:#9ca3af;'"
                                style="flex:1;padding:6px 4px;border-radius:8px;font-size:11px;border:none;cursor:pointer;transition:all .15s;">
                            <x-icon name="bank" class="w-3 h-3 inline-block" /> Loans
                        </button>
                        <button @click="bankTab='account'"
                                :style="bankTab==='account' ? 'background:rgba(245,158,11,0.12);color:#fbbf24;font-weight:700;' : 'color:#9ca3af;'"
                                style="flex:1;padding:6px 4px;border-radius:8px;font-size:11px;border:none;cursor:pointer;transition:all .15s;">
                            <x-icon name="user" class="w-3 h-3 inline-block" /> Account
                        </button>
                    </div>

                    {{-- TAB: Savings --}}
                    <div x-show="bankTab==='savings'" style="margin-top:10px;">
                        <template x-if="district.savings_schemes && district.savings_schemes.length > 0">
                            <div class="pc-eq-schemes">
                                <div class="pc-eq-schemes-label"><x-icon name="coin" class="w-3 h-3 inline-block" /> My Savings Schemes</div>
                                <template x-for="scheme in district.savings_schemes" :key="scheme.name">
                                    <div class="pc-eq-scheme-row">
                                        <span x-text="scheme.emoji" style="font-size:16px;"></span>
                                        <div class="pc-eq-scheme-body">
                                            <div class="pc-eq-scheme-name" x-text="scheme.name"></div>
                                            <div class="pc-eq-scheme-track">
                                                <div class="pc-eq-scheme-fill" :style="'width:' + scheme.progress_pct + '%'"></div>
                                            </div>
                                        </div>
                                        <span class="pc-eq-scheme-pct" x-text="scheme.progress_pct + '%'"></span>
                                    </div>
                                </template>
                                <div class="pc-eq-total-row">
                                    <span>Total Saved</span>
                                    <strong style="color:#10b981;" x-text="'KES ' + (district.total_savings ?? 0).toLocaleString()"></strong>
                                </div>
                            </div>
                        </template>
                        <template x-if="!district.savings_schemes || district.savings_schemes.length === 0">
                            <div class="pc-eq-no-schemes">
                                <span style="display:inline-flex;" x-html="pqIcon('bank', 'w-6 h-6')"></span>
                                <div>No savings schemes yet. Start one and watch your money grow.</div>
                            </div>
                        </template>
                        <div class="pc-panel-actions" style="margin-top:12px;">
                            <a href="/savings" class="pc-action-btn pc-action-primary">💰 Manage Savings</a>
                            <a href="/chama" class="pc-action-btn pc-action-ghost">🤝 My Chama</a>
                        </div>
                    </div>

                    {{-- TAB: Loans --}}
                    <div x-show="bankTab==='loans'" style="margin-top:10px;">
                        <template x-if="district.my_loans && district.my_loans.length > 0">
                            <div style="margin-bottom:12px;">
                                <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;"><x-icon name="clipboard" class="w-2.5 h-2.5 inline-block" /> My Active Loans</div>
                                <template x-for="loan in district.my_loans" :key="loan.id">
                                    <div style="border-radius:12px;border:1px solid rgba(59,130,246,0.2);padding:10px 12px;margin-bottom:6px;background:rgba(59,130,246,0.05);">
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                            <span x-text="loan.icon" style="font-size:18px;"></span>
                                            <div style="flex:1;">
                                                <div style="font-size:13px;font-weight:700;color:#f9fafb;" x-text="loan.name"></div>
                                                <div style="font-size:11px;color:#6b7280;" x-text="'Balance: KES ' + loan.outstanding_balance.toLocaleString()"></div>
                                            </div>
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                            <div style="font-size:11px;color:#93c5fd;" x-text="'Next payment: KES ' + loan.payment_amount.toLocaleString()"></div>
                                            <button @click="repayLoan(loan)"
                                                    :disabled="loanLoading"
                                                    style="padding:5px 12px;border-radius:8px;font-size:11px;font-weight:700;background:rgba(59,130,246,0.2);color:#93c5fd;border:1px solid rgba(59,130,246,0.3);cursor:pointer;">
                                                Repay →
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;"><x-icon name="bank" class="w-2.5 h-2.5 inline-block" /> Available Loans</div>
                        <template x-if="!district.loan_products || district.loan_products.length === 0">
                            <div style="text-align:center;padding:20px;color:#6b7280;font-size:13px;">No loan products available.</div>
                        </template>
                        <template x-for="lp in (district.loan_products ?? [])" :key="lp.id">
                            <div style="border-radius:12px;border:1px solid rgba(255,255,255,0.08);padding:10px 12px;margin-bottom:8px;background:rgba(255,255,255,0.03);"
                                 :style="!lp.eligible ? 'opacity:.5;' : ''">
                                <div style="display:flex;align-items:flex-start;gap:10px;">
                                    <span x-text="lp.icon" style="font-size:22px;flex-shrink:0;"></span>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:13px;font-weight:800;color:#f9fafb;" x-text="lp.name"></div>
                                        <div style="font-size:11px;color:#9ca3af;margin-top:2px;" x-text="lp.description"></div>
                                        <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;">
                                            <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;background:rgba(59,130,246,0.12);color:#93c5fd;border:1px solid rgba(59,130,246,0.25);"
                                                  x-text="lp.annual_interest_rate + '% p.a.'"></span>
                                            <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,0.25);"
                                                  x-text="'Up to KES ' + lp.max_amount.toLocaleString()"></span>
                                            <span x-show="!lp.eligible"
                                                  style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px;background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.25);"
                                                  x-text="'Need ' + lp.min_credit_score + ' credit'"></span>
                                        </div>
                                    </div>
                                </div>
                                <template x-if="lp.eligible">
                                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,0.06);">
                                        <div style="display:flex;gap:6px;align-items:center;">
                                            <input type="number" x-model.number="loanAmounts[lp.id]"
                                                   :min="lp.min_amount" :max="lp.max_amount"
                                                   :placeholder="'KES ' + lp.min_amount.toLocaleString() + ' – ' + lp.max_amount.toLocaleString()"
                                                   style="flex:1;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);border-radius:8px;padding:6px 10px;color:#fff;font-size:12px;min-width:0;"
                                                   @focus="$el.style.borderColor='rgba(245,158,11,0.5)'" @blur="$el.style.borderColor='rgba(255,255,255,0.12)'">
                                            <button @click="takeLoan(lp)"
                                                    :disabled="loanLoading"
                                                    style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:800;background:linear-gradient(135deg,#1d4ed8,#1e40af);color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                                Take Loan →
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- TAB: My Account --}}
                    <div x-show="bankTab==='account'" style="margin-top:10px;">
                        <div style="background:rgba(245,158,11,0.05);border:1px solid rgba(245,158,11,0.15);border-radius:14px;padding:14px;margin-bottom:10px;">
                            <div style="font-size:10px;color:#fbbf24;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">Account Overview</div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:10px;padding:10px 8px;">
                                    <div style="font-size:10px;color:#6b7280;margin-bottom:3px;">Balance</div>
                                    <div style="font-size:14px;font-weight:800;color:#e2e8f0;" x-text="'KES ' + (district.balance ?? 0).toLocaleString()"></div>
                                </div>
                                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:10px;padding:10px 8px;">
                                    <div style="font-size:10px;color:#6b7280;margin-bottom:3px;">Total Saved</div>
                                    <div style="font-size:14px;font-weight:800;color:#34d399;" x-text="'KES ' + (district.total_savings ?? 0).toLocaleString()"></div>
                                </div>
                                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:10px;padding:10px 8px;">
                                    <div style="font-size:10px;color:#6b7280;margin-bottom:3px;">Credit Score</div>
                                    <div style="font-size:14px;font-weight:800;"
                                         :style="'color:' + (district.credit_color ?? '#F59E0B')"
                                         x-text="district.credit_score ?? 500"></div>
                                </div>
                                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:10px;padding:10px 8px;">
                                    <div style="font-size:10px;color:#6b7280;margin-bottom:3px;">Active Loans</div>
                                    <div style="font-size:14px;font-weight:800;color:#93c5fd;"
                                         x-text="(district.my_loans ?? []).length"></div>
                                </div>
                            </div>
                        </div>
                        <template x-if="district.credit_tips && district.credit_tips.length > 0">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:12px;margin-bottom:10px;">
                                <div style="font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;"><x-icon name="bulb" class="w-2.5 h-2.5 inline-block" /> Improve Credit Score</div>
                                <template x-for="tip in district.credit_tips" :key="tip">
                                    <div style="display:flex;gap:8px;align-items:flex-start;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                                        <span style="color:#fbbf24;font-size:11px;flex-shrink:0;margin-top:1px;">→</span>
                                        <span x-text="tip" style="font-size:12px;color:#c4c9d6;line-height:1.5;"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div class="pc-panel-actions" style="margin-top:10px;">
                            <a href="/savings" class="pc-action-btn pc-action-primary">💰 Manage Savings</a>
                            <a href="{{ route('life.board') }}#statement" class="pc-action-btn pc-action-secondary"><x-icon name="document" class="w-3.5 h-3.5 inline-block" /> View Statement</a>
                        </div>
                    </div>

                    {{-- Toast --}}
                    <div x-show="bankMsg" x-cloak
                         :style="bankMsgOk ? 'background:rgba(16,185,129,0.15);border-color:rgba(16,185,129,0.3);color:#34d399;' : 'background:rgba(239,68,68,0.12);border-color:rgba(239,68,68,0.3);color:#f87171;'"
                         style="margin-top:10px;padding:8px 12px;border-radius:10px;border:1px solid;font-size:12px;font-weight:600;"
                         x-text="bankMsg"></div>
                </div>
            </template>

            {{-- ── WORKPLACE — Full Career Management Panel ── --}}
            {{-- ══════════════════════════════════════════
                 CHAMPIONS' COURT — Dreams + Challenges hub
            ══════════════════════════════════════════ --}}
            <template x-if="district && district.slug === 'champions-court'">
                <div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:10px 12px;border-radius:12px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.22);">
                        <div style="flex-shrink:0;color:#f59e0b;"><x-icon name="trophy" class="w-7 h-7" /></div>
                        <div>
                            <div style="font-size:12px;font-weight:900;color:#f59e0b;letter-spacing:.04em;text-transform:uppercase;">Champions' Court</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.45);line-height:1.4;margin-top:2px;">Dreams &amp; fair challenges — progress made DURING the race is all that counts</div>
                        </div>
                    </div>

                    <div class="pc-comm-stats">
                        <div class="pc-comm-stat">
                            <div class="pc-comm-stat-num" x-text="district.owned_dreams ?? 0"></div>
                            <div class="pc-comm-stat-label">Dreams Claimed</div>
                        </div>
                        <div class="pc-comm-stat">
                            <div class="pc-comm-stat-num" x-text="district.open_challenges ?? 0"></div>
                            <div class="pc-comm-stat-label">Open Challenges</div>
                        </div>
                    </div>

                    <template x-if="(district.pending_invites ?? 0) > 0">
                        <div style="margin-top:10px;padding:10px 12px;border-radius:12px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);font-size:12px;font-weight:700;color:#a5b4fc;">
                            ✉️ You have <span x-text="district.pending_invites"></span> pending challenge invite(s) waiting.
                        </div>
                    </template>

                    <template x-if="district.featured_dream">
                        <div style="margin-top:12px;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);">
                            <span style="font-size:26px;" x-text="district.featured_dream.icon"></span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;">Featured Dream</div>
                                <div style="font-size:13px;font-weight:800;color:#fff;" x-text="district.featured_dream.name"></div>
                            </div>
                            <span style="font-size:12px;font-weight:900;color:#fbbf24;" x-text="'KES ' + Number(district.featured_dream.price).toLocaleString()"></span>
                        </div>
                    </template>

                    <template x-if="(district.my_challenges_list || []).length > 0">
                        <div style="margin-top:12px;">
                            <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Your Challenges</div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <template x-for="c in district.my_challenges_list" :key="'mine-' + c.id">
                                    <a :href="'{{ url('/challenges') }}/' + c.id" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);text-decoration:none;">
                                        <span style="font-size:18px;flex-shrink:0;" x-text="c.icon"></span>
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:12px;font-weight:800;color:#fff;" x-text="c.title"></div>
                                            <div style="font-size:10px;" :style="c.live ? 'color:#34d399;' : 'color:#fbbf24;'" x-text="c.subtitle"></div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="(district.open_challenges_list || []).length > 0">
                        <div style="margin-top:12px;">
                            <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Open Challenges — join now</div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <template x-for="c in district.open_challenges_list" :key="c.id">
                                    <div style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);">
                                        <span style="font-size:18px;flex-shrink:0;" x-text="c.icon"></span>
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:12px;font-weight:800;color:#fff;" x-text="c.title"></div>
                                            <div style="font-size:10px;color:rgba(255,255,255,.4);" x-text="c.subtitle"></div>
                                        </div>
                                        <button type="button" @click="joinChampionsChallenge(c.id)" :disabled="champions.joiningId === c.id"
                                                style="flex-shrink:0;padding:5px 10px;border-radius:8px;font-size:10.5px;font-weight:900;color:#fff;border:none;cursor:pointer;"
                                                :style="champions.joiningId === c.id ? 'background:rgba(255,255,255,.1);' : 'background:linear-gradient(135deg,#f59e0b,#b45309);'"
                                                x-text="champions.joiningId === c.id ? '…' : 'Join'"></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="champions.msg">
                        <div style="margin-top:8px;padding:8px 10px;border-radius:10px;font-size:11px;font-weight:700;"
                             :style="champions.msgOk ? 'background:rgba(16,185,129,.1);color:#6ee7b7;border:1px solid rgba(16,185,129,.3);' : 'background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.3);'"
                             x-text="champions.msg"></div>
                    </template>

                    <div class="pc-panel-actions" style="margin-top:14px;">
                        <template x-for="action in (district.actions || [])" :key="action.label">
                            <a :href="action.url" :class="'pc-action-btn pc-action-' + action.style" x-text="action.label"></a>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="district && district.slug === 'workplace'">
                <div x-data="{ wpResignId: null, wpResigning: false, wpMsg: '', wpMsgOk: true }">

                    {{-- Panel identity header --}}
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:10px 12px;border-radius:12px;background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.18);">
                        <div style="flex-shrink:0;color:#10b981;"><x-icon name="briefcase" class="w-7 h-7" /></div>
                        <div>
                            <div style="font-size:12px;font-weight:900;color:#10b981;letter-spacing:.04em;text-transform:uppercase;">Your Career</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.45);line-height:1.4;margin-top:2px;">Manage your active jobs, salary &amp; work performance</div>
                        </div>
                    </div>

                    {{-- NO JOB STATE --}}
                    <template x-if="!district.is_employed">
                        <div style="text-align:center;padding:20px 8px 12px;">
                            <div style="display:flex;justify-content:center;margin-bottom:10px;" x-html="pqIcon('building', 'w-10 h-10')"></div>
                            <div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:6px;">Not Employed Yet</div>
                            <div style="font-size:12px;color:var(--pc-muted);line-height:1.6;margin-bottom:16px;">
                                Head to the <strong style="color:#a5b4fc;">Opportunity Hub</strong> to take a course and apply for your first job.
                            </div>
                            <button @click="walkToDistrict('opportunity-hub')"
                                    class="pc-action-btn pc-action-primary" style="display:inline-flex;cursor:pointer;">
                                <x-icon name="graduation" class="w-3.5 h-3.5 inline-block" /> Go to Opportunity Hub
                            </button>
                        </div>
                    </template>

                    {{-- ACTIVE JOBS LIST --}}
                    <template x-if="district.is_employed">
                        <div>
                            {{-- Job count badge --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                                <div style="font-size:11px;font-weight:800;color:var(--pc-muted);text-transform:uppercase;letter-spacing:.06em;">Your Jobs</div>
                                <div style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:9999px;background:rgba(99,102,241,.15);color:#818cf8;"
                                     x-text="`${district.job_count}/${district.max_jobs} slots`"></div>
                            </div>

                            <template x-for="job in district.active_jobs" :key="job.player_job_id">
                                <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:12px;margin-bottom:8px;">
                                    <div style="display:flex;align-items:flex-start;gap:10px;">
                                        <div style="font-size:24px;flex-shrink:0;" x-text="job.employer_logo"></div>
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:13px;font-weight:900;color:#fff;line-height:1.2;" x-text="job.title"></div>
                                            <div style="font-size:11px;color:var(--pc-muted);" x-text="job.employer_name"></div>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:5px;flex-wrap:wrap;">
                                                <span style="font-size:12px;font-weight:800;color:#10b981;" x-text="'KES ' + job.salary.toLocaleString() + '/mo'"></span>
                                                <span style="font-size:10px;padding:1px 6px;border-radius:9999px;font-weight:700;"
                                                      :style="job.level===3 ? 'background:rgba(245,158,11,.15);color:#f59e0b;' : job.level===2 ? 'background:rgba(99,102,241,.15);color:#818cf8;' : 'background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);'"
                                                      x-text="job.level_label"></span>
                                                <span style="font-size:10px;padding:1px 6px;border-radius:9999px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.4);font-weight:700;"
                                                      x-text="job.employment_type === 'full_time' ? '⭐ Full-time' : '⚡ Part-time'"></span>
                                            </div>
                                        </div>
                                        <button @click="wpResignId = (wpResignId === job.job_id ? null : job.job_id)"
                                                style="font-size:10px;font-weight:700;padding:4px 8px;border-radius:8px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;cursor:pointer;flex-shrink:0;">
                                            Resign
                                        </button>
                                    </div>

                                    {{-- Resign confirm --}}
                                    <template x-if="wpResignId === job.job_id">
                                        <div style="margin-top:10px;padding:10px;border-radius:10px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);">
                                            <div style="font-size:12px;font-weight:700;color:#f87171;margin-bottom:8px;">Resign from <span x-text="job.employer_name"></span>? Your salary will be removed.</div>
                                            <div style="display:flex;gap:6px;">
                                                <button @click="wpResignId=null" style="flex:1;font-size:11px;font-weight:700;padding:6px;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);cursor:pointer;">Cancel</button>
                                                <button :disabled="wpResigning"
                                                        @click="wpResigning=true; fetch(`/opportunities/jobs/${job.job_id}/resign`, {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},credentials:'same-origin'}).then(r=>r.json()).then(d=>{wpResigning=false;wpResignId=null;wpMsg='Resigned. Refresh to update salary.';wpMsgOk=true;district.active_jobs=district.active_jobs.filter(j=>j.job_id!==job.job_id);district.job_count--;district.is_employed=district.active_jobs.length>0;}).catch(()=>{wpResigning=false;wpMsg='Failed. Try again.';wpMsgOk=false;})"
                                                        style="flex:1;font-size:11px;font-weight:700;padding:6px;border-radius:8px;background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.4);color:#f87171;cursor:pointer;">
                                                    <span x-show="!wpResigning">Confirm Resign</span>
                                                    <span x-show="wpResigning">...</span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Total salary --}}
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-radius:10px;background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.15);margin-bottom:10px;">
                                <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);">Total Monthly Income</span>
                                <span style="font-size:14px;font-weight:900;color:#10b981;" x-text="'KES ' + district.total_salary.toLocaleString()"></span>
                            </div>

                            {{-- Add more job slot --}}
                            <template x-if="district.job_count < district.max_jobs">
                                <a href="/opportunities#jobs" class="pc-action-btn pc-action-secondary" style="display:flex;margin-bottom:10px;">
                                    ➕ Add Part-time Job (<span x-text="`${district.max_jobs - district.job_count} slot${district.max_jobs - district.job_count > 1 ? 's' : ''} left`"></span>)
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- Flash message --}}
                    <template x-if="wpMsg">
                        <div style="margin-top:8px;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;"
                             :style="wpMsgOk ? 'background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#10b981;' : 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;'"
                             x-text="wpMsg"></div>
                    </template>

                    {{-- Performance Score — only when employed --}}
                    <template x-if="district.is_employed">
                        <div>
                            <div class="pc-wp-perf" style="margin-top:10px;">
                                <div class="pc-wp-perf-header">
                                    <span>Performance Score</span>
                                    <span class="pc-wp-perf-badge"
                                          :class="district.promotion_disqualified ? 'pc-wp-perf-badge--probation' : (district.promotion_eligible ? 'pc-wp-perf-badge--high' : '')"
                                          x-text="district.perf_score + '/100'"></span>
                                </div>
                                <div class="pc-wp-perf-track">
                                    <div class="pc-wp-perf-fill" :style="'width:' + (district.perf_score ?? 40) + '%'"></div>
                                </div>
                                <div class="pc-wp-milestone"
                                     :class="district.promotion_disqualified ? 'pc-wp-milestone--probation' : (district.promotion_eligible ? 'pc-wp-milestone--ready' : '')"
                                     x-text="district.next_milestone"></div>
                            </div>

                            {{-- Today's Encounter --}}
                            <template x-if="district.today_encounter">
                                <div class="pc-wp-encounter" style="margin-top:10px;">
                                    <div class="pc-wp-encounter-eyebrow">Today's Lesson</div>
                                    <div class="pc-wp-encounter-header">
                                        <span class="pc-wp-encounter-icon" x-text="district.today_encounter.icon"></span>
                                        <span class="pc-wp-encounter-title" x-text="district.today_encounter.title"></span>
                                    </div>
                                    <div class="pc-wp-encounter-lesson" x-text="district.today_encounter.lesson"></div>
                                </div>
                            </template>

                            {{-- Investment tip --}}
                            <template x-if="district.invest_tip">
                                <div class="pc-wp-invest-tip" style="margin-top:8px;">
                                    <span style="font-size:16px;">📈</span>
                                    <span x-text="district.invest_tip"></span>
                                </div>
                            </template>

                            {{-- Find more opportunities CTA --}}
                            <div class="pc-panel-actions" style="margin-top:12px;">
                                <a href="/opportunities#jobs" class="pc-action-btn pc-action-secondary">➕ Find More Jobs</a>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ── OPPORTUNITY HUB — tabbed Courses + Jobs ── --}}
            <template x-if="district && district.slug === 'opportunity-hub'">
                <div>
                    {{-- Panel identity header --}}
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:10px 12px;border-radius:12px;background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.22);">
                        <div style="flex-shrink:0;color:#a5b4fc;"><x-icon name="graduation" class="w-7 h-7" /></div>
                        <div>
                            <div style="font-size:12px;font-weight:900;color:#a5b4fc;letter-spacing:.04em;text-transform:uppercase;">Learn &amp; Apply</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.45);line-height:1.4;margin-top:2px;">Take courses, earn skills &amp; apply for new jobs</div>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div class="pc-opp-tabs">
                        <button class="pc-opp-tab" :class="{ active: oppTab === 'courses' }" @click="oppTab = 'courses'">
                            <x-icon name="graduation" class="w-3.5 h-3.5 inline-block" /> Courses
                        </button>
                        <button class="pc-opp-tab" :class="{ active: oppTab === 'jobs' }" @click="oppTab = 'jobs'">
                            <x-icon name="briefcase" class="w-3.5 h-3.5 inline-block" /> Jobs
                            <span class="pc-opp-tab-lock"
                                  x-show="!oppCourses.some(c => c.player_status === 'completed')"
                                  x-cloak><x-icon name="lock" class="w-3 h-3 inline-block" /></span>
                        </button>
                    </div>

                    {{-- Loading --}}
                    <div class="pc-opp-loading" x-show="oppLoading" x-cloak>
                        <div class="pc-spinner"></div>
                    </div>
                    <div class="pc-opp-error" x-show="oppError && !oppLoading" x-text="oppError" x-cloak></div>

                    {{-- COURSES TAB --}}
                    <template x-if="oppTab === 'courses' && !oppLoading">
                        <div class="pc-course-list">
                            <template x-if="oppCourses.length === 0">
                                <p style="font-size:13px;color:var(--pc-muted);text-align:center;padding:20px;">
                                    No courses available yet.
                                </p>
                            </template>
                            <template x-for="course in oppCourses" :key="course.id">
                                <div class="pc-course-card">
                                    <div class="pc-course-header">
                                        <div class="pc-course-icon"
                                             :style="`background: ${course.color}15; border-color: ${course.color}40;`"
                                             x-text="course.icon"></div>
                                        <div class="pc-course-meta">
                                            <div class="pc-course-title" x-text="course.title"></div>
                                            <span class="pc-course-track" x-text="trackLabel(course.career_track)"></span>
                                        </div>
                                        <span style="font-size:10px;color:var(--pc-green);font-weight:700;white-space:nowrap;"
                                              x-show="course.is_free">FREE</span>
                                    </div>
                                    <div class="pc-course-desc" x-text="course.description"></div>
                                    <div class="pc-course-outcome" x-text="course.outcome"></div>

                                    {{-- ⭐ Career path chip --}}
                                    <div x-show="course.recommended" x-cloak
                                         style="margin-bottom:6px;font-size:10px;font-weight:900;color:#fbbf24;">⭐ On your career path</div>

                                    {{-- Open reader → enroll → study → complete --}}
                                    <template x-if="course.player_status === 'not_enrolled'">
                                        <button class="pc-course-btn pc-btn-enroll"
                                                :style="`border-color: ${course.color}50; background: ${course.color}12;`"
                                                @click="openCourseReader(course.id)">
                                            <span x-text="course.is_free
                                                ? '📖 View Free Course'
                                                : '💳 View Course · KES ' + (course.cost_kes ?? 0).toLocaleString()">
                                            </span>
                                        </button>
                                    </template>
                                    <template x-if="course.player_status === 'enrolled'">
                                        <button class="pc-course-btn pc-btn-enroll"
                                                style="border-color:rgba(77,168,247,.5);background:rgba(77,168,247,.12);"
                                                @click="openCourseReader(course.id)">
                                            ▶ Continue Learning
                                        </button>
                                    </template>
                                    <template x-if="course.player_status === 'completed'">
                                        <button class="pc-course-btn pc-btn-done" disabled>
                                            🏅 Completed
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Full page link --}}
                    <div style="text-align:center;padding:8px 0 4px;">
                        <a href="{{ route('opportunities.index') }}"
                           style="font-size:11px;font-weight:800;color:rgba(77,168,247,.8);text-decoration:none;letter-spacing:.04em;"
                           >Browse Full Opportunity Hub →</a>
                    </div>

                    {{-- JOBS TAB --}}
                    <template x-if="oppTab === 'jobs' && !oppLoading">
                        <div>
                            <template x-if="!oppCourses.some(c => c.player_status === 'completed')">
                                <div style="text-align:center;padding:24px 16px;">
                                    <div style="font-size:28px;margin-bottom:8px;">🔒</div>
                                    <div style="font-size:14px;font-weight:700;color:var(--pc-bright);margin-bottom:6px;">Jobs Locked</div>
                                    <div style="font-size:12px;color:var(--pc-muted);">Complete any course above to unlock the job board.</div>
                                </div>
                            </template>
                            <template x-if="oppCourses.some(c => c.player_status === 'completed')">
                                <div class="pc-job-list">
                                    <template x-for="job in oppJobs" :key="job.id">
                                        <div class="pc-job-card" :class="{ 'locked-job': !job.has_requirement }">
                                            <div class="pc-job-logo" x-text="job.employer_logo"></div>
                                            <div class="pc-job-info">
                                                <div class="pc-job-title" x-text="job.title"></div>
                                                <div class="pc-job-employer" x-text="job.employer_name"></div>
                                                <div class="pc-job-salary"
                                                     x-text="'KES ' + job.salary_kes_month.toLocaleString() + (job.is_gig ? ' one-off' : ' /mo')"></div>
                                                <div style="margin:2px 0 4px;">
                                                    <span x-show="job.employment_type === 'full_time'" style="display:inline-block;font-size:9px;font-weight:900;letter-spacing:.05em;padding:2px 7px;border-radius:999px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.35);color:#34d399;">🏢 FULL-TIME · your only job</span>
                                                    <span x-show="job.employment_type === 'part_time'" style="display:inline-block;font-size:9px;font-weight:900;letter-spacing:.05em;padding:2px 7px;border-radius:999px;background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.35);color:#fcd34d;">⏰ PART-TIME · max 2</span>
                                                    <span x-show="job.employment_type === 'freelance'" style="display:inline-block;font-size:9px;font-weight:900;letter-spacing:.05em;padding:2px 7px;border-radius:999px;background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.4);color:#c4b5fd;">⚡ GIG · one-off pay</span>
                                                </div>
                                                <div class="pc-job-req"
                                                     :class="job.has_requirement ? 'met' : 'unmet'">
                                                    <template x-if="job.required_courses && job.required_courses.length > 0">
                                                        <span>
                                                            <span x-text="job.has_requirement ? '✓' : '✗'"></span>
                                                            Requires: <span x-text="job.required_courses.map(c => c.icon + ' ' + c.title).join(' + ')"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="!job.required_courses || job.required_courses.length === 0">
                                                        <span style="color:var(--pc-green);">✓ No requirements</span>
                                                    </template>
                                                </div>

                                                {{-- Apply button --}}
                                                <template x-if="job.is_employed === true">
                                                    <button class="pc-job-apply-btn pc-btn-employed" disabled x-text="job.is_gig ? '⚡ Gig in progress' : '✅ Employed'"></button>
                                                </template>
                                                <template x-if="job.is_employed === 'applying'">
                                                    <button class="pc-job-apply-btn pc-btn-apply" disabled>Applying…</button>
                                                </template>
                                                <template x-if="!job.is_employed && job.cooldown_days > 0">
                                                    <button class="pc-job-apply-btn pc-btn-done" disabled
                                                            x-text="'⏳ Gig done — reopens in ' + job.cooldown_days + ' game day(s)'"></button>
                                                </template>
                                                <template x-if="!job.is_employed && !(job.cooldown_days > 0) && job.has_requirement">
                                                    <button class="pc-job-apply-btn pc-btn-apply"
                                                            @click="applyJob(job.id)"
                                                            x-text="job.is_gig ? '⚡ Take Gig' : '🚀 Apply Now'">
                                                    </button>
                                                </template>
                                                <template x-if="!job.is_employed && !(job.cooldown_days > 0) && !job.has_requirement">
                                                    <button class="pc-job-apply-btn pc-btn-done" disabled>
                                                        Complete required course first
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            {{-- ── FUN WORLD — Entertainment Budget Lesson ── --}}
            <template x-if="district && district.slug === 'fun-world'">
                <div x-data="{ fwMsg: '', fwOk: true, fwBuying: null, fwMood: district.mood ?? 70, fwTab: 'activities' }">

                    {{-- Mood meter — reacts inline with a glow pulse + emoji pop on every activity, no blocking popup --}}
                    <div x-ref="moodBox" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;padding:10px 12px;border-radius:12px;background:rgba(255,107,53,.07);border:1px solid rgba(255,107,53,.2);">
                        <div x-ref="moodEmoji" style="font-size:26px;flex-shrink:0;" x-text="fwMood >= 80 ? '😄' : fwMood >= 55 ? '😊' : fwMood >= 35 ? '😐' : '😔'"></div>
                        <div style="flex:1;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                <span style="font-size:11px;font-weight:800;color:rgba(255,255,255,.6);">Character Mood</span>
                                <span style="font-size:11px;font-weight:900;color:#FF6B35;" x-text="fwMood + '/100'"></span>
                            </div>
                            <div style="height:6px;border-radius:9999px;background:rgba(255,255,255,.08);overflow:hidden;">
                                <div style="height:100%;border-radius:9999px;transition:width .6s cubic-bezier(.34,1.56,.64,1),background .6s ease;"
                                     :style="'width:' + fwMood + '%;background:' + (fwMood >= 70 ? '#FF6B35' : fwMood >= 40 ? '#f59e0b' : '#f87171')"></div>
                            </div>
                            <div style="font-size:10px;color:rgba(255,255,255,.35);margin-top:3px;"
                                 x-text="district.mood_last_boosted_at ? 'Last fun: ' + district.mood_last_boosted_at : 'No recent fun — treat yourself!'"></div>
                        </div>
                    </div>

                    {{-- Mood gameplay effect labels --}}
                    <template x-if="fwMood > 80">
                        <div style="margin-bottom:10px;padding:6px 12px;border-radius:10px;font-size:11px;font-weight:800;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);color:#34d399;">
                            ✓ Mood bonus active — +10% XP from quests
                        </div>
                    </template>
                    <template x-if="fwMood < 40">
                        <div style="margin-bottom:10px;padding:6px 12px;border-radius:10px;font-size:11px;font-weight:800;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.25);color:#f87171;">
                            ⚠ Low mood penalty active — work income reduced 10%. Recharge here!
                        </div>
                    </template>

                    {{-- Flash message — brief inline confirmation; the mood bar/emoji glow is the real feedback now --}}
                    <template x-if="fwMsg">
                        <div style="margin-bottom:10px;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;"
                             :style="fwOk ? 'background:rgba(255,107,53,.08);border:1px solid rgba(255,107,53,.25);color:#FF6B35;' : 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;'"
                             x-text="fwMsg"></div>
                    </template>

                    {{-- 50/30/20 Rule Banner --}}
                    <div class="pc-fw-rule">
                        <div class="pc-fw-rule-badge" x-text="district.rule"></div>
                        <div class="pc-fw-rule-desc" x-text="district.rule_desc"></div>
                    </div>

                    {{-- Entertainment budget calculator --}}
                    <template x-if="district.monthly_income > 0">
                        <div class="pc-fw-budget">
                            <div class="pc-fw-budget-label">Your Monthly Entertainment Budget</div>
                            <div class="pc-fw-budget-amount"
                                 x-text="'KES ' + (district.entertainment_budget ?? 0).toLocaleString()"></div>
                            <div class="pc-fw-budget-sub">15% of your KES
                                <span x-text="(district.monthly_income).toLocaleString()"></span> /month income
                            </div>
                        </div>
                    </template>

                    {{-- Fun spend so far this game month vs budget --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;padding:9px 12px;border-radius:12px;"
                         :style="(district.fun_spent_month ?? 0) > (district.entertainment_budget ?? 0) && (district.entertainment_budget ?? 0) > 0
                             ? 'background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.25);'
                             : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);'">
                        <span style="font-size:11px;font-weight:800;color:rgba(255,255,255,.55);">🧾 Spent on fun this game month</span>
                        <span style="font-size:12px;font-weight:900;"
                              :style="(district.fun_spent_month ?? 0) > (district.entertainment_budget ?? 0) && (district.entertainment_budget ?? 0) > 0 ? 'color:#f87171;' : 'color:#FF6B35;'"
                              x-text="'KES ' + (district.fun_spent_month ?? 0).toLocaleString()
                                      + ((district.entertainment_budget ?? 0) > 0 ? ' / ' + (district.entertainment_budget).toLocaleString() : '')"></span>
                    </div>

                    {{-- Activities vs Arcade tabs --}}
                    <div style="display:flex;gap:6px;margin-bottom:12px;">
                        <button type="button" @click="fwTab = 'activities'"
                                :style="fwTab === 'activities' ? 'background:rgba(255,107,53,.2);border:1px solid rgba(255,107,53,.4);color:#FF6B35;' : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.5);'"
                                style="flex:1;padding:8px;border-radius:10px;font-size:11px;font-weight:800;cursor:pointer;"><x-icon name="ticket" class="w-3.5 h-3.5 inline-block" /> Activities</button>
                        <button type="button" @click="fwTab = 'arcade'"
                                :style="fwTab === 'arcade' ? 'background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.4);color:#a5b4fc;' : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.5);'"
                                style="flex:1;padding:8px;border-radius:10px;font-size:11px;font-weight:800;cursor:pointer;"><x-icon name="gamepad" class="w-3.5 h-3.5 inline-block" /> Arcade</button>
                    </div>

                    {{-- Arcade tab — mini-games that play with your wallet, not just spend from it --}}
                    <div x-show="fwTab === 'arcade'" x-cloak>
                        <div style="display:flex;align-items:center;gap:12px;padding:14px;border-radius:14px;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.25);">
                            <span style="font-size:32px;">🐍</span>
                            <div style="flex:1;">
                                <div style="font-weight:900;color:#fff;font-size:14px;">Pesa Trail</div>
                                <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;">Stake a bit of your wallet, roll the die, and race Robo (or friends) down the trail — rewards, expenses, ladders and snakes all along the way.</div>
                            </div>
                            <a href="{{ route('arcade.snakes.lobby') }}" style="flex-shrink:0;font-size:11px;font-weight:800;padding:8px 14px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;text-decoration:none;">Play →</a>
                        </div>
                    </div>

                    {{-- Experience cards with spend buttons --}}
                    <div class="pc-fw-experiences" x-show="fwTab === 'activities'" x-cloak>
                        <div class="pc-fw-xp-title"><x-icon name="ticket" class="w-3.5 h-3.5 inline-block" /> Experiences in Fun World</div>
                        <template x-for="xp in (district.experiences || [])" :key="xp.name">
                            <div class="pc-fw-xp-card">
                                <div class="pc-fw-xp-icon" x-text="xp.icon"></div>
                                <div class="pc-fw-xp-body">
                                    <div class="pc-fw-xp-name" x-text="xp.name"></div>
                                    <div class="pc-fw-xp-lesson" x-text="xp.lesson"></div>
                                </div>
                                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                                    <div class="pc-fw-xp-price" x-text="'KES ' + (xp.price).toLocaleString()"></div>
                                    <button :disabled="fwBuying === xp.name || (district.balance ?? 0) < xp.price"
                                            @click="fwBuying = xp.name; fwMsg = '';
                                                fetch('/world/fun-world/spend', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                                    credentials: 'same-origin',
                                                    body: JSON.stringify({ activity_id: xp.id ?? null, name: xp.name, price: xp.price, icon: xp.icon })
                                                })
                                                .then(r => r.json())
                                                .then(d => {
                                                    fwBuying = null;
                                                    if (d.error) { fwMsg = d.error; fwOk = false; }
                                                    else {
                                                        fwMood = d.new_mood; liveBalance = d.new_balance; district.balance = d.new_balance;
                                                        district.fun_spent_month = (district.fun_spent_month ?? 0) + xp.price;
                                                        fwOk = true;
                                                        fwMsg = (d.icon || xp.icon) + ' ' + (d.name || xp.name) + ' — +' + d.mood_boost + ' mood, +' + d.xp_earned + ' XP!';
                                                        clearTimeout(window.__fwMsgTimer);
                                                        window.__fwMsgTimer = setTimeout(() => { fwMsg = ''; }, 4000);
                                                        $refs.moodBox.classList.remove('pc-mood-pulse'); void $refs.moodBox.offsetWidth; $refs.moodBox.classList.add('pc-mood-pulse');
                                                        $refs.moodEmoji.classList.remove('pc-mood-emoji-pop'); void $refs.moodEmoji.offsetWidth; $refs.moodEmoji.classList.add('pc-mood-emoji-pop');
                                                        SoundMgr.play('fun');
                                                    }
                                                })
                                                .catch(() => { fwBuying = null; fwMsg = 'Failed. Try again.'; fwOk = false; })"
                                            style="font-size:10px;font-weight:800;padding:5px 8px;border-radius:8px;cursor:pointer;white-space:nowrap;transition:all .2s;"
                                            :style="(district.balance ?? 0) >= xp.price
                                                ? 'background:rgba(255,107,53,.2);border:1px solid rgba(255,107,53,.4);color:#FF6B35;'
                                                : 'background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.3);cursor:not-allowed;'">
                                        <span x-show="fwBuying !== xp.name">🎉 Enjoy</span>
                                        <span x-show="fwBuying === xp.name">...</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- ── COMMUNITY CENTRE — Market Watch ── --}}
            <template x-if="district && district.slug === 'community'">
                <div>
                    <a href="{{ route('forums.index') }}" class="pc-action-btn pc-action-primary" style="display:block;text-align:center;margin-bottom:8px;"><x-icon name="speech" class="w-3.5 h-3.5 inline-block" /> Visit Forums →</a>
                    <div style="display:flex;gap:8px;margin-bottom:10px;">
                        <a href="{{ route('friends.index') }}" class="pc-action-btn pc-action-secondary" style="flex:1;text-align:center;"><x-icon name="people" class="w-3.5 h-3.5 inline-block" /> Friends &amp; Loans</a>
                        <a href="{{ route('chama.index') }}" class="pc-action-btn pc-action-secondary" style="flex:1;text-align:center;"><x-icon name="group" class="w-3.5 h-3.5 inline-block" /> Chamas</a>
                    </div>

                    <p class="pc-panel-desc" x-text="district.description"></p>

                    {{-- Market Watch — bulletins that telegraph share moves before they land --}}
                    <div class="pc-dreams-board">
                        <div class="pc-dreams-title">📰 Market Watch</div>
                        <div class="pc-dreams-sub">Rumours from the trading floor — not every story pans out</div>
                        <div class="pc-dreams-list">
                            <template x-if="!district.market_news || district.market_news.length === 0">
                                <div class="pc-dream-card" style="opacity:.6;">
                                    <span class="pc-dream-text">Nothing brewing right now — check back soon.</span>
                                </div>
                            </template>
                            <template x-for="item in (district.market_news || [])" :key="item.headline">
                                <div class="pc-dream-card" style="flex-direction:column;align-items:flex-start;gap:4px;cursor:pointer;" @click="openNewsDetail(item)">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <span class="pc-dream-icon" x-text="newsTimeBadge(item.time_state).icon"></span>
                                        <span class="pc-dream-text" style="font-weight:800;" x-text="item.headline"></span>
                                    </div>
                                    <span style="font-size:11px;color:rgba(255,255,255,.5);" x-text="item.status === 'resolved' ? item.lesson : item.flavor"></span>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.03em;" :style="'color:' + newsTimeBadge(item.time_state).color" x-text="newsTimeBadge(item.time_state).label"></span>
                                        <span style="font-size:10px;font-weight:700;color:#67e8f9;">Read more →</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="pc-panel-actions">
                        <template x-for="action in (district.actions || [])" :key="action.label">
                            <a :href="action.url"
                               :class="'pc-action-btn pc-action-' + action.style"
                               x-text="action.label"></a>
                        </template>
                    </div>
                </div>
            </template>

            {{-- ── KIAMBU ESTATES — Locked with progress ── --}}
            <template x-if="district && district.slug === 'estates' && district.status === 'locked'">
                <div style="padding:4px 0;">
                    <div style="text-align:center;padding:16px 8px 12px;">
                        <div style="margin-bottom:8px;display:flex;justify-content:center;color:#9ca3af;"><x-icon name="building" class="w-10 h-10" /></div>
                        <div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:6px;">Property Quarter — Locked</div>
                        <div style="font-size:12px;color:var(--pc-muted);line-height:1.5;margin-bottom:14px;">
                            Unlock by saving KES 200,000 <em>or</em> completing 3 missions.
                        </div>
                    </div>

                    {{-- Savings progress bar --}}
                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:12px;margin-bottom:8px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);"><x-icon name="coin" class="w-3 h-3 inline-block" /> Savings Progress</span>
                            <span style="font-size:11px;font-weight:900;color:#10b981;"
                                  x-text="'KES ' + (district.unlock_balance ?? 0).toLocaleString() + ' / 200,000'"></span>
                        </div>
                        <div style="height:8px;border-radius:4px;background:rgba(255,255,255,.08);overflow:hidden;">
                            <div style="height:100%;border-radius:4px;background:linear-gradient(90deg,#10b981,#34d399);transition:width .6s ease;"
                                 :style="'width:' + (district.unlock_pct ?? 0) + '%'"></div>
                        </div>
                        <div style="font-size:10px;color:rgba(255,255,255,.3);margin-top:4px;"
                             x-text="(district.unlock_pct ?? 0) + '% of KES 200,000 target'"></div>
                    </div>

                    {{-- Mission progress --}}
                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:10px 12px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
                        <span style="font-size:20px;">🎯</span>
                        <div>
                            <div style="font-size:11px;font-weight:700;color:#fff;"
                                 x-text="(district.unlock_missions ?? 0) + '/3 Missions Completed'"></div>
                            <div style="font-size:10px;color:var(--pc-muted);">Complete 3 missions as an alternative path</div>
                        </div>
                    </div>

                    {{-- Preview what's inside --}}
                    <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">Preview — What Awaits</div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:rgba(255,255,255,.025);opacity:.6;">
                            <span>🌳</span><span style="font-size:11px;color:rgba(255,255,255,.5);">Quarter-Acre Plot — Ruiru · KES 250,000</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:rgba(255,255,255,.025);opacity:.6;">
                            <span>🛏️</span><span style="font-size:11px;color:rgba(255,255,255,.5);">Bedsitter — Kasarani · KES 12,000/mo rent</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;background:rgba(255,255,255,.025);opacity:.6;">
                            <span>🏠</span><span style="font-size:11px;color:rgba(255,255,255,.5);">2BR Apartment — Kiambu Road · KES 25,000/mo rent</span>
                        </div>
                    </div>

                    <div class="pc-panel-actions" style="margin-top:14px;">
                        <a href="{{ route('life.board') }}" class="pc-action-btn pc-action-primary">💰 Build My Savings</a>
                    </div>
                </div>
            </template>

            {{-- ── KIAMBU ESTATES — Property Listings ── --}}
            <template x-if="district && district.slug === 'estates' && district.status === 'active'">
                <div>
                    <p class="pc-panel-desc" x-text="district.description"></p>

                    {{-- Unlock celebration badge --}}
                    <div class="pc-estates-banner">
                        <span class="pc-estates-banner-icon" x-html="pqIcon('trophy', 'w-6 h-6')"></span>
                        <div>
                            <div class="pc-estates-banner-title">Zone Unlocked!</div>
                            <div class="pc-estates-banner-sub">Your savings qualify you for Kiambu Estates. Property is long-term wealth.</div>
                        </div>
                    </div>

                    <div class="pc-estates-label"><x-icon name="building" class="w-3.5 h-3.5 inline-block" /> Available Properties</div>
                    <div x-data="{ estMsg: '', estOk: true, estBuying: null }">
                    <template x-if="estMsg">
                        <div style="margin-bottom:10px;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;"
                             :style="estOk ? 'background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#10b981;' : 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;'"
                             x-text="estMsg"></div>
                    </template>
                    <template x-for="prop in (district.properties || [])" :key="prop.name">
                        <div class="pc-property-card" :class="prop.can_afford ? '' : 'pc-property-locked'">
                            <div class="pc-property-header">
                                <div class="pc-property-icon" x-text="prop.icon"></div>
                                <div class="pc-property-meta">
                                    <div class="pc-property-name" x-text="prop.name"></div>
                                    <div class="pc-property-price" x-text="'KES ' + prop.price.toLocaleString()"></div>
                                </div>
                                <template x-if="prop.rental_income > 0">
                                    <div class="pc-property-yield">
                                        <div class="pc-yield-num" x-text="'KES ' + prop.rental_income.toLocaleString()"></div>
                                        <div class="pc-yield-label">/ mo rent</div>
                                    </div>
                                </template>
                            </div>
                            <div class="pc-property-finance">
                                <div class="pc-fin-row">
                                    <span>Deposit (10%) — pay now</span>
                                    <strong x-text="'KES ' + prop.deposit.toLocaleString()"></strong>
                                </div>
                                <div class="pc-fin-row">
                                    <span>Monthly installment (auto-billed)</span>
                                    <strong x-text="'KES ' + prop.monthly.toLocaleString()"></strong>
                                </div>
                                <div class="pc-fin-row" x-show="prop.total_cost">
                                    <span x-text="'Term: ' + (prop.months || 36) + ' game months (' + gdApprox((prop.months || 36) * 30) + ')'"></span>
                                    <strong style="color:#fbbf24;" x-text="'Total: KES ' + (prop.total_cost || 0).toLocaleString()"></strong>
                                </div>
                            </div>
                            <div class="pc-property-lesson" x-text="'💡 ' + prop.lesson"></div>
                            <div style="display:flex;gap:8px;margin-top:10px;" x-show="prop.can_afford_cash || prop.can_afford">
                                <template x-if="prop.can_afford_cash">
                                    <button :disabled="estBuying === prop.asset_id"
                                            @click="estBuying = prop.asset_id; estMsg = '';
                                                fetch('/marketplace/' + prop.asset_id + '/buy', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                                    credentials: 'same-origin',
                                                    body: JSON.stringify({ financing: false })
                                                })
                                                .then(r => r.json())
                                                .then(d => {
                                                    estBuying = null;
                                                    if (d.error) { estMsg = d.error; estOk = false; }
                                                    else { estMsg = '🎉 ' + prop.name + ' bought outright! New balance KES ' + (d.new_balance ?? 0).toLocaleString() + '.'; estOk = true; liveBalance = d.new_balance ?? liveBalance; }
                                                })
                                                .catch(() => { estBuying = null; estMsg = 'Purchase failed. Try again.'; estOk = false; })"
                                            style="flex:1;padding:9px;border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;background:linear-gradient(135deg,rgba(52,211,153,.2),rgba(52,211,153,.1));border:1px solid rgba(52,211,153,.4);color:#34d399;display:flex;align-items:center;justify-content:center;gap:6px;">
                                        <span x-show="estBuying !== prop.asset_id">✓ Buy Cash</span>
                                        <span x-show="estBuying === prop.asset_id">Processing...</span>
                                    </button>
                                </template>
                                <template x-if="prop.can_afford">
                                    <button :disabled="estBuying === prop.asset_id"
                                            @click="estBuying = prop.asset_id; estMsg = '';
                                                fetch('/marketplace/' + prop.asset_id + '/buy', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                                    credentials: 'same-origin',
                                                    body: JSON.stringify({ financing: true })
                                                })
                                                .then(r => r.json())
                                                .then(d => {
                                                    estBuying = null;
                                                    if (d.error) { estMsg = d.error; estOk = false; }
                                                    else { estMsg = '🎉 ' + prop.name + ' financed! Deposit KES ' + (d.financing?.deposit ?? prop.deposit).toLocaleString() + ' paid — KES ' + (d.financing?.monthly ?? prop.monthly).toLocaleString() + '/game month is now on your bills.'; estOk = true; liveBalance = d.new_balance ?? liveBalance; }
                                                })
                                                .catch(() => { estBuying = null; estMsg = 'Purchase failed. Try again.'; estOk = false; })"
                                            style="flex:1;padding:9px;border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;background:linear-gradient(135deg,rgba(163,230,53,.18),rgba(163,230,53,.08));border:1px solid rgba(163,230,53,.35);color:#a3e635;display:flex;align-items:center;justify-content:center;gap:6px;">
                                        <span x-show="estBuying !== prop.asset_id" x-text="'🏠 Finance — Pay KES ' + prop.deposit.toLocaleString() + ' Deposit'"></span>
                                        <span x-show="estBuying === prop.asset_id">Processing...</span>
                                    </button>
                                </template>
                            </div>
                            <template x-if="!prop.can_afford_cash && !prop.can_afford">
                                <div style="display:flex;gap:6px;margin-top:10px;align-items:center;">
                                    <div class="pc-property-afford-badge pc-property-afford-badge--short" style="flex:1;margin-top:0;"
                                         x-text="'Need KES ' + (prop.deposit - (district.balance ?? 0)).toLocaleString() + ' more for the deposit'"></div>
                                    <a href="/savings" style="font-size:11px;font-weight:800;padding:7px 10px;border-radius:9px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;white-space:nowrap;text-decoration:none;">💰 Save</a>
                                </div>
                            </template>
                        </div>
                    </template>
                    </div>

                    <div class="pc-panel-actions" style="margin-top:16px;">
                        <a href="/savings" class="pc-action-btn pc-action-secondary">💰 Grow My Savings</a>
                    </div>
                </div>
            </template>

            {{-- ── JUA KALI CAR YARD — Vehicle Financing ── --}}
            <template x-if="district && district.slug === 'car-yard' && district.status === 'active'">
                <div>
                    <p class="pc-panel-desc" x-text="district.description"></p>

                    {{-- Unlock badge --}}
                    <div class="pc-estates-banner" style="--banner-color: #FFBC00;">
                        <span class="pc-estates-banner-icon">🔑</span>
                        <div>
                            <div class="pc-estates-banner-title">Jua Kali Unlocked!</div>
                            <div class="pc-estates-banner-sub">Your income history qualifies you for vehicle financing. Vehicles = income assets.</div>
                        </div>
                    </div>

                    <div class="pc-estates-label"><x-icon name="car" class="w-3.5 h-3.5 inline-block" /> Available Vehicles</div>
                    <div x-data="{ cyMsg: '', cyOk: true, cyBuying: null }">
                    <template x-if="cyMsg">
                        <div style="margin-bottom:10px;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;"
                             :style="cyOk ? 'background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);color:#10b981;' : 'background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171;'"
                             x-text="cyMsg"></div>
                    </template>
                    <template x-for="vehicle in (district.vehicles || [])" :key="vehicle.name">
                        <div class="pc-vehicle-card" :class="vehicle.can_afford ? '' : 'pc-vehicle-locked'">
                            <div class="pc-vehicle-header">
                                <div class="pc-vehicle-icon" x-text="vehicle.icon"></div>
                                <div class="pc-vehicle-meta">
                                    <div class="pc-vehicle-name" x-text="vehicle.name"></div>
                                    <div class="pc-vehicle-price" x-text="'KES ' + vehicle.price.toLocaleString()"></div>
                                </div>
                                <div class="pc-vehicle-gig">
                                    <div class="pc-yield-num" style="color:#FFBC00;" x-text="'KES ' + vehicle.gig_income.toLocaleString()"></div>
                                    <div class="pc-yield-label">/ mo income</div>
                                </div>
                            </div>
                            <div class="pc-property-finance">
                                <div class="pc-fin-row">
                                    <span>Deposit (20%) — pay now</span>
                                    <strong x-text="'KES ' + vehicle.deposit.toLocaleString()"></strong>
                                </div>
                                <div class="pc-fin-row">
                                    <span>Monthly loan (auto-billed)</span>
                                    <strong x-text="'KES ' + vehicle.monthly.toLocaleString()"></strong>
                                </div>
                                <div class="pc-fin-row" x-show="vehicle.total_cost">
                                    <span x-text="'Term: ' + (vehicle.months || 24) + ' game months (' + gdApprox((vehicle.months || 24) * 30) + ')'"></span>
                                    <strong style="color:#fbbf24;" x-text="'Total: KES ' + (vehicle.total_cost || 0).toLocaleString()"></strong>
                                </div>
                                <div class="pc-fin-row" style="border-top:1px solid rgba(255,188,0,0.15);margin-top:4px;padding-top:6px;">
                                    <span style="color:#FFBC00;font-weight:700;">Net income</span>
                                    <strong style="color:#FFBC00;"
                                            x-text="'KES ' + (vehicle.gig_income - vehicle.monthly).toLocaleString() + ' /mo'"></strong>
                                </div>
                            </div>
                            <div class="pc-property-lesson" x-text="'💡 ' + vehicle.lesson"></div>
                            <div style="display:flex;gap:8px;margin-top:10px;" x-show="vehicle.can_afford_cash || vehicle.can_afford">
                                <template x-if="vehicle.can_afford_cash">
                                    <button :disabled="cyBuying === vehicle.asset_id"
                                            @click="cyBuying = vehicle.asset_id; cyMsg = '';
                                                fetch('/marketplace/' + vehicle.asset_id + '/buy', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                                    credentials: 'same-origin',
                                                    body: JSON.stringify({ financing: false })
                                                })
                                                .then(r => r.json())
                                                .then(d => {
                                                    cyBuying = null;
                                                    if (d.error) { cyMsg = d.error; cyOk = false; }
                                                    else { cyMsg = '🎉 ' + vehicle.name + ' bought outright! New balance KES ' + (d.new_balance ?? 0).toLocaleString() + '.'; cyOk = true; liveBalance = d.new_balance ?? liveBalance; }
                                                })
                                                .catch(() => { cyBuying = null; cyMsg = 'Purchase failed. Try again.'; cyOk = false; })"
                                            style="flex:1;padding:9px;border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;background:linear-gradient(135deg,rgba(52,211,153,.2),rgba(52,211,153,.1));border:1px solid rgba(52,211,153,.4);color:#34d399;display:flex;align-items:center;justify-content:center;gap:6px;">
                                        <span x-show="cyBuying !== vehicle.asset_id">✓ Buy Cash</span>
                                        <span x-show="cyBuying === vehicle.asset_id">Processing...</span>
                                    </button>
                                </template>
                                <template x-if="vehicle.can_afford">
                                    <button :disabled="cyBuying === vehicle.asset_id"
                                            @click="cyBuying = vehicle.asset_id; cyMsg = '';
                                                fetch('/marketplace/' + vehicle.asset_id + '/buy', {
                                                    method: 'POST',
                                                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                                                    credentials: 'same-origin',
                                                    body: JSON.stringify({ financing: true })
                                                })
                                                .then(r => r.json())
                                                .then(d => {
                                                    cyBuying = null;
                                                    if (d.error) { cyMsg = d.error; cyOk = false; }
                                                    else { cyMsg = '🎉 ' + vehicle.name + ' financed! Deposit KES ' + (d.financing?.deposit ?? vehicle.deposit).toLocaleString() + ' paid — KES ' + (d.financing?.monthly ?? vehicle.monthly).toLocaleString() + '/game month is now on your bills.'; cyOk = true; liveBalance = d.new_balance ?? liveBalance; }
                                                })
                                                .catch(() => { cyBuying = null; cyMsg = 'Purchase failed. Try again.'; cyOk = false; })"
                                            style="flex:1;padding:9px;border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;background:linear-gradient(135deg,rgba(255,188,0,.2),rgba(255,188,0,.1));border:1px solid rgba(255,188,0,.4);color:#FFBC00;display:flex;align-items:center;justify-content:center;gap:6px;">
                                        <span x-show="cyBuying !== vehicle.asset_id" x-text="'🚀 Finance — Pay KES ' + vehicle.deposit.toLocaleString() + ' Deposit'"></span>
                                        <span x-show="cyBuying === vehicle.asset_id">Processing...</span>
                                    </button>
                                </template>
                            </div>
                            <template x-if="!vehicle.can_afford_cash && !vehicle.can_afford">
                                <div style="display:flex;gap:6px;margin-top:10px;align-items:center;">
                                    <div class="pc-property-afford-badge pc-property-afford-badge--short" style="flex:1;margin-top:0;"
                                         x-text="'Save KES ' + (vehicle.deposit - (district.balance ?? 0)).toLocaleString() + ' more'"></div>
                                    <a href="/savings" style="font-size:11px;font-weight:800;padding:7px 10px;border-radius:9px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;white-space:nowrap;text-decoration:none;">💰 Save More</a>
                                </div>
                            </template>
                        </div>
                    </template>
                    </div>

                    <div class="pc-panel-actions" style="margin-top:16px;">
                        <a href="/portfolio" class="pc-action-btn pc-action-secondary">💼 My Portfolio</a>
                    </div>
                </div>
            </template>

            {{-- ── QUEST BOARD — in-map popup with level-gated quests ── --}}
            <template x-if="district && district.slug === 'quests'">
                <div>
                    <p class="pc-panel-desc" x-text="district.description"></p>

                    {{-- Loading quests --}}
                    <div class="pc-opp-loading" x-show="questsData.loading" x-cloak>
                        <div class="pc-spinner"></div>
                    </div>

                    {{-- Quest filter tabs --}}
                    <div class="pc-opp-tabs" x-show="!questsData.loading && questsData.quests.length > 0" x-cloak>
                        <button class="pc-opp-tab"
                                :class="{ active: questsData.filter === 'all' }"
                                @click="questsData.filter = 'all'">
                            All <span x-text="questsData.quests.length"></span>
                        </button>
                        <button class="pc-opp-tab"
                                :class="{ active: questsData.filter === 'available' }"
                                @click="questsData.filter = 'available'">
                            <x-icon name="target" class="w-3.5 h-3.5 inline-block" /> Available
                        </button>
                        <button class="pc-opp-tab"
                                :class="{ active: questsData.filter === 'completed' }"
                                @click="questsData.filter = 'completed'">
                            <x-icon name="check-circle" class="w-3.5 h-3.5 inline-block" /> Completed
                        </button>
                        <template x-if="questsData.quests.some(x => x.is_previous_level && x.user_status !== 'completed')">
                            <button class="pc-opp-tab"
                                    :class="{ active: questsData.filter === 'pending_old' }"
                                    @click="questsData.filter = 'pending_old'">
                                ⏳ From Earlier Levels <span x-text="questsData.quests.filter(x => x.is_previous_level && x.user_status !== 'completed').length"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Quest list --}}
                    <div class="pc-quest-panel-list" x-show="!questsData.loading" x-cloak>
                        <template x-for="quest in filteredQuests()" :key="quest.id">
                            <div class="pc-quest-item"
                                 :class="{
                                     'pc-quest-item--locked':    quest.is_locked,
                                     'pc-quest-item--completed': quest.user_status === 'completed',
                                     'pc-quest-item--inprogress': quest.user_status === 'in_progress',
                                 }">
                                {{-- Quest header --}}
                                <div class="pc-quest-item-header">
                                    <span class="pc-quest-item-icon" x-html="pqIcon(quest.icon, 'w-5 h-5')"></span>
                                    <div class="pc-quest-item-meta">
                                        <div class="pc-quest-item-title">
                                            <span x-text="quest.title"></span>
                                            <template x-if="quest.career_badge">
                                                <span :title="'For: ' + quest.career_badge + ' career path'" style="margin-left:4px;font-size:11px;" x-text="quest.career_badge"></span>
                                            </template>
                                        </div>
                                        <div class="pc-quest-item-xp">
                                            <span x-text="'⭐ ' + quest.xp_reward.toLocaleString() + ' XP'"></span>
                                            <template x-if="quest.is_locked">
                                                <span class="pc-quest-level-req"
                                                      x-text="'🔒 Level ' + quest.min_level + ' required'"></span>
                                            </template>
                                            <template x-if="quest.is_previous_level && quest.user_status !== 'completed'">
                                                <span class="pc-quest-level-req" style="color:#FFBC00;"
                                                      x-text="'⏳ Level ' + quest.min_level + ' quest'"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="pc-quest-status-icon">
                                        <template x-if="quest.user_status === 'completed'">
                                            <span style="color:#15C77E;font-size:18px;">✓</span>
                                        </template>
                                        <template x-if="quest.user_status === 'in_progress'">
                                            <span style="color:#FFBC00;font-size:14px;">⟳</span>
                                        </template>
                                        <template x-if="quest.is_locked">
                                            <span style="color:#6B8BA4;font-size:16px;">🔒</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Description (only when not locked) --}}
                                <template x-if="!quest.is_locked">
                                    <div class="pc-quest-item-desc" x-text="quest.description"></div>
                                </template>
                                <template x-if="quest.is_locked">
                                    <div class="pc-quest-item-desc" style="opacity:0.4;">
                                        Reach level <span x-text="quest.min_level"></span> to unlock this quest.
                                    </div>
                                </template>

                                {{-- Action hint (when in progress) --}}
                                <template x-if="quest.user_status === 'in_progress' && quest.hint">
                                    <div class="pc-quest-hint-box">
                                        <div class="pc-quest-hint-label"><x-icon name="pin" class="w-3 h-3 inline-block" /> How to complete:</div>
                                        <div x-text="quest.hint"></div>
                                    </div>
                                </template>

                                {{-- Multi-step checklist --}}
                                <template x-if="quest.triggers && quest.triggers.length > 1">
                                    <div style="margin-top:10px;">
                                        <div style="font-size:.7rem;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Quest Steps</div>
                                        <template x-for="(step, i) in quest.triggers" :key="i">
                                            <div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);">
                                                <div :style="(quest.step_progress && quest.step_progress[i]) ? 'color:#15C77E;font-size:1rem;' : 'color:rgba(255,255,255,.2);font-size:1rem;'">
                                                    <span x-show="quest.step_progress && quest.step_progress[i]">✅</span>
                                                    <span x-show="!quest.step_progress || !quest.step_progress[i]">⬜</span>
                                                </div>
                                                <span :style="(quest.step_progress && quest.step_progress[i]) ? 'color:#6ee7b7;font-size:.78rem;text-decoration:line-through;opacity:.6;' : 'color:#d1d5db;font-size:.78rem;'" x-text="step.label || step.type"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Action buttons --}}
                                <template x-if="!quest.is_locked && quest.user_status === 'available'">
                                    <button class="pc-course-btn pc-btn-enroll"
                                            @click="startQuestInPanel(quest)">
                                        🎯 Start Quest
                                    </button>
                                </template>
                                <template x-if="!quest.is_locked && quest.user_status === 'in_progress'">
                                    <button class="pc-course-btn" style="background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);color:#a5b4fc;cursor:pointer;text-align:center;font-size:.78rem;font-weight:800;padding:9px;border-radius:10px;width:100%;"
                                            @click="openQuestPopup(quest)"
                                            title="Tap to re-read how to complete this quest">
                                        ⟳ In Progress — tap to see how to complete
                                    </button>
                                </template>
                                <template x-if="quest.user_status === 'completed'">
                                    <div class="pc-quest-done-label"><x-icon name="trophy" class="w-3 h-3 inline-block" /> Completed!</div>
                                </template>
                            </div>
                        </template>

                        <template x-if="filteredQuests().length === 0 && !questsData.loading">
                            <div style="text-align:center;padding:24px;color:var(--pc-muted);font-size:13px;">
                                No quests in this category yet.
                            </div>
                        </template>
                    </div>

                </div>
            </template>

            {{-- Locked district --}}
            <template x-if="district && district.status === 'locked'">
                <div class="pc-panel-locked">
                    <div class="pc-locked-icon">🔒</div>
                    <div class="pc-locked-title">Zone Locked</div>
                    <div class="pc-locked-hint" x-text="district.unlock_hint"></div>
                </div>
            </template>

            {{-- Coming soon district --}}
            <template x-if="district && district.status === 'coming-soon'">
                <div class="pc-panel-locked">
                    <div class="pc-locked-icon">🚧</div>
                    <div class="pc-locked-title">Coming Soon</div>
                    <div class="pc-locked-hint" x-text="district.description"></div>
                </div>
            </template>

        </div>

        {{-- Loading state --}}
        <div class="pc-panel-loading" x-show="loading" x-cloak>
            <div class="pc-spinner"></div>
        </div>

    </div>

    {{-- Bottom nav — the exact same shared bar every other page uses
         (Home/City/Arcade/Life/Menu), so mobile navigation is consistent
         everywhere instead of the World map having its own one-off bar.
         The map-specific shortcuts this used to carry (Market/Skills/Quests)
         are still one tap away via the Menu sheet; the character sidebar has
         its own dedicated toggle in the top HUD (the ☰ button), independent
         of this bar. --}}
    <x-mobile-bottom-nav active="city" />

    {{-- ── BOTTOM JOURNEY BAR — real admin-configured Journey Milestones
         (GameSet Hub → Journey Milestones), same data/logic as /life timeline ── --}}
    @php
        $milestones = $journeyMilestones ?? [];
        $doneIdxs   = collect($milestones)->filter(fn ($m) => $m['achieved'] ?? false)->keys();
        $currentIdx = $doneIdxs->isNotEmpty() ? $doneIdxs->max() : 0;
    @endphp
    @if(!empty($milestones))
    <div class="pc-journey-bar" id="pc-journey-bar">
        <div class="pc-journey-scroll">
            @foreach($milestones as $i => $milestone)
            @php
                $isDone    = $milestone['achieved'] ?? false;
                $isCurrent = $i === $currentIdx;
                $isLocked  = !$isDone && !$isCurrent;
            @endphp
            <div class="pc-journey-milestone {{ $isDone ? 'pc-jm-done' : '' }} {{ $isCurrent ? 'pc-jm-current' : '' }} {{ $isLocked ? 'pc-jm-locked' : '' }}"
                 title="{{ $milestone['description'] ?? '' }}">
                @if($i > 0)
                <div class="pc-jm-connector {{ $isDone ? 'pc-jm-connector--done' : '' }}"></div>
                @endif
                <div class="pc-jm-badge">
                    <span class="pc-jm-icon">{{ $milestone['icon'] ?? '⭐' }}</span>
                    @if($isDone && !$isCurrent)
                    <span class="pc-jm-check">✓</span>
                    @endif
                </div>
                <div class="pc-jm-label">{{ $milestone['title'] ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Auto-scroll journey bar to current milestone --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var current = document.querySelector('.pc-jm-current');
        if (current) {
            current.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    });
    </script>

    {{-- Quest Complete celebration styles --}}
<style>
@keyframes pc-bounce {
    from { transform: translateY(0); }
    to   { transform: translateY(-8px); }
}
@keyframes pc-mood-glow {
  0%   { box-shadow: 0 0 0 rgba(255,107,53,0); border-color: rgba(255,107,53,.2); }
  30%  { box-shadow: 0 0 24px rgba(255,107,53,.6); border-color: rgba(255,107,53,.6); }
  100% { box-shadow: 0 0 0 rgba(255,107,53,0); border-color: rgba(255,107,53,.2); }
}
@keyframes pc-mood-emoji-pop {
  0%   { transform: scale(1) rotate(0); }
  35%  { transform: scale(1.45) rotate(-8deg); }
  65%  { transform: scale(0.92) rotate(6deg); }
  100% { transform: scale(1) rotate(0); }
}
.pc-mood-pulse { animation: pc-mood-glow .9s ease; }
.pc-mood-emoji-pop { animation: pc-mood-emoji-pop .6s ease; }
@keyframes qc-particle-fly {
  0%   { transform: translate(0,0) scale(1); opacity: 1; }
  100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; }
}
@keyframes qc-glow-pulse {
  0%, 100% { text-shadow: 0 0 20px rgba(251,191,36,0.6), 0 0 40px rgba(245,158,11,0.4); transform: scale(1); }
  50%       { text-shadow: 0 0 40px rgba(251,191,36,0.9), 0 0 80px rgba(245,158,11,0.7); transform: scale(1.08); }
}
@keyframes qc-fade-in-up {
  from { opacity: 0; transform: translateY(20px) scale(0.95); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
.qc-overlay {
    position: fixed; inset: 0; z-index: 10000;
    background: rgba(0,0,0,0.85);
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
    overflow-y: auto;
    overscroll-behavior: contain;
}
.qc-card {
    background: linear-gradient(155deg, #110e2a, #0c0a1e);
    border: 1px solid rgba(99,102,241,0.45);
    border-radius: 1.25rem;
    width: 100%; max-width: 380px;
    margin: auto;
    padding: 32px 24px 24px;
    text-align: center;
    position: relative;
    overflow: hidden;
    animation: qc-fade-in-up 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.qc-card::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 50% 0%, rgba(99,102,241,0.18) 0%, transparent 65%);
    pointer-events: none;
}
.qc-icon {
    font-size: 72px;
    line-height: 1;
    margin-bottom: 12px;
    display: block;
    animation: qc-glow-pulse 2s ease-in-out infinite;
}
.qc-eyebrow {
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
    margin-bottom: 6px;
}
.qc-title {
    font-size: 18px;
    font-weight: 800;
    color: #f1f5f9;
    margin-bottom: 10px;
    line-height: 1.3;
}
.qc-lesson {
    font-size: 13px;
    color: #94a3b8;
    font-style: italic;
    line-height: 1.6;
    margin-bottom: 18px;
    padding: 0 4px;
}
.qc-rewards {
    display: flex; gap: 10px; justify-content: center; margin-bottom: 20px;
}
.qc-xp-badge {
    padding: 6px 14px; border-radius: 9999px;
    background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.35);
    color: #c084fc; font-size: 13px; font-weight: 800;
}
.qc-kes-badge {
    padding: 6px 14px; border-radius: 9999px;
    background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3);
    color: #34d399; font-size: 13px; font-weight: 800;
}
.qc-btn {
    width: 100%; padding: 13px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(139,92,246,0.25));
    border: 1px solid rgba(99,102,241,0.45);
    color: #a5b4fc; font-size: 15px; font-weight: 800;
    cursor: pointer; transition: all 0.2s;
    font-family: inherit;
}
.qc-btn:hover { background: linear-gradient(135deg, rgba(99,102,241,0.45), rgba(139,92,246,0.38)); transform: translateY(-1px); }
/* Particle stars */
.qc-particles {
    position: absolute; inset: 0; pointer-events: none; overflow: hidden;
}
.qc-star {
    position: absolute;
    width: 8px; height: 8px;
    border-radius: 50%;
    top: 50%; left: 50%;
    animation: qc-particle-fly 1.2s ease-out both;
}

/* Mobile-first shrink for the quest-complete / challenge-result popup —
   same technique as the Tailwind sm: pattern elsewhere: base (here, the
   @media below) is the smaller mobile size, ≥640px restores the original. */
@media (max-width: 639px) {
    .qc-card { padding: 20px 16px 16px; }
    .qc-icon { font-size: 44px; margin-bottom: 8px; }
    .qc-eyebrow { font-size: 10px; }
    .qc-title { font-size: 15px; margin-bottom: 8px; }
    .qc-lesson { font-size: 12px; margin-bottom: 14px; }
    .qc-rewards { gap: 8px; margin-bottom: 14px; }
    .qc-xp-badge, .qc-kes-badge { padding: 5px 10px; font-size: 11px; }
    .qc-btn { padding: 11px; font-size: 13px; }
}

/* Share trade celebration card */
@keyframes stc-pop { 0% { transform: scale(0.7); } 60% { transform: scale(1.15); } 100% { transform: scale(1); } }
@keyframes stc-shrink { from { width: 100%; } to { width: 0%; } }
.share-trade-card {
    margin-top: 10px; padding: 10px 12px; border-radius: 12px; position: relative; overflow: hidden;
}
.share-trade-card.ok  { background: linear-gradient(135deg, rgba(16,185,129,0.14), rgba(53,195,240,0.06)); border: 1px solid rgba(16,185,129,0.35); }
.share-trade-card.bad { background: linear-gradient(135deg, rgba(239,68,68,0.14), rgba(239,68,68,0.05)); border: 1px solid rgba(239,68,68,0.35); }
.stc-icon { font-size: 22px; line-height: 1; animation: stc-pop 0.5s cubic-bezier(0.34,1.56,0.64,1) both; }
.stc-basics { font-size: 11px; color: #e5e7eb; margin-top: 8px; padding: 8px 10px; border-radius: 8px;
    background: rgba(53,195,240,.1); border: 1px solid rgba(53,195,240,.25); line-height: 1.5; }
.stc-edu { font-size: 11px; color: #d1d5db; margin-top: 6px; line-height: 1.4; }
.stc-bar { height: 3px; margin-top: 8px; background: rgba(255,255,255,0.08); border-radius: 2px; overflow: hidden; }
.stc-bar-fill { height: 100%; background: rgba(255,255,255,0.35); animation: stc-shrink 6s linear forwards; }

/* Multi-column layout for share/deal card lists — the Equity Square panel
   is a full-bleed bottom sheet, so on wide screens a single stacked column
   stretches each card edge-to-edge. auto-fill naturally collapses back to
   one column on narrow/mobile widths without needing a breakpoint. */
.pc-card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px; align-items: start; }

/* Equity Square share cards (Market + My Shares) */
.share-card { border-radius: 14px; padding: 12px 14px; margin-bottom: 10px;
    background: linear-gradient(145deg, rgba(255,255,255,.045), rgba(255,255,255,.015));
    border: 1px solid rgba(255,255,255,.08); transition: transform .18s ease, border-color .18s ease; }
.share-card:hover { transform: translateY(-2px); border-color: rgba(53,195,240,.32); }
.share-card-top { display: flex; align-items: flex-start; gap: 12px; }
/* Sized to actually show a company logo, not just a symbolic icon glyph —
   the old 38px box cropped real photos/crests down to an illegible smear. */
.share-icon-badge { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center;
    justify-content: center; font-size: 22px; flex-shrink: 0; border: 1px solid; overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,.25); background-color: rgba(255,255,255,.03); }
.share-icon-badge img { width: 100%; height: 100%; object-fit: cover; }
.share-card-info { flex: 1; min-width: 0; }
.share-name { font-size: 13px; font-weight: 800; color: #f9fafb; margin-bottom: 4px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.share-card-tags { display: flex; gap: 4px; flex-wrap: wrap; }
.share-tag { font-size: 9px; font-weight: 800; padding: 2px 7px; border-radius: 999px;
    border: 1px solid rgba(255,255,255,.14); background: rgba(255,255,255,.05); color: #9ca3af; white-space: nowrap; }
.share-price-block { text-align: right; flex-shrink: 0; }
.share-price { font-size: 16px; font-weight: 900; color: #f9fafb; line-height: 1.1; }
.share-change-chip { display: inline-flex; align-items: center; gap: 2px; font-size: 10px; font-weight: 800;
    padding: 2px 7px; border-radius: 999px; margin-top: 4px; }
.share-change-chip.up { background: rgba(16,185,129,.16); color: #34d399; }
.share-change-chip.down { background: rgba(239,68,68,.16); color: #f87171; }
.share-change-chip.flat { background: rgba(255,255,255,.07); color: #9ca3af; }
.share-card-mid { display: flex; align-items: center; justify-content: space-between; gap: 10px;
    margin-top: 11px; padding-top: 11px; border-top: 1px solid rgba(255,255,255,.06); }
.share-trend-label { font-size: 9px; font-weight: 800; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; flex-shrink: 0; }
.share-candles { display: flex; align-items: center; gap: 4px; height: 34px; flex-shrink: 0; }
.share-event { font-size: 10.5px; color: #d1d5db; margin-top: 9px; padding: 7px 10px; border-radius: 8px;
    background: rgba(255,255,255,.035); border-left: 2px solid rgba(53,195,240,.4); line-height: 1.4; }
.share-card-actions { display: flex; gap: 8px; align-items: center; justify-content: space-between; margin-top: 11px; padding-top: 11px;
    border-top: 1px solid rgba(255,255,255,.06); }
.share-qty-input { flex: 0 1 76px; width: 76px; text-align: center; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.14);
    border-radius: 9px; padding: 7px 5px; color: #fff; font-size: 11.5px; min-width: 0; transition: border-color .15s; }
.share-qty-input:focus { outline: none; border-color: rgba(53,195,240,.5); }
.share-buy-btn { flex: 0 1 auto; min-width: 108px; padding: 8px 20px; border-radius: 9px; font-size: 12px; font-weight: 800;
    background: linear-gradient(135deg,#0891b2,#0e7490); color: #fff; border: none; cursor: pointer;
    white-space: nowrap; box-shadow: 0 2px 10px rgba(8,145,178,.35); transition: box-shadow .15s, transform .15s; }
.share-buy-btn:hover:not(:disabled) { box-shadow: 0 4px 16px rgba(8,145,178,.5); transform: translateY(-1px); }
.share-buy-btn:disabled { opacity: .5; cursor: not-allowed; }
.share-sell-btn { flex: 0 1 auto; min-width: 108px; padding: 8px 20px; border-radius: 9px; font-size: 12px; font-weight: 800;
    background: rgba(239,68,68,.16); color: #f87171; border: 1px solid rgba(239,68,68,.32); cursor: pointer;
    white-space: nowrap; transition: background .15s; }
.share-sell-btn:hover:not(:disabled) { background: rgba(239,68,68,.24); }
.share-sell-btn:disabled { opacity: .5; cursor: not-allowed; }
.share-gain-pill { text-align: right; flex-shrink: 0; }
.share-gain-val { font-size: 13px; font-weight: 800; }
.share-gain-pct { font-size: 10px; color: #6b7280; margin-top: 1px; }
.share-estimate { font-size: 10.5px; color: #9ca3af; margin-top: 6px; text-align: center; }
.share-estimate strong { color: #e5e7eb; font-weight: 800; }
</style>

{{-- ══════════════════════════════════════
     QUEST COMPLETE CELEBRATION (Task A)
══════════════════════════════════════ --}}
<div class="qc-overlay"
     x-show="questComplete.show"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="questComplete.show = false">
    <div class="qc-card">
        {{-- Particle stars --}}
        <div class="qc-particles">
            <div class="qc-star" style="background:#fbbf24;--tx:-90px;--ty:-80px;animation-delay:.05s;"></div>
            <div class="qc-star" style="background:#a78bfa;--tx:85px;--ty:-95px;animation-delay:.12s;"></div>
            <div class="qc-star" style="background:#34d399;--tx:-70px;--ty:90px;animation-delay:.18s;width:6px;height:6px;"></div>
            <div class="qc-star" style="background:#fbbf24;--tx:100px;--ty:75px;animation-delay:.08s;width:10px;height:10px;border-radius:2px;"></div>
            <div class="qc-star" style="background:#f472b6;--tx:-115px;--ty:20px;animation-delay:.22s;width:6px;height:6px;"></div>
            <div class="qc-star" style="background:#60a5fa;--tx:110px;--ty:-30px;animation-delay:.15s;width:6px;height:6px;"></div>
            <div class="qc-star" style="background:#fbbf24;--tx:-40px;--ty:-120px;animation-delay:.25s;width:12px;height:12px;border-radius:2px;"></div>
            <div class="qc-star" style="background:#a78bfa;--tx:50px;--ty:115px;animation-delay:.10s;width:7px;height:7px;"></div>
            <div class="qc-star" style="background:#34d399;--tx:130px;--ty:50px;animation-delay:.20s;width:5px;height:5px;"></div>
            <div class="qc-star" style="background:#f59e0b;--tx:-130px;--ty:-45px;animation-delay:.07s;width:9px;height:9px;border-radius:2px;"></div>
        </div>

        <span class="qc-icon w-8 h-8 sm:w-10 sm:h-10" x-html="pqIcon(questComplete.icon, 'w-8 h-8 sm:w-10 sm:h-10')"></span>
        <div class="qc-eyebrow">QUEST COMPLETE!</div>
        <div class="qc-title" x-text="questComplete.title"></div>
        <div class="qc-lesson" x-show="questComplete.lesson" x-text="questComplete.lesson"></div>
        <div class="qc-rewards">
            <span class="qc-xp-badge" x-show="questComplete.xp > 0" x-text="'⚡ +' + questComplete.xp + ' XP'"></span>
            <span class="qc-kes-badge" x-show="questComplete.kes > 0" x-text="'💵 KES ' + questComplete.kes.toLocaleString()"></span>
        </div>
        <button class="qc-btn" @click="questComplete.show = false">Awesome! 🎉</button>
    </div>
</div>

{{-- ══════════════════════════════════════
     CHALLENGE RESULT (win / loss / cancelled)
══════════════════════════════════════ --}}
<div class="qc-overlay"
     x-show="challengeResult.show"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="closeChallengeResult()">
    <div class="qc-card">
        <template x-if="challengeResult.isWinner">
            <div class="qc-particles">
                <div class="qc-star" style="background:#fbbf24;--tx:-90px;--ty:-80px;animation-delay:.05s;"></div>
                <div class="qc-star" style="background:#f59e0b;--tx:85px;--ty:-95px;animation-delay:.12s;"></div>
                <div class="qc-star" style="background:#fbbf24;--tx:-70px;--ty:90px;animation-delay:.18s;width:6px;height:6px;"></div>
                <div class="qc-star" style="background:#f59e0b;--tx:100px;--ty:75px;animation-delay:.08s;width:10px;height:10px;border-radius:2px;"></div>
                <div class="qc-star" style="background:#fbbf24;--tx:-115px;--ty:20px;animation-delay:.22s;width:6px;height:6px;"></div>
            </div>
        </template>

        <span class="qc-icon" x-text="challengeResult.icon"></span>
        <div class="qc-eyebrow" x-text="challengeResult.isWinner ? 'YOU WON!' : 'CHALLENGE OVER'"></div>
        <div class="qc-title" x-text="challengeResult.title"></div>
        <div class="qc-lesson" x-show="challengeResult.body" x-text="challengeResult.body"></div>
        <button class="qc-btn" @click="closeChallengeResult()" x-text="challengeResult.isWinner ? 'Awesome! 🎉' : 'Got it'"></button>
    </div>
</div>

    {{-- Step-complete flash (multi-trigger quests) --}}
    <div x-show="stepFlash" x-cloak x-transition
         style="position:fixed;bottom:120px;left:50%;transform:translateX(-50%);z-index:9000;
                background:rgba(16,185,129,0.95);color:#fff;padding:10px 20px;border-radius:9999px;
                font-size:.85rem;font-weight:800;font-family:'Figtree',sans-serif;
                box-shadow:0 4px 20px rgba(0,0,0,0.4);white-space:nowrap;"
         x-text="'✓ ' + stepFlash">
    </div>

    {{-- Panel backdrop --}}
    <div class="pc-backdrop"
         x-show="panelOpen"
         x-cloak
         @click="closePanel()"></div>

    {{-- ══════════════════════════════════════
         WORLD EVENT OVERLAY (Phase 8)
         Career / Asset / Opportunity / Cost events
         Populated by EventEngine via WorldController
    ══════════════════════════════════════ --}}
    <div class="pc-event-overlay"
         x-show="worldEvent.show"
         x-cloak
         :class="'pc-event--' + worldEvent.type">

        <div class="pc-event-popup">
            <div class="pc-event-accent"></div>

            {{-- Choice state --}}
            <template x-if="!worldEvent.resolved">
                <div class="pc-event-body">
                    <div class="pc-event-eyebrow">
                        <span x-text="worldEvent.category_label"></span>
                        {{-- Expiry countdown badge for opportunity events --}}
                        <template x-if="worldEvent.expires_in_days !== null">
                            <span class="pc-event-expiry"
                                  :class="worldEvent.expires_in_days <= 2 ? 'pc-event-expiry--urgent' : ''"
                                  x-text="'⏳ ' + worldEvent.expires_in_days + (worldEvent.expires_in_days === 1 ? ' game day' : ' game days') + ' left (' + gdApprox(worldEvent.expires_in_days) + ')'">
                            </span>
                        </template>
                    </div>
                    <div class="pc-event-icon" x-text="worldEvent.icon"></div>
                    <div class="pc-event-title" x-text="worldEvent.title"></div>
                    <div class="pc-event-desc"  x-text="worldEvent.description"></div>

                    {{-- Impact preview (optional KES impact chips) --}}
                    <template x-if="worldEvent.impact_chips && worldEvent.impact_chips.length > 0">
                        <div class="pc-event-impact">
                            <template x-for="chip in worldEvent.impact_chips" :key="chip.label">
                                <div class="pc-event-impact-chip">
                                    <div class="pc-event-impact-val"
                                         :style="'color:' + chip.color"
                                         x-text="chip.value"></div>
                                    <div class="pc-event-impact-label" x-text="chip.label"></div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Choice buttons --}}
                    <div class="pc-event-choices">
                        <template x-for="choice in worldEvent.choices" :key="choice.id">
                            <button class="pc-event-choice"
                                    @click="resolveWorldEvent(choice.id)">
                                <span class="pc-event-choice-icon" x-text="choice.icon"></span>
                                <div class="pc-event-choice-meta">
                                    <span x-text="choice.label"></span>
                                    <span class="pc-event-choice-outcome" x-text="choice.outcome_hint"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- Dismiss (skippable events only) --}}
                    <template x-if="worldEvent.dismissable">
                        <div class="pc-event-dismiss">
                            <button class="pc-event-dismiss-btn"
                                    @click="dismissWorldEvent()">Not now →</button>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Result state (after choice made) --}}
            <template x-if="worldEvent.resolved">
                <div class="pc-event-result">
                    <div class="pc-event-result-icon" x-text="worldEvent.result_icon"></div>
                    <div class="pc-event-result-title" x-text="worldEvent.result_title"></div>
                    <div class="pc-event-result-balance"
                         :style="'color:' + (worldEvent.result_delta >= 0 ? 'var(--pc-green)' : '#EF5350')"
                         x-text="(worldEvent.result_delta >= 0 ? '+' : '') + 'KES ' + Math.abs(worldEvent.result_delta).toLocaleString()">
                    </div>
                    <button class="pc-event-result-ok"
                            @click="dismissWorldEvent()">Continue →</button>
                </div>
            </template>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         BADGE POPUP OVERLAY (Phase 4)
    ══════════════════════════════════════ --}}
    <div class="pc-badge-overlay"
         x-show="badge.show"
         x-cloak
         @click.self="dismissBadge()">

        {{-- Glow ring behind popup --}}
        <div class="pc-badge-ring"
             :style="`background: radial-gradient(circle, ${badge.color}22 0%, transparent 70%); border: 1px solid ${badge.color}30;`">
        </div>

        <div class="pc-badge-popup">

            {{-- Decorative star particles --}}
            <div class="pc-badge-stars">
                @foreach([[8,12],[92,8],[15,85],[88,80],[50,5],[20,45],[78,55],[35,92],[65,20],[42,68]] as $star)
                <div class="pc-star"
                     style="left:{{ $star[0] }}%;top:{{ $star[1] }}%;animation-delay:{{ $loop->index * 0.17 }}s;"></div>
                @endforeach
            </div>

            <div class="pc-badge-eyebrow">Achievement Unlocked</div>

            <div class="pc-badge-big-icon w-14 h-14"
                 x-html="pqIcon(badge.icon, 'w-14 h-14')"
                 :style="`color: ${badge.color};`"></div>

            <div class="pc-badge-popup-name" x-text="badge.name"></div>
            <div class="pc-badge-popup-desc"  x-text="badge.desc"></div>

            <div class="pc-badge-rewards" x-show="badge.rewards && badge.rewards.length">
                <template x-for="chip in badge.rewards" :key="chip.label">
                    <div class="pc-reward-chip"
                         :style="`color:${chip.color}; background:${chip.color}18; border-color:${chip.color}40;`"
                         x-text="chip.label"></div>
                </template>
            </div>

            <div class="pc-badge-next" x-show="badge.next && !badge.chain">
                Next: <strong x-text="badge.next"></strong> unlocked!
            </div>
            <div class="pc-badge-next" x-show="badge.chain" style="color:var(--pc-gold);">
                🏆 Mission chain complete — salary incoming!
            </div>

            <button class="pc-badge-continue" @click="dismissBadge()">
                Continue Playing →
            </button>
        </div>
    </div>

    {{-- Quest Complete Overlay (Phase 15) — replaced by qc-overlay above (Task A) --}}

    {{-- ══════════════════════════════════════
         MARKET WATCH DETAIL POPUP
         Full bulletin text + which shares it names, so a player can
         actually act on it in the Market before it resolves.
    ══════════════════════════════════════ --}}
    <div class="pc-news-overlay"
         x-show="newsDetail.show"
         x-cloak
         @click.self="closeNewsDetail()">
        <div class="pc-news-popup">
            <button class="pc-news-close" @click="closeNewsDetail()">✕</button>

            <div class="pc-news-eyebrow" :style="newsDetail.item ? 'color:' + newsTimeBadge(newsDetail.item.time_state).color : ''" x-text="newsDetail.item ? newsTimeBadge(newsDetail.item.time_state).label : 'Market Watch'"></div>
            <div class="pc-news-icon" x-text="newsDetail.item ? newsTimeBadge(newsDetail.item.time_state).icon : '📰'"></div>
            <div class="pc-news-headline" x-text="newsDetail.item ? newsDetail.item.headline : ''"></div>
            <div class="pc-news-body" x-text="newsDetail.item ? newsDetail.item.flavor : ''"></div>

            <template x-if="newsDetail.item && newsDetail.item.lesson">
                <div class="pc-news-tip">
                    <span class="pc-news-tip-label">💡 Financial tip</span>
                    <span x-text="newsDetail.item.lesson"></span>
                </div>
            </template>

            <template x-if="newsDetail.item && newsDetail.item.affected_shares && newsDetail.item.affected_shares.length > 0">
                <div class="pc-news-subject">
                    <div class="pc-news-subject-label">Which shares this is about</div>
                    <div class="pc-news-subject-chips">
                        <template x-for="s in newsDetail.item.affected_shares" :key="s.symbol">
                            <span class="pc-news-chip">
                                <span x-text="s.icon"></span>
                                <span x-text="s.name + ' (' + s.symbol + ')'"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="newsDetail.item && newsDetail.item.status !== 'resolved'">
                <p class="pc-news-hint">
                    Not every story pans out — read it, form a guess, and decide whether to buy or sell in the Market before it resolves.
                </p>
            </template>

            <button class="pc-news-cta" @click="closeNewsDetail(); sessionStorage.setItem('pc_eq_tab_intent', 'market'); walkToDistrict('bank');">
                Go to Market →
            </button>
        </div>
    </div>

</div>{{-- /pc-root --}}

@include('partials.game-calendar')

</x-layouts.world>
