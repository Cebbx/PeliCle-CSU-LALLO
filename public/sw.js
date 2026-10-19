const CACHE_NAME = 'pelicle-v4';
const STATIC_ASSETS = [
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.map(key => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    // 1. NEVER intercept non-GET requests (e.g. POST, PUT, DELETE, Livewire updates)
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // 2. Ignore cross-origin requests so offline hotspot mode never throws SW errors
    if (url.origin !== self.location.origin) {
        return;
    }

    // 3. NEVER intercept dynamic endpoints: Livewire (including /livewire-*), API, or page navigations
    if (
        url.pathname.startsWith('/livewire') ||
        url.pathname.startsWith('/api/') ||
        event.request.headers.get('X-Livewire') ||
        event.request.mode === 'navigate'
    ) {
        return;
    }

    // 4. Static PWA assets only (icons, manifest)
    const isStatic = url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json';
    if (isStatic) {
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(event.request).catch(() => new Response('', { status: 408 }));
            })
        );
        return;
    }
});

