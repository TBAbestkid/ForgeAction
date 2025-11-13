@extends('partials.app')
@section('title', 'Simulador de Rolagem de Dados')
@section('content')
<div class="container my-4">
    <h1>Simulador de Rolagem</h1>

    <div id="dice-options" class="mt-3 text-center">
        <button class="btn btn-outline-primary m-1 dice-btn" data-sides="4">D4</button>
        <button class="btn btn-outline-primary m-1 dice-btn" data-sides="6">D6</button>
        <button class="btn btn-outline-primary m-1 dice-btn" data-sides="10">D10</button>
        <button class="btn btn-outline-primary m-1 dice-btn" data-sides="12">D12</button>
        <button class="btn btn-outline-primary m-1 dice-btn" data-sides="20">D20</button>
    </div>

    <div class="my-3">
        <input type="number" id="force-value" class="form-control" placeholder="Insira valor que deseja"/>
    </div>

    <div id="scene-container" style="width: 400px; height: 400px; border: 1px solid #ccc;"></div>
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
            console.log('Resultado:', results);
        }
    });

    // Init
    (async () => {
        await box.initialize();

        // Seleciona todos os botões de dado
        document.querySelectorAll('.dice-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const sides = btn.dataset.sides;
                const forceVal = parseInt(document.getElementById('force-value').value);

                // Sempre 1 dado
                let rollString = `1d${sides}`;

                // Se houver valor forçado
                if (!isNaN(forceVal) && forceVal > 0) {
                    rollString += `@${forceVal}`;
                }

                box.roll(rollString);
            });
        });
    })();
</script>
@endsection
