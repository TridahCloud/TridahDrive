const CACHE_NAME = 'tridahdrive-cache-v2';
const APP_SHELL = [
    '/',
    '/offline.html',
    '/manifest.webmanifest',
    '/css/dashboard.css',
    '/css/auth.css',
    '/js/theme-toggle.js',
    '/js/toast.js',
    '/js/notifications.js',
    '/images/tridah-icon-192.png',
    '/images/tridah-icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);
    const pathname = url.pathname;

    // Skip service worker entirely for project board routes to avoid caching issues
    // These routes need to work without service worker interference
    if (pathname.includes('/projects') || 
        pathname.includes('/project-board') ||
        (pathname.includes('/drives/') && pathname.includes('/projects'))) {
        // Don't intercept at all - let browser handle normally
        return;
    }

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Don't intercept requests that might be authorization errors
    // Allow 403/401 responses to pass through normally
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Don't cache error responses (4xx, 5xx) - just return them
                    if (response.status >= 400) {
                        return response;
                    }
                    // Only cache successful responses
                    if (response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(async (error) => {
                    // Only show offline page for actual network errors (not HTTP errors)
                    console.error('Service worker fetch error:', error);
                    try {
                        const cached = await caches.match(request);
                        if (cached) {
                            return cached;
                        }
                        // Only show offline page if it's a network error, not an HTTP error
                        const offlinePage = await caches.match('/offline.html');
                        if (offlinePage) {
                            return offlinePage;
                        }
                        // Fallback response if offline page not found
                        return new Response('Network error', { 
                            status: 503,
                            headers: { 'Content-Type': 'text/plain' }
                        });
                    } catch (cacheError) {
                        console.error('Cache error:', cacheError);
                        return new Response('Network error', { 
                            status: 503,
                            headers: { 'Content-Type': 'text/plain' }
                        });
                    }
                })
        );
        return;
    }

    if (request.destination === 'style' || request.destination === 'script' || request.destination === 'image') {
        event.respondWith(
            caches.match(request).then((cached) => {
                const fetchPromise = fetch(request)
                    .then((response) => {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        return response;
                    })
                    .catch(() => cached);
                return cached || fetchPromise;
            })
        );
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => {
            return (
                cached ||
                fetch(request)
                    .then((response) => {
                        if (response && response.status === 200 && response.type === 'basic') {
                            const copy = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        }
                        return response;
                    })
                    .catch(() => cached)
            );
        })
    );
});

