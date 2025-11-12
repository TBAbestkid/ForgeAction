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

    <div id="dice-container" style="width:100%;height:500px; position: relative;"></div>
</div>

<script type="module">
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.162.0/build/three.module.js';
import { OrbitControls } from 'https://cdn.jsdelivr.net/npm/three@0.162.0/examples/jsm/controls/OrbitControls.js';
import * as CANNON from 'https://cdn.jsdelivr.net/npm/cannon-es@0.20.0/dist/cannon-es.js';
import { DiceManager, DiceD6, DiceD20 } from 'https://cdn.jsdelivr.net/npm/threejs-dice@1.1.0/dist/threejs-dice.module.js';

console.log("🚀 DOM carregado, iniciando cena 3D de dados...");

async function initDice(containerId = '#dice-container') {
    // Cena
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x222222);

    // Camera
    const camera = new THREE.PerspectiveCamera(50, window.innerWidth/window.innerHeight, 0.1, 1000);
    camera.position.set(0, 50, 100);

    // Renderer
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.querySelector(containerId).appendChild(renderer.domElement);

    // Controles
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;

    // Física
    const world = new CANNON.World();
    world.gravity.set(0, -9.8, 0);
    DiceManager.setWorld(world);

    // Chão
    const ground = new CANNON.Body({
        type: CANNON.Body.STATIC,
        shape: new CANNON.Plane(),
        material: new CANNON.Material(),
    });
    ground.quaternion.setFromEuler(-Math.PI/2, 0, 0);
    world.addBody(ground);

    // Função para rolar dado com valor controlado
    async function rollWithValue(sides, value) {
        console.log(`\n🎲 Rolando D${sides} → valor desejado: ${value}`);

        let dice;
        switch(sides) {
            case 4: dice = new DiceD4({ size: 15 }); break;
            case 6: dice = new DiceD6({ size: 15 }); break;
            case 10: dice = new DiceD10({ size: 15 }); break;
            case 12: dice = new DiceD12({ size: 15 }); break;
            case 20: dice = new DiceD20({ size: 15 }); break;
            default:
                console.warn("⚠️ Dado não suportado, usando D6");
                dice = new DiceD6({ size: 15 });
        }

        scene.add(dice.getObject());
        dice.updateBodyFromMesh();

        // Prepara valor controlado
        DiceManager.prepareValues([{ dice, value }]);

        // Lança o dado
        dice.throwRandom();

        // Loop de animação
        function animate() {
            world.step(1/60);
            dice.updateMeshFromBody();
            renderer.render(scene, camera);
            if (!dice.isSleeping()) requestAnimationFrame(animate);
            else console.log("✅ Dado parado → resultado:", dice.getResult());
        }
        animate();
    }

    // Exporta globalmente
    window.rollWithValue = rollWithValue;
    return { scene, camera, renderer, world, rollWithValue };
}

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
