{{-- welcome.blade.php is not the active homepage.
     The '/' route → LandingController → landing.blade.php.
     This file exists as a Laravel default but is unused. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="0;url={{ route('landing') }}">
<title>PesaQuest — Learn Money. Play Pesa City.</title>
<meta name="description" content="Kenya's first financial literacy game. Navigate Pesa City, choose your career, face real-life money decisions, and build your financial future.">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:      #08111A;
  --surf:    #0E1C2C;
  --surf2:   #132233;
  --bdr:     #1A3048;
  --green:   #15C77E;
  --green-d: rgba(21,199,126,0.10);
  --gold:    #FFBC00;
  --gold-d:  rgba(255,188,0,0.10);
  --blue:    #4DA8F7;
  --orange:  #FF6B35;
  --purple:  #A78BFA;
  --muted:   #5A7A96;
  --text:    #C4D8F0;
  --bright:  #E8F4FF;
  --sans: system-ui,-apple-system,'Segoe UI',sans-serif;
}

html { background: var(--bg); color: var(--text); font-family: var(--sans); scroll-behavior: smooth; }
body { overflow-x: hidden; }

/* ── NAV ── */
.nav {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 5%; height: 64px;
  background: rgba(8,17,26,0.85);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(21,199,126,0.08);
}
.nav-brand {
  display: flex; align-items: center; gap: 10px;
  font-size: 18px; font-weight: 900; letter-spacing: -0.02em;
  color: var(--bright); text-decoration: none;
}
.nav-brand-badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 8px;
  background: linear-gradient(135deg, var(--green), #0fa864);
  font-size: 16px;
}
.nav-sub { font-size: 10px; font-weight: 600; letter-spacing: 0.1em;
  color: var(--green); text-transform: uppercase; margin-top: 1px; }
.nav-links { display: flex; align-items: center; gap: 12px; }
.nav-link-login {
  padding: 7px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
  color: var(--text); text-decoration: none;
  border: 1px solid var(--bdr); transition: border-color 0.2s;
}
.nav-link-login:hover { border-color: var(--green); color: var(--bright); }
.nav-link-cta {
  padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 700;
  color: #000; background: var(--green); text-decoration: none;
  transition: transform 0.15s, box-shadow 0.15s;
}
.nav-link-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(21,199,126,0.4); }

/* ── HERO ── */
.hero {
  min-height: 100vh; display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 100px 5% 80px;
  position: relative; overflow: hidden;
  text-align: center;
}
.hero-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(21,199,126,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(21,199,126,0.04) 1px, transparent 1px);
  background-size: 48px 48px;
}
.hero-glow {
  position: absolute; top: 10%; left: 50%; transform: translateX(-50%);
  width: 700px; height: 400px; border-radius: 50%;
  background: radial-gradient(ellipse, rgba(21,199,126,0.08) 0%, transparent 70%);
  pointer-events: none;
}
.hero-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 6px 16px; border-radius: 30px;
  background: rgba(21,199,126,0.08);
  border: 1px solid rgba(21,199,126,0.25);
  font-size: 11px; font-weight: 800; letter-spacing: 0.12em;
  color: var(--green); text-transform: uppercase;
  margin-bottom: 28px; position: relative;
}
.hero-eyebrow-dot {
  width: 6px; height: 6px; border-radius: 50%; background: var(--green);
  animation: heroBlip 2s ease-in-out infinite;
}
@keyframes heroBlip { 0%,100%{opacity:1} 50%{opacity:0.3} }
.hero-title {
  font-size: clamp(2.8rem, 7vw, 5.5rem);
  font-weight: 900; line-height: 1.05; letter-spacing: -0.04em;
  color: var(--bright); margin-bottom: 6px;
  text-wrap: balance; position: relative;
}
.hero-title-accent { color: var(--green); }
.hero-title-line2 { color: var(--gold); }
.hero-sub {
  font-size: clamp(1rem, 2.5vw, 1.25rem);
  color: var(--muted); max-width: 580px; margin: 24px auto 40px;
  line-height: 1.65; font-weight: 400;
}
.hero-sub strong { color: var(--text); font-weight: 600; }
.hero-ctas { display: flex; align-items: center; justify-content: center; gap: 14px; flex-wrap: wrap; position: relative; }
.btn-primary {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 16px 32px; border-radius: 12px;
  font-size: 15px; font-weight: 800; color: #000;
  background: linear-gradient(135deg, var(--green), #0fa864);
  text-decoration: none; transition: transform 0.15s, box-shadow 0.15s;
  box-shadow: 0 4px 24px rgba(21,199,126,0.35);
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(21,199,126,0.5); }
.btn-ghost {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 15px 28px; border-radius: 12px;
  font-size: 15px; font-weight: 700; color: var(--text);
  text-decoration: none; border: 1px solid var(--bdr);
  transition: border-color 0.2s, color 0.2s;
}
.btn-ghost:hover { border-color: var(--blue); color: var(--bright); }

/* City skyline SVG */
.hero-city {
  position: relative; width: 100%; max-width: 900px; margin: 60px auto 0;
  height: 180px;
}
.hero-city svg { width: 100%; height: 100%; }

/* ── STATS BAR ── */
.stats-bar {
  display: flex; align-items: center; justify-content: center;
  flex-wrap: wrap; gap: 0;
  border-top: 1px solid var(--bdr); border-bottom: 1px solid var(--bdr);
  padding: 28px 5%;
  background: linear-gradient(135deg, rgba(21,199,126,0.03) 0%, transparent 100%);
}
.stat-item {
  display: flex; flex-direction: column; align-items: center;
  padding: 0 40px; gap: 4px;
}
.stat-item:not(:last-child) { border-right: 1px solid var(--bdr); }
.stat-num {
  font-size: 2.25rem; font-weight: 900; letter-spacing: -0.04em;
  font-variant-numeric: tabular-nums;
}
.stat-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; color: var(--muted); text-transform: uppercase; }

/* ── HOW IT WORKS ── */
.section { padding: 100px 5%; max-width: 1200px; margin: 0 auto; }
.section-eyebrow {
  font-size: 10px; font-weight: 800; letter-spacing: 0.15em;
  text-transform: uppercase; color: var(--green); margin-bottom: 12px;
}
.section-title {
  font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 900; letter-spacing: -0.03em;
  color: var(--bright); line-height: 1.15; margin-bottom: 16px;
}
.section-sub {
  font-size: 1.05rem; color: var(--muted); max-width: 520px; line-height: 1.65;
}

.steps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2px; margin-top: 56px; }
.step-card {
  background: var(--surf); padding: 36px 32px;
  position: relative; overflow: hidden;
  transition: background 0.2s;
}
.step-card:first-child { border-radius: 16px 0 0 16px; }
.step-card:last-child  { border-radius: 0 16px 16px 0; }
.step-card:hover { background: var(--surf2); }
.step-num {
  font-size: 4.5rem; font-weight: 900; letter-spacing: -0.06em;
  line-height: 1; margin-bottom: 16px;
  background: linear-gradient(135deg, var(--green) 0%, rgba(21,199,126,0.2) 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.step-title { font-size: 1.2rem; font-weight: 800; color: var(--bright); margin-bottom: 10px; }
.step-body { font-size: 0.9rem; color: var(--muted); line-height: 1.65; }
.step-icon { font-size: 2rem; margin-bottom: 12px; }

/* ── FEATURES GRID ── */
.features-section { padding: 100px 5%; }
.features-section .inner { max-width: 1200px; margin: 0 auto; }
.features-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1px; margin-top: 56px; background: var(--bdr);
  border-radius: 16px; overflow: hidden;
}
.feature-card {
  background: var(--surf); padding: 32px 28px;
  transition: background 0.2s;
  border: none;
}
.feature-card:hover { background: var(--surf2); }
.feature-icon { font-size: 2.2rem; margin-bottom: 16px; }
.feature-title { font-size: 1.05rem; font-weight: 800; color: var(--bright); margin-bottom: 8px; }
.feature-body  { font-size: 0.875rem; color: var(--muted); line-height: 1.65; }
.feature-badge {
  display: inline-block; margin-top: 12px;
  padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700;
  letter-spacing: 0.06em; text-transform: uppercase;
}
.badge-green  { background: var(--green-d); color: var(--green); border: 1px solid rgba(21,199,126,0.25); }
.badge-gold   { background: var(--gold-d);  color: var(--gold);  border: 1px solid rgba(255,188,0,0.25); }
.badge-blue   { background: rgba(77,168,247,0.08); color: var(--blue); border: 1px solid rgba(77,168,247,0.25); }
.badge-orange { background: rgba(255,107,53,0.08); color: var(--orange); border: 1px solid rgba(255,107,53,0.25); }
.badge-purple { background: rgba(167,139,250,0.08); color: var(--purple); border: 1px solid rgba(167,139,250,0.25); }

/* ── DISTRICTS ── */
.districts-section { padding: 100px 5%; background: var(--surf); }
.districts-section .inner { max-width: 1200px; margin: 0 auto; }
.districts-scroll {
  display: flex; gap: 16px; overflow-x: auto; padding: 4px 0 20px;
  margin-top: 48px; scroll-snap-type: x mandatory;
  scrollbar-width: thin; scrollbar-color: var(--bdr) transparent;
}
.district-card {
  flex: 0 0 220px; background: var(--bg); border-radius: 16px;
  border: 1px solid var(--bdr); padding: 24px 20px;
  scroll-snap-align: start; transition: border-color 0.2s, transform 0.2s;
  text-decoration: none;
}
.district-card:hover { border-color: var(--accent, var(--green)); transform: translateY(-4px); }
.district-card-icon { font-size: 2.5rem; margin-bottom: 12px; }
.district-card-name {
  font-size: 1rem; font-weight: 800; color: var(--bright); margin-bottom: 6px;
}
.district-card-tag {
  display: inline-block; padding: 2px 9px; border-radius: 20px;
  font-size: 9px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase;
  margin-bottom: 10px;
}
.district-card-lesson { font-size: 0.78rem; color: var(--muted); line-height: 1.5; }

/* ── CAREER PATHS ── */
.careers-section { padding: 100px 5%; }
.careers-section .inner { max-width: 1200px; margin: 0 auto; }
.careers-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px; margin-top: 48px;
}
.career-card {
  background: var(--surf); border: 1px solid var(--bdr);
  border-radius: 14px; padding: 24px 20px;
  transition: border-color 0.2s, transform 0.15s;
}
.career-card:hover { border-color: rgba(255,188,0,0.4); transform: translateY(-3px); }
.career-card-icon { font-size: 2rem; margin-bottom: 10px; }
.career-card-name { font-size: 0.95rem; font-weight: 800; color: var(--bright); margin-bottom: 4px; }
.career-card-income { font-size: 0.78rem; color: var(--muted); }
.career-card-income strong { color: var(--gold); }

/* ── MISSION SECTION ── */
.mission-section {
  padding: 100px 5%;
  background: linear-gradient(160deg, rgba(21,199,126,0.04) 0%, transparent 60%);
  border-top: 1px solid var(--bdr); border-bottom: 1px solid var(--bdr);
}
.mission-inner { max-width: 780px; margin: 0 auto; text-align: center; }
.mission-logo {
  width: 64px; height: 64px; border-radius: 16px; margin: 0 auto 24px;
  background: linear-gradient(135deg, var(--green), #0fa864);
  display: flex; align-items: center; justify-content: center; font-size: 32px;
}
.mission-quote {
  font-size: clamp(1.2rem, 3vw, 1.7rem); font-weight: 700;
  color: var(--bright); line-height: 1.45; margin-bottom: 20px;
  text-wrap: balance;
}
.mission-quote em { color: var(--green); font-style: normal; }
.mission-attr { font-size: 0.875rem; color: var(--muted); }
.mission-attr strong { color: var(--text); }

/* ── CTA SECTION ── */
.cta-section { padding: 120px 5%; text-align: center; position: relative; overflow: hidden; }
.cta-glow {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
  width: 600px; height: 400px; border-radius: 50%;
  background: radial-gradient(ellipse, rgba(21,199,126,0.07) 0%, transparent 70%);
  pointer-events: none;
}
.cta-inner { position: relative; max-width: 680px; margin: 0 auto; }
.cta-title {
  font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 900; letter-spacing: -0.04em;
  color: var(--bright); line-height: 1.1; margin-bottom: 16px;
}
.cta-title span { color: var(--green); }
.cta-sub { font-size: 1.05rem; color: var(--muted); margin-bottom: 40px; line-height: 1.65; }
.cta-note { font-size: 0.8rem; color: var(--muted); margin-top: 16px; }

/* ── FOOTER ── */
.footer {
  padding: 40px 5%; border-top: 1px solid var(--bdr);
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.footer-brand { font-size: 0.875rem; font-weight: 700; color: var(--muted); }
.footer-brand strong { color: var(--text); }
.footer-links { display: flex; gap: 24px; }
.footer-links a { font-size: 0.8rem; color: var(--muted); text-decoration: none; transition: color 0.2s; }
.footer-links a:hover { color: var(--text); }

/* ── RESPONSIVE ── */
@media (max-width: 640px) {
  .stat-item { padding: 0 20px; }
  .stat-item:not(:last-child) { border-right: none; border-bottom: 1px solid var(--bdr); padding: 16px 0; }
  .stats-bar { flex-direction: column; }
  .step-card:first-child { border-radius: 16px 16px 0 0; }
  .step-card:last-child  { border-radius: 0 0 16px 16px; }
  .footer { flex-direction: column; text-align: center; }
  .nav-brand-sub { display: none; }
}

/* Animated counter */
@keyframes countUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
.stat-num { animation: countUp 0.6s ease both; }
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <a href="{{ url('/') }}" class="nav-brand">
    <div class="nav-brand-badge">🏙️</div>
    <div>
      <div>PesaQuest</div>
      <div class="nav-sub">by NGO Moski</div>
    </div>
  </a>
  <div class="nav-links">
    @auth
      <a href="{{ url('/world') }}" class="nav-link-login">Back to City</a>
      <a href="{{ url('/dashboard') }}" class="nav-link-cta">Dashboard →</a>
    @else
      <a href="{{ route('login') }}" class="nav-link-login">Sign In</a>
      <a href="{{ route('register') }}" class="nav-link-cta">Play Free →</a>
    @endauth
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-grid"></div>
  <div class="hero-glow"></div>

  <div class="hero-eyebrow">
    <div class="hero-eyebrow-dot"></div>
    Now Live · Pesa City is Open
  </div>

  <h1 class="hero-title">
    <span class="hero-title-accent">Learn Money.</span><br>
    <span class="hero-title-line2">Play Pesa City.</span><br>
    Build Your Future.
  </h1>

  <p class="hero-sub">
    Kenya's first financial literacy game where your <strong>career choices</strong>,
    <strong>saving habits</strong>, and <strong>investment decisions</strong>
    shape a living virtual city — and the financial skills you carry for life.
  </p>

  <div class="hero-ctas">
    @guest
      <a href="{{ route('register') }}" class="btn-primary">
        🏙️ Enter Pesa City — Free
      </a>
      <a href="{{ route('login') }}" class="btn-ghost">
        Sign In →
      </a>
    @else
      <a href="{{ url('/world') }}" class="btn-primary">
        🏙️ Return to Pesa City
      </a>
      <a href="{{ url('/dashboard') }}" class="btn-ghost">
        My Dashboard →
      </a>
    @endguest
  </div>

  <!-- City skyline illustration -->
  <div class="hero-city">
    <svg viewBox="0 0 900 180" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <!-- Stars -->
      <circle cx="45"  cy="15" r="1.2" fill="rgba(196,216,240,0.5)"/>
      <circle cx="120" cy="8"  r="0.8" fill="rgba(196,216,240,0.4)"/>
      <circle cx="210" cy="12" r="1"   fill="rgba(196,216,240,0.5)"/>
      <circle cx="340" cy="6"  r="1.3" fill="rgba(196,216,240,0.4)"/>
      <circle cx="430" cy="10" r="0.9" fill="rgba(255,188,0,0.6)"/>
      <circle cx="580" cy="7"  r="1.1" fill="rgba(196,216,240,0.4)"/>
      <circle cx="700" cy="14" r="0.8" fill="rgba(196,216,240,0.5)"/>
      <circle cx="820" cy="9"  r="1.2" fill="rgba(196,216,240,0.4)"/>
      <circle cx="870" cy="18" r="0.7" fill="rgba(196,216,240,0.3)"/>

      <!-- Moon -->
      <circle cx="810" cy="22" r="18" fill="rgba(255,188,0,0.07)" stroke="rgba(255,188,0,0.15)" stroke-width="1"/>
      <circle cx="816" cy="18" r="14" fill="#08111A"/>

      <!-- Background buildings (dark) -->
      <rect x="0"   y="90" width="40"  height="90" fill="#0B1A28" rx="2"/>
      <rect x="35"  y="70" width="30"  height="110" fill="#0B1A28" rx="2"/>
      <rect x="60"  y="85" width="25"  height="95" fill="#0B1A28" rx="2"/>
      <rect x="820" y="75" width="35"  height="105" fill="#0B1A28" rx="2"/>
      <rect x="850" y="88" width="50"  height="92" fill="#0B1A28" rx="2"/>

      <!-- Mid buildings -->
      <rect x="80"  y="60" width="50"  height="120" fill="#0E1C2C" rx="3"/>
      <rect x="125" y="45" width="35"  height="135" fill="#0F2035" rx="3"/>
      <rect x="155" y="72" width="40"  height="108" fill="#0E1C2C" rx="3"/>
      <rect x="190" y="55" width="28"  height="125" fill="#0F2035" rx="3"/>
      <rect x="680" y="52" width="45"  height="128" fill="#0E1C2C" rx="3"/>
      <rect x="720" y="68" width="38"  height="112" fill="#0F2035" rx="3"/>
      <rect x="752" y="45" width="55"  height="135" fill="#0E1C2C" rx="3"/>

      <!-- Windows on mid buildings (lit) -->
      <rect x="87"  y="68" width="8" height="6" fill="rgba(255,188,0,0.25)" rx="1"/>
      <rect x="100" y="68" width="8" height="6" fill="rgba(77,168,247,0.25)" rx="1"/>
      <rect x="87"  y="82" width="8" height="6" fill="rgba(77,168,247,0.2)" rx="1"/>
      <rect x="100" y="82" width="8" height="6" fill="rgba(255,188,0,0.18)" rx="1"/>
      <rect x="87"  y="96" width="8" height="6" fill="rgba(21,199,126,0.2)" rx="1"/>
      <rect x="128" y="55" width="9" height="7" fill="rgba(255,188,0,0.3)" rx="1"/>
      <rect x="141" y="55" width="9" height="7" fill="rgba(255,188,0,0.2)" rx="1"/>
      <rect x="128" y="70" width="9" height="7" fill="rgba(77,168,247,0.25)" rx="1"/>
      <rect x="688" y="60" width="10" height="7" fill="rgba(255,188,0,0.3)" rx="1"/>
      <rect x="703" y="60" width="10" height="7" fill="rgba(21,199,126,0.25)" rx="1"/>
      <rect x="688" y="76" width="10" height="7" fill="rgba(77,168,247,0.2)" rx="1"/>
      <rect x="758" y="55" width="10" height="7" fill="rgba(255,188,0,0.28)" rx="1"/>
      <rect x="773" y="55" width="10" height="7" fill="rgba(255,107,53,0.2)" rx="1"/>
      <rect x="758" y="70" width="10" height="7" fill="rgba(21,199,126,0.22)" rx="1"/>
      <rect x="773" y="70" width="10" height="7" fill="rgba(255,188,0,0.2)" rx="1"/>

      <!-- Foreground prominent buildings -->
      <!-- Central tower (Opportunity Hub) -->
      <rect x="410" y="20" width="80" height="160" fill="#0E1C2C" rx="4"/>
      <rect x="415" y="15" width="70" height="10" fill="#0F2035" rx="2"/>
      <rect x="440" y="8"  width="20" height="10" fill="#132233" rx="2"/>
      <!-- Tower antenna -->
      <rect x="449" y="0" width="2" height="12" fill="rgba(255,107,53,0.6)"/>
      <circle cx="450" cy="0" r="3" fill="rgba(255,107,53,0.8)"/>
      <!-- Tower windows grid -->
      <rect x="420" y="30" width="12" height="8" fill="rgba(77,168,247,0.4)" rx="1"/>
      <rect x="437" y="30" width="12" height="8" fill="rgba(77,168,247,0.3)" rx="1"/>
      <rect x="454" y="30" width="12" height="8" fill="rgba(255,188,0,0.4)" rx="1"/>
      <rect x="471" y="30" width="12" height="8" fill="rgba(77,168,247,0.35)" rx="1"/>
      <rect x="420" y="46" width="12" height="8" fill="rgba(255,188,0,0.25)" rx="1"/>
      <rect x="437" y="46" width="12" height="8" fill="rgba(77,168,247,0.4)" rx="1"/>
      <rect x="454" y="46" width="12" height="8" fill="rgba(21,199,126,0.3)" rx="1"/>
      <rect x="471" y="46" width="12" height="8" fill="rgba(255,188,0,0.3)" rx="1"/>
      <rect x="420" y="62" width="12" height="8" fill="rgba(21,199,126,0.35)" rx="1"/>
      <rect x="437" y="62" width="12" height="8" fill="rgba(255,188,0,0.25)" rx="1"/>
      <rect x="454" y="62" width="12" height="8" fill="rgba(77,168,247,0.3)" rx="1"/>
      <rect x="471" y="62" width="12" height="8" fill="rgba(21,199,126,0.4)" rx="1"/>

      <!-- Marketplace building (left of center) -->
      <rect x="240" y="40" width="100" height="140" fill="#0E1C2C" rx="4"/>
      <rect x="245" y="34" width="90"  height="10"  fill="#152538" rx="2"/>
      <!-- Market sign glow -->
      <rect x="258" y="50" width="65" height="22" fill="rgba(21,199,126,0.12)" rx="3" stroke="rgba(21,199,126,0.35)" stroke-width="1"/>
      <text x="264" y="65" font-size="10" fill="#15C77E" font-weight="bold" font-family="system-ui">MARKET</text>
      <rect x="248" y="82" width="14" height="10" fill="rgba(21,199,126,0.25)" rx="1"/>
      <rect x="267" y="82" width="14" height="10" fill="rgba(255,188,0,0.2)" rx="1"/>
      <rect x="286" y="82" width="14" height="10" fill="rgba(21,199,126,0.2)" rx="1"/>
      <rect x="305" y="82" width="14" height="10" fill="rgba(77,168,247,0.2)" rx="1"/>
      <rect x="248" y="100" width="14" height="10" fill="rgba(77,168,247,0.2)" rx="1"/>
      <rect x="267" y="100" width="14" height="10" fill="rgba(21,199,126,0.3)" rx="1"/>
      <rect x="286" y="100" width="14" height="10" fill="rgba(255,188,0,0.25)" rx="1"/>

      <!-- Bank building (right of center) -->
      <rect x="560" y="35" width="110" height="145" fill="#0F2035" rx="4"/>
      <rect x="555" y="28" width="120" height="12" fill="#132233" rx="2"/>
      <!-- Columns -->
      <rect x="568" y="40" width="8" height="140" fill="rgba(21,199,126,0.06)" rx="1"/>
      <rect x="588" y="40" width="8" height="140" fill="rgba(21,199,126,0.04)" rx="1"/>
      <rect x="648" y="40" width="8" height="140" fill="rgba(21,199,126,0.06)" rx="1"/>
      <rect x="628" y="40" width="8" height="140" fill="rgba(21,199,126,0.04)" rx="1"/>
      <!-- Bank sign -->
      <rect x="570" y="50" width="75" height="20" fill="rgba(53,195,240,0.10)" rx="2" stroke="rgba(53,195,240,0.3)" stroke-width="1"/>
      <text x="575" y="64" font-size="9" fill="#35C3F0" font-weight="bold" font-family="system-ui">EQUITY SQ.</text>
      <rect x="572" y="80" width="12" height="8" fill="rgba(53,195,240,0.3)" rx="1"/>
      <rect x="590" y="80" width="12" height="8" fill="rgba(53,195,240,0.2)" rx="1"/>
      <rect x="608" y="80" width="12" height="8" fill="rgba(255,188,0,0.25)" rx="1"/>
      <rect x="626" y="80" width="12" height="8" fill="rgba(53,195,240,0.25)" rx="1"/>
      <rect x="572" y="96" width="12" height="8" fill="rgba(255,188,0,0.2)" rx="1"/>
      <rect x="590" y="96" width="12" height="8" fill="rgba(53,195,240,0.3)" rx="1"/>
      <rect x="608" y="96" width="12" height="8" fill="rgba(21,199,126,0.2)" rx="1"/>

      <!-- Ground / road -->
      <rect x="0" y="168" width="900" height="12" fill="#0A1928" rx="0"/>
      <rect x="0" y="170" width="900" height="4"  fill="rgba(21,199,126,0.04)"/>
      <!-- Road markings -->
      <rect x="100" y="171" width="40" height="2" fill="rgba(255,188,0,0.12)" rx="1"/>
      <rect x="200" y="171" width="40" height="2" fill="rgba(255,188,0,0.12)" rx="1"/>
      <rect x="400" y="171" width="40" height="2" fill="rgba(255,188,0,0.12)" rx="1"/>
      <rect x="600" y="171" width="40" height="2" fill="rgba(255,188,0,0.12)" rx="1"/>
      <rect x="720" y="171" width="40" height="2" fill="rgba(255,188,0,0.12)" rx="1"/>

      <!-- Bottom glow -->
      <defs>
        <linearGradient id="skyGlow" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%"   stop-color="rgba(21,199,126,0.0)"/>
          <stop offset="100%" stop-color="rgba(21,199,126,0.08)"/>
        </linearGradient>
      </defs>
      <rect x="0" y="130" width="900" height="50" fill="url(#skyGlow)"/>
    </svg>
  </div>
</section>

<!-- STATS BAR -->
<div class="stats-bar">
  <div class="stat-item">
    <div class="stat-num" style="color:var(--green)">7</div>
    <div class="stat-label">Active Districts</div>
  </div>
  <div class="stat-item">
    <div class="stat-num" style="color:var(--gold)">8</div>
    <div class="stat-label">Career Paths</div>
  </div>
  <div class="stat-item">
    <div class="stat-num" style="color:var(--blue)">6</div>
    <div class="stat-label">Structured Quests</div>
  </div>
  <div class="stat-item">
    <div class="stat-num" style="color:var(--orange)">500<span style="font-size:1.4rem">+</span></div>
    <div class="stat-label">Life Decisions</div>
  </div>
  <div class="stat-item">
    <div class="stat-num" style="color:var(--purple)">∞</div>
    <div class="stat-label">Replay Value</div>
  </div>
</div>

<!-- HOW IT WORKS -->
<div style="background: var(--surf); padding: 0;">
<div class="section">
  <div class="section-eyebrow">How It Works</div>
  <h2 class="section-title">Three steps to<br>financial mastery</h2>
  <p class="section-sub">Pesa City is built on the same decision loops as real life — earn, save, invest, and protect. Just with better feedback loops.</p>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">01</div>
      <div class="step-icon">🎯</div>
      <div class="step-title">Choose Your Career Path</div>
      <div class="step-body">Take a short career quiz. Are you a tech developer, a healthcare worker, a farmer, or an entrepreneur? Your career determines your income, your city events, and the financial dilemmas you face. Every path is different.</div>
    </div>
    <div class="step-card">
      <div class="step-num">02</div>
      <div class="step-icon">🏙️</div>
      <div class="step-title">Navigate Pesa City</div>
      <div class="step-body">Walk your stickman through 7 financial districts. The Marketplace, Equity Bank, Opportunity Hub, Kiambu Estates, and more. Each district teaches a real financial domain. Quests unlock districts as you grow.</div>
    </div>
    <div class="step-card">
      <div class="step-num">03</div>
      <div class="step-icon">💡</div>
      <div class="step-title">Make Real Decisions</div>
      <div class="step-body">A locum shift offer. A bulk purchase deal. A supplier who owes you money. Every choice moves your balance, credit score, and net worth in real time. The While-You-Were-Away modal catches you up when you return.</div>
    </div>
  </div>
</div>
</div>

<!-- FEATURES -->
<div class="features-section">
  <div class="inner">
    <div class="section-eyebrow">Platform Features</div>
    <h2 class="section-title">Built for the way<br>young Kenyans actually learn</h2>
    <p class="section-sub">Not worksheets. Not lectures. A living simulation that responds to your choices the same way real money does.</p>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🗺️</div>
        <div class="feature-title">7 Live Financial Districts</div>
        <div class="feature-body">Marketplace, Equity Bank, Opportunity Hub, Kiambu Estates, Jua Kali Car Yard, Fun World, and Community Centre — each teaching a distinct financial domain through gameplay.</div>
        <div class="feature-badge badge-green">Phase 11 Unlocks</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🎯</div>
        <div class="feature-title">Career-Aware Events</div>
        <div class="feature-body">Your tech developer faces freelance gig offers and software license renewals. Your farmer gets harvest loan deals and fertilizer subsidies. 24 career-targeted scenarios across 8 tracks.</div>
        <div class="feature-badge badge-blue">EventEngine</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🌍</div>
        <div class="feature-title">While You Were Away</div>
        <div class="feature-body">The game runs on a real clock. Return after a day and your salary arrived, bills were settled, and life events fired. The WYWA modal catches you up every time you enter Pesa City.</div>
        <div class="feature-badge badge-green">Live</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🏅</div>
        <div class="feature-title">6-Quest Progression Chain</div>
        <div class="feature-body">From your first connected device to your first business — 6 structured quests with game-day deadlines, XP rewards, and badge unlocks. Each quest unlocks a new district and a new financial chapter.</div>
        <div class="feature-badge badge-gold">6 Quests Active</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">⏳</div>
        <div class="feature-title">Time-Sensitive Opportunities</div>
        <div class="feature-body">That bulk deal expires in 3 game days. The locum shift is only available this weekend. Urgency is real in Pesa City — just like in real life. Claim before the clock ticks out.</div>
        <div class="feature-badge badge-orange">Urgency System</div>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🎁</div>
        <div class="feature-title">Daily Login Bonus</div>
        <div class="feature-body">Show up every day and collect your streak reward — KES deposits and XP that scale with your level. The habit of checking in daily is itself a financial discipline lesson.</div>
        <div class="feature-badge badge-purple">Retention Loop</div>
      </div>
    </div>
  </div>
</div>

<!-- DISTRICTS -->
<div class="districts-section">
  <div class="inner">
    <div class="section-eyebrow">Explore the City</div>
    <h2 class="section-title">7 districts.<br>7 financial lessons.</h2>
    <p class="section-sub">Every district in Pesa City is designed around a core financial behaviour. Walk in — walk out knowing something you didn't before.</p>

    <div class="districts-scroll">
      <div class="district-card" style="--accent: #15C77E;">
        <div class="district-card-icon">🥬</div>
        <div class="district-card-name">Mama Mboga Market</div>
        <div class="district-card-tag" style="background:rgba(21,199,126,0.1);color:#15C77E;border:1px solid rgba(21,199,126,0.25);">Marketplace</div>
        <div class="district-card-lesson">Buy assets, shares, and devices. The first market you enter. Every hustle starts with the right tools.</div>
      </div>
      <div class="district-card" style="--accent: #4DA8F7;">
        <div class="district-card-icon">🎓</div>
        <div class="district-card-name">Opportunity Hub</div>
        <div class="district-card-tag" style="background:rgba(77,168,247,0.1);color:#4DA8F7;border:1px solid rgba(77,168,247,0.25);">Skills</div>
        <div class="district-card-lesson">Free courses, job listings, and internships. Every career in Pesa City starts here. Skill up before you earn.</div>
      </div>
      <div class="district-card" style="--accent: #35C3F0;">
        <div class="district-card-icon">🏛️</div>
        <div class="district-card-name">Equity Square</div>
        <div class="district-card-tag" style="background:rgba(53,195,240,0.1);color:#35C3F0;border:1px solid rgba(53,195,240,0.25);">Banking</div>
        <div class="district-card-lesson">Savings schemes, SACCO, credit score review. Know your number. Grow your number. HELB management included.</div>
      </div>
      <div class="district-card" style="--accent: #A3E635;">
        <div class="district-card-icon">🏠</div>
        <div class="district-card-name">Kiambu Estates</div>
        <div class="district-card-tag" style="background:rgba(163,230,53,0.1);color:#A3E635;border:1px solid rgba(163,230,53,0.25);">Property</div>
        <div class="district-card-lesson">Plots, bedsitters, and apartments. Own your first piece of Pesa City land. Rental income awaits smart buyers.</div>
      </div>
      <div class="district-card" style="--accent: #FFBC00;">
        <div class="district-card-icon">🚗</div>
        <div class="district-card-name">Jua Kali Car Yard</div>
        <div class="district-card-tag" style="background:rgba(255,188,0,0.1);color:#FFBC00;border:1px solid rgba(255,188,0,0.25);">Transport</div>
        <div class="district-card-lesson">Bajaj, salon cars, and SUVs. Vehicles are income-generating assets when you know how to finance them right.</div>
      </div>
      <div class="district-card" style="--accent: #FF6B35;">
        <div class="district-card-icon">🎡</div>
        <div class="district-card-name">Fun World</div>
        <div class="district-card-tag" style="background:rgba(255,107,53,0.1);color:#FF6B35;border:1px solid rgba(255,107,53,0.25);">Lifestyle</div>
        <div class="district-card-lesson">Entertainment budgeting. The 50/30/20 rule in action. A good life isn't only about saving — learn to budget for joy.</div>
      </div>
      <div class="district-card" style="--accent: #A78BFA;">
        <div class="district-card-icon">📣</div>
        <div class="district-card-name">Community Centre</div>
        <div class="district-card-tag" style="background:rgba(167,139,250,0.1);color:#A78BFA;border:1px solid rgba(167,139,250,0.25);">Community</div>
        <div class="district-card-lesson">Shoutouts, Dreams Board, school leaderboard. Your story belongs in the city. Progress is public — and celebrated.</div>
      </div>
    </div>
  </div>
</div>

<!-- CAREER PATHS -->
<div class="careers-section">
  <div class="inner">
    <div class="section-eyebrow">8 Career Paths</div>
    <h2 class="section-title">Your career shapes<br>your entire game</h2>
    <p class="section-sub">The career quiz isn't a filter — it's a personalisation engine. Every scenario, every opportunity, every city event is calibrated to your real career world.</p>

    <div class="careers-grid">
      <div class="career-card">
        <div class="career-card-icon">💻</div>
        <div class="career-card-name">Technology</div>
        <div class="career-card-income">Developer · Designer · Data Analyst<br><strong>KES 35K–120K/mo</strong></div>
      </div>
      <div class="career-card">
        <div class="career-card-icon">📈</div>
        <div class="career-card-name">Business</div>
        <div class="career-card-income">Entrepreneur · Sales · Consultant<br><strong>KES 25K–200K/mo</strong></div>
      </div>
      <div class="career-card">
        <div class="career-card-icon">🏥</div>
        <div class="career-card-name">Healthcare</div>
        <div class="career-card-income">Nurse · CHO · Lab Tech<br><strong>KES 28K–80K/mo</strong></div>
      </div>
      <div class="career-card">
        <div class="career-card-icon">🌾</div>
        <div class="career-card-name">Agriculture</div>
        <div class="career-card-income">Farmer · Agri Manager<br><strong>KES 20K–60K/mo</strong></div>
      </div>
      <div class="career-card">
        <div class="career-card-icon">💰</div>
        <div class="career-card-name">Finance</div>
        <div class="career-card-income">Banker · Microfinance · CPA<br><strong>KES 30K–150K/mo</strong></div>
      </div>
      <div class="career-card">
        <div class="career-card-icon">📚</div>
        <div class="career-card-name">Education</div>
        <div class="career-card-income">Teacher · Tutor · Trainer<br><strong>KES 18K–45K/mo</strong></div>
      </div>
      <div class="career-card">
        <div class="career-card-icon">⚙️</div>
        <div class="career-card-name">Engineering</div>
        <div class="career-card-income">Civil · Mechanical · Electrical<br><strong>KES 40K–120K/mo</strong></div>
      </div>
      <div class="career-card">
        <div class="career-card-icon">🎨</div>
        <div class="career-card-name">Creative</div>
        <div class="career-card-income">Designer · Creator · Artist<br><strong>KES 15K–80K/mo</strong></div>
      </div>
    </div>
  </div>
</div>

<!-- MISSION -->
<div class="mission-section">
  <div class="mission-inner">
    <div class="mission-logo">🏙️</div>
    <p class="mission-quote">
      "We built Pesa City because <em>financial literacy shouldn't live in a textbook</em>. It should live in the decisions you make every day — and those decisions should have real consequences you can feel, replay, and learn from."
    </p>
    <p class="mission-attr"><strong>NGO Moski</strong> — Building financial skills for Kenyan youth through play</p>
  </div>
</div>

<!-- CTA -->
<div class="cta-section">
  <div class="cta-glow"></div>
  <div class="cta-inner">
    <h2 class="cta-title">Your financial life<br>starts in <span>Pesa City</span></h2>
    <p class="cta-sub">Join Kenya's most immersive financial literacy game. Choose your career, navigate the city, face real money decisions, and build the habits that last a lifetime.</p>
    @guest
      <a href="{{ route('register') }}" class="btn-primary" style="font-size:17px;padding:18px 40px;">
        🏙️ Start Playing — It's Free
      </a>
      <p class="cta-note">No credit card · Takes 5 minutes · Kenyan youth ages 8–26</p>
    @else
      <a href="{{ url('/world') }}" class="btn-primary" style="font-size:17px;padding:18px 40px;">
        🏙️ Return to Pesa City
      </a>
    @endguest
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-brand"><strong>PesaQuest</strong> by NGO Moski · &copy; 2026 · Nairobi, Kenya</div>
  <div class="footer-links">
    @guest
      <a href="{{ route('login') }}">Sign In</a>
      <a href="{{ route('register') }}">Register</a>
    @else
      <a href="{{ url('/world') }}">Pesa City</a>
      <a href="{{ url('/dashboard') }}">Dashboard</a>
    @endguest
  </div>
</footer>

</body>
</html>
