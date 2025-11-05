// js/loading.js
console.log('teste!');
let loadingInterval, dotsInterval;

function showLoading(duration = 5000) {
    console.log("Overlay ativado");
    const overlay = document.getElementById("loading-overlay");
    const bar = document.getElementById("progress-bar");
    const text = document.getElementById("loading-text");

    if (!overlay || !bar || !text) return;

    overlay.style.display = "flex";

    let width = 0;
    let step = 100 / (duration / 100);

    loadingInterval = setInterval(() => {
        if (width >= 100) {
            clearInterval(loadingInterval);
            console.log("Barra completada!");
        } else {
            width += step;
            if (width > 100) width = 100;
            bar.style.width = width + "%";
            console.log("Barra width:", width.toFixed(2) + "%");
        }
    }, 100);

    let dots = "";
    dotsInterval = setInterval(() => {
        if (dots.length >= 3) dots = "";
        else dots += ".";
        text.textContent = "Forjando" + dots;
        console.log("Texto animado:", text.textContent);
    }, 500);
}

function hideLoading() {
    clearInterval(loadingInterval);
    clearInterval(dotsInterval);
    const overlay = document.getElementById("loading-overlay");
    const bar = document.getElementById("progress-bar");
    if (overlay) overlay.style.display = "none";
    if (bar) bar.style.width = "0%";
    console.log("Overlay escondido e barra resetada");
}

function goToPage(url, duration = 5000) {
    showLoading(duration);
    setTimeout(() => {
        console.log("Redirecionando para:", url);
        window.location.href = url;
    }, duration);
}
