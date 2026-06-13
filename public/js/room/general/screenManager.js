/**
 * =========================
 * 📌 INICIALIZAÇÃO
 * =========================
 */
window.onload = function() {
    const aviso = document.getElementById('aviso-fullscreen');

    // Mostra aviso depois de 1s
    setTimeout(() => {
        if (!aviso) return;

        aviso.style.display = 'block';

        // Ao clicar no aviso → entra em fullscreen
        aviso.onclick = function() {
            entrarEmFullscreen();
            aviso.style.display = 'none';
        };
    }, 1000);

    // Esconde automaticamente após 5s
    setTimeout(() => {
        if (aviso) aviso.style.display = 'none';
    }, 5000);

    // Garante estado correto do botão ao carregar
    atualizarBotaoFullscreen();
};


/**
 * =========================
 * 📌 LISTENERS GLOBAIS
 * =========================
 */

// Dispara quando entra/sai do fullscreen via API
document.addEventListener('fullscreenchange', atualizarBotaoFullscreen);

// Dispara quando tela muda tamanho (inclui F11)
window.addEventListener('resize', atualizarBotaoFullscreen);

// Intercepta F11 e usa nosso controle
document.addEventListener('keydown', (e) => {
    if (e.key === 'F11') {
        e.preventDefault(); // bloqueia fullscreen padrão do navegador
        toggleFullscreen();
    }
});


/**
 * =========================
 * 📌 CONTROLE DO BOTÃO
 * =========================
 */
function atualizarBotaoFullscreen() {
    const btn = document.getElementById('fullscreen');
    if (!btn) return;

    // Detecta fullscreen via API
    const isFullscreenAPI = !!document.fullscreenElement;

    // Detecta fullscreen "real" (F11)
    const isFullscreenReal = window.innerHeight === screen.height;

    // Se qualquer um for true → está em fullscreen
    if (isFullscreenAPI || isFullscreenReal) {
        btn.innerHTML = '<i class="fa-solid fa-compress"></i> Minimizar';
        btn.onclick = sairDoFullscreen;
    } else {
        btn.innerHTML = '<i class="fa-solid fa-expand"></i> Tela Cheia';
        btn.onclick = entrarEmFullscreen;
    }
}


/**
 * =========================
 * 📌 AÇÕES DE FULLSCREEN
 * =========================
 */

// Entra em fullscreen usando API
function entrarEmFullscreen() {
    const elem = document.documentElement;

    if (elem.requestFullscreen) {
        elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) {
        elem.webkitRequestFullscreen(); // Safari
    } else if (elem.msRequestFullscreen) {
        elem.msRequestFullscreen(); // IE/Edge antigo
    }
}


// Sai do fullscreen
function sairDoFullscreen() {
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    }
}


// Alterna entre entrar/sair
function toggleFullscreen() {
    const isFullscreenAPI = !!document.fullscreenElement;
    const isFullscreenReal = window.innerHeight === screen.height;

    if (isFullscreenAPI || isFullscreenReal) {
        sairDoFullscreen();
    } else {
        entrarEmFullscreen();
    }
}
