const CACHE_NAME = 'aci-notas-static-v1';
const scopePath = new URL(self.registration.scope).pathname.replace(/\/$/, '');
const appPath = (path) => `${scopePath}${path}` || '/';
const offlinePage = appPath('/offline.html');

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll([
        offlinePage,
        appPath('/images/pwa/icon-192.png'),
        appPath('/images/pwa/icon-512.png'),
    ])));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(caches.keys().then((keys) => Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
    )));
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Never cache authenticated HTML. It must always come from Laravel.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(offlinePage)));
        return;
    }

    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) {
        return;
    }

    event.respondWith(caches.match(request).then((cached) => {
        if (cached) {
            return cached;
        }

        return fetch(request).then((response) => {
            if (!response.ok || response.type !== 'basic') {
                return response;
            }

            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));

            return response;
        });
    }));
});
