// dados.js
import DiceBox from '/vendor/dicebox/dice-box-threejs.es.js'; // arquivo JS local
// assetPath aponta para a pasta de texturas local
const box = new DiceBox('#scene-container', {
    assetPath: '/vendor/dicebox/textures/',
    theme: 'default',
    scale: 5,
    light_intensity: 1,
    gravity_multiplier: 600,
    baseScale: 100,
    strength: 2,
    onRollComplete: results => {
        console.log('Resultados:', results);
    }
});

// Função para rolar os dados
// Pega os elementos do HTML
const notationInputEl = document.getElementById('dice-notation');
const forceValueEl = document.getElementById('force-value');
const rollBtn = document.getElementById('roll-btn');

// Event listener do botão
rollBtn.addEventListener('click', () => {
    // 1️⃣ Pega a notação do dado (ex: 1d6, 1d20)
    const notation = notationInputEl.value || '1d20';

    // 2️⃣ Pega o valor forçado (opcional)
    const forceVal = parseInt(forceValueEl.value);

    // 3️⃣ Define a string de rolagem final
    let rollString = notation;

    // Se o usuário colocou um valor válido, força todos os dados para esse valor
    if (!isNaN(forceVal) && forceVal > 0) {
    const match = notation.match(/(\d+)d(\d+)/);
        if (match) {
            const diceCount = match[1];  // número de dados
            const diceSides = match[2];  // tipo de dado (4,6,8,20 etc)
            rollString = `${diceCount}d${diceSides}@${Array(diceCount).fill(forceVal).join(',')}`;
        }
    }

    // 4️⃣ Muda as cores do dado aleatoriamente (opcional)
    const colors = ['#00ffcb', '#ff6600', '#1d66af', '#7028ed', '#c4c427', '#d81128'];
    const randomColor = colors[Math.floor(Math.random() * colors.length)];

    box.updateConfig({
        theme_customColorset: {
            background: randomColor,
            foreground: '#ffffff',
            texture: 'marble',
            material: 'metal'
        }
    });

    // 5️⃣ Rola o dado
    box.roll(rollString);
});
