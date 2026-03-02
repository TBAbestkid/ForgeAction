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

function verificarPermissaoAcao() {

    if (!estadoRodada.ativa) return;

    const jogadorAtual = estadoRodada.ordem[estadoRodada.turnoAtual];

    const meuId = window.CHAT_CONFIG?.userId;

    const possoAgir = jogadorAtual.id === meuId;

    const turnControls = document.getElementById('turnControls');

    if (turnControls) {
        turnControls.classList.toggle('d-none', !possoAgir);
    }
}

function atualizarUIRodada() {
}

function lancarDados() {
    console.log(' 🎲 Lançar dados acionado');

    const faces = 20;
    const valor = Math.floor(Math.random() * faces) + 1;

    ws.send('/app/backchannel/rodadas', {
        acao: "lancarDados",
        salaId: window.CHAT_CONFIG?.salaId,
        faces,
        valor
    });
}
