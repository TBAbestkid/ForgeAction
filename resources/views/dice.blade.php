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

    <div id="dice-container"></div>
</div>

<script type="module">
document.addEventListener("DOMContentLoaded", async () => {
    try {
        const { default: DiceBox } = await import(
            "https://unpkg.com/@3d-dice/dice-box@1.1.3/dist/dice-box.es.min.js"
        );

        const diceBox = new DiceBox("#dice-container", {
            assetPath: "/assets/",
            theme: "classic",
            scale: 25,
            gravity: 9.8,
            friction: 0.9,
            onRollComplete: result => console.log("🎯 Resultado da rolagem:", result)
        });

        await diceBox.init();

        // Função para "forçar" um valor no dado
        async function rollWithValue(diceType, value) {
            // Exemplo: "1d20@15" força cair 15
            await diceBox.roll(`1d${diceType}@${value}`);
        }

        // Listener dos botões
        [4, 6, 10, 12, 20].forEach(faces => {
            document.querySelector(`#btn-d${faces}`).addEventListener('click', async () => {
                const value = Math.floor(Math.random() * faces) + 1; // valor aleatório
                console.log(`🎲 Forçando D${faces} para cair em ${value}`);
                await rollWithValue(faces, value);
            });
        });

    } catch (err) {
        console.error("Erro ao carregar DiceBox:", err);
        const container = document.querySelector("#dice-container");
        container.innerHTML = `
            <p style="color:red; text-align:center; padding-top:200px;">
                Não foi possível carregar os dados 3D.<br>
                Verifique se a pasta /assets/themes/classic/ e /assets/ammo/ existem e estão corretas.
            </p>`;
    }
});
</script>
@endsection
