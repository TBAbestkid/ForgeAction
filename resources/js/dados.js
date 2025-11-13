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
document.getElementById('roll-btn').addEventListener('click', () => {
    const notationInput = document.getElementById('dice-notation').value || '1d6';
    const forceVal = parseInt(document.getElementById('force-value').value);

    let rollString = notationInput;

    // Forçar valores se informado
    if (!isNaN(forceVal) && forceVal > 0) {
        const diceCount = notationInput.match(/\d+d\d+/g)?.[0]?.split('d')[0] || 1;
        rollString = `1d20@18`;
    }

    // Alterar cores aleatoriamente
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

    box.roll(rollString);
});
