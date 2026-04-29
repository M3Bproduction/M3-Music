/**
 * M3'Music - Script d'intégration favoris et historique
 * À inclure dans toutes les pages avec lecteur audio
 * 
 * Ce script s'occupe automatiquement de:
 * - Ajouter le bouton favori au lecteur
 * - Gérer les clics sur le bouton favori
 * - Enregistrer l'historique de lecture
 */

function initMusicDB() {
  // Attendre que db.js soit chargé
  if (typeof window.M3MusicDB === 'undefined') {
    console.warn('[M3Music] db.js non chargé, nouvelle tentative...');
    setTimeout(initMusicDB, 100);
    return;
  }

  // Vérifier que les éléments nécessaires existent
  const audio = document.getElementById('audio');
  const playBtn = document.getElementById('play');
  
  if (!audio || !playBtn) {
    console.warn('[M3Music] Lecteur audio non trouvé');
    return;
  }

  // Ajouter le bouton favori s'il n'existe pas
  addFavoriteButton();

  // Initialiser la base de données
  window.M3MusicDB.initDB().then(() => {
    console.log('[M3Music] DB initialisée');
    setupFavoriteButton();
    setupHistoryTracking();
  }).catch(err => {
    console.error('[M3Music] Erreur DB:', err);
  });
}

function addFavoriteButton() {
  const controls = document.querySelector('.controls');
  if (!controls) return;

  // Vérifier si le bouton existe déjà
  if (document.getElementById('favorite')) return;

  const favoriteBtn = document.createElement('button');
  favoriteBtn.id = 'favorite';
  favoriteBtn.title = 'Ajouter aux favoris';
  favoriteBtn.innerHTML = '<i class="far fa-heart"></i>';
  
  // Insérer avant le div des sliders
  const sliderDiv = controls.querySelector('div');
  if (sliderDiv) {
    controls.insertBefore(favoriteBtn, sliderDiv);
  } else {
    controls.appendChild(favoriteBtn);
  }
}

function setupFavoriteButton() {
  const favoriteBtn = document.getElementById('favorite');
  if (!favoriteBtn) return;

  // Fonction pour mettre à jour l'état du bouton
  async function updateFavoriteButton() {
    const track = playlist[currentTrack];
    if (!track) return;
    
    const songId = window.M3MusicDB.createSongId(track.title, track.artist);
    const isFav = await window.M3MusicDB.isFavorite(songId);
    favoriteBtn.innerHTML = isFav ? '<i class="fas fa-heart"></i>' : '<i class="far fa-heart"></i>';
  }

  // Gestion du clic sur le bouton favori
  favoriteBtn.addEventListener('click', async () => {
    const track = playlist[currentTrack];
    if (!track) return;
    
    const songId = window.M3MusicDB.createSongId(track.title, track.artist);
    const isFav = await window.M3MusicDB.isFavorite(songId);
    
    if (isFav) {
      await window.M3MusicDB.removeFromFavorites(songId);
    } else {
      await window.M3MusicDB.addToFavorites({
        id: songId,
        title: track.title,
        artist: track.artist,
        src: track.src,
        image: track.cover
      });
    }
    updateFavoriteButton();
  });

  // Mettre à jour quand la piste change
  const originalLoadTrack = window.loadTrack;
  window.loadTrack = async function(index) {
    if (originalLoadTrack) {
      originalLoadTrack(index);
    }
    await updateFavoriteButton();
  };

  // Mise à jour initiale
  updateFavoriteButton();
}

function setupHistoryTracking() {
  const audio = document.getElementById('audio');
  if (!audio) return;

  // Enregistrer dans l'historique quand une musique commence
  audio.addEventListener('play', async () => {
    const track = playlist[currentTrack];
    if (!track) return;
    
    await window.M3MusicDB.addToHistory({
      id: window.M3MusicDB.createSongId(track.title, track.artist),
      title: track.title,
      artist: track.artist,
      src: track.src,
      image: track.cover
    });
  });
}

// Lancer l'initialisation quand le DOM est prêt
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMusicDB);
} else {
  initMusicDB();
}