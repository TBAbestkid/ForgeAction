const CACHE_NAME = "forgeaction-v1.1";
const urlsToCache = [
    "/",
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

    if (event.request.method !== "GET") return;

    event.respondWith(
        caches.match(event.request).then((response) => {

            if (response) {
                return response;
            }

            return fetch(event.request)
                .then((networkResponse) => {

                    const responseClone = networkResponse.clone();

                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });

                    return networkResponse;
                })
                .catch(() => {
                    return new Response("Offline", {
                        status: 503,
                        statusText: "Offline"
                    });
                });

        })
    );

});
