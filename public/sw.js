const CACHE_NAME = 'pelicle-v2';
const STATIC_ASSETS = [
    '/manifest.json',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS);
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
    // 1. NEVER touch cache for non-GET requests (e.g. POST, PUT, DELETE, Livewire updates)
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // 2. NEVER cache dynamic endpoints: Livewire requests, admin/employee/driver panels
    if (
        url.pathname.startsWith('/livewire/') ||
        event.request.headers.get('X-Livewire') ||
        event.request.mode === 'navigate'
    ) {
        // ALWAYS NETWORK FIRST: Always fetch live from server so mobile data is always 100% fresh
        event.respondWith(
            fetch(event.request).catch(err => {
                // Only if completely offline, try cache
                return caches.match(event.request);
            })
        );
        return;
    }

    // 3. Static assets only (icons, manifest)
    const isStatic = url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json';
    if (isStatic) {
        event.respondWith(
            caches.match(event.request).then(cachedResponse => {
                return cachedResponse || fetch(event.request);
            })
        );
        return;
    }

    // 4. Default: Network directly
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
