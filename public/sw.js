const CACHE_NAME = 'allhabit-v1';

const PRECACHE = [
    '/offline.html',
];

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (e) => {
    const { request } = e;

    if (request.method !== 'GET') return;

    if (request.mode === 'navigate') {
        e.respondWith(
            fetch(request)
                .then(res => {
                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    return res;
                })
                .catch(() => caches.match(request).then(r => r || caches.match('/offline.html')))
        );
        return;
    }

    if (request.destination === 'style' || request.destination === 'script' || request.destination === 'font') {
        e.respondWith(
            caches.match(request).then(cached => {
                const fetched = fetch(request).then(res => {
                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                    return res;
                });
                return cached || fetched;
            })
        );
        return;
    }
});
