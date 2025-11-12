@extends('partials.app')

@section('title', 'Dados Teste - Controle Total')

@section('content')
<div id="scene-container" style="width: 400px; height: 400px;"></div>

<script type="module">
    import DiceBox from 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/dice-box-threejs.es.js';

    const box = new DiceBox("#scene-container", {
        assetPath: 'https://unpkg.com/@3d-dice/dice-box-threejs/dist/',
        scale: 5,
        theme: 'default',
    });

    await box.init();
    await box.roll('1d20@18');
    console.log('Roll complete');
</script>
@endsection
