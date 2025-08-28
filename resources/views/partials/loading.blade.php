<!-- Overlay de loading -->
<div id="loading-overlay" style="
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.9);
    display: none;
    z-index: 9999;
">

    <!-- Barra de progresso centralizada na parte inferior -->
    <div style="
        position: absolute;
        bottom: 50px;
        left: 50%;
        transform: translateX(-50%);
        width: 80%;
        max-width: 1200px;
    ">
        <div style="
            width: 100%;
            height: 25px;
            background: #222;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 20px #FFD700;
        ">
            <div id="progress-bar" style="
                width: 0%;
                height: 100%;
                background: linear-gradient(90deg, #FFD700, #FF8C00, #FF4500, #FFD700);
                box-shadow: 0 0 10px #FFD700, 0 0 20px #FF4500, 0 0 30px #FF8C00;
                border-radius: 12px;
                transition: width 0.2s;
                animation: glow 1s infinite alternate;
            "></div>
        </div>
    </div>

    <!-- GIF e texto centralizados acima da barra -->
    <div style="
        position: absolute;
        bottom: 90px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 15px;
        justify-content: center;
    ">
        <img src="{{ asset('assets/images/forjando.gif') }}" alt="Carregando..." style="width: 80px; height: auto;">
        <h2 id="loading-text" class="font-medieval text-white" style="font-size: 2rem; margin: 0;">
            Forjando
        </h2>
    </div>


</div>

<style>
@keyframes glow {
    0% { box-shadow: 0 0 10px #FFD700, 0 0 20px #FF4500, 0 0 30px #FF8C00; }
    50% { box-shadow: 0 0 15px #FFD700, 0 0 25px #FF4500, 0 0 35px #FF8C00; }
    100% { box-shadow: 0 0 10px #FFD700, 0 0 20px #FF4500, 0 0 30px #FF8C00; }
}
</style>
