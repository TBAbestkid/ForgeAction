import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import * as CANNON from 'cannon-es';
import { DiceManager, DiceD4, DiceD6, DiceD10, DiceD12, DiceD20 } from 'threejs-dice';

export async function initDice(containerId = '#dice-container') {
    console.log("🚀 Iniciando cena 3D de dados...");

    // Cena e câmera
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x222222);
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

    // Função de rolagem com valor controlado
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

        // Define valor antes da rolagem
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
    console.log("✅ rollWithValue() disponível globalmente");

    return { scene, camera, renderer, world, rollWithValue };
}
