// ====== GAME FLOW ======

// Estado atual de seleção/modo
let modoAcaoAtual = null;
let personagemDaVezAtual = null;

/**
 * Obtém o card do personagem pelo ID (fallback se não estiver definido)
 */
function getCardById(personagemId) {
    if (window.getCardById) {
        return window.getCardById(personagemId);
    }
    return document.getElementById(`personagem-online-${personagemId}-pc`) ||
           document.getElementById(`personagem-online-${personagemId}-mb`);
}

/**
 * Destaca o personagem da vez com borda amarela (warning)
 * Remove destaque anterior
 */
function destacarPersonagemDaVez(personagemId) {
    // Remove destaque anterior da vez
    document.querySelectorAll('.personagem-card.hz-turno').forEach(c => {
        c.classList.remove('hz-turno', 'border', 'border-warning', 'border-3');
    });

    // Adiciona novo destaque
    const card = getCardById(personagemId);
    if (card) {
        card.classList.add('hz-turno', 'border', 'border-warning', 'border-3');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        personagemDaVezAtual = personagemId;
    }
}

/**
 * Define o modo de ação do mestre e aplica cores aos cards
 * Modos: 'dano', 'cura', 'upgrade'
 */
function definirModoAcao(modo) {
    // Remove modo anterior
    document.querySelectorAll('.personagem-card').forEach(c => {
        c.classList.remove('hz-modo-dano', 'hz-modo-cura', 'hz-modo-upgrade');
        c.classList.remove('border-danger', 'border-success', 'border-info');
    });

    if (!modo) {
        modoAcaoAtual = null;
        return;
    }

    modoAcaoAtual = modo;

    // Mapear modo para classe e cor
    const modoMap = {
        'dano': { classe: 'hz-modo-dano', cor: 'border-danger' },
        'cura': { classe: 'hz-modo-cura', cor: 'border-success' },
        'upgrade': { classe: 'hz-modo-upgrade', cor: 'border-info' }
    };

    const config = modoMap[modo];
    if (!config) return;

    // Aplica a cor a todos os cards (exceto o do turno atual que mantém warning)
    document.querySelectorAll('.personagem-card').forEach(card => {
        // Se for o card da vez, mantém warning
        if (card.classList.contains('hz-turno')) {
            return;
        }

        card.classList.add(config.classe, 'border', config.cor, 'border-3', 'hz-selecionavel');

        // Adiciona animação de pulse
        card.style.animation = 'hz-pulse 1.5s ease-in-out infinite';
    });

    console.log(`🎯 Modo de ação: ${modo.toUpperCase()}`);
}

/**
 * Remove modo de ação
 */
function limparModoAcao() {
    definirModoAcao(null);
    document.querySelectorAll('.personagem-card.hz-selecionavel').forEach(c => {
        c.classList.remove('hz-selecionavel');
        c.style.animation = '';
    });
}

/**
 * Destaca um personagem (versão anterior, mantida para compatibilidade)
 */
function destacarPersonagem(card) {
    document.querySelectorAll('.personagem-card').forEach(c => {
        c.classList.remove('border-warning', 'border-3');
    });
    if (card) {
        card.classList.add('border', 'border-warning', 'border-3');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

window.destacarPersonagem = destacarPersonagem;
window.destacarPersonagemDaVez = destacarPersonagemDaVez;
window.definirModoAcao = definirModoAcao;
window.limparModoAcao = limparModoAcao;
window.getCardById = getCardById;
