/* ============================================================
   LAKKAD LOHA — Service Worker
   Caches static assets + provides offline fallback
   ============================================================ */

const CACHE_NAME  = 'lakkadloha-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.3/chart.umd.min.js',
];

/* --- Install --- */
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })));
        }).then(() => self.skipWaiting())
    );
});

/* --- Activate — delete old caches --- */
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

/* --- Fetch strategy ---
   - CSS/JS/images: Cache-first
   - Navigation: Network-first, fallback to offline page
   - API calls:  Network-only (never cache)
*/
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and API routes
    if (request.method !== 'GET') return;
    if (url.pathname.startsWith('/api/')) return;
    if (url.pathname.startsWith('/sanctum/')) return;

    // Static assets — cache first
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font' ||
        url.hostname !== location.hostname
    ) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request).then(response => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => new Response('', { status: 503 }));
            })
        );
        return;
    }

    // Navigation — network first
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(OFFLINE_URL).then(cached => cached || new Response(
                    '<html><body style="font-family:sans-serif;text-align:center;padding:60px;background:#0c0d14;color:#f1f2f8"><h2>You\'re offline</h2><p>Check your internet connection and try again.</p></body></html>',
                    { headers: { 'Content-Type': 'text/html' } }
                ))
            )
        );
        return;
    }
});
