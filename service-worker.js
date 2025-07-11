const CACHE_NAME = "m3-music-cache-v1";
const ASSETS_TO_CACHE = [
  "/",
  "/index.html",
  "/offline.html",
  "/manifest.json",
  "/favicon.ico",
  "/icons/icon-192x192.png",
  //"/icons/icon-512x512.png",
  "/music.css",
  "/albums.html",
  "/artistes.html",
  "/classique.jpg",
  "/rap.png",
  "/NAS BLK/nasblk.html",
  "/NAS BLK/nasblk.jpg",
  "/NAS BLK/tsola.jpg",
  "/SLK/slk.html",
  "/SLK/slk.jpg",
  "/SLK/ndezeanla.html",
  "/SLK/NDEZE ANLA/1. SLK - ZE-ANLA.mp3"
  "/SLK/NDEZE ANLA/ndezeanla.jpg",
  "/SLK/NGAM NENO/ngamneno.jpg",
  "/Victorious Awax/awax.html",
  "/Victorious Awax/awax.jpg",
  "/Victorious Awax/NI YENSHI/niyenshi.jpg",
  "/Victorious Awax/ROHO/roho.jpg",
  "/Victorious Awax/TafaUti/tafauti.jpg"
];

// Installation : mise en cache initiale
self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Activation : suppression des anciens caches
self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      )
    )
  );
  self.clients.claim();
});

// Gestion des requêtes : réseau d'abord, puis cache, puis offline.html pour HTML
self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseClone);
        });
        return response;
      })
      .catch(() => {
        return caches.match(event.request).then(cached => {
          if (cached) return cached;
          if (event.request.mode === 'navigate') {
            return caches.match("/offline.html");
          }
        });
      })
  );
});
