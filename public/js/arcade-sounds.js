/* ═══════════════════════════════════════════════════════════════
   ARCADE SOUND — procedural WebAudio, no MP3 files (same approach
   as the Pesa City world SoundMgr in public/js/world.js, kept as
   its own small module since the arcade page doesn't load world.js).
   ═══════════════════════════════════════════════════════════════ */
const ArcadeSound = (() => {
  let _ctx = null;
  let _on = true;

  function _ctx_() {
    if (!_ctx) {
      try { _ctx = new (window.AudioContext || window.webkitAudioContext)(); } catch (_) { return null; }
    }
    if (_ctx.state === 'suspended') { _ctx.resume().catch(() => {}); }
    return _ctx;
  }

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
    o.start(startAt); o.stop(startAt + dur + 0.02);
  }

  function _noise(startAt, dur, gainVal, hiCut = 2400) {
    const ctx = _ctx_(); if (!ctx) return;
    const bufSz = Math.ceil(ctx.sampleRate * dur);
    const buf = ctx.createBuffer(1, bufSz, ctx.sampleRate);
    const data = buf.getChannelData(0);
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

  function _bell(freq, startAt, dur, gain) {
    const ctx = _ctx_(); if (!ctx) return;
    const g = ctx.createGain();
    g.gain.setValueAtTime(gain, startAt);
    g.gain.exponentialRampToValueAtTime(0.001, startAt + dur);
    g.connect(ctx.destination);
    [1, 2.756].forEach((ratio, i) => {
      const o = ctx.createOscillator();
      o.type = i === 0 ? 'triangle' : 'sine';
      o.frequency.value = freq * ratio;
      const hg = ctx.createGain(); hg.gain.value = i === 0 ? 1 : 0.32;
      o.connect(hg); hg.connect(g);
      o.start(startAt); o.stop(startAt + dur + 0.05);
    });
  }

  const _sfx = {
    // Dice rattle — a burst of short noise clicks, pitch rising slightly
    roll() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      for (let i = 0; i < 6; i++) {
        _noise(t + i * 0.07, 0.06, 0.20, 3000 + i * 200);
      }
    },

    // A single token hop
    move() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      _osc('triangle', 260, t, 0.1, 0.18);
    },

    // Cash in — bright ascending chime
    coinGain() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [660, 880, 1180].forEach((f, i) => _bell(f, t + i * 0.05, 0.4, 0.26));
    },

    // Cash out — dull descending thud
    coinLoss() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      _osc('sawtooth', 220, t, 0.2, 0.20);
      _osc('sawtooth', 160, t + 0.08, 0.24, 0.18);
    },

    // Ladder climb — quick rising sparkle
    ladder() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [523, 659, 784, 988].forEach((f, i) => _bell(f, t + i * 0.06, 0.35, 0.22));
    },

    // Snake bite + slide down — hiss then descending glide
    snake() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      _noise(t, 0.18, 0.22, 5000);
      const g = ctx.createGain();
      g.gain.setValueAtTime(0.22, t + 0.1);
      g.gain.exponentialRampToValueAtTime(0.001, t + 0.6);
      g.connect(ctx.destination);
      const o = ctx.createOscillator();
      o.type = 'sawtooth';
      o.frequency.setValueAtTime(500, t + 0.1);
      o.frequency.exponentialRampToValueAtTime(120, t + 0.6);
      o.connect(g); o.start(t + 0.1); o.stop(t + 0.65);
    },

    // Mystery tile reveal — a curious little "boing"
    mystery() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      const g = ctx.createGain();
      g.gain.setValueAtTime(0.001, t);
      g.gain.linearRampToValueAtTime(0.26, t + 0.05);
      g.gain.exponentialRampToValueAtTime(0.001, t + 0.35);
      g.connect(ctx.destination);
      const o = ctx.createOscillator();
      o.type = 'sine';
      o.frequency.setValueAtTime(300, t);
      o.frequency.linearRampToValueAtTime(600, t + 0.15);
      o.frequency.linearRampToValueAtTime(420, t + 0.35);
      o.connect(g); o.start(t); o.stop(t + 0.4);
    },

    // Bust — low sad drop
    bust() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      const g = ctx.createGain();
      g.gain.setValueAtTime(0.26, t);
      g.gain.exponentialRampToValueAtTime(0.001, t + 0.9);
      g.connect(ctx.destination);
      const o = ctx.createOscillator();
      o.type = 'sawtooth';
      o.frequency.setValueAtTime(220, t);
      o.frequency.exponentialRampToValueAtTime(60, t + 0.9);
      o.connect(g); o.start(t); o.stop(t + 0.95);
    },

    // Win fanfare — celebratory arpeggio + sparkle tail
    win() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [523, 659, 784, 1047, 1319].forEach((f, i) => _bell(f, t + i * 0.09, 0.75, 0.28));
      setTimeout(() => {
        const c = _ctx_(); if (!c) return;
        const t2 = c.currentTime;
        [1568, 1976, 2093].forEach((f, i) => _bell(f, t2 + i * 0.06, 0.45, 0.17));
      }, 480);
    },

    cashout() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [523, 784, 1047].forEach((f, i) => _bell(f, t + i * 0.07, 0.55, 0.24));
    },

    // Generic UI toggle (drawer, accordion, opponent row) — a soft, quick click,
    // reusing the same _osc() helper every other effect here is built from.
    toggle() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      _osc('triangle', 420, t, 0.06, 0.14);
    },

    // A notification arriving — a light two-note ping, reusing _bell().
    notify() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      [784, 988].forEach((f, i) => _bell(f, t + i * 0.08, 0.3, 0.2));
    },

    // An emoji reaction/taunt landing on the opponent's screen — a playful little "boing".
    reaction() {
      const ctx = _ctx_(); if (!ctx) return;
      const t = ctx.currentTime;
      const g = ctx.createGain();
      g.gain.setValueAtTime(0.22, t);
      g.gain.exponentialRampToValueAtTime(0.001, t + 0.3);
      g.connect(ctx.destination);
      const o = ctx.createOscillator();
      o.type = 'sine';
      o.frequency.setValueAtTime(500, t);
      o.frequency.exponentialRampToValueAtTime(850, t + 0.12);
      o.frequency.exponentialRampToValueAtTime(650, t + 0.3);
      o.connect(g); o.start(t); o.stop(t + 0.32);
    },
  };

  // Background ambience — the exact same loop the Pesa City world map plays
  // (SoundMgr.playWorld() in world.js), not a separate invented sound, so the
  // arcade doesn't feel like a different app musically.
  let _ambientAudio = null;
  function _startAmbient() {
    if (!_ambientAudio) {
      try {
        _ambientAudio = new Audio('/audio/map-loop.mp3');
        _ambientAudio.loop = true;
        _ambientAudio.volume = 0.38;
      } catch (_) { _ambientAudio = null; }
    }
    if (_ambientAudio && _ambientAudio.paused) _ambientAudio.play().catch(() => {});
  }
  function _stopAmbient() {
    if (_ambientAudio && !_ambientAudio.paused) _ambientAudio.pause();
  }

  return {
    get on() { return _on; },
    toggle(v) {
      _on = v ?? !_on;
      if (!_on) _stopAmbient();
      return _on;
    },
    // Creates/resumes the AudioContext on an early user gesture (no sound played)
    // so the first real play() call isn't also the browser's first-ever attempt
    // to unlock audio — reduces the chance of a missed/late first sound effect.
    unlock() { _ctx_(); },
    play(name) {
      if (!_on) return;
      try { _sfx[name]?.(); } catch (_) {}
    },
    startAmbient() { if (_on) _startAmbient(); },
    stopAmbient() { _stopAmbient(); },
  };
})();
