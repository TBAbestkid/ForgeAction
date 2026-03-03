/*
 * Gerenciador de UI de Turnos
 * Ou seja, liberar os botões do player que é a vez, e bloquear os outros
 * O mestre será em turnUIManagerMaster.js, para não misturar as lógicas
 */

// turnUIManager.js
let ws;
document.addEventListener('DOMContentLoaded', () => {
    ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ WebSocket não disponível');
        return;
    }
});

function alterarTextoTurno() {
    const turnoInfo = document.getElementById('dice-placeholder');
    // Só trocar texto, vai exibir se é mestre, se é o player lá, e etc
}

function desativarBotoesPlayer() {
    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');

    if (btnRoll) btnRoll.disabled = true;
    if (btnSkip) btnSkip.disabled = true;
}

function atualizarInterfaceTurno(turnoEhMeu) {

    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');
    const btnControle = document.getElementById('btnIniciarTurno');
    const icon = btnControle?.querySelector('i');

    if (btnRoll) btnRoll.disabled = !turnoEhMeu;
    if (btnSkip) btnSkip.disabled = !turnoEhMeu;

    if (window.isMestre && window.turnState.rodadaIniciada) {

        if (icon) {
            icon.classList.remove('fa-play');
            icon.classList.add('fa-forward-fast');
        }

        btnControle?.setAttribute('title', 'Próximo Turno');
    }

    atualizarTextoTurno(turnoEhMeu);
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
