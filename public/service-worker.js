const CACHE_NAME = "forgeaction-v1.1";
const urlsToCache = [
    "/",
    "/index.html",
    "/css/app.css",
    "/js/app.js",
    "/assets/images/forgeicon/icon-192x192.png",
    "/assets/images/forgeicon/icon-512x512.png",
];

self.addEventListener("install", (event) => {
    // Pula a espera — novo SW assume imediatamente
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache))
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
        Promise.all(
            keys
            .filter((key) => key !== CACHE_NAME)
            .map((key) => caches.delete(key))
        )
        )
    );
    // Novo SW assume o controle de todas as abas
    self.clients.claim();
});

self.addEventListener("fetch", (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => response || fetch(event.request))
    );
});
