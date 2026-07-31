/**
 * PesaQuest Web Push — soft-prompt + subscribe/unsubscribe plumbing.
 *
 * Bundled into app.js so it runs on every substantial page. Never shows the
 * REAL browser permission dialog cold — a contextual banner asks first
 * ("want alerts before wages are lost?"), and only calls
 * Notification.requestPermission() if the player says yes. This roughly
 * triples opt-in rates versus prompting on load.
 */

const DISMISS_KEY = 'pq_push_dismissed_at';
const DISMISS_DAYS = 14;
const PROMPT_DELAY_MS = 8000;

function supported() {
    return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(base64);
    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
}

function csrfToken() {
    return document.querySelector('meta[name=csrf-token]')?.content || '';
}

async function fetchPublicKey() {
    try {
        const res = await fetch('/push/public-key', { headers: { Accept: 'application/json' } });
        if (!res.ok) return null;
        const data = await res.json();
        return data.available ? data.key : null;
    } catch (e) {
        return null;
    }
}

async function subscribe() {
    if (!supported()) return { ok: false, reason: 'This browser does not support notifications.' };

    const key = await fetchPublicKey();
    if (!key) return { ok: false, reason: 'Push notifications are not configured yet.' };

    let permission = Notification.permission;
    if (permission === 'default') {
        permission = await Notification.requestPermission();
    }
    if (permission !== 'granted') {
        return { ok: false, reason: permission === 'denied'
            ? 'Notifications are blocked in your browser settings.'
            : 'Permission was not granted.' };
    }

    try {
        const reg = await navigator.serviceWorker.ready;
        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(key),
            });
        }

        const json = sub.toJSON();
        const res = await fetch('/push/subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ endpoint: json.endpoint, keys: json.keys }),
        });
        if (!res.ok) return { ok: false, reason: 'Could not save your subscription.' };

        return { ok: true };
    } catch (e) {
        return { ok: false, reason: 'Something went wrong enabling notifications.' };
    }
}

async function unsubscribe() {
    try {
        const reg = await navigator.serviceWorker.ready;
        const sub = await reg.pushManager.getSubscription();
        const endpoint = sub?.endpoint;
        if (sub) await sub.unsubscribe();
        await fetch('/push/unsubscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ endpoint }),
        });
    } catch (e) {}
    return { ok: true };
}

window.PesaPush = { subscribe, unsubscribe, supported };

// ── Soft-prompt banner ───────────────────────────────────────────────────────

function dismissedRecently() {
    const raw = localStorage.getItem(DISMISS_KEY);
    if (!raw) return false;
    const days = (Date.now() - parseInt(raw, 10)) / 86400000;
    return days < DISMISS_DAYS;
}

function buildBanner() {
    const el = document.createElement('div');
    el.id = 'pq-push-banner';
    el.style.cssText = 'position:fixed;left:12px;right:12px;bottom:76px;z-index:9800;max-width:26rem;margin:0 auto;'
        + 'background:linear-gradient(135deg,#1e1b4b,#0f0e17);border:1px solid rgba(245,158,11,.35);border-radius:16px;'
        + 'padding:14px 16px;box-shadow:0 12px 32px rgba(0,0,0,.5);font-family:Figtree,sans-serif;color:#fff;'
        + 'display:flex;gap:10px;align-items:flex-start;opacity:0;transform:translateY(12px);transition:all .35s ease;';
    el.innerHTML = `
        <span style="font-size:22px;flex-shrink:0;">🔔</span>
        <div style="flex:1;min-width:0;">
            <p style="font-size:13px;font-weight:800;margin:0 0 4px;">Never miss payday</p>
            <p style="font-size:11.5px;color:rgba(255,255,255,.65);margin:0 0 10px;line-height:1.4;">
                Get alerted before wages are forfeited or bills go overdue — even when you're not playing.
            </p>
            <div style="display:flex;gap:8px;">
                <button id="pq-push-yes" style="flex:1;padding:7px 10px;border-radius:9px;border:none;font-size:12px;font-weight:800;color:#fff;cursor:pointer;background:linear-gradient(135deg,#f59e0b,#d97706);">Yes, notify me</button>
                <button id="pq-push-no" style="padding:7px 12px;border-radius:9px;border:1px solid rgba(255,255,255,.15);background:transparent;color:rgba(255,255,255,.55);font-size:12px;font-weight:700;cursor:pointer;">Not now</button>
            </div>
        </div>`;
    document.body.appendChild(el);
    requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
    return el;
}

function dismiss(el) {
    localStorage.setItem(DISMISS_KEY, String(Date.now()));
    el.style.opacity = '0';
    el.style.transform = 'translateY(12px)';
    setTimeout(() => el.remove(), 350);
}

async function maybeShowSoftPrompt() {
    if (!supported()) return;
    if (Notification.permission !== 'default') return; // already decided
    if (!document.querySelector('meta[name=csrf-token]')) return; // not logged into an authed page
    if (dismissedRecently()) return;
    if (document.getElementById('pq-push-banner')) return;

    const key = await fetchPublicKey();
    if (!key) return; // admin hasn't configured push yet

    const el = buildBanner();
    el.querySelector('#pq-push-no').addEventListener('click', () => dismiss(el));
    el.querySelector('#pq-push-yes').addEventListener('click', async () => {
        el.querySelector('#pq-push-yes').textContent = 'Enabling…';
        const result = await subscribe();
        dismiss(el);
        if (!result.ok && result.reason) {
            // Silent on decline/failure — no need to nag with an error toast for a soft-prompt
            console.debug('[push]', result.reason);
        }
    });
}

if (document.readyState === 'complete') {
    setTimeout(maybeShowSoftPrompt, PROMPT_DELAY_MS);
} else {
    window.addEventListener('load', () => setTimeout(maybeShowSoftPrompt, PROMPT_DELAY_MS));
}
