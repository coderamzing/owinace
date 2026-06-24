const CACHE_NAME = 'leadcliq-v2';

const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/favicon.ico',
];

const shouldHandleRequest = (request) => {
    const url = new URL(request.url);

    if (url.protocol !== 'http:' && url.protocol !== 'https:') {
        return false;
    }

    if (url.pathname.startsWith('/admin')) {
        return false;
    }

    if (url.pathname.startsWith('/js/') || url.pathname.startsWith('/css/')) {
        return false;
    }

    return request.method === 'GET';
};

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)),
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key)),
            ),
        ),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (! shouldHandleRequest(event.request)) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.ok) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }

                return response;
            })
            .catch(() => caches.match(event.request)),
    );
});
