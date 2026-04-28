// public/js/room/dice-manager.js
import DiceBox from 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/dice-box-threejs.es.js';

let box = null;
let ultimoValorForcado = null;

/**
 * Inicializa o DiceBox se ainda não estiver pronto.
 */
async function initDiceBox() {
    if (!box) {
        box = new DiceBox('#dice-box', {
            assetPath: 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/',
            theme: 'default',
            scale: 5,
            light_intensity: 1,
            gravity_multiplier: 600,
            baseScale: 100,
            strength: 2,
            onRollComplete: handleRollComplete
        });

        await box.initialize();
        console.log('✅ DiceBox inicializado!');
    }
}

/**
 * Função global de rolagem de dados.
 * Pode ser chamada de qualquer outro script.
 * @param {number} facesDados - número de faces do dado (ex: 6, 20)
 * @param {number|null} valorForcado - valor forçado opcional
 */

async function funcaoChamarDados(facesDados, valorForcado = null) {

    await initDiceBox();

    ultimoValorForcado = valorForcado;

    // Desabilita botões do jogador ao rolar
    const btnRoll = document.getElementById('btn-roll');
    const btnSkip = document.getElementById('btn-skip');

    if (btnRoll) btnRoll.disabled = true;
    if (btnSkip) btnSkip.disabled = true;

    let rollString = `1d${facesDados}`;

    if (!isNaN(valorForcado) && valorForcado > 0) {
        rollString += `@${valorForcado}`;
    }

    box.roll(rollString);
}

function handleRollComplete(results) {

    console.log("🔥 onRollComplete disparou");

    const valor = ultimoValorForcado ?? results?.total ?? null;

    console.log('🎲 Resultado final:', valor);

    mostrarResultadoDados(valor);

    setTimeout(() => {

        console.log("🧹 Limpando dados...");

        if (box?.clearDice) {
            box.clearDice();
        }

        // Avança turno automaticamente após rolar dados (apenas se for a vez do jogador)
        const isMyTurn = window.turnState?.turnoAtual === String(window.CHAT_CONFIG?.userId);
        if (isMyTurn) {
            console.log("⏭️ Avançando turno automaticamente após rolagem...");
            window.avancarTurno?.();
        }
    }, 2000);

    // limpa o valor forçado para a próxima rolagem normal
    ultimoValorForcado = null;
}

function mostrarResultadoDados(valor) {
    const container = document.getElementById('dice-container');
    if (!container || valor === null || valor === undefined) {
        return;
    }

    const resultEl = document.createElement('div');
    resultEl.className = 'dice-result';
    resultEl.textContent = valor;
    container.appendChild(resultEl);

    resultEl.addEventListener('animationend', () => {
        resultEl.remove();
    });
}

// Torna acessível globalmente
window.funcaoChamarDados = funcaoChamarDados;
window.mostrarResultadoDados = mostrarResultadoDados;
