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
        // ✅ Usa a versão estável e compatível do DiceBox
        const { default: DiceBox } = await import(
            "https://unpkg.com/@3d-dice/dice-box@1.1.3/dist/dice-box.es.min.js"
        );

        // ⚙️ Inicializa corretamente com a nova API
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

        /**
         * 🧙 Função que rola o dado com animação e depois força o valor desejado.
         * @param {number} diceType  Ex: 4, 6, 10, 12, 20
         * @param {number} value     Valor que deve cair
         */
        async function rollControlled(diceType, value) {
            console.log(`🎲 Rolando D${diceType} com valor controlado: ${value}`);

            // Etapa 1: rola de forma normal
            await diceBox.roll(`1d${diceType}`);

            // Etapa 2: espera o tempo da animação física (~1.5s)
            setTimeout(() => {
                // Mostra o resultado visualmente forçado
                diceBox.showResult(`1d${diceType}`, [value]);
                console.log(`✨ Resultado manipulado: D${diceType} caiu em ${value}`);
            }, 1500);
        }

        // 🎮 Botões interativos
        [4, 6, 10, 12, 20].forEach(faces => {
            document.querySelector(`#btn-d${faces}`).addEventListener('click', async () => {
                const value = Math.floor(Math.random() * faces) + 1; // gera valor controlado
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
