@extends('partials.app')

@section('title', 'Dados Teste - Controle Total')

@section('content')

<div id="scene-container" style="width: 400px; height: 400px;"></div>

<script type="module">
    // import DiceBox from 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/dice-box-threejs.es.js';
    import DiceBox from '@3d-dice/dice-box-threejs';

    const Box = new DiceBox("#scene-container", {
        assetPath: 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/',
        scale: 5,
        theme: 'default',
        onRollComplete: (result) => {
            console.log('Roll result:', result);
        }
    });

    Box.initalize()
        .then(() => {
            setTimeout(() => {
                Box.roll('1d20@18');
            }, 1000);
        })
        .catch((error) => {
            console.error('Error initializing DiceBox:', error);
        });

    console.log('DiceBox initialized');
</script>
@endsection
