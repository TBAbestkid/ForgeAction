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

function atualizarBotoesPlayer(turnoEhMeu) {

    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');

    const habilitar = turnoEhMeu && !window.isMestre;

    if (btnRoll) btnRoll.disabled = !habilitar;
    if (btnSkip) btnSkip.disabled = !habilitar;
}

function atualizarBotoesMestre(turnoDoMestre) {

    const btnMestre = document.getElementById('btnLancarMestre');
    const btnPermitir = document.getElementById('btnPermitirJogadaExtra');
    const btnDano = document.getElementById('btnDano');
    const btnCurar = document.getElementById('btnCurar');
    const btnUpar = document.getElementById('btnUpar');

    const habilitar = window.isMestre && turnoDoMestre;

    if (btnMestre) btnMestre.disabled = !habilitar;
    if (btnPermitir) btnPermitir.disabled = !habilitar;
    if (btnDano) btnDano.disabled = !habilitar;
    if (btnCurar) btnCurar.disabled = !habilitar;
    if (btnUpar) btnUpar.disabled = !habilitar;
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

    atualizarBotoesPlayer(turnoEhMeu);
    atualizarBotoesMestre(turnoDoMestre);
    atualizarControleTurno();

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
