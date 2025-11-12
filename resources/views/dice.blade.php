@extends('partials.app')

@section('title', 'Dados Teste - Controle Total')

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
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.162.0/build/three.module.js';
import * as CANNON from 'https://cdn.jsdelivr.net/npm/cannon-es@0.20.0/dist/cannon-es.js';

document.addEventListener("DOMContentLoaded", async () => {
    console.log("🚀 DOM carregado, iniciando cena de dados 3D...");

    // ======== Setup da cena ========
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x222222);

    const camera = new THREE.PerspectiveCamera(50, window.innerWidth/window.innerHeight, 0.1, 1000);
    camera.position.set(0, 50, 100);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.querySelector("#dice-container").appendChild(renderer.domElement);

    const light = new THREE.DirectionalLight(0xffffff, 1);
    light.position.set(50, 100, 50);
    scene.add(light);
    scene.add(new THREE.AmbientLight(0x888888));

    // ======== Setup da física ========
    const world = new CANNON.World();
    world.gravity.set(0, -9.8, 0);

    const groundMat = new CANNON.Material();
    const groundBody = new CANNON.Body({ mass: 0, material: groundMat });
    groundBody.addShape(new CANNON.Plane());
    groundBody.quaternion.setFromEuler(-Math.PI/2, 0, 0);
    world.addBody(groundBody);

    // ======== Função para criar dado ========
    function createDice(sides) {
        const size = 10;
        let geometry;
        switch(sides){
            case 4:  geometry = new THREE.TetrahedronGeometry(size); break;
            case 6:  geometry = new THREE.BoxGeometry(size, size, size); break;
            case 8:  geometry = new THREE.OctahedronGeometry(size); break;
            case 12: geometry = new THREE.DodecahedronGeometry(size); break;
            case 20: geometry = new THREE.IcosahedronGeometry(size); break;
            default: geometry = new THREE.BoxGeometry(size, size, size);
        }
        const material = new THREE.MeshStandardMaterial({color: 0xff4444});
        const mesh = new THREE.Mesh(geometry, material);
        scene.add(mesh);

        const shape = new CANNON.Box(new CANNON.Vec3(size/2, size/2, size/2));
        const body = new CANNON.Body({ mass: 1, shape: shape });
        body.position.set(0, 50, 0);
        world.addBody(body);

        return { mesh, body, sides };
    }

    // ======== Função de rolagem controlada ========
    function rollDiceControlled(sides, value) {
        console.log(`🎲 Rolando D${sides} → valor desejado: ${value}`);

        const dice = createDice(sides);

        // Ajuste inicial de rotação para que o valor desejado fique para cima
        // (exemplo básico: para D6, mapeia valor para rotação)
        if (sides === 6) {
            const rotations = [
                [0,0,0],                  // 1
                [Math.PI/2,0,0],          // 2
                [Math.PI,0,0],            // 3
                [-Math.PI/2,0,0],         // 4
                [0,0,Math.PI/2],          // 5
                [0,0,-Math.PI/2]          // 6
            ];
            const r = rotations[value-1];
            dice.body.quaternion.setFromEuler(r[0], r[1], r[2]);
        }
        // Para outros dados, você pode criar mapeamentos semelhantes

        // Aplica rotação aleatória inicial para simular “rolagem”
        dice.body.angularVelocity.set(Math.random()*10, Math.random()*10, Math.random()*10);

        // Loop de animação
        function animate() {
            world.step(1/60);
            dice.mesh.position.copy(dice.body.position);
            dice.mesh.quaternion.copy(dice.body.quaternion);
            renderer.render(scene, camera);

            if (dice.body.position.y > 10 || dice.body.angularVelocity.length() > 0.1) {
                requestAnimationFrame(animate);
            } else {
                console.log(`✅ Dado parado → valor final: ${value}`);
            }
        }
        animate();
    }

    // ======== Botões ========
    [4,6,10,12,20].forEach(sides => {
        document.querySelector(`#btn-d${sides}`).addEventListener('click', () => {
            const value = Math.floor(Math.random() * sides) + 1;
            rollDiceControlled(sides, value);
        });
    });

    console.log("✅ Setup completo — pronto para rolagens");
});
</script>
@endsection
