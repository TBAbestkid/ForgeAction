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
    console.log("🚀 DOM carregado, iniciando DiceBox...");

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
        onRollComplete: result => {
            console.log("🎯 onRollComplete callback:", result);
        }
    });

    await diceBox.init();
    console.log("✅ DiceBox inicializado com sucesso");

    async function rollWithValue(sides, value) {
        console.log(`\n🎲 [INÍCIO] Rolagem D${sides} → valor desejado: ${value}`);

        // 1️⃣ Rola normalmente
        console.log("⏳ Chamando diceBox.roll()...");
        await diceBox.roll(`1d${sides}`);
        console.log("✅ diceBox.roll() concluído");

        // 2️⃣ Espera a física terminar (~1.5s)
        setTimeout(() => {
            console.log("⏱️ Tempo de animação expirou, tentando aplicar valor controlado...");

            const die = diceBox.dice?.[0];
            console.log("🔍 Objeto do dado:", die);

            // Caminho 1: API interna
            if (die && typeof die.setResult === "function") {
                console.log("🔧 setResult() disponível, aplicando valor...");
                die.setResult(value);
                console.log(`✨ Valor final do dado definido via API interna: ${value}`);
                return;
            }

            // Caminho 2: fallback overlay
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
                textShadow: "0 0 30px #000",
                pointerEvents: "none",
                zIndex: "999",
                opacity: "0",
                transition: "opacity 0.4s",
            });

            const container = document.querySelector("#dice-container");
            container.style.position = "relative";
            container.appendChild(overlay);
            console.log("🖼️ Overlay criado e adicionado ao container");

            setTimeout(() => {
                overlay.style.opacity = "1";
                console.log("✨ Overlay visível");
            }, 1000);

            setTimeout(() => {
                overlay.style.opacity = "0";
                console.log("💨 Overlay começando a desaparecer");
                setTimeout(() => {
                    overlay.remove();
                    console.log("🗑️ Overlay removido");
                }, 400);
            }, 3000);

        }, 1500);
    }

    // Liga os botões com logs detalhados
    [4, 6, 10, 12, 20].forEach(sides => {
        document.querySelector(`#btn-d${sides}`).addEventListener("click", async () => {
            const forced = Math.floor(Math.random() * sides) + 1;
            console.log(`\n🎮 Botão D${sides} clicado → valor gerado: ${forced}`);
            await rollWithValue(sides, forced);
        });
    });

    // Exporta a função globalmente
    window.rollWithValue = rollWithValue;
    console.log("✅ Função rollWithValue() disponível globalmente");
});
</script>
@endsection
