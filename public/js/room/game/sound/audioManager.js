// Debug helper
function debugLog(...args) { console.log('[AudioManager]', ...args); }

class AudioManager {
    constructor() {
        this.sounds = {};
        this.volume = 0.5;

        this.loadSounds();
    }

    // dano:
    // https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022592/dano_ljxrei.mp3

    // turno:
    // https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022590/turno_vsfhwe.mp3

    // falha:
    // https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022588/falha_jifime.mp3

    // normal:
    // https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022586/normal_gmtkls.mp3

    // critico:
    // https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022584/critico_s0nqyo.mp3

    // vida:
    // https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022579/vida_hijagz.mp3

    // up:
    // https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022577/up_bws14x.mp3

    loadSounds() {
        const basePath = 'assets/sound';

        this.sounds = {
            critico: new Audio('https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022584/critico_s0nqyo.mp3'),
            dano: new Audio('https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022592/dano_ljxrei.mp3'),
            falha: new Audio('https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022588/falha_jifime.mp3'),
            normal: new Audio('https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022586/normal_gmtkls.mp3'),
            turno: new Audio('https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022590/turno_vsfhwe.mp3'),
            up: new Audio('https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022577/up_bws14x.mp3'),
            vida: new Audio('https://res.cloudinary.com/dyqxn8ci9/video/upload/v1778022579/vida_hijagz.mp3'),
            // Depois é pra trocar pelos links do Cloudinary, mas por enquanto é mais fácil testar localmente

        };

        Object.entries(this.sounds).forEach(([name, audio]) => {
            audio.volume = this.volume;

            // Event listeners para debug
            audio.addEventListener('loadstart', () => {
                debugLog(`📥 Carregando áudio: ${name}`);
            });

            audio.addEventListener('canplay', () => {
                debugLog(`✅ Áudio pronto: ${name}`);
            });

            audio.addEventListener('error', (e) => {
                console.error(`❌ Erro ao carregar áudio "${name}":`, e, audio.error);
            });
        });

        debugLog('🔊 Sons carregados:', Object.keys(this.sounds));
    }

    play(name) {
        const sound = this.sounds[name];

        if (!sound) {
            console.error(`❌ Som "${name}" não encontrado!`);
            return;
        }

        try {
            // Clona o audio corretamente
            const clone = sound.cloneNode(true);
            clone.volume = this.volume;

            debugLog(`🔊 Tocando som: ${name} (volume: ${this.volume})`);

            const playPromise = clone.play();

            // Trata a Promise de play()
            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        debugLog(`▶️ Som iniciado: ${name}`);
                    })
                    .catch(error => {
                        console.error(`❌ Erro ao reproduzir "${name}":`, error.name, error.message);
                        debugLog(`Erro: ${error.name} - ${error.message}`);
                    });
            }
        } catch (error) {
            console.error(`❌ Exceção ao tocar som "${name}":`, error);
        }
    }

    setVolume(volume) {
        this.volume = volume;
        Object.values(this.sounds).forEach(audio => {
            audio.volume = volume;
        });

        console.log(`🔊 Volume ajustado para ${volume}`);
    }
}


window.audioManager = new AudioManager();

// Exemplo de uso:
// window.audioManager.play('critico');
// window.audioManager.setVolume(0.8);
