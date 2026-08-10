<x-app-layout>
<style>
[x-cloak]{display:none!important}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes popIn{0%{opacity:0;transform:scale(.5)}60%{transform:scale(1.08)}100%{opacity:1;transform:scale(1)}}
@keyframes shimmer{0%{background-position:-200% center}100%{background-position:200% center}}
@keyframes spin-ring{to{transform:rotate(360deg)}}
@keyframes glow-pulse{0%,100%{filter:drop-shadow(0 0 10px rgba(99,102,241,.4))}50%{filter:drop-shadow(0 0 22px rgba(99,102,241,.7))}}
@keyframes pulse-border{0%,100%{box-shadow:0 0 0 0 rgba(99,102,241,.4)}50%{box-shadow:0 0 0 6px rgba(99,102,241,0)}}

body{background:#07060f;}

.cover-wrapper{height:200px;position:relative;}
.cover-inner{position:absolute;inset:0;border-radius:0 0 2rem 2rem;overflow:hidden;}
.cover-wrapper img{width:100%;height:100%;object-fit:cover;object-position:center 35%;}
.cover-fade{position:absolute;inset:0;background:linear-gradient(180deg,rgba(7,6,15,.05) 0%,rgba(7,6,15,.7) 100%);}
.cover-gradient{width:100%;height:100%;background:linear-gradient(135deg,rgba(99,102,241,.35),rgba(139,92,246,.25),rgba(245,158,11,.15),rgba(16,185,129,.12));}

.avatar-anchor{position:absolute;bottom:-52px;left:24px;z-index:20;animation:glow-pulse 4s ease-in-out infinite;}
@media(max-width:640px){.avatar-anchor{left:50%;transform:translateX(-50%);}}

.av-outer{position:relative;width:108px;height:108px;border-radius:50%;cursor:default;}
.av-spin{position:absolute;inset:0;border-radius:50%;background:conic-gradient(from 0deg,#6366f1 0%,#a78bfa 30%,#f472b6 55%,#f59e0b 80%,#6366f1 100%);animation:spin-ring 5s linear infinite;}
.av-gap{position:absolute;inset:4px;border-radius:50%;background:#07060f;z-index:2;}
.av-inner{position:absolute;inset:8px;border-radius:50%;z-index:3;background:linear-gradient(135deg,#6366f1,#a78bfa);overflow:hidden;display:flex;align-items:center;justify-content:center;}
.av-inner img{width:100%;height:100%;object-fit:cover;}
.av-initials{color:#fff;font-size:2.5rem;font-weight:900;}
.lv-pin{position:absolute;bottom:2px;right:2px;z-index:30;min-width:28px;height:28px;border-radius:9999px;padding:0 .3rem;background:linear-gradient(135deg,#f59e0b,#fbbf24);border:3px solid #07060f;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:900;color:#0c0a1a;}

.profile-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:1.5rem;padding:1.5rem;}
.stat-pill{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:.875rem;padding:.7rem 1rem;display:flex;flex-direction:column;align-items:center;gap:.2rem;transition:all .3s;min-width:80px;}
.stat-pill:hover{background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.3);transform:translateY(-2px);}
.stat-val{font-size:1.3rem;font-weight:900;background:linear-gradient(135deg,#818cf8,#c4b5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.stat-lbl{font-size:.58rem;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;font-weight:700;}

.xp-bar{width:100%;height:6px;background:rgba(255,255,255,.07);border-radius:9999px;overflow:hidden;}
.xp-fill{height:100%;background:linear-gradient(90deg,#6366f1,#a78bfa);border-radius:9999px;transition:width 1.4s ease;}

.badge-item{display:flex;flex-direction:column;align-items:center;gap:.5rem;animation:popIn .4s ease both;}
.badge-circle{width:68px;height:68px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.85rem;transition:transform .3s,box-shadow .3s;}
.badge-circle:hover{transform:scale(1.2);}
.badge-circle-plain{background:transparent!important;border:none!important;box-shadow:none!important;}
.badge-circle-plain img{width:82%!important;height:82%!important;filter:drop-shadow(0 2px 8px rgba(0,0,0,.45));}
.badge-name{font-size:.6rem;color:#9ca3af;text-align:center;max-width:72px;font-weight:600;line-height:1.3;}

.chapter-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem .9rem;border-radius:9999px;font-size:.75rem;font-weight:800;letter-spacing:.03em;}

.credit-track{height:10px;background:rgba(255,255,255,.06);border-radius:9999px;overflow:hidden;position:relative;}
.credit-fill{height:100%;border-radius:9999px;transition:width 1.4s ease;}

.decision-item{display:flex;align-items:flex-start;gap:.75rem;padding:.75rem 0;border-bottom:1px solid rgba(255,255,255,.045);}
.decision-item:last-child{border-bottom:none;}

.shimmer-text{background:linear-gradient(90deg,#818cf8,#c4b5fd,#a5f3fc,#818cf8);background-size:250% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:shimmer 3s linear infinite;}
</style>

<div class="min-h-screen" style="background:#07060f;">
<div class="max-w-2xl mx-auto pb-16">

    {{-- COVER — the image/gradient/fade are clipped+rounded inside .cover-inner;
         the avatar sits OUTSIDE that clip so its bottom half isn't cut off
         where it deliberately overlaps below the cover. --}}
    <div class="cover-wrapper" style="animation:fadeUp .4s ease both;">
        <div class="cover-inner">
            @if($user->cover_photo)
                <img src="{{ $user->cover_photo }}" alt="Cover">
            @else
                <div class="cover-gradient"></div>
            @endif
            <div class="cover-fade"></div>
        </div>

        {{-- Avatar overlapping cover --}}
        <div class="avatar-anchor">
            <div class="av-outer">
                <div class="av-spin"></div>
                <div class="av-gap"></div>
                <div class="av-inner">
                    @if($user->profile_photo)
                        <img src="{{ $user->profile_photo }}" alt="{{ $user->name }}">
                    @else
                        <span class="av-initials">{{ strtoupper(substr($user->name,0,1)) }}</span>
                    @endif
                </div>
                <div class="lv-pin">Lv {{ $progress?->level ?? 1 }}</div>
            </div>
        </div>
    </div>

    {{-- IDENTITY HEADER --}}
    <div class="px-4 pt-16 pb-4" style="animation:fadeUp .4s .05s ease both;">
        <div class="flex items-start justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-black text-white">{{ $user->name }}</h1>
                @if($user->username)
                <p class="text-sm font-bold" style="color:#a5b4fc;">{{ $user->handle }}</p>
                @endif
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    {{-- Chapter badge --}}
                    <span class="chapter-badge" style="background:{{ $chapterMeta['color'] }}22;border:1px solid {{ $chapterMeta['color'] }}55;color:{{ $chapterMeta['color'] }};">
                        {{ $chapterMeta['icon'] }} {{ $chapterMeta['label'] }}
                    </span>
                    {{-- Age group --}}
                    @if($user->age_group)
                    <span class="text-xs px-2 py-1 rounded-full font-semibold" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#9ca3af;">{{ $user->age_group }} yrs</span>
                    @endif
                    {{-- Premium badge --}}
                    @if($user->hasActiveSubscription())
                    <span class="text-xs px-2 py-1 rounded-full font-bold" style="background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.35);color:#fbbf24;">💎 Premium</span>
                    @endif
                </div>
                @if($user->bio)
                <p class="text-sm text-gray-400 mt-2 max-w-xs leading-relaxed">{{ $user->bio }}</p>
                @endif
                <p class="text-xs text-gray-600 mt-1">Joined {{ $user->created_at->format('M Y') }}</p>
            </div>
            {{-- Share + Logout (Log Out only shown on YOUR OWN profile) --}}
            <div x-data="{ copied: false }" class="flex flex-col items-end gap-2">
                @if($isOwnProfile ?? false)
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl transition-all"
                            style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;">
                        🚪 Log Out
                    </button>
                </form>
                @elseif(\Illuminate\Support\Facades\Schema::hasTable('friendships') && auth()->check() && !\App\Models\Friendship::between(auth()->id(), $user->id))
                <form method="POST" action="{{ route('friends.request') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <button type="submit"
                            class="flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl transition-all hover:scale-[1.03]"
                            style="background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.4);color:#a5b4fc;">
                        👋 Add Friend
                    </button>
                </form>
                @elseif(!($isOwnProfile ?? false) && \Illuminate\Support\Facades\Schema::hasTable('friendships') && auth()->check() && \App\Models\Friendship::areFriends(auth()->id(), $user->id))
                <span class="flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;">🤝 Friends</span>
                @endif
                <button type="button"
                        @click="navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                        class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl transition-all"
                        :style="copied ? 'background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#6ee7b7;' : 'background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#6b7280;'">
                    <span x-show="!copied">🔗 Share</span>
                    <span x-show="copied">✅ Copied!</span>
                </button>
                <a href="{{ route('players.search') }}" class="text-xs text-gray-600 hover:text-indigo-400 transition-colors">← All Players</a>
            </div>
        </div>
    </div>

    {{-- XP PROGRESS BAR — reads the SAME admin-configured level_config as
         every other level display in the app (UserProgress accessors), never
         a locally hardcoded threshold/name list that drifts from the real config. --}}
    @if($progress)
    @php
        $lvl      = $progress->level ?? 1;
        $curXp    = $progress->points_total ?? 0;
        $toNext   = $progress->points_to_next_level;
        $nextXp   = $toNext > 0 ? $curXp + $toNext : null;
        $lvlPct   = max(0, min(100, $progress->level_progress_percent));
    @endphp
    <div class="px-4 mb-4" style="animation:fadeUp .4s .1s ease both;">
        <div class="flex items-center justify-between text-xs mb-1.5">
            <span class="font-bold shimmer-text">{{ $progress->level_name }}</span>
            <span class="text-gray-500">{{ number_format($curXp) }} {{ $nextXp ? '/ '.number_format($nextXp).' XP' : 'XP (max level)' }}</span>
        </div>
        <div class="xp-bar"><div class="xp-fill" style="width:{{ $lvlPct }}%;"></div></div>
        <p class="text-xs text-gray-600 mt-1">{{ $lvlPct }}% to Level {{ $lvl + 1 }}</p>
    </div>
    @endif

    {{-- STATS GRID --}}
    <div class="px-4 mb-4" style="animation:fadeUp .4s .12s ease both;">
        <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
            <div class="stat-pill">
                <span class="stat-val">{{ number_format($progress?->points_total ?? 0) }}</span>
                <span class="stat-lbl">XP</span>
            </div>
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#34d399;">Ksh {{ number_format($progress?->balance ?? 0) }}</span>
                <span class="stat-lbl">Balance</span>
            </div>
            @if(($progress?->net_worth_cache ?? 0) > 0)
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#fbbf24;">Ksh {{ number_format($progress->net_worth_cache) }}</span>
                <span class="stat-lbl">Net Worth</span>
            </div>
            @endif
            @if($portfolioValue > 0)
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#60a5fa;">Ksh {{ number_format($portfolioValue) }}</span>
                <span class="stat-lbl">Portfolio</span>
            </div>
            @endif
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#f472b6;">{{ $badges->count() }}</span>
                <span class="stat-lbl">Badges</span>
            </div>
            @if(($ownedDreams ?? collect())->count())
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#fbbf24;">{{ $ownedDreams->count() }}</span>
                <span class="stat-lbl">Dreams</span>
            </div>
            @endif
            @if(($wonChallenges ?? collect())->count())
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#fb923c;">{{ $wonChallenges->count() }}</span>
                <span class="stat-lbl">Trophies</span>
            </div>
            @endif
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#fb923c;">🔥 {{ $streak?->current_streak ?? 0 }}</span>
                <span class="stat-lbl">Streak</span>
            </div>
            @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'forum_karma') && ((int) ($user->forum_karma ?? 0)) !== 0)
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#c4b5fd;">✦ {{ number_format($user->forum_karma) }}</span>
                <span class="stat-lbl">Karma</span>
            </div>
            @endif
            @if(($progress?->net_worth_cache ?? 0) > 0)
            <div class="stat-pill">
                <span class="stat-val" style="-webkit-text-fill-color:#a5f3fc;">{{ $progress->chapterIcon() }}</span>
                <span class="stat-lbl">{{ $progress->chapterName() }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- CREDIT SCORE CARD --}}
    @if(($progress?->credit_score ?? 0) > 0)
    @php
        $cs        = $progress->credit_score;
        $csLabel   = $cs >= 740 ? 'Excellent' : ($cs >= 670 ? 'Good' : ($cs >= 580 ? 'Fair' : 'Poor'));
        $csColor   = $cs >= 740 ? '#10b981' : ($cs >= 670 ? '#22d3ee' : ($cs >= 580 ? '#f59e0b' : '#ef4444'));
        $csPct     = round(($cs - 300) / (850 - 300) * 100);
    @endphp
    <div class="px-4 mb-4" style="animation:fadeUp .4s .14s ease both;">
        <div class="profile-card">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-black text-white">Credit Score</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Financial trustworthiness rating</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-black" style="color:{{ $csColor }};">{{ $cs }}</span>
                    <p class="text-xs font-bold" style="color:{{ $csColor }};">{{ $csLabel }}</p>
                </div>
            </div>
            <div class="credit-track">
                <div class="credit-fill" style="width:{{ $csPct }}%;background:linear-gradient(90deg,#ef4444,#f59e0b {{ min(60, $csPct * .6) }}%,{{ $csColor }});"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600 mt-1">
                <span>300 Poor</span><span>580 Fair</span><span>670 Good</span><span>850 Excellent</span>
            </div>
        </div>
    </div>
    @endif

    {{-- BADGES GALLERY --}}
    @if($badges->count())
    <div class="px-4 mb-4" style="animation:fadeUp .4s .16s ease both;">
        <div class="profile-card">
            <h3 class="text-sm font-black text-white mb-4">🏅 Badges ({{ $badges->count() }})</h3>
            <div class="flex flex-wrap gap-4">
                @foreach($badges as $badge)
                <div class="badge-item" style="animation-delay:{{ $loop->index * 0.05 }}s;" title="{{ $badge->description ?? $badge->name }}">
                    <div class="badge-circle {{ $badge->image_url ? 'badge-circle-plain' : '' }}" @unless($badge->image_url) style="background:linear-gradient(135deg,{{ $badge->color ?? '#f59e0b' }}30,{{ $badge->color ?? '#f59e0b' }}12);border:2px solid {{ $badge->color ?? '#f59e0b' }}55;box-shadow:0 0 16px {{ $badge->color ?? '#f59e0b' }}28;" @endunless>
                        @if($badge->image_url)
                            <img src="{{ $badge->image_url }}" alt="{{ $badge->name }}" style="width:60%;height:60%;object-fit:contain;">
                        @else
                            <x-icon :name="$badge->icon ?? 'medal'" class="w-6 h-6" />
                        @endif
                    </div>
                    <span class="badge-name">{{ $badge->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- TROPHY CASE — owned Dreams + won Challenges --}}
    @if(($ownedDreams ?? collect())->count() || ($wonChallenges ?? collect())->count())
    <div class="px-4 mb-4" style="animation:fadeUp .4s .17s ease both;">
        <div class="profile-card">
            <h3 class="text-sm font-black text-white mb-4">🏆 Trophy Case</h3>
            <div class="flex flex-wrap gap-4">
                @foreach($wonChallenges ?? [] as $win)
                <div class="badge-item" style="animation-delay:{{ $loop->index * 0.05 }}s;" title="Won: {{ $win->challenge->title }}">
                    <div class="badge-circle" style="background:linear-gradient(135deg,#f59e0b30,#f59e0b12);border:2px solid #f59e0b55;box-shadow:0 0 16px #f59e0b28;">
                        {{ $win->challenge->template?->icon ?? '🏆' }}
                    </div>
                    <span class="badge-name">{{ $win->challenge->title }}</span>
                </div>
                @endforeach
                @foreach($ownedDreams ?? [] as $pd)
                <div class="badge-item" style="animation-delay:{{ ($loop->index + ($wonChallenges->count() ?? 0)) * 0.05 }}s;" title="{{ $pd->dream?->name }} — claimed {{ $pd->purchased_at?->format('M Y') }}">
                    <div class="badge-circle" style="background:linear-gradient(135deg,#8b5cf630,#8b5cf612);border:2px solid #8b5cf655;box-shadow:0 0 16px #8b5cf628;">
                        @if($pd->dream?->image_url)
                            <img src="{{ $pd->dream->image_url }}" alt="{{ $pd->dream->name }}" style="width:60%;height:60%;object-fit:contain;">
                        @else
                            {{ $pd->dream?->icon ?? '🌟' }}
                        @endif
                    </div>
                    <span class="badge-name">{{ $pd->dream?->name }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- RECENT DECISIONS --}}
    @if(count($recentDecisions))
    <div class="px-4 mb-4" style="animation:fadeUp .4s .18s ease both;">
        <div class="profile-card">
            <h3 class="text-sm font-black text-white mb-3">📖 Recent Financial Decisions</h3>
            <div>
                @foreach($recentDecisions as $decision)
                @if(!empty($decision))
                <div class="decision-item">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0" style="background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.3);color:#a5b4fc;">
                        {{ $loop->index + 1 }}
                    </div>
                    <div class="min-w-0 flex-1">
                        @if(is_array($decision))
                            <p class="text-sm text-gray-300 font-semibold truncate">{{ $decision['choice_label'] ?? $decision['label'] ?? 'Decision made' }}</p>
                            @if(!empty($decision['lesson']))
                            <p class="text-xs text-indigo-400 mt-0.5 leading-relaxed">💡 {{ $decision['lesson'] }}</p>
                            @endif
                            @if(!empty($decision['balance_change']))
                            <p class="text-xs mt-0.5 font-bold {{ $decision['balance_change'] > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $decision['balance_change'] > 0 ? '+' : '' }}Ksh {{ number_format($decision['balance_change']) }}
                            </p>
                            @endif
                        @else
                            <p class="text-sm text-gray-400">{{ $decision }}</p>
                        @endif
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- STREAK + ACTIVITY --}}
    @if($streak)
    <div class="px-4 mb-4" style="animation:fadeUp .4s .2s ease both;">
        <div class="profile-card">
            <h3 class="text-sm font-black text-white mb-3">🔥 Streak & Activity</h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl p-3 text-center" style="background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);">
                    <p class="text-2xl font-black text-orange-400">{{ $streak->current_streak ?? 0 }}</p>
                    <p class="text-xs text-gray-500 font-semibold mt-0.5">Day Streak</p>
                </div>
                <div class="rounded-xl p-3 text-center" style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.2);">
                    <p class="text-2xl font-black text-emerald-400">{{ $streak->longest_streak ?? 0 }}</p>
                    <p class="text-xs text-gray-500 font-semibold mt-0.5">Best Streak</p>
                </div>
                <div class="rounded-xl p-3 text-center" style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);">
                    <p class="text-2xl font-black text-indigo-400">{{ (int) $user->created_at->diffInDays(now()) }}</p>
                    <p class="text-xs text-gray-500 font-semibold mt-0.5">Days Active</p>
                </div>
            </div>
            @if($progress?->last_played_at)
            <p class="text-xs text-gray-600 mt-3 text-center">Last active {{ $progress->last_played_at->diffForHumans() }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- LIFE JOURNEY CHAPTERS --}}
    @if($progress?->life_chapter)
    @php
        $chapterCfg   = \App\Models\UserProgress::chapters();
        $chapterOrder = array_column($chapterCfg, 'key');
        $chapterHues  = ['student'=>'#6366f1','graduate'=>'#8b5cf6','hustler'=>'#f59e0b','settler'=>'#10b981','builder'=>'#06b6d4','elder'=>'#fbbf24'];
        $chapterData  = collect($chapterCfg)->keyBy('key')->map(fn ($c) => [
            'icon'  => $c['icon'],
            'label' => preg_replace('/^The\s+/i', '', $c['name']),
            'color' => $chapterHues[$c['key']] ?? '#6366f1',
        ])->all();
        $currentIdx = array_search($progress->life_chapter, $chapterOrder) ?: 0;
    @endphp
    <div class="px-4 mb-4" style="animation:fadeUp .4s .22s ease both;">
        <div class="profile-card">
            <h3 class="text-sm font-black text-white mb-4">🗺 Life Journey</h3>
            <div class="flex items-center gap-1 overflow-x-auto pb-2">
                @foreach($chapterOrder as $ci => $cKey)
                @php $cd = $chapterData[$cKey]; $done = $ci < $currentIdx; $active = $ci === $currentIdx; @endphp
                <div class="flex items-center gap-1 flex-shrink-0">
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg transition-all"
                             style="{{ $active ? "background:{$cd['color']}33;border:2px solid {$cd['color']};box-shadow:0 0 14px {$cd['color']}55;" : ($done ? "background:{$cd['color']}22;border:2px solid {$cd['color']}66;" : 'background:rgba(255,255,255,.04);border:2px solid rgba(255,255,255,.08);opacity:.4;') }}">
                            {{ $cd['icon'] }}
                        </div>
                        <span class="text-[9px] font-bold {{ $active ? 'text-white' : ($done ? 'text-gray-400' : 'text-gray-700') }}">{{ $cd['label'] }}</span>
                    </div>
                    @if(!$loop->last)
                    <div class="w-6 h-0.5 mb-5 rounded-full" style="{{ $done ? "background:{$cd['color']};" : 'background:rgba(255,255,255,.08);' }}"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
</div>
</x-app-layout>
