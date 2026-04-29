/**
 * M3'Music - Lecteur global persistant
 * Fonctionne sur toutes les pages sans interruption
 */

class GlobalPlayer {
  constructor() {
    this.audio = new Audio();
    this.isPlaying = false;
    this.currentTrack = null;
    this.playlist = [];
    this.currentIndex = 0;
    this.mode = 'normal'; // normal, shuffle, loop
    
    this.init();
  }

  async init() {
    // Charger l'état depuis localStorage
    this.loadState();
    
    // Garder le lecteur actif
    this.keepAlive();
    
    // Écouter les événements
    this.setupEventListeners();
    
    // Afficher/masquer le lecteur selon l'état
    this.updateUI();
    
    console.log('[GlobalPlayer] Initialisé');
  }

  loadState() {
    const state = localStorage.getItem('m3music_player_state');
    if (state) {
      const parsed = JSON.parse(state);
      this.currentTrack = parsed.currentTrack;
      this.playlist = parsed.playlist || [];
      this.currentIndex = parsed.currentIndex || 0;
      this.mode = parsed.mode || 'normal';
      
      if (this.currentTrack) {
        this.audio.src = this.currentTrack.src;
        this.audio.currentTime = parsed.currentTime || 0;
      }
    }
  }

  saveState() {
    const state = {
      currentTrack: this.currentTrack,
      playlist: this.playlist,
      currentIndex: this.currentIndex,
      mode: this.mode,
      currentTime: this.audio.currentTime
    };
    localStorage.setItem('m3music_player_state', JSON.stringify(state));
  }

  keepAlive() {
    // Empêcher le lecteur de se mettre en pause
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden && this.isPlaying) {
        this.audio.play().catch(() => {});
      }
    });
  }

  setupEventListeners() {
    // Fin de la piste
    this.audio.addEventListener('ended', () => this.handleTrackEnd());
    
    // Mise à jour de la progression
    this.audio.addEventListener('timeupdate', () => this.updateProgress());
    
    // Chargement terminé
    this.audio.addEventListener('loadedmetadata', () => this.updateUI());
    
    // Erreur
    this.audio.addEventListener('error', (e) => console.error('[GlobalPlayer] Erreur:', e));

    // Écouter les commandes depuis les autres pages
    window.addEventListener('storage', (e) => {
      if (e.key === 'm3music_command') {
        const command = JSON.parse(e.newValue);
        this.handleCommand(command);
      }
    });

    // Vérifier les commandes locales
    setInterval(() => {
      const command = localStorage.getItem('m3music_command');
      if (command) {
        this.handleCommand(JSON.parse(command));
        localStorage.removeItem('m3music_command');
      }
    }, 500);
  }

  handleCommand(command) {
    switch (command.action) {
      case 'play':
        this.playTrack(command.track, command.playlist, command.index);
        break;
      case 'toggle':
        this.togglePlay();
        break;
      case 'next':
        this.next();
        break;
      case 'prev':
        this.prev();
        break;
      case 'setMode':
        this.setMode(command.mode);
        break;
    }
  }

  sendCommand(action, data = {}) {
    localStorage.setItem('m3music_command', JSON.stringify({ action, ...data }));
    // Trigger storage event pour la même page
    window.dispatchEvent(new StorageEvent('storage', {
      key: 'm3music_command',
      newValue: JSON.stringify({ action, ...data })
    }));
  }

  playTrack(track, playlist = [], index = 0) {
    this.currentTrack = track;
    this.playlist = playlist;
    this.currentIndex = index;
    
    this.audio.src = track.src;
    this.audio.play()
      .then(() => {
        this.isPlaying = true;
        this.updateUI();
        this.saveState();
        
        // Ajouter à l'historique
        if (window.M3MusicDB) {
          window.M3MusicDB.addToHistory({
            id: window.M3MusicDB.createSongId(track.title, track.artist),
            title: track.title,
            artist: track.artist,
            src: track.src,
            image: track.cover
          });
        }
      })
      .catch(err => console.error('[GlobalPlayer] Erreur lecture:', err));
  }

  togglePlay() {
    if (this.isPlaying) {
      this.audio.pause();
      this.isPlaying = false;
    } else {
      this.audio.play();
      this.isPlaying = true;
    }
    this.updateUI();
    this.saveState();
  }

  next() {
    if (this.playlist.length === 0) return;
    
    if (this.mode === 'shuffle') {
      let nextIndex;
      do {
        nextIndex = Math.floor(Math.random() * this.playlist.length);
      } while (nextIndex === this.currentIndex && this.playlist.length > 1);
      this.currentIndex = nextIndex;
    } else {
      this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
    }
    
    this.playTrack(this.playlist[this.currentIndex], this.playlist, this.currentIndex);
  }

  prev() {
    if (this.playlist.length === 0) return;
    this.currentIndex = (this.currentIndex - 1 + this.playlist.length) % this.playlist.length;
    this.playTrack(this.playlist[this.currentIndex], this.playlist, this.currentIndex);
  }

  setMode(mode) {
    this.mode = mode;
    this.updateUI();
    this.saveState();
  }

  handleTrackEnd() {
    if (this.mode === 'loop') {
      this.audio.currentTime = 0;
      this.audio.play();
    } else if (this.mode === 'shuffle') {
      let nextIndex;
      do {
        nextIndex = Math.floor(Math.random() * this.playlist.length);
      } while (nextIndex === this.currentIndex && this.playlist.length > 1);
      this.currentIndex = nextIndex;
      this.playTrack(this.playlist[this.currentIndex], this.playlist, this.currentIndex);
    } else {
      this.next();
    }
  }

  updateProgress() {
    const progressBar = document.getElementById('global-progress');
    const currentTimeEl = document.getElementById('global-current-time');
    const durationEl = document.getElementById('global-duration');
    
    if (progressBar && this.audio.duration) {
      progressBar.value = (this.audio.currentTime / this.audio.duration) * 100;
    }
    
    if (currentTimeEl) {
      currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
    }
    
    if (durationEl) {
      durationEl.textContent = this.formatTime(this.audio.duration || 0);
    }
  }

  formatTime(seconds) {
    if (isNaN(seconds)) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  }

  updateUI() {
    const player = document.getElementById('global-player');
    const playBtn = document.getElementById('global-play-btn');
    const modeBtn = document.getElementById('global-mode-btn');
    const titleEl = document.getElementById('global-track-title');
    const artistEl = document.getElementById('global-track-artist');
    const coverEl = document.getElementById('global-cover');
    
    if (!player) return;
    
    // Afficher/masquer selon l'état
    if (this.currentTrack) {
      player.classList.add('active');
    } else {
      player.classList.remove('active');
    }
    
    // Bouton play/pause
    if (playBtn) {
      playBtn.innerHTML = this.isPlaying 
        ? '<i class="fas fa-pause"></i>' 
        : '<i class="fas fa-play"></i>';
    }
    
    // Mode
    if (modeBtn) {
      const icons = { normal: 'fa-music', shuffle: 'fa-random', loop: 'fa-redo' };
      modeBtn.innerHTML = `<i class="fas ${icons[this.mode]}"></i>`;
    }
    
    // Infos piste
    if (titleEl && this.currentTrack) titleEl.textContent = this.currentTrack.title;
    if (artistEl && this.currentTrack) artistEl.textContent = this.currentTrack.artist;
    if (coverEl && this.currentTrack) coverEl.src = this.currentTrack.cover || 'rap.png';
  }

  seek(percent) {
    if (this.audio.duration) {
      this.audio.currentTime = (percent / 100) * this.audio.duration;
    }
  }

  setVolume(value) {
    this.audio.volume = Math.max(0, Math.min(1, value));
  }
}

// Instance globale
window.globalPlayer = new GlobalPlayer();

// Fonctions utilitaires pour les pages
window.playTrack = function(track, playlist, index) {
  window.globalPlayer.playTrack(track, playlist, index);
};

window.toggleGlobalPlay = function() {
  window.globalPlayer.togglePlay();
};

window.nextTrack = function() {
  window.globalPlayer.next();
};

window.prevTrack = function() {
  window.globalPlayer.prev();
};

window.setGlobalMode = function(mode) {
  window.globalPlayer.setMode(mode);
};