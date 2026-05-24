const CACHE_NAME = 'nexshop-cache-v1';
const urlsToCache = [
    '/',
    '/manifest.json'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    // Only cache GET requests
    if (event.request.method !== 'GET') return;
    
    // Skip caching for API and Admin routes
    if (event.request.url.includes('/api/') || event.request.url.includes('/admin/')) return;
    
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
