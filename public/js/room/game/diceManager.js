// public/js/room/dice-manager.js
import DiceBox from 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/dice-box-threejs.es.js';

let box = null;

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

    let rollString = `1d${facesDados}`;

    if (!isNaN(valorForcado) && valorForcado > 0) {
        rollString += `@${valorForcado}`;
    }

    box.roll(rollString);
}

function handleRollComplete(results) {

    console.log("🔥 onRollComplete disparou");

    const valor = results?.total ?? null;
    console.log('🎲 Resultado final:', valor);

    setTimeout(() => {

        console.log("🧹 Limpando dados...");

        if (box?.clearDice) {
            box.clearDice();
        }

        if (!window.isMestre) {
            console.log("⏭️ Player avançando turno automaticamente...");
            window.avancarTurno?.();
        }
    }, 4000);
}


// Torna acessível globalmente
window.funcaoChamarDados = funcaoChamarDados;
