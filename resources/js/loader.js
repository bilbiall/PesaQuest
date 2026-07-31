/**
 * PesaQuest global screen loader.
 *
 * Bundled into app.js so EVERY page (dashboard, world, life, marketplace,
 * gameset, admin…) shows the branded loader:
 *   1. on initial render — visible until the window `load` event
 *   2. on navigation — reappears the moment a same-origin link or form
 *      submission starts, and the next page's copy takes over.
 *
 * If the page already ships its own #pq-loader (layouts/app.blade.php),
 * we adopt it instead of injecting a duplicate.
 */

const MESSAGES = [
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
    '🎯 Unlocking New Opportunities...',
];

const MIN_SHOW_MS = 450;   // never a sub-frame flash — feels intentional
const CSS = `
#pq-loader{position:fixed;inset:0;z-index:99999;background:rgba(7,6,15,.82);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);display:flex;flex-direction:column;align-items:center;justify-content:center;transition:opacity .5s ease;}
#pq-loader.pq-fade{opacity:0;pointer-events:none;}
.pq-ring{position:relative;width:96px;height:96px;}
.pq-ring svg{position:absolute;inset:0;width:100%;height:100%;animation:pqSpin 1.6s linear infinite;}
@keyframes pqSpin{to{transform:rotate(360deg);}}
.pq-ring-logo{position:absolute;inset:14px;border-radius:50%;object-fit:cover;animation:pqLogoBreathe 2.4s ease-in-out infinite;}
@keyframes pqLogoBreathe{0%,100%{transform:scale(1);opacity:.88;}50%{transform:scale(1.06);opacity:1;}}
.pq-loader-brand{margin-top:18px;font-family:'Figtree',sans-serif;font-size:1.35rem;font-weight:900;letter-spacing:-.02em;background:linear-gradient(135deg,#fff 0%,rgba(255,255,255,.6) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.pq-dots{display:flex;gap:5px;margin-top:10px;}
.pq-dot{width:7px;height:7px;border-radius:50%;animation:pqDot 1.5s ease-in-out infinite;}
.pq-dot:nth-child(1){animation-delay:0s;background:#15C77E;}
.pq-dot:nth-child(2){animation-delay:.18s;background:#6366f1;}
.pq-dot:nth-child(3){animation-delay:.36s;background:#f59e0b;}
@keyframes pqDot{0%,80%,100%{transform:scale(.7);opacity:.35;}40%{transform:scale(1.3);opacity:1;}}
.pq-msg{margin-top:24px;font-family:'Figtree',sans-serif;font-size:.8rem;font-weight:500;color:rgba(255,255,255,.55);letter-spacing:.04em;text-align:center;min-height:1.5em;}
`;

let shownAt = 0;
let msgTimer = null;

function buildLoader() {
    const style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);

    const el = document.createElement('div');
    el.id = 'pq-loader';
    el.setAttribute('aria-live', 'polite');
    el.setAttribute('aria-label', 'Loading PesaQuest');
    el.innerHTML = `
        <div class="pq-ring">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="44" stroke="rgba(255,255,255,0.07)" stroke-width="4.5"/>
                <circle cx="50" cy="50" r="44" stroke="url(#pqArcGrad)" stroke-width="4.5" stroke-linecap="round" stroke-dasharray="88 189"/>
                <defs><linearGradient id="pqArcGrad" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#15C77E"/><stop offset="50%" stop-color="#6366f1"/><stop offset="100%" stop-color="#f59e0b"/>
                </linearGradient></defs>
            </svg>
            <img src="/img/game/screenloader.png" class="pq-ring-logo" alt="" onerror="this.style.display='none'">
        </div>
        <div class="pq-loader-brand">PesaQuest</div>
        <div class="pq-dots"><div class="pq-dot"></div><div class="pq-dot"></div><div class="pq-dot"></div></div>
        <p class="pq-msg">${MESSAGES[Math.floor(Math.random() * MESSAGES.length)]}</p>`;
    document.body.appendChild(el);
    return el;
}

function loaderEl() {
    return document.getElementById('pq-loader') || buildLoader();
}

function startMessages(el) {
    const msg = el.querySelector('.pq-msg, #pq-msg');
    if (!msg || msgTimer) return;
    msgTimer = setInterval(() => {
        msg.textContent = MESSAGES[Math.floor(Math.random() * MESSAGES.length)];
    }, 1800);
}

function show() {
    const el = loaderEl();
    el.classList.remove('pq-fade');
    el.style.display = 'flex';
    shownAt = Date.now();
    startMessages(el);
}

function hide() {
    const el = document.getElementById('pq-loader');
    if (!el) return;
    const wait = Math.max(0, MIN_SHOW_MS - (Date.now() - shownAt));
    setTimeout(() => {
        el.classList.add('pq-fade');
        if (msgTimer) { clearInterval(msgTimer); msgTimer = null; }
        setTimeout(() => { el.style.display = 'none'; }, 550);
    }, wait);
}

// ── 1. Initial page load ─────────────────────────────────────────────────────
if (!document.getElementById('pq-loader')) {
    // Inject as soon as the body exists; hide when the page has fully loaded
    if (document.body) { show(); } else {
        document.addEventListener('DOMContentLoaded', show, { once: true });
    }
}
if (document.readyState === 'complete') {
    hide();
} else {
    window.addEventListener('load', hide, { once: true });
}

// bfcache restore (Android/iOS back button) — never leave a stuck overlay
window.addEventListener('pageshow', (e) => { if (e.persisted) hide(); });

// ── 2. Outgoing navigation ───────────────────────────────────────────────────
document.addEventListener('click', (e) => {
    const a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href') || '';
    if (
        a.target === '_blank' || a.hasAttribute('download') ||
        href.startsWith('#') || href.startsWith('javascript:') ||
        href.startsWith('mailto:') || href.startsWith('tel:') ||
        e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0
    ) return;
    try {
        const url = new URL(a.href, window.location.href);
        if (url.origin !== window.location.origin) return;
        // Same-page anchor?
        if (url.pathname === window.location.pathname && url.hash) return;
    } catch { return; }
    show();
}, true);

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.target === '_blank' || form.dataset.noLoader !== undefined) return;
    // AJAX-driven forms usually preventDefault — only show if navigation really starts
    setTimeout(() => { if (!e.defaultPrevented) show(); }, 0);
}, true);

// Safety net: if a click showed the loader but navigation never happened
// (JS-handled link), auto-hide after a few seconds
setInterval(() => {
    const el = document.getElementById('pq-loader');
    if (el && !el.classList.contains('pq-fade') && el.style.display !== 'none'
        && shownAt && Date.now() - shownAt > 6000 && document.readyState === 'complete') {
        hide();
    }
}, 2000);
