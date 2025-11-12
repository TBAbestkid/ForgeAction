@extends('partials.app')

@section('title', 'Dados Teste - ForgeAction')

@section('content')
<div class="container my-5">
    <div id="controls" class="mb-4 text-center">
        <button id="btn-d4">🎲 D4</button>
        <button id="btn-d6">🎲 D6</button>
        <button id="btn-d10">🎲 D10</button>
        <button id="btn-d12">🎲 D12</button>
        <button id="btn-d20">🎲 D20</button>
    </div>

    <div id="dice-container" style="width:100%;height:500px;"></div>
</div>
<script type="module">
import { initDice } from '/js/dice3d.js';

(async () => {
    const { rollWithValue } = await initDice('#dice-container');

    [4,6,10,12,20].forEach(sides => {
        document.querySelector(`#btn-d${sides}`).addEventListener('click', () => {
            const value = Math.floor(Math.random() * sides) + 1;
            console.log(`🎮 Botão D${sides} clicado → valor gerado: ${value}`);
            rollWithValue(sides, value);
        });
    });
})();
</script>
@endsection
