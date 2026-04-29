/**
 * M3'Music - Gestion de base de données locale (IndexedDB)
 * Permet de stocker les favoris et l'historique d'écoute
 */

const DB_NAME = 'M3MusicDB';
const DB_VERSION = 1;

// Initialisation de la base de données
function initDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      // Store pour les favoris
      if (!db.objectStoreNames.contains('favorites')) {
        const favoritesStore = db.createObjectStore('favorites', { keyPath: 'id' });
        favoritesStore.createIndex('addedAt', 'addedAt', { unique: false });
      }

      // Store pour l'historique d'écoute
      if (!db.objectStoreNames.contains('history')) {
        const historyStore = db.createObjectStore('history', { keyPath: 'id' });
        historyStore.createIndex('playedAt', 'playedAt', { unique: false });
      }

      // Store pour les paramètres utilisateur
      if (!db.objectStoreNames.contains('settings')) {
        db.createObjectStore('settings', { keyPath: 'key' });
      }
    };
  });
}

// ============ GESTION DES FAVORIS ============

// Ajouter aux favoris
async function addToFavorites(song) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['favorites'], 'readwrite');
    const store = transaction.objectStore('favorites');
    
    const favoriteData = {
      id: song.id || song.title,
      title: song.title,
      artist: song.artist,
      src: song.src,
      image: song.image,
      addedAt: new Date().toISOString()
    };

    const request = store.put(favoriteData);
    request.onsuccess = () => resolve(true);
    request.onerror = () => reject(request.error);
  });
}

// Retirer des favoris
async function removeFromFavorites(songId) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['favorites'], 'readwrite');
    const store = transaction.objectStore('favorites');
    const request = store.delete(songId);
    request.onsuccess = () => resolve(true);
    request.onerror = () => reject(request.error);
  });
}

// Vérifier si un morceau est en favori
async function isFavorite(songId) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['favorites'], 'readonly');
    const store = transaction.objectStore('favorites');
    const request = store.get(songId);
    request.onsuccess = () => resolve(!!request.result);
    request.onerror = () => reject(request.error);
  });
}

// Obtenir tous les favoris
async function getFavorites() {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['favorites'], 'readonly');
    const store = transaction.objectStore('favorites');
    const request = store.getAll();
    request.onsuccess = () => resolve(request.result || []);
    request.onerror = () => reject(request.error);
  });
}

// ============ GESTION DE L'HISTORIQUE ============

// Ajouter à l'historique
async function addToHistory(song) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['history'], 'readwrite');
    const store = transaction.objectStore('history');
    
    const historyData = {
      id: song.id || song.title,
      title: song.title,
      artist: song.artist,
      src: song.src,
      image: song.image,
      playedAt: new Date().toISOString()
    };

    const request = store.put(historyData);
    request.onsuccess = () => resolve(true);
    request.onerror = () => reject(request.error);
  });
}

// Obtenir l'historique récent
async function getHistory(limit = 50) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['history'], 'readonly');
    const store = transaction.objectStore('history');
    const request = store.getAll();
    
    request.onsuccess = () => {
      const history = request.result || [];
      // Trier par date décroissante et limiter
      history.sort((a, b) => new Date(b.playedAt) - new Date(a.playedAt));
      resolve(history.slice(0, limit));
    };
    request.onerror = () => reject(request.error);
  });
}

// Vider l'historique
async function clearHistory() {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['history'], 'readwrite');
    const store = transaction.objectStore('history');
    const request = store.clear();
    request.onsuccess = () => resolve(true);
    request.onerror = () => reject(request.error);
  });
}

// ============ GESTION DES PARAMÈTRES ============

// Sauvegarder un paramètre
async function saveSetting(key, value) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['settings'], 'readwrite');
    const store = transaction.objectStore('settings');
    const request = store.put({ key, value });
    request.onsuccess = () => resolve(true);
    request.onerror = () => reject(request.error);
  });
}

// Obtenir un paramètre
async function getSetting(key, defaultValue = null) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(['settings'], 'readonly');
    const store = transaction.objectStore('settings');
    const request = store.get(key);
    request.onsuccess = () => resolve(request.result ? request.result.value : defaultValue);
    request.onerror = () => reject(request.error);
  });
}

// ============ FONCTIONS UTILITAIRES ============

// Créer un ID unique pour un morceau
function createSongId(title, artist) {
  return `${artist}-${title}`.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
}

// Exporter les fonctions globalement
window.M3MusicDB = {
  initDB,
  addToFavorites,
  removeFromFavorites,
  isFavorite,
  getFavorites,
  addToHistory,
  getHistory,
  clearHistory,
  saveSetting,
  getSetting,
  createSongId
};

console.log('[M3Music] Base de données initialisée');