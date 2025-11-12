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

        // 🔧 Inicializa o DiceBox
        const diceBox = new DiceBox({
            container: "#dice-container",
            assetPath: "/assets/",
            theme: "classic",
            scale: 25,
            gravity: 9.8,
            friction: 0.9,
            delay: 200,
            onRollComplete: result => {
                console.log("🎯 Resultado real (antes da manipulação):", result);
            }
        });

        await diceBox.init();

        // 🧙 Função que rola o dado e força o valor visualmente
        async function rollControlled(diceType, value) {
            console.log(`🎲 Rolando D${diceType} com valor controlado: ${value}`);

            // Rola normalmente
            await diceBox.roll(`1d${diceType}`);

            // Aguarda a animação física (~1.5s)
            setTimeout(() => {
                const die = diceBox.dice?.[0];

                // Caminho 1: tenta API nativa (se existir)
                if (die && typeof die.setResult === "function") {
                    die.setResult(value);
                    console.log(`✨ Valor forçado via API interna: ${value}`);
                    return;
                }

                // Caminho 2: fallback visual (overlay 2D)
                console.warn("⚠️ setResult() não disponível — usando overlay visual");

                const overlay = document.createElement("div");
                overlay.className = "forced-result";
                overlay.textContent = value;
                Object.assign(overlay.style, {
                    position: "absolute",
                    top: "50%",
                    left: "50%",
                    transform: "translate(-50%, -50%)",
                    color: "#fff",
                    fontSize: "6rem",
                    fontWeight: "bold",
                    textShadow: "0 0 25px rgba(0,0,0,0.9)",
                    opacity: "0",
                    pointerEvents: "none",
                    transition: "opacity 0.4s ease-out",
                    zIndex: "999",
                });

                const container = document.querySelector("#dice-container");
                container.style.position = "relative";
                container.appendChild(overlay);

                setTimeout(() => (overlay.style.opacity = "1"), 1000);
                setTimeout(() => {
                    overlay.style.opacity = "0";
                    setTimeout(() => overlay.remove(), 400);
                }, 3000);
            }, 1500);
        }

        // 🎮 Liga os botões
        [4, 6, 10, 12, 20].forEach(faces => {
            document.querySelector(`#btn-d${faces}`).addEventListener('click', async () => {
                const value = Math.floor(Math.random() * faces) + 1;
                await rollControlled(faces, value);
            });
        });

    } catch (err) {
        console.error("Erro ao carregar DiceBox:", err);
        document.querySelector("#dice-container").innerHTML = `
            <p style="color:red; text-align:center; padding-top:200px;">
                Não foi possível carregar os dados 3D.<br>
                Verifique se a pasta /assets/themes/classic/ e /assets/ammo/ existem e estão corretas.
            </p>`;
    }
});
</script>
@endsection
