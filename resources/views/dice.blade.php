@extends('partials.app')
@section('title', 'Simulador de Rolagem de Dados')
@section('content')
<div class="container my-4">
    <h1>Simulador de Rolagem</h1>

    <input id="dice-notation" class="form-control mb-2" placeholder="Notação de dados (ex: 1d20)" />
    <input id="force-value" type="number" class="form-control mb-2" placeholder="Forçar valor (opcional)" />
    <button id="roll-btn" class="btn btn-primary mb-4">Rolar</button>
    <div id="scene-container" style="width: 400px; height: 400px;"></div>
</div>

<script type="module">
    import DiceBox from 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/dice-box-threejs.es.js';

    const box = new DiceBox('#scene-container', {
        assetPath: 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/',
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

    // Init async com IIFE
    (async () => {
        await box.init();

        document.getElementById('roll-btn').addEventListener('click', () => {
            const notationInput = document.getElementById('dice-notation').value || '1d6';
            const forceVal = parseInt(document.getElementById('force-value').value);

            let rollString = notationInput;

            // Se um valor for informado, força todos os dados para ele
            if (!isNaN(forceVal) && forceVal > 0) {
                const diceCount = notationInput.match(/\d+d\d+/g)?.[0]?.split('d')[0] || 1;
                rollString = `${diceCount}d6@${Array(diceCount).fill(forceVal).join(',')}`;
            }

            // Opção: mudar cores aleatoriamente
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
    })();
</script>
@endsection
