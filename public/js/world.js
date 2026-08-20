/* ═══════════════════════════════════════════════════════════════
   PESA CITY — WORLD JS  v3.0
   Phase 2: Walking polish + Phase 4-7: Missions, Opportunities,
            Badge popup, Opportunity Hub tabs
   Phase 7+: Sound scaffold, Fun World, Mobile sidebar
   ═══════════════════════════════════════════════════════════════ */

/* District pin destinations — the CENTER of each admin-calibrated tap-area
   rectangle (GameSet Hub → World Map), the same rectangle the tap-area itself
   is drawn from. One source of truth instead of two hand-kept arrays that
   could (and did) disagree. */
const DISTRICT_POSITIONS = Object.fromEntries(
  Object.entries(window.__PESA_DISTRICT_POS__ || {}).map(([slug, r]) => [
    slug, { left: r.left + r.width / 2, top: r.top + r.height / 2 },
  ])
);

/* ── Game-days → approximate real time (uses admin clock via __PESA_SPT__) ── */
window.gdApprox = function (days) {
  const spt = window.__PESA_SPT__ || 514; // fallback: 1 real hr = 1 game week
  const s   = Math.max(0, days) * spt;
  if (s < 90)    return 'under 2 real min';
  if (s < 3600)  return '≈' + Math.round(s / 60) + ' real min';
  if (s < 86400) return '≈' + (Math.round(s / 360) / 10) + ' real hrs';
  return '≈' + (Math.round(s / 8640) / 10) + ' real days';
};

/* ── Sound Manager — Phase 12: Procedural WebAudio (no MP3 files needed) ──
   All sounds are synthesised in real-time with the Web Audio API.
   Zero external file dependencies. Toggle with the 🔊 HUD button.
   ──────────────────────────────────────────────────────────────────────── */
const SoundMgr = (() => {
  let _ctx = null;          // AudioContext (lazy init on first user gesture)
  let _on  = true;
  let _zoneNode  = null;     // Active ambient oscillator graph for zone BGM
  let _worldNode = null;     // World-level background music (lower priority than zone)
  let _zoneFadeTimer = null;

  // ── MP3 file registry ────────────────────────────────────────────
  const _BASE = (window.__PESA_ASSET_BASE__ || '');
  const _FILES = {
    arrive:       _BASE + '/audio/arrive.mp3',
    badge:        _BASE + '/audio/badge.mp3',
    bonus:        _BASE + '/audio/bonus.mp3',
    close:        _BASE + '/audio/close.mp3',
    open:         _BASE + '/audio/open.mp3',
    quest:        _BASE + '/audio/quest-complete.mp3',
    notification: _BASE + '/audio/notification.mp3',
  };
  const _buffers   = {};          // decoded AudioBuffer cache
  let   _preloaded = false;
  let   _worldAudio = null;       // HTML5 Audio element for the looping map track

  async function _preloadFiles() {
    if (_preloaded) return;
    _preloaded = true;
    const ctx = _ctx_(); if (!ctx) return;
    await Promise.allSettled(
      Object.entries(_FILES).map(async ([name, url]) => {
        try {
          const ab = await (await fetch(url)).arrayBuffer();
          _buffers[name] = await ctx.decodeAudioData(ab);
        } catch (_) {}
      })
    );
  }

  function _playBuffer(name, vol) {
    const ctx = _ctx_(); if (!ctx) return false;
    const buf = _buffers[name]; if (!buf) return false;
    const src = ctx.createBufferSource();
    src.buffer = buf;
    const g = ctx.createGain();
    g.gain.value = vol ?? 1.0;
    src.connect(g); g.connect(ctx.destination);
    src.start();
    return true;
  }

  function _ctx_() {
    if (!_ctx) {
      try {
        _ctx = new (window.AudioContext || window.webkitAudioContext)();
      } catch (_) { return null; }
    }
    if (_ctx.state === 'suspended') { _ctx.resume().catch(() => {}); }
    return _ctx;
  }

  /* ── Utility helpers ──────────────────────────────────────────────── */
  function _osc(type, freq, startAt, dur, gain, detune = 0) {
    const ctx = _ctx_(); if (!ctx) return;
    const g = ctx.createGain();
    g.connect(ctx.destination);
    g.gain.setValueAtTime(0, startAt);
    g.gain.linearRampToValueAtTime(gain, startAt + 0.01);
    g.gain.exponentialRampToValueAtTime(0.001, startAt + dur);

    const o = ctx.createOscillator();
    o.type = type;
    o.frequency.setValueAtTime(freq, startAt);
    o.detune.setValueAtTime(detune, startAt);
    o.connect(g);
    o.start(startAt);
    o.stop(startAt + dur + 0.02);
  }

  function _noise(startAt, dur, gainVal, hiCut = 2400) {
    const ctx = _ctx_(); if (!ctx) return;
    const bufSz = Math.ceil(ctx.sampleRate * dur);
    const buf   = ctx.createBuffer(1, bufSz, ctx.sampleRate);
    const data  = buf.getChannelData(0);
    for (let i = 0; i < bufSz; i++) data[i] = (Math.random() * 2 - 1);

    const src = ctx.createBufferSource();
    src.buffer = buf;
    const filt = ctx.createBiquadFilter();
    filt.type = 'lowpass';
    filt.frequency.value = hiCut;

    const g = ctx.createGain();
    g.gain.setValueAtTime(gainVal, startAt);
    g.gain.exponentialRampToValueAtTime(0.001, startAt + dur);

    src.connect(filt); filt.connect(g); g.connect(ctx.destination);
    src.start(startAt); src.stop(startAt + dur + 0.02);
  }

  /* ── Musical marimba-style bell helper ───────────────────────────── */
  function _bell(freq, startAt, dur, gain) {
    const ctx = _ctx_(); if (!ctx) return;
    const g = ctx.createGain();
    g.gain.setValueAtTime(gain, startAt);
    g.gain.exponentialRampToValueAtTime(0.001, startAt + dur);
    g.connect(ctx.destination);
    // Primary tone + 2nd harmonic (marimba character)
    [1, 2.756].forEach((ratio, i) => {
      const o = ctx.createOscillator();
      o.type = i === 0 ? 'triangle' : 'sine';
      o.frequency.value = freq * ratio;
      const hg = ctx.createGain(); hg.gain.value = i === 0 ? 1 : 0.32;
      o.connect(hg); hg.connect(g);
      o.start(startAt); o.stop(startAt + dur + 0.05);
    });
  }

  /* ── SFX library — MP3 first, procedural fallback ─────────────── */
  const _sfx = {

    step() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      const g = ctx.createGain();
      g.gain.setValueAtTime(0.12, t);
      g.gain.exponentialRampToValueAtTime(0.001, t + 0.09);
      g.connect(ctx.destination);
      const o = ctx.createOscillator();
      o.type = 'triangle';
      o.frequency.setValueAtTime(180, t);
      o.frequency.exponentialRampToValueAtTime(80, t + 0.08);
      o.connect(g); o.start(t); o.stop(t + 0.1);
    },

    // Faint bird tweets — used by the map ambience layer when a flock passes
    chirp() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [[2900, 0], [3300, 0.13], [2600, 0.27]].forEach(([f, d]) => {
        const g = ctx.createGain();
        g.gain.setValueAtTime(0, t + d);
        g.gain.linearRampToValueAtTime(0.045, t + d + 0.015);
        g.gain.exponentialRampToValueAtTime(0.001, t + d + 0.1);
        g.connect(ctx.destination);
        const o = ctx.createOscillator();
        o.type = 'sine';
        o.frequency.setValueAtTime(f, t + d);
        o.frequency.exponentialRampToValueAtTime(f * 0.72, t + d + 0.09);
        o.connect(g); o.start(t + d); o.stop(t + d + 0.12);
      });
    },

    arrive() {
      if (_playBuffer('arrive', 0.75)) return;
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [[523,0],[659,0.11],[784,0.22],[988,0.34],[1047,0.48]].forEach(([f,d]) => {
        _bell(f, t + d, 0.7, 0.16);
      });
    },

    open() {
      if (_playBuffer('open', 0.55)) return;
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      _bell(523, t,       0.25, 0.14);
      _bell(784, t + 0.1, 0.30, 0.10);
    },

    close() {
      if (_playBuffer('close', 0.50)) return;
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      _bell(784, t,       0.20, 0.12);
      _bell(523, t + 0.1, 0.25, 0.08);
    },

    badge() {
      if (_playBuffer('badge', 0.80)) return;
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [523,659,784,880,1047].forEach((f, i) => _bell(f, t + i * 0.09, 0.8, 0.15));
      setTimeout(() => {
        const t2 = _ctx_()?.currentTime ?? 0;
        [523,659,784].forEach(f => _bell(f, t2, 1.2, 0.10));
      }, 480);
    },

    bonus() {
      if (_playBuffer('bonus', 0.65)) return;
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [523,659,784,1047,1319].forEach((f, i) => _bell(f, t + i * 0.06, 0.4, 0.13));
    },

    quest()        { _playBuffer('quest',        0.85); },
    notification() { _playBuffer('notification', 0.65); },

    // Fun World celebration — bouncy carnival arpeggio with a sparkle tail
    fun() {
      if (_playBuffer('fun', 0.8)) return;
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      // Rising major-sixth bounce
      [[523,0],[659,0.09],[784,0.18],[880,0.27],[1047,0.38]].forEach(([f,d]) => _bell(f, t + d, 0.5, 0.15));
      // Sparkle tail — quick high shimmer
      [1319,1568,2093].forEach((f, i) => _bell(f, t + 0.5 + i * 0.07, 0.3, 0.08));
    },
  };

  /* ── Zone ambient BGM (procedural drones, no MP3) ─────────────────── */
  const _zoneDefs = {
    'marketplace':     { root: 174, mode: 'mixo',  speed: 1.4,  atmo: 0.08 },
    'opportunity-hub': { root: 220, mode: 'dorian', speed: 1.8,  atmo: 0.07 },
    'bank':            { root: 196, mode: 'major',  speed: 0.9,  atmo: 0.06 },
    'savings':         { root: 196, mode: 'major',  speed: 0.9,  atmo: 0.06 },
    'fun-world':       { root: 261, mode: 'lydian', speed: 2.2,  atmo: 0.10 },
    'community':       { root: 246, mode: 'major',  speed: 1.2,  atmo: 0.08 },
    'estates':         { root: 164, mode: 'major',  speed: 0.8,  atmo: 0.06 },
    'car-yard':        { root: 185, mode: 'mixo',   speed: 1.6,  atmo: 0.07 },
    'quests':          { root: 207, mode: 'lydian', speed: 1.5,  atmo: 0.09 },
    'workplace':       { root: 233, mode: 'dorian', speed: 1.1,  atmo: 0.06 },
    default:           { root: 130, mode: 'dorian', speed: 1.0,  atmo: 0.05 },
  };

  const _scales = {
    major:  [0, 4, 7, 12, 16, 19],
    dorian: [0, 2, 3, 7, 10, 12],
    mixo:   [0, 4, 7, 10, 12, 16],
    lydian: [0, 4, 6, 7, 11, 12],
  };

  function _startAmbient(def) {
    const ctx = _ctx_(); if (!ctx) return null;
    const master = ctx.createGain();
    master.gain.setValueAtTime(0, ctx.currentTime);
    master.gain.linearRampToValueAtTime(0.04, ctx.currentTime + 2);
    master.connect(ctx.destination);
    const scale = _scales[def.mode] || _scales.major;
    const node = { master, oscs: [], active: true };

    function chime() {
      if (!node.active) return;
      const c = _ctx_(); if (!c) return;
      const t = c.currentTime + 0.1;
      const ni = scale[Math.floor(Math.random() * scale.length)];
      const freq = def.root * Math.pow(2, ni / 12) * (Math.random() > 0.6 ? 1 : 2);
      const g = c.createGain();
      g.gain.setValueAtTime(0, t);
      g.gain.linearRampToValueAtTime(0.045, t + 0.06);
      g.gain.exponentialRampToValueAtTime(0.001, t + 2.2);
      g.connect(master);
      const o = c.createOscillator();
      o.type = 'sine';
      o.frequency.value = freq;
      o.connect(g);
      o.start(t); o.stop(t + 2.3);
      node.oscs.push(o);
      setTimeout(chime, 2200 + Math.random() * 2800);
    }

    chime();
    setTimeout(chime, 700 + Math.random() * 900);
    return node;
  }

  function _stopAmbient(node) {
    if (!node) return;
    node.active = false;
    const ctx = _ctx_();
    if (ctx) {
      node.master.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.8);
    }
    setTimeout(() => {
      try {
        node.oscs.forEach(o => { try { o.stop(); } catch (_) {} });
        node.master.disconnect();
      } catch (_) {}
    }, 900);
  }

  return {
    get on() { return _on; },

    play(name) {
      if (!_on) return;
      try { _sfx[name]?.(); } catch (_) {}
    },

    playZone(slug) {
      if (!_on) return;
      if (_zoneFadeTimer) { clearTimeout(_zoneFadeTimer); _zoneFadeTimer = null; }
      if (_zoneNode) { _stopAmbient(_zoneNode); }
      const def = _zoneDefs[slug] || _zoneDefs.default;
      try { _zoneNode = _startAmbient(def); } catch (_) { _zoneNode = null; }
    },

    stopZone() {
      if (_zoneNode) {
        _stopAmbient(_zoneNode);
        _zoneNode = null;
      }
    },

    // Background world BGM — plays map-loop.mp3, falls back to procedural arpeggios
    playWorld() {
      if (!_on) return;
      // HTML5 Audio for the long looping map track
      if (!_worldAudio) {
        try {
          _worldAudio = new Audio(_BASE + '/audio/map-loop.mp3');
          _worldAudio.loop   = true;
          _worldAudio.volume = 0.38;
        } catch (_) { _worldAudio = null; }
      }
      if (_worldAudio) {
        if (_worldAudio.paused) _worldAudio.play().catch(() => {});
        return;
      }
      // Procedural fallback if file unavailable
      if (_worldNode) return;
      const ctx = _ctx_(); if (!ctx) return;
      const master = ctx.createGain();
      master.gain.setValueAtTime(0, ctx.currentTime);
      master.gain.linearRampToValueAtTime(0.05, ctx.currentTime + 2.5);
      master.connect(ctx.destination);
      const oscs = [];
      const scale = [261.6, 329.6, 392.0, 440.0, 523.3, 587.3];
      const patts = [[0,2,4],[1,3,5],[0,2,4,5],[2,4,1,3],[0,1,3,2]];
      let step = 0;
      const node = { master, oscs, active: true, _grooveTimer: null };
      function arp() {
        if (!node.active) return;
        const c = _ctx_(); if (!c) return;
        const p = patts[step++ % patts.length];
        const base = c.currentTime + 0.1;
        const nl = 0.58;
        p.forEach((ni, i) => {
          const t = base + i * nl;
          const g = c.createGain();
          g.gain.setValueAtTime(0, t);
          g.gain.linearRampToValueAtTime(0.065, t + 0.04);
          g.gain.exponentialRampToValueAtTime(0.001, t + nl * 1.5);
          g.connect(master);
          const o = c.createOscillator();
          o.type = 'sine';
          o.frequency.value = scale[ni % scale.length];
          o.connect(g); o.start(t); o.stop(t + nl * 1.6);
          oscs.push(o);
        });
        setTimeout(arp, (p.length * nl + 2.2) * 1000);
      }
      arp();
      _worldNode = node;
    },

    stopWorld() {
      if (_worldAudio && !_worldAudio.paused) _worldAudio.pause();
      if (_worldNode) { clearInterval(_worldNode._grooveTimer); _stopAmbient(_worldNode); _worldNode = null; }
    },

    preload() { _preloadFiles().catch(() => {}); },

    toggle() {
      _on = !_on;
      if (!_on) { this.stopZone(); this.stopWorld(); }
      return _on;
    },
  };
})();

// Expose for the map ambience layer (bird chirps etc.) — respects the mute toggle
window.PesaSound = SoundMgr;

/* HOME is the stickman's base — the house at bottom-center of the map */
const PLAZA_POS = { left: 48, top: 70 };

/* ── Waypoint graph — road nodes matching worldmap.png roads ─── */
const WAYPOINTS = {
  // Home base
  'spawn':      { x: 48, y: 70 },
  // Road from home up to the city center
  's-junction': { x: 48, y: 64 },
  // Main city intersections
  'nw-cross':   { x: 28, y: 20 },
  'n-road':     { x: 48, y: 13 },
  'ne-cross':   { x: 67, y: 14 },
  'far-e-top':  { x: 84, y: 14 },
  'w-road':     { x: 13, y: 38 },
  'mid-cross':  { x: 48, y: 38 },
  'e-cross':    { x: 67, y: 36 },
  'mid-se':     { x: 67, y: 55 },
  'sw-road':    { x: 28, y: 57 },
  // District entrances
  'd-marketplace':   { x: 56, y: 21 },
  'd-opp-hub':       { x: 10, y: 18 },
  'd-bank':          { x: 73, y: 40 },
  'd-savings':       { x: 73, y: 69 },
  'd-estates':       { x: 20, y: 60 },
  'd-car-yard':      { x:  7, y: 39 },
  'd-fun-world':     { x: 84, y: 17 },
  'd-community':     { x: 63, y: 53 },
  'd-quests':        { x: 46, y: 44 },
  'd-workplace':     { x: 33, y: 28 },
};

/* ── Routes from HOME to each district — follow road nodes ─── */
const DISTRICT_ROUTES = {
  'marketplace':     ['spawn', 's-junction', 'mid-cross', 'n-road', 'd-marketplace'],
  'opportunity-hub': ['spawn', 's-junction', 'mid-cross', 'nw-cross', 'd-opp-hub'],
  'bank':            ['spawn', 's-junction', 'mid-cross', 'e-cross', 'd-bank'],
  'savings':         ['spawn', 's-junction', 'mid-cross', 'e-cross', 'mid-se', 'd-savings'],
  'estates':         ['spawn', 's-junction', 'sw-road', 'd-estates'],
  'car-yard':        ['spawn', 's-junction', 'sw-road', 'w-road', 'd-car-yard'],
  'fun-world':       ['spawn', 's-junction', 'mid-cross', 'n-road', 'ne-cross', 'far-e-top', 'd-fun-world'],
  'community':       ['spawn', 's-junction', 'mid-cross', 'e-cross', 'mid-se', 'd-community'],
  'quests':          ['spawn', 's-junction', 'mid-cross', 'd-quests'],
  'workplace':       ['spawn', 's-junction', 'mid-cross', 'nw-cross', 'd-workplace'],
};

/* ── Quest Tips — contextual financial nudges per quest ───────────
   Written in Kenyan youth voice. Indirect, educational, fun.
   Educate through discovery — never lecture directly.
   ──────────────────────────────────────────────────────────────── */
const QUEST_TIPS = {
  'get-connected': [
    {
      icon: '📱',
      text: 'A device isn\'t a flex — it\'s your earning tool. M-Pesa agent, gig apps, social selling, job applications. Every hustle starts with connectivity.',
    },
    {
      icon: '🎓',
      text: 'Short on cash? Upskill first. A free course at the Opportunity Hub unlocks job applications — and one month\'s salary can cover a starter phone.',
    },
    {
      icon: '💰',
      text: 'Check your Life Board. You might already have an income stream you haven\'t noticed — savings returns, asset earnings, or a pending salary.',
    },
    {
      icon: '🏪',
      text: 'Mama Mboga Market stocks devices at different price points. You don\'t need the best one — you need the one that fits what you have today.',
    },
    {
      icon: '⚡',
      text: 'Every game day without a device is a day without income tools. The clock is ticking — explore your options and move.',
    },
  ],
  'level-up-skills': [
    {
      icon: '📚',
      text: 'Free courses at the Opportunity Hub cost zero and open doors that cash alone can\'t. Knowledge is the cheapest investment with the highest return.',
    },
    {
      icon: '🎯',
      text: 'Tech, Business, Finance, Creative — pick the track that fits your hustle. Passion + skill = sustainable income. Bored skills don\'t stick.',
    },
    {
      icon: '🛣️',
      text: '"I\'ll learn it later" is how opportunity passes you by. One certificate now changes what jobs you qualify for — permanently.',
    },
    {
      icon: '💡',
      text: 'Your course completion badge shows on your profile. Employers in Pesa City can see it. That\'s what unlocks job applications.',
    },
    {
      icon: '🚀',
      text: 'The Opportunity Hub is your shortcut from zero to employed. Most successful players completed a course within their first 10 game days.',
    },
  ],
  'first-hustle': [
    {
      icon: '💼',
      text: 'Your first job doesn\'t have to be your dream role. It starts the income clock, builds your employment record, and pays every game month.',
    },
    {
      icon: '📊',
      text: 'Once employed, salary lands automatically on your next login. Check your Life Board — it shows exactly when your next payday arrives.',
    },
    {
      icon: '🚀',
      text: 'Apply even if you feel under-qualified. Requirement met? Apply immediately. Worst case: no. Best case: income that compounds for months.',
    },
    {
      icon: '🏦',
      text: 'Employment is step 1 of 4: get hired → save consistently → invest in assets → build towards your first property in Kiambu Estates.',
    },
    {
      icon: '💡',
      text: 'Employed players earn 4× more lifetime income in Pesa City. The hustle isn\'t optional — it\'s the foundation everything else stands on.',
    },
  ],
  'first-investment': [
    {
      icon: '📈',
      text: 'Investing means putting money to work so you don\'t have to work every shilling. Shares, M-Akiba bonds, or unit trusts — start small, start now.',
    },
    {
      icon: '⏳',
      text: 'Time in the market beats timing the market. KES 5,000 invested today compounds longer than KES 20,000 invested in two years. Start now.',
    },
    {
      icon: '🎯',
      text: 'Risk and return move together. High returns always carry higher risk. Match your investment to how much loss you can live with — not just potential gain.',
    },
    {
      icon: '📊',
      text: 'Diversify. Spreading across 3–4 assets is safer than betting everything on one winner. Even the best stocks have bad months.',
    },
    {
      icon: '💡',
      text: 'Liquid cash earns nothing. Money sitting in a wallet loses value to inflation every month. Investing is how you fight back.',
    },
  ],
  'first-property': [
    {
      icon: '🏠',
      text: 'Owning land is how most Kenyan families build generational wealth. A plot outside Nairobi today can be a 3-bedroom home in five years.',
    },
    {
      icon: '📋',
      text: 'Before buying property: confirm ownership with a title deed search at the Lands Registry. Verbal agreements don\'t hold in court.',
    },
    {
      icon: '💳',
      text: 'A good credit score opens the door to affordable mortgage rates. Start building yours now — pay bills on time, keep debt low, avoid defaults.',
    },
    {
      icon: '🔑',
      text: 'Rental income is passive income. A bedsitter earning KES 12,000/month means your asset is working while you sleep.',
    },
    {
      icon: '🏗️',
      text: 'Your first property doesn\'t have to be a mansion. A plot, a studio, a stall — what matters is that it\'s yours and it\'s appreciating.',
    },
  ],
  'first-business': [
    {
      icon: '🏪',
      text: 'A business turns your skill into an asset that runs beyond your own hours. You stop trading time for money and start building a system.',
    },
    {
      icon: '📝',
      text: 'Register your business — a Sole Proprietorship at Huduma Centre costs less than KES 1,000 and gives you credibility for tenders and bank loans.',
    },
    {
      icon: '💰',
      text: 'Separate your business money from personal money from day one. Mixing accounts is why most SMEs fail in year two — they can\'t read their own finances.',
    },
    {
      icon: '🎯',
      text: 'A business solves a problem for a specific person. Not everyone. One customer avatar, one problem, one clear offer — then scale.',
    },
    {
      icon: '📊',
      text: 'Profit is not revenue. Track cost of goods, operating expenses, and net profit monthly. If you don\'t know your margin, you\'re guessing.',
    },
  ],
};
const CSRF      = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const HEADERS   = () => ({
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-CSRF-TOKEN': CSRF(),
});

// ═══════════════════════════════════════════════════════════════
//  PESA CITY CANVAS MAP — Phase 13
//  Procedurally illustrated city drawn via Canvas 2D API.
//  Replaces Phase 12 CSS placeholders — no image files needed.
// ═══════════════════════════════════════════════════════════════

let _cityCanvas = null;
let _cityCtx    = null;
let _walkPath   = null;   // { fx, fy, tx, ty } — % coords, active during walk

function _redrawCanvas() {
  if (_cityCanvas && _cityCtx) _drawCity(_cityCtx, _cityCanvas.width, _cityCanvas.height);
}

function initCityCanvas() {
  // Map now uses worldmap.png background — no canvas needed
  const map = document.getElementById('pc-map');
  if (map) map.classList.add('pc-imgmap-active');
}

function _drawCity(ctx, W, H) {
  const X = p => W * p / 100;
  const Y = p => H * p / 100;
  ctx.clearRect(0, 0, W, H);

  // ── 1. GRASS BASE ─────────────────────────────────────────────────
  const grassG = ctx.createLinearGradient(0, 0, 0, H);
  grassG.addColorStop(0,    '#4A9045');
  grassG.addColorStop(0.42, '#3A7C36');
  grassG.addColorStop(0.78, '#2E6A2C');
  grassG.addColorStop(1,    '#1E4D1E');
  ctx.fillStyle = grassG; ctx.fillRect(0, 0, W, H);

  // Soft texture patches
  ctx.fillStyle = 'rgba(255,255,255,0.033)';
  [[14,7],[32,4],[54,11],[79,7],[10,30],[47,24],[76,28],[19,54],[50,49],[87,50],[9,75],[42,82],[68,79]].forEach(([gx,gy])=>{
    ctx.beginPath(); ctx.ellipse(X(gx), Y(gy), X(5.5), Y(3.5), 0, 0, Math.PI*2); ctx.fill();
  });

  // ── 2. NAIROBI RIVER ─────────────────────────────────────────────
  ctx.save();
  ctx.beginPath();
  ctx.moveTo(0, Y(80));
  ctx.bezierCurveTo(X(18),Y(77), X(36),Y(83), X(52),Y(79));
  ctx.bezierCurveTo(X(68),Y(75), X(82),Y(83), W, Y(79));
  ctx.lineTo(W, H); ctx.lineTo(0, H); ctx.closePath();
  const rivG = ctx.createLinearGradient(0, Y(76), 0, H);
  rivG.addColorStop(0,    '#42A5F5');
  rivG.addColorStop(0.35, '#1E88E5');
  rivG.addColorStop(1,    '#0D47A1');
  ctx.fillStyle = rivG; ctx.fill();
  ctx.strokeStyle = 'rgba(255,255,255,0.18)'; ctx.lineWidth = 1.5;
  ctx.setLineDash([X(1.5), X(1.5)]);
  for(let i=0;i<4;i++){
    ctx.beginPath();
    ctx.moveTo(X(5+i*18), Y(81+i*0.5));
    ctx.quadraticCurveTo(X(14+i*18), Y(79.2), X(23+i*18), Y(81.5+i*0.5));
    ctx.stroke();
  }
  ctx.setLineDash([]);
  ctx.fillStyle = 'rgba(255,255,255,0.48)';
  ctx.font = `italic ${Math.max(9,X(1.1))}px Georgia,serif`;
  ctx.textAlign = 'center';
  ctx.fillText('~ Nairobi River ~', X(50), Y(87.5));
  ctx.restore();

  // ── 3. ROADS ──────────────────────────────────────────────────────
  function road(x1,y1,x2,y2,w){
    ctx.save();
    ctx.lineCap = 'butt';
    ctx.strokeStyle = 'rgba(0,0,0,0.22)'; ctx.lineWidth = w+6;
    ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
    ctx.strokeStyle = '#3E3E3E'; ctx.lineWidth = w;
    ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
    ctx.strokeStyle = 'rgba(255,255,255,0.07)'; ctx.lineWidth = 1.5;
    ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
    ctx.strokeStyle = 'rgba(255,225,0,0.4)'; ctx.lineWidth = 1.5;
    ctx.setLineDash([X(1.6),X(1.8)]);
    ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
    ctx.setLineDash([]);
    ctx.restore();
  }
  road(0,Y(47),W,Y(47),X(3.2));
  road(X(48),0,X(48),H,X(3.2));
  road(0,Y(22),W,Y(22),X(2.2));
  road(0,Y(72),W,Y(72),X(2.2));
  road(X(22),0,X(22),H,X(2.2));
  road(X(72),0,X(72),H,X(2.2));

  // ── 4. CENTRAL PLAZA ──────────────────────────────────────────────
  {
    const cx=X(48), cy=Y(47), pr=X(5.5);
    ctx.beginPath(); ctx.arc(cx,cy,pr,0,Math.PI*2);
    ctx.fillStyle='#C4A064'; ctx.fill();
    ctx.strokeStyle='#9E804A'; ctx.lineWidth=2; ctx.stroke();
    ctx.beginPath(); ctx.arc(cx,cy,pr*0.75,0,Math.PI*2);
    ctx.strokeStyle='rgba(158,128,74,0.35)'; ctx.lineWidth=1;
    ctx.setLineDash([3,5]); ctx.stroke(); ctx.setLineDash([]);
    ctx.beginPath(); ctx.arc(cx,cy,pr*0.38,0,Math.PI*2);
    ctx.fillStyle='#5AB3E8'; ctx.fill();
    ctx.strokeStyle='#3A93C8'; ctx.lineWidth=1.5; ctx.stroke();
    ctx.fillStyle='rgba(120,195,255,0.5)';
    ctx.beginPath(); ctx.arc(cx,cy-pr*0.1,pr*0.08,0,Math.PI*2); ctx.fill();
  }

  // ── 5. HELPERS ────────────────────────────────────────────────────
  function shadow(lx,ty,bw,bh){ ctx.fillStyle='rgba(0,0,0,0.18)'; ctx.fillRect(lx+5,ty+5,bw,bh); }
  function walls(lx,ty,bw,bh,c){
    ctx.fillStyle=c; ctx.fillRect(lx,ty,bw,bh);
    const s=ctx.createLinearGradient(lx,ty,lx+bw,ty);
    s.addColorStop(0,'rgba(255,255,255,0.13)'); s.addColorStop(0.3,'rgba(255,255,255,0)'); s.addColorStop(1,'rgba(0,0,0,0.09)');
    ctx.fillStyle=s; ctx.fillRect(lx,ty,bw,bh);
  }
  function gable(lx,ty,bw,rh,rc){
    ctx.fillStyle=rc;
    ctx.beginPath(); ctx.moveTo(lx-X(0.5),ty); ctx.lineTo(lx+bw/2,ty-rh); ctx.lineTo(lx+bw+X(0.5),ty); ctx.closePath(); ctx.fill();
    ctx.fillStyle='rgba(0,0,0,0.12)'; ctx.fillRect(lx,ty,bw,3);
  }
  function win(x,y,ww,wh,c){
    c=c||'#87CEEB'; ctx.fillStyle=c; ctx.fillRect(x,y,ww,wh);
    ctx.fillStyle='rgba(255,255,255,0.38)'; ctx.fillRect(x,y,ww*0.44,wh*0.44);
  }
  function lbl(text,cx,y,sz,col){
    ctx.save(); ctx.fillStyle=col||'rgba(255,255,255,0.95)';
    ctx.font=`bold ${Math.max(9,sz)}px system-ui,sans-serif`; ctx.textAlign='center';
    ctx.shadowColor='rgba(0,0,0,0.85)'; ctx.shadowBlur=6;
    ctx.fillText(text,cx,y); ctx.shadowBlur=0; ctx.restore();
  }

  // ── 6A. MARKETPLACE — Mama Mboga (56%, 13%) ───────────────────────
  {
    const cx=X(56),cy=Y(13),bw=X(8.5),bh=Y(10),lx=cx-bw/2,ty=cy-bh;
    ctx.fillStyle='#4E7A3A'; ctx.fillRect(lx-X(1.5),ty-Y(1),bw+X(3),bh+Y(3.5));
    shadow(lx,ty,bw,bh); walls(lx,ty,bw,bh,'#CF7A42'); gable(lx,ty,bw,Y(3.2),'#8B3A1A');
    // Red/white awning
    const aww=bw*1.12,awh=Y(3),awx=cx-aww/2,awy=ty+bh*0.26;
    for(let i=0;i<8;i++){ ctx.fillStyle=i%2?'#FFF':'#CC2222'; ctx.fillRect(awx+i*aww/8,awy,aww/8+1,awh); }
    ctx.fillStyle='#AA1111';
    for(let i=0;i<16;i++){ ctx.beginPath(); ctx.arc(awx+(i+0.5)*aww/16,awy+awh,aww/32*0.9,0,Math.PI); ctx.fill(); }
    ctx.fillStyle='rgba(0,0,0,0.1)'; ctx.fillRect(awx,awy+awh-2,aww,2);
    // Door
    ctx.fillStyle='#3D1508'; ctx.fillRect(cx-X(1.1),ty+bh-Y(3.8),X(2.2),Y(3.8));
    ctx.fillStyle='#FFB300'; ctx.beginPath(); ctx.arc(cx+X(0.65),ty+bh-Y(2.1),X(0.17),0,Math.PI*2); ctx.fill();
    win(lx+X(1),ty+Y(1.2),X(2.2),Y(2.5)); win(lx+bw-X(3.2),ty+Y(1.2),X(2.2),Y(2.5));
    // Produce stalls
    ['#E53935','#43A047','#FB8C00','#1E88E5'].forEach((c,s)=>{ ctx.fillStyle=c; ctx.fillRect(lx+s*(bw/4),ty+bh-Y(0.9),bw/4-2,Y(0.9)); });
    // Sign
    ctx.fillStyle='#1B5E20'; ctx.fillRect(lx+X(0.8),ty+bh*0.6,bw-X(1.6),Y(2.3));
    ctx.fillStyle='#FFD700'; ctx.font=`bold ${Math.max(7,X(1.1))}px system-ui`; ctx.textAlign='center';
    ctx.fillText('MAMA MBOGA',cx,ty+bh*0.6+Y(1.55));
  }

  // ── 6B. OPPORTUNITY HUB (8%, 10%) ────────────────────────────────
  {
    const cx=X(8),cy=Y(10),bw=X(8),bh=Y(10.5),lx=cx-bw/2,ty=cy-bh;
    ctx.fillStyle='#5A8C48'; ctx.fillRect(lx-X(1.2),ty-Y(0.8),bw+X(2.4),bh+Y(4.5));
    shadow(lx,ty,bw,bh); walls(lx,ty,bw,bh,'#7AAEC8');
    // Flat roof + parapet
    ctx.fillStyle='#2E6488'; ctx.fillRect(lx-X(0.4),ty-Y(1.6),bw+X(0.8),Y(1.6));
    for(let c=0;c<5;c++){ ctx.fillStyle='#3A7AA0'; ctx.fillRect(lx+c*(bw/5),ty-Y(2.8),bw/5-3,Y(1.4)); }
    // Columns
    for(let c=0;c<4;c++){
      const colX=lx+(c+0.5)*bw/4-X(0.45);
      ctx.fillStyle='#C8E4F5'; ctx.fillRect(colX,ty,X(0.9),bh);
      ctx.fillStyle='#2E6488'; ctx.fillRect(colX-X(0.22),ty,X(1.34),Y(0.9)); ctx.fillRect(colX-X(0.22),ty+bh-Y(0.9),X(1.34),Y(0.9));
    }
    // Steps
    for(let s=0;s<3;s++){ ctx.fillStyle=`hsl(205,35%,${68+s*5}%)`; ctx.fillRect(lx+s*X(0.4),ty+bh+s*Y(0.38),bw-s*X(0.8),Y(0.38)); }
    // Sign
    ctx.fillStyle='#173456'; ctx.fillRect(lx+X(0.8),ty+bh*0.47,bw-X(1.6),Y(3));
    ctx.fillStyle='#FFD700'; ctx.font=`bold ${Math.max(7,X(1))}px system-ui`; ctx.textAlign='center';
    ctx.fillText('OPPORTUNITY',cx,ty+bh*0.47+Y(1.25)); ctx.fillText('HUB',cx,ty+bh*0.47+Y(2.5));
    win(lx+X(0.8),ty+Y(1.2),X(2),Y(2)); win(lx+bw-X(2.8),ty+Y(1.2),X(2),Y(2));
    // Flag
    ctx.strokeStyle='#999'; ctx.lineWidth=1.5;
    ctx.beginPath(); ctx.moveTo(cx+X(2.5),ty-Y(1.6)); ctx.lineTo(cx+X(2.5),ty-Y(5.2)); ctx.stroke();
    ctx.fillStyle='#0052CC'; ctx.fillRect(cx+X(2.5),ty-Y(5.2),X(2.5),Y(1.6));
    ctx.fillStyle='rgba(255,255,255,0.45)'; ctx.fillRect(cx+X(2.5),ty-Y(5.2),X(2.5),Y(0.65));
  }

  // ── 6C. EQUITY SQUARE — Bank (66%, 38%) ──────────────────────────
  {
    const cx=X(66),cy=Y(38),bw=X(9.5),bh=Y(11),lx=cx-bw/2,ty=cy-bh;
    ctx.fillStyle='#5A6E45'; ctx.fillRect(lx-X(1.5),ty-Y(0.8),bw+X(3),bh+Y(5.5));
    shadow(lx,ty,bw,bh); walls(lx,ty,bw,bh,'#C5CACD');
    // Pediment
    ctx.fillStyle='#9EA4A8';
    ctx.beginPath(); ctx.moveTo(lx-X(1.2),ty); ctx.lineTo(cx,ty-Y(4.8)); ctx.lineTo(lx+bw+X(1.2),ty); ctx.closePath(); ctx.fill();
    ctx.strokeStyle='#858B8E'; ctx.lineWidth=1; ctx.stroke();
    ctx.fillStyle='#AAAEB2'; ctx.fillRect(lx-X(0.2),ty,bw+X(0.4),Y(1.3));
    // Columns (5)
    for(let c=0;c<5;c++){
      const colX=lx+(c+0.5)*bw/5-X(0.42);
      ctx.fillStyle='#DDE2E5'; ctx.fillRect(colX,ty,X(0.85),bh);
      ctx.fillStyle='#AEB3B6'; ctx.fillRect(colX-X(0.27),ty,X(1.4),Y(1)); ctx.fillRect(colX-X(0.27),ty+bh-Y(1),X(1.4),Y(1));
    }
    // Steps
    for(let s=2;s>=0;s--){ ctx.fillStyle=`hsl(210,8%,${60+s*7}%)`; ctx.fillRect(lx-s*X(0.9),ty+bh+s*Y(0.42),bw+s*X(1.8),Y(0.42)); }
    // Double doors
    ctx.fillStyle='#1C2E48';
    ctx.fillRect(cx-X(1.5),ty+bh-Y(4.2),X(1.3),Y(4.2)); ctx.fillRect(cx+X(0.2),ty+bh-Y(4.2),X(1.3),Y(4.2));
    ctx.beginPath(); ctx.arc(cx,ty+bh-Y(4.2),X(1.5),Math.PI,0); ctx.fill();
    // Sign
    ctx.fillStyle='#1A237E'; ctx.fillRect(lx+X(1.8),ty+bh*0.5,bw-X(3.6),Y(2.5));
    ctx.fillStyle='#FFD700'; ctx.font=`bold ${Math.max(7,X(1.1))}px system-ui`; ctx.textAlign='center';
    ctx.fillText('EQUITY',cx,ty+bh*0.5+Y(1.2)); ctx.fillText('SQUARE',cx,ty+bh*0.5+Y(2.2));
  }

  // ── 6D. KIAMBU ESTATES (28%, 63%) ────────────────────────────────
  {
    const cx=X(28),cy=Y(63),bw=X(7.5),bh=Y(9.5),lx=cx-bw/2,ty=cy-bh;
    ctx.fillStyle='#52A84A'; ctx.fillRect(lx-X(1.8),ty-Y(0.6),bw+X(3.6),bh+Y(5));
    [['#FF6B6B',lx-X(1.1)],['#FFB347',lx-X(0.3)],['#87CEEB',lx+bw+X(0.3)]].forEach(([fc,fx])=>{
      ctx.fillStyle=fc; ctx.beginPath(); ctx.arc(fx,ty+bh+Y(1.6),X(0.38),0,Math.PI*2); ctx.fill();
    });
    shadow(lx,ty,bw,bh); walls(lx,ty,bw,bh,'#F0D49A'); gable(lx,ty,bw,Y(4.2),'#A83020');
    // Shuttered windows
    [lx+X(0.5),lx+bw-X(2.9)].forEach(wx=>{
      ctx.fillStyle='#7A4820'; ctx.fillRect(wx-X(0.15),ty+Y(1.4),X(0.38),Y(3.2)); ctx.fillRect(wx+X(2.2),ty+Y(1.4),X(0.38),Y(3.2));
      win(wx+X(0.22),ty+Y(1.6),X(2),Y(2.8));
    });
    // Arched door
    ctx.fillStyle='#5C2810'; const dw=X(2.2),dh=Y(4.2);
    ctx.fillRect(cx-dw/2,ty+bh-dh,dw,dh);
    ctx.beginPath(); ctx.arc(cx,ty+bh-dh,dw/2,Math.PI,0); ctx.fill();
    ctx.fillStyle='#FFD700'; ctx.beginPath(); ctx.arc(cx+dw*0.28,ty+bh-dh*0.42,X(0.15),0,Math.PI*2); ctx.fill();
    // Fence
    ctx.strokeStyle='#7A5C2E'; ctx.lineWidth=2;
    const fY=ty+bh+Y(0.9),fX1=lx-X(1.5),fX2=lx+bw+X(1.5);
    for(let p=0;p<=8;p++){ const fp=fX1+p*(fX2-fX1)/8; ctx.beginPath(); ctx.moveTo(fp,fY); ctx.lineTo(fp,fY+Y(2)); ctx.stroke(); }
    ctx.beginPath(); ctx.moveTo(fX1,fY+Y(0.9)); ctx.lineTo(fX2,fY+Y(0.9)); ctx.stroke();
  }

  // ── 6E. JUA KALI CAR YARD (5%, 52%) ──────────────────────────────
  {
    const cx=X(5),cy=Y(52),bw=X(10),bh=Y(12),lx=cx-bw/2,ty=cy-bh;
    ctx.fillStyle='rgba(0,0,0,0.14)'; ctx.fillRect(lx+3,ty+3,bw,bh+Y(2));
    ctx.fillStyle='#707070'; ctx.fillRect(lx,ty,bw,bh+Y(2));
    ctx.strokeStyle='rgba(255,255,255,0.09)'; ctx.lineWidth=1;
    for(let i=1;i<3;i++){ ctx.beginPath(); ctx.moveTo(lx,ty+bh*i/3); ctx.lineTo(lx+bw,ty+bh*i/3); ctx.stroke(); }
    // Canopy sign
    ctx.fillStyle='rgba(0,0,0,0.18)'; ctx.fillRect(lx+2,ty-Y(2.6),bw,Y(3));
    ctx.fillStyle='#E6B800'; ctx.fillRect(lx,ty-Y(2.8),bw,Y(2.8));
    for(let s=0;s<6;s++){ ctx.fillStyle='rgba(0,0,0,0.07)'; ctx.fillRect(lx+s*bw/6,ty-Y(2.8),bw/12,Y(2.8)); }
    ctx.fillStyle='#1A1200'; ctx.font=`bold ${Math.max(8,X(1.2))}px system-ui`; ctx.textAlign='center';
    ctx.fillText('JUA KALI',cx,ty-Y(0.9));
    ctx.font=`${Math.max(7,X(0.9))}px system-ui`; ctx.fillText('CAR YARD',cx,ty-Y(0.1));
    // Cars
    function car(cx,cy,cw,ch,col){
      ctx.fillStyle='rgba(0,0,0,0.2)'; ctx.fillRect(cx+3,cy+3,cw,ch*0.65);
      ctx.fillStyle=col; ctx.fillRect(cx,cy,cw,ch*0.65);
      const rX=cx+cw*0.12,rY=cy-ch*0.34,rW=cw*0.76,rH=ch*0.36;
      ctx.fillStyle=col; ctx.fillRect(rX,rY,rW,rH);
      ctx.fillStyle='#AED6F1'; ctx.fillRect(rX+rW*0.05,rY+rH*0.06,rW*0.42,rH*0.7); ctx.fillRect(rX+rW*0.52,rY+rH*0.06,rW*0.42,rH*0.7);
      [cw*0.2,cw*0.8].forEach(wx=>{ ctx.fillStyle='#1C1C1C'; ctx.beginPath(); ctx.arc(cx+wx,cy+ch*0.65,ch*0.22,0,Math.PI*2); ctx.fill(); ctx.fillStyle='#555'; ctx.beginPath(); ctx.arc(cx+wx,cy+ch*0.65,ch*0.1,0,Math.PI*2); ctx.fill(); });
    }
    const cw=bw*0.38,ch=Y(4.5);
    car(lx+X(0.4),ty+Y(0.5),cw,ch,'#E53935');
    car(lx+bw-cw-X(0.4),ty+Y(0.5),cw,ch,'#1565C0');
    car(lx+(bw-cw*1.1)/2,ty+Y(7),cw*1.1,ch*0.9,'#2E7D32');
  }

  // ── 6F. FUN WORLD (80%, 9%) ────────────────────────────────────────
  {
    const cx=X(82),cy=Y(10),R=Math.min(X(6),Y(7.2));
    const fgG=ctx.createRadialGradient(cx,cy+R*0.8,0,cx,cy+R*0.8,R*1.4);
    fgG.addColorStop(0,'#FF8C42'); fgG.addColorStop(1,'#E65100');
    ctx.fillStyle=fgG; ctx.beginPath(); ctx.ellipse(cx,cy+R*0.85,R*1.35,R*0.42,0,0,Math.PI*2); ctx.fill();
    // Ferris wheel rings
    ctx.strokeStyle='#FF6F00'; ctx.lineWidth=R*0.075; ctx.beginPath(); ctx.arc(cx,cy,R,0,Math.PI*2); ctx.stroke();
    ctx.strokeStyle='#FFB300'; ctx.lineWidth=R*0.04; ctx.beginPath(); ctx.arc(cx,cy,R*0.55,0,Math.PI*2); ctx.stroke();
    // Spokes
    ctx.strokeStyle='#FFA000'; ctx.lineWidth=R*0.038;
    for(let i=0;i<8;i++){ const a=i*Math.PI/4; ctx.beginPath(); ctx.moveTo(cx+Math.cos(a)*R*0.1,cy+Math.sin(a)*R*0.1); ctx.lineTo(cx+Math.cos(a)*R,cy+Math.sin(a)*R); ctx.stroke(); }
    // Hub
    ctx.fillStyle='#FF6F00'; ctx.beginPath(); ctx.arc(cx,cy,R*0.11,0,Math.PI*2); ctx.fill();
    ctx.fillStyle='#FFB300'; ctx.beginPath(); ctx.arc(cx,cy,R*0.065,0,Math.PI*2); ctx.fill();
    // Gondolas + rim lights drawn by animation layer (so they rotate)
    // Support legs
    ctx.strokeStyle='#7A3B10'; ctx.lineWidth=R*0.065; ctx.lineCap='round';
    ctx.beginPath(); ctx.moveTo(cx-R*0.38,cy+R); ctx.lineTo(cx,cy); ctx.lineTo(cx+R*0.38,cy+R); ctx.stroke();
    ctx.lineWidth=R*0.032; ctx.beginPath(); ctx.moveTo(cx-R*0.22,cy+R*0.55); ctx.lineTo(cx+R*0.22,cy+R*0.55); ctx.stroke();
    // Ticket booth
    const tx=cx-R*1.65,tbY=cy+R*0.45,tbW=X(4.2),tbH=Y(5.5);
    ctx.fillStyle='rgba(0,0,0,0.15)'; ctx.fillRect(tx+2,tbY+2,tbW,tbH);
    ctx.fillStyle='#FF5722'; ctx.fillRect(tx,tbY,tbW,tbH);
    ctx.fillStyle='#BF360C'; ctx.beginPath(); ctx.moveTo(tx-X(0.5),tbY); ctx.lineTo(tx+tbW/2,tbY-Y(3)); ctx.lineTo(tx+tbW+X(0.5),tbY); ctx.closePath(); ctx.fill();
    ctx.fillStyle='white'; ctx.font=`bold ${Math.max(7,X(0.85))}px system-ui`; ctx.textAlign='center';
    ctx.fillText('TICKETS',tx+tbW/2,tbY+tbH*0.55);
  }

  // ── 6G. COMMUNITY CENTRE (70%, 58%) ──────────────────────────────
  {
    const cx=X(70),cy=Y(58),bw=X(8.5),bh=Y(10),lx=cx-bw/2,ty=cy-bh;
    ctx.fillStyle='#4E6040'; ctx.fillRect(lx-X(1.5),ty-Y(0.6),bw+X(3),bh+Y(4.5));
    shadow(lx,ty,bw,bh); walls(lx,ty,bw,bh,'#7B52C0');
    // Dome
    ctx.fillStyle='#9C27B0';
    ctx.beginPath(); ctx.arc(cx,ty,bw/2+X(0.6),Math.PI,0); ctx.lineTo(lx+bw,ty); ctx.lineTo(lx,ty); ctx.closePath(); ctx.fill();
    ctx.fillStyle='rgba(255,255,255,0.1)'; ctx.beginPath(); ctx.arc(cx-bw*0.1,ty-Y(1.2),bw*0.27,Math.PI*1.15,Math.PI*1.85); ctx.fill();
    // Stained windows (3 arched)
    const wC=['#CE93D8','#B39DDB','#90CAF9'],wSlot=(bw-X(2.4))/3;
    for(let w=0;w<3;w++){
      const wx=lx+X(1.2)+w*wSlot,wy=ty+Y(2),ww=wSlot-X(0.4),wh=Y(2.8);
      ctx.fillStyle=wC[w]; ctx.fillRect(wx,wy,ww,wh);
      ctx.beginPath(); ctx.arc(wx+ww/2,wy,ww/2,Math.PI,0); ctx.fill();
      ctx.fillStyle='rgba(255,255,255,0.25)'; ctx.fillRect(wx,wy,ww*0.38,wh*0.38);
    }
    // Double door
    ctx.fillStyle='#4A148C';
    ctx.fillRect(cx-X(1.4),ty+bh-Y(4.2),X(1.3),Y(4.2)); ctx.fillRect(cx+X(0.1),ty+bh-Y(4.2),X(1.3),Y(4.2));
    ctx.beginPath(); ctx.arc(cx,ty+bh-Y(4.2),X(1.4),Math.PI,0); ctx.fill();
    // Sign
    ctx.fillStyle='#2E1065'; ctx.fillRect(lx+X(0.8),ty+bh*0.65,bw-X(1.6),Y(2.1));
    ctx.fillStyle='#EA80FC'; ctx.font=`bold ${Math.max(7,X(0.9))}px system-ui`; ctx.textAlign='center';
    ctx.fillText('COMMUNITY',cx,ty+bh*0.65+Y(0.95)); ctx.fillText('CENTRE',cx,ty+bh*0.65+Y(1.9));
    // Flagpole
    ctx.strokeStyle='#AAAAAA'; ctx.lineWidth=2;
    ctx.beginPath(); ctx.moveTo(lx+bw+X(0.6),ty); ctx.lineTo(lx+bw+X(0.6),ty-Y(5.5)); ctx.stroke();
    ctx.fillStyle='#E53935'; ctx.fillRect(lx+bw+X(0.6),ty-Y(5.5),X(2.6),Y(1.6));
    ctx.fillStyle='rgba(255,255,255,0.4)'; ctx.fillRect(lx+bw+X(0.6),ty-Y(5.5),X(2.6),Y(0.6));
  }

  // ── 7. TREES ──────────────────────────────────────────────────────
  function tree(tx,ty,r){
    ctx.fillStyle='#5D3A1A'; ctx.fillRect(tx-r*0.15,ty,r*0.3,r*0.85);
    ctx.fillStyle='rgba(0,0,0,0.09)'; ctx.beginPath(); ctx.ellipse(tx+r*0.4,ty+r*0.08,r*0.75,r*0.22,0.1,0,Math.PI*2); ctx.fill();
    ['#388E3C','#43A047','#66BB6A'].forEach((g,t)=>{ ctx.fillStyle=g; ctx.beginPath(); ctx.arc(tx,ty-r*0.28-t*r*0.48,r*(1.06-t*0.19),0,Math.PI*2); ctx.fill(); });
    ctx.fillStyle='rgba(255,255,255,0.07)'; ctx.beginPath(); ctx.arc(tx-r*0.22,ty-r*1.5,r*0.3,0,Math.PI*2); ctx.fill();
  }
  [[13,6,8],[31,4,9],[43,5,8],[71,6,7],[90,6,8],
   [6,33,7],[17,36,8],[87,32,8],[94,39,7],
   [19,54,8],[36,68,9],[63,66,7],[91,61,7],
   [53,4,7],[39,27,8],[25,15,7],
   [36,40,7],[56,30,8],[46,59,7],
   [13,63,7],[41,84,7],[73,85,7],[57,90,7],
   [79,47,7],[63,17,7],[33,20,8],[60,43,7],
  ].forEach(([tx,ty,sz])=>{ tree(X(tx),Y(ty),X(sz)*0.5); });

  // ── 8. QUEST BOARD (42%, 31%) ────────────────────────────────────
  {
    const cx=X(42),cy=Y(31),bw=X(7.5),bh=Y(8.5),lx=cx-bw/2,ty=cy-bh;
    ctx.fillStyle='#5A7A48'; ctx.fillRect(lx-X(1.5),ty-Y(0.8),bw+X(3),bh+Y(4));
    shadow(lx,ty,bw,bh); walls(lx,ty,bw,bh,'#B8860B');
    // Wooden frame / board
    ctx.fillStyle='#8B6914'; ctx.fillRect(lx-X(0.5),ty-Y(0.5),bw+X(1),Y(1));
    ctx.fillRect(lx-X(0.5),ty+bh-Y(0.5),bw+X(1),Y(1));
    ctx.fillRect(lx-X(0.5),ty,X(1),bh); ctx.fillRect(lx+bw-X(0.5),ty,X(1),bh);
    // Board face (cork/parchment)
    ctx.fillStyle='#F5DEB3'; ctx.fillRect(lx+X(0.8),ty+Y(1),bw-X(1.6),bh-Y(2));
    // Pinned scrolls / quest papers
    const qColors=['#FFF8E1','#E8F5E9','#E3F2FD'];
    for(let q=0;q<3;q++){
      const qx=lx+X(1.2)+q*(bw-X(2.4))/3, qy=ty+Y(1.5), qw=(bw-X(2.4))/3-X(0.3), qh=bh-Y(4);
      ctx.fillStyle='rgba(0,0,0,0.08)'; ctx.fillRect(qx+2,qy+2,qw,qh);
      ctx.fillStyle=qColors[q]; ctx.fillRect(qx,qy,qw,qh);
      // Pin
      ctx.fillStyle='#E53935'; ctx.beginPath(); ctx.arc(qx+qw/2,qy+Y(0.2),X(0.25),0,Math.PI*2); ctx.fill();
      // Lines on scroll
      ctx.strokeStyle='rgba(0,0,0,0.2)'; ctx.lineWidth=0.8;
      for(let l=0;l<3;l++){ ctx.beginPath(); ctx.moveTo(qx+X(0.2),qy+Y(1)+l*Y(0.9)); ctx.lineTo(qx+qw-X(0.2),qy+Y(1)+l*Y(0.9)); ctx.stroke(); }
    }
    // Sign
    ctx.fillStyle='#5B3A0A'; ctx.fillRect(lx+X(0.5),ty+bh-Y(1.8),bw-X(1),Y(1.8));
    ctx.fillStyle='#FFD700'; ctx.font=`bold ${Math.max(7,X(1))}px system-ui`; ctx.textAlign='center';
    ctx.fillText('QUEST BOARD',cx,ty+bh-Y(0.5));
    // Roof (banner-style)
    ctx.fillStyle='#8B0000'; ctx.fillRect(lx-X(0.5),ty-Y(0.5),bw+X(1),Y(1.5));
    for(let b=0;b<5;b++){
      ctx.fillStyle=b%2?'#FFD700':'#8B0000';
      ctx.beginPath(); ctx.moveTo(lx+b*(bw/5),ty-Y(0.5)); ctx.lineTo(lx+b*(bw/5)+bw/10,ty+Y(1)); ctx.lineTo(lx+(b+0.5)*(bw/5),ty-Y(0.5)); ctx.closePath(); ctx.fill();
    }
  }

  // ── 9. WALK PATH moved to animation layer ─────────────────────────
}

// ═══════════════════════════════════════════════════════════════
//  PESA CITY ANIMATION LAYER — Phase 13
//  Second canvas (z-index 2) — runs requestAnimationFrame loop.
//  Draws: rotating ferris wheel gondolas, moving vehicles,
//         animated walk path, drifting clouds.
// ═══════════════════════════════════════════════════════════════

let _animCanvas = null;
let _animCtx    = null;
let _animState  = {
  wheel:  0,
  cars:   [0.12, 0.52, 0.75, 0.35],
  carV:   [0.18, 0.66],
  glow:   0,
  clouds: [
    { x: 0.08,  y: 0.06, w: 0.11, s: 0.0035 },
    { x: 0.48,  y: 0.04, w: 0.09, s: 0.0028 },
    { x: 0.82,  y: 0.07, w: 0.10, s: 0.0032 },
  ],
  last: 0,
};

function initAnimCanvas() {
  const map = document.getElementById('pc-map');
  if (!map) return;
  _animCanvas = document.createElement('canvas');
  _animCanvas.id = 'pc-anim-canvas';
  Object.assign(_animCanvas.style, {
    position:'absolute', inset:'0', width:'100%', height:'100%',
    zIndex:'2', pointerEvents:'none', borderRadius:'inherit', display:'block',
  });
  const city = document.getElementById('pc-city-canvas');
  if (city && city.nextSibling) map.insertBefore(_animCanvas, city.nextSibling);
  else map.appendChild(_animCanvas);
  _animCtx = _animCanvas.getContext('2d');

  const resize = () => {
    _animCanvas.width  = map.offsetWidth  || 900;
    _animCanvas.height = map.offsetHeight || 600;
  };
  resize();
  try { new ResizeObserver(resize).observe(map); } catch(_) {}

  function frame(now) {
    const dt = Math.min((now - _animState.last) / 1000, 0.05);
    _animState.last = now;
    _animState.wheel += dt * 0.45;
    _animState.glow   = (Math.sin(now * 0.0014) + 1) * 0.5;
    const spds = [0.052, 0.037, 0.063, 0.042];
    _animState.cars = _animState.cars.map((v, i) => (v + dt * spds[i]) % 1);
    _animState.carV = _animState.carV.map((v, i) => (v + dt * [0.038, 0.055][i]) % 1);
    _animState.clouds.forEach(c => { c.x += dt * c.s; if (c.x > 1.15) c.x = -0.18; });
    if (_animCtx && _animCanvas.width > 0) _drawAnimLayer(_animCtx, _animCanvas.width, _animCanvas.height);
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
}

function _drawAnimLayer(ctx, W, H) {
  const X = p => W * p / 100;
  const Y = p => H * p / 100;
  ctx.clearRect(0, 0, W, H);

  // ── SOFT CLOUDS ────────────────────────────────────────────────
  _animState.clouds.forEach(c => {
    ctx.save();
    ctx.fillStyle = 'rgba(255,255,255,0.13)';
    [0, -c.w*0.3, c.w*0.28].forEach((ox, i) => {
      const scales = [1, 0.65, 0.7];
      ctx.beginPath();
      ctx.ellipse(W*(c.x+ox), H*c.y, W*c.w*scales[i], H*c.w*scales[i]*0.45, 0, 0, Math.PI*2);
      ctx.fill();
    });
    ctx.restore();
  });

  // ── FERRIS WHEEL GONDOLAS (rotate) ─────────────────────────────
  {
    const cx = X(82), cy = Y(10), R = Math.min(X(8), Y(9.5));
    const gC = ['#E53935','#1E88E5','#43A047','#FB8C00','#E91E63','#00ACC1','#7B1FA2','#F9A825'];
    for (let i = 0; i < 8; i++) {
      const a = i * Math.PI / 4 + _animState.wheel;
      const gx = cx + Math.cos(a) * R, gy = cy + Math.sin(a) * R;
      ctx.fillStyle = 'rgba(0,0,0,0.15)'; ctx.fillRect(gx-R*0.1+2, gy-R*0.065+2, R*0.2, R*0.15);
      ctx.fillStyle = gC[i];              ctx.fillRect(gx-R*0.1,   gy-R*0.065,   R*0.2, R*0.15);
      ctx.fillStyle = 'rgba(255,255,255,0.3)'; ctx.fillRect(gx-R*0.08, gy-R*0.055, R*0.08, R*0.055);
    }
    // Twinkling rim lights
    const gl = _animState.glow;
    ctx.fillStyle = `rgba(255,220,0,${0.45 + gl * 0.55})`;
    for (let i = 0; i < 32; i++) {
      const a = i * Math.PI / 16 + _animState.wheel * 0.5;
      ctx.beginPath();
      ctx.arc(cx + Math.cos(a)*R*1.03, cy + Math.sin(a)*R*1.03, R*0.026, 0, Math.PI*2);
      ctx.fill();
    }
  }

  // ── MOVING TOP-DOWN VEHICLES ────────────────────────────────────
  function veh(x, y, w, h, col, flipX) {
    ctx.save();
    if (flipX) { ctx.translate(x + w, y); ctx.scale(-1, 1); x = 0; y = 0; }
    ctx.fillStyle = col; ctx.fillRect(x, y, w, h);
    ctx.fillStyle = 'rgba(0,0,0,0.25)'; ctx.fillRect(x+w*0.15, y+h*0.12, w*0.7, h*0.76);
    ctx.fillStyle = 'rgba(160,225,255,0.55)'; ctx.fillRect(x+w*0.18, y+h*0.15, w*0.64, h*0.3);
    ctx.fillRect(x+w*0.18, y+h*0.56, w*0.64, h*0.27);
    [w*0.2, w*0.8].forEach(wx => {
      ctx.fillStyle = '#111'; ctx.beginPath(); ctx.arc(x+wx, y+h*0.5, h*0.5+w*0.06, 0, Math.PI*2); ctx.fill();
      ctx.fillStyle = '#444'; ctx.beginPath(); ctx.arc(x+wx, y+h*0.5, h*0.22, 0, Math.PI*2); ctx.fill();
    });
    ctx.restore();
  }
  function vehV(x, y, w, h, col) {
    ctx.fillStyle = col; ctx.fillRect(x-w/2, y-h/2, w, h);
    ctx.fillStyle = 'rgba(0,0,0,0.25)'; ctx.fillRect(x-w/2+w*0.12, y-h/2+h*0.12, w*0.76, h*0.76);
    ctx.fillStyle = 'rgba(160,225,255,0.55)'; ctx.fillRect(x-w/2+w*0.14, y-h/2+h*0.15, w*0.72, h*0.28);
    ctx.fillRect(x-w/2+w*0.14, y-h/2+h*0.57, w*0.72, h*0.26);
    [h*0.2, h*0.8].forEach(wy => {
      ctx.fillStyle = '#111'; ctx.beginPath(); ctx.arc(x, y-h/2+wy, w*0.55, 0, Math.PI*2); ctx.fill();
      ctx.fillStyle = '#444'; ctx.beginPath(); ctx.arc(x, y-h/2+wy, w*0.22, 0, Math.PI*2); ctx.fill();
    });
  }

  const cW = X(3.2), cH = Y(1.8);
  veh(X(_animState.cars[0] * 100) - cW/2, Y(47) - cH - Y(0.6), cW, cH, '#FF8C42', false);
  veh(X((1 - _animState.cars[1]) * 100) - cW/2, Y(47) + Y(0.6), cW, cH, '#4CAF50', true);
  veh(X(_animState.cars[2] * 100) - cW*0.85/2, Y(22) - cH*0.85 - Y(0.5), cW*0.85, cH*0.85, '#2196F3', false);
  veh(X((1 - _animState.cars[3]) * 100) - cW*0.85/2, Y(72) + Y(0.5), cW*0.85, cH*0.85, '#FFB300', true);
  vehV(X(48), Y(_animState.carV[0] * 100), X(1.8), Y(3.2), '#E91E63');
  vehV(X(48), Y((_animState.carV[1] + 0.5) * 100 % 100), X(1.8), Y(3.2), '#9C27B0');

  // ── ANIMATED WALK PATH ──────────────────────────────────────────
  if (_walkPath) {
    const {fx,fy,tx:tpx,ty:tpy} = _walkPath;
    const x1=X(fx),y1=Y(fy),x2=X(tpx),y2=Y(tpy);
    const dashOff = (_animState.wheel * 22) % 12;
    ctx.save();
    ctx.strokeStyle='rgba(21,199,126,0.78)'; ctx.lineWidth=2.5; ctx.lineCap='round';
    ctx.setLineDash([5,7]); ctx.lineDashOffset = -dashOff;
    ctx.shadowColor='#15C77E'; ctx.shadowBlur=10;
    ctx.beginPath(); ctx.moveTo(x1,y1); ctx.lineTo(x2,y2); ctx.stroke();
    ctx.setLineDash([]);
    const pulse = 0.55 + _animState.glow * 0.45;
    ctx.fillStyle = `rgba(21,199,126,${0.14 * pulse})`;
    ctx.beginPath(); ctx.arc(x2,y2,X(3.2)*pulse,0,Math.PI*2); ctx.fill();
    ctx.strokeStyle='#15C77E'; ctx.lineWidth=2; ctx.shadowBlur=14;
    ctx.beginPath(); ctx.arc(x2,y2,X(2.2),0,Math.PI*2); ctx.stroke();
    ctx.fillStyle='#15C77E'; ctx.shadowBlur=6;
    ctx.beginPath(); ctx.arc(x2,y2,X(0.72),0,Math.PI*2); ctx.fill();
    ctx.restore();
  }
}

function pesaCity() {
  return {

    // ── Live stats (Alpine-reactive) ───────────────────────────────
    liveBalance:  window.__PESA_BALANCE__ ?? 0,

    // ── Stickman state ─────────────────────────────────────────────
    stickLeft:    PLAZA_POS.left,
    stickTop:     PLAZA_POS.top,
    isWalking:    false,
    headingTo:    '',
    _walkTimer:    null,
    _returnTimer:  null,
    _stepInterval: null,

    // ── Panel state ────────────────────────────────────────────────
    panelOpen:  false,
    loading:    false,
    district:   null,

    // Pull-down-to-dismiss on the district panel — only engages once the
    // panel's own content is already scrolled to the top, so it never
    // hijacks a normal scroll gesture partway down a long list.
    panelDragY:      0,
    panelDragging:   false,
    panelSnapping:   false,
    _panelDragStartY: null,

    // ── Opportunity Hub state ──────────────────────────────────────
    oppTab:      'courses',   // 'courses' | 'jobs'
    oppCourses:  [],
    oppJobs:     [],
    oppLoading:  false,
    oppError:    '',

    // ── Course completion popup ────────────────────────────────────
    coursePopup: { show: false, course: null, jobs: [], xp: 0 },

    // ── Course reader (enroll → read content → complete) ───────────
    courseReader: { show: false, course: null, busy: false, error: '' },

    // ── Mission sidebar state ──────────────────────────────────────
    mission: null,   // {id, title, description, icon, district_slug, sequence}

    // ── UI state ───────────────────────────────────────────────────
    soundOn:     true,
    sidebarOpen: false,

    // ── Quest tip rotation ─────────────────────────────────────────
    tips:      window.__PESA_TIPS__ || [],
    tipIdx:    0,
    _tipTimer: null,

    // ── Badge popup state ──────────────────────────────────────────
    badge: {
      show:    false,
      icon:    '',
      name:    '',
      desc:    '',
      color:   '#15C77E',
      rewards: [],
      next:    '',
    },

    // ── Arrival celebration popup ──────────────────────────────────
    arrival: {
      show:    false,
      icon:    '🏙️',
      name:    '',
      tagline: '',
      color:   '#15C77E',
    },
    _arrivalTimer: null,

    // ── Market Watch detail popup ───────────────────────────────────
    newsDetail: {
      show: false,
      item: null,
    },

    // ── Notification bell state (Phase 15) ────────────────────────
    notifOpen:    false,
    notifLoading: false,
    notifUnread:  0,
    notifications: [],

    // ── Quest panel state (Phase 15) ───────────────────────────────
    questsData: {
      quests:  [],
      loading: false,
      filter:  'all',
    },

    // ── Champions' Court panel state ────────────────────────────────
    champions: {
      joiningId: null,
      msg:       '',
      msgOk:     true,
    },

    // ── Challenge-result celebration popup ──────────────────────────
    challengeResult: {
      show:      false,
      icon:      '🏆',
      title:     '',
      body:      '',
      isWinner:  false,
    },
    _challengeResultQueue: [],

    // ── Quest complete celebration overlay ─────────────────────────
    questComplete: {
      show:   false,
      icon:   '🏆',
      title:  '',
      lesson: '',
      xp:     0,
      kes:    0,
    },

    // ── Step-complete flash (multi-trigger quests) ─────────────────
    stepFlash: '',
    _stepFlashTimer: null,

    // ── Quest detail popup ─────────────────────────────────────────
    questPopup: {
      show:          false,
      icon:          '📜',
      image:         null,
      title:         '',
      description:   '',
      instructions:  '',
      lesson:        '',
      xp_reward:     0,
      kes_reward:    0,
      trigger_label: '',
      trigger_type:  '',
      trigger_value: '',
    },

    // ── Event cycle tracker (Phase 8+) — index into __PESA_EVENTS__ ──
    _eventIdx: 0,

    // ── World Event state (Phase 8 — EventEngine) ─────────────────
    // Populated by showWorldEvent({ type, icon, title, description,
    //   category_label, choices, impact_chips, dismissable, expires_in_days })
    worldEvent: {
      show:            false,
      resolved:        false,
      type:            'opportunity',   // opportunity | cost | career | asset
      category_label:  '',
      icon:            '',
      title:           '',
      description:     '',
      impact_chips:    [],
      choices:         [],
      dismissable:     true,
      expires_in_days: null,           // null = no expiry; number = game-day countdown
      result_icon:     '',
      result_title:    '',
      result_delta:    0,
    },

    // ── Init ────────────────────────────────────────────────────────
    init() {
      // Restore stickman position
      const saved = sessionStorage.getItem('pc_pos');
      if (saved) {
        try {
          const pos    = JSON.parse(saved);
          this.stickLeft = pos.left ?? PLAZA_POS.left;
          this.stickTop  = pos.top  ?? PLAZA_POS.top;
        } catch (_) {}
      }
      this._applyStickPos(true);

      // Deep-link support — e.g. /world?open=quests from the mobile bottom
      // nav's Quests tab, which used to point at a stale standalone page
      // with its own (empty-looking) quest list instead of the real,
      // level-gated, auto-completing quests shown here.
      const openParam = new URLSearchParams(window.location.search).get('open');
      if (openParam) {
        setTimeout(() => this.walkToDistrict(openParam), 300);
      }

      // Load active mission from server (graceful if table not yet migrated)
      this._fetchMission();

      // Load notification count for bell badge
      this._fetchNotifications();

      // ── Scroll hint auto-hide (portrait mobile only) ─────────────
      const hint = document.getElementById('pc-scroll-hint');
      if (hint) {
        const hideHint = () => {
          hint.classList.add('hidden');
          hint.removeEventListener('transitionend', () => hint.remove());
          // Remove from DOM after fade
          setTimeout(() => { if (hint.parentNode) hint.parentNode.removeChild(hint); }, 600);
        };
        // Auto-hide after 4.5s
        const hintTimer = setTimeout(hideHint, 4500);
        // Also hide on first map scroll or click
        const mapEl = document.getElementById('pc-map');
        const mapParent = mapEl ? mapEl.parentNode : null;
        if (mapParent) {
          const onInteract = () => {
            clearTimeout(hintTimer);
            hideHint();
            mapParent.removeEventListener('scroll', onInteract);
            mapParent.removeEventListener('pointerdown', onInteract);
          };
          mapParent.addEventListener('scroll', onInteract, { passive: true });
          mapParent.addEventListener('pointerdown', onInteract, { passive: true });
        }
      }

      // ── Auto-show first world event (3s delay — let player settle in) ──
      const events = window.__PESA_EVENTS__ ?? [];
      if (events.length > 0) {
        setTimeout(() => {
          // Don't interrupt an ongoing walk or open panel
          if (!this.isWalking && !this.panelOpen && !this.badge.show) {
            this.showWorldEvent(events[this._eventIdx]);
            this._eventIdx++;
          }
        }, 3000);
      }

      // Show any pending quest completions from other pages (savings, marketplace)
      const pendingQC = window.__PESA_QUEST_COMPLETIONS__ ?? [];
      if (pendingQC.length > 0) {
        setTimeout(() => {
          this.showQuestComplete(pendingQC[0]);
        }, 2000);
      }

      // Show step-fired flashes (multi-trigger quests, triggered from other pages)
      const pendingSF = window.__PESA_STEP_FIRES__ ?? [];
      if (pendingSF.length > 0) {
        setTimeout(() => {
          const sf = pendingSF[0];
          this.showStepFlash(sf.step_label || 'Step complete!');
        }, 2500);
      }

      // Poll for live quest completions (fired from other panels/pages while world is open)
      this._pollQuestCompletions = () => {
        if (document.hidden) return;
        fetch('/world/quests/pending-completions', {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
          credentials: 'same-origin',
        })
          .then(r => r.ok ? r.json() : null)
          .then(data => {
            if (!data) return;
            if (data.completions && data.completions.length > 0) {
              this.showQuestComplete(data.completions[0]);
            } else if (data.step_fires && data.step_fires.length > 0) {
              const sf = data.step_fires[0];
              this.showStepFlash(sf.step_label || 'Step complete!');
            }
          })
          .catch(() => {});
      };
      this._questPollTimer = setInterval(this._pollQuestCompletions, 5000);

      // Poll for challenge results (win/loss/cancelled) — DB-backed via
      // GameNotification rather than a session queue, since a challenge can
      // settle while this player is offline (nightly sweep, or another
      // participant's page load winning a duel first).
      this._pollChallengeResults = () => {
        if (document.hidden) return;
        fetch('/world/challenges/pending-results', {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
          credentials: 'same-origin',
        })
          .then(r => r.ok ? r.json() : null)
          .then(items => {
            if (!items || !items.length) return;
            this._challengeResultQueue.push(...items);
            if (!this.challengeResult.show) this.nextChallengeResult();
          })
          .catch(() => {});
      };
      this._challengeResultPollTimer = setInterval(this._pollChallengeResults, 5000);
      this._pollChallengeResults();

      // Draw procedural canvas city + start animation layer
      initCityCanvas();
      initAnimCanvas();

      // Start world BGM + preload all SFX buffers on first user interaction
      document.addEventListener('click', () => { SoundMgr.preload(); SoundMgr.playWorld(); }, { once: true });

      // Pause all audio when app goes to background / user leaves; resume on return
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          SoundMgr.stopZone(); SoundMgr.stopWorld();
          if (this._wakeLock) { this._wakeLock.release().catch(() => {}); this._wakeLock = null; }
        } else {
          SoundMgr.playWorld();
          this._acquireWakeLock();
        }
      });
      window.addEventListener('pagehide', () => { SoundMgr.stopZone(); SoundMgr.stopWorld(); clearInterval(this._questPollTimer); });

      // Wake Lock: prevent screen sleeping during gameplay
      this._wakeLock = null;
      this._acquireWakeLock = async () => {
        if (!('wakeLock' in navigator) || this._wakeLock) return;
        try { this._wakeLock = await navigator.wakeLock.request('screen'); } catch (_) {}
      };
      this._acquireWakeLock();

      // Listen for quest popup event (from Quest Tracker card)
      window.addEventListener('open-quest-popup', (e) => {
        this.openQuestPopup(e.detail);
      });

      // Listen for Escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          if (this.questPopup.show)  { this.questPopup.show = false; return; }
          if (this.worldEvent.show && !this.worldEvent.resolved) { this.dismissWorldEvent(); return; }
          if (this.badge.show)  { this.badge.show = false; return; }
          if (this.panelOpen)   { this.closePanel(); }
        }
      });
    },

    // ══════════════════════════════════════════════════════════════
    //  PLAYER PIN MOVEMENT  (Phase 16 — replaces stickman walking)
    //  Pin teleports with CSS transition — no road-following delay.
    // ══════════════════════════════════════════════════════════════

    visitDistrict(slug, el) {
      const pos = DISTRICT_POSITIONS[slug];
      if (!pos) return;

      clearTimeout(this._walkTimer);
      clearTimeout(this._returnTimer);
      clearTimeout(this._arrivalTimer);
      this.arrival.show = false;

      const name = el
        ? (el.querySelector('.pc-district-name')?.textContent?.trim() || slug)
        : slug;

      this.headingTo = name;
      SoundMgr.stopWorld();

      // Move the player pin to the district (CSS transition handles the animation)
      const pin = document.getElementById('pc-player-pin');
      if (pin) {
        pin.style.left = pos.left + '%';
        pin.style.top  = pos.top  + '%';
      }
      this.stickLeft = pos.left;
      this.stickTop  = pos.top;
      sessionStorage.setItem('pc_pos', JSON.stringify({ left: pos.left, top: pos.top }));

      // After CSS transition (~2s stroll) show arrival + open panel
      this._walkTimer = setTimeout(() => {
        this.headingTo = '';
        SoundMgr.play('arrive');
        SoundMgr.playZone(slug);
        this._showArrival(slug);

        const pendingEvents = window.__PESA_EVENTS__ ?? [];
        if (pendingEvents.length > this._eventIdx && Math.random() < 0.30) {
          const evt = pendingEvents[this._eventIdx++];
          setTimeout(() => {
            if (!this.badge.show && !this.worldEvent.show) {
              this.showWorldEvent({ ...evt, category_label: `📍 ${name} Encounter` });
            }
          }, 1400);
        }

        this._openPanel(slug);
      }, 2060);
    },

    _getDistrictInfo(slug) {
      return (window.__PESA_DISTRICTS__ || {})[slug] || null;
    },

    _showArrival(slug) {
      const d = this._getDistrictInfo(slug);
      this.arrival = {
        show:    true,
        icon:    d?.icon    ?? 'city',
        name:    d?.name    ?? slug,
        tagline: d?.tagline ?? '',
        color:   d?.color   ?? '#15C77E',
      };
      clearTimeout(this._arrivalTimer);
      this._arrivalTimer = setTimeout(() => { this.arrival.show = false; }, 3000);
    },

    // Called from sidebar "Go there" button
    walkToDistrict(slug) {
      const el = document.querySelector(`[data-slug="${slug}"]`);
      this.visitDistrict(slug, el);
    },

    _returnToPlaza() {
      const pin = document.getElementById('pc-player-pin');
      if (pin) {
        pin.style.left = PLAZA_POS.left + '%';
        pin.style.top  = PLAZA_POS.top  + '%';
      }
      this.stickLeft = PLAZA_POS.left;
      this.stickTop  = PLAZA_POS.top;
      sessionStorage.setItem('pc_pos', JSON.stringify(PLAZA_POS));
    },

    _applyStickPos(instant = false) {
      const pin = document.getElementById('pc-player-pin');
      if (!pin) return;
      if (instant) pin.style.transition = 'none';
      pin.style.left = this.stickLeft + '%';
      pin.style.top  = this.stickTop  + '%';
      if (instant) {
        requestAnimationFrame(() => requestAnimationFrame(() => { pin.style.transition = ''; }));
      }
    },

    // ══════════════════════════════════════════════════════════════
    //  DISTRICT PANEL
    // ══════════════════════════════════════════════════════════════

    _openPanel(slug) {
      SoundMgr.play('open');
      this.panelOpen  = true;
      this.loading    = true;
      this.district   = null;
      this.oppCourses = [];
      this.oppJobs    = [];
      this.oppError   = '';
      this.oppTab     = 'courses';
      this.questsData.quests  = [];
      this.questsData.filter  = 'all';
      this.questsData.loading = (slug === 'quests');

      this.$nextTick(() => {
        const panel = document.querySelector('.pc-panel');
        if (panel) panel.scrollTop = 0;
      });

      fetch(`/world/district/${slug}`, {
        headers: HEADERS(),
        credentials: 'same-origin',
      })
        .then(r => {
          if (!r.ok) throw new Error(`HTTP ${r.status}`);
          return r.json();
        })
        .then(data => {
          this.district = data;
          this.loading  = false;
          if (slug === 'opportunity-hub') this._loadOpportunityHub();
          if (slug === 'quests') this._loadQuests();
        })
        .catch(() => {
          this.district = { slug, icon: '⚠️', name: 'Could Not Load', status: 'error', description: 'Could not load this district right now. Check your connection and try again.' };
          this.loading  = false;
          this.questsData.loading = false;
        });
    },

    panelTouchStart(e) {
      const panelEl = document.getElementById('pc-panel');
      // Only arm the drag-to-close once the panel's own content is already
      // scrolled to the top — otherwise this would hijack a normal scroll
      // partway down a long list.
      if (!panelEl || panelEl.scrollTop > 0) { this._panelDragStartY = null; return; }
      this._panelDragStartY = e.touches[0].clientY;
      this.panelDragging = false;
      this.panelDragY = 0;
    },

    panelTouchMove(e) {
      if (this._panelDragStartY === null) return;
      const panelEl = document.getElementById('pc-panel');
      if (panelEl && panelEl.scrollTop > 0) { this._panelDragStartY = null; this.panelDragY = 0; return; }
      const delta = e.touches[0].clientY - this._panelDragStartY;
      if (delta <= 0) { this.panelDragY = 0; return; } // only a downward pull counts
      this.panelDragging = true;
      this.panelDragY = delta;
      e.preventDefault(); // stop the page/panel's own scroll bounce while we're dragging the sheet
    },

    panelTouchEnd() {
      const shouldClose = this.panelDragY > 90;
      this.panelDragging    = false;
      this._panelDragStartY = null;
      if (shouldClose) {
        this.panelDragY = 0;
        this.closePanel();
        return;
      }
      // Below the threshold — snap back smoothly instead of just vanishing.
      this.panelSnapping = true;
      this.panelDragY    = 0;
      setTimeout(() => { this.panelSnapping = false; }, 220);
    },

    closePanel() {
      SoundMgr.play('close');
      SoundMgr.stopZone();
      SoundMgr.playWorld();
      this.panelOpen = false;
      this.panelDragging = false;
      this.panelDragY    = 0;
      // The piece stays where you left it — no auto-walk back to the plaza.
      // (Walk home anytime via the 🏠 Home marker on the map.)
      clearTimeout(this._returnTimer);
    },

    // ══════════════════════════════════════════════════════════════
    //  QUEST PANEL  (Phase 15 — in-map quests, auto-completion)
    // ══════════════════════════════════════════════════════════════

    // ══════════════════════════════════════════════════════════════
    //  NOTIFICATION BELL  (Phase 15)
    // ══════════════════════════════════════════════════════════════

    async _fetchNotifications() {
      try {
        const r    = await fetch('/game/notifications', { headers: HEADERS(), credentials: 'same-origin' });
        const data = await r.json();
        this.notifications = data;
        this.notifUnread   = data.filter(n => !n.is_read).length;
      } catch (_) {}
    },

    async toggleNotifications() {
      const wasOpen  = this.notifOpen;
      this.notifOpen = !wasOpen;
      if (this.notifOpen) {
        this.notifLoading = true;
        await this._fetchNotifications();
        this.notifLoading = false;
        // Mark all read the moment the panel actually opens — matches the
        // Dashboard's bell behaviour, so it's consistent across both pages.
        if (this.notifUnread > 0) this.markAllRead();
      }
    },

    async markAllRead() {
      try {
        await fetch('/game/notifications/read', { method: 'POST', headers: HEADERS(), credentials: 'same-origin' });
        this.notifications.forEach(n => { n.is_read = true; });
        this.notifUnread = 0;
      } catch (_) {}
    },

    filteredQuests() {
      const q = this.questsData.quests;
      const f = this.questsData.filter;
      if (f === 'available') return q.filter(x => !x.is_locked && x.user_status !== 'completed');
      if (f === 'completed') return q.filter(x => x.user_status === 'completed');
      if (f === 'pending_old') return q.filter(x => x.is_previous_level && x.user_status !== 'completed');
      return q;
    },

    async _loadQuests() {
      this.questsData.loading = true;
      try {
        const r    = await fetch('/world/quests', { headers: HEADERS(), credentials: 'same-origin' });
        const data = await r.json();
        this.questsData.quests  = data;
      } catch (_) {
        this.questsData.quests = [];
      }
      this.questsData.loading = false;
    },

    async startQuestInPanel(quest) {
      quest.user_status = 'in_progress';
      // Show the "how to complete" brief immediately (server enriches it below)
      this.openQuestPopup(quest);
      try {
        const r    = await fetch(`/world/quests/${quest.id}/start`, {
          method: 'POST', headers: HEADERS(), credentials: 'same-origin',
        });
        const data = await r.json();
        if (data && data.started) {
          // Server returns the canonical brief (instructions with [TRIGGER:] stripped)
          this.openQuestPopup({ ...quest, ...data, status: 'in_progress' });
        } else if (data && data.error) {
          quest.user_status = 'available';
          this.questPopup.show = false;
        }
      } catch (_) {}
    },

    async completeQuestInPanel(quest) {
      if (quest._completing) return;
      quest._completing = true;
      try {
        const r    = await fetch(`/world/quests/${quest.id}/complete`, {
          method: 'POST', headers: HEADERS(), credentials: 'same-origin',
        });
        const data = await r.json();
        if (data.completed) {
          quest.user_status = 'completed';
          SoundMgr.play('quest');
          // The KES reward is credited server-side already — reflect it in the
          // live HUD balance immediately instead of only after a page reload.
          if (data.kes_earned) {
            this.liveBalance += data.kes_earned;
          }
          this.showQuestComplete({
            icon:   quest.icon,
            title:  quest.title,
            lesson: data.lesson || quest.description,
            xp:     data.xp_earned || 0,
            kes:    data.kes_earned || 0,
          });
        } else if (data.step_fired !== undefined && data.step_fired !== null) {
          // Multi-trigger: a step fired but quest isn't complete yet
          this.showStepFlash(data.step_label || 'Step complete!');
          // Update step_progress on quest object if available
          if (data.step_progress !== undefined) {
            quest.step_progress = data.step_progress;
          }
        }
      } catch (_) {}
      quest._completing = false;
    },

    showQuestComplete(data) {
      this.questComplete = {
        show:   true,
        icon:   data.icon       ?? 'trophy',
        title:  data.title      ?? 'Quest Complete!',
        lesson: data.lesson     ?? '',
        xp:     data.xp_earned  ?? data.xp ?? 0,
        kes:    data.kes_earned  ?? data.kes ?? 0,
      };
    },

    // ── Challenge-result celebration popup — one at a time off a queue ──
    nextChallengeResult() {
      const item = this._challengeResultQueue.shift();
      if (!item) { this.challengeResult.show = false; return; }
      this.challengeResult = {
        show:     true,
        icon:     item.icon  ?? (item.is_winner ? '🏆' : '🎗️'),
        title:    item.title ?? '',
        body:     item.body  ?? '',
        isWinner: !!item.is_winner,
      };
      if (item.is_winner) SoundMgr.play('badge');
    },
    closeChallengeResult() {
      this.challengeResult.show = false;
      if (this._challengeResultQueue.length) {
        setTimeout(() => this.nextChallengeResult(), 300);
      }
    },

    // ── Multi-trigger: show a small step-complete flash ────────────
    showStepFlash(msg) {
      this.stepFlash = msg;
      clearTimeout(this._stepFlashTimer);
      this._stepFlashTimer = setTimeout(() => { this.stepFlash = ''; }, 3000);
    },

    openQuestPopup(q) {
      if (!q) return;
      this.questPopup = {
        show:          true,
        icon:          q.icon          ?? 'checklist',
        image:         q.image         ?? null,
        title:         q.title         ?? '',
        description:   q.description   ?? '',
        instructions:  q.instructions  ?? q.hint ?? '',
        lesson:        q.lesson        ?? '',
        xp_reward:     q.xp_reward     ?? 0,
        kes_reward:    q.kes_reward     ?? 0,
        trigger_label: q.trigger_label ?? '',
        trigger_type:  q.trigger_type  ?? '',
        trigger_value: q.trigger_value ?? '',
        status:        q.status        ?? '',
      };
    },

    // ══════════════════════════════════════════════════════════════
    //  OPPORTUNITY HUB  (Phase 5 + 6)
    // ══════════════════════════════════════════════════════════════

    _loadOpportunityHub() {
      this.oppLoading = true;
      Promise.all([
        fetch('/opportunities/courses', { headers: HEADERS(), credentials: 'same-origin' }).then(r => r.json()),
        fetch('/opportunities/jobs',    { headers: HEADERS(), credentials: 'same-origin' }).then(r => r.json()),
      ])
        .then(([courses, jobs]) => {
          this.oppCourses = courses;
          this.oppJobs    = jobs;
          this.oppLoading = false;
        })
        .catch(() => {
          this.oppError   = 'Could not load opportunities. Please try again.';
          this.oppLoading = false;
        });
    },

    // Open the course reader — the player must actually go through the content
    // before the course can be completed. Nothing completes on a single click.
    openCourseReader(courseId) {
      const course = this.oppCourses.find(c => c.id === courseId);
      if (!course || course.player_status === 'completed') return;
      this.courseReader = { show: true, course, busy: false, error: '' };
      SoundMgr.play('open');
    },

    closeCourseReader() {
      this.courseReader = { show: false, course: null, busy: false, error: '' };
    },

    // Step 1 — enroll (pays the fee for paid courses). Course stays 'enrolled'
    // and the reader switches to the full content for the player to study.
    async enrollCourse(courseId) {
      const course = this.oppCourses.find(c => c.id === courseId);
      if (!course || course.player_status !== 'not_enrolled') return;

      this.courseReader.busy = true;
      this.courseReader.error = '';
      try {
        const r = await fetch(`/opportunities/courses/${courseId}/enroll`, {
          method: 'POST', headers: HEADERS(), credentials: 'same-origin',
        });
        if (!r.ok) {
          const err = await r.json().catch(() => ({}));
          this.courseReader.error = err.error ?? 'Could not enroll. Please try again.';
          return;
        }
        course.player_status = 'enrolled';
        SoundMgr.play('arrive');
      } catch (_) {
        this.courseReader.error = 'Could not enroll. Please try again.';
      } finally {
        this.courseReader.busy = false;
      }
    },

    // Step 2 — complete, only reachable from the bottom of the course content.
    // Awards XP and shows the celebration popup with unlocked jobs.
    async completeCourse(courseId) {
      const course = this.oppCourses.find(c => c.id === courseId);
      if (!course || course.player_status !== 'enrolled') return;

      this.courseReader.busy = true;
      this.courseReader.error = '';
      try {
        const r = await fetch(`/opportunities/courses/${courseId}/complete`, {
          method: 'POST', headers: HEADERS(), credentials: 'same-origin',
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok) {
          this.courseReader.error = data.error ?? 'Could not complete the course. Please try again.';
          return;
        }
        course.player_status = 'completed';
        this.closeCourseReader();
        // Refresh jobs so has_requirement updates immediately
        fetch('/opportunities/jobs', { headers: HEADERS(), credentials: 'same-origin' })
          .then(jr => jr.json()).then(jobs => { this.oppJobs = jobs; }).catch(() => {});
        // Celebration popup with XP + jobs unlocked
        this.coursePopup = {
          show:   true,
          course: data.course ?? course,
          jobs:   data.jobs_unlocked ?? [],
          xp:     data.xp_awarded ?? 0,
        };
        SoundMgr.play('badge');
        if (data.mission_result?.completed) {
          this._handleMissionComplete(data.mission_result);
        }
      } catch (_) {
        this.courseReader.error = 'Could not complete the course. Please try again.';
      } finally {
        this.courseReader.busy = false;
      }
    },

    closeCoursePopup() {
      this.coursePopup = { show: false, course: null, jobs: [], xp: 0 };
    },

    // Join an open Champions' Court challenge straight from the world-map
    // popup — mirrors enrollCourse()'s fetch-and-patch-in-place shape.
    async joinChampionsChallenge(challengeId) {
      this.champions.joiningId = challengeId;
      this.champions.msg = '';
      try {
        const r = await fetch(`/challenges/${challengeId}/join`, {
          method: 'POST', headers: HEADERS(), credentials: 'same-origin',
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok || !data.ok) {
          this.champions.msg = data.error ?? 'Could not join. Please try again.';
          this.champions.msgOk = false;
          return;
        }
        if (this.district?.open_challenges_list) {
          this.district.open_challenges_list = this.district.open_challenges_list.filter(c => c.id !== challengeId);
        }
        this.district.my_active_challenges = (this.district.my_active_challenges ?? 0) + 1;
        this.champions.msg = "You're in — good luck!";
        this.champions.msgOk = true;
        SoundMgr.play('arrive');
      } catch (_) {
        this.champions.msg = 'Could not join. Please try again.';
        this.champions.msgOk = false;
      } finally {
        this.champions.joiningId = null;
      }
    },

    async applyJob(jobId) {
      const job = this.oppJobs.find(j => j.id === jobId);
      if (!job || !job.has_requirement || job.is_employed) return;

      job.is_employed = 'applying';
      try {
        const r    = await fetch(`/opportunities/jobs/${jobId}/apply`, {
          method: 'POST', headers: HEADERS(), credentials: 'same-origin',
        });
        const data = await r.json();

        if (!r.ok) {
          job.is_employed = false;
          this.oppError   = data.error ?? 'Could not apply.';
          return;
        }

        job.is_employed = true;
        // Show XP celebration if earned
        if (data.xp_awarded > 0) {
          this.coursePopup = {
            show: true,
            course: { title: job.title, icon: job.employer_logo || '🏢', career_track: job.career_track,
                      financial_tip: 'Your new salary of KES ' + (job.salary_kes_month||0).toLocaleString() + '/month starts now. Save at least 20%.' },
            jobs: [],
            xp: data.xp_awarded,
          };
        }
        if (data.mission_result?.completed) {
          this._handleMissionComplete(data.mission_result);
        }
      } catch (_) {
        job.is_employed = false;
      }
    },

    // ══════════════════════════════════════════════════════════════
    //  MISSION ENGINE  (Phase 4-7)
    // ══════════════════════════════════════════════════════════════

    async _fetchMission() {
      try {
        const r = await fetch('/missions/active', { headers: HEADERS(), credentials: 'same-origin' });
        if (r.ok) {
          this.mission = await r.json();
          this._loadTips();
        }
      } catch (_) {} // graceful if not yet migrated
    },

    _loadTips() {
      const slug = this.mission?.slug || '';
      this.tips  = QUEST_TIPS[slug] || window.__PESA_TIPS__ || [];
      this.tipIdx = 0;
      clearInterval(this._tipTimer);
      if (this.tips.length > 1) {
        this._tipTimer = setInterval(() => {
          this.tipIdx = (this.tipIdx + 1) % this.tips.length;
        }, 7000); // rotate every 7 seconds
      }
    },

    nextTip() {
      if (!this.tips.length) return;
      this.tipIdx = (this.tipIdx + 1) % this.tips.length;
      // Reset the auto-rotate timer so manual taps don't clash
      clearInterval(this._tipTimer);
      if (this.tips.length > 1) {
        this._tipTimer = setInterval(() => {
          this.tipIdx = (this.tipIdx + 1) % this.tips.length;
        }, 7000);
      }
    },

    urgencyColor(u) {
      return u === 'critical' ? '#EF5350' : u === 'warning' ? '#FFBC00' : '#15C77E';
    },

    async checkMission(missionId) {
      try {
        const r    = await fetch(`/missions/${missionId}/check`, {
          method: 'POST', headers: HEADERS(), credentials: 'same-origin',
        });
        const data = await r.json();
        if (data.completed) {
          this._handleMissionComplete(data);
        }
      } catch (_) {}
    },

    _handleMissionComplete(data) {
      // Show badge popup
      if (data.badge) {
        SoundMgr.play('badge');
        this.badge = {
          show:    true,
          icon:    data.badge.icon    ?? 'medal',
          name:    data.badge.name    ?? 'Badge Unlocked',
          desc:    data.mission_title ?? '',
          color:   data.badge.color   ?? '#15C77E',
          rewards: this._buildRewardChips(data.rewards),
          next:    data.next_mission  ?? '',
          chain:   data.chain_complete ?? false,
        };
      }
      // Refresh mission card in sidebar
      this._fetchMission();
    },

    _buildRewardChips(rewards) {
      if (!rewards) return [];
      const chips = [];
      if (rewards.xp)  chips.push({ label: '+' + rewards.xp + ' XP',        color: '#4DA8F7' });
      if (rewards.kes) chips.push({ label: '+KES ' + rewards.kes.toLocaleString(), color: '#15C77E' });
      return chips;
    },

    dismissBadge() {
      this.badge.show = false;
    },

    // ══════════════════════════════════════════════════════════════
    //  MARKET WATCH DETAIL POPUP
    // ══════════════════════════════════════════════════════════════

    openNewsDetail(item) {
      this.newsDetail.item = item;
      this.newsDetail.show = true;
    },

    closeNewsDetail() {
      this.newsDetail.show = false;
    },

    // ══════════════════════════════════════════════════════════════
    //  WORLD EVENT SYSTEM  (Phase 8 — EventEngine integration)
    // ══════════════════════════════════════════════════════════════

    showWorldEvent(event) {
      // event shape: { type, icon, title, description, category_label,
      //               choices, impact_chips, dismissable }
      Object.assign(this.worldEvent, {
        show:            true,
        resolved:        false,
        type:            event.type            ?? 'opportunity',
        category_label:  event.category_label  ?? '🌍 City Event',
        icon:            event.icon            ?? '⚡',
        title:           event.title           ?? 'Something happened',
        description:     event.description     ?? '',
        impact_chips:    event.impact_chips    ?? [],
        choices:         event.choices         ?? [],
        dismissable:     event.dismissable     !== false,
        expires_in_days: event.expires_in_days ?? null,
        result_icon:     '',
        result_title:    '',
        result_delta:    0,
      });
    },

    async resolveWorldEvent(choiceId) {
      const choice = this.worldEvent.choices.find(c => c.id === choiceId);
      if (!choice) return;

      try {
        const res = await fetch('/world/events/resolve', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' },
          body:    JSON.stringify({ event_id: this.worldEvent.event_id, choice_id: choiceId }),
        });
        const data = await res.json();

        this.worldEvent.resolved    = true;
        this.worldEvent.result_icon  = data.result_icon  ?? (data.delta >= 0 ? '💵' : '😬');
        this.worldEvent.result_title = data.result_title ?? choice.label;
        this.worldEvent.result_delta = data.delta        ?? choice.delta ?? 0;
        this.liveBalance = Math.max(0, this.liveBalance + this.worldEvent.result_delta);
      } catch (_) {
        // Graceful offline: apply the choice's known delta without server confirmation
        this.worldEvent.resolved    = true;
        this.worldEvent.result_icon  = choice.delta >= 0 ? '💵' : '😬';
        this.worldEvent.result_title = choice.label;
        this.worldEvent.result_delta = choice.delta ?? 0;
        this.liveBalance = Math.max(0, this.liveBalance + this.worldEvent.result_delta);
      }
    },

    dismissWorldEvent() {
      this.worldEvent.show     = false;
      this.worldEvent.resolved = false;
    },

    toggleSound() {
      this.soundOn = SoundMgr.toggle();
    },

    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen;
    },

    // ══════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════

    fmtKES(n) {
      if (!n && n !== 0) return '—';
      n = Number(n);
      if (n >= 1_000_000) return 'KES ' + (n / 1_000_000).toFixed(1) + 'M';
      if (n >= 1_000)     return 'KES ' + Math.round(n).toLocaleString();
      return 'KES ' + n;
    },

    creditLabel(score) {
      score = Number(score);
      if (score >= 800) return { label: 'Excellent', color: '#15C77E' };
      if (score >= 670) return { label: 'Good',      color: '#4DA8F7' };
      if (score >= 580) return { label: 'Fair',      color: '#FFBC00' };
      if (score >= 500) return { label: 'Poor',      color: '#FF6B35' };
      return                   { label: 'Very Poor', color: '#EF5350' };
    },

    trackLabel(track) {
      return { tech: '⚙️ Tech', business: '📊 Business', finance: '💹 Finance', creative: '🎨 Creative' }[track] ?? track;
    },
  };
}


// ══════════════════════════════════════════════════════════════
//  EQUITY SQUARE — Deals + Loans Alpine component
// ══════════════════════════════════════════════════════════════
function equitySquare() {
  return {
    bankTab:      'savings',
    eqTab:        'deals',
    dealLoading:  false,
    loanLoading:  false,
    shareLoading: false,
    loanAmounts:  {},
    shareQty:     {},
    bankMsg:      '',
    bankMsgOk:    true,
    shareTradeResult: null,

    init() {
      // Set by the Market Watch "Go to Market" button before walking here —
      // a plain data property can't be reached from outside this nested
      // x-data, so sessionStorage is the hand-off.
      const intent = sessionStorage.getItem('pc_eq_tab_intent');
      if (intent) {
        this.eqTab = intent;
        sessionStorage.removeItem('pc_eq_tab_intent');
      }
    },

    showMsg(msg, ok = true) {
      this.bankMsg   = msg;
      this.bankMsgOk = ok;
      setTimeout(() => { this.bankMsg = ''; }, 4000);
    },

    portfolioTotals(holdings) {
      holdings = holdings ?? [];
      return {
        value:     holdings.reduce((sum, h) => sum + h.value, 0),
        gain_loss: holdings.reduce((sum, h) => sum + h.gain_loss, 0),
      };
    },

    // 4 chunky OHLC candles from the raw price history, so a glance shows the
    // shape of the trend instead of 15 thin, hard-to-read slivers.
    candles(history, buckets = 4) {
      if (!history || history.length === 0) return [];
      const n         = Math.min(buckets, history.length);
      const chunkSize = Math.ceil(history.length / n);
      const chunks    = [];
      for (let i = 0; i < history.length; i += chunkSize) chunks.push(history.slice(i, i + chunkSize));

      const ohlc = chunks.map(c => ({
        open: c[0], close: c[c.length - 1],
        high: Math.max(...c), low: Math.min(...c),
      }));
      const globalMax = Math.max(...ohlc.map(c => c.high));
      const globalMin = Math.min(...ohlc.map(c => c.low));
      const range     = (globalMax - globalMin) || 1;

      return ohlc.map(c => ({
        color:      c.close >= c.open ? '#34d399' : '#f87171',
        wickTop:    ((globalMax - c.high) / range) * 100,
        wickHeight: Math.max(3, ((c.high - c.low) / range) * 100),
        bodyTop:    ((globalMax - Math.max(c.open, c.close)) / range) * 100,
        bodyHeight: Math.max(10, (Math.abs(c.open - c.close) / range) * 100),
      }));
    },

    async enterDeal(deal, district) {
      if (this.dealLoading) return;
      const cs = document.querySelector('meta[name=csrf-token]')?.content ?? '';
      this.dealLoading = true;
      try {
        const res  = await fetch('/deals/invest', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cs, 'Accept': 'application/json' },
          body: JSON.stringify({ deal_id: deal.id }),
        });
        const data = await res.json();
        if (data.error) {
          this.showMsg(data.error, false);
        } else {
          // Same celebration treatment as a share trade — an animated card up
          // top, not a plain toast that a page reload used to cut short.
          this.celebrateTrade(true, deal.icon, data.message ?? 'Deal entered!', data.education, data.basics);
          if (data.my_deal) district.my_deals = [...(district.my_deals ?? []), data.my_deal];
          district.balance = data.balance;
          if (typeof window.pesaWorld !== 'undefined') window.pesaWorld.liveBalance = data.balance;
        }
      } catch (e) {
        this.showMsg('Could not enter deal. Try again.', false);
      }
      this.dealLoading = false;
    },

    async takeLoan(lp) {
      const amount = this.loanAmounts[lp.id];
      if (!amount || amount < lp.min_amount || amount > lp.max_amount) {
        this.showMsg('Enter a valid amount between KES ' + lp.min_amount.toLocaleString() + ' and KES ' + lp.max_amount.toLocaleString(), false);
        return;
      }
      if (this.loanLoading) return;
      const cs = document.querySelector('meta[name=csrf-token]')?.content ?? '';
      this.loanLoading = true;
      try {
        const res  = await fetch('/loans/take', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cs, 'Accept': 'application/json' },
          body: JSON.stringify({ loan_product_id: lp.id, amount }),
        });
        const data = await res.json();
        if (data.error) {
          this.showMsg(data.error, false);
        } else {
          this.showMsg(data.message ?? 'Loan disbursed!', true);
          setTimeout(() => location.reload(), 1200);
        }
      } catch (e) {
        this.showMsg('Could not take loan. Try again.', false);
      }
      this.loanLoading = false;
    },

    async repayLoan(loan) {
      if (this.loanLoading) return;
      const cs = document.querySelector('meta[name=csrf-token]')?.content ?? '';
      this.loanLoading = true;
      try {
        const res  = await fetch('/loans/' + loan.id + '/repay', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cs, 'Accept': 'application/json' },
          body: JSON.stringify({ amount: loan.payment_amount }),
        });
        const data = await res.json();
        if (data.error) {
          this.showMsg(data.error, false);
        } else {
          this.showMsg(data.message ?? 'Payment made!', true);
          setTimeout(() => location.reload(), 1200);
        }
      } catch (e) {
        this.showMsg('Could not process payment. Try again.', false);
      }
      this.loanLoading = false;
    },

    celebrateTrade(ok, icon, message, education, basics) {
      this.shareTradeResult = { ok, icon, message, education, basics };
      setTimeout(() => { this.shareTradeResult = null; }, basics ? 11000 : 7000);
    },

    // Patches district.shares/my_shares/balance directly from the trade response
    // instead of reloading the page — the celebration card would otherwise get
    // cut short by the reload, and a live update feels far more responsive.
    applyTradeUpdate(district, shareId, data) {
      if (data.share) {
        const sIdx = district.shares.findIndex(s => s.id === shareId);
        if (sIdx >= 0) district.shares[sIdx] = data.share;
      }
      const hIdx = district.my_shares.findIndex(h => h.share_id === shareId);
      if (data.holding) {
        if (hIdx >= 0) district.my_shares[hIdx] = data.holding;
        else district.my_shares.push(data.holding);
      } else if (hIdx >= 0) {
        district.my_shares.splice(hIdx, 1);
      }
      if (typeof window.pesaWorld !== 'undefined') window.pesaWorld.liveBalance = data.balance;
    },

    async buyShare(share, district) {
      const qty = parseInt(this.shareQty[share.id]) || 1;
      if (qty < 1) { this.showMsg('Enter a valid quantity', false); return; }
      if (this.shareLoading) return;
      const cs = document.querySelector('meta[name=csrf-token]')?.content ?? '';
      this.shareLoading = true;
      try {
        const res  = await fetch('/shares/buy', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cs, 'Accept': 'application/json' },
          body: JSON.stringify({ share_id: share.id, quantity: qty }),
        });
        const data = await res.json();
        if (data.error) {
          this.showMsg(data.error, false);
        } else {
          this.celebrateTrade(true, share.icon, data.message ?? 'Bought!', data.education, data.basics);
          this.applyTradeUpdate(district, share.id, data);
          this.shareQty[share.id] = null;
        }
      } catch (e) {
        this.showMsg('Could not buy shares. Try again.', false);
      }
      this.shareLoading = false;
    },

    async sellShare(holding, district) {
      const qty = parseInt(this.shareQty[holding.share_id]) || holding.quantity;
      if (qty < 1) { this.showMsg('Enter a valid quantity', false); return; }
      if (this.shareLoading) return;
      const cs = document.querySelector('meta[name=csrf-token]')?.content ?? '';
      this.shareLoading = true;
      try {
        const res  = await fetch('/shares/sell', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cs, 'Accept': 'application/json' },
          body: JSON.stringify({ share_id: holding.share_id, quantity: qty }),
        });
        const data = await res.json();
        if (data.error) {
          this.showMsg(data.error, false);
        } else {
          this.celebrateTrade((data.profit_loss ?? 0) >= 0, holding.icon, data.message ?? 'Sold!', data.education, data.basics);
          this.applyTradeUpdate(district, holding.share_id, data);
          this.shareQty[holding.share_id] = null;
        }
      } catch (e) {
        this.showMsg('Could not sell shares. Try again.', false);
      }
      this.shareLoading = false;
    },
  };
}
