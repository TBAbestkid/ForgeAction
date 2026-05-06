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

        Object.values(this.sounds).forEach(audio => {
            audio.volume = this.volume;
        });

        debugLog('🔊 Sons carregados:', Object.keys(this.sounds));
    }

    play(name) {
        const sound = this.sounds[name];

        if (!sound) {
            console.warn(`Som "${name}" não encontrado!`);
            return;
        }

        // Clona a bomba do audio
        const clone = sound.cloneNode();
        clone.volume = this.volume;
        clone.play();
    }

    setVolume(volume) {
        this.volume = volume;
        Object.values(this.sounds).forEach(audio => {
            audio.volume = volume;
        });
        debugLog(`🔊 Volume ajustado para ${volume}`);
    }
}


window.audioManager = new AudioManager();

// Exemplo de uso:
// window.audioManager.play('critico');
// window.audioManager.setVolume(0.8);
