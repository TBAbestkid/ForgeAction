// turnManager.js
let ws;

document.addEventListener('DOMContentLoaded', () => {
    ws = window.AppWebSocket;
    if (!ws) {
        console.error('❌ WebSocket não disponível');
        return;
    }

    const btnIniciar = document.getElementById('btnIniciarTurno');
    if (btnIniciar) {
        btnIniciar.addEventListener('click', () => {
            iniciarRodada();
        });
    }
});

function iniciarRodada() {
    const salaId = window.CHAT_CONFIG?.salaId;
    console.log('Iniciando rodada...');
    ws.send('/app/' + salaId, "iniciarRodada");
}