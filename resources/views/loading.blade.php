<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carregando ForgeAction</title>
    <style>
        /* Corpo centralizado e fundo escuro */
        body {
            margin: 0;
            height: 100vh;
            background-color: #111111;
            font-family: sans-serif;
            overflow: hidden; /* evita scroll enquanto carrega */
        }

        /* Overlay de loading */
        #loading-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        /* GIF e texto centralizados */
        #loading-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        #loading-content img {
            width: 120px;
            height: auto;
        }

        #loading-text {
            color: #fff;
            font-size: 2rem;
            text-align: center;
            font-family: 'MedievalSharp', serif;
        }

        /* Barra de progresso */
        #progress-container {
            width: 80%;
            max-width: 400px;
            height: 20px;
            background: #222;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 30px;
            box-shadow: 0 0 20px #FFD700;
        }

        #progress-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #FFD700, #FF8C00, #FF4500, #FFD700);
            border-radius: 10px;
            transition: width 0.2s;
            animation: glow 1s infinite alternate;
        }

        @keyframes glow {
            0% { box-shadow: 0 0 10px #FFD700, 0 0 20px #FF4500, 0 0 30px #FF8C00; }
            50% { box-shadow: 0 0 15px #FFD700, 0 0 25px #FF4500, 0 0 35px #FF8C00; }
            100% { box-shadow: 0 0 10px #FFD700, 0 0 20px #FF4500, 0 0 30px #FF8C00; }
        }
    </style>
</head>
<body>

    <!-- Overlay de loading -->
    <div id="loading-overlay">
        <div id="loading-content">
            <img src="/assets/images/forjando.gif" alt="Carregando...">
            <h2 id="loading-text">Forjando</h2>
        </div>
        <div id="progress-container">
            <div id="progress-bar"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        console.log('Loading JS ativo!');

        let loadingInterval, dotsInterval;

        function showLoading(duration = 2000) {
            const overlay = document.getElementById("loading-overlay");
            const bar = document.getElementById("progress-bar");
            const text = document.getElementById("loading-text");

            if (!overlay || !bar || !text) return;

            overlay.style.display = "flex";

            let width = 0;
            let step = 100 / (duration / 100);

            loadingInterval = setInterval(() => {
                width += step;
                if (width >= 100) {
                    width = 100;
                    clearInterval(loadingInterval);
                }
                bar.style.width = width + "%";
            }, 100);

            let dots = "";
            dotsInterval = setInterval(() => {
                if (dots.length >= 3) dots = "";
                else dots += ".";
                text.textContent = "Forjando" + dots;
            }, 500);
        }

        function hideLoading() {
            clearInterval(loadingInterval);
            clearInterval(dotsInterval);
            const overlay = document.getElementById("loading-overlay");
            const bar = document.getElementById("progress-bar");
            if (overlay) overlay.style.display = "none";
            if (bar) bar.style.width = "0%";
        }

        window.addEventListener('load', () => {
            const duration = 3000; // tempo de loading
            showLoading(duration);
            setTimeout(() => {
                hideLoading();
                window.location.href = '/home'; // redireciona para o app
            }, duration);
        });
    </script>
</body>
</html>
