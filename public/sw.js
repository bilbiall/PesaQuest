const CACHE_NAME = 'pesaquest-v4';
const OFFLINE_URL = '/offline';

const PRECACHE = [
    '/manifest.json',
    '/img/game/pwa-192.png',
    '/img/game/pwa-512.png',
    '/img/game/screenloader.png',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/admin')) return;
    if (event.request.url.includes('/gameset')) return;

    // Always serve precached assets from cache first
    const url = new URL(event.request.url);
    const isPrecached = PRECACHE.some(p => url.pathname === p);
    if (isPrecached) {
        event.respondWith(
            caches.match(event.request).then(cached => cached || fetch(event.request))
        );
        return;
    }

    // Network-first for HTML (app pages), cache as fallback
    event.respondWith(
        fetch(event.request)
            .then(response => {
                if (response.ok && event.request.headers.get('accept')?.includes('text/html')) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(event.request)
                .then(cached => cached || caches.match('/dashboard'))
            )
    );
});

self.addEventListener('sync', event => {
    if (event.tag === 'sync-decisions') {
        event.waitUntil(syncPendingDecisions());
    }
});

async function syncPendingDecisions() {
    // Placeholder for offline decision queuing
}

// ── Web Push ─────────────────────────────────────────────────────────────────

self.addEventListener('push', event => {
    let payload = {};
    try { payload = event.data ? event.data.json() : {}; } catch (e) {}

    const title = payload.title || 'PesaQuest';
    const url   = payload.url || '/dashboard';

    event.waitUntil((async () => {
        // Never double-notify a player already looking at the game — the
        // in-game bell/toast already told them. Only pop the OS notification
        // if no visible PesaQuest tab exists right now.
        const clientsList = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const isVisible = clientsList.some(c => c.visibilityState === 'visible' && c.focused !== false);
        if (isVisible) return;

        await self.registration.showNotification(title, {
            body: payload.body || '',
            icon: payload.icon || '/img/game/pwa-192.png',
            badge: payload.badge || '/img/game/pwa-192.png',
            tag: payload.tag || 'pesaquest',
            // Without renotify, a new notification with the SAME tag (e.g. two
            // announcements in a row — every broadcast shares tag 'announcement')
            // replaces the old one SILENTLY: no sound, no banner. That made
            // repeat broadcasts look like they never arrived.
            renotify: true,
            data: { url },
            // No custom sound file — Web Notifications always use the
            // device/browser's own notification sound (or silence, if the
            // player muted it at the OS level). There is no cross-platform
            // API to ship a custom audio file with a push notification.
            vibrate: [80, 40, 80],
        });
    })());
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url || '/dashboard';

    event.waitUntil((async () => {
        const clientsList = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        for (const client of clientsList) {
            if ('focus' in client) {
                await client.focus();
                if ('navigate' in client) await client.navigate(url);
                return;
            }
        }
        await self.clients.openWindow(url);
    })());
});
