/**
 * Pesa City global toast system — top-right popups on every page.
 *
 * Two layers:
 *  1. window.pesaToast(msg, type, opts) — call it from anywhere.
 *     type: 'success' | 'error' | 'warning' | 'info' | 'upgrade'
 *     opts: { icon: '💼', action: { label: 'Upgrade', href: '/subscribe' } }
 *
 *  2. A fetch interceptor: any 4xx JSON response carrying { error: "…" }
 *     toasts itself automatically — savings limits, marketplace, loans,
 *     spin cooldowns, job applications, plan gates… one hook covers the
 *     whole app. Responses with { upgrade: true } get a ✨ Subscribe button
 *     (subscribe_url). Duplicate messages within 2.5s are collapsed, so
 *     pages that also show the error locally don't double-toast.
 */
(function () {
    const RECENT = new Map(); // message -> last shown timestamp

    const STYLE = `
#pesa-toasts { position:fixed; top:74px; right:16px; z-index:99999; display:flex; flex-direction:column; gap:10px;
    width:min(370px, calc(100vw - 32px)); pointer-events:none; }
.pesa-toast { pointer-events:auto; display:flex; align-items:flex-start; gap:10px; padding:13px 15px; border-radius:16px;
    background:rgba(13,12,26,.96); backdrop-filter:blur(14px); box-shadow:0 16px 44px rgba(0,0,0,.55);
    font-family:'Figtree',system-ui,sans-serif; font-size:13px; font-weight:700; line-height:1.45; color:#e5e7eb;
    animation:pesaToastIn .25s cubic-bezier(.21,1.02,.55,1.01); }
.pesa-toast.success { border:1px solid rgba(21,199,126,.5); }
.pesa-toast.error   { border:1px solid rgba(248,113,113,.5); }
.pesa-toast.warning { border:1px solid rgba(245,158,11,.5); }
.pesa-toast.info    { border:1px solid rgba(77,168,247,.5); }
.pesa-toast.upgrade { border:1px solid rgba(167,139,250,.55); background:linear-gradient(135deg,rgba(30,20,60,.97),rgba(13,12,26,.97)); }
.pesa-toast .pt-icon  { font-size:19px; flex-shrink:0; margin-top:1px; }
.pesa-toast .pt-body  { flex:1; min-width:0; }
.pesa-toast .pt-action { display:inline-block; margin-top:8px; padding:6px 14px; border-radius:9px; font-size:12px; font-weight:900;
    color:#fff; text-decoration:none; background:linear-gradient(135deg,#8b5cf6,#6366f1); }
.pesa-toast .pt-action:hover { filter:brightness(1.15); }
.pesa-toast .pt-close { flex-shrink:0; background:none; border:none; color:rgba(255,255,255,.35); cursor:pointer; font-size:13px; padding:0 2px; }
.pesa-toast .pt-close:hover { color:#fff; }
.pesa-toast.leaving { animation:pesaToastOut .25s ease forwards; }
@keyframes pesaToastIn  { from { opacity:0; transform:translateX(40px) scale(.96); } }
@keyframes pesaToastOut { to   { opacity:0; transform:translateX(60px); } }
@media (max-width:640px) { #pesa-toasts { top:64px; } }
`;

    const ICONS = { success: '🎉', error: '⚠️', warning: '⏳', info: '💡', upgrade: '✨' };

    function box() {
        let el = document.getElementById('pesa-toasts');
        if (!el) {
            const style = document.createElement('style');
            style.textContent = STYLE;
            document.head.appendChild(style);
            el = document.createElement('div');
            el.id = 'pesa-toasts';
            document.body.appendChild(el);
        }
        return el;
    }

    window.pesaToast = function (msg, type = 'error', opts = {}) {
        if (!msg || typeof msg !== 'string') return;
        const now = Date.now();
        if ((RECENT.get(msg) || 0) > now - 2500) return; // collapse duplicates
        RECENT.set(msg, now);

        const toast = document.createElement('div');
        toast.className = 'pesa-toast ' + (ICONS[type] ? type : 'info');

        const icon = document.createElement('span');
        icon.className = 'pt-icon';
        icon.textContent = opts.icon || ICONS[type] || '💡';

        const body = document.createElement('div');
        body.className = 'pt-body';
        body.textContent = msg;
        if (opts.action && opts.action.href) {
            const a = document.createElement('a');
            a.className = 'pt-action';
            a.href = opts.action.href;
            a.textContent = opts.action.label || 'Open';
            body.appendChild(document.createElement('br'));
            body.appendChild(a);
        }

        const close = document.createElement('button');
        close.className = 'pt-close';
        close.textContent = '✕';

        const dismiss = () => {
            if (!toast.parentNode) return;
            toast.classList.add('leaving');
            setTimeout(() => toast.remove(), 240);
        };
        close.onclick = dismiss;

        toast.append(icon, body, close);
        box().appendChild(toast);
        setTimeout(dismiss, opts.action ? 9000 : 5500); // actionable toasts linger
    };

    // ── JSON error interceptor: every failed request becomes a toast ──
    const origFetch = window.fetch;
    window.fetch = async function (...args) {
        const res = await origFetch.apply(this, args);
        try {
            if (res.status >= 400 && res.status < 500 &&
                (res.headers.get('content-type') || '').includes('application/json')) {
                const data = await res.clone().json();
                if (data && typeof data.error === 'string' && data.error) {
                    const upgrade = data.upgrade === true;
                    window.pesaToast(data.error, upgrade ? 'upgrade' : 'error', {
                        action: upgrade && data.subscribe_url
                            ? { label: '✨ Subscribe & Unlock', href: data.subscribe_url }
                            : null,
                    });
                }
            }
        } catch (e) { /* never break the actual caller */ }
        return res;
    };
})();
