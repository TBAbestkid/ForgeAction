import { DiceBox } from '@3d-dice/dice-box-threejs';

document.addEventListener('DOMContentLoaded', async () => {
    const box = new DiceBox('#scene-container', {
        assetPath: '/vendor/dicebox/', // ajustaremos já
        scale: 5,
        theme: 'default',
    });

    await box.init();

    document.querySelector('#roll-btn').addEventListener('click', () => {
        const notation = document.querySelector('#dice-notation').value || '1d20';
        box.roll(notation);
    });
});
