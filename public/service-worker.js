const CACHE_NAME = 'forgeaction-cache-v1';
const urlsToCache = [
    '/',
    '/loading.html',
    '/assets/images/forgeicon/icon-192x192.png',
    '/assets/images/forgeicon/icon-512x512.png',
    '/css/app.css',
    '/js/app.js',
];

self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then(function(cache) {
            return cache.addAll(urlsToCache);
        })
    );
});

self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
        .then(function(response) {
            return response || fetch(event.request);
        })
    );
});
