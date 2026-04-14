/*
 * Gerenciador de UI de Turnos
 * Ou seja, liberar os botões do player que é a vez, e bloquear os outros
 * O mestre será em turnUIManagerMaster.js, para não misturar as lógicas
 */

// turnUIManager.js
// Placeholder global para evitar erro se roomManager tentar chamar antes do carregamento completo
window.atualizarInterfaceTurno = window.atualizarInterfaceTurno || function(turnoEhMeu) {
    console.warn('⚠️ atualizarInterfaceTurno placeholder chamada antes da implementação final:', turnoEhMeu);
};

document.addEventListener('DOMContentLoaded', () => {
    const ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ WebSocket não disponível');
        return;
    }
});

function alterarTextoTurno() {
    const turnoInfo = document.getElementById('dice-placeholder');
    // Só trocar texto, vai exibir se é mestre, se é o player lá, e etc
}

function atualizarBotoesPlayer(turnoEhMeu) {

    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');

    const habilitar = turnoEhMeu && !window.isMestre;

    if (btnRoll) {
        btnRoll.disabled = !habilitar;
        if (habilitar) {
            // Adiciona event listener quando habilitar
            btnRoll.addEventListener('click', window.toggleOpcoesDados);
        } else {
            // Remove event listeners se não for a vez
            btnRoll.replaceWith(btnRoll.cloneNode(true));
        }
    }

    if (btnSkip) {
        btnSkip.disabled = !habilitar;
        if (habilitar) {
            // Adiciona event listener quando habilitar
            btnSkip.addEventListener('click', () => {
                window.avancarTurno?.();
            });
        } else {
            // Remove event listeners se não for a vez
            btnSkip.replaceWith(btnSkip.cloneNode(true));
        }
    }
}

function atualizarBotoesMestre(turnoDoMestre) {

    const btnMestre = document.getElementById('btnLancarMestre');
    const btnPermitir = document.getElementById('btnPermitirJogadaExtra');
    const btnDano = document.getElementById('btnDano');
    const btnCurar = document.getElementById('btnCurar');
    const btnUpar = document.getElementById('btnUpar');

    const habilitar = window.isMestre && turnoDoMestre;

    if (btnMestre) {
        btnMestre.disabled = !habilitar;
        if (habilitar) {
            btnMestre.addEventListener('click', window.toggleOpcoesDados);
        } else {
            btnMestre.replaceWith(btnMestre.cloneNode(true));
        }
    }

    if (btnPermitir) {
        btnPermitir.disabled = !habilitar;
        if (habilitar) {
            btnPermitir.addEventListener('click', () => { window.permitirJogada?.(); });
        } else {
            btnPermitir.replaceWith(btnPermitir.cloneNode(true));
        }
    }

    if (btnDano) {
        btnDano.disabled = !habilitar;
        if (habilitar) {
            btnDano.addEventListener('click', () => { window.definirModoAcao?.('dano'); window.ativarModoSelecao?.(); });
        } else {
            btnDano.replaceWith(btnDano.cloneNode(true));
        }
    }

    if (btnCurar) {
        btnCurar.disabled = !habilitar;
        if (habilitar) {
            btnCurar.addEventListener('click', () => { window.definirModoAcao?.('cura'); window.ativarModoSelecao?.(); });
        } else {
            btnCurar.replaceWith(btnCurar.cloneNode(true));
        }
    }

    if (btnUpar) {
        btnUpar.disabled = !habilitar;
        if (habilitar) {
            btnUpar.addEventListener('click', () => { window.definirModoAcao?.('upgrade'); window.ativarModoSelecao?.(); });
        } else {
            btnUpar.replaceWith(btnUpar.cloneNode(true));
        }
    }
}

function atualizarControleTurno() {

    const btnControle = document.getElementById('btnIniciarTurno');
    const icon = btnControle?.querySelector('i');

    if (window.isMestre && window.turnState.rodadaIniciada) {

        if (icon) {
            icon.classList.remove('fa-play');
            icon.classList.add('fa-forward-fast');
        }

        btnControle?.setAttribute('title', 'Próximo Turno');
    }
}

function atualizarInterfaceTurno(turnoEhMeu) {

    const turnoDoMestre = window.turnState.turnoAtual === "mestre";

    const diceOptions = document.getElementById('diceOptions');
    if (diceOptions) {
        diceOptions.classList.add('d-none');
    }

    atualizarBotoesPlayer(turnoEhMeu);
    atualizarBotoesMestre(turnoDoMestre);
    atualizarControleTurno();

    atualizarTextoTurno(turnoEhMeu);

    // 🆕 Destaca o personagem da vez (se não for mestre)
    if (!window.isMestre && !turnoDoMestre) {
        const personagensNaSala = document.querySelectorAll('[id^="personagem-online-"]');
        personagensNaSala.forEach(card => {
            const usuarioIdDoCard = card.dataset.usuarioId;
            if (String(usuarioIdDoCard) === String(window.turnState.turnoAtual)) {
                const personagemId = card.dataset.id;
                if (typeof window.destacarPersonagemDaVez === 'function') {
                    window.destacarPersonagemDaVez(personagemId);
                } else {
                    console.warn('⚠️ destacarPersonagemDaVez não disponível ainda');
                }
            }
        });
    }
}

function atualizarTextoTurno(turnoEhMeu) {

    const placeholder = document.getElementById('dice-placeholder');
    if (!placeholder) return;

    if (!window.turnState.rodadaIniciada) {
        placeholder.innerText = "🎲 Aguardando início da rodada...";
        return;
    }

    if (turnoEhMeu) {
        placeholder.innerText = "🔥 É o seu turno!";
        return;
    }

    if (window.turnState.turnoAtual === "mestre") {
        placeholder.innerText = "🧙 Turno do Mestre";
    } else {
        placeholder.innerText = "⏳ Aguardando outro jogador...";
    }
}

// Exportar nér.... disponibilizar globalmente para o roomManager usar
window.atualizarInterfaceTurno = atualizarInterfaceTurno;
