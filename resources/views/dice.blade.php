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

    async function rollWithValue(sides, value) {
        console.log(`\n🎲 [INÍCIO] Rolagem D${sides} → valor desejado: ${value}`);

        // Rola normalmente
        await diceBox.roll(`1d${sides}`);
        console.log("✅ diceBox.roll() concluído");

        // Força o valor no primeiro dado da rolagem
        const die = diceBox.dice[0]; // pega o primeiro dado
        if (die && typeof die.setResult === "function") {
            die.setResult(value);
            console.log(`✨ Valor forçado no dado: ${value}`);
        } else {
            console.warn("⚠️ setResult() não disponível, usando overlay visual");

            const overlay = document.createElement("div");
            overlay.textContent = value;
            Object.assign(overlay.style, {
                position: "absolute",
                top: "50%",
                left: "50%",
                transform: "translate(-50%, -50%)",
                fontSize: "6rem",
                fontWeight: "bold",
                color: "#fff",
                textShadow: "0 0 25px rgba(0,0,0,0.9)",
                opacity: "0",
                pointerEvents: "none",
                zIndex: "999",
                transition: "opacity 0.4s"
            });

            const container = document.querySelector("#dice-container");
            container.style.position = "relative";
            container.appendChild(overlay);

            setTimeout(() => overlay.style.opacity = "1", 1000);
            setTimeout(() => {
                overlay.style.opacity = "0";
                setTimeout(() => overlay.remove(), 400);
            }, 3000);
        }
    }

    // Botões
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
