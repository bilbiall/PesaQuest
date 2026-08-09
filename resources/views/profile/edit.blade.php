<x-app-layout>
<style>
[x-cloak]{display:none!important}

/* ── Animations ── */
@keyframes fadeUp    { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
@keyframes popIn     { 0%{opacity:0;transform:scale(.45) rotate(-8deg)} 70%{transform:scale(1.1) rotate(2deg)} 100%{opacity:1;transform:scale(1)} }
@keyframes shimmer   { 0%{background-position:-250% center} 100%{background-position:250% center} }
@keyframes spin-ring { to{transform:rotate(360deg)} }
@keyframes glow-pulse{
  0%,100%{filter:drop-shadow(0 0 10px rgba(99,102,241,.4)) drop-shadow(0 0 25px rgba(139,92,246,.2))}
  50%    {filter:drop-shadow(0 0 20px rgba(99,102,241,.65)) drop-shadow(0 0 45px rgba(139,92,246,.35))}
}
@keyframes float-badge{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }

/* ── Hero Card ── */
.pf-hero          { position:relative;background:linear-gradient(135deg,#120f2a,#0e0c22);
  border:1px solid rgba(255,255,255,.07);border-radius:1.5rem;overflow:hidden;
  animation:fadeUp .45s ease both; }
/* Cover fills the hero card as a background layer */
.pf-hero-bg       { position:absolute;inset:0;z-index:0; }
.pf-cover-bg      { width:100%;height:100%;object-fit:cover;display:block; }
.pf-cover-bg-plain{ width:100%;height:100%;
  background:linear-gradient(135deg,rgba(99,102,241,.38),rgba(139,92,246,.24),rgba(245,158,11,.14),rgba(16,185,129,.1)); }
.pf-cover-fade    { position:absolute;inset:0;
  background:linear-gradient(160deg,rgba(7,6,15,.45) 0%,rgba(7,6,15,.88) 60%,rgba(7,6,15,.97) 100%);
  pointer-events:none; }
.pf-cover-edit    { position:absolute;top:14px;right:14px;z-index:20;
  display:flex;align-items:center;gap:.4rem;
  background:rgba(7,6,15,.72);backdrop-filter:blur(10px);
  border:1px solid rgba(255,255,255,.18);color:#fff;
  padding:.36rem .8rem;border-radius:.65rem;
  font-size:.76rem;font-weight:700;cursor:pointer;transition:background .2s; }
.pf-cover-edit:hover{ background:rgba(7,6,15,.92) }

/* ── Avatar (flex item, fully visible inside hero) ── */
.pf-av-outer      { position:relative;width:140px;height:140px;border-radius:50%;
  cursor:pointer;display:block;flex-shrink:0;
  animation:glow-pulse 3s ease-in-out infinite; }
@media(max-width:640px){ .pf-av-outer{ width:110px;height:110px; } }
.pf-av-outer .spin{ position:absolute;inset:0;border-radius:50%;
  background:conic-gradient(from 0deg,#6366f1 0%,#a78bfa 25%,#f472b6 50%,#f59e0b 75%,#6366f1 100%);
  animation:spin-ring 5s linear infinite; }
.pf-av-outer .gap { position:absolute;inset:4px;border-radius:50%;background:#0e0c22;z-index:2; }
.pf-av-outer .inner{ position:absolute;inset:8px;border-radius:50%;z-index:3;
  background:linear-gradient(135deg,#6366f1,#a78bfa);
  overflow:hidden;display:flex;align-items:center;justify-content:center; }
.pf-av-outer .inner img{ width:100%;height:100%;object-fit:cover; }
.pf-av-initials   { color:#fff;font-size:2.5rem;font-weight:900;line-height:1;user-select:none; }
@media(max-width:640px){ .pf-av-initials{ font-size:1.9rem; } }
.pf-av-edit-ov    { position:absolute;inset:0;background:rgba(0,0,0,.55);border-radius:50%;
  display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .3s;z-index:4; }
.pf-av-outer:hover .pf-av-edit-ov{ opacity:1 }
.pf-lvl-pin       { position:absolute;bottom:4px;right:4px;z-index:30;
  min-width:28px;height:28px;border-radius:9999px;padding:0 .3rem;
  background:linear-gradient(135deg,#f59e0b,#fbbf24);border:3px solid #0e0c22;
  display:flex;align-items:center;justify-content:center;
  font-size:.6rem;font-weight:900;color:#0c0a1a;white-space:nowrap;
  animation:float-badge 3s ease-in-out infinite; }

/* ── Hero main: avatar + identity side by side ── */
.pf-hero-main     { position:relative;z-index:10;
  display:flex;align-items:center;gap:1.25rem;
  padding:1.5rem 1.5rem 1.25rem; min-height:190px; }
@media(max-width:640px){ .pf-hero-main{ min-height:160px;padding:1.25rem 1rem 1rem; } }
.pf-hero-info     { flex:1;min-width:0; }

/* ── Hero stats bar ── */
.pf-hero-stats    { position:relative;z-index:10;
  display:grid;grid-template-columns:repeat(3,1fr);
  border-top:1px solid rgba(255,255,255,.07); }
.pf-hstat         { display:flex;flex-direction:column;align-items:center;padding:16px 8px;
  border-right:1px solid rgba(255,255,255,.07);transition:background .2s; }
.pf-hstat:last-child{ border-right:none }
.pf-hstat:hover   { background:rgba(255,255,255,.02) }
.pf-hstat-val     { font-size:1.25rem;font-weight:900;line-height:1.1; }
.pf-hstat-lbl     { font-size:.6rem;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;margin-top:.3rem;font-weight:600; }

/* ── Section card ── */
.pf-card          { background:rgba(13,12,28,.9);border:1px solid rgba(99,102,241,.12);
  border-radius:1.5rem;padding:1.5rem;animation:fadeUp .5s ease both; }
.pf-card:hover    { border-color:rgba(99,102,241,.22) }

/* ── Form fields ── */
.ifield           { background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.11);
  border-radius:.75rem;color:#fff;padding:.625rem .875rem;width:100%;
  font-size:.875rem;outline:none;transition:border-color .2s,box-shadow .2s; }
.ifield:focus     { border-color:rgba(99,102,241,.6);box-shadow:0 0 0 3px rgba(99,102,241,.1) }
.ifield::placeholder{ color:#374151 }
textarea.ifield   { resize:vertical;min-height:80px }

/* ── Shimmer ── */
.shimmer-val      { background:linear-gradient(90deg,#818cf8,#c4b5fd,#a5f3fc,#818cf8);
  background-size:300% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;
  background-clip:text;animation:shimmer 3.5s linear infinite;
  font-size:1rem;font-weight:700;display:inline; }

/* ── Progress bar ── */
.pbar             { width:100%;height:8px;background:rgba(255,255,255,.08);border-radius:9999px;overflow:hidden }
.pfill            { height:100%;background:linear-gradient(90deg,#6366f1,#a78bfa);border-radius:9999px;transition:width 1.2s ease }

/* ── Badges ── */
.pf-badge-grid    { display:grid;grid-template-columns:repeat(auto-fill,minmax(88px,1fr));gap:1.25rem }
.pf-badge-carousel{ display:flex;gap:1rem;overflow-x:auto;-ms-overflow-style:none;scrollbar-width:none;padding:.25rem 0 }
.pf-badge-carousel::-webkit-scrollbar{ display:none }
.pf-badge-item    { display:flex;flex-direction:column;align-items:center;animation:popIn .5s ease both }
.pf-badge-circle  { width:80px;height:80px;border-radius:50%;display:flex;align-items:center;
  justify-content:center;font-size:2rem;transition:transform .3s,box-shadow .3s;
  box-shadow:0 4px 14px rgba(0,0,0,.3);flex-shrink:0; }
.pf-badge-circle:hover{ transform:scale(1.18);box-shadow:0 8px 28px rgba(0,0,0,.5) }
.pf-badge-circle img{ width:100%;height:100%;object-fit:contain;padding:.5rem }
.pf-badge-name    { font-size:.65rem;color:#9ca3af;margin-top:.45rem;text-align:center;max-width:80px;font-weight:600;line-height:1.25 }
@media(max-width:640px){ .pf-badge-circle{width:66px;height:66px;font-size:1.7rem} }

/* ── Edit panel sidebar layout ── */
.pf-edit-wrap     { display:grid;grid-template-columns:180px 1fr;gap:0;
  background:rgba(13,12,28,.9);border:1px solid rgba(99,102,241,.12);
  border-radius:1.5rem;overflow:hidden;animation:fadeUp .5s .18s ease both; }
@media(max-width:640px){ .pf-edit-wrap{ grid-template-columns:1fr;grid-template-rows:auto 1fr } }
.pf-sidebar       { background:rgba(7,6,15,.55);border-right:1px solid rgba(255,255,255,.06);
  display:flex;flex-direction:column;padding:.75rem .5rem; }
@media(max-width:640px){ .pf-sidebar{ flex-direction:row;border-right:none;
  border-bottom:1px solid rgba(255,255,255,.06);padding:.5rem;overflow-x:auto;gap:.25rem; } }
.pf-tab-btn       { display:flex;align-items:center;gap:.55rem;padding:.65rem .9rem;
  border-radius:.75rem;background:transparent;border:none;
  color:#4b5563;font-weight:600;font-size:.8rem;cursor:pointer;
  transition:color .2s,background .2s;white-space:nowrap;width:100%;text-align:left; }
.pf-tab-btn.active{ background:rgba(99,102,241,.2);color:#a5b4fc; }
.pf-tab-btn:hover:not(.active){ background:rgba(255,255,255,.04);color:#d1d5db }
.pf-tab-btn.logout{ color:#f87171; }
.pf-tab-btn.logout:hover{ background:rgba(239,68,68,.1) }
@media(max-width:640px){ .pf-tab-btn{ padding:.5rem .75rem;width:auto } }
.pf-tab-spacer    { flex:1; }
@media(max-width:640px){ .pf-tab-spacer{ display:none } }
.pf-tab-content   { padding:1.5rem; }

/* ── Ring SVG ── */
.ring-svg         { filter:drop-shadow(0 0 8px rgba(99,102,241,.35)) }

/* ── Stat boxes ── */
.pf-stat-box      { border-radius:.85rem;padding:.875rem;text-align:center; }

/* ── Mobile bottom nav ── */
.pf-bottom-nav    { position:fixed;bottom:0;left:0;right:0;z-index:100;
  background:rgba(8,7,15,.92);backdrop-filter:blur(14px);
  border-top:1px solid rgba(255,255,255,.07);
  display:flex;align-items:center;padding:.5rem 0 .25rem; }
.pf-bnav-btn      { flex:1;display:flex;flex-direction:column;align-items:center;gap:.2rem;
  padding:.4rem .25rem;border:none;background:transparent;
  color:#6b7280;font-size:.6rem;font-weight:700;cursor:pointer;transition:color .2s; }
.pf-bnav-btn.active{ color:#a5b4fc }
.pf-bnav-btn:hover:not(.active){ color:#d1d5db }
.pf-bnav-icon     { font-size:1.15rem;line-height:1; }
</style>

<div style="background:#08070f;min-height:100vh;padding-bottom:5rem;">
<div class="max-w-4xl mx-auto pb-8 px-4 sm:px-6">

{{-- ═══════════════════════════════════════
     HERO CARD
═══════════════════════════════════════ --}}
<div class="pf-hero mt-6">

  {{-- Cover: absolute background layer --}}
  <div class="pf-hero-bg">
    @if($user->cover_photo)
      <img src="{{ $user->cover_photo }}" alt="Cover" class="pf-cover-bg" id="cover-preview">
    @else
      <div id="cover-preview" class="pf-cover-bg-plain"></div>
    @endif
    <div class="pf-cover-fade"></div>
  </div>

  {{-- Edit cover button --}}
  <label class="pf-cover-edit" title="Change cover photo">
    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
      <circle cx="12" cy="13" r="3"/>
    </svg>
    Edit Cover
    <input type="file" name="cover_photo" accept="image/*" class="hidden" form="profile-form" onchange="pfPreviewCover(this)">
  </label>

  {{-- Hero main: avatar + identity side by side --}}
  <div class="pf-hero-main">

    {{-- Avatar (fully inside the flex row — no overflow clipping) --}}
    <label class="pf-av-outer">
      <div class="spin"></div>
      <div class="gap"></div>
      <div class="inner">
        @if($user->profile_photo)
          <img src="{{ $user->profile_photo }}" alt="{{ $user->name }}" id="avatar-preview">
        @else
          <span class="pf-av-initials">{{ strtoupper(substr($user->name,0,1)) }}{{ strtoupper(substr(explode(' ',$user->name)[1]??'',0,1)) }}</span>
        @endif
        <div class="pf-av-edit-ov">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            <circle cx="12" cy="13" r="3"/>
          </svg>
        </div>
        <input type="file" name="profile_photo" accept="image/*" class="hidden" form="profile-form" onchange="pfPreviewAvatar(this)">
      </div>
      <div class="pf-lvl-pin">Lv&nbsp;{{ $progress?->level ?? 1 }}</div>
    </label>

    {{-- Identity info --}}
    <div class="pf-hero-info">
      <h1 class="text-xl sm:text-2xl font-black text-white leading-tight flex items-center gap-2">
        {{ $user->name }}
        <svg width="18" height="18" viewBox="0 0 24 24" fill="#6366f1" style="flex-shrink:0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </h1>
      <p class="text-slate-400 text-sm mt-0.5">{{ $user->email }}</p>
      <div class="flex flex-wrap gap-2 mt-2">
        @if($user->hasActiveSubscription())
          <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:rgba(16,185,129,.18);border:1px solid rgba(16,185,129,.35);color:#6ee7b7">💎 Premium</span>
        @else
          <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:rgba(245,158,11,.14);border:1px solid rgba(245,158,11,.28);color:#fbbf24">⭐ Free</span>
        @endif
        <span class="px-3 py-1 rounded-full text-xs font-semibold" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#6b7280">📅 {{ $user->created_at->format('M Y') }}</span>
      </div>
      @if($user->bio)
        <p class="text-slate-400 text-sm mt-2 leading-relaxed italic" style="max-width:340px">&ldquo;{{ $user->bio }}&rdquo;</p>
      @endif
    </div>
  </div>{{-- /pf-hero-main --}}

  {{-- Hero stats bar --}}
  <div class="pf-hero-stats">
    <div class="pf-hstat">
      <span class="pf-hstat-val" style="color:#818cf8;">⬡ {{ number_format($progress?->points_total ?? 0) }}</span>
      <span class="pf-hstat-lbl">Total XP</span>
    </div>
    <div class="pf-hstat">
      <span class="pf-hstat-val" style="color:#fbbf24;">🏆 {{ $badges->count() }}</span>
      <span class="pf-hstat-lbl">Badges</span>
    </div>
    <div class="pf-hstat">
      <span class="pf-hstat-val" style="color:#f97316;">🔥 {{ $streak?->current_streak ?? 0 }}</span>
      <span class="pf-hstat-lbl">Day Streak</span>
    </div>
  </div>

</div>{{-- /pf-hero --}}


{{-- ═══════════════════════════════════════
     2-COLUMN GRID
═══════════════════════════════════════ --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

  {{-- LEFT: Financial Life Snapshot --}}
  <div class="pf-card" style="animation-delay:.06s">
    <h2 class="text-white font-bold text-sm uppercase tracking-widest mb-4 flex items-center gap-2">
      <span style="font-size:1.1rem">💰</span> Financial Life Snapshot
    </h2>

    {{-- Chapter pill + level --}}
    <div class="flex items-center gap-3 mb-4">
      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-black"
            style="background:{{ $chapterMeta['color'] }}22;border:1px solid {{ $chapterMeta['color'] }}55;color:{{ $chapterMeta['color'] }};">
        {{ $chapterMeta['icon'] ?? '🌱' }} {{ $chapterMeta['label'] }}
      </span>
      <span class="text-xs text-gray-500">Level {{ $progress?->level ?? 1 }} &middot; {{ $progress?->level_name ?? 'Novice' }}</span>
    </div>

    {{-- 3 stat boxes: always show all 3, use — if zero --}}
    <div class="grid grid-cols-3 gap-2 mb-4">
      <div class="pf-stat-box" style="background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.15);">
        <p class="text-base font-black text-emerald-400">
          @if(($progress?->balance ?? 0) > 0) Ksh {{ number_format($progress->balance) }}
          @else <span class="text-gray-500">—</span>
          @endif
        </p>
        <p class="text-xs text-gray-500 mt-0.5 font-semibold">Balance</p>
      </div>
      <div class="pf-stat-box" style="background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.15);">
        <p class="text-base font-black text-amber-400">
          @if(($progress?->net_worth_cache ?? 0) > 0) Ksh {{ number_format($progress->net_worth_cache) }}
          @else <span class="text-gray-500">—</span>
          @endif
        </p>
        <p class="text-xs text-gray-500 mt-0.5 font-semibold">Net Worth</p>
      </div>
      <div class="pf-stat-box" style="background:rgba(96,165,250,.07);border:1px solid rgba(96,165,250,.15);">
        <p class="text-base font-black text-blue-400">
          @if($portfolioValue > 0) Ksh {{ number_format($portfolioValue) }}
          @else <span class="text-gray-500">—</span>
          @endif
        </p>
        <p class="text-xs text-gray-500 mt-0.5 font-semibold">Portfolio</p>
      </div>
    </div>

    {{-- Credit score bar --}}
    @if(($progress?->credit_score ?? 0) > 0)
    @php
      $cs      = $progress->credit_score;
      $csLabel = $cs >= 740 ? 'Excellent' : ($cs >= 670 ? 'Good' : ($cs >= 580 ? 'Fair' : 'Poor'));
      $csColor = $cs >= 740 ? '#10b981' : ($cs >= 670 ? '#22d3ee' : ($cs >= 580 ? '#f59e0b' : '#ef4444'));
      $csPct   = round(($cs - 300) / (850 - 300) * 100);
    @endphp
    <div>
      <div class="flex items-center justify-between mb-1.5">
        <span class="text-xs text-gray-400 font-semibold">Credit Score</span>
        <span class="text-sm font-black" style="color:{{ $csColor }};">{{ $cs }} — {{ $csLabel }}</span>
      </div>
      <div class="pbar">
        <div class="pfill" style="width:{{ $csPct }}%;background:linear-gradient(90deg,#ef4444 0%,#f59e0b 40%,{{ $csColor }} 100%);"></div>
      </div>
      <div class="flex justify-between text-xs text-gray-600 mt-1">
        <span>300</span><span>580 Fair</span><span>670 Good</span><span>850</span>
      </div>
    </div>
    @endif
  </div>

  {{-- RIGHT: Progression --}}
  <div class="pf-card" style="background:rgba(99,102,241,.055);border-color:rgba(99,102,241,.14);animation-delay:.1s">
    <h2 class="text-white font-bold text-sm uppercase tracking-widest mb-4 flex items-center gap-2">
      <span style="font-size:1.1rem">🚀</span> Progression
    </h2>
    <div class="flex gap-5 items-center">
      {{-- SVG level ring --}}
      <div class="flex-shrink-0">
        <svg class="ring-svg" width="130" height="130" viewBox="0 0 130 130">
          <circle cx="65" cy="65" r="58" fill="none" stroke="rgba(255,255,255,.08)" stroke-width="9"/>
          <circle cx="65" cy="65" r="58" fill="none" stroke="url(#xpGrad2)" stroke-width="9"
                  stroke-linecap="round" stroke-dasharray="365"
                  style="stroke-dashoffset:{{ 365 * (1 - max(0, min(100, $progress?->level_progress_percent ?? 0)) / 100) }}"
                  transform="rotate(-90 65 65)"/>
          <defs>
            <linearGradient id="xpGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#6366f1"/>
              <stop offset="100%" stop-color="#a78bfa"/>
            </linearGradient>
          </defs>
          <text x="65" y="59" text-anchor="middle" fill="white" font-size="26" font-weight="900" font-family="sans-serif">{{ $progress?->level ?? 1 }}</text>
          <text x="65" y="78" text-anchor="middle" fill="#6b7280" font-size="10" font-family="sans-serif" letter-spacing="2">LEVEL</text>
        </svg>
      </div>
      {{-- Right stats --}}
      <div class="flex-1 space-y-3 min-w-0">
        <div>
          <div class="flex justify-between text-xs mb-1.5">
            <span class="text-slate-400">{{ $progress?->next_level_name ? 'XP to '.$progress->next_level_name : 'Max Level' }}</span>
            <span class="text-slate-500">{{ $progress?->next_level_name ? number_format($progress->points_to_next_level).' XP left' : '—' }}</span>
          </div>
          <div class="pbar">
            <div class="pfill" style="width:{{ max(0, min(100, $progress?->level_progress_percent ?? 0)) }}%;"></div>
          </div>
        </div>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-400">Total XP</span>
            <span class="shimmer-val">{{ number_format($progress?->points_total ?? 0) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-400">Balance</span>
            <span class="text-emerald-400 font-bold">Ksh {{ number_format($progress?->balance ?? 0) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-400">Last Played</span>
            <span class="text-white font-medium text-xs">{{ $progress?->last_played_at?->diffForHumans() ?? 'Never' }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-400">Best Streak</span>
            <span class="text-orange-400 font-bold">🔥 {{ $streak?->longest_streak ?? 0 }} days</span>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>{{-- /2-col grid --}}


{{-- ═══════════════════════════════════════
     BADGES (full-width, collapsible)
═══════════════════════════════════════ --}}
@if($badges->count())
<div x-data="{ open: true }" class="pf-card mt-5" style="background:rgba(245,158,11,.045);border-color:rgba(245,158,11,.14);animation-delay:.14s;overflow:hidden">
  <div class="flex items-center justify-between mb-1 cursor-pointer" @click="open=!open">
    <div class="flex items-center gap-2">
      <h2 class="text-white font-bold text-sm uppercase tracking-widest flex items-center gap-2"><span>🥇</span> BADGES</h2>
      <span style="background:rgba(245,158,11,.2);border:1px solid rgba(245,158,11,.3);color:#fbbf24;border-radius:9999px;padding:.1rem .55rem;font-size:.72rem;font-weight:700">{{ $badges->count() }}</span>
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('players.show', $user) }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold transition-colors" @click.stop>View All →</a>
      <svg class="w-4 h-4 text-gray-500 transition-transform" :class="open?'':'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
  </div>

  <div x-show="open" x-transition>
    {{-- Desktop grid --}}
    <div class="hidden md:block mt-3">
      <div class="pf-badge-grid">
        @foreach($badges as $badge)
        <div class="pf-badge-item" style="animation-delay:{{ $loop->index * .05 }}s">
          <div class="pf-badge-circle" title="{{ $badge->description }}"
               style="background:linear-gradient(135deg,{{ $badge->color ?: '#f59e0b' }}22,{{ $badge->color ?: '#f59e0b' }}0c);border:1px solid {{ $badge->color ?: '#f59e0b' }}30">
            @if($badge->image_url)<img src="{{ $badge->image_url }}" alt="{{ $badge->name }}">
            @else{{ $badge->icon ?? '🏆' }}@endif
          </div>
          <div class="pf-badge-name">{{ $badge->name }}</div>
        </div>
        @endforeach
      </div>
    </div>
    {{-- Mobile carousel --}}
    <div class="md:hidden mt-3">
      <div class="pf-badge-carousel">
        @foreach($badges as $badge)
        <div class="pf-badge-item" style="flex-shrink:0">
          <div class="pf-badge-circle"
               style="background:linear-gradient(135deg,{{ $badge->color ?: '#f59e0b' }}22,{{ $badge->color ?: '#f59e0b' }}0c);border:1px solid {{ $badge->color ?: '#f59e0b' }}30">
            @if($badge->image_url)<img src="{{ $badge->image_url }}" alt="{{ $badge->name }}">
            @else{{ $badge->icon ?? '🏆' }}@endif
          </div>
          <div class="pf-badge-name">{{ $badge->name }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif


{{-- ═══════════════════════════════════════
     EDIT PANEL (sidebar + content)
═══════════════════════════════════════ --}}
<div x-data="{ activeTab: 'profile' }" class="pf-edit-wrap mt-5">

  {{-- Sidebar tabs --}}
  <div class="pf-sidebar">
    <button @click="activeTab='profile'"  :class="{'active':activeTab==='profile'}"  class="pf-tab-btn" type="button">
      <span>📝</span><span>Profile Info</span>
    </button>
    <button @click="activeTab='password'" :class="{'active':activeTab==='password'}" class="pf-tab-btn" type="button">
      <span>🔐</span><span>Password</span>
    </button>
    <button @click="activeTab='settings'" :class="{'active':activeTab==='settings'}" class="pf-tab-btn" type="button">
      <span>⚙️</span><span>Settings</span>
    </button>
    <div class="pf-tab-spacer"></div>
    <form method="POST" action="{{ route('logout') }}" class="w-full md:w-auto">
      @csrf
      <button type="submit" class="pf-tab-btn logout w-full">
        <span>🚪</span><span>Log Out</span>
      </button>
    </form>
  </div>

  {{-- Content area --}}
  <div class="pf-tab-content">

    {{-- PROFILE INFO TAB --}}
    <div x-show="activeTab==='profile'" x-transition>
      <p class="text-xs font-black text-indigo-300 uppercase tracking-widest mb-4">PROFILE INFORMATION</p>

      <form x-show="activeTab==='profile'" id="profile-form"
            method="POST" action="{{ route('profile.update') }}"
            enctype="multipart/form-data" class="space-y-4">
        @csrf @method('PATCH')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">Full Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="ifield">
            @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          @if(\App\Models\User::usernamesEnabled())
          <div>
            <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">Username</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-bold text-sm pointer-events-none">@</span>
              <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                     minlength="3" maxlength="20" pattern="[a-zA-Z][a-zA-Z0-9_]{2,19}" class="ifield" style="padding-left:1.75rem;">
            </div>
            <p class="text-slate-500 text-[11px] mt-1">Your public handle — it becomes your profile link and how friends find you. Letters, numbers and _ only.</p>
            @error('username')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
          @endif
          <div>
            <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="ifield">
            @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
          </div>
        </div>

        <div>
          <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">
            Bio <span class="normal-case font-normal text-slate-600">(max 300 chars)</span>
          </label>
          <textarea name="bio" maxlength="300" class="ifield" placeholder="Tell the PesaQuest community about yourself…">{{ old('bio', $user->bio) }}</textarea>
          @error('bio')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">
            County <span class="normal-case font-normal text-slate-600">(optional)</span>
          </label>
          <select name="county" class="ifield">
            <option value="">Prefer not to say</option>
            @foreach($counties as $c)
              <option value="{{ $c }}" @selected(old('county', $user->county) === $c)>{{ $c }}</option>
            @endforeach
          </select>
          @error('county')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">
            Date of Birth <span class="normal-case font-normal text-slate-600">🔒 private — used only for birthday gifts &amp; your game world</span>
          </label>
          @if($user->date_of_birth)
            <div class="ifield flex items-center gap-2 text-slate-400" style="cursor:default;">
              🎂 Set <span class="text-slate-600 text-xs">(kept private — contact support to change)</span>
            </div>
          @else
            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="ifield"
                   max="{{ now()->subYears(5)->format('Y-m-d') }}" min="1920-01-01" style="color-scheme:dark;">
            <p class="text-slate-600 text-xs mt-1">Add it to unlock birthday surprises 🎁 — it can only be set once.</p>
          @endif
          @error('date_of_birth')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        @if(session('status') === 'profile-updated')
          <p class="text-emerald-400 text-xs font-semibold">✓ Profile updated successfully.</p>
        @endif

        <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                style="background:linear-gradient(135deg,#6366f1,#a78bfa)">
          Save Changes
        </button>
      </form>

      {{-- Action buttons --}}
      <div class="flex flex-wrap gap-3 mt-4">
        <a href="{{ route('subscribe.index') }}"
           class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-opacity min-w-[140px]"
           style="background:linear-gradient(135deg,#10b981,#059669)">💎 Subscription &amp; Plans</a>
        <a href="{{ route('players.search') }}"
           class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition-opacity min-w-[140px]"
           style="background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.24);color:#a5b4fc">🔍 Find Other Players</a>
        <form method="POST" action="{{ route('onboarding.replay') }}" class="flex-1 min-w-[140px]"
              onsubmit="sessionStorage.removeItem('wizard_snoozed')">
          @csrf
          <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition-opacity"
                  style="background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.22);color:#6ee7b7">🧭 Replay Intro Tour</button>
        </form>
        <button x-data="" x-on:click.prevent="$dispatch('open-modal','confirm-user-deletion')" type="button"
                class="flex-1 py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition-opacity min-w-[140px]"
                style="background:rgba(239,68,68,.11);border:1px solid rgba(239,68,68,.24);color:#fca5a5">
          🗑️ Delete Account
        </button>
      </div>
    </div>

    {{-- PASSWORD TAB --}}
    <div x-show="activeTab==='password'" x-transition class="space-y-4">
      @if(session('status') === 'password-updated')
        <p class="text-emerald-400 text-xs font-semibold">✓ Password updated successfully.</p>
      @endif

      <div x-data="{ show: false }">
        <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">Current Password</label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="current_password" autocomplete="current-password" class="ifield pr-10" form="pw-form">
          <button type="button" @click="show=!show" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-200 transition-colors">
            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
          </button>
        </div>
        @error('current_password','updatePassword')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div x-data="{ show: false }">
        <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">New Password</label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="password" autocomplete="new-password" class="ifield pr-10" form="pw-form">
          <button type="button" @click="show=!show" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-200 transition-colors">
            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
          </button>
        </div>
        @error('password','updatePassword')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <div x-data="{ show: false }">
        <label class="block text-slate-400 text-xs font-semibold mb-2 uppercase tracking-wider">Confirm New Password</label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" class="ifield pr-10" form="pw-form">
          <button type="button" @click="show=!show" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-200 transition-colors">
            <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <svg x-show="show" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" form="pw-form"
              class="w-full py-2.5 rounded-xl text-sm font-bold transition-opacity hover:opacity-90"
              style="background:rgba(99,102,241,.18);border:1px solid rgba(99,102,241,.28);color:#a5b4fc">
        Update Password
      </button>
    </div>

    {{-- SETTINGS TAB --}}
    <div x-show="activeTab==='settings'" x-transition class="space-y-4">

      {{-- Notification Preferences --}}
      <div x-data="notificationPrefsPanel()" x-init="load()" class="rounded-xl p-4 space-y-3" style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2);">
        <div class="flex items-center justify-between">
          <p class="text-xs font-black text-amber-300 uppercase tracking-wider">🔔 Notifications</p>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="subscribed ? 'text-emerald-300' : 'text-gray-500'" :style="subscribed ? 'background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);' : 'background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);'" x-text="subscribed ? 'Push enabled on this device' : 'Push not enabled here'"></span>
        </div>
        <p class="text-xs text-slate-400 leading-relaxed">Get alerted about payday, overdue bills, crisis warnings and achievements — even when PesaQuest is closed. Quiet hours 9:30pm–6am, max 4 per day.</p>

        <template x-if="!subscribed">
          <button type="button" @click="enable()" :disabled="working"
                  class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                  style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <span x-show="!working">🔔 Enable Notifications on This Device</span>
            <span x-show="working">Requesting…</span>
          </button>
        </template>

        <template x-if="subscribed">
          <div class="space-y-2">
            <template x-for="cat in categories" :key="cat.key">
              <label class="flex items-center justify-between p-2.5 rounded-lg cursor-pointer select-none" style="background:rgba(255,255,255,.04);">
                <span class="text-xs font-semibold text-gray-300" x-text="cat.label"></span>
                <div class="relative">
                  <input type="checkbox" x-model="prefs[cat.key]" @change="save()" class="sr-only peer">
                  <div class="w-10 h-5 rounded-full transition-colors" :class="prefs[cat.key] ? 'bg-amber-500' : 'bg-white/10'"></div>
                  <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform" :class="prefs[cat.key] ? 'translate-x-5' : ''"></div>
                </div>
              </label>
            </template>
            <button type="button" @click="disable()" :disabled="working" class="text-xs text-red-400/80 hover:text-red-400 font-semibold mt-1">Turn off notifications on this device</button>
          </div>
        </template>

        <p x-show="msg" x-transition class="text-xs font-bold" :class="msgOk ? 'text-emerald-400' : 'text-red-400'" x-text="msg"></p>
      </div>

      {{-- Public profile link --}}
      <div x-data="{ copied: false }" class="rounded-xl p-4 space-y-2" style="background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.2);">
        <p class="text-xs font-black text-indigo-300 uppercase tracking-wider">Your Public Profile Link</p>
        <p class="text-xs text-slate-400 leading-relaxed">Share this link so other players can find and view your profile.</p>
        <div class="flex gap-2 items-center">
          <input type="text" readonly
                 value="{{ route('players.show', $user) }}"
                 class="flex-1 rounded-xl px-3 py-2 text-xs text-gray-300 select-all focus:outline-none"
                 style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);"
                 @click="$el.select()">
          <button type="button"
                  @click="navigator.clipboard.writeText('{{ route('players.show', $user) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                  class="shrink-0 px-3 py-2 rounded-xl text-xs font-bold transition-all"
                  :style="copied ? 'background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;' : 'background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);color:#9ca3af;'">
            <span x-show="!copied">Copy</span>
            <span x-show="copied">✅ Copied!</span>
          </button>
        </div>
        <a href="{{ route('players.show', $user) }}" target="_blank"
           class="inline-block text-xs text-indigo-400 hover:text-indigo-300 transition-colors font-semibold">
          Preview my public profile →
        </a>
      </div>

      <p class="text-slate-500 text-xs leading-relaxed">
        <strong class="text-slate-300">Photo sizes:</strong> Profile — 400×400 · Cover — 1200×400 · Max 8 MB · PNG / JPG / WEBP
      </p>

      <a href="{{ route('subscribe.index') }}" class="block text-center py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-opacity"
         style="background:linear-gradient(135deg,#10b981,#059669)">
        💎 Subscription &amp; Plans
      </a>

      <a href="{{ route('players.search') }}" class="block text-center py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition-opacity"
         style="background:rgba(99,102,241,.14);border:1px solid rgba(99,102,241,.24);color:#a5b4fc">
        🔍 Find Other Players
      </a>

      <button x-data="" x-on:click.prevent="$dispatch('open-modal','confirm-user-deletion')" type="button"
              class="w-full py-2.5 rounded-xl text-sm font-bold hover:opacity-90 transition-opacity"
              style="background:rgba(239,68,68,.11);border:1px solid rgba(239,68,68,.24);color:#fca5a5">
        🗑️ Delete Account
      </button>
    </div>

  </div>{{-- /pf-tab-content --}}
</div>{{-- /pf-edit-wrap --}}

</div>{{-- /max-w-4xl --}}
</div>{{-- /outer bg --}}


{{-- ═══════════════════════════════════════
     DELETE ACCOUNT MODAL
═══════════════════════════════════════ --}}
<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
  <form method="POST" action="{{ route('profile.destroy') }}" class="p-6">
    @csrf @method('DELETE')
    <h2 class="text-lg font-semibold text-gray-900 mb-2">Delete Account?</h2>
    <p class="text-sm text-gray-600 mb-4">This will permanently delete all your progress, badges, and subscription.</p>
    <x-text-input id="delete-password" name="password" type="password" class="mt-1 block w-full" placeholder="Enter your password"/>
    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2"/>
    <div class="mt-6 flex justify-end gap-3">
      <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
      <x-danger-button>Delete Account</x-danger-button>
    </div>
  </form>
</x-modal>


{{-- Hidden password form --}}
<form id="pw-form" method="POST" action="{{ route('profile.password.update') }}" hidden>
  @csrf @method('PUT')
</form>


{{-- Mobile bottom nav (md:hidden) --}}
<div class="md:hidden pf-bottom-nav" x-data="{ moreOpen: false }">
  <button class="pf-bnav-btn" onclick="document.querySelector('.pf-edit-wrap [x-data]').__x.$data.activeTab='profile'" type="button">
    <span class="pf-bnav-icon">📝</span>Profile
  </button>
  <button class="pf-bnav-btn" onclick="document.querySelector('.pf-edit-wrap [x-data]').__x.$data.activeTab='password'" type="button">
    <span class="pf-bnav-icon">🔐</span>Password
  </button>
  <button class="pf-bnav-btn" onclick="document.querySelector('.pf-edit-wrap [x-data]').__x.$data.activeTab='settings'" type="button">
    <span class="pf-bnav-icon">⚙️</span>Settings
  </button>
  <div class="pf-bnav-btn relative" @click="moreOpen=!moreOpen">
    <span class="pf-bnav-icon">⋯</span>More
    <div x-show="moreOpen" x-cloak @click.outside="moreOpen=false"
         style="position:absolute;bottom:calc(100% + 6px);right:0;background:#0e0c22;border:1px solid rgba(255,255,255,.1);border-radius:.75rem;min-width:150px;z-index:200;overflow:hidden;">
      <a href="{{ route('subscribe.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-emerald-400 hover:bg-white/5">💎 Subscription</a>
      <a href="{{ route('players.search') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-indigo-400 hover:bg-white/5">🔍 Find Players</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-4 py-2.5 text-xs text-red-400 hover:bg-white/5 w-full text-left">🚪 Log Out</button>
      </form>
    </div>
  </div>
</div>


<script>
function pfPreviewCover(input) {
  if (!input.files?.[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const bg = document.querySelector('.pf-hero-bg');
    let el = document.getElementById('cover-preview');
    if (el && el.tagName === 'DIV') {
      const img = document.createElement('img');
      img.id = 'cover-preview';
      img.className = 'pf-cover-bg';
      el.replaceWith(img);
      el = img;
    } else if (!el) {
      const img = document.createElement('img');
      img.id = 'cover-preview';
      img.className = 'pf-cover-bg';
      bg.insertBefore(img, bg.firstChild);
      el = img;
    }
    el.src = e.target.result;
  };
  reader.readAsDataURL(input.files[0]);
}

function pfPreviewAvatar(input) {
  if (!input.files?.[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const inner = input.closest('.inner');
    let img = inner.querySelector('img');
    const initials = inner.querySelector('.pf-av-initials');
    if (initials) initials.style.display = 'none';
    if (!img) {
      img = document.createElement('img');
      img.id = 'avatar-preview';
      inner.insertBefore(img, inner.firstChild);
    }
    img.src = e.target.result;
  };
  reader.readAsDataURL(input.files[0]);
}

function notificationPrefsPanel() {
    return {
        subscribed: false,
        working: false,
        msg: '', msgOk: true,
        categories: [
            { key: 'money_alerts',  label: '💰 Money alerts (bills, payday, crises)' },
            { key: 'achievements',  label: '🏆 Achievements (badges, quests, milestones)' },
            { key: 'opportunities', label: '⚡ Opportunities (jobs, gigs, forum replies)' },
            { key: 'announcements', label: '📢 Announcements from Moski' },
            { key: 'real_life_reminders', label: '🌍 Real-life bill reminders (your own bills, Premium)' },
        ],
        prefs: {},

        async load() {
            try {
                const res = await fetch('{{ route('push.preferences.index') }}', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.prefs = data.preferences || {};
                this.subscribed = !!data.subscribed;
                this.categories = this.categories.filter(c => c.key in this.prefs);
            } catch (e) {}
        },

        async enable() {
            this.working = true; this.msg = '';
            const result = await window.PesaPush.subscribe();
            if (result.ok) {
                this.subscribed = true; this.msgOk = true; this.msg = '✓ Notifications enabled on this device!';
                await this.load();
            } else {
                this.msgOk = false; this.msg = result.reason || 'Could not enable notifications.';
            }
            this.working = false;
        },

        async disable() {
            this.working = true;
            await window.PesaPush.unsubscribe();
            this.subscribed = false;
            this.working = false;
        },

        async save() {
            try {
                await fetch('{{ route('push.preferences.save') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ preferences: this.prefs }),
                });
            } catch (e) {}
        },
    };
}
</script>
</x-app-layout>
