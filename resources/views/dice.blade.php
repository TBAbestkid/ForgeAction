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

    <div id="dice-container" style="width:100%; height:500px; position: relative;"></div>
</div>

<script type="module">
document.addEventListener("DOMContentLoaded", async () => {
    console.log("🚀 DOM carregado, iniciando DiceBox 3D...");

    const { default: DiceBox } = await import(
        "https://unpkg.com/@3d-dice/dice-box@1.1.3/dist/dice-box.es.min.js"
    );

    const diceBox = new DiceBox({
        container: "#dice-container",
        assetPath: "/assets/",
        theme: "classic",
        scale: 25,
        gravity: 9.8,
        friction: 0.9,
        delay: 200,
        onRollComplete: results => {
            console.log("🎯 onRollComplete callback:", results);
        }
    });

    await diceBox.init();
    console.log("✅ DiceBox inicializado");

    // Função que gera a rolagem controlada
    async function rollWithValue(sides, value) {
        console.log(`\n🎲 Rolando D${sides} → valor desejado: ${value}`);

        // ✅ Passa o valor desejado no próprio roll
        await diceBox.roll([{ sides: sides, value: value }]);
        console.log("✅ diceBox.roll() concluído com valor controlado");
    }

    // Liga os botões
    [4,6,10,12,20].forEach(sides => {
        document.querySelector(`#btn-d${sides}`).addEventListener('click', () => {
            const value = Math.floor(Math.random() * sides) + 1;
            console.log(`🎮 Botão D${sides} clicado → valor gerado: ${value}`);
            rollWithValue(sides, value);
        });
    });

    window.rollWithValue = rollWithValue;
    console.log("✅ rollWithValue() disponível globalmente");
});
</script>
@endsection
