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

    // ✅ Importa DiceBox 3D via CDN (ESM)
    const { default: DiceBox } = await import(
        "https://unpkg.com/@3d-dice/dice-box@1.1.3/dist/dice-box.es.min.js"
    );

    const diceBox = new DiceBox({
        container: "#dice-container",
        assetPath: "/assets/",   // pasta dos assets do DiceBox
        theme: "classic",
        scale: 25,
        gravity: 9.8,
        friction: 0.9,
        delay: 200,
        onRollComplete: result => {
            console.log("🎯 onRollComplete callback:", result);
        }
    });

    await diceBox.init();
    console.log("✅ DiceBox inicializado com sucesso");

    // Função que rola dado e força o valor desejado
    async function rollWithValue(sides, value) {
        console.log(`\n🎲 [INÍCIO] Rolagem D${sides} → valor desejado: ${value}`);

        // 1️⃣ Rola o dado normalmente (animação física)
        console.log("⏳ Chamando diceBox.roll()...");
        await diceBox.roll(`1d${sides}`);
        console.log("✅ diceBox.roll() concluído");

        // 2️⃣ Espera a física terminar (~1.5s) e força o resultado
        setTimeout(() => {
            console.log("⏱️ Tempo de animação expirou, aplicando valor controlado...");
            try {
                diceBox.showResult(`1d${sides}`, [value]);
                console.log(`✨ Valor final do dado definido: ${value}`);
            } catch (err) {
                console.error("❌ Não foi possível aplicar valor controlado:", err);
            }
        }, 1500);
    }

    // Botões de controle
    [4, 6, 10, 12, 20].forEach(sides => {
        document.querySelector(`#btn-d${sides}`).addEventListener('click', () => {
            // 🎯 Gera valor aleatório
            const value = Math.floor(Math.random() * sides) + 1;
            console.log(`🎮 Botão D${sides} clicado → valor gerado: ${value}`);
            rollWithValue(sides, value);
        });
    });

    // Exporta globalmente (opcional)
    window.rollWithValue = rollWithValue;
    console.log("✅ Função rollWithValue() disponível globalmente");
});
</script>
@endsection
