const CACHE_VERSION = "v2";
const CACHE_NAME = `m3-music-cache-${CACHE_VERSION}`;
const OFFLINE_URL = "/offline.html";

const ASSETS_TO_CACHE = [
  "/",
  "/index.html",
  "/offline.html",
  "/manifest.json",
  "/favicon.ico",
  "/icons/icon-192x192.png",
  "/music.css",
  "/lecteur.css",
  "/db.js",
  "/ma-musique.html",
  "/music-db-integrator.js",
  "/albums.html",
  "/artistes.html",
  "/rap.html",
  "/toietmoi.html",
  "/classique.jpg",
  "/rap.png",
  "/NAS BLK/nasblk.html",
  "/NAS BLK/tsola.html",
  "/NAS BLK/fataha.html",
  "/NAS BLK/roho.html",
  "/SLK/slk.html",
  "/SLK/ngamneno.html",
  "/SLK/ndezeanla.html",
  "/SLK/djitamke.html",
  "/Djoudja & Rimka La Pute/djoudjarimka.html",
  "/Djoudja & Rimka La Pute/mgala.html",
  "/COMOSTYLE/comostyle.html",
  "/COMOSTYLE/bumbwa.html",
  "/Victorious Awax/awax.html",
  "/Victorious Awax/tafauti.html",
  "/Victorious Awax/roho.html",
  "/Victorious Awax/niyenshi.html",
  "/Victorious Awax/mshinda.html",
  "/AYDII LAMANY/aydii.html",
  "/AYDII LAMANY/sabiki.html",
  "/Sahel/sahel.html",
  "/Sahel/ngambaki.html",
  "/Killamen/killamen.html",
  "/Killamen/panga.html"
];

// Installation : pré-cache
self.addEventListener("install", event => {
  console.log("[SW] Install - version:", CACHE_NAME);
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Activation : nettoyage des anciens caches
self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            console.log("[SW] Delete old cache:", key);
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Requête (fetch)
self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Mettre à jour le cache en arrière-plan
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(event.request, responseClone);
        });
        return response;
      })
      .catch(() => {
        // Requête échouée : utiliser le cache
        return caches.match(event.request).then(cached => {
          if (cached) return cached;

          // Pour les pages HTML : renvoyer la page hors ligne
          if (
            event.request.mode === "navigate" ||
            event.request.headers.get("accept").includes("text/html")
          ) {
            return caches.match(OFFLINE_URL);
          }
        });
      })
  );
});
