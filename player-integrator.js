/**
 * M3'Music - Intégrateur de lecteur global
 * Remplace le lecteur local par le lecteur global sur toutes les pages
 */

function initGlobalPlayerIntegrator() {
  // Attendre que globalPlayer soit prêt
  if (typeof window.globalPlayer === 'undefined') {
    setTimeout(initGlobalPlayerIntegrator, 100);
    return;
  }

  // Masquer le lecteur audio local
  const audioPlayer = document.querySelector('.audio-player');
  if (audioPlayer) {
    audioPlayer.style.display = 'none';
  }

  // Ajouter des boutons de lecture dans la playlist
  setupPlaylistButtons();

  console.log('[Integrator] Lecteur global activé');
}

function setupPlaylistButtons() {
  const playlistContainer = document.getElementById('playlist');
  if (!playlistContainer) return;

  // Ajouter des boutons play sur chaque élément
  const items = playlistContainer.querySelectorAll('.playlist-item');
  items.forEach((item, index) => {
    // Ajouter un bouton play
    const playBtn = document.createElement('button');
    playBtn.className = 'playlist-play-btn';
    playBtn.innerHTML = '<i class="fas fa-play"></i>';
    playBtn.title = 'Écouter';
    playBtn.onclick = (e) => {
      e.stopPropagation();
      playFromPlaylist(index);
    };
    
    // Insérer avant le lien de téléchargement
    const downloadLink = item.querySelector('a[download]');
    if (downloadLink) {
      item.insertBefore(playBtn, downloadLink);
    } else {
      item.appendChild(playBtn);
    }
  });
}

function playFromPlaylist(index) {
  if (typeof playlist !== 'undefined' && playlist[index]) {
    const track = playlist[index];
    
    // Ajouter la pochette si manquante
    if (!track.cover) {
      // Essayer de trouver la pochette depuis la page
      const bg = document.querySelector('.music-content');
      if (bg && bg.style.backgroundImage) {
        track.cover = bg.style.backgroundImage.replace(/url\(['"]?(.+?)['"]?\)/, '$1');
      }
    }
    
    // Jouer via le lecteur global
    window.playTrack(track, playlist, index);
    
    // Afficher le lecteur global
    const player = document.getElementById('global-player');
    if (player) {
      player.classList.add('active');
    }
  }
}

// Styles pour les boutons de playlist
const style = document.createElement('style');
style.textContent = `
  .playlist-play-btn {
    background: #e91e63;
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
  }
  .playlist-play-btn:hover {
    background: #d81b60;
    transform: scale(1.1);
  }
  .playlist-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
`;
document.head.appendChild(style);

// Lancer l'initialisation
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initGlobalPlayerIntegrator);
} else {
  initGlobalPlayerIntegrator();
}